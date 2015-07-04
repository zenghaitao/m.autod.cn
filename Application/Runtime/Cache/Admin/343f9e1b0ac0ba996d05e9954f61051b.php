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
    <style>
    .main div{
        margin-bottom:20px;
    }
    #container{
        margin-top:20px;
    }
    .photo_group{
        margin:0px;
        margin-top:40px;
        clear:both;
        list-style:none;
    }
    .photo_group li{
        width:180px;
        height:180px;
        float:left;
    }
    </style>
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
<?php if(is_array($group)): foreach($group as $key=>$row): ?><li>
    <a href="<?php echo ($row["url"]); ?>"><?php echo ($row["name"]); ?></a>
  </li><?php endforeach; endif; ?>
  </ul><?php endforeach; endif; ?>
</div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
        <form id="form1" method="POST" enctype="multipart/form-data">
          <h1 class="sub-header">发布动态</h1>
          
          <div class="col-md-2">
          <select class="form-control" name="uid" id="uid">
             <option value="0">请选择用户</option>
          <?php if(is_array($root_list)): foreach($root_list as $key=>$row): ?><option value="<?php echo ($row["id"]); ?>"><?php echo ($row["name"]); ?></option><?php endforeach; endif; ?>
          </select>
          </div>
          <div class="col-md-2">
          <button type="button" id="sub_btn" class="btn btn-success">发布</button>
          <input type="hidden" name="pics" id="pics" value="" />
          </div>
          <div class="col-md-10">
            <textarea class="form-control" name="contents" rows="4"></textarea>
            
            <div id="container">
                <a class="btn btn-default btn-lg " id="pickfiles" href="#" >
                    <i class="glyphicon glyphicon-plus"></i>
                    <sapn>选择文件</sapn>
                </a>
            </div>
            
          </div>
          <ul id="photo_group" class="photo_group">
          </ul>
        </form>
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

<script type="text/javascript" src="/Public/js/plupload/plupload.full.min.js"></script>
<script type="text/javascript" src="/Public/js/plupload/i18n/zh_CN.js"></script>
<script type="text/javascript" src="/Public/js/qiniu.js"></script>
<script type="text/javascript">
 //引入Plupload 、qiniu.js后

var uploader = Qiniu.uploader({
    runtimes: 'html5,flash,html4',    //上传模式,依次退化
    browse_button: 'pickfiles',       //上传选择的点选按钮，**必需**
    uptoken_url: '/Admin/Index/qiniuToken',            //Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
    // uptoken : '', //若未指定uptoken_url,则必须指定 uptoken ,uptoken由其他程序生成
    unique_names: true, // 默认 false，key为文件名。若开启该选项，SDK为自动生成上传成功后的key（文件名）。
    // save_key: true,   // 默认 false。若在服务端生成uptoken的上传策略中指定了 `sava_key`，则开启，SDK会忽略对key的处理
    domain: 'http://7xjrkc.com1.z0.glb.clouddn.com/',   //bucket 域名，下载资源时用到，**必需**
    container: 'container',           //上传区域DOM ID，默认是browser_button的父元素，
    max_file_size: '4mb',           //最大文件体积限制
    flash_swf_url: '../../Public/js/plupload/Moxie.swf',  //引入flash,相对路径
    max_retries: 3,                   //上传失败最大重试次数
    dragdrop: true,                   //开启可拖曳上传
    drop_element: 'container',        //拖曳上传区域元素的ID，拖曳文件或文件夹后可触发上传
    chunk_size: '4mb',                //分块上传时，每片的体积
    auto_start: true,                 //选择文件后自动上传，若关闭需要自己绑定事件触发上传
    init: {
        'FilesAdded': function(up, files) {
            plupload.each(files, function(file) {
                // 文件添加进队列后,处理相关的事情
                //alert('1');
            });
        },
        'BeforeUpload': function(up, file) {
               // 每个文件上传前,处理相关的事情
              // alert('2');
        },
        'UploadProgress': function(up, file) {
               // 每个文件上传时,处理相关的事情
               //alert('3');
        },
        'UploadComplete': function() {
            //alert('succ');
        },
        'FileUploaded': function(up, file, info) {
            var domain = up.getOption('domain');
            var res = jQuery.parseJSON(info);
            var sourceLink = domain + res.key;
            var sourceLinkThumbs = sourceLink + '?imageView2/1/w/140/h/140';
            
            UploadCmp(sourceLink , sourceLinkThumbs , up);
            
            
        },
        'Error': function(up, err, errTip) {
               //上传出错时,处理相关的事情
        },
        'UploadComplete': function() {
               //队列文件处理完毕后,处理相关的事情
        },
        'Key': function(up, file) {
            // 若想在前端对每个文件的key进行个性化处理，可以配置该函数
            // 该配置必须要在 unique_names: false , save_key: false 时才生效
            var key = "";
            // do something with key here
            return key
        }
    }
});

function UploadCmp(pic , thumbs , up){
    var html = '<li><img class="img-thumbnail" org="'+pic+'" src="'+thumbs+'" /></li>';
    $('#photo_group').append(html);
    var pic_num = $('#photo_group').find('li').length;
    if(pic_num >= 9){
        $('#container').hide();
    }
}

$("#form1").submit(function(){
    if($('#uid').val() == 0){
        alert('请选择用户!');
        return false;
    }
    
    var lis = $('#photo_group').find('li');
    if(lis.length < 1){
        alert('请上传图片!');
        return false;
    }
    var pics = '';
    for(var i = 0 ; i < lis.length ; i ++){
        var img = lis.eq(i).find('img',0);
        if(pics == ''){
            pics = img.attr('org');
        }else{
            pics = pics + ';,;' + img.attr('org');
        }
    }
    $('#pics').val(pics);                                                                                                                                                                                
    
    return true;
});

$('#sub_btn').click(function(){
    $("#form1").submit();
});

// domain 为七牛空间（bucket)对应的域名，选择某个空间后，可通过"空间设置->基本设置->域名设置"查看获取

// uploader 为一个plupload对象，继承了所有plupload的方法，参考http://plupload.com/docs
            
</script>
  </body>
</html>