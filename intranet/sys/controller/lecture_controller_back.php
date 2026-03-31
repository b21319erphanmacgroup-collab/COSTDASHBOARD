<?	

	/***************************************
	* 기술자료,경제특강,기획특강 
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/
session_start();
	if($_SESSION['memberID']=="")
	{
		$_SESSION['memberID']=$memberID;
	}else{
		$memberID=$_SESSION['memberID'];
	}
	require('../util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatus();

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/lecture_logic.php";
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new LectureLogic($smarty);

	if($ActionMode=="insert_page")																	//기술자료 / 경제특강 / 기획특강 등록 페이지
		$CurrentLogic->InsertPage();
	else if($ActionMode=="insert")																	//기술자료 / 경제특강 / 기획특강 등록
		$CurrentLogic->InsertAction();
	else if($ActionMode=="update_page" or $ActionMode=="delete_page")			//기술자료 / 경제특강 / 기획특강 읽기 / 수정 페이지
		$CurrentLogic->UpdateReadPage();
	else if($ActionMode=="update")																	//기술자료 / 경제특강 / 기획특강 수정
		$CurrentLogic->UpdateAction();
	else if($ActionMode=="delete")																	//기술자료 / 경제특강 / 기획특강 수정
		$CurrentLogic->DeleteAction();
	else if($ActionMode=="find_page")
		$CurrentLogic->View();																			//기술자료 / 경제특강 / 기획특강 LIST
	else
		$CurrentLogic->View();

?>
