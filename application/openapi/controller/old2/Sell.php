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
use app\common\model\OrderCash;
use app\common\model\User as UserModel;
use app\common\model\UserPayewm;
use app\common\model\UserBankcard;
use app\common\model\UserRebate;
use app\common\model\Task;
use think\Db;
use think\Request;

/**
 * 兑出接口
 */
class Sell extends Api
{
    use Send;
    protected $noNeedRight = ['list','detail','preorder','payOrder'];
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

        $this->access_key = $info['access_key'];

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
     * 抢单列表
     */
    public function list(){

        $orderCash = new OrderCash();
        $userPayewm = new UserPayewm();

        $page = $this->request->param('page',1);
        $diqu = $this->request->param('diqu',1);

        $list = $orderCash->field('orderid,user_id,pay_type,bi_type,act_num,amount,rate,diqu')->where("pintai_id","<>",$this->access_key)->where(['status'=>1,'pay_status'=>0,'diqu'=>$diqu])->page($page)->select();

        if(!$list){
            return $this->error('没有数据');
        }

        $pay_skpt = ['nopay','wxpay','alipay'];

        foreach ($list as $key => $value) {

            // $where = [];
            // $where['user_id'] = $value['user_id'];
            // $where['sys_status'] = "normal";
            // $where['status'] = "normal";
            // if($value['pay_type']>0){
            //      $where['pay_skpt'] = $pay_skpt[$value['pay_type']];
            // }

            // $ewminfo = $userPayewm->field("pay_skpt,pay_nums,pay_ewm_image,type")->where($where)->select();

            // foreach ($ewminfo as $k => $v) {
            //     $ewminfo[$k]['pay_ewm_image'] =_sImage($v['pay_ewm_image']);
            // }

            // $list[$key]['payinfo'] = $ewminfo ? $ewminfo : [];
            unset($list[$key]['user_id']);
        }
        return $this->success('success',$list);
    }

    /**
     * 订单详情
     */
    public function detail(){ 

        $orderCash = new OrderCash();
        $userPayewm = new UserPayewm();

        $orderid = $this->request->param('orderid');

        $info = $orderCash->field('orderid,user_id,pay_type,bi_type,act_num,amount,rate')->where(['orderid'=>$orderid,'status'=>1,'pay_status'=>0])->find();

        if(!$info){
            return $this->error('订单不存在');
        }
        $ewminfo = $userPayewm->field("username,pay_skpt,pay_nums,pay_ewm_image,type")->where(['user_id'=>$info['user_id'],'pay_skpt'=>$info['pay_type']])->where(['sys_status'=>'normal','status'=>'normal'])->select();
        foreach ($ewminfo as $k => $v) {
            $ewminfo[$k]['pay_ewm_image'] =_sImage($v['pay_ewm_image']);
        }
        unset($info['user_id']);
        $info['payinfo'] = $ewminfo;
        return $this->success('success',$info);

    }

    /***
     * 出售
     * 预抢单
     */
    public function preorder(){ 

        $orderid = $this->request->param('orderid');        // 订单id
        $receiveid = input("business_id",'');               // 收款人id
        $address_name = input("type",'');                    // 收货人通道名称
        $address = input("address",'');                     // 收货人通道地址
        $backurl = input("backurl",'');                     // 回调地址

        if (!$orderid) {
            return $this->error('参数错误');
        }
        if (!$backurl) {
            return $this->error('参数错误');
        }        

        $orderCash = new OrderCash();
        $info = $orderCash->where(['orderid'=>$orderid])->find();
        if(!$info){
            return $this->error('订单不存在');
        }
        if($info['pay_status'] != 0){
            return $this->error('订单已被抢单');
        }


        $res = $orderCash->update(['pay_status'=>1,'buy_pintai_id'=>$this->access_key,'buyer_callback'=>$backurl],['id'=>$info['id']]);
        if($res){
            return $this->success('抢单成功',request()->domain().'/cash/#/?orderid='.$orderid.'&access_key='.$this->access_key);
        }else{
            return $this->error('抢单失败');
        }

    }

    /**
    * 支付订单
    */   
/*    public function payOrder(){ 

        $orderid = $this->request->param('orderid');        // 订单id
        $receiveid = input("business_id",'');               // 收款人id
        $pay_type = $this->request->param('pay_type','');  // 支付方式
        $pinzheng_image = $this->request->param('pinzheng_image');  // 凭证图片


        if(!$orderid){ 
            return $this->error('订单id不能为空');
        }
        // if(!$receiveid){ 
        //     return $this->error('收款人不能为空');
        // }
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

        $orderCash = new OrderCash();
        $info = $orderCash->where(['orderid'=>$orderid])->find();
        if(!$info){
            return $this->error('订单不存在');
        }
      
        if($info['pay_status'] == 0){
            return $this->error('请先预抢单');
        }
        if($info['pay_status'] >= 2){
            return $this->error('订单已处理');
        }


        $data = [
            'pay_status' => 2,
            'pay_time' => time(),
            'pay_type' => $pay_type,
            'pinzheng_image'=>$pinzheng_image,
        ];

        $res = $orderCash->update($data,['id'=>$info['id']]);
        if($res){
            //通知买方预抢单成功
            $taskModel = new Task();
            $data = [
                'name' => 'buy',
                'message'=>'',
                'params' => [
                    'orderid'=> $orderid,
                    'url'  => $info['buyer_callback'],
                    'pay_status'=>2
                ]
            ];
            $taskModel->addTask($data,"Buy");
            return $this->success('下单成功，等待确认');
        }else{
            return $this->error('下单失败');
        }

    }*/

}