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
						<div class="widget-title am-fl">系统参数管理</div>
						<br/>
						<div class="must-input"><small>警告: 系统参数请慎重修改, 在不确定的情况下修改可能会造成系统无法正常使用.</small></div>
					</div>
					<div class="widget-body am-fr">

						<form action="<?php echo U('save');?>" method="post" class="ajaxForm am-form tpl-form-border-form tpl-form-border-br">
							<div class="am-tabs" data-am-tabs="{noSwipe: 1}" id="doc-tab-demo-1">
								<ul class="am-tabs-nav am-nav am-nav-tabs">
									<?php if(is_array(C("CONFIG_GROUP_LIST"))): $i = 0; $__LIST__ = C("CONFIG_GROUP_LIST");if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$group): $mod = ($i % 2 );++$i;?><li <?php if(($id) == $key): ?>class="am-active"<?php endif; ?> onclick="window.location.href='<?php echo U('?id='.$key);?>'">
											<a href="javascript:void(0)"><?php echo ($group); ?></a>
										</li><?php endforeach; endif; else: echo "" ;endif; ?>
								</ul>
							</div>

							<div class="am-tabs-bd">
								<div class="am-tab-panel am-active">
									<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$config): $mod = ($i % 2 );++$i;?><div class="am-form-group">
											<label class="am-u-sm-3 am-form-label">
												 <span class="tpl-form-line-small-title"><?php echo ($config["key"]); ?></span> <?php echo ($config["name"]); ?>
											</label>
											<div class="am-u-sm-7 am-u-sm-pull-2">
												<?php switch($config["type"]): case "0": ?><input type="text" class="tpl-form-input" name="config[<?php echo ($config["key"]); ?>]" value="<?php echo ($config["value"]); ?>"><?php break;?>
													<?php case "1": ?><input type="text" class="tpl-form-input" name="config[<?php echo ($config["key"]); ?>]" value="<?php echo ($config["value"]); ?>"><?php break;?>
													<?php case "2": ?><textarea class="tpl-form-input" name="config[<?php echo ($config["key"]); ?>]"><?php echo ($config["value"]); ?></textarea><?php break;?>
													<?php case "3": ?><select class="select" name="config[<?php echo ($config["key"]); ?>]">
															<?php $_result=parse_config_attr($config['extra']);if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>" <?php if(($config["value"]) == $key): ?>selected<?php endif; ?>><?php echo ($vo); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
														</select><?php break;?>
													<?php case "4": ?><textarea class="tpl-form-input" name="config[<?php echo ($config["key"]); ?>]" rows="5" cols=""><?php echo ($config["value"]); ?></textarea><?php break; endswitch;?>
												<small><?php echo ($config["desc"]); ?></small>
											</div>
										</div><?php endforeach; endif; else: echo "" ;endif; ?>

									<?php if(empty($list)): ?><h4>aOh! 参数内容为空, 请新建!</h4>
									<?php else: ?>
										<div class="am-form-group">
											<div class="am-u-sm-7 am-u-sm-pull-2 am-u-sm-push-3">
												<input type="hidden" name="rank_id" value="<?php echo ($rankinfo['rank_id']); ?>" />

												<button type="submit" class="am-btn am-btn-primary tpl-btn-bg-color-success ">提交</button>
											</div>
										</div><?php endif; ?>
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
    


</body>
</html>