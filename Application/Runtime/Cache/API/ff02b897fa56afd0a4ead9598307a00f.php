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
<div class="news_cont">
    <div class="content">
    <?php echo ($page); ?>
    </div>
</div>
</body>
</html>