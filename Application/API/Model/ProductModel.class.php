<?php
namespace API\Model;

class ProductModel
{

    protected $_db_auto_keyword;
    
    public function __construct(){
        $this -> _db_auto_keyword = M('auto_keyword' , 'ad_' , 'DB0_CONFIG');
    }
    
    public function addAutoKeyword($data){
        $info = $this -> _db_auto_keyword -> where("type='bseries' AND pid = '{$data['bseries_id']}'") -> find();
        if(!$info['id']){
            $data['name_cn'] = str_replace('(进口)','',$data['name']);
            $data['name_cn'] = str_replace('（进口）','',$data['name_cn']);
            $data['len'] = strlen($data['name_cn']);
            $data['pid'] = $data['bseries_id'];
            $data['type'] = 'bseries';
            return $this -> _db_auto_keyword -> add($data);
        }
    }
    
    public function getAutoKeywords(){
        return $this -> _db_auto_keyword -> where(1) -> order('len DESC') -> select();
    }
}
