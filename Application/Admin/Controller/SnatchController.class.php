<?php
namespace Admin\Controller;
use API\Model\NewsModel;
use API\Model\StoryModel;
use Admin\Model\SnatchModel;

set_time_limit(0);
ini_set('memory_limit', '1024M');

class SnatchController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
    }
    
    public function init(){
        /* 清除 news_choice */
        $_db_table = M('news_choice' , 'ad_' , 'DB0_CONFIG');
        $_db_table -> where('1') -> delete();
        
        /* 清除 news_comments */
        $_db_table = M('news_comments' , 'ad_' , 'DB0_CONFIG');
        $_db_table -> where('1') -> delete();
        
        /* 清除 news_comments_like */
        $_db_table = M('news_comments_like' , 'ad_' , 'DB0_CONFIG');
        $_db_table -> where('1') -> delete();
        
        /* 清除 news_fav */
        $_db_table = M('news_fav' , 'ad_' , 'DB0_CONFIG');
        $_db_table -> where('1') -> delete();
        
        /* 清除 news_like */
        $_db_table = M('news_like' , 'ad_' , 'DB0_CONFIG');
        $_db_table -> where('1') -> delete();
        
        /* 清除 user */
        $_db_table = M('user' , 'ad_' , 'DB0_CONFIG');
        $_db_table -> where('1') -> delete();
        
        /* 清除 user_device */
        $_db_table = M('user_device' , 'ad_' , 'DB0_CONFIG');
        $_db_table -> where('1') -> delete();
        
        /* 清除 user_feedback */
        $_db_table = M('user_feedback' , 'ad_' , 'DB0_CONFIG');
        $_db_table -> where('1') -> delete();
        
        /* 初始化 ina 新闻数据 */
        $_db_news_story = M('news_story' , 'ad_' , 'DB0_CONFIG');
        $_db_news_story_content = M('news_story_content' , 'ad_' , 'DB0_CONFIG');
        $_db_cms_story = M('autod_story' , 'cms_' , 'DB_CMS_CONFIG');
        $_db_cms_story_content = M('autod_story_content' , 'cms_' , 'DB_CMS_CONFIG');
        $_db_cms_video = M('ina_vedio' , 'cms_' , 'DB_INA_CONFIG');
        
        /* 删除网通社新闻内容 */
        $_db_news_story -> where("plant = 'ina'") -> delete();
        $_db_news_story -> where("plant = 'uutv'") -> delete();
        
        /* 网通社新闻部分 */
        $max_id = $_db_news_story -> where("plant = 'ina'") ->order("article_id DESC") -> find();
        $max_id = (int)$max_id['article_id'];
        
        $M_snatch = new SnatchModel();
        
        $list = $_db_cms_story -> where("sourceId='1' AND status = 'published' AND storyDate > '2015' AND id > '{$max_id}'") -> order("storyDate ASC") -> select();
        foreach ($list as $row){
            /* 写入news_story表 */
            $data = array();
            $data['article_id'] = $row['id'];
            $data['plant'] = 'ina';
            $data['title'] = $row['title'];
            $data['short_summary'] = $row['shortSummary'];
            $data['source'] = $row['source'];
            $data['source_id'] = '49';
            $data['story_date'] = $row['storyDate']?$row['storyDate']:'2015-01-01';
            $data['column_id'] = $this -> column($row['columnId']);
            if($row['title_pic3']){
                $data['img_count'] = 3;
            }elseif ($row['title_pic1']){
                $data['img_count'] = 1;
            }else {
                $data['img_count'] = 0;
            }
            $data['title_pic1'] = $row['title_pic1'];
            $data['title_pic2'] = $row['title_pic2'];
            $data['title_pic3'] = $row['title_pic3'];
            
            $data['url'] = $row['url'];
            $data['add_date'] = date('Y-m-d H:i:s');
            
            //文章信息入库
            $story_id = $_db_news_story -> add($data);
            
            if($story_id){
                //获取文章正文
                $contents = $_db_cms_story_content -> where("storyId = '{$row['id']}'") -> order("page ASC") -> select();
                $content = '';
                foreach ($contents as $val){
                    $content .= $val['content'];
                }

                /* 写入news_story_content表 */
                $images = $M_snatch -> img($content);
                $content = strip_tags(trim($content) , '<p><img><div><table><tr><td>');
                
                $images_str = implode(';,;' , $images);
                $images_count = count($images);
                
                $data = array();
                $data['story_id'] = $story_id;
                $data['article_id'] = $row['id'];
                $data['page'] = '1';
                $data['content'] = $content;
                $data['images'] = $images_str;
                $data['image_count'] = $images_count;
                $data['http'] = '3306';
                $data['add_time'] = date('Y-m-d H:i:s');
                
                //文章正文入库
                $ins_id = $_db_news_story_content -> add($data);
                
                $i++;
            }
        }
        echo ("INA NEWS:{$i}\n");
        
        /* 初始化 uutv 视频数据 */
        //视频类内容入库
        $max_id = $_db_news_story -> where("plant = 'uutv'") ->order("article_id DESC") -> find();
        $max_id = (int)$max_id['article_id'];
        
        $i = 0;
        
        //查找未入库内容
        $list = $_db_cms_video -> where("id > '{$max_id}' AND platform = 'youku' AND status = 'published'") -> order("id ASC") -> select();
        foreach ($list as $row){
            $data = array();
            $data['article_id'] = $row['id'];
            $data['plant'] = 'uutv';
            $data['title'] = $row['title'];
            $data['short_summary'] = '';
            $data['source'] = 'UUTV';
            $data['source_id'] = '48';
            $data['story_date'] = $row['publish_time']?$row['publish_time']:'2015-01-01';;
            $data['column_id'] = '20';
            $data['title_pic1'] = $row['img'];
            $data['img_count'] = 0;
            
            $data['url'] = $row['url'];
            $data['add_date'] = date('Y-m-d H:i:s');
            
            //文章信息入库
            $story_id = $_db_news_story -> add($data);
            
            if($story_id)
                $i++;
        }
        echo ("UUTV VIDEO:{$i}\n");
        
        /* 初始化story表choice状态 */
        $_db_news_story -> where('1') -> save(array('is_choice'=>'no'));
        
        /* 初始化chioce数据 */
        $_db_news_source = M('news_source' , 'ad_' , 'DB0_CONFIG');
        
        $i = 0;
        
        /* 获取要处理的新闻 */
        $today = date('Y-m-d');
        $list = $_db_news_story -> where("is_choice = 'no' AND img_count > 0") -> order("story_date ASC , article_id ASC") -> select();
        foreach ($list as $row){
            //写入前台列表
            $news_id = $this -> intoChoice($row);
            $i++;
        }
        echo ("CHOICE NEWS:{$i}\n");
    }
    
    public function zhengli_content(){
        $_db_news_story_content = M('news_story_content' , 'ad_' , 'DB0_CONFIG');
        
        $M_snatch = new SnatchModel();
        $i = 0;
        
        $list = $_db_news_story_content -> where("images is null") -> select();
        foreach ($list as $row){
            $images = $M_snatch -> img($row['content']);
            $data = array();
            $data['image_count'] = count($images);
            $data['images'] = implode(';,;' , $images);
            if($data['image_count']){
                var_dump($_db_news_story_content -> where("id = '{$row['id']}'") -> save($data));
                $i++;
            }
        }
        
        var_dump($i.'over');
    }
    
    public function syncIna(){
        $url = 'http://api.news18a.com/auto/data/ina_news_dujia_list.js';
        $json = json_decode(file_get_contents($url),1);
        
        $_db_cms_story = M('autod_story' , 'cms_' , 'DB0_CONFIG');
        $_db_cms_story_content = M('autod_story_content' , 'cms_' , 'DB0_CONFIG');
        $_db_cms_video = M('ina_vedio' , 'cms_' , 'DB0_CONFIG');
        
        foreach ($json as $row){
            var_dump($row);exit;
            
            $data = array();
            $data['title'] = $row['title'];
            $data['shortTitle'] = $row['shorttitle'];
            $data['shortSummary'] = $row['short_summary'];
            $data['author'] = $row['writer'];
            $data['source'] = $row['source'];
            $data['sourceId'] = '1';
            $data['storyDate'] = $row['story_date'];
            $data['keyWord'] = $row['keyword'];
            $data['columnId'] = 0;
            $data['logoId'] = $row['logo_id'];
            $data['bseriesId'] = $row['series_id'];
            $data['title_pic1'] = $row['pic1'];
            if($row['pic2']){
                $data['title_pic1'] = $row['pic1'];
            }
            $data['title_pic2'] = '';
            $data['title_pic3'] = '';
            
            $data['position'] = 0;
            $data['url'] = $row['url'];
            $data['status'] = 'published';
            $data['modiDate'] = date('Y-m-d H:i:s');
            $data['addDate'] = date('Y-m-d H:i:s');
            $data['is_top'] = 'no';
            $data['is_hot'] = 'no';
            $data['is_rec'] = 'no';
            $data['is_img_pick'] = 'no';
            $data['weight'] = 0;
            
            //文章信息入库
            $story_id = $_db_cms_story -> add($data);
            
        }
        
        var_dump($json);exit;
    }
    
    /**
     * 抓取INA的数据进story表
     *
     */
    public function ina(){
        $_db_news_source = M('news_source' , 'ad_' , 'DB0_CONFIG');
        $_db_news_story = M('news_story' , 'ad_' , 'DB0_CONFIG');
        $_db_news_story_content = M('news_story_content' , 'ad_' , 'DB0_CONFIG');
        
        $_db_cms_story = M('autod_story' , 'cms_' , 'DB_CMS_CONFIG');
        $_db_cms_story_content = M('autod_story_content' , 'cms_' , 'DB_CMS_CONFIG');
        
        $_db_cms_video = M('ina_vedio' , 'cms_' , 'DB_CMS_CONFIG');
        
        $M_snatch = new SnatchModel('');
        
        /* 网通社新闻部分 */
        $max_id = $_db_news_story -> where("plant = 'ina'") ->order("article_id DESC") -> find();
        $max_id = $max_id['article_id'];
        
        $i = 0;
        
        //查找未入库内容
        $list = $_db_cms_story -> where("id > '{$max_id}' AND sourceId = '1' AND status = 'published'") -> order("id ASC") -> limit(100) -> select();
        foreach ($list as $row){
            $data = array();
            $data['article_id'] = $row['id'];
            $data['plant'] = 'ina';
            $data['title'] = $row['title'];
            $data['short_summary'] = $row['shortSummary'];
            $data['source'] = $row['source'];
            $data['source_id'] = '49';
            $data['story_date'] = $row['storyDate'];
            $data['column_id'] = $this -> column($row['columnId']);
            if($row['title_pic3']){
                $data['img_count'] = 3;
            }elseif ($row['title_pic1']){
                $data['img_count'] = 1;
            }else {
                $data['img_count'] = 0;
            }
            $data['title_pic1'] = $row['title_pic1'];
            $data['title_pic2'] = $row['title_pic2'];
            $data['title_pic3'] = $row['title_pic3'];
            
            $data['url'] = $row['url'];
            $data['add_date'] = date('Y-m-d H:i:s');
            
            //文章信息入库
            $story_id = $_db_news_story -> add($data);
            
            if($story_id){
            
                //获取文章正文
                $contents = $_db_cms_story_content -> where("storyId = '{$row['id']}'") -> order("page ASC") -> select();
                $content = '';
                foreach ($contents as $val){
                    $content .= $val['content'];
                }
                $images = $M_snatch -> img($content);
                $content = strip_tags(trim($content) , '<p><img><div><table><tr><td>');
                
                $images_str = implode(';,;' , $images);
                $images_count = count($images);
                
                $data = array();
                $data['story_id'] = $story_id;
                $data['article_id'] = $row['id'];
                $data['page'] = '1';
                $data['content'] = $content;
                $data['images'] = $images_str;
                $data['image_count'] = $images_count;
                $data['http'] = '3306';
                $data['add_time'] = date('Y-m-d H:i:s');
                
                //文章正文入库
                $ins_id = $_db_news_story_content -> add($data);
                
                $i++;
                
            }
            
        }
        echo "INA UPDATE:{$i};\n";
        
        
        /* UUTV视频部分 */
        //视频类内容入库
        $max_id = $_db_news_story -> where("plant = 'uutv'") ->order("article_id DESC") -> find();
        $max_id = $max_id['article_id'];
        
        $i = 0;
        
        //查找未入库内容
        $list = $_db_cms_video -> where("id > '{$max_id}' AND platform = 'youku' AND status = 'published'") -> order("id ASC") -> limit(100) -> select();
        foreach ($list as $row){
            $data = array();
            $data['article_id'] = $row['id'];
            $data['plant'] = 'uutv';
            $data['title'] = $row['title'];
            $data['short_summary'] = '';
            $data['source'] = 'UUTV';
            $data['source_id'] = '48';
            $data['story_date'] = $row['publish_time'];
            $data['column_id'] = '20';
            $data['title_pic1'] = $row['img'];
            $data['img_count'] = 1;
            
            $data['url'] = $row['url'];
            $data['add_date'] = date('Y-m-d H:i:s');
            
            //文章信息入库
            $story_id = $_db_news_story -> add($data);
            
            if($story_id)
                $i++;

        }
        echo "UUTV UPDATE:{$i};\n";
        
        
    }
    
    private function column($id){
        switch ($id){
            case 1://导购
                return 1;
                break;
            case 2://评测
                return 2;
                break;
            case 3://新车
                return 3;
                break;
            case 4://行情
                return 4;
                break;
            case 5://行业
                return 5;
                break;
            case 6://文化
                return 6;
                break;
            case 7://用车
                return 7;
                break;
            case 8://车友
                return 8;
                break;
            case 9://百家
                return 9;
                break;
            case 10://自媒体
                return 10;
                break;
            
            default:
                return 0;
                break;
        }
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
                $story_info = $data;
                $story_info['id'] = $story_id;
                
                //填充content数据
                if($story_id){
                    $data = array();
                    $data['story_id'] = $story_id;
                    $data['article_id'] = $row['article_id'];
                    $data['page'] = 1;
                    $data['content'] = $result['content'];
                    $data['images'] = implode(';,;' , $result['images']);
                    $data['image_count'] = count($result['images']);
                    $data['http'] = $result['http'];
                    $data['add_time'] = date('Y-m-d H:i:s');
                    $_db_news_story_content -> add($data);
                    
                    //自动推荐到前台列表中
                    if($prv_source_id != $row['source_id']){
                        //写入前台列表
                        $news_id = $this -> intoChoice($story_info);
                        $prv_source_id = $row['source_id'];
                    }
                    $i++;
                }
            }
        }
        
        var_dump("Update:".$i);
        
    }
    
    /**
     * 推荐到前台列表
     *
     * @param 新闻信息 $story_info
     * @return int
     */
    private function intoChoice($story_info){
        $_db_news_choice = M('news_choice' , 'ad_' , 'DB0_CONFIG');
        $_db_news_story = M('news_story' , 'ad_' , 'DB0_CONFIG');
        
        $data = array();                        
        $data['story_id'] = $story_info['id'];
        $data['cate_id'] = $story_info['column_id'];
        $data['title'] = $story_info['title'];
        $data['summary'] = $story_info['short_summary'];
        $data['source'] = $story_info['source'];
        $data['source_id'] = $story_info['source_id'];
        if($story_info['title_pic3'])
            $data['images'] = $story_info['title_pic1'].';,;'.$story_info['title_pic2'].';,;'.$story_info['title_pic3'];
        else 
            $data['images'] = $story_info['title_pic1'];

        $data['story_date'] = $story_info['story_date'];
        if($story_info['plant'] == 'UUTV')
            $data['open_mode'] = 'video';
        else 
            $data['open_mode'] = 'news';

        $data['hot'] = rand(321,1999);
        $data['day'] = date('Y-m-d');
        $data['add_time'] = date('Y-m-d H:i:s');
        $news_id = $_db_news_choice -> add($data);
        
        $_db_news_story -> where("id = '{$story_info['id']}'") -> save(array('is_choice'=>'yes'));
        
        return $news_id;
    }
    
    /**
     * 自动推荐程序
     *
     */
    public function autoChoice(){
        $_db_news_choice = M('news_choice' , 'ad_' , 'DB0_CONFIG');
        $_db_news_source = M('news_source' , 'ad_' , 'DB0_CONFIG');
        $_db_news_story = M('news_story' , 'ad_' , 'DB0_CONFIG');
        
        $i = 0;
        $prv_source_id = 0;
        
        /* 获取最后的栏目id */
        $info = $_db_news_choice -> where("1") -> order("id DESC") -> find();
        $prv_source_id = $info['source_id'];
        
        /* 获取要处理的新闻 */
        $today = date('Y-m-d');
        $list = $_db_news_story -> where("is_choice = 'no' AND img_count > 0 AND story_date > '{$today}'") -> order("story_date ASC , article_id ASC") -> limit(100) -> select();
        foreach ($list as $row){
            if($i >= 10)
                continue;
            
            if($prv_source_id != $row['source_id']){
                //写入前台列表
                $news_id = $this -> intoChoice($row);
                $prv_source_id = $row['source_id'];
                $i++;
            }
        }
        
        var_dump("Update:".$i);
        
    }
    
    
}