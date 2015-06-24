<?php
namespace Home\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;

class IndexController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
    }
    
    public function index(){
        return false;
    }
    
    /**
     * 推荐新闻列表
     *
     */
    public function news(){
        
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
            $row['displayMode'] = 'default';
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
            unset($row['story_date']);

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
     * 评论列表
     *
     */
    public function comments(){
        $M_news = new NewsModel();
        $list = $M_news -> commentsList((int)$_GET['newsId'] , (int)$_GET['sinceId'] , (int)$_GET['count'] );
        
        $this -> succ($list);
    }
}