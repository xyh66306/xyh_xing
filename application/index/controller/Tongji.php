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
        $cj_profit = truncateDecimal($cj_user_fee + $cj_supply_fee,4);

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
        $total_supply_number_today = Db::name("supply_usdt")->where("pay_status", 3)->whereTime("createtime", "today")->sum("usdt");

        echo "<br/>";
        echo "<br/>";
        echo "===============";          
        echo "<br/>";              
        echo $today;
        echo "<br/>";        
        echo "===============";                        
        echo "<br/>";
        echo "商户提现<br/>";
        echo "数量：".$total_supply_tx_number."条<br/>";
        echo "提现数量：".$total_supply_number."USDT<br/>";
        echo "提现手续费：".$total_supply_tx_fee."USDT<br/>";      
        echo "今日提币：".$total_supply_number_today."USDT<br/>";  


        $total_user_number = Db::name("user_usdt")->where("status", 'normal')->sum("num");
        $total_user_cz_number = Db::name("user_usdt")->where("status", 'normal')->count("id");
        $total_user_number_fee = Db::name("user_usdt")->where("status", 'normal')->sum("fee");
        $total_user_number_today = Db::name("user_usdt")->whereTime("createtime", "today")->where("status", 'normal')->sum("num");

        echo "<br/>";
        echo "承兑商充值<br/>";
        echo "数量：".$total_user_cz_number."条<br/>";
        echo "今日充值：".$total_user_number_today."USDT<br/>";          
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
        $duizhang1 = Db::name("user")->field('id,usdt,username')->where("id", 168024)->value("usdt");
        $duizhang2 = Db::name("user")->field('id,usdt,username')->where("id", 168023)->value("usdt");
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


        // $zhuanru2dui =   Db::name("user_usdt_log")->where("flow_type",1)->where("type",1)->whereLike("beizhu", "转入:656048398@qq.com")->sum("usdt");
        // $zhuanru1dui =   Db::name("user_usdt_log")->where("flow_type",1)->where("type",1)->whereLike("beizhu", "转入:xiaoqiang857775@163.com")->sum("usdt");
        // $zhuanruspark =   Db::name("user_usdt_log")->where("flow_type",1)->where("type",1)->whereLike("beizhu", "转入:1541631503@qq.com")->sum("usdt");

        // echo "<br/>";
        // echo "1队转入：".$zhuanru1dui."<br/>";
        // echo "2队转入：".$zhuanru2dui."<br/>";
        // echo "spark转入：".$zhuanruspark."<br/>";

        $zhuanchu2dui =   Db::name("user_usdt_log")->where("flow_type",2)->where("type",1)->where("user_id", 168023)->sum("usdt");
        $zhuanchu1dui =   800.8622;
        $zhuanchuspark =   Db::name("user_usdt_log")->where("flow_type",2)->where("type",1)->where("user_id", 168022)->sum("usdt");

        echo "<br/>";
        echo "1队转出：".$zhuanchu1dui."<br/>";
        echo "2队转出：".$zhuanchu2dui."<br/>";
        echo "spark转出：".$zhuanchuspark."<br/>";        


        $Commission = new Commission();
        $fanyong_daili_total_2026 = $Commission->where(['chaoshi' => 1, 'status' => 1])->whereNotIn('p_userid', '168023,168024,168022')->sum("money");
        $fanyong_daili_total = $fanyong_daili_total_2026+ 691.5565;       
        $fanyong_duizhang_total_2026 = $Commission->where(['chaoshi' => 1, 'status' => 1])->whereIn('p_userid', '168023,168024,168022')->sum("money");
        $fanyong_duizhang_total = $fanyong_duizhang_total_2026+ 670.9940;       
        $company = Db::name("company")->where("id", 1)->find();

        // echo "=================";
        // echo $company['usdt'];
        $total_supply_recharge_fee = Db::name("supply_recharge")->where("pay_status", 3)->sum("fee");
        $total_supply_recharge_fee2 = Db::name("supply_recharge")->where("pay_status", 3)->where("id","<",3)->sum("fee");

        $company_usdt_all = truncateDecimal($company['usdt'] + $total_supply_recharge_fee2 + 3039.1797); //-353.023


        // $dz1zhuanchu = Db::name("user_usdt_log")->where("type",1)->where("user_id", 168024)->value("usdt");

        

        echo "<br/>";
        echo "盈利：<br/>";
        echo "入金盈利：".$rj_profit."<br/>";
        echo "出金盈利：".$cj_profit."<br/>";
        echo "商户提币手续费：".$total_supply_tx_fee."<br/>";
        echo "商户充币手续费：".$total_supply_recharge_fee."<br/>";
        echo "承兑商充值手续费：".$total_user_number_fee."<br/>";
        echo "承兑商代理返佣：".$fanyong_daili_total."<br/>";
        echo "队长返佣：".$fanyong_duizhang_total."<br/>";
        echo "公司资产：".$company_usdt_all."<br/>";
        echo "==================<br/>";
        echo "入金盈利+出金盈利+商户提现手续费+商户充币手续费+承兑商充值手续费-代理返佣-1队-2队";
        echo "<br/>";
        $total_profit = $rj_profit + $cj_profit + $total_supply_tx_fee + $total_supply_recharge_fee + $total_user_number_fee-$fanyong_daili_total-$duizhang1-$duizhang2;
        echo "总盈利:".$total_profit."<br/>";
        $dz3zhuanchu = 800.8633;
        echo "总盈利-转出:".truncateDecimal($total_profit-$dz3zhuanchu,2)."<br/>";


        //商户充币+承兑商充值-商户提现-商户累计资产-承兑商累计资产 
        echo "==================<br/>";
        // echo "队长返佣+代理返佣+公司资产-总盈利";
        // echo "<br/>";
        // $diff = truncateDecimal($fanyong_duizhang_total + $fanyong_daili_total+ $company_usdt_all - $total_profit,4);
        // echo "差值:".$diff."<br/>";

        $spark = Db::name("user")->where("id", 168022)->value("usdt");
        // $spark2zhuanchu = Db::name("user_usdt_log")->where("type",1)->where("user_id", 168022)->sum("usdt");
        //3 spark+转出的+公司不再动的数字

        // $fanyong_spark_total_2026 = $Commission->where(['chaoshi' => 1, 'status' => 1,"p_userid"=>168022])->sum("money");

        // echo $fanyong_spark_total_2026;
        echo "<br/>";
        echo "总盈利-spark-公司资产";
        echo "<br/>";
        $diff = truncateDecimal($total_profit-$spark - $company_usdt_all,4);
        echo $total_profit."-".$spark."-".$company_usdt_all;
        echo "<br/>";
        echo "差值:".$diff."<br/>";


        // if($diff>20){
        //     $email = "870416982@qq.com";
        //     $msg = $today."差值为".$diff;
        //     $result = Emslib::notice($email, $msg,"resetpwd");
        // }

        return;
        
    }
}