<?php


namespace app\admin\controller\mall;



use app\admin\model\MallCate;
use app\admin\service\TriggerService;
use app\admin\constants\AdminConstant;
use app\admin\controller\Base;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use app\admin\traits\Curd;
/**
 * Class Admin
 * @package app\admin\controller\system
 * @ControllerAnnotation(title="商品分类管理")
 */
class Cate extends Base
{

    use Curd;
    

    public function __construct()
    {
        $this->model = new MallCate();
    }

}
