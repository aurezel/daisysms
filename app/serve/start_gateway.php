<?php
namespace think;

require __DIR__ . '/../../vendor/autoload.php';
use GatewayWorker\Register;
use Workerman\Worker;
use GatewayWorker\Gateway;
$context = array(
    // 更多ssl选项请参考手册 http://php.net/manual/zh/context.ssl.php
    'ssl' => array(
        // 请使用绝对路径
        'local_cert'                 => '/opt/3596667__expertcp.com.pem', // 也可以是crt文件
        'local_pk'                   => '/opt/3596667__expertcp.com.key',
        'verify_peer'               => false,
    )
);
$gateway = new Gateway("Websocket://0.0.0.0:7887",$context);

// 设置名称，方便status时查看
$gateway->name = 'FwGateway';
// 设置进程数，gateway进程数建议与cpu核数相同
$gateway->count = 2;
// 分布式部署时请设置成内网ip（非127.0.0.1）
$gateway->lanIp = '127.0.0.1';
// 内部通讯起始端口。假如$gateway->count=4，起始端口为2300
// 则一般会使用2300 2301 2302 2303 4个端口作为内部通讯端口
$gateway->startPort = 2790;
// 心跳间隔
$gateway->pingInterval = 15;
$gateway->pingNotResponseLimit = 2;
// 心跳数据
$gateway->pingData = '{"type":"ping"}';
// 服务注册地址
$gateway->registerAddress = '127.0.0.1:1233';
//开启ssl
$gateway->transport = 'ssl';


// 如果不是在根目录启动，则运行runAll方法
if (!defined('GLOBAL_START')) {
    Worker::runAll();
}