<?php
/*
 module:		聊天设置
 create_time:	2020-05-13 15:52:54
 author:		
 contact:		
*/

namespace app\api\controller;

use app\admin\model\Agent;
use think\facade\Db;
use think\facade\Log;

class GetContact extends Common
{


    /**
     * @api {post} /ChatSetting/view 01、查看数据
     *
     */
    function view()
    {
        try {
            $channel =  $this->request->get('channel');
            $channel = empty($channel)?'sysdl':$channel;
            $wherebb['channel'] = $channel;
            $resutl= Db::name('agent')->where($wherebb)->field('nick,contact,contactpic,channel,groupid')->find();
            if(!empty($resutl)){
                $redata=$resutl;
                $str='';
                $picarr=[];
                if(!empty($redata['contactpic'])){
                    $sparr = explode("|",$redata['contactpic']);
                    if(!empty($sparr)){

                        foreach($sparr as $key=>$v){
                            if(!empty($v)){
                                $sparr = explode(";",$v);
                                if(count($sparr)==2){
                                    $picarr[]=$sparr;
                                    $str.='<span class="title">'.$sparr[0].':</span><img src="'.$sparr[1].'" class="apppic" />';
                                }
                            }
                        }
                    }
                }
                unset($redata['contactpic']);
                $redata['contactpic'] = $picarr;
                //print_r($redata);
            }
            $resutl= Db::name('chat_setting')->where($wherebb)->field('androidlink,ioslink')->find();
            if(!empty($resutl)){
                $redata['androidlink']=$resutl['androidlink'];
                $redata['ioslink']=$resutl['ioslink'];
            }else{
                $redata['androidlink']='';
                $redata['ioslink']='';
            }

        } catch (\Exception $e) {
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => $e->getMessage()]);
        }
//        Log::info('接口输出：' . print_r($res, true));
        return $this->reDecodejson(['status' => $this->successCode, 'data' => $redata]);
    }




}

