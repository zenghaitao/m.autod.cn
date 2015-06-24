<?php
namespace API\Controller;
use API\Model\UserModel;

class UserController extends BaseController {
    
    protected $_auto_check = 1;
    
    public function __construct(){
        parent::__construct();
        if($this -> auto_check){
            $this -> checkPermission();
        }
        
    }
    
    public function index(){
        $M_user = new UserModel();
        $token = '2.00nkajUFbnlrHC3ce4ad1ba7nG1w4C';
        $open_id = '1149955862';
        $info = $M_user -> weibo($token , $open_id);
        var_dump($info);
    }
    
    
    /**
     * 根据设备ID创建用户
     *
     */
    public function registerDevice()
    {

        $this -> _auto_check = 0;
        
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
            $this -> succ(array('sessionId'=>session_id(),'regId'=>$reg_id,'userId'=>0,'user'=>NULL));
        }else 
            $this -> fail(102);
        
    }
    
    private function testDate(){
        return array(
            'device_id' => 'dadsadsadsadsa',
            'device_plant' => 'ios',
            'device_name' => 'iphone6',
            'device_os' => 'ios7',
            'device_token' => 'dadsadsadsadsa'
        );
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
        $this -> _auto_check = 0;
        
        $reg_id    = intval($_POST['regId']);
        
        if(empty($reg_id)) {
            $status = array('status'=>'fail','info'=>array('message'=>'data format fail!'));
            echo $this -> apiEncode($data);
            exit;
        }
        
        $M_user = new UserModel();
        $res = $M_user -> setSession($reg_id);
        
        if($res){
            $status = array('status'=>'succ','info'=>array('sessionId'=>session_id(),'regId'=>$reg_id,'userId'=>$_SESSION['user_id'],'user'=>$_SESSION['user']));
        }else{
            $status = array('status'=>'fail','info'=>array('message'=>'device id fail!'));
        }
        
        echo json_encode($status);
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
            $status = array('status'=>'succ','info'=>array('sessionId'=>session_id(),'regId'=>$_SESSION['reg_id'],'userId'=>$_SESSION['user_id'],'user'=>$_SESSION['user']));
            $this -> succ($status);
        }else{
            $this -> fail(102);
        }
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
            $status = array('status'=>'succ','info'=>array('sessionId'=>session_id(),'regId'=>$_SESSION['reg_id'],'userId'=>$_SESSION['user_id'],'user'=>$_SESSION['user']));
        }else{
            $status = array('status'=>'fail','info'=>array('message'=>'logout fail!'));
        }
        echo $this -> apiEncode($status);
        exit;
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
            $status = array( 'status' => 'succ' , 'info' => $res );
        }else{
            $status = array( 'status' => 'fail' , 'info'=>array('message' => 'reg id fail') );
        }
        echo $this -> apiEncode($status);
        exit;
    }
    
}