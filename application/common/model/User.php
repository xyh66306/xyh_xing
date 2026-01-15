<?php

namespace app\common\model;

use think\Db;
use think\Model;

/**
 * 会员模型
 */
class User extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
    ];

    /**
     * 获取个人URL
     * @param string $value
     * @param array  $data
     * @return string
     */
    public function getUrlAttr($value, $data)
    {
        return "/u/" . $data['id'];
    }

    /**
     * 获取头像
     * @param string $value
     * @param array  $data
     * @return string
     */
    public function getAvatarAttr($value, $data)
    {
        if (!$value) {
            //如果不需要启用首字母头像，请使用
            //$value = '/assets/img/avatar.png';
            $value = letter_avatar($data['nickname']);
        }
        return $value;
    }

    /**
     * 获取会员的组别
     */
    public function getGroupAttr($value, $data)
    {
        return UserGroup::get($data['group_id']);
    }

    /**
     * 获取验证字段数组值
     * @param string $value
     * @param array  $data
     * @return  object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array)json_decode($value, true));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);
        return (object)$value;
    }

    /**
     * 设置验证字段
     * @param mixed $value
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;
        return $value;
    }

    /**
     * 变更会员余额
     * @param int    $money   余额
     * @param int    $user_id 会员ID
     * @param string $memo    备注
     */
    public static function money($money, $user_id, $memo)
    {
        Db::startTrans();
        try {
            $user = self::lock(true)->find($user_id);
            if ($user && $money != 0) {
                $before = $user->money;
                //$after = $user->money + $money;
                $after = function_exists('bcadd') ? bcadd($user->money, $money, 2) : $user->money + $money;
                //更新会员信息
                $user->save(['money' => $after]);
                //写入日志
                MoneyLog::create(['user_id' => $user_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

    /**
     * 变更会员积分
     * @param int    $score   积分
     * @param int    $user_id 会员ID
     * @param string $memo    备注
     */
    public static function score($score, $user_id, $memo)
    {
        Db::startTrans();
        try {
            $user = self::lock(true)->find($user_id);
            if ($user && $score != 0) {
                $before = $user->score;
                $after = $user->score + $score;
                $level = self::nextlevel($after);
                //更新会员信息
                $user->save(['score' => $after, 'level' => $level]);
                //写入日志
                ScoreLog::create(['user_id' => $user_id, 'score' => $score, 'before' => $before, 'after' => $after, 'memo' => $memo]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

     /**
     * 变更会员usdt
     * @param int    $money  usdt
     * @param int    $user_id 会员ID
     * @param string $memo    备注
     */
    public static function usdt($usdt, $user_id,$type,$flow_type, $memo='',$beizhu='')
    {
        $bianhao = md5("usdt_dj_{$user_id}_{$usdt}_" . time());
        // 检查是否已存在相同操作
        $existing = Db::name('user_usdt_log')
            ->where('user_id', $user_id)
            ->where('usdt', $usdt)
            ->where('type', $type)
            ->where('createtime', '>', time() - 60) // 1分钟内检查
            ->find();
        
        if ($existing) {
            return false; // 防止重复操作
        }


        Db::startTrans();
        try {
            $user = self::lock(true)->find($user_id);
            if ($user && $usdt != 0) {
                $before = $user->usdt;
                if($flow_type==1){
                    $after = function_exists('bcadd') ? bcadd($user->usdt, $usdt, 4) : $user->usdt + $usdt;
                } else {
                    $after = function_exists('bcsub') ? bcsub($user->usdt, $usdt, 4) : $user->usdt - $usdt;
                }
                //更新会员信息
                $user->save(['usdt' => $after]);                
                //写入日志
                UsdtLog::create(['user_id' => $user_id,'bianhao'=>$bianhao, 'usdt' => $usdt, 'before' => $before, 'after' => $after,'type'=>$type,'flow_type'=>$flow_type, 'memo' => $memo,'beizhu'=>$beizhu]);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {           
            Db::rollback();
            return $e->getMessage();
        }
    }

    public static function usdt_dj($usdt, $user_id,$type,$flow_type, $memo='',$beizhu='')
    {

        $bianhao = md5("usdt_dj_{$user_id}_{$usdt}_" . time());
        // 检查是否已存在相同操作
        $existing = Db::name('user_usdtdj_log')
            ->where('user_id', $user_id)
            ->where('usdt', $usdt)
            ->where('type', $type)
            ->where('createtime', '>', time() - 60) // 1分钟内检查
            ->find();
        
        if ($existing) {
            return false; // 防止重复操作
        }

        Db::startTrans();
        try {
            $user = self::lock(true)->find($user_id);
            if ($user && $usdt != 0) {
                $before = $user->usdt_dj;
                if($flow_type==1){
                    $after = function_exists('bcadd') ? bcadd($user->usdt_dj, $usdt, 4) : $user->usdt_dj + $usdt;
                } else {
                    $after = function_exists('bcsub') ? bcsub($user->usdt_dj, $usdt, 4) : $user->usdt_dj - $usdt;
                }
                //更新会员信息
                $user->save(['usdt_dj' => $after]);

                Db::name('user_usdtdj_log')->insert([
                    'bianhao' => $bianhao,
                    'user_id' => $user_id,
                    'type' => $type,
                    'flow_type' => $flow_type,
                    'usdt' => $usdt,
                    'before' => $before,
                    'after' => $after,
                    'memo' => $memo,
                    'beizhu' => $beizhu,
                    'createtime'=>time()
                ]);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            return false;
            Db::rollback();
        }
    }

    /**
     * 根据积分获取等级
     * @param int $score 积分
     * @return int
     */
    public static function nextlevel($score = 0)
    {
        $lv = array(1 => 0, 2 => 30, 3 => 100, 4 => 500, 5 => 1000, 6 => 2000, 7 => 3000, 8 => 5000, 9 => 8000, 10 => 10000);
        $level = 1;
        foreach ($lv as $key => $value) {
            if ($score >= $value) {
                $level = $key;
            }
        }
        return $level;
    }
}
