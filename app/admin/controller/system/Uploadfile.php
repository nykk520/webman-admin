<?php

// +----------------------------------------------------------------------
// | EasyAdmin
// +----------------------------------------------------------------------
// | PHP交流群: 763822524
// +----------------------------------------------------------------------
// | 开源协议  https://mit-license.org 
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zhongshaofa/EasyAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller\system;


use app\admin\model\SystemUploadfile;
use app\admin\controller\Base;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;


/**
 * @ControllerAnnotation(title="上传文件管理")
 * Class Uploadfile
 * @package app\admin\controller\system
 */
class Uploadfile extends Base
{

    use \app\admin\traits\Curd;

    public function __construct()
    {
        
        $this->model = new SystemUploadfile();
    }

}