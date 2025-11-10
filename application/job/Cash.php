<?php
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-08-01 17:22:40
 */
namespace app\job;

use think\queue\Job;
use app\common\model\Task;
use app\common\model\order\Rujin;
use app\common\model\User as UserModel;
use app\common\library\Sms as Smslib;
use app\common\library\Ems as Emslib;
use think\Log;

class Cash
{

    public function fire(Job $job, $params)
    {
        $ietaskModle = new Task();
        
        try {
            // 检查参数格式是否正确
            if (!isset($params['params'])) {
                $job->delete();
                return;
            }
            
            $data['orderid'] = $params['params']['orderid'];
            $data['pay_status'] = $params['params']['pay_status'];

            $header = $params['header'];

            recordLogs("Cash_data",json_encode($data));
            recordLogs("Cash_data",json_encode($header));
            
            $res = $this->postCurl($params['params']['url'], $data,$header);
            
            if ($res == 'success') {

                $bData['state'] = "2";
                $bData['updatetime'] = time();
                $ietaskModle->update($bData, ['id' => $params['task_id']]);
                $job->delete(); // 成功后删除任务
            } else {
                // 增加重试次数检查
                if ($job->attempts() >= 3) {
                    $bData['state'] = "3";
                    $bData['updatetime'] = time();
                    $bData['message'] = "visited url is not exists!";
                    $ietaskModle->update($bData, ['id' => $params['task_id']]);
                    $job->delete(); // 失败超过3次后删除任务
                }
                // 未达到最大重试次数，任务会自动重新入队
            }
        } catch (\Exception $e) {
            // 异常处理
            if ($job->attempts() >= 3) {
                $bData['state'] = "3";
                $bData['updatetime'] = time();
                $bData['message'] = $e->getMessage();
                $ietaskModle->update($bData, ['id' => $params['task_id'] ?? 0]);
                $job->delete();
            }
        }
    }

    public function failed($data)
    {
        // ...任务达到最大重试次数后，失败了
    }


    public static function postCurl($url, $params = [], $headers = [], $method = 'POST')
    {
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'])) {
            return false;
        }

        $opts = [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_URL            => $url,
            CURLOPT_FAILONERROR    => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_HEADER         => false
        ];

        // 正确处理 headers
        $httpHeaders = [];
        if (!empty($headers) && is_array($headers)) {
            foreach ($headers as $key => $value) {
                $httpHeaders[] = is_int($key) ? $value : $key . ': ' . $value;
            }
        }

        // 添加 Content-Type（如果不存在）
        $hasContentType = false;
        foreach ($httpHeaders as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                $hasContentType = true;
                break;
            }
        }

        if (!$hasContentType) {
            $httpHeaders[] = 'Content-Type: application/json; charset=UTF-8';
        }

        if ($method == 'POST' && !is_null($params)) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($params);
        }

        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == 'https') {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = false;
        }

        if (!empty($httpHeaders)) {
            $opts[CURLOPT_HTTPHEADER] = $httpHeaders;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $data = curl_exec($curl);
        $err  = curl_errno($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($err > 0) {
            throw new \Exception($error);
            return false;
        } else {
            return $data;
        }
    }

}
