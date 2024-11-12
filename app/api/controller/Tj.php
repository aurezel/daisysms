<?php
/*
 module:		会员管理
 create_time:	2020-08-09 18:28:55
 author:		
 contact:		
*/

namespace app\api\controller;

use app\admin\model\Agent;
use app\admin\model\Bill;
use app\admin\model\Incomeday;
use app\admin\model\Tradegroup;
use app\api\model\Apiuser;
use app\api\service\ActiveService;
use app\api\service\BinanceService;
use app\api\service\CavenService;
use app\api\service\MemberFindMethodService;
use app\api\service\PayService;
use think\facade\Db;
use think\facade\Log;


class Tj extends Common
{
    //生成支付账单 作废
    function fw(){
        $date = time()-3600*24*10;
        $sql = "select count(*) as b,userid FROM cd_apilog where optype=1 AND userid>0 AND dateline>='".$date."' GROUP BY userid ORDER BY b desc;";
        $list = Db::query($sql);
        echo '访问次数-用户ID<br />';
        if(!empty($list)){
            foreach($list as $key=>$v){
                echo $v['b'].'-'.$v['userid'].'<br />';
            }
        }
    }
}