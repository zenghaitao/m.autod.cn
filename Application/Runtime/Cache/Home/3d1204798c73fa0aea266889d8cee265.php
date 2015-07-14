<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
<meta content="width=device-width, initial-scale=1, maximum-scale=1" name="viewport">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no"/>
<meta charset="UTF-8">
<title>document</title>
<style>

*{/*-webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;*/margin: 0;padding: 0;}
html{-ms-touch-action: none;font-size: 62.5%;}
html,body{width: 100%;height: 100%;}
body{font-size: 1.4rem;color: #222;font-family: "Microsoft Yahei",arial,helvetica,sans-serif;}
img,a,input,div{-webkit-touch-callout:none;-webkit-tap-highlight-color:rgba(0,0,0,0);outline: none;}
img{border: 0;}
a{text-decoration: none;color: #333;}
ul{list-style: none;}
.fr{float: right;}
.fl{float: left;}
input{-webkit-appearance:none;border-radius: 0;}
.container{background: #f4f4f4;}
.cardbox li{width: 96%;margin: 0 2% 2%;position: relative;box-shadow: 0 1px 2px #d6d6d6;border-bottom: 1px solid #d4d4d4;min-height: 48px;background: #fff;overflow: hidden;}
.icon_wrap{float: left;width: 68px;margin: 15px 10px;}
.icon_wrap img{display: block;}
.app_desc{margin: 15px 0 25px 88px;}
.app_desc a{display: block;height: 1.125em;line-height: 1;margin-top: 3px;width: 150px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;color: #333;font-size: 16px;}
.meta{white-space: nowrap;overflow: hidden;text-overflow: ellipsis;margin-top: 5px;}
.meta span{vertical-align: middle;color: #ccc;font-size: .75em;}
.comment{color: #999;font-size: 13px;margin-top: 8px;margin-right: 15px;height: 60px;line-height: 20px;overflow: hidden;}
.download_link{display: block;letter-spacing: 1px;font-size: .875em;position: absolute;top: 12px;right: 10px;text-align: center;width: 64px;height: 26px;line-height: 26px;color: #fff;background: #5dbbf5;}

</style>
</head>
<body>
<div class="container">
    <ul class="cardbox">
        <?php if(is_array($list)): foreach($list as $key=>$vo): ?><li>
            <div class="icon_wrap">
                <a href="<?php echo ($vo["url"]); ?>"><img src="<?php echo ($vo["icon"]); ?>" width="68" height="68" alt="<?php echo ($vo["name"]); ?>" class="icon"></a>
            </div>
            <div class="app_desc">
                <a href=""><?php echo ($vo["name"]); ?></a>
                <div class="meta">
                    <span>文件大小</span>
                    <span>·</span>
                    <span><?php echo ($vo["size"]); ?>MB</span>
                </div>
                <div class="comment"><?php echo ($vo["intro"]); ?></div>
            </div>
            <a class="download_link" href="<?php echo ($vo["url"]); ?>">下载</a>
        </li><?php endforeach; endif; ?>
    </ul>
</div>
</body>
</html>