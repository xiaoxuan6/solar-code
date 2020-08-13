<?php
/**
 * Created by PhpStorm.
 * User: james.xue
 * Date: 2019/7/4
 * Time: 14:30
 */

namespace James\SolarCode;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use James\SolarCode\Exception\ErrorException;

class SolarCode
{
    private $appid;
    private $secret;
    private $token;
    private $path;
    protected $client;

    const CACHE_PREFIX = "solar_access_token";
    const ACCESS_TOKEN_URL = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";
    const WXACODEUNLIMIT_URL = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=";
    const WXACODE_URL = "https://api.weixin.qq.com/wxa/getwxacode?access_token=";
    const CREATEWXAQRCODE_URL = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=";

    /**
     * SolarCode constructor.
     * @param null $token
     * @param null $appid
     * @param null $secret
     */
    public function __construct($token = null, $appid = null, $secret = null, $expires_time = 0)
    {
        $this->client = new Client([
            'verify'  => false,
            'timeout' => 30
        ]);

        if ($token) {
            $this->token = $token;
        }

        if($appid && $secret){
            $this->appid = $appid;
            $this->secret = $secret;

            $this->token = $this->getAccessToken($appid, $secret, $expires_time);
        }
    }

    /**
     * Notes: 获取 access_token
     * Date: 2020/8/13 13:52
     * @param null $appid
     * @param null $secret
     * @param int $expires_in
     * @return mixed
     * @throws ErrorException
     */
    public function getAccessToken($appid = null, $secret = null, $expires_in = 0)
    {
        if (Cache::has(self::CACHE_PREFIX))
            return Cache::get(self::CACHE_PREFIX);

        if (!$appid || !$secret) {
            $appid = $this->appid;
            $secret = $this->secret;
        }

        $url = sprintf(self::ACCESS_TOKEN_URL, $appid, $secret);

        try {
            $response = $this->client->get($url);

            $result = json_decode($response->getBody()->getContents(), 1);
            if (isset($result['errcode']) && $result['errcode'] != 0) {
                throw new ErrorException("response: {$result['errmsg']}");
            }

            $access_token = $result['access_token'];

        } catch (RequestException $exception) {
            throw new ErrorException("invalid access token");
        }

        if (app()->version() < 5.8) {
            $expires_time = $result['expires_in'] / 60;
        } else {
            $expires_time = $result['expires_in'];
        }

        $expires_time = $expires_in ? $expires_in : $expires_time;

        Cache::put(self::CACHE_PREFIX, $access_token, $expires_time);
        return $access_token;
    }

    /**
     * Notes: 二维码生成 A类  适用于需要的码数量较少的业务场景
     * Date: 2019/7/4 15:21
     * @param $path             不能为空，最大长度 128 字节
     * @param bool $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array $line_color 二维码的线条颜色
     * @param int $width 二维码的宽度
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWxcode($path = '', $width = 430, $auto_color = false, $line_color = ['r' => '0', 'g' => '0', 'b' => '0'])
    {
        if (!$path)
            throw new ErrorException("参数：Path 必填！");

        $url = self::WXACODE_URL . $this->token;

        $params = [
            'path'       => $path,
            'width'      => $width,
            'auto_color' => $auto_color,
            'line_color' => $line_color, //文档中是json对象，在代码中就传数组
            'is_hyaline' => false,
        ];

        $response = $this->client->request('POST', $url, [
            'body' => json_encode($params)
        ]);

        $this->path = $response->getBody()->getContents();

        return $this;
    }

    /**
     * Notes: 二维码生成 B类  适用于需要的码数量极多，或仅临时使用的业务场景
     * Date: 2019/7/4 14:49
     * @param $scene            最大32个可见字符，只支持数字，大小写英文以及部分特殊字
     * @param $page             不能为空，最大长度 128 字节
     * @param bool $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array $line_color 二维码的线条颜色
     * @param int $width 二维码的宽度
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWxcodeunlimit($page = '', $scene = '', $width = 430, $auto_color = false, $line_color = ['r' => '0', 'g' => '0', 'b' => '0'])
    {
        if (!$page || !$scene)
            throw new ErrorException("参数：Page、Scene 必填！");

        $url = self::WXACODEUNLIMIT_URL . $this->token;

        $params = [
            'scene'      => $scene,
            'page'       => ltrim($page, "/"),
            'width'      => $width,
            'auto_color' => $auto_color,
            'line_color' => $line_color, //文档中是json对象，在代码中就传数组
            'is_hyaline' => false,
        ];

        $response = $this->client->request('POST', $url, [
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createwxaqrcode($path = '', $width = 430)
    {
        if (!$path)
            throw new ErrorException("参数：Path 必填！");

        $url = self::CREATEWXAQRCODE_URL . $this->token;

        $params = [
            'path'  => $path,
            'width' => $width,
        ];

        $response = $this->client->request('POST', $url, [
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
     * @return mixed
     */
    public function image()
    {
        return response($this->path)->header('Content-Type', 'image/jpeg');
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
        if (!$fileName)
            $fileName = date('Ymd') . uniqid() . '.png';

        $imageSrc = $filePath . DIRECTORY_SEPARATOR . $fileName;

        Storage::disk($disk)->put($imageSrc, base64_decode(base64_encode($this->path)));

        return $filePath ? Storage::disk($disk)->url($imageSrc) : Storage::disk($disk)->url($fileName);
    }

    /**
     * Notes: 生成 base64
     * Date: 2019/7/10 13:57
     * @return string
     */
    public function base64()
    {
        return 'data:image/png;base64,' . base64_encode($this->path);
    }
}