<?php 
/*
 module:		投诉管理
 create_time:	2020-11-04 16:29:02
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\MemberTousuService;
use app\admin\model\MemberTousu as MemberTousuModel;
use think\facade\Db;

class MemberTousu extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];
			$where['id'] = $this->request->param('id', '', 'serach_in');
			$where['tousu_uid'] = $this->request->param('tousu_uid', '', 'serach_in');
			$where['type'] = $this->request->param('type', '', 'serach_in');

			$addtime_start = $this->request->param('addtime_start', '', 'serach_in');
			$addtime_end = $this->request->param('addtime_end', '', 'serach_in');

			$where['addtime'] = ['between',[strtotime($addtime_start),strtotime($addtime_end)]];
			$where['description'] = $this->request->param('description', '', 'serach_in');
			$where['tousu_type_id'] = $this->request->param('tousu_type_id', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'id,screenshots,tousu_uid,type,addtime,description,tousu_type_id';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';

			$res = MemberTousuService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*添加*/
	function add(){
		if (!$this->request->isPost()){
			return view('add');
		}else{
			$postField = 'msgs,screenshots,tousu_uid,type,addtime,description,tousu_type_id';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = MemberTousuService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$id = $this->request->get('id','','serach_in');
			if(!$id) $this->error('参数错误');
			$this->view->assign('info',checkData(MemberTousuModel::find($id)));
			return view('update');
		}else{
			$postField = 'id,msgs,screenshots,tousu_uid,type,addtime,description,tousu_type_id';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = MemberTousuService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			MemberTousuModel::destroy(['id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$id = $this->request->get('id','','serach_in');
		if(!$id) $this->error('参数错误');
		$this->view->assign('info',MemberTousuModel::find($id));
		return view('view');
	}



}

