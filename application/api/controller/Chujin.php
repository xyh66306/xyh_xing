<?php


namespace app\api\controller;
use app\common\controller\Api;

use app\common\model\OrderCash as OrderCashModel;
use app\common\model\User as UserModel;
use app\common\model\order\Chujin as ChujinModel;
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


class Chujin extends Api
{

     protected $noNeedRight = ['index','orderin','detail','addCash','uploadPzImg'];



    public function index()
    {
        $page = input("page",1);
        $orderid = input("orderid",'');
        $pay_type = input("pay_type",'');


        $ChujinModel = new ChujinModel();

        $where =[];

        if($orderid){
            $where['orderid'] = $orderid;
        }
        if($pay_type){
            $where['pay_type'] = $pay_type;
        }        

        $list = $ChujinModel->where($where)->where('pay_status',1)->where('status','normal')->page($page)->select();

        foreach ($list as $key => $value) {

            if($value['pay_type']=='bank'){
                $list[$key]['pay_type'] = '银行卡';
            }elseif($value['pay_type']=='alipay'){
                $list[$key]['pay_type'] = '支付宝';
            }else{
                $list[$key]['pay_type'] = '微信';
            }


            if($value['pay_status']==1){
                $list[$key]['pay_status_txt'] = '开始抢单';
            }elseif($value['pay_status']==2){
                $list[$key]['pay_status_txt'] = '已抢单';
            }elseif($value['pay_status']==3){
                $list[$key]['pay_status_txt'] = '已支付';
            }elseif($value['pay_status']==4){
                $list[$key]['pay_status_txt'] = '审核中';
            }elseif($value['pay_status']==5){
                $list[$key]['pay_status_txt'] = '已完成';
            }elseif($value['pay_status']==6){
                $list[$key]['pay_status_txt'] = '已取消';
            }


            $list[$key]['createtime'] = date("Y-m-d H:i:s",$value['createtime']);

        }

        $data['count'] = $ChujinModel->where($where)->where('pay_status',1)->count("id");
        $data['list'] = $list;

        $this->success('', $data);

    }

    public function detail()
    {
        $orderid = input("orderid",'');
        if(!$orderid){
            $this->error('参数错误');
        }
        $chujinModel = new ChujinModel();
        $data = $chujinModel->where("orderid",$orderid)->find();
        if(!$data){
            $this->error('数据不存在');
        }

        $data['ctime'] = date("Y-m-d H:i:s",$data['createtime']);
        $data['pay_ewm_image'] = $data['pay_ewm_image']?_sImage($data['pay_ewm_image']):'';
        $this->success('', $data);

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

 
        $chujinModel = new ChujinModel();

        $orderInfo = $chujinModel->where("orderid",$orderid)->find();

        if($orderInfo['pay_status']>1 && $orderInfo['user_id'] != $this->auth->id){
            $this->error('订单已被他人抢走');
        }

        if($orderInfo['pay_status']>=2 && $orderInfo['user_id'] == $this->auth->id){
            $this->error('请勿重复抢单');
        }


        Db::startTrans();
        try{ 
            $res =  $chujinModel->update(['pay_status'=>2,'user_id'=>$this->auth->id,'updatetime'=>time()],['id'=>$orderInfo['id']]);
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

        $chujinModel = new ChujinModel();

        $info = $chujinModel->where('orderid',$orderId)->find();
        if(!$info){
            $this->error('订单不存在');
        }
        if($info['user_usdt']<=0){
            $this->error('订单金额不足');
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
            $res2 =  $userModel->usdt_dj($info['user_usdt'],$this->auth->id,6,1);

            //添加用户金额
            $res3 =  $userModel->usdt($info['user_usdt'],$this->auth->id,7,1);

            if($res && $res2 && $res3){
                Db::commit();                
            } else {
                Db::rollback();
                $this->error('上传失败');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('上传成功');

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



        $ChujinModel = new ChujinModel();
        $userModel = new UserModel();
        $user_id = $this->auth->id;

        $where =[];
        $ids = [];

        if($pay_status){
            if($pay_status==1){
                $ids = [1,2];
            }elseif($pay_status==2){
                $ids = [3,4];
            }elseif($pay_status==3){
                $ids = [5];
            }
        }

        if($orderid){
            $where['orderid'] = $orderid;
        }
        if($pay_type){
            $where['pay_type'] = $pay_type;
        }        

        $where['user_id'] = $user_id;

        if($ids){
         $list = $ChujinModel->where($where)->where('pay_status','in',$ids)->where('status','normal')->page($page)->order('id desc')->select();
        } else {
            $list = $ChujinModel->where($where)->where('status','normal')->page($page)->order('id desc')->select();

        }

        foreach ($list as $key => $value) {

            if($value['pay_type']=='bank'){
                $list[$key]['pay_type'] = '银行卡';
            }elseif($value['pay_type']=='alipay'){
                $list[$key]['pay_type'] = '支付宝';
            }else{
                $list[$key]['pay_type'] = '微信';
            }

            $list[$key]['usdt'] = $value['user_usdt'];

            if($value['pay_status']==1){
                $list[$key]['pay_status_txt'] = '开始抢单';
            }elseif($value['pay_status']==2){
                $list[$key]['pay_status_txt'] = '已抢单';
            }elseif($value['pay_status']==3){
                $list[$key]['pay_status_txt'] = '已支付';
            }elseif($value['pay_status']==4){
                $list[$key]['pay_status_txt'] = '审核中';
            }elseif($value['pay_status']==5){
                $list[$key]['pay_status_txt'] = '已完成';
            }elseif($value['pay_status']==6){
                $list[$key]['pay_status_txt'] = '已取消';
            }


            $list[$key]['createtime'] = date("Y-m-d H:i:s",$value['updatetime']);

        }
        $data['user_usdt_count'] = $ChujinModel->where($where)->sum('user_usdt');
        $data['amount_count'] = $ChujinModel->where($where)->sum('withdrawAmount');
        $data['count'] = $ChujinModel->where($where)->count("id");
        $data['list'] = $list;

        $this->success('', $data);

    }



}