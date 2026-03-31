<?php
if(!($ActionMode=="CheckVacation" or $ActionMode=="CheckOverWork" or $ActionMode=="CancelOverWork" or $open_type == 'package')){
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
}

	/***************************************
	* 전자결재문서 작성
	****************************************/

	include "../inc/dbcon.inc";
	include "../inc/function_mysql.php";
//	include "../inc/dbconForMystation.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../inc/function_intranet_v2.php";//181030 : 한맥ERP 프로젝트코드 작업 관련하여 추가 : by moon
	include "../../../person_mng/inc/vacationfunction.php";


	echo "FormNum".$FormNum."<br>";

	if($memberID <> "T03203"){
		include "../util/OracleClass.php";
	}


	extract($_POST);
	class DocumentLogic {
		var $smarty;
		var $oracle;
		function DocumentLogic($smarty)
		{
			global $memberID,$FormNum;
			if($memberID <> "T03203"){
				$this->oracle=new OracleClass($smarty);
			}

			echo "FormNum1".$FormNum."<br>";

			$this->smarty=$smarty;
		}



		//============================================================================
		// 전자결재 문서작성
		//============================================================================
		function InsertAction()
		{
			include "../inc/approval_function.php";
			global $db,$memberID;

			global $FormNum,$TmpArrCount;

			global $NewSN,$FormNum,$ProjectCode,$DocTitle,$AttchFile,$MemberNo,$Project_Name,$Project_Code,$GroupName,$WriterName;
			global $RT_Sanction_,$RT_SanctionState, $MemberInfo;
			global $RG_Code,$Security,$ConservationYear,$Account;
			global $Detail1,$Detail2,$Detail3,$Detail4,$Detail5,$Detail_5tmp;

			global $Position,$RG_Date,$RG_Code,$PG_Code,$PG_Date;
			global $mLabel,$mName,$mCode,$menu_cmd,$DocSN;
			global $Detail_1,$Detail_2,$Detail_3,$Detail_4,$Detail_5;

			global $Detail_4_0,$Detail_4_1,$Detail_4_2,$Detail_4_3,$Detail_4_4;

			global $userfile,$userfile_name,$userfile_size,$filename,$Addfile;
			global $DeviceChk,$doc_status;
			global $vacation_num,$AttchFile_1,$attachfile;
			global $Detail6,$Detail_6;
		}
		//============================================================================
		// 전자결재 문서보기
		//============================================================================
		function InsertPage()
		{
			include "../inc/approval_function.php";

			global $db,$memberID,$ActionMode,$DocTitle,$DocSN;
			global $FormNum,$End_index,$Receive_index,$Now_Step,$menu_cmd;
			global $Detail1,$Detail2,$Detail3,$Detail4,$Detail5,$CompanyKind,$doc_status;
			global $TempValue,$TempValue2,$TempValue3;
			global $Detail_1;
			global $report_type, $dateto, $dept, $seq, $satis,$targetKind;	//전표관련 변수

		}

		//============================================================================
		// 전자결재 수정
		//============================================================================
		function UpdateReadPage()
		{
			include "../inc/approval_function.php";

			global $db,$memberID,$ActionMode;
			global $FormNum,$End_index,$Receive_index,$Now_Step,$DocSN;
			global $RG_Code,$PG_Code,$menu_cmd;
			global $dbkey,$doc_status,$DocSN;
			global $Comment,$CompanyKind;

			global $printYN, $satis;
			global $sdate,$edate,$group_code,$send_group,$sub_group_code,$open_check,$selt,$targetKind,$open_type,$currentPage;
			global $Detail6,$Detail_6;

		}


		//============================================================================
		// 전자결재 Update Logic
		//============================================================================
		function UpdateAction()
		{

			include "../inc/approval_function.php";
			global $db,$memberID,$db01;

			global $FormNum,$TmpArrCount;

			global $NewSN,$FormNum,$ProjectCode,$DocTitle,$AttchFile,$MemberNo;
			global $RT_Sanction_,$RT_SanctionState,$RT_Sanction;
			global $RG_Code,$Security,$ConservationYear,$Account;
			global $Detail1,$Detail2,$Detail3,$Detail4,$Detail5;
			global $MemberInfo,$DocSN;

			global $Position,$ConservationYear,$RG_Date,$RG_Code,$PG_Code,$PG_Date;
			global $mLabel,$mName,$mCode;

			global $Detail_1,$Detail_2,$Detail_3,$Detail_4,$Detail_5;
			global $menu_cmd,$kind;
			global $Comment,$FinishMemberNo,$MemberNo;

			global $ExtNo,$Rank,$subId,$subName;
			global $AfterMember,$targetKind,$open_type;
			global $confirm_date_input;
			global $Detail6,$Detail_6;



			

		}


		//============================================================================
		// 전자결재 결재자 표시 관련 Logic
		//============================================================================
		function ApprovalName($ItemData)
		{
				include "../inc/approval_var.php";

				global	$db,$memberID;
				global	$FormNum,$doc_CodeORName,$RT_Sanction  ;
				global	$End_index,$Receive_index,$Now_Step;
				global  $RG_Code,$PG_Code;

				$MemberNoName=MemberNo2Name(substr($ItemData,0,6));
				$MemberNoRank=MemberNo2Rank(substr($ItemData,0,6));


				$TmpMember =split("-",$ItemData);
				if ($TmpMember[1] =="임원")
				{
					$StrFinish=strpos($RT_Sanction,"FINISH");
					if ($StrFinish === false) //FINISH 란 말이없으면 현재 기안부서
					{
						$Tmp_GroupCode=$RG_Code;
					}
					else //FINISH 란 말이없으면 현재 처리부서(경영지원부)
					{
						if($i < $Receive_index){
							$Tmp_GroupCode=$RG_Code;
						}
						else
						{
							$Tmp_GroupCode=$PG_Code;
						}
					}

					if ($TmpMember[0] != Group2Manager($Tmp_GroupCode) ) //부서장이 아니라면 대리결재 표시
					{

						$StrResign=strpos($ItemData,"대결");
						if ($StrResign === false) //대결이 없으면 대결-대결 중복방지(처음상신시는 부서장이 달라도 대결이라는 말이 안붙음
						{
							$MemberNoRank=MemberNo2Rank(substr($ItemData,0,6));
							$TmpArr[$i]=$TmpArr[$i];
							$Msg="";
						}

						$StrResign1=strpos($ItemData,"부서장-대결");
						if ($StrResign1 !== false) //대결이 없으면 대결-대결 중복방지(팀장,부서장 결재시 대결이란 말이 있으면
						{
							$MemberNoRank=MemberNo2Rank(substr($ItemData,0,6));
						}

					}
				}


			return $MemberNoName." ".$MemberNoRank.$Msg;
		}


		//============================================================================
		// 전자결재 결재현황 및 결재자 변경 Logic
		//============================================================================
		function ApprovalCheck($ItemData,$i)
		{
				include "../inc/approval_var.php";

				global	$db,$memberID;
				global	$FormNum,$DocSN,$doc_CodeORName,$RT_Sanction  ;
				global	$End_index,$Receive_index,$Now_Step;
				global	$doc_status;


				if($ItemData <> "")
				{
							if($Now_Step > $Receive_index) { //처리부서내 결재중인경우
									if($i >= $Receive_index) {
										if(Process_FinishCheck($DocSN,($i+1)) == "NO") {
											if ($memberID ==  substr($ItemData,0,6)) { //수신부서 결재자가 자신의 결재를 다른사람으로 변경못하게 처리
												$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
											}
											else
											{
												$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
											}
										} else {
												$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
										}
									} else {
										if(FinishCheck($DocSN,($i+1)) == "NO") {
											if ($memberID ==  substr($ItemData,0,6)) { //수신부서 결재자가 자신의 결재를 다른사람으로 변경못하게 처리
												$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
											}
											else
											{
												$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
											}
										} else {
												$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
										}
									}
							} else {  //결의부서내 결재중인경우
								if($i < $Receive_index) {
									if(FinishCheck($DocSN,($i+1)) == "NO") {
										if ($doc_status == $DOC_STATUS_APPROVE  && $memberID ==  substr($ItemData,0,6))  //결재자가 자신의 결재를 다른사람으로 변경못하게 처리
										{
											$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
										}
										elseif($doc_status == $DOC_STATUS_CREATE  && $memberID ==  substr($ItemData,0,6) && $i==0)  //처음기안자 담당항목이 있는경우
										{
											$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='기안자' title='$i' />";
										}
										else{
											if($FormNum=="BRF-9-2" || $FormNum=="HMF-9-2")
											{
												$msg="<div class=bt_63x23 style=margin-left:36px;><a href=# onClick=cmd_SanctionChange('{$i}');>변　경</a></div>";
											}else
											{
												$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange('{$i}');>변　경</a></div>";
											}
										}
									} else {
											$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
									}
								}
							}
				}else
				{

						if($Now_Step > $Receive_index) {
							if(($i+1) > $Now_Step) {
								if($i > $Receive_index) {
									$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange('{$i}');>지　정</a></div>";
								}
							} else {
								$msg="-"; //Skip
							}
						} else {

							if($i < $Receive_index) {
								if(($i+1) > $Now_Step) {
									if($FormNum=="BRF-9-2" || $FormNum=="HMF-9-2")
									{
										$msg="<div class=bt_63x23 style=margin-left:36px;><a href=# onClick=cmd_SanctionChange('{$i}');>지　정</a></div>";
									}else
									{
										$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange('{$i}');>지　정</a></div>";
									}

								} else {
									$msg="-"; //Skip

								}
							}
						}


				}

				return $msg;
		}



		function ApprovalCheck2($ItemData,$i)
		{
				include "../inc/approval_var.php";

				global	$db,$memberID;
				global	$FormNum,$DocSN,$doc_CodeORName,$RT_Sanction  ;
				global	$End_index,$Receive_index,$Now_Step;
				global	$doc_status;

				if($ItemData <> "")
				{
						if($i >= $Receive_index) {
							if(Process_FinishCheck($DocSN,($i+1)) == "NO") {
								if ($memberID ==  substr($ItemData,0,6)) { //수신부서 결재자가 자신의 결재를 다른사람으로 변경못하게 처리
									$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
								}
								else
								{
									$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
								}
							} else {
									$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
							}
						} else {
							if(FinishCheck($DocSN,($i+1)) == "NO") {
								if ($memberID ==  substr($ItemData,0,6)) { //수신부서 결재자가 자신의 결재를 다른사람으로 변경못하게 처리
									$msg="<input type=text size=5 readonly class='info_input01' style='text-align:center;' value='결재중'  />";
								}
								else
								{
									$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange2('{$i}');>변　경</a></div>";
								}
							} else {
									$msg="<input type=text size=8 readonly class='info_input01' style='text-align:center;' value='결재완료'  />";
							}
						}
				}else
				{
						$msg="<div class=bt_63x23 style=margin-left:19px;><a href=# onClick=cmd_SanctionChange('{$i}');>지　정</a></div>";
				}

				return $msg;
		}


		// 휴가계 잔여연차 계산 함수
		//============================================================================
		function NowVacation($MemberNo)
		{
			global	$db;
			$ThisYear=date("Y");
			$StartDay = $ThisYear."-01-01";
			$EndDay = $ThisYear."-12-31";

			$sql ="select a.MemberNo as MemberNo,a.korName as Name,b.Name as GroupName,a.Name as Position,a.EntryDate as EntryDate,a.vacation as vacation,a.Pasword as Pasword,a.RankCode as RankCode
			from
			(
				select * from
				(
					select * from member_tbl where MemberNo='$MemberNo'
				)a1 left JOIN
				(
					select * from systemconfig_tbl where SysKey='PositionCode'
				)a2 on a1.RankCode = a2.code

			) a left JOIN
			(
				select * from systemconfig_tbl where SysKey='GroupCode'
			)b on a.GroupCode = b.code";

			$re = mysql_query($sql,$db);
			$re_num = mysql_num_rows($re);
			while($re_row = mysql_fetch_array($re))
			{
					$EntryDate=$re_row[EntryDate];
					$vacation=$re_row[vacation];

			}

			//============================================================================
			// 휴가 전년이월
			//============================================================================

				$sql2 = "select * from diligence_tbl where MemberNo = '$MemberNo' and date like '%$ThisYear%'";

				$re2 = mysql_query($sql2,$db);
				$re_num2 = mysql_num_rows($re2);
				if($re_num2 > 0)
				{
					$rest_day = mysql_result($re2,0,"rest_day");
				}
				else
				{
					$rest_day = "&nbsp;";
				}

				if($rest_day > 0)  //전년월차 남아있으면 모두 0으로처리
				{
					$rest_day=0;
				}

			//============================================================================
			// 올해 생성년차
			//============================================================================

				$new_day=0;
				$EnterYear = substr($EntryDate,0,4);  //입사년도
				$EnterMonth = substr($EntryDate,5,2);  //입사월
				$EnterDay = substr($EntryDate,8,2);  //입사일


				if($EntryDate <"2017-05-30")
				{
						$JoinYear = $ThisYear - $EnterYear; //현제년-입사년
						if($JoinYear <= 0) //1년미만은 없음
						{
							$new_day = 0;
						}
						elseif($JoinYear == 1) //1년이상은 월별 차등지급
						{

							if($EnterMonth == "01"){$new_day = 15;}
							elseif($EnterMonth == "02"){$new_day = 14;}
							elseif($EnterMonth == "03"){$new_day = 13;}
							elseif($EnterMonth == "04"){$new_day = 11;}
							elseif($EnterMonth == "05"){$new_day = 10;}
							elseif($EnterMonth == "06"){$new_day = 9;}
							elseif($EnterMonth == "07"){$new_day = 7;}
							elseif($EnterMonth == "08"){$new_day = 6;}
							elseif($EnterMonth == "09"){$new_day = 5;}
							elseif($EnterMonth == "10"){$new_day = 3;}
							elseif($EnterMonth == "11"){$new_day = 2;}
							elseif($EnterMonth == "12"){$new_day = 0;}
						}
						else  //그외는 2년에 1일씩 증가
						{
							$remainder=$JoinYear % 2;
							if ($remainder == 0 )
							{
								$division=(int)($JoinYear/2);
								$new_day= $division-1+15;
							}
							else
							{
								$division=(int)($JoinYear/2);
								$new_day= $division+15-1;
							}
						}

						$new_day=$new_day+$vacation;

					//============================================================================
					// MY Vacation 사용휴가
					//============================================================================
						$spend_day=0;
						$sql_use = "select * from userstate_tbl where state = 1 and MemberNo = '$MemberNo' and start_time like '$ThisYear%' and end_time <>'0000-00-00'";
						$re_use = mysql_query($sql_use,$db);
						$re_num_use = mysql_num_rows($re_use);
						if($re_num_use > 0)
						{
							while($re_row_use = mysql_fetch_array($re_use))
							{
								if($re_row_use[start_time] >= $StartDay && $re_row_use[end_time] <= $EndDay)
								{
									$spend = calculate($re_row_use[start_time],$re_row_use[end_time],$re_row_use[note]);
								}
								elseif($re_row_use[start_time] < $StartDay)
								{
									if($re_row_use[end_time] > $EndDay)
									{
										$spend = calculate($StartDay,$EndDay,$re_row_use[note]);
									}
									else
									{
										$spend = calculate($StartDay,$re_row_use[end_time],$re_row_use[note]);
									}
								}
								else
								{
									$spend = calculate($re_row_use[start_time],$EndDay,$re_row_use[note]);
								}

								$spend_day = $spend_day + $spend;
							}
						}
					 $now_vacation=$rest_day+$new_day-$spend_day;
			}else
			{
					$this_year=$ThisYear;
					//입사일
					$enter_start=$this_year."-".$EnterMonth."-".$EnterDay;
					//오늘날짜
					$now_start=$this_year."-".date("m-d");

					if($EntryDate>$now_start)
					{
						$now_vacation="0"; //잔여연차
					}else
					{
						if($enter_start > $now_start)
						{
							$this_year2=$this_year-1;
							$year_start=$this_year2."-".$EnterMonth."-".$EnterDay;

						}else
						{
							$year_start=$enter_start;
						}

						$year_end = date("Y-m-d", strtotime("+1 year", strtotime($year_start)));
						$year_end = date("Y-m-d", strtotime("-1 day", strtotime($year_end)));

						$ThisDay=$this_year."-".date("m-d");
						if($ThisDay < $year_end )
						{
							$ThisDay=$year_end;
						}
						$Today=date("Y-m-d");


						$arryear=getDiffdate($Today, $EntryDate);
						$yeargap=$arryear[yeargap];

						if($yeargap==0)  //1년미만
						{
							$ThisDay=$this_year."-".date("m-d");
						}

						$tmpData=getAnnualLeave($ThisDay,$EntryDate,$MemberNo);

						$now_vacation=$tmpData[4]; //잔여연차
					}

					$StartDay= $year_start;
					$EndDay= $year_end;

			}


				list($use_day,$use_hour,$remain_hour)=UsedAnnualDayPeriod($StartDay,$EndDay,$MemberNo);

				$now_vacation=$now_vacation-$use_day;
				if($use_hour>0)
				{
					$now_vacation=$now_vacation-1;
					$now_vacation=$now_vacation."일".$remain_hour."시간";
				}else if($use_hour==0)
				{
					$remain_hour="";
					$now_vacation=$now_vacation."일";
				}
			 return ($now_vacation);
		}

		function Passcheck()
		{
			global	$db,$memberID,$menu_cmd,$CMD_TYPE;

			$user_ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];   /// remote ip 저장
			$sql = "select * from member_tbl where MemberNo = '$memberID'";
			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			$re_row = mysql_fetch_array($re);
			$db_pw = $re_row[Pasword];

			$this->smarty->assign('db_pw',$db_pw);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('menu_cmd',$menu_cmd);
			$this->smarty->assign('CMD_TYPE',$CMD_TYPE);

			$this->smarty->display("intranet/common_contents/work_approval/pass_check_mvc.tpl");
		}

		function ParkingPrint()
		{
			include "../inc/approval_function.php";
			include "../inc/approval_var.php";

			global	$db,$memberID,$menu_cmd,$CMD_TYPE,$dbkey;

			$DocSN = $dbkey;
			$azSQL="select * from SanctionDoc_tbl where DocSN='$DocSN'";
			//echo $azSQL."<br>";
			$res_sanction = mysql_query($azSQL,$db);
			$DocTitle = mysql_result($res_sanction,0,"DocTitle");
			$Detail4 = mysql_result($res_sanction,0,"Detail4");
			$Dt4=split("/n",$Detail4);
			$Detail5 = mysql_result($res_sanction,0,"Detail5");
			$Dt5=split("/n",$Detail5);

		if($Dt4[4]=="회사차량")
			{
			$azSQL1="select * from systemconfig_tbl where SysKey='bizcarno' and Code='$Dt5[3]'";
			//echo $azSQL1."<br>";

			$res_device = mysql_query($azSQL1,$db);
			$CarName = mysql_result($res_device,0,"Name");
			}
			$CheckSign_member =	CheckSign($DocSN,"담당",$SANCTION_CODE2);

			$this->smarty->assign('db_pw',$db_pw);
			$this->smarty->assign('memberID',$memberID);
			$this->smarty->assign('menu_cmd',$menu_cmd);
			$this->smarty->assign('CMD_TYPE',$CMD_TYPE);
			$this->smarty->assign('DocTitle',$DocTitle);
			$this->smarty->assign('Detail4',$Detail4);
			$this->smarty->assign('Dt4',$Dt4);
			$this->smarty->assign('Detail5',$Detail5);
			$this->smarty->assign('Dt5',$Dt5);
			$this->smarty->assign('CheckSign_member',$CheckSign_member);

			$this->smarty->display("intranet/common_contents/work_approval/Parking_Print_mvc.tpl");
		}

		function SortKeyCombination($m_MemberNo)
		{
			global	$db;

			$_SortKeyCombination = "000E0";
			$azSQL = "SELECT * FROM member_tbl WHERE MemberNo='$m_MemberNo'";
			//echo $azSQL;
			$res_sortkey = mysql_query($azSQL,$db);
			if(mysql_num_rows($res_sortkey) > 0) {
				$GroupCode = mysql_result($res_sortkey,0,"GroupCode");
				$RankCode = mysql_result($res_sortkey,0,"RankCode");
				$_SortKeyCombination = sprintf("%03d",$GroupCode) . $RankCode;
			}
			return $_SortKeyCombination;
		}



		function fnGetData()
		{
			global $db,$workdate;
			include "../inc/approval_function.php";

			if(holycheck($workdate)=="weekday")
			{
				$msg="<input type='radio' id='Detail5' name='Detail5' value='1' checked onclick='txtview(1);'><span class='ml_0'>연장근무</span> ";
				$msg.="<input type='radio' id='Detail5' name='Detail5' value='2' onclick='txtview(2);'><span class='ml_0'>휴일근무</span>";
			}else
			{
				$msg="<input type='radio' id='Detail5' name='Detail5' value='1' onclick='txtview(1);'><span class='ml_0'>연장근무</span> ";
				$msg.="<input type='radio' id='Detail5' name='Detail5' value='2' checked onclick='txtview(2);'><span class='ml_0'>휴일근무</span> (하루씩 1올려주시기 바랍니다)";
			}

			echo  $msg;

		}




			//============================================================================
			// 전자문서 파일삭제
			//============================================================================
			function DeleteFile()
			{

				include "../inc/approval_function.php";

				global $db;
				global $memberID,$current_id,$FormNum,$dbkey,$doc_status;


				$azSQL="select * from SanctionDoc_tbl where DocSN='$dbkey'";
				//echo $azSQL."<br>";
				$res_sanction = mysql_query($azSQL,$db);
				$Addfile = mysql_result($res_sanction,0,"Addfile");

				$path ="./../../../intranet_file/documents/".$FormNum."/";
				$path_is ="./../../../intranet_file/documents/".$FormNum;

				if($FormNum=="HMF-6-1" or $FormNum=="HMF-6-2")
				{

					$Addfile_Arr=split("/n",$Addfile);
					$Addfile_Arr2=split("/",$Addfile_Arr[$current_id]);
					$del_path = $path.$Addfile_Arr2[2];
					$Resultfile_org = file_exists("$del_path");

					if($Resultfile_org)	{
						$re=unlink("$del_path");
					}

					unset($Addfile_Arr[$current_id]);
					for($i=0; $i<count($Addfile_Arr); $i++)
					{
						if($Addfile_Arr[$i]<>"")
						{
							$re_Addfile= $re_Addfile . str_replace("'","",$Addfile_Arr[$i])."/n";
						}
					}
				}
				else
				{
					$Addfile_Arr2=split("/",$Addfile);
					$del_path = $path.$Addfile_Arr2[2];
					$Resultfile_org = file_exists("$del_path");
					if($Resultfile_org)	{
						$re=unlink("$del_path");
					}
					$re_Addfile="";
				}



				$sql = "update SanctionDoc_tbl set Addfile ='$re_Addfile' where DocSN='$dbkey'";
				//echo $sql;
				mysql_query($sql,$db);

				$this->smarty->assign('target','self');
				$this->smarty->assign('MoveURL',"document_controller.php?ActionMode=update_page&docno=$FormNum&dbkey=$dbkey&doc_status=EDIT&memberID=$memberID&CompanyKind=HANM");
				$this->smarty->display("intranet/move_page.tpl");
			}

			//============================================================================
			// 전자 전표 결재 문서 접수 전달
			//============================================================================
			function doc_toss(){
				include "../inc/approval_function.php";

				extract($_REQUEST);
				global $db, $memberID, $FormNum;

				$dbinsert = "yes";
				//$dbinsert = "no";
				if($fun_type == "list"){
					$azSQL = "
						SELECT
							ReceiveMember
							, (SELECT korName FROM member_tbl WHERE MemberNo LIKE ReceiveMember) AS korName
						FROM approval_tbl
						WHERE FormName LIKE 'HMF-5-%'
						GROUP BY ReceiveMember
						ORDER BY FormName
					";

					echo "[''";
					$re = mysql_query($azSQL,$db);
					while($re_row = mysql_fetch_array($re)) {
						echo ",['".$re_row[ReceiveMember]."', '".$re_row[korName]."']";
					}
					echo "]";
				}else{
					$azSQL = "
						SELECT
							DocSN
						FROM approval_account_tbl
						WHERE DocSN LIKE '$DocSN'
					";
					$re = mysql_query($azSQL,$db);
					if(mysql_num_rows($re) > 0){
						$sql = "UPDATE approval_account_tbl SET MemberNo = '$TargetMemberNo' WHERE DocSN LIKE '$DocSN'";
					}else{
						$sql = "INSERT INTO approval_account_tbl (DocSN, MemberNo, OriginMemberNo) VALUES ('$DocSN', '$TargetMemberNo', '$memberID')";
					}
					mysql_query($sql,$db);

					$this->smarty->assign('target','account_reload');
					$this->smarty->display("intranet/move_page.tpl");
				}
			}



			//============================================================================
			// 발신문서 발행일 변경
			//============================================================================
			function doc_date()
			{
				$dbinsert="yes";
				//$dbinsert="no";

				include "../inc/approval_function.php";
				global $db,$memberID;

				global $DocSN;
				global $FormNum,$tab_index,$currentPage;
				global $PG_Y,$PG_M,$PG_D,$Detail_2;

				for($i=0; $i<=1; $i++) {
					$Detail2= $Detail2 . str_replace("'","",$Detail_2[$i])."/n";
				}

				$azSQL = "update SanctionDoc_tbl set Detail2='".$Detail2.$PG_Y."-".$PG_M."-".$PG_D."' where DocSN='$DocSN'";

				if($dbinsert =="yes"){
					$result=mysql_query($azSQL,$db);


					$this->smarty->assign('target','reload');
					$this->smarty->assign('MoveURL',"approval_controller.php?Category=MyListView&FormNum=".$FormNum."&tab_index=".$tab_index."&memberID=".$memberID."&currentPage=".$currentPage);
					$this->smarty->display("intranet/move_page.tpl");

					//$this->smarty->assign('target','no');
					//$this->smarty->display("intranet/move_page.tpl");

				}else{
					echo "[save--- ".$azSQL."<br>";


				}
			}

			//============================================================================
			// 발신공문 출력
			//============================================================================
			function doc_print(){
				include "../inc/approval_function.php";
				include "../inc/approval_var.php";

				global	$db,$memberID,$menu_cmd,$CMD_TYPE,$dbkey,$FormNum,$printYN;

				$DocSN = $dbkey;
				$azSQL="select * from SanctionDoc_tbl where DocSN='$DocSN'";
				//echo $azSQL."<br>";
				$res_sanction = mysql_query($azSQL,$db);

				$DocTitle = mysql_result($res_sanction,0,"DocTitle");
				$Detail1 = mysql_result($res_sanction,0,"Detail1");
				$Detail2 = mysql_result($res_sanction,0,"Detail2");
				$Detail3 = mysql_result($res_sanction,0,"Detail3");
				$Detail4 = mysql_result($res_sanction,0,"Detail4");
				$Detail5 = mysql_result($res_sanction,0,"Detail5");
				$ProjectCode = mysql_result($res_sanction,0,"ProjectCode");
				$PG_Date = mysql_result($res_sanction,0,"PG_Date");
				$RT_Sanction = mysql_result($res_sanction,0,"RT_Sanction");
				$RT_SanctionState = mysql_result($res_sanction,0,"RT_SanctionState");
				$MemberNo = mysql_result($res_sanction,0,"MemberNo");
				$Account = mysql_result($res_sanction,0,"Account");



					$Dt2=split("/n",$Detail2);
					if($Dt2[2] == ""){
						$Dt2[2] = $PG_Date;
					}
					$this->smarty->assign('Dt2',$Dt2);
					$Dt3=split("/n",$Detail3);
					$this->smarty->assign('Dt3',$Dt3);
					$Dt4 = split("/n",$Detail4);
					$this->smarty->assign('Dt4',$Dt4);
					$Coop = split("/",$ProjectCode);
					$RT_Sanction_member_origin = split(":",$RT_Sanction);
					for($i=0; ; $i++){
						if( $RT_Sanction_member_origin[$i] == "RECEIVE"){
							break;
						}
						$temp_member = split("-",$RT_Sanction_member_origin[$i]);
						//$RT_Sanction_member[$i] = $temp_member[1]." ".MemberNo2Name($temp_member[0]);
						$duty_name = MemberNo2Rank($temp_member[0]);
						if($Coop[$i] == '0' and ($duty_name == "본부장" or $duty_name == "부서장" or $duty_name == "원장" or $duty_name == "실장" or $duty_name == "회장" or $duty_name == "대표이사")){
							$RT_Sanction_member[$i] = $duty_name." : ".MemberNo2Name($temp_member[0]);
						}
					}
					if(strpos($RT_SanctionState, "FINISH") !== false and $Detail5 == "1"){
						$printYN = 'N';
					}else{
						$printYN = 'Y';
					}


				$this->smarty->assign('db_pw',$db_pw);
				$this->smarty->assign('memberID',$MemberNo);
				if($Account=="")
				{
					$this->smarty->assign('MemberName',MemberNo2Name($MemberNo));
				}else
				{
					$this->smarty->assign('MemberName',$Account);
				}
				$this->smarty->assign('menu_cmd',$menu_cmd);
				$this->smarty->assign('CMD_TYPE',$CMD_TYPE);
				$this->smarty->assign('printYN',$printYN);

				$this->smarty->assign('Sanction_data',$Sanction_data);
				$this->smarty->assign('GroupName',$GroupName);
				$this->smarty->assign('DocSN',$DocSN);
				$this->smarty->assign('DocTitle',$DocTitle);
				$this->smarty->assign('Detail1',$Detail1);
				$this->smarty->assign('Detail2',$Detail2);
				$this->smarty->assign('Detail3',$Detail3);
				$this->smarty->assign('Detail4',$Detail4);
				$this->smarty->assign('Detail5',$Detail5);
				$this->smarty->assign('PG_Date',str_replace("-", " . ", $PG_Date));
				$this->smarty->assign('RT_Sanction_member',$RT_Sanction_member);

				if($FormNum=="BRF-6-1")  //발신공문
				{
					$this->smarty->display("intranet/common_contents/work_approval/BRF-6-1_print.tpl");
				}else
				{
					$this->smarty->display("intranet/common_contents/work_approval/HMF-6-1_print.tpl");
				}
			}



			//============================================================================
			// 발신공문 출력
			//============================================================================
			function doc_print_test(){
				include "../inc/approval_function.php";
				include "../inc/approval_var.php";

				global	$db,$memberID,$menu_cmd,$CMD_TYPE,$dbkey,$FormNum,$printYN;

				$DocSN = $dbkey;
				$azSQL="select * from SanctionDoc_tbl where DocSN='$DocSN'";
				//echo $azSQL."<br>";
				$res_sanction = mysql_query($azSQL,$db);

				$DocTitle = mysql_result($res_sanction,0,"DocTitle");
				$Detail1 = mysql_result($res_sanction,0,"Detail1");
				$Detail2 = mysql_result($res_sanction,0,"Detail2");
				$Detail3 = mysql_result($res_sanction,0,"Detail3");
				$Detail4 = mysql_result($res_sanction,0,"Detail4");
				$Detail5 = mysql_result($res_sanction,0,"Detail5");
				$ProjectCode = mysql_result($res_sanction,0,"ProjectCode");
				$PG_Date = mysql_result($res_sanction,0,"PG_Date");
				$RT_Sanction = mysql_result($res_sanction,0,"RT_Sanction");
				$RT_SanctionState = mysql_result($res_sanction,0,"RT_SanctionState");
				$MemberNo = mysql_result($res_sanction,0,"MemberNo");
				$Account = mysql_result($res_sanction,0,"Account");



					$Dt2=split("/n",$Detail2);
					if($Dt2[2] == ""){
						$Dt2[2] = $PG_Date;
					}
					$this->smarty->assign('Dt2',$Dt2);
					$Dt3=split("/n",$Detail3);
					$this->smarty->assign('Dt3',$Dt3);
					$Dt4 = split("/n",$Detail4);
					$this->smarty->assign('Dt4',$Dt4);
					$Coop = split("/",$ProjectCode);
					$RT_Sanction_member_origin = split(":",$RT_Sanction);
					for($i=0; ; $i++){
						if( $RT_Sanction_member_origin[$i] == "RECEIVE"){
							break;
						}
						$temp_member = split("-",$RT_Sanction_member_origin[$i]);
						//$RT_Sanction_member[$i] = $temp_member[1]." ".MemberNo2Name($temp_member[0]);
						$duty_name = MemberNo2Rank($temp_member[0]);
						if($Coop[$i] == '0' and ($duty_name == "본부장" or $duty_name == "부서장" or $duty_name == "원장" or $duty_name == "실장" or $duty_name == "회장" or $duty_name == "대표이사")){
							$RT_Sanction_member[$i] = $duty_name." : ".MemberNo2Name($temp_member[0]);
						}
					}
					if(strpos($RT_SanctionState, "FINISH") !== false and $Detail5 == "1"){
						$printYN = 'N';
					}else{
						$printYN = 'Y';
					}


				$this->smarty->assign('db_pw',$db_pw);
				$this->smarty->assign('memberID',$MemberNo);
				if($Account=="")
				{
					$this->smarty->assign('MemberName',MemberNo2Name($MemberNo));
				}else
				{
					$this->smarty->assign('MemberName',$Account);
				}
				$this->smarty->assign('menu_cmd',$menu_cmd);
				$this->smarty->assign('CMD_TYPE',$CMD_TYPE);
				$this->smarty->assign('printYN',$printYN);

				$this->smarty->assign('Sanction_data',$Sanction_data);
				$this->smarty->assign('GroupName',$GroupName);
				$this->smarty->assign('DocSN',$DocSN);
				$this->smarty->assign('DocTitle',$DocTitle);
				$this->smarty->assign('Detail1',$Detail1);
				$this->smarty->assign('Detail2',$Detail2);
				$this->smarty->assign('Detail3',$Detail3);
				$this->smarty->assign('Detail4',$Detail4);
				$this->smarty->assign('Detail5',$Detail5);
				$this->smarty->assign('PG_Date',str_replace("-", " . ", $PG_Date));
				$this->smarty->assign('RT_Sanction_member',$RT_Sanction_member);


				$this->smarty->display("intranet/common_contents/work_approval/HMF-6-1_print_test.tpl");

			}

}
//END============================================================================
?>