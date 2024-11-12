<?php 
/*
 module:		验证码
 create_time:	2023-07-29 11:55:35
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\MemberYzmService;
use app\admin\model\MemberYzm as MemberYzmModel;
use think\facade\Db;

class MemberYzm extends Admin {


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
			$where['verify'] = $this->request->param('verify', '', 'serach_in');
			$where['verify_id'] = $this->request->param('verify_id', '', 'serach_in');
			$where['type'] = $this->request->param('type', '', 'serach_in');

			$dateline_start = $this->request->param('dateline_start', '', 'serach_in');
			$dateline_end = $this->request->param('dateline_end', '', 'serach_in');

			$where['dateline'] = ['between',[strtotime($dateline_start),strtotime($dateline_end)]];

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'id,mobile,verify,verify_id,type,dateline';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';

			$res = MemberYzmService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*添加*/
	function add(){
		if (!$this->request->isPost()){
			return view('add');
		}else{
			$postField = 'mobile,verify,verify_id,type';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = MemberYzmService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$id = $this->request->get('id','','serach_in');
			if(!$id) $this->error('参数错误');
			$this->view->assign('info',checkData(MemberYzmModel::find($id)));
			return view('update');
		}else{
			$postField = 'id,mobile,verify,verify_id,type';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = MemberYzmService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			MemberYzmModel::destroy(['id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$id = $this->request->get('id','','serach_in');
		if(!$id) $this->error('参数错误');
		$this->view->assign('info',MemberYzmModel::find($id));
		return view('view');
	}



}

