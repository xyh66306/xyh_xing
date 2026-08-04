<?php
/*
 * @Author: Xyhao
 * @Date: 2026-07-20 10:22:38
 * @Description: 安徽爱喜网络科技有限公司
 */

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Task;
use think\Queue;
use think\Db;
use app\common\model\Supply;
use app\common\model\order\Rujin;
use app\common\model\order\Chujin;
use app\common\model\Commission;
use app\common\library\Ems as Emslib;

class Tongji extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';    

    public function index(){


        $today = date("Y-m-d");
        //昨日
        $yesterday = date("Y-m-d", strtotime("-1 day"));

        $Commission = new Commission();
        $rujinModel = new Rujin();

        $rj_total = $rujinModel->where("pay_status", 4)->count("id");
        $rj_user_usdt = $rujinModel->where("pay_status", 4)->sum("user_usdt");
        $rj_user_fee = $rujinModel->where("pay_status", 4)->sum("user_fee");
        $rj_supply_fee = $rujinModel->where("pay_status", 4)->sum("supply_fee");        
        $rj_profit = truncateDecimal($rj_user_fee + $rj_supply_fee, 4);


        echo "入金<br/>";
        echo "数量：".$rj_total."条<br/>";
        echo "承兑商：".$rj_user_usdt."USDT<br/>";
        echo "手续费：".$rj_profit."USDT<br/>";

        $chujinModel = new Chujin();
        $cj_total = $chujinModel->where("pay_status", 5)->count("id");
        $cj_user_usdt = $chujinModel->where("pay_status", 5)->sum("user_usdt");
        $cj_user_fee = $chujinModel->where("pay_status", 5)->sum("user_fee");
        $cj_supply_fee = $chujinModel->where("pay_status", 5)->sum("supply_fee");        
        $cj_profit = truncateDecimal($cj_user_fee + $cj_supply_fee,5);

        echo "<br/>";
        echo "出金<br/>";
        echo "数量：".$cj_total."条<br/>";
        echo "承兑商：".$cj_user_usdt."USDT<br/>";
        echo "手续费：".$cj_profit."USDT<br/>";

        $supply_chongzhi = Db::name("supply_recharge")->where("pay_status",3)->sum("usdt");
        $supply_chongzhi_count = Db::name("supply_recharge")->where("pay_status",3)->count("id");    
        $total_supply_recharge_fee = Db::name("supply_recharge")->where("pay_status", 3)->sum("fee");         
        $total_supply_recharge_fee = $total_supply_recharge_fee+461;   

        echo "<br/>";
        echo "商户充币<br/>";
        echo "数量：".$supply_chongzhi_count."条<br/>";
        echo "充币数量：".$supply_chongzhi."USDT<br/>";
        echo "充币手续费：".$total_supply_recharge_fee."USDT<br/>";


        $total_supply_number = Db::name("supply_usdt")->where("pay_status", 3)->sum("usdt");
        $total_supply_tx_number = Db::name("supply_usdt")->where("pay_status", 3)->count("id");
        $total_supply_tx_fee = Db::name("supply_usdt")->where("pay_status", 3)->sum("fee");

        echo "<br/>";
        echo "商户提现<br/>";
        echo "数量：".$total_supply_tx_number."条<br/>";
        echo "提现数量：".$total_supply_number."USDT<br/>";
        echo "提现手续费：".$total_supply_tx_fee."USDT<br/>";        


        $total_user_number = Db::name("user_usdt")->where("status", 'normal')->sum("num");
        $total_user_cz_number = Db::name("user_usdt")->where("status", 'normal')->count("id");
        $total_user_number_fee = Db::name("user_usdt")->where("status", 'normal')->sum("fee");

        echo "<br/>";
        echo "承兑商充值<br/>";
        echo "数量：".$total_user_cz_number."条<br/>";
        echo "充值数量：".$total_user_number."USDT<br/>";
        echo "充值手续费：".$total_user_number_fee."USDT<br/>";     

        $supplyList = Db::name("supply")->field('id,usdt,title')->where("id", '>', 2)->where("usdt", '<>', 0)->select();
        $supplyUsdt = Db::name("supply")->field('id,usdt,title')->where("id", '>', 2)->where("usdt", '<>', 0)->sum("usdt");
        echo "<br/>";
        echo "商户资产<br/>";
        foreach($supplyList as $item){
            echo $item['title']."：".$item['usdt']."<br/>";
        }
        echo "==================<br/>";
        echo "累计资产:".$supplyUsdt."<br/>";



        $uesrList = Db::name("user")->field('id,usdt,username')->where("usdt", '<>', 0)->select();
        $userUsdt = Db::name("user")->field('id,usdt,username')->where("usdt", '<>', 0)->sum("usdt");
        echo "<br/>";
        echo "承兑商资产<br/>";
        foreach($uesrList as $item){
            echo $item['username']."：".$item['usdt']."<br/>";
        }
        echo "==================<br/>";
        echo "累计资产:".$userUsdt."<br/>";


        $supply_today_price = $rujinModel->where("pay_status", 4)->where("status", 1)->whereTime('ctime', 'today')->sum("supply_usdt");
        $cj_user_today_price = $chujinModel->where("pay_status", 5)->whereTime('updatetime', 'today')->sum("user_usdt");
        echo "<br/>";
        echo "今日入金：".$supply_today_price."<br/>";
        echo "今日出金：".$cj_user_today_price."<br/>";
        echo "总入金：".$rj_user_usdt."<br/>";
        echo "总出金：".$cj_user_usdt."<br/>";

        $Commission = new Commission();
        $fanyong_daili_total_2026 = $Commission->where(['chaoshi' => 1, 'status' => 1])->whereNotIn('p_userid', '168023,168024,168022')->sum("money");
        $fanyong_daili_total = $fanyong_daili_total_2026+ 691.5565;       
        $fanyong_duizhang_total_2026 = $Commission->where(['chaoshi' => 1, 'status' => 1])->whereIn('p_userid', '168023,168024,168022')->sum("money");
        $fanyong_duizhang_total = $fanyong_duizhang_total_2026+ 670.9940;       

        $company = Db::name("company")->where("id", 1)->find();
        $company_usdt_all = truncateDecimal($company['usdt'] + 3039.1797-353.023);

        echo "<br/>";
        echo "盈利：<br/>";
        echo "入金盈利：".$rj_profit."<br/>";
        echo "出金盈利：".$cj_profit."<br/>";
        echo "商户提币手续费：".$total_supply_tx_fee."<br/>";
        echo "商户充币手续费：".$total_supply_recharge_fee."<br/>";
        echo "承兑商充值手续费：".$total_user_number_fee."<br/>";
        echo "代理返佣：".$fanyong_daili_total."<br/>";
        echo "队长返佣：".$fanyong_duizhang_total."<br/>";
        echo "公司资产：".$company_usdt_all."<br/>";
        echo "==================<br/>";
        echo "入金盈利+出金盈利+商户提现手续费+商户充币手续费+承兑商充值手续费";
        echo "<br/>";
        $total_profit = $rj_profit + $cj_profit + $total_supply_tx_fee + $total_supply_recharge_fee + $total_user_number_fee;
        echo "总盈利:".$total_profit."<br/>";


        //商户充币+承兑商充值-商户提现-商户累计资产-承兑商累计资产 
        echo "==================<br/>";
        echo "队长返佣+代理返佣+公司资产-总盈利";
        echo "<br/>";
        $diff = truncateDecimal($fanyong_duizhang_total + $fanyong_daili_total+ $company_usdt_all - $total_profit,4);
        echo "差值:".$diff."<br/>";


        if($diff>20){
            $email = "870416982@qq.com";
            $msg = $today."差值为".$diff;
            $result = Emslib::notice($email, $msg,"resetpwd");
        }

        return;
        
    }
}