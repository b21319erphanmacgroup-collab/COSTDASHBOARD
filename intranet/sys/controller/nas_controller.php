<?php
	/* **********************************
	*************************************** */
	session_start();
	/*---------------------------------------------*/
	$get_memberID	= $_GET['memberID']; 
	/*---------------------------------------------*/
// 	require('../../sys/util/LoginInfomation.php');
// 	$LoginIn = new LoginInfomation();
// 	$LoginIn->GetLoginStatus();
	/*---------------------------------------------*/
?>
<?php
	/* DB설정 -------------------------------------*/
	include "../../sys/inc/dbcon.inc";
	/* 스마티 설정---------------------------------*/
	include "../../../SmartyConfig.php";
	/* 로직클래스----------------------------------*/
	include "../../sys/model/NasLogic.php";
	/*---------------------------------------------*/
	require_once($SmartyClassPath);
	/*---------------------------------------------*/
    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;
	/*---------------------------------------------*/
	$GoLogic = new NasLogic($smarty);
	/*---------------------------------------------*/
	if($ActionMode=="nasMain"){		//
		$GoLogic->nasMain_page();

	}else if($ActionMode=="nas_manage_list"){		//
		$GoLogic->Nas_manage_list();
	
	}else if($ActionMode=="queryMode"){		//
		$GoLogic->QueryMode();
	
	}else if($ActionMode=="executeDB"){		//
		$GoLogic->ExecuteDB();
	
	}else if($ActionMode=="nas_person_insertUpdate"){		//
		$GoLogic->Nas_person_insertUpdate();
		
	}else if($ActionMode=="return_Data"){		//
		$GoLogic->Return_Data();
		
	}else{
		$msg = "ActionMode가 불명확합니다.";
		$msg=trim(ICONV("UTF-8","EUC-KR",$msg));
		echo $msg;
	}
?>
