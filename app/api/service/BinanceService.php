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

class BinanceService extends CommonService {

    public static function tokentouid($token){
        if(!$token){
            return ['status'=>201,'msg'=>'token不能为空'];
        }
        if(count(explode('.',$token)) <> 3){
            return ['status'=>201,'msg'=>'token格式错误'];
        }
        $jwt = Jwt::getInstance();
        $jwt->setIss(config('my.jwt_iss'))->setAud(config('my.jwt_aud'))->setSecrect(config('my.jwt_secrect'))->setToken($token);
        try{
            if($jwt->decode()->getClaim('exp') < time()){
                return ['status'=>201,'msg'=>'token过期'];
            }
            if($jwt->validate() && $jwt->verify()){
                $uid = $jwt->decode()->getClaim('uid');
                return ['status'=>200,'uid'=>$uid];

            }else{
                return ['status'=>201,'msg'=>'token失效'];
            }
        }catch(\Exception $e){
            return ['status'=>201,'msg'=>'token失效'];
        }

    }

    public static function getToken($uid){
        $jwt = Jwt::getInstance();
        $jwt->setIss(config('my.jwt_iss'))->setAud(config('my.jwt_aud'))->setSecrect(config('my.jwt_secrect'))->setExpTime(config('my.jwt_expire_time'));
        $token = $jwt->setUid($uid)->encode()->getToken();
        return $token;
    }

    public static function getTestToken($uid){
        $url = 'https://trade.sua8.com:7676/api/getToken/test';
        $indata['uid'] = $uid;
       return curl_post($url,$indata);
    }

    public static function parseFromApi($uid,$method='getTradeUserConfig',$indata=array(),$islh=0){
        $vinfo = in_array($method,array('getUserKey','saveUserKey','auditUserKey'))?'dingTalkV2':'dingTalkV3';

        $indata['token'] = self::getToken($uid);
        //$indata['token'] = self::getTestToken($uid);
        $indata['userId'] = $uid;
        $indata['source'] = 'App';
        $baseurl = 'http://43.129.14.131:8080';//https://api1.binance.com https://api2.binance.com https://api3.binance.com
        //$baseurl = 'http://biniu.biniuhelper.com/core/api';
       if(in_array($method,array('getLeaderConfig','getUserPnlByBat'))){
           $vinfo = 'dingTalk';
       }
       if(isset($indata['checkBinance'])&&$indata['checkBinance']==0){
           $data['checkBinance']=1;
       }
       if(in_array($method,array('leaderTradeData','leaderTradeDataForchart'))){
           $vinfo = 'report';
       }
        if($method=='getUserOrderlist'){
            $vinfo='userOrder';
            $method='list';
            //$baseurl = 'http://43.154.251.104:8080';
        }

        if($method=='transferHistory'){
            $vinfo='userBalance';
            $method='transferHistory';
            //$baseurl = 'http://43.154.251.104:8080';
        }
        
        if($method=='userStatMain'){
            $vinfo='userReport';
            $method='userStatMain';
            //$baseurl = 'http://43.154.251.104:8080';
        }
        if(in_array($method,array('getTotalBalanceByChannel','getUserBalanceByChannel'))){
            $vinfo='channel';
            //$method='userStatMain';
        }

        if(in_array($method,array('clearByChannelName','clearByUserId','resetPosByUserId','resetPosByChannelName'))){
            $vinfo='userPos';
            //$method='userStatMain';
        }
        
        if(in_array($method,array('getFinishList'))){
            $vinfo='walletAdmin';
            //$method='userStatMain';
        }
        $url = $baseurl.'/'.$vinfo.'/'.$method;
        $urlb = $vinfo.'/'.$method;
       if($islh==1){
           $url = $baseurl.'/martingale/'.$method;
       }
        $result = curl_post($url,$indata);
        $lastresult = ['status'=>201,'msg'=>'参数错误204_myapi'];
        if(!empty($result)) {
            $opdata = [];
            $opdata['userid'] = $uid;
            $opdata['optype'] = 3;
            $opdata['opaction']=$urlb;
            $opdata['opdes']='接口返回';
            unset($indata['token']);
            if(isset($indata['key'])){
                unset($indata['key']);
            }
            $opdata['content']='传入参数:'.json_encode($indata).',回传数据:'.$result;
            CavenService::Oplog($opdata);
            
            $result = json_decode($result, true);
            if (!empty($result)) {
                $lastresult['status'] = isset($result['success'])&&$result['success']?200:201;
                if($result['message']=='找不到userKey'){
                    $lastresult['status'] = 202;
                }
                $lastresult['msg'] =$result['message'].':'.$result['error'];
                $lastresult['result'] = isset($result['result'])?$result['result']:'';
            }
        }
        return $lastresult;
    }
    //保存信息
    public static function saveUserKey($uid,$inapp){
        return self::parseFromApi($uid,'saveUserKey',$inapp);
    }

    public static function formatUserkey($redata,$data = '',$timformage=1){
        $adddata = [];
        $adddata['userid'] = $redata['userId'];
        if(isset($data['apiKey'])){
            $adddata['apikey'] = $data['apiKey'];
        }else{
            $adddata['apikey'] = $redata['apiKey'];
        }
        if(isset($data['secretKey'])){
            $adddata['secretkey'] = $data['secretKey'];
        }else{
            $adddata['secretkey'] = $redata['secretKey'];
        }
        $adddata['tradename'] = intval($redata['tradeName']);
        $adddata['passphrase'] = $redata['passphrase'];
        $adddata['type'] = intval($redata['type']);
        $adddata['remark'] = $redata['remark'];
        if($timformage==1){
            if($redata['isValid']==1){
                $adddata['validtime'] = strtotime($redata['validTime']);
                $adddata['createtime'] = strtotime($redata['createTime']);
                $adddata['updatetime'] = strtotime($redata['updateTime']);
            }
        }
        $adddata['tradenameid'] = $redata['tradeNameId'];
        $adddata['isvalid'] = $redata['isValid']?1:0;
        $adddata['source'] = $redata['source'];
        return $adddata;
    }


    //取得网站配置信息
    public static function getLeaderConfig($uid,$leaderid=''){
        $filename = "getLeaderConfig";
        $list = read_static_cache($filename,10);
        if($list){
            if(!empty($leaderid)){
                foreach($list as $key=>$v){
                    if($v['acceptSource']==$leaderid){
                        return $v;
                    }
                }
                return '';
            }
           return $list;
        }
        $result =self::parseFromApi($uid,'getLeaderConfig');
       // print_r($result);die;
        if($result['status']==200){
            //写入大神信息表
            $list = $result['result'];
            if(!empty($leaderid)&&!empty($list)){
                foreach($list as $key=>$v){
                    if($v['acceptSource']==$leaderid){
                        return $v;
                    }
                }
                return '';
            }
            if(!empty($list)){
                foreach($list as $key=>$v){
                    $adddata = [];
                    $adddata['title'] = $v['title'];
                    //$adddata['catid'] = intval($v['catid']);
                    $adddata['acceptsource'] = $v['acceptSource'];
                    $adddata['summoney'] = $v['sumMoney'];
                    $adddata['realsummoney'] = $v['realSumMoney'];
                    $adddata['proportion'] = $v['proportion'];
                    $adddata['isvalid'] = $v['isValid'];
                    $adddata['isenable'] = intval($v['isEnable']);
                    $adddata['orderno'] = $v['orderNo'];
                    $adddata['remark'] = $v['remark'];
                    $where = [];
                    $where['id'] = $v['id'];
                    $info = Leader::getWhereInfo($where);
                    if(!empty($info)){
                        $adddata['isvalid'] = 1;
                        $info['isvalid']=1;
                        Leader::update($adddata,$where);
                    }else{
                        $adddata['nick'] = $v['title'];
                        $adddata['id'] = $v['id'];
                        Leader::create($adddata);
                    }
                }
            }

        }

        write_static_cache($filename,$list);
        return $list;
    }

//取得币种列表
    public static function getcoinList(){
        $var = array (
            'BTCUSDT'=>array(
                'icon'=>'https://static.aicoinstorge.com/coin/20180523/152707758483056.png?x-oss-process=image/resize,m_fill,h_108,w_108,limit_0',
                'title'=> 'BTCUSDT',
		        'entitle'=>'未配置',
                'check'=>0,
            ),
            'ETHUSDT'=>array(
                'icon'=>'https://static.aicoinstorge.com/coin/20180613/152885626571451.png?x-oss-process=image/resize,m_fill,h_108,w_108,limit_0',
                'title'=> 'ETHUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'BNBUSDT'=>array(
                'icon'=>'https://static.aicoinstorge.com/coin/20180613/152885535137663.png?x-oss-process=image/resize,m_fill,h_108,w_108,limit_0',
                'title'=> 'BNBUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'BCHUSDT'=>array(
                'icon'=>'https://static.aicoinstorge.com/coin/20201116/160549209779375.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=> 'BCHUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'LTCUSDT'=>array(
                'icon'=>'https://static.aicoinstorge.com/coin/20180524/152713571467773.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=> 'LTCUSDT',
                'entitle'=>'未配置',
            ),
            'EOSUSDT'=>array(
                'icon'=>'https://static.aicoinstorge.com/coin/20180524/152713388970485.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=> 'EOSUSDT',
                'entitle'=> '未配置',
                'check'=>0,
            ),
            'ETCUSDT'=>array(
                'icon'=> 'https://static.aicoinstorge.com/coin/20180613/152885624239507.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=>'ETCUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'XRPUSDT'=>array(
                'icon'=>'https://static.aicoinstorge.com/coin/20200713/159463019667764.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=>'XRPUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'DOGEUSDT'=>array(
                'icon'=>'https://static.aicoinstorge.com/coin/20180613/152885602569226.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=>'DOGEUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'UNIUSDT'=>array(
                'icon'=>'https://static.aicoinstorge.com/coin/20200917/160030852297622.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=>'UNIUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'DOTUSDT'=>array(
                'icon'=>'https://static.aicoinstorge.com/coin/20200824/159826733695914.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=>'DOTUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'ADAUSDT'=>array(
                'icon'=> 'https://static.aicoinstorge.com/coin/20180523/152706148030378.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=>'ADAUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'TRXUSDT'=>array(
                'icon'=> 'https://static.aicoinstorge.com/coin/20181207/154418734169962.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=>'TRXUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'LINKUSDT'=>array(
                'icon'=> 'https://static.aicoinstorge.com/coin/20200522/159013618579999.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=> 'LINKUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'THETAUSDT'=>array(
                'icon'=> 'https://static.aicoinstorge.com/coin/20180613/152885931258088.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=>'THETAUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'XLMUSDT'=>array(
                'icon'=> 'https://static.aicoinstorge.com/coin/20190511/155755639180248.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=>'XLMUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'FILUSDT'=>array(
                'icon'=> 'https://static.aicoinstorge.com/coin/20201010/160230069488390.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=>'FILUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            ),
            'EGLDUSDT'=>array(
                'icon'=> 'https://static.aicoinstorge.com/coin/20200902/159901149239113.png?x-oss-process=image/resize,m_lfit,h_45,w_45,image/circle,r_21/format,png',
                'title'=>'EGLDUSDT',
                'entitle'=>'未配置',
                'check'=>0,
            )

        );

        return $var;

    }

    function substr_cut($user_name){
        $strlen   = mb_strlen($user_name, 'utf-8');
        $firstStr   = ucfirst(strtolower(mb_substr($user_name, 0, 3, 'utf-8')));
        $lastStr   = strtolower(substr($user_name, -3));
        if($strlen == 2){
            $hideStr = str_repeat('*', strlen($user_name, 'utf-8') - 1);
            $result = $firstStr . $hideStr ;
        }else {
            $hideStr = substr(str_repeat("*", $strlen - 6), 0, 3);
            $result = $firstStr . $hideStr . $lastStr;
        }
        return $result;
    }
    //编辑用户API状态
    public static function auditUser($data,$uid){
        //$data['validTime'] = date("Y-m-d H:i:s",strtotime("+1 month"));
        if(isset($data['isValid'])){
            $data['isValid'] = $data['isValid'];
        }else{
            $data['isValid'] = 1;
        }
        $result =  BinanceService::parseFromApi($uid,'auditUserKey',$data);
        return $result;
    }

    public static function array_sort($arr, $keys, $type = 'desc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = isset($v[$keys]) ? $v[$keys] : "";
        }

        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    //取得网站配置信息
    public static function getMyLeaderConfig(){
        $filename = "getMyLeaderConfig";
        $list = read_static_cache($filename,1);
        if($list){
            return $list;
        }
        $where = [];

        $order  = '';	//排序字段 bootstrap-table 传入
        $sort  = '';		//排序方式 desc 或 asc

        $field = 'a.id,a.title,nick,setdes,catid';
        $orderby = ($sort && $order) ? $sort.' '.$order : 'a.id desc';
        $list=[];
        $res = LeaderService::indexList(formatWhere($where),$field,$orderby,1000,1);
        if(!empty($res['rows'])){
            foreach($res['rows'] as $key=>$v){
                $one= array();
                $one['id']=$v['id'];
                $one['title']=$v['title'];
                $one['nick']=$v['nick'];
                $one['setdes']=$v['setdes'];
                $one['catid']=$v['catid'];
                $list[$v['id']]=$one;
            }
        }
        write_static_cache($filename,$list);
        return $list;
    }


    //取得网站配置信息
    public static function getLeaderConfigCategory($uid,$leaderid=''){
        $filename = "getLeaderConfigCategory";
        $list = read_static_cache($filename,10);
        if($list){

            return $list;
        }
        $result =self::parseFromApi($uid,'getLeaderConfigCategory');

        if($result['status']==200){
            //写入大神信息表
            $list = $result['result'];

            if(!empty($list)){
                foreach($list as $key=>$v){
                    $adddata = [];
                    $adddata['title'] = $v['title'];
                    $adddata['stitle'] = $v['title'];
                    $adddata['head_url'] = $v['head_url'];
                    //$adddata['learder_desc'] = intval($v['learder_desc']);
                    $adddata['tid'] = $v['id'];
                    $where = [];
                    $where['tid'] = $v['id'];
                    $info =Db::name('leadercategory')->where($where)->field('*')->find();
                    if(!empty($info)){
                        if(!empty($info['head_url'])){
                            unset($adddata['head_url']);
                        }
                        Leadercategory::update($adddata,$where);
                    }else{
                        $adddata['entitle'] = $v['title'];
                        $adddata['tid'] = $v['id'];
                        Leadercategory::create($adddata);
                    }
                }
            }

        }

        write_static_cache($filename,$list);
        return $list;
    }


    //通过分类ID取得大神列表
    public static function getLeaderConfigByCategory($uid,$catid='',$isall=0){
        $filename = "getLeaderConfigCategory_".$catid;
        $list = read_static_cache($filename,10);
        if($list){

           // return $list;
        }
        $postdata['id']=$catid;
        $result =self::parseFromApi($uid,'getLeaderConfigByCategory',$postdata);
        if($isall){
            return $result;
        }
        if($result['status']==200){
            //写入大神信息表
            $list = $result['result'];
            if(isset($list['items'])&&!empty($list['items'])){
                foreach($list['items'] as $key=>$v){
                    $adddata = [];
                    $adddata['catid'] = $catid;
                    $where = [];
                    $where['acceptsource'] = $v['value'];
                    $info =Db::name('leader')->where($where)->field('*')->find();
                    if(!empty($info)){
                        Leader::update($adddata,$where);
                    }else{
                        echo 'aa';
                    }
                }
            }

        }

        write_static_cache($filename,$list);
        return $list;
    }


    //编辑用户最近100条交易记录
    public static function getUserOrderlist($uid){
        $result = BinanceService::parseFromApi($uid,'getUserOrderlist');
        return $result;
    }

    //编辑用户最近100条交易记录
    public static function userStatMain($uid){
        $re = [];
        $re['usdt'] = '0';
        $re['swapusdt'] = '0';
        $re['spotusdt'] = '0';
        $result = BinanceService::parseFromApi($uid,'userStatMain');
        if(!empty($result)&&$result['status']==200){
            if(isset($result['result'])&&!empty($result['result'])){
                $re['usdt'] =number_format($result['result']['balance'],2);
                $re['swapusdt'] =number_format($result['result']['balanceSwap'],2);
                $re['spotusdt'] =number_format($result['result']['balanceSpot'],2);
            }
        }
        return $re;
    }
}

