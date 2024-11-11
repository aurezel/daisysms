<?php 
/*
 module:		标签
 create_time:	2020-08-18 08:27:41
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\MemberTagService;
use app\admin\model\MemberTag as MemberTagModel;
use think\facade\Db;

class MemberTag extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];
			$where['tagname'] = $this->request->param('tagname', '', 'serach_in');
			$where['member_id'] = $this->request->param('member_id', '', 'serach_in');

			$addtime_start = $this->request->param('addtime_start', '', 'serach_in');
			$addtime_end = $this->request->param('addtime_end', '', 'serach_in');

			$where['addtime'] = ['between',[strtotime($addtime_start),strtotime($addtime_end)]];

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'id,tagname,member_id,addtime';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';

			$res = MemberTagService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*添加*/
	function add(){
		if (!$this->request->isPost()){
			return view('add');
		}else{
			$postField = 'tagname,member_id,addtime';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = MemberTagService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$id = $this->request->get('id','','serach_in');
			if(!$id) $this->error('参数错误');
			$this->view->assign('info',checkData(MemberTagModel::find($id)));
			return view('update');
		}else{
			$postField = 'id,tagname,member_id,addtime';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = MemberTagService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			MemberTagModel::destroy(['id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$id = $this->request->get('id','','serach_in');
		if(!$id) $this->error('参数错误');
		$this->view->assign('info',MemberTagModel::find($id));
		return view('view');
	}



}

