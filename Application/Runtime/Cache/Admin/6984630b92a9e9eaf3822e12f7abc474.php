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
    


</head>
<body>
    
    <!-- 内容区 -->
    <div id="content">
        
	<div class="row-content am-cf">
		<div class="row">
			<div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
				<div class="widget am-cf">
					<div class="widget-head am-cf">
						<div class="widget-title am-fl"><?php echo ($privilege['id'] ? '编辑' : '添加'); ?>权限节点</div>
					</div>
					<div class="widget-body am-fr">

						<form action="/index.php/Admin/Privilege/editPrivilege" method="post" class="ajaxForm am-form tpl-form-border-form tpl-form-border-br">

							<div class="am-form-group">
								<label for="parent_id" class="am-u-sm-3 am-form-label">
									所属上级权限
								</label>
								<div class="am-u-sm-9">
									<div class="tpl-table-list-select am-align-left">
										<select data-am-selected="{searchBox: 1}" style="display: none;" name="parent_id" id="parent_id">
											<option value="0">顶级权限</option>
											<?php if(is_array($privilegelist)): foreach($privilegelist as $key=>$v): ?><option value="<?php echo ($v['id']); ?>">
													<?php if($v['parent_id'] == 0): ?><b> <?php echo ($v['pri_name']); ?> </b>
													<?php else: ?> <?php echo (str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;',$v['level'])); ?> ├- <?php echo ($v['pri_name']); endif; ?>
												</option><?php endforeach; endif; ?>
										</select>
										<?php if($privilege['parent_id'] != 0): ?><script>
                                                $('#parent_id').val("<?php echo ($privilege['parent_id']); ?>");
											</script><?php endif; ?>
									</div>
								</div>
							</div>

							<div class="am-form-group">
								<label for="pri_name" class="am-u-sm-3 am-form-label">
									角色名称 <span class="tpl-form-line-small-title must-input">*</span>
								</label>
								<div class="am-u-sm-7 am-u-sm-pull-2">
									<input type="text" class="tpl-form-input" id="pri_name" name="pri_name" value="<?php echo ($privilege['pri_name']); ?>" placeholder="请输入角色名称" >
								</div>
							</div>

							<div class="am-form-group">
								<label for="module_name" class="am-u-sm-3 am-form-label">
									模块名称
								</label>
								<div class="am-u-sm-7 am-u-sm-pull-2">
									<input type="text" class="tpl-form-input" id="module_name" name="module_name" value="<?php echo ($privilege['module_name']); ?>" placeholder="请输入模块名称" >
								</div>
							</div>

							<div class="am-form-group">
								<label for="controller_name" class="am-u-sm-3 am-form-label">
									控制器名称
								</label>
								<div class="am-u-sm-7 am-u-sm-pull-2">
									<input type="text" class="tpl-form-input" id="controller_name" name="controller_name" value="<?php echo ($privilege['controller_name']); ?>" placeholder="请输入控制器名称" >
								</div>
							</div>

							<div class="am-form-group">
								<label for="action_name" class="am-u-sm-3 am-form-label">
									方法名称
								</label>
								<div class="am-u-sm-7 am-u-sm-pull-2">
									<input type="text" class="tpl-form-input" id="action_name" name="action_name" value="<?php echo ($privilege['action_name']); ?>" placeholder="请输入方法名称" >
								</div>
							</div>

							<div class="am-form-group">
								<label for="params" class="am-u-sm-3 am-form-label">
									URL参数
								</label>
								<div class="am-u-sm-7 am-u-sm-pull-2">
									<input type="text" class="tpl-form-input" id="params" name="params" value="<?php echo ($privilege['params']); ?>" placeholder="请输入URL参数路径" >
								</div>
							</div>

							<div class="am-form-group">
								<label class="am-u-sm-3 am-form-label">
									排序 <span class="tpl-form-line-small-title must-input">*</span>
								</label>
								<div class="am-u-sm-9">
									<div class="row">
										<div class="am-u-sm-3">
											<input type="text" placeholder="从小到大排序" id="sort" name="sort" value="<?php echo ($privilege['sort']); ?>">
											<small>请输入整数类型</small>
										</div>
										<div class="am-u-sm-9"></div></div>
								</div>
							</div>

							<div class="am-form-group">
								<div class="am-u-sm-9 am-u-sm-push-3">
									<input type="hidden" name="id" value="<?php echo ($privilege['id']); ?>">
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

	<script type="text/javascript">
        function callback(data) {
            toastr(data.info);
            if (data.status == 1) {
                location.href = '/index.php/Admin/Privilege/listPrivilege';
            }
        }
        var ue = UE.getEditor('content1', {
            autoHeight: false,
            initialFrameHeight: 500
        });

        $(function(){
            ajaxUpload('#btnUpload', $("#img"), 'Article', '');
        })
	</script>

</body>
</html>