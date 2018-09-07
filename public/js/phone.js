$(function () {
    //  产品页幻灯
//  定义图片外框尺寸
    $(".cx-projector-ul .thumb").each(function () {
        var w = $(this).width(),
            h = $(this).find('.img-responsive').height(),
            t = (w - h)/2;
        $(this).find('.img-responsive').css('padding-top',t+'px');
    });
    $(".cx-projector-ul .thumb:first").each(function () {
        var d = $(this).find('img').data();
        $(this).addClass('active');
        $(".cx-projector-k").prepend("<img class='img-responsive' src='"+d.big+"'>");
        $(".cx-projector-big").find('img').attr('src',d.big);
    });
//  幻灯鼠标移入
    $(".cx-projector-ul .thumb").mouseover(function () {
        var d = $(this).find('img').data();
        $(this).addClass('active');
        $(this).parent().siblings().find(".thumb").removeClass('active');
        $(".cx-projector-k").find('img').attr('src',d.big);
        $(".cx-projector-big").find('img').attr('src',d.big);
    });
    $(".cx-projector-k").each(function () {
        var w = $(this).width(),
            h = $(this).find('.img-responsive').height(),
            t = (w - h)/2;
        $(this).height(w);
        $(this).find('.img-responsive').css('padding-top',t+'px');
    });
    $(".cx-projector-xz").mouseout(function () {
        $('.cx-projector-xd').hide();
        $('.cx-projector-big').hide();
    });
//  计算移动框尺寸
    $(".cx-projector-xz").mouseover(function () {
        var sw = $(this).width(),
            sh = $(this).height(),
            bw = $('.cx-projector-big').width(),
            bh = $('.cx-projector-big').height();
        var img = new Image();
        img.src = $('.cx-projector-big img').attr("src") ;
        var iw = img.width,
            ih = img.height;
        $('.cx-projector-xd').show();
        $('.cx-projector-big').show();
//square的宽/smallImg的宽 = bigBox的宽/bigIma的宽
        $('.cx-projector-xd').width(sw * bw / iw);
        $('.cx-projector-xd').height(sh * bh / ih);
    });
//  鼠标移动放大
    $(".cx-projector-k .cx-projector-xz").mousemove(function (e) {
        var w = $(".cx-projector-xz").width() - $('.cx-projector-xd').width(),
            h = $(".cx-projector-xz").height() - $('.cx-projector-xd').height();
        var x,y;
        var img = new Image();
        var iw = img.width,
            ih = img.height;
// //x和y的坐标
        x = e.offsetX - $('.cx-projector-xd').width() / 2;
        y = e.offsetY - $('.cx-projector-xd').height() / 2;
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
        $('.cx-projector-xd').css('left',x +'px');
        $('.cx-projector-xd').css('top',y +'px');
        $('.cx-projector-big img').css('left', '-'+ iw / $('.cx-projector-k .img-responsive').width() * x +'px');
        $('.cx-projector-big img').css('top', '-'+ ih / $('.cx-projector-k .img-responsive').height() * y +'px');
    });

})