<?php 
/*
 module:		上传配置
 create_time:	2020-05-06 22:56:53
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\UploadConfig;
use think\exception\ValidateException;
use xhadmin\CommonService;

class UploadConfigService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = UploadConfig::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}


	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$res = UploadConfig::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res->id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($data){
		try{
			$res = UploadConfig::update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res;
	}




}

