define(["easy-admin"], function (ea) {

    var Controller = {
        index: function () {

            if (top.location !== self.location) {
                top.location = self.location;
            }

            $('.bind-password').on('click', function () {
                if ($(this).hasClass('icon-5')) {
                    $(this).removeClass('icon-5');
                    $("input[name='password']").attr('type', 'password');
                } else {
                    $(this).addClass('icon-5');
                    $("input[name='password']").attr('type', 'text');
                }
            });

            $('.icon-nocheck').on('click', function () {
                if ($(this).hasClass('icon-check')) {
                    $(this).removeClass('icon-check');
                } else {
                    $(this).addClass('icon-check');
                }
            });
            
            $('.login-tip').on('click', function () {
                $('.icon-nocheck').click();
            });

            ea.listen(function (data) {
                data['keep_login'] = $('.icon-nocheck').hasClass('icon-check') ? 1 : 0;
                return data;
            }, function (res) {
                ea.msg.success(res.msg, function () {
                    window.location = res.url
                })
            }, function (res) {
                ea.msg.error(res.msg, function () {
                    $('#refreshCaptcha').trigger("click");
                });
            });

        },
        google: function () {

            if (top.location !== self.location) {
                top.location = self.location;
            }
            ea.listen(function (data) {
                return data;
            }, function (res) {
            	ea.msg.success(res.msg, function () {
                	 window.location = res.url
                })
            	
            }, function (res) {
                ea.msg.error(res.msg, function () {
                   
                    window.location = "index"
                });
            });

        },
    };
    return Controller;
});
