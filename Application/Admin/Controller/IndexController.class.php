<?php
namespace Admin\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;
use API\Model\SnsModel;
use API\Model\UserModel;
use API\Model\QiniuModel;
use Admin\Model\SnatchModel;
use Admin\Model\AdminModel;
use Think\Upload\Driver\Qiniu\QiniuStorage;

set_time_limit(0);

class IndexController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
        
        //是否为合法访问
        if(!in_array($_SERVER['REDIRECT_URL'] , array('/Admin/Index/login','/Admin/Index/logout'))){
            $this -> checkLogin();
        }
    }
    
    public function login(){
        if($_POST){
            $name = $_POST['name'];
            $pwd = $_POST['pwd'];
            
            $M_admin = new AdminModel();
            if($M_admin -> login($name , $pwd)){
                //登录成功
                if($_POST['remenber'] == 'yes'){
                    cookie('admin_name',$name,3600*24*30);
                }
                $url = "/Admin/";
                header("Location:{$url}");
                exit;
            }else{
                $msg = '登录失败，请重试！';
                $url = "/Admin/Index/login?msg=".urlencode($msg);
                header("Location:{$url}");
                exit;
            }
        }
        
        $this -> assign('name' , $_COOKIE['admin_name']);
        $this -> assign('msg' , $_GET['msg']);
        
        $this -> display('login');
    }
    
    public function logout(){
        unset($_SESSION['admin_uid']);
        unset($_SESSION['admin_user']);
        
        $url = "/Admin/Index/login";
        header("Location:{$url}");
        exit;
    }
    
    public function frame(){
        
        $this -> assign('news_id' , $_GET['id']);
        
        $this -> display('frame');
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
        $this -> display('source');
    }
    
    /**
     * 新闻管理功能
     *
     */
    public function news(){
        $M_story = new StoryModel();
        $M_news = new NewsModel();
        
        $type = $_GET['type'];
        if(!$type)
            $type = 'story';
        
        $page = (int)$_GET['page'];
        if($page < 1)
            $page = 1;
        $page_count = 50;
        
        if($type == 'story'){        
            $title = "待处理";
            $result = $M_story -> storyAdminList('no' , $page , $page_count);
            $this -> page($page , $result['count'] , $page_count , "/Admin/Index/news?type=story&page={page}");
            
            $this -> assign('list' , $result['list']);
            $this -> assign("action" , 'story');
        }
        
        if($type == 'choice'){        
            $title = "新闻列表";
            $result = $M_news -> newsList(0 , $page , $page_count);
            $this -> page($page , $result['count'] , $page_count , "/Admin/Index/news?type=choice&page={page}");
            
            foreach ($result['list'] as &$row){
                $images = explode(';,;' , $row['images']);
                $row['title_pic1'] = $images[0];
                $row['title_pic2'] = $images[1];
                $row['title_pic3'] = $images[2];
                
                $row['column_id'] = $row['cate_id'];
            }
            
            $this -> assign('list' , $result['list']);
            $this -> assign("action" , 'choice');
        }
        
        if($type == 'cate'){        
            $title = "未分类新闻";
            $result = $M_story -> noCateNewsList( $page , $page_count);
            $this -> page($page , $result['count'] , $page_count , "/Admin/Index/news?type=cate&page={page}");
            
            $this -> assign('list' , $result['list']);
            $this -> assign("action" , 'catenews');
            
            /* 获取分类列表 */
            $cate = $M_news -> getCateList();
            $this -> assign("catelist" , $cate);
        }
        
        $this -> assign('title' , $title);
        $this -> display('news');
    }
    
    public function saveStoryCate(){
        $M_story = new StoryModel();
        $M_news = new NewsModel();
        
        $story_id = $_POST['story_id'];
        $cate_id = $_POST['cate_id'];
        
        $data['column_id'] = $cate_id;
        $result = $M_story -> updateStory($story_id , $data);
        
        if($result !== false){
            $data['cate_id'] = $cate_id;
            $M_news -> updateStory($story_id , $data);
            die('succ');
        }
        die('fail');
    }
    
    /**
     * 新闻管理功能
     *
     */
    public function news_push(){
        $M_news = new NewsModel();
        
        $this -> display('news_push');
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