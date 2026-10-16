<?php

namespace app\openapi\controller;

use app\common\controller\Api;
use app\common\model\Supply;
use app\common\model\OrderCash as OrderCashModel;
use app\common\model\User as UserModel;
use app\common\model\UserBankcard;
use app\common\model\Bi as BiModel;
use app\common\model\UserPayewm;
use app\common\model\UserRebate;
use app\common\model\order\Rujin;
use app\common\model\Task;
use app\common\model\Commission;
use app\common\library\Sms as Smslib;
use app\common\library\Ems as Emslib;
use think\Db;
use think\Cache;
use think\Request;


/**
 * 用户信息接口
 */
class User extends Api
{

    use Send;
    protected $noNeedRight = ['index'];
    protected $noNeedLogin = ['index'];

    protected $access_key = "";
    public function __construct(Request $request)
    {
        parent::__construct(); // 确保调用父类构造函数


        // 检查当前时间是否在禁止访问时间段内
        $currentHour = date('H');
        $currentMinute = date('i');

        $currentTime = $currentHour * 60 + $currentMinute; // 转换为分钟数便于比较
        
        $startTime = 22 * 60 + 30; // 22:30 转换为分钟
        $endTime = 7 * 60 + 30;   // 07:30 转换为分钟
        
        // 如果当前时间在晚上10:30之后且在早上7:30之前，则禁止访问
        if (($currentHour >= 22 && $currentTime >= $startTime) || 
            ($currentHour < 7 || ($currentHour == 7 && $currentMinute <= 30))) {
            $this->error('系统维护时间，晚上10:30到早上7:30暂停服务');
        }        


        $this->request = $request;
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
        $info = $supplyModel->where('access_key',$header['accesskey'])->cache(3600)->find();

        if(empty($info)){
            $this->error('商户不存在');
        }
        $this->access_key = $header['accesskey'];

        $params = [
            'accesskey'    => $header['accesskey'],
            'gmtrequest'    => $header['gmtrequest'],
            'randomstr'     => $header['randomstr'],
            'signature'     => $header['signature'],
        ];
        #先鉴权
        $this->Authentication($params, $info['access_secret']);
    }   
    
    public function index(){
       
        $supplyModel = new Supply();
        $info = $supplyModel->field("title,usdt,freeze_usdt,createtime")->where('access_key',$this->access_key)->cache(3600)->find();        
        $info['createtime'] = date("Y-m-d H:i:s", $info['createtime']);
        return $this->success('success',$info);
    }

}