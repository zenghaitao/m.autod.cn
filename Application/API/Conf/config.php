<?php
return array(
	//'配置项'=>'配置值'
	
	'DB0_CONFIG' => array(
	    'db_name'           => 'autodaily',
        'db_type'           => 'mysql',
        'db_host'           => $_SERVER['AD_DB0_M_HOST'].','.$_SERVER['AD_DB0_S0_HOST'],
        'db_user'           => $_SERVER['AD_DB0_M_USER'].','.$_SERVER['AD_DB0_S0_USER'],
        'db_pwd'            => $_SERVER['AD_DB0_M_PASS'].','.$_SERVER['AD_DB0_S0_PASS'],
        'db_port'           => $_SERVER['AD_DB0_M_PORT'],
        'db_prefix'         => 'ad_',
        'db_deploy_type'    => '1',
        'db_rw_separate'    =>  true,
        'db_master_num'     =>  1,
        'db_slave_no'       =>  1,
        'db_charset'        => 'utf8',
     ),
	
);