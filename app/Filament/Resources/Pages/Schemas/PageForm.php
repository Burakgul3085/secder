<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        $richToolbar = [
            'bold', 'italic', 'underline', 'strike',
            'h2', 'h3', 'bulletList', 'orderedList',
            'blockquote', 'link', 'undo', 'redo',
        ];

        return $schema->components([
            Tabs::make('i18n_tabs')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('TR')->schema([
                        Grid::make(1)->schema([
                            TextInput::make('title_i18n.tr')
                                ->label('Başlık (TR)')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, $set, $get): void {
                                    if (! filled($get('slug'))) {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            RichEditor::make('content_i18n.tr')
                                ->label('İçerik (TR)')
                                ->toolbarButtons($richToolbar)
                                ->columnSpanFull(),
                        ]),
                    ]),
                    Tab::make('EN')->schema([
                        Grid::make(1)->schema([
                            TextInput::make('title_i18n.en')->label('Title (EN)'),
                            RichEditor::make('content_i18n.en')
                                ->label('Content (EN)')
                                ->toolbarButtons($richToolbar)
                                ->columnSpanFull(),
                        ]),
                    ]),
                    Tab::make('AR')->schema([
                        Grid::make(1)->schema([
                            TextInput::make('title_i18n.ar')->label('العنوان (AR)'),
                            RichEditor::make('content_i18n.ar')
                                ->label('المحتوى (AR)')
                                ->toolbarButtons($richToolbar)
                                ->columnSpanFull(),
                        ]),
                    ]),
                    Tab::make('RU')->schema([
                        Grid::make(1)->schema([
                            TextInput::make('title_i18n.ru')->label('Заголовок (RU)'),
                            RichEditor::make('content_i18n.ru')
                                ->label('Содержимое (RU)')
                                ->toolbarButtons($richToolbar)
                                ->columnSpanFull(),
                        ]),
                    ]),
                ]),

            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->helperText('URL adresi. Genelde Türkçe başlıktan üretilir; değiştirmeyin.'),

            Repeater::make('story_items')
                ->label('Hikayemiz Zaman Tüneli Öğeleri')
                ->schema([
                    Tabs::make('story_i18n')->tabs([
                        Tab::make('TR')->schema([
                            TextInput::make('title_i18n.tr')->label('Öğe Başlığı (TR)')->required()->maxLength(120),
                            Textarea::make('description_i18n.tr')->label('Açıklama (TR)')->required()->rows(4)->maxLength(2000)->columnSpanFull(),
                        ]),
                        Tab::make('EN')->schema([
                            TextInput::make('title_i18n.en')->label('Item Title (EN)')->maxLength(120),
                            Textarea::make('description_i18n.en')->label('Description (EN)')->rows(4)->maxLength(2000)->columnSpanFull(),
                        ]),
                        Tab::make('AR')->schema([
                            TextInput::make('title_i18n.ar')->label('العنوان (AR)')->maxLength(120),
                            Textarea::make('description_i18n.ar')->label('الوصف (AR)')->rows(4)->maxLength(2000)->columnSpanFull(),
                        ]),
                        Tab::make('RU')->schema([
                            TextInput::make('title_i18n.ru')->label('Заголовок (RU)')->maxLength(120),
                            Textarea::make('description_i18n.ru')->label('Описание (RU)')->rows(4)->maxLength(2000)->columnSpanFull(),
                        ]),
                    ])->columnSpanFull(),
                    FileUpload::make('image')
                        ->label('Görsel')
                        ->image()
                        ->disk('public')
                        ->directory('pages/story')
                        ->imageEditor()
                        ->maxSize(4096)
                        ->required(),
                ])
                ->defaultItems(0)
                ->reorderable()
                ->collapsible()
                ->cloneable()
                ->addActionLabel('Zaman Tüneli Öğesi Ekle')
                ->columnSpanFull()
                ->visible(fn ($get): bool => (string) $get('slug') === 'hikayemiz'),

            Section::make('Başkanın Mesajı Alanları')
                ->schema([
                    FileUpload::make('page_meta.president_image')
                        ->label('Başkan Görseli')
                        ->image()
                        ->disk('public')
                        ->directory('pages/president')
                        ->imageEditor()
                        ->maxSize(4096),
                    TextInput::make('page_meta.signature_name')
                        ->label('Başkan Ad Soyad')
                        ->maxLength(120)
                        ->placeholder('Örn. Ahmet Yılmaz')
                        ->helperText('İsim çevirilmez; tüm dillerde aynı görünür.'),
                    Tabs::make('signature_title_tabs')->tabs([
                        Tab::make('TR')->schema([
                            TextInput::make('page_meta.signature_title_i18n.tr')->label('Dernek / Unvan (TR)')->maxLength(190),
                        ]),
                        Tab::make('EN')->schema([
                            TextInput::make('page_meta.signature_title_i18n.en')->label('Organization / Title (EN)')->maxLength(190),
                        ]),
                        Tab::make('AR')->schema([
                            TextInput::make('page_meta.signature_title_i18n.ar')->label('المؤسسة / اللقب (AR)')->maxLength(190),
                        ]),
                        Tab::make('RU')->schema([
                            TextInput::make('page_meta.signature_title_i18n.ru')->label('Организация / Титул (RU)')->maxLength(190),
                        ]),
                    ])->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn ($get): bool => (string) $get('slug') === 'baskanin-mesaji'),

            Section::make('Hakkımızda Sayfası Alanları')
                ->schema([
                    FileUpload::make('page_meta.about_image')
                        ->label('Üst Görsel')
                        ->image()
                        ->disk('public')
                        ->directory('pages/about')
                        ->imageEditor()
                        ->maxSize(15360)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('Hakkımızda sayfasında gösterilecek görsel. JPG/PNG/WEBP, maksimum 15 MB. Metin çevirileri üstteki dil sekmelerinden girilir.'),
                ])
                ->columnSpanFull()
                ->visible(fn ($get): bool => (string) $get('slug') === 'hakkimizda'),

            Section::make('Vizyon Misyon Sayfası Alanları')
                ->schema([
                    Tabs::make('vision_mission_tabs')->tabs([
                        Tab::make('TR')->schema([
                            RichEditor::make('page_meta.vision_text_i18n.tr')->label('Vizyon (TR)')->toolbarButtons($richToolbar)->columnSpanFull(),
                            RichEditor::make('page_meta.mission_text_i18n.tr')->label('Misyon (TR)')->toolbarButtons($richToolbar)->columnSpanFull(),
                        ]),
                        Tab::make('EN')->schema([
                            RichEditor::make('page_meta.vision_text_i18n.en')->label('Vision (EN)')->toolbarButtons($richToolbar)->columnSpanFull(),
                            RichEditor::make('page_meta.mission_text_i18n.en')->label('Mission (EN)')->toolbarButtons($richToolbar)->columnSpanFull(),
                        ]),
                        Tab::make('AR')->schema([
                            RichEditor::make('page_meta.vision_text_i18n.ar')->label('الرؤية (AR)')->toolbarButtons($richToolbar)->columnSpanFull(),
                            RichEditor::make('page_meta.mission_text_i18n.ar')->label('المهمة (AR)')->toolbarButtons($richToolbar)->columnSpanFull(),
                        ]),
                        Tab::make('RU')->schema([
                            RichEditor::make('page_meta.vision_text_i18n.ru')->label('Видение (RU)')->toolbarButtons($richToolbar)->columnSpanFull(),
                            RichEditor::make('page_meta.mission_text_i18n.ru')->label('Миссия (RU)')->toolbarButtons($richToolbar)->columnSpanFull(),
                        ]),
                    ])->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn ($get): bool => (string) $get('slug') === 'vizyon-misyon'),

            Section::make('Kurumsal Belge Sayfası Alanları')
                ->schema([
                    Tabs::make('document_title_tabs')->tabs([
                        Tab::make('TR')->schema([
                            TextInput::make('page_meta.document_title_i18n.tr')->label('Belge Başlığı (TR)')->maxLength(150),
                        ]),
                        Tab::make('EN')->schema([
                            TextInput::make('page_meta.document_title_i18n.en')->label('Document Title (EN)')->maxLength(150),
                        ]),
                        Tab::make('AR')->schema([
                            TextInput::make('page_meta.document_title_i18n.ar')->label('عنوان المستند (AR)')->maxLength(150),
                        ]),
                        Tab::make('RU')->schema([
                            TextInput::make('page_meta.document_title_i18n.ru')->label('Название документа (RU)')->maxLength(150),
                        ]),
                    ])->columnSpanFull(),
                    FileUpload::make('page_meta.document_file')
                        ->label('Belge Dosyası (PDF/JPG/PNG/DOC/DOCX)')
                        ->disk('public')
                        ->directory('pages/charter')
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->maxSize(15360)
                        ->downloadable()
                        ->openable()
                        ->helperText('Maksimum 15 MB. PDF/JPG/PNG/DOC/DOCX yükleyebilirsiniz.'),
                ])
                ->columns(1)
                ->columnSpanFull()
                ->visible(fn ($get): bool => in_array((string) $get('slug'), ['dernek-tuzugu', 'faaliyet-belgesi', 'kurumsal-evrak-arsivi'], true)),

            Section::make('Yönetim Sayfası Alanları')
                ->schema([
                    Repeater::make('page_meta.management_sections')
                        ->label('Yönetim Bölümleri')
                        ->schema([
                            Tabs::make('section_title_tabs')->tabs([
                                Tab::make('TR')->schema([
                                    TextInput::make('section_title_i18n.tr')->label('Bölüm Başlığı (TR)')->required()->maxLength(120),
                                ]),
                                Tab::make('EN')->schema([
                                    TextInput::make('section_title_i18n.en')->label('Section Title (EN)')->maxLength(120),
                                ]),
                                Tab::make('AR')->schema([
                                    TextInput::make('section_title_i18n.ar')->label('عنوان القسم (AR)')->maxLength(120),
                                ]),
                                Tab::make('RU')->schema([
                                    TextInput::make('section_title_i18n.ru')->label('Заголовок раздела (RU)')->maxLength(120),
                                ]),
                            ])->columnSpanFull(),
                            Repeater::make('members')
                                ->label('Bölüm Üyeleri')
                                ->schema([
                                    TextInput::make('name')->label('Ad Soyad')->required()->maxLength(120),
                                    Tabs::make('role_tabs')->tabs([
                                        Tab::make('TR')->schema([
                                            TextInput::make('role_i18n.tr')->label('Unvan (TR)')->required()->maxLength(140),
                                        ]),
                                        Tab::make('EN')->schema([
                                            TextInput::make('role_i18n.en')->label('Title (EN)')->maxLength(140),
                                        ]),
                                        Tab::make('AR')->schema([
                                            TextInput::make('role_i18n.ar')->label('المسمى (AR)')->maxLength(140),
                                        ]),
                                        Tab::make('RU')->schema([
                                            TextInput::make('role_i18n.ru')->label('Должность (RU)')->maxLength(140),
                                        ]),
                                    ])->columnSpanFull(),
                                    FileUpload::make('photo')
                                        ->label('Fotoğraf (Opsiyonel)')
                                        ->image()
                                        ->disk('public')
                                        ->directory('pages/management')
                                        ->imageEditor()
                                        ->maxSize(4096),
                                ])
                                ->reorderable()
                                ->collapsible()
                                ->cloneable()
                                ->addActionLabel('Üye Ekle')
                                ->columnSpanFull()
                                ->minItems(1),
                        ])
                        ->reorderable()
                        ->collapsible()
                        ->cloneable()
                        ->addActionLabel('Yönetim Bölümü Ekle')
                        ->columnSpanFull()
                        ->defaultItems(0),
                ])
                ->columnSpanFull()
                ->visible(fn ($get): bool => (string) $get('slug') === 'yonetim'),

            Section::make('Resmi Bilgiler Sayfası Alanları')
                ->schema([
                    TextInput::make('page_meta.maps_embed_url')
                        ->label('Google Maps Embed URL')
                        ->url()
                        ->helperText('Google Maps "Embed a map" bağlantısını girin. Bu sayfadaki harita kartında kullanılır.')
                        ->columnSpanFull(),
                    TextInput::make('page_meta.donation_page_url')
                        ->label('Bağış Sayfası URL (Opsiyonel)')
                        ->url()
                        ->helperText('Resmi Bilgiler sayfasındaki bağış hesabı kartları bu adrese yönlendirilir.'),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn ($get): bool => (string) $get('slug') === 'resmi-bilgiler'),

            Section::make('Basın Kiti Sayfası Alanları')
                ->schema([
                    Repeater::make('page_meta.press_kit_items')
                        ->label('Basın Kiti Dosyaları')
                        ->schema([
                            Tabs::make('press_title_tabs')->tabs([
                                Tab::make('TR')->schema([
                                    TextInput::make('title_i18n.tr')->label('Dosya Başlığı (TR)')->required()->maxLength(120),
                                ]),
                                Tab::make('EN')->schema([
                                    TextInput::make('title_i18n.en')->label('File Title (EN)')->maxLength(120),
                                ]),
                                Tab::make('AR')->schema([
                                    TextInput::make('title_i18n.ar')->label('عنوان الملف (AR)')->maxLength(120),
                                ]),
                                Tab::make('RU')->schema([
                                    TextInput::make('title_i18n.ru')->label('Название файла (RU)')->maxLength(120),
                                ]),
                            ])->columnSpanFull(),
                            FileUpload::make('logo')
                                ->label('Kartta Görünecek Logo / Görsel')
                                ->image()
                                ->disk('public')
                                ->directory('pages/press-kit/logos')
                                ->imageEditor()
                                ->maxSize(10240)
                                ->helperText('Opsiyonel. Boş bırakılırsa site logosu kullanılır.'),
                            FileUpload::make('file')
                                ->label('İndirilebilir Dosya')
                                ->disk('public')
                                ->directory('pages/press-kit/files')
                                ->required()
                                ->acceptedFileTypes([
                                    'image/jpeg', 'image/png', 'image/webp', 'application/pdf',
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'application/vnd.ms-excel',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'text/csv',
                                ])
                                ->maxSize(20480)
                                ->downloadable()
                                ->openable()
                                ->helperText('JPG, PNG, PDF, Word veya Excel. Maksimum 20 MB.'),
                            TextInput::make('format_label')
                                ->label('Format Etiketi (Opsiyonel)')
                                ->maxLength(40)
                                ->placeholder('PNG / PDF / DOCX / XLSX'),
                        ])
                        ->reorderable()
                        ->collapsible()
                        ->cloneable()
                        ->addActionLabel('Basın Kiti Dosyası Ekle')
                        ->columnSpanFull()
                        ->defaultItems(0),
                ])
                ->columnSpanFull()
                ->visible(fn ($get): bool => (string) $get('slug') === 'basin-kiti'),

            Toggle::make('is_active')->default(true)->label('Aktif'),
        ]);
    }
}
