<?php
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-09-14 14:48:51
 */


namespace app\api\controller;

use app\common\controller\Api;

use app\common\model\User as UserModel;
use app\common\model\order\Rujin as RujinModel;
use app\common\model\UserRebate;
use app\common\model\UserPayewm;
use app\common\model\UserBankcard;
use app\common\model\OrderShensu;
use app\common\model\Commission;
use app\common\model\Task;
use app\common\model\Supply;
use app\common\model\user\Address as UsdtAddress;
use fast\Random;
use think\Config;
use think\Db;
use think\Validate;


class Rujin extends Api
{

    protected $noNeedRight = ['index', 'detail', 'commission','payorder', 'getOrderLst'];
    /**
     * 发起者
     * 订单兑入
     */
    public function index()
    {
        $page = input("page", 1);
        $orderid = input("orderid", '');
        $pay_type = input("pay_type", '');

        $model = new RujinModel();

        $where = [];

        if ($orderid) {
            $where['orderid'] = $orderid;
        }
        if ($pay_type) {
            $where['pay_type'] = $pay_type;
        }

        $where['status'] = 1;

        $list = $model->where($where)->where('pay_status', 'in', [0, 1])->page($page)->order("id desc")->select();


        foreach ($list as $key => $value) {

            if ($value['pay_type'] == 'bank') {
                $list[$key]['pay_type'] = '银行卡';
            } elseif ($value['pay_type'] == 'alipay') {
                $list[$key]['pay_type'] = '支付宝';
            } else {
                $list[$key]['pay_type'] = '微信';
            }


            if ($value['pay_status'] == 1) {
                $list[$key]['pay_status_txt'] = '开始抢单';
            } elseif ($value['pay_status'] == 2) {
                $list[$key]['pay_status_txt'] = '已抢单';
            } elseif ($value['pay_status'] == 3) {
                $list[$key]['pay_status_txt'] = '已支付';
            } elseif ($value['pay_status'] == 4) {
                $list[$key]['pay_status_txt'] = '审核中';
            } elseif ($value['pay_status'] == 5) {
                $list[$key]['pay_status_txt'] = '已完成';
            } elseif ($value['pay_status'] == 6) {
                $list[$key]['pay_status_txt'] = '已取消';
            }


            $list[$key]['createtime'] = date("Y-m-d H:i:s", $value['ctime']);
        }

        $data['count'] = $model->where($where)->where('pay_status', 'in', [0, 1])->count("id");
        $data['list'] = $list;

        $this->success('', $data);
    }


    public function detail()
    {
        $orderid = input("orderid", '');
        if (!$orderid) {
            $this->error('参数错误');
        }
        $model = new RujinModel();
        $data = $model->where("orderid", $orderid)->find();
        if (!$data) {
            $this->error('数据不存在');
        }

        $data['ctime'] = date("Y-m-d H:i:s", $data['ctime']);
        $data['pay_ewm_image'] = $data['pay_ewm_image'] ? _sImage($data['pay_ewm_image']) : '';
        $this->success('', $data);
    }



    /***
     * 订单支付
     */
    public function payorder()
    {

        $orderid = input("orderid", '');
        if (!$orderid) {
            $this->error('参数错误');
        }
        $model = new RujinModel();
        $data = $model->where("orderid", $orderid)->order("id desc")->find();
        if (!$data) {
            $this->error('数据不存在');
        }


        Db::startTrans();
        try {
            $userModel = new UserModel();
            //添加usdt_dj 冻结金额
            $userModel->usdt_dj($data['user_usdt'], $this->auth->id, 8, 1);
            //扣除usdt 金额
            $userModel->usdt($data['user_usdt'], $this->auth->id, 8, 2);

            $model->update(['pay_status' => 3, 'pay_time' => time(), 'utime' => time()], ['id' => $data['id']]);
            // $this->commission($data);

            //通知订单完成
            // $taskModel = new Task();
            // $data = [
            //     'name' => 'sell',
            //     'message' => '',
            //     'params' => [
            //         'url'  => $data['sell_callback'],
            //         'orderid' => $orderid,
            //         'pay_status' => 3
            //     ]
            // ];

            // $taskModel->addTask($data, "Sell");

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作失败' . $e->getMessage());
        }

        $this->success('操作成功');
    }




    /***
     * 分佣
     * data.pay_type = 支付方式:1=银行卡,2=支付宝,3=微信
     */
    public function commission($data)
    {

        $Commission = new Commission();
        $userModel  = new UserModel();

        $recomer = '';
        $money = 0;

        if(!$data['recomer']){
            $uinfo = $userModel->where("id", $data['user_id'])->find();
            $recomer = $uinfo['invite'];
        }
 

        if ($data['buy_commission'] <= 0 && $data['sell_commission'] <= 0) {
            return;
        }

        Db::startTrans();
        try {

            $rebateData[] = [
                'user_id' => $data['user_id'],
                'p_userid' => $data['sell_recomer'],
                'fy_orderid' => $data['id'],
                'p4b_orderid' => $data['orderid'],
                'number' => $data['act_num'],
                'rate'  => $data['rate'],
                'money' => $data['sell_commission'],
                'type' => 1,
                'level' => 1,
                'status' => 2,
                'chaoshi' => 1,
                'ctime' => time(),
                'utime' => time(),
            ];
            $Commission->saveAll($rebateData);

            //添加 usdt 金额
            $userModel->usdt($data['sell_commission'], $data['sell_recomer'], 5, 1);
            //添加 usdt 金额
            $userModel->usdt($data['buy_commission'], $data['buy_recomer'], 5, 1);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作失败' . $e->getMessage());
        }
        return true;
    }


    /***
     * 获取订单列表
     */
    public function getOrderLst()
    {
        $page = input("page", 1);
        $orderid = input("orderid", '');
        $pay_type = input("pay_type", '');
         $pay_status = input("pay_status",'');

        $model = new RujinModel();

        $where =[];
        $ids = [];

        if($pay_status){
            if($pay_status==0 || $pay_status==1){
                $ids = [0,1,2];
            }elseif($pay_status==2){
                $ids = [3];
            }elseif($pay_status==3){
                $ids = [4];
            }elseif($pay_status==4){
                $ids = [5];
            }
        }

        if ($orderid) {
            $where['orderid'] = $orderid;
        }
        if ($pay_type) {
            $where['pay_type'] = $pay_type;
        }

        $where['status'] = 1;
        $where['user_id'] = $this->auth->id;

        $data =[];

        if($ids){
            $list = $model->where($where)->where('pay_status', 'in', $ids)->page($page)->order("id desc")->select();
            $data['count'] = $model->where($where)->where('pay_status', 'in', $ids)->count("id");

        } else {
            $list = $model->where($where)->page($page)->order("id desc")->select();
            $data['count'] = $model->where($where)->count("id");

        }

        


        foreach ($list as $key => $value) {

            if ($value['pay_type'] == 'bank') {
                $list[$key]['pay_type'] = '银行卡';
            } elseif ($value['pay_type'] == 'alipay') {
                $list[$key]['pay_type'] = '支付宝';
            } else {
                $list[$key]['pay_type'] = '微信';
            }

            
             $list[$key]['usdt'] = $value['user_usdt'];


            if ($value['pay_status'] == 1) {
                $list[$key]['pay_status_txt'] = '开始抢单';
            } elseif ($value['pay_status'] == 2) {
                $list[$key]['pay_status_txt'] = '已抢单';
            } elseif ($value['pay_status'] == 3) {
                $list[$key]['pay_status_txt'] = '已支付';
            } elseif ($value['pay_status'] == 4) {
                $list[$key]['pay_status_txt'] = '审核中';
            } elseif ($value['pay_status'] == 5) {
                $list[$key]['pay_status_txt'] = '已完成';
            } elseif ($value['pay_status'] == 6) {
                $list[$key]['pay_status_txt'] = '已取消';
            }


            $list[$key]['createtime'] = date("Y-m-d H:i:s", $value['ctime']);
        }

        $data['list'] = $list;

        $this->success('', $data);
    }
}
