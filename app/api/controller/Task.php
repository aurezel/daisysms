<?php 
/*
 module:		通讯录导入
 create_time:	2020-09-02 15:23:11
 author:		
 contact:		
*/

namespace app\api\controller;

use app\api\service\AddressListService;
use app\api\model\AddressList as AddressListModel;
use app\api\service\MemberService;
use org\Pinyin;
use OSS\Core\OssUtil;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;
use xhadmin\db\Tasklist;

class Task extends Common {
    protected  $base_url = 'http://task.expertcp.com';
    protected  $appkey = '26d55873efe9e9cb4f74523a13a104d1';
    protected  $appSecrect = '444e534030d45ab4215695c8bbea7da1382cfcbb41ae0a70fcd8f629cbbff984';

    /**
     * @api {post} /Task/token 01、生成任务token
     * @apiGroup Task
     * @apiVersion 1.0.0
     * @apiDescription  生成任务token
     * @apiParam (输入参数：) {string}     		[username] 会员名称membername
     * @apiSuccessExample {json} 01 成功示例
     * {
    "status": "200",
    "msg": "获取成功",
    "data": {
    "membername": "test1",
    "access_token": "eyJhbGciOiJIUzI1NiIsInppcCI6IkdaSVAifQ.H4sIAAAAAAAAAD2OSwuCQBSF_8tdhsJcH-OMu8CFIhSJYDu5OQNpZoNaJNF_b0To7M6Dj_OBbm4hBiIKheLKDSSii6gvrlRR4zKG3NfIlJIBONDSDDFyJpF7YSQdIGNuerGA86kmEeSvvmMyN6PdNlcaBt2Xi9G2L9OsSOrDsayrrEyTYl_ZiX6bdqS5fQwb1QuEjOwB4cBz0mOmIBbWcf6nrRH6uPUD3Vf0rKedFXx_FALfdc0AAAA.9M0wmxpY8k2Ns0EWK_Z9DLRSoS_ar5oUzyO3OeW91w4",
    "refresh_token": "3bd974012bba3204b52eb0d10a561b3c152c6ae2383aac5de146436f433cb2c3",
    "expires_in": 86400,
    "updatetime": 1609162578
    }
    }
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    public function token(){
        $username  = $this->request->post('username', 0);

        if(empty($username)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        $path = '/openapi/v1.server/token';
        $url = $this->base_url.$path;
        $data = array(
            'appkey'=>$this->appkey,
            't'=>time(),
            'username'=>$username,
        );
        $sign = $this->createsign($data,$path);

        $data['sign'] = $sign;

        $result  = curl_post($url,$data);
        return $result;
        return $this->ajaxReturn($this->errorCode,'参数错误',$result);
        $result = \Qiniu\json_decode($result,TRUE);
        return $result;
        $result = \Qiniu\json_decode($result);
        return $result;
        //return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($result));
    }

    private function createsign($data,$path){
        $paramskey = http_build_query($data);
        $paramskey.='&appSecret='.$this->appSecrect;

        $paramskey = md5($path.'?'.$paramskey);
        return $paramskey;
    }

    /**
     * @api {post} /Task/lists 02、任务列表
     * @apiGroup Task
     * @apiVersion 1.0.0
     * @apiDescription  返回任务列表
     * @apiSuccessExample {json} 01 成功示例
     * {
    "status": "200",
    "msg": "获取成功",
    "data": [
    {
    "id": 46,
    "otherid": 107,
    "logo_addr": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/logo/20200904134439643.jpg",
    "name": "淘宝特价版",
    "exp": "淘宝下单立减五元",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 513,
    "amount": 410,
    "last_task_count": 241,
    "total": 800,
    "stop_time": 1635572665000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 180,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 108,
    "cstcreate": 1599198337074,
    "cstupdate": 1609203580098,
    "task_steps": [
    {
    "title": "浏览器扫码输入手机号（手机号必须是淘宝关联的手机号）—获取红包下载淘宝特价版 打开APP淘宝一键登陆— 获取后点击去使用  —弹出来一个5元和36元新人礼券点击收下红包立即使用截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926005647752.png?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1598327347338"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926005649763.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "领到的5元红包是无抵扣红包，在新人专享页面用红包购买，真实发货实际支付1毛钱 （想买好一点的也可以多支付一点  ，次日记得去点击下确认收货 还有一个36元的红包 ，首次购物之后就可以使用买东西直接抵扣",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926005702717.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926005705093.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交手机号+订单号",
    "taskStepItemList": [],
    "form": {
    "id": 6264,
    "label": "手机号订单号",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交新人专区红包截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201020171253014.png"
    }
    ],
    "form": {
    "id": 6265,
    "label": "提交新人专区红包截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交我的截图5元红包必须使用",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926010236531.jpg"
    }
    ],
    "form": {
    "id": 6266,
    "label": "提交我的截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交订单详情截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201103150820227.jpg"
    }
    ],
    "form": {
    "id": 6267,
    "label": "提交订单详情截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 44,
    "otherid": 25,
    "logo_addr": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/logo/20200810184944903.jpg",
    "name": "滴滴学生认证",
    "exp": "滴滴打车券海量领",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 558,
    "amount": 446,
    "last_task_count": 378,
    "total": 800,
    "stop_time": 1635677343000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 107,
    "cstcreate": 1597056590000,
    "cstupdate": 1609201606398,
    "task_steps": [
    {
    "title": "微信或者滴滴app扫码，扫描二维码进入扫码后直接进入“学生中心”认证页面（如中途退出请重新扫码）提示：未登陆状态，需要先登陆滴滴账户（手机需打开定位，允许定位，非定位会判定无效数据）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201105231923233.png?qrtext=https%3A%2F%2Fv.didi.cn%2Fv%2Fe6RwE%3Ftoggleext%3Dbt8ahtb4bhn9g44i38l0"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208172946631.png"
    }
    ],
    "form": null
    },
    {
    "title": "填写信息点击同意“填写完学校后，会出现一个提示当前位置个地址不一致这个，点击确定就可以了），提交（校园用户可立即审核通过；认证成功，享受学生特权）（隐私信息可以部分打码）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208172959194.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208173001165.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6268,
    "label": "提交手机号",
    "type": "PHONE",
    "orderId": 0
    }
    },
    {
    "title": "提交：填写信息页面截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208173018264.png"
    }
    ],
    "form": {
    "id": 6269,
    "label": "提交填写信息页面截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交认证成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208173029207.jpg"
    }
    ],
    "form": {
    "id": 6270,
    "label": "提交认证成功截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 48,
    "otherid": 116,
    "logo_addr": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/logo/20200912182637021.jpg",
    "name": "招商银行",
    "exp": "招商银行最高价版",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 1458,
    "amount": 1166,
    "last_task_count": 249,
    "total": 500,
    "stop_time": 1635668778000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 104,
    "cstcreate": 1599906405044,
    "cstupdate": 1609203777915,
    "task_steps": [
    {
    "title": "微信扫描二维码，填写手机号，获取验证码，下载招商银行 app",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925234236211.png?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1599468919787"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925234238182.png"
    }
    ],
    "form": null
    },
    {
    "title": "打开下载好的 app，填写登录手机号，完成注册流程",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925234246143.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "进入首页，点击银行卡，绑定银行卡即可（当天不要解绑银行卡）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208182805348.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208182807414.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208182809361.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交个人中心截图银行卡数必须为一",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208182817077.jpg"
    }
    ],
    "form": {
    "id": 6271,
    "label": "个人中心截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交短信截图（您已成功注册一网通）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208182824709.jpg"
    }
    ],
    "form": {
    "id": 6272,
    "label": "提交短信截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交姓名手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6273,
    "label": "提交姓名手机号",
    "type": "TEXT",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 53,
    "otherid": 152,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201010200948091.jpg",
    "name": "一淘福利单",
    "exp": "一淘一分钱白捡50口罩",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 720,
    "amount": 576,
    "last_task_count": 116,
    "total": 500,
    "stop_time": 1635682023000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 86,
    "cstcreate": 1602331789447,
    "cstupdate": 1609205117132,
    "task_steps": [
    {
    "title": "应用商店下载一淘App-打开一淘App-点击立即领取-点击登录领取",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201221151147947.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201221151149720.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201221151151711.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "填入邀请码： 764CG（字母大写）领取红包-新人尊享里点开口罩-购买50个口罩",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201224173923868.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201224173925843.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交一淘昵称",
    "taskStepItemList": [],
    "form": {
    "id": 6274,
    "label": "提交一淘昵称",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交订单详情截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201221151515759.png"
    }
    ],
    "form": {
    "id": 6275,
    "label": "订单详情截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交全部订单截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201221151528132.png"
    }
    ],
    "form": {
    "id": 6276,
    "label": "提交全部订单截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 59,
    "otherid": 183,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201019163842631.png",
    "name": "南京银行小程序",
    "exp": "只限首次进入小程序的新用户",
    "type": "APP_SHI_WAN",
    "award_type": 1,
    "channel_amount": 45,
    "amount": 36,
    "last_task_count": 258,
    "total": 607,
    "stop_time": 1635669453000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 85,
    "cstcreate": 1603096723133,
    "cstupdate": 1609168146493,
    "task_steps": [
    {
    "title": "保存二维码后-打开微信扫描保存的二维码进入确认-允许",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209133127753.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209133129758.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209133131996.png"
    }
    ],
    "form": null
    },
    {
    "title": "首次进入的新用户都有2500的小鑫星，没有的话就是做过的老用户了，然后我的页面截图即可，不要打马赛克。【获得小鑫星时间和提交任务时间间隔不要超过10分钟，超过不算，所以请尽快提交】",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209133142941.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209133144900.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交微信昵称手机号码",
    "taskStepItemList": [],
    "form": {
    "id": 6277,
    "label": "提交微信昵称手机号码",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交我的页面截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209133150202.png"
    }
    ],
    "form": {
    "id": 6278,
    "label": "提交我的页面截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交领取成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209133156164.jpg"
    }
    ],
    "form": {
    "id": 6279,
    "label": "提交领取成功截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 42,
    "otherid": 16,
    "logo_addr": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/logo/20200702234807381.jpg",
    "name": "云闪付新用户",
    "exp": "云闪付天天优惠券",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 2052,
    "amount": 1641,
    "last_task_count": 179,
    "total": 500,
    "stop_time": 1635695216000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 72,
    "cstcreate": 1593704890000,
    "cstupdate": 1609204171895,
    "task_steps": [
    {
    "title": "微信扫描二维码，输入手机号，根据页面提示跳转，下载安装云闪付APP（若显示为老朋友就不要继续操作了）  特别注意：如弹出的下载提示需跳转应用宝下载，请直接选下载界面下面一行小字普通下载，",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925183849839.png?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1596526237822"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208163447153.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208163448177.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "打开APP，点击右下角我的，使用刚刚的手机号码进行新用户注册登录后，完成实名认证",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925183922364.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925183924962.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "绑定银行卡",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925183949138.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925183952415.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925183955355.png"
    }
    ],
    "form": null
    },
    {
    "title": "绑定完成后，保存下面收款码，点击云闪付APP首页扫一扫，进行支付0.1或0.01，支付成功记得保存截图重要提示：1.过程中不可使用微信或者支付宝扫码支付，必须使用银联APP扫一扫后银行卡付款",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201217094811272.jpg?qrtext=https%3A%2F%2Fqr.95516.com%2F00010002%2F01211317828088117100280848634951"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201217094813320.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交姓名手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6280,
    "label": "提交姓名手机号",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交支付成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208163348726.jpg"
    }
    ],
    "form": {
    "id": 6281,
    "label": "提交支付成功截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交参与成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208163356204.jpg"
    }
    ],
    "form": {
    "id": 6282,
    "label": "提交参与成功截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 45,
    "otherid": 26,
    "logo_addr": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/logo/20200810195114145.jpg",
    "name": "乐花卡",
    "exp": "乐花卡一分钟出额得30话费",
    "type": "GAO_E_JIE_TU",
    "award_type": 1,
    "channel_amount": 3420,
    "amount": 2736,
    "last_task_count": 540,
    "total": 600,
    "stop_time": 1635681089000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 180,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 70,
    "cstcreate": 1597060300000,
    "cstupdate": 1609173401724,
    "task_steps": [
    {
    "title": "扫码填写手机号领取，进入填写资料界面填信息获取授信额度（截图保存、不需要填写推荐码）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926010859025.png?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1596006413053"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201103125205324.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201103125403557.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "扫码激活乐花卡（截图保存）-绑定乐花卡至微信钱包",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926010912988.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201015033851833.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201015033855781.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "1元充值30元话费",
    "taskStepItemList": [
    {
    "type": "TEXT",
    "label": "",
    "value": "点击微信钱包—点击支付-充值30元话费—支付选择百信银行支付，满30元立减29元，实际支付一元钱-截图保存账单明细话费券充值订单公众号会推送消息，具体账单明细可在“分期乐”公众号菜单栏“服务”-“查订单”进行查看"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201015033914326.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201015034315428.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交姓名+手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6283,
    "label": "提交姓名手机号",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "认证成功截图（四个必须都认证）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104202943698.jpg"
    }
    ],
    "form": {
    "id": 6284,
    "label": "认证成功截图四个必须都认证",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交激活成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926011110576.png"
    }
    ],
    "form": {
    "id": 6285,
    "label": "提交激活成功截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交分期乐交易提醒截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926011128397.png"
    }
    ],
    "form": {
    "id": 6286,
    "label": "提交分期乐交易提醒截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 43,
    "otherid": 21,
    "logo_addr": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/logo/20200703003450180.jpg",
    "name": "掌上生活新用户",
    "exp": "免费领特价电影券",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 1494,
    "amount": 1195,
    "last_task_count": 175,
    "total": 500,
    "stop_time": 1635611667000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1593707704000,
    "cstupdate": 1609199739497,
    "task_steps": [
    {
    "title": "扫描下方二维码，填写手机号",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926002115219.jpg?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1597994426456"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201102144858710.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201102144903434.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "点击查看礼品，下载掌上生活 APP",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926002345543.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926002348084.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "打开掌上生活 APP，用刚才的手机号注册",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208172246410.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208172248641.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208172250534.png"
    }
    ],
    "form": null
    },
    {
    "title": "绑定一张银行卡/信用卡（可以绑定的卡可点击蓝色标志查看）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926002407595.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926002409849.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208172330357.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交姓名手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6365,
    "label": "提交手机号姓名",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交：账户领取红包页面截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201102144924674.jpg"
    }
    ],
    "form": {
    "id": 6366,
    "label": "账户领取红包页面截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交：我的优惠券截图（一定要有9元看电影）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201102145222856.png"
    }
    ],
    "form": {
    "id": 6367,
    "label": "我的优惠券截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交：绑定成功短信截图(必须要看到时间和卡片验证短信)",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201024103654255.png"
    }
    ],
    "form": {
    "id": 6368,
    "label": "绑定成功短信截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 47,
    "otherid": 114,
    "logo_addr": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/logo/20200912181408467.jpg",
    "name": "东呈小程序",
    "exp": "仅限东呈新用户",
    "type": "APP_SHI_WAN",
    "award_type": 1,
    "channel_amount": 27,
    "amount": 21,
    "last_task_count": 403,
    "total": 1327,
    "stop_time": 1638265238000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1599903655011,
    "cstupdate": 1609205097077,
    "task_steps": [
    {
    "title": "保存二维码后-打开微信扫一扫识别相册中刚才保存的二维码",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20200929165033970.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "点击一键登录—领取会员截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201013190246809.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201013190251058.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交领取成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201013190303219.png"
    }
    ],
    "form": {
    "id": 6364,
    "label": "提交领取成功截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 49,
    "otherid": 125,
    "logo_addr": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/logo/20200912213544832.jpg",
    "name": "平安小橙花出额",
    "exp": "平安小橙花高通过秒出额",
    "type": "GAO_E_JIE_TU",
    "award_type": 1,
    "channel_amount": 3510,
    "amount": 2808,
    "last_task_count": 719,
    "total": 800,
    "stop_time": 1635686247000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1599916886823,
    "cstupdate": 1609199059594,
    "task_steps": [
    {
    "title": "使用微信或者浏览器扫描二维码，下载app",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925221932975.jpg?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1597725538549"
    }
    ],
    "form": null
    },
    {
    "title": "点击立即申请，根据系统步骤完成人脸识别、绑定银行卡、身份证、基本信息录入等，完成对应流程操作。",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208183637860.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208183639327.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208183640643.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交额度截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208183648535.jpg"
    }
    ],
    "form": {
    "id": 6361,
    "label": "提交额度截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交短信出额信息截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208183654624.png"
    }
    ],
    "form": {
    "id": 6362,
    "label": "短信出额信息截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交姓名手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6363,
    "label": "提交姓名手机号",
    "type": "TEXT",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 50,
    "otherid": 126,
    "logo_addr": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/logo/20200912214325856.jpg",
    "name": "翼支付—甜橙",
    "exp": "仅限翼支付新用户",
    "type": "GAO_E_JIE_TU",
    "award_type": 1,
    "channel_amount": 2142,
    "amount": 1713,
    "last_task_count": 255,
    "total": 300,
    "stop_time": 1635687762000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 180,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1599918215647,
    "cstupdate": 1609165796657,
    "task_steps": [
    {
    "title": "扫码-点击申请-点击领取",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104212814590.jpg?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1599207276312"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104212816674.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104212818548.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "下载App-登录-点击借钱",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104212833376.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104212835945.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104212837400.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "绑卡-填写信息-刷脸",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104212850217.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104212852900.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104212854601.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交姓名手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6358,
    "label": "提交姓名手机号",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交授信结果出额截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104212935844.jpg"
    }
    ],
    "form": {
    "id": 6359,
    "label": "提交授信结果出额截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交短信截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104212952156.jpg"
    }
    ],
    "form": {
    "id": 6360,
    "label": "提交短信截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 51,
    "otherid": 139,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201012143628632.jpg",
    "name": "苏宁任性付",
    "exp": "苏宁任性付简单出额",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 2907,
    "amount": 2325,
    "last_task_count": 448,
    "total": 500,
    "stop_time": 1638282578000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 180,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1601044223160,
    "cstupdate": 1609198750498,
    "task_steps": [
    {
    "title": "使用浏览器扫描二维码，输入手机号完成注册并下载登录APP【登录后先别动，看第二步】",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925222710659.jpg?qrtext=http%3A%2F%2Fqr34.cn%2FBRCKMY"
    }
    ],
    "form": null
    },
    {
    "title": "再用浏览器扫描下面的二维码，领取任性付新人礼包（保留截图）【注：不是步骤一的码，一定要扫这个码做单】",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925222736871.jpg?qrtext=http%3A%2F%2Fqr34.cn%2FDqpmlu"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925222738204.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "打开第一步下载的app，在首页点击任性付【先完成第二步领取礼包才行】【千万不要点错】，点立即申请，完成信息认证，成功出了额度，收到额度通过短信就算完成",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925222820471.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200925222822701.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交姓名+手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6354,
    "label": "提交姓名手机号",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交任性付新人礼包截图（不要打码）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926014620129.jpg"
    }
    ],
    "form": {
    "id": 6355,
    "label": "提交任性付新人礼包截图不要打码",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交我的账号截图（需看到有任性付额度）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926014647441.jpg"
    }
    ],
    "form": {
    "id": 6356,
    "label": "提交我的账号截图需看到有任性付额度",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交任性付成功出额度的短信截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//qianduoduo-oss.oss-cn-beijing.aliyuncs.com/xqq/img/task/step-info/20200926014718937.jpg"
    }
    ],
    "form": {
    "id": 6357,
    "label": "提交任性付成功出额度的短信截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 52,
    "otherid": 146,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20200929171657865.jpg",
    "name": "原子链实名",
    "exp": "原子币简单实名",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 558,
    "amount": 446,
    "last_task_count": 366,
    "total": 500,
    "stop_time": 1635585348000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1601371029678,
    "cstupdate": 1609199332398,
    "task_steps": [
    {
    "title": "保存二维码后-浏览器扫码-注册-下载",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201021143310378.jpg?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1599816637113"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208184617092.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208184619448.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "登录后点右上角实名认证，完成后截图个人信息截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208184632621.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208184633706.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208184635726.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "点击原理觉醒-我的好友-点击右上角邀请我的人-截图邀请我的人",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20200929171422336.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20200929171424786.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20200929171427753.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6351,
    "label": "提交手机号",
    "type": "PHONE",
    "orderId": 0
    }
    },
    {
    "title": "提交个人信息截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208184730364.png"
    }
    ],
    "form": {
    "id": 6352,
    "label": "提交个人信息截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交邀请我的人截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208184738176.png"
    }
    ],
    "form": {
    "id": 6353,
    "label": "提交邀请我的人截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 54,
    "otherid": 162,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201012153759829.png",
    "name": "安逸花",
    "exp": "安逸花申请高通过",
    "type": "GAO_E_JIE_TU",
    "award_type": 1,
    "channel_amount": 3852,
    "amount": 3081,
    "last_task_count": 471,
    "total": 500,
    "stop_time": 1635692664000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1602428707524,
    "cstupdate": 1609188358599,
    "task_steps": [
    {
    "title": "扫码填写手机号~领取红包~下载",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201011230130060.jpg?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1602313037377"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208185331365.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "①下载登入APP，②首页~~立即申请 ，填写资料等待出额  ③出额度后红包在：我的~~优惠卡券~~我的红包~~点击转出余额~~然后提现（不强制要求红包提现）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208185340539.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208185342003.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6348,
    "label": "提交手机号",
    "type": "PHONE",
    "orderId": 0
    }
    },
    {
    "title": "提交额度短信截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208185433029.png"
    }
    ],
    "form": {
    "id": 6349,
    "label": "提交额度短信",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交我的额度页面截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208185440501.png"
    }
    ],
    "form": {
    "id": 6350,
    "label": "提交我的额度页面截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 55,
    "otherid": 167,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201012153520197.jpg",
    "name": "钱夹谷谷出额",
    "exp": "钱夹谷谷认证秒通过",
    "type": "GAO_E_JIE_TU",
    "award_type": 1,
    "channel_amount": 5562,
    "amount": 4449,
    "last_task_count": 194,
    "total": 200,
    "stop_time": 1635694723000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 180,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1602430793807,
    "cstupdate": 1609199151298,
    "task_steps": [
    {
    "title": "微信扫码注册申请，完成身份认证后，完善一下个人信息【可优化填写，年收入6万，学历大专以上】，提交申请。一般5分钟内会出结果。",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201226174031271.png?qrtext=http%3A%2F%2Fqr34.cn%2FEROybj"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201226174034367.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201226174037565.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "审核通过后，下载钱夹谷谷app，激活额度，简单添加绑定到微信或者支付宝，随便交任意金额话费【使用刚才添加的卡号付款】",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201118165026839.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交姓名手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6375,
    "label": "提交姓名手机号",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交：消息通知图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201118165056962.png"
    }
    ],
    "form": {
    "id": 6376,
    "label": "提交消息通知图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交交话费后的消费详情截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201118165123960.png"
    }
    ],
    "form": {
    "id": 6377,
    "label": "提交交话费后的消费详情截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 56,
    "otherid": 169,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201012155216730.jpg",
    "name": "360简单出额",
    "exp": "360出额最高价",
    "type": "GAO_E_JIE_TU",
    "award_type": 1,
    "channel_amount": 7011,
    "amount": 5608,
    "last_task_count": 251,
    "total": 300,
    "stop_time": 1635610072000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1602432520523,
    "cstupdate": 1609201516689,
    "task_steps": [
    {
    "title": "使用浏览器扫描二维码输入手机号领取新人礼包，并下载登录360借条APP，注册手机号和登陆手机号全程必须保持一致。年龄小于20岁的请不要做本任务。",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201228140442545.jpg?qrtext=http%3A%2F%2F3f360.sosudu.cn"
    }
    ],
    "form": null
    },
    {
    "title": "打开app，在借钱页面点击“我要借钱”并按提示完成扫脸认证、绑定银行卡、身份证认证、联系人信息填写，完成提交",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208190703530.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208190705366.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208190707443.png"
    }
    ],
    "form": null
    },
    {
    "title": "在借钱页面，点击右上角的消息提醒，点击账务提醒，截图保存。（含审核通过消息和时间）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208190714421.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208190715730.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交姓名手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6387,
    "label": "提交姓名手机号",
    "type": "PHONE",
    "orderId": 0
    }
    },
    {
    "title": "提交财务出额度样式截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208190732775.png"
    }
    ],
    "form": {
    "id": 6388,
    "label": "提交财务出额度样式截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交短信出额截图（如果没收到请备注说明，客服进行核实）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201208190738297.png"
    }
    ],
    "form": {
    "id": 6389,
    "label": "提交短信出额截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 57,
    "otherid": 170,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201012001317032.jpg",
    "name": "魔龙小程序",
    "exp": "仅限魔龙新用户",
    "type": "APP_SHI_WAN",
    "award_type": 1,
    "channel_amount": 45,
    "amount": 36,
    "last_task_count": 410,
    "total": 500,
    "stop_time": 1635610381000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1602432798964,
    "cstupdate": 1609195825600,
    "task_steps": [
    {
    "title": "保存二维码后-打开微信扫一扫识别相册中刚才保存的二维码-进入小程序—提现0.3元",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201201185752583.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201201185754031.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交我的队伍截图（点击邀请券-点击我的好友就可以找到）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201012001240691.png"
    }
    ],
    "form": {
    "id": 6340,
    "label": "提交我的队伍截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交账户明细截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201012001253762.jpg"
    }
    ],
    "form": {
    "id": 6341,
    "label": "提交账户明细截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 58,
    "otherid": 180,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201013200812329.jpg",
    "name": "还呗简单出额",
    "exp": "还呗简单出额秒过",
    "type": "GAO_E_JIE_TU",
    "award_type": 1,
    "channel_amount": 2142,
    "amount": 1713,
    "last_task_count": 453,
    "total": 500,
    "stop_time": 1635682032000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1602590893691,
    "cstupdate": 1609203840071,
    "task_steps": [
    {
    "title": "扫描二维码输入手机号注册下载APP（一定要点击普通下载，不要点击安全下载，不然不合格）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201013200606479.png?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1602584892092"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209111926617.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209111929965.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "登录app点击立即激活额度，按照提示申请额度就行学历如果太低的话，可以适当写高一点点 必须选择大专或者本科以上，填完资料基本三五分钟就出结果了。",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209112003667.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6337,
    "label": "提交手机号",
    "type": "PHONE",
    "orderId": 0
    }
    },
    {
    "title": "提交额度短信图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209112011663.png"
    }
    ],
    "form": {
    "id": 6338,
    "label": "提交额度短信图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交出额度界面截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209112017627.png"
    }
    ],
    "form": {
    "id": 6339,
    "label": "提交出额度界面截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 60,
    "otherid": 194,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201027125720007.jpg",
    "name": "支付宝医保",
    "exp": "支付宝扫码简单已通过",
    "type": "APP_SHI_WAN",
    "award_type": 1,
    "channel_amount": 45,
    "amount": 36,
    "last_task_count": 394,
    "total": 500,
    "stop_time": 1635656188000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1603774643134,
    "cstupdate": 1609205579815,
    "task_steps": [
    {
    "title": "扫码-领取红包",
    "taskStepItemList": [
    {
    "type": "TEXT",
    "label": "",
    "value": "支付宝扫码-领取1000红包，然后跳转到下面授权一下就可以了"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201214184552614.png?qrtext=https%3A%2F%2Fqr.alipay.com%2F0dl19568hg0zghrq74fija0"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201214184554964.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201214184556875.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交：支付宝真实名字",
    "taskStepItemList": [],
    "form": {
    "id": 6335,
    "label": "提交支付宝真实名字",
    "type": "NAME",
    "orderId": 0
    }
    },
    {
    "title": "提交领到优惠券截图（必须是当天领取到支付宝红包的）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201103151753740.jpg"
    }
    ],
    "form": {
    "id": 6336,
    "label": "提交领到优惠券截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 61,
    "otherid": 195,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201027130421046.jpg",
    "name": "在机场小程序",
    "exp": "扫码领取优惠券",
    "type": "APP_SHI_WAN",
    "award_type": 1,
    "channel_amount": 45,
    "amount": 36,
    "last_task_count": 437,
    "total": 829,
    "stop_time": 1635656626000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1603775086679,
    "cstupdate": 1609197610083,
    "task_steps": [
    {
    "title": "扫码-领券",
    "taskStepItemList": [
    {
    "type": "TEXT",
    "label": "",
    "value": "扫码进入小程序-选择任意优惠券点击领取，完成授权。完成后点击“去使用”，进入详情页截图（有显示领券时间，必须是当天！！否则是老用户数据无效"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104230208252.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209143339432.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209143342157.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交姓名",
    "taskStepItemList": [],
    "form": {
    "id": 6333,
    "label": "提交姓名",
    "type": "NAME",
    "orderId": 0
    }
    },
    {
    "title": "提交：优惠券详情页截图（领券显示的时间，必须是当天才算有效）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209143347947.jpg"
    }
    ],
    "form": {
    "id": 6334,
    "label": "优惠券详情页截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 62,
    "otherid": 203,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201103194046002.jpg",
    "name": "支付宝新用户",
    "exp": "0元充值10元话费",
    "type": "GAO_E_JIE_TU",
    "award_type": 1,
    "channel_amount": 3249,
    "amount": 2599,
    "last_task_count": 290,
    "total": 300,
    "stop_time": 1635680440000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1604403652126,
    "cstupdate": 1609196075271,
    "task_steps": [
    {
    "title": "用浏览器扫描图中二维码（一定要先用浏览器扫描二维码）-输入新用户手机号点击立即领取（若不出下图界面换个浏览器扫直接出现如下界面）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201103193756083.png?qrtext=https%3A%2F%2Fqr.alipay.com%2F01j16705tdxl1zeskw4sjf4"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104142239056.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104142248263.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "下载安装支付宝App，用手机号完成注册-首次登录支付宝想要获取新用户地理位置时，选择始终允许（如登录支付宝未提示获取地理位置，可在手机设置里打开手机定位）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209144131587.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209144133703.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209144135001.png"
    }
    ],
    "form": null
    },
    {
    "title": "使用支付宝扫描支付宝拉新二维码进入红包领取页面，选择0元充话费红包（为本机新用户手机号充值才算有效）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201103193840033.png?qrtext=https%3A%2F%2Fqr.alipay.com%2F01j16705tdxl1zeskw4sjf4"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201103193844860.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "领取红包时进行实名认证-扫脸认证即可",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209144152819.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209144154810.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交：姓名+手机号（注册的新用户手机号）",
    "taskStepItemList": [],
    "form": {
    "id": 6329,
    "label": "姓名手机号",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交账单详情截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201103193957114.jpg"
    }
    ],
    "form": {
    "id": 6330,
    "label": "提交账单详情截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交短信截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201103194011079.png"
    }
    ],
    "form": {
    "id": 6331,
    "label": "提交短信截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交新人专享福利截图（必须有0元冲话费10元这个福利）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201103194029822.jpg"
    }
    ],
    "form": {
    "id": 6332,
    "label": "提交新人专享福利截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 63,
    "otherid": 204,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201104205006953.jpg",
    "name": "淘宝助力",
    "exp": "简单易通过",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 342,
    "amount": 273,
    "last_task_count": 426,
    "total": 500,
    "stop_time": 1635684534000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 180,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1604494207889,
    "cstupdate": 1609202602957,
    "task_steps": [
    {
    "title": "保存二维码后-微信扫描保存的二维码-复制口令【没APP的去应用商店下载安装，切记要先复制口令再打开】打开淘宝特价版助力（助力成功的截图）一定要提交，没有助力成功的不符合。请勿提交，谢谢。",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109104347752.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109104349650.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交注册手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6327,
    "label": "提交注册手机号",
    "type": "PHONE",
    "orderId": 0
    }
    },
    {
    "title": "提交助力成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104204844876.png"
    }
    ],
    "form": {
    "id": 6328,
    "label": "提交助力成功截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 64,
    "otherid": 209,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201104222308101.jpg",
    "name": "微粒贷",
    "exp": "可领取5元红包秒到账",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 1287,
    "amount": 1029,
    "last_task_count": 293,
    "total": 300,
    "stop_time": 1635690130000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1604499789309,
    "cstupdate": 1609205051108,
    "task_steps": [
    {
    "title": "保存二维码后-微信扫描保存的二维码（不是每个人都有的，打开是邀请页面的请放弃任务）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201104221937920.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "出额度自动到五元红包（保留截图，必须看到领取时间，否则不合格）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209145233727.png"
    }
    ],
    "form": null
    },
    {
    "title": "扫码进去活动规则第三条里面查看邀请码可以看到邀请人截图邀请码必须是bbeaa2",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209145240051.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交姓名和手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6324,
    "label": "提交姓名和手机号",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交我的邀请码页面截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209145247846.jpg"
    }
    ],
    "form": {
    "id": 6325,
    "label": "提交我的邀请码页面截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交邀请有礼红包截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209145254525.png"
    }
    ],
    "form": {
    "id": 6326,
    "label": "提交邀请有礼红包截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 65,
    "otherid": 220,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201105003618400.png",
    "name": "超级走路团",
    "exp": "超级简单易通过",
    "type": "APP_SHI_WAN",
    "award_type": 1,
    "channel_amount": 18,
    "amount": 14,
    "last_task_count": 336,
    "total": 500,
    "stop_time": 1635611712000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1604507796582,
    "cstupdate": 1609201325560,
    "task_steps": [
    {
    "title": "扫码-授权-领取红包-点击任意一个金币-跳转页面-保留金币截图（保留截图）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218161227569.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218161229697.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218161231492.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交获得金币截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201118202059360.jpg"
    }
    ],
    "form": {
    "id": 6323,
    "label": "提交获得金币截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 66,
    "otherid": 222,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201105005808947.jpg",
    "name": "B站新人助力",
    "exp": "简单助力即可",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 297,
    "amount": 237,
    "last_task_count": 180,
    "total": 500,
    "stop_time": 1635613035000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1604509089247,
    "cstupdate": 1609205053939,
    "task_steps": [
    {
    "title": "保存二维码后-浏览器识别二维码）跳转到哔哩上完成助力，没跳转到哔哩app上助力的属于不合格，如果你扫码显示满了或者充能已完成，需要刷新一下等一分钟重新扫码助力即可。",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218141940060.png?qrtext=http%3A%2F%2Fqr34.cn%2FF7yIAR"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218141942755.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交自己主页的截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209145620286.jpg"
    }
    ],
    "form": {
    "id": 6321,
    "label": "提交自己主页的截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交新兵助力成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201209145628071.jpg"
    }
    ],
    "form": {
    "id": 6322,
    "label": "提交新兵助力成功截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 67,
    "otherid": 224,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201109205404667.png",
    "name": "广发积分兑苏宁小店",
    "exp": "登录发现精彩APP进行兑换",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 1710,
    "amount": 1368,
    "last_task_count": 277,
    "total": 300,
    "stop_time": 1635684753000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 180,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1604926447907,
    "cstupdate": 1609194356777,
    "task_steps": [
    {
    "title": "登录发现精彩→左上角定位苏州→点击饭票→上面输入框搜索苏宁，找到苏宁小店，进入后找到30苏宁小店劵 →立即抢购→提交，完成积分兑换、",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109205106853.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109205108841.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109205110844.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "兑换成功后，截图券码提交即可，不可打码【看清楚是12位的劵码】样图如下",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109205118919.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交：复制券码号【是12位卷码号，不是订单号】+手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6319,
    "label": "提交复制券码号和手机号",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交自己的券码截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109205217918.jpg"
    }
    ],
    "form": {
    "id": 6320,
    "label": "提交自己的券码截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 68,
    "otherid": 225,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201109231411500.png",
    "name": "电信积分兑换",
    "exp": "兑换电影券",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 1287,
    "amount": 1029,
    "last_task_count": 257,
    "total": 300,
    "stop_time": 1635693108000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 180,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1604934854381,
    "cstupdate": 1609202688705,
    "task_steps": [
    {
    "title": "微信扫码-点击兑换-输入手机号验证码登录",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109231054797.png?qrtext=https%3A%2F%2Fmomall.telefen.com%2FProvinceWechat%2FCommodity%2FDetail%2F220885"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109231057391.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109231059924.png"
    }
    ],
    "form": null
    },
    {
    "title": "点击立即兑换-兑换成功后，点击获取卡号卡密短信",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109231108769.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109231110335.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "复制卡号提交一定要复制",
    "taskStepItemList": [],
    "form": {
    "id": 6317,
    "label": "复制卡号提交一定要复制",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交带有卡号的短信截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201109231137026.jpg"
    }
    ],
    "form": {
    "id": 6318,
    "label": "提交带有卡号的短信截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 69,
    "otherid": 227,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201110112307739.png",
    "name": "平安积分兑换天猫券",
    "exp": "兑换50购物券",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 2160,
    "amount": 1728,
    "last_task_count": 296,
    "total": 300,
    "stop_time": 1635650502000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 180,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1604978589792,
    "cstupdate": 1609202682663,
    "task_steps": [
    {
    "title": "应用商店下载壹钱包，点击“购物”，上方搜索框 搜索 天猫， 选择“天猫 购物券50元”，用27570积分支付",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201110112104991.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201110112106018.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "复制兑换成功的整条短信内容提交",
    "taskStepItemList": [],
    "form": {
    "id": 6315,
    "label": "复制兑换成功的整条短信内容提交",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交兑换成功的短信截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201110112127547.jpg"
    }
    ],
    "form": {
    "id": 6316,
    "label": "提交兑换成功的短信截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 70,
    "otherid": 249,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201118173221910.png",
    "name": "招商兑百度会员",
    "exp": "积分可以兑换会员",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 945,
    "amount": 756,
    "last_task_count": 288,
    "total": 300,
    "stop_time": 1635672626000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 180,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1605691942739,
    "cstupdate": 1609174009698,
    "task_steps": [
    {
    "title": "兑换",
    "taskStepItemList": [
    {
    "type": "TEXT",
    "label": "",
    "value": "打开掌上生活APP，在\"精选\"页面左上角，将城市切换到北京、广州、上海任一城市。点击顶部搜索栏输入\"百度网盘\"→下拉找到“百度网盘超级会员月卡\"→立即支付1299积分。兑换成功后，点击查看订单详情或点击兑换记录截图。"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201118173000114.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201118173002528.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "复制卷码提交",
    "taskStepItemList": [],
    "form": {
    "id": 6311,
    "label": "复制卷码提交",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交兑换成功的截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201118173017585.jpg"
    }
    ],
    "form": {
    "id": 6312,
    "label": "提交兑换成功的截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 71,
    "otherid": 252,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201118200743815.png",
    "name": "砸金蛋小程序",
    "exp": "小程序授权秒完成",
    "type": "APP_SHI_WAN",
    "award_type": 1,
    "channel_amount": 27,
    "amount": 21,
    "last_task_count": 226,
    "total": 649,
    "stop_time": 1635682001000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1605701263796,
    "cstupdate": 1609203077429,
    "task_steps": [
    {
    "title": "微信扫码-点击任意金蛋-点击立即领取-点击我的-登录-截图我的（显示加入联想第0天即可）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218162013463.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218162015110.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218162017149.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交我的截图（显示加入联想第0天）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201118200625065.jpg"
    }
    ],
    "form": {
    "id": 6309,
    "label": "提交我的截图显示加入联想第0天",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6310,
    "label": "提交手机号",
    "type": "PHONE",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 72,
    "otherid": 307,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201210192506381.png",
    "name": "赞丽生活实名",
    "exp": "仅限赞丽生活新用户做任务",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 1710,
    "amount": 1368,
    "last_task_count": 461,
    "total": 500,
    "stop_time": 1635679501000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1607599507608,
    "cstupdate": 1609191931399,
    "task_steps": [
    {
    "title": "浏览器扫码注册邀请码默认-填完信息记得先截图再点注册（无注册图不合格）-注册完下载APP",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201210192359127.png?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1603420273760"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201210192400855.png"
    }
    ],
    "form": null
    },
    {
    "title": "登录点我的-身份信息-实名认证-输入姓名身份证号实名（保留认证成功截图）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201210192407112.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201210192409850.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6305,
    "label": "提交手机号",
    "type": "PHONE",
    "orderId": 0
    }
    },
    {
    "title": "提交注册页面截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201210192427857.png"
    }
    ],
    "form": {
    "id": 6306,
    "label": "提交注册页面截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交身份信息截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201210192439708.png"
    }
    ],
    "form": {
    "id": 6307,
    "label": "提交身份信息截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交认证成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201210192452453.png"
    }
    ],
    "form": {
    "id": 6308,
    "label": "提交认证成功截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 73,
    "otherid": 327,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201218191452924.png",
    "name": "苏宁零元购",
    "exp": "领券下单购物",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 414,
    "amount": 331,
    "last_task_count": 270,
    "total": 300,
    "stop_time": 1635678886000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1608290103002,
    "cstupdate": 1609178637498,
    "task_steps": [
    {
    "title": "应用商店下载苏宁易购-微信扫码注册苏宁易购，不扫码后台没有数据，然后跳转苏宁易购登录，登录后点击天天领红包，找到新人专区领取新人券，领取后记得截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191350993.png?qrtext=http%3A%2F%2Fwww.jinshanju.com%2Fapi%2Fad%3Fkey%3D1606119126178"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191352891.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191354162.png"
    }
    ],
    "form": null
    },
    {
    "title": "如遇到领券说明活动火爆（如图一）请放弃此任务，在新人专区选一个商品进行购买，用券抵扣5元，签到红包抵扣1元，实际支付0元，下单成功后截图提交（下单不成功为风控用户，请不要提交）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191400686.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191402836.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191404266.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交注册手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6302,
    "label": "提交注册手机号",
    "type": "PHONE",
    "orderId": 0
    }
    },
    {
    "title": "提交成功领取新人券截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191425452.jpg"
    }
    ],
    "form": {
    "id": 6303,
    "label": "提交成功领取新人券截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交下单成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191436943.jpg"
    }
    ],
    "form": {
    "id": 6304,
    "label": "提交下单成功截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 74,
    "otherid": 328,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201218192029449.png",
    "name": "易校园",
    "exp": "必须使用钱包支付",
    "type": "APP_XIA_ZAI",
    "award_type": 1,
    "channel_amount": 603,
    "amount": 482,
    "last_task_count": 264,
    "total": 300,
    "stop_time": 1635679224000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1608290430142,
    "cstupdate": 1609192581799,
    "task_steps": [
    {
    "title": "扫码下载易校园APP，点击密码登陆-用户注册，邀请码5055（填错或不填不合格）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191856697.png?qrtext=https%3A%2F%2Fh5.xiaofubao.com%2Factivity%2Fh5%2Fdownload%3Ffrom%3Dhfsj"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191858631.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191859209.png"
    }
    ],
    "form": null
    },
    {
    "title": "点击去领取，选择在校学生并完成实名认证，认证成功返回首页话费充值，选择1元话费，实际支付几毛钱。",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191906696.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191909546.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交姓名",
    "taskStepItemList": [],
    "form": {
    "id": 6298,
    "label": "提交姓名",
    "type": "NAME",
    "orderId": 0
    }
    },
    {
    "title": "提交手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6299,
    "label": "提交手机号",
    "type": "PHONE",
    "orderId": 0
    }
    },
    {
    "title": "提交支付中心截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218191955846.png"
    }
    ],
    "form": {
    "id": 6300,
    "label": "提交支付中心截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交订单详情截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218192011336.png"
    }
    ],
    "form": {
    "id": 6301,
    "label": "提交订单详情截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 75,
    "otherid": 330,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201218210108638.png",
    "name": "民生纯注册",
    "exp": "简单注册就可以了",
    "type": "APP_SHI_WAN",
    "award_type": 1,
    "channel_amount": 90,
    "amount": 72,
    "last_task_count": 220,
    "total": 344,
    "stop_time": 1635685224000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1608296471999,
    "cstupdate": 1609203988898,
    "task_steps": [
    {
    "title": "微信扫码-点击关注公众号-点击领券-注册（保留注册成功截图）",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201222163344537.jpg?qrtext=http%3A%2F%2Fweixin.qq.com%2Fq%2F02ZGAbdgcRb_e100000070"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201222163345984.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201222163347120.png"
    }
    ],
    "form": null
    },
    {
    "title": "提交手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6295,
    "label": "提交手机号",
    "type": "PHONE",
    "orderId": 0
    }
    },
    {
    "title": "提交注册成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218210015180.png"
    }
    ],
    "form": {
    "id": 6296,
    "label": "提交注册成功截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 76,
    "otherid": 331,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201218210622994.png",
    "name": "微视助力红包",
    "exp": "需要有微视这个App的用户",
    "type": "APP_SHI_WAN",
    "award_type": 1,
    "channel_amount": 36,
    "amount": 28,
    "last_task_count": 278,
    "total": 319,
    "stop_time": 1635685559000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1608296783382,
    "cstupdate": 1609204271658,
    "task_steps": [
    {
    "title": "保存二维码到微信或者QQ扫码助力红包，微视是微信登录的就用微信扫码，微视是QQ登录的就用QQ扫码，不然助力不了",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218210449877.png?qrtext=https%3A%2F%2Fapi.erweicaihong.cn%2FWEXF"
    }
    ],
    "form": null
    },
    {
    "title": "点击去微视领惊喜跳转到微信app点击去助力，助力成功截图即可，助力成功会有助力成功这四个字",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218210456106.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218210458356.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218210500194.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交注册手机号",
    "taskStepItemList": [],
    "form": {
    "id": 6291,
    "label": "提交注册手机号",
    "type": "PHONE",
    "orderId": 0
    }
    },
    {
    "title": "提交微视用户名称",
    "taskStepItemList": [],
    "form": {
    "id": 6292,
    "label": "提交微视用户名称",
    "type": "TEXT",
    "orderId": 0
    }
    },
    {
    "title": "提交助力成功截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218210536354.jpg"
    }
    ],
    "form": {
    "id": 6293,
    "label": "提交助力成功截图",
    "type": "IMG",
    "orderId": 0
    }
    },
    {
    "title": "提交我的页面截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218210549652.jpg"
    }
    ],
    "form": {
    "id": 6294,
    "label": "提交我的页面截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 77,
    "otherid": 333,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201218211153699.png",
    "name": "妙呀成语小程序",
    "exp": "每答2-3题就可领取红包",
    "type": "APP_SHI_WAN",
    "award_type": 1,
    "channel_amount": 54,
    "amount": 43,
    "last_task_count": 241,
    "total": 357,
    "stop_time": 1635685870000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 240,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1608297116761,
    "cstupdate": 1609201481662,
    "task_steps": [
    {
    "title": "扫码进入小程序-点击开始猜成语-开红包即可",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218211043456.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218211045171.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218211046919.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交兑换0.2元红包记录截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218211104865.png"
    }
    ],
    "form": {
    "id": 6290,
    "label": "提交兑换红包记录截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    },
    {
    "id": 78,
    "otherid": 336,
    "logo_addr": "//oss.jinbig.cn/xqq/img/task/logo/20201223135015003.jpeg",
    "name": "北京常住人员满意度调查",
    "exp": "测试问卷题",
    "type": "WEN_JUAN",
    "award_type": 1,
    "channel_amount": 270,
    "amount": 216,
    "last_task_count": 50,
    "total": 50,
    "stop_time": 1609393819000,
    "status": "RUNNING",
    "limitation": 1,
    "achieve_minute_limit": 120,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqurl": null,
    "addtime": 0,
    "updatetime": 0,
    "orderid": 0,
    "cstcreate": 1608702993673,
    "cstupdate": 1609174926299,
    "task_steps": [
    {
    "title": "注意事项",
    "taskStepItemList": [
    {
    "type": "TEXT",
    "label": "",
    "value": "问卷填写必须填写北京常住人口，手机信号所在地为北京。  注意打分题记住大概后面会统一进行一次回顾检查。"
    }
    ],
    "form": {
    "id": 6252,
    "label": "最后一题Y5题提交姓名答案",
    "type": "TEXT",
    "orderId": 0
    }
    }
    ],
    "finish": null,
    "receive": null,
    "commit": null
    }
    ]
    }
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    public function lists(){
        $access_token=$this->request->post('access_token');
        if(empty($access_token)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        $data = array('access_token'=>$access_token);
        $task = curl_post('http://task.expertcp.com/openapi/v1.server/task',$data);

        $task = \Qiniu\json_decode($task,true);

        return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($task['data']));
    }

    /**
     * @api {post} /Task/detail 03、返回任务详情
     * @apiGroup Task
     * @apiVersion 1.0.0
     * @apiDescription  任务详情
     * @apiParam (输入参数：) {int}     		[taskid] 任务编号
     * @apiParam (输入参数：) {string}     		[access_token] token
     * @apiSuccessExample {json} 01 成功示例
     * {
    "status": "200",
    "msg": "返回成功",
    "data": {
    "id": 333,
    "logoAddr": "//oss.jinbig.cn/xqq/img/task/logo/20201218211153699.png",
    "name": "妙呀成语小程序",
    "desc": "每答2-3题就可领取红包",
    "type": "APP_SHI_WAN",
    "awardType": 1,
    "amount": 43,
    "last": 240,
    "userTaskId": "5fea88434fd40d3ed7913b7a",
    "userTaskStatus": 1,
    "quesTargetUrl": "",
    "total": 357,
    "stopTime": 1635685870000,
    "status": "RUNNING",
    "limitation": 1,
    "version": 749,
    "achieveMinuteLimit": 240,
    "taskSteps": [
    {
    "title": "扫码进入小程序-点击开始猜成语-开红包即可",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218211043456.png"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218211045171.jpg"
    },
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218211046919.jpg"
    }
    ],
    "form": null
    },
    {
    "title": "提交兑换0.2元红包记录截图",
    "taskStepItemList": [
    {
    "type": "IMG",
    "label": null,
    "value": "//oss.jinbig.cn/xqq/img/task/step-info/20201218211104865.png"
    }
    ],
    "form": {
    "id": 6290,
    "label": "提交兑换红包记录截图",
    "type": "IMG",
    "orderId": 0
    }
    }
    ],
    "approveDesc": null,
    "flag": false,
    "submitTimeOut": null,
    "wechat": "17703630118",
    "qq": "680979431",
    "qqUrl": "mqqopensdkapi://bizAgent/qm/qr?url=http%3A%2F%2Fqm.qq.com%2Fcgi-bin%2Fqm%2Fqr%3Ffrom%3Dapp%26p%3Dandroid%26k%3Dk4J5piE_b_FQx5cUM3jgRG-gXXtbe1c6",
    "forms": [
    {
    "formType": "IMG",
    "formName": "提交兑换红包记录截图",
    "formKey": "6290",
    "formValue": "http//oss.jinbig.cn/xqq/img/task/step-info/20201218211104865.png"
    }
    ]
    }
    }
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    public function detail(){
        $taskid  = $this->request->post('taskid', 0, 'intval');
        $access_token  = $this->request->post('access_token', '');
        if(empty($taskid)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        if(empty($access_token)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }

        $url = 'http://task.expertcp.com/openapi/v1.server/taskdetail';

        $data = array(
            'id'=>$taskid,
            'access_token'=>$access_token
            );

        $result  = curl_post($url,$data);
        $result = \Qiniu\json_decode($result,TRUE);
        $result = $result['data']['result'];


        $result['stop_time'] = $result['stop_time']/1000;
        $result['task_steps'] = \Qiniu\json_decode($result['task_steps'],TRUE);
        $result['averagecheck'] = empty($result['averagecheck'])?3:$result['averagecheck'];
        $result['averagefinish'] = empty($result['averagefinish'])?7:$result['averagefinish'];
        return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($result));
    }

    /**
     * @api {post} /Task/receivingTask 04、接任务
     * @apiGroup Task
     * @apiVersion 1.0.0
     * @apiDescription  接任务
     * @apiParam (输入参数：) {string}     		[taskid] 任务编号
     * @apiParam (输入参数：) {string}     		[access_token] 任务token
     * @apiSuccessExample {json} 01 成功示例
     * {"status":200,"msg":"返回任务编号","data":"asdfasdfasdfa"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":90101,"msg":"有未完成的任务"}
     */
    public function receivingTask(){
        $taskid  = $this->request->post('taskid', 0, 'intval');
        $access_token  = $this->request->post('access_token', '');
        if(empty($taskid)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        if(empty($access_token)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }

        $url = 'http://task.expertcp.com/openapi/v1.server/receivingTask';

        $data = array(
            'id'=>$taskid,
            'access_token'=>$access_token
        );

        $result  = curl_post($url,$data);
        return $result;
        $result = json_decode($result,TRUE);
        if($result['status']==1){
            return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($result));
        }else{
            return $this->ajaxReturn($result['status'],$result['msg']);
        }
    }

    /**
     * @api {post} /Task/membertask 05、已接会员任务表
     * @apiGroup Task
     * @apiVersion 1.0.0
     * @apiDescription  接任务
     * @apiParam (输入参数：) {int}     		[page] 分页 默认1
     * @apiParam (输入参数：) {int}     		[pagesize] 每页显示 默认20
     * @apiParam (输入参数：) {int}     		[status] 状态 默认0 ；0、已领取,待提交；1、已提交,审核中； 2任务超时; 3、已完成；4、审核未通过,5 已放弃
     * @apiSuccessExample {json} 01 成功示例
     * {"status":200,"msg":"返回任务编号","data":"asdfasdfasdfa"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":90101,"msg":"有未完成的任务"}
     */
    public function membertask(){

        $page  = $this->request->post('page', '1','intval');
        $pagesize  = $this->request->post('pagesize', '20','intval');
        $status  = $this->request->post('status', '0','intval');

        $access_token  = $this->request->post('access_token', '');

        if(empty($access_token)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }

        $url = 'http://task.expertcp.com/openapi/v1.server/lists';

        $data = array(
            'page'=>$page,
            'pagesize'=>$pagesize,
            'status'=>$status,
            'access_token'=>$access_token
        );
        $result  = curl_post($url,$data);
        $result = \Qiniu\json_decode($result,TRUE);

        if($result['status']==1){
            return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($result['msg']['data']));
        }else{
            return $this->ajaxReturn($result['status'],$result['msg']);
        }

    }

    /**
     * @api {post} /Task/submit 06、提交任务
     * @apiGroup Task
     * @apiVersion 1.0.0
     * @apiDescription  接任务
     * @apiParam (输入参数：) {string}     		[taskid] 详情里的userTaskid
     * @apiParam (输入参数：) {array}     		[forms]  提交的表单数组
     * @apiParam (输入参数：) {string}           [forms.formKey] 表单key-该值任务详情返回(taskSteps-form-id)
     * @apiParam (输入参数：) {string}           [forms.formName] 表单名--该值任务详情返回(taskSteps-form-label)
     * @apiParam (输入参数：) {string}           [forms.formType] 表单类型枚举值: IMG|PHONE|WX|QQ|NAME|TEXT(taskSteps-form-type)
     * @apiParam (输入参数：) {string}           [forms.formValue] 表单值,用户填写
     * @apiParam (输入参数：) {string}     		[access_token] access_token
     * @apiSuccessExample {json} 01 成功示例
     * {"status":200,"msg":"返回任务编号","data":"asdfasdfasdfa"}
     * @apiErrorExample {json} 02 失败示例
     * {
    "status": 90104,
    "msg": "操作失败, 任务状态错误"
    }
     */
    public function submit(){
        $taskid  = $this->request->post('taskid', '');
        $access_token  = $this->request->post('access_token', '');
        $forms  = $this->request->post('forms', '');

        if(empty($taskid)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        if(empty($access_token)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }

        $url = 'http://task.expertcp.com/openapi/v1.server/submit';
        $data = array(
            'taskid'=>$taskid,
            'access_token'=>$access_token,
            'forms'=>($forms)
        );

        $result  = curl_post($url,$data);

        $result = json_decode($result,TRUE);

        if($result['status']==1){
            return $this->ajaxReturn($this->successCode,'返回成功');
        }else{
            if($result['status']=='90103'){
                $result['msg']['message']='该任务您已经提交或者不存在';
            }
            return $this->ajaxReturn($result['status'],$result['msg']['message']);
        }
    }

    /**
     * @api {post} /Task/userincome 07、任务收入
     * @apiGroup Task
     * @apiVersion 1.0.0
     * @apiDescription  接任务
     * @apiParam (输入参数：) {int}     		[id] 任务编号
     * @apiParam (输入参数：) {string}     		[access_token] access_token
     * @apiSuccessExample {json} 01 成功示例
     * {"status":200,"msg":"返回任务编号","data":"asdfasdfasdfa"}
     * @apiErrorExample {json} 02 失败示例
     * {
    "status": 90104,
    "msg": "操作失败, 任务状态错误"
    }
     */
    public function userincome(){
        $accesstoken = $this->request->post('access_token', '', '');
        $id = $this->request->post('id', '', 'intval');
        /*if(empty($id)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }*/
        if(empty($accesstoken)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        $data = array(
            'id'=>$id,
            'access_token'=>$accesstoken,
        );
        $url = 'http://task.expertcp.com/openapi/v1.server/userincome';
        $result  = curl_post($url,$data);
        return $result;
    }

    /**
     * @api {post} /Task/usergoodwork 08、其他任务
     * @apiGroup Task
     * @apiVersion 1.0.0
     * @apiDescription  接任务
     * @apiParam (输入参数：) {int}     		[id] 任务编号
     * @apiSuccessExample {json} 01 成功示例
     * {"status":200,"msg":"返回任务编号","data":"asdfasdfasdfa"}
     * @apiErrorExample {json} 02 失败示例
     * {
    "status": 90104,
    "msg": "操作失败, 任务状态错误"
    }
     */
    public function usergoodwork(){

        $id = $this->request->post('id', '', 'intval');
        /*if(empty($id)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }*/
        if(empty($id)){
            return $this->ajaxReturn($this->errorCode,'参数错误');
        }
        $data = array(
            'id'=>$id,
        );
        $url = 'http://task.expertcp.com/openapi/v1.server/usergoodwork';
        $result  = curl_post($url,$data);
        return $result;
    }


}

