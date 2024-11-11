<?php 
/*
 module:		客服成员管理
 create_time:	2020-07-25 19:46:54
 author:		
 contact:		
*/

namespace app\api\service;
use app\admin\model\Leadercategory;
use app\admin\service\LeaderService;
use app\api\controller\Jwt;
use app\api\model\Leader;
use think\facade\Db;
use think\facade\Log;
use think\exception\ValidateException;
use xhadmin\CommonService;


class DaisySMSService extends CommonService {

    public static function parseDaisySmsApi($uid, $method = 'getBalance', $indata = array()) {
        // API key和基础URL，可以根据需求动态配置
        $apiKey = config("my.daisy_api_key"); // 替换为你的 API Key
        $baseUrl = "https://daisysms.com/stubs/handler_api.php?api_key=$apiKey&action=";
        // 构造 URL
        $url = $method;
        // 针对不同的请求方法添加对应的参数
        switch ($method) {
            case 'getBalance':
                // 不需要额外参数，直接调用
                break;
            case 'getNumber':
                // 租用号码时，检查必要参数
                if (!isset($indata['service']) || !isset($indata['max_price'])) {
                    return "Error: Missing service or max_price parameter.";
                }
                $url .= '&service=' . $indata['service'];
                $url .= '&max_price=' . $indata['max_price'];
                if (isset($indata['areas'])) {
                    $url .= '&areas=' . $indata['areas'];
                }
                if (isset($indata['carriers'])) {
                    $url .= '&carriers=' . $indata['carriers'];
                }
                break;
            case 'getStatus': //取短信验证码
                // 获取验证码时，检查是否有 id 参数
                if (!isset($indata['id'])) {
                    return "Error: Missing id parameter.";
                }
                $url .= '&id=' . $indata['id'];
                break;
            case 'setStatus':
                // 设置状态，比如标记为完成或者取消
                if (!isset($indata['id']) || !isset($indata['status'])) {
                    return "Error: Missing id or status parameter.";
                }
                $url .= '&id=' . $indata['id'] . '&status=' . $indata['status'];
                break;
            case 'getPricesVerification':
            case 'getPrices':
                // 这两个方法不需要额外参数
                break;
            default:
                return "Error: Unsupported method.";
        }
        $urls = $baseUrl . $url;
        // 初始化 cURL 请求
        $result = static::curl_params($urls);
        $lastresult = ['status'=>201,'msg'=>'参数错误_myapi'];
        if(!empty($result)) {
            $opdata = [];
            $opdata['userid'] = $uid;
            $opdata['optype'] = 1;
            $opdata['opaction']=$url;
            $opdata['opdes']='接口返回';
            $opdata['content']='传入参数:'.json_encode($indata).',回传数据:'.$result;
            CavenService::Oplog($opdata);

            $lastresult['status'] = 200;
            $lastresult['msg'] = "";
            $lastresult['result'] = $result;
        }
        return $lastresult;
    }

    public static function curl_params($url, $data = array(), $token = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;

    }
}

