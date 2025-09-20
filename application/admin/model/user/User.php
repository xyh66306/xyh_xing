<?php

namespace app\admin\model\user;

use think\Model;


class User extends Model
{

    

    

    // 表名
    protected $name = 'user';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'prevtime_text',
        'logintime_text',
        'jointime_text',
        'status_text',
        'sfz_status_text',
        'dq_status_text'
    ];
    

    protected static function init()
    {
        self::beforeUpdate(function ($row) {
            $changed = $row->getChangedData();
            //如果有修改密码
            if (isset($changed['password'])) {
                if ($changed['password']) {
                    $salt = \fast\Random::alnum();
                    $row->password = \app\common\library\Auth::instance()->getEncryptPassword($changed['password'], $salt);
                    $row->salt = $salt;
                } else {
                    unset($row->password);
                }
            }
        });

    }


    
    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden'), 'check' => __('Status check')];
    }

    public function getSfzStatusList()
    {
        return ['0' => __('Sfz_status 0'), '1' => __('Sfz_status 1'), '2' => __('Sfz_status 2')];
    }

    public function getDiquStatusList()
    {
        return ['1' => __('Dalu'), '2' => __('Xinjiang'), '3' => __('haiwai')];
    }


    public function getPayStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden')];
    }



    public function getPrevtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['prevtime']) ? $data['prevtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getLogintimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['logintime']) ? $data['logintime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['jointime']) ? $data['jointime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getSfzStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['sfz_status']) ? $data['sfz_status'] : '');
        $list = $this->getSfzStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    
    public function getDqStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['diqu']) ? $data['diqu'] : '');
        $list = $this->getDiquStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    

    protected function setPrevtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setLogintimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setJointimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function group()
    {
        return $this->belongsTo('Group', 'group_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function admingroup()
    {
        return $this->belongsTo('app\admin\model\agent\Group', 'agent_group_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
