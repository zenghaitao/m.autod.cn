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
        }
    }
    
    /**
     * 处理用户是否有权限请求
     *
     * @return bool
     */
    protected function checkPermission(){
        if(!isset($_SESSION['reg_id'])){
            return false;
        }
/*        if($_SERVER['IS_DEBUG'] == 'yes'){
            return true;
        }*/
    }
    
    /**
     * 公关的返回接口
     *
     * @param mixed $data
     */
    protected function apiEncode($data){
        return json_encode($data);
    }
    
    
    protected function convArray($array){
        foreach ($array as $key => $val){
            if(strpos($key , '_') !== false){
                
            }
        }
    }
}