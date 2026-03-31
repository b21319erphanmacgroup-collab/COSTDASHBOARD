<?php
	include_once realpath(dirname(__FILE__) . "/../../sys/inc/dbcon.inc");
	include_once realpath(dirname(__FILE__) . "/../../sys/inc/function_intranet.php");
	include_once realpath(dirname(__FILE__) . "/../../sys/inc/function_timework.php");
	include_once realpath(dirname(__FILE__) . "/../../../SmartyConfig.php");
	include_once realpath(dirname(__FILE__) . "/../../sys/inc/getNeedDate.php"); //로직에 사용되는 PHP시간&날짜 정의
	include_once realpath(dirname(__FILE__) . "/../util/OracleClass.php");
	include_once realpath(dirname(__FILE__) . "/../util/dashBoard/costDashBoardCommon.php");
	require_once realpath(dirname(__FILE__) . "/../util/dashBoard/session_init.php");
	require_once realpath(dirname(__FILE__) . "/../util/dashBoard/SessionAuth.php");
	require_once realpath(dirname(__FILE__) . "/costDashBoardBatch.php");
	require_once($SmartyClassPath);
	
	class CostDashBoard {
		var $smarty;
		var $oracle;
		var $companyCode;
		
		function CostDashBoard($smarty) {
			$Controller = isset($_REQUEST ["Controller"]) ? $_REQUEST ["Controller"] : "costDashBoard_controller.php";
			$ActionMode = $_REQUEST ["ActionMode"];
			$MainAction = $_REQUEST ["MainAction"];
			$SubAction = $_REQUEST ["$SubAction"];

			$this->companyCode = searchCompanyKind();
			$this->smarty = $smarty;
			$this->smarty->assign('Controller', $Controller );
			$this->smarty->assign('ActionMode', $ActionMode );
			$this->smarty->assign('MainAction', $MainAction );
			$this->smarty->assign('SubAction', $SubAction );
			$this->smarty->assign('session', $_SESSION);
		}
		
		function batch() {
			global $MainAction, $SubAction, $db;
			
			$token = $_REQUEST["token"];
			if ($token != "dashboard") {
				$result = array(
					"success"=> false,
					"rows"=> array(),
					"error"=> array(
						"message"=> "invalid access"
					)
				);
				
				writeLog("D:/APM_Setup/htdocs/intranet/sys/log/dashboard/", json_encode($_REQUEST), "INVALID_ACCESS");
				echo json_encode($result);
				return;
			}
			
			switch ($SubAction) {
				case "insertUserData" :
					$startDayFormat = convertDateFormat($_REQUEST["startDay"]);
					$endDayFormat = convertDateFormat($_REQUEST["endDay"]);
					$startDay = $startDayFormat["yyyy_mm_dd"];
					$endDay = $endDayFormat["yyyy_mm_dd"];
					$deptNo = $_REQUEST["deptNo"];
					
					if (!$endDayFormat["valid"]) {
						$endDay = Date("Y-m-d");
					}
					
					if (!$startDayFormat["valid"]) {
						$startDay = Date("Y-m-d", strtotime($endDay . " -6 days"));
					}
					
					$costBoardBatch = new CostBoardBatch();
					$result = $costBoardBatch->insertUserDataBatch($startDay, $endDay, $deptNo);
					
					writeLog("D:/APM_Setup/htdocs/intranet/sys/log/dashboard/", json_encode($_REQUEST), "REQUEST");
					writeLog("D:/APM_Setup/htdocs/intranet/sys/log/dashboard/", json_encode($result));
					
					echo json_encode($result);
					break;
				case "insertSlipData" :
					$startDayFormat = convertDateFormat($_REQUEST["startDay"]);
					$endDayFormat = convertDateFormat($_REQUEST["endDay"]);
					
					$startDay = $startDayFormat["yyyymmdd"];
					$endDay = $endDayFormat["yyyymmdd"];
					
					if (!$endDayFormat["valid"]) {
						$endDay = Date("Ymd");
					}
					
					if (!$startDayFormat["valid"]) {
						$startDay = Date("Ymd");
					}
					
					$costBoardBatch = new CostBoardBatch();
					$result = $costBoardBatch->insertSlipDataBatch($startDay, $endDay);
					
					writeLog("D:/APM_Setup/htdocs/intranet/sys/log/dashboard/", json_encode($_REQUEST), "REQUEST");
					writeLog("D:/APM_Setup/htdocs/intranet/sys/log/dashboard/", json_encode($result));
					
					if (!$result["success"]) {
						echo $result["error"]["message"];
					}
					
					echo json_encode($result);
					break;
				case "insertCategory":
					$this->oracle = new OracleClass($smarty);
					
					$cnt = 0;
					$sqlCnt = 0;
					if (($handle = fopen("mhslip.csv", "r")) !== FALSE) {
						while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
							// 각 셀을 UTF-8로 변환
							foreach ($data as &$value) {
								$value = iconv("CP949", "UTF-8", $value);
							}
							
							if ($cnt == 0) {
								$cnt++;
								continue;
							}
							
							$GROUP_DVS_CD = $data[3];
							$GROUP_CODE = $data[4];
							$ACNT_CODE = $data[5];
							
							if ($GROUP_DVS_CD != "") {
								$sql  = " Insert Into MH_SLIP_CATEGORY_DTL ";
								$sql .= " ( ";
								$sql .= " GROUP_DVS_CD,  ";
								$sql .= " GROUP_CODE,  ";
								$sql .= " ACNT_CODE ";
								$sql .= " ) ";
								$sql .= " Values ";
								$sql .= " ( ";
								$sql .= " '$GROUP_DVS_CD',  ";
								$sql .= " '$GROUP_CODE',  ";
								$sql .= " '$ACNT_CODE' ";
								$sql .= " ) ";
								
								$insertResult = $this->oracle->executeDml($sql);
							}
						}
					}
					
					fclose($handle);
					exit;
					break;
				default:
					break;
			}
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
					$sql .= "      b.Name INTRANET_RANK_NAME, ";
					$sql .= "      c.Name INTRANET_GROUP_NAME, ";
					$sql .= "      a.*,  ";
					$sql .= "      (Select Max(Code) COMPANY_CODE From systemconfig_tbl Where SysKey = 'CompanyKind') INTRANET_COMPANY_CODE  ";
					$sql .= " From   ";
					$sql .= "      member_tbl a Left Join ";
					$sql .= "      ( ";
					$sql .= "         Select  ";
					$sql .= "             *  ";
					$sql .= "         From  ";
					$sql .= "             systemconfig_tbl  ";
					$sql .= "         Where SysKey = 'PositionCode' ";
					$sql .= "      ) b On a.RankCode = b.Code Left Join ";
					$sql .= "      ( ";
					$sql .= "         Select  ";
					$sql .= "             *  ";
					$sql .= "         From  ";
					$sql .= "             systemconfig_tbl  ";
					$sql .= "         Where SysKey = 'GroupCode'      ";
					$sql .= "      ) c On a.GroupCode = c.Code ";
					$sql .= " Where a.MemberNo = '$id'   ";
					$sql .= " And a.Pasword = '$pwd'   ";
					$sql .= " And a.WorkPosition <> 9        ";
					$resource = mysql_query($sql, $db);
					
					if (!$resource) {
						$response["rstCd"] = 500;
						$response["error"]["message"] = "mysql_query(resource) error";
						
						echo json_encode($response);
						return;
					}
					
					while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
						$satis = $this->getUserInfo("ALL", "$id", "1900-01-01", "1900-01-01");
						if (!$satis["success"]) {
							$response["rstCd"] = 500;
							$response["error"]["message"] = "mysql_query(satis user resource) error";
							
							echo json_encode($response);
							return;
						}
						
						$userInfo = array(
							"INTRANET"=> $row,
							"SATIS"=> $satis["rows"][0]
						);
						
						if (!$this->setSession($userInfo)) {
							$response["rstCd"] = 500;
							$response["error"]["message"] = "session set error";
							
							echo json_encode($response);
							return;
						}
						
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
				case "logout" : 
					$auth = new SessionAuth();
					return $auth->logout();
					
					break;
				default:
					$this->smarty->display("intranet/common_contents/dashboard/login.tpl");
					break;
			}
		}
		
		function setSession($userInfo) {
			$auth = new SessionAuth();
			return $auth->login($userInfo);
		}
		
		function findUserById($users, $userId, $colName = "USER_ID") {
			foreach ($users As $user) {
				if (isset($user[$colName]) && $user[$colName] == $userId) {
					return $user;
				}
			}
			
			return null;
		}
		
		function main() {
			global $db,$memberID,$SubAction;
			
			switch ($SubAction) {
				case "attendanceStatus":
					$deptNo = $_REQUEST["deptNo"];
					$startDay = $_REQUEST["startDay"];
					$endDay = $_REQUEST["endDay"];
					
					$attendance = $this->getUserInfo($deptNo, "ALL", $startDay, $endDay);
					if (!$attendance["success"]) {
						echo "attendance select error";
						return;
					}
					$loginUser = $this->findUserById($attendance["rows"], $_SESSION["CBD_INTRA_USER_ID"]);
					
					$this->smarty->assign("attendance", $attendance["rows"]);
					$this->smarty->assign("loginUser", $loginUser);
					
					$this->smarty->display("intranet/common_contents/dashboard/attendanceStatus.tpl");
					break;
				case "memberList":
					$deptNo = $_REQUEST["deptNo"];
					$startDay = $_REQUEST["startDay"];
					$endDay = $_REQUEST["endDay"];
					
					$userWorkData = $this->getSummaryData($startDay, $endDay, $deptNo, null);
					$businessTrip = $this->getSummaryData($startDay, $endDay, $deptNo, '03');
					
					$betweenDay = Get_BetweenDateArray($startDay, $endDay);
					$usersResult = $this->getMemberInfo($deptNo, $startDay, $endDay);
					if (!$usersResult["success"]) {
						echo "select user query error";
						return;
					}
					$users = $usersResult["rows"];
					$userWorkTime = $this->getWorkTimeSum($startDay, $endDay, $deptNo);
					$maxWorkHour = $this->getMaxHour($betweenDay);
					
					$this->smarty->assign("startDay", $startDay);
					$this->smarty->assign("endDay", $endDay);
					$this->smarty->assign("users", $users);
					$this->smarty->assign("userWorkTime", $userWorkTime);
					$this->smarty->assign("deptNo", $deptNo);
					$this->smarty->assign("maxWorkHour", $maxWorkHour);
					$this->smarty->assign("userWorkData", $userWorkData);
					$this->smarty->assign("businessTrip", $businessTrip);
					
					$this->smarty->display("intranet/common_contents/dashboard/memberList.tpl");
					break;
				case "projectDetailedSummary":
					$this->oracle = new OracleClass($smarty);
					
					$deptNo = $_REQUEST["deptNo"];
					$startDay = $_REQUEST["startDay"];
					$endDay = $_REQUEST["endDay"];
					
					$sql  = " Select  ";
					$sql .= "     x.xx_proj_code1,  ";
					$sql .= "     Max(x.PROJ_NAME) PROJ_NAME,  ";
					$sql .= "     Max(x.JOB_KIND) JOB_KIND,  ";
					$sql .= "     Max(x.JOB_NAME) JOB_NAME,  ";
					$sql .= "     Max(x.oldProjectCode) oldProjectCode,  ";
					$sql .= "     Sum(x.MH) MH_SUM,  ";
					$sql .= "     x.RankCode, ";
					$sql .= "     Count(Distinct x.USER_ID) As USER_CNT, ";
					$sql .= "     ( ";
					$sql .= "         Select ";
					$sql .= "             Group_Concat(c.KorName, '(', c.CNT, ')' Order By c.KorName) LABEL ";
					$sql .= "         From ";
					$sql .= "             ( ";
					$sql .= "                 Select ";
					$sql .= "                     a.xx_proj_code1, ";
					$sql .= "                     b.RankCode, ";
					$sql .= "                     b.KorName, ";
					$sql .= "                     Count(Distinct a.USER_ID) CNT ";
					$sql .= "                 From  ";
					$sql .= "                     mh_work_summary a Left Join  ";
					$sql .= "                     member_tbl b On a.USER_ID = b.MemberNo ";
					$sql .= "                 Where a.WORK_DAY Between '$startDay' And '$endDay' ";
					$sql .= "                 And a.DEPT_NO = '$deptNo' ";
					$sql .= "                 Group By ";
					$sql .= "                     a.xx_proj_code1, ";
					$sql .= "                     b.RankCode, ";
					$sql .= "                     b.KorName ";
					$sql .= "             ) c ";
					$sql .= "         Where c.xx_proj_code1 = x.xx_proj_code1 ";
					$sql .= "         And c.RankCode = x.RankCode ";
					$sql .= "         Group By ";
					$sql .= "             c.xx_proj_code1, ";
					$sql .= "             c.RankCode ";
					$sql .= "      ) LABEL, ";
					$sql .= "      n.PROJECT_NORMAL_MH, ";
					$sql .= "      m.PROJECT_OVER_MH ";
					$sql .= " From   ";
					$sql .= "     (    ";
					$sql .= "         Select ";
					$sql .= "             f.*,  ";
					$sql .= "             g.product_name JOB_NAME  ";
					$sql .= "         From  ";
					$sql .= "             (  ";
					$sql .= "                 Select     ";
					$sql .= "                     c.projectViewCode,     ";
					$sql .= "                     c.ProjectNickname PROJ_NAME,    ";
					$sql .= "                     c.oldProjectCode oldProjectCode,  ";
					$sql .= "                     SubStr(c.projectViewCode, 1, 2) JOB_KIND,  ";
					$sql .= "                     a.*,      ";
					$sql .= "                     b.Name,     ";
					$sql .= "                     b.para1 WORK_DVS_NAME,   ";
					$sql .= "                     d.RankCode,   ";
					$sql .= "                     d.korName, ";
					$sql .= "                     SubStr(d.korName, 1, 1) FIRST_NAME, ";
					$sql .= "                     e.Name POSITION_NAME   ";
					$sql .= "                 From      ";
					$sql .= "                     mh_work_summary a Left Join     ";
					$sql .= "                     (     ";
					$sql .= "                         Select      ";
					$sql .= "                             *      ";
					$sql .= "                         From      ";
					$sql .= "                             systemconfig_tbl      ";
					$sql .= "                         Where SysKey = 'SummaryUserStateCode'      ";
					$sql .= "                     ) b On a.WORK_DVS_CD = b.Code Left Join    ";
					$sql .= "                     project_tbl c On a.xx_proj_code1 = c.ProjectCode Left Join     ";
					$sql .= "                     member_tbl d On a.USER_ID = d.MemberNo Left Join    ";
					$sql .= "                     (   ";
					$sql .= "                         Select    ";
					$sql .= "                             *    ";
					$sql .= "                         From    ";
					$sql .= "                             systemconfig_tbl    ";
					$sql .= "                         Where SysKey = 'PositionCode'   ";
					$sql .= "                     ) e On d.RankCode = e.Code   ";
					$sql .= "                 Where a.WORK_DAY Between '$startDay' And '$endDay'     ";
					$sql .= "                 And a.DEPT_NO = '$deptNo'     ";
					$sql .= "             ) f Left Join ";
					$sql .= "             ( ";
					$sql .= "                 Select      ";
					$sql .= "                     *      ";
					$sql .= "                 From      ";
					$sql .= "                     project_code_product_tbl      ";
					$sql .= "             ) g On f.JOB_KIND = g.product_code ";
					$sql .= "     ) x Left Join  ";
					$sql .= "     ( ";
					$sql .= "         Select ";
					$sql .= "             xx_proj_code1, ";
					$sql .= "             WORK_DVS_CD, ";
					$sql .= "             Sum(MH) PROJECT_NORMAL_MH ";
					$sql .= "         From ";
					$sql .= "             mh_work_summary ";
					$sql .= "         Where 1 = 1 ";
					$sql .= "         And WORK_DAY Between '$startDay' And '$endDay'  ";
					$sql .= "         And DEPT_NO = '$deptNo' ";
					$sql .= "         And WORK_DVS_CD <> '04' ";
					$sql .= "         Group By ";
					$sql .= "             xx_proj_code1 ";
					$sql .= "     ) n On x.xx_proj_code1 = n.xx_proj_code1 Left Join ";
					$sql .= "     ( ";
					$sql .= "         Select ";
					$sql .= "             xx_proj_code1, ";
					$sql .= "             WORK_DVS_CD, ";
					$sql .= "             Sum(MH) PROJECT_OVER_MH ";
					$sql .= "         From ";
					$sql .= "             mh_work_summary ";
					$sql .= "         Where 1 = 1 ";
					$sql .= "         And WORK_DAY Between '$startDay' And '$endDay'  ";
					$sql .= "         And DEPT_NO = '$deptNo' ";
					$sql .= "         And WORK_DVS_CD = '04' ";
					$sql .= "         Group By ";
					$sql .= "             xx_proj_code1 ";
					$sql .= "     ) m On x.xx_proj_code1 = m.xx_proj_code1 ";
					$sql .= " Group By  ";
					$sql .= "     x.xx_proj_code1,  ";
					$sql .= "     x.RankCode  ";
					$sql .= " Order By  ";
					$sql .= "     Case ";
					$sql .= "         When Max(x.JOB_KIND) In ('BT', 'BF') Then 1 ";
					$sql .= "         When Max(x.JOB_KIND) In ('BG') Then 3 ";
					$sql .= "         Else ";
					$sql .= "             2 ";
					$sql .= "     End, ";
					$sql .= "     x.xx_proj_code1  ";
					$resource = mysql_query($sql, $db);
					
					if (!$resource) {
						echo "mysql_query(resource) error";
						return;
					}
					
					$expenses = array(
						"BUSINESS_TRIP"=> $this->getSlipGroupData("BUSINESS_TRIP", $deptNo),
						"WELFARE"=> $this->getSlipGroupData("WELFARE", $deptNo),
						"ETC"=> $this->getSlipGroupData("ETC", $deptNo)
					);
					
					$summary = array();
					$projectInfo = array();
					while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
						$projCode = $row["xx_proj_code1"];
						$projName = $row["PROJ_NAME"];
						$rankCode = $row["RankCode"];
						$jobName = $row["JOB_NAME"];
						$oldProjectCode = $row["oldProjectCode"];
						$row["MH_SUM"] = sprintf("%0.1f", $row["MH_SUM"] / 60);
						
						if (!is_array($projectInfo[$projCode])) {
							$projectInfo[$projCode] = array();
							$projectInfo[$projCode]["PROJ_NAME"] = $projName;
							$projectInfo[$projCode]["intranetProjCode"] = $projCode;
							$projectInfo[$projCode]["oldProjectCode"] = $oldProjectCode;
							$projectInfo[$projCode]["isPM"] = "F";
							$projectInfo[$projCode]["JOB_NAME"] = $jobName;
							
							$projectNormalMH = round(($row["PROJECT_NORMAL_MH"] / 60), 1);
							$projectOverMH = round(($row["PROJECT_OVER_MH"] / 60), 1);
							$projectInfo[$projCode]["NORMAL_MH"] = $projectNormalMH;
							$projectInfo[$projCode]["OVER_MH"] = $projectOverMH;
							
							$projectInfo[$projCode]["MH"] = $projectNormalMH + $projectOverMH;
							$projectInfo[$projCode]["BUSINESS_TRIP"] = $expenses["BUSINESS_TRIP"][$deptNo][$projCode];
							$projectInfo[$projCode]["WELFARE"] = $expenses["WELFARE"][$deptNo][$projCode];
							$projectInfo[$projCode]["ETC"] = $expenses["ETC"][$deptNo][$projCode];
							
							$projectInfo[$projCode]["BUSINESS_TRIP_TOTAL"] = $expenses["BUSINESS_TRIP"]["TOTAL"][$projCode];
							$projectInfo[$projCode]["WELFARE_TOTAL"] = $expenses["WELFARE"]["TOTAL"][$projCode];
							$projectInfo[$projCode]["ETC_TOTAL"] = $expenses["ETC"]["TOTAL"][$projCode];
							
							if ($row["JOB_KIND"] == "BT" || $row["JOB_KIND"] == "BF") {
								$projectInfo[$projCode]['REVENUE_TYPE'] = "REVENUE";
								$projectInfo[$projCode]['CLASS_NAME'] = "hanmacgreen";
							} else if ($row["JOB_KIND"] == "BG") {
								$projectInfo[$projCode]['REVENUE_TYPE'] = "COMMON";
								$projectInfo[$projCode]['CLASS_NAME'] = "hanmacgray";
							} else {
								$projectInfo[$projCode]['REVENUE_TYPE'] = "NON_REVENUE";
								$projectInfo[$projCode]['CLASS_NAME'] = "hanmacbrown";
							}
						}
						
						if (!is_array($summary[$projCode])) {
							$summary[$projCode] = array();
						}
						
						if (!is_array($summary[$projCode][$rankCode])) {
							$summary[$projCode][$rankCode] = array();
						}
						
						array_push($summary[$projCode][$rankCode], $row);
					}
					
					$laborCosts = $this->getMonthlyProjectLaborCost($startDay, $endDay, $deptNo);
					foreach ($projectInfo As $projCode => $values) {
						$projectInfo[$projCode]["LABORCOST"] =  $laborCosts["data"][$deptNo][$projCode];
						$projectInfo[$projCode]["LABORCOST_TOTAL"] =  $laborCosts["data"]["TOTAL"][$projCode];
						
						$projectInfo[$projCode]["SLIP_SUM"] = $projectInfo[$projCode]["LABORCOST"] + $projectInfo[$projCode]["BUSINESS_TRIP"]
																+ $projectInfo[$projCode]["WELFARE"] + $projectInfo[$projCode]["ETC"];
					}
					
					$oldProjects = array();
					$projcetMatcher = array();
					foreach ($projectInfo As $projCode => $info) {
						$projcetMatcher[$info["oldProjectCode"]] = $info["intranetProjCode"];
						
						array_push($oldProjects, $info["oldProjectCode"]);	
					}
					$result = "'" . implode("','", $oldProjects) . "'";
					
					$sql  = " Select ";
					$sql .= "     a.* ";
					$sql .= " From ";
					$sql .= "     ( ";
					$sql .= "         Select  ";
					$sql .= "             PROJ_CODE, ";
					$sql .= "             Max(PM_EMPNO) PM_EMPNO, ";
					$sql .= "             Max(PM_EMPNO2) PM_EMPNO2, ";
					$sql .= "             Max(TM_EMPNO) TM_EMPNO, ";
					$sql .= "             Max(PO_EMPNO) PO_EMPNO ";
					$sql .= "         From  ";
					$sql .= "             CS_CONT_REGISTER ";
					$sql .= "         Group By ";
					$sql .= "             PROJ_CODE ";
					$sql .= "     ) a ";
					$sql .= " Where 1 = 1 ";
					$sql .= " And Exists ( ";
					$sql .= "     Select 1 ";
					$sql .= "     From  ";
					$sql .= "         COMPANY_DEPT_USER x ";
					$sql .= "     Where 1 = 1 ";
					$sql .= "     And x.DEPT_NO = '$deptNo' ";
					$sql .= "     And x.USER_ID In (a.PM_EMPNO, a.PM_EMPNO2, a.TM_EMPNO, a.PO_EMPNO) ";
					$sql .= " ) ";
					$result = $this->oracle->executeSelect($sql);
					
					if (!$result["success"]) {
						echo "PM_TAG Select Error";
						return;
					}
					
					for ($i = 0; $i < count($result[rows]); $i++) {
						$row = $result[rows][$i];
						$intranetProjCode = $projcetMatcher[$row[PROJ_CODE]];
						
						if (array_key_exists($intranetProjCode, $projectInfo)) {
							$projectInfo[$intranetProjCode]["isPM"] = "T";
						} 
					}
					
					$this->smarty->assign("startDay", $startDay);
					$this->smarty->assign("endDay", $endDay);
					$this->smarty->assign("summary", $summary);
					$this->smarty->assign("projectInfo", $projectInfo);
					$this->smarty->assign("stdHour", 80);
					
					$this->smarty->display("intranet/common_contents/dashboard/projectDetailedSummary.tpl");
					break;
				case "searchUser":
					$this->searchUser();
					break;
				case "getMhUnitPrint":
					$deptNo = $_REQUEST["deptNo"];
					$startDay = $_REQUEST["startDay"];
					$endDay = $_REQUEST["endDay"];
					
					$response = array(
						"rstCd"=> "200",
						"data"=> array(),
						"error"=> array()
					);
					
					$unitResult = $this->getMhUnitPrice($startDay, $endDay);
					if (!$unitResult["success"]) {
						$response["rstCd"] = "500";
						$response["error"]["message"] = "getMhUnitPrice Select Error";
						echo json_encode($response);
					}
					
					$response["data"] = $unitResult["rows"];
					echo json_encode($response);
					break;
				default:
					$depts = array(
						"TDC"=> array(),
						"GPD"=> array()
					);
					
					// TDC
					$result = $this->getDepts("500");
					if (!$result["success"]) {
						echo "select tdc query error";
						return;
					}
					$depts["TDC"] = $result["rows"];
					
					// GPD
					$result = $this->getDepts("100");
					if (!$result["success"]) {
						echo "select gpd query error";
						return;
					}
					$depts["GPD"] = $result["rows"];
					
					$this->smarty->assign("depts", $depts);
					
					$endDay = Date("Y-m-d", strtotime(Date("Y-m-d") . " -1 days"));
					$startDay = Date("Y-m-d", strtotime($endDay . " -5 days"));
					
					/*
					$endDay = "2026-03-22";
					$startDay = "2026-03-16";
					*/
					$userDept = $_SESSION["CBD_SATIS_TEAM_CODE"];
					$usersResult = $this->getUserInfo($userDept, "ALL", $startDay, $endDay);
					if (!$usersResult["success"]) {
						echo "select user query error";
						return;
					}
					
					$users = array();
					$rows = $usersResult["rows"];
					for ($i = 0; $i < count($rows); $i++) {
						$row = $rows[$i];
						
						if (!is_array($users[$row["DEPT_NO"]])) {
							$users[$row["DEPT_NO"]] = array();
						}
						
						array_push($users[$row["DEPT_NO"]], $row);
					}
					
					$userPrentDepts = $this->getParentDeptList();
					if (!$userPrentDepts["success"]) {
						echo "getParentDeptList select error";
						return;
					}
					
					
					$unitPriceResult = $this->getMhUnitPrice($startDay, $endDay);
					if (!$unitPriceResult["success"]) {
						echo "unitPriceResult Error";
						return;
					}
					
					$this->smarty->assign("userPrentDepts", $userPrentDepts["rows"]);
					$this->smarty->assign("startDay", $startDay);
					$this->smarty->assign("endDay", $endDay);
					$this->smarty->assign("users", $users);
					$this->smarty->assign("userDept", $userDept);
					$this->smarty->assign("unitPriceResult", $unitPriceResult["rows"]);
					
					$this->smarty->display("intranet/common_contents/dashboard/dashBoard.tpl");
					break;
			}
		}
		
		function report() {
			global $db,$memberID,$SubAction;
			
			switch ($SubAction) {
				case "":
					break;
				default:
					break;
			}
		}
		
		function getSlipGroupData($dvsCd, $deptNo) {
			$this->oracle = new OracleClass($smarty);
			$sql  = " Select   ";
			$sql .= "     CoalEsce(a.TEAM_CODE, 'TOTAL') TEAM_CODE, ";
			$sql .= "     a.INTRANET_PROJECT_CODE,  ";
			$sql .= "     Sum(AMT)  AMT_SUM  ";
			$sql .= " From   ";
			$sql .= "     MH_SLIP_SUMMARY a   ";
			$sql .= " Where 1 = 1   ";
			$sql .= " And Exists (   ";
			$sql .= "    Select 1   ";
			$sql .= "    From    ";
			$sql .= "        MH_SLIP_CATEGORY x   ";
			$sql .= "    Where x.GROUP_DVS_CD = '$dvsCd'   ";
			$sql .= "    And x.GROUP_CODE = a.GROUP_CODE   ";
			$sql .= " )   ";
			$sql .= " And INTRANET_PROJECT_CODE Is Not Null  ";
			$sql .= " And TEAM_CODE = '$deptNo' ";
			$sql .= " Group By Grouping Sets ( ";
			$sql .= "     (a.TEAM_CODE, a.INTRANET_PROJECT_CODE), ";
			$sql .= "     (a.INTRANET_PROJECT_CODE)  ";
			$sql .= " ) ";
			$oracleResult = $this->oracle->executeSelect($sql);
			
			$result = array(
				"$deptNo"=> array(),
				'TOTAL'=> array()
			);
			for ($i = 0; $i < count($oracleResult["rows"]); $i++) {
				$row = $oracleResult["rows"][$i];
				
				$code = $row["INTRANET_PROJECT_CODE"];
				$value = $row["AMT_SUM"];
				
				if ($row["TEAM_CODE"] == "TOTAL") {
					$result[TOTAL][$code] = $value;
				} else {
					$result[$deptNo][$code] = $value;
				}
			}
			
			return $result;
		}
		
		function getMemberInfo($deptNo, $startDay, $endDay) {
			global $db;
			
			$result = array(
				"success"=> true
			);
			
			$sql  = " Select ";
			$sql .= "     USER_ID ";
			$sql .= " From ";
			$sql .= "     mh_work_summary ";
			$sql .= " Where DEPT_NO = '$deptNo' ";
			$sql .= " And WORK_DAY Between '$startDay' And '$endDay' ";
			$sql .= " Group By ";
			$sql .= "     USER_ID ";
			$resource = mysql_query($sql, $db);
			
			if (!$resource) {
				$result["success"] = false;
				return $result;
			}
			
			$userIds = array();
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				$userIds[] = "'" . $row['USER_ID'] . "'";
			}
			$in_clause = implode(",", $userIds);
			
			$startDay = preg_replace('/[^0-9]/', '', $startDay);
			$endDay = preg_replace('/[^0-9]/', '', $endDay);
			$startYear = substr($startDay, 0, 4);
			$startMonth = substr($startDay, 4, 2);
			$endYear = substr($endDay, 0, 4);
			$endMonth = substr($endDay, 4, 2);
			if(!empty($in_clause)){
				$this->oracle = new OracleClass($smarty);
				$sql  = " Select      ";
				$sql .= "     a.EMP_NO USER_ID, ";
				$sql .= "     a.TITLE_CODE POSITION_CODE, ";
				$sql .= "     a.EMP_NAME USER_NAME,   ";
				$sql .= "     a.TEAM_CODE, ";
				$sql .= "     c.*,     ";
				$sql .= "     d.GRADE_NAME,    ";
				$sql .= "     e.TITLE_NAME,   ";
				$sql .= "     h.REF_NAME TEAM_NAME,    ";
				$sql .= "     f.*   ";
				$sql .= " From     ";
				$sql .= "     HR_PERS_MASTER a,   ";
				$sql .= "     (      ";
				$sql .= "         Select   ";
				$sql .= "             b.*   ";
				$sql .= "         From    ";
				$sql .= "             (   ";
				$sql .= "                 Select   ";
				$sql .= "                     a.*,   ";
				$sql .= "                     Row_Number() Over (   ";
				$sql .= "                         Partition By EMP_NO   ";
				$sql .= "                         Order By    ";
				$sql .= "                             APPLY_ORDER_DATE Desc,    ";
				$sql .= "                             SEQ Desc   ";
				$sql .= "                     ) RN   ";
				$sql .= "                 From    ";
				$sql .= "                     HR_ORDE_MASTER a ";
				$sql .= "                 Where APPLY_ORDER_DATE <= '$endDay'   ";
				$sql .= "             ) b   ";
				$sql .= "         Where RN = 1    ";
				$sql .= "     ) c,      ";
				$sql .= "     HR_CODE_GRADE d,    ";
				$sql .= "     HR_CODE_TITLE e,   ";
				$sql .= "     (   ";
				$sql .= "         Select   ";
				$sql .= "             b.*   ";
				$sql .= "         From   ";
				$sql .= "             (   ";
				$sql .= "                 Select    ";
				$sql .= "                     a.MEMBERNO,   ";
				$sql .= "                     a.TARDY,    ";
				$sql .= "                     a.REMAIN_VACATION,   ";
				$sql .= "                     Trunc(a.REMAIN_VACATION / 8) REMAIN_DAY,   ";
				$sql .= "                     Mod(a.REMAIN_VACATION, 8) REMAIN_TIME   ";
				$sql .= "                 From    ";
				$sql .= "                     HR_WORKER_TOTAL a   ";
				$sql .= "                 Where 1 = 1   ";
				$sql .= "                 And a.DATE_Y = '$endYear'   ";
				$sql .= "                 And a.DATE_M = '$endMonth'   ";
				$sql .= "             ) b   ";
				$sql .= "     ) f,   ";
				$sql .= "     (   ";
				$sql .= "         Select   ";
				$sql .= "             *   ";
				$sql .= "         From   ";
				$sql .= "             HR_CODE_REF       ";
				$sql .= "         Where REF_GBN_CODE = '99'   ";
				$sql .= "         And REF_CODE <> '00'   ";
				$sql .= "     ) h   ";
				$sql .= " Where 1 = 1     ";
				$sql .= " And a.EMP_NO = c.EMP_NO     ";
				$sql .= " And c.GRADE_CODE = d.GRADE_CODE    ";
				$sql .= " And a.TITLE_CODE = e.TITLE_CODE(+)   ";
				$sql .= " And a.EMP_NO = f.MEMBERNO(+)    ";
				$sql .= " And a.TEAM_CODE = h.REF_CODE(+)      ";
				$sql .= " And a.EMP_NO In ($in_clause) ";
				$sql .= " Order By   ";
				$sql .= "     a.TITLE_CODE,   ";
				$sql .= "     c.GRADE_CODE   ";
				$result = $this->oracle->executeSelect($sql);
			}
			
			return $result;
		}
		
		function getParentDeptList($userId = null) {
			if (empty($userId)) {
				$userId = $_SESSION[CBD_INTRA_USER_ID];
			}
			
			$this->oracle = new OracleClass($smarty);
			$sql  = " Select       ";
			$sql .= "     a.*       ";
			$sql .= " From       ";
			$sql .= "     COMPANY_DEPT a     ";
			$sql .= " Where 1 = 1      ";
			$sql .= " Start With DEPT_NO In (     ";
			$sql .= "     Select     ";
			$sql .= "         x.TEAM_CODE     ";
			$sql .= "     From     ";
			$sql .= "         HR_PERS_MASTER x,     ";
			$sql .= "         COMPANY_DEPT z   ";
			$sql .= "     Where 1 = 1     ";
			$sql .= "     And x.TEAM_CODE = z.DEPT_NO     ";
			$sql .= "     And x.EMP_NO = '$userId'     ";
			$sql .= " )      ";
			$sql .= " Connect By       ";
			$sql .= "     Prior UP_DEPT_NO = DEPT_NO  ";
			$sql .= " Order By  ";
			$sql .= "     a.INNER_CD     ";
			$result = $this->oracle->executeSelect($sql);
			
			return $result;
		}
			
		
		function getMaxHour($betweenDay) {
			$result = array(
				"normal"=> 0,
				"holy"=> 0,
				"total"=> 0
			);
			
			for ($i = 0; $i < count($betweenDay); $i++) {
				if (holy($betweenDay[$i]) == "holyday") {
					$result["holy"] += 5;
					$result["total"] += 5;
				} else {
					$result["normal"] += 11;
					$result["total"] += 11;
				}
			}
			
			return $result;
		}
		
		function getMhUnitPrice($startDay, $endDay) {
			$this->oracle = new OracleClass($smarty);
			$sql  = " Select ";
			$sql .= "     a.*, ";
			$sql .= "     b.GRADE_NAME ";
			$sql .= " From     ";
			$sql .= "     ( ";
			$sql .= "         Select    ";
			$sql .= "             Max(x.TIME_AMT) TIME_AMT,  ";
			$sql .= "             x.GRADE_CODE,    ";
			$sql .= "             To_Char(To_Date(Max(x.YYYYMM), 'YYYYMM'), 'YYYY-MM') DISP_YM    ";
			$sql .= "         From    ";
			$sql .= "             PM_CODE_GRADEUNIT x,    ";
			$sql .= "             (    ";
			$sql .= "                 Select    ";
			$sql .= "                     To_Char(Add_Months(To_Date('$startDay', 'YYYY-MM-DD'), Level - 1), 'YYYYMM') As YM    ";
			$sql .= "                 From    ";
			$sql .= "                     Dual    ";
			$sql .= "                 Connect By    ";
			$sql .= "                     Level <= Months_Between(To_Date('$endDay', 'YYYY-MM-DD'), To_Date('$startDay', 'YYYY-MM-DD')) + 1    ";
			$sql .= "             ) y    ";
			$sql .= "         Where 1 = 1    ";
			$sql .= "         And x.YYYYMM <= y.YM    ";
			$sql .= "         Group By    ";
			$sql .= "             x.GRADE_CODE,    ";
			$sql .= "             y.YM    ";
			$sql .= "     ) a, ";
			$sql .= "     ( ";
			$sql .= "         Select ";
			$sql .= "             * ";
			$sql .= "         From ";
			$sql .= "             HR_CODE_GRADE ";
			$sql .= "     ) b ";
			$sql .= " Where 1 = 1 ";
			$sql .= " And a.GRADE_CODE In ( ";
			$sql .= "     'C0', ";
			$sql .= "     'E0', ";
			$sql .= "     'F0', ";
			$sql .= "     'J0' ";
			$sql .= " ) ";
			$sql .= " And a.GRADE_CODE = b.GRADE_CODE(+) ";
			$sql .= " Order By ";
			$sql .= "     a.DISP_YM Desc, ";
			$sql .= "     b.GRADE_CODE ";
			$unitPriceResult = $this->oracle->executeSelect($sql);
			
			$result = array();
			for ($i = 0; $i < count($unitPriceResult["rows"]); $i++) {
				$row = $unitPriceResult["rows"][$i];
				
				if (!is_array($result[$row["DISP_YM"]])) {
					$result[$row["DISP_YM"]] = array();
				}
				
				if (!is_array($result[$row["DISP_YM"]][$row["GRADE_CODE"]])) {
					$result[$row["DISP_YM"]][$row["GRADE_CODE"]] = array();
				}
				
				$result[$row["DISP_YM"]][$row["GRADE_CODE"]]["GRADE_NAME"] = $row["GRADE_NAME"];
				$result[$row["DISP_YM"]][$row["GRADE_CODE"]]["TIME_AMT"] = $row["TIME_AMT"];
			}
			
			$unitPriceResult["rows"] = $result;
			return $unitPriceResult;
		}
		
		function getMonthlyProjectLaborCost($startDay, $endDay, $deptNo) {
			global $db;
			$this->oracle = new OracleClass($smarty);
			
			$laborCost = array(
				"success"=> true,
				"data"=> array(
					$deptNo=> array(),
					"TOTAL"=> array()
				),
				"error"=> array()
			);
			$sql  = " Select   ";
			$sql .= "     a.YYYYMM STAN_YYYYMM, ";
			$sql .= "     m.YM COND_YM,   ";
			$sql .= "     a.GRADE_CODE,     ";
			$sql .= "     b.GRADE_NAME, ";
			$sql .= "     x.TIME_AMT ";
			$sql .= " From   ";
			$sql .= "     (   ";
			$sql .= "         Select   ";
			$sql .= "             To_Char(Add_Months(To_Date('$startDay', 'YYYY-MM-DD'), Level - 1), 'YYYYMM') As YM   ";
			$sql .= "         From   ";
			$sql .= "             Dual   ";
			$sql .= "         Connect By   ";
			$sql .= "             Level <= Months_Between(To_Date('$endDay', 'YYYY-MM-DD'), To_Date('$startDay', 'YYYY-MM-DD')) + 1   ";
			$sql .= "     ) m,   ";
			$sql .= "     (   ";
			$sql .= "         Select   ";
			$sql .= "             Max(g.TIME_AMT) TIME_AMT, ";
			$sql .= "             g.GRADE_CODE,   ";
			$sql .= "             m2.YM,   ";
			$sql .= "             Max(g.YYYYMM) As YYYYMM   ";
			$sql .= "         From   ";
			$sql .= "             PM_CODE_GRADEUNIT g,   ";
			$sql .= "             (   ";
			$sql .= "                 Select   ";
			$sql .= "                     To_Char(Add_Months(To_Date('$startDay', 'YYYY-MM-DD'), Level - 1), 'YYYYMM') As YM   ";
			$sql .= "                 From   ";
			$sql .= "                     Dual   ";
			$sql .= "                 Connect By   ";
			$sql .= "                     Level <= Months_Between(To_Date('$endDay', 'YYYY-MM-DD'), To_Date('$startDay', 'YYYY-MM-DD')) + 1   ";
			$sql .= "             ) m2   ";
			$sql .= "         Where 1 = 1   ";
			$sql .= "         And g.YYYYMM <= m2.YM   ";
			$sql .= "         Group By   ";
			$sql .= "             g.GRADE_CODE,   ";
			$sql .= "             m2.YM   ";
			$sql .= "     ) x,   ";
			$sql .= "     PM_CODE_GRADEUNIT a,   ";
			$sql .= "     (   ";
			$sql .= "         Select   ";
			$sql .= "             GRADE_CODE,   ";
			$sql .= "             GRADE_NAME   ";
			$sql .= "         From   ";
			$sql .= "             HR_CODE_GRADE   ";
			$sql .= "         Where USE_YN = 'Y'   ";
			$sql .= "         And GRADE_CODE > 'B2'   ";
			$sql .= "     ) b   ";
			$sql .= " Where 1 = 1   ";
			$sql .= " And m.YM = x.YM   ";
			$sql .= " And x.GRADE_CODE = a.GRADE_CODE   ";
			$sql .= " And x.YYYYMM = a.YYYYMM   ";
			$sql .= " And a.GRADE_CODE = b.GRADE_CODE(+)   ";
			$sql .= " Order By   ";
			$sql .= "     m.YM,   ";
			$sql .= "     a.GRADE_CODE   ";
			$larborCostResult = $this->oracle->executeSelect($sql);
			
			if (!$larborCostResult["success"]) {
				$laborCost["success"] = false;
				$laborCost["error"]['message'] = "getMonthlyProjectLaborCost larborCostResult error";
				return $laborCost;
			}
			
			$gradeUnits = array();
			for ($i = 0; $i < count($larborCostResult["rows"]); $i++) {
				$row = $larborCostResult["rows"][$i];
				
				if (!is_array($gradeUnits[$row["COND_YM"]])) {
					$gradeUnits[$row["COND_YM"]] = array();
				}
				
				$gradeUnits[$row["COND_YM"]][$row["GRADE_CODE"]] = $row["TIME_AMT"];
			}
			
			$sql  = " Select ";
			$sql .= "     date_format(a.WORK_DAY, '%Y%m') YM,  ";
			$sql .= "     c.projectViewCode,      ";
			$sql .= "     c.ProjectNickname PROJ_NAME,     ";
			$sql .= "     c.oldProjectCode oldProjectCode,   ";
			$sql .= "     a.*,       ";
			$sql .= "     b.Name,      ";
			$sql .= "     b.para1 WORK_DVS_NAME,    ";
			$sql .= "     d.RankCode,    ";
			$sql .= "     d.korName,  ";
			$sql .= "     e.para1,  ";
			$sql .= "     e.Name POSITION_NAME    ";
			$sql .= " From       ";
			$sql .= "     mh_work_summary a Left Join      ";
			$sql .= "     (      ";
			$sql .= "         Select       ";
			$sql .= "             *       ";
			$sql .= "         From       ";
			$sql .= "             systemconfig_tbl       ";
			$sql .= "         Where SysKey = 'SummaryUserStateCode'       ";
			$sql .= "     ) b On a.WORK_DVS_CD = b.Code Left Join     ";
			$sql .= "     project_tbl c On a.xx_proj_code1 = c.ProjectCode Left Join      ";
			$sql .= "     member_tbl d On a.USER_ID = d.MemberNo Left Join     ";
			$sql .= "     (    ";
			$sql .= "         Select     ";
			$sql .= "             *     ";
			$sql .= "         From     ";
			$sql .= "             systemconfig_tbl     ";
			$sql .= "         Where SysKey = 'PositionCode'    ";
			$sql .= "     ) e On d.RankCode = e.Code    ";
			$sql .= " Where a.WORK_DAY Between '$startDay' And '$endDay'      ";
			$resource = mysql_query($sql, $db);
			
			if (!$resource) {
				$laborCost["success"] = false;
				$laborCost["error"]['message'] = "getMonthlyProjectLaborCost mysql_query(resource) error";
				return $laborCost;
			}
			
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				$ym = $row["YM"];
				$unitPrice = $gradeUnits[$ym][$row["para1"]];
				$cost = $row["WORK_DVS_CD"] == "04" ? (round($row["MH"] / 60, 1) * $unitPrice * 1.5) : (round($row["MH"] / 60, 1) * $unitPrice * 1.0);
				
				if ($row["DEPT_NO"] == $deptNo) {
					$laborCost["data"][$deptNo][$row["xx_proj_code1"]] += $cost;
				}
				
				$laborCost["data"]["TOTAL"][$row["xx_proj_code1"]] += $cost;
			}
			
			return $laborCost;
		}
		
		function getWorkTimeSum($startDay, $endDay, $deptNo) {
			global $db;
		
			$sql  = " Select ";
			$sql .= "     x.DEPT_NO, ";
			$sql .= "     x.USER_ID, ";
			$sql .= "     CoalEsce(x.MH, 0) MH, ";
			$sql .= "     Round(CoalEsce(x.MH, 0) / 60, 2) MH_LABEL, ";
			$sql .= "     CoalEsce(y.OVER_MH, 0) OVER_MH, ";
			$sql .= "     Round(CoalEsce(y.OVER_MH, 0) / 60, 2) OVER_MH_LABEL ";
			$sql .= " From ";
			$sql .= "     ( ";
			$sql .= "         Select  ";
			$sql .= "             a.DEPT_NO,  ";
			$sql .= "             a.USER_ID,  ";
			$sql .= "             Sum(a.MH) MH  ";
			$sql .= "         From  ";
			$sql .= "             mh_work_summary a  ";
			$sql .= "         Where a.WORK_DAY Between '$startDay' And '$endDay'  ";
			$sql .= "         And a.DEPT_NO = '$deptNo'  ";
			$sql .= "         Group By  ";
			$sql .= "             a.DEPT_NO,  ";
			$sql .= "             a.USER_ID     ";
			$sql .= "     ) x Left Join ";
			$sql .= "     ( ";
			$sql .= "         Select  ";
			$sql .= "             a.DEPT_NO,  ";
			$sql .= "             a.USER_ID,  ";
			$sql .= "             Sum(a.MH) OVER_MH  ";
			$sql .= "         From  ";
			$sql .= "             mh_work_summary a  ";
			$sql .= "         Where a.WORK_DAY Between '$startDay' And '$endDay'  ";
			$sql .= "         And a.DEPT_NO = '$deptNo'  ";
			$sql .= "         And a.WORK_DVS_CD = '04'  ";
			$sql .= "         Group By  ";
			$sql .= "             a.DEPT_NO,  ";
			$sql .= "             a.USER_ID  ";
			$sql .= "     ) y On x.DEPT_NO = y.DEPT_NO And x.USER_ID = y.USER_ID ";
			$resource = mysql_query($sql, $db);
			
			if (!$resource) {
				echo "mysql_query(resource) error";
				return;
			}
			
			$fullData = array();
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				$row["MH_LABEL"] = sprintf("%0.1f", $row["MH"] / 60);
				$row["OVER_MH_LABEL"] = sprintf("%0.1f", $row["OVER_MH"] / 60);
				
				$fullData[$row["USER_ID"]] = $row;
			}
			
			return $fullData;
		}
		
		function getSummaryData($startDay, $endDay, $deptNo, $type = null) {
			global $db;
			
			if ($type == "03") {
				$sql  = " Select ";
				$sql .= "      Max(e.JOB_NAME) JOB_NAME,  ";
				$sql .= "      e.USER_ID,  ";
				$sql .= "      Max(e.WORK_DVS_NAME) WORK_DVS_NAME,  ";
				$sql .= "      e.xx_proj_code1 PROJ_CODE,  ";
				$sql .= "      Max(e.PROJ_NAME) PROJ_NAME,  ";
				$sql .= "      Sum(e.MH) MH_SUM, ";
				$sql .= "      Max(e.ACTIVITY_NAME) ACTIVITY_NAME ";
				$sql .= " From ";
				$sql .= "     ( ";
				$sql .= "         Select   ";
				$sql .= "             d.*   ";
				$sql .= "         From   ";
				$sql .= "             (   ";
				$sql .= "                 Select   ";
				$sql .= "                     k.product_name JOB_NAME,   ";
				$sql .= "                     c.projectViewCode,    ";
				$sql .= "                     c.ProjectNickname PROJ_NAME,   ";
				$sql .= "                     a.*,     ";
				$sql .= "                     b.Name,    ";
				$sql .= "                     b.para1 WORK_DVS_NAME   ";
				$sql .= "                 From     ";
				$sql .= "                     mh_work_summary a Left Join    ";
				$sql .= "                     (    ";
				$sql .= "                         Select     ";
				$sql .= "                             *     ";
				$sql .= "                         From     ";
				$sql .= "                             systemconfig_tbl     ";
				$sql .= "                         Where SysKey = 'SummaryUserStateCode'     ";
				$sql .= "                     ) b On a.WORK_DVS_CD = b.Code Left Join   ";
				$sql .= "                     project_tbl c On a.xx_proj_code1 = c.ProjectCode Left Join     ";
				$sql .= "                     ( ";
				$sql .= "                         Select  ";
				$sql .= "                             *  ";
				$sql .= "                         From  ";
				$sql .= "                             project_code_product_tbl  ";
				$sql .= "                     ) k On SubStr(c.projectViewCode, 1, 2) = k.product_code  ";
				$sql .= "                 Where a.WORK_DAY Between '$startDay' And '$endDay'    ";
				$sql .= "                 And a.DEPT_NO = '$deptNo'    ";
				$sql .= "                 And a.WORK_DVS_CD = '$type' ";
				$sql .= "             ) d ";
				$sql .= "     ) e ";
				$sql .= " Group By  ";
				$sql .= "     e.USER_ID,  ";
				$sql .= "     e.WORK_DVS_CD,  ";
				$sql .= "     e.xx_proj_code1  ";
				$sql .= " Order By  ";
				$sql .= "     e.USER_ID,  ";
				$sql .= "     e.xx_proj_code1  ";
			} else {
				$sql  = " Select  ";
				$sql .= "     Max(e.JOB_NAME) JOB_NAME,  ";
				$sql .= "     e.USER_ID,  ";
				$sql .= "     e.xx_proj_code1 PROJ_CODE,  ";
				$sql .= "     Max(e.PROJ_NAME) PROJ_NAME,  ";
				$sql .= "     Sum(e.MH) MH_SUM,  ";
				$sql .= "     CoalEsce(f.OVER_MH, 0) OVER_MH,  ";
				$sql .= "     Group_Concat(e.ACTIVITY_NAME) ACTIVITY_NAME   ";
				$sql .= " From  ";
				$sql .= "     (  ";
				$sql .= "         Select   ";
				$sql .= "             d.*   ";
				$sql .= "         From   ";
				$sql .= "             (   ";
				$sql .= "                 Select   ";
				$sql .= "                     k.product_name JOB_NAME,   ";
				$sql .= "                     c.projectViewCode,    ";
				$sql .= "                     c.ProjectNickname PROJ_NAME,   ";
				$sql .= "                     a.*,     ";
				$sql .= "                     b.Name,    ";
				$sql .= "                     b.para1 WORK_DVS_NAME   ";
				$sql .= "                 From     ";
				$sql .= "                     mh_work_summary a Left Join    ";
				$sql .= "                     (    ";
				$sql .= "                         Select     ";
				$sql .= "                             *     ";
				$sql .= "                         From     ";
				$sql .= "                             systemconfig_tbl     ";
				$sql .= "                         Where SysKey = 'SummaryUserStateCode'     ";
				$sql .= "                     ) b On a.WORK_DVS_CD = b.Code Left Join   ";
				$sql .= "                     project_tbl c On a.xx_proj_code1 = c.ProjectCode Left Join ";
				$sql .= "                     ( ";
				$sql .= "                         Select  ";
				$sql .= "                             *  ";
				$sql .= "                         From  ";
				$sql .= "                             project_code_product_tbl  ";
				$sql .= "                     ) k On SubStr(c.projectViewCode, 1, 2) = k.product_code  ";
				$sql .= "                 Where a.WORK_DAY Between '$startDay' And '$endDay'    ";
				$sql .= "                 And a.DEPT_NO = '$deptNo'    ";
				$sql .= "                 And a.WORK_DVS_CD Not In ('03', '04')  ";
				$sql .= "             ) d ";
				$sql .= "     ) e Left Join  ";
				$sql .= "     (  ";
				$sql .= "         Select  ";
				$sql .= "             USER_ID,  ";
				$sql .= "             xx_proj_code1,  ";
				$sql .= "             Sum(MH) OVER_MH  ";
				$sql .= "         From  ";
				$sql .= "             mh_work_summary  ";
				$sql .= "         Where WORK_DAY Between '$startDay' And '$endDay'    ";
				$sql .= "         And DEPT_NO = '$deptNo'    ";
				$sql .= "         And WORK_DVS_CD = '04'  ";
				$sql .= "         Group By  ";
				$sql .= "             USER_ID,  ";
				$sql .= "             xx_proj_code1  ";
				$sql .= "     ) f On e.USER_ID = f.USER_ID And e.xx_proj_code1 = f.xx_proj_code1  ";
				$sql .= " Group By  ";
				$sql .= "     e.USER_ID,  ";
				$sql .= "     e.xx_proj_code1  ";
				$sql .= " Order By  ";
				$sql .= "     e.USER_ID,  ";
				$sql .= "     e.xx_proj_code1  ";
			}
			$resource = mysql_query($sql, $db);
			
			if (!$resource) {
				echo "mysql_query($type resource) error";
				return;
			}
			
			$fullData = array();
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				if (!is_array($fullData[$row["USER_ID"]])) {
					$fullData[$row["USER_ID"]] = array();
				}
				
				if (!is_array($fullData[$row["USER_ID"]][$row["PROJ_CODE"]])) {
					$fullData[$row["USER_ID"]][$row["PROJ_CODE"]] = array();
				}
				
				array_push($fullData[$row["USER_ID"]][$row["PROJ_CODE"]], $row);
			}
			
			return $fullData;
		}
		
		function getUserInfo($deptNo, $userId, $startDay = null, $endDay = null) {
			$this->oracle = new OracleClass($smarty);
			
			if (empty($startDay)) {
				$startDay = date("Ymd");
			}
			
			if (empty($endDay)) {
				$endDay = date("Ymd");
			}
			
			$startDay = preg_replace('/[^0-9]/', '', $startDay);
			$endDay = preg_replace('/[^0-9]/', '', $endDay);
			
			$startYear = substr($startDay, 0, 4);
			$startMonth = substr($startDay, 4, 2);
			$endYear = substr($endDay, 0, 4);
			$endMonth = substr($endDay, 4, 2);
			
			$sql  = " Select    ";
			$sql .= "     a.*,   ";
			$sql .= "     b.USER_NAME,   ";
			$sql .= "     c.*,   ";
			$sql .= "     d.GRADE_NAME,  ";
			$sql .= "     e.TITLE_NAME, ";
			$sql .= "     g.TEAM_CODE, ";
			$sql .= "     h.REF_NAME TEAM_NAME,  ";
			$sql .= "     f.* ";
			$sql .= " From   ";
			$sql .= "     COMPANY_DEPT_USER a,   ";
			$sql .= "     SM_AUTH_USER b,   ";
			$sql .= "     (    ";
			$sql .= "         Select ";
			$sql .= "             b.* ";
			$sql .= "         From  ";
			$sql .= "             ( ";
			$sql .= "                 Select ";
			$sql .= "                     a.*, ";
			$sql .= "                     Row_Number() Over ( ";
			$sql .= "                         Partition By EMP_NO ";
			$sql .= "                         Order By  ";
			$sql .= "                             APPLY_ORDER_DATE Desc,  ";
			$sql .= "                             SEQ Desc ";
			$sql .= "                     ) ROW_NUM ";
			$sql .= "                 From  ";
			$sql .= "                     HR_ORDE_MASTER a ";
			$sql .= "             ) b ";
			$sql .= "         Where ROW_NUM = 1  ";
			$sql .= "     ) c,    ";
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
			$sql .= "                 And a.DATE_Y = '$endYear' ";
			$sql .= "                 And a.DATE_M = '$endMonth' ";
			$sql .= "             ) b ";
			$sql .= "     ) f, ";
			$sql .= "     HR_PERS_MASTER g, ";
			$sql .= "     ( ";
			$sql .= "         Select ";
			$sql .= "             * ";
			$sql .= "         From ";
			$sql .= "             HR_CODE_REF     ";
			$sql .= "         Where REF_GBN_CODE = '99' ";
			$sql .= "         And REF_CODE <> '00' ";
			$sql .= "     ) h ";
			$sql .= " Where a.USER_ID = b.USER_ID   ";
			$sql .= " And a.USER_ID = c.EMP_NO   ";
			$sql .= " And c.GRADE_CODE = d.GRADE_CODE  ";
			$sql .= " And a.POSITION_CODE = e.TITLE_CODE(+) ";
			$sql .= " And a.USER_ID = f.MEMBERNO(+)  ";
			$sql .= " And a.USER_ID = g.EMP_NO   ";
			$sql .= " And g.TEAM_CODE = h.REF_CODE(+)    ";
			$sql .= " And (Upper(a.USER_ID) = Upper('$userId') Or '$userId' = 'ALL')    ";
			$sql .= " And Nvl(g.RETIRE_DATE, '20991231') >= '$endDay'   ";
			$sql .= " And (a.DEPT_NO = '$deptNo' Or '$deptNo' = 'ALL')  ";
			$sql .= " Order By ";
			$sql .= "     a.POSITION_CODE, ";
			$sql .= "     c.GRADE_CODE ";
			$usersResult = $this->oracle->executeSelect($sql);
			
			return $usersResult;
		}
		
		function searchUser() {
			$this->oracle = new OracleClass($smarty);
			
			$response = array(
				"rstCd"=> "200",
				"data"=> array(),
				"error"=> array()
			);
			$searchText = $_REQUEST["searchText"];
			
			$sql  = " Select   ";
			$sql .= "     c.*   ";
			$sql .= " From   ";
			$sql .= "     COMPANY_DEPT c,   ";
			$sql .= "     (   ";
			$sql .= "         Select   ";
			$sql .= "             b.COMPANY_ID,    ";
			$sql .= "             b.DEPT_NO    ";
			$sql .= "         From    ";
			$sql .= "             (        ";
			$sql .= "                 Select      ";
			$sql .= "                     Connect_By_Root a.DEPT_NO AS START_DEPT,    ";
			$sql .= "                     a.*      ";
			$sql .= "                 From      ";
			$sql .= "                     COMPANY_DEPT a    ";
			$sql .= "                 Where 1 = 1     ";
			$sql .= "                 Start With DEPT_NO In (    ";
			$sql .= "                     Select    ";
			$sql .= "                         x.TEAM_CODE    ";
			$sql .= "                     From    ";
			$sql .= "                         HR_PERS_MASTER x, ";
			$sql .= "                         COMPANY_DEPT z  ";
			$sql .= "                     Where 1 = 1     ";
			$sql .= "                     And x.TEAM_CODE = z.DEPT_NO    ";
			$sql .= "                     And (x.EMP_NAME Like '%$searchText%' Or Upper(z.DEPT_NAME) Like '%' || Upper('$searchText') || '%')    ";
			$sql .= "                 )     ";
			$sql .= "                 Connect By      ";
			$sql .= "                     Prior UP_DEPT_NO = DEPT_NO    ";
			$sql .= "             ) b        ";
			$sql .= "         Group By   ";
			$sql .= "             b.COMPANY_ID,   ";
			$sql .= "             b.DEPT_NO    ";
			$sql .= "     ) d   ";
			$sql .= " Where c.COMPANY_ID = d.COMPANY_ID   ";
			$sql .= " And c.DEPT_NO = d.DEPT_NO   ";
			$sql .= " Order By    ";
			$sql .= "     c.INNER_CD    ";
			$usersResult = $this->oracle->executeSelect($sql);
			
			if (!$usersResult["success"]) {
				$response["error"]["message"] = "searchUser select query error";
				$response["rstCd"] = "500";
				echo json_encode($response);
				return;
			}
			
			$searchedData = array(
				"GPD"=> array(),
				"TDC"=> array()
			);
			
			for ($i = 0; $i < count($usersResult["rows"]); $i++) {
				$row = $usersResult["rows"][$i];
				
				if (substr($row["INNER_CD"], 0, 3) == "001") {	
					array_push($searchedData["GPD"], $row);
				}
				
				if (substr($row["INNER_CD"], 0, 3) == "002") {
					array_push($searchedData["TDC"], $row);
				}
			}
			
			$response["data"] = $searchedData;
			echo json_encode($response);
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
			$sql .= " Order By  ";
			$sql .= "     INNER_CD ";
			$result = $this->oracle->executeSelect($sql);
			
			return $result;
		}
	}

?>
