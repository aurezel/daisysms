<?php

namespace app\api\controller;

use think\facade\Validate;
use think\facade\Filesystem;
use think\Image;
use think\exception\ValidateException;

/**
 * 代码同步脚本
 * 参考
 * https://www.cnblogs.com/jkko123/p/6294561.html
 * 给www 加权限
 * Class Hook
 * @package app\api\controller
 */
class Hook extends Common
{
    public function index()
    {
        $secret_key = 'a000000';
        $web_root='/data/wwwroot/tradehelp.sua8.com';

        // get payload
//        $payload = trim(file_get_contents("php://input"));
//
//        if (empty($payload)) {
//            echo('缺少必要的参数');
//            exit();
//        }
//
//        // get header signature
//        $header_signature = isset($_SERVER['HTTP_X_GITEA_SIGNATURE']) ? $_SERVER['HTTP_X_GITEA_SIGNATURE'] : '';
//
//        if (empty($header_signature)) {
//            echo('无效的签名');
//            exit();
//        }
////
////        // calculate payload signature
//        $payload_signature = hash_hmac('sha256', $payload, $secret_key, false);
////
////        // check payload signature against header signature
//        if ($header_signature !== $payload_signature) {
//            echo('签名错误');
//            exit();
//        }

        //todo
        $shell = "cd {$web_root}/ && pwd && git pull 2>&1";
        exec($shell,$out);
        print_r($out);
    }
}