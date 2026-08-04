<?php 
namespace app\index\controller;

use app\common\controller\Frontend;

use app\common\model\User as UserModel;
use think\Db;


/**
 * 出金接口
 */
class Sell extends Frontend
{

    
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    public function index()
    {

        echo "已取消订单";
    }


    /**
     * 
     * 回调
     * @return void
     */
    public function payOutCallback()
    { 

        recordLogs("payOutCallback",$_POST);
    }


    public function notice(){

        $count = Db::name("order_chujin")->where("pay_status", 1)->count();
        if($count==0){
            return;
        }
        $amounts = Db::name("order_chujin")->where("pay_status", 1)->column("withdrawAmount");

        foreach ($amounts as &$amount) {
            $amount = floatval($amount);
        }    
        $amounts_str = implode(",", $amounts);    

        $userModel = new UserModel();

        $where["status"] = "normal";
        $where["sfz_status"] = "1";
        $where["cj_switch"] = 1;
        $ulist = $userModel->where($where)->where("usdt","<",1000)->where("usdt","<>",0)->select();

        foreach ($ulist as $key => $value) {

            $exportData =[];
            $exportData['type']     = "sendSellChujinNotice";
            $exportData['email']    = $value['email'];
            $exportData['amount']    = $amounts_str;
            $exportData['count']    = $count;
            $jobClass = 'app\job\Notice@fire';
            \think\Queue::push($jobClass, $exportData);//加入队列
        }
        echo "已发送邮件通知";

    }




}
