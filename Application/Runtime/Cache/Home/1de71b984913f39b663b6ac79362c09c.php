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
    
        
        <?php if(is_array($list)): foreach($list as $key=>$row): ?><section class="video_box"><if condition="$key % 2 == 0">
            <div class="video">
                <a href="/Home/Index/page?id=<?php echo ($row["id"]); ?>">
                    <div class="video_link" href="">
                        <img src="<?php echo ($row["images"]["0"]); ?>" alt="">
                        <span><img src="/Public/images/player.png" alt=""></span>
                    </div>
                    <h3><?php echo ($row["title"]); ?></h3>
                    <div class="item_info2">
                        <span class="time">今天</span>
                        <span>热度<?php echo ($row["hot"]); ?></span>
                    </div>
                </a>
            </div>
        </section><?php endforeach; endif; ?>
    </div>
    <section class="load_img"><img src="images/loading.gif" alt=""></section>
</div>
</body>
<script>

</script>
</html>