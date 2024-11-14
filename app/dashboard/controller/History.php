<?php 
/*
 module:		消息管理
 create_time:	2020-07-25 20:01:31
 author:		
 contact:		
*/

namespace app\dashboard\controller;

use app\dashboard\service\HistoryService;
use app\dashboard\service\PaymentService;
use app\dashboard\model\History as HistoryModel;
use app\dashboard\model\History as PaymentModel;
use think\facade\Db;

class History extends Dashboard {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){

			return view('index');
		}else{

			$limit  = $this->request->post('limit', 200, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];
            $where['name'] = $this->request->param('service', '', 'serach_in');
            $where['status'] = 0;//$this->request->param('status', '', 'serach_in');
            $where['user_id'] = session('user.user_id');
            if (!empty($where['name'])) {
                $where['name'] = ['like', '%' . $where['name'] . '%']; // 使用 LIKE 模糊查询
            }
			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = '*';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';
//            $orderby = ($sort && $order) ? "CASE WHEN code = 'unlisted' THEN 1 ELSE 0 END," . $sort.' '.$order :  "CASE WHEN code = 'unlisted' THEN 1 ELSE 0 END".' ,id desc';

            $res = HistoryService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

    function rentals()
    {
        if (!$this->request->isAjax()){
            return view('rentals');
        }else{

            $limit  = $this->request->post('limit', 200, 'intval');
            $offset = $this->request->post('offset', 0, 'intval');
            $page   = floor($offset / $limit) +1 ;

            $where = [];
//            $where['service_name'] = $this->request->param('service_name', 'Google', 'serach_in');
            $where['status'] = $this->request->param('status', '', 'serach_in');
            if(!empty($where['status'])){
                $where['status'] = ['in', [0,1,3]];
            }

            $order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
            $sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

            $field = '*';
            $orderby = ($sort && $order) ? $sort.' '.$order : 'id asc';

            $res = HistoryService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
            return json($res);
        }
    }

    function payments()
    {
        if (!$this->request->isAjax()){
            return view('payment');
        }else{

            $limit  = $this->request->post('limit', 200, 'intval');
            $offset = $this->request->post('offset', 0, 'intval');
            $page   = floor($offset / $limit) +1 ;

            $where = [];

            $order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
            $sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

            $field = '*';
            $orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';
            $res = PaymentService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
            return json($res);
        }
    }
    function stats()
    {
        if (!$this->request->isAjax()){
            return view('stats');
        }else{

            $limit  = $this->request->post('limit', 200, 'intval');
            $offset = $this->request->post('offset', 0, 'intval');
            $page   = floor($offset / $limit) +1 ;

            $where = [];
            $where['status'] = ['in', [0,1,3]];#$this->request->param('status', [1,3], 'serach_in');
            $order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
            $sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

            $field = '*';
            $orderby = ($sort && $order) ? $sort.' '.$order : 'id asc';
            $res = HistoryService::statsList(formatWhere($where),$field,$orderby,$limit,$page);

            return json($res);
        }
    }
	/*修改排序开关按钮操作*/
	function updateExt(){
		$postField = 'mid,isread';
		$data = $this->request->only(explode(',',$postField),'post',null);
		if(!$data['mid']) $this->error('参数错误');
		try{
			MessageModel::update($data);
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
			$postField = 'id,description,amount,user_id';


			$data = $this->request->only(explode(',',$postField),'post',null);
            $userInfo = db('user')->field("balance","user_id")->where('user_id', $data['user_id'])->find();
            if(empty($userInfo)){
                $re = ['status' => 201, 'msg' => '用户不存在'];
                return json($re);
            }
            $data['balance'] = $userInfo['balance'] + $data['amount'];
			$res = PaymentService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$id = $this->request->get('id','','serach_in');
			if(!$id) $this->error('参数错误');
			$this->view->assign('info',checkData(PaymentModel::find($id)));
			return view('update');
		}else{
			$postField = 'id,description,createtime,balance,amount';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = PaymentService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			PaymentModel::destroy(['id'=>explode(',',$idx)]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}

	/*查看详情*/
	function view(){
		$mid = $this->request->get('id','','serach_in');
		if(!$mid) $this->error('参数错误');
		$this->view->assign('info',PaymentModel::find($mid));
		return view('view');
	}



}

