<?
namespace API\Controller;
use Think\Controller;

//恢复session设置
if($_GET['session'])
    session_id($_GET['session']);
session_start();

class BaseController extends Controller{
    
    public function __construct(){
        parent::__construct();
        
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
        
        if(!$this -> checkPermission()){
            
        }
    }
    
    /**
     * 处理用户是否有权限请求
     *
     * @return bool
     */
    protected function checkPermission(){
        return true;
        if($_SERVER['IS_DEBUG'] == 'yes'){
            return true;
        }
    }
    
    /**
     * 公关的返回接口
     *
     * @param mixed $data
     */
    protected function api_encode($data , $show_text='' , $talkshow=''){
        $data['barshow'] = $show_text;
        $data['talkshow'] = $talkshow;
        return json_encode($data);
    }
    
}