<?php
/*
 module:		会员管理
 create_time:	2020-08-09 18:28:55
 author:		
 contact:		
*/

namespace app\api\controller;

use app\admin\model\Agent;
use app\admin\model\Bill;
use app\admin\model\Incomeday;
use app\admin\model\Tradegroup;
use app\api\model\Apiuser;
use app\api\service\ActiveService;
use app\api\service\BinanceService;
use app\api\service\CavenService;
use app\api\service\MemberFindMethodService;
use app\api\service\PayService;
use think\facade\Db;
use think\facade\Log;


class GetAppurl extends Common
{
    
    public static function _login(){
        
       $path = root_path();
       $temp_dir = $path.'tempcookies/cookie.ym.tmp';
		$url = 'https://www.yimenapp.com/developer/login/ajax.aspx?ReturnUrl=&method=login'; 
		$data = array( 
			'userName'  => 'testtv', 
			'userPwd'  => 'a000000'
		); 
		$header = array('X-Requested-With: XMLHttpRequest');
		//curl初始化 
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		//设置为post请求 
		curl_setopt($ch, CURLOPT_POST, true); 
		//设置附带返回header信息为空 
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		//post数据 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
		
		
		//cookie保存文件位置 
		
		curl_setopt($ch, CURLOPT_COOKIEJAR, $temp_dir); 
		//设置数据返回作为变量储存，而不是直接输出 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。

		//执行请求 
		$ret = curl_exec($ch); 
		print_r($ret);
		
		//关闭连接 
		curl_close($ch);
	}
	
    
	public function _getContent($url,$post=1,$cookies='',$data='',$isXML=0){
		//global $cookies;
		$ch =curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		if($isXML==1){
			$header = array('X-Requested-With: XMLHttpRequest','Accept: application/json, text/javascript, */*; q=0.01');
		}
		if($post){
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
				}
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HEADER,false);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		curl_setopt($ch, CURLOPT_REFERER, 'https://zhangwenwenhua.cn/admin/referral/book/index?sort=idx&order=desc&offset=0&limit=10&_=1569394827270');
		//$cookies='PHPSESSID=68tledm6acto1nigaa77bc7vcv; keeplogin=11000%7C604800%7C1595323766%7C99ee38b534929e9106089eedb4e31152';
		//curl_setopt($ch,CURLOPT_COOKIE,$cookies);
		curl_setopt($ch, CURLOPT_COOKIEFILE, temp_dir); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
		$content = curl_exec($ch);
		curl_close($ch); 
		return $content;
	}
    
    public static function curl_post($url, $data = array(),$ispost = 0,$token = '')
    {
        $path = root_path();
       $temp_dir = $path.'tempcookies/cookie.ym.tmp';
        $cookies = 'x=3984A78F2C78F23602EA80C6A05F318DEF1ACD6A38DF150EC4B08D69249B58A6AEA50A9E3C441298E002576341E8A6F8D4B3A67BE0FC1DA6AFB0F62B258C839BBB68E781233FAB10357390EDB3247C99126F09928870EFC482D241CA63DB8944E26ABF44900B2A11C6857835DB49357C; __root_domain_v=.yimenapp.com; _qddaz=QD.497608865099685; Hm_lvt_3da5a313e099b629a89e99f0ef41896c=1708864705,1708932996,1708997120; Hm_lvt_acb5b28fbdbef6aadca2373f2329a647=1708864705,1708932996,1708997120; ASP.NET_SessionId=hpeyxbabypl4gt0kxfd5v2uh';
        $header = array(
            'Host: www.yimenapp.com',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
           // 'Cookie: .x=94ABE621D38778B3633633C6FB61732B66E24BE793A0B706681DB6771EA53BA0DDB19DE5FE4A2AE335176E89DD856CE631A9483CB548C90BB85C72C64D39ABBA92DE4D8FB210222C6D012D0474B7BD2F0A38719F424C75AD1F78926FC3AD8F94443AEFA37622A97CA56B335B4E79A346; __root_domain_v=.yimenapp.com; _qddaz=QD.497608865099685; ASP.NET_SessionId=hpeyxbabypl4gt0kxfd5v2uh; Hm_lvt_3da5a313e099b629a89e99f0ef41896c=1708864705,1708932996,1708997120,1710158660; Hm_lvt_acb5b28fbdbef6aadca2373f2329a647=1708864705,1708932996,1708997120,1710158660; Hm_lpvt_3da5a313e099b629a89e99f0ef41896c=1710738927; Hm_lpvt_acb5b28fbdbef6aadca2373f2329a647=1710738927',
            'Upgrade-Insecure-Requests: 1',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
            'Sec-Fetch-User: ?1',
            'TE: trailers'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEFILE, $temp_dir); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
       curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
       // curl_setopt($ch, CURLOPT_TIMEOUT,30);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
      //  curl_setopt($ch,CURLOPT_COOKIE,$cookies);
        // POST数据
        curl_setopt($ch, CURLOPT_POST, $ispost);
        if(!empty($data)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        // 把post的变量加上
        //
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;

    }
    
    public function index(){
        $url = 'https://www.yimenapp.com/developer/xapi/?type=webclip&id=292728&method=AppDownloadTemp';
        $result = self::curl_post($url);
        $result = json_decode($result,true);
        if(empty($result)){
            self::_login();
            $url = 'https://www.yimenapp.com/developer/xapi/?type=webclip&id=292728&method=AppDownloadTemp';
            $result = self::curl_post($url);
            $result = json_decode($result,true);
        }
        header("Location: ".$result['d']);
        //print_r($result);
    }
}