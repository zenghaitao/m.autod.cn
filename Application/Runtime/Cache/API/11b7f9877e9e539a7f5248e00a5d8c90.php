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
<body>
    <div class="video_con" id="youkuplayer"></div>

<script type="text/javascript" src="http://player.youku.com/jsapi">
player = new YKU.Player('youkuplayer',{
styleid: '1',
client_id: 'f70ac119a85b248e',

vid: '<?php echo ($video["videoid"]); ?>',

show_related: false
});
</script>

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