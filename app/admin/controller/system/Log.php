<?php

namespace app\admin\controller\system;


use app\admin\model\SystemLog;
use app\admin\controller\Base;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;


/**
 * @ControllerAnnotation(title="操作日志管理")
 * Class Auth
 * @package app\admin\controller\system
 */
class Log extends Base
{

    public function __construct()
    {
        
        $this->model = new SystemLog();
    }

    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        if (request()->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            [$page, $limit, $where, $excludeFields] = $this->buildTableParames(['month']);

            $month = (isset($excludeFields['month']) && !empty($excludeFields['month']))
                ? date('Ym',strtotime($excludeFields['month']))
                : date('Ym');

            // todo TP6框架有一个BUG，非模型名与表名不对应时（name属性自定义），withJoin生成的sql有问题

            
            $list = $this->model
                ->setMonth($month)
                ->with('admin')
                ->where($where)
                ->order($this->sort)
                ->paginate(['list_rows' => $limit, 'page' => $page])
                ->toArray();

            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $list['total'],
                'data'  => $list['data'],
            ];
            return json($data);
        }
        return $this->fetch();
    }

}