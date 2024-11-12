<?php 
/*
 module:		渠道管理
 create_time:	2024-07-05 16:19:50
 author:		
 contact:		
*/

namespace app\admin\service;
use app\admin\model\Agent;
use think\exception\ValidateException;
use xhadmin\CommonService;

class AgentService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = db('agent')->field($field)->alias('a')->join('tradegroup b','a.groupid=b.id','left')->where($where)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
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
			validate(\app\admin\validate\Agent::class)->scene('add')->check($data);
			$data['passwd'] = md5($data['passwd'].config('my.password_secrect'));
			$data['dateline'] = time();
			$data['tradetype'] = implode(',',$data['tradetype']);
			$data['dorule'] = implode(',',$data['dorule']);
			$res = Agent::create($data);
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
			validate(\app\admin\validate\Agent::class)->scene('update')->check($data);
			$data['dateline'] = strtotime($data['dateline']);
			$data['tradetype'] = implode(',',$data['tradetype']);
			$data['dorule'] = implode(',',$data['dorule']);
			$res = Agent::update($data);
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

