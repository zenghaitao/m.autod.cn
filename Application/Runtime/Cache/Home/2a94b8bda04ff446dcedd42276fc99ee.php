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
var action = 'init';
var dataOff = true;

//创建新闻列表
function creatNews( info){
    id = info.id;
    displayMode = info.displayMode;
    imgsrc = info['images'];
    title = info.title;
    time = info.postTime;
    type = info['type'];
    hot = info.hot;
    gourl = info.gourl;
    
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
    
    var html = '';
    
    if(displayMode=='A'){
        html += '<section class="box"><a href="/Home/Index/page?id='+id+'"><div class="img_box"><img src="'+imgsrc+'" alt=""></div><h3>'+title+'</h3><div class="item_info"><span class="time">'+time+'</span><span class="source">热度'+hot+'</span>'+oType+'</div></a></section>';
    }else if(displayMode=='B'){
        aImg='<div class="img_cont"><div class="img_box2"><img src="'+imgsrc[0]+'" alt=""></div><div class="img_box2"><img src="'+imgsrc[1]+'" alt=""></div><div class="img_box2"><img src="'+imgsrc[2]+'" alt=""></div></div>';
        html += '<section class="box"><a href="/Home/Index/page?id='+id+'"><h3 class="long_title">'+title+'</h3>'+aImg+'<div class="item_info item_info2"><span class="time">今天</span><span class="source">热度'+hot+'</span>'+oType+'</div></a></section>';
    }else if(displayMode=='C'){
        html += '<section class="box"><a href="'+gourl+'" target="_blank"><h3 class="long_title">'+title+'</h3><div class="ad_cont"><img src="'+imgsrc+'" alt=""></div><div class="item_info item_info2"><span class="time">今天</span>'+oType+'</div></a></section>';
    }
    return html;
}

function ajaxAPI(){
    if(action != 'up' && action != 'init' && action != 'down'){
        alert(action);
        return false;
    }
    var async_status = true;
    if(action == 'up')
        async_status = false;
    
    $.ajax({
        url:'/Home/Index/news?page='+action+'&maxId='+maxId+'&sinceId='+sinceId+'&jsoncallback=?',
        type:'get',
        dataType:'jsonp',
        async:async_status,  //设置为同步
        success:function(data){
            
            if(action == 'down'){
                $('.load_img').css('display','none');
                dataOff = !dataOff;
            }
            
            var html = '';
            for(var i=0;i<data.info.statuses.length;i++){
                html += creatNews(data.info.statuses[i]);
            }
            sinceId = data.info.sinceId;
            maxId = data.info.maxId;
            if(action == 'up'){
                $('.list_box').eq(0).prepend(html);
                $('.list_top').remove();
            }else{
                $('.list_box').eq(0).append(html);
            }
            
            
        }
    })
}


$(function(){
    //获取新闻列表
    action = 'init';
    ajaxAPI();
    
    //上拉刷新
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
        action = 'down';
        ajaxAPI();
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


var clientH=document.documentElement.clientHeight||document.body.clientHeight;
var vendors=['webkitT','mozT','oT','msT'];
//var endTrue=false;
if($(document).scrollTop()==0){
    document.addEventListener('touchstart',function(ev){
        var downY=ev.targetTouches[0].pageY;
        document.addEventListener('touchmove',function(ev){
            var y = ev.targetTouches[0].pageY - downY;
            $('.list_top').remove();
            //document.removeEventListener('touchend',touceEnd,false)
            if(!$('.list_top').length){
                var oRefresh='<div class="list_top"><div class="v2"><img src="/Public/images/refresh.png" alt=""></div></div>';
                $('body').append(oRefresh);
            }
            if(y>=200)y=200;
            for(var i=0;i<vendors.length;i++){
                $('.list_top')[0].style[vendors[i]+'ransform']='translateY('+y*0.5+'px)';
                $('.v2')[0].style[vendors[i]+'ransform']='rotate('+(Math.abs(y/clientH)*365*5)+'deg)';
            }
        },false);
        function touceEnd(){
            for(var i=0;i<vendors.length;i++){
                $('.list_top')[0].style[vendors[i]+'ransition']='all 0.5s';
                $('.list_top')[0].style[vendors[i]+'ransform']='translateY(60px)';
            }
            $('.list_top')[0].addEventListener('webkitTransitionEnd', function () {
                if(!$('.v2 img')[0])return;
                $('.v2 img')[0].style.webkitAnimation='anime 1s linear infinite';
                
                action = 'up';
                ajaxAPI();
            });
        }
        document.addEventListener('touchend',touceEnd,false);
    },false)
}
</script>

</html>