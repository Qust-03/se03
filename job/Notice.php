<?php

namespace addons\cnework\job;

use addons\cnework\library\Tencentsms;
use app\common\library\Email;
use think\queue\Job;
use think\Request;

class Notice
{
    /**
     * 发送 Email
     *
     * @param Job $job
     * @param $data
     */
    public function email(Job $job, $data)
    {
        if (empty($data) || $job->attempts() >= 2) {
            $job->delete();
            return;
        }
        $config = get_addon_config('cnework');
        $template = $config['email'];
        $url = url('cnework/mine?ref=addtabs', '', '', true);
        $template = str_replace('{url}', $url, $template);
        $email = new Email;
        $email->to($data['email'])
            ->subject('工资条')
            ->message($template)
            ->send();

        $job->delete();
    }

    /**
     * 批量发送短信
     *
     * @param Job $job
     * @param $data
     */
    public function sms(Job $job, $data)
    {
        if (empty($data) || $job->attempts() >= 2) {
            $job->delete();
            return;
        }

        $config = get_addon_config('cnework');
        $sms = new Tencentsms($config);
        $page = ceil(count($data) / 200);
        $url = url('cnework/mine?ref=addtabs', '', '', true);
        for ($i = 0; $i < $page; $i++) {
            $mobiles = array_slice($data, $i * 200, 200);
            if ($config['sms_param_url']) {
                $sms->send($mobiles, [$url]);
            } else {
                $sms->send($mobiles);
            }
        }

        $job->delete();
    }

    /**
     * 公众号消息
     *
     * @param Job $job
     * @param $data
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function wechat(Job $job, $data)
    {
        if (empty($data) || $job->attempts() >= 2) {
            $job->delete();
            return;
        }

        if (!isset($data['id'])) {
            $job->delete();
            return;
        }

        $wechat = \app\admin\model\cnework\Wechat::where('id', $data['id'])->find();
        if (!$wechat) {
            $job->delete();
            return;
        }
    
        $config = get_addon_config('cnework');
        $app_config = [
            'app_id' => $config['wechat_appid'],
            'secret' => $config['wechat_appsecret'],
            'response_type' => 'array',
        ];
        $app = \EasyWeChat\Factory::officialAccount($app_config);
        $app->template_message->send([
            'touser' => $wechat->openid,
            'template_id' => $config['wechat_templateid'],
            'url' => url('cnework/mine?ref=addtabs', '', '', true),
            'data' => $data['params'],
        ]);

        $job->delete();
    }
}