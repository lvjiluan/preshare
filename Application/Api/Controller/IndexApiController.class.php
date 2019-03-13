<?php
namespace Api\Controller;
use Common\Controller\ApiCommonController;

class IndexApiController extends ApiCommonController{
    public function __construct() {
        parent::__construct();
    }

    //首页接口
    public function index(){
        //获取轮播图
        $banner=M('Ad')->where(array('position_id'=>1,'display'=>1))->field('ad_id,content')->order('sort asc,ad_id desc')->limit(5)->select();
        foreach($banner as $k=>$v){
            $banner[$k]['content']=WEB_URL.$v['content'];
        }
        $spec_material=C('MATERIAL');
        $spec_width=C('WIDTH');
        $spec_height=C('HEIGHT');
        //获取产品
        $goods= M('Goods')
            ->alias('g')
            ->join('__GOODS_CATEGORY__ as c on g.goods_cat_id = c.id', 'LEFT')
            ->field('c.cat_name,g.goods_id,g.material,g.height,g.width,g.thumb_img,g.price')
            ->order('g.goods_id desc')
            ->where(array('display'=>1))
            ->limit(5)
            ->select();
        foreach($goods as $key=>$v){
            $goods[$key]['thumb_img']=WEB_URL.$v['thumb_img'];
            $goods[$key]['price']=fen_to_yuan($v['price']);
            $goods[$key]['material']=$spec_material[$v['material']];
            $goods[$key]['width']=$spec_width[$v['width']];
            $goods[$key]['height']=$spec_height[$v['height']];
        }
        $list['banner']=$banner;
        $list['goods']=$goods;
        $this->apiReturn(V(1,'查询成功',$list));
    }

    //新闻
    public function news(){
        $p=I('p',1);
        $where['display']=1;
        $news=M('Article')->where($where)->field('article_id,thumb_img,title,introduce')->page($p,5)->order('addtime desc')->select();
       // p(M('Article')->_sql());die;
        foreach ($news as $k=>$v){
            if($news[$k]['thumb_img']){
                $news[$k]['thumb_img']=WEB_URL.$news[$k]['thumb_img'];
            }
        }
        $this->apiReturn(V(1,'查询成功',$news));
    }
    
    //新闻详情
    public function news_detail(){
        $id=I('id');
        if(!$id){
            $this->apiReturn(V(1,'参数错误'));
        }
        $where['id']=$id;
        $news=M('Article')->where($where)->field('article_id,title,introduce,content,addtime')->find();
        $news['addtime']=date("Y-m-d H:i:s",$news['addtime']);
        $this->apiReturn(V(1,'查询成功',$news));
    }

}
