<?php


namespace app\admin\controller\mall;


use app\admin\model\MallGoods;
use app\admin\service\TriggerService;
use app\admin\constants\AdminConstant;
use app\admin\controller\Base;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use app\admin\traits\Curd;

/**
 * Class Goods
 * @package app\admin\controller\mall
 * @ControllerAnnotation(title="商城商品管理")
 */
class Goods extends Base
{

    use Curd;

    protected $relationSearch = true;

    public function __construct()
    {
        
        $this->model = new MallGoods();
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
            list($page, $limit, $where) = $this->buildTableParames();
            $count = $this->model
                ->withJoin('cate', 'LEFT')
                ->where($where)
                ->count();
            $list = $this->model
                ->withJoin('cate', 'LEFT')
                ->where($where)
                ->page($page, $limit)
                ->order($this->sort)
                ->select();
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
            ];
            return json($data);
        }
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="入库")
     */
    public function stock()
    {
        $id = input('id');
        $row = $this->model->find($id);
        empty($row) && $this->error('数据不存在');
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [];
            $this->validate($post, $rule);
            try {
                $post['total_stock'] = $row->total_stock + $post['stock'];
                $post['stock'] = $row->stock + $post['stock'];
                $save = $row->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败');
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign('row', $row);
        return $this->fetch();
    }

}