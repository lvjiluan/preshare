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
						<div class="widget-title am-fl"><?php echo ($adminInfo['admin_id'] ? '编辑' : '添加'); ?>管理员</div>
					</div>
					<div class="widget-body am-fr">

						<form action="/index.php/Admin/Admin/editAdmin" method="post" class="ajaxForm am-form tpl-form-border-form tpl-form-border-br">
							<div class="am-form-group">
								<label for="admin_name" class="am-u-sm-3 am-form-label">
									管理员账号
									<?php if($adminInfo['admin_id'] <= 0): ?><span class="tpl-form-line-small-title must-input">*</span><?php endif; ?>
								</label>
								<div class="am-u-sm-7 am-u-sm-pull-2">
									<input type="text" class="tpl-form-input" id="admin_name" name="admin_name"
									       placeholder="请输入管理员账号"  value="<?php echo ($adminInfo['admin_name']); ?>"
										<?php if($adminInfo['admin_id'] > 0): ?>readonly<?php endif; ?> >
									<small>
										<?php if($adminInfo['admin_id'] <= 0): ?>请填写管理员账号1-30字符, 字母或数字, 不含中文或特殊字符。
										<?php else: ?>
											管理员账号不可编辑<?php endif; ?>
									</small>
								</div>
							</div>

							<div class="am-form-group">
								<label for="password" class="am-u-sm-3 am-form-label">
									密码
									<?php if($adminInfo['admin_id'] <= 0): ?><span class="tpl-form-line-small-title must-input">*</span><?php endif; ?>
								</label>
								<div class="am-u-sm-7 am-u-sm-pull-2">
									<input type="password" placeholder="" id="password" name="password" value="">
									<small>
										<?php if($adminInfo['admin_id'] <= 0): ?>如果不修改密码, 请留空.<?php endif; ?>
										6-30位密码, 建议大小写字符加数字及特殊字符混合使用
									</small>
								</div>
							</div>

							<div class="am-form-group">
								<label for="cpassword" class="am-u-sm-3 am-form-label">
									确认密码
									<?php if($adminInfo['admin_id'] <= 0): ?><span class="tpl-form-line-small-title must-input">*</span><?php endif; ?>
								</label>
								<div class="am-u-sm-7 am-u-sm-pull-2">
									<input type="password" placeholder="" id="cpassword" name="cpassword" value="">
									<small>再次输入管理员密码</small>
								</div>
							</div>

							<div class="am-form-group">
								<label for="email" class="am-u-sm-3 am-form-label">
									邮箱
								</label>
								<div class="am-u-sm-7 am-u-sm-pull-2">
									<input type="email" placeholder="@" id="email" name="email" value="<?php echo ($adminInfo['email']); ?>">
									<small>管理员常用邮箱</small>
								</div>
							</div>

							<div class="am-form-group">
								<label class="am-u-sm-3 am-form-label">是否禁用</label>
								<div class="am-u-sm-7 am-u-sm-pull-2">
									<div class="am-form-group">
										<label class="am-radio-inline">
											<input type="radio" name="disabled" id="able" value="1" data-am-ucheck> 启用
										</label>
										<label class="am-radio-inline">
											<input type="radio" name="disabled" id="disable" value="0" data-am-ucheck> 禁用
										</label>
									</div>
									<?php if($adminInfo['disabled'] == 1 or $adminInfo['disabled'] == ''): ?><script>
                                            $('#able').attr('checked','true');
										</script>
									<?php else: ?>
										<script>
                                            $('#disable').attr('checked','true');
										</script><?php endif; ?>
								</div>
							</div>
							<div class="am-form-group">
								<label class="am-u-sm-3 am-form-label">所属角色</label>
								<div class="am-u-sm-7 am-u-sm-pull-2">
									<?php if(is_array($roleData)): foreach($roleData as $key=>$v): ?><label class="am-checkbox-inline">
											<input data-am-ucheck name="role_ids[]" type="checkbox"  value="<?php echo ($v['id']); ?>"
												<?php if(strpos(','.$rids.',' , ','.$v['id'].',') !== false): ?>checked="checked"<?php endif; ?> >
											<?php echo ($v['role_name']); ?>
										</label><?php endforeach; endif; ?>
								</div>
							</div>

							<div class="am-form-group">
								<div class="am-u-sm-9 am-u-sm-push-3">
									<input type="hidden" name="admin_id" value="<?php echo ($adminInfo['admin_id']); ?>">
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
    
	<script type="text/javascript">
        function callback(data) {
            toastr(data.info);
            if (data.status == 1) {
                location.href = '/index.php/Admin/Admin/listAdmin';
            }
        }
	</script>

</body>
</html>