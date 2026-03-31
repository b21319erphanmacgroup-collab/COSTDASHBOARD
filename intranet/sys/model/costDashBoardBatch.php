<?php
	include_once realpath(dirname(__FILE__) . "/../util/OracleClass.php");
	include_once realpath(dirname(__FILE__) . "/../inc/dbcon.inc");
	include_once realpath(dirname(__FILE__) . "/../util/dashBoard/costDashBoardCommon.php");
	include_once realpath(dirname(__FILE__) . "/../inc/function_intranet.php");
	
	class CostBoardBatch {
		var $oracle;
		Var $mysql;
		var $companyCode;
		var $logPath;
		
		public function __construct() {
			global $db;
			$result = $this->initResult();
			
			$sql  = " Select  ";
			$sql .= "     *  ";
			$sql .= " From  ";
			$sql .= "     systemconfig_tbl  ";
			$sql .= " Where SysKey = 'CompanyKind' ";
			$resource = mysql_query($sql, $db);
			
			if (!$resource) {
				$result["success"] = false;
				$result["error"]["message"] = "__construct error";
				return $result;
			}
			
			$fullData = array();
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				$this->companyCode = $row["Code"];
			}
			
			$this->logPath = "D:/APM_Setup/htdocs/intranet/sys/log/dashboard/";
		}
		
		public function initResult() {
			$result = array(
				"success"=> true,
				"rows"=> array(),
				"error"=> array()
			);
			
			return $result;
		}
		
		/******************************************************************************
		 기    능 : MH대쉬보드 전표 배치파일
		 관 련 DB :
		 프로시져 :
		 사용메뉴 : MHCOSTDASHBOARD
		 기    타 :
		 변경이력 : 작업일 / 요청자 / 작업자 / 내용
		 	1. 2026-03-24 / - / 김병철 / 최초작성 
		******************************************************************************/
		public function insertSlipDataBatch($startDay, $endDay) {
			global $db;
			$result = $this->initResult();
			$startDay = preg_replace('/[^0-9]/', '', $startDay);
			$endDay = preg_replace('/[^0-9]/', '', $endDay);
			
			$formats = array('Ymd');
			if (!validateDateFormat($startDay, $formats) || !validateDateFormat($endDay, $formats)) {
				$result["success"] = false;
				$result["error"]["message"] = "invalid DataFormat";
				return $result;
			}
			
			$this->oracle = new OracleClass($smarty);
			$sql .= " Delete ";
			$sql .= " From ";
			$sql .= "     MH_SLIP_SUMMARY ";
			$sql .= " Where WORK_DATE Between '$startDay' And '$endDay' ";
			$deleteResult = $this->oracle->executeDml($sql);
			if (!$deleteResult["success"]) {
				$result["success"] = false;
				$result["error"]["message"] = "slip delete error";
				return $result;
			}
			
			$sql  = " Select ";
			$sql .= "     y.EMP_NAME, ";
			$sql .= "     y.TEAM_CODE CUST_USER_ID_TEAM_CODE, ";
			$sql .= "     z.TEAM_CODE EXCEPTION_USER_ID_TEAM_CODE,  ";
			$sql .= "     x.*  ";
			$sql .= " From ";
			$sql .= "     ( ";
			$sql .= "         Select ";
			$sql .= "             e.PROJ_CODE, ";
			$sql .= "             f.USER_ID CUST_USER_ID, ";
			$sql .= "             g.PM_EMPNO PRE_CONT_PM, ";
			$sql .= "             e.PM_EMPNO2 CONT_PM, ";
			$sql .= "             CoalEsce(e.PM_EMPNO2, g.PM_EMPNO) TEAM_USER_ID,  ";
			$sql .= "             d.* ";
			$sql .= "         From ";
			$sql .= "             ( ";
			$sql .= "                 Select   ";
			$sql .= "                     Case   ";
			$sql .= "                         When a.DEPT_CODE = 'ZZZZZZ' Then a.DEPT_CODE    ";
			$sql .= "                         When SubStr(a.DEPT_CODE, 1, 1) In ('Y', 'Z') Then '0' || SubStr(a.DEPT_CODE, 2)   ";
			$sql .= "                         When SubStr(a.DEPT_CODE, 1, 1) = 'X' Then '9' || SubStr(a.DEPT_CODE, 2)   ";
			$sql .= "                         Else   ";
			$sql .= "                             ''    ";
			$sql .= "                     End SATIS_PROJ_CODE,   ";
			$sql .= "                     a.WORK_DATE || '-' || a.WORK_DEPT || '-' || a.WORK_DEPT_NO || '-' || a.WORK_SEQ_SCR SLIP_NUMBER, ";
			$sql .= "                     b.ADJUSTED_DRCR,   ";
			$sql .= "                     c.GROUP_CODE,  ";
			$sql .= "                     SubStr(a.WORK_DATE, 1, 6) YEAR_MONTH,  ";
			$sql .= "                     Nvl(a.DR_AMT, a.CR_AMT) AMT,  ";
			$sql .= "                     a.*   ";
			$sql .= "                 From   ";
			$sql .= "                     AM_SLIP_DETAIL a,   ";
			$sql .= "                     AM_CODE_ACNT b,  ";
			$sql .= "                     (  ";
			$sql .= "                         Select  ";
			$sql .= "                             a.*  ";
			$sql .= "                         From  ";
			$sql .= "                             MH_SLIP_CATEGORY_DTL a,  ";
			$sql .= "                             MH_SLIP_CATEGORY b  ";
			$sql .= "                         Where a.GROUP_DVS_CD = b.GROUP_DVS_CD  ";
			$sql .= "                         And a.GROUP_CODE = b.GROUP_CODE  ";
			$sql .= "                         Order By  ";
			$sql .= "                             b.GROUP_DVS_CD,  ";
			$sql .= "                             b.GROUP_CODE  ";
			$sql .= "                     ) c  ";
			$sql .= "                 Where 1 = 1   ";
			$sql .= "                 And a.ACNT_CODE = b.ACNT_CODE(+)   ";
			$sql .= "                 And a.ACNT_CODE = c.ACNT_CODE  ";
			$sql .= "                 And a.CHECK_STATUS = '2'  ";
			$sql .= "                 And a.WORK_DATE Between '$startDay' And '$endDay'   ";
			$sql .= "                 And a.DEPT_CODE != 'ZZZZZZ' ";
			$sql .= "                 Order By   ";
			$sql .= "                     a.WORK_DATE Desc   ";
			$sql .= "             ) d, ";
			$sql .= "             CS_CONT_REGISTER e, ";
			$sql .= "             SM_AUTH_USER f, ";
			$sql .= "             CS_CONT_REGISTER g ";
			$sql .= "         Where 1 = 1 ";
			$sql .= "         And d.SATIS_PROJ_CODE = e.PROJ_CODE(+) ";
			$sql .= "         And d.CUST_CODE = f.USER_ID(+) ";
			$sql .= "         And d.SATIS_PROJ_CODE = g.PROJ_CODE(+) ";
			$sql .= "     ) x, ";
			$sql .= "     HR_PERS_MASTER y, ";
			$sql .= "     HR_PERS_MASTER z   ";
			$sql .= " Where 1 = 1 ";
			$sql .= " And x.CUST_USER_ID = y.EMP_NO(+) ";
			$sql .= " And x.TEAM_USER_ID = z.EMP_NO(+) ";
			$sql .= " Order By  ";
			$sql .= "     x.WORK_DATE Desc  ";
			$selectResult = $this->oracle->executeSelect($sql);
			
			if (!$selectResult["success"]) {
				$result["success"] = false;
				$result["error"]["message"] = "slip select error";
				return $result;
			}
			
			$sql  = " Select ";
			$sql .= "     ProjectCode, ";
			$sql .= "     projectViewCode, ";
			$sql .= "     oldProjectCode, ";
			$sql .= "     ProjectName, ";
			$sql .= "     ProjectNickname ";
			$sql .= " From ";
			$sql .= "     project_tbl ";
			$sql .= " Where 1 = 1  ";
			$sql .= " And NullIf(oldProjectCode, '') <> '' ";
			$sql .= " And Substr(ProjectCode, 1, 1) <> 'H' ";
			$sql .= " And oldProjectCode <> 'ZZZZZZ' ";
			$resource = mysql_query($sql, $db);
			
			if (!$resource) {
				$result["success"] = false;
				$result["error"]["message"] = "mappingtable mysql_query(resource) error";
				return $result;
			}
			
			$mappingTable = array();
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				$mappingTable[$row["oldProjectCode"]] = $row;
			}
			
			$exceptionIds = array(
				"D0100"=> "총괄공통"
			);
			
			$rows = $selectResult["rows"];
			$total = count($rows);
			$batchSize = 300;
			
			for ($i = 0; $i < $total; $i += $batchSize) {
				$sql  = "Insert All ";
				
				$chunk = array_slice($rows, $i, $batchSize);
				$chunkTotal = count($chunk);
				for ($j = 0; $j < $chunkTotal; $j++) {
					$row = $chunk[$j];
					$mappingRow = $mappingTable[$row["SATIS_PROJ_CODE"]];
					
					$INTRANET_PROJECT_CODE = $row["SATIS_PROJ_CODE"] == "ZZZZZZ" ? $row["SATIS_PROJ_CODE"] : $mappingRow["ProjectCode"];
					$INTRANET_PROJECT_NAME = $row["SATIS_PROJ_CODE"] == "ZZZZZZ" ? $row["SATIS_PROJ_CODE"] : $mappingRow["ProjectNickname"];
					$INTRANET_PROJECT_VIEW_CODE = $row["SATIS_PROJ_CODE"] == "ZZZZZZ" ? $row["SATIS_PROJ_CODE"] : $mappingRow["projectViewCode"];
					$TEAM_CODE = array_key_exists($row["INPUT_DUTY_ID"], $exceptionIds) ? $row["EXCEPTION_USER_ID_TEAM_CODE"] : $row["CUST_USER_ID_TEAM_CODE"];
					
					$sql .= " Into MH_SLIP_SUMMARY ";
					$sql .= " ( ";
					$sql .= "     COMPANY_ID,            ";
					$sql .= "     WORK_DATE,          ";
					$sql .= "     WORK_NO,             ";
					$sql .= "     WORK_SEQ,            ";
					$sql .= "     YEAR_MONTH,            ";
					$sql .= "     WORK_SEQ_SCR,            ";
					$sql .= "     WORK_DEPT,            ";
					$sql .= "     WORK_DEPT_NO,            ";
					$sql .= "     SLIP_KIND_CODE,      ";
					$sql .= "     ACNT_CODE,           ";
					$sql .= "     GROUP_CODE,          ";
					$sql .= "     DEPT_CODE,           ";
					$sql .= "     CONT_CODE,           ";
					$sql .= "     INTRANET_PROJECT_CODE,        ";
					$sql .= "     INTRANET_PROJECT_NAME,        ";
					$sql .= "     INTRANET_PROJECT_VIEW_CODE,        ";
					$sql .= "     DR_AMT,              ";
					$sql .= "     CR_AMT,              ";
					$sql .= "     AMT,         ";
					$sql .= "     REMARK1,         ";
					$sql .= "     COST_TEAM_CODE         ";
					$sql .= " ) ";
					$sql .= " Values ";
					$sql .= " ( ";
					$sql .= "     'BARO',            ";
					$sql .= "     '$row[WORK_DATE]',          ";
					$sql .= "     '$row[WORK_NO]',             ";
					$sql .= "     '$row[WORK_SEQ]',            ";
					$sql .= "     '$row[YEAR_MONTH]',            ";
					$sql .= "     '$row[WORK_SEQ_SCR]',            ";
					$sql .= "     '$row[WORK_DEPT]',            ";
					$sql .= "     '$row[WORK_DEPT_NO]',            ";
					$sql .= "     '$row[SLIP_KIND_CODE]',            ";
					$sql .= "     '$row[ACNT_CODE]',           ";
					$sql .= "     '$row[GROUP_CODE]',          ";
					$sql .= "     '$row[DEPT_CODE]',           ";
					$sql .= "     '$row[SATIS_PROJ_CODE]',           ";
					$sql .= "     '$INTRANET_PROJECT_CODE',        ";
					$sql .= "     '$INTRANET_PROJECT_NAME',        ";
					$sql .= "     '$INTRANET_PROJECT_VIEW_CODE',        ";
					$sql .= "     '$row[DR_AMT]',              ";
					$sql .= "     '$row[CR_AMT]',              ";
					$sql .= "     '$row[AMT]',         ";
					$sql .= "     '$row[REMARK1]', ";
					$sql .= "     '$TEAM_CODE' ";
					$sql .= " ) ";
				}
				$sql .= " Select * From DUAL";
				
				$insertResult = $this->oracle->executeDml($sql);
				if (!$insertResult["success"]) {
					$result["success"] = false;
					$result["error"]["message"] = "MH_SLIP_SUMMARY bulk insert error";
					
					return $result;
				}
			}
			
			return $result;
		}
		
		public function insertUserDataBatch($startDay, $endDay, $deptNo) {
			$result = $this->initResult();
			
			if (!validateDateFormat($startDay) || !validateDateFormat($endDay)) {
				$result["success"] = false;
				$result["error"]["message"] = "invalid DataFormat";
				return $result;
			}
			
			if (!isset($deptNo)) {
				$result["success"] = false;
				$result["error"]["message"] = "deptNo Parameter error";
				return $result;
			}
			
			$deleteResult = $this->deleteUserData($startDay, $endDay, $deptNo);
			if (!$deleteResult["success"]) {
				return $deleteResult;
			}
			
			$targetsResult = $this->getAttendanceTargets($deptNo);
			if (!$targetsResult["success"]) {
				return $targetsResult;
			}
			
			for ($i = 0; $i < count($targetsResult["rows"]); $i++) {
				$userWorkData = array();
				$row = $targetsResult["rows"][$i];
				
				$workData = $this->getWorkData($row["EMP_NO"], $startDay, $endDay);
				if (!$workData["success"]) {
					return $workData;
				}
				
				$userWorkData[$row["EMP_NO"]] = $workData["rows"];
				$insertResult = $this->bulkInsertUserData($userWorkData, $row["TEAM_CODE"]);
				if (!$insertResult["success"]) {
					return $insertResult;
				}
			}
			
			return $insertResult;
		}
		
		
		public function bulkInsertUserData($userWorkData, $deptNo) {
			global $db;
			$result = $this->initResult();
			
			$values = array();
			foreach ($userWorkData As $userId => $days) {
				$POSITION_CODE = null;
				
				foreach ($days As $day => $works) {
					$totalMinute = 0;
					
					// 출장
					if (array_key_exists("userstate_tbl", $works)) {
						$rows = $works["userstate_tbl"];
						
						for ($i = 0; $i < count($rows); $i++) {
							$row = $rows[$i];
							$xxProjCode1 = $this->getConvertedProjCode($row[MH_PROJ_CODE1]);
							
							switch ($row["state"]) {
								case "03":
									// 출장
									$sql = "";
									$sql .= " ( ";
									$sql .= "     '$day' ";
									$sql .= "     ,'$deptNo' ";
									$sql .= "     ,'$row[MH_PROJ_CODE1]' ";
									$sql .= "     ,'$row[MH_PROJ_CODE2]' ";
									$sql .= "     ,'$userId' ";
									$sql .= "     ,'$row[MH_ACTIVITY_NAME]' ";
									$sql .= "     ,'$row[state]' ";
									$sql .= "     ,'$xxProjCode1' ";
									$sql .= "     ,'$POSITION_CODE' ";
									$sql .= "     ,'480' ";
									$sql .= " ) ";
									$values[] = $sql;
									break;
								case "30": case "31":
									// 오전, 오후 반차
									$totalMinute += 240;
									break;
								case "18":
									// 시차
									$totalMinute += $row["sub_code"] * 60;
									break;
								default:
									break;
							}
						}
					}
					
					// 추가업무
					if (array_key_exists("dallyproject_addwork_tbl", $works)) {
						$rows = $works["dallyproject_addwork_tbl"];
						
						for ($i = 0; $i < count($rows); $i++) {
							$row = $rows[$i];
							$xxProjCode1 = $this->getConvertedProjCode($row[MH_PROJ_CODE1]);
							$mh = $row["work_hour"] * 60 + $row["work_min"];
							
							$sql = "";
							$sql .= " ( ";
							$sql .= "     '$day' ";
							$sql .= "     ,'$deptNo' ";
							$sql .= "     ,'$row[MH_PROJ_CODE1]' ";
							$sql .= "     ,'$row[MH_PROJ_CODE2]' ";
							$sql .= "     ,'$userId' ";
							$sql .= "     ,'$row[MH_ACTIVITY_NAME]' ";
							$sql .= "     ,'52' ";
							$sql .= "     ,'$xxProjCode1' ";
							$sql .= "     ,'$POSITION_CODE' ";
							$sql .= "     ,'$mh' ";
							$sql .= " ) ";
							$values[] = $sql;
							
							$totalMinute += $mh;
						}
					}
					
					// 업무
					if (array_key_exists("dallyproject_tbl", $works)) {
						$rows = $works["dallyproject_tbl"];
						
						for ($i = 0; $i < count($rows); $i++) {
							$row = $rows[$i];
							
							if (holy($day) == "holyday") {
								$xxProjCode1 = $this->getConvertedProjCode($row[MH_ENTRY_PROJ_CODE1]);
								
								$mh = min(300 - $totalMinute, $row["REAL_WORK_TIME"]);
								$sql = "";
								$sql .= " ( ";
								$sql .= "     '$day' ";
								$sql .= "     ,'$deptNo' ";
								$sql .= "     ,'$row[MH_ENTRY_PROJ_CODE1]' ";
								$sql .= "     ,'$row[MH_ENTRY_PROJ_CODE2]' ";
								$sql .= "     ,'$userId' ";
								$sql .= "     ,'$row[MH_ENTRY_ACTIVITY_NAME]' ";
								$sql .= "     ,'04' ";
								$sql .= "     ,'$xxProjCode1' ";
								$sql .= "     ,'$POSITION_CODE' ";
								$sql .= "     ,'$mh' ";
								$sql .= " ) ";
								$values[] = $sql;
							} else {
								$xxProjCode1 = $this->getConvertedProjCode($row[MH_ENTRY_PROJ_CODE1]);
								$mh = 480 - $totalMinute;
								
								$state = $row["EntryJobCode"] == "출장" ? "03" : "51";
								$sql = "";
								$sql .= " ( ";
								$sql .= "     '$day' ";
								$sql .= "     ,'$deptNo' ";
								$sql .= "     ,'$row[MH_ENTRY_PROJ_CODE1]' ";
								$sql .= "     ,'$row[MH_ENTRY_PROJ_CODE2]' ";
								$sql .= "     ,'$userId' ";
								$sql .= "     ,'$row[MH_ENTRY_ACTIVITY_NAME]' ";
								$sql .= "     ,'$state' ";
								$sql .= "     ,'$xxProjCode1' ";
								$sql .= "     ,'$POSITION_CODE' ";
								$sql .= "     ,'$mh' ";
								$sql .= " ) ";
								$values[] = $sql;
								
								if (intval($row["OVER_TIME"]) > "0") {
									$xxProjCode1 = $this->getConvertedProjCode($row[MH_LEAVE_PROJ_CODE1]);
									$row[OVER_TIME_MINUTE] = min($row[OVER_TIME_MINUTE], 180);
									
									$sql = "";
									$sql .= " ( ";
									$sql .= "     '$day' ";
									$sql .= "     ,'$deptNo' ";
									$sql .= "     ,'$row[MH_LEAVE_PROJ_CODE1]' ";
									$sql .= "     ,'$row[MH_LEAVE_PROJ_CODE2]' ";
									$sql .= "     ,'$userId' ";
									$sql .= "     ,'$row[MH_LEAVE_ACTIVITY_NAME]' ";
									$sql .= "     ,'04' ";
									$sql .= "     ,'$xxProjCode1' ";
									$sql .= "     ,'$POSITION_CODE' ";
									$sql .= "     ,'$row[OVER_TIME_MINUTE]' ";
									$sql .= " ) ";
									$values[] = $sql;
								}
							}
						}
					}
				}
			}
			
			if (count($values) > 0) {
				$chunkSize = 500; 
				
				mysql_query("START TRANSACTION", $db);
				foreach (array_chunk($values, $chunkSize) as $chunk) {
					$sql  = "INSERT INTO MH_WORK_SUMMARY ";
					$sql .= " ( ";
					$sql .= "     WORK_DAY ";
					$sql .= "     ,DEPT_NO ";
					$sql .= "     ,PROJ_CODE1 ";
					$sql .= "     ,PROJ_CODE2 ";
					$sql .= "     ,USER_ID ";
					$sql .= "     ,ACTIVITY_NAME ";
					$sql .= "     ,WORK_DVS_CD ";
					$sql .= "     ,XX_PROJ_CODE1 ";
					$sql .= "     ,POSITION_CODE ";
					$sql .= "     ,MH ";
					$sql .= " ) ";
					$sql .= " VALUES ";
					$sql .= implode(",", $chunk);
					$resource = mysql_query($sql, $db);
					
					if (!$resource) {
						mysql_query("ROLLBACK", $db);
						
						$result["success"] = false;
						$result["error"]["message"] = mysql_error($db);
						return $result;
					}
				}
				
				mysql_query("COMMIT", $db);
			}
			
			return $result;
		}
		
		public function deleteUserData($startDay, $endDay, $deptNo = null) {
			global $db;
			$result = $this->initResult();
			
			$sql  = " Delete  ";
			$sql .= " From  ";
			$sql .= "     MH_WORK_SUMMARY  ";
			$sql .= " Where WORK_DAY Between '$startDay' And '$endDay' ";
			$sql .= " And ('$deptNo' = 'ALL' Or DEPT_NO = '$deptNo') ";
			$resource = mysql_query($sql, $db);
			
			if (!$resource) {
				$result["success"] = false;
				$result["error"]["message"] = "mysql_query(batch delete resource) error";
				return $result;
			}
			
			return $result;
		}
		
		public function getAttendanceTargets($deptNo) {
			$sql  = " Select  ";
			$sql .= "     * ";
			$sql .= " From  ";
			$sql .= "     HR_PERS_MASTER ";
			$sql .= " Where TEAM_CODE Is Not Null  ";
			$sql .= " And ('$deptNo' = 'ALL' Or TEAM_CODE = '$deptNo')  ";
			$sql .= " Order By ";
			$sql .= "     TEAM_CODE ";
			$this->oracle = new OracleClass($smarty);
			
			return $this->oracle->executeSelect($sql);
		}
		
		public function getWorkData($memberID, $startDay = null, $endDay = null, $currYM = null) {
			global $db;
			$result = $this->initResult();
			
			if (isset($currYM)) {
				$lastDay = date('t', strtotime($currYM));
				$startDay = "$currYM-01";
				$endDay = "$currYM-$lastDay";
			}
			
			if (!validateDateFormat($startDay) || !validateDateFormat($endDay)) {
				$result["success"] = false;
				$result["error"]["message"] = "invalid DateFormat";
				return $result;
			}
			
			$fromDt = $startDay;
			$toDt = $endDay;
			$days = Get_BetweenDateArray($fromDt, $toDt);
			for ($i = 0; $i < count($days); $i++) {
				$day = $days[$i];
				$result["rows"][$day] = array();
			}
			
			// 휴가상태
			$sql  = " Select   ";
			$sql .= "     'userstate_tbl' DVS_CD,   ";
			$sql .= "     a.ProjectCode MH_PROJ_CODE1,   ";
			$sql .= "     a.NewProjectCode MH_PROJ_CODE2,   ";
			$sql .= "     a.*,  ";
			$sql .= "     b.NAME STATE_NAME ";
			$sql .= " From   ";
			$sql .= "     userstate_tbl a Left Join  ";
			$sql .= "     (  ";
			$sql .= "         Select    ";
			$sql .= "             x.Code,   ";
			$sql .= "             x.Name   ";
			$sql .= "         From    ";
			$sql .= "             systemconfig_tbl x   ";
			$sql .= "         Where x.SysKey = 'UserStateCode'   ";
			$sql .= "     ) b On a.state = b.Code   ";
			$sql .= " Where 1 = 1   ";
			$sql .= " And a.MemberNo = '$memberID'   ";
			$sql .= " And a.start_time <= '$toDt'   ";
			$sql .= " And a.end_time >= '$fromDt'   ";
			$sql .= " And a.state In ('03', '18', '30', '31') ";
			$sql .= " Order By   ";
			$sql .= "     a.start_time, ";
			$sql .= "     a.sub_code  ";
			$resource = mysql_query($sql, $db);
			
			if (!$resource) {
				$result["success"] = false;
				$result["error"]["message"] = "mysql_query(userstate_tbl_resource) error";
				return $result;
			}
			
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				$betweenDate = Get_BetweenDateArray($row["start_time"], $row["end_time"]);
				
				for ($i = 0; $i < count($betweenDate); $i++) {
					$day = $betweenDate[$i];
					
					$dateDiff = strtotime($toDt) - strtotime($day);
					if ($dateDiff < 0) {
						break;
					}
					
					$result["rows"][$day][$row["DVS_CD"]][] = $row;
				}
			}
			
			// 근무
			$sql  = " Select ";
			$sql .= "     b.*, ";
			$sql .= "     b.EntryPCode MH_ENTRY_PROJ_CODE1,  ";
			$sql .= "     b.EntryPCode2 MH_ENTRY_PROJ_CODE2,  ";
			$sql .= "     b.EntryJobCode MH_ENTRY_ACTIVITY_NAME, ";
			$sql .= "     b.LeavePCode MH_LEAVE_PROJ_CODE1,  ";
			$sql .= "     b.LeavePCode2 MH_LEAVE_PROJ_CODE2,  ";
			$sql .= "     b.LeaveJobCode MH_LEAVE_ACTIVITY_NAME ";
			$sql .= " From ";
			$sql .= "     ( ";
			$sql .= "         Select   ";
			$sql .= "             'dallyproject_tbl' DVS_CD,   ";
			$sql .= "             Date_Format(a.EntryTime, '%Y-%m-%d') WORK_DAY,   ";
			$sql .= "             Date_Format(a.EntryTime, '%d') `DAY`,   ";
			$sql .= "             Date_Format(a.EntryTime, '%H:%i') ENTRY_TIME,   ";
			$sql .= "             Round((Unix_Timestamp(a.LeaveTime) - Unix_Timestamp(a.EntryTime)) / 60, 1) REAL_WORK_TIME,   ";
			$sql .= "             Case   ";
			$sql .= "                 When a.LeaveTime = '0000-00-00 00:00:00' Then '미입력'   ";
			$sql .= "                 Else   ";
			$sql .= "                     Date_Format(a.LeaveTime, '%H:%i')   ";
			$sql .= "             End LEAVE_TIME,   ";
			$sql .= "             Case   ";
			$sql .= "                 When a.OverTime = '0000-00-00 00:00:00' Or a.LeaveTime = '0000-00-00 00:00:00' Then ''   ";
			$sql .= "                 When Unix_Timestamp(a.LeaveTime)  - Unix_Timestamp(a.OverTime) < 0 Then ''   ";
			$sql .= "                 Else   ";
			$sql .= "                     Time_Format(Sec_to_time(Unix_Timestamp(a.LeaveTime)  - Unix_Timestamp(a.OverTime)), '%H:%i')       ";
			$sql .= "             End OVER_TIME,    ";
			$sql .= "             Case   ";
			$sql .= "                 When a.OverTime = '0000-00-00 00:00:00' Or a.LeaveTime = '0000-00-00 00:00:00' Then ''   ";
			$sql .= "                 When Unix_Timestamp(a.LeaveTime)  - Unix_Timestamp(a.OverTime) < 0 Then ''   ";
			$sql .= "                 Else   ";
			$sql .= "                     (Unix_Timestamp(a.LeaveTime)  - Unix_Timestamp(a.OverTime)) / 60       ";
			$sql .= "             End OVER_TIME_MINUTE,    ";
			$sql .= "             Case   ";
			$sql .= "                 When a.OverTime = '0000-00-00 00:00:00' Then ''   ";
			$sql .= "                 Else   ";
			$sql .= "                     Date_Format(a.OverTime, '%H:%i')   ";
			$sql .= "             End OVER_STARTTIME,   ";
			$sql .= "             a.*   ";
			$sql .= "         From   ";
			$sql .= "             dallyproject_tbl a    ";
			$sql .= "         Where a.MemberNo = '$memberID'   ";
			//$sql .= "         And Date_Format(a.EntryTime, '%Y-%m-%d') Between '$fromDt' And '$toDt'   ";
			$sql .= "         And a.EntryTime >= '$fromDt 00:00:00' And a.EntryTime <= '$toDt 23:59:59'   ";
			$sql .= "     ) b ";
			$resource = mysql_query($sql, $db);
			
			if (!$resource) {
				$result["success"] = false;
				$result["error"]["message"] = "mysql_query(dallyproject_tbl_resource) error";
				return $result;
			}
			
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				if (array_key_exists($row["WORK_DAY"], $result["rows"])) {
					$value = round($row["OVER_TIME_MINUTE"] / 60, 1);
					$convertedValue = ($value == (int)$value) ? (int)$value : $value;
					$row["OVER_TIME_LABEL"] = $convertedValue;
					
					$result["rows"][$row["WORK_DAY"]][$row["DVS_CD"]][] = $row;
				}
			}
			
			// 외근(외근 -> usersatet X / official_plan_tbl O)
			$sql  = " Select    ";
			$sql .= "     'official_plan_tbl' DVS_CD,    ";
			$sql .= "     b.ProjectCode MH_PROJ_CODE1,    ";
			$sql .= "     b.NewProjectCode MH_PROJ_CODE2,    ";
			$sql .= "     '' VIEW_ACTIVITY_NAME,  ";
			$sql .= "     '외근' STATE_NAME,    ";
			$sql .= "     '외근' ACTIVITY_NAME, ";
			$sql .= "     Date_Format(b.o_start, '%Y-%m-%d') WORK_DAY,   ";
			$sql .= "     b.*    ";
			$sql .= " From    ";
			$sql .= "     (   ";
			$sql .= "         Select   ";
			$sql .= "             a.* ";
			$sql .= "         From   ";
			$sql .= "             official_plan_tbl a       ";
			$sql .= "     ) b    ";
			$sql .= " Where 1 = 1     ";
			$sql .= " And b.memberno = '$memberID'    ";
			$sql .= " And b.o_change = '1'    ";
			$sql .= " And b.o_start <= '$toDt 23:59:59'     ";
			$sql .= " And b.o_start >= '$fromDt 00:00:00'     ";
			$sql .= " Order By     ";
			$sql .= "     b.o_start  ";
			$resource = mysql_query($sql, $db);
			
			if (!$resource) {
				$result["success"] = false;
				$result["error"]["message"] = "mysql_query(official_plan_tbl_resource) error";
				return $result;
			}
			
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				if (array_key_exists($row["WORK_DAY"], $result["rows"])) {
					$result["rows"][$row["WORK_DAY"]][$row["DVS_CD"]][] = $row;
				}
			}
			
			// 추가업무
			$sql  = " Select    ";
			$sql .= "     'dallyproject_addwork_tbl' DVS_CD,    ";
			$sql .= "     a.EntryTime WORK_DAY,   ";
			$sql .= "     a.activity_code MH_ACTIVITY_NAME,   ";
			$sql .= "     a.project_code MH_PROJ_CODE1,     ";
			$sql .= "     a.new_project_code MH_PROJ_CODE2,     ";
			$sql .= "     ((a.work_hour * 60) + a.work_min) MH,  ";
			$sql .= "     a.*    ";
			$sql .= " From    ";
			$sql .= "     dallyproject_addwork_tbl a   ";
			$sql .= " Where MemberNo = '$memberID'    ";
			$sql .= " And a.EntryTime Between '$fromDt' And '$toDt'  ";
			$resource = mysql_query($sql, $db);
			
			if (!$resource) {
				$result["success"] = false;
				$result["error"]["message"] = "mysql_query(dallyproject_addwork_tbl_resource) error";
				return $result;
			}
			
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				if (array_key_exists($row["WORK_DAY"], $result["rows"])) {
					$result["rows"][$row["WORK_DAY"]][$row["DVS_CD"]][] = $row;
				}
			}
			
			return $result;
		}
		
		public function getConvertedProjCode($projCode) {
			$projCodeArr = explode("-", $projCode);
			
			if (Count($projCodeArr) < 3) {
				return $projCode;
			}
			
			$prefix = $projCodeArr[0];
			$mid = $projCodeArr[1];
			$last = $projCodeArr[2];
			
			if (!isXXCode($this->companyCode, $mid)) {
				return $projCode;
			}
			
			return getXXCode($this->companyCode) . "-" . $mid . "-" . $last;
		}
	}
?>