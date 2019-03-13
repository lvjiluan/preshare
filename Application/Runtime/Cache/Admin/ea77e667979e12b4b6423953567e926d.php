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
    
    <link rel="stylesheet" href="/Application/Admin/Statics/zTree/css/zTreeStyle/zTreeStyle.css" type="text/css">

</head>
<body>
    
    <!-- 内容区 -->
    <div id="content">
        
    <div class="row-content am-cf">
        <div class="row">
            <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
                <div class="widget am-cf">
                    <div class="widget-head am-cf">
                        <div class="widget-title  am-cf">权限结点列表</div>

                    </div>
                    <div class="widget-body  am-fr">

                        <div class="am-u-sm-12 am-u-md-8 am-u-lg-8">
                            <div class="am-form-group">
                                <div class="am-btn-toolbar">
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a href="<?php echo U('Privilege/editPrivilege');?>" type="button" class="am-btn am-btn-default am-btn-success"><span class="am-icon-plus"></span> 新增</a>
                                        <button type="button" class="am-btn am-btn-default am-btn-danger" onclick="javascript:recycle('chkbId', '确认删除?! 删除后无法恢复!', true)">
                                            <span class="am-icon-trash-o"></span> 批量删除
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="am-u-sm-12">
                            <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black " id="example-r">
                                <thead>
                                    <tr>
                                        <th><input class="check-all" type="checkbox"></th>
                                        <th>序号</th>
                                        <th>权限名称</th>
                                        <th>模块名称</th>
                                        <th>控制器名称</th>
                                        <th>方法名称</th>
                                        <th>排序</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($privilegeData)): foreach($privilegeData as $key=>$v): ?><tr class="gradeX">
                                            <td><input class="ids" type="checkbox" name="chkbId" value="<?php echo ($v['id']); ?>"></td>
                                            <td><?php echo ($v['id']); ?></td>
                                            <td style="text-align:left;">
                                                <?php if($v['parent_id'] == 0): ?><b><?php echo ($v['pri_name']); ?></b>
                                                    <?php else: ?>
                                                    &nbsp;&nbsp;&nbsp;&nbsp; - <?php echo ($v['pri_name']); endif; ?>
                                            </td>
                                            <td><?php echo ($v['module_name']); ?></td>
                                            <td><?php echo ($v['controller_name']); ?></td>
                                            <td><?php echo ($v['action_name']); ?></td>
                                            <td><?php echo ($v['sort']); ?></td>

                                            <td class="f-14">
                                                <div class="tpl-table-black-operation">
                                                    <a href="<?php echo U('Privilege/editPrivilege',array('id'=>$v['id']));?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                    <a href="javascript:void(0)" onclick="javascript:recycle(<?php echo ($v['id']); ?>, '确认删除?!此步骤无法恢复!', true)" class="tpl-table-black-operation-del">
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                </div>
                                            </td>
                                        </tr><?php endforeach; endif; ?>
                                </tbody>
                            </table>

                            <?php if(empty($privilegeData)): ?><h4>aOh! 没有相关内容!</h4><?php endif; ?>
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