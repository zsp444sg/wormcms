/**
 * Created by 84071 on 2017-12-04.
 */
layui.define('layer', function(exports){
    var $ = layui.jquery,
        layer = layui.layer;
    var label = {
        editlabel:function (o) {
            var e = $(o).data(),
                g = .8 * $("body").width(),
                k = .9 * $(window).height();
            layer.open({
                title: '编辑标签',
                type: 2,
                area: [g + "px", k + "px"],
                content: e.href + '?title='+ e.title+'&type='+e.typ+'&plate='+e.plate+'&mlabel='+e.mlabel+'&parts='+e.parts,
                cancel: function(index, layero){
                    location.reload()
                }
            })
            return false;
        },
    }
    $('body.cx-bodydbclick').dblclick(function () {
        if(/\?label\=show$/.test(location.href)){
            layer.confirm('你是否要退出标签管理?', {icon: 3, title:'提示'}, function(index){
                window.location.href=location.href.replace('?label=show','');
                layer.close(index);
            })
        }else{
            layer.confirm('你是否要进入标签管理?', {icon: 3, title:'提示'}, function(index){
                thisUrl = location.href;
                if (/\?/.test(thisUrl)){
                    window.location.href=thisUrl+'?label=show';
                }else{
                    window.location.href=thisUrl+'?label=show';
                }
                layer.close(index);
            });

        }
    });
    $('.ipt-txt').focus(function () {
        $(this).parent().addClass('ipt-bor-on');
        $(this).parent().siblings().removeClass('ipt-bor-on');
    });
    $('body').on('click','.cx-label',function () {
        var a = $(this),
            b = a.data('type');
        label[b] ? label[b].call(this, a) : ""
    });

    exports('default', {});
})