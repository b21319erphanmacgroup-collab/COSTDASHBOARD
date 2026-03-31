<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php

	/***************************************
	* 기본 결재 수신인 설정
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH
	****************************************/ 

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../inc/approval_function.php";


	if ($CompanyKind=="")
	{
		$CompanyKind=searchCompanyKind2();
	}

	extract($_GET);
	class ApprovalSettingLogic {
		var $smarty;

		function ApprovalSettingLogic($smarty)
		{
			$this->smarty=$smarty;
		}

		//============================================================================
		// 기본결재선 보기
		//============================================================================
		function InsertPage()
		{	
			global $db,$memberID;
			global $FormNum,$i,$CompanyKind;

			if($CompanyKind=="JANG")
			{
				$KeyName[0]="관리자";
				$KeyName[1]="임원"; 
			}
			else if($CompanyKind=="PILE")
			{
				$KeyName[0]="관리자";
				$KeyName[1]="임원"; 
			}
			else if ($CompanyKind=="HANM")
			{
				$KeyName[0]="팀장";
				$KeyName[1]="부서장"; 
			}
			else if ($CompanyKind=="BARO")
			{
				$KeyName[0]="팀장";
				$KeyName[1]="부서장";
			}
			
			for($i=0; $i<=1; $i++) 
			{
				$sql="select * from 
					(
						select b.MemberNo,b.RankCode,b.KorName from 
						(	
							select * from sanctionmember_tbl where MemberNo='$memberID' and KeyName='$KeyName[$i]'
						)a left join
						(	
							select * from member_tbl 
						)b on a.SanctionMember=b.MemberNo
					)aa left join
					(
						select * from systemconfig_tbl where SysKey='PositionCode'
					)bb on aa.RankCode=bb.Code";

				
				$re = mysql_query($sql,$db);
				$sanctionnum_row[$i]=mysql_num_rows($re);
				if ($sanctionnum_row[$i]>"0")
				{
					{
						$SanctionMember[$i] = mysql_result($re,0,"MemberNo");
						$approval_name[$i] = mysql_result($re,0,"KorName");
						$approval_rank[$i] = mysql_result($re,0,"Name");
					}
				}
				
			}

			$this->smarty->assign('gcode',$gcode);
			$this->smarty->assign('KeyName',$KeyName);
			$this->smarty->assign('approval_name',$approval_name);
			$this->smarty->assign('approval_rank',$approval_rank);
			$this->smarty->assign('SanctionMember',$SanctionMember);

			$this->smarty->assign("page_action","approval_setting_controller.php");
			$this->smarty->display("intranet/common_contents/work_approval/approval_setting_mvc.tpl");	
		}


		//============================================================================
		// 결재선 설정
		//============================================================================
		function InsertAction()
		{

			global $db,$memberID;
			global $FormNum,$i,$KeyName,$mCode;

			// 결재 문서를 작성하여 임시저장 또는 결재상신 (기안자 본인만 해당)

			$azSQL = "delete from SanctionMember_tbl where MemberNo='$memberID'";
			$result=mysql_query($azSQL,$db);

			for($i=0; $i<=1; $i++) {
				$azSQL = "insert into SanctionMember_tbl (MemberNo, SanctionStep, KeyName, SanctionMember) values('$memberID', $i, '$KeyName[$i]', '$mCode[$i]')";
				// echo $azSQL."<br>";
				mysql_query($azSQL,$db);
			}
		
			$this->smarty->assign('target',"no");
			$this->smarty->display("intranet/move_page.tpl");
		
		}

		//============================================================================
		// 기본결재선 보기
		//============================================================================
		function InsertDocPage()
		{	
			global $db,$memberID;
			global $FormNum,$i,$CompanyKind,$DocSN;

			$KeyName[0]="부서장";
			$KeyName[1]="대표이사"; 
		
			$sql="select * from sanctiondoc_tbl where DocSN='$DocSN'";
			//echo $sql."<br>"; 
			$re = mysql_query($sql,$db);
			$sanctionnum_row=mysql_num_rows($re);
			if ($sanctionnum_row>"0")
			{
				$RT_Sanction = mysql_result($re,0,"RT_Sanction");
				$RT_SanctionState = mysql_result($re,0,"RT_SanctionState");

				$RT_SanctionStep = substr($RT_SanctionState,0,1);
				
				$TmpArr = split("RECEIVE:",$RT_Sanction);
				$TmpArr2 = split(":",$TmpArr[1]);

				for($i=0; $i<2; $i++) {

					$TmpMemnerNo = split("-",$TmpArr2[$i]);

					if($TmpMemnerNo[0] <> "")
					{
						$sql2="select a.MemberNo,korName,Name as RankName from 
						(
							select * from member_tbl where MemberNo='$TmpMemnerNo[0]'
						)a left join
						(
							select * from systemconfig_tbl where SysKey='PositionCode'
						)b on a.RankCode=b.Code";
						
						//echo $sql2."<Br>";
						
						$re2 = mysql_query($sql2,$db);
						
						$SanctionMember[$i]= mysql_result($re2,0,"MemberNo");
						$approval_name[$i]= mysql_result($re2,0,"korName");
						$approval_rank[$i] = mysql_result($re2,0,"RankName");
					}else
					{
						$SanctionMember[$i]= "";
						$approval_name[$i]= "";
						$approval_rank[$i] = "";
					}

				}
			}
			

			$this->smarty->assign('gcode',$gcode);
			$this->smarty->assign('KeyName',$KeyName);
			$this->smarty->assign('approval_name',$approval_name);
			$this->smarty->assign('approval_rank',$approval_rank);
			$this->smarty->assign('SanctionMember',$SanctionMember);
			$this->smarty->assign('RT_SanctionStep',$RT_SanctionStep);
			$this->smarty->assign('DocSN',$DocSN);
			
			$this->smarty->assign("page_action","approval_setting_controller.php");
			$this->smarty->display("intranet/common_contents/work_approval/approval_docsetting_mvc.tpl");	
		}


		//============================================================================
		// 발신공문결재선 설정
		//============================================================================
		function InsertDocAction()
		{

			global $db,$memberID;
			global $FormNum,$i,$KeyName,$mCode,$DocSN;

			$sql="select * from sanctiondoc_tbl where DocSN='$DocSN'";
			//echo $sql."<br>"; 
			$re = mysql_query($sql,$db);
			$sanctionnum_row=mysql_num_rows($re);
			if ($sanctionnum_row>"0")
			{
				$RT_Sanction = mysql_result($re,0,"RT_Sanction");
				$RT_SanctionState = mysql_result($re,0,"RT_SanctionState");
				$RT_SanctionStep = substr($RT_SanctionState,0,1);
				
				$TmpArr = split("RECEIVE:",$RT_Sanction);
				
				if($mCode[0] =="")
				{
					$Receive_01=":";
				}else
				{
					$Receive_01=$mCode[0]."-담당:";
				}

				if($mCode[1] =="")
				{
					$Receive_02=":";
				}else
				{
					$Receive_02=$mCode[1]."-부서장:";
				}

				//$NewRT_Sanction=$TmpArr[0]."RECEIVE:".$mCode[0]."-담당:".$mCode[1]."-부서장:FINISH:" ;

				$NewRT_Sanction=$TmpArr[0]."RECEIVE:".$Receive_01.$Receive_02."FINISH:" ;


				$TmpArr2 = split(":",$RT_SanctionState);

				if($RT_SanctionStep=="5")
				{	//$TmpArr2[2]=$mCode[0]."-담당";
					$TmpArr2[2]=$Receive_01;
				}else
				{	//$TmpArr2[2]=$mCode[1]."-부서장";
					$TmpArr2[2]=$Receive_02;
				}

				$NewRT_SanctionState=$TmpArr2[0].":".$TmpArr2[1].":".$TmpArr2[2].":".$TmpArr2[3];
				
				$upsql = "update sanctiondoc_tbl set RT_Sanction='$NewRT_Sanction', RT_SanctionState='$NewRT_SanctionState' where DocSN='$DocSN'";
				//echo $upsql."<Br>";
				mysql_query($upsql,$db);

			}
			
			$this->smarty->assign('target',"reload2");
			$this->smarty->display("intranet/move_page.tpl");
		}
	}
?>
