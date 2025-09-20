<?php

namespace app\admin\controller\supply;

use app\common\controller\Backend;
use app\admin\model\supply\Supply;

/**
 * 会员余额变动管理
 *
 * @icon fa fa-circle-o
 */
class Usdtlog extends Backend
{

    /**
     * Usdtlog模型对象
     * @var \app\admin\model\supply\Usdtlog
     */
    protected $model = null;
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\supply\Usdtlog;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("flowTypeList", $this->model->getFlowTypeList());

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {

        $supplyModel = new Supply();
        $admin_id = $this->auth->id;
        if ($admin_id < 5) {
            $admin_id = 0;
        }

        $admin_ids_str = "%A" . $admin_id . "A%";
        $supply_info = $supplyModel->whereLike("admin_id", $admin_ids_str)->find();

        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    ->with(['supply'])
                    ->where('supply_id', $supply_info['access_key'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                $row->getRelation('supply')->visible(['title']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
