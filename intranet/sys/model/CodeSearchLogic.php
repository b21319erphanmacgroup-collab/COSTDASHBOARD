<?php
	/* ***********************************
	* 프로젝트코드검색, 사원검색
	* 2014-12-   :
	* 2014-12-18 : 세션값을 쿠키값으로 대체(/sys/inc/getCookieOfUser.php : 파일생성) : SUK
	* 2014-12-18 : php.날짜변수 관련 공통 페이지 삽입(/sys/inc/getNeedDate.php : 파일생성) : SUK
	* 2014-12-16 : 파일정리: SUK 
	*************************************** */
	/*스마티 설정파일------------------*/
	require('../../../SmartyConfig.php');
	require_once($SmartyClassPath);
	/*---------------------------------*/
	require('../inc/function_intranet.php');	//자주쓰는 기능 Function
	/*---------------------------------*/
	include "../inc/getCookieOfUser.php";		//사용자에 관한 쿠키값
	include "../inc/getNeedDate.php";			//로직에 사용되는 PHP시간&날짜 정의
	/* ------------------------------------ */
?>
<?php
	extract($_GET);
		$MemberNo	=	"";	//사원번호
	if($_SESSION['SS_memberID']!=""){
		/* SET SESSION ----------------------- */
		$MemberNo   =   $_SESSION['SS_memberID'];		//사원번호     
		$memberID	=   $_SESSION['SS_memberID'];		//사원번호     

		$CompanyKind=   $_SESSION['SS_CompanyKind'];//장헌산업(JANG),파일테크(PILE),바론컨설턴트(BARO),한맥(HANM)
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
 
		$CompanyKind=	$_SESSION['CK_CompanyKind'];	//회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:BARO,한맥:HANM)
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
	/* 검색을 위해 넘어온 GET VALUE****************/
	/* *********************** */
	/* go_searchCode('key01','key02','key03') */
	/* *********************** */
	/*리턴되어질 페이지의 ID명*/
	$key01 = $_GET["key01"];	//p_code    inputID
	$key02 = $_GET["key02"];	//sub_code  inputID
	$key03 = $_GET["key03"];	//p_name    inputID
	/* *********************** */
	/* *********************************** */
?>
<?php
class CodeSearchLogic extends Smarty {
	// 생성자
	function CodeSearchLogic()
	{ 
		/*---------------------------------*/
		global $SmartyClass_TemplateDir;
		global $SmartyClass_CompileDir;
		global $SmartyClass_ConfigDir;
		global $SmartyClass_CacheDir;
		/*---------------------------------*/
		$this->Smarty();
		/*---------------------------------*/
		$this->template_dir		=$SmartyClass_TemplateDir;
		$this->compile_dir		=$SmartyClass_CompileDir;
		$this->config_dir		=$SmartyClass_ConfigDir;	
		$this->cache_dir		=$SmartyClass_CacheDir;
		/*---------------------------------*/
	}//Main End
	/* ------------------------------------------------------------------------------ */
	function SearchInfo01()
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
		global  $key01;
		global  $key02;
		global  $key03;
		/* ----------------- */
		$inputId_p_code;   
		$inputId_sub_code; 
		$inputId_p_name;   
		$re_kind;
		/* *********************** */
		$inputId_p_code   = $key01;
		$inputId_sub_code = $key02;
		$inputId_p_name   = $key03;
		/* *********************** */
		if( $inputId_p_name =="2"){
		$re_kind = $inputId_p_name;
		}
		/*---------------------------------*/
		//  Start ************************************************* */
		$sql01=        " SELECT									";		// 
		$sql01= $sql01."  SYS.SysKey as sys_SysKey			 	";		// 
		$sql01= $sql01." ,SYS.Code as sys_Code				 	";		// 
		$sql01= $sql01." ,SYS.Name as sys_Name				 	";		// 
		$sql01= $sql01." ,SYS.Note as sys_Note				 	";		// 
		$sql01= $sql01." ,SYS.CodeORName as sys_CodeORName		";		// 
		$sql01= $sql01." ,SYS.Description as sys_Description 	";		// 
		$sql01= $sql01." FROM								 	";		// 
		$sql01= $sql01." SYSTEMCONFIG_TBL SYS				 	";		// 
		$sql01= $sql01." where								 	";		// 
		$sql01= $sql01." SysKey = 'ProjectCode' and Code not in('107','130','118')		";		// 
		$sql01= $sql01." ORDER BY orderno						 	";		// 
		//echo $sql01;
		
		/*---------------------------------*/
		$query_data01 = array();
		/*---------------------------------*/
		$re01 = mysql_query($sql01,$db);
		while($re_row01 = mysql_fetch_array($re01)) 
		{
			array_push($query_data01,$re_row01);
		}
		/*---------------------------------*/
		$this->assign('inputId_p_code',$inputId_p_code);
		$this->assign('inputId_sub_code',$inputId_sub_code);
		$this->assign('inputId_p_name',$inputId_p_name);
		$this->assign('re_kind',$re_kind);
		/*---------------------------------*/
		$this->assign('list_data01',$query_data01);
		/*---------------------------------*/
	}  //SearchInfo01 End
	/* ------------------------------------------------------------------------------ */
	function SearchInfo02()
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
		global  $key01;
		global  $key02;
		global  $key03;
		/* ----------------- */
		/* 검색을 위해 넘어온 POST VALUE****************************** */
		//search01에서 넘어온값
		$code01		= $_POST['code01'];
		$code_kind	= $_POST['code_kind'];
		/* *********************** */
		//  Start *********************************************************** */
		$sql=      " SELECT    ";
		$sql= $sql."  P.ProjectCode as P_ProjectCode ";
		$sql= $sql." ,left(P.ProjectCode,2) as P_CodeCheck01 ";
		$sql= $sql." ,substring(P.ProjectCode,3,6) as P_CodeCheck02 ";
		$sql= $sql." ,P.ProjectNickname as P_ProjectNickname ";
		$sql= $sql." FROM ";
		$sql= $sql." PROJECT_TBL P ";
		$sql= $sql." WHERE ";
		$sql= $sql." ProjectCode like '%-".$code01."-%' ";
		$sql= $sql." AND ";	
		$sql= $sql." (ProjectCode like '0%' or ProjectCode like 'X%' or ProjectCode like '1%')  ";	
		$sql= $sql." ORDER BY ProjectCode DESC";
		//echo $sql."<Br>"; 
		/*---------------------------------*/
		$query_data02 = array(); 
		/*---------------------------------*/
		$result01 = mysql_query($sql,$db);
		$result_num = mysql_num_rows($result01);
		/*---------------------------------*/
		if($result_num != 0)
		{	
			$i=1;
			while($re_row = mysql_fetch_array($result01)) 
			{
				/*---------------------------------*/
				// 프로젝트 코드 시작이'XX'일 경우 현재년도 적용 2014년->14
				$strCheck = $re_row[P_CodeCheck01];
				/*---------------------------------*/
				if($strCheck=="XX"){
					$YearCut = mb_substr(date("Ymd"),2,2,"UTF-8");
					$re_row[P_ProjectCode] = $YearCut.$re_row[P_CodeCheck02];

				}//if End
			array_push($query_data02,$re_row01);

			}//while
		}//if
		/*---------------------------------*/
		$this->assign('code_kind',$code_kind);
		$this->assign('list_data02',$query_data02);
	}  //SearchInfo02 End
	/* ------------------------------------------------------------------------------ */
	function SearchPage()
	{
		/* -----------------*/
		global	$CompanyKind;	  // 회사명
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
		$this->assign('CompanyKind',$CompanyKind);
		/* ----------------- */
		$this->myinfo();
		$this->display("intranet/common_layout/codeSearchPop.tpl");
		/* ----------------- */
	}  //SearchPage End
	/* ------------------------------------------------------------------------------ */
	function SearchMemberPage()	//사원검색 팝업
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
		$GroupCode = (int)$GroupCode;	  // 직급
		/* ----------------- */
		$returnId01 = $_GET["returnId01"];	//    이름 리턴ID
		$returnId02 = $_GET["returnId02"];	//    사번 리턴ID
		/* ----------------- */
		//============================================================================
		$sql= " SELECT																		";
		$sql= $sql."  a.korName		as Name													";
		$sql= $sql." ,a.MemberNo	as MemberNo												";
		$sql= $sql." ,a.Name		as Position												";
		$sql= $sql." ,a.ExtNo		as ExtNo												";
		$sql= $sql." ,b.Name		as GroupName											";
		$sql= $sql." FROM																	";
		$sql= $sql." (																		";
		$sql= $sql." 	select * from														";
		$sql= $sql." 	( select * from														";
		$sql= $sql." 					member_tbl											";
		$sql= $sql." 				where													";
		$sql= $sql." 					GroupCode='".$GroupCode."'							";
		$sql= $sql." 				AND WorkPosition not in('2','8','9')					";
		$sql= $sql." 				order by RankCode asc, korName asc 						";
		$sql= $sql." 	 )a1																";
		$sql= $sql." 	 left JOIN															";
		$sql= $sql." 	( select * from systemconfig_tbl where SysKey='PositionCode' )a2	";
		$sql= $sql." 	 on a1.RankCode = a2.code											";
		$sql= $sql." ) a left JOIN															";
		$sql= $sql." ( select * from systemconfig_tbl where SysKey='GroupCode'  )b			";
		$sql= $sql."  on a.GroupCode = b.code												";
		//============================================================================
		/* ----------------- */
		$result01 = mysql_query($sql,$db);
		$result_num = mysql_num_rows($result01);
		/* ----------------- */
		$query_data01 = array(); 
		/* ----------------- */
		if($result_num != 0){	
			while($re_row = mysql_fetch_array($result01)){
				array_push($query_data01,$re_row);
			}//while End
		}//if End
		/* ----------------- */
		$this->assign('returnId01',$returnId01);
		$this->assign('returnId02',$returnId02);
		$this->assign('list_data01',$query_data01);
		$this->display("intranet/common_layout/searchMemberPop.tpl");
		/* ----------------- */
	}  //GoOutPop End
	/* ------------------------------------------------------------------------------ */
	function SearchMember()	//사원검색
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
		$ajax_insertStr		= ($_POST['ajax_insertStr']==""?"":$_POST['ajax_insertStr']);	//검색입력값                
		$ajax_returnId01	= ($_POST['ajax_returnId01']==""?"":$_POST['ajax_returnId01']);	//리턴되어질 ID값 : 한글이름
		$ajax_returnId02	= ($_POST['ajax_returnId02']==""?"":$_POST['ajax_returnId02']);	//리턴되어질 ID값 : 사원번호
		/* ----------------- */
?>
<?php
		$inner_html=""; 
		//$inner_html = $inner_html.'<script src="../../js/jquery/jquery-1.10.2.js" type="text/javascript"></script>';
		//$inner_html = $inner_html.'<script src="../../js/common/left_menubar.js"  type="text/javascript"></script> ';
		$inner_html = $inner_html.'	<table class="tbl_bg_white t_center" style="font-family:굴림;font-size:12px;" width="100%" height="60px;" border="0" cellspacing="0" cellpadding="0"> 	';
		$inner_html = $inner_html.' 	<colgroup>                                                                                	';
		$inner_html = $inner_html.'		<col width="13.1%"/>                                                                      	';
		$inner_html = $inner_html.'		<col width="31.8%"/>                                                                      	';
		$inner_html = $inner_html.'		<col width="18.2%"/>                                                                      	';
		$inner_html = $inner_html.'		<col width="16.7%"/>                                                                      	';
		$inner_html = $inner_html.'		<col width="*%"/>                                                                         	';
		$inner_html = $inner_html.'		</colgroup>                                                                               	';
		$inner_html = $inner_html.'		<input type="hidden" id="returnId01" name="returnId01" value="'.$ajax_returnId01.'">        ';
		$inner_html = $inner_html.'		<input type="hidden" id="returnId02" name="returnId02" value="'.$ajax_returnId02.'">        ';
?>
<?php
		//============================================================================
		$sql= " SELECT																										";
		$sql= $sql."  a.korName  as Name																					";
		$sql= $sql." ,a.MemberNo as MemberNo																				";
		$sql= $sql." ,a.Name as Position																					";
		$sql= $sql." ,a.ExtNo as ExtNo																						";
		$sql= $sql." ,b.Name as GroupName																					";
		$sql= $sql." FROM																									";
		$sql= $sql." (																										";
		$sql= $sql." 	select * from																						";
		$sql= $sql." 	( select * from																						";
		$sql= $sql." 					member_tbl																			";
		$sql= $sql." 				where																					";
		$sql= $sql." 					( MemberNo like '%".$ajax_insertStr."%' or korName like '%".$ajax_insertStr."%')    ";
		$sql= $sql." 				AND WorkPosition not in('2','8','9')													";
		$sql= $sql." 				order by korName asc, MemberNo asc														";
		$sql= $sql." 	 )a1																								";
		$sql= $sql." 	 left JOIN																							";
		$sql= $sql." 	( select * from systemconfig_tbl where SysKey='PositionCode' )a2									";
		$sql= $sql." 	 on a1.RankCode = a2.code																			";
		$sql= $sql." ) a left JOIN																							";
		$sql= $sql." ( select * from systemconfig_tbl where SysKey='GroupCode'  )b											";
		$sql= $sql."  on a.GroupCode = b.code																				";
		//============================================================================

		$result01 = mysql_query($sql,$db);
		$result_num = mysql_num_rows($result01);
		/* ----------------- */
		if($result_num != 0)
		{	
			$i=1;
			while($re_row = mysql_fetch_array($result01)) 
			{
				$inner_html = $inner_html.'<tr onclick="selectedMember($(this).attr(\'id\'),$(this).attr(\'name\'))" class="selectedMember2" id="'.$re_row[Name].'" name="'.$re_row[MemberNo].'" title="'.$re_row[MemberNo].'" style="height:20px !important;">		';
				$inner_html = $inner_html.'<td class="tbl_right_border tbl_bottom_border hand" >'.$i.'</td>														';
				$inner_html = $inner_html.'<td class="tbl_right_border tbl_bottom_border hand">'.($re_row[GroupName]==""?"&nbsp;":$re_row[GroupName]).'</td>	';
				$inner_html = $inner_html.'<td class="tbl_right_border tbl_bottom_border hand">'.($re_row[Name]==""?"&nbsp;":$re_row[Name]).'</td>				';
				$inner_html = $inner_html.'<td class="tbl_right_border tbl_bottom_border hand">'.$re_row[MemberNo].'</td>										';
				$inner_html = $inner_html.'<td class="tbl_bottom_border hand">'.$re_row[Position].'</td>														';
				$inner_html = $inner_html.'</tr>																												';
			$i++;
				}//while End
		}else{
				$inner_html = $inner_html.'<tr>											';
				$inner_html = $inner_html.'<td colspan="5">등록된 정보가 없습니다</td>	';
				$inner_html = $inner_html.'</tr>										';
		} //if End
				$inner_html = $inner_html.' </table>         ';
/////////////////
echo $inner_html;
/////////////////
	}  //SearchMember() End

	/* ------------------------------------------------------------------------------ */

	function myinfo()
	{
		/* ----------------- */
		global $db;
		global $memberID;
		/* ----------------- */
		//============================================================================
		// 이름,부서,직급
		//============================================================================
		$sql= "		 SELECT																	";
		$sql= $sql."  a.korName as Name														";
		$sql= $sql." ,a.Name as Position													";
		$sql= $sql." ,a.ExtNo as ExtNo														";
		$sql= $sql." ,b.Name as GroupName													";
		$sql= $sql." FROM																	";
		$sql= $sql." (                                                                 		";
		$sql= $sql." 	select * from                                                 		";
		$sql= $sql." 	( select * from member_tbl where MemberNo = '$memberID' )a1         ";
		$sql= $sql." 	 left JOIN                                                 		    ";
		$sql= $sql." 	( select * from systemconfig_tbl where SysKey='PositionCode' )a2	";
		$sql= $sql." 	 on a1.RankCode = a2.code                                  		    ";
		$sql= $sql." ) a left JOIN                                                     		";
		$sql= $sql." ( select * from systemconfig_tbl where SysKey='GroupCode'  )b          ";
		$sql= $sql."  on a.GroupCode = b.code                                      		    ";
		/* ----------------- */
		//echo $sql."<br>";
		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		if($re_num != 0)
		{	
			$Name		= mysql_result($re,0,"Name");
			$GroupName	= mysql_result($re,0,"GroupName");
			$Position	= mysql_result($re,0,"Position");
			$ExtNo		= mysql_result($re,0,"ExtNo");
		}
		/* ----------------- */
		/*
		echo $Name."<br>";
		echo $GroupName."<br>";
		echo $Position."<br>";
		*/
		//============================================================================
		// 사진
		//============================================================================
		$src_photo = "../erpphoto/".$memberID.".jpg";
		$src_photo1 = "../erpphoto/".$memberID.".gif";
		if(file_exists($src_photo)) {
			$MemberPic=$src_photo;
		}else if(file_exists($src_photo1)){ 
			$MemberPic=$src_photo2;
		}else{
			$MemberPic="../erpphoto/noimage.gif";
		}
		/* ----------------- */
		//echo $MemberPic."<br>";
		/* ----------------- */
		$this->assign('memberID',$memberID);
		$this->assign('Name',$Name);
		$this->assign('korName',$Name);
		$this->assign('GroupName',$GroupName);
		$this->assign('Position',$Position);
		$this->assign('MemberPic',$MemberPic);
		$this->assign('ExtNo',$ExtNo);
		/* ----------------- */
	}//myinfo

	/* ------------------------------------------------------------------------------ */
}//class Main End
?>
<?php
/* 점검용 ******************************************
$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
$ipStr  = "192.168.2.62";
$myCompany   = searchCompanyKind();
if($myip==$ipStr){
	echo $myip."===".$myCompany."===<BR>";
}//if End
**************************************************** */
?>
