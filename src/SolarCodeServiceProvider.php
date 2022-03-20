<?php
/**
 * This file is part of PHP CS Fixer.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace James\SolarCode;

use Illuminate\Support\ServiceProvider;

class SolarCodeServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('SolarCode', function ($app) {
            return new SolarCode($app['config']);
        });

        $this->app->alias(SolarCode::class, 'SolarCode');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['SolarCode'];
    }
}
