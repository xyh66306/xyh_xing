<?php
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-07-30 17:08:02
 */

namespace app\openapi\controller;

trait Send
{

    /**
     * 第三方鉴权方法
     *
     * @return void
     */
    public function Authentication($params = [], $secret = '')
    {
        $tpos_sign = empty($params['signature']) ? '' : $params['signature'];
        if(empty($params) || !is_array($params) || empty($tpos_sign)) {
             $this->error('签名错误');
        }

        foreach($params as $key => $v) {
            if(empty($v)) {
                unset($params[$key]);
            }
            unset($params['signature']);
        }
        $ascii_str = $this->ascii($params);
        if($ascii_str == false) {
            $this->error('签名错误');
        }

        $stringSignTemp = $ascii_str."&key=".$secret;
        $sign = strtoupper(MD5($stringSignTemp));
        if($sign !== $tpos_sign) {
            $this->error('签名错误');
        }
    }

    /**
     * 入参参数名ASCII码从小到大排序（字典序）
     *
     * @param array $params
     * @return void
     */
    public function ascii($params = []){
        if(!empty($params) && is_array($params)){
            $p =  ksort($params);
            if($p){
                foreach ($params as $k => $v){
                    if(is_array($v)){
                        $params[$k] = json_encode($v);
                    }
                }
                $strs = urldecode(http_build_query($params));
                $strs = str_replace('\\','',$strs);
                return $strs;
            }
        }
        return false;
    }

}

