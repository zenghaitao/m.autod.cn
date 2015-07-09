<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
<meta content=" initial-scale=1, maximum-scale=1" name="viewport">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no"/>
<meta charset="UTF-8">
<title><?php echo ($_PAGE['title']); ?></title>
<meta name="keywords" content="<?php echo ($_PAGE['keywords']); ?>">
<meta name="description" content="<?php echo ($_PAGE['description']); ?>">
<link rel="stylesheet" href="/Public/css/style.css">
<script src="/Public/js/jquery.js"></script>
</head>
<body class="pat">
    
    <div class="app_banner">
        <img src="/Public/images/icon.png" alt="">
        <p>汽车日报</p>
        <i>新版上线，快来试试吧</i>
        <a href="/Home/Index/download">立即打开</a>
        <img class="close_btn" src="/Public/images/close.png" alt="">
    </div>

    <?php if(0): ?><div class="bar_top"> 
        <a href="/Home/Index/source?id=<?php echo ($source["id"]); ?>"> 
            <div class="avatar"> 
                <img src="<?php echo ($source["icon"]); ?>" class="avatar" alt="<?php echo ($source["name"]); ?>">
            </div> 
            <span class="name"><?php echo ($source["name"]); ?></span> 
        </a> 
    </div><?php endif; ?>
    <article class="con_box">
        <h1 class="title"><?php echo ($info["title"]); ?></h1>
        <div class="subtitle"> 
            <span><?php echo ($source["name"]); ?></span>
            <span><?php echo ($source["story_date"]); ?></span>
            <span class="fr"><?php echo ($info["hot"]); ?>热度</span>
        </div>
        <div class="article_box">
            <div>
            <?php echo ($page); ?>
            </div>
        </div>
        <div class="overflow_show">
            <a class="text_link">
                <i>展开</i><i>全文</i>
            </a>
        </div>
    </article>
    <div class="midd_banner">
        <a href="/Home/Index/download"><img class="banner" src="http://s0.pstatp.com/resource/toutiao_wap/static/image/banner_middle_1bf5188.jpg"></a>
    </div>
    <?php if($ad): ?><div class="ad_box">
        <a href="<?php echo ($ad["gourl"]); ?>">
            <div class="ad_title"><?php echo ($ad["title"]); ?></div>
            <img src="<?php echo ($ad["images"]["0"]); ?>">
        </a>
    </div><?php endif; ?>
    
    <?php if($comments): ?><div class="com_box">
        <div class="com_title">
            <h2>精彩评论</h2>
        </div>
        <ul class="com_ul">
            <?php if(is_array($comments)): foreach($comments as $key=>$vo): ?><li>
                <div class="head_img"><img src="<?php echo ($vo["userphoto"]); ?>" alt="<?php echo ($vo["username"]); ?>"></div>
                <div class="com_cont">
                    <div class="user_name"><?php echo ($vo["username"]); ?></div>
                    <div class="dianzan">
                        <?php if($vo["like_count"] != 0): ?><span><?php echo ($vo["like_count"]); ?></span><?php endif; ?>
                        <a class="hand" href="/Home/Index/download"><img src="/Public/images/hand.png" alt=""></a>
                        <a class="comment" href="/Home/Index/download"><img src="/Public/images/comment.png" alt=""></a>
                    </div>
                    <p><?php echo ($vo["post"]); ?></p>
                    <?php if($vo["reply_id"] != 0): ?><div class="bef_com">
                        <span></span>
                        <p>原评论：<?php echo ($vo["reply_username"]); ?></p>
                        <p><?php echo ($vo["reply_post"]); ?></p>
                    </div><?php endif; ?>
                </div>
            </li><?php endforeach; endif; ?>
        </ul>
    </div>
    <div class="comment_app_download">
        <a href="/Home/Index/download">打开汽车日报，查看<?php echo ($info["comments_count"]); ?>条评论</a>
    </div>
    <?php else: ?>
    <div class="comment-empty comment-share-container">暂时没有评论</div>
    <div class="comment_app_download">
        <a href="/Home/Index/download">进入汽车日报发表评论</a>
    </div><?php endif; ?>
    
    <div class="news_box">
        <div class="com_title">
            <h2>相关阅读</h2>
        </div>
        <div class="list_box">
        <?php if(is_array($relates)): foreach($relates as $key=>$vo): if($vo["displayMode"] == 'A'): ?><section class="box">
                <a href="/Home/Index/page?id=<?php echo ($vo["id"]); ?>">
                    <div class="img_box">
                        <img src="<?php echo ($vo["images"]["0"]); ?>" alt="">
                    </div>
                    <h3><?php echo ($vo["title"]); ?></h3>
                    <div class="item_info">
                        <span class="time">刚刚</span>
                        <span class="source"><?php echo ($vo["source"]); ?></span>
                        <span class="type">热门</span>
                    </div>
                </a>
            </section><?php endif; ?>
            <?php if($vo["displayMode"] == 'B'): ?><section class="box">
                <a href="/Home/Index/page?id=<?php echo ($vo["id"]); ?>">
                    <h3 class="long_title"><?php echo ($vo["title"]); ?></h3>
                    <div class="img_cont">
                        <div class="img_box2">
                            <img src="<?php echo ($vo["images"]["0"]); ?>" alt="">
                        </div>
                        <div class="img_box2">
                            <img src="<?php echo ($vo["images"]["1"]); ?>" alt="">
                        </div>
                        <div class="img_box2">
                            <img src="<?php echo ($vo["images"]["2"]); ?>" alt="">
                        </div>
                    </div>
                    <div class="item_info item_info2">
                        <span class="time">刚刚</span>
                        <span class="source"><?php echo ($vo["source"]); ?></span>
                        <span class="type">热门</span>
                    </div>
                </a>
            </section><?php endif; endforeach; endif; ?>
        </div>
    </div>
    <div class="comment_app_download">
        <a href="/Home/Index/download">都翻到这了，就安装个汽车日报吧</a>
    </div>
    <br/>
</body>
<script>
function addCookie(name,value,iDay){
    var oDate=new Date();
    oDate.setDate(oDate.getDate()+iDay);
    document.cookie=name+'='+value+';path=/;expires='+oDate;
}
function getCookie(name){
    var arr=document.cookie.split('; ');
    for(var i=0; i<arr.length; i++){
        var arr2=arr[i].split('=');
        if(arr2[0]==name){
            return arr2[1]; 
        }
    }
    return '';
}
function delCookie(name){
    addCookie(name,'1',-1);
}
$(function(){
    if($('.article_box')[0].offsetHeight>1200){
        $('.article_box').css('height','1000px');
        $('.overflow_show').css('display','block');
        $('.overflow_show a').click(function(){
            $('.article_box').css('height','auto');
            $(this).css('display','none');
        })
    }
    if(getCookie('downApp_cookie')){
        $('.pat').css({'margin-top':'-50px'})
        $('.app_banner').hide();
    }else{
        $('.pat').css('margin','0')
    }
    $('.close_btn').click(function(){
        $('.app_banner').hide();
        addCookie('downApp_cookie',1)
        $('.pat').css({'padding-top':'-0'})
    })

})
</script>
</html>