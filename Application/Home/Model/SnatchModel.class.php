<?php
namespace Home\Model;
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
            return $this -> netease_news();
        }
        
        exit;
    }
    
    private function netease_news(){
        $html = file_get_html($this -> _url);
        
        $res = $html -> find('.end-text' , 0) -> innertext ;
        
        $res = strip_tags($res , "<p><img>");
        
        return (iconv('gbk' , 'utf-8' , $res));
    }
}