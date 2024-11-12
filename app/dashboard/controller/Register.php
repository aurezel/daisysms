<?php
namespace app\dashboard\controller;
use app\dashboard\controller\Dashboard;
use think\exception\ValidateException;

class Register extends Dashboard
{
	
	//用户登录
    public function index(){
		if (!$this->request->isPost()) {
            return view('index');
        } else {
			$postField = 'username,password,verify';
			$data = $this->request->only(explode(',',$postField),'post',null);
            var_dump($data);
			if(!captcha_check($data['verify'])){
				throw new ValidateException('Verification code error');
			}
            if($this->checkRegister($data)){
				$this->success('Register Success', url('dashboard/Index/index'));
			}
        }
    }
	
    //验证登录
    private function checkRegister($data){
		$where['membername'] = $data['username'];
		$where['pwd']  = md5($data['password'].config('my.password_secrect'));
//        echo $where['pwd'];
		try{
			$info = db('members')->field("member_id as user_id,email,status,umoney")->where($where)->find();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		
		if(!$info){
			throw new ValidateException("请检查用户名或者密码");
		}
		if(!($info['status'])){
			throw new ValidateException("该账户被禁用");
		}
        
//		$info['nodes'] = db("access")->where('role_id','in',$info['user_role_ids'])->column('purviewval','id');
//		$info['nodes'] = array_unique($info['nodes']);
		
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
