<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use app\common\model\Task;
use app\common\model\Supply;
use app\common\model\Bi as BiModel;
use app\admin\model\supply\Usdtlog;
use app\common\model\User as UserModel;
use app\common\model\company\Profit as companyProfit;
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
 * 订单出金管理
 *
 * @icon fa fa-circle-o
 */
class Chujin extends Backend
{

    /**
     * Chujin模型对象
     * @var \app\common\model\order\Chujin
     */
    protected $model = null;
    protected $supply_info = [];

    protected $noNeedRight = ['editsply'];
    //editsply

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\order\Chujin;
        $this->view->assign("payTypeList", $this->model->getPayTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("paystatusList", $this->model->getPayStatusList());


        $supplyModel = new Supply();
        $admin_id = $this->auth->id;
        if ($admin_id < 5) {
            $admin_id = 0;
        }

        $admin_ids_str = "%A" . $admin_id . "A%";
        $supply_info = $supplyModel->whereLike("admin_id", $admin_ids_str)->find();

        $BiModel = new BiModel();
        $biinfo = $BiModel->where("id", 1)->find();
        $fee_dalu_supply_duichu = config('site.fee_dalu_supply_duichu');
        $fee_dalu_supply_duichu = $fee_dalu_supply_duichu / 100;

        $this->supply_info = $supply_info;
        $this->view->assign('supply_info', $supply_info);
        $this->view->assign('supply_fee', $fee_dalu_supply_duichu);
        $this->view->assign('biinfo', $biinfo);

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

    //     $diqu = '';
    //    $group_ids = $this->auth->getGroupIds($this->auth->id);


    //    if($group_ids[0]==3 || $group_ids[0]==7){
    //      $diqu =1;
    //    } elseif($group_ids[0]==5 || $group_ids[0]==8){
    //      $diqu =2;
    //    } elseif($group_ids[0]==6 || $group_ids[0]==9){
    //      $diqu =3;
    //    }


        //当前是否为关联查询
        $this->relationSearch = false;
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
                $row->visible(['id', 'user_id','orderid', 'merchantOrderNo', 'realName', 'cardNumber', 'bankName', 'bankBranchName', 'pay_type', 'pay_account', 'pay_ewm_image', 'user_usdt', 'user_fee', 'supply_fee', 'supply_usdt', 'updatetime', 'status', 'access_key', 'pay_status', 'usdt','withdrawCurrency','pinzheng_image']);

                $row->getRelation('user')->visible(['nickname']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }


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

        unset($params['access_key']);
        // if($this->supply_info['access_key'] == $row['access_key']){            
        //     if($params['supply_usdt'] > $this->supply_info['usdt']){
        //         $this->error('提现数量超出可提现数量');
        //     }
        // }


        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //已支付 执行回调
            if ($params['pay_status'] == 5 && $row['pay_status']<=4) {

                $params['updatetime'] = time();

                $Usdtlog = new Usdtlog();
                //扣除商户冻结金额
                 $res = $Usdtlog->authtxLog($row['access_key'],$row['supply_usdt'],$row['orderid']);
                if(!$res){
                    Db::rollback();
                    $this->error('扣除商户冻结金额失败');                    
                }

                //扣除承兑商冻结金额
                $userModel = new UserModel();
                $res2 = $userModel->usdt_dj($row['user_usdt'],$row['user_id'],7,2);
                if(!$res2){
                    Db::rollback();
                    $this->error('扣除承兑商冻结金额失败');                    
                }

                //添加公司金额
                $companyProfit1 = new companyProfit();
                $res3 =  $companyProfit1->addLog($row['usdt'],$row['supply_fee'],2,3,1,$row['orderid']);  
                if(!$res3){
                    Db::rollback();
                    $this->error('添加公司金额商户手续费失败');                    
                } 

                $companyProfit2 = new companyProfit();
                $res4 = $companyProfit2->addLog($row['usdt'],$row['user_fee'],2,1,1,$row['orderid']); 
                if(!$res4){
                    Db::rollback();
                    $this->error('添加公司金额承兑商手续费失败');                    
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
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    public function editsply($ids = null)
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

        unset($params['access_key']);
        // if($this->supply_info['access_key'] == $row['access_key']){            
        //     if($params['supply_usdt'] > $this->supply_info['usdt']){
        //         $this->error('提现数量超出可提现数量');
        //     }
        // }


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
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }



    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
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

        if($params['supply_usdt'] > $this->supply_info['usdt']){
            $this->error('提现数量超出可提现数量');
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            
            $supplyModel = new Supply();
            $info = $supplyModel->where('access_key', $this->supply_info['access_key'])->find();

            $orderid = getOrderNo();

            //扣除商户冻结金额
            $Usdtlog = new Usdtlog();
            $Usdtlog->addtxLog($info['access_key'],$params['supply_usdt'],2,$orderid,2);

            $params['orderid'] = $orderid;
            $params['merchantOrderNo'] = empty($info['merchantOrderNo'])?date("Ymdhis",time()):$info['merchantOrderNo'];
            $params['pay_type'] = 'bank';
            $params['diqu'] = 1;
            $params['fiatCurrency'] = "USDT";
            $params['withdrawCurrency'] = "USDT";
            $params['pay_status'] = 1;
            // $params['user_fee'] = '7.26';
            //7.2兑出汇率用户

            $BiModel = new BiModel();
            $biinfo = $BiModel->where("id", 1)->find();
            $params['user_usdt'] = sprintf('%.4f',$params['withdrawAmount']/$biinfo['duichu']);
            $params['user_fee'] = $params['usdt'] - $params['user_usdt'];

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


    /***
     * 代理商
     */
    public function supply()
    {

        $supplyModel = new Supply();
        $admin_id = $this->auth->id;
        if ($admin_id < 5) {
            $admin_id = 0;
        }

        $admin_ids_str = "%A" . $admin_id . "A%";
        $supply_info = $supplyModel->whereLike("admin_id", $admin_ids_str)->find();


        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->where('access_key', $supply_info['access_key'])
                ->order($sort, $order)
                ->paginate($limit);


            foreach ($list as $row) {
                $row->visible(['id', 'orderid', 'merchantOrderNo', 'realName', 'cardNumber', 'bankName', 'bankBranchName', 'pay_type', 'pay_account', 'pay_ewm_image', 'user_usdt', 'user_fee', 'supply_fee', 'supply_usdt', 'updatetime', 'status', 'access_key', 'pay_status', 'usdt','withdrawCurrency','pinzheng_image']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();        
        
    }


    public function adds($ids = null)
    { 

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


    public function addru($ids = null)
    { 
        $params = $this->request->post();
        $supplyModel = new Supply();
        $supply_info = $supplyModel->whereLike("access_key",$params['access_key'])->find();
        if(!$supply_info){
            $this->error('商户不存在');
        }

        if($supply_info['usdt']<$params['withdrawAmount']){
            $this->error('余额不足');
        }

        $orderid = getOrderNo();
        $createtime  = $updatetime = time();
        $data = [
            'orderid' => $orderid,
            'realName' => $params['realName'],
            'cardNumber' => $params['cardNumber'],
            'bankName' => $params['bankName'],
            'bankBranchName' => $params['bankBranchName'],
            'pay_type' => 'bank',
            'pintai_id' => $params['access_key'],
            'withdrawAmount'=>$params['withdrawAmount'],
            'withdrawCurrency'=>'CNY',
            'fiatCurrency'=>'CNY',
            'createtime' => $createtime,
            'updatetime' => $updatetime,
        ];

        Db::startTrans();
        try { 
            $res = $this->model->allowField(true)->save($data); 

            $Usdtlog = new Usdtlog();
            $Usdtlog->addtxLog($params['access_key'],$params['withdrawAmount'],2,$orderid,2);    

            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        
        if($res){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }

    }

}
