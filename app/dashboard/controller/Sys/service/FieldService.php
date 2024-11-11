<?php

namespace app\admin\controller\Sys\service;
use app\admin\controller\Sys\model\Menu;
use app\admin\controller\Sys\model\Field;
use think\facade\Validate;
use xhadmin\CommonService;


class FieldService extends CommonService
{

	 /*
     * @Description  添加或修改信息
	 * @param (输入参数：)  {string}        type 操作类型 add 添加 update修改
     * @param (输入参数：)  {array}         data 原始数据
     * @return (返回参数：) {bool}        
	 */
	public static function saveData($type,$data){
		
		try{
			//调用验证器
			$rule = [
				'name'  => 'require',
				'field' => 'require',
				'type' => 'require'
			];
			
			$msg = [
				'name.require'  => '字段名称必填',
				'field.require'  => '字段必填',
				'type.require'=>'字段类型必填',
			];
			
			$validate = Validate::rule($rule)->message($msg);	
			if (!$validate->check($data)) {
				throw new ValidateException($validate->getError());
			}
			$field_letter_status = !is_null(config('my.field_letter_status')) ? config('my.field_letter_status') : true;
			if($field_letter_status){
				$data['field'] = strtolower(trim($data['field'])); //字段强制小写
			}

			if($type == 'add'){
				$info = Field::where(['menu_id'=>$data['menu_id'],'field'=>$data['field']])->find();
				$reset = Field::create($data);	//创建操作字段
				if($reset->id){
					Field::update(['id'=>$reset->id,'sortid'=>$reset->id]); //更新排序
				}		
			}elseif($type == 'edit'){
				$res = Field::update($data);
				if($res){
					$fieldInfo = Field::find($data['id']);
					if($data['name'] == '编号' && $data['field'] <> $fieldInfo['field']){
						Menu::update(['pk_id'=>$data['field'],'menu_id'=>$fieldInfo['menu_id']]);
					}
				}
			}
		}catch(\Exception $e){
			throw new \Exception($e->getMessage());
		}
			
		return $reset;
	}
	
	
	/**
     * 移动排序
	 * @param (输入参数：)  {string}        id 当前ID
     * @param (输入参数：)  {string}        type 类型 1上移 2 下移
     * @return (返回参数：) {bool}        
     * @return bool 信息
     */
	public static function arrowsort($id,$type){
		$data = Field::find($id);

		if($type == 1){
			$map = 'sortid < '.$data['sortid'].' and menu_id = '.$data['menu_id'];
			$info = Field::where($map)->order('sortid desc')->find();
		}else{
			$map = 'sortid > '.$data['sortid'].' and menu_id = '.$data['menu_id'];
			$info = Field::where($map)->order('sortid asc')->find();
		}
		try{
			if($info && $data){
				Field::update(['id'=>$id,'sortid'=>$info['sortid']]);
				Field::update(['id'=>$info['id'],'sortid'=>$data['sortid']]);
			}else{
				throw new \Exception('目标位置没有数据');
			}
		}catch(\Exception $e){
			throw new \Exception($e->getMessage());
		}
		 return true;
		
	}

    
}
