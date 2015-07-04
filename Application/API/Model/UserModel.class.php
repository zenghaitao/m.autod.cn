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
     * 设置是否为用户推送
     *
     * @param int $reg_id
     * @param string $val
     * @return bool
     */
    public function updatePush($reg_id , $val){
        
        $res = $this -> _db_user_device -> where("reg_id = {$reg_id}") -> save(array('device_push'=>$val));
        
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
     * 获取机器人列表
     *
     * @return array
     */
    public function getRoot(){
        $list = $this -> _db_user -> where("is_root = 'yes'") -> select();
        return $list;
    }
    
    /**
     * 第三方用户绑定
     *
     * @param array $data
     */
    public function bind($reg_id , $platform , $token , $open_id){
        //用户是否已被创建
        $info = $this -> _db_user -> where("open_id = '{$open_id}' AND platform = '{$platform}'") -> find();
        if(!$info){
            //创建用户
            if($platform == 'weibo'){
                $data = $this -> weibo($token , $open_id);
            }
            if($platform == 'qq'){
                $data = $this -> qq($token , $open_id);
            }
            
            if(!$data)
                return false;
                
            $uid = $this -> _db_user -> add($data);            
        }else{
            //更新用户登录时间
            //$data['last_login'] = time();
            $this -> _db_user -> where("id = '{$info['id']}'") -> save(array('last_login' => time()));
            $uid = $info['id'];
        }
        //将此设备绑定到用户
        $res = $this -> _db_user_device -> where("reg_id = '{$reg_id}'") -> save(array('uid' => $uid));
        
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
    
    /*
     *绑定weibo的方法
     */
    private function weibo($token,$open_id) {
        
        include_once(dirname(__FILE__)."/saetv2.ex.class.php");
        
        $akey = '350665549';
        $skey = '04fe0953acd694fd939c89c9eafdd626';
        
        $sae = new SaeTClientV2($akey , $skey , $token);
        $info = $sae -> show_user_by_id($open_id);
        
        $data = array();
        if($info['idstr']){
            $data['open_id'] = $open_id;
            $data['name'] = $info['screen_name'];
            $data['access_token'] = $token;
            if($info['gender'] == 'm')
                $data['gender'] = '男';
            elseif ($info['gender'] == 'f')
                $data['gender'] = '女';
            else 
                $data['gender'] = '未知';
            $data['province'] = $info['province'];
            $data['city'] = $info['city'];
            $data['location'] = $info['location'];
            $data['photo'] = $info['profile_image_url'];
            $data['add_time'] = date('Y-m-d H:i:s');
            $data['last_login'] = date('Y-m-d H:i:s');
            $data['platform'] = 'weibo';
            $data['description'] = $info['description'];
            $data['tages'] = $info['ability_tags'];
        }
        
        return $data;
        
    }
    
    public function qq($token , $open_id){
        
        $app_id = '101030407';
        $app_key = '5abfdd9910cbe5140e2ba341300a9687';
        
        $get_user_info = "https://graph.qq.com/user/get_user_info?"
        . "access_token=" . $token
        . "&oauth_consumer_key=" . $app_id
        . "&openid=" . $open_id
        . "&format=json";
        
        $info = file_get_contents($get_user_info);
        $info = json_decode($info, true);
        
        $data = array();
        if($info['nickname']){
            $data['open_id'] = $open_id;
            $data['name'] = $info['nickname'];
            $data['access_token'] = $token;
            if($info['gender'] == '男')
                $data['gender'] = '男';
            elseif ($info['gender'] == '女')
                $data['gender'] = '女';
            else 
                $data['gender'] = '未知';
            $data['province'] = $info['province'];
            $data['city'] = $info['city'];
            $data['location'] = $info['province']." ".$info['city'];
            $data['photo'] = $info['figureurl_2'];
            $data['add_time'] = date('Y-m-d H:i:s');
            $data['last_login'] = date('Y-m-d H:i:s');
            $data['platform'] = 'qq';
            $data['description'] = '';
            $data['tages'] = '';
        }
        
        return $data;
    }

}
