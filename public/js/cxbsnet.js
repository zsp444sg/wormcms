/**
 * Created by 84071 on 2018-03-30.
 */
$(function () {
    //  折叠
    $(".cx-fold .cx-fold-title").each(function() {
        var e = $(this);
        e.click(function() {
            e.next(".cx-fold-cont").toggle();
        })
    });
    // 浮动导航
    $(".cx-fixed").each(function () {
        var ot = $(this);
        var f = ot.attr('data-f');
        var o = ot.attr("data-t");
        if(o == null){
            o = ot.offset().top;
        } else {
            o = ot.offset().top - parseInt(o);
        }
        if(f == null){
            f = "top";
        }
        $(window).bind('scroll',function () {
            var tp = o - $(window).scrollTop();
            if (f == 'top' && tp < 0) {
                ot.addClass("cx-fixed-top");
            } else {
                ot.removeClass("cx-fixed-top");
            }
            var tb = o - $(window).scrollTop() - $(window).height();
            if (f == "bottom" && tb > 0) {
                ot.addClass("cx-fixed-bottom");
            } else {
                ot.removeClass("cx-fixed-bottom");
            }
        })
    });
    //  定义幻灯片高度
    $(".in-huandeng").each(function () {
        var w = $(this).width();
        var h = w / 2;
        if(h>600){
            $(this).height('600px');
        }else{
            $(this).height(h+'px');
        }
    })
    //  产品页幻灯
    $(".cx-projector-thumb").each(function () {
        var w = $(this).width(),
            iw = parseInt(w / 5 - 4);
        $(".cx-projector-thumb li").width(iw);
        $(".cx-projector-thumb li").height(iw);
        var d = $(".cx-projector-thumb li:first").find('img').data();
        $(".cx-projector-thumb li:first").addClass('active');
        $(".cx-projector-k").prepend("<img class='img-responsive' src='"+d.big+"'>");
        $(".cx-projector-big").find('img').attr('src',d.big);
        $(".cx-projector-pn .cx-icon").css('line-height',iw + 'px');
        $(".cx-projector-ul").height(iw + 4);
        $(this).height(iw + 4);
        //  图片居中显示
        $(".cx-projector-thumb li").each(function () {
            var imgw = $(this).find('.img-responsive').width(),
                imgh = $(this).find('.img-responsive').height();
            if(imgh > imgw){
                $(this).find('.img-responsive').height(iw);
                $(this).find('.img-responsive').width('auto');
                var imgaw = $(this).find('.img-responsive').width(),
                    left = (iw - imgaw) / 2;
                $(this).find('.img-responsive').css('left', left + 'px');
            }else{
                var top = (iw - imgh) / 2;
                $(this).find('.img-responsive').css('top', top + 'px');
            }
        });
    });
    //  幻灯鼠标移入
    $(".cx-projector-ul li").mouseover(function () {
        var d = $(this).find('img').data();
        $(this).addClass('active');
        $(this).siblings().removeClass('active');
        $(".cx-projector-k").find('img').attr('src',d.big);
        $(".cx-projector-big").find('img').attr('src',d.big);
    });
    $(".cx-projector-zk").each(function () {
        var w = $(this).width();
        $(this).height(w);
        $('.cx-projector-k').width(w);
        $('.cx-projector-k').height(w);
        var imgw = $(this).find('img').width(),
            imgh = $(this).find('img').height(),
            l = (w - imgw)/2,
            t = (w - imgh)/2;
        if(imgw > imgh){
            $('.cx-projector-k').width(imgw);
            $('.cx-projector-k').height(imgh);
            $('.cx-projector-k').css('top', t +'px');
        }else{
            $('.cx-projector-k').width(imgw);
            $('.cx-projector-k').height(imgh);
            $('.cx-projector-k').css('left', l +'px');
        }
    });
    $(".cx-projector-zk .cx-projector-k").mouseout(function () {
        $('.cx-projector-xz').hide();
        $('.cx-projector-big').hide();
    });
    //  计算移动框尺寸
    $(".cx-projector-k").mouseover(function () {
        var sw = $(this).find('img').width(),
            sh = $(this).find('img').height();
        $('.cx-projector-xz').show();
        $('.cx-projector-big').show();
        //cx-projector-xz的宽/smallImg的宽 = bigBox的宽/bigIma的宽
        $('.cx-projector-xz').width(80);
        $('.cx-projector-xz').height(80);
    });
    //  鼠标移动放大
    $(".cx-projector-xd").mousemove(function (e) {
        var w = $(this).width() - 80,
            h = $(this).height() - 80;
        var x,y;
        var img = new Image();
        img.src = $('.cx-projector-big img').attr("src") ;
        var iw = img.width,
            ih = img.height;
        // //x和y的坐标
        x = e.offsetX - 40;
        y = e.offsetY - 40;
        if(x<0){
            x=0;
        }
        if(x > w){
            x = w;
        }
        if(y<0){
            y=0;
        }
        if(y > h){
            y = h;
        }
        $('.cx-projector-xz').css('left',x +'px');
        $('.cx-projector-xz').css('top',y +'px');
        //sw * bw / iw
        $('.cx-projector-big img').css('left', '-'+ iw / $('.cx-projector-k img').width() * x +'px');
        $('.cx-projector-big img').css('top', '-'+ ih / $('.cx-projector-k img').height() * y +'px');
    });
    // 切换上一个
    $(".cx-projector-pn").click(function (o) {
        var l = $(".cx-projector-ul li.active"),
            la = $(".cx-projector-ul li").length,
            ln = l.nextAll().length,
            lp = l.prevAll().length,
            liw = l.width() + 2,
            tw = $(".cx-projector-thumb").width();
        if($(this).hasClass('cx-projector-prev')){
            if(lp > 0) {
                var u = ln + 2,
                    w = (lp - 1) * liw,
                    d = l.prev().find('img').data();
                if(u > 5){
                    $(".cx-projector-ul").css('left','-'+ w +'px');
                }
                l.prev().addClass('active');
                l.removeClass('active');
                $(".cx-projector-k").find('img').attr('src',d.big);
                $(".cx-projector-big").find('img').attr('src',d.big);
            }
        }
        if($(this).hasClass('cx-projector-next')){
            if(ln > 0){
                var u = la - ln + 1,
                    w = (u - 5) * liw,
                    d = l.next().find('img').data();
                if(u > 5){
                    $(".cx-projector-ul").css('left', '-' + w + 'px');
                }
                l.next().addClass('active');
                l.removeClass('active');
                $(".cx-projector-k").find('img').attr('src',d.big);
                $(".cx-projector-big").find('img').attr('src',d.big);
            }
        }
    });
});