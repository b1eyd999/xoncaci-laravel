<?php

namespace App\Support;

use App\Models\FillItem;
use App\Models\Setting;
use App\Models\Xonca;
use Illuminate\Validation\ValidationException;

/**
 * Prices a cart the way the server sees it. The browser sends only ids and
 * quantities; every price is read again here, so a changed page cannot
 * change what is charged.
 */
class Quote
{
    /** @var list<array<string, mixed>> */
    public array $lines = [];

    public float $xoncaTotal = 0;

    public float $fillTotal = 0;

    public float $depositTotal = 0;

    public float $deliveryFee = 0;

    public float $total = 0;

    /**
     * @param  list<array{xonca_id:int, fill_mode?:string, ribbon?:string|null, fill?:list<array{id:int, qty:int}>}>  $cart
     */
    public static function make(array $cart, string $deliveryType = 'delivery'): self
    {
        $quote = new self;

        $xoncas = Xonca::with('defaultFills.item')->whereIn('id', collect($cart)->pluck('xonca_id'))->where('is_active', true)->get()->keyBy('id');
        $items = FillItem::whereIn('id', collect($cart)->pluck('fill')->flatten(1)->pluck('id')->filter())->where('is_active', true)->get()->keyBy('id');

        foreach ($cart as $i => $row) {
            $xonca = $xoncas->get((int) ($row['xonca_id'] ?? 0));
            if (! $xonca) {
                throw ValidationException::withMessages(['cart' => 'Səbətdəki xonçalardan biri artıq mövcud deyil. Səbəti yeniləyin.']);
            }

            $mode = $row['fill_mode'] ?? 'ours';
            if (! in_array($mode, ['ours', 'custom', 'empty'], true)) {
                $mode = 'ours';
            }
            if (! $xonca->fill_allowed && $mode === 'custom') {
                $mode = 'ours';
            }

            $fill = [];
            if ($mode === 'ours') {
                foreach ($xonca->defaultFills as $f) {
                    if ($f->item && $f->item->is_active) {
                        $fill[] = ['id' => $f->item->id, 'name' => $f->item->name, 'price' => (float) $f->item->price, 'qty' => (int) $f->qty];
                    }
                }
            } elseif ($mode === 'custom') {
                foreach ((array) ($row['fill'] ?? []) as $f) {
                    $item = $items->get((int) ($f['id'] ?? 0));
                    $qty = min(max(1, (int) ($f['qty'] ?? 1)), $item?->max_qty ?? 1);
                    if ($item) {
                        $fill[] = ['id' => $item->id, 'name' => $item->name, 'price' => (float) $item->price, 'qty' => $qty];
                    }
                }
            }

            $fillSum = collect($fill)->sum(fn ($f) => $f['price'] * $f['qty']);
            $price = $mode === 'empty' ? max(0, $xonca->price - $xonca->empty_discount) : $xonca->price;

            $ribbon = $row['ribbon'] ?? null;
            if (! in_array($ribbon, $xonca->ribbonChoices(), true)) {
                $ribbon = $xonca->ribbon_color;
            }

            $quote->lines[] = [
                'xonca' => $xonca,
                'xonca_id' => $xonca->id,
                'xonca_name' => $xonca->name,
                'fill_mode' => $mode,
                'ribbon_color' => $ribbon,
                'price' => (float) $price,
                'deposit' => (float) $xonca->deposit,
                'fill' => $fill,
                'fill_total' => (float) $fillSum,
            ];

            $quote->xoncaTotal += $price;
            $quote->fillTotal += $fillSum;
            $quote->depositTotal += $xonca->deposit;
        }

        $subtotal = $quote->xoncaTotal + $quote->fillTotal;
        $freeFrom = (float) Setting::get('free_delivery_from');
        $quote->deliveryFee = $deliveryType === 'pickup' || ($freeFrom > 0 && $subtotal >= $freeFrom)
            ? 0.0
            : (float) Setting::get('delivery_fee');

        $quote->total = $subtotal + $quote->deliveryFee;

        return $quote;
    }
}
