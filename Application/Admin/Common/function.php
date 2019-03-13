<?php
// 获取数据的状态操作
function show_dispaly($display) {
    switch ($display) {
        case 0  : return    '<span class = "display_0">隐藏</span>';   break;
        case 1  : return    '<span class = "display_1">显示</span>';   break;
        default : return    false;      break;
    }
}

// 获取数据的状态操作
function show_disabled($disabled) {
    switch ($disabled) {
        case 0  : return    '<span class = "disabled_0">禁用</span>';   break;
        case 1  : return    '<span class = "disabled_1">正常</span>';   break;
        default : return    false;      break;
    }
}

//展示是否是最佳答案
function show_optimum($isOptimum) {
    switch ($isOptimum) {
        case 0  : return    '<span class = "disabled_0" style="color: #ff230e">否</span>';   break;
        case 1  : return    '<span class = "disabled_1" style="color: #3eff17">是</span>';   break;
        default : return    false;      break;
    }
}



// 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')){
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    }else{
        $value  =   $array;
    }
    return $value;
}

/**
 * 获取配置的分组
 * @param string $group 配置分组
 * @return string
 */
function get_config_group($group = 0){
    $list = C('CONFIG_GROUP_LIST');
    return $group?$list[$group]:'';
}

/**判断是否为超级管理员
 * @return bool
 */
function is_admin(){
    if(session('admin_info')&&session('admin_info')['is_admin']){
        return true;
    }else{
        return false;
    }

}

/**
 * 通用分页处理函数
 * @param Int $count 总条数
 * @param int $page_size 分页大小
 * @return Array  ['page']分页数据  ['limit']查询调用的limit条件
 */
function get_page($count, $page_size=0){
    if ($page_size == 0) $page_size = C('PAGE_SIZE');
    $page = new \Think\Page($count, $page_size);
    $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
    $show = $page->show();
    $limit = $page->firstRow.','.$page->listRows;
    return array('page'=>$show,'limit'=>$limit);
}

// 审核状态
function withdraw_audit_status($disabled) {
    switch ($disabled) {
        case 0  : return    '<span style="color: brown">待处理</span>';   break;
        case 1  : return    '<span style="color: green">已通过</span>';   break;
        case 2  : return    '<span style="color: #ff9c03">已驳回</span>';   break;
        case 3  : return    '<span style="color: #ff0003">已打款</span>'; break;
        case 4  : return    '<span style="color: #ff0003">未打款</span>'; break;
        default : return    false;      break;
    }
}

/**
 * 管理员操作记录日志
 * @param $log_sql 操作执行的SQL语句
 * @param $log_info 记录信息
 */
function admin_log($log_info = '', $log_sql = '') {
    $data['admin_id'] = session('admin_id');
    $data['client_ip'] = get_client_ip();
    $data['dateline'] = NOW_TIME;
    $data['uri'] = __ACTION__;
    $data['action_name'] = __CONTROLLER__;
    $data['method_name'] = __MODULE__;
    $data['content'] = $log_info;
    M('AdminLog')->add($data);
}

// 获取数据的状态操作
function show_meetinghide($hide) {
    switch ($hide) {
        case 1  : return    '<span class = "hide_0">显示</span>';   break;
        case 0  : return    '<span class = "hide_1">隐藏</span>';   break;
        default : return    false;      break;
    }
}