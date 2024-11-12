<?php
/*
 module:		接口告警
 create_time:	2020-09-23 11:38:42
 author:		
 contact:		
*/

namespace app\api\controller;

use app\api\service\InterfaceErrorService;
use app\api\model\InterfaceError as InterfaceErrorModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

class InterfaceError extends Common
{


    /**
     * @api {post} /InterfaceError/add 01、报错写入
     * @apiGroup InterfaceError
     * @apiVersion 1.0.0
     * @apiDescription  创建数据
     * @apiParam (输入参数：) {int}            url 接口url
     * @apiParam (输入参数：) {int}            params 接口参数 json格式 eg token:54545454454,
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码  201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    function add()
    {
        $postField = 'url,params';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = InterfaceErrorService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }


}

