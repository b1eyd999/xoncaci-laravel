<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\FillCategory;
use App\Models\FillItem;
use App\Models\Occasion;
use App\Models\Review;
use App\Models\Xonca;
use App\Support\Availability;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function home()
    {
        $featured = Xonca::active()->with('defaultFills.item', 'occasions')->where('is_featured', true)->take(8)->get();
        if ($featured->isEmpty()) {
            $featured = Xonca::active()->with('defaultFills.item', 'occasions')->take(8)->get();
        }

        return view('site.home', [
            'hero' => $featured->first(),
            'featured' => $featured,
            'occasions' => Occasion::active()->withCount(['xoncas' => fn ($q) => $q->where('is_active', true)])->get(),
            'reviews' => Review::where('is_active', true)->orderBy('sort')->latest()->take(9)->get(),
            'faqs' => Faq::where('is_active', true)->orderBy('sort')->get(),
            'items' => $this->itemCatalog(),
            'itemCount' => FillItem::where('is_active', true)->count(),
            'xoncaCount' => Xonca::where('is_active', true)->count(),
        ]);
    }

    public function catalog(Request $request, ?Occasion $occasion = null)
    {
        $query = Xonca::active()->with('defaultFills.item', 'occasions');

        if ($occasion) {
            $query->whereHas('occasions', fn ($q) => $q->whereKey($occasion->id));
        }
        if ($shape = $request->string('forma')->toString()) {
            $query->where('shape', $shape);
        }
        if ($style = $request->string('stil')->toString()) {
            $query->where('style', $style);
        }
        match ($request->string('sirala')->toString()) {
            'ucuz' => $query->reorder('price'),
            'baha' => $query->reorder()->orderByDesc('price'),
            default => null,
        };

        $xoncas = $query->get();

        // A date filter keeps only the trays that are still free that day.
        $date = $request->date('tarix');
        if ($date) {
            $xoncas = $xoncas->filter(fn (Xonca $x) => Availability::free($x, $date->toDateString()) > 0)->values();
        }

        return view('site.catalog', [
            'xoncas' => $xoncas,
            'occasion' => $occasion,
            'occasions' => Occasion::active()->get(),
            'items' => $this->itemCatalog(),
            'date' => $date?->toDateString(),
        ]);
    }

    public function show(Xonca $xonca)
    {
        abort_unless($xonca->is_active, 404);
        $xonca->load('defaultFills.item', 'occasions');

        return view('site.xonca', [
            'xonca' => $xonca,
            'categories' => FillCategory::orderBy('sort')->with(['items' => fn ($q) => $q->where('is_active', true)])->get()->filter(fn ($c) => $c->items->isNotEmpty()),
            'items' => $this->itemCatalog(),
            'busy' => Availability::busyDays($xonca),
            'earliest' => Availability::earliest()->toDateString(),
            'similar' => Xonca::active()->with('defaultFills.item')->whereKeyNot($xonca->id)
                ->whereHas('occasions', fn ($q) => $q->whereIn('occasions.id', $xonca->occasions->pluck('id')))
                ->take(4)->get(),
        ]);
    }

    public function availability(Request $request)
    {
        $data = $request->validate(['xonca' => 'required|integer', 'date' => 'required|date']);
        $xonca = Xonca::findOrFail($data['xonca']);

        return ['free' => Availability::free($xonca, $data['date']), 'stock' => (int) $xonca->stock];
    }

    /** @return array<int, array<string, mixed>> */
    public static function itemCatalog(): array
    {
        return FillItem::active()->get()->mapWithKeys(fn (FillItem $i) => [$i->id => $i->toViewer()])->all();
    }

    protected function itemCatalogFor(): array
    {
        return self::itemCatalog();
    }
}
