<?php
namespace Admin\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;
use API\Model\SnsModel;
use API\Model\UserModel;
use API\Model\QiniuModel;
use Admin\Model\SnatchModel;
use Think\Upload\Driver\Qiniu\QiniuStorage;

set_time_limit(0);

class IndexController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
    }
    
    public function initNewsChoice(){
        $_db_news_choice = M('news_choice' , 'ad_' , 'DB0_CONFIG');
        $_db_news_story = M('news_story' , 'ad_' , 'DB0_CONFIG');
        $where_str = "story_date > '2015-01-01' AND img_count > '0' AND is_choice = 'no'";
        $list = $_db_news_story -> where($where_str) -> order('story_date ASC , id ASC') -> select();
        foreach ($list as $row){
            $data = array();
            $data['story_id'] = $row['id'];
            $data['cate_id'] = $row['column_id'];
            $data['title'] = $row['title'];
            $data['summary'] = $row['short_summary'];
            $data['source'] = $row['source'];
            $data['source_id'] = $row['source_id'];
            
            $data['images'] = $row['title_pic1'];
            if($row['title_pic3']){
                $data['images'] .= ';,;'.$row['title_pic2'];
                $data['images'] .= ';,;'.$row['title_pic3'];
            }
            
            $data['story_date'] = $row['story_date'];
            if($row['plant'] == 'UUTV')
                $data['open_mode'] = 'news';
            elseif ($row['plant'] == 'toutiao')
                $data['open_mode'] = 'news';
            else 
                $data['open_mode'] = 'news';
                
            $data['day'] = $row['story_date'];
            $data['add_time'] = $row['add_date'];;
            
            $_db_news_choice -> add($data);
            
            $_db_news_story -> where("id = '{$row['id']}'") -> save(array('is_choice'=>'yes'));
        }
        
        die('over');
        
    }
    
    public function initNewsCoutent(){
        exit;
        
        $_db_news_content = M('news_story_content' , 'ad_' , 'DB0_CONFIG');
        
        $since_id = $_GET['id'];
        $max_id = 0;
        
        $where_str = '1';
        if($since_id)
            $where_str .= " AND id < '{$since_id}'";
        
        $list = $_db_news_content -> where($where_str) -> order("id DESC") -> limit(1000) -> select();
        if(!$list)
            die('over');
        foreach ($list as $row){
            $max_id = $row['id'];
            if(!$row['image_count'])
                continue;
                
            $array = explode(';,;',$row['images']);
            $new_imagess = array();
            
            foreach ($array as $key => $val){
                if(strpos($val , 'http://') !== false){
                    $new_imagess[] = $val;
                }
            }
            
            $data['images'] = implode(';,;',$new_imagess);
            $data['image_count'] = count($new_imagess);
            
            $_db_news_content -> where("id = '{$row['id']}'") -> save($data);
        }
        
        if($max_id){
            $url = "/Admin/Index/initNewsCoutent?id=".$max_id;
            echo $url;
            echo "<script language=\"javascript\" type=\"text/javascript\">
window.location.href=\"{$url}\"; 
</script>";
        }else 
            die('over');
        
        
    }
    
    public function snatch_media_content(){
        exit;
        
        
        $since_id = $_GET['id'];
        $_db_story = M('story' , 'ad_' , 'DB0_CONFIG');
        $_db_story_content = M('story_content' , 'ad_' , 'DB0_CONFIG');
        
        $where_str = "plant = 'toutiao'";
        if($since_id)
            $where_str .= " AND id > '{$since_id}'";
            
        $list = $_db_story -> where($where_str) -> order("id ASC") -> limit(1) -> select();
        
        if(!$list)
            die('over');
        
        $max_id = 0;
        foreach ($list as $row){
            $max_id = $row['id'];
            
            $info = $_db_story_content -> where(" id = '{$max_id}' ") -> find();
            if($info['content'])
                continue;
            
            $M_snatch = new SnatchModel($row['url']);
            $result = $M_snatch -> toutiaoContent();
            
            //var_dump($result);
            
            $data = array();
            $data['id'] = $max_id;
            $data['article_id'] = $row['article_id'];
            $data['content'] = $result['content'];
            $data['images'] = $result['images'];
            $data['image_count'] = count(explode(';,;',$result['images']));
            if(!$data['images'])
                $data['image_count'] = 0;
            $data['http'] = $result['http'];
                
            $data['add_time'] = date('Y-m-d H:i:s');
            
            $_db_story_content -> save($data);
        }
        
        exit;
        
        if($max_id){
            $url = "/Admin/Index/snatch_media_content?id=".$max_id;
            echo $url;
            echo "<script language=\"javascript\" type=\"text/javascript\">
window.location.href=\"{$url}\"; 
</script>";
        }else 
            die('over');
        
    }
    
    public function init_video(){
        exit;
        
        $_db_video = M('ina_vedio' , 'cms_' , 'DB0_CONFIG');
        $_db_story = M('news_story' , 'ad_' , 'DB0_CONFIG');
        $list = $_db_video -> select();
        foreach ($list as $row){
            $data = array();
            $data['article_id'] = $row['id'];
            $data['title'] = $row['title'];
            $data['short_summary'] = $row['shorttitle'];
            $data['source'] = 'UUTV';
            $data['source_id'] = '48';
            $data['story_date'] = $row['publish_time'];
            $data['column_id'] = '20';
            $data['img_count'] = '1';
            $data['title_pic1'] = $row['img'];
            $data['url'] = "http://auto.tom.com/video/play_{$row['id']}.html";
            $data['add_date'] = date('Y-m-d H:i:s');
            $_db_story -> add($data);
        }
        var_dump('over');
    }
    
    public function snatch_media_page(){
        die('over');
        
        $_db_soure = M('soure' , 'ad_' , 'DB0_CONFIG');
        $_db_story = M('story' , 'ad_' , 'DB0_CONFIG');
        $list = $_db_soure -> select();
        foreach ($list as $row) {
            if($row['media_id'] != '3336759531')
                continue;
            
            
            echo $row['name'];
            
        	$path = "/tmp/".$row['media_id'];
        	$file_list = scandir($path);
        	$i = 0;
        	foreach ($file_list as $val){
        	    if($val != '.' && $val != '..'){
        	        $file = $path.'/'.$val;
        	        //echo "\t".$file;
        	        
        	        $M_snatch = new SnatchModel($file);
        	        $result = $M_snatch -> toutiaoPage();
        	        
        	        unset($M_snatch);
        	        
        	        foreach ($result as $data){
            	        $data['source'] = $row['name'];
            	        $data['source_id'] = $row['media_id'];
            	        $data['column_id'] = 0;
            	        $data['add_date'] = date('Y-m-d H:i:s');
            	        
            	        if(strpos($data['url'],'group') === false){
            	           @$_db_story -> add($data);
            	           $i++;
            	        }
        	        }
        	        unset($result);
        	    }
        	}
        	echo "(".$i.")\n";
        }
    }
    
    public function snatch_media(){
        $_db_soure = M('soure' , 'ad_' , 'DB0_CONFIG');
        $list = $_db_soure -> select();
        foreach ($list as $row) {
        	$this -> media_page($row['media_id']);
        	echo ($row['name']);
        }
    }
    
    private function media_page($id){
        $path = "/tmp/".$id;
        $this -> mkDir($path);
        
        $url = "http://toutiao.com/m{$id}/";
        
        $M_snatch = new SnatchModel($url);
        
        $list = $M_snatch -> toutiaoPageList();
        
        foreach ($list as $key => $row){
            $file = $path."/".$key;
            if(is_file($file))
                continue;
            
            $html = file_get_contents($row);
            file_put_contents($file , $html);
        }
        
        $num = count($list);
        echo "({$num})\n";
    }
    
    public function mkDir($path){
        $path = explode('/',$path);
        $dir = '';
        foreach ($path as $row){
            if(!$row)
                continue;
            $dir .= "/".$row;
            if(!is_dir($dir)){
                mkdir($dir);
            }
        }
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
        $M_story -> 
        
        $this -> display('news');
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