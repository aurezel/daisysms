<?php

namespace app\ApplicationName\controller;

class Base extends Admin
{
   
    /*修改密码*/
    public function password(){
	    if (!$this->request->isPost()){	
			return view('password');
		}else{			
			$password = $this->request->post('password', '', 'strip_tags,trim');
			$data['pwd'] = md5($password.config('my.password_secrect'));
			$data['pk_id'] = session('ApplicationName.pk_id');
			try {
				db('tablename')->update($data);
			} catch (\Exception $e) {
				abort(config('my.error_log_code'),$e->getMessage());
			}
            return json(['status'=>'00','message'=>'修改成功']);
		}
    }
  
	

}
