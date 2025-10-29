<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 返佣订单
 *
 * @icon fa fa-circle-o
 */
class Commission extends Backend
{

    /**
     * Commission模型对象
     * @var \app\admin\model\Commission
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Commission;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("chaoshiList", $this->model->getChaoshiList());
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

       $diqu = '';
       $group_ids = $this->auth->getGroupIds($this->auth->id);


       if($group_ids[0]==3){
         $diqu =1;
       } elseif($group_ids[0]==5){
         $diqu =2;
       } elseif($group_ids[0]==6){
         $diqu =2;
       }        
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

            if($diqu){
              $list = $this->model
                  ->with(['user','puser'])
                  ->where($where)
                  ->where('user.diqu',$diqu)
                  ->order($sort, $order)
                  ->paginate($limit);
            } else {
              $list = $this->model
                    ->with(['user','puser'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            }

            foreach ($list as $row) {
                
              $row->getRelation('puser')->visible(['nickname']);
                $row->getRelation('user')->visible(['nickname']);
            }

            $total = $this->model->where("status",1)->cache(3600)->sum("money");
            $duiru = $this->model->where("status",1)->where("source",1)->cache(3600)->sum("money");
            $duichu = $this->model->where("status",1)->where("source",2)->cache(3600)->sum("money");

            $result = array("total" => $list->total(), "rows" => $list->items(),"extend" => compact('total','duiru','duichu'));

            return json($result);
        }
        return $this->view->fetch();
    }

}
