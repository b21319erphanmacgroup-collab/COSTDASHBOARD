<?php
include "../inc/dbcon.inc"; // 한맥
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
	
	function assignRequest() {
		foreach ($_REQUEST As $key => $value) {
			$this->smarty->assign($key, $value);
		}
	}
	
	/******************************************************************************
	 기    능 : My Class
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : intranet/common_contents/work_myclass/MyClass.tpl : 학습실 > My Class
	 기    타 :
	 변경이력 :
			1. 2021-12-28 / 김윤하 / 김병철 / 과정구분 추가에따른 클래스타입 분기처리
 												- 차후 기존과정구분 : 사원~차장(연구원,선임) / 신규 : 과장~차장(선임), 사원~대리(연구원) 정리필요
	 ******************************************************************************/
	function MyClass() {
		extract($_REQUEST);
		global $db;
		
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
		
		$year = date("Y", time());
		$quarter = ceil(date("m", time()) / 3);
		$companyName = $aCompanyName["name"];
		$class_type = $this->getClassType($Company_Kind, $memberID, $year, $quarter);
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
	 사용메뉴 : intranet/common_contents/work_myclass/MyClass.tpl : 학습실 > My Class
	 기    타 :
	 변경이력 :
			1. 2021-12-28 / 김윤하 / 김병철 / 과정구분 추가에따른 클래스타입 분기처리
 												- 차후 기존과정구분 : 사원~차장(연구원,선임) / 신규 : 과장~차장(선임), 사원~대리(연구원) 정리필요
	 ******************************************************************************/
	function MyClass_GetList() {
		extract($_REQUEST);
		global $db;
		$class_type = $this->getClassType($Company_Kind, $memberID, $year, $quarter);
		
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
		$sSql .= "             a.year,   ";
		$sSql .= "             a.quarter,   ";
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
	 사용메뉴 : intranet/common_contents/work_myclass/MyClass.tpl : 학습실 > My Class 팝업
	 기    타 :
	 ******************************************************************************/
	function MyClass_GetImpressions() {
		extract($_REQUEST);
		global $db;
		
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
	 사용메뉴 : intranet/common_contents/work_myclass/MyClass.tpl : 학습실 > My Class 팝업
	 기    타 :
	 변경이력 :
			1. 2021-12-28 / 김윤하 / 김병철 / 과정구분 추가에따른 클래스타입 분기처리
 												- 차후 기존과정구분 : 사원~차장(연구원,선임) / 신규 : 과장~차장(선임), 사원~대리(연구원) 정리필요
	 ******************************************************************************/
	function MyClass_SaveImpressions() {
		extract($_REQUEST);
		global $db;
		$class_type = $this->getClassType($Company_Kind, $memberID, $year, $quarter);
		
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
	 사용메뉴 : intranet/common_contents/work_myclass/MyClass.tpl : 학습실 > My Class > 소감문작성 팝업
	 기    타 :
	 ******************************************************************************/
	function MyClass_GetContents() {
		extract($_REQUEST);
		global $db;
		
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
	 사용메뉴 : intranet/common_contents/work_myclass/MyClass.tpl : 학습실 > My Class > 소감문작성 팝업
	 기    타 :
	 ******************************************************************************/
	function MyClass_GetImages() {
		extract($_REQUEST);
		global $db;
		$class_type = $this->getClassType($Company_Kind, $memberID, $year, $quarter);
		
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
	
	function MyClass_Mobile() {
		extract($_REQUEST);
		global $db;
		
		switch($SubAction) {
			case "MyClass_View" : 
				// 감상문 보기
				$this->assignRequest();
				$userInfo = $this->getUserInfo($Company_Kind, $memberID);
				
				$sql  = " Select ";
				$sql .= "     a.*, ";
				$sql .= "     Case ";
				$sql .= "         When b.memberID Is Not Null Then 'T' ";
				$sql .= "         Else  ";
				$sql .= "             'F' ";
				$sql .= "     End write_tf ";
				$sql .= " From ";
				$sql .= "     ( ";
				$sql .= "         Select  ";
				$sql .= "             *  ";
				$sql .= "         From  ";
				$sql .= "             class_list_tbl  ";
				$sql .= "         Where year = '$year'  ";
				$sql .= "         And quarter = '$quarter'  ";
				$sql .= "         And class_code = '$class_code' ";
				$sql .= "     ) a Left Join ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             * ";
				$sql .= "         From ";
				$sql .= "             class_impressions_tbl ";
				$sql .= "         Where year = '$year' ";
				$sql .= "         And quarter = '$quarter' ";
				$sql .= "         And class_code = '$class_code' ";
				$sql .= "         And memberID = '$memberID' ";
				$sql .= "     ) b On a.year = b.year And a.quarter = b.quarter And a.class_code = b.class_code ";
				
				$resource = mysql_query($sql, $db);
				if (!$resource) {
					echo "mysql_query(resource) error";
					return;
				}
				
				$myclass_view = mysql_fetch_array($resource, MYSQL_ASSOC);
				
				$this->smarty->assign("userInfo", $userInfo);
				$this->smarty->assign("myclass_view", $myclass_view);
				
				$this->smarty->display("intranet/common_contents/work_myclass/MyClass_View_mobile.tpl");
				break;
			case "MyClass_Impressions" : 
				// 감상문 작성
				$this->assignRequest();
				$userInfo = $this->getUserInfo($Company_Kind, $memberID);
				
				$sql  = " Select ";
				$sql .= "     a.*, ";
				$sql .= "     b.contents ";
				$sql .= " From ";
				$sql .= "     ( ";
				$sql .= "         Select  ";
				$sql .= "             *  ";
				$sql .= "         From  ";
				$sql .= "             class_list_tbl  ";
				$sql .= "         Where year = '$year'  ";
				$sql .= "         And quarter = '$quarter'  ";
				$sql .= "         And class_code = '$class_code' ";
				$sql .= "     ) a Left Join ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             * ";
				$sql .= "         From ";
				$sql .= "             class_impressions_tbl ";
				$sql .= "         Where year = '$year' ";
				$sql .= "         And quarter = '$quarter' ";
				$sql .= "         And class_code = '$class_code' ";
				$sql .= "         And memberID = '$memberID' ";
				$sql .= "     ) b On a.year = b.year And a.quarter = b.quarter And a.class_code = b.class_code ";
				
				$resource = mysql_query($sql, $db);
				if (!$resource) {
					echo "mysql_query(resource) error";
					return;
				}
				
				$myclass_impressions = mysql_fetch_array($resource, MYSQL_ASSOC);
				
				$this->smarty->assign("userInfo", $userInfo);
				$this->smarty->assign("myclass_impressions", $myclass_impressions);
				
				$this->smarty->display("intranet/common_contents/work_myclass/MyClass_Impressions_mobile.tpl");
				break;
			case "MyClass_SaveImpressions" : // 감상문 저장
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
				
				$resRtn = mysql_query($sSql, $db);
				
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
				
				/* [감상문 Merge Into Start] */
				$sql  = " Insert Into  class_impressions_tbl  ";
				$sql .= " ( ";
				$sql .= "     year, ";
				$sql .= "     quarter, ";
				$sql .= "     class_code, ";
				$sql .= "     registration, ";
				$sql .= "     memberID, ";
				$sql .= "     class_type, ";
				$sql .= "     company, ";
				$sql .= "     CompanyKind, ";
				$sql .= "     dept_code, ";
				$sql .= "     position_code, ";
				$sql .= "     dept, ";
				$sql .= "     position, ";
				$sql .= "     contents, ";
				$sql .= "     insert_member, ";
				$sql .= "     insert_date ";
				$sql .= " ) ";
				$sql .= " Values  ";
				$sql .= " ( ";
				$sql .= "     '$year', ";
				$sql .= "     '$quarter', ";
				$sql .= "     '$class_code', ";
				$sql .= "     '$registration', ";
				$sql .= "     '$memberID', ";
				$sql .= "     '$class_type', ";
				$sql .= "     '$company_name', ";
				$sql .= "     '$Company_Kind', ";
				$sql .= "     '$dept_code', ";
				$sql .= "     '$position_code', ";
				$sql .= "     '$dept', ";
				$sql .= "     '$position', ";
				$sql .= "     '$contents', ";
				$sql .= "     '$insert_member', ";
				$sql .= "     CurDate()     ";
				$sql .= " ) ";
				$sql .= " On Duplicate Key ";
				$sql .= " Update  ";
				$sql .= "     contents = '$contents',  ";
				$sql .= "     edit_date = CurDate(); ";
				
				$resource = mysql_query($sql, $db);
				if (!$resRtn) {
					$sRtnMessage = "MyClass_SaveImpressions Insert Error";
					echo json_encode($sRtnMessage);
					return;
				}
				/* [감상문 Merge Into End] */
				
				$sRtnMessage = "success";
				echo json_encode($sRtnMessage);
				break;
			default : 
				// 메인(리스트)
				$year = date("Y");
				$quarter = ceil(date("m", time()) / 3);
				$userInfo = $this->getUserInfo($Company_Kind, $memberID);
				if (empty($userInfo[class_type])) {
					$userInfo[class_type] = '99999'; // 예외처리
				}
				
				/* [마감 날짜 Select] */
				$sql  = " Select  ";
				$sql .= "     Max(end_date) MAX_END_DATE ";
				$sql .= " From  ";
				$sql .= "     class_list_tbl  ";
				$sql .= " Where year = '$year'  ";
				$sql .= " And quarter = '$quarter'  ";
				$sql .= " And class_type = '$userInfo[class_type]' ";
				$sql .= " Group By ";
				$sql .= "     year, ";
				$sql .= "     quarter, ";
				$sql .= "     class_type ";
				$resource = mysql_query($sql, $db);
				
				if (!$resource) {
					echo "mysql_query(resource) error";
					return;
				}
				
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					$MAX_END_DATE = $row["MAX_END_DATE"];
				}
				
				if ($MAX_END_DATE != "") {
					$maxTimestamp = strtotime($MAX_END_DATE);
					$curTimestamp = strtotime(date("Y-m-d"));
					$D_day = ($maxTimestamp - $curTimestamp) / 86400;
				}
				/* [마감 날짜 End] */
				
				/* [MyClass 목록 리스트 가져오기 Start] */
				$sql  = " Select   ";
				$sql .= "     x.*,   ";
				$sql .= "     Case   ";
				$sql .= "         When x.memberID Is Not Null Then 'T'   ";
				$sql .= "         Else   ";
				$sql .= "             'F'   ";
				$sql .= "     End WRITE_TF,   ";
				$sql .= "     Case ";
				$sql .= "         When CurDate() >= x.start_date And CurDate() <= x.end_date Then 'T' ";
				$sql .= "         Else ";
				$sql .= "             'F' ";
				$sql .= "     End EDIT_TF    ";
				$sql .= " From   ";
				$sql .= "     (   ";
				$sql .= "         Select   ";
				$sql .= "             a.class_title,   ";
				$sql .= "             a.class_content,   ";
				$sql .= "             a.year,   ";
				$sql .= "             a.quarter,   ";
				$sql .= "             a.class_code,   ";
				$sql .= "             a.start_date,   ";
				$sql .= "             a.end_date,   ";
				$sql .= "             a.orderno,   ";
				$sql .= "             Case ";
				$sql .= "                 When a.thumbnail_img Is Null || a.thumbnail_img = ''  Then '../../image/phonebook/thumb_noimage.png' ";
				$sql .= "                 Else  ";
				$sql .= "                     a.thumbnail_img ";
				$sql .= "             End thumbnail_img, ";
				$sql .= "             b.memberID,  ";
				$sql .= "             b.registration,   ";
				$sql .= "             b.class_type,   ";
				$sql .= "             b.company,   ";
				$sql .= "             b.dept,   ";
				$sql .= "             b.position,   ";
				$sql .= "             b.insert_member   ";
				$sql .= "         From   ";
				$sql .= "             (  ";
				$sql .= "                 Select  ";
				$sql .= "                     *  ";
				$sql .= "                 From  ";
				$sql .= "                     class_list_tbl  ";
				$sql .= "                 Where year = '$year'  ";
				$sql .= "                 And quarter = '$quarter'  ";
				$sql .= "                 And class_type = '$userInfo[class_type]'  ";
				$sql .= "             ) a Left Join   ";
				$sql .= "             (   ";
				$sql .= "                 Select   ";
				$sql .= "                     *   ";
				$sql .= "                 From   ";
				$sql .= "                     class_impressions_tbl   ";
				$sql .= "                 Where memberID = '$memberID'   ";
				$sql .= "             ) b On a.year = b.year And a.quarter = b.quarter And a.class_code = b.class_code   ";
				$sql .= "     ) x   ";
				$sql .= " Order By   ";
				$sql .= "     x.orderno,   ";
				$sql .= "     x.class_code * 1   ";
				
				$resRtn = mysql_query($sql, $db);
				if (!$resRtn) {
					echo "MyClass_GetList Select Error";
					return;
				}
				
				$myclassList = array(
					"data" => array(),
					"sum" => array()
				);
				$totalCnt = 0;
				$wirteCnt = 0;
				
				while ($row = mysql_fetch_array($resRtn, MYSQL_ASSOC)) {
					$offset = 0;
					$videoCnt = 0;
					while (true) {
						$offset = strpos($row["class_content"], "<iframe", $offset);
						if ($offset === false) {
							break;
						}
						
						$offset += 7;
						$videoCnt++;
					}
					$row["videoCnt"] = $videoCnt;
					array_push($myclassList["data"], $row);
					
					$totalCnt++;
					if ($row["WRITE_TF"] == "T") {
						$wirteCnt++;
					}
				}
				
				$sum = array(
					"TOTAL" => $totalCnt,
					"WRITE_TOTAL" => $wirteCnt
				);
				array_push($myclassList["sum"], $sum);
				/* [MyClass 목록 리스트 가져오기 End] */
				// 인사정보(주민등록번호) 가입이 안된 사용자 여부 
				$viewTF = preg_match("/\d{6}-?\d{7}/i", $userInfo["registration"]);
				
				$this->smarty->assign("viewTF", $viewTF);
				$this->smarty->assign("D_day", $D_day);
				$this->smarty->assign("userInfo", $userInfo);
				$this->smarty->assign("myclassList", $myclassList);
				
				$this->smarty->display("intranet/common_contents/work_myclass/MyClass_mobile.tpl");
				break;
		}
	}
	
	/******************************************************************************
	 기    능 : MyClass 로그
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : intranet/common_contents/work_myclass/MyClass.tpl : 학습실 > My Class
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
	 사용메뉴 : intranet/common_contents/work_myclass/MyClass.tpl : 학습실 > My Class 
	 기    타 :
	 변경이력 :
			1. 2021-12-28 / 김윤하 / 김병철 / 과정구분 추가에따른 클래스타입 분기처리
 												- 차후 기존과정구분 : 사원~차장(연구원,선임) / 신규 : 과장~차장(선임), 사원~대리(연구원) 정리필요
	 ******************************************************************************/
	function MyClass_GetFocusList() {
		extract($_REQUEST);
		global $db;
		$class_type = $this->getClassType($Company_Kind, $memberID, $year, $quarter);
		
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
	
	/******************************************************************************
	기    능 : 사용자 정보 가져오기
	관 련 DB :
	프로시져 :
	사용메뉴 : MyClass 모바일
	기    타 :
	******************************************************************************/
	function getUserInfo($Company_Kind, $memberID) {
		global $db;
		$userInfo = array();
		
		if (empty($Company_Kind) || empty($memberID)) {
			return $userInfo;
		}
		
		$year = date("Y", time());
		$quarter = ceil(date("m", time()) / 3);
		
		$sql  = " Select   ";
		$sql .= "     d.*,   ";
		$sql .= "     Case   ";
		$sql .= "         When d.Company_Kind = 'SAMA' Then '삼안'   ";
		$sql .= "         When d.Company_Kind = 'HANM' Then '바론컨설턴트'   ";
		$sql .= "         When d.Company_Kind = 'JANG' Then '장헌산업'   ";
		$sql .= "         When d.Company_Kind = 'PTC' Then '피티씨'   ";
		$sql .= "         When d.Company_Kind = 'HALL' Then '한라산업개발'   ";
		$sql .= "     End company_name  ";
		$sql .= " From   ";
		$sql .= "     (   ";
		$sql .= "         Select     ";
		$sql .= "             '$memberID' memberID,    ";
		$sql .= "             '$Company_Kind' Company_Kind,    ";
		$sql .= "             a.dept_code,    ";
		$sql .= "             a.rank_code position_code,   ";
		$sql .= "             b.Name dept,   ";
		$sql .= "             c.rank_name position,   ";
		$sql .= "             c.class_type,  ";
		$sql .= "             a.kor_name insert_member,     ";
		$sql .= "             Replace(a.jumin_no, '-', '') registration    ";
		$sql .= "         From     ";
		$sql .= "             comp_member_tbl a Left Join   ";
		$sql .= "             (   ";
		$sql .= "                 Select     ";
		$sql .= "                     *     ";
		$sql .= "                 From     ";
		$sql .= "                     comp_systemconfig_tbl     ";
		$sql .= "                 Where SysKey = 'DEPT'     ";
		$sql .= "                 And comp_code = '$Company_Kind'  ";
		$sql .= "             ) b on a.dept_code = b.Code Left Join   ";
		$sql .= "             (   ";
		$sql .= "                 Select     ";
		$sql .= "                     *     ";
		$sql .= "                 From     ";
		$sql .= "                     class_quarters_tbl     ";
		$sql .= "                 Where company = '$Company_Kind'  ";
		$sql .= "                 And memberID = '$memberID' ";
		$sql .= "                 And Year = '$year' ";
		$sql .= "                 And quarter = '$quarter' ";
		$sql .= "             ) c on a.rank_code = c.rank_code   ";
		$sql .= "         Where a.member_id = '$memberID'     ";
		$sql .= "     ) d ";
		
		$resource = mysql_query($sql);
		if (!$resource) {
			echo "mysql_query(user_info) error";
			return;
		}
		
		$userInfo = mysql_fetch_array($resource, MYSQL_ASSOC);
		
		return $userInfo;
	}
	
	/******************************************************************************
	기    능 : 사용자 클래스타입 가져오기
	관 련 DB :
	프로시져 :
	사용메뉴 : intranet/common_contents/work_myclass/MyClass.tpl : 학습실 > My Class
	기    타 :
	변경이력 :
		 1. 2021-12-28 / 김윤하 / 김병철 / 과정구분 추가에따른 클래스타입 분기처리
 												- 차후 기존과정구분 : 사원~차장(연구원,선임) / 신규 : 과장~차장(선임), 사원~대리(연구원) 정리필요
	******************************************************************************/
	function getClassType($Company_Kind, $memberID, $year, $quarter) {
		global $db;
		
		$sql  = " Select ";
		$sql .= "     * ";
		$sql .= " From ";
		$sql .= "     class_quarters_tbl ";
		$sql .= " Where company = '$Company_Kind' ";
		$sql .= " And memberID = '$memberID' ";
		$sql .= " And year = '$year' ";
		$sql .= " And quarter = '$quarter' ";
		
		$resource = mysql_query($sql);
		if (!$resource) {
			echo "mysql_query(resource) error";
			return;
		}
		$aClassType = mysql_fetch_array($resource, MYSQL_ASSOC);
		
		return $aClassType["class_type"];
	}
}
?>