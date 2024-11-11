<?php
namespace app\dashboard\controller;

use app\api\model\DaisySmsHistory;
use app\dashboard\model\Members as MembersModel;
use app\dashboard\model\ServiceSms;
use app\dashboard\model\History as HistoryModel;
use app\dashboard\service\HistoryService;
use app\dashboard\service\PaymentService;
use app\dashboard\service\ServiceSmsService;
use app\api\controller\DaisySMS;
use think\exception\ValidateException;
use think\facade\Db;

class Index extends Dashboard
{
	
	//框架主体
    public function index(){
        if (!$this->request->isAjax()){
            $uid = session('user.user_id');

            $umoney = db("members")->where('member_id', $uid)->value('umoney');
            $historyCount = db("daisysms_history")->where('user_id', $uid)->value('count(*)');

            if($umoney <= 0 && $historyCount < 1){
                $isMoney = 1;
            }
            $this->view->assign("isMoney",$isMoney);

            return view('main');
        }else{
            $limit  = $this->request->post('limit', 500, 'intval');
            $offset = $this->request->post('offset', 0, 'intval');
            $page   = floor($offset / $limit) +1 ;

            $where = [];
            $order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
            $sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

            $field = 'id,code,name,cost * {config(my.daisy_profit} as cost,repeatable';
            $orderby = ($sort && $order) ? $sort.' '.$order : 'id desc';
            $res = ServiceSmsService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
            return json($res);
        }
    }

    function getPhone()
    {
        $res = json(['status' => 200, 'msg' => '发送成功']);
        $user_id = session('user.user_id');
//        $id = $this->request->param('id', '', 'serach_in');
        $where = [];
        $where['id'] = $this->request->param('id', '', 'serach_in');
        $where['status'] = 1;
        $serviceInfo = ServiceSms::where($where)->field('cost,code,name,custom_price')->find();#var_dump($serviceInfo);
        if (empty($serviceInfo)) throw new ValidateException ('Network is Error');
        if (empty($user_id)) throw new ValidateException ('Cache is losing');
        $history = db("daisysms_history")->where(['status'=>0,'user_id'=>$user_id,'service_name'=>$serviceInfo['name']])->value('id');
        if(!empty($history)){
            throw new ValidateException ('Has number is Active');
        }
        try {
            $indata = [];
            $indata['service_names'] = 'admin';    //发送手机号
            $indata['service_name'] = $serviceInfo['name'];    //发送手机号
            $indata['service'] = $serviceInfo['code'];    //发送手机号
            $indata['max_price'] = $serviceInfo['cost'];
            $indata['custom_price'] = $serviceInfo['custom_price'];
            $res = \app\api\controller\DaisySMS::getNumberNow($user_id, $indata);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }
    function cancel()
    {
        $res = json(['status' => 201, 'msg' => "It's failure"]);
        $user_id = session('user.user_id');
        if (empty($user_id)) throw new ValidateException ('Cache is missing');

        $where = [];
        $where['id'] = $this->request->param('id', '', 'serach_in');
        $where['status'] = 0;
        $where['user_id'] = $user_id;
        $history = DaisySmsHistory::getWhereInfo($where,'*');
        if(empty($history)){
            throw new ValidateException ('No Data To Cancel');
        }
        $history_id = $history['id'];
        try {
            $data = [];
            $data['id'] = $history['phone_id'];
            $data['price'] = $history['price'];
            $data['mid'] = $history['id'];
            $res = \app\api\controller\DaisySMS::cancelPhone($user_id, $data);
        } catch (\Exception $e) {
//            Db::rollback();
            abort(config('my.error_log_code'),$e->getMessage());
        }
        return $res;
    }

    function getCode()
    {
        $res = ['status' => 201, 'msg' => "it's failure"];
        $user_id = session('user.user_id');
        if (empty($user_id)) throw new ValidateException ('Cache is losing');
        $where = [];
        $where['b.id'] = $this->request->param('id', '', 'serach_in');
        $where['b.user_id'] = $user_id;
        $where['b.status'] = 0;
        $historyData = db('daisysms_service')->alias('a')->join('daisysms_history b','a.name=b.service_name')->where($where)->field('b.id,a.minutes,b.date,b.status,b.price,b.phone_id')->find();

        if(!empty($historyData)){
            try{
                $currentStatus = time() - $historyData['date'] <= $historyData['minutes'] * 60;
                if($currentStatus){
                    $data = [];
                    $data['id'] = $historyData['phone_id'];
                    $ret = \app\api\controller\DaisySMS::getSmsCode($user_id, $data);
                    $result = $ret->getData();
                    if($result['status'] == 200){
                        $res['status'] = 200;
                    }else{
                        $res['msg'] = $result['data'];
                    }
                }else{
                    Db::startTrans();
                    $cost = $historyData['price'];
                    if($cost > 0){
                        $rest = MembersModel::setInc(['member_id'=>$user_id],'umoney',$cost);
                    }
                    $hStatus = 4;//状态更改为超时
                    HistoryModel::update(['status'=>$hStatus], ['id'=>$historyData['id']]);
                    Db::commit();
                    $res['status'] = 200;
                }

            }catch(\Exception $e){
                Db::rollback();
                abort(config('my.error_log_code'),$e->getMessage());

            }

        }
        return json($res);
    }
    /**
     * 优化生成左侧 菜单栏方法
     * @return array
     * @author MJ
     * created_at 2020-7-20 10:46:59
     */
    public function test()
    {
        $list_1 = db("menu")->where(['status' => 1, 'app_id' => 1, 'pid' => 0])->order('sortid asc')->select()->toArray();
        $list_2 = db("menu")->where(['status' => 1, 'app_id' => 1])->where('pid', '>', 0)->order('sortid asc')->select()->toArray();
        $list = array_merge($list_1, $list_2);
        $list = array_column($list, null, 'menu_id');
        foreach ($list as $key => $val) {
            $menus[$key]['pid'] = $val['pid'];
            $menus[$key]['menu_id'] = $val['menu_id'];
            $menus[$key]['title'] = $val['title'];
            $menus[$key]['icon'] = !empty($val['menu_icon']) ? $val['menu_icon'] : 'fa fa-clone';
            $menus[$key]['url'] = !empty($val['url']) ? (strpos($val['url'], '://') ? $val['url'] : url($val['url'])) :  url('admin/'.str_replace('/','.',$val['controller_name']).'/index');
            $menus[$key]['access_url'] = !empty($val['url']) ? $val['url'] :  'admin/'.str_replace('/','.',$val['controller_name']);
        }

        $a = generateTree($menus, 'pid', 'sub', 'menu_id');
        return $a;
    }
	
	//生成左侧菜单栏
	private function getSubMenu($pid){
		$list = db("menu")->where(['status'=>1,'app_id'=>1,'pid'=>$pid])->order('sortid asc')->select();
		if($list){
			foreach($list as $key=>$val){
				$sublist = db("menu")->where(['status'=>1,'app_id'=>1,'pid'=>$val['menu_id']])->order('sortid asc')->select();
				if($sublist){
					$menus[$key]['sub'] = $this->getSubMenu($val['menu_id']);
				}
				$menus[$key]['title'] = $val['title'];
				$menus[$key]['icon'] = !empty($val['menu_icon']) ? $val['menu_icon'] : 'fa fa-clone';
				$menus[$key]['url'] = !empty($val['url']) ? (strpos($val['url'], '://') ? $val['url'] : url($val['url'])) :  url('admin/'.str_replace('/','.',$val['controller_name']).'/index');
				$menus[$key]['access_url'] = !empty($val['url']) ? $val['url'] :  'admin/'.str_replace('/','.',$val['controller_name']);

			}
			return $menus;
		}
	}
	
	public function getSubClass(){
		$class_id = $this->request->param('class_id');
		$list = db("catagory")->where('pid',$class_id)->select();
		return json($list);
	}
	
	//后台首页框架内容
	public function main(){
		
		$rootPath = app()->getRootPath();
		$res = scandir($rootPath.'/app/admin/controller/Goods');
		return view('main');
	}
	
}
