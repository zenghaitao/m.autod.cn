<?php
namespace API\Model;

class StoryModel
{

    protected $_db_story;
    protected $_db_story_content;
    protected $_db_video;
    
    public function __construct(){
        $this -> _db_story = M('news_story' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_story_content = M('news_story_content' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_video = M('ina_vedio' , 'cms_' , 'DB0_CONFIG');
        $this -> _db_news_spider_page = M('news_spider_page' , 'ad_' , 'DB0_CONFIG');
    }
    
    /**
     * 获取新闻信息
     *
     * @param int $story_id
     * @return array
     */
    public function getStoryInfo($story_id , $is_choice = 'yes'){
        $where = "id = '{$story_id}'";
        if($is_choice == 'yes'){
            $where .= " AND is_choice = '{$is_choice}'";
        }
        $info = $this -> _db_story -> where($where) -> find();
        $count = $this -> _db_story_content -> where("story_id = '$story_id'") -> count();
        $info['page_num'] = $count;
        
        return $info;
    }
    
    /**
     * 获取新闻单页内容
     *
     * @param int $story_id
     * @param int $page
     * @return array
     */
    public function getStoryPage($story_id){
        $pages = $this -> _db_story_content -> where("story_id = '$story_id'") -> order("page ASC") -> select();
        $html = '';
        $images = '';
        $image_count = 0;
        foreach ($pages as $row){
            $html .= strip_tags($row['content'] , "<p><img><table><tr><td><tbody><video>");
            $images .= $row['images'];
            $image_count += $row['image_count'];
        }
        if($images == ""){
            $images = array();
        }else{
            $images = explode(';,;' , $images);
        }
        return array('html' => $html , 'images' => $images , 'image_count' => $image_count);
    }
    
    /**
     * 获取视频详细内容
     *
     * @param int $video_id
     * @return array
     */
    public function getVideo($video_id){
        $info = $this -> _db_video -> where("id = '{$video_id}'") -> find();
        return $info;
    }
    
    
    public function initNews(){
        $list = $this -> _db_story -> limit(90) -> select();
        return $list;
    }
    
    public function initVideo(){
        $list = $this -> _db_video -> where("`platform` = 'youku' AND `is_private` = 'no' AND `status` = 'published'") -> limit(10) -> select();
        return $list;
    }
    
    public function storyAdminList( $is_choice = 'no', $page = 1 , $count = 10){
        $where_str = "is_choice = '{$is_choice}'";
        $limit = ($page - 1)*$count . ',' . $count;
        
        $count = $this -> _db_story -> where($where_str) -> count();
        $list = $this -> _db_story -> where($where_str) -> order('story_date DESC') -> limit($limit) -> select();
        return array('list'=>$list , 'count'=> $count);
        
    }
    
    public function noCateNewsList($page = 1 , $count = 10){
        $where_str = "column_id = '0'";
        $limit = ($page - 1)*$count . ',' . $count;
        
        $count = $this -> _db_story -> where($where_str) -> count();
        $list = $this -> _db_story -> where($where_str) -> order('is_choice ASC , id DESC') -> limit($limit) -> select();
        
        return array('list'=>$list , 'count'=> $count);
    }
    
    //更新story行记录
    public function updateStory($id , $data){
        return $this -> _db_story -> where("id = '{$id}'") -> save($data);
    }
    
    /**
     * news_story 插入数据
     * @param string $article_id 文章id
     */
    public function addStory($data){
    	$urlArr = parse_url($data['url']);
    	if( $urlArr['host'] == '3g.163.com' ) {
    		$data['plant'] = 'ntes';
    	}
    	if($data['plant'] != 'ntes'){
    	   $data['article_int_id'] = $data['article_id'];
    	}
    	$data['add_date'] = date('Y-m-d H:i:s');
    	$data['is_choice'] = 'no';
    	$data['img_count'] = substr_count($data['images'],';,;');

    	return $this -> _db_story -> add($data);
    }
    
    /**
     * news_story_content 插入数据
     * @param unknown_type $story_id 
     */
    public function addStoryContent($data){
    	$data['page'] = '1';
    	$data['image_count'] = substr_count($data['images'],';,;');
    	$data['add_time'] = date('Y-m-d H:i:s');
    	 
    	return $this -> _db_story_content -> add($data);
    }
    
    /**
     * spider_page 插入数据
     * @param array $data
     */
    public function addSpiderPage($data){
    	$urlArr = parse_url($data['url']);
    	if( $urlArr['host'] == '3g.163.com' ) {
    		$data['plant'] = 'ntes';
    	}
    	$data['img_count'] = substr_count($data['images'],';,;');
    	$data['add_date'] = date('Y-m-d H:i:s');
    	$data['is_snatch'] = 'yes';
    	
    	return $this -> _db_news_spider_page -> add($data);
    }
    
    /**
     * news_story 通过文章id查信息
     * @param int $article_id 
     * @return arr $info 
     */
    public function getInfoByArticleId($article_id){
    	return $this -> _db_story -> where("article_id='{$article_id}'") -> find();
    }
    
    /**
     * 通过story_id查询story_content里的content
     * @param unknown_type $story_id
     */
  	public function getContentByStoryId($story_id){
  		return $this -> _db_story_content -> field('content') -> where("story_id='{$story_id}'") -> find();
  	}
    
}
