<?php 
/*
 module:		站内消息
 create_time:	2022-04-27 08:40:26
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Mysg;
use think\exception\ValidateException;
use xhadmin\CommonService;

class MysgService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = Mysg::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
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
			$data['dateline'] = time();
			$res = Mysg::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res->message_id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($data){
		try{
			$data['dateline'] = strtotime($data['dateline']);
			$data['updateline'] = time();
			$res = Mysg::update($data);
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

