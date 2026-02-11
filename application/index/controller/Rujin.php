<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\order\Rujin as RujinModel;
use app\common\model\User as UserModel;

class Rujin extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';


    public function index()
    {
        $RujinModel = new RujinModel();
        $time = time()-3600*1;
        $list = $RujinModel->where("pay_status","<=",1)->where('ctime','<',$time)->select();

        foreach ($list as $key => $value) {
            $RujinModel->where("id",$value['id'])->update(["status"=>2,'pay_status'=>5]);
        }
        
    }




    public function notice(){

        $RujinModel = new RujinModel();
        $time = time() - 60*5;
        $list = $RujinModel->where("pay_status",2)->where("pay_time","<=",$time)->select();

        if(!$list){
            return;
        }

        foreach ($list as $key => $value) {

            $userModel = new UserModel();
            $userInfo = $userModel->where(['id'=>$value['user_id']])->find();
            $exportData['type']     = "sendEmsNotice";
            $exportData['email']    = $userInfo['email'];
            $exportData['orderid']  = $value['orderid'];
            $exportData['user_id']    = $value['user_id'];
            $jobClass = 'app\job\Notice@fire';
            \think\Queue::push($jobClass, $exportData);//加入队列
        }

    }



}
