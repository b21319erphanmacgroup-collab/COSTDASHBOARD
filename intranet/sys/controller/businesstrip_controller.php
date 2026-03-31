<?	
	/***************************************
	* 출금전표
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
	$LoginIn->GetLoginStatusPop();

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/businesstrip_logic.php";
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new BusinessTripLogic($smarty);
	
	if($ActionMode=="insert_page")//출금전표 작성 페이지 이동
		$CurrentLogic->InsertPage();
	else if($ActionMode=="insert")//출금전표 저장(insert)
		$CurrentLogic->InsertAction();	
	else if($ActionMode=="update_page" or $ActionMode=="delete_page")//출금전표 수정/읽기 페이지 이동
		$CurrentLogic->UpdateReadPage();
	else if($ActionMode=="update")//출금전표 저장(update)
		$CurrentLogic->UpdateAction();
	else if($ActionMode=="delete")//출금전표 삭제(delete)
		$CurrentLogic->DeleteAction();
	else if($ActionMode=="projectcode_search")//프로젝트코드 검색 페이지 이동
		$CurrentLogic->ProjectCodeSearch();
	else if($ActionMode=="account_search")//계정과목/항목 검색 페이지 이동
		$CurrentLogic->AccountTitleSearch();
	else if($ActionMode=="vender_search")//거래처/사원 검색 페이지 이동
		$CurrentLogic->VenderCodeSearch();
	else if($ActionMode=="member_search")//청구자(직원) 검색 페이지 이동
		$CurrentLogic->	MemberCodeSearch();
	else if($ActionMode=="vender_edit")//거래처 추가 페이지 이동
		$CurrentLogic->	EditVenderLogic();
	else if($ActionMode=="vender_save")//거래처 저장
		$CurrentLogic->	SaveVenderLogic();
	else
		$CurrentLogic->View();//출금전표 list 이동

?>
