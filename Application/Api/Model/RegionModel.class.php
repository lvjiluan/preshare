<?php
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
namespace Api\Model;
use Think\Model;

class RegionModel extends Model {
    
    public function getRegionNameById($id = 0){
        $where['id'] = $id;
        $regionName = $this->where($where)->getField('region_name');
        return $regionName;
    }

    //update by yangchunfu 
    public function getRegionNameByParentId(){
    	$where = array();
    	$parent_id = I('parent_id',-1,'intval');
    	if (!empty($parent_id) && $parent_id != -1) {
    		$where['parent_id'] = array('eq',$parent_id);
    	} else {
    		$where['parent_id'] = array('eq',1);
    	}
    	//获取缓存中的省市县数据
    	$citys = S('parent_id'.$parent_id);
    	if (!$citys) {
    		$where['is_display'] = array('eq',0);
    		$res = M('Region')->where($where)->field('id,region_name')->order('id,region_order')->select();
    		S('parent_id'.$parent_id,$res);
    	} else {
    		$res = $citys;
    	}

    	return $res;
    }

}