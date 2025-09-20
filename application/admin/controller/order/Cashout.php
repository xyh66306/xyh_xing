<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;

/**
 * 订单兑入
 *
 * @icon fa fa-circle-o
 */
class Cashout extends Backend
{

    /**
     * Cashout模型对象
     * @var \app\admin\model\order\Cashout
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\order\Cashout;
        $this->view->assign("payTypeList", $this->model->getPayTypeList());
        $this->view->assign("biTypeList", $this->model->getBiTypeList());
        $this->view->assign("payStatusList", $this->model->getPayStatusList());
        $this->view->assign("statusList", $this->model->getStatusList());
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
                    ->with(['user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                $row->visible(['id','order_id','pay_type','bi_type','act_num','num','rate','receive_name','bank_name','bank_account','bank_zhihang','pinzheng_image','pay_status','pingtai','ctime','utime','status']);
                $row->visible(['user']);
				$row->getRelation('user')->visible(['nickname']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
