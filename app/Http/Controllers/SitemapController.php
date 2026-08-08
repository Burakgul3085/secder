<?php

namespace App\Http\Controllers;

use App\Models\MediaAlbum;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [];

        $add = function (
            string $loc,
            mixed $lastmod = null,
            string $changefreq = 'weekly',
            string $priority = '0.7'
        ) use (&$urls): void {
            $urls[] = [
                'loc' => $loc,
                'lastmod' => $lastmod
                    ? Carbon::parse($lastmod)->toAtomString()
                    : now()->toAtomString(),
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];
        };

        $add(route('home'), now(), 'daily', '1.0');
        $add(route('donations'), now(), 'weekly', '0.9');
        $add(route('activities.index'), now(), 'weekly', '0.9');
        $add(route('news.index'), now(), 'daily', '0.9');
        $add(route('gallery'), now(), 'weekly', '0.8');
        $add(route('contact'), now(), 'monthly', '0.7');
        $add(route('volunteer'), now(), 'monthly', '0.7');
        $add(route('zakat.index'), now(), 'monthly', '0.6');
        $add(route('islamic-finance.index'), now(), 'monthly', '0.6');

        Page::query()
            ->active()
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->each(function (Page $page) use ($add): void {
                $add(
                    route('pages.show', ['slug' => $page->slug]),
                    $page->updated_at,
                    'monthly',
                    '0.8'
                );
            });

        Project::query()
            ->active()
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at'])
            ->each(function (Project $project) use ($add): void {
                $add(
                    route('activities.show', ['slug' => $project->slug]),
                    $project->updated_at,
                    'weekly',
                    '0.8'
                );
            });

        News::query()
            ->active()
            ->latest('published_at')
            ->get(['id', 'published_at', 'updated_at'])
            ->each(function (News $news) use ($add): void {
                $add(
                    route('news.show', $news),
                    $news->published_at ?? $news->updated_at,
                    'weekly',
                    '0.7'
                );
            });

        MediaAlbum::query()
            ->active()
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at'])
            ->each(function (MediaAlbum $album) use ($add): void {
                if (! filled($album->slug)) {
                    return;
                }

                $add(
                    route('gallery', ['album' => $album->slug]),
                    $album->updated_at,
                    'weekly',
                    '0.6'
                );
            });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
