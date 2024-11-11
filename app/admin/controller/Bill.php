<?php 
/*
 module:		账单明细
 create_time:	2023-08-28 17:49:15
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\BillService;
use app\admin\model\Bill as BillModel;
use think\facade\Db;

class Bill extends Admin {


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
			$where['memberid'] = $this->request->param('memberid', '', 'serach_in');
			$where['type'] = $this->request->param('type', '', 'serach_in');
			$where['ordernumber'] = $this->request->param('ordernumber', '', 'serach_in');
			$where['parentid'] = $this->request->param('parentid', '', 'serach_in');
			$where['twoid'] = $this->request->param('twoid', '', 'serach_in');
			$where['threeid'] = $this->request->param('threeid', '', 'serach_in');
			$where['channel'] = $this->request->param('channel', '', 'serach_in');
			$where['paystatus'] = $this->request->param('paystatus', '', 'serach_in');

			$lastpaytime_start = $this->request->param('lastpaytime_start', '', 'serach_in');
			$lastpaytime_end = $this->request->param('lastpaytime_end', '', 'serach_in');

			$where['lastpaytime'] = ['between',[strtotime($lastpaytime_start),strtotime($lastpaytime_end)]];
			$where['billmonth'] = $this->request->param('billmonth', '', 'serach_in');
			$where['billtype'] = $this->request->param('billtype', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'id,mobile,memberid,title,money,type,addtime,channel,paystatus,paytime,lastpaytime,billmonth,income,billtype,tznumber';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';

			$res = BillService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$id = $this->request->get('id','','serach_in');
			if(!$id) $this->error('参数错误');
			$this->view->assign('info',checkData(BillModel::find($id)));
			return view('update');
		}else{
			$postField = 'id,mobile,memberid,title,money,type,ordernumber,addtime,updateline,status,parentid,paystatus,paytime,lastpaytime,income';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = BillService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*查看详情*/
	function view(){
		$id = $this->request->get('id','','serach_in');
		if(!$id) $this->error('参数错误');
		$this->view->assign('info',BillModel::find($id));
		return view('view');
	}



}

