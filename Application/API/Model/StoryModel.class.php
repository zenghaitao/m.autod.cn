<?php
namespace API\Model;

class StoryModel
{

    protected $_db_story;
    protected $_db_story_content;
    
    public function __construct(){
        $this -> _db_story = M('autod_story' , 'cms_' , 'DB0_CONFIG');
        $this -> _db_story_content = M('autod_story_content' , 'cms_' , 'DB0_CONFIG');
    }
    
    /**
     * 获取新闻信息
     *
     * @param int $story_id
     * @return array
     */
    public function getStoryInfo($story_id){
        $info = $this -> _db_story -> where("id = '{$story_id}' AND status = 'published'") -> find();
        $count = $this -> _db_story_content -> where("storyId = '$story_id'") -> count();
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
    public function getStoryPage($story_id , $page = 0){
        $info = $this -> _db_story_content -> where("storyId = '$story_id' AND page = '{$page}'") -> find();
        return $info;
    }
    
    public function initNews(){
        $list = $this -> _db_story -> select();
        return $list;
    }
}
