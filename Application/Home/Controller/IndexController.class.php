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
        $news['type'] = $row['type'];
        $news['openMode'] = $row['open_mode'];
        $news['gourl'] = '';
        
        if($news['imageCount'] == 3)
            $news['displayMode'] = 'B';
        else 
            $news['displayMode'] = 'A';
        
        $news['favCount'] = $row['fav_count'];
        $news['likeCount'] = $row['like_count'];
        $news['commentsCount'] = $row['comments_count'];
        
        $news['hot'] = rand(1000 , 9999);
        
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
        
        //记录hot值
        $M_news -> incHot($news_id);
        
        //页面内容
        $M_story = new StoryModel();
        $page = $M_story -> getStoryPage($info['story_id']);
        
        //热门评论
        $comments = $M_news -> commentsList($news_id , 0 , 50);
        
        //相关新闻
        $relates = $M_news -> getRelatedNews($news_id , 0 , 20);
        
        $this -> assign('info' , $info);
        $this -> assign('page' , $page);
        $this -> assign('comments' , $comments);
        $this -> assign('relates' , $relates);
        
        $this -> display('page');
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
     * 评论列表
     *
     */
    public function comments(){
        $M_news = new NewsModel();
        $list = $M_news -> commentsList((int)$_GET['newsId'] , (int)$_GET['sinceId'] , (int)$_GET['count'] );
        
        $this -> succ($list);
    }

}