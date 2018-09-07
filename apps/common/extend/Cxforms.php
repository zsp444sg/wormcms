<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------

namespace app\common\extend;;

class Cxforms{
    //  解析字段
    function rarfield($data,$fields = ''){
        $field = $data['field_db'];
        $list = array();
        if(!empty($field) && isset($field)){
            foreach ($field as $v){
                $list[] = $this->modelfield($v,$fields);
            }
        }
        return $list;
    }
    //  字段显示
    function formtext($data){
        $array = array();
        foreach ($data as $k => $v){
            switch($v['formtype']){
                case 'text':
                    $v['formtext'] = '单行文本框';
                    break;
                case 'uptxt':
                    $v['formtext'] = '多附件';
                    break;
                case 'select':
                    $v['formtext'] = '下拉菜单';
                    break;
                case 'radio':
                    $v['formtext'] = '单选框';
                    break;
                case 'upimg':
                    $v['formtext'] = '多图片';
                    break;
                case 'ieedit':
                    $v['formtext'] = '可视化编辑器';
                    break;
                case 'upmv':
                    $v['formtext'] = '多视频';
                    break;
                case 'time':
                    $v['formtext'] = '日期选择框';
                    break;
                case 'textarea':
                    $v['formtext'] = '多行文本框';
                    break;
                case 'upfile':
                    $v['formtext'] = '单文件上传';
                    break;
                case 'checkbox':
                    $v['formtext'] = '多选框';
                    break;
                case 'uppaly':
                    $v['formtext'] = '单视频';
                    break;
                case 'chinacode':
                    $v['formtext'] = '系统地区选择';
                    break;
                case 'diqu':
                    $v['formtext'] = '自订义地区选择';
                    break;
            }
            $array[$k] = $v;
        }
        return $array;
    }
    //  生成模型表单
    public function modelfield($data){
        $array = '';
        if($data['formrequired'] == 1){
            $required = 'required lay-verify="required"';
            $retext = "(<span class='t-red'>*</span>)";
        }else{
            $required = '';
            $retext = '';
        }
        if(!empty($data['formtitle']) || $data['formtitle'] != null){
            $formtitle = "<div class=\"t-gray\" style=\"width: 100%;clear:both\">{$data['formtitle']}</div>";
        }else{
            $formtitle = '';
        }
        switch ($data['formtype']){
            case 'text':
                $fl = '';
                if(isset($data['formunist']) && $data['formunist'] != null){
                    $fl = "<div class=\"cx-input-tabel\"><input type=\"text\" name=\"{$data['sqlname']}\" placeholder=\"请输入{$data['title']}\" {$required} class=\"ipt\" value=\"{\$postdb.".$data['sqlname']."|default='".$data['formvalue']."'}\"><span class=\"cx-input-fl\">{$data['formunist']}</span></div>";
                }else{
                    $fl = "<input type=\"text\" name=\"{$data['sqlname']}\" placeholder=\"请输入{$data['title']}\" {$required} class=\"ipt\" value=\"{\$postdb.".$data['sqlname']."|default='".$data['formvalue']."'}\">";
                }
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">{$fl}{$formtitle}</div>
                    </div>";
                break;
            case 'textarea':
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                            <textarea class=\"ipt\" type=\"text\" name=\"{$data['sqlname']}\" rows=\"3\" {$required}>{\$postdb.".$data['sqlname']."|default='".$data['formvalue']."'}</textarea>
                            {$formtitle}
                        </div>
                    </div>";
                break;
            case 'ieedit':
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                        <div id=\"{$data['sqlname']}\">{\$postdb.".$data['sqlname']."|default='".$data['formvalue']."'}</div>
                        <textarea id=\"{$data['sqlname']}txt\" class='hidden' name=\"{$data['sqlname']}\" {$required}>{\$postdb.".$data['sqlname']."|default='".$data['formvalue']."'}</textarea>
                        {$formtitle}
                        </div>
                    </div>
                    <script type=\"text/javascript\">
                        var {$data['sqlname']} = new E('#{$data['sqlname']}');
                        var \${$data['sqlname']}txt = \$('#{$data['sqlname']}txt');
                        {$data['sqlname']}.customConfig.uploadImgServer = \"{:url('Uploads/wangeditor')}\";
                        {$data['sqlname']}.customConfig.uploadImgMaxSize = 1 * 1024 * 1024;
                        {$data['sqlname']}.customConfig.onchange = function (html) {
                            // 监控变化，同步更新到 textarea
                            \${$data['sqlname']}txt.val(html);
                        }
                        {$data['sqlname']}.create();
                        \${$data['sqlname']}txt.val({$data['sqlname']}.txt.html());
                    </script>";
                break;
            case 'radio':
                $value = explode(',',$data['formset']);
                $arr = '';
                foreach ($value as  $v){
                    $arr .= "<input name=\"{$data['sqlname']}\" value=\"{$v}\" {eq name=\"postdb.".$data['sqlname']."|default='".$data['formvalue']."'\" value=\"{$v}\"}checked=\"checked\"{/eq} type=\"radio\" title=\"{$v}\" {$required}>";
                }
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                            {$arr}
                            {$formtitle}
                        </div>
                    </div>";
                break;
            case 'checkbox':
                $value = explode(',',$data['formset']);
                $arr =  '';
                foreach ($value as  $k => $v){
                    $arr .= "<input name=\"{$data['sqlname']}[]\" value=\"{$v}\" {if isset(\$postdb['".$data['sqlname']."']) && in_array('{$v}',explode(',',\$postdb['".$data['sqlname']."']))}checked{/if} type=\"checkbox\" title=\"{$v}\" {$required}>";
                }
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                            {$arr}
                            {$formtitle}
                        </div>
                    </div>";
                break;
            case 'select':
                $value = explode(',',$data['formset']);
                $arr =  "<option value=''>请选择{$data['title']}</option>";
                foreach ($value as  $k => $v){
                    $arr .= "<option value=\"".$v."\" {eq name=\"postdb.{$data['sqlname']}|default='".$data['formvalue']."'\" value=\"".$v."\"}selected=\"selected\"{/eq}>{$v}</option>";
                }
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                            <select id=\"{$data['sqlname']}\" name=\"{$data['sqlname']}\" {$required} lay-filter=\"{$data['sqlname']}\">
                            {$arr}
                            </select>
                            {$formtitle}
                        </div>
                    </div>";
                break;
            case 'time':
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                            <input type=\"text\" name=\"{$data['sqlname']}\" placeholder=\"请选择{$data['title']}\" {$required} class=\"ipt cx-time\" value=\"{\$postdb.".$data['sqlname']."|default='".$data['formvalue']."'}\">
                            {$formtitle}
                        </div>
                    </div>";
                break;
            case 'upfile':
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                            <div class=\"cx-fex-l\">
                                <div class='x6'>
                                <input name=\"{$data['sqlname']}\" class=\"ipt {$data['sqlname']}val\" placeholder=\"默认值一般为空\" type=\"text\" value=\"{\$postdb.".$data['sqlname']."|default=''}\" >
                                </div>
                                <div>
                                <span class=\"{$data['sqlname']}text\"></span>
                                <a class=\"button bg-green uploadbtn\" data-img=\"{$data['sqlname']}img\" data-val=\"{$data['sqlname']}val\" data-text=\"{$data['sqlname']}text\" data-del=\"{$data['sqlname']}del\" lay-data=\"{method:'post',accept:'file',data:'dfile',field:'".$data['sqlname']."'}\"><i class=\"cx-icon cx-icon-shangchuan1\"></i>上传文件</a>
                                <a class=\"button bg-red {$data['sqlname']}del cx-click\" data-img=\"{$data['sqlname']}img\" data-val=\"{$data['sqlname']}val\" data-type=\"closdiv\" {notpresent name=\"postdb.".$data['sqlname']."\"}style=\"display:none;\"{/notpresent}><i class=\"cx-icon cx-icon-lajixiang\"></i>删除</a>
                                </div>
                            </div>
                            {$formtitle}
                        </div>
                    </div>";
                break;
            case 'upimg':
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                            <div class=\"cx-input-tabel\">
                                <a class=\"button bor-green dfeilds\" data-acc=\"img\" data-tabd=\"{$data['sqlname']}tab\" data-valname=\"{$data['sqlname']}\" lay-data=\"{method:'post',accept:'images',data:'img',field:'".$data['sqlname']."'}\"><i class=\"cx-icon cx-icon-shangchuan1\"></i>选择并上传图片</a>
                            </div>
                            <div class=\"layui-upload-list\">
                                <table class=\"layui-table\">
                                  <thead>
                                    <tr>
                                    <th>文件名</th>
                                    <th class=\"t-c\">状态</th>
                                    <th class=\"t-c\">大小</th>
                                    <th class=\"t-c\">操作</th>
                                    </tr>
                                    </thead>
                                  <tbody id=\"{$data['sqlname']}tab\">
                                  {volist name=\"postdb.".$data['sqlname']."|default=''\" id=\"cx\"}
                                  <tr>
                                    <td><img src=\"{\$cx.value}\" height=\"100\" width=\"auto\"><span class=\"up-title\">{\$cx.title}</span><input type=\"hidden\" name=\"{$data['sqlname']}[{\$cx.title}]\" class=\"ipt\" value=\"{\$cx.value}\"></td>
                                    <td class=\"t-c\"><a class=\"button bg-green\">正常</a></td>
                                    <td></td>
                                    <td class=\"t-c\"><a class=\"button bg-red cx-click\" data-type=\"clostr\">删除</a></td>
                                    </tr>
                                  {/volist}
                                  </tbody>
                                </table>
                            </div>
                            {$formtitle}
                        </div>
                    </div>";
                break;
            case 'uptxt':
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                            <div class=\"cx-input-tabel\">
                                <a class=\"button bor-green dfeilds\" data-acc=\"dfile\" data-tabd=\"{$data['sqlname']}tab\" data-valname=\"{$data['sqlname']}\" lay-data=\"{method:'post',accept:'file',data:'dfile',field:'".$data['sqlname']."'}\"><i class=\"cx-icon cx-icon-shangchuan1\"></i>选择并上传文件</a>
                            </div>
                            <div class=\"layui-upload-list\">
                                <table class=\"layui-table\">
                                  <thead>
                                    <tr>
                                    <th>文件名</th>
                                    <th class=\"t-c\">状态</th>
                                    <th class=\"t-c\">大小</th>
                                    <th class=\"t-c\">操作</th>
                                    </tr>
                                    </thead>
                                  <tbody id=\"{$data['sqlname']}tab\">
                                  {volist name=\"postdb.".$data['sqlname']."|default=''\" id=\"cx\"}
                                  <tr>
                                    <td>{\$cx.title}<input type=\"hidden\" name=\"{$data['sqlname']}[{\$cx.title}]\" class=\"ipt\" value=\"{\$cx.value}\"></td>
                                    <td class=\"t-c\"><a class=\"button bg-green\">正常</a></td>
                                    <td></td>
                                    <td class=\"t-c\"><a class=\"button bg-red cx-click\" data-type=\"clostr\">删除</a></td>
                                    </tr>
                                  {/volist}
                                  </tbody>
                                </table>
                                {$formtitle}
                            </div>
                        </div>
                    </div>";
                break;
            case 'upmv':
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                            <div class=\"cx-input-tabel\">
                                <a class=\"button bor-green dfeilds\" data-acc=\"dfile\" data-tabd=\"{$data['sqlname']}tab\" data-valname=\"{$data['sqlname']}\" lay-data=\"{method:'post',accept:'video',data:'dfile',field:'".$data['sqlname']."'}\"><i class=\"cx-icon cx-icon-shangchuan1\"></i>选择并上传视频</a>
                            </div>
                            <div class=\"layui-upload-list\">
                                <table class=\"layui-table\">
                                  <thead>
                                    <tr>
                                    <th>文件名</th>
                                    <th class=\"t-c\">状态</th>
                                    <th class=\"t-c\">大小</th>
                                    <th class=\"t-c\">操作</th>
                                    </tr>
                                    </thead>
                                  <tbody id=\"{$data['sqlname']}tab\">
                                  {volist name=\"postdb.".$data['sqlname']."|default=''\" id=\"cx\"}
                                  <tr>
                                    <td>{\$cx.title}<input type=\"hidden\" name=\"{$data['sqlname']}[{\$cx.title}]\" class=\"ipt\" value=\"{\$cx.value}\"></td>
                                    <td class=\"t-c\"><a class=\"button bg-green\">正常</a></td>
                                    <td></td>
                                    <td class=\"t-c\"><a class=\"button bg-red cx-click\" data-type=\"clostr\">删除</a></td>
                                    </tr>
                                  {/volist}
                                  </tbody>
                                </table>
                            </div>
                        </div>
                    </div>";
                break;
            case 'uppaly':
                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                            <div class=\"cx-fex-l\">
                            <div class='x6'>
                            <input name=\"{$data['sqlname']}\" class=\"ipt {$data['sqlname']}val\" placeholder=\"默认值一般为空\" type=\"text\" value=\"{\$postdb.".$data['sqlname']."|default=''}\" >
                            </div>
                                <div>
                                <span class=\"{$data['sqlname']}text\"></span>
                                <a class=\"button bg-green uploadbtn\" data-img=\"{$data['sqlname']}img\" data-val=\"{$data['sqlname']}val\" data-text=\"{$data['sqlname']}text\" data-del=\"{$data['sqlname']}del\" lay-data=\"{method:'post',accept:'video',data:'dfile',field:'".$data['sqlname']."'}\"><i class=\"cx-icon cx-icon-shangchuan1\"></i>上传视频文件</a>
                                <a class=\"button bg-red {$data['sqlname']}del cx-click\" data-img=\"{$data['sqlname']}img\" data-val=\"{$data['sqlname']}val\" data-type=\"closdiv\" {notpresent name=\"postdb.".$data['sqlname']."\"}style=\"display:none;\"{/notpresent}><i class=\"cx-icon cx-icon-lajixiang\"></i>删除</a>
                                </div>
                            </div>
                        </div>
                    </div>";
                break;
            case 'chinacode':
                $sheng = $shi = $qu = '';
                $china = model('Chinacode')->allcode();
                $sheng = "<option value=\"\">请选择省</option>";
                $shi = "<option value=\"\">请选择市</option>";
                $qu = "<option value=\"\">请选择区</option>";
                //  获取省份
                foreach ($china['sheng'] as $v){
                    $sheng .= "<option value=\"{$v['zoneid']}\" {eq name=\"postdb.".$data['sqlname'].".0|default='310000'\" value=\"{$v['zoneid']}\"}selected=\"selected\"{/eq}>{$v['zonename']}</option>";
                }
                $select = "<select class='cx-china-select' name=\"{$data['sqlname']}[0]\" lay-filter=\"chinacodesheng\" data-title='{$data['sqlname']}'>{$sheng}</select>";
                //  获取市
                foreach ($china['shi'] as $k => $v){
                    $shi .= "{eq name=\"postdb.".$data['sqlname'].".0|default='310000'\" value='{$v['parzoneid']}'}
                    <option value='{$v['zoneid']}' {eq name=\"postdb.".$data['sqlname'].".1|default='310100'\" value=\"{$v['zoneid']}\"}selected=\"selected\"{/eq}>{$v['zonename']}</option>
                    {/eq}";
                }
                $shiselect = "<select id='{$data['sqlname']}shi' class='cx-china-select' name=\"{$data['sqlname']}[1]\" lay-filter=\"chinacodeshi\" data-title='{$data['sqlname']}'>{$shi}</select>";
                //  获取区
                foreach ($china['qu'] as $k => $v) {
                    $qu .= "{eq name=\"postdb." . $data['sqlname'] . ".1|default='310100'\" value='{$v['parzoneid']}'}
                    <option value='{$v['zoneid']}' {eq name=\"postdb." . $data['sqlname'] . ".2|default=''\" value=\"{$v['zoneid']}\"}selected=\"selected\"{/eq}>{$v['zonename']}</option>
                    {/eq}";
                }
                $quselect = "<select id='{$data['sqlname']}qu' class='cx-china-select' name=\"{$data['sqlname']}[2]\" lay-filter=\"chinacodequ\">{$qu}</select>";

                $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}{$retext}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                            <div class=\"cx-input-tabel\">
                            {$select}{$shiselect}{$quselect}
                            </div>
                        </div>
                    </div>";
                break;
            case 'diqu':
                $arr = '';
                $yiji = model('Diqu')->where('pid',0)->where('status',1)->order('sort desc,id asc')->select();
                $arr = "<option value=\"0\">请选择地区</option>";
                foreach ($yiji as $v){
                    $arr .= "<option class=\"cx-click\" data-href=\"".url('Diqu/dontlevel')."\" data-type=\"diqu\" data-cid=\"1\" value=\"{$v['id']}\">{$v['title']}</option>";
                }
                $select = "<select id=\"{$data['sqlname']}\" name=\"{$data['sqlname']}[]\" lay-ignore>{$arr}</select>";
                $array = "<div class=\"x3 t-r\">
                        <label class=\"lab\">{$data['title']}{$retext}</label>
                        <div class=\"xs8 xl9\">
                            <div class=\"cx-input-tabel\">
                            {\$postdb.".$data['sqlname']."|default='".$select."'}
                            </div>
                        </div>
                    </div>";
                break;
        }
        return $array;
    }
    //  生成模型表单
    public function modelth($data){
        $art = '';
        if(!empty($data['listsee']) && isset($data['listsee']) && $data['listsee'] == '1'){
            $art =  "<th class='t-c'>{$data['title']}</th>";
        }
        return $art;
    }
    //  生成模型表单
    public function modeltd($data){
        $art = '';
        if(!empty($data['listsee']) && isset($data['listsee']) && $data['listsee'] == '1'){
            $art =  "<td class='t-c'>{\$cx.{$data['sqlname']}}</td>";
        }
        return $art;
    }
    //  生成内容页面
    public function modelarticle($data){
        $array = $formunist = '';
        if($data['formtype'] == 'upimg'){
            $art = "{volist name=\"\$postdb.{$data['sqlname']}\" id=\"c\" empty=\"\"}<img src='{\$c.value}'>{/volist}";
        }elseif($data['formtype'] == 'uptxt' || $data['formtype'] == 'upmv'){
            $art = "{volist name=\"\$postdb.{$data['sqlname']}\" id=\"c\" empty=\"\"}{\$c.value}{/volist}";
        }else{
            $art = "{\$postdb.{$data['sqlname']}}";
        }
        if(isset($data['formunist']) && $data['formunist'] != null){
            $formunist = $data['formunist'];
        }
        $array = "<div class=\"cx-fex-l mb-10\">
                        <div class=\"x3 t-r\">
                         <label class=\"lab\">{$data['title']}</label>
                         </div>
                        <div class=\"xs8 xl9\">
                             <label class=\"lab t-red\">{$art} {$formunist}</label>
                        </div>
                    </div>";
        return $array;
    }
    //  读取文件
    public function file_read($filename,$method="rb"){
        if($handle=@fopen($filename,$method)){
            @flock($handle,LOCK_SH);
            $filedata=@fread($handle,@filesize($filename));
            @fclose($handle);
        }
        return $filedata;
    }
    //  写入文件
    public function file_write($filename,$data,$metd='rb+',$flock='1'){
        @touch($filename);
        $op = @fopen($filename,$metd);
        if($flock){
            @flock($op,LOCK_EX);
        }
        @fputs($op,$data);
        if($metd=="rb+"){
            @ftruncate($op,strlen($data));
        }
        @fclose($op);
        @chmod($filename,0777);
        if(is_writable($filename) ){
            return 1;
        }else{
            return 0;
        }
    }
}
