<?php
namespace app\dashboard\controller;
use app\api\model\Members;
use app\dashboard\controller\Dashboard;
use think\exception\ValidateException;

class Profile extends Dashboard
{
	
	//用户登录
    public function index(){
        $user_id = session('user.user_id');
		if (!$this->request->isPost()) {
            $where = [];
            $where['member_id'] = $user_id;
            $user = Members::getWhereInfo($where, 'membername ,email, mobile');

            $this->view->assign("user", $user);
            return view('index');
        } else {

        }
    }

	
}
