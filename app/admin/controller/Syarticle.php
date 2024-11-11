<?php 
/*
 module:		帮助信息管理
 create_time:	2022-02-19 16:01:18
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\SyarticleService;
use app\admin\model\Syarticle as SyarticleModel;
use think\facade\Db;

class Syarticle extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];
			$where['title'] = $this->request->param('title', '', 'serach_in');
			$where['keytype'] = $this->request->param('keytype', '', 'serach_in');

			$dateline_start = $this->request->param('dateline_start', '', 'serach_in');
			$dateline_end = $this->request->param('dateline_end', '', 'serach_in');

			$where['dateline'] = ['between',[strtotime($dateline_start),strtotime($dateline_end)]];
			$where['isindex'] = $this->request->param('isindex', '', 'serach_in');
			$where['type'] = $this->request->param('type', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'id,title,keytype,dateline,isindex,sortid,type';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';

			$res = SyarticleService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*修改排序开关按钮操作*/
	function updateExt(){
		$postField = 'id,isindex,sortid';
		$data = $this->request->only(explode(',',$postField),'post',null);
		if(!$data['id']) $this->error('参数错误');
		try{
			SyarticleModel::update($data);
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
			$postField = 'isindex,dateline,content,title,keytype,sortid,type,description,keywords,siteid,sitename';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = SyarticleService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$id = $this->request->get('id','','serach_in');
			if(!$id) $this->error('参数错误');
			$this->view->assign('info',checkData(SyarticleModel::find($id)));
			return view('update');
		}else{
			$postField = 'id,isindex,dateline,content,title,keytype,sortid,type,description,keywords,siteid,sitename';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = SyarticleService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			SyarticleModel::destroy(['id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$id = $this->request->get('id','','serach_in');
		if(!$id) $this->error('参数错误');
		$this->view->assign('info',SyarticleModel::find($id));
		return view('view');
	}



}

