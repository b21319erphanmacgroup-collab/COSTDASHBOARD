<?php
	/***************************************
	* 전자결재 리스트
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
	

	/* ------------------------------------- */
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";

	/* php function--------------------------------*/

	//$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드

	include "../model/ManHour_logic.php";


	require_once($SmartyClassPath);
	/* ------------------------------------- */
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
	$smarty->compile_dir	=$SmartyClass_CompileDir;
	$smarty->config_dir		=$SmartyClass_ConfigDir;
	$smarty->cache_dir		=$SmartyClass_CacheDir;
	/* ------------------------------------- */
	$smarty->assign('controller',"ManHour_controller.php");
	$CurrentLogic=new ManHourLogic($smarty);
	/* ------------------------------------- */
	extract($_REQUEST);
	if($ActionMode=="term"){	//개인 M-H 입력현황 프로젝트별 페이지
		$CurrentLogic->term();
	}else if($ActionMode=="daily"){	//개인 M-H 입력현황 일자별 페이지
		$CurrentLogic->daily();
	}else if($ActionMode=="manage_main"){	//관리자 M-H 메인 페이지
		$CurrentLogic->manage_main();
	}else if($ActionMode=="manage_person"){	//관리자 M-H 팀원 관리 페이지
		$CurrentLogic->manage_person();
	}else if($ActionMode=="manage_person_favorites"){	//관리자 M-H 팀원 즐겨찾기
		$CurrentLogic->manage_person_favorites();
	}else if($ActionMode=="manage_project"){	//관리자 M-H 프로젝트 목록 페이지
		$CurrentLogic->manage_project();
	}else if($ActionMode=="manage_project_sub"){	//관리자 M-H 프로젝트 사원목록 페이지
		$CurrentLogic->manage_project_sub();
	}else if($ActionMode=="manage_project_favorites"){	//관리자 M-H 프로젝트 관리 페이지
		$CurrentLogic->manage_project_favorites();
	}else if($ActionMode=="leader_main"){	//관리자 팀장 관리 메인 페이지
		$CurrentLogic->leader_main();
	}else if($ActionMode=="leader_favorites"){	//관리자 팀장 관리 proc
		$CurrentLogic->leader_favorites();
	}else if($ActionMode=="edit_main"){	//관리자 맨아워 관리 메인 페이지
		$CurrentLogic->edit_main();
	}else if($ActionMode=="edit_get_member"){	//관리자 맨아워 관리 사원목록
		$CurrentLogic->edit_get_member();
	}else if($ActionMode=="edit_view"){	//관리자 맨아워 관리 화면
		$CurrentLogic->edit_view();
	}else if($ActionMode=="proj_permission"){	//프로젝트 권한 체크
		$CurrentLogic->proj_permission();
		}else if($ActionMode=="proj_permission_test"){	//프로젝트 권한 체크
			$CurrentLogic->proj_permission_test();
	}else if($ActionMode=="manhour_insert"){	//맨아워 입력
		$CurrentLogic->manhour_insert();
	}else if($ActionMode=="manhour_insert_test"){	//맨아워 입력 test
		$CurrentLogic->manhour_insert_test();
	}else if($ActionMode=="month_manhour_insert"){	//한달치 맨아워
		$CurrentLogic->month_manhour_insert();
	}else if($ActionMode=="manage_check"){	//관리자 권한 체크
		$CurrentLogic->manage_check();
	}else if($ActionMode=="no_insert_check"){	//미입력 체크
		$CurrentLogic->no_insert_check();
	}else if($ActionMode=="WeekPopCheck"){	//맨아워 팝업 체크
		$CurrentLogic->WeekPopCheck();
	}else if($ActionMode=="DHWeekInsert"){	//일주일치 배치 입력
		$CurrentLogic->DHWeekInsert();
	}else if($ActionMode=="DHWeekMain"){	//부서장 맨아워 관리 현황 메인화면
		$CurrentLogic->DHWeekMain();
	}else if($ActionMode=="DHWeekDetail"){	//
		$CurrentLogic->DHWeekDetail();
	}else if($ActionMode=="PMWeekMenu"){	//PM 관리 메인화면
		$CurrentLogic->PMWeekMenu();
	}else if($ActionMode=="PMWeekMain"){	//프로젝트 정보화면
		$CurrentLogic->PMWeekMain();
	}else if($ActionMode=="PMWeekMore"){	//PM 더보기 클릭시 데이터
		$CurrentLogic->PMWeekMore();
	}else if($ActionMode=="PMWeekCheck"){	//프로젝트 제출
		$CurrentLogic->PMWeekCheck();
	}else{
		$CurrentLogic->PersonMain();	//개인 M-H 메인 페이지
	}
	/* ------------------------------------- */
?>
