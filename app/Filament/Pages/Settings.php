<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Telegram;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Sayt';

    protected static ?string $navigationLabel = 'Tənzimləmələr';

    protected static ?string $title = 'Tənzimləmələr';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(collect(Setting::DEFAULTS)->keys()->mapWithKeys(fn ($k) => [$k => Setting::get($k)])->all());
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Tabs::make()->tabs([
                Forms\Components\Tabs\Tab::make('Əlaqə')->icon('heroicon-m-phone')->columns(2)->schema([
                    Forms\Components\TextInput::make('site_name')->label('Saytın adı')->required(),
                    Forms\Components\TextInput::make('phone')->label('Telefon')->required(),
                    Forms\Components\TextInput::make('whatsapp')->label('WhatsApp nömrəsi')->helperText('Yalnız rəqəmlər, məsələn 994501234567'),
                    Forms\Components\TextInput::make('instagram')->label('Instagram')->prefix('@'),
                    Forms\Components\TextInput::make('address')->label('Ünvan (AZ)'),
                    Forms\Components\TextInput::make('working_hours')->label('İş saatları (AZ)'),
                    Forms\Components\TextInput::make('address_ru')->label('Ünvan (RU)'),
                    Forms\Components\TextInput::make('working_hours_ru')->label('İş saatları (RU)'),
                    Forms\Components\TextInput::make('address_en')->label('Ünvan (EN)'),
                    Forms\Components\TextInput::make('working_hours_en')->label('İş saatları (EN)'),
                ]),
                Forms\Components\Tabs\Tab::make('Ana səhifə')->icon('heroicon-m-home')->schema([
                    Forms\Components\TextInput::make('hero_title')->label('Böyük başlıq (AZ)')
                        ->helperText('«|» işarəsindən sonrakı hissə yeni sətirdə, qızılı rəngdə göstərilir. İşarənin özü saytda görünmür.'),
                    Forms\Components\TextInput::make('hero_title_ru')->label('Böyük başlıq (RU)'),
                    Forms\Components\TextInput::make('hero_title_en')->label('Böyük başlıq (EN)'),
                    Forms\Components\Textarea::make('hero_subtitle')->label('Alt mətn (AZ)')->rows(3),
                    Forms\Components\Textarea::make('hero_subtitle_ru')->label('Alt mətn (RU)')->rows(3),
                    Forms\Components\Textarea::make('hero_subtitle_en')->label('Alt mətn (EN)')->rows(3),
                ]),
                Forms\Components\Tabs\Tab::make('Şəkillər')->icon('heroicon-m-photo')->columns(3)->schema([
                    Forms\Components\Placeholder::make('img_help')->hiddenLabel()->columnSpanFull()
                        ->content('Boş qalan yerdə pulsuz Unsplash fotosu göstərilir. Öz fotolarınızı yükləyin — geniş, yüksək keyfiyyətli, tünd tonlu şəkillər ən lüks görünür.'),
                    Forms\Components\Select::make('ar_enabled')->label('«Evinizdə baxın» (3D, kamera)')->options(['0' => 'Gizli', '1' => 'Saytda göstər'])->required()->columnSpanFull()
                        ->helperText('Xonçaların 3D skanları kifayət qədər real görünəndə açın.'),
                    Forms\Components\FileUpload::make('hero_image')->label('Ana ekran (böyük foto)')->image()->directory('site')->imageResizeTargetWidth(2600)->maxSize(12288),
                    Forms\Components\FileUpload::make('craft_image')->label('«Necə işləyir» bölməsi')->image()->directory('site')->imageResizeTargetWidth(2000)->maxSize(12288),
                    Forms\Components\FileUpload::make('cta_image')->label('Aşağıdakı çağırış bölməsi')->image()->directory('site')->imageResizeTargetWidth(2600)->maxSize(12288),
                ]),
                Forms\Components\Tabs\Tab::make('İcarə və çatdırılma')->icon('heroicon-m-truck')->columns(3)->schema([
                    Forms\Components\TextInput::make('delivery_fee')->label('Çatdırılma haqqı')->numeric()->prefix('₼'),
                    Forms\Components\TextInput::make('free_delivery_from')->label('Bu məbləğdən pulsuz')->numeric()->prefix('₼')->helperText('0 = heç vaxt pulsuz deyil'),
                    Forms\Components\TextInput::make('min_days_ahead')->label('Ən azı neçə gün əvvəl sifariş')->numeric()->suffix('gün'),
                    Forms\Components\TextInput::make('days_before')->label('Məclisdən neçə gün əvvəl aparılır')->numeric()->suffix('gün'),
                    Forms\Components\TextInput::make('days_after')->label('Məclisdən neçə gün sonra qaytarılır')->numeric()->suffix('gün')
                        ->helperText('Bu günlərdə xonça başqasına verilə bilməz.'),
                    Forms\Components\Textarea::make('districts')->label('Rayonlar (hər sətirdə bir)')->rows(8)->columnSpanFull(),
                ]),
                Forms\Components\Tabs\Tab::make('Telegram')->icon('heroicon-m-paper-airplane')->columns(2)->schema([
                    Forms\Components\Placeholder::make('tg_help')->hiddenLabel()->columnSpanFull()->content(
                        '1) Telegram-da @BotFather-ə yazın, /newbot ilə bot yaradın və tokeni bura yazın. 2) Bota və ya botu əlavə etdiyiniz qrupa «/start» yazın. 3) «Chat ID-ni tap» düyməsini basın. Hər yeni sifariş dərhal Telegram-a gələcək.'
                    ),
                    Forms\Components\TextInput::make('telegram_token')->label('Bot tokeni')->password()->revealable(),
                    Forms\Components\TextInput::make('telegram_chat')->label('Chat ID')
                        ->suffixAction(Forms\Components\Actions\Action::make('find')->icon('heroicon-m-magnifying-glass')->tooltip('Chat ID-ni tap')
                            ->action(function (Forms\Get $get, Forms\Set $set) {
                                $token = trim((string) $get('telegram_token'));
                                if (! $token) {
                                    Notification::make()->title('Əvvəlcə tokeni yazın')->warning()->send();

                                    return;
                                }
                                $updates = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getUpdates")->json('result', []);
                                foreach (array_reverse((array) $updates) as $u) {
                                    $chat = $u['message']['chat']['id'] ?? $u['my_chat_member']['chat']['id'] ?? $u['channel_post']['chat']['id'] ?? null;
                                    if ($chat) {
                                        $set('telegram_chat', (string) $chat);
                                        Notification::make()->title('Tapıldı: ' . $chat)->success()->send();

                                        return;
                                    }
                                }
                                Notification::make()->title('Mesaj tapılmadı. Bota «/start» yazıb yenidən yoxlayın.')->warning()->send();
                            })),
                ]),
            ])->persistTabInQueryString(),
        ]);
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            if (is_array($value)) {
                $value = reset($value) ?: '';
            }
            Setting::put($key, is_string($value) ? trim($value) : $value);
        }
        Notification::make()->title('Yadda saxlanıldı')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')->label('Telegram-ı yoxla')->icon('heroicon-m-paper-airplane')->color('gray')
                ->action(function () {
                    $this->save();
                    Telegram::send('✅ Xonçacı saytı Telegram-a qoşuldu. Yeni sifarişlər buraya gələcək.')
                        ? Notification::make()->title('Mesaj göndərildi')->success()->send()
                        : Notification::make()->title('Göndərilmədi — token və Chat ID-ni yoxlayın')->danger()->send();
                }),
        ];
    }
}
