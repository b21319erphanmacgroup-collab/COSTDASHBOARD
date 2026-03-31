<?php
	/***************************************
	* 멤버 업무 일일 리스트	
	****************************************/
	include "../inc/dbcon.inc";
	include "../inc/function_intranet.php";
	include "../inc/function_timework.php";
	include "../util/HanamcPageControl.php";
	include "../../../SmartyConfig.php";

	require_once($SmartyClassPath);

	extract($_GET);

	$MemberNo	=	"";	//사원번호
	
	if($_SESSION['SS_memberID']!=""){
		/* SET SESSION ----------------------- */
		$MemberNo   =   $_SESSION['SS_memberID'];		//사원번호
		$memberID	=   $_SESSION['SS_memberID'];		//사원번호

		$CompanyKind=   $_SESSION['SS_CompanyKind'];//장헌산업(JANG),파일테크(PILE),바론컨설턴트(HANM)
		$korName	=	$_SESSION['SS_korName'];	//한글이름
		$RankCode	=	$_SESSION['SS_RankCode'];	//직급코드
		if($GroupCode==""){
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
	if($CompanyKind==""){
		$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
	}//if End

	class WorkCheck extends Smarty {


		function WorkCheck(){

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
		
		function ViewPage(){
			global $db;
			extract($_REQUEST);
			
			switch($MainAction){
				case "DayList": $this->WorkCheck_DayList_01();	break;
				case "MemberWorkList": $this->WorkCheck_MemberWorkList_01();	break;
				case "MemberWorkConfirm": $this->WorkCheck_MemberWorkConfirm_01();	break;
				default:
			
					if($sel_year==""){$sel_year=date('Y');}
					if($sel_month==""){$sel_month=date('m');}
					if($sel_day==""){$sel_day=date('d');}
					
					$this->assign('standStartYear','2025');
					$this->assign('standEndYear',date('Y'));			
					
					$this->assign('sel_year',$sel_year);
					$this->assign('sel_month',$sel_month);
					$this->assign('sel_day',$sel_day);
					$this->display("intranet/common_contents/work_check/work_check_board_mvc.tpl");
				break;
			}
		}
		
		function WorkCheck_DayList_01(){
			global $db;
			extract($_REQUEST);
			
			switch($SubAction){
				default:
					// 1. 날짜 및 기초 변수 설정
					if($sel_date == ""){
						$sel_date = $sel_year."-".$sel_month."-".date("d");
					}
					
					$lastday = date('t', strtotime("$sel_year-$sel_month-01"));
					$fulldata = array();
					$weekDay = array("일","월","화","수","목","금","토");
					
					// 2. 해당 월의 시작일과 종료일 계산 (시각까지 포함하여 정확한 범위 지정)
					$startDate = "$sel_year-$sel_month-01 00:00:00";
					$endDate = "$sel_year-$sel_month-$lastday 23:59:59";
					
					// 3. DB에서 한 달치 데이터를 한 번에 가져오기
					$sql = "
							    SELECT
							        DATE(EntryTime) AS WorkDate,
							        COUNT(*) AS TotalCount,
							        SUM(CASE WHEN WorkConfirmYN = 'Y' THEN 1 ELSE 0 END) AS Y_Count,
							        SUM(CASE WHEN WorkConfirmYN <> 'Y' OR WorkConfirmYN IS NULL THEN 1 ELSE 0 END) AS NotY_Count
							    FROM dallyproject_tbl
							    WHERE ReportingBoss = '".$_SESSION["memberID"]."'
												    AND EntryTime BETWEEN '$startDate' AND '$endDate'
												    GROUP BY DATE(EntryTime)
												    ORDER BY WorkDate ASC;
					    ";
					
					$re = mysql_query($sql, $db);
					$dbData = array();
					
					// 4. DB 결과를 날짜를 Key로 하는 연관 배열에 임시 저장
					if ($re) {
						while($re_row = mysql_fetch_array($re)){
							$dbData[$re_row["WorkDate"]] = $re_row;
						}
					}
					
					// 5. 1일부터 말일까지 루프를 돌며 데이터 매칭
					for($index = 0; $index < $lastday; $index++){
						$dayNum = sprintf('%02d', ($index + 1));
						$targetDate = $sel_year . "-" . $sel_month . "-" . $dayNum;
					
						// 기본값 설정 (데이터가 없는 날을 대비)
						$tempArray = array();
						$tempArray["WorkDate"] = $targetDate;
						$tempArray["WorkDay"]  = $weekDay[date('w', strtotime($targetDate))];
						$tempArray["TotalCount"] = 0;
						$tempArray["Y_Count"]    = 0;
						$tempArray["NotYCount"]  = 0;
						$tempArray["CompleteYN"] = 'F'; // 데이터 없음
						$tempArray["CompleteGBN"] = '';
					
						// 6. DB에 해당 날짜의 데이터가 있는지 확인 후 처리
						if(isset($dbData[$targetDate])){
							$row = $dbData[$targetDate];
							$tempArray["TotalCount"] = (int)$row["TotalCount"];
							$tempArray["Y_Count"]    = (int)$row["Y_Count"];
							$tempArray["NotYCount"]  = (int)$row["NotY_Count"];
					
							// 상태값 판별 로직
							if($tempArray["TotalCount"] == $tempArray["Y_Count"]){
								$tempArray["CompleteYN"] = 'Y';
								$tempArray["CompleteGBN"] = '완료';
							}
							else if($tempArray["TotalCount"] > $tempArray["Y_Count"]){
								$tempArray["CompleteYN"] = 'N';
								$tempArray["CompleteGBN"] = '미완료';
							}
							else if($tempArray["TotalCount"] < $tempArray["Y_Count"]){
								$tempArray["CompleteYN"] = 'E';
								$tempArray["CompleteGBN"] = '에러';
							}
						}
					
						array_push($fulldata, $tempArray);
					}
					
					$this->assign("sel_date",$sel_date);
					$this->assign("fulldata",$fulldata);			
					$this->display("intranet/common_contents/work_check/work_check_daylist_mvc.tpl");
				break;
			}
		}
		
		function WorkCheck_MemberWorkList_01(){
			global $db;
			extract($_REQUEST);
			
			switch($SubAction){
				default:
					
					$query_data = array();
					$query_data2 = array();
					
					$MemSQL = "SELECT 
											A.MemberNo 
										FROM 
											dallyproject_tbl A
										LEFT JOIN
											member_tbl B
										ON
											A.MemberNo=B.MemberNo
										WHERE 
											ReportingBoss='".$_SESSION["memberID"]."' 
										AND SUBSTR(EntryTime,1,10) = '".$date."'
										ORDER BY B.RankCode, B.EntryDate
										";
					
					$MemRe = mysql_query($MemSQL,$db);
					
					$searchMemberArr = "";
						
					while($MemReRow=mysql_fetch_assoc($MemRe)){
						if($searchMemberArr==""){
							$searchMemberArr="'".$MemReRow["MemberNo"]."'";
						}
						else{
							$searchMemberArr=$searchMemberArr.",'".$MemReRow["MemberNo"]."'";
						}
					}
					
					if($searchMemberArr!=""){
					
						//외출조회
						$intGroupCode=(int)$_SESSION["MyGroupCode"];
						$sqlout="select 
											memberno 
										from 
											view_official_planout_tbl 
										where o_group = '$intGroupCode' 
										AND o_start like '$date%' 
										AND memberno IN ($searchMemberArr)";
						
						$reout = mysql_query($sqlout,$db);
						$nuout=1;
						
						while($re_rowout = mysql_fetch_array($reout)){
							$MemberNoArr=$MemberNoArr.$re_rowout[memberno]."/";
						}
						
					
						//휴가조회
						$sqlu="select 
										MemberNo 
									from 
										view_userstate_tbl  
									where GroupCode = '$intGroupCode' 
									and start_time <= '$date' 
									and end_time >= '$date' 
									AND MemberNo IN ($searchMemberArr)";
						$reu = mysql_query($sqlu,$db);
						while($re_rowu = mysql_fetch_array($reu)){
							$MemberNoArr2=$MemberNoArr2.$re_rowu[MemberNo]."/";
						}
						
						if($GroupCode=="all"){
							$presql="select 
												* 
											from 
												member_tbl 
											where (WorkPosition = 1 or WorkPosition = 4) 
											order by GroupCode,RankCode,JuminNo ";
						}
						else{
							$presql="select 
											* 
										from 
											member_tbl 
										where (WorkPosition = 1 or WorkPosition = 4) 
										and GroupCode='$intGroupCode' 
										AND MemberNo IN ($searchMemberArr)
										order by GroupCode,IF(RealRankCode > 'C7',RankCode,IF(RealRankCode='',RankCode, IFNULL(RealRankCode,RankCode))),binary(korName),EntryDate ";
						}
						
							
						$sql = "select
										a.MemberNo as MemberNo,
										a.korName as korName,
										a.Name as Name,
										a.RankCode as RankCode,
										a.RealRankCode as RealRankCode,
										a.WorkPosition as WorkPosition,
										a.GroupCode as GroupCode,
							
										b.EntryTime as EntryTime,
										b.OverTime as OverTime,
										b.LeaveTime as LeaveTime,
										b.EntryPCode as EntryPCode,
										b.EntryPCode2 as EntryPCode2,
										b.EntryJobCode as EntryJobCode,
										b.EntryJob as EntryJob,
										b.LeavePCode as LeavePCode,
										b.LeaveJobCode as LeaveJobCode,
										b.LeaveJob as LeaveJob,
										/*b.Note as Note,*/
										b.modify as modify,
										b.ProjectNickname as ProjectNickname,
										b.projectViewCode as projectViewCode,
										b.NewProjectCode as NewProjectCode,
						
										b.u_state as u_state,
										b.u_ProjectCode as u_ProjectCode,
										b.u_note as u_note,
										b.u_sub_code as u_sub_code,
										b.StateName as StateName,
										b.ReportingBoss as ReportingBoss		
								from
								(
									select * from
									("
										.$presql."
									)a1 
									left JOIN
									(
										select * from systemconfig_tbl where SysKey='PositionCode'
									)a2 
									on a1.RankCode = a2.code
								)a 
								left JOIN
								(
									select * from
									(
										select * from
										(
											select * from view_dallyproject_tbl where EntryTime like '$date%'
										)c1 
										left JOIN
										(
											select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl
										)c2 
										on (c1.EntryPCode = c2.ProjectCode )
									)cc 
									left join 
									(
										select u.MemberNo as u_MemberNo,u.state as u_state,u.ProjectCode as u_ProjectCode,u.note as u_note,SUM(u.sub_code) as u_sub_code,s.Name as StateName 
										from
										(
											select * from view_userstate_tbl where (start_time <= '$date' and end_time >= '$date') and state <>15
										)u 
										left JOIN
										(
											select * from systemconfig_tbl where SysKey = 'UserStateCode'
										)s 
										on u.state = s.Code 
										GROUP BY u.MemberNo, u.state
										order by u.num
									)dd 
									on cc.MemberNo=dd.u_MemberNo
								)b 
								on a.MemberNo = b.MemberNo ";
					
						
						$re = mysql_query($sql,$db);
						$num=1;
															
						while($re_row = mysql_fetch_array($re)){
							$MemberNo=$re_row[MemberNo];
							$RankCode=$re_row[RankCode];
							$RealRankCode=$re_row[RealRankCode];
							$EntryTime=$re_row[EntryTime];
							$OverTime=$re_row[OverTime];
							$LeaveTime=$re_row[LeaveTime];
							$LeaveTime_full=$re_row[LeaveTime];

							$u_state=$re_row[u_state];
							$u_ProjectCode=$re_row[u_ProjectCode];
							$u_note=$re_row[u_note];
							$u_sub_code=$re_row[u_sub_code];
							$StateName=$re_row[StateName];

							$EntryTime=substr($EntryTime,11,5);
							$OverTime=substr($OverTime,11,5);
							$LeaveTime=substr($LeaveTime,11,5);
					
							$re_row["ReportingBossText"] = "";
					
							if($re_row["ReportingBoss"]!=""){
								$MemSQL = "SELECT a.korName, (SELECT Name FROM systemconfig_tbl WHERE SysKey='PositionCode' AND Code=a.RankCode) AS RankName
													FROM member_tbl a WHERE a.MemberNo='".$re_row["ReportingBoss"]."'";
								$MemRe = mysql_query($MemSQL,$db);
																	
								$MemRankName = "";
									
								if(mysql_num_rows($MemRe)>0){
									$MemRankName = mysql_result($MemRe,0,"RankName");

									if(mysql_result($MemRe,0,"RankName")=="선임연구원"){
										$MemRankName = "선임";
									}
									else if(mysql_result($MemRe,0,"RankName")=="책임연구원"){
										$MemRankName = "책임";
									}
									else if(mysql_result($MemRe,0,"RankName")=="수석연구원"){
										$MemRankName = "수석";
									}

									$re_row["ReportingBossText"] = mysql_result($MemRe,0,"korName")." ".$MemRankName;

								}
							}
					
					
							$projectViewCode=$re_row["projectViewCode"];

							$EntryPCode=$re_row["EntryPCode"];
							$EntryJobCode=$re_row["EntryJobCode"];
							$EntryJob=$re_row["EntryJob"];
							$ProjectNickname=$re_row["ProjectNickname"];

							$LeavePCode=$re_row["LeavePCode"];
							$LeaveJobCode=$re_row["LeaveJobCode"];
							$LeaveJob=$re_row["LeaveJob"];
					
							if(change_XXIS($EntryPCode))	{
								$ProjectCodeXX = change_XX($EntryPCode);
								$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$EntryPCode' or ProjectCode='$ProjectCodeXX'";
							}
							else{
								$ProjectCodeXX ="";
								$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$EntryPCode'";
							}
					
							$re3= mysql_query($sql3,$db);
	
							$projectViewCode=@mysql_result($re3,0,"projectViewCode");
							$re_row[projectViewCode]=$projectViewCode;
							$ProjectNickname=@mysql_result($re3,0,"ProjectNickname");
	
							$modify=$re_row[modify];
							
							$ProjectCode = $EntryPCode;
							$UseState = "";
								
							if($GroupCode=="003"){
								if($RealRankCode<>""){
									$RankCode=$RealRankCode;
								}
							}
	
							$re_row[EntryPCode_Is]=$ProjectCode;
	
							if($OverTime == "00:00"){ $OverTime="";}
							//if($LeaveTime == "00:00" ){ $LeaveTime="";}
							if($LeaveTime_full == "0000-00-00 00:00:00" ){ $LeaveTime="";}
	
							$re_row[num]=$num;
	
							$re_row[EntryTime_Is]=$EntryTime;
							$re_row[OverTime_Is]=$OverTime;
							
							$woSQL = "SELECT
													(CASE WHEN COUNT(*) > 0 THEN 'Y' ELSE 'N' END) as WorkOutNotYN,
													ClockOutReason
												FROM
													workout_not_tbl
												WHERE
													MemberNo='".$MemberNo."'
												AND WorkOutDate='".$date."' ";
							$woResult = mysql_query($woSQL,$db);
							
							$WorkOutNotYN="";
							$ClockOutReason="";
							
							if(mysql_num_rows($woResult) > 0){
								$WorkOutNotYN= mysql_result($woResult,0,"WorkOutNotYN");
								$ClockOutReason=mysql_result($woResult,0,"ClockOutReason");
							}
							
							$re_row["ClockOutReason"] = $ClockOutReason;
	
							if($WorkOutNotYN=="Y"){
								$re_row["LeaveTime_Is"]="<font style='color:#FF2200;'>".$LeaveTime."</font>";
							}
							else{
								$re_row["LeaveTime_Is"]=$LeaveTime;								
							}
	
							//$re_row[ProjectNickname]=utf8_strcut($ProjectNickname,12,'...');
							//$re_row[ProjectNickname]=strcut_utf8($ProjectNickname,16,'...');
	
							$re_row[EntryJobCode_Is]=$EntryJobCode;
							if ($UseState == 18){ //시차
								$re_row[EntryJob_Is]=utf8_strcut($EntryJob,41,'...');
							}
							else{
								//$re_row[EntryJob_Is]=utf8_strcut($EntryJob,17,'...');
								$re_row[EntryJob_Is]=$EntryJob;
							}
	
	
							if($modify=="1"){$re_row[modify_Is]="○";
							}else{ $re_row[modify_Is]="";}
	
							$total_minutes = 0; // 총 분을 저장할 변수 초기화
	
							$addWorkSQL = "SELECT
															project_code,
															new_project_code,
															activity_code,
															contents,
															work_hour,
															work_min
														FROM
															dallyproject_addwork_tbl
														WHERE
															MemberNo='".$MemberNo."'
														AND EntryTime = '".$date."'
														ORDER BY seq_no
												";
							$addWorkRe = mysql_query($addWorkSQL,$db);
							
							$addWorkArray = array();
	
							$tr_count=mysql_num_rows($addWorkRe);
	
							while($addWorkReRow=mysql_fetch_assoc($addWorkRe)){
									
								$sqlp="select * from project_tbl where ProjectCode ='".$addWorkReRow["project_code"]."'";
								
								$rep = mysql_query($sqlp,$db);
								$re_nump = mysql_num_rows($rep);
								if($re_nump != 0){
									$ProjectNickname = mysql_result($rep,0,"ProjectNickname");
								}
								else{
									$ProjectNickname ="";
								}
									
									
								$addWorkArray["activity_code"]=$addWorkReRow["activity_code"];
								$addWorkArray["contents"]=$addWorkReRow["contents"];
								$addWorkArray["workTime"] = sprintf('%02d',$addWorkReRow["work_hour"]).":".sprintf('%02d',$addWorkReRow["work_min"]);
								//$addWorkArray["ProjectNickname"]=utf8_strcut($ProjectNickname,12,'...');
								$addWorkArray["ProjectNickname"]=$ProjectNickname;
								$addWorkArray["row_num"]=$num;
								$addWorkArray["projectViewCode"]	= projectToColumn($addWorkReRow["project_code"],'projectViewCode');
									
								$hours = (int) $addWorkReRow["work_hour"]; // 시
								$minutes = (int) $addWorkReRow["work_min"]; // 분
	
								// 3. 분 단위로 변환하여 누적
								$total_minutes += ($hours * 60) + $minutes;
								
								//추가 업무시간 더하기 종료
								array_push($query_data2,$addWorkArray);
							}
							
							//연차,반차,시차 등등 관련 내용
							//배열선언
							$userVactionArray = array();
							
							//$u_state가 빈 값이 아니면 배열에 담기
							if($u_state <> ""){
								
								if(change_XXIS($u_ProjectCode)){
									$u_ProjectCodeXX = change_XX($u_ProjectCode);
									$u_sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$u_ProjectCodeXX'";
								}
								else{
									$ProjectCodeXX = "";
									$u_sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$u_ProjectCodeXX'";
								}
								
								$u_re3	= mysql_query($u_sql3,$db);
								$u_ProjectNickname=@mysql_result($u_re3,0,"ProjectNickname");
								$u_ProjectCode=@mysql_result($u_re3,0,"projectViewCode");
								
								if($u_state=="30" || $u_state=="31"){
									$u_sub_code = '4';
								}
								else if($u_state=="18"){
									$u_note_arr = explode(':', $u_note);
									$u_note = $u_note_arr[0]."[".$u_sub_code."H]";
								}
								
								$userVactionArray["activity_code"]="<font color='#303FD9'>".$StateName."</font>";
								$userVactionArray["contents"]="<font color='#303FD9'>".$u_note."</font>";
								$userVactionArray["workTime"] = "<font color='#303FD9'>".sprintf('%02d',$u_sub_code).":".sprintf('%02d',0)."</font>";
								$userVactionArray["ProjectNickname"]="<font color='#303FD9'>".$u_ProjectNickname."</font>";
								$userVactionArray["row_num"]=$num;
								$userVactionArray["projectViewCode"]	= "<font color='#303FD9'>".$u_ProjectCode."</font>";
									
								$hours = (int) $u_sub_code; // 시
								$minutes = 0; // 분
								
								// 3. 분 단위로 변환하여 누적
								$total_minutes += ($hours * 60) + $minutes;
								
								//추가 업무시간 더하기 종료
								array_push($query_data2,$userVactionArray);
								$tr_count++;
							}
							
							
							//연장근무 담는 배열
							$overWorkArray=array();
	
							//연장근무가 빈 값이 아니면 overWorkArray 생성
							if($LeavePCode!="" && $re_row[OverTime] != "0000-00-00 00:00:00"){
								$sqlp="select * from project_tbl where ProjectCode ='".$LeavePCode."'";
	
								$rep = mysql_query($sqlp,$db);
								$re_nump = mysql_num_rows($rep);
								if($re_nump != 0){
									$LeaveProjectNickname = mysql_result($rep,0,"ProjectNickname");
								}
								else{
									$LeaveProjectNickname ="";
								}
									
								$diff_workTime= strtotime($re_row["LeaveTime"])-strtotime($re_row["OverTime"]);
								$threshold_seconds = 10800;	//3시간 초로 변환
									
								if($diff_workTime > $threshold_seconds){
									$diff_workTime='03:00';
								}
								else{
									$diff_workTime=gmdate('H:i', $diff_workTime);
								}
									
								$overWorkArray["activity_code"]="<font style='color:blue;'>".$LeaveJobCode."</font>";
								$overWorkArray["contents"]="<font style='color:blue;'>".$LeaveJob."</font>";
								$overWorkArray["workTime"] = "<font style='color:blue;'>".$diff_workTime."</font>";
								//$overWorkArray["ProjectNickname"]="<font style='color:blue;'>".utf8_strcut($LeaveProjectNickname,12,'...')."</font>";
								$overWorkArray["ProjectNickname"]="<font style='color:blue;'>".$LeaveProjectNickname."</font>";
								$overWorkArray["row_num"]=$num;
								$overWorkArray["projectViewCode"]	= "<font style='color:blue;'>".projectToColumn($ProjectCode,'projectViewCode')."</font>";
									
								array_push($query_data2,$overWorkArray);
									
								$tr_count++;
							}
	
							$remainTime = $this->remainTime($total_minutes);
	
							$re_row["EntryJob_Is"] = "[".$remainTime."]".$re_row["EntryJob_Is"];
	
	
							if($re_row[GroupCode]=="31" && $re_row[WorkPosition]=="4"){ //감리대기자
								$re_row[korName]="<font color=blue>".$re_row[korName]."</font>";
							}
	
							$re_row[rowcount]=$tr_count+$tr_count2+1;
							$tr_count2=0;
							array_push($query_data,$re_row);
							$num++;
	
						}
							
							
							
						if($sub_index == "")
							$sub_index=$GroupCode;
								
							$tab_index=$GroupCode;
								
							$GroupList = array();
								
							$Group_Row="0";
							$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' and Code<>'28' order by orderno  asc";
								
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
								$re_row2[Name]="";
								array_push($GroupList,$re_row2);
							}
					}
					
					/* For 코드사용분기 Start  ******************* */
					
					$this->assign('CK_CompanyKind',$CompanyKind);
					
					$this->assign('query_data',$query_data);
					$this->assign('query_data2',$query_data2);
					
					$this->assign('date',$date);
					
					$this->display("intranet/common_contents/work_check/work_check_memworklist_mvc.tpl");
				break;
			}
		}
		
		function WorkCheck_MemberWorkConfirm_01(){
			global $db;
				
			extract($_REQUEST);
				
			
			$nowDate = date("Y-m-d");
				
			$confirmDate = "";
				
			if($nowDate == $selectDate){
				$confirmDate= date("Y-m-d",strtotime("-1 day"));
			}
			else if($nowDate > $selectDate){
				$confirmDate = $selectDate;
			}
			else if($nowDate < $selectDate){
				echo 'OverDate';
				exit;
			}
		
			$sql="Update
						dallyproject_tbl
					SET
						WorkConfirmYN='Y'
					WHERE
						DATE_FORMAT(EntryTime,'%Y-%m-%d') = '".$confirmDate."'
					AND ReportingBoss='".$user_id."'";
			
			$re=mysql_query($sql,$db);
				
			if($re){ echo "success";}
			else { echo "fail";}
		}
		
		
		//=========================================================//
		// 함수 영역
		//========================================================//
		
		function remainTime($total_minutes){
			$stnd_total_work_time = 8 * 60; // 8시간 = 480분
			$remaining_minutes = $stnd_total_work_time - $total_minutes;
			
			$final_hours = floor($remaining_minutes / 60); // 총 시
			$final_minutes = $remaining_minutes % 60;    // 남은 분
			
			// 4. 결과 출력
			$remaining_time_formatted = sprintf('%02d:%02d', $final_hours, $final_minutes);
			
			return $remaining_time_formatted;
		}
}

// 끝
//==================================
?>