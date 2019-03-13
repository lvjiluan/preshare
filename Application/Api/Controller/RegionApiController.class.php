<?php
namespace Api\Controller;
use Common\Controller\ApiCommonController;
/**
 * 客户管理API
 * create by
 */
class RegionApiController extends ApiCommonController {
    
    public function getRegionList(){
        $regionList = D('Region')->getRegionNameByParentId();
        $this->apiReturn(V(1, '区域列表', $regionList));
    }
}