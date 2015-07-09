<?php
namespace Home\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;
use Home\Model\SnatchModel;

class IndexController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
    }
    
    public function index(){
        $this -> display('index');
    }
    
    /**
     * 推荐新闻列表
     *
     */
    public function news(){
        
        $page = $_GET['page'];
        if(!isset($_GET['page']) && $page != 'down' && $page != 'up')
            $page = 'none';
            
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
     * 格式化时间字符串
     *
     * @param string $date
     * @return string
     */
    private function formatTime($date){
        $time = strtotime($date);
        $now = time();
        
        $day = date('m-d' , $time);
        $today = date('m-d');
        
        $diff = $now - $time;
        
        $diff_min = ceil($diff / 60);
        $diff_5min = ceil($diff / 60 / 5) * 5;
        $diff_15min = ceil($diff / 60 / 15) * 15;
        $diff_hor = round($diff / 3600);
        
        if($diff < 60)
            return '刚刚';
        if($diff_min <= 5)
            return $diff_min.'分钟前';
        if($diff_min <= 30)
            return $diff_5min.'分钟前';
        
        if($diff_min <= 60)
            return $diff_15min.'分钟前';
            
        if($diff_min < 60 * 6)
            return $diff_hor.'小时前';
        
        if($day == $today)
            return date('H:i' , $time);
        
        return $day;
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
        $news['timeString'] = $this -> formatTime($row['story_date']);
        
        $news['type'] = $row['type'];
        if($news['imageCount'] == 3)
            $news['displayMode'] = 'B';
        else 
            $news['displayMode'] = 'A';
        if($news['type'] == 'ad')
            $news['displayMode'] = 'C';
        $news['openMode'] = (string)$row['open_mode'];
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
            $row = $this -> formatNews($row);
            $since_id = $row['id'];
        }
        
        $this -> assign('list' , $list);
        $this -> assign('count' , count($list));
        
        if($cate_id == 20){
            $this -> display('list1');
        }
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
        $news_id = (int)$_GET['id'];
        
        //新闻信息
        $M_news = new NewsModel();
        $info = $M_news -> getNews($news_id);
        
        //来源信息
        $source = $M_news -> getSource($info['source_id']);
        
        $this -> assign('source' , $source);
        
        //广告信息
        $ad = $this -> newsAD();
        shuffle($ad);
        
        $this -> assign('ad' , end($ad));
        
        //记录hot值
        $M_news -> incHot($news_id);
        
        if($info['open_mode'] == 'video'){
            $this -> video($info);
            exit;
        }
        
        if($info['open_mode'] == 'topic'){
            $this -> topic($info);
        }
        
        if($info['open_mode'] == 'news'){
            $this -> content($info);
        }
        
        if($info['open_mode'] == 'pic'){
            $this -> photo($info);
        }
        
        if($info['open_mode'] == 'sns'){
            $this -> sns($info);
        }
    }
    
    /**
     * 新闻内容
     *
     */
    private function content($info){
        $news_id = $info['id'];
        $M_news = new NewsModel();
        //页面内容
        $M_story = new StoryModel();
        $page = $M_story -> getStoryPage($info['story_id']);
        
        //热门评论
        $comments = $M_news -> commentsList($news_id , 0 , 50);
        
        //相关新闻
        $relates = $M_news -> getRelatedNews($news_id , 0 , 10);
        foreach ($relates as &$row){
            $row = $this -> formatNews($row);
        }
        
        $this -> assign('info' , $info);
        $this -> assign('page' , $page);
        $this -> assign('comments' , $comments);
        $this -> assign('relates' , $relates);
        
        $this -> display('page1');
    }
    
    /**
     * 视频内容
     *
     */
    private function video($info){
        $news_id = $info['id'];
        $M_news = new NewsModel();
        
        //页面内容
        $M_story = new StoryModel();
        $story = $M_story -> getStoryInfo($info['story_id']);
        $video = $M_story -> getVideo($story['article_id']);
        
        $this -> assign('info' , $info);
        $this -> assign('video' , $video);
        
        if(isset($_GET['mini'])){
            $this -> display('video-mini');
            exit;
        }
        
        //热门评论
        $comments = $M_news -> commentsList($news_id , 0 , 50);
        
        //相关新闻
        $relates = $M_news -> getRelatedNews($news_id , 20 , 20);
        foreach ($relates as &$row){
            $row = $this -> formatNews($row);
        }
        
        $this -> assign('comments' , $comments);
        $this -> assign('relates' , $relates);
        
        $this -> display('video1');
    }
    
    /**
     * APP下载跳转链接
     *
     */
    public function download(){
       $ios = 'https://itunes.apple.com/cn/app/qi-che-ri-bao/id850404817?mt=8';
       $android = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.ina.car&g_f=991653';
       
       $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
       if(strpos( $user_agent , 'iphone') !== false || strpos( $user_agent , 'ipad') !== false){
           header("Location:{$ios}");
       }else{
           header("Location:{$android}");
       }
       exit;
    }
    

}