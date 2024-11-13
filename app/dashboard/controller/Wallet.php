<?php
namespace app\dashboard\controller;
use app\dashboard\service\WalletService;
use app\api\model\Members;
use app\dashboard\model\Payment;
use think\exception\ValidateException;

class Wallet extends Dashboard
{
	
	//用户登录
    public function index(){
		if (!$this->request->isPost()) {
            $where = [];
            $where['member_id'] = session('user.user_id');
            $user = Members::getWhereInfo($where, 'umoney');//->column("balance");

            $this->view->assign("umoney",$user['umoney'] );
            return view('index');
        } else {
            $user_id = session('user.user_id');
            if (empty($user_id)) throw new ValidateException ('Cache is losing');
            $where = [];
            $amount = $this->request->param('amount', '', 'serach_in');
            if($amount < 20 || $amount > 150){
                throw new ValidateException ('Recharge of this amount is not allowed');
            }

            $add = [];
            $add['description'] = "Credit Payment";
            $add['amount'] = $amount;
            $add['balance'] = session('user.balance');
            $add['user_id'] = $user_id;
            $add['createtime'] = time();

            try{
                $res = Payment::create($add);
                $add['id'] = $res->id;
//                $result = WalletService::sendPaymentRequest($add);
                $result = WalletService::sendHttpsRequest($add);
                if($result['status'] == 1){
                    return json(['status'=>'00','msg'=>'Success','data'=>$result['pay_html']]);
                }
            }catch(ValidateException $e){
                throw new ValidateException ($e->getError());
            }catch(\Exception $e){
                abort(500,$e->getMessage());
            }
            return json(['status'=>'00','msg'=>'Success','data'=>'baidu']);
//            return json(['status'=>201,'msg'=>'error']);
        }
    }

    public function charge()
    {
        file_put_contents("adminer.log","test");exit;
        $user_id = session('user.user_id');
        if (empty($user_id)) throw new ValidateException ('Cache is losing');
        $where = [];
        $amount = 50;//$this->request->param('amount', '', 'serach_in');
        if($amount < 20 || $amount > 150){
            throw new ValidateException ('Recharge of this amount is not allowed');
        }

        $add = [];
        $add['description'] = "Credit Payment";
        $add['amount'] = $amount;
        $add['balance'] = session('user.balance');
        $add['user_id'] = $user_id;
        $add['createtime'] = time();

        try{
            $res = Payment::create($add);
            $add['id'] = $res->id;
//                var_dump($add);
            $test = WalletService::sendPaymentRequest($add);
            var_dump($test);
        }catch(ValidateException $e){
            throw new ValidateException ($e->getError());
        }catch(\Exception $e){
            abort(500,$e->getMessage());
        }
        $order_id = $res->id;
        echo $order_id;
//			$postField = 'username,password,verify';
//			$data = $this->request->only(explode(',',$postField),'post',null);
//			if(!captcha_check($data['verify'])){
//				throw new ValidateException('验证码错误');
//			}
////            var_dump($this->checkLogin($data));
//            if($this->checkLogin($data)){
//				$this->success('登录成功', url('dashboard/Index/index'));
//			}
    }

    public function payment()
    {
        $user_id = session('user.user_id');
        if (empty($user_id)) throw new ValidateException ('Cache is losing');
        $where = [];
        $amount = 62;//$this->request->param('amount', '', 'serach_in');
        if($amount < 20 || $amount > 150){
            throw new ValidateException ('Recharge of this amount is not allowed');
        }

        $add = [];
        $add['description'] = "Credit Payment";
        $add['amount'] = $amount;
        $add['balance'] = session('user.balance');
        $add['user_id'] = $user_id;
        $add['createtime'] = time();

        try{
            $res = Payment::create($add);
            $add['id'] = $res->id;
//                var_dump($add);
            $test = WalletService::sendWwdpayRequest($add);
            var_dump($test);
        }catch(ValidateException $e){
            throw new ValidateException ($e->getError());
        }catch(\Exception $e){
            abort(500,$e->getMessage());
        }
        $order_id = $res->id;
        echo $order_id;
    }
}
