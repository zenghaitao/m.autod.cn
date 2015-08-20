<?php
namespace Admin\Model;

class CarModel{
	protected $_ad_car_area;
	protected $_ad_car_sign;
	protected $_ad_car_bseries;
	protected $_ad_car_factory;
	protected $_ad_car_model;
	protected $_ad_car_params;
	protected $_ad_car_img;
	protected $_ad_car_param_config;
	protected $_ad_car_order;
	protected $_ad_car_keyword;
	
	public function __construct(){
		$this -> _ad_car_area = M('car_area' , 'ad_' , 'DB0_CONFIG');
		
		$this -> _ad_car_sign = M('car_sign' , 'ad_' , 'DB0_CONFIG');
		
		$this -> _ad_car_factory = M('car_factory' , 'ad_' , 'DB0_CONFIG');

		$this -> _ad_car_bseries = M('car_bseries' , 'ad_' , 'DB0_CONFIG');
		
		$this -> _ad_car_model = M('car_model' , 'ad_' , 'DB0_CONFIG');
		
		$this -> _ad_car_params = M('car_params' , 'ad_' , 'DB0_CONFIG');
		
		$this -> _ad_car_img = M('car_img' , 'ad_' , 'DB0_CONFIG');
		
		$this -> _ad_car_param_config = M('car_param_config' , 'ad_' , 'DB0_CONFIG');
		
		$this -> _ad_car_order = M('car_order' , 'ad_' , 'DB0_CONFIG');
		
		$this -> _ad_car_keyword = M('car_keyword' , 'ad_' , 'DB0_CONFIG');
	}
	
	/**
	 * 插入地区数据
	 * @param unknown_type $data
	 */
	public function insertAreaData($data){
		return $this -> _ad_car_area -> add($data);	
	}
	
	/**
	 * 插入品牌数据
	 * @param unknown_type $data
	 */
	public function insertBrandData($data){
		return $this -> _ad_car_sign -> add($data);
	}
	
	/**
	 * 插入厂商数据
	 * @param unknown_type $data
	 */
	public function insertFactoryData($data){
		return $this -> _ad_car_factory -> add($data);
	}
	
	/**
	 * 插入车系数据
	 * @param unknown_type $data
	 */
	public function insertBseriesData($data){
		return $this -> _ad_car_bseries -> add($data);
	}
	
	/**
	 * 插入车型数据
	 * @param unknown_type $data
	 */
	public function insertModelData($data){
		return $this -> _ad_car_model -> add($data);
	}
	
	/**
	 * 插入参数数据
	 * @param unknown_type $data
	 */
	public function insertParamsData($data){
		return $this -> _ad_car_params -> add($data);
	}
	
	/**
	 * 插入参配数据
	 * @param unknown_type $data
	 */
	public function insertParamConfigData( $data ) {
		return $this -> _ad_car_param_config -> add($data);
	}
	
	/**
	 * 更新参配数据
	 * @param unknown_type $data
	 */
	public function updateParamConfig($data) {
		return $this -> _ad_car_param_config -> save($data);
	}
	
	/**
	 * 删除参配数据
	 * @param unknown_type $data
	 */
	public function deleteParamConfig($id) {
		return $this -> _ad_car_param_config -> where("id='{$id}'") -> delete($data);
	}
	
	/**
	 * 更新品牌数据
	 * @param unknown_type $data
	 */
	public function updateBrand($data){
		return $this -> _ad_car_sign -> save($data);
	}
	
	/**
	 * 更新厂商数据
	 * @param unknown_type $data
	 */
	public function updateFactory($data){
		return $this -> _ad_car_factory -> save($data);
	}
	
	/**
	 * 更新车系数据
	 * @param unknown_type $data
	 */
	public function updateBseries($data){
		$id = $data['id'];	
		$arr['minprice'] = str_replace('.', '', $data['minPrice']);
		$arr['maxprice'] = str_replace('.', '', $data['maxPrice']);
		return $this -> _ad_car_bseries -> where("id='{$id}'") -> save($arr);
	}
	
	/**
	 * 更新车型数据
	 * @param unknown_type $data
	 */
	public function updateModel($data){
		$id = $data['id'];
		$arr['minprice'] = str_replace('.', '', $data['minPrice']);
		$arr['maxprice'] = str_replace('.', '', $data['maxPrice']);

		return $this -> _ad_car_model -> where("id='{$id}'") -> save($arr);
		echo $this -> _ad_car_model -> getLastSql();
	}
	
	/**
	 * 更新参数数据
	 * @param unknown_type $data
	 */
	public function updateParams($data){
		return $this -> _ad_car_params -> save($data);
	}
	
	/**
	 * 删除品牌数据
	 * @param unknown_type $id
	 */
	public function deleteBrand( $id ){
		return $this -> _ad_car_sign -> where("id='{$id}'") -> delete();
	}
	
	/**
	 * 删除厂商数据
	 * @param unknown_type $id
	 */
	public function deleteFactory( $id ){
		return $this -> _ad_car_factory -> where("id='{$id}'") -> delete();
	}
	
	/**
	 * 删除车系数据
	 * @param unknown_type $id
	 */
	public function deleteBseries( $id ){
		return $this -> _ad_car_bseries -> where("id='{$id}'") -> delete();
	}
	
	/**
	 * 删除车型数据
	 * @param unknown_type $id
	 */
	public function deleteModel( $id ){
		return $this -> _ad_car_model -> where("id='{$id}'") -> delete();
	}
	
	/**
	 * 删除参数数据
	 * @param unknown_type $id
	 */
	public function deleteParams( $id ){
		return $this -> _ad_car_params -> where("id='{$id}'") -> delete();
	}
	
	/**
	 * img表插入数据
	 * @param unknown_type $data
	 */
	public function insertImgData( $data ){
		return $this -> _ad_car_img -> add( $data );
	}
	
	/**
	 * img更新数据
	 * @param unknown_type $data
	 */
	public function updateImg( $data ){
		return $this -> _ad_car_img -> save( $data );
	}
	
	/**
	 * img删除数据
	 * @param unknown_type $id
	 */
	public function deleteImg( $id ){
		return $this -> _ad_car_img -> where("id={'$id'}") -> delete();
	}
	
	/**
	 * 通过id获取一条图片数据
	 * @param unknown_type $id
	 */
	public function getOneImgById( $id ) {
		return $this -> _ad_car_img -> where("id='{$id}'") -> find();
	}
	
	/**
	 * 通过id获取一条参配数据
	 * @param unknown_type $id
	 * @param unknown_type $model_id
	 */
	public function getOneConfigByParamIdModelId( $id, $model_id ) {
		return $this -> _ad_car_param_config -> where("paramid='{$id}' and model_id='{$model_id}'") -> find();
	}	
	
	/**
	 * 通过id获取一条车型数据
	 * @param unknown_type $id
	 */
	public function getOneModelById( $id ) {
		return $this -> _ad_car_model -> where("id='{$id}'") -> find();
	}
	
	/**
	 * 获取下一个车系id
	 * @param unknown_type $id
	 * @return unknown|boolean
	 */
	public function getNextBseriesId($id = '0') {
		$res = $this -> _ad_car_bseries -> where("id > '{$id}'") -> field('id') -> order('id asc') -> find();

		if( $res ) {
			return $res['id'];
		}else{
			return false;
		}
	}
	
	/**
	 * 获取下一个车型id
	 * @param unknown_type $id
	 * @return unknown|boolean
	 */
	public function getNextModelId($id = '0') {
		$res = $this -> _ad_car_model -> where("id > '{$id}'") -> field('id') -> order('id asc') -> find();
// 		var_dump($this -> _ad_car_model -> getLastSql());exit;
		if( $res ) {
			return $res['id'];
		}else{
			return false;
		}
	}
	
	/**
	 * 通过id获取一条车系数据
	 * @param unknown_type $id
	 */
	public function getOneBseriesInfoByid( $id ) {
		return $this -> _ad_car_bseries -> where("id='{$id}' and salestate='在销'") -> find();
	}
	
	/**
	 * 通过车系id获取车型数据
	 * @param int $bseries_id
	 */
	public function getModelsByBseriesId( $bseries_id ) {
		return $this -> _ad_car_model -> where("bseries_id='{$bseries_id}' and producestate = '在产'") -> order('pinyin asc') -> select();
	}
	
	/**
	 * 根据车型id获取参配数据
	 * @param unknown_type $model_id
	 */
	public function getParamsByModelId( $model_id ) {
		return $this -> _ad_car_param_config -> where("model_id='{$model_id}'") -> select();
	}
	
	/**
	 * 根据车系id获取图片shuju
	 * @param unknown_type $bseries_id
	 */
	public function getImgsByBseriesId( $bseries_id ) {
		return $this -> _ad_car_img -> where("bseries_id='{$bseries_id}'") -> select();
	}
	
	/**
	 * 根据页数获取模型数据
	 * @param unknown_type $page
	 * @param unknown_type $pageSize
	 * @return multitype:number unknown
	 */
	public function getModelsByPage($page='1',$pageSize='10'){

		$res = $this -> _ad_car_model ->limit($page*$pageSize,$pageSize) -> select();
		$Page = $page+1;
		return array('res'=>$res,'page'=>$Page);
	}
	
	/**
	 * 通过厂商id获取品牌信息
	 * @param unknown_type $factory_id
	 */
	public function getOneSignByBrandId($factory_id){
// 		return 
		return $this -> _ad_car_sign -> query("SELECT signname FROM ad_car_sign WHERE id IN(SELECT sign_id FROM ad_car_factory WHERE id = '{$factory_id}' )");
// 		echo $this -> _ad_car_sign -> getLastSql();
	}
	
	/**
	 * 获取有经销商报价的车系数量
	 */
	public function getBseriesByprice(){
		$res = $this -> _ad_car_bseries -> field("count(id) as count") -> where('minprice != 0 and maxprice != 0') -> find();
		return $res['count'];
	}
	
	/**
	 * 获取所有车系信息
	 */
	public function getAllBeseries(){
		return $this -> _ad_car_bseries -> field('id,name,showname,brand_id,pricecount,dealercount') -> where("salestate = '在销' and pricecount >0") -> select();
	}
	
	/**
	 * 通过id查询一条厂商信息
	 * @param unknown_type $id
	 */
	public function getOneFactoryById($id){
		return $this -> _ad_car_factory -> where("id='{$id}'") -> find();
	}
	
	/**
	 * 通过id查询一条品牌信息
	 * @param unknown_type $id
	 */
	public function getOneSignById( $id ) {
		return $this -> _ad_car_sign -> where("id='{$id}'") -> find();
	}
	
	/**
	 * 向order插入一条数据
	 * @param unknown_type $data
	 */
	public function insertOrder($data){
		return $this -> _ad_car_order -> add($data);
	}
	
	/**
	 * 通过phone name 查询订单信息
	 * @param unknown_type $phone
	 * @param unknown_type $name
	 */
	public function getOneOrderByphone($phone,$name){
		return $this -> _ad_car_order -> where("phone='{$phone}' and name='{$name}'") -> find();
	}
	
	/**
	 * 向keyword插入一条记录
	 * @param unknown_type $data
	 */
	public function insertKeywords($data){
		return $this -> _ad_car_keyword -> add($data);
	}
	
	/**
	 * 删除keyword表的所有数据
	 */
	public function truncateKeywords(){
		return $this -> _ad_car_keyword -> where('1') -> delete();
	}
	
	/**
	 * 通过keyword和is_export查询keyword
	 * @param unknown_type $keyword
	 * @param unknown_type $is_export
	 */
	public function getOneKeywordByKeywordAndIsexport($keyword,$is_export){
		return $this -> _ad_car_keyword -> where("keyword = '{$keyword}' and is_export = '{$is_export}'") -> find();
	}
	
	/**
	 * 通过keyword查询keyword
	 * @param unknown_type $keyword
	 */
	public function getOneKeywordByKeyword($keyword){
		return $this -> _ad_car_keyword -> where("keyword = '{$keyword}'") -> find();
	}
	
	/**
	 * 更新keyword数据
	 * @param unknown_type $id
	 * @param unknown_type $data
	 */
	public function updateKeywordById($id,$data){
		return $this -> _ad_car_keyword -> where("id='{$id}'") -> save($data);
	}
	
	/**
	 * 获取keyword所有数据
	 */
	public function getAllKeywords(){
		return $this -> _ad_car_keyword ->field('keyword,type,pid') -> order('length desc') -> select();
	}
	/**
	 * 获取关键词字典条数
	 */
	public function getCountKeyword(){
		return $this -> _ad_car_keyword -> where("id != 0") -> count();
	}
	
	/**
	 * 通过id删除单条关键词
	 * @param unknown_type $id
	 */
	public function deleteOneKeyword($id){
		return $this -> _ad_car_keyword -> where("id='{$id}'") -> delete();
	}
	
	/**
	 * 字典插入单条关键词
	 * @param unknown_type $data
	 */
	public function addOneKeyword( $data ){
		$num = 0;
		$showname = $data['showname'];
		$is_export = '0';
		
		if( strpos($showname,'进口') !== false ) {
			$is_export = '1';
		}
		
		if( strpos($showname,'(进口)') !== false ) {
			$showname = str_replace('(进口)', '', $showname);
		}
		
		if( strpos($showname,'（进口）') !== false ) {
			$showname = str_replace('（进口）', '', $showname);
		}
			
		if( strpos($showname,'.') !== false ) {
			$showname = str_replace('.', ' ', $showname);
		}
			
		if( strpos($showname,'三厢') !== false ) {
			$showname = str_replace('三厢', '', $showname);
		}
			
		$length = strlen($showname);
		
		$arr['keyword'] = $showname;
		$arr['pid'] = $data['id'];
		$arr['type'] = 'bseries';
		$arr['length'] = $length;
		$arr['is_export'] = $is_export;
			
			
		//如果是进口车系
		if( $is_export == '1' ) {
			//查询字典里有无此关键词(国产)
			$keyword_info = $this -> getOneKeywordByKeywordAndIsexport($showname,0);
		
			//如果有 不入库
			if($keyword_info){
				return false;
			}
			//如果是国产车系
		}else if( $is_export == '0' ) {
			$keyword_info = $this -> getOneKeywordByKeyword($showname);
		
			if( $keyword_info ) {
				$dele_res = $this -> deleteOneKeyword($keyword_info['id']);
				if( $dele_res ) {
					$num--;
				}
			}
		}
			
		$result = $this -> insertKeywords($arr);
		
		if( $result ) {
		 	$num++;
		}
		
		return $num;
	}
	
	/**
	 * 分页查询关键词
	 * @param unknown_type $page
	 * @param unknown_type $pagesize
	 */
	public function keywordList($page,$pagesize){
		return $this -> _ad_car_keyword -> limit($page*$pagesize,$pagesize) -> order('id desc') -> select();
	}
	
	/**
	 * 删除关键词
	 * @param unknown_type $id
	 */
	public function deleteKeywordById($id){
		return $this -> _ad_car_keyword -> where("id='{$id}'") -> delete();
	}
}