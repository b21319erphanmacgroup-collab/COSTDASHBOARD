<?
	/***************************************
	* 대화시스템 메인
	* ------------------------------------
	* 
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
	//$LoginIn->GetLoginStatus();

	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../../sys/model/conversation_logic_test.php";

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
	$smarty->compile_dir	= $SmartyClass_CompileDir;
	$smarty->config_dir		= $SmartyClass_ConfigDir;
	$smarty->cache_dir		= $SmartyClass_CacheDir;
	
	$CurrentLogic = new Conversation();
	if($ActionMode=="ConversationInsert")
		$CurrentLogic->ConversationInsert();
	else if($ActionMode=="ConversationUpload")
		$CurrentLogic->ConversationUpload();
	else if($ActionMode=="ConversationDelete")
		$CurrentLogic->ConversationDelete();
	else if($ActionMode=="ConversationView")
		$CurrentLogic->ConversationView();
	else
		$CurrentLogic->ConversationList();

?>
