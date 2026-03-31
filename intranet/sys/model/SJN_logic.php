<?php
	/***************************************
	* 사장님 페이지
	****************************************/

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";

	class SJNLogic {
		var $smarty;
		function SJNLogic($smarty){
			$this->smarty=$smarty;
		}

		//============================================================================
		// 회의실 정보
		//============================================================================
		function meeting(){
			global $db;
			extract($_REQUEST);
			$yoil = array("일","월","화","수","목","금","토");

			if( $MainAction == 'select' ){
				$sql_search = "
					select
						*
					from
						( Select * from systemconfig_tbl where SysKey = 'meetingroom' ) A
						, ( select * from schedule_meetingroom_tbl where is_del = 1 and '$ndate' between sdate and edate ) B
					where
						A.code = B.devicecode
					order by A.orderno desc, B.stime
				";
				//echo $sql_search."<br>";
				$result_array = array();
				$re_search = mysql_query($sql_search,$db);
				while($re_search_row = mysql_fetch_array($re_search)){
					array_push($result_array,$re_search_row);
				}//while

				$full_arr = array();
				$full_arr['ndate'] = $ndate;
				$full_arr['pre_date'] = date("Y-m-d", strtotime($ndate." -1 day"));
				$full_arr['next_date'] = date("Y-m-d", strtotime($ndate." +1 day"));
				$full_arr['yoil'] = $yoil[date('w', strtotime($ndate))];
				$full_arr['list'] = $result_array;

				echo json_encode($full_arr);

			}else{
				$this->smarty->assign('ndate',$ndate);
				$this->smarty->assign('pre_date', date("Y-m-d", strtotime($ndate." -1 day")) );
				$this->smarty->assign('next_date', date("Y-m-d", strtotime($ndate." +1 day")) );
				$this->smarty->assign('yoil',$yoil[date('w', strtotime($ndate))]);
				$this->smarty->display("intranet/common_contents/SJN/SJN_meeting.tpl");
			}
		}

		//============================================================================
		// 정보
		//============================================================================
		function info(){
			extract($_REQUEST);



			$sql_search = "
				SELECT
					L.menu_num   as lunch_menu_num
					,L.menu_day   as lunch_menu_day
					,L.menu_main  as lunch_menu_main
					,L.menu_sub   as lunch_menu_sub
					,L.menu_add   as lunch_menu_add
				FROM
					lunch_menu_tbl L
				WHERE
					L.menu_num = '$ndate'
			";
			//echo $state_sql."<br>";
			$re_search = mysql_query($sql_search,$db);
			while($re_search_row = mysql_fetch_array($re_search)){
				array_push($search_data_array,$re_search_row[start_time]);
			}//while
			$this->smarty->assign('itemdata_json',json_encode($result_array));

			$this->smarty->display("intranet/common_contents/SJN/SJN_info.tpl");
		}


	}
//END============================================================================
?>