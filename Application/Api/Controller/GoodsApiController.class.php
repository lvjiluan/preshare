<?php
namespace Api\Controller;
use Common\Controller\ApiCommonController;

class GoodsApiController extends ApiCommonController{
    public function __construct() {
        parent::__construct();
    }

    //搜索条件
    public function condition(){
        //品名
        $spec['cat']=M('GoodsCategory')->where(array('is_show'=>1))->order('sort asc')->select();
        //材质
        $spec['material'] = returnArrData(C('MATERIAL'));
        //规格
        $spec['width'] = returnArrData(C('WIDTH'));
        $spec['height'] = returnArrData(C('HEIGHT'));
        $this->apiReturn(V(1,'查询成功',$spec));
    }

    //产品列表
    public function goodsList(){
        $p=I('p',1);
        $cat=I('cat_id');
        $material=I('material');
        $width=I('width');
        $height=I('height');
        $order=I('order',1);  //1价格降序  2价格升序
        $where['display']=1;
        if($cat){
            $where['goods_cat_id']=$cat;
        }
        if($material){
            $where['material']=$material;
        }
        if($width){
            $where['width']=$width;
        }
        if($height){
            $where['height']=$height;
        }
        if($order){
            if($order==2){
                $order="price desc";
            }else{
                $order="price asc";
            }
        }
        $spec_material=C('MATERIAL');
        $spec_width=C('WIDTH');
        $spec_height=C('HEIGHT');
        $goods_list=M('Goods g')
            ->where($where)
            ->join('__GOODS_CATEGORY__ as c on g.goods_cat_id = c.id', 'LEFT')
            ->field('c.cat_name,g.goods_id,g.material,g.height,g.width,g.thumb_img,g.price')
            ->page($p,7)
            ->order('goods_id desc')
            ->order($order)
            ->select();
        foreach($goods_list as $key=>$v){
            $goods_list[$key]['thumb_img']=WEB_URL.$v['thumb_img'];
            $goods_list[$key]['price']=fen_to_yuan($v['price']);
            $goods_list[$key]['material']=$spec_material[$v['material']];
            $goods_list[$key]['width']=$spec_width[$v['width']];
            $goods_list[$key]['height']=$spec_height[$v['height']];
        }
        $this->apiReturn(V(1,'查询成功',$goods_list));

    }

    //产品详情
    public function goods_detail(){
        $goods_id=I('goods_id','');
        if($goods_id==''){
            $this->apiReturn(V(0,'参数错误'));
        }
        $where['goods_id']=$goods_id;
        $info=M('Goods g')
            ->where($where)
            ->field('g.goods_id,g.width,g.height,g.material,g.price,g.grade,g.explain,g.store_count,c.cat_name,c.manufacturer')
            ->join('__GOODS_CATEGORY__ as c on g.goods_cat_id = c.id', 'LEFT')
            ->find();
        $spec_material=C('MATERIAL');
        $spec_width=C('WIDTH');
        $spec_height=C('HEIGHT');
        //$info['thumb_img']=WEB_URL.$info['thumb_img'];
        $info['price']=fen_to_yuan($info['price']);
        $info['material']=$spec_material[$info['material']];
        $info['width']=$spec_width[$info['width']];
        $info['height']=$spec_height[$info['height']];
        if($info['grade']){
            $info['grade']=$info['grade']."等品";
        }
        $this->apiReturn(V(1,'产品详情',$info));

    }



}
