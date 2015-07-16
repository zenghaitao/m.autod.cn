<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Auto Daily Dashboard</title>
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
          <h1 class="sub-header">来源分类</h1>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>名称</th>
                  <th>今日文章量</th>
                  <th>昨日文章量</th>
                  <th>前日文章量</th>
                  <th>文章总量</th>
                  <th>最后更新时间</th>
                </tr>
              </thead>
              <tbody>
              <?php if(is_array($list)): foreach($list as $key=>$vo): ?><tr>
                  <td><?php echo ($vo["id"]); ?></td>
                  <td><?php echo ($vo["name"]); ?></td>
                  <td><?php echo ($vo["today_num"]); ?></td>
                  <td><?php echo ($vo["today_num"]); ?></td>
                  <td><?php echo ($vo["today_num"]); ?></td>
                  <td><?php echo ($vo["num"]); ?></td>
                  <td><?php echo ($vo["last_time"]); ?></td>
                </tr><?php endforeach; endif; ?>
              </tbody>
            </table>
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
  </body>
</html>