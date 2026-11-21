<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Real-looking launch content for the live site: upcoming events (free tickets),
 * home slider banners, category / city / blog images, and the privacy policy,
 * terms and cookie policy pages. Content + photos live in database/seeders/live-content/.
 *
 *   php artisan db:seed --class=LiveContentSeeder --force
 *
 * Safe to run again: events/blogs/banners are matched by name/title and updated,
 * never duplicated. Writes only columns that exist, so it also works on a server
 * whose schema is a few migrations behind.
 */
class LiveContentSeeder extends Seeder
{
    private array $content;
    private string $src;
    private array $columns = [];

    public function run(): void
    {
        $this->src = database_path('seeders/live-content');
        $this->content = json_decode(file_get_contents($this->src . '/content.json'), true);

        $this->categories();
        $this->cities();
        $organizer = $this->organizer();
        $events = $this->events($organizer);
        $this->banners($events);
        $this->blogs();
        $this->hidePlaceholderBrands();
        $this->policies();
        $this->contact();

        $this->command?->info('Live content seeded: ' . count($events) . ' events.');
    }

    // ------------------------------------------------------------------ helpers

    /** Keep only keys that are real columns of $table. */
    private function only(string $table, array $data): array
    {
        $this->columns[$table] ??= Schema::getColumnListing($table);
        return array_intersect_key($data, array_flip($this->columns[$table]));
    }

    /** Copy a bundled photo into public/images/upload once; returns the stored file name. */
    private function image(string $file): string
    {
        $name = 'w2p_' . $file;
        $dest = public_path('images/upload/' . $name);
        if (!is_file($dest)) {
            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0755, true);
            }
            copy($this->src . '/' . $file, $dest);
        }
        return $name;
    }

    private function categoryId(string $name): int
    {
        $id = DB::table('category')->where('name', $name)->value('id');
        if (!$id) {
            $id = DB::table('category')->insertGetId($this->only('category', [
                'name' => $name, 'status' => 1, 'image' => 'default.png',
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }
        return (int) $id;
    }

    private function cityId(string $name): ?int
    {
        if (!Schema::hasTable('city')) {
            return null;
        }
        $id = DB::table('city')->where('name', $name)->value('id');
        if (!$id) {
            $id = DB::table('city')->insertGetId($this->only('city', [
                'name' => $name, 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]));
        }
        return (int) $id;
    }

    // ------------------------------------------------------------------ sections

    private function categories(): void
    {
        foreach ($this->content['category_rename'] as $from => $to) {
            if (!DB::table('category')->where('name', $to)->exists()) {
                DB::table('category')->where('name', $from)->update(['name' => $to, 'updated_at' => now()]);
            }
        }
        foreach ($this->content['category_images'] as $name => $file) {
            $id = $this->categoryId($name);
            DB::table('category')->where('id', $id)->update(['image' => $this->image($file), 'status' => 1, 'updated_at' => now()]);
        }
    }

    private function cities(): void
    {
        if (!Schema::hasTable('city') || !Schema::hasColumn('city', 'image')) {
            return;
        }
        foreach ($this->content['city_images'] as $name => $file) {
            $id = $this->cityId($name);
            DB::table('city')->where('id', $id)->update(['image' => $this->image($file), 'status' => 1, 'updated_at' => now()]);
        }
    }

    /** The organizer shown on these events. */
    private function organizer(): int
    {
        $user = User::where('email', 'events@way2party.com')->first();
        if (!$user) {
            $user = new User();
            foreach ($this->only('users', [
                'first_name' => 'Way2Party',
                'last_name' => 'Events',
                'name' => 'Way2Party Events',
                'organization_name' => 'Way2Party Events',
                'email' => 'events@way2party.com',
                'password' => Hash::make(Str::random(32)),
                'status' => 1,
                'is_verify' => 1,
                'image' => 'defaultuser.png',
                'bio' => 'Curated parties and events across Mumbai, Hyderabad, Chennai and Indore.',
                'country' => 'India',
            ]) as $k => $v) {
                $user->{$k} = $v;
            }
            $user->save();
        }
        if (method_exists($user, 'hasRole') && !$user->hasRole('Organizer')) {
            $user->assignRole('Organizer');
        }
        return $user->id;
    }

    /** @return array<string,int> event key => id */
    private function events(int $organizer): array
    {
        $scanner = method_exists(User::class, 'scopeRole')
            ? User::role('scanner')->orderBy('id')->value('id')
            : null;
        $ids = [];
        $hasPivot = Schema::hasTable('event_category');

        foreach ($this->content['events'] as $e) {
            $categoryId = $this->categoryId($e['category']);
            $row = $this->only('events', [
                'name' => $e['name'],
                'type' => 'offline',
                'user_id' => $organizer,
                'scanner_id' => $scanner ? (string) $scanner : null,
                'category_id' => $categoryId,
                'city_id' => $this->cityId($e['city']),
                'address' => $e['address'],
                'lat' => $e['lat'],
                'lang' => $e['lang'],
                'start_time' => $e['start'],
                'end_time' => $e['end'],
                'image' => $this->image($e['image']),
                'gallery' => '',
                'people' => $e['people'],
                'description' => $e['description'],
                'tags' => '',
                'security' => 1,
                'status' => 1,
                'is_deleted' => 0,
                'event_status' => 'Pending',
                'is_featured' => $e['featured'] > 0 ? 1 : 0,
                'featured_order' => $e['featured'],
                'visibility' => 'everyone',
                'approval_status' => 'approved',
                'cancellation_allowed' => 0,
                'cancellation_charges' => 0,
                'updated_at' => now(),
            ]);

            $id = DB::table('events')->where('name', $e['name'])->value('id');
            if ($id) {
                DB::table('events')->where('id', $id)->update($row);
            } else {
                $id = DB::table('events')->insertGetId($row + $this->only('events', ['created_at' => now()]));
            }
            $ids[$e['key']] = (int) $id;

            if ($hasPivot) {
                DB::table('event_category')->updateOrInsert(['event_id' => $id, 'category_id' => $categoryId]);
            }
            $this->ticket((int) $id, $organizer, $e);
        }

        return $ids;
    }

    private function ticket(int $eventId, int $organizer, array $e): void
    {
        $row = $this->only('tickets', [
            'event_id' => $eventId,
            'user_id' => $organizer,
            'name' => 'Free Entry Pass',
            'type' => 'free',
            'price' => 0,
            'quantity' => $e['people'],
            'ticket_per_order' => 4,
            'allday' => 1,
            'maximum_checkins' => 1,
            'start_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'end_time' => $e['end'],
            'description' => $this->content['ticket_desc'],
            'status' => 1,
            'is_deleted' => 0,
            'updated_at' => now(),
        ]);
        $existing = DB::table('tickets')->where('event_id', $eventId)->where('name', 'Free Entry Pass')->value('id');
        if ($existing) {
            DB::table('tickets')->where('id', $existing)->update($row);
        } else {
            DB::table('tickets')->insert($row + $this->only('tickets', [
                'ticket_number' => chr(rand(65, 90)) . chr(rand(65, 90)) . '-' . rand(1000, 9999),
                'created_at' => now(),
            ]));
        }
    }

    private function banners(array $events): void
    {
        $titles = array_column($this->content['banners'], 'title');
        // retire the old placeholder slides
        DB::table('banner')->whereNotIn('title', $titles)->update(['status' => 0]);

        foreach ($this->content['banners'] as $b) {
            if (empty($events[$b['event']])) {
                continue;
            }
            DB::table('banner')->updateOrInsert(
                ['title' => $b['title']],
                $this->only('banner', [
                    'event_id' => $events[$b['event']],
                    'image' => $this->image($b['image']),
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    private function blogs(): void
    {
        $titles = array_column($this->content['blogs'], 'title');
        // retire the test posts ("placeholder blog content used for testing…")
        DB::table('blog')->whereNotIn('title', $titles)
            ->where('description', 'like', '%placeholder%')->update(['status' => 0]);

        foreach ($this->content['blogs'] as $b) {
            DB::table('blog')->updateOrInsert(
                ['title' => $b['title']],
                $this->only('blog', [
                    'category_id' => $this->categoryId($b['category']),
                    'image' => $this->image($b['image']),
                    'description' => $b['description'],
                    'tags' => $b['tags'],
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    /** Placeholder "organizer" logos linking to example.com — hide them (section disappears). */
    private function hidePlaceholderBrands(): void
    {
        if (!Schema::hasTable('brands') || !Schema::hasColumn('brands', 'status')) {
            return;
        }
        $q = DB::table('brands');
        if (Schema::hasColumn('brands', 'url')) {
            $q->where(function ($w) {
                $w->where('url', 'like', '%example.com%')->orWhereNull('url')->orWhere('url', '');
            });
        }
        $q->update(['status' => 0]);
    }

    private function policies(): void
    {
        $data = $this->only('general_settng', $this->content['settings']);
        if ($data) {
            DB::table('general_settng')->where('id', 1)->update($data);
        }
    }

    /** Contact page details — fills only empty fields, so edits made in admin are never overwritten. */
    private function contact(): void
    {
        if (!Schema::hasTable('admin_contact') || empty($this->content['contact'])) {
            return;
        }
        $row = DB::table('admin_contact')->where('id', 1)->first();
        if (!$row) {
            DB::table('admin_contact')->insert(['id' => 1] + $this->only('admin_contact', $this->content['contact']));
            return;
        }
        $fill = array_filter(
            $this->only('admin_contact', $this->content['contact']),
            fn ($v, $k) => empty($row->{$k}),
            ARRAY_FILTER_USE_BOTH
        );
        if ($fill) {
            DB::table('admin_contact')->where('id', 1)->update($fill);
        }
    }
}
