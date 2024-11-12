<?php
/*
 module:		会员管理
 create_time:	2020-08-09 18:28:55
 author:		
 contact:		
*/

namespace app\api\controller;

use app\admin\model\CustomerConsultation;
use app\admin\model\Syarticle;
use app\admin\service\CustomerConsultationService;
use app\admin\service\PiclistService;
use app\admin\service\SyarticleService;
use app\api\service\MemberFindMethodService;
use think\db\Where;
use think\facade\App;



class Aboutus extends Common
{

    public function index()
    {
        $title = '';
        $content = '';
        $arr = ['status' => config('my.successCode'), 'msg' => 操作成功];
        $arr['title'] = $title;
        $arr['data'] = $content;
        return $this->reDecodejson($arr);
    }

    public function server()
    {
        $title = '用户协议';
        $content = '';
        $arr = ['status' => config('my.successCode'), 'msg' => 操作成功];
        $arr['title'] = $title;
        $arr['data'] = $content;
        return $this->reDecodejson($arr);
    }

    public function content()
    {

        $title = '';
        $content1 = $content2='';
        $arr = ['status' => config('my.successCode'), 'msg' => 操作成功];
        $arr['title'] = $title;
        $arr['server'] = $content1;
        $arr['secret'] = $content2;
        return $this->reDecodejson($arr);


    }

    public  function sercret(){
        $content2 ='';
        echo nl2br($content2);
    }

//取得帮助信息列表
    function getHelpList(){
        $limit  = $this->request->post('limit', 20, 'intval');
        $page   = $this->request->post('page', 1, 'intval');

        $where = [];
        $type = $this->request->post('type', '', 'intval');//联系我们|1|success,系统帮助|2|primary,会员疑问|3|warning,开发者疑问|4|danger
        $isindex = $this->request->post('isindex', '', 'intval');
        if(($type!='')&& in_array($type,array(1,2,3,4))){
            $where['type'] = $type;
        }
        if(($isindex!='')&& in_array($isindex,array(1,0))){
            $where['isindex'] = $isindex;
        }
        $field = '*';
        $orderby = 'sortid asc';
        $res = SyarticleService::indexList(formatWhere($where),$field,$orderby,$limit,$page);
        return $this->ajaxReturn($this->successCode,'返回成功',$res);
    }


    function getHelpInfo(){
        $id = $this->request->post('id','');
        $type = $this->request->post('type','1','intval');
        if($type==1){
            $id = intval($id);
            $data['id'] = $id;
        }else{
            $data['keytype'] = $id;
        }
        $field='*';
        $res  = checkData(Syarticle::field($field)->where($data)->find());
        if(!empty($res)){
            $res['content'] = htmlspecialchars_decode($res['content']);
        }else{
            return $this->reDecodejson(['status' => $this->errorCode, 'msg' => '未找到相关数据']);
        }
        return $this->ajaxReturn($this->successCode,'返回成功',$res);
    }

//取得大神栏目
    public function getpiclist(){
        $lan = $this->request->post('lan', 'zh');
        $limit  = $this->request->post('limit', 3, 'intval');
        $page   = $this->request->post('page', 1, 'intval');
        $where = [];
        $field = '*';
        $orderby = 'paixu asc';
        $res = PiclistService::indexList(formatWhere($where),$field,$orderby,$limit,$page);

        return $this->ajaxReturn($this->successCode,'返回成功',$res);
    }

    //
    public function customerList(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $lan = $this->request->post('lan', 'zh');
        $limit  = $this->request->post('limit', 3, 'intval');
        $page   = $this->request->post('page', 1, 'intval');
        $where = [];
        $where['member_id']=$member_id;
        $field = '*';
        $orderby = 'id asc';
        $res = CustomerConsultationService::indexList(formatWhere($where),$field,$orderby,$limit,$page);

        return $this->ajaxReturn($this->successCode,'返回成功',$res);
    }


    //
    public function Adcustomer(){
        $member_id = $this->request->uid;
        $content = $this->request->post('content');
        $contact  = $this->request->post('contact');
        $type   = $this->request->post('type', 1, 'intval');
        if(empty($content)&&empty($contact)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'error']);
        }
        $ad['member_id'] = intval($member_id);
        $ad['content']=$content;
        $ad['member_name']=$contact;
        $ad['type']=$type;
        $ad['add_time']=time();
        CustomerConsultation::create($ad);
        return $this->ajaxReturn($this->successCode,'返回成功');
    }

    //
    public function Adcustomernologin(){
        $content = $this->request->post('content');
        $contact  = $this->request->post('contact');
        $type   = $this->request->post('type', 1, 'intval');
        if(empty($content)&&empty($contact)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'error']);
        }
        $ad['member_id'] = 0;
        $ad['content']=$content;
        $ad['member_name']=$contact;
        $ad['type']=$type;
        $ad['add_time']=time();
        CustomerConsultation::create($ad);
        return $this->ajaxReturn($this->successCode,'返回成功');
    }
    //
    public function getcustomerinfo(){
        $member_id = $this->request->uid;
        if(empty($member_id)){
            return $this->reDecodejson(['status'=>$this->errorCode,'msg'=>'token过期']);
        }
        $cid   = $this->request->post('cid', 0, 'intval');
        $where=[];
        $where['member_id'] = $member_id;
        $where['id'] = $cid;
        $ageninfo = CustomerConsultation::Where($where)->field('*')->find();
        return $this->ajaxReturn($this->successCode,'返回成功',$ageninfo);
    }

}

