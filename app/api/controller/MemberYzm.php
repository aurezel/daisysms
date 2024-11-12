<?php 
/*
 module:		验证码
 create_time:	2020-11-13 15:43:51
 author:		
 contact:		
*/

namespace app\api\controller;

use app\api\service\MemberYzmService;
use app\api\model\MemberYzm as MemberYzmModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

class MemberYzm extends Common {


	/**
	* @api {get} /MemberYzm/index 01、首页数据列表
	* @apiGroup MemberYzm
	* @apiVersion 1.0.0
	* @apiDescription  首页数据列表
	* @apiParam (输入参数：) {int}     		[limit] 每页数据条数（默认20）
	* @apiParam (输入参数：) {int}     		[page] 当前页码
	* @apiParam (输入参数：) {string}		[mobile] mobile 
	* @apiParam (输入参数：) {string}		[verify] verify 
	* @apiParam (输入参数：) {string}		[verify_id] verify_id 
	* @apiParam (输入参数：) {string}		[type] type 

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
		$where['mobile'] = $this->request->get('mobile', '', 'serach_in');
		$where['verify'] = $this->request->get('verify', '', 'serach_in');
		$where['verify_id'] = $this->request->get('verify_id', '', 'serach_in');
		$where['type'] = $this->request->get('type', '', 'serach_in');

		$field = '*';
		$orderby = 'id desc';

		$res = MemberYzmService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
		return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($res));
	}

	/**
	* @api {post} /MemberYzm/add 02、添加
	* @apiGroup MemberYzm
	* @apiVersion 1.0.0
	* @apiDescription  添加
	* @apiParam (输入参数：) {string}			mobile mobile 
	* @apiParam (输入参数：) {string}			verify verify 
	* @apiParam (输入参数：) {string}			verify_id verify_id 
	* @apiParam (输入参数：) {string}			type type 

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
		$postField = 'mobile,verify,verify_id,type';
		$data = $this->request->only(explode(',',$postField),'post',null);
		$res = MemberYzmService::add($data);
		return $this->ajaxReturn($this->successCode,'操作成功',$res);
	}

	/**
	* @api {post} /MemberYzm/update 03、修改
	* @apiGroup MemberYzm
	* @apiVersion 1.0.0
	* @apiDescription  修改
	
	* @apiParam (输入参数：) {string}     		id 主键ID (必填)
	* @apiParam (输入参数：) {string}			mobile mobile 
	* @apiParam (输入参数：) {string}			verify verify 
	* @apiParam (输入参数：) {string}			verify_id verify_id 
	* @apiParam (输入参数：) {string}			type type 

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
		$postField = 'id,mobile,verify,verify_id,type';
		$data = $this->request->only(explode(',',$postField),'post',null);
		if(empty($data['id'])){
			throw new ValidateException('参数错误');
		}
		$where['id'] = $data['id'];
		$res = MemberYzmService::update($where,$data);
		return $this->ajaxReturn($this->successCode,'操作成功');
	}

	/**
	* @api {post} /MemberYzm/delete 04、删除
	* @apiGroup MemberYzm
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
			MemberYzmModel::destroy($data);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $this->ajaxReturn($this->successCode,'操作成功');
	}

	/**
	* @api {get} /MemberYzm/view 05、查看详情
	* @apiGroup MemberYzm
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
		$field='id,mobile,verify,verify_id,type';
		$res  = checkData(MemberYzmModel::field($field)->where($data)->find());
		return $this->ajaxReturn($this->successCode,'返回成功',$res);
	}



}

