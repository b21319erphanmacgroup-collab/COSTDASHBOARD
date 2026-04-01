<?php
	header('Access-Control-allow-Origin:*');
	session_start();
	// 부서정보 출력
	//======================================================
	include "../inc/dbcon.inc";
	class get_intranet_dept
	{
		/* ******************************************************************************************************* */
		//=============================================================================
		// 부서정보 출력
		//=============================================================================
		function get_intranet_dept()
		{
		}
		function GetInfo()
		{
			global $db;
			extract($_REQUEST);

			switch($info_type){
				case "dept":
					// $sql = "select Code, Name from systemconfig_tbl where SysKey in ( 'GroupCode', 'GroupCode_del' ) order by SysKey, orderno";
					$sql = "select Code, Name from systemconfig_tbl where SysKey in ( 'GroupCode' ) order by SysKey, orderno";
					break;
				case "rank":
					//$sql = "select Code, CONCAT(Name, ifnull(Note, '')) as Name from systemconfig_tbl where SysKey='PositionCode' order by orderno";
					$sql = "select Code, Name from systemconfig_tbl where SysKey='PositionCode' order by Code";
					break;
				case "rank_common":
					$sql = "select Name as Code, Name, Code as code2 from systemconfig_tbl where SysKey='PositionCode' AND orderno IN (0, 1) order by code2 desc";
					break;
			}

			if( $info_type != ''){
				//echo $sql."<br>";
				$return_arr = array();
				$re = mysql_query($sql,$db);
				while($re_row = mysql_fetch_array($re)){
					array_push($return_arr, $re_row);
				}
				echo json_encode($return_arr);
			}
		}//GetInfo End
		/* ******************************************************************************************************* */

	}//class get_intranet_dept End

	$info = new get_intranet_dept();
	$info->GetInfo();
?>