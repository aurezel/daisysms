<?php 
/*
 module:		会员聊天记录
 create_time:	2020-08-10 15:58:51
 author:		
 contact:		
*/

namespace app\api\controller;

use app\api\service\ChatService;
use app\api\model\Chat as ChatModel;
use GatewayWorker\Lib\Gateway;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

class Sport extends Common {


	/**
 * @api {get} /Sport/index 01、首页数据列表
 * @apiGroup Sport
 * @apiVersion 1.0.0
 * @apiDescription  首页数据列表
 * @apiParam (输入参数：) {int}     		[limit] 每页数据条数（默认20）
 * @apiParam (输入参数：) {int}     		[page] 当前页码
 * @apiParam (输入参数：) {string}		[type] football:足球；basketball：篮球
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
        $postField = 'type,limit,page';
        $data = $this->request->only(explode(',',$postField),'post',null);
        $url = 'http://live.expertcp.com/api/FootballMatch/amy_matches';
        if(empty($data['type'])){
            $data['type'] = 'football';
        }
        $arr = array('type'=>$data['type'],'page'=>$data['page'],'limit'=>$data['limit']);
        $result = curl_post($url,$arr);
        return $result;
    }

    /**
     * @api {get} /Sport/detail 02、比赛详情
     * @apiGroup Sport
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}     		[match_id] 比赛id
     * @apiParam (输入参数：) {string}     		[type] 类型 ：football basketball
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
    function detail(){
        $postField = 'match_id,type';
        $data = $this->request->only(explode(',',$postField),'post',null);


        if(empty($data['match_id'])){
            throw new ValidateException('参数错误');
        }
        if(empty($data['type'])){
            throw new ValidateException('参数错误');
        }
        if($data['type']=='football'){
            $url = 'http://live.expertcp.com/api/FootballMatch/view';
        }else{
            $url = 'http://live.expertcp.com/api/BasketBallMatch/view';
        }
        $arr = array('match_id'=>$data['match_id']);
        $result = curl_post($url,$arr);
        return $result;
    }
    /**
     * @api {get} /Sport/video 03、首页数据列表
     * @apiGroup Sport
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}     		[limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}     		[page] 当前页码
     * @apiParam (输入参数：) {int}		[sport_id] 1:足球；2：篮球
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
	function video(){
        $url = 'http://live.expertcp.com/api/SportsVideoCollection/index';
        $postField = 'sport_id,limit,page,id';
        $data = $this->request->only(explode(',',$postField),'post',null);
        if(empty($data['sport_id'])){
            $data['sport_id'] = '1';
        }

        $arr = array('id'=>$data['id'],'sport_id'=>$data['sport_id'],'page'=>$data['page'],'limit'=>$data['limit']);
        $result = curl_post($url,$arr);
        return $result;
    }
    /**
     * @api {get} /Sport/videodetail 04、集锦详情
     * @apiGroup Sport
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}		id
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
    function videodetail(){
        $url = 'http://live.expertcp.com/api/SportsVideoCollection/view';
        $postField = 'id';
        $data = $this->request->only(explode(',',$postField),'post',null);
        if(empty($data['id'])){
            throw new ValidateException('参数错误');
        }
        $arr = array('id'=>$data['id']);
        $result = curl_post($url,$arr);
        return $result;
    }

    /**
     * @api {get} /Sport/message 04、发送消息
     * @apiGroup Sport
     * @apiVersion 1.0.0
     * @apiDescription  发送消息
     * @apiParam (输入参数：) {int}		matchid
     * @apiParam (输入参数：) {string}		type football|basketball
     * @apiParam (输入参数：) {string}		content 发布内容
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
    function message(){
        $uid = $this->request->uid;
        $info = \app\api\model\Member::find($uid);
        $postField = 'matchid,type,content';
        $data = $this->request->only(explode(',',$postField),'post',null);
        if(empty($data['content'])){
            throw new ValidateException('参数错误');
        }
        if(empty($data['matchid'])){
            throw new ValidateException('参数错误');
        }
        if(empty($data['type'])){
            throw new ValidateException('参数错误');
        }
        Gateway::$registerAddress = '127.0.0.1:1233';
        Gateway::$persistentConnection = false;
        $arr = array(
            'event'=>'game_message',
            'data'=>array(
                'avatar'=>$info['avatar'],
                'nickname'=>$info['nickname'],
                'content'=>$data['content'],
                'messagetype'=>'message'
            )
        );
        $arr = json_encode($arr);
        $groupid = $data['matchid'].$data['type'];

        Gateway::sendToGroup($groupid,$arr);
        return $this->ajaxReturn($this->successCode,'发送成功');
    }
    /**
     * @api {get} /Sport/joingroup 05、加入组
     * @apiGroup Sport
     * @apiVersion 1.0.0
     * @apiDescription  发送消息
     * @apiParam (输入参数：) {int}		matchid
     * @apiParam (输入参数：) {string}		type football|basketball
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
    function joingroup(){
        $uid = $this->request->uid;
        $info = \app\api\model\Member::find($uid);
        $postField = 'matchid,type';
        $data = $this->request->only(explode(',',$postField),'post',null);

        if(empty($data['matchid'])){
            throw new ValidateException('参数错误');
        }
        if(empty($data['type'])){
            throw new ValidateException('参数错误');
        }
        $groupid = $data['matchid'].$data['type'];

        Gateway::$registerAddress = '127.0.0.1:1233';
        Gateway::$persistentConnection = false;

        $clientlists = Gateway::getClientIdByUid($uid);

        foreach ($clientlists as $key=>$v){
            Gateway::joinGroup($v,$groupid);
        }

        $arr = array(
            'event'=>'joingroup',
            'data'=>array(
                'avatar'=>$info['avatar'],
                'nickname'=>$info['nickname'],
                'content'=>'进入直播间',
                'messagetype'=>'comein'
            )
        );
        $arr = json_encode($arr);
        Gateway::sendToGroup($groupid,$arr);
        return $this->ajaxReturn($this->successCode,'恭喜您，加入组成功',1);
    }
    /**
     * @api {get} /Sport/outgroup 06、离开组
     * @apiGroup Sport
     * @apiVersion 1.0.0
     * @apiDescription  发送消息
     * @apiParam (输入参数：) {int}		matchid
     * @apiParam (输入参数：) {string}		type football|basketball
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
    function outgroup(){
        $uid = $this->request->uid;
        $info = \app\api\model\Member::find($uid);
        $postField = 'matchid,type';
        $data = $this->request->only(explode(',',$postField),'post',null);
        if(empty($data['matchid'])){
            throw new ValidateException('参数错误');
        }
        $groupid = $data['matchid'].$data['type'];
        Gateway::$registerAddress = '127.0.0.1:1233';
        Gateway::$persistentConnection = false;
        $clientlists = Gateway::getClientIdByUid($uid);
        foreach ($clientlists as $key=>$v){
            Gateway::leaveGroup($v,$groupid);
        }
        $arr = array(
            'event'=>'outgroup',
            'data'=>array(
                'avatar'=>$info['avatar'],
                'nickname'=>$info['nickname'],
                'content'=>'退出了直播间',
                'messagetype'=>'outgroup'
            )
        );
        $arr = json_encode($arr);
        $groupid = $data['matchid'].$data['type'];
        Gateway::sendToGroup($groupid,$arr);
        return $this->ajaxReturn($this->successCode,'恭喜您，离开组成功');
    }

    function sendmsg(){
       $str='钱不是问题，问题是没钱!喝醉了我谁也不服,我就扶墙!我就像一只趴在玻璃上的苍蝇!前途一片光明，但又找不到出路!大师兄，你知道吗?二师兄的肉现在比师傅的都贵了!如果多吃鱼可以补脑让人变聪明的话，那么你至少得吃一对儿鲸鱼??!水至清则无鱼，人至贱则无敌!青春就像卫生纸，看着挺多得，用着用着就不够了~!怀才就像怀孕，时间久了才能让人看出来。!我身边的朋友们啊，你们快点出名吧，这样我的回忆录就可以畅销了~~~!同事去见客户,可能是紧张,一开口便是：“刘先生你好,请问你贵姓啊?”汗啊~~~~~~!一女同学黑了些,她男友又太白了些,有天宿舍里得毒舌天后突然对她冒出一句：“你们这样不行,你们会生出斑马来的”!老娘一向视帅哥与金钱如粪土，而他们也一直是这样看我的!不要和我比懒,我懒得和你比!我不是个随便的人 我随便起来不是人!上帝说,要有光,我说我反对,从此世界上有了黑暗!今天心情不好.我只有四句话想说.包括这句和前面的两句.我的话说完了......!做人就要做一个徘徊在牛a和牛c之间的人!我的大名叫上帝，小名叫耶稣，英文名god， 法号是如来...!人不能在一棵树上吊死，要在附近几棵树上多死几次试试!树不要皮，必死无疑;人不要脸，天下无敌。!农夫三拳有点疼!其实我一直很受人欢迎的：小时候的我人见人爱，如今的我人贱人爱!不怕虎一样的敌人，就怕猪一样的队友!走自己的路，让别人打车去吧!老鼠扛刀，满街找猫!只要功夫深，拉屎也认真!中国人谁跑的最快?是曹操(非刘翔)。因为说曹操曹操到!思想有多远，你就给我滚多远!只有在火车站大排长龙时，才能真正意识到自己是“龙的传人”。!有情人终成家属!春天来了,一群大雁正向北飞,一会儿排成b字型,一会儿排成t字型..!在哪里跌倒 就在哪里躺下!老虎不发威 你当我是hello kitty!驴是的念来过倒~    ';
        $strs = explode('!',$str);

        Gateway::$registerAddress = '127.0.0.1:1233';
        Gateway::$persistentConnection = false;

        $content = rand(0,count($strs)-1);
        $memberid = rand(13103, 13406);
        $info = \app\api\model\Member::find($memberid);


        $arr1 = array(
            'event'=>'game_message',
            'data'=>array(
                'avatar'=>$info['avatar'],
                'nickname'=>$info['nickname'],
                'content'=>$strs[$content],
                'messagetype'=>'message'
            )
        );
        $arr1 = json_encode($arr1);
        $url = 'http://live.expertcp.com/api/FootballMatch/amy_matches';
        if(empty($data['type'])){
            $data['type'] = 'football';
        }
        $arr = array('type'=>$data['type'],'page'=>1,'limit'=>500);
        $result = curl_post($url,$arr);
        $result = \Qiniu\json_decode($result,true);
        $result = $result['data'];
        foreach ($result as $key=>$v){
            $groupid = $v['id'].$data['type'];
            Gateway::sendToGroup($groupid,$arr1);
        }


    }
}

