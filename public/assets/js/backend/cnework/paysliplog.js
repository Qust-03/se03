define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'jsignature'], function ($, undefined, Backend, Table, Form, jSignature) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cnework/paysliplog/index' + location.search,
                    table: 'cnework_paysliplog',
                }
            });

            var table = $("#table");

            var columns = [
                {field: 'id', title: __('Id'), operate: false},
                {field: 'username', title: __('Username'), operate: 'LIKE'},
                {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
            ];

            $.each(Config.fields, function (index, value) {
                columns.push({
                    field: 'content.' + index,
                    title: value,
                    operate: false
                });
            })

            columns.push({
                    field: 'is_read',
                    title: __('Is_read'),
                    searchList: {"0": __('Is_read 0'), "1": __('Is_read 1')},
                    formatter: Table.api.formatter.normal
                },
                {
                    field: 'is_sign',
                    title: __('Is_sign'),
                    searchList: {"0": __('Is_sign 0'), "1": __('Is_sign 1')},
                    formatter: Table.api.formatter.normal
                },
                {
                    field: 'sign',
                    title: __('Sign'),
                    events: Table.api.events.image,
                    //formatter: Table.api.formatter.image,
                    formatter: function (value, data) {
                        if (value) {
                            return '<a href="javascript:;"><img class="sign-img" style="max-width: 90px; max-height: 60px;" src="' + value + '"></a>';
                        }
                    }
                },
                {
                    field: 'status',
                    title: __('Status'),
                    searchList: {"1": __('Status 1'), "2": __('Status 2'), "0": __('Status 0'),},
                    formatter: Table.api.formatter.status
                },
                {
                    field: 'operate',
                    title: __('Operate'),
                    table: table,
                    events: Table.api.events.operate,
                    formatter: Table.api.formatter.operate,
                    buttons: [{
                        name: 'backedit',
                        text: __('撤回并修改'),
                        icon: 'fa fa-pencil',
                        classname: 'btn btn-info btn-xs btn-dialog',
                        url: 'cnework/paysliplog/backedit',
                        disable: function (data) {
                            if (data.status === 2) {
                                return false;
                            }
                            return true;
                        },
                        success: function () {
                            table.bootstrapTable('refresh');
                        }
                    },{
                        name: 'send',
                        text: __('重发'),
                        icon: 'fa fa fa-paper-plane',
                        classname: 'btn btn-success btn-xs btn-ajax',
                        url: 'cnework/paysliplog/send',
                        disable: function (data) {
                            if (data.status === 0) {
                                return false;
                            }
                            return true;
                        },
                        success: function () {
                            table.bootstrapTable('refresh');
                        }
                    },{
                        name: 'back',
                        text: __('Back'),
                        icon: 'fa fa-mail-reply',
                        classname: 'btn btn-danger btn-xs btn-ajax',
                        url: 'cnework/paysliplog/back',
                        disable: function (data) {
                            if (data.status === 2) {
                                return false;
                            }
                            return true;
                        },
                        success: function () {
                            table.bootstrapTable('refresh');
                        }
                    }],
                });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showExport: false,
                showColumns: false,
                showToggle: false,
                searchFormVisible: false,
                search: false,
                fixedColumns: true,
                fixedRightNumber: 5,
                columns: [
                    columns
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            $(document).on('click', '.sign-img', function () {
                var data = [];
                data.push({
                    src: $(this).attr('src'),
                    thumb: $(this).attr('src')
                });
                Layer.photos({
                    photos: {
                        "start": 0,
                        "data": data
                    },
                    anim: 5
                });
            });

            $(document).on('click', '.btn-back', function () {

                Layer.confirm('确定全部撤回？', function () {
                    Fast.api.ajax({
                        url: 'cnework/paysliplog/backAll',
                        data: {id: Fast.api.query('id')},
                    }, function (data) {
                        table.bootstrapTable('refresh');
                    })
                    layer.closeAll();
                });

            });

        },
        backedit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
