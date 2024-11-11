<?php
namespace app\api\controller;
use app\admin\model\Moneylog;
use app\admin\model\Tradegroup;
use app\admin\service\ProductsetService;
use app\api\model\Apiuser;
use app\api\model\Member;
use app\api\service\CavenService;
use think\facade\Db;
use app\admin\model\Agent;

class MymemberApi extends Common
{
    //取得提现信息
    function gettxmsg(){
        $member_id = $this->request->uid;

        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $where['member_id']=$member_id;
        //utype ==1 普通会员 2 代理会员
        $filed="member_id,yjmoney as umoney";
        $res  =Member::getWhereInfo($where,$filed);
        if(empty($res)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'用户信息获取失败']);
        }
        $redata['money'] = $res['umoney'];
        $redata['txcount'] = '-1';
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
                $redata['txcount'] = 0;
            }else{
                $redata['txcount'] = $txnumber-$txcound;
            }
        }
        $redata['msg']=getSetConfig('inmoneydes');
        return $this->reDecodejson(['status'=>200,'msg'=>'success','data'=>$redata]);
    }
    function syinfo(){
        $member_id = $this->request->uid;

        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }

        $where['member_id']=$member_id;
        //utype ==1 普通会员 2 代理会员
        $filed="member_id,membername,aupay,sex,realname,umoney,groupid,utype,nickname,avatar,mobile,level,utype,billmoney,yjmoney,trademoney";
        $res  =Member::getWhereInfo($where,$filed);
        if(empty($res)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'用户信息获取失败']);
        }
        //查询昨日收益
        $yes = date("Y-m-d",strtotime("-1 day"));
        $sql = "SELECT income as money FROM `cd_income_day` WHERE `day`=".strtotime($yes)." AND userid=".$member_id;
        $resu = Db::query($sql);
        $yesmoney = isset($resu[0]['money'])?$resu[0]['money']:0;

        //查询本月收益
        $billmonth = date("Ym");
        $sql = "SELECT sum(income) as money FROM `cd_income_day` WHERE `billmonth`=".$billmonth." AND userid=".$member_id;
        $resu = Db::query($sql);
        $montotal = isset($resu[0]['money'])?$resu[0]['money']:0;

        $redata['sytotal']=$res['trademoney'];
        $redata['yesmoney']=$yesmoney;
        $redata['montotal']=$montotal;



        return $this->ajaxReturn($this->successCode,'返回成功',$redata);
    }
    /**
     * @api {get} /Member/info 05、查看详情
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  查看详情
     */
    function info(){
        $member_id = $this->request->uid;

        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $where['member_id']=$member_id;
        //utype ==1 普通会员 2 代理会员
        $filed="member_id,profitpoint,email,membername,aupay,sex,realname,umoney,groupid,utype,nickname,avatar,mobile,level,utype,billmoney,yjmoney,trademoney,pwd";
        $res  =Member::getWhereInfo($where,$filed);
        if(empty($res)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'用户信息获取失败']);
        }
        if(empty($res['avatar'])){

            $res['avatar'] =getSysConfig('demthumb'); //dedsthumb
        }
        $redata = [];
        if(empty($res['mobile'])){
            $res['mobile'] = $res['email'];
        }
        $res['apitype']=0;
        $res['pctype']=0;
        $res['xjqdstatus']=0;
        $res['apict']=0;
        if($res['utype']==2){
            //查询渠道权限
            $agentinfo = CavenService::checkAgent($member_id,2);
            if($agentinfo!=203){
                $res['apitype']=1;
            }

            $agentinfo = CavenService::checkAgent($member_id,4);
            if($agentinfo!=203){
                $res['pctype']=1;
            }
            $agentinfo = CavenService::checkAgent($member_id,6);
            if($agentinfo!=203){
                $res['xjqdstatus']=1;
            }
            $agentinfo = CavenService::checkAgent($member_id,7);
            if($agentinfo!=203){
                $res['apict']=1;
            }
        }
        $res['totalmoney']=$res['yjmoney']+$res['umoney'];
        $redata['info'] = $res;
        $whereb['userid'] = $member_id;
        //validtime 有效日期 isvalid 审核状态
        $apiinfo = Apiuser::getWhereInfo($whereb,'isvalid,id,userid,tradename,validtime');
        if(empty($apiinfo)){
            $apiinfo['status']=0;
        }else{
            $apiinfo['status']=1;
        }
        if($apiinfo['isvalid']==0){
            $apiinfo['validtime']='未审核';
        }else{
            if($apiinfo['validtime']<time()){
                $apiinfo['validtime'] = '已过期';
            }else{
                $apiinfo['validtime'] = date('Y-m-d',$apiinfo['validtime']);
            }
        }
        $redata['api']=$apiinfo;
        $redata['syinfo']['total']=($res['trademoney']);
        //查询昨日收益
        $yes = date("Y-m-d",strtotime("-1 day"));
        $sql = "SELECT income as money FROM `cd_income_day` WHERE `day`=".strtotime($yes)." AND userid=".$member_id;
        $resu = Db::query($sql);
        $yesmoney = isset($resu[0]['money'])?$resu[0]['money']:0;
        $redata['syinfo']['yesmoney']=$yesmoney;
        $groupinfo = Tradegroup::find($res['groupid']);
        $regroup = [];
        $viptype = array(
            '1'=>'包月VIP',
            '2'=>'收益VIP',
            '3'=>'贵宾VIP'
        );
        if(!empty($groupinfo)){
            $regroup['pic'] = $groupinfo['pic'];
            $regroup['gname'] = $groupinfo['gname'];
            $regroup['dpoint'] = $groupinfo['dpoint'];
            $regroup['profitpoint'] = (isset($res['profitpoint'])&&$res['profitpoint']>0)?$res['profitpoint']:$groupinfo['profitpoint'];
            $regroup['payttype'] = $groupinfo['payttype'];
            $regroup['payttitle'] = $groupinfo['payttype']==1?"正常":'授权码';
            $regroup['vippaytype'] = $groupinfo['vippaytype'];
            $regroup['vippaytitle'] = $viptype[$groupinfo['vippaytype']];
        }
        if($regroup['vippaytype']==1){
            $regroup['des'] = '<p>1. 费用先支付后使用。</p><p>2. 根据套餐购买相关的时长。</p><p>3. 请在会员过期前提前续费，VIP一过期系统将会自动停止跟单服务。</p><p>4. 可使用大神操盘功能。</p>';
        }
        if($regroup['vippaytype']==3) {
            if ($regroup['billtype'] == 1) {
                $regroup['des'] = '<p>1. 费用收取盈利率的{{regroup.profitpoint}}%。</p><p>2. 先使用后付费，隔天出前一天的收益账单。系统自动扣除。</p><p>3. 请保持资金账号有足够的余额，如果在账单扣费时，余额不够，系统自动停止跟单服务。</p><p>4. 可使用大神操盘功能。</p>';
            }
            if ($regroup['billtype'] == 2) {
                $regroup['des'] = '<p>1. 费用收取盈利率的{{regroup.profitpoint}}%。</p><p>2. 先使用后付费，每周一早上出上一周的收益账单。系统自动扣除。</p><p>3. 请保持资金账号有足够的余额，如果在账单扣费时，余额不够，系统自动停止跟单服务。</p><p>4. 可使用大神操盘功能。</p>';
            }
            if ($regroup['billtype'] == 3) {
                $regroup['des'] = '<p>1. 费用收取盈利率的{{regroup.profitpoint}}%。</p><p>2. 先使用后付费，每个月1到5号出上个月的收益账单。</p><p>3. 10号前结清上个月的的账单后系统自动延期一个月的时间，如果超出2天未支付，系统自动停止跟单服务。</p><p>4. 可使用大神操盘功能。</p>';

            }
        }
        if($regroup['vippaytype']==3){
            $regroup['des'] = '<p>1. 在本站白吃白喝会员。简称白嫖！</p><p>2. 可使用大神操盘功能。</p>';
        }

        //$regroup['des'] = '<p>1,2,3</p>';
        $res['billleftmoney']='-';
        if($groupinfo['vippaytype']==2){
            $res['billleftmoney']=($groupinfo['billmoney']-$res['billmoney']);
        }
        $redata['regroup']=$regroup;
        //查看未支付账单
        $sql = "SELECT title,id,addtime,income,lastpaytime,billmonth,paystatus,money,ordernumber,paystatus FROM `cd_bill` WHERE memberid =".$member_id." AND type=2 AND paystatus=1 order by id asc limit 1";
        $result = Db::query($sql);
        $paybill = [];
        if(!empty($result)&&isset($result[0])){

            $paybillinfo = $result[0];
            $paybill['title'] = $paybillinfo['title'];
            $paybill['addtime'] = date('Y-m-d',$paybillinfo['addtime']);
            $paybill['lastpaytime'] = date('Y-m-d',$paybillinfo['lastpaytime']);
            $paybill['money'] = $paybillinfo['money'];
            $paybill['ordernumber'] = $paybillinfo['ordernumber'];
            $paybill['paystatus'] = $paybillinfo['paystatus'];
            $paybill['income'] = $paybillinfo['income'];
            $paybill['billmonth'] = $paybillinfo['billmonth'];
        }
        $redata['paybill']=$paybill;

        //激活操作

        $aginfo = CavenService::getAgentinfo($res['channel']);
        $jhinfo = [];
        $jhinfo['status']=1;
        $jhinfo['jhmoney'] = 0;
        $jhinfo['des']='';
        //此处加入验证充值信息
        if($aginfo['jhmoney']>0){
            $sql = "SELECT SUM(money) as total FROM `cd_chargeorder` WHERE member_id='".$member_id."' AND status=2";
            $res = Db::query($sql);
            if($res[0]['total']<$aginfo['jhmoney']){
                $jhinfo['status']=0;
                $jhinfo['des']='您只需往账户充值'.$aginfo['jhmoney'].'U到资金账户，系统会自动激活';
            }
            $jhinfo['jhmoney'] =$aginfo['jhmoney'];
        }
        $redata['jhinfo'] = $jhinfo;
        //查出会员组
        return $this->ajaxReturn($this->successCode,'返回成功',$redata);
    }





    //取得我的收益详情
    public function Getincomeday(){
        $terracearr = array(
            'SWAP'=>'合约',
            'ALL'=>'所有'
        );
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $limit  = $this->request->post('per_page', 20, 'intval');
        $ordernumber  = $this->request->post('ordernumber');
        $page = $this->request->post('page', 1, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        $profitpoint = 0.02;
        if(!empty($ordernumber)){
            $wheremember = [];
            $wheremember['member_id']=$uid;
            //utype ==1 普通会员 2 代理会员
            $filed="groupid";
            $memberinfo  =Member::getWhereInfo($wheremember,$filed);
            $whereg=[];
            $whereg['id'] = $memberinfo['groupid'];
            $ginfo = Tradegroup::Where($whereg)->field('id,profitpoint')->find();
            if(!empty($ginfo)) {
                $profitpoint = $ginfo['profitpoint'] / 100;
            }
            $where[] = ['billmonth','=',$ordernumber];
            //$where[] = ['isbill','=',1];
        }
        $where[] = ['userid','=',$uid];
        $field = 'id,userid,day,income,insttype,tradename,createtime';
        $orderby = 'id desc';
        $res = db('income_day')->where($where)
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['day'] = date('Y-m-d',$v['day']);
                $res['data'][$key]['createtime'] = date('m-d H:i:s',$v['createtime']);
                $res['data'][$key]['tradename'] = ($v['tradename']==1)?'币安':'OKEX';
                $res['data'][$key]['insttype'] = $terracearr[$v['insttype']];
                $res['data'][$key]['paymoney'] = sprintf("%.2f",($v['income']*$profitpoint));
            }
        }

        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }

    //取得我的账单列表
    public function Getbill(){
        //未结算|1|success,已结算|2|warning,已失效|3|warning
        $estatusarr = array(
            '1'=>'未结算',
            '2'=>'已结算',
            '3'=>'已失效',
        );
        $paystatus = array(
            '1'=>'未支付',
            '2'=>'已支付',
            '3'=>'活动优惠',
        );
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $trade_no = $this->request->post('ordernumber', '');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        $where[] = ['memberid','=',$uid];
        if(!empty($trade_no)){
            $where[] = ['trade_no','=',$trade_no];
        }
        $field = 'id,memberid,title,billmonth,money,paystatus,income,type,ordernumber,addtime,status,lastpaytime';
        $orderby = 'id desc';
        $res = db('bill')->where($where)
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['addtime'] = date('Y-m-d',$v['addtime']);
                $res['data'][$key]['estatus'] = $estatusarr[$v['status']];
                $res['data'][$key]['epaystatus'] = $paystatus[$v['paystatus']];
                $res['data'][$key]['lastpaytime'] = date('Y-m-d',$v['lastpaytime']);
            }
        }

        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }

    //取得我的账单提成列表
    public function GetMyDownbill(){
        //未结算|1|success,已结算|2|warning,已失效|3|warning
        $estatusarr = array(
            '1'=>'未结算',
            '2'=>'已结算',
            '3'=>'已失效',
        );
        $paystatus = array(
            '1'=>'未生效',
            '2'=>'已生效',
            '3'=>'活动优惠',
        );
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $type  = $this->request->post('type', 1, 'intval');
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $childuid  = $this->request->post('childuid', 0, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        if(!in_array($type,array(1,2,3))){
            $type=1;
        }
        if(!empty($childuid)){
            $where[] = ['memberid','=',$childuid];
        }
        if($type==1){
            $where[] = ['parentid','=',$uid];
            $field = 'id,mobile,memberid,parentmoney as money,paystatus,type,ordernumber,addtime,status';
        }
        if($type==2){
            $where[] = ['twoid','=',$uid];
            $field = 'id,mobile,memberid,twomoney as money,paystatus,type,ordernumber,addtime,status';
        }
        if($type==3){
            $where[] = ['threeid','=',$uid];
            $field = 'id,mobile,memberid,threemoney as money,paystatus,type,ordernumber,addtime,status';
        }

        $orderby = 'id desc';
        $res = db('bill')->where($where)
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['addtime'] = date('Y-m-d',$v['addtime']);
                $res['data'][$key]['estatus'] = $estatusarr[$v['status']];
                $res['data'][$key]['epaystatus'] = $paystatus[$v['paystatus']];
                $res['data'][$key]['mobile'] = hiden_mymoblie($v['mobile']);
            }
        }

        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }


    //取得我的下级列表
    public function GetMyTgmember(){
        $estatusarr=array(
            '1'=>'正常',
            '2'=>'锁定'
        );
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $utypeen = getUtype();
        $type  = $this->request->post('type', 1, 'intval');
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        if(!in_array($type,array(1,2,3))){
            $type=1;
        }
        if($type==1){
            $where[] = ['parentid','=',$uid];
        }
        if($type==2){
            $where[] = ['twoid','=',$uid];
        }
        if($type==3){
            $where[] = ['threeid','=',$uid];
        }
        $defaultavatar = getSysConfig('demthumb');
        $field = 'member_id as memberid,nickname as mobile,avatar,regtime,status,utype';
        $orderby = 'member_id desc';
        $memberidarr= [];
        $memberidakey= [];

        $wheremember = [];
        $wheremember['member_id']=$uid;
        //utype ==1 普通会员 2 代理会员
        $filed="utype";
        $memberinfo  =Member::getWhereInfo($wheremember,$filed);
        if(!empty($memberinfo)){
            $isdown = $memberinfo['utype']>=2?1:0;
        }
        $res = db('member')->where($where)
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['regtime'] = date('Y-m-d',$v['regtime']);
                $res['data'][$key]['status'] = $estatusarr[$v['status']];
                $res['data'][$key]['avatar'] = empty($v['avatar'])?$defaultavatar:$v['avatar'];
                $res['data'][$key]['mobile'] = hiden_mymoblie($v['mobile']);
                $res['data'][$key]['tcmoney'] = CavenService::getdownsy($v['memberid'],$type);
                $res['data'][$key]['level'] = $utypeen[$v['utype']]['title'];
                $res['data'][$key]['isdown'] = $isdown;
                $res['data'][$key]['number'] = 0;
                $memberidarr[]=$v['memberid'];
                $memberidakey[$v['memberid']]=$key;
            }
        }

        if(!empty($memberidarr)){
            $uidarr =  implode(",",$memberidarr);
            $sql = "SELECT count(*) as total,parentid FROM `cd_member` WHERE parentid in(".$uidarr.") GROUP BY parentid";

            $result =Db::query($sql);
            if(!empty($result)){
                foreach($result as $key=>$v){
                    $res['data'][$memberidakey[$v['parentid']]]['number']=intval($v['total']);
                }
            }

        }
        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }


    //取得我的下级列表
    public function GetMyTgDownmember(){
        $estatusarr=array(
            '1'=>'正常',
            '2'=>'锁定'
        );
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $puid  = $this->request->post('puid', 0, 'intval');
        $userid  = $this->request->post('userid', 0, 'intval');
        $utypeen = getUtype();
        $type  = $this->request->post('type', 1, 'intval');
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        if(!in_array($type,array(1,2,3))){
            $type=1;
        }
        if($type==1){
            $where[] = ['parentid','=',$puid];
        }
        if($type==2){
            $where[] = ['twoid','=',$uid];
        }
        if($type==3){
            $where[] = ['threeid','=',$uid];
        }
        if(!empty($userid)){
            $where[] = ['member_id','=',$userid];
        }

        $wheremember = [];
        $wheremember['member_id']=$uid;
        //utype ==1 普通会员 2 代理会员
        $filed="utype,channel";
        $isdown = 0;
        $memberinfo  =Member::getWhereInfo($wheremember,$filed);
        if(!empty($memberinfo)){
            $isdown = $memberinfo['utype']==2?1:0;
        }
        if($memberinfo['utype']==3){
            $where[] = ['twoid','=',$uid];
        }
        if($memberinfo['utype']==2){
            $where[] = ['channel','=',$memberinfo['channel']];
        }

        $defaultavatar = getSysConfig('demthumb');
        $field = 'member_id as memberid,nickname as mobile,avatar,regtime,status,utype';
        $orderby = 'member_id desc';
        $memberidarr= [];
        $memberidakey= [];
        $res = db('member')->where($where)
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['regtime'] = date('Y-m-d',$v['regtime']);
                $res['data'][$key]['status'] = $estatusarr[$v['status']];
                $res['data'][$key]['avatar'] = empty($v['avatar'])?$defaultavatar:$v['avatar'];
                $res['data'][$key]['mobile'] = hiden_mymoblie($v['mobile']);
                $res['data'][$key]['tcmoney'] = CavenService::getdownsy($v['memberid'],$type);
                $res['data'][$key]['level'] = $utypeen[$v['utype']]['title'];
                $res['data'][$key]['isdown'] = $isdown;
                $res['data'][$key]['number'] = 0;
                $memberidarr[]=$v['memberid'];
                $memberidakey[$v['memberid']]=$key;
            }
        }

        if(!empty($memberidarr)){
            $uidarr =  implode(",",$memberidarr);
            $sql = "SELECT count(*) as total,parentid FROM `cd_member` WHERE parentid in(".$uidarr.") GROUP BY parentid";

            $result =Db::query($sql);
            if(!empty($result)){
                foreach($result as $key=>$v){
                    $res['data'][$memberidakey[$v['parentid']]]['number']=intval($v['total']);
                }
            }

        }
        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }


    //取得推送消息列表
    public function GetMsg(){

        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $limit  = $this->request->post('per_page', 20, 'intval');
        $luser  = $this->request->post('luser');
        $type  = $this->request->post('type');
        $page = $this->request->post('page', 1, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        if(in_array($type,array('leaderTrade','trade'))){
            $type = $type;
        }else{
            $type='trade';
        }
        $where = array();
        if($type!='trade'){
            if(empty($luser)){
                $res = [];
                return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
            }
            $uid = $luser;
        }
        $where[] = ['userid','=',$uid];
        $field = '*';
        $orderby = 'id desc';
        $res = db('pushmsg')->where($where)
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['time'] = date('m-d H:i:s',$v['time']);
                $res['data'][$key]['etype'] = ($v['type']=='trade')?'交易':'其它';
            }
        }

        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }


    //取得资金流水
    public function GetMonylog(){
        $estatusarr=array(
            '1'=>'充值',
            '2'=>'提成',
            '3'=>'扣费',
            '4'=>'还款',
            '5'=>'提现'
        );
        $etypearr = array(
            '1'=>'收入',
            '2'=>'支出'
        );
        //充值|1|success,提成|2|warning,扣费|3|nfo,还款|4|danger
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $trantype  = $this->request->post('trantype', 0, 'intval');
        $type  = $this->request->post('type', 0, 'intval');
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        $where[] = ['member_id','=',$uid];
        if(in_array($type,array(1,2))){
            $where[] = ['type','=',$type];
        }
        if(in_array($trantype,array(1,2,3,4,5))){
            $where[] = ['trantype','=',$trantype];
        }
        $field = '*';
        $orderby = 'moneylog_id desc';
        $res = db('moneylog')->where($where)
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['dateline'] = date('m-d H:i:s',$v['dateline']);
                $res['data'][$key]['etrantype'] = $estatusarr[$v['trantype']];
                $res['data'][$key]['etype'] = $etypearr[$v['type']];
                $res['data'][$key]['emoney'] = $v['type']==1?'+'.$v['money']:'-'.$v['money'];
            }
        }

        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }

    //取得资金详情
    public function GetMonyloginfo(){
        $estatusarr=array(
            '1'=>'充值',
            '2'=>'提成',
            '3'=>'扣费',
            '4'=>'还款',
            '5'=>'提现',
        );
        $etypearr = array(
            '1'=>'收入',
            '2'=>'支出'
        );
        //充值|1|success,提成|2|warning,扣费|3|nfo,还款|4|danger
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $ordernumber = $this->request->post('ordernumber', '');
        $where=[];
        $where['trade_no']=$ordernumber;
        $where['member_id'] = $uid;
        $billinfo = Moneylog::Where($where)->field("*")->find();
        if(empty($billinfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'交易记录错误']);
        }
        $billinfo['etrantype'] = $estatusarr[$billinfo['trantype']];
        $billinfo['etype'] = $etypearr[$billinfo['type']];
        return $this->ajaxReturn($this->successCode,'SUCCESS',$billinfo);
    }

    /**
     * 用户提现记录
     */
    public function withdrawalList(){
        //等待处理|1|success,已处理|2|warning,处理失败|3|warning
        $estatusarr=array(
            '1'=>'等待处理',
            '2'=>'提现成功',
            '3'=>'提现失败'
        );
        $uid = $this->request->uid;
        $per_page  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $orderby = 'withdrawal_id desc';

        $where = [];
        $where['member_id'] = $uid;
        $status = $this->request->post('status','');
        if(in_array($status,array(1,2,3))){
            $where['status'] = $status;
        }
        $trade_no = $this->request->post('trade_no','');
        if(!empty($trade_no)){
            $where['trade_no'] = $trade_no;
        }
        $field = 'withdrawal_id,member_id,momey,charge,transfer_money,status,trade_no,dostatus,paytype,dateline,updateline';
        $res = db('withdrawal')->where(formatWhere($where))
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$per_page,'page'=>$page])->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['dateline'] = date('m-d H:i:s',$v['dateline']);
                $res['data'][$key]['updateline'] = date('m-d H:i:s',$v['updateline']);
                $res['data'][$key]['estatus'] = $estatusarr[$v['status']];
            }
        }
        return $this->ajaxReturn($this->successCode,'SUCCESS',$res);
    }

    //查看推荐会员数
    function getCountInfoLevel(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $where['member_id']=$member_id;
        $sql = "SELECT COUNT(*) as total FROM `cd_member` WHERE parentid=".$member_id;
        $result = Db::query($sql);
        $tjtotal = isset($result[0]['total'])?$result[0]['total']:0;

        //查询昨日收益
        $sql = "SELECT sum(parentmoney) as money FROM `cd_bill` WHERE  parentid=".$member_id;
        $resu = Db::query($sql);
        $yesmoney = isset($resu[0]['money'])?$resu[0]['money']:0;
        $redata['tjtital']=$tjtotal;
        $redata['totalmoney']=$yesmoney;
        $redata['memberid']=$member_id;

        return $this->ajaxReturn($this->successCode,'返回成功',$redata);
    }

    function upaupay(){
        $member_id = $this->request->uid;

        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $aupay  = $this->request->post('aupay', 0, 'intval');
        if(in_array($aupay,array(0,1))){
            $sql = "UPDATE `cd_member` set aupay=".intval($aupay)." WHERE member_id=".$member_id;
            $result = Db::query($sql);
        }
        return $this->ajaxReturn($this->successCode,'返回成功',[]);
    }


    /**
     * @api {get} /Member/info 05、查看详情
     * @apiGroup Member
     * @apiVersion 1.0.0
     * @apiDescription  查看详情
     */
    function getTg(){
        $member_id = $this->request->uid;

        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $where['member_id']=$member_id;
        //utype ==1 普通会员 2 代理会员
        $filed="member_id,profitpoint,membername,groupid,channel";
        $res  =Member::getWhereInfo($where,$filed);
        if(empty($res)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'用户信息获取失败']);
        }

        $where=[];
        $where['channel'] = $res['channel'];
        $ageninfo = Agent::Where($where)->field('*')->find();
        $topusertcarr = CavenService::getTcByChannel('',1,$ageninfo['buyvipset'],2);


        $member['member_id']=$member_id;
        $member['groupid']=$res['groupid'];
        $member['tgthumb'] = CavenService::get_qrcode($member_id,0);
        $wapurl =  getSetConfig('wapurl');
        $url = $wapurl.'/#/pages/common/login/recommend?reuid='.$member_id;
        $member['tgurl'] = $url;
        $redata['member'] =$member;
        $wherec['isbuy']=1;
        // $where['showtype']=$showtype;

        $test = [];
        $title = array(
            '0'=>'一级下级会员',
            '1'=>'二级下级会员',
            '2'=>'三级下级会员',
        );
        if(!empty($topusertcarr)){
            foreach($topusertcarr as $key=>$v){
                if($v>0){
                    $test[]=$title[$key].'充值消费金额的'.$v.'%的提成';
                }


            }
        }
        if(!empty($test)){
            $redata['vippro'] =implode(";",$test);
        }else{
            $redata['vippro'] ='';
        }

        //查出会员组
        return $this->ajaxReturn($this->successCode,'返回成功',$redata);
    }


    public function getsummsg(){
        $uid = $this->request->uid;
        $sql = "SELECT count(*) as total FROM `cd_mysg`  where status=0 AND member_id=".$uid;
        $result = Db::query($sql);
        if(isset($result[0])){
            $total = $result[0]['total'];
        }else{
            $total = 0;
        }
        $data['total'] = $total;
        return $this->ajaxReturn($this->successCode,'返回成功',$data);
    }
    /**
     * 用户提现记录
     */
    public function msgList(){
        //系统|1|success,其它|2|danger,好友申请消息|3|warning,提示小消息|4|danger
        $estatusarr=array(
            '1'=>'已读',
            '2'=>'未读',
        );
        $mtypearr=array(
            '1'=>'系统',
            '2'=>'其它',
            '3'=>'提示小消息'
        );
        $uid = $this->request->uid;
        $per_page  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $orderby = 'message_id desc';

        $where = [];
        $where['member_id'] = $uid;
        $status = $this->request->post('status','');
        if(in_array($status,array(1,2,3))){
            $where['status'] = $status;
        }

        $field = '*';
        $res = db('mysg')->where(formatWhere($where))
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$per_page,'page'=>$page])->toArray();
        $idarr = [];
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['dateline'] = date('m-d H:i:s',$v['dateline']);
                $res['data'][$key]['updateline'] = date('m-d H:i:s',$v['updateline']);
                $res['data'][$key]['emtype'] = $mtypearr[$v['mtype']];
                $res['data'][$key]['estatus'] = $estatusarr[$v['status']];
                $idarr[]=$v['message_id'];
            }
        }
        if(!empty($idarr)){
            $sql = "UPDATE `cd_mysg` set status=1 where message_id in(".implode(",",$idarr).") AND status=0 AND member_id=".$uid;
            Db::query($sql);
        }
        return $this->ajaxReturn($this->successCode,'SUCCESS',$res);
    }
    
    
   
}
?>