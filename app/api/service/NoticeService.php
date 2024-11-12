<?php 
/*
 module:		通知系统
 create_time:	2020-07-25 19:46:59
 author:		
 contact:		
*/

namespace app\api\service;
use app\api\model\Notice;
use think\facade\Db;
use think\facade\Log;
use think\exception\ValidateException;
use xhadmin\CommonService;

class NoticeService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$orderby,$limit,$page){
		try{
			$res = Notice::where($where)->field($field)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return ['list'=>$res->items(),'count'=>$res->total()];
	}

    public static function loadData( $where, $field, $orderby, $limit, $page)
    {
        $page = $page - 1;
        $page = $page * $limit;
        $result = Db::name('notice')->field($field)->alias('a')->join('member' . ' b', 'a.' . 'ufrom' . '=b.' . 'member_id' , "INNER")
            ->join('member' . ' c', 'a.' . 'uto' . '=c.' . 'member_id' , "INNER")
            ->where($where)->limit($page, $limit)->order($orderby)->select()->toArray();
        return $result;
    }




	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$data['addtime'] = time();
			$res = Notice::create($data);
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
	public static function update($where,$data){
		try{
			!is_null($data['addtime']) && $data['addtime'] = strtotime($data['addtime']);
			$res = Notice::where($where)->update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res;
	}




}

