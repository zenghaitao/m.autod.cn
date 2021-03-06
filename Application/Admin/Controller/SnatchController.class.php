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
        die('none');
        
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
        $_db_cms_story = M('ina_story' , 'cms_' , 'DB0_CONFIG');
        $_db_cms_story_content = M('ina_story_content' , 'cms_' , 'DB0_CONFIG');
        $_db_cms_video = M('ina_vedio' , 'cms_' , 'DB0_CONFIG');
        
        /* 删除网通社新闻内容 */
        $_db_news_story -> where("plant = 'ina'") -> delete();
        $_db_news_story -> where("plant = 'uutv'") -> delete();
        
        /* 网通社新闻部分 */
        $max_id = $_db_news_story -> where("plant = 'ina'") ->order("article_int_id DESC") -> find();
        $max_id = (int)$max_id['article_int_id'];
        
        $M_snatch = new SnatchModel();
        
        $list = $_db_cms_story -> where("status = 'published' AND id > '{$max_id}'") -> order("story_date ASC") -> select();
        foreach ($list as $row){
            /* 写入news_story表 */
            $data = array();
            $data['article_id'] = $row['id'];
            $data['plant'] = 'ina';
            $data['title'] = $row['title'];
            $data['short_summary'] = $row['short_summary'];
            $data['source'] = $row['source'];
            $data['source_id'] = '49';
            $data['story_date'] = $row['story_date']?$row['story_date']:'2015-01-01';
            $data['column_id'] = $this -> column($row['column']);
            if($row['title_pic2']){
                $data['title_pic1'] = $row['title_pic2'];
            }else {
                $data['title_pic1'] = $row['title_pic1'];
            }
            $data['img_count'] = 1;
            
            $data['url'] = $row['url'];
            $data['add_date'] = date('Y-m-d H:i:s');
            
            //文章信息入库
            $story_id = $_db_news_story -> add($data);
            
            if($story_id){
                //获取文章正文
                $contents = $_db_cms_story_content -> where("story_id = '{$row['id']}'") -> order("id ASC") -> select();
                $content = '';
                foreach ($contents as $val){
                    $content .= $val['content'];
                }
                if(!$content){
                    continue;
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
        $max_id = $_db_news_story -> where("plant = 'uutv'") ->order("article_int_id DESC") -> find();
        $max_id = (int)$max_id['article_int_id'];
        
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
            $data['img_count'] = 1;
            
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
    
    private function microtime( $decimals = 2 , $dec_point = '.' , $dec_point = ','  ){
        list( $usec ,  $sec ) =  explode ( " " ,  microtime ());
        $time = (float) $usec  + (float) $sec;
        $time = number_format ( $time ,  $decimals ,  $dec_point ,  $dec_point );
        return $time;
    }
    
    private function toutiaoNow($time){
        $url = "http://toutiao.com/api/article/recent/?source=2&count=20&category=news_car&utm_source=toutiao&offset=0&_={$time}";
        $json = json_decode(file_get_contents($url) , 1);
        return $json;
    }
    
    private function toutiaoNext($max_behot_time , $max_create_time , $time){
        $url = "http://toutiao.com/api/article/recent/?source=2&count=20&category=news_car&max_behot_time={$max_behot_time}&utm_source=toutiao&offset=0&max_create_time={$max_create_time}&_={$time2}";
        $json = json_decode(file_get_contents($url) , 1);
        return $json;
    }
    
    public function now(){
                
        //$time1 = $this -> microtime(2 , '.' , '') - 60 * 60;
        $time = $this -> microtime(3 , '' , '');
        $json = $this -> toutiaoNow($time);
        $max_create_time = 0;
        $id_data = array();
        $list = array();
        
        foreach ($json['data'] as $row){
            if($row['source'] == '头条专题'){
                continue;
            }
            if(!in_array($row['id'] , $id_data)){
                $id_data[] = $row['id'];
                $list[] = $row;
            }
            if($row['create_time'] > $max_create_time)
                $max_create_time = $row['create_time'];
        }
        $max_behot_time = $json['next']['max_behot_time'];
        
        for ($i = 0 ; $i < 5 ; $i ++ ){
            $time = $time + 1;
            $json = $this -> toutiaoNext($max_behot_time , $max_create_time , $time);
            foreach ($json['data'] as $row){
                if($row['source'] == '头条专题'){
                    continue;
                }
                if(!in_array($row['id'] , $id_data)){
                    $id_data[] = $row['id'];
                    $list[] = $row;
                }
                if($row['create_time'] > $max_create_time)
                    $max_create_time = $row['create_time'];
            }
            $max_behot_time = $json['next']['max_behot_time'];
        }
        
        foreach ($list as $row){
            var_dump($row['title'],$row['id']);
        }
    
    }
    
    public function syncIna(){
        $_db_ina_story = M('ina_story' , 'cms_' , 'DB0_CONFIG');
        $_db_ina_story_content = M('ina_story_content' , 'cms_' , 'DB0_CONFIG');
        $_db_ina_video = M('ina_vedio' , 'cms_' , 'DB0_CONFIG');
        
        $url = 'http://api.news18a.com/init.php?m=api&c=dujia&a=story';
        $json = json_decode(file_get_contents($url),1);
        
        $i = 0;
        $j = 0;
        if(!$json['error']){
        //if(0){
            foreach ($json as $row){
                $data = array();
                $data['iid'] = $row['iid'];
                $data['title'] = $row['title'];
                $data['shorttitle'] = $row['shorttitle'];
                $data['keyword'] = $row['keyword'];
                $data['column'] = $row['column'];
                $data['logo_id'] = $row['logo_id'];
                $data['series_id'] = $row['series_id'];
                $data['author'] = $row['writer'];
                $data['editor'] = $row['editor'];
                $data['source'] = $row['source'];
                $data['story_date'] = $row['story_date'];
                $data['title_pic1'] = $row['pic1'];
                $data['title_pic1'] = $row['pic1'];
                $data['short_summary'] = $row['short_summary'];
                $data['url'] = $row['url'];
                $data['status'] = 'published';
                $data['add_date'] = date('Y-m-d H:i:s');
                $data['modi_date'] = date('Y-m-d H:i:s');
                
                $info = $_db_ina_story -> where("iid = '{$row['iid']}'") -> find();
                $j++;
                if(!$info){
                    //文章信息入库
                    $story_id = $_db_ina_story -> add($data);
                    if($story_id){
                        //写入content库
                        foreach ($row['page'] as $page){
                            $data = array();
                            $data['story_id'] = $story_id;
                            $data['sub_title'] = $page['sub_title'];
                            $data['content'] = $page['content'];
                            $data['dt'] = date('Y-m-d H:i:s');
                            $_db_ina_story_content -> add($data);
                        }
                    }
                    $i++;
                }
            }
        }
        echo "add news:{$i}/{$j}";
        
        $i = 0;$j=0;
        $url = 'http://api.news18a.com/init.php?m=api&c=dujia&a=video';
        $json = json_decode(file_get_contents($url),1);
        if(!$json['error']){
            foreach ($json as $row){
                $info = $_db_ina_video -> where("iid = '{$row['iid']}'") -> find();
                $j++;
                if(!$info){
                    $data = array();
                    $data = $row;
                    $_db_ina_video -> add($data);
                    $i++;
                }
            }
        }
        echo "add video:{$i}/{$j}";
    }
    
    /**
     * 抓取INA的数据进story表
     *
     */
    public function ina(){
        $_db_news_source = M('news_source' , 'ad_' , 'DB0_CONFIG');
        $_db_news_story = M('news_story' , 'ad_' , 'DB0_CONFIG');
        $_db_news_story_content = M('news_story_content' , 'ad_' , 'DB0_CONFIG');
        
        $_db_cms_story = M('ina_story' , 'cms_' , 'DB0_CONFIG');
        $_db_cms_story_content = M('ina_story_content' , 'cms_' , 'DB0_CONFIG');
        
        $_db_cms_video = M('ina_vedio' , 'cms_' , 'DB0_CONFIG');
        
        $M_snatch = new SnatchModel('');
        
        /* 网通社新闻部分 */
        $max_id = $_db_news_story -> where("plant = 'ina'") ->order("article_int_id DESC") -> find();
        $max_id = $max_id['article_int_id'];
        
        $i = 0;
        
        //查找未入库内容
        $list = $_db_cms_story -> where("id > '{$max_id}' AND status = 'published'") -> order("id ASC") -> limit(100) -> select();
        foreach ($list as $row){
            $data = array();
            $data['article_id'] = $row['id'];
            $data['article_int_id'] = $row['id'];
            $data['plant'] = 'ina';
            $data['title'] = $row['title'];
            $data['short_summary'] = $row['short_summary'];
            $data['source'] = $row['source'];
            $data['source_id'] = '49';
            $data['story_date'] = $row['story_date'];
            $data['column_id'] = $this -> column($row['column']);
            if($row['title_pic2']){
                $data['title_pic1'] = $row['title_pic2'];
            }else {
                $data['title_pic1'] = $row['title_pic1'];
            }
            $data['img_count'] = 1;
            
            $data['url'] = $row['url'];
            $data['add_date'] = date('Y-m-d H:i:s');
            
            //文章信息入库
            $story_id = $_db_news_story -> add($data);
            
            if($story_id){
            
                //获取文章正文
                $contents = $_db_cms_story_content -> where("story_id = '{$row['id']}'") -> order("id ASC") -> select();
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
        $max_id = $_db_news_story -> where("plant = 'uutv'") ->order("article_int_id DESC") -> find();
        $max_id = (int)$max_id['article_int_id'];
        
        $i = 0;
        
        //查找未入库内容
        $list = $_db_cms_video -> where("id > '{$max_id}' AND platform = 'youku' AND status = 'published'") -> order("id ASC") -> limit(100) -> select();
        foreach ($list as $row){
            $data = array();
            $data['article_id'] = $row['id'];
            $data['article_int_id'] = $row['id'];
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
    
    private function column($column){
        if(in_array($column , array('进口新车','国产新车','SUV-新车','新车资讯','国产电动车','安全测试','SUV-新闻','图说新车','suv','车界娱乐','汽车IT技术','无人驾驶'))){
            return 3;
        }
        if(in_array($column , array('进口车测试','国产车测试','车型对比'))){
            return 2;
        }
        if(in_array($column , array('导购信息','加价/降价','维修保养'))){
            return 1;
        }
        if(in_array($column , array('行业新闻','高端采访','环保科技','召回预警','明星人物','人事变动','行业研究','行业分析','厂商要闻','人物百科','工厂探秘','车展专题'))){
            return 5;
        }
        return 0;
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
        
        $M_snatch = new SnatchModel();
        
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
            $pages = $M_snatch -> toutiaoPageList($url);
            
            $array = array();
            $_find = 0;
            //本次需采集的页面列表
            foreach ($pages as $key => $page){
                if($_find){
                    continue;
                }
                
                $news_list = $M_snatch -> toutiaoPage($url);
                
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
        $M_snatch = new SnatchModel();
        
        /* 获取要处理的页面 */
        $list = $_db_news_spider_page -> where("is_snatch = 'no' AND img_count > 0") -> order("story_date ASC , article_id ASC") -> limit(100) -> select();
        foreach ($list as $row){
            if($i >= 10)
                continue;
            
            //$M_snatch = new SnatchModel('http://toutiao.com/a4639192103/');
            $result = $M_snatch -> toutiaoContent($row['url']);
            
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
                    $data['article_int_id'] = $row['article_id'];
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
        $M_news = new NewsModel();
        $news_id = $M_news -> addNewsToChoice($story_info['id']);
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
        if($prv_source_id == '49')
            $prv_source_id = 0;
        
        /* 获取要处理的新闻 */
        $today = date('Y-m-d',time() - 3600 * 24 * 2);
        $list = $_db_news_story -> where("is_choice = 'no' AND img_count > 0 AND add_date > '{$today}'") -> order("story_date ASC , article_int_id ASC") -> limit(100) -> select();
        foreach ($list as $row){
            if($i >= 10)
                continue;
            
            if($prv_source_id != $row['source_id']){
                //写入前台列表
                $news_id = $this -> intoChoice($row);
                $prv_source_id = $row['source_id'];
                if($prv_source_id == '49')
                    $prv_source_id = 0;
                $i++;
            }
        }
        
        var_dump("Update:".$i);
        
    }
    
    
}