<?php
namespace Mobile\Controller;
use Common\Controller\ApiUserCommonController;

class OrderController extends ApiUserCommonController{

    function add_order(){
            $spec_material=C('MATERIAL');
            $spec_width=C('WIDTH');
            $spec_height=C('HEIGHT');
            $address_id = I("address_id"); // 商品id
            $goods_id = I("goods_id"); // 商品id
            $goods_num = I("goods_num",1);// 商品数量
            $width = I("width");// 商品规格
            $height = I("height");// 商品规格
            $material = I("material");// 商品材质
            $user_note = I("user_note");// 留言
            $where['goods_id']=$goods_id;
            $where['width']=$width;
            $where['height']=$height;
            $where['material']=$material;

            $goodsInfo=M('Goods g')
                ->where($where)
                ->field('g.goods_id,g.width,g.height,g.material,g.price,g.grade,g.explain,g.store_count,c.cat_name,c.manufacturer')
                ->join('__GOODS_CATEGORY__ as c on g.goods_cat_id = c.id', 'LEFT')
                ->find();
        if(!$address_id){
            $this->apiReturn(V(0,'请先填写收货地址'));
        }
            if(!$goodsInfo){
                $this->apiReturn(V(0,'参数错误'));
            }
            if($goods_num>$goodsInfo['store_count']){
                $this->apiReturn(V(0,'库存不足'));
            }
            $goods_price=$goodsInfo['price']*$goods_num;

//            $order_count = M('Order')->where("user_id= UID and order_sn like '".date('Ymd')."%'")->count(); // 查找购物车商品总数量
//
//            if($order_count >= 50)
//                $this->apiReturn(V(0,'一天只能下50个订单'));
            // 0插入订单 order
            $address = M('UserAddress')->where("address_id = $address_id")->find();

            $data = array(
                'order_sn'         => date('YmdHis').rand(1000,9999), // 订单编号
                'user_id'          =>UID, // 用户id
                'consignee'        =>$address['consignee'], // 收货人
                'province'         =>$address['province'],//'省份id',
                'city'             =>$address['city'],//'城市id',
                'district'         =>$address['district'],//'县',
                'address'          =>$address['address'],//'详细地址',
                'mobile'           =>$address['mobile'],//'手机',
                'goods_price'      =>$goodsInfo['price'],//'商品价格',
                'total_amount'     =>$goods_price,// 订单总额
                'add_time'         =>time(), // 下单时间
                'user_note'        =>$user_note,
                'goods_id'         =>$goods_id,
                'goods_num'        =>$goods_num,
                'height'           =>$height,
                'width'            =>$width,
                'material'         =>$material,
                'cat_name'         =>$goodsInfo['cat_name'],
                'width_name'       =>$spec_width[$width],
                'height_name'      =>$spec_height[$height],
                'material_name'    =>$spec_material[$material],
                'manufacturer'     =>$goodsInfo['manufacturer'],
                'spec'             =>$spec_width[$width]."*".$spec_height[$height],
            );

            $order_id = M("Order")->add($data);
            //M('Goods')->where("goods_id = ".$goods_id)->setDec('store_count',$goods_num);
            if($order_id){
                $this->apiReturn(V(1,'提交成功'));
            }else{
                $this->apiReturn(V(0,'请填写正确信息'));
            }
    }

    function add_order_my(){
        if(IS_POST){
            $goods_num = I("goods_num");// 商品数量
            $spec = I("spec");// 商品规格
            $material = I("material_name");// 商品材质
            $user_note = I("user_note");// 留言
            $cat_name=I('cat_name');
            $consignee=I('consignee');
            $mobile=I('mobile');

            $data = array(
                'order_sn'         => date('YmdHis').rand(1000,9999), // 订单编号
                'user_id'          =>UID, // 用户id
                'consignee'        =>$consignee, // 收货人
                'mobile'           =>$mobile,//'手机',
                'add_time'         =>time(), // 下单时间
                'user_note'        =>$user_note,
                'goods_num'        =>$goods_num,
                'cat_name'         =>$cat_name,
                'material_name'    =>$material,
                'spec'             =>$spec,
            );

            $order_id = M("OrderCustomize")->add($data);
            if($order_id){
                $this->apiReturn(V(1,'提交成功'));
            }else{
                $this->apiReturn(V(0,'提交失败'));
            }
        }else{
            $where['is_show']=1;
            $category=M('GoodsCategory')->where($where)->select();
            $this->assign('category',$category);
            $this->display();
        }

    }

    function index(){
        $spec_material=C('MATERIAL');
        $spec_width=C('WIDTH');
        $spec_height=C('HEIGHT');
        $address=M('UserAddress')->where(array('user_id'=>UID))->find();
        $goods_id = I("goods_id"); // 商品id
        $goods_num = I("goods_num",1);// 商品数量
        $where['goods_id']=$goods_id;
        $goodsInfo=M('Goods g')
            ->where($where)
            ->field('g.goods_id,g.width,g.height,g.material,g.price,g.grade,g.explain,g.store_count,c.cat_name,c.manufacturer')
            ->join('__GOODS_CATEGORY__ as c on g.goods_cat_id = c.id', 'LEFT')
            ->find();
        $goodsInfo['price']=fen_to_yuan($goodsInfo['price']);
        $goodsInfo['height_name']=$spec_height[$goodsInfo['height']];
        $goodsInfo['width_name']=$spec_width[$goodsInfo['width']];
        $goodsInfo['material_name']=$spec_material[$goodsInfo['material']];
        $goods_price=$goodsInfo['price']*$goods_num;
        $this->assign('goods_num', $goods_num);
        $this->assign('goods_price', $goods_price);
        $this->assign('address',$address);
        $this->assign('goodsInfo',$goodsInfo);
        $this->display();
    }

    //查询该用户是否填写收货地址
    function checkAddress(){
        $userInfo=M('UserAddress')->where(array('user_id'=>UID))->find();
        if($userInfo){
            $this->apiReturn(V(1,'收货地址',$userInfo));
        }else{
            $this->apiReturn(V(0,'未填写收货地址'));
        }
    }

}