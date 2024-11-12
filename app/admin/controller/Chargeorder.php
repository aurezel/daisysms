<?php 
/*
 module:		充值订单
 create_time:	2023-07-27 23:47:31
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\ChargeorderService;
use app\admin\model\Chargeorder as ChargeorderModel;
use think\facade\Db;

class Chargeorder extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];
			$where['member_id'] = $this->request->param('member_id', '', 'serach_in');
			$where['mobile'] = $this->request->param('mobile', '', 'serach_in');
			$where['ordernumber'] = $this->request->param('ordernumber', '', 'serach_in');
			$where['status'] = $this->request->param('status', '', 'serach_in');
			$where['payway'] = $this->request->param('payway', '', 'serach_in');
			$where['des'] = $this->request->param('des', '', 'serach_in');

			$dateline_start = $this->request->param('dateline_start', '', 'serach_in');
			$dateline_end = $this->request->param('dateline_end', '', 'serach_in');

			$where['dateline'] = ['between',[strtotime($dateline_start),strtotime($dateline_end)]];
			$where['dostatus'] = $this->request->param('dostatus', '', 'serach_in');
			$where['tradeno'] = $this->request->param('tradeno', '', 'serach_in');
			$where['parentid'] = $this->request->param('parentid', '', 'serach_in');
			$where['twoid'] = $this->request->param('twoid', '', 'serach_in');
			$where['threeid'] = $this->request->param('threeid', '', 'serach_in');
			$where['channel'] = $this->request->param('channel', '', 'serach_in');
			$where['inuserid'] = $this->request->param('inuserid', '', 'serach_in');
			$where['twochannel'] = $this->request->param('twochannel', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'chargeorder_id,member_id,mobile,money,ordernumber,status,payway,des,dateline,updateline,dostatus,tradeno,parentid,parentmoney,twoid,twomoney,threeid,threemoney,channelmoney,channel,systemmoney,inuserid,twochannelmoney,twochannel,threechannelmoney,threechannel';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'chargeorder_id desc';

			$res = ChargeorderService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$chargeorder_id = $this->request->get('chargeorder_id','','serach_in');
			if(!$chargeorder_id) $this->error('参数错误');
			$this->view->assign('info',checkData(ChargeorderModel::find($chargeorder_id)));
			return view('update');
		}else{
			$postField = 'chargeorder_id,member_id,mobile,money,ordernumber,status,payway,des,dateline,updateline,dostatus,tradeno';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = ChargeorderService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*查看详情*/
	function view(){
		$chargeorder_id = $this->request->get('chargeorder_id','','serach_in');
		if(!$chargeorder_id) $this->error('参数错误');
		$this->view->assign('info',ChargeorderModel::find($chargeorder_id));
		return view('view');
	}


/*start*/
	/*添加*/
	function add(){
		if (!$this->request->isPost()){
			return view('add');
		}else{
			$postField = 'member_id,money,des,dateline';
			$data = $this->request->only(explode(',',$postField),'post',null);
            $data['payway']=7;//管理员入账
            $otherinfo = \app\api\service\PayService::getCzotherinfo($data['money'],$data['member_id']);
            if(!empty($otherinfo)&&is_array($otherinfo)){
                $data = array_merge($data,$otherinfo);
            }
            $data['dostatus'] = 1;
            $data['status'] = 2;
            $data['ordernumber']=date('YmdHis').mt_rand(1000,9999);
            $data['tradeno']=date('12YmdHis').mt_rand(1000,9999);
			$chargeorder_id = ChargeorderService::add($data);
            \app\api\service\CavenService::doChargeorder($chargeorder_id);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}
/*end*/



}

