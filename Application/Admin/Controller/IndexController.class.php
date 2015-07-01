<?php
namespace Admin\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;
use API\Model\SnsModel;
use API\Model\UserModel;
use API\Model\QiniuModel;
use Admin\Model\SnatchModel;
use Think\Upload\Driver\Qiniu\QiniuStorage;

class IndexController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
    }
    
    /**
     * 控制台
     *
     */
    public function index(){
        $this -> assign("action" , 'index');
        $this -> display('main');
    }
    
    /**
     * 分类管理功能
     *
     */
    public function cate(){
        
        $M_news = new NewsModel();
        $list = $M_news -> getCateList();
        
        $this -> assign('list' , $list);
        $this -> assign("action" , 'cate');
        $this -> display('cate');
    }
    
    /**
     * 来源管理功能
     *
     */
    public function source(){
        $M_news = new NewsModel();
        $list = $M_news -> getSoureList();
        
        $this -> assign('list' , $list);
        $this -> assign("action" , 'source');
        $this -> display('cate');
    }
    
    /**
     * 社区版块管理
     *
     */
    public function sns_foram(){
        $M_sns = new SnsModel();
        $list = $M_sns -> foramList();
        
        $this -> assign('list' , $list);
        $this -> assign("action" , 'sns_foram');
        $this -> display('sns_foram');
    }
    
    /**
     * 社区帖子管理
     *
     */
    public function sns_thread(){
        $page = (int)$_GET['page'];
        if($page < 1)
            $page = 1;
            
        $page_count = 50;
            
        $M_sns = new SnsModel();
        $list = $M_sns -> threadList( 0  , $page , $page_count );
        
        $count = $M_sns -> threadCount();
        
        $this -> page($page , $count , $page_count , "/Admin/Index/sns_thread?page={page}");
        
        $this -> assign('list' , $list);
        $this -> assign("action" , 'sns_thread');
        $this -> display('sns_thread');
    }
    
    private function format_thread($thread){
        $data = $thread;
        
        return $data;
    }
    
    public function sns_thread_status(){
        
        $M_user = new UserModel();
        $M_sns = new SnsModel();
        
        if($_POST){ 
            $user = $M_user -> getUserInfo($_POST['uid']);
            
            $data['uid'] = $_POST['uid'];
            $data['images'] = $_POST['pics'];
            $data['contents'] = $_POST['contents'];
            $data['foram_id'] = 1;
            $data['username'] = $user['name'];
            $data['userphoto'] = $user['photo'];
            $data['add_time'] = date('Y-m-d H:i:s');
            
            $id = $M_sns -> addThreadStatus($data);
            
            $info  = $M_sns -> thread($id);
            
            exit;
        }
        
        $root_list = $M_user -> getRoot();
        
        $this -> assign("root_list" , $root_list);
        $this -> assign("action" , 'sns_thread');
        $this -> display('sns_thread_status');
    }
    
    /**
     * 七牛上传token
     *
     */
    public function qiniuToken(){
       
        $QN_config = C('UPLOAD_SITEIMG_QINIU');
        //var_dump($QN_config);exit;
        
        $qiniu = new QiniuStorage($QN_config['driverConfig']);
        $token = $qiniu -> UploadToken($QN_config['driverConfig']['secrectKey'] , $QN_config['driverConfig']['accessKey'] ,$param);
        echo json_encode(array('uptoken'=>$token));
        //echo json_encode(array('uptoken'=>'0MLvWPnyya1WtPnXFy9KLyGHyFPNdZceomLVk0c9:gsa0agNkLsn-ChFV2-erE51qs6k=:eyJzY29wZSI6InFpbml1LXBsdXBsb2FkIiwiZGVhZGxpbmUiOjE0MzU2NDY0NTJ9'));
    }
    
    public function qiniuLog(){
        $domain = $_POST['domain'];
        $key = $_POST['key'];
        
        $M_qiniu = new QiniuModel();
        $M_qiniu -> add($domain , $key);
        
    }
}