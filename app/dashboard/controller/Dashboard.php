<?php

namespace app\dashboard\controller;
use app\api\model\Members;
use think\exception\FuncNotFoundException;
use think\exception\ValidateException;
use app\BaseController;

class Dashboard extends BaseController
{
	
	public function initialize(){

		$controller = $this->request->controller();
		$action = $this->request->action();
        $app = app('http')->getName();
//        var_dump($app);
//		$app = "dashboard";
		$user = session('user');
        $userid = session('user_sign') == data_auth_sign($user) ? $user['user_id'] : 0;
        if( !$userid && ( $app <> 'dashboard' || !in_array($controller,['Login','Register']))){
			echo '<script type="text/javascript">top.parent.frames.location.href="'.url('dashboard/Login/index').'";</script>';exit();
        }

		event('DoLog');
		
		$list = db("config")->cache(true,60)->column('data','name');
		config($list,'xhadmin');
        $uMoney = Members::Where(['member_id'=>$userid])->value("umoney");
        $this->view->assign('uMoney', $uMoney);
	}
	
	public function __call($method, $args){
        throw new FuncNotFoundException('Not Found Controller',$method);
    }

}
