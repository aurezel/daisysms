<?php
/*
 module:		会员管理
 create_time:	2020-08-09 18:28:55
 author:
 contact:
*/

namespace app\api\controller;
use app\api\model\DaisySmsHistory;
use app\api\model\Members;
use app\api\service\CavenService;
use app\api\service\PicEndeService;
use think\facade\Db;
use app\api\service\DaisySMSService;

//getUserKey tradeName 0 或空是bian，1为OK

//大神列表

class DaisySMS extends Common
{
    public function getBalance() {

        $result = DaisySMSService::parseDaisySmsApi(0,"getBalance");
        $balance = explode(":", $result['result']);
        if(count($balance) >= 1){
            db("config")->where(['name'=>"daisysms_balance"])->update(["data"=>$balance[1]]);
        }
        return json(['status' => 200, 'msg' => '更新成功', 'data' => '']);
    }
//    更新服务商信息
    public function getPrices() {
        $re = ['status' => 201, 'msg' => '', 'data' => ''];
        $result = DaisySMSService::parseDaisySmsApi(0,"getPrices");
        if ($result['status'] == 200) {
            $info = $result['result'];
            $info = json_decode($info, true);
            $convertedData = [];
            $updatetime = time();
            foreach ($info as $countryId => $group) {
                foreach ($group as $code => $item) {
                    $tableData = $item;
                    $tableData['code'] = $code; // 添加 'code' 字段
                    $tableData['country_id'] = $countryId; // 直接添加到 $tableData 中
                    $tableData['updatetime'] = $updatetime; // 添加 'updatetime'
                    $tableData['status'] = 1; // 添加 'status'
                    $convertedData[] = $tableData; // 将转换后的数据存入数组
                }
            }
            $existingCodes = Db::name('daisysms_service')->whereIn('code', array_column($convertedData, 'code'))->column('code');
            $updateData = [];
            $insertData = [];
            if(count($convertedData) > 0) {
                foreach ($convertedData as $item) {
                    if (in_array($item['code'], $existingCodes)) {
                        $updateData[] = $item;
                    } else {
                        $insertData[] = $item;
                    }
                }
            }
            if(!empty($updateData) || !empty($insertData)) {
                Db::name('daisysms_service')
                    ->where('code', $item['code'])
                    ->update(['status'=>0,'updatetime'=>time()]); // 这里 $item 包含需要更新的字段
            }
            if(!empty($updateData)) {
                foreach ($updateData as $item) {
                    Db::name('daisysms_service')
                        ->where('code', $item['code'])
                        ->update($item); // 这里 $item 包含需要更新的字段
                }
            }

            if (!empty($insertData)) {
                Db::name('daisysms_service')->insertAll($insertData);
            }
            $re['status'] = 200;
            $re['msg'] = "更新成功";
        }
        return json($re);
    }

    public function getNumberNow($uid, $indata)
    {
        //service_name,max_price[areas,carriers]
        $cost = $indata['custom_price'];
        $re = ['status' => 201, 'msg' => '', 'data' => ''];
//        判断账号信息
        $members = db("members")->field("umoney")->where('member_id', $uid)->find();
        if($members){
            if( ($members['umoney'] > 0 && $members['umoney'] < $cost) || $members['umoney'] == 0){
                $re['msg'] = "Not Money";
                return json($re);
            }
        }else{
            $re['msg'] = "Account Not Found";
            return json($re);
        }
        $historyInfo = DaisySmsHistory::getWhereInfo(['user_id' => $uid, 'status'=>0, 'service_name'=>$indata['service_name']], 'id');
        if($historyInfo){
            $re['msg'] = "Phone number already retrieved！";
            return json($re);
        }
//        $result = DaisySMSService::parseDaisySmsApi($uid,"getNumber", $indata);
//        file_put_contents("admin2.log", json_encode($result).json_encode($indata).PHP_EOL, FILE_APPEND);
        $result = ['status'=>200,'result'=>'ACCESS_NUMBER:85879035:12055032834'];
        if ($result['status'] == 200) {
            $response = $result['result'];
            if($response == "MAX_PRICE_EXCEEDED"){
                $this->getPrices();
            }elseif($response == "NO_NUMBERS"){
                $re['msg'] = "No Available Numbers";
                return json($re);
            }elseif($response == "TOO_MANY_ACTIVE_RENTALS" || $response == "NO_MONEY"){
                $re['msg'] = "Please Contact Administrator";
                return json($re);
            }else{
                $numberInfo = explode(":",$response);   #ACCESS_NUMBER:999999:13476711222 #ACCESS_NUMBER:85879034:12055032833
                if(count($numberInfo)>2){
                    try{
                        Db::startTrans(); #待使用中的标记为完结|取手机号不做处理 取验证码做处理
                        $HistoryData = ['phone_id'=>$numberInfo[1], "phone"=>$numberInfo[2], 'service_name'=>$indata['service_name'],'price'=>$cost, "user_id"=>$uid, "date"=>time()];
                        DaisySmsHistory::create($HistoryData);
                        $where = array();
                        $where['member_id'] = $uid;
                        $dodec=Members::setDes($where,'umoney',$cost);
                        if(!$dodec){
                            $re['msg'] = "Your account balance is insufficient, please recharge first";
                            return json($re);
                        }

                        Db::commit();
                        $re['status'] = 200;
                        $re['data'] = $response;
                    }catch(\Exception $e){
                        Db::rollback();
                        abort(config('my.error_log_code'),$e->getMessage());
                    }
                }
            }
        }
        return json($re);
    }

    public function getSmsCode($uid, $indata)    //取验证码 STATUS_OK:12345
    {
        $re = ['status' => 201, 'msg' => '', 'data' => '']; return json($re);
//        $umoney = db("members")->where('member_id', $uid)->value('umoney');
//        if(!$umoney){
//            $re['msg'] = "Account Not Found";
//            return json($re);
//        }
//        $where = [];
//        $where['id'] = $indata['id'];
//        $where['user_id'] = $uid;
//        $where['status'] = ['like',[0,1]];
//        $history = DaisySmsHistory::getWhereInfo($where,'*');
//        if(empty($history)){
//            $re['msg'] = "Error Format";
//            return json($re);
//        }
        if(!isset($indata['id'])){
            $re['msg'] = "Error Format";
            return json($re);
        }
        $re['msg'] = "Error Format";
        return json($re);
        $data = [];
        $data['id'] = $indata['id'];
        try{
//            $result = DaisySMSService::parseDaisySmsApi($uid,"getStatus", $data);
            $result['status'] = 201;
            $result['result'] = "NO_ACTIVATION";
            if ($result['status'] == 200) {
                $info = $result['result'];
                $Info = explode(":", $info, 2);   #ACCESS_NUMBER:999999:13476711222
                if (count($Info) > 1 && $info[0] == 'STATUS_OK') {
                    $historyInfo = DaisySmsHistory::getWhereInfo(['phone_id' => $uid], 'price, status, sms_code');
                    if ($historyInfo['status'] == 1) {
                        DaisySmsHistory::update(["status" => 1, 'sms_code' => $info[1]], ['phone_id' => $indata['id']]);
                    }
                    $re['status'] = 200;
                    $re['data'] = $info[1];
                }
            }else{
                $re['msg'] = $result['result'];
            }
        }catch(\Exception $e){
            abort(config('my.error_log_code'),$e->getMessage());
        }
        return json($re);
    }

    public function finished($uid, $indata)    //取验证码 STATUS_OK:12345
    {
        $re = ['status' => 201, 'msg' => '', 'data' => ''];
        $umoney = db("members")->where('member_id', $uid)->value('umoney');
        if(!$umoney){
            $re['msg'] = "Account Not Found";
            return json($re);
        }

        if(!isset($indata['id']) || !isset($indata['status'])){
            $re['msg'] = "Error Format";
            return json($re);
        }
        $data = [];
        $data['id'] = $indata['id'];
        $data['status'] = $indata['status'];

        try{
//            $result = DaisySMSService::parseDaisySmsApi($uid,"getStatus", $data);
            $result['status'] = 201;
            $result['result'] = "NO_ACTIVATION";
            if ($result['status'] == 200) {
                $info = $result['result'];
                $Info = explode(":", $info);   #ACCESS_NUMBER:999999:13476711222
                if (count($Info) > 1 && $info[0] == 'STATUS_OK') {
                    $historyInfo = DaisySmsHistory::getWhereInfo(['phone_id' => $uid], 'price, status, sms_code');
                    if ($historyInfo['status'] == 1) {
                        DaisySmsHistory::update(["status" => 1, 'sms_code' => $info[1]], ['phone_id' => $indata['id']]);
                    }
                    $re['status'] = 200;
                    $re['data'] = $info[1];
                }
            }else{
                $re['msg'] = $result['result'];
            }
        }catch(\Exception $e){
            abort(config('my.error_log_code'),$e->getMessage());
        }
        return json($re);
    }
    public function test()    //取验证码 STATUS_OK:12345
    {
        $re = ['status' => 200, 'msg' => 'ok', 'data' => ''];
        return json($re);
    }
    public function cancelPhone($uid, $indata)    //取验证码 STATUS_OK:12345
    {
        $re = ['status' => 201, 'msg' => '', 'data' => ''];
        if(!isset($indata['id']) || !isset($indata['price'])){
            $re['msg'] = "Error Format1";
            return json($re);
        }

        $cost = $indata['price'];
//        $data = [];
//        $data['id'] = $indata['id'];
//        $data['status'] = 8; //cancel
//        $result = DaisySMSService::parseDaisySmsApi($uid,"setStatus", $data);   //ACCESS_CANCEL
        $result['status'] = 200;
        $result['result'] = 'ACCESS_CANCEL';
        try{
            Db::startTrans();
            if ($result['status'] == 200 && $result['result'] == 'ACCESS_CANCEL') {
                $updateHistory = DaisySmsHistory::update(['status'=>2], ['id' => $indata['mid']]);
                $where = array();
                $where['member_id'] = $uid;
                Members::setInc($where,'umoney',$cost);
                $re['msg'] = $result['result'];
            }else{
                $re['msg'] = $result['result'];
            }
            Db::commit();
            $re['status'] = 200;
        }catch(\Exception $e){
            Db::rollback();
            abort(config('my.error_log_code'),$e->getMessage());
        }
        return json($re);
    }

    //status:6完成，status:8取消 history->status 0待收短信，1已收短信，2取消手机号，3完成
    public function setStatus($uid,$indata)
    {
        $re = ['status' => 201, 'msg' => '', 'data' => ''];
        $umoney = db("members")->where('member_id', $uid)->value('umoney');
        if(!$umoney){
            $re['msg'] = "Account Not Found";
            return json($re);
        }
        $result = DaisySMSService::parseDaisySmsApi($uid,"setStatus", $indata);
        if ($result['status'] == 200) {
            $info = $result['result'];
            if ($indata['status'] == 6 && $info == 'ACCESS_ACTIVATION') {
                $where = ['phone_id' => $indata['id']];
                $data = ['status' => 3];
                $re['status'] = 200;
            }
            if ($indata['status'] == 8 && $info == 'ACCESS_CANCEL') {
                $where = ['phone_id' => $indata['id']];
                $data = ['status' => 2];
                $re['status'] = 200;
            }
            DaisySmsHistory::update($data, $where);
        }
        return json($re);
    }

    protected function ajaxReturn($status,$msg,$data='',$token=''){
        $debug = $this->request->param('debug');
        $res = ['status'=>$status,'msg'=>$msg];
        !empty($data) && $res['data'] = $data;
        !empty($token) && $res['token'] = $token;
        if($debug){
            return json($res);
        }
        $content = json_encode($res);
        $content = PicEndeService::encrypt($content);
        return json($content);
    }

}

