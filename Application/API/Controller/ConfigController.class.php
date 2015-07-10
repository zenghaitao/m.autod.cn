<?php
namespace API\Controller;

class ConfigController extends BaseController {
    
    public function __construct(){
        parent::__construct();
        $this -> checkPermission();
    }
    
    public function screenPhoto(){
        $imgs[] = 'http://7xjrkc.com1.z0.glb.clouddn.com/IMG_0640.jpg';
        $imgs[] = 'http://7xjrkc.com1.z0.glb.clouddn.com/IMG_0670.jpg';
        
        
        $this -> succ(array('photos'=>$imgs));
    }
    
}