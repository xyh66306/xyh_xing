<?php

namespace app\admin\controller\supply;

use app\common\controller\Backend;
use app\admin\model\supply\Supply;
use app\admin\model\supply\Usdtlog;
use app\admin\model\company\Account;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use think\Db;
use think\db\exception\BindParamException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

/**
 * 商户充值
 *
 * @icon fa fa-circle-o
 */
class Recharge extends Backend
{

    /**
     * Recharge模型对象
     * @var \app\admin\model\supply\Recharge
     */
    protected $model = null;

    protected $noNeedLogin = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\supply\Recharge;
        $this->view->assign("payStatusList", $this->model->getPayStatusList());
        $this->view->assign("diquList", $this->model->getDiquList());
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
                    ->with(['supply'])
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


    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }

        $order_id = "o" . date("Ymdhis", time()) . rand(100000, 999999);

        $params['order_id'] = $order_id;
        $params['status'] = 1;
        $params['pay_status'] = 2;

        $admin_id = $this->auth->id;

        $group_ids = $this->auth->getGroupIds($this->auth->id);
        if ($group_ids[0] == 7) {
            $params['diqu'] = 1;
        } elseif ($group_ids[0] == 8) {
            $params['diqu'] = 2;
        } elseif ($group_ids[0] == 9) {
            $params['diqu'] = 3;
        }


        $supplyModel = new Supply();

        if ($admin_id < 5) {
            $admin_id = 0;
        }

        $admin_ids_str = "%A" . $admin_id . "A%";
        $supply_info = $supplyModel->whereLike("admin_id", $admin_ids_str)->find();


        if (!$supply_info) {
            $this->error("供应商不存在");
        }

        $params['supply_id'] = $supply_info['access_key'];


        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }


    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {

            if($params['pay_status']==3){
                $Usdtlog = new Usdtlog();
                $Usdtlog->addLog($row['supply_id'], $row['usdt'], 2, 1, '充值');

                $AccountModel = new Account();
                $AccountModel->addLog($row['usdt'],7,3,1,$row['id']);
            }

            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }    


    public function supply()
    {
        // //当前是否为关联查询
        // $this->relationSearch = true;
        // //设置过滤方法
        // $this->request->filter(['strip_tags', 'trim']);
        // if ($this->request->isAjax()) {
        //     //如果发送的来源是Selectpage，则转发到Selectpage
        //     if ($this->request->request('keyField')) {
        //         return $this->selectpage();
        //     }
        //     list($where, $sort, $order, $offset, $limit) = $this->buildparams();

        //     $list = $this->model
        //             ->with(['supply'])
        //             ->where($where)
        //             ->order($sort, $order)
        //             ->paginate($limit);

        //     foreach ($list as $row) {
                
        //         $row->getRelation('supply')->visible(['title']);
        //     }

        //     $result = array("total" => $list->total(), "rows" => $list->items());

        //     return json($result);
        // }
        // return $this->view->fetch();

        $order = "id desc";
        $page = input('page',1);
        $supplyModel = new Supply();
        $admin_id = $this->auth->id;
        if ($admin_id < 5) {
            $admin_id = 0;
        }

        $admin_ids_str = "%A" . $admin_id . "A%";
        $supply_info = $supplyModel->where("admin_id", 'like',$admin_ids_str)->find();

        $list = $this->model
            ->where('supply_id', $supply_info['access_key'])
            ->order($order)
            ->paginate(10);

        foreach ($list as &$row) { 
            $row['createtime'] = date("Y-m-d H:i:s", $row['createtime']);
            $row['updatetime'] = date("Y-m-d H:i:s", $row['updatetime']);
        }



        $this->view->assign("supply_info", $supply_info);

        // 获取分页显示
        $page = $list->render();
        // 模板变量赋值
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->view->fetch();


    }

}
