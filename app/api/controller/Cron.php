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

class Cron extends Common
{
    function te(){
        $adddata['mobile'] = 1111111;
                $adddata['type'] = 1;
                $adddata['userid'] = 10000000;
                $adddata['createtime'] = time();
                $adddata['updatetime'] = time();
        Apiuser::create($adddata);
        
    }
    
    //生成支付账单 作废
    function gojszddate(){
        $sql = "SELECT * FROM `cd_income_day` WHERE isbill=1 AND billstatus=0 AND yltjtype=1 ORDER BY createtime ASC limit 100";
        $list = Db::query($sql);
        return;
        if(!empty($list)){
            foreach($list as $key=>$v){
                $sql = "UPDATE `cd_income_day` set billstatus=1 WHERE id=".$v['id'];
                Db::query($sql);
                $memberinfo = \app\api\model\Member::find($v['userid']);
                if(empty($memberinfo['mobile'])){
                    $memberinfo['mobile'] = $memberinfo['email'];
                }
                $this->dozhangdan($v,$memberinfo);
            }
        }
    }

    //生成每日支付账单
    function gojszdgoday(){
        $i=1;
        $billmonth = date( 'Ymd');
        $bdateo=$bdate = date('Y-m-d',strtotime('-1 day'));
        $bdatb=$bdate = strtotime($bdate);
        $sql = "SELECT sum(income) as income,billmonth,userid ,`day`,billtype FROM `cd_income_day` WHERE  billstatus=0 AND billtype=1 AND yltjtype=1 AND `day`='".$bdate."' GROUP BY userid";
        $list = Db::query($sql);
        $bdate = date('Ymd',$bdate);
        if(!empty($list)){
            foreach($list as $key=>$v){
                $ordernumber = date('YmdHis').mt_rand(1000,9999);
                $sql = "UPDATE `cd_income_day` set billstatus=1,order_no='".$ordernumber."' WHERE userid='".$v['userid']."' AND billstatus=0 AND yltjtype=1 AND  `day`='".$bdatb."'";
                Db::query($sql);
                $memberinfo = \app\api\model\Member::find($v['userid']);
                if(empty($memberinfo['mobile'])){
                    $memberinfo['mobile'] = $memberinfo['email'];
                }
                $this->dozhangdan($v,$memberinfo,$ordernumber,$bdate);
            }
        }
    }


    //生成每周支付账单
    function gojszdgoweek(){
        $date= date('Y-m-d', strtotime('-1 monday', time()));
        $bdate=strftime('%G%V',strtotime($date));

        $sql = "SELECT sum(income) as income,billmonth,userid ,`day`,billtype FROM `cd_income_day` WHERE  billstatus=0 AND billtype=2 AND yltjtype=1 AND `dayweek`='".$bdate."' GROUP BY userid";
        $list = Db::query($sql);
        if(!empty($list)){
            foreach($list as $key=>$v){
                $ordernumber = date('YmdHis').mt_rand(1000,9999);
                $sql = "UPDATE `cd_income_day` set billstatus=1,order_no='".$ordernumber."' WHERE userid='".$v['userid']."' AND yltjtype=1 AND billstatus=0 AND `dayweek`='".$bdate."'";
                Db::query($sql);
                $memberinfo = \app\api\model\Member::find($v['userid']);
                if(empty($memberinfo['mobile'])){
                    $memberinfo['mobile'] = $memberinfo['email'];
                }
                $this->dozhangdan($v,$memberinfo,$ordernumber,$bdate);
            }
        }
    }

    //生成每周支付盈利抵扣
    function gojszdgoweekyl(){
        $date= date('Y-m-d', strtotime('-1 monday', time()));
        $bdate=strftime('%G%V',strtotime($date));

        $sql = "SELECT sum(income) as income,billmonth,userid ,`day`,billtype FROM `cd_income_day` WHERE  billstatus=0 AND billtype=2 AND yltjtype=2 AND `dayweek`<='".$bdate."' GROUP BY userid";
        //echo $sql;
        $list = Db::query($sql);
        if(!empty($list)){
            foreach($list as $key=>$v){
               // print_r($v);
                if($v['income']>0) {
                    $ordernumber = date('YmdHis') . mt_rand(1000, 9999);
                    $sql = "UPDATE `cd_income_day` set billstatus=1,isbill=2,order_no='" . $ordernumber . "' WHERE userid='" . $v['userid'] . "' AND yltjtype=2 AND billstatus=0 AND isbill=1 AND `dayweek`<='" . $bdate . "'";
                    Db::query($sql);
                    $memberinfo = \app\api\model\Member::find($v['userid']);
                    if (empty($memberinfo['mobile'])) {
                        $memberinfo['mobile'] = $memberinfo['email'];
                    }
                    $this->dozhangdan($v, $memberinfo, $ordernumber, $bdate);
                }
            }
        }
    }

    //生成月支付账单
    function gojszd(){
        $i=1;
        $billmonth = date( 'Ym', strtotime( 'last day of -'.$i.' months' ) );
        $sql = "SELECT sum(income) as income,billmonth,userid,`day`,billtype FROM `cd_income_day` WHERE  billstatus=0 AND yltjtype=1 AND billtype=3 AND billmonth='".$billmonth."' GROUP BY userid";
        $list = Db::query($sql);
        if(!empty($list)){
            foreach($list as $key=>$v){
                $ordernumber = date('YmdHis').mt_rand(1000,9999);
                $sql = "UPDATE `cd_income_day` set billstatus=1,order_no='".$ordernumber."' WHERE userid='".$v['userid']."' AND yltjtype=1 AND billstatus=0 AND billmonth='".$billmonth."'";
                Db::query($sql);
                $memberinfo = \app\api\model\Member::find($v['userid']);
                if(empty($memberinfo['mobile'])){
                    $memberinfo['mobile'] = $memberinfo['email'];
                }
                $this->dozhangdan($v,$memberinfo,$ordernumber,$billmonth);
            }
        }
    }

    //自动结算账单
    function aupaybill(){
        $timeb=$time=time();
        $time = $time-3600*2;
        $sql = "SELECT * FROM `cd_bill` WHERE paystatus=1 AND updateline<=".$time." AND lastpaytime<=".$timeb."  ORDER BY id  ASC limit 200";
        $list = Db::query($sql);
        if(!empty($list)){
            foreach($list as $key=>$v){
                $memberinfo = \app\api\model\Member::find($v['memberid']);
                if(!empty($memberinfo)){
                   $this->payBill($memberinfo,$v); 
                }else{
                    $sql="UPDATE `cd_bill` SET paystatus=3,updateline=".time()." WHERE id=".$v['id'];
                    Db::query($sql);
                }
                
            }
        }
    }

    function payBill($memberinfo,$billinfo){
        if(empty($memberinfo)){
            return json(['status'=>$this->errorCode,'msg'=>'会员信息错误']);
        }
        $member_id = $memberinfo['member_id'];
        $money = $billinfo['money'];
        // if($money>$memberinfo['umoney']||$memberinfo['aupay']==0){
        if($money>$memberinfo['umoney']){
            $sql = "SELECT COUNT(id) as total,sum(tznumber) as totz FROM `cd_bill` WHERE paystatus=1 AND type=2 AND memberid=".$member_id;
            $result = Db::query($sql);
            $sql = "UPDATE `cd_bill` SET updateline=".time().",tznumber=tznumber+1 WHERE id=".$billinfo['id'];
            Db::query($sql);
            if($result[0]['total']>=0){
                if($result[0]['totz']<=3) {
                    $msg = "您的金额不够结算账单，系统自动停止跟单功能，如要重新开启动，请支付完账单，系统自动开启跟单功能！";
                    sendToUser($memberinfo, $msg, '', 1);
                }
                CavenService::doUpApistatus(0,$member_id);
            }
            $msg = "您的金额不够结算账单，系统自动停止跟单功能，如要重新开启动，请支付完账单，系统自动开启跟单功能！";
            echo $member_id.'金额不够<br />';
            return;
        }

        if($memberinfo['umoney']<10){
            $msg = "您的账户余额已不足10U，请及时缴款，以免费影响您的跟单功能";
            sendToUser($memberinfo,$msg,'',1);
        }
        //进行支付操作
        $where = array();
        $where['member_id'] = $member_id;
        $dodec=\app\api\model\Member::setDes($where,'umoney',$money);
        if(!$dodec){
            $sql = "UPDATE `cd_bill` SET updateline=".time()." WHERE id=".$billinfo['id'];
            Db::query($sql);
            
            $msg = "您的金额不够结算账单，系统自动停止跟单功能，如要重新开启动，请支付完账单，系统自动开启跟单功能！";
            echo $member_id.$msg.'<br />';
            sendToUser($memberinfo,$msg,'',1);
            CavenService::doUpApistatus(0,$member_id);

            return;
        }
        $sql = "UPDATE `cd_member` set billmoney=billmoney-".$money." WHERE member_id=".$member_id;
        Db::query($sql);
        $des ='支付:'.$billinfo['des'];
        addPayLog($memberinfo,($memberinfo['umoney']-$money),$money,2,4,$des,$billinfo['ordernumber']);
        $wherebill['id']=$billinfo['id'];
        $updata['paystatus']=2;
        $updata['paytime']=time();
        Bill::update($updata,$wherebill);
        //CavenService::doPayBillorder($billinfo['id']);
        $msg = "您的".$billinfo['title'].'已支付完成，如有疑问请及时联系我们。';
        sendToUser($memberinfo,$msg,'',1);
        echo $billinfo['id'].'ok';
    }

//给用户分成
    function autopaycztc(){
        $sql = "SELECT * FROM `cd_chargeorder` WHERE status=2 AND dostatus=2  ORDER BY chargeorder_id  ASC limit 10";
        $list = Db::query($sql);
        if(!empty($list)){
            foreach($list as $key=>$v){
                CavenService::doPayCZorder($v['chargeorder_id']);
            }
        }

    }

    function dozhangdan($vinfo=[],$memberinfo=[],$ordernumber='',$billtitle=''){
        //$lasttime = date('Y-m');
        //$lasttime = $lasttime.'11';
        //$lasttime = strtotime($lasttime);
        $lasttime= time()+3600*24*5;
        
        if($vinfo['billtype']==2){
            $lasttime = $lasttime+3600*24*15;
        }
        if(!empty($vinfo)){

            if($vinfo['income']>0){

                //查出会员组交易提成

                $addbill = [];
                $addbill['mobile'] = $memberinfo['mobile'];
                $addbill['memberid'] = $memberinfo['member_id'];
                $addbill['addtime'] = time();
                $addbill['updateline'] = time();
                $addbill['billtype'] = $vinfo['billtype'];
                $addbill['type'] = 2;
                $addbill['status'] = 1;
                $addbill['income'] = $vinfo['income'];
                $addbill['channel'] = $memberinfo['channel'];

                $addbill['title'] = $billtitle.'收益账单';
                $addbill['des'] = $billtitle.'交易盈利:'.$vinfo['income'];
                $addbill['datekey'] = $billtitle;
                $addbill['billmonth'] = $billtitle;
                $addbill['lastpaytime'] = $lasttime;
                $where=[];
                $where['id'] = $memberinfo['groupid'];
                $ginfo = Tradegroup::Where($where)->field('id,profitpoint,isfree')->find();

                if(!empty($ginfo)){
                    $profitpoint = $ginfo['profitpoint'];
                    if($memberinfo['profitpoint']!=''){
                        if($memberinfo['profitpoint']>0){
                            $profitpoint = intval($memberinfo['profitpoint']);
                        }
                    }
                    $money = $vinfo['income']*$profitpoint/100;
                    $systemmoney = $money;
                    $addbill['money'] = $money;
                    if($money>0){
                        $addbill['paystatus'] = 1;
                        $addbill['ordernumber'] = empty($ordernumber)?date('YmdHis').mt_rand(1000,9999):$ordernumber;
                        $wherekey=[];
                        $wherekey['datekey'] = $addbill['datekey'];
                        $wherekey['memberid'] = $addbill['memberid'];
                        $wherekey['type'] = 2;
                        $wherekey['paystatus'] = 1;
                        if($ginfo['isfree']==0){
                            $wherekey['paystatus'] = 3;
                            $addbill['paystatus'] = 3;
                        }
                        if($systemmoney>0){
                            $billinfo = Bill::Where($wherekey)->field('*')->find();
                            if(empty($billinfo)){
                                Bill::create($addbill);
                                //更新income\
                                if($ginfo['isfree']==0){
                                    $msg = "您的".$addbill['title'].'已生成，请注意查收，目前您的会员组处于活动期，系统自动赠送服务，您无需支付此账单。';
                                }else {
                                    $msg = "您的" . $addbill['title'] . '已生成，请注意查收，并在相应的时间内支付，以免影响您的大神跟单功能。';
                                    $sql = "UPDATE `cd_member` set billmoney=billmoney+".$money." WHERE member_id=".$memberinfo['member_id'];
                                    Db::query($sql);
                                }
                                sendToUser($memberinfo,$msg,'',1);
                                //更新用户欠款

                            }

                        }


                    }
                }

            }
        }
    }

    //同步收益数据
    function cronGetyldate(){


        $key='lxf20220324';
        //dingTalk/getUserPnlByBat?id=18344&limit=2&md5=36784d18e0037be466e327440ecff16e&time=1648124787511
        $time = time();
        $time  = $time.'000';
        $filename = "getcronGetyldate";
        $itd = read_static_cache($filename,0);
        $uid = '0';
        $indata['id']=intval($itd);
        //echo $indata['id'];
        //$indata['id']=0;
        $indata['limit'] = 100;
        $indata['time'] = $time;
        $indata['md5'] = md5($key.$time);

        $result =  BinanceService::parseFromApi($uid,'getUserPnlByBat',$indata);
        $maxid = 0;
        if(($result['status']!=200)){
            $re['status'] = 200;
            $re['msg'] = $result['msg'];
            //$re['data'] = $info;
        }else{

            if(isset($result['result'])&&!empty($result['result'])){
                $i=0;
                foreach($result['result'] as $key=>$v){
                    $info = Incomeday::find($v['id']);
                    $maxid = ($maxid>$v['id'])?$maxid:$v['id'];
                    if(empty($info)){
                        $ondarr = [];
                        $ondarr['billmonth'] = date('Ym',strtotime($v['day']));
                        $ondarr['id'] = $v['id'];
                        $ondarr['userid'] = $v['userId'];
                        $ondarr['nickname'] = $v['nickName'];
                        $ondarr['income'] = $v['income'];
                        $ondarr['tradename'] = $v['tradeName'];
                        $ondarr['insttype'] = $v['instType'];
                        $ondarr['day'] = strtotime($v['day']);
                        $ondarr['dayweek'] = strftime('%G%V',strtotime($v['day']));
                        $ondarr['updatetimer'] = $v['updateTimer'];
                        $ondarr['createtime'] = strtotime($v['createTime']);
                        $ondarr['updatetime'] = strtotime($v['updateTime']);

                        $ondarr['isbill'] = $v['income']>0?1:2;
                        $memberinfo = \app\api\model\Member::find($v['userId']);
                        if(!empty($memberinfo)){
                            $ondarr['parentid'] = intval($memberinfo['parentid']);
                            $ondarr['twoid'] = intval($memberinfo['twoid']);
                            $ondarr['threeid'] = intval($memberinfo['threeid']);
                            $ondarr['channel'] = $memberinfo['channel'];

                        }else{
                            $ondarr['isbill'] =2;
                            continue;
                        }
                        $ondarr['yltjtype'] =1;
                        $billtype = 4;
                        if(isset($memberinfo['groupid'])){
                            //查找是否是收益会员
                            $groupinfo = Tradegroup::find($memberinfo['groupid']);
                            if(!empty($groupinfo)){
                                if($groupinfo['vippaytype']!=2){
                                    $ondarr['isbill'] =2;
                                }
                                $ondarr['yltjtype']= intval($groupinfo['yltjtype'])==2?2:1;
                                $billtype = $groupinfo['billtype'];
                                if($v['income']<0){
                                   if(intval($groupinfo['yltjtype'])==2){
                                       $ondarr['isbill'] =1;
                                   }
                                }
                            }
                        }
                        
                        $ondarr['billtype'] =$billtype;
                        if(isset($memberinfo['member_id'])&&$memberinfo['member_id']>0)
                        {
                        //更新用户交易所收益情况
                            $sql = "UPDATE `cd_member` set trademoney=trademoney+".$v['income']." WHERE member_id=".$memberinfo['member_id'];
                            Db::query($sql);
                            Incomeday::create($ondarr);
                        }
                        //$this->dozhangdan($ondarr,$memberinfo);
                    }else{
                        $ondarr = [];
                        $ondarr['id'] = $v['id'];
                        $ondarr['billmonth'] = date('Ym',strtotime($v['day']));
                        $ondarr['userid'] = $v['userId'];
                        $ondarr['nickname'] = $v['nickName'];
                        $ondarr['income'] = $v['income'];
                        $ondarr['tradename'] = $v['tradeName'];
                        $ondarr['insttype'] = $v['instType'];
                        $ondarr['day'] = strtotime($v['day']);
                        $ondarr['updatetimer'] = $v['updateTimer'];
                        $ondarr['createtime'] = strtotime($v['createTime']);
                        $ondarr['updatetime'] = strtotime($v['updateTime']);
                        $ondarr['isbill'] = $v['income']>0?1:2;
                        $where=[];
                        $where['id']=$v['id'];

                        //Incomeday::update($ondarr,$where);
                    }
                }
            }
        }
        write_static_cache($filename,$maxid);
    }
    //处理VIP数据
    function checkvip(){
        $time = time()+3600*24*7;
        //a.validtime<".$time."
        $sql = "SELECT a.*,b.* FROM `cd_apiuser` a left join `cd_member` b on a.userid=b.member_id WHERE a.validtime<".$time." AND a.isvalid=1";
        $list = Db::query($sql);

        if(!empty($list)){
            foreach($list as $key=>$v){
                $result = CavenService::doVipupdate($v);
                if($result['status']==1){
                    $sql = "UPDATE `cd_apiuser` SET validtime=".$result['validtime']." WHERE userid=".$v['userid'];
                    Db::query($sql);
                    echo $result['msg'].'会员'.$v['mobile'].'-'.$v['email'].'-'.$v['userid'].'<br />';
                }else{
                    $msg = date('Y-m-d H:i:s').'会员'.$v['mobile'].'-'.$v['email'].'自动续费失败!'.$result['msg'];
                    Log::error($msg);
                    echo $msg;
                    //Log::error($v);
                }
            }
        }
    }

    function aucheck(){
        $time = time();
        $sql = "SELECT * FROM `cd_apiuser` WHERE isvalid=0 AND tradename=1 AND validtime>".$time." order by id desc limit 100";
        $result  = Db::query($sql);
        if(!empty($result)){
            foreach($result as $key=>$v){
                $updata=[];
                $updata['validTime']=date('Y-m-d',$v['validtime']);
                $updata['isValid']=1;
                $result =\app\api\service\BinanceService::auditUser($updata,$v['userid']);
                $sql = "UPDATE `cd_apiuser` SET isvalid=1 WHERE id=".$v['id'];
                $result = Db::query($sql);
            }
        }

    }


    function getqdyhzj(){
        $sql = "SELECT channel FROM `cd_agent` where channel in('t3','zy','sc')";
        $result = Db::query($sql);
        if(!empty($result)){
            foreach($result as $key=>$v){
                $this->getZjbychannel($v['channel']);
            }
        }
    }

    function getZjbychannel($channel=''){

        $data['channelName'] = $channel;
        $result =  BinanceService::parseFromApi('','getUserBalanceByChannel',$data);
        if($result['status']=200){
            if(isset($result['result'])&&!empty($result['result'])){
                foreach($result['result'] as $key=>$v){
                    $usdt =$v['total'];
                    $upda= [];
                    $upda['usdt'] = round($usdt,2);
                    $upda['swapusdt'] = round($v['balance_swap'],2);
                    $upda['spotusdt'] = round($v['balance_spot'],2);
                    $upda['zcuptime'] = time();
                    $where=[];
                    $where['member_id'] = $v['user_id'];
                    \app\api\model\Member::update($upda,$where);
                }
            }
        }
    }

    //同步用户资金旧接口情况

    function yhzj(){
        $time = time();
        //$sql = "SELECT b.member_id,b.channel FROM `cd_agent` a LEFT JOIN `cd_member` b on a.channel=b.channel WHERE a.maxmoney>0  order by zcuptime asc limit 20";
        $sql = "SELECT userid FROM `cd_apiuser`  WHERE isvalid=1 AND validtime>".time()." order by zcuptime asc limit 30";

        $resultb  = Db::query($sql);
        //print_r($resultb);die;
        if(!empty($resultb)){
            foreach($resultb as $key=>$v){
                $result =  BinanceService::parseFromApi($v['userid'],'userStatMain');
                if(!empty($result)&&$result['status']==200){
                    if(isset($result['result'])&&!empty($result['result'])){
                        $usdt = $result['result']['balance'];
                        $upda= [];
                        $upda['usdt'] = round($usdt,2);
                        $upda['swapusdt'] = round($result['result']['balanceSwap'],2);
                        $upda['spotusdt'] = round($result['result']['balanceSpot'],2);
                        $upda['zcuptime'] = time();
                        $where=[];
                        $where['member_id'] = $v['userid'];
                        //print_r($upda);
                        \app\api\model\Member::update($upda,$where);

                    }
                }else{

                    $upda= [];
                    $upda['usdt'] = 0;
                    $upda['swapusdt'] = 0;
                    $upda['spotusdt'] = 0;
                    $upda['zcuptime'] = $time;
                    $where=[];
                    $where['member_id'] = $v['userid'];
                    \app\api\model\Member::update($upda,$where);
                }

                $wherb['userid'] = $v['userid'];
                $updb['zcuptime'] = time();
                Apiuser::update($updb,$wherb);
            }
        }

    }


    //同步用户资金情况

    function yhzjold(){
        $time = time();
        //$sql = "SELECT b.member_id,b.channel FROM `cd_agent` a LEFT JOIN `cd_member` b on a.channel=b.channel WHERE a.maxmoney>0  order by zcuptime asc limit 20";
        $sql = "SELECT b.member_id,b.channel FROM `cd_agent` a LEFT JOIN `cd_member` b on a.channel=b.channel WHERE a.maxmoney>0  order by zcuptime asc limit 20";

        $resultb  = Db::query($sql);
        //print_r($resultb);die;
        if(!empty($resultb)){
            foreach($resultb as $key=>$v){
                $result =  BinanceService::parseFromApi($v['member_id'],'userStatMain');
                if(!empty($result)&&$result['status']==200){
                    if(isset($result['result'])&&!empty($result['result'])){
                        $usdt = $result['result']['balance'];
                        $upda= [];
                        $upda['usdt'] = number_format($usdt,2);
                        $upda['swapusdt'] = number_format($result['result']['balanceSwap'],2);
                        $upda['spotusdt'] = number_format($result['result']['balanceSpot'],2);
                        $upda['zcuptime'] = time();
                        $where=[];
                        $where['member_id'] = $v['member_id'];
                        \app\api\model\Member::update($upda,$where);
                    }
                }else{

                    $upda= [];
                    $upda['usdt'] = 0;
                    $upda['zcuptime'] = $time;
                    $where=[];
                    $where['member_id'] = $v['member_id'];
                    \app\api\model\Member::update($upda,$where);
                }
            }
        }

    }

    function countzjtj(){
        $time = time();
        $sql = "SELECT channel,id FROM `cd_agent` WHERE maxmoney>0";
        $resultb  = Db::query($sql);
        if(!empty($resultb)){
            foreach($resultb as $key=>$v){
                $sql = "SELECT SUM(spotusdt) as total FROM `cd_member` WHERE channel='".$v['channel']."'";
                $re = Db::query($sql);
                //print_r($re);
                if(!empty($re)){
                    if(isset($re[0]['total'])){
                        $to = $re[0]['total'];
                        $sql = "UPDATE `cd_agent` set totalmoney = '".$to."' WHERE id='".$v['id']."'";
                        Db::query($sql);
                    }
                }
            }
        }

    }

    function docz(){
        $data['tradeName'] = 'binance';
        $re=[];
        $result =  BinanceService::parseFromApi('','getFinishList',$data);

        print_r($result);

        die;

        $orderinfo=[];
        $orderinfo['member_id']=54053;
        $orderinfo['domember_id']=54134;
        $orderinfo['channel']='sysdl';
        ActiveService::doActive($orderinfo,2);
    }
}