<?php
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-07-30 16:09:08
 */
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-07-30 16:09:08
 */

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\model\User as UserModel;
use app\common\model\UserRebate;
use app\common\model\Bi;
use fast\Random;
use think\Db;
/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    /**
     * User模型对象
     * @var \app\admin\model\user\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\user\User;
        $this->view->assign("diquList", $this->model->getDiquStatusList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("sfzStatusList", $this->model->getSfzStatusList());
        $this->view->assign("payStatusList", $this->model->getPayStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {

       $diqu = '';
       $group = '';
       $group_ids = $this->auth->getGroupIds($this->auth->id);


       if($group_ids[0]==3 || $group_ids[0]==7){
         $diqu =1;
       } elseif($group_ids[0]==5 || $group_ids[0]==8){
         $diqu =2;
       } elseif($group_ids[0]==6 || $group_ids[0]==9){
         $diqu =3;
       }
       $sfz_show = true;
       if($group_ids[0]>=7){
         $sfz_show = false;
         $group = 1;
       }

        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            if($diqu){
                $map = [];
                $map['diqu'] = $diqu;
                if($group){
                    $map['group_id'] = $group;
                }
                $list = $this->model
                    ->with(['group','invite','agent'])
                    ->where($where)
                    ->where($map)
                    ->order($sort, $order)
                    ->paginate($limit);
            } else {
                $list = $this->model
                    ->with(['group','invite','agent'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);
            }

            foreach ($list as $row) {
                $row->visible(['id','bianhao','username','nickname','mobile','email','usdt','usdt_dj','sfz_fimage','sfz_bimage','sfz_pimage','createtime','status','sfz_status','diqu','pay_status','agent_group_id','invite','agent_group_id']);
                $row->visible(['group']);
				$row->getRelation('group')->visible(['name']);

                $row->visible(['invite']);
				$row->getRelation('invite')->visible(['username']);       
                
                $row->visible(['agent']);
				$row->getRelation('agent')->visible(['username']);                     
                // $row->visible(['admingroup']);
				// $row->getRelation('admingroup')->visible(['title']);                
            }
            $usdt = $this->model->cache(60)->sum("usdt");
            $usdt_dj = $this->model->cache(60)->sum("usdt_dj");
            $result = array("total" => $list->total(), "rows" => $list->items(),'sfz_show'=>$sfz_show,'extend'=>compact('usdt','usdt_dj'));

            return json($result);
        }
        return $this->view->fetch();
    }





        /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            // //是否采用模型验证
            // if ($this->modelValidate) {
            //     $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
            //     $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
            //     $this->model->validateFailException()->validate($validate);
            // }
            // $result = $this->model->allowField(true)->save($params);

            $ip = request()->ip();
            $time = time();
    
            $password = $params['password'];

            $params = array_merge($params, [
                'salt'      => Random::alnum(),
                'jointime'  => $time,
                'joinip'    => $ip,
                'logintime' => $time,
                'loginip'   => $ip,
                'prevtime'  => $time,
            ]);
            $params['password'] = $this->getEncryptPassword($password, $params['salt']);

            $params['avatar'] = $params['avatar']?$params['avatar']:"/assets/img/avatar.png";

            $userModel = new UserModel();
            $user =  $userModel::create($params, true);

            $sparent_id = "A".$user->id."A";
            $userModel->update(['sparent'=>$sparent_id],['id'=>$user->id]);
            $this->addRebate($user->id,$params['invite']);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success();
    }


    public function getEncryptPassword($password, $salt = '')
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;

        $userModel = new UserModel();

        if($params['invite'] != $row['invite']){
            $pinfo = $userModel->where("id",$params['invite'])->find();
            if($pinfo){
                if($row['group_id'] == 2 && $pinfo['group_id'] == 2){
                    $this->error("邀请上级不能是公司类型");
                }
                if($row['group_id'] == 2 && $pinfo['group_id'] == 1){
                    $this->error("邀请上级不能是交易员类型");
                }
                $agent_group_id = $pinfo['agent_group_id'];
                if($pinfo['group_id']==3){
                    $agent_group_id = $params['invite'];
                }

                $sparent_id = "A".$ids."A".",".$pinfo['sparent'];
                $sparent_id = trim($sparent_id,',');
                $userModel->update(['sparent'=>$sparent_id,'invite'=>$params['invite'],'agent_group_id'=>$agent_group_id],['id'=>$ids]);
                $this->addRebate($ids,$params['invite']);
            }else{
                $this->error("邀请码错误");
            }

        }

        Db::startTrans();
        try {

            if(!$row['bianhao']){
                $params['bianhao'] = getOrderNo('user');
            }

            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }


    public function addRebate($uid,$invite){
        

        $userRebate = new UserRebate();
        $BiModel    = new Bi();


        $bi_type = $BiModel->where("status",1)->column("name");

        $time = time();

        $data = [];

        for($i=0;$i<count($bi_type);$i++){

            $info = $userRebate->where("user_id",$uid)->where(['type'=>'bank','bi'=>$bi_type[$i]])->find();
            if($info){
                $userRebate->update(['pid'=>$invite],['id'=>$info['id']]);
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
