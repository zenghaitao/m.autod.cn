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
    }
    
    /**
     * 获取新闻信息
     *
     * @param int $story_id
     * @return array
     */
    public function getStoryInfo($story_id){
        $info = $this -> _db_story -> where("id = '{$story_id}' AND is_choice = 'yes'") -> find();
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
        foreach ($pages as $row){
            $html .= strip_tags($row['content'] , "<p><img>");;
        }
        return $html;
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
    
    public function storyAdminList( $is_choice = 'no', $count = 10){
        $where_str = "is_choice = '{}'";
    }
}
