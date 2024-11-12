<?php
namespace app\api\controller;
use app\api\model\Agent;
use app\admin\model\Tradegroup;
use app\admin\service\ChargeorderService;
use app\admin\service\MeadmoneylogService;
use app\api\model\Apiuser;
use app\api\model\Member;
use app\api\service\BinanceService;
use app\api\service\CavenService;
use app\admin\service\ApiuserService;
use app\admin\model\Chargeorder;
use app\admin\model\Withdrawal;
use think\facade\Db;

class AgentApi extends Common
{


    public function  _checkdown($member_id,$channel){
        $wheremember['channel'] = $channel;
        $wheremember['member_id'] = $member_id;
        $memberinfo  =Member::getWhereInfo($wheremember,"*");
        return empty($memberinfo)?'0':'1';
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
        $agentinfo = CavenService::checkAgent($uid);
        if(empty($agentinfo)){
            return $this->ajaxReturn(201, 'error');
        }
        $channel=$agentinfo['channel'];
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $childuid  = $this->request->post('childuid', 0, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();

        if(!empty($childuid)){
            $where[] = ['channel','=',$channel];
        }
        $where[] = ['channel','=',$channel];
        $field = 'id,mobile,memberid,channelmoney as money,paystatus,type,ordernumber,addtime,status';

        $orderby = 'id desc';
        $res = db('bill')->where($where)
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['addtime'] = date('Y-m-d',$v['addtime']);
                $res['data'][$key]['memberid'] = $v['memberid'];
                $res['data'][$key]['estatus'] = $estatusarr[$v['status']];
                $res['data'][$key]['epaystatus'] = $paystatus[$v['paystatus']];
                $res['data'][$key]['mobile'] = hiden_mymoblie($v['mobile']);
            }
        }

        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }


//取得渠道会员账单信息
    public function GetAgentMyDownbill(){
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
        $agentinfo = CavenService::checkAgent($uid);
        if(empty($agentinfo)){
            return $this->ajaxReturn(201, 'error');
        }
        $channel=$agentinfo['channel'];
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $childuid  = $this->request->post('childuid', 0, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        $where[] = ['a.channel','=',$channel];
        if(!empty($childuid)){
            $where[] = ['memberid','=',$childuid];
        }
        
        $field = 'a.id,b.mobile,a.memberid,a.money,a.paystatus,a.type,a.ordernumber,a.addtime,a.status';

        $orderby = 'a.id desc';
        $res = db('bill')->where($where)
            ->field($field)->alias('a')->join('member b','a.memberid=b.member_id','right')
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['id'] = $v['id'];
                $res['data'][$key]['addtime'] = date('Y-m-d',$v['addtime']);
                $res['data'][$key]['memberid'] = $v['memberid'];
                $res['data'][$key]['estatus'] = $estatusarr[$v['status']];
                $res['data'][$key]['epaystatus'] = $paystatus[$v['paystatus']];
                $res['data'][$key]['mobile'] = hiden_mymoblie($v['mobile']);
            }
        }

        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }

    //取得渠道会员每日盈利情况
    public function GetAgentDaybill(){
        //未结算|1|success,已结算|2|warning,已失效|3|warning
        $estatusarr = array(
            '1'=>'未结算',
            '2'=>'已结算',
        );
        //按天统计|1|success,按周统计|2|warning,按月统计|3|warning
        $paystatus = array(
            '1'=>'按天统计',
            '2'=>'按周统计',
            '3'=>'按月统计',
        );
        $terracearrb = array(
            'SWAP'=>'合约',
            'ALL'=>'所有'
        );
        $uid = $this->request->uid;
        $agentinfo = CavenService::checkAgent($uid);
        if(empty($agentinfo)){
            return $this->ajaxReturn(201, 'error');
        }
        $channel=$agentinfo['channel'];
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $childuid  = $this->request->post('childuid', 0, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        $where[] = ['a.channel','=',$channel];
        if(!empty($childuid)){
            $where[] = ['userid','=',$childuid];
        }
        
        //$res['rows'] = db('pgorder')->field('a.oz_tracking_number,b.tname,b.userid')->alias('a')->join('shoplist b','a.appid=b.appid','left')->where($where)->limit(config('my.max_dump_data'))->order('a.id desc')->select();
        
        $field = 'a.id,a.userid,a.day,a.income,a.insttype,a.tradename,a.createtime,b.mobile,a.isbill,a.billtype';

        $orderby = 'a.id desc';
        $res = db('income_day')->where($where)
            ->field($field)->alias('a')->join('member b','a.userid=b.member_id','right')
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['id'] = $v['id'];
                $res['data'][$key]['day'] = date('Y-m-d',$v['day']);
                $res['data'][$key]['memberid'] =$v['memberid'];
                $res['data'][$key]['isbill'] = $estatusarr[$v['isbill']];
                $res['data'][$key]['billtype'] = $paystatus[$v['billtype']];
                $res['data'][$key]['mobile'] = hiden_mymoblie($v['mobile']);
                $res['data'][$key]['createtime'] = date('m-d H:i:s',$v['createtime']);
                $res['data'][$key]['tradename'] = ($v['tradename']==1)?'币安':'OKEX';
                $res['data'][$key]['insttype'] = $terracearrb[$v['insttype']];
                $res['data'][$key]['paymoney'] = sprintf("%.2f",($v['income']));
            }
        }

        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }

    //取得我的充值提成记录
    public function GetMyDownCzList(){
        //未结算|1|success,已结算|2|warning,已失效|3|warning
        $estatusarr = array(
            '1'=>'未支付',
            '2'=>'未结算',
            '3'=>'已失效',
            '4'=>'已结算',
        );
        $paystatus = array(
            '1'=>'未支付',
            '2'=>'已支付',
            '3'=>'已失效',
            '4'=>'已结算',
        );
        $uid = $this->request->uid;
        $agentinfo = CavenService::checkAgent($uid);
        if(empty($agentinfo)){
            return $this->ajaxReturn(201, 'error');
        }
        $channel=$agentinfo['channel'];
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $userid  = $this->request->post('userid', 0, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        $where[] = ['channel','=',$channel];
        if(!empty($userid)){
            $where[] = ['member_id','=',$userid];
        }
        $field = 'chargeorder_id,mobile,member_id,money,channelmoney as tcmoney,status,des,dostatus,ordernumber,dateline,status';

        $orderby = 'chargeorder_id desc';
        $res = db('chargeorder')->where($where)
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['dateline'] = date('Y-m-d',$v['dateline']);
                $res['data'][$key]['estatus'] = $estatusarr[$v['dostatus']];
                $res['data'][$key]['epaystatus'] = $paystatus[$v['status']];
                $res['data'][$key]['mobile'] = hiden_mymoblie($v['mobile']);
            }
        }

        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }


    //取得我的下级列表
    public function GetMyTgmember(){
        $uid = $this->request->uid;
        $agentinfo = CavenService::checkAgent($uid);
        if(empty($agentinfo)){
            return $this->ajaxReturn(201, 'error');
        }
        $channel=$agentinfo['channel'];
        $estatusarr=array(
            '1'=>'正常',
            '2'=>'锁定'
        );
        $utypeen = getUtype();
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }

        $agentinfo = CavenService::checkAgent($uid,4);
        $pctype = 0;
        if($agentinfo!=203){
            $pctype=1;
        }

        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $userid  = $this->request->post('userid', 0, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        $where[] = ['channel','=',$channel];
        if(!empty($userid)){
            $where[] = ['member_id','=',$userid];
        }
        $defaultavatar = getSysConfig('demthumb');
        $field = 'member_id as memberid,nickname as mobile,avatar,regtime,status,utype,usdt,swapusdt,spotusdt,remark';
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
                $memberidarr[]=$v['memberid'];
                //同步会员资金
                /*
                $reblance = BinanceService::userStatMain($v['memberid']);
                $res['data'][$key]['usdt']=$reblance['usdt'];
                $res['data'][$key]['swapusdt']=$reblance['swapusdt'];
                $res['data'][$key]['spotusdt']=$reblance['spotusdt'];
                */
                $res['data'][$key]['regtime'] = date('Y-m-d',$v['regtime']);
                $res['data'][$key]['status'] = $estatusarr[$v['status']];
                $res['data'][$key]['avatar'] = empty($v['avatar'])?$defaultavatar:$v['avatar'];
                $res['data'][$key]['mobile'] = hiden_mymoblie($v['mobile']);
                $res['data'][$key]['tcmoney'] = CavenService::getdownsy($v['memberid'],4);
                $res['data'][$key]['level'] = $utypeen[$v['utype']]['title'];
                $res['data'][$key]['isdown'] = 1;
                $res['data'][$key]['number'] = 0;
                $res['data'][$key]['pctype'] = $pctype;
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
        $agentinfo = CavenService::checkAgent($uid);
        if(empty($agentinfo)){
            return $this->ajaxReturn(201, 'error');
        }
        $userid  = $this->request->post('userid', 0, 'intval');
        $limit  = $this->request->post('per_page', 20, 'intval');
        $luser  = $this->request->post('luser');
        $type  = $this->request->post('type');
        $page = $this->request->post('page', 1, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;

        $isdown = $this->_checkdown($userid,$agentinfo['channel']);
        if(!$isdown){
            return $this->ajaxReturn(201, 'error');
        }

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
        $where[] = ['userid','=',$userid];
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
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($uid);
        if(empty($agentinfo)){
            return $this->ajaxReturn(201, 'error');
        }
        $estatusarr=array(
            '1'=>'充值',
            '2'=>'提成',
            '3'=>'划转到收益',
            '4'=>'转账给下级',
        );
        $etypearr = array(
            '1'=>'收入',
            '2'=>'支出',
        );
        $userid = $agentinfo['user_id'];
        //充值|1|success,提成|2|warning,扣费|3|nfo,还款|4|danger

        $trantype  = $this->request->post('trantype', 0, 'intval');
        $type  = $this->request->post('type', 0, 'intval');
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        $where[] = ['user_id','=',$userid];
        if(in_array($type,array(1,2))){
            $where[] = ['type','=',$type];
        }
        if(in_array($trantype,array(1,2,3,4,5))){
            $where[] = ['trantype','=',$trantype];
        }
        $field = '*';
        $orderby = 'id desc';
        $res = db('meadmoneylog')->where($where)
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res['data'])){
            foreach($res['data'] as $key=>$v){
                $res['data'][$key]['dateline'] = date('m-d H:i:s',$v['dateline']);
                $res['data'][$key]['etrantype'] = $estatusarr[$v['tradetype']];
                $res['data'][$key]['etype'] = $etypearr[$v['type']];
                $res['data'][$key]['emoney'] = $v['type']==1?'+'.$v['money']:'-'.$v['money'];
            }
        }

        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }



    //查看推荐会员数
    function getCountInfoLevel(){
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($uid);
        if(empty($agentinfo)){
            return $this->ajaxReturn(201, 'error');
        }
        $channel=$agentinfo['channel'];
        $sql = "SELECT COUNT(*) as total FROM `cd_member` WHERE channel='{$channel}'";
        $result = Db::query($sql);
        $tjtotal = isset($result[0]['total'])?$result[0]['total']:0;
        $redata['tjtital']=$tjtotal;

        //查询昨日收益
        $sql = "SELECT sum(channelmoney) as money FROM `cd_bill` WHERE  channel='{$channel}'";
        $resu = Db::query($sql);
        $yesmoney = isset($resu[0]['money'])?$resu[0]['money']:0;
        $redata['tjtital']=$tjtotal;
        $redata['totalmoney']=$yesmoney;
        return $this->ajaxReturn($this->successCode,'返回成功',$redata);
    }
    public static function _checkUser($uid){
        $re = ['status' => 200, 'msg' => '','data'=>''];
        $whereb['member_id'] = $uid;
        $memberinfo = \app\api\model\Member::getWhereInfo($whereb);
        if(empty($memberinfo)){
            $re = ['status' => 201, 'msg' => '用户不存在'];
        }
        return $re;
    }

    //获取用户仓位信息
    public function getUserAccount(){

        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($uid);
        if(empty($agentinfo)){
            return $this->ajaxReturn(201, 'error');
        }
        $userid  = $this->request->post('userid', 0, 'intval');
        $isdown = $this->_checkdown($userid,$agentinfo['channel']);
        if(!$isdown){
            return $this->ajaxReturn(201, 'error');
        }
        $type =  $this->request->post('type', '0', 'intval');
        $re = ['status' => 200, 'msg' => '','data'=>[]];
        if(empty($uid)){
            $re = ['status' => 201, 'msg' => '请先登陆'];
            return $this->reDecodejson($re);
        }
        $ckresult = self::_checkUser($userid);
        if($ckresult['status']!=200){
            return $this->reDecodejson($ckresult);
        }

        $re['data']['apiuser']['status'] = '202';
        $re['data']['apiuser']['data'] = '202';
        //先查询用户本地信息
        $where['userid'] = $userid;
        $info = Apiuser::getWhereInfo($where);
        if(!empty($info)){
            if($info['isvalid']!=1){
                $apiuser['status'] = 202;
                $apiuser['msg'] = 'API未审核';
                $re['data']['apiuser'] = $apiuser;
            }else{
                $result =  BinanceService::parseFromApi($userid,'getUserAccount');
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
            $apiuser['msg'] = '未设置API接口';
            $re['data']['apiuser'] = $apiuser;
        }
        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 2;
        $opdata['opaction']='AgentApi:getUserAccount';
        $opdata['opdes']='代理查看用户:'.$userid.'仓位信息';
        CavenService::Oplog($opdata);
        return $this->reDecodejson($re);
    }

    //取得API列表
    public function getApilist(){
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($uid,2);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }

        $estatusarr=array(
            '1'=>'已审核',
            '0'=>'未审核'
        );
        $channel=$agentinfo['channel'];
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $mobile = $this->request->param('mobile', '', 'serach_in');
        $uid = $this->request->param('uid', '', 'serach_in');
        $uid = intval($uid);
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = [];
        $where['b.channel'] = $channel;
        if(!empty($mobile)){
            $where['b.mobile'] = $mobile;
        }
        if(!empty($uid)){
            $where['b.member_id'] = $uid;
        }

        $field = 'b.mobile,b.member_id as uid,a.tradename,a.validtime,a.createtime,a.updatetime,a.isvalid';
        $orderby = 'a.isvalid,a.validtime';


        $where = formatWhere($where);
        try{
            $res = db('apiuser')->field($field)->alias('a')->join('member b','a.userid=b.member_id','left')->where($where)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page]);
        }catch(\Exception $e){
            abort(500,$e->getMessage());
        }
        $data = [];
        $rows = $res->items();
        if(!empty($rows)){
            foreach($rows as $key=>$v){

                $data[$key]['mobile'] =$v['mobile'];
                $data[$key]['uid'] = $v['uid'];
                $data[$key]['tradename'] = $v['tradename']==1?'OKEX':'Binance';
                //$data[$key]['validtime'] = $v['validtime'];
                //$data[$key]['createtime'] =$v['createtime'];
                //$data[$key]['updatetime'] = $v['updatetime'];
                $data[$key]['isvalid'] = $v['isvalid'];
                $data[$key]['validtime'] = date('Y-m-d',$v['validtime']);
                $data[$key]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                $data[$key]['isvalid_title'] = $estatusarr[$v['isvalid']];
                $data[$key]['mobile'] = hiden_mymoblie($v['mobile']);
            }
        }
        //print_r($data);die;
        return $this->ajaxReturn($this->successCode, 'SUCCESS',$data );

    }


    //审核API
    //审核API
    public function getUpApistatus(){
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($uid,2);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $userid  = $this->request->post('userid', 0, 'intval');
        $status  = $this->request->post('status', 1, 'intval');
        if(empty($userid)){
            return $this->ajaxReturn(201, '会员信息错误');
        }
        $validtime  = $this->request->post('validtime');
        if(!empty($validtime)){
            $agentinfo = CavenService::checkAgent($uid,3);
            if($agentinfo==203){
                return $this->ajaxReturn(201, '权限不够');
            }
        }

        $channel=$agentinfo['channel'];
        $wheremember = [];
        $wheremember['channel'] = $channel;
        $wheremember['member_id'] = $userid;
       $memberinfoa= $memberinfo  =Member::getWhereInfo($wheremember,"*");
        if(empty($memberinfo)){
            return $this->ajaxReturn(201, '会员信息错误');
        }
        $status = $status==1?'1':0;
        $wheremember = [];
        $wheremember['userid'] = $userid;
        //$wheremember['isvalid'] = 0;
        $memberinfo  =Apiuser::getWhereInfo($wheremember,"*");
        if(empty($memberinfo)){
            return $this->ajaxReturn(201, '会员信息错误');
        }
        if($memberinfo['isvalid']==1&&$status==1){
            return $this->ajaxReturn($this->successCode, 'SUCCESS' );
        }
        $where=[];
        $where['member_id'] = $uid;
        $agentinfo = Agent::Where($where)->field('*')->find();
        $updata['isvalid']=$status;
        if($memberinfo['isvalid']==0 && $memberinfo['validtime']<=$memberinfo['createtime']){
            $validtime = date("Y-m-d H:i:s",strtotime("+".$agentinfo['freeday']." day"));
        }else{

            if(empty($validtime)){
                $validtime = date("Y-m-d H:i:s",strtotime("+".$agentinfo['freeday']." day"));
            }
        }

        if($status==1){
            //检测是否有欠款
            $sql = "SELECT COUNT(id) as total FROM `cd_bill` WHERE paystatus=1 AND type=2 AND memberid=".$userid;
            $result = Db::query($sql);
            if($result[0]['total']>=3){
                $msg = "此会员已连续超过3次没有结算账单，系统自动停止跟单功能，如要重新开启动，请支付完账单，系统自动开启跟单功能！";
                return $this->ajaxReturn($this->errorCode,$msg);
            }
        }
        if($memberinfo['createtime']==0){
            $updata['createtime'] = date('Y-m-d H:i:s',$memberinfoa['regtime']);
        }
        $updata['id']=$memberinfo['id'];
        $updata['validtime']=$validtime;
        $updata['updatetime'] =date('Y-m-d H:i:s');
        $godate = date('Y-m-d H:i:s',strtotime($updata['validtime']));
        $res = ApiuserService::update($updata);
        $updata = [];
        $updata['validTime']=$godate;
        $updata['isValid']=$status;

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 2;
        $opdata['opaction']='AgentApi/getUpApistatus:auditUserKey';
        $opdata['opdes']='审核用户:'.$userid.'API信息';
        CavenService::Oplog($opdata);

        $result =\app\api\service\BinanceService::auditUser($updata,$userid);
        if($result['status']==200){
            return $this->ajaxReturn($this->successCode, 'SUCCESS');
        }else{
            return $this->ajaxReturn($this->errorCode,'处理失败请联系管理员！');
        }

    }

    //修改时长
    public function getUpApitime(){
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($uid,3);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $userid  = $this->request->post('userid', 0, 'intval');
        if(empty($userid)){
            return $this->ajaxReturn(201, '会员信息错误');
        }
        $validtime  = $this->request->post('validtime');

        $channel=$agentinfo['channel'];
        $wheremember = [];
        $wheremember['channel'] = $channel;
        $wheremember['member_id'] = $userid;
        $memberinfo  =Member::getWhereInfo($wheremember,"*");
        if(empty($memberinfo)){
            return $this->ajaxReturn(201, '会员信息错误');
        }

        $wheremember = [];
        $wheremember['userid'] = $userid;
        //$wheremember['isvalid'] = 0;
        $memberinfo  =Apiuser::getWhereInfo($wheremember,"*");
        if(empty($memberinfo)){
            return $this->ajaxReturn(201, '会员信息错误');
        }
        if($memberinfo['isvalid']==0){
            return $this->ajaxReturn(201, 'api-status-error');
        }

        if(empty($validtime)){
            if(!empty($memberinfo['validtime'])&&$memberinfo['validtime']>time()){
                //return $this->ajaxReturn($this->successCode, 'SUCCESS' );
                $validtime = $memberinfo['validtime'];
                $no = date('Y-m-d',$validtime);
                $validtime = date($no,strtotime("+1 month"));
            }else{
                $validtime = date("Y-m-d",strtotime("+1 month"));
            }

        }

        $updata['id']=$memberinfo['id'];
        $updata['validtime']=$validtime;
        $updata['updatetime'] = date('Y-m-d H:i:s');
        $godate = date('Y-m-d H:i:s',strtotime($updata['validtime']));
        $res = ApiuserService::update($updata);
        $updata = [];
        $updata['validTime']=$godate;
        $updata['isValid']=1;
        $result =\app\api\service\BinanceService::auditUser($updata,$userid);

        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 2;
        $opdata['opaction']='AgentApi/getUpApistatus:auditUserKey';
        $opdata['opdes']='代理修改用户:'.$userid.'API信息';
        CavenService::Oplog($opdata);

        if($result['status']==200){
            return $this->ajaxReturn($this->successCode, 'SUCCESS');
        }else{
            return $this->ajaxReturn($this->errorCode,'处理失败请联系管理员！');
        }
        return $this->ajaxReturn($this->successCode, 'SUCCESS');

    }



    //审核API
    public function getUserOrderlist(){
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($uid);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $userid  = $this->request->post('userid', 0, 'intval');
        if(empty($userid)){
            return $this->ajaxReturn(201, '会员信息错误');
        }
        $channel=$agentinfo['channel'];
        $wheremember = [];
        $wheremember['channel'] = $channel;
        $wheremember['member_id'] = $userid;
        $memberinfo  =Member::getWhereInfo($wheremember,"*");
        if(empty($memberinfo)){
            return $this->ajaxReturn(201, '会员信息错误');
        }

        $result =  BinanceService::parseFromApi($userid,'getUserOrderlist');
        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 2;
        $opdata['opaction']='AgentApi:getUserOrderlist';
        $opdata['opdes']='查看用户:'.$userid.'订单交易信息';
        CavenService::Oplog($opdata);
        return $this->reDecodejson($result);

    }

    //审核API
    public function pcAct(){
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($uid,4);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $leaderId  = $this->request->post('leaderId');

        $symbol  = $this->request->post('symbol');
        //$leadAmount  = $this->request->post('leadAmount');
        //$leadAmount = (float)$leadAmount;
        if(empty($leaderId)){
            return $this->ajaxReturn(201, 'leaderId不能为空');
        }
        if(empty($symbol)){
            return $this->ajaxReturn(201, '平仓币对不能为空');
        }
//        if(empty($leadAmount)){
//            return $this->ajaxReturn(201, '平仓数量不能为空');
//        }
        $where=[];
        $where['member_id'] = $uid;
        $agentinfo = Agent::Where($where)->field('*')->find();
        $gid = $agentinfo['groupid'];
        $groupinfo = Tradegroup::find($gid);
        if(empty($groupinfo)||empty($groupinfo['leaderlist'])){
            $re = ['status' => 201, 'msg' => '会员信息错误！'];
            return $this->reDecodejson($re);
        }

        $leaderlist = $groupinfo['leaderlist'];
        $lederidlist = explode(',', $leaderlist);
        $sql = "SELECT acceptsource FROM `cd_leader` WHERE id in (".implode(",",$lederidlist).")";
        $ledlist = Db::query($sql);
        if(empty($ledlist)){
            $re = ['status' => 201, 'msg' => '信息错误！'];
            return $this->reDecodejson($re);
        }
        $lee = [];
        foreach($ledlist as $key=>$v){
            $lee[]=$v['acceptsource'];
        }
        if(!in_array($leaderId,$lee)){
            $re = ['status' => 201, 'msg' => '信息错误！'];
            return $this->reDecodejson($re);
        }
        $channel=$agentinfo['channel'];
        $inarr=[];
        $symbol = strtoupper($symbol);
        $inarr['symbol']=$symbol;
        $inarr['leaderId']=$leaderId;
        //$inarr['leadAmount']=$leadAmount;
        $inarr['channelName']=$channel;
        $inarr['type']='SPOT';
        $inarr['key']='lxfyangcai';
        $re = ['status' => 200, 'msg' => '处理成功！'];
        $result =  BinanceService::parseFromApi($uid,'resetPosByChannelName',$inarr);
        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 2;
        $opdata['opaction']='AgentApi:pcAct';
        $opdata['opdes']='清空'.$channel.','.$symbol.'币对';
        CavenService::Oplog($opdata);
        return $this->reDecodejson($result);

    }


    //审核API
    public function pcBuyuser(){
        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($uid,3);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $userid  = $this->request->post('userid', 0, 'intval');
        if(empty($userid)){
            return $this->ajaxReturn(201, '会员信息错误');
        }

        $channel=$agentinfo['channel'];
        $wheremember = [];
        $wheremember['channel'] = $channel;
        $wheremember['member_id'] = $userid;
        $memberinfo  =Member::getWhereInfo($wheremember,"*");
        if(empty($memberinfo)){
            return $this->ajaxReturn(201, '会员信息错误');
        }
        $leaderId  = $this->request->post('leaderId');

        $symbol  = $this->request->post('symbol');
        //$leadAmount  = $this->request->post('leadAmount');
        //$leadAmount = (float)$leadAmount;
        if(empty($leaderId)){
            return $this->ajaxReturn(201, 'leaderId不能为空');
        }
        if(empty($symbol)){
            return $this->ajaxReturn(201, '平仓币对不能为空');
        }
        // if(empty($leadAmount)){
        //return $this->ajaxReturn(201, '平仓数量不能为空');
        //}

        $inarr=[];
        $symbol = strtoupper($symbol);
        $inarr['symbol']=$symbol;
        $inarr['leaderId']=$leaderId;
        //$inarr['leadAmount']=$leadAmount;
        $inarr['userId']=$userid;
        $inarr['type']='SPOT';
        $inarr['key']='lxfyangcai';
        $result =  BinanceService::parseFromApi($userid,'resetPosByUserId',$inarr);
        $opdata = [];
        $opdata['userid'] = $uid;
        $opdata['optype'] = 2;
        $opdata['opaction']='AgentApi:pcBuyuser';
        $opdata['opdes']='清空用户'.$userid.','.$symbol.'币对';
        CavenService::Oplog($opdata);
        return $this->reDecodejson($result);

    }


    //转代理收益到绑定会员的收益账号
    function doQdToAccount(){
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

        $where = array();
        $where['member_id'] = $member_id;
        $ageninfo = Agent::Where($where)->field('*')->find();
        if(empty($ageninfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'渠道会员信息错误']);
        }

        if($ageninfo['money']<$money){
            return $this->reDecodejson(['status'=>'203','msg'=>'您的渠道余额不足']);
        }
        $trade_no = date('YmdHis').mt_rand(1000,9999);
        Db::startTrans();
        try {
            //进行支付操作
            $where = array();
            $where['member_id'] = $member_id;
            $dodec = Agent::setDes($where, 'money', $money);
            if (!$dodec) {
                return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '您的渠道余额不足']);
            }
            $des = '渠道余额划转到收益账号:'.$member_id;
            $dataag = [];
            $dataag['ordernumber'] = $trade_no;
            $dataag['blance'] = $ageninfo['money']-$money;
            $dataag['dateline'] = time();
            $dataag['money'] = $money;
            $dataag['type'] = 2;
            $dataag['tradetype'] = 3;
            $dataag['des'] = $des;
            $dataag['user_id'] = $ageninfo['user_id'];
            MeadmoneylogService::add($dataag);
            $where = array();
            $where['member_id'] = $member_id;
            Member::setInc($where, 'yjmoney', $money);
            addPayLog($memberInfo, ($memberInfo['yjmoney'] + $money), $money, 1, 7, $des, $trade_no, 2);
            Db::commit();
            if (empty($orderid)) {
                return $this->reDecodejson(['status' => '200', 'msg' => '操作成功']);
            }
        }catch (\Exception $e){
            Db::rollback();
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>$e->getMessage()]);
        }

    }

    //购买产品getProductlist
    function doQdTouser(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($member_id,5);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $money   = $this->request->post('money');
        $userid  = $this->request->post('userid', 0, 'intval');
        if(empty($userid)){
            return $this->ajaxReturn(201, '会员信息错误');
        }
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

        $where = array();
        $where['member_id'] = $member_id;
        $ageninfo = Agent::Where($where)->field('*')->find();
        if(empty($ageninfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'渠道会员信息错误']);
        }
        $channel=$ageninfo['channel'];
        $wheremember = [];
        $wheremember['channel'] = $channel;
        $wheremember['member_id'] = $userid;
        $memberinfo  =Member::getWhereInfo($wheremember,"*");
        if(empty($memberinfo)){
            return $this->ajaxReturn(201, '找不到划转会员信息');
        }

        if($ageninfo['money']<$money){
            return $this->reDecodejson(['status'=>'203','msg'=>'您的渠道余额不足']);
        }
        $trade_no = date('YmdHis').mt_rand(1000,9999);
        Db::startTrans();
        try {
            //进行支付操作
            //进行支付操作
            $where = array();
            $where['member_id'] = $member_id;
            $dodec = Agent::setDes($where, 'money', $money);
            if (!$dodec) {
                return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '您的渠道余额不足']);
            }
            $des = '渠道余额划转到下级会员账号:'.$userid;
            $dataag = [];
            $dataag['ordernumber'] = $trade_no;
            $dataag['blance'] = $ageninfo['money']-$money;
            $dataag['dateline'] = time();
            $dataag['money'] = $money;
            $dataag['type'] = 2;
            $dataag['tradetype'] = 4;
            $dataag['des'] = $des;
            $dataag['user_id'] = $ageninfo['user_id'];
            MeadmoneylogService::add($dataag);
            //生成一条充值分成订单
            $dodata['payway']=9;//管理员入账
            $otherinfo = \app\api\service\PayService::getCzotherinfo($money,$member_id);
            //member_id,money,des,dateline
            $dodata['member_id']=$userid;
            $dodata['money']=$money;
            if(!empty($otherinfo)&&is_array($otherinfo)){
                $dodata = array_merge($dodata,$otherinfo);
            }
            $dodata['dostatus'] = 1;
            $dodata['inuserid'] = $member_id;
            $dodata['status'] = 2;
            $dodata['des'] = '渠道账号划转充值';
            $dodata['ordernumber']=date('YmdHis').mt_rand(1000,9999);
            $dodata['tradeno']=date('12YmdHis').mt_rand(1000,9999);
            $chargeorder_id = ChargeorderService::add($dodata);
            \app\api\service\CavenService::doChargeorder($chargeorder_id);
            Db::commit();

            if (empty($orderid)) {
                return $this->reDecodejson(['status' => '200', 'msg' => '操作成功']);
            }
        }catch (\Exception $e){
            Db::rollback();
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>$e->getMessage()]);
        }

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
        $where = array();
        $where['member_id'] = $member_id;
        $ageninfo = Agent::Where($where)->field('money,paygetpoint,allmoney,channel')->find();
        if(empty($ageninfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'渠道会员信息错误']);
        }
        $channel = $ageninfo['channel'];
        $agentinfo = CavenService::checkAgent($member_id,5);
        $hzstatus = 0;
        if($agentinfo==203){

        }else{
            $hzstatus = 1;
        }

        $agentinfo = CavenService::checkAgent($member_id,6);
        $xjqdstatus = 0;
        if($agentinfo==203){

        }else{
            $xjqdstatus = 1;
        }

        $ageninfo['hzstatus']=$hzstatus;
        $ageninfo['xjqdstatus']=$xjqdstatus;
        $redata['info']=$ageninfo;


        /*/查询昨日收益
        $yes = date("Y-m-d",strtotime("-1 day"));
        $start = strtotime($yes);
        $endt = $start+3600*24;
        $sql = "SELECT channelmoney as money,money as czmoney  FROM `cd_chargeorder` WHERE status=2 AND `dateline`>='".$start."' AND `dateline`<'".$endt."' AND channel='".$channel."'";
        $resu = Db::query($sql);
        $yesmoney = isset($resu[0]['money'])?$resu[0]['money']:0;
        $yesczmoney = isset($resu[0]['czmoney'])?$resu[0]['czmoney']:0;
        $redata['syinfo']['yesmoney']=$yesmoney;
        $redata['syinfo']['yesczmoney']=$yesczmoney;

        //查询总充值收益

        $sql = "SELECT sum(channelmoney) as money,sum(money) as zczmoney FROM `cd_chargeorder` WHERE status=2 AND channel='".$channel."'";
        $resu = Db::query($sql);
        $yesmoney = isset($resu[0]['money'])?$resu[0]['money']:0;
        $zczyesmoney = isset($resu[0]['zczmoney'])?$resu[0]['zczmoney']:0;
        $redata['syinfo']['totalmoney']=$yesmoney;
        $redata['syinfo']['totalczmoney']=$zczyesmoney;
        */
        $redata['downinfo'] = $this->getDownmember($channel);
        $redata['syinfo'] = $this->getcztj($channel,0);
        //查出会员组
        return $this->ajaxReturn($this->successCode,'返回成功',$redata);
    }

    public  function getDownmember($channel){
        $sql = "SELECT count(member_id) as total  FROM `cd_member` WHERE  channel='".$channel."'";
        $resu = Db::query($sql);
        $data['totalmember']=isset($resu[0]['total'])?$resu[0]['total']:0;
        return $data;
    }

    public  function getcztj($channel,$type=0){
        //查询昨日收益
        $yes = date("Y-m-d",strtotime("-1 day"));
        $start = strtotime($yes);
        $endt = $start+3600*24;
        if($type==0){
            $sql = "SELECT channelmoney as money,money as czmoney  FROM `cd_chargeorder` WHERE status=2 AND `dateline`>='".$start."' AND `dateline`<'".$endt."' AND channel='".$channel."'";
            $sql1 = "SELECT sum(channelmoney) as money,sum(money) as zczmoney FROM `cd_chargeorder` WHERE status=2 AND channel='".$channel."'";
        }else if($type==1){
            $sql = "SELECT twochannelmoney as money,money as czmoney  FROM `cd_chargeorder` WHERE status=2 AND `dateline`>='".$start."' AND `dateline`<'".$endt."' AND channel='".$channel."'";
            $sql1 = "SELECT sum(twochannelmoney) as money,sum(money) as zczmoney FROM `cd_chargeorder` WHERE status=2 AND channel='".$channel."'";
        }else if($type==2){
            $sql = "SELECT threechannelmoney as money,money as czmoney  FROM `cd_chargeorder` WHERE status=2 AND `dateline`>='".$start."' AND `dateline`<'".$endt."' AND channel='".$channel."'";
            $sql1 = "SELECT sum(threechannelmoney) as money,sum(money) as zczmoney FROM `cd_chargeorder` WHERE status=2 AND channel='".$channel."'";
        }
        $resu = Db::query($sql);
        $yesmoney = isset($resu[0]['money'])?$resu[0]['money']:0;
        $yesczmoney = isset($resu[0]['czmoney'])?$resu[0]['czmoney']:0;
        $redata['yesmoney']=$yesmoney;
        $redata['yesczmoney']=$yesczmoney;

        //查询总充值收益


        $resu = Db::query($sql1);
        $yesmoney = isset($resu[0]['money'])?$resu[0]['money']:0;
        $zczyesmoney = isset($resu[0]['zczmoney'])?$resu[0]['zczmoney']:0;
        $redata['totalmoney']=$yesmoney;
        $redata['totalczmoney']=$zczyesmoney;
        return $redata;
    }
    //取得我的渠道会员列表
    public function GetMyTgChannel(){

        $uid = $this->request->uid;
        if(empty($uid)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($uid,6);
         $channel = $ageninfo['channel'];
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $where = array();
        $where['member_id'] = $uid;
        $ageninfo = Agent::Where($where)->field('money,paygetpoint,allmoney,channel')->find();
        if(empty($ageninfo)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'渠道会员信息错误']);
        }

       
        $type  = $this->request->post('type', 1, 'intval');
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = array();
        if(!in_array($type,array(1,2))){
            $type=1;
        }
        if($type==1){
            $where[] = ['twochannel','=',$channel];
        }
        if($type==2){
            $where[] = ['threechannel','=',$channel];
        }
        if($type==1){
            $field = 'channel,paygetpoint,nick,member_id,twopaygetpoint as mygetpoint';
        }else{
            $field = 'channel,paygetpoint,nick,member_id,threepaygetpoint as mygetpoint';
        }

        $orderby = 'id desc';

        $res = db('agent')->where($where)
            ->field($field)
            ->order($orderby)
            ->paginate(['list_rows'=>$limit,'page'=>$page])
            ->toArray();
        if(!empty($res)){
            if(!empty($res['data'])){
                foreach($res['data'] as $key=>$v){
                    $res['data'][$key]['syinfo'] = $this->getcztj($v['channel'],$type);
                    $res['data'][$key]['downinfo'] = $this->getDownmember($v['channel'],$type);
                }
            }
        }
        return $this->ajaxReturn($this->successCode, 'SUCCESS', htmlOutList($res));
    }
    
    
     //取得用户充值定单位
    public function getChargelist(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($member_id,7);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $channel=$agentinfo['channel'];
//充值中|1|success,充值成功|2|warning,充值失败|3|danger
        $estatusarr=array(
            '1'=>'充值中',
            '2'=>'成功',
            '3'=>'失败'
        );
       
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $mobile = $this->request->param('mobile', '', 'serach_in');
        $uid = $this->request->param('uid', '', 'serach_in');
        $uid = intval($uid);
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = [];
        $where['b.channel'] = $channel;
        if(!empty($mobile)){
            $where['b.mobile'] = $mobile;
        }
        if(!empty($uid)){
            $where['b.member_id'] = $uid;
        }

        $field = 'b.mobile,b.member_id as uid,a.ordernumber,a.money,a.status,a.payway,a.des,a.dateline,a.updateline,a.chargeorder_id as id';
        $orderby = 'a.chargeorder_id';
         $where = formatWhere($where);
        try{
            $res = db('chargeorder')->field($field)->alias('a')->join('member b','a.member_id=b.member_id','left')->where($where)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page]);
           
        }catch(\Exception $e){
            abort(500,$e->getMessage());
        }
        $data = [];
        $rows = $res->items();
        if(!empty($rows)){
            foreach($rows as $key=>$v){
                $data[$key]['mobile'] =$v['mobile'];
                $data[$key]['id'] = $v['id'];
                $data[$key]['uid'] = $v['uid'];
                $data[$key]['money'] = $v['money'];
                $data[$key]['status'] = $v['status'];
                $data[$key]['statusen'] = $estatusarr[$v['status']];
                $data[$key]['ordernumber'] = $v['ordernumber'];
                $data[$key]['dateline'] = date('Y-m-d',$v['dateline']);
                $data[$key]['des'] =$v['des'];
                
            }
        }
        //print_r($data);die;
        return $this->ajaxReturn($this->successCode, 'SUCCESS',$data );

    }
    
    
    //提现管理
    public function getWithdrawallist(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($member_id,7);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
//充值中|1|success,充值成功|2|warning,充值失败|3|danger
        $estatusarr=array(
            '1'=>'处理中',
            '2'=>'成功',
            '3'=>'失败'
        );
        //币安转账|1|success,OKX转账|2|warning,钱包充提|3|success
        $estxtypearr=array(
            '1'=>'币安转账',
            '2'=>'OKX转账',
            '3'=>'钱包充提'
        );
        $channel=$agentinfo['channel'];
        
        $limit  = $this->request->post('per_page', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $mobile = $this->request->param('mobile', '', 'serach_in');
        $uid = $this->request->param('uid', '', 'serach_in');
        $uid = intval($uid);
        $page   = intval($page);
        $page = $page<=0?1:$page;
        $where = [];
        $where['b.channel'] = $channel;
        if(!empty($mobile)){
            $where['b.mobile'] = $mobile;
        }
        if(!empty($uid)){
            $where['b.member_id'] = $uid;
        }

        $field = 'b.mobile,b.member_id as uid,a.trade_no as ordernumber,a.momey,a.charge,a.paytype,a.transfer_money,a.status,a.account,a.des,a.dateline,a.updateline,a.withdrawal_id as id';
        $orderby = 'a.withdrawal_id';


        $where = formatWhere($where);
        try{
            $res = db('withdrawal')->field($field)->alias('a')->join('member b','a.member_id=b.member_id','left')->where($where)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page]);
        }catch(\Exception $e){
            abort(500,$e->getMessage());
        }
        $data = [];
        $rows = $res->items();
        if(!empty($rows)){
            foreach($rows as $key=>$v){
                $data[$key]['mobile'] =$v['mobile'];
                $data[$key]['id'] = $v['id'];
                $data[$key]['uid'] = $v['uid'];
                $data[$key]['money'] = $v['momey'];
                $data[$key]['status'] = $v['status'];
                $data[$key]['paytypeen'] = $estxtypearr[$v['paytype']];
                $data[$key]['transfer_money'] = $v['transfer_money'];
                $data[$key]['account'] = $v['account'];
                $data[$key]['statusen'] = $estatusarr[$v['status']];
                $data[$key]['ordernumber'] = $v['ordernumber'];
                $data[$key]['dateline'] = date('Y-m-d',$v['dateline']);
                $data[$key]['des'] =$v['des'];
                
            }
        }
        //print_r($data);die;
        return $this->ajaxReturn($this->successCode, 'SUCCESS',$data );

    }
    
    
    //转代理收益到绑定会员的收益账号
    function doChargelist(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($member_id,7);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $id   = $this->request->post('id');
        $status   = $this->request->post('status');
        $id = intval($id);
        if(empty($id)||!in_array($status,array(2,3))){
            return $this->reDecodejson(['status'=>201,'msg'=>'您提交的信息不对']);
        }
        $channel=$agentinfo['channel'];
        $sql = "SELECT b.* FROM `cd_chargeorder` a LEFT JOIN `cd_member` b  on a.member_id=b.member_id WHERE a.chargeorder_id=".$id." AND b.channel='".$channel."' AND a.status=1";
        $result = Db::query($sql);
        if(empty($result)){
             return $this->ajaxReturn(201, '找不到记录或此订单已处理');
        }
        $charinfo = $result[0];
        Db::startTrans();
        try {
            $updata = [];
            $updata['status']=$status;
            $updata['chargeorder_id']=$id;
            $updata['updateline']=time();
            $res = ChargeorderService::update($updata);
            if($status==2){
                \app\api\service\CavenService::doChargeorder($id);
            }
            
            Db::commit();
            return $this->reDecodejson(['status' => '200', 'msg' => '操作成功']);
        }catch (\Exception $e){
            Db::rollback();
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>$e->getMessage()]);
        }

    }
    
    
    //添加充值记录
    function doAdChargelist(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($member_id,7);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $uid   = $this->request->post('uid', 0, 'intval');
        
        $uid = intval($uid);
        $des  = $this->request->post('des');
        if(empty($uid)){
            return $this->ajaxReturn(201, '会员信息错误');
        }
        $money   = $this->request->post('money');
        $money = (float)$money;
        if(empty($money)){
            return $this->reDecodejson(['status'=>201,'msg'=>'您提交的金额不对']);
        }
        if($money<0){
            return $this->reDecodejson(['status'=>201,'msg'=>'您提交的金额不对']);
        }
        
        
        $channel=$agentinfo['channel'];
        $sql = "SELECT * FROM `cd_member` WHERE member_id=".$uid." AND channel='".$channel."' limit 1";
        $result = Db::query($sql);
        if(empty($result)){
             return $this->ajaxReturn(201, '会员信息错误');
        }
        $memberinfo = $result[0];
        $trade_no = date('YmdHis').mt_rand(1000,9999);
        Db::startTrans();
        try {
            $data = [];
            $data['dostatus'] = 1;
            $data['status'] = 1;
            $data['member_id']=$uid;
            $data['money']=$money;
            $otherinfo = \app\api\service\PayService::getCzotherinfo($data['money'],$data['member_id']);
            if(!empty($otherinfo)&&is_array($otherinfo)){
                $data = array_merge($data,$otherinfo);
            }
            
            $data['des']=$des;
            $data['dateline']=time();
            $data['ordernumber']=date('YmdHis').mt_rand(1000,9999);
            $data['tradeno']=date('12YmdHis').mt_rand(1000,9999);
            $data['payway']=8;//渠道管理员入账
			$chargeorder_id = ChargeorderService::add($data);
            Db::commit();
            return $this->reDecodejson(['status' => '200', 'msg' => '操作成功']);
        }catch (\Exception $e){
            Db::rollback();
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>$e->getMessage()]);
        }

    }
    
    
    //处理提现管理
    function doWithdrawallist(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($member_id,7);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $id   = $this->request->post('id');
        $status   = $this->request->post('status');
        $des   = $this->request->post('des');
        $money   = $this->request->post('transfer_money');
        $money = (float)$money;
        if(empty($money)){
            return $this->reDecodejson(['status'=>201,'msg'=>'您提交的金额不对']);
        }
        $id = intval($id);
        if(empty($id)||!in_array($status,array(2,3))){
            return $this->reDecodejson(['status'=>201,'msg'=>'您提交的信息不对']);
        }
        $channel=$agentinfo['channel'];
        $sql = "SELECT a.* FROM `cd_withdrawal` a LEFT JOIN `cd_member` b  on a.member_id=b.member_id WHERE a.withdrawal_id=".$id." AND b.channel='".$channel."' AND a.status=1";
        $result = Db::query($sql);
        if(empty($result)){
             return $this->ajaxReturn(201, '找不到记录或此订单已处理');
        }
        $charinfo = $result[0];
        if($status==2){
            if($charinfo['momey']<$money){
                return $this->reDecodejson(['status'=>201,'msg'=>'转账金额不能大于提现金额']);
            }
            
        }else{
            $money = $charinfo['transfer_money'];
        }
        Db::startTrans();
        try {
            $updata = [];
            $updata['status']=$status;
            $updata['withdrawal_id']=$id;
            $updata['updateline']=time();
            $updata['transfer_money']=$money;
            $res = Withdrawal::update($updata);
            \app\api\service\CavenService::doWithdrawalorder($data['withdrawal_id']);
            Db::commit();
            return $this->reDecodejson(['status' => '200', 'msg' => '操作成功']);
        }catch (\Exception $e){
            Db::rollback();
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>$e->getMessage()]);
        }

    }
    
    
    //添加充值记录
    function doUpremark(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $agentinfo = CavenService::checkAgent($member_id,3);
        if($agentinfo==203){
            return $this->ajaxReturn(201, '权限不够');
        }
        $userid   = $this->request->post('userid', 0, 'intval');
        $remark   = $this->request->post('remark');
        $uid = intval($uid);
        $des  = $this->request->post('des');
        if(empty($userid)){
            return $this->ajaxReturn(201, '会员信息错误');
        }
        if(empty($remark)){
            return $this->ajaxReturn(201, '备注信息错误');
        }
        $channel=$agentinfo['channel'];
        $sql = "UPDATE `cd_member` SET remark='".$remark."' WHERE member_id=".$userid." AND channel='".$channel."'";
        $result = Db::query($sql);
        return $this->reDecodejson(['status' => '200', 'msg' => '操作成功']);

    }
}
