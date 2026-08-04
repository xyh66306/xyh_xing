<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use app\common\model\Task;
use app\common\model\Supply;
use app\common\model\Bi as BiModel;
use app\admin\model\supply\Usdtlog;
use app\common\model\User as UserModel;
use app\common\model\company\Profit as companyProfit;
use app\common\library\Sms as Smslib;
use app\common\model\Commission;
use think\Db;
use Exception;
use think\db\exception\BindParamException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\response\Json;
use app\common\library\Ems as Emslib;

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

    protected $noNeedRight = ['editsply', 'import'];
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
        if ($admin_id < 5 || $admin_id == 8 || $admin_id == 13) {
            $admin_id = 0;
        }

        $admin_ids_str = "%A" . $admin_id . "A%";
        $supply_info = $supplyModel->whereLike("admin_id", $admin_ids_str)->find();

        $BiModel = new BiModel();
        $biinfo = $BiModel->where("id", 1)->find();
        // $fee_dalu_supply_duichu = config('site.fee_dalu_supply_duichu');
        $fee_dalu_supply_duichu = isset($supply_info['duichu_fanyong']) ? $supply_info['duichu_fanyong'] : 0;
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
                ->order($sort, $order)
                ->paginate($limit);



            foreach ($list as $k => $row) {

                $row->ptname = Db::name("supply")->where("access_key",$row->access_key)->value("title");

                $row->visible(['id', 'payername', 'user_id', 'orderid', 'merchantOrderNo', 'realName', 'cardNumber', 'bankName', 'bankBranchName', 'pay_type', 'pay_account', 'pay_ewm_image', 'user_usdt', 'user_fee', 'supply_fee', 'supply_usdt', 'updatetime', 'status', 'access_key', 'pay_status', 'usdt', 'withdrawCurrency', 'pinzheng_image', 'withdrawAmount','ptname','createtime']);
            }
            // $supply_price = $this->model->where($where)->sum("supply_usdt");
            // $user_price = $this->model->where($where)->sum("user_usdt");
            // $user_fee = $this->model->where($where)->sum("user_fee");
            // $supply_fee = $this->model->where($where)->sum("supply_fee");

            $supply_price = $this->model->where(['pay_status' => 5])->sum("supply_usdt");
            $user_price = $this->model->where(['pay_status' => 5])->sum("user_usdt");
            $user_fee = $this->model->where(['pay_status' => 5])->sum("user_fee");
            $supply_fee = $this->model->where(['pay_status' => 5])->sum("supply_fee");
            // $amount = $this->model->where(['pay_status'=>4])->sum("amount");

            $company_price =  truncateDecimal($user_fee + $supply_fee);

            $result = array("total" => $list->total(), "rows" => $list->items(), "extend" => compact('supply_price', 'user_price', 'company_price'));

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
        //不支持工商和农业 
        if ($params['bankName'] == '工商银行' || $params['bankName'] == '中国工商银行' || $params['bankName'] == '农业银行' || $params['bankName'] == '中国农业银行') {
            $this->error('不支持工商和农业');
        }
        $isset = Db::name("blacklist")->where("username", $params['realName'])->find();
        if ($isset) {
            $this->error('用户名在黑名单中');
        }

        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //已支付 执行回调
            if ($params['pay_status'] == 5 && $row['pay_status'] <= 4) {

                $params['updatetime'] = time();

                $Usdtlog = new Usdtlog();
                //扣除商户冻结金额
                $res = $Usdtlog->authtxLog($row['access_key'], $row['supply_usdt'], $row['orderid']);
                if (!$res) {
                    Db::rollback();
                    $this->error('扣除商户冻结金额失败');
                }

                //扣除承兑商冻结金额
                $userModel = new UserModel();
                $res2 = $userModel->usdt_dj($row['user_usdt'], $row['user_id'], 7, 2);
                if (!$res2) {
                    Db::rollback();
                    $this->error('扣除承兑商冻结金额失败');
                }


                //添加公司金额
                $spark_id = 168022;
                $userModel = new UserModel();
                $userModel->usdt($row['supply_fee'],$spark_id, 7, 1,$row['merchantOrderNo']);                

                $userModel = new UserModel();
                $userModel->usdt($row['user_fee'],$spark_id, 7, 1,$row['merchantOrderNo']);                  

                // $companyProfit1 = new companyProfit();
                // $res3 =  $companyProfit1->addLog($row['usdt'], $row['supply_fee'], 2, 3, 1, $row['orderid']);
                // if (!$res3) {
                //     Db::rollback();
                //     $this->error('添加公司金额商户手续费失败');
                // }

                // $companyProfit2 = new companyProfit();
                // $res4 = $companyProfit2->addLog($row['usdt'], $row['user_fee'], 2, 1, 1, $row['orderid']);
                // if (!$res4) {
                //     Db::rollback();
                //     $this->error('添加公司金额承兑商手续费失败');
                // }

                //添加代理商佣金
                $commissionModel = new Commission();
                $commissionModel->update(['order_status' => 2], ['fy_orderid' => $row['orderid']]);


                $supplyModel = new Supply();
                $info = $supplyModel->where('access_key', $row['access_key'])->find();
                if ($info && $row['webhookUrl']) {
                    $taskModel = new Task();
                    $data = [
                        'access_key'    => $info['access_key'],
                        'access_secret' => $info['access_secret'],
                        'name' => 'sell',
                        'message' => '',
                        'params' => [
                            'orderid' => $row['merchantOrderNo'],
                            'url'  => $row['webhookUrl'],
                            'pay_status' => 3   //已支付   
                        ]
                    ];
                    $taskModel->addTask($data, "Sell");
                }
            }
            //取消商户订单
            if ($params['pay_status'] == 6 && $row['pay_status'] <= 2) {
                //添加商户冻结金额
                $Usdtlog = new Usdtlog();
                $Usdtlog->quxiaotxLog($row['access_key'], $row['supply_usdt'], 1, $row['orderid'], 2);

                $commissionModel = new Commission();
                $commissionModel->update(['status' => 2, 'order_status' => 3], ['fy_orderid' => $row['orderid']]);
            }

            //取消商户订单
            if ($params['pay_status'] == 6 && $row['pay_status'] == 3) {
                //添加商户冻结金额
                $Usdtlog = new Usdtlog();
                $Usdtlog->quxiaotxLog($row['access_key'], $row['supply_usdt'], 1, $row['orderid'], 2);

                //添加承兑商冻结金额
                $userModel = new UserModel();
                $res2 = $userModel->usdt_dj($row['user_usdt'], $row['user_id'], 7, 1);

                //减少用户金额
                $res3 =  $userModel->usdt($row['user_usdt'], $row['user_id'], 7, 2);
                if (!$res2 || !$res3) {
                    Db::rollback();
                    $this->error('扣除承兑商冻结金额失败');
                }

                $commissionModel = new Commission();
                $commissionModel->update(['status' => 2, 'order_status' => 3], ['fy_orderid' => $row['orderid']]);
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

        // if($params['supply_usdt'] > $this->supply_info['usdt']){
        //     $this->error('提现数量超出可提现数量');
        // }

        //不支持工商和农业 
        if ($params['bankName'] == '工商银行' || $params['bankName'] == '中国工商银行' || $params['bankName'] == '农业银行' || $params['bankName'] == '中国农业银行') {
            $this->error('不支持工商和农业');
        }
        $isset = Db::name("blacklist")->where("username", $params['realName'])->find();
        if ($isset) {
            $this->error('用户名在黑名单中');
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
            $Usdtlog->addtxLog($info['access_key'], $params['supply_usdt'], 2, $orderid, 2);

            $params['orderid'] = $orderid;
            $params['merchantOrderNo'] = empty($params['merchantOrderNo']) ? date("Ymdhis", time()) : $params['merchantOrderNo'];
            $params['pay_type'] = 'bank';
            $params['diqu'] = 1;
            $params['fiatCurrency'] = "USDT";
            $params['withdrawCurrency'] = "USDT";
            $params['pay_status'] = 1;
            // $params['user_fee'] = '7.26';
            //7.2兑出汇率用户

            $BiModel = new BiModel();
            $biinfo = $BiModel->where("id", 1)->find();
            $params['user_usdt'] = sprintf('%.4f', truncateDecimal($params['withdrawAmount'] / $biinfo['duichu'], 4));
            $params['user_fee'] = $params['usdt'] - $params['user_usdt'];

            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->sendNotice();
        $this->success();
    }


    /***
     * 代理商
     */
    public function supply()
    {

        $supplyModel = new Supply();
        $admin_id = $this->auth->id;
        if ($admin_id < 5 || $admin_id == 8 || $admin_id == 13) {
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
                $row->visible(['id', 'orderid', 'merchantOrderNo', 'realName', 'cardNumber','withdrawAmount','bankName', 'bankBranchName', 'pay_type', 'pay_account', 'pay_ewm_image', 'user_usdt', 'user_fee', 'supply_fee', 'supply_usdt', 'updatetime', 'status', 'access_key', 'pay_status', 'usdt', 'withdrawCurrency', 'pinzheng_image', 'createtime']);
            }

            $supply_price = $this->model->where("pay_status", 5)->where('access_key', $supply_info['access_key'])->cache(3600)->sum("supply_usdt");
            $supply_fee = $this->model->where("pay_status", 5)->where('access_key', $supply_info['access_key'])->cache(3600)->sum("supply_fee");

            $result = array("total" => $list->total(), "rows" => $list->items(), 'extend' => compact('supply_price', 'supply_fee'));

            return json($result);
        }
        return $this->view->fetch();
    }


    public function adds($ids = null)
    {

        $supplyModel = new Supply();
        $admin_id = $this->auth->id;
        if ($admin_id < 5 || $admin_id == 8 || $admin_id == 13) {
            $admin_id = 0;
        }

        $admin_ids_str = "%A" . $admin_id . "A%";
        $supply_info = $supplyModel->whereLike("admin_id", $admin_ids_str)->find();

        $this->view->assign("supply_info", $supply_info);
        return $this->view->fetch();
    }


    public function addru($ids = null)
    {
        $params = $this->request->post();
        $supplyModel = new Supply();
        $supply_info = $supplyModel->whereLike("access_key", $params['access_key'])->find();
        if (!$supply_info) {
            $this->error('商户不存在');
        }

        if ($supply_info['usdt'] < $params['withdrawAmount']) {
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
            'withdrawAmount' => $params['withdrawAmount'],
            'withdrawCurrency' => 'CNY',
            'fiatCurrency' => 'CNY',
            'createtime' => $createtime,
            'updatetime' => $updatetime,
        ];

        Db::startTrans();
        try {
            $res = $this->model->allowField(true)->save($data);

            $Usdtlog = new Usdtlog();
            $Usdtlog->addtxLog($params['access_key'], $params['withdrawAmount'], 2, $orderid, 2);
            $this->sendNotice();
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        if ($res) {
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }


    public function import()
    {

        $path = input("file", "");
        if (empty($path)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $BiModel = new BiModel();
        $biinfo = $BiModel->where("id", 1)->find();
        $duichu_rate = $this->supply_info['duichu'];
        if($this->supply_info['usdt']<=0){
            $this->error('余额不足');
        }


        $fee_dalu_supply_duichu = isset($this->supply_info['duichu_fanyong']) ? $this->supply_info['duichu_fanyong'] : 0;
        $fee_dalu_supply_duichu = $fee_dalu_supply_duichu / 100;


        // 获取上传文件的路径
        $filePath = ROOT_PATH . 'public' . $path;

        // 根据文件扩展名选择合适的读取器
        $reader = IOFactory::createReader('Xlsx');

        // 设置读取器不自动识别文件格式
        $reader->setReadDataOnly(true);

        // 加载Excel文件
        $spreadsheet = $reader->load($filePath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $data = [];
        $importCount = 0;

        // 处理Excel文件内容并保存到数据库中
        foreach ($sheetData as $key => $value) {
            // 跳过表头
            if ($key == 1) continue;

            // 获取关键字段值
            $orderid = $value['A'] ?? '';
            $money = $value['B'] ?? 0;

            // 跳过空行（通过检查关键字段）
            if (empty($orderid)) continue;
            if ($money == 0) continue;
            if ($money < 3500){
                $this->error('不支持低于3500的兑出');
            }

            if ($value['D'] == '工商银行' || $value['D'] == '中国工商银行' || $value['D'] == '农业银行' || $value['D'] == '中国农业银行') {
                $this->error('不支持工商和农业');
            }

            // 处理数据
            $item = [
                'orderid' => $orderid,
                'money' => $money,
                'realName' => $value['C'] ?? '',
                'bankName' => $value['D'] ?? '',
                'cardNumber' => $value['E'] ?? '',
                'bankBranchName' => $value['F'] ?? '',
            ];

            $data[] = $item;
            $importCount++;
        }

        $count = 0;
        // 如果有数据需要处理
        if (!empty($data)) {
            foreach ($data as $item) {
                $supplyModel = new Supply();
                $sinfo = $supplyModel->where('access_key', $this->supply_info['access_key'])->find();
                if ($sinfo['usdt'] <= 0) {
                    continue;
                }

                $order = $this->model->where('merchantOrderNo', $item['orderid'])->find();
                if (!$order) {
                    $usdt = truncateDecimal($item['money'] / $duichu_rate, 4);
                    $user_usdt = sprintf('%.4f', truncateDecimal($item['money'] / $biinfo['duichu'], 4));
                    $supply_fee = truncateDecimal($usdt * $fee_dalu_supply_duichu);
                    $supply_usdt = truncateDecimal($usdt + $supply_fee);

                    $newOrderId = getOrderNo();

                    $saveData = [
                        'access_key' => $this->supply_info['access_key'],
                        'orderid'   => $newOrderId,
                        'merchantOrderNo' => $item['orderid'],
                        'realName' => $item['realName'],
                        'bankName' => $item['bankName'],
                        'cardNumber' => $item['cardNumber'],
                        'bankBranchName' => $item['bankBranchName'],
                        'pay_type' => 'bank',
                        'diqu'      => 1,
                        'fiatCurrency' => 'USDT',
                        'withdrawCurrency' => 'USDT',
                        'withdrawAmount' => $item['money'],
                        'huilv'         => $duichu_rate,
                        'pay_status'    => 1,
                        'usdt'          => $usdt,
                        'user_usdt'     => $user_usdt,
                        'user_fee'      => $usdt - $user_usdt,
                        'supply_fee'   => $supply_fee,
                        'supply_usdt'   => $supply_usdt,
                        'createtime'    => time(),
                        'updatetime'    => time(),
                    ];

                    // 只调用一次 save
                    // $res = $this->model->allowField(true)->save($saveData);
                    $res = Db::name("order_chujin")->insert($saveData);
                    //扣除商户冻结金额
                    if($res){
                        $Usdtlog = new Usdtlog();
                        $Usdtlog->addtxLog($this->supply_info['access_key'], $supply_usdt, 2, $newOrderId, 2);
                    }
                    $count++;
                }
            }
        }

        // 删除临时文件
        // @unlink($filePath);

        return $this->success('文件上传成功，共处理 ' . $count . ' 条数据');
    }


    /**
     * chaoshi1 返佣
     * chaoshi2 超时
     * 佣金
     */
    public function commission($fy_orderid, $chaoshi = 1)
    {

        $commissionModel = new Commission();

        if ($chaoshi == 2) {
            $commissionModel->update(['status' => 1, 'chaoshi' => 2], ['fy_orderid' => $fy_orderid]);
            return;
        }

        $list = $commissionModel->where("fy_orderid", $fy_orderid)->select();
        foreach ($list as $row) {
            $userModel = new UserModel();
            $userModel->usdt($row['money'], $row['p_userid'], 5, 1, $fy_orderid);
        }

        $commissionModel->update(['status' => 1], ['fy_orderid' => $fy_orderid]);
        return true;
    }

    public function sendNotice()
    {

        $email = "870416982@qq.com";
        $count = Db::name("order_chujin")->where("pay_status", "<", 2)->count();
        $amounts = Db::name("order_chujin")->where("pay_status", "<", 2)->column("withdrawAmount");

        foreach ($amounts as &$amount) {
            $amount = floatval($amount);
        }

        $amounts_str = implode(",", $amounts);
        $msg = "您好，商户已分配" . $count . "笔订单兑出订单,金额：" . $amounts_str . "，麻烦请处理，谢谢。温馨提醒交易员需麻烦确认好金额才打款，不要多付/重复打款，避免不必要的损失";
        Emslib::notice($email, $msg, "resetpwd");
    }


    /**
     * 回调
     */
    public function huidiao($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $supplyModel = new Supply();
        $info = $supplyModel->where('access_key', $row['access_key'])->find();
        $taskModel = new Task();
        // $data = [
        //     'access_key'    => $info['access_key'],
        //     'access_secret' => $info['access_secret'],
        //     'name' => 'cash',
        //     'message' => '',
        //     'params' => [
        //         'orderid' => $row['orderid'],
        //         'url'  => $row['callback'],
        //         'pay_status' => 3
        //     ]
        // ];
        $data = [
            'access_key'    => $info['access_key'],
            'access_secret' => $info['access_secret'],
            'name' => 'sell',
            'message' => '',
            'params' => [
                'orderid' => $row['merchantOrderNo'],
                'url'  => $row['webhookUrl'],
                'pay_status' => 3   //已支付   
            ]
        ];
        $taskModel->addTask($data, "Sell");
        // if (false === $result) {
        //     $this->error(__('No rows were updated'));
        // }
        $this->success("回调请求成功", null, ['id' => $ids]);
    }
}
