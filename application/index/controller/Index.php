<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Task;
use think\Queue;
use app\common\model\Supply;

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

        $signArr = [];

        $randomStr =  $this->getRandomStr(32);
        $time      = time();

        $signArr['accesskey'] = $data['access_key'];
        $signArr['gmtrequest'] = $time;
        $signArr['randomstr']  = $randomStr;

        //生成签名
        $sign = $this->makeSign($signArr,$data['access_secret']);

        $signArr['sign']  = $sign;



        $taskModel->addTask($data, "Sell");

        $data['orderid'] = $orderid;
        $data['pay_status'] = 3;
        $header = $signArr;
        
        $res = $this->postCurl($url, $data,$header);

    }


    public function test()
    {

        echo "success";
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
        array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
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
