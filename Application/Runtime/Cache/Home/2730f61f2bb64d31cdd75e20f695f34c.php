<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <title>帮助中心</title>
    <style>
        body,p {margin:0; padding:0; background:#ebebeb;}
        .swipe {overflow:hidden; visibility:hidden; position:relative;}
        .swipe-wrap {overflow:hidden; position:relative;}
        .swipe-wrap > div {float:left; width:100%; position:relative;}
        .swipe-wrap > div > img {width:100%; display:block;}
    </style>
</head>
<body>
    <div id='slider' class='swipe'>
        <div class='swipe-wrap'>
            <div><img src="http://autod.b0.upaiyun.com/autod_img/source_logo/help_1.png" alt="" /></div>
            <div><img src="http://autod.b0.upaiyun.com/autod_img/source_logo/help_2.png" alt="" /></div>
            <div><img src="http://autod.b0.upaiyun.com/autod_img/source_logo/help_3.png" alt="" /></div>
            <div><img src="http://autod.b0.upaiyun.com/autod_img/source_logo/help_4.png" alt="" /></div>
        </div>
    </div>
<script src="http://app.autod.cn/public/js/swipe.js"></script>
<script>
window.mySwipe = new Swipe(document.getElementById('slider'));
</script>
</body>
</html>