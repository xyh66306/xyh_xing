<?php

namespace app\openapi\controller;

use app\common\controller\Api;
use app\common\model\Supply;
use app\common\model\OrderCash as OrderCashModel;
use app\common\model\User as UserModel;
use app\common\model\UserBankcard;
use app\common\model\UserPayewm;
use app\common\model\UserRebate;
use app\common\model\Commission;
use app\common\model\Task;
use think\Db;
use think\Request;


/**
 * 兑入接口
 */
class Buy extends Api
{

    use Send;
    protected $noNeedRight = "*";
    protected $noNeedLogin = "*";

    protected $access_key = "";
    public function __construct(Request $request)
    {
        parent::__construct(); // 确保调用父类构造函数

        $this->request = $request;

        // 使用 input() 方法获取请求参数
        if(empty($this->request->param('access_key'))) {
            $this->error('access_key错误');
        }
        if(empty($this->request->param('randomStr')) || strlen($this->request->param('randomStr')) != 32) {
            $this->error('随机字符串错误');
        }
        if(empty($this->request->param('gmtRequest'))) {
            $this->error('请求时间错误');
        }
        $time = time();
        if($this->request->param('gmtRequest')+600<=$time){
            $this->error('时间过期');
        }

        $supplyModel = new Supply();
        $info = $supplyModel->where('access_key',$this->request->param('access_key'))->find();

        $this->access_key = $this->request->param('access_key');

        if(empty($info)){
            $this->error('商户不存在');
        }
        if($info['access_secret'] != $this->request->param('access_secret')){
            $this->error('商户密钥错误');
        }
        // $client_ip = $_SERVER['REMOTE_ADDR'];
        // if($info['ip'] && $info['ip'] !="*" && $info['ip'] != $client_ip ){
        //     $this->error('IP错误');
        // }

        $params = [
            'access_key'    => $this->request->param('access_key'),
            'randomStr'     => $this->request->param('randomStr'),
            'gmtRequest'    => $this->request->param('gmtRequest'),
            'signature'          => $this->request->param('signature')
        ];
        $access_secret = $this->request->param('access_secret');

        #先鉴权
        $this->Authentication($params, $access_secret);
    }



    /**
     * 出售 对应sell
     * 添加提币
     */
    public function addOrder(){

        $orderid = "o".date("YmdHis").random_int(1000,9999);

        $userid = input("business_id",'');
        $bi_type = input("bi_type",'');
        $pay_type = input("pay_type",'');
        $act_num = input("act_num",'');
        $rate = input("rate",'');
        $huilv = input("huilv",'');       
		$amount = input("amount","");
        $remarks = input("remarks",'');
        $backurl = input("backurl",'');
        $diqu   = input("diqu",1); //地区 1大陆2江城3海外
		$validitys = input("validitys",""); //秒


        $seller_name = input("seller_name",'');
        //银行信息
        $bank_name = input("bank_name",'');
        $bank_account = input("bank_account",'');
        $bank_zhdz = input("bank_zhdz",'');
        //二维码收款账户
        $pay_account = input("pay_account",'');
        $pay_ewm_image = input("pay_ewm_image",'');


        if(!$userid || !$bi_type || !$pay_type || !$act_num ||  !$huilv){
            return $this->error('参数错误');
        }

        $pay_type_arr = ['bank','alipay','wxpay'];

        if(!in_array($pay_type,$pay_type_arr)){
            return $this->error('pay_type参数错误');
        }


        if($pay_type == 'bank'){
            if(!$bank_name || !$bank_account || !$bank_zhdz){
                return $this->error('请填写银行账号和开户行');
            }
        } else {
            if(!$pay_account){
                return $this->error('请填写收款账户和二维码');
            }
        }

        // //保留2未小数
        // $amount = sprintf("%.2f",$act_num * $huilv);

        $order = [
            'orderid'=>$orderid,
            'user_id'=>$userid,
            'seller_name'=>$seller_name,
            'bi_type'=>$bi_type,
            'pay_type'=>$pay_type,
            'act_num'=>$act_num,
            'amount'=>$amount,
            'rate'=>$rate,
            'huilv'=>$huilv,
            'remarks'=>$remarks,
            'sell_commission'=>0,
            'sell_recomer' => 0,
            'status'=>1,
            'pintai_id'=>$this->access_key,
            'diqu' => $diqu,
            'sell_callback'=>$backurl,
            'bank_name'=>$bank_name,
            'bank_account'=>$bank_account,
            'bank_zhihang'=>$bank_zhdz,
            'pay_account'=>$pay_account,
            'pay_ewm_image'=>$pay_ewm_image,
			'validitys'=>$validitys,
            'ctime'=>time(),
            'utime'=>time(),
        ];


        $cashModel = new OrderCashModel();
        $res = $cashModel->insert($order);
        if($res){
            $this->success('添加成功');          
        }else{
            $this->error('添加失败');
        }

    }

    /***
     * 订单支付
     */
    public function payorder() { 

        $orderid = input("orderid",'');
        $userid = input("business_id",'');
        if(!$orderid || !$userid){
            $this->error('参数错误');
        }
        $orderCashModel = new OrderCashModel();
        $data = $orderCashModel->where("orderid",$orderid)->order("id desc")->find();
        if(!$data){
            $this->error('数据不存在');
        }

        if($data['pay_status'] != 2){
            $this->error('订单状态不正确！');
        }

        Db::startTrans();
        try {
            $userModel = new UserModel();
            //扣除usdt_dj 冻结金额
            $userModel->usdt_dj($data['act_num'],$userid,6,2);
            //扣除usdt 金额
            $userModel->usdt($data['act_num'],$userid,3,2);

            $orderCashModel->update(['pay_status'=>3,'pay_time'=>time(),'utime'=>time()],['id'=>$data['id']]);
            $this->commission($data);

            //通知订单完成
            // $taskModel = new Task();
            // $data = [
            //     'name' => 'buy',
            //     'message'=>'',
            //     'params' => [
            //         'orderid'=> $orderid,
            //         'url'  => $data['callback'],                    
            //         'pay_status'=>3
            //     ]
            // ];

            // $taskModel->addTask($data,"Buy");

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作失败'.$e->getMessage());
        }
        
        $this->success('操作成功');

    }
    



    /**
     * 取消订单
     */
    public function nopayorder(){

        $orderid = input("orderid",'');
        if(!$orderid){
            $this->error('参数错误');
        }
        $orderCashModel = new OrderCashModel();
        $data = $orderCashModel->where("orderid",$orderid)->order("id desc")->find();
        if(!$data){
            $this->error('数据不存在');
        }

        if($data['pay_status'] >= 1){
            $this->error('订单状态不正确！');
        }

        Db::startTrans();
        try {
            $orderCashModel->update(['pay_status'=>4,'utime'=>time()],['id'=>$data['id']]);
            //通知取消订单
            $taskModel = new Task();
            $data = [
                'name' => 'sell',
                'message'=>'',
                'params' => [
                    'orderid'=> $orderid,
                    'url'  => $data['sell_callback'],                    
                    'pay_status'=>4
                ]
            ];
            $taskModel->addTask($data,"Sell");
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作失败');
        }
        
        $this->success('操作成功');

    }


}