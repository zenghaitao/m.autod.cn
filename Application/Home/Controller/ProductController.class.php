<?php
namespace Home\Controller;


use Admin\Model\SnatchModel;
use API\Model\UserModel;
use Admin\Model\CarModel;
use Admin\Model\ProApiModel;

class ProductController extends BaseController  {

	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 车系信息展示
	 */
	public function order(){
		$M_car = new CarModel();
		$M_api = new ProApiModel();
		$M_snatch = new SnatchModel();

		if( isset($_GET['beseiesid'])  && $_GET['beseiesid'] ) {
			$id = $_GET['beseiesid'];
		}
		
		//根据id获取车系数据
		$seriesInfo = $M_car -> getOneBseriesInfoByid($id);
		if( empty($seriesInfo) ) {
			header('Location: http://m.autod.com/Home/Index/');
		}
		
		//根据车系id获取车型数据
		$models = $M_car -> getModelsByBseriesId($id);
		
		//获取行情新闻
		$hangQing = $M_snatch -> fetchAutodHangQing($id);
		
		$this -> assign('hangQing',$hangQing);
		$this -> assign('models', $models);
		$this -> assign('list', $seriesInfo);
		$this -> display('bserise');
	}
	
	/**
	 * 根据车系和城市获取经销商
	 */
	public function getSalesByCityId() {
		$M_api = new ProApiModel();
		$type = "dealerByBseries";
		
		if( $_POST['cityId'] ) {
			$cityId = $_POST['cityId'];
		}
		
		if( $_POST['id'] ) {
			$id = $_POST['id'];
		}
		
		$dealer = $M_api -> getDealer($type, $id, $cityId,0,0);
// 		dump($dealer);exit;
		$this -> assign('dealer', $dealer);
		$this -> display('sales');
	}

	/**
	 * 根据车型和城市获取经销商
	 */	
	public function getSalesByModelIdCityId(){
		$M_api = new ProApiModel();
		$type = "dealerByModel";
		
		if( $_POST['modelId'] ) {
			$modelId = $_POST['modelId'];
		}
		
		if( $_POST['cityId'] ) {
			$cityId = $_POST['cityId'];
		}
		
		$dealer = $M_api -> getDealer($type, $modelId, $cityId,0,0);
		
		$this -> assign('dealer',$dealer);
		$this -> display('sales');
	}
	
	/**
	 * 预约试驾/询底价
	 */
	public function yuYueDrive(){
		if( $_POST ) {
			if( empty($_POST['name']) ) {
				$data = array('status'=>'error','message'=>'请填写用户名');
			}elseif( empty($_POST['phone']) ){
				$data = array('status'=>'error','message'=>'请填写手机号');
			}elseif( empty($_POST['bserise_id']) ){
				$data = array('status'=>'error','message'=>'请选择车系');
			}elseif( empty($_POST['model_id']) ){
				$data = array('status'=>'error','message'=>'请填选择车型');
			}elseif( empty($_POST['sales_province_id']) ){
				$data = array('status'=>'error','message'=>'请填选择经销商所在省');
			}elseif( empty($_POST['sales_city_id']) ){
				$data = array('status'=>'error','message'=>'请填选择经销商所在市');
			}elseif( empty($_POST['sales_id'])){
				$data = array('status'=>'error','message'=>'请填选择经销商');
			}else{
				$M_car = new CarModel();
				
				$searchByPhone = $M_car -> getOneOrderByPhone($_POST['phone'],$_POST['name']);
				$SeriesInfo = $M_car -> getOneBseriesInfoByid($_POST['bserise_id']);

				$seriesName = $SeriesInfo['name'];
				$userIp = $_SERVER['REMOTE_ADDR'];

				if( $searchByPhone ) {
					$data = array('status'=>'error','message'=>'请不要重复提交');
				}else{
					$res = $M_car -> insertOrder($_POST);
					if( $res ) {
						$url = 'http://api.news18a.com/clues/index.php';
						
						$param = array(
								'activityId'=>97,
								'activityType'=>10100,
								'httpUrl'=>$_SERVER['HTTP_REFERER'],
								'agent'=>$_SERVER['HTTP_USER_AGENT'],
								'customer_name'=>$_POST['name'],
								'mobile'=>$_POST['phone'],
								'series_id'=>$_POST['bserise_id'],
								'series'=>$seriesName,
								'model_id'=>$_POST['model_id'],
								'model'=>$_POST['model_name'],
								'province_code'=> $_POST['sales_province_id'],
								'province_name'=>$_POST['sales_province_name'],
								'city_code'=>$_POST['sales_city_id'],
								'city_name'=>$_POST['sales_city_name'],
								'dealer_code'=>$_POST['sales_id'],
								'dealer_name'=>$_POST['dianName'],
						);
						
						$response = $this -> curlPost($url, $param);
						
						if( $response['statusCode'] == '1' ) {
							$data = array('status'=>'sucess','message'=>'预约成功');
						}elseif ( $response['statusCode'] == '0' ) {
							$data = array('status'=>'error','message'=>'预约失败');
						}elseif ( $response['statusCode'] == '2' ) {
							$data = array('status'=>'error','message'=>'请不要重复提交');
						}
					}else{
						$data = array('status'=>'error','message'=>'预约失败，请重试');
					}
				}
				
			}
			
			$this -> ajaxReturn($data);
			exit;
		}
	}
	
	/**
	 * curlpost方法
	 * @param string $url 地址
	 * @param string $param 参数
	 * @return mixed
	 */
	private function curlPost($url,$param) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url);
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $param );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 5 );
		$return = curl_exec ( $ch );
		curl_close ( $ch );
// 		var_dump($return);
// 		exit;
		return $return_ = (array)json_decode($return);
	}
	
}