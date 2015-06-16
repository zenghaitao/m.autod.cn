<?php
namespace API\Controller;
use Think\Controller;
class QuestionController extends BaseController {
    
    /**
     * 为试题添加评论
     *
     */
    public function comment(){
        $comment_id = $_POST['cid'];
        $contents = $_POST['contents'];
        $reply_cid = $_POST['reply_cid'];
        $uid = $_SESSION['user']['uid'];
        //将新评论导入数据库，并将动态更新到动态列表
        
        echo $this -> api_encode( array( 'status' => 'succ' , 'info' => $info ));
    }
    
    /**
     * 获取试题的评论列表
     *
     */
    public function getComments(){
        $comment_id = $_GET['cid'];
        $since_id = $_GET['since_id'];
        $count = $_GET['count']?$_GET['count']:20;
        //获取评论列表
        
        echo $this -> api_encode( array( 'status' => 'succ' , 'info' => $info ));
    }
    
    /**
     * 提交用户答题记录到服务器
     *
     */
    public function addLog(){
        $data['uid'] = $_SESSION['user']['uid'];
        $data['type'] = $_POST['type'];
        $data['question_id'] = $_POST['question_id'];
        $data['answer'] = $_POST['answer'];
        $data['is_bingo'] = $_POST['is_bingo'];
        $data['add_time'] = date('Y-m-d H:i:s');
        //将答题记录存入数据库
        
        echo $this -> api_encode( array( 'status' => 'succ' , 'info' => $info ));
    }
}