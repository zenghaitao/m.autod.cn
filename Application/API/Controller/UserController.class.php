<?php
namespace API\Controller;
use API\Model\UserModel;

class UserController extends BaseController {
    
    public function __construct(){
        parent::__construct();
        
        //是否为合法访问
        if(!in_array($_SERVER['PATH_INFO'] , array('User/registerDevice','User/reconnect'))){
            $this -> checkPermission();
        }
    }
    
    public function index(){

    }
    
    
    /**
     * 根据设备ID创建用户
     *
     */
    public function registerDevice()
    {
        if(empty($_POST['deviceId']) || empty($_POST['devicePlant']) || empty($_POST['deviceName']) || empty($_POST['deviceOS'])) {
            $this -> fail(101);
        }
        
        $time                   = date('Y-m-d H:i:s',time());
        // $time                   = time();
        $data['device_id']      = $_POST['deviceId'];
        $data['device_plant']   = $_POST['devicePlant'];
        $data['device_name']    = $_POST['deviceName'];
        $data['device_os']      = $_POST['deviceOS'];
        // $data['add_time']       = $time;
        $data['add_time']       = date("Y-m-d H:i:s");
        if(!empty($_POST['device_token'])) {
           $data['device_token'] = $_POST['deviceToken'];
        }

        
        /* 注册设备 */
        $M_user = new UserModel();
        $reg_id = $M_user -> registerUserWithDeviceID($data);
        
        if($reg_id){
            //设置session数据
            $M_user -> setSession($reg_id);
            $this -> succ(array('sessionId'=>session_id(),'regId'=>$reg_id,'userId'=>0,'user'=>NULL,'open_qq'=>'no'));
        }else 
            $this -> fail(102);
        
    }
    
    /**
	 *	更新用户的device_token
	 */
	public function setPushToken(){
	    
		if(empty($_SESSION['reg_id']) || empty($_POST['deviceToken'])) {
            $status = array('status'=>'fail','info'=>array('message'=>'data format fail!','code'=>400));
            echo $this -> apiEncode($status);
            exit;
        }
		
        $M_user = new UserModel();
        $res = $M_user -> updateDeviceToken($_SESSION['reg_id'] , $_POST['device_token']);
        
		if($res){
			$status = array('status'=>'succ','info'=>array('message'=>'update device_token'));
		}else{
			$status = array('status'=>'fail','info'=>array('message'=>'update device_token fail!'));
		}
        echo $this -> apiEncode($status);
        exit;
	}
	
	/**
	 * 设置推送开关
	 *
	 */
	public function setPushOpen(){
	    if(empty($_POST['val'])) {
            $this -> fail(101);
        }
		
        if($_POST['val'] != 'yes')
            $_POST['val'] = 'no';
        
        $M_user = new UserModel();
        $res = $M_user -> updatePush($_SESSION['reg_id'] , $_POST['val']);
        
		if($res){
		    $this -> succ(array('message'=>'update push open'));
		}else{
		    $this -> fail(102);
		}
	}
    
    /*
     * session_id过期后 重新获取session_id
     * @user_id     true    用户id
     * @
     */
    public function reconnect() {
        $reg_id    = intval($_POST['regId']);
        
        if(empty($reg_id)) {
            $this -> fail(101);
        }
        
        $M_user = new UserModel();
        $res = $M_user -> setSession($reg_id);
        
        if($res){
            $status = array('sessionId'=>session_id(),'regId'=>$reg_id,'userId'=>$_SESSION['user_id'],'user'=>$_SESSION['user']);
            $this -> succ($status);
        }else{
            $this -> fail(102);
        }
        
    }
    
    /**
     * 绑定第三方平台登录
     *
     */
    public function bind(){
        $platform       = $_POST['platform'];
        $token          = $_POST['accessToken'];
        $open_id        = $_POST['openId'];
        
        if(empty($token) || empty($platform) || empty($open_id)) {
            $this -> fail(101);
        }
        
        //绑定第三方帐号
        $M_user = new UserModel();
        
        if($uid = $M_user -> bind($_SESSION['reg_id'] , $platform , $token , $open_id)){
            $info = $M_user -> getUserInfo($uid);
            //重设session
            $M_user -> setSession($_SESSION['reg_id']);
            $status = array('sessionId'=>session_id(),'regId'=>$_SESSION['reg_id'],'userId'=>$_SESSION['user_id'],'user'=>$_SESSION['user']);
            $this -> succ($status);
        }else{
            $this -> fail(102);
        }
    }
    
    /**
     * 登录功能
     *
     */
    public function login(){
        $open_id    = $_POST['phone'];
        $token      = $_POST['password'];
        $platform   = 'mobile';
        
        if(empty($token) || empty($platform) || empty($open_id)) {
            $this -> fail(101);
        }
        
        $M_user = new UserModel();
        
        if($uid = $M_user -> bind($_SESSION['reg_id'] , $platform , $token , $open_id)){
            $info = $M_user -> getUserInfo($uid);
            //重设session
            $M_user -> setSession($_SESSION['reg_id']);
            $status = array('sessionId'=>session_id(),'regId'=>$_SESSION['reg_id'],'userId'=>$_SESSION['user_id'],'user'=>$_SESSION['user']);
            $this -> succ($status);
        }else{
            $this -> fail(102);
        }
        
    }
    
    /**
     * 用户注册
     *
     */
    public function register(){
        $open_id    = $_POST['phone'];
        $token      = $_POST['password'];
        $username   = $_POST['username'];
        $photo      = $_POST['photo'];
        $code       = $_POST['code'];
        
        if(!$photo)
            $photo = 'http://m.autod.cn/Public/images/admin.png';
        else 
            $photo .= '?imageMogr2/thumbnail/200x200';
            
        if(empty($token) || empty($open_id) || empty($photo) || empty($username) || empty($code)) {
            $this -> fail(101);
        }
        
        $M_user = new UserModel();
        
        $res = $M_user -> checkCode($open_id , $code);
        if(!$res)
            $this -> fail(103);
            
        //使用此验证码
        $M_user -> useCode($open_id , $code);
            
        $res = $M_user -> regMobile($_SESSION['reg_id'] , $open_id , $token , $username , $photo);
        if(!$res)
            $this -> fail(102);
        else {
            
            $info = $M_user -> getUserInfo($res);
            //重设session
            $M_user -> setSession($_SESSION['reg_id']);
            $status = array('sessionId'=>session_id(),'regId'=>$_SESSION['reg_id'],'userId'=>$_SESSION['user_id'],'user'=>$_SESSION['user']);
            $this -> succ($status);
        }
    }
    
    /**
     * 发送手机验证码
     *
     */
    public function sendCode(){
        $phone        = $_POST['phone'];
        if(empty($phone)) {
            $this -> fail(101);
        }
        
        $M_user = new UserModel();
        $res = $M_user -> makeCode($phone);
        if($res){
            $this -> succ($phone);
        }else{
            $this -> fail(102);
        }
    }
    
    /**
     * 检测验证码是否正确
     *
     */
    public function checkCode(){
        $phone        = $_POST['phone'];
        $code         = $_POST['code'];
        if(empty($phone) || empty($code)) {
            $this -> fail(101);
        }
        $M_user = new UserModel();
        $res = $M_user -> checkCode($phone , $code);
        if($res)
            $this -> succ($code);
        else 
            $this -> fail(103);
        
    }
    
    /**
     * 用户退出登录
     *
     */
    public function logout(){
        $reg_id = $_SESSION['reg_id'];
        
        $M_user = new UserModel();
        if($M_user -> unbind($reg_id)){
            $M_user -> setSession($reg_id);
            $status = array('sessionId'=>session_id(),'regId'=>$_SESSION['reg_id'],'userId'=>$_SESSION['user_id'],'user'=>$_SESSION['user']);
            $this -> succ($status);
        }else{
            $this -> fail(102);
        }
    }
    
    /**
     * 获取用户信息
     *
     */
    public function info(){
        if(!(int)$_GET['uid']){
            $uid = $_SESSION['device']['uid'];
        }else {
            $uid = (int)$_GET['uid'];
        }
        
        if($uid){
            $M_user = new UserModel();
            $res = $M_user -> getUserInfo($uid);
            
            $this -> succ($res);
        }else{

            $this -> fail(102);
        }
    }
    
}