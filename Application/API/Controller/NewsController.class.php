<?php
namespace API\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;

class NewsController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
    }
    
    public function init(){
        $M_story = new StoryModel();
        $M_news = new NewsModel();
        $list = $M_story -> initNews();
        foreach ($list as $row){
            $M_news -> addNewsToChoice($row['id']);
        }
        
        $list = $M_story -> initVideo();
        foreach ($list as $row){
            $M_news -> addVideoToChoice($row['id']);
        }
    }
    
    /**
     * 推荐新闻列表
     *
     */
    public function index(){
        
        $page = (int)$_GET['page'];
        if(!$page)
            $_SESSION['uids'] = '';
        
        $ids = $_SESSION['uids'];
        $ids = explode(',' , $ids);
        $uids = $ids;
        
        $M_news = new NewsModel();
        //从当天数据池中获取推荐新闻
        $list = $M_news -> newsPool($ids);
        
        foreach ($list as &$row){
            $uids[] = $row['story_id'];
            $row['storyId'] = $row['story_id'];
            $row['cateId'] = $row['cate_id'];
            $row['sourceId'] = $row['source_id'];

            $images = explode(';,;' , $row['images']);
            $row['imageCount'] = count($images);
            $row['images'] = $images;
            
            $row['postTime'] = $row['story_date'];
            $row['display_mode'] = 'default';
            $row['gourl'] = '';
            
            $row['favCount'] = $row['fav_count'];
            $row['likeCount'] = $row['like_count'];
            $row['commentsCount'] = $row['comments_count'];
            
            unset($row['story_id']);
            unset($row['cate_id']);
            unset($row['source_id']);
            unset($row['fav_count']);
            unset($row['like_count']);
            unset($row['comments_count']);

            if(count($uids) > 80){
                array_shift($uids);
            }
        }
        
        $_SESSION['uids'] = implode(',' , $uids);
        
        $result = array();
        $result['status'] = 'succ';
        $result['info']['statuses'] = $list;
        $result['info']['updateCount'] = count($list);
        $result['info']['nextPage'] = $page + 1;
        $result['info']['prevPage'] = $page - 1;
        
        echo $this -> apiEncode($result);
        exit();
        
    }
    
    /**
     * 分类新闻列表
     *
     */
    public function cateList(){
        $cate_id = $_GET['cateId'];
        $count = (int)$_GET['count'];
        if(!$count)
            $count = 10;
        $begin_id = (int)$_GET['sinceId'];
        $M_news = new NewsModel();
        $list = $M_news -> newsCateList($cate_id , $begin_id , $count);
        $since_id = 0;
        
        foreach ($list as &$row){
            $row['storyId'] = $row['story_id'];
            $row['cateId'] = $row['cate_id'];
            $row['sourceId'] = $row['source_id'];

            $images = explode(';,;' , $row['images']);
            $row['imageCount'] = count($images);
            $row['images'] = $images;
            
            $row['postTime'] = $row['story_date'];
            $row['display_mode'] = 'default';
            $row['gourl'] = '';
            
            $row['favCount'] = $row['fav_count'];
            $row['likeCount'] = $row['like_count'];
            $row['commentsCount'] = $row['comments_count'];
            
            unset($row['story_id']);
            unset($row['cate_id']);
            unset($row['source_id']);
            unset($row['fav_count']);
            unset($row['like_count']);
            unset($row['comments_count']);
            
            $since_id = $row['id'];
        }
        
        $result = array();
        $result['status'] = 'succ';
        $result['info']['statuses'] = $list;
        $result['info']['updateCount'] = count($list);
        $result['info']['sinceId'] = $since_id;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 媒体新闻列表
     *
     */
    public function sourceList(){  
        $source_id = $_GET['sourceId'];
        $count = (int)$_GET['count'];
        if(!$count)
            $count = 10;
        $begin_id = (int)$_GET['sinceId'];
        $M_news = new NewsModel();
        $list = $M_news -> newsSourceList($cate_id , $begin_id , $count);
        $since_id = 0;
        
        foreach ($list as &$row){
            $row['storyId'] = $row['story_id'];
            $row['cateId'] = $row['cate_id'];
            $row['sourceId'] = $row['source_id'];

            $images = explode(';,;' , $row['images']);
            $row['imageCount'] = count($images);
            $row['images'] = $images;
            
            $row['postTime'] = $row['story_date'];
            $row['display_mode'] = 'default';
            $row['gourl'] = '';
            
            $row['favCount'] = $row['fav_count'];
            $row['likeCount'] = $row['like_count'];
            $row['commentsCount'] = $row['comments_count'];
            
            unset($row['story_id']);
            unset($row['cate_id']);
            unset($row['source_id']);
            unset($row['fav_count']);
            unset($row['like_count']);
            unset($row['comments_count']);
            
            $since_id = $row['id'];
        }
        
        $result = array();
        $result['status'] = 'succ';
        $result['info']['statuses'] = $list;
        $result['info']['updateCount'] = count($list);
        $result['info']['sinceId'] = $since_id;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 新闻页内容
     *
     */
    public function page(){
        //新闻ID
        $story_id = (int)$_GET['storyId'];
        //页码
        $page = (int)$_GET['page'];
        
        $M_story = new StoryModel();
        $info = $M_story -> getStoryPage($story_id , $page);
        
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $info;
        
        echo $this -> apiEncode($result);
        exit();       
        
    }
    
    /**
     * 视频内容
     *
     */
    public function video(){
        //视频ID
        $video_id = $_GET['videoId'];
        
        $M_story = new StoryModel();
        $info = $M_story -> getVideo($video_id);
        
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $info;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 图片新闻内容
     *
     */
    public function photo(){
        
    }
    
    /**
     * 搜索结果列表
     *
     */
    public function search(){
        $keyword    =   $_GET['keyword'];
        $since_id   = (int)$_GET['sinceId'];
        $count      = (int)$_GET['count'];
        
        $M_news = new NewsModel();
        $list = $M_news -> search($keyword , $since_id , $count);
        
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $list;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 搜索关键词推荐
     *
     */
    public function hotWord(){
        
    }
    
    /**
     * 发布评论
     *
     */
    public function post(){
        $uid = $_SESSION['user_id'];
        if($uid > 0){
            $M_news = new NewsModel();
            $comment_id = $M_news -> comments((int)$_POST['newsId'] , $_POST['post'] , $uid , $_POST['replyId']);
        }
        
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $comment_id;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 评论列表
     *
     */
    public function comments(){
        $M_news = new NewsModel();
        $list = $M_news -> commentsList((int)$_GET['newsId'] , (int)$_GET['sinceId'] , (int)$_GET['count'] );
        
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $list;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 删除评论
     *
     */
    public function delComment(){
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> commentsDel((int)$_POST['commentId'] , $uid);
        }
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $res;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 点赞
     *
     */
    public function ding(){
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> likeAdd((int)$_POST['newsId'] , $uid);
        }
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $res;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 取消点赞
     *
     */
    public function unding(){
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> likeDel((int)$_POST['newsId'] , $uid);
        }
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $res;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 收藏
     * 
     */
    public function fav(){
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> favAdd((int)$_POST['newsId'] , $uid);
        }
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $res;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 取消收藏
     *
     */
    public function unfav(){
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> favDel((int)$_POST['newsId'] , $uid);
        }
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $res;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 收藏列表
     *
     */
    public function favList(){
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> favList($uid , (int)$_GET['sinceId'] , (int)$_GET['count']);
        }
        
        $list = array();
        foreach ($res as $row){
            $list[] = $M_news -> getNews($row['news_id']);
        }
        
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $list;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 订阅
     *
     */
    public function follow(){
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> followAdd((int)$_POST['sourceId'] , $uid);
        }
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $res;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 取消订阅
     *
     */
    public function unfollow(){
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> followDel((int)$_POST['sourceId'] , $uid);
        }
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $res;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
    /**
     * 订阅列表
     *
     */
    public function followList(){
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> followList($uid);
        }
        
        $list = array();
        foreach ($res as $row){
            $list[] = $M_news -> getSource($row['source_id']);
        }
        
        $result = array();
        $result['status'] = 'succ';
        $result['info'] = $list;
        
        echo $this -> apiEncode($result);
        exit();
    }
    
}