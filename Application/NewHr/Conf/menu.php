<?php
$modules = array(
    'Hr' => array('label' => '个人资料', 'action' => '', 'items' => array(
        array('label' => '个人资料', 'action' => U('Hr/setHr'), 'class' => 'home'),
        array('label' => '公司资料', 'action' => U('Hr/setCompany'), 'class' => 'home'),
        )),
    'resume' => array('label' => '简历管理' , 'action' => '' , 'items' => array(
        array('label' => '简历列表', 'action' => U('Resume/listHrResume'), 'class' => 'resume'),
        array('label' => '上传简历', 'action' => U('Resume/editResume'), 'class' => 'resume'),
        array('label' => '搜索简历', 'action' => U('Resume/researchResume'), 'class' => 'resume'),
        array('label' => '悬赏中心', 'action' => U('Recruit/recruitList'), 'class' => 'reward'),
        array('label' => '申请发票', 'action' => U('Invoice/invoiceList'), 'class' => 'bill'),
        array('label' => '充值记录', 'action' => U('UserAccount/getAccount'), 'class' => 'recharge'),
        array('label' => '转账管理', 'action' => U('TransferAccount/getMyAccounts'), 'class' => 'recharge'),
        array('label' => '意见反馈', 'action' => U('Resume/editFeedBack'), 'class' => 'data')
        )),
    
);
return $modules;