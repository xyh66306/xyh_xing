<?php

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
use app\common\model\Commission;
use app\admin\model\supply\Usdtlog;
use think\Queue;
use think\Db;

class Demo extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';


    public function index()
    {


        // $a = 1001;
        // $b = 7.21;

        // echo truncateDecimal($a/$b,4);


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
        // $list = $model->where('id',6)->select();

        // foreach ($list as $k => $row) { 
        //     $companyProfit1 = new companyProfit();
        //     $companyProfit1->addLog($row['usdt'],$row['supply_fee'],2,3,1,$row['orderid']);   
        //     $companyProfit2 = new companyProfit();
        //     $companyProfit2->addLog($row['usdt'],$row['user_fee'],2,1,1,$row['orderid']); 
        // }
        // $model = new Rujin();
        // $list = $model->where('pay_status', 4)->select();

        // foreach ($list as $k => $row) {
        //     $companyProfit1 = new companyProfit();
        //     $companyProfit1->addLog($row['usdt'], $row['supply_fee'], 1, 3, 1, $row['orderid']);
        //     $companyProfit2 = new companyProfit();
        //     $companyProfit2->addLog($row['usdt'], $row['user_fee'], 1, 1, 1, $row['orderid']);
        // }



        // return $this->view->fetch();
    }

    public function test()
    {


        $userModel = new User();
        $userModel->usdt(1378.8993,168025, 8, 2,'50445');


                    // $orderId = 'f932703753';
        // $merchantOrderNo = '1757319239';
        // $user_usdt = '197.7808';

        // $this->commission(168017,$orderId,$merchantOrderNo,$user_usdt);

        

    }


    /***
     * 分佣
     */
    public function commission($user_id,$fy_orderid,$p4b_orderid,$number)
    {

        $Commission = new Commission();
        $userModel  = new User();

        $uinfo = $userModel->where("id", $user_id)->find();
        $invite = $uinfo['invite'];

        $userRebate = new UserRebate();

        $rateInfo = $userRebate->where(['user_id' => $user_id,'pid'=>$invite,'churu'=>'duichu','type'=>'bank'])->find();
        if(!$rateInfo){
            return true;
        }

        $rateLst =  $this->getrate($uinfo);

        $result = [];
        foreach ($rateLst as $key => $value) { 

            $money = truncateDecimal($number * $value['rate'] / 100);
            if($money<=0){
                continue;
            }

            $rebateData = [
                'user_id' =>$user_id,
                'p_userid' => $value['user_id'],
                'fy_orderid' => $fy_orderid,
                'p4b_orderid' => $p4b_orderid,
                'number' => $number,
                'rate'  => $value['rate'],
                'money' => $money,
                'type' => 1,
                'source' => 2,
                'level' => $key+1,
                'status' => 2,
                'chaoshi' => 1,
                'ctime' => time(),
                'utime' => time(),
            ];

            $result[] = $rebateData;
        }

        if(count($result)==0){
            return true;    
        }

        Db::startTrans();
        try {
            $Commission->saveAll($result);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作失败' . $e->getMessage());
        }
        return true;
    }


    public function getrate($uinfo){

        $sparent_str = str_replace("A", "", $uinfo['sparent']);
        $sparent_arr = explode(",", $sparent_str);
        $sparent_arr = array_diff($sparent_arr, [$uinfo['id']]); //删除自身

        $result = [];
        $max = 0;
        $user_id = $uinfo['id'];

        foreach ($sparent_arr as $key => $value) { 
            $res = [];
            $userRebate = new UserRebate();
            $rateInfo = $userRebate->where(['user_id' => $user_id,'pid'=>$value,'churu'=>'duichu','type'=>'bank'])->find();

            $user_id = $value;
            if(!$rateInfo || $rateInfo['rate']<=0){
                continue;
            }
            $res['user_id'] = $value;
            $res['rate'] = $rateInfo['rate'] -$max;
            if($rateInfo['rate']>0){
                $max = $rateInfo['rate'];
            }
            $result[] = $res;
            
        }
        return $result;
    }



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
