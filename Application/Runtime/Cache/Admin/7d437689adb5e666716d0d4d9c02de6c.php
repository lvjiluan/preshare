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
    
	<style>
		.am-table {
			font-size: 1.3rem;
			margin-bottom: 0;
		}
		.am-btn{
			padding:5px 15px;font-size:12px
		}
	</style>

</head>
<body>
    
    <!-- 内容区 -->
    <div id="content">
        
	<!-- 内容区域 -->
	<div class="row-content am-cf">
		<div class="row">
			<div class="widget am-cf">
				<div class="widget-head am-cf">
					<div class="widget-title  am-cf">用户基本信息</div>
				</div>
				<div class="widget-body  am-fr">
					<form id="orderForm" action="/index.php/Admin/User/userDetail" method="post" class="ajaxForm">
						<table width="100%" class="am-table am-table-bordered">
							<thead>
							<tr><th colspan="4">用户基本信息</th></tr>
							</thead>
							<tbody>
							<tr>
								<td style="width: 35%;"><div align="right"><strong>真实姓名：</strong></div></td>
								<td><?php echo ($userInfo['user_name']); ?></td>
							</tr>
							<tr>
								<td><div align="right"><strong>昵称：</strong></div></td>
								<td><?php echo ($userInfo['nickname']); ?></td>
							</tr>
							<tr>
								<td><div align="right"><strong>手机号：</strong></div></td>
								<td><?php echo ($userInfo['mobile']); ?></td>
							</tr>
							<tr>
								<td><div align="right"><strong>性别：</strong></div></td>
								<td>
									<?php if($userInfo['sex'] == 1): ?>男
										<?php elseif($userInfo['sex'] == 2): ?>
										女
										<?php elseif($userInfo['sex'] == 0): ?>
										保密<?php endif; ?>
								</td>
							</tr>


							<tr>
								<td><div align="right"><strong>注册时间：</strong></div></td>
								<td><?php echo time_format($userInfo['register_time']);?></td>
							</tr>
							<tr>
								<td><div align="right"><strong>公司名称：</strong></div></td>
								<td><?php echo ($userInfo['company']); ?></td>
							</tr>
							</tbody>
						</table>
						<table width="100%" class="am-table am-table-bordered">
							<thead>
							<tr><th colspan="4">用户验证信息</th></tr>
							</thead>
							<tbody>
							<?php if(!empty($userInfo)): ?><tr>
									<td style="width: 35%;"><div align="right"><strong>真实姓名：</strong></div></td>
									<td><?php echo ($userInfo['true_name']); ?></td>
								</tr>


								<tr>
									<td><div align="right"><strong>头像：</strong></div></td>
									<td><img src="<?php echo ($userInfo['head_pic']); ?>" width="100px" alt="头像"></td>
								</tr>

								<tr>
									<td><div align="right"><strong>添加时间：</strong></div></td>
									<td><?php echo time_format($userInfo['add_time']);?></td>
								</tr><?php endif; ?>
							<!--<tr>-->
								<!--<td style="width: 35%;"><div align="right"><strong>验证结果：</strong></div></td>-->
								<!--<td>-->
									<!--<?php if($userInfo['is_auth'] == 1): ?>已通过<?php else: ?>未通过<?php endif; ?>-->
								<!--</td>-->
							<!--</tr>-->

							</tbody>
						</table>
						<table width="100%" class="am-table">
							<tbody>
							<tr>
								<td align="center">
									<button type="button" class="am-btn am-btn-default tpl-btn-bg-color-default " onclick="goback()">返回</button>
								</td>
							</tr>
							</tbody>
						</table>
					</form>
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
        $(function(){
            $('img').click(function(){
                window.open($(this).attr('src'));
            });
        });
	</script>

</body>
</html>