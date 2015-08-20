<?php
namespace Admin\Model;

/**
 * 产品库接口模型
 * @author user
 *
 */
class ProApiModel{
	
	public function getSign(){
		$url = "http://api.autod.cn/getXml.php?type=sign";
		$res = simplexml_load_file($url, null, LIBXML_NOCDATA);
		
		$res = $this -> objToArray($res);
		
		return $res;
	}
	
	public function getBrand(){
		$url = "http://api.autod.cn/getXml.php?type=brand";
		$res = simplexml_load_file($url, null, LIBXML_NOCDATA);
	
		$res = $this -> objToArray($res);
		
		return $res;
	}
	
	public function getBseries(){
		$url = "http://api.autod.cn/getXml.php?type=bseries";
		$res = simplexml_load_file($url, null, LIBXML_NOCDATA);
	
		$res = $this -> objToArray($res);
	
		return $res;
	}
	
	public function getModel(){
		$url = "http://api.autod.cn/getXml.php?type=model";
		$res = simplexml_load_file($url, null, LIBXML_NOCDATA);
		
		$res = $this -> objToArray($res);
	
		return $res;
	}
	
	public function getParams(){
		$url = "http://api.autod.cn/getXml.php?type=params";
		$res = simplexml_load_file($url, null, LIBXML_NOCDATA);
	
		$res = $this -> objToArray($res);
	
		return $res;
	}
	
	public function getOne($type,$id){
		$url = "http://api.autod.cn/getData.php?type=$type&id=$id";
		$res = simplexml_load_file($url, null, LIBXML_NOCDATA);
	
		$res = $this -> objToArray($res);
	
		return $res;
	}
	
	public function getDealer($type,$id,$cityId,$start,$num){
		$url = "http://api.autod.cn/getData.php?type=$type&id=$id&cityId=$cityId";
		$res = simplexml_load_file($url, null, LIBXML_NOCDATA);
		
		$res = $this -> objToArray($res);
		
		if( !isset($res['item']['0']) ) {
			$arr['item']['0'] = $res['item'];
			return $arr['item'];
		}
		
		if( $num > 0 ) {
			$arr = array_chunk($res['item'], $num);
			return $arr[$start];
		}else{
			return $res['item'];
		}
		
	}
	
	public function getArea(){
		$url = "http://api.autod.cn/getXml.php?type=area";
		$res = simplexml_load_file($url, null, LIBXML_NOCDATA);
	
		$res = $this -> objToArray($res);
	
		return $res;
	}
	
	/**
	 * 对象转数组
	 * @param 对象 $obj
	 */
	private function objToArray($obj){
		$obj = (array)$obj;
		foreach ( $obj as $k => $v ) {
			if( gettype($v) == 'object' || gettype($v) == 'array' ) {
				$obj[$k] = $this -> objToArray($v);
			}
		}
		
		return $obj;
	}
	
}