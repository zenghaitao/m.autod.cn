<?php
namespace API\Model;

class UserModel
{

    protected $_db_user;
    protected $_db_user_device;
    
    public function __construct(){
        $this -> _db_user_device = M('User_device' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_user = M('User' , 'ad_' , 'DB0_CONFIG');
    }
    
    /**
     * 根据设备ID来注册用户
     *
     * @param string $deviceID
     * @return bool
     */
	public function registerUserWithDeviceID($info)
    {
    	//创建一条新设备记录
    	$reg_id = $this -> _db_user_device -> add($info);
    	$user_id = 0;

    	return $reg_id;
    }
    
    /**
     * 获取注册设备信息
     *
     * @param int $reg_id
     * @return array
     */
    public function getRegisterDeviceInfo($reg_id){
        $info = $this -> _db_user_device -> where("reg_id = '{$reg_id}'") -> find();
        return $info;
    }
    
    
    /**
     * 用户初始化时需要重构的数据
     *
     * @param int $uid
     * @return bool
     */
    private function intiUser($uid){
        return $uid;
    }
    
    /**
     * 更新设备token值
     *
     * @param int $uid
     * @param string $device_id
     * @param string $device_token
     * @return bool
     */
    public function updateDeviceToken($reg_id , $device_token){
        
        $res = $this -> _db_user_device -> where("reg_id = {$reg_id}") -> save(array('device_token'=>$device_token));
        
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 获取用户信息
     *
     * @param int $uid
     * @return array
     */
    public function getUserInfo($uid){
        $info = $this -> _db_user -> where("id = '{$uid}'") -> find();
        if($info['pid'] != 0){
            $info = $this -> getUserInfo($info['pid']);
        }
        return $info;
    }
    
    /**
     * 根据设备注册id设置session
     *
     * @param int $reg_id
     * @return array
     */
    public function setSession($reg_id){
        //设置session数据
        $_SESSION['reg_id'] = $reg_id;
        $_SESSION['device'] = $this -> getRegisterDeviceInfo($reg_id);
        $_SESSION['user_id'] = $_SESSION['device']['uid'];
        $_SESSION['user'] = $this -> getUserInfo($_SESSION['user_id']);
        
        return $_SESSION['device'];
    }
    
    /**
     * 第三方用户绑定
     *
     * @param array $data
     */
    public function bind($reg_id , $data){
        //用户是否已被创建
        $info = $this -> _db_user -> where("open_id = '{$data['open_id']}'") -> find();
        if(!$info){
            //创建用户
            $data['add_time'] = time();
            $data['last_login'] = time();
            $uid = $this -> _db_user -> add($data);
            
        }else{
            //更新用户登录时间
            //$data['last_login'] = time();
            $this -> _db_user -> where("id = '{$info['id']}'") -> save(array('last_login' => time()));
            $uid = $info['id'];
        }
        //将此设备绑定到用户
        $res = $this -> _db_user_device -> where("reg_id = '{$reg_id}'") -> save(array('user_id' => $uid));
        
        return $uid;
    }
    
    /**
     * 解除绑定
     *
     * @param int $reg_id
     */
    public function unbind($reg_id){
        $res = $this -> _db_user_device -> where("reg_id = '{$reg_id}'") -> save(array('uid' => '0'));
        if($res !== false)
            return  true;
        else 
            return false;
    }
}
