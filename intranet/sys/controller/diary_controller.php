<?php
	/* ***********************************
	* 주요일정
	* 2014-12-   :
	* 2014-12-18 : 세션값을 쿠키값으로 대체( $_COOKIE['CK_memberID'] )
	* 2014-12-16 : 파일정리: SUK
	*************************************** */
	/*---------------------------------------------*/
	$get_memberID	= $_GET['memberID'];
	/*---------------------------------------------*/
	require('../../sys/util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatus();
	/* DB설정 -------------------------------------*/
	include "../../sys/inc/dbcon.inc";
	/* 스마티 설정---------------------------------*/
	include "../../../SmartyConfig.php";
	/* 로직클래스----------------------------------*/
	if( $_GET['memberID'] == 'M20330' or $_GET['memberID'] == 'T03225' ){
		//include "../../sys/model/DiaryLogic_jmj.php";
		include "../../sys/model/DiaryLogic.php";
	}else{
		include "../../sys/model/DiaryLogic.php";
	}
	/*---------------------------------------------*/
	require_once($SmartyClassPath);
	/*---------------------------------------------*/
    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;
	/*---------------------------------------------*/
	$GoLogic = new DiaryLogic($smarty);
	/*---------------------------------------------*/
	if($ActionMode=="list"){				//페이지이동 : 주요일정목록
		$GoLogic->DiaryListAction();

	}else if($ActionMode=="insert_page"){	//일정추가 팝업창으로 이동
		$GoLogic->InsertPage();

	}else if($ActionMode=="update_page"){	//일정수정 팝업창으로 이동
		$GoLogic->Update_page();

	}else if($ActionMode=="insert"){		//일정추가 DB 실행
		$GoLogic->InsertAction();

	}else if($ActionMode=="update"){		//일정수정  DB 실행
		$GoLogic->UpdateAction();

	}else if($ActionMode=="delete"){		//일정삭제  DB 실행
		$GoLogic->DeleteAction();

	}else if($ActionMode=="search_list_pop"){	
		$GoLogic->displaySearchList();
	}else{
		$GoLogic->DiaryListAction();		//페이지이동 : 주요일정목록
	}
?>
