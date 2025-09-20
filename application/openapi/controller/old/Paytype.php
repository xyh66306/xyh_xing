<?

namespace app\openapi\controller;

use app\common\controller\Api;
use app\common\model\UserPayewm;
use app\common\model\UserBankcard;
use app\common\model\Supply;
use think\Request;

class Paytype extends Api
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
     * 获取用户银行卡信息
     */
    public function getbankinfo(){

        $userid = input("business_id",'');
        if(!$userid){
            $this->error("参数错误");
        }
        $userBankcard = new UserBankcard();
        $info = $userBankcard::where("user_id",$userid)->where("sys_status",'normal')->find();
        if($info){
            $this->success("获取成功",$info);
        }else{
            $this->error("获取失败");
        }

    }

    /**
     * 添加银行卡
     */
    public function addbankcard()
    {
        $id = $this->request->post("id","");
        $name = $this->request->post("name","");
        $type = $this->request->post("type","");
        $bank_name = $this->request->post("bank_name","");
        $bank_nums = $this->request->post("bank_nums","");
        $bank_zhmc = $this->request->post("bank_zhmc","");
        $bank_zhdz = $this->request->post("bank_zhdz","");
        $userid = input("business_id",'');

        if(!$userid){
            $this->error("参数错误");
        }
        if(!$name || !$type || !$bank_name || !$bank_nums || !$bank_zhmc || !$bank_zhdz){
            $this->error("参数错误");
        }

        $userBankcard = new UserBankcard();
        if($id){
            $ret = $userBankcard::update([
                "user_id" => $userid,
                "username"=>$name,
                "type"=>$type,
                "bank_name"=>$bank_name,
                "bank_nums"=>$bank_nums,
                "bank_zhmc"=>$bank_zhmc,
                "bank_zhdz"=>$bank_zhdz,
                "ctime"=>time()
            ],['id'=>$id]);
        } else {
            $ret = $userBankcard::create([
                "user_id" => $userid,
                "username"=>$name,
                "type"=>$type,
                "bank_name"=>$bank_name,
                "bank_nums"=>$bank_nums,
                "bank_zhmc"=>$bank_zhmc,
                "bank_zhdz"=>$bank_zhdz,
                "ctime"=>time()
            ]);
        }

        if($ret){
            $this->success("添加成功");
        }else{
            $this->error("添加失败");
        }
        
    }



    /***
     * 
     *  (用户模块 获取支付二维码)
     */
    public function getpayewm(){

        $userPayewm = new UserPayewm();

        $pay_skpt = $this->request->post("pay_skpt",'');

        $userid = input("business_id",'');

        if(!$userid){
            $this->error("参数错误");
        }

        $info = $userPayewm::where("user_id",$userid)->where("pay_skpt",$pay_skpt)->where("sys_status","normal")->find();
        if($info){
            $this->success("获取成功",$info);
        }else{
            $this->error("获取失败");
        }

    }


    /***
     * 
     *用户模块 添加支付二维码
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

        $userid = input("business_id",'');

        if(!$userid){
            $this->error("参数错误");
        }

        $userPayewm = new UserPayewm();
        $ret = $userPayewm::create([
            "user_id" => $userid,
            "username"=>$name,
            "pay_skpt"=>$pay_skpt,
            "type"=>$type,
            "pay_nums"=>$pay_nums,
            "shuoming"=>$shuoming,
            "beizhu"=>$beizhu,
            "pay_ewm_image"=>$pay_ewm,
            "ctime"=>time()
        ]);

        if($ret){
            $this->success("添加成功");
        }else{
            $this->error("添加失败");
        }
        
    }








    /**
     * 获取用户收款方式
     */
    public function getlst(){ 

        $userid = input("business_id",'');

        if(!$userid){
            $this->error("参数错误");
        }

        
        $userPayewm = new UserPayewm();
        $userBackcard = new UserBankcard();
        
        $ewmlist = $userPayewm->where("user_id",$userid)->where(['sys_status'=>'normal'])->select();
        $backlist = $userBackcard->where("user_id",$userid)->where(['sys_status'=>'normal'])->select();

        $payType = [];

        foreach ($ewmlist as $key => $value) {
            if($value['pay_skpt']=='wxpay'){
                $payType[] = "微信";
            }elseif($value['pay_skpt']=='alipay'){
                $payType[] = "支付宝";
            }
        }

        if($backlist){
            $payType[] = '银行卡';
        }

        $info = [
            'ewm'=>$ewmlist,
            'payType'=>$payType,
            'back'=>$backlist
        ];

        return $this->success('success', $info);
    }


}