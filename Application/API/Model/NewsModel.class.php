<?php
namespace API\Model;

use Admin\Model\CarModel;

class NewsModel
{

    protected $_db_news_choice;
    protected $_db_news_comments;
    protected $_db_news_like;
    protected $_db_news_fav;
    protected $_db_news_follow;
    protected $_db_news_source;
    protected $_db_news_cate;
    protected $_db_news_comments_like;
    protected $_db_news_keyword;
    
    public function __construct(){
        $this -> _db_news_choice = M('news_choice' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_comments = M('news_comments' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_like = M('news_like' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_fav = M('news_fav' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_follow = M('news_follow' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_source = M('news_source' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_cate = M('news_cate' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_comments_like = M('news_comments_like' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_spider_page = M('news_spider_page' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_keyword = M('news_keyword' , 'ad_' , 'DB0_CONFIG');
    }
    
    /**
     * 获取最新的新闻
     *
     * @param int $max_id
     */
    public function laestNews($max_id , $count){
        $today = date('Y-m-d');
        $sum_count = $this -> _db_news_choice -> where("id > '{$max_id}' AND day = '{$today}'") -> count();
        $list = $this -> _db_news_choice -> where("id > '{$max_id}' AND day = '{$today}'") -> order('id DESC') -> limit($count) -> select();
        return array('list' => $list , 'count' => $sum_count);
    }
    
    /**
     * 获取更多新闻
     *
     * @param int $since_id
     * @param int $count
     * @param string $action
     */
    public function pullNews($since_id , $count , $action){
        $today = date('Y-m-d');
        $where_str = "1";
        if($since_id)
            $where_str = "id < '{$since_id}'";
        if($action !== 'down'){
            $where_str .= " AND day = '{$today}'";
        }
        $list = $this -> _db_news_choice -> where($where_str) -> order('id DESC') -> limit($count) -> select();
        return $list;
    }
    
    /**
     * 自动创建今日数据
     *
     */
    public function initTodayNews(){
        $today = date('Y-m-d');
        
        if($_SESSION['today'] == $today){
            return true;
        }
        
        $_SESSION['today'] = $today;
        $data['day'] = $today;
        
        $count = $this -> _db_news_choice -> where("day = '{$today}'") -> count();
        if($count < 100){
            $this -> _db_news_choice -> where("day != '{$today}'") -> order("id DESC") -> limit(100-$count) -> save($data);
        }
        return true;
    }
    
    /**
     * 获取单行新闻
     *
     * @param int $news_id
     * @return array
     */
    public function getNews($news_id){
        return $this -> _db_news_choice -> where("id = '{$news_id}'") -> find();
    }
    
    /**
     * 新闻热度值递增
     *
     * @param int $news_id
     * @return array
     */
    public function incHot($news_id){
        return $this -> _db_news_choice -> where("id = '{$news_id}'")->setInc('hot'); 
    }
    
    /**
     * 将指定文章添加到推荐
     *
     * @param int $story_id
     * @return mixed
     */
    public function addNewsToChoice($story_id){
        $M_news = new NewsModel();
        $M_car = new CarModel();
        $M_story = new StoryModel();
       
        
        $info = $M_story -> getStoryInfo($story_id , 'all');

        $data = array();
        $data['story_id'] = $story_id;
        $data['cate_id'] = $this -> convCateId($info['column_id']);
        $data['title'] = $info['title'];
        $data['summary'] = $info['short_summary'];
        $data['source'] = $info['source'];
        $data['source_id'] = $info['source_id']?$info['source_id']:0;
        $data['images'] = $this -> makeImages($info['title_pic1'] , $info['title_pic2'] , $info['title_pic3']);
        $data['story_date'] = $info['story_date']?$info['story_date']:date("Y-m-d H:i:s");
        $data['type'] = 'default';
        $data['fav_count'] = '0';
        $data['like_count'] = '0';
        $data['comments_count'] = '0';
        $data['day'] = date('Y-m-d');
        $data['add_time'] = date("Y-m-d H:i:s");
        $data['open_mode'] = "news";
        if($data['cate_id'] == 20)
            $data['open_mode'] = "video";
        $data['hot'] = rand(321,1999);
        
        if(!$data['images'])
            return 'no pic';
        
        $news_id = $this -> _db_news_choice -> add($data);
        
        if( $news_id ) {
            //更新is_choice状态
            $M_story -> updateStory($story_id , array('is_choice' => 'yes'));
            
        	$news_info = $this -> getNews($news_id);
        	
        	//分析此篇新闻的关键词并入库
        	$keywords = $M_car -> getAllKeywords();
        	$M_news -> addKeywordsToNewsKeyword($news_info,$keywords);
        	
        	return $news_id;
        }else{
        	return false;
        }
        
    }
    
    /**
     * 将栏目ID转化为分类ID
     *
     * @param int $columnId
     * @return int
     */
    private function convCateId($columnId){
        if($columnId)
            return $columnId;
        else 
            return 0;
    }
    
    /**
     * 合并缩略图信息字段
     *
     * @param string $pic1
     * @param string $pic2
     * @param string $pic3
     * @return string
     */
    private function makeImages($pic1 , $pic2 , $pic3){
        $images = $pic1;
        if($pic2 && $pic3)
            $images .= ';,;'.$pic2;
        if($pic3)
            $images .= ';,;'.$pic3;
        
        return $images;
    }
    
    /**
     * 转化文章状态标识
     *
     * @param unknown_type $is_top
     * @param unknown_type $is_hot
     * @param unknown_type $is_rec
     */
    private function convNewsType($is_top = '' , $is_hot = '' , $is_rec = ''){
        if($is_top == 'yes')
            return 'head';
        if($is_hot == 'yes')
            return 'hot';
        if($is_rec == 'yes')
            return 'recommend';
        return 'default';
    }
    
    
    /**
     * 将指定视频添加到推荐
     *
     * @param int $video_id
     * @return mixed
     */
    public function addVideoToChoice($video_id){
        
        $M_story = new StoryModel();
        
        $info = $M_story -> getVideo($video_id);
        
        //var_dump($info['title_pic1']);
        
        $data = array();
        $data['story_id'] = $video_id;
        $data['cate_id'] = 20;
        $data['title'] = $info['title'];
        $data['summary'] = $info['short'];
        $data['source'] = 'UUTV';
        $data['source_id'] = 0;
        $data['images'] = $this -> makeImages($info['img']);
        $data['story_date'] = $info['publish_time']?$info['publish_time']:date("Y-m-d H:i:s");
        $data['type'] = $this -> convNewsType($info['is_top'] , $info['is_hot'] , $info['is_rec']);
        $data['fav_count'] = '0';
        $data['like_count'] = '0';
        $data['comments_count'] = '0';
        $data['day'] = date('Y-m-d');
        $data['add_time'] = date("Y-m-d H:i:s");
        $data['open_mode'] = "video";
        
        if(!$data['images'])
            return false;
        
        return $this -> _db_news_choice -> add($data);
    }
    
    /**
     * 获取分类新闻列表
     *
     * @param int $cate_id
     * @param int $begin_id
     * @param int $count
     * @return array
     */
    public function newsCateList($cate_id = 0 , $begin_id = 0 , $count = 10){
        if(!$cate_id)
            $where_str = '1';
        else 
            $where_str = "cate_id = '{$cate_id}'";
        if($begin_id)
            $where_str .= " AND id < '{$begin_id}'";
        
        $list = $this -> _db_news_choice -> where($where_str) -> order("id DESC") -> limit($count) -> select();
        return $list;
    }
    
    public function newsList($cate_id , $page = 1 , $count = 10 ){
        if($cate_id == '-1')
            $where_str = '1';
        else 
            $where_str = "cate_id = '{$cate_id}'";
        
        $limit_str = ($page - 1)*$count.','.$count;
            
        $list = $this -> _db_news_choice -> where($where_str) -> limit($limit_str) -> order("id DESC") -> select();
        $count = $this -> _db_news_choice -> where($where_str) -> count();
        
        return array('list' => $list , 'count' => $count);
    }
    
    
    /**
     * 获取相关新闻
     *
     * @param unknown_type $news_id
     * @param unknown_type $cate_id
     */
    public function getRelatedNews($news_id , $cate_id = 0 , $count = 3){
        $where_str = "cate_id = '{$cate_id}' AND id != '{$news_id}'";
        $list = $this -> _db_news_choice -> where($where_str) -> order("id DESC") -> limit($count) -> select();
        return $list;
    }
    
    /**
     * 获取媒体新闻列表
     *
     * @param int $source_id
     * @param int $begin_id
     * @param int $count
     * @return array
     */
    public function newsSourceList($source_id , $begin_id = 0 , $count = 10){
        $where_str = "source_id = '{$source_id}'";
        if($begin_id)
            $where_str .= " AND id < '{$begin_id}'";
        
        $list = $this -> _db_news_choice -> where($where_str) -> order("id DESC") -> limit($count) -> select();
        return $list;
    }
    
    /**
     * 通过sourceid获取新闻列表
     * @author nj
     * @param $sourceid 来源id
     */
    public function getNewsBySourceId( $sourceId , $page = 0 , $pageSize = '15') {
    	$where = '1=1';
    	if( $sourceId ) {
    		$where .= " and source_id = '{$sourceId}'";
    	}
    	
    	$list = $this -> _db_news_choice -> where($where) -> limit($page*$pageSize) -> order('id desc') -> select();
    	return $list;
    }
    
    
    /**
     * 基于关键词的搜索结果
     *
     * @param string $keyword
     * @param int $begin_id
     * @param int $count
     * @return array
     */
    public function search( $keyword , $begin_id = 0 , $count = 10){
        $where_str = "title LIKE '%{$keyword}%'";
        if($begin_id)
            $where_str .= " AND id < '{$begin_id}'";
        
        if($count < 1)
            $count = 10;
            
        $list = $this -> _db_news_choice -> where($where_str) -> order("id DESC") -> limit($count) -> select();
        return $list;
    }
    
    /**
     * 获取单条评论信息
     *
     * @param int $comment_id
     * @return array
     */
    public function getComments($comment_id){
        return $this -> _db_news_comments -> where("id = '{$comment_id}'") -> find();
    }
    
    /**
     * 添加评论
     *
     * @param int $story_id
     * @param string $type
     * @param string $post
     * @param int $user_id
     * @param int $reply_id
     */
    public function comments($news_id , $post , $user_id , $reply_id = 0){
        $reply_id = (int)$reply_id;
        if($reply_id)
            $reply = $this -> getComments($reply_id);
        else 
            $reply_id = 0;
            
        $M_user = new UserModel();
        $user = $M_user -> getUserInfo($user_id);
        
        $data = array();
        $data['news_id'] = $news_id;
        $data['post'] = $post;
        $data['uid'] = $user_id;
        $data['username'] = $user['name'];
        $data['userphoto'] = $user['photo'];
        $data['reply_id'] = $reply_id;
        if($reply_id){
            $data['reply_uid'] = $reply['uid'];
            $data['reply_post'] = $reply['post'];
            $data['reply_username'] = $reply['username'];
            $data['reply_userphoto'] = $reply['userphoto'];
            
        }
        $data['add_time'] = date("Y-m-d H:i:s");
        
        $is_robot = '0';
        if( $user['is_robot'] == 'yes' ) {
        	$is_robot = '1';
        }
        
        $data['is_robot'] = $is_robot;
        $comment_id = $this -> _db_news_comments -> add($data);
        if($comment_id){
            $this -> _db_news_choice -> where("id = '{$news_id}'")->setInc('comments_count'); 
        }
        return $comment_id;
    }
    
    /**
     * 评论记录信息
     *
     * @param int $id
     * @return array
     */
    public function getCommentInfo($id){
        return $this -> _db_news_comments -> where("id = '{$id}'") -> find();
    }
    
    /**
     * 我参与的评论列表
     *
     */
    public function myCommentList($user_id , $since_id = 0 , $count = 10){
        
        if(!$count)
            $count = 10;
        $where_str = "(uid = '{$user_id}' OR reply_uid = '{$user_id}')";
        if($since_id)
            $where_str .= " AND id < '{$since_id}'";
        
        $list = $this -> _db_news_comments -> where($where_str) -> order("id DESC") -> limit($count) -> select();
        
        return $list;
    }
    
    /**
     * 评论列表
     *
     * @param int $story_id
     * @param string $type
     * @param int $since_id
     * @param int $count
     */
    public function commentsList($news_id , $since_id , $count = 10){
        if(!$count)
            $count = 10;
        $where_str = "news_id = '{$news_id}'";
        if($since_id)
            $where_str .= " AND id < '{$since_id}'";
        
        $list = $this -> _db_news_comments -> where($where_str) -> order("id DESC") -> limit($count) -> select();
        return $list;
    }
    
    /**
     * 热门评论
     *
     * @param int $news_id
     */
    public function commentsHotList($news_id , $count = 3){
        $where_str = "news_id = '{$news_id}' AND like_count > 0";
        
        $list = $this -> _db_news_comments -> where($where_str) -> order("like_count DESC , id DESC") -> limit($count) -> select();
        return $list;
    }
    
    /**
     * 删除评论
     *
     * @param unknown_type $comment_id
     * @param unknown_type $uid
     * @return unknown
     */
    public function commentsDel($comment_id , $uid){
    	$info = $this -> getComments($comment_id);
    	
        $where_str = "id = '{$comment_id}' AND uid = '{$uid}'";
        $res = $this -> _db_news_comments -> where($where_str) -> delete();
        
        if($res){
            $res = $this -> _db_news_choice -> where("id = '{$info['news_id']}'")->setDec('comments_count');
        }
        return $res;
    }
    
    /**
     * 添加喜欢
     *
     * @param int $news_id
     * @param int $uid
     */
    public function likeAdd($news_id , $uid){
        $data = array();
        $data['news_id'] = $news_id;
        $data['uid'] = $uid;
        $data['add_time'] = date('Y-m-d H:i:s');
        
        $res = $this -> _db_news_like -> add($data);
        if($res){
            $this -> _db_news_choice -> where("id = '{$news_id}'")->setInc('like_count'); 
        }
        return $res;
    }
    
    /**
     * 取消喜欢
     *
     * @param int $news_id
     * @param int $uid
     */
    public function likeDel($news_id , $uid){
        $data = "news_id = '{$news_id}' AND uid = '{$uid}'";
        $res = $this -> _db_news_like -> where($data) -> delete();
        if($res){
            $this -> _db_news_choice -> where("id = '{$news_id}'")->setDec('like_count'); 
        }
        return $res;
    }
    
    /**
     * 是否已点赞
     *
     * @param int $news_id
     * @param int $uid
     * @return bool
     */
    public function liked($news_id , $uid){
        $data = "news_id = '{$news_id}' AND uid = '{$uid}'";
        $row = $this -> _db_news_like -> where($data) -> find();
        if($row['id'])
            return true;
        else 
            return false;
        
    }
    
    /**
     * 添加收藏
     *
     * @param int $news_id
     * @param int $uid
     */
    public function favAdd($news_id , $uid){
        
        $res = $this -> _db_news_fav -> where("uid = '{$uid}' AND news_id = '{$news_id}'") -> find();
        if($res)
            return $res['id'];
        
        $data = array();
        $data['news_id'] = $news_id;
        $data['uid'] = $uid;
        $data['add_time'] = date('Y-m-d H:i:s');
        
        $res = $this -> _db_news_fav -> add($data);
        if($res){
            $this -> _db_news_choice -> where("id = '{$news_id}'")->setInc('fav_count'); 
        }
        return $res;
    }
    
    /**
     * 取消收藏
     *
     * @param int $news_id
     * @param int $uid
     */
    public function favDel($news_id , $uid){
        $data = "news_id = '{$news_id}' AND uid = '{$uid}'";
        $res = $this -> _db_news_fav -> where($data) -> delete();
        if($res){
            $this -> _db_news_choice -> where("id = '{$news_id}'")->setDec('fav_count'); 
        }
        return $res;
    }
    
    /**
     * 是否已收藏
     *
     * @param int $news_id
     * @param int $uid
     * @return bool
     */
    public function faved($news_id , $uid){
        $data = "news_id = '{$news_id}' AND uid = '{$uid}'";
        $row = $this -> _db_news_fav -> where($data) -> find();
        if($row['id'])
            return true;
        else 
            return false;
        
    }
    
    /**
     * 用户收藏列表
     *
     * @param unknown_type $uid
     */
    public function favList($uid , $since_id = 0 , $count = 10){
        $data = "uid = '{$uid}'";
        if($since_id){
            $data .= " AND id < '{$since_id}'";
        }
        $list = $this -> _db_news_fav -> where($data) -> order("id DESC") -> limit($count) -> select();
        
        return $list;
    }
    
    /**
     * 订阅
     *
     * @param int $news_id
     * @param int $uid
     */
    public function followAdd($source_id , $uid){
        $data = array();
        $data['source_id'] = $source_id;
        $data['uid'] = $uid;
        $data['add_time'] = date('Y-m-d H:i:s');
        
        $res = $this -> _db_news_follow -> add($data);
        if($res){
            $this -> _db_news_source -> where("id = '{$source_id}'")->setInc('fans'); 
        }
        return $res;
    }
    
    /**
     * 取消订阅
     *
     * @param int $news_id
     * @param int $uid
     */
    public function followDel($source_id , $uid){
        $data = "source_id = '{$source_id}' AND uid = '{$uid}'";
        $res = $this -> _db_news_follow -> where($data) -> delete();
        if($res){
            $this -> _db_news_source -> where("id = '{$source_id}'")->setDec('fans'); 
        }
        return $res;
    }
    
    /**
     * 是否已订阅
     *
     * @param int $source_id
     * @param int $uid
     * @return bool
     */
    public function followed($source_id , $uid){
        $data = "source_id = '{$source_id}' AND uid = '{$uid}'";
        $row = $this -> _db_news_follow -> where($data) -> find();
        
        if($row['id'])
            return true;
        else 
            return false;
    }
    
    /**
     * 订阅列表
     *
     * @param int $uid
     * @return array
     */
    public function followList($uid){
        $data = "uid = '{$uid}'";
        $list = $this -> _db_news_follow -> where($data) -> order("initial ASC , pinyin ASC") -> select();
        return $list;
    }
    
    /**
     * 获取内容源基本信息
     *
     * @param int $source_id
     * @return mixed
     */
    public function getSource($source_id){
        return $this -> _db_news_source -> where("id = '{$source_id}'") -> find();
    }
    
    /**
     * 获取所有内容源
     *
     * @return mixed
     */
    public function getSoureList(){
        return $this -> _db_news_source -> order("id ASC") -> select();
    }
    
    /**
     * 获取所有分类
     *
     * @return array
     */
    public function getCateList(){
        return $this -> _db_news_cate -> select();
    }
    
    /**
     * 点赞
     *
     * @param int $comment_id
     * @param int $user_id
     */
    public function commentLike($comment_id , $user_id){
        $data = array();
        $data['comment_id'] = $comment_id;
        $data['uid'] = $user_id;
        $data['add_time'] = date('Y-m-d H:i:s');
        
        $res = $this -> _db_news_comments_like -> add($data);
        if($res){
            $this -> _db_news_comments -> where("id = '{$comment_id}'")->setInc('like_count'); 
        }
        return $res;
    }
    
    /**
     * 取消点赞
     *
     * @param int $comment_id
     * @param int $user_id
     */
    public function commentUnlike($comment_id , $user_id){
        $data = array();
        $data['comment_id'] = $comment_id;
        $data['uid'] = $user_id;
        $data['add_time'] = date('Y-m-d H:i:s');
        
        $res = $this -> _db_news_comments_like -> where("comment_id = '{$comment_id}' AND uid = '{$user_id}'") -> delete();
        if($res){
            $this -> _db_news_comments -> where("id = '{$comment_id}'")->setDec('like_count'); 
        }
        return $res;
    }
    
    /**
     * 是否已点赞
     *
     * @param unknown_type $comment_ids
     * @param unknown_type $user_id
     */
    public function commentLiked($comment_ids , $user_id){
        $result = array();
        $comment_arr = explode(',' , $comment_ids);
        foreach ($comment_arr as $id){
            $result[$id] = 'no';
        }
        
        if(!$comment_ids)
            return array();
        
        //未登录用户直接返回
        if(!$user_id)
            return $result;

        //登录用户在数据库中查询
        $where_str = "comment_id IN ({$comment_ids}) AND uid = '{$user_id}'";
        $list = $this -> _db_news_comments_like -> where($where_str) -> select();
        
        foreach ($list as $row){
            if(in_array($row['comment_id'] , $comment_arr)){
                $result[$row['comment_id']] = 'yes';
            }
        }
        return $result;
    }
    
    
    //更新story行记录
    public function updateStory($id , $data){
        return $this -> _db_news_choice -> where("story_id = '{$id}'") -> save($data);
    }
    
    /**
     * 通过id删除newschoice
     * @author nj 2015-7-31
     * @param int $id 
     * @param $res
     */
    public function deleteNewsById($id){
    	
    	$res = $this -> _db_news_choice -> where("id = '{$id}'") -> delete();
    	
    	if( $res ) {
    		$this -> _db_news_comments -> where("news_id='{$id}'") -> delete();	
    	}
    	
    	return $res;

    }
    
    /**
     * 通过新闻id删除评论
     * @param unknown_type $news_id
     */
    public function deleteCommnetByNewsId($news_id){
    	return $this -> _db_news_comments -> where("news_id='{$news_id}'") -> delete();
    }
    /**
     * 通过is_auto查询来源
     * @param smallint $isAuto 是否自动抓取
     */
    public function getSoureListByIsAuto($isAuto){
    	return $this -> _db_news_source -> where("is_auto='{$isAuto}'") -> select();
    }
    
    /**
     * 查询所有评论
     * @param is_robot 是否机器人
     * @param unknown_type $page
     * @param unknown_type $page_count
     */
    public function getAllCommnets($is_robot,$page,$page_count,$news_id,$uid){
    	$where = '1=1';
    	$start = ($page-1)*$page_count;
    	$limit = " limit $start,$page_count";

    	if( $is_robot != 'all' ) {
    		$where .= " and is_robot='{$is_robot}'";
    	}

    	if( $news_id ) {
    		$where .= " and news_id = '{$news_id}'";
    	}
    	if( $uid ) {
    		$where .= " and uid = '{$uid}'";
    	}
   	
    	$count = $this -> _db_news_comments -> where($where) -> count();
    	$res = $this -> _db_news_comments -> where($where) -> limit($start,$page_count) -> order('id desc') -> select();
    	
    	$sql = "SELECT id,rp.c FROM `ad_news_comments` a right join (SELECT reply_id,count(1) as c FROM `ad_news_comments` WHERE reply_id != '' and $where group by reply_id $limit)  as rp On rp.reply_id = a.id";
		$replyCount = $this -> _db_news_comments -> query($sql);
		$replyCountArr = array();
		if( $replyCount ) {
			foreach ( $replyCount as $v ) {
				$replyCountArr[$v['id']] = $v['c'];
			}
		}
		
    	return array('count'=>$count,'res'=>$res,'replyCount'=>$replyCountArr);
    }
    
    /**
     * 通过news_id查询评论
     * @param unknown_type $new_id
     */
    public function getCommentByNewsId($new_id) {
    	return $this -> _db_news_comments -> where("news_id='{$new_id}'") -> order('id asc') -> find();
    }
    
    /**
     * 分页查询news_choice
     * @param unknown_type $page
     * @param unknown_type $pageSize
     * @return multitype:unknown
     */
    public function getLimitNews( $page = 0, $pageSize = 20 ){
    	$news = $this -> _db_news_choice -> field('id,story_id') -> limit($page*$pageSize,$pageSize) -> order('id desc') -> select();
    	$page++;
    	return array('news'=>$news,'page'=>$page);
    }
    
    /**
     * news_keyword插入数据
     */
    public function insertKeywordToKeyword($data){
    	return $this -> _db_news_keyword -> add($data);
    }
    
	/**
	 * 通过news_id删除关键词
	 * @param unknown_type $keyword
	 */
    public function deleteKeywordInfoByNewsId($news_id){
    	return $this -> _db_news_keyword -> where("news_id='{$news_id}'") -> delete();
    }
    
    /**
     * 入库关键词
     */
    public function addKeywordsToNewsKeyword($news,$keywords){
    	$num = 0;	
    	$message = '';
    	$M_story = new StoryModel();
    	
    	
    	//删除此新闻的所有关键词
    	$this -> deleteKeywordInfoByNewsId($news['id']);
    	
    	//查出文章内容
    	$content = $M_story -> getContentByStoryId($news['story_id']);
    	$story_content = strip_tags($content['content']);
    	
    	foreach ( $keywords as $k=>$v ) {

    		//匹配关键词
    		$preg_res = strpos($story_content, $v['keyword']);
    		
    		if( $preg_res !== false ) {

    			//剔除已匹配到的关键词 （防止当长关键词里包含短关键词关键词重复问题）
    			$story_content = str_replace($v['keyword'], '', $story_content);
    	
    			$data['news_id'] = $news['id'];
    			$data['keyword'] = $v['keyword'];
    			$data['type'] = $v['type'];
    			$data['pid'] = $v['pid'];
    			
    			//关键词入库
    			$ins_res = $this -> insertKeywordToKeyword($data);
    			if( $ins_res ) {
    				$num++;
    			}else{
    				$message .= '新闻' . $val['id'] . '关键词' . $v['keyword'] . '入库失败' . '<p>'; 
    			}
    		}
    			 
    	}
    		
    	return array('num'=>$num,'msg'=>$message);
    }

    /**
     * 获取新闻关键词
     *
     * @param int $news_id
     * @return mixed
     */
    public function newsKeyword($news_id){
        return $this -> _db_news_keyword -> where("news_id = '{$news_id}'") -> select();
    }

}
