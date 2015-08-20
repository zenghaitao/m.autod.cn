<?php
namespace Admin\Model;

class RecordLogModel
{
	protected $_record_log;
	protected $_actions_need_record = array(
			'Index/login',
			'Index/logout',
			'Index/editPwd',
			'Index/admin_user_add',
			'Index/admin_user_delete',
			'Index/admin_user_update_group',
			'Index/deleteNews',
			'Index/saveStoryCate',
			'Index/addNews',
			'Index/news_reply_comment',
			'Index/news_reply',
			'Index/news_comment_delete',
			'Index/newsUpStory'
			);	//需要记录日志的操作
	
	public function __construct(){
		$this -> _record_log = M('record_log' , 'ad_' , 'DB0_CONFIG');
		
	}
	
	/**
	 * 记录操作日志
	 * @param int $uid 操作者id
	 * @param string $action 操作
	 * @param int $result 操作结果
	 */
	public function recordLog($uid,$url,$action_info){
		//记录操作日志
		if( !in_array( $url, $this -> _actions_need_record ) ) {
			return false;
		}
		
		$data['uid'] = $uid;
		$data['action'] = $url;
		$data['action_info'] = serialize($action_info);
		
		return $this -> _record_log -> data($data) -> add();
	}
	
}
?>