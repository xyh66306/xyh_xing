<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use app\common\model\Task;
use app\admin\model\supply\Supply;
use app\admin\model\supply\Usdtlog as SpullyUsdtLog;
use app\admin\model\user\usdt\Log as UsdtLog;
use app\common\model\User as UserModel;
use app\common\model\company\Profit as companyProfit;
use app\common\model\Commission;
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
 * 订单兑入
 *
 * @icon fa fa-circle-o
 */
class Rujin extends Backend
{

    protected $noNeedRight = ['editsupply'];
    /**
     * Rujin模型对象
     * @var \app\admin\model\order\Rujin
     */
    protected $model = null;


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\order\Rujin;
        $this->view->assign("payTypeList", $this->model->getPayTypeList());
        $this->view->assign("payStatusList", $this->model->getPayStatusList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("diquList", $this->model->getDiquList());
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


        $adminAuths = $this->auth->getGroups($this->auth->id);
        $authIds = '';
        if (!empty($adminAuths)) {
            foreach ($adminAuths as $k => $v) {
                $authIds .= $v['id'] . ',';
            }
        }
        $authIds = rtrim($authIds, ',');
        $authGroup = model('auth_group')->where('id', 'in', $authIds)->where('isBoothView', 1)->find();
        $isBoothView = 0;
        if (!empty($authGroup)) {
            $isBoothView = 1;
        }
        $this->assignconfig('isBoothView', $isBoothView);

        $diqu = '';
        $group_ids = $this->auth->getGroupIds($this->auth->id);


        if ($group_ids[0] == 3 || $group_ids[0] == 7) {
            $diqu = 1;
        } elseif ($group_ids[0] == 5 || $group_ids[0] == 8) {
            $diqu = 2;
        } elseif ($group_ids[0] == 6 || $group_ids[0] == 9) {
            $diqu = 3;
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

            if ($diqu) {
                $list = $this->model
                    ->with(['supply'])
                    ->where($where)
                    ->where('diqu', $diqu)
                    ->order($sort, $order)
                    ->paginate($limit);
            } else {
                $list = $this->model
                    ->with(['supply'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);
            }


            foreach ($list as $row) {
                $row->visible(['id', 'orderid', 'merchantOrderNo','amount', 'username', 'bank_name', 'bank_account', 'bank_zhihang', 'pay_account', 'pay_ewm_image', 'pinzheng_image', 'pay_status', 'ctime', 'diqu', 'usdt', 'bi_type', 'payername', 'huilv', 'user_fee', 'supply_fee', 'supply_usdt', 'user_usdt','utime','status']);
                $row->visible(['supply']);
                $row->getRelation('supply')->visible(['title']);
                $row->fee = $row->supply_fee;
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }


    public function supply()
    {

        /*
        $adminAuths = $this->auth->getGroups($this->auth->id);
        $authIds = '';
        if (!empty($adminAuths)) {
            foreach ($adminAuths as $k => $v) {
                $authIds .= $v['id'] . ',';
            }
        }
        $authIds = rtrim($authIds, ',');
        $authGroup = model('auth_group')->where('id', 'in', $authIds)->where('isBoothView', 1)->find();
        $isBoothView = 0;
        if (!empty($authGroup)) {
            $isBoothView = 1;
        }
        $this->assignconfig('isBoothView', $isBoothView);

        $diqu = '';
        $group_ids = $this->auth->getGroupIds($this->auth->id);


        if ($group_ids[0] == 3 || $group_ids[0] == 7) {
            $diqu = 1;
        } elseif ($group_ids[0] == 5 || $group_ids[0] == 8) {
            $diqu = 2;
        } elseif ($group_ids[0] == 6 || $group_ids[0] == 9) {
            $diqu = 3;
        }

        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

        if ($diqu) {
            $list = $this->model
                ->with(['supply'])
                ->where($where)
                ->where('diqu', $diqu)
                ->order($sort, $order)
                ->paginate($limit);
        } else {
            $list = $this->model
                ->with(['supply'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
        }


        foreach ($list as $row) {
            $row->visible(['id', 'orderid', 'amount', 'username', 'bank_name', 'bank_account', 'bank_zhihang', 'pay_account', 'pay_ewm_image', 'pinzheng_image', 'pay_status', 'ctime', 'diqu', 'usdt', 'bi_type', 'payername', 'huilv', 'user_fee', 'supply_fee', 'supply_usdt', 'user_usdt']);
            $row->visible(['supply']);
            $row->getRelation('supply')->visible(['title']);
            $row->fee = $row->supply_fee;
        }

        // 获取分页显示
        $page = $list->render();
        // 模板变量赋值
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->view->fetch();*/

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
                ->where($where)
                ->where('pintai_id', $supply_info['access_key'])
                ->order($sort, $order)
                ->paginate($limit);


            foreach ($list as $row) {
                $row->visible(['id', 'orderid', 'merchantOrderNo','amount','pay_type', 'username', 'bank_name', 'bank_account', 'bank_zhihang', 'pay_account', 'pay_ewm_image', 'pinzheng_image', 'pay_status', 'ctime', 'diqu', 'usdt', 'bi_type', 'payername', 'huilv', 'user_fee', 'supply_fee', 'supply_usdt', 'user_usdt']);
                $row->visible(['supply']);
                $row->getRelation('supply')->visible(['title']);
                $row->fee = $row->supply_fee;
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    public function editsupply($ids = null)
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
            if ($params['pay_status'] == 4 && $row['callback']) {

                $row['act_amount'] = $params['amount'];
                $params['pay_time'] = time();
                //通知买方已确认

                $supplyModel = new Supply();
                $info = $supplyModel->where('access_key', $row['pintai_id'])->find();
                if ($info) {
                    $taskModel = new Task();
                    $data = [
                        'access_key'    => $info['access_key'],
                        'access_secret' => $info['access_secret'],
                        'name' => 'cash',
                        'message' => '',
                        'params' => [
                            'orderid' => $row['orderid'],
                            'url'  => $row['callback'],
                            'pay_status' => 3
                        ]
                    ];
                    $taskModel->addTask($data, "Cash");
                }
                //增加商户USDT
                $SpullyUsdtLog = new SpullyUsdtLog();
                $SpullyUsdtLog->addLog($row['pintai_id'], $row['supply_usdt'], 1, 1, $row['orderid']);

                // //减少USDT //用户没有手续费
                // $UsdtLog = new UsdtLog();
                // $UsdtLog->addLog($row['user_id'], 3, 2, $row['usdt'], $row['orderid']);
                
                //减少用户usdt_dj 冻结金额
                $userModel = new UserModel();
                $userModel->usdt_dj($row['user_usdt'],$row['user_id'], 6, 2);

                // 增加代理商分润

                //添加公司金额
                $companyProfit1 = new companyProfit();
                $companyProfit1->addLog($row['usdt'],$row['supply_fee'],1,1,1,$row['orderid']);   

                $companyProfit2 = new companyProfit();
                $companyProfit2->addLog($row['usdt'],$row['user_fee'],1,3,1,$row['orderid']); 

                //添加代理商佣金
                $commissionModel = new Commission();
                if($row['order_status']==2){
                    $commissionModel->update(['status'=>1,'chaoshi'=>2],['fy_orderid'=>$row['merchantOrderNo']]);
                } else {

                    $comlist = $commissionModel->where("fy_orderid",$row['merchantOrderNo'])->select();
                    $comSum  = $commissionModel->where("fy_orderid",$row['merchantOrderNo'])->sum('money');
                    if($comSum>0){
                        
                        foreach ($comlist as $vo) {
                            $userModel = new UserModel();
                            $userModel->usdt($vo['money'],$vo['p_userid'],5,1,$row['merchantOrderNo']);
                        }

                        $companyProfit3 = new companyProfit();
                        $res5 = $companyProfit3->addLog($row['usdt'],$comSum,10,2,2,$row['merchantOrderNo']); 
                        $commissionModel->update(['status'=>1,'chaoshi'=>1],['fy_orderid'=>$row['merchantOrderNo']]);
                    }                

                }

            }

            //追回订单
            if ($params['pay_status'] == 5 && $row['pay_status']==4) {
                //减少商户USDT
                $SpullyUsdtLog = new SpullyUsdtLog();
                $SpullyUsdtLog->addLog($row['pintai_id'], $row['supply_usdt'], 1, 2,'ping-'. $row['orderid']);

                //增加用户usdt 金额
                $userModel = new UserModel();
                $userModel->usdt($row['user_usdt'],$row['user_id'], 6, 1);

                //减少公司金额
                $companyProfit1 = new companyProfit();
                $companyProfit1->addLog($row['usdt'],$row['supply_fee'],1,1,2,'ping-'.$row['orderid']);   

                $companyProfit2 = new companyProfit();
                $companyProfit2->addLog($row['usdt'],$row['user_fee'],1,3,2,'ping-'.$row['orderid']); 

                //减少代理商佣金
                if($row['order_status']==1){
                    $commissionModel = new Commission();                         
                    $comlist = $commissionModel->where("fy_orderid",$row['merchantOrderNo'])->select();
                    $comSum  = $commissionModel->where("fy_orderid",$row['merchantOrderNo'])->sum('money');
                    if($comSum>0){
                        
                        foreach ($comlist as $vo) {
                            $userModel = new UserModel();
                            $userModel->usdt($vo['money'],$vo['p_userid'],5,2,$row['merchantOrderNo']);
                        }

                        $companyProfit3 = new companyProfit();
                        $res5 = $companyProfit3->addLog($row['usdt'],$comSum,10,2,1,$row['merchantOrderNo']); 
                        $commissionModel->update(['status'=>1,'chaoshi'=>1],['fy_orderid'=>$row['merchantOrderNo']]);
                    }   
                }
            }  

            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }
}
