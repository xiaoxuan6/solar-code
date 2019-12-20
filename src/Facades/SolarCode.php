<?php
/**
 * Created by PhpStorm.
 * User: james.xue
 * Date: 2019/7/4
 * Time: 14:26
 */

namespace James\SolarCode\Facades;

use Illuminate\Support\Facades\Facade;

class SolarCode extends Facade
{
	/**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function getFacadeAccessor()
    {
        return "SolarCode";
    }
}