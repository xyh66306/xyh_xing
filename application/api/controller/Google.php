<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

/**
 * 谷歌验证码接口
 */
class Google extends Api
{
    protected $noNeedLogin = '';
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    public function get()
    {
        $ga = new GoogleAuthenticator();
        $secret = $ga->generateSecret();
        $qr = 'otpauth://totp/' . $this->auth->id . '@KYC?secret=' . $secret;
        $this->success('', [
            'secret' => $secret,
            'qr' => cdnurl('/qrcode/build', true) . '?text=' . urlencode($qr)
        ]);
    }

    public function send()
    {
        $secret = $this->request->param('secret', $this->auth->google_secret);
        $ga = new GoogleAuthenticator();
        $code = $ga->getCode($secret);
        $this->success('', [
            'code' => $code,
            'secret' => $secret
        ]);
    }

    public function verify($code, $secret)
    {
        $ga = new GoogleAuthenticator();
        $res = $ga->checkCode($secret, $code);
        return $res;
    }

    public function bind() {
        $code = $this->request->post('code');
        $secret = $this->request->post('secret');
        $google_code = $this->request->post('google_code');
        if(!Ems::check($this->auth->email, $code, 'google')) {
            $this->error('邮箱验证码错误');
        }
        $res = $this->verify($google_code, $secret);
        if(!$res) {
            $this->error('谷歌验证码错误');
        }
        $user = $this->auth->getUser();
        $user->google_secret = $secret;
        $user->save();
        $this->success();
    }

    public function check() {
        $code = $this->request->post('code');
        if(!$this->verify($code, $this->auth->google_secret)) {
            $this->error('谷歌验证码错误');
        }
        $this->success();
    }
}
