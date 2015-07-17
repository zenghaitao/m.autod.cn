<?php if (!defined('THINK_PATH')) exit(); if($type != 'list'): ?><!doctype html>
<html lang="en">
<head>
<meta content=" initial-scale=1, maximum-scale=1" name="viewport">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no"/>
<meta charset="UTF-8">
<title><?php echo ($_PAGE['title']); ?></title>
<meta name="keywords" content="<?php echo ($_PAGE['keywords']); ?>">
<meta name="description" content="<?php echo ($_PAGE['description']); ?>">
<link rel="stylesheet" href="/Public/css/style.css">
<script src="/Public/js/jquery.js"></script>
</head>
<body>
    <?php if($comments): ?><div class="com_box">
        <ul class="com_ul">
            <?php if(is_array($comments)): foreach($comments as $key=>$vo): ?><li>
                <div class="head_img"><img src="<?php echo ($vo["userphoto"]); ?>" alt="<?php echo ($vo["username"]); ?>"></div>
                <div class="com_cont">
                    <div class="user_name"><?php echo ($vo["username"]); ?></div>
                    <div class="dianzan">
                        <?php if($vo["like_count"] != 0): ?><span><?php echo ($vo["like_count"]); ?></span><?php endif; ?>
                        <a class="comment" href="autod://com.auto/comments/?newsId=<?php echo ($vo["news_id"]); ?>&replyId=<?php echo ($vo["id"]); ?>"><img src="/Public/images/comment.png" alt=""></a>
                    </div>
                    <p><?php echo ($vo["post"]); ?></p>
                    <?php if($vo["reply_id"] != 0): ?><div class="bef_com">
                        <span></span>
                        <p>原评论：<?php echo ($vo["reply_username"]); ?></p>
                        <p><?php echo ($vo["reply_post"]); ?></p>
                    </div><?php endif; ?>
                </div>
            </li><?php endforeach; endif; ?>
        </ul>
    </div>
    <?php else: endif; ?>
</body>
<script>
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
        var oBox = $('.com_ul');
        var aList =oBox.find('li');
        if(!aList.length) return false;
        var lastboxHeight = $(aList[aList.length-1]).offset().top+Math.floor($(aList[aList.length-1]).outerHeight()/2);
        var documentHeight = $(window).height();
        var scrollTop = $(document).scrollTop();
        //if(box.length>=200){return false;}
        return lastboxHeight<documentHeight+scrollTop?true:false;
    }
    function getNewList(){
        $('.load_img').css('display','block');
        var url = "/Home/Index/myComment?sinceId="+sinceId+"&sessionId="+sessionId;
        dataOff = !dataOff;
        $.ajax({
            url:url,
            type:'get',
            dataType:'json',
            success:function(data){
                sinceId = data.sinceId;
                $('.com_ul').append(data.html);
                $('.load_img').css('display','none');
                if(data.count > 0)
                    dataOff = !dataOff;
            }
        })
    }

</script>
</html>
<?php else: ?>
<?php if(is_array($comments)): foreach($comments as $key=>$vo): ?><li>
    <div class="head_img"><img src="<?php echo ($vo["userphoto"]); ?>" alt="<?php echo ($vo["username"]); ?>"></div>
    <div class="com_cont">
        <div class="user_name"><?php echo ($vo["username"]); ?></div>
        <div class="dianzan">
            <?php if($vo["like_count"] != 0): ?><span><?php echo ($vo["like_count"]); ?></span><?php endif; ?>
            <a class="comment" href="autod://com.auto/comments/?newsId=<?php echo ($vo["news_id"]); ?>&replyId=<?php echo ($vo["id"]); ?>"><img src="/Public/images/comment.png" alt=""></a>
        </div>
        <p><?php echo ($vo["post"]); ?></p>
        <?php if($vo["reply_id"] != 0): ?><div class="bef_com">
            <span></span>
            <p>原评论：<?php echo ($vo["reply_username"]); ?></p>
            <p><?php echo ($vo["reply_post"]); ?></p>
        </div><?php endif; ?>
    </div>
</li><?php endforeach; endif; endif; ?>