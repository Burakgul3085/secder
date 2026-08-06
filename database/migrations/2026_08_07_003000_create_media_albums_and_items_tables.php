<?php

use App\Models\MediaItem;
use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_albums', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            $table->foreignId('media_album_id')->nullable()->constrained('media_albums')->cascadeOnDelete();
            $table->string('type', 20); // image|video
            $table->string('path');
            $table->string('title')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'type', 'sort_order']);
            $table->index(['media_album_id', 'type', 'sort_order']);
            $table->index(['type']);
        });

        // Mevcut faaliyet JSON galerisini tek medya havuzuna aktar.
        Project::query()->orderBy('id')->each(function (Project $project): void {
            $order = 0;
            foreach (array_values(array_filter((array) $project->gallery_images)) as $path) {
                MediaItem::query()->create([
                    'project_id' => $project->id,
                    'media_album_id' => null,
                    'type' => MediaItem::TYPE_IMAGE,
                    'path' => ltrim((string) $path, '/'),
                    'sort_order' => $order++,
                ]);
            }

            $order = 0;
            foreach (array_values(array_filter((array) $project->gallery_videos)) as $path) {
                MediaItem::query()->create([
                    'project_id' => $project->id,
                    'media_album_id' => null,
                    'type' => MediaItem::TYPE_VIDEO,
                    'path' => ltrim((string) $path, '/'),
                    'sort_order' => $order++,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_items');
        Schema::dropIfExists('media_albums');
    }
};
