<?php 
/*
 module:		文件上传模型
 create_time:	2020-11-15 21:59:23
 author:		
 contact:		
*/

namespace app\admin\model;
use think\facade\Db;
use think\Model;

class FileStorage extends Model {


	protected $pk = 'id';

 	protected $name = 'file_storage';

 	public static function loadData($member_id,$page=1,$limit=20){
 	    $start = ($page-1)*$limit;

        $sql = 'select a.*,b.member_id as likeid from cd_file_storage a left join cd_video_zan b on a.id=b.videoid where a.isvideo=1 and  not exists (select * from cd_video_see b where a.id=b.video_id and b.member_id='.$member_id.")";
        $sql .=' limit '.$start.','.$limit;

        return Db::query($sql);

    }

    public static function getWhereInfo($where){
        $where= formatWhere($where);
        $result = Db::name('file_storage')->where($where)->find();
        return $result;
    }

}

