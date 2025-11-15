<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Task;
use think\Queue;

class Buy extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';


    public function index()
    {
        return $this->view->fetch();
    }

    /**
     * 出售订单
     */
    public function addorder()
    { 
        $url = "https://bingocn.wobeis.com/openapi/buy/addOrder";
        $params = [
            'access_key' => '1250730111',            
            'randomStr' => 'cc17c30cd111c7215fc8f51f8790e0e1',
            'gmtRequest'=> time(),
        ];
        $access_secret = '5a12f50988688f1d1b5951e7e2493c74';
        $sign = $this->makeSign($params,$access_secret);
        $data = $params;
        $data['access_secret'] = $access_secret;
        $data['signature'] = $sign;
        // $data['randomStr'] = 

        $data['business_id'] = '2000520';
        $data['bi_type'] = 'TWD';
        $data['pay_type'] = 'alipay';
        $data['act_num'] = '10000';
        $data['huilv'] = '7.3';
        $data['backurl'] = 'http://www.baidu.com';

        $data['seller_name'] = '李四';
        $data['bank_name'] = '中国工商银行';
        $data['bank_account'] = '123456789';
        $data['bank_zhdz'] = '中国工商银行合肥支行';

        $data['pay_account'] = '96651111';
        $data['pay_ewm_image'] = '/image.png';

        $data['diqu'] =1;

        // var_dump($data);
        $res = $this->postCurl($url,$data);

        var_dump($res);

    }

    public function cash()
    { 
        $url = "http://localhost/openapi/cash/index";

        $randomStr = $this->getRandomStr(32);

        $header = [
            'accesskey' => '1250730111',
            'randomstr' => $randomStr,
            'gmtrequest' => time(),
        ];
        $access_secret = '5a12f50988688f1d1b5951e7e2493c74';
        $sign = $this->makeSign($header, $access_secret);
        $header['signature'] = $sign;



        $params = [
            'access_key' => '1250730111',   
            'randomStr' => $randomStr,         
            'gmtRequest'=> time(),
        ];
        $access_secret = '5a12f50988688f1d1b5951e7e2493c74';
        $sign = $this->makeSign($params,$access_secret);


        $data = $params;
        $data['access_secret'] = $access_secret;
        $data['signature'] = $sign;
        $data['backurl'] = 'https://ceshiotc.wobeis.com/index/index/ceshi';
        $data['orderid'] = "casher".date("YmdHis",time());
        $data['amount'] = '3800';
        $data['diqu'] = 1;
        $data['payername'] = '李四';

        $res = $this->postCurl($url,$data,$header);

        var_dump($res);

    }    



    public function makeSign($params = [], $secret = '')
    {

        if(empty($params) || !is_array($params)) {
             $this->error('签名错误');
        }

        foreach($params as $key => $v) {
            if(empty($v)) {
                unset($params[$key]);
            }
        }
        $ascii_str = $this->ascii($params);
        if($ascii_str == false) {
            $this->error('签名错误');
        }

        $stringSignTemp = $ascii_str."&key=".$secret;
        return strtoupper(MD5($stringSignTemp));
        
    }



    /**
     * 入参参数名ASCII码从小到大排序（字典序）
     *
     * @param array $params
     * @return void
     */
    public function ascii($params = []){
        if(!empty($params) && is_array($params)){
            $p =  ksort($params);
            if($p){
                foreach ($params as $k => $v){
                    if(is_array($v)){
                        $params[$k] = json_encode($v);
                    }
                }
                $strs = urldecode(http_build_query($params));
                $strs = str_replace('\\','',$strs);
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
