<?php
/**
 * Created by PhpStorm.
 * User: james.xue
 * Date: 2019/7/4
 * Time: 14:30
 */

namespace James\SolarCode;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use James\SolarCode\Exception\ErrorException;

class SolarCode
{
    const WXACODEUNLIMIT_URL = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=";
    const WXACODE_URL= "https://api.weixin.qq.com/wxa/getwxacode?access_token=";

    private $path;

    /**
     * Notes: 二维码生成 A类
     * Date: 2019/7/4 15:21
     * @param $token            小程序的token
     * @param $scene            最大32个可见字符，只支持数字，大小写英文以及部分特殊字
     * @param $path             不能为空，最大长度 128 字节
     * @param bool $auto_color  自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array $line_color 二维码的线条颜色
     * @param int $width        二维码的宽度
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWxcode($token = '', $path = '', $width = 430, $auto_color = false, $line_color = ['r' => '0', 'g' =>'0', 'b' =>'0'])
    {
        if(!$token || !$path )
            throw new ErrorException("参数：Token、Path 必填！");

        $url = self::WXACODE_URL.$token;

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

        $this->path = $response->getBody()->getContents();
        return $this;
    }

    /**
     * Notes: 二维码生成 B类
     * Date: 2019/7/4 14:49
     * @param $token            小程序的token
     * @param $scene            最大32个可见字符，只支持数字，大小写英文以及部分特殊字
     * @param $path             不能为空，最大长度 128 字节
     * @param bool $auto_color  自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array $line_color 二维码的线条颜色
     * @param int $width        二维码的宽度
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWxcodeunlimit($token = '', $path = '', $scene = '', $width = 430, $auto_color = false, $line_color = ['r' => '0', 'g' =>'0', 'b' =>'0'])
    {
        if(!$token || !$path || !$scene)
            throw new ErrorException("参数：Token、Path、Scene 必填！");

        $url = self::WXACODEUNLIMIT_URL.$token;

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

        $this->path = $response->getBody()->getContents();
        return $this;
    }

    /**
     * Notes: 生成图片--保存
     * Date: 2019/7/4 18:13
     * @param string $disk
     * @param string $fileName
     * @param string $filePath
     */
    public function imagePath($filePath = '', $fileName = '', $disk = 'public')
    {
        if(!$fileName)
            $fileName = date('Ymd').uniqid().'.png';

        $data = 'image/png;base64,'.base64_encode($this->path);

        if (strstr($data,",")){
            $image = explode(',',$data);
            $image = $image[1];
        }

        $imageSrc = $filePath . DIRECTORY_SEPARATOR . $fileName;

        Storage::disk($disk)->put($imageSrc, base64_decode($image));

        return $filePath ? Storage::disk($disk)->url($imageSrc)  : Storage::disk($disk)->url($fileName);
    }

    /**
     * Notes: 返回微信响应
     * Date: 2019/7/4 18:42
     * @return mixed
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * Notes: 生成图片--不保存
     * Date: 2019/7/8 14:20
     * @return mixed
     */
    public function image()
    {
        return response($this->path)->header('Content-Type', 'image/jpeg');
    }
}