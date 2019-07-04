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
    public function boot()
    {

    }

    public function register()
    {
        $this->app->singleton('SolarCode', function(){
            return new SolarCode();
        });

        class_alias ( '\James\SolarCode\Facades\SolarCode' , 'SolarCode');
    }
}