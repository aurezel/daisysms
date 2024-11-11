<?php 
/*
 module:		充提方式
 create_time:	2022-04-24 17:39:50
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\PaywayService;
use app\admin\model\Payway as PaywayModel;
use think\facade\Db;

class Payway extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];

			$charge_start = $this->request->param('charge_start', '', 'serach_in');
			$charge_end = $this->request->param('charge_end', '', 'serach_in');

			$where['charge'] = ['between',[$charge_start,$charge_end]];

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'payway_id,name,img,type,paytype,charge,chargetype,status,sortid,account,thumb,smkey';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'payway_id desc';

			$res = PaywayService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*修改排序开关按钮操作*/
	function updateExt(){
		$postField = 'payway_id,status,sortid';
		$data = $this->request->only(explode(',',$postField),'post',null);
		if(!$data['payway_id']) $this->error('参数错误');
		try{
			PaywayModel::update($data);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*添加*/
	function add(){
		if (!$this->request->isPost()){
			return view('add');
		}else{
			$postField = 'name,des,img,type,paytype,charge,chargetype,status,sortid,account,thumb,smkey';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = PaywayService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$payway_id = $this->request->get('payway_id','','serach_in');
			if(!$payway_id) $this->error('参数错误');
			$this->view->assign('info',checkData(PaywayModel::find($payway_id)));
			return view('update');
		}else{
			$postField = 'payway_id,name,des,img,type,paytype,charge,chargetype,status,sortid,account,thumb,smkey';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = PaywayService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('payway_id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			PaywayModel::destroy(['payway_id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$payway_id = $this->request->get('payway_id','','serach_in');
		if(!$payway_id) $this->error('参数错误');
		$this->view->assign('info',PaywayModel::find($payway_id));
		return view('view');
	}



}

