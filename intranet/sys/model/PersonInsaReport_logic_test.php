<?php

	/*
	* -----------------------------------------------------------------------------------
	*  수 정 날 짜 |    작 성 자    |  수정내용
	* -----------------------------------------------------------------------------------


	*/
	extract($_REQUEST);
	//header('Content-Type: text/html; charset=UTF-8');

	// include "../util/OracleClass.php";
	include "../inc/dbcon.inc";	//인트라넷 DB연결

	include "../../../SmartyConfig.php";
	include "../inc/function_add.php";
	include "../inc/getNeedDate.php";
	/*include "../util/AutoGrid_Jqgrid.php";*/


	class PersonInsaReportLogic {
		var $smarty;
		var $year;
		var $start_month;
		var $start_day;
		var $end_month;
		var $end_day;
		var $memo;
		var $QueryDay;
		var $QueryDay2;
		var $oracle;


		function PersonInsaReportLogic($smarty, $COMPANY)
		{
			global $emp_id;
			if( $COMPANY == 'HANMAC' || $COMPANY == 'SAMAN' ){
				$this->oracle=new OracleClass($smarty, $COMPANY);
			}elseif( $COMPANY == 'HALLA'){
				$this->mssql=new MssqlClass($smarty);
			}

			$this->smarty=$smarty;

			$this->PRINTYN=$_REQUEST['PRINT'];
			$this->excel=$_REQUEST['excel'];
			$this->start_day=$_REQUEST['start_day'];
			$this->end_day=$_REQUEST['end_day'];
			//$this->memo=trim($_REQUEST['memo']);


			if($this->start_day == "")
				$this->start_day=date("Y").date("m").date("d");

			if($this->end_day == "")
				$this->end_day=date("Y").date("m").date("d");

			$this->start_day=str_replace("-","",$this->start_day);
			$this->start_day=str_replace(".","",$this->start_day);

			$this->end_day=str_replace("-","",$this->end_day);
			$this->end_day=str_replace(".","",$this->end_day);


			$QueryStartDate=$this->start_day;
			$QueryEndDate=$this->end_day;

			$ActionMode=$_REQUEST['ActionMode'];
			$this->smarty->assign('ActionMode',$ActionMode);
			$this->smarty->assign('excel',$this->excel);
			$this->smarty->assign('print',$this->print);
			$this->smarty->assign('search_month',date("Y")."-".date("m"));
			$this->smarty->assign('current_year',date("Y"));
			$this->smarty->assign('current_month',date("Y")."-".date("m"));
			$this->smarty->assign('current_day',date("Y")."-".date("m")."-".date("d"));
			$this->smarty->assign('now_year',date("Y"));
			$this->smarty->assign('now_month',date("m"));

			$this->smarty->assign('displaylist_01',$_REQUEST['displaylist_01']);
			$this->smarty->assign('displaylist_02',$_REQUEST['displaylist_02']);
			$this->smarty->assign('displaylist_03',$_REQUEST['displaylist_03']);
			$this->smarty->assign('displaylist_04',$_REQUEST['displaylist_04']);
			$this->smarty->assign('displaylist_05',$_REQUEST['displaylist_05']);
			$this->smarty->assign('displaylist_06',$_REQUEST['displaylist_06']);
			$this->smarty->assign('displaylist_07',$_REQUEST['displaylist_07']);

			$this->DefaultView="";
			$this->DefaultView=$this->DefaultView."&displaylist_01=".$_REQUEST['displaylist_01'];
			$this->DefaultView=$this->DefaultView."&displaylist_02=".$_REQUEST['displaylist_02'];
			$this->DefaultView=$this->DefaultView."&displaylist_03=".$_REQUEST['displaylist_03'];
			$this->DefaultView=$this->DefaultView."&displaylist_04=".$_REQUEST['displaylist_04'];
			$this->DefaultView=$this->DefaultView."&displaylist_05=".$_REQUEST['displaylist_05'];
			$this->DefaultView=$this->DefaultView."&displaylist_06=".$_REQUEST['displaylist_06'];
			$this->DefaultView=$this->DefaultView."&displaylist_07=".$_REQUEST['displaylist_07'];



			$this->userid = $_SESSION['planning_user_id'];
			$this->deptcode = $_SESSION['planning_user_deptcode'];

			extract($_REQUEST);
			if($this->userid =="") 	$this->userid=$userid;
			if($this->deptcode =="") 	$this->deptcode=$deptcode;

			$this->smarty->assign('Read_Write',$Read_Write);
			$this->smarty->assign('planning_user_id',$_SESSION['planning_user_id']);
			$this->smarty->assign('planning_user_deptcode',$_SESSION['planning_user_deptcode']);

			//$CommonCode = new CommonCodeList ( $this->smarty );
			//$this->DeveloperYN = $CommonCode->ConfirmDeveloperYN ( $_SESSION ['planning_user_id'], "DeveloperYN", "assign" );
		}


		function REPORT_02(){
			extract($_REQUEST);
			switch($MainAction){
				case "HTML_Ajax_01": 	$this->REPORT_02_HTML_Ajax_01();	break;
			}
		}

		function REPORT_02_HTML_Ajax_01($mode=true){
			extract($_REQUEST);
			switch($SubAction){
				case "insert_worker":
					header('Content-Type: text/html; charset=UTF-8');
					include "../../../person_mng/inc/vacationfunction.php";
					include "../util/OracleClass.php";
					$this->oracle = new OracleClass($this->smarty, 'BARO');

					global $db;
					$action = true;	//	true	false

					if($test == 'test'){
						$action = false;
					}

					//$set_date = str_replace ( '-', '', $set_date) ;
					if($set_date == null or $set_date == ''){
						$set_date = date('Ym');
					}

					$set_date_ori = $set_date;

					//한맥 worker 입력
					for($c=0; $c<2; $c++){

						if($c == 0){
							$set_date = $set_date_ori;
						}else{
							$set_date = date("Ym",strtotime("-".$c." month", strtotime($set_date_ori."01")));
						}

						$set_date_bar = substr($set_date,0,4)."-".substr($set_date,4,2);
						$set_year = substr($set_date,0,4);
						$set_month = (int)substr($set_date,4,2);
						$set_date_last = $set_date.date('t', strtotime($set_date_bar."-01"));
						$set_date_last_bar = $set_date_bar.'-'.date('t', strtotime($set_date_bar."-01"));
						$set_month_Pre = date("Y-m",strtotime("-1 month", strtotime($set_date."01")));

						/* 기간 월별로 나누기 */
						$azsql_s = "
							SELECT *, date_format(start_time,'%Y-%m') as s_ym, date_format(end_time,'%Y-%m') as e_ym FROM userstate_tbl
							WHERE
								state = 1
								AND ( date_format(start_time,'%Y%m') = '".$set_date."' OR date_format(end_time,'%Y%m') = '".$set_date."' )
								/*AND memberno IN (SELECT memberno FROM member_tbl WHERE groupcode in (3, 98, 24, 25) AND ( date_format(leavedate , '%Y%m') >= '".$set_date."' OR leavedate = '0000-00-00' ))*/
								AND date_format(start_time,'%Y%m') != date_format(end_time,'%Y%m')
						;";
						if(!$action){
							echo '<br><br>기간 월별로 나누기<br>';
							echo $azsql_s;
						}

						$sql_arr = array();
						$re = mysql_query($azsql_s,$db);
						while($row=mysql_fetch_array($re)){
							/* 나누는거 해야함. */
							//print_r($row);
							if($row["s_ym"] == $set_date_bar){
								$azsql = "
									update userstate_tbl set start_time = '".$row["e_ym"]."-01' where num = ".$row["num"]."
								;";
								array_push($sql_arr, $azsql);
								$azsql = "
									insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
									values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["start_time"]."', '".$row["s_ym"].'-'.date('t', strtotime($row["start_time"]))."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
								;";
								array_push($sql_arr, $azsql);
							}else{
								$azsql = "
									update userstate_tbl set end_time = '".$row["s_ym"]."-".date('t', strtotime($row["start_time"]))."' where num = '".$row["num"]."'
								;";
								array_push($sql_arr, $azsql);
								$azsql = "
									insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
									values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["e_ym"]."-01', '".$row["end_time"]."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
								;";
								array_push($sql_arr, $azsql);
							}
						}

						for($i=0; $i<count($sql_arr); $i++){
							if($action){
								mysql_query($sql_arr[$i],$db);
							}else{
								echo '<br>'.$sql_arr[$i];
							}
						}

						/*sortkey 셋팅*/
						/* ERP 발령정보에서 정보가져와서 설정.
							발령 마지막 전날이 퇴직이 아닐때
						*/
						// 한맥 DB set
						$host="192.168.10.5";
						$user="root";
						$pass="erp";
						$dataname="hanmacerp";
						$hanmac_db=mysql_connect($host,$user,$pass);

						mysql_select_db($dataname,$hanmac_db);
						mysql_query("set names utf8");
						//mysql_query("set names euckr");
						//mysql_set_charset("utf-8",$hanmac_db);

						$azsql = "
							select
								member_id as EMP_NO
								, LPAD(DEPT_CODE,2,'0') AS DEPT_CODE
								, ( select code from total_systemconfig_tbl B where syskey = 'RANK' and comp_code = '20' and sys_code = '0' and B.name = A.rank_name ORDER BY CODE LIMIT 1 ) AS RANK_CODE
							from
								total_member_tbl A
							WHERE
								sys_comp_code = '30'
								/*and working_comp = '20'*/
								and join_date <= '".$set_date_last_bar."'
								and (
									retire_date = '0000-00-00'
									or retire_date > '".$set_date_bar."-01'
								)
							;
						";
						if(!$action){
							echo '<br><br>바론인원<br>';
							echo $azsql;
						}
						//echo $azsql;

						$member_info = array();
						$re = mysql_query($azsql,$hanmac_db);
						while($row=mysql_fetch_array($re)){
							$member_info[$row["EMP_NO"]] = $row;
						}

						/*월 삭제*/
						$azsql = "
							delete from worker_date_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
						;";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>월 삭제<br>';
							echo $azsql;
						}

						/*월 추가*/
						$azsql = "
							INSERT INTO worker_date_tbl (date_y, date_m, date_d, week, holy, week_cnt )
							SELECT
								date_y
								, date_m
								, COUNT(date_m) AS date_d
								, SUM(week) AS week
								, (SELECT COUNT(date) FROM holyday_tbl B WHERE a.date_y = YEAR(B.date) AND A.date_m = MONTH(B.date) and DAYOFWEEK( B.date ) in (2,3,4,5,6) ) AS holy
								, sum(week_cnt) as week_cnt
							FROM (
								SELECT
									a.date_ymd
									, YEAR(a.date_ymd) AS date_y
									, MONTH(a.date_ymd) AS date_m
									, IF( DAYOFWEEK( a.date_ymd ) = 1 OR DAYOFWEEK( a.date_ymd ) = 7, 1, 0) AS week
									, IF( DAYOFWEEK( a.date_ymd ) = 5, 1, 0) AS week_cnt
								FROM (
									SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
										SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
										UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
									) AS a
									CROSS JOIN (
										SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
										UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
									) AS b
									CROSS JOIN (
										SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
										UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
									) AS c
								) AS a
								WHERE 1 = 1
								AND a.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
							) a
							GROUP BY
								date_y, date_m
						;";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>월 추가<br>';
							echo $azsql;
						}

						/*사번별 추가*/

						$azsql = "
							delete from worker_total_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
						;";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>사번별 삭제<br>';
							echo $azsql;
						}

						if(!$action){
							echo '<br><br>사번별 추가<br>';
						}
						foreach($member_info as $key => $value){
							$azsql = "
								INSERT INTO worker_total_tbl (memberno, date_y, date_m, dept_top_code, dept_code, rank_code)
								values
								( '$key', ".$set_year.", ".$set_month.", (SELECT Code FROM systemconfig_tbl WHERE SysKey = 'GroupCode_Top' AND CodeORName LIKE CONCAT( '%g', '".$value['DEPT_CODE']."'*1, 'g%' )) , '".$value['DEPT_CODE']."', '".$value['RANK_CODE']."' ) ;
							";
							//echo '<br>'.$azsql.'<br>';
							if($action){
								mysql_query($azsql,$db);
							}else{
								echo '<br>'.$azsql;
							}
						}

						/*연차 1, 18, 30, 31 - 주말,휴일 제외*/
						$azsql = "
							UPDATE worker_total_tbl BB SET vacation1 = IFNULL((
								SELECT SUM(vacation1)
								FROM (
									SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
									FROM (
										SELECT
											memberno
											, start_time
											, end_time
											, (
												CASE
													WHEN state = 1 THEN IF(note LIKE '%반차%', 4, 8)
													WHEN state = 30 THEN 4
													WHEN state = 31 THEN 4
													ELSE sub_code
												END
											) * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
										FROM (
											SELECT
												memberno
												, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
												, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
												, state
												, note
												, sub_code
											FROM
												userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (1, 18, 30, 31)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
										) AAA
										ORDER BY vacation desc
									) A
									, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT '1986-12-31' FROM dual ) B
									GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
								) AA
								WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
								GROUP BY memberno, date_y, date_m
							), 0)
							WHERE date_y = ".$set_year." AND date_m = ".$set_month."
						;";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>연차 1, 18, 30, 31 - 주말,휴일 제외<br>';
							echo $azsql;
						}

						/*기타 주말제외. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가), 10:대기, 17:기타*/
						$azsql = "
							UPDATE worker_total_tbl BB SET vacation2 = IFNULL((
								SELECT SUM(vacation1)
								FROM (
									SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
									FROM (
										SELECT
											memberno
											, start_time
											, end_time
											, 8 * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
										FROM (
											SELECT
												memberno
												, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
												, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
											FROM
												userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (5,6,7,8,10,17)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
										) AAA
										ORDER BY vacation desc
									) A
									, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT date FROM date_weekend_tbl where date_format(DATE,'%Y%m') like '".$set_date."'
									) B
									GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
								) AA
								WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
								GROUP BY memberno, date_y, date_m
							), 0)
							WHERE date_y = ".$set_year." AND date_m = ".$set_month."
						;";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>기타 5,6,7,8,10,17 주말제외<br>';
							echo $azsql;
						}

						/*비고 입력. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가),17:기타, 20:합사, 21:합사, 9:파견, 15:파견*/
						$azsql = "
							UPDATE
								worker_total_tbl A
								, (
									SELECT
										memberno
										, ".$set_year." AS date_y
										, ".$set_month." AS date_m
										, CONCAT(
											IF(SUM(state_1) > 0, '_경조', '')
											, IF(SUM(state_2) > 0, '_보건', '')
											, IF(SUM(state_3) > 0, '_출산', '')
											, IF(SUM(state_4) > 0, '_특별', '')
											, IF(SUM(state_5) > 0, '_훈련', '')
											, IF(SUM(state_6) > 0, '_교육', '')
											, IF(SUM(state_7) > 0, '_기타', '')
											, IF(SUM(state_8) > 0, '_합사', '')
											, IF(SUM(state_9) > 0, '_파견', '')
										) AS etc
									FROM
										(

											SELECT
												memberno
												, IF(state = 7, 1, 0) AS state_1
												, 0 AS state_2
												, 0 AS state_3
												, 0 AS state_4
												, IF(state = 5, 1, 0) AS state_5
												, IF(state = 6, 1, 0) AS state_6
												, 0 AS state_7
												, 0 AS state_8
												, 0 AS state_9
											FROM userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (5,6,7)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )

											UNION ALL

											SELECT
												memberno
												, 0
												, state_2
												, state_3
												, state_4
												, 0
												, 0
												, state - state_2 - state_3 - state_4
												, 0
												, 0
											FROM (
												SELECT
													memberno
													, IF(note LIKE '%보건%', 1, 0) AS state_2
													, IF(note LIKE '%출산%', 1, 0) AS state_3
													, IF(note LIKE '%특별%', 1, 0) AS state_4
													, 1 AS state
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (8,17)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) A

											UNION ALL

											SELECT
												memberno
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 1
												, 0
											FROM userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (20, 21)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											UNION ALL

											SELECT
												memberno
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 1
											FROM userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (9, 15)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
										) C
									GROUP BY memberno
								) B
							SET
								A.etc = B.etc
							WHERE
								A.date_y = B.date_y
								AND A.date_m = B.date_m
								AND A.memberno = B.memberno
							;
						";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>비고 입력<br>';
							echo $azsql;
						}

						/*지각 횟수*/
						$azsql = "
							UPDATE
								worker_total_tbl BB
							SET
								tardy = IFNULL((
									SELECT tardy FROM (
										SELECT
											A.memberno
											, YEAR(A.entrytime) AS date_y
											, MONTH(A.entrytime) AS date_m
											, SUM( CASE WHEN date_format(A.entrytime,'%H') >= IFNULL(B.tardy_h, 9) AND date_format(A.entrytime,'%i') > IFNULL(B.tardy_m, 0) THEN IF( ( SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(A.entrytime,'%Y-%m-%d') ) > 0 , 0, 1) ELSE 0 end) AS tardy
										FROM (
											SELECT A.memberno, A.entrytime FROM (
												SELECT
													memberno
													, entrytime
												FROM
													dallyproject_tbl
												WHERE
													EntryTime BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
													AND DAYOFWEEK(entrytime) NOT IN (1,7)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) A
											left join
											( SELECT memberno, start_time, end_time FROM userstate_tbl WHERE state IN ( 1, 18, 17, 30, 5, 6, 2, 3 ) AND ( date_format( start_time , '%Y%m') = '".$set_date."' OR date_format( end_time , '%Y%m') = '".$set_date."' ) ) B
											ON
												A.memberno = B.memberno
												AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.start_time AND B.end_time
											WHERE B.memberno IS null
										) A
										left join
										( SELECT * FROM worker_tardy_tbl WHERE '".$set_date."' between date_format(s_date, '%Y%m') and date_format(e_date, '%Y%m') ) B
										ON
											A.memberno = B.memberno
											AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.s_date AND B.e_date
										GROUP BY A.memberno
									) AA
									WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
								), 0)
								, BB.login_cnt = (select count(MemberNo) from dallyproject_tbl where MemberNo = BB.MemberNo and date_format(EntryTime , '%Y-%m-%d') IN (
									SELECT date_format(DATE , '%Y-%m-%d') FROM (
										SELECT date FROM holyday_tbl WHERE DATE BETWEEN '".$set_date_bar."-01' AND '".$set_date_last_bar."'
										UNION ALL
										SELECT date_format(date_ymd , '%Y-%m-%d') FROM (
											SELECT C.date_ymd, YEAR(C.date_ymd) AS date_y, MONTH(C.date_ymd) AS date_m, IF( DAYOFWEEK( C.date_ymd ) = 1 OR DAYOFWEEK( C.date_ymd ) = 7, 1, 0) AS week
											FROM (
												SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
												SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS a
												CROSS JOIN (
												SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS b
												CROSS JOIN (
												SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS c
											) AS C
											WHERE
												C.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
										) D WHERE week = 1
									) E GROUP BY date
								))
							WHERE date_y = ".$set_year." AND date_m = ".$set_month."
						;";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>지각 횟수 - 출장 외출 그런거 안따짐.<br>';
							echo $azsql;
						}

						/*연장근로시간 - 실제,인정, 연장근로일수 - 실제,인정*/
						if(date("Ym",strtotime("-2 month", strtotime(date('Ym')."01"))) < $set_date){
							$azsql = "
								SELECT
									C.MemberNo
									, EntryTime
									, termTime
									, min_MINUTE
									, max_MINUTE
									, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_time
									, C.RankCode
								FROM
									(
										SELECT
											MemberNo
											, ( SELECT IF(RealRankCode = '' , RankCode, ifnull(RealRankCode, RankCode)) AS RankCode FROM member_tbl WHERE MemberNo = A.MemberNo ) AS RankCode
											, EntryTime
											, date_format(OverTime,'%Y-%m-%d') AS over_date
											, IF(
												DAYOFWEEK = 0 and date_format(OverTime,'%H:%i') = '00:00'
												, 0
												, TIMESTAMPDIFF(
													MINUTE
													, IF(
														DAYOFWEEK = 0
														, OverTime
														, IF(
															date_format(EntryTime,'%H:%i') < B.start_time
															, CONCAT(date_format(EntryTime,'%Y-%m-%d '), B.start_time, ':00')
															, EntryTime
														)
													)
													, LeaveTime
												)
											) AS termTime
											, (SUBSTRING_INDEX(min_time, ':', 1) * 60 + SUBSTRING_INDEX(min_time, ':', -1) * 1 ) AS min_MINUTE
											, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_MINUTE
										FROM
											(
												SELECT
													MemberNo
													, EntryTime
													, IF( DAYOFWEEK( EntryTime ) = 1 OR DAYOFWEEK( EntryTime ) = 7, 1, (SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(dallyproject_tbl.EntryTime,'%Y-%m-%d ') ) ) AS DAYOFWEEK
													, OverTime
													, LeaveTime
												FROM dallyproject_tbl
												WHERE EntryTime BETWEEN '".$set_month_Pre."-21 00:00:00' AND '".$set_date_bar."-20 23:59:59' AND MODIFY = 1
												ORDER BY MemberNo
											) A
											, (
												SELECT * FROM overtime_basic_new_tbl WHERE code in ( 100, 101 )
											) B
										WHERE
											A.DAYOFWEEK = B.code or (A.DAYOFWEEK+100) = B.code
									) C
									, (
										SELECT * FROM overtime_basic_new_tbl WHERE code BETWEEN 102 AND 105
										union all
										select 999, '', '', '999:00', '', '-C0-C1-C2-C3-C4-C5-C6-C7-C8-C0A-C4A-C7A-C8A-', '' from dual
									) D
								WHERE
									D.RankCode LIKE CONCAT('%-', C.RankCode, '-%')
								order by MemberNo, EntryTime
							;";
							if($action){
							}else{
								echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
								echo $azsql;
							}

							$over_arr = array();

							$re = mysql_query($azsql,$db);
							while($row=mysql_fetch_array($re)){
								if($over_arr[$row['MemberNo']] == null){
									$over_arr[$row['MemberNo']] = array();
									$over_arr[$row['MemberNo']]['over_day_real'] = 0;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_day_confirm'] = 0;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_time_real'] = 0;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_time_confirm'] = 0;	//실제 일한 날짜
									$over_check = false;
								}

								if( $row['termTime'] >= $row['min_MINUTE'] ){	//최소 시간보다 클때

									$over_arr[$row['MemberNo']]['over_day_real']++;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_day_confirm']++;	//인정 날짜
									$over_arr[$row['MemberNo']]['over_time_real'] += $row['termTime'];	//실제 야근시간

									if( $row['termTime'] > $row['max_MINUTE'] ){	//하루 최대시간 보다 많을때
										$row['termTime'] = $row['max_MINUTE'];
									}

									$over_arr[$row['MemberNo']]['over_time_confirm'] += $row['termTime'];	//인정 야근시간
									if($over_arr[$row['MemberNo']]['over_time_confirm'] > $row['max_time']){	//총 야근시간이 최대 인정시간보다 많을때
										if($over_check and strpos($row['max_MINUTE'], 'C') === false){
											$over_arr[$row['MemberNo']]['over_day_confirm']--;	//인정 날짜
										}
										$over_check = true;
										//$over_arr[$row['MemberNo']]['over_time_confirm'] = $row['max_time'];	//야근시간은 인정시간만큼
									}
								}
							}
							//print_r($over_arr);

							foreach ($over_arr as $key => $value) {
								$azsql = "
									update worker_total_tbl set
										over_time_real = ".$over_arr[$key]['over_time_real']."
										, over_time_confirm = ".$over_arr[$key]['over_time_confirm']."
										, over_day_real = ".$over_arr[$key]['over_day_real']."
										, over_day_confirm = ".$over_arr[$key]['over_day_confirm']."
									where
										date_y = ".$set_year."
										AND date_m = ".$set_month."
										AND memberno = '".$key."'
								";
								if($action){
									mysql_query($azsql,$db);
								}else{
									echo '<br>'.$azsql;
								}
							}
						}else{
							$azsql = "
								UPDATE
									worker_total_tbl A
									, (
										SELECT
											(SUBSTRING_INDEX(total_time, ':', 1) * 60 + SUBSTRING_INDEX(total_time, ':', -1) * 1 ) AS over_time_real
											, (SUBSTRING_INDEX(total_tmp_apply_time, ':', 1) * 60 + SUBSTRING_INDEX(total_tmp_apply_time, ':', -1) * 1 ) AS over_time_confirm
											, ( weekday_count + holyday_count ) AS over_day_real
											, ( weekday_count + holyday_count - IFNULL(daycount, 0) ) AS over_day_confirm
											, memberno
											, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%Y')*1 AS date_y
											, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%m')*1 AS date_m
										FROM
											overtime_save_new_tbl
										WHERE
											DATE = '".$set_date_bar."'
									) B
								SET
									A.over_time_real = B.over_time_real
									, A.over_time_confirm = B.over_time_confirm
									, A.over_day_real = B.over_day_real
									, A.over_day_confirm = B.over_day_confirm
								WHERE
									A.date_y = B.date_y
									AND A.date_m = B.date_m
									AND A.memberno = B.memberno
							;";
							if($action){
								mysql_query($azsql,$db);
							}else{
								echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
								echo $azsql;
							}
						}

						//평균 추가
						/* 평균 연장근로시간 실제 - 분 */
						$azsql = "
							delete from worker_average_tbl where date_y = ".$set_year." AND date_m = ".$set_month."
						";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>평균 전체 삭제<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, MAX(date_m) AS date_m
								, 'dept'
								, dept_code
								, ROUND( SUM(over_time_real) / COUNT(dept_code) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(dept_code) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_code) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
								and dept_code is not null
							GROUP BY
								dept_code
							;
						";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>부서 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, MAX(date_m) AS date_m
								, 'rank'
								, rank_code
								, ROUND( SUM(over_time_real) / COUNT(rank_code) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(rank_code) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(rank_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(rank_code) , 1) + ROUND( SUM(login_cnt) / COUNT(rank_code) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
								and rank_code is not null
							GROUP BY
								rank_code
							;
						";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>직위 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, date_m
								, 'all'
								, 'all'
								, ROUND( SUM(over_time_real) / COUNT(date_m) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(date_m) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(date_m) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(date_m) , 1) + ROUND( SUM(login_cnt) / COUNT(date_m) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
							GROUP BY
								date_m
							;
						";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>전체 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, date_m
								, 'dept_top'
								, dept_top_code
								, ROUND( SUM(over_time_real) / COUNT(dept_top_code) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(dept_top_code) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_top_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_top_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_top_code) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
								and dept_top_code is not null
							GROUP BY
								dept_top_code
							;
						";
						if($action){
							mysql_query($azsql,$db);
						}else{
							echo '<br><br>본부 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}

						echo '<br><br>'.$set_date.' finish';
					}

					//장헌 worker 입력
					/*장헌 정보 셋팅*/

					/*장헌 DB정보*/
					$db_hostname_jang ='erp.jangheon.co.kr';
					$db_database_jang = 'hanmacerp';
					$db_username_jang = 'root';
					$db_password_jang = 'erp';

					/*장헌 DB연결----------------------------------------------------------------------*/
					$db_jang	= mysql_connect($db_hostname_jang,$db_username_jang,$db_password_jang);
						if(!$db_jang) die ("Unable to connect to MySql : ".mysql_error());
					mysql_select_db($db_database_jang);
					mysql_set_charset("utf-8",$db_jang);
					mysql_query("set names utf8");

					for($c=0; $c<2; $c++){
						if($c == 0){
							$set_date = $set_date_ori;
						}else{
							$set_date = date("Ym",strtotime("-".$c." month", strtotime($set_date_ori."01")));
						}

						$set_date_bar = substr($set_date,0,4)."-".substr($set_date,4,2);
						$set_year = substr($set_date,0,4);
						$set_month = (int)substr($set_date,4,2);
						$set_date_last = $set_date.date('t', strtotime($set_date_bar."-01"));
						$set_date_last_bar = $set_date_bar.'-'.date('t', strtotime($set_date_bar."-01"));
						$set_month_Pre = date("Y-m",strtotime("-1 month", strtotime($set_date."01")));

						/* 기간 월별로 나누기 */
						$azsql_s = "
							SELECT *, date_format(start_time,'%Y-%m') as s_ym, date_format(end_time,'%Y-%m') as e_ym FROM userstate_tbl
							WHERE
								state = 1
								AND ( date_format(start_time,'%Y%m') = '".$set_date."' OR date_format(end_time,'%Y%m') = '".$set_date."' )
								AND date_format(start_time,'%Y%m') != date_format(end_time,'%Y%m')
						;";
						if(!$action){
							echo '<br><br>기간 월별로 나누기<br>';
							echo $azsql_s;
						}

						$sql_arr = array();
						$re = mysql_query($azsql_s,$db);
						while($row=mysql_fetch_array($re)){
							/* 나누는거 해야함. */
							//print_r($row);
							if($row["s_ym"] == $set_date_bar){
								$azsql = "
									update userstate_tbl set start_time = '".$row["e_ym"]."-01' where num = ".$row["num"]."
								;";
								array_push($sql_arr, $azsql);
								$azsql = "
									insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
									values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["start_time"]."', '".$row["s_ym"].'-'.date('t', strtotime($row["start_time"]))."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
								;";
								array_push($sql_arr, $azsql);
							}else{
								$azsql = "
									update userstate_tbl set end_time = '".$row["s_ym"]."-".date('t', strtotime($row["start_time"]))."' where num = '".$row["num"]."'
								;";
								array_push($sql_arr, $azsql);
								$azsql = "
									insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
									values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["e_ym"]."-01', '".$row["end_time"]."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
								;";
								array_push($sql_arr, $azsql);
							}
						}

						for($i=0; $i<count($sql_arr); $i++){
							if($action){
								mysql_query($sql_arr[$i],$db_jang);
							}else{
								echo '<br>'.$sql_arr[$i];
							}
						}

						$azsql = "
							select
								member_id as EMP_NO
								, LPAD(DEPT_CODE,2,'0') AS DEPT_CODE
								, ( select code from total_systemconfig_tbl B where syskey = 'RANK' and comp_code = '40' and sys_code = '0' and B.name = A.rank_name ORDER BY CODE LIMIT 1 ) AS RANK_CODE
							from
								total_member_tbl A
							WHERE
								sys_comp_code = '40'
								and working_comp = '40'
								and join_date <= '".$set_date_last_bar."'
								and (
									retire_date = '0000-00-00'
									or retire_date > '".$set_date_bar."-01'
								)
							;
						";
						if(!$action){
							echo '<br><br>장헌인원<br>';
							echo $azsql;
						}
						//echo $azsql;

						$member_info = array();
						$re = mysql_query($azsql,$db);
						while($row=mysql_fetch_array($re)){
							$member_info[$row["EMP_NO"]] = $row;
						}

						/*월 추가*/
						$azsql = "
							delete from worker_date_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
						;";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>월 삭제<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_date_tbl (date_y, date_m, date_d, week, holy )
							SELECT
								date_y
								, date_m
								, COUNT(date_m) AS date_d
								, SUM(week) AS week
								, (SELECT COUNT(date) FROM holyday_tbl B WHERE a.date_y = YEAR(B.date) AND A.date_m = MONTH(B.date) and DAYOFWEEK( B.date ) in (2,3,4,5,6) ) AS holy
							FROM (
								SELECT a.date_ymd, YEAR(a.date_ymd) AS date_y, MONTH(a.date_ymd) AS date_m, IF( DAYOFWEEK( a.date_ymd ) = 1 OR DAYOFWEEK( a.date_ymd ) = 7, 1, 0) AS week
								FROM (
								SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
								SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
								UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
								) AS a
								CROSS JOIN (
								SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
								UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
								) AS b
								CROSS JOIN (
								SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
								UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
								) AS c
								) AS a
								WHERE 1 = 1
								AND a.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
							) a GROUP BY date_y, date_m
						;";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>월 추가<br>';
							echo $azsql;
						}

						/*사번별 추가*/

						$azsql = "
							delete from worker_total_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
						;";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>사번별 삭제<br>';
							echo $azsql;
						}

						if(!$action){
							echo '<br><br>사번별 추가<br>';
						}
						foreach($member_info as $key => $value){
							$azsql = "
								INSERT INTO worker_total_tbl (memberno, date_y, date_m, dept_top_code, dept_code, rank_code)
								SELECT '$key', ".$set_year.", ".$set_month.", (SELECT Code FROM systemconfig_tbl WHERE SysKey = 'GroupCode_Top' AND CodeORName LIKE CONCAT( '%g', '".$value['DEPT_CODE']."'*1, 'g%' )) , '".$value['DEPT_CODE']."', '".$value['RANK_CODE']."' FROM dual ;
							";
							//echo '<br>'.$azsql.'<br>';
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br>'.$azsql;
							}
						}

						/*연차 1, 18, 30, 31 - 주말,휴일 제외*/
						$azsql = "
							UPDATE worker_total_tbl BB SET vacation1 = IFNULL((
								SELECT SUM(vacation1)
								FROM (
									SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
									FROM (
										SELECT
											memberno
											, start_time
											, end_time
											, (
												CASE
													WHEN state = 1 THEN IF(note LIKE '%반차%', 4, 8)
													WHEN state = 30 THEN 4
													WHEN state = 31 THEN 4
													ELSE sub_code
												END
											) * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
										FROM (
											SELECT
												memberno
												, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
												, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
												, state
												, note
												, sub_code
											FROM
												userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (1, 18, 30, 31)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
										) AAA
										ORDER BY vacation desc
									) A
									, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT '1986-12-31' FROM dual ) B
									GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
								) AA
								WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
								GROUP BY memberno, date_y, date_m
							), 0)
							WHERE date_y = ".$set_year." AND date_m = ".$set_month."
						;";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>연차 1, 18, 30, 31 - 주말,휴일 제외<br>';
							echo $azsql;
						}

						/*기타 주말제외. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가), 10:대기, 17:기타*/
						$azsql = "
							UPDATE worker_total_tbl BB SET vacation2 = IFNULL((
								SELECT SUM(vacation1)
								FROM (
									SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
									FROM (
										SELECT
											memberno
											, start_time
											, end_time
											, 8 * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
										FROM (
											SELECT
												memberno
												, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
												, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
											FROM
												userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (5,6,7,8,10,17)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
										) AAA
										ORDER BY vacation desc
									) A
									, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT '1986-12-31' FROM dual ) B
									GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
								) AA
								WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
								GROUP BY memberno, date_y, date_m
							), 0)
							WHERE date_y = ".$set_year." AND date_m = ".$set_month."
						;";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>기타 5,6,7,8,10,17 주말제외<br>';
							echo $azsql;
						}

						/*비고 입력. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가),17:기타, 20:합사, 21:합사, 9:파견, 15:파견*/
						$azsql = "
							UPDATE
								worker_total_tbl A
								, (
									SELECT
										memberno
										, ".$set_year." AS date_y
										, ".$set_month." AS date_m
										, CONCAT(
											IF(SUM(state_1) > 0, '_경조', '')
											, IF(SUM(state_2) > 0, '_보건', '')
											, IF(SUM(state_3) > 0, '_출산', '')
											, IF(SUM(state_4) > 0, '_특별', '')
											, IF(SUM(state_5) > 0, '_훈련', '')
											, IF(SUM(state_6) > 0, '_교육', '')
											, IF(SUM(state_7) > 0, '_기타', '')
											, IF(SUM(state_8) > 0, '_합사', '')
											, IF(SUM(state_9) > 0, '_파견', '')
										) AS etc
									FROM
										(

											SELECT
												memberno
												, IF(state = 7, 1, 0) AS state_1
												, 0 AS state_2
												, 0 AS state_3
												, 0 AS state_4
												, IF(state = 5, 1, 0) AS state_5
												, IF(state = 6, 1, 0) AS state_6
												, 0 AS state_7
												, 0 AS state_8
												, 0 AS state_9
											FROM userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (5,6,7)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )

											UNION ALL

											SELECT
												memberno
												, 0
												, state_2
												, state_3
												, state_4
												, 0
												, 0
												, state - state_2 - state_3 - state_4
												, 0
												, 0
											FROM (
												SELECT
													memberno
													, IF(note LIKE '%보건%', 1, 0) AS state_2
													, IF(note LIKE '%출산%', 1, 0) AS state_3
													, IF(note LIKE '%특별%', 1, 0) AS state_4
													, 1 AS state
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (8,17)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) A

											UNION ALL

											SELECT
												memberno
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 1
												, 0
											FROM userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (20, 21)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											UNION ALL

											SELECT
												memberno
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 1
											FROM userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (9, 15)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
										) C
									GROUP BY memberno
								) B
							SET
								A.etc = B.etc
							WHERE
								A.date_y = B.date_y
								AND A.date_m = B.date_m
								AND A.memberno = B.memberno
							;
						";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>비고 입력<br>';
							echo $azsql;
						}

						/*지각 횟수*/
						$azsql = "
							UPDATE
								worker_total_tbl BB
							SET
								tardy = IFNULL((
									SELECT tardy FROM (
										SELECT
											A.memberno
											, YEAR(A.entrytime) AS date_y
											, MONTH(A.entrytime) AS date_m
											, SUM( CASE WHEN date_format(A.entrytime,'%H') >= IFNULL(B.tardy_h, 9) AND date_format(A.entrytime,'%i') > IFNULL(B.tardy_m, 0) THEN IF( ( SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(A.entrytime,'%Y-%m-%d') ) > 0 , 0, 1) ELSE 0 end) AS tardy
										FROM (
											SELECT A.memberno, A.entrytime FROM (
												SELECT
													memberno
													, entrytime
												FROM
													dallyproject_tbl
												WHERE
													EntryTime BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
													AND DAYOFWEEK(entrytime) NOT IN (1,7)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) A
											left join
											( SELECT memberno, start_time, end_time FROM userstate_tbl WHERE state IN ( 1, 18, 17, 5, 6, 2, 3 ) AND ( date_format( start_time , '%Y%m') = '".$set_date."' OR date_format( end_time , '%Y%m') = '".$set_date."' ) ) B
											ON
												A.memberno = B.memberno
												AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.start_time AND B.end_time
											WHERE B.memberno IS null
										) A
										left join
										( SELECT * FROM worker_tardy_tbl WHERE '".$set_date."' between date_format(s_date, '%Y%m') and date_format(e_date, '%Y%m') ) B
										ON
											A.memberno = B.memberno
											AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.s_date AND B.e_date
										GROUP BY A.memberno
									) AA
									WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
								), 0)
								, BB.login_cnt = (select count(MemberNo) from dallyproject_tbl where MemberNo = BB.MemberNo and date_format(EntryTime , '%Y-%m-%d') IN (
									SELECT date_format(DATE , '%Y-%m-%d') FROM (
										SELECT date FROM holyday_tbl WHERE DATE BETWEEN '".$set_date_bar."-01' AND '".$set_date_last_bar."'
										UNION ALL
										SELECT date_format(date_ymd , '%Y-%m-%d') FROM (
											SELECT C.date_ymd, YEAR(C.date_ymd) AS date_y, MONTH(C.date_ymd) AS date_m, IF( DAYOFWEEK( C.date_ymd ) = 1 OR DAYOFWEEK( C.date_ymd ) = 7, 1, 0) AS week
											FROM (
												SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
												SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS a
												CROSS JOIN (
												SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS b
												CROSS JOIN (
												SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS c
											) AS C
											WHERE
												C.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
										) D WHERE week = 1
									) E GROUP BY date
								))
							WHERE date_y = ".$set_year." AND date_m = ".$set_month."
						;";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>지각 횟수 - 출장 외출 그런거 안따짐.<br>';
							echo $azsql;
						}

						/*연장근로시간 - 실제,인정, 연장근로일수 - 실제,인정*/
						if(date("Ym",strtotime("-2 month", strtotime(date('Ym')."01"))) < $set_date){
							$azsql = "
								SELECT C.MemberNo, EntryTime, termTime, min_MINUTE, max_MINUTE, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_time, C.RankCode FROM
									(
										SELECT
											MemberNo
											, ( SELECT RankCode FROM member_tbl WHERE MemberNo = A.MemberNo ) AS RankCode
											, EntryTime
											, date_format(OverTime,'%Y-%m-%d') AS over_date
											, IF(
												DAYOFWEEK = 0 and date_format(OverTime,'%H:%i') = '00:00'
												, 0
												, TIMESTAMPDIFF(
													MINUTE
													, IF(
														DAYOFWEEK = 0
														, OverTime
														, IF(
															date_format(EntryTime,'%H:%i') < B.start_time
															, CONCAT(date_format(EntryTime,'%Y-%m-%d '), B.start_time, ':00')
															, EntryTime
														)
													)
													, LeaveTime
												)
											) AS termTime
											, (SUBSTRING_INDEX(min_time, ':', 1) * 60 + SUBSTRING_INDEX(min_time, ':', -1) * 1 ) AS min_MINUTE
											, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_MINUTE
										FROM
											(
												SELECT
													MemberNo
													, EntryTime
													, IF( DAYOFWEEK( EntryTime ) = 1 OR DAYOFWEEK( EntryTime ) = 7, 1, (SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(dallyproject_tbl.EntryTime,'%Y-%m-%d ') ) ) AS DAYOFWEEK
													, OverTime
													, LeaveTime
												FROM dallyproject_tbl
												WHERE EntryTime BETWEEN '".$set_month_Pre."-21 00:00:00' AND '".$set_date_bar."-20 23:59:59' AND MODIFY = 1
												ORDER BY MemberNo
											) A
											, (
												SELECT * FROM overtime_basic_new_tbl WHERE code in (100, 101)
											) B
										WHERE
											A.DAYOFWEEK = B.code or (A.DAYOFWEEK+100) = B.code
									) C
									, (
										SELECT * FROM overtime_basic_new_tbl WHERE code BETWEEN 102 AND 105
										union all
										select 999, '', '', '999:00', '', '-C0-C1-C2-C4-C5-C6-C7-C8-C9-C3A-' from dual
									) D
								WHERE
									D.RankCode LIKE CONCAT('%-', C.RankCode, '-%')
								order by MemberNo, EntryTime
							;";
							if($action){
							}else{
								echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
								echo $azsql;
							}

							$over_arr = array();

							$re = mysql_query($azsql,$db_jang);
							while($row=mysql_fetch_array($re)){
								if($over_arr[$row['MemberNo']] == null){
									$over_arr[$row['MemberNo']] = array();
									$over_arr[$row['MemberNo']]['over_day_real'] = 0;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_day_confirm'] = 0;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_time_real'] = 0;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_time_confirm'] = 0;	//실제 일한 날짜
									$over_check = false;
								}

								if( $row['termTime'] >= $row['min_MINUTE'] ){	//최소 시간보다 클때

									$over_arr[$row['MemberNo']]['over_day_real']++;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_day_confirm']++;	//인정 날짜
									$over_arr[$row['MemberNo']]['over_time_real'] += $row['termTime'];	//실제 야근시간

									if( $row['termTime'] > $row['max_MINUTE'] ){	//하루 최대시간 보다 많을때
										$row['termTime'] = $row['max_MINUTE'];
									}

									$over_arr[$row['MemberNo']]['over_time_confirm'] += $row['termTime'];	//인정 야근시간
									if($over_arr[$row['MemberNo']]['over_time_confirm'] > $row['max_time']){	//총 야근시간이 최대 인정시간보다 많을때
										if($over_check and strpos($row['max_MINUTE'], 'C') === false){
											$over_arr[$row['MemberNo']]['over_day_confirm']--;	//인정 날짜
										}
										$over_check = true;
										//$over_arr[$row['MemberNo']]['over_time_confirm'] = $row['max_time'];	//야근시간은 인정시간만큼
									}
								}
							}
							//print_r($over_arr);

							foreach ($over_arr as $key => $value) {
								$azsql = "
									update worker_total_tbl set
										over_time_real = ".$over_arr[$key]['over_time_real']."
										, over_time_confirm = ".$over_arr[$key]['over_time_confirm']."
										, over_day_real = ".$over_arr[$key]['over_day_real']."
										, over_day_confirm = ".$over_arr[$key]['over_day_confirm']."
									where
										date_y = ".$set_year."
										AND date_m = ".$set_month."
										AND memberno = '".$key."'
								";
								if($action){
									mysql_query($azsql,$db_jang);
								}else{
									echo '<br>'.$azsql;
								}
							}
						}else{
							$azsql = "
								UPDATE
									worker_total_tbl A
									, (
										SELECT
											(SUBSTRING_INDEX(total_time, ':', 1) * 60 + SUBSTRING_INDEX(total_time, ':', -1) * 1 ) AS over_time_real
											, (SUBSTRING_INDEX(total_tmp_apply_time, ':', 1) * 60 + SUBSTRING_INDEX(total_tmp_apply_time, ':', -1) * 1 ) AS over_time_confirm
											, ( weekday_count + holyday_count ) AS over_day_real
											, ( weekday_count + holyday_count - IFNULL(daycount, 0) ) AS over_day_confirm
											, memberno
											, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%Y')*1 AS date_y
											, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%m')*1 AS date_m
										FROM
											overtime_save_new_tbl
										WHERE
											DATE = '".$set_date_bar."'
									) B
								SET
									A.over_time_real = B.over_time_real
									, A.over_time_confirm = B.over_time_confirm
									, A.over_day_real = B.over_day_real
									, A.over_day_confirm = B.over_day_confirm
								WHERE
									A.date_y = B.date_y
									AND A.date_m = B.date_m
									AND A.memberno = B.memberno
							;";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
								echo $azsql;
							}
						}


						//평균 추가
						/* 평균 연장근로시간 실제 - 분 */
						$azsql = "
							delete from worker_average_tbl where date_y = ".$set_year." AND date_m = ".$set_month."
						";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>평균 전체 삭제<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, MAX(date_m) AS date_m
								, 'dept'
								, dept_code
								, ROUND( SUM(over_time_real) / COUNT(dept_code) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(dept_code) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_code) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
								and dept_code is not null
							GROUP BY
								dept_code
							;
						";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>부서 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, MAX(date_m) AS date_m
								, 'rank'
								, rank_code
								, ROUND( SUM(over_time_real) / COUNT(rank_code) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(rank_code) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(rank_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(rank_code) , 1) + ROUND( SUM(login_cnt) / COUNT(rank_code) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
								and rank_code is not null
							GROUP BY
								rank_code
							;
						";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>직위 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, date_m
								, 'all'
								, 'all'
								, ROUND( SUM(over_time_real) / COUNT(date_m) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(date_m) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(date_m) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(date_m) , 1) + ROUND( SUM(login_cnt) / COUNT(date_m) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
							GROUP BY
								date_m
							;
						";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>전체 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, date_m
								, 'dept_top'
								, dept_top_code
								, ROUND( SUM(over_time_real) / COUNT(dept_top_code) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(dept_top_code) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_top_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_top_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_top_code) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
								and dept_top_code is not null
							GROUP BY
								dept_top_code
							;
						";
						if($action){
							mysql_query($azsql,$db_jang);
						}else{
							echo '<br><br>본부 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}
						echo '<br><br>'.$set_date.' jang finish';
					}

					//PTC worker 입력
					/*PTC 정보 셋팅*/

					/*PTC DB정보*/
					$db_hostname_PTC ='erp.pre-cast.co.kr';
					$db_database_PTC = 'hanmacerp';
					$db_username_PTC = 'root';
					$db_password_PTC = 'erp';

					/*PTC DB연결----------------------------------------------------------------------*/
					$db_PTC	= mysql_connect($db_hostname_PTC,$db_username_PTC,$db_password_PTC);
						if(!$db_PTC) die ("Unable to connect to MySql : ".mysql_error());
					mysql_select_db($db_database_PTC);
					mysql_set_charset("utf-8",$db_PTC);
					mysql_query("set names utf8");

					for($c=0; $c<2; $c++){
						if($c == 0){
							$set_date = $set_date_ori;
						}else{
							$set_date = date("Ym",strtotime("-".$c." month", strtotime($set_date_ori."01")));
						}

						$set_date_bar = substr($set_date,0,4)."-".substr($set_date,4,2);
						$set_year = substr($set_date,0,4);
						$set_month = (int)substr($set_date,4,2);
						$set_date_last = $set_date.date('t', strtotime($set_date_bar."-01"));
						$set_date_last_bar = $set_date_bar.'-'.date('t', strtotime($set_date_bar."-01"));
						$set_month_Pre = date("Y-m",strtotime("-1 month", strtotime($set_date."01")));

						/* 기간 월별로 나누기 */
						$azsql_s = "
							SELECT *, date_format(start_time,'%Y-%m') as s_ym, date_format(end_time,'%Y-%m') as e_ym FROM userstate_tbl
							WHERE
								state = 1
								AND ( date_format(start_time,'%Y%m') = '".$set_date."' OR date_format(end_time,'%Y%m') = '".$set_date."' )
								AND date_format(start_time,'%Y%m') != date_format(end_time,'%Y%m')
						;";
						if(!$action){
							echo '<br><br>기간 월별로 나누기<br>';
							echo $azsql_s;
						}

						$sql_arr = array();
						$re = mysql_query($azsql_s,$db);
						while($row=mysql_fetch_array($re)){
							/* 나누는거 해야함. */
							//print_r($row);
							if($row["s_ym"] == $set_date_bar){
								$azsql = "
									update userstate_tbl set start_time = '".$row["e_ym"]."-01' where num = ".$row["num"]."
								;";
								array_push($sql_arr, $azsql);
								$azsql = "
									insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
									values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["start_time"]."', '".$row["s_ym"].'-'.date('t', strtotime($row["start_time"]))."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
								;";
								array_push($sql_arr, $azsql);
							}else{
								$azsql = "
									update userstate_tbl set end_time = '".$row["s_ym"]."-".date('t', strtotime($row["start_time"]))."' where num = '".$row["num"]."'
								;";
								array_push($sql_arr, $azsql);
								$azsql = "
									insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
									values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["e_ym"]."-01', '".$row["end_time"]."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
								;";
								array_push($sql_arr, $azsql);
							}
						}

						for($i=0; $i<count($sql_arr); $i++){
							if($action){
								mysql_query($sql_arr[$i],$db_PTC);
							}else{
								echo '<br>'.$sql_arr[$i];
							}
						}

						$azsql = "
							select
								member_id as EMP_NO
								, LPAD(DEPT_CODE,2,'0') AS DEPT_CODE
								, ( select code from total_systemconfig_tbl B where syskey = 'RANK' and comp_code = '60' and sys_code = '0' and B.name = A.rank_name ORDER BY CODE LIMIT 1 ) AS RANK_CODE
							from
								total_member_tbl A
							WHERE
								sys_comp_code = '60'
								and working_comp = '60'
								and join_date <= '".$set_date_last_bar."'
								and (
									retire_date = '0000-00-00'
									or retire_date > '".$set_date_bar."-01'
								)
							;
						";
						if(!$action){
							echo '<br><br>PTC인원<br>';
							echo $azsql;
						}
						//echo $azsql;

						$member_info = array();
						$re = mysql_query($azsql,$db);
						while($row=mysql_fetch_array($re)){
							$member_info[$row["EMP_NO"]] = $row;
						}

						/*월 추가*/
						$azsql = "
							delete from worker_date_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
						;";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>월 삭제<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_date_tbl (date_y, date_m, date_d, week, holy )
							SELECT
								date_y
								, date_m
								, COUNT(date_m) AS date_d
								, SUM(week) AS week
								, (SELECT COUNT(date) FROM holyday_tbl B WHERE a.date_y = YEAR(B.date) AND A.date_m = MONTH(B.date) and DAYOFWEEK( B.date ) in (2,3,4,5,6) ) AS holy
							FROM (
								SELECT a.date_ymd, YEAR(a.date_ymd) AS date_y, MONTH(a.date_ymd) AS date_m, IF( DAYOFWEEK( a.date_ymd ) = 1 OR DAYOFWEEK( a.date_ymd ) = 7, 1, 0) AS week
								FROM (
								SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
								SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
								UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
								) AS a
								CROSS JOIN (
								SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
								UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
								) AS b
								CROSS JOIN (
								SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
								UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
								) AS c
								) AS a
								WHERE 1 = 1
								AND a.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
							) a GROUP BY date_y, date_m
						;";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>월 추가<br>';
							echo $azsql;
						}

						/*사번별 추가*/

						$azsql = "
							delete from worker_total_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
						;";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>사번별 삭제<br>';
							echo $azsql;
						}

						if(!$action){
							echo '<br><br>사번별 추가<br>';
						}
						foreach($member_info as $key => $value){
							$azsql = "
								INSERT INTO worker_total_tbl (memberno, date_y, date_m, dept_top_code, dept_code, rank_code)
								SELECT '$key', ".$set_year.", ".$set_month.", (SELECT Code FROM systemconfig_tbl WHERE SysKey = 'GroupCode_Top' AND CodeORName LIKE CONCAT( '%g', '".$value['DEPT_CODE']."'*1, 'g%' )) , '".$value['DEPT_CODE']."', '".$value['RANK_CODE']."' FROM dual ;
							";
							//echo '<br>'.$azsql.'<br>';
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br>'.$azsql;
							}
						}

						/*연차 1, 18, 30, 31 - 주말,휴일 제외*/
						$azsql = "
							UPDATE worker_total_tbl BB SET vacation1 = IFNULL((
								SELECT SUM(vacation1)
								FROM (
									SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
									FROM (
										SELECT
											memberno
											, start_time
											, end_time
											, (
												CASE
													WHEN state = 1 THEN IF(note LIKE '%반차%', 4, 8)
													WHEN state = 30 THEN 4
													WHEN state = 31 THEN 4
													ELSE sub_code
												END
											) * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
										FROM (
											SELECT
												memberno
												, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
												, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
												, state
												, note
												, sub_code
											FROM
												userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (1, 18, 30, 31)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
										) AAA
										ORDER BY vacation desc
									) A
									, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT '1986-12-31' FROM dual ) B
									GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
								) AA
								WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
								GROUP BY memberno, date_y, date_m
							), 0)
							WHERE date_y = ".$set_year." AND date_m = ".$set_month."
						;";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>연차 1, 18, 30, 31 - 주말,휴일 제외<br>';
							echo $azsql;
						}

						/*기타 주말제외. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가), 10:대기, 17:기타*/
						$azsql = "
							UPDATE worker_total_tbl BB SET vacation2 = IFNULL((
								SELECT SUM(vacation1)
								FROM (
									SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
									FROM (
										SELECT
											memberno
											, start_time
											, end_time
											, 8 * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
										FROM (
											SELECT
												memberno
												, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
												, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
											FROM
												userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (5,6,7,8,10,17)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
										) AAA
										ORDER BY vacation desc
									) A
									, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT '1986-12-31' FROM dual ) B
									GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
								) AA
								WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
								GROUP BY memberno, date_y, date_m
							), 0)
							WHERE date_y = ".$set_year." AND date_m = ".$set_month."
						;";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>기타 5,6,7,8,10,17 주말제외<br>';
							echo $azsql;
						}

						/*비고 입력. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가),17:기타, 20:합사, 21:합사, 9:파견, 15:파견*/
						$azsql = "
							UPDATE
								worker_total_tbl A
								, (
									SELECT
										memberno
										, ".$set_year." AS date_y
										, ".$set_month." AS date_m
										, CONCAT(
											IF(SUM(state_1) > 0, '_경조', '')
											, IF(SUM(state_2) > 0, '_보건', '')
											, IF(SUM(state_3) > 0, '_출산', '')
											, IF(SUM(state_4) > 0, '_특별', '')
											, IF(SUM(state_5) > 0, '_훈련', '')
											, IF(SUM(state_6) > 0, '_교육', '')
											, IF(SUM(state_7) > 0, '_기타', '')
											, IF(SUM(state_8) > 0, '_합사', '')
											, IF(SUM(state_9) > 0, '_파견', '')
										) AS etc
									FROM
										(

											SELECT
												memberno
												, IF(state = 7, 1, 0) AS state_1
												, 0 AS state_2
												, 0 AS state_3
												, 0 AS state_4
												, IF(state = 5, 1, 0) AS state_5
												, IF(state = 6, 1, 0) AS state_6
												, 0 AS state_7
												, 0 AS state_8
												, 0 AS state_9
											FROM userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (5,6,7)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )

											UNION ALL

											SELECT
												memberno
												, 0
												, state_2
												, state_3
												, state_4
												, 0
												, 0
												, state - state_2 - state_3 - state_4
												, 0
												, 0
											FROM (
												SELECT
													memberno
													, IF(note LIKE '%보건%', 1, 0) AS state_2
													, IF(note LIKE '%출산%', 1, 0) AS state_3
													, IF(note LIKE '%특별%', 1, 0) AS state_4
													, 1 AS state
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (8,17)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) A

											UNION ALL

											SELECT
												memberno
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 1
												, 0
											FROM userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (20, 21)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											UNION ALL

											SELECT
												memberno
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 0
												, 1
											FROM userstate_tbl
											WHERE
												'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
												AND state IN (9, 15)
												AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
										) C
									GROUP BY memberno
								) B
							SET
								A.etc = B.etc
							WHERE
								A.date_y = B.date_y
								AND A.date_m = B.date_m
								AND A.memberno = B.memberno
							;
						";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>비고 입력<br>';
							echo $azsql;
						}

						/*지각 횟수*/
						$azsql = "
							UPDATE
								worker_total_tbl BB
							SET
								tardy = IFNULL((
									SELECT tardy FROM (
										SELECT
											A.memberno
											, YEAR(A.entrytime) AS date_y
											, MONTH(A.entrytime) AS date_m
											, SUM( CASE WHEN date_format(A.entrytime,'%H') >= IFNULL(B.tardy_h, 9) AND date_format(A.entrytime,'%i') > IFNULL(B.tardy_m, 0) THEN IF( ( SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(A.entrytime,'%Y-%m-%d') ) > 0 , 0, 1) ELSE 0 end) AS tardy
										FROM (
											SELECT A.memberno, A.entrytime FROM (
												SELECT
													memberno
													, entrytime
												FROM
													dallyproject_tbl
												WHERE
													EntryTime BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
													AND DAYOFWEEK(entrytime) NOT IN (1,7)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) A
											left join
											( SELECT memberno, start_time, end_time FROM userstate_tbl WHERE state IN ( 1, 18, 17, 5, 6, 2, 3 ) AND ( date_format( start_time , '%Y%m') = '".$set_date."' OR date_format( end_time , '%Y%m') = '".$set_date."' ) ) B
											ON
												A.memberno = B.memberno
												AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.start_time AND B.end_time
											WHERE B.memberno IS null
										) A
										left join
										( SELECT * FROM worker_tardy_tbl WHERE '".$set_date."' between date_format(s_date, '%Y%m') and date_format(e_date, '%Y%m') ) B
										ON
											A.memberno = B.memberno
											AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.s_date AND B.e_date
										GROUP BY A.memberno
									) AA
									WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
								), 0)
								, BB.login_cnt = (select count(MemberNo) from dallyproject_tbl where MemberNo = BB.MemberNo and date_format(EntryTime , '%Y-%m-%d') IN (
									SELECT date_format(DATE , '%Y-%m-%d') FROM (
										SELECT date FROM holyday_tbl WHERE DATE BETWEEN '".$set_date_bar."-01' AND '".$set_date_last_bar."'
										UNION ALL
										SELECT date_format(date_ymd , '%Y-%m-%d') FROM (
											SELECT C.date_ymd, YEAR(C.date_ymd) AS date_y, MONTH(C.date_ymd) AS date_m, IF( DAYOFWEEK( C.date_ymd ) = 1 OR DAYOFWEEK( C.date_ymd ) = 7, 1, 0) AS week
											FROM (
												SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
												SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS a
												CROSS JOIN (
												SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS b
												CROSS JOIN (
												SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS c
											) AS C
											WHERE
												C.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
										) D WHERE week = 1
									) E GROUP BY date
								))
							WHERE date_y = ".$set_year." AND date_m = ".$set_month."
						;";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>지각 횟수 - 출장 외출 그런거 안따짐.<br>';
							echo $azsql;
						}

						/*연장근로시간 - 실제,인정, 연장근로일수 - 실제,인정*/
						if(date("Ym",strtotime("-2 month", strtotime(date('Ym')."01"))) < $set_date){
							$azsql = "
								SELECT C.MemberNo, EntryTime, termTime, min_MINUTE, max_MINUTE, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_time, C.RankCode FROM
									(
										SELECT
											MemberNo
											, ( SELECT RankCode FROM member_tbl WHERE MemberNo = A.MemberNo ) AS RankCode
											, EntryTime
											, date_format(OverTime,'%Y-%m-%d') AS over_date
											, IF(
												DAYOFWEEK = 0 and date_format(OverTime,'%H:%i') = '00:00'
												, 0
												, TIMESTAMPDIFF(
													MINUTE
													, IF(
														DAYOFWEEK = 0
														, OverTime
														, IF(
															date_format(EntryTime,'%H:%i') < B.start_time
															, CONCAT(date_format(EntryTime,'%Y-%m-%d '), B.start_time, ':00')
															, EntryTime
														)
													)
													, LeaveTime
												)
											) AS termTime
											, (SUBSTRING_INDEX(min_time, ':', 1) * 60 + SUBSTRING_INDEX(min_time, ':', -1) * 1 ) AS min_MINUTE
											, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_MINUTE
										FROM
											(
												SELECT
													MemberNo
													, EntryTime
													, IF( DAYOFWEEK( EntryTime ) = 1 OR DAYOFWEEK( EntryTime ) = 7, 1, (SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(dallyproject_tbl.EntryTime,'%Y-%m-%d ') ) ) AS DAYOFWEEK
													, OverTime
													, LeaveTime
												FROM dallyproject_tbl
												WHERE EntryTime BETWEEN '".$set_month_Pre."-21 00:00:00' AND '".$set_date_bar."-20 23:59:59' AND MODIFY = 1
												ORDER BY MemberNo
											) A
											, (
												SELECT * FROM overtime_basic_new_tbl WHERE code in (100, 101)
											) B
										WHERE
											A.DAYOFWEEK = B.code or (A.DAYOFWEEK+100) = B.code
									) C
									, (
										SELECT * FROM overtime_basic_new_tbl WHERE code BETWEEN 102 AND 105
										union all
										select 999, '', '', '999:00', '', '-C0-C1-C2-C3-C4-C5-C6-C7-C8-C9-' from dual
									) D
								WHERE
									D.RankCode LIKE CONCAT('%', C.RankCode, '%')
								order by MemberNo, EntryTime
							;";
							if($action){
							}else{
								echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
								echo $azsql;
							}

							$over_arr = array();

							$re = mysql_query($azsql,$db_PTC);
							while($row=mysql_fetch_array($re)){
								if($over_arr[$row['MemberNo']] == null){
									$over_arr[$row['MemberNo']] = array();
									$over_arr[$row['MemberNo']]['over_day_real'] = 0;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_day_confirm'] = 0;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_time_real'] = 0;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_time_confirm'] = 0;	//실제 일한 날짜
									$over_check = false;
								}

								if( $row['termTime'] >= $row['min_MINUTE'] ){	//최소 시간보다 클때

									$over_arr[$row['MemberNo']]['over_day_real']++;	//실제 일한 날짜
									$over_arr[$row['MemberNo']]['over_day_confirm']++;	//인정 날짜
									$over_arr[$row['MemberNo']]['over_time_real'] += $row['termTime'];	//실제 야근시간

									if( $row['termTime'] > $row['max_MINUTE'] ){	//하루 최대시간 보다 많을때
										$row['termTime'] = $row['max_MINUTE'];
									}

									$over_arr[$row['MemberNo']]['over_time_confirm'] += $row['termTime'];	//인정 야근시간
									if($over_arr[$row['MemberNo']]['over_time_confirm'] > $row['max_time']){	//총 야근시간이 최대 인정시간보다 많을때
										if($over_check and strpos($row['max_MINUTE'], 'C') === false){
											$over_arr[$row['MemberNo']]['over_day_confirm']--;	//인정 날짜
										}
										$over_check = true;
										//$over_arr[$row['MemberNo']]['over_time_confirm'] = $row['max_time'];	//야근시간은 인정시간만큼
									}
								}
							}
							//print_r($over_arr);

							foreach ($over_arr as $key => $value) {
								$azsql = "
									update worker_total_tbl set
										over_time_real = ".$over_arr[$key]['over_time_real']."
										, over_time_confirm = ".$over_arr[$key]['over_time_confirm']."
										, over_day_real = ".$over_arr[$key]['over_day_real']."
										, over_day_confirm = ".$over_arr[$key]['over_day_confirm']."
									where
										date_y = ".$set_year."
										AND date_m = ".$set_month."
										AND memberno = '".$key."'
								";
								if($action){
									mysql_query($azsql,$db_PTC);
								}else{
									echo '<br>'.$azsql;
								}
							}
						}else{
							$azsql = "
								UPDATE
									worker_total_tbl A
									, (
										SELECT
											(SUBSTRING_INDEX(total_time, ':', 1) * 60 + SUBSTRING_INDEX(total_time, ':', -1) * 1 ) AS over_time_real
											, (SUBSTRING_INDEX(total_tmp_apply_time, ':', 1) * 60 + SUBSTRING_INDEX(total_tmp_apply_time, ':', -1) * 1 ) AS over_time_confirm
											, ( weekday_count + holyday_count ) AS over_day_real
											, ( weekday_count + holyday_count - IFNULL(daycount, 0) ) AS over_day_confirm
											, memberno
											, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%Y')*1 AS date_y
											, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%m')*1 AS date_m
										FROM
											overtime_save_new_tbl
										WHERE
											DATE = '".$set_date_bar."'
									) B
								SET
									A.over_time_real = B.over_time_real
									, A.over_time_confirm = B.over_time_confirm
									, A.over_day_real = B.over_day_real
									, A.over_day_confirm = B.over_day_confirm
								WHERE
									A.date_y = B.date_y
									AND A.date_m = B.date_m
									AND A.memberno = B.memberno
							;";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
								echo $azsql;
							}
						}


						//평균 추가
						/* 평균 연장근로시간 실제 - 분 */
						$azsql = "
							delete from worker_average_tbl where date_y = ".$set_year." AND date_m = ".$set_month."
						";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>평균 전체 삭제<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, MAX(date_m) AS date_m
								, 'dept'
								, dept_code
								, ROUND( SUM(over_time_real) / COUNT(dept_code) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(dept_code) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_code) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
								and dept_code is not null
							GROUP BY
								dept_code
							;
						";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>부서 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, MAX(date_m) AS date_m
								, 'rank'
								, rank_code
								, ROUND( SUM(over_time_real) / COUNT(rank_code) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(rank_code) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(rank_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(rank_code) , 1) + ROUND( SUM(login_cnt) / COUNT(rank_code) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
								and rank_code is not null
							GROUP BY
								rank_code
							;
						";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>직위 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, date_m
								, 'all'
								, 'all'
								, ROUND( SUM(over_time_real) / COUNT(date_m) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(date_m) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(date_m) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(date_m) , 1) + ROUND( SUM(login_cnt) / COUNT(date_m) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
							GROUP BY
								date_m
							;
						";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>전체 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}

						$azsql = "
							INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
							SELECT
								MAX(date_y) AS date_y
								, date_m
								, 'dept_top'
								, dept_top_code
								, ROUND( SUM(over_time_real) / COUNT(dept_top_code) , 1) AS time_average
								, ROUND( SUM(over_day_real) / COUNT(dept_top_code) , 1) AS day_average
								, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_top_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_top_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_top_code) , 1) AS per_average
							FROM
								worker_total_tbl A
							WHERE
								date_y = ".$set_year."
								AND date_m = ".$set_month."
								and dept_top_code is not null
							GROUP BY
								dept_top_code
							;
						";
						if($action){
							mysql_query($azsql,$db_PTC);
						}else{
							echo '<br><br>본부 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
							echo $azsql;
						}
						echo '<br><br>'.$set_date.' PTC finish';
					}


					$mysql_end = mysql_close($db_jang);
					$mysql_end = mysql_close($db_PTC);
					$mysql_end = mysql_close($db);

					break;

				default:
					break;
			}
		}

		function REPORT_CHECK(){
			header('Content-Type: text/html; charset=UTF-8');
			extract($_REQUEST);
			include "../../../person_mng/inc/vacationfunction.php";
			include "../util/OracleClass.php";
			$this->oracle = new OracleClass($this->smarty, 'HANMAC');
			$ORACLE_SAMAN = new OracleClass($this->smarty, 'SAMAN');
			include "../inc/dbcon_JANG.inc";	//인트라넷 DB연결
			include "../inc/dbcon_PTC.inc";	//인트라넷 DB연결
			global $db;
			$action = true;	//	true	false

			if($action_test == 'test'){
				$action = false;
			}

			//$set_date = str_replace ( '-', '', $set_date) ;
			if($set_date == null or $set_date == ''){
				$set_date = date('Ym');
			}

			$set_date_ori = $set_date;
			for($c=0; $c<2; $c++){
				if($c == 0){
					$set_date = $set_date_ori;
				}else{
					$set_date = date("Ym",strtotime("-".$c." month", strtotime($set_date_ori."01")));
				}

				$set_date_bar = substr($set_date,0,4)."-".substr($set_date,4,2);
				$set_year = substr($set_date,0,4);
				$set_month = (int)substr($set_date,4,2);
				$set_date_last = $set_date.date('t', strtotime($set_date_bar."-01"));
				$set_date_last_bar = $set_date_bar.'-'.date('t', strtotime($set_date_bar."-01"));
				$set_month_Pre = date("Y-m",strtotime("-1 month", strtotime($set_date."01")));
			}
		}

		//화면에 출력할 데이터
		function SET_LIST( $LIST_MODE, $LIST_NAME, $daterow, $LIMIT_CNT ){
			$daterow_cnt = count($daterow);
			$this->smarty->assign( $LIST_NAME.'_CNT' , $daterow_cnt );
			/*
			if($daterow_cnt > $LIMIT_CNT and $LIST_MODE == 'limit' ){	//제한상태면 목록에서 안나오도록 제거
				$daterow[$daterow_cnt-1]['LAST'] = true;
				$daterow[$LIMIT_CNT-2] = $daterow[$daterow_cnt-1];
				for($i=($LIMIT_CNT-1); $i<$daterow_cnt; $i++){
					unset($daterow[$i]);
				}
			}
			*/
			$this->smarty->assign( $LIST_NAME.'_LIMIT' , $LIMIT_CNT );
			$this->smarty->assign( $LIST_NAME , $daterow );
		}

		//======================================================================================================

		function HangleEncode($item)
		{
				$result=trim(ICONV("EUC-KR","UTF-8",$item));
				if(trim($result)=="") 	$result="&nbsp";
				return $result;
		}

		function HangleEncodeUTF8_EUCKR($item)
		{
				$result=trim(ICONV("UTF-8","EUC-KR",$item));
				return $result;
		}

		function bear3StrCut($str,$len,$tail="..."){
			$rtn = array();
			return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str;
		}
		//=================================================
		// POST로 입력받은 자료를 처리하는 함수
		//=================================================
		function GetPOST_Item($Section)
		{
				$query_item=$_POST[$Section];
				$query_item=$this->HangleEncodeUTF8_EUCKR($query_item);
				return $query_item;
		}

		function PrintExcelHeader($filename)
		{

			$filename = $this->HangleEncodeUTF8_EUCKR($filename);
			if($this->excel != "")
			{
				header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
				header("Content-type:   application/x-msexcel; charset=utf-8");
				header("Content-Disposition: attachment; filename=\"$filename.xls\"");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false);
			}

		}

		function ExistFile($filename)
		{
			if(is_file($filename)==false){
				echo "파일없음";
				exit();
			}
		}

		function GetDateFormat($i_data)
		{
			if($i_data == "")
				return "";
			$data= str_replace("-", "", $i_data);
			$data= str_replace(".", "", $data);

			if(strlen($data) ==6)
				return substr($data,0,4).".".substr($data,4,2).".";
			else
				return substr($data,0,4).".".substr($data,4,2).".".substr($data,6,2).".";
		}

		function set_mobile_number($mobile){
			$re_mobile = '';
			if($mobile != ''){
				$mobile = str_replace(" ","",str_replace("-","",$mobile));
				$re_mobile = preg_replace("/(^02.{0}|^01.{1}|[0-9]{3})([0-9]+)([0-9]{4})/", "$1-$2-$3", $mobile);
			}
			return $re_mobile;
		}

}
?>