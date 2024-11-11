<?php
namespace think;
define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
require __DIR__ . '/../../vendor/autoload.php';
use GatewayWorker\Register;
use Workerman\Worker;

new Register('text://0.0.0.0:1233');

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}