<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\model\User as UserModel;
use app\common\model\UserPayewm;
use app\common\model\UsdtLog;
use app\common\model\UserBankcard;
use app\common\model\UserRebate;
use app\common\model\Supply;
use app\common\model\user\Withdraw;
use app\common\model\Bi;
use app\admin\model\user\Usdt as UsdtModel;
use fast\Random;
use think\Config;
use think\Validate;
use think\Db;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    public function getUserInfo() {
        $data = $this->auth->getUserinfo();
        $data['google_secret'] = $this->auth->google_secret ? 1 : 0;
        $data['paypwd']        = $this->auth->paypwd ? true : false;
        $data['money']        = $this->auth->usdt - $this->auth->usdt_dj;
        $this->success('', $data);
    }


    public function getsfzinfo() {
        
        $UserModel = new UserModel();
        $user = $UserModel->where('id',$this->auth->id)->find();

        if(!$user['sfz_fimage'] || !$user['sfz_bimage'] || !$user['sfz_pimage']){
            $this->error('暂未认证');
        }

        $domain = $this->request->domain();

        $data['sfz_fimage'] = $user->sfz_fimage ? $domain.$user->sfz_fimage : '';
        $data['sfz_bimage'] = $user->sfz_bimage ? $domain.$user->sfz_bimage : '';
        $data['sfz_pimage'] = $user->sfz_pimage ? $domain.$user->sfz_pimage :'';
        $this->success('', $data);
    
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
     * 手机验证码登录
     *
     * @ApiMethod (POST)
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @ApiMethod (POST)
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email    邮箱
     * @param string $mobile   手机号
     * @param string $code     验证码
     */
    public function register()
    {
        $email = $this->request->post('email');
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        $password = $this->request->post('password');
        $invite = $this->request->post('invite');
        $nickname = $this->request->post('nickname');
        $diqu = $this->request->post('diqu',1);
        
        // $sfz_f = $this->request->post('sfz_f');
        // $sfz_b = $this->request->post('sfz_b');
        // $sfz_p = $this->request->post('sfz_p');
        $username = Random::alpha(12);
        if (!$email || !$mobile || !$code || !$password || !$nickname || !is_numeric($invite)) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Ems::check($email, $code, 'register');
        if (!$ret) {
            // $this->error(__('Captcha is incorrect'));
            $this->error("验证码错误");
        }

        $supplyModel = new Supply();
        $supplyInfo = $supplyModel->where('id',1)->find();

        $userModel = new UserModel();

        if($userModel->where("email",$email)->find()){
            $this->error("邮箱已存在");
        }

        $puserInfo = UserModel::get($invite);
        if (!$puserInfo) {
            $this->error("无效推广码");
        }
        $sparent_id = $puserInfo['sparent'];

        $agent_group_id = $puserInfo['agent_group_id'];
        if($puserInfo['group_id'] == 3){
            $agent_group_id = $puserInfo['id'];
        }

        $ret = $this->auth->register($username, $password, $email, $mobile, [
            'nickname' => $nickname,
            'invite' => $invite,
            'group_id'=>1,
            'diqu'=>$diqu,
            'access_key' => $supplyInfo['access_key'],
            'agent_group_id'=>$agent_group_id
            // 'sfz_f' => $sfz_f,
            // 'sfz_b' => $sfz_b,
            // 'sfz_p' => $sfz_p,
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
     * 更新会员身份证信息
     */
    public function updatesfz()
    {
        $sfz_f = $this->request->post('sfz_f');
        $sfz_b = $this->request->post('sfz_b');
        $sfz_p = $this->request->post('sfz_p');
        if (!$sfz_f || !$sfz_b || !$sfz_p ) {
            $this->error(__('Invalid parameters'));
        }
        if(!isImageUrl($sfz_f)){
             $this->error("不支持的图片格式");
        }
        if(!isImageUrl($sfz_b)){
             $this->error("不支持的图片格式");
        }
        if(!isImageUrl($sfz_p)){
             $this->error("不支持的图片格式");
        }                

        $ret = $this->auth->update_sfz($this->auth->id,$sfz_f, $sfz_b,$sfz_p);
        if ($ret) {
            $this->success("已上传，待审核");
        } else {
            $this->error($this->auth->getError());
        }
    }

    public function updateInfo()
    {
        $nickname = $this->request->post('nickname');
        $username = $this->request->post('username');
        $mobile = $this->request->post('mobile');
        $email = $this->request->post('email');

        $ret = $this->auth->update_info($this->auth->id,$nickname, $username,$mobile,$email);
        if ($ret) {
            $this->success("已更新");
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 退出登录
     * @ApiMethod (POST)
     */
    public function logout()
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @ApiMethod (POST)
     * @param string $avatar   头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio      个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     *
     * @ApiMethod (POST)
     * @param string $email   邮箱
     * @param string $captcha 验证码
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->post('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @ApiMethod (POST)
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @ApiMethod (POST)
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->post("platform");
        $code = $this->request->post("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @ApiMethod (POST)
     * @param string $mobile      手机号
     * @param string $newpassword 新密码
     * @param string $captcha     验证码
     */
    public function resetpwd()
    {
        $type = $this->request->post("type", "mobile");
        $mobile = $this->request->post("mobile");
        $email = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $newpassword], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /***
     * 更新支付密码
     */
    public function updatepaypwd()
    {
        $paypwd = $this->request->post("paypwd",'');
        $email = $this->request->post("email");
        $captcha = $this->request->post("captcha");
        if (!$paypwd || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        $ret = Ems::check($email, $captcha, 'resetpwd');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->changepaypwd($paypwd);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     *支付密码验证
     */
    public function checkpaypwd()
    {
        $paypwd = $this->request->post("paypwd",'');
        $ret = $this->auth->checkpaypwd($paypwd);
        if ($ret) {
            $this->success(__('Check successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * *
     * @ApiSummary  (用户模块 获取支付二维码)
     * @return void
     */
    public function getpayLst(){

        $userPayewm = new UserPayewm();

        $info = $userPayewm::where("user_id",$this->auth->id)->select();
        if($info){
            $this->success("获取成功",$info);
        }else{
            $this->error("获取失败");
        }

    }

    /***
     * 
     * @ApiSummary  (用户模块 获取支付二维码)
     */
    public function getpayewm(){

        $userPayewm = new UserPayewm();

        $pay_skpt = $this->request->post("pay_skpt",'wxpay');

        $info = $userPayewm::where("user_id",$this->auth->id)->where("pay_skpt",$pay_skpt)->find();
        if($info){
            $this->success("获取成功",$info);
        }else{
            $this->error("获取失败");
        }

    }


    /***
     * 
     * @ApiSummary  (用户模块 添加支付二维码)
     */
    public function addpayewm()
    {
        $name = $this->request->post("name","");
        $pay_skpt = $this->request->post("pay_skpt","");
        $type = $this->request->post("type","CNY");
        $pay_nums = $this->request->post("pay_nums","");
        $shuoming = $this->request->post("shuoming","");
        $beizhu = $this->request->post("beizhu","");
        $pay_ewm = $this->request->post("pay_ewm","");
        $status = $this->request->post("status","normal");
        $id = $this->request->post("id","");

        $userPayewm = new UserPayewm();
        
        if($id){
            $ret = $userPayewm->update([
                    "username"=>$name,
                    "type"=>$type,
                    "pay_nums"=>$pay_nums,
                    "shuoming"=>$shuoming,
                    "beizhu"=>$beizhu,
                    "pay_ewm_image"=>$pay_ewm,
                    'status'=>$status,
                    'sys_status'=>'normal',
                ],["id"=>$id]);

        } else {
            $ret = $userPayewm::create([
                "user_id" => $this->auth->id,
                "username"=>$name,
                "pay_skpt"=>$pay_skpt==1?'wxpay':'alipay',
                "type"=>$type,
                "pay_nums"=>$pay_nums,
                "shuoming"=>$shuoming,
                "beizhu"=>$beizhu,
                "pay_ewm_image"=>$pay_ewm,
                'status'=>$status,
                'sys_status'=>'normal',
                "ctime"=>time()
            ]);
        }        

        $this->updatePayStatus($this->auth->id,$name);
        if($ret){
            $this->success("添加成功");
        }else{
            $this->error("添加失败");
        }
        
    }


    /**
     * 
     * 银行卡列表
     * @return void
     */
    public function getbankcardLst(){

        $userBankcard = new UserBankcard();

        $info = $userBankcard::where("user_id",$this->auth->id)->select();

        if($info){
            $this->success("获取成功",$info);
        }else{
            $this->error("获取失败");
        }

    }
    
    /***
     * 
     * @ApiSummary  (用户模块 获取支付二维码)
     */
    public function getbankcard(){

        $userBankcard = new UserBankcard();
        $id = $this->request->post("id",'');
        $info = $userBankcard::where("user_id",$this->auth->id)->where("id",$id)->find();
        if($info){
            $this->success("获取成功",$info);
        }else{
            $this->error("获取失败");
        }

    }


    /***
     * 
     * @ApiSummary  (用户模块 添加支付二维码)
     */
    public function addbankcard()
    {
        $name = $this->request->post("name","");
        $type = $this->request->post("type","");
        $bank_name = $this->request->post("bank_name","");
        $bank_nums = $this->request->post("bank_nums","");
        $bank_zhmc = $this->request->post("bank_zhmc","");
        $bank_zhdz = $this->request->post("bank_zhdz","");
        $status = $this->request->post("status","normal");
        $min_cny = $this->request->post("min_cny","0");
        $max_cny = $this->request->post("max_cny","10000");

        $id = $this->request->post("id","");

        $userBankcard = new UserBankcard();
        if($id){
            $ret = $userBankcard->update([
                "username"=>$name,
                "type"=>$type,
                "bank_name"=>$bank_name,
                "bank_nums"=>$bank_nums,
                "bank_zhmc"=>$bank_zhmc,
                "bank_zhdz"=>$bank_zhdz,
                'status'=>$status,
                'sys_status'=>'normal',
                'min_cny'   =>$min_cny,
                'max_cny'   =>$max_cny,
            ],["id"=>$id]);
        } else {
            $ret = $userBankcard::create([
                "user_id" => $this->auth->id,
                'bianhao'=>getOrderNo('bank'),
                "username"=>$name,
                "type"=>$type,
                "bank_name"=>$bank_name,
                "bank_nums"=>$bank_nums,
                "bank_zhmc"=>$bank_zhmc,
                "bank_zhdz"=>$bank_zhdz,
                'min_cny'   =>$min_cny,
                'max_cny'   =>$max_cny,                
                'status'=>'normal',
                'sys_status'=>'normal',
                "ctime"=>time()
            ]);
        }

        $this->updatePayStatus($this->auth->id,$name);
        if($ret){
  
            $this->success("添加成功");
        }else{
            $this->error("添加失败");
        }
        
    }


    /**
     * 
     * 删除银行卡
     * @return void
     */
    public function delbankcard(){

        $id = $this->request->post("id",'');

        $userBankcard = new UserBankcard();
        $info = $userBankcard->where("id",$id)->find();
        if(!$info){
            $this->error("数据不存在");
        }
        if($this->auth->id != $info['user_id']){
            $this->error("数据不存在");
        }
        $ret = $userBankcard->where("id",$id)->delete();
        if($ret){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }


    public function updatePayStatus($user_id,$name){

        $userBankcard = new UserBankcard();
        $bankcount = $userBankcard::where("user_id",$user_id)->where(['status'=>'normal','sys_status'=>'normal'])->count();

        $userPayewm = new UserPayewm();
        $paycount = $userPayewm::where("user_id",$user_id)->where(['status'=>'normal','sys_status'=>'normal'])->count();

        $min_cny = $userBankcard::where("user_id",$user_id)->where(['status'=>'normal','sys_status'=>'normal'])->min('min_cny');
        $max_cny = $userBankcard::where("user_id",$user_id)->where(['status'=>'normal','sys_status'=>'normal'])->max('max_cny');

        $userModel = new UserModel();

        if($bankcount > 0 || $paycount > 0){
            $userModel->update([
                "pay_status"=>'normal',
                'min_cny'=>$min_cny,
                'max_cny'=>$max_cny,
                'username'=>$name,
            ],["id"=>$user_id]);
        } else {
            $userModel = new UserModel();
            $userModel->update([
                "pay_status"=>'hidden',
                'min_cny'=>$min_cny,
                'max_cny'=>$max_cny,     
                'username'=>$name,           
            ],["id"=>$user_id]);
        }  

    }

    /**
     * 
     * czusdt资金列表
     * @return void
     */
    public function czusdt(){ 

        $UsdtModel = new UsdtModel();

        $post = $this->request->post();

        $info = $UsdtModel->where("user_id",$this->auth->id)->where("status","hidden")->find();
        if($info){
            $this->error("存在待审核的订单");
        }

        
        $post['user_id'] = $this->auth->id;
        $post['status'] = 'hidden';

        $rate = config('site.fee_chongzhi');    
        if($rate && $rate>0){
            $post['fee'] = sprintf("%.4f",$post['num']*$rate/100);
        }else{
            $post['fee'] = 0;
        }
        $post['act_num'] = $post['num']-$post['fee'];
        $post['bianhao'] = getOrderNo('withdraw');
        $res = $UsdtModel->allowField(true)->save($post);
        if($res){
            $this->success("添加成功");
        }else{
            $this->error("添加失败");
        }

    }


    /**
     * usdt资金转账
     */
    public function usdttransfer()
    {
        $usdt = $this->request->post("num",'');
        $email = $this->request->post("email",'');
        $paypwd = $this->request->post("paypwd",'');
        $remark = $this->request->post("remark",'');
        if (!$usdt || !$email || !$paypwd) {
            $this->error(__('Invalid parameters'));
        }

        // $ret = $this->auth->checkpaypwd($paypwd);
        // if (!$ret) {
        //     $this->error($this->auth->getError());
        // }
        $userModel = new UserModel();
        $fuserInfo = $userModel->where("email",$email)->find();
        if(!$fuserInfo){
            $this->error("对象不存在");
        }
        if($fuserInfo->id == $this->auth->id){
            $this->error("不能转给自己");
        }
        if($usdt > $this->auth->usdt){
            $this->error("余额不足");
        }
        //转账扣除
        $ret = $userModel->usdt($usdt,$this->auth->id,1,2,$remark,'转出:'.$fuserInfo->email);
        //转账对象增加
        $ret = $userModel->usdt($usdt,$fuserInfo->id,1,1,$remark,'转入:'.$this->auth->email);

        if($ret){
            $this->success("转账成功");
        }else{
            $this->error("转账失败");
        }

    }

    /**
     * 获取usdt记录
     */
    public function getusdtlog(){

        $page = $this->request->post("page",1);
        $type = $this->request->post("type",'');
        $flow_type = $this->request->post("flow_type",'');

        $usdtLogModel = new UsdtLog();

        $where = [
            "user_id" => $this->auth->id,
        ];
        if($type){
            $where["type"] = $type;
        }

        if($flow_type){
            $where["flow_type"] = $flow_type;
        }

        $list = $usdtLogModel->where($where)->page($page)->order("id desc")->select();
        $data['count'] =  $usdtLogModel->where($where)->count("id");

        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date("Y-m-d H:i:s",$v['createtime']);
        }   

        $data['list'] = $list;
        $this->success("获取成功",$data);
    }

    /**
     * 用户提币
     * @return void
     */
    public function withdraw(){

        $pay_type = input('pay_type','');
        $usdt = $this->request->post("usdt",'');
        $remarks = $this->request->post("remarks",'');

        $fee = config('site.fee_ti');
        $act_usdt = $usdt-$fee;

        $userModel = new UserModel();
        $info = $userModel->where("id",$this->auth->id)->find();
        if(($info['usdt'] - $info['usdt_dj']) < $usdt){
            $this->error("余额不足");
        }


        $withdrawModel = new Withdraw();
        $params = [
            "user_id"=>$this->auth->id,
            "usdt"=>$usdt,
            "fee"=>$fee,
            "act_usdt"=>$act_usdt,
            "pay_type"=>$pay_type,
            "remarks"=>$remarks,
            "status"=>'hidden',
        ];
        $res = $withdrawModel->allowField(true)->save($params);

        if($res){

            
            //减去用户余额
            $userModel->usdt($usdt,$this->auth->id,3,2,$remarks,'提现:'.$usdt);
            //增加用户冻结余额
            $userModel->usdt_dj($usdt,$this->auth->id,3,1,$remarks,'冻结:'.$usdt);

            $this->success("添加成功");
        }else{
            $this->error("添加失败");
        }

    }



    /***
     * 获取团队列表
     */
    public function getTeamLst(){

        $page = $this->request->post("page",1);

        $userModel = new UserModel();
        $user_id = $this->auth->id;
        $sparent = $this->auth->sparent;


        $list = $userModel->field("id,nickname,email,pay_status,invite,status,pay_status,agent_group_id")->where('group_id',1)->where("sparent","like","%".$sparent."%")->where("id","<>",$user_id)->order('id desc')->page($page)->select();

        if(!$list){
            $this->error("暂无数据");
        }
        foreach ($list as $key => $value) {
            $status_txt = '';
            if($value['status'] == 'hidden'){ 
                $status_txt = '账户待审核';
            }elseif($value['pay_status'] == 'hidden'){
                $status_txt = '身份证待审核';
            }else{
                $status_txt = '启用';
            }
            $list[$key]['status_txt'] = $status_txt;
            $list[$key]['teamname'] = Db::name("user_team")->where("team_user_id",$value['agent_group_id'])->value("name");
        }
        $data['count'] = $userModel->where("sparent","like","%".$sparent."%")->where('group_id',1)->where("id","<>",$user_id)->count("id");
        $data['list'] = $list;
        $this->success("获取成功",$data);

    }



    public function addRebate($uid,$invite){
        

        $userRebate = new UserRebate();
        $BiModel    = new Bi();


        $bi_type = $BiModel->where("status",1)->column("name");

        $time = time();

            // $bi_type = ['CNY','TWD'];
        // $bi_type = config("bi_type");

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
