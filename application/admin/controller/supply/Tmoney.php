<?php

namespace app\admin\controller\supply;

use app\common\controller\Backend;
use app\admin\model\supply\Supply;
use app\admin\model\supply\Money;
use think\Exception;
use think\Db;
use think\db\exception\BindParamException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

class Tmoney extends Backend
{

    protected $model = null;
    protected $noNeedLogin = ['*'];
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\supply\Tmoney;
        $this->view->assign("payStatusList", $this->model->getPayStatusList());

    }

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

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

        return $this->view->fetch('supply/tmoney/default');
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
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            if($params['pay_status']==3){
                $moneyModel = new Money();
                $moneyModel->addLog($params['supply_id'],$params['amount'],'充值审核');
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

    /**
     * 提现申请
     */
    public function tixian(){

        $supplyModel = new Supply();
        $admin_id = $this->auth->id;
        if($admin_id<5){
            $admin_id = 0;
        }

        $admin_ids_str = "%A".$admin_id."A%";
        $supply_info = $supplyModel->whereLike("admin_id",$admin_ids_str)->find();

        $this->view->assign("supply_info",$supply_info);
        return $this->view->fetch();
    }


    public function txadd(){



        $admin_id = $this->auth->id;
        $group_ids = $this->auth->getGroupIds($this->auth->id);
        
        $post = $this->request->post();
        $order_id = "o".date("Ymdhis",time()).rand(100000,999999);

        $post['order_id'] = $order_id;
        $post['status'] = 1;
        $post['pay_status'] = 2;
        if($group_ids[0] == 7){ 
            $post['diqu'] = 1;
        } elseif ($group_ids[0] == 8){
            $post['diqu'] = 2;
        }  elseif ($group_ids[0] == 9) {
            $post['diqu'] = 3;
        }

        $supplyModel = new Supply();

        if($admin_id<5){
            $admin_id = 0;
        }

        $admin_ids_str = "%A".$admin_id."A%";
        $supply_info = $supplyModel->whereLike("admin_id",$admin_ids_str)->find();


        if(!$supply_info){
            $this->error("供应商不存在");
        }
        if($supply_info['money']<$post['amount']){
            $this->error("余额不足");
        }
        $admin_money_txfee = config('site.admin_money_txfee'); //手续费固定金额
        $admin_money_txbili = config('site.admin_money_txbili'); //手续费百分比
        $fee = 0;
        if($admin_money_txfee >=0 && $admin_money_txbili>0){
            $fee = $post['amount'] * $admin_money_txbili / 100;
        } else if($admin_money_txfee>0 && $admin_money_txbili==0){
            $fee = $admin_money_txfee;
        } else {
            $this->error("提现手续费配置错误");
        }

        $money = $post['amount'];
        $post['amount'] = $money - $fee;
        $post['fee'] = $fee;
        $post['supply_id'] = $supply_info['id'];
    
        Db::startTrans();
        try{

            $result = $this->model->save($post);
            $moneyModel = new Money();
            $res =  $moneyModel->addtxLog($supply_info['id'],$money,'提现申请');
            if(!$res){
                 Db::rollback();
                 $this->error("添加失败");
            }
            Db::commit();
        } catch(Exception $e){
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success("添加成功");

    }



    public function chongzhi(){

        $supplyModel = new Supply();
        $admin_id = $this->auth->id;
        if($admin_id<5){
            $admin_id = 0;
        }

        $admin_ids_str = "%A".$admin_id."A%";
        $supply_info = $supplyModel->whereLike("admin_id",$admin_ids_str)->find();

        $this->view->assign("supply_info",$supply_info);
        return $this->view->fetch();
    }


    public function czadd(){



        $admin_id = $this->auth->id;
        $group_ids = $this->auth->getGroupIds($this->auth->id);
        
        $post = $this->request->post();
        $order_id = "o".date("Ymdhis",time()).rand(100000,999999);

        $post['order_id'] = $order_id;
        $post['status'] = 1;
        $post['pay_status'] = 2;
        if($group_ids[0] == 7){ 
            $post['diqu'] = 1;
        } elseif ($group_ids[0] == 8){
            $post['diqu'] = 2;
        }  elseif ($group_ids[0] == 9) {
            $post['diqu'] = 3;
        }

        $supplyModel = new Supply();

        if($admin_id<5){
            $admin_id = 0;
        }

        $admin_ids_str = "%A".$admin_id."A%";
        $supply_info = $supplyModel->whereLike("admin_id",$admin_ids_str)->find();


        if(!$supply_info){
            $this->error("供应商不存在");
        }

        $post['supply_id'] = $supply_info['access_key'];
    
        Db::startTrans();
        try{
            $result = $this->model->save($post);
            if(!$result){
                 Db::rollback();
                 $this->error("添加失败");
            }
            Db::commit();
        } catch(Exception $e){
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success("添加成功");

    }    
}