<?php

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

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';


    public function index()
    {
        return $this->view->fetch();
    }

    public function ceshi()
    {


        $supplyModel = new Supply();
        $info = $supplyModel->where('access_key', "1250803358")->find();

        $orderid = "56068";    
        $url = "https://asiacnbo.com/pay/back/XinHuoPay";
        $taskModel = new Task();
        $data = [
            'access_key'    => $info['access_key'],
            'access_secret' => $info['access_secret'],
            'name' => 'cash',
            'message' => '',
            'params' => [
                'orderid' =>  $orderid,
                'url'  =>$url,
                'pay_status' => 3
            ]
        ];            

        dump($data);
        $taskModel->addTask($data, "Sell");
        // return $this->view->fetch();

        // $data['orderid'] = "o202508011824004289";
        // $data['pay_status'] = 2;

        // $res = $this->postCurl("https://bingocn.wobeis.com/index/index/test",$data);

        // dump($res);

    }


    public function tongji(){


        $today = date("Y-m-d");

        $Commission = new Commission();
        $rujinModel = new Rujin();


        $total_money =$rujinModel->where("pay_status",4)->sum("amount");
        $supply_usdt =$rujinModel->where("pay_status",4)->sum("supply_usdt");
        $user_usdt =$rujinModel->where("pay_status",4)->sum("user_usdt");
        $user_fee =$rujinModel->where("pay_status",4)->sum("user_fee");
        $supply_fee =$rujinModel->where("pay_status",4)->sum("supply_fee");
        $total = $rujinModel->where("pay_status",4)->count("id");
        $fanyong_total = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1])->sum("money");
        $company_price = truncateDecimal($user_fee + $supply_fee);

        
        $supply_today_price =$rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'today')->sum("supply_usdt");
        $user_today_price =$rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'today')->sum("user_usdt");
        $total_today_money =$rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'today')->sum("amount");
        $user_today_fee =$rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'today')->sum("user_fee");
        $supply_today_fee =$rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'today')->sum("supply_fee");
        $today_total = $rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'today')->count("id");
        $today_total2 = $rujinModel->where("pay_status",'>=',2)->where("pay_status",'<',5)->where("status",1)->whereTime('ctime', 'today')->count("id");
        $fanyong_taday_total = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1])->whereTime('ctime', 'today')->sum("money");
        $fanyong_daili_taday_total = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1])->whereNotIn('id','168023,168024,168022')->whereTime('ctime', 'today')->sum("money");
        $company_today_price = truncateDecimal($user_today_fee + $supply_today_fee-$fanyong_taday_total);        

        $fanyong_taday_total_1d = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1,'p_userid'=>168024])->whereTime('ctime', 'today')->sum("money"); //1队
        $fanyong_taday_total_2d = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1,'p_userid'=>168023])->whereTime('ctime', 'today')->sum("money"); //2队
        $fanyong_taday_total_spark = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1,'p_userid'=>168022])->whereTime('ctime', 'today')->sum("money"); //spark
        
        

        $chujinModel = new Chujin();
        $cj_supply_money = $chujinModel->where("pay_status",5)->sum("withdrawAmount");
        $cj_supply_usdt = $chujinModel->where("pay_status",5)->sum("supply_usdt");
        $cj_user_usdt = $chujinModel->where("pay_status",5)->sum("user_usdt");
        $cj_user_fee = $chujinModel->where("pay_status",5)->sum("user_fee");
        $cj_supply_fee = $chujinModel->where("pay_status",5)->sum("supply_fee");
        $cj_total = $chujinModel->where("pay_status",5)->count("id");
        // $cj_company_price =  $cj_user_fee + $cj_supply_fee;
        // $cj_fanyong_total = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1])->sum("money");
        $cj_company_price =  $cj_user_fee + $cj_supply_fee;


        $cj_supply_today_money = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'today')->sum("withdrawAmount");
        $cj_supply_today_money2 = $chujinModel->where("pay_status","<=",5)->whereTime('updatetime', 'today')->sum("withdrawAmount");
        $cj_supply_today_price = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'today')->sum("supply_usdt");
        $cj_user_today_price = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'today')->sum("user_usdt");
        $cj_user_today_fee = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'today')->sum("user_fee");
        $cj_supply_today_fee = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'today')->sum("supply_fee");
        $cj_today_total = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'today')->count("id");
        $cj_today_total2 = $chujinModel->where("pay_status",'>=',1)->where("pay_status",'<=',5)->whereTime('updatetime', 'today')->count("id");
        $cj_taday_fanyong_total = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1])->whereTime('ctime', 'today')->sum("money");
        $cj_taday_daili_fanyong_total = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1])->whereNotIn('id','168023,168024,168022')->whereTime('ctime', 'today')->sum("money");
        $cj_company_today_price =  truncateDecimal($cj_user_today_fee + $cj_supply_today_fee-$cj_taday_fanyong_total);

        $cj_fanyong_taday_total_1d = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1,'p_userid'=>168024])->whereTime('ctime', 'today')->sum("money"); //1队
        $cj_fanyong_taday_total_2d = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1,'p_userid'=>168023])->whereTime('ctime', 'today')->sum("money"); //2队
        $cj_fanyong_taday_total_spark = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1,'p_userid'=>168022])->whereTime('ctime', 'today')->sum("money"); //spark
    


       /* $response = "
            <h3>兑出{$today}订单统计</h3>
            <table border='1' cellpadding='10' cellspacing='0'>
                <tr>
                    <th>&nbsp;</th>
                    <td>累计数量(个)</td>
                    <th>商户提现金额（cny）</th>
                    <th>商户累计结算数量（usdt）</th>
                    <th>承兑商累计结算数量（usdt）</th>
                    <th>公司全部利润（usdt）</th>
                    <th>代理全部利润(usdt)</th>
                    <td>今日订单数量(个)</td>
                    <th>今日商户提现金额（cny）</th>
                    <th>今日承兑商结算数量（usdt）</th>
                    <th>今日商户结算数量（usdt）</th>
                    <th>今日公司利润(usdt)</th>
                    <th>今日代理利润（usdt）</th>
                    <th>1队利润(usdt)</th>
                    <th>2队利润(usdt)</th>
                    <th>spark利润(usdt)</th>                      
                </tr>
                <tr>
                    <td>统计</td>
                    <td>{$cj_total}</td>
                    <td>{$cj_supply_money}</td>
                    <td>{$cj_supply_price}</td>
                    <td>{$cj_user_price}</td>
                    <td>{$cj_company_price}</td>
                    <td>{$cj_fanyong_total}</td>
                    <td>{$cj_today_total}/{$cj_today_total2}</td>
                    <td>{$cj_supply_today_money}/{$cj_supply_today_money2}</td>
                    <td>{$cj_user_today_price}</td>
                    <td>{$cj_supply_today_price}</td>
                    <td>{$cj_company_today_price}</td>
                    <td>{$cj_taday_daili_fanyong_total}</td>
                    <td>{$cj_fanyong_taday_total_1d}</td>
                    <td>{$cj_fanyong_taday_total_2d}</td>
                    <td>{$cj_fanyong_taday_total_spark}</td>                        
                </tr>              
            </table>
        ";        
        echo $response;*/

 



        // $company_today_profit = truncateDecimal($cj_company_today_price + $company_today_price);


        //公司资产
       /* $response = "
            <h3>公司资产</h3>
            <table border='1' cellpadding='10' cellspacing='0'>
                <tr>
                    <th>&nbsp;</th>
                    <td>公司资产</td>
                    <th>1队资产</th>
                    <th>2队资产</th>
                    <th>spark资产</th>
                    <th>累计资产</th>
                    <th>今日1队利润</th>
                    <th>今日2队利润</th>
                    <th>今日spark利润</th>
                    <th>今日公司账户利润</th>
                    <th>今日累计利润</th>
                </tr>
                <tr>
                    <td>统计</td>
                    <td>{$company['usdt']}</td>
                    <td>{$oneteam}</td>
                    <td>{$twoteam}</td>
                    <td>{$spark}</td>   
                    <td>{$my_profit}</td>   
                    <td>{$one_today_profit}</td>   
                    <td>{$two_today_profit}</td>
                    <td>{$spark_today_profit}</td>
                    <td>{$company_today_profit}</td>    
                    <td>{$total_today_profit}</td>                      
                </tr>              
            </table>
        ";  
        echo $response;*/
        
        $supply = Db::name("supply")->where("id",'>',2)->column('usdt');
        $supply_freeze_usdt = Db::name("supply")->where("id",'>',2)->column('freeze_usdt');
        $company = Db::name("company")->where("id",1)->find();
        $oneteam = Db::name("user")->where("id",168023)->value('usdt');
        $twoteam = Db::name("user")->where("id",168024)->value('usdt');
        $spark = Db::name("user")->where("id",168022)->value('usdt');        
        $userTotalUsdt = Db::name("user")->where("usdt","<>",0)->sum('usdt');
        $userTotalUsdtdJ = Db::name("user")->where("usdt","<>",0)->sum('usdt_dj');

        $my_profit = truncateDecimal($company['usdt'] + $oneteam + $twoteam + $spark + 3039.1797);

        //返佣
        $fanyong_taday_total_1d = $Commission->where(['chaoshi'=>1,'status'=>1,'p_userid'=>168024])->whereTime('ctime', 'today')->sum("money"); //1队
        $fanyong_taday_total_2d = $Commission->where(['chaoshi'=>1,'status'=>1,'p_userid'=>168023])->whereTime('ctime', 'today')->sum("money"); //2队
        $fanyong_taday_total_spark = $Commission->where(['chaoshi'=>1,'status'=>1,'p_userid'=>168022])->whereTime('ctime', 'today')->sum("money"); //spark


        //承兑商充值
        $total_user_number = Db::name("user_usdt")->where("status",'normal')->sum("num");
        $total_user_cz_number = Db::name("user_usdt")->where("status",'normal')->count("id");    
        
        //服务商提现
        $total_supply_number = Db::name("supply_usdt")->where("pay_status",3)->sum("usdt");
        $total_supply_cz_number = Db::name("supply_usdt")->where("pay_status",3)->count("id");           
        $total_supply_cz_fee = Db::name("supply_usdt")->where("pay_status",3)->sum("fee");
        $totol_supply_usdt = $supply[0]+$supply[1];
        $total_supply_freeze_usdt = $supply_freeze_usdt[0]+$supply_freeze_usdt[1];

        $data = [
            "today"             => $today,
            'userTotalUsdt'     => $userTotalUsdt,
            'userTotalUsdt'     => $userTotalUsdt,
            'userTotalUsdtdJ'   => $userTotalUsdtdJ,
            'oneteam'           => $oneteam,
            'twoteam'           => $twoteam,
            'spark'             => $spark,
            'company_usdt'      => $company['usdt'],
            'company_usdt_all'  => $company['usdt'] + 3039.1797,
            'my_profit'         => $my_profit,
            'fanyong_taday_total_1d'  => $fanyong_taday_total_1d,
            'fanyong_taday_total_2d'  => $fanyong_taday_total_2d,
            'fanyong_taday_total_spark'  => $fanyong_taday_total_spark,  
            'supply'            => $supply,
            'totol_supply_usdt' => $totol_supply_usdt,
            'total_supply_freeze_usdt'=>$total_supply_freeze_usdt
        ];
        $this->assign($data);

        $rjdata = [
            'total'        =>$total,
            'total_money' => $total_money,
            'supply_usdt' => $supply_usdt,
            'user_usdt'   => $user_usdt,
            'user_fee'     =>$user_fee,
            'supply_fee'    =>$supply_fee,
            'today_total'   => $today_total,
            'today_total2'  => $today_total2,
            'fanyong_taday_total'=>$fanyong_taday_total,
            'total_today_money' => $total_today_money,
            'supply_today_price' =>$supply_today_price,
            'user_today_price' => $user_today_price,
            'company_today_price'   => $company_today_price,
            'fanyong_daili_taday_total'=>$fanyong_daili_taday_total,
            'company_price' => $company_price,
        ];
        $this->assign($rjdata);    
        
        $cjdata = [
            'cj_total'        =>$cj_total,
            'cj_supply_money' => $cj_supply_money,
            'cj_supply_usdt' => $cj_supply_usdt,
            'cj_user_usdt'   => $cj_user_usdt,
            'cj_user_fee'     =>$cj_user_fee,
            'cj_supply_fee'    =>$cj_supply_fee,
            'cj_today_total'   => $cj_today_total,
            'cj_today_total2'  => $cj_today_total2,

            // 'fanyong_taday_total'=>$fanyong_taday_total,
            'cj_supply_today_money' => $cj_supply_today_money,
            'cj_supply_today_price' =>$cj_supply_today_price,
            'cj_user_today_price' => $cj_user_today_price,
            'cj_company_today_price'   => $cj_company_today_price,
            'cj_taday_daili_fanyong_total'=>$cj_taday_daili_fanyong_total,
            'cj_company_price' => $cj_company_price,
        ];
        $this->assign($cjdata);      
        
        $cztxData = [
            'total_user_number'        =>$total_user_number,
            'total_user_cz_number' => $total_user_cz_number,
            'total_supply_number' => $total_supply_number,
            'total_supply_cz_number'   => $total_supply_cz_number,
            'total_supply_cz_fee'=>$total_supply_cz_fee,
        ];
        $this->assign($cztxData); 

        //误差计算
        // 入金利润 + 出金利润 = 分润
        $all_company_price = $company_price + $cj_company_price;

        $commission_all_2026 = $Commission->where(['chaoshi'=>1,'status'=>1])->sum("money");
        $commission_all_2025 = 691.5565;
        $commission_all = $commission_all_2025 + $commission_all_2026;

        // 所有分润
        $diff = $total_user_number - $userTotalUsdt - $total_supply_number - $totol_supply_usdt - $all_company_price -$total_supply_freeze_usdt + $commission_all;

        $this->assign('all_company_price',$all_company_price); 
        $this->assign("diff",$diff);
        $this->assign("commission_all",$commission_all); 
        return $this->fetch();

    }

    /**
     * 昨日统计
     */
    public function tongjizr(){


        $today = date("Y-m-d", strtotime("yesterday"));

        $Commission = new Commission();
        $rujinModel = new Rujin();
        $total_money =$rujinModel->where("pay_status",4)->sum("amount");
        $supply_price =$rujinModel->where("pay_status",4)->sum("supply_usdt");
        $user_price =$rujinModel->where("pay_status",4)->sum("user_usdt");
        $user_fee =$rujinModel->where("pay_status",4)->sum("user_fee");
        $supply_fee =$rujinModel->where("pay_status",4)->sum("supply_fee");
        $total = $rujinModel->where("pay_status",4)->count("id");
        $fanyong_total = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1])->sum("money");
        $company_price = truncateDecimal($user_fee + $supply_fee-$fanyong_total);

        $supply_today_price =$rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'yesterday')->sum("supply_usdt");
        $user_today_price =$rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'yesterday')->sum("user_usdt");
        $total_today_money =$rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'yesterday')->sum("amount");
        $user_today_fee =$rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'yesterday')->sum("user_fee");
        $supply_today_fee =$rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'yesterday')->sum("supply_fee");
        $today_total = $rujinModel->where("pay_status",4)->where("status",1)->whereTime('ctime', 'yesterday')->count("id");
        $today_total2 = $rujinModel->where("pay_status",'>=',2)->where("pay_status",'<',5)->where("status",1)->whereTime('ctime', 'yesterday')->count("id");
        $fanyong_taday_total = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1])->whereNotIn('id','168023,168024,168022')->whereTime('ctime', 'yesterday')->sum("money");
        $company_today_price = truncateDecimal($user_today_fee + $supply_today_fee-$fanyong_taday_total);        

        $fanyong_taday_total_1d = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1,'p_userid'=>168024])->whereTime('ctime', 'yesterday')->sum("money"); //1队
        $fanyong_taday_total_2d = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1,'p_userid'=>168023])->whereTime('ctime', 'yesterday')->sum("money"); //2队
        $fanyong_taday_total_spark = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1,'p_userid'=>168022])->whereTime('ctime', 'yesterday')->sum("money"); //spark
        
        // 使用表格形式输出
        $response = "
            <h3>兑入{$today}订单统计</h3>
            <table border='1' cellpadding='10' cellspacing='0'>
                <tr>
                    <th>&nbsp;</th>
                    <td>累计数量(个)</td>
                    <td>累计金额（cny）</td>
                    <th>商户累计结算数量(usdt)</th>
                    <th>承兑商累计结算数量(usdt)</th>
                    <th>公司全部利润(usdt)</th>
                    <th>代理全部利润(usdt)</th>
                    <td>今日订单数量(个)</td>
                    <td>今日累计金额(cny)</td>
                    <th>今日承兑商结算数量(usdt)</th>
                    <th>今日商户结算数量(usdt)</th>
                    <th>今日公司利润(usdt)</th>
                    <th>今日代理利润(usdt)</th>
                    <th>1队利润(usdt)</th>
                    <th>2队利润(usdt)</th>
                    <th>spark利润(usdt)</th>                    
                </tr>
                <tr>
                    <td>统计</td>
                    <td>{$total}</td>
                    <td>{$total_money}</td>
                    <td>{$supply_price}</td>
                    <td>{$user_price}</td>
                    <td>{$company_price}</td>
                    <td>{$fanyong_total}</td>
                    <td>{$today_total}/$today_total2</td>
                    <td>{$total_today_money}</td>
                    <td>{$user_today_price}</td>
                    <td>{$supply_today_price}</td>
                    <td>{$company_today_price}</td>
                    <td>{$fanyong_taday_total}</td>
                    <td>{$fanyong_taday_total_1d}</td>
                    <td>{$fanyong_taday_total_2d}</td>
                    <td>{$fanyong_taday_total_spark}</td>                    
                </tr>              
            </table>
        ";        
        echo $response;

        $chujinModel = new Chujin();
        $cj_supply_money = $chujinModel->where("pay_status",5)->sum("withdrawAmount");
        $cj_supply_price = $chujinModel->where("pay_status",5)->sum("supply_usdt");
        $cj_user_price = $chujinModel->where("pay_status",5)->sum("user_usdt");
        $cj_user_fee = $chujinModel->where("pay_status",5)->sum("user_fee");
        $cj_supply_fee = $chujinModel->where("pay_status",5)->sum("supply_fee");
        $cj_total = $chujinModel->where("pay_status",5)->count("id");
        // $cj_company_price =  $cj_user_fee + $cj_supply_fee;
        $cj_fanyong_total = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1])->sum("money");
        $cj_company_price =  $cj_user_fee + $cj_supply_fee - $cj_fanyong_total;


        $cj_supply_today_money = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'yesterday')->sum("withdrawAmount");
        $cj_supply_today_money2 = $chujinModel->where("pay_status","<=",5)->whereTime('updatetime', 'yesterday')->sum("withdrawAmount");
        $cj_supply_today_price = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'yesterday')->sum("supply_usdt");
        $cj_user_today_price = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'yesterday')->sum("user_usdt");
        $cj_user_today_fee = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'yesterday')->sum("user_fee");
        $cj_supply_today_fee = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'yesterday')->sum("supply_fee");
        $cj_today_total = $chujinModel->where("pay_status",5)->whereTime('updatetime', 'yesterday')->count("id");
        $cj_today_total2 = $chujinModel->where("pay_status",'>=',1)->where("pay_status",'<=',5)->whereTime('updatetime', 'yesterday')->count("id");


        $cj_taday_fanyong_total = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1])->whereNotIn('id','168023,168024,168022')->whereTime('ctime', 'yesterday')->sum("money");
        $cj_company_today_price =  truncateDecimal($cj_user_today_fee + $cj_supply_today_fee-$cj_taday_fanyong_total);

        $cj_fanyong_taday_total_1d = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1,'p_userid'=>168024])->whereTime('ctime', 'yesterday')->sum("money"); //1队
        $cj_fanyong_taday_total_2d = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1,'p_userid'=>168023])->whereTime('ctime', 'yesterday')->sum("money"); //2队
        $cj_fanyong_taday_total_spark = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1,'p_userid'=>168022])->whereTime('ctime', 'yesterday')->sum("money"); //spark
    

        $response = "
            <h3>兑出{$today}订单统计</h3>
            <table border='1' cellpadding='10' cellspacing='0'>
                <tr>
                    <th>&nbsp;</th>
                    <td>累计数量(个)</td>
                    <th>商户提现金额（cny）</th>
                    <th>商户累计结算数量（usdt）</th>
                    <th>承兑商累计结算数量（usdt）</th>
                    <th>公司全部利润（usdt）</th>
                    <th>代理全部利润(usdt)</th>
                    <td>今日订单数量(个)</td>
                    <th>今日商户提现金额（cny）</th>
                    <th>今日承兑商结算数量（usdt）</th>
                    <th>今日商户结算数量（usdt）</th>
                    <th>今日公司利润(usdt)</th>
                    <th>今日代理利润（usdt）</th>
                    <th>1队利润(usdt)</th>
                    <th>2队利润(usdt)</th>
                    <th>spark利润(usdt)</th>                      
                </tr>
                <tr>
                    <td>统计</td>
                    <td>{$cj_total}</td>
                    <td>{$cj_supply_money}</td>
                    <td>{$cj_supply_price}</td>
                    <td>{$cj_user_price}</td>
                    <td>{$cj_company_price}</td>
                    <td>{$cj_fanyong_total}</td>
                    <td>{$cj_today_total}/{$cj_today_total2}</td>
                    <td>{$cj_supply_today_money}/{$cj_supply_today_money2}</td>
                    <td>{$cj_user_today_price}</td>
                    <td>{$cj_supply_today_price}</td>
                    <td>{$cj_company_today_price}</td>
                    <td>{$cj_taday_fanyong_total}</td>
                    <td>{$cj_fanyong_taday_total_1d}</td>
                    <td>{$cj_fanyong_taday_total_2d}</td>
                    <td>{$cj_fanyong_taday_total_spark}</td>                        
                </tr>              
            </table>
        ";        
        echo $response;

    }

}
