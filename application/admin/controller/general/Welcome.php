<?php

namespace app\admin\controller\general;

use app\admin\model\Admin;
use app\common\controller\Backend;
use app\common\model\Supply;
use fast\Random;
use think\Session;
use think\Validate;


class Welcome extends Backend
{

    protected $noNeedRight = ['*'];
    /**
     * 查看
     */
    public function index()
    {
        $supplyModel = new Supply();
        $admin_id = $this->auth->id;
        if ($admin_id < 5) {
            $admin_id = 0;
        }

        $admin_ids_str = "%A" . $admin_id . "A%";
        $supply_info = $supplyModel->whereLike("admin_id", $admin_ids_str)->find();
        $this->view->assign('supply_info', $supply_info);
        return $this->view->fetch();
    }


}
