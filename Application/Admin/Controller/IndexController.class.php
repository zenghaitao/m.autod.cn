<?php
namespace Admin\Controller;

use Admin\Model\ProApiModel;
use Admin\Model\CarModel;
use API\Model\NewsModel;
use API\Model\StoryModel;
use API\Model\SnsModel;
use API\Model\UserModel;
use API\Model\QiniuModel;
use Admin\Model\SnatchModel;
use Admin\Model\AdminModel;
use Admin\Model\RecordLogModel;
use Think\Upload\Driver\Qiniu\QiniuStorage;

set_time_limit(0);

class IndexController extends BaseController  {
    
    public function __construct(){
        parent::__construct();
		
        $RecordLog = new RecordLogModel();
		$RecordLog -> recordLog($_SESSION['admin_uid'],$_SERVER['PATH_INFO'],$_REQUEST);
		
        //是否为合法访问(调用接口不用登陆)
        if(!in_array($_SERVER['PATH_INFO'] , array('Index/login','Index/logout','Index/syncData'))){
            $this -> checkLogin();
        }
    }
    
    public function login(){
        if($_POST){
            $name = $_POST['name'];
            $pwd = $_POST['pwd'];
            
            $M_admin = new AdminModel();
            
            if($M_admin -> login($name , $pwd)){
                //登录成功
                if($_POST['remenber'] == 'yes'){
                    cookie('admin_name',$name,3600*24*30);
                }
                $url = "/Admin/";
				
                header("Location:{$url}");
                exit;
            }else{
                $msg = '登录失败，请重试！';
                $url = "/Admin/Index/login?msg=".urlencode($msg);

                header("Location:{$url}");
                exit;
            }
            
        }
        
        $this -> assign('name' , $_COOKIE['admin_name']);
        $this -> assign('msg' , $_GET['msg']);
        
        $this -> display('login');
    }
    
    public function logout(){
        unset($_SESSION['admin_uid']);
        unset($_SESSION['admin_user']);
        
        $url = "/Admin/Index/login";
        header("Location:{$url}");
        exit;
    }
    
    public function frame(){
        
        $this -> assign('news_id' , $_GET['id']);
        
        $this -> display('frame');
    }
    /**
     * 控制台
     *
     */
    public function index(){
        $this -> assign("action" , 'index');
        $this -> display('main');
    }
    
    /**
     * 分类管理功能
     *
     */
    public function cate(){
        
        $M_news = new NewsModel();
        $list = $M_news -> getCateList();

        $this -> assign('list' , $list);
        $this -> assign("action" , 'cate');
        $this -> display('cate');
    }
    
    /**
     * 来源管理功能
     *
     */
    public function source(){
        $M_news = new NewsModel();
        $list = $M_news -> getSoureList();
        
        $this -> assign('list' , $list);
        $this -> assign("action" , 'source');
        $this -> display('source');
    }
    
    /**
     * 新闻管理功能
     *
     */
    public function news(){
        $M_story = new StoryModel();
        $M_news = new NewsModel();
        
        $type = $_GET['type'];
        if(!$type)
            $type = 'story';
        
        $page = (int)$_GET['page'];
        if($page < 1)
            $page = 1;
        $page_count = 50;
        
        if( isset($_GET['cateid']) && !empty($_GET['cateid']) ) {
        	$cateid = $_GET['cateid'];
        }else{
        	$cateid = 0;
        }
        
    	if( isset($_GET['sourceid']) && !empty($_GET['sourceid']) ) {
        	$sourceid = $_GET['sourceid'];
        }else{
        	$sourceid = '';
        }
        
        if($type == 'story'){       
            $title = "待处理";
            $result = $M_story -> storyAdminList('no' , $page , $page_count);
            $this -> page($page , $result['count'] , $page_count , "/Admin/Index/news?type=story&page={page}");
            
            /* 获取分类列表 */
            $cate = $M_news -> getCateList();
            $this -> assign("catelist" , $cate);
            
            $this -> assign('list' , $result['list']);
            $this -> assign("action" , 'story');
        }
        
        if($type == 'choice'){        
            $title = "新闻列表";
            $result = $M_news -> newsList($cateid , $page , $page_count );
            $this -> page($page , $result['count'] , $page_count , "/Admin/Index/news?type=choice&page={page}&cateid={$cateid}");
            /* 获取分类列表 */
            $cate = $M_news -> getCateList();
            foreach($cate as $row){
            	$n_cate[$row['id']] = $row['name'];
            }
            $cate = $n_cate;
            
            foreach ($result['list'] as &$row){
                $images = explode(';,;' , $row['images']);
                $row['title_pic1'] = $images[0];
                $row['title_pic2'] = $images[1];
                $row['title_pic3'] = $images[2];
                
                $row['column_id'] = $row['cate_id'];

                $row['colunm_catename'] = empty($cate[$row['cate_id']]) ? '未分类' : $cate[$row['cate_id']];
                
            }

            $this -> assign("catelist" , $n_cate);
            $this -> assign('list' , $result['list']);
            $this -> assign("action" , 'choice');
        }

        if($type == 'cate'){        
            $title = "未分类新闻";
            $result = $M_story -> noCateNewsList( $page , $page_count);
            $this -> page($page , $result['count'] , $page_count , "/Admin/Index/news?type=cate&page={page}");
            
            $this -> assign('list' , $result['list']);
            $this -> assign("action" , 'catenews');
            
            /* 获取分类列表 */
            $cate = $M_news -> getCateList();
            $this -> assign("catelist" , $cate);
        }
        
        if( $type == 'source' ) {
        	$title = "新闻列表";
        	
        	$result = $M_news -> getNewsBySourceId($sourceid , $page);
            /* 获取分类列表 */
            $cate = $M_news -> getCateList();
            foreach($cate as $row){
            	$n_cate[$row['id']] = $row['name'];
            }
            $cate = $n_cate;
            
            foreach ($result as &$row){
                $images = explode(';,;' , $row['images']);
                $row['title_pic1'] = $images[0];
                $row['title_pic2'] = $images[1];
                $row['title_pic3'] = $images[2];
                
                $row['column_id'] = $row['cate_id'];

                $row['colunm_catename'] = empty($cate[$row['cate_id']]) ? '未分类' : $cate[$row['cate_id']];
                
            }
        	
            $this -> assign('title',$title);
            $this -> assign('sourcename', $result['0']['source']);
            $this -> assign("catelist" , $n_cate);
        	$this -> assign('list',$result);
        	$this -> display('news_source');
        	exit;
        }
        

        $this -> assign('title' , $title);
        $this -> display('news');
    }
    
    public function saveStoryCate(){
        $M_story = new StoryModel();
        $M_news = new NewsModel();
        
        $story_id = $_POST['story_id'];
        $cate_id = $_POST['cate_id'];
        
        $data['column_id'] = $cate_id;
        $result = $M_story -> updateStory($story_id , $data);
        
        if($result !== false){
            $data['cate_id'] = $cate_id;
            $M_news -> updateStory($story_id , $data);
            die('succ');
        }
        die('fail');
    }
    
    /**
     * 新闻管理功能
     *
     */
    public function news_push(){
        $M_news = new NewsModel();
        
        $this -> display('news_push');
    }
    
    /**
     * 社区版块管理
     *
     */
    public function sns_foram(){
        $M_sns = new SnsModel();
        $list = $M_sns -> foramList();
        
        $this -> assign('list' , $list);
        $this -> assign("action" , 'sns_foram');
        $this -> display('sns_foram');
    }
    
    /**
     * 社区帖子管理
     *
     */
    public function sns_thread(){
        $page = (int)$_GET['page'];
        if($page < 1)
            $page = 1;
            
        $page_count = 50;
            
        $M_sns = new SnsModel();
        $list = $M_sns -> threadList( 0  , $page , $page_count );
        
        $count = $M_sns -> threadCount();
        
        $this -> page($page , $count , $page_count , "/Admin/Index/sns_thread?page={page}");
        
        $this -> assign('list' , $list);
        $this -> assign("action" , 'sns_thread');
        $this -> display('sns_thread');
    }
    
    private function format_thread($thread){
        $data = $thread;
        
        return $data;
    }
    
    public function sns_thread_status(){
        
        $M_user = new UserModel();
        $M_sns = new SnsModel();
        
        if($_POST){ 
            $user = $M_user -> getUserInfo($_POST['uid']);
            
            $data['uid'] = $_POST['uid'];
            $data['images'] = $_POST['pics'];
            $data['contents'] = $_POST['contents'];
            $data['foram_id'] = 1;
            $data['username'] = $user['name'];
            $data['userphoto'] = $user['photo'];
            $data['add_time'] = date('Y-m-d H:i:s');
            
            $id = $M_sns -> addThreadStatus($data);
            
            $info  = $M_sns -> thread($id);
            
            exit;
        }
        
        $root_list = $M_user -> getRoot();
        
        $this -> assign("root_list" , $root_list);
        $this -> assign("action" , 'sns_thread');
        $this -> display('sns_thread_status');
    }
    
    /**
     * 七牛上传token
     *
     */
    public function qiniuToken(){
       
        $QN_config = C('UPLOAD_SITEIMG_QINIU');
        //var_dump($QN_config);exit;
        
        $qiniu = new QiniuStorage($QN_config['driverConfig']);
        $token = $qiniu -> UploadToken($QN_config['driverConfig']['secrectKey'] , $QN_config['driverConfig']['accessKey'] ,$param);
        echo json_encode(array('uptoken'=>$token));
        //echo json_encode(array('uptoken'=>'0MLvWPnyya1WtPnXFy9KLyGHyFPNdZceomLVk0c9:gsa0agNkLsn-ChFV2-erE51qs6k=:eyJzY29wZSI6InFpbml1LXBsdXBsb2FkIiwiZGVhZGxpbmUiOjE0MzU2NDY0NTJ9'));
    }
    
    public function qiniuLog(){
        $domain = $_POST['domain'];
        $key = $_POST['key'];
        
        $M_qiniu = new QiniuModel();
        $M_qiniu -> add($domain , $key);
        
    }
    
    /**
     * 修改用户密码
     * @author nj 2015-7-29 
     */
    public function editPwd(){
		if( $_POST ) {
			$data = array();
			
			if( $_POST['oldpwd'] || $_POST['newpwd'] || $_POST['secondpwd'] ) {
				$oldpwd = trim($_POST['oldpwd']);
				$newpwd = trim($_POST['newpwd']);
				$secondpwd = trim($_POST['secondpwd']);
			
				if ( empty($oldpwd) ){
					$data['status'] ='error';
					$data['message'] = '旧密码不能为空';
				}elseif ( md5($oldpwd) != $_SESSION['admin_user']['pwd'] ){
					$data['status'] ='error';
					$data['message'] = '旧密码错误';
				}elseif ( empty($newpwd) ){
					$data['status'] ='error';
					$data['message'] = '新密码不能为空';
				}elseif ( $oldpwd == $newpwd ){
					$data['status'] ='error';
					$data['message'] = '新密码不能和旧密码一致';
				}elseif ( empty($secondpwd) ){
					$data['status'] ='error';
					$data['message'] = '再次输入新密码不能为空';
				}elseif ( $newpwd != $secondpwd ) {
					$data['status'] ='error';
					$data['message'] = '新密码和再次输入新密码不一致';
				}
				
				if( $_POST['action'] == 'updatepwd' && !isset($data['message']) ){
					$adminuser =  new AdminModel();
					$res = $adminuser -> updatepwd($_SESSION['admin_uid'],$newpwd);
					if( res ){
						$loginres = $adminuser -> login($_SESSION['admin_user']['email'],$newpwd);
						if( $loginres ) {
							$data['status'] = 'success';
							$data['message'] = '修改密码成功';
						}else{
							$data['status'] = 'error';
							$data['message'] = 'session数据更新失败,需重新登录';
						}
						
					}else{
						$data['status'] ='error';
						$data['message'] = '修改密码失败';
					}
					
				}
			}elseif( empty($_POST['oldpwd']) && empty($_POST['newpwd']) && empty($_POST['secondpwd']) ) {
				$data['status'] ='error';
				$data['message'] = '表单不能为空';
			}

			$this -> baseAjaxReturn($data);
			exit;
		} 
		
    	$this -> assign("username" , $_SESSION['admin_user']['name']);
    	$this -> display();
    }
    
    /**
     * 用户管理
     * @author nj 2015-7-30
     */
    public function admin_user(){
    	$adminuser =  new AdminModel();
    	
    	$this -> assign( 'list', $adminuser -> getAllUser() );
    	$this -> assign( "group_config", $adminuser -> _group_config );
    	$this -> assign("action" , 'admin_user');
    	$this -> display();
    }
    
   /**
    * 添加用户
    * @author nj 2015-7-30
    */
    public function admin_user_add(){
    	$adminuser =  new AdminModel();
    	
    	$data = array();
    	
    	if( $_POST ) {
    		if( $_POST['name'] || $_POST['email'] || $_POST['pwd'] || $_POST['secpwd'] || $_POST['groupid'] ){
	    		$name = trim($_POST['name']);
	    		$email = trim($_POST['email']);
	    		$pwd = trim($_POST['pwd']);
	    		$secpwd = trim($_POST['secpwd']);
	    		$groupid = trim($_POST['groupid']);	    		
	    		
	    		if( empty($name) ) {
	    			$data['status'] = 'error';
	    			$data['message'] = '用户名不能为空';		
	    		}elseif( $adminuser->getUserInfoByname($name) ){
	    			$data['status'] = 'error';
	    			$data['message'] = '用户名已存在';
	    		}elseif ( empty($email) ) {
	    			$data['status'] = 'error';
	    			$data['message'] = '邮箱不能为空';
	    		}elseif ( !preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*.\w+([-.]\w+)*/',$email) ) {
	    			$data['status'] = 'error';
	    			$data['message'] = '邮箱格式不正确';
	    		}elseif( $adminuser->getUserInfoByemail($email) ){
	    			$data['status'] = 'error';
	    			$data['message'] = '邮箱已存在';
	    		}elseif ( empty($pwd) ) {
	    			$data['status'] = 'error';
	    			$data['message'] = '密码不能为空';
	    		}elseif ( empty($secpwd) ) {
	    			$data['status'] = 'error';
	    			$data['message'] = '再次输入密码不能为空';
	    		}elseif ( $groupid == '0' ) {
	    			$data['status'] = 'error';
	    			$data['message'] = '请选择组';
	    		}elseif ( $pwd != $secpwd ) {
	    			$data['status'] = 'error';
	    			$data['message'] = '两次密码输入不一致';
	    		}
	    		
	    		if( !isset($data['message']) && $_POST['action'] == 'adduser' ) {
		    		
		    		$res = $adminuser -> addUser($email,$name,$pwd,$groupid);
		    		
		    		if($res){
		    			$data['status'] = 'success';
		    			$data['message'] = '添加成功';
		    		}else{
		    			$data['status'] = 'error';
		    			$data['message'] = '添加失败';
		    		}
		    	
	    		}
    		}elseif ( empty($_POST['name']) && empty($_POST['email']) && empty($_POST['pwd']) && empty($_POST['secpwd']) && empty($_POST['groupid']) ) {
    			$data['status'] = 'error';
    			$data['message'] = '表单不能为空';
    		}

    		
    		$this -> baseAjaxReturn($data);
    		exit;
    	}
    	
    	$this -> assign( "group_config", $adminuser -> _group_config );
    	$this -> display();
    }
    
    /**
     * ajax删除用户
     * @author nj 2015-7-30
     */
    public function admin_user_delete(){
    	$data = array();
    	
    	if( $_POST['action'] == 'admin_user_delete' && !empty($_POST['id']) && !empty($_POST['groupid'])) {
    		$id = trim($_POST['id']);
    		$groupid = trim($_POST['groupid']);
    		
    		if( $id == $_SESSION['admin_uid'] ) {
    			$data = array('status' => 'error', 'message' => '不能删除自己');
    		}elseif( $groupid == '2' ) {
    			$data = array('status' => 'error', 'message' => '不能删除超级管理员');
    		}else{
	    		$adminuser =  new AdminModel();
	    		$res = $adminuser -> deleteById($id);
	    		if( $res ) 
	    			$data = array('status' => 'success', 'message' => '删除成功');
	    		else
	    			$data = array('status' => 'error', 'message' => '删除失败');    	
    		}
    	}else{
    		$data = array('status' => 'error', 'message' => '用户id不能为空');
    	}
    	
    	
    	$this -> baseAjaxReturn($data);
    	exit;
    }
    
    /**
     * ajax更新用户组
     * @author nj 2015-7-30
     */
    public function admin_user_update_group(){
    	$data = array();
    	
    	if( $_POST['action'] == 'admin_user_update_group' && !empty($_POST['id']) && !empty($_POST['groupid']) ) {
    		$id = trim($_POST['id']);
    		$groupid = trim($_POST['groupid']);
    		$adminuser =  new AdminModel();
    		$res = $adminuser -> updateGroupById($id,$groupid);
    		if( $res ){
    			if($id == $_SESSION['admin_uid']){
    				$data = array('status' => 'success', 'message' => '更新成功需重新登录');
    			}else{
    				$data = array('status' => 'success', 'message' => '');
    			}
    		}else{
    			$data = array('status' => 'error', 'message' => '更新失败');
    		}
    		
    	}else{
    		$data = array('status' => 'error', 'message' => '用户id和组id不能为空');
    	}


    	$this -> baseAjaxReturn($data);
    	exit;
    }
    
    /**
     * ajax删除新闻
     * @author nj 2015-7-31
     */
    public function deleteNews(){
    	$data = array();
    	
    	if( $_POST['action'] == 'deleteNews' && !empty($_POST['id']) ) {
    		$id = trim($_POST['id']);
    		
    		$news = new NewsModel();

    		$res = $news -> deleteNewsById($id);
    		
    		if( $res ) {
    			$data = array('status' => 'success','message' => '删除成功');
    		}else{
    			$data = array('status' => 'error','message' => '删除失败');
    		}
    		
    	}else{
    		$data = array('status' => 'error','message' => '参数不能为空');
    	}
    	
    	$this -> baseAjaxReturn($data);
    	exit;
    }
    
    /**
     * 删除runtime缓存
     */
    public function delete_runtime_cache(){
    	$this -> rm_dir_p(RUNTIME_PATH);
    	$this -> success('删除成功');
    }
    
    /**
     * 删除文件
     * @param string $path  要删除的文件
     */
    private function rm_dir_p($path){
    	$list = scandir ($path);
    	foreach ($list as $row){
    		if($row == '.' || $row == '..'){
    			continue;
    		}
    
    		if(is_file($path . '/' . $row)){
    			@unlink($path . '/' . $row);
    		}
    		if(is_dir($path . '/' . $row)){
    			$this -> rm_dir_p($path.'/'.$row);
    		}
    	}
    }
    
    /**
     * 通過sourceid獲取新聞信息
     */
    public function getNewsBySourceId(){
    	$sourceId = $_GET['sourceId'];
    	$page = $_GET['page'];
    	$M_news = new NewsModel();
    	
    	$result = $M_news -> getNewsBySourceId($sourceId , $page);
    	
    	/* 获取分类列表 */
    	$cate = $M_news -> getCateList();
    	foreach($cate as $row){
    		$n_cate[$row['id']] = $row['name'];
    	}
    	$cate = $n_cate;
    	
    	foreach ($result as &$row){
    		$images = explode(';,;' , $row['images']);
    		$row['title_pic1'] = $images[0];
    		$row['title_pic2'] = $images[1];
    		$row['title_pic3'] = $images[2];
    	
    		$row['column_id'] = $row['cate_id'];
    	
    		$row['colunm_catename'] = empty($cate[$row['cate_id']]) ? '未分类' : $cate[$row['cate_id']];
    		$row['caozuo']['0'] = '刪除';
    		$row['caozuo']['1'] = '评论';
    		$row['caozuo']['2'] = '查看评论';
    	}
    	
    	$this -> assign('list' , $result);
    	$this -> display('get_news_source');
    }
    
    /**
     * 添加新闻
     */
    public function addNews(){
    	$story = new StoryModel();
    	$snatch = new SnatchModel();

    	if( $_POST ) {
    		$url = trim($_POST['url']);
    		
    		//抓取新闻
    		if( $_POST['action'] == 'fetchNews' ) {
    				$res = $snatch -> parsePage($url);
    				
    				if( $res ) {
    					if( empty($res['content']) || empty($res['images']) ) {
    						$data = array('status'=>'error','message'=>'抓取失败');
    					}else{
    						$res['source'] = '未分配来源';

    						$res['images_str'] = implode(';,;', $res['images']);
    						
    						$this->assign('list',$res);
    						$html = $this->fetch('get_news_story');
    						$data = array('status'=>'success', 'data'=>$html);
    					}
    				}else{
		    			$data = array('status'=>'error','message'=>'未抓取到数据，请确认url输入正确');
	    			}
	    			
	    			$this -> baseAjaxReturn($data);
    				exit;
    		}
    		
    		//插入数据库
    		if( $_POST['action'] == 'addNews' ) {
    			
    			if( empty($_POST['column_id']) ) {
    				$data = array('status'=>'error','message'=>'请选择分类');
    			}elseif ( empty($_POST['source_id']) ) {
    				$data = array('status'=>'error','message'=>'请选择来源');
    			}elseif( empty($_POST['article_id']) ) {
    				$data = array('status'=>'error','message'=>'文章id不能为空');
    			}else{
    				$res = $story -> getInfoByArticleId($_POST['article_id']);
    				
    				if($res) {
    					$data = 	array('status'=>'error','message'=>'此新闻已添加过，不要重复添加');
    				}else{
    					$story_id = $story -> addStory($_POST);
    						
    					if( $story_id ) {
    						$_POST['story_id'] = $story_id;
    						$res = $story -> addStoryContent($_POST);
    						if( $res ) {
    							$res = $story -> addSpiderPage($_POST);
    								
    							if( $res ) {
    								$data = array('status'=>'success','message'=>'添加成功');
    							}else{
    								$data = array('status'=>'error','message'=>'插入spiderpage失败');
    							}
    						}else{
    							$data = array('status'=>'error','message'=>'插入storycontent失败');
    						}
    					}else{
    						$data = array('status'=>'error','message'=>'插入stroy失败');
    					}
    			  	}
    		 	}

	    		$this -> baseAjaxReturn($data);	
    			exit;
    		}
    	}

    	$news = new NewsModel();
    	
    	//获取手动抓取得来源
    	$source_list = $news -> getSoureListByIsAuto('2');
    	
    	//获取所有分类
    	$cate_list = $news -> getCateList();
    	
    	$title = '添加新闻';
    	$this -> assign('cate_list', $cate_list);
    	$this -> assign('source_list', $source_list);
    	$this -> assign('title', $title);
    	$this -> display('news_add');
    }
    
    /**
     * 评论列表
     */
    public function news_reply_list(){
    	$news = new NewsModel();
    	
    	$page = (int)$_GET['page'];
    	if($page < 1)
    		$page = 1;
    	$page_count = 50;
    	
    	$is_robot = 0;
    	if( isset($_GET['is_robot']) ) {
    		$is_robot = $_GET['is_robot'];
    	}
    	
    	if( $is_robot == 1 ) {
    		$title = '机器人评论列表';
    	}elseif ( $is_robot == 0 ) {
    		$title = '用户评论列表';
    	}
    	
    	$news_id = 0;
    	if( isset($_GET['news_id']) ) {
    		$news_id = $_GET['news_id'];
    		$title = '新闻评论列表';
    	}
    	
    	$uid = 0;
    	if( isset($_GET['uid']) ) {
    		$uid = $_GET['uid'];
    		$title = '用户评论列表';
    	}
    	
    	$comments = $news -> getAllCommnets($is_robot, $page, $page_count,$news_id,$uid);
    	
    	
    	$this -> page($page, $comments['count'], $page_count, "/Admin/Index/news_reply_list?is_robot=$is_robot&page={page}&news_id=$news_id&uid=$uid");
    	$this -> assign('title',$title);
		$this -> assign('replyCount',$comments['replyCount']);
    	$this -> assign('list',$comments['res']);
    	$this -> display();
    }
    
    /**
     * 回复评论
     */
    public function news_reply_comment(){
    	$title = "回复评论";
    	$user = new UserModel();
    	$news = new NewsModel();
    	
    	$reply_id = 0;
    	if( isset($_GET['reply_id']) ) {
    		$reply_id = $_GET['reply_id'];
    	}
    	
    	if( $_POST ) {
    		if( empty($_POST['post']) ) {
    			$data = array('status'=>'error','message'=>'评论不能为空');
    		}elseif ( empty($_POST['user_id']) ) {
    			$data = array('status'=>'error','message'=>'请选择回复账号');
    		}elseif ( empty($_POST['news_id']) ) {
    			$data = array('status'=>'error','message'=>'新闻id不能为空');
    		}elseif ( empty($_POST['reply_id']) ){
    			$data = array('status'=>'error','message'=>'reply_id不能为空');
    		}elseif( $_POST['user_id'] == $_POST['reply_uid'] ){
    			$data = array('status'=>'error','message'=>'回复账号和评论账号不能是同一账号');
    		}else{
    			$post = $_POST['post'];
    			$user_id = $_POST['user_id'];
    			$reply_id = $_POST['reply_id'];
    			$news_id = $_POST['news_id'];
    			 
    			$res = $news -> comments($news_id, $post, $user_id, $reply_id);
    			 
    			if( $res ) {
    				$data = array('status'=>'success','message'=>'回复评论成功');
    			}else{
    				$data = array('status'=>'error','message'=>'回复评论失败');
    			}
    		}
    	
    		$this -> baseAjaxReturn($data);
    		exit;
    	}
    	
    	$commentInfo = $news -> getCommentInfo($reply_id);
    	$robots = $user -> getUserByIsRobot('yes');
    	
    	$this -> assign('commentInfo',$commentInfo);
    	$this -> assign('robots',$robots);
    	$this -> assign('title',$title);
    	$this -> display();
    }
    
    /**
     * 评论新闻
     */
    public function news_reply(){
    	$title = "评论新闻";
    	$user = new UserModel();
    	
    	if( $_POST ) {
    		if( empty($_POST['post']) ) {
    			$data = array('status'=>'error','message'=>'评论不能为空');
    		}elseif ( empty($_POST['user_id']) ) {
    			$data = array('status'=>'error','message'=>'请选择评论账号');
    		}elseif (empty($_POST['news_id'])) {
    			$data = array('status'=>'error','message'=>'新闻id不能为空');
    		}else{
    			$post = $_POST['post'];
    			$user_id = $_POST['user_id'];
    			$news_id = $_POST['news_id'];
    			
    			
    			$news = new NewsModel();
    			$res = $news -> comments($news_id, $post, $user_id);
    			
    			if( $res ) {
    				$data = array('status'=>'success','message'=>'评论成功');
    			}else{
    				$data = array('status'=>'error','message'=>'评论失败');
    			}
    		}
    		
    		$this -> baseAjaxReturn($data);
    		exit;
    	}
    	
    	$robots = $user -> getUserByIsRobot('yes');
		
    	$this -> assign('robots',$robots);
    	$this -> assign('title',$title);
    	$this -> display();
    }
    
    /**
     * 删除评论
     */
    public function news_comment_delete(){
    	if( $_POST ) {
    		if( empty($_POST['comment_id']) ) {
    			$data = array('status'=>'error','message'=>'comment_id不能为空');
    		}elseif( empty($_POST['uid']) ){
    			$data = array('status'=>'error','message'=>'uid不能为空');
    		}else{
    			$comment_id = $_POST['comment_id'];
    			$uid = $_POST['uid'];
    			
    			$news = new NewsModel();
    			$res = $news -> commentsDel($comment_id , $uid);
    			
    			if( $res ) {
    				$data = array('status'=>'success','message'=>'删除成功');
    			}else{
    				$data = array('status'=>'error','message'=>'删除失败');
    			}
    		}
    	}else{
    		$data = array('status'=>'error','message'=>'数据不能为空');
    	}
    	
    	$this -> baseAjaxReturn($data);
    	exit;
    }
    
    /**
     * 上推新闻
     */
    public function newsUpStory(){
    	if( $_POST && $_POST['action'] == 'newsUpStory' ) {
    		
    		if( empty( $_POST['id'] ) ) {
    			$data = array('status'=>'error','message'=>'新闻id不能为空');
    		}else{
    			$id = $_POST['id'];
    			$arr['is_choice'] = 'yes';
    			$M_news = new NewsModel();
    			$M_story = new StoryModel();
					    			
    			if( $_POST['cate_id'] != '0' ) {
    				$arr['column_id'] = $_POST['cate_id'];
    			}
    			
    			$res = $M_story -> updateStory($id, $arr);
    			
    			if( !$res ) {
    				$data = array('status'=>'error','message'=>'新闻分类更新失败');
    			}
    			
    			$res = $M_news -> addNewsToChoice($id);
    			if( $res && $res != 'no pic' ) {
    				$data = array('status'=>'success','message'=>'上推成功');
    			}elseif( $res == 'no pic' ){ 
    				$data = array('status'=>'error','message'=>'上推失败。原因:' . $res);
    			}else{
    				$data = array('status'=>'error','message'=>'上推失败');
    			}
    		}
    		
    		$this -> baseAjaxReturn($data);
    		exit;
    	}
    }
    
    /**
     * 导入地区数据
     */
    public function importArea(){
    	$api = new ProApiModel();
    	$car = new CarModel();
    	$res = $api -> getArea();
		
    	$i = 0;
    	$j = 0;
    	
    	if( isset($res['item']) && $res['item'] ) {
    		foreach( $res['item'] as $v ) {
    			$res = $car -> insertAreaData($v);
    			if( $res ) {
    				$i++;
    			}else{
    				$j++; 
    			}
    		}
    	}else{
    		$message = "数据抓取失败";
    		echo $message . '<p>';
    	}
    	
    	echo $i . '条数据插入成功' . $j . '条数据插入失败';
		
    	exit;
    }
    
    /**
     * 导入品牌数据
     */
    public function importBrand(){
    	set_time_limit(0);
    	ini_set('memory_limit', '1024M');
    	
    	$api = new ProApiModel();
    	$car = new CarModel();
    	
    	//抓取品牌数据
    	$res = $api -> getSign();
    	$i = 0;
    	$j = 0;
    	
    	if( isset($res['item']) && $res['item'] ) {

    			$str = '';
    			foreach ( $res['item'] as $key => $val ) {
    				if( !$car -> getOneSignById($val['id']) ) {
    					$resu = $car -> insertBrandData($val);
    					if( $resu ) {
    						$i++;
    					}else{
    						$j++;
    					}
    				}else{
    					echo $val['id'] . '已入库';
    				}
    				
    			}
  		
    	}else{
    		$message = "品牌数据抓取失败";
    	} 

    	echo $i . '条数据插入成功' . $j . '条数据插入失败' . $str;
    	exit;
    }

    /**
     * 导入厂商数据
     */
    public function importFactory(){
    	$api = new ProApiModel();
    	$car = new CarModel();
    	 
    	//抓取厂商数据
    	$res = $api -> getBrand();
    	$i = 0;
    	$j = 0;
    	 
    	if( isset($res['item']) && $res['item'] ) {

    			foreach ( $res['item'] as $key => $val ) {
    				$str = '';
    				if( !$car->getOneFactoryById($val['id']) ) {
    					$res = $car -> insertFactoryData($val);
    					if( $res ) {
    						$i++;
    					}else{
    						$j++;
    					}
    				}else{
    					echo $val['id'] . '已入库';
    				}
    				
    			}

    	}else{
    		$message = "厂商数据抓取失败";
    	}
    
    	echo $i . '条数据插入成功' . $j . '条数据插入失败' . $str;
    	exit;
    }

    /**
     * 导入车系数据
     */
    public function importBseries(){
    	$api = new ProApiModel();
    	$car = new CarModel();
    
    	//抓取车系数据
    	$res = $api -> getBseries();
    	$type="bseriesReferencePrice";
    	$i = 0;
    	$j = 0;
    
    	if( isset($res['item']) && $res['item'] ) {

    			foreach ( $res['item'] as $key => $val ) {
    				$str = '';
    				$res = $car -> insertBseriesData($val);
    				
    				if( $res ) {
    					$price = $api -> getOne($type, $val['id']);
    					
    					if( !(empty($price['item']['minPrice']) && empty($price['item']['maxPrice'])) ) {
    						$res1 = $car -> updateBseries($price['item']);
    						if( !$res1 ) {
    							echo $val['id'] . "更新厂家报价失败";
    						}
    					}
    					
    					$i++;
    				}else{
    					echo $val['id'] . '入库失败';
    				}
    			}

    	}else{
    		$message = "车系数据抓取失败";
    	}
    
    	echo $i . '条数据插入成功' . $str;
    	exit;
    }    
	
    /**
     * 导入车型数据
     */
    public function importModel(){
    	$api = new ProApiModel();
    	$car = new CarModel();
    
    	//抓取车型数据
    	$res = $api -> getModel();
    	$i = 0;
    	$j = 0;
    	$type = "modelReferencePrice";

    	if( isset($res['item']) && $res['item'] ) {

    			foreach ( $res['item'] as $key => $val ) {
    				$str = '';
    				
    				if( !$car -> getOneModelById($val['id']) ) {
    					$res = $car -> insertModelData($val);
    					if( $res ) {
    						$i++;
    					}else{
    						$j++;
    					}
    				}else{
    					$str .= $val['id'] . '已入库';
    				}
    			}

    	}else{
    		$message = "车型数据抓取失败";
    	}
    
    	echo $message . $i . '条数据插入成功' . $j . '条数据插入失败' . $str;
    	exit;
    }    
	
    /**
     * 更新车型商家报价价格
     */
    public function updateModelPrice(){
    	$car = new CarModel();
    	$api = new ProApiModel();
    	$type = "modelReferencePrice";
    	$page = (int)$_GET['page'];
    	
    	$res = $car -> getModelsByPage($page);

    	if( !$res['res'] ) {
    		echo 'success';
    		exit;
    	}
    	
    	foreach( $res['res'] as $k => $val ) {
    
    		$price = $api -> getOne($type, $val['id']);
			
    		if( empty($price['item']['minPrice']) && empty($price['item']['maxPrice']) ) {
    			echo $val['id'] . '价格都为0' . '<p>';
    		}else{
    			$res1 = $car -> updateModel($price['item']);
    			if( !$res1 ) {
    				echo $val['id'] . "更新厂家报价失败(可能数据一致不更新)" . $price['item']['minPrice'] . '--' . $price['item']['maxPrice'] . '<p>';
    			}else{
    				echo $val['id'] . '更新报价成功' . '<p>';
    			}
    		}
    	}
    	
    	$nextpage = $res['page'];
    	echo '<script>window.location.href="/Admin/Index/updateModelPrice?page=' .$nextpage. '"</script>';
    	exit;
    }
    
    /**
     * 导入参数数据
     */
    public function importParam(){
    	$api = new ProApiModel();
    	$car = new CarModel();
    
    	//抓取品牌数据
    	$res = $api -> getParams();
    	$i = 0;
    	$j = 0;
    
    	if( isset($res['item']) && $res['item'] ) {
    		foreach ( $res as $v ) {
    			foreach ( $v as $key => $val ) {
    				$res = $car -> insertParamsData($val);
    				if( $res ) {
    					$i++;
    				}else{
    					$j++;
    				}
    			}
    		}
    	}else{
    		$message = "参数数据抓取失败";
    	}
    
    	echo $i . '条数据插入成功' . $j . '条数据插入失败';
    	exit;
    }
    
    /**
     * 图片数据导入
     */
    public function importImg(){
    	$id = (int)$_GET['id'];
    	$api = new ProApiModel();
    	$car = new CarModel();
    	
    	$nextId = $car -> getNextBseriesId($id);
    	
    	if( !$nextId ) {
    		echo 'success';
    		exit;
    	}
    	
    	$str = '';
    	$res = $api -> getOne('photoByBseries', $nextId);
    	
    	if( !$res['item'] ) {
    		echo $str = $nextId . '车系没有数据';
    	}else{
	    	$i = 0;
	    	$j = 0;

	    	if( isset($res['item']) ) {
	
	    		foreach( $res['item'] as $key=>$val ) {
	    			
	    			if( !is_int($key) ) {
	    				$val = $res['item'];
	    			}
	    			
	    			if( !$car -> getOneImgById($val['id']) ) {
	    				$result = $car -> insertImgData($val);
	    				if( $result ) {
	    					$i++;
	    				}else{
	    					echo $val['id'] . '入库失败<p>';
	    					$j++;
	    				}
	    			}elseif ( $car -> getOneImgById($val['id']) ) {
	    				echo $str = $val['id'] . '已入库';
	    			}
	    			
	    		}
	    		
	    	}
	    	echo $i . '条入库成功' . $j . '条入库失败';
    	}
    	
    	$this -> assign('nextId',$nextId);
    	$this -> display('admin_car_import_img');
    }
    
    /**
     * 导入参配数据
     */
    public function importParamConfig(){
    	$id = (int)$_GET['id'];
    	$api = new ProApiModel();
    	$car = new CarModel();
    	 
    	$nextId = $car -> getNextModelId($id);
    	 
    	if( !$nextId ) {
    		echo 'success';
    		exit;
    	}

    	$str = '';
    	
    	$res = $api -> getOne('param', $nextId);
    	//echo $nextId; dump($res);exit;
    	if( !$res['item'] ) {
    		echo $str = $nextId . '车型没有数据';
    	}else{
    		$i = 0;
    		$j = 0;
    		if( isset($res['item']) && $res['item'] ) {
    			
    			foreach( $res['item'] as $key=>$val ) {
    				if( !is_int($key) ) {
    					$val = $res['item'];
    				}
    				
    				$val['model_id'] = $nextId;
					
    				$isRuKu = $car -> getOneConfigByParamIdModelId($val['paramid'],$nextId);
    				if(!$isRuKu) {
    					
    						$result = $car -> insertParamConfigData($val);
    						
    						if( $result ) {
    							$i++;
    						}else{
    							echo $val['paramid'] . '入库失败<p>';
    							$j++;
    						}

    				}else{
    					echo $str = $val['paramid'] . '已入库' . '<p>';
    				}
    				
    			}
    			
    		}
    		echo $i . '条入库成功' . $j . '条入库失败';
    	}
    	 
    	$this -> assign('nextId',$nextId);
    	$this -> display('admin_car_import_config');    	
    } 
    
    /**
     * 数据变动通知处理
     */
    public function syncData() {
    	if( $_POST ) {
    		if( $_POST['MessageType'] == 'SyncData' ) {
    			$M_car = new CarModel();
    			$M_api = new ProApiModel();
    			$id = $_POST['ObjectIdentity'];
    			$actionName = $_POST['ActionName'];
    			$TargetObject = $_POST['TargetObject'];
    			$allowTable = array('MasterBrand','Brand','Serial','Car','Param','Photo');

    			if( in_array($TargetObject, $allowTable) ) {
    				$result = $this -> getActionNameByTargetObjActionName($TargetObject,$actionName);

    				$type = $result['type'];
					$action = $result['action'];

    				$api_info = $M_api -> getOne($type, $id);
    				
    				if( isset($api_info['0']) && !$api_info['0'] ) {
    					echo 'not find data,please sure you param right';
    				}else{
    					$data = $api_info['item'];

    					$res = false;
    					if( $actionName == 'Delete' ) {
    						$res = $M_car -> $action($id);
    					}else{
    						if( isset($api_info['item']) && $api_info['item'] ) {
    							$res = $M_car -> $action($data);
    						}else{
    							echo 'empty data';
    						}
    					}
    					
    					if( $res ) {
    						echo 'success';
    					}else{
    						echo 'failed';
    					}
    				}
    			}else{
    				echo 'error TargetObject';
    			}
    		}else{
    			echo 'error action';
    		}
    		
    		exit;
    	}
    }
    
    /**
     * 根据接口参数获得操作和类型
     * @param unknown_type $TargetObject
     * @param unknown_type $actionName
     * @return multitype:string
     */
    private function getActionNameByTargetObjActionName( $TargetObject, $actionName ) {
    	switch ($TargetObject){
    		case 'MasterBrand':
    			$tableName = 'Brand';
    			$type = "sign";
    			break;
    		case 'Brand':
    			$tableName = 'Factory';
    			$type = "brand";
    			break;
    		case 'Serial ':
    			$tableName = 'Bseries';
    			$type = "bseries";
    			break;
    		case 'Car':
    			$tableName = 'Model';
    			$type = "model";
    			break;
    		case 'Param':
    			$tableName = 'Params';
    			$type = "param";
    			break;
    		case 'Photo':
    			$tableName = 'Img';
    			$type = "photoByBseries";
    			break;
    		default:break;
    	}
    	
    	switch ($actionName){
    		case 'Insert' :
    			$action = 'insert' . $tableName . 'Data';
    			break;
    		case 'Delete':
    			$action = 'delete' . $tableName;
    			break;
    		case 'Update' :
    			$action = 'update' . $tableName;
    			break;
    		default:break;
    	}
    	
    	return array( 'action'=>$action, 'type'=>$type );
    }
    /**
     * 生成关键词
     */
    public function createKeywords(){
    	$M_car = new CarModel();
    	$res = $M_car -> getAllBeseries();
		
    	$num = 0;
    	foreach ( $res as $k => $v ) {
    		
    		//单条关键词入字典库
			$add_res = $M_car -> addOneKeyword($v);
			
			if( $add_res === false ) {
				
			}else if( $add_res != 0 ) {
    			$num += $add_res;
    		}else if( $add_res == 0 ){
    			echo $v['showname'] . '已入库<p>';
    		}
    	}
    	
    	echo $num . '条关键词入库成功';
    	exit;
    }
    
    /**
     * 分析所有新闻的关键词并入news_keyword库
     */
    public function parseNewsKeyword(){
    	$M_news = new NewsModel();
    	$M_car = new CarModel();
    	
    	$page = (int)$_GET['page'];
    	
    	$keywords = $M_car -> getAllKeywords();
		
    	$news = $M_news -> getLimitNews($page);
    	if( !$news['news'] ) {
    		echo 'success';
    		exit;
    	}

    	$num = 0;
    	foreach ( $news['news'] as $key => $val ) {
		
    		$add_res = $M_news -> addKeywordsToNewsKeyword($val,$keywords);
    		$num = $num + $add_res['num'];
    		if( !empty($add_res['msg']) ) {
    			echo $add_res['msg'];
    			exit;
    		}
    		
    	}
    	
    	echo $num . '条关键词入库成功';
    	
    	echo '<script>window.location.href="/Admin/Index/parseNewsKeyword?page=' . $news['page'] . '"</script>';
    	exit;
    }
    
    /**
     * 产品库字典数据管理
     */
    public function api_keyword(){
    	$M_car = new CarModel();
    	$title = '数据字典管理';
    	$page = (int)$_GET['page'];
    	$page_count = 50;
    	
    	//查询关键词
    	$keywords = $M_car -> keywordList($page, $page_count);
    	
    	//查询关键词条数
    	$count = $M_car -> getCountKeyword();
    	
    	//查询所有的车系
    	$bseries = $M_car -> getAllBeseries();
    	$bseries_arr = array();
    	foreach ( $bseries as $k=>$v ) {
    		$bseries_arr[$v['id']] = $v['name'];
    	}
    	
    	$this -> page($page , $count , $page_count , "/Admin/Index/api_keyword?page={page}");
    	
    	$this -> assign('bseries',$bseries_arr);
    	$this -> assign('keywords',$keywords);
    	$this -> assign('title',$title);
    	$this-> display('api_keyword');
    }
    
    /**
     * 向字典中添加关键词
     */
    public function api_keyword_add(){
    	$M_car = new CarModel();
    	$title = '添加关键词';
    	
    	$Bseries = $M_car -> getAllBeseries();
    	if( $_POST && $_POST['action'] == 'api_keyword_add' ) {
    		$data = array();
    		
    		if( empty($_POST['keyword']) ) {
				$data = array('status'=>'error','mess'=>'请填写关键词'); 			
    		}elseif( empty($_POST['pid']) ) {
    			$data = array('status'=>'error','mess'=>'请选择车系'); 	
    		}else{
    			$keyword_info = $M_car -> getOneKeywordByKeyword($_POST['keyword']);
    			if( $keyword_info ) {
    				$data = array('status'=>'error','mess'=>'关键词在字典中已存在，请勿重复添加');
    			}else{
    				$add_res = $M_car -> insertKeywords($_POST);
    				 
    				if( $add_res ) {
    					$data = array('status'=>'succ','mess'=>'添加成功');
    				}else{
    					$data = array('status'=>'error','mess'=>'添加失败');
    				}
    			}
    		}
    		
    		$this -> baseAjaxReturn($data);
    		exit;
    	}
    	$this -> assign('bseries',$Bseries);
    	$this -> display('api_keyword_add');
    }
    
    /**
     * 删除 关键词
     */
    public function api_keyword_delete(){
    	if( $_POST && $_POST['action'] == 'api_keyword_delete' ) {
    		$arr = array();
    		if( empty($_POST['kid']) ) {
    			$data = array('status'=>'error','msg'=>'id不能为空');
    		}else{
    			$id = $_POST['kid'];
    			$M_car = new CarModel();
    			
    			$del_res = $M_car -> deleteKeywordById($id);
    			if( $del_res ) {
    				$data = array('status'=>'succ','mesg'=>'删除成功');
    			}else{
    				$data = array('status'=>'error','msg'=>'删除失败');
    			}
    		}
    		
    		$this -> baseAjaxReturn($data);
    		exit;
    	}
    }
}