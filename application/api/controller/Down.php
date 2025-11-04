<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;


class Down extends Api
{

    protected $noNeedRight = ['index'];
    protected $noNeedLogin = ['index'];

    public function index()
    {

        $id = input('id','');
        if(!$id){
            $this->error('参数错误');
        }

        $info = Db::name("version")->where('id', $id)->find();

        if($info){
            $domain = $_SERVER['HTTP_HOST'];
            $info['image'] = "http://".$domain.$info['image'];
            $info['updatetime'] = date('Y-m-d H:i:s', $info['updatetime']);
            $this->success('请求成功', $info);
        } else {
            $this->error('请求失败');
        }
       
    }

}
