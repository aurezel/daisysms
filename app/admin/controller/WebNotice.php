<?php 
/*
 module:		公告
 create_time:	2022-04-21 11:05:34
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\WebNoticeService;
use app\admin\model\WebNotice as WebNoticeModel;
use think\facade\Db;

class WebNotice extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];
			$where['status'] = $this->request->param('status', '', 'serach_in');

			$starttime_start = $this->request->param('starttime_start', '', 'serach_in');
			$starttime_end = $this->request->param('starttime_end', '', 'serach_in');

			$where['starttime'] = ['between',[strtotime($starttime_start),strtotime($starttime_end)]];

			$overtime_start = $this->request->param('overtime_start', '', 'serach_in');
			$overtime_end = $this->request->param('overtime_end', '', 'serach_in');

			$where['overtime'] = ['between',[strtotime($overtime_start),strtotime($overtime_end)]];
			$where['type'] = $this->request->param('type', '', 'serach_in');
			$where['level'] = $this->request->param('level', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'id,title,content,status,starttime,overtime,type,posttime,level';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';

			$res = WebNoticeService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*修改排序开关按钮操作*/
	function updateExt(){
		$postField = 'id,status';
		$data = $this->request->only(explode(',',$postField),'post',null);
		if(!$data['id']) $this->error('参数错误');
		try{
			WebNoticeModel::update($data);
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
			$postField = 'title,content,status,starttime,overtime,type,posttime,level';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = WebNoticeService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$id = $this->request->get('id','','serach_in');
			if(!$id) $this->error('参数错误');
			$this->view->assign('info',checkData(WebNoticeModel::find($id)));
			return view('update');
		}else{
			$postField = 'id,title,content,status,starttime,overtime,type,posttime,level';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = WebNoticeService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			WebNoticeModel::destroy(['id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$id = $this->request->get('id','','serach_in');
		if(!$id) $this->error('参数错误');
		$this->view->assign('info',WebNoticeModel::find($id));
		return view('view');
	}



}

