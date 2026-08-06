<?php

namespace App\Support\Crm;

use App\Models\DonationType;
use App\Models\PaymentMethod;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

/**
 * Bağış türü, proje/faaliyet gibi sözlük kayıtları için güvenli silme / pasife alma.
 * Bağışı olan kayıtlar kalıcı silinmez; afiş ve geçmiş veriler korunur.
 */
class LookupDeletionGuard
{
    /**
     * @return array{success: bool, action: 'deleted'|'deactivated'|'blocked', message: string}
     */
    public function deleteOrDeactivate(Model $record): array
    {
        if (! $this->canHardDelete($record)) {
            if (! ($record->is_active ?? true)) {
                return [
                    'success' => false,
                    'action' => 'blocked',
                    'message' => 'Bu kayıt zaten pasif durumda ve bağış kayıtlarına bağlı olduğu için kalıcı silinemez.',
                ];
            }

            $record->update(['is_active' => false]);

            return [
                'success' => true,
                'action' => 'deactivated',
                'message' => 'Kayıt bağışlarda kullanıldığı için listeden kaldırıldı (pasife alındı). Geçmiş bağışlar ve afişler etkilenmez.',
            ];
        }

        $record->delete();

        return [
            'success' => true,
            'action' => 'deleted',
            'message' => 'Kayıt kalıcı olarak silindi.',
        ];
    }

    public function canHardDelete(Model $record): bool
    {
        if ($this->usageCount($record) > 0) {
            return false;
        }

        if ($record instanceof Project && $this->isPublishedWebsiteProject($record)) {
            return false;
        }

        return true;
    }

    public function usageCount(Model $record): int
    {
        if (method_exists($record, 'donations')) {
            return (int) $record->donations()->count();
        }

        return 0;
    }

    public function isPublishedWebsiteProject(Project $project): bool
    {
        return filled($project->cover_image)
            || filled($project->description)
            || filled($project->content)
            || (is_array($project->gallery_images) && count($project->gallery_images) > 0)
            || $project->mediaItems()->exists();
    }

    public function deleteWarning(Model $record): string
    {
        $count = $this->usageCount($record);

        if ($count > 0) {
            return "Bu kayıt {$count} bağışta kullanılıyor. Silmek yerine pasife alınacak; geçmiş bağışlar ve afişler korunur.";
        }

        if ($record instanceof Project && $this->isPublishedWebsiteProject($record)) {
            return 'Bu proje web sitesinde yayınlanmış içerik içeriyor. Kalıcı silmek yerine pasife alınacak.';
        }

        return 'Bu kayıt kalıcı olarak silinecek. Bu işlem geri alınamaz.';
    }

    public function toggleActive(Model $record, bool $active): void
    {
        $record->update(['is_active' => $active]);
    }

    /**
     * @param  class-string<DonationType|Project|PaymentMethod>  $modelClass
     */
    public function activeOptions(string $modelClass, string $labelColumn = 'name'): array
    {
        $query = $modelClass::query();

        if ($labelColumn === 'title') {
            return $query
                ->where(function ($q): void {
                    $q->where('is_active', true)
                        ->orWhereHas('donations');
                })
                ->orderBy('title')
                ->pluck('title', 'id')
                ->all();
        }

        return $query
            ->where(function ($q): void {
                $q->where('is_active', true)
                    ->orWhereHas('donations');
            })
            ->orderBy('sort_order')
            ->pluck($labelColumn, 'id')
            ->all();
    }

    /**
     * Dropdown'larda yalnızca aktif kayıtlar (yeni bağış için).
     *
     * @param  class-string<DonationType|Project>  $modelClass
     */
    public function selectableOptions(string $modelClass, string $labelColumn = 'name'): array
    {
        $query = $modelClass::query()->where('is_active', true);

        if ($labelColumn === 'title') {
            return $query->orderBy('title')->pluck('title', 'id')->all();
        }

        return $query->orderBy('sort_order')->pluck($labelColumn, 'id')->all();
    }
}
