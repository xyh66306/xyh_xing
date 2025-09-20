<?php


namespace app\api\controller;
use app\common\controller\Api;

use app\common\model\Commission as CommissionModel;
use app\common\model\User as UserModel;
use fast\Random;
use think\Config;
use think\Validate;


class Commission extends Api
{
    protected $noNeedRight = '*';


    /**
     * 获取佣金列表
     */
    public function index()
    {
        $user_id = $this->auth->id;
        $page = input("page",1);

        $commissionModel = new CommissionModel();
        $userModel       = new UserModel();

        $list = $commissionModel->where("p_userid",$user_id)->page($page)->select();
        foreach ($list as $key => $value) {
            $list[$key]['nickname'] = $userModel->where("id",$value['user_id'])->value("nickname");
            $list[$key]['ctime'] = date("m-d H:i",$value['ctime']);
        }

        if(!$list){
            $this->error('暂无数据');
        }
        $data = [
            'list'=>$list,
            'count'=>$commissionModel->where("user_id",$user_id)->count("id")
        ];
        $this->success('', $data);

    }


    /***
     * 佣金详情
     */
    public function detail()
    {
        $user_id = $this->auth->id;
        $id = input("id",0);
        if(!$id){
            $this->error('参数错误');
        }

        $commissionModel = new CommissionModel();
        $userModel       = new UserModel();
        $info = $commissionModel->where("id",$id)->find();
        if(!$info){
            $this->error('数据不存在');
        }

        // if($info['p_userid'] != $user_id){
        //     $this->error('数据不匹配');
        // }
        $info['nickname'] = $userModel->where("id",$info['p_userid'])->value("nickname");
        $info['ctime'] = date("Y:m-d H:i",$info['ctime']);
        $info['utime'] = date("Y:m-d H:i",$info['utime']);
        $info['status'] = $info['status']==2?"待审核":"已通过";
        $info['chaoshi'] = $info['chaoshi']==2?"是":"否";
        $this->success('', $info);

    }

}    