<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
    
    public function __construct(){
        parent::__construct();        
    }
    
    public function index(){
//        var_dump(__FUNCTION__);
        echo '<html>
<head><title>403 Forbidden</title></head>
<body bgcolor="white">
<center><h1>403 Forbidden</h1></center>
<hr><center>nginx</center>
</body>
</html>
';
    }
}