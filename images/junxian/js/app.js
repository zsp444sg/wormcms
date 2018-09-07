layui.define(['layer', 'form','element','carousel','util'], function(exports) {
    var $ = layui.jquery,
        layer = layui.layer,
        layelement = layui.element,
        laycarousel = layui.carousel,
        layutil = layui.util,
        layform = layui.form;

    var see = {
        navlay:function(o){
          var d = $(o).data(),
                w = $(window).width() * 0.8,
                h = $(window).height() * 0.8;
          layer.open({
              type:1,
              title:false,
              area:[w + 'px', h + 'px'],
              content:$(d.cid),
              skin: 'jx-navs'
        });
        },
        //  磁性吸附
        cixi:function () {
            $(window).scroll(function () {
                var wh = $(window).scrollTop(), //  获取滚动条尺寸
                    d = $("#cx-cixi").data(),   //  获取对比元素
                    dh = $(d.cid).offset().top, //  获取对比元素距离顶部尺寸
                    ah = $("#cx-cixi").height(),   //   获取对比元素高度
                    bh = $(d.cid).height(), //  获取浮动元素高度
                    eh = bh - ah, //  获取停止浮动尺寸
                    t = dh - wh,    //  计算上卷高度
                    top = wh - dh + d.top;  //  计算磁吸高度
                if($(window).width() > 760){
                    if(eh > 0){
                        $("#cx-cixi").css('position','absolute');
                        if(t < d.top){
                            $("#cx-cixi").css('top',top + 'px');
                        }
                        if(top > eh){
                            $("#cx-cixi").css('top', eh + 'px');
                        }
                    }
                }
            });
        },
        //  监控导航
        navcode:function () {
            $(".top-nav-li").mouseover(function () {
                var o = $(this).find(".top-nav-a").attr('data-cid');
                $("#"+o).addClass('open');
            });
            $(".top-nav-li").mouseout(function () {
                var o = $(this).find(".top-nav-a").attr('data-cid');
                $("#"+o).removeClass('open');
            });
        },
        //  监控侧栏导航
        listnavcode:function () {
            $(".in-cp-li").mouseover(function () {
                $(this).addClass('open');
                $(this).find('.in-cp-lia').addClass('alick');
                $(this).siblings().removeClass('open');
                $(this).siblings().find('.in-cp-lia').removeClass('alick');
            });
        },
        //  产品第一张图片高度
        chanpinon:function () {
            $(".in-cp-onlist").each(function () {
                var w = $(this).width();
                var h = (w - 10) / 2;
                $(this).height(h);
            });
        },
        //  首页新闻列表页展开状态
        newslist:function () {
            $(".in-news-ul").each(function () {
                var dls = $(this).find('dl:first');
                dls.addClass('open');
                dls.siblings().removeClass('open');
            });
            $(".in-news-ul dl").mouseover(function () {
                $(this).addClass('open');
                $(this).siblings().removeClass('open');
            });
        },
        //  列表页广告位尺寸
        listggpx:function () {
            $(".list-gg").each(function () {
                var h = $(this).height();
                $(this).find(".img-auto").height(h);
            });
        }
    };
    see.cixi();
    see.navcode();
    see.listnavcode();
    see.chanpinon();
    see.newslist();
    see.listggpx();
    //  轮播
    laycarousel.render({
        elem: '#in-hd',
        width: '100%',
        arrow: 'hover',
    });
    laycarousel.render({
        elem: '#in-newshds',
        width: '100%',
        arrow: 'hover',
    });
    laycarousel.render({
        elem: '#in-newshds1',
        width: '100%',
        arrow: 'hover',
    });
    laycarousel.render({
        elem: '#in-newshds2',
        width: '100%',
        arrow: 'hover',
    });
    laycarousel.render({
        elem: '#in-newshds3',
        width: '100%',
        arrow: 'hover',
    });
    //手风琴
    $('.index-sfq-li:first').each(function () {
        $(this).addClass('open');
    });

    $('.index-sfq-li').mouseover(function () {
        $(this).addClass('open');
        $(this).siblings().removeClass('open');

    });





    $('body').on('click','.cx-click',function () {
        var a = $(this),
            b = a.data('type');
        see[b] ? see[b].call(this, a) : ""
    });

    exports('app', {});
})