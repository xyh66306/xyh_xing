<?php
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-07-30 16:36:36
 */
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-07-30 16:36:36
 */

namespace app\openapi\controller;

use app\common\controller\Api;
use app\common\model\Supply;
use app\common\model\User as UserModel;
use app\common\model\UserPayewm;
use app\common\model\UserBankcard;
use app\common\model\UserRebate;
use app\common\model\Commission;
use app\common\model\Task;
use app\common\model\order\Rujin;
use think\Db;
use think\Request;

/**
 * 详情接口
 */
class Details extends Api
{
    use Send;
    protected $noNeedRight = ['index'];
    protected $noNeedLogin = ['index','payOrder'];

    protected $access_key = "";
    protected $secret = "";

    public function __construct(Request $request)
    {
        parent::__construct(); // 确保调用父类构造函数

        $this->request = $request;

        // 使用 input() 方法获取请求参数
        if(empty($this->request->param('access_key'))) {
            $this->error('access_key错误');
        }
        $supplyModel = new Supply();
        $info = $supplyModel->where('access_key',$this->request->param('access_key'))->find();
        if(empty($info)){
            $this->error('商户不存在');
        }
        $this->access_key = $info['access_key'];
        $this->secret = $info['access_secret'];
    }



    /**
     * 预抢单
     * 订单详情
     */
    public function index(){ 


        $orderid = $this->request->param('orderid');
        if(!$orderid){
            return $this->error('订单id不能为空');
        }

        $rujinModel = new Rujin();
        $userPayewm = new UserPayewm();
        $UserBankcard = new UserBankcard();
        $userModel = new UserModel();


        $detailsInfo = $rujinModel->field("orderid,user_id,amount,yx_time,diqu")->where(['orderid'=>$orderid])->find();

        if(!$detailsInfo){
            return $this->error('订单不存在');
        }

        
        if($detailsInfo['yx_time']<0){
            return $this->error('已超时');
        }


        $userInfo = $userModel->where(['id'=>$detailsInfo['user_id']])->find();
        if(!$userInfo) {
          return  $this->error('收银员不存在');
        }


        $bankInfo = $UserBankcard->field("username,bank_name,bank_nums,bank_zhdz")->where(['user_id'=>$userInfo['id'],'status'=>'normal','sys_status'=>'normal'])->find();
        $wxpayinfo = $userPayewm->field("username,pay_skpt,pay_ewm_image")->where(['user_id'=>$userInfo['id'],'pay_skpt'=>'wxpay','status'=>'normal','sys_status'=>'normal'])->find();
        $alipayinfo = $userPayewm->field("username,pay_skpt,pay_ewm_image")->where(['user_id'=>$userInfo['id'],'pay_skpt'=>'alipay','status'=>'normal','sys_status'=>'normal'])->find();
        if(!$bankInfo && !$wxpayinfo && !$alipayinfo){
            return $this->error('收款账户不存在');
        }

        // if($detailsInfo['yx_time']-time()<=0){
        //     return $this->error('已超时');
        // }

        $minutes = intdiv($detailsInfo['yx_time']-time(),60); 
        $seconds =  floatval(($detailsInfo['yx_time']-time())%60); 

        $info['yx_time_min'] = $minutes;
        $info['yx_time_sec'] = $seconds;
        $info['wxpay'] = $wxpayinfo;
        $info['alipay'] = $alipayinfo;
        $info['bankInfo'] = $bankInfo;
        $info['amount'] = $detailsInfo['amount'];

        return $this->success('success',$info);

    }

    /**
    * 支付订单
    */   
    public function payOrder(){ 

        $orderid = $this->request->param('orderid');        // 订单id
        $pay_type = $this->request->param('pay_type','');  // 支付方式
        $pinzheng_image = $this->request->param('pinzheng_image');  // 凭证图片


        if(!$orderid){ 
            return $this->error('订单id不能为空');
        }
        if(!$pay_type){ 
            return $this->error('支付方式不能为空');
        }
        if(!$pinzheng_image){ 
            return $this->error('凭证图片不能为空');
        }

        $pay_type_arr = ['bank','alipay','wxpay'];
        if(!in_array($pay_type,$pay_type_arr)){
            return $this->error('pay_type参数错误');
        }

        $rujinModel = new Rujin();
        $info = $rujinModel->where(['orderid'=>$orderid])->find();
        if(!$info){
            return $this->error('订单不存在');
        }
      
        if($info['pay_status'] >= 2){
            return $this->error('订单已处理');
        }

        $userPayewm = new UserPayewm();
        $UserBankcard = new UserBankcard();

        $bankInfo = $UserBankcard->field("username,bank_name,bank_nums,bank_zhdz")->where(['user_id'=>$info['user_id'],'status'=>'normal','sys_status'=>'normal'])->find();
        $wxpayinfo = $userPayewm->field("username,pay_skpt,pay_nums,pay_ewm_image")->where(['user_id'=>$info['user_id'],'pay_skpt'=>'wxpay','status'=>'normal','sys_status'=>'normal'])->find();
        $alipayinfo = $userPayewm->field("username,pay_skpt,pay_nums,pay_ewm_image")->where(['user_id'=>$info['user_id'],'pay_skpt'=>'alipay','status'=>'normal','sys_status'=>'normal'])->find();        

        Db::startTrans();

        try {
            $data = [
                'pay_status' => 2,
                'pay_time' => time(),
                'pay_type' => $pay_type,
                'pinzheng_image'=>$pinzheng_image,
            ];
            if($pay_type == 'bank'){
                $data['username'] = $bankInfo['username'];
                $data['bank_name'] = $bankInfo['bank_name'];
                $data['bank_account'] = $bankInfo['bank_nums'];
                $data['bank_zhihang'] = $bankInfo['bank_zhdz'];
            }
            if($pay_type == 'alipay'){
                $data['username'] = $alipayinfo['username'];
                $data['pay_account'] = $alipayinfo['pay_nums'];
                $data['pay_ewm_image'] = $alipayinfo['pay_ewm_image'];
            }   
            if($pay_type == 'wxpay'){
                $data['username'] = $wxpayinfo['username'];
                $data['pay_account'] = $wxpayinfo['pay_nums'];
                $data['pay_ewm_image'] = $wxpayinfo['pay_ewm_image'];
            }                   


            $res = $rujinModel->update($data,['id'=>$info['id']]);
            if($res){

                //通知买方待审核
                $taskModel = new Task();
                $data = [
                    'access_key'     => $this->access_key,
                    'access_secret'  => $this->secret,
                    'name' => 'cash',
                    'message'=>'',
                    'params' => [
                        'orderid'=> $orderid,
                        'url'  => $info['callback'],
                        'pay_status'=>2
                    ]
                ];
                $taskModel->addTask($data,"Cash");
                Db::commit();
            }

        } catch(\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }
        $this->fenyong($orderid,$info['user_id'],$info['amount'],$pay_type);
        $this->success('下单成功，等待确认');
    }    


    /**
     * type ewm bank
     * 推广用户分配
     */
    private function fenyong($orderid,$userid,$amount,$pay_type){

        $userModel = new UserModel();
        $UserRebate = new UserRebate();
        $Commission = new Commission();

        $invite = $userModel->where(['id'=>$userid])->value("invite");    //推广上级
        if(!$invite){
            return $this->error('推广用户不存在');
        }
        $type = '';
        if($pay_type == 'alipay' || $pay_type == 'wxpay'){
            $type = 'ewm';
        } else {
            $type = 'bank';
        }
        $userRebateInfo = $UserRebate->where(['user_id'=>$userid,'pid'=>$invite,'type'=>$type])->find();

        if(!$userRebateInfo){ 
            return $this->error('推广用户费率未设置');
        }

        Db::startTrans();

        try { 

            $yongjin = sprintf("%.2f",$amount*$userRebateInfo['rate']/100);

            $rujinModel = new Rujin();
            $info = $rujinModel->where(['orderid'=>$orderid])->find();
            $res = $rujinModel->update(['recomer'=>$invite,'commission'=>$yongjin],['orderid'=>$orderid]);

            $log = [
                'user_id' => $userid,
                'p_userid'=>$invite,
                'fy_orderid'=>$orderid,
                'p4b_orderid' => $info['merchantOrderNo'],
                'number' => 0,
                'rate' => $userRebateInfo['rate'],
                'money' => $yongjin,
                'type'  =>1,
                'level' =>1,
                'status'=>2,
                'chaoshi'=>1,
                'ctime' => time(),
                'utime' => time(),
            ];
            $Commission->insert($log);       
            
            Db::commit();

        } catch(\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }

        return $this->success('推广用户分配成功');

    }    

}