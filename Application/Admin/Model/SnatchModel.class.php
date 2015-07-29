<?php
namespace Admin\Model;
include_once(dirname(__FILE__)."/simple_html_dom.php");

class SnatchModel
{
    private $_url = '';
    public function __construct($url){
        $this -> _url = $url;
    }
    
    public function toutiaoPageList(){
        $html = file_get_html($this -> _url);
        
        $a_list = $html -> find('#pagebar a');
        $max_page = 0;
        foreach ($a_list as $a){
            $num = (int)($a -> plaintext);
            if($num > $max_page)
                $max_page = $num;
        }
        
        $list = array();
        for ($i = 1 ; $i <= $max_page ; $i ++){
            if($i == 1){
                $list[$i] = $this -> _url;
                continue;
            }
            $list[$i] = $this -> _url . "p{$i}/";
        }
        
        return $list;
        
    }
    
    public function toutiaoPage(){
        $string = file_get_contents($this -> _url);
        $html = str_get_html($string);
        
        $list = $html -> find('#ColumnContainer .pin');
        $result = array();
        
        foreach ($list as $row){
            $e = $row -> find('.pin-content',0);
            @$data['article_id'] = $e -> group_id;
            @$data['title'] = trim($e -> find('h3',0) -> plaintext);
            @$data['short_summary'] = trim($e -> find('.text',0) -> plaintext);
            @$data['url'] = $e -> find('a',0) -> href;
            @$data['title_pic1'] = trim($e -> find('img',0) -> src);
            @$data['title_pic2'] = trim($e -> find('img',1) -> src);
            @$data['title_pic3'] = trim($e -> find('img',2) -> src);
            @$data['story_date'] = trim($e -> find('.item_info',0) -> find('td',3) -> plaintext);
            $result[] = $data;
        }
        
        return $result;
    }
    
    /**
     * 头条内容分析处理
     *
     * @return array
     */
    public function toutiaoContent(){
        $content = '';
        $imgs = array();
        
        if(strpos($this -> _url , 'http://toutiao.com/a') === false){
            return array('content'=>'','images'=>'');
        }
        
        $string = file_get_contents($this -> _url);
        if(!$string)
            return array('content'=>'','images'=>'');
        
        $html = str_get_html($string);
        $content = @$html -> find(".article-content" , 0) -> innertext;
        if(strpos($content , 'img')){
            $img_list = $this -> img($content);
        }
        
        //去除多余js事件
        $content = str_replace('onerror="javascript:errorimg.call(this);"' , '' , $content);
        
        //去掉script
        if(strpos($content , '<script') !== false){
            $tags = @$html -> find("script");
            foreach ($tags as $tag){
                $content = str_replace($tag -> outertext , '' , $content);
            }
        }
        
        //去掉投票部分
        if(strpos($content , 'mp-vote-box') !== false){
            $votes = @$html -> find(".mp-vote-box");
            foreach ($votes as $vote){
                $vote_html = $vote -> outertext;
                $content = str_replace($vote_html , '' , $content);
            }
        }
        
        $result = array();
        $result['content'] = strip_tags(trim($content) , '<p><img><div><table><tr><td>');
        //$result['content'] = trim($content);
        $result['images'] = $img_list;
        $result['http'] = $http_response_header[0];
        return $result;
        
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
    
    public function autoKeyWord($string){
        $html = str_get_html($string);
        $dds = $html -> find('.ina_xzpp_nr dd a');
        foreach ($dds as $dd){
            $href = $dd -> href;
            if(strpos($href , 'ina_xzpp')){
                $href = str_replace(array('javascript:ina_xzpp(',')') , '' , $href);
                $href = "http://www.huiche100.com/ind/cars.php?sign_id={$href}";
                $html = file_get_contents($href);
                $html = str_get_html($html);
                
                $list = $html -> find('dd a');
                foreach ($list as $row){
                    $name = $row -> value;
                    $id = str_replace(array("javascript:ina_xzcx('{$name}',",')') , '' , $row -> href);
                    
                    var_dump($name,$id);
                }
                exit;
                
            }
        }
    }
}