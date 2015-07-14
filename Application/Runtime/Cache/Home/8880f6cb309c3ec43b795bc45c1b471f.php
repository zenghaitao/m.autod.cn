<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
<meta content="width=device-width, initial-scale=1, maximum-scale=1" name="viewport">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no"/>
<meta charset="UTF-8">
<title><?php echo ($_PAGE['title']); ?></title>
<link rel="stylesheet" href="/Public/css/style.css">
<style>
.mthead{padding: 30px 10px 20px 10px;background: #87b6d4;text-align: center;}
.headbox{width: 16%;overflow: hidden;border-radius: 50%;margin: 0 auto;}
.headbox img{width: 100%;border-radius: 50%;}
.mthead h3{font-size: 16px;white-space:nowrap;color: #fff;margin: 10px 16px;font-weight: normal;}
.mthead p{font-size: 12px;white-space: nowrap;color: #fff;overflow:hidden;}
@media screen and (min-width:360px){
    .mthead h3{font-size: 17px;}
    .mthead p{font-size: 12px;}
}
@media screen and (min-width:400px){
    .mthead h3{font-size: 18px;}
    .mthead p{font-size: 14px;}
}
</style>
</head>
<body>
<div class="container" style="padding-top:0px">
    <div class="mthead">
        <div class="headbox"><img src="<?php echo ($info["icon"]); ?>" alt=""></div>
        <h3><?php echo ($info["name"]); ?></h3>
        <p><?php echo ($info["intro"]); ?></p>
    </div>
    <div class="list_box">
        <?php if(is_array($list)): foreach($list as $key=>$vo): if($vo["displayMode"] == 'A'): ?><section class="box">
            <a href="/Home/Index/page?id=24524">
                <div class="img_box">
                    <img src="<?php echo ($vo["images"]["0"]); ?>" alt="">
                </div>
                <h3><?php echo ($vo["title"]); ?></h3>
                <div class="item_info">
                    <span class="time"><?php echo ($vo["timeString"]); ?></span>
                    <span class="source">热度<?php echo ($vo["hot"]); ?></span>
                </div>
            </a>
        </section><?php endif; ?>
        <?php if($vo["displayMode"] == 'B'): ?><section class="box">
            <a href="/Home/Index/page?id=24517">
                <h3 class="long_title"><?php echo ($vo["title"]); ?></h3>
                <div class="img_cont">
                    <div class="img_box2"><img src="<?php echo ($vo["images"]["0"]); ?>" alt=""></div>
                    <div class="img_box2"><img src="<?php echo ($vo["images"]["1"]); ?>" alt=""></div>
                    <div class="img_box2"><img src="<?php echo ($vo["images"]["2"]); ?>" alt=""></div>
                </div>
                <div class="item_info item_info2">
                    <span class="time"><?php echo ($vo["timeString"]); ?></span>
                    <span class="source">热度<?php echo ($vo["hot"]); ?></span>
                </div>
            </a>
        </section><?php endif; endforeach; endif; ?>
    </div>
</div>
</body>
</html>