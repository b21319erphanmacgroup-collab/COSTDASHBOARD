<?php
include "../../sys/inc/dbcon.inc";
include "../../sys/inc/function_intranet.php";
include "../../sys/inc/function_timework.php";
include "../../../SmartyConfig.php";

include "../../sys/inc/getNeedDate.php";      //로직에 사용되는 PHP시간&날짜 정의
include "../util/OracleClass.php";

extract($_GET);
require_once($SmartyClassPath);

//회사코드 찾기 return 4자리 영어대문자 회사코드
$CompanyKind   = searchCompanyKind();
class CostDashBoard {
	var $smarty;
	var $oracle;
	
	function CostDashBoard($smarty) {
		$Controller = $_REQUEST ['Controller'];
		$ActionMode = $_REQUEST ['ActionMode'];
		$MainAction = $_REQUEST ['MainAction'];
		$SubAction = $_REQUEST ['$SubAction'];
		
		$this->smarty = $smarty;
		$this->smarty->assign('Controller', $Controller );
		$this->smarty->assign('ActionMode', $ActionMode );
		$this->smarty->assign('MainAction', $MainAction );
		$this->smarty->assign('SubAction', $SubAction );
	}
	
	function login() {
		global $MainAction, $SubAction, $db;
		
		switch ($SubAction) {
			case "checkLogin" :
				$response = array(
				"rstCd"=> 200,
				"error"=> array()
				);
				
				$id = $_REQUEST["id"];
				$pwd = $_REQUEST["pwd"];
				
				$sql  = " Select ";
				$sql .= "     * ";
				$sql .= " From ";
				$sql .= "     member_tbl ";
				$sql .= " Where MemberNo = '$id' ";
				$sql .= " And Pasword = '$pwd' ";
				$sql .= " And WorkPosition <> 9 ";
				$resource = mysql_query($sql, $db);
				
				if (!$resource) {
					$response["error"]["rstCd"] = 500;
					$response["error"]["message"] = "mysql_query(resource) error";
					
					echo json_encode($response);
					return;
				}
				
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					// setsession
					$this->setSession();
					$response["rstCd"] = "200";
					echo json_encode($response);
					return;
				}
				
				$response["rstCd"] = "403";
				$response["error"]["message"] = "사번, 비밀번호를 확인해주세요.";
				$response["error"]["id"] = $id;
				$response["error"]["pwd"] = $pwd;
				
				echo json_encode($response);
				break;
			default:
				$this->smarty->display("intranet/common_contents/dashboard/login.tpl");
		}
	}
	
	function setSession() {
		
	}
	
	function main() {
		global $db,$memberID,$SubAction;
		
		switch ($SubAction) {
			case "memberList":
				$jsonUserWorkData = $_POST['data'];
				if (get_magic_quotes_gpc()) {
					$jsonUserWorkData = stripslashes($jsonUserWorkData);
				}
				$userWorkData = json_decode($jsonString, true);
				
				$deptNo = $_REQUEST["deptNo"];
				$startDay = $_REQUEST["startDay"];
				$endDay = $_REQUEST["endDay"];
				$yyyy = $_REQUEST["yyyy"];
				$mm = $_REQUEST["mm"];
				
				$startDay = "2026-01-01";
				$endDay = "2026-01-03";
				$deptNo = "1020";
				$yyyy = "2026";
				$mm = "2";
				
				$betweenDay = Get_BetweenDateArray($startDay, $endDay);
				$users = $this->getUserInfo($deptNo, $yyyy, $mm);
				
				// 해당유저의 workData를 가져온다..
				
				$this->smarty->assign("users", $users);
				$this->smarty->assign("deptNo", $deptNo);
				$this->smarty->assign("total", count($betweenDay) * 8);
				$this->smarty->assign("userWorkData", $userWorkData);
				
				
				$this->smarty->display("intranet/common_contents/dashboard/memberList.tpl");
				break;
			case "searchUser":
				$this->searchUser();
				break;
			default:
				/*
				 1. 일별 사용자별 시간
				 2. 기간별 프로젝트별 집계
				 - 연구원, 선임, 수석, 책임
				 */
				
				$depts = array(
				"TDC"=> array(),
				"GPD"=> array()
				);
				$result = $this->getDepts("100");
				if (!$result["success"]) {
					echo "select tdc query error";
					return;
				}
				$depts["TDC"] = $result["rows"];
				
				$result = $this->getDepts("200");
				if (!$result["success"]) {
					echo "select gpd query error";
					return;
				}
				$depts["GPD"] = $result["rows"];
				
				$this->smarty->assign("depts", $depts);
				
				$condYYYY = '2026';
				$condMM = '2';
				
				$sql  = " Select    ";
				$sql .= "     a.*,   ";
				$sql .= "     b.USER_NAME,   ";
				$sql .= "     c.*,   ";
				$sql .= "     d.GRADE_NAME,  ";
				$sql .= "     e.TITLE_NAME, ";
				$sql .= "     f.* ";
				$sql .= " From   ";
				$sql .= "     COMPANY_DEPT_USER a,   ";
				$sql .= "     SM_AUTH_USER b,   ";
				$sql .= "     (   ";
				$sql .= "         Select    ";
				$sql .= "             a.*   ";
				$sql .= "         From    ";
				$sql .= "             HR_ORDE_MASTER a,   ";
				$sql .= "             (   ";
				$sql .= "                 Select    ";
				$sql .= "                     EMP_NO,   ";
				$sql .= "                     Max(APPLY_ORDER_DATE) APPLY_ORDER_DATE   ";
				$sql .= "                 From    ";
				$sql .= "                     HR_ORDE_MASTER   ";
				$sql .= "                 Group By    ";
				$sql .= "                     EMP_NO   ";
				$sql .= "             ) b   ";
				$sql .= "         Where a.EMP_NO = b.EMP_NO   ";
				$sql .= "         And a.APPLY_ORDER_DATE = b.APPLY_ORDER_DATE   ";
				$sql .= "         And a.SEQ = (   ";
				$sql .= "                 Select    ";
				$sql .= "                     Max(x.SEQ)   ";
				$sql .= "                 From    ";
				$sql .= "                     HR_ORDE_MASTER x   ";
				$sql .= "                 Where x.EMP_NO = b.EMP_NO   ";
				$sql .= "                 And x.APPLY_ORDER_DATE = b.APPLY_ORDER_DATE   ";
				$sql .= "             )   ";
				$sql .= "     ) c,   ";
				$sql .= "     HR_CODE_GRADE d,  ";
				$sql .= "     HR_CODE_TITLE e, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             b.* ";
				$sql .= "         From ";
				$sql .= "             ( ";
				$sql .= "                 Select  ";
				$sql .= "                     a.MEMBERNO, ";
				$sql .= "                     a.TARDY,  ";
				$sql .= "                     a.REMAIN_VACATION, ";
				$sql .= "                     Trunc(a.REMAIN_VACATION / 8) REMAIN_DAY, ";
				$sql .= "                     Mod(a.REMAIN_VACATION, 8) REMAIN_TIME ";
				$sql .= "                 From  ";
				$sql .= "                     HR_WORKER_TOTAL a ";
				$sql .= "                 Where 1 = 1 ";
				$sql .= "                 And a.DATE_Y = '$condYYYY' ";
				$sql .= "                 And a.DATE_M = '$condMM' ";
				$sql .= "             ) b ";
				$sql .= "     ) f ";
				$sql .= " Where a.USER_ID = b.USER_ID   ";
				$sql .= " And a.USER_ID = c.EMP_NO   ";
				$sql .= " And c.GRADE_CODE = d.GRADE_CODE  ";
				$sql .= " And a.POSITION_CODE = e.TITLE_CODE(+) ";
				$sql .= " And a.USER_ID = f.MEMBERNO  ";
				$usersResult = $this->oracle->executeSelect($sql);
				
				if (!$usersResult["success"]) {
					echo "select user query error";
					return;
				}
				
				$users = array();
				$rows = $usersResult["rows"];
				$commuteDatas = array();
				
				for ($i = 0; $i < count($rows); $i++) {
					$row = $rows[$i];
					
					if (!is_array($users[$row["DEPT_NO"]])) {
						$users[$row["DEPT_NO"]] = array();
					}
					
					//$commuteDatas[$row["USER_ID"]] = $this->getWorkData($row["USER_ID"]);
					array_push($users[$row["DEPT_NO"]], $row);
				}
				
				$userWorkData = array (
						"B21369"=> $this->getWorkData("B21369", "2026-02"),
						"M20329"=> $this->getWorkData("M20329", "2026-02"),
						"B21319"=> $this->getWorkData("B21319", "2026-02"),
						"M22014"=> $this->getWorkData("M22014", "2026-02"),
						"M21420"=> $this->getWorkData("M21420", "2026-02"),
						"B23030"=> $this->getWorkData("B23030", "2026-02"),
						"M07318"=> $this->getWorkData("M07318", "2026-02"),
						"B23008"=> $this->getWorkData("B23008", "2026-02")
				);
				
				$summary = array(
						"B21369"=> $this->calcWorkTime($userWorkData["B21369"]),
						"M20329"=> $this->calcWorkTime($userWorkData["M20329"]),
						"B21319"=> $this->calcWorkTime($userWorkData["B21319"]),
						"M22014"=> $this->calcWorkTime($userWorkData["M22014"]),
						"M21420"=> $this->calcWorkTime($userWorkData["M21420"]),
						"B23030"=> $this->calcWorkTime($userWorkData["B23030"]),
						"M07318"=> $this->calcWorkTime($userWorkData["M07318"]),
						"B23008"=> $this->calcWorkTime($userWorkData["B23008"])
				);
				$this->smarty->assign("summary", $summary);
				
				$projectSummary = $this->getProjectSummary($userWorkData, $users["1020"], $summary);
				
				
				$this->smarty->assign("projectSummary", $projectSummary);
				$this->smarty->assign("userWorkData", $userWorkData);
				$this->smarty->assign("users", $users);
				$this->smarty->assign("userDept", "1020");
				$this->smarty->assign("commuteDatas", $commuteDatas);
				
				/*
				 -- 출근
				 dallyproject_tbl
				 -- 외출
				 view_official_planout_tbl
				 -- 휴가
				 view_userstate_tbl
				 -- 전일 업무종료 미입력자
				 workout_not_tbl
				 --  추가업무
				 dallyproject_addwork_tbl
				 
				 
				 F0 = 선임
				 E0 = 책임
				 J0 = 연구원
				 C0 = 수석
				 
				 사람당 프로젝트별 1번만 카운팅
				 
				 요구사항
				 1. 프로젝트별 직급별 M/H 시간을 구해야한다.
				 2. 팀 > 개인별, 일자별 투입 프로젝트를 구해야한다.
				 
				 
				 Table
				 2026-01-01 A프로젝트 김병철 2  직급 엑티비티코드 출근
				 2026-01-01 A프로젝트 김병철 2  직급	엑티비티코드 야근
				 2026-01-01 B프로젝트 김병철 3  직급	엑티비티코드 추가업무
				 2026-01-01 D프로젝트 김병철 2  직급	엑티비티코드 외근
				 
				 2026-01-01 A프로젝트 김한결 2  직급 엑티비티코드 출근
				 2026-01-01 A프로젝트 김한결 3  직급	엑티비티코드 야근
				 2026-01-01 B프로젝트 김한결 3  직급	엑티비티코드 추가업무
				 2026-01-01 D프로젝트 김한결 2  직급	엑티비티코드 외근
				 
				 
				 Array
				 (
				 [BD262811] => Array
				 (
				 [J0] => 1080
				 )
				 
				 [BD262805] => Array
				 (
				 [E0] => 90
				 [F0] => 930
				 )
				 
				 [BD262807] => Array
				 (
				 [E0] => 1740
				 [F0] => 30
				 )
				 
				 [BD262809] => Array
				 (
				 [E0] => 90
				 )
				 
				 [BD262101] => Array
				 (
				 [F0] => 960
				 )
				 
				 [BD262806] => Array
				 (
				 [J0] => 480
				 )
				 
				 [BD262802] => Array
				 (
				 [J0] => 480
				 )
				 
				 )
				 */
				
				
				/* ------------------------------------------------------------- */
				$this->smarty->display("intranet/common_contents/dashboard/dashBoard.tpl");
				/* ------------------------------------------------------------- */
				break;
		}
		
	}
	
	function getUserInfo($deptNo, $yyyy, $mm) {
		$this->oracle = new OracleClass($smarty);
		
		$sql  = " Select    ";
		$sql .= "     a.*,   ";
		$sql .= "     b.USER_NAME,   ";
		$sql .= "     c.*,   ";
		$sql .= "     d.GRADE_NAME,  ";
		$sql .= "     e.TITLE_NAME, ";
		$sql .= "     f.* ";
		$sql .= " From   ";
		$sql .= "     COMPANY_DEPT_USER a,   ";
		$sql .= "     SM_AUTH_USER b,   ";
		$sql .= "     (   ";
		$sql .= "         Select    ";
		$sql .= "             a.*   ";
		$sql .= "         From    ";
		$sql .= "             HR_ORDE_MASTER a,   ";
		$sql .= "             (   ";
		$sql .= "                 Select    ";
		$sql .= "                     EMP_NO,   ";
		$sql .= "                     Max(APPLY_ORDER_DATE) APPLY_ORDER_DATE   ";
		$sql .= "                 From    ";
		$sql .= "                     HR_ORDE_MASTER   ";
		$sql .= "                 Group By    ";
		$sql .= "                     EMP_NO   ";
		$sql .= "             ) b   ";
		$sql .= "         Where a.EMP_NO = b.EMP_NO   ";
		$sql .= "         And a.APPLY_ORDER_DATE = b.APPLY_ORDER_DATE   ";
		$sql .= "         And a.SEQ = (   ";
		$sql .= "                 Select    ";
		$sql .= "                     Max(x.SEQ)   ";
		$sql .= "                 From    ";
		$sql .= "                     HR_ORDE_MASTER x   ";
		$sql .= "                 Where x.EMP_NO = b.EMP_NO   ";
		$sql .= "                 And x.APPLY_ORDER_DATE = b.APPLY_ORDER_DATE   ";
		$sql .= "             )   ";
		$sql .= "     ) c,   ";
		$sql .= "     HR_CODE_GRADE d,  ";
		$sql .= "     HR_CODE_TITLE e, ";
		$sql .= "     ( ";
		$sql .= "         Select ";
		$sql .= "             b.* ";
		$sql .= "         From ";
		$sql .= "             ( ";
		$sql .= "                 Select  ";
		$sql .= "                     a.MEMBERNO, ";
		$sql .= "                     a.TARDY,  ";
		$sql .= "                     a.REMAIN_VACATION, ";
		$sql .= "                     Trunc(a.REMAIN_VACATION / 8) REMAIN_DAY, ";
		$sql .= "                     Mod(a.REMAIN_VACATION, 8) REMAIN_TIME ";
		$sql .= "                 From  ";
		$sql .= "                     HR_WORKER_TOTAL a ";
		$sql .= "                 Where 1 = 1 ";
		$sql .= "                 And a.DATE_Y = '$yyyy' ";
		$sql .= "                 And a.DATE_M = '$mm' ";
		$sql .= "             ) b ";
		$sql .= "     ) f ";
		$sql .= " Where a.USER_ID = b.USER_ID   ";
		$sql .= " And a.USER_ID = c.EMP_NO   ";
		$sql .= " And c.GRADE_CODE = d.GRADE_CODE  ";
		$sql .= " And a.POSITION_CODE = e.TITLE_CODE(+) ";
		$sql .= " And a.USER_ID = f.MEMBERNO  ";
		$sql .= " And (a.DEPT_NO = '$deptNo' Or '$deptNo' = 'ALL')  ";
		$usersResult = $this->oracle->executeSelect($sql);
		
		if (!$usersResult["success"]) {
			echo "select user query error";
			return;
		}
		
		return $usersResult["rows"];
	}
	
	function searchUser() {
		$this->oracle = new OracleClass($smarty);
		
		$response = array(
				"rstCd"=> "",
				"data"=> array(),
				"error"=> array()
		);
		$searchText = $_REQUEST["searchText"];
		
		$sql  = " Select ";
		$sql .= "     c.* ";
		$sql .= " From ";
		$sql .= "     COMPANY_DEPT c, ";
		$sql .= "     ( ";
		$sql .= "         Select ";
		$sql .= "             b.COMPANY_ID,  ";
		$sql .= "             b.DEPT_NO  ";
		$sql .= "         From  ";
		$sql .= "             (      ";
		$sql .= "                 Select    ";
		$sql .= "                     Connect_By_Root a.DEPT_NO AS START_DEPT,  ";
		$sql .= "                     a.*    ";
		$sql .= "                 From    ";
		$sql .= "                     COMPANY_DEPT a  ";
		$sql .= "                 Where 1 = 1   ";
		$sql .= "                 Start With DEPT_NO In (  ";
		$sql .= "                     Select  ";
		$sql .= "                         x.DEPT_NO  ";
		$sql .= "                     From  ";
		$sql .= "                         COMPANY_DEPT_USER x,  ";
		$sql .= "                         SM_AUTH_USER y  ";
		$sql .= "                     Where 1 = 1  ";
		$sql .= "                     And x.USER_ID = y.USER_ID  ";
		$sql .= "                     And (y.USER_NAME Like '%$searchText%' Or y.USER_ID Like '%$searchText%')  ";
		$sql .= "                 )   ";
		$sql .= "                 Connect By    ";
		$sql .= "                     Prior UP_DEPT_NO = DEPT_NO  ";
		$sql .= "             ) b      ";
		$sql .= "         Group By ";
		$sql .= "             b.COMPANY_ID, ";
		$sql .= "             b.DEPT_NO  ";
		$sql .= "     ) d ";
		$sql .= " Where c.COMPANY_ID = d.COMPANY_ID ";
		$sql .= " And c.DEPT_NO = d.DEPT_NO ";
		$sql .= " Order By  ";
		$sql .= "     c.INNER_CD  ";
		$usersResult = $this->oracle->executeSelect($sql);
		
		if (!$usersResult["success"]) {
			$response["error"]["message"] = "searchUser select query error";
			$response["rstCd"] = "500";
			echo json_encode($response);
			return;
		}
		
		$response["data"] = $usersResult["rows"];
		echo json_encode($response);
	}
	
	function getProjectCounts($rows) {
		foreach ($rows As $key => $value) {
		}
	}
	
	function calcWorkTime($rows) {
		if (empty($rows)) {
			return 0;
		}
		
		$result = array();
		foreach ($rows As $date => $data) {
			$totalTime = 0;
			
			$result[$date] = array(
					"userstate_tbl" => 0,
					"dallyproject_addwork_tbl" => 0,
					"dallyproject_tbl" => 0
			);
			
			
			if (count($data["userstate_tbl"]) != 0) {
				$standardRow = $data["userstate_tbl"];
				
				for ($i = 0; $i < count($standardRow); $i++) {
					$row = $standardRow[$i];
					
					if ($row["state"] == "30" || $row["state"] == "31") {
						$totalTime = 240;
					}
				}
				
				$result[$date]["userstate_tbl"] = $totalTime;
			}
			
			if (count($data["dallyproject_addwork_tbl"]) != 0) {
				$standardRow = $data["dallyproject_addwork_tbl"];
				
				for ($i = 0; $i < count($standardRow); $i++) {
					$row = $standardRow[$i];
					
					$totalTime += $row["work_hour"] * 60;
					$totalTime += $row["work_min"];
				}
				
				$result[$date]["dallyproject_addwork_tbl"] = $totalTime;
			}
			
			
			if (count($data["dallyproject_tbl"]) != 0) {
				$remainTime = $this->getRemainTime($totalTime);
				
				$result[$date]["dallyproject_tbl"] = array(
						"normal"=> $remainTime["totalMinute"],
						"overwork"=> $row["OVER_TIME_MINUTE"]
				);
			}
		}
		
		return $result;
	}
	
	function getProjectSummary($userWorkData, $users, $summary) {
		if (empty($userWorkData)) {
			return 0;
		}
		
		$userInfo = array();
		$result = array();
		
		for ($i = 0; $i < count($users); $i++) {
			$row = $users[$i];
			
			$userInfo[$row["USER_ID"]] = $row;
		}
		
		foreach ($userWorkData As $userNo => $rows) {
			$countChecker = array();
			
			foreach ($rows As $date => $data) {
				if (count($data["dallyproject_addwork_tbl"]) != 0) {
					$standardRow = $data["dallyproject_addwork_tbl"];
					$totalTime = 0;
					
					for ($i = 0; $i < count($standardRow); $i++) {
						$row = $standardRow[$i];
						$user = $userInfo[$userNo];
						$totalTime += $row["work_hour"] * 60;
						$totalTime += $row["work_min"];
						
						if (!is_array($result[$row["new_project_code"]])) {
							$result[$row["new_project_code"]] = array();
							$result[$row["new_project_code"]][$user["GRADE_CODE"]] = array();
						}
						
						if (empty($countChecker[$row["new_project_code"]])) {
							$countChecker[$row["new_project_code"]] = true;
							$result[$row["new_project_code"]][$user["GRADE_CODE"]]["cnt"] += 1;
						}
						
						$result[$row["new_project_code"]][$user["GRADE_CODE"]]["minute"] += $totalTime;
					}
				}
				
				if (count($data["dallyproject_tbl"]) != 0) {
					$standardRow = $data["dallyproject_tbl"];
					
					for ($i = 0; $i < count($standardRow); $i++) {
						$row = $standardRow[$i];
						$userNo = $row["MemberNo"];
						$user = $userInfo[$userNo];
						
						if (!is_array($result[$row["EntryPCode2"]])) {
							$result[$row["EntryPCode2"]] = array();
							$result[$row["EntryPCode2"]][$user["GRADE_CODE"]] = array();
						}
						
						if (empty($countChecker[$row["EntryPCode2"]])) {
							$countChecker[$row["EntryPCode2"]] = true;
							$result[$row["EntryPCode2"]][$user["GRADE_CODE"]]["cnt"] += 1;
						}
						
						$result[$row["EntryPCode2"]][$user["GRADE_CODE"]]["minute"] += $summary[$userNo][$date]["dallyproject_tbl"]["normal"];
					}
				}
			}
		}
		
		return $result;
	}
	
	function getDepts($root = "100") {
		$this->oracle = new OracleClass($smarty);
		
		$sql  = " Select  ";
		$sql .= "     *  ";
		$sql .= " From  ";
		$sql .= "     COMPANY_DEPT ";
		$sql .= " Start With ";
		$sql .= "     (UP_DEPT_NO Is Null And DEPT_NO = '$root') ";
		$sql .= " Connect By  ";
		$sql .= "     Prior DEPT_NO = UP_DEPT_NO ";
		$result = $this->oracle->executeSelect($sql);
		
		return $result;
	}
	
	function getRemainTime($minute){
		$result = array(
				"hour"=> 0,
				"minute"=> 0,
				"totalMinute"=> 0
		);
		
		$stanMinuteOfDay = 8 * 60;
		$remainedMinute = $stanMinuteOfDay - $minute;
		
		$hour = floor($remainedMinute / 60); // 총 시
		$minute = $remainedMinute % 60;    // 남은 분
		
		
		$result["hour"] = $hour;
		$result["minute"] = $minute;
		$result["totalMinute"] = $remainedMinute;
		
		return $result;
	}
	
	function getLabel($_minute = 0) {
		$hours = floor($_minute / 60); // 총 시
		$minute = $_minute % 60;    // 남은 분
		
		return sprintf('%02d:%02d', $hours, $minute);
	}
	
	/******************************************************************************
	 기    능 : 근무, 추가업무, 휴가등 근태데이터
	 관 련 DB :
	 프로시져 :
	 사용메뉴 :
	 1. intranet/common_contents/person_login/person_login_mvc.tpl : 인트라넷메인 > 최근업무내용
	 기    타 :
	 변경이력 :
	 1. 2026-01-30 / - / 김병철 / 최초작성
	 ******************************************************************************/
	function getWorkData($memberID, $currYM = null) {
		global $db;
		
		if (empty($currYM)) {
			$currYM = Date('Y-m');
		}
		
		$rtnData = array();
		$lastDay = date('t', strtotime($currYM));
		$fromDt = "$currYM-01";
		$toDt = "$currYM-$lastDay";
		
		$fromDt = "2026-02-03";
		$toDt = "2026-02-04";
		
		/* [회사휴일 Select Start] */
		$sql  = " Select  ";
		$sql .= "     *  ";
		$sql .= " From  ";
		$sql .= "     holyday_tbl  ";
		$sql .= " Where `date` Between '$fromDt' And '$toDt' ";
		$sql .= " Order By  ";
		$sql .= "     `date` Desc  ";
		$resource = mysql_query($sql, $db);
		
		if (!$resource) {
			echo "mysql_query(holyday_tbl_resource) error";
			return;
		}
		
		$companyHolyDays = array();
		while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
			if (!is_array($companyHolyDays[$row["date"]])) {
				$companyHolyDays[$row["date"]] = array();
			}
			
			$companyHolyDays[$row["date"]]["isHolyDay"] = "T";
			$companyHolyDays[$row["date"]]["DAY"] = $row["date"];
			$companyHolyDays[$row["date"]]["NAME"] = $row["description"];
		}
		/* [회사휴일 Select End] */
		
		$days = Get_BetweenDateArray($fromDt, $toDt);
		for ($i = 0; $i < count($days); $i++) {
			$day = $days[$i];
			$rtnData[$day] = array();
			
			// 휴일
			if ($companyHolyDays[$day]["isHolyDay"] == "T") {
				$rtnData[$day]["isHolyDay"] = "T";
				$rtnData[$day]["holyDayName"] = $companyHolyDays[$day]['NAME'];
			} else if (holy($day) == "holyday") {
				$rtnData[$day]["isHolyDay"] = "T";
				$rtnData[$day]["holyDayName"] = "주말";
			} else {
				$rtnData[$day]["isHolyDay"] = "F";
				$rtnData[$day]["holyDayName"] = "평일";
			}
			
			// DayName
			$rtnData[$day]["dispDayName"] =  $day . " 일 " . "(" . week_day_new($day) . ")";
		}
		
		// 휴가상태
		$sql  = " Select   ";
		$sql .= "     'userstate_tbl' DVS_CD,   ";
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
		$sql .= " Order By   ";
		$sql .= "     a.start_time, ";
		$sql .= "     a.sub_code  ";
		$resource = mysql_query($sql, $db);
		
		if (!$resource) {
			echo "mysql_query(userstate_tbl_resource) error";
			return;
		}
		
		while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
			$betweenDate = Get_BetweenDateArray($row["start_time"], $row["end_time"]);
			
			for ($i = 0; $i < count($betweenDate); $i++) {
				$day = $betweenDate[$i];
				
				$dateDiff = strtotime($toDt) - strtotime($day);
				if ($dateDiff < 0) {
					break;
				}
				
				if (!is_array($rtnData[$day][$row["DVS_CD"]])) {
					$rtnData[$day][$row["DVS_CD"]] = array();
				}
				
				array_push($rtnData[$day][$row["DVS_CD"]], $row);
			}
		}
		
		// 근무
		$sql  = " Select ";
		$sql .= "     b.*, ";
		$sql .= "     ( ";
		$sql .= "         Select ";
		$sql .= "             CoalEsce(Min(Date_Format(x.EntryTime, '%Y-%m-%d')), Min(Date_Format(x.LeaveTime, '%y.%m.%d'))) INITALENTRY_TIME  ";
		$sql .= "         From ";
		$sql .= "             dallyproject_tbl x ";
		$sql .= "         Where x.MemberNo = b.MemberNo ";
		$sql .= "         And x.EntryPCode2 = b.EntryPCode2 ";
		$sql .= "     ) INITALENTRY_TIME, ";
		$sql .= "     fn_get_proj_name(b.EntryPCode) PROJ_NAME, ";
		$sql .= "     '사업종류' PROJ_DVS_CD, ";
		$sql .= "     '4시간' DISP_TIME, ";
		$sql .= "     Case ";
		$sql .= "         When b.OVER_STARTTIME != '' Then ( ";
		$sql .= "                                         Select ";
		$sql .= "                                             Min(Date_Format(x.EntryTime, '%Y-%m-%d'))   ";
		$sql .= "                                         From ";
		$sql .= "                                             dallyproject_tbl x ";
		$sql .= "                                         Where x.MemberNo = b.MemberNo ";
		$sql .= "                                         And x.EntryPCode2 = b.LeavePCode2 ";
		$sql .= "                                     ) ";
		$sql .= "         Else ";
		$sql .= "             '' ";
		$sql .= "          ";
		$sql .= "     End INITALLEAVE_TIME ";
		$sql .= " From ";
		$sql .= "     ( ";
		$sql .= "         Select   ";
		$sql .= "             'dallyproject_tbl' DVS_CD,   ";
		$sql .= "             Date_Format(a.EntryTime, '%Y-%m-%d') WORK_DAY,   ";
		$sql .= "             Date_Format(a.EntryTime, '%d') `DAY`,   ";
		$sql .= "             Date_Format(a.EntryTime, '%H:%i') ENTRY_TIME,   ";
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
		$sql .= "         And Date_Format(a.EntryTime, '%Y-%m-%d') Between '$fromDt' And '$toDt'   ";
		$sql .= "     ) b ";
		$resource = mysql_query($sql, $db);
		
		if (!$resource) {
			echo "mysql_query(dallyproject_tbl_resource) error";
			return;
		}
		
		while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
			if (array_key_exists($row["WORK_DAY"], $rtnData)) {
				if (!is_array($rtnData[$row["WORK_DAY"]][$row["DVS_CD"]])) {
					$rtnData[$row["WORK_DAY"]][$row["DVS_CD"]] = array();
				}
				
				$value = $row["OVER_TIME_MINUTE"] / 60;
				$result = round($value, 1);
				$result = ($result == (int)$result) ? (int)$result : $result;
				$row["OVER_TIME_LABEL"] = $result;
				
				array_push($rtnData[$row["WORK_DAY"]][$row["DVS_CD"]], $row);
			}
		}
		
		// 외근
		$sql  = " Select   ";
		$sql .= "     'official_plan_tbl' DVS_CD,   ";
		$sql .= "     '외근' STATE_NAME,   ";
		$sql .= "     '외근' ACTIVITY_NAME,   ";
		$sql .= "     fn_get_proj_name(a.ProjectCode) PROJ_NAME,   ";
		$sql .= "     (  ";
		$sql .= "         Select   ";
		$sql .= "             orderno2 PRIORITY  ";
		$sql .= "         From   ";
		$sql .= "             systemconfig_tbl   ";
		$sql .= "         Where SysKey = 'UserStateCode'   ";
		$sql .= "         And Code = a.state   ";
		$sql .= "     ) PRIORITY,  ";
		$sql .= "     Date_Format(a.o_start, '%Y-%m-%d') WORK_DAY,   ";
		$sql .= "     Date_Format(a.o_start, '%d') `DAY`,   ";
		$sql .= "     Date_Format(a.o_start, '%H') `HOUR`,    ";
		$sql .= "     a.*   ";
		$sql .= " From   ";
		$sql .= "     (  ";
		$sql .= "         Select  ";
		$sql .= "             x.*,  ";
		$sql .= "             '14' state   /* 외근(systemconfig_tbl Code) */ ";
		$sql .= "         From  ";
		$sql .= "             official_plan_tbl x      ";
		$sql .= "     ) a   ";
		$sql .= " Where 1 = 1    ";
		$sql .= " And a.memberno = '$memberID'   ";
		$sql .= " And a.o_change = '1'   ";
		$sql .= " And Date_Format(a.o_start, '%Y-%m-%d') <= '$toDt'    ";
		$sql .= " And Date_Format(a.o_end, '%Y-%m-%d') >= '$fromDt'    ";
		$sql .= " Order By    ";
		$sql .= "     a.o_start    ";
		$resource = mysql_query($sql, $db);
		
		if (!$resource) {
			echo "mysql_query(official_plan_tbl_resource) error";
			return;
		}
		
		while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
			if (array_key_exists($row["WORK_DAY"], $rtnData)) {
				if (!is_array($rtnData[$row["WORK_DAY"]][$row["DVS_CD"]])) {
					$rtnData[$row["WORK_DAY"]][$row["DVS_CD"]] = array();
				}
				
				array_push($rtnData[$row["WORK_DAY"]][$row["DVS_CD"]], $row);
			}
		}
		
		// 출장
		$sql  = " Select   ";
		$sql .= "     'official_plan_tbl2' DVS_CD,   ";
		$sql .= "     '출장' STATE_NAME,   ";
		$sql .= "     '출장' ACTIVITY_NAME,   ";
		$sql .= "     fn_get_proj_name(a.ProjectCode) PROJ_NAME,   ";
		$sql .= "     (  ";
		$sql .= "         Select   ";
		$sql .= "             orderno2 PRIORITY  ";
		$sql .= "         From   ";
		$sql .= "             systemconfig_tbl   ";
		$sql .= "         Where SysKey = 'UserStateCode'   ";
		$sql .= "         And Code = a.state   ";
		$sql .= "     ) PRIORITY,  ";
		$sql .= "     Date_Format(a.o_start, '%Y-%m-%d') WORK_DAY,   ";
		$sql .= "     Date_Format(a.o_start, '%d') `DAY`,   ";
		$sql .= "     Date_Format(a.o_start, '%H') `HOUR`,    ";
		$sql .= "     a.*   ";
		$sql .= " From   ";
		$sql .= "     (  ";
		$sql .= "         Select  ";
		$sql .= "             x.*,  ";
		$sql .= "             '03' state   /* 출장(systemconfig_tbl Code) */ ";
		$sql .= "         From  ";
		$sql .= "             official_plan_tbl x      ";
		$sql .= "     ) a   ";
		$sql .= " Where 1 = 1    ";
		$sql .= " And a.memberno = '$memberID'   ";
		$sql .= " And a.o_change = '2'   ";
		$sql .= " And Date_Format(a.o_start, '%Y-%m-%d') <= '$toDt'    ";
		$sql .= " And Date_Format(a.o_end, '%Y-%m-%d') >= '$fromDt'    ";
		$sql .= " Order By    ";
		$sql .= "     a.o_start    ";
		$resource = mysql_query($sql, $db);
		
		if (!$resource) {
			echo "mysql_query(official_plan_tbl_resource) error";
			return;
		}
		
		while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
			if (array_key_exists($row["WORK_DAY"], $rtnData)) {
				if (!is_array($rtnData[$row["WORK_DAY"]][$row["DVS_CD"]])) {
					$rtnData[$row["WORK_DAY"]][$row["DVS_CD"]] = array();
				}
				
				array_push($rtnData[$row["WORK_DAY"]][$row["DVS_CD"]], $row);
			}
		}
		
		// 추가업무
		$sql  = " Select  ";
		$sql .= "     'dallyproject_addwork_tbl' DVS_CD,  ";
		$sql .= "     Round(((a.work_hour * 60) + a.work_min) / 60, 1) WORK_LABEL,  ";
		$sql .= "     Date_Format(a.EntryTime, '%Y-%m-%d') WORK_DAY,  ";
		$sql .= "     Date_Format(a.EntryTime, '%d') `DAY`,   ";
		$sql .= "     b.projectViewCode VIEW_PROJECT_CODE,   ";
		$sql .= "     b.ProjectName PROJECT_NAME,   ";
		$sql .= "     (  ";
		$sql .= "         Select   ";
		$sql .= "             x.activity_name   ";
		$sql .= "         From   ";
		$sql .= "             project_code_activity_tbl x   ";
		$sql .= "         Where x.product_code = Substr(a.project_code, 2, 1)   ";
		$sql .= "         And x.activity_code = a.activity_code  ";
		$sql .= "     ) ACTIVITY_NAME,   ";
		$sql .= "     a.*  ";
		$sql .= " From  ";
		$sql .= "     dallyproject_addwork_tbl a Left Join  ";
		$sql .= "     (  ";
		$sql .= "         Select   ";
		$sql .= "             x.ProjectCode,  ";
		$sql .= "             x.projectViewCode,  ";
		$sql .= "             x.ProjectName  ";
		$sql .= "         From   ";
		$sql .= "             project_tbl x   ";
		$sql .= "     ) b On a.project_code = b.ProjectCode  ";
		$sql .= " Where MemberNo = '$memberID'  ";
		$sql .= " And a.EntryTime Between '$fromDt' And '$toDt'  ";
		
		$sql  = " Select   ";
		$sql .= "     'dallyproject_addwork_tbl' DVS_CD,   ";
		$sql .= "     Round(((a.work_hour * 60) + a.work_min) / 60, 1) WORK_LABEL,   ";
		$sql .= "     Date_Format(a.EntryTime, '%Y-%m-%d') WORK_DAY,   ";
		$sql .= "     Date_Format(a.EntryTime, '%d') `DAY`,    ";
		$sql .= "     fn_get_proj_name(a.project_code) PROJ_NAME, ";
		$sql .= "     a.*   ";
		$sql .= " From   ";
		$sql .= "     dallyproject_addwork_tbl a  ";
		$sql .= " Where MemberNo = '$memberID'   ";
		$sql .= " And a.EntryTime Between '$fromDt' And '$toDt' ";
		$resource = mysql_query($sql, $db);
		
		if (!$resource) {
			echo "mysql_query(dallyproject_addwork_tbl_resource) error";
			return;
		}
		
		while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
			if (array_key_exists($row["WORK_DAY"], $rtnData)) {
				if (!is_array($rtnData[$row["WORK_DAY"]][$row["DVS_CD"]])) {
					$rtnData[$row["WORK_DAY"]][$row["DVS_CD"]] = array();
				}
				
				$row["EDIT_TF"] = "T";
				$row["ADD_TF"] = "T";
				array_push($rtnData[$row["WORK_DAY"]][$row["DVS_CD"]], $row);
			}
		}
		
		return $rtnData;
	}
}


// 끝
//==================================

?>
