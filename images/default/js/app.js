/**
 * Created by 84071 on 2017-12-04.
 */
layui.define(['layer', 'form','element','carousel','util'], function(exports){
    var $ = layui.jquery,
        layer = layui.layer,
        layelement = layui.element,
        laycarousel = layui.carousel,
        layutil = layui.util,
        layform = layui.form;

    var see = {
        //  网站导航


        //  获取验证码
        phocode:function (o) {
            var d = $(o).data(),
                phones = $(d.phone).val();

            if(phones == ''){
                layer.msg('手机不得为空');
                return false;
            }
            if (!phones.match(/^(((13[0-9]{1})|(15[0-35-9]{1})|(16[0-35-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/)) {
                layer.msg('手机号码格式不正确');
                return false;
            }
            see.settime();
            $.ajax({
                type:'post',
                dataType: "json",
                data: {phones:phones,type:d.types},
                url:"/home/smscode/smscode.html",
                success:function(data) {
                    layer.msg(data.msg);
                }
            });
        },
        settime:function (val) {
            var serverTime = new Date().getTime(),
                endTime = new Date(serverTime+1000*60).getTime();
            layutil.countdown(endTime, serverTime, function(date, serverTime, timer){
                if(date[3] == '0'){
                    $("#phocode").addClass("bg-blue cx-click");
                    $('#phocode').html('获取验证码');
                }else{
                    $("#phocode").removeClass("bg-blue cx-click");
                    $('#phocode').html('重新发送('+ date[3]+')');
                }
            });
        },
        // 关闭层
        close:function () {
            parent.layer.closeAll();
        },
        //  弹出层
        mlistnav:function (o) {
            var d = $(o).data();
            layer.open({
                type:1,
                skin: 'm-layopen',
                resize:false,
                title: false,
                content:$(d.cid),
            });
        },
    }

    //  轮播
    laycarousel.render({
        elem: '#default-hd',
        width: '100%',
        arrow: 'hover',

        autoplay: false,
    });
    $('body').on('click','.cx-click',function () {
        var a = $(this),
            b = a.data('type');
        see[b] ? see[b].call(this, a) : ""
    });

    exports('app', {});
});