<?php
namespace Admin\Controller;
use Think\Controller;

class BaseController extends Controller{
    
    public function __construct(){
        parent::__construct();
        
        $this -> setMenu();
        
    }
    
    /**
     * 初始化左侧菜单
     *
     */
    private function setMenu(){
        $menu = array();
        $i = 0;
        $menu['index'][$i]['action'] = 'index';
        $menu['index'][$i]['name'] = '控制台';
        $menu['index'][$i]['url'] = '/Admin/Index/';
        $i++;
        
        $menu['news'][$i]['action'] = 'cate';
        $menu['news'][$i]['name'] = '栏目管理';
        $menu['news'][$i]['url'] = '/Admin/Index/cate';
        $i++;
        $menu['news'][$i]['action'] = 'source';
        $menu['news'][$i]['name'] = '来源管理';
        $menu['news'][$i]['url'] = '/Admin/Index/source';
        $i++;
        $menu['news'][$i]['action'] = 'news';
        $menu['news'][$i]['name'] = '新闻管理';
        $menu['news'][$i]['url'] = '/Admin/Index/news';
        $i++;
        $i++;
        $menu['news'][$i]['action'] = 'news_push';
        $menu['news'][$i]['name'] = '新闻推送';
        $menu['news'][$i]['url'] = '/Admin/Index/news_push';
        $i++;
        $menu['news'][$i]['action'] = 'news_reply';
        $menu['news'][$i]['name'] = '回复评论';
        $menu['news'][$i]['url'] = '/Admin/Index/news_reply';
        $i++;
        $menu['news'][$i]['action'] = 'news_ad';
        $menu['news'][$i]['name'] = '广告管理';
        $menu['news'][$i]['url'] = '/Admin/Index/news_ad';
        $i++;
        
        $menu['sns'][$i]['action'] = 'sns_foram';
        $menu['sns'][$i]['name'] = '版块管理';
        $menu['sns'][$i]['url'] = '/Admin/Index/sns_foram';
        $i++;
        $menu['sns'][$i]['action'] = 'sns_thread';
        $menu['sns'][$i]['name'] = '帖子管理';
        $menu['sns'][$i]['url'] = '/Admin/Index/sns_thread';
        $i++;
        $menu['sns'][$i]['action'] = 'sns_notice';
        $menu['sns'][$i]['name'] = '公告管理';
        $menu['sns'][$i]['url'] = '/Admin/Index/sns_notice';
        $i++;
        $menu['sns'][$i]['action'] = 'sns_reply';
        $menu['sns'][$i]['name'] = '回复管理';
        $menu['sns'][$i]['url'] = '/Admin/Index/sns_reply';
        $i++;
        
        $this -> assign("menu" , $menu);
    }
    
    /**
     * 分页方法
     *
     */
    public function page($cur_page , $sum_count , $page_count , $url){
        $page_num = ceil ($sum_count / $page_count);
        $page_arr = array();
        
        if($page_num <= 5){
            for ($i = 1 ; $i <= $page_num ; $i ++){
                $row['num'] = $i;
                $row['url'] = str_replace('{page}' , $row['num'] , $url);
                $row['active'] = 'no';
                if($row['num'] == $cur_page)
                    $row['active'] = 'yes';
                $page_arr[] = $row;
            }
        }else{
            $begin_page = $cur_page - 2;
            $end_page = $cur_page + 2;
            
            if($cur_page - 2 < 1){
                $begin_page = 1;
            }
            
            if($end_page > $page_num){
                $begin_page = $page_num - 4;
            }
            
            for ($i = 0 ; $i < 5 ; $i ++){
                $row['num'] = $begin_page + $i;
                $row['url'] = str_replace('{page}' , $row['num'] , $url);
                $row['active'] = 'no';
                if($row['num'] == $cur_page)
                    $row['active'] = 'yes';
                $page_arr[] = $row;
            }
        }
        
        $page = array();
        $page['arr'] = $page_arr;
        $page['first']['num'] = 1;
        $page['first']['url'] = str_replace('{page}' , 1 , $url);
        $page['end']['num'] = $page_num;
        $page['end']['url'] = str_replace('{page}' , $page_num , $url);
        
        $this -> assign('page' , $page);
        
    }
}