<?php
	session_start();

	function deny($msg){
		echo "<script>alert(". json_encode($msg)."); window.close();</script>";
		exit;
	}

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		deny('직접 URL 접근은 허용되지 않습니다.');
	}

	// ✅ MemberNo는 POST로만 받기
	$memberNo = isset($_POST['MemberNo']) ? trim($_POST['MemberNo']) : '';
	$ActionMode = isset($_POST['ActionMode']) ? trim($_POST['ActionMode']) : '';
	if ($memberNo === '') {
		deny('잘못된 요청입니다.');
	}

	$_GET['MemberNo'] = $memberNo;
	$_REQUEST['MemberNo'] = $memberNo;

	include "../../../SmartyConfig.php";
	include "../model/ManHour2.logic.php";
	
	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
	$smarty->compile_dir	=$SmartyClass_CompileDir;
	$smarty->config_dir		=$SmartyClass_ConfigDir;
	$smarty->cache_dir		=$SmartyClass_CacheDir;

	$smarty->assign('Controller','ManHour2_controller.php');
	//echo 'Equipment_controller.php';

	$CurrentLogic=new ManhourLogic($smarty);

	if($ActionMode == 'Manhour_Project'){
		$CurrentLogic->Manhour_Project();
	}elseif($ActionMode == 'Manhour_Myproject'){
		$CurrentLogic->Manhour_Myproject();
	}elseif($ActionMode == 'Manhour_Fullproject'){
        $CurrentLogic->Manhour_Fullproject();
    }else{
		$CurrentLogic->Manhour_my();	//개인 M-H 메인 페이지
	}
?>