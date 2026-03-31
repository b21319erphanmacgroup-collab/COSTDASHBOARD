<?
	session_start();
	require('../util/LoginInfomation.php');
	include "../../../SmartyConfig.php";
	include "../model/CommonLogic.php";
	include "../inc/function_intranet.php";
	//-----------------------------------------------------------------
	require_once($SmartyClassPath);
	extract($_REQUEST);
	//-----------------------------------------------------------------
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;
    //-----------------------------------------------------------------
	$CommonLogic = new CommonLogic($smarty);
	//-----------------------------------------------------------------

	if($ActionMode == "")
		$ActionMode=$_REQUEST['ActionMode'];
	
	if($ActionMode == "")	 echo "no action";
	
	//-----------------------------------------------------------------
	$itemkey1 = $_REQUEST['itemkey1']==""?"":$_REQUEST['itemkey1'];
	$itemkey2 = $_REQUEST['itemkey2']==""?"":$_REQUEST['itemkey2'];
	$itemkey3 = $_REQUEST['itemkey3']==""?"":$_REQUEST['itemkey3'];
	$itemkey4 = $_REQUEST['itemkey4']==""?"":$_REQUEST['itemkey4'];
	$itemkey5 = $_REQUEST['itemkey5']==""?"":$_REQUEST['itemkey5'];
	$returnType = $_REQUEST['returnType']==""?"":$_REQUEST['returnType'];
	$callback = $_REQUEST['callback']==""?"":$_REQUEST['callback'];
	//-----------------------------------------------------------------
	
	if($ActionMode=="popup"){
		// 선택
		switch($itemkey1)
		{
			//=========================================================================
			case "project":
				$CommonLogic->PageView();//페이지 이동
				break;
				
			//=========================================================================
			default:
				$CommonLogic->PageView();//페이지 이동
				break;
				
		}//switch

	}else if($ActionMode=="json"){//	

		// 선택
		switch($itemkey1)
		{
			//=========================================================================
			case "project":
			case "changeOption":
			case "getProjectInfoAll": //해당 프로젝트정보 가져오기 ALL
				
				$CommonLogic->ResultProsesure();//페이지 이동
				break;
			//=========================================================================
			default:
				break;
		
		}//switch
		
		
	}else if($ActionMode=="view"){//	
		// 선택
		switch($itemkey1)
		{
			//=========================================================================
			case "project_person_manage":
				//DivList_01 값
				//DivList_02 값
				//DivList_03 값
				$CommonLogic->PageView();//페이지 이동
				break;
		
				//=========================================================================
			default:
				break;
		
		}//switch
		

	}else if($ActionMode=="executeDB"){//	
		$CommonLogic->ExecuteDB();

		
	} //if
	//-----------------------------------------------------------------
		
?>
