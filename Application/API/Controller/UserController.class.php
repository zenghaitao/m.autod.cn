<?php
namespace API\Controller;
use Think\Controller;
class UserController extends BaseController {
    
    public function index(){
        $this->show('User123','utf-8');
    }
    
    /**
     * 根据设备ID创建用户
     *
     */
    public function registerDevice()
    {
        /**
         * 校验POST参数
         */
    	$data['device_id'] = $_POST['device_id'];
    	$data['device_name'] = $_POST['device_name'];
    	$data['screen'] = $_POST['device_screen'];
    	$data['plant'] = $_POST['device_plant'];
    	$data['os_verion'] = $_POST['device_os'];
    	$data['push_token'] = '';
    	$data['add_time'] = date('Y-m-d H:i:s');
    	
    	$deviceID = $data['device_id'];
    	if (!$deviceID) 
    	{
    		echo $this -> api_encode(array('status'=>'error', 'info'=>'Wrong deviceID'));
    		return ;
    	}
    	
    	//根据设备ID生成用户ID并返回给客户端
    	$userID = UsersModel::registerUserWithDeviceID($deviceID,$data);
        if($userID){
            $info['user_id'] = $userID;
            $info['session_id'] = session_id();
    	    echo $this -> api_encode(array('status'=>'succ', 'info'=>$info));
        }else {
    	    echo $this -> api_encode(array('status'=>'error'));
        }
    }
    
    /**
     * 重新生成session_id
     *
     */
    public function reconnect(){
        $user_id = $_GET['user_id'];
        $device_id = $_GET['device_id'];
        
        //根据设备id获取设备信息
        $device_info;
        
        //根据用户id获取用户信息
        $user_info;
        
        //重新生成session_id
        $session_id = session_regenerate_id();
        
        //设置session数据
        $_SESSION['device'] = $device_info;
        $_SESSION['user'] = $user_info;
        
        $info['user_id'] = $user_id;
        $info['session_id'] = $session_id;
        
        echo $this -> api_encode(array( 'status' => 'succ' , 'info' => $info ));
    }

    /**
     * 设置用户推送口令
     *
     */
    public function setPushToken(){
        //根据uid和device_id,将push_token更新到数据库
        
        echo $this -> api_encode(array( 'status' => 'succ' , 'info' => '' ));
    }
    
    /**
     * 绑定第三方平台登录
     *
     */
    public function bind(){
        //根据access_token获取第三方平台用户信息，并将信息更新到用户信息表
        $user_info;
        
        echo $this -> api_encode(array( 'status' => 'succ' , 'info' => $user_info ));
    }
    
    /**
     * 根据session获取用户信息
     *
     */
    public function info(){
        //获取用户信息
        $user_info = $_SESSION['user_info'];
        echo $this -> api_encode(array( 'status' => 'succ' , 'info' => $user_info ));
    }
    
}