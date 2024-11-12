<?php 
/*
 module:		通知系统
 create_time:	2020-07-25 19:46:59
 author:		
 contact:		
*/

namespace app\api\controller;

use app\api\service\NoticeService;
use app\api\model\Notice as NoticeModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

class Notice extends Common {


	/**
	* @api {get} /Notice/index 01、首页数据列表
	* @apiGroup Notice
	* @apiVersion 1.0.0
	* @apiDescription  首页数据列表
	* @apiParam (输入参数：) {int}     		[limit] 每页数据条数（默认20）
	* @apiParam (输入参数：) {int}     		[page] 当前页码
	* @apiParam (输入参数：) {string}		[membername] 会员名称 
	* @apiParam (输入参数：) {string}		[addtime_start] 发起时间开始
	* @apiParam (输入参数：) {string}		[addtime_end] 发起时间结束
	* @apiParam (输入参数：) {string}		[content] 内容 
	* @apiParam (输入参数：) {int}			[status] 状态 开启|1,关闭|0

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.data 返回数据
	* @apiParam (成功返回参数：) {string}     	array.data.list 返回数据列表
	* @apiParam (成功返回参数：) {string}     	array.data.count 返回数据总数
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","data":""}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"查询失败"}
	*/
	function index(){
		$limit  = $this->request->get('limit', 20, 'intval');
		$page   = $this->request->get('page', 1, 'intval');

		$where = [];
		$where['memberid'] = $this->request->get('memberid', '', 'serach_in');
		$where['membername'] = $this->request->get('membername', '', 'serach_in');

		$addtime_start = $this->request->get('addtime_start', '', 'serach_in');
		$addtime_end = $this->request->get('addtime_end', '', 'serach_in');

		$where['addtime'] = ['between',[strtotime($addtime_start),strtotime($addtime_end)]];
		$where['content'] = $this->request->get('content', '', 'serach_in');
		$where['status'] = $this->request->get('status', '', 'serach_in');
		$where['customerid'] = $this->request->get('customerid', '', 'serach_in');

		$field = '*';
		$orderby = 'id desc';

		$res = NoticeService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
		return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($res));
	}

	/**
	* @api {post} /Notice/add 02、添加
	* @apiGroup Notice
	* @apiVersion 1.0.0
	* @apiDescription  添加
	* @apiParam (输入参数：) {int}				memberid 会员名称 
	* @apiParam (输入参数：) {string}			membername 会员名称 
	* @apiParam (输入参数：) {string}			content 内容 
	* @apiParam (输入参数：) {int}				status 状态 开启|1,关闭|0
	* @apiParam (输入参数：) {int}				customerid 接单客服 

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码  201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","data":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"操作失败"}
	*/
	function add(){
		$postField = 'memberid,membername,addtime,content,status,customerid';
		$data = $this->request->only(explode(',',$postField),'post',null);
		$res = NoticeService::add($data);
		return $this->ajaxReturn($this->successCode,'操作成功',$res);
	}

	/**
	* @api {post} /Notice/update 03、修改
	* @apiGroup Notice
	* @apiVersion 1.0.0
	* @apiDescription  修改
	
	* @apiParam (输入参数：) {string}     		id 主键ID (必填)
	* @apiParam (输入参数：) {int}				memberid 会员名称 
	* @apiParam (输入参数：) {string}			membername 会员名称 
	* @apiParam (输入参数：) {string}			addtime 发起时间 
	* @apiParam (输入参数：) {string}			content 内容 
	* @apiParam (输入参数：) {int}				status 状态 开启|1,关闭|0
	* @apiParam (输入参数：) {int}				customerid 接单客服 

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码  201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","msg":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"操作失败"}
	*/
	function update(){
		$postField = 'id,memberid,membername,addtime,content,status,customerid';
		$data = $this->request->only(explode(',',$postField),'post',null);
		if(empty($data['id'])){
			throw new ValidateException('参数错误');
		}
		$where['id'] = $data['id'];
		$res = NoticeService::update($where,$data);
		return $this->ajaxReturn($this->successCode,'操作成功');
	}

	/**
	* @api {post} /Notice/delete 04、删除
	* @apiGroup Notice
	* @apiVersion 1.0.0
	* @apiDescription  删除
	* @apiParam (输入参数：) {string}     		ids 主键id 注意后面跟了s 多数据删除

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","msg":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":"201","msg":"操作失败"}
	*/
	function delete(){
		$idx =  $this->request->post('ids', '', 'serach_in');
		if(empty($idx)){
			throw new ValidateException('参数错误');
		}
		$data['id'] = explode(',',$idx);
		try{
			NoticeModel::destroy($data);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $this->ajaxReturn($this->successCode,'操作成功');
	}

	/**
	* @api {get} /Notice/view 05、查看详情
	* @apiGroup Notice
	* @apiVersion 1.0.0
	* @apiDescription  查看详情
	
	* @apiParam (输入参数：) {string}     		id 主键ID

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.data 返回数据详情
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","data":""}
	* @apiErrorExample {json} 02 失败示例
	* {"status":"201","msg":"没有数据"}
	*/
	function view(){
		$data['id'] = $this->request->get('id','','serach_in');
		$field='id,memberid,membername,addtime,content,status,customerid';
		$res  = checkData(NoticeModel::field($field)->where($data)->find());
		return $this->ajaxReturn($this->successCode,'返回成功',$res);
	}



}

