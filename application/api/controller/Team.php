<?php


namespace app\api\controller;
use app\common\controller\Api;

use app\common\model\UserTeam;
use app\common\model\User as UserModel;
use fast\Random;


class Team extends Api
{

    protected $noNeedRight = '*';

    public function add(){

        $name = input("name",'');
        $team_user_id = input("team_user_id",'');

        if(!$name){
            $this->error('参数错误');
        }
        if(!$team_user_id){
            $this->error('参数错误');
        }

        $user_id = $this->auth->id;

        $userTeam = new UserTeam();
        $UserModel = new UserModel();

        $teaminfo = $UserModel->where("id",$team_user_id)->find();
    
        if(!$teaminfo){
            $this->error('成员不存在');
        }
        if($teaminfo['invite'] != $this->auth->id){
            $this->error('不是团队成员');
        }

        $info = $userTeam->where("user_id",$user_id)->where("name",$name)->find();
        if($info){
            $this->error('团队名称已存在');
        }

        $data['teamid'] =  "TM".date('md') . mt_rand(100, 999);
        $data['name'] = $name;
        $data['user_id'] = $user_id;
        $data['team_user_id'] = $team_user_id;
        $data['ctime'] = time();
        $data['status'] = 'normal';

        $res = $userTeam->insert($data);
        if($res){
            $UserModel->update(['group_id'=>3],['id'=>$team_user_id]);
            $this->success('添加成功', $data);
        }else{
            $this->error('添加失败');
        }   


    }
    public function edit(){

        $id = input("id",'');
        $name = input("name",'');
        $team_user_id = input("team_user_id",'');

        if(!$id || !$name ){
            $this->error('参数错误');
        }
        if(!$team_user_id){
            $this->error('参数错误');
        }

        $user_id = $this->auth->id;

        $userTeam = new UserTeam();
        $UserModel = new UserModel();

        $teaminfo = $UserModel->where("id",$team_user_id)->find();
    
        if(!$teaminfo){
            $this->error('成员不存在');
        }

        if($teaminfo['invite'] != $this->auth->id){
            $this->error('不是团队成员');
        }

        $info = $userTeam->where("user_id",$user_id)->where('id','<>',$id)->where("name",$name)->find();
        if($info){
            $this->error('团队名称已存在');
        }

        $data['name'] = $name;
        $data['team_user_id'] = $team_user_id;
        $data['status'] = 'normal';

        $res = $userTeam->update($data,['id'=>$id]);
        if($res){
            $this->success('编辑成功', $data);
        }else{
            $this->error('编辑失败');
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
        $team_user_id = $userTeam->where("id",$id)->value('team_user_id');
        $res = $userTeam->where("id",$id)->delete();
        if($res){
            $UserModel = new UserModel();
            $UserModel->update(['group_id'=>1],['id'=>$team_user_id]);
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }


}