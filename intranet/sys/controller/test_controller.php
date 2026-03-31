<?php
	session_start();
	session_cache_limiter('private');

	//	include "../inc/auth.php";
	//DB설정 //////////////////////////////////////
	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../../sys/model/TestLogic.php";

	require_once($SmartyClassPath);

    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;

	$GoLogic = new TestLogic($smarty);

	if($ActionMode=="test_insert_pop"){
		$GoLogic->TestInsertPop();

	}else if($ActionMode=="test_insert"){
		$GoLogic->TestInsert();

	}else if($ActionMode=="pageList"){	//페이징 테스트
		$GoLogic->PageList();

	}else if($ActionMode=="listPage"){	    //목록 페이지로 이동
		$GoLogic->ListPage();

	}else if($ActionMode=="insertPage"){	//입력 페이지로 이동
		$GoLogic->InsertPage();

	}else if($ActionMode=="jsonPage"){	    //ajax json TEST
		$GoLogic->JsonPage();

	}else if($ActionMode=="jsonTest"){	    //ajax json TEST
		$GoLogic->JsonTest();

	}else if($ActionMode=="goCookiePage"){	    //쿠키 TEST
		$GoLogic->GoCookiePage();

	}else if($ActionMode=="goSessionPage"){	    //세션 TEST
		$GoLogic->GoSessionPage();

	}else if($ActionMode=="goGetParamPage"){	    //get TEST
		$GoLogic->GoGetParamPage();
	}else if($ActionMode=="test_mh"){	    //맨아워 TEST
		$GoLogic->test_mh();

	}else if($ActionMode=="test_node"){	    //node.js test
		$GoLogic->test_node();
	}else if($ActionMode=="PlanningHr7"){	    //node.js test
		$GoLogic->PlanningHr7();



	}else
	{

	}
?>


