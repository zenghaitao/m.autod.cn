<?php
namespace Admin\Controller;

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
		
        //是否为合法访问
        if(!in_array($_SERVER['PATH_INFO'] , array('Index/login','Index/logout'))){
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
    		
    		$newschoice = new NewsModel();
    		$res = $newschoice -> deleteNewsById($id);
    		
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
    		$row['caozuo'] = '刪除';
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
    public function news_reply(){
    	
    	//$comments = array('id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1','id'=>'1');
    	$this -> assign('list',$comments);
    	$this -> display();
    }
    
}