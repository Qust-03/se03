<?php

namespace addons\cnework;

use app\common\library\Auth;
use app\common\library\Menu;
use think\Addons;
use think\Request;

/**
 * 工资条插件
 */
class Cnework extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'    => 'cnework',
                'title'   => '工资条管理',
                'icon'    => 'fa fa-list',
                'sublist' => [
                    [
                        'name'    => 'cnework/send',
                        'title'   => '发送工资条',
                        'icon'    => 'fa fa-list',
                        'ismenu'  => 1,
                        'sublist' => [
                            ['name' => 'cnework/send/index', 'title' => '首页'],
                            ['name' => 'cnework/send/add', 'title' => '添加'],
                        ],
                    ],
                    [
                        'name'    => 'cnework/payslip',
                        'title'   => '工资条发送记录',
                        'icon'    => 'fa fa-list',
                        'ismenu'  => 1,
                        'sublist' => [
                            ['name' => 'cnework/payslip/index', 'title' => '首页'],
                            ['name' => 'cnework/payslip/del', 'title' => '删除'],
                            ['name' => 'cnework/paysliplog/index', 'title' => '查看详情'],
                            ['name' => 'cnework/paysliplog/backedit', 'title' => '撤回并修改'],
                            ['name' => 'cnework/paysliplog/send', 'title' => '重发'],
                            ['name' => 'cnework/paysliplog/back', 'title' => '撤回'],
                        ],
                    ],
                    [
                        'name'    => 'cnework/mine',
                        'title'   => '我的工资条',
                        'icon'    => 'fa fa-list',
                        'ismenu'  => 1,
                        'sublist' => [
                            ['name' => 'cnework/mine/index', 'title' => '查看'],
                            ['name' => 'cnework/mine/detail', 'title' => '查看详情'],
                        ],
                    ],
                    [
                        'name'    => 'cnework/configs',
                        'title'   => '配置管理',
                        'icon'    => 'fa fa-list',
                        'ismenu'  => 1,
                        'sublist' => [
                            [
                                'name' => 'cnework/category',
                                'title' => '薪资类别',
                                'ismenu'  => 1,
                                'sublist' => [
                                    ['name' => 'cnework/category/index', 'title' => '首页'],
                                    ['name' => 'cnework/category/add', 'title' => '添加'],
                                    ['name' => 'cnework/category/edit', 'title' => '修改'],
                                    ['name' => 'cnework/category/del', 'title' => '删除'],
                                ],
                            ],
                            [
                                'name' => 'cnework/config',
                                'title' => '配置设置',
                                'ismenu'  => 1,
                                'sublist' => [
                                    ['name' => 'cnework/config/index', 'title' => '首页'],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('cnework');
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable('cnework');
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable('cnework');
        return true;
    }

    /**
     * 会员中心边栏后
     * @return mixed
     * @throws \Exception
     */
    public function userSidenavAfter()
    {
        $request = Request::instance();
        $controllername = strtolower($request->controller());
        $actionname = strtolower($request->action());
        $data = [
            'controllername' => $controllername,
            'actionname'     => $actionname,
        ];

        return $this->fetch('view/hook/user_sidenav_after');
    }

}
