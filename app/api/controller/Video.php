<?php 
/*
 module:		会员聊天记录
 create_time:	2020-08-10 15:58:51
 author:		
 contact:		
*/

namespace app\api\controller;

use app\admin\model\FileStorage;
use app\api\service\ChatService;
use app\api\model\Chat as ChatModel;
use app\api\service\VideoCommentService;
use GatewayWorker\Lib\Gateway;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

class Video extends Common {


	/**
 * @api {get} /Video/index 01、视频数据
 * @apiGroup Video
 * @apiVersion 1.0.0
 * @apiDescription  首页数据列表
 * @apiParam (输入参数：) {int}     		[limit] 每页数据条数（默认20）
 * @apiParam (输入参数：) {int}     		[page] 当前页码
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
        $uid = $this->request->uid;

        $limit = $this->request->post('limit','20','intval');
        $page = $this->request->post('page','1','intval');
        $where =array();

        $where['isvideo'] = 1;
        $lists = FileStorage::loadData($uid,$page,$limit);
        return $this->ajaxReturn($this->successCode,'发送成功',$lists);
    }

    /**
     * @api {get} /Video/see 02、看视频
     * @apiGroup Video
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}     		[video_id] 视频id
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
    function see(){
        $uid = $this->request->uid;

        $video_id = $this->request->post('video_id','0','intval');
        if(empty($video_id)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        $data = array('member_id'=>$uid,'video_id'=>$video_id);
        $info = \app\api\model\VideoSee::getWhereInfo($data);
        if(empty($info))
        $res = \app\api\model\VideoSee::create($data);
        return $this->ajaxReturn($this->successCode,'发送成功',$res);
    }

    /**
     * @api {get} /Video/zan 03、视频点赞
     * @apiGroup Video
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {string}		[video_id] 视频编号

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
    function zan(){
        $uid = $this->request->uid;
        $video_id = $this->request->post('video_id','0','intval');
        if(empty($video_id)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        $where['id'] = $video_id;
        $where['isvideo'] = 1;
        $info = $info = FileStorage::getWhereInfo($where);
        if(empty($info)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        $where = array('member_id'=>$uid,'videoid'=>$video_id);
        $info = \app\api\model\VideoZan::getWhereInfo($where);
        $status = 1;
        if(empty($info)){
            $data= $where;
            $data['addtime'] = time();
            $res = \app\api\model\VideoZan::create($data);
            if($res){
                $where = array();
                $where['id'] = $video_id;
               \xhadmin\db\FileStorage::setInc($where,'likecount',1);
            }
        }else{
            $res = \app\api\model\VideoZan::delete1($where);
            $where = array();
            $where['id'] = $video_id;
            \xhadmin\db\FileStorage::setDec($where,'likecount',1);
            $status = 2;
        }
        return $this->ajaxReturn($this->successCode,$status==1?'点赞成功':'取消点赞',$status);

    }
    /**
     * @api {get} /Video/comment 04、视频评论
     * @apiGroup Video
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {string}     		[content] 内容
     * @apiParam (输入参数：) {int}		[video_id] 视频编号

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
    function comment(){
        $uid = $this->request->uid;
        $video_id = $this->request->post('video_id','0','intval');
        if(empty($video_id)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        $content = $this->request->post('content','');
        $data = array('member_id'=>$uid,'videoid'=>$video_id,'content'=>$content,'addtime'=>time());
        $res = \app\api\model\VideoComment::create($data);
        if($res){
            $where = array();
            $where['id'] = $video_id;
            \xhadmin\db\FileStorage::setInc($where,'commentcount',1);
        }
        return $this->ajaxReturn($this->successCode,'评论成功');

    }

    /**
     * @api {get} /Video/comment_list 05、视频评论列表
     * @apiGroup Video
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}     		[limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}     		[page] 当前页码
     * @apiParam (输入参数：) {string}		[video_id] 视频编号

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
    function comment_list(){
        $uid = $this->request->uid;
        $limit  = $this->request->post('limit', 20, 'intval');
        $page   = $this->request->post('page', 1, 'intval');
        $videoid   = $this->request->post('video_id', 1, 'intval');
        $where = [];
        $where['videoid'] = $videoid;

        $field = 'a.*,b.nickname,b.avatar,b.ypid';
        $orderby = 'a.id desc';
        $res = VideoCommentService::loadData(formatWhere($where),$field,$orderby,$limit,$page);

        return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($res));
    }


}

