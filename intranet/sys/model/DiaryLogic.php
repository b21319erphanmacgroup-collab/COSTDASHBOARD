<?php
	/* ***********************************
	* 주요일정
	* 2014-12-   :
	* 2014-12-18 : 세션값을 쿠키값으로 대체(/sys/inc/getCookieOfUser.php : 파일생성) : SUK
	* 2014-12-18 : php.날짜변수 관련 공통 페이지 삽입(/sys/inc/getNeedDate.php : 파일생성) : SUK
	* 2014-12-16 : 파일정리: SUK
	*************************************** */
	require('../../../SmartyConfig.php');
	require('../../sys/inc/function_intranet.php');
	/* ----------------------------------- */
	require_once($SmartyClassPath);
	/* ----------------------------------- */
	include "../inc/getCookieOfUser.php";  //사용자에 관한 쿠키값
	include "../inc/getNeedDate.php";      //로직에 사용되는 PHP시간&날짜 정의
	/* ----------------------------------- */
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
	}else if($_SESSION['CK_memberID']!=""){				//쿠키값 유무확인
		//쿠키정보 세션으로 대체 250426 김진선
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
	}else{
		/* ----------------------------------- */
		$memberID	=	$_GET['memberID'];
		$MemberNo	=	$memberID;
		$MemberNo	=	strtoupper($MemberNo);
		/* ----------------------------------- */
		require('../../sys/popup/setInfo.php');
		/* ----------------------------------- */
	}//if End
	/* ----------------------------------- */
	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
	/* ----------------------------------- */
	/* ----------------------------------- */

/* 모니터링 기간(2014-12-18~2014-12-22:17시이후)후 삭제가능
// 상단에 인클루드 처리한 파일로 대체
//	include "../inc/getCookieOfUser.php";  //사용자에 관한 쿠키값
//	include "../inc/getNeedDate.php";      //로직에 사용되는 PHP시간&날짜 정의
// SET SESSION -----------------------
		$MemberNo	=	$_SESSION['SS_memberID'];	//사원번호
	if($MemberNo!=""){
		$korName	=	$_SESSION['SS_korName'];	//한글이름
		$RankCode	=	$_SESSION['SS_RankCode'];	//직급코드
		$GroupCode	=	$_SESSION['SS_GroupCode'];	//부서코드
		$SortKey	=	$_SESSION['SS_SortKey'];	//직급+부서코드s
		$EntryDate	=	$_SESSION['SS_EntryDate'];	//입사일자
		$position	=	$_SESSION['SS_position'];	//직위명
		$GroupName	=	$_SESSION['SS_GroupName'];	//부서명

		$date_today  = date("Y-m-d");				// 오늘날짜 년월일      : yyyy-mm-dd
		$date_today1 = date("Y-m-d H:i");			// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
	    $nowYear     = date("Y");					// 오늘날짜 년          : yyyy
		$nowMonth    = date("Y-m");					// 오늘날짜 년월        : yyyy-mm
		$nowHour	 = date("H");					// 현재 시
		$nowMin		 = date("i");					// 현재 분
		$nowTime	 = $nowHour.":".$nowMin;		// 현재 시:분
************************************************************************************************************/

?>
<?php
	/* ----------------------------------- */
	$sendDate = $_GET['sendDate'];
	/* ----------------------------------- */
	$key00; //일정 수정을 위한 기존 등록자 사원번호
	$key01;	//일정 수정을 위한 부서(1),개인(2), 회사(3)구분코드
	$key02; //일정 수정을 위한 pk
	$key00 = $_GET['key00'];
	$key01 = $_GET['key01'];
	$key02 = $_GET['key02'];
	/* ----------------------------------- */
	$myIP=$_SERVER['REMOTE_ADDR'];
	/* ----------------------------------- */
	$ajax_pre_pk	    = $_POST['ajax_pre_pk'];
	$ajax_sendDate	    = $_POST['ajax_sendDate'];
	$ajax_radio_kind01	= $_POST['ajax_radio_kind01'];
	$ajax_radio_kind02	= $_POST['ajax_radio_kind02'];
	$ajax_diary_content	= $_POST['ajax_diary_content'];
?>
<?php
class DiaryLogic extends Smarty {
	// 생성자
	function DiaryLogic()
	{
		global $SmartyClass_TemplateDir;
		global $SmartyClass_CompileDir;
		global $SmartyClass_ConfigDir;
		global $SmartyClass_CacheDir;
		/* ----------------------------------- */
		$this->Smarty();
		/* ----------------------------------- */
		$this->template_dir		=$SmartyClass_TemplateDir;
		$this->compile_dir		=$SmartyClass_CompileDir;
		$this->config_dir		=$SmartyClass_ConfigDir;
		$this->cache_dir		=$SmartyClass_CacheDir;
	}//Main End
	/* ----------------------------------------------------------------------------------------------- */
	function Update_page()
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
		global  $sendDate;
		/* ----------------- */
		global  $key00; //일정 수정을 위한 기존 등록자 사원번호
		global  $key01;	//일정 수정을 위한 개인(1), 부서(2) 회사(3)구분코드
		global  $key02; //일정 수정을 위한 pk
		/* ----------------- */
		$query_data = array();
		/* ----------------- */
		$value01;	//부서&개인구분
		$value02;	//날짜
		$value03;	//상세구분
		$value04;	//내용
		$value05;	//부서그룹코드
		/* ----------------- */
		$key00; //일정 수정을 위한 기존 등록자 사원번호
		$key01;	//일정 수정을 위한 개인(1), 부서(2) 회사(3)구분코드
		$key02; //일정 수정을 위한 pk
		/* ----------------- */
		global  $myIP;
		/* ----------------- */
		if($key01=='2'){ //개인
			$sql= "SELECT									";
			$sql= $sql."  M.no		   as m_no		        ";		// PK 코드
			$sql= $sql." ,M.MemberNo   as m_MemberNo		";		// 사원번호
			$sql= $sql." ,M.groupcode  as m_groupcode		";		// 개인_그룹코드
			$sql= $sql." ,M.pdate      as m_pdate			";		// 개인_스케줄 시작일자
			$sql= $sql." ,M.subcode    as m_subcode			";		// 개인_m_subcode =>  1:업무, 2:일정, 3:기념일 =>구분이름
			$sql= $sql." ,M.contents   as m_contents		";		// 개인_내용
			$sql= $sql." ,M.contents   as m_contentsShort	";		// 개인_내용_줄임
			$sql= $sql." ,M.cdate      as m_cdate			";		// 개인_스케줄 종료일자
			$sql= $sql." ,M.updateuser as m_updateuser		";		// 개인_등록자 사원번호
			$sql= $sql." FROM								";
			$sql= $sql."      my_schedule_tbl M				";
			$sql= $sql." WHERE								";
			//$sql= $sql." M.MemberNo = '".$key00."'		    ";
			//$sql= $sql." AND								";
			$sql= $sql." M.no = '".$key02."'		        ";

			$result = mysql_query($sql,$db);
			$result_num = mysql_num_rows($result);

			if($result_num != 0) {
				/* ------------------------------------------------------------- */
				$value00	= $key02;										//	pk
				$value01	= $key01;										//	부서/개인구분
				$value02	= mysql_result($result,0,"m_pdate");			//	날짜
				$value03	= mysql_result($result,0,"m_subcode");			//	상세구분
				$value04	= mysql_result($result,0,"m_contents");			//	내용

			}//if End
		}else if($key01=='1' || $key01=='3'){ //부서
			$sql= "SELECT									";
			$sql= $sql."  S.no		   as s_no		        ";		// PK 코드
			$sql= $sql." ,S.groupcode  as s_groupcode   	";		// 부서_그룹코드
			$sql= $sql." ,S.pdate	   as s_pdate           ";		// 부서_스케줄 시작일자
			$sql= $sql." ,S.subcode    as s_subcode         ";		// 부서_s_subcode =>  1:일정, 2:업무, 3:기념일, 4:프로젝트 =>구분이름
			$sql= $sql." ,S.contents   as s_contents     	";		// 부서_내용
			$sql= $sql." ,S.contents   as s_contentsShort	";		// 부서_내용_줄임
			$sql= $sql." ,S.set_color  as s_set_color	";		// 부서_배경색
			$sql= $sql." ,S.cdate      as s_cdate           ";		// 부서_스케줄 종료일자
			$sql= $sql." ,S.updateuser as s_updateuser 	    ";		// 부서_등록자 사원번호
			$sql= $sql." FROM								";
			$sql= $sql."     schedule_job_tbl S			    ";
			$sql= $sql." WHERE							    ";
			//$sql= $sql." S.updateuser = '".$key00."'		";
			//$sql= $sql." AND								";
			$sql= $sql."  S.no  = '".$key02."'		        ";

			$result = mysql_query($sql,$db);
			$result_num = mysql_num_rows($result);

			if($result_num != 0) {
				/* ------------------------------------------------------------- */
				$value00	= $key02;									//	pk
				$value01	= $key01;									//	부서/개인구분
				$value02	= mysql_result($result,0,"s_pdate");		//	날짜
				$value03	= mysql_result($result,0,"s_subcode");		//	상세구분
				$value04	= mysql_result($result,0,"s_contents");		//	내용
				$value05	= mysql_result($result,0,"s_groupcode");	//	부서그룹코드
				$this->assign('set_color',mysql_result($result,0,"s_set_color"));//	배경색
				//echo mysql_result($result,0,"s_set_color");

			}//if End
		}//if End
		/* -----------------*/
		$PersonAuthority = new PersonAuthority();
		/* -----------------*/
		if($PersonAuthority->GetInfo($MemberNo,'총무')){
			$this->assign('Auth',true);
		}else{
			$this->assign('Auth',false);
		}
		/* -----------------*/
		$this->assign('pre_pk',$value00);		//	pk
		$this->assign('pre_kind',$value01);		//	부서(1)/개인(2)구분
		$this->assign('pre_pdate',$value02);	//	날짜
		$this->assign('pre_subcode',$value03);	//	상세구분
		$this->assign('pre_contents',$value04);	//	내용
		$this->assign('pre_groupcode',$value05);//	부서그룹코드
		/* -----------------*/
		$this->assign('MemberNo',$MemberNo);
		/* -----------------*/
		$this->assign('RankCode',$RankCode);	//개인_직급코드
		$this->assign('GroupCode',(int)$GroupCode);	//개인_부서코드
		$this->assign('myIP',$myIP);	//개인_부서코드
		/* -----------------*/
		$this->display("intranet/common_contents/work_diary/updateDiary.tpl");
		/* -----------------*/
	}  //Update_page End
	/* ------------------------------------------------------------------------------ */

	/* 일정수정 실행로직------------------------------------------------------------------------------ */
	function UpdateAction()
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
		global  $ajax_pre_pk;			//pk
		global  $ajax_sendDate;
		global  $ajax_radio_kind01;		//부서(1), 개인(2) 구분
		global  $ajax_radio_kind02;		//	부서일정 : 1:일정, 2:업무, 3:기념일, 4:프로젝트 || 개인일정 : 1:업무, 2:일정, 3:기념일
		global  $ajax_diary_content;	//내용
		global  $ajax_set_color;	//배경색
		/* ----------------- */
		$GroupCode = (int)$GroupCode;
		/* ----------------- */
		if($ajax_radio_kind01 ==1){
			//부서일정
			$sql= " UPDATE schedule_job_tbl SET					";
			$sql= $sql."  groupcode='".$GroupCode."'			";
			$sql= $sql." ,pdate='".$ajax_sendDate."'				";
			$sql= $sql." ,subcode='".$ajax_radio_kind02."'      ";
			$sql= $sql." ,contents='".$ajax_diary_content."'    ";
			$sql= $sql." ,set_color='".$ajax_set_color."'    ";
			$sql= $sql." ,cdate='".$date_today."'				";
			$sql= $sql." ,updateuser='".$MemberNo."'			";
			$sql= $sql." WHERE									";
			$sql= $sql." no ='".$ajax_pre_pk."'					";
		}else if($ajax_radio_kind01 ==3){
			//회사일정
			$sql= " UPDATE schedule_job_tbl SET					";
			$sql= $sql."  groupcode='99'						";
			$sql= $sql." ,pdate='".$ajax_sendDate."'			";
			$sql= $sql." ,subcode='".$ajax_radio_kind02."'      ";
			$sql= $sql." ,contents='".$ajax_diary_content."'    ";
			$sql= $sql." ,set_color='".$ajax_set_color."'    ";
			$sql= $sql." ,cdate='".$date_today."'				";
			$sql= $sql." ,updateuser='".$MemberNo."'			";
			$sql= $sql." WHERE									";
			$sql= $sql." no ='".$ajax_pre_pk."'					";
		}else if($ajax_radio_kind01 ==2){
			//개인일정
			$sql= " UPDATE my_schedule_tbl SET					";
			$sql= $sql."   groupcode='".$groupcode."'			";
			$sql= $sql." , MemberNo='".$MemberNo."'				";
			$sql= $sql." , pdate='".$ajax_sendDate."'				";
			$sql= $sql." , subcode='".$ajax_radio_kind02."'		";
			$sql= $sql." , contents='".$ajax_diary_content."'	";
			$sql= $sql." , cdate='".$date_today."'				";
			$sql= $sql." , updateuser='".$MemberNo."'			";
			$sql= $sql." WHERE									";
			$sql= $sql." no ='".$ajax_pre_pk."'					";
		}	//if End
///////////////////////
mysql_query($sql,$db);
///////////////////////
echo "1";
///////////////////////
	}  //UpdateAction End
	/* ------------------------------------------------------------------------------ */


	/* ------------------------------------------------------------------------------ */
	function InsertPage()
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
		global  $sendDate;
		/* ----------------- */
		global  $myIP;
		global  $ActionMode;
		/* ----------------- */
		$PersonAuthority = new PersonAuthority();
		if($PersonAuthority->GetInfo($MemberNo,'총무')){
			$this->assign('Auth',true);
		}else{
			$this->assign('Auth',false);
		}
		/* ----------------- */
		$this->assign('myIP',$myIP);	//개인_부서코드
		$this->assign('RankCode',$RankCode);
		$this->assign('GroupCode',$GroupCode);
		$this->assign('sendDate',$sendDate);
		$this->assign('MemberNo',$MemberNo);
		$this->assign('ActionMode',$ActionMode);
		$this->assign('set_color','FFF');
		$this->display("intranet/common_contents/work_diary/addDiary_jmj.tpl");
		/* ----------------- */
	}  //InsertPage End
	/* ------------------------------------------------------------------------------ */

	/* 일정추가 실행로직------------------------------------------------------------------------------ */
	function InsertAction()
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

		global	$ajax_startDate;	  // 시작일
		global	$ajax_endDate;	  // 종료일
		global	$ajax_set_color;	  // 배경색
		/* ----------------- */
		global  $db;
		/* ----------------- */
		global  $ajax_sendDate;
		global  $ajax_radio_kind01;
		global  $ajax_radio_kind02;
		global  $ajax_diary_content;
		/* ----------------- */
		/* ----------------- */
		if($ajax_radio_kind01 ==1){
			//부서일정
			$GroupCode = (int)$GroupCode;
		//}else if($ajax_radio_kind01 ==3){
		}else if($ajax_radio_kind01 ==3){
			//회사일정
			$GroupCode = '99';
		}else if($ajax_radio_kind01 ==2){
			/*
			//개인일정
			$sql= "INSERT INTO                                                        ";
			$sql= $sql." my_schedule_tbl                                              ";
			$sql= $sql." (MemberNo,groupcode,pdate,subcode,contents,set_color,cdate,updateuser) ";
			$sql= $sql." VALUES                                                       ";
			$sql= $sql." ('$MemberNo','$GroupCode','$ajax_sendDate','$ajax_radio_kind02','$ajax_diary_content','".$set_color."','$date_today','$MemberNo') ";
			*/
		}	//if End


		//$start_date = '2013-12-19';
		//$end_date = '2013-12-29';
		while (strtotime($ajax_startDate) <= strtotime($ajax_endDate)) {
			$sql= "INSERT INTO                                               ";
			$sql= $sql." schedule_job_tbl                                    ";
			$sql= $sql." (groupcode,pdate,subcode,contents,set_color,cdate,updateuser) ";
			$sql= $sql." VALUES                                              ";
			$sql= $sql." ('".$GroupCode."','".$ajax_startDate."','".$ajax_radio_kind02."','".$ajax_diary_content."','".$ajax_set_color."','".$date_today."','".$MemberNo."') ";

			//echo $sql.'<br>';

			///////////////////////
			mysql_query($sql);
			///////////////////////

			$ajax_startDate = date ("Y-m-d", strtotime("+1 day", strtotime($ajax_startDate)));
		}



		echo "1";
		///////////////////////
	}  //InsertAction End
	/* ------------------------------------------------------------------------------ */

	/* ------------------------------------------------------------------------------ */
	function DeleteAction()
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
		global  $ajax_pre_pk;
		global  $ajax_radio_kind01;	//부서개인 구분(부서:1, 개인:1)
		/* ----------------- */
	if( $ajax_radio_kind01 == "1" || $ajax_radio_kind01 == "3" ){	    //부서일정,회사일정 삭제
		$delete_query = "delete from schedule_job_tbl where no = '".$ajax_pre_pk."'";
	///////////////////////
	mysql_query($delete_query);
	///////////////////////
	echo "1";
	///////////////////////

	}else if( $ajax_radio_kind01 == "2" ){	//개인일정 삭제

		$delete_query = "delete from my_schedule_tbl where no = '".$ajax_pre_pk."'";
	///////////////////////
	mysql_query($delete_query);
	///////////////////////
	echo "1";
	///////////////////////
	}else{
	//삭제실패
	echo "2";
	}
	}//DeleteAction() End
	/* ------------------------------------------------------------------------------ */
	function DiaryListAction()
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
		global  $sendDate;
		/* ----------------- */
		if ($sendDate==''){
			$todaySplit = split("-",$date_today);
		}else{
			$todaySplit = split("-",$sendDate);
		}
		
		$this->assign('GroupCode2',$GroupCode); 
	/* ----------------- */
		//$todaySplit[0]:년도, $todaySplit[1]:월, $todaySplit[2]:일
		$s_Y=$todaySplit[0]; // 지정된 년도
		$s_m=$todaySplit[1]; // 지정된 월
		$s_d=$todaySplit[2]; // 지정된 요일
		/* ----------------- */
		$s_t=date("t",mktime(0,0,0,$s_m,$s_d,$s_Y)); // 지정된 달은 몇일까지 있을까요?
		$s_n=date("N",mktime(0,0,0,$s_m,1,$s_Y)); // 지정된 달의 첫날은 무슨요일일1까요?
		$l=$s_n%7; // 지정된 달 1일 앞의 공백 숫자.
		$ra=($s_t+$l)/7; $ra=ceil($ra); $ra=$ra-1; // 지정된 달은 총 몇주로 라인을 그어야 하나?
		/* ----------------- */
		$p_Y= date("Y-m-d",mktime(0,0,0,$s_m,$s_d,$s_Y-1)); // 작년
		$n_Y= date("Y-m-d",mktime(0,0,0,$s_m,$s_d,$s_Y+1)); // 내년
		/* ----------------- */
		$p_m= date("Y-m-d",mktime(0,0,0,$s_m-1,$s_d,$s_Y)); // 이전달
		$n_m= date("Y-m-d",mktime(0,0,0,$s_m+1,$s_d,$s_Y)); // 다음달 (빠뜨린 부분 추가분이에요)
		/* ----------------- */
		$p_d= date("Y-m-d",mktime(0,0,0,$s_m,$s_d-1,$s_Y)); // 이전날
		$n_d= date("Y-m-d",mktime(0,0,0,$s_m,$s_d+1,$s_Y)); // 다음날
		/*
		echo "<br>";
		echo "p_Y:작년:".$p_Y."<br>";
		echo "n_Y:내년:".$n_Y."<br>";
		echo "p_m:이전달:".$p_m."<br>";
		echo "n_m:다음달:".$n_m."<br>";
		//echo "$p_d:어제:".$p_d."<br>";
		//echo "$n_d:내일:".$n_d."<br>";
		*/
		/* ----------------- */
		$firstDay = $todaySplit[0]."-".$todaySplit[1]."-01";
		/* ----------------- */
		//$firstDay = "2014-11-01";
		//$today = "2014-10-01";
		/* ----------------- */
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
		$startDate_tmp = $dayitem[0]."-".$dayitem[1];
		/* ------------------------- */
		//해당월: 년-월-마지막날짜
		$endDate   = $dayitem[0]."-".$dayitem[1]."-".$end_day;
		//해당월: 년-월
		$yearMonthDate = $dayitem[0]."-".$dayitem[1];
		/* ----------------- */

		/* 휴일 ********************* */
		$i=0;
		$sql="select * from holyday_tbl where date between '$startDate' and '$endDate'";
		$re = mysql_query($sql,$db);
		while($re_row = mysql_fetch_array($re)){
			$holydate[$i]=$re_row[date];
			$holyname[$i]=$re_row[description];
			$i++;
		}

		/* *********************************************** */
		/* 해당 월의 달력 생성 Start ********************* */
		$DayList    = array();
		$displayday = 1;
		/* ----------------- */
		/* 달력의 행수를 결정
		*(매월1일이 토요일이면 6줄이고 나머지는 5줄.
		* ---------------------------------------- */
		$row_count;
		if($week==6){
			$row_count=7*6;
		}else{
			$row_count=7*5;
		}
		/* ---------------------------- */
			for($index=0;$index < $row_count;$index++)
			{
				if($index < $week )
				{
					$ItemData=array("day" =>'',"flagUse1"=>"","flagUse2"=>"","s_contents1"=>"","s_contents"=>"","s_contentsShort1"=>"","s_contentsShort"=>"");
					array_push($DayList,$ItemData);
				}
				else if($displayday <= $end_day)
				{
					$CheckDate=$startDate_tmp."-".sprintf("%02d",$displayday);
					$displayday2=$displayday;

					for($i=0;$i < count($holydate);$i++)
					{	if($holydate[$i]==$CheckDate)
						{
							$displayday2="<font color=red>".$displayday." ".$holyname[$i]."</font>";
						}
					}

					$ItemData=array( "day"=>$displayday2,"flagUse1"=>"","flagUse2"=>"","write_id01"=>"","write_id02"=>"","date_s1"=>"","date_s"=>"","keyCode1"=>"","keyCode2"=>"","subCode1"=>"","subCode2"=>"","s_contents1"=>"","s_contents"=>"","s_contentsShort1"=>"","s_contentsShort"=>"");
					array_push($DayList,$ItemData);
					$displayday=$displayday+1;
				}
				else
				{
					$ItemData=array("day" =>'',"flagUse1"=>"","flagUse2"=>"","s_contents1"=>"","s_contents"=>"","s_contentsShort1"=>"","s_contentsShort"=>"");
					array_push($DayList,$ItemData);
				}
			}//for End
			/* ---------------------------- */
		/* 해당 월의 달력 생성 End *********************** */
		/* *********************************************** */
		// 부서 일정 Start *********************************************************** */
		$sql2= "SELECT										";
		$sql2= $sql2."  S.no		 as s_no				";		// PK 코드
		$sql2= $sql2." ,S.groupcode  as s_groupcode			";		// 부서_그룹코드
		$sql2= $sql2." ,S.pdate	     as s_pdate				";		// 부서_스케줄 시작일자
		$sql2= $sql2." ,S.pdate	     as s_pdate_day			";		// 부서_스케줄 시작일자:day
		$sql2= $sql2." ,S.subcode    as s_subcode			";		// 부서_s_subcode =>  1:일정, 2:업무, 3:기념일, 4:프로젝트 =>구분이름
		$sql2= $sql2." ,S.contents   as s_contents			";		// 부서_내용
		$sql2= $sql2." ,S.contents   as s_contentsShort		";		// 부서_내용_줄임
		$sql2= $sql2." ,S.set_color  as s_set_color				";		// 부서 배경색
		$sql2= $sql2." ,S.cdate      as s_cdate				";		// 부서_스케줄 종료일자
		$sql2= $sql2." ,S.updateuser as s_updateuser		";		// 부서_등록자 사원번호
		$sql2= $sql2." FROM									";
		$sql2= $sql2."     schedule_job_tbl S				";
		$sql2= $sql2." WHERE								";
		$sql2= $sql2." '".$startDate."'<= S.pdate			";
		$sql2= $sql2." AND									";
		$sql2= $sql2." S.pdate <='".$endDate."'				";
		$sql2= $sql2." AND									";
		//안전진단부 32, 안전관리부 33, 건설사업관리부 31 의 일정을 같이 공유
		//if( $GroupCode == '31' or $GroupCode == '32' or $GroupCode == '33' ){
		if( $GroupCode == '31' or $GroupCode == '33' ){	//22.07.11 안전진단부는 아니라고함. 공유 제외.
			//$GroupCode = "31','32','33";
			$GroupCode = "31','33";
		}
		$sql2= $sql2." S.groupcode in ( '".(int)$GroupCode."' )	";
		//$sql2= $sql2." S.groupcode in( '".(int)$GroupCode."'	,'' )";
		$sql2= $sql2." ORDER BY S.pdate Desc, s_set_color				";
		//echo $sql2;
		/* ********************************************************************************** */
		$depart_data = array();
		$result_depart = mysql_query($sql2,$db);
		while($re_row_depart = mysql_fetch_array($result_depart)){
			$re_row_depart[s_pdate_day] =  (int)mb_substr($re_row_depart[s_pdate_day],8,2,"UTF-8");

			$memberYN = $re_row_depart[s_updateuser];
			if($memberYN!=""){
				$re_row_depart[s_updateuserName] = MemberNo2Name($memberYN);
			}
			$memberYN ="";

						$s_len = mb_strlen($re_row_depart[s_contentsShort],"UTF-8");
						if($s_len>7){
							$re_row_depart[s_contentsShort] = mb_substr($re_row_depart[s_contentsShort],0,7,"UTF-8")."..";
						}

			array_push($depart_data,$re_row_depart);
		}

		$this->assign('depart_data',$depart_data);
/* ********************************************************************************** */
		//$azsql="select * from my_schedule_tbl where MemberNo = '$MemberNo' and  '$startDate'<= pdate and pdate <='$endDate' ";
		$re2 = mysql_query($sql2,$db);
		while($re_row2 = mysql_fetch_array($re2))
		{
			/* ------------------------------------------ */
			$dayitem=split("-",$re_row2[s_pdate]);
			/* ------------------------------------------ */
			for($index=0;$index < $row_count;$index++){
				if($DayList[$index][day] ==  $dayitem[2]){
						$DayList[$index][flagUse2]="1";
						$DayList[$index][keyCode2]=$re_row2[s_no];
						$DayList[$index][subCode2]=$re_row2[s_subcode];

						$DayList[$index][write_id02]=$re_row2[s_updateuser];

						$DayList[$index][date_s]=(int)mb_substr($re_row2[s_pdate],8,2,"UTF-8");
						//$DayList[$index][flagUse2]="D";
						/* ------------------------------------------------------------------------------ */
						$s_contentsShort_len = mb_strlen($re_row2[s_contentsShort],"UTF-8");
						if($s_contentsShort_len>8){
							$re_row2[s_contentsShort] = mb_substr($re_row2[s_contentsShort],0,7,"UTF-8")."..";
						}
						/* ------------------------------------------------------------------------------ */
						$DayList[$index][s_contentsShort]=$re_row2[s_contentsShort];
						$DayList[$index][s_contents]=$re_row2[s_contents];
				}//if End
			}//for End
		}//while End
		//  일정 End *********************************************************** */
		/* ************************************************************************* */
	// 회사 일정 Start *********************************************************** */
	$sql= "		SELECT								";
	$sql= $sql."  S1.no		    as s_no1			";		// PK 코드
	$sql= $sql." ,S1.groupcode  as s_groupcode1		";		// 부서_그룹코드
	$sql= $sql." ,S1.pdate	    as s_pdate1			";		// 부서_스케줄 시작일자
	$sql= $sql." ,S1.pdate	    as s_pdate_day1		";		// 부서_스케줄 시작일자:day
	$sql= $sql." ,S1.subcode    as s_subcode1		";		// 부서_s_subcode =>  1:일정, 2:업무, 3:기념일, 4:프로젝트 =>구분이름
	$sql= $sql." ,S1.contents   as s_contents1		";		// 부서_내용
	$sql= $sql." ,S1.contents   as s_contentsShort1	";		// 부서_내용_줄임
	$sql= $sql." ,S1.set_color  as s_set_color			";		// 배경색
	$sql= $sql." ,S1.cdate      as s_cdate1			";		// 부서_스케줄 종료일자
	$sql= $sql." ,S1.updateuser as s_updateuser1	";		// 부서_등록자 사원번호
	$sql= $sql." FROM								";
	$sql= $sql."     schedule_job_tbl S1			";
	$sql= $sql." WHERE								";
	$sql= $sql." '".$startDate."'<= S1.pdate		";
	$sql= $sql." AND								";
	$sql= $sql." S1.pdate <='".$endDate."'			";
	$sql= $sql." AND								";
	//$sql= $sql." S1.groupcode not in('1','7','10','".(int)$GroupCode."')"; // 제외 : ,개인소속부서 ,임원,현장(7),현장소장(10)
	$sql= $sql." S1.groupcode in('99')";
	$sql= $sql." ORDER BY S1.pdate Desc				";
	//echo $sql;
	/*
	'GroupCode', '01', '임원'
	'GroupCode', '02', '경영지원부'
	'GroupCode', '04', '공사관리팀'
	'GroupCode', '06', '생산본부'
	'GroupCode', '07', '현장(작업반)'
	'GroupCode', '08', '설계팀'
	'GroupCode', '09', '영업팀'
	'GroupCode', '10', '현장소장'
	'GroupCode', '99', '회사공지'
	*/
/* ********************************************************************************** */
		$company_data = array();
		$result_company = mysql_query($sql,$db);
		while($re_row_company = mysql_fetch_array($result_company)){
			$re_row_company[s_pdate_day1] =  (int)mb_substr($re_row_company[s_pdate_day1],8,2,"UTF-8");

			$memberYN = $re_row_depart[s_updateuser1];
			if($memberYN!=""){
				$re_row_depart[s_updateuserName1] = MemberNo2Name($memberYN);
			}
			$memberYN ="";

						$s_len = mb_strlen($re_row_company[s_contentsShort1],"UTF-8");
						if($s_len>7){
							$re_row_company[s_contentsShort1] = mb_substr($re_row_company[s_contentsShort1],0,7,"UTF-8")."..";
						}

			array_push($company_data,$re_row_company);
		}

		$this->assign('company_data',$company_data);
/* ********************************************************************************** */
		$re = mysql_query($sql,$db);
		while($re_row = mysql_fetch_array($re))
		{
			/* ------------------------------------------ */
			$dayitem=split("-",$re_row[s_pdate1]);
			/* ------------------------------------------ */
			for($index=0;$index < $row_count;$index++){
				if($DayList[$index][day] ==  $dayitem[2]){
						$DayList[$index][flagUse1]="2";
						$DayList[$index][keyCode1]=$re_row[s_no1];
						$DayList[$index][subCode1]=$re_row[s_subcode1];

						$DayList[$index][write_id01]=$re_row[s_updateuser1];

						$DayList[$index][date_s1]=(int)mb_substr($re_row[s_pdate1],8,2,"UTF-8");
						//$DayList[$index][flagUse2]="D";
						/* ------------------------------------------------------------------------------ */
						$s_contentsShort_len = mb_strlen($re_row[s_contentsShort1],"UTF-8");
						if($s_contentsShort_len>8){
							$re_row[s_contentsShort1] = mb_substr($re_row[s_contentsShort1],0,8,"UTF-8")."..";
						}
						/* ------------------------------------------------------------------------------ */
						$DayList[$index][s_contentsShort1]=$re_row[s_contentsShort1];
						$DayList[$index][s_contents1]=$re_row[s_contents1];
				}//if End
			}//for End
		}//while End
		//  일정 End *********************************************************** */
		/* ************************************************************************* */
			/* php페이지에서 달력 테스트-------------------------------------------- */
			for($Row=0;$Row<6;$Row++){
				for($Col=0;$Col<7;$Col++){
					$index=$Row*7+$Col;
				}//for End
			}//for End
			/* -------------------------------------------- */
		global  $myIP;
		/* -------------------------------------------- */
		$this->assign('myIP',$myIP);	//개인_부서코드
		$this->assign('MemberNo',$MemberNo);
		$this->assign('con_id',$MemberNo);	//접속자 사원번호
		$this->assign('con_name',$korName);	//접속자 한글이름
		/* -------------------------------------------- */
		$this->assign('p_Y',$p_Y);	//작년
		$this->assign('n_Y',$n_Y);	//내년
		$this->assign('p_m',$p_m);	//이전달
		$this->assign('n_m',$n_m);	//다음달
		/* -------------------------------------------- */
		$this->assign('yearMonthDate',$yearMonthDate);	//지정된 년-월
		/* -------------------------------------------- */
		$day_now = date('j',strtotime($date_today));
		/* -------------------------------------------- */
		$this->assign('day_now',$day_now);
		/* -------------------------------------------- */
		$this->assign('day_after1',$day_after1);
		$this->assign('day_after2',$day_after2);
		/* -------------------------------------------- */
		$this->assign('DayList',$DayList);
		$this->assign('dev',$_REQUEST[dev]);
		/* -------------------------------------------- */
		//$this->smarty->assign("page_action","schedule_equipment_controller.php");
		/* -------------------------------------------- */
		//$this->display("intranet/common_contents/work_diary/Diary_list.tpl");
		$this->display("intranet/common_contents/work_diary/diaryList.tpl");
		/* -------------------------------------------- */

	}	//DiaryListAction End
	/* ------------------------------------------------------------------------------ */
	
	function displaySearchList() {
		global $db, $GroupCode;
		extract($_REQUEST);
		
		/*
		if ($GroupCode != "31" || $GroupCode != "33") {
			echo "unauthorized user";
			return;
		}
		*/
		
		$this->assignRequest();
		
		$year_month = explode("-", $year_month_date);
		$year = $year_month[0];
		$month = $year_month[1];
		$start_date = "$year-$month-01";
		$end_day = date("t", strtotime($start_date));
		$end_date = "$year-$month-$end_day";  
		$today = date("Y-m-d");
		
		// 휴일
		$sql  = " Select  ";
		$sql .= "     *  ";
		$sql .= " From  ";
		$sql .= "     holyday_tbl  ";
		$sql .= " Where date Between '$start_date' And '$end_date' ";
		$resource = mysql_query($sql, $db);
		
		if (!$resource) {
			echo "mysql_query(holyday_tbl resource) error";
			return;
		}
		
		$holyDays = array();
		while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
			$day = $row[date];
			$dayName = $row[description];
			
			$holyDays[$day] = $dayName;
		}
		
		$dayInfo = array();
		$yoil = array("일", "월", "화", "수", "목", "금", "토");
		for ($i = 1; $i <= $end_day; $i++) {
			$day = "$year-$month-" . sprintf("%02d", $i);
			$dayIndex = date('w', strtotime($day));
			$dayName = $yoil[$dayIndex];
			
			if (!is_array($dayInfo[$day])) {
				$dayInfo[$day] = array();
			}
			
			$dayInfo[$day][dayName] = $dayName;
			$dayInfo[$day][saturdayTF] = $dayIndex == "6" ? "T" : "F";
			$dayInfo[$day][sundayTF] = $dayIndex == "0" ? "T" : "F";
			$dayInfo[$day][holyDayTF] = $holyDays[$day] == true ? "T" : "F";
			
			if ($dayInfo[$day][saturdayTF] == "T") {
				$dayInfo[$day][className] = "saturday";
			} else if ($dayInfo[$day][sundayTF] == "T") {
				$dayInfo[$day][className] = "sunday";
			} else if ($dayInfo[$day][holyDayTF] == "T") {
				$dayInfo[$day][className] = "holyday";
			}
		}
		
		// 스케줄
		$sql  = " Select ";
		$sql .= "     a.*, ";
		$sql .= "     Case ";
		$sql .= "         When a.subcode = 1 Then '일정' ";
		$sql .= "         When a.subcode = 2 Then '업무' ";
		$sql .= "         When a.subcode = 3 Then '기념일' ";
		$sql .= "         When a.subcode = 4 Then '프로젝트' ";
		$sql .= "         Else ";
		$sql .= "             '' ";
		$sql .= "     End subcode_name, ";
		$sql .= "     Case ";
		$sql .= "         When a.GroupCode= 99 Then '04B5FF' ";
		$sql .= "         Else ";
		$sql .= "             'FF8932' ";
		$sql .= "     End group_color ";
		$sql .= " From ";
		$sql .= "     schedule_job_tbl a ";
		$sql .= " Where a.GroupCode In ('31', '33', '99') ";
		$sql .= " And a.pdate Between '$start_date' And '$end_date' ";
		$sql .= " And a.contents Like '%$search_text%' ";
		$sql .= " Order By ";
		$sql .= "     a.pdate, ";
		$sql .= "     a.set_color ";
		$resource = mysql_query($sql, $db);
		
		if (!$resource) {
			echo "mysql_query(schedule_job_tbl resource) error";
			return;
		}
		
		$schedule = array();
		while ($row = mysql_fetch_array($resource, MYSQL_ASSOC)) {
			$pdate = $row[pdate];
			
			if (!is_array($schedule[$pdate])) {
				$schedule[$pdate] = array();
			}
			
			array_push($schedule[$pdate], $row);
		}
		
		$this->assign("today", $today);
		$this->assign("dayInfo", $dayInfo);
		$this->assign("schedule", $schedule);
		$this->assign("year", $year);
		$this->assign("month", $month);
		$this->assign("start_date", $start_date);
		$this->assign("end_date", $end_date);
		$this->assign("year_combo", $this->getComboBox("year"));
		$this->assign("month_combo", $this->getComboBox("month"));
		
		$this->display("intranet/common_contents/work_diary/diarySearchList.tpl");
		
	}
	
	function assignRequest() {
		foreach ($_REQUEST As $key => $value) {
			$this->assign($key, $value);
		}
	}
	
	function getComboBox($kind) {
		$comboBox = array();
		
		if ($kind == "year") {
			$maxYear = date("Y");
			$minYear = $maxYear - 5;
			
			$index = 0;
			for ($i = $maxYear; $i >= $minYear; $i--) {
				$item = array();
				
				$item[key] = $i;
				$item[value] = $i;
				
				array_push($comboBox, $item);
			}
		} else if ($kind == "month") {
			$months = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
			
			for ($i = 0; $i < count($months); $i++) {
				$item = array();
				
				$item[key] = $months[$i];
				$item[value] = $months[$i];
				
				array_push($comboBox, $item);
			}
		}
		
		return $comboBox;
	}

	/* ------------------------------------------------------------------------------ */
}//class Main End
?>
