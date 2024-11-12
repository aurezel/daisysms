<?php 
/*
 module:		充提小计
 create_time:	2022-03-25 12:15:59
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\FinancialService;
use app\admin\model\Financial as FinancialModel;
use think\facade\Db;

class Financial extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];

			$money_start = $this->request->param('money_start', '', 'serach_in');
			$money_end = $this->request->param('money_end', '', 'serach_in');

			$where['money'] = ['between',[$money_start,$money_end]];
			$where['date'] = $this->request->param('date', '', 'serach_in');

			$dateline_start = $this->request->param('dateline_start', '', 'serach_in');
			$dateline_end = $this->request->param('dateline_end', '', 'serach_in');

			$where['dateline'] = ['between',[strtotime($dateline_start),strtotime($dateline_end)]];
			$where['type'] = $this->request->param('type', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'id,money,charge,changemoney,date,dateline,type';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';

			$res = FinancialService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*添加*/
	function add(){
		if (!$this->request->isPost()){
			return view('add');
		}else{
			$postField = 'money,charge,changemoney,date,dateline,type';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = FinancialService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$id = $this->request->get('id','','serach_in');
			if(!$id) $this->error('参数错误');
			$this->view->assign('info',checkData(FinancialModel::find($id)));
			return view('update');
		}else{
			$postField = 'id,money,charge,changemoney,date,dateline,type';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = FinancialService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			FinancialModel::destroy(['id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$id = $this->request->get('id','','serach_in');
		if(!$id) $this->error('参数错误');
		$this->view->assign('info',FinancialModel::find($id));
		return view('view');
	}



}

