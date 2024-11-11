<?php 
/*
 module:		会员管理
 create_time:	2023-05-04 06:43:27
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Member;
use think\exception\ValidateException;
use xhadmin\CommonService;

class MemberService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = db('member')->field($field)->alias('a')->join('tradegroup b','a.groupid=b.id','left')->where($where)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
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
			$data['pwd'] = md5($data['pwd'].config('my.password_secrect'));
			$res = Member::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(500,$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res->member_id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($data){
		try{
			$res = Member::update($data);
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

