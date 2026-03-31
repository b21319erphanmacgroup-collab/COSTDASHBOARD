<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php

	/***************************************
	* 결재 수신인 설정
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH 
	****************************************/ 


	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../inc/approval_function.php";	

	extract($_GET);
	class ApprovalMemberLogic {
		var $smarty;
		function ApprovalMemberLogic($smarty)
		{
			$this->smarty=$smarty;
		}

		//============================================================================
		// 전자결재 수신인 설정
		//============================================================================
		function ApprovalMemberChange()
		{	
			global $db,$memberID;
			global $auth,$i,$gcode,$FormNum,$tmptitle;

			$group_data = array(); 
			//if(!$gcode) $gcode=substr($_COOKIE['CK_GroupCode'],1,2);
			if(!$gcode) $gcode=MemberNo2GroupCode($memberID);

			//그룹 리스트---------------------
			$sql="select * from systemconfig_tbl where SysKey = 'GroupCode' and Code <> '99' order by orderno";		
			
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)) 
			{
				$Code=$re_row[Code];
				$Name=$re_row[Name];
				array_push($group_data,$re_row);
			}

	
			//그룹멤버---------------------
			$member_data = array(); 
			
			if($gcode=="03x" || $gcode=="98x")
			{
				$sql2 = "select aa.*,aa.Name as RankName,bb.Name as GroupName from 
				(
					select *  from
					(
						select * from member_tbl where WorkPosition ='1' and GroupCode='$gcode' order by RealRankCode,RankCode,order_index,EntryDate 
					)a left JOIN
					(
						select * from systemconfig_tbl where SysKey='PositionCode'
					)b on a.RankCode = b.code
				)aa left JOIN
				(
					select * from systemconfig_tbl where SysKey='GroupCode'
				)bb on aa.GroupCode=bb.Code";
			}else
			{
				$subSql = "";
				// 건설관리사업부 이창용, 정미희 추가 정재익이사 요청 240717 서승완
				// M23047 조규형이사 퇴사처리로 인한 사번 제거 241014 서승완
				if($gcode == "31"){					
					// $subSql = " AND MemberNo IN ('B15202','M04602','M05305','M20402','M21423','M23047')  OR MemberNo IN ('M05508','M22007')";	
					//$subSql = " AND MemberNo IN ('B15202','M04602','M05305','M20402','M21423')  OR MemberNo IN ('M05508','M22007')";	//20241202 PM1343 주석처리 조규형 이사 없음
					$subSql = " AND MemberNo IN ('B15202','M04602','M05305','M20402','M21423','M24072')  OR MemberNo IN ('M05508','M22007')";
				}
				
				
				$sql2 = "select aa.*,aa.Name as RankName,bb.Name as GroupName from 
				(
					select *  from
					(
						select * from member_tbl where WorkPosition ='1' and GroupCode='$gcode' ".$subSql." order by RankCode asc
					)a left JOIN
					(
						select * from systemconfig_tbl where SysKey='PositionCode'
					)b on a.RankCode = b.code
				)aa left JOIN
				(
					select * from systemconfig_tbl where SysKey='GroupCode'
				)bb on aa.GroupCode=bb.Code";
			}
			
			// echo $sql2;

			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2)) 
			{
				//양식에서 선택시 
				if  ($FormNum=="approval_option")
				{	
					$member_resign=$tmptitle;
					/*
					if ($re_row2[MemberNo] != Group2Manager($gcode) && $tmptitle =="임원") 
					{
						$member_resign=$member_resign."-대결";
					}
					*/
					
					$re_row2[resign]=$member_resign;
				}
				
				array_push($member_data,$re_row2);
			}
			

			$this->smarty->assign('i',$i);
			$this->smarty->assign('tmptitle',$tmptitle);
			$this->smarty->assign('gcode',$gcode);
			$this->smarty->assign('group_data',$group_data);
			$this->smarty->assign('FormNum',$FormNum);
			$this->smarty->assign('member_data',$member_data);

			$this->smarty->assign("page_action","approval_member_controller.php");
			$this->smarty->display("intranet/common_contents/work_approval/aproval_member_mvc.tpl");
		}

		

		//============================================================================
		// 전자결재 수신인 설정
		//============================================================================
		function ApprovalMemberSelect()
		{	
			global $db,$memberID;
			global $auth,$i,$gcode,$FormNum,$tmptitle;

			$group_data = array(); 
					

			
			
			$sql = "select * from systemconfig_tbl where SysKey = 'NoticeGroup' and CodeOrName <> '' order by Code";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)) 
			{
				array_push($group_data,$re_row);
			}

	
			//그룹멤버---------------------
			$member_data = array(); 

			if($gcode=="03x")
			{
				$sql2 = "select aa.*,aa.Name as RankName,bb.Name as GroupName from 
				(
					select *  from
					(
						select * from member_tbl where WorkPosition ='1' and GroupCode='$gcode' order by RealRankCode,RankCode,EntryDate 
					)a left JOIN
					(
						select * from systemconfig_tbl where SysKey='PositionCode'
					)b on a.RankCode = b.code
				)aa left JOIN
				(
					select * from systemconfig_tbl where SysKey='GroupCode'
				)bb on aa.GroupCode=bb.Code";
			}else
			{
				$sql2 = "select aa.*,aa.Name as RankName,bb.Name as GroupName from 
				(
					select *  from
					(
						select * from member_tbl where WorkPosition ='1' and GroupCode='$gcode' order by RankCode asc
					)a left JOIN
					(
						select * from systemconfig_tbl where SysKey='PositionCode'
					)b on a.RankCode = b.code
				)aa left JOIN
				(
					select * from systemconfig_tbl where SysKey='GroupCode'
				)bb on aa.GroupCode=bb.Code";
			}
			
			//echo $sql2;

			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2)) 
			{
				//양식에서 선택시 
				if  ($FormNum=="approval_option")
				{	
					$member_resign=$tmptitle;
					/*
					if ($re_row2[MemberNo] != Group2Manager($gcode) && $tmptitle =="임원") 
					{
						$member_resign=$member_resign."-대결";
					}
					*/
					
					$re_row2[resign]=$member_resign;
				}
				
				array_push($member_data,$re_row2);
			}
			

			$this->smarty->assign('i',$i);
			$this->smarty->assign('tmptitle',$tmptitle);
			$this->smarty->assign('gcode',$gcode);
			$this->smarty->assign('group_data',$group_data);
			$this->smarty->assign('FormNum',$FormNum);
			$this->smarty->assign('member_data',$member_data);

			$this->smarty->assign("page_action","approval_member_controller.php");
			$this->smarty->display("intranet/common_contents/work_approval/aproval_memberselect_mvc.tpl");
		}



		

	}

?>