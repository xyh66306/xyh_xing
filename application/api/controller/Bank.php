<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Bank as BankModel;
use think\Config;

/**
 * 银行接口
 */
class Bank extends Api
{
    protected $noNeedLogin = ['index'];
    protected $noNeedRight = ['*'];

    const BANK_TYPE_DC = 1;  // 储蓄卡
    const BANK_TYPE_CC = 2; // 信用卡

    public function index()
    {
        $card_code = input('card_code', '');
        if (!$card_code) {
            $this->error('参数错误');
        }

        $url = 'https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo=' . $card_code . '&cardBinCheck=true';
        $res = $this->postCurl($url,[],[],"GET");
        $res = json_decode($res, true);

        if (!$res['validated']) {
            $this->error('无法识别的银行卡');
        } elseif($res['bank']=='ICBC'){
             $this->error('暂不支持此类银行卡');
        } else {
            $card = [];
            $BankModel = new BankModel();
            $bankname = $BankModel->where('bank_code', $res['bank'])->value('bank_name');

            $card['name'] = $bankname;
            $card['type'] =$res['cardType'];
        }
        $this->success('成功', $card);
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
}
