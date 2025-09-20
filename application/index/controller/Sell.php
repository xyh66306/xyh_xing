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
        $url = "https://bingocn.wobeis.com/openapi/sell/index";
        $header = [
            'accesskey' => '1250803358',
            'randomstr' => '5a12f50988688f1d1b5951e7e2493c74',
            'gmtrequest' => time(),
        ];
        $access_secret = '4dc96ddbcc1190b66b478e2b98887bad';
        $sign = $this->makeSign($header, $access_secret);
        $header['signature'] = $sign;


        $data['realName'] = '如孜·芽生';
        $data['cardNumber'] = '6216698300007335877';
        $data['bankName'] = '中国银行';
        $data['bankBranchName'] = '中国银行库车市支行营业部';
        $data['withdrawCurrency'] = 'CNY';
        $data['fiatCurrency'] = 'CNY';
        $data['withdrawAmount'] = 1426;
        $data['requiresReview'] = 1;    //是否需要审核 1=是,2=否
        $data['merchantOrderNo'] = time();
        $data['webhookUrl'] = "https://bingocn.wobeis.com/openapi/sell/payOutCallback";

        $res = $this->postCurl($url, $data, $header);

        var_dump($res);
    }


    public function list()
    {

        $url = "https://bingocn.wobeis.com/openapi/sell/list";
        $params = [
            'access_key' => '1250730111',
            'randomStr' => 'cc17c30cd111c7215fc8f51f8790e0e1',
            'gmtRequest' => time(),
        ];
        $access_secret = '5a12f50988688f1d1b5951e7e2493c74';
        $sign = $this->makeSign($params, $access_secret);
        $data = $params;
        $data['access_secret'] = $access_secret;
        $data['signature'] = $sign;

        $data['page'] = 1;
        $data['diqu'] = 1;

        $res = $this->postCurl($url, $data);

        var_dump($res);
    }

    public function detail()
    {

        $url = "https://bingocn.wobeis.com/openapi/sell/detail";
        $params = [
            'access_key' => '1250730111',
            'randomStr' => 'cc17c30cd111c7215fc8f51f8790e0e1',
            'gmtRequest' => time(),
        ];
        $access_secret = '5a12f50988688f1d1b5951e7e2493c74';
        $sign = $this->makeSign($params, $access_secret);
        $data = $params;
        $data['access_secret'] = $access_secret;
        $data['signature'] = $sign;

        $data['orderid'] = 'o202508091357228849';

        $res = $this->postCurl($url, $data);

        var_dump($res);
    }

    /**
     * 购买方
     * 预下单
     */
    public function preorder()
    {

        $url = "https://bingocn.wobeis.com/openapi/sell/preorder";
        $params = [
            'access_key' => '1250730111',
            'randomStr' => 'cc17c30cd111c7215fc8f51f8790e0e1',
            'gmtRequest' => time(),
        ];
        $access_secret = '5a12f50988688f1d1b5951e7e2493c74';
        $sign = $this->makeSign($params, $access_secret);
        $data = $params;
        $data['access_secret'] = $access_secret;
        $data['signature'] = $sign;

        $data['orderid'] = 'o202508120903344567';
        // $data['business_id'] = '95411145';
        // $data['type'] = 'OTC-20';
        // $data['address'] = '9966454sfdsdfsafsadffs';
        // $data['backurl'] = "https://bingocn.wobeis.com/index/index/test";

        $res = $this->postCurl($url, $data);

        var_dump($res);
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
