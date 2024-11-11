<?php 
/*
 module:		接口告警
 create_time:	2020-09-23 11:20:35
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\InterfaceErrorService;
use app\admin\model\InterfaceError as InterfaceErrorModel;
use think\facade\Db;

class InterfaceError extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'id,url,params,created_at';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';

			$res = InterfaceErrorService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}



}

