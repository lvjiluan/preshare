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
                        <div class="widget-title  am-cf">用户列表</div>

                    </div>
                    <div class="widget-body  am-fr">

                        <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                            <div class="am-form-group">
                                <div class="am-btn-toolbar">
                                    <div class="am-btn-group am-btn-group-xs">
                                        <!--<button type="button" class="am-btn am-btn-default am-btn-danger" onclick="recycle('chkbId', '确认删除?! 删除后无法恢复!', true)">-->
                                            <!--<span class="am-icon-trash-o"></span> 批量删除</button>-->
                                       <!--<a type="button" class="am-btn am-btn-default am-btn-success" href="<?php echo U('Admin/User/user_export', array('type' => $user_type));?>"><span></span> 导出为excel</a>-->

                                    </div>
                                </div>
                            </div>
                        </div>
                        <form action="/index.php/Admin/User/listUser/type/0" method="get">
                            <div class="am-u-sm-12 am-u-md-6 am-u-lg-3">
                                <div class="am-form-group tpl-table-list-select">
                                    <!--<select name="is_auth" id="is_auth" data-am-selected="{}">-->
                                        <!--<option value="-1">请选择验证状态</option>-->
                                        <!--<option value="0">未通过</option>-->
                                        <!--<option value="1">已通过</option>-->
                                    <!--</select>-->
                                    <!--<?php if(is_auth != -1): ?>-->
                                        <!--<script>-->
                                            <!--$('#is_auth').val('<?php echo ($is_auth); ?>');-->
                                        <!--</script>-->
                                    <!--<?php endif; ?>-->
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-12 am-u-lg-3">
                                <div class="am-input-group am-input-group-sm tpl-form-border-form cl-p">
                                    <input type="text" class="am-form-field" name="keyword" placeholder="请输入用户账号/用户名" value="<?php echo ($keyword); ?>">
                                    <span class="am-input-group-btn">
                                        <button class="am-btn  am-btn-default am-btn-success tpl-table-list-field am-icon-search" type="submit"></button>
                                        <a class="am-btn  am-btn-default am-btn-danger tpl-table-list-field am-icon-refresh" type="button" href="/index.php/Admin/User/listUser"></a>
                                    </span>
                                </div>
                            </div>
                            <input name="p" value="1" type="hidden"/>
                        </form>

                        <div class="am-u-sm-12">
                            <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black " id="example-r">
                                <thead>
                                    <tr>
                                        <th width="60">编号</th>
                                        <th width="11%" >会员手机</th>
                                        <th width="11%" >用户名</th>
                                        <th width="11%" >昵称</th>
                                        <th width="11%" >公司名称</th>
                                        <th width="17%" class="align-center">注册时间</th>
                                        <th width="10%" class="align-center">状态</th>
                                        <th >操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(is_array($userslist)): foreach($userslist as $k=>$v): ?><tr class="gradeX">
                                            <!--<td><?php echo pageNumber(15, $k+1);?></td>-->
                                            <td><?php echo ($v['user_id']); ?></td>
                                            <td><?php echo ($v['mobile']); ?></td>
                                            <td><?php echo ($v['user_name']); ?></td>
                                            <td><?php echo ($v['nickname']); ?></td>
                                            <td><?php echo ($v['company']); ?></td>
                                            <td class="align-center"><?php echo time_format($v['register_time']);?></td>
                                            <td class="align-center">
                                                <?php echo show_disabled($v['disabled']);?>
                                            </td>
                                            <td class="f-14">
                                                <div class="tpl-table-black-operation">
                                                    <!--<?php if($v['disabled'] == 0): ?>-->
                                                        <!--<a title="点击启用" href="javascript:void(0)" onclick="changeDisabled(<?php echo ($v['user_id']); ?>);">-->
                                                            <!--启用-->
                                                        <!--</a>-->
                                                    <!--<?php endif; ?>-->
                                                    <!--<?php if($v['disabled'] == 1): ?>-->
                                                        <!--<a title="点击禁用" href="javascript:void(0)" class="tpl-table-black-operation-del" onclick="changeDisabled('<?php echo ($v["user_id"]); ?>');">-->
                                                            <!--禁用-->
                                                        <!--</a>-->
                                                    <!--<?php endif; ?>-->
                                                    <a href="<?php echo U('User/userDetail', array('user_id'=>$v['user_id']));?>"> 用户详情 </a>
                                                    <!--<?php if($user_type == 1): ?>-->
                                                        <!--<a href="<?php echo U('Resume/listHrResume', array('user_id'=>$v['user_id']));?>"> 人才库 </a>-->
                                                        <!--<a href="<?php echo U('CompanyInfo/companyInfoDetail', array('user_id'=>$v['user_id']));?>"> 公司详情 </a>-->
                                                        <!--<?php else: ?>-->
                                                        <!--<a href="<?php echo U('Resume/getResumeDetail', array('user_id'=>$v['user_id']));?>"> 简历详情 </a>-->
                                                    <!--<?php endif; ?>-->
                                                </div>
                                            </td>
                                        </tr><?php endforeach; endif; ?>

                                <!-- more data -->
                                </tbody>
                            </table>

                            <?php if(empty($userslist)): ?><h4>aOh! 没有相关内容!</h4><?php endif; ?>
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
        function changeDisabled(user_id){
            $.ajax({
                url : "<?php echo U('User/changeDisabled');?>",
                type : "POST",
                dataType : "json",
                data : {
                    "user_id" : user_id,
                },
                success : function(data){
                    toastr(data.info);
                    if(data.status == 1){
                        setInterval('reload()', 2000);
                    }
                }
            });
        }
        function reload() {
            location.reload();
        }
    </script>

</body>
</html>