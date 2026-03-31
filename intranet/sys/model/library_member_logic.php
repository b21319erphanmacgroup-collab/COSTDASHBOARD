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
	class LibraryMemberLogic {
		var $smarty;
		function LibraryMemberLogic($smarty)
		{
			$this->smarty=$smarty;
		}

		//============================================================================
		// 전자결재 수신인 설정
		//============================================================================
		function LibraryMemberChange()
		{	
			global $db,$memberID,$CompanyKind;
			global $auth,$i,$gcode,$FormNum,$tmptitle;

	
			//echo $CompanyKind."<Br>";
			

			if($CompanyKind=="HANM")
			{


			}else if($CompanyKind=="JANG")
			{
					//$host="192.168.2.250";
					$host="192.168.10.6";
					$user="root";
					$pass="erp";
					$dataname="hanmacerp";
					$jangheon_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$jangheon_db);
					//mysql_query("set names euckr");
					mysql_set_charset("utf-8",$db);
					mysql_query("set names utf8");
					//$result=mysql_query($dbup9,$jangheon_db);
					//mysql_close($jangheon_db);
			}
			else if($CompanyKind=="PILE")
			{
					//$host="192.168.2.249";
					$host="192.168.10.7";
					$user="root";
					$pass="erp";
					$dataname="hanmacerp";
					$piletech_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$piletech_db);
					//mysql_query("set names euckr");
					mysql_set_charset("utf-8",$db);
					mysql_query("set names utf8");
					//$result=mysql_query($dbup9,$piletech_db);
					//mysql_close($piletech_db);
			}
			else if($CompanyKind=="HALL")
			{
					
					$host="1.234.37.143";
					$user="root";
					$pass="vbxsystem";
					$dataname="hallaerp";
					$halla_db=mysql_connect($host,$user,$pass);
					mysql_select_db($dataname,$halla_db);
					//mysql_query("set names euckr");
					mysql_set_charset("utf-8",$db);
					mysql_query("set names utf8");
					//$result=mysql_query($dbup9,$halla_db);
					//mysql_close($halla_db);
				
			}


			$group_data = array(); 

			if(!$gcode) $gcode=MemberNo2GroupCode($memberID);

			//그룹 리스트---------------------
			$sql="select * from systemconfig_tbl where SysKey = 'GroupCode' and Code <> '99' order by orderno";		
			
			if($CompanyKind=="HANM")
			{
				$re = mysql_query($sql,$db);
			}else if($CompanyKind=="JANG")
			{
				$re = mysql_query($sql,$jangheon_db);
			}else if($CompanyKind=="PILE")
			{
				$re = mysql_query($sql,$piletech_db);
			}else if($CompanyKind=="HALL")
			{
				$re = mysql_query($sql,$halla_db);
			}
		
		

			while($re_row = mysql_fetch_array($re)) 
			{
				$Code=$re_row[Code];
				$Name=$re_row[Name];
				array_push($group_data,$re_row);
			}

	
			//그룹멤버---------------------
			$member_data = array(); 

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
			
			if($CompanyKind=="HANM")
			{
				$re2 = mysql_query($sql2,$db);
			}else if($CompanyKind=="JANG")
			{
				$re2 = mysql_query($sql2,$jangheon_db);
			}else if($CompanyKind=="PILE")
			{
				$re2 = mysql_query($sql2,$piletech_db);
			}else if($CompanyKind=="HALL")
			{
				$re2 = mysql_query($sql2,$halla_db);
			}



			while($re_row2 = mysql_fetch_array($re2)) 
			{
				array_push($member_data,$re_row2);
			}
			

			$this->smarty->assign('i',$i);
			$this->smarty->assign('gcode',$gcode);
			$this->smarty->assign('group_data',$group_data);
			$this->smarty->assign('member_data',$member_data);
			$this->smarty->assign('CompanyKind',$CompanyKind);

			$this->smarty->assign("page_action","library_member_controller.php");
			$this->smarty->display("intranet/common_contents/work_book/library_HM_member_mvc.tpl");
		}

	}

?>