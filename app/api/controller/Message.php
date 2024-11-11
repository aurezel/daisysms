<?php 
/*
 module:		消息管理
 create_time:	2020-07-25 19:46:57
 author:		
 contact:		
*/

namespace app\api\controller;

use app\admin\service\OfflinemessageService;
use app\api\service\CustomerServiceService;
use app\api\service\MessageService;
use app\api\model\Message as MessageModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;
use xhadmin\db\OfflineMessage;
use xhadmin\service\api\RedisService;

class Message extends Common {


	/**
	* @api {get} /Message/index 01、首页数据列表
	* @apiGroup Message
	* @apiVersion 1.0.0
	* @apiDescription  首页数据列表

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.data 返回数据
	* @apiParam (成功返回参数：) {string}     	array.data.list 返回数据列表
	* @apiParam (成功返回参数：) {string}     	array.data.count 返回数据总数
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","data":""}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"查询失败"}
	*/
	function index(){
        $ufrom = $uid = $this->request->uid;
        $info = \app\api\model\Member::find($uid);
        $limit  = $this->request->get('limit', 20, 'intval');
        $page   = $this->request->get('page', 1, 'intval');

        $where = [];

        $field = '*';
        $orderby = 'id desc';


        if(empty($info['customerid'])){

            //没有专属客服
            $where = [];
            $lists = CustomerServiceService::indexList(formatWhere($where),$field,$orderby,$limit,$page);

            $rows = $lists['list'];

            $mid = uuid();
            foreach ($rows as $key=>$v){
                $message_type = $v['type'];
                $channel1 = "user-{$ufrom}" ;
                $data    = [
                    'id'=>$mid.$channel1,
                    'from_id'        => '',
                    'from_name'   =>  '艾米',
                    'from_avatar' => '/static/images/kf.png',
                    'to_id'          => $info['ypid'],
                    'to_name'     => $info['nickname'],
                    'to_avatar'   => $info['avatar'],
                    'data'     => $v['title'],
                    'create_time'   => time(),
                    'chat_type'        => 'friend',
                    'type'    => $message_type,
                    'options'=>'',
                    'isremove'=>0,
                    'sendStatus'=>'success'
                ];

                if (!empty($post['uniqueId'])) {
                    $data['uniqueId'] = $post['uniqueId'];
                }

                $data['channel'] = $channel1;

                \app\api\service\RedisService::add($info['member_id'],$data,'friend');
                $arr = array('event'=>'message','data'=>$data);

                sendUid($info['member_id'],$arr);


            }

        }
        return $this->ajaxReturn($this->successCode,'操作成功');

	}

	/**
	* @api {post} /Message/add 02、添加
	* @apiGroup Message
	* @apiVersion 1.0.0
	* @apiDescription  添加
	* @apiParam (输入参数：) {string}			uto 接受者id 
	* @apiParam (输入参数：) {string}			toname 接受者名称 
	* @apiParam (输入参数：) {string}			ufrom 发起者uid 
	* @apiParam (输入参数：) {string}			fromname 发起者名称 
	* @apiParam (输入参数：) {string}			content 内容 
	* @apiParam (输入参数：) {string}			type 类型 
	* @apiParam (输入参数：) {int}				sub_type 类型2 消息|message|primary,通知|notice|info
	* @apiParam (输入参数：) {string}			timestamp 添加时间 
	* @apiParam (输入参数：) {int}				message_type 消息类型 文本|1|success,图片|2|danger,表情|3|danger,语音|4|danger
	* @apiParam (输入参数：) {string}			options options 
	* @apiParam (输入参数：) {int}				isread isread 已读|1,未读|0

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码  201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","data":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"操作失败"}
	*/
	function add(){
		$postField = 'uto,toname,ufrom,fromname,content,type,sub_type,timestamp,message_type,options,isread';
		$data = $this->request->only(explode(',',$postField),'post',null);
		$res = MessageService::add($data);
		return $this->ajaxReturn($this->successCode,'操作成功',$res);
	}

	/**
	* @api {post} /Message/update 03、修改
	* @apiGroup Message
	* @apiVersion 1.0.0
	* @apiDescription  修改
	
	* @apiParam (输入参数：) {string}     		mid 主键ID (必填)
	* @apiParam (输入参数：) {string}			uto 接受者id 
	* @apiParam (输入参数：) {string}			toname 接受者名称 
	* @apiParam (输入参数：) {string}			ufrom 发起者uid 
	* @apiParam (输入参数：) {string}			fromname 发起者名称 
	* @apiParam (输入参数：) {string}			content 内容 
	* @apiParam (输入参数：) {string}			type 类型 
	* @apiParam (输入参数：) {int}				sub_type 类型2 消息|message|primary,通知|notice|info
	* @apiParam (输入参数：) {string}			timestamp 添加时间 
	* @apiParam (输入参数：) {int}				message_type 消息类型 文本|1|success,图片|2|danger,表情|3|danger,语音|4|danger
	* @apiParam (输入参数：) {string}			options options 
	* @apiParam (输入参数：) {int}				isread isread 已读|1,未读|0

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码  201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","msg":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"操作失败"}
	*/
	function update(){
		$postField = 'mid,uto,toname,ufrom,fromname,content,type,sub_type,timestamp,message_type,options,isread';
		$data = $this->request->only(explode(',',$postField),'post',null);
		if(empty($data['mid'])){
			throw new ValidateException('参数错误');
		}
		$where['mid'] = $data['mid'];
		$res = MessageService::update($where,$data);
		return $this->ajaxReturn($this->successCode,'操作成功');
	}

	/**
	* @api {post} /Message/delete 04、删除
	* @apiGroup Message
	* @apiVersion 1.0.0
	* @apiDescription  删除
	* @apiParam (输入参数：) {string}     		mids 主键id 注意后面跟了s 多数据删除

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","msg":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":"201","msg":"操作失败"}
	*/
	function delete(){
		$idx =  $this->request->post('mids', '', 'serach_in');
		if(empty($idx)){
			throw new ValidateException('参数错误');
		}
		$data['mid'] = explode(',',$idx);
		try{
			MessageModel::destroy($data);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $this->ajaxReturn($this->successCode,'操作成功');
	}

	/**
	* @api {get} /Message/view 05、查看详情
	* @apiGroup Message
	* @apiVersion 1.0.0
	* @apiDescription  查看详情
	
	* @apiParam (输入参数：) {string}     		mid 主键ID

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.data 返回数据详情
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","data":""}
	* @apiErrorExample {json} 02 失败示例
	* {"status":"201","msg":"没有数据"}
	*/
	function view(){
		$data['mid'] = $this->request->get('mid','','serach_in');
		$field='mid,ufrom,uto,content,type,sub_type,timestamp,message_type,options,isread';
		$res  = checkData(MessageModel::field($field)->where($data)->find());
		return $this->ajaxReturn($this->successCode,'返回成功',$res);
	}

    public function lixian(){
        $uid = $this->request->uid;
        $messagelist = OfflinemessageService::groupbymessage($uid);

        if(!empty($messagelist)){
            foreach ($messagelist as $key=>$v){
                if(!empty($v['options']))
                    $v['options'] = \Qiniu\json_decode($v['options']);
                $arr = array('event'=>'lixian','data'=>$v);
                sendUid($v['uid'],$arr);
            }
        }
        $cid  = $this->request->post('cid','','');//组的话就是组id，好友的话就是好友的约炮id
        $platform  = $this->request->post('platform','','');//组的话就是组id，好友的话就是好友的约炮id
        $where = array();
        $where[] = ['cid','=',$cid];
        $where[] = ['platform','=',$platform];
        $data = array('cid'=>'','platform'=>'');
        \app\api\model\Member::update($data,$where);
        $where = array();
        $where[] = ['member_id','=',$uid];
        $data = array('cid'=>$cid,'platform'=>$platform);
        \app\api\model\Member::update($data,$where);

        return success('离线消息获取成功',$messagelist);
    }

    public function lixianbyid(){
        $uid = $this->request->uid;
        $to_id  = $this->request->post('to_id','','');//组的话就是组id，好友的话就是好友的约炮id
        $limit  = $this->request->post('pagesize', 20, 'intval');
        $page   = $this->request->post('page', 1, 'intval');
        $type  = $this->request->post('type', '');
        $maxid  = $this->request->post('maxid', '0','intval');
        if(empty($type)){
            return error('参数错误');
        }
        if(empty($to_id)){
            return error('100');
        }
        if($type=='friend'){
            $where = array();

            $where['ypid'] = $to_id;
            $info = \app\api\model\Member::getWhereInfo($where,'member_id');

        }else if($type=='group'){
            $to_id = intval($to_id);
            $info = \xhadmin\db\Groups::getInfo($to_id);
        }

        if(empty($info)){
            return error('参数错误');
        }
        $where = array();
        $where['uid'] = $uid;
        if($type=='friend')
            $where['from_id'] = $to_id;
        else
            $where['to_id'] = $to_id;
        $order = 'create_time desc';
        $messagelist = OfflinemessageService::indexList(formatWhere($where),'*',$order,$limit,$page);//::loadList($where,$limit,'*','create_time desc');
        $minid = 0;

            foreach ($messagelist['rows'] as $key=>&$v){
                $minid = $v['autoid'];
                if(!empty($v['options']))
                $v['options'] = \Qiniu\json_decode($v['options']);
            }

        $messagelist['minid']= $minid;



        if($page>1&&$maxid>0){
            $where['autoid'] = ['>=',$maxid];
            $tempWhere = $where;
            $tempWhere['chat_type'] = ['=','friend'];

            $list = OfflinemessageService::indexList(formatWhere($tempWhere),'*',$order,$limit,$page);

            $memberid = 0;
            $data = array();
            foreach ($list['rows'] as $key=>$v){
                $memberid = $v['member_id'];
                $data[] = $v;

            }
            sendUid($memberid,(array('event'=>'dealmessage','data'=>$data)));
            \app\api\model\Offlinemessage::delete1(($tempWhere));


        }
        return success('离线消息获取成功',$messagelist);
    }

    public function sendAll(){
        $uid = $this->request->uid;
        $memberinfo = \app\api\model\Member::find($uid);
        $to_id  = $this->request->post('to_id');//组的话就是组id，好友的话就是好友的约炮id
        $where = array();
        $where['ypid'] = $to_id;
        $toInfo = \app\api\model\Member::getWhereInfo($where,'member_id');

        sendUid($toInfo['member_id'],(array('event'=>'dealallmessage','data'=>array('chat_type'=>'friend','to_id'=>$memberinfo['ypid']))));
    }

    public function check(){
        $uid = $this->request->uid;
        $data  = $this->request->post('data');//组的话就是组id，好友的话就是好友的约炮id

        $to = $data['to_id'];
        $id = $data['id'];
        $chat_type = 'friend';
        $maxid = isset($data['maxid'])?intval($data['maxid']):0;
        \app\api\service\RedisService::remove($uid,$chat_type,$id,$maxid);
        $where = array();
        $where['ypid'] = $data['from_id'];

        $info = \app\api\model\Member::getWhereInfo($where,'member_id');//r
        //sendUid($info['member_id'],(array('event'=>'dealmessage','data'=>$data)));
        return success('离线消息处理成功');
    }

    public function noticesee(){
        $uid = $this->request->uid;
        $data  = $this->request->post('data');//组的话就是组id，好友的话就是好友的约炮id
        $where = array();
        $where['ypid'] = $data['from_id'];
        $info = \app\api\model\Member::getWhereInfo($where,'member_id');//r
        sendUid($info['member_id'],(array('event'=>'dealmessage','data'=>array($data))));
    }

}

