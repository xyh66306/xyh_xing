<?php
/*
 * @Author: Xyhao
 * @Date: 2025-10-10 09:14:35
 * @Description: 安徽爱喜网络科技有限公司
 */

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\model\Attachment;
use app\common\model\order\Rujin;
use app\common\model\order\Chujin;
use app\common\model\Commission;
use fast\Date;
use think\Db;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        try {
            \think\Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }
        $today = date("Y-m-d");
        $Commission = new Commission();
        $rujinModel = new Rujin();

        $rujin_total_money = $rujinModel->where("pay_status", 4)->sum("amount");              //入金总额(CNY)      
        $rujin_user_usdt = $rujinModel->where("pay_status", 4)->sum("user_usdt");             //入金总额(USDT)
        $rujin_user_fee = $rujinModel->where("pay_status", 4)->sum("user_fee");
        $rujin_supply_fee = $rujinModel->where("pay_status", 4)->sum("supply_fee");
        $rujin_total = $rujinModel->where("pay_status", 4)->count("id");
        $rujin_profit = truncateDecimal($rujin_user_fee+$rujin_supply_fee,4);


        $today_rujin_total_money = $rujinModel->where("pay_status", 4)->whereTime("ctime", "today")->sum("amount");              //入金总额(CNY)      
        $today_rujin_user_usdt = $rujinModel->where("pay_status", 4)->whereTime("ctime", "today")->sum("user_usdt");             //入金总额(USDT)
        $today_rujin_user_fee = $rujinModel->where("pay_status", 4)->whereTime("ctime", "today")->sum("user_fee");
        $today_rujin_supply_fee = $rujinModel->where("pay_status", 4)->whereTime("ctime", "today")->sum("supply_fee");
        $today_rujin_total = $rujinModel->where("pay_status", 4)->whereTime("ctime", "today")->count("id");
        $today_rujin_profit = truncateDecimal($today_rujin_user_fee+$today_rujin_supply_fee,4);


        $rj_data = [
            'rujin_total_money' => $rujin_total_money,
            'rujin_user_usdt' => $rujin_user_usdt,
            'rujin_profit' => $rujin_profit,
            'rujin_total' => $rujin_total,
            'today_rujin_total_money' => $today_rujin_total_money,
            'today_rujin_user_usdt' => $today_rujin_user_usdt,
            'today_rujin_profit' => $today_rujin_profit,
            'today_rujin_total' => $today_rujin_total,
        ];

        $this->assign($rj_data);


         //出金   
        $chujinModel = new Chujin();         
        $today_chujin_total_money = $chujinModel->where("pay_status", 5)->whereTime("createtime", "today")->sum("withdrawAmount");              //入金总额(CNY)      
        $today_chujin_user_usdt = $chujinModel->where("pay_status", 5)->whereTime("createtime", "today")->sum("user_usdt");             //入金总额(USDT)
        $today_chujin_user_fee = $chujinModel->where("pay_status", 5)->whereTime("createtime", "today")->sum("user_fee");
        $today_chujin_supply_fee = $chujinModel->where("pay_status", 5)->whereTime("createtime", "today")->sum("supply_fee");
        $today_chujin_total = $chujinModel->where("pay_status", 5)->whereTime("createtime", "today")->count("id");
        $today_chujin_profit = truncateDecimal($today_chujin_user_fee+$today_chujin_supply_fee,4);

        $cj_data =[
            'today_chujin_total_money' => $today_chujin_total_money,
            'today_chujin_user_usdt' => $today_chujin_user_usdt,
            'today_chujin_profit' => $today_chujin_profit,
            'today_chujin_total' => $today_chujin_total,
        ];
        $this->assign($cj_data);


        $supplyUsdt = Db::name("supply")->field('id,usdt,title')->where("id", '>', 2)->where("usdt", '<>', 0)->sum("usdt");
        $total_supply_number = Db::name("supply_usdt")->where("pay_status", 3)->sum("usdt");
        $supply_chongzhi = Db::name("supply_recharge")->where("pay_status",3)->sum("usdt");
        $rujin_supply_usdt = $rujinModel->where("pay_status", 4)->sum("supply_usdt");             //商户入金总额(USDT)
        $cj_supply_usdt = $chujinModel->where("pay_status", 5)->sum("supply_usdt");

        $supply_data = [
            'supplyUsdt' => $supplyUsdt,
            'total_supply_number' => $total_supply_number,
            'supply_chongzhi' => $supply_chongzhi,
            'rujin_supply_usdt' => $rujin_supply_usdt,
            'cj_supply_usdt' => $cj_supply_usdt,
        ];
        $this->assign($supply_data);


        $userUsdt = Db::name("user")->field('id,usdt,username')->where("usdt", '<>', 0)->sum("usdt");
        $user_chongzhi_number = Db::name("user_usdt")->where("status", 'normal')->sum("num");
        $rj_user_usdt = $rujinModel->where("pay_status", 4)->sum("user_usdt");
        $cj_user_usdt = $chujinModel->where("pay_status", 5)->sum("user_usdt");

        $user_data=[
            'userUsdt' => $userUsdt,
            'user_chongzhi_number' => $user_chongzhi_number,
            'rj_user_usdt' => $rj_user_usdt,
            'cj_user_usdt' => $cj_user_usdt,
        ];
        $this->assign($user_data);


        return $this->view->fetch();
    }

}
