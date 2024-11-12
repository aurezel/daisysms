<?php 
/*
 module:		接口用户列表
 create_time:	2023-03-15 16:16:42
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Apiuser;
use think\exception\ValidateException;
use xhadmin\CommonService;

class ApiuserService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = Apiuser::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}


	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$data['validtime'] = time();
			$data['createtime'] = time();
			$res = Apiuser::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res->id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($data){
		try{
			$data['validtime'] = strtotime($data['validtime']);
			$data['createtime'] = strtotime($data['createtime']);
			$data['updatetime'] = time();
			$res = Apiuser::update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res;
	}




}

