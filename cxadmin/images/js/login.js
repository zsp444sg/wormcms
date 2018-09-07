/**
 * Created by 84071 on 2018-03-23.
 */
layui.define(['layer', 'form'], function(exports){
    var $ = layui.jquery,
        layer = layui.layer,
        layform = layui.form;
    layform.on('submit(login)', function(data) {
        var href = $("form").attr("action");
        $.ajax({
            async:false,
            type: "post",
            dataType: "json",
            data: data.field,
            url: href,
            success: function (data){
                if(data.code == '1'){
                    layer.msg(data.msg,{icon:6,time:100});
                    layer.load();
                    window.location.href = data.url;
                }else{
                    layer.msg(data.msg,{icon:5});
                }
            }
        });
        return false;
    })
    exports('login', {});
});