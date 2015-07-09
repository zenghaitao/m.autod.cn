<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo ($title); ?> - Auto Daily Dashboard</title>
    <!-- Bootstrap core CSS -->
    <link href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="http://v3.bootcss.com/examples/dashboard/dashboard.css" rel="stylesheet">
    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="http://v3.bootcss.com/examples/dashboard/../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="http://v3.bootcss.com/examples/dashboard/../../assets/js/ie-emulation-modes-warning.js"></script>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/" target="_blank">汽车日报</a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
      <ul class="nav navbar-nav navbar-right">
        <li><a href="#">User Name</a></li>
        <li><a href="#">退出</a></li>
      </ul>
      <form class="navbar-form navbar-right">
        <input type="text" class="form-control" placeholder="Search...">
      </form>
    </div>
  </div>
</nav>
    <div class="container-fluid">
      <div class="row">
      <div class="col-sm-3 col-md-2 sidebar">
<?php if(is_array($menu)): foreach($menu as $key=>$group): ?><ul class="nav nav-sidebar">
<?php if(is_array($group)): foreach($group as $key=>$row): ?><li <?php if($action == $row['action']): ?>class="active"<?php endif; ?>>
    <a href="<?php echo ($row["url"]); ?>"><?php echo ($row["name"]); ?></a>
  </li><?php endforeach; endif; ?>
  </ul><?php endforeach; endif; ?>
</div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          <h1 class="sub-header"><?php echo ($title); ?></h1>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>标题</th>
                  <th>日期</th>
                  <th>来源</th>
                  <th>分类</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                <?php if(is_array($list)): foreach($list as $key=>$vo): ?><tr>
                  <td><?php echo ($vo["id"]); ?></td>
                  <td><?php if($vo["title_pic1"] != ''): ?><a href="<?php echo ($vo["title_pic1"]); ?>" target="_blank"><img src="<?php echo ($vo["title_pic1"]); ?>" height="70px;"/></a><?php endif; if($vo["title_pic2"] != ''): ?>&nbsp;<a href="<?php echo ($vo["title_pic2"]); ?>" target="_blank"><img src="<?php echo ($vo["title_pic2"]); ?>" height="70px;"/></a><?php endif; if($vo["title_pic3"] != ''): ?>&nbsp;<a href="<?php echo ($vo["title_pic3"]); ?>" target="_blank"><img src="<?php echo ($vo["title_pic3"]); ?>" height="70px;"/></a><?php endif; ?><br/><?php echo ($vo["title"]); ?>
                  </td>
                  <td><?php echo ($vo["story_date"]); ?></td>
                  <td><?php echo ($vo["source"]); ?></td>
                  <td>
<select class="form-control" style="width:100px">
<option value="0">请选择</option>
<?php if(is_array($catelist)): foreach($catelist as $key=>$row): ?><option value="<?php echo ($row["id"]); ?>"><?php echo ($row["name"]); ?></option><?php endforeach; endif; ?>
</select>
                 </td>
                  <td>
                  <?php if($action == 'story'): ?><a href="<?php echo ($vo["id"]); ?>">上推</a><?php endif; ?>
                  <?php if($action == 'choice'): ?><a href="<?php echo ($vo["id"]); ?>">删除</a><?php endif; ?>
                  <?php if($action == 'catenews'): ?><a href="###" storyid="<?php echo ($vo["id"]); ?>" class="cate">保存</a><?php endif; ?>
                  </td>
                </tr><?php endforeach; endif; ?>
              </tbody>
            </table>
<nav>
  <ul class="pagination">
    <li>
      <a href="<?php echo ($page["first"]["url"]); ?>" aria-label="Previous">
        <span aria-hidden="true">&laquo;</span>
      </a>
    </li>
    <?php if(is_array($page["arr"])): foreach($page["arr"] as $key=>$row): if($row["active"] == 'yes'): ?><li class="active"><?php else: ?><li><?php endif; ?><a href="<?php echo ($row["url"]); ?>"><?php echo ($row["num"]); ?></a></li><?php endforeach; endif; ?>
    <li>
      <a href="<?php echo ($page["end"]["url"]); ?>" aria-label="Next">
        <span aria-hidden="true">&raquo;</span>
      </a>
    </li>
  </ul>
</nav>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="http://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
    <script src="http://cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="http://v3.bootcss.com/examples/dashboard/../../assets/js/ie10-viewport-bug-workaround.js"></script>
<script>
$().ready(function(){
    $(".cate").click( function () { 
        var storyid = $(this).attr("storyid");
        var cateid = $(this).parent().parent().find('select').eq(0).val();
        if(cateid == 0){
            alert('请选择分类');
            return false;
        }
        alert(12);
    });
});
</script>
  </body>
</html>