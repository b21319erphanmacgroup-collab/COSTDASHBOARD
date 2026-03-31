<?php
	session_start();
	session_cache_limiter('private');

    header('X-UA-Compatible: IE=edge'); 
	//	include "../inc/auth.php";
	//DB설정 //////////////////////////////////////
	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../../sys/model/PmResisterLogic.php";

	require_once($SmartyClassPath);

    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;

	$GoLogic = new PmResisterLogic($smarty);

	if($ActionMode=="pageList"){		//리스트 
		$GoLogic->PageList();

	}else if($ActionMode=="pageList02"){	// 관리대장 -> 페이지이동 : 외주비 지급내역서
		$GoLogic->PageList02();

	}else if($ActionMode=="pageList03"){	// 관리대장 -> 페이지이동 : 수금내역 : 수금현황 리스트
		$GoLogic->PageList03();

	}else if($ActionMode=="pageList04"){	// 관리대장 -> 페이지이동 : 외주비 : 총괄내역 리스트
		$GoLogic->PageList04();

	}else if($ActionMode=="pageList05"){	// 관리대장 -> 페이지이동 : 수행상태
		$GoLogic->PageList05();

	}else if($ActionMode=="pageList06"){	// 관리대장 -> 페이지이동 : 관리대장 : 상세보기
		$GoLogic->PageList06();

	}else if($ActionMode=="pageList07"){	// 관리대장 -> 수금계획집계표
		$GoLogic->pageList07();

	}else if($ActionMode=="insertDB05"){	// 관리대장 -> 수행상태 변경 : DB실행
		$GoLogic->InsertDB05();

	}else if($ActionMode=="updatePage"){	// 관리대장 -> 수금계획 : 입력/수정
		$GoLogic->UpdatePage();




 
	}else if($ActionMode=="viewPage"){		//상세보기
		$GoLogic->ViewPage();

	}else if($ActionMode=="viewPage03"){	// 관리대장 -> 
		$GoLogic->ViewPage03();

	}else if($ActionMode=="insertPage"){	// 관리대장 -> 
		$GoLogic->InsertPage();



	}else if($ActionMode=="updatePage03"){	// 관리대장 -> 
		$GoLogic->UpdatePage03();

	}else if($ActionMode=="updateDB03"){	// 관리대장 -> 
		$GoLogic->UpdateDB03();

	}else if($ActionMode=="insertPage03"){	// 관리대장 -> 
		$GoLogic->InsertPage03();

	}else if($ActionMode=="insertDB03"){	// 관리대장 -> 
		$GoLogic->InsertDB03();

	}else if($ActionMode=="deleteDB03"){	// 관리대장 -> 
		$GoLogic->DeleteDB03();

	}else if($ActionMode=="insertDB"){	// 관리대장 -> 
		$GoLogic->InsertDB();

	}else if($ActionMode=="updateDB"){	// 관리대장 -> 
		$GoLogic->UpdateDB();

	}else if($ActionMode=="confirmPw"){	// 관리대장 -> 
		$GoLogic->ConfirmPw();

	}else if($ActionMode=="deleteDB"){	// 관리대장 -> 
		$GoLogic->DeleteDB();

	}else if($ActionMode=="valueTest"){	// 입력값 확인
		$GoLogic->ValueTest();

	}else{
		
	}
?>


