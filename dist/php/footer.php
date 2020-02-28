<?php
$smartyTpl->assign('op', $op);
$smartyTpl->assign('g2p', $g2p);
$smartyTpl->assign('error', $error);
$smartyTpl->assign('page_title', $page_title);
$smartyTpl->assign('content', $content);
$smartyTpl->assign('_KYC_URL_ROOT', $_KYC_URL_ROOT);
$smartyTpl->display($theme);