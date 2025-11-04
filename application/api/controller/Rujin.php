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
use app\common\model\company\Profit as companyProfit;
use app\common\model\UserRebate;
use app\common\model\UserPayewm;
use app\common\model\UserBankcard;
use app\common\model\OrderShensu;
use app\common\model\Commission;
use app\common\model\Task;
use app\common\model\Supply;
use app\admin\model\supply\Usdtlog as SpullyUsdtLog;
use app\common\model\user\Address as UsdtAddress;
use app\common\library\Ems as Emslib;
use fast\Random;
use think\Config;
use think\Db;
use think\Validate;
use think\Cache;


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
        $where['user_id'] = $this->auth->id;

        $list = $model->where($where)->where('pay_status', '<=', 4)->page($page)->order("id desc")->select();


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
        if($data['pinzheng_image']){
            $arr = explode(",", $data['pinzheng_image']);
            for ($i = 0; $i < count($arr); $i++) {
                $arr[$i] = _sImage($arr[$i]);
            }
            $data['pinzheng_image_arr'] = $arr;
            
        } else {
            $data['pinzheng_image'] = '';
            $data['pinzheng_image_arr'] = [];
        }

        $data['authtoken'] = $this->getOrderToken($orderid);
        // $data['pinzheng_image'] = $data['pinzheng_image'] ? _sImage($data['pinzheng_image']) : '';
        $this->success('', $data);
    }



    /***
     * 订单支付
     */
    public function payorder()
    {

        $orderid = input("orderid", '');
        $authtoken = input("auth_token", '');
        if (!$orderid) {
            $this->error('参数错误');
        }
        if (!$authtoken) {
            $this->error('参数错误');
        }
        if (!$this->checkOrderToken($orderid, $authtoken)) {
            $this->error('参数错误');
        }
        
        $model = new RujinModel();
        $data = $model->where("orderid", $orderid)->order("id desc")->find();
        if (!$data) {
            $this->error('数据不存在');
        }
        if($data['pay_status']==3){
            $this->error('已支付，请勿重复支付');
        }

        Db::startTrans();
        try {
            $userModel = new UserModel();
            //添加usdt_dj 冻结金额
            $userModel->usdt_dj($data['user_usdt'], $this->auth->id, 8, 1);
            //扣除usdt 金额
            $userModel->usdt($data['user_usdt'], $this->auth->id, 8, 2,$orderid);

            $uinfo = $userModel->where("id", $this->auth->id)->find();

            $commission = 0; //分佣金额
            $recomer = 0;
            
            $time = time();
            $order_status = 1;
            if($data['yx_time']<$time){
                $order_status = 2;
            }




            $model->update(['pay_status' => 3, 'pay_time' => time(), 'utime' => time(),'order_status'=>$order_status], ['id' => $data['id']]);

            $this->sendNotice($data);
            // $this->quren($data);
            // $this->commission($data);

            // 通知订单完成
            $taskModel = new Task();
            // $data = [
            //     'name' => 'sell',
            //     'message' => '',
            //     'params' => [
            //         'url'  => $data['sell_callback'],
            //         'orderid' => $orderid,
            //         'pay_status' => 3
            //     ]
            // ];

            $supplyModel = new Supply();
            $info = $supplyModel->where('access_key', $data['pintai_id'])->find();

            $taskModel = new Task();
            $data = [
                'access_key'    => $info['access_key'],
                'access_secret' => $info['access_secret'],
                'name' => 'cash',
                'message' => '',
                'params' => [
                    'orderid' =>  $orderid,
                    'url'  => $data['callback'],
                    'pay_status' => 3
                ]
            ];            

            $taskModel->addTask($data, "Sell");

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作失败' . $e->getMessage());
        }

        $this->success('操作成功');
    }

    public function quren($data){

        $supplyModel = new Supply();
        $info = $supplyModel->where('access_key', $data['pintai_id'])->find();
        if ($info) {
            $taskModel = new Task();
            $data = [
                'access_key'    => $info['access_key'],
                'access_secret' => $info['access_secret'],
                'name' => 'cash',
                'message' => '',
                'params' => [
                    'orderid' => $data['orderid'],
                    'url'  => $data['callback'],
                    'pay_status' => 3
                ]
            ];
            $taskModel->addTask($data, "Cash");
        }
        //增加商户USDT
        $SpullyUsdtLog = new SpullyUsdtLog();
        $SpullyUsdtLog->addLog($data['pintai_id'], $data['supply_usdt'], 1, 1, $data['orderid']);

        //减少用户usdt_dj 冻结金额
        $userModel = new UserModel();
        $userModel->usdt_dj($data['user_usdt'],$data['user_id'], 6, 2);


        //添加公司金额
        $companyProfit1 = new companyProfit();
        $companyProfit1->addLog($data['usdt'],$data['supply_fee'],1,1,1,$data['orderid']);   

        $companyProfit2 = new companyProfit();
        $companyProfit2->addLog($data['usdt'],$data['user_fee'],1,3,1,$data['orderid']); 

        //添加代理商佣金
        $commissionModel = new Commission();
        if($data['order_status']==2){
            $commissionModel->update(['status'=>1,'chaoshi'=>2],['fy_orderid'=>$data['merchantOrderNo']]);
        } else {

            $comlist = $commissionModel->where("fy_orderid",$data['merchantOrderNo'])->select();
            $comSum  = $commissionModel->where("fy_orderid",$data['merchantOrderNo'])->sum('money');
            if($comSum>0){
                
                foreach ($comlist as $vo) {
                    $userModel = new UserModel();
                    $userModel->usdt($vo['money'],$vo['p_userid'],5,1,$data['merchantOrderNo']);
                }

                $companyProfit3 = new companyProfit();
                $res5 = $companyProfit3->addLog($data['usdt'],$comSum,10,2,2,$data['merchantOrderNo']); 
                $commissionModel->update(['status'=>1,'chaoshi'=>1],['fy_orderid'=>$data['merchantOrderNo']]);
            }                

        }  
        $model = new RujinModel();
        $model->update(['pay_status' => 4], ['id' => $data['id']]);

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
            $data['user_usdt_count'] = $model->where($where)->where('pay_status', 'in', $ids)->sum('user_usdt');
            $data['amount_count'] = $model->where($where)->where('pay_status', 'in', $ids)->sum('amount');
        } else {
            $list = $model->where($where)->page($page)->order("id desc")->select();
            $data['count'] = $model->where($where)->count("id");
            $data['user_usdt_count'] = $model->where($where)->sum('user_usdt');
            $data['amount_count'] = $model->where($where)->sum('amount');
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




    //结合orderid 生成一个一次性令牌
    public function getOrderToken($order_id)
    { 
        $token = md5($order_id . time() . uniqid());
        Cache::set('order_token_' . $order_id, $token, 300); // 5分钟有效期
        return $token;        
    }
    public function checkOrderToken($order_id, $token)
    {
        $cache_token = Cache::get('order_token_' . $order_id);
        if ($cache_token == $token) {
            // 验证成功 删除Token
            Cache::rm('order_token_' . $order_id);
            return true;
        }
        return false;
    }



    public function sendNotice($info){

        $email = "870416982@qq.com";
        $msg = "兑入订单号".$info['orderid']."已确认";
        Emslib::notice($email, $msg, "resetpwd");

    }

}
