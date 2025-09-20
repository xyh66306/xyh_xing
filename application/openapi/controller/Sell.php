<?php

namespace app\openapi\controller;

use app\common\controller\Api;
use app\common\model\Supply;
use app\common\model\order\Chujin;
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
     * 出金接口
     */
    public function index()
    { 
        $params = [
            'realName'          => $this->request->param('realName',''),
            'cardNumber'        => $this->request->param('cardNumber',''), //银行卡号
            'bankName'          => $this->request->param('bankName',''), //银行名称
            'bankBranchName'    => $this->request->param('bankBranchName',''), //客户银行分支机构名称
            'withdrawCurrency'  => $this->request->param('withdrawCurrency','CNY'), //提现货币类型（如 MTC/CNY/HKD）
            'fiatCurrency'      => $this->request->param('fiatCurrency','CNY'),    //法币货币代码（如 CNY/HKD）
            'withdrawAmount'    => $this->request->param('withdrawAmount',0),   //提现金额
            'requiresReview'    => $this->request->param('requiresReview',''),   //是否需要审核
            'merchantOrderNo'   => $this->request->param('merchantOrderNo',''), //商户订单号
            'webhookUrl'        => $this->request->param('webhookUrl',''),  //回调地址
        ];
        recordLogs("sell",$params);


        if(empty($params['realName'])) {
            $this->error('真实姓名错误');
        }

        if(empty($params['cardNumber'])) {
            $this->error('银行名称错误');
        }
        if(empty($params['bankName'])) {
            $this->error('银行卡名称错误');
        }
        if(empty($params['bankBranchName'])) {
            $this->error('客户银行分支机构名称');
        }

        if(empty($params['withdrawCurrency'])) {
            $this->error('提现货币类型错误');
        }   
        if(empty($params['fiatCurrency'])) {
            $this->error('法币货币代码错误');
        }
        if(empty($params['withdrawAmount'])) {
            $this->error('提现金额错误');
        }
        if(empty($params['requiresReview'])) {
            $this->error('是否需要审核错误');
        }
        if(empty($params['merchantOrderNo'])) {
            $this->error('商户订单号错误');
        }
        if(empty($params['webhookUrl'])) {
            $this->error('回调地址错误');
        }

        $chujinModel = new Chujin();

        $where['merchantOrderNo'] = $params['merchantOrderNo'];
        $where['status'] = "normal";
        $chujinInfo = $chujinModel->where($where)->find();
        if($chujinInfo){ 
            return $this->error('商户订单号已存在');
        }
        Db::startTrans();
        try{ 
            $time = time();
            $orderid = "sl".date("YmdHis").random_int(1000,9999);
            $data = [
                'orderid'           => $orderid,
                'realName'          => $params['realName'],
                'merchantOrderNo'   => $params['merchantOrderNo'],
                'cardNumber'        => $params['cardNumber'],
                'bankName'          => $params['bankName'],
                'bankBranchName'    => $params['bankBranchName'],
                'withdrawCurrency'  => $params['withdrawCurrency'],
                'fiatCurrency'      => $params['fiatCurrency'],
                'withdrawAmount'    => $params['withdrawAmount'],
                'requiresReview'    => $params['requiresReview'],
                'webhookUrl'        => $params['webhookUrl'],
                'pay_type'          => "bank",
                'access_key'        => $this->access_key,
                'createtime'        => $time,
                'updatetime'        => $time,
                'status'            => "normal",
            ];
            $chujinModel->insert($data);
            Db::commit();

        } catch(\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }
        return $this->success('成功',$orderid);

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