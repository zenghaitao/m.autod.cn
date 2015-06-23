<?php
namespace API\Model;
use API\Model\StoryModel;

class NewsModel
{

    protected $_db_news_choice;
    
    public function __construct(){
        $this -> _db_news_choice = M('news_choice' , 'ad_' , 'DB0_CONFIG');
    }
    
    /**
     * 获取当天新闻池
     *
     */
    public function newsPool($use_ids = array()){
        $list = $this -> _db_news_choice -> order('id DESC') -> limit(100) -> select();
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
     * 将指定文章添加到推荐
     *
     * @param int $story_id
     * @return mixed
     */
    public function addToChoice($story_id){
        
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
}
