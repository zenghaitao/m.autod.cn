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
    
    /**
     * 推荐新闻列表
     *
     */
    public function index(){
        $page = $_GET['page'];
        if(!isset($_GET['page']))
            $page = 'none';
        elseif ($page != 'down')
            $page = 'up';
            
        //最小id
        $since_id = (int)$_GET['sinceId'];
        //最大id
        $max_id = (int)$_GET['maxId'];
        //返回记录数
        $count = rand(8 , 14);
        if($page == 'none')
            $count = rand(18 , 28);
        
        //客户端是否刷新列表
        $refresh = 'no';
        //新入新闻行数
        $laest_news_count = 0;
        //新入新闻
        $laest_news = array();
        //拉取新闻
        $pull_news = array();
        //填补新闻
        $other_news = array();
        
        $M_news = new NewsModel();
        
        $M_news -> initTodayNews();
        
        if($max_id){
            $result = $M_news -> laestNews($max_id , $count);
            if($result['count'] > $count)
                $refresh = 'yes';
            
            if($result['list']){
                $laest_news = $result['list'];
                $end_laest_news = end($laest_news);
            }
            $laest_news_count = count($result['list']);
        }
        
        //更新客户端新闻行数
        $count = $count - $laest_news_count;
        
        if($refresh != 'yes' && $count > 1){
            //继续获取新闻
            $pull_news = $M_news -> pullNews($since_id , $count , $page);
            $count = $count - count($pull_news);
        }
        
        if($count > 0){
            //填充新闻
            $other_news = $M_news -> pullNews((int)$end_laest_news['id'] , $count , 'down');
        }
        
        //合并三份数据
        $list = array_merge ( $laest_news ,  $pull_news , $other_news);
        
        foreach ($list as &$row){
            //格式化新闻行记录
            $row = $this -> formatNews($row);
            if($row['id'] > $max_id)
                $max_id = $row['id'];
        }
        $since_id = (int)$row['id'];
        
        //获取广告数据
        $ad = $this -> newsAD();
        
        //合并新闻和广告
        $list = $this -> mergeList($list , $ad);
        
        if($page == 'none')
            $refresh = 'yes';
        
        $result = array();
        $result['statuses'] = $list;
        $result['updateCount'] = count($list);
        $result['sinceId'] = $since_id;
        $result['maxId'] = $max_id;
        $result['refresh'] = $refresh;
        $this -> succ($result);
    }
    
    /**
     * 合并推荐列表
     *
     * @param array $news
     * @param array $ads
     * @return array
     */
    private function mergeList($news , $ads){
        $i = 0;
        $j = 0;
        $list = array();
        
        foreach ($news as $row){
            $i++;
            $list[] = $row;
            if($i % 3 == 0){
                if(isset($ads[$j])){
                    $list[] = $ads[$j];
                    $j ++;
                }
            }
        }
        
        return $list;
    }
    
    /**
     * Enter description here...
     *
     * @return unknown
     */
    private function newsAD(){
        //获取广告数据
        $ad['title'] = '沃尔沃XC90全新上市';
        $ad['images'] = 'http://img1.126.net/channel12/020138/60095_0629.jpg';
        $ad['type'] = 'ad';
        $ad['gourl'] = 'http://m.xc90.volvocars.com.cn';
        $ad = $this -> formatNews($ad);
        
        $ad['openMode'] = 'topic';
        $ad['displayMode'] = 'C';
        
        //获取广告数据
        $ad1['title'] = '爱卡汽车你我的新选择';
        $ad1['images'] = 'http://p2.pstatp.com/origin/2499/7735513385';
        $ad1['type'] = 'ad';
        $ad1['gourl'] = 'http://m.xcar.com.cn';
        $ad1 = $this -> formatNews($ad1);
        
        $ad1['openMode'] = 'topic';
        $ad1['displayMode'] = 'C';
        
        return array($ad ,$ad1);
    }
    
    /**
     * 格式化新闻行记录
     *
     * @param array $row
     * @return array
     */
    private function formatNews($row){
        
        $news['id'] = (int)$row['id'];
        $news['storyId'] = (int)$row['story_id'];
        $news['cateId'] = (int)$row['cate_id'];
        $news['title'] = (string)$row['title'];
        $news['summary'] = (string)$row['summary'];
        $news['source'] = (string)$row['source'];
        $news['sourceId'] = (int)$row['source_id'];
        $images = explode(';,;' , $row['images']);
        $news['imageCount'] = count($images);
        $news['images'] = (array)$images;
        
        $news['postTime'] = (string)$row['story_date'];
        $news['type'] = $row['type'];
        if($news['imageCount'] == 3)
            $news['displayMode'] = 'B';
        else 
            $news['displayMode'] = 'A';
        if($news['type'] == 'ad')
            $news['displayMode'] = 'C';
        $news['openMode'] = (string)$row['open_mode'];
        if($news['imageCount'] > 1)
            $news['openMode'] = 'image';
            
        $news['gourl'] = (string)$row['gourl'];
        
        $news['favCount'] = (int)$row['fav_count'];
        $news['likeCount'] = (int)$row['like_count'];
        $news['commentsCount'] = (int)$row['comments_count'];
        
        $news['hot'] = (int)$row['hot'];
        
        if($news['type'] == 'default' && $news['type']){
            if($news['hot'] > 1900)
                $news['type'] = 'head';
            elseif($news['hot'] > 1600)
                $news['type'] = 'recommend';
            elseif($news['hot'] > 1000)
                $news['type'] = 'hot';
        }
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
        $list = $M_news -> newsSourceList($source_id , $begin_id , $count);
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
        $page_result = $M_story -> getStoryPage($news_info['storyId']);
        
        $page_html = $page_result['html'];
        
        $this -> assign('page' , $page_html);
        $this -> assign('host' , 'http://'.$_SERVER['HTTP_HOST']);
        $html = $this -> fetch('page');
        
        if($_GET['show'] == 'page')
            die($html);
        
        $news_info['page'] = $html;
        $news_info['pageImages'] = $page_result['images'];
        
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
        
        /* 来源详细信息 */
        $source_info = array();
        if($news_info['sourceId']){
            $source_info = $M_news -> getSource($news_info['sourceId']);
            $this -> formatSource($source_info);
        }
        $news_info['sourceInfo'] = $source_info;
        
        /* 是否已订阅 */
        if($_SESSION['user_id']){
            $followed = $M_news -> followed($news_info['sourceId'] , $_SESSION['user_id']);
            $news_info['followed'] = $followed?'yes':'no';
        }else{
            $news_info['followed'] = 'no';
        }
        
        /* 来源详细信息 */
        
        /*获取相关新闻*/
        $relate_news = $M_news -> getRelatedNews( $news_id , $news_info['cateId']);
        foreach ($relate_news as &$row){
            $row = $this -> formatNews($row);
        }
        $news_info['relate'] = $relate_news;
        
        /* 获取广告数据 */
        $ad['title'] = '沃尔沃XC90全新上市';
        $ad['images'] = 'http://img1.126.net/channel12/020138/60095_0629.jpg';
        $ad['type'] = 'ad';
        $ad['openMode'] = 'topic';
        $ad['gourl'] = 'http://m.xc90.volvocars.com.cn';
        
        $news_info['ad'] = $ad;
        
        
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
        $story_info = $M_story -> getStoryInfo($news_info['storyId']);
        $video_info = $M_story -> getVideo($story_info['article_id']);
        
        $this -> assign('video' , $video_info);
        $this -> assign('host' , 'http://'.$_SERVER['HTTP_HOST']);
        $html = $this -> fetch('video');
        
        if($_GET['show'] == 'page')
            die($html);
        
        $news_info['videoId'] = $video_info['videoid'];
        $news_info['time'] = $video_info['time'];
        $news_info['videoHtml'] = $html;
        
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
        
        /* 来源详细信息 */
        $source_info = array();
        if($news_info['sourceId']){
            $source_info = $M_news -> getSource($news_info['sourceId']);
            $this -> formatSource($source_info);
        }
        $news_info['sourceInfo'] = $source_info;
        
        /* 是否已订阅 */
        if($_SESSION['user_id']){
            $followed = $M_news -> followed($news_info['sourceId'] , $_SESSION['user_id']);
            $news_info['followed'] = $followed?'yes':'no';
        }else{
            $news_info['followed'] = 'no';
        }
        
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
        $page_info = $M_story -> getStoryPage($news_info['story_id']);
        
        $news_info['imageCount'] = $page_info['image_count'];
        
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
        
        /* 来源详细信息 */
        $source_info = array();
        if($news_info['sourceId']){
            $source_info = $M_news -> getSource($news_info['sourceId']);
            $this -> formatSource($source_info);
        }
        $news_info['sourceInfo'] = $source_info;
        
        /* 是否已订阅 */
        if($_SESSION['user_id']){
            $followed = $M_news -> followed($news_info['sourceId'] , $_SESSION['user_id']);
            $news_info['followed'] = $followed?'yes':'no';
        }else{
            $news_info['followed'] = 'no';
        }
        
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
     * 我的评论列表
     *
     */
    public function myCommentList(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $since_id = (int)$_GET['sinceId'];
        
        $M_news = new NewsModel();
        $list = $M_news -> myCommentList($uid , $since_id);
        foreach ($list as &$row){
            $row = $this -> formatComment($row);
            $since_id = $row['id'];
            $news = $M_news -> getNews($row['newsId']);
            $row['newsInfo'] = $this -> formatNews($news);
        }
        
        $result = array();
        $result['myCommentList'] = $list;
        $result['sinceId'] = $since_id;
        $result['updateCount'] = count($list);
        $this -> succ($result);
    }
    
    /**
     * 评论列表
     *
     */
    public function comments(){
        $M_news = new NewsModel();
        $uid = $_SESSION['user_id'];
        
        $hot = array();
        $since_id = 0;
        
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
        
        $news_info = $M_news -> getNews((int)$_GET['newsId']);
        
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
        $result['title'] = $news_info['title'];
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
        
        if($res)
            $this -> succ($res);
        else 
            $this -> fail(102);
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
        
        if($res)
            $this -> succ($res);
        else 
            $this -> fail(102);
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
        
        if($res)
            $this -> succ($res);
        else 
            $this -> fail(102);
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
        if($res)
            $this -> succ($res);
        else 
            $this -> fail(102);
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
        if($res)
            $this -> succ($res);
        else 
            $this -> fail(102);
    }
    
    /**
     * 收藏
     * 
     */
    public function fav(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        if($uid && (int)$_POST['newsId']){
            $M_news = new NewsModel();
            $res = $M_news -> favAdd((int)$_POST['newsId'] , $uid);
        }
        if($res)
            $this -> succ($res);
        else 
            $this -> fail(102);
    }
    
    /**
     * 取消收藏
     *
     */
    public function unfav(){
        //此方法需要用户登录后操作
        $this -> mustLogin();
        
        $uid = $_SESSION['user_id'];
        
        if($uid && (int)$_POST['newsId']){
            $M_news = new NewsModel();
            $res = $M_news -> favDel((int)$_POST['newsId'] , $uid);
        }
        if($res)
            $this -> succ($res);
        else 
            $this -> fail(102);
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
        if($res)
            $this -> succ($res);
        else 
            $this -> fail(102);
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
        if($res)
            $this -> succ($res);
        else 
            $this -> fail(102);
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
        $data['photo'] = $info['icon'];
        $data['lastNews'] = $info['last_news'];
        $data['lastTime'] = $info['last_time'];
        unset($data['last_news']);
        unset($data['last_time']);
        return $data;
    }
    
}