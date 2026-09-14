<?php

namespace Database\Seeders;

use App\Models\AppUser;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Category;
use App\Models\City;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventFaq;
use App\Models\Faq;
use App\Models\Feedback;
use App\Models\MembershipFeature;
use App\Models\MembershipPlan;
use App\Models\Order;
use App\Models\OrderChild;
use App\Models\Review;
use App\Models\Tax;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

/**
 * Fills every admin-managed section with just enough believable data to click
 * through the whole panel + frontend. Safe to run more than once: each block
 * is guarded so existing rows are reused instead of duplicated.
 *
 *   php artisan db:seed --class=Database\\Seeders\\DummyDataSeeder
 */
class DummyDataSeeder extends Seeder
{
    private string $img = 'default.png';

    public function run(): void
    {
        $organizers = $this->organizers();
        $scanners   = $this->scanners($organizers);
        $customers  = $this->customers();
        $categories = $this->categories();
        $cities     = City::where('status', 1)->pluck('id')->all() ?: [1];

        $events  = $this->events($organizers, $scanners, $categories, $cities);
        $tickets = $this->tickets($events);
        $this->eventFaqs($events);
        $this->coupons($events, $organizers);
        $this->banners($events);

        $this->blogs($categories);
        $this->feedback($customers);
        $this->faqs();
        $this->taxes($organizers);

        $this->orders($events, $tickets, $customers);
        $this->membershipPlans();
        $this->attachRealImages();

        $this->command?->info('Dummy data seeded.');
    }

    /** @return int[] organizer user ids */
    private function organizers(): array
    {
        $ids = [];
        $rows = [
            ['first_name' => 'Nova',   'last_name' => 'Events',   'email' => 'nova.events@example.com',   'organization_name' => 'Nova Events Co.'],
            ['first_name' => 'Skyline','last_name' => 'Shows',    'email' => 'skyline.shows@example.com', 'organization_name' => 'Skyline Shows'],
            ['first_name' => 'Pulse',  'last_name' => 'Live',     'email' => 'pulse.live@example.com',    'organization_name' => 'Pulse Live'],
        ];
        foreach ($rows as $r) {
            $user = User::firstOrCreate(
                ['email' => $r['email']],
                array_merge($r, [
                    'password' => Hash::make('password'),
                    'status'   => 1,
                    'is_verify' => 1,
                    'phone'    => '90000' . rand(10000, 99999),
                    'image'    => $this->img,
                    'bio'      => 'Sample organizer account for testing.',
                    'country'  => 'India',
                ])
            );
            if (! $user->hasRole('Organizer')) {
                $user->assignRole('Organizer');
            }
            $ids[] = $user->id;
        }

        // keep the shipped demo organizer in the pool too
        $demo = User::where('email', 'demoorganizer@saasmonks.in')->value('id');
        if ($demo) {
            array_unshift($ids, $demo);
        }

        return array_values(array_unique($ids));
    }

    /** @return int[] scanner user ids */
    private function scanners(array $organizers): array
    {
        $ids = [];
        foreach (['Gate A' => 'scanner.gatea@example.com', 'Gate B' => 'scanner.gateb@example.com'] as $name => $email) {
            [$first, $last] = explode(' ', $name);
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'first_name' => $first,
                    'last_name'  => $last,
                    'password'   => Hash::make('password'),
                    'status'     => 1,
                    'is_verify'  => 1,
                    'org_id'     => $organizers[0],
                    'image'      => $this->img,
                ]
            );
            if (! $user->hasRole('scanner')) {
                $user->assignRole('scanner');
            }
            $ids[] = $user->id;
        }

        $demo = User::where('email', 'democustomer@saasmonks.in')->value('id');
        if ($demo) {
            $ids[] = $demo;
        }

        return array_values(array_unique($ids));
    }

    /** @return int[] app_user ids */
    private function customers(): array
    {
        $ids = [];
        $people = [
            ['name' => 'Aarav',  'last_name' => 'Sharma',  'email' => 'aarav.sharma@example.com'],
            ['name' => 'Diya',   'last_name' => 'Patel',   'email' => 'diya.patel@example.com'],
            ['name' => 'Kabir',  'last_name' => 'Mehta',   'email' => 'kabir.mehta@example.com'],
            ['name' => 'Ananya', 'last_name' => 'Rao',     'email' => 'ananya.rao@example.com'],
            ['name' => 'Vivaan', 'last_name' => 'Nair',    'email' => 'vivaan.nair@example.com'],
        ];
        foreach ($people as $p) {
            $u = AppUser::firstOrCreate(
                ['email' => $p['email']],
                array_merge($p, [
                    'password'          => Hash::make('password'),
                    'provider'          => 'local',
                    'status'            => 1,
                    'is_verify'         => 1,
                    'image'             => 'defaultuser.png',
                    'phone'             => '80000' . rand(10000, 99999),
                    'address'           => 'Test address, India',
                    'email_verified_at' => now(),
                ])
            );
            $ids[] = $u->id;
        }

        return $ids;
    }

    /** @return int[] category ids */
    private function categories(): array
    {
        foreach (['Music', 'Business', 'Sports', 'Food & Drink', 'Technology'] as $name) {
            Category::firstOrCreate(['name' => $name], ['status' => 1, 'image' => $this->img]);
        }

        return Category::where('status', 1)->pluck('id')->all();
    }

    /** @return \App\Models\Event[] */
    private function events(array $organizers, array $scanners, array $categories, array $cities): array
    {
        if (Event::count() > 0) {
            return Event::orderBy('id')->get()->all();
        }

        $scannerCsv = implode(',', $scanners);
        $blueprints = [
            ['name' => 'Indie Music Fest',        'type' => 'offline', 'featured' => 3, 'days' => 7,  'lat' => '22.7196', 'lang' => '75.8577'],
            ['name' => 'Startup Growth Summit',   'type' => 'offline', 'featured' => 5, 'days' => 14, 'lat' => '19.0760', 'lang' => '72.8777'],
            ['name' => 'City Marathon 2026',      'type' => 'offline', 'featured' => 0, 'days' => 21, 'lat' => '17.3850', 'lang' => '78.4867'],
            ['name' => 'Cloud & AI Conference',   'type' => 'online',  'featured' => 4, 'days' => 10, 'lat' => null,      'lang' => null],
            ['name' => 'Street Food Carnival',    'type' => 'offline', 'featured' => 0, 'days' => 5,  'lat' => '13.0827', 'lang' => '80.2707'],
            ['name' => 'Design Systems Workshop',  'type' => 'online',  'featured' => 0, 'days' => 3,  'lat' => null,      'lang' => null],
            ['name' => 'Jazz Night Live',         'type' => 'offline', 'featured' => 1, 'days' => 30, 'lat' => '22.7196', 'lang' => '75.8577'],
            ['name' => 'Photography Bootcamp',    'type' => 'offline', 'featured' => 0, 'days' => 45, 'lat' => '19.0760', 'lang' => '72.8777'],
        ];

        $events = [];
        foreach ($blueprints as $i => $b) {
            $start = Carbon::now()->addDays($b['days'])->setTime(10, 0);
            $events[] = Event::create([
                'name'           => $b['name'],
                'type'           => $b['type'],
                'url'            => $b['type'] === 'online' ? 'https://example.com/live/' . ($i + 1) : null,
                'user_id'        => $organizers[$i % count($organizers)],
                'scanner_id'     => $b['type'] === 'offline' ? $scannerCsv : null,
                'address'        => $b['type'] === 'offline' ? 'Convention Centre, India' : null,
                'category_id'    => $categories[$i % count($categories)],
                'city_id'        => $cities[$i % count($cities)],
                'start_time'     => $start,
                'end_time'       => (clone $start)->addHours(6),
                'image'          => $this->img,
                'gallery'        => '',
                'people'         => [200, 500, 1000, 300][$i % 4],
                'lat'            => $b['lat'],
                'lang'           => $b['lang'],
                'description'    => '<p>' . $b['name'] . ' is a sample event created for testing the admin panel and the public site. It covers registration, ticketing, coupons and check-in flows.</p>',
                'security'       => 1,
                'status'         => 1,
                'is_featured'    => $b['featured'] > 0 ? 1 : 0,
                'featured_order' => $b['featured'],
                'event_status'   => 'Pending',
                'is_deleted'     => 0,
                'tags'           => 'sample, test, ' . strtolower(explode(' ', $b['name'])[0]),
            ]);
        }

        return $events;
    }

    /** @return \App\Models\Ticket[] one representative ticket per event */
    private function tickets(array $events): array
    {
        $tickets = [];
        foreach ($events as $event) {
            if (Ticket::where('event_id', $event->id)->exists()) {
                $tickets[$event->id] = Ticket::where('event_id', $event->id)->first();
                continue;
            }

            $specs = [
                ['name' => 'General Admission', 'type' => 'paid', 'price' => 499,  'quantity' => 300],
                ['name' => 'VIP Pass',          'type' => 'paid', 'price' => 1499, 'quantity' => 50],
                ['name' => 'Free Entry',        'type' => 'free', 'price' => 0,    'quantity' => 100],
            ];
            foreach ($specs as $s) {
                $t = Ticket::create([
                    'event_id'         => $event->id,
                    'user_id'          => $event->user_id,
                    'ticket_number'    => chr(rand(65, 90)) . chr(rand(65, 90)) . '-' . rand(1000, 9999),
                    'name'             => $s['name'],
                    'type'             => $s['type'],
                    'allday'           => 1,
                    'maximum_checkins' => 1,
                    'quantity'         => $s['quantity'],
                    'ticket_per_order' => 5,
                    'start_time'       => Carbon::now()->format('Y-m-d H:i:s'),
                    'end_time'         => Carbon::parse($event->end_time)->format('Y-m-d H:i:s'),
                    'price'            => $s['price'],
                    'description'      => $s['name'] . ' ticket for ' . $event->name,
                    'status'           => 1,
                    'is_deleted'       => 0,
                ]);
                $tickets[$event->id] ??= $t;
            }
        }

        return $tickets;
    }

    private function eventFaqs(array $events): void
    {
        foreach ($events as $event) {
            if (EventFaq::where('event_id', $event->id)->exists()) {
                continue;
            }
            $qa = [
                ['q' => 'Where is the venue?',            'a' => 'Full venue details are on the event page and your ticket email.'],
                ['q' => 'Can I get a refund?',            'a' => 'Refunds are available up to 48 hours before the event start time.'],
                ['q' => 'Is re-entry allowed?',           'a' => 'Yes, your ticket QR can be scanned once per check-in.'],
            ];
            foreach ($qa as $item) {
                EventFaq::create([
                    'event_id' => $event->id,
                    'question' => $item['q'],
                    'answer'   => $item['a'],
                ]);
            }
        }
    }

    private function coupons(array $events, array $organizers): void
    {
        foreach ($events as $i => $event) {
            if ($i % 2 !== 0) {
                continue; // coupons on every other event
            }
            $code = 'SAVE' . (10 + $i * 5);
            Coupon::firstOrCreate(
                ['coupon_code' => $code, 'event_id' => $event->id],
                [
                    'user_id'          => $event->user_id,
                    'name'             => $event->name . ' launch offer',
                    'discount_type'    => 0,
                    'discount'         => 10 + $i,
                    'minimum_amount'   => 200,
                    'maximum_discount' => 500,
                    'description'      => 'Sample coupon for testing checkout.',
                    'start_date'       => Carbon::now()->format('Y-m-d'),
                    'end_date'         => Carbon::now()->addMonths(2)->format('Y-m-d'),
                    'max_use'          => 100,
                    'use_count'        => 0,
                    'max_use_per_user' => 2,
                    'status'           => 1,
                ]
            );
        }
    }

    private function banners(array $events): void
    {
        if (Banner::count() > 0) {
            return;
        }
        foreach (array_slice($events, 0, 3) as $event) {
            Banner::create([
                'title'    => $event->name,
                'event_id' => $event->id,
                'image'    => $this->img,
                'status'   => 1,
            ]);
        }
    }

    private function blogs(array $categories): void
    {
        if (Blog::count() > 0) {
            return;
        }
        $titles = [
            'How to plan a memorable music festival',
            '5 ticketing mistakes event organizers make',
            'A guide to hybrid and online events in 2026',
            'Marketing your event on a small budget',
            'Check-in day: a stress-free operations checklist',
        ];
        foreach ($titles as $i => $title) {
            Blog::create([
                'category_id' => $categories[$i % count($categories)],
                'title'       => $title,
                'description' => '<p>' . $title . '. This is placeholder blog content used for testing the blog list, detail page and admin editor.</p>',
                'image'       => $this->img,
                'tags'        => 'events, tips, sample',
                'status'      => 1,
            ]);
        }
    }

    private function feedback(array $customers): void
    {
        if (Feedback::count() > 0) {
            return;
        }
        $messages = [
            'Great platform, booking was smooth!',
            'Would love a dark mode on the site.',
            'The check-in scanner worked perfectly at our event.',
            'Please add more payment options.',
            'Support team responded quickly. Thanks!',
        ];
        foreach ($messages as $i => $msg) {
            Feedback::create([
                'user_id' => $customers[$i % count($customers)],
                'email'   => 'feedback' . ($i + 1) . '@example.com',
                'message' => $msg,
                'rate'    => rand(3, 5),
                'image'   => null,
            ]);
        }
    }

    private function faqs(): void
    {
        if (Faq::count() > 0) {
            return;
        }
        $qa = [
            ['q' => 'How do I create an event?',           'a' => 'Log in as an organizer, open Events and click Add New.'],
            ['q' => 'How are payouts handled?',            'a' => 'Organizer payouts are managed from the Settlements section.'],
            ['q' => 'Can I sell free tickets?',            'a' => 'Yes, set the ticket type to Free while creating a ticket.'],
            ['q' => 'How do coupons work?',                'a' => 'Create a coupon under an event with a code, discount and validity window.'],
            ['q' => 'Is there a mobile app?',              'a' => 'Yes, apps are available for Android and iOS.'],
            ['q' => 'How do I contact support?',           'a' => 'Use the Contact page or email the support address in settings.'],
        ];
        foreach ($qa as $item) {
            Faq::create(['question' => $item['q'], 'answer' => $item['a'], 'status' => 1]);
        }
    }

    private function taxes(array $organizers): void
    {
        if (Tax::count() > 0) {
            return;
        }
        Tax::create(['user_id' => $organizers[0], 'name' => 'GST', 'price' => 18, 'amount_type' => 'percentage', 'allow_all_bill' => 1, 'status' => 1]);
        Tax::create(['user_id' => $organizers[0], 'name' => 'Booking Fee', 'price' => 25, 'amount_type' => 'fixed', 'allow_all_bill' => 0, 'status' => 1]);
    }

    private function orders(array $events, array $tickets, array $customers): void
    {
        if (Order::count() > 0) {
            return;
        }

        $statuses = [
            ['order_status' => 'Complete', 'payment_status' => 1, 'payment_type' => 'LOCAL'],
            ['order_status' => 'Pending',  'payment_status' => 0, 'payment_type' => 'LOCAL'],
            ['order_status' => 'Complete', 'payment_status' => 1, 'payment_type' => 'WALLET'],
            ['order_status' => 'Cancel',   'payment_status' => 0, 'payment_type' => 'LOCAL'],
        ];

        foreach ($events as $i => $event) {
            $ticket = $tickets[$event->id] ?? null;
            if (! $ticket) {
                continue;
            }
            $customer = $customers[$i % count($customers)];
            $s = $statuses[$i % count($statuses)];
            $qty = rand(1, 4);
            $payment = $ticket->price * $qty;

            $order = Order::create([
                'order_id'        => '#' . rand(10000, 99999),
                'customer_id'     => $customer,
                'organization_id' => $event->user_id,
                'event_id'        => $event->id,
                'ticket_id'       => $ticket->id,
                'quantity'        => $qty,
                'coupon_discount' => 0,
                'ticket_date'     => Carbon::parse($event->start_time),
                'tax'             => 0,
                'org_commission'  => (int) round($payment * 0.1),
                'payment'         => $payment,
                'payment_type'    => $s['payment_type'],
                'payment_status'  => $s['payment_status'],
                'order_status'    => $s['order_status'],
                'org_pay_status'  => 0,
            ]);

            for ($n = 1; $n <= $qty; $n++) {
                OrderChild::create([
                    'ticket_id'     => $ticket->id,
                    'order_id'      => $order->id,
                    'customer_id'   => $customer,
                    'ticket_number' => strtoupper(uniqid()),
                    'status'        => 1,
                    'checkin'       => 0,
                    'paid'          => $s['payment_status'],
                ]);
            }

            // a review for completed orders
            if ($s['order_status'] === 'Complete') {
                Review::firstOrCreate(
                    ['order_id' => $order->id, 'event_id' => $event->id],
                    [
                        'user_id'         => $customer,
                        'organization_id' => $event->user_id,
                        'message'         => 'Really enjoyed ' . $event->name . '. Well organized!',
                        'rate'            => rand(3, 5),
                        'status'          => 1,
                    ]
                );
            }
        }
    }

    private function membershipPlans(): void
    {
        $features = [];
        foreach (['Message organizers', 'Access secret parties', 'Free cancellation', 'Priority support', 'Early ticket access'] as $name) {
            $features[$name] = MembershipFeature::firstOrCreate(['name' => $name])->id;
        }

        $plans = [
            [
                'name' => 'Silver', 'amount' => 9.99, 'price' => 9.99, 'duration_days' => 30, 'sort_order' => 1,
                'description' => 'A light touch of perks for regular party-goers.',
                'features' => ['Message organizers'],
            ],
            [
                'name' => 'Gold', 'amount' => 24.99, 'price' => 19.99, 'duration_days' => 30, 'sort_order' => 2,
                'description' => 'More access, better deals, faster support.',
                'features' => ['Message organizers', 'Free cancellation', 'Early ticket access'],
            ],
            [
                'name' => 'Platinum', 'amount' => 49.99, 'price' => 39.99, 'duration_days' => 90, 'sort_order' => 3,
                'description' => 'Serious perks for members who party often.',
                'features' => ['Message organizers', 'Free cancellation', 'Early ticket access', 'Access secret parties'],
            ],
            [
                'name' => 'Diamond', 'amount' => 99.99, 'price' => 79.99, 'duration_days' => 365, 'sort_order' => 4,
                'description' => 'The full experience - every perk, all year.',
                'features' => ['Message organizers', 'Free cancellation', 'Early ticket access', 'Access secret parties', 'Priority support'],
            ],
        ];

        foreach ($plans as $p) {
            $planFeatures = $p['features'];
            unset($p['features']);
            $p['status'] = 1;

            $plan = MembershipPlan::updateOrCreate(['name' => $p['name']], $p);
            $plan->features()->sync(array_map(fn ($name) => $features[$name], $planFeatures));
        }
    }

    /**
     * Downloads a real photo (picsum.photos for scenes, pravatar.cc for
     * avatars) and caches it under a deterministic filename so re-running
     * the seeder never re-downloads. Returns null on any failure so callers
     * can just skip the update and keep whatever image was already there.
     */
    private function realImage(string $seed, bool $avatar = false): ?string
    {
        $hash = substr(md5($seed), 0, 16);
        $filename = 'dummy_' . $hash . '.jpg';
        $path = public_path('images/upload/' . $filename);
        if (file_exists($path)) {
            return $filename;
        }

        $url = $avatar
            ? 'https://i.pravatar.cc/500?u=' . urlencode($seed)
            : 'https://picsum.photos/seed/' . $hash . '/900/600';

        try {
            // verify=false: this only ever fetches public stock photos for
            // local demo data (never anything sensitive), and some local
            // dev PHP installs ship a broken/relative curl.cainfo path that
            // would otherwise fail every HTTPS request here.
            $response = Http::withOptions(['verify' => false])->timeout(10)->get($url);
            if ($response->successful() && strlen($response->body()) > 0) {
                file_put_contents($path, $response->body());
                return $filename;
            }
        } catch (\Throwable $e) {
            $this->command?->warn("Could not download dummy image for '{$seed}': " . $e->getMessage());
        }

        return null;
    }

    /**
     * Replaces the generic default.png/defaultuser.png placeholder with a
     * real photo, but ONLY for the exact records this seeder itself
     * creates (matched by the same emails/names used above) - never a
     * broad "any row still using default.png" scan, so real accounts that
     * simply haven't uploaded a photo yet are never touched.
     */
    private function attachRealImages(): void
    {
        $organizerEmails = ['nova.events@example.com', 'skyline.shows@example.com', 'pulse.live@example.com'];
        foreach (User::whereIn('email', $organizerEmails)->get() as $u) {
            if ($img = $this->realImage('organizer-' . $u->email, true)) {
                $u->update(['image' => $img]);
            }
        }

        $scannerEmails = ['scanner.gatea@example.com', 'scanner.gateb@example.com'];
        foreach (User::whereIn('email', $scannerEmails)->get() as $u) {
            if ($img = $this->realImage('scanner-' . $u->email, true)) {
                $u->update(['image' => $img]);
            }
        }

        $customerEmails = ['aarav.sharma@example.com', 'diya.patel@example.com', 'kabir.mehta@example.com', 'ananya.rao@example.com', 'vivaan.nair@example.com'];
        foreach (AppUser::whereIn('email', $customerEmails)->get() as $u) {
            if ($img = $this->realImage('customer-' . $u->email, true)) {
                $u->update(['image' => $img]);
            }
        }

        $categoryNames = ['Music', 'Business', 'Sports', 'Food & Drink', 'Technology'];
        foreach (Category::whereIn('name', $categoryNames)->get() as $c) {
            if ($img = $this->realImage('category-' . $c->name)) {
                $c->update(['image' => $img]);
            }
        }

        $eventNames = ['Indie Music Fest', 'Startup Growth Summit', 'City Marathon 2026', 'Cloud & AI Conference', 'Street Food Carnival', 'Design Systems Workshop', 'Jazz Night Live', 'Photography Bootcamp'];
        foreach (Event::whereIn('name', $eventNames)->get() as $e) {
            if ($img = $this->realImage('event-' . $e->name)) {
                $e->update(['image' => $img]);
            }
        }

        $blogTitles = [
            'How to plan a memorable music festival',
            '5 ticketing mistakes event organizers make',
            'A guide to hybrid and online events in 2026',
            'Marketing your event on a small budget',
            'Check-in day: a stress-free operations checklist',
        ];
        foreach (Blog::whereIn('title', $blogTitles)->get() as $b) {
            if ($img = $this->realImage('blog-' . $b->title)) {
                $b->update(['image' => $img]);
            }
        }

        foreach (Banner::whereIn('title', $eventNames)->get() as $banner) {
            if ($img = $this->realImage('banner-' . $banner->title)) {
                $banner->update(['image' => $img]);
            }
        }

        $planNames = ['Free', 'Silver', 'Gold', 'Platinum', 'Diamond'];
        foreach (MembershipPlan::whereIn('name', $planNames)->get() as $plan) {
            if ($img = $this->realImage('plan-' . $plan->name)) {
                $plan->update(['image' => $img]);
            }
        }
    }
}
