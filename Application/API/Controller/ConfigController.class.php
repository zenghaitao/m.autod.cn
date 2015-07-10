<?php
namespace API\Controller;

class ConfigController extends BaseController {
    
    public function __construct(){
        parent::__construct();
        $this -> checkPermission();
    }
    
    public function screenPhoto(){
        $imgs = array();
        
        $imgs[] = 'http://7xjrkc.com1.z0.glb.clouddn.com/IMG_0640.jpg';
        $imgs[] = 'http://7xjrkc.com1.z0.glb.clouddn.com/IMG_0670.jpg';
        
        
        $this -> succ(array('photos'=>$imgs));
    }
    
    /**
     * 检查版本更新
     *
     */
    public function version(){
        $result = array();
        $result['version'] = '2.0.0';
        $result['url'] = 'http://'.$_SERVER['HTTP_HOST']."/Home/Index/download";
        
        $version = $_GET['ver'];
        if(strpos($version , 'iphone') !== false){
            $n_version = C("IPHONE_VERSION");
            $version = str_replace('iphone' , '' , $version);
        }
        if(strpos($version , 'android') !== false){
            $n_version = C("ANDROID_VERSION");
            $version = str_replace('android' , '' , $version);
        }
        
        if($version >= $n_version){
            $result['update'] = 'no';
        }else{
            $result['update'] = 'yes';
        }
            
        $this -> succ($result);
    }
}
