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
            $M_news -> addToChoice($row['id']);
        }
    }
    
    public function reset(){
        session_destroy();
    }
    
    /**
     * 推荐新闻列表
     *
     */
    public function index(){
        
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
        $result['info']['update_count'] = count($list);
        
        echo $this -> apiEncode($result);
        exit();
        
    }
    
    /**
     * 分类新闻列表
     *
     */
    public function cateList(){
        
    }
    
    /**
     * 视频列表
     *
     */
    public function videoList(){
        
    }
    
    /**
     * 媒体新闻列表
     *
     */
    public function mediaList(){  
        
    }
    
    /**
     * 新闻页内容
     *
     */
    public function page(){
        
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
        
    }
    
    /**
     * 评论列表
     *
     */
    public function comments(){
        
    }
    
    /**
     * 删除评论
     *
     */
    public function delComment(){
        
    }
    
    /**
     * 点赞
     *
     */
    public function ding(){
        
    }
    
    /**
     * 取消点赞
     *
     */
    public function unding(){
        
    }
    
    /**
     * 收藏
     * 
     */
    public function fav(){
        
    }
    
    /**
     * 取消收藏
     *
     */
    public function unfav(){
        
    }
    
    /**
     * 订阅
     *
     */
    public function follow(){
        
    }
    
    /**
     * 取消订阅
     *
     */
    public function unfollow(){
        
    }
    
    /**
     * 订阅列表
     *
     */
    public function followList(){
        
    }
    
}