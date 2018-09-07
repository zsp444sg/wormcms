
//导航
$(document).ready(function() {
    $('.tb').click(function(){
        if($(this).siblings('ul').css('display')=='none'){
            $(this).addClass('tbs');
            $(this).siblings('ul').slideDown(100).children('li');
        }else{
            $(this).removeClass('tbs');
            $(this).siblings('ul').slideUp(100);
        }
    })
});

//新闻轮播
layui.use('carousel', function(){
    var carousel = layui.carousel;
    //建造实例
    carousel.render({
        elem: '#xwlb'
        ,width: '100%' //设置容器宽度
        ,arrow: 'always' //始终显示箭头
        //,anim: 'updown' //切换动画方式
    });
});

var see = {
    //  网站导航
    navsee:function(o){
        var d = $(o).data();
        $(d.cid).toggle();
    },
    navlay:function(o){
        var d = $(o).data();
        var g = .8 * $(window).width(),
            k = .9 * $(window).height();
        layer.open({
            type:1,
            area: [g + "px", k + "px"],
            resize:false,
            title: false,
            content:$(d.cid),
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