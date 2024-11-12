<?php 
/*
 module:		验证码
 create_time:	2020-11-13 15:43:52
 author:		
 contact:		
*/

namespace app\api\service;
use app\api\model\MemberYzm;
use think\facade\Log;
use think\exception\ValidateException;
use xhadmin\CommonService;

class MemberYzmService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$orderby,$limit,$page){
		try{
			$res = MemberYzm::where($where)->field($field)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return ['list'=>$res->items(),'count'=>$res->total()];
	}


	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$res = MemberYzm::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return $res->id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($where,$data){
		try{
			$res = MemberYzm::where($where)->update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		return $res;
	}




}

