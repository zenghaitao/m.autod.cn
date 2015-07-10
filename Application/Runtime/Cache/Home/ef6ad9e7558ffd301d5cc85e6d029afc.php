<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>意见反馈</title>
    <link href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
<style>
ul{margin:0px;padding:0px;}
li{list-style:none; margin-top:20px;text-align:center}
.tips{line-height:40px;color:#666;font-size:12px;display:none}
.btn-default{ font-size:14px; padding:10px 16px; width:50%}
</style>
  </head>

  <body>

    <div class="container">
    <ul>
<li><textarea class="form-control" rows="4" placeholder="有什么意见和想法尽管告诉我吧" id="feedback"></textarea></li>
<li><input type="text" class="form-control" placeholder="请输入您的联系方式" id="contacts"></li>
<li><button type="button" class="btn btn-default" id="post">提交反馈信息</button></li>
<li><p class="tips bg-warning">请输入您的反馈意见</p></li>
    </ul>
    
    </div> <!-- /container -->
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script>
$().ready(function(){
    $('#post').click(function(){
        var feedback = $('#feedback').val();
        var contacts = $('#contacts').val();
        
        if(feedback == ''){
            $('.tips').html("请输入您的反馈意见").attr('class','tips bg-warning').fadeIn(function(){
                setTimeout('tips_hide()',1000);
            });
            return false;
        }
        
        if(contacts == ''){
            $('.tips').html("请输入您的联系方式").attr('class','tips bg-warning').fadeIn(function(){
                setTimeout('tips_hide()',1000);
            });
            return false;
        }
        
        $.post("/Home/Index/feedback", { "feedback": feedback , "contacts":contacts },
           function(data){
             if(data == 'succ'){
                 $('.tips').html("我们已接收到了您的意见反馈，谢谢你的厚爱！").attr('class','tips bg-success').fadeIn(function(){
                     $('#feedback').val('');
                     $('#contacts').val('');
                     setTimeout('tips_hide()',1000);
                 });
             }else{
                 $('.tips').html("好像网络不是太通畅，再试一次吧！").attr('class','tips bg-danger').fadeIn(function(){
                     setTimeout('tips_hide()',1000);
                 });
             }
           });
    });
});
function tips_hide(){
    $(".tips").fadeOut(800);
}
</script>


  </body>
</html>