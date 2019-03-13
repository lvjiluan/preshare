<?php
namespace Mobile\Controller;
use Common\Controller\ApiUserCommonController;

class NewsController extends ApiUserCommonController{

    public function index(){
        $p=I('p',1);
        $where['display']=1;
        $news=M('Article')->where($where)->field('article_id,thumb_img,title,introduce')->page($p,6)->order('addtime desc')->select();
        $this->assign('list',$news);
       // p($news);die;
        $count=M('Article')->where($where)->field('article_id,thumb_img,title,introduce')->order('addtime desc')->count();
        $page_count=round($count/1);
        $this->assign('page_count',$page_count);
        $this->assign('page',$p);
        $this->display();
    }

    //新闻
    public function news(){
        $p=I('p',2);
        $where['display']=1;
        $news=M('Article')->where($where)->field('article_id,thumb_img,title,introduce')->page($p,6)->order('addtime desc')->select();

        // p(M('Article')->_sql());die;
        foreach ($news as $k=>$v){
            if($news[$k]['thumb_img']){
                $news[$k]['thumb_img']=WEB_URL.$news[$k]['thumb_img'];
            }
        }
        $this->apiReturn(V(1,'查询成功',$news));
    }

    //新闻详情
    public function detail(){
        $id=I('id');
        $where['article_id']=$id;
        $news=M('Article')->where($where)->field('article_id,title,introduce,content,addtime')->find();
        $news['addtime']=date("Y-m-d H:i:s",$news['addtime']);

        $this->assign('info',$news);
        $this->display();
    }

    //分享
    public function share(){
        $this->display();
    }

    //加载更多新闻
    public function ajax_more(){
        $p=I('p',2);
        $where['display']=1;
        $count=M('Article')->where($where)->field('article_id,thumb_img,title,introduce')->order('addtime desc')->count();
        $news=M('Article')->where($where)->field('article_id,thumb_img,title,introduce')->page($p,6)->order('addtime desc')->select();
        $page_count=round($count/6);
        $this->assign('page_count',$page_count);
        $this->assign('page',$p);
        $this->assign('list',$news);
        $this->display();


    }

}