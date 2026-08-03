<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Başlık')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
            TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
            RichEditor::make('content')->label('İçerik')->columnSpanFull(),
            Repeater::make('story_items')
                ->label('Hikayemiz Zaman Tüneli Öğeleri')
                ->schema([
                    TextInput::make('title')
                        ->label('Öğe Başlığı')
                        ->required()
                        ->maxLength(120),
                    Textarea::make('description')
                        ->label('Açıklama')
                        ->required()
                        ->rows(4)
                        ->maxLength(2000)
                        ->columnSpanFull(),
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
                        ->helperText('Ana sayfada / Başkanın Mesajı sayfasında görünen isim. Boş bırakılırsa isim gösterilmez.'),
                    TextInput::make('page_meta.signature_title')
                        ->label('Dernek / Unvan Satırı')
                        ->maxLength(190)
                        ->default('SECDER - Selahaddin Eyyubi Cami Derneği')
                        ->placeholder('SECDER - Selahaddin Eyyubi Cami Derneği')
                        ->helperText('İsim üstünde görünen kurum satırı.'),
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
                        ->maxSize(4096)
                        ->helperText('Hakkımızda sayfasında en üstte ortalı gösterilecek görsel.'),
                ])
                ->columnSpanFull()
                ->visible(fn ($get): bool => (string) $get('slug') === 'hakkimizda'),
            Section::make('Vizyon Misyon Sayfası Alanları')
                ->schema([
                    RichEditor::make('page_meta.vision_text')
                        ->label('Vizyon Metni')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'h2',
                            'h3',
                            'bulletList',
                            'orderedList',
                            'blockquote',
                            'link',
                            'undo',
                            'redo',
                        ])
                        ->columnSpanFull(),
                    RichEditor::make('page_meta.mission_text')
                        ->label('Misyon Metni')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'h2',
                            'h3',
                            'bulletList',
                            'orderedList',
                            'blockquote',
                            'link',
                            'undo',
                            'redo',
                        ])
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->visible(fn ($get): bool => (string) $get('slug') === 'vizyon-misyon'),
            Section::make('Kurumsal Belge Sayfası Alanları')
                ->schema([
                    TextInput::make('page_meta.document_title')
                        ->label('Belge Başlığı')
                        ->maxLength(150)
                        ->placeholder('Faaliyet Belgesi'),
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
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn ($get): bool => in_array((string) $get('slug'), ['dernek-tuzugu', 'faaliyet-belgesi', 'kurumsal-evrak-arsivi'], true)),
            Section::make('Yönetim Sayfası Alanları')
                ->schema([
                    Repeater::make('page_meta.management_sections')
                        ->label('Yönetim Bölümleri')
                        ->schema([
                            TextInput::make('section_title')
                                ->label('Bölüm Başlığı')
                                ->required()
                                ->maxLength(120)
                                ->placeholder('Başkan / Başkan Yardımcısı / Genel Sekreter / Yönetim Kurulu Üyeleri'),
                            Repeater::make('members')
                                ->label('Bölüm Üyeleri')
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Ad Soyad')
                                        ->required()
                                        ->maxLength(120),
                                    TextInput::make('role')
                                        ->label('Unvan')
                                        ->required()
                                        ->maxLength(140),
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
                            TextInput::make('title')
                                ->label('Dosya Başlığı')
                                ->required()
                                ->maxLength(120)
                                ->placeholder('Kurumsal Logo'),
                            FileUpload::make('logo')
                                ->label('Kartta Görünecek Logo / Görsel')
                                ->image()
                                ->disk('public')
                                ->directory('pages/press-kit/logos')
                                ->imageEditor()
                                ->maxSize(10240),
                            FileUpload::make('file')
                                ->label('İndirilebilir Dosya')
                                ->disk('public')
                                ->directory('pages/press-kit/files')
                                ->required()
                                ->maxSize(10240)
                                ->rules([
                                    'file',
                                ]),
                            TextInput::make('format_label')
                                ->label('Format Etiketi (Opsiyonel)')
                                ->maxLength(40)
                                ->placeholder('PNG / PDF / JPG'),
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
