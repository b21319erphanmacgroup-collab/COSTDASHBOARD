<?php
include "../inc/dbcon.inc"; // 한맥
include "../../sys/inc/function_intranet.php";
include "../../../SmartyConfig.php";
include "../util/HanamcPageControl.php";

extract($_REQUEST);
class ClassRemommendLogic {
	var $smarty;
	
	function ClassRemommendLogic($smarty) {
		$Controller = "ClassRecommendController.php";
		$ActionMode = $_REQUEST['ActionMode'];
		
		$this->smarty=$smarty;
		$this->smarty->assign('Controller', $Controller);
		$this->smarty->assign('ActionMode', $ActionMode);
		
		if ($ActionMode == "Mobile") {
			$userInfo = array (
				"Company_Kind" => $_REQUEST['Company_Kind'],
				"memberID" => $_REQUEST['memberID']
			);
			
			$this->smarty->assign("userInfo", $userInfo);
		}
	}
	
	function assignRequest() {
		foreach ($_REQUEST As $key => $value) {
			$this->smarty->assign($key, $value);
		}
	}
	
	/******************************************************************************
	기    능 : 추천콘텐츠(웹)
	관 련 DB :
	프로시져 :
	사용메뉴 : intranet/common_contents/work_recommend/classRecommend.tpl : 학습실 > 추천콘텐츠
	기    타 :
	변경이력 :
			1. 2022-02-28 / 김윤하 / 김병철 / 최초작성
	******************************************************************************/
	function main() {
		extract($_REQUEST);
		global $db;
		
		switch ($SubAction) {
			case "getRecommend" :
				$result = array();
				
				$sql  = " Select   ";
				$sql .= "     *   ";
				$sql .= " From   ";
				$sql .= "     class_recommend_tbl ";
				$sql .= " Where seq = $seq ";
				$resource = mysql_query($sql, $db);
				
				if (!$resource) {
					$result["rstCd"] = 500;
					$result["data"] = null;
					$result["error"] = array(
						"msg" => "mysql_query(getRecommend_resource) error"
					);
					echo json_encode($result);
					return;
				}
				
				$fullData = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					array_push($fullData, $row);
				}
				
				$result["rstCd"] = 200;
				$result["data"] = $fullData;
				$result["error"] = null;
				echo json_encode($result);
				
				break;
			default :
				if ($currentPage == "") {
					$currentPage = 1;
				}
				
				if ($dispCount == "") {
					$dispCount = 15;
				}
				
				$sql  = " Select ";
				$sql .= "     c.*, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             Count(*) ";
				$sql .= "         From ";
				$sql .= "             class_recommend_tbl a ";
				$sql .= "         Where ('$searchCode' = '' Or '$searchCode' = 'title' And a.title Like '%$searchText%')";
				$sql .= "     ) TOTAL_COUNT ";
				$sql .= " From ";
				$sql .= "     ( ";
				$sql .= "         Select    ";
				$sql .= "             @ROW_NUM := @ROW_NUM + 1 SORT_NO, ";
				$sql .= "             a.seq,  ";
				$sql .= "             a.title,  ";
				$sql .= "             a.contents,  ";
				$sql .= "             a.file_path,  ";
				$sql .= "             a.file_name,  ";
				$sql .= "             Date_Format(a.reg_dt, '%Y-%m-%d') reg_dt  ";
				$sql .= "         From    ";
				$sql .= "             class_recommend_tbl a,";
				$sql .= "             ( ";
				$sql .= "                 Select  ";
				$sql .= "                     @ROW_NUM := 0 ";
				$sql .= "                 From ";
				$sql .= "                     Dual ";
				$sql .= "             ) b ";
				$sql .= "         Where ('$searchCode' = '' Or '$searchCode' = 'title' And a.title Like '%$searchText%') ";
				$sql .= "         Order By      ";
				$sql .= "             a.orderno Desc";
				$sql .= "     ) c ";
				$sql .= " Where c.SORT_NO Between ($currentPage - 1) * $dispCount + 1 And ($currentPage - 1) * $dispCount + $dispCount ";
				$resource = mysql_query($sql, $db);
				
				if (!$resource) {
					echo "mysql_query(default_resource) error";
					return;
				}
				
				$fullData = array();
				$TOTAL_COUNT = 0;
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					array_push($fullData, $row);
					$TOTAL_COUNT = $row["TOTAL_COUNT"];
				}
			
				$PageHandler =new PageControl($this->smarty);
				$PageHandler->SetMaxRow($TOTAL_COUNT);
				$PageHandler->SetCurrentPage($currentPage);
				$PageHandler->PutTamplate();
				$page_action = "ClassRecommendController.php";
				
				$this->smarty->assign("TOTAL_COUNT", $TOTAL_COUNT);
				$this->smarty->assign("currentPage", $currentPage);
				$this->smarty->assign("searchCode", $searchCode);
				$this->smarty->assign("searchText", $searchText);
				$this->smarty->assign("memberID", $memberID);
				$this->smarty->assign("Company_Kind", $Company_Kind);
				$this->smarty->assign("page_action", $page_action);
				$this->smarty->assign("fullData", $fullData);
				
				$this->smarty->display("intranet/common_contents/work_recommend/classRecommend.tpl");
		}
	
	}
	
	/******************************************************************************
	 기    능 : 추천콘텐츠(모바일)
	 관 련 DB :
	 프로시져 :
	 사용메뉴 : 
	 	1. intranet/common_contents/work_recommend/classRecommend.tpl : 학습실 > 추천콘텐츠
	 	2. 한맥114 > 추천콘텐츠
	 기    타 :
	 변경이력 :
	 	1. 2022-03-02 / 김윤하 / 김병철 / 최초작성
	 ******************************************************************************/
	function Mobile() {
		extract($_REQUEST);
		global $db;
		
		switch ($SubAction) {
			case "view" :
				$sql  = " Select ";
				$sql .= "     * ";
				$sql .= " From ";
				$sql .= "     class_recommend_tbl ";
				$sql .= " Where seq = $seq ";
				$resource = mysql_query($sql);
				
				if (!$resource) {
					echo "mysql_query(view_resource) error";
					return;
				}
				
				$fullData = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					array_push($fullData, $row);
				}
				
				$this->smarty->assign("fullData", $fullData);
				
				$this->smarty->display("intranet/common_contents/work_recommend/classRecommendMobileView.tpl");
				break;
			case "getList" :
				$sql  = " Select ";
				$sql .= "     c.* ";
				$sql .= " From ";
				$sql .= "     ( ";
				$sql .= "         Select    ";
				$sql .= "             (@ROW_NUM := @ROW_NUM + 1) SORT_NO, ";
				$sql .= "             a.seq,  ";
				$sql .= "             a.title,  ";
				$sql .= "             a.contents,  ";
				$sql .= "             a.file_path,  ";
				$sql .= "             a.file_name,  ";
				$sql .= "             Case  ";
				$sql .= "                 When a.thumbnail_img Is Null || a.thumbnail_img = ''  Then '../../image/classRecommend/thumb_noimage.png'  ";
				$sql .= "                 Else   ";
				$sql .= "                     a.thumbnail_img  ";
				$sql .= "             End thumbnail_img,  ";
				$sql .= "             Date_Format(a.reg_dt, '%Y-%m-%d') reg_dt  ";
				$sql .= "         From    ";
				$sql .= "             class_recommend_tbl a, ";
				$sql .= "             ( ";
				$sql .= "                 Select ";
				$sql .= "                     @ROW_NUM := 0 ";
				$sql .= "                 From ";
				$sql .= "                     Dual ";
				$sql .= "             ) b ";
				$sql .= "         Order By      ";
				$sql .= "             a.orderno  Desc ";
				$sql .= "     ) c ";
				$sql .= " Where c.SORT_NO Between ($paging - 1) * $dispCount + 1 And ($paging - 1) * $dispCount + $dispCount ";
				$resource = mysql_query($sql, $db);
				
				if (!$resource) {
					echo "mysql_query(getList_resource) error";
					return;
				}
				
				$fullData = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					$row["videoCnt"] = $this->countIframeTag($row);
					array_push($fullData, $row);
				}
				
				echo json_encode($fullData);
				break;
			default :
				if (empty($paging)) {
					$paging = 1;
				}
				
				if (empty($dispCount)) {
					$dispCount = 20;
				}
				
				$lastPage = 1;
				$sql  = " Select ";
				$sql .= "     c.*, ";
				$sql .= "     ( ";
				$sql .= "         Select ";
				$sql .= "             ceil(Count(*) / $dispCount) ";
				$sql .= "         From ";
				$sql .= "             class_recommend_tbl ";
				$sql .= "     ) LAST_PAGE ";
				$sql .= " From ";
				$sql .= "     ( ";
				$sql .= "         Select    ";
				$sql .= "             (@ROW_NUM := @ROW_NUM + 1) SORT_NO, ";
				$sql .= "             a.seq,  ";
				$sql .= "             a.title,  ";
				$sql .= "             a.contents,  ";
				$sql .= "             a.file_path,  ";
				$sql .= "             a.file_name,  ";
				$sql .= "             Case  ";
				$sql .= "                 When a.thumbnail_img Is Null || a.thumbnail_img = ''  Then '../../image/classRecommend/thumb_noimage.png' ";
				$sql .= "                 Else   ";
				$sql .= "                     a.thumbnail_img  ";
				$sql .= "             End thumbnail_img,  ";
				$sql .= "             Date_Format(a.reg_dt, '%Y-%m-%d') reg_dt  ";
				$sql .= "         From    ";
				$sql .= "             class_recommend_tbl a, ";
				$sql .= "             ( ";
				$sql .= "                 Select ";
				$sql .= "                     @ROW_NUM := 0 ";
				$sql .= "                 From ";
				$sql .= "                     Dual ";
				$sql .= "             ) b ";
				$sql .= "         Order By      ";
				$sql .= "             a.orderno  Desc ";
				$sql .= "     ) c ";
				$sql .= " Where c.SORT_NO Between ($paging - 1) * $dispCount + 1 And ($paging - 1) * $dispCount + $dispCount ";
				$resource = mysql_query($sql, $db);
				
				if (!$resource) {
					echo "mysql_query(default_resource) error";
					return;
				}
				
				$fullData = array();
				while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
					$row["videoCnt"] = $this->countIframeTag($row);
					$lastPage = $row['LAST_PAGE'];
					
					array_push($fullData, $row);
				}
				
				$this->smarty->assign("paging", $paging);
				$this->smarty->assign("dispCount", $dispCount);
				$this->smarty->assign("lastPage", $lastPage);
				$this->smarty->assign("fullData", $fullData);
				
				$this->smarty->display("intranet/common_contents/work_recommend/classRecommendMobile.tpl");
		}
		
	}
	
	function countIframeTag($row) {
		if (empty($row)) {
			return;
		}
		
		$offset = 0;
		$videoCnt = 0;
		while (true) {
			$offset = strpos($row["contents"], "<iframe", $offset);
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