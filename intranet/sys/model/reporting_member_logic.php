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

	extract($_GET);
	class ReportingMemberLogic {
		var $smarty;
		function ReportingMemberLogic($smarty)
		{
			$this->smarty=$smarty;
		}

		
		//============================================================================
		// 업무 보고 받을 인원 목록
		//============================================================================
		function ReportingMemberSelect()
		{	
			global $db,$memberID;
			global $auth,$i,$gcode,$FormNum,$tmptitle;

			$group_data = array(); 
			
			
			$sql = "select * from systemconfig_tbl where SysKey = 'GroupCode' AND Code IN ('03','98') order by Code";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re)){
				array_push($group_data,$re_row);
			}
	
			//그룹멤버---------------------
			$member_data = array(); 
			
			if($gcode==""){
				$gcode=$_SESSION["MyGroupCode"];
			}
			
			if($gcode != ""){
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
				
				
				//echo $sql2;

				$re2 = mysql_query($sql2,$db);
				while($re_row2 = mysql_fetch_array($re2)) {
					
					
					array_push($member_data,$re_row2);
				}
			}

			$this->smarty->assign('i',$i);
			$this->smarty->assign('gcode',$gcode);
			$this->smarty->assign('group_data',$group_data);
			$this->smarty->assign('member_data',$member_data);

			$this->smarty->assign("page_action","reporting_member_controller.php");
			$this->smarty->display("intranet/common_layout/reporting_member_mvc.tpl");
		}



		

	}

?>