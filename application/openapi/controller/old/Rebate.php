<?php

namespace app\openapi\controller;

use app\common\controller\Api;
use app\common\model\UserRebate as UserRebateModel;
use app\common\model\User as UserModel;
use app\common\model\Supply;
use think\Request;

class Rebate extends Api
{
    use Send;
    protected $noNeedRight = '*';
    protected $noNeedLogin = "*";
    protected $access_key = "";


    public function __construct(Request $request)
    {
        parent::__construct(); // 确保调用父类构造函数

        $this->request = $request;

        // 使用 input() 方法获取请求参数
        if(empty($this->request->param('access_key'))) {
            $this->error('access_key错误');
        }
        if(empty($this->request->param('randomStr')) || strlen($this->request->param('randomStr')) != 32) {
            $this->error('随机字符串错误');
        }
        if(empty($this->request->param('gmtRequest'))) {
            $this->error('请求时间错误');
        }
        $time = time();
        if($this->request->param('gmtRequest')+600<=$time){
            $this->error('时间过期');
        }

        $supplyModel = new Supply();
        $info = $supplyModel->where('access_key',$this->request->param('access_key'))->find();

        if(empty($info)){
            $this->error('商户不存在');
        }

        if($info['access_secret'] != $this->request->param('access_secret')){
            $this->error('商户密钥错误');
        }
        // $client_ip = $_SERVER['REMOTE_ADDR'];
        // if($info['ip'] && $info['ip'] !="*" && $info['ip'] != $client_ip ){
        //     $this->error('IP错误');
        // }
        $this->access_key = $info['access_key'];
        $params = [
            'access_key'    => $this->request->param('access_key'),
            'randomStr'     => $this->request->param('randomStr'),
            'gmtRequest'    => $this->request->param('gmtRequest'),
            'signature'          => $this->request->param('signature')
        ];
        $access_secret = $this->request->param('access_secret');

        #先鉴权
        $this->Authentication($params, $access_secret);
    }

    /**
     * 获取团队推荐人的佣金设置
     */

     public function getRecomRebate()
     {
        $churu = input("churu",'duichu');
        $email = input("email",'');
        $page = input("page",1);
        $user_id = input("business_id",'');


         $userRebate = new UserRebateModel();
         $userModel = new UserModel();


         $map = [];
         $map['churu'] = $churu;
         if($email){
             $recom_id = $userModel->where("email",$email)->value("id");
             $map['user_id'] = $recom_id;
         }

         $map['pid'] = $user_id;

         $data = $userRebate->where($map)->page($page)->select();

         if(!$data){
             $this->error('暂无数据');
         }
         $count = $userRebate->where($map)->count();         
         foreach ($data as $key => $value) {
            $data[$key]['type'] = $value['type']=='ewm'?__('Ewm'):__('Bank');
            $data[$key]['nickname'] = $userModel->where("id",$value['user_id'])->value("nickname");
         }
         $res = [
             'data'=>$data,
             'count'=>$count
         ];
         $this->success('', $res);
    }


    /**
    * 设置下级佣金
    */    
     public function setting()
     {
        
        $id = input("id",'');    
        $rate = input("rate",'');

        if(!$id || !$rate){
            $this->error('参数错误');
        }
        if($rate>100 || $rate<=0){
            $this->error('佣金比例错误');
        }

        $userRebate = new UserRebateModel();
        $info = $userRebate->where("id",$id)->find();
        if(!$info){
            $this->error('数据不存在');
        }
        $res = $userRebate->where("id",$id)->update(['rate'=>$rate]);
        if($res){
            $this->success('设置成功');
        }else{
            $this->error('设置失败');
        }

     }



}