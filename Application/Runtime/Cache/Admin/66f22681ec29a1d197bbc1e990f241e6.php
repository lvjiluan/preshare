<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo C('WEB_TITLE');?> - 六牛科技技术支持</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <link rel="stylesheet" type="text/css" href="/Application/Admin/Statics/ui/css/admin.css?v=1.1" media="all">
    <link rel="stylesheet" type="text/css" href="/Application/Admin/Statics/ui/css/amazeui.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="/Application/Admin/Statics/ui/css/app.css?v=1.07" media="all">
    <script type="text/javascript" src="/Application/Admin/Statics/layDate-v5.0.9/laydate/laydate.js"></script>
    <!--[if lt IE 9]-->
    <script type="text/javascript" src="/Public/jquery-1.10.2.min.js"></script>
    <!--[endif]-->
    <script type="text/javascript" src="/Public/jquery-2.0.3.min.js"></script>
    
<style type="text/css">
    #ul_pics li{
        float:left;
    }
    #ul_pics li img{
        /*width:150px;*/
        /*height:76px;*/
        width:300px;
        height:152px;
    }
    .delete_imgBox{
        width: 300px;
        /*height: 50px;*/
        position: absolute;
        background: rgba(0,0,0,0.5);
        font-size: 14px;
        color: #fff !important;
        z-index: 100;
        text-align: center;
        line-height: 50px;
        margin-top: 102px;
    }
</style>

</head>
<body>
    
    <!-- 内容区 -->
    <div id="content">
        
    <div class="row-content am-cf">
        <div class="row">
           <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
                <div class="widget am-cf">
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl"><?php echo ($info['goods_id'] ? '编辑' : '添加'); ?>产品</div>
                    </div>
                    <div class="widget-body am-fr">

                        <form action="/index.php/Admin/Goods/editGoods" method="post" class="ajaxForm am-form tpl-form-border-form tpl-form-border-br">
                            <!--<div class="am-form-group">-->
                                <!--<label for="goods_name" class="am-u-sm-2 am-form-label">-->
                                    <!--产品标题 <span class="tpl-form-line-small-title must-input">*</span>-->
                                <!--</label>-->
                                <!--<div class="am-u-sm-10">-->
                                    <!--<input type="text" class="tpl-form-input" id="goods_name" name="goods_name" placeholder="请输入标题文字"  value="<?php echo ($info['goods_name']); ?>">-->
                                    <!--<small>请填写产品文字1-30字符。</small>-->
                                <!--</div>-->
                            <!--</div>-->

                            <div class="am-form-group">
                                <label for="goods_cat_id" class="am-u-sm-2 am-form-label">
                                    所属分类 <span class="tpl-form-line-small-title must-input">*</span>
                                </label>
                                <div class="am-u-sm-10">
                                    <div class="tpl-table-list-select am-align-left">
                                        <select data-am-selected="{searchBox: 1}" style="display: none;" name="goods_cat_id" id="goods_cat_id">
                                            <option value="0">选择产品所属分类</option>
                                            <?php if(is_array($categoryData)): foreach($categoryData as $key=>$v): ?><option value="<?php echo ($v['id']); ?>" <?php if($v['id'] == $info['goods_cat_id']): ?>selected<?php endif; ?>><?php echo ($v['cat_name']); ?></option><?php endforeach; endif; ?>
                                        </select>
                                        <?php if(goods_cat_id != -1): ?><script>
                                                $('#article_cat_id').val('<?php echo ($articleInfo["article_cat_id"]); ?>');
                                            </script><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="material" class="am-u-sm-2 am-form-label">
                                    材质 <span class="tpl-form-line-small-title must-input">*</span>
                                </label>
                                <div class="am-u-sm-10">
                                    <div class="tpl-table-list-select am-align-left">
                                        <select data-am-selected="{searchBox: 1}" style="display: none;" name="material" id="material">
                                            <option value="0">选择产品材质</option>
                                            <?php if(is_array($spec["material"])): foreach($spec["material"] as $key=>$v): ?><option value="<?php echo ($v['id']); ?>" <?php if($info["material"] == $v['id']): ?>selected<?php endif; ?>><?php echo ($v['value']); ?></option><?php endforeach; endif; ?>
                                        </select>
                                        <?php if(material != -1): ?><script>
                                                $('#material').val('<?php echo ($info["material"]); ?>');
                                            </script><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="width" class="am-u-sm-2 am-form-label">
                                    长度 <span class="tpl-form-line-small-title must-input">*</span>
                                </label>
                                <div class="am-u-sm-10">
                                    <div class="tpl-table-list-select am-align-left">
                                        <select data-am-selected="{searchBox: 1}" style="display: none;" name="width" id="width">
                                            <option value="0">选择长度</option>
                                            <?php if(is_array($spec["width"])): foreach($spec["width"] as $key=>$v): ?><option value="<?php echo ($v['id']); ?>" <?php if($info["width"] == $v['id']): ?>selected<?php endif; ?>><?php echo ($v['value']); ?></option><?php endforeach; endif; ?>
                                        </select>
                                        <?php if(width != -1): ?><script>
                                                $('#width').val('<?php echo ($info["width"]); ?>');
                                            </script><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="height" class="am-u-sm-2 am-form-label">
                                    高 <span class="tpl-form-line-small-title must-input">*</span>
                                </label>
                                <div class="am-u-sm-10">
                                    <div class="tpl-table-list-select am-align-left">
                                        <select data-am-selected="{searchBox: 1}" style="display: none;" name="height" id="height">
                                            <option value="0">选择高</option>
                                            <?php if(is_array($spec["height"])): foreach($spec["height"] as $key=>$v): ?><option value="<?php echo ($v['id']); ?>" <?php if($info["height"] == $v['id']): ?>selected<?php endif; ?>><?php echo ($v['value']); ?></option><?php endforeach; endif; ?>
                                        </select>
                                        <?php if(height != -1): ?><script>
                                                $('#height').val('<?php echo ($info["height"]); ?>');
                                            </script><?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label">封面图(建议尺寸:110*76)</label>
                                <div class="am-u-sm-10">
                                    <div class="row">
                                        <div class="am-u-sm-3">
                                            <div class="am-form-group am-form-file">
                                                <div class="tpl-form-file-img">
                                                    <img src="<?php echo ($info['thumb_img']); ?>" alt="" style="min-height:122px; width: 122px;" id="img_">
                                                </div>
                                                <input type="hidden" value="<?php echo ($info['thumb_img']); ?>" name="thumb_img" id="img" />
                                                <button type="button" class="am-btn am-btn-success am-btn-sm" id="btnUpload">上传</button>
                                                <button type="button" class="am-btn am-btn-danger am-btn-sm" onclick="delFile($('#img').val(), '')" id="btn_delete_">删除</button>
                                                <?php if($info['thumb_img'] == ''): ?><script>
                                                        $("#img_, #btn_delete_").hide();
                                                    </script><?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="am-u-sm-9"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label">产品图(建议尺寸:375*190)</label>
                                <div class="am-u-sm-10">
                                    <div class="row">
                                        <div class="am-u-sm-3" style="width:100%">
                                            <div class="am-form-group am-form-file">
                                                <ul id="ul_pics" class="ul_pics clearfix">
                                                    <?php if(is_array($info['imgs'])): $i = 0; $__LIST__ = $info['imgs'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li id="o_1cv59gop87fgntu1t1h3ou4237">
                                                            <div class="img">
                                                                <div class="delete_imgBox">
                                                                <input type="hidden" name="pics_url[]" value="<?php echo ($vo); ?>">
                                                                <a onclick="delHost(this)" style="color:red">删除</a>
                                                                </div>
                                                                <img src="/<?php echo ($vo); ?>" class="wantHouse-bigPage-shopImg">
                                                            </div>
                                                        </li><?php endforeach; endif; else: echo "" ;endif; ?>
                                                </ul>
                                                <input type="button"   class="wantHouse-bigPage-shopImg btn" id="btn" value="上传">
                                            </div>
                                        </div>
                                        <div class="am-u-sm-9"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="price" class="am-u-sm-2 am-form-label">
                                    价格 <span class="tpl-form-line-small-title must-input">*</span>
                                </label>
                                <div class="am-u-sm-10">
                                    <input type="text" class="tpl-form-input" id="price" name="price" placeholder="请输入价格"  value="<?php echo fen_to_yuan($info['price']);?>">
                                    <small>请填写价格。</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="explain" class="am-u-sm-2 am-form-label">
                                    产品说明 <span class="tpl-form-line-small-title must-input">*</span>
                                </label>
                                <div class="am-u-sm-10">
                                    <textarea id="explain" name="explain" placeholder="请输入简介文字" ><?php echo ($info['explain']); ?></textarea>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label for="grade" class="am-u-sm-2 am-form-label">
                                    产品等级 <span class="tpl-form-line-small-title must-input">*</span>
                                </label>
                                <div class="am-u-sm-10">
                                    <input type="text" class="tpl-form-input" id="grade" name="grade" placeholder="请输入产品等级"  value="<?php echo ($info['grade']); ?>">
                                    <small>请填写产品等级。</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label for="grade" class="am-u-sm-2 am-form-label">
                                    库存 <span class="tpl-form-line-small-title must-input">*</span>
                                </label>
                                <div class="am-u-sm-10">
                                    <input type="text" class="tpl-form-input" id="store_count" name="store_count" placeholder="请输入库存"  value="<?php echo ($info['store_count']); ?>">
                                    <small>请填写库存。</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label">
                                    排序 <span class="tpl-form-line-small-title must-input">*</span>
                                </label>
                                <div class="am-u-sm-10">
                                    <div class="row">
                                        <div class="am-u-sm-3">
                                            <input type="text" placeholder="从小到大排序" id="sort" name="sort" value="<?php echo ((isset($info['sort']) && ($info['sort'] !== ""))?($info['sort']):50); ?>">
                                            <small>请输入整数类型</small>
                                        </div>
                                        <div class="am-u-sm-9"></div></div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-form-label">是否显示</label>
                                <div class="am-u-sm-10">
                                    <div class="am-form-group">
                                        <label class="am-radio-inline">
                                            <input type="radio" name="display" id="display" value="1" data-am-ucheck> 显示
                                        </label>
                                        <label class="am-radio-inline">
                                            <input type="radio" name="display" id="hide" value="0" data-am-ucheck> 隐藏
                                        </label>
                                    </div>
                                    <?php if($info['display'] == 1 or $info['display'] == ''): ?><script>
                                            $('#display').attr('checked','true');
                                        </script>
                                    <?php else: ?>
                                        <script>
                                            $('#hide').attr('checked','true');
                                        </script><?php endif; ?>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <div class="am-u-sm-10 am-u-sm-push-2">
                                    <input type="hidden" name="goods_id" value="<?php echo ($info['goods_id']); ?>">
                                    <button type="submit" class="am-btn am-btn-primary tpl-btn-bg-color-success ">提交</button>
                                    <button type="button" class="am-btn am-btn-primary am-btn-warning " onclick="goback();">返回</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
    <div class="align-center" style="margin-top: 0px; margin-bottom:15px;">
        <!--<small>版权所有 &copy; <a href="javascript:void(0)" target="_blank" >六牛科技</a></small>-->
    </div>
    <!-- /内容区 -->
    <script type="text/javascript" src="/Application/Admin/Statics/ui/js/theme.js"></script>
    <script type="text/javascript" src="/Application/Admin/Statics/ui/js/amazeui.min.js"></script>
    <script type="text/javascript" src="/Application/Admin/Statics/ui/js/amazeui.datatables.min.js"></script>
    <script type="text/javascript" src="/Application/Admin/Statics/ui/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="/Application/Admin/Statics/ui/js/app.js?v=1.02"></script>
    <script type="text/javascript" src="/Public/layer/layer.js"></script>
    <script type="text/javascript" src="/Application/Admin/Statics/js/common.js?v=1.01"></script>
    <script type="text/javascript" src="/Static/js/ajaxForm.js"></script>


    <script>
        // 定义全局变量
        RECYCLE_URL = "<?php echo U('recycle');?>"; // 默认逻辑删除操作执行的地址
        RESTORE_URL = "<?php echo U('restore');?>"; // 默认逻辑删除恢复执行的地址
        DELETE_URL = "<?php echo U('del');?>"; // 默认删除操作执行的地址
        UPLOAD_IMG_URL = "<?php echo U('uploadImg');?>"; // 默认上传图片地址
        UPLOAD_FIELD_URL = "<?php echo U('uploadField');?>"; // 默认上传图片地址
        DELETE_FILE_URL = "<?php echo U('delFile');?>"; // 默认删除图片执行的地址
        CHANGE_STAUTS_URL = "<?php echo U('changeDisabled');?>"; // 修改数据的启用状态
    </script>
    
    <script type="text/javascript" src="/Public/ueditor/ueditor.config.js"></script>
    <script type="text/javascript" src="/Public/ueditor/ueditor.all.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="/Public/ajaxupload/ajaxupload.js?v=1.0"></script>
    <script type="text/javascript" charset="utf-8" src="/Public/ajaxupload/imgupload.js?v=1.0"></script>
    <script type="text/javascript" src="/Public/js/plupload.full.min.js"></script>
    <script type="text/javascript">
        function callback(data) {
            toastr(data.info);
            if (data.status == 1) {
                location.href = '/index.php/Admin/Goods/listGoods';
            }
        }
        var ue = UE.getEditor('content1', {
            autoHeightEnabled: false,
            initialFrameWidth: '700',
            initialFrameHeight: 350
        })

        $(function(){
            ajaxUpload('#btnUpload', $("#img"), 'Goods', '');
        })


        //===【浏览文件上传地址写入文本框】开始
        jQuery(function($) {
           // var bianhao2 = $('#bianhao2').val();

            var uploader = new plupload.Uploader({ //创建实例的构造方法
                runtimes: 'html5,flash,silverlight,html4',
                //上传插件初始化选用那种方式的优先级顺序
                browse_button: 'btn',
                // 上传按钮
                //出租
                url: "/index.php/Admin/Goods/uploadImgs",
                //远程上传地址
                flash_swf_url: 'plupload/Moxie.swf',
                //flash文件地址
                silverlight_xap_url: 'plupload/Moxie.xap',
                //silverlight文件地址
                filters: {
                    max_file_size: '500kb',
                    //最大上传文件大小（格式100b, 10kb, 10mb, 1gb）
                    mime_types: [ //允许文件上传类型
                        {
                            title: "files",
                            extensions: "jpg,png,gif"
                        }]
                },
                multi_selection: true,
                //true:ctrl多文件上传, false 单文件上传
                init: {
                    FilesAdded: function(up, files) { //文件上传前
                        if ($("#ul_pics").children("li").length > 30) {
                            alert("您上传的图片太多了！");
                            uploader.destroy();
                        } else {
                            var li = '';
                            plupload.each(files,
                                function(file) { //遍历文件
                                    li += "<li id='" + file['id'] + "'><div class='progress'><span class='bar'></span><span class='percent'>0%</span></div></li>";
                                });
                            $("#ul_pics").append(li);
                            uploader.start();
                        }
                    },
                    UploadProgress: function(up, file) { //上传中，显示进度条
                        $("#" + file.id).find('.bar').css({
                            "width": file.percent + "%"
                        }).find(".percent").text(file.percent + "%");
                    },
                    FileUploaded: function(up, file, info) { //文件上传成功的时候触发

                        var data = JSON.parse(info.response);


                        // $("#" + file.id).html("<div class='img'><img src='/" + data.pic + "' class='wantHouse-bigPage-shopImg'/ ><input type='hidden' name='pics_url[]' value='/\" + data.pic + \"'><a  onclick='delHost(this)' style='color:red'>删除</a></div>");
                        $("#" + file.id).html("<div class='img'> <div class='delete_imgBox'> <input type='hidden' name='pics_url[]' value='" + data.pic + "'> <a onclick='delHost(this)' style='color:red'>删除</a> </div> <img src='/" + data.pic + "' class='wantHouse-bigPage-shopImg'/ > </div>")
                    },
                    Error: function(up, err) { //上传出错的时候触发
                        alert(err.message);
                    }
                }
            });
            uploader.init();
        });

        function delHost(obj){
            var hurl = $(obj).prev("input[name='pics_url[]']").val();
            //$(obj).parent().parent().remove();
            $.ajax({
                type:'post',
                url:'/index.php/Admin/Goods/del_pic',
                data:{'purl':hurl},
                success:function(data){
                    if(data){
                        $(obj).parent().parent().remove();
                        alert("删除成功");
                    }else{
                        alert("删除失败")
                    }
                }
            })
        }
    </script>

</body>
</html>