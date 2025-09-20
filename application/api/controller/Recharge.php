<?php
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-09-14 14:14:27
 */

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use app\common\model\user\Usdt;


class Recharge extends Api
{
    protected $noNeedLogin = ['index'];
    protected $noNeedRight = ['*'];

    /**
     * 提现接口
     *
     */
    public function index()
    {
        $UsdtModel = new Usdt();

        $page = input('page', 1);

        $data['list'] = $UsdtModel->where('user_id', $this->auth->id)->order('id', 'desc')->page($page, 10)->select();

        foreach ($data['list'] as $key => $value) {
            $data['list'][$key]['createtime'] = date('Y-m-d H:i:s', $value['createtime']);
        }

        $data['count'] = $UsdtModel->where('user_id', $this->auth->id)->count();
        $this->success('请求成功',$data);
    }

    /**
     * 详情
     */
    public function detail() { 

        $id = input('id', 0);
        $UsdtModel = new Usdt();
        $data = $UsdtModel->where('id', $id)->find();

        $this->success('请求成功',$data);
    }


}
