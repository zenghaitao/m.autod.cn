<?php
namespace API\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;

class NewsController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
        $this -> checkPermission();
    }
    
    public function init(){
        return false;
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
            //格式化新闻行记录
            $row = $this -> formatNews($row);
            
            if(count($uids) > 80){
                array_shift($uids);
            }
        }
        
        $_SESSION['uids'] = implode(',' , $uids);
        
        $result = array();
        $result['statuses'] = $list;
        $result['updateCount'] = count($list);
        $result['nextPage'] = $page + 1;
        $result['prevPage'] = $page - 1;

        $this -> succ($result);
    }
    
    /**
     * 格式化新闻行记录
     *
     * @param array $row
     * @return array
     */
    private function formatNews($row){
        
        $news['id'] = $row['id'];
        $news['storyId'] = $row['story_id'];
        $news['cateId'] = $row['cate_id'];
        $news['title'] = $row['title'];
        $news['summary'] = $row['summary'];
        $news['source'] = $row['source'];
        $news['sourceId'] = $row['source_id'];

        $images = explode(';,;' , $row['images']);
        $news['imageCount'] = count($images);
        $news['images'] = $images;
        
        $news['postTime'] = $row['story_date'];
        $news['displayMode'] = 'default';
        $news['type'] = $row['type'];
        $news['openMode'] = $row['open_mode'];
        $news['gourl'] = '';
        
        $news['favCount'] = $row['fav_count'];
        $news['likeCount'] = $row['like_count'];
        $news['commentsCount'] = $row['comments_count'];
        
        return $news;
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
            //格式化新闻行记录
            $row = $this -> formatNews($row);
            
            $since_id = $row['id'];
        }
        
        $result = array();
        $result['statuses'] = $list;
        $result['updateCount'] = count($list);
        $result['sinceId'] = $since_id;
        
        $this -> succ($result);
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
            //格式化新闻行记录
            $row = $this -> formatNews($row);
            
            $since_id = $row['id'];
        }
        
        $result = array();
        $result['statuses'] = $list;
        $result['updateCount'] = count($list);
        $result['sinceId'] = $since_id;
        
        $this -> succ($result);
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
        
        $this -> succ($info);
        
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
        
        $this -> succ($info);
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
        
        $this -> succ($list);
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
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        if($uid > 0){
            $M_news = new NewsModel();
            $comment_id = $M_news -> comments((int)$_POST['newsId'] , $_POST['post'] , $uid , $_POST['replyId']);
        }
        
        $this -> succ($comment_id);
    }
    
    /**
     * 评论列表
     *
     */
    public function comments(){
        $M_news = new NewsModel();
        $list = $M_news -> commentsList((int)$_GET['newsId'] , (int)$_GET['sinceId'] , (int)$_GET['count'] );
        
        $this -> succ($list);
    }
    
    /**
     * 删除评论
     *
     */
    public function delComment(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> commentsDel((int)$_POST['commentId'] , $uid);
        }
        
        $this -> succ($res);
    }
    
    /**
     * 点赞
     *
     */
    public function ding(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> likeAdd((int)$_POST['newsId'] , $uid);
        }
        $this -> succ($res);
    }
    
    /**
     * 取消点赞
     *
     */
    public function unding(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> likeDel((int)$_POST['newsId'] , $uid);
        }
        $this -> succ($res);
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
        $this -> succ($res);
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
        $this -> succ($res);
    }
    
    /**
     * 收藏列表
     *
     */
    public function favList(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> favList($uid , (int)$_GET['sinceId'] , (int)$_GET['count']);
        }
        
        $list = array();
        foreach ($res as $row){
            $list[] = $M_news -> getNews($row['news_id']);
        }
        
        $this -> succ($list);
    }
    
    /**
     * 订阅
     *
     */
    public function follow(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> followAdd((int)$_POST['sourceId'] , $uid);
        }
        $this -> succ($res);
    }
    
    /**
     * 取消订阅
     *
     */
    public function unfollow(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> followDel((int)$_POST['sourceId'] , $uid);
        }
        $this -> succ($res);
    }
    
    /**
     * 订阅列表
     *
     */
    public function followList(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        if($uid){
            $M_news = new NewsModel();
            $res = $M_news -> followList($uid);
        }
        
        $list = array();
        foreach ($res as $row){
            $list[] = $M_news -> getSource($row['source_id']);
        }
        
        $this -> succ($list);
    }
    
}