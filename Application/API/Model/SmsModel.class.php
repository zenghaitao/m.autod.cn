<?php
namespace API\Model;

/**
*	短信验证功能 使用模块(auto api 专题部分)
*	@way 分为POST GET方式发送
*	@param content String 内容
*	@param phone String 手机号
*	@return statusNo 状态码
*/

class SmsModel  {
	private $strReg = '';	//注册号(SN码)
	private $strPwd = '';	//密码
	protected $_db_log_sms;


	public function __construct($strReg='',$strPwd='')
	{
		$this->strReg = "SDK-YQD-010-00031";   
		$this->strPwd = "268687";     
		$this->_db_log_sms = M('Log_sms' , 'ad_' , 'DB0_CONFIG');
	}

	// GET方式发送
	private function getSend($url,$param)
	{
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url."?".$param);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		
		$output = curl_exec($ch);
		curl_close($ch);
		//echo $output;
		return $output;	
	}

	// POST方式发送
	private function postSend($url,$param)
	{
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$param);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	// 编码转换并进行 url编码
	private function gbkToUtf8($str)
	{
		return rawurlencode(iconv('GB2312','UTF-8',$str));	
	}

	// 发送短信
	public function Send($phone,$content,$ext='')
	{
		$strPhone = $phone;	//手机号码，多个手机号用半角逗号分开，最多10000个
		$strContent = $content;   //短信内容
		$strExt = $ext;	//扩展码
		$strStime = "";	//定时发送时间,时间格式yyyy-MM-dd HH:mm:ss,含有空格请转码
		$strRrid = "";	//唯一标识
		$strMsgfmt = ""; //内容编码

		$strSmsUrlArr = array("http://sdk2.entinfo.cn:8061/mdsmssend.ashx","http://sdk.entinfo.cn:8061/mdsmssend.ashx");
		$strKey = array_rand($strSmsUrlArr);
		$strSmsUrl = $strSmsUrlArr[$strKey];
		$strSmsParam = "sn=".$this->strReg."&pwd=".strtoupper(MD5($this->strReg.$this->strPwd))."&mobile=".$strPhone."&content=".$strContent."&ext=".$strExt."&stime=".$strStime."&rrid=".$strRrid."&msgfmt=".$strMsgfmt ;
		//echo $strSmsUrl.$strSmsParam;
		$strRes = $this->getSend($strSmsUrl,$strSmsParam);
		if(!$strRes){
			$strRes = $this->Send($strPhone,$strContent,$ext);
		}else{
			$status = $this->getStatus($strRes);
			$this->logSms($strPhone,$strRes,urldecode($content));
			if(strpos(strtolower($strRes) , 'error') === false){
			    return true;
			}else{
			    return false;
			}
		}
	}

	// 查询余额
	public function Balance()
	{
		$strSmsUrl = "http://sdk.entinfo.cn:8060/webservice.asmx/balance";
		$strSmsParam = "sn=".$this->strReg."&pwd=".strtoupper(MD5($this->strReg.$this->strPwd));
		$xmlRes = $this -> postSend($strSmsUrl, $strSmsParam);
		$xmlArr = simplexml_load_string($xmlRes);
		return $xmlArr[0];
	}

	/* 提示信息
	*	@param $str String 返回值
	*	@return boolean true/false
	*/
	public function getStatus($str)
	{
		if(!$str)return false;
		$position = strpos($str,'-');
		return ($position === 0) ? false : true;
	}

	/**
	* 短信日志
	* @param String phoneNum
	* @param String number
	* @return resourse 
	*/
	private function logSms($phoneNum,$number,$content)
	{
	    
		$data = array();
		$data['phonenum'] = $phoneNum;
		$data['result'] = $number;
		$data['content'] = $content;
		$data['refer'] = $_SERVER['HTTP_HOST'];
		$data['add_time'] = date('Y-m-d H:i:s');
		$data['status'] = 'error';
		
		if(strpos(strtolower($number) , 'error') === false){
		    $data['status'] = 'succ';
		}
		
		$query = $this -> _db_log_sms -> add($data);
		return $query;
	}
	
}
