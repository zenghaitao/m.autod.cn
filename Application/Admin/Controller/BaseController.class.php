<?php
namespace Admin\Controller;
use Think\Controller;

class BaseController extends Controller{
    
    public function __construct(){
        parent::__construct();        
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
        if($_GET['jsoncallback']){
            return "{$_GET['jsoncallback']}(".json_encode($data).")";
        }else 
            return json_encode($data);
    }
    
}