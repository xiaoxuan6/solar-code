<?php
/**
 * Created by PhpStorm.
 * User: james.xue
 * Date: 2019/7/4
 * Time: 14:27
 */

namespace James\SolarCode\Facades;

use Illuminate\Support\Facades\Facade;

class SolarCode extends Facade
{
    public static function getFacadeAccessor()
    {
        return "SolarCode";
    }

}