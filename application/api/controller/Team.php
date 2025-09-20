<?php


namespace app\api\controller;
use app\common\controller\Api;

use app\common\model\UserTeam;
use fast\Random;


class Team extends Api
{

    protected $noNeedRight = '*';

    public function add(){

        $name = input("name",'');

        if(!$name){
            $this->error('参数错误');
        }

        $user_id = $this->auth->id;

        $userTeam = new UserTeam();
    
        $info = $userTeam->where("user_id",$user_id)->where("name",$name)->find();
        if($info){
            $this->error('团队名称已存在');
        }

        $data['teamid'] =  "TM".date('md') . mt_rand(100, 999);
        $data['name'] = $name;
        $data['user_id'] = $user_id;
        $data['ctime'] = time();

        $res = $userTeam->insert($data);
        if($res){
            $this->success('添加成功', $data);
        }else{
            $this->error('添加失败');
        }   


    }

    /**
     * 获取团队列表信息
     */
    public function getTeam(){

        $userTeam = new UserTeam();
        $list = $userTeam->where("user_id",$this->auth->id)->select();

        if(empty($list)){
            $this->error('暂无数据');
        }
        foreach ($list as $key => $value) {
            $list[$key]['ctime'] = date('m-d H:i:s',$value['ctime']);
        }

        $this->success('', $list);

    }

    public function getTeamInfo(){

        $id = input("id",'');

        if(!$id){
            $this->error('参数错误');
        }
        $userTeam = new UserTeam();
        $info = $userTeam->where("id",$id)->where("user_id",$this->auth->id)->find();
        if(!$info){
            $this->error('数据不存在');
        }
        $this->success('', $info);

    }


    /**
     * 编辑团队
     */
     public function editTeam(){

        $id = input("id",'');
        $name = input("name",'');

        if(!$id || !$name){
            $this->error('参数错误');
        }

        $userTeam = new UserTeam();
        $info = $userTeam->where("id",$id)->find();
        if(!$info){
            $this->error('数据不存在');
        }
        $userTeam->update(['name'=>$name],['id'=>$id]);

     }


    /**
     * 删除团队
     */
    public function delTeam(){

        $id = input("id",'');

        if(!$id){
            $this->error('参数错误');
        }

        $userTeam = new UserTeam();
        $res = $userTeam->where("id",$id)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }


}