<?php
namespace Admin\Model;
include_once(dirname(__FILE__)."/simple_html_dom.php");

class SnatchModel
{
    private $_url = '';
    public function __construct($url){
        $this -> _url = $url;
    }
    
    public function parse(){
        $url_arr = parse_url($this -> _url);
        if($url_arr['host'] == 'news.163.com'){
            return $this -> netease();
        }
        
        exit;
    }
    
    private function netease(){
        $html = file_get_html($this -> _url);
        
        $res = $html -> find('.end-text' , 0) -> innertext ;
        
        $res = strip_tags($res , "<p><img>");
        
        return (iconv('gbk' , 'utf-8' , $res));
    }
    
    public function img($string){
        $html = str_get_html($string);
        $imgs = $html -> find('img');
        $res = array();
        foreach ($imgs as $img){
            $res[] = $img -> src;
        }
        return $res;
    }
}