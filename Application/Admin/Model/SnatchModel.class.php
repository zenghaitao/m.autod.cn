<?php
namespace Admin\Model;
include_once(dirname(__FILE__)."/simple_html_dom.php");

class SnatchModel
{
    public function __construct(){

    }
    
    public function toutiaoPageList($url){
        $html = file_get_html($url);
        
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
                $list[$i] = $url;
                continue;
            }
            $list[$i] = $url . "p{$i}/";
        }
        
        return $list;
        
    }
    
    public function toutiaoPage($url){
        $string = file_get_contents($url);
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
            @$data['story_date'] = trim($e -> find('.item_info',0) -> find('td',2) -> plaintext);
            $result[] = $data;
        }
        
        return $result;
    }
    
    /**
     * 头条内容分析处理
     *
     * @return array
     */
    public function toutiaoContent($url){
        $content = '';
        $imgs = array();
        
        if(strpos($url , 'toutiao.com/a') === false && strpos($url , 'toutiao.com/item') === false){
            return array('content'=>'','images'=>'');
        }
        
        $string = @file_get_contents($url);
        
        if(strpos($http_response_header[0] , '200') === false){
            //var_dump($http_response_header[5]);
            $url_arr = parse_url(trim(preg_replace('/Location:/',' ',$http_response_header[5] , 1)));
            if($url_arr['host'] != 'toutiao.com'){
                return array('content'=>'','images'=>'');
            }
        }
        
        if(!$string)
            return array('content'=>'','images'=>'');
        
        $html = str_get_html($string);
        
        $title = $html -> find("h1" , 0) -> innertext;

        $article_id = $html -> find("#pagelet-detailbar",0) -> getAttribute('data-groupid');
        
        $ptime = $html -> find("#container .time" , 0) -> innertext;
        
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
        $result['article_id'] = $article_id;
        $result['content'] = strip_tags(trim($content) , '<p><img><div><table><tr><td>');
        $result['images'] = $img_list;
        $result['http'] = $http_response_header[0];
        $result['title'] = $title;
        $result['short_summary'] = $title;
        $result['story_data'] = $ptime;
        
        $result['title_pic1'] = str_replace('/large/','/list/',$result['images'][0]);
        if(count($result['images']) > 2){
            $result['title_pic2'] = str_replace('/large/','/list/',$result['images'][1]);
            $result['title_pic3'] = str_replace('/large/','/list/',$result['images'][2]);
        }
        
        return $result;
        
    }
    
    /**
     * 网易新闻内容分析处理
     *
     * @param string $url
     * @return mixed
     */
    public function ntesContent($url){
        //http://3g.163.com/ntes/special/0034073A/wechat_article.html?docid=B03IV95M00964M64&spst=0&spss=newsapp&spsf=wx&spsw=1
        $org_url = $url;
        $url = parse_url($url);
        parse_str($url['query'] , $query);
        if(!$query['docid']){
            return $this -> ntesPhots($org_url);
        }
        
        $ntes_id = $query['docid'];
            
        $url = "http://3g.163.com/touch/article/{$ntes_id}/full.html";
        $file = file_get_contents($url);
        $file = str_replace('artiContent(','',$file);
        $file = str_replace('})','}',$file);
        $file = json_decode($file,1);
        $file = $file[$ntes_id];
        
        if(!$file)
            return false;
        
        $img = $file['img'];
        $video = $file['video'];
        $photo = $file['photoSetList'];
        
        $title = $file['title'];
        $ptime = $file['ptime'];
        
        $body = $file['body'];
        
        foreach ($img as $row){
            $img_html = "<p><img src='{$row['src']}' title='{$row['alt']}' /></p>";
            $body = str_replace($row['ref'] , $img_html , $body);
        }
        
        foreach ($video as $row){
            $video_html = "<p><video src='{$row['url_mp4']}' poster='{$row['cover']}' style='width:100%'></video></p>";
            $body = str_replace($row['ref'] , $video_html , $body);
        }
        
        foreach ($photo as $row){
            $img_html = "<p><img src='{$row['cover']}' title='{$row['title']}' /></p>";
            $body = str_replace($row['ref'] , $img_html , $body);
        }
        
        $result = array();
        $result['article_id'] = $ntes_id;
        $result['content'] = strip_tags(trim($body) , '<p><img><div><table><tr><td><video>');
        
        if(strpos($body , 'img')){
            $img_list = $this -> img($body);
        }
        $result['images'] = $img_list;
        $result['http'] = $http_response_header[0];
        $result['title'] = $title;
        $result['short_summary'] = $title;
        $result['story_data'] = $ptime;
        
        $result['title_pic1'] = $result['images'][0];
        if(count($result['images']) > 2){
            $result['title_pic2'] = $result['images'][1];
            $result['title_pic3'] = $result['images'][2];
        }
        return $result;
    }
    
    /**
     * 分析网易图册新闻
     *
     * @param string $url
     * @return mixed
     */
    public function ntesPhots($url){
        //http://3g.163.com/ntes/special/0034073A/photoshare.html?setid=73215&channelid=0096&spst=3&spss=newsapp&spsf=imsg&spsw=1
        $url = parse_url($url);
        parse_str($url['query'] , $query);
        if(!$query['setid'])
            return false;
        
        $ntes_id = $query['setid'];
        $channel_id = $query['channelid'];
            
        $url = "http://c.3g.163.com/photo/api/jsonp/set/{$channel_id}/{$ntes_id}.json";
        $file = file_get_contents($url);
        $file = str_replace('photosetinfo(','',$file);
        $file = str_replace('})','}',$file);
        $file = json_decode($file,1);
        
        $result = array();
        $result['article_id'] = $channel_id.'-'.$ntes_id;
        $result['title'] = $file['setname'];
        $result['short_summary'] = $file['desc'];
        $result['story_data'] = $file['datatime'];
        
        $content = '';
        $images = array();
        foreach ($file['photos'] as $row){
            $img_html = "<p><img src='{$row['imgurl']}' /></p>";
            $content .= $img_html."<p>{$row['note']}</p>";
            $images[] = $row['imgurl'];
        }
        
        $result['content'] = $content;
        $result['images'] = $images;
        
        $result['title_pic1'] = $result['images'][0];
        if(count($result['images']) > 2){
            $result['title_pic2'] = $result['images'][1];
            $result['title_pic3'] = $result['images'][2];
        }
        
        $result['http'] = $http_response_header[0];
        
        return $result;
    }
    
    /**
     * 分析url内容
     *
     * @param string $url
     * @return mixed
     */
    public function parsePage($url){
        $url_arr = parse_url($url);
        
        if($url_arr['host'] == '3g.163.com'){
            return $this -> ntesContent($url);
        }
        
        if($url_arr['host'] == 'toutiao.com'){
            return $this -> toutiaoContent($url);
        }
        
        if($url_arr['host'] == 'm.toutiao.com'){
            return $this -> toutiaoContent($url);
        }
        
        return false;
    }
    
    /**
     * 获取文章中的图片
     *
     * @param string $string
     * @return mixed
     */
    public function img($string){
        $html = str_get_html($string);
        $imgs = $html -> find('img');
        $res = array();
        foreach ($imgs as $img){
            $res[] = $img -> src;
        }
        return $res;
    }
    
    /**
     * 根据车系id抓取汽车日报的行情新闻
     * @param unknown_type $beseiesId
     * return arr
     */
    public function fetchAutodHangQing($beseiesId){
    	$url = "http://m.news18a.com/{$beseiesId}/news/hangqing.html";
    	
    	$string = file_get_contents($url);
    	
    	$html = str_get_html($string);
    	
    	$hangQing = @$html -> find(".ina_dl dl");
    	$arr = array();
    	
    	foreach ( $hangQing as $k => $row ) {
    		@$arr[$k]['href'] = 'http://m.news18a.com/' . $row->find('a',0)->href ;
    		@$arr[$k]['content'] = $row->find('a',0)->innertext ;
    	}

    	$html->clear();
    	return $arr;
    }
     
}