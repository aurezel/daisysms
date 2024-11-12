<?php 
/*
 module:		会员管理
 create_time:	2023-05-04 06:43:27
 author:		
 contact:		
*/

namespace app\dashboard\service;
use app\dashboard\model\History;
use think\exception\ValidateException;
use xhadmin\CommonService;

class HistoryService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
    public static function indexList($where,$field,$order,$limit,$page){
        try{

            $where[] = ['user_id','=',session('user.user_id')];
            $res = History::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);

        }catch(\Exception $e){
            abort(500,$e->getMessage());
        }
        return ['rows'=>$res->items(),'total'=>$res->total()];
    }

    public static function statsList($where,$field,$order,$limit,$page){
        try{
//            var_dump($where);
            $where[] = ['user_id','=',session('user.user_id')];
//            $res = History::where($where)->field($field)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
            $res = History::where($where)
                ->field('user_id, service_name, DATE(FROM_UNIXTIME(date)) as date, COUNT(*) as message_count, SUM(price) as total_amount, AVG(price) as avg_price')
                ->group('user_id, service_name, DATE(FROM_UNIXTIME(date))')
                ->order('date DESC, user_id')
                ->paginate(['list_rows' => $limit, 'page' => $page]);

        }catch(\Exception $e){
            abort(500,$e->getMessage());
        }
        return ['rows'=>$res->items(),'total'=>$res->total()];
    }



}

