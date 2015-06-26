<?php
namespace API\Controller;
use Think\Controller;

class BaseController extends Controller{
    
    public function __construct(){
        parent::__construct();
        
        $this -> setSession();
        
        /* 根据版本号调用不同的方法文件 */
        $this -> _path = dirname(__FILE__);
        $_class_path = $this -> _path."/{$_GET['ver']}/".CONTROLLER_NAME."Controller.class.php";
        if($_GET['ver'] && is_file($_class_path)){
            require_once($_class_path);
            $t_class_name = 'new'.CONTROLLER_NAME.'Controller';
            $t_class = new $t_class_name();
            if(method_exists($t_class,ACTION_NAME)){
                $t_method = ACTION_NAME;
                $t_class -> $t_method();
                exit;
            }
        }
        
    }
    
    /**
     * 设置session
     *
     */
    private function setSession(){
        if(isset($_POST['sessionId'])){
            session_id($_POST['sessionId']);
            session_start();
        }else{
            session_start();
        }
        session(array('expire'=>3600*24));
    }
    
    /**
     * 处理用户是否有权限请求
     *
     * @return bool
     */
    protected function checkPermission(){
        if(!isset($_SESSION['reg_id'])){
            $this -> fail(104);
            return false;
        }
        return true;
/*        if($_SERVER['IS_DEBUG'] == 'yes'){
            return true;
        }*/
    }
    
    /**
     * 检测用户是否为登录状态
     *
     * @return bool
     */
    protected function mustLogin(){
        if($_SESSION['user_id'] < 1){
            $this -> fail(105);
            return false;
        }
        return true;
    }
    
    /**
     * 返回错误信息
     *
     */
    protected function fail($code){
        $data = array();
        $msg = $this -> errCode($code);
        $data = array('status'=>'fail','info'=>array('code' => $code , 'message' => $msg));
        die($this -> apiEncode($data));
    }
    
    /**
     * 返回成功信息
     *
     */
    protected function succ($info){
        $data = array();
        $data = array('status'=>'succ','info'=>$info);
        die($this -> apiEncode($data));
    }
    
    /**
     * 返回错误状态码对应中文说明
     *
     * @param int $code
     * @return string
     */
    private function errCode($code){
        $error = C('ERROR_CODE');
        if(isset($error[$code]))
            return $error[$code];
        else 
            return "操作失败！";
    }
    
    /**
     * 公关的返回接口
     *
     * @param mixed $data
     */
    protected function apiEncode($data){
        return json_encode($data);
    }
    
}