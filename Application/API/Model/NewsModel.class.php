<?php
namespace API\Model;

class NewsModel
{

    protected $_db_news_choice;
    protected $_db_news_comments;
    protected $_db_news_like;
    protected $_db_news_fav;
    protected $_db_news_follow;
    protected $_db_news_source;
    
    public function __construct(){
        $this -> _db_news_choice = M('news_choice' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_comments = M('news_comments' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_like = M('news_like' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_fav = M('news_fav' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_follow = M('news_follow' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_news_source = M('news_source' , 'ad_' , 'DB0_CONFIG');
    }
    
    /**
     * 获取当天新闻池
     *
     */
    public function newsPool($use_ids = array()){
        $list = $this -> _db_news_choice -> order('id DESC') -> limit(100) ->order("rand()") -> select();
        $res = array();
        $i = 0;
        foreach ($list as $row){
            if(!in_array($row['story_id'] , $use_ids)){
                $i ++;
                $res[] = $row;
                if($i >= 10 )
                    break;
            }
        }
        return $res;
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
     * 将指定文章添加到推荐
     *
     * @param int $story_id
     * @return mixed
     */
    public function addNewsToChoice($story_id){
        
        $M_story = new StoryModel();
        
        $info = $M_story -> getStoryInfo($story_id);
        
        //var_dump($info['title_pic1']);
        
        $data = array();
        $data['story_id'] = $story_id;
        $data['cate_id'] = $this -> convCateId($info['columnid']);
        $data['title'] = $info['title'];
        $data['summary'] = $info['shortsummary'];
        $data['source'] = $info['source'];
        $data['source_id'] = $info['sourceid']?$info['sourceid']:0;
        $data['images'] = $this -> makeImages($info['title_pic1'] , $info['title_pic2'] , $info['title_pic3']);
        $data['story_date'] = $info['storyDate']?$info['storyDate']:date("Y-m-d H:i:s");
        $data['type'] = $this -> convNewsType($info['is_top'] , $info['is_hot'] , $info['is_rec']);
        $data['fav_count'] = '0';
        $data['like_count'] = '0';
        $data['comments_count'] = '0';
        $data['day'] = date('Y-m-d');
        $data['add_time'] = date("Y-m-d H:i:s");
        $data['open_mode'] = "news";
        
        if(!$data['images'])
            return 'no pic';
        
        return $this -> _db_news_choice -> add($data);
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
    public function newsCateList($cate_id , $begin_id = 0 , $count = 10){
        $where_str = "cate_id = '{$cate_id}'";
        if($begin_id)
            $where_str .= " AND id < '{$begin_id}'";
        
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
        if($reply_id)
            $reply = $this -> getComments($reply_id);
        
        $M_user = new UserModel();
        $user = $M_user -> getUserInfo($user_id);
        
        $data = array();
        $data['news_id'] = $news_id;
        $data['post'] = $post;
        $data['uid'] = $user_id;
        $data['username'] = $user['name'];
        $data['userphoto'] = $user['photo'];
        $data['reply_id'] = $reply_id;
        $data['reply_uid'] = $reply['uid'];
        $data['reply_post'] = $reply['post'];
        $data['reply_username'] = $reply['username'];
        $data['reply_userphoto'] = $reply['userphoto'];
        $data['add_time'] = date("Y-m-d H:i:s");
        
        $comment_id = $this -> _db_news_comments -> add($data);
        if($comment_id){
            $this -> _db_news_choice -> where("id = '{$news_id}'")->setInc('comments_count'); 
        }
        return $comment_id;
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
        $where_str = "news_id = '{$news_id}'";
        if($since_id)
            $where_str .= " AND id < '{$since_id}'";
        
        $list = $this -> _db_news_comments -> where($where_str) -> order("id DESC") -> limit($count) -> select();
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
        $where_str = "id = '{$comment_id}' AND uid = '{$uid}'";
        $res = $this -> _db_news_comments -> where($where_str) -> delete();
        if($res){
            $info = $this -> getComments($comment_id);
            $this -> _db_news_choice -> where("id = '{$info['news_id']}'")->setDec('comments_count'); 
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
     * 添加收藏
     *
     * @param int $news_id
     * @param int $uid
     */
    public function favAdd($news_id , $uid){
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
        $data = "source_id = '{$news_id}' AND uid = '{$uid}'";
        $res = $this -> _db_news_follow -> where($data) -> delete();
        if($res){
            $this -> _db_news_source -> where("id = '{$source_id}'")->setDec('fans'); 
        }
        return $res;
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
        return $this -> _db_news_source -> order("pinyin ASC") -> select();
    }
}
