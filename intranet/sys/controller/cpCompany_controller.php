<?php
	session_start();
	session_cache_limiter('private');

    header('X-UA-Compatible: IE=edge'); 
	//	include "../inc/auth.php";
	//DB설정 //////////////////////////////////////
	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../../sys/model/CpCompanyLogic.php";

	require_once($SmartyClassPath);

    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;

	$GoLogic = new CpCompanyLogic($smarty);

	if($ActionMode=="pageList"){		//리스트 
		$GoLogic->PageList();

	}else if($ActionMode=="pageList02"){	// 협력업체 -> 페이지이동 : 외주 계약현황
		$GoLogic->PageList02();

	}else if($ActionMode=="pageList03"){	// 협력업체 -> 페이지이동 : 특정업체 외주 평가현황 리스트
		$GoLogic->PageList03();

	}else if($ActionMode=="viewPage"){		//상세보기
		$GoLogic->ViewPage();

	}else if($ActionMode=="viewPage03"){	// 협력업체 -> 페이지이동 : 특정업체 외주 평가현황 상세보기
		$GoLogic->ViewPage03();

	}else if($ActionMode=="insertPage"){	// 협력업체 -> 페이지이동 : 입력/등록 페이지
		$GoLogic->InsertPage();

	}else if($ActionMode=="updatePage"){	// 협력업체 -> 페이지이동 : 수정페이지
		$GoLogic->UpdatePage();

	}else if($ActionMode=="updatePage03"){	// 협력업체 -> 페이지이동 : 특정업체 외주 평가현황 : 수정페이지
		$GoLogic->UpdatePage03();

	}else if($ActionMode=="updateDB03"){	// 협력업체 -> DB실행 : 자료수정 (특정업체 외주 평가현황)
		$GoLogic->UpdateDB03();

	}else if($ActionMode=="insertPage03"){	// 협력업체 -> 페이지이동 : 특정업체 외주 평가현황 : 입력/등록 페이지 :
		$GoLogic->InsertPage03();

	}else if($ActionMode=="insertDB03"){	// 협력업체 -> DB실행 : 자료입력 (특정업체 외주 평가현황 : 입력/등록DB )
		$GoLogic->InsertDB03();

	}else if($ActionMode=="deleteDB03"){	// 협력업체 -> DB실행 : 자료삭제 (특정업체 외주 평가현황)
		$GoLogic->DeleteDB03();

	}else if($ActionMode=="insertDB"){	// 협력업체 -> DB실행 : 자료입력 
		$GoLogic->InsertDB();

	}else if($ActionMode=="updateDB"){	// 협력업체 -> DB실행 : 자료수정
		$GoLogic->UpdateDB();

	}else if($ActionMode=="confirmPw"){	// 협력업체 -> DB실행 : 자료입력 
		$GoLogic->ConfirmPw();

	}else if($ActionMode=="deleteDB"){	// 협력업체 -> DB실행 : 자료삭제 
		$GoLogic->DeleteDB();

	}else if($ActionMode=="valueTest"){	// 입력값 확인
		$GoLogic->ValueTest();

	}else{
		
	}
?>


