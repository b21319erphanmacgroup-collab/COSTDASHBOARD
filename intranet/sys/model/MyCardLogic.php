<?php
	/* **********************************
	* 메인화면 : LEFT : 개인정보 : 인사카드
	* 사원정보 조회
	* ------------------------------------
	* 2015-12-   :
	* 2014-12-18 : 세션-> 쿠키값 단계별으로 체크 : SUK
	* 2014-12-18 : php.날짜변수 관련 공통 페이지 삽입(/sys/inc/getNeedDate.php : 파일생성) : SUK
	* 2014-12-16 : 파일정리: SUK 
	*************************************** */
	/* ----------------------------------- */
	require('../../../SmartyConfig.php');	
	require_once($SmartyClassPath);
	/* ----------------------------------- */
	require('../inc/function_intranet.php');//자주쓰는 기능 Function
	/* ----------------------------------- */
	include "../inc/getCookieOfUser.php";	//사용자에 관한 쿠키값
	include "../inc/getNeedDate.php";		//로직에 사용되는 PHP시간&날짜 정의
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
	}else{
		/* ----------------------------------- */
		$memberID	=	$_GET['memberID'];
		$MemberNo	=	$memberID;
		$MemberNo	=	strtoupper($MemberNo);
		/* ----------------------------------- */
		require('../popup/setInfo.php');
		/* ----------------------------------- */
	}//if End
	/* ----------------------------------- */
	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
	/* ----------------------------------- */
?>
<?php
class MyCardLogic extends Smarty {
	// 생성자
	function MyCardLogic()
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
	/* ----------------------------------------------------------------------------------------------- */
	/* 인사정보 입력 실행로직------------------------------------------------------------------------------ */
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
	/* ----------------- */
	global  $db;
	/* ----------------- */
	global  $ajax_pre_pk;			//pk
	global  $ajax_sendDate;	    
	global  $ajax_radio_kind01;		//부서(1), 개인(2) 구분
	global  $ajax_radio_kind02;		//	부서일정 : 1:일정, 2:업무, 3:기념일, 4:프로젝트 || 개인일정 : 1:업무, 2:일정, 3:기념일
	global  $ajax_diary_content;	//내용
	/* ----------------- */
	$GroupCode = (int)$GroupCode;
	/* ----------------- */
	/* 기본정보01 member_tbl*/
	$m_MemberNo		= ($_POST['m_MemberNo']==""?"":$_POST['m_MemberNo']);			//           
	$m_Pasword     	= ($_POST['m_Pasword']==""?"":$_POST['m_Pasword']);  			//비밀번호   
	$m_korName     	= ($_POST['m_korName']==""?"":$_POST['m_korName']);  			//한글이름   
	$m_RankCode    	= ($_POST['m_RankCode']==""?"":$_POST['m_RankCode']);			//랭크코드   
	$m_GroupCode   	= ($_POST['m_GroupCode']==""?"":$_POST['m_GroupCode']);			//그룹코드   
	$m_WorkPosition	= ($_POST['m_WorkPosition']==""?"":$_POST['m_WorkPosition']);	//           
	$m_chiName     	= ($_POST['m_chiName']==""?"":$_POST['m_chiName']);  			//한자이름   
	$m_engName     	= ($_POST['m_engName']==""?"":$_POST['m_engName']);  			//영문이름   
	$m_Degree      	= ($_POST['m_Degree']==""?"":$_POST['m_Degree']);				//           
	$m_Technical   	= ($_POST['m_Technical']==""?"":$_POST['m_Technical']);			//           
	$m_ExtNo       	= ($_POST['m_ExtNo']==""?"":$_POST['m_ExtNo']);					//           
	$m_EntryDate   	= ($_POST['m_EntryDate']==""?"":$_POST['m_EntryDate']);			//입사일자   
	$m_LeaveDate   	= ($_POST['m_LeaveDate']==""?"":$_POST['m_LeaveDate']);			//퇴사일자   
	$m_JuminNo     	= ($_POST['m_JuminNo']==""?"":$_POST['m_JuminNo']);  			//주민번호   
	$m_Phone       	= ($_POST['m_Phone']==""?"":$_POST['m_Phone']);					//자택전화   
	$m_Mobile      	= ($_POST['m_Mobile']==""?"":$_POST['m_Mobile']);				//모바일폰   
	$m_eMail       	= ($_POST['m_eMail']==""?"":$_POST['m_eMail']);					//이메일주소 
	$m_OrignAddress	= ($_POST['m_OrignAddress']==""?"":$_POST['m_OrignAddress']);	//본적주소   
	$m_Address     	= ($_POST['m_Address']==""?"":$_POST['m_Address']);  			//주소       
	$m_Author      	= ($_POST['m_Author']==""?"":$_POST['m_Author']);				//           
	$m_Certificate 	= ($_POST['m_Certificate']==""?"":$_POST['m_Certificate']);  	//           
	$m_Meritorious 	= ($_POST['m_Meritorious']==""?"":$_POST['m_Meritorious']);  	//           
	$m_Disabled    	= ($_POST['m_Disabled']==""?"":$_POST['m_Disabled']);			//           
	$m_UpdateDate  	= ($_POST['m_UpdateDate']==""?"":$_POST['m_UpdateDate']);		//           
	$m_UpdateUser  	= ($_POST['m_UpdateUser']==""?"":$_POST['m_UpdateUser']);		//           
	$m_Show_Insa   	= ($_POST['m_Show_Insa']==""?"":$_POST['m_Show_Insa']);			//           
	$m_RegStDate   	= ($_POST['m_RegStDate']==""?"":$_POST['m_RegStDate']);			//           
	$m_RegEdDate   	= ($_POST['m_RegEdDate']==""?"":$_POST['m_RegEdDate']);			//           
	$m_Engineer    	= ($_POST['m_Engineer']==""?"":$_POST['m_Engineer']);			//           
	$m_Company     	= ($_POST['m_Company']==""?"":$_POST['m_Company']);  			//           
	$m_EntryType   	= ($_POST['m_EntryType']==""?"":$_POST['m_EntryType']);			//           
	$m_LeaveReason 	= ($_POST['m_LeaveReason']==""?"":$_POST['m_LeaveReason']);  	//           
	$m_married     	= ($_POST['m_married']==""?"":$_POST['m_married']);  			//혼인여부   

	$m_birthday     	= ($_POST['m_birthday']==""?"":$_POST['m_birthday']);  			//생일 : YYYY-MM-DD : 메인화면에 보여줄 생일 (음력은 양력으로 변경된 생일)(20150708)   
	$m_originalbirthday     	= ($_POST['m_originalbirthday']==""?"":$_POST['m_originalbirthday']);  			//생일 : YYYY-MM-DD : 추가요청(20150703)   
	$m_birthdaytype     	= ($_POST['m_birthdaytype']==""?"":$_POST['m_birthdaytype']);  			//생일 타입 : 음력 / 양력 : 추가요청(20150708)   

	/* 기본정보02 */
	$s_Position		= ($_POST['s_Position']==""?"":$_POST['s_Position']);			//직급/직위  
	$s_GroupName    = ($_POST['s_GroupName']==""?"":$_POST['s_GroupName']);			//그룹명/팀명

	/* 기본정보03 : 장애 및 보훈정보 member_details_tbl*/
	$md_disabled_type   = ($_POST['md_disabled_type']==""?"":$_POST['md_disabled_type']);		//장애유형         
	$md_disabled_grade	= ($_POST['md_disabled_grade']==""?"":$_POST['md_disabled_grade']);		//장애등급         
	$md_disabled_date   = ($_POST['md_disabled_date']==""?"":$_POST['md_disabled_date']);		//장애인인정시기   
	$md_veteran_number	= ($_POST['md_veteran_number']==""?"":$_POST['md_veteran_number']);		//보훈번호         
	$md_veteran_type    = ($_POST['md_veteran_type']==""?"":$_POST['md_veteran_type']);			//보훈구분         

	/* 인사카드 : 학력 member_school_tbl*/
	for($i=1;$i<7;$i++){
		$ms_SchoolStart[$i]		= ($_POST['ms_SchoolStart'.$i]==""?"":$_POST['ms_SchoolStart'.$i]);			
		$ms_SchoolEnd[$i]		= ($_POST['ms_SchoolEnd'.$i]==""?"":$_POST['ms_SchoolEnd'.$i]);			
		$ms_SchoolName[$i]		= ($_POST['ms_SchoolName'.$i]==""?"":$_POST['ms_SchoolName'.$i]);		
		$ms_Specialization[$i]	= ($_POST['ms_Specialization'.$i]==""?"":$_POST['ms_Specialization'.$i]);
	}//for End

	/* 인사카드 : 경력 */
	for($i=1;$i<7;$i++){
		$mcr_CareerStart[$i]	= ($_POST['mcr_CareerStart'.$i]==""?"":$_POST['mcr_CareerStart'.$i]);		//경력_입사일
		$mcr_CareerEnd[$i]		= ($_POST['mcr_CareerEnd'.$i]==""?"":$_POST['mcr_CareerEnd'.$i]);			//경력_퇴사일
		$mcr_CompanyName[$i]	= ($_POST['mcr_CompanyName'.$i]==""?"":$_POST['mcr_CompanyName'.$i]);		//경력_회사명
		$mcr_Position[$i]		= ($_POST['mcr_Position'.$i]==""?"":$_POST['mcr_Position'.$i]);				//경력_직위
	}//for End

	/* 인사카드 : 가족사항 member_family_tbl*/
	for($i=1;$i<7;$i++){
		$mf_Relation[$i]	= ($_POST['mf_Relation'.$i]==""?"":$_POST['mf_Relation'.$i]);		// 관계     
		$mf_Name[$i]		= ($_POST['mf_Name'.$i]==""?"":$_POST['mf_Name'.$i]);				// 성명    
		$mf_birthday[$i]	= ($_POST['mf_birthday'.$i]==""?"":$_POST['mf_birthday'.$i]);		// 생년월일
		$mf_LastSchool[$i]	= ($_POST['mf_LastSchool'.$i]==""?"":$_POST['mf_LastSchool'.$i]);	// 학력    
		$mf_Occupation[$i]	= ($_POST['mf_Occupation'.$i]==""?"":$_POST['mf_Occupation'.$i]);	// 직업
	}//for End
 

	/* 인사카드 : 자격면허 member_certification_tbl*/
	for($i=1;$i<7;$i++){
		$mc_CertificationName[$i]	= ($_POST['mc_CertificationName'.$i]==""?"":$_POST['mc_CertificationName'.$i]);	//종류
		$mc_ObtainDate[$i]			= ($_POST['mc_ObtainDate'.$i]==""?"":$_POST['mc_ObtainDate'.$i]);				//취득일
		$mc_CertificationNo[$i]	= ($_POST['mc_CertificationNo'.$i]==""?"":$_POST['mc_CertificationNo'.$i]);		//자격번호
	}//for End


	/* 인사카드 : 보수교육 member_supplyeducation_tbl*/
	for($i=1;$i<7;$i++){
		$mse_EducationName[$i]		= ($_POST['mse_EducationName'.$i]==""?"":$_POST['mse_EducationName'.$i]);		//과정      
		$mse_OrganizationName[$i]	= ($_POST['mse_OrganizationName'.$i]==""?"":$_POST['mse_OrganizationName'.$i]);	//교육기관명
		$mse_EducationStart[$i]		= ($_POST['mse_EducationStart'.$i]==""?"":$_POST['mse_EducationStart'.$i]);		//시작일   
		$mse_EducationEnd[$i]		= ($_POST['mse_EducationEnd'.$i]==""?"":$_POST['mse_EducationEnd'.$i]);			//종료일   
	}//for End
	/* ****************************************************************************************************************** */
	/* 인사카드 : 상훈 member_award_tbl*/
	for($i=1;$i<7;$i++){
		$ma_AwardDate[$i]		= ($_POST['ma_AwardDate'.$i]==""?"":$_POST['ma_AwardDate'.$i]);			//년월일
		$ma_AwardName[$i]		= ($_POST['ma_AwardName'.$i]==""?"":$_POST['ma_AwardName'.$i]);			//종류및근거
		$ma_Organization[$i]	= ($_POST['ma_Organization'.$i]==""?"":$_POST['ma_Organization'.$i]);	//상훈기관
	}//for End
	/* ****************************************************************************************************************** */
	/* ****************************************************************************************************************** */
	//인사카드 : 기본정보01 업데이트 
		$m_sql= " UPDATE member_tbl SET								";
		$m_sql= $m_sql."  chiName		='".$m_chiName."'				";
		$m_sql= $m_sql." ,engName	='".$m_engName."'			";
		$m_sql= $m_sql." ,JuminNo		='".$m_JuminNo."'				";
		$m_sql= $m_sql." ,eMail			='".$m_eMail."'					";
		$m_sql= $m_sql." ,OrignAddress	='".$m_OrignAddress."'	";
		$m_sql= $m_sql." ,address		='".$m_Address."'				";
		$m_sql= $m_sql." ,Phone		='".$m_Phone."'				";
		$m_sql= $m_sql." ,Mobile		='".$m_Mobile."'				";
		$m_sql= $m_sql." ,married		='".$m_married."'				";
		$m_sql= $m_sql." ,UpdateDate='".$date_today."'			";
		$m_sql= $m_sql." ,birthday		='".$m_originalbirthday."'				";         //생일 : YYYY-MM-DD : 메인화면에 보여줄 생일 (음력은 양력으로 변경된 생일)(20150708) 년초 자동으로 1회만 입력 됨
		$m_sql= $m_sql." ,originalbirthday		='".$m_originalbirthday."'	  	";//생일 : YYYY-MM-DD : 추가요청(20150703)
		$m_sql= $m_sql." ,BirthdayType		='".$m_birthdaytype."'				";//생일 타입 : 음력 / 양력 : 추가요청(20150708)   
		$m_sql= $m_sql." WHERE											";
		$m_sql= $m_sql." MemberNo = '".$MemberNo."'				";
////////////////////////
mysql_query($m_sql,$db);
////////////////////////
	/* ****************************************************************************************************************** */
	//인사카드 : 보훈장애 삭제
	$query20 = "delete from member_details_tbl where MemberNo = '".$MemberNo."'	";
////////////////////////
mysql_query($query20,$db);
////////////////////////
	/* ------------------ */
	//인사카드 : 보훈장애 재입력
		$query21= " INSERT INTO member_details_tbl 	";
		$query21= $query21." (MemberNo,disabled_type,disabled_grade,disabled_date,veteran_number,veteran_type,UpdateDate,UpdateUser) ";
		$query21= $query21." VALUES	";
		$query21= $query21." ('".$MemberNo."','".$md_disabled_type."','".$md_disabled_grade."','".$md_disabled_date."','".$md_veteran_number."','".$md_veteran_type."','".$date_today."','web')	";
////////////////////////
mysql_query($query21);
////////////////////////
	/* ****************************************************************************************************************** */
	//인사카드 : 학력 삭제
	$query40 = "delete from member_school_tbl where MemberNo = '".$MemberNo."'	";
////////////////////////
mysql_query($query40,$db);
////////////////////////
	/* ------------------ */
	//인사카드 : 학력 재입력
	for($j=1;$j<7;$j++){
		if( $ms_SchoolName[$j] != ""){
			$query41= " INSERT INTO member_school_tbl 	";
			$query41= $query41." (MemberNo,SchoolStart,SchoolEnd,SchoolName,Specialization,UpdateDate,UpdateUser) ";
			$query41= $query41." VALUES	";
			$query41= $query41." ('".$MemberNo."','".$ms_SchoolStart[$j]."','".$ms_SchoolEnd[$j]."','".$ms_SchoolName[$j]."','".$ms_Specialization[$j]."','".$date_today."','web')	";
////////////////////////
mysql_query($query41);
////////////////////////
		} //if End
	}//for End
	/* ****************************************************************************************************************** */
	/* ****************************************************************************************************************** */
	//인사카드 : 경력 삭제
	$query50 = "delete from member_career_tbl where MemberNo = '".$MemberNo."'	";
////////////////////////
mysql_query($query50,$db);
////////////////////////
	/* ------------------ */
	//인사카드 : 경력 재입력
	for($j=1;$j<7;$j++){
		if( $mcr_CompanyName[$j] != ""){
			$query51= " INSERT INTO member_career_tbl 	";
			$query51= $query51." (MemberNo,CareerStart,CareerEnd,CompanyName,Position,UpdateDate,UpdateUser) ";
			$query51= $query51." VALUES	";
			$query51= $query51." ('".$MemberNo."','".$mcr_CareerStart[$j]."','".$mcr_CareerEnd[$j]."','".$mcr_CompanyName[$j]."','".$mcr_Position[$j]."','".$date_today."','web')	";
		////////////////////////
		mysql_query($query51);
		////////////////////////
		} //if End
	}//for End
	/* ****************************************************************************************************************** */
	/* ****************************************************************************************************************** */
	//인사카드 : 가족사항 삭제
	$query60 = "delete from member_family_tbl where MemberNo = '".$MemberNo."'	";
////////////////////////
mysql_query($query60,$db);
////////////////////////
	/* ------------------ */
	//인사카드 : 가족사항 재입력
	for($j=1;$j<7;$j++){
		if( $mf_Name[$j] != ""){
			$query61= " INSERT INTO member_family_tbl 	";
			$query61= $query61." (MemberNo,Relation,Name,birthday,LastSchool,Occupation,UpdateDate,UpdateUser)  ";
			$query61= $query61." VALUES	";
			$query61= $query61." ('".$MemberNo."','".$mf_Relation[$j]."','".$mf_Name[$j]."','".$mf_birthday[$j]."','".$mf_LastSchool[$j]."','".$mf_Occupation[$j]."','".$date_today."','web')	";
////////////////////////
mysql_query($query61);
////////////////////////
		} //if End
	}//for End
	/* ****************************************************************************************************************** */
	/* ****************************************************************************************************************** */
	//인사카드 : 자격면허 삭제
	$query70 = "delete from member_certification_tbl where MemberNo = '".$MemberNo."'	";
////////////////////////
mysql_query($query70,$db);
////////////////////////
	/* ------------------ */
	//인사카드 : 자격면허 재입력
	for($j=1;$j<7;$j++){
		if( $mc_CertificationName[$j] != ""){
			$query71= " INSERT INTO member_certification_tbl 	";
			$query71= $query71." (MemberNo,CertificationName,ObtainDate,CertificationNo,UpdateDate,UpdateUser,no)  ";
			$query71= $query71." VALUES	";
			$query71= $query71." ('".$MemberNo."','".$mc_CertificationName[$j]."','".$mc_ObtainDate[$j]."','".$mc_CertificationNo[$j]."','".$date_today."','web','".$i."')	";
////////////////////////
mysql_query($query71);
////////////////////////
		} //if End
	}//for End
	/* ****************************************************************************************************************** */

	/* ****************************************************************************************************************** */
	//인사카드 : 보수교육 삭제
	$query80 = "delete from member_supplyeducation_tbl where MemberNo = '".$MemberNo."'	";
////////////////////////
mysql_query($query80,$db);
////////////////////////
	/* ------------------ */
	//인사카드 : 보수교육 재입력
	for($j=1;$j<7;$j++){
		if( $mse_EducationName[$j] != ""){
			$query81= " INSERT INTO member_supplyeducation_tbl 	";
			$query81= $query81." (MemberNo,EducationName,OrganizationName,EducationStart,EducationEnd,UpdateDate,UpdateUser)  ";
			$query81= $query81." VALUES	";
			$query81= $query81." ('".$MemberNo."','".$mse_EducationName[$j]."','".$mse_OrganizationName[$j]."','".$mse_EducationStart[$j]."','".$mse_EducationEnd[$j]."','".$date_today."','web')	";
////////////////////////
mysql_query($query81);
////////////////////////
		} //if End
	}//for End
	/* ****************************************************************************************************************** */
	/* ****************************************************************************************************************** */
	//인사카드 : 상훈 삭제
	$query90 = "delete from member_award_tbl where MemberNo = '".$MemberNo."'	";
////////////////////////
mysql_query($query90,$db);
////////////////////////
	/* ------------------ */
	//인사카드 : 상훈 재입력
	for($j=1;$j<7;$j++){
		if( $ma_AwardName[$j] != ""){
			$query91= " INSERT INTO member_award_tbl 	";
			$query91= $query91." (MemberNo,AwardDate,AwardName,Organization,UpdateDate,UpdateUser)  ";
			$query91= $query91." VALUES	";
			$query91= $query91." ('".$MemberNo."','".$ma_AwardDate[$j]."','".$ma_AwardName[$j]."','".$ma_Organization[$j]."','".$date_today."','web')	";
////////////////////////
mysql_query($query91);
////////////////////////
		} //if End
	}//for End
	/* ****************************************************************************************************************** */
////////////////////////
echo "1";
////////////////////////
	}  //UpdateAction End
	/* ------------------------------------------------------------------------------ */
	

/*******************************************************************************************************/
	/* 인사카드 인사정보 DB값 가져오기---------------------------------------------------------------- */
	function GetData01()
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
	//============================================================================
	// 사진
	//============================================================================
	$src_photo = "../../../erpphoto/".$MemberNo.".jpg";
	$src_photo1 = "../../../erpphoto/".$MemberNo.".gif";
	if(file_exists($src_photo)) {
		$MemberPic=$src_photo;
	}else if(file_exists($src_photo1))
	{ 
		$MemberPic=$src_photo2;
	}
	else
	{
		$MemberPic="../../../erpphoto/noimage.gif";
	}
	$this->assign('MemberPic' ,$MemberPic); 
/*****************************************************************/
		//기본정보01
		$sql01= "SELECT										";
		$sql01= $sql01."  M.MemberNo		as m_MemberNo		";	//
		$sql01= $sql01." ,M.Pasword     	as m_Pasword     	";	//비밀번호
		$sql01= $sql01." ,M.korName     	as m_korName     	";	//한글이름
		$sql01= $sql01." ,M.RankCode    	as m_RankCode    	";	//랭크코드
		$sql01= $sql01." ,M.GroupCode   	as m_GroupCode   	";	//그룹코드
		$sql01= $sql01." ,M.WorkPosition	as m_WorkPosition	";	//
		$sql01= $sql01." ,M.chiName     	as m_chiName     	";	//한자이름
		$sql01= $sql01." ,M.engName     	as m_engName     	";	//영문이름
		$sql01= $sql01." ,M.Degree      	as m_Degree      	";	//
		$sql01= $sql01." ,M.Technical   	as m_Technical   	";	//
		$sql01= $sql01." ,M.ExtNo       	as m_ExtNo       	";	//
		$sql01= $sql01." ,M.EntryDate   	as m_EntryDate   	";	//입사일자
		$sql01= $sql01." ,M.LeaveDate   	as m_LeaveDate   	";	//퇴사일자
		$sql01= $sql01." ,M.JuminNo     	as m_JuminNo     	";	//주민번호
		$sql01= $sql01." ,M.Phone       	as m_Phone       	";	//자택전화
		$sql01= $sql01." ,M.Mobile      	as m_Mobile      	";	//모바일폰
		$sql01= $sql01." ,M.eMail       	as m_eMail       	";	//이메일주소
		$sql01= $sql01." ,M.OrignAddress	as m_OrignAddress	";	//본적주소
		$sql01= $sql01." ,M.Address     	as m_Address     	";	//주소
		$sql01= $sql01." ,M.Author      	as m_Author      	";	//
		$sql01= $sql01." ,M.Certificate 	as m_Certificate 	";	//
		$sql01= $sql01." ,M.Meritorious 	as m_Meritorious 	";	//
		$sql01= $sql01." ,M.Disabled    	as m_Disabled    	";	//
		$sql01= $sql01." ,M.UpdateDate  	as m_UpdateDate  	";	//
		$sql01= $sql01." ,M.UpdateUser  	as m_UpdateUser  	";	//
		$sql01= $sql01." ,M.Show_Insa   	as m_Show_Insa   	";	//
		$sql01= $sql01." ,M.RegStDate   	as m_RegStDate   	";	//
		$sql01= $sql01." ,M.RegEdDate   	as m_RegEdDate   	";	//
		$sql01= $sql01." ,M.Engineer    	as m_Engineer    	";	//
		$sql01= $sql01." ,M.Company     	as m_Company     	";	//
		$sql01= $sql01." ,M.EntryType   	as m_EntryType   	";	//
		$sql01= $sql01." ,M.LeaveReason 	as m_LeaveReason 	";	//
		$sql01= $sql01." ,M.married     	as m_married     	";	//혼인여부
		$sql01= $sql01." ,M.birthday     	as m_birthday     	";	//생일 : YYYY-MM-DD : 메인화면에 보여줄 생일 (음력은 양력으로 변경된 생일)(20150708)
		$sql01= $sql01." ,M.originalbirthday     	as m_originalbirthday     	";	//생일 : YYYY-MM-DD : 추가요청(20150703) 
		$sql01= $sql01." ,M.BirthdayType     	as m_birthdaytype     	";	//생일 타입 : 음력 / 양력 : 추가요청(20150708)
		$sql01= $sql01."	FROM								";
		$sql01= $sql01."		member_tbl M					";
		$sql01= $sql01." WHERE								";
		$sql01= $sql01." M.MemberNo = '".$MemberNo."'		";
	/* -------------------------------------------------------------------------- */
	$result_member01 = mysql_query($sql01,$db);
	$re_row_member01 = mysql_num_rows($result_member01);
	if($re_row_member01 != 0) {
		$m_MemberNo		= @mysql_result($result_member01,0,"m_MemberNo"); 
		$m_Pasword     	= @mysql_result($result_member01,0,"m_Pasword");
		$m_korName     	= @mysql_result($result_member01,0,"m_korName");
		$m_RankCode    	= @mysql_result($result_member01,0,"m_RankCode");
		$m_GroupCode   	= @mysql_result($result_member01,0,"m_GroupCode");
		$m_WorkPosition	= @mysql_result($result_member01,0,"m_WorkPosition"); 
		$m_chiName     	= @mysql_result($result_member01,0,"m_chiName");
		$m_engName     	= @mysql_result($result_member01,0,"m_engName");
		$m_Degree      	= @mysql_result($result_member01,0,"m_Degree");
		$m_Technical   	= @mysql_result($result_member01,0,"m_Technical");
		$m_ExtNo       	= @mysql_result($result_member01,0,"m_ExtNo"); 
		$m_EntryDate   	= @mysql_result($result_member01,0,"m_EntryDate");
		$m_LeaveDate   	= @mysql_result($result_member01,0,"m_LeaveDate");
		$m_JuminNo     	= @mysql_result($result_member01,0,"m_JuminNo");
		$m_Phone       	= @mysql_result($result_member01,0,"m_Phone");
		$m_Mobile      	= @mysql_result($result_member01,0,"m_Mobile"); 
		$m_eMail       	= @mysql_result($result_member01,0,"m_eMail");
		$m_OrignAddress	= @mysql_result($result_member01,0,"m_OrignAddress");
		$m_Address     	= @mysql_result($result_member01,0,"m_Address");
		$m_Author      	= @mysql_result($result_member01,0,"m_Author");
		$m_Certificate 	= @mysql_result($result_member01,0,"m_Certificate"); 
		$m_Meritorious 	= @mysql_result($result_member01,0,"m_Meritorious");
		$m_Disabled    	= @mysql_result($result_member01,0,"m_Disabled");
		$m_UpdateDate  	= @mysql_result($result_member01,0,"m_UpdateDate");
		$m_UpdateUser  	= @mysql_result($result_member01,0,"m_UpdateUser");
		$m_Show_Insa   	= @mysql_result($result_member01,0,"m_Show_Insa"); 
		$m_RegStDate   	= @mysql_result($result_member01,0,"m_RegStDate");
		$m_RegEdDate   	= @mysql_result($result_member01,0,"m_RegEdDate");
		$m_Engineer    	= @mysql_result($result_member01,0,"m_Engineer");
		$m_Company     	= @mysql_result($result_member01,0,"m_Company");
		$m_EntryType   	= @mysql_result($result_member01,0,"m_EntryType");
		$m_LeaveReason 	= @mysql_result($result_member01,0,"m_LeaveReason");
		$m_married     	= @mysql_result($result_member01,0,"m_married");
		$m_birthday     	= @mysql_result($result_member01,0,"m_birthday");		//생일 : YYYY-MM-DD : 메인화면에 보여줄 생일 (음력은 양력으로 변경된 생일)(20150708)
		$m_originalbirthday     	= @mysql_result($result_member01,0,"m_originalbirthday");		//생일 : YYYY-MM-DD : 추가요청(20150703) 
		$m_birthdaytype     	= @mysql_result($result_member01,0,"m_birthdaytype");		//생일 타입 : 음력 / 양력 : 추가요청(20150708)
	} //End
	/* -------------------------------------------------------------------------- */
		$this->assign('m_MemberNo'    		,$m_MemberNo);     
		$this->assign('m_Pasword'     		,$m_Pasword);      
		$this->assign('m_korName'     		,$m_korName);      
		$this->assign('m_RankCode'    		,$m_RankCode);     
		$this->assign('m_GroupCode'   		,$m_GroupCode);    
		$this->assign('m_WorkPosition'		,$m_WorkPosition); 
		$this->assign('m_chiName'     		,$m_chiName);      
		$this->assign('m_engName'     		,$m_engName);      
		$this->assign('m_Degree'      		,$m_Degree);       
		$this->assign('m_Technical'   		,$m_Technical);    
		$this->assign('m_ExtNo'       		,$m_ExtNo);        
		$this->assign('m_EntryDate'   		,$m_EntryDate);    
		$this->assign('m_LeaveDate'   		,$m_LeaveDate);    
		$this->assign('m_JuminNo'     		,$m_JuminNo);      
		$this->assign('m_Phone'       		,$m_Phone);        
		$this->assign('m_Mobile'      		,$m_Mobile);       
		$this->assign('m_eMail'       		,$m_eMail);        
		$this->assign('m_OrignAddress'		,$m_OrignAddress); 
		$this->assign('m_Address'     		,$m_Address);      
		$this->assign('m_Author'      		,$m_Author);       
		$this->assign('m_Certificate' 		,$m_Certificate);  
		$this->assign('m_Meritorious' 		,$m_Meritorious);  
		$this->assign('m_Disabled'    		,$m_Disabled);     
		$this->assign('m_UpdateDate'  		,$m_UpdateDate);   
		$this->assign('m_UpdateUser'  		,$m_UpdateUser);   
		$this->assign('m_Show_Insa'   		,$m_Show_Insa);    
		$this->assign('m_RegStDate'   		,$m_RegStDate);    
		$this->assign('m_RegEdDate'   		,$m_RegEdDate);    
		$this->assign('m_Engineer'    		,$m_Engineer);     
		$this->assign('m_Company'     		,$m_Company);      
		$this->assign('m_EntryType'   		,$m_EntryType);    
		$this->assign('m_LeaveReason' 		,$m_LeaveReason);  
		$this->assign('m_married'     		,$m_married); 
		$this->assign('m_birthday'     		,$m_birthday); //생일 : YYYY-MM-DD : 메인화면에 보여줄 생일 (음력은 양력으로 변경된 생일)(20150708)
		$this->assign('m_originalbirthday'     		,$m_originalbirthday); //생일 : YYYY-MM-DD : 추가요청(20150703)  
		$this->assign('m_birthdaytype'     		,$m_birthdaytype); //생일 타입 : 음력 / 양력 : 추가요청(20150708) 
		
/*****************************************************************/
		//기본정보02 : 직위,팀명
		$sql02= "SELECT														";
		$sql02= $sql02."  S3.Position	as s_GroupName						";	//직급/직위
		$sql02= $sql02." ,S1.Name		as s_Position						";	//그룹명/팀명
		$sql02= $sql02." FROM												";
		$sql02= $sql02." 	systemconfig_tbl S1								";
		$sql02= $sql02." 	,												";
		$sql02= $sql02." 	(												";
		$sql02= $sql02." 		SELECT										";
		$sql02= $sql02." 		S2.Name as Position							";
		$sql02= $sql02." 		FROM										";
		$sql02= $sql02." 		systemconfig_tbl S2							";
		$sql02= $sql02." 		WHERE										";
		$sql02= $sql02." 		S2.SysKey='GroupCode'						";
		$sql02= $sql02." 		AND											";
		$sql02= $sql02." 		S2.Code ='".sprintf("%02d",$GroupCode)."'	";
		$sql02= $sql02." 	) s3											";
		$sql02= $sql02." WHERE												";
		$sql02= $sql02." 		S1.SysKey='PositionCode'					";
		$sql02= $sql02." 		AND											";
		$sql02= $sql02." 		S1.code ='".$RankCode."'					";
	/* -------------------------------------------------------------------------- */
	$result_member02 = mysql_query($sql02,$db);
	$re_row_member02 = mysql_num_rows($result_member02);
	if($re_row_member02 != 0) {
		$s_Position		= @mysql_result($result_member02,0,"s_Position"); 
		$s_GroupName    = @mysql_result($result_member02,0,"s_GroupName");
	} //End
	/* -------------------------------------------------------------------------- */
		$this->assign('s_Position'  ,$s_Position);
		$this->assign('s_GroupName' ,$s_GroupName);
/*****************************************************************/
		//기본정보03 : 장애 및 보훈정보
		$sql03= "SELECT											";
		$sql03= $sql03."  MD.MemberNo      	as md_MemberNo      	";	//사원번호
		$sql03= $sql03." ,MD.disabled_type 	as md_disabled_type 	";	//장애유형
		$sql03= $sql03." ,MD.disabled_grade	as md_disabled_grade	";	//장애등급
		$sql03= $sql03." ,MD.disabled_date 	as md_disabled_date 	";	//장애인인정시기
		$sql03= $sql03." ,MD.veteran_number	as md_veteran_number	";	//보훈번호
		$sql03= $sql03." ,MD.veteran_type  	as md_veteran_type  	";	//보훈구분
		$sql03= $sql03." ,MD.UpdateDate    	as md_UpdateDate    	";	//업데이트 일자
		$sql03= $sql03." ,MD.UpdateUser    	as md_UpdateUser    	";	//등록자 사원번호
		$sql03= $sql03." FROM										";
		$sql03= $sql03." 	member_details_tbl MD					";
		$sql03= $sql03." WHERE										";
		$sql03= $sql03." MD.MemberNo = '".$MemberNo."'				";
	/* -------------------------------------------------------------------------- */
	$result_member03 = mysql_query($sql03,$db);
	$re_row_member03 = mysql_num_rows($result_member03);
	if($re_row_member03 != 0) {
		$md_MemberNo      	= @mysql_result($result_member03,0,"md_MemberNo"); 
		$md_disabled_type   = @mysql_result($result_member03,0,"md_disabled_type");
		$md_disabled_grade	= @mysql_result($result_member03,0,"md_disabled_grade"); 
		$md_disabled_date   = @mysql_result($result_member03,0,"md_disabled_date");
		$md_veteran_number	= @mysql_result($result_member03,0,"md_veteran_number"); 
		$md_veteran_type    = @mysql_result($result_member03,0,"md_veteran_type");
		$md_UpdateDate    	= @mysql_result($result_member03,0,"md_UpdateDate"); 
		$md_UpdateUser      = @mysql_result($result_member03,0,"md_UpdateUser");
	} //End
	/* -------------------------------------------------------------------------- */
		$this->assign('md_MemberNo'       ,$md_MemberNo);      
		$this->assign('md_disabled_type'  ,$md_disabled_type); 
		$this->assign('md_disabled_grade' ,$md_disabled_grade);
		$this->assign('md_disabled_date'  ,$md_disabled_date); 
		$this->assign('md_veteran_number' ,$md_veteran_number);
		$this->assign('md_veteran_type'   ,$md_veteran_type);  
		$this->assign('md_UpdateDate'     ,$md_UpdateDate);    
		$this->assign('md_UpdateUser'     ,$md_UpdateUser);    
		/*****************************************************************/
	}//GetData01()
/*******************************************************************************************************/
	/* 인사카드 인사정보 DB값 가져오기---- */
	function GetData02()
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
/*****************************************************************/
		//인사카드 : 학력
		//$sql04 ="select *	from member_school_tbl where MemberNo='$MemberNo' order by SchoolStart";
		/* ------------------------------------------------------------------- */
		$sql_cnt04    = "SELECT count(*) cnt04 FROM 	member_school_tbl	where MemberNo='".$MemberNo."'";
		$result_cnt04 = mysql_query($sql_cnt04,$db);
		$re_row_cnt04 = mysql_num_rows($result_cnt04);
		if($re_row_cnt04 != 0) {
			$cnt04    = @mysql_result($result_cnt04,0,"cnt04"); 
			$this->assign('cnt04' ,$cnt04); 
		} //End
		/* ------------------------------------------------------------------- */
		$sql04= "SELECT											";
		$sql04= $sql04."  MS.MemberNo      	as ms_MemberNo      	";	//사원번호
		$sql04= $sql04." ,MS.SchoolStart   	as ms_SchoolStart   	";	//시작일
		$sql04= $sql04." ,MS.SchoolEnd     	as ms_SchoolEnd     	";	//종료일
		$sql04= $sql04." ,MS.SchoolName    	as ms_SchoolName    	";	//학교명
		$sql04= $sql04." ,MS.Specialization	as ms_Specialization	";	//학과
		$sql04= $sql04." ,MS.UpdateDate    	as ms_UpdateDate    	";	//업데이트 일자
		$sql04= $sql04." ,MS.UpdateUser    	as ms_UpdateUser    	";	//등록자 사원번호
		$sql04= $sql04." FROM										";
		$sql04= $sql04." 	member_school_tbl MS					";
		$sql04= $sql04." WHERE										";
		$sql04= $sql04." MS.MemberNo = '".$MemberNo."'				";
		$sql04= $sql04." ORDER BY MS.SchoolStart					";

		$member_data04 = array(); 
		$result_member04 = mysql_query($sql04,$db);
		while($re_row_member04 = mysql_fetch_array($result_member04)){
			array_push($member_data04,$re_row_member04);
		}
		$this->assign('member_data04',$member_data04);
/*****************************************************************/
		//인사카드 : 자격 및 면허
		/* ------------------------------------------------------------------- */
		$sql_cnt05    = "SELECT count(*) cnt05 FROM member_certification_tbl where MemberNo='".$MemberNo."'";
		$result_cnt05 = mysql_query($sql_cnt05,$db);
		$re_row_cnt05 = mysql_num_rows($result_cnt05);
		if($re_row_cnt05 != 0) {
			$cnt05    = @mysql_result($result_cnt05,0,"cnt05");
			$this->assign('cnt05' ,$cnt05); 
		} //End
		/* ------------------------------------------------------------------- */
		$sql05= "SELECT													";
		$sql05= $sql05."  MC.MemberNo         	as mc_MemberNo         	";	//사원번호
		$sql05= $sql05." ,MC.CertificationName	as mc_CertificationName	";	//종류
		$sql05= $sql05." ,MC.ObtainDate       	as mc_ObtainDate       	";	//취득일
		$sql05= $sql05." ,MC.CertificationNo  	as mc_CertificationNo  	";	//자격번호
		$sql05= $sql05." ,MC.UpdateDate       	as mc_UpdateDate       	";	//업데이트 일자
		$sql05= $sql05." ,MC.UpdateUser       	as mc_UpdateUser       	";	//등록자 사원번호
		$sql05= $sql05." FROM											";
		$sql05= $sql05." 	member_certification_tbl MC					";
		$sql05= $sql05." WHERE											";
		$sql05= $sql05." MC.MemberNo = '".$MemberNo."'					";
		$sql05= $sql05." ORDER BY MC.ObtainDate							";
		/* ------------------------------------------------------------------- */
		$member_data05 = array(); 
		$result_member05 = mysql_query($sql05,$db);
		while($re_row_member05 = mysql_fetch_array($result_member05)){
			array_push($member_data05,$re_row_member05);
		}
		/* ------------------------------------------------------------------- */
		$this->assign('member_data05',$member_data05);
/*****************************************************************/
		//인사카드 : 이전경력
		/* ------------------------------------------------------------------- */
		$sql_cnt06    = "SELECT count(*) cnt06 FROM member_career_tbl where MemberNo='".$MemberNo."'";
		$result_cnt06 = mysql_query($sql_cnt06,$db);
		$re_row_cnt06 = mysql_num_rows($result_cnt06);
		if($re_row_cnt06 != 0) {
			$cnt06    = @mysql_result($result_cnt06,0,"cnt06"); 
			$this->assign('cnt06' ,$cnt06); 
		} //End
		/* ------------------------------------------------------------------- */
		$sql06= "SELECT											";
		$sql06= $sql06."  MCR.MemberNo   	as mcr_MemberNo   	";	//사원번호
		$sql06= $sql06." ,MCR.CareerStart	as mcr_CareerStart	";	//입사일자
		$sql06= $sql06." ,MCR.CareerEnd  	as mcr_CareerEnd  	";	//퇴사일자
		$sql06= $sql06." ,MCR.CompanyName	as mcr_CompanyName	";	//회사명
		$sql06= $sql06." ,MCR.Position   	as mcr_Position   	";	//직위
		$sql06= $sql06." ,MCR.UpdateDate 	as mcr_UpdateDate 	";	//업데이트 일자
		$sql06= $sql06." ,MCR.UpdateUser 	as mcr_UpdateUser 	";	//등록자 사원번호
		$sql06= $sql06." FROM									";
		$sql06= $sql06." 	member_career_tbl MCR				";
		$sql06= $sql06." WHERE									";
		$sql06= $sql06." MCR.MemberNo = '".$MemberNo."'			";
		$sql06= $sql06." ORDER BY MCR.CareerStart				";
		/* ------------------------------------------------------------------- */
		$member_data06 = array(); 
		$result_member06 = mysql_query($sql06,$db);
		/* ------------------------------------------------------------------- */
		while($re_row_member06 = mysql_fetch_array($result_member06)){
			array_push($member_data06,$re_row_member06);
		}
		/* ------------------------------------------------------------------- */
		$this->assign('member_data06',$member_data06);
	}// GetData02
/*******************************************************************************************************/
/*******************************************************************************************************/
	/* 인사카드 인사정보 DB값 가져오기---------------------------------------------------------------- */
	function GetData03()
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
/*****************************************************************/
		//인사카드 : 보수교육
		/* ------------------------------------------------------------------- */
		$sql_cnt07    = "SELECT count(*) cnt07 FROM member_supplyeducation_tbl where MemberNo='".$MemberNo."'";
		$result_cnt07 = mysql_query($sql_cnt07,$db);
		$re_row_cnt07 = mysql_num_rows($result_cnt07);
		/* ------------------------------------------------------------------- */
		if($re_row_cnt07 != 0) {
			$cnt07    = @mysql_result($result_cnt07,0,"cnt07"); 
			$this->assign('cnt07' ,$cnt07); 
		} //End
		/* ------------------------------------------------------------------- */
		$sql07= "SELECT													";
		$sql07= $sql07."  MSE.MemberNo        	as mse_MemberNo        	";	//사원번호
		$sql07= $sql07." ,MSE.EducationName   	as mse_EducationName   	";	//과정
		$sql07= $sql07." ,MSE.OrganizationName	as mse_OrganizationName	";	//교육기관명
		$sql07= $sql07." ,MSE.EducationStart  	as mse_EducationStart  	";	//시작일자
		$sql07= $sql07." ,MSE.EducationEnd    	as mse_EducationEnd    	";	//종료일자
		$sql07= $sql07." ,MSE.UpdateDate      	as mse_UpdateDate      	";	//업데이트 일자
		$sql07= $sql07." ,MSE.UpdateUser      	as mse_UpdateUser      	";	//등록자 사원번호
		$sql07= $sql07." FROM											";
		$sql07= $sql07." 	member_supplyeducation_tbl MSE				";
		$sql07= $sql07." WHERE											";
		$sql07= $sql07." MSE.MemberNo = '".$MemberNo."'					";
		$sql07= $sql07." ORDER BY MSE.EducationStart					";
		/* ------------------------------------------------------------------- */
		$member_data07 = array(); 
		$result_member07 = mysql_query($sql07,$db);
		while($re_row_member07 = mysql_fetch_array($result_member07)){
			array_push($member_data07,$re_row_member07);
		}
		/* ------------------------------------------------------------------- */
		$this->assign('member_data07',$member_data07);
/*****************************************************************/
		//인사카드 : 가족사항
		/* ------------------------------------------------------------------- */
		$sql_cnt08    = "SELECT count(*) cnt08 FROM member_family_tbl where MemberNo='".$MemberNo."'";
		$result_cnt08 = mysql_query($sql_cnt08,$db);
		$re_row_cnt08 = mysql_num_rows($result_cnt08);
		/* ------------------------------------------------------------------- */
		if($re_row_cnt08 != 0) {
			$cnt08    = @mysql_result($result_cnt08,0,"cnt08");
			$this->assign('cnt08' ,$cnt08); 
		} //End
		/* ------------------------------------------------------------------- */
		$sql08= "SELECT										";
		$sql08= $sql08."  MF.MemberNo  	as mf_MemberNo  	";	//사원번호
		$sql08= $sql08." ,MF.Relation  	as mf_Relation  	";	//관계
		$sql08= $sql08." ,MF.Name      	as mf_Name      	";	//한글이름
		$sql08= $sql08." ,MF.PersonalNo	as mf_PersonalNo	";	//주민등록번호
		$sql08= $sql08." ,MF.LastSchool	as mf_LastSchool	";	//최종학력
		$sql08= $sql08." ,MF.Occupation	as mf_Occupation	";	//직업
		$sql08= $sql08." ,MF.UpdateDate	as mf_UpdateDate	";	//회사명
		$sql08= $sql08." ,MF.birthday  	as mf_birthday  	";	//생년월일
		$sql08= $sql08." ,MF.UpdateUser	as mf_UpdateUser	";	//등록자 사원번호
		$sql08= $sql08." FROM								";
		$sql08= $sql08." 	member_family_tbl MF			";
		$sql08= $sql08." WHERE								";
		$sql08= $sql08." MF.MemberNo = '".$MemberNo."'		";
		$sql08= $sql08." ORDER BY MF.birthday DESC			";
		/* ------------------------------------------------------------------- */
		$member_data08 = array(); 
		$result_member08 = mysql_query($sql08,$db);
		while($re_row_member08 = mysql_fetch_array($result_member08)){
			array_push($member_data08,$re_row_member08);
		}
		/* ------------------------------------------------------------------- */
		$this->assign('member_data08',$member_data08);
/*****************************************************************/
		//인사카드 : 상훈
		/* ------------------------------------------------------------------- */
		$sql_cnt09    = "SELECT count(*) cnt09 FROM member_award_tbl where MemberNo='".$MemberNo."'";
		$result_cnt09 = mysql_query($sql_cnt09,$db);
		$re_row_cnt09 = mysql_num_rows($result_cnt09);
		/* ------------------------------------------------------------------- */
		if($re_row_cnt09 != 0) {
			$cnt09    = @mysql_result($result_cnt09,0,"cnt09");
			$this->assign('cnt09' ,$cnt09); 
		} //End
		/* ------------------------------------------------------------------- */
		$sql09= "SELECT											";
		$sql09= $sql09."  MA.MemberNo    	as ma_MemberNo    	";	//사원번호
		$sql09= $sql09." ,MA.AwardDate   	as ma_AwardDate   	";	//받은날짜
		$sql09= $sql09." ,MA.AwardName   	as ma_AwardName   	";	//종류및근거
		$sql09= $sql09." ,MA.Organization	as ma_Organization	";	//상훈기관
		$sql09= $sql09." ,MA.UpdateDate  	as ma_UpdateDate  	";	//등록일자
		$sql09= $sql09." ,MA.UpdateUser  	as ma_UpdateUser  	";	//등록자 사원번호
		$sql09= $sql09." FROM									";
		$sql09= $sql09." 	member_award_tbl MA					";
		$sql09= $sql09." WHERE									";
		$sql09= $sql09." MA.MemberNo = '".$MemberNo."'			";
		$sql09= $sql09." ORDER BY MA.AwardDate					";
		/* ------------------------------------------------------------------- */
		$member_data09 = array(); 
		$result_member09 = mysql_query($sql09,$db);
		/* ------------------------------------------------------------------- */
		while($re_row_member09 = mysql_fetch_array($result_member09)){
			array_push($member_data09,$re_row_member09);
		}
		/* ------------------------------------------------------------------- */
		$this->assign('member_data09',$member_data09);
		/* ------------------------------------------------------------------- */
	}//GetData03 End
/*******************************************************************************************************/
	/* 인사카드 인사정보 TPL---------------------------------------------------------------- */
	function GetData04()
	{
		$this->display("intranet/common_contents/work_myInfo/myCardInfo.tpl");
	}//GetData04 End
/*******************************************************************************************************/


	/* ------------------------------------------------------------------------------ */
}//class Main End
?>