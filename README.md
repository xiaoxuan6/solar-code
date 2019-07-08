微信小程序生成太阳码
======

## Installation

    composer require james.xue/solar-code

## User

    $solar = new SolarCode;
    $result = $solar->getCode($accessToken, 'pages/findModule/pages/index/index', 'sd')->image();
    $result = $solar->getCode($accessToken, 'pages/findModule/pages/index/index', 'sd')->path();

[Awesome](https://github.com/xiaoxuan6/)
 
 
