<?php
namespace app\dashboard\controller;
use app\dashboard\controller\Dashboard;
use app\dashboard\model\Members;
use think\exception\ValidateException;
use think\facade\Request;

class Register extends Dashboard
{
	
	//用户登录
    public function index(){
		if (!$this->request->isPost()) {
            return view('index');
        } else {
			$postField = 'email,username,password,verify';
			$data = $this->request->only(explode(',',$postField),'post',null);
//            var_dump($data);
			if(!captcha_check($data['verify'])){
				throw new ValidateException('Verification code error');
			}
            if($this->checkRegister($data)){
				$this->success('Register Success', url('dashboard/Index/index'));
			}
        }
    }
	
    //验证注册
    private function checkRegister($data){
		$where['membername'] = $data['username'];
		$where['email'] = $data['email'];
//		$where['pwd']  = md5($data['password'].config('my.password_secrect'));
//        echo $where['pwd'];
		try{
			$info = db('members')->field("member_id as user_id,email,status,umoney")->where($where)->find();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		
		if($info){
			throw new ValidateException("Username or email address already exists");
		}
        $time = time();
        $ip = Request::ip();
        $members = [];
        $members['regtime'] = $time;
        $members['logintime'] = $time;
        $members['regip'] = $ip;
        $members['loginip'] = $time;
        $members['membername'] = $data['username'];
        $members['email'] = $data['email'];
        $members['pwd'] = md5($data['password'].config('my.password_secrect'));
        $res = Members::create($members);
        $info["user_id"] = $res->member_id;
        $info['email'] = $data['email'];
        $info['status'] = 1;
        $info['umoney'] = 0.00;
//        var_dump($info);
//		"member_id as user_id,email,status,umoney"
		
        session('user', $info);
		session('user_sign', data_auth_sign($info));
		
		event('LoginLog',$info);	//写入登录日志
		
        return true;
    }
	
	//验证码
	public function verify(){
		ob_clean();
	    return captcha();
	}


}
