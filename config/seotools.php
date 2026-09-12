<?php
/**
 * @see https://github.com/artesaos/seotools
 */

return [
    'meta' => [
        /*
         * The default configurations to be used by the meta generator.
         */
        'defaults'       => [
            'title'        => false, // left falsy so master.blade.php can detect "no controller title set" and fall back to the page's @section('title')
            'titleBefore'  => false, // Put defaults.title before page title, like 'It's Over 9000! - Dashboard'
            'description'  => 'Discover, book and manage events with ' . env('APP_NAME', 'EventRight Pro') . '.', // fallback description so pages are never shipped without one
            'separator'    => ' - ',
            'keywords'     => [],
            'canonical'    => null, // Set null for using Url::current(), set false to total remove
            'robots'       => false, // Set to 'all', 'none' or any combination of index/noindex and follow/nofollow
        ],
        /*
         * Webmaster tags are always added.
         */
        'webmaster_tags' => [
            'google'    => null,
            'bing'      => null,
            'alexa'     => null,
            'pinterest' => null,
            'yandex'    => null,
            'norton'    => null,
        ],

        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        /*
         * The default configurations to be used by the opengraph generator.
         */
        'defaults' => [
            // Only used as a fallback when a controller doesn't call OpenGraph::setTitle()/setDescription()
            // (setupDefaults() in the package never overwrites a value already set for the current page).
            'title'       => env('APP_NAME', 'EventRight Pro'),
            'description' => env('APP_NAME', 'EventRight Pro') . ' - discover, book and manage events online.',
            'url'         => null, // Set null for using Url::current(), set false to total remove
            'type'        => 'website',
            'site_name'   => false, // populated per-request from Setting in AppServiceProvider
            'images'      => [],
        ],
    ],
    'twitter' => [
        /*
         * The default values to be used by the twitter cards generator.
         */
        'defaults' => [
            'card' => 'summary_large_image',
        ],
    ],
    'json-ld' => [
        /*
         * The default configurations to be used by the json-ld generator.
         */
        'defaults' => [
            'title'       => false, // set false to total remove -- avoid shipping placeholder data on pages that don't set their own
            'description' => false, // set false to total remove
            'url'         => null, // Set null for using Url::current(), set false to total remove
            'type'        => 'WebPage',
            'images'      => [],
        ],
    ],
];
