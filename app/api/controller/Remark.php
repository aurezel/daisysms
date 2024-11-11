<?php 
/*
 module:		客服备注
 create_time:	2020-07-25 19:47:01
 author:		
 contact:		
*/

namespace app\api\controller;

use app\api\service\RemarkService;
use app\api\model\Remark as RemarkModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

class Remark extends Common {


	/**
	* @api {get} /Remark/index 01、首页数据列表
	* @apiGroup Remark
	* @apiVersion 1.0.0
	* @apiDescription  首页数据列表
	* @apiParam (输入参数：) {int}     		[limit] 每页数据条数（默认20）
	* @apiParam (输入参数：) {int}     		[page] 当前页码
	* @apiParam (输入参数：) {string}		[customerid] 客服id 
	* @apiParam (输入参数：) {string}		[membername] 会员名称 
	* @apiParam (输入参数：) {string}		[content] 内容 
	* @apiParam (输入参数：) {int}			[status] 状态 接待中|0|success,完成|1|success,关闭|2|danger
	* @apiParam (输入参数：) {string}		[addtime_start] 添加时间开始
	* @apiParam (输入参数：) {string}		[addtime_end] 添加时间结束
	* @apiParam (输入参数：) {string}		[operatorname] 录入人名称 

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
		$where['a.customerid'] = $this->request->get('customerid', '', 'serach_in');
		$where['a.memberid'] = $this->request->get('memberid', '', 'serach_in');
		$where['a.membername'] = $this->request->get('membername', '', 'serach_in');
		$where['a.content'] = $this->request->get('content', '', 'serach_in');
		$where['a.status'] = $this->request->get('status', '', 'serach_in');

		$addtime_start = $this->request->get('addtime_start', '', 'serach_in');
		$addtime_end = $this->request->get('addtime_end', '', 'serach_in');

		$where['a.addtime'] = ['between',[strtotime($addtime_start),strtotime($addtime_end)]];
		$where['a.operatorid'] = $this->request->get('operatorid', '', 'serach_in');
		$where['a.operatorname'] = $this->request->get('operatorname', '', 'serach_in');

		$field = 'a.*,b.name as customername';
		$orderby = 'id desc';

		$res = RemarkService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
		return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($res));
	}

	/**
	* @api {post} /Remark/add 02、添加
	* @apiGroup Remark
	* @apiVersion 1.0.0
	* @apiDescription  添加
	* @apiParam (输入参数：) {string}			customerid 客服id 
	* @apiParam (输入参数：) {int}				memberid 会员id 
	* @apiParam (输入参数：) {string}			membername 会员名称 
	* @apiParam (输入参数：) {string}			avatar 会员头像 
	* @apiParam (输入参数：) {string}			content 内容 
	* @apiParam (输入参数：) {int}				status 状态 接待中|0|success,完成|1|success,关闭|2|danger

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
		$postField = 'customerid,memberid,membername,avatar,content,status,addtime';
		$data = $this->request->only(explode(',',$postField),'post',null);
		$res = RemarkService::add($data);
		return $this->ajaxReturn($this->successCode,'操作成功',$res);
	}

	/**
	* @api {post} /Remark/update 03、修改
	* @apiGroup Remark
	* @apiVersion 1.0.0
	* @apiDescription  修改
	
	* @apiParam (输入参数：) {string}     		id 主键ID (必填)
	* @apiParam (输入参数：) {string}			customerid 客服id 
	* @apiParam (输入参数：) {int}				memberid 会员id 
	* @apiParam (输入参数：) {string}			membername 会员名称 
	* @apiParam (输入参数：) {string}			avatar 会员头像 
	* @apiParam (输入参数：) {string}			content 内容 
	* @apiParam (输入参数：) {int}				status 状态 接待中|0|success,完成|1|success,关闭|2|danger
	* @apiParam (输入参数：) {string}			addtime 添加时间 

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
		$postField = 'id,customerid,memberid,membername,avatar,content,status,addtime';
		$data = $this->request->only(explode(',',$postField),'post',null);
		if(empty($data['id'])){
			throw new ValidateException('参数错误');
		}
		$where['id'] = $data['id'];
		$res = RemarkService::update($where,$data);
		return $this->ajaxReturn($this->successCode,'操作成功');
	}

	/**
	* @api {post} /Remark/delete 04、删除
	* @apiGroup Remark
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
			RemarkModel::destroy($data);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $this->ajaxReturn($this->successCode,'操作成功');
	}

	/**
	* @api {get} /Remark/view 05、查看详情
	* @apiGroup Remark
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
		$field='id,customerid,memberid,membername,avatar,content,status,addtime';
		$res  = checkData(RemarkModel::field($field)->where($data)->find());
		return $this->ajaxReturn($this->successCode,'返回成功',$res);
	}



}

