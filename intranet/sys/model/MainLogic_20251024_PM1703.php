<?php
	/* ***********************************
	* 초기화면 로딩내용 관련
	* 2015-      :
	* 2015-03-10 : 최근업무내용(LoginInfo()) : 소스코드 고도화 : SUK
	* 2015-01-12 : 최근업무내용(LoginInfo())쿼리추가 : AND D.EntryTime <> DATE_FORMAT(D.EntryTime, '%Y-%m-%d 00:00:00')
	* 2015-01-09 : 업무상태 관련 소스 추가 : $Status=6
	* 2014-12-24 : '최근업무내용' 목록 : LoginInfo() : 프로젝트 코드 및 닉네임 셀렉트 관련 수정 작업 : SUK
	* 2014-12-19 : '공지사항' 목록에 사용되는 function SelectDataList1() : 함수내 쿼리변경 : ORDER BY id DESC : SUK
	* 2014-12-18 : 세션값을 쿠키값으로 대체(/sys/inc/getCookieOfUser.php : 파일생성) : SUK
	* 2014-12-18 : php.날짜변수 관련 공통 페이지 삽입(/sys/inc/getNeedDate.php : 파일생성) : SUK
	* 2014-12-16 : 파일정리 : SUK
	*************************************** */
	/* ------------------------------------ */
	require('../SmartyConfig.php');
	require_once($SmartyClassPath);
	/* ------------------------------------ */
	require('./sys/inc/function_intranet.php');	//자주쓰는 기능 Function
	require('./sys/inc/function_erp.php');
	/* ------------------------------------ */
	include "./sys/inc/getCookieOfUser.php";	//사용자에 관한 쿠키값
	include "./sys/inc/getNeedDate.php";		//로직에 사용되는 PHP시간&날짜 정의

	/* ------------------------------------ */
?>
<?php
	extract($_GET);
		$MemberNo	=	"";	//사원번호
	if($_SESSION['SS_memberID']!=""){
		/* SET SESSION ----------------------- */
		$MemberNo   =   $_SESSION['SS_memberID'];		//사원번호
		$memberID	=   $_SESSION['SS_memberID'];		//사원번호

		$CompanyKind=   $_SESSION['SS_CompanyKind'];//장헌산업(JANG),파일테크(PILE),바론컨설턴트(HANM)
		$korName	=	$_SESSION['SS_korName'];	//한글이름
		$RankCode	=	$_SESSION['SS_RankCode'];	//직급코드
		$GroupCode	=	$_SESSION['SS_GroupCode'];	//부서코드
		$SortKey	=	$_SESSION['SS_SortKey'];	//직급+부서코드
		$EntryDate	=	$_SESSION['SS_EntryDate'];	//입사일자
		$position	=	$_SESSION['SS_position'];	//직위명
		$GroupName	=	$_SESSION['SS_GroupName'];	//부서명
		$ExtNo		=	$_SESSION['SS_ExtNo'];		//내선번호

	}else if($_SESSION['CK_memberID']!=""){				//쿠키값 유무확인
		//쿠키정보 세션으로 대체 250626 김진선
		/* SET COOKIE --------------------------------- */
		$MemberNo   =   $_SESSION['CK_memberID'];	//사원번호
		$memberID	=   $_SESSION['CK_memberID'];	//사원번호

		$CompanyKind=	$_SESSION['CK_CompanyKind'];	//회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		$korName	=	$_SESSION['CK_korName'];		//한글이름
		$RankCode	=	$_SESSION['CK_RankCode'];	//직급코드
		$GroupCode	=	$_SESSION['CK_GroupCode'];	//부서코드
		$SortKey	=	$_SESSION['CK_SortKey'];		//직급+부서코드
		$EntryDate	=	$_SESSION['CK_EntryDate'];	//입사일자
		$position	=	$_SESSION['CK_position'];	//직위명
		$GroupName	=	$_SESSION['CK_GroupName'];	//부서명
		$ExtNo		=	$_SESSION['CK_ExtNo'];		//내선번호
	}else{
		/* ----------------------------------- */
		$memberID	=	$_GET['memberID'];
		$MemberNo	=	$memberID;
		$MemberNo	=	strtoupper($MemberNo);
		/* ----------------------------------- */
		require('./sys/popup/setInfo.php');
		/* ----------------------------------- */
	}//if End
	/* ----------------------------------- */
	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
	/* ----------------------------------- */
	$WorkPosition = getWorkPositionByMemberNo($memberID); //워크포지션(WorkPosition)
?>
<?php
class MainLogic extends Smarty {
	// 생성자
	function MainLogic()
	{
		global $SmartyClass_TemplateDir;
		global $SmartyClass_CompileDir;
		global $SmartyClass_ConfigDir;
		global $SmartyClass_CacheDir;

		$this->Smarty();

		$this->template_dir		=$SmartyClass_TemplateDir;
		$this->compile_dir		=$SmartyClass_CompileDir;
		$this->config_dir		=$SmartyClass_ConfigDir;
		$this->cache_dir		=$SmartyClass_CacheDir;


	}//Main End
	/* ****************************************************************** */
	//소멸자
	function __destruct(){
		global $db;
		mysql_close($db);
	}

	/* ******************************************************************************************* */
	/* 오늘의 업무계획 /////////////////////////////////////////////////////////////////////////// */
	function SelectWorkPlan()
	{
		/* ----------------------------------- */
		global $db;
		global $memberID,$CompanyKind;
		/* ----------------------------------- */
		global $shortY;
		//$memberID='TADMIN';
		/* ----------------------------------- */


		if(DevConfirm($memberID)){
			//echo $memberID.'=';
			//echo 'shortY='.$shortY;
			$this->assign('DevYN','Y');
		}

		$Status    = "1";
		$StartTime = date("H:i");
		$Today     = date("Y-m-d");

		if($memberID=="B14306" || $memberID=="M21420"){
			//$Today     = date("Y-m-d", strtotime("+1 day", strtotime($Today)));
			//echo $memberID.$Today;
			//exit();
		}


		$workout_Count=0;	//업무종료 미입력 횟수 변수 선언 초기값 0


		/* ----------------------------------- */
		if($StartTime < "06:00") {           /// 6시 이전은 어제날짜로 처리
			$Today = find_lastday($Today);
		}
		/* ----------------------------------- */
		$FiveDay= find_sevendays1($Today);   /// 오늘날짜에서 5일전 구하는곳
		/* ----------------------------------- */
		$sql = " SELECT															";
		$sql.= "	 a.EntryTime as EntryTime_full								";	//업무시작 시간
		$sql.= "	,DATE_FORMAT(a.EntryTime, '%Y-%m-%d') as EntryTime			";	//업무시작 시간
		$sql.= "	,(cast((DATE_FORMAT(a.EntryTime,'%H:%i:%s')) as char))  as EntryTime_hms		";	//시분초 00:00:00  /*2015-01-09추가*/
		$sql.= "	,DATE_FORMAT(a.OverTime, '%Y-%m-%d')  as OverTime			";	//연장근무시작 시간
		$sql.= "	,DATE_FORMAT(a.LeaveTime, '%Y-%m-%d') as LeaveTime			";	//업무종료 시간

 		$sql.= "	,a.EntryPCode												";	//프로젝트코드
 		$sql.= "	,a.EntryJobCode												";	//프로젝트서브코드
 		//$sql.= "	,b.ProjectNickname as ProjectNickname						";	//프로젝트 닉네임
		$sql.= "	,(select bb.ProjectNickname from Project_tbl bb where a.EntryPCode = replace(bb.ProjectCode, 'XX','".$shortY."') LIMIT 1) as ProjectNickname 	";	//프로젝트 닉네임
 		$sql.= "	,a.EntryJob													";	//업무내용

 		$sql.= "	,a.OverWorkIP												";	//
 		$sql.= "	,a.EndWorkIP												";	//

		$sql.= "	,a.LeavePCode                         						";	//프로젝트코드
		$sql.= "	,a.LeaveJobCode                       						";	//프로젝트서브코드
		$sql.= "	,(select  bb.ProjectNickname from Project_tbl bb where a.LeavePCode = replace(bb.ProjectCode, 'XX','".$shortY."') LIMIT 1) as ProjectNickname2 	";	//프로젝트 닉네임
		$sql.= "	,a.LeaveJob                           						";	//업무내용

		$sql.= " from															";
		$sql.= " (																";
		$sql.= "	select * from dallyproject_tbl								";
		$sql.= "	where														";
		$sql.= "	MemberNo = '".$memberID."'									";
		//$sql.= "	and EntryTime > '".$FiveDay." 00:00:00'						";
		$sql.= "	and EntryTime < '".$Today." 23:59:59'						";
		$sql.= "	order by EntryTime Desc limit 1								";
		$sql.= " ) a															";
		//$sql.= " left JOIN														";

		//if(DevConfirm($memberID)){
		//	$sql.= " ( select * from Project_tbl )b						";
		//}else{
		//	$sql.= " ( select * from Project_tbl )b								";
		//}

		//$sql.= " on a.EntryPCode = replace(b.ProjectCode, 'XX','".$shortY."')	";
		//$sql.= " on a.LeavePCode = replace(b.ProjectCode, 'XX','".$shortY."')	";

		// if($memberID == "M23047")echo $sql;
		/* ----------------------------------- */
		$result = mysql_query($sql,$db);
		$result_num = mysql_num_rows($result);
		/* ----------------------------------- */



if(DevConfirm($memberID)){
	//echo 'memberID='.$memberID.'<br>';
	//echo 'sql='.$sql;
}

		/* ----------------------------------- */
		//오늘날짜 이전 5일치 데이터 조회 : 업무복귀후 업무시작할 때 직전 휴가가 6일 이상이었을 경우 프로젝트 코드 다시 입력해야함, 그외에는 검색결과 프로젝트 코드로 자동 셋팅
		if($result_num != 0) {
			/* ------------------------------------------------------------- */
			//WP = WorkPlan

			$OverWorkIP			= mysql_result($result,0,"OverWorkIP"); 		//연장근무시작 IP
			$EndWorkIP			= mysql_result($result,0,"EndWorkIP"); 			//업무종료 IP

			$EntryTime_full     = mysql_result($result,0,"EntryTime_full"); 	//업무시작 시간
			$EntryTime			= mysql_result($result,0,"EntryTime"); 			//업무시작 시간
			$OverTime			= mysql_result($result,0,"OverTime");			//연장근무시작 시간
			$LeaveTime			= mysql_result($result,0,"LeaveTime");			//업무종료시작 시간

			$code_EntryPCode	= mysql_result($result,0,"EntryPCode");			//프로젝트코드
			$EntryJobCode		= mysql_result($result,0,"EntryJobCode");		//프로젝트서브코드
			$ProjectNickname	= mysql_result($result,0,"ProjectNickname");	//프로젝트 닉네임
			$EntryJob			= mysql_result($result,0,"EntryJob");			//업무내용

			$code_EntryPCode2	= mysql_result($result,0,"LeavePCode");			//프로젝트코드
			$EntryJobCode2		= mysql_result($result,0,"LeaveJobCode");		//프로젝트서브코드
			$ProjectNickname2	= mysql_result($result,0,"ProjectNickname2");	//프로젝트 닉네임
			$EntryJob2			= mysql_result($result,0,"LeaveJob");			//업무내용

			$EntryTime_hms = mysql_result($result,0,"EntryTime_hms"); //시분초 00:00:00  2015-01-09추가

			if(DevConfirm($memberID)){

				$projectViewCode	= projectToColumn($code_EntryPCode,'projectViewCode',DevConfirm($memberID));		//프로젝트코드 ViewCode
				$NewProjectCode	= projectToColumn($code_EntryPCode,'NewProjectCode',DevConfirm($memberID));		//프로젝트코드 NewProjectCode
// 				echo '<br><br>'.'code_EntryPCode='.$code_EntryPCode.'<br>';
// 				echo '<br>projectToColumn : projectViewCode ='.$projectViewCode;
// 				echo '<br>projectToColumn : NewProjectCode ='.$NewProjectCode;
			}else{
				$projectViewCode	= projectToColumn($code_EntryPCode,'projectViewCode');		//프로젝트코드 ViewCode
				$NewProjectCode	= projectToColumn($code_EntryPCode,'NewProjectCode');		//프로젝트코드 NewProjectCode
			}


			/* 업무미시작------------------------------------------------------------- */
			if($Today != $EntryTime && $Today != $OverTime && $Today != $LeaveTime ){
				$Status="1";		//업무시작X : 연장근무X : 업무종료X
				$Status_detail="업무시작X : 연장근무X : 업무종료X";
			} //if End
			/* 업무시작------------------------------------------------------------- */
			if($Today == $EntryTime && $Today != $OverTime && $Today != $LeaveTime){
				$Status="2";		//업무시작0 : 연장근무X : 업무종료X
				$Status_detail="업무시작0 : 연장근무X : 업무종료X";
			} //if End
			/* 업무시작 후 연장근무 중인 상태 ------------------------------------------------------------- */
			if($Today == $OverTime && $Today == $OverTime && $Today != $LeaveTime){ //연장근무0 : 업무종료 X
				$Status="3";		//업무시작0 : 연장근무0 : 업무종료X
				$Status_detail="업무시작0 : 연장근무0 : 업무종료X";
			} //if End
			/*업무시작 후 연장근무하고 업무종료 상태 -------------------------------------------------------- */
			if($Today == $EntryTime && $Today == $OverTime && $Today == $LeaveTime  ){
				$Status="4";   //업무시작0 : 연장근무0 : 업무종료0
				$Status_detail="업무시작0 : 연장근무0 : 업무종료0";
			} //if End
			/*업무시작 후 업무종료상태(연장근무안함) ---------------------------------------------------------- */
			if($Today == $EntryTime && $Today != $OverTime && $Today == $LeaveTime  ){
				$Status="5";   //업무시작0 : 연장근무X : 업무종료0
				$Status_detail="업무시작0 : 연장근무X : 업무종료0";
			} //if End

			/* 2015-01-09추가 Start*** */
			if( (($Today == $EntryTime && $EntryTime_hms=="00:00:00")&& $Today != $OverTime && $Today != $LeaveTime ) ) {
				$Status="6";   //업무시작0 : 연장근무X : 업무종료0
				$Status_detail="업무시작가능 : 출장 및 기타사유로 EntryTime에 현재날짜와 시분초가 00:00:00 로 임의 저장되어 있는 상태, 업무시작X : 연장근무X : 업무종료X";
			} //if End
			/* 2015-01-09추가 End*** */


// 			if(DevConfirm($memberID)){
// 				/*업무시작 후 연장근무하고 업무종료 상태 -------------------------------------------------------- */
// 				if($Status=="4" && $OverWorkIP!="" && $EndWorkIP!="" ){
// 					$Status="11";   //업무시작0 : 연장근무0 : 업무종료0 : 연장근무종료0
// 					$Status_detail="업무시작0 : 연장근무0 : 업무종료0 : 연장근무종료0";
// 				} //if End
// 			}


			/*--------------------------------------------------------------- */
		} //if End
		/* ------------------------------------------------------------- */

		$addList = array();
		/* ------------------------------------------------------------- */
		$sql_addWorkList    = "select Note from dallyproject_tbl where MemberNo = '".$memberID."' and  EntryTime like '".$Today."%'";
		/* ------------------------------------------------------------- */
		//echo $sql_addWorkList;
		/* ------------------------------------------------------------- */
		$result_addWorkList = mysql_query($sql_addWorkList,$db);
		/* ------------------------------------------------------------- */
		$result_str = @mysql_result($result_addWorkList,0,"Note"); 			//금일 등록된 추가업무내용 존재여부
			/* ------------------------------------------------------------- */
			if($result_str == '' || $result_str == null ) {  //금일 등록된 Note 없음
				$this->assign('Note_arr_cnt',0);
				$this->assign('Note_cnt',0);
			}else{
				$Note_arr     = explode("<br>",$result_str);
				$Note_arr_cnt = count ($Note_arr); //3개면 3번
				/*----------------------------------------------*/
				$this->assign('Note_arr_cnt',$Note_arr_cnt);
				/*----------------------------------------------*/
				for($i=0;$i<$Note_arr_cnt;$i++){ //3개면 3번
					$Note     = explode("<|>",$Note_arr[$i]);
					$Note_cnt = count ($Note);
					/*----------------------------------------------*/
					for($j=0;$j<$Note_cnt;$j++){ //4개면 4번
						/*----------------------------------------------*/
						array_push($addList,array( 'list_p_code'=>$Note[$j],'list_sub_code' =>$Note[$j],'list_content'  =>$Note[$j],'list_time'=>$Note[$j]));
						/*----------------------------------------------*/
					}//for End
				}//for End
				/* ------------------------------------------------------------- */
				$this->assign("addList",$addList);
				/* ------------------------------------------------------------- */
			}//if End
			/* ------------------------------------------------------------- */
		// *********** WP_= WorkPlan
		$this->assign('WP_EntryTime',$EntryTime);				//업무시작 시간
		$this->assign('WP_OverTime',$OverTime);					//연장근무시작 시간





		if($Status=="1" && $result_num != 0 && FN_Get_OfficeType($memberID)!="10" ){ //업무미시작 && 감리현장 근무자 제외(FN_Get_OfficeType($memberID)!="10")

			if( DevConfirm($memberID)){
				//echo '='.DevConfirm($memberID).'=';
				$test_text = 'EntryJob2='.$EntryJob2.'  code_EntryPCode2=['.$code_EntryPCode2.']  : MainLogic.php : 292';

				$this->assign('test',$test_text);
			}

			//업무미시작
			$this->assign('WP_code_EntryPCode',$code_EntryPCode2);	//프로젝트코드
			$this->assign('WP_EntryJobCode',$EntryJobCode2);		//프로젝트서브코드
			$this->assign('WP_ProjectNickname',$ProjectNickname2);	//프로젝트 닉네임
			$this->assign('WP_EntryJob',$EntryJob2);				//업무내용

			$compare_date_today = date("Y-m-d");
			//-----------------------------------------------------------------
			//-----------------------------------------------------------------
			if($code_EntryPCode2=="" && $EntryJob2=="" && $EntryTime!=$compare_date_today){
				//업무미시작
				$Status="10";
				$this->assign('WP_code_EntryPCode',$code_EntryPCode2);	//프로젝트코드
				$this->assign('WP_EntryJobCode',$EntryJobCode2);		//프로젝트서브코드
				$this->assign('WP_ProjectNickname',$ProjectNickname2);	//프로젝트 닉네임
				$this->assign('WP_EntryJob',$EntryJob2);				//업무내용

				$this->assign('WP_EntryTime_latest',$EntryTime);				//업무종료 데이터 없을경우
				$this->assign('WP_EntryTime_latest_full',$EntryTime_full);		//업무종료 데이터 없을경우 풀시간
				
				$cSql = "
								SELECT WorkOutDate FROM workout_not_tbl where MemberNo='".$memberID."'
								AND WorkOutdate >= '2025-09-01' GROUP BY WorkOutDate
							";
				$cResult = mysql_query($cSql,$db);
				
				
				$workout_Count = mysql_num_rows($cResult);
				//----------------------
			}else{
// 				$this->assign('WP_code_EntryPCode',$code_EntryPCode);	//프로젝트코드
// 				$this->assign('WP_EntryJobCode',$EntryJobCode);			//프로젝트서브코드
// 				$this->assign('WP_ProjectNickname',$ProjectNickname);	//프로젝트 닉네임
// 				$this->assign('WP_EntryJob',$EntryJob);					//업무내용
				$this->assign('WP_code_EntryPCode',$code_EntryPCode2);	//프로젝트코드
				$this->assign('WP_EntryJobCode',$EntryJobCode2);		//프로젝트서브코드
				$this->assign('WP_ProjectNickname',$ProjectNickname2);	//프로젝트 닉네임
				$this->assign('WP_EntryJob',$EntryJob2);				//업무내용

			}

			$projectViewCode	= projectToColumn($code_EntryPCode2,'projectViewCode');		//프로젝트코드 ViewCode
			$NewProjectCode	= projectToColumn($code_EntryPCode2,'NewProjectCode');		//프로젝트코드 NewProjectCode

		}else{

			if( DevConfirm($memberID)){
				//echo '='.DevConfirm($memberID).'=';
				$test_text = $Status.'  : MainLogic.php:346 ';
				$test_text ="";
				$this->assign('test',$test_text);
			}

			$compare_date_today = date("Y-m-d");
			//-----------------------------------------------------------------
			$this->assign('WP_EntryTime_latest',$EntryTime);				//업무시작 시간
			$this->assign('WP_EntryTime_latest_full',$EntryTime_full);		//업무시작 시간
			//-----------------------------------------------------------------
			if(FN_Get_OfficeType($memberID)=="10" ){//감리현장 근무자 <== 새로운 업무종료 적용안함 : 기존 업무종료
				$this->assign('WP_code_EntryPCode',$code_EntryPCode);	//프로젝트코드
				$this->assign('WP_EntryJobCode',$EntryJobCode);			//프로젝트서브코드
				$this->assign('WP_ProjectNickname',$ProjectNickname);	//프로젝트 닉네임
				$this->assign('WP_EntryJob',$EntryJob);					//업무내용
			}else{
				if($Status=="2"){//감리현장 근무자 <== 새로운 업무종료 적용안함 : 기존 업무종료
					$this->assign('WP_code_EntryPCode',$code_EntryPCode);	//프로젝트코드
					$this->assign('WP_EntryJobCode',$EntryJobCode);			//프로젝트서브코드
					$this->assign('WP_ProjectNickname',$ProjectNickname);	//프로젝트 닉네임
					$this->assign('WP_EntryJob',$EntryJob);					//업무내용
				}else{
					$this->assign('WP_code_EntryPCode',$code_EntryPCode2);	//프로젝트코드
					$this->assign('WP_EntryJobCode',$EntryJobCode2);		//프로젝트서브코드
					$this->assign('WP_ProjectNickname',$ProjectNickname2);	//프로젝트 닉네임
					$this->assign('WP_EntryJob',$EntryJob2);				//업무내용

					$projectViewCode	= projectToColumn($code_EntryPCode2,'projectViewCode');		//프로젝트코드 ViewCode
					$NewProjectCode	= projectToColumn($code_EntryPCode2,'NewProjectCode');		//프로젝트코드 NewProjectCode
				}
			}

		}



		$this->assign('WP_OfficeType',FN_Get_OfficeType($memberID));	//프로젝트코드 ViewCode

		$this->assign('WP_projectViewCode',$projectViewCode);	//프로젝트코드 ViewCode
		$this->assign('WP_NewProjectCode',$NewProjectCode);	//프로젝트코드 NewProjectCode







// 		if($Status=="1" && ($memberID=="B14306" || $memberID=="J08305" || $memberID=="T08301" )){
// 			//업무미시작
// 			$this->assign('WP_code_EntryPCode',$code_EntryPCode2);	//프로젝트코드
// 			$this->assign('WP_EntryJobCode',$EntryJobCode2);			//프로젝트서브코드
// 			$this->assign('WP_ProjectNickname',$ProjectNickname2);	//프로젝트 닉네임
// 			$this->assign('WP_EntryJob',$EntryJob2);					//업무내용
// 			if($code_EntryPCode2=="" && $EntryJob2=="" ){
// 				//업무미시작
// 				$Status="10";

// 				$this->assign('WP_EntryTime_latest',$EntryTime);				//업무시작 시간
// 				$this->assign('WP_EntryTime_latest_full',$EntryTime_full);		//업무시작 시간

// 			}
// 		}else{
// 			$this->assign('WP_code_EntryPCode',$code_EntryPCode);	//프로젝트코드
// 			$this->assign('WP_EntryJobCode',$EntryJobCode);			//프로젝트서브코드
// 			$this->assign('WP_ProjectNickname',$ProjectNickname);	//프로젝트 닉네임
// 			$this->assign('WP_EntryJob',$EntryJob);					//업무내용
// 		}



		if(SetNewCodeBoolean("2020-06-01 00:00:00", "")){
			if(FN_Confirm_OutOfficeWorker($memberID) == "Y") {
				//합사파견중
				$Status="100";
			}
		}

// 		if(SetNewCodeBoolean( "2020-05-28 17:20:00","2020-05-28 19:00:00") && $memberID=="B14306"){
// 			if(FN_Confirm_OutOfficeWorker($memberID) == "Y") {
// 				$set_sql = " DELETE FROM USERSTATE_TBL WHERE memberno='B14306' AND (state='20' or state='21'); ";
// 				FN_Scheduled_sql($set_sql,$memberID);
// 			}
// 		}


		if($memberID=="B14306" || $memberID=="M21420"){
			//$Today     = date("Y-m-d", strtotime("+1 day", strtotime($Today)));
//			echo $Status;
			//exit();
		}
		$this->assign('workout_Count',$workout_Count);	//미입력 업무종료 횟수

		$this->assign('WP_Status',$Status);						//상태
		$this->assign('WP_Status_detail',$Status_detail);		//상태상세
		/* ------------------------------------------------------------- */
		$this->assign('WP_memberID',$memberID);					//사원번호
		$this->assign('CompanyKind',$CompanyKind);				//회사종류
		/* ------------------------------------------------------------- */
	}//SelectWorkPlan End
	/* ******************************************************************************************* */

	/* ******************************************************************************************* */
	/* 최근업무내용 ////////////////////////////////////////////////////////////////////////////// */
	function LoginInfo()
	{
	$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장


	/*점검용*/
	$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
	$ipStr  = "218.232.187.68";
	$myCompany   = searchCompanyKind();
	if($myip==$ipStr){
		//echo $myip."===".$myCompany."===<BR>";
	}//if End
	/**/

		/* 최근업무내용 홈화면 리스트*/
		/* *********************
		** 사용 테이블 :  dallyproject_tbl, project_tbl,userstate_tbl, systemconfig_tbl
        ************************/
		global $db;
		global $memberID;
		/* ------------------------------------ */
		global $shortY;
		global $CompanyKind;
		/* ------------------------------------ */
		$resultArrayDate = array();
		$resultArrayDate_cnt=0;
		/*-----------------------*/
		$resultDayColor = array();//일자 컬러
		$resultTimeColor = array();//시간 컬러
		/*-----------------------*/
		$resultStr00 = "";//일자
		$resultStr01 = "";//요일
		$resultStr02 = "";//출근시간
		$resultStr03 = "";//연장근무유무
		$resultStr04 = "";//프로젝트코드
		$resultStr05 = "";//프로젝트 닉네임
		$resultStr06 = "";//업무내용

		$resultStr10 = "";//프로젝트 뷰코드 : 181022
		/*배열값-----------------------*/
		$resultArray00 = array();//일자
		$resultArray01 = array();//요일
		$resultArray02 = array();//출근시간
		$resultArray03 = array();//연장근무유무
		$resultArray04 = array();//프로젝트코드
		$resultArray05 = array();//프로젝트 닉네임
		$resultArray06 = array();//업무내용
		$resultArray07 = array();//업무내용 full

		$resultArray10 = array();//프로젝트 뷰코드 : 181022
		/*-----------------------*/
		for($i=0;$i<31;$i++){
			/*-----------------------*/
			//userstate_tbl조회
			$sql33  =   "	SELECT																										";
			$sql33 .=	"		a.num				as a_num																			";
			$sql33 .=	"		,a.MemberNo		    as a_MemberNo																			";
			$sql33 .=	"		,a.GroupCode		as a_GroupCode																		";
			$sql33 .=	"		,a.state			as a_state																		";
			$sql33 .=	"		,a.start_time		as a_start_time																		";
			$sql33 .=	"	    ,DATE_FORMAT(a.start_time, '%d')	as a_start_timeDD													";	//시작일자 : DD
			$sql33 .=	"	    ,DAYOFWEEK(a.start_time)            as a_dayName														";	//요일(숫자:일(0)~토(6))
			$sql33 .=	"	    ,DATE_FORMAT((current_date() - interval ".$i." day) , '%d')	as a_start_timeDDCompare					";	//일자 : DD
			$sql33 .=	"	    ,(current_date() - interval ".$i." day) as a_dayCompare													";
			$sql33 .=	"	    ,DAYOFWEEK(current_date() - interval ".$i." day)  as a_dayNameCompare									";	//요일(숫자:일(0)~토(6))
			$sql33 .=	"		,a.end_time      as a_end_time																			";
			$sql33 .=	"		,a.ProjectCode   as a_ProjectCode																		";
			$sql33 .=	"		,a.note          as a_note																				";
			$sql33 .=	"		,a.sub_code      as a_sub_code																			";
			$sql33 .=	"		,b.Name			 as b_StateName																			";
			$sql33 .=	"		FROM																									";
			$sql33 .=	"		(																										";
			$sql33 .=	"		SELECT * FROM userstate_tbl																				";
			$sql33 .=	"			WHERE																								";
			$sql33 .=	"				(																								";
			$sql33 .=	"				start_time <= (current_date() - interval ".$i." day)											";
			$sql33 .=	"				AND																								";
			$sql33 .=	"				end_time >= (current_date() - interval ".$i." day)												";
			$sql33 .=	"				)																								";
			$sql33 .=	"				 AND																							";
			$sql33 .=	"				 MemberNo = '".$memberID."'																		";
			$sql33 .=	"				 AND																							";
			$sql33 .=	"				 state <> '15'	and    state <> '18'															";/*조건추가 : 20160114 : state=15 인 인원(파견B)은 출근 등 기타내역을  본사근무자와 동일하게 표시 End */
			$sql33 .=	"		) a left JOIN																							";
			$sql33 .=	"	(																											";
			$sql33 .=	"		SELECT * from systemconfig_tbl where SysKey = 'UserStateCode'											";
			$sql33 .=	"	)b on a.state = b.Code																						";
			/*------------------------------------*/

			if(DevConfirm($memberID)){
				//echo 'sql33 =='.$sql33;
			}

			if($memberID=="M03201"){
				//echo 'sql33 =='.$sql33;
			}


			$re_code33 = mysql_query($sql33,$db);
			$re_num_code33 = mysql_num_rows($re_code33);
			/*------------------------------------*/
			if($re_num_code33 != 0) {

				/*array계산관련 default------------------------------------*/
				$dateOfDay = @mysql_result($re_code33,0,"a_dayCompare");
				array_push($resultArrayDate,$dateOfDay);

				/*일자컬러 지정------------------------------*/
				$holyCheck = holy($dateOfDay); //평일:weekday : holy() : /inc/function_intranet.php파일내 FUNCTION
					if($holyCheck == "weekday"){
						array_push($resultDayColor,"black");
					}else if($holyCheck != "weekday") {
						array_push($resultDayColor,"hotpink");
					}//if End

				/*일자------------------------------------*/
				$resultStr00 = @mysql_result($re_code33,0,"a_start_timeDDCompare");
				array_push($resultArray00,$resultStr00);

				/*요일------------------------------------*/
				$resultStr01 = @mysql_result($re_code33,0,"a_dayNameCompare");
					/*날짜 요일 설정----------------------------------*/
					switch ($resultStr01) {
						case "1":
						 $resultStr01="(일)";
						 break;
						case "2":
						 $resultStr01="(월)";
						  break;
						case "3":
						 $resultStr01="(화)";
						  break;
						case "4":
						 $resultStr01="(수)";
						  break;
						case "5":
						 $resultStr01="(목)";
						  break;
						case "6":
						 $resultStr01="(금)";
						  break;
						case "7":
						 $resultStr01="(토)";
						  break;
						default:
						  echo "";
					} //switch End
				array_push($resultArray01,$resultStr01);

				/*==================================================*/
				/*출근시간 : userstate_tbl=a_note ------------------*/
				$resultStr02 = @mysql_result($re_code33,0,"b_StateName");
				$resultStr02="<font color='blue'><b>".$resultStr02."</b></font>";
				array_push($resultArray02 ,$resultStr02);
				/*==================================================*/

				/*연장근무유무 ------------------------------------*/
				$a_dayCompare = @mysql_result($re_code33,0,"a_dayCompare");
				$resultStr03  = overtimeCheck($a_dayCompare,$memberID,'');
				array_push($resultArray03 ,$resultStr03);

				/*프로젝트코드------------------------------------*/
				$resultStr04 = @mysql_result($re_code33,0,"a_ProjectCode");
				array_push($resultArray04 ,$resultStr04);

				//프로젝트코드 ViewCode : 181022
				$resultStr10	= projectToColumn($resultStr04,'projectViewCode',DevConfirm($memberID));		//프로젝트코드 ViewCode : 181022
				array_push($resultArray10 ,$resultStr10);

				/*프로젝트 닉네임------------------------------------*/
				//$resultStr05 = @mysql_result($re_code33,0,"d_EntryPCode");
				//array_push($resultArray05 ,$resultStr05);

				/*프로젝트 닉네임------------------------------------*/
				$a_ProjectCode = @mysql_result($re_code33,0,"a_ProjectCode");
				$resultStr05 ="";


					/*------------------------------------------------------*/
					/*프로젝트 닉네임 조회--- */
					$ProjectNickname = "";
					if( change_XXIS02($a_ProjectCode,$CompanyKind)){ //리턴값이 true  : XX관련 코드

						if(DevConfirm($memberID)){
							//echo '리턴값이 true  : XX관련 코드 =='.$a_ProjectCode;
						}

						$ProjectCode2 = change_XX02($a_ProjectCode,$CompanyKind); //XX-AA-BB 코드로 변환하여 project_tbl에서 조회가능하도록 한다.
						$sql_code01="SELECT * FROM project_tbl WHERE ProjectCode ='".$ProjectCode2."' ";


						$re_code01 = mysql_query($sql_code01,$db);
						$re_num_code01 = mysql_num_rows($re_code01);
						if($re_num_code01 != 0) {
							$ProjectNickname = mysql_result($re_code01,0,"ProjectNickname");
							$resultStr05     = utf8_strcut($ProjectNickname,8,'..');

						}else{
							$resultStr05 = "&nbsp;";
						}//if End
					}else{ //리턴값이 false : 일반프로젝트 코드
						$sql_code02="SELECT * FROM project_tbl WHERE ProjectCode ='".$a_ProjectCode."' ";
						/*------------------------------------*/
						$re_code02 = mysql_query($sql_code02,$db);
						$re_num_code02 = mysql_num_rows($re_code02);
						/*------------------------------------*/
						if($re_num_code02 != 0) {
							$ProjectNickname = mysql_result($re_code02,0,"ProjectNickname");
							$resultStr05  = utf8_strcut($ProjectNickname,8,'..');
							/*------------------------------------*/
						}else{
							$resultStr05 = "&nbsp;";
						}//if End
					}//if End
					/*프로젝트 닉네임 조회 End--*/
					/*------------------------------------------------------*/
				array_push($resultArray05 ,$resultStr05);

				/*업무내용_short------------------------------------*/
				$a_note = @mysql_result($re_code33,0,"a_note");
				$resultStr06  = utf8_strcut($a_note,19,'..');
				array_push($resultArray06 ,$resultStr06);
				array_push($resultArray07 ,@mysql_result($re_code33,0,"a_note"));
			}else{
				/*-----------------------*/
				$sql99  = "	SELECT																			";
				$sql99 .= "	 D.MemberNo											as d_MemberNo				";	//사원번호
				$sql99 .= "	,D.EntryTime										as d_EntryTime				";	//업무시작 시간
				$sql99 .= "	,D.LeaveTime										as d_LeaveTime				";	//업무종료 시간
				$sql99 .= "	,D.OverTime											as d_OverTime				";	//연장근무시작 시간
				$sql99 .= "	,DATE_FORMAT(D.EntryTime, '%Y-%m-%d')				as d_ViewDate				";	//업무시작일자 : YYYY-MM-DD
				$sql99 .= "	,DATE_FORMAT(D.EntryTime, '%Y')						as d_ViewDateYYYY			";	//업무시작일자 : YYYY
				$sql99 .= "	,DATE_FORMAT(D.EntryTime, '%m')						as d_ViewDateMM				";	//업무시작일자 : MM
				$sql99 .= "	,DATE_FORMAT(D.EntryTime, '%d')						as d_ViewDateDD				";	//업무시작일자 : DD
				$sql99 .= "	,DATE_FORMAT(D.EntryTime,'%H:%i')					as d_EntryMin				";	//업무시작 시간
				$sql99 .= "	,DATE_FORMAT(D.LeaveTime,'%H:%i')					as d_LeaveMin				";	//업무종료 시간
				$sql99 .= "	,DATE_FORMAT(D.OverTime,'%H:%i')					as d_OverMin				";	//연장근무시작 시간
				$sql99 .= "	,DAYNAME(DATE_FORMAT(D.EntryTime, '%Y-%m-%d'))		as d_DN						";  //요일(English)
				$sql99 .= "	,DAYOFWEEK(DATE_FORMAT(D.EntryTime,'%Y-%m-%d'))     as d_dayName				";  //요일(숫자:일(0)~토(6))
				$sql99 .= "	,D.EntryPCode										as d_EntryPCode				";	//프로젝트코드
				$sql99 .= "	,D.EntryJobCode										as d_EntryJobCode			";	//프로젝트서브코드
				$sql99 .= "	,D.EntryJob											as d_EntryJob				";	//업무내용
				$sql99 .= "	,D.EntryJob											as d_EJ_FULL				";	//업무내용(풀네임)
				$sql99 .= "	,substring(D.EntryJob,1,13)							as d_EJ_change				";	//업무내용
				$sql99 .= "	,D.modify											as d_modify					";	//O/T 승인여부
				$sql99 .= "	,D.Note												as d_Note					";
				$sql99 .= "	,substring(D.SortKey,4,2)							as d_RankCode				";	//직급코드

				$sql99 .= "	,D.LeaveTime										as e_LeaveTime				";	//업무종료 : 종료시간
				$sql99 .= "	,D.LeavePCode										as e_LeavePCode				";	//업무종료 : 프로젝트코드(구)
				$sql99 .= "	,D.LeavePCode2										as e_LeavePCode2			";	//업무종료 : 프로젝트코드(신)
				$sql99 .= "	,D.LeaveJobCode										as e_LeaveJobCode			";	//업무종료 : 프로젝트서브코드
				$sql99 .= "	,D.LeaveJob											as e_LeaveJob				";	//업무종료 : 업무내용
				$sql99 .= "	,D.LeaveJob											as e_EJ_FULL				";	//업무종료 : 업무내용
				$sql99 .= "	,substring(D.LeaveJob,1,13)							as e_EJ_change				";	//업무종료 : 업무내용

				//$sql99 .= " FROM dallyproject_tbl D														";
				$sql99 .= " FROM view_dallyproject_year_tbl D												";
				$sql99 .= " WHERE																			";
				$sql99 .= " D.MemberNo = '".$memberID."'													";
				$sql99 .= " AND																				";
				$sql99 .= " DATE_FORMAT(D.EntryTime, '%Y-%m-%d')  = (current_date() - interval ".$i." day)	";
				/*------------------------------------*/
				$re_code99 = mysql_query($sql99,$db);
				$re_num_code99 = mysql_num_rows($re_code99);
				/*------------------------------------*/
				if($re_num_code99 != 0) {

					/*array계산관련 default------------------------------*/
					$dateOfDay = @mysql_result($re_code99,0,"d_EntryTime");
					array_push($resultArrayDate,$dateOfDay);

					/*일자컬러 지정------------------------------*/
					$d_ViewDate = @mysql_result($re_code99,0,"d_ViewDate");
					$holyCheck = holy($d_ViewDate); //평일:weekday : holy() : /inc/function_intranet.php파일내 FUNCTION
						if($holyCheck == "weekday"){
							array_push($resultDayColor,"black");
						}else{
							array_push($resultDayColor,"hotpink");
						}//if End

					/*일자------------------------------------*/
					$resultStr00 = @mysql_result($re_code99,0,"d_ViewDateDD");
					array_push($resultArray00,$resultStr00);

					/*요일------------------------------------*/
					$resultStr01 = @mysql_result($re_code99,0,"d_dayName");
						/*날짜 요일 설정----------------------------------*/
						switch ($resultStr01) {
							case "1":
							 $resultStr01="(일)";
							 break;
							case "2":
							 $resultStr01="(월)";
							  break;
							case "3":
							 $resultStr01="(화)";
							  break;
							case "4":
							 $resultStr01="(수)";
							  break;
							case "5":
							 $resultStr01="(목)";
							  break;
							case "6":
							 $resultStr01="(금)";
							  break;
							case "7":
							 $resultStr01="(토)";
							  break;
							default:
							  echo "";
						} //switch End
					array_push($resultArray01,$resultStr01);


					//------------------------
					$TV_YN               = "";
					$TV_num              = "";
					$TV_MemberNo         = "";
					$TV_GroupCode        = "";
					$TV_state            = "";
					$TV_start_time       = "";
					$TV_end_time         = "";
					$TV_ProjectCode      = "";
					$TV_NewProjectCode   = "";
					$TV_note             = "";
					$TV_sub_code         = "";
					//------------------------
					$TV_add_msg1		 = "";
					//------------------------
					//시차 발생시
					$TV_array_time_vacation =FN_confirm_time_vacation($d_ViewDate,$memberID);
					if($TV_array_time_vacation[0][re_YN]=="Y"){
						$TV_YN               =  $TV_array_time_vacation[0][re_YN];
						$TV_num              =  $TV_array_time_vacation[0][re_num];
						$TV_MemberNo         =  $TV_array_time_vacation[0][re_MemberNo];
						$TV_GroupCode        =  $TV_array_time_vacation[0][re_GroupCode];
						$TV_state            =  $TV_array_time_vacation[0][re_state];
						$TV_start_time       =  $TV_array_time_vacation[0][re_start_time];
						$TV_end_time         =  $TV_array_time_vacation[0][re_end_time];
						$TV_ProjectCode      =  $TV_array_time_vacation[0][re_ProjectCode];
						$TV_NewProjectCode   =  $TV_array_time_vacation[0][re_NewProjectCode];
						$TV_note             =  $TV_array_time_vacation[0][re_note];
						$TV_sub_code         =  $TV_array_time_vacation[0][re_sub_code];

						$TV_add_msg1="<font color='blue'>[시차:".$TV_sub_code."H]</font>";
					}

					if($memberID=="B14306"){
						//echo $TV_YN;
					}

					/*출근시간------------------------------------*/
					$d_EntryMin = @mysql_result($re_code99,0,"d_EntryMin");
						/* 출근시간 컬러표시 Start : 08시50분~09시: 주황색, 9시넘으면 적색 ------------*/
						$TimeCheck = $d_EntryMin;
						$TCheck = (int)str_replace(":","",$TimeCheck);
						//echo $TCheck."<br>";
						$holyCheck = holy($d_ViewDate); //평일:weekday : holy() : /inc/function_intranet.php파일내 FUNCTION
						if($holyCheck == "weekday"){
								if($memberID=="J15205" || $memberID=="M13301"  || $memberID=="J15306" || $memberID=="B18213" || $memberID=="M05205" || $memberID=="T05308") //2017-08-18 김도훈 09:30출근 임시  //2018-04-10 이광태 09:30 출근 //180502 김세열 B18213 류한솔 || $memberID=="M05205" T05308 정미희 19-03-01손희창 탄력
								{
									if($TCheck >= 920 && $TCheck <= 930 && $TV_YN!="Y"){
										$resultStr02="<font color='orange'><b>".$TimeCheck."</b></font>";
									}else if($TCheck > 930){
										$resultStr02="<font color='red'><b>".$TimeCheck."</b></font>";
									}else{
										$resultStr02 = $TimeCheck;
									}//if End
								}else
								{
									if($TCheck >= 850 && $TCheck <= 900 && $TV_YN!="Y"){
										$resultStr02="<font color='orange'>".$TimeCheck."</font>";
									}else if($TCheck > 900 && $TV_YN!="Y"){
										$resultStr02="<font color='red'><b>".$TimeCheck."</b></font>";
									}else{
										$resultStr02 = $TimeCheck;
									}//if End
								}

								if ($memberID=="M05205")  //손희창
								{
									if($d_ViewDate > '2019-01-01' || $d_ViewDate < '2019-03-01')
									{
										if($TCheck >= 950 && $TCheck <= 1000 && $TV_YN!="Y"){
											$resultStr02="<font color='orange'>".$TimeCheck."</font>";
										}else if($TCheck > 1000 && $TV_YN!="Y"){
											$resultStr02="<font color='red'><b>".$TimeCheck."</b></font>";
										}else{
											$resultStr02 = $TimeCheck;
										}//if End
									}
								}


// 												/* 코로나로 인한 탄력근무 기간동안  메인화면 출근시간 색상변경 안함)  */
// 												/* 20200311 : 연장요청(20200320까지)  */
// 												/* 20200320 : 연장요청(20200331까지)  */
// 												/* 20200331 : 연장요청(20200417까지)  */
// 												/* 20200820 : 연장요청(20200829까지)  */
// 												/* 20200831 : 연장요청(20200904까지)  */
// 												/* 20200907 : 연장요청(20200911까지)  */
// 												/* 20200921 : 연장요청(20200930까지)  */
// 												/* 20201006 : 연장요청(20201017까지)  */
// 												if(date("Ymd")<"20201017"){
// 													//코로나 2.5
// 													$resultStr02 = $TimeCheck;
// 												}

								//--------------------------------------------------------
								//개인별 탄력근무 관련 정보 start : 20201207 moon
								//--------------------------------------------------------
								$FlexibleWorkYN="N";
								$FlexibleWork_array = FN_FlexibleWork_info($d_ViewDate, $memberID);
								if($FlexibleWork_array[0][re_YN]=="Y"){
									$re_memberno    =  $FlexibleWork_array[0][re_memberno];
									$re_s_date      =  $FlexibleWork_array[0][re_s_date];
									$re_e_date      =  $FlexibleWork_array[0][re_e_date];
									$re_tardy_h     =  $FlexibleWork_array[0][re_tardy_h];
									$re_tardy_m     =  $FlexibleWork_array[0][re_tardy_m];
									$re_info        =  $FlexibleWork_array[0][re_info];		//코로나

									$FlexibleWorkYN="Y";
								}else{}
								if($FlexibleWorkYN=="Y"){ //탄력근무

									if($re_tardy_m=="0"){
										$re_tardy_m="00";
									}
									$c_time = $re_tardy_h.''.$re_tardy_m;

									if($re_tardy_h!="1"){
										$comp_h=(int)$re_tardy_h-1;
									}
									if($re_tardy_m=="00"){
										$comp_m="50";
									}else if($re_tardy_m=="30"){
										$comp_m="20";
									}

									if($TV_YN == 'Y'){
										$resultStr02="<font color='blue'>".$TimeCheck."</font>";
									}elseif($TCheck > $c_time) {
										$resultStr02="<font color='red'>".$TimeCheck."</font>";
									}else{
										$comp_t = $comp_h.''.$comp_m;
										if($TCheck > $comp_t) {
											$resultStr02="<font color='orange'>".$TimeCheck."</font>";
										}else{
											$resultStr02 = $TimeCheck;
										}//if End

									}//if End

								}else{
									$resultStr02 = $TimeCheck;
								}
								//--------------------------------------------------------
								//개인별 탄력근무 관련 정보 end
								//--------------------------------------------------------


						}else{//휴일&공휴일
							$resultStr02 = $TimeCheck;
						}//if End
						/* 출근시간 컬러표시 End-------------------------------------------------------------------- */

					array_push($resultArray02 ,$resultStr02);


					/*연장근무유무 ------------------------------------*/
					$d_ViewDate = @mysql_result($re_code99,0,"d_ViewDate");
					$resultStr03  = overtimeCheck($d_ViewDate,$memberID,'');
					array_push($resultArray03 ,$resultStr03);





					/*프로젝트코드------------------------------------*/
					$resultStr04 = @mysql_result($re_code99,0,"d_EntryPCode");
					array_push($resultArray04 ,$resultStr04);






$e_LeaveTime		= @mysql_result($re_code99,0,"e_LeaveTime");	//업무종료 : 종료시간
$e_LeavePCode		= @mysql_result($re_code99,0,"e_LeavePCode");	//업무종료 : 프로젝트코드(구)
$e_LeavePCode2		= @mysql_result($re_code99,0,"e_LeavePCode2");	//업무종료 : 프로젝트코드(신)
$e_LeaveJobCode		= @mysql_result($re_code99,0,"e_LeaveJobCode");	//업무종료 : 프로젝트서브코드
$e_LeaveJob			= @mysql_result($re_code99,0,"e_LeaveJob");		//업무종료 : 업무내용
$e_EJ_FULL			= @mysql_result($re_code99,0,"e_EJ_FULL");		//업무종료 : 업무내용
$e_EJ_change		= @mysql_result($re_code99,0,"e_EJ_change");		//업무종료 : 업무내용


if(($e_LeaveTime!="" || $e_LeaveTime!="0000-00-00 00:00:00") && ($e_LeavePCode!="")){
//업무종료시간 존재&&종료프로젝트코드 있을시
//업무종료시 입력한 내용 표시
	//프로젝트코드 ViewCode : 181022
	$resultStr10	= projectToColumn($e_LeavePCode,'projectViewCode',DevConfirm($memberID));		//프로젝트코드 ViewCode : 181022
	array_push($resultArray10 ,$resultStr10);
}else{
	//프로젝트코드 ViewCode : 181022
	$resultStr10	= projectToColumn($resultStr04,'projectViewCode',DevConfirm($memberID));		//프로젝트코드 ViewCode : 181022
	array_push($resultArray10 ,$resultStr10);
}








					/*프로젝트 닉네임------------------------------------*/
					$d_EntryPCode = @mysql_result($re_code99,0,"d_EntryPCode");


					if(DevConfirm($memberID)){
						//echo '$d_EntryPCode =='.$d_EntryPCode.'<br><br>';

					}









					// 					$e_LeaveTime		= @mysql_result($re_code99,0,"e_LeaveTime");	//업무종료 : 종료시간
					// 					$e_LeavePCode		= @mysql_result($re_code99,0,"e_LeavePCode");	//업무종료 : 프로젝트코드(구)
					// 					$e_LeavePCode2		= @mysql_result($re_code99,0,"e_LeavePCode2");	//업무종료 : 프로젝트코드(신)
					// 					$e_LeaveJobCode		= @mysql_result($re_code99,0,"e_LeaveJobCode");	//업무종료 : 프로젝트서브코드
					// 					$e_LeaveJob			= @mysql_result($re_code99,0,"e_LeaveJob");		//업무종료 : 업무내용
					// 					$e_EJ_FULL			= @mysql_result($re_code99,0,"e_EJ_FULL");		//업무종료 : 업무내용
					// 					$e_EJ_change		= @mysql_result($re_code99,0,"e_EJ_change");		//업무종료 : 업무내용

					if(($e_LeaveTime!="" || $e_LeaveTime!="0000-00-00 00:00:00") && ($e_LeavePCode!="")){
						//업무종료시간 존재&&종료프로젝트코드 있을시
						//업무종료시 입력한 내용 표시
						//프로젝트코드 ViewCode : 181022
						$set_EntryPCode = $e_LeavePCode;
					}else{
						//프로젝트코드 ViewCode : 181022
						$set_EntryPCode = $d_EntryPCode;
					}


					$resultStr05 ="";
						/*------------------------------------------------------*/
						/*프로젝트 닉네임 조회--- */
						$ProjectNickname = "";
						//if(change_XXIS($d_EntryPCode)){ //리턴값이 true  : XX관련 코드
						if( change_XXIS02($set_EntryPCode,$CompanyKind)){ //리턴값이 true  : XX관련 코드

							$ProjectCode2 = change_XX($set_EntryPCode); //XX-AA-BB 코드로 변환하여 project_tbl에서 조회가능하도록 한다.

							$sql_code01="SELECT * FROM project_tbl WHERE ProjectCode ='".$ProjectCode2."' ";

							if(DevConfirm($memberID)){
								//echo 'sql_code01 =='.$sql_code01.'<br><br>';
							}


							$re_code01 = mysql_query($sql_code01,$db);
							$re_num_code01 = mysql_num_rows($re_code01);
							if($re_num_code01 != 0) {
								$ProjectNickname = mysql_result($re_code01,0,"ProjectNickname");
								$resultStr05     = utf8_strcut($ProjectNickname,8,'..');
							}else{
								$resultStr05 = "&nbsp;";
							}//if End
						}else{ //리턴값이 false : 일반프로젝트 코드
							$sql_code02="SELECT * FROM project_tbl WHERE ProjectCode ='".$set_EntryPCode."' ";
							/*------------------------------------*/
							$re_code02 = mysql_query($sql_code02,$db);
							$re_num_code02 = mysql_num_rows($re_code02);
							/*------------------------------------*/
							if($re_num_code02 != 0) {
								$ProjectNickname = mysql_result($re_code02,0,"ProjectNickname");
								$resultStr05  = utf8_strcut($ProjectNickname,8,'..');
								/*------------------------------------*/
							}else{
								$resultStr05 = "&nbsp;";
							}//if End
						}//if End
						/*프로젝트 닉네임 조회 End--*/
						/*------------------------------------------------------*/
					array_push($resultArray05 ,$resultStr05);


					$str_length_add = 15;
					if($TV_YN=="Y"){
						$str_length_add = 10;
					}
					//------------------------
					if(($e_LeaveTime!="" || $e_LeaveTime!="0000-00-00 00:00:00") && ($e_LeavePCode!="")){
						//업무종료시간 존재&&종료프로젝트코드 있을시
						//업무종료시 입력한 내용 표시
						/*업무내용_short------------------------------------*/
						$resultStr06 = @mysql_result($re_code99,0,"e_EJ_change");

						$resultStr06_len = mb_strlen($resultStr06,"UTF-8");
						if($resultStr06_len>$str_length_add){
							$resultStr06 = mb_substr($resultStr06,0,$str_length_add,"UTF-8")."..";
						}
						if($TV_YN=="Y"){
							$resultStr06 = $TV_add_msg1.$resultStr06;
						}
						array_push($resultArray06 ,$resultStr06);
						array_push($resultArray07 ,@mysql_result($re_code99,0,"e_EJ_FULL"));
					}else{
						/*업무내용_short------------------------------------*/
						$resultStr06 = @mysql_result($re_code99,0,"d_EJ_change");

						$resultStr06_len = mb_strlen($resultStr06,"UTF-8");
						if($resultStr06_len>$str_length_add){
							$resultStr06 = mb_substr($resultStr06,0,$str_length_add,"UTF-8")."..";
						}
						if($TV_YN=="Y"){
							$resultStr06 = $TV_add_msg1.$resultStr06;
						}
						array_push($resultArray06 ,$resultStr06);
						array_push($resultArray07 ,@mysql_result($re_code99,0,"d_EJ_FULL"));
					}

					/*----------------------------------------------*/
				}else{}//if End
				/*----------------------------------------------*/
			}//if End
			/*----------------------------------------------*/

			if($memberID=="B14306"){

// 				$re_cnt = count($resultArray02);

// 				$time_vacation_str =$resultArray02[$re_cnt-1];
// 				echo $time_vacation_str."=========================================";

			}

			/*배열 카운트 체크후, BREAK =>6줄 -----------------------------------*/
			if(count($resultArrayDate)==6){
				break;
			}//if End
			/*----------------------------------------------*/
		}//for End
		/*----------------------------------------------*/
		$resultArrayDate_cnt = count($resultArrayDate);
		/*----------------------------------------------*/
		$this->assign('resultArrayDate',$resultArrayDate);//배열
		$this->assign('resultArrayDate_cnt',$resultArrayDate_cnt);//배열길이
		/*----------------------------------------------*/
		$this->assign('resultDayColor',$resultDayColor);//일자 컬러
		$this->assign('resultTimeColor',$resultTimeColor);//시간 컬러
		/*----------------------------------------------*/
		$this->assign('resultArray00',$resultArray00);//일자
		$this->assign('resultArray01',$resultArray01);//요일
		$this->assign('resultArray02',$resultArray02);//출근시간
		$this->assign('resultArray03',$resultArray03);//연장근무유무

		$this->assign('resultArray04',$resultArray04);//프로젝트코드

		$this->assign('resultArray05',$resultArray05);//프로젝트 닉네임
		$this->assign('resultArray06',$resultArray06);//업무내용
		$this->assign('resultArray07',$resultArray07);//업무내용 full

		$this->assign('resultArray10',$resultArray10);//프로젝트뷰코드 : 181022
		/*----------------------------------------------*/
		//$this->display("intranet/common_layout/main_home.tpl");
		/* *************************************************************************************************************************** */
	}//LoginInfo() End
	/* -최근업무내용 End--------------------------------------------------------------------------- */
	/* ******************************************************************************************* */

	/* ******************************************************************************************* */
	/* 최근업무내용 ////////////////////////////////////////////////////////////////////////////// */
	function LoginInfo_back()
	{
			global $db,$memberID;
			global $SearchID,$Display,$CompanyKind;

			$ProjectNickname=""; //프로젝트 닉네임 전역변수 선언

			$daily_work  = array();

			$overtimesql="select * from overtime_basic_new_tbl order by code";
			$result_over = mysql_query($overtimesql,$db);
			while($result_over_row = mysql_fetch_array($result_over))
			{
				if($result_over_row[code] =="0") //평일근무시간
				{
					$weekday_start = $result_over_row[start_time];
					$weekday_min = $result_over_row[min_time];
					$weekday_max = $result_over_row[max_time];
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
			$maxnum=6;
			$SearchDate=date("Y-m-d");
			for($j="0";$j<$maxnum;$j++)
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
				$sql= $sql." ,D.EntryJobCode EntryJobCode ";						//프로젝트서브코드
				$sql= $sql." ,D.EntryJob EntryJob ";								//업무내용
				$sql= $sql." ,D.modify modify";										//  O/T 승인여부
				$sql= $sql." ,D.Note Note";
				$sql= $sql." ,substring(D.SortKey,4,2) RankCode";
				$sql= $sql." From ";
				$sql= $sql." dallyproject_tbl D ";
				$sql= $sql." WHERE ";
				$sql= $sql." D.EntryTime  like '".$SearchDate."%'";
				$sql= $sql." AND ";
				$sql= $sql." D.MemberNo = '".$memberID."'";
				$sql= $sql." ORDER BY D.EntryTime asc";

				//echo $sql."<br>";
				$holy_sc = holy($SearchDate);
				$tmp=explode("-",$SearchDate);
				$syear=$tmp[0];
				$smonth=$tmp[1];
				$tday = week_day($tmp[0],$tmp[1],$tmp[2]);

				if ($holy_sc =="holyday")
				{
					$ViewDate_IS= "<font color='#FF65A3'>".$tmp[2]."일(".$tday.")</font>";
				}
				else
				{
					$ViewDate_IS=$tmp[2]."일(".$tday.")";
				}

				$re = mysql_query($sql,$db);
				$re_num = mysql_num_rows($re);

				if($re_num == 0){  //근무기록없으면
					//휴가파견  표시
					$sql2 = "	SELECT a.*,b.Name as StateName 															";
					$sql2 =	$sql2."			FROM																		";
					$sql2 =	$sql2."			(																			";
					$sql2 =	$sql2."			SELECT * FROM userstate_tbl													";
					$sql2 =	$sql2."				WHERE																	";
					$sql2 =	$sql2."					(start_time <= '".$SearchDate."' and end_time >= '".$SearchDate."')		";
					$sql2 =	$sql2."					 AND																";
					$sql2 =	$sql2."					 MemberNo = '".$memberID."'											";
					$sql2 =	$sql2."			) a left JOIN																";
					$sql2 =	$sql2."		(																				";
					$sql2 =	$sql2."			SELECT * from systemconfig_tbl where SysKey = 'UserStateCode'				";
					$sql2 =	$sql2."		)b on a.state = b.Code															";
					//echo "근무기록없으면::".$sql2."<br><br>";

					$re2 = mysql_query($sql2,$db);
					$re_num= mysql_num_rows($re2);

					if($re_num > 0) {  //휴가파견있으면
						if($holy_sc == "holyday") //휴일 휴가는 표시안함
						{
							$ProjectCode	= "";
							$UseState		= "";
							$note			= "";
							$StateName		= "";
							$EntryTime = "";
							$EntryJobCode = "";
							$EntryJob     = "";
						}else
						{
							$ProjectCode	= mysql_result($re2,0,"ProjectCode");
							$UseState		= mysql_result($re2,0,"state");
							$note			= mysql_result($re2,0,"note");
							$note			= str_replace(" ","",$note);
							$StateName		= mysql_result($re2,0,"StateName");

							if($UseState != 15) { //파견이외
								$EntryTime2 = "<font color=blue>".$StateName."</font>";

							}else{
								$EntryTime2 = "<font color=blue>".$StateName."</font>";
							}//if End

							$EntryTime    = $EntryTime2;
							$EntryJobCode = "<font color=#6a7e82>".$StateName."</font>";
							$EntryJob     = $note ;

							if(change_XXIS($ProjectCode))
							{
								$ProjectCode2 = change_XX($ProjectCode);
								$sqlp="select * from project_tbl where ProjectCode ='$ProjectCode2'";

								$rep = mysql_query($sqlp,$db);
								$re_nump = mysql_num_rows($rep);
								if($re_nump != 0) {
									$ProjectNickname = mysql_result($rep,0,"ProjectNickname");
								}else
								{	$ProjectNickname ="";
								}
							}
						}
						$OverTime=="";
						$LeaveTime="";
						/* -------------------------- */
						$re_row[ViewDate_IS]=$ViewDate_IS;
						$re_row[EntryTime_Is]=$EntryTime;
						$re_row[EntryPCode_Is]=$ProjectCode;
						$re_row[OverTime_Is]=$OverTime;
						$re_row[ProjectNickname]=utf8_strcut($ProjectNickname,8,'..');
						$re_row[EntryJob_Is]=utf8_strcut($EntryJob,18,'..');
						array_push($daily_work,$re_row);
						/* -------------------------- */
					}else{
						if($maxnum<10)	$maxnum++;
					}
				}else{  //근무기록 존재시
					//$num=1;
					while($re_row = mysql_fetch_array($re)){
						/* -------------------------- */
						$SearchID=$re_row[MemberNo];
						$RankCode=$re_row[RankCode];
						$ViewDate=$re_row[ViewDate];
						/* -------------------------- */
						$EntryTime=$re_row[EntryMin];
						$OverTime=$re_row[OverMin];
						$LeaveTime=$re_row[LeaveMin];
						/* -------------------------- */
						$EntryPCode=$re_row[EntryPCode];
						/* -------------------------- */
						$EntryJobCode=$re_row[EntryJobCode];
						$EntryJob=$re_row[EntryJob];
						$Note=$re_row[Note];
						$modify=$re_row[modify];
						/* -------------------------- */
						//$ProjectNickname=$re_row[ProjectNickname];
						$overdate= substr($re_row[OverTime],5,2)."-".substr($re_row[OverTime],8,2);
						$leavedate= substr($re_row[LeaveTime],5,2)."-".substr($re_row[LeaveTime],8,2);
						/* -------------------------- */
						$holy_sc = holy($ViewDate);
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
										if($OverTime < $weekday_start)
										{	$OverTime2=$weekday_start;
										}else
										{
											$OverTime2=$OverTime;
										}

										$LeaveSec = strtotime($LeaveTime);
										$OverSec = strtotime($OverTime2);
										$OTTime = sec_time00($LeaveSec - $OverSec);

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
						/* 코드사용분기 Start  *************** */
						if($CompanyKind=="PILE" || $CompanyKind=="HANM" || $CompanyKind=="BARO" ){//파일테크(PILE),바론컨설턴트(HANM)

							if($OTState=="○" && $modify=="1" )
							{
								$OTState="○";;
							}else if($OTState=="中")
							{
								$OTState="中";
							}else
							{
								$OTState="&nbsp;";
							}
						}else if($CompanyKind=="JANG"){//장헌산업(JANG)
						}
						/* 코드사용분기 End  *************** */
							if($nowork =="yes")
							{
								$OTTime="";
							} //if End
							/* ------------------------------------------- */
							$re_row[OTTime]=$OTTime;
							$re_row[OTState]=$OTState;
							/* ------------------------------------------- */
							//휴가파견  표시
							$sql2 =		  "	SELECT																										";
							$sql2 = $sql2."	 a.*                                                                                     					";
							$sql2 = $sql2."	,b.Name as StateName                                                                                     	";
							$sql2 = $sql2."	FROM																										";
							$sql2 = $sql2."	(                                                                                                           ";
							$sql2 = $sql2."	SELECT * FROM userstate_tbl 																				";
							$sql2 = $sql2."		WHERE (start_time <= '".$ViewDate."' and end_time >= '".$ViewDate."') and MemberNo = '".$memberID."'	";
							$sql2 = $sql2."	) a left JOIN                                                                                               ";
							$sql2 = $sql2."	(                                                                                                           ";
							$sql2 = $sql2."	SELECT * FROM systemconfig_tbl WHERE SysKey = 'UserStateCode'                                               ";
							$sql2 = $sql2."	)b on a.state = b.Code                                                                                      ";
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

								$StateName = mysql_result($re2,0,"StateName");

								if($note == "오후반차") {
									$EntryTime2 = "<A href=# title=$LeaveTime>"."<font color=blue>"."휴가"."</font>"."</A>";

								} else {
									if($UseState != 15){ //파견이외
										$EntryTime2 = "<A href=# title='$EntryTime'>"."<font color=blue>".$StateName."</font>"."</A>";

									}else{
										$EntryTime2 = "<font color=blue>".$StateName."</font>";

									} //if End
								} //if End

								$EntryTime=$EntryTime2;
								//$OverTime="";
								//$LeaveTime="";
								$EntryJobCode="<font color=#6a7e82>".$StateName."</font>";
								$EntryJob=$note ;

							}else{
								$ProjectCode = $re_row[EntryPCode];
								$UseState = "";

								if($RankCode<="C7" || $holy_sc == "holyday"){

								}else{


										$EntryTime=c_colort2($EntryTime,$ViewDate,$RankCode);

								}//if End
							}//if End
						$re_row[EntryPCode_Is]=$ProjectCode;
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

						if($CompanyKind=="JANG"){
							if($arrayStr2 == "관리" || $arrayStr2 == "고문" || $arrayStr2 == "교휴" || $arrayStr2 == "영업" || $arrayStr2 == "업무" || $arrayStr2 == "지원"){
								$d_EntryPCode_edit = "XX"."-".$arrayStr2."-".$arrayStr3;
							}

						}else if ($CompanyKind=="PILE"){
							if($arrayStr2 == "관리" || $arrayStr2 == "교휴"){

								$d_EntryPCode_edit = "XX"."-".$arrayStr2."-".$arrayStr3;
							}
						}else if ($CompanyKind=="HANM" ){
							if($arrayStr2 == "관리" || $arrayStr2 == "고문" || $arrayStr2 == "교휴" || $arrayStr2 == "영업" || $arrayStr2 == "자기" || $arrayStr2 == "기술"){
								$d_EntryPCode_edit = "HXX"."-".$arrayStr2."-".$arrayStr3;
							}
						}else if ($CompanyKind=="BARO" ){
							if($arrayStr2 == "관리" || $arrayStr2 == "고문" || $arrayStr2 == "교휴" || $arrayStr2 == "영업" || $arrayStr2 == "자기" || $arrayStr2 == "기술"){
								$d_EntryPCode_edit = "HXX"."-".$arrayStr2."-".$arrayStr3;
							}
						}//if End
						/*-------------------------*/
						$sql2 =      " SELECT										";
						$sql2 = $sql2."	 ProjectCode	    as ProjectCode 			";
						$sql2 = $sql2."	,ProjectNickname	as ProjectNickname  	";
						//$sql2 = $sql2."	,count(*) cnt  	";
						$sql2 = $sql2."	FROM										";
						$sql2 = $sql2."	project_tbl									";
						$sql2 = $sql2."	WHERE										";
						$sql2 = $sql2."	ProjectCode = '".$d_EntryPCode_edit."'		";
						/*-------------------------*/
						$re2				= mysql_query($sql2,$db);
						$ProjectNickname	= @mysql_result($re2,0,"ProjectNickname"); 			//프로젝트 닉네임
						/*-------------------------*/
						if($OverTime == "00:00"){
							$OverTime="";
						} //if End
						$re_row[ViewDate_IS]=$ViewDate_IS;
						$re_row[EntryTime_Is]=$EntryTime;
						$re_row[ProjectNickname]=utf8_strcut($ProjectNickname,8,'..');
						$re_row[EntryJobCode_Is]=$EntryJobCode;
						$re_row[EntryJob_Is]=utf8_strcut($EntryJob,18,'..');
						/* ---------------------- */
						array_push($daily_work,$re_row);

						$num++;

					}//while End
					/* ------------------------- */

				 }// if End : 근무기록 존재시
				 /* ------------------------- */
			  $SearchDate=find_last($SearchDate);
			}
			/* ------------------------------------------------------------- */
			$this->assign('StartYear',$StartYear);
			$this->assign('Display',$Display);
			$this->assign('MemberName',$MemberNo2Name);
			$this->assign('daily_work',$daily_work);
			$this->assign('CompanyKind',$CompanyKind);
		}
	/* 최근업무내용종료 ////////////////////////////////////////////////////////////////////////////// */
	/* ******************************************************************************************* */

	/* ******************************************************************************************* */
	/* 공지사항 /////////////////////////////////////////////////////////////////////////////////// */
	function SelectDataList1()
	{
		/* 홈화면 사내공지 미니리스트*/
		/* *********************
		** notice_new_tbl
		**    level,sub,id,name,email, home,pass,title,comment,
		**	  wdate,see,group_code,popup,view_start,view_end,pop_start,
		**    pop_end,view,filename,filesize,forcepopup
        ************************/
		global $db;
		global $memberID;		//사원번호
		global $CompanyKind;	//장헌산업(JANG),파일테크(PILE),바론컨설턴트(HANM)
		/* ---------------------------- */
		$text_table = "notice_new_tbl";
		/* ---------------------------- */
		$today=date("Y-m-d");
		/* ---------------------------- */
		$query_data  = array();
		$query_data2 = array();
		/* ---------------------------- */
		$all_groupcode="99";
		$GroupCode=$_SESSION['MyGroupCode'];
		/* ---------------------------- */
		$sql=     "	SELECT * FROM													";
		$sql=$sql."	notice_new_tbl													";
		$sql=$sql."	WHERE															";
		if($GroupCode == '31' || $GroupCode == '33'){
			$sql=$sql."	group_code in('31','33','".$all_groupcode."')			";
		}else{
		$sql=$sql."	group_code in('".$GroupCode."','".$all_groupcode."')			";
		}
		$sql=$sql."	and																";
		$sql=$sql."	(																";
		$sql=$sql."		(view_start <= '".$today."' and view_end >= '".$today."')   ";
		$sql=$sql."		or															";
		$sql=$sql."		(view_start is null and view_end is null)					";
		$sql=$sql."		or															";
		$sql=$sql."		(view_start='0000-00-00' and view_end='0000-00-00')			";
		$sql=$sql."	)																";
		$sql=$sql."	ORDER BY id DESC LIMIT 0,5										";

		/* ---------------------------- */
		$result = mysql_query($sql,$db);
		/* ---------------------------- */
		while($re_row = mysql_fetch_array($result)) {

			//첨부파일다운관련-----------------------------------------
// 			$tmpfile=explode("/",$re_row[filename]);
// 			$no= count($tmpfile)-1;
// 			$filename_is= $tmpfile[$no];
// 			$re_row[filename_is]=$filename_is;
			$re_row[filename_is] = str_replace("./noticefile","", $re_row[filename]);
			//-----------------------------------------


			$re_row[title]=utf8_strcut($re_row[title],35,'...');
			/* ---------------------------- */
			//if(substr($re_row[wdate],0,10)==$today){
			if(substr($re_row[wdate],0,10)==$today or $re_row[view_start]==$today or $re_row[pop_start]==$today){
				$re_row[newicon]=true;
			}else{
				$re_row[newicon]=false;
			}//if End
			/* ---------------------------- */
			array_push($query_data,$re_row);
			/* ---------------------------- */
		}//while End
		/* ---------------------------- */
		$this->assign('query_data',$query_data);
		/* ---------------------------- */

		/* 팝업 체크 부분 ---------------------------------------------------------------- */
		/* ---------------------------- */
		$sql=     "	SELECT * FROM																";
		$sql=$sql."	notice_new_tbl																";
		$sql=$sql."	WHERE																		";
		if($GroupCode == '31' || $GroupCode == '33'){
			$sql=$sql."	group_code in('31','33','".$all_groupcode."')			";
		}else{
		$sql=$sql."	group_code in('".$GroupCode."','".$all_groupcode."')			";
		}
		$sql=$sql."	AND																			";
		$sql=$sql."	(																			";
		$sql=$sql."		(popup='on' AND pop_start <= '".$today."' AND pop_end >= '".$today."')	";
		$sql=$sql."		AND																		";
		$sql=$sql."		(																		";
		$sql=$sql."			(view_start <= '$today' AND view_end >= '".$today."')				";
		$sql=$sql."			or																	";
		$sql=$sql."			(view_start is null  AND view_end is null)							";
		$sql=$sql."			or																	";
		$sql=$sql."			(view_start='0000-00-00' AND view_end='0000-00-00')					";
		$sql=$sql."		)																		";
		$sql=$sql."	)																			";
		$sql=$sql."	ORDER BY id DESC															";
//echo $sql."<Br>";
			/* ---------------------------- */
			$popresult=mysql_query($sql,$db);
			$leftv=30;
			$topv=10;
			/* ---------------------------- */
		while($pop_row=mysql_fetch_array($popresult)){
			if($pop_row[forcepopup]=="on"){
				/* ---------------------------- */
				/* 버그발생원인 : "name"=>$pop_row[level]  레벨이 동일값이라서 팝업네임이 같게 설정
				  $ItemData=array("id" =>$pop_row[id],"name"=>$pop_row[level],"top"=>$topv,"left"=>$leftv);
				*/
				$ItemData=array("id" =>$pop_row[id],"name"=>$pop_row[id],"top"=>$topv,"left"=>$leftv);
				/* ---------------------------- */
				array_push($query_data2,$ItemData);
				/* ---------------------------- */
				$leftv=$leftv+30;
				$topv=$topv+30;
				/* ---------------------------- */
			}else{
				/* ---------------------------- */
				$ssql="select * from notice_read_tbl where notice_id='$pop_row[id]' and MemberNo='$memberID'";
				/* ---------------------------- */
				//echo $ssql."<Br>";
				$presult=mysql_query($ssql,$db);
				$presult_row=mysql_num_rows($presult);
				/* ---------------------------- */
				if($presult_row <= 0 ){
					/* ---------------------------- */
					/* 버그발생원인 : "name"=>$pop_row[level]  레벨이 동일값이라서 팝업네임이 같게 설정
					  $ItemData=array("id" =>$pop_row[id],"name"=>$pop_row[level],"top"=>$topv,"left"=>$leftv);
					*/
					$ItemData=array("id" =>$pop_row[id],"name"=>$pop_row[id],"top"=>$topv,"left"=>$leftv);
					/* ---------------------------- */
			 		array_push($query_data2,$ItemData);
					/* ---------------------------- */
					$leftv=$leftv+30;
					$topv=$topv+30;

				}// if End
				/* ---------------------------- */
			}// if End
				/* ---------------------------- */
		} // while End

//echo count($query_data2);

		/* 팝업 체크 부분 End ---------------------------------------------------------------- */
		$this->assign('query_data2',$query_data2);

		$this->assign('query',$sql);


		/* ---------------------------- */
		$this->assign('CompanyKind',$CompanyKind);
		/* ---------------------------- */

	}//SelectDataList1 : 공지사항 End
	/* ******************************************************************************************* */
	// 주요일정 : 미니달력 ///////////////////////////////////////////////////////////////////////
	function Calendar_Mini(){
		/* -----------------*/
		global	$MemberNo;	  // 사원번호
		global	$korName;	  // 한글이름
		global	$GroupCode;	  // 부서
		global	$RankCode;	  // 직급
		global	$SortKey;	  // 직급+부서
		/* ----------------- */
		global	$date_today;  // 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1; // 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$nowYear;     // 오늘날짜 년          : yyyy
		global	$nowMonth;    // 오늘날짜 년월        : yyyy-mm
		global	$nowHour;	  // 현재 시
		global	$nowMin;	  // 현재 분
		global	$nowTime;	  // 현재 시:분
		global	$CompanyKind;	  // 회사종류
		/* ----------------- */
		global  $db;
		/* ----------------- */
		$row_count = date('w')==6? 7*6 : 7*5;
		$lastmonth = date('t');
		$day_now = date('d');
		$day_after1 = $day_now + 1;
		$day_after2 = $day_now + 2;
		$startDate = date('Y-m-01');		//'2022-10-01';
		$endDate = date('Y-m-t');		//'2022-10-30';
		$week = strftime("%w", strtotime($startDate));
		$displayday = 1;

		for($i=0; $i<=$row_count; $i++){
			if( $i < $week ){
				$DayList[] = array("day" =>'',"flagUse1"=>"","flagUse2"=>"","s_contents1"=>"","s_contents"=>"","s_contentsShort1"=>"","s_contentsShort"=>"");
			}else if( $displayday <= $lastmonth ){
				$DayList[] = array("day" =>$displayday,"flagUse1"=>"","flagUse2"=>"","s_contents1"=>"","s_contents"=>"","s_contentsShort1"=>"","s_contentsShort"=>"");
				$displayday++;
			}else{
				$DayList[] = array("day" =>'',"flagUse1"=>"","flagUse2"=>"","s_contents1"=>"","s_contents"=>"","s_contentsShort1"=>"","s_contentsShort"=>"");
			}
		}

		//2023.03.07 김한결 수정 : 정재익 부장 요청으로 건설사업관리-안전관리부 일정 똑같이 보이도록
		if( in_array($GroupCode, array('31', '33')) ){
			$GroupCode = "31','33";
		}else{
			$GroupCode = (int)$GroupCode;
		}
		$sql = "
			SELECT
				groupcode as s_groupcode,
				pdate as s_pdate,
				subcode as s_subcode,
				contents as s_contents,
				cdate as s_cdate,
				updateuser as s_updateuser
			FROM schedule_job_tbl
			WHERE pdate >= '{$startDate}' AND pdate <= '{$endDate}'
			AND groupcode IN ('99', '{$GroupCode}')
			ORDER BY pdate DESC
		";
		/*
		'GroupCode', '01', '임원'
		'GroupCode', '02', '경영지원부'
		'GroupCode', '04', '공사관리팀'
		'GroupCode', '06', '생산본부'
		'GroupCode', '07', '현장(작업반)'
		'GroupCode', '08', '설계팀'
		'GroupCode', '09', '영업팀'
		'GroupCode', '10', '현장소장'
		'GroupCode', '99', '사내공지만'
		*/
		/* ----------------------------------------------------- */
		$re = mysql_query($sql,$db);
		/* ----------------------------------------------------- */
		while($re_row = mysql_fetch_array($re)) {
			$dayitem = explode("-", $re_row['s_pdate']);
			foreach($DayList as $key => $value) {
				if ($value['day'] == (int)$dayitem[2]) {
					$day = $key;
				}
			}

			if( $DayList[$day]['day'] > 0 ){
				$DayList[$day]['day'] = sprintf('%02d', $DayList[$day]['day']);
				if( $re_row['s_groupcode'] == '99' ){
				//사내일정
					$s_contentsShort_len = iconv_strlen($re_row['s_contents'],"UTF-8");
					if($s_contentsShort_len>7){
						$re_row['s_contentsShort'] = iconv_substr($re_row['s_contents'],0,7,"UTF-8")."..";
					}
					$DayList[$day]['flagUse1'] = 'C';
					$DayList[$day]['s_contentsShort1'] = $re_row['s_contentsShort'];
					$DayList[$day]['s_contents1'] = $re_row['s_contents'];
				}else{
				//부서일정
					$s_contentsShort_len = iconv_strlen($re_row['s_contents'],"EUC-KR");
					if($s_contentsShort_len>7){
						$re_row['s_contentsShort'] = iconv_substr($re_row['s_contents'],0,7,"UTF-8")."..";
					}
					$DayList[$day]['flagUse2'] = 'D';
					$DayList[$day]['s_contentsShort'] = $re_row['s_contentsShort'];
					$DayList[$day]['s_contents'] = $re_row['s_contents'];
				}
			}
		}


		/*-------------------------------------------*/
		$this->assign('CompanyKind',$CompanyKind);
		$this->assign('nowMonth',$nowMonth);
		$this->assign('day_now',$day_now);
		$this->assign('day_after1',$day_after1);
		$this->assign('day_after2',$day_after2);
		/*-------------------------------------------*/
		$this->assign('DayList',$DayList);
		/*-------------------------------------------*/
	}//Calendar_Mini End



	/* ******************************************************************************************* */
	function lunchMini()		//메인화면 오늘의 점심식단
	{
		/* -----------------*/
		global	$MemberNo;		// 사원번호
		global	$korName;		// 한글이름
		global	$GroupCode;		// 부서
		global	$RankCode;		// 직급
		global	$SortKey;		// 직급+부서
		/* ----------------- */
		global	$date_today;	// 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1;	// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$nowYear;		// 오늘날짜 년          : yyyy
		global	$nowMonth;		// 오늘날짜 년월        : yyyy-mm
		global	$nowHour;		// 현재 시
		global	$nowMin;		// 현재 분
		global	$nowTime;		// 현재 시:분
		global	$todayName;		//오늘의 요일(일:0, 월:1,화:2,수:3,목:4,금:5,토:6)
		/* ----------------- */
		global $db;
		/* ----------------- */
		$lunch_menu_main_short;
		$lunch_menu_sub_short;
		/* ----------------- */
		// 점심식단 Start *********************************************************** */
		$sql= "SELECT										";
		$sql= $sql."  L.menu_num   as lunch_menu_num		";		// num
		$sql= $sql." ,L.menu_day   as lunch_menu_day 		";		// 요일
		$sql= $sql." ,L.menu_main  as lunch_menu_main		";		// 메인메뉴
		$sql= $sql." ,L.menu_sub   as lunch_menu_sub 		";		// 서브메뉴
		$sql= $sql." ,L.menu_add   as lunch_menu_add 		";		// 기타추가 항목 필요시
		$sql= $sql." FROM									";
		$sql= $sql."      lunch_menu_tbl L					";
		$sql= $sql." WHERE									";
		$sql= $sql."	L.menu_num = '".$todayName."'		";
		/* ----------------------------- */

		if($MemberNo=="B14306" || $memberID=="M21420"){
		}

		/* 장헌인트라넷*/
		/*
		$db_hostname01 ='211.206.127.71';
		$db_database01 ='hanmacerp';
		$db_username01 ='root';
		$db_password01 ='erp';
		*/
		/* 장헌인트라넷*/
						$re_dbinfo = FN_db_info("jang",'');//hanm/jang/pile
						$db_hostname01 = $re_dbinfo[0][hostname];
						$db_database01 = $re_dbinfo[0][database];
						$db_username01 = $re_dbinfo[0][username];
						$db_password01 = $re_dbinfo[0][password];
						/*-----------------------------------------------------------------------*/
						$db01	= mysql_connect($db_hostname01,$db_username01,$db_password01);
							//if(!$db01) die ("Unable to connect to MySql : 장헌인트라넷 DB장애".mysql_error());

						if($db01){
							$lunch_serverYN = "Y";
							/*-----------------------------------------------------------------------*/
							mysql_select_db($db_database01);
							/*-----------------------------------------------------------------------*/
							mysql_set_charset("utf-8",$db01);
							mysql_query("set names utf8");
							/*-----------------------------------------------------------------------*/
							$re = mysql_query($sql,$db01);
							$re_num = mysql_num_rows($re);
							/* ----------------------------- */
							if($re_num != 0){
								/* ----------------- */
								$lunch_menu_num  = mysql_result($re,0,"lunch_menu_num");
								$lunch_menu_main = mysql_result($re,0,"lunch_menu_main");
								$lunch_menu_sub	 = mysql_result($re,0,"lunch_menu_sub");
								/* ----------------- */
								$len01 = mb_strlen($lunch_menu_main,"UTF-8");
								if($len01>35){
									$lunch_menu_main_short = mb_substr($lunch_menu_main,0,35,"UTF-8");
									$lunch_menu_main_short = $lunch_menu_main_short."..";
									$lunch_menu_main_short =  str_replace("\n","<br>", $lunch_menu_main_short);

								}else{
									$lunch_menu_main_short = $lunch_menu_main;
									$lunch_menu_main_short =  str_replace("\n","<br>", $lunch_menu_main_short);
								}
								/* ----------------- */
								$len02 = mb_strlen($lunch_menu_sub,"UTF-8");
								if($len02>35){
									$lunch_menu_sub_short = mb_substr($lunch_menu_sub,0,35,"UTF-8");
									$lunch_menu_sub_short = $lunch_menu_sub_short."..";
									$lunch_menu_sub_short =  str_replace("\n","<br>", $lunch_menu_sub_short);
								}else{
									$lunch_menu_sub_short = $lunch_menu_sub;
									$lunch_menu_sub_short =  str_replace("\n","<br>", $lunch_menu_sub_short);
								}
								/* ----------------- */
							}else{
								$lunch_menu_main = "등록된 메뉴없음";
								$lunch_menu_sub	 = "메뉴담당자에게 확인";
							} //if End
							//////////////
							mysql_close();
							//////////////

	}else{
		$lunch_serverYN = "N";
		$lunch_menu_main = "식단관련 DB서버 확인필요";
		$lunch_menu_sub	 = "관리자에게 확인";
	}

		/* ----------------------------- */
		$this->assign('lunch_serverYN',$lunch_serverYN);					// 식단DB 관련 서버 동작유무(Y=정상동작, N=미동작)
		$this->assign('lunch_menu_num',$lunch_menu_num);					// 메인메뉴
		$this->assign('lunch_menu_main',$lunch_menu_main);					// 메인메뉴
		$this->assign('lunch_menu_sub',$lunch_menu_sub);					// 서브메뉴
		$this->assign('lunch_menu_main_short',$lunch_menu_main_short);		// 메인메뉴(35자)
		$this->assign('lunch_menu_sub_short',$lunch_menu_sub_short);		// 서브메뉴(35자)
		/* ----------------------------- */
	} //lunchMini End
	/* 점심식단 End----------------------------------------------------------------------- */
	/* ******************************************************************************************* */



	/*
	function lunchMini22()		//수정전 : 메인화면 오늘의 점심식단
	{
		global	$MemberNo;		// 사원번호
		global	$korName;		// 한글이름
		global	$GroupCode;		// 부서
		global	$RankCode;		// 직급
		global	$SortKey;		// 직급+부서
		global	$date_today;	// 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1;	// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$nowYear;		// 오늘날짜 년          : yyyy
		global	$nowMonth;		// 오늘날짜 년월        : yyyy-mm
		global	$nowHour;		// 현재 시
		global	$nowMin;		// 현재 분
		global	$nowTime;		// 현재 시:분
		global	$todayName;		//오늘의 요일(일:0, 월:1,화:2,수:3,목:4,금:5,토:6)
		global $db;
		$lunch_menu_main_short;
		$lunch_menu_sub_short;
		// 점심식단 Start
		$sql= "SELECT										";
		$sql= $sql."  L.menu_num   as lunch_menu_num		";		// num
		$sql= $sql." ,L.menu_day   as lunch_menu_day 		";		// 요일
		$sql= $sql." ,L.menu_main  as lunch_menu_main		";		// 메인메뉴
		$sql= $sql." ,L.menu_sub   as lunch_menu_sub 		";		// 서브메뉴
		$sql= $sql." ,L.menu_add   as lunch_menu_add 		";		// 기타추가 항목 필요시
		$sql= $sql." FROM									";
		$sql= $sql."      lunch_menu_tbl L					";
		$sql= $sql." WHERE									";
		$sql= $sql."	L.menu_num = '".$todayName."'		";
		// 장헌인트라넷
		//$db_hostname01 ='192.168.2.250';
		$db_hostname01 ='192.168.10.6';
		$db_database01 ='hanmacerp';
		$db_username01 ='root';
		$db_password01 ='';
		//-----------------------------------------------------------------------
		$db01	= mysql_connect($db_hostname01,$db_username01,$db_password01);
			if(!$db01) die ("Unable to connect to MySql : ".mysql_error());
		//-----------------------------------------------------------------------
		mysql_select_db($db_database01);
		//-----------------------------------------------------------------------
		mysql_set_charset("utf-8",$db01);
		mysql_query("set names utf8");
		//-----------------------------------------------------------------------
		$re = mysql_query($sql,$db01);
		$re_num = mysql_num_rows($re);
		//-----------------------------------------------------------------------
		if($re_num != 0){
			//---------------
			$lunch_menu_num  = mysql_result($re,0,"lunch_menu_num");
			$lunch_menu_main = mysql_result($re,0,"lunch_menu_main");
			$lunch_menu_sub	 = mysql_result($re,0,"lunch_menu_sub");
			//---------------
			$len01 = mb_strlen($lunch_menu_main,"UTF-8");
			if($len01>35){
				$lunch_menu_main_short = mb_substr($lunch_menu_main,0,35,"UTF-8");
				$lunch_menu_main_short = $lunch_menu_main_short."..";
				$lunch_menu_main_short =  str_replace("\n","<br>", $lunch_menu_main_short);

			}else{
				$lunch_menu_main_short = $lunch_menu_main;
				$lunch_menu_main_short =  str_replace("\n","<br>", $lunch_menu_main_short);
			}
			//---------------
			$len02 = mb_strlen($lunch_menu_sub,"UTF-8");
			if($len02>35){
				$lunch_menu_sub_short = mb_substr($lunch_menu_sub,0,35,"UTF-8");
				$lunch_menu_sub_short = $lunch_menu_sub_short."..";
				$lunch_menu_sub_short =  str_replace("\n","<br>", $lunch_menu_sub_short);
			}else{
				$lunch_menu_sub_short = $lunch_menu_sub;
				$lunch_menu_sub_short =  str_replace("\n","<br>", $lunch_menu_sub_short);
			}
			//---------------
		}else{
			$lunch_menu_main = "등록된 메뉴없음";
			$lunch_menu_sub	 = "메뉴담당자에게 확인";
		} //if End
		//////////////
		mysql_close();
		//////////////
		//---------------
		$this->assign('lunch_menu_num',$lunch_menu_num);					// 메인메뉴
		$this->assign('lunch_menu_main',$lunch_menu_main);					// 메인메뉴
		$this->assign('lunch_menu_sub',$lunch_menu_sub);					// 서브메뉴
		$this->assign('lunch_menu_main_short',$lunch_menu_main_short);		// 메인메뉴(35자)
		$this->assign('lunch_menu_sub_short',$lunch_menu_sub_short);		// 서브메뉴(35자)
		//---------------
	} //lunchMini End
*/


	/* 생일자 표시------------------------------------------------------------------------------ */
	function BirthdayList()
	{
		global $db;
		global $memberID;
		global $CompanyKind;

		$year = date('Y');
		$start_week = date("Ymd", strtotime('-2 days'));
		$end_week   = date("Ymd", strtotime('+7 days'));

		$sql="SELECT 
					a.*,
					right(a.birth,4) short_birth,
					sys.`Name` PositionName,
					ls.*
				FROM (
					SELECT
						korName,
						RankCode,
						CASE
							/*미입력자는 주민번호*/
							WHEN birthdayViewYn is null OR birthdayViewYn = 0
							THEN left(JuminNo,6)
							ELSE replace(birthday,'-','')
						END birth,
						birthdayType,
						birthdayViewYn
					FROM member_tbl
					WHERE birthdayViewYn = 1
					AND WorkPosition = 1
					AND GroupCode in ('01', '02', '03', '98')
				) a 
				JOIN systemconfig_tbl sys ON sys.`Code` = a.RankCode AND sys.SysKey = 'PositionCode'
				JOIN lunar_solar_tbl ls ON CONCAT('{$year}',right(a.birth,4)) = case when a.birthdayType = 2 then ls.ls_lunar ELSE ls.ls_solar END
				WHERE ls.ls_solar BETWEEN '{$start_week}' AND '{$end_week}'
				ORDER BY ls.ls_solar, a.RankCode, a.korName";
		
		$re = mysql_query($sql,$db);
		$birth_array = array();
		while($re_row = mysql_fetch_array($re)){
			$birth_txt = date('m월d일', strtotime($re_row['ls_solar']));
			if( date('md') == date('md', strtotime($re_row['ls_solar'])) ){
				$birth_array[] = "<span style='background-color:#FAED7D'>[{$re_row['korName']} {$re_row['PositionName']}님 {$birth_txt}]</span>";
			} else {
				$birth_array[] = "[{$re_row['korName']} {$re_row['PositionName']}님 {$birth_txt}]";
			}

		}
		$birth = implode(' ', $birth_array);

		if(count($birth_array) > 0){
			$birth ="생일자 입니다. ".$birth." 모두모두 축하합니다.";
		}else{
			$birth ="이번주 생일자가 없습니다.";
		}
		//if( $memberID == 'B23033' ){
		//}else{
		//	$birth = "업무지원이 필요하시면 경영지원부 인사총무팀, 기술개발센터는 총괄기획실 인재성장팀으로 문의 주시기 바랍니다.  자세한 내용은 회사생활 가이드를 참고해 주십시요.  인트라넷/ERP 사용 문의는 우측상단에 있는 ERP Q&A를 이용해 주십시요.";
		//}

		$this->assign('birthday',$birth);
		$this->assign('memberID',$memberID);
		$this->assign('CompanyKind',$CompanyKind);
	}
	function BirthdayList_bak()
	{
		/* ----------------------------- */
		global $db;
		global $memberID;
		global $CompanyKind;
		/* ----------------------------- */
		/*이번주*/
		$today = time();
		$week = date("w");
		/* ----------------------------- */
		$week_first = $today-($week*86400);
		$week_last = $week_first+(6*86400);
		/*
			echo "지난주 =". date("Y-m-d",$week_first-(86400*7))." ~ ".date("Y-m-d",$week_last-(86400*7))."<br>";
			echo "이번주 =".date("Y-m-d",$week_first)." ~ ".date("Y-m-d",$week_last)."<Br>";
			echo "이번주 =".date("md",$week_first)." ~ ".date("md",$week_last)."<Br>";
			echo "다음주 =".date("Y-m-d",$week_first+(86400*7))." ~ ".date("Y-m-d",$week_last+(86400*7))."<br>";
		*/
			$start_week = date("md",$week_first); //두자리수 월+두자리수 일 = 0101 (1월1일)
			$end_week   = date("md",$week_last); //두자리수 월+두자리수 일 = 0101 (1월1일)

			$sql  = "	SELECT a1.korName as Name,a2.Name as PositionName, DATE_FORMAT(a1.birthday, '%m') as birthday_month, DATE_FORMAT(a1.birthday, '%d') as birthday_day ";
			$sql .= "	FROM																																																					";
			$sql .= "	(																																																						";
			$sql .= "		SELECT * FROM member_tbl WHERE WorkPosition ='1' AND DATE_FORMAT(birthday, '%m%d') between '{$start_week}' AND '{$end_week}'							";
			$sql .= "	)a1 left JOIN																																																		";
			$sql .= "	(																																																						";
			$sql .= "		SELECT * FROM systemconfig_tbl WHERE SysKey='PositionCode'																																";
			$sql .= "	)a2 on a1.RankCode = a2.code ORDER BY substr(a1.birthday,6,5), a1.birthday																																												";

			/* 변경전 Start : 모니터링 기간이후 삭제가능부분
			*	$sql="select a1.korName as Name,a2.Name as PositionName,JuminNo,substring(JuminNo,5,2) birthday  from ";
			*	$sql = $sql."(                                                                ";
			*	$sql = $sql."		select * from member_tbl where WorkPosition ='1' and substring(JuminNo,3,4) between '$start_week' and '$end_week'  ";
			*	$sql = $sql."	)a1 left JOIN                                                    ";
			*	$sql = $sql."	(                                                                ";
			*	$sql = $sql."		select * from systemconfig_tbl where SysKey='PositionCode'   ";
			*	$sql = $sql.")a2 on a1.RankCode = a2.code order by substring(JuminNo,5,2)" ;
			*	 변경전 End :모니터링 기간이후 삭제가능부분 */

		/*월별*/
		/*
			$Today=date("m");
			$sql="select a1.korName as Name,a2.Name as PositionName,JuminNo,substring(JuminNo,5,2) birthday  from ";
			$sql = $sql."(                                                                ";
			$sql = $sql."		select * from member_tbl where WorkPosition ='1' and substring(JuminNo,3,2) like '$Today%'  ";
			$sql = $sql."	)a1 left JOIN                                                    ";
			$sql = $sql."	(                                                                ";
			$sql = $sql."		select * from systemconfig_tbl where SysKey='PositionCode'   ";
			$sql = $sql.")a2 on a1.RankCode = a2.code order by substring(JuminNo,5,2)" ;
		*/
		/* 일별
			$Today=date("md");
			$sql="select a1.korName as Name,a2.Name as PositionName from ";
			$sql = $sql."(                                                                ";
			$sql = $sql."		select * from member_tbl where WorkPosition ='1' and substring(JuminNo,3,4) ='$Today'  ";
			$sql = $sql."	)a1 left JOIN                                                    ";
			$sql = $sql."	(                                                                ";
			$sql = $sql."		select * from systemconfig_tbl where SysKey='PositionCode'   ";
			$sql = $sql.")a2 on a1.RankCode = a2.code";
		*/

if($memberID=="M21420"){
	//echo $sql."<br>";
}


		$re = mysql_query($sql,$db);
		while($re_row = mysql_fetch_array($re)){

			//$birthday=substr($re_row[JuminNo],2,2)."/".substr($re_row[JuminNo],4,2);
			$birth=	$birth."[".$re_row[Name]." ".$re_row[PositionName]."님 ".$re_row[birthday_month]."월".$re_row[birthday_day]."일] ";
		}//while End
		/* ----------------------------- */
		if($birth <> ""){
				$birth ="이번주 생일자 입니다. ".$birth."모두모두 축하합니다.";
		}else{
				$birth ="이번주 생일자가 없습니다.";
		} //if End
		/* ----------------------------- */
		$this->assign('birthday',$birth);
		$this->assign('memberID',$memberID);
		$this->assign('CompanyKind',$CompanyKind);
		/* ----------------------------- */
	} //BirthdayList() End

	/* ******************************************************************************************* */
	function SelectProjectCode()
	{
		/* ----------------------------- */
		global $db;
		global $memberID;
		global $shortY;
		/* ----------------------------- */
		$sql=      " SELECT															";
		$sql= $sql."	 D.MemberNo MemberNo										";	//사원번호
		$sql= $sql."	,D.EntryTime ET												";	//업무시작 시간
		$sql= $sql."	,D.LeaveTime LT												";	//업무종료 시간
		$sql= $sql."	,D.OverTime OT												";	//연장근무시작 시간
		$sql= $sql."	,DATE_FORMAT(D.EntryTime, '%Y-%m-%d') typeYYYYmmDD			";
		$sql= $sql."	,DAYNAME(DATE_FORMAT(D.EntryTime, '%Y-%m-%d')) DN			";  // 요일(English)
		$sql= $sql."	,D.EntryPCode EntryPCode									";	//프로젝트코드
		$sql= $sql."	,D.EntryJobCode EntryJobCode								";	//프로젝트서브코드
		$sql= $sql."	,P.ProjectNickname ProjectNickname							";	//프로젝트 닉네임
		$sql= $sql."	,D.EntryJob EntryJob										";	//업무내용
		$sql= $sql."	,D.modify modify											";	//O/T 승인여부
		$sql= $sql." From															";
		$sql= $sql."	dallyproject_tbl D											";
		$sql= $sql."	,project_tbl P												";
		$sql= $sql." WHERE															";
		$sql= $sql."	D.EntryPCode  = replace(P.ProjectCode, 'XX','".$shortY."')	";
		$sql= $sql."	AND															";
		$sql= $sql."	D.MemberNo = '".$memberID."'								";
		$sql= $sql." ORDER BY D.EntryTime DESC										";
 		/* ----------------------------- */
		$result = mysql_query($sql,$db);
		$result_num = mysql_num_rows($result);
		/* ----------------------------- */
		if($result_num != 0) {
			$code_EntryPCode	= mysql_result($result,0,"EntryPCode");
			$EntryJobCode		= mysql_result($result,0,"EntryJobCode");
			$ProjectNickname	= mysql_result($result,0,"ProjectNickname");
			$EntryJob			= mysql_result($result,0,"EntryJob");
		} //End
		/* ----------------------------- */
		$this->assign('code_MemberNo',$MemberNo);
		$this->assign('code_EntryPCode',$code_EntryPCode);
		$this->assign('code_EntryJobCode',$EntryJobCode);
		$this->assign('code_ProjectNickname',$ProjectNickname);
		$this->assign('code_EntryJob',$EntryJob);
		/* ----------------------------- */
	}//SelectProjectCode End

	/* ******************************************************************************************* */
	function SelectDataList3()
	{
		/* 최근업무내용 홈화면 리스트*/
		/* *********************
		** 사용 테이블 :  dallyproject_tbl, project_tbl
        ************************/
		global $db;
		global $memberID;
		/* ----------------------------- */
		$query_data = array();
		/* ----------------------------- */
		$sql=      " SELECT															";
		$sql= $sql."	 D.MemberNo MemberNo										";	//사원번호
		$sql= $sql."	,D.EntryTime ET												";	//업무시작 시간
		$sql= $sql."	,D.LeaveTime LT												";	//업무종료 시간
		$sql= $sql."	,D.OverTime OT												";	//연장근무시작 시간
		$sql= $sql."	,DATE_FORMAT(D.EntryTime, '%Y-%m-%d') typeYYYYmmDD			";
		$sql= $sql."	,DAYNAME(DATE_FORMAT(D.EntryTime, '%Y-%m-%d')) DN			";  // 요일(English)
		$sql= $sql."	,D.EntryPCode EntryPCode									";	//프로젝트코드
		$sql= $sql."	,D.EntryJobCode EntryJobCode								";	//프로젝트서브코드
		$sql= $sql."	,P.ProjectNickname ProjectNickname							";	//프로젝트 닉네임
		$sql= $sql."	,P.ProjectNickname PNShort									";	//프로젝트 닉네임
		$sql= $sql."	,D.EntryJob EntryJob										";	//업무내용
		$sql= $sql."	,D.EntryJob EntryJobShort									";	//업무내용
		$sql= $sql."	,D.modify modify											";	//연장근무 승인여부
		$sql= $sql." From															";
		$sql= $sql." dallyproject_tbl D												";
		$sql= $sql." , project_tbl P												";
		$sql= $sql." WHERE															";
		$sql= $sql." D.EntryPCode  = replace(P.ProjectCode, 'XX','".$shortY."')		";
		$sql= $sql." AND															";
		$sql= $sql." D.MemberNo = '".$memberID."'									";
		$sql= $sql." ORDER BY D.EntryTime DESC										";
		$sql= $sql." LIMIT 0,6														";
		/* ----------------------------- */
		$result = mysql_query($sql,$db);
		/* ----------------------------- */
		while($re_row = mysql_fetch_array($result)){
			switch ($re_row[DN]) {
				case "Sunday":
				 $re_row[DN]="일";
				 break;
				case "Monday":
				 $re_row[DN]="월";
				  break;
				case "Tuesday":
				 $re_row[DN]="화";
				  break;
				case "Wednesday":
				 $re_row[DN]="수";
				  break;
				case "Thursday":
				 $re_row[DN]="목";
				  break;
				case "Friday":
				 $re_row[DN]="금";
				  break;
				case "Saturday":
				 $re_row[DN]="토";
				  break;
				default:
				  echo "";
			} //switch End
			if(($re_row[DN]=="토") or ($re_row[DN]=="일"))
			{
				$re_row[dayColor] = "day_red";
			}

			$re_row[PNShort] =utf8_strcut($re_row[PNShort],10,'...');

			array_push($query_data,$re_row);
		} //while End
		/* ----------------------------- */
			//============================================================================
			// 사진
			//============================================================================
			$src_photo = "../erpphoto/".$memberID.".jpg";
			$src_photo1 = "../erpphoto/".$memberID.".gif";
			/* ----------------------------- */
			if(file_exists($src_photo)) {
				$MemberPic=$src_photo;
			}else if(file_exists($src_photo1)){
				$MemberPic=$src_photo2;
			}else{
				$MemberPic="../erpphoto/noimage.gif";
			}//if End
		/* ----------------------------- */
		$this->assign('memberID',$memberID);
		$this->assign('n_num',$memberID);
		/* ----------------------------- */
		$korName = $_SESSION['CK_korName'];
		$this->assign('korName',$korName);
		/* ----------------------------- */
		$this->assign('MemberPic',$MemberPic);
		/* ----------------------------- */
		$this->assign('query_data03',$query_data);
		/* ----------------------------- */
	}//SelectDataList2 End

	/* ******************************************************************************************* */
	function myinfo()
	{
		/* ----------------------------- */
		global $db;
		global $memberID;
		/*----------------------------------*/
		global $RankCode;	//직급코드
		global $GroupCode;	//부서코드
		global $SortKey;	//직급+부서코드
		global $EntryDate;	//입사일자
		/*----------------------------------*/
		//============================================================================
		// 이름,부서,직급
		//============================================================================
		$sql= "			SELECT																";
		$sql= $sql."	  a.korName as Name													";
		$sql= $sql."	 ,b.Name as GroupName												";
		$sql= $sql."	 ,a.Name as Position												";
		$sql= $sql."	 ,a.Mobile as Mobile												";
		$sql= $sql."	 ,a.ManageIP as ManageIP												";
		$sql= $sql."	 FROM																";
		$sql= $sql."	 (																	";
		$sql= $sql."	 	SELECT * from													";
		$sql= $sql."	 	(																";
		$sql= $sql."	 		SELECT * from member_tbl where MemberNo = '".$memberID."'	";
		$sql= $sql."	 	)a1 left JOIN													";
		$sql= $sql."	 	(																";
		$sql= $sql."	 		SELECT * from systemconfig_tbl where SysKey='PositionCode'	";
		$sql= $sql."	 	)a2 on a1.RankCode = a2.code									";
		$sql= $sql."																		";
		$sql= $sql."	 ) a left JOIN														";
		$sql= $sql."	 (																	";
		$sql= $sql."	 	SELECT * from systemconfig_tbl where SysKey='GroupCode'			";
		$sql= $sql."	 )b on a.GroupCode = b.code											";
		/* ----------------------------- */
		// if($memberID == 'B23030' || $memberID == 'TADMIN') { $sql = "SELECT a.korName as Name ,b.Name as GroupName ,a.Name as Position ,a.Mobile as Mobile, a.ManageIP FROM ( SELECT * from ( SELECT * from member_tbl_copy where MemberNo = 'B23030' )a1 left JOIN ( SELECT * from systemconfig_tbl where SysKey='PositionCode' )a2 on a1.RankCode = a2.code ) a left JOIN ( SELECT * from systemconfig_tbl where SysKey='GroupCode' )b on a.GroupCode = b.code "; echo $sql;}
		// if($memberID == 'Tadmin' || $memberID == 'TADMIN') { $sql = "SELECT a.korName as Name ,b.Name as GroupName ,a.Name as Position ,a.Mobile as Mobile, a.ManageIP FROM ( SELECT * from ( SELECT * from member_tbl where MemberNo = 'TADMIN' )a1 left JOIN ( SELECT * from systemconfig_tbl where SysKey='PositionCode' )a2 on a1.RankCode = a2.code ) a left JOIN ( SELECT * from systemconfig_tbl where SysKey='GroupCode' )b on a.GroupCode = b.code ";}
		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		/* ----------------------------- */
		if($re_num != 0){
			$Name = mysql_result($re,0,"Name");
			$GroupName = mysql_result($re,0,"GroupName");
			$Position = mysql_result($re,0,"Position");
			$Mobile = mysql_result($re,0,"Mobile");
			// 그룹, 사번 조건 추가
			// if($memberID == 'Tadmin' || $memberID == 'TADMIN'){
			$ManageIP = mysql_result($re,0,"ManageIP");
			// 총괄기획실과 기술개발센터만 선적용
			if($GroupCode == '03'|| $GroupCode == '98'){ $myip_yn = $ManageIP != $_SERVER['REMOTE_ADDR'] ? "N" : "Y"; }
			else{ $myip_yn = "Y" ;}
			
			// 김영수 부사장님 예외처리 240517 서승완
			if($memberID == 'B18209'){ $myip_yn = "Y" ;}	
			// 한맥빌딩 네트워트 변동 이슈로 242, 243 IP 예외 체크 24.05.10 서승완 
			// 61.98.205.242, 61.98.205.243
			if($_SERVER['REMOTE_ADDR'] == "61.98.205.242" &&  $myip_yn == "N"){
				$myip_yn = $ManageIP == "61.98.205.243" ? "Y" : "N"; 
			} 
			if($_SERVER['REMOTE_ADDR'] == "61.98.205.243"  && $myip_yn == "N"){
				$myip_yn = $ManageIP == "61.98.205.242" ? "Y" : "N"; 
			}
			// $myip_yn = ($ManageIP != $_SERVER['REMOTE_ADDR'] and $office_type != '4') ? "N" : "Y";

		}//if End
		//============================================================================
		// 사진
		//============================================================================
		$src_photo = "../erpphoto/".$memberID.".jpg";
		$src_photo1 = "../erpphoto/".$memberID.".gif";
		/* ----------------------------- */
		if(file_exists($src_photo)) {
			$MemberPic=$src_photo;
		}else if(file_exists($src_photo1)){
			$MemberPic=$src_photo2;
		}else{
			$MemberPic="../erpphoto/noimage.gif";
		}//if End

		//연차사용계획서----------------------------------------
		//if($memberID=="B21329" ){ //|| substr($RankCode,0,1)=="E" ){
		//if($memberID=="B21329" ){ //|| substr($RankCode,0,1)=="E" ){
		//if($memberID=="M21464"){

			include "./sys/util/MysqlClass.php";

			//----------------------------------------------------------------------
			$this->mysql=new MysqlClass();
			$yyyy = date("Y");
			$yyyymmdd = date("Y-m-d");
			//$SET_PROCEDURE_SQL = "CALL PRO_GET_BOOST_ALRAM_INFO('$memberID','$yyyy','$yyyymmdd',''); ";//
			$SET_PROCEDURE_SQL = "CALL PRO_GET_BOOST_ALRAM_INFO_HOME('$memberID','$yyyy','$yyyymmdd'); ";//
			$datarow =  $this->mysql->ClassFN_LoadData_Procedure($SET_PROCEDURE_SQL,'NEXT');
			//print_R($SET_PROCEDURE_SQL);
			//echo $SET_PROCEDURE_SQL."</br>";
			//print_R($datarow);
			//echo "</br>";




			$VAC_INFO_ALRAM_YN             = $datarow[0]["V_ALRAM_YN"];


			$VAC_INFO_REMAIND_DAY         = $datarow[0]["vb_remaind_day"];


// 			if($memberID=="M22001" ){
// 			    echo $SET_PROCEDURE_SQL."<br>";
// 			    echo $VAC_INFO_ALRAM_YN."<br>";
// 			    echo $VAC_INFO_REMAIND_DAY."<br>";

// 			}



			/*
			$VAC_INFO_AGO2                 = $datarow[0]["AGO2"];
			$VAC_INFO_AGO3                 = $datarow[0]["AGO3"];
			$VAC_INFO_AGO6                 = $datarow[0]["AGO6"];

			$VAC_INFO_AGO1                 = $datarow[0]["AGO1"];
			$VAC_INFO_AGO10D               = $datarow[0]["AGO10D"];

			$VAC_INFO_ALRAM_CNT            = $datarow[0]["V_ALRAM_CNT"];
			$VAC_INFO_ALRAM_DATE           = $datarow[0]["V_ALRAM_DATE"];
			$VAC_INFO_ALRAM_DEGREE         = $datarow[0]["V_ALRAM_DEGREE"];
			$VAC_INFO_ALRAM_YN             = $datarow[0]["V_ALRAM_YN"];
			$VAC_INFO_ENTRY_DATE           = $datarow[0]["V_ENTRY_DATE"];
			$VAC_INFO_EXPIRY_DATE          = $datarow[0]["V_EXPIRY_DATE"];
			$VAC_INFO_KORNAME              = $datarow[0]["V_KORNAME"];
			$VAC_INFO_OCCUR_DATE           = $datarow[0]["V_OCCUR_DATE"];
			$VAC_INFO_OCCUR_DAYS           = $datarow[0]["V_OCCUR_DAYS"];
			$VAC_INFO_RANKCODE             = $datarow[0]["V_RANKCODE"];
			$VAC_INFO_REMAIN_DAYS          = $datarow[0]["V_REMAIN_DAYS"];
			$VAC_INFO_USED_DAYS            = $datarow[0]["V_USED_DAYS"];
			$VAC_INFO_USER_ID              = $datarow[0]["V_USER_ID"];
			$VAC_INFO_VACATION_TYPE        = $datarow[0]["V_VACATION_TYPE"];
			$VAC_INFO_VACATION_TYPE_KOR    = $datarow[0]["V_VACATION_TYPE_KOR"];
			$VAC_INFO_WORKPOSITION         = $datarow[0]["V_WORKPOSITION"];
			$VAC_INFO_WORK_AREA_DIV        = $datarow[0]["V_WORK_AREA_DIV"];
			$VAC_INFO_WORK_PERIOD_TYPE     = $datarow[0]["V_WORK_PERIOD_TYPE"];    //W0=1년미만, W1=1년, W2=일년이상
			$VAC_INFO_WORK_PERIOD_TYPE_KOR = $datarow[0]["V_WORK_PERIOD_TYPE_KOR"];
*/

			/*
			V_WORK_PERIOD_TYPE = 'W0';			# 0=일년미만
			*/


			//----------------------------------------------------------------------
			$VAC_INFO_DOC_EXIST_YN = "N";
			$VAC_INFO_DOC_STATUS   = "";



			if($VAC_INFO_ALRAM_YN=="Y" && $VAC_INFO_REMAIND_DAY >0.4){




				$VAC_INFO_ALRAM_DATE           	= $datarow[0]["vb_notice_dt"];
				$VAC_INFO_ALRAM_DEGREE         	= $datarow[0]["vb_degree"];
				$datarow[0]["V_ALRAM_DATE"] 	= $VAC_INFO_ALRAM_DATE;
				$datarow[0]["V_ALRAM_DEGREE"] 	= $VAC_INFO_ALRAM_DEGREE;

				$VAC_INFO_VB_ETC_01         	= $datarow[0]["vb_etc_01"];
				$datarow[0]["V_WORK_PERIOD_TYPE"] 		= "";
				$datarow[0]["V_WORK_PERIOD_TYPE_KOR"] 	= "";

				if($VAC_INFO_VB_ETC_01=="회계일자/일년미만" ){
					$datarow[0]["V_WORK_PERIOD_TYPE"] 		= "W0";
					$datarow[0]["V_WORK_PERIOD_TYPE_KOR"] 	= "회계일자/일년미만";
				}
				if($VAC_INFO_VB_ETC_01=="입사일자/일년미만"){
					$datarow[0]["V_WORK_PERIOD_TYPE"] 		= "W0";
					$datarow[0]["V_WORK_PERIOD_TYPE_KOR"] 	= "입사일자/일년미만";
				}

				//----------------------------------------------------------------------
				$timestamp = strtotime($VAC_INFO_ALRAM_DATE);
				$timestamp_ch = date("Y-m-d", $timestamp);
				$timestamp_ch2 =strtotime($timestamp_ch. '+9 days');
				$AFTER_10 = date("Y-m-d", $timestamp_ch2);
				$AFTER_10_REMAIN = daycount(date("Y-m-d"), $AFTER_10); //고지일자기준 10일뒤 날짜까지 남은 일자(오늘기준)

				//----------------------------------------------------------------------
				$timestamp_22 = strtotime($AFTER_10);
				$timestamp_ch_22 = date("Y-m-d", $timestamp_22);
				$timestamp_ch2_5d =strtotime($timestamp_ch_22. '-5 days');
				$AFTER_5 = date("Y-m-d", $timestamp_ch2_5d); //고지일자기준 5일뒤 날짜
				$datarow[0]["AFTER_5"] = $AFTER_5;

				//echo $AFTER_10_REMAIN.'<br>';
				//----------------------------------------------------------------------
				$datarow[0]["AFTER_10"] = $AFTER_10;
				$datarow[0]["AFTER_10_REMAIN"] = $AFTER_10_REMAIN;//고지일자기준 10일뒤 날짜까지 남은 일자(오늘기준)

				//$AFTER_10='2022-11-01';
				//----------------------------------------------------------------------
				$datarow[0]["WORK_START_YN"] = "N";
				if(strtotime(date("Y-m-d"))<=strtotime($AFTER_10)){
					$datarow[0]["WORK_START_YN"] = "Y";
				}

				//연차촉진대상자이고 결재진행중인 연차사용계획서 존재여부
				$SET_PROCEDURE_SQL_DOC = "CALL USP_VACATION_BOOST_DOC_STATUS_INFO('$memberID','$VAC_INFO_ALRAM_DATE','$VAC_INFO_ALRAM_DEGREE'); ";
				$datarow_DOC =  $this->mysql->ClassFN_LoadData_Procedure($SET_PROCEDURE_SQL_DOC,'NEXT');
				$VAC_INFO_DOC_EXIST_YN = $datarow_DOC[0]["DOC_EXIST_YN"];
				$VAC_INFO_DOC_STATUS   = $datarow_DOC[0]["DOC_STATUS"];

				if($VAC_INFO_DOC_EXIST_YN=="Y"){
					//결재진행중인 연차사용계획서(연차촉진)가 존재할 경우 => 알람 X
					$datarow[0]["V_ALRAM_YN"] = "N";
				}else{
				}
			}else{}

			if($VAC_INFO_ALRAM_YN=="Y" && $VAC_INFO_REMAIND_DAY <0.5){
			    $datarow[0]["V_ALRAM_YN"] = "N";
			}
			$datarow[0]["DOC_EXIST_YN"] = $VAC_INFO_DOC_EXIST_YN;
			$datarow[0]["DOC_STATUS"]   = $VAC_INFO_DOC_STATUS;
			//----------------------------------------------------------------------

// 			echo $SET_PROCEDURE_SQL_DOC;
// 			print_r($datarow_DOC);

			//print_r($datarow);
			$this->assign('VACAIOTN_INFO',$datarow);

			//if($memberID=="HM01836"){
				//print_r($datarow);
				//$this->assign('VACAIOTN_INFO_ARRAY',$datarow);
				$this->assign('VACAIOTN_INFO_JSON',json_encode($datarow));
			//}
		//}
		//연차사용계획서----------------------------------------

		/* ----------------------------- */
		$this->assign('memberID',$memberID);
		$this->assign('Name',$Name);
		$this->assign('korName',$Name);
		$this->assign('GroupName',$GroupName);
		$this->assign('Position',$Position);
		$this->assign('MemberPic',$MemberPic);

		$this->assign('Mobile',$Mobile);
		/* ----------------------------- */
		$this->assign('RankCode',$RankCode);   //직급코드
		$this->assign('GroupCode',$GroupCode); //부서코드
		$this->assign('SortKey',$SortKey);	   //직급+부서코드
		$this->assign('EntryDate',$EntryDate); //입사일자
		$this->assign('myip_yn', $myip_yn); // 접속 IP 체크
		$this->assign('ManageIP', $ManageIP); // 접속 IP 체크
		$this->assign('currIp', $_SERVER['REMOTE_ADDR']); // 접속 IP 체크

		/* ----------------------------- */
	}//myinfo() End
	/* ******************************************************************************************* */
	/* 전자결재 카운트------------------------------------------------------------------------------ */
	function ApprovalCount()
	{
		/* ----------------------------- */
		include "./sys/inc/approval_var.php";
		/* ----------------------------- */
		global $db;
		global $memberID;
		global $CompanyKind;
		/* ----------------------------- */
		//처리부서 담당자 체크
		$sql="select * from approval_tbl where ReceiveMember='$memberID'  ";
		$re = mysql_query($sql,$db);
		$re_row = mysql_num_rows($re);//총 개수 저장
		/* ----------------------------- */
		while($re_row = mysql_fetch_array($re)){
			if(strpos($re_row[FormName], "HMF-5") === false){
				$FormList=$FormList."'".$re_row[FormName]."',";
			}else{
				$FormList_Account=$FormList_Account."'".$re_row[FormName]."',";
			}
		}//while End
		/* ----------------------------- */
		$FormList=substr($FormList,0,strlen($FormList)-1);
		$FormList_Account=substr($FormList_Account,0,strlen($FormList_Account)-1);
		/* ----------------------------- */
		$MyGroupCode=$_SESSION['MyGroupCode'] ;

		if($FormList == "") {  //일반사용자
			$sql = "
			select *
			from view_sanctiondoc_tbl
			where
				RT_SanctionState like '%".$SANCTION_CODE.":".$memberID."%'
				or RT_SanctionState like '%".$SANCTION_CODE2.":".$memberID."%'
				or ( RT_SanctionState like '%".$PROCESS_FINISH."%' and confirm_members like '%".$memberID."/0%' )
			";
			//echo $sql;
			$re = mysql_query($sql,$db);
			$count = mysql_num_rows($re);
			if($count > 0){
				$WaitDoc_Count= $count;
			}else{
				$WaitDoc_Count=0;
			}//if End
			/* ------------------------ */
			
			$sql2 = "select * from SanctionDoc_tbl where MemberNo='".$memberID."' and (RT_SanctionState like '%".$PROCESS_REJECTION."%' or RT_SanctionState like '%".$PROCESS_RETURN."%')";
			$re2 = mysql_query($sql2,$db);
			$count_reject = mysql_num_rows($re2);
			if($count_reject > 0) {
				$Reject_Count= $count_reject;
			}else{
				$Reject_Count=0;
			}//if End
			/* ------------------------ */
			$sql3 = "select * from SanctionDoc_tbl where MemberNo='".$memberID."' and (RT_SanctionState like '%".$SANCTION_CODE."%' or RT_SanctionState like '%".$SANCTION_CODE2."%' or RT_SanctionState like '%".$PROCESS_RECEIVE."%')";
			$re3 = mysql_query($sql3 ,$db);
			$count_up = mysql_num_rows($re3);
			if($count_up > 0) {
				$Up_Count= $count_up;
			}else{
				$Up_Count=0;
			}//if End
			/* ------------------------ */
		}else {               //처리부서 접수담당자
			$sql = "
			select *
			from view_sanctiondoc_tbl
			where
				(RT_SanctionState like '%".$SANCTION_CODE.":".$memberID."%' or RT_SanctionState like '%".$SANCTION_CODE2.":".$memberID."%')
				or (PG_Code='".$MyGroupCode."' and RT_SanctionState like '%".$PROCESS_RECEIVE."%' and FormNum in ($FormList))
				or ( RT_SanctionState like '%".$PROCESS_FINISH."%' and confirm_members like '%".$memberID."/0%' )
			";
			//echo $sql;
			$re = mysql_query($sql,$db);
			$count = mysql_num_rows($re);
			if($count > 0) {
				$WaitDoc_Count=$count;
			}else{
				$WaitDoc_Count=0;
			}//if End
			/* ------------------------ */
			$sql2 = "select * from SanctionDoc_tbl where MemberNo='".$memberID."' and (RT_SanctionState like '%".$PROCESS_REJECTION."%' or RT_SanctionState like '%".$PROCESS_RETURN."%')";
			//echo "처리부서담당자".$azSQL."<br>";
			$re2 = mysql_query($sql2,$db);
			$count_reject = mysql_num_rows($re2);
			if($count_reject > 0) {
				$Reject_Count= $count_reject;
			}else{
				$Reject_Count=0;
			}//if End
			/* ------------------------ */
			$sql3 = "select * from SanctionDoc_tbl where MemberNo='".$memberID."' and (RT_SanctionState like '%".$SANCTION_CODE."%' or RT_SanctionState like '%".$SANCTION_CODE2."%' or RT_SanctionState like '%".$PROCESS_RECEIVE."%')";
			$re3 = mysql_query($sql3,$db);
			$count_up = mysql_num_rows($re3);
			if($count_up > 0) {
				$Up_Count= $count_up;
			}else{
				$Up_Count=0;
			}//if End
			/* ------------------------ */
		}//if End
		/* ----------------------------- */

		//결재할 문서 카운트 추가
		$sql02 = "select count(*) cnt from sanction_notice_tbl where send_member like '%".$memberID."%' and read_member not like '%".$memberID."%'  ";
		
		$result02 = mysql_query($sql02,$db);
		$cnt = mysql_result($result02,0,"cnt");
		$cnt_sum  = $WaitDoc_Count+$cnt;
		//echo $cnt."<br>";
		//echo $WaitDoc_Count."<br>";
		/* ----------------------------- */
		//$this->assign('WaitDoc_Count',$WaitDoc_Count);//결재할 문서 건수 (기존)
		$this->assign('WaitDoc_Count',$cnt_sum);//결재할 문서 건수 (수정)
		/* ----------------------------- */
		$this->assign('Reject_Count',$Reject_Count);
		$this->assign('Up_Count',$Up_Count);
		$this->assign('CompanyKind',$CompanyKind);
		/* ----------------------------- */




//echo $FormList_Account;
		//전표카운트////////////////////////////////////////////////////////////////////////////////////////

			//토스 받은 문서 검색
			$azSQL = "
				SELECT
					DocSN
				FROM approval_account_tbl
				WHERE MemberNo LIKE '$memberID'
			";
			$re = mysql_query($azSQL,$db);
			while($re_row = mysql_fetch_array($re)){
				$ReciveList=$ReciveList."'".$re_row[DocSN]."',";
			}
			$ReciveList=substr($ReciveList,0,strlen($ReciveList)-1);
			//echo "ReciveList = ".$ReciveList."<br>";

			//토스한 문서 검색
			$azSQL = "
				SELECT
					DocSN
				FROM approval_account_tbl
				WHERE OriginMemberNo LIKE '$memberID'
			";
			$re = mysql_query($azSQL,$db);
			while($re_row = mysql_fetch_array($re)){
				$TossList=$TossList."'".$re_row[DocSN]."',";
			}
			$TossList=substr($TossList,0,strlen($TossList)-1);
			//echo "TossList = ".$TossList."<br>";
			
			// 김윤재, 박주한 , 장종찬 240521 서승완 
			// 김윤재->신종호 사용자 변경, 김지영A[M21430] 사원 추가 20241230 김한결
			if($memberID == "B23029" || $memberID == "M22006" || $memberID == "M02210" || $memberID == "M21430"){
				$where_code = " AND RG_CODE IN ('02','03','98') ";
			}
			else if($memberID == "T02328" || $memberID == "TADMIN"){
				$where_code = " AND RG_CODE LIKE '%' ";
			}
			else {
				$where_code = " AND RG_CODE NOT IN ('02','03','98') ";
			}

			$sql4 = "";
			if($ReciveList){
				$sql4 .= "
					select * from view_sanctiondoc_tbl where DocSN in ($ReciveList)
					union
				";
			}
			$sql4 .= "
				select
					*
				from
					view_sanctiondoc_tbl
				where
					 ";
			if($FormList_Account != ""){

					if($TossList){
						$sql4 .= " DocSN NOT IN ($TossList) AND ";
					}

						$sql4 .= " ( FormNum in ($FormList_Account) and RT_SanctionState like '%".$PROCESS_RECEIVE."%' ".$where_code;




				$sql4 .= ") OR";

			}

				$sql4 .= "
					(
						FormNum like 'HMF-5-%'
						AND RT_SanctionState like '%처리부서내:$memberID%'
						AND SUBSTRING_INDEX(RT_SanctionState, ':', 1) > 5
					)
				";
				$sql4 .= " $where_code";
				//echo $sql4;
				

				$re4 = mysql_query($sql4,$db);
				$count_account = mysql_num_rows($re4);

				if($count_account > 0) {
					$Account_Count= $count_account;
				}else{
					$Account_Count=0;
				}

				$this->assign('Account_Count',$Account_Count);


				// ERP 전자결재 카운트
				// $erp_no=erp_count($memberID);

				// ERP 전자결재 카운트 240318 intranet sync 데이터 조회로 변경 _서승완
				$sql5 = "SELECT ApprovalCnt from approval_count_tbl where memberNo = '$memberID'";
				$re5 = mysql_query($sql5,$db);
				if(mysql_num_rows($re5) > 0){
					$erp_no = mysql_result($re5,0,"ApprovalCnt");
				}
				else{
					$erp_no = 0;
				}

				$this->assign('Erp_Count',$erp_no);

				// 대여신청 카운트 (남궁성)
				if($memberID=="T03225" || $memberID=="B15306")
				{
					// 22.03.08 도서대여 방법 변경으로 인한 주석처리
					/*
					$sql5 = "select * from library_rent_tbl a where status='APPLICATION'";
					$re5 = mysql_query($sql5,$db);
					$Book_Count = mysql_num_rows($re5);
					*/
				}

				$temp_echo = array(
					'WaitDoc_Count'=>$cnt_sum,
					'Reject_Count'=>$Reject_Count,
					'Up_Count'=>$Up_Count,
					'Account_Count'=>$Account_Count,
					'Erp_Count'=>$erp_no,
					'Book_Count'=>$Book_Count
				);
				echo json_encode($temp_echo);


	}
	/* 전자결재 카운트 끝------------------------------------------------------------------------------ */

	/* ******************************************************************************************* */
	/* 현황공유(배차.비품,출장)------------------------------------------------------------------------------ */
	function CarCount()
	{
		/* ----------------------------- */
		global $db;
		global $memberID;
		/* ----------------------------- */
		$Today=date("Y-m-d");
		$korName=$_SESSION['korName'];
		/* ----------------------------- */
		//배차현황
		$sql = "SELECT * FROM schedule_car_tbl WHERE membername='".$korName."' and sdate <='".$Today."' and edate >='".$Today."'";
		$Today=date("m");
		/* ----------------------------- */
		//echo $sql;
		$re = mysql_query($sql,$db);
		$count = mysql_num_rows($re);
		/* ----------------------------- */
		if($count > 0) {
			$Car_Count= $count;
		}else{
			$Car_Count=0;
		}//if End
		/* ----------------------------- */
		//비품현황
		$sql2= "SELECT * FROM schedule_device_tbl WHERE membername='".$korName."' and sdate <='".$Today."' and edate >='".$Today."'";
		//echo $sql;
		$re2 = mysql_query($sql2,$db);
		$count2 = mysql_num_rows($re2);
		/* ----------------------------- */
		if($count2 > 0) {
			$equipment_Count= $count2;
		}else {
			$equipment_Count=0;
		}//if End
		/* ----------------------------- */
		//출장현황
		$sql3= "SELECT * FROM official_plan_tbl WHERE o_start<= '".$Today."'  and o_end >= '".$Today."' and o_change='2'  and o_name='".$korName."'";
		$re3 = mysql_query($sql3,$db);
		$count3 = mysql_num_rows($re3);
		/* ----------------------------- */
		if($count3 > 0) {
			$trip_Count= $count3;
		}else{
			$trip_Count=0;
		}//if End
		/* ----------------------------- */
		$this->assign('Car_Count',$Car_Count);
		$this->assign('equipment_Count',$equipment_Count);
		$this->assign('trip_Count',$trip_Count);
		$this->assign('CompanyKind',$CompanyKind);
		/* ----------------------------- */
	}

	/* ******************************************************************************************* */
	/* 날씨(서울,당진)------------------------------------------------------------------------------ */
	function Weather_real()
	{
		$url="http://www.kma.go.kr/XML/weather/sfc_web_map.xml";
		$xml=@simplexml_load_file($url);

		if(!$xml){  //false
		$this->assign('weatherStatus',"false");

		}else{		//true
		$this->assign('weatherStatus',"true");

				$weather=$xml->weather;
				foreach($weather->local as $local){
					//$local2=iconv('UTF-8','EUC-KR',$local);
					$local2=$local;
					if($local2=="서울"){
						//1:맑음 2:구름조금 3:구름많음 4:흐림 5:비 6:눈/비  7:눈  8:비 15:안개 17:박무 18:연무
						$icon=$local[icon];
						$weather=$local[desc];
						$temp=$local[ta];

						if($icon =="01") //맑음
						{	$iconimg="weather_icon02.png";
						}else if($icon =="02") //구름조금
						{	$iconimg="weather_icon01.png";
						}else if($icon =="03") //구름많음
						{	$iconimg="weather_icon03.png";
						}else if($icon =="04") //흐림
						{	$iconimg="weather_icon04.png";
						}else if($icon =="05" || $icon =="08" ) //비
						{	$iconimg="weather_icon05.png";
						}else if($icon =="06") //눈/비
						{	$iconimg="weather_icon06.png";
						}else if($icon =="07") //눈
						{	$iconimg="weather_icon07.png";
						}else if($icon =="15" || $icon =="17" || $icon =="18") //안개
						{	$iconimg="weather_icon08.png";
						}else
						{	$iconimg="weather_icon03.png"; }
						$this->assign('icon',$icon);
						$this->assign('weather',$weather);
						$this->assign('temp',$temp);
						$this->assign('iconimg',$iconimg);
					}//if End
					if($local2=="서산"){
						//1:맑음 2:구름조금 3:구름많음 4:흐림 5:비 6:눈/비  7:눈  8:비 15:안개 17:박무 18:연무
						$icon2=$local[icon];
						$weather2=$local[desc];
						$temp2=$local[ta];

						if($icon2 =="01") //맑음
						{	$iconimg2="weather_icon02.png";
						}else if($icon2 =="02") //구름조금
						{	$iconimg2="weather_icon01.png";
						}else if($icon2 =="03") //구름많음
						{	$iconimg2="weather_icon03.png";
						}else if($icon2 =="04") //흐림
						{	$iconimg2="weather_icon04.png";
						}else if($icon2 =="05" || $icon2 =="08" ) //비
						{	$iconimg2="weather_icon05.png";
						}else if($icon2 =="06") //눈/비
						{	$iconimg2="weather_icon06.png";
						}else if($icon2 =="07") //눈
						{	$iconimg2="weather_icon07.png";
						}else if($icon2 =="15" || $icon2 =="17" || $icon2 =="18") //안개
						{	$iconimg2="weather_icon08.png";
						}else
						{	$iconimg2="weather_icon03.png";
						}
						$this->assign('icon2',$icon2);
						$this->assign('weather2',$weather2);
						$this->assign('temp2',$temp2);
						$this->assign('iconimg2',$iconimg2);
					}//if End
				}//foreach End
		}//if End 기상청사이트 XML확인
		/* ------------------------------------ */
		$date_today  = date("Y-m-d");
		$tmp = split("-",$date_today);
		$tday = week_day($tmp[0],$tmp[1],$tmp[2]);
		$this->assign('Now_year',$tmp[0]);
		$this->assign('Now_month',$tmp[1]);
		$this->assign('Now_day',$tmp[2]);
		$this->assign('Now_date',$tday);

	}//Weather End
	/* ******************************************************************************************* */

	function Weather()
	{
		global $db;
		$sql="select * from weather_tbl where areacode in('108','129') order by areacode";  //108 서울 129 서산(당진)
		$re = mysql_query($sql,$db);
		if(mysql_num_rows($re) > 0) {
			while($re_row = mysql_fetch_array($re))
			{
				$areacode=$re_row[areacode];
				if($areacode =="108") //서울
				{
					$icon=$re_row[icon];
					$weather=$re_row[weather];
					$temp=$re_row[temp];

					if($icon =="01") //맑음
					{	$iconimg="weather_icon02.png";
					}else if($icon =="02") //구름조금
					{	$iconimg="weather_icon01.png";
					}else if($icon =="03") //구름많음
					{	$iconimg="weather_icon03.png";
					}else if($icon =="04") //흐림
					{	$iconimg="weather_icon04.png";
					}else if($icon =="05" || $icon =="08" ) //비
					{	$iconimg="weather_icon05.png";
					}else if($icon =="06") //눈/비
					{	$iconimg="weather_icon06.png";
					}else if($icon =="07") //눈
					{	$iconimg="weather_icon07.png";
					}else if($icon =="15" || $icon =="17" || $icon =="18") //안개
					{	$iconimg="weather_icon08.png";
					}else
					{	$iconimg="weather_icon03.png"; }
					$this->assign('icon',$icon);
					$this->assign('weather',$weather);
					$this->assign('temp',$temp);
					$this->assign('iconimg',$iconimg);

				}else  //서산(당진)
				{
					$icon2=$re_row[icon];
					$weather2=$re_row[weather];
					$temp2=$re_row[temp];

					if($icon2 =="01") //맑음
					{	$iconimg2="weather_icon02.png";
					}else if($icon2 =="02") //구름조금
					{	$iconimg2="weather_icon01.png";
					}else if($icon2 =="03") //구름많음
					{	$iconimg2="weather_icon03.png";
					}else if($icon2 =="04") //흐림
					{	$iconimg2="weather_icon04.png";
					}else if($icon2 =="05" || $icon2 =="8" ) //비
					{	$iconimg2="weather_icon05.png";
					}else if($icon2 =="06") //눈/비
					{	$iconimg2="weather_icon06.png";
					}else if($icon2 =="07") //눈
					{	$iconimg2="weather_icon07.png";
					}else if($icon2 =="15" || $icon2 =="17" || $icon2 =="18") //안개
					{	$iconimg2="weather_icon08.png";
					}else
					{	$iconimg2="weather_icon03.png";
					}
					$this->assign('icon2',$icon2);
					$this->assign('weather2',$weather2);
					$this->assign('temp2',$temp2);
					$this->assign('iconimg2',$iconimg2);
				}
			}
			$this->assign('weatherStatus',true);
		}else
		{		/*
				$this->assign('icon','01');
				$this->assign('weather','맑음');
				$this->assign('temp','0');
				$this->assign('iconimg','weather_icon01.png');

				$this->assign('icon2','01');
				$this->assign('weather2','맑음');
				$this->assign('temp2','0');
				$this->assign('iconimg2','weather_icon01.png');
				*/
				$this->assign('weatherStatus',false);
		}

		$date_today  = date("Y-m-d");
		$tmp = split("-",$date_today);
		$tday = week_day($tmp[0],$tmp[1],$tmp[2]);
		$this->assign('Now_year',$tmp[0]);
		$this->assign('Now_month',$tmp[1]);
		$this->assign('Now_day',$tmp[2]);
		$this->assign('Now_date',$tday);
	}

	/* ******************************************************************************************* */
	function BlackAction()		//외출버튼 관련 기능
	{
		/* -----------------*/
		global	$MemberNo;		// 사원번호
		global	$korName;		// 한글이름
		global	$GroupCode;		// 부서
		global	$RankCode;		// 직급
		global	$SortKey;		// 직급+부서
		/* ----------------- */
		global	$date_today;	// 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1;	// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$nowYear;		// 오늘날짜 년          : yyyy
		global	$nowMonth;		// 오늘날짜 년월        : yyyy-mm
		global	$nowHour;		// 현재 시
		global	$nowMin;		// 현재 분
		global	$nowTime;		// 현재 시:분
		/* ----------------- */
		global $db;
		/* ----------------- */
		$str_time	= $_GET['str_time'];
		$this->assign('b_str_time',$str_time);	//외출시작시간
		/* ------------------------------------ */
		$this->assign('b_MemberNo',$MemberNo);		// 사원번호
		$this->assign('b_korName',$korName);		// 한글이름
		$this->assign('b_GroupCode',$GroupCode);	// 부서
		$this->assign('b_RankCode',$RankCode);		// 직급
		$this->assign('b_date_today',$date_today);	// 오늘날짜 년월일      : yyyy-mm-dd
		$this->assign('b_nowTime',$nowTime);		// 현재 시:분
		/* ----------------- */
		$this->display("intranet/common_layout/main_home_black.tpl");
	} //BlackAction End

	/* ******************************************************************************************* */
	function MyStatus()
	{
		/* ------------------------------------ */
		global $db;
		global $memberID;
		/* ------------------------------------ */
		$sql= "SELECT ";
		$sql= $sql."  DATE_FORMAT(a.EntryTime, '%Y-%m-%d') as EntryTime ";	//업무시작 시간
		$sql= $sql." ,DATE_FORMAT(a.OverTime, '%Y-%m-%d')  as OverTime	";	//연장근무시작 시간
		$sql= $sql." ,DATE_FORMAT(a.LeaveTime, '%Y-%m-%d') as LeaveTime ";	//연장근무시작 시간
		$sql= $sql." ,a.EntryPCode										";	//프로젝트코드
		$sql= $sql." ,a.EntryJobCode									";	//프로젝트서브코드
		$sql= $sql." ,b.ProjectNickname as ProjectNickname				";	//프로젝트 닉네임
		$sql= $sql." ,a.EntryJob										";	//업무내용
		$sql= $sql." FROM												";
		$sql= $sql." (													";
		$sql= $sql." SELECT * FROM dallyproject_tbl						";
		$sql= $sql." WHERE												";
		$sql= $sql." MemberNo = '$memberID'								";
		$sql= $sql." and EntryTime > '$FiveDay 00:00:00'				";
		$sql= $sql." and EntryTime < '$Today 23:59:59'					";
		$sql= $sql." ORDER BY EntryTime Desc limit 1					";
		$sql= $sql." ) a												";
		$sql= $sql." LEFT JOIN											";
		$sql= $sql." ( SELECT * FROM Project_tbl )b						";
		$sql= $sql." ON a.EntryPCode = b.ProjectCode					";
		/* ------------------------------------ */
		$result = mysql_query($sql,$db);
		$result_num = mysql_num_rows($result);
		if($result_num != 0) {
			/* ------------------------------------------------------------- */
			//WP = WorkPlan
			$EntryTime			= mysql_result($result,0,"EntryTime"); 			//업무시작 시간
			$OverTime			= mysql_result($result,0,"OverTime");			//연장근무시작 시간
			$LeaveTime			= mysql_result($result,0,"LeaveTime");			//업무종료 시간
			$code_EntryPCode	= mysql_result($result,0,"EntryPCode");			//프로젝트코드
			$EntryJobCode		= mysql_result($result,0,"EntryJobCode");		//프로젝트서브코드
			$ProjectNickname	= mysql_result($result,0,"ProjectNickname");	//프로젝트 닉네임
			$EntryJob			= mysql_result($result,0,"EntryJob");			//업무내용
			/* ------------------------------------------------------------- */
		}//if End
		/* ------------------------------------------------------------- */
		$this->assign('MemberPic',$MemberPic);
		/* ------------------------------------------------------------- */
	}// End


/* ******************************************************************************************* */
	//================================================
	//연차 리스트 출력
	//================================================
	function VacationList(){
		global $db;
		global $memberID;
		$GroupList = "";

		//근태코드 20,21 중인 사람들은 제외.
		$sql = " select * from userstate_tbl where MemberNo = '$memberID' and state in (20, 21) and '".date("Y-m-d")."' between start_time and end_time ";
		$result = mysql_query($sql,$db);
		$result_num = mysql_num_rows($result);

		if($result_num > 0){
			return true;
		}


		$sql ="select GroupCode,RankCode from member_tbl where MemberNo = '$memberID' ";

		$re = mysql_query($sql,$db);
		while($re_row = mysql_fetch_array($re)){
			$GroupList = $re_row[GroupCode];
			$RankCode = $re_row[RankCode];

		}
        // addSQl = ("GroupCode IN ($GroupList)")
		if($GroupList =="02" || $GroupList =="03" )
		{
			$FormNumType="BRF-4-9";
		}
		else
		{
			$FormNumType="HMF-4-9";
		}
        // 그룹추가 -> memberID 리스트로 변경 
        // if($memberID == "M02107"){
        //     $GroupList = "1, 11, 12, 13, 21, 22, 23, 24, 25, 31, 32";
        // }
		$this->assign('FormNumType',$FormNumType);
		$this->assign('current_day',date("Y").".".date("m").".".date("d"));

		//$RankCode="C8";
		//if($RankCode<="C8" and  $RankCode>="C6"){   //이사,상무,전무
			//개발 : M20329 신지호, M21420 문형석
			//책임 팀장 :B18214 신혜영, J08305 김우진, B22042 조선두
			$RankCode=substr($RankCode,0,2);
		if($RankCode<="C8" || $memberID == "M20329"|| $memberID=="M21420"|| $memberID=="Tadmin" || $memberID == "B18214" || $memberID == "J08305" || $memberID == "B22042"){   //이사이상
			// 이경훈사장님 일부 임원휴가 추가표출
			// '이현구' , '이성구', '박성웅' , '윤성호' ,'이광철' , '김상수' , '이병도' , '정혜연' , '현종철' , '박승신' , '이기종' , '신광수'
			if($memberID == 'M02107'){
				$addSql = " ( GroupCode IN ($GroupList) or a.memberNo in ('M03211', 'M04602', 'M02202', 'M06505', 'M10103', 'M12203', 'M14101', 'M16222', 'M20101', 'M21410', 'M21471', 'M22009') ) ";
			} else{ $addSql = " GroupCode IN ($GroupList) ";}
			$TodayMemberList = array();
			$sql =	"select
						a.MemberNo
						, b.korName
						, note as vaction_type
						, start_time
						, end_time
					from
						userstate_tbl a
						, (select MemberNo, korName from member_tbl where WorkPosition != 9 ) b
					where /*GroupCode IN ($GroupList)*/
					$addSql
						and (state like '1' or state='7' or state='8' or state='30'  or state='31')
						and start_time like '".date("Y-m-d")."'
						and a.MemberNo = b.MemberNo
			";

			//echo $sql."<br>";
			$re = mysql_query($sql,$db);	//당일 일때
			while($re_row = mysql_fetch_array($re)){

				$re_row[vaction_type]=str_replace("연차휴가사용계획서", "연차사용계획서", $re_row[vaction_type]);
				if(strpos($re_row[vaction_type],"보건휴가") >= 0){
					$re_row[vaction_type] = "휴가(개인사정)";
				}
				array_push($TodayMemberList,array( "MemberNo" => $re_row[MemberNo], "korName" => $re_row[korName], "vaction_type" => $re_row[vaction_type], "start_time" => $re_row[start_time], "end_time" => $re_row[end_time]));
			}
			$this->assign('TodayMemberList',$TodayMemberList);
			// 이경훈사장님 일부 임원휴가 추가표출
			// '이현구' , '이성구', '박성웅' , '윤성호' ,'이광철' , '김상수' , '이병도' , '정혜연' , '현종철' , '박승신' , '이기종' , '신광수'
			if($memberID == 'M02107'){
				$addSql = " ( GroupCode IN ($GroupList) or memberNo in ('M03211', 'M04602', 'M02202', 'M06505', 'M10103', 'M12203', 'M14101', 'M16222', 'M20101', 'M21410', 'M21471', 'M22009') ) ";
			} else{ $addSql = " GroupCode IN ($GroupList) ";}
			$WeekMemberList = array();
			$sql =	"
				select
					*
				from (
					/*연속된 연차 계산 최대2주치*/
					select
						D.MemberNo
						, (select korName from member_tbl b where D.MemberNo = b.MemberNo) as korName
						, D.GroupCode
						, 1 as state
						, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( D.temp_time, '_', 2 ), '_', -1 ), '%Y-%m-%d' ) as start_time
						, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( D.temp_time, '_', 3 ), '_', -1 ), '%Y-%m-%d' ) as end_time
						, D.note
					from
					(
						select
							C.MemberNo
							, C.GroupCode
							, C.note
							, SUBSTRING_INDEX (SUBSTRING_INDEX(C.dates,'/',numbers.n),'/',-1) temp_time
						from
						(select  1 n union  all  select 2
							union  all  select  3  union  all select 4
							union  all  select  5  union  all  select  6
							union  all  select  7  union  all  select  8
							union  all  select  9 union  all  select  10 union  all  select  11 union  all  select  12 union  all  select  13 union  all  select  14) numbers
						INNER  JOIN
						(
							select
								MemberNo
								, GroupCode
								, note
								, concat(
									concat('_', date_1)
									, if( date_diff_2 = 0 or date_diff_2 = 1, '', '/' )
									, if( date_diff_2 = 0 or ( date_diff_2 = 1 and date_diff_3 = 1 ), '', concat('_', date_2) )
									, if( date_diff_3 = 0 or date_diff_3 = 1, '', '/' )
									, if( date_diff_3 = 0 or ( date_diff_3 = 1 and date_diff_4 = 1 ), '', concat('_', date_3) )
									, if( date_diff_4 = 0 or date_diff_4 = 1, '', '/' )
									, if( date_diff_4 = 0 or ( date_diff_4 = 1 and date_diff_5 = 1 ), '', concat('_', date_4) )
									, if( date_diff_5 = 0 or date_diff_5 = 1, '', '/' )
									, if( date_diff_5 = 0 or ( date_diff_5 = 1 and date_diff_6 = 1 ), '', concat('_', date_5) )
									, if( date_diff_6 = 0 or date_diff_6 = 1, '', '/' )
									, if( date_diff_6 = 0 or ( date_diff_6 = 1 and date_diff_7 = 1 ), '', concat('_', date_6) )
									, if( date_diff_7 = 0 or date_diff_7 = 1, '', '/' )
									, if( date_diff_7 = 0 or ( date_diff_7 = 1 and date_diff_8 = 1 ), '', concat('_', date_7) )
									, if( date_diff_8 = 0 or date_diff_8 = 1, '', '/' )
									, if( date_diff_8 = 0 or ( date_diff_8 = 1 and date_diff_9 = 1 ), '', concat('_', date_8) )
									, if( date_diff_9 = 0 or date_diff_9 = 1, '', '/' )
									, if( date_diff_9 = 0 or ( date_diff_9 = 1 and date_diff_10 = 1 ), '', concat('_', date_9) )
									, if( date_diff_10 = 0 or date_diff_10 = 1, '', '/' )
									, if( date_diff_10 = 0 or ( date_diff_10 = 1 and date_diff_11 = 1 ), '', concat('_', date_10) )
									, if( date_diff_11 = 0 or date_diff_11 = 1, '', '/' )
									, if( date_diff_11 = 0 or ( date_diff_11 = 1 and date_diff_12 = 1 ), '', concat('_', date_11) )
									, if( date_diff_12 = 0 or date_diff_12 = 1, '', '/' )
									, if( date_diff_12 = 0 or ( date_diff_12 = 1 and date_diff_13 = 1 ), '', concat('_', date_12) )
									, if( date_diff_13 = 0 or date_diff_13 = 1, '', '/' )
									, if( date_diff_13 = 0 or ( date_diff_13 = 1 and date_diff_14 = 1 ), '', concat('_', date_13) )
									, if( date_diff_14 = 0 or date_diff_14 = 1, '', '/' )
									, if( date_diff_14 = 0, '', concat('_', date_14) )
								) as dates
							from
							(
								select
									MemberNo
									, GroupCode
									, note
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 1 ), ',', -1 ), '%Y-%m-%d' ) as date_1
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 1 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 2 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_2
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 2 ), ',', -1 ), '%Y-%m-%d' ) as date_2
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 2 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 3 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_3
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 3 ), ',', -1 ), '%Y-%m-%d' ) as date_3
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 3 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 4 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_4
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 4 ), ',', -1 ), '%Y-%m-%d' ) as date_4
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 4 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 5 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_5
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 5 ), ',', -1 ), '%Y-%m-%d' ) as date_5
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 5 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 6 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_6
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 6 ), ',', -1 ), '%Y-%m-%d' ) as date_6
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 6 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 7 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_7
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 7 ), ',', -1 ), '%Y-%m-%d' ) as date_7
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 7 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 8 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_8
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 8 ), ',', -1 ), '%Y-%m-%d' ) as date_8
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 8 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 9 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_9
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 9 ), ',', -1 ), '%Y-%m-%d' ) as date_9
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 9 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 10 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_10
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 10 ), ',', -1 ), '%Y-%m-%d' ) as date_10
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 10 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 11 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_11
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 11 ), ',', -1 ), '%Y-%m-%d' ) as date_11
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 11 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 12 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_12
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 12 ), ',', -1 ), '%Y-%m-%d' ) as date_12
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 12 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 13 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_13
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 13 ), ',', -1 ), '%Y-%m-%d' ) as date_13
									, TIMESTAMPDIFF( DAY, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 13 ), ',', -1 ), '%Y-%m-%d' ), DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 14 ), ',', -1 ), '%Y-%m-%d' ) ) as date_diff_14
									, DATE_FORMAT( SUBSTRING_INDEX( SUBSTRING_INDEX( va_date, ',', 14 ), ',', -1 ), '%Y-%m-%d' ) as date_14
								from
								(
									SELECT
										MemberNo
										, max(GroupCode) as GroupCode
										, max(note) as note
										, GROUP_CONCAT(start_time ORDER BY start_time ASC) as va_date
									FROM userstate_tbl
									WHERE
										state like '1'
										and start_time = end_time
										and start_time > '".date("Y-m-d")."'
										and /*GroupCode IN ($GroupList)*/
										$addSql
									group by
										MemberNo
								) A
							) B
						) C
						on CHAR_LENGTH ( C.dates ) - CHAR_LENGTH ( REPLACE ( C.dates ,  '/' ,  '' ))>= numbers . n-1
					) D
					union all
					select
						MemberNo
						, (select korName from member_tbl b where a.MemberNo = b.MemberNo) as korName
						, GroupCode
						, state
						, start_time
						, end_time
						, note
					from userstate_tbl a
					where
						/*GroupCode IN ($GroupList)*/
						$addSql
						and (
							( state like '1' and start_time != end_time )
							or ( state='7' or state='8' or state='30' or state='31' )
						)
						and start_time <= '".date("Y-m-d",strtotime("+7 day"))."'
						and end_time > '".date("Y-m-d")."'
						and note != '출산휴가(출산휴가)'
				) E, member_tbl F
				where
					E.MemberNo=F.MemberNo
					and E.start_time <= '".date("Y-m-d",strtotime("+7 day"))."'
					and E.end_time > '".date("Y-m-d")."'
					and F.LeaveDate IN ('0000-00-00')
				order by E.korName, E.start_time
				;
			";
			// echo $sql."<br>";
			$re = mysql_query($sql,$db);	//당일 일때
			while($re_row = mysql_fetch_array($re)){
				array_push($WeekMemberList,$re_row);
				//array_push($WeekMemberList,array( "MemberNo" => $re_row[MemberNo], "korName" => $re_row[korName], "vaction_type" => $re_row[vaction_type], "start_time" => $re_row[start_time], "end_time" => $re_row[end_time]));
			}

			$this->assign('WeekMemberList',$WeekMemberList);
		}else{	//부서원이면서 일주일이내 연차가 있을때
			$vacation_list = array();


			$sql =	"select
						MemberNo
						, (select korName from member_tbl b where a.MemberNo = b.MemberNo) as korName
						, note as vaction_type
						, start_time
						, end_time
					from userstate_tbl a
					where MemberNo like '$memberID'
						and (state like '1' or state='7' or state='8' or state='30'  or state='31')
						and ((start_time <= '".date("Y-m-d",strtotime("+1 day"))."' and end_time >= '".date("Y-m-d",strtotime("+1 day"))."')
							or (start_time <= '".date("Y-m-d",strtotime("+2 day"))."' and end_time >= '".date("Y-m-d",strtotime("+2 day"))."')
							or (start_time <= '".date("Y-m-d",strtotime("+3 day"))."' and end_time >= '".date("Y-m-d",strtotime("+3 day")) . "')
							or (start_time <= '" . date("Y-m-d", strtotime("+4 day")) . "' and end_time >= '" . date("Y-m-d", strtotime("+4 day")) . "')
							or (start_time <= '" . date("Y-m-d", strtotime("+5 day")) . "' and end_time >= '" . date("Y-m-d", strtotime("+5 day")) . "')
							or (start_time <= '" . date("Y-m-d", strtotime("+6 day")) . "' and end_time >= '" . date("Y-m-d", strtotime("+6 day")) . "')
						) order by start_time";

			//echo $sql."<br>";
			$re = mysql_query($sql,$db);	//당일 일때
			while($re_row = mysql_fetch_array($re)){
				array_push($vacation_list,array( "MemberNo" => $re_row[MemberNo], "korName" => $re_row[korName], "vaction_type" => $re_row[vaction_type], "start_time" => $re_row[start_time], "end_time" => $re_row[end_time]));
			}
			$this->assign('vacation_list',$vacation_list);
		}
	}//VacationList()

	/* ******************************************************************************************* */
	function LeftProcess()
	{
		global $memberID;
		global $CompanyKind;//회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)

		global $WorkPosition;
		$this->assign('WorkPosition',$WorkPosition);

		$PersonAuthority = new PersonAuthority();
		if($PersonAuthority->GetInfo($memberID,'임원')){
			$this->assign('auth_ceo',true);
		}else{
			$this->assign('auth_ceo',false);
		}

		if($PersonAuthority->GetInfo($memberID,'부서')){
			$this->assign('auth_depart',true);
		}else{
			$this->assign('auth_depart',false);
		}

		if($PersonAuthority->GetInfo($memberID,'업무')){
			$this->assign('auth_mng',true);
		}else{
			$this->assign('auth_mng',false);
		}

		if($PersonAuthority->GetInfo($memberID,'사업')){
			$this->assign('auth_business',true);
		}else{
			$this->assign('auth_business',false);
		}

		if($PersonAuthority->GetInfo($memberID,'협조')){
			$this->assign('auth_cooper',true);
		}else{
			$this->assign('auth_cooper',false);
		}

		if($PersonAuthority->GetInfo($memberID,'실행')){
			$this->assign('auth_Runbudget',true);
		}else{
			$this->assign('auth_Runbudget',false);
		}

		if($PersonAuthority->GetInfo($memberID,'팀장')){
			$this->assign('auth_team',true);
		}else{
			$this->assign('auth_team',false);
		}

		if($PersonAuthority->GetInfo($memberID,'조직')){
			$this->assign('auth_org',true);
		}else{
			$this->assign('auth_org',false);
		}

		if($PersonAuthority->GetInfo($memberID,'지적')){
			$this->assign('auth_patent',true);
		}else{
			$this->assign('auth_patent',false);
		}

		if($PersonAuthority->GetInfo($memberID,'SW관리')){
			$this->assign('auth_SWMng',true);
		}else{
			$this->assign('auth_SWMng',false);
		}
		/* Set MyClass Parameter */
		$this->SetMyClassParameter();
		/* ------------------------------------------------------------- */
		$this->myinfo();
		/* ------------------------------------------------------------- */
		global $connectFlag;	//웹브라우저를 통한 일반사용자의 접근(connectFlag=web)
		/* ------------------------------------------------------------- */
		$this->assign('connectFlag',$connectFlag);
		$this->assign('CompanyKind',$CompanyKind);

		$this->display("intranet/common_layout/left.tpl");
		/*
		if($memberID=="M20209")  //최은영과장 이정근 메뉴안보이게 처리
		{	$this->display("intranet/common_layout/left_hidden.tpl");
		}else{
			$this->display("intranet/common_layout/left.tpl");
		}
		*/
		/* ------------------------------------------------------------- */
	}

	/* ******************************************************************************************* */
	// 보안서약서 작성 확인
	function SecuPledgeCheck()
	{
		global $db;
		global $MemberNo;
		$year = date("Y");
		$sql = "select count(*) isCheck from secu_pledge_li_tbl where MemberNo = '$MemberNo' and  YEAR(ConsentDate) = $year";
		$result = mysql_query($sql, $db);

		$this -> assign("secuIsCheck" , mysql_result($result,0));
	}
	/* ******************************************************************************************* */
	function MainHomeProcess() //메인화면 TPL
	{
		global $memberID;

		global $WorkPosition;
		$this->assign('WorkPosition',$WorkPosition);

		$PersonAuthority = new PersonAuthority();
		if($PersonAuthority->GetInfo($memberID,'업무A')){
			$this->assign('auth_mng',true);
		}else{
			$this->assign('auth_mng',false);
		}
		if($PersonAuthority->GetInfo($memberID,'업무B')){
			$this->assign('auth_mng_admin',true);
		}else{
			$this->assign('auth_mng_admin',false);
		}
		if($PersonAuthority->GetInfo($memberID,'인사A')){
			$this->assign('auth_person',true);
		}else{
			$this->assign('auth_person',false);
		}
		if($PersonAuthority->GetInfo($memberID,'인사B')){
			$this->assign('auth_person_admin',true);
		}else{
			$this->assign('auth_person_admin',false);
		}
		if($PersonAuthority->GetInfo($memberID,'경리A')){
			$this->assign('auth_account',true);
		}else{
			$this->assign('auth_account',false);
		}
		if($PersonAuthority->GetInfo($memberID,'경리B')){
			$this->assign('auth_account_admin',true);
		}else{
			$this->assign('auth_account_admin',false);
		}
		if($PersonAuthority->GetInfo($memberID,'노무A')){
			$this->assign('auth_worker',true);
		}else{
			$this->assign('auth_worker',false);
		}
		if($PersonAuthority->GetInfo($memberID,'설정')){
			$this->assign('auth_setting',true);
		}else{
			$this->assign('auth_setting',false);
		}
		if($PersonAuthority->GetInfo($memberID,'공간정보')){
			$this->assign('auth_spatial',true);
		}else{
			$this->assign('auth_spatial',false);
		}
		if($PersonAuthority->GetInfo($memberID,'총괄')){
			$this->assign('auth_planning',true);
		}else{
			$this->assign('auth_planning',false);
		}

		if($PersonAuthority->GetInfo($memberID,'센터')){
			$this->assign('auth_center',true);
		}else{
			$this->assign('auth_center',false);
		}

		if($PersonAuthority->GetInfo($memberID,'장헌')){
			$this->assign('auth_jangheon',true);
		}else{
			$this->assign('auth_jangheon',false);
		}
		if($PersonAuthority->GetInfo($memberID,'PTC')){
			$this->assign('auth_ptc',true);
		}else{
			$this->assign('auth_ptc',false);
		}

		if($PersonAuthority->GetInfo($memberID,'EIS')){
			$this->assign('auth_eis',true);
		}else{
			$this->assign('auth_eis',false);
		}

		if($PersonAuthority->GetInfo($memberID,'단가')){
			$this->assign('auth_unitprice',true);
		}else{
			$this->assign('auth_unitprice',false);
		}


		if($PersonAuthority->GetInfo($memberID,'맨아워A')){
			$this->assign('auth_Manhour',true);
		}else{
			$this->assign('auth_Manhour',false);
		}

		if($PersonAuthority->GetInfo($memberID,'SW관리')){
			$this->assign('auth_SWMng',true);
		}else{
			$this->assign('auth_SWMng',false);
		}

		if($PersonAuthority->GetInfo($memberID,'설계')){
			$this->assign('auth_design',true);
		}else{
			$this->assign('auth_design',false);
		}


		/*---------------------------------------------------*/
		//웹브라우저를 통한 일반사용자의 접근(connectFlag=web)
		global $connectFlag;

		$this->assign('connectFlag',$connectFlag);
		/*---------------------------------------------------*/
		$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
		$this->assign('myip',$myip);
		/*----------------------------- ---------------------*/
		$this->myinfo();
		/*---------------------------------------------------*/
		$this->SelectWorkPlan();		//오늘의 업무계획
		$this->LoginInfo();				//최근업무내용
		$this->SelectDataList1();		//공지사항
		/*---------------------------------------------------*/
		$this->Calendar_Mini();			//주요일정_mini
		/*---------------------------------------------------*/
		$this->Weather();				//날씨
		$this->lunchMini();				//점심식단
		$this->BirthdayList();			//생일자표시
		/*---------------------------------------------------*/
		//$this->ApprovalCount();			//전자결재카운트
		$this->CarCount();				//현황카운트
		/*---------------------------------------------------*/
		$this->VacationList();		//부서장일때 연차자 리스트 목록
		/*---------------------------------------------------*/
        $this->SecuPledgeCheck(); //보안서약서 작성 확인
        /*---------------------------------------------------*/

		$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
		$this->assign('CompanyKind',$CompanyKind);

		$Member_Name   = MemberNo2Name($memberID);//사번으로 이름찾기
		$this->assign('Member_Name',$Member_Name);

		$Member_Position   = memberNoToPositionName($memberID);//사번으로 직급찾기
		$this->assign('Member_Position',$Member_Position);

		if(DevConfirm($memberID)){}else{}

		//=======================================================
		//회사코드 가져오기 2 20200511 for 공간정보
		if($memberID=="B14306" || $memberID=="Tadmin" || $memberID=="HM01836"){
		}else{}
		/* 회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM) */
		$CompanyType=Get_CompanyType();
		$this->assign('CompanyType',$CompanyType);
		//=======================================================


		//=================================================================================
		/*
		$set_from_date = "2018-10-28 23:59:00";
		if(SetNewCodeBoolean($set_from_date, "")){
			//echo 'new코드 적용함<br><br>';
			//-----------------------------------------------------------
			//$this->display("intranet/common_layout/main_home_new181029.tpl");
			$this->display("intranet/common_layout/main_home.tpl");
			//-----------------------------------------------------------
		}else{
			if(DevConfirm($memberID)){
				//echo 'new코드 적용안함<br><br>';
				//-----------------------------------------------------------
				//$this->display("intranet/common_layout/editWorkPop.tpl");
				//$this->display("intranet/common_layout/main_home_new181029.tpl");
				$this->display("intranet/common_layout/main_home.tpl");
				//-----------------------------------------------------------
			}else{
				//-----------------------------------------------------------
				$this->display("intranet/common_layout/main_home.tpl");
				//-----------------------------------------------------------
			}
		}
		*/

		if($memberID=="M20209")  //최은영과장 이정근 메뉴안보이게 처리
		{	$this->display("intranet/common_layout/main_home_hidden.tpl");
		}elseif($memberID=="M20330"){  //정명준
				$this->display("intranet/common_layout/main_home.tpl");
		}
        else{
			if($memberID=="M21420" || $memberID=="M21420QQ")  //최은영과장 이정근 메뉴안보이게 처리 || $memberID=="T03225" //$memberID=="B22001"
			{
				//$this->assign('testPageYN',"Y");

				//----------------------------------------------------
				//공간정보 서버 상태확인 동작
				global $CompanyKind;
				$RUN_SPATIAL_YN=FN_RUN_SPATIAL_YN($CompanyKind);
				$this->assign('RUN_SPATIAL_YN',$RUN_SPATIAL_YN);
				//----------------------------------------------------
				/*
				$this->display("intranet/common_layout/main_home.tpl");

				$this->assign('testPageYN',"Y");
				$this->display("intranet/common_layout/main_home_TEST.tpl");

				*/


				$this->assign('testPageYN',"Y");
				$this->assign("vers",date("YmdHis"));	//20230305-김한결 추가-kngil.js 캐시 용도
				$this->display("intranet/common_layout/main_home.tpl");
				//$this->display("intranet/common_layout/main_home_TEST.tpl");


			}else{
				//----------------------------------------------------
				//공간정보 서버 상태확인 동작
				global $CompanyKind;
				$RUN_SPATIAL_YN=FN_RUN_SPATIAL_YN($CompanyKind);
				$this->assign('RUN_SPATIAL_YN',$RUN_SPATIAL_YN);
				$this->assign("vers",date("YmdHis"));	//20230305-김한결 추가-kngil.js 캐시 용도
				//----------------------------------------------------
				
				$this->display("intranet/common_layout/main_home.tpl");
				
				//$this->display("intranet/common_layout/main_home_TEST.tpl");
			}

		}

		//=================================================================================

	}//Process End
	/* ******************************************************************************************* */



	function lunchMenuPop() //메인화면 TPL
	{
		global $memberID;

		global $WorkPosition;
		$this->assign('WorkPosition',$WorkPosition);


		$this->lunchMini();				//점심식단


		$this->display("intranet/common_layout/lunchMenuPop.tpl");
	}//MainHomeProcess22222 End

	//=============================================================================
	//기    능 : My Class 파라매터설정
	//관 련 DB :
	//프로시져 :
	//사용메뉴 : intranet/common_contents/work_lecture/MyClass.tpl : 교육실 > My Class
	//기    타 :
	//=============================================================================
	function SetMyClassParameter() {
		global $db;
		global $memberID;

		$sSql  = " Select  ";
		$sSql .= "     '$memberID' memberID, ";
		$sSql .= "     'HANM' Company_Kind, ";
		$sSql .= "     GroupCode dept_code,  ";
		$sSql .= "     (  ";
		$sSql .= "         Select  ";
		$sSql .= "             NAME  ";
		$sSql .= "         From  ";
		$sSql .= "             (  ";
		$sSql .= "                 Select  ";
		$sSql .= "                     *  ";
		$sSql .= "                 From  ";
		$sSql .= "                     systemconfig_tbl  ";
		$sSql .= "                 Where SysKey = 'GroupCode'   ";
		$sSql .= "             ) a  ";
		$sSql .= "         Where a.Code = x.GroupCode  ";
		$sSql .= "     ) dept, ";
		$sSql .= "     RankCode position_code, ";
		$sSql .= "     (  ";
		$sSql .= "         Select  ";
		$sSql .= "             NAME  ";
		$sSql .= "         From  ";
		$sSql .= "             (  ";
		$sSql .= "                 Select  ";
		$sSql .= "                     *  ";
		$sSql .= "                 From  ";
		$sSql .= "                     systemconfig_tbl  ";
		$sSql .= "                 Where SysKey = 'PositionCode'  ";
		$sSql .= "             ) a  ";
		$sSql .= "         Where a.Code = x.RankCode  ";
		$sSql .= "     ) position, ";
		$sSql .= "     korName insert_member,  ";
		$sSql .= "     Replace(JuminNo, '-', '') registration ";
		$sSql .= " From  ";
		$sSql .= "     member_tbl x  ";
		$sSql .= " Where MemberNo = '$memberID'  ";

		$resRtn = mysql_query($sSql);

		$aUserInfo = mysql_fetch_array($resRtn, MYSQL_ASSOC);

		$this->assign('memberID', $aUserInfo[memberID]);
		$this->assign('Company_Kind', $aUserInfo[Company_Kind]);
		$this->assign('dept_code', $aUserInfo[dept_code]);
		$this->assign('dept', $aUserInfo[dept]);
		$this->assign('position_code', $aUserInfo[position_code]);
		$this->assign('position', $aUserInfo[position]);
		$this->assign('insert_member', $aUserInfo[insert_member]);
		$this->assign('registration', $aUserInfo[registration]);
	}

}//class Main End/* ******************************************************************************************* */

// 끝
//==================================