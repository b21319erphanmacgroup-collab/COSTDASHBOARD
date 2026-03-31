<?php

	/***************************************
	* 부서장 결재 메시지
	****************************************/

include "../inc/dbcon.inc";
//	include "../inc/dbconForMystation.inc";
include "../../../SmartyConfig.php";
include "../util/HanamcPageControl.php";

extract($_POST);
class familycontactLogic {
	var $smarty;
	var $oracle;
	function familycontactLogic($smarty)
	{
		$this->smarty=$smarty;
	}

	//============================================================================
	// 목록 출력
	//============================================================================
	function MemberList(){
		global $db;
		extract($_REQUEST);
		//print_r($_REQUEST);
		/*
		$test = base64_encode ( $membername1 ); //암호화
		echo $test;
		*/

		$membername1 = base64_decode ( $membername1 ); //복호화
		$membername2 = base64_decode ( $membername2 ); //복호화

		if($membername1 != "" or $membername2 != ""){
			$sql = "
				SELECT
					(SELECT Name FROM systemconfig_tbl WHERE SysKey LIKE 'groupcode' and Code = member_tbl.GroupCode) AS GroupName
					,korName
					,(SELECT Name FROM systemconfig_tbl WHERE SysKey LIKE 'PositionCode' and Code = member_tbl.RankCode) AS RankName
					,ExtNo
					,Mobile
					,eMail
				FROM
					member_tbl
				WHERE
					WorkPosition not like 9
					AND (
						korName LIKE ''
				";

				if($membername1 != ""){
					$sql .= "
						or korName LIKE '%".$membername1."%'
					";
				}
				if($membername2 != ""){
					$sql .= "
						or korName LIKE '%".$membername2."%'
					";
				}

				$sql .= "
					)
				ORDER BY RankCode asc
				";
			//echo $sql;

			$list_data = Array();
			$re = @mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)) {
				array_push($list_data,$re_row);
			}
		}

		$this->smarty->assign("list_data",$list_data);
		$this->smarty->display("intranet/common_contents/work_familycontact/familycontact.tpl");
	}
}
?>