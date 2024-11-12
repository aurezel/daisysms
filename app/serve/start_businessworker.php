<?php

// 加载基础文件
require __DIR__ . '/../../vendor/autoload.php';


use think\App;
use think\facade\Env;
use \Workerman\Worker;
use \GatewayWorker\BusinessWorker;
use Workerman\Lib\Timer;


$app = new App();
$app->initialize();


$worker = new BusinessWorker();
$worker->name = 'FwBusinessWorker';
$worker->count = 2;
$worker->registerAddress = '127.0.0.1:1233';

//参考 https://wenda.workerman.net/question/2600

$worker->onWorkerStart = function ($worker) {
    if ($worker->id === 0) {
       /* Timer::add(10, function () {
            @file_get_contents('https://api.aimichat.com/sport/sendmsg');
        });

        Timer::add(86400, function () {
            #删除oss文件
            @file_get_contents('https://api.aimichat.com/async/oss_delete_file');
        });*/

        Timer::add(2, function () {
            #删除oss文件
            @file_get_contents('https://aimichat.expertcp.com/cron/increasecoin');
        });


    }
};
/*
 * 设置处理业务的类为MyEvent。
 * 如果类带有命名空间，则需要把命名空间加上，
 * 类似$worker->eventHandler='\my\namespace\MyEvent';
 */
$worker->eventHandler = '\app\serve\Events';

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}
