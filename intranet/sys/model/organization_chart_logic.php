<?php

	/***************************************
	* 조직도
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/
	include "../../sys/inc/dbcon.inc";
	include "../../sys/inc/function_intranet.php";
	include "../../../SmartyConfig.php";

	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드

	extract($_GET);
	require_once($SmartyClassPath);

	class OrganizationChart extends Smarty {



		//================================================================================
		// 조직도 부서별 LIST Logic
		//================================================================================
		function OrganizationChart()
		{

			global $SmartyClass_TemplateDir;
			global $SmartyClass_CompileDir;
			global $SmartyClass_ConfigDir;
			global $SmartyClass_CacheDir;
			global $ProjectCode,$bridgeno,$n_num,$Item_no,$id,$mode,$CompanyKind;

			$this->Smarty();
			$this->template_dir	=$SmartyClass_TemplateDir;
			$this->compile_dir	=$SmartyClass_CompileDir;
			$this->config_dir	=$SmartyClass_ConfigDir;

		}


		//============================================================================
		// 인사 정보 표시
		//============================================================================
		function OrganizationChartList()
		{

			global $db;
			global $sel_year,$sel_month,$sel_day,$GroupCode,$CompanyKind,$get_memberID;

			$query_data = array();


			$uyear = date("Y")+1;
			$last_day = date("t",mktime(0,0,0,date("m"),1,date("Y")));
			$last_day =$last_day +1;

			if($sel_year=="") $sel_year=date("Y");
			if($sel_month=="") $sel_month=date("m");
			if($sel_day=="") $sel_day=date("d");

			$this->assign('uyear',$uyear);
			$this->assign('last_day',$last_day);
			$this->assign('sel_year',$sel_year);
			$this->assign('sel_month',$sel_month);
			$this->assign('sel_day',$sel_day);

			$date=sprintf('%04d-%02d-%02d',$sel_year,$sel_month,$sel_day);

			$holy_sc = holy($date);

			if($GroupCode=="")
				$GroupCode=$_SESSION['SS_GroupCode'];


			//echo $GroupCode;
			if($GroupCode =="" || $GroupCode=="all")
			{
				$presql="select * from member_tbl where WorkPosition = 1 order by GroupCode,RankCode,JuminNo";
			}
			else if($GroupCode =="ceo_info")
			{
				if($CompanyKind=="JANG")
				{
					$presql="select * from member_tbl where MemberNo='J06401'";
				}
				else if($CompanyKind=="PILE")
				{
					$presql="select * from member_tbl where MemberNo='P06201'";
				}
				else if ($CompanyKind=="HANM")
				{
					$presql="select * from member_tbl where MemberNo='M02107'";
				}
				else if ($CompanyKind=="BARO")
				{
					$presql="select * from member_tbl where MemberNo='M02107'";
				}
			}
			else
			{

					if($GroupCode=="3") //기술개발센터
					{
						//$presql="select * from member_tbl where WorkPosition = 1 and GroupCode='$GroupCode' and MemberNo not in ('T03203','T03225','B14306','J08305','M19301') order by GroupCode,RealRankCode,RankCode,EntryDate"	;
						$presql="select * from member_tbl where WorkPosition in (1,3) and GroupCode='$GroupCode' order by GroupCode, IF(RealRankCode > 'C7',RankCode,IF(RealRankCode='',RankCode, IFNULL(RealRankCode,RankCode))),binary(korName),EntryDate"	;
					}else if($GroupCode=="98") //총괄기획시
					{
						//$presql="select * from member_tbl where WorkPosition = 1 and GroupCode='$GroupCode' or MemberNo in ('T03203','T03225','B14306','J08305','M19301') order by RankCode,EntryDate"	;

						//$presql="select * from member_tbl where WorkPosition = 1 and GroupCode='$GroupCode' order by RankCode,EntryDate"	;
						$presql="select * from member_tbl where WorkPosition =1 and GroupCode='$GroupCode' order by GroupCode, IF(RealRankCode > 'C7',RankCode,IF(RealRankCode='',RankCode, IFNULL(RealRankCode,RankCode))),binary(korName),EntryDate"	;

					}else
					{
						$presql="select * from member_tbl where WorkPosition = 1 and GroupCode='$GroupCode' order by GroupCode,RankCode,JuminNo"	;
					}
			}
			$sql = "select a1.*,a2.Name as Position from
					(
						".$presql."
					)
						a1 left JOIN
					(
						select * from systemconfig_tbl where SysKey='PositionCode'
					)
						a2 on a1.RankCode = a2.code";

			if($GroupCode=="98") //총괄기획시
			{
				//$sql=$sql." order by a1.RealRankCode,a1.EntryDate";
			}

			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			$num=1;
			while($re_row = mysql_fetch_array($re))
			{
				$PhotoFile="../../../erpphoto/".$re_row[MemberNo].".jpg";
				$exist_file = file_exists("$PhotoFile");
				if($exist_file) {
					$re_row[Photo]=$PhotoFile;
				}else
				{
					$re_row[Photo]="../../image/sub_08_user.png";
				}

				$Mobile=$re_row[Mobile];
				if(strlen($Mobile) == 11)
					$Mobile=substr($Mobile,0,3)."-".substr($Mobile,3,4)."-".substr($Mobile,7);
				else if(strlen($Mobile) == 10)
					$Mobile=substr($Mobile,0,3)."-".substr($Mobile,3,3)."-".substr($Mobile,6);

				$re_row[Mobile]=$Mobile;
				$re_row[korName2]= urlencode($re_row[korName]);

				if($re_row[HeadType] <>"")
				{
					$re_row[MemberNo2]=$re_row[HeadType];
				}else
				{
					$re_row[MemberNo2]=$re_row[MemberNo];
				}
				$re_row[JuminNo] = substr( $re_row[JuminNo], 0, 6 );
				array_push($query_data,$re_row);
			}

			//============================================================================
			// 팀선택 탭 관련 Logic
			//============================================================================
			$get_memberID=strtoupper($get_memberID);
			$this->assign('get_memberID',$get_memberID);

			//장종찬실장,권혁진부장만 -권부장요청 (인사정보)  ,최은영,정혜윤
			if($get_memberID=="M02210" || $get_memberID=="B20304" || $get_memberID=="T03225" || $get_memberID=="TADMIN" || $get_memberID=="T08301" || $get_memberID=="B17305" || $get_memberID=="B20334" || $get_memberID=="M20330" || $get_memberID=="T02303" || $get_memberID=="M20328" || $get_memberID=="M21201" )
			{
				if($GroupCode=="98" || $GroupCode=="3"  || $GroupCode=="24"  || $GroupCode=="25" )  //98 총괄,3 센터 24 수자원 25 상하수도
				{
					$InsaView=true;
					$InsaView=false;
				}else{
					$InsaView=false;
				}
			}else
			{
				$InsaView=false;
			}

			$this->assign('InsaView',$InsaView);

			//장종찬실장,권혁진부장만 -권부장요청 (근태분석)
			if($get_memberID=="M02210" || $get_memberID=="B20304" ||$get_memberID=="T03225" || $get_memberID=="TADMIN" || $get_memberID=="T08301" || $get_memberID=="B17305" || $get_memberID=="B20334" || $get_memberID=="M20330" || $get_memberID=="T02303" || $get_memberID=="M20328" || $get_memberID=="M21201")
			{
				if($GroupCode=="98" || $GroupCode=="3" || $GroupCode=="24"  || $GroupCode=="25")  //98 총괄,3 센터 24 수자원 25 상하수도
				{
					$ReportView=true;
					$ReportView=false;
				}else{
					$ReportView=false;
				}
			}else
			{
				$ReportView=false;
			}

			$this->assign('ReportView',$ReportView);

			//모든 부서 확인 김윤하 이사, 정명준 만 (근태분석)
			$ReportView2=false;
			if($get_memberID=="T03225" || $get_memberID=="TADMIN" || $get_memberID=="M20330" )
			{
				$ReportView2=true;
				$ReportView2=false;
			}

			$this->assign('ReportView2',$ReportView2);



			$GroupName=Code2Name(sprintf("%02d",$GroupCode), 'GroupCode', '0');

			$this->assign('query_data',$query_data);
			$this->assign('GroupCode',$GroupCode);
			$this->assign('GroupName',$GroupName);



			$this->display("intranet/common_contents/work_human/organization_chart_mvc.tpl");
		}

		//================================================================================
		// 조직도 페이지 Logic
		//================================================================================
		function OrganizationChartGraph()
		{

			global $db;
			global $sel_year,$sel_month,$sel_day,$GroupCode,$CompanyKind;

		//*****************************************************************************************************************************
		// 장헌산업 팀별 원형 그래프 Logic *****************************************************************************************	//*****************************************************************************************************************************
			if($CompanyKind=="JANG")
			{
				$query_data1 = array();

				$sql2="select GroupCode,count(*) num from member_tbl where  GroupCode<>'3' and GroupCode<>'5' and GroupCode<>'7' and GroupCode<>'20' and LeaveDate = '0000-00-00' and WorkPosition<>'9' and WorkPosition<>'2' and WorkPosition<>'8' and WorkPosition<>'3' group by GroupCode";
				$re2 = mysql_query($sql2,$db);
				while($re_row2 = mysql_fetch_array($re2)) {
					$GroupCode=$re_row2[GroupCode];

					if($GroupCode=="1")//임원
					{
						$GroupCode_1=$re_row2[num];
					}else if($GroupCode=="2")//경영지원부
					{
						$GroupCode_2=$re_row2[num];
					}else if($GroupCode=="4")//공사관리팀
					{
						$GroupCode_4=$re_row2[num];
					}else if($GroupCode=="6")//생산본부
					{
						$GroupCode_6=$re_row2[num];
					}else if($GroupCode=="8")//설계팀
					{
						$GroupCode_8=$re_row2[num];
					}else if($GroupCode=="9")//영업팀
					{
						$GroupCode_9=$re_row2[num];
					}else if($GroupCode=="10")//현장소장
					{
						$GroupCode_10=$re_row2[num];
					}
				}

				$Group_sum = $GroupCode_1+$GroupCode_2+$GroupCode_4+$GroupCode_6+$GroupCode_8+$GroupCode_9+$GroupCode_10;

				$something1 = array('category' => urlencode("사장단"),'column-1' => urlencode($GroupCode_1));
				array_push($query_data1,$something1);
				$something1 = array('category' => urlencode("경영지원"),'column-1' => urlencode($GroupCode_2));
				array_push($query_data1,$something1);
				$something1 = array('category' => urlencode("영업팀"),'column-1' => urlencode($GroupCode_9));
				array_push($query_data1,$something1);
				$something1 = array('category' => urlencode("설계팀"),'column-1' => urlencode($GroupCode_8));
				array_push($query_data1,$something1);
				$something1 = array('category' => urlencode("공사관리"),'column-1' => urlencode($GroupCode_4));
				array_push($query_data1,$something1);
				$something1 = array('category' => urlencode("현장소장"),'column-1' => urlencode($GroupCode_10));
				array_push($query_data1,$something1);
				$something1 = array('category' => urlencode("생산본부"),'column-1' => urlencode($GroupCode_6));
				array_push($query_data1,$something1);

				$jsondata1= urldecode(json_encode($query_data1));
			}


		//*****************************************************************************************************************************
		// 파일테크 팀별 원형 그래프 Logic *****************************************************************************************	//*****************************************************************************************************************************
			else if($CompanyKind=="PILE")
			{
				$query_data1 = array();

				$sql2="select GroupCode,count(*) num from member_tbl where LeaveDate = '0000-00-00' and WorkPosition='1' group by GroupCode";
				$re2 = mysql_query($sql2,$db);
				while($re_row2 = mysql_fetch_array($re2)) {
					$GroupCode=$re_row2[GroupCode];

					if($GroupCode=="1" )//사장단
					{
						$GroupCode_1=$re_row2[num];
					}else if($GroupCode=="2")//수주관리팀
					{
						$GroupCode_2=$re_row2[num];
					}else if($GroupCode=="3")//기술팀
					{
						$GroupCode_3=$re_row2[num];
					}
				}


				$Group_sum = $GroupCode_1+$GroupCode_2+$GroupCode_3;

				$something1 = array('category' => urlencode("사장단"),'column-1' => urlencode($GroupCode_1));
				array_push($query_data1,$something1);
				$something1 = array('category' => urlencode("수주관리팀"),'column-1' => urlencode($GroupCode_2));
				array_push($query_data1,$something1);
				$something1 = array('category' => urlencode("기술팀"),'column-1' => urlencode($GroupCode_3));
				array_push($query_data1,$something1);

				$jsondata1= urldecode(json_encode($query_data1));

			}
		//*****************************************************************************************************************************
		// 바론컨설턴트 팀별 원형 그래프 Logic *****************************************************************************************	//*****************************************************************************************************************************
			else if($CompanyKind=="HANM")
			{
				$query_data1 = array();

				//$sql2="select GroupCode,count(*) num from member_tbl where LeaveDate = '0000-00-00' and WorkPosition='1' group by GroupCode";
				$sql2="select * from
						(
							select GroupCode,count(*) num from member_tbl where LeaveDate = '0000-00-00' and WorkPosition in('1','3') group by GroupCode
						)a left join
						(
							  select * from systemconfig_tbl where SysKey='GroupCode'
						)b on a.GroupCode=b.Code order by b.orderno";


				$re2 = mysql_query($sql2,$db);

				
				while($re_row2 = mysql_fetch_array($re2)) {


					$GroupCode=$re_row2[GroupCode];
					$num=$re_row2[num];
					$Name=$re_row2[Name];

					if($GroupCode=="3") //기술개발센터
					{
						//$num=$num-3;
					}else if($GroupCode=="98") //총괄기획시
					{
						//$num=$num+3;
					}

					$something1 = array('category' => urlencode($Name),'column-1' => urlencode($num));
					array_push($query_data1,$something1);

					$Group_sum += $num;
				}
				
				$jsondata1= urldecode(json_encode($query_data1));
			}
		//*****************************************************************************************************************************
		// 바론컨설턴트 팀별 원형 그래프 Logic *****************************************************************************************	//*****************************************************************************************************************************
			else if($CompanyKind=="BARO")
			{
				$query_data1 = array();

				//$sql2="select GroupCode,count(*) num from member_tbl where LeaveDate = '0000-00-00' and WorkPosition='1' group by GroupCode";
				$sql2="select * from
						(
							select GroupCode,count(*) num from member_tbl where LeaveDate = '0000-00-00' and WorkPosition in('1','3') group by GroupCode
						)a left join
						(
							  select * from systemconfig_tbl where SysKey='GroupCode'
						)b on a.GroupCode=b.Code order by b.orderno";


				$re2 = mysql_query($sql2,$db);

				
				while($re_row2 = mysql_fetch_array($re2)) {


					$GroupCode=$re_row2[GroupCode];
					$num=$re_row2[num];
					$Name=$re_row2[Name];

					if($GroupCode=="3") //기술개발센터
					{
						//$num=$num-3;
					}else if($GroupCode=="98") //총괄기획시
					{
						//$num=$num+3;
					}

					$something1 = array('category' => urlencode($Name),'column-1' => urlencode($num));
					array_push($query_data1,$something1);

					$Group_sum += $num;
				}
				
				$jsondata1= urldecode(json_encode($query_data1));
			}

			//=팀별현황 그래프 자바스크립트 include==================================================//
			include "../../../Smarty/templates/intranet/organization_graph.tpl";
			//===========================================================================//

			//============================================================================
			// 직급별 현황 그래프 Logic
			//============================================================================

			$end_date=$YearMonth."-31";

		//*****************************************************************************************************************************
		// 장헌산업 직급별 그래프 SQL **********************************************************************************************	//*****************************************************************************************************************************
			if($CompanyKind=="JANG")
			{
			$sql="select RankCode,count(*) num from member_tbl where GroupCode<>'0' and GroupCode<>'3' and GroupCode<>'5' and GroupCode<>'7' and GroupCode<>'20' and LeaveDate = '0000-00-00' and WorkPosition<>'2' and WorkPosition<>'9' and WorkPosition<>'8' and WorkPosition<>'3' group by RankCode";
			}

		//*****************************************************************************************************************************
		// 파일테크 직급별 그래프 SQL **********************************************************************************************	//*****************************************************************************************************************************
			else if($CompanyKind=="PILE")
			{
				$sql="select RankCode,count(*) num from member_tbl where LeaveDate = '0000-00-00' and WorkPosition='1' group by RankCode";
			}
		//*****************************************************************************************************************************
		// 바론컨설턴트 직급별 그래프 SQL **********************************************************************************************	//*****************************************************************************************************************************

			else if($CompanyKind=="HANM")
			{
				$sql="select RankCode,count(*) num from member_tbl where LeaveDate = '0000-00-00' and WorkPosition='1' group by RankCode";
			}	
			else if($CompanyKind=="BARO")
			{
				$sql="select RankCode,count(*) num from member_tbl where LeaveDate = '0000-00-00' and WorkPosition='1' group by RankCode";
			}
				$re = mysql_query($sql,$db);

				while($re_row = mysql_fetch_array($re)) {
					$RankCode=$re_row[RankCode];

					if($RankCode=="C0")
					{
						$RankCode_C0=$re_row[num];
					}else if($RankCode=="C1")
					{
						$RankCode_C1=$re_row[num];
					}else if($RankCode=="C2")
					{
						$RankCode_C2=$re_row[num];
					}else if($RankCode=="C3")
					{
						$RankCode_C3=$re_row[num];
					}else if($RankCode=="C4")
					{
						$RankCode_C4=$re_row[num];
					}else if($RankCode=="C5")
					{
						$RankCode_C5=$re_row[num];
					}else if($RankCode=="C6")
					{
						$RankCode_C6=$re_row[num];
					}else if($RankCode=="C7")
					{
						$RankCode_C7=$re_row[num];
					}else if($RankCode=="C8")
					{
						$RankCode_C8=$re_row[num];
					}else if($RankCode=="E1")
					{
						$RankCode_E1=$re_row[num];
					}else if($RankCode=="E1A")
					{
						$RankCode_E1A=$re_row[num];
					}else if($RankCode=="E1B")
					{
						$RankCode_E1B=$re_row[num];
					}else if($RankCode=="E2")
					{
						$RankCode_E2=$re_row[num];
					}else if($RankCode=="E3")
					{
						$RankCode_E3=$re_row[num];
					}else if($RankCode=="E4")
					{
						$RankCode_E4=$re_row[num];
					}else if($RankCode=="E5")
					{
						$RankCode_E5=$re_row[num];
					}else if($RankCode=="E6")
					{
						$RankCode_E6=$re_row[num];
					}else if($RankCode=="E7")
					{
						$RankCode_E7=$re_row[num];
					}else if($RankCode=="E8")
					{
						$RankCode_E8=$re_row[num];
					}
				}

				$executive_sum=$RankCode_C0+$RankCode_C1+$RankCode_C2+$RankCode_C3+$RankCode_C4+$RankCode_C5;
				$headdepart_sum=$RankCode_E1+$RankCode_E1A+$RankCode_E1B;
				$staff_sum=$RankCode_E5+$RankCode_E6+$RankCode_E7+$RankCode_E8;
				$Rank_sum = $executive_sum+$RankCode_C6+$RankCode_C7+$RankCode_C8+$headdepart_sum+$RankCode_E2+$RankCode_E3+$RankCode_E4+$staff_sum;

				$this->assign('jsondata1',$jsondata1);

				$this->assign('executive_sum',$executive_sum);
				$this->assign('RankCode_C6',$RankCode_C6);
				$this->assign('RankCode_C7',$RankCode_C7);
				$this->assign('RankCode_C8',$RankCode_C8);
				$this->assign('headdepart_sum',$headdepart_sum);
				$this->assign('RankCode_E2',$RankCode_E2);
				$this->assign('RankCode_E3',$RankCode_E3);
				$this->assign('RankCode_E4',$RankCode_E4);
				$this->assign('staff_sum',$staff_sum);

				$this->assign('memberID',$_SESSION['memberID']);

				$this->display("intranet/common_contents/work_human/organization_chartgraph_mvc.tpl");
		}



		//============================================================================
		// 인사 정보 표시
		//============================================================================
		function OrganizationChartList2()
		{

			global $db;
			global $sel_year,$sel_month,$sel_day,$GroupCode,$CompanyKind;

			$query_data = array();

			$uyear = date("Y")+1;
			$last_day = date("t",mktime(0,0,0,date("m"),1,date("Y")));
			$last_day =$last_day +1;

			if($sel_year=="") $sel_year=date("Y");
			if($sel_month=="") $sel_month=date("m");
			if($sel_day=="") $sel_day=date("d");

			$this->assign('uyear',$uyear);
			$this->assign('last_day',$last_day);
			$this->assign('sel_year',$sel_year);
			$this->assign('sel_month',$sel_month);
			$this->assign('sel_day',$sel_day);

			$date=sprintf('%04d-%02d-%02d',$sel_year,$sel_month,$sel_day);

			$holy_sc = holy($date);

			if($GroupCode=="")
				$GroupCode=$_SESSION['SS_GroupCode'];


			//echo $GroupCode;
			if($GroupCode =="" || $GroupCode=="all")
			{
				$presql="select * from member_tbl where WorkPosition = 1 order by GroupCode,RankCode,JuminNo";
			}
			else if($GroupCode =="ceo_info")
			{
				if($CompanyKind=="JANG")
				{
					$presql="select * from member_tbl where MemberNo='J06401'";
				}
				else if($CompanyKind=="PILE")
				{
					$presql="select * from member_tbl where MemberNo='P06201'";
				}
				else if ($CompanyKind=="HANM")
				{
					$presql="select * from member_tbl where MemberNo='M02107'";
				}
				else if ($CompanyKind=="BARo")
				{
					$presql="select * from member_tbl where MemberNo='M02107'";
				}
			}
			else
			{

					if($GroupCode=="3") //기술개발센터
					{
						$presql="select * from member_tbl where WorkPosition = 1 and GroupCode='$GroupCode' and MemberNo not in ('T03203','T03225','B14306','J08305') order by GroupCode,RankCode,EntryDate"	;
					}else if($GroupCode=="98") //총괄기획시
					{
						$presql="select * from member_tbl where WorkPosition = 1 and GroupCode='$GroupCode' or MemberNo in ('T03203','T03225','B14306','J08305') order by RankCode,EntryDate"	;
					}else
					{
						$presql="select * from member_tbl where WorkPosition = 1 and GroupCode='$GroupCode' order by GroupCode,RankCode,JuminNo"	;
					}
			}
			$sql = "select a1.*,a2.Name as Position from
					(
						".$presql."
					)
						a1 left JOIN
					(
						select * from systemconfig_tbl where SysKey='PositionCode'
					)
						a2 on a1.RankCode = a2.code";


			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			$num=1;
			while($re_row = mysql_fetch_array($re))
			{
				$PhotoFile="../../../erpphoto/".$re_row[MemberNo].".jpg";
				$exist_file = file_exists("$PhotoFile");
				if($exist_file) {
					$re_row[Photo]=$PhotoFile;
				}else
				{
					$re_row[Photo]="../../image/sub_08_user.png";
				}

				$Mobile=$re_row[Mobile];
				if(strlen($Mobile) == 11)
					$Mobile=substr($Mobile,0,3)."-".substr($Mobile,3,4)."-".substr($Mobile,7);
				else if(strlen($Mobile) == 10)
					$Mobile=substr($Mobile,0,3)."-".substr($Mobile,3,3)."-".substr($Mobile,6);

				$re_row[Mobile]=$Mobile;


				array_push($query_data,$re_row);
			}

			//============================================================================
			// 팀선택 탭 관련 Logic
			//============================================================================


			$GroupName=Code2Name(sprintf("%02d",$GroupCode), 'GroupCode', '0');

			$this->assign('query_data',$query_data);
			$this->assign('GroupCode',$GroupCode);
			$this->assign('GroupName',$GroupName);

			$this->display("intranet/common_contents/work_human/organization_chart_mvc2.tpl");
		}




}

?>