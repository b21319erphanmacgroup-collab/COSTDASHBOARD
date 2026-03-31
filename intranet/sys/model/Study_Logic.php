<?php
include "../inc/dbcon.inc"; // 한맥
include "../../sys/inc/function_intranet.php";
include "../../../SmartyConfig.php";
include "../util/HanamcPageControl.php";

extract($_REQUEST);
class StudyLogic {
	var $smarty;
	var $userInfo;
	
	function StudyLogic($smarty) {
		session_start();
		$Controller = "Study_Controller.php";
		$ActionMode = $_REQUEST['ActionMode'];
		$MainAction = $_REQUEST['MainAction'];
		$userInfo = $this->getUserInfo($_REQUEST[Company_Kind], $_REQUEST[memberID]);
		
		$this->smarty=$smarty;
		$this->userInfo=$userInfo;
		$this->smarty->assign('userInfo', $userInfo);
		$this->smarty->assign('Controller', $Controller);
		$this->smarty->assign('ActionMode', $ActionMode);
		$this->smarty->assign('MainAction', $MainAction);
		
		$this->assignRequest();
	}
	
	function assignRequest() {
		foreach ($_REQUEST As $key => $value) {
			$this->smarty->assign($key, $value);
		}
	}
	
	function MyClass_Mobile() {
		extract($_REQUEST);
		global $db;
		
		switch($MainAction) {
			case "HanmacPick" : 
				$this->HanmacPick();
				break;
			case "Culture" : 
				$this->Culture();
				break;
			case "Duty" : 
				$this->Duty();
				break;
			case "Search" :
				$this->Search();
				break;
			default : 
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_HanmacPick_Main.tpl");
				break;
		}
	}
	
	/******************************************************************************
	 기    능 : 한맥배움터(한맥PICK)
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : 한맥114 > 한맥배움터
	 기    타 :
	 변경이력 :
	 	1. 2022-05-30 / 김윤하 / 김병철 / 최초작성
	 ******************************************************************************/
	function HanmacPick() {
		extract($_REQUEST);
		global $db;
		if (empty($page)) {
			$page = 1;
			$displayNum = 99999;
		}
		
		switch($SubAction) {
			case "Main" :
				$impressionTypes = array (
					"C" => "한맥특강",
					"D" => "마이클래스"
				);
				$commentTypes = array(
					"A" => "선택없음",
					"B" => "한맥알기",
					"F" => "별책부록",
					"G" => "리더맞춤",
					"E" => "이달추천"
				);
				
				if (array_key_exists($type, $impressionTypes)) {
					if ($type == "C") {
						$userInfo = $this->userInfo;
						
						$sql  = " Select     ";
						$sql .= "     x.*,     ";
						$sql .= "     Case     ";
						$sql .= "         When x.memberID Is Not Null Then 'T'     ";
						$sql .= "         Else     ";
						$sql .= "             'F'     ";
						$sql .= "     End WRITE_TF ";
						$sql .= " From     ";
						$sql .= "     (   ";
						$sql .= "         Select ";
						$sql .= "             a.*, ";
						$sql .= "             a.title class_title, ";
						$sql .= "             a.contents class_content, ";
						$sql .= "             Case  ";
						$sql .= "                 When NullIf(a.thumbnail_img, '') Is Not Null  Then a.thumbnail_img  ";
						$sql .= "                 Else   ";
						$sql .= "                     '../../image/phonebook/thumb_noimage.png'  ";
						$sql .= "             End preview_img,  ";
						$sql .= "             b.lecture_seq, ";
						$sql .= "             b.memberID, ";
						$sql .= "             b.registration, ";
						$sql .= "             b.company,     ";
						$sql .= "             b.dept,     ";
						$sql .= "             b.position,     ";
						$sql .= "             b.insert_member    ";
						$sql .= "         From ";
						$sql .= "             ( ";
						$sql .= "                 Select  ";
						$sql .= "                     *  ";
						$sql .= "                 From  ";
						$sql .= "                     class_recommend_new_tbl  ";
						$sql .= "                 Where hanmacPick_cd = 'C' ";
						$sql .= "                 And hide_yn = 'N' ";
						$sql .= "                 And category = 'A' ";
						$sql .= "             ) a Left Join ";
						$sql .= "             ( ";
						$sql .= "                 Select ";
						$sql .= "                     * ";
						$sql .= "                 From ";
						$sql .= "                     class_lecture_impressions_tbl ";
						$sql .= "                 Where memberID = '$userInfo[memberID]' ";
						$sql .= "                 And CompanyKind = '$userInfo[Company_Kind]' ";
						$sql .= "                 And dvs_cd = 'A' ";
						$sql .= "             ) b On a.seq = b.seq ";
						$sql .= "     ) x ";
						$sql .= " Order By ";
						$sql .= "     x.orderno ";
						$resRtn = mysql_query($sql, $db);
						
						if (!$resRtn) {
							echo "mysql_query(Type:C) Impression Select Error";
							return;
						}
					} else if ($type == "D") {
						$currYear = date("Y");
						$currMonth = date("m");
						$currQuarter = ceil($currMonth / 3);
						$currYearQuarter = $currYear . $currQuarter;
						/* [년도,분기 조건 Select Start] */
						$sql  = " Select ";
						$sql .= "     x.* ";
						$sql .= " From ";
						$sql .= "     ( ";
						$sql .= "         Select   ";
						$sql .= "             year,   ";
						$sql .= "             quarter, ";
						$sql .= "             Concat(year, quarter) year_quarter,   ";
						$sql .= "             Concat(year, '^', quarter) yq_condition_code,   ";
						$sql .= "             Concat(year, '년 ', quarter, '분기') yq_condition_name   ";
						$sql .= "         From   ";
						$sql .= "             class_list_new_tbl   ";
						$sql .= "         Group By  ";
						$sql .= "             year,  ";
						$sql .= "             quarter ";
						$sql .= "     ) x ";
						$sql .= " Where 1 = 1  ";
						$sql .= " And x.year_quarter <= $currYearQuarter ";
						$sql .= " Order By  ";
						$sql .= "     year Desc,  ";
						$sql .= "     quarter Desc ";
						$resource = mysql_query($sql);
						
						if (!$resource) {
							echo "mysql_query(Type:D) Year_Quarter Select error";
							return;
						}
						
						$yq_condition = array();
						while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
							array_push($yq_condition, $row);
						}
						$this->smarty->assign("yq_condition", $yq_condition);
						/* [년도,분기 조건 Select End] */
						
						if (empty($yearQuarter)) {
							if (count($yq_condition) > 0) {
								$year = $yq_condition[0]["year"];
								$quarter = $yq_condition[0]["quarter"];
								$yearQuarter = $yq_condition[0]["yq_condition_code"];
							}
						} else {
							$yearQuarterExplode = explode("^", $yearQuarter);
							$year = $yearQuarterExplode[0];
							$quarter = $yearQuarterExplode[1];
						}
						$this->smarty->assign("yearQuarter", $yearQuarter);
						$userInfo = $this->getUserInfo($Company_Kind, $memberID, "MyClass", $year, $quarter);
						
						/* [MyClass 목록 리스트 가져오기 Start] */
						$sql  = " Select    ";
						$sql .= "     x.*,    ";
						$sql .= "     Case    ";
						$sql .= "         When x.memberID Is Not Null Then 'T'    ";
						$sql .= "         Else    ";
						$sql .= "             'F'    ";
						$sql .= "     End WRITE_TF,    ";
						$sql .= "     Case  ";
						$sql .= "         When CurDate() >= x.start_date And CurDate() <= x.end_date Then 'T'  ";
						$sql .= "         Else  ";
						$sql .= "             'F'  ";
						$sql .= "     End EDIT_TF     ";
						$sql .= " From    ";
						$sql .= "     (    ";
						$sql .= "         Select    ";
						$sql .= "             a.class_title,    ";
						$sql .= "             a.class_content,    ";
						$sql .= "             a.seq,    ";
						$sql .= "             a.year,    ";
						$sql .= "             a.quarter,    ";
						$sql .= "             a.class_code,    ";
						$sql .= "             a.start_date,    ";
						$sql .= "             a.end_date,    ";
						$sql .= "             a.orderno,    ";
						$sql .= "             Case  ";
						$sql .= "                 When NullIf(a.m_thumbnail_img, '') Is Not Null  Then a.m_thumbnail_img  ";
						$sql .= "                 When NullIf(a.m_img_path, '') Is Not Null  Then a.m_img_path  ";
						$sql .= "                 Else   ";
						$sql .= "                     '../../image/phonebook/thumb_noimage.png'  ";
						$sql .= "             End preview_img,  ";
						$sql .= "             b.memberID,   ";
						$sql .= "             b.registration,    ";
						$sql .= "             b.class_type,    ";
						$sql .= "             b.company,    ";
						$sql .= "             b.dept,    ";
						$sql .= "             b.position,    ";
						$sql .= "             b.insert_member    ";
						$sql .= "         From    ";
						$sql .= "             (   ";
						$sql .= "                 Select    ";
						$sql .= "                     a.*, ";
						$sql .= "                     b.hanmacPick_cd, ";
						$sql .= "                     b.thumbnail_img m_thumbnail_img, ";
						$sql .= "                     b.img_path m_img_path ";
						$sql .= "                 From    ";
						$sql .= "                     class_list_new_tbl a Join ";
						$sql .= "                     ( ";
						$sql .= "                         Select ";
						$sql .= "                             * ";
						$sql .= "                         From ";
						$sql .= "                             class_recommend_new_tbl ";
						$sql .= "                         Where hanmacPick_cd = '$type' ";
						$sql .= "                         And hide_yn = 'N' ";
						$sql .= "                         And category = 'A' ";
						$sql .= "                     ) b On a.seq = b.seq ";
						$sql .= "                 Where a.year = '$year'    ";
						$sql .= "                 And a.quarter = '$quarter'    ";
						$sql .= "                 And a.class_type = '$userInfo[class_type]'   ";
						$sql .= "             ) a Left Join    ";
						$sql .= "             (    ";
						$sql .= "                 Select    ";
						$sql .= "                     *    ";
						$sql .= "                 From    ";
						$sql .= "                     class_impressions_tbl    ";
						$sql .= "                 Where memberID = '$memberID'    ";
						$sql .= "                 And CompanyKind = '$userInfo[Company_Kind]'    ";
						$sql .= "             ) b On a.year = b.year And a.quarter = b.quarter And a.class_code = b.class_code    ";
						$sql .= "     ) x    ";
						$sql .= " Order By    ";
						$sql .= "     x.orderno,    ";
						$sql .= "     x.class_code * 1    ";
						$resRtn = mysql_query($sql, $db);
						/* [MyClass 목록 리스트 가져오기 End] */
						
						if (!$resRtn) {
							echo "MyClass_GetList(Type:D) Select Error";
							return;
						}
					}
					
					$impressionDataList = array(
						"data" => array(),
						"sum" => array()
					);
					$sortNo = mysql_num_rows($resRtn);
					$totalCnt = 0;
					$writeCnt = 0;
					
					while ($row = mysql_fetch_array($resRtn, MYSQL_ASSOC)) {
						$row["videoCnt"] = $this->getIframeCount($row["class_content"]);
						$row["sortNo"] = $sortNo;
						array_push($impressionDataList["data"], $row);
						
						$totalCnt++;
						if ($row["WRITE_TF"] == "T") {
							$writeCnt++;
						}
						$sortNo--;
					}
					
					$sum = array(
						"TOTAL" => $totalCnt,
						"WRITE_TOTAL" => $writeCnt
					);
					array_push($impressionDataList["sum"], $sum);
					
					// 인사정보(주민등록번호) 가입이 안된 사용자 여부
					$viewableTF = preg_match("/\d{6}-?\d{7}/i", $userInfo["registration"]);
					
					$this->smarty->assign("viewableTF", $viewableTF);
					$this->smarty->assign("userInfo", $userInfo);
					$this->smarty->assign("impressionDataList", $impressionDataList);
					$this->smarty->assign("typeName", $impressionTypes[$type]);
					
					$this->smarty->display("intranet/common_contents/work_myclass/study/Study_HanmacPick_Impression_Main.tpl");
				} else if (array_key_exists($type, $commentTypes)) {
					$userInfo = $this->getUserInfo($Company_Kind, $memberID);
					
					$sql  = " Select ";
					$sql .= "     d.*, ";
					$sql .= "     ( ";
					$sql .= "         Select   ";
					$sql .= "             Count(*) ";
					$sql .= "         From   ";
					$sql .= "             class_recommend_new_tbl   ";
					$sql .= "         Where hanmacPick_cd = '$type' ";
					$sql .= "         And category = 'A' ";
					$sql .= "     ) totalCnt, ";
					$sql .= "     Case  ";
					$sql .= "         When NullIf(d.thumbnail_img, '') Is Not Null  Then d.thumbnail_img  ";
					$sql .= "         When NullIf(d.img_path, '') Is Not Null  Then d.img_path  ";
					$sql .= "         Else   ";
					$sql .= "             '../../image/phonebook/thumb_noimage.png'  ";
					$sql .= "     End preview_img  ";
					$sql .= " From ";
					$sql .= "     ( ";
					$sql .= "         Select ";
					$sql .= "             b.*, ";
					$sql .= "             @ROWNUM := @ROWNUM + 1 SORT_NO ";
					$sql .= "         From ";
					$sql .= "             ( ";
					$sql .= "                 Select  ";
					$sql .= "                     a.*,  ";
					$sql .= "                     (  ";
					$sql .= "                         Select  ";
					$sql .= "                             Count(*)  ";
					$sql .= "                         From  ";
					$sql .= "                             study_comment_tbl x  ";
					$sql .= "                         Where x.seq = a.seq  ";
					$sql .= "                     ) comment_cnt,  ";
					$sql .= "                     (  ";
					$sql .= "                         Select  ";
					$sql .= "                             Count(*)  ";
					$sql .= "                         From  ";
					$sql .= "                             study_retrieve_log_tbl x  ";
					$sql .= "                         Where x.seq = a.seq  ";
					$sql .= "                     ) retrieve_cnt  ";
					$sql .= "                 From  ";
					$sql .= "                     (  ";
					$sql .= "                         Select   ";
					$sql .= "                             *   ";
					$sql .= "                         From   ";
					$sql .= "                             class_recommend_new_tbl   ";
					$sql .= "                         Where hanmacPick_cd = '$type'  ";
					$sql .= "                         And hide_yn = 'N'  ";
					$sql .= "                         And category = 'A'  ";
					$sql .= "                     ) a   ";
					$sql .= "                 Order By  ";
					$sql .= "                     a.orderno Desc ";
					$sql .= "             ) b, ";
					$sql .= "             ( ";
					$sql .= "                 Select  ";
					$sql .= "                     @ROWNUM := 0 ";
					$sql .= "             ) c ";
					$sql .= "     ) d ";
					$sql .= " Where SORT_NO Between ($page - 1) * $displayNum + 1 And $page * $displayNum ";
					$resource = mysql_query($sql, $db);
					
					if (!$resource) {
						echo "mysql_query(commentTypes resource) error";
						return;
					}
					
					$fullData = array();
					$isFristRow = true;
					$pageTotalCnt = $page * $displayNum;
					while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
						if ($isFristRow) {
							$totalCnt =  $row["totalCnt"];
							$sortNoMax = min($totalCnt, $pageTotalCnt);
						}
						
						$row["videoCnt"] = $this->getIframeCount($row["contents"]);
						$row["sortNo"] = $sortNoMax;
						array_push($fullData, $row);
						
						$sortNoMax--;
						$isFristRow = false;
					}
					
					$this->smarty->assign("fullData", $fullData);
					$this->smarty->assign("typeName", $commentTypes[$type]);
					$this->smarty->assign("userInfo", $userInfo);
					
					$this->smarty->display("intranet/common_contents/work_myclass/study/Study_HanmacPick_Comment_Main.tpl");
				}
				
				break;
			case "Detail" :
				if (empty($type) || empty($seq)) {
					echo  $this->getHtmlError(400, "HanmacPick Detail Parameter Error");
					return;
				}
				
				if (!$this->insertRetrieveLog()) {
					echo "insertRetrieveLog error";
					return;
				}
				$writeTF = "F";
				
				$sql  = " Select  ";
				$sql .= "     a.*, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             Count(*) ";
				$sql .= "         From ";
				$sql .= "             study_retrieve_log_tbl x ";
				$sql .= "         Where x.seq = a.seq ";
				$sql .= "     ) retrieve_cnt ";
				$sql .= " From  ";
				$sql .= "     class_recommend_new_tbl a ";
				$sql .= " Where hanmacPick_cd = '$type' ";
				$sql .= " And hide_yn = 'N' ";
				$sql .= " And category = 'A' ";
				$sql .= " And seq = '$seq' ";
				$resource = mysql_query($sql, $db);
				
				if (!$resource) {
					echo "mysql_query(recommend resource) error";
					return;
				}
				
				$fullData = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					if ($row[contents_cd] == "B") {
						$row["file_path"] = "http://$_SERVER[HTTP_HOST]/intranet_file/classRecommend/file/$row[seq]/file/$row[file_name]";
					} else {
						$row["file_path"] = $this->convertSatisPathToIntranetPath($row["file_path"]);
					}
					
					array_push($fullData, $row);
				}
				
				$sql  = " Select  ";
				$sql .= "     x.*  ";
				$sql .= " From  ";
				$sql .= "     (  ";
				$sql .= "         Select   ";
				$sql .= "             a.comment,   ";
				$sql .= "             a.seq,   ";
				$sql .= "             a.company_code,   ";
				$sql .= "             a.company_name,   ";
				$sql .= "             a.userno,   ";
				$sql .= "             a.comment_seq,   ";
				$sql .= "             Case    ";
				$sql .= "                 When NullIf(a.moddate, '') Is Not Null Then date_format(a.moddate, '%Y-%m-%d %H:%i:%s')    ";
				$sql .= "             Else   ";
				$sql .= "                 date_format(a.crtdate, '%Y-%m-%d %H:%i:%s')   ";
				$sql .= "             End write_date,   ";
				$sql .= "             b.kor_name KorName   ";
				$sql .= "         From  ";
				$sql .= "             ( ";
				$sql .= "                 Select ";
				$sql .= "                     *, ";
				$sql .= "                     Case ";
				$sql .= "                         When company_code = 'HALL' Then '50' ";
				$sql .= "                         When company_code ='HANM' Then '20' ";
				$sql .= "                         When company_code ='JANG' Then '40' ";
				$sql .= "                         When company_code ='PTC' Then '60' ";
				$sql .= "                         When company_code ='SAMA' Then '10' ";
				$sql .= "                     End Company_Kind ";
				$sql .= "                 From ";
				$sql .= "                     study_comment_tbl ";
				$sql .= "                 Where seq =  '$seq' ";
				$sql .= "             ) a,   ";
				$sql .= "             (   ";
				$sql .= "                 Select    ";
				$sql .= "                     *   ";
				$sql .= "                 From   ";
				$sql .= "                     total_member_tbl ";
				$sql .= "                 Where sys_comp_code ";
				$sql .= "             ) b   ";
				$sql .= "         Where 1 = 1   ";
				$sql .= "         And a.userno = b.member_id ";
				$sql .= "         And a.Company_Kind = b.sys_comp_code ";
				$sql .= "     ) x  ";
				$sql .= " Order By  ";
				$sql .= "     x.write_date Desc ";
				
				$resource = mysql_query($sql);
				
				if (!$resource) {
					echo "mysql_query(comment resource) error";
					return;
				}
				
				$comment = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					if ($row["userno"] == "$memberID") {
						$writeTF = "T";
					}
					array_push($comment, $row);
				}
				
				$this->smarty->assign("seq", $seq);
				$this->smarty->assign("company_code", $Company_Kind);
				$this->smarty->assign("fullData", $fullData);
				$this->smarty->assign("comment", $comment);
				$this->smarty->assign("writeTF", $writeTF);
				
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_HanmacPick_Comment_Detail.tpl");
				break;
			case "saveComment" :
				if (empty($seq)) {
					echo json_encode($this->getResponseError(500, "필수값 에러 ==> seq"));
					return;
				}
				
				$result = empty($comment_seq) ? $this->manipulateComment("insert") : $this->manipulateComment("update");
				echo json_encode($result);
				break;
			case "deleteComment" :
				$result = $this->manipulateComment("delete");
				echo json_encode($result);
				break;
			case "ImpressionView" :
				if (!$this->insertRetrieveLog()) {
					echo "insertRetrieveLog error";
					return;
				}
				$userInfo = $this->userInfo;
				
				if ($type == "C") {
					$sql  = " Select  ";
					$sql .= "     a.*,  ";
					$sql .= "     a.contents class_content,  ";
					$sql .= "     Case  ";
					$sql .= "         When b.memberID Is Not Null Then 'T'  ";
					$sql .= "         Else   ";
					$sql .= "             'F'  ";
					$sql .= "     End WRITE_TF,  ";
					$sql .= "     'T' EDIT_TF       ";
					$sql .= " From  ";
					$sql .= "     (  ";
					$sql .= "         Select ";
					$sql .= "             * ";
					$sql .= "         From ";
					$sql .= "             class_recommend_new_tbl ";
					$sql .= "         Where seq = '$seq' ";
					$sql .= "         And hide_yn = 'N' ";
					$sql .= "         And category = 'A' ";
					$sql .= "     ) a Left Join  ";
					$sql .= "     (  ";
					$sql .= "         Select  ";
					$sql .= "             *  ";
					$sql .= "         From  ";
					$sql .= "             class_lecture_impressions_tbl  ";
					$sql .= "         Where seq = '$seq'  ";
					$sql .= "         And memberID = '$userInfo[memberID]'  ";
					$sql .= "         And CompanyKind = '$userInfo[Company_Kind]'  ";
					$sql .= "         And dvs_cd = 'A'  ";
					$sql .= "     ) b On a.seq = b.seq ";
					$resource = mysql_query($sql, $db);
					
					if (!$resource) {
						echo "mysql_query(ImpressionView(Type:C) resource) error";
						return;
					}
					
					$fullData = array();
					while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
						if ($row[contents_cd] == "B") {
							$row["file_path"] = "http://$_SERVER[HTTP_HOST]/intranet_file/classRecommend/file/$row[seq]/file/$row[file_name]";
						} else {
							$row["file_path"] = $this->convertSatisPathToIntranetPath($row["file_path"]);
						}
						
						array_push($fullData, $row);
					}
					
					$this->smarty->assign("userInfo", $userInfo);
					$this->smarty->assign("fullData", $fullData);
				} else if ($type == "D") {
					$sql  = " Select ";
					$sql .= "     a.*, ";
					$sql .= "     Case ";
					$sql .= "         When b.memberID Is Not Null Then 'T' ";
					$sql .= "         Else  ";
					$sql .= "             'F' ";
					$sql .= "     End WRITE_TF, ";
					$sql .= "     Case   ";
					$sql .= "         When CurDate() >= a.start_date And CurDate() <= a.end_date Then 'T'   ";
					$sql .= "         Else   ";
					$sql .= "             'F'   ";
					$sql .= "     End EDIT_TF,      ";
					$sql .= "     c.contents_cd,      ";
					$sql .= "     c.file_path,      ";
					$sql .= "     c.file_name      ";
					$sql .= " From ";
					$sql .= "     ( ";
					$sql .= "         Select  ";
					$sql .= "             *  ";
					$sql .= "         From  ";
					$sql .= "             class_list_new_tbl  ";
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
					$sql .= "     ) b On a.year = b.year And a.quarter = b.quarter And a.class_code = b.class_code Join";
					$sql .= "     class_recommend_new_tbl c On a.seq = c.seq";
					$sql .= " Where c.hide_yn = 'N' ";
					$sql .= " And c.category = 'A' ";
					$resource = mysql_query($sql, $db);
					
					if (!$resource) {
						echo "mysql_query(ImpressionView(Type:D) resource) error";
						return;
					}
					
					$fullData = array();
					while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
						if ($row[contents_cd] == "B") {
							$row["file_path"] = "http://$_SERVER[HTTP_HOST]/intranet_file/classRecommend/file/$row[seq]/file/$row[file_name]";
						} else {
							$row["file_path"] = $this->convertSatisPathToIntranetPath($row["file_path"]);
						}
						
						array_push($fullData, $row);
					}
				}
				
				$this->smarty->assign("userInfo", $userInfo);
				$this->smarty->assign("fullData", $fullData);
				
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_HanmacPick_Impression_Detail.tpl");
				break;
			case "ImpressionWrite" :
				if ($type == "C") {
					$userInfo = $this->userInfo;
					
					$sql  = " Select     ";
					$sql .= "     x.*,     ";
					$sql .= "     Case     ";
					$sql .= "         When x.memberID Is Not Null Then 'T'     ";
					$sql .= "         Else     ";
					$sql .= "             'F'     ";
					$sql .= "     End WRITE_TF, ";
					$sql .= "     'T' EDIT_TF ";
					$sql .= " From     ";
					$sql .= "     (   ";
					$sql .= "         Select ";
					$sql .= "             a.title class_title, ";
					$sql .= "             a.seq, ";
					$sql .= "             a.orderno, ";
					$sql .= "             b.contents, ";
					$sql .= "             b.lecture_seq, ";
					$sql .= "             b.memberID, ";
					$sql .= "             b.registration, ";
					$sql .= "             b.company,     ";
					$sql .= "             b.dept,     ";
					$sql .= "             b.position,     ";
					$sql .= "             b.insert_member    ";
					$sql .= "         From ";
					$sql .= "             ( ";
					$sql .= "                 Select  ";
					$sql .= "                     *  ";
					$sql .= "                 From  ";
					$sql .= "                     class_recommend_new_tbl  ";
					$sql .= "                 Where hanmacPick_cd = '$type' ";
					$sql .= "                 And hide_yn = 'N' ";
					$sql .= "                 And category = 'A' ";
					$sql .= "                 And seq = '$seq' ";
					$sql .= "             ) a Left Join ";
					$sql .= "             ( ";
					$sql .= "                 Select ";
					$sql .= "                     * ";
					$sql .= "                 From ";
					$sql .= "                     class_lecture_impressions_tbl ";
					$sql .= "                 Where memberID = '$userInfo[memberID]' ";
					$sql .= "                 And seq = '$seq' ";
					$sql .= "                 And CompanyKind = '$userInfo[Company_Kind]' ";
					$sql .= "                 And dvs_cd = 'A' ";
					$sql .= "             ) b On a.seq = b.seq ";
					$sql .= "     ) x ";
					$sql .= " Order By ";
					$sql .= "     x.orderno ";
					$resource = mysql_query($sql, $db);
					
					if (!$resource) {
						echo "mysql_query(ImpressionView(Type C) resource) error";
						return;
					}
					
					$fullData = array();
					while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
						array_push($fullData, $row);
					}
				} else if ($type == "D") {
					$userInfo = $this->getUserInfo($Company_Kind, $memberID, "MyClass", $year, $quarter);
					
					$sql  = " Select ";
					$sql .= "     a.*, ";
					$sql .= "     b.contents, ";
					$sql .= "     Case ";
					$sql .= "         When b.memberID Is Not Null Then 'T' ";
					$sql .= "         Else  ";
					$sql .= "             'F' ";
					$sql .= "     End WRITE_TF, ";
					$sql .= "     Case   ";
					$sql .= "         When CurDate() >= a.start_date And CurDate() <= a.end_date Then 'T'   ";
					$sql .= "         Else   ";
					$sql .= "             'F'   ";
					$sql .= "     End EDIT_TF      ";
					$sql .= " From ";
					$sql .= "     ( ";
					$sql .= "         Select  ";
					$sql .= "             *  ";
					$sql .= "         From  ";
					$sql .= "             class_list_new_tbl  ";
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
						echo "mysql_query(ImpressionView(Type D) resource) error";
						return;
					}
					
					$fullData = array();
					while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
						array_push($fullData, $row);
					}
				}
				$this->smarty->assign("userInfo", $userInfo);
				$this->smarty->assign("fullData", $fullData);
				
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_HanmacPick_Impression_Write.tpl");
				break;
			case "ImpressionSave" :
				$response = array (
					rstCd => 200,
					data => null,
					error => null
				);
				
				if ($type == "C") {
					$userInfo = $this->userInfo;
					
					if ($write_tf == "T") {
						$sql  = " Update class_lecture_impressions_tbl ";
						$sql .= " Set ";
						$sql .= "     contents = '$contents' ";
						$sql .= " Where lecture_seq = '$lecture_seq' ";
						$sql .= " And dvs_cd = 'A' ";
						$resource = mysql_query($sql);
						
						if (!$resource) {
							$response["rstCd"] = "500";
							$response["error"] = array();
							//$response["error"]["sql"] = $sql;
							$response["error"]["msg"] = "mysql_query(update resource) error";
							echo json_encode($response);
							return;
						}
					} else if ($write_tf == "F") {
						$sql  = " Insert Into class_lecture_impressions_tbl ";
						$sql .= " ( ";
						$sql .= "     seq, ";
						$sql .= "     lecture_seq, ";
						$sql .= "     memberID, ";
						$sql .= "     registration, ";
						$sql .= "     CompanyKind, ";
						$sql .= "     company, ";
						$sql .= "     dept_code, ";
						$sql .= "     position_code, ";
						$sql .= "     dept, ";
						$sql .= "     `position`, ";
						$sql .= "     contents, ";
						$sql .= "     insert_member, ";
						$sql .= "     insert_date, ";
						$sql .= "     dvs_cd ";
						$sql .= " ) ";
						$sql .= " Values ";
						$sql .= " ( ";
						$sql .= "     '$seq', ";
						$sql .= "     (Select a.* From (Select CoalEsce(Max(lecture_seq), 0) + 1 From class_lecture_impressions_tbl Where dvs_cd = 'A') a), ";
						$sql .= "     '$userInfo[memberID]', ";
						$sql .= "     '$userInfo[registration]', ";
						$sql .= "     '$userInfo[Company_Kind]', ";
						$sql .= "     '$userInfo[company_name]', ";
						$sql .= "     '$userInfo[dept_code]', ";
						$sql .= "     '$userInfo[position_code]', ";
						$sql .= "     '$userInfo[dept]', ";
						$sql .= "     '$userInfo[position]', ";
						$sql .= "     '$contents', ";
						$sql .= "     '$userInfo[insert_member]', ";
						$sql .= "     CurDate(), ";
						$sql .= "     'A' ";
						$sql .= " ) ";
						$resource = mysql_query($sql);
						
						if (!$resource) {
							$response["rstCd"] = "500";
							$response["error"] = array();
							//$response["error"]["sql"] = $sql;
							$response["error"]["msg"] = "mysql_query(insert resource) error";
							echo json_encode($response);
							return;
						}
					}
				} else if ($type == "D") {
					$userInfo = $this->getUserInfo($Company_Kind, $memberID, "MyClass", $year, $quarter);
					
					if ($write_tf == "T") {
						$sql  = " Update class_impressions_tbl ";
						$sql .= " Set ";
						$sql .= "     contents = '$contents' ";
						$sql .= " Where year = '$year' ";
						$sql .= " And quarter = '$quarter' ";
						$sql .= " And class_code = '$class_code' ";
						$sql .= " And CompanyKind= '$Company_Kind' ";
						$sql .= " And memberID = '$memberID' ";
						$resource = mysql_query($sql);
						
						if (!$resource) {
							$response["rstCd"] = "500";
							$response["error"] = array();
							//$response["error"]["sql"] = $sql;
							$response["error"]["msg"] = "mysql_query(update resource) error";
							echo json_encode($response);
							return;
						}
					} else if ($write_tf == "F") {
						$sql  = " Insert Into class_impressions_tbl  ";
						$sql .= " (  ";
						$sql .= "     year, ";
						$sql .= "     quarter, ";
						$sql .= "     class_code, ";
						$sql .= "     registration, ";
						$sql .= "     class_type, ";
						$sql .= "     company, ";
						$sql .= "     CompanyKind, ";
						$sql .= "     memberID, ";
						$sql .= "     dept_code, ";
						$sql .= "     position_code, ";
						$sql .= "     dept, ";
						$sql .= "     position, ";
						$sql .= "     contents, ";
						$sql .= "     insert_member, ";
						$sql .= "     insert_date, ";
						$sql .= "     seq ";
						$sql .= " )  ";
						$sql .= " Values  ";
						$sql .= " (  ";
						$sql .= "     '$year',  ";
						$sql .= "     '$quarter',  ";
						$sql .= "     '$class_code',  ";
						$sql .= "     '$userInfo[registration]',  ";
						$sql .= "     '$userInfo[class_type]',  ";
						$sql .= "     '$userInfo[company_name]',  ";
						$sql .= "     '$userInfo[Company_Kind]',  ";
						$sql .= "     '$userInfo[memberID]', ";
						$sql .= "     '$userInfo[dept_code]',  ";
						$sql .= "     '$userInfo[position_code]',  ";
						$sql .= "     '$userInfo[dept]', ";
						$sql .= "     '$userInfo[position]', ";
						$sql .= "     '$contents', ";
						$sql .= "     '$userInfo[insert_member]', ";
						$sql .= "     CurDate(), ";
						$sql .= "     '$seq' ";
						$sql .= " )  ";
						$resource = mysql_query($sql);
						
						if (!$resource) {
							$response["rstCd"] = "500";
							$response["error"] = array();
							$response["error"]["sql"] = $sql;
							$response["error"]["msg"] = "mysql_query(insert resource) error";
							echo json_encode($response);
							return;
						}
					}
				}
				
				echo json_encode($response);
				break;
			default :
				$userInfo = $this->userInfo;
				
				$sql  = " Select ";
				$sql .= "     b.*, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             Count(*) ";
				$sql .= "         From ";
				$sql .= "             study_retrieve_log_tbl x ";
				$sql .= "         Where x.seq = b.seq ";
				$sql .= "     ) retrieve_cnt, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             Count(*) ";
				$sql .= "         From ";
				$sql .= "             study_comment_tbl x ";
				$sql .= "         Where x.seq = b.seq ";
				$sql .= "     ) comment_cnt ";
				$sql .= " From ";
				$sql .= "     ( ";
				$sql .= "         Select   ";
				$sql .= "             a.seq, ";
				$sql .= "             a.title, ";
				$sql .= "             a.contents, ";
				$sql .= "             a.hanmacPick_cd, ";
				$sql .= "             Case ";
				$sql .= "                 When NullIf(a.thumbnail_img, '') Is Not Null Then a.thumbnail_img  ";
				$sql .= "                 When NullIf(a.img_path, '') Is Not Null Then a.img_path ";
				$sql .= "                 Else ";
				$sql .= "                     Null ";
				$sql .= "             End preview_img ";
				$sql .= "         From  ";
				$sql .= "             class_recommend_new_tbl a ";
				$sql .= "         Where a.hanmacPick_cd = 'E'  ";
				$sql .= "         And a.hide_yn = 'N'  ";
				$sql .= "         And a.category = 'A'  ";
				$sql .= "         Order By  ";
				$sql .= "             a.reg_dt Desc,  ";
				$sql .= "             a.orderno Desc  ";
				$sql .= "         limit 5 ";
				$sql .= "     ) b ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					echo "mysql_query(month Recommend resource) error";
					return;
				}
				
				$monthlyRecommendContents = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					$row["preview_img"] = $this->convertSatisPathToIntranetPath($row["preview_img"]);
					$row["videoCnt"] = $this->getIframeCount($row["contents"]);
					array_push($monthlyRecommendContents, $row);
				}
				$this->smarty->assign("monthlyRecommendContents", $monthlyRecommendContents);
				
				$sql  = " Select   ";
				$sql .= "     v.*,   ";
				$sql .= "     Case   ";
				$sql .= "         When (v.hanmacPick_cd = 'C' || v.hanmacPick_cd = 'D') Then 'T'   ";
				$sql .= "         Else   ";
				$sql .= "             'F'   ";
				$sql .= "     End IMPRESSION_TF,   ";
				$sql .= "     Case   ";
				$sql .= "         When v.hanmacPick_cd = 'C' Then (  ";
				$sql .= "                                             Select   ";
				$sql .= "                                                 Case When Count(*) > 0 Then 'T' Else 'F' End  ";
				$sql .= "                                             From   ";
				$sql .= "                                                 class_lecture_impressions_tbl a   ";
				$sql .= "                                             Where a.seq = v.seq   ";
				$sql .= "                                             And a.CompanyKind = '$userInfo[Company_Kind]'  ";
				$sql .= "                                             And a.memberID = '$userInfo[memberID]'  ";
				$sql .= "                                             And a.dvs_cd = 'A'  ";
				$sql .= "                                         )   ";
				$sql .= "         When v.hanmacPick_cd = 'D' Then (  ";
				$sql .= "                                             Select   ";
				$sql .= "                                                 Case When Count(*) > 0 Then 'T' Else 'F' End  ";
				$sql .= "                                             From   ";
				$sql .= "                                                 class_impressions_tbl a   ";
				$sql .= "                                             Where a.year = v.year   ";
				$sql .= "                                             And a.quarter = v.quarter   ";
				$sql .= "                                             And a.class_code = v.class_code   ";
				$sql .= "                                             And CompanyKind = '$userInfo[Company_Kind]'   ";
				$sql .= "                                             And memberID = '$userInfo[memberID]'   ";
				$sql .= "                                         )   ";
				$sql .= "         Else    ";
				$sql .= "             Null   ";
				$sql .= "     End IMPRESSION_WRITE_TF   ";
				$sql .= " From  ";
				$sql .= "     (  ";
				$sql .= "         Select  ";
				$sql .= "             t.contentsType,  ";
				$sql .= "             t.year,  ";
				$sql .= "             t.quarter,  ";
				$sql .= "             t.class_code,  ";
				$sql .= "             t.seq,  ";
				$sql .= "             t.referer, ";
				$sql .= "             t.referer_type, ";
				$sql .= "             u.title,  ";
				$sql .= "             u.contents,  ";
				$sql .= "             u.hanmacPick_cd,  ";
				$sql .= "             Case      ";
				$sql .= "                 When NullIf(u.thumbnail_img, '') Is Not Null Then u.thumbnail_img      ";
				$sql .= "                 When NullIf(u.img_path, '') Is Not Null Then u.img_path      ";
				$sql .= "                 Else     ";
				$sql .= "                     Null      ";
				$sql .= "             End preview_img,      ";
				$sql .= "             (     ";
				$sql .= "                 Select     ";
				$sql .= "                     Count(*)     ";
				$sql .= "                 From     ";
				$sql .= "                     study_retrieve_log_tbl a     ";
				$sql .= "                 Where a.seq = t.seq     ";
				$sql .= "             ) retrieve_cnt,     ";
				$sql .= "             (     ";
				$sql .= "                 Select     ";
				$sql .= "                     Count(*)     ";
				$sql .= "                 From     ";
				$sql .= "                     study_comment_tbl a     ";
				$sql .= "                 Where a.seq = t.seq     ";
				$sql .= "             ) comment_cnt,  ";
				$sql .= "             Case  ";
				$sql .= "                 When contentsType = 'history' Then t.reg_dt  ";
				$sql .= "                 Else  ";
				$sql .= "                     u.reg_dt  ";
				$sql .= "             End reg_dt,  ";
				$sql .= "             Case  ";
				$sql .= "                 When NullIf(u.orderno, '') Is Null Then '-999999'  ";
				$sql .= "                 Else  ";
				$sql .= "                     u.orderno  ";
				$sql .= "             End sortNo  ";
				$sql .= "         From  ";
				$sql .= "             (  ";
				$sql .= "                 Select    ";
				$sql .= "                      o.contentsType,   ";
				$sql .= "                      o.reg_dt,   ";
				$sql .= "                      o.year,   ";
				$sql .= "                      o.quarter,   ";
				$sql .= "                      o.class_code,   ";
				$sql .= "                      o.seq, ";
				$sql .= "                      o.referer, ";
				$sql .= "                      o.referer_type ";
				$sql .= "                  From    ";
				$sql .= "                     (    ";
				$sql .= "                         Select ";
				$sql .= "                             'history' contentsType,    ";
				$sql .= "                             c.reg_dt,  ";
				$sql .= "                             c.seq, ";
				$sql .= "                             d.year,  ";
				$sql .= "                             d.quarter,  ";
				$sql .= "                             d.class_code,  ";
				$sql .= "                             d.referer, ";
				$sql .= "                             d.referer_type ";
				$sql .= "                         From ";
				$sql .= "                             ( ";
				$sql .= "                                 Select       ";
				$sql .= "                                      'history' contentsType,    ";
				$sql .= "                                      Max(b.retrieve_seq) retrieve_seq, ";
				$sql .= "                                      Max(b.retrieve_time) reg_dt,  ";
				$sql .= "                                      b.company_code, ";
				$sql .= "                                      b.userno, ";
				$sql .= "                                      '' year,  ";
				$sql .= "                                      '' quarter,  ";
				$sql .= "                                      '' class_code,  ";
				$sql .= "                                      b.seq    ";
				$sql .= "                                 From       ";
				$sql .= "                                     (       ";
				$sql .= "                                         Select       ";
				$sql .= "                                             a.*       ";
				$sql .= "                                         From       ";
				$sql .= "                                             study_retrieve_log_tbl a        ";
				$sql .= "                                         Where company_code = '$userInfo[Company_Kind]'       ";
				$sql .= "                                         And userno = '$userInfo[memberID]'       ";
				$sql .= "                                         And Nullif(content_type, '') Is Null  ";
				$sql .= "                                     ) b        ";
				$sql .= "                                 Group By ";
				$sql .= "                                     b.company_code, ";
				$sql .= "                                     b.userno, ";
				$sql .= "                                     b.seq ";
				$sql .= "                             ) c join ";
				$sql .= "                             ( ";
				$sql .= "                                 Select ";
				$sql .= "                                     * ";
				$sql .= "                                 From ";
				$sql .= "                                       study_retrieve_log_tbl ";
				$sql .= "                                 Where company_code = '$userInfo[Company_Kind]'       ";
				$sql .= "                                 And userno = '$userInfo[memberID]'       ";
				$sql .= "                                 And Nullif(content_type, '') Is Null  ";
				$sql .= "                             ) d On c.seq = d.seq And c.company_code = d.company_code And c.userno = d.userno And c.retrieve_seq = d.retrieve_seq And c.reg_dt = d.retrieve_time ";
				$sql .= "                         Union All  ";
				$sql .= "                         Select       ";
				$sql .= "                             'history' contentsType,    ";
				$sql .= "                             Max(b.retrieve_time) reg_dt,  ";
				$sql .= "                             b.year,  ";
				$sql .= "                             b.quarter,  ";
				$sql .= "                             b.class_code,  ";
				$sql .= "                             b.seq, ";
				$sql .= "                             'HanmacPick', ";
				$sql .= "                             'D' ";
				$sql .= "                         From       ";
				$sql .= "                             (       ";
				$sql .= "                                 Select       ";
				$sql .= "                                     a.*       ";
				$sql .= "                                 From       ";
				$sql .= "                                     study_retrieve_log_tbl a        ";
				$sql .= "                                 Where company_code = '$userInfo[Company_Kind]'       ";
				$sql .= "                                 And userno = '$userInfo[memberID]'       ";
				$sql .= "                                 And content_type = 'myclass'  ";
				$sql .= "                             ) b        ";
				$sql .= "                         Group By       ";
				$sql .= "                             b.year,  ";
				$sql .= "                             b.quarter,  ";
				$sql .= "                             b.class_code,  ";
				$sql .= "                             b.seq  ";
				$sql .= "                      ) o   ";
				$sql .= "                 Union All  ";
				$sql .= "                 Select  ";
				$sql .= "                     p.*  ";
				$sql .= "                 From  ";
				$sql .= "                     (  ";
				$sql .= "                         Select       ";
				$sql .= "                            'comment' contentsType,  ";
				$sql .= "                            '' reg_dt,  ";
				$sql .= "                            '' year,  ";
				$sql .= "                            '' quarter,  ";
				$sql .= "                            '' class_code,  ";
				$sql .= "                            a.seq, ";
				$sql .= "                            'HanmacPick', ";
				$sql .= "                            a.hanmacPick_cd ";
				$sql .= "                         From      ";
				$sql .= "                             class_recommend_new_tbl a     ";
				$sql .= "                         Where a.hanmacPick_cd Not In ('C', 'D')  ";
				$sql .= "                         And a.category = 'A'  ";
				$sql .= "                     ) p  ";
				$sql .= "                 Union All  ";
				$sql .= "                 Select  ";
				$sql .= "                     q.*  ";
				$sql .= "                 From  ";
				$sql .= "                     (  ";
				$sql .= "                         Select       ";
				$sql .= "                            'impression_lecture' contentsType,  ";
				$sql .= "                            '' reg_dt,  ";
				$sql .= "                            '' year,  ";
				$sql .= "                            '' quarter,  ";
				$sql .= "                            '' class_code,  ";
				$sql .= "                            a.seq, ";
				$sql .= "                            'HanmacPick', ";
				$sql .= "                            a.hanmacPick_cd ";
				$sql .= "                         From      ";
				$sql .= "                             class_recommend_new_tbl a     ";
				$sql .= "                         Where a.hanmacPick_cd = 'C'      ";
				$sql .= "                         And a.category = 'A'      ";
				$sql .= "                     ) q  ";
				$sql .= "                 Union All  ";
				$sql .= "                 Select     ";
				$sql .= "                     r.*, ";
				$sql .= "                     'HanmacPick', ";
				$sql .= "                      'D' ";
				$sql .= "                 From     ";
				$sql .= "                     (     ";
				$sql .= "                         Select   ";
				$sql .= "                             'impression_myclass' contentsType,  ";
				$sql .= "                             '' reg_dt,  ";
				$sql .= "                             d.year,   ";
				$sql .= "                             d.quarter,   ";
				$sql .= "                             d.class_code,  ";
				$sql .= "                             d.seq  ";
				$sql .= "                         From   ";
				$sql .= "                             (   ";
				$sql .= "                                 Select   ";
				$sql .= "                                     a.*   ";
				$sql .= "                                 From   ";
				$sql .= "                                     class_quarters_tbl a Join   ";
				$sql .= "                                     (   ";
				$sql .= "                                         Select    ";
				$sql .= "                                             year,   ";
				$sql .= "                                             quarter   ";
				$sql .= "                                         From    ";
				$sql .= "                                             class_list_new_tbl   ";
				$sql .= "                                         Group By   ";
				$sql .= "                                             year,   ";
				$sql .= "                                             quarter       ";
				$sql .= "                                     ) b On a.year = b.year And a.quarter = b.quarter   ";
				$sql .= "                                 Where company = '$userInfo[Company_Kind]'   ";
				$sql .= "                                 And memberID = '$userInfo[memberID]'   ";
				$sql .= "                             ) c Join   ";
				$sql .= "                             class_list_new_tbl d On c.year = d.year And c.quarter = d.quarter And c.class_type = d.class_type    ";
				$sql .= "                     ) r     ";
				$sql .= "             ) t Join  ";
				$sql .= "             class_recommend_new_tbl u On t.seq = u.seq  ";
				$sql .= "         Where u.hide_yn = 'N' ";
				$sql .= "         And u.category = 'A' ";
				$sql .= "     ) v  ";
				$sql .= " Order By  ";
				$sql .= "     v.reg_dt Desc,  ";
				$sql .= "     v.sortNo Desc  ";
				$sql .= " limit 20  ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					echo "mysql_query(recentContents resource) error";
					return;
				}
				
				$recentContents = array();
				$duplicationChecker = array();
				$contentsLimit = 6;
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					if ($duplicationChecker[$row["seq"]]) {
						continue;
					}
					
					$duplicationChecker[$row["seq"]] = true;
					$row["preview_img"] = $this->convertSatisPathToIntranetPath($row["preview_img"]);
					$row["videoCnt"] = $this->getIframeCount($row["contents"]);
					$row["year"] = trim($row["year"]);
					$row["quarter"] = trim($row["quarter"]);
					$row["class_code"] = trim($row["class_code"]);
					
					array_push($recentContents, $row);
					if (count($recentContents) == $contentsLimit) {
						break;
					}
				}
				$this->smarty->assign("recentContents", $recentContents);
				$this->smarty->assign("userInfo", $userInfo);
				
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_HanmacPick_Main.tpl");
				break;
		}
	}
	
	/******************************************************************************
	 기    능 : 한맥배움터(교양)
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : 한맥114 > 한맥배움터
	 기    타 :
	 변경이력 :
	 	1. 2022-05-30 / 김윤하 / 김병철 / 최초작성
	 ******************************************************************************/
	function Culture() {
		extract($_REQUEST);
		global $db;
		
		switch($SubAction) {
			case "Main" :
				if (empty($page)) {
					$page = 1;
					$displayNum = 99999;
				}
				$commentTypes = array(
					"A" => "경영경제",
					"B" => "인문사회",
					"C" => "IT과학",
					"D" => "문화예술"
				);
				$userInfo = $this->userInfo;
				
				$sql  = " Select ";
				$sql .= "     d.*, ";
				$sql .= "     ( ";
				$sql .= "         Select   ";
				$sql .= "             Count(*) ";
				$sql .= "         From   ";
				$sql .= "             class_recommend_new_tbl   ";
				$sql .= "         Where culture_cd = '$type' ";
				$sql .= "         And hanmacPick_cd Not In ('C', 'D') ";
				$sql .= "         And category = 'A' ";
				$sql .= "     ) totalCnt, ";
				$sql .= "     Case ";
				$sql .= "         When NullIf(d.thumbnail_img, '') Is Not Null Then d.thumbnail_img ";
				$sql .= "         When NullIf(d.img_path, '') Is Not Null Then d.img_path ";
				$sql .= "         Else ";
				$sql .= "             Null ";
				$sql .= "     End preview_img ";
				$sql .= " From ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             b.*, ";
				$sql .= "             @ROWNUM := @ROWNUM + 1 SORT_NO ";
				$sql .= "         From ";
				$sql .= "             ( ";
				$sql .= "                 Select  ";
				$sql .= "                     a.*,  ";
				$sql .= "                     (  ";
				$sql .= "                         Select  ";
				$sql .= "                             Count(*)  ";
				$sql .= "                         From  ";
				$sql .= "                             study_comment_tbl x  ";
				$sql .= "                         Where x.seq = a.seq  ";
				$sql .= "                     ) comment_cnt,  ";
				$sql .= "                     (  ";
				$sql .= "                         Select  ";
				$sql .= "                             Count(*)  ";
				$sql .= "                         From  ";
				$sql .= "                             study_retrieve_log_tbl x  ";
				$sql .= "                         Where x.seq = a.seq  ";
				$sql .= "                     ) retrieve_cnt  ";
				$sql .= "                 From  ";
				$sql .= "                     (  ";
				$sql .= "                         Select   ";
				$sql .= "                             *   ";
				$sql .= "                         From   ";
				$sql .= "                             class_recommend_new_tbl   ";
				$sql .= "                         Where culture_cd = '$type'  ";
				$sql .= "                         And hanmacPick_cd Not In ('C', 'D')  ";
				$sql .= "                         And category = 'A'  ";
				$sql .= "                     ) a   ";
				$sql .= "                 Order By  ";
				$sql .= "                     a.orderno Desc ";
				$sql .= "             ) b, ";
				$sql .= "             ( ";
				$sql .= "                 Select  ";
				$sql .= "                     @ROWNUM := 0 ";
				$sql .= "             ) c ";
				$sql .= "     ) d ";
				$sql .= " Where SORT_NO Between ($page - 1) * $displayNum + 1 And $page * $displayNum ";
				$resource = mysql_query($sql, $db);
				
				if (!$resource) {
					echo "mysql_query(B resource) error";
					return;
				}
				
				$fullData = array();
				$isFristRow = true;
				$pageTotalCnt = $page * $displayNum;
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					if ($isFristRow) {
						$totalCnt =  $row["totalCnt"];
						$sortNoMax = min($totalCnt, $pageTotalCnt);
					}
					
					$row["videoCnt"] = $this->getIframeCount($row["contents"]);
					$row["sortNo"] = $sortNoMax;
					array_push($fullData, $row);
					
					$sortNoMax--;
					$isFristRow = false;
				}
				
				$this->smarty->assign("fullData", $fullData);
				$this->smarty->assign("typeName", $commentTypes[$type]);
				$this->smarty->assign("userInfo", $userInfo);
				
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_Culture_Comment_Main.tpl");
				break;
			case "Detail" :
				if (empty($type) || empty($seq)) {
					echo "Parameter Error";
					return;
				}
				
				if (!$this->insertRetrieveLog()) {
					echo "insertRetrieveLog error";
					return;
				}
				$userInfo = $this->userInfo;
				$writeTF = "F";
				
				$sql  = " Select  ";
				$sql .= "     a.*, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             Count(*) ";
				$sql .= "         From ";
				$sql .= "             study_retrieve_log_tbl x ";
				$sql .= "         Where x.seq = a.seq ";
				$sql .= "     ) retrieve_cnt ";
				$sql .= " From  ";
				$sql .= "     class_recommend_new_tbl a ";
				$sql .= " Where culture_cd = '$type' ";
				$sql .= " And category = 'A' ";
				$sql .= " And seq = '$seq' ";
				$resource = mysql_query($sql, $db);
				
				if (!$resource) {
					echo "mysql_query(recommend resource) error";
					return;
				}
				
				$fullData = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					if ($row[contents_cd] == "B") {
						$row["file_path"] = "http://$_SERVER[HTTP_HOST]/intranet_file/classRecommend/file/$row[seq]/file/$row[file_name]";
					} else {
						$row["file_path"] = $this->convertSatisPathToIntranetPath($row["file_path"]);
					}
					
					array_push($fullData, $row);
				}
				
				$sql  = " Select  ";
				$sql .= "     x.*  ";
				$sql .= " From  ";
				$sql .= "     (  ";
				$sql .= "         Select   ";
				$sql .= "             a.comment,   ";
				$sql .= "             a.seq,   ";
				$sql .= "             a.company_code,   ";
				$sql .= "             a.company_name,   ";
				$sql .= "             a.userno,   ";
				$sql .= "             a.comment_seq,   ";
				$sql .= "             Case    ";
				$sql .= "                 When NullIf(a.moddate, '') Is Not Null Then date_format(a.moddate, '%Y-%m-%d %H:%i:%s')    ";
				$sql .= "             Else   ";
				$sql .= "                 date_format(a.crtdate, '%Y-%m-%d %H:%i:%s')   ";
				$sql .= "             End write_date,   ";
				$sql .= "             b.kor_name KorName   ";
				$sql .= "         From  ";
				$sql .= "             ( ";
				$sql .= "                 Select ";
				$sql .= "                     *, ";
				$sql .= "                     Case ";
				$sql .= "                         When company_code = 'HALL' Then '50' ";
				$sql .= "                         When company_code ='HANM' Then '20' ";
				$sql .= "                         When company_code ='JANG' Then '40' ";
				$sql .= "                         When company_code ='PTC' Then '60' ";
				$sql .= "                         When company_code ='SAMA' Then '10' ";
				$sql .= "                     End Company_Kind ";
				$sql .= "                 From ";
				$sql .= "                     study_comment_tbl ";
				$sql .= "                 Where seq =  '$seq' ";
				$sql .= "             ) a,   ";
				$sql .= "             (   ";
				$sql .= "                 Select    ";
				$sql .= "                     *   ";
				$sql .= "                 From   ";
				$sql .= "                     total_member_tbl ";
				$sql .= "                 Where sys_comp_code ";
				$sql .= "             ) b   ";
				$sql .= "         Where 1 = 1   ";
				$sql .= "         And a.userno = b.member_id ";
				$sql .= "         And a.Company_Kind = b.sys_comp_code ";
				$sql .= "     ) x  ";
				$sql .= " Order By  ";
				$sql .= "     x.write_date Desc ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					echo "mysql_query(comment resource) error";
					return;
				}
				
				$comment = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					if ($row["userno"] == "$memberID") {
						$writeTF = "T";
					}
					array_push($comment, $row);
				}
				
				$this->smarty->assign("seq", $seq);
				$this->smarty->assign("company_code", $Company_Kind);
				$this->smarty->assign("fullData", $fullData);
				$this->smarty->assign("comment", $comment);
				$this->smarty->assign("writeTF", $writeTF);
				$this->smarty->assign("userInfo", $userInfo);
				
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_Culture_Comment_Detail.tpl");
				break;
			case "saveComment" :
				if (empty($seq)) {
					echo json_encode($this->getResponseError(500, "필수값 에러 ==> seq"));
					return;
				}
				
				$result = empty($comment_seq) ? $this->manipulateComment("insert") : $this->manipulateComment("update");
				echo json_encode($result);
				break;
			case "deleteComment" :
				$result = $this->manipulateComment("delete");
				echo json_encode($result);
				break;
			default : 
				$sql  = " Select ";
				$sql .= "     a.title, ";
				$sql .= "     a.contents, ";
				$sql .= "     a.seq, ";
				$sql .= "     a.culture_cd, ";
				$sql .= "     Case ";
				$sql .= "         When NullIf(a.thumbnail_img, '') Is Not Null Then a.thumbnail_img ";
				$sql .= "         When NullIf(a.img_path, '') Is Not Null Then a.img_path ";
				$sql .= "         Else ";
				$sql .= "             Null ";
				$sql .= "     End preview_img, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             Count(*) ";
				$sql .= "         From ";
				$sql .= "             study_retrieve_log_tbl x ";
				$sql .= "         Where x.seq = a.seq ";
				$sql .= "     ) retrieve_cnt, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             Count(*) ";
				$sql .= "         From ";
				$sql .= "             study_comment_tbl x ";
				$sql .= "         Where x.seq = a.seq ";
				$sql .= "     ) comment_cnt ";
				$sql .= " From ";
				$sql .= "     class_recommend_new_tbl a ";
				$sql .= " Where a.culture_cd In ('A', 'B', 'C', 'D') ";
				$sql .= " And a.hanmacPick_cd Not In ('C', 'D') ";
				$sql .= " And a.hide_yn = 'N' ";
				$sql .= " And a.category = 'A' ";
				$sql .= " Order By ";
				$sql .= "     a.reg_dt Desc, ";
				$sql .= "     a.orderno Desc ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					echo "mysql_query(resource) error";
					return;
				}
				
				$fullData = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					$row["videoCnt"] = $this->getIframeCount($row["contents"]);
					array_push($fullData, $row);
				}
				
				$this->smarty->assign("fullData", $fullData);
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_Culture_Main.tpl");
				break;
		}
	}
	
	/******************************************************************************
	 기    능 : 한맥배움터(직무)
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : 한맥114 > 한맥배움터 
	 기    타 :
	 변경이력 :
	 	1. 2022-05-30 / 김윤하 / 김병철 / 최초작성
	 ******************************************************************************/
	function Duty() {
		extract($_REQUEST);
		global $db;
		
		switch($SubAction) {
			case "Main" :
				if (empty($page)) {
					$page = 1;
					$displayNum = 99999;
				}
				$commentTypes = array(
					"A" => "토목공학",
					"B" => "IT개발",
					"C" => "디자인",
					"D" => "경영관리"
				);
				$userInfo = $this->userInfo;
				
				$sql  = " Select ";
				$sql .= "     d.*, ";
				$sql .= "     ( ";
				$sql .= "         Select   ";
				$sql .= "             Count(*) ";
				$sql .= "         From   ";
				$sql .= "             class_recommend_new_tbl   ";
				$sql .= "         Where duty_cd = '$type' ";
				$sql .= "         And category = 'A' ";
				$sql .= "     ) totalCnt, ";
				$sql .= "     Case ";
				$sql .= "         When NullIf(d.thumbnail_img, '') Is Not Null Then d.thumbnail_img ";
				$sql .= "         When NullIf(d.img_path, '') Is Not Null Then d.img_path ";
				$sql .= "         Else ";
				$sql .= "             Null ";
				$sql .= "     End preview_img ";
				$sql .= " From ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             b.*, ";
				$sql .= "             @ROWNUM := @ROWNUM + 1 SORT_NO ";
				$sql .= "         From ";
				$sql .= "             ( ";
				$sql .= "                 Select  ";
				$sql .= "                     a.*,  ";
				$sql .= "                     (  ";
				$sql .= "                         Select  ";
				$sql .= "                             Count(*)  ";
				$sql .= "                         From  ";
				$sql .= "                             study_comment_tbl x  ";
				$sql .= "                         Where x.seq = a.seq  ";
				$sql .= "                     ) comment_cnt,  ";
				$sql .= "                     (  ";
				$sql .= "                         Select  ";
				$sql .= "                             Count(*)  ";
				$sql .= "                         From  ";
				$sql .= "                             study_retrieve_log_tbl x  ";
				$sql .= "                         Where x.seq = a.seq  ";
				$sql .= "                     ) retrieve_cnt  ";
				$sql .= "                 From  ";
				$sql .= "                     (  ";
				$sql .= "                         Select   ";
				$sql .= "                             *   ";
				$sql .= "                         From   ";
				$sql .= "                             class_recommend_new_tbl   ";
				$sql .= "                         Where duty_cd = '$type'  ";
				$sql .= "                         And hanmacPick_cd Not In ('C', 'D')  ";
				$sql .= "                         And category = 'A'  ";
				$sql .= "                     ) a   ";
				$sql .= "                 Order By  ";
				$sql .= "                     a.orderno Desc ";
				$sql .= "             ) b, ";
				$sql .= "             ( ";
				$sql .= "                 Select  ";
				$sql .= "                     @ROWNUM := 0 ";
				$sql .= "             ) c ";
				$sql .= "     ) d ";
				$sql .= " Where SORT_NO Between ($page - 1) * $displayNum + 1 And $page * $displayNum ";
				$resource = mysql_query($sql, $db);
				
				if (!$resource) {
					echo "mysql_query(B resource) error";
					return;
				}
				
				$fullData = array();
				$isFristRow = true;
				$pageTotalCnt = $page * $displayNum;
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					if ($isFristRow) {
						$totalCnt =  $row["totalCnt"];
						$sortNoMax = min($totalCnt, $pageTotalCnt);
					}
					
					$row["videoCnt"] = $this->getIframeCount($row["contents"]);
					$row["sortNo"] = $sortNoMax;
					array_push($fullData, $row);
					
					$sortNoMax--;
					$isFristRow = false;
				}
				
				$this->smarty->assign("fullData", $fullData);
				$this->smarty->assign("typeName", $commentTypes[$type]);
				$this->smarty->assign("userInfo", $userInfo);
				
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_Duty_Comment_Main.tpl");
				break;
			case "Detail" :
				if (empty($type) || empty($seq)) {
					echo "Parameter Error";
					return;
				}
				
				if (!$this->insertRetrieveLog()) {
					echo "insertRetrieveLog error";
					return;
				}
				$writeTF = "F";
				$userInfo = $this->userInfo;
				
				$sql  = " Select  ";
				$sql .= "     a.*, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             Count(*) ";
				$sql .= "         From ";
				$sql .= "             study_retrieve_log_tbl x ";
				$sql .= "         Where x.seq = a.seq ";
				$sql .= "     ) retrieve_cnt ";
				$sql .= " From  ";
				$sql .= "     class_recommend_new_tbl a ";
				$sql .= " Where duty_cd = '$type' ";
				$sql .= " And category = 'A' ";
				$sql .= " And seq = '$seq' ";
				$resource = mysql_query($sql, $db);
				
				if (!$resource) {
					echo "mysql_query(recommend resource) error";
					return;
				}
				
				$fullData = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					if ($row[contents_cd] == "B") {
						$row["file_path"] = "http://$_SERVER[HTTP_HOST]/intranet_file/classRecommend/file/$row[seq]/file/$row[file_name]";
					} else {
						$row["file_path"] = $this->convertSatisPathToIntranetPath($row["file_path"]);
					}
					
					array_push($fullData, $row);
				}
				
				$sql  = " Select  ";
				$sql .= "     x.*  ";
				$sql .= " From  ";
				$sql .= "     (  ";
				$sql .= "         Select   ";
				$sql .= "             a.comment,   ";
				$sql .= "             a.seq,   ";
				$sql .= "             a.company_code,   ";
				$sql .= "             a.company_name,   ";
				$sql .= "             a.userno,   ";
				$sql .= "             a.comment_seq,   ";
				$sql .= "             Case    ";
				$sql .= "                 When NullIf(a.moddate, '') Is Not Null Then date_format(a.moddate, '%Y-%m-%d %H:%i:%s')    ";
				$sql .= "             Else   ";
				$sql .= "                 date_format(a.crtdate, '%Y-%m-%d %H:%i:%s')   ";
				$sql .= "             End write_date,   ";
				$sql .= "             b.kor_name KorName   ";
				$sql .= "         From  ";
				$sql .= "             ( ";
				$sql .= "                 Select ";
				$sql .= "                     *, ";
				$sql .= "                     Case ";
				$sql .= "                         When company_code = 'HALL' Then '50' ";
				$sql .= "                         When company_code ='HANM' Then '20' ";
				$sql .= "                         When company_code ='JANG' Then '40' ";
				$sql .= "                         When company_code ='PTC' Then '60' ";
				$sql .= "                         When company_code ='SAMA' Then '10' ";
				$sql .= "                     End Company_Kind ";
				$sql .= "                 From ";
				$sql .= "                     study_comment_tbl ";
				$sql .= "                 Where seq =  '$seq' ";
				$sql .= "             ) a,   ";
				$sql .= "             (   ";
				$sql .= "                 Select    ";
				$sql .= "                     *   ";
				$sql .= "                 From   ";
				$sql .= "                     total_member_tbl ";
				$sql .= "                 Where sys_comp_code ";
				$sql .= "             ) b   ";
				$sql .= "         Where 1 = 1   ";
				$sql .= "         And a.userno = b.member_id ";
				$sql .= "         And a.Company_Kind = b.sys_comp_code ";
				$sql .= "     ) x  ";
				$sql .= " Order By  ";
				$sql .= "     x.write_date Desc ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					echo "mysql_query(comment resource) error";
					return;
				}
				
				$comment = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					if ($row["userno"] == "$memberID") {
						$writeTF = "T";
					}
					array_push($comment, $row);
				}
				
				$this->smarty->assign("seq", $seq);
				$this->smarty->assign("company_code", $Company_Kind);
				$this->smarty->assign("fullData", $fullData);
				$this->smarty->assign("comment", $comment);
				$this->smarty->assign("writeTF", $writeTF);
				$this->smarty->assign("userInfo", $userInfo);
				
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_Duty_Comment_Detail.tpl");
				break;
			case "saveComment" :
				if (empty($seq)) {
					echo json_encode($this->getResponseError(500, "필수값 에러 ==> seq"));
					return;
				}
				
				$result = empty($comment_seq) ? $this->manipulateComment("insert") : $this->manipulateComment("update");
				echo json_encode($result);
				break;
			case "deleteComment" :
				$result = $this->manipulateComment("delete");
				echo json_encode($result);
				break;
			default :
				$userInfo = $this->userInfo;
				
				$sql  = " Select ";
				$sql .= "     a.title, ";
				$sql .= "     a.contents, ";
				$sql .= "     a.seq, ";
				$sql .= "     a.culture_cd, ";
				$sql .= "     a.duty_cd, ";
				$sql .= "     Case ";
				$sql .= "         When NullIf(a.thumbnail_img, '') Is Not Null Then a.thumbnail_img ";
				$sql .= "         When NullIf(a.img_path, '') Is Not Null Then a.img_path ";
				$sql .= "         Else ";
				$sql .= "             Null ";
				$sql .= "     End preview_img, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             Count(*) ";
				$sql .= "         From ";
				$sql .= "             study_retrieve_log_tbl x ";
				$sql .= "         Where x.seq = a.seq ";
				$sql .= "     ) retrieve_cnt, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             Count(*) ";
				$sql .= "         From ";
				$sql .= "             study_comment_tbl x ";
				$sql .= "         Where x.seq = a.seq ";
				$sql .= "     ) comment_cnt ";
				$sql .= " From ";
				$sql .= "     class_recommend_new_tbl a ";
				$sql .= " Where a.duty_cd In ('A', 'B', 'C', 'D') ";
				$sql .= " And a.hanmacPick_cd Not In ('C', 'D')  ";
				$sql .= " And a.hide_yn = 'N' ";
				$sql .= " And a.category = 'A' ";
				$sql .= " Order By ";
				$sql .= "     a.reg_dt Desc, ";
				$sql .= "     a.orderno Desc ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					echo "mysql_query(resource) error";
					return;
				}
				
				$fullData = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					$row["videoCnt"] = $this->getIframeCount($row["contents"]);
					array_push($fullData, $row);
				}
				
				$this->smarty->assign("fullData", $fullData);
				$this->smarty->assign("userInfo", $userInfo);
				
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_Duty_Main.tpl");
				break;
		}
	}
	
	
	/******************************************************************************
	 기    능 : 한맥배움터 검색
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : 한맥114 > 한맥배움터
	 기    타 :
	 변경이력 :
	 	1. 2022-05-30 / 김윤하 / 김병철 / 최초작성
	 ******************************************************************************/
	function Search() {
		extract($_REQUEST);
		global $db;
		$response = array (
			rstCd => 200,
			data => null,
			error => null
		);
		
		switch($SubAction) {
			case "deleteKeyword" :
				$sql  = " Delete ";
				$sql .= " From  ";
				$sql .= "     study_user_search_log_tbl ";
				$sql .= " Where company_code = '$Company_Kind' ";
				$sql .= " And userno = '$memberID' ";
				$sql .= " And search_seq = '$search_seq' ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					$response["rstCd"] = "500";
					$response["error"] = array();
					$response["error"]["msg"] = "mysql_query(deleteKeyword resource) error";
					
					echo json_encode($response);
					return;
				}
				
				echo json_encode($response);
				break;
			case "deleteAll" :
				$deletedList = "($deletedList)";
				$sql  = " Delete ";
				$sql .= " From  ";
				$sql .= "     study_user_search_log_tbl ";
				$sql .= " Where company_code = '$Company_Kind' ";
				$sql .= " And userno = '$memberID' ";
				$sql .= " And search_seq In $deletedList ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					$response["rstCd"] = "500";
					$response["error"] = array();
					$response["error"]["msg"] = "mysql_query(deleteAll resource) error";
					
					echo json_encode($response);
					return;
				}
				
				echo json_encode($response);
				break;
			case "insertKeyword" :
				$userInfo = $this->userInfo;
				$currDate = date("Y-m-d");
				$oneWeeksAgo = date("Y-m-d", strtotime("$currDate -7 day")) . " 00:00:00";
				$from = $oneWeeksAgo;
				$to = $currDate . " 23:59:59";
				
				$sql  = " Select ";
				$sql .= "     * ";
				$sql .= " From ";
				$sql .= "     study_user_search_log_tbl ";
				$sql .= " Where company_code = '$userInfo[Company_Kind]' ";
				$sql .= " And userno = '$userInfo[insert_member]' ";
				$sql .= " And keyword = '$search_text' ";
				$sql .= " And search_time Between '$from' And '$to'  ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					$response["rstCd"] = "500";
					$response["error"] = array();
					$response["error"]["sql"] = $sql;
					$response["error"]["msg"] = "mysql_query(duplication_checking resource) error";
					
					echo json_encode($response);
					return;
				}
				
				$searchData = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					array_push($searchData, $row);
				}
				
				$curdMode;
				if (count($searchData) > 0) {
					$curdMode = "update";
					$row = $searchData[0];
					
					$sql  = " Update study_user_search_log_tbl ";
					$sql .= " Set ";
					$sql .= "     search_time = sysdate() ";
					$sql .= " Where company_code = '$userInfo[Company_Kind]' ";
					$sql .= " And userno = '$userInfo[insert_member]' ";
					$sql .= " And search_seq = '$row[search_seq]' ";
				} else {
					$curdMode = "insert";
					
					$sql  = " Insert Into study_user_search_log_tbl ";
					$sql .= " ( ";
					$sql .= "     company_code, ";
					$sql .= "     userno, ";
					$sql .= "     search_time, ";
					$sql .= "     search_seq, ";
					$sql .= "     keyword, ";
					$sql .= "     dept_code, ";
					$sql .= "     position_code, ";
					$sql .= "     company_name, ";
					$sql .= "     dept_name, ";
					$sql .= "     position_name ";
					$sql .= " ) ";
					$sql .= " Values ";
					$sql .= " ( ";
					$sql .= "     '$userInfo[Company_Kind]',  ";
					$sql .= "     '$userInfo[memberID]',  ";
					$sql .= "     Sysdate(), ";
					$sql .= "     (Select a.* From (Select CoalEsce(Max(search_seq), 0) + 1 From study_user_search_log_tbl) a), ";
					$sql .= "     '$search_text',  ";
					$sql .= "     '$userInfo[dept_code]',  ";
					$sql .= "     '$userInfo[position_code]',  ";
					$sql .= "     '$userInfo[company_name]',  ";
					$sql .= "     '$userInfo[dept]',  ";
					$sql .= "     '$userInfo[position]' ";
					$sql .= " ) ";
					
				}
				$resource = mysql_query($sql);
				
				if (!$resource) {
					$response["rstCd"] = "500";
					$response["error"] = array();
					//$response["error"]["sql"] = $sql;
					$response["error"]["msg"] = "mysql_query($curdMode resource) error";
					
					echo json_encode($response);
					return;
				}
				
				echo json_encode($response);
				break;
			case "searchResult" :
				$userInfo = $this->getUserInfo($Company_Kind, $memberID);
				
				$category_name = array();
				$sql  = " Select ";
				$sql .= "     x.* ";
				$sql .= " From ";
				$sql .= "     systemconfig_tbl x ";
				$sql .= " Where x.SysKey In ( ";
				$sql .= "     'classRecommend_hanmacPick', ";
				$sql .= "     'classRecommend_duty', ";
				$sql .= "     'classRecommend_leader', ";
				$sql .= "     'classRecommend_culture' ";
				$sql .= " ) ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					echo "mysql_query(category resource) error";
					return;
				}
				
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					if ($row[SysKey] == "classRecommend_hanmacPick") {
						$category_name[hanmacPick][$row[Code]] = $row[Name];
					} else if ($row[SysKey] == "classRecommend_duty") {
						$category_name[duty][$row[Code]] = $row[Name];
					} else if ($row[SysKey] == "classRecommend_leader") {
						$category_name[leader][$row[Code]] = $row[Name];
					} else if ($row[SysKey] == "classRecommend_culture") {
						$category_name[culture][$row[Code]] = $row[Name];
					}
				}
				
				$sql  = " Select ";
				$sql .= "     a.*, ";
				$sql .= "     (    ";
				$sql .= "         Select    ";
				$sql .= "             Count(*)    ";
				$sql .= "         From    ";
				$sql .= "             study_retrieve_log_tbl x    ";
				$sql .= "         Where x.seq = a.seq    ";
				$sql .= "     ) retrieve_cnt,    ";
				$sql .= "     (    ";
				$sql .= "         Select    ";
				$sql .= "             Count(*)    ";
				$sql .= "         From    ";
				$sql .= "             study_comment_tbl x    ";
				$sql .= "         Where x.seq = a.seq    ";
				$sql .= "     ) comment_cnt, ";
				$sql .= "     b.title, ";
				$sql .= "     b.hanmacPick_cd, ";
				$sql .= "     Case    ";
				$sql .= "         When NullIf(b.thumbnail_img, '') Is Not Null Then b.thumbnail_img    ";
				$sql .= "         When NullIf(b.img_path, '') Is Not Null Then b.img_path    ";
				$sql .= "         When a.contentsType = 'impression_myclass' Then '../../image/phonebook/thumb_noimage.png'   ";
				$sql .= "         Else   ";
				$sql .= "             Null    ";
				$sql .= "     End preview_img    ";
				$sql .= " From ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             'impression_myclass' contentsType, ";
				$sql .= "             y.seq, ";
				$sql .= "             y.year, ";
				$sql .= "             y.quarter, ";
				$sql .= "             y.class_code ";
				$sql .= "         From ";
				$sql .= "             ( ";
				$sql .= "                 Select ";
				$sql .= "                     a.* ";
				$sql .= "                 From ";
				$sql .= "                     class_quarters_tbl a Join ";
				$sql .= "                     ( ";
				$sql .= "                         Select  ";
				$sql .= "                             year, ";
				$sql .= "                             quarter ";
				$sql .= "                         From  ";
				$sql .= "                             class_list_new_tbl ";
				$sql .= "                         Group By ";
				$sql .= "                             year, ";
				$sql .= "                             quarter     ";
				$sql .= "                     ) b On a.year = b.year And a.quarter = b.quarter ";
				$sql .= "                 Where company = '$userInfo[Company_Kind]' ";
				$sql .= "                 And memberID = '$userInfo[memberID]' ";
				$sql .= "             ) x Join ";
				$sql .= "             class_list_new_tbl y On x.year = y.year And x.quarter = y.quarter And x.class_type = y.class_type Join ";
				$sql .= "             class_recommend_new_tbl z On y.seq = z.seq  ";
				$sql .= "         Where z.hanmacPick_cd = 'D' ";
				$sql .= "         And z.category = 'A' ";
				$sql .= "         Union All ";
				$sql .= "         Select ";
				$sql .= "             'impression' contentsType, ";
				$sql .= "             x.seq, ";
				$sql .= "             '' year, ";
				$sql .= "             '' quarter, ";
				$sql .= "             '' class_code ";
				$sql .= "         From ";
				$sql .= "             class_recommend_new_tbl x ";
				$sql .= "         Where x.hanmacPick_cd = 'C' ";
				$sql .= "         And x.category = 'A' ";
				$sql .= "         Union All ";
				$sql .= "         Select ";
				$sql .= "             'comment' contentsType, ";
				$sql .= "             x.seq, ";
				$sql .= "             '' year, ";
				$sql .= "             '' quarter, ";
				$sql .= "             '' class_code ";
				$sql .= "         From ";
				$sql .= "             class_recommend_new_tbl x ";
				$sql .= "         Where x.hanmacPick_cd Not In ('C', 'D') ";
				$sql .= "         And x.category = 'A' ";
				$sql .= "     ) a Join ";
				$sql .= "     class_recommend_new_tbl b On a.seq = b.seq ";
				$sql .= " Where b.hide_yn = 'N' ";
				$sql .= " And b.category = 'A' ";
				$sql .= " And b.title Like '%$search_text%' ";
				$sql .= " Order By ";
				$sql .= "     b.reg_dt Desc,";
				$sql .= "     b.orderno Desc";
				
				if ($userInfo[memberID] == "B21319") {
					$sql  = " Select   ";
					$sql .= "     a.*,   ";
					$sql .= "     (      ";
					$sql .= "         Select      ";
					$sql .= "             Count(*)      ";
					$sql .= "         From      ";
					$sql .= "             study_retrieve_log_tbl x      ";
					$sql .= "         Where x.seq = a.seq      ";
					$sql .= "     ) retrieve_cnt,      ";
					$sql .= "     (      ";
					$sql .= "         Select      ";
					$sql .= "             Count(*)      ";
					$sql .= "         From      ";
					$sql .= "             study_comment_tbl x      ";
					$sql .= "         Where x.seq = a.seq      ";
					$sql .= "     ) comment_cnt,   ";
					$sql .= "     b.title,  ";
					$sql .= "     b.reg_dt,  ";
					$sql .= "     b.orderno,  ";
					$sql .= "     Case      ";
					$sql .= "         When NullIf(b.thumbnail_img, '') Is Not Null Then b.thumbnail_img      ";
					$sql .= "         When NullIf(b.img_path, '') Is Not Null Then b.img_path      ";
					$sql .= "         When a.contentsType = 'impression_myclass' Then '../../image/phonebook/thumb_noimage.png'     ";
					$sql .= "         Else     ";
					$sql .= "             Null      ";
					$sql .= "     End preview_img  ";
					$sql .= " From   ";
					$sql .= "     (   ";
					$sql .= "         Select   ";
					$sql .= "             'impression_myclass' contentsType,   ";
					$sql .= "             y.seq,   ";
					$sql .= "             y.year,   ";
					$sql .= "             y.quarter,   ";
					$sql .= "             y.class_code,  ";
					$sql .= "             z.hanmacPick_cd,  ";
					$sql .= "             z.duty_cd,  ";
					$sql .= "             z.leader_cd,  ";
					$sql .= "             z.culture_cd  ";
					$sql .= "         From   ";
					$sql .= "             (   ";
					$sql .= "                 Select   ";
					$sql .= "                     a.*   ";
					$sql .= "                 From   ";
					$sql .= "                     class_quarters_tbl a Join   ";
					$sql .= "                     (   ";
					$sql .= "                         Select    ";
					$sql .= "                             year,   ";
					$sql .= "                             quarter   ";
					$sql .= "                         From    ";
					$sql .= "                             class_list_new_tbl   ";
					$sql .= "                         Group By   ";
					$sql .= "                             year,   ";
					$sql .= "                             quarter       ";
					$sql .= "                     ) b On a.year = b.year And a.quarter = b.quarter   ";
					$sql .= "                 Where company = '$userInfo[Company_Kind]'   ";
					$sql .= "                 And memberID = '$userInfo[memberID]'   ";
					$sql .= "             ) x Join   ";
					$sql .= "             class_list_new_tbl y On x.year = y.year And x.quarter = y.quarter And x.class_type = y.class_type Join   ";
					$sql .= "             class_recommend_new_tbl z On y.seq = z.seq    ";
					$sql .= "         Where z.hanmacPick_cd = 'D'   ";
					$sql .= "         And z.category = 'A'  ";
					$sql .= "         Union All  ";
					$sql .= "         Select   ";
					$sql .= "             'comment' contentsType,   ";
					$sql .= "             x.seq,   ";
					$sql .= "             '' year,   ";
					$sql .= "             '' quarter,   ";
					$sql .= "             '' class_code,  ";
					$sql .= "             x.hanmacPick_cd,  ";
					$sql .= "             x.duty_cd,  ";
					$sql .= "             x.leader_cd,  ";
					$sql .= "             x.culture_cd  ";
					$sql .= "         From   ";
					$sql .= "             class_recommend_new_tbl x   ";
					$sql .= "         Where x.hanmacPick_cd Not In ('C', 'D')   ";
					$sql .= "         And x.category = 'A'   ";
					$sql .= "     ) a Join   ";
					$sql .= "     class_recommend_new_tbl b On a.seq = b.seq   ";
					$sql .= " Where b.hide_yn = 'N'   ";
					$sql .= " And b.category = 'A'   ";
					$sql .= " And b.title Like '%$search_text%'   ";
					$sql .= " Order By   ";
					$sql .= "     b.reg_dt Desc,  ";
					$sql .= "     b.orderno Desc  ";
				}
				
				$resource = mysql_query($sql);
				
				if (!$resource) {
					echo "mysql_query(searchResult resource) error";
					return;
				}
				
				$searchedData = array();
				$rowCnt = mysql_num_rows($resource);
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					if ($userInfo[memberID] == "B21319") {
						$category = "";
						
						if ($row[hanmacPick_cd] != "A") {
							$category .= $category == "" ? $category_name[hanmacPick][$row[hanmacPick_cd]] : "," . $category_name[hanmacPick][$row[hanmacPick_cd]];
						}
						if ($row[duty_cd] != "E") {
							$category .= $category == "" ? $category_name[duty][$row[duty_cd]] : "," . $category_name[duty][$row[duty_cd]];
						}
						if ($row[leader_cd] != "D") {
							$category .= $category == "" ? $category_name[leader][$row[leader_cd]] : "," . $category_name[leader][$row[leader_cd]];
						}
						if ($row[culture_cd] != "E") {
							$category .= $category == "" ? $category_name[culture][$row[culture_cd]] : "," . $category_name[culture][$row[culture_cd]];
						}
						
						$row[category_name] = $category;
						array_push($searchedData, $row);
					} else {
						array_push($searchedData, $row);
					}
				}
				
				$this->smarty->assign("rowCnt", $rowCnt);
				$this->smarty->assign("searchedData", $searchedData);
				
				$this->smarty->display("intranet/common_contents/work_myclass/study2/Study_SearchResult.tpl");
				break;
			default :
				$currDate = date("Y-m-d");
				$oneWeeksAgo = date("Y-m-d", strtotime("$currDate -7 day")) . " 00:00:00";
				$from = $oneWeeksAgo;
				$to = $currDate . " 23:59:59";
				$userInfo = $this->userInfo;
				
				$sql  = " Select  ";
				$sql .= "     a.company_code, ";
				$sql .= "     a.userno, ";
				$sql .= "     date_format(a.search_time, '%y-%m-%d') search_date, ";
				$sql .= "     a.search_time, ";
				$sql .= "     a.search_seq, ";
				$sql .= "     a.keyword ";
				$sql .= " From  ";
				$sql .= "     study_user_search_log_tbl a ";
				$sql .= " Where company_code = '$userInfo[Company_Kind]' ";
				$sql .= " And userno = '$userInfo[memberID]' ";
				$sql .= " And search_time Between '$from' And '$to' ";
				$sql .= " Order By ";
				$sql .= "     a.search_time Desc ";
				$sql .= " limit 10 ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					echo "mysql_query(searchList resource) error";
					return;
				}
				
				$searchList = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					array_push($searchList, $row);
				}
				
				$this->smarty->assign("searchList", $searchList);
				
				$this->smarty->display("intranet/common_contents/work_myclass/study/Study_Search.tpl");
				break;
		}
	}
	
	/******************************************************************************
	 기    능 : 댓글 Insert, Update, Delete
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : 한맥114 > 한맥배움터
	 기    타 :
	 변경이력 :
	 	1. 2022-05-30 / 김윤하 / 김병철 / 최초작성
	 ******************************************************************************/
	function manipulateComment($crudMode) {
		extract($_REQUEST);
		$response = array (
			rstCd => 200,
			data => null,
			error => null
		);
		
		if ($crudMode == "insert") {
			$sql  = " Insert into study_comment_tbl ";
			$sql .= " ( ";
			$sql .= "     seq, ";
			$sql .= "     comment_seq, ";
			$sql .= "     company_code, ";
			$sql .= "     userno, ";
			$sql .= "     dept_code, ";
			$sql .= "     position_code, ";
			$sql .= "     company_name, ";
			$sql .= "     dept_name, ";
			$sql .= "     position_name, ";
			$sql .= "     crtdate, ";
			$sql .= "     crtuser, ";
			$sql .= "     comment ";
			$sql .= " )";
			$sql .= " Values ";
			$sql .= " ( ";
			$sql .= "     '$seq', ";
			$sql .= "     (Select a.* From (Select Coalesce(Max(comment_seq), 0) + 1 comment_seq From study_comment_tbl) a), ";
			$sql .= "     '$Company_Kind', ";
			$sql .= "     '$memberID', ";
			$sql .= "     '$dept_code', ";
			$sql .= "     '$position_code', ";
			$sql .= "     '$company_name', ";
			$sql .= "     '$dept_name', ";
			$sql .= "     '$position_name', ";
			$sql .= "     sysdate(), ";
			$sql .= "     '$memberID', ";
			$sql .= "     '$comment' ";
			$sql .= " ); ";
			$resource = mysql_query($sql);
			
			if (!$resource) {
				$response["rstCd"] = "500";
				$response["error"] = array();
				$response["error"]["sql"] = $sql;
				$response["error"]["msg"] = "mysql_query(insert resource) error";
			}
		} else if ($crudMode == "update") {
			$sql  = " Update study_comment_tbl ";
			$sql .= " Set ";
			$sql .= "     comment = '$comment', ";
			$sql .= "     moddate = sysdate(), ";
			$sql .= "     moduser = '$memberID' ";
			$sql .= " Where seq = '$seq' ";
			$sql .= " And company_code = '$Company_Kind' ";
			$sql .= " And userno = '$memberID' ";
			$sql .= " And comment_seq = '$comment_seq' ";
			$resource = mysql_query($sql);
			
			if (!$resource) {
				$response["rstCd"] = "500";
				$response["error"] = array();
				//$response["error"]["sql"] = $sql;
				$response["error"]["msg"] = "mysql_query(update resource) error";
			}
		} else if ($crudMode == "delete") {
			$sql  = " Delete ";
			$sql .= " From  ";
			$sql .= "     study_comment_tbl  ";
			$sql .= " Where seq = '$seq'  ";
			$sql .= " And company_code = '$Company_Kind' ";
			$sql .= " And userno = '$memberID' ";
			$sql .= " And comment_seq = '$comment_seq' ";
			$resource = mysql_query($sql);
			
			if (!$resource) {
				$response["rstCd"] = 500;
				$response["error"] = array();
				$response["error"]['msg'] = "mysql_query(delete resource) error";
			}
		}
		
		return $response;
	}
	
	/******************************************************************************
	 기    능 : 조회로그 
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : 한맥114 > 한맥배움터
	 기    타 :
	 변경이력 :
	 	1. 2022-05-30 / 김윤하 / 김병철 / 최초작성
	******************************************************************************/
	function insertRetrieveLog() {
		global $db;
		extract($_REQUEST);
		
		if (empty($Company_Kind) || empty($memberID)) {
			return false;
		}
		$userInfo = $this->userInfo;
		
		$sql  = " Insert Into study_retrieve_log_tbl ";
		$sql .= " ( ";
		$sql .= "     seq, ";
		$sql .= "     retrieve_seq, ";
		$sql .= "     company_code, ";
		$sql .= "     userno, ";
		$sql .= "     retrieve_time, ";
		$sql .= "     dept_code, ";
		$sql .= "     position_code, ";
		$sql .= "     company_name, ";
		$sql .= "     dept_name, ";
		$sql .= "     position_name, ";
		$sql .= "     referer, ";
		$sql .= "     referer_type, ";
		$sql .= "     year, ";
		$sql .= "     quarter, ";
		$sql .= "     class_code, ";
		$sql .= "     content_type ";
		$sql .= " ) ";
		$sql .= " Values ";
		$sql .= " ( ";
		$sql .= "     '$seq', ";
		$sql .= "     (Select a.* From (Select CoalEsce(Max(retrieve_seq), 0) + 1 From study_retrieve_log_tbl) a), ";
		$sql .= "     '$userInfo[Company_Kind]', ";
		$sql .= "     '$memberID', ";
		$sql .= "      sysdate(), ";
		$sql .= "     '$userInfo[dept_code]', ";
		$sql .= "     '$userInfo[position_code]', ";
		$sql .= "     '$userInfo[company_name]', ";
		$sql .= "     '$userInfo[dept]', ";
		$sql .= "     '$userInfo[position]', ";
		$sql .= "     '$MainAction', ";
		$sql .= "     '$type', ";
		$sql .= "     '$year', ";
		$sql .= "     '$quarter', ";
		$sql .= "     '$class_code', ";
		$sql .= "     '$content_type' ";
		$sql .= " ) ";
		$resource = mysql_query($sql, $db);
		
		if (!$resource) {
			return false;
		}
		
		return true;
	}
	
	/******************************************************************************
	 기    능 : MyClass 로그
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : intranet/common_contents/work_myclass/MyClass.tpl : 학습실 > My Class
	 기    타 :
		1. 2022-05-30 / 김윤하 / 김병철 / 최초작성
											- 접근로그 필요한 경우 사용
	 ******************************************************************************/
	function writeAccessLog() {
		$sCurYear = date('Y');
		$sCurMonth = date('m');
		$sCurDay = date('Y-m-d');
		
		$sFolderName = "../log/study/" . "$sCurYear/" . "$sCurMonth/" ;
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
	기    능 : 사용자 정보 가져오기
	관 련 DB :
	프로시져 :
	사용메뉴 : 한맥114 > 한맥배움터
	기    타 :
	******************************************************************************/
	function getUserInfo($Company_Kind, $memberID, $type=null, $year=null, $quarter=null) {
		global $db;
		$userInfo = array();
		
		if (empty($Company_Kind) || empty($memberID)) {
			$html = $this->getHtmlError(401);
			echo $html;
			exit;
		}
		
		if ($type == "MyClass") {
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
			$sql .= "             a.kor_name,     ";
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
			$sql .= "         And a.comp_code = '$Company_Kind'     ";
			$sql .= "     ) d ";
			
			$resource = mysql_query($sql);
			if (!$resource) {
				echo "mysql_query(user_info) error";
				return;
			}
			
			$userInfo = mysql_fetch_array($resource, MYSQL_ASSOC);
		} else {
			//HALLA HANMAC JANG PTC SAMAN
			//HALL HANM JANG PTC SAMA
			$convertCompanyCode = array (
				'HALL' => "HALLA",
				'HANM' => "HANMAC",
				'JANG' => "JANG",
				'PTC' => "PTC",
				'SAMA' => "SAMAN"
			);
			$convertSystemCompanyCode = array (
				'HALL' => "50",
				'HANM' => "20",
				'JANG' => "40",
				'PTC' => "60",
				'SAMA' => "10"
			);
			
			$companyCode = $convertCompanyCode[$Company_Kind];
			$systemCompanyCode = $convertSystemCompanyCode[$Company_Kind];
			
			$sql  = " Select  ";
			$sql .= "     kor_name, ";
			$sql .= "     Case ";
			$sql .= "         When sys_comp_code = 10 Then 'SAMA' ";
			$sql .= "         When sys_comp_code = 20 Then 'HANM' ";
			$sql .= "         When sys_comp_code = 40 Then 'JANG' ";
			$sql .= "         When sys_comp_code = 50 Then 'HALL' ";
			$sql .= "         When sys_comp_code = 60 Then 'PTC' ";
			$sql .= "         Else ";
			$sql .= "             Null ";
			$sql .= "     End Company_Kind,  ";
			$sql .= "     Case ";
			$sql .= "         When sys_comp_code = 10 Then '삼안' ";
			$sql .= "         When sys_comp_code = 20 Then '바론컨설턴트' ";
			$sql .= "         When sys_comp_code = 40 Then '장헌산업' ";
			$sql .= "         When sys_comp_code = 50 Then '한라산업개발' ";
			$sql .= "         When sys_comp_code = 60 Then '피티씨' ";
			$sql .= "         Else ";
			$sql .= "             Null ";
			$sql .= "     End company_name, ";
			$sql .= "     dept_name dept, ";
			$sql .= "     dept_code, ";
			$sql .= "     member_id insert_member, ";
			$sql .= "     member_id memberID, ";
			$sql .= "     rank_name position, ";
			$sql .= "     rank_intra_code position_code, ";
			$sql .= "     Replace(jumin_no, '-', '') registration    ";
			$sql .= " From ";
			$sql .= "     total_member_tbl ";
			$sql .= " Where sys_comp_code = '$systemCompanyCode' ";
			$sql .= " And member_id = '$memberID' ";
			
			$resource = mysql_query($sql);
			if (!$resource) {
				echo "mysql_query(user_info) error";
				return;
			}
			
			$userInfo = mysql_fetch_array($resource, MYSQL_ASSOC);
		}
		
		return $userInfo;
	}
	
	function getResponseError($code, $msg="") {
		$defaultMsg = array(
			400 => "잘못된 요청입니다. 관리자에게 문의하세요.",
			404 => "페이지를 찾을 수 없습니다.",
			500 => "처리도중 문제가 발생하였습니다.. 관리자에게 문의하세요.",
			401 => "로그인 후 정상적으로 사용가능합니다.",
			403 => "권한이 없는 사용자는 접근할 수없습니다. 관리자에게 문의하세요.",
		);
		
		$response = array (
			rstCd => 200,
			data => null,
			error => array()
		);
		
		if (empty($msg)) {
			$msg = $defaultMsg[$code];
		}
		
		$response["rstCd"] = $code;
		$response["error"]["msg"] = $msg;
		
		return $response;
	}
	
	function getHtmlError($code, $msg="") {
		$defaultMsg = array(
			400 => "잘못된 요청입니다. 관리자에게 문의하세요.",
			404 => "페이지를 찾을 수 없습니다.",
			500 => "처리도중 문제가 발생하였습니다.. 관리자에게 문의하세요.",
			401 => "로그인 후 정상적으로 사용가능합니다.",
			403 => "권한이 없는 사용자는 접근할 수없습니다. 관리자에게 문의하세요.",
		);
		
		if (empty($msg)) {
			$msg = $defaultMsg[$code];
		}
		$CRLF = "\r\n";
		
		$html .= "<html>$CRLF";
		$html .= "<head>$CRLF";
			$html .= "<meta charset='utf-8' />$CRLF";
		$html .= "</head>$CRLF";
		$html .= "<body>$CRLF";
			$html .= $msg;
		$html .= "</body>$CRLF";
		$html .= "</html>$CRLF";
		
		return $html;
	}
	
	/******************************************************************************
	 기    능 : Satis 파일 Path -> 인트라넷 파일 Path 변환
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : 한맥114 > 한맥배움터
	 기    타 :
	 변경이력 :
	 	1. 2022-05-30 / 김윤하 / 김병철 / 최초작성
	 ******************************************************************************/
	function convertSatisPathToIntranetPath($filePath) {
		if (empty($filePath)) {
			return $filePath;
		}
		
		$pattern = "/\.\.\//i";
		return preg_replace($pattern, '', $filePath, 1);
	}
	
	function getIframeCount($content) {
		$offset = 0;
		$videoCnt = 0;
		
		if (empty($content)) {
			return $videoCnt;
		}
		
		while (true) {
			$offset = strpos($content, "<iframe", $offset);
			if ($offset === false) {
				break;
			}
			
			$offset += 7;
			$videoCnt++;
		}
		
		return $videoCnt;
	}
}
?>