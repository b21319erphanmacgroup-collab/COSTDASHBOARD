<?php
	/* **********************************
	* 공통사용 :  
	* 외출/출장,업무추가/수정/종료
	* ,점심식단,개인정보비밀번호확인 등
	* ------------------------------------
	* 2014-12-   :
	* 2014-12-18 : 세션값을 쿠키값으로 대체( $_COOKIE['CK_memberID'] )
	* 2014-12-16 : 파일정리: SUK 
	*************************************** */
	session_start();
	/*---------------------------------------------*/
	extract($_REQUEST);
	$connectFlag = $_REQUEST['connectFlag'];//웹브라우저를 통한 일반사용자의 접근(connectFlag=web)
	/*---------------------------------------------*/
	$get_memberID	= $_REQUEST['memberID']; 
	/*---------------------------------------------*/
	require('../../sys/util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatusPop();
	/*---------------------------------------------*/
?>
<?
	/* DB설정 -------------------------------------*/
	include "../../sys/inc/dbcon.inc";
	/* 스마티 설정---------------------------------*/
	include "../../../SmartyConfig.php";
	/* 로직클래스----------------------------------*/
	include "../../sys/model/PopLogic.php";
	/*---------------------------------------------*/
	require_once($SmartyClassPath);
	/*---------------------------------------------*/
    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;
	/*---------------------------------------------*/
	$GoLogic = new PopLogic($smarty);
	/*---------------------------------------------*/
	// actionMode : myInfo        : 나의 정보
	// actionMode : myInfo_detail : 인사카드
	/*---------------------------------------------*/
	if($ActionMode=="addWorkPop"){				//페이지이동 : 업무추가 팝업창
		$GoLogic->AddWorkPop();

	}else if($ActionMode=="addWork"){			//업무추가 DB실행
		$GoLogic->AddWork();

	}else if($ActionMode=="editWorkPop"){		//페이지이동 : 업무수정 팝업창
		$GoLogic->EditWorkPop();

	}else if($ActionMode=="editWork"){			//업무수정 DB실행
		$GoLogic->editWork();

	}else if($ActionMode=="overWorkPop"){		//페이지이동 : 연장근무시작 페이지 : 업무종료:감리현장인원 제외 모든 직원
		$GoLogic->OverWorkPop();
		
	}else if($ActionMode=="endWorkPop"){		//페이지이동 : 업무종료:감리현장인원 제외 모든 직원
		$GoLogic->EndWorkPop();  
		
	}else if($ActionMode=="endWorkPop_old"){		//페이지이동 : 업무종료:감리현장인원
		$GoLogic->EndWorkPop_old();
		
	}else if($ActionMode=="endWork"){			//업무종료 DB실행
		$GoLogic->EndWork();
		
	}else if($ActionMode=="endWork_new"){			//업무종료 DB실행 : 감리직원
		$GoLogic->EndWork_new();
		
	}else if($ActionMode=="lunchPop"){			//페이지이동 : 점심식단 LIST팝업
		$GoLogic->LunchPop();

	}else if($ActionMode=="lunchEditPop"){		//페이지이동 : 점심식단 수정팝업
		$GoLogic->LunchEditPop();

	}else if($ActionMode=="lunchEdit"){			//점심식단 수정 DB실행
		$GoLogic->LunchEdit();

	}else if($ActionMode=="goOutPop"){			//페이지이동 : 외출입력 팝업 발생
		//$GoLogic->GoOutHistoryList();
		$GoLogic->myinfo();
		$GoLogic->GoOutPop();

	}else if($ActionMode=="goOutInsert"){		//외근입력 DB실행
		$GoLogic->GoOutInsertAction();

	}else if($ActionMode=="goOutComback"){		//외근복귀 DB실행
		$GoLogic->GoOutComback();

	}else if($ActionMode=="goTripPop"){			//페이지이동 : 출장입력 팝업 발생
		//$GoLogic->GoOutHistoryList();
		$GoLogic->myinfo();
		$GoLogic->GoTripPop();

	}else if($ActionMode=="goTripInsert"){		//출장입력 DB실행
		$GoLogic->GoTripInsertAction();

	}else if($ActionMode=="goOutHistoryList"){	//페이지이동 : 외근/출장 내역
		$GoLogic->myinfo();
		$GoLogic->GoOutHistoryList();
		//$GoLogic->GoOutTripListPop();

	}else if($ActionMode=="checkPwMain"){		//페이지이동 : 패스워드 체크
		$GoLogic->myinfo();
		$GoLogic->CheckPwMainPage();

	}else if($ActionMode=="signPop"){			//페이지이동 : 패스워드 체크
		$GoLogic->SignPop();

	}else if($ActionMode=="daySearch"){			//일자별 조회
		$GoLogic->DaySearch();

		
	}else if($ActionMode=="goOutHistoryListPerson"){//일자별 조회
		$GoLogic->GoOutHistoryListPerson();

	}else if($ActionMode=="deleteDB_officialPlan"){	//출장삭제
		$GoLogic->DeleteDB_officialPlan();
	
	}else if($ActionMode=="goTripPopUpdate"){	    //페이지 이동 : 출장 수정
		$GoLogic->GoTripPopUpdate();

	}else if($ActionMode=="updateDB_officialPlan"){	//DB실행 : 출장 수정DB
		$GoLogic->UpdateDB_officialPlan();

	}else if($ActionMode=="goEtcPop"){				//페이지 이동 : 경유/휴가 신청(임원)
		$GoLogic->GoEtcPop();

	}else if($ActionMode=="etcCRUD"){				//DB실행
		$GoLogic->EtcCRUD();

	}else if($ActionMode=="process_data"){				//개인 당일 외근/출장 취소
		$GoLogic->Process_data();	
	
		
	}else{
		
	}


?>