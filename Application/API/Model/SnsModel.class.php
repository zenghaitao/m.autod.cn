<?php
namespace API\Model;

class SnsModel
{

    protected $_db_sns_foram;
    protected $_db_sns_thread;
    
    public function __construct(){
        $this -> _db_sns_foram = M('sns_foram' , 'ad_' , 'DB0_CONFIG');
        $this -> _db_sns_thread = M('sns_thread' , 'ad_' , 'DB0_CONFIG');
    }
    
    /**
     * 获取版块列表
     *
     */
    public function foramList(){
        $list = $this -> _db_sns_foram -> order('id ASC') -> limit(100) -> select();
        return $list;
    }
    
    /**
     * 获取帖子列表
     *
     * @param int $foram_id
     * @param int $page
     * @param int $count
     * @return array
     */
    public function threadList($foram_id , $page = 1 , $count = 50){
        if($foram_id)
            $where_str = "foram_id = '{$foram_id}'";
        else 
            $where_str = "1";
        $limit_str = ($page - 1) * $count . ',' . $count;
        $list = $this -> _db_sns_thread -> where($where_str) -> order('id DESC') -> limit($limit_str) -> select();
        return $list;
    }
    
    /**
     * 获取帖子总数量
     *
     * @param int $foram_id
     * @return int
     */
    public function threadCount($foram_id = 0){
        if($foram_id)
            $where_str = "foram_id = '{$foram_id}'";
        else 
            $where_str = "1";
        $count = $this -> _db_sns_thread -> where($where_str) -> count();
        return $count;
    }
    
    public function addThreadStatus($data){
        $res = $this -> _db_sns_thread -> add($data);
        return $res;
    }
    
    public function thread($id){
        $res = $this -> _db_sns_thread -> where("id = '{$id}'") -> find();
        return $res;
    }
}
