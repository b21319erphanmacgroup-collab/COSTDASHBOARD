<? 
	$ActionMode = $_REQUEST['ActionMode'];
	$Company_Kind = $_REQUEST['Company_Kind'];
	
	$sessionCompnays = array(
		'HANM' => "바론컨설턴트"
	);
	
	if (array_key_exists($Company_Kind, $sessionCompnays)) {
		session_start();
		require('../util/LoginInfomation.php');
		$LoginIn = new LoginInfomation();
		$LoginIn->GetLoginStatus();
		
		if ($_SESSION['memberID'] == "") {
			$_SESSION['memberID'] = $memberID;
		} else {
			$memberID = $_SESSION['memberID'];
		}
	}
	
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/ClassRecommend_Logic.php";
	require_once($SmartyClassPath);
	
	$smarty = new Smarty($smarty);
	$smarty->template_dir = $SmartyClass_TemplateDir;
	$smarty->compile_dir = $SmartyClass_CompileDir;
	$smarty->config_dir = $SmartyClass_ConfigDir;
	$smarty->cache_dir = $SmartyClass_CacheDir;
	
	$CurrentLogic = new ClassRemommendLogic($smarty);
	
	if ($ActionMode == "Mobile") {
		$CurrentLogic->Mobile();
	} else {
		$CurrentLogic->Main();
	}
?>
