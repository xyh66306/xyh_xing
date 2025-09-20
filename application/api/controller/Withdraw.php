<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;


class Withdraw extends Api
{

    protected $noNeedRight = ['index'];

    public function index(){

        $page = input('page',1);
        $list = Db::name('user_usdt')->where("user_id",$this->auth->id)->page($page)->select();

        foreach ($list as $key => $value) {
            $list[$key]['createtime'] = date('Y-m-d H:i:s',$value['createtime']);
        }

        $this->success('',$list);

    }

}
