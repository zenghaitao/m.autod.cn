<?php
namespace Admin\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;
use Admin\Model\SnatchModel;

class IndexController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
    }
    
    public function index(){
        $this -> display('main');
    }
    
    
    /**
     * 处理采集响应
     *
     */
    public function snatch(){
        $url = 'http://news.163.com/15/0625/06/ASUGGVRH00014JB6.html';
        
        $M_snatch = new SnatchModel($url);
//        $M_snatch -> netease($url);
        var_dump($M_snatch -> parse() );
        
    }
}