<?php
namespace Home\Controller;
use Admin\Model\AppRecommendModel;

use API\Model\NewsModel;
use API\Model\StoryModel;
use API\Model\UserModel;
use Home\Model\SnatchModel;

class IndexController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
    }
    
    public function index(){
        
        $_PAGE['title'] = "《汽车日报》每日汽车新闻播报! - autod.cn";
        $_PAGE['keywords'] = "汽车,汽车日报网,汽车日报,汽车每日报,汽车每日播报 - autod.cn";
        $_PAGE['description'] = "《汽车日报》(autod.cn)是一款汇集全网汽车资讯的软件,它每天都会为您推荐不一样的汽车新闻资讯,并且越用越懂你! - autod.cn";
        $this -> assign('_PAGE',$_PAGE);
        
        $this -> display('index');
    }
    
    /**
     * 推荐新闻列表
     *
     */
    public function news(){
        
        $page = $_GET['page'];
        if(!isset($_GET['page']) && $page != 'down' && $page != 'up')
            $page = 'none';
            
        //最小id
        $since_id = (int)$_GET['sinceId'];
        //最大id
        $max_id = (int)$_GET['maxId'];
        //返回记录数
        $count = rand(8 , 14);
        if($page == 'none')
            $count = rand(18 , 28);
        
        //客户端是否刷新列表
        $refresh = 'no';
        //新入新闻行数
        $laest_news_count = 0;
        //新入新闻
        $laest_news = array();
        //拉取新闻
        $pull_news = array();
        //填补新闻
        $other_news = array();
        
        $M_news = new NewsModel();
        
        $M_news -> initTodayNews();
        
        if($max_id){
            $result = $M_news -> laestNews($max_id , $count);
            if($result['count'] > $count)
                $refresh = 'yes';
            
            if($result['list']){
                $laest_news = $result['list'];
                $end_laest_news = end($laest_news);
            }
            $laest_news_count = count($result['list']);
        }
        
        //更新客户端新闻行数
        $count = $count - $laest_news_count;
        
        if($refresh != 'yes' && $count > 1){
            //继续获取新闻
            $pull_news = $M_news -> pullNews($since_id , $count , $page);
            $count = $count - count($pull_news);
        }
        
        if($count > 0){
            //填充新闻
            $other_news = $M_news -> pullNews((int)$end_laest_news['id'] , $count , 'down');
        }
        
        //合并三份数据
        $list = array_merge ( $laest_news ,  $pull_news , $other_news);
        
        foreach ($list as &$row){
            //格式化新闻行记录
            $row = $this -> formatNews($row);
            
            if($row['id'] > $max_id)
                $max_id = $row['id'];
        }
        $since_id = (int)$row['id'];
        
        //获取广告数据
        $ad = $this -> newsAD();
        
        //合并新闻和广告
        $list = $this -> mergeList($list , $ad);
        
        if($page == 'none')
            $refresh = 'yes';
        
        $result = array();
        $result['statuses'] = $list;
        $result['updateCount'] = count($list);
        $result['sinceId'] = $since_id;
        $result['maxId'] = $max_id;
        $result['refresh'] = $refresh;

        $this -> succ($result);
    }
    
    /**
     * 格式化时间字符串
     *
     * @param string $date
     * @return string
     */
    private function formatTime($date){
        $time = strtotime($date);
        $now = time();
        
        $day = date('m-d' , $time);
        $today = date('m-d');
        
        $diff = $now - $time;
        
        $diff_min = ceil($diff / 60);
        $diff_5min = ceil($diff / 60 / 5) * 5;
        $diff_15min = ceil($diff / 60 / 15) * 15;
        $diff_hor = round($diff / 3600);
        
        if($diff < 60)
            return '刚刚';
        if($diff_min <= 5)
            return $diff_min.'分钟前';
        if($diff_min <= 30)
            return $diff_5min.'分钟前';
        
        if($diff_min <= 60)
            return $diff_15min.'分钟前';
            
        if($diff_min < 60 * 6)
            return $diff_hor.'小时前';
        
        if($day == $today)
            return date('H:i' , $time);
        
        return $day;
    }
    
    /**
     * 合并推荐列表
     *
     * @param array $news
     * @param array $ads
     * @return array
     */
    private function mergeList($news , $ads){
        $i = 0;
        $j = 0;
        $list = array();
        
        shuffle($ads);
        $ads = array_slice($ads , 0 , 3);
        
        foreach ($news as $row){
            $i++;
            $list[] = $row;
            if($i % 3 == 0){
                if(isset($ads[$j])){
                    $list[] = $ads[$j];
                    $j ++;
                }
            }
        }
        
        return $list;
    }
    
    
    /**
     * Enter description here...
     *
     * @return unknown
     */
    private function newsAD(){
        if($_SERVER['IS_DEBUG'] != 'yes')
            return array();
        $adlist = array();
        
        $i = 0;
        //获取广告数据
        $ad['title'] = '2015款雷诺风景 全新上市 莅临试驾';
        $ad['images'] = 'http://7xjrkc.com1.z0.glb.clouddn.com/01.jpg';
        $ad['type'] = 'ad';
        $ad['gourl'] = 'http://www.dongfeng-renault.com.cn/';
        $ad['story_date'] = date('Y-m-d H:i:s');
        $ad = $this -> formatNews($ad);
        
        $ad['openMode'] = 'topic';
        $ad['displayMode'] = 'C';
        $adlist[$i++] = $ad;
        
        //获取广告数据
        $ad['title'] = 'BMW宝马金融服务 助您轻松拥有宝马';
        $ad['images'] = 'http://7xjrkc.com1.z0.glb.clouddn.com/03.jpg';
        $ad['type'] = 'ad';
        $ad['gourl'] = 'http://www.bmw.com.cn';
        $ad['story_date'] = date('Y-m-d H:i:s');
        $ad = $this -> formatNews($ad);
        
        $ad['openMode'] = 'topic';
        $ad['displayMode'] = 'C';
        $adlist[$i++] = $ad;
        
        //获取广告数据
        $ad['title'] = 'BMW宝马金融服务 助您轻松拥有宝马';
        $ad['images'] = 'http://7xjrkc.com1.z0.glb.clouddn.com/04.jpg';
        $ad['type'] = 'ad';
        $ad['gourl'] = 'http://www.bmw.com.cn';
        $ad['story_date'] = date('Y-m-d H:i:s');
        $ad = $this -> formatNews($ad);
        
        $ad['openMode'] = 'topic';
        $ad['displayMode'] = 'C';
        $adlist[$i++] = $ad;
        
        //获取广告数据
        $ad['title'] = 'BMW宝马金融服务 助您轻松拥有宝马';
        $ad['images'] = 'http://7xjrkc.com1.z0.glb.clouddn.com/05.jpg';
        $ad['type'] = 'ad';
        $ad['gourl'] = 'http://www.bmw.com.cn';
        $ad['story_date'] = date('Y-m-d H:i:s');
        $ad = $this -> formatNews($ad);
        
        $ad['openMode'] = 'topic';
        $ad['displayMode'] = 'C';
        $adlist[$i++] = $ad;
        
        return $adlist;
    }
    
    
    /**
     * 格式化新闻行记录
     *
     * @param array $row
     * @return array
     */
    private function formatNews($row){
        
        $news['id'] = (int)$row['id'];
        $news['storyId'] = (int)$row['story_id'];
        $news['cateId'] = (int)$row['cate_id'];
        $news['title'] = (string)$row['title'];
        $news['summary'] = (string)$row['summary'];
        $news['source'] = (string)$row['source'];
        $news['sourceId'] = (int)$row['source_id'];
        $images = explode(';,;' , $row['images']);
        $news['imageCount'] = count($images);
        $news['images'] = (array)$images;
        
        $news['postTime'] = (string)$row['story_date'];
        $news['timeString'] = $this -> formatTime($row['story_date']);
        
        $news['type'] = $row['type'];
        if($news['imageCount'] == 3)
            $news['displayMode'] = 'B';
        else 
            $news['displayMode'] = 'A';
        if($news['type'] == 'ad')
            $news['displayMode'] = 'C';
        $news['openMode'] = (string)$row['open_mode'];
/*        if($news['imageCount'] > 1)
            $news['openMode'] = 'image';*/
            
        $news['gourl'] = (string)$row['gourl'];
        
        $news['favCount'] = (int)$row['fav_count'];
        $news['likeCount'] = (int)$row['like_count'];
        $news['commentsCount'] = (int)$row['comments_count'];
        
        $news['hot'] = (int)$row['hot'];
        
        if($news['type'] == 'default' && $news['type']){
            if($news['hot'] > 1900)
                $news['type'] = 'head';
            elseif($news['hot'] > 1600)
                $news['type'] = 'recommend';
            elseif($news['hot'] > 1000)
                $news['type'] = 'hot';
        }
        return $news;
    }
    
    /**
     * 分类新闻列表
     *
     */
    public function cateList(){
        $cate_id = $_GET['cateId'];
        $count = (int)$_GET['count'];
        if(!$count)
            $count = 10;
        $begin_id = (int)$_GET['sinceId'];
        $M_news = new NewsModel();
        $list = $M_news -> newsCateList($cate_id , $begin_id , $count);
        $since_id = 0;
        
        foreach ($list as &$row){
            //格式化新闻行记录
            $row = $this -> formatNews($row);
            
            $since_id = $row['id'];
        }
        
        $result = array();
        $result['statuses'] = $list;
        $result['updateCount'] = count($list);
        $result['sinceId'] = $since_id;
        
        $this -> succ($result);
    }
    
    /**
     * 新闻页内容
     *
     */
    public function page(){
        $news_id = (int)$_GET['id'];
        
        //新闻信息
        $M_news = new NewsModel();
        $info = $M_news -> getNews($news_id);
        
        //来源信息
        $source = $M_news -> getSource($info['source_id']);
        $this -> assign('source' , $source);
        var_dump($_SESSION);exit;
        /* 是否已订阅 */
        if($_SESSION['user_id']){
            $followed = $M_news -> followed($info['source_id'] , $_SESSION['user_id']);
            $source['followed'] = $followed?'yes':'no';
        }else{
            $source['followed'] = 'no';
        }
        
        //广告信息
        $ad = $this -> newsAD();
        shuffle($ad);
        
        $this -> assign('ad' , end($ad));
        
        //记录hot值
        $M_news -> incHot($news_id);
        
        $_PAGE['title'] = "{$info['title']} - 汽车日报(autod.cn)";
        $_PAGE['keywords'] = "{$info['title']} - 汽车日报(autod.cn)";
        $_PAGE['description'] = "{$info['summary']} - 汽车日报(autod.cn)";
        $this -> assign('_PAGE',$_PAGE);
        
        if($_GET['type']=='app'){
            $this -> appShowPage($info);
            exit;
        }
        
        if($info['open_mode'] == 'video'){
            $this -> video($info);
            exit;
        }
        
        if($info['open_mode'] == 'topic'){
            $this -> topic($info);
            exit;
        }
        
        if($info['open_mode'] == 'news'){
            $this -> content($info);
            exit;
        }
        
        if($info['open_mode'] == 'pic'){
            $this -> photo($info);
            exit;
        }
        
        if($info['open_mode'] == 'sns'){
            $this -> sns($info);
            exit;
        }
    }
    
    /**
     * 新闻内容
     *
     */
    private function appShowPage($info){
        $news_id = $info['id'];
        $session_id = $_GET['sessionId'];
        $uid = $_SESSION['user_id'];
        
        $M_news = new NewsModel();
        //页面内容
        $M_story = new StoryModel();
        $page = $M_story -> getStoryPage($info['story_id']);
        $page = $page['html'];
        $page = str_replace('src="','_src="',$page);
        $page = str_replace('alt="','_alt="',$page);
        
        //获取关键词
        $keywords = $M_news -> newsKeyword($news_id);
        
        //转化关键词链接
        foreach ($keywords as $row){
            $page = $this -> makeKeywordsLink($row , $page);
        }
        
        if($uid)
            $this -> assign('isLogin' , 'yes');
        else 
            $this -> assign('isLogin' , 'no');
        
        //判断来源
        if(stripos($_SERVER['HTTP_USER_AGENT'] , 'ios') !== false){
            $this -> assign('deviceOS' , 'ios');
        }else{
            $this -> assign('deviceOS' , 'android');
        }
            
        //相关新闻
        $relates = $M_news -> getRelatedNews($news_id , 0 , 6);
        foreach ($relates as &$row){
            $row = $this -> formatNews($row);
        }
        
        $this -> assign('host' , 'http://'.$_SERVER['HTTP_HOST']);
        $this -> assign('session_id' , $session_id);
        $this -> assign('info' , $info);
        $this -> assign('page' , $page);
        $this -> assign('comments' , $comments);
        $this -> assign('relates' , $relates);
        
        $this -> display('app_page');
    }
    
    /**
     * 新闻内容
     *
     */
    private function content($info){
        $news_id = $info['id'];
        $M_news = new NewsModel();
        //页面内容
        $M_story = new StoryModel();
        $page = $M_story -> getStoryPage($info['story_id']);
        $page = $page['html'];
        
        //获取关键词
        $keywords = $M_news -> newsKeyword($news_id);
        
        //转化关键词链接
        foreach ($keywords as $row){
            $page = $this -> makeKeywordsLink($row , $page);
        }
        
        //热门评论
        $comments = $M_news -> commentsList($news_id , 0 , 20);
        
        //相关新闻
        $relates = $M_news -> getRelatedNews($news_id , 0 , 10);
        foreach ($relates as &$row){
            $row = $this -> formatNews($row);
        }
        
        $this -> assign('info' , $info);
        $this -> assign('page' , $page);
        $this -> assign('comments' , $comments);
        $this -> assign('relates' , $relates);
        
        $this -> display('page1');
    }
    
    /**
     * 更替关键词链接
     *
     * @param 关键词 $info
     * @param string $content
     * @return string
     */
    private function makeKeywordsLink($info , $content){
        //标签处理
        $content = preg_replace("/\r\n/","",$content);
        $tag_arr = array();
        //图片
        $img_pattern = "/<img[^>]*\>/";
        $a_pattern = "/<a[^>]*\>[^<]*<\/a>/";
        $table_pattern = "/<td[^>]*\>[^<]*<\/td>/";
        preg_match_all($img_pattern,$content,$match_img);
        preg_match_all($a_pattern,$content,$match_a);
        preg_match_all($table_pattern,$content,$match_table);
        $i = 10000;
        foreach ($match_img[0] as $row){
            $content = str_replace($row , "<!--{$i}-->" , $content);
            $tag_arr[$i] = $row;
            $i++;
        }
        foreach ($match_a[0] as $row){
            $content = str_replace($row , "<!--{$i}-->" , $content);
            $tag_arr[$i] = $row;
            $i++;
        }
        foreach ($match_table[0] as $row){
            $content = str_replace($row , "<!--{$i}-->" , $content);
            $tag_arr[$i] = $row;
            $i++;
        }
        
        if($info['type'] == 'bseries'){
            $content = preg_replace('/'.$info['keyword'].'/' , "<a href='###' pid='{$info['pid']}' type='{$info['type']}'>{$info['keyword']}</a>" , $content , 1 );
/*            $new_keyword = str_replace(' ' , $info['keyword']);
            if($new_keyword != $info['keyword']){
                $content = preg_replace('/'.$new_keyword.'/' , "<a href='###' pid='{$info['pid']}' type='{$info['type']}'>{$info['keyword']}</a>" , $content , 1 );
            }*/
        }
        //标签还原
        foreach ($tag_arr as $key => $row){
            $content = str_replace("<!--{$key}-->" , $row , $content);
        }
        return $content;
    }
    
    /**
     * 视频内容
     *
     */
    private function video($info){
        $news_id = $info['id'];
        $M_news = new NewsModel();
        
        //页面内容
        $M_story = new StoryModel();
        $story = $M_story -> getStoryInfo($info['story_id']);
        $video = $M_story -> getVideo($story['article_id']);
        
        $this -> assign('info' , $info);
        $this -> assign('video' , $video);
        
        if(isset($_GET['mini'])){
            $this -> display('video-mini');
            exit;
        }
        
        //热门评论
        $comments = $M_news -> commentsList($news_id , 0 , 50);
        
        //相关新闻
        $relates = $M_news -> getRelatedNews($news_id , 20 , 20);
        foreach ($relates as &$row){
            $row = $this -> formatNews($row);
        }
        
        $this -> assign('comments' , $comments);
        $this -> assign('relates' , $relates);
        
        $this -> display('video1');
    }
    
    /**
     * APP下载跳转链接
     *
     */
    public function download(){
       $ios = 'https://itunes.apple.com/cn/app/qi-che-ri-bao/id850404817?mt=8';
       $android = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.autod.toutiao';
       $pc = "http://www.autod.cn/app";
       
       $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
       if(strpos( $user_agent , 'iphone') !== false || strpos( $user_agent , 'ipad') !== false){
           header("Location:{$ios}");
       }elseif(strpos( $user_agent , 'android') !== false){
           header("Location:{$android}");
       }else{
           header("Location:{$pc}");
       }
       exit;
    }
    
    /**
     * 使用帮助
     *
     */
    public function help(){
        $this -> display('help');
    }
    
    /**
     * 关于我们
     *
     */
    public function about(){
        $this -> display('about');
    }
    
    /**
     * 意见反馈
     *
     */
    public function feedback(){
        $status = 'init';
               
        if($_POST){
            //记录反馈信息
            $data = array();
            $data['reg_id'] =  $_SESSION['reg_id'];
            $data['feedback'] =  htmlspecialchars($_POST['feedback']);
            $data['contacts'] =  htmlspecialchars($_POST['contacts']);
            $data['add_time'] = date('Y-m-d H:i:s');
            
            $M_user = new UserModel();
            if($M_user -> feedback($data)){
                $status = 'succ';
            }else{
                $status = 'fail';
            }
            die($status);
        }
        
        $this -> assign('session_id' , $_GET['sessionId']);
        $this -> display('feedback');
    }
    
    /**
     * 找回密码
     *
     */
    public function findPwd(){
        if($_POST){
            $M_user = new UserModel();
            if($_GET['action'] == 'sendCode'){
                $phone = $_POST['phone'];
                $res = $M_user -> makeCode($phone);
                if($res){
                    echo 'succ';
                }else{
                    echo 'error';
                }
                exit;
            }
            
            if($_GET['action'] == 'changePwd'){
                $phone = $_POST['phone'];
                $code = $_POST['code'];
                $pwd = $_POST['npwd'];
                
                $res = $M_user -> findPwd($phone , $code , $pwd);
                if($res){
                    echo 'succ';
                }else{
                    echo '无效的验证码，请重试！';
                }
                exit;
            }
        }
        
        $this -> assign('phone' , $_GET['phone']);
        $this -> display('findPwd');
    }
    
    /**
     * 评论列表
     *
     */
    public function comments(){
        $news_id = (int)$_GET['newsId'];
        $session_id = $_GET['sessionId'];
        $since_id = (int)$_GET['sinceId'];
        
        $_PAGE['title'] = "评论列表";
        $this -> assign('_PAGE',$_PAGE);
        
        $M_news = new NewsModel();
        
        //热门评论
        $comments = $M_news -> commentsList($news_id , $since_id , 10);
        $since_id = end($comments);
        $since_id = $since_id['id'];
        
        if(!$_GET['sinceId']){
            $hot_comments = $M_news -> commentsHotList($news_id);
        }
        
        $this -> assign('news_id' , $news_id);
        $this -> assign('session_id' , $session_id);
        $this -> assign('since_id' , $since_id);
        $this -> assign('hot_comments' , $hot_comments);
        $this -> assign('comments' , $comments);
        
        
        if($_GET['sinceId']){
            $this -> assign('type' , 'list');
            $html = $this -> fetch('comments');
            echo json_encode(array('sinceId'=>$since_id,'html'=>$html,'count'=>count($comments)));
            exit;
        }
        
        
        $this -> display('comments');
    }
    
    /**
     * 我的评论
     *
     */
    public function myComment(){
        $session_id = $_GET['sessionId'];
        $uid = $_SESSION['user_id'];
        $since_id = (int)$_GET['sinceId'];
        
        if(!uid)
            die('need login!');
        
        $_PAGE['title'] = "我的评论";
        $this -> assign('_PAGE',$_PAGE);
        
        $M_news = new NewsModel();
        
        //热门评论
        $comments = $M_news -> myCommentList($uid , $since_id , 10);
        $since_id = end($comments);
        $since_id = $since_id['id'];
        
        $this -> assign('session_id' , $session_id);
        $this -> assign('since_id' , $since_id);
        $this -> assign('news_id' , $news_id);
        $this -> assign('comments' , $comments);
        
        if($_GET['sinceId']){
            $this -> assign('type' , 'list');
            $html = $this -> fetch('comments');
            echo json_encode(array('sinceId'=>$since_id,'html'=>$html,'count'=>count($comments)));
            exit;
        }
        
        $this -> display('my_comments');
    }
    
    /**
     * 我的订阅
     *
     */
    public function followList(){
        $session_id = $_GET['sessionId'];
        $uid = $_SESSION['user_id'];
        
        $M_news = new NewsModel();
        $res = $M_news -> followList($uid);
        
        $list = array();
        foreach ($res as $row){
            $row = $M_news -> getSource($row['source_id']);
            $row['last_time'] = date('Y-m-d',strtotime($row['last_time']));
            $row['followed'] = 'yes';
            $list[] = $row;
        }
        
        $this -> assign('list' , $list);
        $this -> assign('session_id' , $session_id);
        
        $this -> display('follow_list');
    }
    
    /**
     * 可订阅列表
     *
     */
    public function sourceList(){
        $session_id = $_GET['sessionId'];
        $uid = $_SESSION['user_id'];
        
        $M_news = new NewsModel();
        $list = $M_news -> getSoureList();
        
        $follows = $M_news -> followList($uid);
        $follow_ids = array();
        foreach ($follows as $row){
            $follow_ids[] = $row['source_id'];
        }
        
        foreach ($list as &$row){
            $row['last_time'] = date('Y-m-d',strtotime($row['last_time']));
            
            if(in_array($row['id'] , $follow_ids)){
                $row['followed'] = 'yes';
            }else{
                $row['followed'] = 'no';
            }
        }
        
        $this -> assign('list' , $list);
        $this -> assign('session_id' , $session_id);
        
        $this -> display('source_list');
    }
    
    /**
     * 媒体新闻列表
     *
     */
    public function source(){
        $session_id = $_GET['sessionId'];
        $source_id = $_GET['sourceId'];
        $begin_id = (int)$_GET['sinceId'];
        
        $M_news = new NewsModel();
        $list = $M_news -> newsSourceList($source_id , $begin_id);
        $since_id = 0;
        
        foreach ($list as &$row){
            //格式化新闻行记录
            $row = $this -> formatNews($row);
            
            $since_id = $row['id'];
        }
        
        $info = $M_news -> getSource($source_id);
        
        $this -> assign('session_id' , $session_id);
        $this -> assign('source_id' , $source_id);
        $this -> assign('since_id' , $since_id);
        $this -> assign('list' , $list);
        $this -> assign('info' , $info);
        
        if($begin_id){
            $this -> assign('type' , 'list');
            $html = $this -> fetch('source');
            echo json_encode(array('sinceId'=>$since_id,'html'=>$html,'count'=>count($list)));
            exit;
        }
        
        $this -> display('source');
    }
    
    public function search(){
        $session_id = (int)$_GET['sessionId'];
        $keyword    =   $_GET['keyword'];
        $since_id   = (int)$_GET['sinceId'];
        $count      = 10;
        
        $M_news = new NewsModel();
        $list = $M_news -> search($keyword , $since_id , $count);
        foreach ($list as &$row){
            //格式化新闻行记录
            $row = $this -> formatNews($row);
            if($row['imageCount'] > 1)
                $row['openMode'] = 'image';
            $since_id = $row['id'];
        }
        
        $this -> assign('since_id' , $since_id);
        $this -> assign('keyword' , $keyword);
        $this -> assign('session_id' , $session_id);
        $this -> assign('list' , $list);
        
        if($_GET['sinceId']){
            $this -> assign('type' , 'list');
            $html = $this -> fetch('search');
            
            echo json_encode(array('sinceId'=>$since_id,'html'=>$html,'count'=>count($list)));
            
            exit;
        }
        
        $this -> display('search');
    }
    
    public function favList(){
        $session_id = (int)$_GET['sessionId'];
        $uid = $_SESSION['user_id'];
        $since_id = (int)$_GET['sinceId'];
        
        $M_news = new NewsModel();
        $res = $M_news -> favList($uid , $since_id , 10);
    
        $since_id = 0;
        $list = array();
        foreach ($res as $row){
            $since_id = $row['id'];
            $list[] = $M_news -> getNews($row['news_id']);
        }
        
        foreach ($list as &$row){
            //格式化新闻行记录
            $row = $this -> formatNews($row);
        }
        
        
        $this -> assign('session_id' , $session_id);
        $this -> assign('since_id' , $since_id);
        
        $this -> assign('list' , $list);
        
        if($_GET['sinceId']){
            $this -> assign('type' , 'list');
            $html = $this -> fetch('fav_list');
            echo json_encode(array('sinceId'=>$since_id,'html'=>$html,'count'=>count($list)));
            exit;
        }
        
        $this -> display('fav_list');
    }
    
    public function appStore(){
        $list = array();
        
        /*
        $row = array();
        $row['name'] = '微用';
        $row['url'] = 'http://www.weyoo.com.cn/';
        $row['icon'] = 'http://7xjrkc.com1.z0.glb.clouddn.com/icon68.png';
        $row['intro'] = '一秒下载安装获得3000+优质应用，微用引领H5新潮流，是手机必备的应用聚合工具';
        $row['size'] = '2.2';
        $list[] = $row;
        
        $row = array();
        $row['name'] = '网尚实用查询';
        $row['url'] = 'http://m.46644.com/appstore/down.php';
        $row['icon'] = 'http://img2.autod.cn/autod_img/index_link/icon68.jpg';
        $row['intro'] = '网尚实用查询为用户提供实用便民工具，涵盖生活中的各个方面，方便快捷。工具包括：天气、万年历、公交、违章、火车、长途汽车、快递、空气指数、彩票开奖、航班查询、翻译、新华字典、国学经典、算命等。';
        $row['size'] = '2.49';
        $list[] = $row;
        
        $row = array();
        $row['name'] = '火速轻应用';
        $row['url'] = 'http://www.huosu.com/phone_download.php';
        $row['icon'] = 'http://img2.autod.cn/autod_img/index_link/icon68_huosuqingyingyong.png';
        $row['intro'] = '火速轻应用，在这里数千款Html5轻应用/游戏一触即玩，无需下载及更新。';
        $row['size'] = '1.82';
        $list[] = $row;
        
        $row = array();
        $row['name'] = '微信';
        $row['url'] = 'http://www.wandoujia.com/apps/com.tencent.mm';
        $row['icon'] = 'http://img.wdjimg.com/mms/icon/v1/3/bd/5295ac6a9c6d51e8285690bdbe1b1bd3_68_68.png';
        $row['intro'] = '可以发语音、文字消息、表情、图片、视频。30M 流量可以收发上千条语音，省电省流量。';
        $row['size'] = '22.62';
        $list[] = $row;
        
        $row = array();
        $row['name'] = 'QQ';
        $row['url'] = 'http://www.wandoujia.com/apps/com.tencent.mobileqq';
        $row['icon'] = 'http://img.wdjimg.com/mms/icon/v1/4/38/f7210e10b4e624d71611d972f9a66384_68_68.png';
        $row['intro'] = 'QQ 4.5 带来了全新的阅读中心、表情签到、空间挂件、阅后即焚等多项功能。';
        $row['size'] = '23.35';
        $list[] = $row;
        
        $row = array();
        $row['name'] = '微博';
        $row['url'] = 'http://www.wandoujia.com/apps/com.sina.weibo';
        $row['icon'] = 'http://img.wdjimg.com/mms/icon/v1/e/49/b91822e4adc0540a15b321ed7587d49e_68_68.png';
        $row['intro'] = '官方客户端更新，拥有全新的界面，主页新增高清头图，支持二维码扫描并在正文直接评论转发微博。';
        $row['size'] = '21.28';
        $list[] = $row;
        
        $row = array();
        $row['name'] = '优酷';
        $row['url'] = 'http://www.wandoujia.com/apps/com.youku.phone';
        $row['icon'] = 'http://img.wdjimg.com/mms/icon/v1/3/2d/dc14dd1e40b8e561eae91584432262d3_68_68.png';
        $row['intro'] = '活动内详：23 日前在优酷应用内的“用户反馈”中提交使用感受，有机会赢得三星 Note 2 等豪礼！';
        $row['size'] = '18.67';
        $list[] = $row;
        */
        $M_recommend = new AppRecommendModel();
        $list = $M_recommend -> getAllRecommend();
		
        $this -> assign('list' , $list);
        $this -> display('appstore');
    }

}