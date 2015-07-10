<?php
namespace API\Controller;

class ConfigController extends BaseController {
    
    public function __construct(){
        parent::__construct();
        $this -> checkPermission();
    }
    
    public function screenPhoto(){
        $imgs = array();
        
//        $imgs[] = 'http://7xjrkc.com1.z0.glb.clouddn.com/IMG_0640.jpg';
        $imgs[] = 'http://7xjrkc.com1.z0.glb.clouddn.com/IMG_0670.jpg';
        
        $imgs[] = 'http://pic.58pic.com/58pic/14/76/74/40b58PICiS9_1024.jpg';
//        $imgs[] = 'http://pic21.nipic.com/20120613/9507361_164919714197_2.jpg';
//        $imgs[] = 'http://pic2.ooopic.com/12/17/43/46bOOOPIC69_1024.jpg';
        
//        $imgs[] = 'http://pic21.nipic.com/20120613/9507361_164919714197_2.jpg';
        
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
