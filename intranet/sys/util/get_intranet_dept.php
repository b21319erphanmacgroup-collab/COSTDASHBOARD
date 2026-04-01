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

			$sql = "select Code, Name from systemconfig_tbl where SysKey='GroupCode' order by orderno";
			//echo $sql."<br>";
			$return_arr = array();
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)){
				array_push($return_arr, $re_row);
			}
			echo json_encode($return_arr);
		}//GetInfo End
		/* ******************************************************************************************************* */

	}//class get_intranet_dept End

	$dept = new get_intranet_dept();
	$dept->GetInfo();
?>