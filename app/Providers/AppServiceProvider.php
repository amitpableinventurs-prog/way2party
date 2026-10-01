<?php

namespace App\Providers;

use \App\Models\Setting;
use \App\Models\Currency;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
        // Runs for every view and partial, so resolve the symbol once per request
        // instead of 2 queries per rendered view.
        $currencySymbol = null;
        view()->composer('*', function ($view) use (&$currencySymbol) {
            if ($currencySymbol === null) {
                $currency = null;
                if (config('database.connections.' . config('database.default') . '.database')) {
                    $setting = Setting::current();
                    if ($setting && $setting->currency) {
                        $currency = optional(Currency::where('code', $setting->currency)->first())->symbol;
                    }
                }
                $currencySymbol = $currency ?? '$';
            }
            $view->with('currency', $currencySymbol);
        });

        // Scoped to the root layout (rendered once per request) so addImage() below never
        // appends the same fallback image twice across nested partial views.
        view()->composer('frontend.master', function () {
            if (!config('database.connections.' . config('database.default') . '.database')) {
                return;
            }
            $setting = Setting::current();
            if ($setting && $setting->logo) {
                $logoUrl = ($setting->imagePath ?? url('images/upload/')) . $setting->logo;
                OpenGraph::setSiteName($setting->app_name)->addImage($logoUrl);
                TwitterCard::setImage($logoUrl);
            }
        });
    }
}
