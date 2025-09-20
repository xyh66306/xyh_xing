<?php
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-07-31 10:12:39
 */


namespace app\openapi\controller;

use app\common\controller\Api;

use app\common\model\User as UserModel;
use app\common\model\UserRebate;
use app\common\model\Bi;
use app\common\model\Supply;
use think\Db;
use think\Request;
use fast\Random;
use think\Config;
use think\Validate;

/**
 * 兑出接口
 */
class User extends Api
{
    use Send;
    protected $noNeedRight = '*';
     protected $noNeedLogin = '*';
    protected $access_key = "";
    protected $access_secret = '';
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
        $this->access_secret = $access_secret;
        #先鉴权
        $this->Authentication($params, $access_secret);
    }

    public function getUserInfo() {

        $userid = input("business_id",'');
        if(!$userid) {
            $this->error(__('Invalid parameters'));
        }
        $userModel = new UserModel();
        $data = $userModel->where("id",$userid)->find();

        $data['google_secret'] = $this->auth->google_secret ? 1 : 0;
        $data['paypwd']        = $this->auth->paypwd ? true : false;
        $this->success('', $data);
    }


    /**
     * 注册
     */
    public function reg(){
        
        $email = $this->request->post('email');
        $mobile = $this->request->post('mobile');
        $password = "123456";
        $invite = $this->request->post('invite');
        $nickname = $this->request->post('nickname');
        $diqu = $this->request->post('diqu',1);
        
        $username = Random::alpha(12);
        if (!$email || !$mobile  || !$password || !$nickname || !is_numeric($invite)) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }


        $userModel = new UserModel();
        $puserInfo = UserModel::get($invite);
        if (!$puserInfo) {
            $this->error("无效推广码");
        }
        $sparent_id = $puserInfo['sparent'];

        $ret = $this->auth->register($username, $password, $email, $mobile, [
            'nickname' => $nickname,
            'invite' => $invite,
            'group_id'=>1,
            'diqu'=>$diqu,
            'access_key' => $this->access_key,
        ]);
        if ($ret) {
            $data = $this->auth->getUserinfo();
            $sparent_id = "A".$this->auth->id."A".",".$sparent_id;
            $sparent_id = trim($sparent_id,',');
            $userModel->update(['sparent'=>$sparent_id],['id'=>$this->auth->id]);
            $this->addRebate($this->auth->id,$invite);
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 会员登录
     *
     * @ApiMethod (POST)
     * @param string $account  账号
     * @param string $password 密码
     */
    public function login()
    {
        $account = $this->request->post('username');
        $password = $this->request->post('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = $this->auth->getUserinfo();
            if($this->access_key != $data['access_key']){
                $this->error('您没有权限');
            }   
            if($data['status']=="check"){
                $this->error("审核中");
            }
            $data['google_secret'] = $this->auth->google_secret ? 1 : 0;
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * 更新会员信息
     */
    public function updatesfz()
    {
        $sfz_f = $this->request->post('sfz_fimage');
        $sfz_b = $this->request->post('sfz_bimage');
        $sfz_p = $this->request->post('sfz_pimage');

        $user_id = input("business_id",'');


        if (!$sfz_f || !$sfz_b || !$sfz_p || !$user_id) {
            $this->error(__('Invalid parameters'));
        }
        $userModel = new UserModel();
        $userInfo = $userModel->where("id", $user_id)->find();
        if (!$userInfo) {
            $this->error(__('Invalid parameters'));
        }
        if($userInfo['sfz_status']==2){
            $this->error(__('您已提交过认证了'));
        }
        if($userInfo['sfz_status']==1){
            $this->error("账户已认证，请勿重复提交");
        } 
     

        $ret = $userModel->where("id", $user_id)->update([
            "sfz_fimage" => $sfz_f,
            "sfz_bimage" => $sfz_b,
            "sfz_pimage" => $sfz_p,
            "sfz_status" => 2,
        ]);

        if ($ret) {
            $this->success("已上传，待审核");
        } else {
            $this->error($this->auth->getError());
        }
    }




    public function addRebate($uid,$invite){
        

        $userRebate = new UserRebate();
        $BiModel    = new Bi();


        $bi_type = $BiModel->where("status",1)->column("name");

        $time = time();
        $data = [];

        for($i=0;$i<count($bi_type);$i++){

            $info = $userRebate->where("user_id",$uid)->where(['type'=>'bank','bi'=>$bi_type[$i]])->find();
            if($info){
                continue;
            }

            $data[] = [
                'user_id' =>$uid,
                'pid'   => $invite,
                'type'  => 'bank',
                'churu' => 'duiru',
                'bi'    => $bi_type[$i],
                'min_usdt'=>1000,
                'max_usdt'=>700000,
                'rate'=>0,
                'ctime'=>$time,
                'utime'=>$time
            ];
            $data[] = [
                'user_id' =>$uid,
                'pid'   => $invite,
                'type'  => 'bank',
                'churu' => 'duichu',
                'bi'    => $bi_type[$i],
                'min_usdt'=>1000,
                'max_usdt'=>700000,
                'rate'=>0,
                'ctime'=>$time,
                'utime'=>$time
            ];


            $data[] = [
                'user_id' =>$uid,
                'pid'   => $invite,
                'type'  => 'ewm',
                'churu' => 'duiru',
                'bi'    => $bi_type[$i],
                'min_usdt'=>1000,
                'max_usdt'=>700000,
                'rate'=>0,
                'ctime'=>$time,
                'utime'=>$time
            ];
            $data[] = [
                'user_id' =>$uid,
                'pid'   => $invite,
                'type'  => 'ewm',
                'churu' => 'duichu',
                'bi'    => $bi_type[$i],
                'min_usdt'=>1000,
                'max_usdt'=>700000,
                'rate'=>0,
                'ctime'=>$time,
                'utime'=>$time
            ];

        }

        $userRebate->saveAll($data);

    }


}