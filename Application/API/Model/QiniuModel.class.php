<?php
namespace API\Model;

class QiniuModel
{

    protected $_db_qiniu_log;
    
    public function __construct(){
        $this -> _db_qiniu_log = M('qiniu_log' , 'ad_' , 'DB0_CONFIG');
    }
    
    /**
     * 获取版块列表
     *
     */
    public function add($domain , $key){
        $data['domain'] = $domain;
        $data['key'] = $key;
        $data['add_time'] = date('Y-m-d H:i:s');
        return $this -> _db_qiniu_log -> add($data);
    }
    
    
}
