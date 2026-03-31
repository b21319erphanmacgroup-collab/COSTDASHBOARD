<?php
header('Content-Type: text/html; charset=UTF-8');
include "../inc/dbcon.inc";
include "../../sys/inc/function_intranet.php";
include "../../../SmartyConfig.php";
include "../util/HanamcPageControl.php";

extract($_REQUEST);
class MyclassLogic {
	var $smarty;

	function MyclassLogic($smarty) {
		$Controller = "MyClassReply_Controller.php";
		$ActionMode = $_REQUEST['ActionMode'];

		$this->smarty=$smarty;
		$this->smarty->assign('Controller', $Controller);
		$this->smarty->assign('ActionMode', $ActionMode);
	}


	function MyClass_Reply() {

		global $db;
		global $memberID,$Company;

		$sql = "select * from myclass_reply_tbl where Member_ID='$memberID' and Company in ( 'JANG', 'PTC', 'PLAN', 'CENTER' ,'HANM' ,'SAMA' )";
		//echo $sql."<br>";
		$re = mysql_query($sql,$db);
		while($re_row = mysql_fetch_array($re))
		{
				$title = $re_row[title];
				$korName = $re_row[Member_Name];
				$RankName = $re_row[Member_Rank];
				$FileName = $re_row[Reply_File];
				$Company = $re_row[Company];

				//echo $title."<br>";
		?>
			<!DOCTYPE HTML>
			<body>
				<form name='fm' action = './MyClassReply_Controller.php' method = 'post' target="myclass">
					<input type='hidden' name="ActionMode" value="MyClass_View">
					<input type='hidden' name="memberID" value="<?=$memberID?>">
					<input type='hidden' name="Company" value="<?=$Company?>">
					<input type='hidden' name="korName" value="<?=$korName?>">
					<input type='hidden' name="RankName" value="<?=$RankName?>">
					<input type='hidden' name="FileName" value="<?=$FileName?>">
				</form>
				<script type="text/javascript">
					var url='';
					var myclass = window.open(url, "myclass", "width=845, height=865, scrollbars=yes, resizable=yes, status=no, location=no, directories=no, toolbar=no, menubar=no, left=0, top=0, resizable=yes");
					//console.log(document.fm);
					document.fm.submit();
				</script>
			</body>
			</html>

		<?
		}
	}

	function MyClass_View() {

		global $db;
		global $memberID,$Company;
		global $korName,$RankName,$FileName;

		//echo $korName."<Br>";
		//echo $RankName."<Br>";
		//echo $FileName."<Br>";

		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('Company',$Company);
		$this->smarty->assign('korName',$korName);
		$this->smarty->assign('RankName',$RankName);
		$this->smarty->assign('FileName',$FileName);


		$this->smarty->display("intranet/common_contents/work_myclass_reply/reply.tpl");
	}


	function arrayToString($array) {
		$sRtn = "[";

		foreach($array as $key => $value) {
			$sRtn .= $sRtn == "[" ? "$key=$value" : "&$key=$value";
		}
		$sRtn .= "]";

		return $sRtn;
	}

	function getUrl($host, $uri) {
		$nIndex = strpos($uri, "?");
		$sUrl = $host . mb_substr($uri, 0, $nIndex, "UTF-8");

		return $sUrl;
	}
}
?>