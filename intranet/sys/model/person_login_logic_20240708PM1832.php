<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php

	/***************************************
	* 근태이력 상세
	* ------------------------------------
	* 2014-12-16 : 파일정리: SUK KYH
	****************************************/

	include "../../sys/inc/dbcon.inc";
	include "../../sys/inc/function_intranet.php";
	include "../../sys/inc/function_timework.php";
	include "../../../SmartyConfig.php";

	include "../../sys/inc/getNeedDate.php";      //로직에 사용되는 PHP시간&날짜 정의

	extract($_GET);
	require_once($SmartyClassPath);

	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드

	class PersonLoginBoard extends Smarty {

		function PersonLoginBoard()
		{
			global $SmartyClass_TemplateDir;
			global $SmartyClass_CompileDir;
			global $SmartyClass_ConfigDir;
			global $SmartyClass_CacheDir;
			global $ProjectCode,$bridgeno,$n_num,$Item_no,$id,$mode;
			global $CompanyKind;


			$this->Smarty();
			$this->template_dir	=$SmartyClass_TemplateDir;
			$this->compile_dir	=$SmartyClass_CompileDir;
			$this->config_dir	=$SmartyClass_ConfigDir;
		}

		//============================================================================
		// 개인별 근태현황 표시
		//============================================================================
		function PersonLoginList()
		{
			global $date_today;
			global $db,$memberID;
			global $sel_year,$sel_month,$sel_day,$GroupCode;
			global $SearchID,$Display,$CompanyKind;

			$ProjectNickname=""; //프로젝트 닉네임 전역변수 선언

			$query_data  = array();
			$query_data2 = array();
			$monthdate   = array();

			$uyear = date("Y")+1;
			$last_day = date("t",mktime(0,0,0,date("m"),1,date("Y")));
			$last_day =$last_day +1;

			if($Display=="all")
			{
				$StartYear="2005";
				$StartYear= "2017";

				$dallytbl=" view_dallyproject_tbl D ";
			}
			else
			{
				$StartYear= date("Y")-1;

				$dallytbl=" view_dallyproject_year_tbl D ";
			}

			$Today=date("Y-m-d");
			if($sel_year=="") $sel_year=date("Y");
			if($sel_month=="") $sel_month=date("m");

			$YearShot=substr($sel_year,2,2);

			$this->assign('uyear',$uyear);
			$this->assign('last_day',$last_day);
			$this->assign('sel_year',$sel_year);
			$this->assign('sel_month',$sel_month);
			$this->assign('SearchID',$SearchID);

		/////////////////////////////////////////////////////////
		// 일주일전~오늘날짜 수정가능 여부
		$time = time();
		$search_array = array();
		for($i=7;$i>0;$i--){
			//echo date("Y-m-d",strtotime("-$i day", $time))." <br>";
			array_push($search_array, date("Y-m-d",strtotime("-$i day", $time)));
		}
		//for
		array_push($search_array,(string)$date_today);//오늘날짜
		/////////////////////////////////////////////////////////

			//외출조회
			$sel_yearmonth=$sel_year."-".sprintf("%02d",$sel_month);
			$sqlout="select * from view_official_planout_tbl where memberno = '$SearchID' and o_start like '$sel_yearmonth%'";
			//echo $sqlout."<Br>";
			$reout = mysql_query($sqlout,$db);
			$nuout=1;
			while($re_rowout = mysql_fetch_array($reout))
			{
				$o_startarr=$o_startarr.$re_rowout[o_start]."/";
			}



			$overtimesql="select * from overtime_basic_new_tbl order by code";
			$result_over = mysql_query($overtimesql,$db);
			while($result_over_row = mysql_fetch_array($result_over))
			{
				if($result_over_row[code] =="0") //평일근무시간
				{
					$weekday_start = $result_over_row[start_time];
					$weekday_min = $result_over_row[min_time];
					$weekday_max = $result_over_row[max_time];

					//echo $weekday_start.':'.$weekday_min.':'.$weekday_max;
				}
				elseif($result_over_row[code] =="1") //휴일근무시간
				{
					$holy_start = $result_over_row[start_time];
					$holy_min = $result_over_row[min_time];
					$holy_max = $result_over_row[max_time];
				}
				elseif($result_over_row[code] =="2") //부장월제한시간23
				{
					$E1_max = $result_over_row[max_time];
				}
				elseif($result_over_row[code] =="3") //차장월제한시간34
				{
					$E2_max = $result_over_row[max_time];
				}
				elseif($result_over_row[code] =="4") //과장월제한시간40
				{
					$E3_max = $result_over_row[max_time];
				}
				elseif($result_over_row[code] =="5") //대리사원월제한시간46
				{
					$E4_max = $result_over_row[max_time];
				}
			}

			$MemberNo2Name=MemberNo2Name($SearchID);

			$ilastday= month_lastday($sel_year,$sel_month);

			$Today1=date("Y-m-d",strtotime("+1 day", $time));
			$Today2=date("Y-m-d",strtotime("+10 day", $time));
			$Today2=$ilastday;

			for ($iday=1;$iday<=$ilastday;$iday++)
			{
				$SearchDate=sprintf('%04d-%02d-%02d',$sel_year,$sel_month,$iday);

				//if($SearchDate <=$Today1)
				if($SearchDate <=$ilastday)
				{

					$sql= "SELECT ";
					$sql= $sql." D.MemberNo MemberNo ";									//사원번호
					$sql= $sql." ,D.EntryTime EntryTime ";								//업무시작 시간
					$sql= $sql." ,D.LeaveTime LeaveTime ";								//업무종료 시간
					$sql= $sql." ,D.OverTime OverTime ";								//연장근무시작 시간
					$sql= $sql." ,DATE_FORMAT(D.EntryTime, '%Y-%m-%d') ViewDate ";
					$sql= $sql." ,DATE_FORMAT(D.EntryTime,'%H:%i') EntryMin ";			//업무시작 시간
					$sql= $sql." ,DATE_FORMAT(D.LeaveTime,'%H:%i') LeaveMin ";			//업무종료 시간
					$sql= $sql." ,DATE_FORMAT(D.OverTime,'%H:%i') OverMin ";			//연장근무시작 시간
					$sql= $sql." ,DAYNAME(DATE_FORMAT(D.EntryTime, '%Y-%m-%d')) DN";	// 요일(English)
					$sql= $sql." ,D.EntryPCode EntryPCode ";							//프로젝트코드
					$sql= $sql." ,D.EntryPCode2 EntryPCode2 ";							//프로젝트코드
					$sql= $sql." ,D.EntryJobCode EntryJobCode ";						//프로젝트서브코드
					$sql= $sql." ,D.EntryJob EntryJob ";								//업무내용
					$sql= $sql." ,D.LeavePCode LeavePCode ";								//업무종료코드
					$sql= $sql." ,D.LeavePCode2 LeavePCode2 ";								//업무종료코드

					$sql= $sql." ,D.LeaveJobCode LeaveJobCode ";								//
					$sql= $sql." ,D.LeaveJob LeaveJob ";								//

					$sql= $sql." ,D.modify modify";										//  O/T 승인여부
					$sql= $sql." ,D.Note Note";
					$sql= $sql." ,(select concat(tardy_h,RPAD(tardy_m,2,'0')) from worker_tardy_tbl where memberno=D.MemberNo and (s_date <= '$SearchDate' and e_date >= '$SearchDate' ) order by s_date desc limit 1) as StartTime";

					$sql= $sql." ,substring(D.SortKey,4,2) RankCode";
					$sql= $sql." From ";
					//$sql= $sql." view_dallyproject_tbl D ";
					$sql= $sql.$dallytbl;
					$sql= $sql." WHERE ";
					$sql= $sql." D.EntryTime  like '".$SearchDate."%'";
					$sql= $sql." AND ";
					$sql= $sql." D.MemberNo = '".$SearchID."'";
					$sql= $sql." ORDER BY D.EntryTime asc";
//echo $sql."<br>";

					$re = mysql_query($sql,$db);
					$re_num = mysql_num_rows($re);
				}else
				{
					$re_num = 0;
				}

				$holy_sc = holy($SearchDate);
				$tmp=explode("-",$SearchDate);
				$syear=$tmp[0];
				$smonth=$tmp[1];
				$tday = week_day($tmp[0],$tmp[1],$tmp[2]);

				if ($holy_sc =="holyday")
				{
					$ViewDate_IS= "<font color='#FF65A3'>".$tmp[2]."일 (".$tday.")</font>";
				}
				else
				{
					$ViewDate_IS=$tmp[2]."일 (".$tday.")";
				}


				if($re_num == 0) //근무기록없으면
				{
					if($SearchDate >$Today2)
					{
							$ProjectCode	= "";
							$UseState		= "";
							$note			= "";
							$StateName		= "";
							$EntryTime = "";
							$EntryJobCode = "";
							$EntryJob     = "";
							$OverTime="";
							$LeaveTime="";
							$projectViewCode="";
					}else
					{
						if($holy_sc == "holyday") //휴일 휴가는 표시안함
						{
							$ProjectCode	= "";
							$UseState		= "";
							$note			= "";
							$StateName		= "";
							$EntryTime = "";
							$EntryJobCode = "";
							$EntryJob     = "";
							$OverTime="";
							$LeaveTime="";
							$projectViewCode="";

						}else
						{

							$ProjectCode	= "";
							$UseState		= "";
							$note			= "";
							$StateName		= "";
							$EntryTime = "";
							$EntryJobCode = "";
							$EntryJob     = "";
							$OverTime="";
							$LeaveTime="";
							$projectViewCode="";

							//휴가파견  표시
							$sql2 = "	SELECT a.*,b.Name as StateName 															";
							$sql2 =	$sql2."			FROM																		";
							$sql2 =	$sql2."			(																			";
							$sql2 =	$sql2."			SELECT * FROM view_userstate_tbl													";
							$sql2 =	$sql2."				WHERE																	";
							$sql2 =	$sql2."					(start_time <= '".$SearchDate."' and end_time >= '".$SearchDate."')	";
							$sql2 =	$sql2."					 AND																";
							$sql2 =	$sql2."					 MemberNo = '".$SearchID."'	and state <> 15 							";
							$sql2 =	$sql2."			) a left JOIN																";
							$sql2 =	$sql2."		(																				";
							$sql2 =	$sql2."			SELECT * from systemconfig_tbl where SysKey = 'UserStateCode'				";
							$sql2 =	$sql2."		)b on a.state = b.Code	order by a.num											";
							//echo $sql2."<br>";
							$re2 = mysql_query($sql2,$db);
							$re_num2 = mysql_num_rows($re2);
							if($re_num2>0)
							{
								$ProjectCode	= mysql_result($re2,0,"ProjectCode");
								$UseState		= mysql_result($re2,0,"state");
								$note			= mysql_result($re2,0,"note");
								$note			= str_replace(" ","",$note);
								$StateName		= mysql_result($re2,0,"StateName");


								$Str_arr = explode("-",$ProjectCode);
								$arrayStr1 = $Str_arr[0];
								$arrayStr2 = $Str_arr[1];
								$arrayStr3 = $Str_arr[2];

								$ProjectCode2 = $ProjectCode;

								if($arrayStr2 == "관리" || $arrayStr2 == "고문" || $arrayStr2 == "교휴" || $arrayStr2 == "영업" || $arrayStr2 == "자기" || $arrayStr2 == "기술" || $arrayStr2 == "전산"){
									$ProjectCode2 = "HXX"."-".$arrayStr2."-".$arrayStr3;
								}

								$sql2 ="select ProjectCode,ProjectNickname,projectViewCode from project_tbl where ProjectCode='$ProjectCode2'";

								$re2				= mysql_query($sql2,$db);
								$ProjectNickname	= @mysql_result($re2,0,"ProjectNickname");
								$projectViewCode	= @mysql_result($re2,0,"projectViewCode");

								if($UseState != 9) { //파견이외
										$EntryTime2 = "<font color=blue>".$StateName."</font>";
								}else{
									$EntryTime2 = "<font color=blue>".$StateName."</font>";
								}//if End

								if($UseState == 15) {  //로그인파견
									$EntryTime    = "";
									$EntryJobCode = "";
									$EntryJob     = "";
									$ProjectCode="";
									$ProjectNickname ="";
								}else
								{
									$EntryTime    = $EntryTime2;
									$EntryJobCode = "<font color=#6a7e82>".$StateName."</font>";
									$EntryJob     = $note ;
								}
							}

						}
					}


					$re_row[EntryTime_Is]=$EntryTime;
					$re_row[EntryPCode_Is]=$ProjectCode;
					$re_row[projectViewCode]	= $projectViewCode;
					$re_row[OverTime_Is]=$OverTime;
					$re_row[LeaveTime_Is]=$LeaveTime;
					/* -------------------------- */
					$re_row[ProjectNickname]=utf8_strcut($ProjectNickname,8,'..');
					$re_row[EntryJobCode_Is]=$EntryJobCode;
					//$re_row[EntryJob_Is]=utf8_strcut($EntryJob,18,'..');
					$re_row[EntryJob_Is]=$EntryJob;
					/* -------------------------- */


					/////////////////////////////////////////////////////////
					// 일주일전~오늘날짜 수정가능 여부
					if (in_array($SearchDate,$search_array)) {
						//echo "포함:$aaa";
						$re_row[editYN]="Y";
					}
					else{
						//echo "미포함:$aaa";
						$re_row[editYN]="N";
						if($memberID=="B14306"){
							$re_row[editYN]="Y";
						}
					}
					/////////////////////////////////////////////////////////

					$re_row[ViewDate_IS]=$ViewDate_IS;
					array_push($query_data,$re_row);
					/* -------------------------- */

				}else //근무기록 존재시
				{

						while($re_row = mysql_fetch_array($re)){
							/* -------------------------- */
							$SearchID=$re_row[MemberNo];
							$RankCode=$re_row[RankCode];
							$ViewDate=$re_row[ViewDate];
							/* -------------------------- */
							$EntryTime=$re_row[EntryMin];
							$e_time=$EntryTime;
							$OverTime=$re_row[OverMin];
							$LeaveTime=$re_row[LeaveMin];
							/* -------------------------- */
							$StartTime=$re_row[StartTime];


							if($re_row[LeavePCode] =="")
							{
								$EntryPCode=$re_row[EntryPCode];
								$EntryJobCode=$re_row[EntryJobCode];
								$EntryJob=$re_row[EntryJob];
							}else
							{
								$EntryPCode=$re_row[LeavePCode];
								$EntryJobCode=$re_row[LeaveJobCode];
								$EntryJob=$re_row[LeaveJob];
							}

							/* -------------------------- */
							$Note=$re_row[Note];
							$modify=$re_row[modify];

							//$ProjectNickname=$re_row[ProjectNickname];
							$overdate= substr($re_row[OverTime],5,2)."-".substr($re_row[OverTime],8,2);
							$leavedate= substr($re_row[LeaveTime],5,2)."-".substr($re_row[LeaveTime],8,2);
							/* -------------------------- */
							//$holy_sc = holy($ViewDate);
							$nowork="no";
							/* -------------------------- */
							$OTTime="";
							$OTState="";
							/* -------------------------- */
								if($holy_sc == "weekday")  //// 평일 일 때  OverTime 야근시작시간  LeaveTime퇴근시간
								{
										//야근시작시간이 있을때
										if($OverTime != "00:00")
										{
											//O/T시간계산
											//야근시작시간이 19:00 이전이면 19:00 부터야근시작시간(계산)
											if($OverTime <= $weekday_start){
												if($SearchDate < '2018-06-21')
												{
													$OverTime2=$weekday_start;
												}else{
													if($OverTime < "18:30"){
														$OverTime2="18:30";
													}else
													{
														$OverTime2=$OverTime;
													}
												}
											}else{
												$OverTime2=$OverTime;
											}

											if  //코로나관련 탄력근무 표시 2020.03.11~2020.03.31
											(
												$SearchID=="B10201" ||
												$SearchID=="B14306" ||
												$SearchID=="B16305" ||
												$SearchID=="B16307" ||
												$SearchID=="B16312" ||
												$SearchID=="B17204" ||
												$SearchID=="B17308" ||
												$SearchID=="B17314" ||
												$SearchID=="B17315" ||
												$SearchID=="B18211" ||
												$SearchID=="B18303" ||
												$SearchID=="B18311" ||
												$SearchID=="B19206" ||
												$SearchID=="B19207" ||
												$SearchID=="B19208" ||
												$SearchID=="B19310" ||
												$SearchID=="J08305" ||
												$SearchID=="M02507" ||
												$SearchID=="M03204" ||
												$SearchID=="M03212" ||
												$SearchID=="M07308" ||
												$SearchID=="M09311" ||
												$SearchID=="M10202" ||
												$SearchID=="M14301" ||
												$SearchID=="M15204" ||
												$SearchID=="M16215" ||
												$SearchID=="M16316" ||
												$SearchID=="M16320" ||
												$SearchID=="M17203" ||
												$SearchID=="M17209" ||
												$SearchID=="M17217" ||
												$SearchID=="M18205" ||
												$SearchID=="M18213" ||
												$SearchID=="M18218" ||
												$SearchID=="M18302" ||
												$SearchID=="M18308" ||
												$SearchID=="M19305" ||
												$SearchID=="M19328"
											)
											{

												if($SearchDate <'2020-04-01' && $SearchDate > '2020-03-10' )
												{
													if($e_time <"08:01")
													{
														if($OverTime < "18:30")
														{
															$OverTime22=$OverTime;
														}else
														{
															$OverTime22=date("Y-m-d H:i:s",strtotime ("-1 hour", strtotime($re_row[OverTime])));
															$OverTime22 = substr($OverTime22,11,5);
														}
													}else
													{
														$OverTime22=$OverTime2;
													}
													$re_row[OverTime22]=$OverTime22;
													$OverTime2=$OverTime22;
												}
											}



											if  //코로나관련 탄력근무 표시 2020.04.01~2020.04.17
											(
												$SearchID=="B10201" ||
												$SearchID=="B16305" ||
												$SearchID=="B16307" ||
												$SearchID=="B16312" ||
												$SearchID=="B17204" ||
												$SearchID=="B17315" ||
												$SearchID=="B18303" ||
												$SearchID=="B18311" ||
												$SearchID=="B19303" ||
												$SearchID=="B19310" ||
												$SearchID=="B19312" ||
												$SearchID=="J14202" ||
												$SearchID=="M02507" ||
												$SearchID=="M13301" ||
												$SearchID=="M16316" ||
												$SearchID=="M17209" ||
												$SearchID=="M18205" ||
												$SearchID=="M18207" ||
												$SearchID=="M19210" ||
												$SearchID=="M19312" ||
												$SearchID=="M20303"

											)
											{

												if($SearchDate <'2020-04-18' && $SearchDate > '2020-03-31' )
												{
													if($e_time <"08:01")
													{
														if($OverTime < "18:30")
														{
															$OverTime22=$OverTime;
														}else
														{
															$OverTime22=date("Y-m-d H:i:s",strtotime ("-1 hour", strtotime($re_row[OverTime])));
															$OverTime22 = substr($OverTime22,11,5);
														}
													}else
													{
														$OverTime22=$OverTime2;
													}
													$re_row[OverTime22]=$OverTime22;
													$OverTime2=$OverTime22;
												}
											}


											if  //코로나관련 탄력근무 표시 2020.06.02~2020.06.19
											(
												$SearchID=="M18206"||
												$SearchID=="M18308"||
												$SearchID=="M19312"||
												$SearchID=="M05508"||
												$SearchID=="M20310"||
												$SearchID=="B17308"||
												$SearchID=="B18211"||
												$SearchID=="B17206"||
												$SearchID=="B19316"||
												$SearchID=="B10201"||
												$SearchID=="B16307"||
												$SearchID=="B16305"||
												$SearchID=="B19310"||
												$SearchID=="B17315"||
												$SearchID=="B18311"||
												$SearchID=="B19303"

											)
											{

												if($SearchDate <'2020-06-20' && $SearchDate > '2020-06-01' )
												{
													if($e_time <"08:01")
													{
														if($OverTime < "18:30")
														{
															$OverTime22=$OverTime;
														}else
														{
															$OverTime22=date("Y-m-d H:i:s",strtotime ("-1 hour", strtotime($re_row[OverTime])));
															$OverTime22 = substr($OverTime22,11,5);
														}
													}else
													{
														$OverTime22=$OverTime2;
													}
													$re_row[OverTime22]=$OverTime22;
													$OverTime2=$OverTime22;
												}
											}

											//코로나관련 탄력근무 표시 2020.06.02~2020.06.19
											if($SearchDate <'2020-09-12' && $SearchDate > '2020-08-18' )
											{
													if  //08시근무자
														(
															$SearchID=="M18201" ||
															$SearchID=="B20314" ||
															$SearchID=="M20310" ||
															$SearchID=="B20313" ||
															$SearchID=="B20317" ||
															$SearchID=="B20312" ||
															$SearchID=="B17315" ||
															$SearchID=="B18211" ||
															$SearchID=="B20308" ||
															$SearchID=="B20309" ||
															$SearchID=="B19303" ||
															$SearchID=="B18311" ||
															$SearchID=="M20317" ||
															$SearchID=="B20320" ||
															$SearchID=="B10201" ||
															$SearchID=="B16305" ||
															$SearchID=="B20315" ||
															$SearchID=="B19310" ||
															$SearchID=="B16312" ||
															$SearchID=="M18308" ||
															$SearchID=="M19312" ||
															$SearchID=="B16307"

														)
													{
														$OverTime22=$OverTime;
													}
													else if  //10시근무자
														(
															$SearchID=="M05508" ||
															//$SearchID=="M06203" ||
															$SearchID=="B19314" ||
															$SearchID=="B13301" ||
															$SearchID=="B16312"

														)
													{
														if($OverTime < "19:30")
														{
															$OverTime22="19:30";
														}else
														{
															$OverTime22=$OverTime;
														}
													}else
													{
														$OverTime22=$OverTime2;
													}
													$re_row[OverTime22]=$OverTime22;
													$OverTime2=$OverTime22;
											}


											//코로나관련 탄력근무 표시 2020.10.07~2020.10.16
											if($SearchDate <'2020-10-17' && $SearchDate > '2020-10-06' )
											{
													if  //08시근무자
														(
															$SearchID=="B20317" ||
															$SearchID=="B18211" ||
															$SearchID=="B20309" ||
															$SearchID=="B19303" ||
															$SearchID=="M20317" ||
															$SearchID=="B20320" ||
															$SearchID=="B10201" ||
															$SearchID=="B16305" ||
															$SearchID=="B20315" ||
															$SearchID=="B19310" ||
															$SearchID=="B16307" ||
															$SearchID=="B16312" ||
															$SearchID=="M18207" ||
															$SearchID=="M18308" ||
															$SearchID=="M19312" 

														)
													{
														$OverTime22=$OverTime;
													}
													else if  //10시근무자
														(
															$SearchID=="M18201" ||
															$SearchID=="M05508" ||
															$SearchID=="M12502" ||
															$SearchID=="B19314" ||
															$SearchID=="B18311" ||
															$SearchID=="B13301"
														)
													{
														if($OverTime < "19:30")
														{
															$OverTime22="19:30";
														}else
														{
															$OverTime22=$OverTime;
														}
													}else
													{
														$OverTime22=$OverTime2;
													}
													$re_row[OverTime22]=$OverTime22;
													$OverTime2=$OverTime22;
											}

											
											//코로나관련 탄력근무 표시 2020.12.08~2020.12.31
											/*
											if($SearchDate <'2021-01-01' && $SearchDate > '2020-12-07' )
											{
													if  //08시근무자
														(
															$SearchID=='M18207' ||
															$SearchID=='M20310' ||
															$SearchID=='B20314' ||
															$SearchID=='M20322' ||
															$SearchID=='B20329' ||
															$SearchID=='B19209' ||
															$SearchID=='B10201' ||
															$SearchID=='J15306' ||
															$SearchID=='B16305' ||
															$SearchID=='B16312' ||
															$SearchID=='B16307' ||
															$SearchID=='B17309' ||
															$SearchID=='B19310' ||
															$SearchID=='B20315' ||
															$SearchID=='B18311' ||
															$SearchID=='B19303' ||
															$SearchID=='B20308' ||
															$SearchID=='B20309' ||
															$SearchID=='M20317' ||
															$SearchID=='B17315' ||
															$SearchID=='B20317' ||
															$SearchID=='B20332' ||
															$SearchID=='B20320'
														)
													{
														$OverTime22=$OverTime;
													}
													else if  //10시근무자
														(
															$SearchID=='M05508' ||
															$SearchID=='B19313' ||
															$SearchID=='B20322' ||
															$SearchID=='B13301' ||
															$SearchID=='B19314'
														)
													{
														if($OverTime < "19:30")
														{
															$OverTime22="19:30";
														}else
														{
															$OverTime22=$OverTime;
														}
													}else
													{
														$OverTime22=$OverTime2;
													}
													$re_row[OverTime22]=$OverTime22;
													$OverTime2=$OverTime22;
											}
											*/

											
											if($StartTime <>"")
											{
												$OverTime2=$OverTime;
											}

											//echo $OverTime2."<br>";
											$LeaveSec = strtotime($LeaveTime);
											$OverSec = strtotime($OverTime2);
											$OTTime = sec_time00($LeaveSec - $OverSec);


											//echo $LeaveTime."--".$OverTime2."--".$OTTime."<br>";

											if ($overdate != "00-00")
											{
												if ($leavedate > $overdate) //다음날새벽에 끝나는 경우야근처리
												{
													$OTTime=$weekday_max;
													$OTState="○";
												}
												else
												{

													//최소근무시간 2시간이상이면 야근표시
													if($OTTime >= $weekday_min)
													{	//최대근무시간 3시간을 초과하면 최대 3시간으로 표시

														if($OTTime > $weekday_max) $OTTime=$weekday_max;
														$OTState="○";
													}
													else //최소근무시간 2시간을 미만이면 시간표시하지 않음
													{
														$OTTime="&nbsp;";
														$OTState="&nbsp;";
													}

													if($Today == $ViewDate)
													{
														$OTState="中";
													}
												}
											}
											else //($overdate <> "")
											{
												$OTTime="&nbsp;";
												$OTState="&nbsp;";
											}
										}
								}else if($holy_sc == "holyday"){ ////휴일 일 때  EntryTime 야근시작시간  LeaveTime퇴근시간
										//출근시간이 있을때
										if($EntryTime != "00:00")
										{
											// 연장근무신청서만 올리고 근무안함
											if ($EntryTime =="00:00" && $LeaveTime =="18:18")
											{
												$nowork="yes";
											}

											//출근시작시간이 09:00 이전이면 09:00 부터야근시작시간
											if($EntryTime < $holy_start) {
												$EntryTime=$holy_start;
											}

											$LeaveSec = strtotime($LeaveTime);
											$EntrySec = strtotime($EntryTime);
											$OTTime = sec_time00($LeaveSec - $EntrySec);

											if ($overdate == "00-00"){  //휴일근무에는 연장근무시작을 안누르는 경우있음
												$overdate = substr($re_row[EntryTime],5,2)."-".substr($re_row[EntryTime],8,2);

											}
											/* ------------------------------------------------------------- */
											if ($leavedate > $overdate){ //다음날새벽에 끝나는 경우야근처리
												$OTTime=$holy_max;
												$OTState="○";

											}else{
												//휴일최소근무시간 3시간이상이면 야근표시
												if($OTTime >= $holy_min){	//휴일최대근무시간 5시간을 초과하면 최대 5시간으로 표시
													if($OTTime > $holy_max ){
														$OTTime=$holy_max;
													}//if End
													$OTState="○";

												}else{						//휴일최소근무시간 3시간을 미만이면 시간표시하지 않음
													$OTTime="&nbsp;";
													$OTState="&nbsp;";
												}//if End

												if($Today == $ViewDate){
													$OTState="中";
												} //if End
												/* ------------------------------------------- */
											} //if End
											/* ------------------------------------------- */
										} //if End
										/* ------------------------------------------- */
								} //if End
								/* ------------------------------------------- */


								if($OTState=="○" )
								{
									if($modify=="1")
									{
										$OTState="○";
									}else
									{
										$OTState="";
									}
								}else if($OTState=="中" )
								{
									$OTState="中";
								}else
								{
									$OTState="&nbsp;";
								}


								/* ------------------------------------------- */
								if($nowork =="yes")
								{
									$OTTime="";
								} //if End
								/* ------------------------------------------- */
								$re_row[OTTime]=$OTTime;
								$re_row[OTState]=$OTState;
								/* ------------------------------------------- */

								//휴가파견  표시=====================================================================
									$sql2 =		  "	SELECT																										";
									$sql2 = $sql2."	 a.*                                                                                     					";
									$sql2 = $sql2."	,b.Name as StateName                                                                                     	";
									$sql2 = $sql2."	FROM																										";
									$sql2 = $sql2."	(                                                                                                           ";
									$sql2 = $sql2."	SELECT * FROM view_userstate_tbl 																				";
									$sql2 = $sql2."		WHERE (start_time <= '".$ViewDate."' and end_time >= '".$ViewDate."') and MemberNo = '".$SearchID."'	";
									$sql2 = $sql2."	) a left JOIN                                                                                               ";
									$sql2 = $sql2."	(                                                                                                           ";
									$sql2 = $sql2."	SELECT * FROM systemconfig_tbl WHERE SysKey = 'UserStateCode'                                               ";
									$sql2 = $sql2."	)b on a.state = b.Code   order by a.start_time desc,a.num asc												";
									//echo "근무기록 존재시:휴가파견  표시::<br>".$sql2."<br><br>";
									/* ------------------------------------------- */
									$re2 = mysql_query($sql2,$db);
									$re_num= mysql_num_rows($re2);
									/* ------------------------------------------- */
									if($re_num > 0) {  //휴가파견있으면
										if($EntryPCode ==""){
											$ProjectCode = mysql_result($re2,0,"ProjectCode");
										}else{
											$ProjectCode = $EntryPCode;
										} //if End
										/* ------------------------------------------- */
										$UseState = mysql_result($re2,0,"state");
										$note = mysql_result($re2,0,"note");
										$note = str_replace(" ","",$note);
										$sub_code = mysql_result($re2,0,"sub_code");

										$StateName = mysql_result($re2,0,"StateName");
										if(strpos($note,"오전반차") !== false)
										{
											$EntryTime2 = "<A href=# title=$EntryTime>"."<font color=blue>"."오전반차"."</font>"."</A>";
										}else if(strpos($note,"오후반차") !== false )
										{	if($Display=="all")  //관리자모드
											{
												$EntryTime2 =$EntryTime;
											}else  //개인
											{
												$EntryTime2 = "<A href=# title=$EntryTime>"."<font color=blue>"."오후반차"."</font>"."</A>";
											}
											$LeaveTime = "<A href=# title=$LeaveTime>"."<font color=blue>"."오후반차"."</font>"."</A>";
										}else
										{
											if($UseState != 9) { //파견이외
												if($UseState == 15) {  //로그인파견
													$EntryTime2 = $EntryTime;
												}else
												{
													$EntryTime2 = "<A href=# title='$EntryTime'>"."<font color=blue>".$StateName."</font>"."</A>";
												}

											} else {
												$EntryTime2 = "<font color=blue>".$StateName."</font>";
											}
										}

										if($UseState != 15) {  //로그인파견
											if ($UseState == 18){ //시차
													$EntryTime2 = $EntryTime;
													$ProjectCode = $EntryPCode;
													$notearr=split("/n",$note);
													$EntryJob=$EntryJob."/<font color=blue>".$notearr[0]."[".$sub_code."H]</font>";

											}else
											{
												$EntryJobCode="<font color=#6a7e82>".$StateName."</font>";
												$EntryJob=$note ;
											}
										}

										$EntryTime=$EntryTime2;
										//$OverTime="";
										//$LeaveTime="";
									}else{
										$ProjectCode = $re_row[EntryPCode];
										$UseState = "";

										if($RankCode<="C7" || $holy_sc == "holyday"){

										}else{

											//탄력2017-08-18 김도훈 09:30출근 임시  //2018-04-10 이광태 09:30 출근 180502 김세열  B18213 류한솔  || $memberID=="M05205" 손희창
											/*
											if($SearchID=="J15205" || $SearchID=="M13301" || $SearchID=="J15306" || $SearchID=="B18213" || $memberID=="M05205")
											{
												$EntryTime=c_colort4($EntryTime,$ViewDate,$RankCode);
											}else
											{
												$EntryTime=c_colort2($EntryTime,$ViewDate,$RankCode);
											}
											*/
											//echo $StartTime."<br>";
											if($StartTime <>"")
											{	
													$EntryTime=time_state2($EntryTime,$StartTime,$RankCode,$SearchID);
											}else
											{
												$EntryTime=c_colort5($EntryTime,$ViewDate,$RankCode,$SearchID);
											}


										}//if End
									}//if End

							//휴가파견  표시=====================================================================


						/* /////////////////////////////////////////////////////////////////// */
							/*-------------------------*/
							$Str_arr = explode("-",$ProjectCode);
							/*-------------------------*/
							$arrayStr1 = $Str_arr[0];
							$arrayStr2 = $Str_arr[1];
							$arrayStr3 = $Str_arr[2];
							/*-------------------------*/
							//관리,고문,교휴,영업,업무,지원 은 XX-로 시작함
							$d_EntryPCode_edit = $ProjectCode;

							if($arrayStr2 == "관리" || $arrayStr2 == "고문" || $arrayStr2 == "교휴" || $arrayStr2 == "영업" || $arrayStr2 == "자기" || $arrayStr2 == "기술" || $arrayStr2 == "전산"){
								$d_EntryPCode_edit = "HXX"."-".$arrayStr2."-".$arrayStr3;
							}

							$sql2 ="select ProjectCode,ProjectNickname,projectViewCode from project_tbl where ProjectCode='$d_EntryPCode_edit'";

							$re2				= mysql_query($sql2,$db);
							$ProjectNickname	= @mysql_result($re2,0,"ProjectNickname");
							$projectViewCode	= @mysql_result($re2,0,"projectViewCode");


							$re_row[EntryPCode_Is]=$ProjectCode;
							$re_row[projectViewCode]= $projectViewCode;


						/* /////////////////////////////////////////////////////////////////// */

							/* ---------------------- */
							if($OverTime == "00:00"){
								$OverTime="";
							} //if End
							/* ---------------------- */
							$re_row[num]=$num;
							/* ---------------------- */
							$re_row[EntryTime_Is]=$EntryTime;
							$re_row[OverTime_Is]=$OverTime;
							$re_row[LeaveTime_Is]=$LeaveTime;
							/* ---------------------- */
							$re_row[ProjectNickname]=utf8_strcut($ProjectNickname,8,'..');
							/* ---------------------- */
							$re_row[EntryJobCode_Is]=$EntryJobCode;
							$re_row[EntryJob_Is_Full]=$EntryJob;
							//$re_row[EntryJob_Is]=utf8_strcut($EntryJob,22,'..');
							$re_row[EntryJob_Is]=$EntryJob;

							/* ---------------------- */
							if($modify=="1"){
								$re_row[modify_Is]="○";

							}else{
								$re_row[modify_Is]="";

							}//if End
							/* ---------------------- */
							$re_row[ViewDate_IS]=$ViewDate_IS;
							/* ---------------------- */

						/////////////////////////////////////////////////////////
						// 일주일전~오늘날짜 수정가능 여부
						if (in_array($SearchDate,$search_array)) {
							//echo "포함:$aaa";
							$re_row[editYN]="Y";
						}
						else{
							//echo "미포함:$aaa";
							$re_row[editYN]="N";
							if($memberID=="B14306"){
								//$re_row[editYN]="Y";
							}
						}
						/////////////////////////////////////////////////////////

							/* ------------------------------------------------------------ */
							//추가근무
							//echo $Note;
							$tr_count=0;
							$tr_count = substr_count($Note,"<br>");
							if($tr_count > 0) {
								$td_array = explode("<br>",$Note);
								for($k=0;$k<$tr_count;$k++) {
									$td_array2   = explode("<|>",$td_array[$k]);
									$ProjectCode = change_XX($td_array2[0]);

								/* /////////////////////////////////////////////////////////////////// */
									/*-------------------------*/
									$Str_arr = explode("-",$ProjectCode);
									/*-------------------------*/
									$arrayStr1 = $Str_arr[0];
									$arrayStr2 = $Str_arr[1];
									$arrayStr3 = $Str_arr[2];
									/*-------------------------*/
									//관리,고문,교휴,영업,업무,지원 은 XX-로 시작함
									$d_EntryPCode_edit = $ProjectCode;
									/*-------------------------*/
									if($arrayStr2 == "관리" || $arrayStr2 == "고문" || $arrayStr2 == "교휴" || $arrayStr2 == "영업" || $arrayStr2 == "자기" || $arrayStr2 == "기술"){
										$d_EntryPCode_edit = "HXX"."-".$arrayStr2."-".$arrayStr3;
									}

									/*-------------------------*/
									/*-------------------------*/
									$sql2 =      " SELECT										";
									$sql2 = $sql2."	 ProjectCode	    as ProjectCode 			";
									$sql2 = $sql2."	,ProjectViewCode	as ProjectViewCode 		";
									$sql2 = $sql2."	,ProjectNickname	as ProjectNickname  	";
									$sql2 = $sql2."	FROM										";
									$sql2 = $sql2."	project_tbl									";
									$sql2 = $sql2."	WHERE										";
									$sql2 = $sql2."	ProjectCode = '".$d_EntryPCode_edit."'		";
									/*-------------------------*/
								///////////////
								//echo $sql2."<br><br>";
								///////////////
									$re2				= mysql_query($sql2,$db);
									$ProjectNickname	= @mysql_result($re2,0,"ProjectNickname"); 			//프로젝트 닉네임
									$ProjectViewCode	= @mysql_result($re2,0,"ProjectViewCode"); 			//프로젝트뷰코드
									/*-------------------------*/
								///////////////
								//echo $ProjectNickname."<br><br>";
								///////////////

								/* /////////////////////////////////////////////////////////////////// */
									$td_array2[job]=$td_array2[2];
									$td_array2[2]=utf8_strcut($td_array2[2],20,'...');
									$td_array2[ProjectNickname]=utf8_strcut($ProjectNickname,8,'..');
									$td_array2[ProjectViewCode]=$ProjectViewCode;
									$td_array2[row_num]=$num;
									array_push($query_data2,$td_array2);

								}// for End
							}// if End



							//외출상태 표시
							/* /////////////////////////////////////////////////////////////////// */
							if(strpos($o_startarr, $ViewDate) !== false)
							{
								$sql_out="select a.*,b.ProjectNickName,b.projectViewCode from
								(
									select * from view_official_planout_tbl where MemberNo ='$SearchID' and o_start like '$ViewDate%'
								)a left join
								(
									select ProjectCode,ProjectNickName,projectViewCode from project_tbl
								)b on a.ProjectCode=b.ProjectCode";
								//echo $sql_out."<br>";


								$re_out = mysql_query($sql_out,$db);
								$re_out_num = mysql_num_rows($re_out);
								if($re_out_num != 0) //외출한 로그있으면
								{
									if($re_out_num != 0) //외출한 로그있으면
									{
										while($re_out_row = mysql_fetch_array($re_out))
										{

											$td_array2[0]=$re_out_row[ProjectCode];
											if($td_array2[0]=="")
											{
												$td_array2[0]=$re_out_row[projectViewCode];
											}

											$td_array2[ProjectNickname]=utf8_strcut($re_out_row[ProjectNickName],8,'..');
											$td_array2[1]="외출";
											$td_array2[2]=$re_out_row[o_object];
											if (substr($re_out_row[o_end],11,8) == "00:00:00")
											{
												$td_array2[3]="<font color=#FF6600>복귀시간없음</font>";

												if($re_row[LeaveTime_Is]="18:18")
												{
													$re_row[LeaveTime_Is]="-:-";
												}
											}else
											{
												$td_array2[3]=sec_time00(strtotime($re_out_row[o_end])-strtotime($re_out_row[o_start]));
											}
											$td_array2[row_num]=$num;

											//$td_array2[4]	= projectToColumn($td_array2[0],'projectViewCode');
											//$td_array2[ProjectViewCode]=$td_array2[4];
											$td_array2[ProjectViewCode]=$re_out_row[projectViewCode];


											if($td_array2[ProjectNickname] =="")
											{
													$sql3=" select * from project_tbl where ProjectViewCode='$td_array2[4]'";
													$re3				= mysql_query($sql3,$db);
													$td_array2[ProjectNickname]	= @mysql_result($re3,0,"ProjectNickname"); 			//프로젝트 닉네임
													$td_array2[ProjectNickname]=utf8_strcut($td_array2[ProjectNickname],8,'..');
											}

											array_push($query_data2,$td_array2);

											$tr_count=$tr_count+1;

										}
									}
								}
								/* /////////////////////////////////////////////////////////////////// */
							}



							/* ------------------------- */
							$re_row[rowcount]=$tr_count+1;
							/* ------------------------- */
							array_push($query_data,$re_row);

							$num++;

						}//while End
						/* ------------------------- */
				 }// if End : 근무기록 존재시
				 /* ------------------------- */
			}
			//$WorkPosition=getWorkPositionByMemberNo($SearchID);
			$this->assign('memberID',$memberID);

////////////////////////////////////////////////////////////
if($memberID=="B14306"){
	$testYN="";
}else{
	$testYN="";
}
$this->assign('testYN',$testYN);
////////////////////////////////////////////////////////////

			/* ------------------------------------------------------------- */
			$this->assign('StartYear',$StartYear);
			$this->assign('Display',$Display);
			$this->assign('MemberName',$MemberNo2Name);
			//$this->assign('WorkPosition',$WorkPosition);
			$this->assign('query_data',$query_data);
			$this->assign('query_data2',$query_data2);

			/* ------------------------------------------------------------- */
			$this->display("intranet/common_contents/person_login/person_login_mvc.tpl");
			/* ------------------------------------------------------------- */



		}
	}


// 끝
//==================================

?>
