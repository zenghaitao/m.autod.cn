<?php if (!defined('THINK_PATH')) exit(); if($type != 'list'): ?><!doctype html>
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
<script src="/Public/js/jquery.js"></script>
</head>
<body>
<div class="container" style="padding-top:0px">
    <div class="mthead">
        <div class="headbox"><img src="<?php echo ($info["icon"]); ?>" alt=""></div>
        <h3><?php echo ($info["name"]); ?></h3>
        <p><?php echo ($info["intro"]); ?></p>
    </div>
    <div class="list_box"><?php endif; ?>
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
<?php if($type != 'list'): ?></div>
</div>
<script>
var sourceId = '<?php echo ($source_id); ?>';
var sinceId = '<?php echo ($since_id); ?>';
var sessionId = '<?php echo ($session_id); ?>';

//上拉刷新
    var dataOff = true;
    $(window).scroll(function(){
        if(getDataCheck() && dataOff){
            getNewList();
        }
    })
    function getDataCheck(){
        var oBox = $('.list_box');
        var aList =oBox.find('section');
        if(!aList.length) return false;
        var lastboxHeight = $(aList[aList.length-1]).offset().top+Math.floor($(aList[aList.length-1]).outerHeight()/2);
        var documentHeight = $(window).height();
        var scrollTop = $(document).scrollTop();
        //if(box.length>=200){return false;}
        return lastboxHeight<documentHeight+scrollTop?true:false;
    }
    function getNewList(){
        $('.load_img').css('display','block');
        var url = "/Home/Index/source?sourceId="+sourceId+"&sinceId="+sinceId+"&sessionId="+sessionId;
        dataOff = !dataOff;
        $.ajax({
            url:url,
            type:'get',
            dataType:'json',
            success:function(data){
                sinceId = data.sinceId;
                $('.list_box').append(data.html);
                $('.load_img').css('display','none');
                if(data.count > 0)
                    dataOff = !dataOff;
            }
        })
    }

</script>
</body>
</html><?php endif; ?>