<?php
/*
 module:		会员管理
 create_time:	2020-08-09 18:28:55
 author:		
 contact:		
*/

namespace app\api\controller;

use app\admin\model\Pushmsg;
use app\admin\model\Tradegroup;
use app\api\model\Apiuser;
use app\api\service\BinanceService;
use app\api\service\CavenService;
use app\api\service\MemberFindMethodService;
use think\db\Where;
use think\facade\App;


class BinanceotherApi extends Common
{


    public static function _checkUser($uid){
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $whereb['member_id'] = $uid;
        $memberinfo = \app\api\model\Member::getWhereInfo($whereb);
        if(empty($memberinfo)){
            $re = ['status' => 201, 'msg' => '用户不存在'];
        }
        return $re;
    }
    //取得获取大神仓位
    public function getLeaderPos($liderid='',$type=0,$uid=0){

        $re = ['status' => 200, 'msg' => '','data'=>''];
        if($type==0) {
            $uid = $this->request->uid;
            if (empty($uid)) {
                $re = ['status' => 201, 'msg' => '请先登陆'];
                return $this->reDecodejson($re);
            }
            $ckresult = self::_checkUser($uid);
            if ($ckresult['status'] != 200) {
                return $this->reDecodejson($ckresult);
            }
            $postField = 'leaderId';
            $data = $this->request->only(explode(',', $postField), 'post', null);
        }else{
            $data['leaderId'] = $liderid;
        }
        $indata['leaderId'] = $data['leaderId'];
        if(substr($indata['leaderId'],-5)=='order'){
            $indata['leaderId'] = $indata['leaderId'].'-2';
        }
        //查询用户API情况

        //先查询用户本地信息
        $wherebb = [];
        $wherebb['userid'] = $uid;
        $wherebb['isvalid'] = 1;

        $info = Apiuser::getWhereInfo($wherebb);
        if(empty($info)||$info['validtime']<time()){
            $re['data'] = [];
            $re['status'] = 202;
            $re['msg'] = '无权限';
            return ($type==1)?$re:$this->reDecodejson($re);
        }
        $result =  BinanceService::parseFromApi($uid,'getLeaderPos',$indata);

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceotherApi:getLeaderPos';
        $opdata['opdes']='取得获取大神仓位';
        CavenService::Oplog($opdata);

        $re['kggl']=0;
        $re['dggl']=0;
        $re['zggl']=0;
        if(($result['status']!=200)){
            $re['status'] = 200;
            $re['msg'] = $result['msg'];
            //$re['data'] = $info;
        }else{
            if(isset($result['result'])&&!empty($result['result'])){
                foreach($result['result'] as $key=>$v){
                    if($v['positionAmt']==0){
                        unset($result['result'][$key]);
                        continue;
                    }
                    $re['zggl']=$re['zggl']+$v['leverageRatio'];
                    if($v['direction']=='LONG'){
                        $re['dggl']=$re['dggl']+$v['leverageRatio'];
                    }else{
                        $re['kggl']=$re['kggl']+$v['leverageRatio'];
                    }
                    $result['result'][$key]['biName'] = str_replace("-SWAP","",$result['result'][$key]['biName']);
                    $result['result'][$key]['biName'] = str_replace("-","",$result['result'][$key]['biName']);
                    $result['result'][$key]['entryPrice'] = sprintf("%.5f",$v['entryPrice']);
                }
            }
        }
        $re['kggl']=sprintf("%.2f",$re['kggl']);
        $re['dggl']=sprintf("%.2f",$re['dggl']);
        $re['zggl']=sprintf("%.2f",$re['zggl']);
        $re['date'] = date('y-m-d');
        $re['time'] = date('H:i:s');
        if($type==0){
            $re['leadinfo'] = BinanceService::getLeaderConfig($uid,$data['leaderId']);
        }

        $re['data'] = $result['result'];

        return ($type==1)?$re:$this->reDecodejson($re);
    }

    //获取某个用户的历史订单
    public function getMyOrder(){
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        $ckresult = self::_checkUser($uid);
        if($ckresult['status']!=200){
            return $this->reDecodejson($ckresult);
        }
        //先查询用户本地信息
        $where['userid'] = $uid;
        $info = Apiuser::getWhereInfo($where);
        if(!empty($info)){
            if($info['isvalid']!=1){
                $re = ['status' => 202, 'msg' => '您的API还在审核中'];
                return $this->reDecodejson($re);
            }
            $result =  BinanceService::parseFromApi($uid,'getMyOrder');

            $opdata = [];
            $opdata['userid'] = $uid;
            $opdata['optype'] = 1;
            $opdata['opaction']='BinanceotherApi:getMyOrder';
            $opdata['opdes']='获取某个用户的历史订单';
            CavenService::Oplog($opdata);

            if(($result['status']!=200)){
                $re['status'] = 200;
                $re['msg'] = $result['msg'];
                //$re['data'] = $info;
            }
            $re['data'] = $result['result'];
        }else{
            $re = ['status' => 204, 'msg' => '请先设置API接口'];
        }

        return $this->reDecodejson($re);
    }

    //获取某个用户的历史订单
    public function getNews(){
        $postField = 'num';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $num = intval($data['num'])==2?5:'';
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $uid = 0;
        $filename = "getNewsConfig";
        $list = read_static_cache($filename,300);
        if(!empty($list)){
            $newarr = $list;
            if($num>0){
                if(!empty($list)){
                    $newarr = [];
                    $i=1;
                    foreach($list as $key=>$v){
                        if($i<=$num){
                            $newarr[]=$v;
                        }else{
                            break;
                        }
                        $i++;
                    }
                }
            }
            $re['data'] = $newarr;
            return $this->reDecodejson($re);
        }
        $result =  BinanceService::parseFromApi($uid,'getNews');

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceotherApi:getNews';
        $opdata['opdes']='获取新闻';
        CavenService::Oplog($opdata);

        if(($result['status']!=200)){
            $re['status'] = $result['status'];
            $re['msg'] = $result['msg'];
            //$re['data'] = $info;
        }else{
            $newarr = $result['result'];
            write_static_cache($filename,$result['result']);
            if($num>0){
                if(!empty($result['result'])){
                    $newarr = [];
                    $i=1;
                    foreach($result['result'] as $key=>$v){
                        if($i<=$num){
                            $newarr[]=$v;
                        }else{
                            break;
                        }
                        $i++;
                    }
                }
            }
            $re['data'] = $newarr;
        }
        return $this->reDecodejson($re);
    }

    //获取用户盈利情况
    public function getUserPnl(){
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        $ckresult = self::_checkUser($uid);
        if($ckresult['status']!=200){
            return $this->reDecodejson($ckresult);
        }
        //先查询用户本地信息
        $where['userid'] = $uid;
        $info = Apiuser::getWhereInfo($where);
        $postField = 'year';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $indata['year'] = !isset($data['year'])||empty($data['year'])?date('Y'):$data['year'];
        if(!empty($info)){
            if($info['isvalid']!=1){
                $re = ['status' => 202, 'msg' => '您的API还在审核中'];
                return $this->reDecodejson($re);
            }
            $result =  BinanceService::parseFromApi($uid,'getUserPnl',$indata);
            $opdata = [];
            $opdata['userid'] = $uid;
            $opdata['optype'] = 1;
            $opdata['opaction']='BinanceotherApi:getUserPnl';
            $opdata['opdes']='获取用户盈利情况';
            CavenService::Oplog($opdata);

            if(($result['status']!=200)){
                $re['status'] = 200;
                $re['msg'] = $result['msg'];
                //$re['data'] = $info;
            }
            $re['data'] = $result['result'];
        }else{
            $re = ['status' => 204, 'msg' => '请先设置API接口'];
        }

        return $this->reDecodejson($re);
    }

    //获取用户仓位信息
    public function getUserAccount(){
        $type =  $this->request->post('type', '0', 'intval');
        $re = ['status' => 200, 'msg' => '','data'=>[]];
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        $ckresult = self::_checkUser($uid);
        if($ckresult['status']!=200){
            return $this->reDecodejson($ckresult);
        }
        $whereu['member_id'] =$uid;
        $memberinfo = \app\api\model\Member::getWhereInfo($whereu);
        if(empty($memberinfo)){
            $re = ['status' => 203, 'msg' => '请先注册'];
            return $this->reDecodejson($re);
        }
        $usinfo['totalmoney'] = $memberinfo['money']+$memberinfo['umoney'];

        $re['data']['user'] = $usinfo;
        $re['data']['apiuser']['status'] = '202';
        $re['data']['apiuser']['data'] = '202';
        //先查询用户本地信息
        $where['userid'] = $uid;
        $info = Apiuser::getWhereInfo($where);
        if(!empty($info)){
            if($info['isvalid']!=1){
                $apiuser['status'] = 202;
                $apiuser['msg'] = '您的API还在审核中';
                $re['data']['apiuser'] = $apiuser;
            }else{
                $result =  BinanceService::parseFromApi($uid,'getUserAccount');
                $opdata = [];
                $opdata['userid'] = $uid;
                $opdata['optype'] = 1;
                $opdata['opaction']='BinanceotherApi:getUserAccount';
                $opdata['opdes']='获取用户仓位信息';
                CavenService::Oplog($opdata);

                if(($result['status']!=200)){
                    $apiuser['status'] = $result['status'];
                    $apiuser['msg'] = $result['msg'];
                    //$re['data'] = $info;
                }else{

                    $apiuser['status'] = 200;
                    if($type==1){
                        if(isset($result['result']['mapUserSpotPosition'])){
                            unset($result['result']['mapUserSpotPosition']);
                        }
                        if(isset($result['result']['mapUserSwapPosition'])){
                            unset($result['result']['mapUserSwapPosition']);
                        }
                        if(isset($result['result']['totalBalance'])&&!empty($result['result']['totalBalance'])){
                            $result['result']['totalBalance'] = sprintf("%.3f",$result['result']['totalBalance']);
                        }
                        if(isset($result['result']['swapBalance'])&&!empty($result['result']['swapBalance'])){
                            $result['result']['swapBalance'] = sprintf("%.3f",$result['result']['swapBalance']);
                        }
                        if(isset($result['result']['spotBalance'])&&!empty($result['result']['spotBalance'])){
                            $result['result']['spotBalance'] = sprintf("%.3f",$result['result']['spotBalance']);
                        }
                    }else{
                        if(isset($result['result']['totalBalance'])&&!empty($result['result']['totalBalance'])){
                            $result['result']['totalBalance'] = sprintf("%.3f",$result['result']['totalBalance']);
                        }
                        if(isset($result['result']['swapBalance'])&&!empty($result['result']['swapBalance'])){
                            $result['result']['swapBalance'] = sprintf("%.3f",$result['result']['swapBalance']);
                        }
                        if(isset($result['result']['spotBalance'])&&!empty($result['result']['spotBalance'])){
                            $result['result']['spotBalance'] = sprintf("%.3f",$result['result']['spotBalance']);
                        }
                        if(isset($result['result']['mapUserSpotPosition'])&&!empty($result['result']['mapUserSpotPosition'])){
                            foreach($result['result']['mapUserSpotPosition'] as $key=>$v){
                                $v['positionAmt']=sprintf("%.4f",$v['positionAmt']);
                                if($v['positionAmt']<=0){
                                    unset($result['result']['mapUserSpotPosition'][$key]);
                                    continue;
                                }
                                $v['biName'] = str_replace('-SWAP','',$v['biName']);
                                $v['biName'] = str_replace('-','',$v['biName']);
                                $v['title']=$v['biName'].''.(($v['positionSide']=='LONG')?' 做多 '.$v['exchange_name']:' 做空 '.$v['exchange_name']);

                                $result['result']['mapUserSpotPosition'][$key]=$v;
                            }
                        }
                        if(isset($result['result']['mapUserSwapPosition'])&&!empty($result['result']['mapUserSwapPosition'])){
                            foreach($result['result']['mapUserSwapPosition'] as $key=>$v){
                                if($v['positionAmt']<=0){
                                    unset($result['result']['mapUserSwapPosition'][$key]);
                                    continue;
                                }
                                $v['biName'] = str_replace('-SWAP','',$v['biName']);
                                $v['biName'] = str_replace('-','',$v['biName']);
                                $v['title']=(($v['positionSide']=='LONG')?' 做多 '.$v['exchange_name']:' 做空 '.$v['exchange_name']);
                                $v['syl'] = ($v['unrealised_pnl_ratio']*100).'%';
                                $v['entryPrice'] = sprintf("%.4f",$v['entryPrice']);
                                $result['result']['mapUserSwapPosition'][$key]=$v;
                            }
                        }
                    }
                    $apiuser['msg'] = '';
                    $apiuser['data'] = $result['result'];
                }
                $re['data']['apiuser'] = $apiuser;
            }

        }else{
            $apiuser['status'] = '204';
            $apiuser['msg'] = '请先设置API接口';
            $re['data']['apiuser'] = $apiuser;
        }

        return $this->reDecodejson($re);
    }
    //取得推送信息
    public function getMsg(){
        $key = 'Md5apidkeyTrade';
        $data['type'] =  $this->request->post('type');
        $data['userid'] =  $this->request->post('userId');
        $data['msg'] =  $this->request->post('msg');
        $data['time'] =  $this->request->post('time');
        $data['sign'] =  $this->request->post('sign');
        $sign = md5(md5($data['type'].$data['userid'].$data['time'].$key));
        if($sign!=$data['sign']){
//            exit('签名不对');
        }

        if(!empty($data['type'])&&!empty($data['userid'])&&!empty($data['msg'])&&!empty($data['time'])){
            $data['time'] = substr($data['time'],0,10);
           Pushmsg::create($data);
        }
        $re = ['status' => 200, 'msg' => '','data'=>[]];
        return $this->reDecodejson($re);
    }


    public function getLeaderConfigByUserSet($uid=0,$type=0){
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
        $result =  BinanceService::parseFromApi($uid,'getLeaderConfigByUserId');

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceotherApi:getLeaderConfigByUserId';
        $opdata['opdes']='获取用户已配置大V信息';
        CavenService::Oplog($opdata);

        $itemsformat = [];
        if(isset($result['result']['items'])&&!empty($result['result']['items'])){
            foreach($result['result']['items'] as $key=>$v){
                if($v['check']!=1){
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
                $lederinfo = explode(',', $v['name']);
                if($v['disabled']){
                    $itemsformat[$v['value']]['checkInfo']='暂停';
                }
                $itemsformat[$v['value']]['leadername']=isset($mysiteleader[$v['id']]['nick'])?$mysiteleader[$v['id']]['nick']:$lederinfo[0];;

                $itemsformat[$v['value']]['leadermoney']=trim($lederinfo[1]);
                $itemsformat[$v['value']]['LeaderPos'] =$this->getLeaderPos($v['value'],1,$uid);
            }
            //$itemsformat = BinanceService::array_sort($itemsformat,'check','desc');

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

    function gettempdata(){
        $time =  date("Y-m-d",strtotime("-30 day"));
        $re['list_roi'] = [];
        //$re['list_pnl'] = [];
        for($i=1;$i<=30;$i++){
            $one[0]= strtotime("$time +{$i} day").'000';
            $one[1]=0;
            $re['list_roi'][]=$one;
            //$re['list_roi'][]=$one;
        }
        return $re;
    }
//获取大神走势
    public function getleaderTradeData(){
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $uid = $this->request->uid;
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        $indata['leaderId'] =  $this->request->post('leaderId');
        $indata['type'] =  $this->request->post('type');
        if(empty($indata['type'])||!in_array($indata['type'],array('roi','pnl'))){
            $indata['type'] = 'roi';
        }

        $result =  BinanceService::parseFromApi($uid,'leaderTradeData',$indata);

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceotherApi:leaderTradeData';
        $opdata['opdes']='获取大神走势';
        CavenService::Oplog($opdata);

        if(($result['status']!=200)||$result['msg']==':Internal Server Error'){
            $re['status'] = 200;

            $re['msg'] = $result['msg'];
            unset($result);
            $result['result']['balance']=0;
            $result['result']['all_roi']=0;
            $result['result']['all_pnl']=0;
            $result['result']['weekly_pnl']=0;
            $result['result']['weekly_roi']=0;
            $result['result']['list_all_roi']=0;
            $result['result']['list_all_pnl']=0;
            //$tempdata = $this->gettempdata();
            $result['result']['list_pnl']=[];
            $result['result']['list_roi']=[];
            $result['result']['winning_rate']=0;
            $result['result']['trade_day']=0;
            $result['result']['timer_per_day']=0;
            $re['data'] = $result['result'];
            return $this->reDecodejson($re);
            //$re['data'] = $info;
        }


        if(isset($result['result']['balance'])){
            $result['result']['balance']=sprintf("%.2f",$result['result']['balance']);
        }

        if(isset($result['result']['all_roi'])){
            $result['result']['all_roi']=sprintf("%.2f",($result['result']['all_roi']*100));
        }
        if(isset($result['result']['winning_rate'])){
            $result['result']['winning_rate']=sprintf("%.2f",($result['result']['winning_rate']*100));
        }
        if(isset($result['result']['all_pnl'])){
            $result['result']['all_pnl']=sprintf("%.2f",$result['result']['all_pnl']);
        }
        if(isset($result['result']['weekly_pnl'])){
            $result['result']['weekly_pnl']=sprintf("%.2f",$result['result']['weekly_pnl']);
        }
        if(isset($result['result']['weekly_roi'])){
            $result['result']['weekly_roi']=sprintf("%.2f",($result['result']['weekly_roi']*100));
        }
        if(isset($result['result']['list_all_roi'])){
            $result['result']['list_all_roi']=sprintf("%.2f",$result['result']['list_all_roi']);
        }
        if(isset($result['result']['list_all_pnl'])){
            $result['result']['list_all_pnl']=sprintf("%.2f",$result['result']['list_all_pnl']);
        }
        if(isset($result['result']['list_roi'])&&!empty($result['result']['list_roi'])){
            foreach($result['result']['list_roi'] as $key=>$v){

                $result['result']['list_roi'][$key][1]=(float)sprintf("%.2f",$v[1]*100);

            }
        }

        $re['data'] = $result['result'];
        return $this->reDecodejson($re);
    }


    public function getleaderTradeDataChart(){
        $re = ['status' => 200, 'msg' => '','data'=>''];

        $indata['leaderId'] =  $this->request->post('leaderId');
        $indata['type'] =  $this->request->post('type');
        //$token =  $this->request->post('token');
        if(empty($indata['type'])||!in_array($indata['type'],array('roi','pnl'))){
            $indata['type'] = 'roi';
        }
        $uid =54053;
        $result =  BinanceService::parseFromApi($uid,'leaderTradeData',$indata);

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 1;
        $opdata['opaction']='BinanceotherApi:leaderTradeData';
        $opdata['opdes']='获取大神走势外挂';
        CavenService::Oplog($opdata);

        if(($result['status']!=200)||$result['msg']==':Internal Server Error'){
            $re['status'] = 200;

            $re['msg'] = $result['msg'];
            unset($result);
            $result['result']['balance']=0;
            $result['result']['all_roi']=0;
            $result['result']['all_pnl']=0;
            $result['result']['weekly_pnl']=0;
            $result['result']['weekly_roi']=0;
            $result['result']['list_all_roi']=0;
            $result['result']['list_all_pnl']=0;
            //$tempdata = $this->gettempdata();
            $result['result']['list_pnl']=[];
            $result['result']['list_roi']=[];
            $result['result']['winning_rate']=0;
            $result['result']['trade_day']=0;
            $result['result']['timer_per_day']=0;
            $re['data'] = $result['result'];
            return $this->reDecodejson($re);
            //$re['data'] = $info;
        }


        if(isset($result['result']['balance'])){
            $result['result']['balance']=sprintf("%.2f",$result['result']['balance']);
        }

        if(isset($result['result']['all_roi'])){
            $result['result']['all_roi']=sprintf("%.2f",($result['result']['all_roi']*100));
        }
        if(isset($result['result']['winning_rate'])){
            $result['result']['winning_rate']=sprintf("%.2f",($result['result']['winning_rate']*100));
        }
        if(isset($result['result']['all_pnl'])){
            $result['result']['all_pnl']=sprintf("%.2f",$result['result']['all_pnl']);
        }
        if(isset($result['result']['weekly_pnl'])){
            $result['result']['weekly_pnl']=sprintf("%.2f",$result['result']['weekly_pnl']);
        }
        if(isset($result['result']['weekly_roi'])){
            $result['result']['weekly_roi']=sprintf("%.2f",($result['result']['weekly_roi']*100));
        }
        if(isset($result['result']['list_all_roi'])){
            $result['result']['list_all_roi']=sprintf("%.2f",$result['result']['list_all_roi']);
        }
        if(isset($result['result']['list_all_pnl'])){
            $result['result']['list_all_pnl']=sprintf("%.2f",$result['result']['list_all_pnl']);
        }
        if(isset($result['result']['list_roi'])&&!empty($result['result']['list_roi'])){
            foreach($result['result']['list_roi'] as $key=>$v){

                $result['result']['list_roi'][$key][1]=(float)sprintf("%.2f",$v[1]*100);

            }
        }

        $re['data'] = $result['result'];
        return $this->reDecodejson($re);
    }
}

