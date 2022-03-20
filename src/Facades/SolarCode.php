<?php
/**
 * This file is part of PHP CS Fixer.
 *
 * (c) vinhson <15227736751@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace James\SolarCode\Facades;

use RuntimeException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Facade;

/**
 * Class SolarCode
 *
 * @method static Client getClient()
 * @method static string getAccessToken($appid = null, $secret = null)
 * @method static \James\SolarCode\SolarCode getWxcode(string $path = '', int $width = 430, bool $auto_color = false, array $line_color = ['r' => '0', 'g' => '0', 'b' => '0'])
 * @method static \James\SolarCode\SolarCode getWxcodeunlimit(string $page = '', string $scene = '', int $width = 430, bool $auto_color = false, array $line_color = ['r' => '0', 'g' => '0', 'b' => '0'])
 * @method static \James\SolarCode\SolarCode createWxaqrcode(string $path = '', int $width = 430)
 *
 * @package James\SolarCode\Facades
 */
class SolarCode extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'SolarCode';
    }
}
