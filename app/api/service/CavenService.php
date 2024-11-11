<?php
/*
 module:		会员列表
 create_time:	2020-05-10 13:00:18
 author:		
 contact:		
*/

namespace app\api\service;
use app\api\model\Apiuser;
use app\admin\model\Agent;
use app\admin\model\Apilog;
use app\admin\model\Bill;
use app\admin\model\Chargeorder;
use app\admin\model\Financial;
use app\admin\model\Productset;
use app\admin\model\Tradegroup;
use app\admin\model\Withdrawal;
use app\admin\service\ApiuserService;
use app\admin\service\MeadmoneylogService;
use think\facade\Db;
use xhadmin\CommonService;
use app\api\model\Member;
use app\admin\model\Moneylog;
use think\facade\App;
require_once App::getRootPath() . 'extend/QRCode/phpqrcode.php';
class CavenService extends CommonService {


    public static function getTcpoint($topuser,$price,$typekey='taskset'){
        $rearr = [0,0,0];
        if($topuser[0]==0){
            return $rearr;
        }
        $configdata =  getSetConfig($typekey,3);
        if(!empty($configdata)&&is_array($configdata)){
            foreach($configdata as $key=>$v){
                if(!empty($topuser[$key])){
                    $rearr[$key]=intval($v)/100*$price;
                }else{
                    $rearr[$key]=0;
                }
            }
        }
        return $rearr;
    }

    public static function getTcByChannel($topuser,$price,$typekey,$type=1){
        $rearr = [0,0,0];
        if($type==1){
            if($topuser[0]==0){
                return $rearr;
            }
            $configdata =  explode('|',$typekey);
            if(!empty($configdata)&&is_array($configdata)){
                foreach($configdata as $key=>$v){
                    if(!empty($topuser[$key])){
                        $rearr[$key]=intval($v)/100*$price;
                    }else{
                        $rearr[$key]=0;
                    }

                }
            }
        }else{
            $configdata =  explode('|',$typekey);
            if(!empty($configdata)&&is_array($configdata)){
                foreach($configdata as $key=>$v){
                    $rearr[$key]=$v;
                }
            }
        }

        return $rearr;
    }

    public  static function getTopuser($uuid,$memberid=1){
        $data  = array();
        $data['member_id'] = $uuid;
        $res  = Member::getWhereInfo($data,'member_id,mobile,avatar,parentid,twoid');
        if($memberid){
            return !empty($res)&&isset($res['member_id'])?$res['member_id']:0;
        }
        return $res;
    }


    public static function getAgentinfo($channel='sysdl'){
        $wherebb['channel'] = $channel;
        return Db::name('agent')->where($wherebb)->field('*')->find();
    }
    /*
     * @Description  此方法已废弃
     * @param (输入参数：)  {int}      taskrecordid  任务记录ID
     * @param (输入参数：)  {int}      $msg  说明
     *
     *
     */
    public static function doPayBillorder($taskrecordid){
        //VIP续费|1|success,收益结算|2|warning,其它|3|primary
        $typearr = array(
            '1'=>'VIP续费',
            '2'=>'收益结算',
            '3'=>'其它'
        );

        $doinfo = Bill::find($taskrecordid);

        if(!empty($doinfo)){
            $membxf = Member::find($doinfo['member_id']);
            if($doinfo['status']==1){//开始结算

                $errmoney = 0;
                if($doinfo['parentmoney']>0 && $doinfo['parentid']>0){
                    $where = array();

                    $where['member_id'] = $doinfo['parentid'];
                    $memberinfo = Member::find($doinfo['parentid']);
                    if(!empty($memberinfo)){
                        $des = '下级('.hiden_mymoblie($membxf['mobile']).'),'.$typearr[$doinfo['type']].':'.$doinfo['ordernumber'].'提成';
                        try {
                            $sql = "UPDATE `cd_member` SET yjmoney=yjmoney+".$doinfo['parentmoney']." WHERE member_id=".$memberinfo['member_id'];
                            Db::query($sql);
                            $blance = $memberinfo['umoney']+$doinfo['parentmoney'];
                            addPayLog($memberinfo,$blance,$doinfo['parentmoney'],1,2,$des);
                        } catch (\Exception $e) {
                            return json(['status'=>'201','msg'=>$e->getMessage()]);
                        }
                    }else{
                        $errmoney=$errmoney+$doinfo['parentmoney'];
                    }

                }

                if($doinfo['twoprice']>0 && $doinfo['twoid']>0){
                    //给做任务会员结算
                    $where = array();
                    $where['member_id'] = $doinfo['twoid'];
                    $memberinfo = Member::find($doinfo['twoid']);
                    $des = '下级('.hiden_mymoblie($membxf['mobile']).'),'.$typearr[$doinfo['type']].':'.$doinfo['ordernumber'].'二级提成';
                    if(!empty($memberinfo)) {
                        try {
                            $sql = "UPDATE `cd_member` SET yjmoney=yjmoney+".$doinfo['twoprice']." WHERE member_id=".$memberinfo['member_id'];
                            Db::query($sql);
                            $blance = $memberinfo['umoney']+$doinfo['twoprice'];
                            addPayLog($memberinfo, $blance, $doinfo['twoprice'], 1, 5, $des);
                        } catch (\Exception $e) {
                            return json(['status' => '201', 'msg' => $e->getMessage()]);
                        }
                    }else{
                        $errmoney=$errmoney+$doinfo['twoprice'];
                    }
                }
                if($doinfo['threeprice']>0 && $doinfo['threeid']>0){
                    //给做任务会员结算
                    $where = array();
                    $where['member_id'] = $doinfo['threeid'];
                    $memberinfo = Member::find($doinfo['threeid']);
                    if(!empty($memberinfo)) {
                        $des = '下级('.hiden_mymoblie($membxf['mobile']).'),'.$typearr[$doinfo['type']].':'.$doinfo['ordernumber'].'三级提成';
                        try {
                            $sql = "UPDATE `cd_member` SET yjmoney=yjmoney+".$doinfo['threeprice']." WHERE member_id=".$memberinfo['member_id'];
                            Db::query($sql);
                            $blance = $memberinfo['umoney']+$doinfo['threeprice'];
                            addPayLog($memberinfo, $blance, $doinfo['threeprice'], 1, 5, $des);
                        } catch (\Exception $e) {
                            return json(['status' => '201', 'msg' => $e->getMessage()]);
                        }
                    }else{
                        $errmoney=$errmoney+$doinfo['threeprice'];
                    }
                }

                if($doinfo['channelmoney']>0 && !empty($doinfo['channel'])){
                    //给做任务会员结算
                    $where = array();
                    $where['channel'] = $doinfo['channel'];
                    $memberinfo = Agent::Where($where)->field('*')->find();

                    if(!empty($memberinfo)) {
                        $des = '用户('.hiden_mymoblie($membxf['mobile']).'),'.$typearr[$doinfo['type']].':'.$doinfo['ordernumber'].'提成';
                        try {
                            $money = $memberinfo['money']+$doinfo['channelmoney'];
                            $allmoney = $memberinfo['allmoney']+$doinfo['channelmoney'];
                            $sql = "UPDATE `cd_agent` SET money=".$money.",allmoney=".$allmoney." WHERE id=".$memberinfo['id'];
                            Db::query($sql);
                            $data['ordernumber'] = date('YmdHis').mt_rand(1000,9999);
                            $data['blance'] = $money;
                            $data['dateline'] = time();
                            $data['money'] = $doinfo['channelmoney'];
                            $data['type'] = 1;
                            $data['tradetype'] = 1;
                            $data['des'] = $des;
                            $data['user_id'] = $memberinfo['user_id'];
                            $res = MeadmoneylogService::add($data);
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                            return json(['status' => '201', 'msg' => $e->getMessage()]);
                        }
                    }
                }

                $where = array();
                $where['id'] = $taskrecordid;
                $updata['status'] = 2;
                $updata['updateline'] = time();
                if($errmoney>0){
                    $updata['systemmoney'] = $doinfo['systemmoney']+$errmoney;
                }
                Db::name('bill')->where($where)->update($updata);

            }
        }
    }


    /*
     * @Description  更新充值订单
     * @param (输入参数：)  {int}      taskrecordid  订单ID
     * @param (输入参数：)  {int}      $msg  说明
     *
     *
     */
    public static function doChargeorder($taskrecordid){
        $doinfo = Chargeorder::find($taskrecordid);
        if(!empty($doinfo)&&$doinfo['money']>0&&$doinfo['status']>1){
            if($doinfo['dostatus']==1){//开始结算
                if($doinfo['status']==2){
                    $where = array();
                    $where['member_id'] = $doinfo['member_id'];
                    $memberinfo = Member::find($doinfo['member_id']);
                    $sql = "UPDATE `cd_member` SET umoney=umoney+".$doinfo['money']." WHERE member_id=".$memberinfo['member_id'];
                    Db::query($sql);
                    $des ='账号充值';
                    $blance = $memberinfo['umoney']+$doinfo['money'];
                    addPayLog($memberinfo,$blance,$doinfo['money'],1,1,$des,$doinfo['ordernumber']);
                    //更新提面记录表
                    $upfinarr = array();
                    $upfinarr['money'] = $doinfo['money'];
                    $upfinarr['charge'] = 0;
                    $upfinarr['changemoney'] = $doinfo['money'];
                    self::addfinancial($upfinarr);


                }
                $where = array();
                $where['chargeorder_id'] = $taskrecordid;
                $updata['dostatus'] = 2;
                $updata['updateline'] = time();
                //$updata['des'] = $des;
                Chargeorder::update($updata,$where);

            }

        }
    }

    /*
     * @Description  更新提现充值订单
     * @param (输入参数：)  {int}      taskrecordid  订单ID
     * @param (输入参数：)  {int}      $msg  说明
     *
     *
     */
    public static function doWithdrawalorder($taskrecordid){
        $doinfo = Withdrawal::find($taskrecordid);
        if(!empty($doinfo)&&$doinfo['momey']>0&&$doinfo['status']==3){
            if($doinfo['dostatus']==1){//开始返钱
                $where = array();
                $where['member_id'] = $doinfo['member_id'];
                $memberinfo = Member::find($doinfo['member_id']);
                Member::setInc($where,'yjmoney',$doinfo['momey']);
                $des ='提现失败退款,订单号:'.$doinfo['trade_no'];
                addPayLog($memberinfo,($memberinfo['yjmoney']+$doinfo['momey']),$doinfo['momey'],1,5,$des,$doinfo['trade_no'],2);

            }
        }
        if($doinfo['dostatus']==1){
            $where = array();
            $where['withdrawal_id'] = $taskrecordid;
            $updata['dostatus'] = 2;
            $updata['updateline'] = time();
            //$updata['des'] = $des;
            Withdrawal::update($updata,$where);
            $where = array();
            $where['trade_no'] = $doinfo['trade_no'];
            $updata = array();
            $updata['txstatus'] = 2;
            //$updata['des'] = $des;
            if($doinfo['status']==3){
                $updata['txstatus'] =3;
            }
            Moneylog::update($updata,$where);
            if($doinfo['status']==2){
                //更新提面记录表
                $upfinarr = array();
                $upfinarr['money'] = $doinfo['transfer_money'];
                $upfinarr['charge'] = $doinfo['charge'];
                $upfinarr['changemoney'] = $doinfo['momey'];
                self::addfinancial($upfinarr,2);


            }
        }
    }

    /*
     * @Description  检测是否可提现
     * @param (输入参数：)  {int}      taskrecordid  订单ID
     * @param (输入参数：)  {int}      $msg  说明
     *
     *
     */
    public static function checkTxorder($memberinfo,$outmoney=10)
    {

        $rearr = array();
        $rearr['status'] = 1;
        $rearr['msg'] = '';
        $rearr['charge'] = 0;

        return $rearr;
    }
    /*
         * @Description  更新充提记录表
         * @param (输入参数：)  {int}      $upadd  订单ID
         * @param (输入参数：)  {int}      $type 1充值，2提现
         *
         *
         */
    public static function addfinancial($upadd,$type=1){
        $where['type']=$type;
        $time = date('Y-m-d');
        $where['dateline'] = strtotime($time);
        $listinfo = Financial::where($where)->field('*')->find();
        if(!empty($listinfo)){
            $upwhere = array();
            $upwhere['id'] = $listinfo['id'];
            $updata['money'] = $listinfo['money']+$upadd['money'];
            $updata['charge'] = $listinfo['charge']+$upadd['charge'];
            $updata['changemoney'] = $listinfo['changemoney']+$upadd['changemoney'];
            Db::name('financial')->where($upwhere)->update($updata);
        }else{
            $upadd['type'] = $type;
            $upadd['dateline'] = strtotime($time);
            $upadd['date'] = $time;
            Financial::create($upadd);
        }
    }

    public static function getdownsy($memberid,$type=1){
        if($type==1){
            $sql = "select sum(parentmoney) as totalmoney FROM `cd_chargeorder` WHERE status>=2 AND member_id=".$memberid;
        }
        if($type==2){
            $sql = "select sum(twomoney) as totalmoney FROM `cd_chargeorder` WHERE status>=2 AND member_id=".$memberid;
        }
        if($type==3){
            $sql = "select sum(threemoney) as totalmoney FROM `cd_chargeorder` WHERE status>=2 AND member_id=".$memberid;
        }
        if($type==4){
            $sql = "select sum(channelmoney) as totalmoney FROM `cd_chargeorder` WHERE status>=2 AND member_id=".$memberid;
        }
        $result = Db::query($sql);
        $result[0]['totalmoney'] = empty($result[0]['totalmoney'])?0:$result[0]['totalmoney'];
        return $result[0]['totalmoney'];

    }

    /*
            * @Description  检测充提时间
            * @param (输入参数：)  {int}      $upadd  订单ID
            * @param (输入参数：)  {int}      $type 1充值，2提现
            *
            *
            */
    public static function cheackOutIntime($type=1){
        $rearr = array();
        $rearr['status'] = 0;
        $rearr['msg'] = "";
        $starttime = '9';
        $endtime = '22';
        $configdata =  getSetConfig('inouttimeset',3);
        if(!empty($configdata)&&count($configdata)==2){
            $starttime = $configdata[0];
            $endtime =$configdata[1];
        }
        $n = date("H");
        if($n<$starttime || $n>=$endtime){
            $rearr['msg'] = "本站的充提时间为早上".$starttime.'点到'.$endtime.'点';
        }else{
            $rearr['status'] = 1;
        }
        return $rearr;
    }

    function get_qrcode($uid,$type=1)
    {
        $path = root_path();
        //$base_domain = config('my.home_domain');
        $fiurl =  getSetConfig('domain');
        //$fiurl = $base_domain;
        if($type==0){
            //$uid = $uid;    //token解码用户ID
            $a = \xhadmin\db\Member::getInfo($uid);
            if (!$a) {
                return json(['status' => $this->errorCode, 'msg' => '不存在该成员']);
            }


            $base_url = $path . "public/uploads/tg/";
            //$base_url = 'uploads/';
            $file = $base_url . "member_" . $uid . '.png';
            $fiurl = $fiurl."/tg/member_" . $uid . '.png';
            unlink($file);
            $wapurl =  getSetConfig('wapurl');

            $data = $wapurl.'/#/pages/common/login/recommend?reuid='.$uid;
        }else{
            $data = $uid;
            $uid = md5($uid);
            $base_url = $path . "public/uploads/";
            $file = $base_url . "address_" . $uid . '.png';
            $fiurl = $fiurl."/address_" . $uid . '.png';
            unlink($file);
        }


        // $value = json_encode($data, true); //二维码内容
        $value = $data;
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
        //生成二维码图片
        \QRcode::png($value, $file);
        $logo = $a['avatar'] ? $a['avatar'] : FALSE;//准备好的logo图片
        $QR = $fiurl . $file;//已经生成的原始二维码图
        header("Content-type: image/jpg");

        if ($logo !== FALSE) {
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));

            $QR_width = imagesx($QR);//二维码图片宽度
            $QR_height = imagesy($QR);//二维码图片高度
            $logo_width = imagesx($logo);//logo图片宽度
            $logo_height = imagesy($logo);//logo图片高度
            #logo修改成正方形
            $l_w = $logo_width > $logo_height ? $logo_height : $logo_width; #取最短
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                $logo_qr_height, $l_w, $l_w);
        }

        //输出图片
        imagepng($QR, $file);
        return  $fiurl.'?t='.time();
    }

    public  static function doVipupdate($info=[]){
        $re['status']=0;
        $re['msg']='未知错误';
        $updata = [];
        $member_id = $info['userid'];
        //vippaytype 1包月，2收益，3贵宾
        if(!empty($info)){
            $groupinfo = Tradegroup::find($info['groupid']);
            if(!empty($groupinfo)){
                switch ($groupinfo['vippaytype']){
                    case 1://包月，是否自动续费
                        if($groupinfo['isfree']==0){//免费
                            $upadd = array();
                            $expiretime = date('Y-m-d H:i:s', strtotime ("+1 month", $info['validtime']));
                            $upadd['validtime'] = strtotime($expiretime);
                            $updata = [];
                            $updata['validTime']=date('Y-m-d H:i:s',$upadd['validtime']);
                            $updata['isValid']=1;
                            $result = \app\api\service\BinanceService::auditUser($updata,$member_id);
                            if($result['status']==200){
                                $re['status']=1;
                                $re['validtime'] = strtotime($result['result']['validTime']);
                                $re['msg']='延期成功';
                                $msg='系统已免费为您成功延期到'.$updata['validtime'];
                                sendToUser($info,$msg,'',1);
                                return $re;
                            }else{
                                $re['msg']='延期失败';
                                return $re;
                            }
                        }

                        if($info['aupay']==1){
                            $apiInfo = $info;

                            //处理自动包月
                            //查出包月费用
                            $memberInfo = Member::find($info['userid']);
                            $productInfo = Productset::find('22');
                            $pid = 22;
                            if(!empty($productInfo)){
                                if($productInfo['price']>$info['umoney']){
                                    $re['msg']='账号余额不足，不能自动续费';
                                    $msg='由于您的账号余额不足，系统无法为您自动延期服务，请及时充值后，再进行购买相关服务';
                                    sendToUser($info,$msg,'',1);
                                    return $re;
                                }else{
                                    $trade_no = date('YmdHis').mt_rand(1000,9999);
                                    $money = $productInfo['price'];
                                    //进行支付操作
                                    $where = array();
                                    $where['member_id'] = $info['userid'];
                                    $dodec=Member::setDes($where,'umoney',$money);
                                    if(!$dodec){
                                        $re['msg']='账号余额不足，不能自动续费';
                                        $msg='由于您的账号余额不足，系统无法为您自动延期服务，请充值后，再进行购买相关服务';
                                        sendToUser($info,$msg,'',1);
                                        return $re;
                                    }
                                    $des ='系统自动续费购买:'.$productInfo['ltitle'];
                                    $money = $productInfo['price'];
                                    addPayLog($memberInfo,($memberInfo['umoney']-$money),$money,2,3,$des,$trade_no);
//加入账单记录
                                    $systemmoney = $money;
                                    $addbill = [];
                                    $addbill['mobile'] = empty($memberInfo['mobile'])?$memberInfo['email']:$memberInfo['mobile'];
                                    $addbill['memberid'] = $memberInfo['member_id'];
                                    $addbill['addtime'] = time();
                                    $addbill['paytime'] = time();
                                    $addbill['updateline'] = time();
                                    $addbill['type'] = 1;
                                    $addbill['status'] = 1;
                                    $addbill['paystatus'] = 2;
                                    $addbill['title'] = $des;
                                    $addbill['des'] = $des;
                                    $addbill['datekey'] = date('Ymd');
                                    $addbill['money'] = $money;
                                    $addbill['channel'] = $memberInfo['channel'];

                                    $addbill['ordernumber'] = $trade_no;
                                    $wherekey=[];
                                    $wherekey['ordernumber'] = $trade_no;
                                    $wherekey['type'] = 1;
                                    $wherekey['paystatus'] = 2;
                                    $orderid = 0;
                                    if($systemmoney>0){
                                        $billinfo = Bill::Where($wherekey)->field('*')->find();
                                        if(empty($billinfo)){
                                            $orderid = Db::name('bill')->insertGetId($addbill);
                                        }
                                    }
                                    if(empty($orderid)){
                                        $re['msg']='自动续费失败1';

                                        return $re;
                                    }

                                    //进行分成信息
                                    //self::doPayBillorder($orderid);
                                    //更新产品购买数量

                                    $sql = "UPDATE `cd_productset` SET buynumber=buynumber+1 WHERE levelset_id=".$pid;
                                    Db::query($sql);

                                    $upadd = array();
                                    $expiretime = date('Y-m-d H:i:s', strtotime ("+1 month", $apiInfo['validtime']));
                                    $upadd['validtime'] = strtotime($expiretime);
                                    $where = array();
                                    $where['userid'] = $member_id;
                                    try{
                                        //Db::name('apiuser')->where($where)->update($upadd);
                                        $updata = [];
                                        $updata['validTime']=date('Y-m-d H:i:s',$upadd['validtime']);
                                        $updata['isValid']=1;
                                        $result = \app\api\service\BinanceService::auditUser($updata,$member_id);
                                        if($result['status']==200){
                                            $re['status']=1;
                                            $re['validtime'] = strtotime($result['result']['validTime']);
                                            $msg='您开通的自动延期服务，系统已自动为您延期到'.$result['result']['validTime'];
                                            sendToUser($info,$msg,'',1);
                                            return $re;
                                        }

                                    }catch (\Exception $e){
                                        $re['msg']='自动续费失败1';
                                        return $re;
                                    }
                                }
                            }
                        }

                        break;
                    case 2:
                        //查询是否有账单 收益会员全部根据账号自动启停
                        $member_id = $info['userid'];
                        if($groupinfo['isfree']==1) {
                            /*
                            $sql = "SELECT title,id,addtime,income,lastpaytime,billmonth,paystatus,money,ordernumber,paystatus FROM `cd_bill` WHERE memberid =" . $member_id . " AND type=2 AND paystatus=1 order by id asc limit 1";
                            $result = Db::query($sql);
                            if (!empty($result) && isset($result[0])) {
                                $re['msg'] = '账单未支付，不能自动续期';
                                $msg='由于您的前面的账单未支付，系统无法为您自动延期服务，请您及时支付完账单，以免影响您的业务';
                                sendToUser($info,$msg,'',1);
                                return $re;
                            }
                           */
                        }else{
                            $sql = "UPDATE `cd_bill` SET paystatus=3  WHERE memberid =" . $member_id . " AND type=2 AND paystatus=1";
                            Db::query($sql);
                        }
                        $data = date('Y-m').'-15';
                        $dattime = strtotime($data);
                        $expiretime = date('Y-m-d H:i:s', strtotime ("+1 month", $dattime));
                        $updata['validTime']=$expiretime;
                        $updata['isValid']=$info['isvalid'];
                        $result = \app\api\service\BinanceService::auditUser($updata,$info['userid']);
                        if($result['status']==200){
                            $re['status']=1;
                            $re['validtime'] = strtotime($result['result']['validTime']);
                            //$re['msg'] = '延期成功';
                            //$msg='系统已自动为您延期到'.$result['result']['validTime'];
                            //sendToUser($info,$msg,'',1);
                        }else{
                            $re['msg'] = $result['msg'];
                        }
                        break;
                    case 3://
                        $data = date('Y-m').'-15';
                        $dattime = strtotime($data);
                        $expiretime = date('Y-m-d H:i:s', strtotime ("+1 month", $dattime));
                        $updata['validTime']=$expiretime;
                        $updata['isValid']=$info['isvalid'];
                        $result = \app\api\service\BinanceService::auditUser($updata,$info['userid']);
                        if($result['status']==200){
                            $re['status']=1;
                            $re['validtime'] = strtotime($result['result']['validTime']);
                            $re['msg'] = '延期成功';
                            $msg='系统已自动为您延期到'.$result['result']['validTime'];
                            sendToUser($info,$msg,'',1);
                        }
                        break;
                }
            }
        }

        return $re;
    }


    public static function checkAgent($member_id='',$level=0){
        if(empty($member_id)){
            return '';
        }
        $where=[];
        if(is_numeric($member_id)){
            $where['member_id'] = $member_id;
        }else{
            $where['channel'] = $member_id;
        }

        $ageninfo = Agent::Where($where)->field('*')->find();
        if(!empty($level)){
            if(!empty($ageninfo['dorule'])){
                $lederinfo = explode(',', $ageninfo['dorule']);
                if(count($lederinfo)>0){
                    if(!in_array($level,$lederinfo)){
                        return 203;
                    }
                }
            }
        }
        return !empty($ageninfo)?$ageninfo:'';
    }
    //日志记录
    public  function Oplog($data){
        $data['dateline'] = time();
        $data['ip']=ip();
        Apilog::create($data);
    }



    /*
     * @Description  更新任务为成功同时给会员加钱
     * @param (输入参数：)  {int}      taskrecordid  任务记录ID
     * @param (输入参数：)  {int}      $msg  说明
     *
     *
     */
    public static function doPayCZorder($taskrecordid){
        //VIP续费|1|success,收益结算|2|warning,其它|3|primary
        $typetitle = '充值消费';
        $doinfo = Chargeorder::find($taskrecordid);

        if(!empty($doinfo)){
            if($doinfo['dostatus']==2){//开始结算

                $errmoney = 0;
                $membxf = Member::find($doinfo['member_id']);
                if($doinfo['parentmoney']>0 && $doinfo['parentid']>0){
                    $where = array();

                    $where['member_id'] = $doinfo['parentid'];
                    $memberinfo = Member::find($doinfo['parentid']);
                    if(!empty($memberinfo)){
                        $des = '会员('.hiden_mymoblie($membxf['mobile']).'),'.$typetitle.':'.$doinfo['ordernumber'].'提成';
                        try {
                            $sql = "UPDATE `cd_member` SET yjmoney=yjmoney+".$doinfo['parentmoney']." WHERE member_id=".$memberinfo['member_id'];
                            Db::query($sql);
                            $blance = $memberinfo['yjmoney']+$doinfo['parentmoney'];
                            addPayLog($memberinfo,$blance,$doinfo['parentmoney'],1,2,$des,'',2);
                        } catch (\Exception $e) {
                            return json(['status'=>'201','msg'=>$e->getMessage()]);
                        }
                    }else{
                        $errmoney=$errmoney+$doinfo['parentmoney'];
                    }

                }
                //print_r($doinfo);
                if($doinfo['twomoney']>0 && $doinfo['twoid']>0){
                    //给做任务会员结算
                    //echo 'twoprice'.$doinfo['twoprice'].'<br/>';
                    $where = array();
                    $where['member_id'] = $doinfo['twoid'];
                    $memberinfo = Member::find($doinfo['twoid']);
                    $des = '会员('.hiden_mymoblie($membxf['mobile']).'),'.$typetitle.':'.$doinfo['ordernumber'].'二级提成';
                    //print_r($memberinfo);
                    if(!empty($memberinfo)) {
                        //print_r($memberinfo);
                        try {
                            $sql = "UPDATE `cd_member` SET yjmoney=yjmoney+".$doinfo['twomoney']." WHERE member_id=".$memberinfo['member_id'];
                            Db::query($sql);
                            $blance = $memberinfo['yjmoney']+$doinfo['twomoney'];
                            addPayLog($memberinfo, $blance, $doinfo['twomoney'], 1, 2, $des,'',2);
                        } catch (\Exception $e) {
                            return json(['status' => '201', 'msg' => $e->getMessage()]);
                        }
                    }else{
                        $errmoney=$errmoney+$doinfo['twomoney'];
                    }
                }
                if($doinfo['threemoney']>0 && $doinfo['threeid']>0){
                    //给做任务会员结算
                    //echo 'threeprice'.$doinfo['threemoney'].'<br/>';
                    $where = array();
                    $where['member_id'] = $doinfo['threeid'];
                    $memberinfo = Member::find($doinfo['threeid']);
                    //print_r($memberinfo);
                    if(!empty($memberinfo)) {
                        //print_r($memberinfo);
                        $des = '会员('.hiden_mymoblie($membxf['mobile']).'),'.$typetitle.':'.$doinfo['ordernumber'].'三级提成';
                        try {
                            $sql = "UPDATE `cd_member` SET yjmoney=yjmoney+".$doinfo['threemoney']." WHERE member_id=".$memberinfo['member_id'];
                            Db::query($sql);
                            $blance = $memberinfo['yjmoney']+$doinfo['threemoney'];
                            addPayLog($memberinfo, $blance, $doinfo['threemoney'], 1, 2, $des,'',2);
                        } catch (\Exception $e) {
                            return json(['status' => '201', 'msg' => $e->getMessage()]);
                        }
                    }else{
                        $errmoney=$errmoney+$doinfo['threemoney'];
                    }
                }

                if($doinfo['channelmoney']>0 && !empty($doinfo['channel'])){
                    //给做任务会员结算
                    $where = array();
                    $where['channel'] = $doinfo['channel'];
                    $memberinfo = Agent::Where($where)->field('*')->find();

                    if(!empty($memberinfo)) {
                        $des = '会员('.hiden_mymoblie($membxf['mobile']).'),'.$typetitle.':'.$doinfo['ordernumber'].'提成';
                        try {
                            $money = $memberinfo['money']+$doinfo['channelmoney'];
                            $allmoney = $memberinfo['allmoney']+$doinfo['channelmoney'];
                            $sql = "UPDATE `cd_agent` SET money=".$money.",allmoney=".$allmoney." WHERE id=".$memberinfo['id'];
                            Db::query($sql);
                            $data['ordernumber'] = date('YmdHis').mt_rand(1000,9999);
                            $data['blance'] = $money;
                            $data['dateline'] = time();
                            $data['money'] = $doinfo['channelmoney'];
                            $data['type'] = 1;
                            $data['tradetype'] = 1;
                            $data['des'] = $des;
                            $data['user_id'] = $memberinfo['user_id'];
                            $res = MeadmoneylogService::add($data);
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                            return json(['status' => '201', 'msg' => $e->getMessage()]);
                        }
                    }
                }

                if($doinfo['twochannelmoney']>0 && !empty($doinfo['twochannel'])){
                    //给做任务会员结算
                    $where = array();
                    $where['channel'] = $doinfo['twochannel'];
                    $memberinfo = Agent::Where($where)->field('*')->find();

                    if(!empty($memberinfo)) {
                        $des = '会员('.hiden_mymoblie($membxf['mobile']).'),'.$typetitle.':'.$doinfo['ordernumber'].'提成';
                        try {
                            $money = $memberinfo['money']+$doinfo['twochannelmoney'];
                            $allmoney = $memberinfo['allmoney']+$doinfo['twochannelmoney'];
                            $sql = "UPDATE `cd_agent` SET money=".$money.",allmoney=".$allmoney." WHERE id=".$memberinfo['id'];
                            Db::query($sql);
                            $data['ordernumber'] = date('YmdHis').mt_rand(1000,9999);
                            $data['blance'] = $money;
                            $data['dateline'] = time();
                            $data['money'] = $doinfo['twochannelmoney'];
                            $data['type'] = 1;
                            $data['tradetype'] = 1;
                            $data['des'] = $des;
                            $data['user_id'] = $memberinfo['user_id'];
                            $res = MeadmoneylogService::add($data);
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                            return json(['status' => '201', 'msg' => $e->getMessage()]);
                        }
                    }
                }

                if($doinfo['threechannelmoney']>0 && !empty($doinfo['threechannel'])){
                    //给做任务会员结算
                    $where = array();
                    $where['channel'] = $doinfo['threechannel'];
                    $memberinfo = Agent::Where($where)->field('*')->find();

                    if(!empty($memberinfo)) {
                        $des = '会员('.hiden_mymoblie($membxf['mobile']).'),'.$typetitle.':'.$doinfo['ordernumber'].'提成';
                        try {
                            $money = $memberinfo['money']+$doinfo['threechannelmoney'];
                            $allmoney = $memberinfo['allmoney']+$doinfo['threechannelmoney'];
                            $sql = "UPDATE `cd_agent` SET money=".$money.",allmoney=".$allmoney." WHERE id=".$memberinfo['id'];
                            Db::query($sql);
                            $data['ordernumber'] = date('YmdHis').mt_rand(1000,9999);
                            $data['blance'] = $money;
                            $data['dateline'] = time();
                            $data['money'] = $doinfo['threechannelmoney'];
                            $data['type'] = 1;
                            $data['tradetype'] = 1;
                            $data['des'] = $des;
                            $data['user_id'] = $memberinfo['user_id'];
                            $res = MeadmoneylogService::add($data);
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                            return json(['status' => '201', 'msg' => $e->getMessage()]);
                        }
                    }
                }
                $where = array();
                $where['chargeorder_id'] = $taskrecordid;
                $updata['dostatus'] = 4;
                $updata['updateline'] = time();
                if($errmoney>0){
                    $updata['systemmoney'] = $doinfo['systemmoney']+$errmoney;
                }
                Db::name('chargeorder')->where($where)->update($updata);

            }
        }
    }

    //更新用户API状态
    public function doUpApistatus($status,$userid){
        
        $data = date('Y-m').'-15';
        $dattime = strtotime($data);
        $expiretime = date('Y-m-d H:i:s', strtotime ("+1 month", $dattime));
        $updata=[];
        $wheremember = [];
        $wheremember['userid'] = $userid;
        $memberinfo  =Apiuser::getWhereInfo($wheremember,"*");
        if(empty($memberinfo)){
            return;
        }
        if($status==$memberinfo['isvalid']){
            return;
        }
        $updata['id']=$memberinfo['id'];
        $updata['isvalid'] = $status;
        $updata['updatetime'] = date('Y-m-d H:i:s');
        if($memberinfo['createtime']==0){
            $memberinfob = \app\api\model\Member::find($userid);
           $updata['createtime'] = date('Y-m-d H:i:s',$memberinfob['regtime']);
        }else{
            $updata['createtime'] = date('Y-m-d H:i:s',$memberinfo['createtime']);
        }
        $updata['validtime']=$expiretime;
        
        $res = ApiuserService::update($updata);
        if($status==0){
            $expiretime = date('Y-m-d H:i:s', $memberinfo['validtime']);
        }
        $updataa['validTime']=$expiretime;
        $updataa['isValid']=$status;
        $result =\app\api\service\BinanceService::auditUser($updataa,$userid);

    }
}

