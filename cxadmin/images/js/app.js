/**
 * Created by 84071 on 2017-11-14.
 */
layui.define(['layer','laydate', 'form','element','upload'], function(exports){
    var $ = layui.jquery,
        layer = layui.layer,
        layelement = layui.element,
        layupload = layui.upload,
        laydate = layui.laydate,
        layform = layui.form;

    var see = {
        /*更改显示状态*/
        sestatus:function (o) {
            var d = $(o).data();
            $.ajax({
                async:false,
                type: "post",
                dataType: "json",
                data: {cxbsid:d.id},
                url: d.href,
                success:function (data) {
                    if(data.code == '1'){
                        layer.msg(data.msg,{icon:6});
                        location.reload();
                    }else{
                        layer.msg(data.msg,{icon:5});
                    }
                }
            })
        },
        /*单项删除*/
        deldata:function (o) {
            var d = $(o).data();
            layer.confirm("不可恢复！你确定要删除吗？", {icon: 3, title:"友情提示"}, function(index){
                $.ajax({
                    async:false,
                    type: "post",
                    dataType: "json",
                    data: {id:d.id},
                    url: d.href,
                    success:function (data) {
                        layer.msg(data.msg,{icon:7});
                        location.reload();
                    }
                })
                layer.close(index);
            });
        },
        //  选择图标
        upicon:function (o) {
            var d = $(o).data();
            layer.open({
                title: d.title,
                skin: 'cx-tclayui',
                type: 1,
                content: $(d.href),
                end: function(index, layero){
                    $(d.href).hide()
                }
            });
        },
        //  清空标签
        closdiv:function (o) {
            var d = $(o).data();
            $('.'+d.img).attr('src','');
            $('.'+d.val).val('');
            $(o).hide();
        },
        // 清空表格
        clostr:function (o) {
            var trs = $(o).closest("tr");
            trs.find("input").remove();
            trs.find("img").hide();
            trs.hide();
        },
        // 关闭层
        close:function () {
            parent.layer.closeAll();
        },
    };
    //  权限选择复选框
    layform.on('checkbox(authbox)', function(data){
        var dboxnum = $(this).closest("dl").find("dd input:checked").length;
        if($(this).parent().hasClass('dt')){
            $(this).closest("dl").find("input:checkbox").prop("checked",$(this).prop("checked"));
            if(data.elem.checked){
                $(this).closest("li.cx-fold-group").find("h4 input:checkbox").prop("checked",$(this).prop("checked"));
            }else{
                var tboxnum = $(this).closest(".cx-fold-cont").find("dt input:checked").length;
                if(tboxnum == '0'){
                    $(this).closest("li.cx-fold-group").find("h4 input:checkbox").prop("checked",$(this).prop("checked"));
                }
            }
        }else{
            if(data.elem.checked){
                $(this).closest("dl").find("dt input:checkbox").prop("checked",$(this).prop("checked"));
                $(this).closest("li.cx-fold-group").find("h4 input:checkbox").prop("checked",$(this).prop("checked"));
            }else{
                if(dboxnum == '0'){
                    $(this).closest("dl").find("dt input:checkbox").prop("checked",$(this).prop("checked"));
                }
                var tboxnum = $(this).closest(".cx-fold-cont").find("dt input:checked").length;
                if(tboxnum == '0'){
                    $(this).closest("li.cx-fold-group").find("h4 input:checkbox").prop("checked",$(this).prop("checked"));
                }
            }
        }
        layform.render();
    });
    // 删除与排序
    layform.on('submit(sd-btn)', function(data){
        var d = data.elem.dataset;
        if(d.type == 'sort'){
            $.ajax({
                type: "post",
                dataType: "json",
                data: data.field,
                url: d.href,
                success:function (data) {
                    layer.msg(data.msg,{icon:7});
                    location.reload();
                }
            })
        }
        if(d.type == 'edel'){
            layer.confirm("删除后，请至回收站找回", {icon: 3, title:"友情提示"}, function(index){
                $.ajax({
                    type: "post",
                    dataType: "json",
                    data: data.field,
                    url: d.href,
                    success:function (data) {
                        layer.msg(data.msg,{icon:7});
                        location.reload();
                    }
                })
                layer.close(index);
            })
        }
        if(d.type == 'pdel'){
            layer.confirm("批量后不可恢复！你确定要删除吗？", {icon: 3, title:"友情提示"}, function(index){
                $.ajax({
                    type: "post",
                    dataType: "json",
                    data: data.field,
                    url: d.href,
                    success:function (data) {
                        layer.msg(data.msg,{icon:7});
                        location.reload();
                    }
                })
                layer.close(index);
            })
        }
        return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
    });
    //  下拉菜单选择器
    layform.on('select(keys)', function(data){
        var old = $("input[name='keywords']").val();
        if(data.value != 0){
            if(old){
                $("input[name='keywords']").val(old + ',' + data.value)
            }else{
                $("input[name='keywords']").val(data.value)
            }
        }
    });
    layform.on('select(sources)', function(data){
        var href = $(data.elem).find("option:selected").text();
        if(data.value != 0) {
            $("input[name='sourceurl']").val(data.value);
            $("input[name='source']").val(href);
        }
    });
    //  省市区选择器
    layform.on('select(chinacodesheng)', function(data){
        var d = $(data.elem).data();
        $.ajax({
            async:false,
            type: "post",
            dataType: "json",
            data: {parzoneid:data.value,level:'2'},
            url: '/admin/Chinacode/cha.html',
            success: function (data){
                console.log(data);
                $("#"+d.title+"shi option").remove();
                $("#"+d.title+"qu option").remove();
                $("#"+d.title+"shi").append("<option value=''>请选择地市</option>");
                $("#"+d.title+"qu").append("<option value=''>请选择县区</option>");
                $(data).each(function(){
                    $("#"+d.title+"shi").append("<option value='"+this.zoneid+"'>"+this.zonename+"</option>");
                });
                layform.render('select');
            }
        });
    });
    layform.on('select(chinacodeshi)', function(data){
        var d = $(data.elem).data();
        $.ajax({
            async:false,
            type: "post",
            dataType: "json",
            data: {parzoneid:data.value,level:'3'},
            url: '/admin/Chinacode/cha.html',
            success: function (data){
                $("#"+d.title+"qu option").remove();
                $("#"+d.title+"qu").append("<option value=''>请选择县区</option>");
                $(data).each(function(){
                    $("#"+d.title+"qu").append("<option value='"+this.zoneid+"'>"+this.zonename+"</option>");
                });
                layform.render('select');
            }
        });
    });
    //  监听提交按钮
    layform.on('submit(*)', function(data){
    });
    //同时绑定多个日期选择
    $(".cx-time").each(function(){
        laydate.render({
            elem: this,
            format: 'yyyy-MM-dd',
            calendar: true,
            min: '1900-1-1',
            trigger: 'click'
        });
    });

    $('body').on('click','.cx-click',function () {
        var a = $(this),
            b = a.data('type');
        see[b] ? see[b].call(this, a) : ""
    });
    exports('app', {});
});
