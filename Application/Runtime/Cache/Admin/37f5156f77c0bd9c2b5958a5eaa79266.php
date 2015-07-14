<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title>新闻页</title>
    <script>
    function SetWinHeight(obj) 
    { 
        alert(win.contentDocument.body.offsetHeight);
        var win=obj; 
        if (document.getElementById) 
        { 
            if (win && !window.opera) 
            { 
                if (win.contentDocument && win.contentDocument.body.offsetHeight) 
                    win.height = win.contentDocument.body.offsetHeight; 
                else if(win.Document && win.Document.body.scrollHeight) 
                    win.height = win.Document.body.scrollHeight; 
            } 
        } 
    } 
    </script>
  </head>
  <body>
<iframe width="320" align="center" height="100%" id="win" name="win" onload="Javascript:SetWinHeight(this)" frameborder="0" scrolling="auto" src="http://m.auto.rehao.net/Home/Index/page?id=25488"></iframe> 
  </body>
</html>