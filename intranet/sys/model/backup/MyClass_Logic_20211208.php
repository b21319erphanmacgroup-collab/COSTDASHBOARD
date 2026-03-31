<?php
include "../inc/dbcon.inc";
include "../../sys/inc/function_intranet.php";
include "../../../SmartyConfig.php";
include "../util/HanamcPageControl.php";

extract($_REQUEST);
class MyclassLogic {
	var $smarty;
	
	function MyclassLogic($smarty) {
		$Controller = "MyClass_Controller.php";
		$ActionMode = $_REQUEST['ActionMode'];
		
		$this->writeLog();
		$this->smarty=$smarty;
		$this->smarty->assign('Controller', $Controller);
		$this->smarty->assign('ActionMode', $ActionMode);
	}
	
	/******************************************************************************
	 기    능 : My Class
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : intranet/common_contents/work_lecture/MyClass.tpl : 교육실 > My Class
	 기    타 :
	 ******************************************************************************/
	function MyClass() {
		global $db;
		/* Parameter */
		global $memberID, $Company_Kind, $dept_code, $dept, $position_code, $position, $insert_member, $registration;
		
		/* [회사명 Select Start] */
		$sSql  = " Select ";
		$sSql .= "     * ";
		$sSql .= " From ";
		$sSql .= "     comp_systemconfig_tbl ";
		$sSql .= " Where comp_code = 'COMMON' ";
		$sSql .= " And syskey = 'COMPANY' ";
		$sSql .= " And code = '$Company_Kind' ";
		$sSql .= " Order By     ";
		$sSql .= "     orderno ";
		
		$resRtn = mysql_query($sSql);
		
		if (!$resRtn) {
			echo "CompanyNames Select Error";
			return;
		}
		
		$aCompanyName = mysql_fetch_array($resRtn, MYSQL_ASSOC);
		/* [회사명 Select End] */
		
		/* [클래스타입 Select Start] */
		$sSql  = " Select ";
		$sSql .= "     code CODE, ";
		$sSql .= "     name NAME, ";
		$sSql .= "     para1 CLASS_TYPE ";
		$sSql .= " From ";
		$sSql .= "     comp_systemconfig_tbl ";
		$sSql .= " Where syskey = 'RANK' ";
		$sSql .= " And comp_code = '$Company_Kind' ";
		$sSql .= " And code = '$position_code' ";
		
		$resRtn = mysql_query($sSql);
		
		if (!$resRtn) {
			echo "ClassType Select Error";
			return;
		}
		
		$aClassType = mysql_fetch_array($resRtn, MYSQL_ASSOC);
		/* [클래스타입 Select End] */
		
		$year = date("Y", time());
		$quarter = ceil(date("m", time()) / 3);
		$companyName = $aCompanyName["name"];
		$class_type = $aClassType["CLASS_TYPE"];
		$today = date("Y-m-d", time());
		
		/* [감상문 목록 Select Start] */
		$sSql  = " Select  ";
		$sSql .= "     x.*,  ";
		$sSql .= "     Case  ";
		$sSql .= "         When x.memberID Is Not Null Then 'T'  ";
		$sSql .= "         Else  ";
		$sSql .= "             'F'  ";
		$sSql .= "     End WRITE_TF   ";
		$sSql .= " From  ";
		$sSql .= "     (  ";
		$sSql .= "         Select  ";
		$sSql .= "             a.class_title,  ";
		$sSql .= "             a.class_content,  ";
		$sSql .= "             a.class_code,  ";
		$sSql .= "             a.orderno,  ";
		$sSql .= "             b.memberID,  ";
		$sSql .= "             b.registration,  ";
		$sSql .= "             b.class_type,  ";
		$sSql .= "             b.company,  ";
		$sSql .= "             b.dept,  ";
		$sSql .= "             b.position,  ";
		$sSql .= "             b.insert_member  ";
		$sSql .= "         From  ";
		$sSql .= "             ( ";
		$sSql .= "                 Select ";
		$sSql .= "                     * ";
		$sSql .= "                 From ";
		$sSql .= "                     class_list_tbl ";
		$sSql .= "                 Where year = '$year' ";
		$sSql .= "                 And quarter = '$quarter' ";
		$sSql .= "                 And class_type = '$class_type'  ";
		$sSql .= "             ) a Left Join  ";
		$sSql .= "             (  ";
		$sSql .= "                 Select  ";
		$sSql .= "                     *  ";
		$sSql .= "                 From  ";
		$sSql .= "                     class_impressions_tbl  ";
		$sSql .= "                 Where year = '$year' ";
		$sSql .= "                 And quarter = '$quarter' ";
		$sSql .= "                 And memberID = '$memberID'  ";
		$sSql .= "                 And class_type = '$class_type' ";
		$sSql .= "             ) b On a.year = b.year And a.quarter = b.quarter And a.class_code = b.class_code  ";
		$sSql .= "     ) x  ";
		$sSql .= " Order By  ";
		$sSql .= "     x.orderno,  ";
		$sSql .= "     x.class_code * 1  ";
		
		$resRtn = mysql_query($sSql);
		
		if (!$resRtn) {
			echo "MyClass Default Select Error";
			return;
		}
		
		$aFullDate = array();
		$nTotalCnt = 0;
		$nWriteCnt = 0;
		
		while ($aRow = mysql_fetch_array($resRtn, MYSQL_ASSOC)) {
			array_push($aFullDate, $aRow);
			$nTotalCnt++;
			
			if ($aRow[WRITE_TF] == "T") {
				$nWriteCnt++;
			}
		}
		
		$aFooter = array("class_title" => "총 $nTotalCnt 건", "WRITE_TOTAL" => "$nWriteCnt 건");
		array_push($aFullDate, $aFooter);
		/* [감상문 목록 Select End] */
		
		$this->smarty->assign("impressions_list", $aFullDate);
		$this->smarty->assign("year", $year);
		$this->smarty->assign("quarter", $quarter);
		$this->smarty->assign("today", $today);
		$this->smarty->assign("class_type", $class_type);
		$this->smarty->assign("memberID", $memberID);
		$this->smarty->assign("Company_Kind", $Company_Kind);
		$this->smarty->assign("companyName", $companyName);
		$this->smarty->assign("dept_code", $dept_code);
		$this->smarty->assign("dept", $dept);
		$this->smarty->assign("position_code", $position_code);
		$this->smarty->assign("position", $position);
		$this->smarty->assign("insert_member", $insert_member);
		$this->smarty->assign("registration", $registration);
		
		$this->smarty->display("intranet/common_contents/work_myclass/MyClass.tpl");
	}
	
	/******************************************************************************
	 기    능 : My Class 소감 리스트 가져오기
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : intranet/common_contents/work_lecture/MyClass.tpl : 교육실 > My Class
	 기    타 :
	 ******************************************************************************/
	function MyClass_GetList() {
		global $db;
		global $year, $quarter, $class_type, $memberID, $registration;
		
		$sSql  = " Select   ";
		$sSql .= "     x.*,   ";
		$sSql .= "     Case   ";
		$sSql .= "         When x.memberID Is Not Null Then 'T'   ";
		$sSql .= "         Else   ";
		$sSql .= "             'F'   ";
		$sSql .= "     End WRITE_TF,   ";
		$sSql .= "     Case ";
		$sSql .= "         When CurDate() >= x.start_date And CurDate() <= x.end_date Then 'T' ";
		$sSql .= "         Else ";
		$sSql .= "             'F' ";
		$sSql .= "     End EDIT_TF    ";
		$sSql .= " From   ";
		$sSql .= "     (   ";
		$sSql .= "         Select   ";
		$sSql .= "             a.class_title,   ";
		$sSql .= "             a.class_content,   ";
		$sSql .= "             a.class_code,   ";
		$sSql .= "             a.start_date,   ";
		$sSql .= "             a.end_date,   ";
		$sSql .= "             a.orderno,   ";
		$sSql .= "             b.memberID,  ";
		$sSql .= "             b.registration,   ";
		$sSql .= "             b.class_type,   ";
		$sSql .= "             b.company,   ";
		$sSql .= "             b.dept,   ";
		$sSql .= "             b.position,   ";
		$sSql .= "             b.insert_member   ";
		$sSql .= "         From   ";
		$sSql .= "             (  ";
		$sSql .= "                 Select  ";
		$sSql .= "                     *  ";
		$sSql .= "                 From  ";
		$sSql .= "                     class_list_tbl  ";
		$sSql .= "                 Where year = '$year'  ";
		$sSql .= "                 And quarter = '$quarter'  ";
		$sSql .= "                 And class_type = '$class_type'  ";
		$sSql .= "             ) a Left Join   ";
		$sSql .= "             (   ";
		$sSql .= "                 Select   ";
		$sSql .= "                     *   ";
		$sSql .= "                 From   ";
		$sSql .= "                     class_impressions_tbl   ";
		$sSql .= "                 Where memberID = '$memberID'   ";
		$sSql .= "             ) b On a.year = b.year And a.quarter = b.quarter And a.class_code = b.class_code   ";
		$sSql .= "     ) x   ";
		$sSql .= " Order By   ";
		$sSql .= "     x.orderno,   ";
		$sSql .= "     x.class_code * 1   ";
		
		$resRtn = mysql_query($sSql);
		
		if (!$resRtn) {
			echo "MyClass_GetList Select Error";
			return;
		}
		
		$joFullDate = array();
		$sStartDate = "";
		$sEndDate = "";
		$nTotalCnt = 0;
		$nWriteCnt = 0;
		
		while ($aRow = mysql_fetch_array($resRtn, MYSQL_ASSOC)) {
			$sStartDate = $aRow[start_date];
			$sEndDate = $aRow[end_date];
			array_push($joFullDate, $aRow);
			
			$nTotalCnt++;
			if ($aRow[WRITE_TF] == "T") {
				$nWriteCnt++;
			}
		}
		
		$aFooter = array(
				"TOTAL_TEXT" => "총 $nTotalCnt 건",
				"WRITE_TOTAL_TEXT" => "$nWriteCnt 건",
				"TOTAL" => $nTotalCnt,
				"WRITE_TOTAL" => $nWriteCnt,
				"START_DATE" => $sStartDate,
				"END_DATE" => $sEndDate
		);
		array_push($joFullDate, $aFooter);
		
		echo json_encode($joFullDate);
	}
	
	/******************************************************************************
	 기    능 : My Class 소감 가져오기
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : intranet/common_contents/work_lecture/MyClass.tpl : 교육실 > My Class 팝업
	 기    타 :
	 ******************************************************************************/
	function MyClass_GetImpressions() {
		global $db;
		global $class_code, $memberID, $registration, $command;
		
		if ($command == "add") {
			$sSql  = " Select ";
			$sSql .= "     x.*, ";
			$sSql .= "     y.Name class_name ";
			$sSql .= " From ";
			$sSql .= "     ( ";
			$sSql .= "         Select ";
			$sSql .= "             * ";
			$sSql .= "         From ";
			$sSql .= "             class_list_tbl ";
			$sSql .= "         Where class_code = '$class_code'  ";
			$sSql .= "     ) x Join  ";
			$sSql .= "     ( ";
			$sSql .= "         Select ";
			$sSql .= "             * ";
			$sSql .= "         From ";
			$sSql .= "             systemconfig_tbl ";
			$sSql .= "         Where SysKey = 'class_type' ";
			$sSql .= "     ) y On x.class_type = y.Code ";
			$sSql .= " Order By ";
			$sSql .= "     x.class_code ";
			
			$resRtn = mysql_query($sSql);
			
			if (!$resRtn) {
				echo "MyClass_GetImpressions(new) Select Error";
				return;
			}
			
			$joFullDate = array();
			
			while ($aRow = mysql_fetch_array($resRtn, MYSQL_ASSOC)) {
				array_push($joFullDate, $aRow);
			}
			
			echo json_encode($joFullDate);
			
		} else if ($command == "update") {
			$sSql  = " Select ";
			$sSql .= "     x.*, ";
			$sSql .= "     y.Name class_name, ";
			$sSql .= "     ( ";
			$sSql .= "         Select ";
			$sSql .= "             class_title ";
			$sSql .= "         From ";
			$sSql .= "             class_list_tbl n ";
			$sSql .= "         Where n.year = x.year ";
			$sSql .= "         And n.quarter = x.quarter ";
			$sSql .= "         And n.class_code = x.class_code ";
			$sSql .= "     ) class_title ";
			$sSql .= " From ";
			$sSql .= "     ( ";
			$sSql .= "         Select ";
			$sSql .= "              * ";
			$sSql .= "         From ";
			$sSql .= "             class_impressions_tbl  ";
			$sSql .= "         Where class_code = '$class_code' ";
			$sSql .= "         And memberID = '$memberID' ";
			$sSql .= "     ) x Join  ";
			$sSql .= "     ( ";
			$sSql .= "         Select ";
			$sSql .= "             * ";
			$sSql .= "         From ";
			$sSql .= "             systemconfig_tbl ";
			$sSql .= "         Where SysKey = 'class_type' ";
			$sSql .= "     ) y On x.class_type = y.Code ";
			$sSql .= " Order By ";
			$sSql .= "     x.class_code ";
			
			$resRtn = mysql_query($sSql);
			
			if (!$resRtn) {
				echo "MyClass_GetImpressions(modify) Select Error";
				return;
			}
			
			$joFullDate = array();
			
			while ($aRow = mysql_fetch_array($resRtn, MYSQL_ASSOC)) {
				array_push($joFullDate, $aRow);
			}
			
			echo json_encode($joFullDate);
		}
	}
	
	/******************************************************************************
	 기    능 : My Class 소감 저장
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : intranet/common_contents/work_lecture/MyClass.tpl : 교육실 > My Class 팝업
	 기    타 :
	 ******************************************************************************/
	function MyClass_SaveImpressions() {
		global $db, $command;
		global $year, $quarter, $class_code, $registration;
		global $class_type, $companyName, $Company_Kind, $memberID, $dept_code, $position_code, $dept, $position, $contents, $insert_member;
		
		$sRtnMessage = "";
		
		/* [memberID 검증 Start] */
		if ($memberID == "") {
			$sRtnMessage = "Session(memberID) Error";
			echo json_encode($sRtnMessage);
			return;
		}
		/* [memberID 검증 End] */
		
		/* [작성기한 검증 Start] */
		$sSql  = " Select ";
		$sSql .= "     Case ";
		$sSql .= "         When CurDate() >= start_date And CurDate() <= end_date Then 'T' ";
		$sSql .= "         Else ";
		$sSql .= "             'F' ";
		$sSql .= "     End EDIT_TF, ";
		$sSql .= "     start_date START_DATE, ";
		$sSql .= "     end_date END_DATE ";
		$sSql .= " From ";
		$sSql .= "     class_list_tbl ";
		$sSql .= " Where year = '$year' ";
		$sSql .= " And quarter = '$quarter' ";
		$sSql .= " And class_code = '$class_code' ";
		
		$resRtn = mysql_query($sSql);
		
		if (!$resRtn) {
			$sRtnMessage = "Date_Validation Select Error";
			echo json_encode($sRtnMessage);
			return;
		}
		
		$aCheckRow = mysql_fetch_array($resRtn, MYSQL_ASSOC);
		
		if ($aCheckRow["EDIT_TF"] == "F") {
			$sRtnMessage = "Expried Error";
			echo json_encode($sRtnMessage);
			return;
		}
		/* [작성기한 검증 End] */
		
		if ($command == "add") {
			/* [중복검사 Start] */
			$sSql  = " Select ";
			$sSql .= "     Count(*) CNT ";
			$sSql .= " From ";
			$sSql .= "     class_impressions_tbl ";
			$sSql .= " Where year = '$year' ";
			$sSql .= " And quarter = '$quarter' ";
			$sSql .= " And class_code = '$class_code' ";
			$sSql .= " And memberID = '$memberID' ";
			
			$resRtn = mysql_query($sSql);
			
			if (!$resRtn) {
				$sRtnMessage = "MyClass_SaveImpressions Dup_Validation Select Error";
				echo json_encode($sRtnMessage);
				return;
			}
			
			if (mysql_result($resRtn, 0, "CNT") > 0) {
				$sRtnMessage = "Duplication";
				echo json_encode($sRtnMessage);
				return;
			}
			/* [중복검사 End] */
			
			$sSql  = " Insert Into class_impressions_tbl  ";
			$sSql .= " (  ";
			$sSql .= "     year, ";
			$sSql .= "     quarter, ";
			$sSql .= "     class_code, ";
			$sSql .= "     registration, ";
			$sSql .= "     class_type, ";
			$sSql .= "     company, ";
			$sSql .= "     CompanyKind, ";
			$sSql .= "     memberID, ";
			$sSql .= "     dept_code, ";
			$sSql .= "     position_code, ";
			$sSql .= "     dept, ";
			$sSql .= "     position, ";
			$sSql .= "     contents, ";
			$sSql .= "     insert_member, ";
			$sSql .= "     insert_date ";
			$sSql .= " )  ";
			$sSql .= " Values  ";
			$sSql .= " (  ";
			$sSql .= "     '$year',  ";
			$sSql .= "     '$quarter',  ";
			$sSql .= "     '$class_code',  ";
			$sSql .= "     '$registration',  ";
			$sSql .= "     '$class_type',  ";
			$sSql .= "     '$companyName',  ";
			$sSql .= "     '$Company_Kind',  ";
			$sSql .= "     '$memberID', ";
			$sSql .= "     '$dept_code',  ";
			$sSql .= "     '$position_code',  ";
			$sSql .= "     '$dept', ";
			$sSql .= "     '$position', ";
			$sSql .= "     '$contents', ";
			$sSql .= "     '$insert_member', ";
			$sSql .= "     CurDate() ";
			$sSql .= " )  ";
			
			$resRtn = mysql_query($sSql);
			
			if (!$resRtn) {
				$sRtnMessage = "MyClass_SaveImpressions Insert Error";
				echo json_encode($sRtnMessage);
				return;
			}
			
			$sRtnMessage = "success";
			echo json_encode($sRtnMessage);
		} else if ($command == "update") {
			$sSql  = " Update class_impressions_tbl ";
			$sSql .= " Set ";
			$sSql .= "     contents = '$contents', ";
			$sSql .= "     edit_date = CurDate() ";
			$sSql .= " Where year = '$year' ";
			$sSql .= " And quarter = '$quarter' ";
			$sSql .= " And class_code = '$class_code' ";
			$sSql .= " And memberID = '$memberID' ";
			
			$resRtn = mysql_query($sSql);
			
			if (!$resRtn) {
				$sRtnMessage = "MyClass_SaveImpressions Update Error" ;
			}
			
			$sRtnMessage = "success";
			echo json_encode($sRtnMessage);
		}
	}
	
	/******************************************************************************
	 기    능 : My Class 컨텐츠 내용 가져오기
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : intranet/common_contents/work_lecture/MyClass.tpl : 교육실 > My Class > 소감문작성 팝업
	 기    타 :
	 ******************************************************************************/
	function MyClass_GetContents() {
		global $db;
		global $year, $quarter, $class_code;
		
		$sSql  = " Select ";
		$sSql .= "     * ";
		$sSql .= " From ";
		$sSql .= "     class_list_tbl ";
		$sSql .= " Where year = '$year' ";
		$sSql .= " And quarter = '$quarter' ";
		$sSql .= " And class_code = '$class_code' ";
		
		$resRtn = mysql_query($sSql);
		
		if (!$resRtn) {
			echo "MyClass_GetContents Select Error";
			return;
		}
		
		$aRow = mysql_fetch_array($resRtn, MYSQL_ASSOC);
		
		echo json_encode($aRow);
	}
	
	/******************************************************************************
	 기    능 : My Class 이미지 가져오기(상단 슬라이드)
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : intranet/common_contents/work_lecture/MyClass.tpl : 교육실 > My Class > 소감문작성 팝업
	 기    타 :
	 ******************************************************************************/
	function MyClass_GetImages() {
		global $db;
		global $year, $quarter, $class_type;
		
		$sSql  = " Select ";
		$sSql .= "     x.*, ";
		$sSql .= "     Concat(PREFIX_SRC, class_code, '/', img_file) FILE_SRC ";
		$sSql .= " From ";
		$sSql .= "     ( ";
		$sSql .= "         Select ";
		$sSql .= "             class_code, ";
		$sSql .= "             class_title, ";
		$sSql .= "             class_content, ";
		$sSql .= "             '../../../intranet_file/myclass_img/' PREFIX_SRC, ";
		$sSql .= "             img_file ";
		$sSql .= "         From ";
		$sSql .= "             class_list_tbl  ";
		$sSql .= "         Where year = '$year' ";
		$sSql .= "         And quarter = '$quarter' ";
		$sSql .= "         And class_type = '$class_type' ";
		$sSql .= "         And (img_file Is Not Null And img_file != '') ";
		$sSql .= "     ) x   ";
		
		$resRtn = mysql_query($sSql);
		
		if (!$resRtn) {
			echo "MyClass_GetImages Select Error";
			return;
		}
		
		$joFullData = array();
		while ($aRow = mysql_fetch_array($resRtn, MYSQL_ASSOC)) {
			array_push($joFullData, $aRow);
		}
		shuffle($joFullData);
		
		echo json_encode($joFullData);
	}
	
	/******************************************************************************
	 기    능 : MyClass 로그
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : intranet/common_contents/work_lecture/MyClass.tpl : 교육실 > My Class
	 기    타 :
	 ******************************************************************************/
	function writeLog() {
		$sCurYear = date('Y');
		$sCurMonth = date('m');
		$sCurDay = date('Y-m-d');
		
		$sFolderName = "../log/myclass/" . "$sCurYear/" . "$sCurMonth/" ;
		if (!is_dir($sFolderName)) {
			mkdir($sFolderName, null, true);
		}
		
		$sFileName = $sFolderName . $sCurDay . "-log.txt";
		$resFile = fopen($sFileName, 'a');
		
		$sLogTime = date('Y-m-d H:i:s');
		$sLogText = "";
		
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$sLogText .= $sLogTime. " - " . "METHOD = " . $_SERVER["REQUEST_METHOD"] . " | URL = " . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . " | Param = " . urldecode($this->arrayToString($_POST)) . "\n";
		} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
			$sLogText .= $sLogTime. " - " . "METHOD = " . $_SERVER["REQUEST_METHOD"] . "  | URL = " . $this->getUrl($_SERVER["HTTP_HOST"], urldecode($_SERVER['REQUEST_URI'])) . " | Param = [" . urldecode($_SERVER['argv'][0]) . "]\n";
		}
		
		fwrite($resFile, $sLogText);
		fclose($resFile);
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
	
	/******************************************************************************
	 기    능 : My Class 한번 생각해봅시다 리스트 가져오기
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : intranet/common_contents/work_lecture/MyClass.tpl : 교육실 > My Class > 소감문작성 팝업
	 기    타 :
	 ******************************************************************************/
	function MyClass_GetFocusList() {
		global $db;
		global $year, $quarter, $class_type, $memberID;
		
		$sql  = " Select  ";
		$sql .= "     Member_ID,  ";
		$sql .= "     Company  ";
		$sql .= " From  ";
		$sql .= "     myclass_reply_tbl  ";
		$sql .= " Where Member_ID = '$memberID' ";
		
		$resource = mysql_query($sql);
		$rowCount = mysql_num_rows($resource);
		
		if (!$resource) {
			echo "mysql_query(resource) error";
			return;
		}
		
		$companys = array("JANG", "PTC", "PLAN", "CENTER", "HANM", "SAMA");
		if ($rowCount <= 0) {
			$joFullData[rstCode] = 'no target';
			
			echo json_encode($joFullData);
			return;
		} else {
			while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
				if (!in_array($row[Company], $companys)) {
					$joFullData[rstCode] = 'no target';
					
					echo json_encode($joFullData);
					return;
				}
			}
		}
		
		$sql  = " Select     ";
		$sql .= "     x.*     ";
		$sql .= " From     ";
		$sql .= "     (     ";
		$sql .= "         Select     ";
		$sql .= "             a.*,     ";
		$sql .= "             Case     ";
		$sql .= "                 When b.memberID Is Null Then 'F'     ";
		$sql .= "                 Else     ";
		$sql .= "                     'T'     ";
		$sql .= "             End WRITE_TF,    ";
		$sql .= "             Case    ";
		$sql .= "                 When CurDate() >= a.start_date And CurDate() <= a.end_date Then 'T'    ";
		$sql .= "                 Else    ";
		$sql .= "                 'F'    ";
		$sql .= "             End EDIT_TF,      ";
		$sql .= "             b.memberID,     ";
		$sql .= "             b.contents     ";
		$sql .= "         From     ";
		$sql .= "             (     ";
		$sql .= "                 Select     ";
		$sql .= "                     *     ";
		$sql .= "                 From     ";
		$sql .= "                     class_list_tbl     ";
		$sql .= "                 Where Year = $year     ";
		$sql .= "                 And class_type = '$class_type'     ";
		$sql .= "                 And class_title Like '%한번 생각해봅시다%'     ";
		$sql .= "             ) a Left Join      ";
		$sql .= "             (     ";
		$sql .= "                 Select     ";
		$sql .= "                     *     ";
		$sql .= "                 From     ";
		$sql .= "                     class_impressions_tbl     ";
		$sql .= "                 Where memberID = '$memberID'     ";
		$sql .= "             ) b On a.year = b. year And a.quarter = b.quarter And a.class_code = b.class_code     ";
		$sql .= "     ) x    ";
		$sql .= " Where x.WRITE_TF = 'T' ";
		$sql .= " Order By ";
		$sql .= "     x.Year, ";
		$sql .= "     x.Quarter ";
		
		$resource = mysql_query($sql);
		
		if (!$resource) {
			echo "mysql_query(resource) error";
			return;
		}
		
		$joFullData = array();
		$fullData = array();
		$nTotalCnt = 0;
		$nWriteCnt = 0;
		while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
			array_push($fullData, $row);
			
			$nTotalCnt++;
			if ($row[WRITE_TF] == "T") {
				$nWriteCnt++;
			}
		}
		$aFooter = array(
			"TOTAL_TEXT" => "총 $nTotalCnt 건",
			"WRITE_TOTAL_TEXT" => "$nWriteCnt 건",
			"TOTAL" => $nTotalCnt,
			"WRITE_TOTAL" => $nWriteCnt
		);
		array_push($fullData, $aFooter);
		
		$joFullData[rstCode] = "target";
		$joFullData[data] = $fullData;
		
		echo json_encode($joFullData);
	}
}
?>