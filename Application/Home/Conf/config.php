<?php
return array(
	//'配置项'=>'配置值'
	'SESSION_AUTO_START' => false,
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
	'ERROE_CODE' => array(
	   '101'   => '数据格式出错',
	   '102'   => '写入操作出错',
	   '103'   => '不存在的数据',
	   '104'   => '需授权后访问',
	   '105'   => '请先登录后访问',
	   '106'   => '暂无此访问权限',
	),
);