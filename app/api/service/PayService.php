<?php
/*
 module:		会员列表
 create_time:	2020-05-10 13:00:18
 author:		
 contact:		
*/

namespace app\api\service;
use app\admin\model\Agent;
use app\admin\model\Chargeorder;
use think\facade\Db;
use xhadmin\CommonService;
use app\api\model\Member;
use app\admin\model\Moneylog;

class PayService extends CommonService {


    public static function goCheckPay($memberid,$type='inmoney',$txid='',$pt='Okx',$paywayinfo,$channel=''){
        $re = ['status' => 201, 'msg' => '暂时未到账，请过一会儿再试','data'=>[]];
        if(empty($txid)){
            $re['msg']='txid不能为空';
            return $re;
        }
        //查询这个txid是否使用过
        $txid = str_replace(' ','',$txid);
        $wherebb['tradeno']=trim($txid);
        $info = Chargeorder::where($wherebb)->field('*')->find();
        if(!empty($info)){
            $re['msg']='此txid 已充值过，请确认后再提交';
            return $re;
        }
        $result = self::getPayFromApi($txid,$pt,$channel);
        if(!empty($result)){
            if($result['status']==204){
                $re['msg']=$result['msg'];
                return $re;
            }
            if($result['status']!=2){
                $re['msg']='请等待确认成功后，再提交';
                return $re;
            }
            $wherebb['tradeno']=$txid;
            $info = Chargeorder::where($wherebb)->field('*')->find();
            if(!empty($info)){
                $re['msg']='此txid 已充值过，请确认后再提交';
                return $re;
            }else{
                //处理入账
                $ts = $result['dateline'];
                $time = time();

                if(($time-$ts)>(3600*48)){
                    $re['msg']='充值记录已超过48小时，请联系管理员处理';
                    return $re;
                }
                $result['payway'] = $paywayinfo['payway_id'];
                return self::addDeposit($result,$memberid);

            }
        }
        return $re;
    }

    public static function getCzotherinfo($money,$memberinfo){
        if(is_numeric($memberinfo)){
            $where['member_id']=$memberinfo;
            //utype ==1 普通会员 2 代理会员
            $filed="*";
            $memberinfo  =Member::getWhereInfo($where,$filed);
            if(empty($memberinfo)){
                return json(['status'=>'201','msg'=>'用户信息获取失败']);
            }
        }
        $systemmoney = $money;
        $where=[];
        $where['channel'] = $memberinfo['channel'];
        $ageninfo = Agent::Where($where)->field('*')->find();
        if(!empty($ageninfo)){

            $agenmoney = $money*$ageninfo['paygetpoint']/100;
            $order_data['channelmoney'] = $agenmoney;
            $systemmoney = $systemmoney-$agenmoney;
            //
            if(!empty($ageninfo['twochannel'])&&!empty($ageninfo['twopaygetpoint'])){
                $twoagenmoney = $money*$ageninfo['twopaygetpoint']/100;
                $order_data['twochannelmoney'] = $twoagenmoney;
                $order_data['twochannel'] = $ageninfo['twochannel'];
                $systemmoney = $systemmoney-$twoagenmoney;
            }

            if(!empty($ageninfo['threechannel'])&&!empty($ageninfo['threepaygetpoint'])){
                $threeagenmoney = $money*$ageninfo['threepaygetpoint']/100;
                $order_data['threechannelmoney'] = $threeagenmoney;
                $order_data['threechannel'] = $ageninfo['threechannel'];
                $systemmoney = $systemmoney-$threeagenmoney;
            }
        }
        $topuser[0]=intval($memberinfo['parentid']);
        $topuser[1]=intval($memberinfo['twoid']);
        $topuser[2]=intval($memberinfo['threeid']);
        $order_data['parentid'] = $topuser[0];
        $order_data['twoid'] = $topuser[1];
        $order_data['threeid'] = $topuser[2];
        $topmoney = CavenService::getTcByChannel($topuser,$money,$ageninfo['buyvipset']);
        $topmoney[0] = isset($topmoney[0])?$topmoney[0]:0;
        $topmoney[1] = isset($topmoney[1])?$topmoney[1]:0;
        $topmoney[2] = isset($topmoney[2])?$topmoney[2]:0;
        $systemmoney = $systemmoney-$topmoney[0]-$topmoney[1]-$topmoney[2];
        $order_data['parentmoney'] = $topmoney[0];
        $order_data['twomoney'] = $topmoney[1];
        $order_data['threemoney'] = $topmoney[2];
        $order_data['systemmoney'] = $systemmoney;
        $order_data['channel'] = $memberinfo['channel'];
        $order_data['mobile'] = $memberinfo['mobile'];
        return $order_data;
    }

    //添加充值订单
    public static function addDeposit($orderinfo,$member_id){
        $where['member_id']=$member_id;
        //utype ==1 普通会员 2 代理会员
        $filed="*";
        $memberinfo  =Member::getWhereInfo($where,$filed);
        if(empty($memberinfo)){
            return json(['status'=>'201','msg'=>'用户信息获取失败']);
        }
        $ordernumber = date('YmdHis').mt_rand(1000,9999);
        $time = time();
        $order_data = array(
            'member_id'=>$memberinfo['member_id'],
            'mobile'=>$memberinfo['mobile'],
            'username'=>$memberinfo['mobile'],
            'money'=>$orderinfo['money'],
            'ordernumber'=>$ordernumber,
            'payway'=>$orderinfo['payway'],
            'des'=>$orderinfo['des'],
            'status'=>$orderinfo['status'],
            'dostatus'=>1,
            'dateline'=>$orderinfo['dateline'],
            'tradeno'=>trim($orderinfo['tradeno']),
            'updateline'=>$time,
            'channel'=>$memberinfo['channel'],
        );

        //生成提成信息

        $money = $order_data['money'];

        //$systemmoney = $money;
        if($money>0){
            $otherinfo = self::getCzotherinfo($money,$memberinfo);
            if(!empty($otherinfo)&&is_array($otherinfo)){
                $order_data = array_merge($order_data,$otherinfo);
            }

        }
        try {
            $res = Chargeorder::create($order_data);
            $order_id = $res->chargeorder_id;
            if ($order_id){
                CavenService::doChargeorder($order_id);
                return ['status'=>'200','msg'=>'成功充值'.$order_data['money']."U"];
            }else{
                return ['status'=>'201','msg'=>'充值失败！'];
            }
        }catch(\Exception $e){
            return ['status'=>'201','msg'=>'充值失败！'];
        }
    }


    public static function getPayFromApi($txid='',$tradeName='Binance',$channel){

        $data['tradeName'] = $tradeName;
        $data['channel'] = $channel;
        $re=[];
        $result =  BinanceService::parseFromApi('','getFinishList',$data);
        if($result['status']==200){
            if(!empty($result['result'])){
                foreach($result['result'] as $key=>$v){
                    $v['txId'] = str_replace(' ','',$v['txId']);
                    $v['txId'] =trim($v['txId']);
                    if($v['txId']==$txid){
                        $re['money'] = $v['amount'];
                        $re['dateline'] = substr($v['ts'],0,10);
                        $re['tradeno']=$v['txId'];
                        $re['status'] = 2;
                        $re['des']=$v['depositId'];
                        break;
                    }
                }
            }
        }
        return $re;
    }


    public static function getPayFrombinance($type='inmoney',$txid=''){

        $data['tradeName'] = 'binance';
        $re=[];
        $result =  BinanceService::parseFromApi('','getFinishList',$data);
        if($result['status']==200){
            if(!empty($result['result'])){
                foreach($result['result'] as $key=>$v){
                    $v['txId'] = str_replace(' ','',$v['txId']);
                    $v['txId'] =trim($v['txId']);
                    if($v['txId']==$txid){

                        $re['money'] = $v['amount'];
                        $re['dateline'] = substr($v['ts'],0,10);
                        $re['tradeno']=$v['txId'];
                        $re['status'] = 2;
                        $re['des']=$v['depositId'];
                        break;
                    }
                }
            }
        }
        return $re;
    }
    public static function getPayFromOkx($type='inmoney',$txid=''){
        return ['status'=>'204','msg'=>'暂未开通'];
        $url = 'http://okapi.com/apiok.php';
        $indata['type'] = $type;
        $indata['time'] = time();
        $key = 'okapipasslxf';
        $indata['sign'] =md5($type.$indata['time'].$key);
        $result = curl_post($url,$indata);
        $re=[];
        if(!empty($result)){
            $result = json_decode($result, true);
            if($result['status']==200){
                $list = $result['data'];
                if(!empty($list)&&$type=='inmoney'){
                    foreach($list as $key=>$v){
                        if($v['txId']==$txid){
                            $re['money'] = $v['amt'];
                            if($v['chain']=='USDT-Omni'){
                                //$re['payway'] = 2;
                                $re['des']='OKX账号:'.$v['from'];
                            }
                            if($v['chain']=='USDT-TRC20'){
                                //$re['payway'] = 3;
                                $re['des']='充值地址:'.$v['to'];
                            }
                            $re['dateline'] = substr($v['ts'],0,10);
                            $re['tradeno']=$v['txId'];
                            $re['status'] = $v['state'];
                            break;
                        }
                    }
                }else{
                    $relist = [];
                    if($type=='address'){
                        if(!empty($list)){
                            foreach($list as $key=>$v){
                                if($v['chain']=='USDT-TRC20'){
                                    //$re['payway'] = 3;
                                    $relist[]=$v;
                                    break;

                                }
                            }
                        }
                        return $relist;
                    }
                }
            }
        }
        return $re;

    }

}

