layui.define(['layer', 'form','element','carousel','util'], function(exports){
    var layelement = layui.element;
    var see = {

    };

    $('body').on('click','.cx-click',function () {
        var a = $(this),
            b = a.data('type');
        see[b] ? see[b].call(this, a) : ""
    });
    exports('app', {});
});
