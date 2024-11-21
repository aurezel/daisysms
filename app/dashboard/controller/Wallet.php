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

                $result = WalletService::sendHttpsRequest($add);
                if($result['status'] == 1){
                    return json(['status'=>'00','msg'=>'Success','data'=>$result['data']['pay_html']]);
                }
            }catch(ValidateException $e){
                throw new ValidateException ($e->getError());
            }catch(\Exception $e){
                abort(500,$e->getMessage());
            }
        }
    }

	public function charge()
    {
        $user_id = session('user.user_id');
        if (empty($user_id)) throw new ValidateException ('Cache is losing');
        $where = [];
        $amount = 80;//$this->request->param('amount', '', 'serach_in');
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
    }

    public function payment()
    {
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
            $test = WalletService::sendHttpsRequest($add);
		if($test['status']){
			echo $test['data']['pay_html'];
		}
        }catch(ValidateException $e){
            throw new ValidateException ($e->getError());
        }catch(\Exception $e){
            abort(500,$e->getMessage());
        }
    }
}
