<?php 
/*
 module:		提现管理
 create_time:	2023-04-06 18:24:53
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\WithdrawalService;
use app\admin\model\Withdrawal as WithdrawalModel;
use think\facade\Db;

class Withdrawal extends Admin {


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
			$where['status'] = $this->request->param('status', '', 'serach_in');
			$where['account'] = $this->request->param('account', '', 'serach_in');
			$where['ip'] = $this->request->param('ip', '', 'serach_in');
			$where['des'] = $this->request->param('des', '', 'serach_in');

			$dateline_start = $this->request->param('dateline_start', '', 'serach_in');
			$dateline_end = $this->request->param('dateline_end', '', 'serach_in');

			$where['dateline'] = ['between',[strtotime($dateline_start),strtotime($dateline_end)]];
			$where['trade_no'] = $this->request->param('trade_no', '', 'serach_in');
			$where['dostatus'] = $this->request->param('dostatus', '', 'serach_in');
			$where['paytype'] = $this->request->param('paytype', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'withdrawal_id,member_id,mobile,transfer_money,status,account,ip,des,dateline,updateline,trade_no,dostatus,paytype';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'withdrawal_id desc';

			$res = WithdrawalService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*查看详情*/
	function view(){
		$withdrawal_id = $this->request->get('withdrawal_id','','serach_in');
		if(!$withdrawal_id) $this->error('参数错误');
		$this->view->assign('info',WithdrawalModel::find($withdrawal_id));
		return view('view');
	}

 /*start*/
	function update(){
		if (!$this->request->isPost()){
			$withdrawal_id = $this->request->get('withdrawal_id','','serach_in');
			if(!$withdrawal_id) $this->error('参数错误');
			$this->view->assign('info',checkData(WithdrawalModel::find($withdrawal_id)));
			return view('update');
		}else{
			$postField = 'withdrawal_id,member_id,transfer_money,status,des';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = WithdrawalService::update($data);
            \app\api\service\CavenService::doWithdrawalorder($data['withdrawal_id']);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}
    /*end*/



}

