<?php
namespace app\admin\controller;

class Index extends Admin
{
	
	//框架主体
    public function index(){
        $menu = $this->test();
		$cmsMenu = include app()->getRootPath().'/app/admin/controller/Cms/config.php';	//cms菜单配置
		if($cmsMenu){
			$menu = array_merge($cmsMenu,$menu);
		}
		$this->view->assign('menus',$menu);
		return view('index');
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
