<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['index'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }

    public function getRecharge()
    {
        $diqu = input('diqu',1);
        
        if($diqu===2){
            $arr = config('site.recharge_xj');
        } else {
            $arr = config('site.recharge');
        }

        $data = [];
        foreach ($arr as $k => $v) {
            $data[] = [
                'name' => $k,
                'addr' => $v,
                'qr' => cdnurl('/qrcode/build', true) . '?text=' . $v
            ];
        }
        $this->success('', $data);
    }

    public function getInvite()
    {
        $data = [
            'url' => cdnurl('/otc/#/pages/register/register?diqu=' . $this->auth->diqu .'&invite=' . $this->auth->id, true)
        ];
        $data['qr'] = cdnurl('/qrcode/build', true) . '?text=' . urlencode($data['url']);
        $this->success('', $data);
    }

    /**
     * 获取币列表
     */
    public function getBiLst()
    {
        $data = Db::name('bi')->field("name,default,duiru,duichu")->where('status', 1)->select();

        if(!$data){
            $this->error('暂无币种');
        }

        $this->success('', $data);
    }


    public function getBaseInfo()
    {
        $data = [
            'tbpay_switch' => config('site.tbpay_switch')
        ];
        
        $this->success('', $data);
    }

}
