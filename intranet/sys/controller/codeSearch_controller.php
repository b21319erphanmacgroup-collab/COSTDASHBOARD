<?php
	/* ***********************************
	* 프로젝트코드검색, 사원검색
	* 2014-12-   :
	* 2014-12-16 : 파일정리: SUK 
	*************************************** */
	session_start();
	/*---------------------------------------------*/
?>
	<script src="../../js/jquery/jquery-1.10.2.js" type="text/javascript"></script>
	<script src="../../js/common/left_menubar.js"  type="text/javascript"></script>
<?php
	/*---------------------------------------------*/
	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../../sys/model/CodeSearchLogic.php";
	/* ---------------------------------------------------- */
	require_once($SmartyClassPath);
	/* ---------------------------------------------------- */
    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;
	/* ---------------------------------------------------- */

	/* ---------------------------------------------------- */
	$GoLogic = new CodeSearchLogic($smarty);
	/* ---------------------------------------------------- */
	if($ActionMode=="search_page"){				//페이지이동 : 프로젝트 코드검색
		$GoLogic->SearchInfo01();
		$GoLogic->SearchPage();

	}else if($ActionMode=="search02"){			//프로젝트 코드검색 DB실행
		$GoLogic->SearchInfo02();

	}else if($ActionMode=="searchMember_page"){	//페이지이동 : 사원검색
		$GoLogic->SearchMemberPage();

	}else if($ActionMode=="searchMember"){		//사원검색 DB실행
		$GoLogic->SearchMember();

	}else{
		$GoLogic->SearchInfo01();
		$GoLogic->SearchPage();
	}
?>
