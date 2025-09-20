<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Task;
use app\common\model\User;
use app\common\model\user\Bankcard;
use app\common\model\user\Payewm;
use app\common\model\order\Rujin;
use app\common\model\order\Chujin;
use app\common\model\Bi as BiModel;
use app\common\model\company\Profit as companyProfit;
use app\admin\model\supply\Usdtlog;
use think\Queue;

class Demo extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';


    public function index()
    {

            // $Usdtlog = new Usdtlog();
            // $usdt = '1987.4474';
            // $orderid = 'f540827457';
            // $Usdtlog->addtxLog('1250803358',$usdt,2,$orderid,2);

        // $model = new Chujin();
        // $list = $model->select();

        // foreach ($list as $k => $row) { 
        //     $Usdtlog = new Usdtlog();
        //     $Usdtlog->addtxLog('1250803358',$row['supply_usdt'],2,$row['orderid'],2);
        // }



        // $rujin = new Rujin();
        // $orderid = 44544;
        // $row = $rujin->where('orderid', $orderid)->find();

        // $model = new Chujin();
        // $list = $model->where('pay_status',5)->select();

        // foreach ($list as $k => $row) { 
        //     $companyProfit1 = new companyProfit();
        //     $companyProfit1->addLog($row['usdt'],$row['supply_fee'],2,3,1,$row['orderid']);   
        //     $companyProfit2 = new companyProfit();
        //     $companyProfit2->addLog($row['usdt'],$row['user_fee'],2,1,1,$row['orderid']); 
        // }
        $model = new Rujin();
        $list = $model->where('pay_status',4)->select();

        foreach ($list as $k => $row) { 
            $companyProfit1 = new companyProfit();
            $companyProfit1->addLog($row['usdt'],$row['supply_fee'],1,3,1,$row['orderid']);   
            $companyProfit2 = new companyProfit();
            $companyProfit2->addLog($row['usdt'],$row['user_fee'],1,1,1,$row['orderid']); 
        }



        // return $this->view->fetch();
    }

    // public function test()
    // {
    //     $Rujin = new Rujin();
    //     $list = $Rujin->where('orderid', 46790)->select();
    //     $BiModel = new BiModel();
    //     $info = $BiModel->where(['default' => 1, 'status' => 1])->find();

    //     foreach ($list as $k => $v) {

    //         $usdt = $v['amount'] / $info['duiru'];
    //         if($v['diqu']==1){
    //             $supply_rate = config('site.fee_dalu_supply_duiru');
    //             $supply_fee = sprintf('%.4f',$usdt * $supply_rate/100);
    //             $supply_usdt = $usdt - $supply_fee;

    //             $user_rate = config('site.fee_dalu_user_duiru');
    //             $user_fee = sprintf('%.4f',$usdt * $user_rate/100);
    //             $user_usdt = $usdt + $user_fee;  //加上用户手续费 审核时候扣除

    //         } elseif($v['diqu']==2){
    //             $supply_rate = config('site.fee_jc_supply_duiru');
    //             $supply_fee = sprintf('%.4f',$usdt * $supply_rate/100);
    //             $supply_usdt = $usdt - $supply_fee;

    //             $user_rate = config('site.fee_jc_user_duiru');
    //             $user_fee = sprintf('%.4f',$usdt * $user_rate/100);
    //             $user_usdt = $usdt + $user_fee;  //加上用户手续费 审核时候扣除

    //         }
    //         $Rujin->update(['supply_fee'=>$supply_fee,'supply_usdt'=>$supply_usdt,'user_fee'=>$user_fee,'user_usdt'=>$user_usdt],['id'=>$v['id']]);
    //     }
    // }



    public function makeSign($params = [], $secret = '')
    {

        if (empty($params) || !is_array($params)) {
            $this->error('签名错误');
        }

        foreach ($params as $key => $v) {
            if (empty($v)) {
                unset($params[$key]);
            }
        }
        $ascii_str = $this->ascii($params);
        if ($ascii_str == false) {
            $this->error('签名错误');
        }

        $stringSignTemp = $ascii_str . "&key=" . $secret;
        return strtoupper(MD5($stringSignTemp));
    }



    /**
     * 入参参数名ASCII码从小到大排序（字典序）
     *
     * @param array $params
     * @return void
     */
    public function ascii($params = [])
    {
        if (!empty($params) && is_array($params)) {
            $p =  ksort($params);
            if ($p) {
                foreach ($params as $k => $v) {
                    if (is_array($v)) {
                        $params[$k] = json_encode($v);
                    }
                }
                $strs = urldecode(http_build_query($params));
                $strs = str_replace('\\', '', $strs);
                return $strs;
            }
        }
        return false;
    }



    /**
     * postCurl 京东的 helper 类拷贝过来的，可以正常使用
     *
     * @param $url
     * @param array $params
     * @param bool $decode
     * @return mixed
     * @throws \Exception
     */
    public static function postCurl($url, $params = [], $method = 'POST')
    {
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'])) {
            return false;
        }

        $opts = [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_URL            => $url,
            CURLOPT_FAILONERROR    => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_HEADER         => false
        ];

        $headers = [];
        array_push($headers, "Content-Type" . ":" . "application/json; charset=UTF-8");
        if ($method == 'POST' && !is_null($params)) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($params);
        }

        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == 'https') {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = false;
        }

        if (!empty($headers) && is_array($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }
        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $data = curl_exec($curl);
        $err  = curl_errno($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($err > 0) {
            throw new \Exception($error);
            return false;
        } else {
            return $data;
        }
    }
}
