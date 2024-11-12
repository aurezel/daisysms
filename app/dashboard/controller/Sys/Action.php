<?php

namespace app\admin\controller\Sys;
use app\admin\controller\Sys\service\ActionService;
use app\admin\controller\Sys\service\ActionSetService;
use app\admin\controller\Sys\model\Action as ActionModel;
use app\admin\controller\Sys\model\Menu;
use app\admin\controller\Sys\model\Application;
use app\admin\controller\Sys\service\ExtendService;
use app\admin\controller\Admin;
use think\facade\Db;


class Action extends Admin
{
	
	public function initialize(){
		parent::initialize();
		config(['view_path'=>app_path()],'view');
	}
	
	private function getTableList(){
		$list = Db::query('show tables');
		foreach($list as $k=>$v){
			$tableList[] = str_replace(config('database.connections.mysql.prefix'),'',$v['Tables_in_'.config('database.connections.mysql.database')]);
		}
		$no_show_table = ['application','menu','config','role','upload_config','action','access','field','log'];
		foreach($tableList as $key=>$val){
			if(in_array($val,$no_show_table)){
				unset($tableList[$key]);
			}
		}
		
		return $tableList;
	}
	
    public function index(){ 
	   if (!$this->request->isAjax()){
		    $menu_id = $this->request->get('menu_id','','intval');
		    $menuInfo = Menu::find($menu_id);
			$applicationInfo = Application::find($menuInfo['app_id']);
			$tpl = $applicationInfo['app_type'] == 1 ? 'controller/Sys/view/action/index' : 'controller/Sys/view/action/api_index';
			$this->view->assign('menu_id',$menu_id);
			return view($tpl);
		}else{
			$limit  = input('post.limit', 20, 'intval');
			$offset = input('post.offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;
			
			$menu_id = $this->request->get('menu_id','','intval');

			$limit = ($page-1) * $limit.','.$limit;
			try{
				$data['rows']  = ActionModel::where(['menu_id'=>$menu_id])->order('sortid asc')->select();
				$data['total'] = ActionModel::where(['menu_id'=>$menu_id])->count();
			}catch(\Exception $e){
				exit($e->getMessage());
			}
			$list = $data['rows'];
			$menuInfo = Menu::find($menu_id);
			$applicationInfo = Application::find($menuInfo['app_id']);
			if($applicationInfo['app_type'] == 1){
				$actionList = ActionSetService::actionList() + ExtendService::$adminActions;
			}else{
				$actionList = ActionSetService::apiList() + ExtendService::$apiActions;
			}
			
			foreach($list as $key=>$val){
				$list[$key]['type'] = $actionList[$val['type']];
			}
			
			$data['rows']  = $list;

			return json($data);
		}
    }
	
	public function add(){
		if (!$this->request->isPost()){
			$menu_id = $this->request->get('menu_id','','intval');
			$menuInfo = Menu::find($menu_id);
			$applicationInfo = Application::find($menuInfo['app_id']);
			$tpl = $applicationInfo['app_type'] == 1 ? 'controller/Sys/view/action/info' : 'controller/Sys/view/action/api_info';
			$actionList = $applicationInfo['app_type'] == 1 ? ActionSetService::actionList() : ActionSetService::apiList();
			$extendAction = $applicationInfo['app_type'] == 1 ? ExtendService::$adminActions : ExtendService::$apiActions;
			$this->view->assign('menu_id',$menu_id);
			$this->view->assign('actionList',$actionList + $extendAction);
			$this->view->assign('requestList',ActionSetService::requestList());
			$this->view->assign('tableList',$this->getTableList());
			return view($tpl);
		}else{
			$data = $this->request->post();
			$data['sql_query'] = $this->request->post('sql_query','','sql_replace');
			try{
				ActionService::saveData('add',$data);
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}
	
	//快速生成
	public function fast(){
		if (!$this->request->isPost()){
			$this->view->assign('menu_id',$this->request->param('menu_id'));
			return view('controller/Sys/view/action/fast');
		}else{
			$data = $this->request->post();
			try{
				ActionService::addFast($data);	
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}
	
	public function update(){
		if (!$this->request->isPost()){
			$id = $this->request->get('id','','intval');
			if(!$id) $this->error('参数错误');
			$actionInfo = ActionModel::find($id);
			$menuInfo = Menu::find($actionInfo['menu_id']);
			$applicationInfo = Application::find($menuInfo['app_id']);
			$tpl = $applicationInfo['app_type'] == 1 ? 'controller/Sys/view/action/info' : 'controller/Sys/view/action/api_info';
			$actionList = $applicationInfo['app_type'] == 1 ? ActionSetService::actionList() : ActionSetService::apiList();
			$extendAction = $applicationInfo['app_type'] == 1 ? ExtendService::$adminActions : ExtendService::$apiActions;
			$this->view->assign('actionList',$actionList + $extendAction);
			$this->view->assign('info',$actionInfo);
			$this->view->assign('menu_id',$actionInfo['menu_id']);
			$this->view->assign('requestList',ActionSetService::requestList());
			$this->view->assign('tableList',$this->getTableList());
			return view($tpl);
		}else{
			$data = $this->request->post();
			$data['sql_query'] = $this->request->post('sql_query','','sql_replace');
			try{
				ActionService::saveData('edit',$data);
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}
	
	//更新排序
	public function setSort(){
		$id = $this->request->post('id','','intval');
		$sortid = $this->request->post('sortid','','intval');
		if(!$id || !$sortid) $this->error('参数错误');

		try{
			ActionModel::update(['id'=>$id,'sortid'=>$sortid]);
		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
		return json(['status'=>'00','msg'=>'修改成功']);
	}
	
	//箭头排序
	public function arrowsort(){
		$id = $this->request->post('id','','intval');
		$type = $this->request->post('type','','intval');		
		if(!$id || !$type) $this->error('参数错误');	
		try{
			ActionService::arrowsort($id,$type);
		}catch(\Exception $e){
			$this->error($e->getMessage());
		}
		return json(['status'=>'00','msg'=>'设置成功']);
	}
	
	public function delete(){
		$id = $this->request->post('id','','intval');
		if(!$id) $this->error('参数错误');

		try{
			ActionModel::destroy($id);
		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
		return json(['status'=>'00','msg'=>'删除成功']);
	}
	
	
	/*修改排序、开关按钮操作 如果没有此类操作 可以删除该方法*/
	function updateExt(){
		$data = $this->request->post();
		if(!$data['id']) $this->error('参数错误');
		try{
			ActionModel::update($data);
		}catch(\Exception $e){
			$this->error($e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}
	
	public function config(){
		return view('controller/Sys/view/action/config');
	}
	
}
