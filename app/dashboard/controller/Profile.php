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
            $where['status'] = 1;
            $user = Members::getWhereInfo($where, 'membername ,email, mobile');

            $this->view->assign("user", $user);
            return view('index');
        } else {
            $where = [];
            $where['member_id'] = $user_id;
            $where['membername'] = $this->request->post('name', '', 'serach_in');
            $where['email'] = $this->request->post('email', '', 'serach_in');
            $where['mobile'] = $this->request->post('mobile', '', 'serach_in');
            if (empty($data['membername']) || empty($data['email']) || empty($data['mobile'])) {
                return json(['code' => 0, 'msg' => '用户名、邮箱或手机号不能为空']);
            }
//            Members::update($where, ['membername' => $data['membername'], 'email' => $data['email'], 'mobile' => $data['mobile']]);
//            Members::update($where, ['membername' => $data['membername'], 'email' => $data['email'], 'mobile' => $data['mobile']]);
//            $field = '*';
//            $orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';
//            $res = PaymentService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
//            return json($res);
        }
    }

//    private function checkLimit($data)
//    {
//        try{
//            $info = db('members')->field("member_id as user_id,email,status,umoney")->where($where)->find();
//        }catch(\Exception $e){
//            abort(config('my.error_log_code'),$e->getMessage());
//        }
//
//        if(!$info){
//            throw new ValidateException("Please check your username or password");
//        }
//        if(!($info['status'])){
//            throw new ValidateException("The account is disabled");
//        }
//    }
	
}
