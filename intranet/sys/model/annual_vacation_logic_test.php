<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
	/***************************************
	* 연차이력
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH
	****************************************/


	include "../../sys/inc/dbcon.inc";
	include "../../sys/inc/function_intranet.php";
	include "../util/HanamcPageControl.php";
	include "../../../SmartyConfig.php";
	include "../../../person_mng/inc/vacationfunction.php";
	include "../../../person_mng/inc/vacationfunction_v3.php";

	extract($_GET);
	require_once($SmartyClassPath);

	class VacationBoard extends Smarty {
		function VacationBoard()
		{
			global $SmartyClass_TemplateDir;
			global $SmartyClass_CompileDir;
			global $SmartyClass_ConfigDir;
			global $SmartyClass_CacheDir;
			global $ProjectCode,$bridgeno,$n_num,$Item_no,$id,$mode,$sub_index;

			$this->Smarty();
			$this->template_dir	=$SmartyClass_TemplateDir;
			$this->compile_dir	=$SmartyClass_CompileDir;
			$this->config_dir	=$SmartyClass_ConfigDir;
		}

		//============================================================================
		// 전년,생성년차,지각 조회
		//============================================================================
		function VacationBoardList()
		{
			global $db,$memberID;
			global $sel_year,$GroupCode;

			/*
			if($_SESSION['auth_ceo'])//임원
				$this->assign('auth_ceo',true);
			else
				$this->assign('auth_ceo',false);


			if($_SESSION['auth_depart'])//부서장
				$this->assign('auth_depart',true);
			else
				$this->assign('auth_depart',false);


			if($_SESSION['auth_person_admin'])//인사B
				$this->assign('auth_person',true);
			else
				$this->assign('auth_person',false);
			*/

			$PersonAuthority=new PersonAuthority();
			if($PersonAuthority->GetInfo($memberID,'임원'))
				$this->assign('auth_ceo',true);
			else
				$this->assign('auth_ceo',false);


			if($PersonAuthority->GetInfo($memberID,'부서'))
				$this->assign('auth_depart',true);
			else
				$this->assign('auth_depart',false);

			if($PersonAuthority->GetInfo($memberID,'인사B'))
				$this->assign('auth_person',true);
			else
				$this->assign('auth_person',false);


			$query_data = array();

			$uyear = date("Y")+1;
			$last_day = date("t",mktime(0,0,0,date("m"),1,date("Y")));
			$last_day =$last_day +1;

			if($sel_year=="") $sel_year=date("Y");

			$this->assign('uyear',$uyear);
			$this->assign('sel_year',$sel_year);


			$ThisYear=$sel_year;
			$StartDay = $ThisYear."-01-01";
			$EndDay = $ThisYear."-12-31";
			$NowMonth = sprintf('%04d-%02d',date("Y"),date("m"));

			if($GroupCode==""){
				$GroupCode=$_SESSION['MyGroupCode'];
			}

			if($GroupCode=="all")
			{
				$presql="select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and (RankCode > 'C8' and RankCode < 'E8') order by GroupCode,RankCode,MemberNo ";
			}else
			{
				if($GroupCode=="31")  //감리부는 모두 보여줌
				{
					$presql="select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and GroupCode='$GroupCode' order by GroupCode,RankCode,MemberNo ";
				}else if($GroupCode=="3" || $GroupCode=="98")  //센터,총괄
				{
					$presql="select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and (RealRankCode > 'C8' and RealRankCode < 'E8') and GroupCode='$GroupCode' order by IF(RealRankCode > 'C7',RankCode,IF(RealRankCode='',RankCode, IFNULL(RealRankCode,RankCode))),binary(korName)";
				}else
				{
					$presql="select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and (RankCode > 'C8' and RankCode < 'E8') and GroupCode='$GroupCode' order by GroupCode,RankCode,EntryDate,MemberNo ";
				}


			}

			//$presql="select * from member_tbl where MemberNo='M17208'";

			//$sql ="select a.MemberNo as MemberNo,a.korName as Name,b.Name as GroupName,a.Name as Position,a.EntryDate as EntryDate,a.Pasword as Pasword ,a.vacation as vacation
			$sql ="select a.MemberNo as MemberNo,a.korName as Name,b.Name as GroupName,a.Name as Position,a.EntryDate as EntryDate,a.Pasword as Pasword ,c.vacationplus as vacation 
			from
			(
				select * from
				("
					.$presql."
				)a1 
			left JOIN ( select * from systemconfig_tbl where SysKey='PositionCode')a2 on a1.RankCode = a2.code ) a 
			left JOIN ( select * from systemconfig_tbl where SysKey='GroupCode')b on a.GroupCode = b.code 
			left JOIN ( select * from vacation_set where YEAR='$ThisYear') c on a.MemberNo=c.MemberNo";

			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			$re_num = mysql_num_rows($re);
			while($re_row = mysql_fetch_array($re))
			{
				$MemberNo=$re_row[MemberNo];
				$EntryDate=$re_row[EntryDate];
				//============================================================================
				// MY Vacation 전년이월
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
						$rest_day = "0";
					}

					if($rest_day > 0)  //전년월차 남아있으면 모두 0으로처리
					{
						$rest_day=0;
					}

					$re_row[rest_day]=$rest_day;

				//============================================================================
				// MY Vacation 연차생성  -임원은 표시안함   //J07202(김회성),T02303(신현우) +1
				//============================================================================
					$new_day=0;
					$EnterYear = substr($EntryDate,0,4);  //입사년도
					$EnterMonth = substr($EntryDate,5,2);  //입사월
					$EnterDay = substr($EntryDate,8,2);  //입사일


					if($EntryDate <"2017-05-30")
					{

							$JoinYear = $ThisYear - $EnterYear; //현제년-입사년

							$StartDay = $ThisYear."-01-01";
							$EndDay   = $ThisYear."-12-31";


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
							$re_row[new_day]=$new_day+$re_row[vacation];

							$re_row[cal_type]="0";
							$re_row[cal_name]="회계일";
							$spend_day=0;


						//============================================================================
						// MY Vacation 결근표시
						//============================================================================
						//$absence_num=0;
						/*
							$sql = "select * from userstate_tbl where state = 13 and MemberNo = '$MemberNo' and start_time like '$ThisYear%'";
							$re_no = mysql_query($sql,$db);
							$absence_num = mysql_num_rows($re_no);
							$re_row[absence_num]=$absence_num;
						*/
						//============================================================================
						// MY Vacation 사용휴가
						//============================================================================
							$spend_day=0;
							//$sql_use = "select * from userstate_tbl where (state = 1 or state = 18) and MemberNo = '$MemberNo' and start_time like '$ThisYear%' and end_time <>'0000-00-00'";
							$sql_use = "select * from userstate_tbl where state in ( 1, 18, 30, 31 ) and MemberNo = '$MemberNo' and start_time like '$ThisYear%' and end_time <>'0000-00-00'";
							$spend_hour = 0;
							$re_use = mysql_query($sql_use,$db);
							$re_num_use = mysql_num_rows($re_use);
							if($re_num_use > 0)
							{

									while($re_row_use = mysql_fetch_array($re_use))
									{
										//if($re_row_use[state]=="1")
										if($re_row_use[state]=="1" or $re_row_use[state]=="30" or $re_row_use[state]=="31")
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
										}else
										{
											$spend_hour+=$re_row_use[sub_code];
										}

									}
							}

							$re_row[use_day]=$spend_day;

							$re_row[rest_day]=$re_row[rest_day]*8;
							$re_row[new_day]=$re_row[new_day]*8;
							$re_row[new_day_sum]=$re_row[rest_day]+$re_row[new_day];
							$re_row[use_day]=$re_row[use_day]*8+$spend_hour;
							$re_row[remaind_day]=$re_row[new_day_sum]-$re_row[use_day];

							//--일 시간 으로 변환-----
							$re_row[rest_day2]=hourtodatehour($re_row[rest_day]);
							$re_row[new_day2]=hourtodatehour($re_row[new_day]);
							$re_row[new_day_sum2]=hourtodatehour($re_row[new_day_sum]);
							$re_row[use_day2]=hourtodatehour($re_row[use_day]);
							$re_row[remaind_day2]=hourtodatehour($re_row[remaind_day]);
							//--일 시간 으로 변환-----

							$re_row[vac_type]="yeardate";

					}else
					{
								//============================================================================
								// MY Vacation 결근표시
								//============================================================================
								//$absence_num=0;
								/*
								$sql = "select * from userstate_tbl where state = 13 and MemberNo = '$MemberNo' and start_time like '$ThisYear%'";
								$re_no = mysql_query($sql,$db);
								$absence_num = mysql_num_rows($re_no);
								$re_row[absence_num]=$absence_num;
								*/

								$EnterYear = substr($EntryDate,0,4);  //입사년도
								$EnterMonth = substr($EntryDate,5,2);  //입사월
								$EnterDay = substr($EntryDate,8,2);  //입사일

								$this_year=$ThisYear;

								//입사일
								$enter_start=$this_year."-".$EnterMonth."-".$EnterDay;
								//오늘날짜
								$now_start=$this_year."-".date("m-d");

								if($EntryDate>$now_start)
								{
									$re_row[rest_day]=""; //이월연차
									$re_row[new_day]="";  //생성연차
									$re_row[new_day_sum]="";  //연차합계
									$re_row[spend_day]="";  //사용연차
									$re_row[use_day]="";  //사용연차
									$re_row[remaind_day]=""; //잔여연차
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
//3333333333333333
									$arryear=getDiffdate_v3($Today, $EntryDate);
									$yeargap=$arryear[yeargap];

									if($yeargap==0)  //1년미만
									{
										$ThisDay=$this_year."-".date("m-d");
									}
									
									$vacationplus = $re_row[vacation];
									//$tmpData=getAnnualLeave($ThisDay,$EntryDate,$MemberNo);
									//$tmpData=getAnnualLeaveNew2($ThisDay,$EntryDate,$MemberNo);
									$tmpData=getAnnualLeaveNew2_v3($ThisDay,$EntryDate,$MemberNo,$vacationplus,$sel_year);
									$chkfirst=$sel_year-$EnterYear;
echo $MemberNo."/";

print_R($tmpData);
echo "</br>";
									/*
									$re_row[rest_day]=$tmpData[0]; //이월연차
									$re_row[new_day]=$tmpData[1];  //생성연차
									$re_row[new_day_sum]=$tmpData[2];  //연차합계
									$re_row[use_day]=$tmpData[3];  //사용연차
									$re_row[remaind_day]=$tmpData[4]; //잔여연차

									//일 시간변환
									$re_row[rest_day2]=$tmpData[5]; //이월연차
									$re_row[new_day2]=$tmpData[6];  //생성연차
									$re_row[new_day_sum2]=$tmpData[7];  //연차합계
									$re_row[use_day2]=$tmpData[8];  //사용연차
									$re_row[remaind_day2]=$tmpData[9]; //잔여연차
									*/

									//$re_row[rest_day]=$tmpData[0]; //이월연차시간					
									$re_row[rest_day]=$tmpData[21]; //이월연차시간					
									$re_row[use_day]=$tmpData[3];  //사용연차
									//일 시간변환
									//$re_row[rest_day2]=$tmpData[5]; //이월연차
									$re_row[rest_day2]=$tmpData[17]; //이월연차


									//if($yeargap==0){  //1년미만
									if($chkfirst=="0"){
										$re_row[new_day]=$tmpData[11];  //생성연차
										$re_row[new_day_sum]=$tmpData[11];  //연차합계
										$re_row[new_day2]=$tmpData[12];  //생성연차
										$re_row[new_day_sum2]=$tmpData[12];  //연차합계
										$re_row[remaind_day2]=$tmpData[13]; //잔여연차
										$re_row[remaind_day]=$tmpData[14]; //잔여연차
									}elseif($yeargap==0){
										$re_row[new_day]=$tmpData[11];  //생성연차
										$re_row[new_day_sum]=$tmpData[11];  //연차합계
										$re_row[new_day2]=$tmpData[12];  //생성연차
										$re_row[new_day_sum2]=$tmpData[12];  //연차합계
										$re_row[remaind_day2]=$tmpData[13]; //잔여연차
										$re_row[remaind_day]=$tmpData[14]; //잔여연차
									}else{
										//$re_row[new_day]=$tmpData[1];  //생성연차										
										//$re_row[new_day2]=$tmpData[6];  //생성연차
										//$re_row[new_day2]=$tmpData[16];  //생성연차
										$re_row[new_day2]=$tmpData[18];  //생성연차
										$re_row[new_day]=$tmpData[22];  //생성연차_숫자
										
										//$re_row[new_day_sum2]=$tmpData[7];  //연차합계
										$re_row[new_day_sum2]=$tmpData[19];  //연차합계
										$re_row[new_day_sum]=$tmpData[23];  //연차합계_숫자
										//$re_row[remaind_day2]=$tmpData[9]; //잔여연차
										$re_row[remaind_day2]=$tmpData[20]; //잔여연차
										//$re_row[remaind_day]=$tmpData[4]; //잔여연차
										$re_row[remaind_day]=$tmpData[24]; //잔여연차
									}
									$re_row[use_day2]=$tmpData[8];  //사용연차
								}


								$re_row[vac_type]="enterdate";

								$re_row[cal_type]="1";
								$re_row[cal_name]="입사일";

								$StartDay= $year_start;
								$EndDay= $year_end;
					}


						$use_day=0;
						$use_hour=0;
						$remain_hour=0;


						list($use_day,$use_hour,$remain_hour)=UsedAnnualDayPeriod($StartDay,$EndDay,$MemberNo);
						/*
						echo "StartDay".$StartDay."<br>";
						echo "EndDay".$EndDay."<br>";

						echo "use_day".$use_day."<br>";
						echo "use_hour".$use_hour."<br>";
						echo "remain_hour".$remain_hour."<br>";

						echo "sum_day".$sum_day."<br>";
						echo "spend_day".$spend_day."<br>";
						echo "remain".$remain."<br>";
						echo "-------------------------------<Br>";
						*/
//echo "use_hour".$use_hour."<br>";
						/*
						$re_row[spend_day]=$re_row[spend_day]+$use_day;
						$re_row[remaind_day]=$re_row[remaind_day]-$use_day;
						if($use_hour==4)
						{

							$re_row[spend_day]=$re_row[spend_day]+0.5;
							$re_row[remaind_day]=$re_row[remaind_day]-0.5;
							$re_row[use_hour]="";
							$use_hour="";
							$remain_hour="";
						}else if($use_hour>0)
						{
							$re_row[remaind_day]=$re_row[remaind_day]-1;
						}else if($use_hour==0)
						{
							$remain_hour="0";
						}

						$re_row[use_hour]=$use_hour;
						$re_row[remain_hour]=$remain_hour;
						*/
					array_push($query_data,$re_row);
				}


			$month_alert=false;


			//============================================================================
			// 전년이월
			//============================================================================
			$query_lastyear = "select * from diligence_tbl where date like '%$ThisYear%'";
			$result_lastyear = mysql_query($query_lastyear,$db);
			$lastyear = mysql_num_rows($result_lastyear);
			if($lastyear == 0) {
				$year_alert=true;
			}else
			{
				$year_alert=false;
			}

			if($sub_index == "")
				$sub_index=$GroupCode;

			$tab_index=$GroupCode;

			$GroupList = array();
			$Group_Row="0";


			//============================================================================
			// 표시그룹
			//============================================================================

			$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode' and Code<>'01' and Code<>'07' and Code<>'99' and  Code<>'28'  order by orderno asc";

			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
				array_push($GroupList,$re_row2);

				$gCode[$Group_Row] = $re_row2[Code];
				$gName[$Group_Row] = $re_row2[Name];

				$Group_Row++;
			}

			if($Group_Row % 8 >0 )
			{
				$Group_Row_num= ceil($Group_Row/8)*8;
			}
			for($k=$Group_Row;$k<$Group_Row_num;$k++) {
  			   $re_row2[Name]="";;
				array_push($GroupList,$re_row2);
			}

			$this->assign('tab_index',$tab_index);
			$this->assign('sub_index',$sub_index);
			$this->assign('query_data',$query_data);
			$this->assign('GroupCode',$GroupCode);
			$this->assign('month_alert',$month_alert);
			$this->assign('year_alert',$year_alert);
			$this->assign('gCode',$gCode);
			$this->assign('gName',$gName);
			$this->assign('Group_Row',$Group_Row);
			$this->assign('GroupList',$GroupList);

			$this->display("intranet/common_contents/work_vacation/annual_vacation_mvc_test.tpl");
		}





		//============================================================================
		//개인별 연차 상세내역
		//============================================================================
		function VacationDetailList()
		{
			global $db,$memberID;
			global $sel_year,$SearchID,$vac_type,$EntryDate;

			$query_data = array();

			//echo "vac_type=".$vac_type."<br>";
			if($vac_type=="enterdate")
			{


				$this_year=$sel_year;
				$EnterYear = substr($EntryDate,0,4);  //입사년도
				$EnterMonth = substr($EntryDate,5,2);  //입사월
				$EnterDay = substr($EntryDate,8,2);  //입사일


				//입사일
				$enter_start=$this_year."-".$EnterMonth."-".$EnterDay;
				//오늘날짜
				$now_start=$this_year."-".date("m-d");

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

				$arryear=getDiffdate($Today, $EntryDate);
				$yeargap=$arryear[yeargap];

				if($yeargap==0)  //1년미만
				{
					$ThisDay=$this_year."-".date("m-d");
				}

				if($sel_year==date("Y") )   //올해 연차사용계획서 미리 받아두어서 계산하기
				{
					//$year_end=date("Y")."-12-31";
				}

			//echo "start=".$year_start."~".$year_end."<br>";




				$sql2 = "select * from member_tbl l where MemberNo = '$SearchID'";
				$re2 = mysql_query($sql2,$db);
				if(mysql_num_rows($re2) > 0)
				{
			        $SearchName=mysql_result($re2,0,"KorName");
				}

			}else
			{
				$year_start = $sel_year."-01-01";
				$year_end = $sel_year."-12-31";
			}


			$sql = "select a.*,b.KorName from
			(
				select * from userstate_tbl where MemberNo = '$SearchID' and (state = 1 or state = 30 or state = 31) and end_time >= '$year_start' and start_time <= '$year_end'
			) a left join
			(
				select * from member_tbl
			)b on a.memberNo=b.MemberNo order by a.start_time";

			//echo $sql."<br>";

			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{

				if($re_row[start_time] < $year_start) { $re_row[start_time] = $year_start; }
				if($re_row[end_time] > $year_end) { $re_row[end_time] = $year_end; }

				if($re_row[state] == 1) {
					$spend = calculate($re_row[start_time],$re_row[end_time],$re_row[note]);
				}else if( $re_row[state] == 30 or $re_row[state] == 31 ) { $spend = 0.5; }

				$re_row[spend]=	$spend. " 일";
				$spend_sum+=$spend;

				array_push($query_data,$re_row);
				$SearchName =$re_row[KorName];
			}

			$sql2 = "select * from userstate_tbl where MemberNo = '$SearchID' and state = 18 and end_time >= '$year_start' and start_time <= '$year_end'";
			//echo $sql2."<br>";
			$re2 = mysql_query($sql2,$db);
			while($re_row2 = mysql_fetch_array($re2))
			{
				$speen_hour+=$re_row2[sub_code];
				$re_row2[spend]=$re_row2[sub_code]." 시간";
				array_push($query_data,$re_row2);
			}


			list($use_day,$use_hour)=UsedAnnualDay($speen_hour);
			$spend_sum=$spend_sum+$use_day;

			if($use_hour==0){
				$total=$spend_sum."일";
			/*
			}else if($use_hour==4){
				$spend_sum=$spend_sum+0.5;
				$total=$spend_sum."일";
				$use_hour=0;
			}else if($use_hour>4 && $use_hour<8) {
				$spend_sum=$spend_sum+0.5;
				$total=$spend_sum."일 ". (8-$use_hour)."시간";

			}else{
				$total=$spend_sum."일 ".$use_hour."시간";
			}
			*/
			}else{
				$total = floor( ( $spend_sum*8 + $use_hour ) / 8 )."일 ".( ( $spend_sum*8 + $use_hour ) % 8)."시간";
			}

			$this->assign('total',$total);

			$this->assign('query_data',$query_data);
			$this->assign('sel_year',$sel_year);
			$this->assign('SearchName',$SearchName);
			$this->assign('year_start',$year_start);
			$this->assign('year_end',$year_end);

			$this->display("intranet/common_contents/work_vacation/annual_vacation_detail.tpl");
		}
}
?>



