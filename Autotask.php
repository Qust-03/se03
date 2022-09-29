<?php

namespace addons\cnework\controller;

use app\admin\model\cnework\Payslip;
use app\admin\model\cnework\Paysliplog;
use think\Controller;
use think\Validate;

/**
 * 定时任务接口
 *
 * 以Crontab方式每分钟定时执行,且只可以Cli方式运行
 * @internal
 */
class Autotask extends Controller
{

    /**
     * 初始化方法,最前且始终执行
     */
    public function _initialize()
    {
        // 只可以以cli方式执行
        if (!$this->request->isCli()) {
            $this->error('Autotask script only work at client!');
        }

        parent::_initialize();

        // 清除错误
        error_reporting(0);

        // 设置永不超时
        set_time_limit(0);
    }

    /**
     * 定时任务，每分钟执行一次
     *
     * @throws \Exception
     */
    public function index()
    {
        $notice_emails = [];
        $notice_mobiles = [];
        $notice_wechat_data = [];

        $payslips = Payslip::where('status', 1)
            ->where('sendtime', '<=', time())
            ->select();

        foreach ($payslips as $key => $payslip) {
            $notice_types = is_array($payslip->notice_types) ? $payslip->notice_types : explode(',', $payslip->notice_types);

            $paysliplogs = Paysliplog::where('payslip_id', $payslip->id)
                ->where('status', 1)
                ->select();

            foreach ($paysliplogs as $index => $paysliplog) {
                if (in_array(1, $notice_types) && $paysliplog->mobile) {
                    $notice_mobiles[] = $paysliplog->mobile;
                }
                if (in_array(2, $notice_types) && Validate::is($paysliplog->email, "email")) {
                    $notice_emails[] = $paysliplog->email;
                }
                if (in_array(3, $notice_types)) {
                    $notice_wechat_data[] = [
                        'id' => $paysliplog->user_id,
                        '{员工姓名}' => $paysliplog->username,
                        '{计薪月份}' => $payslip->month,
                        '{实发工资}' => $paysliplog->amount,
                        '{通知时间}' => date('Y-m-d', time())
                    ];
                }

                $paysliplog->status = 2;
                $paysliplog->save();
            }

            $payslip->status = 2;
            $payslip->save();
        }

        // 短信
        if ($notice_mobiles) {
            $job = "addons\cnework\job\Notice@sms";
            \think\Queue::push($job, $notice_mobiles, null);
        }

        // email
        if ($notice_emails) {
            foreach ($notice_emails as $key => $email) {
                $data = [];
                $data['email'] = $email;
                $job = "addons\cnework\job\Notice@email";
                \think\Queue::push($job, $data, null);
            }
        }

        // 公众号
        if ($notice_wechat_data) {
            $config_wechat_params = ['{员工姓名}', '{计薪月份}', '{实发工资}', '{通知时间}'];
            $config = get_addon_config('cnework');
            foreach ($notice_wechat_data as $key => $item) {
                $params = [];
                if ($config['wechat_templateparams']) {
                    foreach ($config['wechat_templateparams'] as $k => $v) {
                        foreach ($config_wechat_params as $param) {
                            if (isset($item[$param])) {
                                $v = str_replace($param, $item[$param], $v);
                            }
                        }
                        $params[$k] = $v;
                    }
                }

                $data = [];
                $data['id'] = $item['id'];
                $data['params'] = $params;
                $job = "addons\cnework\job\Notice@wechat";
                \think\Queue::push($job, $data, null);
            }
        }

        echo 'success';
        return;
    }
}
