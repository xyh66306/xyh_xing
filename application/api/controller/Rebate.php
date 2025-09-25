<?php


namespace app\api\controller;
use app\common\controller\Api;

use app\common\model\UserRebate as UserRebateModel;
use app\common\model\User as UserModel;
use fast\Random;
use think\Config;
use think\Validate;


class Rebate extends Api
{

    protected $noNeedRight = '*';

    /**
     * 获取自己的佣金设置
     */
    public function index()
    {
        $userRebate = new UserRebateModel();
        $user_id = $this->auth->id;

        $data = $userRebate->where("user_id",$user_id)->select();

        foreach ($data as $key => $value) {
            $data[$key]['type'] = $value['type']=='ewm'?__('Ewm'):__('Bank');
            $data[$key]['churu'] = $value['churu']=='duichu'?__('Duichu'):__('Duiru');
        }

        $this->success('', $data);

    }

    /**
     * 获取团队推荐人的佣金设置
     */

     public function getRecomRebate()
     {
        $churu = input("churu",'duichu');
        $email = input("email",'');
        $page = input("page",1);


         $userRebate = new UserRebateModel();
         $userModel = new UserModel();

         $user_id = $this->auth->id;


         $map = [];
         $map['churu'] = $churu;
         if($email){
             $recom_id = $userModel->where("email",$email)->value("id");
             $map['user_id'] = $recom_id;
         }

         $map['pid'] = $user_id;

         $data = $userRebate->where($map)->page($page)->order("id desc")->select();

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


    /***
     * 获取团队佣金
     */
    public function getTeamRebate()
    {
       $churu = input("churu",'duichu');
       $email = input("email",'');
       $page = input("page",1);
       $group = input("group",1);


        $userRebate = new UserRebateModel();
        $userModel = new UserModel();

        $user_id = $this->auth->id;

        $sparent = $this->auth->sparent;


        $map = [];
        $map['churu'] = $churu;
        if($email){
            $recom_id = $userModel->where("email",$email)->value("id");
            $map['user_id'] = $recom_id;
        }

        if($sparent && $group==2){
           $tj_ids = $userModel->whereLike("sparent","%".$sparent."%")->where('group_id',3)->where("id","<>",$this->auth->id)->column("id");
           $map['user_id'] = ['in',$tj_ids];
        } else {
            $tj_ids = $userModel->whereLike("sparent","%".$sparent."%")->where("id","<>",$this->auth->id)->column("id");
            $map['user_id'] = ['in',$tj_ids];
        }

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
        $userRebate = new UserRebateModel();
        $info = $userRebate->where("id",$id)->find();
        if(!$info){
            $this->error('数据不存在');
        }

        $group_id = $this->auth->group_id;
        
        if( $group_id != 2 ){
            $father_info = $userRebate->where(['user_id'=>$this->auth->id,'type'=>$info['type'],'churu'=>$info['churu']])->find();
            if($rate > $father_info['rate']){
                $this->error('佣金比例不能大于自己的比例');
            }
        }

     

        $res = $userRebate->where("id",$id)->update(['rate'=>$rate]);

        if($res){
            $this->success('设置成功');
        }else{
            $this->error('设置失败');
        }

    }

}
?>