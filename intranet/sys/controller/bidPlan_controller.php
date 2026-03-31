<?php
	session_start();
	session_cache_limiter('private');

    header('X-UA-Compatible: IE=edge'); 
	//	include "../inc/auth.php";
	//DB설정 //////////////////////////////////////
	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../../sys/model/BidPlanLogic.php";

	require_once($SmartyClassPath);

    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;

	$GoLogic = new BidPlanLogic($smarty);

	if($ActionMode=="pageList"){		//리스트 
		$GoLogic->PageList();


	}else if($ActionMode=="PopPage01"){		//
		$GoLogic->PopPage01();

	}else if($ActionMode=="PopPage02"){		//
		$GoLogic->PopPage02();

	}else if($ActionMode=="viewPage"){		//상세보기
		$GoLogic->ViewPage();

	}else if($ActionMode=="insertPage"){	// 입찰계획 -> 페이지이동 : 입력페이지(자료등록) 
		$GoLogic->InsertPage();

	}else if($ActionMode=="updatePage"){	// 입찰계획 -> 페이지이동 : 수정페이지(자료수정)
		$GoLogic->UpdatePage();

	}else if($ActionMode=="insertDB"){	// 입찰계획 -> DB실행 : 자료입력 
		$GoLogic->InsertDB();

	}else if($ActionMode=="updateDB"){	// 입찰계획 -> DB실행 : 자료수정
		$GoLogic->UpdateDB();

	}else if($ActionMode=="confirmPw"){	// 입찰계획 -> DB실행 : 자료입력 
		$GoLogic->ConfirmPw();

	}else if($ActionMode=="deleteDB"){	// 입찰계획 -> DB실행 : 자료삭제 
		$GoLogic->DeleteDB();

	}else if($ActionMode=="valueTest"){	// 입력값 확인
		$GoLogic->ValueTest();

	}else{
		
	}
?>


