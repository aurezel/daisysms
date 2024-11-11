<?php
namespace app\admin\controller;
use think\exception\ValidateException;

class Login extends Admin
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
            if($this->checkLogin($data)){
				$this->success('登录成功', url('admin/Index/index'));
			}
        }
    }
	
    //验证登录
    private function checkLogin($data){	
		$where['a.user'] = $data['username'];
		$where['a.pwd']  = md5($data['password'].config('my.password_secrect'));

		try{
			$info = db('user')->alias('a')->join('role b', 'a.role_id in(b.role_id)')->field('a.user_id,a.name,a.user as username,a.status,a.role_id as user_role_ids,b.role_id,b.name as role_name,b.status as role_status')->where($where)->find();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		
		if(!$info){
			throw new ValidateException("请检查用户名或者密码");
		}
		if(!($info['status']) || !($info['role_status'])){
			throw new ValidateException("该账户被禁用");
		}
        
		$info['nodes'] = db("access")->where('role_id','in',$info['user_role_ids'])->column('purviewval','id');
		$info['nodes'] = array_unique($info['nodes']);
		
        session('admin', $info);
		session('admin_sign', data_auth_sign($info));
		
		event('LoginLog',$info);	//写入登录日志
		
        return true;
    }
	
	//验证码
	public function verify(){
		ob_clean();
	    return captcha();
	}

	//退出
    public function out(){
        session('admin', null);
        return redirect(url('admin/Login/index'));
    }
	
}
