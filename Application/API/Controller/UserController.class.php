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
        $url = "80325,80284,80395,80372,80292,80121,79986,79326,78981,78019,80418,80285,80327,80283,80290,80118,79984,79977,79318,77782,80379,80356,80282,80281,80287,80119,79985,79975,79974,79973,80351,80280,80393,80298,80279,80133,79993,79971,79970,79969,80296,80337,80278,80409,80303,80254,80000,79968,79967,79966,80355,80348,80375,80301,80276,80252,80027,79972,79965,79964,80323,80306,80028,80026,80411,80404,80023,79980,79963,79962,80073,80025,80405,80338,80277,80249,80024,79988,79961,79921,80031,80030,80412,80360,80300,80247,80029,79960,79880,79188,80072,80413,80410,80364,80059,80056,80055,79176,79126,79104,80368,80406,80307,80288,80251,80243,80054,79106,79066,79044,80328,80245,80370,80362,80244,80240,80053,79108,79043,79042,80373,80365,80414,80295,80242,80237,80057,79107,79041,78967,80403,80329,80415,80246,80239,80230,80077,79129,79040,78813,80109,80248,80236,80402,80233,80108,80076,79130,79039,78582,80117,80396,80250,80234,80114,80105,80092,79171,79033,78578,80380,80253,80401,80235,80120,80111,80099,79228,79022,78573,80132,80376,80416,80305,80131,80116,80098,79229,79015,78568,80124,80366,80394,80241,80232,80123,80106,79830,78994,78564,80417,80352,80408,80369,80347,80110,80074,79628,78990,78558";
        $url = explode(',',$url);
        var_dump($url);
    }
    
    
    /**
     * 根据设备ID创建用户
     *
     */
    public function registerDevice()
    {

        $this -> _auto_check = 0;
        
        if(empty($_POST['deviceId']) || empty($_POST['devicePlant']) || empty($_POST['deviceName']) || empty($_POST['deviceOs'])) {
            $status = array('status'=>'fail','info'=>array('message'=>'data format fail!'));
            echo $this -> api_encode($status);
            exit;
        }
        
        $time                   = date('Y-m-d H:i:s',time());
        // $time                   = time();
        $data['device_id']      = $_POST['deviceId'];
        $data['device_plant']   = $_POST['devicePlant'];
        $data['device_name']    = $_POST['deviceName'];
        $data['device_os']      = $_POST['deviceOs'];
        // $data['add_time']       = $time;
        $data['add_time']       = time();
        if(!empty($_POST['device_token'])) {
           $data['device_token'] = $_POST['deviceToken'];
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
	    
		if(empty($_SESSION['reg_id']) || empty($_POST['deviceToken'])) {
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