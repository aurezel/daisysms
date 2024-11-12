<?php
/**
 * TODO 新版朋友圈
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/10/19
 * Time: 10:22
 */

namespace app\api\controller;


use app\api\service\FriendCircleService;
use think\facade\Db;

class NewFriendCircle extends Common
{
    /**
     * @api {get} /NewFriendCircle/interest 01、朋友圈推荐
     * @apiGroup NewFriendCircle
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":[{"likeid":0,"type":2,"id":594,"content":"[\"http:\\\/\\\/img.expertcp.com\\\/api\\\/202010\\\/202010161434360140016.jpg\"]","province":"四川省","city":"成都市","title":"被保护的人喜欢肆无忌惮，爱依赖的人容易提心吊胆。","clickcount":0,"likecount":4,"tag":"美图","commentcount":0,"addtime":1602830078,"ypid":"yp_5f62f6975db1a","avatar":"http:\/\/img.expertcp.com\/api\/202009\/202009171339130161577.png","nickname":"限量版小祖宗","gzid":0,"options":"{\"width\":651,\"height\":750}","ratio":0.87,"isvote":0,"isowner":"0","like_list":[{"friendcircleid":594,"member_id":13511,"avatar":"http:\/\/img.expertcp.com\/api\/202009\/202009171339130161577.png","nickname":"限量版小祖宗"},{"friendcircleid":594,"member_id":13999,"avatar":"http:\/\/img.expertcp.com\/api\/202010\/202010120847480170747.jpg","nickname":"大胖"},{"friendcircleid":594,"member_id":13286,"avatar":"http:\/\/img.expertcp.com\/api\/202009\/202009211620290141716.png","nickname":"b j j k k k k葫芦娃救爷爷"},{"friendcircleid":594,"member_id":13998,"avatar":"http:\/\/img.expertcp.com\/api\/202010\/202010100312110114116.jpg","nickname":"DavidLiii"}],"comment_list":[]}]}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    public function interest()
    {
        $uid = $this->request->uid;
        $limit = $this->request->post('limit', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');

        $where = array();
        $un_like_list = Db::name('friend_report')->where("member_id", $uid)->select()->toArray();
        if ($un_like_list) {
            $un_like_ids = array_column($un_like_list, 'o_member_id');
            $where[] = ['a.member_id', 'not in', $un_like_ids];
        }

        $where[] = ['a.type', 'in', [0, 2, 4]];
        $field = 'c.member_id as likeid,a.member_id,type,a.id,content,province,city,title,clickcount,likecount,tag,commentcount,a.addtime,ypid,avatar,nickname,b.id as gzid,options';

        $orderby = 'a.addtime desc';
        $res = FriendCircleService::loadData($uid, $where, $field, $orderby, $limit, $page);
        if ($res) {
            $friendcircleids = array_column($res, 'id');
            $like_list = Db::name('friend_priase')->alias('a')->field('a.friendcircleid,b.member_id,b.avatar,b.nickname')->whereIn("a.friendcircleid", $friendcircleids)->join('member b', 'a.member_id = b.member_id')->select()->toArray();
            $like_list_array = [];
            foreach ($like_list as $key => $value) {
                $like_list_array[$value['friendcircleid']][] = $value;
            }
            $comment_list = Db::name('friend_comment')->alias('a')->field('a.*,b.ypid,b.member_id,b.avatar,b.nickname')->whereIn("a.friendcircleid", $friendcircleids)->join('member b', 'a.member_id = b.member_id')->select()->toArray();
            $comment_list_array = [];
            foreach ($comment_list as $key => $value) {
                $comment_list_array[$value['friendcircleid']][] = $value;
            }
            foreach ($res as $key => &$val) {
                $val['like_list'] = [];
                $val['comment_list'] = [];
                if (isset($like_list_array[$val['id']])) {
                    $val['like_list'] = $like_list_array[$val['id']];
                }
                if (isset($comment_list_array[$val['id']])) {
                    $val['comment_list'] = $comment_list_array[$val['id']];
                }
            }
        }
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }


    /**
     * @api {get} /NewFriendCircle/my_circle 02、查看朋友圈
     * @apiGroup NewFriendCircle
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {int}            ypid 要查看的ypid
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":[{"year":2020,"list":[{"day":"03","month":"09","list":[{"likeid":0,"type":2,"id":327,"content":"[\"http:\\\/\\\/img.expertcp.com\\\/api\\\/202009\\\/202009030901170187210.png\"]","province":null,"city":"贵阳市","title":"什么鬼","clickcount":0,"likecount":2,"tag":"美图","commentcount":0,"addtime":1599094878,"ypid":"YP_3jc2t07tspshl04s1kitiiu107","avatar":"http:\/\/img.expertcp.com\/api\/202009\/202009021820570152330.png","nickname":"艾米","gzid":0,"options":null,"ratio":0,"isvote":0,"isowner":"0"}]},{"day":"02","month":"09","list":[{"likeid":0,"type":2,"id":325,"content":"[\"http:\\\/\\\/img.expertcp.com\\\/api\\\/202009\\\/202009021811410159199.png\",\"http:\\\/\\\/img.expertcp.com\\\/api\\\/202009\\\/202009021811420125411.png\"]","province":null,"city":"贵阳市","title":"这个了","clickcount":0,"likecount":0,"tag":"美图","commentcount":0,"addtime":1599041502,"ypid":"YP_3jc2t07tspshl04s1kitiiu107","avatar":"http:\/\/img.expertcp.com\/api\/202003\/202003261652580121448.jpg","nickname":"艾米","gzid":0,"options":null,"ratio":0,"isvote":0,"isowner":"0"}]},{"day":"01","month":"09","list":[{"likeid":0,"type":4,"id":293,"content":"{\"pic\":\"http:\\\/\\\/qukufile2.qianqian.com\\\/data2\\\/pic\\\/b733a1a9fc0f63c7015be29b7b840b66\\\/672866107\\\/672866107.jpg@s_2,w_500,h_500\",\"title\":\"\\u6865\\u8fb9\\u59d1\\u5a18\",\"song_id\":\"672865438\",\"author\":\"\\u821e\\u8e48\\u5973\\u795e\\u8bfa\\u6db5\"}","province":null,"city":"贵阳市","title":"吕天逸","clickcount":0,"likecount":0,"tag":"音乐","commentcount":0,"addtime":1598940321,"ypid":"YP_3jc2t07tspshl04s1kitiiu107","avatar":"http:\/\/img.expertcp.com\/api\/202003\/202003261652580121448.jpg","nickname":"艾米","gzid":0,"options":null,"isvote":0,"isowner":"0"},{"likeid":0,"type":4,"id":292,"content":"{\"pic\":\"http:\\\/\\\/qukufile2.qianqian.com\\\/data2\\\/pic\\\/b733a1a9fc0f63c7015be29b7b840b66\\\/672866107\\\/672866107.jpg@s_2,w_500,h_500\",\"title\":\"\\u6865\\u8fb9\\u59d1\\u5a18\",\"song_id\":\"672865438\",\"author\":\"\\u821e\\u8e48\\u5973\\u795e\\u8bfa\\u6db5\"}","province":null,"city":"贵阳市","title":"吕天逸","clickcount":0,"likecount":0,"tag":"音乐","commentcount":0,"addtime":1598940320,"ypid":"YP_3jc2t07tspshl04s1kitiiu107","avatar":"http:\/\/img.expertcp.com\/api\/202003\/202003261652580121448.jpg","nickname":"艾米","gzid":0,"options":null,"isvote":0,"isowner":"0"}]},{"day":"31","month":"08","list":[{"likeid":0,"type":4,"id":284,"content":"{\"pic\":\"http:\\\/\\\/qukufile2.qianqian.com\\\/data2\\\/pic\\\/b733a1a9fc0f63c7015be29b7b840b66\\\/672866107\\\/672866107.jpg@s_2,w_500,h_500\",\"title\":\"\\u6865\\u8fb9\\u59d1\\u5a18\",\"song_id\":\"672865438\",\"author\":\"\\u821e\\u8e48\\u5973\\u795e\\u8bfa\\u6db5\"}","province":null,"city":"贵阳市","title":"你们","clickcount":0,"likecount":0,"tag":"音乐","commentcount":0,"addtime":1598866113,"ypid":"YP_3jc2t07tspshl04s1kitiiu107","avatar":"http:\/\/img.expertcp.com\/api\/202003\/202003261652580121448.jpg","nickname":"艾米","gzid":0,"options":null,"isvote":0,"isowner":"0"}]},{"day":"29","month":"08","list":[{"likeid":0,"type":2,"id":283,"content":"[\"http:\\\/\\\/img.expertcp.com\\\/api\\\/202008\\\/202008291214440154326.png\",\"http:\\\/\\\/img.expertcp.com\\\/api\\\/202008\\\/202008291214450148558.png\"]","province":null,"city":"贵阳市","title":"漂亮","clickcount":0,"likecount":0,"tag":"美图","commentcount":0,"addtime":1598674487,"ypid":"YP_3jc2t07tspshl04s1kitiiu107","avatar":"http:\/\/img.expertcp.com\/api\/202003\/202003261652580121448.jpg","nickname":"艾米","gzid":0,"options":null,"ratio":0,"isvote":0,"isowner":"0"}]},{"day":"27","month":"08","list":[{"likeid":0,"type":4,"id":225,"content":"{\"pic\":\"http:\\\/\\\/qukufile2.qianqian.com\\\/data2\\\/pic\\\/0d359ec1be6f5365f92d4c83d3eeb022\\\/603758238\\\/603758238.jpg@s_2,w_500,h_500\",\"title\":\"\\u5927\\u9c7c\",\"song_id\":\"265715650\",\"author\":\"\\u5468\\u6df1\"}","province":null,"city":"贵阳市","title":"鱼","clickcount":0,"likecount":0,"tag":"音乐","commentcount":0,"addtime":1598509626,"ypid":"YP_3jc2t07tspshl04s1kitiiu107","avatar":"http:\/\/img.expertcp.com\/api\/202003\/202003261652580121448.jpg","nickname":"艾米","gzid":0,"options":null,"isvote":0,"isowner":"0"},{"likeid":0,"type":0,"id":218,"content":null,"province":null,"city":"贵阳市","title":"恶魔\n","clickcount":0,"likecount":0,"tag":null,"commentcount":0,"addtime":1598507817,"ypid":"YP_3jc2t07tspshl04s1kitiiu107","avatar":"http:\/\/img.expertcp.com\/api\/202003\/202003261652580121448.jpg","nickname":"艾米","gzid":0,"options":null,"isvote":0,"isowner":"0"},{"likeid":0,"type":0,"id":217,"content":null,"province":null,"city":"贵阳市","title":"恶魔\n","clickcount":0,"likecount":0,"tag":null,"commentcount":0,"addtime":1598507816,"ypid":"YP_3jc2t07tspshl04s1kitiiu107","avatar":"http:\/\/img.expertcp.com\/api\/202003\/202003261652580121448.jpg","nickname":"艾米","gzid":0,"options":null,"isvote":0,"isowner":"0"}]}]}]}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    public function my_circle()
    {
        $uid = $this->request->uid;
        $limit = $this->request->post('limit', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $ypid = $this->request->post('ypid');

        if (!$ypid) {
            return $this->ajaxReturn($this->errorCode, 'ypid必传');
        }
        $where = [];

        $where[] = ['a.type', 'in', [0, 2, 4]];
        $where[] = ['a.ypid', '=', $ypid];
        $field = 'a.imgs,a.pictures,c.member_id as likeid,a.member_id,type,a.id,content,province,city,title,clickcount,likecount,tag,commentcount,a.addtime,ypid,avatar,nickname,b.id as gzid,options';


        $orderby = 'a.addtime desc';
        $res = FriendCircleService::loadData($uid, $where, $field, $orderby, $limit, $page);
        $data = [];
        $new_data = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $year = date('Y', $val['addtime']);
                $month = date('m', $val['addtime']);
                $day = date('d', $val['addtime']);
                if (!isset($data[$year])) {
                    $year_info = [];
                    $year_info['year'] = $year;
                    $day_info = [];
                    $day_info['day'] = $day;
                    $day_info['month'] = $month;
                    $day_info['list'][] = $val;
                    $data[$year]["$month - $day"] = $day_info;
                } else {
                    if (isset($data[$year]["$month - $day"])) {
                        $data[$year]["$month - $day"]['list'][] = $val;
                    } else {
                        $day_info = [];
                        $day_info['day'] = $day;
                        $day_info['month'] = $month;
                        $day_info['list'][] = $val;
                        $data[$year]["$month - $day"] = $day_info;
                    }
                }
            }
            foreach ($data as $key => $val) {
                $year_info = [];
                $year_info['year'] = $key;
                $year_info['list'] = [];
                foreach ($val as $k => $v) {
                    $year_info['list'][] = $v;
                }
                $new_data[] = $year_info;
            }
        }
        return $this->ajaxReturn($this->successCode, '返回成功', $new_data);
    }

    /**
     * @api {get} /NewFriendCircle/circle_like_list 03、朋友圈详情点赞列表
     * @apiGroup NewFriendCircle
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {int}            id 朋友圈id
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.is_like 1已点赞 0未点赞
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"返回成功","data":{"list":[{"friendcircleid":327,"member_id":13235,"avatar":"http:\/\/img.expertcp.com\/api\/202009\/202009021820570152330.png","nickname":"艾米"},{"friendcircleid":327,"member_id":13219,"avatar":"http:\/\/img.expertcp.com\/api\/202009\/202009011820010121273.png","nickname":"13322222222"}],"is_like":0}}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    public function circle_like_list()
    {
        $uid = $this->request->uid;
        $id = $this->request->post('id');
        $like_list = Db::name('friend_priase')->alias('a')->field('a.friendcircleid,b.member_id,b.ypid,b.avatar,b.nickname')->where("a.friendcircleid", $id)->join('member b', 'a.member_id = b.member_id')->select()->toArray();
        $is_like = 0;
        if ($like_list) {
            if (in_array($uid, array_column($like_list, 'member_id'))) {
                $is_like = 1;
            }
        }
        $data = [
            'list' => $like_list,
            'is_like' => $is_like
        ];
        return $this->ajaxReturn($this->successCode, '返回成功', $data);

    }


}