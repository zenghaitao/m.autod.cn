<?php
namespace API\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;
use Admin\Model\SnatchModel;

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
        
        $since_id = (int)$_GET['sinceId'];
        if(!$since_id)
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
        if($news['imageCount'] == 3)
            $news['displayMode'] = 'B';
        else 
            $news['displayMode'] = 'A';
        $news['type'] = $row['type'];
        $news['openMode'] = $row['open_mode'];
        $news['gourl'] = '';
        
        $news['favCount'] = $row['fav_count'];
        $news['likeCount'] = $row['like_count'];
        $news['commentsCount'] = $row['comments_count'];
        
        $news['hot'] = rand(1000 , 9999);
        $news['faved'] = 'no';
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
    public function sourceNewsList(){  
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
        
        $info = $M_news -> getSource($source_id);
        $info = $this -> formatSource($info);
        
        $result = array();
        $result['statuses'] = $list;
        $result['source'] = $info;
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
        $news_id = (int)$_GET['newsId'];
        
        $M_news = new NewsModel();
        $news_info = $M_news -> getNews($news_id);
        $news_info = $this -> formatNews($news_info);
        
        $M_story = new StoryModel();
        $page_html = $M_story -> getStoryPage($news_info['storyId']);
        
        $news_info['page'] = $page_html;
        
        /* 是否已点赞 */
        if($_SESSION['user_id']){
            $liked = $M_news -> liked($news_id , $_SESSION['user_id']);
            $news_info['liked'] = $liked?'yes':'no';
        }else{
            $news_info['liked'] = 'no';
        }
        
        /* 是否已收藏 */
        if($_SESSION['user_id']){
            $faved = $M_news -> faved($news_id , $_SESSION['user_id']);
            $news_info['faved'] = $faved?'yes':'no';
        }else{
            $news_info['faved'] = 'no';
        }
        
        /* 是否已订阅 */
        if($_SESSION['user_id']){
            $followed = $M_news -> faved($news_info['sourceId'] , $_SESSION['user_id']);
            $news_info['followed'] = $followed?'yes':'no';
        }else{
            $news_info['followed'] = 'no';
        }
        
        /*获取相关新闻*/
        $relate_news = $M_news -> getRelatedNews( $news_id , $news_info['cateId']);
        foreach ($relate_news as &$row){
            $row = $this -> formatNews($row);
        }
        $news_info['relate'] = $relate_news;
        
        //记录hot值
        $M_news -> incHot($news_id);
        
        $this -> succ($news_info);
        
    }
    
    /**
     * 视频内容
     *
     */
    public function video(){
        $news_id = $_GET['newsId'];
        
        $M_news = new NewsModel();
        $news_info = $M_news -> getNews($news_id);
        $news_info = $this -> formatNews($news_info);
        
        $M_story = new StoryModel();
        $info = $M_story -> getVideo($news_info['storyId']);
        
        $news_info['videoId'] = $info['videoid'];
        $news_info['time'] = $info['time'];
        
        /*获取相关视频*/
        $relate_news = $M_news -> getRelatedNews( $news_id , $news_info['cateId']);
        foreach ($relate_news as &$row){
            $row = $this -> formatNews($row);
        }
        $news_info['relate'] = $relate_news;
        
        //记录hot值
        $M_news -> incHot($news_id);
        
        $this -> succ($news_info);
    }
    
    /**
     * 图片新闻内容
     *
     */
    public function photo(){
        //新闻ID
        $news_id = (int)$_GET['newsId'];
        
        $M_news = new NewsModel();
        $news_info = $M_news -> getNews($news_id);
        $news_info = $this -> formatNews($news_info);
        
        $M_story = new StoryModel();
        $page_html = $M_story -> getStoryPage($news_info['storyId']);
        
        $images = strip_tags($page_html , "<img>");
        $M_snatch = new SnatchModel();
        $res = $M_snatch -> img($images);
        
        $news_info['imageCount'] = count($res);
        $news_info['images'] = $res;
        
        $this -> succ($news_info);
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
     * 搜索关键词推荐
     *
     */
    public function hotWord(){
        $keyword = array("凯迪拉克",
                          "宝马",
                          "奔驰",
                          "英菲尼迪",
                          "奥迪",
                          "保时捷",
                          "大众",
                          "丰田",
                          "雪铁龙",
                          "本田",
                          "日产",
                          "特斯拉");
        $result = array();
        $result['word'] = $keyword;
        $result['wordCount'] = count($keyword);
        
        $this -> succ($result);
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
        $uid = $_SESSION['user_id'];
        
        if(!$_GET['sinceId']){
            /* 热门评论列表 */
            $ids = array();
            $hot = $M_news -> commentsHotList((int)$_GET['newsId']);
            foreach ($hot as &$row){
                $row = $this -> formatComment($row);
                $ids[] = $row['id'];
            }
            $ids = implode(',' , $ids);
            $liked_list = $M_news -> commentLiked($ids , $uid);
            foreach ($hot as &$row){
                $row['liked'] = $liked_list[$row['id']];
            }
        }
        
        
        /* 评论列表 */
        $list = $M_news -> commentsList((int)$_GET['newsId'] , (int)$_GET['sinceId'] , (int)$_GET['count'] );
        
        $ids = array();
        foreach ($list as &$row){
            $row = $this -> formatComment($row);
            $since_id = $row['id'];
            $ids[] = $row['id'];
        }
        $ids = implode(',' , $ids);
        
        $liked_list = $M_news -> commentLiked($ids , $uid);
        foreach ($list as &$row){
            $row['liked'] = $liked_list[$row['id']];
        }
        
        $result = array();
        $result['commentHotList'] = $hot;
        $result['commentList'] = $list;
        $result['sinceId'] = $since_id;
        $result['updateCount'] = count($list);
        $this -> succ($result);
    }
    
    /**
     * 格式化评论信息
     *
     * @param array $comment
     * @return array
     */
    public function formatComment($comment){
        $data = $comment;
        $data['newsId'] = $comment['news_id'];
        $data['replyId'] = $comment['reply_id'];
        $data['replyUid'] = $comment['reply_uid'];
        $data['replyPost'] = $comment['reply_post'];
        $data['replyUsername'] = $comment['reply_username'];
        $data['replyUserphoto'] = $comment['reply_userphoto'];
        $data['likeCount'] = $comment['like_count'];
        
        unset($data['news_id']);
        unset($data['reply_id']);
        unset($data['reply_uid']);
        unset($data['reply_post']);
        unset($data['reply_username']);
        unset($data['reply_userphoto']);
        unset($data['like_count']);
        
        return $data;
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
     * 评论点赞
     *
     */
    public function commentLike(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        $M_news = new NewsModel();
        $res = $M_news -> commentLike( $_POST['commentId'] , $uid);
        $this -> succ($res);
    }
    
    /**
     * 取消评论点赞
     *
     */
    public function commentUnlike(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        $M_news = new NewsModel();
        $res = $M_news -> commentUnlike( $_POST['commentId'] , $uid);
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
        
        if($uid && $_POST['sourceId']){
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
            $row = $M_news -> getSource($row['source_id']);
            $row = $this -> formatSource($row);
            $row['followed'] = 'yes';
            $list[] = $row;
        }
        
        $this -> succ(array('sourceList'=>$list));
    }
    
    /**
     * 待订阅列表
     *
     */
    public function sourceList(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        $M_news = new NewsModel();
        $list = $M_news -> getSoureList();
        
        $follows = $M_news -> followList($uid);
        $follow_ids = array();
        foreach ($follows as $row){
            $follow_ids[] = $row['source_id'];
        }
        
        foreach ($list as &$row){
            $row = $this -> formatSource($row);
            
            if(in_array($row['id'] , $follow_ids)){
                $row['followed'] = 'yes';
            }else{
                $row['followed'] = 'no';
            }
        }
        
        $this -> succ(array('sourceList'=>$list));
    }
    
    /**
     * 格式化新闻源信息
     *
     * @param array $info
     * @return array
     */
    private function formatSource($info){
        $data = $info;
        $data['lastNews'] = $info['last_news'];
        $data['lastTime'] = $info['last_time'];
        $data['photo'] = 'http://autod.b0.upaiyun.com/autod_img/source_logo/face.jpg';
        unset($data['last_news']);
        unset($data['last_time']);
        return $data;
    }
    
}