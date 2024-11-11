<?php 
/*
 module:		接口访问日志
 create_time:	2023-03-28 10:16:13
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\ApilogService;
use app\admin\model\Apilog as ApilogModel;
use think\facade\Db;

class Apilog extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];
			$where['userid'] = $this->request->param('userid', '', 'serach_in');
			$where['optype'] = $this->request->param('optype', '', 'serach_in');
			$where['opaction'] = $this->request->param('opaction', '', 'serach_in');
			$where['ip'] = $this->request->param('ip', '', 'serach_in');

			$dateline_start = $this->request->param('dateline_start', '', 'serach_in');
			$dateline_end = $this->request->param('dateline_end', '', 'serach_in');

			$where['dateline'] = ['between',[strtotime($dateline_start),strtotime($dateline_end)]];

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'id,userid,optype,opaction,ip,dateline,opdes';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';

			$res = ApilogService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			ApilogModel::destroy(['id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$id = $this->request->get('id','','serach_in');
		if(!$id) $this->error('参数错误');
		$this->view->assign('info',ApilogModel::find($id));
		return view('view');
	}



}

