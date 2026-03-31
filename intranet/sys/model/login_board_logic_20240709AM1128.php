<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
	/***************************************
	* 근태이력
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH
	* 2015-02-02 : 코드내 문제점  -> 세션값에만 의존한 코딩(memberID,CompanyKind등등) , 세션값 코드내 직접사용
	* 2015-02-02 : 해결방안-> 세션,쿠키,GET value 등을 체크후 코딩 상단에 전역변수를 선언하여 메서드에서 GLOBAL 선언하여 사용 :SUK
	* 2015-02-02 : 회사코드 DB조회 FUNCTION 추가(function_intranet.php > searchCompanyKind()) : SUK
	****************************************/
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
	}else if($_COOKIE['CK_memberID']!=""){				//쿠키값 유무확인
		/* SET COOKIE --------------------------------- */
		$MemberNo   =   $_COOKIE['CK_memberID'];	//사원번호
		$memberID	=   $_COOKIE['CK_memberID'];	//사원번호

		$CompanyKind=	$_COOKIE['CK_CompanyKind'];	//회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		$korName	=	$_COOKIE['CK_korName'];		//한글이름
		$RankCode	=	$_COOKIE['CK_RankCode'];	//직급코드
		if($GroupCode=="")
		{
			$GroupCode	=	$_COOKIE['CK_GroupCode'];	//부서코드
		}
		$SortKey	=	$_COOKIE['CK_SortKey'];		//직급+부서코드
		$EntryDate	=	$_COOKIE['CK_EntryDate'];	//입사일자
		$position	=	$_COOKIE['CK_position'];	//직위명
		$GroupName	=	$_COOKIE['CK_GroupName'];	//부서명
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
?>
<?
	class LoginBoard extends Smarty {


		function LoginBoard()
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


			//============================================================================
		// 근태이력 표시
		//============================================================================
		function LoginBoardList()
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

			$query_data = array();
			$query_data2 = array();

			$uyear = date("Y")+1;


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


				//외출조회
				$intGroupCode=(int)$GroupCode;
				$sqlout="select * from view_official_planout_tbl where o_group = '$intGroupCode' and o_start like '$date%'";
				$reout = mysql_query($sqlout,$db);
				$nuout=1;
				while($re_rowout = mysql_fetch_array($reout))
				{
					$MemberNoArr=$MemberNoArr.$re_rowout[memberno]."/";
				}


				//휴가조회
				$sqlu="select * from view_userstate_tbl  where GroupCode = '$intGroupCode' and start_time <= '$date' and end_time >= '$date'";
				$reu = mysql_query($sqlu,$db);
				while($re_rowu = mysql_fetch_array($reu))
				{
					$MemberNoArr2=$MemberNoArr2.$re_rowu[MemberNo]."/";
				}


				//
				if($GroupCode=="all"){
					$presql="select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) order by GroupCode,RankCode,JuminNo ";
				}
				else{
					if($GroupCode=="003" || $GroupCode=="098" ){
						//$presql="select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and GroupCode='$GroupCode' order by GroupCode,RealRankCode,RankCode,order_index,EntryDate ";

						$presql="select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and GroupCode='$GroupCode' order by GroupCode,IF(RealRankCode > 'C7',RankCode,IF(RealRankCode='',RankCode, IFNULL(RealRankCode,RankCode))),binary(korName),EntryDate ";
					}
					else{
						$presql="select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and GroupCode='$GroupCode' order by GroupCode,RankCode,EntryDate ";
					}
				}

				/*
				$sql = "select a.MemberNo as MemberNo,
					a.korName as korName,
					a.Name as Name,
					a.RankCode as RankCode,
					a.RealRankCode as RealRankCode,
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
					b.Note as Note,
					b.modify as modify,
					b.ProjectNickname as ProjectNickname,
					b.projectViewCode as projectViewCode,
					b.NewProjectCode as NewProjectCode
				from
				(
						select * from
						("
							.$presql."
						)a1 left JOIN
						(
							select * from systemconfig_tbl where SysKey='PositionCode'
						)a2 on a1.RankCode = a2.code

				)a left JOIN
				(
						select * from
						(
							select * from view_dallyproject_tbl where EntryTime like '%$date%'
						)c1 left JOIN
						(

							select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl
						)c2 on (c1.EntryPCode = c2.ProjectCode)

				)b on a.MemberNo = b.MemberNo ";
			*/
			/*
				$sql = "select a.MemberNo as MemberNo,
					a.korName as korName,
					a.Name as Name,
					a.RankCode as RankCode,
					a.RealRankCode as RealRankCode,
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
					b.Note as Note,
					b.modify as modify,
					b.ProjectNickname as ProjectNickname,
					b.projectViewCode as projectViewCode,
					b.NewProjectCode as NewProjectCode
				from
				(
						select * from
						("
							.$presql."
						)a1 left JOIN
						(
							select * from systemconfig_tbl where SysKey='PositionCode'
						)a2 on a1.RankCode = a2.code

				)a left JOIN
				(

						select cc.*,dd.state from
						(
							select * from
							( 	select * from view_dallyproject_tbl where EntryTime like '$date%'
							)c1 left JOIN
							(
								select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl
							)c2 on (c1.EntryPCode = c2.ProjectCode )
						 )cc left join (


							 select state,MemberNo  from view_userstate_tbl where (start_time <= '$date' and end_time >= '$date' ) and state <>15
						)dd on cc.MemberNo=dd.MemberNo
				)b on a.MemberNo = b.MemberNo ";

			*/

			$sql = "select a.MemberNo as MemberNo,
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
					b.Note as Note,
					b.modify as modify,
					b.ProjectNickname as ProjectNickname,
					b.projectViewCode as projectViewCode,
					b.NewProjectCode as NewProjectCode,

					b.u_state as u_state,
					b.u_ProjectCode as u_ProjectCode,
					b.u_note as u_note,
					b.u_sub_code as u_sub_code,
					b.StateName as StateName

				from
				(
						select * from
						("
							.$presql."
						)a1 left JOIN
						(
							select * from systemconfig_tbl where SysKey='PositionCode'
						)a2 on a1.RankCode = a2.code

				)a left JOIN
				(

						select * from
						(
							select * from
							( 	select * from view_dallyproject_tbl where EntryTime like '$date%'
							)c1 left JOIN
							(
								select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl
							)c2 on (c1.EntryPCode = c2.ProjectCode )
						 )cc left join (

							 select u.MemberNo as u_MemberNo,u.state as u_state,u.ProjectCode as u_ProjectCode,u.note as u_note,u.sub_code as u_sub_code,s.Name as StateName from
							(
								select * from view_userstate_tbl where (start_time <= '$date' and end_time >= '$date') and state <>15
							)u left JOIN
							(
								select * from systemconfig_tbl where SysKey = 'UserStateCode'
							)s on u.state = s.Code order by u.num

						)dd on cc.MemberNo=dd.u_MemberNo
				)b on a.MemberNo = b.MemberNo ";

			//echo $sql."<Br>";

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


				$projectViewCode=$re_row[projectViewCode];

				if($re_row[LeavePCode] ==""){
					$EntryPCode=$re_row[EntryPCode];
					$EntryJobCode=$re_row[EntryJobCode];
					$EntryJob=$re_row[EntryJob];
					$ProjectNickname=$re_row[ProjectNickname];
				}
				else{
					$EntryPCode=$re_row[LeavePCode];
					$EntryJobCode=$re_row[LeaveJobCode];
					$EntryJob=$re_row[LeaveJob];
				}

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

				//echo $projectViewCode."<br>";

				$Note=$re_row[Note];
				$modify=$re_row[modify];


				/*
				$sql2 = "select a.*,b.Name as StateName from
						(
							select * from view_userstate_tbl where start_time = '$date'  and MemberNo = '$MemberNo' and state <>15
						) a left JOIN
						(
							select * from systemconfig_tbl where SysKey = 'UserStateCode'
						)b on a.state = b.Code order by a.num	";
				*/
				/*
						$sql2 = "select a.*,b.Name as StateName from
						(
							select * from view_userstate_tbl where (start_time <= '$date' and end_time >= '$date') and MemberNo = '$MemberNo' and state <>15
						) a left JOIN
						(
							select * from systemconfig_tbl where SysKey = 'UserStateCode'
						)b on a.state = b.Code order by a.num	";


				//echo $sql2."<Br>";
				$re2 = mysql_query($sql2,$db);
				$re_num= mysql_num_rows($re2);
				*/

				if($u_state <> '') {  //휴가파견있으면
					
					if($EntryPCode ==""){
						//echo "-----------------------------<br>";
						$ProjectCode = mysql_result($re2,0,"ProjectCode");
						if(change_XXIS($ProjectCode)){
							$ProjectCodeXX = change_XX($ProjectCode);
							$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$ProjectCode' or ProjectCode='$ProjectCodeXX'";
						}
						else{	
							$ProjectCodeXX = "";
							$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$ProjectCode'";
						}

						//echo $sql3."<Br>";
						$re3				= mysql_query($sql3,$db);
						$re_row[projectViewCode]=$projectViewCode;
						$ProjectNickname=@mysql_result($re3,0,"ProjectNickname");
						$ProjectCode=@mysql_result($re3,0,"projectViewCode");
					}
					else{

						$ProjectCode = $EntryPCode;
						/*
						$UseState = mysql_result($re2,0,"state");
						$note = mysql_result($re2,0,"note");
						$note = str_replace(" ","",$note);
						$sub_code = mysql_result($re2,0,"sub_code");
						$StateName = mysql_result($re2,0,"StateName");
						*/

						$UseState = $u_state;
						$note = $u_note;
						$note = str_replace(" ","",$note);
						$sub_code = $u_sub_code;
						
						if(strpos($note,"오전반차") !== false)	{
							$EntryTime2 = "<A href=# title=$EntryTime>"."<font color=blue>"."오전반차"."</font>"."</A>";
						}
						else if(strpos($note,"오후반차") !== false ){	
							$EntryTime2 =$EntryTime;
							$LeaveTime = "<A href=# title=$LeaveTime>"."<font color=blue>"."오후반차"."</font>"."</A>";
						}
						else{
							
							if(strpos($note,"보건휴가") !== false){
								$StateName = "휴가";
								$note = "휴가(개인사정)";
							}
							
							if($UseState != 9) { //파견이외
								if($UseState == 15) {  //로그인파견
									$EntryTime2 = $EntryTime;
									if($EntryTime ==""){
										$ProjectCode="";
									}

								}
								else{									
									$EntryTime2 = "<A href=# title='$EntryTime'>"."<font color=blue>".$StateName."</font>"."</A>";									
								}
							} 
							else {
								$EntryTime2 = "<font color=blue>".$StateName."</font>";
							}
						}
						
						if($UseState != 15) {  //로그인파견
							if ($UseState == 18){ //시차
									$EntryTime2 = "<font color=blue>".$EntryTime."</font>";
									$ProjectCode = $EntryPCode;
									$EntryJob="<font color=blue>[시차:".$sub_code."H]</font>".$EntryJob;

							}
							else	{
								if($StateName == "감리대기"){
									$EntryJobCode="<font color=red>".$StateName."</font>";
								}
								else{
									$EntryJobCode="<font color=#6a7e82>".$StateName."</font>";
								}
								$EntryJob=$note ;
							}
						}

						$EntryTime=$EntryTime2;
					}

				}
				else	{
					$ProjectCode = $EntryPCode;
					$UseState = "";

					if($GroupCode=="003"){
						if($RealRankCode<>""){
							$RankCode=$RealRankCode;
						}
					}

						//휴가상태 표시
						if(strpos($MemberNoArr2, $MemberNo) !== false) {

								$sqlu2 = "select a.*,b.Name as StateName from
								(
									select * from view_userstate_tbl where (start_time <= '$date' and end_time >= '$date') and MemberNo = '$MemberNo' and state <>15
								) a left JOIN
								(
									select * from systemconfig_tbl where SysKey = 'UserStateCode'
								)b on a.state = b.Code order by a.num limit 1";


								//echo $sql_out."<br>";
								$re_u2 = mysql_query($sqlu2,$db);
								$re_u2_num = mysql_num_rows($re_u2);
								if($re_u2_num != 0){ //외출한 로그있으면
								
									while($re_u2_row = mysql_fetch_array($re_u2)){

										$ProjectCode = $re_u2_row[ProjectCode];
										if(change_XXIS($ProjectCode)){
											$ProjectCodeXX = change_XX($ProjectCode);
											$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$ProjectCode' or ProjectCode='$ProjectCodeXX'";
										}
										else{	$ProjectCodeXX = "";
											$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$ProjectCode'";
										}

										//echo $sql3."<Br>";
										$re3				= mysql_query($sql3,$db);
										$re_row[projectViewCode]=$projectViewCode;
										$ProjectNickname=@mysql_result($re3,0,"ProjectNickname");
										$ProjectCode=@mysql_result($re3,0,"projectViewCode");


										$UseState = $re_u2_row[state];
										$note = $re_u2_row[note];
										$note = str_replace(" ","",$note);
										$sub_code = $re_u2_row[sub_code];
										$StateName = $re_u2_row[StateName];

											if(strpos($note,"오전반차") !== false){
												$EntryTime2 = "<A href=# title=$EntryTime>"."<font color=blue>"."오전반차"."</font>"."</A>";
											}
											else if(strpos($note,"오후반차") !== false ){	
												$EntryTime2 =$EntryTime;
												$LeaveTime = "<A href=# title=$LeaveTime>"."<font color=blue>"."오후반차"."</font>"."</A>";
											}
											else{
												
												if(strpos($note,"보건휴가") !== false){
													$StateName = "휴가";
													$note = "휴가(개인사정)";
												}
												
												if($UseState != 9) { //파견이외
													if($UseState == 15) {  //로그인파견
														$EntryTime2 = $EntryTime;
														if($EntryTime ==""){
															$ProjectCode="";
														}

													}
													else{
														//==============수정작업 시작===============//
														//2024.07.08 건설사업관리부 정재익 이사 요청으로 인한 수정
														//현재일 >= 조회일 경우 출근에 글자는 빨간색 글자 뒤에 [ X ]로 표시 요청
														//현재일 > 조회일 경우 퇴근에 X 넣고 글자는 빨간색으로 표시 요청
														$searchDate = date('Y-m-d',strtotime($sel_year."-".$sel_month."-".$sel_day));
														$nDate  = date('Y-m-d');
														
														if($UseState == "22" || $UseState == "23"){															
															if($EntryTime == "" ){
																if($nDate >= $searchDate){
																	$EntryTime2 = "<A href=# title='$EntryTime'>"."<font color=red>".$StateName."<br/>[ X ]</font>"."</A>";
																}
																else{
																	$EntryTime2 = "<A href=# title='$EntryTime'>"."<font color=blue>".$StateName."</font>"."</A>";
																}
															}
															else{
																$EntryTime2 = "<A href=# title='$EntryTime'>"."<font color=blue>".$StateName."</font>"."</A>";
															}
														}
														else{
															$EntryTime2 = "<A href=# title='$EntryTime'>"."<font color=blue>".$StateName."</font>"."</A>";
														}
														//=============수정작업 종료=============//
													}
												} 
												else {
													$EntryTime2 = "<font color=blue>".$StateName."</font>";
												}
											}

											if($UseState != 15) {  //로그인파견
												if ($UseState == 18){ //시차
														$EntryTime2 = "<font color=blue>".$EntryTime."</font>";
														$ProjectCode = $EntryPCode;
														$EntryJob="<font color=blue>[시차:".$sub_code."H]</font>".$EntryJob;

												}
												else	{
													if($StateName == "감리대기"){
														$EntryJobCode="<font color=red>".$StateName."</font>";
													}
													else{
														$EntryJobCode="<font color=#6a7e82>".$StateName."</font>";
													}
													//$EntryJobCode="<font color=#6a7e82>".$StateName."</font>";
													$EntryJob=$note ;
												}
											}

											$EntryTime=$EntryTime2;

									}
								}

						}//휴가상태 표시



					if($RankCode<="C7" || $holy_sc == "holyday")
					{}
				    else{

						//2017-08-18 김도훈 09:30출근 임시  //2018-04-10 이광태 09:30 출근 //2018-05-02 김세열 B18213 류한솔   201901~02  손희창10시 03 09:30
						/*
						if($MemberNo=="J15205" || $MemberNo=="M13301" || $MemberNo=="J15306" || $MemberNo=="B18213" || $memberID=="M05205")
						{
							$EntryTime=c_colort4($EntryTime,$date,$RankCode);
						}else
						{
							$EntryTime=c_colort2($EntryTime,$date,$RankCode);
						}
						*/
						/*
						echo $EntryTime."<br>";
						echo $date."<br>";
						echo $RankCode."<br>";
						echo $MemberNo."<br>";
						echo "--------------<br>";
						*/
						/*
						if($memberID=="T03225")
						{
							$EntryTime=time_state($EntryTime,$date,$RankCode,$MemberNo);
						}else
						{
							$EntryTime=c_colort5($EntryTime,$date,$RankCode,$MemberNo);
						}
						*/

						$EntryTime=time_state($EntryTime,$date,$RankCode,$MemberNo);



						//외출상태 표시
						if(strpos($MemberNoArr, $MemberNo) !== false) {

								$sql_out="select a.*,b.ProjectNickName,b.projectViewCode from
								(
									select * from view_official_planout_tbl where MemberNo ='$MemberNo' and o_start like '$date%'
								)a left join
								(
									select ProjectCode,ProjectNickName,projectViewCode from project_tbl
								)b on a.ProjectCode=b.ProjectCode";

								//echo $sql_out."<br>";
								$re_out = mysql_query($sql_out,$db);
								$re_out_num = mysql_num_rows($re_out);
								if($re_out_num != 0){ //외출한 로그있으면
								
									while($re_out_row = mysql_fetch_array($re_out)){
										$td_array2[0]=$re_out_row[ProjectCode];
										$td_array2[projectViewCode]=$re_out_row[projectViewCode];
										$td_array2[ProjectNickname]=utf8_strcut($re_out_row[ProjectNickName],8,'..');
										$td_array2[1]="&nbsp;외출";
										$td_array2[2]=$re_out_row[o_object];
										if (substr($re_out_row[o_end],11,8) == "00:00:00"){
											$td_array2[3]="<font color=#FF6600>복귀시간없음</font>";

											if($re_row[LeaveTime_Is]=="18:18"){
												$re_row[LeaveTime_Is]="-:-";
											}
											
											

										}
										else{
											$td_array2[3]=sec_time00(strtotime($re_out_row[o_end])-strtotime($re_out_row[o_start]));
										}
										$td_array2[row_num]=$num;


										array_push($query_data2,$td_array2);

										$tr_count2=$tr_count2+1;

									}
								}

						}//외출상태 표시
					}
				}
				$re_row[EntryPCode_Is]=$ProjectCode;

				if($OverTime == "00:00"){ $OverTime="";}
				//if($LeaveTime == "00:00" ){ $LeaveTime="";}
				if($LeaveTime_full == "0000-00-00 00:00:00" ){ $LeaveTime="";}

				$re_row[num]=$num;

				$re_row[EntryTime_Is]=$EntryTime;
				$re_row[OverTime_Is]=$OverTime;
				$re_row[LeaveTime_Is]=$LeaveTime;


				//$re_row[ProjectNickname]=utf8_strcut($ProjectNickname,12,'...');
				$re_row[ProjectNickname]=strcut_utf8($ProjectNickname,16,'...');

				$re_row[EntryJobCode_Is]=$EntryJobCode;
				if ($UseState == 18){ //시차
					$re_row[EntryJob_Is]=utf8_strcut($EntryJob,41,'...');
				}else
				{
					$re_row[EntryJob_Is]=utf8_strcut($EntryJob,17,'...');
				}


				if($modify=="1"){$re_row[modify_Is]="○";
				}else{ $re_row[modify_Is]="";}

				//추가근무
				$tr_count = substr_count($Note,"<br>");
				if($tr_count > 0) {
					$td_array = explode("<br>",$Note);
					for($k=0;$k<$tr_count;$k++) {
						$td_array2 = explode("<|>",$td_array[$k]);


						$ProjectCode = change_XX($td_array2[0]);

						$sqlp="select * from project_tbl where ProjectCode ='$ProjectCode'";

						$rep = mysql_query($sqlp,$db);
						$re_nump = mysql_num_rows($rep);
						if($re_nump != 0)
							$ProjectNickname = mysql_result($rep,0,"ProjectNickname");
						else
							$ProjectNickname ="";

						$td_array2[job]=$td_array2[2];
						$td_array2[2]=utf8_strcut($td_array2[2],12,'...');
						$td_array2[ProjectNickname]=utf8_strcut($ProjectNickname,12,'...');
						$td_array2[row_num]=$num;
						$td_array2[projectViewCode]	= projectToColumn($ProjectCode,'projectViewCode');

						array_push($query_data2,$td_array2);
					}

				}


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

			$this->display("intranet/common_contents/work_login/login_board_mvc.tpl");
		}


		//============================================================================
		// 근태이력 표시
		//============================================================================
		function LoginBoardList_200205()
		{
			global $db;
			global $sel_year,$sel_month,$sel_day,$GroupCode,$sub_index,$memberID;

			global $CompanyKind;

			/*
			if($_SESSION['auth_ceo']){//임원
				$this->assign('auth_ceo',true);
				//echo $_SESSION['auth_ceo']."true";
			}else{
				$this->assign('auth_ceo',false);
				//echo $_SESSION['auth_ceo']."false";
			}

			if($_SESSION['auth_depart']){//부서장
				$this->assign('auth_depart',true);
			}else{
				$this->assign('auth_depart',false);
			}
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


			//이상훈 전체근태 조회가능 18.08.29 최은영과장요청
			if($memberID=="B18309")
				$this->assign('auth_ceo',true);


			$query_data = array();
			$query_data2 = array();

			$uyear = date("Y")+1;



			if($sel_year=="") $sel_year=date("Y");
			if($sel_month=="") $sel_month=date("m");
			if($sel_day=="") $sel_day=date("d");

			$last_day = date("t",mktime(0,0,0,$sel_month,1,date("Y")));
			$last_day =$last_day +1;


			$this->assign('uyear',$uyear);
			$this->assign('last_day',$last_day);
			$this->assign('sel_year',$sel_year);
			$this->assign('sel_month',$sel_month);
			$this->assign('sel_day',$sel_day);

			$date=sprintf('%04d-%02d-%02d',$sel_year,$sel_month,$sel_day);

			$holy_sc = holy($date);
			if($GroupCode=="")
				$GroupCode=$_SESSION['MyGroupCode'];



			if($GroupCode=="all")
			{
				$presql="select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) order by GroupCode,RankCode,JuminNo ";
			}else
			{
				if($GroupCode=="003")
				{
					$presql="select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and GroupCode='$GroupCode' order by GroupCode,RealRankCode,RankCode,EntryDate ";
				}else
				{
					$presql="select * from member_tbl where (WorkPosition = 1 or WorkPosition = 4) and GroupCode='$GroupCode' order by GroupCode,RankCode,EntryDate ";
				}
			}



				$sql = "select a.MemberNo as MemberNo,
					a.korName as korName,
					a.Name as Name,
					a.RankCode as RankCode,
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
					b.Note as Note,
					b.modify as modify,
					b.ProjectNickname as ProjectNickname,
					b.projectViewCode as projectViewCode,
					b.NewProjectCode as NewProjectCode
				from
				(
						select * from
						("
							.$presql."
						)a1 left JOIN
						(
							select * from systemconfig_tbl where SysKey='PositionCode'
						)a2 on a1.RankCode = a2.code

				)a left JOIN
				(
						select * from
						(
							select * from view_dallyproject_tbl where EntryTime like '%$date%'
						)c1 left JOIN
						(

							select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl
						)c2 on (c1.EntryPCode = c2.ProjectCode)

				)b on a.MemberNo = b.MemberNo ";
			//echo $sql."<Br>";

			$re = mysql_query($sql,$db);
			$num=1;
			while($re_row = mysql_fetch_array($re))
			{
				$MemberNo=$re_row[MemberNo];
				$RankCode=$re_row[RankCode];
				$EntryTime=$re_row[EntryTime];
				$OverTime=$re_row[OverTime];
				$LeaveTime=$re_row[LeaveTime];
				$LeaveTime_full=$re_row[LeaveTime];

				$EntryTime=substr($EntryTime,11,5);
				$OverTime=substr($OverTime,11,5);
				$LeaveTime=substr($LeaveTime,11,5);


				$projectViewCode=$re_row[projectViewCode];

				if($re_row[LeavePCode] =="")
				{

					$EntryPCode=$re_row[EntryPCode];
					$EntryJobCode=$re_row[EntryJobCode];
					$EntryJob=$re_row[EntryJob];
					$ProjectNickname=$re_row[ProjectNickname];
				}else
				{
					$EntryPCode=$re_row[LeavePCode];
					$EntryJobCode=$re_row[LeaveJobCode];
					$EntryJob=$re_row[LeaveJob];
				}


				if(change_XXIS($EntryPCode))
				{
					$ProjectCodeXX = change_XX($EntryPCode);
					$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$EntryPCode' or ProjectCode='$ProjectCodeXX'";
				}else
				{
					$ProjectCodeXX ="";
					$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$EntryPCode'";
				}


				$re3				= mysql_query($sql3,$db);

				$projectViewCode=@mysql_result($re3,0,"projectViewCode");
				$re_row[projectViewCode]=$projectViewCode;
				$ProjectNickname=@mysql_result($re3,0,"ProjectNickname");

				//echo $projectViewCode."<br>";

				$Note=$re_row[Note];
				$modify=$re_row[modify];


				$sql2 = "select a.*,b.Name as StateName from
						(
							select * from view_userstate_tbl where (start_time <= '$date' and end_time >= '$date') and MemberNo = '$MemberNo' and state <>15
						) a left JOIN
						(
							select * from systemconfig_tbl where SysKey = 'UserStateCode'
						)b on a.state = b.Code order by a.num	";

				//echo $sql2."<Br>";
				$re2 = mysql_query($sql2,$db);
				$re_num= mysql_num_rows($re2);

				if($re_num > 0) {  //휴가파견있으면
					if($EntryPCode =="")
					{
						$ProjectCode = mysql_result($re2,0,"ProjectCode");
						if(change_XXIS($ProjectCode))
						{
							$ProjectCodeXX = change_XX($ProjectCode);
							$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$ProjectCode' or ProjectCode='$ProjectCodeXX'";
						}else
						{	$ProjectCodeXX = "";
							$sql3="select ProjectCode,projectViewCode,NewProjectCode,ProjectNickname from Project_tbl where ProjectCode='$ProjectCode'";
						}



						//echo $sql3."<Br>";
						$re3				= mysql_query($sql3,$db);
						$re_row[projectViewCode]=$projectViewCode;
						$ProjectNickname=@mysql_result($re3,0,"ProjectNickname");
						$ProjectCode=@mysql_result($re3,0,"projectViewCode");
					}
					else
					$ProjectCode = $EntryPCode;
					$UseState = mysql_result($re2,0,"state");
					$note = mysql_result($re2,0,"note");
					$note = str_replace(" ","",$note);

					$StateName = mysql_result($re2,0,"StateName");

					if(strpos($note,"오전반차") !== false)
					{
						$EntryTime2 = "<A href=# title=$EntryTime>"."<font color=blue>"."오전반차"."</font>"."</A>";
					}else if(strpos($note,"오후반차") !== false )
					{	$EntryTime2 =$EntryTime;
						$LeaveTime = "<A href=# title=$LeaveTime>"."<font color=blue>"."오후반차"."</font>"."</A>";
					}else
					{
						if($UseState != 9) { //파견이외
							if($UseState == 15) {  //로그인파견
								$EntryTime2 = $EntryTime;
								if($EntryTime =="")
								{
									$ProjectCode="";
								}

							}else
							{
								$EntryTime2 = "<A href=# title='$EntryTime'>"."<font color=blue>".$StateName."</font>"."</A>";
							}
						} else {
							$EntryTime2 = "<font color=blue>".$StateName."</font>";
						}
					}

					$EntryTime=$EntryTime2;
					if($UseState != 15) {  //로그인파견
						if($StateName == "감리대기"){
							$EntryJobCode="<font color=red>".$StateName."</font>";
						}
						else{
							$EntryJobCode="<font color=#6a7e82>".$StateName."</font>";
						}
						
						//$EntryJobCode="<font color=#6a7e82>".$StateName."</font>";
						$EntryJob=$note ;
					}


				}else
				{
					$ProjectCode = $EntryPCode;
					$UseState = "";

					if($RankCode<="C7" || $holy_sc == "holyday")
					{}
				    else
					{
						//2017-08-18 김도훈 09:30출근 임시  //2018-04-10 이광태 09:30 출근 //2018-05-02 김세열 B18213 류한솔   201901~02  손희창10시 03 09:30

						if($MemberNo=="J15205" || $MemberNo=="M13301" || $MemberNo=="J15306" || $MemberNo=="B18213" || $memberID=="M05205")
						{
							$EntryTime=c_colort4($EntryTime,$date,$RankCode);
						}else
						{
							$EntryTime=c_colort2($EntryTime,$date,$RankCode);
						}

						$EntryTime=c_colort5($EntryTime,$date,$RankCode,$MemberNo);
					}

				}




				$re_row[EntryPCode_Is]=$ProjectCode;

				/*
				if(change_XXIS($ProjectCode))
				{
					$ProjectCode = change_XX($ProjectCode);
				}

				$sqlp="select * from project_tbl where ProjectCode ='$ProjectCode'";


				$rep = mysql_query($sqlp,$db);
				$re_nump = mysql_num_rows($rep);
				if($re_nump != 0)
					$ProjectNickname = mysql_result($rep,0,"ProjectNickname");
				else
					$ProjectNickname ="";

				*/
				if($OverTime == "00:00"){ $OverTime="";}
				//if($LeaveTime == "00:00" ){ $LeaveTime="";}
				if($LeaveTime_full == "0000-00-00 00:00:00" ){ $LeaveTime="";}

				$re_row[num]=$num;

				$re_row[EntryTime_Is]=$EntryTime;
				$re_row[OverTime_Is]=$OverTime;
				$re_row[LeaveTime_Is]=$LeaveTime;


				//$re_row[ProjectNickname]=utf8_strcut($ProjectNickname,12,'...');
				$re_row[ProjectNickname]=strcut_utf8($ProjectNickname,16,'...');

				$re_row[EntryJobCode_Is]=$EntryJobCode;
				$re_row[EntryJob_Is]=utf8_strcut($EntryJob,17,'...');




				if($modify=="1"){$re_row[modify_Is]="○";
				}else{ $re_row[modify_Is]="";}


				//추가근무
				$tr_count = substr_count($Note,"<br>");
				if($tr_count > 0) {
					$td_array = explode("<br>",$Note);
					for($k=0;$k<$tr_count;$k++) {
						$td_array2 = explode("<|>",$td_array[$k]);


						$ProjectCode = change_XX($td_array2[0]);

						$sqlp="select * from project_tbl where ProjectCode ='$ProjectCode'";

						$rep = mysql_query($sqlp,$db);
						$re_nump = mysql_num_rows($rep);
						if($re_nump != 0)
							$ProjectNickname = mysql_result($rep,0,"ProjectNickname");
						else
							$ProjectNickname ="";

						$td_array2[job]=$td_array2[2];
						$td_array2[2]=utf8_strcut($td_array2[2],12,'...');
						$td_array2[ProjectNickname]=utf8_strcut($ProjectNickname,12,'...');
						$td_array2[row_num]=$num;
						$td_array2[projectViewCode]	= projectToColumn($ProjectCode,'projectViewCode');

						array_push($query_data2,$td_array2);
					}

				}


				//외출상태 표시
				/* /////////////////////////////////////////////////////////////////// */
				$sql_out="select a.*,b.ProjectNickName from
				(
					select * from view_official_plan_tbl where o_change='1' and MemberNo like '%$MemberNo%' and o_start like '$date%' order by o_change,o_start, o_end, o_area DESC
				)a left join
				(
					select * from project_tbl
				)b on a.ProjectCode=b.ProjectCode";
//echo $sql_out."<br>";


				$re_out = mysql_query($sql_out,$db);
				$re_out_num = mysql_num_rows($re_out);
				if($re_out_num != 0) //외출한 로그있으면
				{
					while($re_out_row = mysql_fetch_array($re_out))
					{

						$td_array2[0]=$re_out_row[ProjectCode];
						$td_array2[ProjectNickname]=utf8_strcut($re_out_row[ProjectNickName],8,'..');
						$td_array2[1]="&nbsp;&nbsp;외출";
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

						$td_array2[projectViewCode]	= projectToColumn($td_array2[0],'projectViewCode');

						if($td_array2[ProjectNickname] =="")
						{
								$sql3=" select * from project_tbl where ProjectViewCode='$td_array2[projectViewCode]'";
								$re3				= mysql_query($sql3,$db);
								$td_array2[ProjectNickname]	= @mysql_result($re3,0,"ProjectNickname"); 			//프로젝트 닉네임
						}

						//echo $td_array2[0]."-".$td_array2[projectViewCode]."<br>";
						array_push($query_data2,$td_array2);

						$tr_count=$tr_count+1;

					}
				}

				/* /////////////////////////////////////////////////////////////////// */


				$re_row[rowcount]=$tr_count+1;




				array_push($query_data,$re_row);
				$num++;
			}

			if($sub_index == "")
				$sub_index=$GroupCode;

			$tab_index=$GroupCode;

			$GroupList = array();

			$Group_Row="0";
			//if($_COOKIE['CK_CompanyKind']=="JANG")
			if($CompanyKind=="JANG")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'07' and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="PILE")
				$sql2="select * from systemconfig_tbl where SysKey = 'GroupCode'  and Code<>'99' order by orderno  asc";
			else if ($CompanyKind=="HANM")
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
  			   $re_row2[Name]="";;
				array_push($GroupList,$re_row2);
			}

			/* For 코드사용분기 Start  ******************* */
			/* 장헌산업(JANG),파일테크(PILE),바론컨설턴트(HANM)*/
			//$CK_CompanyKind = $_COOKIE['CK_CompanyKind'];
//echo ".".$CompanyKind;
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

			$this->display("intranet/common_contents/work_login/login_board_mvc.tpl");
		}




		//============================================================================
		// 근태현황 표시
		//============================================================================
		function LoginBoardReport()
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
											if($CompanyKind=="PILE" || $CompanyKind=="HANM" ){
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
												if($CompanyKind=="PILE" || $CompanyKind=="HANM" ){
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

								if($CompanyKind=="PILE" || $CompanyKind=="HANM" ){
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

			$this->display("intranet/common_contents/work_login/login_report_mvc.tpl");
		}

}

// 끝
//==================================
?>