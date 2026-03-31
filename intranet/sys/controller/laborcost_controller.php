<?	
	/***************************************
	* 권한설정
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH
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
	include "../model/laborcost_logic.php";
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new LaborCostLogic($smarty);

	if($ActionMode=="Detail"){		//상세보기
		$CurrentLogic->DetailView(); 
	}
	else if($ActionMode=="View"){		//상세보기
		$CurrentLogic->View(); 
	}
	else{							//전체보기
		$CurrentLogic->Loading();	
	}
?>
