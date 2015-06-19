<?php
namespace API\Controller;
use API\Model\UserModel;

class UserController extends BaseController {
    
    protected $_auto_check = 1;
    
    public function __construct(){
        parent::__construct();
        if($this -> auto_check){
            if(!$this -> checkPermission()){
                return false;
            }
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
        //$_POST = $this -> testDate();
        
        
        $this -> _auto_check = 0;
        
        if(empty($_POST['device_id']) || empty($_POST['device_plant']) || empty($_POST['device_name']) || empty($_POST['device_os'])) {
            $status = array('status'=>'fail','info'=>array('message'=>'data format fail!'));
            echo $this -> api_encode($status);
            exit;
        }
        
        $time                   = date('Y-m-d H:i:s',time());
        // $time                   = time();
        $data['device_id']      = $_POST['device_id'];
        $data['device_plant']   = $_POST['device_plant'];
        $data['device_name']    = $_POST['device_name'];
        $data['device_os']      = $_POST['device_os'];
        // $data['add_time']       = $time;
        $data['add_time']       = time();
        if(!empty($_POST['device_token'])) {
           $data['device_token'] = $_POST['device_token'];
        }

        
        /* 注册设备 */
        $M_user = new UserModel();
        $reg_id = $M_user -> registerUserWithDeviceID($data);
        
        if($reg_id){
            //设置session数据
            $M_user -> setSession($reg_id);
            $status = array('status'=>'succ','info'=>array('session_id'=>session_id(),'reg_id'=>$reg_id,'uid'=>0,'user'=>NULL));
        }else 
            $status = array('status'=>'fail','info'=>array('message'=>'add device fail!'));
            
        echo $this -> api_encode($status);
        exit;
        
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
	
	    $_POST['device_token'] = "token";
	    
		if(empty($_SESSION['reg_id']) || empty($_POST['device_token'])) {
            $status = array('status'=>'fail','info'=>array('message'=>'data format fail!','code'=>400));
            echo $this -> api_encode($status);
            exit;
        }
		
        $M_user = new UserModel();
        $res = $M_user -> updateDeviceToken($_SESSION['reg_id'] , $_POST['device_token']);
        
		if($res){
			$status = array('status'=>'succ','info'=>array('message'=>'update device_token'));
		}else{
			$status = array('status'=>'fail','info'=>array('message'=>'update device_token fail!'));
		}
        echo $this -> api_encode($status);
        exit;
	}
    
    /*
     * session_id过期后 重新获取session_id
     * @user_id     true    用户id
     * @
     */
    public function reconnect() {
        $reg_id    = intval($_POST['reg_id']);
        $reg_id    = 4;
        
        if(empty($reg_id)) {
            $status = array('status'=>'fail','info'=>array('message'=>'data format fail!'));
            echo $this -> api_encode($data);
            exit;
        }
        
        $M_user = new UserModel();
        $res = $M_user -> setSession($reg_id);
        
        if($res){
            $status = array('status'=>'succ','info'=>array('session_id'=>session_id(),'reg_id'=>$reg_id,'uid'=>$_SESSION['user_id'],'user'=>$_SESSION['user']));
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
        $token          = $_POST['access_token'];
        $open_id        = $_POST['open_id'];
        $name           = $_POST['name'];
        $photo          = $_POST['photo'];
        $gender         = $_POST['gender'];
        $province       = $_POST['province'];
        $city           = $_POST['city'];
        $country        = $_POST['country'];
        $location       = $_POST['location'];
        $description    = $_POST['description'];
        
        if(empty($token) || empty($platform) || empty($open_id) || empty($name) || empty($photo)) {
            $data = array('status'=>'fail','info'=>array('message'=>'data format fail!'));
            echo $this -> api_encode($data);
            exit;
        }
        
        //绑定第三方帐号
        $M_user = new UserModel();
        if($uid = $M_user -> bind($_SESSION['reg_id'] , $_POST)){
            $info = $M_user -> getUserInfo($uid);
            //重设session
            $M_user -> setSession($_SESSION['reg_id']);
            $status = array('status'=>'succ','info'=>array('session_id'=>session_id(),'reg_id'=>$_SESSION['reg_id'],'uid'=>$_SESSION['user_id'],'user'=>$_SESSION['user']));
        }else{
            $data = array('status'=>'fail','info'=>array('message'=>'add user fail!'));
        }
        
        echo $this -> api_encode($status);
        exit;
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
            $status = array('status'=>'succ','info'=>array('session_id'=>session_id(),'reg_id'=>$_SESSION['reg_id'],'uid'=>$_SESSION['user_id'],'user'=>$_SESSION['user']));
        }else{
            $status = array('status'=>'fail','info'=>array('message'=>'logout fail!'));
        }
        echo $this -> api_encode($status);
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
        echo $this -> api_encode($status);
        exit;
    }
    
}