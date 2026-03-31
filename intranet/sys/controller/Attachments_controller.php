<?
	session_start();
	require('../util/LoginInfomation.php');
	include "../../../SmartyConfig.php";
	include "../model/Attachments_logic.php";

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	
		$CurrentLogic=new Attachments_logic($smarty);
		$ActionMode=$_REQUEST['ActionMode'];

		if($ActionMode == "FileInput"){
			$CurrentLogic->FileInput();				//첨부파일 업로드 화면
		}elseif($ActionMode == "FileUploadAction"){
			$CurrentLogic->FileUploadAction();		//첨부파일 업로드 실행
		}elseif($ActionMode == "AttachfileDel"){
			$CurrentLogic->AttachfileDel();			//첨부파일 삭제
		}elseif($ActionMode == "test"){
			$CurrentLogic->test();				//test
		}
	//}
?>
