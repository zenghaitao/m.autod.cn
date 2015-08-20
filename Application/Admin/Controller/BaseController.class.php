<?php
namespace Admin\Controller;
use Admin\Model\AdminModel;

use Think\Controller;

class BaseController extends Controller{
    
    public function __construct(){
        parent::__construct();
        session_start();
        session(array('expire'=>3600*24));
        
        $this -> setMenu();
        
        $this -> assign('user' , $_SESSION['admin_user']);
        
    }
    
    /**
     * 处理用户是否有权限请求
     *
     * @return bool
     */
    protected function checkLogin(){
        if($_SESSION['admin_uid'] < 1){
            $url = "/Admin/Index/login";
            header("Location:{$url}");
            exit;
        }
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
        $menu['index'][$i]['level'] = '1';
        $i++;
        
        $menu['news'][$i]['action'] = 'cate';
        $menu['news'][$i]['name'] = '栏目管理';
        $menu['news'][$i]['url'] = '/Admin/Index/cate';
        $menu['news'][$i]['level'] = '1';
        $i++;
        $menu['news'][$i]['action'] = 'source';
        $menu['news'][$i]['name'] = '来源管理';
        $menu['news'][$i]['url'] = '/Admin/Index/source';
        $menu['news'][$i]['level'] = '1';
        $i++;
        $menu['news'][$i]['action'] = 'catenews';
        $menu['news'][$i]['name'] = '整理分类';
        $menu['news'][$i]['url'] = '/Admin/Index/news?type=cate';
        $menu['news'][$i]['level'] = '1';
        $i++;
        $menu['news'][$i]['action'] = 'choice';
        $menu['news'][$i]['name'] = '新闻列表';
        $menu['news'][$i]['url'] = '/Admin/Index/news?type=choice';
        $menu['news'][$i]['level'] = '1';
        $i++;
        $menu['news'][$i]['action'] = 'story';
        $menu['news'][$i]['name'] = '推荐新闻';
        $menu['news'][$i]['url'] = '/Admin/Index/news?type=story';
        $menu['news'][$i]['level'] = '1';
        $i++;
        $menu['news'][$i]['action'] = 'news_push';
        $menu['news'][$i]['name'] = '新闻推送';
        $menu['news'][$i]['url'] = '/Admin/Index/news_push';
        $menu['news'][$i]['level'] = '1';
        $i++;
        $menu['news'][$i]['action'] = 'news_reply';
        $menu['news'][$i]['name'] = '回复评论';
        $menu['news'][$i]['url'] = '/Admin/Index/news_reply_list';
        $menu['news'][$i]['level'] = '1';
        $i++;
        
        $menu['api'][$i]['action'] = 'api_keyword';
        $menu['api'][$i]['name'] = '产品库数据管理';
        $menu['api'][$i]['url'] = '/Admin/Index/api_keyword';
        $menu['api'][$i]['level'] = '2';
        $i++;
        
        $menu['news'][$i]['action'] = 'news_ad';
        $menu['news'][$i]['name'] = '广告管理';
        $menu['news'][$i]['url'] = '/Admin/Index/news_ad';
        $menu['news'][$i]['level'] = '1';
        $i++;
        
        $menu['sns'][$i]['action'] = 'sns_foram';
        $menu['sns'][$i]['name'] = '版块管理';
        $menu['sns'][$i]['url'] = '/Admin/Index/sns_foram';
        $menu['sns'][$i]['level'] = '1';
        $i++;
        $menu['sns'][$i]['action'] = 'sns_thread';
        $menu['sns'][$i]['name'] = '帖子管理';
        $menu['sns'][$i]['url'] = '/Admin/Index/sns_thread';
        $menu['sns'][$i]['level'] = '1';
        $i++;
        $menu['sns'][$i]['action'] = 'sns_notice';
        $menu['sns'][$i]['name'] = '公告管理';
        $menu['sns'][$i]['url'] = '/Admin/Index/sns_notice';
        $menu['sns'][$i]['level'] = '1';
        $i++;
        $menu['news'][$i]['action'] = 'sns_reply';
        $menu['news'][$i]['name'] = '回复管理';
        $menu['news'][$i]['url'] = '/Admin/Index/sns_reply';
        $menu['news'][$i]['level'] = '1';
        $i++;
        $menu['user'][$i]['action'] = 'admin_user';
        $menu['user'][$i]['name'] = '用户管理';
        $menu['user'][$i]['url'] = '/Admin/Index/admin_user';
        $menu['user'][$i]['level'] = '2';
        $i++;
        
        
        
        $this -> assign("menu" , $this -> assignByGroupId($menu));
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
    
    /**
     * 
     * @param array $data 要返回的数据
     * @author nj 2015-7-30 
     * @return string json数据
     */
    public function baseAjaxReturn($data){
    	$this -> ajaxReturn($data);
    }
    
    /**
     * 根据权限分配栏目
     * @author nj 2015-7-31
     */
    public function assignByGroupId($menu){
    	$actionarr = array();
    	$adminuser = new AdminModel();
    	$actions = $adminuser -> _group_action[$_SESSION['admin_user']['groupid']];
  		
    	foreach ( $menu as $key => $val ) {
    		foreach( $val as $k => $v ) {
    			if( $_SESSION['admin_user']['groupid'] >= $v['level'] ) {
    				$actionarr[$key][$k] = $v; 
    			}
    		}
    	}

    	return $actionarr;
    }
}