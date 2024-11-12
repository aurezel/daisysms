<?php 
/*
 module:		公告
 create_time:	2022-04-21 11:05:35
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\WebNotice;
use think\exception\ValidateException;
use xhadmin\CommonService;

class WebNoticeService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = WebNotice::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
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
			$data['starttime'] = strtotime($data['starttime']);
			$data['overtime'] = strtotime($data['overtime']);
			$data['posttime'] = time();
			$res = WebNotice::create($data);
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
			$data['starttime'] = strtotime($data['starttime']);
			$data['overtime'] = strtotime($data['overtime']);
			$data['posttime'] = strtotime($data['posttime']);
			$res = WebNotice::update($data);
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

