<?php
/**
 * Created by PhpStorm.
 * User: james.xue
 * Date: 2019/7/4
 * Time: 14:26
 */

namespace James\SolarCode;

use Illuminate\Support\ServiceProvider;

class SolarCodeServiceProvider extends ServiceProvider
{

	/**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;


	/**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('SolarCode', function(){
            return new SolarCode();
        });

        $this->app->alias(SolarCode::class, "SolarCode");
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
    	return ["SolarCode"];
    }
}