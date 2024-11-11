<?php 
/*
 module:		关注列表
 create_time:	2020-08-15 11:17:10
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\FollowService;
use app\admin\model\Follow as FollowModel;
use think\facade\Db;

class Follow extends Admin {


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
			$where['friend_id'] = $this->request->param('friend_id', '', 'serach_in');

			$addtime_start = $this->request->param('addtime_start', '', 'serach_in');
			$addtime_end = $this->request->param('addtime_end', '', 'serach_in');

			$where['addtime'] = ['between',[strtotime($addtime_start),strtotime($addtime_end)]];

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'id,member_id,friend_id,addtime';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';

			$res = FollowService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*添加*/
	function add(){
		if (!$this->request->isPost()){
			return view('add');
		}else{
			$postField = 'member_id,friend_id,addtime';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = FollowService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$id = $this->request->get('id','','serach_in');
			if(!$id) $this->error('参数错误');
			$this->view->assign('info',checkData(FollowModel::find($id)));
			return view('update');
		}else{
			$postField = 'id,member_id,friend_id,addtime';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = FollowService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			FollowModel::destroy(['id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$id = $this->request->get('id','','serach_in');
		if(!$id) $this->error('参数错误');
		$this->view->assign('info',FollowModel::find($id));
		return view('view');
	}



}

