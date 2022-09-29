<?php

namespace addons\cnework\controller;

use app\admin\model\cnework\Wechat;
use EasyWeChat\Factory;
use fast\Random;
use think\Db;
use think\Session;

/**
 * 微信接口
 */
class Index extends \think\addons\Controller
{
    // 授权
    public function oauth()
    {
        $config = get_addon_config('cnework');
        $app_config = [
            'app_id' => $config['wechat_appid'],
            'secret' => $config['wechat_appsecret'],
            'response_type' => 'array',
            'oauth' => [
                'scopes' => ['snsapi_base'],
                'callback' => '/addons/cnework/index/oauth_callback',
            ],
        ];
        $app = Factory::officialAccount($app_config);

        $referer = $this->request->get('referer');
        if ($referer) {
            session('referer', $referer);
        }
        $app->oauth->redirect()->send();
    }

    // 回调
    public function oauth_callback()
    {
        $config = get_addon_config('cnework');
        $app_config = [
            'app_id' => $config['wechat_appid'],
            'secret' => $config['wechat_appsecret'],
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($app_config);
        $user = $app->oauth->user()->toArray();

        // 是否已绑定微信
        $wechat = Wechat::where('openid', $user['id'])->find();
        if ($wechat) {
            $admin = \app\admin\model\Admin::where('id', $wechat->id)->find();
            if ($admin) {
                $referer = session('referer');
                if ($referer) {
                    $admin->token = Random::uuid();
                    $admin->save();
                    Session::set("admin", $admin->toArray());
                    $this->redirect($referer);
                } else {
                    session('cnework', ['id' => $admin->id, 'nickname' => $admin->nickname]);
                    $this->view->assign('admin', $admin);
                    return $this->view->fetch('bind');
                }
            }
        }

        $this->view->assign('user', $user);
        return $this->view->fetch();
    }

    // 绑定帐号
    public function bind()
    {
        $this->token();

        $openid = $this->request->post('openid', '');
        $username = $this->request->post('username', '');
        $password = $this->request->post('password', '');
        if (!$openid) {
            $this->error('参数错误', '');
        }

        $admin = \app\admin\model\Admin::where('username', $username)->find();
        if (!$admin) {
            $this->error('用户名不存在', '');
        }
        if ($admin['status'] == 'hidden') {
            $this->error('帐号已经被禁止登录', '');
        }
        if (\think\Config::get('fastadmin.login_failure_retry') && $admin->loginfailure >= 10 && time() - $admin->updatetime < 86400) {
            $this->error('请于1天后再尝试登录', '');
        }
        if ($admin->password != md5(md5($password) . $admin->salt)) {
            $this->error('密码错误', '');
        }

        // 是否已绑定微信
        $wechat = Wechat::where('id', $admin->id)->find();
        // 已绑定过是否要覆盖
//        if ($wechat && $wechat->cnework_wechat_openid != $cnework_wechat_openid) {
//            $this->error('该账户已绑定');
//        }

        Wechat::create([
            'id' => $admin->id,
            'openid' => $openid
        ]);

        session('cnework', ['id' => $admin->id, 'nickname' => $admin->nickname]);
        $this->view->assign('admin', $admin);
        return $this->view->fetch();
    }

    // 解绑
    public function unbind()
    {
        $this->token();
        $id = $this->request->post('id');

        $admin = session('cnework');
        if (!$admin || $admin['id'] != $id) {
            $this->error('解绑失败');
        }

        Wechat::where('id', $admin['id'])->delete();
        $this->success('解绑成功', addon_url('cnework/index/oauth'));
    }

}
