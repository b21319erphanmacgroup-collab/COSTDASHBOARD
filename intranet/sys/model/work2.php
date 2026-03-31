<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
/**
 * *************************************
 * 수주 및 매출, 수금현황
 * ------------------------------------
 * 2014-01-14 : 파일정리: JYJ
 * **************************************
 */

// include "../../sys/inc/dbcon.inc";
include "../../sys/inc/dbcon_hm.inc";
include "../../sys/inc/function_intranet.php";
include "../util/HanamcPageControl.php";
include "../../../SmartyConfig.php";
include "../../sys/inc/function_project_v2_work.php";


//수주총괄

		$sel_year="2017";


		$UNIT = "100000000"; // 단위:백만원
		$MAX_CNT = "12";
		$MAX_SUM = "13";
				
		$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectGroup' order by code";
		$recordBlock1 = mysql_query ( $azSQL, $db );
		while ( $record1 = mysql_fetch_array ( $recordBlock1 ) ) {
			$group_code = $record1 [Code];
			$group_name = $record1 [Name];
			
			$i = $group_code;
			$n_color = $group_code;
			
			$g = "0";
			$azSQL = "select * from systemconfig_tbl where SysKey = 'ProjectCode' and CodeORName = '$group_code' and Code not in ('117','118','131','151','160','161','167','170') order by orderno";
			//echo $azSQL."<br>";
			
			$recordBlock2 = mysql_query ( $azSQL, $db );
			while ( $record2 = mysql_fetch_array ( $recordBlock2 ) ) {
				$g_code = $record2 [Code];
				$g_name [$g] = $record2 [Name];
				
					$sum [$MAX_SUM] = 0;
					for($month = 1; $month <= $MAX_CNT; $month ++) {
						$design_name [$g] = $g_name [$g];
						$sum [$month] = Contract_SUM_M ( $g_name [$g], $sel_year, $month ) + Change_SUM_M ( $g_name [$g], $sel_year, $month );
						$sum [$MAX_SUM] = $sum [$MAX_SUM] + $sum [$month];


						
					}

					
	
					for($month = 1; $month <= $MAX_SUM + 1; $month ++) {
						$soft1_tot [$month] = $soft1_tot [$month] + $sum [$month];
						
					}
				
				$g ++;
			} // while($record2 = mysql_fetch_array($recordBlock2))
		} // while($record1 = mysql_fetch_array($recordBlock1))
	
	
		for($month = 1; $month <= $MAX_SUM; $month ++) {
			//$soft1_tot_sum [$month] = number_format ( $soft1_tot [$month] / $UNIT );
			$soft1_tot_sum [$month] = sprintf ( "%.1f", round ( $soft1_tot [$month] / $UNIT, 2 ) );
			echo $month."-".$soft1_tot_sum [$month]."<br>";
			echo $month."-".$soft1_tot_sum2 [$month]."<br>";
			echo "------------------------------<Br>";
		}
	

?>
