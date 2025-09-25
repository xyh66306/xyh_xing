<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\order\Rujin as RujinModel;

class Rujin extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';


    public function index()
    {
        $RujinModel = new RujinModel();
        $time = time()-3600*1;
        $list = $RujinModel->where("status",1)->where("pay_status","<=",1)->where('ctime','<',$time)->select();

        foreach ($list as $key => $value) {
            $RujinModel->where("id",$value['id'])->update(["status"=>2]);
        }
        
    }


}
