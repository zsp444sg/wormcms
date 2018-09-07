/**
 * Created by 84071 on 2017-11-14.
 */
layui.define(['layer', 'form','element'], function(exports){
    var $ = layui.jquery,
        layer = layui.layer,
        layelement = layui.element,
        layform = layui.form;

    var see = {
        opentaskbar:function (a) {
            see.hidemenu();
            $("#cx-openmenu").toggleClass("cx-menu-on").off("mousedown", see.stope).on("mousedown", see.stope);
            $(".cx-win").toggleClass("cx-win-on").off("mousedown", see.stope).on("mousedown", see.stope);
            $(document).off("mousedown", see.offmenu).on("mousedown", see.offmenu);
            $(window).off("resize", see.offmenu).on("resize", see.offmenu);
            a.off("mousedown", see.stope).on("mousedown", see.stope)
        },
        offmenu: function() {
            see.hidemenu();
            $(".cx-menu").removeClass("cx-menu-on")
            $(".cx-win").removeClass("cx-win-on")
        },
        maxApp: function() {
            see.hidemenu();
            var a = $(".cx-taskbar").width() - 160,
                c = $(".cx-task").width();
            return 34 < a - c ? !0 : !1
        },
        openadd:function (o) {
            see.hidemenu();
            var e = $(o).data(),
                icon = $(o).find('i').attr('class'),
                m = !0,
                g = .8 * $(".map").width(),
                f = "",
                k = .9 * $(".map").height();
            $(document).find(".cx-task-app").each(function(c, d) {
                $(d).attr("title") == e.title && (o.removeClass("disabled"), $(d).click(), m = !1)
            });
            if(m){
                if (!see.maxApp()) {
                    if ($(".cx-task-app span.app-title").hasClass("layui-hide")) {
                        layer.alert("请先关闭一些窗口！", {
                            title: "温馨提示",
                            icon: 2,
                            zIndex: layer.zIndex + 1
                        }, function(b) {
                            a.removeClass("disabled");
                            layer.close(b)
                        });
                        return
                    }
                    $(".cx-task-app span.app-title").addClass("layui-hide")
                }
                see.offmenu();
                var cid = layer.open({
                    title: e.title,
                    id: e.cid,
                    type: 2,
                    shadeClose: false,
                    anim: 1 === o.full ? -1 : "5",
                    area: [g + "px", k + "px"],
                    fixed: false,
                    maxmin: true,
                    content: e.href,
                    shade:0,
                    resize:true,
                    skin:'cx-layer-open',
                    zIndex: layer.zIndex,
                    success: function(b, c) {
                        o.removeClass("disabled");
                        layer.setTop(b);
                        b.find(".layui-layer-title").find("i").is(":visible") || b.find(".layui-layer-title").prepend('<i class="mr-10 '+icon+'"></i>');
                        b.find(".layui-refreswind").is(":visible") || b.find(".layui-layer-setwin").prepend('<a class="layui-icon pointer cx-click layui-refreswind" data-type="refreshWind" data-id="' + c + '">&#x1002;</a>');
                    },
                    min: function(a, c) {
                        $(a).hide();
                        $("#" + f).removeClass("cx-task-on");
                        var e = [];
                        $(document).find(".layui-layer-iframe:visible").each(function(a, c) {
                            e.push($(c).css("z-index"))
                        });
                        if (1 > e.length) return !1;
                        var d = e.sort().pop();
                        $(document).find(".layui-layer-iframe:visible").each(function(a, c) {
                            if ($(c).css("z-index") == d) return $("#cx-" + $(c).attr("id")).addClass("cx-task-on"), !1
                        });
                        return !1
                    },
                    full: function(b, a) {
                        b.css({'height':$(document).height()-40,'top':'-5px'});
                        b.find('#layui-layer-iframe'+ cid).height(b.height()-43);
                    },
                    restore: function(b, a) {},
                    moveEnd: function() {
                        $("#" + f).addClass("cx-task-on").siblings().removeClass("cx-task-on")
                    },
                    cancel: function(a) {
                        var c = layui.data("cx-click")["cx-click-" + a];
                        layui.each(c, function(a, b) {
                            layer.close(b)
                        });
                        layui.data("cx-click", {
                            key: "cx-click-" + a,
                            remove: !0
                        });
                        $("#" + f).remove()
                    },
                });
                1 === e.full && layer.full(cid);
                f = "cx-layui-layer" + cid;
                g = "";
                g = $(".cx-task-app span.app-title").hasClass("layui-hide") ? "layui-hide" : "";
                g = icon ? "" + ('<div class="layui-inline layui-elip cx-task-app cx-task-on" title="' + e.title + '" id="' + f + '"><i class="mr-10 '+icon+'"></i><span class="app-title layui-elip ' + g + '">' + e.title + "</span></div>") : "" + ('<div class="layui-inline layui-elip cx-task-app cx-task-on" title="' + e.title + '" id="' + f + '"><span class="app-title layui-elip ' + g + '">' + e.title + "</span></div>");
                $("#" + f).is(":visible") || ($(".cx-task").append(g), $("#" + f).on("click", function() {
                    if($(this).hasClass("cx-task-on")){
                        $("#layui-layer" + cid).hide();
                        $(this).removeClass("cx-task-on");
                    }else{
                        $("#layui-layer" + cid).show();
                        $(this).addClass("cx-task-on").siblings().removeClass("cx-task-on");
                        layer.zIndex = parseInt(layer.zIndex + 1), layer.style(cid, {
                            zIndex: layer.zIndex
                        })
                    }
                }).siblings().removeClass("cx-task-on"))
            }
        },
        menuleft:function (a) {
            see.hidemenu();
            if($(this).find("span.ml-10").is(':hidden')){
                $(this).closest(".cx-menuleft").find("span.ml-10").show();
                $(".cx-menuleft").width($(".cx-menuleftsz li").width());
            }else{
                $(this).closest(".cx-menuleft").find("span.ml-10").hide();
                $(".cx-menuleft").width($(".cx-menuleftsz li").width());
            }
        },
        refreshWind: function(a) {
            see.hidemenu();
            var a = a.data("id"),
                url = $("#layui-layer-iframe" + a);
            $(url).attr('src', $(url).attr('src'));
        },
        //  显示桌面
        showdesktop: function () {
             see.hidemenu();
            $(document).find(".cx-layer-open .layui-layer-min").click();
            $(document).find(".cx-task-app").removeClass("cx-task-on")
        },
        stope: function(a) {
            see.hidemenu();
            a = a || window.event;
            a.stopPropagation ? a.stopPropagation() : a.cancelBubble = !0
        },
        hidemenu: function(a) {
            $(".right-menu").hide()
        },
        //  菜单栏尺寸
        dcinfo: function () {
            var thei = $(window).height() - 40;
            if(thei > 600){
                $(".cx-side").height(thei / 2);
            }else{
                $(".cx-side").height(300);
            }
            $(".cx-menu").height($(".cx-side").height())
            $(".cx-menuleft").height($(".cx-side").height())
            $(".cx-map").height(thei)
            $(window).resize(function(){
                var thei = $(window).height() - 40;
                if(thei > 600){
                    $(".cx-side").height(thei / 2);
                }else{
                    $(".cx-side").height(300);
                }
                $(".cx-menu").height($(".cx-side").height())
                $(".cx-menuleft").height($(".cx-side").height())
            })
        },
        //  调用时间
        pattern: function(a) {
            var b = new Date,
                e = {
                    "M+": b.getMonth() + 1,
                    "d+": b.getDate(),
                    "h+": 0 == b.getHours() % 12 ? 12 : b.getHours() % 12,
                    "H+": b.getHours(),
                    "m+": b.getMinutes(),
                    "s+": b.getSeconds(),
                    "q+": Math.floor((b.getMonth() + 3) / 3),
                    S: b.getMilliseconds()
                },
                c = {
                    0: "日",
                    1: "一",
                    2: "二",
                    3: "三",
                    4: "四",
                    5: "五",
                    6: "六"
                };
            /(y+)/.test(a) && (a = a.replace(RegExp.$1, (b.getFullYear() + "").substr(4 - RegExp.$1.length)));
            /(E+)/.test(a) && (a = a.replace(RegExp.$1, (1 < RegExp.$1.length ? 2 < RegExp.$1.length ? "星期" : "周" : "") + c[b.getDay() + ""]));
            for (var d in e)(new RegExp("(" + d + ")")).test(a) && (a = a.replace(RegExp.$1, 1 == RegExp.$1.length ? e[d] : ("00" + e[d]).substr(("" + e[d]).length)));
            return a
        },
        dctime:function () {
            $(".cx-time").attr("title", see.pattern("yyyy年MM月dd日 EEE"));
            $("#laydate-hs").text(see.pattern("HH:mm"));
            $("#laydate-ymd").text(see.pattern("yyyy-MM-dd"))
        },
        bar:function () {
            $(window).load(function(){
                $(".cx-side").mCustomScrollbar();
            });
        },
        arrange: function(a) {
            a = $(".cx-map").index();
            a = $(".cx-mapindex:eq(" + ("" == a || void 0 == a ? 0 : a) + ")");
            var c = $(".cx-mapindex"),
                e = 0,
                d = 0,
                k = 96,
                f = 96,
                h = 0,
                h = c.height() - 40;
            c.width();
            a.find(".cx-map-app").each(function(a, c) {
                $(c).css("top", d + "px");
                $(c).css("left", e + "px");
                f = $(c).height();
                k = $(c).width();
                d = d + f + 10 + 10;
                d >= h - 65 && (d = 0, e = e + k + 10)
            })
        },
        //  右键
        rightmenu: function() {
            $(document).contextmenu(function() {
                return !1;
            });
            $(".cx-map").on("contextmenu", function(a) {
                var c = a.clientX;
                    a = a.clientY;
                var d = $(".right-menu"),
                    g = document.documentElement.clientWidth,
                    k = document.documentElement.clientHeight,
                    c = c + d.width() >= g ? g - d.width() - 15 : c;
                    a = a + d.height() >= k - 40 ? k - d.height() - 50 : a;
                d.css({
                    top: a,
                    left: c
                }).show()
            })
        },
        //  全屏
        allfull:function(o){
            see.hidemenu();
            var docElm = document.documentElement;
            $(o).text('退出全屏').data('type','endallfull');
            if (docElm.requestFullscreen) {
                docElm.requestFullscreen();
            }else if (docElm.mozRequestFullScreen) {
                docElm.mozRequestFullScreen();
            }else if (docElm.webkitRequestFullScreen) {
                docElm.webkitRequestFullScreen();
            }else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        },
        endallfull:function(o){
            see.hidemenu();
            $(o).text('进入全屏').data('type','allfull');
            document.exitFullscreen ? document.exitFullscreen() : document.mozCancelFullScreen ? document.mozCancelFullScreen() : document.webkitExitFullscreen && document.webkitExitFullscreen()
        },
        //  关闭所有
        closeall:function(){
            see.hidemenu();
            layer.confirm('确定要关闭所有窗口吗?', {icon: 3, title:'提示',zIndex: layer.zIndex,success: function(layero){
                    layer.setTop(layero);
                }}, function(index){
                layer.closeAll('iframe');
                $(document).find(".cx-task").html("")
                layer.close(index);
            });
        },
        info: function () {
            see.dcinfo();
            see.bar();
            see.arrange();
            see.rightmenu();
            setInterval(see.dctime, 1E3);
        }
    }
    see.info();
    $('body').on('click','.cx-click',function () {
        var a = $(this),
            b = a.data('type');
        see[b] ? see[b].call(this, a) : ""
    });
    exports('map', {});
});
