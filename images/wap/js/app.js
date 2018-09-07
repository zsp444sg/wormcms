/**
 * Created by 84071 on 2017-12-04.
 */
layui.define(['layer','form','element','carousel'], function(exports){
    var $ = layui.jquery,
        layer = layui.layer,
        laycarousel = layui.carousel,
        layform = layui.form;

    var see = {
        //  网站导航
        navsee:function(o){
            var d = $(o).data();
            $(d.cid).toggle();
        },
        //  网站导航
        listnavsee:function(o){
            var d = $(o).data();
            layer.open({
                type:'1',
                title: d.title,
                content: $(d.cid)
            });
        },
        listnavz:function (o) {
            var d = $(o).data();
            $(o).parent().siblings().find('.list-znav').removeClass('alict');
            $(o).parent().siblings().find('i').removeClass('cx-icon-xiangxiajiantou');
            $(o).parent().siblings().find('i').addClass('cx-icon-xiangyoujiantou');
            $(o).parent().siblings().find('a').removeClass('alick');

            $(o).find('i').removeClass('cx-icon-xiangyoujiantou');
            $(o).find('i').addClass('cx-icon-xiangxiajiantou');
            $(o).siblings('ul .list-znav').addClass('alict');
            $(o).addClass('alick');
        },

        // 打开页面
        openarticle:function (o) {
            var d = $(o).data();
            var g = .98 * $(window).width();
            var h = .98 * $(window).height();
            var index = layer.open({
                type: 2,
                title:d.title,
                area:[g + "px", h + "px"],
                closeBtn:1,
                content: d.href
            });
        },
        //  音乐控制
        inplay:function(o){
            var music = document.getElementById("music");
            if(music.paused){
                music.play();
                $(o).parent().addClass('in-play');
                $(o).removeClass('cx-icon-zanting');
                $(o).addClass('cx-icon-yinlemusic214');
            }else{
                music.pause();
                $(o).parent().removeClass('in-play');
                $(o).removeClass('cx-icon-yinlemusic214');
                $(o).addClass('cx-icon-zanting');
            }
        },
        // 关闭层
        close:function () {
            parent.layer.closeAll();
        },
        
    }
    //常规轮播
    laycarousel.render({
        elem: '#inhd',
        width: '100%',
        arrow: 'hover',
        anim:'fade'
    });
    $('body').on('click','.cx-click',function () {
        var a = $(this),
            b = a.data('type');
        see[b] ? see[b].call(this, a) : ""
    });

    exports('app', {});
});