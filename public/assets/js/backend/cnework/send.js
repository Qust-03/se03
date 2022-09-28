define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'upload', 'template'], function ($, undefined, Backend, Table, Form, Upload, Template) {

    var fields = [];
    var Controller = {
        index: function () {

            require(['bootstrap-datetimepicker'], function () {
                var options = {
                    format: 'YYYY-MM-DD HH:mm:ss',
                    icons: {
                        time: 'fa fa-clock-o',
                        date: 'fa fa-calendar',
                        up: 'fa fa-chevron-up',
                        down: 'fa fa-chevron-down',
                        previous: 'fa fa-chevron-left',
                        next: 'fa fa-chevron-right',
                        today: 'fa fa-history',
                        clear: 'fa fa-trash',
                        close: 'fa fa-remove'
                    },
                    showTodayButton: true,
                    showClose: true
                };
                $('.datetimepicker').parent().css('position', 'relative');
                $('.datetimepicker').datetimepicker(options).on('dp.change', function (e) {
                    $(this, document).trigger("changed");
                });
            });

            // 第一步导入数据，确认信息
            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                if (ret.code == 1) {

                    Controller.api.step(2);

                    $('.month').text(data.month);
                    $('.category').text(data.category);
                    $('.user_count').text(data.user_count);

                    var columns = [
                        {
                            'field': 'username',
                            'title': '姓名',
                            formatter: function (value) {
                                return '<input name="username" type="text" class="username" value="' + value + '">';
                            }
                        }
                    ];

                    var chk_nocice_types = [];
                    $('input[name="notice_types"]:checked').each(function () {
                        chk_nocice_types.push($(this).val());
                    });

                    columns.push({
                        'field': 'mobile',
                        'title': '手机号',
                        formatter: function (value) {
                            return '<input name="mobile" data-rule="required" type="text" class="mobile" value="' + value + '">';
                        }
                    });

                    if (data.email_notice) {
                        columns.push({
                            'field': 'email',
                            'title': '邮箱',
                            formatter: function (value) {
                                return '<input name="email" data-rule="required" type="text" class="email" value="' + value + '">';
                            }
                        });
                    }

                    $('#c-username').html('');
                    $('#c-username').append('<option value="">请选择</option>');
                    $('#c-amount').html('');
                    $('#c-amount').append('<option value="">请选择</option>');
                    $.each(data.fields, function (index, value) {
                        columns.push({
                            'field': index,
                            'title': '<label><input name="chk-fields" data-rule="required" type="checkbox" class="chk-fields" data-index="' + index + '" data-value="' + value + '" checked>' + value + '</label>'
                        });

                        var username_option = '<option value="' + index + '">' + value + '</option>';
                        if (data.name_index == index) {
                            username_option = '<option value="' + index + '" selected>' + value + '</option>';
                        }
                        $('#c-username').append(username_option);
                        var amount_option = '<option value="' + index + '">' + value + '</option>';
                        if (data.amount_index == index) {
                            amount_option = '<option value="' + index + '" selected>' + value + '</option>';
                        }
                        $('#c-amount').append(amount_option);

                    });

                    var table = $("#table");
                    Table.api.init();
                    table.bootstrapTable('destroy');

                    table.bootstrapTable({
                        pk: 'id',
                        sortName: 'weigh',
                        showExport: false,
                        commonSearch: false,
                        search: false,
                        showToggle: false,
                        showColumns: false,
                        pagination: false,
                        escape: false,
                        columns: [
                            columns
                        ],
                        data: data.rows
                    });

                    $('.user-list').html('');
                    $.each(data.rows, function (i, v) {
                        var amount = 0;
                        if (data.amount_index) {
                            amount = v[data.amount_index];
                        }
                        $('.user-list').append(Template('userlisttpl', {user: v, amount: amount, value: JSON.stringify(v)}));
                    });

                    $('#user_count').html(data.rows.length + '人');
                }
                return false;
            });

            // 上一步
            $(document).on('click', '.btn-prev', function () {
                Controller.api.step($(this).data('step'));
            });

            // 第二步确认信息，预览发送
            $(document).on('click', '.btn-next', function () {
                var selected_username_index = $('#c-username').val();
                if (!selected_username_index) {
                    Fast.api.toastr.error('请选择员工姓名列');
                    return false;
                }
                var selected_amount_index = $('#c-amount').val();
                if (!selected_amount_index) {
                    Fast.api.toastr.error('请选择实发金额列');
                    return false;
                }

                var chk_nocice_types = [];
                $('input[name="notice_types[]"]:checked').each(function () {
                    chk_nocice_types.push($(this).val());
                });

                var flag = true, usernames = [], mobiles = [], emails = [];
                $('.username').each(function () {
                    if (!$(this).val()) {
                        Fast.api.toastr.error('姓名不能为空');
                        flag = false;
                        return false;
                    }
                    usernames.push($(this).val());
                });
                $('.mobile').each(function () {
                    if (!$(this).val()) {
                        Fast.api.toastr.error('手机号不能为空');
                        flag = false;
                        return false;
                    }
                    mobiles.push($(this).val());
                });
                if ($.inArray('2', chk_nocice_types) >= 0) {
                    $('.email').each(function () {
                        if (!$(this).val()) {
                            Fast.api.toastr.error('邮箱不能为空');
                            flag = false;
                            return false;
                        }
                        emails.push($(this).val());
                    });
                }

                if (!flag) {
                    return false;
                }

                var chk_fields_item = $('.fixed-table-header').find('.chk-fields');
                fields = [];
                chk_fields_item.each(function () {
                    if ($(this).is(':checked')) {
                        fields.push({index: $(this).data('index'), value: $(this).data('value')})
                    }
                });
                $('.user-list .selected').removeClass('selected');
                $('.user-detail').html('');
                Controller.api.step(3);

                $('.user-list li:first').trigger('click');
            });

            // 选中切换员工
            $(document).on('click', '.user', function () {
                $('.user-list .selected').removeClass('selected');
                if (!$(this).hasClass('selected')) {
                    $(this).addClass('selected');
                    var user = $(this).data('v');
                    $('.user-detail').html(Template('userdetailtpl', {amount: $(this).data('amount'), data: user, fields: fields}))
                }
            });

            // 确认
            $(document).on('click', '.btn-confirm', function () {

                var selected_username_index = $('#c-username').val();
                if (!selected_username_index) {
                    Fast.api.toastr.error('请选择员工姓名列');
                    return false;
                }
                var selected_amount_index = $('#c-amount').val();
                if (!selected_amount_index) {
                    Fast.api.toastr.error('请选择实发金额列');
                    return false;
                }

                var chk_nocice_types = [];
                $('input[name="notice_types[]"]:checked').each(function () {
                    chk_nocice_types.push($(this).val());
                });

                var chk_fields_item = $('.fixed-table-header').find('.chk-fields');
                var chk_fields = [];
                chk_fields_item.each(function () {
                    if ($(this).is(':checked')) {
                        chk_fields.push($(this).data('index'));
                    }
                });

                var flag = true, usernames = [], mobiles = [], emails = [];
                $('.username').each(function () {
                    if (!$(this).val()) {
                        Fast.api.toastr.error('姓名不能为空');
                        flag = false;
                        return false;
                    }
                    usernames.push($(this).val());
                });
                $('.mobile').each(function () {
                    if (!$(this).val()) {
                        Fast.api.toastr.error('手机号不能为空');
                        flag = false;
                        return false;
                    }
                    mobiles.push($(this).val());
                });
                if ($.inArray('2', chk_nocice_types) >= 0) {
                    $('.email').each(function () {
                        if (!$(this).val()) {
                            Fast.api.toastr.error('邮箱不能为空');
                            flag = false;
                            return false;
                        }
                        emails.push($(this).val());
                    });
                }

                if (!flag) {
                    return false;
                }

                Fast.api.ajax({
                    url: 'cnework/send/index',
                    data: {
                        month: $('#c-month').val(),
                        category_id: $('#c-category_id').val(),
                        file: $('#c-file').val(),
                        notice_types: chk_nocice_types,
                        chk_fields: chk_fields.join(','),
                        confirm: 1,
                        usernames: usernames,
                        mobiles: mobiles,
                        emails: emails,
                        username_index: $('#c-username').val(),
                        amount_index: $('#c-amount').val(),
                        sendtime: $('#c-sendtime').val(),
                    }
                }, function (data, ret) {
                    Controller.api.step(4);
                    $('.btn-info').attr('data-id', data.id);
                });
            });

            // 返回
            $(document).on('click', '.btn-back', function () {
                Controller.api.step(1);
                $('#c-file').val('');
            });

            // 查看详情
            $(document).on('click', '.btn-info', function () {
                Fast.api.open('cnework/paysliplog/index?id=' + $(this).attr('data-id'), '查看工资条详情', {
                    area: ['80%', '100%'],
                    offset: 'l'
                });
            });

            // 查看上传示例
            $(document).on('click', '.upload-demo', function () {
                layer.open({
                    type: 1,
                    title: false,
                    closeBtn: 1,
                    area: ['auto'],
                    skin: 'layui-layer-nobg',
                    shadeClose: true,
                    content: $('.img-demo')
                });
            })

            // 上传文件
            Upload.api.upload('.btn-import', function (data, ret) {
                $('#c-file').val(data.url);
            });

            // 类别
            $(document).on('click', '.btn-category', function () {
                Fast.api.open('cnework/category', '薪资类别');
            })

        },
        api: {
            step: function (step) {
                $('.number').removeClass('active');
                $('.number' + step).addClass('active');
                $('.step-row').hide();
                $('.step' + step).show();
            }
        }
    };
    return Controller;
});
