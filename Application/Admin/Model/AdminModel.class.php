<?php
namespace Admin\Model;

class AdminModel
{
    protected $_db_admin_user;
    public $_group_config = array('1'=>'普通管理员','2'=>'超级管理员'); 	//分组
    
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
    
    /**
     * 修改用户密码
     * @author nj
     * @param int $uid 用户id
     * @param string $pwd 新密码
     * @return bool $res 结果
     */
    public function updatePwd($uid,$pwd){
    	$data['pwd'] = md5($pwd);
    	$res = $this -> _db_admin_user -> where("id=$uid") -> save($data);
    	return $res;
    }
    
    /**
     * 添加新用户
     * @param string $email 邮箱
     * @param string $name 用户名
     * @param string $pwd 密码
     * @param int $groupid 组
     * @return
     */
	public function addUser($email,$name,$pwd,$groupid){
 		$data['email'] = $email;
 		$data['name']  = $name;
 		$data['pwd']  = md5($pwd);
 		$data['groupid']  = $groupid;
		
		return $this -> _db_admin_user -> filter('strip_tags') -> add($data);
	}
    
	/**
	 * 通过用户名查询用户信息
	 * @author nj 2015-7-30
	 * @param string $name 用户名
	 * @return $res 
	 */
	public function getUserInfoByname($name){
		$res = $this -> _db_admin_user -> where("name='{$name}'") -> find();
		
		return $res;
	}
	
	/**
	 * 通过邮箱查询用户信息
	 * @author nj 2015-7-30
	 * @param string $email 用户名
	 * @return $res
	 */
	public function getUserInfoByemail($email){
		$res = $this -> _db_admin_user -> where("email='{$email}'") -> find();
	
		return $res;
	}
	
	public function getAllUser(){
		$res = $this -> _db_admin_user -> select();
		
		return $res;
	}
	
	/**
	 * 删除用户
	 * @author nj 2015-7-30
	 * @param int $id 用户id
	 * @return $res
	 */
	public function deleteById($id){
		$res = $this -> _db_admin_user -> where("id='{$id}'") -> delete();
		return $res;
	}
	
	/**
	 * 更新用户组
	 * @author nj 2015-7-31
	 * @param int $id 用户id
	 * @param int $groupid 组id
	 * @return $res
	 */
	public function updateGroupById($id,$groupid){
		$data['groupid'] = $groupid;
		$res = $this -> _db_admin_user -> where("id='{$id}'") -> save($data);

		return $res;
	}
}