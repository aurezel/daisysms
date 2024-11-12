<?php

namespace app\admin\controller;
use app\admin\service\UserService;
use app\admin\service\RoleService;
use app\admin\model\User;
use app\admin\model\Role;

class Base extends Admin
{
   
	//系统配置
    function config(){
		if (!$this->request->isPost()){
			$info = db("config")->column('data','name');
			$this->view->assign('info',checkData($info,false));
			return view('base/config');
		}else{
			$data = $this->request->post();
			try{
				$info = db("config")->column('data','name');
				foreach ($data as $key => $value) {
					if(array_key_exists($key,$info)){
						db("config")->where(['name'=>$key])->update(['data'=>$value]);
					}else{
						db("config")->insert(['name'=>$key,'data'=>$value]);
					}
				}
			}catch(\Exception $e){
				abort(config('error_log_code'),$e->getMessage());
			}
            return json(['status'=>'00','msg'=>'修改成功']);
		}
	}
	
    //修改密码
    public function password(){
	    if (!$this->request->isPost()){	
			return view('password');
		}else{
			$password = $this->request->post('password','','trim');
			$data['user_id'] = session('admin.user_id');
			$data['pwd'] = md5($password.config('my.password_secrect'));
			try{
				User::update($data);
			}catch(\Exception $e){
				abort(config('error_log_code'),$e->getMessage());
			}
            return json(['status'=>'00','message'=>'修改成功']);
		}
    }
	
	
	public function auth(){
		if (!$this->request->isAjax()){
			$role_id = $this->request->get('role_id','','intval');
			$info = Role::find($role_id);
			$parentInfo = Role::find($info->pid);
			$parentAccess = db("access")->where('role_id',$parentInfo->role_id)->column('purviewval','id');	//父角色俱备的权限
			$access = db("access")->where('role_id',$role_id)->column('purviewval','id');	//当前角色的权限
			$nodes = $this->getNodes(0,$access,$parentAccess,$info);
			$cmsMenu = include app()->getRootPath().'/app/admin/controller/Cms/access.php';	//cms菜单配置
			$cmsNodes = $this->getCmsNodes($cmsMenu,$access,$parentAccess,$info);
			if($cmsNodes){
				$nodes = array_merge($cmsNodes,$nodes);
			}
			return view('auth',['nodes'=>json_encode($nodes)]);
		}else{
			$role_id = $this->request->post('role_id','','intval');
			$purval = $this->request->post('idx','','strval');
			db('access')->where('role_id',$role_id)->delete();
			foreach($purval as $val){
				$data = ['purviewval'=>$val,'role_id'=>$role_id];				
				db('access')->insert($data);								
			}
			return json(['status'=>'00','message'=>'设置成功']);
		}
	}
	
	public function getNodes($pid,$access,$parentAccess,$info){
		$list = db("menu")->where(['app_id'=>1,'pid'=>$pid])->order('sortid asc')->select()->toArray();
		if($list){
			foreach($list as $key=>$val){
				$selectStatus = false;
				$url = !empty($val['url']) ? $val['url'] :  'admin/'.str_replace('/','.',$val['controller_name']);
				if(in_array($url,$access)){
					$selectStatus = true;
				}
				if(in_array($url,$parentAccess) || empty($info['pid']) || $info['pid'] == 1){
					$menus[$key]['text'] = $val['title'].' '.$url;
					$menus[$key]['state'] = ['opened'=>true,'selected'=>$selectStatus];
					$menus[$key]['a_attr'] = ['data-id'=>$url];
					$sublist = db("menu")->where(['app_id'=>1,'pid'=>$val['menu_id']])->order('sortid asc')->select()->toArray();
					if($sublist){
						$menus[$key]['children'] = $this->getNodes($val['menu_id'],$access,$parentAccess,$info);
					}else{
						$funs = $this->getFuns($val,$access,$parentAccess);
						$funs && $menus[$key]['children'] = $funs;
					}
				}
			}
		}
		return array_values($menus);
	}
	
	
	public function getFuns($info,$access,$parentAccess){
		$list = db("action")->where('menu_id',$info['menu_id'])->order('sortid asc')->select();
		if($list){
			foreach($list as $key=>$val){
				$selectStatus = false;
				$url = $val['jump'] ? 'admin'.$val['jump'] : 'admin/'.str_replace('/','.',$info['controller_name']).'/'.$val['action_name'];
				if(in_array($url,$access)){
					$selectStatus = true;
				}
				if(in_array($url,$parentAccess) || !$parentAccess){
					$funs[$key]['text'] = $val['name'].' ('.$url.')';
					$funs[$key]['state'] = ['opened'=>true,'selected'=>$selectStatus];
					$funs[$key]['a_attr'] = ['data-id'=>$url];
					$funs[$key]['icon'] = !empty($val['bs_icon']) ? $val['bs_icon'] : 'fa fa-clone';
				}
			}
			return array_values($funs);
		}
	}
	
	public function getCmsNodes($cmsMenu,$access,$parentAccess,$info){
		foreach($cmsMenu as $key=>$val){
			$selectStatus = false;
			$url = $val['a_attr']['data-id'];
			if(in_array($val['a_attr']['data-id'],$access)){
				$selectStatus = true;
			}
			if(in_array($url,$parentAccess) || empty($info['pid']) || $info['pid'] == 1){
				$new[$key]['text'] = $val['text'];
				$new[$key]['a_attr'] = $val['a_attr'];
				$new[$key]['icon'] = $val['icon'];
				$new[$key]['state'] = ['opened'=>true,'selected'=>$selectStatus];
				if($val['children']){
					$new[$key]['children'] = $this->getCmsNodes($val['children'],$access,$parentAccess,$info);
				}
			}
		}
		return $new;
	}
	
	//清除缓存 出去session缓存
	public function clearData(){
		$dir = config('my.clear_cache_dir') ? app()->getRootPath().'/runtime/admin' : app()->getRootPath().'/runtime';
		$applicationInfo = db('application')->where('app_type',3)->find();
		try{
			deldir($dir) && $applicationInfo['app_dir'] && deldir(app()->getRootPath().'runtime/'.$applicationInfo['app_dir']);
		}catch(\Exception $e){
			return json(['status'=>'01','msg'=>$e->getMessage()]);
		}
		return json(['status'=>'00','msg'=>'删除成功']);
	}
	
	//字体图标选择器
    public function icon(){
        $field =input('param.field','','strval');
		$this->view->assign('field',$field);
		return view('icon');
    }
	
	

}
