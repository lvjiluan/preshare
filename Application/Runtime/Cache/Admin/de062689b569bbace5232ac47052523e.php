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
						<div class="widget-title  am-cf">广告列表</div>
					</div>
					<div class="widget-body  am-fr">
						<div class="am-u-sm-12 am-u-md-6 am-u-lg-5">
							<div class="am-form-group">
								<div class="am-btn-toolbar">
									<div class="am-btn-group am-btn-group-xs">
										<a type="button" class="am-btn am-btn-default am-btn-success" href="<?php echo U('Ad/editAd');?>"><span class="am-icon-plus"></span> 新增</a>
										<button type="button" class="am-btn am-btn-default am-btn-danger" onclick="javascript:recycle('chkbId', '确认删除?! 删除后无法恢复!', true)">
											<span class="am-icon-trash-o"></span> 批量删除
										</button>
									</div>
								</div>
							</div>
						</div>
						<form action="/index.php/Admin/Ad/listAd" method="get">
	                        <div class="am-u-sm-12 am-u-md-6 am-u-lg-3">
	                            <div class="am-form-group tpl-table-list-select">
	                                <select data-am-selected="{searchBox: 1}" name="position_id" id="position_id" >
	                                    <option value="0">广告位置</option>
	                                    <?php if(is_array($adPosList)): foreach($adPosList as $key=>$v): ?><option value="<?php echo ($v['position_id']); ?>"><?php echo ($v['name']); ?></option><?php endforeach; endif; ?>
	                                </select>
	                                <?php if($position_id != 0): ?><script>
	                                        $("#position_id").val("<?php echo ($position_id); ?>");
	                                    </script><?php endif; ?>
	                            </div>
	                        </div>
	                        <div class="am-u-sm-12 am-u-md-12 am-u-lg-4">
	                            <div class="am-input-group am-input-group-sm tpl-form-border-form cl-p">
	                                <input type="text" class="am-form-field" name="keywords" placeholder="广告名称" value="<?php echo ($keyword); ?>">
	                                <span class="am-input-group-btn">
	                                    <button class="am-btn  am-btn-default am-btn-success tpl-table-list-field am-icon-search" type="submit"></button>
	                                    <a class="am-btn  am-btn-default am-btn-danger tpl-table-list-field am-icon-refresh" type="button" href="/index.php/Admin/Ad/listAd"></a>
	                                </span>
	                            </div>
	                        </div>
	                    </form>

						<div class="am-u-sm-12">
							<table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black " id="example-r">
								<thead>
								<tr>
									<th><input class="check-all" type="checkbox"></th>
									<th nowrap>序号</th>
									<th nowrap>广告名称</th>
									<th nowrap>所属广告位置</th>
									<!--<th nowrap>开始时间</th>-->
									<!--<th nowrap>结束时间</th>-->
									<th nowrap>是否显示</th>
									<th nowrap>操作</th>
								</tr>
								</thead>
								<tbody>
								<?php if(is_array($data)): foreach($data as $key=>$v): ?><tr class="gradeX">
										<td><input class="ids" type="checkbox" name="chkbId" value="<?php echo ($v['ad_id']); ?>"></td>
										<td><?php echo ($v['sort']); ?></td>
										<td><?php echo ($v['title']); ?></td>
										<td><?php echo ($v['name']); ?></td>
										<!--<td><?php echo time_format($v['start_time'], 'Y-m-d');?></td>-->
										<!--<td><?php echo time_format($v['end_time'], 'Y-m-d');?></td>-->
										<td><?php echo show_dispaly($v['display']);?></td>

										<td class="f-14">
											<div class="tpl-table-black-operation">
												<a href="<?php echo U('Ad/editAd',array('ad_id'=>$v['ad_id']));?>">
													<i class="am-icon-pencil"></i> 编辑
												</a>
												<a href="javascript:void(0)" onclick="javascript:recycle(<?php echo ($v['ad_id']); ?>, '确认删除?!此步骤无法恢复!', true)" class="tpl-table-black-operation-del">
													<i class="am-icon-trash"></i> 删除
												</a>
											</div>
										</td>
									</tr><?php endforeach; endif; ?>

								<!-- more data -->
								</tbody>
							</table>

							<?php if(empty($data)): ?><h4>aOh! 没有相关内容!</h4><?php endif; ?>
						</div>
						<div class="am-u-lg-12 am-cf">

							<div class="am-fr">
								<div class="am-pagination tpl-pagination">
									<?php echo ($page); ?>
								</div>
							</div>
						</div>
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
	</script>

</body>
</html>