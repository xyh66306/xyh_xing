<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Task;
use think\Queue;

class Sell extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';


    /***
     * 
     * {realName=testName, bankCard.cardNumber=1001, requiresReview=1, fiatCurrency=CNY, withdrawCurrency=CNY, bankCard.bankName=testBank, bankCard.bankBranchName=subName, withdrawAmount=2, merchantOrderNo=1755759619864_1ib, webhookUrl=https://asiacnbo.com/pay/back/XinHuoPay/payOutCallback}
     * 
     */

    public function index()
    {
        $url = "http://100.24.115.218/client/tracnce";

        $pickupUrl = "http://bingocn.wobeis.com/index/sell/paysuccess";
        $signType = "md5";
        $receiveUrl = "http://bingocn.wobeis.com/index/sell/callback";
        $orderNo = '555666';
        $customerId = '393';
        $orderCurrency = "CNY";
        $orderAmount = 100;
        $exchangeRate = 7.13;
        $md5_key = 'sgFTS4BbMg';

        $str = $pickupUrl .$receiveUrl. $signType. $orderNo. $orderAmount.$exchangeRate.$orderCurrency.$customerId.$md5_key;
        $sign = md5( $str);


        $params = [
            'receiveUrl' => $receiveUrl,
            'orderNo' => $orderNo,
            'customerId'=> $customerId,
            'orderCurrency'=> $orderCurrency,
            'orderAmount'  =>$orderAmount,
            'sign' =>$sign,
            'signType'=> $signType,
            'pickupUrl'=> $pickupUrl,
            'exchangeRate'=>$exchangeRate,
        ];

        $urls = http_build_query($params);

        echo $url.'?'.$urls;

        // $res = $this->postCurl($url.'?'.$urls,$params,[],"GET");
        // echo $res;
    }


    public function callback()
    { 
        $data = input('post.'); 

        recordLogs('sell_callback', $data);
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
     * @param array $headers
     * @param string $method
     * @return mixed
     * @throws \Exception
     */
    public static function postCurl($url, $params = [], $headers = [], $method = 'POST')
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

        // 正确处理 headers
        $httpHeaders = [];
        if (!empty($headers) && is_array($headers)) {
            foreach ($headers as $key => $value) {
                $httpHeaders[] = is_int($key) ? $value : $key . ': ' . $value;
            }
        }

        // 添加 Content-Type（如果不存在）
        $hasContentType = false;
        foreach ($httpHeaders as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                $hasContentType = true;
                break;
            }
        }

        if (!$hasContentType) {
            $httpHeaders[] = 'Content-Type: application/json; charset=UTF-8';
        }

        if ($method == 'POST' && !is_null($params)) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($params);
        }

        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == 'https') {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = false;
        }

        if (!empty($httpHeaders)) {
            $opts[CURLOPT_HTTPHEADER] = $httpHeaders;
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
