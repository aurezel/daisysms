<?php 
/*
 module:		活动列表
 create_time:	2023-04-14 19:00:17
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Activelist;
use think\exception\ValidateException;
use xhadmin\CommonService;

class ActivelistService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = Activelist::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
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
			$data['starttime'] = time();
			$data['endtime'] = time();
			$data['groupset'] = implode(',',$data['groupset']);
			$res = Activelist::create($data);
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
			$data['endtime'] = strtotime($data['endtime']);
			$data['groupset'] = implode(',',$data['groupset']);
			$res = Activelist::update($data);
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

