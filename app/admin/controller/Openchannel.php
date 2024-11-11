<?php 
/*
 module:		开通渠道
 create_time:	2023-04-18 09:15:48
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\OpenchannelService;
use app\admin\model\Openchannel as OpenchannelModel;
use think\facade\Db;

class Openchannel extends Admin {


 /*start*/
	/*添加*/
	function add(){
		if (!$this->request->isPost()){
			return view('add');
		}else{
			$postField = 'member_id,groupid,channel,nick,buyvipset,paygetpoint,checkrebate,checkbinance,tradetype,dorule,maxpaygetpoint,twopaygetpoint,threepaygetpoint,twochannel';
			$data = $this->request->only(explode(',',$postField),'post',null);
			 Db::startTrans();
			try{
			    $data['groupid']= intval($data['groupid']);
			    if(empty($data['groupid'])){
			       
			        return $this->error('请选择要复制的会员组');
			    }
			    
			    $sql = "SELECT member_id,mobile,utype,groupid,channel FROM `cd_member` WHERE member_id=".intval($data['member_id']);
			    $memberinfo=Db::query($sql);
			    if(empty($memberinfo)){
			        return $this->error('找不到会员数据');
			    }
			    $memberinfo = $memberinfo[0];
			    if(empty($memberinfo['mobile'])){
			        return $this->error('会员手机号不能为空');
			    }
			    $sql = "SELECT member_id FROM `cd_agent` WHERE member_id=".intval($data['member_id']);
			    $ageninfo=Db::query($sql);
			    if(!empty($ageninfo)){
			       
			        return $this->error('此用户已经是渠道代理了，无需再添加');
			    }
			    
			     $sql = "SELECT * FROM `cd_user` WHERE user='{$data['mobile']}'";
                 $result = Db::query($sql);
                if(!empty($result)){
                    return $this->error('此手机已注册过渠道用户');
                 }
			    
			    $sql = "SELECT member_id FROM `cd_agent` WHERE channel='".$data['channel']."'";
			    $ischannel=Db::query($sql);
			    if(!empty($ischannel)){
			        
			        return $this->error('渠道标识已使用过，请更换后再添加');
			    }
			    
			    if(!empty($data['twochannel'])){
			        $sql = "SELECT channel,twochannel FROM `cd_agent` WHERE channel='".$data['twochannel']."' limit 1";
			        $ischannel=Db::query($sql);
			        if(!empty($ischannel)){
			        
			            return $this->error('上级渠道信息错误');
			        }else{
			            $chaninfo = $ischannel[0];
			            if(!empty($chaninfo['twochannel'])){
			                $data['threechannel'] = $chaninfo['twochannel'];  
			            }else{
			               $data['threepaygetpoint'] = 0;
			            }
			            
			        }
			    }else{
			       $data['twopaygetpoint'] = 0; 
			    }
			    
			    $vippoint =0;
			    if(!empty($data['buyvipset'])){
			        $configdata =  explode('|',$data['buyvipset']);
			        foreach($configdata as $key=>$v){
			            $vippoint+=intval($v);
			        }
			        
			    }
			    $data['paygetpoint'] = intval($data['paygetpoint']);
			    if($data['maxpaygetpoint']>70){
			         return $this->error('渠道最大提成不能超过70%');
			    }
			    $vipandqdaomax = $vippoint+$data['paygetpoint'];
			    if($vipandqdaomax>$data['maxpaygetpoint']){
			         return $this->error('渠道提成信息加会员提成总的不能超过设置的最大提成');
			    }
			    
			    $semaxpoint = $data['maxpaygetpoint']-$vipandqdaomax;
			    if($data['twopaygetpoint']>$semaxpoint){
			       $data['twopaygetpoint']=$semaxpoint; 
			    }
			    $data['threepaygetpoint'] = $data['maxpaygetpoint']-$vipandqdaomax-$data['twopaygetpoint']; 
			    if(empty($data['threechannel'])&&!empty($data['twochannel'])){
			       $data['twopaygetpoint']=$data['maxpaygetpoint']-$vipandqdaomax;;  
			    }
			    
			    $datainfo =\app\admin\model\Tradegroup::find($data['groupid']);
			    unset($data['maxpaygetpoint']);
			    unset($datainfo['id']);
			    $updata=[];
			    $pic = '';
			    if(!empty($datainfo)){
			        $updata['gname']=$datainfo['gname'];
			   
                    $pic=$updata['pic']=$datainfo['pic'];
                    $updata['payttype']=$datainfo['payttype'];
                    $updata['utype']=$datainfo['utype'];
                    $updata['dpoint']=$datainfo['dpoint'];
                    $updata['profitpoint']=$datainfo['profitpoint'];
                    $updata['vippaytype']=$datainfo['vippaytype'];
                    $updata['cucharge']=$datainfo['cucharge'];
                    $updata['des']=$data['nick'];
                    $updata['leaderlist']=$datainfo['leaderlist'];
                    $updata['indexleaderlist']=$datainfo['indexleaderlist']; 
                    $updata['billmoney']=$datainfo['billmoney'];
                    $updata['isfree']=$datainfo['isfree'];
                    $updata['billtype']=$datainfo['billtype'];
			}
			    $updata['pic']=$pic;
			//echo $sql;
			    $groupid = \app\admin\service\TradegroupService::add($updata);
			
			//更新代理组
			    $sql = "UPDATE `cd_member` SET groupid=".$groupid.",utype=2,channel='".$data['channel']."' WHERE member_id=".$data['member_id'];
			    Db::query($sql);
			$data['passwd'] = 'a123456';   
			$data['groupid'] = $groupid;
			//tradetype,dorule
		//	$data['tradetype'] = implode(",",$data['tradetype']);
		//	$data['dorule'] = implode(",",$data['dorule']);;
			$data['freeday'] = 30;
			$data['mobile'] = $memberinfo['mobile'];
			$data['status'] = 1;
			
            $res = \app\admin\service\AgentService::add($data);
            if ($res > 0) {
                $wuid = $res;
                $add = [];
                //$add['pwd'] = $data['pwd'];
                $add['user'] = $data['mobile'];
                $add['mobile'] = $data['mobile'];
                $add['agentid'] = $res;
                $add['member_id'] = $data['member_id'];
                $add['create_time'] = time();
                $add['update_at'] = time();
                $add['name'] = $data['mobile'];
                $add['status'] = 1;
                $add['create_time'] = time();
                
                $add['pwd'] = md5($data['passwd'] . config('my.password_secrect'));
                $add['role_id'] = 3;
                //print_r($add);die;
                
                $res = \app\admin\model\User::create($add);
                if (!empty($res)) {
                    //$updata['id'] = $wuid;
                    //$updata['member_id'] = $member_id;
                    $updata['user_id'] = $res->user_id;
                    $whereup['id'] = $wuid;
                    \app\admin\model\Agent::update($updata, $whereup);


                }else{
                    return $this->error('添加失败');
                }
            }
			    
			    
			    Db::commit();
			    
			}catch(\Exception $e){
			     Db::rollback();
			    abort(config('my.error_log_code'),$e->getMessage());
		    }
		    
		    return json(['status'=>'00','msg'=>'添加成功']);
			
		}
	}

/*end*/



}

