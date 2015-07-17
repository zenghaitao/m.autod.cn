<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html lang="en">
<head>
<meta content=" initial-scale=1, maximum-scale=1" name="viewport">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no"/>
<meta charset="UTF-8">
<title><?php echo ($_PAGE['title']); ?></title>
<link rel="stylesheet" href="/Public/css/style.css">

<style>
*{/*-webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;*/margin: 0;padding: 0;}
html{-ms-touch-action: none;font-size: 62.5%;}
html,body{width: 100%;height: 100%;}
body{/*font-size: 1.4rem;*/color: #222;font-family: "Microsoft Yahei",arial,helvetica,sans-serif;}
img,a,input,div{-webkit-touch-callout:none;-webkit-tap-highlight-color:rgba(0,0,0,0);outline: none;}
img{border: 0;}
a{text-decoration: none;color: #333;}
input{-webkit-appearance:none;border-radius: 0;}
.takebox{padding: 10px;}
.dybtn{height: 30px;border: 1px solid #c42020;border-radius: 10px;line-height: 30px;text-align: center;font-size: 16px;color: #c42020;}
.dybtn span{display: inline-block;width: 10px;height: 10px;border: 2px solid #c42020;border-radius: 50%;font-size: 10px;text-align: center;line-height: 10px;margin-right: 10px;vertical-align: middle;}
.dylist{padding-top: 10px;}
.dylist li{position: relative;padding: 10px 0;border-bottom: 1px solid #dedede;}
.dylist li .imgbox{float: left;width: 60px;height: 60px;border-radius: 50%;overflow: hidden;margin-right: 10px;}
.dylist li .imgbox img{width: 100%;}
.nr{overflow: hidden;padding: 6px 80px 6px 0;}
.nr h3{font-size: 14px;white-space:nowrap;overflow: hidden;}
.nr h3 b{font-weight: normal;max-width: 100px;overflow: hidden;display: inline-block;vertical-align: middle;font-size: 14px;}
.nr h3 i{font-style: normal;font-size: 10px;color: #c4c4c4;margin-left: 10px;}
.nr p{color: #c4c4c4;white-space:nowrap;overflow: hidden;margin-top: 10px;font-weight: normal;font-size: 14px;}
.dylist li span{position: absolute;font-size: 10px;padding: 5px 10px;background: #cecfd0;border-radius: 10px;color: #fff;right: 0;top: 30px; width:50px; text-align:center}
.dylist li span.dy{background:#58baf8;}


@media screen and (min-width:360px){
    .nr h3 b{max-width: 130px;font-size: 16px;}
    .nr h3 i{font-size: 12px;}
    .nr p{margin-top: 10px;font-size: 14px;}
    .dylist li span{font-size: 12px;border-radius: 12px;}
}
@media screen and (min-width:400px){
    .nr{padding: 8px 60px 8px 0;}
    .dylist li .imgbox{width: 70px;height: 70px;}
    .nr h3 b{max-width: 180px;font-size: 18px;}
    .nr h3 i{font-size: 14px;}
    .nr p{margin-top: 10px;font-size: 18px;}
    .dylist li span{font-size: 14px;border-radius: 20px;}
}
</style>

<script src="/Public/js/jquery.js"></script>
</head>
<body>
    <div class="takebox">
        <ul class="dylist">
            <?php if(is_array($list)): foreach($list as $key=>$vo): ?><li sid="<?php echo ($vo["id"]); ?>">
                <div class="imgbox">
                    <img src="<?php echo ($vo["icon"]); ?>" alt="">
                </div>
                <div class="nr">
                    <h3><b><?php echo ($vo["name"]); ?></b><i><?php echo ($vo["last_time"]); ?></i></h3>
                    <p><?php echo ($vo["last_news"]); ?></p>
                </div>
                <?php if($vo["followed"] == 'yes'): ?><span>取消订阅</span>
                <?php else: ?>
                <span class="dy">订阅</span><?php endif; ?>
                
            </li><?php endforeach; endif; ?>
        </ul>
    </div>
<script>
var sessionId = '<?php echo ($session_id); ?>';

$().ready(function(){
    $('.imgbox img').click(function(){
        var url="autod://com.auto/subscribe/?action=showSubscribe&sourceId="+$(this).attr('sid');
        window.location.href=url;
    });
    
    $('.imgbox img').click(function(){
        var url="autod://com.auto/subscribe/?action=showSubscribe&sourceId="+$(this).parent().parent().attr('sid');
        window.location.href=url;
    });
    
    $('.dylist li span').click(function(){
        if($(this).text()=='订阅'){
           $(this).text('取消订阅'); 
           //var url = "autod://com.auto/subscribe/?action=cancelSubscribe&sourceId="+$(this).parent().attr('sid');
           var url = "/API/News/unfollow";
        }else{
           $(this).text('订阅');
           //var url = "autod://com.auto/subscribe/?action=addSubscribe&sourceId="+$(this).parent().attr('sid');
           var url = "/API/News/follow";
        }
        
        var pamra = "sessionId="+sessionId+"&sourceId="+$(this).parent().attr('sid');
        
        $.ajax({
            url:url,
            type:'post',
            data: pamra,
            dataType:'json',
            success:function(data){
            }
        })
        
        $(this).toggleClass("dy");
    });
});

</script>
</body>
</html>