<?php
namespace API\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $this->show('Index','utf-8');
    }
}