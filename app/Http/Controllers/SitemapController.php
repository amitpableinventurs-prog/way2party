<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Category;
use App\Models\City;
use App\Models\Tag;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];

        $urls[] = ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => url('/all-events'), 'priority' => '0.9', 'changefreq' => 'daily'];
        $urls[] = ['loc' => url('/all-category'), 'priority' => '0.7', 'changefreq' => 'weekly'];
        $urls[] = ['loc' => url('/all-blogs'), 'priority' => '0.7', 'changefreq' => 'weekly'];
        $urls[] = ['loc' => url('/contact'), 'priority' => '0.5', 'changefreq' => 'monthly'];
        $urls[] = ['loc' => url('/privacy_policy'), 'priority' => '0.3', 'changefreq' => 'yearly'];

        $timezone = optional(\App\Models\Setting::find(1))->timezone ?: config('app.timezone');
        $now = Carbon::now($timezone)->format('Y-m-d');

        $upcoming = Event::where([['status', 1], ['is_deleted', 0], ['event_status', 'Pending'], ['end_time', '>', $now]])
            ->orderByDesc('id')
            ->get(['id', 'name', 'city_id', 'tags', 'updated_at']);

        $upcoming->each(function ($event) use (&$urls) {
            $urls[] = [
                'loc' => url('/event/' . $event->id . '/' . Str::slug($event->name)),
                'lastmod' => optional($event->updated_at)->format('Y-m-d'),
                'priority' => '0.8',
                'changefreq' => 'daily',
            ];
        });

        Category::where('status', 1)->orderByDesc('id')->get(['id', 'name'])
            ->each(function ($category) use (&$urls) {
                $urls[] = [
                    'loc' => url('/events-category/' . $category->id . '/' . Str::slug($category->name)),
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                ];
            });

        City::where('status', 1)->orderBy('name')->get(['id', 'name'])
            ->each(function ($city) use (&$urls) {
                $urls[] = [
                    'loc' => $city->url,
                    'priority' => '0.7',
                    'changefreq' => 'daily',
                ];
            });

        // tag pages, site-wide and per city, only where upcoming events exist
        $cities = City::where('status', 1)->get(['id', 'name'])->keyBy('id');
        $tags = Tag::where('status', 1)->get(['id', 'name'])->keyBy(fn ($t) => mb_strtolower($t->name));
        $tagPages = [];
        foreach ($upcoming as $event) {
            foreach ($event->tag_list as $name) {
                $tag = $tags->get(mb_strtolower($name));
                if (!$tag) {
                    continue;
                }
                $tagPages[$tag->url()] = true;
                if ($city = $cities->get($event->city_id)) {
                    $tagPages[$tag->url($city)] = true;
                }
            }
        }
        foreach (array_keys($tagPages) as $loc) {
            $urls[] = ['loc' => $loc, 'priority' => '0.6', 'changefreq' => 'daily'];
        }

        Blog::where('status', 1)->orderByDesc('id')->get(['id', 'title', 'updated_at'])
            ->each(function ($blog) use (&$urls) {
                $urls[] = [
                    'loc' => url('/blog-detail/' . $blog->id . '/' . Str::slug($blog->title)),
                    'lastmod' => optional($blog->updated_at)->format('Y-m-d'),
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                ];
            });

        $xml = view('sitemap', compact('urls'))->render();

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /user/',
            'Disallow: /checkout',
            'Disallow: /my-tickets',
            'Disallow: /my-ticket',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        return Response::make(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
    }
}
