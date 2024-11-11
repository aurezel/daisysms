<?php 
/*
 module:		通知系统
 create_time:	2020-07-25 19:26:57
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\NoticeService;
use app\admin\model\Notice as NoticeModel;
use think\facade\Db;

class Notice extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];
			$where['memberid'] = $this->request->param('memberid', '', 'serach_in');
			$where['membername'] = $this->request->param('membername', '', 'serach_in');

			$addtime_start = $this->request->param('addtime_start', '', 'serach_in');
			$addtime_end = $this->request->param('addtime_end', '', 'serach_in');

			$where['addtime'] = ['between',[strtotime($addtime_start),strtotime($addtime_end)]];
			$where['content'] = $this->request->param('content', '', 'serach_in');
			$where['status'] = $this->request->param('status', '', 'serach_in');
			$where['customerid'] = $this->request->param('customerid', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'id,memberid,membername,addtime,content,status,customerid';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';

			$res = NoticeService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*修改排序开关按钮操作*/
	function updateExt(){
		$postField = 'id,status';
		$data = $this->request->only(explode(',',$postField),'post',null);
		if(!$data['id']) $this->error('参数错误');
		try{
			NoticeModel::update($data);
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
			$postField = 'memberid,membername,addtime,content,status,customerid';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = NoticeService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$id = $this->request->get('id','','serach_in');
			if(!$id) $this->error('参数错误');
			$this->view->assign('info',checkData(NoticeModel::find($id)));
			return view('update');
		}else{
			$postField = 'id,memberid,membername,addtime,content,status,customerid';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = NoticeService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			NoticeModel::destroy(['id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$id = $this->request->get('id','','serach_in');
		if(!$id) $this->error('参数错误');
		$this->view->assign('info',NoticeModel::find($id));
		return view('view');
	}



}

