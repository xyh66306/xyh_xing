<?php

namespace app\openapi\controller;

use app\common\controller\Api;
use app\common\model\Supply;
use app\common\model\order\Chujin;
use app\admin\model\supply\Usdtlog;
use app\common\model\Bi as BiModel;
use app\common\model\Task;
use think\Db;
use think\Request;


/**
 * 出金接口
 */
class Sell extends Api
{

    use Send;
    
    protected $noNeedRight = ['index','payOutCallback'];
    protected $noNeedLogin = ['index','payOutCallback'];
    protected $access_key = "";

    public function __construct(Request $request)
    {
        parent::__construct(); // 确保调用父类构造函数

        $this->request = $request;

        // recordLogs("sell_construct",$request);

        $header = $this->request->header();

        // 使用 input() 方法获取请求参数
        if(empty($header['accesskey'])) {
            $this->error('accesskey错误');
        }
        if(empty($header['gmtrequest'])) {
            $this->error('请求时间错误');
        }

        if(empty($header['randomstr'])  && $header['randomstr'] != '32' ) {
            $this->error('获取随机字符串错误');
        }     
        $time = time();
        if($header['gmtrequest']+600<=$time){
            $this->error('时间过期');
        }

        $supplyModel = new Supply();
        $info = $supplyModel->where('access_key',$header['accesskey'])->find();

        $this->access_key = $header['accesskey'];

        if(empty($info)){
            $this->error('商户不存在');
        }

        $params = [
            'accesskey'    => $header['accesskey'],
            'gmtrequest'    => $header['gmtrequest'],
            'randomstr'     => $header['randomstr'],
            'signature'     => $header['signature'],
        ];
        #先鉴权
        $this->Authentication($params, $info['access_secret']);
    }



    /**
     * 创建出金订单
     * @param  string $realName           收款人
     * @param  string $merchantOrderNo    商户订单号
     * @param  string $cardNumber         银行卡号
     * @param  string $bankName           银行名称
     * @param  string $bankBranchName     客户银行分支机构名称
     * @param  string $pay_type           支付类型: wxpay=微信支付, alipay=支付宝, bank=银行卡, qtpay=其他平台
     * @param  string $pay_account        收款账号(微信支付宝必填)
     * @param  string $usdt               提币数量
     * @param  string $webhookUrl         回调地址
     * @return json
     */
    public function index()
    { 
        $params = [
            'realName'          => $this->request->param('realName',''),
            'cardNumber'        => $this->request->param('cardNumber',''), //银行卡号
            'bankName'          => $this->request->param('bankName',''), //银行名称
            'bankBranchName'    => $this->request->param('bankBranchName',''), //客户银行分支机构名称
            'pay_type'          => $this->request->param('pay_type',''),
            'usdt'              => $this->request->param('usdt',''),
            'merchantOrderNo'   => $this->request->param('orderid',''), //商户订单号
            'webhookUrl'        => $this->request->param('webhookUrl',''),  //回调地址
            'pay_account'       => $this->request->param('pay_account',''),
        ];

        if(empty($params['realName'])) {
            $this->error('收款人姓名错误');
        }



        if(empty($params['merchantOrderNo'])) {
            $this->error('商户订单号错误');
        }
        if(empty($params['webhookUrl'])) {
            $this->error('回调地址错误');
        }

        $allowedPayTypes = ['wxpay', 'alipay', 'bank', 'qtpay'];
        if (!in_array($params['pay_type'], $allowedPayTypes)) {
            $this->error('支付类型错误，支持的类型有：wxpay, alipay, bank, qtpay');
        }

        if($params['pay_type'] == 'alipay' || $params['pay_type'] == 'wxpay') {
            if(empty($params['pay_account'])) {
                $this->error('收款账号错误');
            }
        }

        if($params['pay_type'] == 'bank'){
            if(empty($params['cardNumber'])) {
                $this->error('银行名称错误');
            }
            if(empty($params['bankName'])) {
                $this->error('银行卡名称错误');
            }

            //不支持工商和农业 
            if($params['bankName'] == '工商银行' || $params['bankName'] == '中国工商银行' || $params['bankName'] == '农业银行' || $params['bankName'] == '中国农业银行') {
                $this->error('不支持工商银行和农业银行');
            }

            if(empty($params['bankBranchName'])) {
                $this->error('客户银行分支机构名称');
            }
        }

        if(empty($params['usdt']) || $params['usdt'] <= 100) {
            $this->error('提现金额错误');
        }



        $supplyModel = new Supply();
        $supplyinfo = $supplyModel->where('access_key',$this->access_key)->find();
        if(!$supplyinfo) {
            $this->error('商户不存在');
        }
        if($supplyinfo['status'] != "normal") {
            $this->error('商户状态异常');
        }
        if($supplyinfo['usdt']<$params['usdt'] ){
            $this->error('商户余额不足');
        }



        $chujinModel = new Chujin();

        $where['merchantOrderNo'] = $params['merchantOrderNo'];
        $where['status'] = "normal";
        $chujinInfo = $chujinModel->where($where)->find();
        if($chujinInfo){ 
            return $this->error('商户订单号已存在');
        }

        $withdrawAmount = round($params['usdt'] * $supplyinfo['duichu'],2);

        $BiModel = new BiModel();
        $biinfo = $BiModel->where("id", 1)->find();
        $params['user_usdt'] = truncateDecimal($withdrawAmount/$biinfo['duichu'],4);
        $params['user_fee'] = $params['usdt'] - $params['user_usdt'];

        $fee_dalu_supply_duichu = config('site.fee_dalu_supply_duichu');
        $fee_dalu_supply_duichu = $fee_dalu_supply_duichu / 100;
        $params['supply_fee'] = truncateDecimal($params['usdt'] * $fee_dalu_supply_duichu);
        $params['supply_usdt'] = $params['usdt'] + $params['supply_fee'];

        recordLogs("sell",$params);


        Db::startTrans();
        try{ 
            $time = time();
            $orderid = "sl".date("YmdHis").random_int(1000,9999);
            $data = [
                'orderid'           => $orderid,
                'realName'          => $params['realName'],
                'merchantOrderNo'   => $params['merchantOrderNo'],
                'cardNumber'        => $params['cardNumber'] ? $params['cardNumber'] : "",
                'bankName'          => $params['bankName'] ? $params['bankName'] : "",
                'bankBranchName'    => $params['bankBranchName'] ? $params['bankBranchName'] : "",
                'withdrawCurrency'  => "USDT",
                'fiatCurrency'      => "USDT",
                'withdrawAmount'    => $withdrawAmount,
                'requiresReview'    => 2,
                'webhookUrl'        => $params['webhookUrl'],
                'pay_type'          => $params['pay_type'],
                'access_key'        => $this->access_key,
                'createtime'        => $time,
                'updatetime'        => $time,
                'huilv'             =>$supplyinfo['duichu'],
                'diqu'              => 1,
                'pay_status'        => 0,
                'status'            => "normal",
                'usdt'              => $params['usdt'],
                'user_usdt'         => $params['user_usdt'],
                'user_fee'          => $params['user_fee'],
                'supply_fee'        => $params['supply_fee'],
                'supply_usdt'        => $params['supply_usdt'],
                'pay_account'       => $params['pay_account']?$params['pay_account']:"",

            ];
            $chujinModel->insert($data);

            //扣除商户冻结金额
            $Usdtlog = new Usdtlog();
            $Usdtlog->addtxLog($this->access_key,$params['supply_usdt'],2,$params['merchantOrderNo'],2);

            Db::commit();

        } catch(\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }
        return $this->success('success',$orderid);

    }

    /**
     * 
     * 回调
     * @return void
     */
    public function payOutCallback()
    { 

        recordLogs("payOutCallback",$_POST);
    }
}