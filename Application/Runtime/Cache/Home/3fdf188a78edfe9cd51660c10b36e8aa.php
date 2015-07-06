<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
<!-- <meta content="width=device-width, initial-scale=1, maximum-scale=1" name="viewport"> -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no"/>
<meta name="viewport" content="target-densitydpi=device-dpi,width=750">
<meta charset="UTF-8">
<title><?php echo ($_PAGE['title']); ?></title>
<link rel="stylesheet" href="/Public/css/style.css">
<script src="/Public/js/jquery.js"></script>
</head>
<body style="font-size:36px;background:#fafafa;">
    <div class="video_con" id="youkuplayer"></div>

<script type="text/javascript" src="http://player.youku.com/jsapi">
player = new YKU.Player('youkuplayer',{
styleid: '1',
client_id: 'f70ac119a85b248e',

vid: '<?php echo ($video["videoid"]); ?>',

show_related: false
});
</script>

<div class="news_cont">
    <div class="news_title">
        <h1><?php echo ($info["title"]); ?></h1>
        <p><span><?php echo ($info["source"]); ?></span><span><?php echo ($info["story_date"]); ?></span></p>
    </div>
</div>
<div class="line"></div>
<div class="comment">
    <h4><span>热门评论</span></h4>
    <?php if($comments): ?><ul class="com_box">
        <?php if(is_array($comments)): foreach($comments as $key=>$vo): ?><li>
            <div class="head_img"><img src="<?php echo ($vo["userphoto"]); ?>" alt="<?php echo ($vo["username"]); ?>"></div>
            <div class="com_cont">
                <div class="user_name"><?php echo ($vo["username"]); ?></div>
                <p><?php echo ($vo["post"]); ?></p>
                <span class="dianzan">
                    <span><?php echo ($vo["like_count"]); ?></span>
                    <a href="#" class="hand"></a>
                </span>
            </div>
        </li><?php endforeach; endif; ?>
    </ul>
    <?php else: ?>
    暂无评论<?php endif; ?>
</div>
<div class="line"></div>
<div class="other_news">
    <h4><span>相关新闻</span></h4>
    <ul class="x_news">
        <?php if(is_array($relates)): foreach($relates as $key=>$vo): ?><li><a href="/Home/Index/page?id=<?php echo ($vo["id"]); ?>"><?php echo ($vo["title"]); ?></a></li><?php endforeach; endif; ?>
    </ul>
</div>


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

</body>
</html>