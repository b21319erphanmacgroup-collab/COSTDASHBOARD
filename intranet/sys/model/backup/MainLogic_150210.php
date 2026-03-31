<?php
	/* ***********************************
	* 초기화면 로딩내용 관련
	* 2015-      : 
	* 2015-01-12 : 최근업무내용(LoginInfo())쿼리추가 : AND D.EntryTime <> DATE_FORMAT(D.EntryTime, '%Y-%m-%d 00:00:00')
	* 2015-01-09 : 업무상태 관련 소스 추가 : $Status=6
	* 2014-12-24 : '최근업무내용' 목록 : LoginInfo() : 프로젝트 코드 및 닉네임 셀렉트 관련 수정 작업 : SUK 
	* 2014-12-19 : '공지사항' 목록에 사용되는 function SelectDataList1() : 함수내 쿼리변경 : ORDER BY id DESC : SUK
	* 2014-12-18 : 세션값을 쿠키값으로 대체(/sys/inc/getCookieOfUser.php : 파일생성) : SUK
	* 2014-12-18 : php.날짜변수 관련 공통 페이지 삽입(/sys/inc/getNeedDate.php : 파일생성) : SUK
	* 2014-12-16 : 파일정리 : SUK 
	*************************************** */
	require('../SmartyConfig.php');	

	include "./sys/inc/getCookieOfUser.php";  //사용자에 관한 쿠키값
	include "./sys/inc/getNeedDate.php";      //로직에 사용되는 PHP시간&날짜 정의

	require('./sys/inc/function_intranet.php');	
	/* *********************************** */
	require_once($SmartyClassPath);
	/* *********************************** */
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

	}else if($_COOKIE['CK_memberID']!=""){				//쿠키값 유무확인
		/* SET COOKIE --------------------------------- */
		$MemberNo   =   $_COOKIE['CK_memberID'];	//사원번호     
		$memberID	=   $_COOKIE['CK_memberID'];	//사원번호     

		$CompanyKind=	$_COOKIE['CK_CompanyKind'];	//회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		$korName	=	$_COOKIE['CK_korName'];		//한글이름     
		$RankCode	=	$_COOKIE['CK_RankCode'];	//직급코드
		$GroupCode	=	$_COOKIE['CK_GroupCode'];	//부서코드
		$SortKey	=	$_COOKIE['CK_SortKey'];		//직급+부서코드
		$EntryDate	=	$_COOKIE['CK_EntryDate'];	//입사일자
		$position	=	$_COOKIE['CK_position'];	//직위명
		$GroupName	=	$_COOKIE['CK_GroupName'];	//부서명
		$ExtNo		=	$_COOKIE['CK_ExtNo'];		//내선번호
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
/*
$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
$myCompany   = searchCompanyKind();
if($myip=="192.168.2.62"){
	echo $myip."===".$myCompany."===<BR>";
}//if End
*/


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

	/* ******************************************************************************************* */
	/* 오늘의 업무계획 ////////////////////////////////////////////////////////////////////////////// */
	function SelectWorkPlan()
	{
		/* ----------------------------------- */
		global $db;
		global $memberID;
		/* ----------------------------------- */
		global $shortY;
		//$memberID='TADMIN';
		/* ----------------------------------- */
		$Status    = "1";
		$StartTime = date("H:i");
		$Today     = date("Y-m-d");
		/* ----------------------------------- */
		if($StartTime < "06:00") {           /// 6시 이전은 어제날짜로 처리
			$Today = find_lastday($Today);  
		}
		/* ----------------------------------- */
		$FiveDay= find_sevendays1($Today);   /// 오늘날짜에서 5일전 구하는곳
		/* ----------------------------------- */
		$sql=      "SELECT															";
		$sql= $sql."	 DATE_FORMAT(a.EntryTime, '%Y-%m-%d') as EntryTime			";	//업무시작 시간
		$sql= $sql."	,(cast((DATE_FORMAT(a.EntryTime,'%H:%i:%s')) as char))  as EntryTime_hms		";	//시분초 00:00:00  /*2015-01-09추가*/
		$sql= $sql."	,DATE_FORMAT(a.OverTime, '%Y-%m-%d')  as OverTime			";	//연장근무시작 시간
		$sql= $sql."	,DATE_FORMAT(a.LeaveTime, '%Y-%m-%d') as LeaveTime			";	//업무종료시작 시간
		$sql= $sql."	,a.EntryPCode												";	//프로젝트코드
		$sql= $sql."	,a.EntryJobCode												";	//프로젝트서브코드
		$sql= $sql."	,b.ProjectNickname as ProjectNickname						";	//프로젝트 닉네임
		$sql= $sql."	,a.EntryJob													";	//업무내용
		$sql= $sql." from															";
		$sql= $sql." (																";
		$sql= $sql."	select * from dallyproject_tbl								";
		$sql= $sql."	where														";
		$sql= $sql."	MemberNo = '".$memberID."'									";
		$sql= $sql."	and EntryTime > '".$FiveDay." 00:00:00'						";
		$sql= $sql."	and EntryTime < '".$Today." 23:59:59'						";
		$sql= $sql."	order by EntryTime Desc limit 1								";
		$sql= $sql." ) a															";
		$sql= $sql." left JOIN														";
		$sql= $sql." ( select * from Project_tbl )b									";
		$sql= $sql." on a.EntryPCode = replace(b.ProjectCode, 'XX','".$shortY."')	";
		/* ----------------------------------- */
		$result = mysql_query($sql,$db);
		$result_num = mysql_num_rows($result);
		/* ----------------------------------- */
		//echo $sql."<br>";

	if($result_num != 0) {
		/* ------------------------------------------------------------- */
		//WP = WorkPlan
		$EntryTime			= mysql_result($result,0,"EntryTime"); 			//업무시작 시간
		$OverTime			= mysql_result($result,0,"OverTime");			//연장근무시작 시간
		$LeaveTime			= mysql_result($result,0,"LeaveTime");			//업무종료시작 시간
		$code_EntryPCode	= mysql_result($result,0,"EntryPCode");			//프로젝트코드
		$EntryJobCode		= mysql_result($result,0,"EntryJobCode");		//프로젝트서브코드
		$ProjectNickname	= mysql_result($result,0,"ProjectNickname");	//프로젝트 닉네임
		$EntryJob			= mysql_result($result,0,"EntryJob");			//업무내용

		$EntryTime_hms = mysql_result($result,0,"EntryTime_hms"); //시분초 00:00:00  2015-01-09추가

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
		$this->assign('WP_code_EntryPCode',$code_EntryPCode);	//프로젝트코드       
		$this->assign('WP_EntryJobCode',$EntryJobCode);			//프로젝트서브코드   
		$this->assign('WP_ProjectNickname',$ProjectNickname);	//프로젝트 닉네임    
		$this->assign('WP_EntryJob',$EntryJob);					//업무내용           
		$this->assign('WP_Status',$Status);						//상태           
		$this->assign('WP_Status_detail',$Status_detail);		//상태상세           
		/* ------------------------------------------------------------- */
		$this->assign('WP_memberID',$memberID);					//사원번호          
		/* ------------------------------------------------------------- */
	}//SelectWorkPlan End
	/* ******************************************************************************************* */














	/* ******************************************************************************************* */
	/* 최근업무내용 ////////////////////////////////////////////////////////////////////////////// */
	function LoginInfo()
	{
		/* 최근업무내용 홈화면 리스트*/
		/* *********************
		** 사용 테이블 :  dallyproject_tbl, project_tbl
        ************************/
		global $db;
		global $memberID;
		/* ------------------------------------ */
		global $shortY;
		global $CompanyKind;		

		$overtimesql = "select * from overtime_basic_new_tbl order by code";
		$result_over = mysql_query($overtimesql,$db);
		/* ------------------------------------ */
		while($result_over_row = mysql_fetch_array($result_over)){
			if($result_over_row[code] =="0") //평일근무시간
			{
				$weekday_start = $result_over_row[start_time];
				$weekday_min = $result_over_row[min_time];
				$weekday_max = $result_over_row[max_time];
			}elseif($result_over_row[code] =="1"){ //휴일근무시간
				$holy_start = $result_over_row[start_time];
				$holy_min = $result_over_row[min_time];
				$holy_max = $result_over_row[max_time];
			}elseif($result_over_row[code] =="2"){ //부장월제한시간23
				$E1_max = $result_over_row[max_time];
			}elseif($result_over_row[code] =="3"){ //차장월제한시간34
				$E2_max = $result_over_row[max_time];
			}elseif($result_over_row[code] =="4") {//과장월제한시간40
				$E3_max = $result_over_row[max_time];
			}elseif($result_over_row[code] =="5"){ //대리사원월제한시간46
				$E4_max = $result_over_row[max_time];
			}//if End
			/* ------------------------------------ */
		}//while End
		/* ------------------------------------ */

				/* ----------------------------------------------------------------------------- */
				$query_data03 = array(); 
				/* ----------------------------------------------------------------------------- */
				$sql=	   "SELECT																	";
				$sql= $sql."	 D.MemberNo MemberNo												";	//사원번호
				$sql= $sql."	,D.EntryTime EntryTime												";	//업무시작 시간
				$sql= $sql."	,D.LeaveTime LeaveTime												";	//업무종료 시간
				$sql= $sql."	,D.OverTime OverTime												";	//연장근무시작 시간
				$sql= $sql."	,DATE_FORMAT(D.EntryTime, '%Y-%m-%d') ViewDate						";	//업무시작일자 : YYYY-MM-DD
				$sql= $sql."	,DATE_FORMAT(D.EntryTime,'%H:%i') EntryMin							";	//업무시작 시간
				$sql= $sql."	,DATE_FORMAT(D.LeaveTime,'%H:%i') LeaveMin							";	//업무종료 시간
				$sql= $sql."	,DATE_FORMAT(D.OverTime,'%H:%i') OverMin							";	//연장근무시작 시간
				$sql= $sql."	,DAYNAME(DATE_FORMAT(D.EntryTime, '%Y-%m-%d')) DN					";  //요일(English)
				$sql= $sql."	,D.EntryPCode EntryPCode											";	//프로젝트코드
				$sql= $sql."	,D.EntryJobCode EntryJobCode										";	//프로젝트서브코드
				$sql= $sql."	,D.EntryJob EntryJob												";	//업무내용
				$sql= $sql."	,D.EntryJob EJ_FULL													";	//업무내용(풀네임)
				$sql= $sql."	,D.modify modify													";	//O/T 승인여부
				$sql= $sql."	,D.Note Note														";
				$sql= $sql."	,substring(D.SortKey,4,2) RankCode									";	//직급코드
				$sql= $sql." FROM dallyproject_tbl D												";
				$sql= $sql." WHERE																	";
				$sql= $sql." D.MemberNo = '".$memberID."'											";

				$sql= $sql." AND																	";
				$sql= $sql." D.EntryTime <> DATE_FORMAT(D.EntryTime, '%Y-%m-%d 00:00:00')			";

				$sql= $sql." ORDER BY D.EntryTime DESC												";
				$sql= $sql." LIMIT 0,6																";
				/* ----------------------------------------------------------------------------- */
				$re = mysql_query($sql,$db);
				$re_num = mysql_num_rows($re);

				if($re_num > 0) { //조회결과가 존재하면
					while($re_row = mysql_fetch_array($re)) {
						/* ------------------------------------ */
						$ViewDate		=	$re_row[ViewDate];				//업무시작일자 : YYYY-MM-DD
						$ViewDate_day   =   substr($re_row[ViewDate],8,2);	//업무시작일자 : DD
						$re_row[ViewDate_day] = $ViewDate_day;

						/* ------------------------------------ */
						$RankCode		=	$re_row[RankCode];				//직급코드
						/* ------------------------------------ */
						$EntryTime		=	$re_row[EntryMin];				//업무시작 시간 '%H:%i
						$LeaveTime		=	$re_row[LeaveMin];				//업무종료 시간 '%H:%i
						$OverTime		=	$re_row[OverMin];				//연장근무시작 시간 '%H:%i
						/* ------------------------------------ */
						$EntryPCode		=	$re_row[EntryPCode];			//프로젝트코드
						/* ------------------------------------ */
						$EntryJobCode	=	$re_row[EntryJobCode];			//프로젝트서브코드
						/* ------------------------------------ */
						$EntryJob		=	$re_row[EntryJob];				//업무내용
						$re_row[EntryJob_short]     = utf8_strcut($EntryJob,18,'..');
						/* ------------------------------------ */

						/* ------------------------------------ */
						switch ($re_row[DN]) {
							case "Sunday":
							 $re_row[DN]="<font color='pink'>일</font>";
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
							 $re_row[DN]="<font color='pink'>토</font>";
							  break;
							default:
							  echo "";
						} //switch End
						/* ------------------------------------ */

						$holyCheck = holy($ViewDate); //평일:weekday : holy() : /inc/function_intranet.php파일내 FUNCTION
						if($holyCheck == "weekday"){
							//평일

						}else{//휴일&공휴일
							$DN_Color="<font color='hotpink'>".$re_row[DN]."</font>";
							$re_row[DN] = $DN_Color;

						}//if End
						/* 출근시간 컬러표시 End---------------------------------------------------- */


						/* ------------------------------------ */
						$Note			=	$re_row[Note];
						$modify			=	$re_row[modify];
						//$ProjectNickname=	$re_row[ProjectNickname];	//프로젝트 닉네임
						/* ------------------------------------ */
						$overdate		=	substr($re_row[OverTime],5,2)."-".substr($re_row[OverTime],8,2);

						$leavedate		=	substr($re_row[LeaveTime],5,2)."-".substr($re_row[LeaveTime],8,2);
						/* ------------------------------------ */
						$ProjectNickname = "";
						if(change_XXIS($EntryPCode)){ //리턴값이 true  : XX관련 코드 
							$ProjectCode2 = change_XX($EntryPCode); //XX-AA-BB 코드로 변환하여 project_tbl에서 조회가능하도록 한다.
							$sql_code01="SELECT * FROM project_tbl WHERE ProjectCode ='".$ProjectCode2."' ";
							$re_code01 = mysql_query($sql_code01,$db);
							$re_num_code01 = mysql_num_rows($re_code01);
							if($re_num_code01 != 0) {
								$ProjectNickname = mysql_result($re_code01,0,"ProjectNickname");
								$re_row[ProjectNickname] = $ProjectNickname;
								$re_row[ProjectNickname_short]  = utf8_strcut($ProjectNickname,8,'..');
							}else{	
								$re_row[ProjectNickname] = "&nbsp;";
								$re_row[ProjectNickname_short] = "&nbsp;";
							}//if End

						}else{ //리턴값이 false : 일반프로젝트 코드
							$sql_code02="SELECT * FROM project_tbl WHERE ProjectCode ='".$EntryPCode."' ";
							
							$re_code02 = mysql_query($sql_code02,$db);
							$re_num_code02 = mysql_num_rows($re_code02);
							if($re_num_code02 != 0) {
								$ProjectNickname = mysql_result($re_code02,0,"ProjectNickname");
								$re_row[ProjectNickname] = $ProjectNickname;
								$re_row[ProjectNickname_short]  = utf8_strcut($ProjectNickname,8,'..');

							}else{	
								$re_row[ProjectNickname] = "&nbsp;";
								$re_row[ProjectNickname_short] = "&nbsp;";
							}//if End
						
						}//if End

						$EntryTime =	$re_row[EntryMin];

						/* 출근시간 컬러표시 Start : 08시50분~09시: 주황색, 9시넘으면 적색 ------------*/
						$TimeCheck = $re_row[EntryMin];
						$TCheck = (int)str_replace(":","",$TimeCheck);

						$holyCheck = holy($ViewDate); //평일:weekday : holy() : /inc/function_intranet.php파일내 FUNCTION
						if($holyCheck == "weekday"){
							if($TCheck >= 850 && $TCheck <= 900){
								$re_row[EntryMin_color]="<font color='orange'>".$TimeCheck."</font>";

							}else if($TCheck > 900){
								$re_row[EntryMin_color]="<font color='red'><b>".$TimeCheck."</b></font>";

							}else{
								$re_row[EntryMin_color] = $TimeCheck;
							}//if End

						}else{//휴일&공휴일
							$re_row[EntryMin_color] = $TimeCheck;

						}//if End
						/* 출근시간 컬러표시 End---------------------------------------------------- */


						/* OT표시관련 Start ******************* */ 
						$holy_sc = holy($ViewDate);
						$nowork="no";
						/* ------------------------------------ */
						$OTTime="";
						$OTState="";
							/* ------------------------------------ */
							if($holy_sc == "weekday"){// 평일 일 때  OverTime 야근시작시간  LeaveTime퇴근시간
								//야근시작시간이 있을때
								if($OverTime != "00:00"){	
									//O/T시간계산
									//야근시작시간이 19:00 이전이면 19:00 부터야근시작시간(계산)
									if($OverTime < $weekday_start){ 
										$OverTime2=$weekday_start;
									}//if End			
									/* ------------------------------------ */
									$LeaveSec = strtotime($LeaveTime); 
									$OverSec  = strtotime($OverTime2);
									$OTTime   = sec_time00($LeaveSec - $OverSec); 
									/* ------------------------------------ */
									if ($overdate != "00-00"){
										if ($leavedate > $overdate){ //다음날새벽에 끝나는 경우야근처리
											$OTTime=$weekday_max;
											$OTState="○";
										}else{	
											//최소근무시간 2시간이상이면 야근표시
											if($OTTime >= $weekday_min){	//최대근무시간 3시간을 초과하면 최대 3시간으로 표시
												if($OTTime > $weekday_max) $OTTime=$weekday_max;
												$OTState="○";	
											}else{ //최소근무시간 2시간을 미만이면 시간표시하지 않음
												$OTTime="&nbsp;";						
												$OTState="&nbsp;";				
											}//if End
											/* ------------------------------------ */
											if($Today == $ViewDate){
												$OTState="中";
											}//if End
											/* ------------------------------------ */
										}//if End
									}else{ //($overdate <> "")
										$OTTime="&nbsp;";						
										$OTState="&nbsp;";
									}//if End	
								}
							}else if($holy_sc == "holyday"){ ////휴일 일 때  EntryTime 야근시작시간  LeaveTime퇴근시간
								//출근시간이 있을때
								if($EntryTime != "00:00"){	
									// 연장근무신청서만 올리고 근무안함
									if ($EntryTime =="00:00" && $LeaveTime =="18:18"){
										$nowork="yes";
									}//if End
									/* ------------------------------------ */
									//출근시작시간이 09:00 이전이면 09:00 부터야근시작시간
									if($EntryTime < $holy_start){
										$EntryTime=$holy_start;	
									}//if End
									/* ------------------------------------ */
									$LeaveSec = strtotime($LeaveTime); 
									$EntrySec = strtotime($EntryTime);
									$OTTime = sec_time00($LeaveSec - $EntrySec); 
									/* ------------------------------------ */
									if ($overdate == "00-00") { //휴일근무에는 연장근무시작을 안누르는 경우있음
										$overdate = substr($re_row[EntryTime],5,2)."-".substr($re_row[EntryTime],8,2);
									}//if End
									/* ------------------------------------ */
									if ($leavedate > $overdate) {//다음날새벽에 끝나는 경우야근처리
										$OTTime=$holy_max;
										$OTState="○";
									}else{
										//휴일최소근무시간 3시간이상이면 야근표시
										if($OTTime >= $holy_min){//휴일최대근무시간 5시간을 초과하면 최대 5시간으로 표시
											if($OTTime > $holy_max ) $OTTime=$holy_max ;
											$OTState="○";				
										}else {///휴일최소근무시간 3시간을 미만이면 시간표시하지 않음
											$OTTime="&nbsp;";						
											$OTState="&nbsp;";				
										}//if End
										/* ------------------------------------ */
										if($Today == $ViewDate){
											$OTState="中";
										}//if End
										/* ------------------------------------ */
									}//if End
								}//if End
							}//if End

							/* 코드사용분기 Start  *************** */
							if( $CompanyKind=="PILE" || $CompanyKind=="HANM" ){//파일테크(PILE),바론컨설턴트(HANM)
								if($modify=="1"){
									$OTState="○";
								}else{
									$OTState="";
								}//if End
							}else if($CompanyKind=="JANG"){//장헌산업(JANG)
								/* */
							}//if End
							/* 코드사용분기 End  *************** */

							if($nowork =="yes"){
								$OTTime="";
							}
							/* ------------------------------------ */
							//$re_row[OTTime]=$OTTime;	
							$re_row[OTState]=$OTState;	
						/* OT표시관련 End ******************* */ 

							array_push($query_data03,$re_row);
							/* ------------------------------------ */
					}//while End
					/* ---------------------------- */
					$dataExist = "YES";//데이터유무  
					/* ---------------------------- */
				}else{  //조회결과가 없으면
					/* ---------------------------- */
					$dataExist = "NO";//데이터유무  
					/* ---------------------------- */
				}//if End

					$this->assign('sql',$sql);
					$this->assign('dataExist',"$dataExist");	//데이터유무  
					$this->assign('query_data03',$query_data03);
					/* ---------------------------- */

	}//LoginInfo() End
	/* -최근업무내용 End--------------------------------------------------------------------------- */
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
		$sql=$sql."	group_code in('".$GroupCode."','".$all_groupcode."')			";
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
			$re_row[title]=utf8_strcut($re_row[title],35,'...');
			/* ---------------------------- */
			if(substr($re_row[wdate],0,10)==$today){
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
		$sql=$sql."	group_code in('".$GroupCode."','".$all_groupcode."')						";
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
				$ItemData=array("id" =>$pop_row[id],"name"=>$pop_row[level],"top"=>$topv,"left"=>$leftv);
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
					$ItemData=array("id" =>$pop_row[id],"name"=>$pop_row[level],"top"=>$topv,"left"=>$leftv);
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
		/* 팝업 체크 부분 End ---------------------------------------------------------------- */
		$this->assign('query_data2',$query_data2);
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
		/* ----------------- */
		global  $db;
		/* ----------------- */
		$todaySplit = split("-",$date_today);  
		/* ------------------------- */
		$firstDay = $todaySplit[0]."-".$todaySplit[1]."-01";
		/* ------------------------- */
		$day_now    = $todaySplit[2];
		$day_after1 = $todaySplit[2]+1;
		$day_after2 = $todaySplit[2]+2;
		//$firstDay = "2014-11-01";
		//$today = "2014-10-01";
		//$dayitem[0]:년도, $dayitem[1]:월, $dayitem[2]:일
		$dayitem = split("-",$firstDay);   
		/* ------------------------- */
		//strtotime=> str값을 정상적인 날짜로 변환, strftime=>strftime("%w",time());요일반환(일:0~토:6)
		$week= strftime("%w", strtotime($firstDay)) ;  
		/* ------------------------- */
		//해당년도 월의 마지막날 날짜
		$end_day = date("t", mktime(0, 0, 0, $dayitem[1], 1, $dayitem[0])); 
		/* ------------------------- */
		//해당월: 년-월-1일 
		$startDate = $dayitem[0]."-".$dayitem[1]."-01";
		/* ------------------------- */
		//해당월: 년-월-마지막날짜 
		$endDate   = $dayitem[0]."-".$dayitem[1]."-".$end_day;
		/* 해당 월의 달력 생성 Start **************************************************** */
		$DayList    = array(); 
		$displayday = 1;
		/* ---------------------------- */
		/* 달력의 행수를 결정
		*(매월1일이 토요일이면 6줄이고 나머지는 5줄.
		* ---------------------------------------- */
		$row_count;
		/* ---------------------------- */
		if($week==6){
			$row_count=7*6;
		}else{
			$row_count=7*5;
		}//if End
		/* ---------------------------- */
		for($index=0;$index < $row_count;$index++){
			if($index < $week ){
				$ItemData=array("day" =>'',"flagUse1"=>"","flagUse2"=>"","s_contents1"=>"","s_contents"=>"","s_contentsShort1"=>"","s_contentsShort"=>"");
				array_push($DayList,$ItemData);

			}else if($displayday <= $end_day){
				$ItemData=array( "day"=>$displayday,"flagUse1"=>"","flagUse2"=>"","s_contents1"=>"","s_contents"=>"","s_contentsShort1"=>"","s_contentsShort"=>"");
				array_push($DayList,$ItemData);
				$displayday=$displayday+1;

			}else{
				$ItemData=array("day" =>'',"flagUse1"=>"","flagUse2"=>"","s_contents1"=>"","s_contents"=>"","s_contentsShort1"=>"","s_contentsShort"=>"");
				array_push($DayList,$ItemData);

			}//if End
			/* ---------------------------- */
		}//for End
		/* ---------------------------- */
		/* 해당 월의 달력 생성 End **************************************************** */

		// 사내 일정 Start *********************************************************** */
		// 주요일정 : 오늘 내일 모레 주요일정
		$sql=      "SELECT								";
		$sql= $sql."  S1.groupcode  as s_groupcode1   	";	// 부서_그룹코드
		$sql= $sql." ,S1.pdate	    as s_pdate1         ";	// 부서_스케줄 시작일자
		$sql= $sql." ,S1.subcode    as s_subcode1		";	// 부서_s_subcode =>  1:일정, 2:업무, 3:기념일, 4:프로젝트 =>구분이름
		$sql= $sql." ,S1.contents   as s_contents1     	";	// 부서_내용
		$sql= $sql." ,S1.contents   as s_contentsShort1	";	// 부서_내용_줄임
		$sql= $sql." ,S1.cdate      as s_cdate1			";	// 부서_스케줄 종료일자
		$sql= $sql." ,S1.updateuser as s_updateuser1 	";	// 부서_등록자 사원번호
		$sql= $sql." FROM								";
		$sql= $sql."     schedule_job_tbl S1			";
		$sql= $sql." WHERE								";
		$sql= $sql."	'".$startDate."'<= S1.pdate		";
		$sql= $sql."	AND								";
		$sql= $sql."	S1.pdate <='".$endDate."'		";
		$sql= $sql."	AND								";
		$sql= $sql."	S1.groupcode in('99')			";	// 99:사내공지만
		$sql= $sql." ORDER BY S1.pdate Desc				";
		/*
		'GroupCode', '01', '임원'			
		'GroupCode', '02', '경영지원부'		
		'GroupCode', '04', '공사관리팀'		
		'GroupCode', '06', '생산본부'		
		'GroupCode', '07', '현장(작업반)'	
		'GroupCode', '08', '설계팀'			
		'GroupCode', '09', '영업팀'			
		'GroupCode', '10', '현장소장'		
		*/
		/* ----------------------------------------------------- */
		$re = mysql_query($sql,$db);
		/* ----------------------------------------------------- */
		while($re_row = mysql_fetch_array($re)) {
			/* ----------------------------------------------------- */
			$dayitem=split("-",$re_row[s_pdate1]);
			/* ----------------------------------------------------- */
			for($index=0;$index < $row_count;$index++){
				if($DayList[$index][day] ==  $dayitem[2]){
						//$DayList[$index][flagUse1]="사내";
						$DayList[$index][flagUse1]="C"; //C:Company
						//$DayList[$index][flagUse1]=$re_row[s_contents1];
						/* ------------------------------------------------------------------------------ */
						$s_contentsShort1_len = mb_strlen($re_row[s_contentsShort1],"UTF-8");
						if($s_contentsShort1_len>7){
							$re_row[s_contentsShort1] = mb_substr($re_row[s_contentsShort1],0,7,"UTF-8")."..";
						}
						/* ------------------------------------------------------------------------------ */
						$DayList[$index][s_contentsShort1]=$re_row[s_contentsShort1];
						$DayList[$index][s_contents1]=$re_row[s_contents1];
				}//if End
			}//for End
		} //while End
		// 사내 일정 End *********************************************************** */

		// 부서 일정 Start *********************************************************** */
		$sql2=       "SELECT								";
		$sql2= $sql2."  S.groupcode  as s_groupcode			";	// 부서_그룹코드
		$sql2= $sql2." ,S.pdate	     as s_pdate				";	// 부서_스케줄 시작일자
		$sql2= $sql2." ,S.subcode    as s_subcode			";	// 부서_s_subcode =>  1:일정, 2:업무, 3:기념일, 4:프로젝트 =>구분이름
		$sql2= $sql2." ,S.contents   as s_contents			";	// 부서_내용
		$sql2= $sql2." ,S.contents   as s_contentsShort		";	// 부서_내용_줄임
		$sql2= $sql2." ,S.cdate      as s_cdate				";	// 부서_스케줄 종료일자
		$sql2= $sql2." ,S.updateuser as s_updateuser		";	// 부서_등록자 사원번호
		$sql2= $sql2." FROM									";
		$sql2= $sql2."     schedule_job_tbl S				";
		$sql2= $sql2." WHERE								";
		$sql2= $sql2."	'".$startDate."'<= S.pdate			";
		$sql2= $sql2."	AND									";
		$sql2= $sql2."	S.pdate <='".$endDate."'			";
		$sql2= $sql2."	AND									";
		$sql2= $sql2."	S.groupcode = '".(int)$GroupCode."'	";
		$sql2= $sql2." ORDER BY S.pdate Desc				";
		/*-------------------------------------------*/
		// 부서일정
		//$azsql="select * from my_schedule_tbl where MemberNo = '$MemberNo' and  '$startDate'<= pdate and pdate <='$endDate' ";
		$re2 = mysql_query($sql2,$db);
		/*-------------------------------------------*/
		while($re_row2 = mysql_fetch_array($re2)){
			$dayitem=split("-",$re_row2[s_pdate]);
			/*-------------------------------------------*/
			for($index=0;$index < $row_count;$index++){
				if($DayList[$index][day] ==  $dayitem[2]){
					//$DayList[$index][flagUse2]="부서";
					$DayList[$index][flagUse2]="D";
					/* ------------------------------------------------------------------------------ */
					$s_contentsShort_len = mb_strlen($re_row2[s_contentsShort],"UTF-8");
					/*-------------------------------------------*/
					if($s_contentsShort_len>7){
						$re_row2[s_contentsShort] = mb_substr($re_row2[s_contentsShort],0,7,"UTF-8")."..";
					}//if End
					/* ------------------------------------------------------------------------------ */
					$DayList[$index][s_contentsShort]=$re_row2[s_contentsShort];
					$DayList[$index][s_contents]=$re_row2[s_contents];
				}//if End
			}//for End
		}//while End
		// 부서 일정 End *********************************************************** */

		for($Row=0;$Row<6;$Row++){
			for($Col=0;$Col<7;$Col++){
				$index=$Row*7+$Col;
			}//for End
		}//for End
		/*-------------------------------------------*/
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
		/* 장헌인트라넷*/
		$db_hostname01 ='192.168.2.250';
		$db_database01 ='hanmacerp';
		$db_username01 ='root';
		$db_password01 ='';
		/*-----------------------------------------------------------------------*/
		$db01	= mysql_connect($db_hostname01,$db_username01,$db_password01);
			if(!$db01) die ("Unable to connect to MySql : ".mysql_error()); 
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
			$len02 = mb_strlen($lunch_menu_main,"UTF-8");
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
		/* ----------------------------- */
		$this->assign('lunch_menu_num',$lunch_menu_num);					// 메인메뉴
		$this->assign('lunch_menu_main',$lunch_menu_main);					// 메인메뉴
		$this->assign('lunch_menu_sub',$lunch_menu_sub);					// 서브메뉴
		$this->assign('lunch_menu_main_short',$lunch_menu_main_short);		// 메인메뉴(35자)
		$this->assign('lunch_menu_sub_short',$lunch_menu_sub_short);		// 서브메뉴(35자)
		/* ----------------------------- */
	} //lunchMini End
	/* 점심식단 End----------------------------------------------------------------------- */
	/* ******************************************************************************************* */

	/* 생일자 표시------------------------------------------------------------------------------ */
	function BirthdayList()
	{
		/* ----------------------------- */
		global $db;
		global $memberID;
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
			$start_week = date("md",$week_first);
			$end_week   = date("md",$week_last);
			
			$sql="SELECT a1.korName as Name,a2.Name as PositionName,JuminNo,substring(JuminNo,5,2) birthday  FROM ";
			$sql = $sql."(                                                                ";
			$sql = $sql."		SELECT * FROM member_tbl WHERE WorkPosition ='1' AND substring(JuminNo,3,4) between '".$start_week."' AND '".$end_week."'  ";
			$sql = $sql."	)a1 left JOIN                                                    ";
			$sql = $sql."	(                                                                ";
			$sql = $sql."		SELECT * FROM systemconfig_tbl WHERE SysKey='PositionCode'   ";
			$sql = $sql.")a2 on a1.RankCode = a2.code order by substring(JuminNo,5,2)" ;    	

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
		//echo $sql."<br>";
		$re = mysql_query($sql,$db);
		while($re_row = mysql_fetch_array($re)){
			
			//$birthday=substr($re_row[JuminNo],2,2)."/".substr($re_row[JuminNo],4,2);
			$birth=	$birth."[".$re_row[Name]." ".$re_row[PositionName]."님 ".$re_row[birthday]."일] ";		
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
		/* ----------------------------- */
	
	} //BirthdayList() End
	/* ******************************************************************************************* */

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
		$korName = $_COOKIE['CK_korName'];
		$this->assign('korName',$korName);
		/* ----------------------------- */
		$this->assign('MemberPic',$MemberPic);
		/* ----------------------------- */
		$this->assign('query_data03',$query_data);
		/* ----------------------------- */
	}//SelectDataList2 End
	/* ******************************************************************************************* */

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
		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		/* ----------------------------- */
		if($re_num != 0){	
			$Name = mysql_result($re,0,"Name");
			$GroupName = mysql_result($re,0,"GroupName");
			$Position = mysql_result($re,0,"Position");
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
		/* ----------------------------- */
		$this->assign('memberID',$memberID);
		$this->assign('Name',$Name);
		$this->assign('korName',$Name);
		$this->assign('GroupName',$GroupName);
		$this->assign('Position',$Position);
		$this->assign('MemberPic',$MemberPic);
		/* ----------------------------- */
		$this->assign('RankCode',$RankCode);   //직급코드      
		$this->assign('GroupCode',$GroupCode); //부서코드      
		$this->assign('SortKey',$SortKey);	   //직급+부서코드 
		$this->assign('EntryDate',$EntryDate); //입사일자
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
		/* ----------------------------- */
		//처리부서 담당자 체크
		$sql="select * from approval_tbl where ReceiveMember='$memberID'  ";
		$re = mysql_query($sql,$db);
		$re_row = mysql_num_rows($re);//총 개수 저장
		/* ----------------------------- */
		while($re_row = mysql_fetch_array($re)){
			$FormList=$FormList."'".$re_row[FormName]."',";
		}//while End
		/* ----------------------------- */
		$FormList=substr($FormList,0,strlen($FormList)-1);
		/* ----------------------------- */
		$MyGroupCode=$_SESSION['MyGroupCode'] ;

		if($FormList == "") {  //일반사용자
			$sql = "select * from SanctionDoc_tbl where RT_SanctionState like '%".$SANCTION_CODE.":".$memberID."%' or  RT_SanctionState like '%".$SANCTION_CODE2.":".$memberID."%'";
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
			$sql = "select * from SanctionDoc_tbl where (RT_SanctionState like '%".$SANCTION_CODE.":".$memberID."%' or RT_SanctionState like '%".$SANCTION_CODE2.":".$memberID."%') or (PG_Code='".$MyGroupCode."' and RT_SanctionState like '%".$PROCESS_RECEIVE."%' and FormNum in ($FormList))";
//echo "처리부서담당자".$sql."<br>";
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
//echo "처리부서담당자".$sql3."<br>";
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
		/* ----------------------------- */
	}
	/* 전자결재 카운트 끝------------------------------------------------------------------------------ */
	/* ******************************************************************************************* */

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
		/* ----------------------------- */

	}
	/* ******************************************************************************************* */

	/* ******************************************************************************************* */
	/* 날씨(서울,당진)------------------------------------------------------------------------------ */
	function Weather()
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
				{	$iconimg="weather_icon01.png";
				}else if($icon =="02") //구름조금
				{	$iconimg="weather_icon02.png";
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

				if($icon2 =="1") //맑음
				{	$iconimg2="weather_icon01.png";
				}else if($icon2 =="2") //구름조금
				{	$iconimg2="weather_icon02.png";
				}else if($icon2 =="3") //구름많음
				{	$iconimg2="weather_icon03.png";
				}else if($icon2 =="4") //흐림
				{	$iconimg2="weather_icon04.png";
				}else if($icon2 =="5" || $icon2 =="8" ) //비
				{	$iconimg2="weather_icon05.png";
				}else if($icon2 =="6") //눈/비
				{	$iconimg2="weather_icon06.png";
				}else if($icon2 =="7") //눈
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

	/* ******************************************************************************************* */
	function LeftProcess()
	{
		global $memberID;
		global $CompanyKind;//회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)

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
		
		/* ------------------------------------------------------------- */
		$this->myinfo();
		/* ------------------------------------------------------------- */
		global $connectFlag;	//웹브라우저를 통한 일반사용자의 접근(connectFlag=web)
		/* ------------------------------------------------------------- */
		$this->assign('connectFlag',$connectFlag);
		$this->assign('CompanyKind',$CompanyKind);

		$this->display("intranet/common_layout/left.tpl");
		/* ------------------------------------------------------------- */
	}
	/* ******************************************************************************************* */

	/* ******************************************************************************************* */
	function MainHomeProcess() //메인화면 TPL
	{
		global $memberID;
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
		$this->ApprovalCount();			//전자결재카운트
		$this->CarCount();				//현황카운트
		/*---------------------------------------------------*/
		$this->display("intranet/common_layout/main_home.tpl");
	}//Process End
	/* ******************************************************************************************* */

/* ******************************************************************************************* */
}//class Main End
/* ******************************************************************************************* */
?>

