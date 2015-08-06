<?php
namespace API\Model;
use API\Model\SmsModel;

class UserModel
{

    protected $_db_user;
    protected $_db_user_device;
    protected $_db_user_feedback;
    protected $_db_user_code;
    
    public function __construct(){
        $this -> _db_user_device = M('User_device' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_user = M('User' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_user_feedback = M('User_feedback' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_user_code = M('User_code' , 'ad_' , 'DB0_CONFIG');
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
        //手机登录用户对token做加密处理
        if($platform == 'mobile')
            $token = md5($token);
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
            if($platform == 'mobile'){
                return false;
                //$data = $this -> mobile($token , $open_id);
            }
            
            if(!$data)
                return false;
                
            $uid = $this -> _db_user -> add($data);            
        }else{
            //更新用户登录时间
            //$data['last_login'] = time();
            $this -> _db_user -> where("id = '{$info['id']}'") -> save(array('last_login' => date('Y-m-d H:i:s')));
            $uid = $info['id'];
        }
        //将此设备绑定到用户
        $res = $this -> _db_user_device -> where("reg_id = '{$reg_id}'") -> save(array('uid' => $uid));
        
        return $uid;
    }
    
    /**
     * 生成来自weibo平台的水军
     *
     * @param array $info
     * @return mixd
     */
    public function addWeiboRobot($info){
        $data = array();
        $data['open_id'] = $info['idstr'];
        $data['name'] = $info['screen_name'];
        $data['access_token'] = 'robot';
        if($info['gender'] == 'm')
            $data['gender'] = '男';
        elseif ($info['gender'] == 'f')
            $data['gender'] = '女';
        else 
            $data['gender'] = '未知';
        $data['province'] = $info['province'];
        $data['city'] = $info['city'];
        $data['location'] = $info['location'];
        $data['photo'] = $info['avatar_large'];
        $data['add_time'] = date('Y-m-d H:i:s');
        $data['last_login'] = date('Y-m-d H:i:s');
        $data['platform'] = 'robot';
        $data['description'] = $info['description'];
        $data['tages'] = $info['ability_tags'];
        $data['is_robot'] = 'yes';
        
        $uid = $this -> _db_user -> add($data);

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
            $data['photo'] = $info['avatar_large'];
            $data['add_time'] = date('Y-m-d H:i:s');
            $data['last_login'] = date('Y-m-d H:i:s');
            $data['platform'] = 'weibo';
            $data['description'] = $info['description'];
            $data['tages'] = $info['ability_tags'];
        }

        return $data;
        
    }
    
    private function qq($token , $open_id){
        
        $app_id = '1104779252';
        $app_key = 'KBoUNjEFxOMT9N5L';
        
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
    
    /**
     * 根据手机号注册用户
     *
     * @param string $phone
     * @param string $password
     * @param string $username
     * @param string $photo
     * @return mixed
     */
    public function regMobile($reg_id , $phone , $password , $username , $photo){
        $info = $this -> getInfoByOpenId($phone , 'mobile');
        if($info)
            return false;
        
        $data = array();
        $data['open_id'] = $phone;
        $data['name'] = $username;
        $data['access_token'] = md5($password);
        $data['gender'] = '未知';
        $data['province'] = 0;
        $data['city'] = 0;
        $data['location'] = 0;
        $data['photo'] = $photo;
        $data['add_time'] = date('Y-m-d H:i:s');
        $data['last_login'] = date('Y-m-d H:i:s');
        $data['platform'] = 'mobile';
        $data['description'] = '';
        $data['tages'] = '';
        
        $uid = $this -> _db_user -> add($data);
        if($uid){
            //将此设备绑定到用户
            $this -> _db_user_device -> where("reg_id = '{$reg_id}'") -> save(array('uid' => $uid));
        }
        return $uid;
        
    }
    
    /**
     * 根据open_id获取用户信息
     *
     * @param string $open_id
     * @param string $platform
     * @return array
     */
    public function getInfoByOpenId($open_id , $platform){
        $info = $this -> _db_user -> where("open_id = '{$open_id}' AND platform = '{$platform}'") -> find();
        return $info;
    }

    /**
     * 意见反馈
     *
     * @param array $data
     * @return bool
     */
    public function feedback($data){
        return $this -> _db_user_feedback -> add($data);
    }
    
    /**
     * 为手机号生成验证码
     *
     */
    public function makeCode($phone){
        $code = $this -> randCode();
        
        $data = array();
        $data['phone'] = $phone;
        $data['code'] = $code;
        $data['is_valid'] = 'yes';
        $data['add_time'] = date('Y-m-d H:i:s');
        
        $res = $this -> _db_user_code -> add($data);
        if($res){
            $M_sms = new SmsModel();
            $res = $M_sms -> Send($phone , "您在汽车日报的验证码为【{$code}】");
            return $res;
        }else{
            return false;
        }
    }
    
    /**
     * 验证码生产算法
     *
     * @return string
     */
    private function randCode(){
        $code = rand(1000000 , 1999999);
        return substr($code , 1 , 6);
    }
    
    /**
     * 验证code
     *
     * @param string $phone
     * @param string $code
     */
    public function checkCode($phone , $code){
        $info = $this -> _db_user_code -> where("phone = '{$phone}' AND code = '{$code}' AND is_valid = 'yes'") -> find();
        if(time() - strtotime($info['add_time']) <= 60*10){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 使用此验证码
     *
     * @param string $phone
     * @param string $code
     */
    public function useCode($phone , $code){
        $data = array();
        $data['is_valid'] = 'no';
        $this -> _db_user_code -> where("phone = '{$phone}' AND code = '{$code}'") -> save($data);
    }
    
    /**
     * 重设密码
     *
     * @param string $phone
     * @param string $code
     * @param string $pwd
     * @return bool
     */
    public function findPwd($phone , $code , $pwd){
        
        $res = $this -> checkCode($phone , $code);
        if($res){
            $this -> useCode($phone , $code);
            
            $data = array();
            $data['token'] = md5($pwd);
            
            $this -> _db_user -> where("open_id = '{$phone}' , platform = 'mobile'") -> save($data);
            
            return true;
        }else{
            return false;
        }
        
    }
}
