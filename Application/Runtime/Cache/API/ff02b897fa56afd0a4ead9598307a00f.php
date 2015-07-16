<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
<meta content=" initial-scale=1, maximum-scale=1" name="viewport">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no"/>
<meta charset="UTF-8">
<title><?php echo ($_PAGE['title']); ?></title>
<link rel="stylesheet" href="<?php echo ($host); ?>/Public/css/style.css">
<script src="<?php echo ($host); ?>/Public/js/jquery.js"></script>
</head>
<body>
    <article class="con_box">
        <div class="article_box">
            <div>
            <?php echo ($page); ?>
            </div>
        </div>
    </article>
    <script>
    <?php if($type == 'iphone'): ?>$().ready(function(){
        $('img').click(function(){
            var url="autod://com.auto/news_pics_detail/?newsId=<?php echo ($news_id); ?>&url="+$(this).attr('src');
            //alert(url);
            window.location.href=url;
        });
    });
    <?php else: ?>
    
    $().ready(function(){
        $('img').click(function(){
            var url = $(this).attr('src');
            window.jscalljava.displayImage(url);
        });
    });
    <?php endif; ?>
    </script>
</body>
</html>