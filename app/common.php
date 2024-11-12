<?php
// +----------------------------------------------------------------------
// | 应用公共文件
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------


use think\facade\Db;
use think\facade\Log;
use GatewayWorker\Lib\Gateway;
error_reporting(0);
use app\api\service\PicEndeService;

function content_verify($content)
{
    $res = cache('baidu_token');
    if(empty($res)){
        $url = 'https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id=Ba0t0zfK6h9ViAKGSovBy6lj&client_secret=uzq19Eg523yw5qKVwxNqGCvTqZfgeUoA';
        $res = file_get_contents($url);
        $res = json_decode($res, TRUE);
        cache('baidu_token',$res, 2591000);
    }

    $access_token = $res['access_token'];
    $url = 'https://aip.baidubce.com/rest/2.0/solution/v1/text_censor/v2/user_defined';
    $post_data = [];
    $post_data['text'] = $content;
    $post_data['access_token'] = $access_token;

    $post_data = http_build_query($post_data);
    $res = request_post($url, $post_data);

    $res = json_decode($res, TRUE);
   if($res['conclusion']==1){
       return true;
   }
   return false;

    if ($res['conclusion'] == '不合规') {
        return -1;
    } else if ($res['conclusion'] == '疑似') {
        return -2;
    }
    return 1;

}

function getGroupAvatar($pic_list = array(), $is_save = false, $save_path = '')
{
    //验证参数
    if (empty($pic_list) || empty($save_path)) {
        return false;
    }
    if ($is_save) {
        //如果需要保存，需要传保存地址
        if (empty($save_path)) {
            return false;
        }
    }
    // 只操作前9个图片
    $pic_list = array_slice($pic_list, 0, 9);
    //设置背景图片宽高
    $bg_w = 150; // 背景图片宽度
    $bg_h = 150; // 背景图片高度
    //新建一个真彩色图像作为背景
    $background = imagecreatetruecolor($bg_w, $bg_h);
    //为真彩色画布创建白灰色背景，再设置为透明
    $color = imagecolorallocate($background, 236, 236, 236);
    imagefill($background, 0, 0, $color);
    imageColorTransparent($background, $color);
    //根据图片个数设置图片位置
    $pic_count = count($pic_list);
    $lineArr = array();//需要换行的位置
    $space_x = 3;
    $space_y = 3;
    $line_x = 0;
    switch ($pic_count) {
        case 1: // 正中间
            $start_x = intval($bg_w / 4); // 开始位置X
            $start_y = intval($bg_h / 4); // 开始位置Y
            $pic_w = intval($bg_w / 2); // 宽度
            $pic_h = intval($bg_h / 2); // 高度
            break;
        case 2: // 中间位置并排
            $start_x = 2;
            $start_y = intval($bg_h / 4) + 3;
            $pic_w = intval($bg_w / 2) - 5;
            $pic_h = intval($bg_h / 2) - 5;
            $space_x = 5;
            break;
        case 3:
            $start_x = 40; // 开始位置X
            $start_y = 5; // 开始位置Y
            $pic_w = intval($bg_w / 2) - 5; // 宽度
            $pic_h = intval($bg_h / 2) - 5; // 高度
            $lineArr = array(2);
            $line_x = 4;
            break;
        case 4:
            $start_x = 4; // 开始位置X
            $start_y = 5; // 开始位置Y
            $pic_w = intval($bg_w / 2) - 5; // 宽度
            $pic_h = intval($bg_h / 2) - 5; // 高度
            $lineArr = array(3);
            $line_x = 4;
            break;
        case 5:
            $start_x = 30; // 开始位置X
            $start_y = 30; // 开始位置Y
            $pic_w = intval($bg_w / 3) - 5; // 宽度
            $pic_h = intval($bg_h / 3) - 5; // 高度
            $lineArr = array(3);
            $line_x = 5;
            break;
        case 6:
            $start_x = 5; // 开始位置X
            $start_y = 30; // 开始位置Y
            $pic_w = intval($bg_w / 3) - 5; // 宽度
            $pic_h = intval($bg_h / 3) - 5; // 高度
            $lineArr = array(4);
            $line_x = 5;
            break;
        case 7:
            $start_x = 53; // 开始位置X
            $start_y = 5; // 开始位置Y
            $pic_w = intval($bg_w / 3) - 5; // 宽度
            $pic_h = intval($bg_h / 3) - 5; // 高度
            $lineArr = array(2, 5);
            $line_x = 5;
            break;
        case 8:
            $start_x = 30; // 开始位置X
            $start_y = 5; // 开始位置Y
            $pic_w = intval($bg_w / 3) - 5; // 宽度
            $pic_h = intval($bg_h / 3) - 5; // 高度
            $lineArr = array(3, 6);
            $line_x = 5;
            break;
        case 9:
            $start_x = 5; // 开始位置X
            $start_y = 5; // 开始位置Y
            $pic_w = intval($bg_w / 3) - 5; // 宽度
            $pic_h = intval($bg_h / 3) - 5; // 高度
            $lineArr = array(4, 7);
            $line_x = 5;
            break;
    }
    foreach( $pic_list as $k=>$pic_path ) {
        $kk = $k + 1;
        if ( in_array($kk, $lineArr) ) {
            $start_x = $line_x;
            $start_y = $start_y + $pic_h + $space_y;
        }
        $pathInfo = pathinfo($pic_path);
        switch( strtolower($pathInfo['extension']) ) {
            case 'jpg':
            case 'jpeg':
                $imagecreatefromjpeg = 'imagecreatefromjpeg';
                break;
            case 'png':
                $imagecreatefromjpeg = 'imagecreatefrompng';
                break;
            case 'gif':
            default:
                $imagecreatefromjpeg = 'imagecreatefromstring';
                $pic_path = file_get_contents($pic_path);
                break;
        }
        $resource = $imagecreatefromjpeg($pic_path);
        // $start_x,$start_y copy图片在背景中的位置
        // 0,0 被copy图片的位置
        // $pic_w,$pic_h copy后的高度和宽度
        imagecopyresized($background,$resource,$start_x,$start_y,0,0,$pic_w,$pic_h,imagesx($resource),imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
        $start_x = $start_x + $pic_w + $space_x;
    }
    if ($is_save) {

        $dir = pathinfo($save_path, PATHINFO_DIRNAME);

        if (!is_dir($dir)) {

            $file_create_res = mkdir($dir, 0777, true);
            if (!$file_create_res) {
                return false;//没有创建成功
            }
        }
        $res = imagejpeg($background, $save_path);
        $img = radius_img($save_path,20);
        imagejpeg($img, $save_path);
//        return config('my.home_domain').$save_path;

        $result = write_to_oss($save_path);
        if ($result != false) {
            unlink($save_path);
            return $result;
        } else {
            return false;
        }

    } else {
        //直接输出
        header("Content-type: image/jpg");
        imagejpeg($background);
        imagedestroy($background);
    }
}
function sendchatmsg($data)
{
    Gateway::$registerAddress = '127.0.0.1:1233';
    Gateway::$persistentConnection = false;
    Gateway::sendToAll(json_encode($data));
}

function sendchatUid($uid, $data)
{
    Gateway::$registerAddress = '127.0.0.1:1233';
    Gateway::$persistentConnection = false;
    Gateway::sendToUid($uid, json_encode($data));
}

/**
 * todo  个推推送客服app
 * @param $cid
 * @param $platform
 * @param $type_content
 * @param string $type 类别
 * @param string $type_data 跳转类别所需要的数据
 */
function push_geTui($cid, $platform, $type_content, $type = 'message', $type_data = '')
{
    $pack_name = config('ke_fu_service_push.pack_name');
    $v_name = '通知';
    $payload = json_encode(array('type' => $type, 'data' => $type_data));
    $igt = \app\api\controller\UniPush::get_ke_fu_Igt();
    $result = \app\api\controller\UniPush::send($igt, $cid, $platform, $pack_name, $v_name, $type_content, $payload);
}

/**
 * 数组生成树结构
 * @param $items 要操作的数组
 * @param string $field pid
 * @param string $list son
 * @return array
 */
function generateTree($items, $field = 'pid', $list = 'son', $pkid = 'id')
{
    $tree = array();
    foreach ($items as $item) {
        if (isset($items[$item[$field]])) {
            $items[$item[$field]][$list][] = &$items[$item[$pkid]];
        } else {
            $tree[] = &$items[$item[$pkid]];
        }
    }
    return $tree;
}

/**
 * 随机字符
 * @param int $length 长度
 * @param string $type 类型
 * @param int $convert 转换大小写 1大写 0小写
 * @return string
 */
function random($length = 10, $type = 'letter', $convert = 0)
{
    $config = array(
        'number' => '1234567890',
        'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string' => 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );

    if (!isset($config[$type])) $type = 'letter';
    $string = $config[$type];

    $code = '';
    $strlen = strlen($string) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $string{mt_rand(0, $strlen)};
    }
    if (!empty($convert)) {
        $code = ($convert > 0) ? strtoupper($code) : strtolower($code);
    }
    return $code;
}

/*
 * 生成交易流水号
 * @param char(2) $type
 */
function doOrderSn($type)
{
    return date('YmdHis') . $type . substr(microtime(), 2, 3) . sprintf('%02d', rand(0, 99));
}


function deldir($dir)
{
//先删除目录下的文件：
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
            $fullpath = $dir . "/" . $file;
            if (!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }

    closedir($dh);
    //删除当前文件夹：
    if (rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}


/**
 * 数据签名认证
 * @param  array $data 被认证的数据
 * @return string       签名
 */
function data_auth_sign($data)
{
    //数据类型检测
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

//通过字段值获取字段配置的名称
function getFieldVal($val, $fieldConfig)
{
    if ($fieldConfig) {
        foreach (explode(',', $fieldConfig) as $k => $v) {
            $tempstr = explode('|', $v);
            foreach (explode(',', $val) as $m => $n) {
                if ($tempstr[1] == $n) {
                    $fieldvals .= $tempstr[0] . ',';
                }
            }

        }
        return rtrim($fieldvals, ',');
    }
}


//通过字段名称获取字段配置的值
function getFieldName($val, $fieldConfig)
{
    if ($fieldConfig) {
        foreach (explode(',', $fieldConfig) as $k => $v) {
            $tempstr = explode('|', $v);
            if ($tempstr[0] == $val) {
                $fieldval = $tempstr[1];
            }
        }
        return $fieldval;
    }
}


//通过键值返回键名
function getKeyByVal($array, $data)
{
    foreach ($array as $key => $val) {
        if ($val == $data) {
            $data = $key;
        }
    }
    return $data;
}


//导出时候当有三级联动字段的时候 需要将查询字段重载
function formartExportWhere($field)
{
    foreach ($field as $k => $v) {
        if (strpos($v, '|') > 0) {
            $dt = $field[$k];
            unset($field[$k]);
        }
    }

    return \xhadmin\CommonService::filterEmptyArray(array_merge($field, explode('|', $dt)));
}


/*格式化列表*/
function formartList($fieldConfig, $list)
{
    $cat = new \org\Category($fieldConfig);
    $ret = $cat->getTree($list);
    return $ret;
}

/*写入
* @param  string  $type 1 为生成控制器
*/

function filePutContents($content, $filepath, $type)
{
    if (in_array($type, [1, 3])) {
        $str = file_get_contents($filepath);
        $parten = '/\s\/\*+start\*+\/(.*)\/\*+end\*+\//iUs';
        preg_match_all($parten, $str, $all);
        if ($all[0]) {
            foreach ($all[0] as $key => $val) {
                $ext_content .= $val . "\n\n";
            }
        }

        $content .= $ext_content . "\n\n";
        if ($type == 1) {
            $content .= "}\n\n";
        }
    }

    ob_start();
    echo $content;
    $_cache = ob_get_contents();
    ob_end_clean();

    if ($_cache) {
        $File = new \think\template\driver\File();
        $File->write($filepath, $_cache);
    }
}

function htmlOutList($list, $err_status = false)
{
    foreach ($list as $key => $row) {
        $res[$key] = checkData($row, $err_status);
    }
    return $res;
}

//err_status  没有数据是否抛出异常 true 是 false 否
function checkData($data, $err_status = true)
{
    if (empty($data) && $err_status) {
        abort(412, '没有数据');
    }

    if (is_object($data)) {
        $data = $data->toArray();
    }

    foreach ($data as $k => $v) {
        if ($v && is_array($v)) {
            $data[$k] = checkData($v);
        } else {
            $data[$k] = ($v);
        }
    }
    return $data;

}

//html代码输入
function html_in($str)
{
    $str = htmlspecialchars($str);
    $str = strip_tags($str);
    $str = addslashes($str);
    return $str;
}


//html代码输出
function html_out($str)
{
    if (is_string($str)) {
        if (function_exists('htmlspecialchars_decode')) {
            $str = htmlspecialchars_decode($str);
        } else {
            $str = html_entity_decode($str);
        }
        $str = stripslashes($str);
    }
    return $str;
}

//后台sql输入框语句过滤
function sql_replace($str)
{
    $farr = ["/insert[\s]+|update[\s]+|create[\s]+|alter[\s]+|delete[\s]+|drop[\s]+|load_file|outfile|dump/is"];
    $str = preg_replace($farr, '', $str);
    return $str;
}

//上传文件黑名单过滤
function upload_replace($str)
{
    $farr = ["/php|php3|php4|php5|phtml|pht|/is"];
    $str = preg_replace($farr, '', $str);
    return $str;
}

//查询方法过滤
function serach_in($str)
{
    $farr = ["/^select[\s]+|insert[\s]+|and[\s]+|or[\s]+|create[\s]+|update[\s]+|delete[\s]+|alter[\s]+|count[\s]+|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/i"];
    $str = preg_replace($farr, '', html_in($str));
    return trim($str);
}


//返回字段定义的时间格式
function getTimeFormat($val)
{
    $default_time_format = explode('|', $val['default_value']);
    $time_format = $default_time_format[0];
    if (!$time_format || $val['default_value'] == 'null') {
        $time_format = 'Y-m-d H:i:s';
    }
    return $time_format;
}

/**
 * 过滤掉空的数组
 * @access protected
 * @param  array $data 数据
 * @return array
 */
function filterEmptyArray($data = [])
{
    foreach ($data as $k => $v) {
        if (!$v && $v !== 0)
            unset($data[$k]);
    }
    return $data;
}

/**
 * tp官方数组查询方法废弃，数组转化为现有支持的查询方法
 * @param array $data 原始查询条件
 * @return array
 */
function formatWhere($data)
{
    $where = [];
    foreach ($data as $k => $v) {
        if (is_array($v)) {
            if (((string)$v[1] <> null && !is_array($v[1])) || (is_array($v[1]) && (string)$v[1][0] <> null)) {
                switch (strtolower($v[0])) {
                    //模糊查询
                    case 'like':
                        $v[1] = '%' . $v[1] . '%';
                        break;

                    //表达式查询
                    case 'exp':
                        $v[1] = Db::raw($v[1]);
                        break;
                }
                $where[] = [$k, $v[0], $v[1]];
            }
        } else {
            if ((string)$v != null) {
                $where[] = [$k, '=', $v];
            }
        }
    }
    return $where;
}

//导出excel表头设置
function getTag($key3, $no = 100)
{
    $data = [];
    $key = ord("A");//A--65
    $key2 = ord("@");//@--64
    for ($n = 1; $n <= $no; $n++) {
        if ($key > ord("Z")) {
            $key2 += 1;
            $key = ord("A");
            $data[$n] = chr($key2) . chr($key);//超过26个字母时才会启用
        } else {
            if ($key2 >= ord("A")) {
                $data[$n] = chr($key2) . chr($key);//超过26个字母时才会启用
            } else {
                $data[$n] = chr($key);
            }
        }
        $key += 1;
    }
    return $data[$key3];
}

/**
 * 实例化数据库类
 * @param string $name 操作的数据表名称（不含前缀）
 * @param array|string $config 数据库配置参数
 * @param bool $force 是否强制重新连接
 * @return \think\db\Query
 */
if (!function_exists('db')) {
    function db($name = '')
    {
        return Db::connect('mysql', false)->name($name);
    }
}

function error($errorcode = 1)
{
    $errorConfig = array(
        1 => '未知错误',
        2 => '服务暂不可用',
        3 => '未知的方法',
        5 => '请求来自未经授权的IP地址',
        6 => '无权限访问该用户数据',
        7 => '来自该refer的请求无访问权限',
        8 => '您不是群主无法修改群名称',
        9 => '群名字不能为空',
        10 => '您不是群主无法修改群公告',
        100 => '请求参数无效',
        101 => 'api key无效',
        104 => '无效签名',
        105 => '请求参数过多',
        107 => 'timestamp参数无效',
        110 => '无效的access token',
        111 => 'access token过期',
        114 => '无效的ip参数',
        202 => '您的账号已经冻结，请联系管理员',
        203 => '该群聊不存在',
        204 => '您不是群成员，无法操作'
    );
    if (!key_exists($errorcode, $errorConfig)) {
        $errorcode = 1;
    }
    $arr = ['status' => $errorcode, 'msg' => $errorConfig[$errorcode]];
    if (!empty($data)) {
        $arr['data'] = $data;
    }
    return json($arr);
}

function removeEmoji($text)
{
    $clean_text = "";
    // Match Emoticons
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '', $text);
    // Match Miscellaneous Symbols and Pictographs
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '', $clean_text);
    // Match Transport And Map Symbols
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '', $clean_text);
    // Match Miscellaneous Symbols
    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $clean_text = preg_replace($regexMisc, '', $clean_text);
    // Match Dingbats
    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);
    return $clean_text;
}


function curl_post($url, $data = array(), $token = '')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    // POST数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // 把post的变量加上
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $output = curl_exec($ch);
    curl_close($ch);
    return $output;

}

function _checkPwLevel($pw)
{

    if (empty($pw)) {
        return 0;
    }

    $pattern['weak'] = '/((^[0-9]{6,})|(^[a-z]{6,})|(^[A-Z]{6,}))$/';

    $pattern['middle'] = '/((^[0-9,a-z]{6,})|(^[0-9,A-Z]{6,})|(^[a-z,A-Z]{6,}))$/';

    $pattern['strong'] = '/^[\x21-\x7e,A-Za-z0-9]{6,}/';

    $key = '';

    foreach ($pattern as $k => $v) {

        $res = preg_match($v, $pw);

        if ($res) {

            $key = $k;

            break;

        }

    }

    switch ($key) {

        case 'weak':

            return 3;

        case 'middle':

            return 2;

        case 'strong':

            return 1;

        default:

            return 0;

    }

}

function success($msg = '操作成功', $data = array())
{
    $arr = ['status' => config('my.successCode'), 'msg' => $msg];
    $arr['data'] = $data;
    return json($arr);
}

function uuid()
{
    return md5(uniqid(md5(microtime(true)), true));
}

function createQQPic($pic_list, $bg_w = 100, $bg_h = 100)
{
    /*    $pic_list  = array(
            'http://img.expertcp.com/admin/202010/202010050918450122685.png',
            'http://img.expertcp.com/admin/202010/202010050940130199959.png',
            'http://img.expertcp.com/admin/202010/202010051816220196021.png',
            'http://img.expertcp.com/admin/202010/202010051816220196021.png',
        );*/
    if (count($pic_list) < 2) {
        return false;
    }
    $background = imagecreatetruecolor($bg_w, $bg_h);
    $color = imagecolorallocate($background, 255, 255, 255);
    imagefill($background, 0, 0, $color);
    imageColorTransparent($background, $color);

    $pic_w = $bg_w / 2;
    $pic_h = $bg_h;
    $start_x = 0;
    $start_y = 0;
    $pic_list = array_slice($pic_list, 0, 4); // 只操作前4个图片
    $count = count($pic_list);
    switch ($count) {
        case 2:
            $pic_path = file_get_contents($pic_list[0]);
            $resource = imagecreatefromstring($pic_path);
            imagecopyresized($background, $resource, $start_x, 0, 0, 0, $pic_w - 1, $pic_h, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            $pic_path = file_get_contents($pic_list[1]);
            $resource = imagecreatefromstring($pic_path);
            imagecopyresized($background, $resource, $pic_w, 0, 0, 0, $pic_w, $pic_h, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度

            break;
        case 3:
            $pic_path = file_get_contents($pic_list[0]);
            $resource = imagecreatefromstring($pic_path);
            imagecopyresized($background, $resource, $start_x, 0, 0, 0, $pic_w - 2, $pic_h, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            $pic_path = file_get_contents($pic_list[1]);
            $resource = imagecreatefromstring($pic_path);
            imagecopyresized($background, $resource, $pic_w, $start_y, 0, 0, $pic_w, $pic_h / 2 - 1, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            $pic_path = file_get_contents($pic_list[2]);

            $resource = imagecreatefromstring($pic_path);
            imagecopyresized($background, $resource, $pic_w, $pic_h / 2 + 1, 0, 0, $pic_w, $pic_h / 2, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            break;
        case 4:
            $pic_path = file_get_contents($pic_list[0]);
            $resource = imagecreatefromstring($pic_path);
            imagecopyresized($background, $resource, $start_x, $start_y, 0, 0, $pic_w - 1, $pic_h / 2 - 1, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            $pic_path = file_get_contents($pic_list[1]);
            $resource = imagecreatefromstring($pic_path);
            imagecopyresized($background, $resource, $pic_w, $start_y, 0, 0, $pic_w, $pic_h / 2 - 1, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            $pic_path = file_get_contents($pic_list[2]);
            $resource = imagecreatefromstring($pic_path);
            imagecopyresized($background, $resource, 0, $pic_w, 0, 0, $pic_w, $pic_h / 2 - 1, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度

            $pic_path = file_get_contents($pic_list[3]);
            $resource = imagecreatefromstring($pic_path);
            imagecopyresized($background, $resource, $pic_w, $pic_w, 0, 0, $pic_w, $pic_h / 2 - 1, imagesx($resource), imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            break;

    }
    $picname = realpath('temp/') . '/pic_' . uniqid() . '.jpg';
    imagejpeg($background, $picname);

    $img = radius_img($picname);

    imagejpeg($img, $picname, $picname);
    $result = write_to_oss($picname);
    if ($result != false) {
        unlink($picname);
        return $result;
    } else {
        return false;
    }
}

function write_to_oss($local_file_name, $base_file = 'qq_pic')
{
    $file_md5 = md5_file($local_file_name);

    $storage = \app\admin\model\FileStorage::getWhereInfo(['md5' => $file_md5]);
    if ($storage) {
        return $storage['oss'];
    } else {
        $suffix = strrchr($local_file_name, '.');
        $md5_name = $base_file . '/' . $file_md5 . $suffix;
        $config = config('my');
        $oss_client = new \OSS\OssClient($config['ali_oss_accessKeyId'], $config['ali_oss_accessKeySecret'], $config['ali_oss_endpoint']);
        $uploadToAliyunOss = $oss_client->uploadFile($config['ali_oss_bucket'], $md5_name, $local_file_name);
        if ($uploadToAliyunOss) {
            // 上传成功返回路径
            $storage_info = [];
            $storage_info['md5'] = $file_md5;
            $storage_info['oss'] = 'http://img.expertcp.com/' . $md5_name;
            $storage_info['created_at'] = time();
            $re = \app\admin\model\FileStorage::create($storage_info);
            if ($re !== false) {
                return $storage_info['oss'];
            } else {
                return false;
            }
        } else {
            // 上传失败，打印错误信息
            halt($uploadToAliyunOss);
            return false;
        }
    }

}

function write_to_oss_sec($local_file_name, $base_file = 'qq_pic', $file_name)
{
    $file_md5 = md5_file($local_file_name);

    $storage = \xhadmin\db\FileStorage::getWhereInfo(['md5' => $file_md5]);
    if ($storage) {
        if($storage['status']==1){
            echo $local_file_name;
            if(file_exists($local_file_name))
                @unlink($local_file_name);
        }
        return 1;
    } else {
        $suffix = strrchr($local_file_name, '.');
        $md5_name = $base_file . '/' . $file_md5 . $suffix;
        $config = config('my');

        $storage_info = [];
        $storage_info['md5'] = $file_md5;
        $storage_info['oss'] = '';
//        $storage_info['oss'] = 'http://img.expertcp.com/' . $md5_name;
        $storage_info['created_at'] = time();
        $file_n = explode('.', $file_name);
        $storage_info['title'] = is_array($file_n) ? $file_n[0] : '';
        $temp_file_name = explode('\\',$storage_info['title']);
        $storage_info['type']= $temp_file_name[1];
        $storage_info['title']= $temp_file_name[2];
        $re = \xhadmin\db\FileStorage::createData($storage_info);

        if ($re !== false) {
            $oss_client = new \OSS\OssClient($config['ali_oss_accessKeyId'], $config['ali_oss_accessKeySecret'], $config['ali_oss_endpoint']);

            $uploadToAliyunOss = $oss_client->uploadFile($config['ali_oss_bucket'], $md5_name, $local_file_name);

            if ($uploadToAliyunOss) {
                // 上传成功返回路径
                $storage_info = [];
                $storage_info['id'] = $re;
                $storage_info['oss'] =config('my.ali_oss_base_url') . $md5_name;
                $storage_info['status'] = 1;
                $re = \xhadmin\db\FileStorage::edit($storage_info);
                if ($re !== false) {
                    unlink($local_file_name);
                    return $storage_info['oss'];
                } else {
                    return 0;
                }
                return $storage_info['oss'];
            }  else {
                // 上传失败，打印错误信息
                halt($uploadToAliyunOss);
                return 0;
            }


        }
    }

}

function radius_img($imgpath = './t.png', $radius = 15)
{
    $ext = pathinfo($imgpath);
    $src_img = null;
    switch ($ext['extension']) {
        case 'jpg':
            $src_img = imagecreatefromjpeg($imgpath);
            break;
        case 'png':
            $src_img = imagecreatefrompng($imgpath);
            break;
    }
    $wh = getimagesize($imgpath);
    $w = $wh[0];
    $h = $wh[1];
    // $radius = $radius == 0 ? (min($w, $h) / 2) : $radius;
    $img = imagecreatetruecolor($w, $h);
    //这一句一定要有
    imagesavealpha($img, true);
    //拾取一个完全透明的颜色,最后一个参数127为全透明
    $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
    imagefill($img, 0, 0, $bg);
    $r = $radius; //圆 角半径
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            $rgbColor = imagecolorat($src_img, $x, $y);
            if (($x >= $radius && $x <= ($w - $radius)) || ($y >= $radius && $y <= ($h - $radius))) {
                //不在四角的范围内,直接画
                imagesetpixel($img, $x, $y, $rgbColor);
            } else {
                //在四角的范围内选择画
                //上左
                $y_x = $r; //圆心X坐标
                $y_y = $r; //圆心Y坐标
                if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
                //上右
                $y_x = $w - $r; //圆心X坐标
                $y_y = $r; //圆心Y坐标
                if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
                //下左
                $y_x = $r; //圆心X坐标
                $y_y = $h - $r; //圆心Y坐标
                if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
                //下右
                $y_x = $w - $r; //圆心X坐标
                $y_y = $h - $r; //圆心Y坐标
                if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
    }
    return $img;
}

function isnull($str){
    if(is_null($str)||$str==''){
        return true;
    }
    return false;
}


function ip()
{
    $ip = '未知IP';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return is_ip($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : $ip;
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return is_ip($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $ip;
    } else {
        return is_ip($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $ip;
    }
}
function is_ip($str)
{
    $ip = explode('.', $str);
    for ($i = 0; $i < count($ip); $i++) {
        if ($ip[$i] > 255) {
            return false;
        }
    }
    return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $str);
}

function getip() {
    static $realip;
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $realip = $_SERVER['REMOTE_ADDR'];
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
    return $realip;
}

function request_post($url = '', $param = '')
{
    if (empty($url) || empty($param)) {
        return false;
    }

    $postUrl = $url;
    $curlPost = $param;
    $curl = curl_init();//初始化curl
    curl_setopt($curl, CURLOPT_URL, $postUrl);//抓取指定网页
    curl_setopt($curl, CURLOPT_HEADER, 0);//设置header
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($curl);//运行curl
    curl_close($curl);

    return $data;
}


/**
 * 写结果缓存文件
 *
 * @params string $cache_name
 * @params string $caches
 *
 * @return
 */
function write_static_cache($cache_name, $caches)
{
    if ((DEBUG_MODE & 2) == 2)
    {
        return false;
    }
    $path = root_path();
    $cache_file_path = $path . "/runtime/" . $cache_name . ".php";
    $content = "<?php
";
    $content .= '$data = ' . var_export($caches, true) . ";
";
    $content .= "?>";
    file_put_contents($cache_file_path, $content, LOCK_EX);
}

/**
 * 读结果缓存文件
 *
 * @params string $cache_name
 *
 * @return array  $data
 */
function read_static_cache($cache_name,$time=0)
{
    if ((DEBUG_MODE & 2) == 2)
    {
        return false;
    }
    static $result = array();
    if (!empty($result[$cache_name]))
    {
        return $result[$cache_name];
    }
    $path = root_path();
    $cache_file_path = $path . "/runtime/" . $cache_name . ".php";
    if (file_exists($cache_file_path))
    {
        if($time>0) {
            $timex = filemtime($cache_file_path);
            $now = time();
            $uptime = $now-$timex;
            if($uptime>$time){
                return false;
            }

        }

        include_once($cache_file_path);
        $result[$cache_name] = $data;
        return $result[$cache_name];
    }
    else
    {
        return false;
    }
}


function setValueToArr($file,$data){
    $filelist = explode(',',$file);
    $relist = array();
    foreach($filelist as $key=>$v){
        $relist[$v]=$data[$v];
    }
    return $relist;
}


function create_invite_code() {
    $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $rand = $code[rand(0,25)]
        .strtoupper(dechex(date('m')))
        .date('d')
        .substr(time(),-5)
        .substr(microtime(),2,5)
        .sprintf('%02d',rand(0,99));
    for(
        $a = md5( $rand, true ),
        $s = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        $d = '',
        $f = 0;
        $f < 10;
        $g = ord( $a[ $f ] ),
        $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
        $f++
    );
    return $d;
}


function getSysConfig($key,$ttl=20){
    $where[] = ['name','=',$key];
    $res=Db::name('config')->where($where)->cache($ttl)->find();
    return $res['data'];
}

function addPayLog($infomember,$coin,$money,$type,$trantype,$desc,$ordernunber='',$blancetype=1){
    if(empty($money))return false;
    $ordernunber = empty($ordernunber)?date('YmdHis').mt_rand(1000,9999):$ordernunber;
    $uid = $infomember['member_id'];
    $data = array(
        'member_id'=>$uid,
        'mobile'=>$infomember['mobile'],
        'des'=>$desc,
        'balance'=>$coin,
        'money'=>$money,
        'type'=>$type,
        'trantype'=>$trantype,
        'ip'=>ip(),
        'blancetype'=>$blancetype,
        'trade_no'=>$ordernunber,
        'dateline'=>time()
    );
    try {
        Db::name('moneylog')->insertGetId($data);
        return true;
    }catch (\Exception $e){
        return false;
    }

}

/**
 * 取得配置信息
 *  @param string key配置KEY
 *  @param string isgetlist 是格式化配置
 *
 */
function getSetConfig($key,$isgetlist = 0){
    $whereb[] = ['name','=',$key];
    $configInfo =  Db::name('config')->where($whereb)->field('*')->find();
    if(!empty($configInfo)) {
        $configdata = htmlspecialchars_decode($configInfo['data']);
        if($isgetlist==2){
            $config = str_replace("：",":",$configdata);
            $datas = explode(',',$config);
            return $datas;
        }
        if($isgetlist==3){
            $datas = explode('|',$configdata);
            return $datas;
        }
        if($isgetlist==1){
            $rearr = array();
            $config = $configdata;
            $config = str_replace("：",":",$config);
            $datas = explode('|',$config);
            if(!empty($datas)){
                $i = 1;
                foreach($datas as $key=>$v){
                    $rearr[$i] = explode(':',$v);
                    $i++;
                }
            }
            return $rearr;
        }else{
            return $configdata;
        }

    }else{
        return '';
    }
}

//用来处理隐藏部分文字的相关函数
function strmySplit($str, $num = 4) {
    $len = mb_strlen($str, 'utf-8'); //获取字符串长度 每个汉字算1
    $partNum = ceil($len / $num);
    $arr = array();
    for ($i = 0; $i < $partNum; $i++) {
        $begin = $i * $num;
        $arr[] = iconv_substr($str, $begin, $num, 'utf-8');
    }
    return $arr;
}

function hiden_mymoblie($str) {
    if (empty($str))
        return $str;
    $string = $str;
    $pattern = "/(1\d{1,2})\d\d(\d{0,3})/";
    $replacement = "\$1*****\$3";
    $restr = strlen($str) == 11 ? preg_replace($pattern, $replacement, $string) : $str;
    return substr_count($restr, '*****') ? $restr : hidenmyword($str);
}

function hidenmyword($str) {
    $arr = strmySplit($str, 1);
    $strlen = count($arr);
    $stcut = floor($strlen / 2);
    $restr = '';
    for ($i = 0; $i < $strlen; $i++) {
        $restr.=$i >= $stcut ? '*' : $arr[$i];
    }
    return $restr;
}


/*
 * 取得用户nick
 *
 * */
function getnick($memberinfo){

    if(!empty($memberinfo['nickname'])) return $memberinfo['nickname'];
    if(!empty($memberinfo['mobile'])&&empty($memberinfo['membername'])){
        return hiden_mymoblie($memberinfo['mobile']);
    }

    if(empty($memberinfo['mobile'])&&!empty($memberinfo['membername'])){
        return hidenmyword($memberinfo['membername']);
    }

}

/*
 * 发送消息
 * $mtype (1 系统，2其它，3好友消息) $rememberinfo接收用户相关信息，$content 内容,$sendmemberinfo 发送者信息
 * */
function sendToUser($rememberinfo,$content,$sendmemberinfo,$mtype=1){
    if(!empty($sendmemberinfo)){
        $sendmemberinfo['nick'] = getnick($sendmemberinfo);
    }
    if($mtype==1||$mtype==4){
        if(empty($sendmemberinfo['nick'])){
            $sendmemberinfo['nick']='系统消息';
        }
        if(empty($sendmemberinfo['member_id'])){
            $sendmemberinfo['member_id']=0;
        }
        if(empty($sendmemberinfo['avatar'])){
            $sendmemberinfo['avatar']=0;
        }
    }

    if(!empty($rememberinfo)){
        $rememberinfo['nick'] = getnick($rememberinfo);
    }

    $data = array(
        'member_id'=>$rememberinfo['member_id'],
        'nick'=>$rememberinfo['nick'],
        'mtype'=>$mtype,
        'status'=>0,
        'content'=>$content,
        'sendmember_id'=>$sendmemberinfo['member_id'],
        'sendnick'=>$sendmemberinfo['nick'],
        'dateline'=>time(),
        'repic'=>$rememberinfo['pic'],
        'spic'=>$sendmemberinfo['pic'],
        'apstatus'=>0,
        'updateline'=>time()
    );
    
    $date = date('Y-m-d');
    $time = strtotime($date);
    $sql="SELECT COUNT(*) as total FROM `cd_mysg` WHERE member_id=".$rememberinfo['member_id']." AND dateline>=".$time;
    $result = Db::query($sql);
    if($result[0]['total']>10){
        return;
    }
    \app\admin\model\Mysg::create($data);
}

function isMobileOrEmail($phonenumber){
    if(preg_match("/^1[345678]{1}\d{9}$/",$phonenumber)){
        return 1;
    }else{
        if (filter_var($phonenumber, FILTER_VALIDATE_EMAIL))
        {
            return 2;
        }
    }
    return 0;
}

function getUtype(){

  $rearr=[];
  $rearr['1']=array(
      'title'=>'普通会员',
      'entitle'=>''
  );
    $rearr['2']=array(
        'title'=>'渠道会员',
        'entitle'=>''
    );
    $rearr['3']=array(
        'title'=>'团长',
        'entitle'=>''
    );
    return $rearr;
}

//接口返回
function reDecodejson($res,$debug=0){

    if($debug){
        return json($res);
    }
    $content = json_encode($res);
    $content = PicEndeService::encrypt($content);
    return json($content);
}