<?php

namespace app\api\controller;

use app\common\controller\Api;
use fast\Http;
use fast\Random;
use think\Exception;

/**
 * 三方接口
 */
class Third extends Api
{
  protected $noNeedLogin = '*';
  protected $noNeedRight = '*';

  public function test()
  {
    $secret = '0ECPDVPLNJ9OKWDX';
    $data = [
      'appid' => 'appid',
      'type' => 'type',
      'currency' => 'currency',
      'money' => 'money',
      'third_no' => 'third_no',
      'return_url' => 'return_url',
      'callback_url' => 'callback_url',
    ];
    $sign = $this->getSignStr($data, $secret);
    $this->success('', $data);
  }

  public function orderSaleCreate()
  {
    $params = $this->request->post();
    try {
      $data = [
        'pt' => $params['pt'],
        'bh' => $params['bh'],
        'skr' => $params['skr'],
        'skzh' => $params['skzh'],
        'skyh' => $params['skyh'],
        'zhmc' => $params['zhmc'],
        'cny' => $params['cny'],
      ];
      if (db('order_sale')->where('pt', $data['pt'])->where('bh', $data['bh'])->find()) {
        $this->error('编号已存在');
      }
      db('order_sale')->insert($data);
      $this->success('操作成功', $data);
    } catch (Exception $e) {
      $this->error('请求错误');
    }
  }

  public function createOrder()
  {
    $params = $this->request->param();
    $appid = '000000';
    $secret = '0ECPDVPLNJ9OKWDX';
    try {
      $data = [
        'appid' => $params['appid'],
        'type' => $params['type'],
        'currency' => $params['currency'],
        'money' => $params['money'],
        'third_no' => $params['third_no'],
        'return_url' => $params['return_url'],
        'callback_url' => $params['callback_url'],
      ];
      $sign = $this->getSignStr($data, $secret);
      if ($sign != strtoupper($params['sign'])) {
        $this->error('签名错误');
      }
      if (db('order_third')->where('appid', $data['appid'])->where('third_no', $data['third_no'])->find()) {
        $this->error('编号已存在');
      }
      $data['create_time'] = datetime(time());
      $data['order_no'] = date('YmdHis') . Random::numeric(6);
      db('order_third')->insert($data);
      $data['pay_url'] = cdnurl('/kyc/#/pages/payment/payment?order_no=' . $data['order_no']);
      $this->success('操作成功', $data);
    } catch (Exception $e) {
      $this->error('请求错误');
    }
  }

  // appid=appid&callback_url=callback_url&currency=currency&money=money&return_url=return_url&third_no=third_no&type=type&secret=0ECPDVPLNJ9OKWDX
  public function getSignStr($data, $secret)
  {
    ksort($data);
    $str = [];
    foreach ($data as $k => $v) {
      $str[] = $k . '=' . $v;
    }
    $str[] = 'secret=' . $secret;
    $str = implode('&', $str);
    return strtoupper(md5($str));
  }
}
