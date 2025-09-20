<?php
namespace app\api\controller;
use app\common\controller\Api;
use app\common\model\User as UserModel;
use app\common\model\order\Rujin as OrderCashModel;
use fast\Random;
use think\Config;
use think\Db;
use think\Validate;
use think\facade\Cache;

class Sale extends Api
{
    protected $noNeedRight = '*';


    public function orderout()
    {
        $page = input("page",1);
        $pay_status = input("pay_status","");
        $diqu = input("diqu",1);

        $userModel = new UserModel();
        $orderCashModel = new OrderCashModel();

        
        $user_id = $this->auth->id;

        $where['user_id'] = $user_id;
        // $where['diqu'] = $diqu;
        // $where['pay_status'] = $pay_status==0?['in',[1,2]]:['in',[3,4]];

        if($pay_status){
            $where['pay_status'] = $pay_status;
        }
        $list = $orderCashModel->where($where)->page($page)->select();

        foreach ($list as $key => $value) {

            if($value['pay_type']=='bank'){
                $list[$key]['pay_type_txt'] = '银行卡';
            }elseif($value['pay_type']=='alipay'){
                $list[$key]['pay_type_txt'] = '支付宝';
            }else{
                $list[$key]['pay_type_txt'] = '微信';
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
        }

        $data['count'] = $orderCashModel->where($where)->count("id");
        $data['list'] = $list;

        $this->success('', $data);

    }


    public function detailByOrderId()
    {
        $orderid = input("orderid",'');
        if(!$orderid){
            $this->error('参数错误');
        }
        $orderCashModel = new OrderCashModel();
        $data = $orderCashModel->where("orderid",$orderid)->order("id desc")->find();
        if(!$data){
            $this->error('数据不存在');
        }

        $data['ctime'] = date("Y-m-d H:i:s",$data['ctime']);
        $data['pinzheng_image'] = $data['pinzheng_image']?_sImage($data['pinzheng_image']):'';
        $this->success('', $data);

    }



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
            // $userModel->usdt_dj($data['act_num'],$this->auth->id,6,2);
            //扣除usdt 金额
            // $userModel->usdt($data['act_num'],$this->auth->id,3,2);

            $orderCashModel->update(['pay_status'=>3,'pay_time'=>time(),'utime'=>time()],['id'=>$data['id']]);
            // $this->commission($data);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作失败'.$e->getMessage());
        }
        
        $this->success('操作成功');

    }
    

}