<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
<meta content=" initial-scale=1, maximum-scale=1" name="viewport">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no"/>
<meta charset="UTF-8">
<title><?php echo ($_PAGE['title']); ?></title>
<link rel="stylesheet" href="/Public/css/style.css">
<script src="/Public/js/jquery.js"></script>
</head>
<body class="pat">
    <div class="app_banner"></div>
    <?php if(0): ?><div class="bar_top"> 
        <a href="/Home/Index/source?id=<?php echo ($source["id"]); ?>"> 
            <div class="avatar"> 
                <img src="<?php echo ($source["icon"]); ?>" class="avatar" alt="<?php echo ($source["name"]); ?>">
            </div> 
            <span class="name"><?php echo ($source["name"]); ?></span> 
        </a> 
    </div><?php endif; ?>
    <div class="video_con" id="youkuplayer"></div>

<script type="text/javascript" src="http://player.youku.com/jsapi">
player = new YKU.Player('youkuplayer',{
styleid: '1',
client_id: 'f70ac119a85b248e',

vid: '<?php echo ($video["videoid"]); ?>',

show_related: false
});
</script>

    <article class="con_box">
        <h1 class="title"><?php echo ($info["title"]); ?></h1>
        <div class="subtitle"> 
            <span><?php echo ($source["name"]); ?></span>
            <span><?php echo ($source["story_date"]); ?></span>
            <span class="fr"><?php echo ($info["hot"]); ?>热度</span>
        </div>
    </article>
    
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
                        <span><?php echo ($vo["like_count"]); ?></span>
                        <a class="hand" href="#"><img src="/Public/images/hand.png" alt=""></a>
                        <a class="comment" href="#"><img src="/Public/images/comment.png" alt=""></a>
                    </div>
                    <p><?php echo ($vo["post"]); ?></p>
                    <?php if($vo.reply_id): ?><div class="bef_com">
                        <span></span>
                        <p>原评论：<?php echo ($vo["reply_username"]); ?></p>
                        <p><?php echo ($vo["reply_post"]); ?></p>
                    </div><?php endif; ?>
                </div>
            </li><?php endforeach; endif; ?>
        </ul>
    </div>
    <div class="comment_app_download">
        <a href="">打开汽车日报，查看<?php echo ($info["comments_count"]); ?>条评论</a>
    </div>
    <?php else: ?>
    <div class="comment-empty comment-share-container">暂时没有评论</div>
    <div class="comment_app_download">
        <a href="">进入汽车日报发表评论</a>
    </div><?php endif; ?>
    
    <?php if($ad): ?><div class="ad_box">
        <a href="<?php echo ($ad["gourl"]); ?>">
            <div class="ad_title"><?php echo ($ad["title"]); ?></div>
            <img src="<?php echo ($ad["images"]["0"]); ?>">
        </a>
    </div><?php endif; ?>
    
    <div class="news_box">
        <div class="com_title">
            <h2>更多视频</h2>
        </div>
        <div class="list_box video_box_list">
        <?php if(is_array($relates)): foreach($relates as $key=>$vo): if($vo["displayMode"] == 'A'): ?><section class="box">
                <a href="/Home/Index/page?id=<?php echo ($vo["id"]); ?>">
                    <div class="img_box">
                        <img src="<?php echo ($vo["images"]["0"]); ?>" alt="">
                    </div>
                    <h3><?php echo ($vo["title"]); ?></h3>
                    <div class="item_info">
                        <span class="time"><?php echo (date("m-d",strtotime($vo["postTime"]))); ?></span>
                        <span class="source">热度</span>
                        <span class="type"><?php echo ($vo["hot"]); ?></span>
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
                        <span class="time"><?php echo (date("m-d",strtotime($vo["postTime"]))); ?></span>
                        <span class="source">热度</span>
                        <span class="type"><?php echo ($vo["hot"]); ?></span>
                    </div>
                </a>
            </section><?php endif; endforeach; endif; ?>
        </div>
    </div>
    <div class="comment_app_download">
        <a href="">都翻到这了，就安装个汽车日报吧</a>
    </div>
    <br/>
</body>

<script>
function boxHeight(){
    var videoBox=$('.video_con')[0];
    videoBox.style.height=(videoBox.offsetWidth*10/16)+'px';
}
boxHeight();
window.onresize=function(){
    boxHeight();
}
</script>

</html>