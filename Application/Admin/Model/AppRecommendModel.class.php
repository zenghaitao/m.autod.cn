<?php
namespace Admin\Model;

/**
 * app推荐模型
 * @author user
 *
 */
class AppRecommendModel{
	protected $_ad_app_recommend;
	
	public function __construct(){
		$this -> _ad_app_recommend = M('app_recommend','ad_','DB0_CONFIG');
	}
	
	/**
	 * 添加推荐
	 * @param unknown_type $data
	 */
	public function addRecommend($data){
		return $this -> _ad_app_recommend -> add($data);
	}
	
	/**
	 * 删除推荐
	 * @param unknown_type $id
	 */
	public function delteRecommendById($id){
		return $this -> _ad_app_recommend -> where("id='{$id}'") -> delete();
	}
	
	/**
	 * 更新某一字段值
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @param unknown_type $id
	 */
	public function updateColumnById($name,$value,$id){
		$data = array($name=>$value);
		return $this -> _ad_app_recommend -> where("id='{$id}'") -> save($data);
	}
	
	/**
	 * 按页查询推荐列表
	 * @param unknown_type $page
	 * @param unknown_type $pagesize
	 */
	public function getRecommendList($page,$pagesize){
		return $this -> _ad_app_recommend -> limit(($page-1)*$pagesize,$pagesize) -> order('priority desc') -> select();
	}
	
	/**
	 * 获取推荐应用的总数
	 */
	public function getCountRecommends(){
		return $this -> _ad_app_recommend -> count();
	}
	
	/**
	 * 获取所有的应用
	 */
	public function getAllRecommend(){
		return $this -> _ad_app_recommend -> where("id!=0") -> order('priority desc') -> select();
	}
	
	/**
	 * 通过id查找app推荐信息
	 * @param unknown_type $id
	 */
	public function findRecommendById($id){
		return $this -> _ad_app_recommend -> where("id='{$id}'") -> find();
	}
	
	/**
	 * 通过id更新数据
	 * @param unknown_type $id
	 * @param unknown_type $data
	 */
	public function updateRecommendById($id,$data){
		return $this -> _ad_app_recommend -> where("id='{$id}'") -> save($data);
	}
}