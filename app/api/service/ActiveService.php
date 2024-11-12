<?php
/*
 module:		会员列表
 create_time:	2020-05-10 13:00:18
 author:		
 contact:		
*/

namespace app\api\service;
use app\admin\model\Activerecord;
use app\admin\service\ApiuserService;
use think\facade\Db;
use xhadmin\CommonService;
use app\api\model\Member;
class ActiveService extends CommonService {

    //传入订单信息 $orderinfo['member_id','upmember_id','channel']，$actype 活类型 注册新用户|1|success,推荐新用户|2|warning
    public static function doActive($orderinfo,$acttype='1'){
        $time =time();
        $otherw = '';
        if($acttype==1){
           $otherw = " AND mtype=1";
        }
        if($acttype==2){
            $otherw = " AND mtype=2";
        }
        $sql="SELECT * FROM `cd_activelist` WHERE hdmodule=".$acttype." AND status=1 AND starttime<=".$time." AND endtime>=".$time.$otherw."  ORDER BY id asc";
        $result = Db::query($sql);
        if(!empty($result)){
            foreach($result as $key=>$v){

                if(!empty($v['groupset'])){
                    $channelarr = explode(',',$v['groupset']);

                    if(!in_array($orderinfo['channel'],$channelarr)){
                        continue;
                    }
                    self::dooneActive($v,$orderinfo);
                }

            }
        }
    }
    //处理活动
    public static function dooneActive($actinfo,$orderinfo){
        $channel = $orderinfo['channel'];
        $member_id = $orderinfo['member_id'];
        if($actinfo['actnumber']>0){
            $sql = "SELECT count(id) as total FROM `cd_activerecord` WHERE actid=".$actinfo['id']." AND member_id=".$orderinfo['member_id'];
            $result = Db::query($sql);
            $total = $result[0]['total'];
            if($actinfo['actnumber']<=$total){
                return;
            }
        }else{
            //oneuser
            $actinfo['oneuser'] = intval($actinfo['oneuser']);
            if($actinfo['actnumber']==0){
                if($actinfo['mtype']==2&&$actinfo['oneuser']>0){
                    $sql = "SELECT count(id) as total FROM `cd_activerecord` WHERE actid=".$actinfo['id']." AND domember_id='".$orderinfo['domember_id']."' AND member_id=".$orderinfo['member_id'];
                    $result = Db::query($sql);
                    $total = $result[0]['total'];
                    if($actinfo['oneuser']<=$total){
                        return;
                    }
                }
            }
        }
        $daystatus = 0;
        $mstatus = 1;
        $ordernumber=date('YmdHis').mt_rand(10,99).'-'.$actinfo['id'];
        if($actinfo['money']>0){//处理会员天数
            $money = $actinfo['money'];
            $memberInfo = Member::find($member_id);
            $sql = "UPDATE `cd_member` SET umoney=umoney+".$money." WHERE member_id=".$member_id;
            Db::query($sql);
            $des = $actinfo['des'];
            addPayLog($memberInfo, ($memberInfo['umoney'] + $money), $money, 1, 8, $des, $ordernumber, 1);
        }
        if($actinfo['hyday']>0){//处理会员天数
            $sql = "SELECT * FROM `cd_apiuser` WHERE userid=".$member_id." AND isvalid=1 limit 1";
            $result = Db::query($sql);
            if(!empty($result)){

                $apiinfo = $result[0];
                if(!empty($apiinfo['validtime'])&&$apiinfo['validtime']>time()){
                    //return $this->ajaxReturn($this->successCode, 'SUCCESS' );
                    $validtime = $apiinfo['validtime'];
                    $no = date('Y-m-d',$validtime);
                    $validtime = date($no,strtotime("+1 month"));
                }else{
                    $validtime = date("Y-m-d",strtotime("+1 month"));
                }

                $updata['id']=$apiinfo['id'];
                $updata['validtime']=$validtime;
                $updata['updatetime'] = date('Y-m-d H:i:s');
                $godate = date('Y-m-d H:i:s',strtotime($updata['validtime']));
                $res = ApiuserService::update($updata);
                $updata = [];
                $updata['validTime']=$godate;
                $updata['isValid']=1;
                \app\api\service\BinanceService::auditUser($updata,$member_id);

                $daystatus = 1;
            }
        }
        $data['actid'] = $actinfo['id'];
        $data['member_id'] = $member_id;
        $data['hyday'] = $actinfo['hyday'];
        $data['money'] = $actinfo['money'];
        $data['channel'] = $channel;
        if($actinfo['member_id']!=$orderinfo['domember_id']){
            $data['des'] = $actinfo['des'].',下级会员ID:'.$actinfo['domember_id'];
        }else{
            $data['des'] = $actinfo['des'];
        }
        $data['domember_id'] = $orderinfo['domember_id'];
        $data['dateline'] = time();
        $data['daystatus'] = $daystatus;
        $data['mstatus'] = $mstatus;
        Activerecord::create($data);
    }


}

