<?php

namespace app\admin\controller\agent;

use app\common\controller\Backend;
use app\common\model\Supply;
use app\admin\model\Admin;
use app\admin\model\agent\Group;

class Invite extends Backend
{

    protected $noNeedRight = ['*'];

    /**
     * 查看
     */
    public function index()
    {
        $adminModel = new Admin();
        $admin_id = $this->auth->id;

        $admininfo = $adminModel->where("id", $admin_id)->find();

        $Group = new Group();
        $groupLst = $Group->where('agent_id',$admin_id)->select();

        $this->assign('groupLst', $groupLst);
        $this->assign('admininfo', $admininfo);
        return $this->view->fetch();
    }

}
