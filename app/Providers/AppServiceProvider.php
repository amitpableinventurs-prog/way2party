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
        view()->composer('*', function ($view) {
            $currency = null;
            if (config('database.connections.' . config('database.default') . '.database')) {
                $setting = Setting::find(1);
                if ($setting && $setting->currency) {
                    $currency = optional(Currency::where('code', $setting->currency)->first())->symbol;
                }
            }
            $view->with('currency', $currency ?? '$');
        });

        // Scoped to the root layout (rendered once per request) so addImage() below never
        // appends the same fallback image twice across nested partial views.
        view()->composer('frontend.master', function () {
            if (!config('database.connections.' . config('database.default') . '.database')) {
                return;
            }
            $setting = Setting::find(1);
            if ($setting && $setting->logo) {
                $logoUrl = ($setting->imagePath ?? url('images/upload/')) . $setting->logo;
                OpenGraph::setSiteName($setting->app_name)->addImage($logoUrl);
                TwitterCard::setImage($logoUrl);
            }
        });
    }
}
