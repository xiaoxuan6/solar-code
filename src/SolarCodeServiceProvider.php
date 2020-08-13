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
    protected $defer = true;

    public function register()
    {
        $this->app->singleton('SolarCode', function () {
            return new SolarCode();
        });
    }
}