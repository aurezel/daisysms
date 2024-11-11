<?php 
/*
 module:		资金变动记录
 create_time:	2023-05-03 00:07:01
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\MoneylogService;
use app\admin\model\Moneylog as MoneylogModel;
use think\facade\Db;

class Moneylog extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];
			$where['mobile'] = $this->request->param('mobile', '', 'serach_in');

			$money_start = $this->request->param('money_start', '', 'serach_in');
			$money_end = $this->request->param('money_end', '', 'serach_in');

			$where['money'] = ['between',[$money_start,$money_end]];
			$where['type'] = $this->request->param('type', '', 'serach_in');
			$where['trantype'] = $this->request->param('trantype', '', 'serach_in');
			$where['txstatus'] = $this->request->param('txstatus', '', 'serach_in');
			$where['trade_no'] = $this->request->param('trade_no', '', 'serach_in');
			$where['blancetype'] = $this->request->param('blancetype', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'moneylog_id,member_id,mobile,des,balance,money,type,trantype,dateline,txstatus,trade_no,blancetype';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'moneylog_id desc';

			$res = MoneylogService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*添加*/
	function add(){
		if (!$this->request->isPost()){
			return view('add');
		}else{
			$postField = 'balance,money,des,member_id,mobile,type,trantype,ip,dateline,txstatus,trade_no,username';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = MoneylogService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$moneylog_id = $this->request->get('moneylog_id','','serach_in');
			if(!$moneylog_id) $this->error('参数错误');
			$this->view->assign('info',checkData(MoneylogModel::find($moneylog_id)));
			return view('update');
		}else{
			$postField = 'moneylog_id,balance,money,des,member_id,mobile,type,trantype,ip,dateline,txstatus,trade_no,username';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = MoneylogService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('moneylog_id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			MoneylogModel::destroy(['moneylog_id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$moneylog_id = $this->request->get('moneylog_id','','serach_in');
		if(!$moneylog_id) $this->error('参数错误');
		$this->view->assign('info',MoneylogModel::find($moneylog_id));
		return view('view');
	}



}

