<?php

class UsersModel
{
    /**
     * 根据设备ID来注册用户
     *
     * @param string $deviceID
     * @return bool
     */
	public static function registerUserWithDeviceID($deviceID , $info)
    {
    	//创建一条新设备记录
    	
    	//创建一条用户记录
    	
    	
    	//设置session数据
        $_SESSION['device'] = $device_info;
        $_SESSION['user'] = $user_info;
        
    	return $user_id;
    }
}