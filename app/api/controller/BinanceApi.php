<?php
/*
 module:		会员管理
 create_time:	2020-08-09 18:28:55
 author:
 contact:
*/

namespace app\api\controller;

use app\admin\model\Agent;
use app\admin\model\Leadercategory;
use app\admin\model\Tradegroup;
use app\admin\service\LeadercategoryService;
use app\api\model\Apiuser;
use app\api\service\BinanceService;
use app\api\service\CavenService;
use app\api\service\MemberFindMethodService;
use think\facade\Db;

//getUserKey tradeName 0 或空是bian，1为OK

//大神列表

class BinanceApi extends Common
{

    //取得用户APIKEY
    public function getUserKey($uid=0,$type=0){
        $re = ['status' => 200, 'msg' => '','data'=>''];
        if(empty($type)){
            $uid = $this->request->uid;
        }
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $re;
                //return $this->reDecodejson($re);
        }

        $whereb['member_id'] = $uid;
        $memberinfo = \app\api\model\Member::getWhereInfo($whereb);

        if(empty($memberinfo)){
            $re = ['status' => 201, 'msg' => '用户不存在'];
            return $re;
                //return $this->reDecodejson($re);
        }
        //先查询用户本地信息
        $where['userid'] = $uid;
        $info = Apiuser::getWhereInfo($where);
        
        if(!empty($info)){
            $result =  BinanceService::parseFromApi($uid,'getUserKey');
            $re['data'] = $result;
            if(($result['status']!=200)){
                $re['status'] = 200;
                $re['msg'] = $result['msg'];
                //$re['data'] = $info;
            }else{
                $redata = $result['result'];
                $adddata =  BinanceService::formatUserkey($redata,'',1);
                if($adddata['userid']==0){
                    $re = ['status' => 201, 'msg' => $uid.'-'.$type];
                    return $this->reDecodejson($re);
                }
                $adddata['mobile'] = $memberinfo['mobile'];
                Apiuser::update($adddata,$where);
            }
            $re['data'] = $info;
        }else{
            $result =  BinanceService::parseFromApi($uid,'getUserKey');

            if(($result['status']==201)||$result['msg']=='没有找到相关资料'){
               $data['apistauts'] = 0;
               $re=[];
               return $re;
                //return $this->reDecodejson($re);
            }else{
                $redata = $result['result'];
                $adddata =  BinanceService::formatUserkey($redata,'',1);
                $adddata['mobile'] = $memberinfo['mobile'];
                $adddata['type'] = 1;
                $adddata['userid'] = $uid;
                $adddata['createtime'] = time();
                $adddata['updatetime'] = time();
                if($adddata['userid']==0){
                    $re = ['status' => 201, 'msg' => $uid.'-'.$type];
                    return $this->reDecodejson($re);
                }

                $where['userid'] = $uid;
                $info = Apiuser::getWhereInfo($where);
                if(!empty($info)){
                    Apiuser::update($adddata,$where);
                }else{
                    $adddata['tradename']=3;
                    Apiuser::create($adddata);
                }

                //$where['userid'] = $uid;
                $info = Apiuser::getWhereInfo($where);
                $re['data'] = $info;


            }

        }
        if($info['tradenameid']&&!empty($info['apikey'])){
            $re['data']['apistatus']=1;
            $re['data']['tradenameen']=$re['data']['tradename']==0?'Binance':'OkexV5';
            //$re['data']['createtime'] = substr($re['data']['createtime'],0,10);
            //$re['data']['updatetime'] = substr($re['data']['updatetime'],0,10);
        }else{
            $re['data']['apistatus']=0;
            $re['data']['tradenameen']='-';
            $re['data']['createtime']='-';
            $re['data']['updatetime']='-';
        }

        if(isset($re['data'])){
            $re['data']['validtime'] = empty($re['data']['validtime'])?'-':date('Y-m-d',$re['data']['validtime']);
            $re['data']['createtime'] = ($re['data']['createtime']!="-" && !empty($re['data']['createtime']))?date('Y-m-d',$re['data']['createtime']):'-';
            $re['data']['updatetime'] = ($re['data']['updatetime']!="-" && !empty($re['data']['updatetime']))?date('Y-m-d',$re['data']['updatetime']):'-';
            $re['data']['apikey'] = BinanceService::substr_cut($re['data']['apikey']);
            $re['data']['secretkey'] = BinanceService::substr_cut($re['data']['secretkey']);
        }
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceApi/getUserKey:getUserKey';
        $opdata['opdes']='取得用户API信息';
        CavenService::Oplog($opdata);
        if($type){
            return $re['data'];
        }
        return $re;
    }
    //保存信息
    public function saveUserKey(){

        $re = ['status' => 200, 'msg' => '','data'=>''];
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }

        //,isValid,validTime,updateTime,remark,tradeName,
        $postField = 'apikey,secretkey,tradenameid,passphrase,tradename';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        //test
        $data['apiKey'] = $data['apikey'];
        $data['secretKey'] = $data['secretkey'];
        $data['tradeNameId'] = $data['tradenameid'];
        $data['tradename'] = intval($data['tradename']);

        if(empty($data['apiKey'])|| strlen($data['apiKey'])<30){
            $re = ['status' => 201, 'msg' =>'apiKey信息输入错误'];
            return $this->reDecodejson($re);
        }
        if(empty($data['secretKey'])|| strlen($data['secretKey'])<30){
            $re = ['status' => 201, 'msg' =>'secretKey信息输入错误'];
            return $this->reDecodejson($re);
        }
        if(empty($data['tradeNameId'])|| strlen($data['tradeNameId'])<5){
            $re = ['status' => 201, 'msg' =>'交易平台用户ID填写错误'];
            return $this->reDecodejson($re);
        }
        if($data['tradename']==1 && empty($data['passphrase'])){
            $re = ['status' => 201, 'msg' =>'passphrase信息输入错误'];
            return $this->reDecodejson($re);
        }
        $whereb = [];
        $whereb['member_id'] = $uid;
        $memberinfo = \app\api\model\Member::getWhereInfo($whereb);
        $aginfo = CavenService::getAgentinfo($memberinfo['channel']);

        //此处加入验证充值信息
        if($aginfo['jhmoney']>0){
            $sql = "SELECT SUM(money) as total FROM `cd_chargeorder` WHERE member_id='".$uid."' AND status=2";
            $res = Db::query($sql);
            if($res[0]['total']<$aginfo['jhmoney']){
                $re = ['status' => 201, 'msg' =>'您的账户尚未激活，请先充值'.$aginfo['jhmoney'].'U到资金账户，系统会自动激活账户，激活账户后，您再进行绑定API操作！'];
                return $this->reDecodejson($re);
            }
        }

        $inapp['checkBinance'] = 1;//为不检查，1要检查
        $inapp['checkRebate']=1;//检查下线 0不检查，1检测
        if($aginfo){
            $inapp['checkRebate'] = intval($aginfo['checkrebate']);
            $inapp['checkBinance']=intval($aginfo['checkbinance']);
            if(!empty($aginfo['minmoney'])&&is_numeric($aginfo['minmoney'])){
                $inapp['usdt']=intval($aginfo['minmoney']);
            }
            $aginfo['maxmoney'] = intval($aginfo['maxmoney']);
            $aginfo['totalmoney'] = intval($aginfo['totalmoney']);
            if($aginfo['maxmoney']>0&&$aginfo['maxmoney']<$aginfo['totalmoney']){
                $re = ['status' => 201, 'msg' =>'对不起此项目招募金额已达到，不再接收新人，请联系管理员'];
                return $this->reDecodejson($re);
            }
        }

        if($aginfo['tradetype']!=''){
            $chanarr = explode(',',$aginfo['tradetype']);
            if(!in_array($data['tradename'],$chanarr)){
                if($data['tradename']==1){
                    $re = ['status' => 201, 'msg' =>'此交易不支持OKEX账号'];
                    return $this->reDecodejson($re);
                }else{
                    $re = ['status' => 201, 'msg' =>'此交易不支持Binance账号'];
                    return $this->reDecodejson($re);
                }
            }
        }

        $inapp['apiKey'] = $data['apiKey'];
        $inapp['secretKey'] = $data['secretKey'];
        $inapp['tradeNameId'] = $data['tradeNameId'];
        $inapp['tradeName'] = $data['tradename'];
        $inapp['passphrase'] = $data['passphrase'];
        $inapp['channel'] = $memberinfo['channel'];
        //$inapp['checkBinance'] = '1';//为不检查，1要检查
        //$inapp['checkRebate']=0;//检查下线 0不检查，1检测
        $inapp['nickName'] = $memberinfo['mobile'];
        $result =  BinanceService::saveUserKey($uid,$inapp);
        if(($result['status']==201)||$result['msg']=='没有找到相关资料'){
            $re['status'] = 201;
            $re['msg'] = $result['msg'];
            return $this->reDecodejson($re);
        }else{
            $redata = $result['result']['userKey'];
            $adddata =  BinanceService::formatUserkey($redata,$data,1);
            $wherec['userid'] = $uid;
            $info = Apiuser::getWhereInfo($wherec);
            if(!empty($info)){
                $adddata['isvalid']=0;
                Apiuser::update($adddata,$wherec);
            }else{
                $whereb['member_id'] = $uid;
                $memberinfo = \app\api\model\Member::getWhereInfo($whereb);
                if(empty($memberinfo)){
                    $re = ['status' => 201, 'msg' => '用户不存在'];
                }else{
                    $adddata['type'] = 1;
                    $adddata['userid'] = $uid;
                    $adddata['mobile'] = $memberinfo['mobile'];
                    $info = Apiuser::getWhereInfo($wherec);
                    if(!empty($info)){
                        $adddata['isvalid']=0;
                        Apiuser::update($adddata,$wherec);
                    }else{
                        if($adddata['userid']==0){
                            $re = ['status' => 201, 'msg' => $uid];
                            return $this->reDecodejson($re);
                        }
                        $adddata['isvalid']=0;
                        $adddata['tradename']=3;
                        $adddata['createtime']=time();
                        $adddata['updatetime']=time();
                        Apiuser::create($adddata);
                    }

                }
            }
            $re['msg'] = '操作成功';
        }
        $where['userid'] = $uid;
        $info = Apiuser::getWhereInfo($where);
        $re['data'] = $info;
        $re['data']['apistatus']=1;
        if(isset($re['data'])){
            $re['data']['validtime'] = empty($re['data']['validtime'])?'-':date('Y-m-d H:i:s',$re['data']['validtime']);
            $re['data']['createtime'] = ($re['data']['createtime']!="-" && !empty($re['data']['createtime']))?date('Y-m-d',$re['data']['createtime']):'-';
            $re['data']['updatetime'] = ($re['data']['updatetime']!="-" && !empty($re['data']['updatetime']))?date('Y-m-d',$re['data']['updatetime']):'-';
            $re['data']['apikey'] = BinanceService::substr_cut($re['data']['apikey']);
            $re['data']['secretkey'] = BinanceService::substr_cut($re['data']['secretkey']);
        }
        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceApi/saveUserKey:saveUserKey';
        $opdata['opdes']='保存用户API接口信息';
        CavenService::Oplog($opdata);
        return $this->reDecodejson($re);

    }

    //取得大V列表

    public function getLeaderConfigByUserId($uid=0,$type=0){
        if(empty($type)){
            $uid = $this->request->uid;
            if(empty($uid)){
                $re = ['status' => 201, 'msg' => '请先登陆'];
                return $this->reDecodejson($re);
            }
        }
        $whereb['member_id'] = $uid;
        $memberinfo = \app\api\model\Member::getWhereInfo($whereb);
        if(empty($memberinfo)){
            $re = ['status' => 201, 'msg' => '用户不存在'];
            return $this->reDecodejson($re);
        }
        $gid = $memberinfo['groupid'];
        $groupinfo = Tradegroup::find($gid);
        if(empty($groupinfo)||empty($groupinfo['leaderlist'])){
            $re = ['status' => 202, 'msg' => '会员信息错误！'];
            return $this->reDecodejson($re);
        }
        $leaderlist = $groupinfo['leaderlist'];
        $lederidlist = explode(',', $leaderlist);
        $mysiteleader = BinanceService::getMyLeaderConfig();
        //print_r($groupinfo);


        $filename = "getLeaderConfigByUserId_".$uid;
        $result = read_static_cache($filename,10);
        if(empty($result)){
            $result =  BinanceService::parseFromApi($uid,'getLeaderConfigByUserId');
            $opdata = [];
            $opdata['userid'] = $uid;
            $opdata['optype'] = 1;
            $opdata['opaction']='BinanceApi:getLeaderConfigByUserId';
            $opdata['opdes']='取得大V列表';
            CavenService::Oplog($opdata);
            write_static_cache($filename,$result);
        }


        $itemsformat = [];
        if(isset($result['result']['items'])&&!empty($result['result']['items'])){
            foreach($result['result']['items'] as $key=>$v){
                if(!isset($v['realSumMoney'])){
                    $v['realSumMoney'] = '-';
                }else{
                    $v['realSumMoney'] = sprintf("%.2f",$v['realSumMoney']);
                }
                if(!isset($v['sumMoney'])){
                    $v['sumMoney'] = '-';
                }else{
                    $v['sumMoney'] = sprintf("%.2f",$v['sumMoney']);
                }
                if(!in_array($v['id'],$lederidlist)){
                    unset($result['result']['items'][$key]);
                    continue;
                }
                $itemsformat[$v['value']] = $v;
                $lederinfo = explode(',', $v['name']);
                if($v['disabled']){
                    $itemsformat[$v['value']]['checkInfo']='暂停';
                }
                $itemsformat[$v['value']]['leadername']=isset($mysiteleader[$v['id']]['nick'])?$mysiteleader[$v['id']]['nick']:$lederinfo[0];;

                $itemsformat[$v['value']]['leadermoney']=trim($lederinfo[1]);
            }
            $itemsformat = BinanceService::array_sort($itemsformat,'check','desc');

            $result['result']['items'] = $itemsformat;
        }
        //print_r($result['result']['items']);
        if(isset($result['result']['tradeUserConfig'])&&!empty($result['result']['tradeUserConfig'])){
            $result['result']['tradeUserConfig']['leadername'] = '-';
            $result['result']['tradeUserConfig']['leadermoney'] = '-';

            if(!empty($itemsformat)){
                if(isset($itemsformat[$result['result']['tradeUserConfig']['acceptSource']])){
                    $result['result']['tradeUserConfig']['leadername'] = $itemsformat[$result['result']['tradeUserConfig']['acceptSource']]['leadername'];
                    $result['result']['tradeUserConfig']['leadermoney'] = $itemsformat[$result['result']['tradeUserConfig']['acceptSource']]['leadermoney'];
                }
            }
        }
        //print_r($result['result']['items']);
        if($type==1){
            if(isset($result['result'])){
                return $result['result'];
            }
        }
        //print_r(1);
        return $this->reDecodejson($result);
    }

//取得大V列表
    public function getLeaderConfig(){
//        $uid = $this->request->uid;
//        if(empty($uid)){
//            $re = ['status' => 201, 'msg' => '请先登陆'];
//            return $this->reDecodejson($re);
//        }
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $result =  BinanceService::getLeaderConfig(0);
        $re['data']=$result;
        return $this->reDecodejson($re);
        //print_r($result);
    }
//保存大神配置
    public function saveTradeUserConfig(){
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        //默认A 所有 其它是单个币种的配置对应单个币的标识  proportion 延时下单毫秒，acceptSource 大神标识,云挂机开关 effective，bailValue 保证金比例
        $postField = 'symbol,leaderId,acceptSource,proportion,bailValue,effective,sleepTime';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if(empty($data['symbol'])){
            $data['symbol'] = 'A';
        }else{
            $coinlist = $this->getcoinList(1);
            $coinarr = array();
            if(!empty($coinlist)){
                foreach($coinlist as $key=>$v){
                    $coinarr[$key]=$key;
                }
            }
            if(!in_array($data['symbol'],$coinarr)&&$data['symbol']!='A'){
                $re = ['status' => 201, 'msg' =>'单个币种信息选择错误1'];
                return $this->reDecodejson($re);
            }
        }
        if(empty($data['acceptSource'])&&$data['symbol']=='A'){
            $re = ['status' => 201, 'msg' =>'请指定一位大神'];
            return $this->reDecodejson($re);
        }
        if(empty($data['proportion'])){
            $data['proportion']=0.0001;
        }
        if(empty($data['sleepTime'])){
            $data['sleepTime']=0;
        }
        $data['sleepTime'] = intval($data['sleepTime']);
        if(empty($data['bailValue'])&&$data['symbol']=='A'){
            $re = ['status' => 201, 'msg' =>'保证金告警系数不能为空,默认为 10'];
            return $this->reDecodejson($re);
        }
        $data['effective'] = intval($data['effective']);
        if(!in_array($data['effective'],array(0,1))&&$data['symbol']=='A'){
            $re = ['status' => 201, 'msg' =>'开关不能为空'];
            return $this->reDecodejson($re);
        }
        $data['effective'] = $data['effective']==1?'true':'false';
        $data['checkBinance']=0;
        $result =  BinanceService::parseFromApi($uid,'saveTradeUserConfig',$data);

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceApi:saveTradeUserConfig';
        $opdata['opdes']='保存大神配置';
        CavenService::Oplog($opdata);
        return $this->reDecodejson($result);
    }
//取得币种列表
    public function getcoinList($type=0){
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $result = BinanceService::getcoinList();
        if($type==1){
            return $result;
        }
        $resultb =  self::getTradeUserConfigAll(1);
        $arr = [];
        if(isset($resultb['result'])){
            if(!empty($resultb['result'])){
                foreach($resultb['result'] as $key=>$v){
                    $arr[$v['symbol']] = $v['symbol'];
                }
            }
        }
        if(!empty($arr)){
            foreach($result as $key=>$v){
                $result[$key]['check'] = in_array($v['title'],$arr)?1:0;
                $result[$key]['entitle'] = in_array($v['title'],$arr)?'已配置':'未配置';

            }
        }
        $re['data']=$result;
        return $this->reDecodejson($re);
    }


    //取得大V列表
    public function getTradeUserConfigAll($type=0){
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        $result =  BinanceService::parseFromApi($uid,'getTradeUserConfigAll');
        $rearr = array();
        if(isset($result['result'])&&!empty($result['result'])){
            foreach(($result['result']) as $key=>$v){
                $rearr[$v['symbol']]=$v;
            }
            $result['result'] = $rearr;
        }
        if($type==1){
            return $result;
        }

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceApi:getTradeUserConfigAll';
        $opdata['opdes']='取得大V列表';
        CavenService::Oplog($opdata);
        return $this->reDecodejson($result);
        //print_r($result);
    }

    //取得大V列表
    public function getTradeUserConfig(){
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        $result =  BinanceService::parseFromApi($uid,'getTradeUserConfig');
        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceApi:getTradeUserConfig';
        $opdata['opdes']='取得用户配置';
        CavenService::Oplog($opdata);
        return $this->reDecodejson($result);
        //print_r($result);
    }


    public function getAllconfig(){
        $uid = $this->request->uid;
        $re = ['status' => 201, 'msg' => ''];
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        $re['status']=200;
        $re['userkey'] = $this->getUserKey($uid,1);
        $re['leaderconfig'] = $this->getLeaderConfigByUserId($uid,1);

        return $this->reDecodejson($re);
    }

    public function test6(){
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
//        date("Y-m-d",strtotime("+1 month",strtotime("2012-02-04")));

        $data['validTime'] = date("Y-m-d",strtotime("+1 month"));
        $data['isValid'] = 1;
        $result =  BinanceService::parseFromApi($uid,'auditUserKey',$data);
        return $this->reDecodejson($result);
    }

//取得单个大V配置列表

    public function getLeaderConfigByLeaderId(){
        if(empty($type)){
            $uid = $this->request->uid;
            if(empty($uid)){
                $re = ['status' => 201, 'msg' => '请先登陆'];
                return $this->reDecodejson($re);
            }
        }
        $mysiteleader = BinanceService::getMyLeaderConfig();
        $postField = 'symbol,leaderId';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if(empty($data['symbol'])){
            $data['symbol'] = 'A';
        }
        $result =  BinanceService::parseFromApi($uid,'getLeaderConfigByLeaderId',$data);

        $itemsformat = [];
        if(isset($result['result']['items'])&&!empty($result['result']['items'])){
            foreach($result['result']['items'] as $key=>$v){
                $itemsformat[$v['value']] = $v;
                $lederinfo = explode(',', $v['name']);
                if($v['disabled']){
                    $itemsformat[$v['value']]['checkInfo']='暂停';
                }
                $itemsformat[$v['value']]['leadername']=isset($mysiteleader[$v['id']]['nick'])?$mysiteleader[$v['id']]['nick']:$lederinfo[0];
                $itemsformat[$v['value']]['setdes']=isset($mysiteleader[$v['id']]['setdes'])?$mysiteleader[$v['id']]['setdes']:'';
                $itemsformat[$v['value']]['leadermoney']=str_replace('对标资金:','',trim($lederinfo[1]));
            }
            $itemsformat = BinanceService::array_sort($itemsformat,'check','desc');

            $result['result']['items'] = $itemsformat;
        }
        //print_r($result['result']['items']);
        if(isset($result['result']['tradeUserConfig'])&&!empty($result['result']['tradeUserConfig'])){
            $result['result']['tradeUserConfig']['leadername'] = '-';
            $result['result']['tradeUserConfig']['leadermoney'] = '-';

            if(!empty($itemsformat)){
                if(isset($itemsformat[$result['result']['tradeUserConfig']['acceptSource']])){
                    $result['result']['tradeUserConfig']['leadername'] = $itemsformat[$result['result']['tradeUserConfig']['acceptSource']]['leadername'];
                    $result['result']['tradeUserConfig']['leadermoney'] = $itemsformat[$result['result']['tradeUserConfig']['acceptSource']]['leadermoney'];
                    $result['result']['tradeUserConfig']['setdes'] = $itemsformat[$result['result']['tradeUserConfig']['acceptSource']]['setdes'];
                }
            }
        }

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceApi:getLeaderConfigByLeaderId';
        $opdata['opdes']='取得大神配置';
        CavenService::Oplog($opdata);
        return $this->reDecodejson($result);
    }

    public function getlider(){
        $list = BinanceService::getMyLeaderConfig();
        print_r($list);
    }


    //首页大神信息列表
    //取得大V列表

    public function getLeaderConfigByUserIdNewindex($uid=0,$type=0){
        if(empty($type)){
            $uid = $this->request->uid;
            if(empty($uid)){
                $re = ['status' => 201, 'msg' => '请先登陆'];
                return $this->reDecodejson($re);
            }
        }
        $whereb['member_id'] = $uid;
        $memberinfo = \app\api\model\Member::getWhereInfo($whereb);
        if(empty($memberinfo)){
            $re = ['status' => 201, 'msg' => '用户不存在'];
            return $this->reDecodejson($re);
        }
        $gid = $memberinfo['groupid'];
        $groupinfo = Tradegroup::find($gid);
        if(empty($groupinfo)||empty($groupinfo['leaderlist'])){
            $re = ['status' => 202, 'msg' => '会员信息错误！'];
            return $this->reDecodejson($re);
        }
        $leaderlist = $groupinfo['leaderlist'];
        $lederidlist=[];
        if(!empty($leaderlist)){
            $lederidlist = explode(',', $leaderlist);
        }

        $mysiteleader = BinanceService::getMyLeaderConfig();
        $indexleaderlist = $groupinfo['indexleaderlist'];
        if(!empty($indexleaderlist)){
            $indexlederidlist = explode(',', $indexleaderlist);
        }

        //$result =  BinanceService::parseFromApi($uid,'getLeaderConfigByUserId');

        $filename = "getLeaderConfigByUserId_".$uid;
        $result = read_static_cache($filename,10);
        if(empty($result)){
            $result =  BinanceService::parseFromApi($uid,'getLeaderConfigByUserId');

            $opdata = [];
            $opdata['userid'] = $uid;
            $opdata['optype'] = 1;
            $opdata['opaction']='BinanceApi:getLeaderConfigByUserId';
            $opdata['opdes']='取得大神配置';
            CavenService::Oplog($opdata);
            write_static_cache($filename,$result);
        }
        $itemsformat = [];
        $alldslist = [];
        if(isset($result['result']['items'])&&!empty($result['result']['items'])){
            foreach($result['result']['items'] as $key=>$v){
                if(!isset($v['realSumMoney'])){
                    $v['realSumMoney'] = '-';
                }else{
                    $v['realSumMoney'] = sprintf("%.2f",$v['realSumMoney']);
                }
                if(!isset($v['sumMoney'])){
                    $v['sumMoney'] = '-';
                }else{
                    $v['sumMoney'] = sprintf("%.2f",$v['sumMoney']);
                }
                $itemsformat[$v['value']] = $v;
                $lederinfo = explode(',', $v['name']);
                if($v['disabled']){
                    $itemsformat[$v['value']]['checkInfo']='暂停';
                }
                $itemsformat[$v['value']]['leadername']=isset($mysiteleader[$v['id']]['nick'])?$mysiteleader[$v['id']]['nick']:$lederinfo[0];;

                $itemsformat[$v['value']]['leadermoney']=trim($lederinfo[1]);
                if(!empty($indexleaderlist)){
                    if(in_array($v['id'],$indexlederidlist)){
                        $alldslist[$v['value']] = $itemsformat[$v['value']];
                    }
                }else{
                    $alldslist[$v['value']] = $itemsformat[$v['value']];
                }

                if(!in_array($v['id'],$lederidlist)){
                    unset($result['result']['items'][$key]);
                    unset($itemsformat[$v['value']]);
                    continue;
                }

            }
            //print_r($itemsformat);die;
            $itemsformat = BinanceService::array_sort($itemsformat,'check','desc');

            $result['result']['items'] = $itemsformat;
            $result['result']['dslist'] = $alldslist;
        }
        if(isset($result['result']['tradeUserConfig'])&&!empty($result['result']['tradeUserConfig'])){
            $result['result']['tradeUserConfig']['leadername'] = '-';
            $result['result']['tradeUserConfig']['leadermoney'] = '-';

            if(!empty($itemsformat)){
                if(isset($itemsformat[$result['result']['tradeUserConfig']['acceptSource']])){
                    $result['result']['tradeUserConfig']['leadername'] = $itemsformat[$result['result']['tradeUserConfig']['acceptSource']]['leadername'];
                    $result['result']['tradeUserConfig']['leadermoney'] = $itemsformat[$result['result']['tradeUserConfig']['acceptSource']]['leadermoney'];
                }
            }
        }
        //print_r($result['result']['items']);
        if($type==1){
            if(isset($result['result'])){
                return $result['result'];
            }
        }
        //print_r(1);
        return $this->reDecodejson($result);
    }


    public function getJyslist(){
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        $whereb = [];
        $whereb['member_id'] = $uid;
        $memberinfo = \app\api\model\Member::getWhereInfo($whereb);
        if(empty($memberinfo['channel'])){
            $memberinfo['channel']='sysdl';
        }
        $aginfo = CavenService::getAgentinfo($memberinfo['channel']);
        $retrajyslist[] = array('tradekey'=>0,'tradetitle'=>'Binance');
        $retrajyslist[] = array('tradekey'=>1,'tradetitle'=>'OKEX');

        if(!$aginfo){
            $re['data'] = $retrajyslist;

            return $this->reDecodejson($re);
        }
        $newtralist = [];
        if($aginfo['tradetype']!=''){
            $chanarr = explode(',',$aginfo['tradetype']);
            foreach($retrajyslist as $key=>$v){
                if(in_array($v['tradekey'],$chanarr)){
                    $newtralist[]=$v;
                }
            }
            $retrajyslist = $newtralist;
        }


        $re['data'] = $retrajyslist;

        return $this->reDecodejson($re);

    }


    //取得大V分类列表
    public function getLeaderConfigCategory(){
//        $uid = $this->request->uid;
//        if(empty($uid)){
//            $re = ['status' => 201, 'msg' => '请先登陆'];
//            return $this->reDecodejson($re);
//        }
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $result =  BinanceService::getLeaderConfigCategory(0);
        $re['data']=$result;
        $opdata = [];
        $opdata['userid'] = 0;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceApi:getLeaderConfigCategory';
        $opdata['opdes']='取得大V分类列表';
        CavenService::Oplog($opdata);
        return $this->reDecodejson($re);
        //print_r($result);
    }

    //通过分类列表更新大神分类信息
    public function updateCategory(){
//        $uid = $this->request->uid;
//        if(empty($uid)){
//            $re = ['status' => 201, 'msg' => '请先登陆'];
//            return $this->reDecodejson($re);
//        }
        //BinanceService::getLeaderConfigByCategory(0,3);
        //die;
        $sql = "SELECT * FROM `cd_leadercategory` where tid not in(1,2,3,20)";
        $list = Db::query($sql);
        if(!empty($list)){
            foreach($list as $key=>$v){
                $result =  BinanceService::getLeaderConfigByCategory(0,$v['tid']);
            }
        }

    }

    //通过分类列表更新大神分类信息
    public function getDsAll(){
        $result =  BinanceService::getLeaderConfigByCategory(54299,20);
        print_r($result);

    }


    public function getLeaderConfigByUserIdCatidindex($uid=0,$type=0){
        if(empty($type)){
            $uid = $this->request->uid;
            if(empty($uid)){
                $re = ['status' => 201, 'msg' => '请先登陆'];
                return $this->reDecodejson($re);
            }
        }
        $whereb['member_id'] = $uid;
        $memberinfo = \app\api\model\Member::getWhereInfo($whereb);
        if(empty($memberinfo)){
            $re = ['status' => 201, 'msg' => '用户不存在'];
            return $this->reDecodejson($re);
        }
        $gid = $memberinfo['groupid'];
        $groupinfo = Tradegroup::find($gid);
        if(empty($groupinfo)||empty($groupinfo['leaderlist'])){
            $re = ['status' => 202, 'msg' => '会员信息错误！'];
            return $this->reDecodejson($re);
        }

        $leaderlist = $groupinfo['leaderlist'];
        $lederidlist = explode(',', $leaderlist);

        $mysiteleader = BinanceService::getMyLeaderConfig();
        //$result =  BinanceService::parseFromApi($uid,'getLeaderConfigByUserId');

        $result =  BinanceService::getLeaderConfigByCategory($uid,20,1);

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceApi:getLeaderConfigByUserId';
        $opdata['opdes']='取得大V分配置信息';
        CavenService::Oplog($opdata);

        $itemsformat = [];
        $itemsformatlast = [];
        if(isset($result['result']['items'])&&!empty($result['result']['items'])){
            foreach($result['result']['items'] as $key=>$v){
                if(!isset($mysiteleader[$v['id']])){
                    continue;
                }


                if(!isset($v['realSumMoney'])){
                    $v['realSumMoney'] = '-';
                }else{
                    $v['realSumMoney'] = sprintf("%.2f",$v['realSumMoney']);
                }
                if(!isset($v['sumMoney'])){
                    $v['sumMoney'] = '-';
                }else{
                    $v['sumMoney'] = sprintf("%.2f",$v['sumMoney']);
                }
                if(!in_array($v['id'],$lederidlist)){
                    unset($result['result']['items'][$key]);
                    continue;
                }
                $itemsformat[$v['value']] = $v;
                $itemsformat[$v['value']]['catid'] = $mysiteleader[$v['id']]['catid'];
                $lederinfo = explode(',', $v['name']);
                if($v['disabled']){
                    $itemsformat[$v['value']]['checkInfo']='暂停';
                }
                $itemsformat[$v['value']]['leadername']=isset($mysiteleader[$v['id']]['nick'])?$mysiteleader[$v['id']]['nick']:$lederinfo[0];;

                $itemsformat[$v['value']]['leadermoney']=trim($lederinfo[1]);


                $itemsformatlast[$itemsformat[$v['value']]['catid']][$v['value']]=$itemsformat[$v['value']];


                /*
                if($v['tradeName']=='Binance'){
                    $itemsformatlast[$itemsformat[$v['value']][2]][$v['value']]=$itemsformat[$v['value']];
                }
                if($v['tradeName']=='Okx'){
                    $itemsformatlast[$itemsformat[$v['value']][3]][$v['value']]=$itemsformat[$v['value']];
                }
                */

            }



            $itemsformat = BinanceService::array_sort($itemsformatlast,'check','desc');

            if(!empty($itemsformat)){
                foreach($itemsformat as $key=>$v){
                    if(!empty($v)){
                        foreach($v as $key=>$vv){
                            if($vv['tradeName']=='Binance'){
                                // $itemsformat[2][$key]=$vv;
                            }
                            if($vv['tradeName']=='Okx'){
                                // $itemsformat[3][$key]=$vv;
                            }
                            $itemsformat[20][$key]=$vv;
                            if($vv['check']==1){
                                $itemsformat[1][$key]=$vv;
                            }
                        }
                    }
                }
            }

            $result['result']['items'] = $itemsformat;
        }
        //print_r($result['result']['items']);
        if(isset($result['result']['tradeUserConfig'])&&!empty($result['result']['tradeUserConfig'])){
            $result['result']['tradeUserConfig']['leadername'] = '-';
            $result['result']['tradeUserConfig']['leadermoney'] = '-';

            if(!empty($itemsformat)){
                if(isset($itemsformat[$result['result']['tradeUserConfig']['acceptSource']])){
                    $result['result']['tradeUserConfig']['leadername'] = $itemsformat[$result['result']['tradeUserConfig']['acceptSource']]['leadername'];
                    $result['result']['tradeUserConfig']['leadermoney'] = $itemsformat[$result['result']['tradeUserConfig']['acceptSource']]['leadermoney'];
                }
            }
        }

        if($type==1){
            if(isset($result['result'])){
                return $result['result'];
            }
        }
        //print_r(1);
        return $this->reDecodejson($result);
    }

    //取得大神栏目
    public function getCatlist(){
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $lan = $this->request->post('lan', 'zh');
        $limit  = $this->request->post('limit', 20, 'intval');
        $page   = $this->request->post('page', 1, 'intval');

        $wheremember = [];
        $wheremember['member_id'] = $uid;
        $memberinfo  =\app\api\model\Member::getWhereInfo($wheremember,"*");
        $channel = $memberinfo['channel'];

        $agentinfo = CavenService::checkAgent($channel,1);
        $decat = 1;
        if($agentinfo==203){
            $decat =0;
        }


        $field = '*';


        if($decat){
            $where=" status=1 AND tid in(1,20)";
            // $where[]=array('id','in',[1,20]);
        }else{
            $where['status'] = 1;
        }
        //echo $where;die;
        if($lan=='zh'){
            $field = ' tid as id,stitle as title,head_url';
        }
        if($lan=='en'){
            $field = ' tid as id,entitle as title,head_url';
        }
        $orderby = 'learder_desc asc';
        $res = LeadercategoryService::indexList($where,$field,$orderby,$limit,$page);

        return $this->ajaxReturn($this->successCode,'返回成功',$res);
    }

    //取得单个大神操作
    public function getLeaderConfigOrderByLeaderId(){
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }//
        $postField = 'leaderId,lan,page,debug';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if(empty($data['leaderId'])){
            $re = ['status' => 201, 'msg' => 'error'];
        }
        $lan = empty($data['lan'])?'zh':$data['lan'];
        $arrtitle =[];
        if($lan=='zh'){

            $arrtitle['closeBUYSHORT']='买入-平空';
            $arrtitle['openSELLSHORT']='卖出-开空';
            $arrtitle['closeSELLLONG']='卖出-平多';
            $arrtitle['openBUYLONG']='买入-开多';
        }
        if($lan=='en'){
            $arrtitle['closeBUYSHORT']='CLOSE-BUY-SHORT';
            $arrtitle['openSELLSHORT']='OPEN-SELL-SHORT';
            $arrtitle['closeSELLLONG']='CLOSE-SELL-LONG';
            $arrtitle['openBUYLONG']='OPEN-BUY-LONG';
        }

        //先查询用户本地信息
        $wherebb = [];
        $wherebb['userid'] = $uid;
        $wherebb['isvalid'] = 1;

        $info = Apiuser::getWhereInfo($wherebb);
        if(empty($info)||$info['validtime']<time()){
            $result['listTradeOrder']=[];
            $re = ['status' => 202, 'msg' => '无权限','result'=>$result];
            return $this->reDecodejson($re,$data['debug']);
        }


        $data['page'] = intval($data['page']);
        $data['page'] = $data['page']>0?$data['page']:1;
        $data['page']=$data['page']-1;
        $result =  BinanceService::parseFromApi($uid,'getLeaderConfigOrderByLeaderId',$data);

        if(isset($result['result']['listTradeOrder'])&&!empty($result['result']['listTradeOrder'])){
            foreach($result['result']['listTradeOrder'] as $key=>$v){
                $tikey = $v['order_type'].$v['side'].$v['position_side'];
                //
                $v['symbol']=str_replace('-SWAP','',$v['symbol']);
                $v['symbol']=str_replace('-','',$v['symbol']);
                $result['result']['listTradeOrder'][$key]['symbol'] = $v['symbol'];
                $result['result']['listTradeOrder'][$key]['create_time'] = substr($v['create_time'],2);
                $result['result']['listTradeOrder'][$key]['pzsm'] = $v['symbol'].'-'.$arrtitle[$tikey];
            }
        }

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceApi:getLeaderConfigOrderByLeaderId';
        $opdata['opdes']='取得单个大神操作记录';
        CavenService::Oplog($opdata);
        return $this->reDecodejson($result,$data['debug']);
    }

    //取得用户交易记录 最新100条
    public function getUserOrderlist(){
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }

        $userid  = $this->request->post('userid', 0, 'intval');
        if(empty($userid)){
            $userid  =$this->request->get('userid', 0, 'intval');
        }
        if(!empty($userid)){
            $agentinfo = CavenService::checkAgent($uid);
            if($agentinfo==203){
                return $this->ajaxReturn(202, '权限不够','');
            }
            $channel=$agentinfo['channel'];
            $wheremember = [];
            $wheremember['channel'] = $channel;
            $wheremember['member_id'] = $userid;
            $memberinfo  =\app\api\model\Member::getWhereInfo($wheremember,"*");
            if(empty($memberinfo)){
                return $this->ajaxReturn(200, '会员信息错误','');
            }

            $uid = $userid;
        }



        $result =  BinanceService::parseFromApi($uid,'getUserOrderlist');
        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceApi:getUserOrderlist';
        $opdata['opdes']='取得用户交易记录';
        CavenService::Oplog($opdata);
        if($result['status']==201){
            $result['status'] = 200;
            $result['data']=[];
        }
        return $this->reDecodejson($result);

    }


    //取得用户金额信息
    public function getUserStatMain(){
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        $re['data'] =  BinanceService::userStatMain($uid);
        return $this->reDecodejson($re);

    }


    //取得用户交易记录 最新100条
    public function transferHistory(){
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            $this->reDecodejson($re);
        }

        $userid  = $this->request->post('userid', 0, 'intval');
        if(empty($userid)){
            $userid  =$this->request->get('userid', 0, 'intval');
        }
        if(!empty($userid)){
            $agentinfo = CavenService::checkAgent($uid);
            if($agentinfo==203){
                return $this->ajaxReturn(202, '权限不够','');
            }
            $channel=$agentinfo['channel'];
            $wheremember = [];
            $wheremember['channel'] = $channel;
            $wheremember['member_id'] = $userid;
            $memberinfo  =\app\api\model\Member::getWhereInfo($wheremember,"*");
            if(empty($memberinfo)){
                return $this->ajaxReturn(202, '会员信息错误','');
            }

            $uid = $userid;
        }
        $result =  BinanceService::parseFromApi($uid,'transferHistory');
        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceApi:transferHistory';
        $opdata['opdes']='取得用户提现记录';
        CavenService::Oplog($opdata);
        if($result['status']==201){
            $result['status'] = 200;
            $result['data']=[];
        }
        $forarr = [];
        if(isset($result['result'])&&!empty($result['result']['listTransferHistory'])){
            foreach($result['result']['listTransferHistory'] as $key=>$v){
                $one = [];
                $one['asset'] = $v['asset'];
                $one['amount'] = $v['amount'];
                $one['typevalue'] = $v['typeValue'];
                $one['times'] = ($v['timestamp']/1000);
                $one['times'] = date('y-m-d H:i:s',$one['times']);
                $forarr[]=$one;
            }
            unset($result['result']['listTransferHistory']);
            $result['result']['listTransferHistory'] = $forarr;
        }

        return $this->reDecodejson($result);

    }


    //通过分类列表更新大神分类信息
    public function getdslistNewapi(){

        $result =  BinanceService::getLeaderConfigByCategory(0,20,1);
        print_r($result);

    }
}

