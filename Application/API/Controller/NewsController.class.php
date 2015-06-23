<?php
namespace API\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;

class NewsController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
    }
    
    /**
     * 推荐新闻列表
     *
     */
    public function index(){
        
        $ids = $_SESSION['uids'];
        $ids = explode(',' , $ids);
        $uids = array();
        
        $M_news = new NewsModel();
        $list = $M_news -> newsPool($ids);
        
        foreach ($list as &$row){
            $uids[] = $row['story_id'];
            
            $images = explode(';,;' , $row['images']);
            $row['image_count'] = count($images);
            $row['images'] = $images;
            
            $row['post_time'] = $row['story_date'];
            $row['display_mode'] = 'default';
            $row['gourl'] = '';
            
        }
        
        var_dump($list);
        
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