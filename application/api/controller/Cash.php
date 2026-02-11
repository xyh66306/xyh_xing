<?php


namespace app\api\controller;
use app\common\controller\Api;

use app\common\model\OrderCash as OrderCashModel;
use app\common\model\User as UserModel;
use app\common\model\order\Chujin;
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


class Cash extends Api
{

    protected $noNeedRight = '*';


    public function index(){
        
    }


    /**
     * 发起者
     * 订单兑出
     */
    public function orderout()
    {
        $page = input("page",1);
        $pay_status = input("pay_status","");
        $diqu = input("diqu",1);

        $userModel = new UserModel();
        $orderCashModel = new OrderCashModel();

        
        $user_id = $this->auth->id;

        $where['user_id'] = $user_id;
        $where['diqu'] = $diqu;
        // $where['pay_status'] = $pay_status==0?['in',[1,2]]:['in',[3,4]];

        if($pay_status){
            $where['pay_status'] = $pay_status;
        }
        $list = $orderCashModel->where($where)->page($page)->select();

        foreach ($list as $key => $value) {

            if($value['pay_type']==1){
                $list[$key]['pay_type'] = '银行卡';
            }elseif($value['pay_type']==2){
                $list[$key]['pay_type'] = '支付宝';
            }else{
                $list[$key]['pay_type'] = '微信';
            }

            if($value['pay_status']==1){
                $list[$key]['pay_status_txt'] = '抢单中';
            }elseif($value['pay_status']==2){
                $list[$key]['pay_status_txt'] = '审核中';
            }elseif($value['pay_status']==3){
                $list[$key]['pay_status_txt'] = '已完成';
            }elseif($value['pay_status']==4){
                $list[$key]['pay_status_txt'] = '已取消';
            }elseif($value['pay_status']==5){
                $list[$key]['pay_status_txt'] = '已申诉';
            }elseif($value['pay_status']==6){
                $list[$key]['pay_status_txt'] = '已退款';
            }

            $list[$key]['ctime'] = date("Y-m-d H:i:s",$value['ctime']);
            // $list[$key]['nickname'] = $userModel->where("id",$value['user_id'])->value("nickname");
        }

        $data['count'] = $orderCashModel->where($where)->count("id");
        $data['list'] = $list;

        $this->success('', $data);

    }



    /**
     * 发起者
     * 订单兑入
     */
    public function orderin()
    {
        $page = input("page",1);
        $orderid = input("orderid",'');
        $pay_type = input("pay_type",'');
        $pay_status = input("pay_status",'');



        $ChujinModel = new Chujin();
        $userModel = new UserModel();
        $user_id = $this->auth->id;

        $where =[];

        // $where['pay_status'] = $pay_status;

        if($orderid){
            $where['orderid'] = $orderid;
        }
        if($pay_type){
            $where['pay_type'] = $pay_type;
        }        


        $list = $ChujinModel->where($where)->whereIn("pay_status",[1,2])->where('status','normal')->page($page)->select();

        foreach ($list as $key => $value) {

            if($value['pay_type']=='bank'){
                $list[$key]['pay_type'] = '银行卡';
            }elseif($value['pay_type']=='alipay'){
                $list[$key]['pay_type'] = '支付宝';
            }else{
                $list[$key]['pay_type'] = '微信';
            }


            if($value['pay_status']==1){
                $list[$key]['pay_status_txt'] = '抢单中';
            }elseif($value['pay_status']==2){
                $list[$key]['pay_status_txt'] = '承兑商已支付';
            }elseif($value['pay_status']==3){
                $list[$key]['pay_status_txt'] = '商户审核';
            }elseif($value['pay_status']==4){
                $list[$key]['pay_status_txt'] = '平台审核';
            }elseif($value['pay_status']==5){
                $list[$key]['pay_status_txt'] = '已取消';
            }

            $list[$key]['createtime'] = date("Y-m-d H:i:s",$value['createtime']);

        }

        $data['count'] = $ChujinModel->where($where)->count("id");
        $data['list'] = $list;

        $this->success('', $data);

    }


    /***
     * 订单详情
     */
    public function detail()
    {
        $id = input("id",'');
        if(!$id){
            $this->error('参数错误');
        }
        $orderCashModel = new OrderCashModel();
        $data = $orderCashModel->where("id",$id)->find();
        if(!$data){
            $this->error('数据不存在');
        }
        $userModel = new UserModel();
        $data['nickanme'] = $userModel->where("id",$data['user_id'])->value("nickname");
        $data['ctime'] = date("Y-m-d H:i:s",$data['ctime']);

        $data['pay_ewm_image'] = _sImage($data['pay_ewm_image']);
        $data['pinzheng_image'] = _sImage($data['pinzheng_image']);
        
        $this->success('', $data);

    }



    /***
     * 申诉
    */
    public function shensu(){

        $id = input("id",'');
        $orderid = input("orderid",'');
        $pz_image = input("pz_image",'');
        $beizhu = input("beizhu",'');

        if(!$orderid || !$pz_image || !$beizhu){
            $this->error('参数错误');
        }

        $shensuModel = new orderShensu();

        if(!$id){
            $res = $shensuModel->save([
                'orderid'=>$orderid,
                'pz_image'=>$pz_image,
                'beizhu'=>$beizhu,
                'user_id'=>$this->auth->id,
                'ctime'=>time(),
                'utime'=>time(),
            ]);
        } else {
            $res = $shensuModel->update([
                'orderid'=>$orderid,
                'pz_image'=>$pz_image,
                'beizhu'=>$beizhu,
                'user_id'=>$this->auth->id,
                'ctime'=>time(),
                'utime'=>time(),
            ],['id'=>$id]);

        }

        if($res){
            $this->success('申诉成功');
        }else{
            $this->error('申诉失败');
        }
    }


    /**
     * 获取申诉记录
     */
    public function getshensu()
    {
        $orderid = input("orderid",'');
        if(!$orderid){
            $this->error('参数错误');
        }
        $shensuModel = new orderShensu();
        $data = $shensuModel->where("orderid",$orderid)->order("id desc")->find();
        if(!$data){
            $this->error('数据不存在');
        }
        $data['ctime'] = date("Y-m-d H:i:s",$data['ctime']);
        $data['pz_image'] = _sImage($data['pz_image']);
        $this->success('', $data);

    }


    /**
     * 获取用户收款方式
     */
    public function getPayType(){ 


        $userid = $this->auth->id;

        $userPayewm = new UserPayewm();
        $userBackcard = new UserBankcard();
        
        $ewmlist = $userPayewm->where("user_id",$userid)->where(['sys_status'=>'normal'])->select();
        $backlist = $userBackcard->where("user_id",$userid)->where(['sys_status'=>'normal'])->select();

        $payType = [];


        foreach ($ewmlist as $key => $value) {
            if($value['pay_skpt']=='wxpay'){
                $payType[] = "微信";
            }elseif($value['pay_skpt']=='alipay'){
                $payType[] = "支付宝";
            }
        }

        if($backlist){
            $payType[] = '银行卡';
        }

        $info = [
            'ewm'=>$ewmlist,
            'payType'=>$payType,
            'back'=>$backlist
        ];

        return $this->success('success', $info);
    }
    /**
     * 获取佣金配置
     */
    public function getConfig(){ 

        $bi_type = input("bi_type",'');
        $type = input("type",'');
        $userid = $this->auth->id;

        if(!$bi_type || !$type){ 
            return $this->error('参数错误');
        }

        $userRebate = new UserRebate();
        $info = $userRebate->where(['user_id'=>$userid,'bi'=>$bi_type,'type'=>$type,'churu'=>'duichu'])->find();
        return $this->success('success', $info);
    }

    /**
     * 添加提币
     */
    public function addOrder(){

        $orderid = "o".date("YmdHis").random_int(1000,9999);

        $userid = $this->auth->id;
        $bi_type = input("bi_type",'');
        $pay_type = input("pay_type",'');
        $act_num = input("act_num",'');
        $rate = input("rate",'');
        $huilv = input("huilv",'');       
        $remarks = input("remarks",'');

        if(!$bi_type || !$pay_type || !$act_num ||  !$huilv){
            return $this->error('参数错误');
        }
        //获取用户余额
        $userModel = new UserModel();
        $userInfo = $userModel->field("id,usdt,usdt_dj,diqu")->where('id',$userid)->find();
        if(!$userInfo){ 
            return $this->error('用户不存在');
        }
        if(($userInfo['usdt']-$userInfo['usdt_dj']) < $act_num){
            return $this->error('余额不足');
        }

        $userBankcard = new UserBankcard();
        $userPayewm = new UserPayewm();

        $info = [];
        if($pay_type == 'bank'){
            $info = $userBankcard->where("user_id",$userid)->where(['sys_status'=>'normal'])->find();
        }elseif($pay_type == 'alipay'){
            $info = $userPayewm->where("user_id",$userid)->where("pay_skpt","alipay")->where(['sys_status'=>'normal'])->find();
        }else{
           $info = $userPayewm->where("user_id",$userid)->where("pay_skpt","wxpay")->where(['sys_status'=>'normal'])->find();
        }
        if(!$info){
            return $this->error('请添加收款信息');
        }

        $supplyModel = new Supply();
        $pintai_id = $supplyModel->where("id",1)->value("access_key");


        //实际支付金额
        $amount = $act_num * $huilv;
        $commission = sprintf("%.2f",$act_num * $rate/100);

        $order = [
            'orderid'=>$orderid,
            'user_id'=>$userid,
            'bi_type'=>$bi_type,
            'pay_type'=>$pay_type,
            'act_num'=>$act_num,
            'amount'=>$amount,
            'rate'=>$rate,
            'huilv'=>$huilv,
            'remarks'=>$remarks,
            // 'sell_commission'=>$commission,
            // 'sell_recomer' => $this->auth->invite,
            'sell_commission'=>0,
            'sell_recomer' => 0,            
            'status'=>1,
            'pintai_id'=>$pintai_id,
            'diqu' => $userInfo['diqu'],
            'ctime'=>time(),
            'utime'=>time(),
        ];

        if($pay_type == 'bank'){
            $order['seller_name']  = $info['username'];
            $order['bank_name'] = $info['bank_name'];
            $order['bank_account'] = $info['bank_nums'];
            $order['bank_zhihang'] = $info['bank_zhdz'];
        } else {
            $order['seller_name']  = $info['username'];
            $order['pay_account']   = $info['pay_nums'];
            $order['pay_ewm_image'] = $info['pay_ewm_image'];
        }

        $cashModel = new OrderCashModel();
        $res = $cashModel->insert($order);
        if($res){
            //冻结
            $res2 = $userModel->usdt_dj($act_num,$userid,6,1,$remarks);
            if($res2){
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }            
        }else{
            $this->error('添加失败');
        }

    }


    /**
     * 兑出抢单
     * 添加抢单记录
     */
    public function addCash(){

        $orderid = input("orderid",'');



        if(!$orderid){
            $this->error('参数错误');
        }

 
        $chujinModel = new Chujin();

        $orderInfo = $chujinModel->where("orderid",$orderid)->find();

        if($orderInfo['pay_status']>1 && $orderInfo['user_id'] != $this->auth->id){
            $this->error('订单已被他人抢走');
        }

        if($orderInfo['pay_status']>=2 && $orderInfo['user_id'] == $this->auth->id){
            $this->error('请勿重复抢单');
        }


        Db::startTrans();
        try{ 
            $res =  $chujinModel->update(['pay_status'=>2,'user_id'=>$this->auth->id],['id'=>$orderInfo['id']]);
            if($res){
                Db::commit();
            }

        } catch (\Exception $e) {
            Db::rollback();
            $this->error('抢单失败：'.$e->getMessage());
        }
        $this->success('抢单成功');
    }

    /**
     * 兑出抢单
     * 上传支付凭证
     */
    public function uploadPzImg() {

        $orderId = input('post.orderid');
        $pinzheng_image = input('post.pinzheng_image');

        if(!$orderId || !$pinzheng_image) {
            $this->error('参数错误');
        }

        $chujinModel = new Chujin();

        $info = $chujinModel->where('orderid',$orderId)->find();
        if(!$info){
            $this->error('订单不存在');
        }

        Db::startTrans();
        try{ 
            $data = [
                'pay_ewm_image'=>$pinzheng_image,
                'pay_status' =>3,
                'updatetime'=>time(),
            ];
            $res = $chujinModel->update($data,['orderid'=>$orderId]);
            $userModel = new UserModel();
            //添加用户冻结金额
            $userModel->usdt_dj($info['usdt'],$this->auth->id,6,1);

            if($res){
                Db::commit();                
            }
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('上传成功');

    }

    /***
     * 订单详情
     */
    public function detailByOrderId()
    {
        $orderid = input("orderid",'');
        if(!$orderid){
            $this->error('参数错误');
        }
        $chujinModel = new Chujin();
        $data = $chujinModel->where("orderid",$orderid)->find();
        if(!$data){
            $this->error('数据不存在');
        }

        $data['ctime'] = date("Y-m-d H:i:s",$data['createtime']);
        $data['pay_ewm_image'] = $data['pay_ewm_image']?_sImage($data['pay_ewm_image']):'';
        $this->success('', $data);

    }

    /***
     * 订单支付
     */
    public function payorder() { 

        $orderid = input("orderid",'');
        if(!$orderid){
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
            $userModel->usdt_dj($data['act_num'],$this->auth->id,6,2);
            //扣除usdt 金额
            $userModel->usdt($data['act_num'],$this->auth->id,3,2);

            $orderCashModel->update(['pay_status'=>3,'pay_time'=>time(),'utime'=>time()],['id'=>$data['id']]);
            $this->commission($data);

            //通知订单完成
            $taskModel = new Task();
            $data = [
                'name' => 'sell',
                'message'=>'',
                'params' => [
                    'url'  => $data['sell_callback'],
                    'orderid'=> $orderid,
                    'pay_status'=>3
                ]
            ];

            $taskModel->addTask($data,"Sell");

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作失败'.$e->getMessage());
        }
        
        $this->success('操作成功');

    }
    

    /***
     * 分佣
     * data.pay_type = 支付方式:1=银行卡,2=支付宝,3=微信
     */
    public function commission($data){

        $Commission = new Commission();
        $userModel  = new UserModel();

        if($data['buy_commission']<=0 && $data['sell_commission']<=0){
            return;
        }

        Db::startTrans();
        try {         
            //出售者分佣   
            $rebateData[] = [
                'user_id'=>$data['user_id'],
                'p_userid'=>$data['sell_recomer'],
                'fy_orderid'=>$data['id'],
                'p4b_orderid'=>$data['orderid'],
                'number' =>$data['act_num'],
                'rate'  =>$data['rate'],
                'money' =>$data['sell_commission'],
                'type' =>1,
                'level'=>1,
                'status'=>2,
                'chaoshi'=>1,
                'ctime'=>time(),
                'utime'=>time(),
            ];
            //购买者
            $rebateData[] = [
                'user_id'=>$data['receiveid'],
                'p_userid'=>$data['buy_recomer'],
                'fy_orderid'=>$data['id'],
                'p4b_orderid'=>$data['orderid'],
                'number' =>$data['act_num'],
                'rate'  =>$data['rate'],
                'money' =>$data['buy_commission'],
                'type' =>1,
                'level'=>1,
                'status'=>2,
                'chaoshi'=>1,
                'ctime'=>time(),
                'utime'=>time(),
            ];            
            $Commission->saveAll($rebateData);
                
            //添加 usdt 金额
            $userModel->usdt($data['sell_commission'],$data['sell_recomer'],5,1);
            //添加 usdt 金额
            $userModel->usdt($data['buy_commission'],$data['buy_recomer'],5,1);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作失败'.$e->getMessage());
        }
        return true;

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

        if($data['pay_status'] != 2){
            $this->error('订单状态不正确！');
        }

        Db::startTrans();
        try {
            $userModel = new UserModel();
            //用户冻结资金返回
            $userModel->usdt_dj($data['act_num'],$data['user_id'],6,2);
            $orderCashModel->update(['pay_status'=>4,'utime'=>time()],['id'=>$data['id']]);


            //通知订单完成
            $taskModel = new Task();
            $data = [
                'name' => 'sell',
                'message'=>'',
                'params' => [
                    'url'  => $data['sell_callback'],
                    'orderid'=> $orderid,
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
?>