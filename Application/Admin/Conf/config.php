<?php
return array(
	//'配置项'=>'配置值'
	
	'DB0_CONFIG' => array(
	    'db_name'           => $_SERVER['AD_DB0_M_NAME'].','.$_SERVER['AD_DB0_S0_NAME'],
        'db_type'           => 'mysql',
        'db_host'           => $_SERVER['AD_DB0_M_HOST'].','.$_SERVER['AD_DB0_S0_HOST'],
        'db_user'           => $_SERVER['AD_DB0_M_USER'].','.$_SERVER['AD_DB0_S0_USER'],
        'db_pwd'            => $_SERVER['AD_DB0_M_PASS'].','.$_SERVER['AD_DB0_S0_PASS'],
        'db_port'           => $_SERVER['AD_DB0_M_PORT'].','.$_SERVER['AD_DB0_S0_PORT'],
        'db_prefix'         => 'ad_',
        'db_deploy_type'    => '1',
        'db_rw_separate'    =>  true,
        'db_master_num'     =>  1,
        'db_slave_no'       =>  1,
        'db_charset'        => 'utf8',
     ),
     
     'DB_CMS_CONFIG' => array(
	    'db_name'           => $_SERVER['CMS_DB_M_NAME'].','.$_SERVER['CMS_DB_S0_NAME'],
        'db_type'           => 'mysql',
        'db_host'           => $_SERVER['CMS_DB_M_HOST'].','.$_SERVER['CMS_DB_S0_HOST'],
        'db_user'           => $_SERVER['CMS_DB_M_USER'].','.$_SERVER['CMS_DB_S0_USER'],
        'db_pwd'            => $_SERVER['CMS_DB_M_PASS'].','.$_SERVER['CMS_DB_S0_PASS'],
        'db_port'           => $_SERVER['CMS_DB_M_PORT'].','.$_SERVER['CMS_DB_S0_PORT'],
        'db_prefix'         => 'cms_',
        'db_deploy_type'    => '1',
        'db_rw_separate'    =>  true,
        'db_master_num'     =>  1,
        'db_slave_no'       =>  1,
        'db_charset'        => 'utf8',
     ),
     
     'DB_INA_CONFIG' => array(
	    'db_name'           => $_SERVER['INA_DB_M_NAME'].','.$_SERVER['INA_DB_S0_NAME'],
        'db_type'           => 'mysql',
        'db_host'           => $_SERVER['INA_DB_M_HOST'].','.$_SERVER['INA_DB_S0_HOST'],
        'db_user'           => $_SERVER['INA_DB_M_USER'].','.$_SERVER['INA_DB_S0_USER'],
        'db_pwd'            => $_SERVER['INA_DB_M_PASS'].','.$_SERVER['INA_DB_S0_PASS'],
        'db_port'           => $_SERVER['INA_DB_M_PORT'].','.$_SERVER['INA_DB_S0_PORT'],
        'db_prefix'         => 'ina_',
        'db_deploy_type'    => '1',
        'db_rw_separate'    =>  true,
        'db_master_num'     =>  1,
        'db_slave_no'       =>  1,
        'db_charset'        => 'utf8',
     ),
	'ERROE_CODE' => array(
	   '101'   => '数据格式出错',
	   '102'   => '写入操作出错',
	   '103'   => '不存在的数据',
	   '104'   => '需授权后访问',
	   '105'   => '请先登录后访问',
	   '106'   => '暂无此访问权限',
	),
	'UPLOAD_SITEIMG_QINIU' => array ( 
        'maxSize' => 5 * 1024 * 1024,//文件大小
        'rootPath' => './',
        'saveName' => array ('uniqid', ''),
        'driver' => 'Qiniu',
        'driverConfig' => array (
            'secrectKey' => 'f_ChZHHqliljnu0gZal5_uvRdS7EZ2yMN49BF5DD', 
            'accessKey' => 'P34_Ls3LpEFpjisNpHgmXNnEOMB1nCqcFtEBdj-q',
            'domain' => '7xjrkc.com1.z0.glb.clouddn.com',
            'bucket' => 'autod', 
        )
    ),
);