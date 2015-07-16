<?php
namespace Admin\Model;

class AdminModel
{
    protected $_db_admin_user;
    
    public function __construct(){
        $this -> _db_admin_user = M('admin_user' , 'ad_' , 'DB0_CONFIG');
    }
    
    public function login($email , $pwd){
        $pwd = md5($pwd);
        $info = $this -> _db_admin_user -> where("email = '{$email}' AND pwd = '{$pwd}'") -> find();
        if($info){
            $this -> setSession($info);
            return true;
        }else 
            return false;
    }
    
    private function setSession($info){
        //设置session数据
        $_SESSION['admin_uid'] = $info['id'];
        $_SESSION['admin_user'] = $info;
    }
}