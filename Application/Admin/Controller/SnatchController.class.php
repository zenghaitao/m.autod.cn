<?php
namespace Admin\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;
use Admin\Model\SnatchModel;

set_time_limit(0);

class SnatchController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
    }
    
    /**
     * 自动抓取程序
     *
     */
    public function spider(){
        $_db_news_source = M('news_source' , 'ad_' , 'DB0_CONFIG');
        $_db_news_story = M('news_story' , 'ad_' , 'DB0_CONFIG');
        $_db_news_spider_page = M('news_spider_page' , 'ad_' , 'DB0_CONFIG');
        
        $sources = $_db_news_source -> where("media_id != 0") -> select();
        foreach ($sources as $source){
            $url = "http://toutiao.com/m{$source['media_id']}/";
            
            /* 获取最后更新的记录 */
            $end_row = $_db_news_story -> where("source_id = '{$source['id']}'") -> order("article_id DESC") -> limit(1) -> find();
            $max_id = $end_row['article_id'];
            
            $spider_end_row = $_db_news_spider_page -> where("source_id = '{$source['id']}'") -> order("article_id DESC") -> limit(1) -> find();
            $spider_max_id = $spider_end_row['article_id'];
            
            if($spider_max_id > $max_id)
                $max_id = $spider_max_id;
                
            /* 获取媒体新闻列表页码 */
            $M_snatch = new SnatchModel($url);
            $pages = $M_snatch -> toutiaoPageList();
            unset($M_snatch);
            
            $array = array();
            $_find = 0;
            //本次需采集的页面列表
            foreach ($pages as $key => $page){
                if($_find){
                    continue;
                }
                
                $M_snatch = new SnatchModel($page);
                $news_list = $M_snatch -> toutiaoPage();
                
                foreach ($news_list as $news){
                    if($news['article_id'] > $max_id){
                        $array[] = $news;
                    }else {
                        $_find ++;
                    }
                }
            }
            
            /* 存入待处理列表 */
            foreach ($array as $row){
                
                $row['plant'] = 'toutiao';
                $row['source'] = $source['name'];
                $row['source_id'] = $source['id'];
                $row['column_id'] = '0';
                if($row['title_pic3'])
                    $row['img_count'] = 3;
                elseif ($row['title_pic1'])
                    $row['img_count'] = 1;
                else 
                    $row['img_count'] = 0;
                    
                $row['add_date'] = date('Y-m-d H:i:s');
                
                $one = $_db_news_spider_page -> where("article_id = '{$row['article_id']}'") -> find();
                if(!$one)
                    $_db_news_spider_page -> add($row);
            }
            
            echo ($source['name'].':'.count($array)."\n");
            
        }
    }
    
    /**
     * 处理页面数据
     *
     */
    public function page(){
        $_db_news_choice = M('news_choice' , 'ad_' , 'DB0_CONFIG');
        $_db_news_source = M('news_source' , 'ad_' , 'DB0_CONFIG');
        $_db_news_story = M('news_story' , 'ad_' , 'DB0_CONFIG');
        $_db_news_story_content = M('news_story_content' , 'ad_' , 'DB0_CONFIG');
        $_db_news_spider_page = M('news_spider_page' , 'ad_' , 'DB0_CONFIG');
        
        $i = 0;
        $prv_source_id = 0;
        /* 获取要处理的页面 */
        $list = $_db_news_spider_page -> where("is_snatch = 'no' AND img_count > 0") -> order("story_date ASC , article_id ASC") -> limit(100) -> select();
        foreach ($list as $row){
            if($i >= 10)
                continue;
            
            $M_snatch = new SnatchModel($row['url']);
            //$M_snatch = new SnatchModel('http://toutiao.com/a4639192103/');
            $result = $M_snatch -> toutiaoContent();
            
            //更新spider行记录状态
            $_db_news_spider_page -> where("id = '{$row['id']}'") -> save(array('is_snatch'=>'yes'));
            
            if($result['content']){
                //移入story表
                $data = $row;
                unset($data['id']);
                $data['add_date'] = date('Y-m-d H:i:s');
                $story_id = $_db_news_story -> add($data);
                
                //填充content数据
                if($story_id){
                    $data = array();
                    $data['story_id'] = $story_id;
                    $data['article_id'] = $row['article_id'];
                    $data['page'] = 1;
                    $data['content'] = $result['content'];
                    $data['images'] = implode(';,;' , $result['images']);
                    $data['images'] = count($result['images']);
                    $data['http'] = $result['http'];
                    $data['add_time'] = date('Y-m-d H:i:s');
                    $_db_news_story_content -> add($data);
                    
                    //自动推荐到前台列表中
                    if($prv_source_id != $row['source_id']){
                        $data = array();                        
                        $data['story_id'] = $story_id;
                        $data['cate_id'] = $row['column_id'];
                        $data['title'] = $row['title'];
                        $data['summary'] = $row['short_summary'];
                        $data['source'] = $row['source'];
                        $data['source_id'] = $row['source_id'];
                        if($row['title_pic3'])
                            $data['images'] = $row['title_pic1'].';,;'.$row['title_pic2'].';,;'.$row['title_pic3'];
                        else 
                            $data['images'] = $row['title_pic1'];

                        $data['story_date'] = $row['story_date'];
                        if($row['plant'] == 'UUTV')
                            $data['open_mode'] = 'video';
                        else 
                            $data['open_mode'] = 'news';

                        $data['hot'] = rand(321,1999);
                        $data['day'] = date('Y-m-d');
                        $data['add_time'] = date('Y-m-d H:i:s');
                        $news_id = $_db_news_choice -> add($data);
                        
                        $_db_news_story -> where("id = '{$story_id}'") -> save(array('is_choice'=>'yes'));
                        
                        $prv_source_id = $row['source_id'];
                    }
                    $i++;
                }
            }
        }
        
        var_dump("Update:".$i);
        
    }
    
    
}