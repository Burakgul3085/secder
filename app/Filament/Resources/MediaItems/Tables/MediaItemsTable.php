<?php

namespace App\Filament\Resources\MediaItems\Tables;

use App\Models\MediaItem;
use App\Support\MediaGallerySync;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class MediaItemsTable
{
    public static function configure(Table $table): Table
    {
        $sync = app(MediaGallerySync::class);

        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                ImageColumn::make('path')
                    ->label('Önizleme')
                    ->disk('public')
                    ->square()
                    ->getStateUsing(fn (MediaItem $record): ?string => $record->isImage() ? $record->path : null)
                    ->defaultImageUrl(url('/images/default-logo.svg')),
                TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === MediaItem::TYPE_VIDEO ? 'Video' : 'Fotoğraf')
                    ->color(fn (string $state): string => $state === MediaItem::TYPE_VIDEO ? 'warning' : 'info'),
                TextColumn::make('owner')
                    ->label('Başlık')
                    ->state(fn (MediaItem $record): string => $record->ownerLabel())
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search): void {
                            $q->whereHas('project', fn ($p) => $p->where('title', 'like', "%{$search}%"))
                                ->orWhereHas('album', fn ($a) => $a->where('title', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('path')
                    ->label('Dosya')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Güncelleme')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tip')
                    ->options([
                        MediaItem::TYPE_IMAGE => 'Fotoğraf',
                        MediaItem::TYPE_VIDEO => 'Video',
                    ]),
                SelectFilter::make('project_id')
                    ->label('Faaliyet')
                    ->relationship('project', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('media_album_id')
                    ->label('Albüm')
                    ->relationship('album', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('move')
                    ->label('Taşı')
                    ->icon('heroicon-o-arrows-right-left')
                    ->form([
                        Select::make('destination')
                            ->label('Hedef başlık')
                            ->options(fn (): array => $sync->destinationOptions())
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (MediaItem $record, array $data) use ($sync): void {
                        if (($data['destination'] ?? '') === $record->destinationKey()) {
                            Notification::make()->title('Öğe zaten bu başlıkta.')->warning()->send();

                            return;
                        }

                        $sync->moveToDestination($record, (string) $data['destination']);
                        Notification::make()->title('Medya taşındı.')->success()->send();
                    }),
                DeleteAction::make()
                    ->before(function (MediaItem $record): void {
                        $record->deleteFileIfOrphan();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('moveSelected')
                        ->label('Seçilenleri taşı')
                        ->icon('heroicon-o-arrows-right-left')
                        ->form([
                            Select::make('destination')
                                ->label('Hedef başlık')
                                ->options(fn (): array => $sync->destinationOptions())
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (Collection $records, array $data) use ($sync): void {
                            foreach ($records as $record) {
                                if (! $record instanceof MediaItem) {
                                    continue;
                                }
                                if (($data['destination'] ?? '') === $record->destinationKey()) {
                                    continue;
                                }
                                $sync->moveToDestination($record, (string) $data['destination']);
                            }

                            Notification::make()->title('Seçilen medya öğeleri taşındı.')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
