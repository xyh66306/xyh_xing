<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Task;
use think\Queue;
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
        $supply_price =$rujinModel->where("pay_status",4)->sum("supply_usdt");
        $user_price =$rujinModel->where("pay_status",4)->sum("user_usdt");
        $user_fee =$rujinModel->where("pay_status",4)->sum("user_fee");
        $supply_fee =$rujinModel->where("pay_status",4)->sum("supply_fee");
        $total = $rujinModel->where("pay_status",4)->count("id");
        $company_price = truncateDecimal($user_fee + $supply_fee);

        $supply_today_price =$rujinModel->where("pay_status",4)->whereTime('ctime', 'today')->sum("supply_usdt");
        $user_today_price =$rujinModel->where("pay_status",4)->whereTime('ctime', 'today')->sum("user_usdt");
        $total_today_money =$rujinModel->where("pay_status",4)->whereTime('ctime', 'today')->sum("amount");
        $user_today_fee =$rujinModel->where("pay_status",4)->whereTime('ctime', 'today')->sum("user_fee");
        $supply_today_fee =$rujinModel->where("pay_status",4)->whereTime('ctime', 'today')->sum("supply_fee");
        $today_total = $rujinModel->where("pay_status",4)->whereTime('ctime', 'today')->count("id");
        $fanyong_total = $Commission->where(['source'=>1,'chaoshi'=>1,'status'=>1])->whereTime('ctime', 'today')->sum("money");
        $company_today_price = truncateDecimal($user_today_fee + $supply_today_fee-$fanyong_total);        

        
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
                    <td>今日累计数量(个)</td>
                    <td>今日累计金额(cny)</td>
                    <th>今日承兑商结算数量(usdt)</th>
                    <th>今日商户结算数量(usdt)</th>
                    <th>今日公司利润(usdt)</th>
                    <th>今日代理利润(usdt)</th>
                </tr>
                <tr>
                    <td>统计</td>
                    <td>{$total}</td>
                    <td>{$total_money}</td>
                    <td>{$supply_price}</td>
                    <td>{$user_price}</td>
                    <td>{$company_price}</td>
                    <td>{$today_total}</td>
                    <td>{$total_today_money}</td>
                    <td>{$user_today_price}</td>
                    <td>{$supply_today_price}</td>
                    <td>{$company_today_price}</td>
                    <td>{$fanyong_total}</td>
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
        $cj_company_price =  $cj_user_fee + $cj_supply_fee;


        $cj_supply_today_money = $chujinModel->where("pay_status",5)->whereTime('createtime', 'today')->sum("withdrawAmount");
        $cj_supply_today_money2 = $chujinModel->whereTime('createtime', 'today')->sum("withdrawAmount");
        $cj_supply_today_price = $chujinModel->where("pay_status",5)->whereTime('createtime', 'today')->sum("supply_usdt");
        $cj_user_today_price = $chujinModel->where("pay_status",5)->whereTime('createtime', 'today')->sum("user_usdt");
        $cj_user_today_fee = $chujinModel->where("pay_status",5)->whereTime('createtime', 'today')->sum("user_fee");
        $cj_supply_today_fee = $chujinModel->where("pay_status",5)->whereTime('createtime', 'today')->sum("supply_fee");
        $cj_today_total = $chujinModel->where("pay_status",5)->whereTime('createtime', 'today')->count("id");
        $cj_today_total2 = $chujinModel->whereTime('createtime', 'today')->count("id");
        $cj_fanyong_total = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1])->whereTime('ctime', 'today')->sum("money");
        $cj_company_today_price =  truncateDecimal($cj_user_today_fee + $cj_supply_today_fee-$cj_fanyong_total);


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
                    <td>累计数量(个)</td>
                    <th>今日商户提现金额（cny）</th>
                    <th>今日承兑商结算数量（usdt）</th>
                    <th>今日商户结算数量（usdt）</th>
                    <th>今日公司利润（usdt）</th>
                    <th>今日代理利润（usdt）</th>
                </tr>
                <tr>
                    <td>金额(USDT)</td>
                    <td>{$cj_total}</td>
                    <td>{$cj_supply_money}</td>
                    <td>{$cj_supply_price}</td>
                    <td>{$cj_user_price}</td>
                    <td>{$cj_company_price}</td>
                    <td>{$cj_today_total}/{$cj_today_total2}</td>
                    <td>{$cj_supply_today_money}/{$cj_supply_today_money2}</td>
                    <td>{$cj_user_today_price}</td>
                    <td>{$cj_supply_today_price}</td>
                    <td>{$cj_company_today_price}</td>
                    <td>{$cj_fanyong_total}</td>
                </tr>              
            </table>
        ";        
        echo $response;


        // $Supply = new Supply();
        // $list = $Supply->where("status",1)->select();

    }




}
