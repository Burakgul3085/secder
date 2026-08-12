<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Genel Ayarlar')
                ->description('Site genel görünümü ve temel bilgiler. Önerilen sıra: Site Kimliği → İletişim Bilgileri → Ana Sayfa alanları → Yasal Metinler.')
                ->schema([
                    Section::make('Site Kimliği')
                        ->icon('heroicon-o-identification')
                        ->description('Üst menü, footer ve tarayıcı sekmesinde görünen kimlik alanları.')
                        ->collapsible()
                        ->schema([
                            TextInput::make('site_title')
                                ->label('Site Başlığı')
                                ->helperText('Menü ve footer gibi ana alanlarda görünür.')
                                ->required(),
                            TextInput::make('header_tagline')
                                ->label('Header Sloganı')
                                ->maxLength(80)
                                ->placeholder('Örn: Secde Eden Bir Nesil İçin')
                                ->helperText('Üst menüde logo ve dernek adının altında yazdığınız gibi görünür. Boş bırakılırsa slogan satırı gizlenir. En fazla 80 karakter, tek satır önerilir.'),
                            Textarea::make('site_description')
                                ->label('Kısa Açıklama')
                                ->rows(3)
                                ->helperText('Footer ve meta açıklama (SEO) alanlarında kullanılır.'),
                            FileUpload::make('logo')
                                ->image()
                                ->disk('public')
                                ->directory('settings')
                                ->label('Logo')
                                ->helperText('Menü ve footer logoları için kullanılır.'),
                            FileUpload::make('favicon')
                                ->image()
                                ->disk('public')
                                ->directory('settings')
                                ->label('Favicon')
                                ->helperText('Tarayıcı sekmesinde görünen küçük simge.'),
                        ])->columns(2),

                    Section::make('İletişim Bilgileri')
                        ->icon('heroicon-o-phone')
                        ->description('İletişim sayfası, footer ve bazı tanıtım bloklarında kullanılır.')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            TextInput::make('phone')->label('Telefon'),
                            TextInput::make('email')->email()->label('E-posta'),
                            TextInput::make('address')->label('Adres')->columnSpanFull(),
                            TextInput::make('website_url')->url()->label('Web Site Linki')->columnSpanFull(),
                            TextInput::make('google_maps_embed_url')
                                ->label('Google Maps Linki')
                                ->helperText('Footer üstünde Aile ve Nesil tarzı harita gösterir. En iyi sonuç: Google Maps → Paylaş → «Haritayı yerleştir» → HTML içindeki src linkini yapıştırın. Kısa maps.app.goo.gl linki de otomatik dönüştürülür.')
                                ->placeholder('https://www.google.com/maps/embed?pb=... veya maps.app.goo.gl/...')
                                ->columnSpanFull(),
                            Textarea::make('volunteer_preferences')
                                ->label('Gönüllülük Tercihleri')
                                ->rows(4)
                                ->helperText('Her satıra bir tercih yazın. Örn: Sosyal Medya, Saha Görevlisi, Genel Gönüllü')
                                ->default("Sosyal Medya\nSaha Görevlisi\nGenel Gönüllü")
                                ->columnSpanFull(),
                        ])->columns(2),

                    Section::make('Yasal Metinler')
                        ->icon('heroicon-o-document-text')
                        ->description('Footer modallarında ve ilgili başvuru alanlarında gösterilir.')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            Textarea::make('kvkk_text')
                                ->label('KVKK Aydınlatma Metni')
                                ->rows(8)
                                ->helperText('Footer’daki «KVKK» butonuna tıklanınca açılır.')
                                ->columnSpanFull(),
                            Textarea::make('volunteer_clarification_text')
                                ->label('Aydınlatma Metni')
                                ->rows(8)
                                ->helperText('Footer’daki «Aydınlatma Metni» ve gönüllü başvuru formunda gösterilir.')
                                ->columnSpanFull(),
                            Textarea::make('privacy_policy_text')
                                ->label('Gizlilik Politikası Metni')
                                ->rows(8)
                                ->helperText('Footer’daki «Gizlilik Politikası» içeriği.')
                                ->columnSpanFull(),
                            Textarea::make('cookie_policy_text')
                                ->label('Çerez Politikası Metni')
                                ->rows(8)
                                ->helperText('Footer’daki «Çerez Politikası» içeriği.')
                                ->columnSpanFull(),
                        ]),

                    Section::make('Ana Sayfa - Odak Kartları')
                        ->icon('heroicon-o-squares-2x2')
                        ->description('Ana sayfada öne çıkan 3 kartın başlık ve açıklamalarını düzenler.')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            TextInput::make('home_focus_1_title')
                                ->label('Kart 1 Başlık')
                                ->default('İlmi Eğitimler'),
                            Textarea::make('home_focus_1_text')
                                ->label('Kart 1 Metin')
                                ->rows(3)
                                ->default('Gaziantep\'te ilmi ve fikri eğitim programlarıyla bilinçli bir neslin yetişmesine katkı sunuyoruz.'),
                            TextInput::make('home_focus_2_title')
                                ->label('Kart 2 Başlık')
                                ->default('Sosyal Projeler'),
                            Textarea::make('home_focus_2_text')
                                ->label('Kart 2 Metin')
                                ->rows(3)
                                ->default('Selahaddin Eyyubi Cami Derneği olarak toplumun ihtiyaçlarına yönelik sosyal ve kültürel projeler yürütüyoruz.'),
                            TextInput::make('home_focus_3_title')
                                ->label('Kart 3 Başlık')
                                ->default('Cami ve Dayanışma'),
                            Textarea::make('home_focus_3_text')
                                ->label('Kart 3 Metin')
                                ->rows(3)
                                ->default('Cami çevresinde kardeşlik, gönüllülük ve dayanışma ruhunu güçlendiren çalışmalar yapıyoruz.'),
                        ])->columns(2),

                    Section::make('Ana Sayfa - Biz Kimiz Alanı')
                        ->icon('heroicon-o-user-group')
                        ->description('Ana sayfadaki Biz Kimiz bölümünün tüm metin ve görselleri.')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            TextInput::make('home_about_badge')
                                ->label('Rozet Metni')
                                ->default('SECDER'),
                            TextInput::make('home_about_title')
                                ->label('Başlık')
                                ->default('Biz Kimiz!'),
                            Textarea::make('home_about_intro')
                                ->label('Kısa Giriş Metni')
                                ->rows(3)
                                ->columnSpanFull(),
                            Textarea::make('home_about_body')
                                ->label('Açıklama Metni')
                                ->rows(5)
                                ->columnSpanFull(),
                            Textarea::make('home_about_items')
                                ->label('Madde Listesi')
                                ->rows(4)
                                ->helperText('Her satıra bir madde yazın.')
                                ->columnSpanFull(),
                            TextInput::make('home_about_button_text')
                                ->label('Buton Metni')
                                ->default('Hakkımızda'),
                            FileUpload::make('home_about_image')
                                ->image()
                                ->disk('public')
                                ->directory('settings')
                                ->label('Görsel (kare önerilir)')
                                ->helperText('Ana sayfadaki Biz Kimiz görsel alanında kullanılır.'),
                        ])->columns(2),

                    Section::make('Header Hızlı Panel')
                        ->icon('heroicon-o-squares-plus')
                        ->description('Menüdeki kare ikondan açılan panel için metin alanı.')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            Textarea::make('header_panel_volunteer_text')
                                ->label('Gönüllü Alanı Metni')
                                ->rows(4)
                                ->helperText('Ana menüdeki kare ikonundan açılan panelde, gönüllülük bölümünde gösterilir.')
                                ->columnSpanFull(),
                        ]),
                ])
                ->columns(1),

            Section::make('Sosyal Medya')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->description('Header/footer sosyal ikon linkleri ile hızlı panel başlığını düzenler.')
                ->collapsible()
                ->collapsed()
                ->schema([
                TextInput::make('social_section_title')
                    ->label('Sosyal medya bölümü başlığı')
                    ->helperText('Header hızlı panelin alt kısmındaki başlık (boşsa varsayılan metin kullanılır).'),
                TextInput::make('facebook_url')->url()->label('Facebook'),
                TextInput::make('instagram_url')->url()->label('Instagram'),
                TextInput::make('youtube_url')->url()->label('YouTube'),
                TextInput::make('tiktok_url')->url()->label('TikTok'),
                TextInput::make('x_url')->url()->label('X'),
                TextInput::make('linkedin_url')->url()->label('LinkedIn'),
                TextInput::make('whatsapp_url')->url()->label('WhatsApp (tam link, örn. https://wa.me/9053...)'),
                TextInput::make('telegram_url')->url()->label('Telegram (tam link)'),
                Toggle::make('is_active')->default(true)->label('Aktif'),
            ])->columns(2),
        ]);
    }
}
