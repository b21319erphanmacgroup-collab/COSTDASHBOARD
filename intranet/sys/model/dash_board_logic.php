<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
	include "../../sys/inc/dbcon.inc";
	include "../../sys/inc/function_intranet.php";
	include "../../sys/inc/function_timework.php";
	include "../util/HanamcPageControl.php";
	include "../../../SmartyConfig.php";

	require_once($SmartyClassPath);
?>
<?
	extract($_GET);

		$MemberNo	=	"";	//사원번호
	if($_SESSION['SS_memberID']!=""){
		/* SET SESSION ----------------------- */
		$MemberNo   =   $_SESSION['SS_memberID'];		//사원번호
		$memberID	=   $_SESSION['SS_memberID'];		//사원번호

		$CompanyKind=   $_SESSION['SS_CompanyKind'];//장헌산업(JANG),파일테크(PILE),바론컨설턴트(HANM)
		$korName	=	$_SESSION['SS_korName'];	//한글이름
		$RankCode	=	$_SESSION['SS_RankCode'];	//직급코드
		if($GroupCode=="")
		{
			$GroupCode	=	$_SESSION['SS_GroupCode'];	//부서코드
		}
		$SortKey	=	$_SESSION['SS_SortKey'];	//직급+부서코드
		$EntryDate	=	$_SESSION['SS_EntryDate'];	//입사일자
		$position	=	$_SESSION['SS_position'];	//직위명
		$GroupName	=	$_SESSION['SS_GroupName'];	//부서명
	}else if($_SESSION['CK_memberID']!=""){				//쿠키값 유무확인
		/* SET COOKIE --------------------------------- */
		$MemberNo   =   $_SESSION['CK_memberID'];	//사원번호
		$memberID	=   $_SESSION['CK_memberID'];	//사원번호

		$CompanyKind=	$_SESSION['CK_CompanyKind'];	//회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		$korName	=	$_SESSION['CK_korName'];		//한글이름
		$RankCode	=	$_SESSION['CK_RankCode'];	//직급코드
		if($GroupCode=="")
		{
			$GroupCode	=	$_SESSION['CK_GroupCode'];	//부서코드
		}
		$SortKey	=	$_SESSION['CK_SortKey'];		//직급+부서코드
		$EntryDate	=	$_SESSION['CK_EntryDate'];	//입사일자
		$position	=	$_SESSION['CK_position'];	//직위명
		$GroupName	=	$_SESSION['CK_GroupName'];	//부서명
	}else{
		/* ----------------------------------- */
		//$memberID	=	$_GET['memberID'];
		$memberID = ($_GET['memberID']==""?$_POST['memberID']:$_GET['memberID']);

		$MemberNo	=	$memberID;
		$MemberNo	=	strtoupper($MemberNo);
		/* ----------------------------------- */
		require('../../sys/popup/setInfo.php');
		/* ----------------------------------- */
	}//if End
	/* ----------------------------------- */

?>
<?
	class DashBoard extends Smarty {


		function DashBoard()
		{

			global $SmartyClass_TemplateDir;
			global $SmartyClass_CompileDir;
			global $SmartyClass_ConfigDir;
			global $SmartyClass_CacheDir;
			global $ProjectCode,$bridgeno,$n_num,$Item_no,$id,$mode;


			$this->Smarty();
			$this->template_dir	=$SmartyClass_TemplateDir;
			$this->compile_dir	=$SmartyClass_CompileDir;
			$this->config_dir	=$SmartyClass_ConfigDir;

		}


		function DashBoardList()
		{
			global $db;
			global $sel_year,$sel_month,$sel_day,$GroupCode,$sub_index,$memberID;

			$query_data = array();
			$query_data2 = array();

			$uyear = date("Y")+1;

			$this->assign('auth_ceo',true);
			if($sel_year=="") $sel_year=date("Y");
			if($sel_month=="") $sel_month=date("m");
			if($sel_day=="") $sel_day=date("d");

			$last_day0 = date("t",mktime(0,0,0,$sel_month,1,date("Y")));
			$last_day =$last_day0 +1;


			$this->assign('uyear',$uyear);
			$this->assign('last_day',$last_day);
			$this->assign('sel_year',$sel_year);
			$this->assign('sel_month',$sel_month);
			$this->assign('sel_day',$sel_day);

			if($sel_day>$last_day0){$sel_day="01";}
			$date=sprintf('%04d-%02d-%02d',$sel_year,$sel_month,$sel_day);

			$holy_sc = holy($date);
			if($GroupCode=="")
				$GroupCode=$_SESSION['MyGroupCode'];

			$sql = "select * from
					(
						select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and GroupCode='$GroupCode' order by GroupCode,IF(RealRankCode > 'C7',RankCode,IF(RealRankCode='',RankCode, IFNULL(RealRankCode,RankCode))),binary(korName),EntryDate
					)a1 left JOIN
					(
						select * from systemconfig_tbl where SysKey='PositionCode'
					)a2 on a1.RankCode = a2.code";

			//echo $sql."<Br>";

			$re = mysql_query($sql,$db);
			$num=1;
			while($re_row = mysql_fetch_array($re)){
				
				$MemberNo=$re_row[MemberNo];
				$RankCode=$re_row[RankCode];
				$EntryDate=$re_row[EntryDate];
				array_push($query_data,$re_row);
				$num++;
			}

			if($sub_index == "")
				$sub_index=$GroupCode;

			$tab_index=$GroupCode;

			$GroupList = array();

			$Group_Row="0";
			$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code in ('98','03') order by orderno  desc";

			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{

				array_push($GroupList,$re_row2);
				$gCode[$Group_Row] = $re_row2[Code];
				$gName[$Group_Row] = $re_row2[Name];

				$Group_Row++;
			}

			if($Group_Row % 9 >0 )
			{
				$Group_Row_num= ceil($Group_Row/9)*9;
			}
			for($k=$Group_Row;$k<$Group_Row_num;$k++) {
  			   $re_row2[Name]="";;
				array_push($GroupList,$re_row2);
			}

			/* For 코드사용분기 Start  ******************* */

			$this->assign('CK_CompanyKind',$CompanyKind);
			$this->assign('memberID',$memberID);

			$this->assign('tab_index',$tab_index);
			$this->assign('sub_index',$sub_index);
			$this->assign('query_data',$query_data);
			$this->assign('query_data2',$query_data2);

			$this->assign('GroupCode',$GroupCode);

			$this->assign('gCode',$gCode);
			$this->assign('gName',$gName);
			$this->assign('Group_Row',$Group_Row);
			$this->assign('GroupList',$GroupList);
			$this->assign('SearchDate',$searchDate);

			$this->display("intranet/common_contents/work_dash/dash_board_mvc.tpl");
		}





		
		function DashBoardReport()
		{
			global $db;
			global $sel_year,$sel_month,$sel_day,$GroupCode,$sub_index,$memberID;
			global $CompanyKind;


			$PersonAuthority=new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'임원'))
				$this->assign('auth_ceo',true);
			else
				$this->assign('auth_ceo',false);


			if($PersonAuthority->GetInfo($memberID,'부서'))
				$this->assign('auth_depart',true);
			else
				$this->assign('auth_depart',false);




			$uyear = date("Y")+1;



			if($sel_year=="") $sel_year=date("Y");
			if($sel_month=="") $sel_month=date("m");

			$last_day = date("t",mktime(0,0,0,$sel_month,1,date("Y")));


			$this->assign('uyear',$uyear);
			$this->assign('last_day',$last_day);
			$this->assign('sel_year',$sel_year);
			$this->assign('sel_month',$sel_month);

			$SearchDate=sprintf('%04d-%02d',$sel_year,$sel_month);
			$NowDate=date("Y-m");

			$date_start=$SearchDate."-01";
			$date_end=$SearchDate."-".$last_day;

			$StartDay = $sel_year."-01-01";
			$EndDay = $sel_year."-12-31";


			for($i=1;$i <= $last_day;$i++)
			{
				$chkday = sprintf('%04d-%02d-%02d',$sel_year,$sel_month,$i);
				$holychk=holy($chkday );
				if($holychk=="weekday")
				{
					$workcount++;
				}

			}


			if($GroupCode=="")
				$GroupCode=$_SESSION['MyGroupCode'];



				$query_data = array();

				if($GroupCode=="003")
				{
					$sql="select a3.MemberNo,a3.Name as RankName,b3.Name as GroupName,a3.korName,a3.RankCode,a3.WorkPosition,a3.EntryDate  from
					(
						select *  from
						(
							select * from member_tbl where GroupCode='$GroupCode' and  (WorkPosition = '1' or WorkPosition = '4') order by IF(RealRankCode > 'C7',RankCode,IF(RealRankCode='',RankCode, IFNULL(RealRankCode,RankCode))),binary(korName),EntryDate
						)a2 left JOIN
						(
							select * from systemconfig_tbl where SysKey='PositionCode'
						)b2 on a2.RankCode = b2.code
					)a3 left JOIN
					(
						select * from systemconfig_tbl where SysKey='GroupCode'
					)b3 on a3.GroupCode=b3.Code	";
				}else
				{
					$sql="select a3.MemberNo,a3.Name as RankName,b3.Name as GroupName,a3.korName,a3.RankCode,a3.WorkPosition,a3.EntryDate  from
					(
						select *  from
						(
							select * from member_tbl where GroupCode='$GroupCode' and  (WorkPosition = '1' or WorkPosition = '4') order by RankCode,EntryDate
						)a2 left JOIN
						(
							select * from systemconfig_tbl where SysKey='PositionCode'
						)b2 on a2.RankCode = b2.code
					)a3 left JOIN
					(
						select * from systemconfig_tbl where SysKey='GroupCode'
					)b3 on a3.GroupCode=b3.Code	";
				}

				//echo $sql."<br>";
				$re = mysql_query($sql,$db);
				while($re_row = mysql_fetch_array($re))
				{

					$MemberNo=$re_row[MemberNo];
					$EntryDate=$re_row[EntryDate];

					$re_row[late]=0;

					$pos=strpos(substr($re_row[RankCode],0,1), 'C');
					if ($pos !== false) //임원
						$ceo=true;
					else
						$ceo=false;



				//연장근무,평일근무,휴일근무  한맥,파일 modify=1 ----------------------------------------------------------
					$latecount=0;
					$overwork=0;
					$weekwork=0;
					$holywork=0;

					$realoverwork=0;
					$realholywork=0;

					$sql_over="select * from view_dallyproject_tbl where  MemberNo='$MemberNo' and EntryTime like '$SearchDate%'";
					//echo $sql_over."<br>";
					$re_over = mysql_query($sql_over,$db);
					while($re_over_row = mysql_fetch_array($re_over))
					{

						$modify=$re_over_row[modify];
						$holy_sc = holy(substr($re_over_row[EntryTime],0,10));
						$l_time = substr($re_over_row[LeaveTime],11,5);
						$o_time = substr($re_over_row[OverTime],11,5);
						$e_time = substr($re_over_row[EntryTime],11,5);


						$overdate= substr($re_over_row[OverTime],5,2)."-".substr($re_over_row[OverTime],8,2);
						$leavedate= substr($re_over_row[LeaveTime],5,2)."-".substr($re_over_row[LeaveTime],8,2);


						if($holy_sc == "weekday")  //// 평일 일 때  OverTime 야근시작시간  LeaveTime퇴근시간
						{

								$weekwork++;

								if(!$ceo)
								{
									//지각체크
									if(substr($re_over_row[EntryTime],11,5) > "09:00")  /// 9시 넘을때
									{
											/*
											$u_date = substr($re_over_row[EntryTime],0,10);

											$sql90="select * from worker_tardy_tbl where memberno='$MemberNo' and (s_date <= '$u_date' and e_date >= '$u_date')";
											//echo $sql."<br>";
											$re90=mysql_query($sql90,$db);
											$re_num90 = mysql_num_rows($re90);
											if($re_num90 == 0)
											{
												$latecount++;

											}else
											{
												while($re_row90=mysql_fetch_array($re90))
												{
													//$start_time=$re_row90[tardy_h].$re_row90[tardy_m];

													$start_time=sprintf("%02d",$re_row90[tardy_h]).":".sprintf("%02d",$re_row90[tardy_m]);
													//echo substr($re_over_row[EntryTime],11,5)."--".$start_time."<br>";
													if(substr($re_over_row[EntryTime],11,5) > $start_time)
													{
														//echo "지각";
														$latecount++;
													}
												}
											}


											$sql0 = "select * from view_userstate_tbl where MemberNo = '$MemberNo' and (start_time <= '$u_date' and end_time >= '$u_date')";
											$re0 = mysql_query($sql0,$db);
											$re0_num = mysql_num_rows($re0);
											if($re0_num > 0)  /// 값이 있을 때
											{
												$u_note = mysql_result($re0,0,"note");
												$u_note = str_replace(" ","",$u_note);
												if(strpos($u_note,"오후반차") !== false)
												{
													$latecount++;
												}
											}
											*/

										}
								}


								//야근시작시간이 있을때
								if($re_over_row[OverTime] != "0000-00-00 00:00:00")
								{
									//야근시작시간이 19:00 이전이면 19:00 부터야근시작시간
									if($o_time < $weekday_start)
									{
											if(substr($re_over_row[EntryTime],0,10) < '2018-06-21')
											{
												$o_time=$weekday_start;
											}else
											{
												$o_time=$o_time;
											}
									}else
									{
											$o_time=$o_time;
									}

									$sl_time = strtotime($l_time);
									$so_time = strtotime($o_time);
									$ottime = sec_time00($sl_time - $so_time);
									$ottime_tmp= $sl_time - $so_time;

									if ($overdate != "00-00")  ////다음날새벽에 끝나는 경우야근처리
									{
										if ($leavedate > $overdate) //다음날새벽에 끝나는 경우야근처리
										{
											if($CompanyKind=="PILE" || $CompanyKind=="HANM" || $CompanyKind=="BARO" ){
												if($modify=="1")
													$overwork++;
											}else
											{
												$overwork++;
											}

											$realoverwork++;
										}
										else
										{
											//최소근무시간 2시간이상이면 야근표시
											if($ottime >= $weekday_min)
											{
												if($CompanyKind=="PILE" || $CompanyKind=="HANM" || $CompanyKind=="BARO" ){
													if($modify=="1")
														$overwork++;
												}else
												{
													$overwork++;
												}

												$realoverwork++;
											}
										}
									}
								}
						}
						elseif($holy_sc == "holyday") ////휴일 일 때
						{

								if($CompanyKind=="PILE" || $CompanyKind=="HANM" || $CompanyKind=="BARO" ){
									if($modify=="1")
										$holywork++;
								}else
								{
									$holywork++;
								}

								$realholywork++;
						}

					}




					//휴가,훈련,기타(보건,출산휴가),대기,경조----------------------------------------------------------
					$onevacation=0;
					$amvacation=0;
					$pmvacation=0;
					$halfvacation=0;
					$etccount=0;
					$back_start_time="";

					$sql_va = "select * from view_userstate_tbl where MemberNo = '$MemberNo' and (state = 1 or state = 5 or state = 7 or state = 8 or state = 10) and end_time >= '$date_start' and start_time <= '$date_end'";
					//echo $sql_va."<br>";

					$re_va = mysql_query($sql_va,$db);

					if(mysql_num_rows($re_va)> 0)
					{
						while($re_va_row = mysql_fetch_array($re_va))
						{

							$state = $re_va_row[state];

							if($state =="1") //휴가
							{



								$u_note = $re_va_row[note];
								$u_note = str_replace(" ","",$u_note);

								if($re_va_row[start_time] < $date_start) { $re_va_row[start_time] = $date_start; }
								if($re_va_row[end_time] > $date_end) { $re_va_row[end_time] = $date_end; }


								//연차카운트
								if(strpos($u_note,"반차") === false)
								{
									$count = calculate($re_va_row[start_time],$re_va_row[end_time],$re_va_row[note]);
									$onevacation+=$count;
								}else  //반차 카운트
								{
									$halfcount = calculatehalf($re_va_row[start_time],$re_va_row[end_time]);
									if($back_start_time==$re_va_row[start_time])  //같은날 오전반차,오후반차쓴경우 연차로 처리
									{
										$onevacation++;
										$halfvacation=$halfvacation-1;
									}else{
										$halfvacation+=$halfcount*0.5;
									}
								}

								$back_start_time=$re_va_row[start_time];

							}else
							{
								$etccount = calculate($re_va_row[start_time],$re_va_row[end_time],$re_va_row[note]);

							}


						}
					}



					if( $workcount < ($weekwork+$onevacation+$etccount))  //중복data 제거
					{
						$workdiff=($weekwork+$onevacation+$etccount)-$workcount;
						$weekwork=$weekwork-$workdiff;
					}

					//근무 daillyproject
					$re_row[overwork] = $overwork;   //연장
					$re_row[holywork] = $holywork;   //휴일

					$re_row[weekwork] = $weekwork-$halfvacation;  //평일

					$re_row[realoverwork] = $realoverwork;  //연장(전자결재)
					$re_row[realholywork] = $realholywork;	//휴일(전자결재)

					//휴가,usrstatus
					$re_row[vacation_count] = $onevacation+$halfvacation;
					$re_row[etccount] = $etccount;

					$re_row[workcount] = $weekwork+$onevacation+$etccount;

					//지각



				//연차----------------------------------------------------------

					if($ceo)  //임원은 연차업음
					{
						$re_row[vacation]="-";
						$re_row[late] ="-";
					}
					else
					{

						$re_row[late] = $latecount;

						$re_row[chk]="#000000";
						if($NowDate <> $SearchDate)
						{
							if( $workcount >($weekwork+$onevacation+$etccount) )
								$re_row[chk]="red";
						}

						// MY Vacation 전년이월----------------------------------------------------------
							$sql2 = "select * from diligence_tbl where MemberNo = '$MemberNo' and date like '%$sel_year%'";

							$re2 = mysql_query($sql2,$db);
							$re_num2 = mysql_num_rows($re2);
							if($re_num2 > 0){
								$rest_day = mysql_result($re2,0,"rest_day");

								if($rest_day > 0)  //전년월차 남아있으면 모두 0으로처리
									$rest_day=0;

								$rest_day = $rest_day + (double)mysql_result($re2,0,"spend_day");
							}else{
								$rest_day = "0";
							}


						// MY Vacation 연차생성  -임원은 표시안함   //J07202(김회성),T02303(신현우) +1

							$new_day=0;
							$EnterYear = substr($EntryDate,0,4);  //입사년도
							$EnterMonth = substr($EntryDate,5,2);  //입사월

							$JoinYear = $sel_year - $EnterYear; //현제년-입사년

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


							$new_day=$new_day+$re_row[vacation];


							// MY Vacation 연차

							$spend_day=0;
							$sql_use = "select * from view_userstate_tbl where state = 1 and MemberNo = '$MemberNo' and start_time like '$sel_year%' and end_time <>'0000-00-00'";
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
											$spend = calculate($StartDay,$EndDay,$re_row_use[note]);
										else
											$spend = calculate($StartDay,$re_row_use[end_time],$re_row_use[note]);
									}
									else
									{
										$spend = calculate($re_row_use[start_time],$EndDay,$re_row_use[note]);
									}


									$spend_day = $spend_day + $spend;
								}
							}

							$re_row[vacation]=$rest_day+$new_day-$spend_day;

							if($re_row[vacation]>100)
								$re_row[vacation]="입사일확인";
					}
							array_push($query_data,$re_row);
				}


			$this->assign('query_data',$query_data);

			if($sub_index == "")
				$sub_index=$GroupCode;

			$tab_index=$GroupCode;

			$GroupList = array();

			$Group_Row="0";
			if($CompanyKind=="JANG")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'07' and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="PILE")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="HANM")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' and Code<>'28' order by orderno  asc";
			else if ($CompanyKind=="BARO")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' and Code<>'28' order by orderno  asc";




			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
				array_push($GroupList,$re_row2);
				$gCode[$Group_Row] = $re_row2[Code];
				$gName[$Group_Row] = $re_row2[Name];

				$Group_Row++;
			}
			if($Group_Row >9)
			{
				if($Group_Row % 9 >0 )
				{
					$Group_Row_num= ceil($Group_Row/9)*9;
				}
				for($k=$Group_Row;$k<$Group_Row_num;$k++) {
				   $re_row2[Name]="";;
					array_push($GroupList,$re_row2);
				}
			}




			//해당월 지각자명단
			$query_data3 = array();
			$GroupCode = sprintf("%02d",$GroupCode);

			$sql3="select a1.EntryTime,a1.EntryTime2,a1.LeaveTime2,a1.MemberNo,a1.EntryJob,b1.korName ,b1.RankName ,b1.GroupName,a1.EntryPCode from
			(

					select DATE_FORMAT(EntryTime, '%Y-%m-%d') as EntryTime,DATE_FORMAT(EntryTime, '%H:%i') as EntryTime2,DATE_FORMAT(LeaveTime, '%H:%i') as LeaveTime2,MemberNo,EntryPCode,EntryJob  from view_dallyproject_tbl  where  EntryTime like '%$SearchDate%'  and dayofweek(EntryTime) between  2 and 6   and substring(EntryTime,12,8) >= '10:00:00' and right(SortKey,2) > 'C8' and substring(SortKey,2,2)='$GroupCode' order by EntryTime

			)a1
			left join
			(
				select a3.MemberNo,a3.korName,a3.Name as RankName,b3.Name as GroupName from
				(
					select *  from
					(
						select * from member_tbl
					)a2 left JOIN
					(
						select * from systemconfig_tbl where SysKey='PositionCode'
					)b2 on a2.RankCode = b2.code
				)a3 left JOIN
				(
					select * from systemconfig_tbl where SysKey='GroupCode'
				)b3 on a3.GroupCode=b3.Code

			)b1
			on a1.MemberNo = b1.MemberNo";

			//echo $sql3."<br>";

			$re3 = mysql_query($sql3,$db);
			$num=1;
			while($re_row3 = mysql_fetch_array($re3))
			{

					$EntryDate = substr($re_row3[EntryTime],0,10);
					$sql4 = "select * from view_userstate_tbl where (start_time <= '$EntryDate' and end_time >= '$EntryDate') and MemberNo = '$re_row3[MemberNo]'";
					//echo $sql4."<br>";
					$re4 = mysql_query($sql4,$db);
					$re4_num = mysql_num_rows($re4);
					if(mysql_num_rows($re4) == 0)
					{
						if(!holy2($EntryDate))
						{
								array_push($query_data3,$re_row3);
						}
					}

			}






			/* For 코드사용분기 Start  ******************* */
			/* 장헌산업(JANG),파일테크(PILE),바론컨설턴트(HANM)*/
			$this->assign('CompanyKind',$CompanyKind);
			$this->assign('workcount',$workcount);
			$this->assign('memberID',$memberID);

			$this->assign('tab_index',$tab_index);
			$this->assign('sub_index',$sub_index);
			$this->assign('query_data',$query_data);
			$this->assign('query_data3',$query_data3);

			$this->assign('GroupCode',$GroupCode);

			$this->assign('gCode',$gCode);
			$this->assign('gName',$gName);
			$this->assign('Group_Row',$Group_Row);
			$this->assign('GroupList',$GroupList);

			$this->display("intranet/common_contents/work_Dash/Dash_report_mvc.tpl");
		}

}

// 끝
//==================================
?>