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
<body style="background-color:#000"">
    <div class="video_con" id="youkuplayer"></div>

<script type="text/javascript" src="http://player.youku.com/jsapi"></script>
<script type="text/javascript">
var full = 'no';

player = new YKU.Player('youkuplayer',{
styleid: '1',
client_id: 'f70ac119a85b248e',

vid: '<?php echo ($video["videoid"]); ?>',

show_related: false,
    events:{
        'onSwitchFullScreen': onSwitchFullScreen,
        'onPlayEnd': onPlayEnd,    
    }
});

function onSwitchFullScreen(){
    if(full == 'no'){
        window.jscalljava.fullScreen(1);
        onMaxScreen();
    }else{
        window.jscalljava.fullScreen(0);
        NormalScreen();
    }
}

function onPlayEnd(){
    window.jscalljava.fullScreen(0);
    NormalScreen();
}

//客户端回调
function onMaxScreen() {
//    ChangeAccessor(0);
    var box = document.getElementById('youkuplayer');
    box.style.width = '100%';
    box.style.height = '100%';
    document.body.style.margin = 0;
    full = 'yes';
}
//客户端回调
function NormalScreen()
{
    //alert('normal');
    var box = document.getElementById('youkuplayer');
//    ChangeAccessor(1);
    box.style.width = '100%';
    box.style.height=(box.offsetWidth*10/16)+'px';
    full = 'no';
}  

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