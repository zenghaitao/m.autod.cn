<?php
namespace API\Controller;
use Think\Upload\Driver\Qiniu\QiniuStorage;

class QiniuController extends BaseController {
    
    public function __construct(){
        parent::__construct();
    }
    
    /**
     * 七牛上传token
     *
     */
    public function token(){
       
        $QN_config = C('UPLOAD_SITEIMG_QINIU');
        //var_dump($QN_config);exit;
        
        $qiniu = new QiniuStorage($QN_config['driverConfig']);
        $token = $qiniu -> UploadToken($QN_config['driverConfig']['secrectKey'] , $QN_config['driverConfig']['accessKey'] ,$param);
        echo json_encode(array('uptoken'=>$token));
        //echo json_encode(array('uptoken'=>'0MLvWPnyya1WtPnXFy9KLyGHyFPNdZceomLVk0c9:gsa0agNkLsn-ChFV2-erE51qs6k=:eyJzY29wZSI6InFpbml1LXBsdXBsb2FkIiwiZGVhZGxpbmUiOjE0MzU2NDY0NTJ9'));
    }
}