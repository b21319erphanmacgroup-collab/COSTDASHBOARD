<?
	require('./sys/util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatus();

/*
	include "./sys/inc/dbcon.inc";
	require('./sys/model/MainLogic.php');
	$SmartyMVC = new MainLogic();
	$SmartyMVC->MainHomeProcess();
*/	
	extract($_GET);
	$connectFlag = $_GET['connectFlag'];//웹브라우저를 통한 일반사용자의 접근(connectFlag=web)

	include "./sys/inc/dbcon.inc";
	
	//http://erp.hanmaceng.co.kr/intranet/main_home.php?memberID=T03225&connectFlag=web
	
	require('./sys/model/MainLogic.php');

	$SmartyMVC = new MainLogic();
		
	if($ActionMode == 'LoginInfo'){
		$SmartyMVC->LoginInfo();
	}elseif($ActionMode == 'ApprovalCount'){
		$SmartyMVC->ApprovalCount();
	}else{
		$SmartyMVC->MainHomeProcess();
	}
?>