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
                'url'  => $url,
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


    public function tongji()
    {


        $today = date("Y-m-d");

        $Commission = new Commission();
        $rujinModel = new Rujin();


        $total_money = $rujinModel->where("pay_status", 4)->sum("amount");
        $supply_usdt = $rujinModel->where("pay_status", 4)->sum("supply_usdt");
        $user_usdt = $rujinModel->where("pay_status", 4)->sum("user_usdt");
        $user_fee = $rujinModel->where("pay_status", 4)->sum("user_fee");
        $supply_fee = $rujinModel->where("pay_status", 4)->sum("supply_fee");
        $total = $rujinModel->where("pay_status", 4)->count("id");
        $fanyong_total = $Commission->where(['source' => 1, 'chaoshi' => 1, 'status' => 1])->sum("money");
        $company_price = truncateDecimal($user_fee + $supply_fee);


        $supply_today_price = $rujinModel->where("pay_status", 4)->where("status", 1)->whereTime('ctime', 'today')->sum("supply_usdt");
        $user_today_price = $rujinModel->where("pay_status", 4)->where("status", 1)->whereTime('ctime', 'today')->sum("user_usdt");
        $total_today_money = $rujinModel->where("pay_status", 4)->where("status", 1)->whereTime('ctime', 'today')->sum("amount");
        $user_today_fee = $rujinModel->where("pay_status", 4)->where("status", 1)->whereTime('ctime', 'today')->sum("user_fee");
        $supply_today_fee = $rujinModel->where("pay_status", 4)->where("status", 1)->whereTime('ctime', 'today')->sum("supply_fee");
        $today_total = $rujinModel->where("pay_status", 4)->where("status", 1)->whereTime('ctime', 'today')->count("id");
        $today_total2 = $rujinModel->where("pay_status", '>=', 2)->where("pay_status", '<', 5)->where("status", 1)->whereTime('ctime', 'today')->count("id");
        $fanyong_taday_total = $Commission->where(['source' => 1, 'chaoshi' => 1, 'status' => 1])->whereTime('ctime', 'today')->sum("money");
        $fanyong_daili_taday_total = $Commission->where(['source' => 1, 'chaoshi' => 1, 'status' => 1])->whereNotIn('id', '168023,168024,168022')->whereTime('ctime', 'today')->sum("money");
        $company_today_price = truncateDecimal($user_today_fee + $supply_today_fee - $fanyong_taday_total);

        $fanyong_taday_total_1d = $Commission->where(['source' => 1, 'chaoshi' => 1, 'status' => 1, 'p_userid' => 168024])->whereTime('ctime', 'today')->sum("money"); //1队
        $fanyong_taday_total_2d = $Commission->where(['source' => 1, 'chaoshi' => 1, 'status' => 1, 'p_userid' => 168023])->whereTime('ctime', 'today')->sum("money"); //2队
        $fanyong_taday_total_spark = $Commission->where(['source' => 1, 'chaoshi' => 1, 'status' => 1, 'p_userid' => 168022])->whereTime('ctime', 'today')->sum("money"); //spark



        $chujinModel = new Chujin();
        $cj_supply_money = $chujinModel->where("pay_status", 5)->sum("withdrawAmount");
        $cj_supply_usdt = $chujinModel->where("pay_status", 5)->sum("supply_usdt");
        $cj_user_usdt = $chujinModel->where("pay_status", 5)->sum("user_usdt");
        $cj_user_fee = $chujinModel->where("pay_status", 5)->sum("user_fee");
        $cj_supply_fee = $chujinModel->where("pay_status", 5)->sum("supply_fee");
        $cj_total = $chujinModel->where("pay_status", 5)->count("id");
        // $cj_company_price =  $cj_user_fee + $cj_supply_fee;
        // $cj_fanyong_total = $Commission->where(['source'=>2,'chaoshi'=>1,'status'=>1])->sum("money");
        $cj_company_price =  $cj_user_fee + $cj_supply_fee;


        $cj_supply_today_money = $chujinModel->where("pay_status", 5)->whereTime('updatetime', 'today')->sum("withdrawAmount");
        $cj_supply_today_money2 = $chujinModel->where("pay_status", "<=", 5)->whereTime('updatetime', 'today')->sum("withdrawAmount");
        $cj_supply_today_price = $chujinModel->where("pay_status", 5)->whereTime('updatetime', 'today')->sum("supply_usdt");
        $cj_user_today_price = $chujinModel->where("pay_status", 5)->whereTime('updatetime', 'today')->sum("user_usdt");
        $cj_user_today_fee = $chujinModel->where("pay_status", 5)->whereTime('updatetime', 'today')->sum("user_fee");
        $cj_supply_today_fee = $chujinModel->where("pay_status", 5)->whereTime('updatetime', 'today')->sum("supply_fee");
        $cj_today_total = $chujinModel->where("pay_status", 5)->whereTime('updatetime', 'today')->count("id");
        $cj_today_total2 = $chujinModel->where("pay_status", '>=', 1)->where("pay_status", '<=', 5)->whereTime('updatetime', 'today')->count("id");
        $cj_taday_fanyong_total = $Commission->where(['source' => 2, 'chaoshi' => 1, 'status' => 1])->whereTime('ctime', 'today')->sum("money");
        $cj_taday_daili_fanyong_total = $Commission->where(['source' => 2, 'chaoshi' => 1, 'status' => 1])->whereNotIn('id', '168023,168024,168022')->whereTime('ctime', 'today')->sum("money");
        $cj_company_today_price =  truncateDecimal($cj_user_today_fee + $cj_supply_today_fee - $cj_taday_fanyong_total);


        $supply = Db::name("supply")->where("id", '>', 2)->column('usdt');
        $supply_freeze_usdt = Db::name("supply")->where("id", '>', 2)->column('freeze_usdt');
        $company = Db::name("company")->where("id", 1)->find();
        $oneteam = Db::name("user")->where("id", 168023)->value('usdt');
        $twoteam = Db::name("user")->where("id", 168024)->value('usdt');
        $spark = Db::name("user")->where("id", 168022)->value('usdt');
        $userTotalUsdt = Db::name("user")->where("usdt", "<>", 0)->sum('usdt');
        $userTotalUsdtdJ = Db::name("user")->where("usdt", "<>", 0)->sum('usdt_dj');

        $my_profit = truncateDecimal($company['usdt'] + $oneteam + $twoteam + $spark + 3039.1797);

        //返佣
        $fanyong_taday_total_1d = $Commission->where(['chaoshi' => 1, 'status' => 1, 'p_userid' => 168024])->whereTime('ctime', 'today')->sum("money"); //1队
        $fanyong_taday_total_2d = $Commission->where(['chaoshi' => 1, 'status' => 1, 'p_userid' => 168023])->whereTime('ctime', 'today')->sum("money"); //2队
        $fanyong_taday_total_spark = $Commission->where(['chaoshi' => 1, 'status' => 1, 'p_userid' => 168022])->whereTime('ctime', 'today')->sum("money"); //spark


        //承兑商充值
        $total_user_number = Db::name("user_usdt")->where("status", 'normal')->sum("num");
        $total_user_cz_number = Db::name("user_usdt")->where("status", 'normal')->count("id");

        //服务商提现
        $total_supply_number = Db::name("supply_usdt")->where("pay_status", 3)->sum("usdt");
        $total_supply_cz_number = Db::name("supply_usdt")->where("pay_status", 3)->count("id");
        $total_supply_cz_fee = Db::name("supply_usdt")->where("pay_status", 3)->sum("fee");
        $totol_supply_usdt = $supply[0] + $supply[1] + $supply[2];
        $total_supply_freeze_usdt = $supply_freeze_usdt[0] + $supply_freeze_usdt[1];

        $company_usdt_all = $company['usdt'] + 3039.1797;

        $data = [
            "today"             => $today,
            'userTotalUsdt'     => $userTotalUsdt,
            'userTotalUsdt'     => $userTotalUsdt,
            'userTotalUsdtdJ'   => $userTotalUsdtdJ,
            'oneteam'           => $oneteam,
            'twoteam'           => $twoteam,
            'spark'             => $spark,
            'company_usdt'      => $company['usdt'],
            'company_usdt_all'  => $company_usdt_all,
            'my_profit'         => $my_profit,
            'fanyong_taday_total_1d'  => $fanyong_taday_total_1d,
            'fanyong_taday_total_2d'  => $fanyong_taday_total_2d,
            'fanyong_taday_total_spark'  => $fanyong_taday_total_spark,
            'supply'            => $supply,
            'totol_supply_usdt' => $totol_supply_usdt,
            'total_supply_freeze_usdt' => $total_supply_freeze_usdt
        ];
        $this->assign($data);

        $rjdata = [
            'total'        => $total,
            'total_money' => $total_money,
            'supply_usdt' => $supply_usdt,
            'user_usdt'   => $user_usdt,
            'user_fee'     => $user_fee,
            'supply_fee'    => $supply_fee,
            'today_total'   => $today_total,
            'today_total2'  => $today_total2,
            'fanyong_taday_total' => $fanyong_taday_total,
            'total_today_money' => $total_today_money,
            'supply_today_price' => $supply_today_price,
            'user_today_price' => $user_today_price,
            'company_today_price'   => $company_today_price,
            'fanyong_daili_taday_total' => $fanyong_daili_taday_total,
            'company_price' => $company_price,
        ];
        $this->assign($rjdata);

        $cjdata = [
            'cj_total'        => $cj_total,
            'cj_supply_money' => $cj_supply_money,
            'cj_supply_usdt' => $cj_supply_usdt,
            'cj_user_usdt'   => $cj_user_usdt,
            'cj_user_fee'     => $cj_user_fee,
            'cj_supply_fee'    => $cj_supply_fee,
            'cj_today_total'   => $cj_today_total,
            'cj_today_total2'  => $cj_today_total2,

            // 'fanyong_taday_total'=>$fanyong_taday_total,
            'cj_supply_today_money' => $cj_supply_today_money,
            'cj_supply_today_price' => $cj_supply_today_price,
            'cj_user_today_price' => $cj_user_today_price,
            'cj_company_today_price'   => $cj_company_today_price,
            'cj_taday_daili_fanyong_total' => $cj_taday_daili_fanyong_total,
            'cj_company_price' => $cj_company_price,
        ];
        $this->assign($cjdata);

        $cztxData = [
            'total_user_number'        => $total_user_number,
            'total_user_cz_number' => $total_user_cz_number,
            'total_supply_number' => $total_supply_number,
            'total_supply_cz_number'   => $total_supply_cz_number,
            'total_supply_cz_fee' => $total_supply_cz_fee,
        ];
        $this->assign($cztxData);

        //误差计算
        // 入金利润 + 出金利润 = 分润
        $all_company_price = $company_price + $cj_company_price;

        $commission_all_2026 = $Commission->where(['chaoshi' => 1, 'status' => 1])->sum("money");
        $commission_all_2025 = 691.5565;
        $commission_all = $commission_all_2025 + $commission_all_2026;

        // 所有分润
        $diff = $total_user_number - $userTotalUsdt - $total_supply_number - $totol_supply_usdt - $all_company_price - $total_supply_freeze_usdt + $commission_all;

        $this->assign('all_company_price', $all_company_price);
        $this->assign("diff", $diff);
        $this->assign("commission_all", $commission_all);


        $params =[
            'rujin_num' => $today_total,
            'rujin_money'=> $total_today_money,
            'rujin_supply_usdt' =>$supply_today_price,
            'rujin_user_usdt'=> $user_today_price,
            'rujin_profit_usdt' => $company_today_price,
            'chujin_num' => $cj_today_total,
            'chujin_money'=> $total_today_money,
            'chujin_supply_usdt'=> $cj_supply_today_price,
            'chujin_user_usdt'  => $cj_user_today_price,
            'chujin_profit_usdt'=>$cj_company_today_price,
            'user_cz_usdt'      =>$total_user_number,
            'supply_tx_usdt'    =>$total_supply_number,
            'company_profit'    => $company_usdt_all,
            'spark_profit'      => $spark,
            'one_profit'      => $oneteam,
            'two_profit'      => $twoteam,
            'user_total_usdt'    => $userTotalUsdt,
            'fanyong'             => $commission_all,
            'supply_account1'      => $supply[0],
            'supply_account2'    => $supply[1],
            'supply_account3'    => $supply[2],
            'supply_account4'    => $supply[3],
            'supply_account5'    => $supply[4],
        ];
        $this->add($params);
        return $this->fetch();
    }



    public function add($params)
    {
        // 验证参数
        if (!is_array($params)) {
            echo "参数必须是数组";
            return;
        }

        // 定义允许的字段列表
        $allowedFields = [
            'rujin_num',
            'rujin_money',
            'rujin_supply_usdt',
            'rujin_user_usdt',
            'rujin_profit_usdt',
            'chujin_num',
            'chujin_money',
            'chujin_supply_usdt',
            'chujin_user_usdt',
            'chujin_profit_usdt',
            'user_cz_usdt',
            'supply_tx_usdt',
            'company_profit',
            'spark_profit',
            'one_profit',
            'two_profit',
            'user_total_usdt',
            'fanyong',
            'supply_account1',
            'supply_account2',
            'supply_account3',
            'supply_account4',
            'supply_account5',
            // 'supply_account6'
        ];

        // 验证参数字段
        foreach ($allowedFields as $field) {
            if (!isset($params[$field])) {
                echo "缺少必要参数: {$field}";
                return;
            }
        }

        $today = date("Y-m-d");

        $info = Db::name("tongji")->where("tjdate", $today)->find();

        // 构建数据数组
        $data = [
            "rujin_num" => (int)$params['rujin_num'],
            "rujin_money" => (float)$params['rujin_money'],
            "rujin_supply_usdt" => (float)$params['rujin_supply_usdt'],
            "rujin_user_usdt" => (float)$params['rujin_user_usdt'],
            "rujin_profit_usdt" => (float)$params['rujin_profit_usdt'],
            "chujin_num" => (int)$params['chujin_num'],
            "chujin_money" => (float)$params['chujin_money'],
            "chujin_supply_usdt" => (float)$params['chujin_supply_usdt'],
            "chujin_user_usdt" => (float)$params['chujin_user_usdt'],
            "chujin_profit_usdt" => (float)$params['chujin_profit_usdt'],
            "user_cz_usdt" => (float)$params['user_cz_usdt'],
            "supply_tx_usdt" => (float)$params['supply_tx_usdt'],
            "company_profit" => (float)$params['company_profit'],
            "spark_profit" => (float)$params['spark_profit'],
            "one_profit" => (float)$params['one_profit'],
            "two_profit" => (float)$params['two_profit'],
            "user_total_usdt" => (float)$params['user_total_usdt'],
            "fanyong" => (float)$params['fanyong'],
            "supply_account1" => (float)$params['supply_account1'],
            "supply_account2" => (float)$params['supply_account2'],
            "supply_account3" => (float)$params['supply_account3'],
            "supply_account4" => (float)$params['supply_account4'],
            "supply_account5" => (float)$params['supply_account5'],
            // "supply_account6" => (float)$params['supply_account6'],
        ];

        if ($info) {
            $data['utime'] = time();
            // 更新现有记录
            $result = Db::name("tongji")->where("id", $info['id'])->update($data);
            if ($result === false) {
                return;
            }
        } else {
            // 插入新记录
            $data['tjdate'] = $today;
            $data['ctime'] = time();
            $result = Db::name("tongji")->insert($data);
            if ($result === false) {
                return;
            }
        }
        return;
    }
}
