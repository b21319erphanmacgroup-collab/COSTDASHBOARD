<?php
	/* **********************************
	* 공통사용 :
	* 외출/출장,업무추가/수정/종료
	* ,점심식단,개인정보비밀번호확인 등
	* ------------------------------------
	* 2025-12-04 : EndWork_new(업무종료) 시 기존 LeaveTime외 Leave 컬럼에 넣던 데이터를 Entry 컬럼으로 변경 (KeyWord : Entry/Leave변경사항)
	* 2025-10-24 : 업무검토 관련 저장 컬럼 및 변수 추가 - 김한결(KeyWord : 업무검토)
	* 2015-03-   :
	* 2015-03-10 : 경유/휴가 관련 코드추가(DaySearch(), GoEtcPop() , EtcCRUD()) :  SUK
	* 2014-12-18 : 세션값을 쿠키값으로 대체(/sys/inc/getCookieOfUser.php : 파일생성) : SUK
	* 2014-12-18 : php.날짜변수 관련 공통 페이지 삽입(/sys/inc/getNeedDate.php : 파일생성) : SUK
	* 2014-12-16 : 파일정리: SUK
	*************************************** */
	require('../../../SmartyConfig.php');
	/* ----------------------------------- */
	include "../inc/getNeedDate.php";      //로직에 사용되는 PHP시간&날짜 정의
	/* php function------------------------*/
	include "../inc/function_intranet.php";
	/* ----------------------------------- */
	require_once($SmartyClassPath);
	/* ----------------------------------- */
?>
<?php
	extract($_GET);
		$MemberNo	=	"";	//사원번호
	if($_SESSION['SS_memberID']!=""){
		/* SET SESSION ----------------------- */
		$MemberNo   =   $_SESSION['SS_memberID'];		//사원번호
		$memberID	=   $_SESSION['SS_memberID'];		//사원번호

		$get_memberID	= $_SESSION['SS_memberID'];

//echo $MemberNo.":01<br>";
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
		/* SET COOKIE --------------------------------- */
		$MemberNo   =   $_SESSION['CK_memberID'];	//사원번호
		$memberID	=   $_SESSION['CK_memberID'];	//사원번호
		$get_memberID	= $_SESSION['CK_memberID'];
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
		$memberID	=  ($memberID==""?$_POST['memberID']:$memberID);
		$memberID	=  ($memberID==""?$_POST['ajax_memberID']:$memberID);

		$get_memberID	= $memberID;

		$MemberNo	=	$memberID;
		$MemberNo	=	strtoupper($MemberNo);
		/* ----------------------------------- */
		require('../popup/setInfo.php');
		/* ----------------------------------- */
	}//if End
	/* ----------------------------------- */
	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
	/*---------------------------------------------*/
	$whereQuery99="MemberNo='".$MemberNo."'";
	$re_RankCode= tableToColumn2("member_tbl","RankCode",$whereQuery99);
	$RankCode = ($RankCode==""?$re_RankCode:$RankCode);//직급코드
	/* ----------------------------------- */

	/* 수정을 위해 넘어온 GET VALUE****************************** */
	/* get 파라미터 한글깨짐 방지----------------------------------- */
	$edit_main_p_code	= iconv('euc-kr', 'utf-8', $_GET['main_p_code']);
	$edit_main_sub_code	= iconv('euc-kr', 'utf-8', $_GET['main_sub_code']);
	$edit_main_p_name	= iconv('euc-kr', 'utf-8', $_GET['main_p_name']);
	$edit_main_content	= iconv('euc-kr', 'utf-8', $_GET['main_content']);
	/* ************************************************************** */
	/*점검용
	$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
	$ipStr  = "192.168.2.62";
	$myCompany   = searchCompanyKind();
	if($myip==$ipStr){
		echo $myip."===".$myCompany."===<BR>";
	}//if End
	*/
?>
<?php
class PopLogic extends Smarty {
	// 생성자
	function PopLogic()
	{
		global $SmartyClass_TemplateDir;
		global $SmartyClass_CompileDir;
		global $SmartyClass_ConfigDir;
		global $SmartyClass_CacheDir;
		/* -----------------*/
		$this->Smarty();
		/* -----------------*/
		$this->template_dir		=$SmartyClass_TemplateDir;
		$this->compile_dir		=$SmartyClass_CompileDir;
		$this->config_dir		=$SmartyClass_ConfigDir;
		$this->cache_dir		=$SmartyClass_CacheDir;
		/* -----------------*/
	}//PopLogic End

	/* ------------------------------------------------------------------------------ */
	function GoOutInsertAction()	//외출신청 DB입력 실행
	{
		global	$CompanyKind; // 회사코드
		/* -----------------*/
		global	$MemberNo;	  // 사원번호
		global	$korName;	  // 한글이름
		global	$GroupCode;	  // 부서
		$GroupCode =(int)$GroupCode;
		global	$RankCode;	  // 직급
		global	$SortKey;	  // 직급+부서
		global  $ExtNo;		  // 내선번호
		/* ----------------- */
		global	$date_today;  // 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1; // date("Y-m-d H:i");			// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$date_today2; // $date_today1.":"."00";		// 오늘날짜 년월일 시분초
		global	$date_today3; // $date_today." 00:00:00";
		global	$nowYear;     // 오늘날짜 년          : yyyy
		global	$nowMonth;    // 오늘날짜 년월        : yyyy-mm
		global	$nowHour;	  // 현재 시
		global	$nowMin;	  // 현재 분
		global	$nowTime;	  // 현재 시:분
		/* ----------------- */
		global  $db;
		/* ----------------- */
		global  $sendDate;
		/* ----------------- */
		$ajax_out_p_code		= ($_POST['ajax_out_p_code']==""?"":$_POST['ajax_out_p_code']);				//외출 : 프로젝트코드
		$ajax_out_p_name     	= ($_POST['ajax_out_p_name']==""?"":$_POST['ajax_out_p_name']);				//외출 : 빅네임
		$ajax_out_radio_kind01  = ($_POST['ajax_out_radio_kind01']==""?"":$_POST['ajax_out_radio_kind01']);	//외출 : 외출(1),출장(2) 구분
		/* ----------------- */
		$ajax_out_radio_kind02  = ($_POST['ajax_out_radio_kind02']==""?"2":$_POST['ajax_out_radio_kind02']);	//외출 : PC_OFF:1, PC_ON:2
		/* ----------------- */
		$ajax_out_destination	= ($_POST['ajax_out_destination']==""?"":$_POST['ajax_out_destination']);	//외출 : 방문지
		$ajax_out_reason		= ($_POST['ajax_out_reason']==""?"":$_POST['ajax_out_reason']);					//외출 : 외출사유
		/* ----------------- */
		if($ajax_out_radio_kind02=="2"){//로그아웃
				$content1 = "[PC켜둠]".$ajax_out_reason;
				$content3 = "PC켜둠/".$ajax_out_reason;
		} else {
			$content1 = $ajax_out_reason;
			$content3 = $ajax_out_reason;
		}//if End
		/* ----------------- */
		$content2 = $ajax_out_destination."<|>".$content1;
		$sele2    = "CMD:OUTSIDE";
		/* ----------------- */
		/* 재석상태 관련 코드추가(2015-03-18) Start *********************************** */
		$value01 = $MemberNo;
		setAbsent($value01, '4', $ajax_out_destination, '', '');
		//$value01=사원번호, $value02=상태값(default:2:자리비움), $value03=코멘트
		//$value04=미지정(추후사용),$value05=미지정(추후사용)
		/* 재석상태 관련 코드추가 End *********************************** */

		//한맥ERP프로젝트 일원화 작업 : 181012
		$NewProjectCode = projectToColumn($ajax_out_p_code,'NewProjectCode');
		$NewProjectCode = $NewProjectCode ==""?"":$NewProjectCode;
		/* ----------------------------------- */


		//쿼리01
		$sql01= "INSERT INTO									";
		$sql01= $sql01." useractionlist_tbl						";
		$sql01= $sql01." (										";
		$sql01= $sql01."  MemberNo								";
		$sql01= $sql01." ,ActionKey								";

		$sql01= $sql01." ,ProjectCode							";
		$sql01= $sql01." ,NewProjectCode						";

		$sql01= $sql01." ,ActionName							";
		$sql01= $sql01." ,ActionTime							";
		$sql01= $sql01." ,SortKey								";
		$sql01= $sql01." )										";
		$sql01= $sql01." VALUES									";
		$sql01= $sql01."		(								";
		$sql01= $sql01."		  '".$MemberNo."'				";
		$sql01= $sql01."		, '".$sele2."'					";

		$sql01= $sql01."		, '".$ajax_out_p_code."'		";
		$sql01= $sql01."		, '".$NewProjectCode."'			";

		$sql01= $sql01."		, '".$content2."'				";
		$sql01= $sql01."		, '".$date_today2."'			";
		$sql01= $sql01."		, '".$SortKey."'				";
		$sql01= $sql01."		)								";
	/* ----------------------------------------------------------------------------- */
		//쿼리02
		$sql02= "INSERT INTO									";
		$sql02= $sql02." official_plan_tbl						";
		$sql02= $sql02." (										";
		$sql02= $sql02."   o_itinerary							";
		$sql02= $sql02." , o_group								";
		$sql02= $sql02." , o_name								";
		$sql02= $sql02." , o_start								";
		$sql02= $sql02." , o_end								";
		$sql02= $sql02." , o_object								";
		$sql02= $sql02." , o_note								";


		$sql02= $sql02." , projectcode							";
		$sql02= $sql02." , NewProjectCode						";

		$sql02= $sql02." , memberno								";
		$sql02= $sql02." , o_change								";
		$sql02= $sql02." , o_traffic							";
		$sql02= $sql02." )										";
		$sql02= $sql02." VALUES									";
		$sql02= $sql02."		(								";
		$sql02= $sql02."		  '".$ajax_out_destination."'	";
		$sql02= $sql02."		, '".$GroupCode."'				";
		$sql02= $sql02."		, '".$korName."'				";
		$sql02= $sql02."		, '".$date_today2."'			";
		$sql02= $sql02."		, '".$date_today3."'			";
		$sql02= $sql02."		, '".$content1."'				";
		$sql02= $sql02."		, '".$date_today."'				";

		$sql02= $sql02."		, '".$ajax_out_p_code."'		";
		$sql02= $sql02."		, '".$NewProjectCode."'			";

		$sql02= $sql02."		, '".$MemberNo."'				";
		$sql02= $sql02."		, '1'							";
		$sql02= $sql02."		, ''							";
		$sql02= $sql02."		)								";
	/* ----------------------------------------------------------------------------- */
//	mysql_query($sql01,$db);  //useractionlist_tbl 외출 저장
//	mysql_query($sql02,$db);  //official_plan_tbl  외출 저장
	/* ----------------------------------------------------------------------------- */

		//------------------------------------------------------------------------
		$ExecuteQuery01 = array();
		array_push($ExecuteQuery01, $sql01); //useractionlist_tbl 외출 저장
		array_push($ExecuteQuery01, $sql02); //official_plan_tbl  외출 저장
		$ErrorQueryIndex ="";
		//-------------
		$result_01 = "";
		//------------------------------------------------------------------------

		//트랜잭션 동작 Start /////////////////////////////////////////////////////////////////////////////
		//작업성공여부 플래그 값
		$success01 = true;
		//트랜잭션 시작
		$result = @mysql_query("SET AUTOCOMMIT=0",$db);
		$result = @mysql_query("BEGIN",$db);

		for($i=0; $i<count($ExecuteQuery01);$i++){
			//$ExecuteQuery01 : ARRAY안에 있는 쿼리 실행
			$result = mysql_query($ExecuteQuery01[$i],$db) ; //위험성평가 및 등록부 내용수정  : UPDATE 쿼리 DB실행

			if($result){

			}else{
				$success01 = false;
				$ErrorQueryIndex = $i;
				break;
			}
		}//for

		if(!$success01){
			//실패
			$result = @mysql_query("ROLLBACK",$db);

			if(!$success01){ //Fail : ALL
				$result_01 = "2";
			}

		}else{
			//성공
			$result = @mysql_query("COMMIT",$db);
			$result_01 = "1";
		}
		//트랜잭션 동작 End /////////////////////////////////////////////////////////////////////////////

// 		/* 마이스테이션 DB처리***************************************************************** */
// 		$db_hostname01 ='192.168.2.113';
// 		$db_database01 ='VBX_new';
// 		$db_username01 ='root';
// 		$db_password01 ='vbxsystem';
// 		/*-----------------------------------------------------------------------*/
// 		$db01	= mysql_connect($db_hostname01,$db_username01,$db_password01);
// 			if(!$db01) die ("Unable to connect to MySql : ".mysql_error());
// 		/*-----------------------------------------------------------------------*/
// 		mysql_select_db($db_database01);
// 		/*-----------------------------------------------------------------------*/
// 		mysql_set_charset("utf-8",$db01);
// 		mysql_query("set names utf8");
// 		/*-----------------------------------------------------------------------*/
// 		$date_today1; // date("Y-m-d H:i");			// 오늘날짜 년월일 시분 : yyyy-mm-dd 00:00
// 		$len          = strlen($date_today1);
// 		$ch_date      = substr($date_today1,2,14);  //  : yy-mm-dd 00:00  = 15-01-20 00:00
// 		//외출 처리
// 		/*-----------------------------------------------------------------------*/
// 		$sql_my01 = "SELECT * from person_tbl WHERE person_name = '".$korName."' ";
// 		$re_my01 = mysql_query($sql_my01,$db01);
// 		if(mysql_num_rows($re_my01) > 0){
// 			if(mysql_num_rows($re_my01) == 1){//단일값
// 				//외출 처리
// 				$sql03 =        "	UPDATE person_tbl SET								";
// 				$sql03 = $sql03."	client_stat = '12'									";
// 				$sql03 = $sql03."	,description='[$ch_date]<br>$ajax_out_reason'		";
// 				$sql03 = $sql03."	WHERE												";
// 				$sql03 = $sql03."		person_name = '$korName'						";
// 				////////////////////////////
// 				mysql_query($sql03,$db01);
// 				////////////////////////////

// 			}else{//복수값
// 				if($ExtNo!=""){
// 					$sql_my02 = "SELECT * from person_tbl WHERE extnum = '".$ExtNo."' ";
// 					$re_my02 = mysql_query($sql_my02,$db01);
// 					if(mysql_num_rows($re_my02) > 0){//내선번호 존재시
// 						//외출 처리**************************** */
// 						$sql03 =        "	UPDATE person_tbl SET								";
// 						$sql03 = $sql03."	client_stat = '12'									";
// 						$sql03 = $sql03."	,description='[$ch_date]<br>$ajax_out_reason'		";
// 						$sql03 = $sql03."	WHERE												";
// 						$sql03 = $sql03."		extnum = '$ExtNo' AND person_name = '$korName' 	";
// 						////////////////////////////
// 						mysql_query($sql03,$db01);
// 						////////////////////////////
// 						/* **************************** */
// 					}else{
// 						//pass
// 					}
// 				}else{
// 					//pass
// 				}
// 			}
// 		}//IF END
// 		/* *********************************************************************************** */

		if($result_01=="1"){ //DB처리 성공
				if($ajax_out_radio_kind02=="2"){//PC켜둠
						//echo $date_today2;
						echo "2";
				}else if($ajax_out_radio_kind02=="1"){//전원꺼짐
						//echo $date_today2;
						echo "1";
				}else{
						echo "99";
				}//if End
		}else{
				echo "111";
		}

		/* ----------------- */
	}  //GoOutInsertAction End

	/* ------------------------------------------------------------------------------ */
	function GoOutComback()	//외출복귀 DB입력 실행
	{
		global	$CompanyKind; // 회사코드
		/* -----------------*/
		global	$MemberNo;	  // 사원번호
		global	$korName;	  // 한글이름
		global	$GroupCode;	  // 부서
		$GroupCode =(int)$GroupCode;
		global	$RankCode;	  // 직급
		global	$SortKey;	  // 직급+부서
		global  $ExtNo;		  // 내선번호
		/* ----------------- */
		global	$date_today;  // 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1; // date("Y-m-d H:i");			// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$date_today2; // $date_today1.":"."00";		// 오늘날짜
		global	$date_today3; // $date_today." 00:00:00";
		global	$nowYear;     // 오늘날짜 년          : yyyy
		global	$nowMonth;    // 오늘날짜 년월        : yyyy-mm
		global	$nowHour;	  // 현재 시
		global	$nowMin;	  // 현재 분
		global	$nowTime;	  // 현재 시:분
		/* ----------------- */
		global  $db;
		/* ----------------- */
		$b_MemberNo		= ($_POST['b_MemberNo']==""?"":$_POST['b_MemberNo']);
		$b_str_time     = ($_POST['b_str_time']==""?"":$_POST['b_str_time']);  //외출시작시간
		/* ----------------- */
		//쿼리01
		$sql01= "UPDATE											";
		$sql01= $sql01." useractionlist_tbl						";
		$sql01= $sql01." SET									";
		$sql01= $sql01." ActionEndTime = '".$date_today3."'		";
		$sql01= $sql01."  WHERE									";
		$sql01= $sql01."  MemberNo = '".$b_MemberNo."' 			";
		$sql01= $sql01."  AND									";
		$sql01= $sql01."  ActionTime = '".$b_str_time."'		";
		/* ----------------------------------------------------------------------------- */
		//쿼리02
		$sql02= "UPDATE											";
		$sql02= $sql02." official_plan_tbl						";
		$sql02= $sql02." SET									";
		$sql02= $sql02." o_end = '".$date_today3."'				";
		$sql02= $sql02."  WHERE									";
		$sql02= $sql02."  o_start = '".$b_str_time."'			";
		//$sql02= $sql02."  AND									";
		//$sql02= $sql02."  no = '' 			";
		/* ----------------------------------------------------------------------------- */
	///////////////////////
	$result_01 = "1";
	$result = mysql_query($sql01,$db);  //useractionlist_tbl 외출 저장
	//mysql_query($sql02,$db);  //official_plan_tbl  외출 저장
	if($result){
		$result_01 = "1";
	}else{
		$result_01 = "2";
	}

	///////////////////////

// /* 마이스테이션 DB처리***************************************************************** */
// $db_hostname01 ='192.168.2.113';
// $db_database01 ='VBX_new';
// $db_username01 ='root';
// $db_password01 ='vbxsystem';
// /*-----------------------------------------------------------------------*/
// $db01	= mysql_connect($db_hostname01,$db_username01,$db_password01);
// if(!$db01) die ("Unable to connect to MySql : ".mysql_error());
// /*-----------------------------------------------------------------------*/
// mysql_select_db($db_database01);
// /*-----------------------------------------------------------------------*/
// mysql_set_charset("utf-8",$db01);
// mysql_query("set names utf8");
// /*-----------------------------------------------------------------------*/
// $date_today1; // date("Y-m-d H:i");			// 오늘날짜 년월일 시분 : yyyy-mm-dd 00:00
// $len          = strlen($date_today1);
// $ch_date      = substr($date_today1,2,14);  //  : yy-mm-dd 00:00  = 15-01-20 00:00
// //외출 처리
// /*-----------------------------------------------------------------------*/
// $sql_my01 = "SELECT * from person_tbl WHERE person_name = '".$korName."' ";
// $re_my01 = mysql_query($sql_my01,$db01);
// if(mysql_num_rows($re_my01) > 0){
// 	if(mysql_num_rows($re_my01) == 1){//단일값
// 		//외출 복귀처리
// 		$sql04 = "update person_tbl set client_stat = '',description='' where person_name = '$korName'  ";
// 		////////////////////////////
// 		mysql_query($sql04,$db01);
// 		////////////////////////////
// 	}else{//복수값
// 		if($ExtNo!=""){
// 			$sql_my02 = "SELECT * from person_tbl WHERE extnum = '".$ExtNo."' ";
// 			$re_my02 = mysql_query($sql_my02,$db01);
// 			if(mysql_num_rows($re_my02) > 0){//내선번호 존재시
// 				//외출 복귀처리
// 				$sql04 = "update person_tbl set client_stat = '',description='' where extnum = '$ExtNo' AND person_name = '$korName' ";
// 				////////////////////////////
// 				mysql_query($sql04,$db01);
// 				////////////////////////////
// 			}else{
// 				//pass
// 			}
// 		}else{
// 			//pass
// 		}
// 	}
// }//IF END
// /* *********************************************************************************** */

	echo $result_01;
	///////////////////////
	}  //GoOutComback End

	/* ------------------------------------------------------------------------------ */
	function GoOutPop() //페이지이동 : 외출신청
	{

	/*점검용
	$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
	$ipStr  = "192.168.2.62";
	$myCompany   = searchCompanyKind();
	if($myip==$ipStr){
		echo $myip."===".$myCompany."===<BR>";
	}//if End
	*/


		/* -----------------*/
		global	$CompanyKind; // 회사코드

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
		global  $connectFlag;	//웹브라우저를 통한 일반사용자의 접근(connectFlag=web)
		global  $member_status;

		$this->assign('connectFlag',$connectFlag);
		$this->assign('member_status',$member_status);
		/*---------------------------------------------------*/
		$this->assign('CompanyKind',$CompanyKind);
		$this->assign('MemberNo',$MemberNo);
		$this->assign('date_today',$date_today);
		$this->myinfo();

		//변경후(jquery->javascript)
		$this->display("intranet/common_layout/goOutPop_new.tpl");
		//변경전(jquery)
		//$this->display("intranet/common_layout/goOutPop.tpl");

//	$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
//	$ipStr  = "1.233.94.4";
/*
	if($myip==$ipStr && ){
		//echo $myip."===".$myCompany."===<BR>";
		$this->display("intranet/common_layout/goOutPop_new.tpl");
	}else{
		$this->display("intranet/common_layout/goOutPop.tpl");
	}//if End
*/

	}  //GoOutPop End

	/* ------------------------------------------------------------------------------ */
	function GoTripPop()	//페이지이동 : 출장신청(임원)
	{
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
		$this->assign('date_today',$date_today);
		$this->myinfo();

		//$this->display("intranet/common_layout/goTripPop.tpl");


				$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
				$ipStr  = "1.233.94.4";

				if($myip==$ipStr){
					//echo $myip."===".$myCompany."===<BR>";
					$this->display("intranet/common_layout/goTripPop_new.tpl");
					//$this->display("intranet/common_layout/goTripPop.tpl");
				}else{
					$this->display("intranet/common_layout/goTripPop.tpl");
				}//if End


	}  //GoTripPop End

	/* ------------------------------------------------------------------------------ */
	function GoTripInsertAction()	//출장신청 DB입력 실행
	{
		global	$CompanyKind; // 회사코드
		/* -----------------*/
		global	$MemberNo;	  // 사원번호
		global	$korName;	  // 한글이름
		global	$GroupCode;	  // 부서
		$GroupCode =(int)$GroupCode;
		global	$RankCode;	  // 직급
		global	$SortKey;	  // 직급+부서
		/* ----------------- */
		global	$date_today;  // 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1; // date("Y-m-d H:i");			// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$date_today2; // $date_today1.":"."00";		// 오늘날짜
		global	$date_today3; // $date_today." 00:00:00";
		global	$nowYear;     // 오늘날짜 년          : yyyy
		global	$nowMonth;    // 오늘날짜 년월        : yyyy-mm
		global	$nowHour;	  // 현재 시
		global	$nowMin;	  // 현재 분
		global	$nowTime;	  // 현재 시:분
		/* ----------------- */
		global  $db;
		/* ----------------- */
		global  $sendDate;
		/* ----------------- */
		$withMemId   = "";
		$withMemName = "";
		/* ----------------- */
		for($i=1;$i<5;$i++){																			//출장 : 출장인원(1~4명까지)
			$ajax_trip_memberId[$i]   = ($_POST['trip_memberId'.$i]==""?"":$_POST['trip_memberId'.$i]);
			$ajax_trip_memberName[$i] = ($_POST['trip_memberName'.$i]==""?"":$_POST['trip_memberName'.$i]);
			if($ajax_trip_memberId[$i] != ""){
				$withMemId   = $withMemId.$ajax_trip_memberId[$i].",";
				$withMemName = $withMemName.$ajax_trip_memberName[$i].",";
			}
		}//for End
		/*-------------------*/
		$len					= mb_strlen($withMemId,"UTF-8");
		$withMemId			= mb_substr($withMemId,0,$len-1,"UTF-8"); //마지막 쉼표제거 ","
		/*-------------------*/
		$len2					= mb_strlen($withMemName,"UTF-8");
		$withMemName	= mb_substr($withMemName,0,$len2-1,"UTF-8"); //마지막 쉼표제거 ","
		/*------------------------------------*/
		//$ajax_trip_groupName	= ($_POST['trip_groupName']==""?"":$_POST['trip_groupName']);		//출장 : 부서명(한글)
		$ajax_trip_p_code		= ($_POST['trip_p_code']==""?"":$_POST['trip_p_code']);					//출장 : 프로젝트코드
		$ajax_trip_p_name		= ($_POST['trip_p_name']==""?"":$_POST['trip_p_name']);				//출장 : 약칭
		$ajax_trip_area			= ($_POST['trip_area']==""?"":$_POST['trip_area']);						//출장 : 방문지역
		$ajax_trip_destination	= ($_POST['trip_destination']==""?"":$_POST['trip_destination']);		//출장 : 목적지
		$ajax_trip_reason			= ($_POST['trip_reason']==""?"":$_POST['trip_reason']);					//출장 : 출장목적
		$ajax_trip_start			= ($_POST['trip_start']==""?"":$_POST['trip_start']);						//출장 : 시작일자
		$ajax_trip_end				= ($_POST['trip_end']==""?"":$_POST['trip_end']);							//출장 : 종료일자
		//$ajax_trip_goout_today		= ($_POST['goout_today']==""?"":$_POST['goout_today']);		//출장 : 신청일자
		/*-------------------*/
		for($i=1;$i<5;$i++){																			//출장 : 출장인원(1~4명까지)
			$ajax_trip_memberId[$i] = ($_POST['trip_memberId'.$i]==""?"":$_POST['trip_memberId'.$i]);
		}//for End
		/*-------------------*/
		/*시작일2 종료일2 (시분초 포함)----------------*/
		$_ajax_trip_start		= $ajax_trip_start." 00:00:00";
		$_ajax_trip_end			= $ajax_trip_end." 23:59:00";
		/*출장인원정보--------*/


		$date_today  = date("Y-m-d");

		$ExecuteQuery01 = array();
		/*----------------*/
		for($i=1;$i<5;$i++){
			if($ajax_trip_memberId[$i] != ""){
				$sql01= " SELECT * FROM										";
				$sql01= $sql01." member_tbl									";
				$sql01= $sql01."	WHERE									";
				$sql01= $sql01."  MemberNo = '".$ajax_trip_memberId[$i]."'  ";
				$sql01= $sql01." AND  										";
				$sql01= $sql01." WorkPosition <= '8'						";
				/*------------------------------*/
				$result01     = mysql_query($sql01,$db);
				$result01_num = mysql_num_rows($result01);
				if($result01_num != 0) {
					$_MemberNo  = mysql_result($result01,0,"MemberNo");
					$_korName   = mysql_result($result01,0,"korName");
					$_GroupCode = mysql_result($result01,0,"GroupCode");
					$_RankCode  = mysql_result($result01,0,"RankCode");
					/*------------------------------*/
					if(mysql_result($result01,0,"RankCode") < "E1") {

						/* ----------------------------------- */
						//한맥ERP프로젝트 일원화 작업 : 181012
						$NewProjectCode = projectToColumn($ajax_trip_p_code,'NewProjectCode');
						$NewProjectCode = $NewProjectCode ==""?"":$NewProjectCode;
						/* ----------------------------------- */

						$azSQL01 = "SELECT max(num) FROM userstate_tbl";
						//ECHO $azSQL01."<br>";
						$res_userstate = mysql_query($azSQL01,$db);
						$res_num = current(mysql_fetch_array($res_userstate));
						$num_01  = $res_num + 1;
							$sql02  = "INSERT INTO									";
							$sql02 .= " userstate_tbl								";
							$sql02 .= " (											";
							$sql02 .= "  num										";
							$sql02 .= " ,MemberNo									";
							$sql02 .= " ,GroupCode									";
							$sql02 .= " ,state										";
							$sql02 .= " ,start_time									";
							$sql02 .= " ,end_time									";

							$sql02 .= " ,ProjectCode								";
							$sql02 .= " ,NewProjectCode								";

							$sql02 .= " ,note										";
							$sql02 .= " ,sub_code									";
							$sql02 .= " )											";
							$sql02 .= " VALUES										";
							$sql02 .= "		(										";
							$sql02 .= "		   '".$num_01."'						";
							$sql02 .= "		  ,'".$_MemberNo."'						";
							$sql02 .= "		  ,'".$_GroupCode."'					";
							$sql02 .= "		  ,'3'									";
							$sql02 .= "		  ,'".$ajax_trip_start."'				";
							$sql02 .= "		  ,'".$ajax_trip_end."'					";

							$sql02 .= "		  ,'".$ajax_trip_p_code."'				";
							$sql02 .= "		  ,'".$NewProjectCode."'				";

							$sql02 .= "		  ,'".$ajax_trip_destination."'			";
							$sql02 .= "		  ,''									";
							$sql02 .= "		)										";
						//echo $sql02."<br>";

							if($date_today>=$ajax_trip_start && $date_today<=$ajax_trip_end )
							{
								setAbsent($_MemberNo, '5', $ajax_trip_destination, '', '');
							}


						mysql_query($sql02,$db);
						array_push($ExecuteQuery01, $sql02);
					}//if End
					/*---------------------------------------------------------------------*/
				}//if($result01_num != 0) End
			}//if($ajax_trip_memberId[$i] != "") End
		}//for End
		/*----------------------------------------------------------------------------------*/

		/* ----------------------------------- */
		//한맥ERP프로젝트 일원화 작업 : 181012
		$NewProjectCode = projectToColumn($ajax_trip_p_code,'NewProjectCode');
		$NewProjectCode = $NewProjectCode ==""?"":$NewProjectCode;
		/* ----------------------------------- */

		/*----------------------------------------------------------------------------------*/
		$query01  = "INSERT INTO							";
		$query01 .= " official_plan_tbl						";
		$query01 .= " (										";
		$query01 .= "  o_area								";
		$query01 .= "  ,o_itinerary							";
		$query01 .= "  ,o_group								";
		$query01 .= "  ,o_name								";
		$query01 .= "  ,o_start								";
		$query01 .= "  ,o_end								";
		$query01 .= "  ,o_object							";
		$query01 .= "  ,o_traffic							";
		$query01 .= "  ,o_passwd							";
		$query01 .= "  ,o_note								";

		$query01 .= "  ,projectcode							";
		$query01 .= "  ,NewProjectCode						";

		$query01 .= "  ,memberno							";
		$query01 .= "  ,o_change							";
		$query01 .= "  )									";
		$query01 .= " VALUES								";
		$query01 .= "		(								";
		$query01 .= "	    '".$ajax_trip_area."'			";
		$query01 .= "	   ,'".$ajax_trip_destination."'	";
		$query01 .= "	   ,'".$GroupCode."'				";
		$query01 .= "	   ,'".$withMemName."'				";
		$query01 .= "	   ,'".$_ajax_trip_start."'			";
		$query01 .= "	   ,'".$_ajax_trip_end."'			";
		$query01 .= "	   ,'".$ajax_trip_reason."'			";
		$query01 .= "	   ,''								";
		$query01 .= "	   ,''								";
		$query01 .= "	   ,'".$date_today."'				";//출장 : 신청일자

		$query01 .= "	   ,'".$ajax_trip_p_code."'			";
		$query01 .= "	   ,'".$NewProjectCode."'			";


		$query01 .= "	   ,'".$withMemId."'				";
		$query01 .= "	   ,'2'								";//출장:2, 외근:1
		$query01 .= "		 )								";
		//////////////////////////
		mysql_query($query01,$db);
		//////////////////////////
		array_push($ExecuteQuery01, $query01);
		//////////////////////////
		$result_01 = "1";
		//////////////////////////


		////////////////////////////////////////////////////
		//DB처리 1단계 : userstate_tbl
		//DB처리 2단계 : official_plan_tbl
		//DB처리 3단계 : person_tbl
		////////////////////////////////////////////////////
		//$result_01 = "1"; //DB처리 성공(1~3단계)
		//$result_01 = "2"; // DB처리 실패(1~2단계)
		//$result_01 = "3"; //DB처리 실패(1~2단계는 성공, 3단계 실패)
		////////////////////////////////////////////////////
		//return $sql03;
		 echo $result_01;
		//echo 1;
		////////////////////////////////////////////////////

	}  //GoTripInsertAction End



	/* ------------------------------------------------------------------------------ */
	function AddWorkPop()	//페이지이동 : 업무추가
	{

		extract($_REQUEST);
		global	$CompanyKind; // 회사코드
		global	$MemberNo;	  // 사원번호
		global	$korName;	  // 한글이름
		global	$GroupCode;	  // 부서
		global	$RankCode;	  // 직급
		global	$SortKey;	  // 직급+부서
		global	$date_today;  // 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1; // 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$nowYear;     // 오늘날짜 년          : yyyy
		global	$nowMonth;    // 오늘날짜 년월        : yyyy-mm
		global	$nowHour;	  // 현재 시
		global	$nowMin;	  // 현재 분
		global	$nowTime;	  // 현재 시:분
		/* ----------------- */
		global  $db;
		global  $sendDate;
		/* ----------------- */
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$date_today	=  $_REQUEST['search_date']==""?$date_today:$_REQUEST['search_date'];
		$this->assign('search_date',$date_today);
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$sql = "select note from dallyproject_tbl where MemberNo = '".$MemberNo."' and EntryTime like '".$date_today."%'";

		//echo $sql;
		/* ----------------- */
		$re     = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		/* ----------------- */
		if($re_num != 0) {
			$Note = mysql_result($re,0,"Note");
			//echo $Note."<br>";
			$Note_arr = explode("<br>",$Note);
		}
		/* ----------------- */
		if($Note_arr[0] != "") {
			$Note1 = explode("<|>",$Note_arr[0]);

			$this->assign('Note1',array($Note1[0],$Note1[1],$Note1[2],$Note1[3]));
			$this->assign('timeSelect01',$Note1[3]);

			$this->assign('ProjectViewCode_01',projectToColumn($Note1[0],'ProjectViewCode',DevConfirm($MemberNo)));
			//$this->assign('Note1',$Note1[0]."==== ".$Note1[1]."==== ".$Note1[2]."==== ".$Note1[3]);
		}
		if($Note_arr[1] != "") {
			$Note2 = explode("<|>",$Note_arr[1]);
			$this->assign('Note2',array($Note2[0],$Note2[1],$Note2[2],$Note2[3]));
			$this->assign('timeSelect02',$Note2[3]);
			$this->assign('ProjectViewCode_02',projectToColumn($Note2[0],'ProjectViewCode',DevConfirm($MemberNo)));
			//$this->assign('Note2',$Note2[0]."==== ".$Note2[1]."==== ".$Note2[2]."==== ".$Note2[3]);
		}
		if($Note_arr[2] != "") {
			$Note3 = explode("<|>",$Note_arr[2]);
			$this->assign('Note3',array($Note3[0],$Note3[1],$Note3[2],$Note3[3]));
			$this->assign('timeSelect03',$Note3[3]);
			$this->assign('ProjectViewCode_03',projectToColumn($Note3[0],'ProjectViewCode',DevConfirm($MemberNo)));

			//$this->assign('Note3',$Note3[0]."==== ".$Note3[1]."==== ".$Note3[2]."==== ".$Note3[3]);
		}
		if($Note_arr[3] != "") {
			$Note4 = explode("<|>",$Note_arr[3]);
			$this->assign('Note4',array($Note4[0],$Note4[1],$Note4[2],$Note4[3]));
			$this->assign('timeSelect04',$Note4[3]);
			$this->assign('ProjectViewCode_04',projectToColumn($Note4[0],'ProjectViewCode',DevConfirm($MemberNo)));
			
			//$this->assign('Note3',$Note3[0]."==== ".$Note3[1]."==== ".$Note3[2]."==== ".$Note3[3]);
		}
		if($Note_arr[4] != "") {
			$Note5 = explode("<|>",$Note_arr[4]);
			$this->assign('Note5',array($Note5[0],$Note5[1],$Note5[2],$Note5[3]));
			$this->assign('timeSelect05',$Note5[3]);
			$this->assign('ProjectViewCode_05',projectToColumn($Note5[0],'ProjectViewCode',DevConfirm($MemberNo)));
			
			//$this->assign('Note3',$Note3[0]."==== ".$Note3[1]."==== ".$Note3[2]."==== ".$Note3[3]);
		}
		/* --------------------------------------------- */
		
		
		$sql  = " Select  ";
		$sql .= "     Code CODE, ";
		$sql .= "     Name NAME ";
		$sql .= " From  ";
		$sql .= "     systemconfig_tbl  ";
		$sql .= " Where SysKey = 'timeOptions' ";
		$resource = mysql_query($sql, $db);
		
		if (!$resource) {
			echo "mysql_query(timeOptions resource) error";
			return;
		}
		
		$timeOptions = array();
		while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
			$timeOptions[$row["CODE"]] = $row["NAME"];
		}
		$this->assign('timeOptions', $timeOptions);
        /* --------------------------------------------- */
		$this->assign('memberID',$MemberNo);
		/* --------------------------------------------- */
		$this->assign('CompanyKind',$CompanyKind);
		/* --------------------------------------------- */
		$this->assign('addOn_kind',$addOn_kind);//$addOn_kind==end : 업무종료 페이지에서 입력
		/* --------------------------------------------- */

		if(DevConfirm($MemberNo)){
			//echo $MemberNo.'<br><br>';
			$this->assign('devYN','Y');
		}

		//=================================================================================
		$set_from_date = "2018-10-28 23:59:00";
		//$set_from_date = "2018-10-26 13:00:00";
		if(SetNewCodeBoolean($set_from_date, "")){
			//echo 'new코드 적용함1<br><br>';
			//-----------------------------------------------------------
			$this->display("intranet/common_layout/addWorkPop.tpl");
			//-----------------------------------------------------------
		}else{
			if(DevConfirm($MemberNo)){
				//echo 'new코드 적용안함2<br><br>';
				//-----------------------------------------------------------
				//$this->display("intranet/common_layout/addWorkPop.tpl");
				$this->display("intranet/common_layout/addWorkPop.tpl");
				//-----------------------------------------------------------
			}else{
				//-----------------------------------------------------------
				$this->display("intranet/common_layout/addWorkPop.tpl");
				//-----------------------------------------------------------
			}
		}
		//=================================================================================

	}  //AddWorkPop End
	/* ------------------------------------------------------------------------------ */
	function AddWork()	//업무추가  DB실행
	{
		/*-------------*/
		global	$CompanyKind; // 회사코드
		/*-------------*/
		global $MemberNo;
		/*-------------*/
		global $p_code_01;
		global $sub_code_01;
		global $content_01;
		global $time_01;
		/*-------------*/
		global $p_code_02;
		global $sub_code_02;
		global $content_02;
		global $time_02;
		/*-------------*/
		global $p_code_03;
		global $sub_code_03;
		global $content_03;
		global $time_03;
		/*-------------*/
		/*-------------*/
		global $p_code_04;
		global $sub_code_04;
		global $content_04;
		global $time_04;
		/*-------------*/
		/*-------------*/
		global $p_code_05;
		global $sub_code_05;
		global $content_05;
		global $time_05;
		/*-------------*/
		global $update_data; //DB에 넣을 값 조합
		/*-------------*/
		global $dallyin;     //db 업데이트 쿼리
		/*-------------*/
		global $date_today; //오늘날짜 yyyy-mm-dd

		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$date_today	=  $_REQUEST['search_date']==""?$date_today:$_REQUEST['search_date'];
		//$this->assign('search_date',$date_today);
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////


		for($i=1;$i<6;$i++){

			$p_code_0[$i]	= ($_POST['p_code_0'.$i]==""?"":$_POST['p_code_0'.$i]);

			/* ----------------------------------- */
			if($p_code_0[$i]!=""){
				//한맥ERP프로젝트 일원화 작업 : 181012
				$new_p_code_0[$i] = projectToColumn($p_code_0[$i],'NewProjectCode',DevConfirm($MemberNo));//한맥 ERP 연동 프로젝트코드 : NewProjectCode
			}
			/* ----------------------------------- */
			$sub_code_0[$i]	= ($_POST['sub_code_0'.$i]==""?"":$_POST['sub_code_0'.$i]);
			$content_0[$i]	= ($_POST['content_0'.$i]==""?"":$_POST['content_0'.$i]);
			$hour_0[$i]		= ($_POST['hour_0'.$i]==""?"":$_POST['hour_0'.$i]);
			$min_0[$i]		= ($_POST['min_0'.$i]==""?"":$_POST['min_0'.$i]);
			$time_0[$i]		= ($_POST['time_0'.$i]==""?"":$_POST['time_0'.$i]);


// 			echo 'p_code_0='.$p_code_0[$i];
// 			echo '<br>';
// 			echo 'new_p_code_0='.$new_p_code_0[$i];

		} //for end

		/* ----------------------------------- */
		$new_p_code_01 ="";
		$new_p_code_02 ="";
		$new_p_code_03 ="";
		$new_p_code_04 ="";
		$new_p_code_05 ="";
		if($p_code_01!=""){
			//한맥ERP프로젝트 일원화 작업 : 181012
			$new_p_code_01 = projectToColumn($p_code_01,'NewProjectCode',DevConfirm($MemberNo));//한맥 ERP 연동 프로젝트코드 : NewProjectCode
		}
		if($p_code_02!=""){
			//한맥ERP프로젝트 일원화 작업 : 181012
			$new_p_code_02 = projectToColumn($p_code_02,'NewProjectCode',DevConfirm($MemberNo));//한맥 ERP 연동 프로젝트코드 : NewProjectCode
		}
		if($p_code_03!=""){
			//한맥ERP프로젝트 일원화 작업 : 181012
			$new_p_code_03 = projectToColumn($p_code_03,'NewProjectCode',DevConfirm($MemberNo));//한맥 ERP 연동 프로젝트코드 : NewProjectCode
		}
		/* ----------------------------------- */
		if($p_code_04!=""){
			//한맥ERP프로젝트 일원화 작업 : 181012
			$new_p_code_04 = projectToColumn($p_code_04,'NewProjectCode',DevConfirm($MemberNo));//한맥 ERP 연동 프로젝트코드 : NewProjectCode
		}
		/* ----------------------------------- */
		if($p_code_05!=""){
			//한맥ERP프로젝트 일원화 작업 : 181012
			$new_p_code_05 = projectToColumn($p_code_05,'NewProjectCode',DevConfirm($MemberNo));//한맥 ERP 연동 프로젝트코드 : NewProjectCode
		}
		/* ----------------------------------- */

		if(DevConfirm($MemberNo)){

		}


//  		echo 'p_code_0='.$p_code_01;
//  		echo '<br>';
//  		echo 'new_p_code_01='.$new_p_code_01;
// // 		echo $new_p_code_03;
// 		exit();



		/* ----------------------------------- */
// 		if( $p_code_01 !=""){
// 			$update_data = $update_data.$p_code_01."<|>".$sub_code_01."<|>".$content_01."<|>".$time_01."<|><br>";
// 		}
// 		if( $p_code_02 !=""){
// 			$update_data = $update_data.$p_code_02."<|>".$sub_code_02."<|>".$content_02."<|>".$time_02."<|><br>";
// 		}
// 		if( $p_code_03 !=""){
// 			$update_data = $update_data.$p_code_03."<|>".$sub_code_03."<|>".$content_03."<|>".$time_03."<|><br>";
// 		} //if end
		/* ----------------------------------- */
		if( $p_code_01 !=""){
			$update_data = $update_data.$p_code_01."<|>".$sub_code_01."<|>".$content_01."<|>".$time_01."<|>".$new_p_code_01."<br>";
		}
		if( $p_code_02 !=""){
			$update_data = $update_data.$p_code_02."<|>".$sub_code_02."<|>".$content_02."<|>".$time_02."<|>".$new_p_code_02."<br>";
		}
		if( $p_code_03 !=""){
			$update_data = $update_data.$p_code_03."<|>".$sub_code_03."<|>".$content_03."<|>".$time_03."<|>".$new_p_code_03."<br>";
		} //if end
		if( $p_code_04 !=""){
			$update_data = $update_data.$p_code_04."<|>".$sub_code_04."<|>".$content_04."<|>".$time_04."<|>".$new_p_code_04."<br>";
		} //if end
		if( $p_code_05 !=""){
			$update_data = $update_data.$p_code_05."<|>".$sub_code_05."<|>".$content_05."<|>".$time_05."<|>".$new_p_code_05."<br>";
		} //if end


		/* ----------------------------------- */
		$sql    = "select COUNT(*) CNT from dallyproject_tbl where MemberNo = '".$MemberNo."' and  EntryTime like '".$date_today."%'";
		$re     = mysql_query($sql);
		$result_count = mysql_result($re,0,"CNT"); 			//금일업무 등록여부
		/* ----------------------------------- */
		if($result_count == 0) {  //금일 등록된 업무없음
			echo "3"; // 업무추가는 금일업무 시작 후 가능합니다.
		}else{	//금일 등록된 업무존재

					$sql2 = "DELETE from dallyproject_addwork_tbl where MemberNo = '".$MemberNo."' and  EntryTime like '".$date_today."%' ";
					mysql_query($sql2);


					for($i=1;$i<6;$i++){
						if($p_code_0[$i] !="" && $sub_code_0[$i]!="" && $content_0[$i]){

							$array_WorkTime = explode(":",$time_0[$i] );
							$work_hour = intval($array_WorkTime[0]);//시간
							$work_min  = intval($array_WorkTime[1]);//분

							//쿼리01
							$sql01  = "INSERT INTO						";
							$sql01 .= " dallyproject_addwork_tbl	";
							$sql01 .= " ( MemberNo, EntryTime, seq_no, project_code, new_project_code, activity_code, contents, work_hour, work_min )	";
							$sql01 .= " VALUES							";
							$sql01 .= " 	(							";
							$sql01 .= " 	 '".$MemberNo."'			"; //사원코드
							$sql01 .= " 	,'".$date_today."'			"; //YYYY-MM-DD
							$sql01 .= " 	,'".$i."'					";

							$sql01 .= " 	,'".$p_code_0[$i]."'		"; //프로젝트코드
							$sql01 .= " 	,'".$new_p_code_0[$i]."'	"; //한맥 ERP 연동 프로젝트코드 : NewProjectCode

							$sql01 .= " 	,'".$sub_code_0[$i]."'		"; //project_code_activity_tbl : activity_code
							$sql01 .= " 	,'".$content_0[$i]."'		"; //업무내용
							$sql01 .= " 	,'".$work_hour."'   		"; //입력형식 = 시간= 00
							$sql01 .= " 	,'".$work_min."'			"; //입력형식 = 분 = 00/30
							$sql01 .= "	)";

							mysql_query($sql01);
						}
					} //for end




					 //프로젝트코드, 서브코드, 업무내용 만 수정가능!
					$dallyin = " UPDATE dallyproject_tbl SET					";
					$dallyin = $dallyin."  Note ='".$update_data."'				";
					$dallyin = $dallyin."  where								";
					$dallyin = $dallyin."  MemberNo ='".$MemberNo."'			";
					$dallyin = $dallyin."  and									";
					$dallyin = $dallyin."  EntryTime like '".$date_today."%'	";
							/* ----------------------------------- */
							/* 쿼리실행 -------------------------- */
							//echo $dallyin;
							mysql_query($dallyin);
							/* ----------------------------------- */
							echo "1";	//업데이트성공
							/* ----------------------------------- */
		} //if End
		/* ----------------------------------- */
	}//AddWork Enc
	/* ------------------------------------------------------------------------------ */
	function editWorkPop()	//페이지이동 : 업무수정
	{
		global	$CompanyKind; // 회사코드
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
		global  $sendDate;
		/* ----------------- */
		global	$edit_main_p_code;
		global	$edit_main_sub_code;
		global	$edit_main_p_name;
		global	$edit_main_content;
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$date_today	=  $_REQUEST['search_date']==""?$date_today:$_REQUEST['search_date'];
		$this->assign('search_date',$date_today);
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/* ------------------------------------------ */
		$sql =      " SELECT								";
		$sql = $sql."	 EntryPCode		as d_EntryPCode 	";
		$sql = $sql."	,EntryPCode2	as d_EntryPCode2	";
		$sql = $sql."	,EntryJobCode	as d_EntryJobCode	";
		$sql = $sql."	,EntryJob		as d_EntryJob		";


		$sql = $sql."	,LeaveTime		as e_LeaveTime 		";
		$sql = $sql."	,LeavePCode		as e_LeavePCode 	";
		$sql = $sql."	,LeavePCode2	as e_LeavePCode2	";
		$sql = $sql."	,LeaveJobCode	as e_LeaveJobCode	";
		$sql = $sql."	,LeaveJob		as e_LeaveJob		";
		
		$sql = $sql."	,ReportingBoss		as e_ReportingBoss		";
		

		$sql = $sql."	FROM								";
		$sql = $sql."	dallyproject_tbl					";
		$sql = $sql."	WHERE								";
		$sql = $sql."	MemberNo = '".$MemberNo."'			";
		$sql = $sql."	AND									";
		$sql = $sql."	EntryTime like '".$date_today."%'	";
		/* ------------------------------------------ */
		$re				= mysql_query($sql);
		/* ------------------------------------------ */

		$e_LeaveTime	= mysql_result($re,0,"e_LeaveTime"); 			//프로젝트코드
		$e_LeavePCode	= mysql_result($re,0,"e_LeavePCode"); 			//프로젝트코드

		if(($e_LeaveTime!="" || $e_LeaveTime!="0000-00-00 00:00:00") && ($e_LeavePCode!="")){
			//업무종료시간 존재&&종료프로젝트코드 있을시
			//업무종료시 입력한 내용 표시
			$d_EntryPCode	= mysql_result($re,0,"e_LeavePCode"); 			//프로젝트코드
			$d_EntryPCode2	= mysql_result($re,0,"e_LeavePCode2"); 			//프로젝트코드
			$d_EntryJobCode = mysql_result($re,0,"e_LeaveJobCode"); 		//잡코드
			$d_EntryJob		= mysql_result($re,0,"e_LeaveJob"); 			//작업내용
		}else{
			$d_EntryPCode	= mysql_result($re,0,"d_EntryPCode"); 			//프로젝트코드
			$d_EntryPCode2	= mysql_result($re,0,"d_EntryPCode2"); 			//프로젝트코드
			$d_EntryJobCode = mysql_result($re,0,"d_EntryJobCode"); 		//잡코드
			$d_EntryJob		= mysql_result($re,0,"d_EntryJob"); 			//작업내용
		}
		/* ------------------------------------------ */
		$Str_arr = explode("-",$d_EntryPCode);
		/*-------------------------*/
		$arrayStr1 = $Str_arr[0];
		$arrayStr2 = $Str_arr[1];
		$arrayStr3 = $Str_arr[2];
		/*-------------------------*/
		//관리,고문,교휴,영업,업무,지원 은 XX-로 시작함
		$d_EntryPCode_edit = $d_EntryPCode;


		/*-------------------------------------------------------------------------------*/
		if(change_XXIS02($d_EntryPCode,$CompanyKind)){ // XX관련 코드 => 리턴값이 true


			//$d_EntryPCode_edit = "XX"."-".$arrayStr2."-".$arrayStr3;
			$d_EntryPCode_edit = change_XXIS02($d_EntryPCode,$CompanyKind,"text");

		}//if End

		if(DevConfirm($MemberNo)){

// 			echo 'd_EntryPCode='.$d_EntryPCode.'<BR><BR>';
// 			echo 'CompanyKind='.$CompanyKind.'<BR><BR>';
// 			echo 'd_EntryPCode_edit='.$d_EntryPCode_edit.'<BR><BR>';
		}

		/*-------------------------------------------------------------------------------*/

		if(DevConfirm($MemberNo)){
			$sql2 =      " SELECT										";
			$sql2 = $sql2."	 ProjectCode	    as p_ProjectCode 		";
			$sql2 = $sql2."	,NewProjectCode	    as p_NewProjectCode 	";
			$sql2 = $sql2."	,ProjectViewCode	as p_ProjectViewCode 	";
			$sql2 = $sql2."	,ProjectNickname	as p_ProjectNickname  	";
			$sql2 = $sql2."	FROM										";
			$sql2 = $sql2."	Project_tbl							";
			$sql2 = $sql2."	WHERE										";
			//$sql2 = $sql2."	ProjectCode = '".$d_EntryPCode_edit."'		";
			$sql2 = $sql2."	ProjectCode LIKE '%".$d_EntryPCode_edit."'		";
		}else{
			$sql2 =      " SELECT										";
			$sql2 = $sql2."	 ProjectCode	    as p_ProjectCode 		";
			$sql2 = $sql2."	,NewProjectCode	    as p_NewProjectCode 	";
			$sql2 = $sql2."	,ProjectViewCode	as p_ProjectViewCode 	";
			$sql2 = $sql2."	,ProjectNickname	as p_ProjectNickname  	";
			$sql2 = $sql2."	FROM										";
			$sql2 = $sql2."	project_tbl									";
			$sql2 = $sql2."	WHERE										";
			$sql2 = $sql2."	ProjectCode = '".$d_EntryPCode_edit."'		";
		}



		/*-------------------------*/
		$re2 = mysql_query($sql2);
		$re_num2 = mysql_num_rows($re2);
		if($re_num2 != 0)
		{

			$p_ProjectCode 		= mysql_result($re2,0,"p_ProjectCode"); 			//프로젝트 코드
			$p_NewProjectCode 	= mysql_result($re2,0,"p_NewProjectCode"); 			//프로젝트 NEW코드
			$p_ProjectViewCode	= mysql_result($re2,0,"p_ProjectViewCode"); 		//프로젝트 뷰코드
			$p_ProjectNickname	= mysql_result($re2,0,"p_ProjectNickname"); 		//프로젝트 닉네임

		}else{
			$p_ProjectNickname	="";
		}
		
		$e_ReportingBoss	= mysql_result($re,0,"e_ReportingBoss"); 			//업무검토자
		$e_ReportingBossText="";
		
		if($e_ReportingBoss!=""){
			
			$MemSQL = "SELECT CONCAT(a.korName,CONCAT(' ',(SELECT Name FROM systemconfig_tbl WHERE SysKey='PositionCode' AND Code=a.RankCode))) AS Upper_User
						FROM member_tbl a WHERE a.MemberNo='$e_ReportingBoss' AND a.WorkPosition!=9";
			
			$MemRe = mysql_query($MemSQL,$db);
			
			if(mysql_num_rows($MemRe)>0){
				$e_ReportingBossText = mysql_result($MemRe,0,"Upper_User");
			}
		}
		/* --------------------------------------------- */
		$this->assign('CompanyKind',$CompanyKind);
		$this->assign('MemberNo',$MemberNo);
		$this->assign('memberID',$MemberNo);
		$this->assign('WP_memberID',$MemberNo);

		$this->assign('code_EntryPCode',$d_EntryPCode);		//프로젝트코드
		$this->assign('code_EntryPCode2',$d_EntryPCode2);	//프로젝트코드
		$this->assign('code_EntryJobCode',$d_EntryJobCode);	//잡코드
		$this->assign('code_EntryJob',$d_EntryJob);		//작업내용
		$this->assign('code_ProjectCode',$p_ProjectCode);
		$this->assign('code_NewProjectCode',$p_NewProjectCode);
		$this->assign('code_ProjectViewCode',$p_ProjectViewCode);
		$this->assign('code_ProjectNickname',$p_ProjectNickname);
		
		$this->assign('code_ReportingBoss',$e_ReportingBoss);
		$this->assign('code_ReportingBossText',$e_ReportingBossText);

		if(DevConfirm($MemberNo)){
			$this->assign('devYN','Y');
		}


		if(($e_LeaveTime!="" || $e_LeaveTime!="0000-00-00 00:00:00") && ($e_LeavePCode!="")){
			//업무종료시간 존재&&종료프로젝트코드 있을시
			//업무종료시 입력한 내용 표시
			$this->assign('endWorkYN','Y');
		}else{
			$this->assign('endWorkYN','');
		}
		//=================================================================================
		$set_from_date = "2018-10-28 23:59:00";
		//$set_from_date = "2018-10-26 13:00:00";
		if(SetNewCodeBoolean($set_from_date, "")){
			//echo 'new코드 적용함<br><br>';
			//-----------------------------------------------------------
			$this->display("intranet/common_layout/editWorkPop_khg.tpl");
			//-----------------------------------------------------------
		}else{
			if(DevConfirm($MemberNo)){
				//echo 'new코드 적용안함<br><br>';
				//-----------------------------------------------------------
				//$this->display("intranet/common_layout/editWorkPop.tpl");
				$this->display("intranet/common_layout/editWorkPop.tpl");
				//-----------------------------------------------------------
			}else{
				//-----------------------------------------------------------
				$this->display("intranet/common_layout/editWorkPop.tpl");
				//-----------------------------------------------------------
			}
		}
		//=================================================================================



	}  //editWorkPop End
	/* ------------------------------------------------------------------------------ */
	function editWork()	//업무수정  DB실행
	{
		global	$CompanyKind; // 회사코드
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
		global	$EntryPCode;
		global	$EntryJobCode;
		global	$EntryJob;
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$date_today	=  $_REQUEST['search_date']==""?$date_today:$_REQUEST['search_date'];



		$endWorkYN	=  $_REQUEST['endWorkYN']==""?"":$_REQUEST['endWorkYN'];

		//$this->assign('search_date',$date_today);
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/* ----------------------------------- */
		$EntryPCode   = $_POST['p_code'];
		$EntryJobCode = $_POST['sub_code'];
		$EntryJob     = $_POST['content_detail'];
		/* ----------------------------------- */
		$sql2    = "select COUNT(*) CNT,IFNULL(WorkConfirmYN,'N') AS WorkConfirmYN from dallyproject_tbl where MemberNo = '".$MemberNo."' and  EntryTime like '".$date_today."%'";
		
		/* ----------------------------------- */
		$re2          = mysql_query($sql2);
		$result_count = mysql_result($re2,0,"CNT"); 			//금일업무 등록여부
		$result_workConfirmYN = mysql_result($re2,0,"WorkConfirmYN");
		/* ----------------------------------- */
		if($result_count == 0) {  //금일 등록된 업무없음
			echo "3";	// 금일업무 시작 후 수정이 가능합니다.
		}else{	// DALLYPROJECT_TBL테이블에 금일 EntryTime 정보가 존재시 업데이트
			if($result_workConfirmYN == "Y"){
				echo "4";
				exit;
			}
			
			 //프로젝트코드, 서브코드, 업무내용 만 수정가능!
			$compare_date_today		= date("Y-m-d");

			if(DevConfirm($MemberNo)){
				/* ----------------------------------- */
				//한맥ERP프로젝트 일원화 작업 : 181012
				$NewProjectCode = projectToColumn($EntryPCode,'NewProjectCode',DevConfirm($MemberNo));
				$NewProjectCode = $NewProjectCode ==""?"":$NewProjectCode;
				/* ----------------------------------- */
			}else{
				/* ----------------------------------- */
				//한맥ERP프로젝트 일원화 작업 : 181012
				$NewProjectCode = projectToColumn($EntryPCode,'NewProjectCode');
				$NewProjectCode = $NewProjectCode ==""?"":$NewProjectCode;
				/* ----------------------------------- */
			}

			/*
			if($date_today==$compare_date_today){
				$dallyin = " UPDATE dallyproject_tbl SET ";

				if($endWorkYN=="Y"){
					$dallyin = $dallyin."   LeavePCode='".$EntryPCode."' ";
					$dallyin = $dallyin."  ,LeavePCode2='".$NewProjectCode."' ";
					$dallyin = $dallyin."  ,LeaveJobCode='".$EntryJobCode."' ";
					$dallyin = $dallyin."  ,LeaveJob='".$EntryJob."' ";
				}else{
					$dallyin = $dallyin."   EntryPCode='".$EntryPCode."' ";
					$dallyin = $dallyin."  ,EntryPCode2='".$NewProjectCode."' ";
					$dallyin = $dallyin."  ,EntryJobCode='".$EntryJobCode."' ";
					$dallyin = $dallyin."  ,EntryJob='".$EntryJob."' ";
				}

				$dallyin = $dallyin."  where ";
				$dallyin = $dallyin."  MemberNo ='".$MemberNo."' ";
				$dallyin = $dallyin."  and ";
				$dallyin = $dallyin."  EntryTime like '".$date_today."%'";
			}else{
				$dallyin = " UPDATE dallyproject_tbl SET ";

				$dallyin = $dallyin."  EntryPCode='".$EntryPCode."' ";
				$dallyin = $dallyin."  ,EntryPCode2='".$NewProjectCode."' ";

				$dallyin = $dallyin." ,EntryJobCode='".$EntryJobCode."' ";
				$dallyin = $dallyin." ,EntryJob='".$EntryJob."' ";

				$dallyin = $dallyin." ,LeavePCode='".$EntryPCode."' ";
				$dallyin = $dallyin." ,LeavePCode2='".$NewProjectCode."' ";

				$dallyin = $dallyin." ,LeaveJobCode='".$EntryJobCode."' ";
				$dallyin = $dallyin." ,LeaveJob='".$EntryJob."' ";

				$dallyin = $dallyin."  where ";
				$dallyin = $dallyin."  MemberNo ='".$MemberNo."' ";
				$dallyin = $dallyin."  and ";
				$dallyin = $dallyin."  EntryTime like '".$date_today."%'";
			}
			*/

				$dallyin = " UPDATE dallyproject_tbl SET ";

				$dallyin = $dallyin."  EntryPCode='".$EntryPCode."' ";
				$dallyin = $dallyin."  ,EntryPCode2='".$NewProjectCode."' ";

				$dallyin = $dallyin." ,EntryJobCode='".$EntryJobCode."' ";
				$dallyin = $dallyin." ,EntryJob='".$EntryJob."' ";
				
				$dallyin = $dallyin." ,ReportingBoss='".$_REQUEST["reportingBoss"]."' ";
/* 
				$dallyin = $dallyin." ,LeavePCode='".$EntryPCode."' ";
				$dallyin = $dallyin." ,LeavePCode2='".$NewProjectCode."' ";

				$dallyin = $dallyin." ,LeaveJobCode='".$EntryJobCode."' ";
				$dallyin = $dallyin." ,LeaveJob='".$EntryJob."' ";
*/
				$dallyin = $dallyin."  where ";
				$dallyin = $dallyin."  MemberNo ='".$MemberNo."' ";
				$dallyin = $dallyin."  and ";
				$dallyin = $dallyin."  EntryTime like '".$date_today."%'";

			if(DevConfirm($MemberNo)){
				/* ----------------------------------- */
				//echo $dallyin;
				/* ----------------------------------- */
			}

	mysql_query($dallyin);
	echo "1";	//실행성공
		}//if End
	}//editWork End
	/* ------------------------------------------------------------------------------ */
	function OverWorkPop()	//페이지이동 : 업무종료
	{
		global	$CompanyKind; // 회사코드
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
		global  $sendDate;
		/* ----------------- */
		$connectFlag = ($_GET['connectFlag']==""?"":$_GET['connectFlag']); //웹브라우저를 통한 로그인 후, 업무종료, connectFlag=web
		/* ----------------- */
		$this->myinfo();
		$this->assign('memberID',$MemberNo);
		$this->assign('connectFlag',$connectFlag);
		$this->assign('nowHour',$nowHour);
		$this->assign('nowMin',$nowMin);

		if(DevConfirm($MemberNo)){
			//echo $MemberNo.'<br><br>';
			$this->assign('devYN','Y');
		}
		//=================================================================================
		$set_from_date = "2018-10-28 23:59:00";
		//$set_from_date = "2018-10-26 13:00:00";
		if(SetNewCodeBoolean($set_from_date, "")){
			//echo 'new코드 적용함1<br><br>';
			//-----------------------------------------------------------
			$this->display("intranet/common_layout/overWorkPop.tpl");
			//-----------------------------------------------------------
		}else{
			if(DevConfirm($MemberNo)){
				//echo 'new코드 적용안함2<br><br>';
				//-----------------------------------------------------------
				//$this->display("intranet/common_layout/overWorkPop.tpl");
				$this->display("intranet/common_layout/overWorkPop.tpl");
				//-----------------------------------------------------------
			}else{

				//-----------------------------------------------------------
				$this->display("intranet/common_layout/overWorkPop.tpl");
				//-----------------------------------------------------------
			}
		}
		//=================================================================================



	}  //OverWorkPop End

	/* ------------------------------------------------------------------------------ */
	function EndWorkPop_old()	//페이지이동 : 업무종료 : 감리직원 대상
	{
		global	$CompanyKind; // 회사코드
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
		global  $sendDate;
		/* ----------------- */
		$connectFlag = ($_GET['connectFlag']==""?"":$_GET['connectFlag']); //웹브라우저를 통한 로그인 후, 업무종료, connectFlag=web
		/* ----------------- */
		$this->myinfo();

		//--------------------------------------------------------
		//개인별 탄력근무 관련 정보 start : 20201207 moon
		//--------------------------------------------------------
		$FlexibleWork_comment="";
		$FlexibleWorkYN="N";
		$FlexibleWork_array = FN_FlexibleWork_info('', $MemberNo);
		if($FlexibleWork_array[0][re_YN]=="Y"){
			$re_memberno    =  $FlexibleWork_array[0][re_memberno];
			$re_s_date      =  $FlexibleWork_array[0][re_s_date];
			$re_e_date      =  $FlexibleWork_array[0][re_e_date];
			$re_tardy_h     =  $FlexibleWork_array[0][re_tardy_h];
			$re_tardy_m     =  $FlexibleWork_array[0][re_tardy_m];
			$re_info        =  $FlexibleWork_array[0][re_info];		//코로나

			$FlexibleWorkYN="Y";
		}else{}
		if($FlexibleWorkYN=="Y"){
			if($re_tardy_m=="0"){
				$re_tardy_m="00";
			}
			$comp_time = $re_tardy_h.''.$re_tardy_m;
			$c_start_str = '';

			if($MemberNo=='M20310'){
				//echo $comp_time;

			}
			if( $comp_time=='730'){
				//7시30 출근 : 16시30 퇴근
				//연장근무 신청가능시간 : 18:00~20:00
				$c_start_str = "연장근무 시작시간 17:00 부터 ";
			}else if( $comp_time=='800' ){
				//8시00분 출근 : 17시00분 퇴근
				//연장근무 신청가능시간 : 17:30~20:00
				$c_start_str = "연장근무 시작시간 17:30 부터 ";
			}else if( $comp_time=='830'){
				//8시30 출근 : 17시30 퇴근
				//연장근무 신청가능시간 : 18:00~20:00
				$c_start_str = "연장근무 시작시간 18:00 부터 ";
			}else if( $comp_time=='930'){
				//9시30 출근 : 18시30 퇴근
				//연장근무 신청가능시간 : 19:00~20:30
				$c_start_str = "연장근무 시작시간 19:00 부터 ";
			}else if( $comp_time=='1000'){
				//10시 출근 : 19시 퇴근
				//연장근무 신청가능시간 : 19:30~21:00
				$c_start_str = "연장근무 시작시간 19:30 부터 ";
			}else{
				//18:30~20:00
				$c_start_str = "연장근무 시작시간  18:30 부터 ";
			}

			if((int)$re_tardy_h<10){
				$re_tardy_h= "0".$re_tardy_h;
			}
			if($re_tardy_m=="0"){
				$re_tardy_m="00";
			}

			$FlexibleWork_comment = "<< 탄력근무 적용기간 (".$re_s_date."~".$re_e_date.") >><br>";
			$FlexibleWork_comment.= " 업무시작시간 ".$re_tardy_h.":".$re_tardy_m." ,   ";
			$FlexibleWork_comment.= $c_start_str;

		}else{}
		$this->assign('FlexibleWork_comment',$FlexibleWork_comment);


		$this->assign('memberID',$MemberNo);
		$this->assign('connectFlag',$connectFlag);
		$this->assign('nowHour',$nowHour);
		$this->assign('nowMin',$nowMin);

		if(DevConfirm($MemberNo)){
			//echo $MemberNo.'<br><br>';
			$this->assign('devYN','Y');
		}

		//=================================================================================
		$this->display("intranet/common_layout/endWorkPop.tpl");
		//=================================================================================


	}  //EndWorkPop End
	/* ------------------------------------------------------------------------------ */
	function EndWorkPop()	//페이지이동 : 업무종료 : 일반직원 (감리직 제외)
	{
		global	$CompanyKind; // 회사코드
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
		global  $sendDate;
		/* ----------------- */
		$connectFlag = ($_GET['connectFlag']==""?"":$_GET['connectFlag']); //웹브라우저를 통한 로그인 후, 업무종료, connectFlag=web
		/* ----------------- */
		$this->myinfo();
		$this->assign('memberID',$MemberNo);
		$this->assign('connectFlag',$connectFlag);
		$this->assign('nowHour',$nowHour);
		$this->assign('nowMin',$nowMin);

		if(DevConfirm($MemberNo)){
			//echo $MemberNo.'<br><br>';
			$this->assign('devYN','Y');
		}

		//--------------------------------------------------------
		//개인별 탄력근무 관련 정보 start : 20201207 moon
		//--------------------------------------------------------
		$FlexibleWork_comment="";
		$FlexibleWorkYN="N";
		$FlexibleWork_array = FN_FlexibleWork_info('', $MemberNo);
		if($FlexibleWork_array[0][re_YN]=="Y"){
			$re_memberno    =  $FlexibleWork_array[0][re_memberno];
			$re_s_date      =  $FlexibleWork_array[0][re_s_date];
			$re_e_date      =  $FlexibleWork_array[0][re_e_date];
			$re_tardy_h     =  $FlexibleWork_array[0][re_tardy_h];
			$re_tardy_m     =  $FlexibleWork_array[0][re_tardy_m];
			$re_info        =  $FlexibleWork_array[0][re_info];		//코로나

			$FlexibleWorkYN="Y";
		}else{}
		if($FlexibleWorkYN=="Y"){
			if($re_tardy_m=="0"){
				$re_tardy_m="00";
			}
			$comp_time = $re_tardy_h.''.$re_tardy_m;
			$c_start_str = '';

			if($MemberNo=='M20310'){
				//echo $comp_time;

			}
			if( $comp_time=='730'){
				//7시30 출근 : 16시30 퇴근
				//연장근무 신청가능시간 : 18:00~20:00
				$c_start_str = "연장근무 시작시간 17:00 부터 ";
			}else if( $comp_time=='800' ){
				//8시00분 출근 : 17시00분 퇴근
				//연장근무 신청가능시간 : 17:30~20:00
				$c_start_str = "연장근무 시작시간 17:30 부터 ";
			}else if( $comp_time=='830'){
				//8시30 출근 : 17시30 퇴근
				//연장근무 신청가능시간 : 18:00~20:00
				$c_start_str = "연장근무 시작시간 18:00 부터 ";
			}else if( $comp_time=='930'){
				//9시30 출근 : 18시30 퇴근
				//연장근무 신청가능시간 : 19:00~20:30
				$c_start_str = "연장근무 시작시간 19:00 부터 ";
			}else if( $comp_time=='1000'){
				//10시 출근 : 19시 퇴근
				//연장근무 신청가능시간 : 19:30~21:00
				$c_start_str = "연장근무 시작시간 19:30 부터 ";
			}else{
				//18:30~20:00
				$c_start_str = "연장근무 시작시간  18:30 부터 ";
			}

			if((int)$re_tardy_h<10){
				$re_tardy_h= "0".$re_tardy_h;
			}
			if($re_tardy_m=="0"){
				$re_tardy_m="00";
			}

			$FlexibleWork_comment = "<< 탄력근무 적용기간 (".$re_s_date."~".$re_e_date.") >><br>";
			$FlexibleWork_comment.= " 업무시작시간 ".$re_tardy_h.":".$re_tardy_m." ,   ";
			$FlexibleWork_comment.= $c_start_str;

		}else{}
		$this->assign('FlexibleWork_comment',$FlexibleWork_comment);

		//--------------------------------------------------------
		//개인별 탄력근무 관련 정보 end
		//--------------------------------------------------------


		//=================================================================================
		$this->display("intranet/common_layout/endWorkPop_2.tpl");
		//=================================================================================


	}  //EndWorkPop End
	/* ------------------------------------------------------------------------------ */
	function EndWork()  //업무종료 DB실행
	{
		global	$CompanyKind; // 회사코드
		/* SET SESSION ----------------------- */
		global	$MemberNo;		//사원번호
		global	$korName;		//한글이름
		global	$RankCode;		//직급코드
		global	$GroupCode;		//부서코드
		global	$SortKey;		//직급+부서코드
		global	$EntryDate;		//입사일자
		global	$position;		//직위명
		global	$GroupName;		//부서명
		/* -----------------*/
		global	$date_today;	// 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1;	// 오늘날짜 년월일 시분 : yyyy-mm-dd +시, 분,
		global	$date_today2;	// $date_today1.":"."00"   오늘날짜	+ 시, 분, 00초
		global	$date_today3;	// $date_today." 00:00:00" 오늘날짜	+ 00시, 00분, 00초
		global	$nowYear;		// 오늘날짜 년          : yyyy
		global	$nowMonth;		// 오늘날짜 년월        : yyyy-mm
		global	$todayName;		//오늘의 요일(일:0, 월:1,화:2,수:3,목:4,금:5,토:6)
		/* ----------------------------------- */
		global	$MonthAgo1;
		global	$MonthAgo2;
		/* ----------------------------------- */
		global	$nowHour;		// 현재 시
		global	$nowMin;		// 현재 분
		global	$nowTime;		// 현재 시:분
		/* ----------------- */
		global  $db;
		/* ----------------- */
		 $y = substr($date_today, 0, 4);
		 $m = substr($date_today, 5, 2);
		 $d = substr($date_today, 8, 2);

		$yesterday = date("Y-m-d", mktime(0,0,0,$m,$d-1,$y)); // 전날 YYYY-MM-DD형식

		//echo "****************************<br>";
		//echo "yesterday=".$yesterday."<br>";
		//echo "date_today=".$date_today;

		$t_day = date("Y-m-d");
		$l_day = find_last($t_day);
		/* ----------------------------------- */
		//Login Log 남김
		//$user_ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];   /// remote ip 저장
		$user_ip = $_SERVER["REMOTE_ADDR"];
		/* ----------------------------------- */
		//Login Log 남김
		$cfile="../../sys/log/".date("Y-m")."_logoff_ok.txt";
		$exist = file_exists("$cfile");
		if($exist) {
			$fd=fopen($cfile,'r');
			$con=fread($fd,filesize($cfile));
			fclose($fd);
		}
		/* ----------------- */
		$fp=fopen($cfile,'w');
		$aa=date("Y-m-d H:i");
		$cond=$con.$aa." ".$korName."[".$MemberNo."] ".$user_ip." ".$power_control." ".$GroupCode."\n";
		fwrite($fp,$cond);
		fclose($fp);

		$re_EntryPCode   = "";
		$re_EntryJobCode = "";
		$re_EntryJob     = "";

		$sql11 = "	  SELECT							";
		$sql11 = $sql11."	max(EntryTime) cur_EntryTime	";
		$sql11 = $sql11."	FROM							";
		$sql11 = $sql11."	dallyproject_tbl				";
		$sql11 = $sql11."	WHERE							";
		$sql11 = $sql11."	MemberNo = '".$MemberNo."'	    ";

		$re11     = mysql_query($sql11,$db);
		$re_num11 = mysql_num_rows($re11);
		/* ----------------- */
		if($re_num11 != 0) {
			$re_cur_EntryTime = mysql_result($re11,0,"cur_EntryTime");
			$re_cur_EntryTimeYYYYMMDD = substr($re_cur_EntryTime,0,10); // YYYY-MM-DD

			$yesterday = date("Y-m-d", mktime(0,0,0,$m,$d-1,$y)); // 전날 YYYY-MM-DD형식
			$compareDate=$date_today; //default=오늘

			if($re_cur_EntryTimeYYYYMMDD==$date_today){
				$compareDate=$date_today;

			}else if($re_cur_EntryTimeYYYYMMDD==$yesterday){ //다음날 새벽까지 야근시 . 전날 날짜와 비교
				$compareDate=$yesterday;

			}else{
				///////////////
				//echo "3";  //업무 미시작 출장등 이후날짜 입력해 놓으므로 이부분 체크제외
				///////////////
			}

			/* ******************************************************************** */
			$sql = "	  SELECT									";
			$sql = $sql."	EntryPCode							";
			$sql = $sql."	,EntryJobCode						";
			$sql = $sql."	,EntryJob							";
			$sql = $sql."	,ReportingBoss					";
			$sql = $sql."	FROM								";
			$sql = $sql."	dallyproject_tbl					";
			$sql = $sql."	WHERE								";
			$sql = $sql."	MemberNo = '$MemberNo'				";
			$sql = $sql."	AND									";
			$sql = $sql."	EntryTime like '".$compareDate."%'	";
			//echo $sql."<br>";
			$re     = mysql_query($sql,$db);
			$re_num = mysql_num_rows($re);
			/* ----------------- */
			if($re_num != 0) {
				$re_EntryPCode = mysql_result($re,0,"EntryPCode");
				$re_EntryJobCode = mysql_result($re,0,"EntryJobCode");
				$re_EntryJob = mysql_result($re,0,"EntryJob");
				$re_ReportingBoss = mysql_result($re,0,"ReportingBoss");
				
				if($re_ReportingBoss!=""){
					$MemSQL = "SELECT CONCAT(a.korName,CONCAT(' ',(SELECT Name FROM systemconfig_tbl WHERE SysKey='PositionCode' AND Code=a.RankCode))) AS Upper_User
					FROM member_tbl a WHERE a.MemberNo='$re_ReportingBoss' AND a.WorkPosition!=9";
						
					$MemRe = mysql_query($MemSQL,$db);
						
					if(mysql_num_rows($MemRe)>0){
						$re_ReportingBossText = mysql_result($MemRe,0,"Upper_User");
					}
				}
			}else{
				///////////////
				echo "3";  //금일 업무시작 안한상태임
				///////////////
			}//if re_num End
		}//if

	/* ****************************************************************************************************************** */
		$re_EntryJob = str_replace("'","",$re_EntryJob);

		/* ----------------------------------- */
		//한맥ERP프로젝트 일원화 작업 : 181012
		$NewProjectCode = projectToColumn($EntryPCode,'NewProjectCode',DevConfirm($MemberNo));
		$NewProjectCode = $NewProjectCode ==""?"":$NewProjectCode;
		/* ----------------------------------- */

		/* 재석상태 관련 코드추가(2015-03-18) Start *********************************** */
		$value01 = $MemberNo;
		setAbsent($value01, '2', '', '', '');
		//$value01=사원번호, $value02=상태값(default:2:자리비움), $value03=코멘트
		//$value04=미지정(추후사용),$value05=미지정(추후사용)
		/* 재석상태 관련 코드추가 End *********************************** */
		$endWork_sql= " UPDATE DallyProject_tbl SET								";

		$endWork_sql= $endWork_sql."   LeavePCode	='".$re_EntryPCode."'		";
		$endWork_sql= $endWork_sql."  ,LeavePCode2	='".$NewProjectCode."'		";

		$endWork_sql= $endWork_sql."  ,LeaveJobCode	='".$re_EntryJobCode."'		";
		$endWork_sql= $endWork_sql."  ,LeaveJob	    ='".$re_EntryJob."'			";
		$endWork_sql= $endWork_sql."  ,LeaveTime	='".$date_today1."'			";
		$endWork_sql= $endWork_sql."  ,EndWorkIP	='".$user_ip."'				";
		$endWork_sql= $endWork_sql." WHERE										";
		$endWork_sql= $endWork_sql." MemberNo = '".$MemberNo."'					";
		$endWork_sql= $endWork_sql." and                          				";
		$endWork_sql= $endWork_sql." EntryTime like '".$compareDate."%' 		";
	////////////////////////
	mysql_query($endWork_sql);
	echo "1";
	////////////////////////
	}  //EndWork() End

	/* ------------------------------------------------------------------------------ */	
	//===============================================//
	//EndWork_new() - Entry/Leave변경사항
	//1. LeavePCode -> EntryPCode
	//2. LeavePCode2 -> EntryPCode2
	//3. LeaveJobCode -> EntryJobCode
	//4. LeaveJob -> EntryJob
	//==============================================//
	function EndWork_new()  //업무종료 DB실행
	{
		extract($_REQUEST);
		global	$CompanyKind; // 회사코드
		/* SET SESSION ----------------------- */
		global	$MemberNo;		//사원번호
		global	$korName;		//한글이름
		global	$RankCode;		//직급코드
		global	$GroupCode;		//부서코드
		global	$SortKey;		//직급+부서코드
		global	$EntryDate;		//입사일자
		global	$position;		//직위명
		global	$GroupName;		//부서명
		/* -----------------*/
		global	$date_today;	// 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1;	// 오늘날짜 년월일 시분 : yyyy-mm-dd +시, 분,
		global	$date_today2;	// $date_today1.":"."00"   오늘날짜	+ 시, 분, 00초
		global	$date_today3;	// $date_today." 00:00:00" 오늘날짜	+ 00시, 00분, 00초
		global	$nowYear;		// 오늘날짜 년          : yyyy
		global	$nowMonth;		// 오늘날짜 년월        : yyyy-mm
		global	$todayName;		//오늘의 요일(일:0, 월:1,화:2,수:3,목:4,금:5,토:6)
		/* ----------------------------------- */
		global	$MonthAgo1;
		global	$MonthAgo2;
		/* ----------------------------------- */
		global	$nowHour;		// 현재 시
		global	$nowMin;		// 현재 분
		global	$nowTime;		// 현재 시:분
		/* ----------------- */
		global  $db;
		/* ----------------- */

		$y = substr($date_today, 0, 4);
		$m = substr($date_today, 5, 2);
		$d = substr($date_today, 8, 2);

		$yesterday = date("Y-m-d", mktime(0,0,0,$m,$d-1,$y)); // 전날 YYYY-MM-DD형식

		//echo "****************************<br>";
		//echo "yesterday=".$yesterday."<br>";
		//echo "date_today=".$date_today;

		$t_day = date("Y-m-d");
		$l_day = find_last($t_day);
		/* ----------------------------------- */
		//Login Log 남김
		//$user_ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];   /// remote ip 저장
		$user_ip = $_SERVER["REMOTE_ADDR"];
		$browser=$_SERVER['HTTP_USER_AGENT'];
		$insql="insert into login_info_tbl (MemberNo,UseIP,LoginDate,BrowserLog,State) values ('$MemberNo','$user_ip',now(),'$browser','work_end' )";
		mysql_query($insql,$db);

		/* ----------------------------------- */
		//Login Log 남김
		/*
		$cfile="../../sys/log/".date("Y-m")."_logoff_ok.txt";
		$exist = file_exists("$cfile");
		if($exist) {
			$fd=fopen($cfile,'r');
			$con=fread($fd,filesize($cfile));
			fclose($fd);
		}
		$fp=fopen($cfile,'w');
		$aa=date("Y-m-d H:i");
		$cond=$con.$aa." ".$korName."[".$MemberNo."] ".$user_ip." ".$power_control." ".$GroupCode."\n";
		fwrite($fp,$cond);
		fclose($fp);
		*/

		$re_EntryPCode   = "";
		$re_EntryJobCode = "";
		$re_EntryJob     = "";

		$sql11 = "	  SELECT							";
		$sql11 = $sql11."	max(EntryTime) cur_EntryTime	";
		$sql11 = $sql11."	FROM							";
		$sql11 = $sql11."	dallyproject_tbl				";
		$sql11 = $sql11."	WHERE							";
		$sql11 = $sql11."	MemberNo = '".$MemberNo."'	    ";

		$re11     = mysql_query($sql11,$db);
		$re_num11 = mysql_num_rows($re11);
		/* ----------------- */
		if($re_num11 != 0) {
			$re_cur_EntryTime = mysql_result($re11,0,"cur_EntryTime");
			$re_cur_EntryTimeYYYYMMDD = substr($re_cur_EntryTime,0,10); // YYYY-MM-DD

			$yesterday = date("Y-m-d", mktime(0,0,0,$m,$d-1,$y)); // 전날 YYYY-MM-DD형식
			$compareDate=$date_today; //default=오늘

			if($re_cur_EntryTimeYYYYMMDD==$date_today){
				$compareDate=$date_today;

			}else if($re_cur_EntryTimeYYYYMMDD==$yesterday){ //다음날 새벽까지 야근시 . 전날 날짜와 비교
				$compareDate=$yesterday;

			}else{
				///////////////
				//echo "3";  //업무 미시작 출장등 이후날짜 입력해 놓으므로 이부분 체크제외
				///////////////
			}
			
			$re_ReportingBoss = "";//업무검토자 담는 변수 추가 -업무검토

			/* ******************************************************************** */
			$sql = "	  SELECT									";
			$sql = $sql."	EntryPCode							";
			$sql = $sql."	,EntryJobCode						";
			$sql = $sql."	,EntryJob							";
			$sql = $sql."	,ReportingBoss							";
			$sql = $sql."	FROM								";
			$sql = $sql."	dallyproject_tbl					";
			$sql = $sql."	WHERE								";
			$sql = $sql."	MemberNo = '$MemberNo'				";
			$sql = $sql."	AND									";
			$sql = $sql."	EntryTime like '".$compareDate."%'	";
			//echo $sql."<br>";
			$re     = mysql_query($sql,$db);
			$re_num = mysql_num_rows($re);
			/* ----------------- */
			if($re_num != 0) {
				$re_EntryPCode = mysql_result($re,0,"EntryPCode");
				$re_EntryJobCode = mysql_result($re,0,"EntryJobCode");
				$re_EntryJob = mysql_result($re,0,"EntryJob");
				$re_ReportingBoss = mysql_result($re,0,"ReportingBoss");
			}else{
				///////////////
				echo "3";  //금일 업무시작 안한상태임
				///////////////
			}//if re_num End
		}//if

		/* ****************************************************************************************************************** */
		$re_EntryJob = str_replace("'","",$re_EntryJob);

		/* 재석상태 관련 코드추가(2015-03-18) Start *********************************** */
		$value01 = $MemberNo;
		setAbsent($value01, '2', '', '', '');
		//$value01=사원번호, $value02=상태값(default:2:자리비움), $value03=코멘트
		//$value04=미지정(추후사용),$value05=미지정(추후사용)
		/* 재석상태 관련 코드추가 End *********************************** */

		/* ----------------------------------- */
		//한맥ERP프로젝트 일원화 작업 : 181012
		$NewProjectCode = projectToColumn($edit_p_code,'NewProjectCode',DevConfirm($MemberNo));
		$NewProjectCode = $NewProjectCode ==""?"":$NewProjectCode;
		/* ----------------------------------- */


		$endoldYN=$endoldYN==""?"N":$endoldYN;
		if($endoldYN=="Y"){
// 			$edit_main_p_code	= iconv('euc-kr', 'utf-8', $_REQUEST['edit_main_p_code']);
// 			$edit_main_sub_code	= iconv('euc-kr', 'utf-8', $_REQUEST['edit_main_sub_code']);
// 			$edit_main_p_name	= iconv('euc-kr', 'utf-8', $_REQUEST['edit_main_p_name']);
// 			$edit_main_content	= iconv('euc-kr', 'utf-8', $_REQUEST['edit_main_content']);

			$edit_main_p_code	= iconv('euc-kr', 'utf-8', $_REQUEST['edit_main_p_code']);
			$edit_main_sub_code	= iconv('euc-kr', 'utf-8', $_REQUEST['edit_main_sub_code']);
			$edit_main_p_name	= iconv('euc-kr', 'utf-8', $_REQUEST['edit_main_p_name']);
			$edit_main_content	= iconv('euc-kr', 'utf-8', $_REQUEST['edit_main_content']);

		}
		//$_REQUEST["reporting_boss_val"] 빈 값이 아니면 변수 담기 - 업무검토
		if($_REQUEST["reporting_boss_val"]!=""){
			$re_ReportingBoss=$_REQUEST["reporting_boss_val"];
		}


		$endWork_sql= " UPDATE DallyProject_tbl SET								";
/*
		$endWork_sql= $endWork_sql."   LeavePCode	='".$edit_p_code."'			";
		$endWork_sql= $endWork_sql."  ,LeavePCode2	='".$NewProjectCode."'		";

		$endWork_sql= $endWork_sql."  ,LeaveJobCode	='".$edit_sub_code."'		";
		$endWork_sql= $endWork_sql."  ,LeaveJob	    ='".$edit_content."'		";
*/
		$endWork_sql= $endWork_sql."  EntryPCode	='".$edit_p_code."'			";
		$endWork_sql= $endWork_sql."  ,EntryPCode2	='".$NewProjectCode."'			";
		$endWork_sql= $endWork_sql."  ,EntryJobCode	='".$edit_sub_code."'			";
		$endWork_sql= $endWork_sql."  ,EntryJob	='".$edit_content."'			";
		
		$endWork_sql= $endWork_sql."  ,LeaveTime	='".$date_today1."'			";
		$endWork_sql= $endWork_sql."  ,EndWorkIP	='".$user_ip."'				";
		$endWork_sql= $endWork_sql."  ,ReportingBoss	='".$re_ReportingBoss."'				";//ReportingBoss 저장 추가 -업무검토
		$endWork_sql= $endWork_sql." WHERE										";
		$endWork_sql= $endWork_sql." MemberNo = '".$MemberNo."'					";
		$endWork_sql= $endWork_sql." and                          				";
		$endWork_sql= $endWork_sql." EntryTime like '".$compareDate."%' 		";


		//$endoldYN=="Y"//감리현장인원 : member_tbl:office_type=10
		if($endoldYN=="Y"){
			//Login Log 남김
			$c_file="../log/".date("Y-m")."_logoff_query.txt";
			$exist = file_exists("$cfile");
			if($exist) {
				$fd=fopen($c_file,'r');
				$con=fread($fd,filesize($c_file));
				fclose($fd);
			}
			$fp=fopen($c_file,'w');
			$aa=date("Y-m-d H:i");

			$cond=$con.$aa." ".$korName."[".$MemberNo."] ".$user_ip." endoldYN:".$endoldYN." : ".$endWork_sql."\n";
			fwrite($fp,$cond);
			fclose($fp);
		}

			////////////////////////
			mysql_query($endWork_sql);
			echo "1";
			////////////////////////

	}  //EndWork_new() End
	
	function OverWorkEditPop()	//페이지이동 : 연장근무 수정
	{
		global  $db;
		global $CompanyKind;
		
		extract($_REQUEST);
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/* ------------------------------------------ */
		$sql =      " SELECT								";
		$sql = $sql."	LeaveTime		as e_LeaveTime 		";
		$sql = $sql."	,LeavePCode		as e_LeavePCode 	";
		$sql = $sql."	,LeavePCode2	as e_LeavePCode2	";
		$sql = $sql."	,LeaveJobCode	as e_LeaveJobCode	";
		$sql = $sql."	,LeaveJob		as e_LeaveJob		";
		$sql = $sql."	FROM								";
		$sql = $sql."	dallyproject_tbl					";
		$sql = $sql."	WHERE								";
		$sql = $sql."	MemberNo = '".$memberID."'			";
		$sql = $sql."	AND									";
		$sql = $sql."	EntryTime like '".$search_date."%'	";
		/* ------------------------------------------ */
		$re = mysql_query($sql);
		/* ------------------------------------------ */
		
		$d_LeaveTime = "";
		$d_LeavePCode = "";
		$d_LeavePCode2 = "";
		$d_LeaveJobCode = "";
		$d_LeaveJob = "";
		
		
		while($re_row=mysql_fetch_assoc($re)){
			
			$d_LeaveTime = $re_row["e_LeaveTime"];
			$d_LeavePCode = $re_row["e_LeavePCode"];
			$d_LeavePCode2 = $re_row["e_LeavePCode2"];
			$d_LeaveJobCode = $re_row["e_LeaveJobCode"];
			$d_LeaveJob = $re_row["e_LeaveJob"];
			
			$d_LeavePCode_edit = $d_LeavePCode;
			
			if(change_XXIS02($d_LeavePCode,$CompanyKind)){ // XX관련 코드 => 리턴값이 true				
				//$d_EntryPCode_edit = "XX"."-".$arrayStr2."-".$arrayStr3;
				$d_LeavePCode_edit = change_XXIS02($d_LeavePCode,$CompanyKind,"text");
			}
		}
		
		if(DevConfirm($memberID)){
			$sql2 =      " SELECT										";
			$sql2 = $sql2."	 ProjectCode	    as p_ProjectCode 		";
			$sql2 = $sql2."	,NewProjectCode	    as p_NewProjectCode 	";
			$sql2 = $sql2."	,ProjectViewCode	as p_ProjectViewCode 	";
			$sql2 = $sql2."	,ProjectNickname	as p_ProjectNickname  	";
			$sql2 = $sql2."	FROM										";
			$sql2 = $sql2."	Project_tbl							";
			$sql2 = $sql2."	WHERE										";
			//$sql2 = $sql2."	ProjectCode = '".$d_EntryPCode_edit."'		";
			$sql2 = $sql2."	ProjectCode LIKE '%".$d_LeavePCode_edit."'		";
		}else{
			$sql2 =      " SELECT										";
			$sql2 = $sql2."	 ProjectCode	    as p_ProjectCode 		";
			$sql2 = $sql2."	,NewProjectCode	    as p_NewProjectCode 	";
			$sql2 = $sql2."	,ProjectViewCode	as p_ProjectViewCode 	";
			$sql2 = $sql2."	,ProjectNickname	as p_ProjectNickname  	";
			$sql2 = $sql2."	FROM										";
			$sql2 = $sql2."	project_tbl									";
			$sql2 = $sql2."	WHERE										";
			$sql2 = $sql2."	ProjectCode = '".$d_LeavePCode_edit."'		";
		}
		
		/*-------------------------*/
		$re2 = mysql_query($sql2);
		$re_num2 = mysql_num_rows($re2);
		if($re_num2 != 0)
		{
			
			$p_ProjectCode 		= mysql_result($re2,0,"p_ProjectCode"); 			//프로젝트 코드
			$p_NewProjectCode 	= mysql_result($re2,0,"p_NewProjectCode"); 			//프로젝트 NEW코드
			$p_ProjectViewCode	= mysql_result($re2,0,"p_ProjectViewCode"); 		//프로젝트 뷰코드
			$p_ProjectNickname	= mysql_result($re2,0,"p_ProjectNickname"); 		//프로젝트 닉네임
			
		}else{
			$p_ProjectNickname	="";
		}
		
		
		/*-------------------------------------------------------------------------------*/
		$this->assign('CompanyKind',$CompanyKind);
		$this->assign('memberID',$memberID);
		$this->assign('search_date',$search_date);
		
		$this->assign('d_LeaveTime',$d_LeaveTime);
		$this->assign('d_LeavePCode',$d_LeavePCode);
		$this->assign('d_LeavePCode2',$d_LeavePCode2);
		$this->assign('d_LeaveJobCode',$d_LeaveJobCode);
		$this->assign('d_LeaveJob',$d_LeaveJob);
		$this->assign('d_ProjectCode',$p_ProjectCode);
		$this->assign('d_NewProjectCode',$p_NewProjectCode);
		$this->assign('d_ProjectViewCode',$p_ProjectViewCode);
		$this->assign('d_ProjectNickname',$p_ProjectNickname);
		
			
		$this->display("intranet/common_layout/overWorkEditPop.tpl");
		
		//=================================================================================
	}  //OverWorkEditPop
	/* ------------------------------------------------------------------------------ */
	/* ------------------------------------------------------------------------------ */
	function OverWorkEdit(){
		global  $db;
		global $CompanyKind;
		
		extract($_REQUEST);
		
			$dallyin = "
								 UPDATE dallyproject_tbl SET
									LeavePCode='".$p_code."'
									,LeavePCode2='".$p_code."'
									,LeaveJobCode='".$sub_code."'
									,LeaveJob='".$content_detail."'	
								WHERE
									MemberNo='".$memberID."'
								AND EntryTime like '".$date_today."%'
							";
			
			$re = mysql_query($dallyin);
			if($re){
				echo "1";	//실행성공
			}
			else{
				echo "2";
			}
			exit;
		//if End
	}//editWork End
	/* ------------------------------------------------------------------------------ */
	function LunchInfo()	  //점심식단 기본정보
	{
		global	$CompanyKind; // 회사코드
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
		global  $sendDate;
		/* ----------------- */
		$sql= "SELECT									";
		$sql= $sql."  L.menu_num   as lunch_menu_num	";		// num
		$sql= $sql." ,L.menu_day   as lunch_menu_day 	";		// 요일
		$sql= $sql." ,L.menu_main  as lunch_menu_main	";		// 메인메뉴
		$sql= $sql." ,L.menu_sub   as lunch_menu_sub 	";		// 서브메뉴
		$sql= $sql." ,L.menu_add   as lunch_menu_add 	";		// 기타추가 항목 필요시
		$sql= $sql." FROM								";
		$sql= $sql."      lunch_menu_tbl L				";
		$sql= $sql." WHERE								";
		$sql= $sql."	L.menu_num NOT IN ('0','6')		";
		$sql= $sql." ORDER BY L.menu_num asc			";
		/* ----------------- */
		$query_data = array();
		/*-----------------------------------------------------------------------*/
		/* 장헌인트라넷 점심메뉴 테이블 공동사용(파일테크,한맥)*/
		//$db_hostname01 ='192.168.2.250';
		$db_hostname01 ='192.168.10.6';
		$db_database01 ='hanmacerp';
		$db_username01 ='root';
		$db_password01 ='erp';
		/*-----------------------------------------------------------------------*/
		$db01	= mysql_connect($db_hostname01,$db_username01,$db_password01);
			if(!$db01) die ("Unable to connect to MySql : ".mysql_error());
		/*-----------------------------------------------------------------------*/
		mysql_select_db($db_database01);
		/*-----------------------------------------------------------------------*/
		mysql_set_charset("utf-8",$db01);
		mysql_query("set names utf8");
		$re = mysql_query($sql,$db01);
		/*-----------------------------------------------------------------------*/
		while($re_row = mysql_fetch_array($re))
		{
			array_push($query_data,$re_row);
		}
	mysql_close();
		/*-------------------------------------*/
		$this->assign('menu_data',$query_data);
		$this->assign('CompanyKind',$CompanyKind);
		/*-------------------------------------*/
	}  //LunchInfo() End
	/* ------------------------------------------------------------------------------ */

	function LunchPop()	  //점심식단 LIST팝업
	{
		global	$CompanyKind; // 회사코드
		global	$MemberNo;	  // 사원번호
		/* ----------------- */
		$this->assign('memberID',$MemberNo);
		$this->LunchInfo();
		/* ----------------- */
		//점심식단 : 쓰기권한
		$PersonAuthority=new PersonAuthority();
		if($PersonAuthority->GetInfo($MemberNo,'총무')){
			$this->assign('Auth',true);
		}else{
			$this->assign('Auth',false);
		}
		/* --------------------------------------------- */
		$this->assign('CompanyKind',$CompanyKind);
		$this->display("intranet/common_contents/work_lunch/lunchPop.tpl");
		/* ----------------- */
	}  //LunchPop() End
	/* ------------------------------------------------------------------------------ */
	function LunchEditPop()//페이지이동 : 점심식단 수정(관리자)
	{
		global	$CompanyKind; // 회사코드
		global	$MemberNo;	  // 사원번호
		/* ----------------- */
		$this->assign('memberID',$MemberNo);
		$this->assign('CompanyKind',$CompanyKind);
		/* --------------------------------------------- */
		$this->LunchInfo();
		$this->display("intranet/common_contents/work_lunch/lunchEditPop.tpl");
		/* ----------------- */
	}  //LunchEditPop() End

	/* ------------------------------------------------------------------------------ */
	function LunchEdit()  //점심식단 수정 DB실행
	{
		global	$CompanyKind; // 회사코드
		global	$MemberNo;	  // 사원번호
		global	$korName;	  // 한글이름
		global	$GroupCode;	  // 부서
		global	$RankCode;	  // 직급
		global	$SortKey;	  // 직급+부서
		global	$date_today;  // 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1; // 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$nowYear;     // 오늘날짜 년          : yyyy
		global	$nowMonth;    // 오늘날짜 년월        : yyyy-mm
		global	$nowHour;	  // 현재 시
		global	$nowMin;	  // 현재 분
		global	$nowTime;	  // 현재 시:분
		/* ----------------- */
		global  $db;
		global  $sendDate;
		/* ----------------- */
		$edit_lunch_menu_num  = "";
		$edit_lunch_menu_main = "";
		$edit_lunch_menu_sub  = "";
		/* ----------------- */
		for($i=1;$i<6;$i++){
			$edit_lunch_menu_num[$i]	= ($_POST['edit_lunch_menu_num'.$i]==""?"":$_POST['edit_lunch_menu_num'.$i]);	//요일넘버(월:1~금:5)
			$edit_lunch_menu_main[$i]	= ($_POST['edit_lunch_menu_main'.$i]==""?" ":$_POST['edit_lunch_menu_main'.$i]);	//메인메뉴
			$edit_lunch_menu_sub[$i]	= ($_POST['edit_lunch_menu_sub'.$i]==""?" ":$_POST['edit_lunch_menu_sub'.$i]);	//서브메뉴
		}//for End
		/* ***************************************************************************** */
		//점심식단 : 업데이트
		for($j=1;$j<6;$j++){
			if( $edit_lunch_menu_main[$j] != ""){
			$lunch_sql= " UPDATE lunch_menu_tbl SET									";
			$lunch_sql= $lunch_sql."  menu_main	='".$edit_lunch_menu_main[$j]."'	";
			$lunch_sql= $lunch_sql." ,menu_sub	='".$edit_lunch_menu_sub[$j]."'		";
			$lunch_sql= $lunch_sql." WHERE											";
			$lunch_sql= $lunch_sql." menu_num = '".$edit_lunch_menu_num[$j]."'		";
		/*-----------------------------------------------------------------------*/
		/* 장헌인트라넷 점심메뉴 테이블 공동사용(파일테크,한맥)*/
		//$db_hostname01 ='192.168.2.250';
		$db_hostname01 ='192.168.10.6';
		$db_database01 ='hanmacerp';
		$db_username01 ='root';
		$db_password01 ='erp';
		/*-----------------------------------------------------------------------*/
		$db01	= mysql_connect($db_hostname01,$db_username01,$db_password01);
			if(!$db01) die ("Unable to connect to MySql : ".mysql_error());
		/*-----------------------------------------------------------------------*/
		mysql_select_db($db_database01);
		/*-----------------------------------------------------------------------*/
		mysql_set_charset("utf-8",$db01);
		mysql_query("set names utf8");
		/*-----------------------------------------------------------------------*/
			//mysql_query($lunch_sql);
			mysql_query($lunch_sql,$db01);
			mysql_close();
			} //if End
		}//for End
		/* ***************************************************************************** */
	echo "1";
	}  //LunchEdit() End

	/* ------------------------------------------------------------------------------ */
	function CheckPwMainPage()// 개인정보로 가는 비밀번호 확인 페이지
	{
		global	$CompanyKind; // 회사코드
		global	$MemberNo;	  // 사원번호
		$this->assign('MemberNo',$MemberNo);
		$this->assign('CompanyKind',$CompanyKind);
		$this->display("intranet/common_layout/checkPwMain.tpl");
	}  //CheckPwMainPage() End
	/* ------------------------------------------------------------------------------ */
	function SignPop()	//결재 건수 팝업
	{
		$get_memberID	=	$_GET['memberID'];
		$param01	=	$_GET['param01'];
		$param02	=	$_GET['param02'];
		$param03	=	$_GET['param03'];
		$param04	=	$_GET['param04'];
		$param05	=	$_GET['param05'];
		$param06	=	$_GET['param06'];
		$openkind	=	$_GET['openkind'];

		/* ----------------- */
		global	$CompanyKind; // 회사코드
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
		global  $db;
		/* ----------------- */
		$this->assign('memberID',$get_memberID);
		$this->assign('MemberNo',$MemberNo);
		$this->assign('CompanyKind',$CompanyKind);
		$this->assign('date_today',$date_today);
		/* ----------------- */
		$this->assign('param01',$param01);//결재할 문서
		$this->assign('param02',$param02);//기안한 문서
		$this->assign('param03',$param03);//부결된 문서
		$this->assign('param04',$param04);//전표 문서
		$this->assign('param05',$param05);//ERP문서
		$this->assign('param06',$param06);//대여신청건수
		$this->assign('openkind',$openkind);//팝업여는위치 null:main , 1:일정시간check

		$this->display("intranet/common_layout/signPop.tpl");
	}  //SignPop End

	/* ------------------------------------------------------------------------------ */
	function myinfo()	//기본정보
	{
		global	$CompanyKind; // 회사코드
		global $db;
		global $MemberNo;
		$sql= " SELECT				";
		$sql= $sql."	 a.MemberNo			as a_MemberNo		";//사원번호
		$sql= $sql."	,a.Pasword			as a_Pasword		";//비밀번호
		$sql= $sql."	,a.korName			as a_korName		";//한글이름
		$sql= $sql."	,a.RankCode			as a_RankCode		";//랭크코드
		$sql= $sql."	,a.GroupCode		as a_GroupCode		";//그룹코드
		$sql= $sql."	,a.WorkPosition		as a_WorkPosition	";//워크포지션
		$sql= $sql."	,a.chiName			as a_chiName		";//한자이름
		$sql= $sql."	,a.engName			as a_engName		";//영어이름
		$sql= $sql."	,a.Degree			as a_Degree			";//학력
		$sql= $sql."	,a.Technical		as a_Technical		";//기술등급
		$sql= $sql."	,a.ExtNo			as a_ExtNo			";//
		$sql= $sql."	,a.EntryDate		as a_EntryDate		";//입사일자
		$sql= $sql."	,a.LeaveDate		as a_LeaveDate		";//퇴사일자
		$sql= $sql."	,a.JuminNo			as a_JuminNo		";//주민등록번호
		$sql= $sql."	,a.Phone			as a_Phone			";//전화번호
		$sql= $sql."	,a.Mobile			as a_Mobile			";//핸드폰번호
		$sql= $sql."	,a.eMail			as a_eMail			";//이메일주소
		$sql= $sql."	,a.OrignAddress		as a_OrignAddress	";//본적
		$sql= $sql."	,a.Address			as a_Address		";//주소
		$sql= $sql."	,a.Author			as a_Author			";//
		$sql= $sql."	,a.Certificate		as a_Certificate	";//
		$sql= $sql."	,a.Meritorious		as a_Meritorious	";//
		$sql= $sql."	,a.Disabled			as a_Disabled		";//
		$sql= $sql."	,a.UpdateDate		as a_UpdateDate		";//등록일자
		$sql= $sql."	,a.UpdateUser		as a_UpdateUser		";//등록DEVICE
		$sql= $sql."	,a.Show_Insa		as a_Show_Insa		";//
		$sql= $sql."	,a.RegStDate		as a_RegStDate		";//
		$sql= $sql."	,a.RegEdDate		as a_RegEdDate		";//
		$sql= $sql."	,a.Engineer			as a_Engineer		";//
		$sql= $sql."	,a.Company			as a_Company		";	//
		$sql= $sql."	,a.EntryType		as a_EntryType		";//
		$sql= $sql."	,a.LeaveReason		as a_LeaveReason	";	//
		$sql= $sql."	,a.married			as a_married		";	//혼인여부
		$sql= $sql."	,a.SysKey			as a_SysKey			";//
		$sql= $sql."	,a.Code				as a_Code			";//
		$sql= $sql."	,a.Name				as a_Position		";	//직위
		$sql= $sql."	,a.CodeORName		as a_CodeORName		";//
		$sql= $sql."	,a.Description		as a_Description	";	//
		$sql= $sql."	,a.Note				as a_Note			";//
		$sql= $sql."	,a.orderno			as a_orderno		";	//
		$sql= $sql."	,b.Name				as b_GroupName		";//부서명
		$sql= $sql." FROM																	";
		$sql= $sql." (                                                                 		";
		$sql= $sql." 	select * from                                                 		";
		$sql= $sql." 	( select * from member_tbl where MemberNo = '".$MemberNo."' )a1     ";
		$sql= $sql." 	 left JOIN                                                 		    ";
		$sql= $sql." 	( select * from systemconfig_tbl where SysKey='PositionCode' )a2	";
		$sql= $sql." 	 on a1.RankCode = a2.code                                  		    ";
		$sql= $sql." ) a left JOIN                                                     		";
		$sql= $sql." ( select * from systemconfig_tbl where SysKey='GroupCode'  )b          ";
		$sql= $sql."  on a.GroupCode = b.code                                      		    ";
		//============================================================================
		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		if($re_num != 0){
			$Name		= mysql_result($re,0,"a_korName");
			$GroupName	= mysql_result($re,0,"b_GroupName");
			$Position	= mysql_result($re,0,"a_Position");
			$ExtNo		= mysql_result($re,0,"a_ExtNo");
		} //if End
		// 사진
		$src_photo = "../erpphoto/".$MemberNo.".jpg";
		$src_photo1 = "../erpphoto/".$MemberNo.".gif";
		if(file_exists($src_photo)) {
			$MemberPic=$src_photo;
		}else if(file_exists($src_photo1)){
			$MemberPic=$src_photo2;
		}else{
			$MemberPic="../erpphoto/noimage.gif";
		}//if End
		/* ----------------- */
		$this->assign('CompanyKind',$CompanyKind);
		$this->assign('MemberNo',$MemberNo);
		$this->assign('memberID',$MemberNo);
		$this->assign('Name',$Name);
		$this->assign('korName',$Name);
		$this->assign('GroupName',$GroupName);
		$this->assign('Position',$Position);
		$this->assign('MemberPic',$MemberPic);
		$this->assign('ExtNo',$ExtNo);
	}//myinfo() End

	/* ------------------------------------------------------------------------------ */
	function GoOutHistoryList()  //외출/출장현황보기 리스트
	{
		global	$CompanyKind; //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		global	$MemberNo;	  // 사원번호
		global	$korName;	  // 한글이름
		global	$GroupCode;	  // 부서
		global	$RankCode;	  // 직급
		global	$SortKey;	  // 직급+부서
		global	$date_today;  // 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1; // 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$nowYear;     // 오늘날짜 년          : yyyy
		global	$nowMonth;    // 오늘날짜 년월        : yyyy-mm
		global	$nowHour;	  // 현재 시
		global	$nowMin;	  // 현재 분
		global	$nowTime;	  // 현재 시:분
		/* ----------------- */
		global  $db;
		global  $sendDate;
		/* ----------------- */
		/*
		$get_MemberNo	= $_GET['memberID'];
		$post_MemberNo	= $_POST['memberID'];
		$post_MemberNo2	= $_POST['ajax_memberID'];
		*/



		$get_MemberNo		= $_REQUEST['memberID'];
		$post_MemberNo		= $_REQUEST['memberID'];
		$post_MemberNo2	= $_REQUEST['ajax_memberID'];

		$connectFlag			= $_REQUEST['connectFlag'];

		$MemberNo		= ($MemberNo==""?$get_MemberNo:$MemberNo);
		$MemberNo		= ($MemberNo==""?$post_MemberNo:$MemberNo);
		$MemberNo		= ($MemberNo==""?$post_MemberNo2:$MemberNo);
		/* ---------------------------------------- */
		$this->assign('CompanyKind',$CompanyKind);
		/* ---------------------------------------- */
		$this->assign('MemberNo',$MemberNo);
		$this->assign('RankCode',$RankCode); //출장신청 버튼 클릭시 (임원과 직원구분)
		/* ---------------------------------------- */
		$this->assign('date_today',$date_today);
		$this->assign('GroupCode',(int)$GroupCode);
		/* ---------------------------------------- */
		$this->assign('connectFlag',$connectFlag);
		/* ---------------------------------------- */

		$this->assign('REMOTE_ADDR',$_SERVER["REMOTE_ADDR"]);


		$this->display("intranet/common_layout/goOutTripList.tpl");
		/* ---------------------------------------- */
	}  //GoOutHistoryList End

	/* ------------------------------------------------------------------------------ */
	function GoOutHistoryListPerson()  //개인별 월간 외출/출장현황보기 리스트
	{
		global	$CompanyKind; //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		global	$MemberNo;	  // 사원번호
		global	$korName;	  // 한글이름
		global	$GroupCode;	  // 부서
		global	$RankCode;	  // 직급
		global	$SortKey;	  // 직급+부서
		global	$date_today;  // 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1; // 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$nowYear;     // 오늘날짜 년          : yyyy
		global	$nowMonth;    // 오늘날짜 년월        : yyyy-mm
		global	$nowHour;	  // 현재 시
		global	$nowMin;	  // 현재 분
		global	$nowTime;	  // 현재 시:분
		/* ----------------- */
		global  $db;
		global  $sendDate;
		/* ----------------- */
		$ajax_memberID		= ($_POST['ajax_memberID']==""?"":$_POST['ajax_memberID']);			//프로젝트코드
		$ajax_outDate     	= ($_POST['ajax_outDate']==""?$date_today:$_POST['ajax_outDate']);			//조회일자
		$ajax_groupCode     = ($_POST['ajax_groupCode']==""?"":$_POST['ajax_groupCode']);		//조회일자
		$ajax_radio_kind01  = ($_POST['ajax_radio_kind01']==""?"":$_POST['ajax_radio_kind01']);	//전체보기(1)외출(2),출장(3)
		/* ---------------------------------------- */
			$insert_query = "";
		if($ajax_radio_kind01=='1'){		//외근&출장 : 1
			$insert_query = " AND OP.o_change in('1','2')";
		}else if($ajax_radio_kind01=='2'){	//외근		: 2
			$insert_query = " AND OP.o_change in('1')";
		}else if($ajax_radio_kind01=='3'){	//출장		: 3
			$insert_query = " AND OP.o_change in('2')";
		}else{
			$insert_query = " AND OP.o_change in('1','2')"; //외근&출장
		}//if End
		/* ---------------------------------------- */
		$sql01=		   " SELECT															";
		$sql01= $sql01."	 OP.no							as op_no					";
		$sql01= $sql01."	,OP.o_area						as op_o_area				";
		$sql01= $sql01."	,OP.o_itinerary					as op_o_itinerary			";
		$sql01= $sql01."	,OP.o_group						as op_o_group				";
		$sql01= $sql01."	,OP.o_name						as op_o_name				";
		$sql01= $sql01."	,OP.o_start						as op_o_start				";
		$sql01= $sql01."	,substring(OP.o_start,6,2)		as op_o_startMonth			";//두자리 해당월
		$sql01= $sql01."	,substring(OP.o_start,9,2)		as op_o_startDay			";//두자리 해당일
		$sql01= $sql01."	,substring(OP.o_start,12,2)		as op_o_startHour			";//두자리 시간
		$sql01= $sql01."	,substring(OP.o_start,15,2)		as op_o_startMin			";//두자리 분
		$sql01= $sql01."	,DATE_FORMAT(OP.o_start, '%Y-%m-%d') as op_o_startYMD		";//YYYY-MM-DD
		$sql01= $sql01."	,OP.o_end						as op_o_end					";
		$sql01= $sql01."	,substring(OP.o_end,6,2)		as op_o_endMonth			";//두자리 해당월
		$sql01= $sql01."	,substring(OP.o_end,9,2)		as op_o_endDay				";//두자리 해당일
		$sql01= $sql01."	,substring(OP.o_end,12,2)		as op_o_endHour				";//두자리 시간
		$sql01= $sql01."	,substring(OP.o_end,15,2)		as op_o_endMin				";//두자리 분
		$sql01= $sql01."	,OP.o_object					as op_o_object				";
		$sql01= $sql01."	,OP.o_traffic					as op_o_traffic				";
		$sql01= $sql01."	,OP.o_note						as op_o_note				";
		$sql01= $sql01."	,OP.o_passwd					as op_o_passwd				";
		$sql01= $sql01."	,OP.ProjectCode					as op_ProjectCode			";
		$sql01= $sql01."	,OP.contents					as op_contents				";
		$sql01= $sql01."	,OP.memberno					as op_memberno				";
		$sql01= $sql01."	,OP.o_change					as op_o_change				";
		$sql01= $sql01."	,S.name							as s_groupName				";
		$sql01= $sql01." FROM															";
		$sql01= $sql01."      official_plan_tbl OP										";
		$sql01= $sql01."     ,( select CAST(code AS UNSIGNED) code, name				";
		$sql01= $sql01."			from systemconfig_tbl where SysKey='GroupCode'  ) S	";
		$sql01= $sql01."  WHERE															";
		$sql01= $sql01."      S.code=OP.o_group											";
		$sql01= $sql01."      AND														";
		$sql01= $sql01."      DATE_FORMAT(OP.o_start, '%Y-%m')<= '".$nowMonth."'		";
		$sql01= $sql01."      AND														";
		$sql01= $sql01."      DATE_FORMAT(OP.o_end, '%Y-%m')>= '".$nowMonth."'			";
		$sql01= $sql01."      AND														";
		$sql01= $sql01."      OP.memberno like '".$MemberNo."%'							";
		$sql01= $sql01.$insert_query;//외출,출장 구분
		$sql01= $sql01." ORDER BY OP.o_group, OP.o_start desc, op_memberno ASC			";
		/* ---------------------------------------- */
		$data = array();
		/* ---------------------------------------- */
		$op_memberno_Name = MemberNo2Name($MemberNo);
		/* ---------------------------------------- */
		$re01 = mysql_query($sql01,$db);
		/*-----------------------------*/
		$count01 = mysql_num_rows($re01);
		/*-----------------------------*/
		if($count01>0){//결과0
			$i=0;
			while($re_row01 = mysql_fetch_array($re01)){
				/* 외근 출장 구분----------------------------------- */
				if($re_row01[op_o_change]==1){
					$re_row01[op_o_changeName]="외근";
				}else if($re_row01[op_o_change]==2){
					$re_row01[op_o_changeName]="출장";
				}//if End
				/* 이름에 내이름 포함여부----------------------------------- */
				$temp_op_o_name = $re_row01[op_o_name];
				$viewKind="";
					if(strpos($temp_op_o_name, $op_memberno_Name) !== false){
						$viewKind="Y";
					}else{
						$viewKind="N";
					}//if End
				$re_row01[viewKind] = $viewKind;//C이면 임원
				/* 직급코드:임원구분---------------------------------------- */
				$ch_RankCode  = substr($RankCode,0,1); //임원구분값 직급코드 앞자리가 대문자="C"
				$re_row01[ch_RankCode] = $ch_RankCode;//C이면 임원
				/*임원가능 메뉴: official_plan_tbl 출장계획 수정삭제----------- */
				$op_o_startYMD = $re_row01[op_o_startYMD];
				if($date_today<=$op_o_startYMD ){// official_plan_tbl 출장계획 수정&삭제 가능 <<-복명서 작성전임
					$re_row01[updateMode]="Y";
				}else{
					$re_row01[updateMode]="N";
				}//if End
				/* ---------------------------------------- */
				$moveLink = "";
				$moveStr  = "";
				if($re_row01[op_o_change]==2){//출장
					$op_no = $re_row01[op_no];
					$whereQuery=" WHERE no='".$op_no."'	";//
					$result_num01 = tableRowCount("official_cost_tbl",$whereQuery); //$mCode: 테이블명,$mCode2:쿼리
					$result_num02 = tableRowCount("official_gasbill_tbl",$whereQuery); //$mCode: 테이블명,$mCode2:쿼리
					/*----------------------------------------*/
					if($result_num01!="N" || $result_num02!="N") { //결과값 존재 : 수정
						$moveLink = "/intranet/sys/controller/businesstrip_controller.php?ActionMode=insert_page&mode=mod&memberID=".$MemberNo."&no=".$op_no;
						$moveStr = "복명서보기";
					}else{//결과값 없음 : 등록
						$moveLink = "/intranet/sys/controller/businesstrip_controller.php?ActionMode=insert_page&mode=add&memberID=".$MemberNo."&no=".$op_no;
						$moveStr  = "복명서작성";
					} //if End
				}else{
					$moveLink = "";
					$moveStr  = "";
				}//if End
				/*----------------------------------------*/
				$re_row01[op_moveLink]=$moveLink;
				$re_row01[op_moveStr] =$moveStr;
				/*----------------------------------------*/
			$data['data'][$i] = array(
									  'op_no'=>$re_row01[op_no]
									, 'op_o_group'=>$re_row01[op_o_group]
									, 's_groupName'=>$re_row01[s_groupName]
									, 'op_o_change'=>$re_row01[op_o_change]
									, 'op_o_changeName'=>$re_row01[op_o_changeName]
									, 'op_o_name'=>$re_row01[op_o_name]
									, 'op_o_itinerary'=>$re_row01[op_o_itinerary]
									, 'op_o_object'=>$re_row01[op_o_object]
									, 'op_o_startMonth'=>$re_row01[op_o_startMonth]
									, 'op_o_startDay'=>$re_row01[op_o_startDay]
									, 'op_o_startHour'=>$re_row01[op_o_startHour]
									, 'op_o_startMin'=>$re_row01[op_o_startMin]
									, 'op_o_endMonth'=>$re_row01[op_o_endMonth]
									, 'op_o_endDay'=>$re_row01[op_o_endDay]
									, 'op_o_endHour'=>$re_row01[op_o_endHour]
									, 'op_o_endMin'=>$re_row01[op_o_endMin]
									, 'viewKind'=>$re_row01[viewKind]
									, 'ch_RankCode'=>$re_row01[ch_RankCode]
									, 'updateMode'=>$re_row01[updateMode]
									, 'op_moveLink'=>$re_row01[op_moveLink]
									, 'op_moveStr'=>$re_row01[op_moveStr]
									);
			$i++;
			}//while End
			/*-----------------------------*/
		}else{
			//결과X
		}//if End
		echo json_encode($data); //php배열을 json 형태로 변경해주는 php 내장함수 입니다.
	}  //GoOutHistoryListPerson End

	/* ------------------------------------------------------------------------------ */
	function DaySearch()
	{
		global	$CompanyKind; //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		global	$MemberNo;	  // 사원번호
		global	$korName;	  // 한글이름
		global	$GroupCode;	  // 부서
		global	$RankCode;	  // 직급
		global	$SortKey;	  // 직급+부서
		global	$date_today;  // 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1; // 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$nowYear;     // 오늘날짜 년          : yyyy
		global	$nowMonth;    // 오늘날짜 년월        : yyyy-mm
		global	$nowHour;	  // 현재 시
		global	$nowMin;	  // 현재 분
		global	$nowTime;	  // 현재 시:분
		/* ----------------- */
		global  $db;
		global  $sendDate;
		/* ----------------- */
		//$ajax_memberID		= ($_POST['ajax_memberID']==""?"":$_POST['ajax_memberID']);
		$ajax_outDate     	= ($_POST['ajax_outDate']==""?$date_today:$_POST['ajax_outDate']);			//조회일자 YYYY-MM-DD
		$ajax_outDateYYYYMM = substr($ajax_outDate,0,7); //경유/휴가 검색용 YYYY-MM
		$ajax_groupCode     = ($_POST['ajax_groupCode']==""?"":$_POST['ajax_groupCode']);		//
		$ajax_radio_kind01  = ($_POST['ajax_radio_kind01']==""?"1":$_POST['ajax_radio_kind01']);	//전체보기(1)외출(2),출장(3), 경유휴가(4)
		$ajax_person_kind   = ($_POST['ajax_person_kind']==""?"":$_POST['ajax_person_kind']);	//
		/* ---------------------------------------- */
		$insert_query11="";
		$insert_query22="";
		$insert_query33="";
			if($ajax_person_kind=="Y"){//개인월간 구분(Y/N)
				/* -------------------------------------------------------------------------------------------------- */
				$insert_query11= $insert_query11."				(													";
				$insert_query11= $insert_query11."				start_time LIKE '".$ajax_outDateYYYYMM."%'			";
				$insert_query11= $insert_query11."				OR													";
				$insert_query11= $insert_query11."				end_time LIKE '".$ajax_outDateYYYYMM."%'			";
				$insert_query11= $insert_query11."				)													";
				/* -------------------------------------------------------------------------------------------------- */
				$insert_query22=				 " AND																";
				$insert_query22= $insert_query22." a.MemberNo like '".$MemberNo."%'									";
				/* -------------------------------------------------------------------------------------------------- */
				$insert_query33=				 "  AND																";
				$insert_query33= $insert_query33."  OP.memberno like '".$MemberNo."%'								";
				$insert_query33= $insert_query33."	AND																";
				$insert_query33= $insert_query33."	(																";
				$insert_query33= $insert_query33."	DATE_FORMAT(OP.o_start, '%Y-%m') LIKE '".$ajax_outDateYYYYMM."%'";
				$insert_query33= $insert_query33."	OR																";
				$insert_query33= $insert_query33."	DATE_FORMAT(OP.o_end, '%Y-%m') LIKE '".$ajax_outDateYYYYMM."%'	";
				$insert_query33= $insert_query33."	)																";
				/* -------------------------------------------------------------------------------------------------- */
			}else if($ajax_person_kind=="N"){
				$insert_query11= $insert_query11."				(													";
				$insert_query11= $insert_query11."				start_time <= '".$ajax_outDate."'					";
				$insert_query11= $insert_query11."				AND													";
				$insert_query11= $insert_query11."				end_time >= '".$ajax_outDate."'						";
				$insert_query11= $insert_query11."				)													";
				/* -------------------------------------------------------------------------------------------------- */
				$insert_query22=" ";
				/* -------------------------------------------------------------------------------------------------- */
				$insert_query33= $insert_query33."      AND															";
				$insert_query33= $insert_query33."      DATE_FORMAT(OP.o_start, '%Y-%m-%d')<= '".$ajax_outDate."'	";
				$insert_query33= $insert_query33."      AND															";
				$insert_query33= $insert_query33."      DATE_FORMAT(OP.o_end, '%Y-%m-%d')>= '".$ajax_outDate."'		";
				/* -------------------------------------------------------------------------------------------------- */
			}else{
				$insert_query11= $insert_query11."				(													";
				$insert_query11= $insert_query11."				start_time <= '".$ajax_outDate."'					";
				$insert_query11= $insert_query11."				AND													";
				$insert_query11= $insert_query11."				end_time >= '".$ajax_outDate."'						";
				$insert_query11= $insert_query11."				)													";
				/* ---------------------------------------- ---------------------------------------------------------- */
				$insert_query22=" ";
				/* ---------------------------------------- ---------------------------------------------------------- */
				$insert_query33= $insert_query33."      AND															";
				$insert_query33= $insert_query33."      DATE_FORMAT(OP.o_start, '%Y-%m-%d')<= '".$ajax_outDate."'	";
				$insert_query33= $insert_query33."      AND															";
				$insert_query33= $insert_query33."      DATE_FORMAT(OP.o_end, '%Y-%m-%d')>= '".$ajax_outDate."'		";
				/* ---------------------------------------- ---------------------------------------------------------- */
			}
		/* ---------------------------------------- */
		if($ajax_radio_kind01=='4'){//경유휴가(4)
			$sql01=		   " SELECT																	";
			$sql01= $sql01."		a.num								as a_num					";
			$sql01= $sql01."		,a.MemberNo							as a_MemberNo				";
			$sql01= $sql01."		,a.GroupCode						as a_GroupCode				";
			$sql01= $sql01."		,a.state							as a_state					";
			$sql01= $sql01."		,a.start_time						as a_start_time				";
			$sql01= $sql01."		,substring(a.start_time,6,2)		as a_startMonth				";//두자리 해당월
			$sql01= $sql01."		,substring(a.start_time,9,2)		as a_startDay				";//두자리 해당일
			$sql01= $sql01."	    ,DATE_FORMAT(a.start_time, '%d')	as a_start_timeDD			";
			$sql01= $sql01."	    ,DAYOFWEEK(a.start_time)            as a_dayName				";
			$sql01= $sql01."		,a.end_time							as a_end_time				";
			$sql01= $sql01."		,substring(a.end_time,6,2)			as a_endMonth				";//두자리 해당월
			$sql01= $sql01."		,substring(a.end_time,9,2)			as a_endDay					";//두자리 해당일
			$sql01= $sql01."		,a.ProjectCode						as a_ProjectCode			";
			$sql01= $sql01."		,a.note								as a_note					";
			$sql01= $sql01."		,a.sub_code							as a_sub_code				";
			$sql01= $sql01."		,b.Name								as b_StateName				";
			$sql01= $sql01."	    ,S.name								as s_groupName				";
			$sql01= $sql01."		FROM															";
			$sql01= $sql01."		(																";
			$sql01= $sql01."		SELECT * FROM userstate_tbl										";
			$sql01= $sql01."			WHERE														";
		$sql01= $sql01.$insert_query11;
//			$sql01= $sql01."				(														";
//			$sql01= $sql01."				start_time <= '".$ajax_outDate."'						";
//			$sql01= $sql01."				AND														";
//			$sql01= $sql01."				end_time = '".$ajax_outDate."'							";
//			$sql01= $sql01."				)														";
			$sql01= $sql01."																		";
			$sql01= $sql01."		) a left JOIN													";
			$sql01= $sql01."	(																	";
			$sql01= $sql01."		SELECT * from systemconfig_tbl where SysKey = 'UserStateCode'	";
			$sql01= $sql01." )b on a.state = b.Code													";
			$sql01= $sql01."     ,( select CAST(code AS UNSIGNED) code, name						";
			$sql01= $sql01."			from systemconfig_tbl where SysKey='GroupCode'  ) S			";
			$sql01= $sql01."  WHERE																	";
			$sql01= $sql01."      S.code=a.GroupCode												";
			$sql01= $sql01."      AND																";
			$sql01= $sql01."      a.state in('1','2','30','31')												";
			$sql01= $sql01.$insert_query22;
			$sql01= $sql01." ORDER BY																";
			$sql01= $sql01."	a.state desc														";
			$sql01= $sql01."	,a.GroupCode asc													";
			$sql01= $sql01."	,a.start_time desc													";
			/* ---------------------------------------- */
			$data = array();
			/* ---------------------------------------- */
			$data['sql01'] = array('vacationQuery'=>$sql01); //jquery ajax 쿼리 확인용(Fiddler툴을 사용시 확인하기 편리.)
			/* ---------------------------------------- */
			$re01 = mysql_query($sql01,$db);
			/*-----------------------------*/
			$count01 = mysql_num_rows($re01);
			/*-----------------------------*/
			if($count01>0){//결과0
				/*-----------------------------*/
				$i=0;
				while($re_row01 = mysql_fetch_array($re01)){
					/* 휴가(1) 경유(2) 구분----------------------------------- */
					if($re_row01[a_state]==1){
						$re_row01[a_stateName]="휴가";
						$re_row01[op_o_change]="3"; //휴가 경유

					}else if($re_row01[a_state]==2){
						$re_row01[a_stateName]="경유";
						$re_row01[op_o_change]="4"; //휴가 경유
					}else if($re_row01[a_state]==30){
						$re_row01[a_stateName]="오전반차";
						$re_row01[op_o_change]="4"; //휴가 경유
					}else if($re_row01[a_state]==31){
						$re_row01[a_stateName]="오후반차";
						$re_row01[op_o_change]="4"; //휴가 경유

					}//if End
					/* 이름에 내이름 포함여부----------------------------------- */
					$temp_a_MemberNo = $re_row01[a_MemberNo];
					$viewKind="";
						if($temp_a_MemberNo==$MemberNo){
							$viewKind="Y";
						}else{
							$viewKind="N";
						}//if End
					/*-----------------------------*/
					$re_row01[a_MemberNo_Name] = MemberNo2Name($temp_a_MemberNo);
					/*-----------------------------*/
					$re_row01[viewKind] = $viewKind;//C이면 임원
					/* 직급코드:임원구분---------------------------------------- */
					$whereQuery99="MemberNo='".$temp_a_MemberNo."'";
					$re_RankCode= tableToColumn2("member_tbl","RankCode",$whereQuery99);
					$ch_RankCode  = substr($re_RankCode,0,1); //임원구분값 직급코드 앞자리가 대문자="C"
					$re_row01[ch_RankCode] = $ch_RankCode;//C이면 임원
					/*임원가능 메뉴: official_plan_tbl 출장계획 수정삭제----------- */
					$op_o_startYMD = $re_row01[a_start_time];
					if($date_today<=$op_o_startYMD ){// official_plan_tbl 출장계획 수정&삭제 가능 <<-복명서 작성전임
						$re_row01[updateMode]="Y";
					}else{
						$re_row01[updateMode]="N";
					}//if End
					/* ---------------------------------------- */
					$moveLink = "";
					$moveStr  = "";
					/*----------------------------------------*/
					$re_row01[op_moveLink]=$moveLink;
					$re_row01[op_moveStr] =$moveStr;
					/*----------------------------------------*/
					$re_contentsShort="";
					$s_contentsShort1_len = mb_strlen($re_row01[a_note],"UTF-8");
					if($s_contentsShort1_len>27){
						$re_contentsShort = mb_substr($re_row01[a_note],0,27,"UTF-8")."..";
					}else{
						$re_contentsShort = $re_row01[a_note];
					}
				$data['data'][$i] = array(
										  'op_no'=>$re_row01[a_num]//<--
										, 'op_o_group'=>$re_row01[updateMode]//<--
										, 'op_o_group'=>$re_row01[a_GroupCode]//<--
										, 's_groupName'=>$re_row01[s_groupName]//<--
										, 'op_o_change'=>$re_row01[op_o_change]//<--
										, 'op_o_changeName'=>$re_row01[a_stateName]//<--
										, 'op_o_name'=>$re_row01[a_MemberNo_Name]//<--
										, 'op_o_itinerary'=>$re_row01[a_stateName]//<--목적지
										, 'op_o_object'=>$re_contentsShort//<--사유
										, 'op_o_startMonth'=>$re_row01[a_startMonth]//<--
										, 'op_o_startDay'=>$re_row01[a_startDay]//<--
										, 'op_o_startHour'=>"00"//<--
										, 'op_o_startMin'=>"00"//<--
										, 'op_o_endMonth'=>$re_row01[a_endMonth]//<--
										, 'op_o_endDay'=>$re_row01[a_endDay]//<--
										, 'op_o_endHour'=>"00"//<--
										, 'op_o_endMin'=>"00"//<--
										, 'viewKind'=>$re_row01[viewKind]
										, 'ch_RankCode'=>$re_row01[ch_RankCode]
										, 'updateMode'=>$re_row01[updateMode]
										, 'op_moveLink'=>$re_row01[op_moveLink]
										, 'op_moveStr'=>$re_row01[op_moveStr]
										, 'op_CompanyKind'=>$CompanyKind
										);
				$i++;
				}//while End
				/*-----------------------------*/
			}else{
				//결과X
			}//if End
		}else{//전체보기(1)외출(2),출장(3)
			/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
			/* ----------------- */
				$insert_query = "";
			if($ajax_radio_kind01=='1'){		//외근&출장 : 1
				$insert_query = " AND OP.o_change in('1','2') ";
			}else if($ajax_radio_kind01=='2'){	//외근		: 2
				$insert_query = " AND OP.o_change in('1') ";
			}else if($ajax_radio_kind01=='3'){	//출장		: 3
				$insert_query = " AND OP.o_change in('2') ";
			}else{
				$insert_query = " AND OP.o_change in('1','2') "; //외근&출장
			}//if End
			/* ---------------------------------------- */
			$sql01=		   " SELECT															";
			$sql01= $sql01."	 OP.no							as op_no					";
			$sql01= $sql01."	,OP.o_area						as op_o_area				";
			$sql01= $sql01."	,OP.o_itinerary					as op_o_itinerary			";
			$sql01= $sql01."	,OP.o_group						as op_o_group				";
			$sql01= $sql01."	,OP.o_name						as op_o_name				";
			$sql01= $sql01."	,OP.o_start						as op_o_start				";
			$sql01= $sql01."	,substring(OP.o_start,6,2)		as op_o_startMonth			";//두자리 해당월
			$sql01= $sql01."	,substring(OP.o_start,9,2)		as op_o_startDay			";//두자리 해당일
			$sql01= $sql01."	,substring(OP.o_start,12,2)		as op_o_startHour			";//두자리 시간
			$sql01= $sql01."	,substring(OP.o_start,15,2)		as op_o_startMin			";//두자리 분
			$sql01= $sql01."	,DATE_FORMAT(OP.o_start, '%Y-%m-%d') as op_o_startYMD	";//YYYY-MM-DD
			$sql01= $sql01."	,OP.o_end						as op_o_end					";
			$sql01= $sql01."	,substring(OP.o_end,6,2)		as op_o_endMonth			";//두자리 해당월
			$sql01= $sql01."	,substring(OP.o_end,9,2)		as op_o_endDay				";//두자리 해당일
			$sql01= $sql01."	,substring(OP.o_end,12,2)		as op_o_endHour				";//두자리 시간
			$sql01= $sql01."	,substring(OP.o_end,15,2)		as op_o_endMin				";//두자리 분
			$sql01= $sql01."	,OP.o_object					as op_o_object				";
			$sql01= $sql01."	,OP.o_traffic					as op_o_traffic				";
			$sql01= $sql01."	,OP.o_note						as op_o_note				";
			$sql01= $sql01."	,OP.o_passwd					as op_o_passwd				";
			$sql01= $sql01."	,OP.ProjectCode					as op_ProjectCode			";

			$sql01= $sql01."	,OP.NewProjectCode				as op_NewProjectCode		";

			$sql01= $sql01."	,OP.contents					as op_contents				";
			$sql01= $sql01."	,OP.memberno					as op_memberno				";
			$sql01= $sql01."	,OP.o_change					as op_o_change				";
			$sql01= $sql01."	,S.name							as s_groupName				";
			$sql01= $sql01." FROM															";
			$sql01= $sql01."      official_plan_tbl OP										";
			$sql01= $sql01."     ,( select CAST(code AS UNSIGNED) code, name				";
			$sql01= $sql01."			from systemconfig_tbl where SysKey='GroupCode'  ) S	";
			$sql01= $sql01."  WHERE															";
			$sql01= $sql01."      S.code=OP.o_group											";
/*			$sql01= $sql01."      AND														";
*			$sql01= $sql01."      DATE_FORMAT(OP.o_start, '%Y-%m-%d')<= '".$ajax_outDate."'	";
*			$sql01= $sql01."      AND														";
*			$sql01= $sql01."      DATE_FORMAT(OP.o_end, '%Y-%m-%d')>= '".$ajax_outDate."'	";
*/
			$sql01= $sql01.$insert_query;//외출,출장 구분
			$sql01= $sql01.$insert_query33;
			$sql01= $sql01." ORDER BY OP.o_change asc, OP.o_group, OP.o_start desc, op_memberno ASC			";
			/* ---------------------------------------- */
			$data = array();
			/* ---------------------------------------- */
			$data['sql02'] = array('tripQuery'=>$sql01); //jquery ajax 쿼리 확인용(Fiddler툴을 사용시 확인하기 편리.)
			/* ---------------------------------------- */
			$op_memberno_Name = MemberNo2Name($MemberNo);
			/* ---------------------------------------- */
			$re01 = mysql_query($sql01,$db);
			/*-----------------------------*/
			$count01 = mysql_num_rows($re01);
			/*-----------------------------*/
			if($count01>0){//결과0
				/*-----------------------------*/
				$i=0;
				while($re_row01 = mysql_fetch_array($re01)){
					/* 외근 출장 구분----------------------------------- */
					if($re_row01[op_o_change]==1){
						$re_row01[op_o_changeName]="외근";
					}else if($re_row01[op_o_change]==2){
						$re_row01[op_o_changeName]="출장";
					}//if End

					/* 이름에 내이름 포함여부----------------------------------- */
					$temp_op_o_name = $re_row01[op_o_name];
					$viewKind="";
						if(strpos($temp_op_o_name, $op_memberno_Name) !== false){
							$viewKind="Y";
						}else{
							$viewKind="N";
						}//if End
					$re_row01[viewKind] = $viewKind;//C이면 임원

					/* 직급코드:임원구분---------------------------------------- */
					$ch_RankCode  = substr($RankCode,0,1); //임원구분값 직급코드 앞자리가 대문자="C"
					$re_row01[ch_RankCode] = $ch_RankCode;//C이면 임원

					/*임원가능 메뉴: official_plan_tbl 출장계획 수정삭제----------- */
					$op_o_startYMD = $re_row01[op_o_startYMD];
					if($date_today<=$op_o_startYMD ){// official_plan_tbl 출장계획 수정&삭제 가능 <<-복명서 작성전임
						$re_row01[updateMode]="Y";
					}else{
						$re_row01[updateMode]="N";
					}//if End
					/* ---------------------------------------- */
					$moveLink = "";
					$moveStr  = "";
					if($re_row01[op_o_change]==2){//출장
						if($CompanyKind=="HANM" ||$CompanyKind=="BARO"){
							$op_no = $re_row01[op_no];
							$whereQuery=" WHERE no='".$op_no."'	";//
							$result_num01 = tableRowCount("official_cost_tbl",$whereQuery); //$mCode: 테이블명,$mCode2:쿼리
							$result_num02 = tableRowCount("official_gasbill_tbl",$whereQuery); //$mCode: 테이블명,$mCode2:쿼리
							/*----------------------------------------*/
							if($result_num01!="N" || $result_num02!="N") { //결과값 존재 : 수정
								$moveLink = "/intranet/sys/controller/businesstrip_controller.php?ActionMode=insert_page&mode=mod&memberID=".$MemberNo."&no=".$op_no;
								$moveStr = "복명서보기";
							}else{//결과값 없음 : 등록
								$moveLink = "/intranet/sys/controller/businesstrip_controller.php?ActionMode=insert_page&mode=add&memberID=".$MemberNo."&no=".$op_no;
								$moveStr  = "복명서작성";
							} //if End
						}else{//장헌,파일(official_gasbill_tbl 테이블 없음)
							$op_no = $re_row01[op_no];
							$whereQuery=" WHERE no='".$op_no."'	";//
							$result_num01 = tableRowCount("official_cost_tbl",$whereQuery); //$mCode: 테이블명,$mCode2:쿼리
							/*----------------------------------------*/
							if($result_num01!="N") { //결과값 존재 : 수정
								$moveLink = "/intranet/sys/controller/businesstrip_controller.php?ActionMode=insert_page&mode=mod&memberID=".$MemberNo."&no=".$op_no;
								$moveStr = "복명서보기";
							}else{//결과값 없음 : 등록
								$moveLink = "/intranet/sys/controller/businesstrip_controller.php?ActionMode=insert_page&mode=add&memberID=".$MemberNo."&no=".$op_no;
								$moveStr  = "복명서작성";
							} //if End
						}
					}else{
						$moveLink = "";
						$moveStr  = "";
					}//if End
					/*----------------------------------------*/
					$re_row01[op_moveLink]=$moveLink;
					$re_row01[op_moveStr] =$moveStr;
					/*----------------------------------------*/
					$ch_op_o_object = $re_row01[op_o_object];/*사유*/
					$ch_op_o_object = str_replace('[PC켜둠]','',$ch_op_o_object);
					/*----------------------------------------*/
				$data['data'][$i] = array(
										  'op_no'=>$re_row01[op_no]
										, 'op_o_group'=>$re_row01[op_o_group]
										, 's_groupName'=>$re_row01[s_groupName]
										, 'op_o_change'=>$re_row01[op_o_change]
										, 'op_o_changeName'=>$re_row01[op_o_changeName]
										, 'op_o_name'=>$re_row01[op_o_name]
										, 'op_o_itinerary'=>$re_row01[op_o_itinerary]//목적지
										/*, 'op_o_object'=>$re_row01[op_o_object]//사유*/
										, 'op_o_object'=>$ch_op_o_object
										, 'op_o_startMonth'=>$re_row01[op_o_startMonth]
										, 'op_o_startDay'=>$re_row01[op_o_startDay]
										, 'op_o_startHour'=>$re_row01[op_o_startHour]
										, 'op_o_startMin'=>$re_row01[op_o_startMin]
										, 'op_o_endMonth'=>$re_row01[op_o_endMonth]
										, 'op_o_endDay'=>$re_row01[op_o_endDay]
										, 'op_o_endHour'=>$re_row01[op_o_endHour]
										, 'op_o_endMin'=>$re_row01[op_o_endMin]
										, 'viewKind'=>$re_row01[viewKind]
										, 'ch_RankCode'=>$re_row01[ch_RankCode]
										, 'updateMode'=>$re_row01[updateMode]
										, 'op_moveLink'=>$re_row01[op_moveLink]
										, 'op_moveStr'=>$re_row01[op_moveStr]
										, 'op_CompanyKind'=>$CompanyKind

										, 'op_o_note'=>$re_row01[op_o_note]
										, 'op_NewProjectCode'=>$re_row01[op_NewProjectCode]
										, 'op_memberno'=>$re_row01[op_memberno]

										);
				$i++;
				}//while End
				/*-----------------------------*/
			}else{
				//결과X
			}//if End
			/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
		}//if End
		echo json_encode($data); //php배열을 json 형태로 변경해주는 php 내장함수 입니다.
	}// DaySearch End
	/* ------------------------------------------------------------------------------ */
	function GoTripPopUpdate()	//페이지이동 : 출장수정
	{
		global  $CompanyKind;//회사코드 찾기 return 4자리 영어대문자 회사코드
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
		$memberID	= ($_GET['memberID']==""?$MemberNo:$_GET['memberID']);		//사원번호
		$content_id	= $_GET['content_id'];	//pk
		/*-----------------------------*/
		$sql=		   " SELECT															";
		$sql= $sql."	 OP.no							as op_no					";
		$sql= $sql."	,OP.o_area						as op_o_area				";
		$sql= $sql."	,OP.o_itinerary					as op_o_itinerary			";
		$sql= $sql."	,OP.o_group						as op_o_group				";
		$sql= $sql."	,OP.o_name						as op_o_name				";
		$sql= $sql."	,OP.o_start						as op_o_start				";
		$sql= $sql."	,DATE_FORMAT(OP.o_start, '%Y-%m-%d') as op_o_startYMD		";//YYYY-MM-DD
		$sql= $sql."	,OP.o_end						as op_o_end					";
		$sql= $sql."	,OP.o_object					as op_o_object				";
		$sql= $sql."	,OP.o_traffic					as op_o_traffic				";
		$sql= $sql."	,OP.o_note						as op_o_note				";
		$sql= $sql."	,OP.o_passwd					as op_o_passwd				";
		$sql= $sql."	,OP.ProjectCode					as op_ProjectCode			";
		$sql= $sql."	,OP.contents					as op_contents				";
		$sql= $sql."	,OP.memberno					as op_memberno				";
		$sql= $sql."	,OP.o_change					as op_o_change				";
		$sql= $sql." FROM															";
		$sql= $sql."      official_plan_tbl OP										";
		$sql= $sql."  WHERE															";
		$sql= $sql."      OP.no = ".$content_id."									";
		/*-----------------------------*/
		$whereAuery=" WHERE no = ".$content_id." ";
		/*-----------------------------*/
			$re = mysql_query($sql,$db);
		/*pk---------------*/
		$op_no = mysql_result($re,0,"op_no");
		$this->assign('op_no',$op_no);

		/*출장인원 이름-----------------------------*/
		$op_o_nameArray=array();
		$op_o_name= mysql_result($re,0,"op_o_name");
		$divfile1 = explode(",",$op_o_name);
		$divnum1  = count($divfile1);
		if($divnum1<1){$divnum1  =$divnum1+1;}
		for($i=0;$i<$divnum1+1;$i++){
		$op_o_nameArray[$i] = $divfile1[$i];
		}//for End
		$this->assign('op_o_nameArray',$op_o_nameArray);
		/*사원번호-----------------------------*/
		$op_membernoArray=array();
		$op_memberno= mysql_result($re,0,"op_memberno");
		$divfile2 = explode(",",$op_memberno);
		$divnum2  = count($divfile2);
		if($divnum2<1){$divnum2  =$divnum2+1;}
		for($i=0;$i<$divnum2;$i++){
		$op_membernoArray[$i] = $divfile2[$i];
		}//for End
		/* --------------------------------------------- */
		$this->assign('CompanyKind',$CompanyKind);//회사코드
		$this->assign('op_membernoArray',$op_membernoArray);
		/*-----------------------------*/
		$op_ProjectCode	= mysql_result($re,0,"op_ProjectCode");
		$this->assign('op_ProjectCode',$op_ProjectCode);
		/*프로젝트명 약칭---------------*/
		//mCode:테이블명, mCode2:프로젝트코드,mCode3:리턴받고자 하는 컬럼명, whereQuery:조건문자열(AND aaa='bbb' ... )
		$op_ProjectNickname = tableToColumn("project_tbl",$op_ProjectCode,"ProjectNickname",$whereQuery);
		$this->assign('op_ProjectNickname',$op_ProjectNickname);
		/*방문지역---------------*/
		$op_o_area		= mysql_result($re,0,"op_o_area");
		$this->assign('op_o_area',$op_o_area);
		/*방문처---------------*/
		$op_o_itinerary	= mysql_result($re,0,"op_o_itinerary");
		$this->assign('op_o_itinerary',$op_o_itinerary);
		/*방문사유---------------*/
		$op_o_object	= mysql_result($re,0,"op_o_object");
		$this->assign('op_o_object',$op_o_object);
		/*시작일---------------*/
		$op_o_start		= mysql_result($re,0,"op_o_start");
		$this->assign('op_o_start',$op_o_start);
		/*종료일---------------*/
		$op_o_end	= mysql_result($re,0,"op_o_end");
		$this->assign('op_o_end',$op_o_end);
		$this->assign('date_today',$date_today);
		$this->myinfo();
		$this->display("intranet/common_layout/goTripPopUpdate.tpl");

	}  //GoTripPop End
/***************************************************************************************** */

	function Process_data()	////개인 당일 외근/출장 취소
	{
		extract($_REQUEST);
		global $CompanyKind;//회사코드 찾기 return 4자리 영어대문자 회사코드
		global $MemberNo;
	//	global $memberID;
		global $korName;
		global $db;
		//----------------------------------------------------------
// 		memberID	: memberID
// 		$p_crud	: p_crud
// 		$p_type	: p_type
// 		$p_param1: p_param1 : 날짜 : yyyy-mm-dd
// 		$p_param2: p_param2 : 사원번호
// 		$p_param3: p_param3 : 1(외근), 2(출장)
// 		$p_param4: p_param4 : NewProjectCode : [출장]취소처리에서만 사용
// 		$p_param5: p_param5 :
		//----------------------------------------------------------
		$update_query = "";
		$delete_query = "";
		//----------------------------------------------------------
		if($p_type=="1"){
			//외근
			//-----------------------------------------------------
			$delete_query = "
				DELETE FROM official_plan_tbl
				WHERE
				memberno = '$p_param2'
				AND o_note = '$p_param1'
				AND o_change = '$p_param3'
				";
// 			echo $delete_query;
// 			exit();
 			$result = mysql_query($delete_query);
 			//-----------------------------------------------------
			if($result){
				echo "1";	//삭제 성공
			}else{
				echo "2";	//삭제 실패
			}
			//-----------------------------------------------------
		}else if($p_type=="2"){
			//출장
			//-----------------------------------------------------
			$memberno_arr = explode(",",$p_param2);
			$update_memberno = "";
			$update_o_name   = "";
			//---------------------------------------
			$cnt = count($memberno_arr);
			$pro_cnt = 0;
			//---------------------------------------
			if($cnt>0){
				for($i=0; $i<$cnt;$i++){
					if($memberno_arr[$i]!=""){
						if($memberID!=$memberno_arr[$i]){
							$update_memberno .= $memberno_arr[$i].',';
							$update_o_name   .= MemberNo2Name($memberno_arr[$i]).',';
						}
						$pro_cnt++;
					}
				}//for
			}//if
			//---------------------------------------
			$update_memberno = FN_cutLastWord($update_memberno);//마지막문자열(쉼표)제거
			$update_o_name   = FN_cutLastWord($update_o_name);//마지막문자열(쉼표)제거
			//---------------------------------------
			if($pro_cnt==1){
				//출장자가 1명일 경우
				$update_query = "
					DELETE FROM official_plan_tbl
					WHERE
						memberno = '$p_param2'
						AND o_note = '$p_param1'
						AND NewProjectCode='$p_param4'
						AND o_change = '$p_param3'
					";
			}else{
				$update_query = "
					UPDATE official_plan_tbl SET
						memberno='$update_memberno'
						, o_name='$update_o_name'
					WHERE
						memberno = '$p_param2'
						AND o_note = '$p_param1'
						AND NewProjectCode='$p_param4'
					";
			}
			//---------------------------------------
			$delete_query = "
				DELETE FROM userstate_tbl
				WHERE
					MemberNo='$memberID'
					AND start_time='$p_param1'
					AND NewProjectCode='$p_param4'
				";

// 			if($memberID=="B14306"){
// 				echo $update_query."<BR>";
// 				echo $delete_query;
// 				exit();
// 			}

			//----------------------------------------------------------------------------
			$result = mysql_query($update_query);
			if($result){
				$result2 = mysql_query($delete_query);
				if($result2){
					echo "1";	//삭제 성공
				}else{
					echo "2";	//삭제 실패
				}
			}else{
				echo "2";	//삭제 실패
			}
			//----------------------------------------------------------------------------
		}

	}//Process_data




	function DeleteDB_officialPlan()	//삭제DB : 출장삭제:임원
	{
		global $CompanyKind;//회사코드 찾기 return 4자리 영어대문자 회사코드
		global $MemberNo;
		global $memberID;
		global $korName;
		global $db;
		/*-----------------------------*/
		$memberID	= ($_POST['memberID']==""?$memberID:$_POST['memberID']);
		/*-----------------------------*/
		$content_id	= (int)$_POST['content_id'];	//컨텐츠PK


			/* ------------------------------------------------------------------------------ */
			/*시작일2 종료일2 (시분초 포함)----------------*/
			$_ajax_trip_start		= $ajax_trip_start." 00:00:00";
			$_ajax_trip_end			= $ajax_trip_end." 23:59:00";
			/*---------------------------------------------------*/
			$sql=		   " SELECT														";
			$sql= $sql."	 OP.no							as op_no					";
			$sql= $sql."	,OP.o_itinerary					as op_o_itinerary			";
			$sql= $sql."	,OP.o_start						as op_o_start				";
			$sql= $sql."	,OP.o_end						as op_o_end					";
			$sql= $sql."	,DATE_FORMAT(OP.o_start, '%Y-%m-%d') as op_o_startYMD		";//YYYY-MM-DD
			$sql= $sql."	,DATE_FORMAT(OP.o_end, '%Y-%m-%d')   as op_o_endYMD			";//YYYY-MM-DD
			$sql= $sql."	,OP.ProjectCode					as op_ProjectCode			";
			$sql= $sql."	,OP.memberno					as op_memberno				";
			$sql= $sql." FROM															";
			$sql= $sql."      official_plan_tbl OP										";
			$sql= $sql."  WHERE															";
			$sql= $sql."      OP.no = '".$content_id."'									";
			/*---------------------------------------------------*/
			$re = mysql_query($sql,$db);
			/*---------------------------------------------------*/
			$op_o_startYMD	= mysql_result($re,0,"op_o_startYMD");
			$op_o_endYMD	= mysql_result($re,0,"op_o_endYMD");
			$op_o_itinerary	= mysql_result($re,0,"op_o_itinerary");
			$op_ProjectCode	= mysql_result($re,0,"op_ProjectCode");
			$op_memberno	= mysql_result($re,0,"op_memberno");
			/*---------------------------------------------------*/

			$memberno_arr = explode(",",$op_memberno);
			$cnt = count($memberno_arr);
			if($cnt>0){

				for($i=0; $i<$cnt;$i++){
					if($memberno_arr[$i]!=""){
						$delete_query = "DELETE FROM userstate_tbl WHERE state='3' AND start_time='".$op_o_startYMD."' AND end_time='".$op_o_endYMD."' AND projectCode='".$op_ProjectCode."' AND  note='".$op_o_itinerary."' AND  memberno='".$memberno_arr[$i]."'   ";//
						$result = mysql_query($delete_query);
					}

				}//for
			}
			/* ------------------------------------------------------------------------------ */

		/*-----------------------------*/
		$delete_query = "DELETE FROM official_plan_tbl WHERE no = '".$content_id."'";
		/***********************************************************************************/
		$result = mysql_query($delete_query);
		if($result){
			echo "1";	//삭제 성공
		}else{
			echo "2";	//삭제 실패
		}
		/*-----------------------------*/
	}  //DeleteDB() End

	/* ------------------------------------------------------------------------------ */
	function UpdateDB_officialPlan()	//출장신청 DB수정 실행
	{
		global $CompanyKind;//회사코드 찾기 return 4자리 영어대문자 회사코드
		global	$MemberNo;	  // 사원번호
		global	$korName;	  // 한글이름
		global	$GroupCode;	  // 부서
		$GroupCode =(int)$GroupCode;
		global	$RankCode;	  // 직급
		global	$SortKey;	  // 직급+부서
		/* ----------------- */
		global	$date_today;  // 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1; // date("Y-m-d H:i");			// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$date_today2; // $date_today1.":"."00";		// 오늘날짜
		global	$date_today3; // $date_today." 00:00:00";
		global	$nowYear;     // 오늘날짜 년          : yyyy
		global	$nowMonth;    // 오늘날짜 년월        : yyyy-mm
		global	$nowHour;	  // 현재 시
		global	$nowMin;	  // 현재 분
		global	$nowTime;	  // 현재 시:분
		/* ----------------- */
		global  $db;
		global  $sendDate;
		/*출장인원정보--------*/
		$withMemId   = "";
		$withMemName = "";
		/* ----------------- */
		$content_id		= ($_POST['content_id']==""?"":$_POST['content_id']);		//pk
		/* ----------------- */
		for($i=1;$i<5;$i++){																			//출장 : 출장인원(1~4명까지)
			$ajax_trip_memberId[$i]   = ($_POST['trip_memberId'.$i]==""?"":$_POST['trip_memberId'.$i]);
			$ajax_trip_memberName[$i] = ($_POST['trip_memberName'.$i]==""?"":$_POST['trip_memberName'.$i]);
			if($ajax_trip_memberId[$i] != ""){
				$withMemId   = $withMemId.$ajax_trip_memberId[$i].",";
				$withMemName = $withMemName.$ajax_trip_memberName[$i].",";
			}
		}//for End
		/*-------------------*/
		$len          = mb_strlen($withMemId,"UTF-8");
		$withMemId    = mb_substr($withMemId,0,$len-1,"UTF-8"); //마지막 쉼표제거 ","
		/*-------------------*/
		$len2         = mb_strlen($withMemName,"UTF-8");
		$withMemName  = mb_substr($withMemName,0,$len2-1,"UTF-8"); //마지막 쉼표제거 ","
		/*------------------------------------*/
		$ajax_trip_groupName		= ($_POST['trip_groupName']==""?"":$_POST['trip_groupName']);		//출장 : 부서명(한글)
		$ajax_trip_p_code			= ($_POST['trip_p_code']==""?"":$_POST['trip_p_code']);				//출장 : 프로젝트코드
		$ajax_trip_p_name			= ($_POST['trip_p_name']==""?"":$_POST['trip_p_name']);				//출장 : 약칭
		$ajax_trip_area				= ($_POST['trip_area']==""?"":$_POST['trip_area']);					//출장 : 방문지역
		$ajax_trip_destination		= ($_POST['trip_destination']==""?"":$_POST['trip_destination']);	//출장 : 목적지
		$ajax_trip_reason			= ($_POST['trip_reason']==""?"":$_POST['trip_reason']);				//출장 : 출장목적
		$ajax_trip_start			= ($_POST['trip_start']==""?"":$_POST['trip_start']);				//출장 : 시작일자
		$ajax_trip_end				= ($_POST['trip_end']==""?"":$_POST['trip_end']);					//출장 : 종료일자
		$ajax_trip_goout_today		= ($_POST['goout_today']==""?"":$_POST['goout_today']);				//출장 : 신청일자
		for($i=1;$i<5;$i++){																			//출장 : 출장인원(1~4명까지)
			$ajax_trip_memberId[$i] = ($_POST['trip_memberId'.$i]==""?"":$_POST['trip_memberId'.$i]);
		}//for End
		/*-------------------*/
		/*시작일2 종료일2 (시분초 포함)----------------*/
		$_ajax_trip_start		= $ajax_trip_start." 00:00:00";
		$_ajax_trip_end			= $ajax_trip_end." 23:59:00";
		/*---------------------------------------------------*/
		$sql=		   " SELECT														";
		$sql= $sql."	 OP.no							as op_no					";
		$sql= $sql."	,OP.o_itinerary					as op_o_itinerary			";
		$sql= $sql."	,OP.o_start						as op_o_start				";
		$sql= $sql."	,OP.o_end						as op_o_end					";
		$sql= $sql."	,DATE_FORMAT(OP.o_start, '%Y-%m-%d') as op_o_startYMD		";//YYYY-MM-DD
		$sql= $sql."	,DATE_FORMAT(OP.o_end, '%Y-%m-%d')   as op_o_endYMD			";//YYYY-MM-DD
		$sql= $sql."	,OP.ProjectCode					as op_ProjectCode			";
		$sql= $sql."	,OP.memberno					as op_memberno				";
		$sql= $sql." FROM															";
		$sql= $sql."      official_plan_tbl OP										";
		$sql= $sql."  WHERE															";
		$sql= $sql."      OP.no = '".$content_id."'									";
		/*---------------------------------------------------*/
		$re = mysql_query($sql,$db);
		/*---------------------------------------------------*/
		$op_o_startYMD	= mysql_result($re,0,"op_o_startYMD");
		$op_o_endYMD	= mysql_result($re,0,"op_o_endYMD");
		$op_o_itinerary	= mysql_result($re,0,"op_o_itinerary");
		$op_ProjectCode	= mysql_result($re,0,"op_ProjectCode");
		$op_memberno	= mysql_result($re,0,"op_memberno");
		/*---------------------------------------------------*/
		$memberno_arr = explode(",",$op_memberno);
		$cnt = count($memberno_arr);
		if($cnt>0){
			for($i=0; $i<$cnt;$i++){
				if($memberno_arr[$i]!=""){
					$delete_query = "DELETE FROM userstate_tbl WHERE state='3' AND start_time='".$op_o_startYMD."' AND end_time='".$op_o_endYMD."' AND projectCode='".$op_ProjectCode."' AND  note='".$op_o_itinerary."' AND  memberno='".$memberno_arr[$i]."'   ";//
					$result = mysql_query($delete_query);
				}
			}//for
		}
		/*---------------------------------------------------*/
		$ExecuteQuery01 = array();
		/*---------------------------------------------------*/

		$date_today  = date("Y-m-d");

		for($i=1;$i<5;$i++){
			if($ajax_trip_memberId[$i] != ""){
				$sql01= " SELECT * FROM										";
				$sql01= $sql01." member_tbl									";
				$sql01= $sql01."	WHERE									";
				$sql01= $sql01."  MemberNo = '".$ajax_trip_memberId[$i]."'  ";
				$sql01= $sql01." AND  										";
				$sql01= $sql01." WorkPosition <= '8'						";
				/*------------------------------*/
				$result01     = mysql_query($sql01,$db);
				$result01_num = mysql_num_rows($result01);
				if($result01_num != 0) {
					$_MemberNo  = mysql_result($result01,0,"MemberNo");
					$_korName   = mysql_result($result01,0,"korName");
					$_GroupCode = mysql_result($result01,0,"GroupCode");
					$_RankCode  = mysql_result($result01,0,"RankCode");
		/***********************************************************************************/
		$result = mysql_query($delete_query);
					if(mysql_result($result01,0,"RankCode") < "E1") {

						/* ----------------------------------- */
						//한맥ERP프로젝트 일원화 작업 : 181012
						$NewProjectCode = projectToColumn($ajax_trip_p_code,'NewProjectCode');
						$NewProjectCode = $NewProjectCode ==""?"":$NewProjectCode;
						/* ----------------------------------- */

						$azSQL01 = "SELECT max(num) FROM userstate_tbl";
						//ECHO $azSQL01."<br>";
						$res_userstate = mysql_query($azSQL01,$db);
						$res_num = current(mysql_fetch_array($res_userstate));
						$num_01  = $res_num + 1;
							$sql02= "INSERT INTO									";
							$sql02= $sql02." userstate_tbl							";
							$sql02= $sql02." (										";
							$sql02= $sql02."  num									";
							$sql02= $sql02." ,MemberNo								";
							$sql02= $sql02." ,GroupCode								";
							$sql02= $sql02." ,state									";//출장:3, 외근:12
							$sql02= $sql02." ,start_time							";
							$sql02= $sql02." ,end_time								";

							$sql02= $sql02." ,ProjectCode							";
							$sql02= $sql02." ,NewProjectCode						";

							$sql02= $sql02." ,note									";
							$sql02= $sql02." ,sub_code								";
							$sql02= $sql02." )										";
							$sql02= $sql02." VALUES									";
							$sql02= $sql02."		(								";
							$sql02= $sql02."		   '".$num_01."'				";
							$sql02= $sql02."		  ,'".$_MemberNo."'				";
							$sql02= $sql02."		  ,'".$_GroupCode."'			";
							$sql02= $sql02."		  ,'3'							";//출장:3, 외근:12
							$sql02= $sql02."		  ,'".$ajax_trip_start."'		";
							$sql02= $sql02."		  ,'".$ajax_trip_end."'			";

							$sql02= $sql02."		  ,'".$ajax_trip_p_code."'		";
							$sql02= $sql02."		  ,'".$NewProjectCode."'		";

							$sql02= $sql02."		  ,'".$ajax_trip_destination."'	";
							$sql02= $sql02."		  ,''							";
							$sql02= $sql02."		)								";
						//echo $sql02."<br>";


							if($ajax_trip_start==$date_today)
							{
								setAbsent($_MemberNo, '5', $ajax_trip_destination, '', '');
							}

						mysql_query($sql02,$db);
						//array_push($ExecuteQuery01, $sql02);
					}//if End
					/*---------------------------------------------------------------------*/
				}//if($result01_num != 0) End
			}//if($ajax_trip_memberId[$i] != "") End
		}//for End
	/*----------------------------------------------------------------------------------*/
	$query01= "UPDATE													";
	$query01= $query01." official_plan_tbl	SET							";
	$query01= $query01."  o_area='".$ajax_trip_area."'					";
	$query01= $query01."  ,o_itinerary=	'".$ajax_trip_destination."'	";
	$query01= $query01."  ,o_group=	'".$GroupCode."'					";
	$query01= $query01."  ,o_name='".$withMemName."'					";
	$query01= $query01."  ,o_start=	'".$_ajax_trip_start."'				";
	$query01= $query01."  ,o_end='".$_ajax_trip_end."'					";
	$query01= $query01."  ,o_object='".$ajax_trip_reason."'				";
	$query01= $query01."  ,o_note='".$$date_today."'					";
	$query01= $query01."  ,projectcode=	'".$ajax_trip_p_code."'			";
	$query01= $query01."  ,memberno='".$withMemId."'					";
	$query01= $query01."  ,o_change='2'									";//출장:2, 외근:1
	$query01= $query01." where											";
	$query01= $query01."	no = '".$content_id."'						";
	//////////////////////////

	//////////////////////////
	mysql_query($query01,$db);
	//////////////////////////
	//array_push($ExecuteQuery01, $query01);
	//////////////////////////
		$result_01 = "1";

		////////////////////////////////////////////////////
		//DB처리 1단계 : userstate_tbl
		//DB처리 2단계 : official_plan_tbl
		//DB처리 3단계 : person_tbl
		////////////////////////////////////////////////////
		//$result_01 = "1"; //DB처리 성공(1~3단계)
		//$result_01 = "2"; // DB처리 실패(1~2단계)
		//$result_01 = "3"; //DB처리 실패(1~2단계는 성공, 3단계 실패)
		////////////////////////////////////////////////////
		//return $sql03;
		 echo $result_01;
		//echo 1;
		////////////////////////////////////////////////////

	}  //UpdateDB_officialPlan End

	/* ------------------------------------------------------------------------------ */
	function EtcCRUD()	//경유/휴가  DB실행
	{
		global	$CompanyKind; // 회사코드
		/*-------------*/
		global	$MemberNo;	  // 사원번호
		global	$korName;	  // 한글이름
		global	$GroupCode;	  // 부서
		$ch_GroupCode = (int)$GroupCode; // 부서
		global	$RankCode;	  // 직급
		global	$SortKey;	  // 직급+부서
		global	$ExtNo;		  //내선번호
		/* ----------------- */
		global	$date_today;  // 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1; // 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$nowYear;     // 오늘날짜 년          : yyyy
		global	$nowMonth;    // 오늘날짜 년월        : yyyy-mm
		global	$nowHour;	  // 현재 시
		global	$nowMin;	  // 현재 분
		global	$nowTime;	  // 현재 시:분
		global  $db;
		/*--------------------------------*/
		$get_value01		=($_GET['value01']==""?"":$_GET['value01']);// insert/update/delete 구분
		$get_value02		=($_GET['value02']==""?"":$_GET['value02']);// PK
		/*--------------------------------*/
		$post_etc_num		=($_POST['etc_num']==""?"":$_POST['etc_num']);			        //PK
		$post_etc_num		=($post_etc_num==""?$get_value02:$post_etc_num);			    //PK (update delete)
		/*--------------------------------*/
		$post_memberID		=($_POST['memberID']==""?$MemberNo:$_POST['memberID']);			//사원번호
		$post_CompanyKind	=($_POST['CompanyKind']==""?$CompanyKind:$_POST['CompanyKind']);//회사코드
		$post_etcKind		=($_POST['etcKind']==""?"":$_POST['etcKind']);					//구분(경유=2, 휴가=1)
		$post_etc_p_code	=($_POST['etc_p_code']==""?"":$_POST['etc_p_code']);			//프로젝트 코드
		$post_etc_p_name	=($_POST['etc_p_name']==""?"":$_POST['etc_p_name']);			//프로젝트 네임
		$post_etc_start_time=($_POST['etc_start_time']==""?"":$_POST['etc_start_time']);	//시작일자(YYYY-MM-DD)
		$post_etc_end_time	=($_POST['etc_end_time']==""?"":$_POST['etc_end_time']);		//종료일자(YYYY-MM-DD)
		$post_etc_note		=($_POST['etc_note']==""?"":$_POST['etc_note']);				//사유
		/*--------------------------------*/
		$resultState="1"; //실행성공=1, 실패=2,3,4...

		$date_today  = date("Y-m-d");

		/* *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** */
		if($get_value01=="insert"){ //등록
			/*-----------------------------------*/
			if(($post_etcKind!="" || $post_etcKind!=null) && ($post_CompanyKind!="" || $post_CompanyKind!=null) ){
				/*-----------------------------------*/
				if($post_etcKind=="1"){//휴가
				/*-----------------------------------*/
					if($date_today==$post_etc_start_time){
					/* 재석상태 관련 코드추가(2015-03-18) Start *********************************** */
					$value01 = $MemberNo;
					setAbsent($value01, '7', $post_etc_note, '', '');
					//$value01=사원번호, $value02=상태값(default:2:자리비움), $value03=코멘트
					//$value04=미지정(추후사용),$value05=미지정(추후사용)
					/* 재석상태 관련 코드추가 End *********************************** */
					}
					/*-----------------------------------*/
					if($post_CompanyKind=="HANM"){
						$post_etc_p_code= "H".substr($nowYear,2,2)."-교휴-04";
					}else if($post_CompanyKind=="BARO"){
						$post_etc_p_code= "H".substr($nowYear,2,2)."-교휴-04";
					}else if($post_CompanyKind=="JANG"){
						$post_etc_p_code= substr($nowYear,2,2)."-교휴-04";
					}else if($post_CompanyKind=="PILE"){
						$post_etc_p_code= substr($nowYear,2,2)."-교휴-04";
					}//if End
					/*-----------------------------------*/


					if($post_etc_start_time==$date_today)
					{
						setAbsent($post_memberID, '7', $post_etc_note, '', '');
					}

				}else if($post_etcKind=="2"){//경유


					if($post_etc_start_time==$date_today)
					{
						setAbsent($post_memberID, '13', $post_etc_note, '', '');
					}

					if($post_etc_p_code==""){
						$resultState="3";//DB실행 실패
					}//if End
					/*-----------------------------------*/
				}//if End
				/* -------------------------------------------------------- */

				/* ----------------------------------- */
				//한맥ERP프로젝트 일원화 작업 : 181012
				$NewProjectCode = projectToColumn($post_etc_p_code,'NewProjectCode');
				$NewProjectCode = $NewProjectCode ==""?"":$NewProjectCode;
				/* ----------------------------------- */

				//쿼리01
				$sql01= "INSERT INTO									";
				$sql01= $sql01." userstate_tbl							";
				$sql01= $sql01." (										";
				$sql01= $sql01."   MemberNo								";
				$sql01= $sql01." , GroupCode							";
				$sql01= $sql01." , state								";
				$sql01= $sql01." , start_time							";
				$sql01= $sql01." , end_time								";

				$sql01= $sql01." , ProjectCode							";
				$sql01= $sql01." , NewProjectCode						";

				$sql01= $sql01." , note									";
				$sql01= $sql01." , sub_code								";
				$sql01= $sql01." )										";
				$sql01= $sql01." VALUES									";
				$sql01= $sql01."		(								";
				$sql01= $sql01."		  '".$post_memberID."'			";
				$sql01= $sql01."		, '".$ch_GroupCode."'			";
				$sql01= $sql01."		, '".$post_etcKind."'			";// 경유=2, 휴가=1
				$sql01= $sql01."		, '".$post_etc_start_time."'	";
				$sql01= $sql01."		, '".$post_etc_end_time."'		";

				$sql01= $sql01."		, '".$post_etc_p_code."'		";
				$sql01= $sql01."		, '".$NewProjectCode."'		";

				$sql01= $sql01."		, '".$post_etc_note."'			";
				$sql01= $sql01."		, ''							";
				$sql01= $sql01."		)								";
				/* ------------------------------------------------------- */






			///////////////////////
	mysql_query($sql01,$db);  //등록
			///////////////////////
				/*(구)인트라넷 소스내 주석-MYSTATION 관련인듯 판단됨***
				* $sql3="update person_tbl set client_stat='$state',description='[$start_time]<br>$note' where person_id = '$ExtNo'";
				* **************************** */
					$resultState="1";//등록성공
				}else{
					$resultState="4";//실행 실패
				}//if End
			/*----------------*/
			echo $resultState; //실행여부 리턴
		 /* *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** *** */
		 }else if($get_value01=="update"){//수정
			 if($post_etc_num!="" || $post_etc_num!=null){
			 	/* ----------------------------------- */
			 	//한맥ERP프로젝트 일원화 작업 : 181012
			 	$NewProjectCode = projectToColumn($post_etc_p_code,'NewProjectCode');
			 	$NewProjectCode = $NewProjectCode ==""?"":$NewProjectCode;
			 	/* ----------------------------------- */
				//쿼리02
				$sql02 = " UPDATE userstate_tbl SET							";
				$sql02 = $sql02."  MemberNo='".$post_memberID."'			";
				$sql02 = $sql02." ,GroupCode='".$ch_GroupCode."'			";
				$sql02 = $sql02." ,state='".$post_etcKind."'				";
				$sql02 = $sql02." ,start_time='".$post_etc_start_time."'	";
				$sql02 = $sql02." ,end_time='".$post_etc_end_time."'		";

				$sql02 = $sql02." ,ProjectCode='".$post_etc_p_code."'		";
				$sql02 = $sql02." ,ProjectCode='".$NewProjectCode."'		";

				$sql02 = $sql02." ,note='".$post_etc_note."'				";
				$sql02 = $sql02." ,sub_code=''								";
				$sql02 = $sql02."  WHERE									";
				$sql02 = $sql02."  num ='".$post_etc_num."'					";
			/////////////////////////
				mysql_query($sql02,$db);
				$resultState="1";
			}else{
				$resultState="5";
			}//if End
			/*----------------*/
			echo $resultState; //실행여부 리턴
			/*----------------*/
		}else if($get_value01=="delete"){//삭제
			 if($post_etc_num!="" || $post_etc_num!=null){
				//쿼리03
				$sql03 = "DELETE FROM userstate_tbl WHERE num='".$post_etc_num."' ";
			/////////////////////////
	mysql_query($sql03,$db);
			/////////////////////////
				$resultState="1";
			}else{
				$resultState="6";
			}//if End
			/*----------------*/
			echo $resultState; //실행여부 리턴
		}else{
			$resultState="2";//DB실행 실패
			/*----------------*/
			echo $resultState; //실행여부 리턴
		}//if End
	}//EtcCRUD End
	/* ------------------------------------------------------------------------------ */
	function GoEtcPop()	//페이지이동 : 경유/휴가 입력
	{
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
		global  $db;
		/* ----------------- */
		$get_pageKind =($_GET['pageKind']==""?"":$_GET['pageKind']);
		$get_content_id =($_GET['content_id']==""?"":$_GET['content_id']);
		if($get_pageKind=="insert"){
			$this->assign('pageKind',$get_pageKind); //insert/update
			$this->assign('date_today',$date_today);
			$this->myinfo();
			$this->display("intranet/common_layout/goEtcPop.tpl");
		}else if($get_pageKind=="update"){
			//데이터 유무 체크. return:Y=데이터있음, N=데이터없음 ///////////////////////////////////
			$mCode ="userstate_tbl";
			$mCode2="WHERE num = '".$get_content_id."'";
			$result_num = tableRowCount($mCode,$mCode2); //$mCode: 테이블명,$mCode2:쿼리
			if($result_num=="Y") { //결과값 존재(Y), 결과없음(N)
				/* ------------------------------------------ */
				$sql =      " SELECT									";
				$sql = $sql."	 U.num				as u_num			";
				$sql = $sql."	,U.MemberNo			as u_MemberNo		";
				$sql = $sql."	,U.GroupCode		as u_GroupCode		";
				$sql = $sql."	,U.state			as u_state			";
				$sql = $sql."	,U.start_time		as u_start_time		";
				$sql = $sql."	,U.end_time			as u_end_time		";
				$sql = $sql."	,U.ProjectCode		as u_ProjectCode	";
				$sql = $sql."	,U.note				as u_note			";
				$sql = $sql."	,U.sub_code			as u_sub_code		";
				$sql = $sql."	FROM									";
				$sql = $sql."	userstate_tbl U							";
				$sql = $sql."	WHERE									";
				$sql = $sql."	U.num = '".$get_content_id."'			";
				/* ------------------------------------------ */
				$re				= mysql_query($sql);
				/* ------------------------------------------ */
				$u_num			= mysql_result($re,0,"u_num");
				$u_MemberNo		= mysql_result($re,0,"u_MemberNo");
				$u_MemberName	= MemberNo2Name($u_MemberNo);
				$u_GroupCode	= mysql_result($re,0,"u_GroupCode");
				$u_state		= mysql_result($re,0,"u_state");
				$u_start_time	= mysql_result($re,0,"u_start_time");
				$u_end_time		= mysql_result($re,0,"u_end_time");
				$u_ProjectCode	= mysql_result($re,0,"u_ProjectCode");
				$mCode=$u_ProjectCode;
				$mCode2="ProjectNickname";
				$u_ProjectNickname = projectToColumn($mCode,$mCode2);
				$u_note			= mysql_result($re,0,"u_note");
				$u_sub_code		= mysql_result($re,0,"u_sub_code");
				/* ------------------------------------------ */
				$this->assign('u_num',$u_num);
				$this->assign('u_MemberNo',$u_MemberNo);
				$this->assign('u_MemberName',$u_MemberName);
				$this->assign('u_GroupCode',$u_GroupCode);
				$this->assign('u_state',$u_state);
				$this->assign('u_start_time',$u_start_time);
				$this->assign('u_end_time',$u_end_time);
				$this->assign('u_ProjectCode',$u_ProjectCode);
				$this->assign('u_ProjectNickname',$u_ProjectNickname);
				$this->assign('u_note',$u_note);
				$this->assign('u_sub_code',$u_sub_code);
				/* ------------------------------------------ */
				$this->assign('pageKind',$get_pageKind); //insert/update
				$this->assign('date_today',$date_today);
				$this->myinfo();
				$this->display("intranet/common_layout/goEtcPop.tpl");
			}else{
				echo "해당 컨텐츠는 존재하지 않습니다.";
			}//if End
		}else{
			echo "이동 페이지가 불확실합니다(관리자에게 문의하세요).";
		}//if End

	}  //GoEtcPop End

	//보안동의서 팝업
	function SecuPledgePop()
	{
		global	$korName;	  // 한글이름
		global	$selName;	  // 프린트시 선텍된 동의서명
		global $ConsentDate;
		global	$print;	  // 서약서 프린트 여부
		$ConsentDate = strtotime(date($ConsentDate));
		$this -> assign("selName",$selName);
		$this -> assign("korName",$korName);
		$this -> assign("ConsentDate",$ConsentDate);
		$this -> assign("print",$print);
		$this -> display("intranet/common_layout/security.tpl"); 
	}
	//보안동의서 승인 
	function SubmitConsent(){
		global $MemberNo;	// 회원 번호
		global $korName;	// 한글이름
		global $GroupCode;	// 그룹코드
		global $db;
		// error_log($sql,3, "../../log/swseo.log");
		
		$sql = "Insert into secu_pledge_li_tbl (MemberNo, Company, GroupCode, KorName, ConsentDate ) "
		." value ('$MemberNo', (Select Company from member_tbl where MemberNo = '$MemberNo'), "
		." $GroupCode, '$korName',curdate())";
		$result = mysql_query($sql , $db);
		// $this -> display("intranet/common_layout/security.tpl"); 
		echo json_encode($result);
	}
	/* ------------------------------------------------------------------------------ */

}//class Main End  /****************************************************************** */
?>
<?PHP
/* *********************************************************************
*	function GoOutHistoryList()			//외출/출장현황보기 리스트
*	function GoOutInsertAction()		//외출신청 DB입력 실행
*	function GoOutComback()				//외출복귀 DB입력 실행
*	function GoOutPop()					//페이지이동 : 외출신청
*	function GoTripPop()				//페이지이동 : 출장신청
*	function GoTripInsertAction()		//출장신청 DB입력 실행
*	function AddWorkPop()				//페이지이동 : 업무추가
*	function AddWork()					//업무추가  DB실행
*	function editWorkPop()				//페이지이동 : 업무수정
*	function editWork()					//업무수정  DB실행
*	function EndWorkPop()				//페이지이동 : 업무종료
*	function EndWork()					//업무종료 DB실행
*	function LunchInfo()				//점심식단 기본정보
*	function LunchPop()					//점심식단 LIST팝업
*	function LunchEditPop()				//페이지이동 : 점심식단 수정(관리자)
*	function LunchEdit()				//점심식단 수정 DB실행
*	function CheckPwMainPage()			//개인정보로 가는 비밀번호 확인 페이지
*	function myinfo()					//기본정보
*	function GoTripPopUpdate()			//페이지이동 : 출장수정
*	function UpdateDB_officialPlan()	//출장신청 DB수정 실행
*	function DeleteDB_officialPlan()	//삭제DB : 출장삭제:임원
*	function SignPop()					//결재 건수 팝업
*   function EtcCRUD()					//경유/휴가  DB실행
*   function GoEtcPop()					//페이지이동 : 경유/휴가 입력
*   function DaySearch()				//일자별 조회
********************************************************************* */
?>