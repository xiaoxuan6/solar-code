<?php
/**
 * Created by PhpStorm.
 * User: james.xue
 * Date: 2019/7/4
 * Time: 14:30
 */

namespace James\SolarCode;

use GuzzleHttp\Client;
use James\SolarCode\Exception\ErrorException;

class SolarCode
{
    const URLA = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=";
    const URLB = "https://api.weixin.qq.com/wxa/getwxacode?access_token=";

    /**
     * Notes: 二维码生成 A类
     * Date: 2019/7/4 14:49
     * @param $token            小程序的token
     * @param $scene            最大32个可见字符，只支持数字，大小写英文以及部分特殊字
     * @param $path             不能为空，最大长度 128 字节
     * @param bool $auto_color  自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array $line_color 二维码的线条颜色
     * @param int $width        二维码的宽度
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getCode($token = '', $path = '', $scene = '', $auto_color = false, $width = 430, $line_color = ['r' => '0', 'g' =>'0', 'b' =>'0'])
    {
        if(!$token || !$path || $scene)
            throw new ErrorException("参数：Token、Path、Scene 必填！");

        $url = self::URLA.$token;

        $params = [
            'scene'=> $scene,
            'path'=> $path,
            'width' => $width,
            'auto_color' => $auto_color,
            'line_color'=> $line_color, //文档中是json对象，在代码中就传数组
            'is_hyaline' => false,
        ];

        $client = new Client(['verify' => false]);
        $response = $client->request('POST', $url, [
            'body' => json_encode($params)
        ]);
        return json_decode($response->getBody()->getContents(), 1);
    }

    /**
     * Notes: 二维码生成 B类
     * Date: 2019/7/4 15:21
     * @param $token            小程序的token
     * @param $scene            最大32个可见字符，只支持数字，大小写英文以及部分特殊字
     * @param $path             不能为空，最大长度 128 字节
     * @param bool $auto_color  自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array $line_color 二维码的线条颜色
     * @param int $width        二维码的宽度
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getCodePath($token = '', $path = '', $auto_color = false, $width = 430, $line_color = ['r' => '0', 'g' =>'0', 'b' =>'0'])
    {
        if(!$token || !$path )
            throw new ErrorException("参数：Token、Path 必填！");

        $url = self::URLB.$token;

        $params = [
            'path'=> $path,
            'width' => $width,
            'auto_color' => $auto_color,
            'line_color'=> $line_color, //文档中是json对象，在代码中就传数组
            'is_hyaline' => false,
        ];

        $client = new Client(['verify' => false]);
        $response = $client->request('POST', $url, [
            'body' => json_encode($params)
        ]);
        return json_decode($response->getBody()->getContents(), 1);
    }

}