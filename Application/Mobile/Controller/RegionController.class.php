<?php
namespace Mobile\Controller;
use Common\Controller\ApiCommonController;
/**
 * 客户管理API
 * create by
 */
class RegionController extends ApiCommonController {
    
    public function getRegionList(){
        $regionList = D('Region')->getRegionNameByParentId();
        $this->apiReturn(V(1, '区域列表', $regionList));
    }

    /*
    * 获取地区
    */
    public function getRegion(){
        $parent_id = I('get.parent_id');
        $selected = I('get.selected',0);
        $data = M('region')->where("parent_id=$parent_id")->select();
        echo M('region')->_sql();
        $html = '';
        if($data){
            foreach($data as $h){
                if($h['id'] == $selected){
                    $html .= "<option value='{$h['id']}' selected>{$h['region_name']}</option>";
                }
                $html .= "<option value='{$h['id']}'>{$h['region_name']}</option>";
            }
        }
        echo $html;
    }
}