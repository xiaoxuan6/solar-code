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

use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Contracts\Config\Repository;
use James\SolarCode\Exception\ErrorException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\{Cache, Storage};
use Illuminate\Contracts\Routing\ResponseFactory;
use GuzzleHttp\Exception\{GuzzleException, RequestException};

class SolarCode
{
    public $config;
    private $path;
    protected static $client;

    public const ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
    public const WXACODEUNLIMIT_URL = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=';
    public const WXACODE_URL = 'https://api.weixin.qq.com/wxa/getwxacode?access_token=';
    public const CREATEWXAQRCODE_URL = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=';

    /**
     * SolarCode constructor.
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * @return Client
     */
    public static function getClient(): Client
    {
        if (empty(self::$client)) {
            self::$client = new Client(['verify' => false, 'timeout' => 30]);
        }

        return self::$client;
    }

    /**
     * Notes: 获取 access_token
     * Date: 2020/8/13 13:52
     * @param null $appid
     * @param null $secret
     * @return mixed
     * @throws ErrorException
     */
    public function getAccessToken($appid = null, $secret = null)
    {
        $prefix = $this->config->get('solar_code.token_prefix');
        if (Cache::has($prefix)) {
            return Cache::get($prefix);
        }

        if (! $appid || ! $secret) {
            $appid = $this->config->get('solar_code.app_id');
            $secret = $this->config->get('solar_code.app_secret');
        }

        $url = sprintf(self::ACCESS_TOKEN_URL, $appid, $secret);

        try {
            $response = self::getClient()->get($url);

            $result = json_decode($response->getBody()->getContents(), 1);
            if (isset($result['errcode']) && $result['errcode'] != 0) {
                throw new ErrorException("response: {$result['errmsg']}");
            }

            $access_token = $result['access_token'];
        } catch (RequestException $exception) {
            throw new ErrorException('invalid access token');
        }

        if (app()->version() < 5.8) {
            $expires_time = $result['expires_in'] / 60;
        } else {
            $expires_time = $result['expires_in'];
        }

        $expires_time = $this->config->get('solar_code.token_expires_time') ?: $expires_time;

        Cache::put($prefix, $access_token, $expires_time);

        return $access_token;
    }

    /**
     * Notes: 二维码生成 A类  适用于需要的码数量较少的业务场景
     * Date: 2019/7/4 15:21
     * @param string $path 不能为空，最大长度 128 字节
     * @param bool $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array $line_color 二维码的线条颜色
     * @param int $width 二维码的宽度
     * @throws GuzzleException|ErrorException
     */
    public function getWxcode(string $path = '', int $width = 430, bool $auto_color = false, array $line_color = ['r' => '0', 'g' => '0', 'b' => '0']): SolarCode
    {
        if (! $path) {
            throw new ErrorException('参数：Path 必填！');
        }

        $url = self::WXACODE_URL . $this->getAccessToken();

        $params = [
            'path' => $path,
            'width' => $width,
            'auto_color' => $auto_color,
            'line_color' => $line_color, //文档中是json对象，在代码中就传数组
            'is_hyaline' => false,
        ];

        $response = self::getClient()->request('POST', $url, [
            'body' => json_encode($params)
        ]);

        $this->path = $response->getBody()->getContents();

        return $this;
    }

    /**
     * Notes: 二维码生成 B类  适用于需要的码数量极多，或仅临时使用的业务场景
     * Date: 2019/7/4 14:49
     * @param string $scene 最大32个可见字符，只支持数字，大小写英文以及部分特殊字
     * @param string $page 不能为空，最大长度 128 字节
     * @param bool $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array $line_color 二维码的线条颜色
     * @param int $width 二维码的宽度
     * @throws GuzzleException|ErrorException
     */
    public function getWxcodeunlimit(string $page = '', string $scene = '', int $width = 430, bool $auto_color = false, array $line_color = ['r' => '0', 'g' => '0', 'b' => '0']): SolarCode
    {
        if (! $page || ! $scene) {
            throw new ErrorException('参数：Page、Scene 必填！');
        }

        $url = self::WXACODEUNLIMIT_URL . $this->getAccessToken();

        $params = [
            'scene' => $scene,
            'page' => ltrim($page, '/'),
            'width' => $width,
            'auto_color' => $auto_color,
            'line_color' => $line_color, //文档中是json对象，在代码中就传数组
            'is_hyaline' => false,
        ];

        $response = self::getClient()->request('POST', $url, [
            'body' => json_encode($params)
        ]);

        $this->path = $response->getBody()->getContents();

        return $this;
    }

    /**
     * Notes: 二维码生成 C类  适用于需要的码数量较少的业务场景
     * Date: 2019/7/8 14:47
     * @param string $path 不能为空，最大长度 128 字节
     * @param int $width 二维码的宽度
     * @return $this
     * @throws ErrorException
     * @throws GuzzleException
     */
    public function createWxaqrcode(string $path = '', int $width = 430): SolarCode
    {
        if (! $path) {
            throw new ErrorException('参数：Path 必填！');
        }

        $url = self::CREATEWXAQRCODE_URL . $this->getAccessToken();

        $params = [
            'path' => $path,
            'width' => $width,
        ];

        $response = self::getClient()->request('POST', $url, [
            'body' => json_encode($params)
        ]);

        $this->path = $response->getBody()->getContents();

        return $this;
    }

    /**
     * Notes: 返回微信响应
     * Date: 2019/7/4 18:42
     * @return mixed
     */
    public function response()
    {
        return $this->path;
    }

    /**
     * Notes: 生成图片--不保存
     * Date: 2019/7/8 14:20
     * @return Application|ResponseFactory|Response
     */
    public function image()
    {
        return response($this->path)->header('Content-Type', 'image/jpeg');
    }

    /**
     * Notes: 生成图片--保存
     * Date: 2019/7/4 18:13
     * @param string $filePath
     * @param string $fileName
     * @param string $disk
     * @return string
     */
    public function imagePath(string $filePath = '', string $fileName = '', string $disk = 'public'): string
    {
        if (! $fileName) {
            $fileName = date('Ymd') . uniqid() . '.png';
        }

        $imageSrc = $filePath . DIRECTORY_SEPARATOR . $fileName;

        Storage::disk($disk)->put($imageSrc, base64_decode(base64_encode($this->path)));

        return $filePath ? Storage::disk($disk)->url($imageSrc) : Storage::disk($disk)->url($fileName);
    }

    /**
     * Notes: 生成 base64
     * Date: 2019/7/10 13:57
     * @return string
     */
    public function base64(): string
    {
        return 'data:image/png;base64,' . base64_encode($this->path);
    }
}
