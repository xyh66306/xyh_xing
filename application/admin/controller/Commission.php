<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\model\User as UserModel;
use app\common\model\company\Profit;
use think\Db;
use Exception;
use think\db\exception\BindParamException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;

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
                
              $row->getRelation('puser')->visible(['username']);
                $row->getRelation('user')->visible(['username']);
            }

            $total = $this->model->with(['user','puser'])->where($where)->sum("money");
            $result = array("total" => $list->total(), "rows" => $list->items(),"extend" => compact('total'));

            return json($result);
        }
        return $this->view->fetch();
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

        // if($row['chaoshi'] ==2){
        //   $this->error('订单已超时，请勿操作！');
        // }
        if($row['order_status'] !=2){
          $this->error('订单未完成，请勿操作！');
        }
        if($row['status'] ==1){
          $this->error('订单已分润，请勿操作！');
        }         

        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $type =0;
            if($row['source']==1){
              $type =9;
            } elseif($row['source']==2){
              $type =10;
            }            
            if($row['chaoshi'] ==1){           
              $userModel = new UserModel();
              $res = $userModel->usdt($row['money'],$row['p_userid'],5,1,$row['p4b_orderid']);

              $profitModel = new Profit();
              $res = $profitModel->addLog($row['number'],$row['money'],$type,1,2,$row['p4b_orderid']);    
            } else {
              $profitModel = new Profit();
              $res = $profitModel->addLog($row['number'],$row['money'],$type,1,1,$row['p4b_orderid']);    
            }        
            if(!$res){
              $this->error('分润失败！');
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

}
