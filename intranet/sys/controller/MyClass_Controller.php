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
	include "../model/MyClass_Logic.php";
	require_once($SmartyClassPath);
	
	$smarty = new Smarty($smarty);
	$smarty->template_dir = $SmartyClass_TemplateDir;
	$smarty->compile_dir = $SmartyClass_CompileDir;
	$smarty->config_dir = $SmartyClass_ConfigDir;
	$smarty->cache_dir = $SmartyClass_CacheDir;
	
	$CurrentLogic = new MyclassLogic($smarty);
	
	if ($ActionMode == "MyClass") {
		$CurrentLogic->MyClass();
	} else if ($ActionMode == "MyClass_GetList") {
		$CurrentLogic->MyClass_GetList();
	} else if ($ActionMode == "MyClass_GetImpressions") {
		$CurrentLogic->MyClass_GetImpressions();
	} else if ($ActionMode == "MyClass_SaveImpressions") {
		$CurrentLogic->MyClass_SaveImpressions();
	} else if ($ActionMode == "MyClass_GetContents") {
		$CurrentLogic->MyClass_GetContents();
	} else if ($ActionMode == "MyClass_GetImages") {
		$CurrentLogic->MyClass_GetImages();
	} else if ($ActionMode == "MyClass_GetFocusList") {
		$CurrentLogic->MyClass_GetFocusList();
	} else if ($ActionMode == "MyClass_Mobile") {
		$CurrentLogic->MyClass_Mobile();
	}
?>
