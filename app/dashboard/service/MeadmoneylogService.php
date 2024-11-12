<?php 
/*
 module:		渠道账号流水
 create_time:	2023-04-09 23:25:55
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Meadmoneylog;
use think\exception\ValidateException;
use xhadmin\CommonService;

class MeadmoneylogService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = db('meadmoneylog')->field($field)->alias('a')->join('agent b','a.user_id=b.user_id','left')->where($where)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
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
			$res = Meadmoneylog::create($data);
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




}

