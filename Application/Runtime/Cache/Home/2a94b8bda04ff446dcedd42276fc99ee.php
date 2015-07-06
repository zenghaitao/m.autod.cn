<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
<meta content="width=device-width, initial-scale=1, maximum-scale=1" name="viewport">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no"/>
<meta charset="UTF-8">
<title>汽车日报</title>
<link rel="stylesheet" href="/Public/css/style.css">
<script src="/Public/js/jquery.js"></script>
</head>
<body>
<section class="slider_bar">
    <ul>
        <li><a href="#">头条</a></li>
        <li><a href="#">视频</a></li>
        <li><a href="#">新车</a></li>
        <li><a href="#">评测</a></li>
        <li><a href="#">导购</a></li>
        <li><a href="#">行业</a></li>
        <li><a href="#">新闻</a></li>
        <li><a href="#">找车</a></li>
    </ul>
</section>
<header data-role="header">
    <h2>汽车日报</h2>
    <a class="head_menu" href="javascript:void(0);">
        <div></div><div></div><div></div>
    </a>
</header>
<div class="container">
    <div class="list_box">
    </div>
    <section class="load_img"><img src="/Public/images/loading.gif" alt=""></section>
</div>
</body>

<script>
var sinceId = 0;
var maxId = 0;

//创建新闻列表
function creatNews(id,displayMode,oBox,imgsrc,title,time,type,hot,gourl){
    var oType,aImg;
    switch(type){
        case 'default':
            oType='';
            break;
        case 'hot':
            oType='<span class="type1">热点</span>';
            break;
        case 'head':
            oType='<span class="type2">头条</span>';
            break;
        case 'ad':
            oType='<span class="type3">推广</span>';
            break;
    }
    if(displayMode=='A'){
        oBox.append('<section class="box"><a href="/Home/Index/page?id='+id+'"><div class="img_box"><img src="'+imgsrc+'" alt=""></div><h3>'+title+'</h3><div class="item_info"><span class="time">'+time+'</span><span class="source">热度'+hot+'</span>'+oType+'</div></a></section>');
    }else if(displayMode=='B'){
        aImg='<div class="img_cont"><div class="img_box2"><img src="'+imgsrc[0]+'" alt=""></div><div class="img_box2"><img src="'+imgsrc[1]+'" alt=""></div><div class="img_box2"><img src="'+imgsrc[2]+'" alt=""></div></div>';
        oBox.append('<section class="box"><a href="/Home/Index/page?id='+id+'"><h3 class="long_title">'+title+'</h3>'+aImg+'<div class="item_info item_info2"><span class="time">今天</span><span class="source">热度'+hot+'</span>'+oType+'</div></a></section>');
    }else if(displayMode=='C'){
        oBox.append('<section class="box"><a href="'+gourl+'" target="_blank"><h3 class="long_title">'+title+'</h3><div class="ad_cont"><img src="'+imgsrc+'" alt=""></div><div class="item_info item_info2"><span class="time">今天</span>'+oType+'</div></a></section>');
    }
}
$(function(){
    //获取新闻列表
    $.ajax({
        url:'/Home/Index/news?jsoncallback=?',
        type:'get',
        dataType:'jsonp',
        success:function(data){
            //data.info.statuses.length
            for(var i=0;i<data.info.statuses.length;i++){
                
      creatNews(data.info.statuses[i].id,data.info.statuses[i].displayMode,$('.list_box'),data.info.statuses[i]['images'],data.info.statuses[i].title,data.info.statuses[i].postTime,data.info.statuses[i]['type'],data.info.statuses[i].hot,data.info.statuses[i].gourl)
            }
            sinceId = data.info.sinceId;
            maxId = data.info.maxId;
        }
    })
    
    //上拉刷新
    var dataOff = true;
    $(window).scroll(function(){
        if(getDataCheck() && dataOff){
            getNewList();
        }
    })
    function getDataCheck(){
        var oBox = $('.list_box');
        var aList =oBox.find('section');
        var lastboxHeight = $(aList[aList.length-1]).offset().top+Math.floor($(aList[aList.length-1]).outerHeight()/2);
        var documentHeight = $(window).height();
        var scrollTop = $(document).scrollTop();
        //if(box.length>=200){return false;}
        return lastboxHeight<documentHeight+scrollTop?true:false;
    }
    function getNewList(){
        $('.load_img').css('display','block');
        dataOff = !dataOff;
        $.ajax({
            url:'/Home/Index/news?page=down&sinceId='+sinceId+'&maxId='+maxId+'&jsoncallback=?',
            type:'get',
            dataType:'jsonp',
            success:function(data){
                $('.load_img').css('display','none');
                dataOff = !dataOff;
                for(var i=0;i<data.info.statuses.length;i++){
                    creatNews(data.info.statuses[i].id,data.info.statuses[i].displayMode,$('.list_box'),data.info.statuses[i]['images'],data.info.statuses[i].title,data.info.statuses[i].postTime,data.info.statuses[i]['type'],data.info.statuses[i].hot,data.info.statuses[i].gourl)
                }
                sinceId = data.info.sinceId;
                maxId = data.info.maxId;
            }
        })
    }






    //侧边栏
    var bSlider=false;
    $('.head_menu').click(function(){
        if(bSlider){$('.slider_bar').stop().animate({'left':'-100%'})
        }else{
            $('.slider_bar').stop().animate({'left':'0px'})
        }
        bSlider=!bSlider;
    })
    $('.slider_bar').click(function(){
        $(this).stop().animate({'left':'-100%'})
        bSlider=!bSlider;
    })

})
</script>

</html>