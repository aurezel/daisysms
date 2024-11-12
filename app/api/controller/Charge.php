<?php
/*
 module:		任务相关接口
 create_time:	2020-05-10 12:04:42
 author:		
 contact:		
*/

namespace app\api\controller;
use app\admin\model\Agent;
use app\admin\model\Bill;
use app\admin\model\Chargeorder;
use app\admin\model\Payway;
use app\admin\model\Channelpayset;
use app\admin\model\Productset;
use app\admin\model\Tradegroup;
use app\admin\model\Withdrawal;
use app\admin\service\ProductsetService;
use app\api\service\CavenService;
use app\api\service\PayService;
use app\api\service\PaytypeService;
use app\api\model\Member;
use think\facade\Db;
use app\admin\service\ChargeorderService;

class Charge extends Common {


    //充值入账
    public function checkIncome(){
        $member_id = $this->request->uid;
        $txid = $this->request->post('txid', '');
        $payway = $this->request->post('payway', 0, 'intval');
        if(empty($payway)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'充值方式错误']);
        }

        $wherebb['payway_id']=$payway;
        $wherebb['type']=1;
        $wherebb['status']=1;
        $paywayinfo = Payway::where($wherebb)->field('*')->find();
        if(empty($paywayinfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'充值方式错误']);
        }

        $terracearr = array(
            '1'=>'Binance',
            '2'=>'Okx',
            '3'=>'Binance'
        );

        $terrace = $terracearr[$paywayinfo['paytype']];
        //币安转账|1|success,OKX转账|2|warning,钱包充提|3|success

        //$txid = '31516712_3_7_0_20554156_0_WALLET';
        if(empty($txid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'txId不能为空']);
        }
        if(empty($terrace)||!in_array($terrace,array('Okx','binance'))){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'充值方式不对']);
        }
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $where['member_id']=$member_id;
        //utype ==1 普通会员 2 代理会员
        $filed="member_id,mobile,umoney,channel";
        $res  =Member::getWhereInfo($where,$filed);
        if(empty($res)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'用户信息获取失败']);
        }
        $result = PayService::goCheckPay($member_id,'inmoney',$txid,$terrace,$paywayinfo,$res['channel']);
        return $this->reDecodejson($result);
    }

    //支付账单
    public function payOrder(){
        $member_id = $this->request->uid;
        $billid = $this->request->post('ordernumber', 0, 'intval');
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        if(empty($billid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'账单订单号错误']);
        }
        $where=[];
        $where['ordernumber']=$billid;
        $where['memberid'] = $member_id;
        //$where['status'] = 1;
        $billinfo = Bill::Where($where)->field("*")->find();
        if(empty($billinfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'账单信息错误，请检查再提交']);
        }
        if($billinfo['paystatus']!=1){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'此账单已支付过']);
        }
        $memberinfo = Member::find($member_id);
        if(empty($memberinfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'会员信息错误']);
        }
        $money = $billinfo['money'];
        if($money>$memberinfo['umoney']){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'您的账户余额不足，请先充值']);
        }
        //进行支付操作
        $where = array();
        $where['member_id'] = $member_id;
        $dodec=Member::setDes($where,'umoney',$money);
        if(!$dodec){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'您的账户余额不足，请先充值']);
        }
        $sql = "UPDATE `cd_member` set billmoney=billmoney-".$money." WHERE member_id=".$member_id;
        Db::query($sql);
        $des ='支付:'.$billinfo['des'];
        addPayLog($memberinfo,($memberinfo['umoney']-$money),$money,2,4,$des,$billid);
        $wherebill['id']=$billinfo['id'];
        $updata['paystatus']=2;
        $updata['paytime']=time();
        Bill::update($updata,$wherebill);
        //支付账单检测是否还有未支付的，如果没有，就开通
        $sql = "SELECT COUNT(id) as total FROM `cd_bill` WHERE paystatus=1 AND type=2 AND memberid=".$member_id;
        $result = Db::query($sql);
        if($result[0]['total']==0){
            $msg='您已全部支付完账单系统自动为您开启跟单功能';
            sendToUser($memberinfo,$msg,'',1);
            CavenService::doUpApistatus(1,$member_id);
        }
        return $this->reDecodejson(['status'=>200,'msg'=>'支付成功']);

        //CavenService::doPayBillorder($billinfo['id']);
        $data = date('Y-m').'-15';
        $dattime = strtotime($data);
        $expiretime = date('Y-m-d H:i:s', strtotime ("+1 month", $dattime));
        $updata=[];
        $updata['validTime']=$expiretime;
        $updata['isValid']=1;
        $result = \app\api\service\BinanceService::auditUser($updata,$member_id);
        if($result['status']==200){
            $msg='您的账单已支付系统自动为您延期到:'.$result['result']['validTime'];
            sendToUser($memberinfo,$msg,'',1);
        }else{
            //$re['msg'] = $result['msg'];
            return $this->reDecodejson(['status'=>200,'msg'=>$result['msg'].'，请及时联系客服处理']);
        }
        return $this->reDecodejson(['status'=>200,'msg'=>'支付成功']);
    }



    /**
     * @api {get} /Charge/getPaylist 22、取得方式列表
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  取得方式列表
     * @apiParam (输入参数：) {string}			type 1为充值，2为提现 默认 1 //充值|1|success,提现|2|warning
     * @apiParam (失败返回参数：) {object}     	array 返回结果集
     * @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}     	array 返回结果集
     * @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}     	array.data 返回数据详情
     *{"status": 200,"msg": "点赞成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status": 201,"msg": "您已经点过赞了"}
     */
    function getPaylist(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        
        $memberInfo = Member::find($member_id);
        //币安转账|1|success,OKX转账|2|warning,钱包充提|3|success
        $paytypearr = array(
            '1'=>'币安账号',
            '2'=>'OKX账号',
            '3'=>'钱包',

        );
        //每笔|1|success,提现额百分比|2|warning
        $chargetypearr = array(
            '1'=>'每笔',
            '2'=>'提现额',
        );
        $type  = $this->request->post('type', '1','intval');
        $type = intval($type);
        if(!in_array($type,array(1,2))){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        $limit = 100;
        $sql="SELECT * FROM `cd_payway` where  status=1 ORDER BY  sortid limit ".$limit;
        $data_pv=Db::query($sql);
        $payarr['chongzi'] = [];
        $payarr['tixian'] = [];
        if(!empty($data_pv)){
            foreach($data_pv as $key=>$v){
                $data_pv[$key]['epaytype'] =$paytypearr[$v['paytype']];
                $data_pv[$key]['echargetype'] =$chargetypearr[$v['chargetype']];
                $data_pv[$key]['isdl'] = 0;
                if($v['type']==2){
                    $payarr['tixian'][$v['payway_id']] = $data_pv[$key];
                }
                if($v['type']==1){
                    $payarr['chongzi'][$v['payway_id']] = $data_pv[$key];
                }
            }
        }
        $channel = $memberInfo['channel'];
        if(!empty($channel)){
            $payarrb['chongzi'] = [];
            $payarrb['tixian'] = [];
            $sql = "SELECT a.name,b.id,a.img,a.paytype,a.type,b.charge,b.chargetype,b.account,b.thumb,b.smkey,b.des,b.payway_id FROM `cd_channelpayset` b LEFT JOIN `cd_payway` a ON a.payway_id=b.payway_id where b.status=1 AND b.channel='".$channel."' ORDER BY  a.sortid limit 100";
            $data_pv=Db::query($sql);
            if(!empty($data_pv)){
                foreach($data_pv as $key=>$v){
                    $data_pv[$key]['epaytype'] =$paytypearr[$v['paytype']];
                    $data_pv[$key]['echargetype'] =$chargetypearr[$v['chargetype']];
                    $data_pv[$key]['isdl'] = $v['id'];
                    if($v['type']==2){
                        $payarrb['tixian'][$v['payway_id']] = $data_pv[$key];
                    }
                    if($v['type']==1){
                        $payarrb['chongzi'][$v['payway_id']] = $data_pv[$key];
                    }
                }
            }
            if(!empty($payarrb['chongzi'])){
                unset($payarr['chongzi']);
                $payarr['chongzi'] = $payarrb['chongzi'];
            }
            if(!empty($payarrb['tixian'])){
                unset($payarr['tixian']);
                $payarr['tixian'] = $payarrb['tixian'];
            }
        }
        
        return $this->reDecodejson(['status'=>$this->successCode,'data'=>$payarr]);
    }

    /**
     * @api {get} /Charge/getPayInfo 22、取得方式列表
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  取得方式列表
     * @apiParam (输入参数：) {string}			type 1为充值，2为提现 默认 1 //充值|1|success,提现|2|warning
     * @apiParam (失败返回参数：) {object}     	array 返回结果集
     * @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}     	array 返回结果集
     * @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}     	array.data 返回数据详情
     *{"status": 200,"msg": "点赞成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status": 201,"msg": "您已经点过赞了"}
     */
    function getPayInfo(){
        $payid  = $this->request->post('id', '','intval');
        $isdl  = $this->request->post('isdl', '','intval');
        $type  = $this->request->post('type', '1','intval');
        if(empty($type)||!in_array($type,array(1,2))){
            return $this->reDecodejson(['status'=>201,'msg'=>'操作错误','data'=>[]]);
        }
        $olist = $lists = Payway::find($payid);
       
        if(empty($lists)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'操作错误1','data'=>[]]);
        }
        if(($lists['type']!=$type)||($lists['status']!=1)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'操作错误2','data'=>[]]);
        }
        //print_r($olist);
        $member_id = $this->request->uid;
        $where['member_id']=$member_id;
        //utype ==1 普通会员 2 代理会员
        $filed="channel";
        $res  =Member::getWhereInfo($where,$filed);
 
        if($res && !empty($res['channel'])){
            $whereb=[];
            $whereb['channel'] =$res['channel'];
            $whereb['payway_id'] =intval($payid);
            if(intval($isdl)>0){
               //$whereb['id'] =intval($isdl); 
            }
            //print_r($whereb);
            $listsb = Channelpayset::where($whereb)->find();
            //print_r($listsb);
            if(!empty($listsb)){
                //$lists = $listsb;
                $lists['name'] = $listsb['name'];
                $lists['img'] = $listsb['img'];
                $lists['paytype'] = $listsb['paytype'];
                $lists['account'] = $listsb['account'];
                $lists['thumb'] = $listsb['thumb'];
               // $lists['paytype'] = $listsb['paytype'];
                if($lists['paytype'] ==2){
                    $lists['id']='';
                }
            }
        }
        $lists['des'] = htmlspecialchars_decode($lists['des']);
        $lists['addressinfo'] = '';
        if($type==1&&$lists['paytype']==3&&$isdl==0){
            $list = PayService::getPayFromOkx('address');
            if(!empty($list[0])){
                $lists['addressinfo'] = $list[0];
                $lists['account'] = $lists['addressinfo']['addr'];
                $lists['thumb'] =CavenService::get_qrcode($lists['addressinfo']['addr']);
            }
        }else{
            if($type==1){
                $lists['addressinfo'] = $lists['account'];
                $lists['account'] = $lists['account'];
                $lists['thumb'] =$lists['thumb'];
            }
        }
        return $this->reDecodejson(['status'=>$this->successCode,'data'=>$lists]);
    }

    /*
       取得可购买产品列表
    */
    public function getProductlist(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $res  = Member::find($member_id);
        if(empty($res)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'会员信息错误']);
        }
        $groupinfo = Tradegroup::find($res['groupid']);
        if($groupinfo['vippaytype']!=1){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'当前会员组不支持购买']);
        }
        $where['isbuy']=1;
        // $where['showtype']=$showtype;
        $orderby = 'sortid asc';
        $data = ProductsetService::indexList($where,"*",$orderby,1000,1);
        $list = [];
        $filed ="levelset_id,ltitle,pcont,price,buynumber,otime,sortid,isbuy,getnumber";
        if(!empty($data['rows'])){
            foreach($data['rows'] as $key=>$v){
                $onelist = setValueToArr($filed,$v);
                $list[] = $onelist;

            }
        }
        return $this->ajaxReturn($this->successCode, '返回成功', $list);

    }


    //购买产品getProductlist
    function buyProduct(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $pid   = $this->request->post('pid', '', 'intval');
        $memberInfo = Member::find($member_id);
        if(empty($memberInfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'会员信息错误']);
        }

        //
        $apiuserwhere = [];
        $apiuserwhere['userid'] = $member_id;
        $apiInfo = db('apiuser')->where($apiuserwhere)->field("*")->find();
        if(empty($apiInfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'请先到辅助配置那去配置交易所API接口信息']);
        }
        if(empty($apiInfo['isvalid'])||$apiInfo['isvalid']!=1){
            //return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'您的信息还在审核中，请联系管理员审核']);
        }
        $groupinfo = Tradegroup::find($memberInfo['groupid']);
        if($groupinfo['vippaytype']!=1){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'当前会员组不支持购买']);
        }

        $where['levelset_id'] = $pid;
        $where['isbuy'] = 1;
        $productInfo = db('productset')->where($where)->field("*")->find();
        if($productInfo['ptype']==2 &&$productInfo['otime']>6){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'产品信息错误，请选择其它产品']);
        }
        if($memberInfo['umoney']<$productInfo['price']){
            return $this->reDecodejson(['status'=>'203','msg'=>'您的余额不足,请先充值']);
        }
        $trade_no = date('YmdHis').mt_rand(1000,9999);
        $money = $productInfo['price'];
        //进行支付操作
        $where = array();
        $where['member_id'] = $member_id;
        $dodec=Member::setDes($where,'umoney',$money);
        if(!$dodec){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'您的账户余额不足，请先充值']);
        }
        $des ='购买:'.$productInfo['ltitle'];
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
        $where=[];
        $where['channel'] = $memberInfo['channel'];
        /*
        $ageninfo = Agent::Where($where)->field('*')->find();

        //print_r($ageninfo);die;
        if(!empty($ageninfo)){
            $agenmoney = $money*$ageninfo['paygetpoint']/100;
            $addbill['channelmoney'] = $agenmoney;
            $systemmoney = $systemmoney-$agenmoney;
        }

        $topuser[0]=intval($memberInfo['parentid']);
        $topuser[1]=intval($memberInfo['twoid']);
        $topuser[2]=intval($memberInfo['threeid']);
        $addbill['parentid'] = $topuser[0];
        $addbill['twoid'] = $topuser[1];
        $addbill['threeid'] = $topuser[2];

        $topmoney = CavenService::getTcpoint($topuser,$money,'buyvipset');
        $topmoney[0] = isset($topmoney[0])?$topmoney[0]:0;
        $topmoney[1] = isset($topmoney[1])?$topmoney[1]:0;
        $topmoney[2] = isset($topmoney[2])?$topmoney[2]:0;
        $systemmoney = $systemmoney-$topmoney[0]-$topmoney[1]-$topmoney[2];
        $addbill['parentmoney'] = $topmoney[0];
        $addbill['twomoney'] = $topmoney[1];
        $addbill['threemoney'] = $topmoney[2];
        $addbill['systemmoney'] = $systemmoney;
        */

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
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'购买失败']);
        }

        //进行分成信息
        //CavenService::doPayBillorder($orderid);
        //更新产品购买数量

        $sql = "UPDATE `cd_productset` SET buynumber=buynumber+1 WHERE levelset_id=".$pid;
        Db::query($sql);

        $upadd = array();
        $upadd['level'] = $productInfo['mgroupid'];
        $monthsarr = array(
            '3'=>1,
            '4'=>3,
            '5'=>6,
            '6'=>12,
        );
        $months = $day = 0;

        if($productInfo['otime']==1){
            $day = 1;
        }else if($productInfo['otime']==2){
            $day = 7;
        }else{
            $months = $monthsarr[$productInfo['otime']];
        }
        if(empty($apiInfo['validtime'])){
            $apiInfo['validtime'] = date('Y-m-d H:i:s');
        }else{
            $time = time();
            $apiInfo['validtime'] = $apiInfo['validtime']<$time?$time:$apiInfo['validtime'];
        }
        if($months>0){
            $expiretime = date('Y-m-d H:i:s', strtotime ("+".$months." month", $apiInfo['validtime']));
        }else{
            $expiretime = date('Y-m-d H:i:s', strtotime ("+".$day." day", $apiInfo['validtime']));
        }
        $upadd['validtime'] = strtotime($expiretime);
        $where = array();
        $where['userid'] = $member_id;
        try{
            Db::name('apiuser')->where($where)->update($upadd);
            $updata = [];
            $updata['validTime']=date('Y-m-d H:i:s',$upadd['validtime']);
            $updata['isValid']=1;
            \app\api\service\BinanceService::auditUser($updata,$member_id);
            return $this->reDecodejson(['status'=>200,'msg'=>'恭喜您成功购买:'.$productInfo['ltitle']]);
        }catch (\Exception $e){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>$e->getMessage()]);
        }
    }


    //充值入账
    function addOrder(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $ch=CavenService::cheackOutIntime();
        if($ch['status']==0){
            return $this->reDecodejson(['status'=>201,'msg'=>$ch['msg']]);
        }
        $payway   = $this->request->post('payway', '', 'intval');
        $money=$this->request->post('money');
        $money = (float)$money;
        $banknumber=$this->request->post('banknumber');
        $memberInfo = Member::find($member_id);
        if(empty($banknumber)){
            return $this->reDecodejson(['status'=>201,'msg'=>'提现账号或提现地址不能为空']);
        }
        if($memberInfo['status']!=1){
            return $this->reDecodejson(['status'=>201,'msg'=>'您的账号已被锁定，请联系管理员']);
        }
        if(empty($money)){
            return $this->reDecodejson(['status'=>201,'msg'=>'您传入的参数不对']);
        }
        if($money<50){
            return $this->reDecodejson(['status'=>201,'msg'=>'最低提现额度不能小于50USDT']);
        }
        if(empty($payway)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'提现方式错误1']);
        }

        $wherebb['payway_id']=$payway;
        $wherebb['type']=2;
        $wherebb['status']=1;
        $paywayinfo = Payway::where($wherebb)->field('*')->find();
        if(empty($paywayinfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'提现方式错误']);
        }

        if($memberInfo['yjmoney']<$money){
            return $this->reDecodejson(['status'=>201,'msg'=>'余额不足！']);
        }
        $chargetype = $paywayinfo['chargetype'];
        $charge = $paywayinfo['charge'];

        if($chargetype==1){
            $totalcharge = $charge;
        }else{
            $totalcharge = $charge*$money/100;
        }

        //判断今天提现了几次
        $txnumber = getSetConfig('txnumber');
        if(intval($txnumber)>0){
            $timeestart = date("Y-m-d");
            $timeestart = strtotime($timeestart);
            $timeend = $timeestart+8400*24;
            $sql = "SELECT count(withdrawal_id) as total FROM `cd_withdrawal` WHERE member_id=".$member_id." AND dateline>=".$timeestart." AND dateline<".$timeend." AND status<=2";
            $result = Db::query($sql);

            $txcound = $result[0]['total'];
            if($txcound>=$txnumber){
                return $this->reDecodejson(['status'=>201,'msg'=>'一天只能提现'.$txnumber.'次']);
            }
        }
        //查询可提现金额，充值金额不能提现
        $checkInfo =  CavenService::checkTxorder($memberInfo,$money);
        if($checkInfo['status']!=1){
            return $this->reDecodejson(['status'=>$checkInfo['status'],'msg'=>$checkInfo['msg']]);
        }

        $transfer_money=$money-$totalcharge;
        //小于多少不需要审核

        $ordernumber = date('YmdHis').mt_rand(1000,9999);
        Db::startTrans();
        try{
            $data = array(
                'member_id'=>$memberInfo['member_id'],
                'mobile'=>$memberInfo['mobile'],
                'momey'=>$money,
                'transfer_money'=>$transfer_money,
                'coinnumber'=>0,
                'charge'=>$charge, //手续费
                'status'=>1,  //处理中|1|success,已完成|2|warning,交易失败|3|danger
                'dostatus'=>1, //等审核|0|success,审核通过|1|success,审核失败|2|warning
                'dateline'=>time(),
                'updateline'=>time(),
                'payway_id'=>$payway,
                'ip'=>ip(),
                'trade_no'=>$ordernumber,
                'paytype'=>$paywayinfo['paytype'],
                'account'=>$banknumber,
            );
            $orderid = Db::name('withdrawal')->insertGetId($data);

            $where = array();
            $where['member_id'] = $member_id;
            $dodec=Member::setDes($where,'yjmoney',$money);
            if(!$dodec){
                return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'您的账户余额不足，请先充值']);
            }
            $des ='申请提现';
            addPayLog($memberInfo,($memberInfo['yjmoney']-$money),$money,2,5,$des,$ordernumber,2);

            if($orderid){
                Db::commit();
                return $this->reDecodejson(['status'=>200,'msg'=>'提现申请已提交，请耐心等待','data'=>$data]);
            }else{
                Db::rollback();
                return $this->reDecodejson(['status'=>200,'msg'=>'未知错误']);
            }
        }catch (\Exception $e){
            Db::rollback();
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>$e->getMessage()]);
        }
    }


    //购买产品getProductlist
    function doChangeMoney(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $money   = $this->request->post('money');
        $money = (float)$money;
        if(empty($money)){
            return $this->reDecodejson(['status'=>201,'msg'=>'您提交的金额不对']);
        }
        if($money<0){
            return $this->reDecodejson(['status'=>201,'msg'=>'您提交的金额不对']);
        }
        if($money<5){
            return $this->reDecodejson(['status'=>201,'msg'=>'每次划转的金额不能小于5U']);
        }
        $memberInfo = Member::find($member_id);
        if(empty($memberInfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'会员信息错误']);
        }



        if($memberInfo['yjmoney']<$money){
            return $this->reDecodejson(['status'=>'203','msg'=>'您的收益余额不足']);
        }
        $trade_no = date('YmdHis').mt_rand(1000,9999);
        Db::startTrans();
        try {
            //进行支付操作
            $where = array();
            $where['member_id'] = $member_id;
            $dodec = Member::setDes($where, 'yjmoney', $money);
            if (!$dodec) {
                return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '您的收益余额不足']);
            }
            $des = '交易账号划转到资金账号:';

            addPayLog($memberInfo, ($memberInfo['yjmoney'] - $money), $money, 2, 6, $des, $trade_no, 2);
            //生成一条充值分成订单
            $dodata['payway']=8;//管理员入账
            $otherinfo = \app\api\service\PayService::getCzotherinfo($money,$member_id);
            //member_id,money,des,dateline
            $dodata['member_id']=$member_id;
            $dodata['money']=$money;
            if(!empty($otherinfo)&&is_array($otherinfo)){
                $dodata = array_merge($dodata,$otherinfo);
            }
            $dodata['dostatus'] = 1;
            $dodata['inuserid'] = $member_id;
            $dodata['status'] = 2;
            $dodata['des'] = '从收效账号划转充值';
            $dodata['ordernumber']=date('YmdHis').mt_rand(1000,9999);
            $dodata['tradeno']=date('12YmdHis').mt_rand(1000,9999);
            $chargeorder_id = ChargeorderService::add($dodata);
            \app\api\service\CavenService::doChargeorder($chargeorder_id);
            Db::commit();
            //Member::setInc($where, 'umoney', $money);
            //addPayLog($memberInfo, ($memberInfo['umoney'] + $money), $money, 1, 6, $des, $trade_no);

            if (empty($orderid)) {
                return $this->reDecodejson(['status' => '200', 'msg' => '操作成功']);
            }
        }catch (\Exception $e){
            Db::rollback();
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>$e->getMessage()]);
        }

    }
}

