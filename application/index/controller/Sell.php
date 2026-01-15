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

        $url = "http://localhost/openapi/sell/index";

        $randomStr = $this->getRandomStr(32);

        $access_key     = "1251201271";
        $access_secret = '04e53093edba7b32528af3949483051a';
        


        $header = [
            'accesskey' => $access_key,
            'randomstr' => $randomStr,
            'gmtrequest' => time(),
        ];
        $sign = $this->makeSign($header, $access_secret);
        $header['signature'] = $sign;

        // $data['access_secret'] = $access_secret;
        // $data['signature'] = $sign;
        $data['webhookUrl'] = 'https://bingocn.wobeis.com/index/index/ceshi';
        $data['orderid'] = "casher".date("YmdHis",time());
        $data['usdt'] = '1463.7784';
        $data['diqu'] = 1;
        $data['realName'] = 'lisi';
        $data['cardNumber'] ="6214830391594115";
        $data['bankName'] = '中国工商银行';
        $data['bankBranchName'] = '中山分行营业部';
        $data['pay_type'] = 'bank';

        // var_dump($data);
        $res = $this->postCurl($url,$data,$header);

        var_dump($res);
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



    /**
     * 获得随机字符串
     * @param $len          需要的长度
     * @param $special      是否需要特殊符号
     * @return string       返回随机字符串
     */
    public function getRandomStr($len, $special = false){
        $chars = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];

       if($special){
           $chars = array_merge($chars, ["!", "@", "#", "$", "?", "|", "{", "/", ":", ";", "%", "^", "&", "*", "(", ")", "-", "_", "[", "]", "}", "<", ">", "~", "+", "=", ",", "."]);
       }

       $charsLen = count($chars) - 1;
       shuffle($chars);                            //打乱数组顺序
       $str = '';
       for($i=0; $i < $len; $i++){
           $str .= $chars[mt_rand(0, $charsLen)];    //随机取出一位
       }
       return $str;
    }

}
