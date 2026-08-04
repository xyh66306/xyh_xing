<?php
/*
 * @Author: Xyhao
 * @Date: 2025-10-10 09:14:35
 * @Description: 安徽爱喜网络科技有限公司
 */

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Task;
use app\common\model\User;
use app\common\model\user\Bankcard;
use app\common\model\user\Payewm;
use app\common\model\UserRebate;
use app\common\model\order\Rujin;
use app\common\model\Bank;
use app\common\model\order\Chujin;
use app\common\model\Bi as BiModel;
use app\common\model\company\Profit as companyProfit;
use app\admin\model\supply\Usdtlog as SpullyUsdtLog;
use app\common\model\Commission;
// use app\admin\model\supply\Usdtlog;
use app\admin\model\user\usdt\Log as UsdtLogModel;
use app\admin\model\user\Usdt as UsdtModel;
use app\common\model\Supply;
use app\common\library\Sms as Smslib;
use app\common\library\Ems as Emslib;
use think\Queue;
use think\Db;
use think\Request;

class Callback extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';


    protected $access_key = "";
    protected $secret = "";
    protected $supplyInfo = [];


    public function index()
    {

        $order_id = "PD20260715122604000008";
        $order_info = Db::name("order_rujin")->where("orderid",$order_id)->find();

        $pintai_id = "1320622959";
        $supplyModel = new Supply();
        $info = $supplyModel->where('access_key', $pintai_id)->find();

        $taskModel = new Task();
        $data = [
            'access_key'    => $info['access_key'],
            'access_secret' => $info['access_secret'],
            'name' => 'cash',
            'message' => '',
            'params' => [
                'orderid' => $order_id,
                'url'  => $order_info['callback'],
                'pay_status' => 3
            ]
        ];
        $taskModel->addTask($data, "Cash");    

    }

}
