<?php
namespace app\dashboard\controller;
use app\dashboard\controller\Dashboard;
use think\exception\ValidateException;

class Referrals extends Dashboard
{
	
	//用户登录
    public function index(){
		if (!$this->request->isPost()) {
            return view('index');
        } else {
			$postField = 'username,password,verify';
			$data = $this->request->only(explode(',',$postField),'post',null);
			if(!captcha_check($data['verify'])){
				throw new ValidateException('验证码错误');
			}
//            var_dump($this->checkLogin($data));
            if($this->checkLogin($data)){
				$this->success('登录成功', url('dashboard/Index/index'));
			}
        }
    }

	
}
