<?php
	session_start();
	//스마티 설정파일 *****************************************
	include "../inc/dbcon.inc";
	include "../inc/function_intranet.php";  
	include "../../../SmartyConfig.php";
	//include "../inc/getCookieOfUser.php";  
	include "../util/HanamcPageControl.php";
	include "../inc/getNeedDate.php";      //로직에 사용되는 PHP시간&날짜 정의
	require_once($SmartyClassPath);
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
		/* SET COOKIE --------------------------------- */
		//쿠키정보 세션으로 대체 250626 김진선
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
	/* 수정을 위해 넘어온 GET VALUE****************************** */
	/* get 파라미터 한글깨짐 방지----------------------------------- */
	/*$edit_main_p_code	= iconv('euc-kr', 'utf-8', $_GET['main_p_code']); */
	/* ************************************************************** */
	$Info01_Kind = $_GET['Info01_Kind'];  //설계 현황구분 //1:입찰현황, 2:낙찰현황
	$Info02_Kind = $_GET['Info02_Kind'];  //건설사업관리 현황구분 //1:입찰현황, 2:낙찰현황
	$pop = $_GET['pop'];  //
	/*--------------------------------------------------------------*/
	$ch_year	= "";
	$ch_year	= ($_GET['ch_year']==""?$nowYear:$_GET['ch_year']);
	$ch_yearYN  = "N";	//프린트 페이지 여부(Y/N). default:N
	/*--------------------------------------------------------------*/
	if($ch_year==$nowYear){
		$ch_yearYN  = "N";
	}else{
		$ch_yearYN  = "Y";
	}//if
	/*--------------------------------------------------------------*/
?>
<?php
class BidPlanLogic {
	var $smarty;// 생성자

	function BidPlanLogic($smarty)
	{ 
		$this->smarty=$smarty;
	}
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function BidCount()  //수주건수
	{
	global $MemberNo;
	global $memberID;
	/*-----------------*/
	global $nowYear;
	global $ch_year;
	/*-----------------*/
	global $GroupCode;
	$GroupCode = (int)$GroupCode;
	$set_GroupCode	= (int)$_GET['set_GroupCode'];
	if($set_GroupCode==""){
		$set_GroupCode	= $GroupCode;
	}//if End
	global $db;
	/*-----------------*/
	$Year = $nowYear;
	/*-----------------*/
		/*설계 수주건수*/
		$sql_count01 =              "	SELECT COUNT(*) CNT FROM pq_bid_tbl	";
		$sql_count01 = $sql_count01."	WHERE								";
		$sql_count01 = $sql_count01."	Kind=1 and AcceptCompany like '%한맥%' and bidDateS like '".$ch_year."%' AND AcceptCompany <>'' order by Deadline	";
		$re01 = mysql_query($sql_count01);
		$result_count01 = mysql_result($re01,0,"CNT");
		/* ----------------------------------- */
		/*건설사업관리 수주건수*/
		$sql_count02 =              "	SELECT COUNT(*) CNT FROM pq_bid_tbl	";
		$sql_count02 = $sql_count02."	WHERE								";
		$sql_count02 = $sql_count02."	Kind=2 and AcceptCompany like '%한맥%' and bidDateS like '".$ch_year."%' AND AcceptCompany <>'' order by Deadline	";
		$re02 = mysql_query($sql_count02);
		$result_count02 = mysql_result($re02,0,"CNT");
		/* ----------------------------------- */
		$this->smarty->assign('count01',$result_count01);	//설계 수주(낙찰)건수
		$this->smarty->assign('count02',$result_count02);	//건설사업관리 수주(낙찰)건수
		/*-----------------------------------------------*/
	}  //BidCount() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function Info01()  //설계입찰현황
	{
	global $MemberNo;
	global $memberID;
	global $date_today; //YYYY-MM-DD
	global $nowYear;
	global $ch_year;
	global $ch_yearYN;
	global $CheckDate;//직전주 일요일
	global $GroupCode;
	$GroupCode = (int)$GroupCode;
	$set_GroupCode	= (int)$_GET['set_GroupCode'];
	if($set_GroupCode==""){
		$set_GroupCode	= $GroupCode;
	}//if End
	global $db;
	$PQKind = "1";
	/*-----------------------------------------------*/
	global $Info01_Kind; //1:입찰현황, 2:낙찰현황
	global $pop;
	/*-----------------------------------------------*/
	if($pop=="pop"){
		//작업무
	}else{
		$pop="";
	}//if
	/*-----------------------------------------------*/
	$add_sql = "";
	if($Info01_Kind=="1"){		//설계 입찰현황  (타회사 낙찰건도 포함)
		if($ch_yearYN=="N"){//$ch_yearYN=="N" : 현재 년도
			$add_sql=         "		and                                  	";
			$add_sql=$add_sql."		PQ.bidDateS LIKE '".$ch_year."%'        ";
			
// 			$add_sql=$add_sql."		(                                    	";
// 			$add_sql=$add_sql."		PQ.bidDateS < '2005-05-01'              ";
// 			$add_sql=$add_sql."		or                                   	";
// 			$add_sql=$add_sql."		PQ.bidDateS > '".$CheckDate."'          ";
// 			$add_sql=$add_sql."		)                                    	";

		}else{//$ch_yearYN=="Y" : 변경된 년도
			$add_sql=         "		and										";
			$add_sql=$add_sql."		(										";
// 			$add_sql=$add_sql."		PQ.bidDateS < '2005-05-01'				";
// 			$add_sql=$add_sql."		or                                   	";
			$add_sql=$add_sql."		PQ.bidDateS LIKE '".$ch_year."%'        ";
			$add_sql=$add_sql."		)										";
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		PQ.CommonCompany like '%한맥%'			";
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."			PQ.AcceptCompany NOT like '%한맥%'	";
			$add_sql=$add_sql."			and                                 ";
			$add_sql=$add_sql."			PQ.AcceptCompany <>''				";
			$add_sql=$add_sql."		)										";
		}//if

	}else if($Info01_Kind=="2"){ //설계 낙찰현황(타회사 낙찰건 제외)
		if($ch_yearYN=="N"){
			$add_sql=$add_sql."		and										";
			$add_sql=$add_sql."		PQ.bidDateS like '".$nowYear."%'        ";
			$add_sql=$add_sql."		and                                  	";
			$add_sql=$add_sql."		PQ.AcceptCompany like '%한맥%'			";

		}else{//$ch_yearYN=="Y"
			$add_sql=$add_sql."		and										";
			$add_sql=$add_sql."		PQ.bidDateS like '".$ch_year."%'        ";
			$add_sql=$add_sql."		and                                  	";
			$add_sql=$add_sql."		PQ.AcceptCompany like '%한맥%'			";
		}//if
	
	}else if($Info01_Kind=="3"){ //설계 입찰현황(전체:금년 등록건 전체)
		if($ch_yearYN=="N"){
			$add_sql=         "		and										";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."		PQ.bidDateS LIKE '".$nowYear."%'        ";
			$add_sql=$add_sql."		OR                                   	";
			$add_sql=$add_sql."		PQ.Deadline LIKE '".$nowYear."%'        ";
			$add_sql=$add_sql."		)										";
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."			PQ.CommonCompany like '%한맥%'		";
			$add_sql=$add_sql."			OR                                  ";
			$add_sql=$add_sql."			PQ.AcceptCompany like '%한맥%'		";
			$add_sql=$add_sql."		)										";

		}else{//$ch_yearYN=="Y"
			$add_sql=         "		and										";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."		PQ.bidDateS LIKE '".$ch_year."%'        ";
			$add_sql=$add_sql."		OR                                   	";
			$add_sql=$add_sql."		PQ.Deadline LIKE '".$ch_year."%'        ";
			$add_sql=$add_sql."		)										";	
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."			PQ.CommonCompany like '%한맥%'		";
			$add_sql=$add_sql."			OR                                  ";
			$add_sql=$add_sql."			PQ.AcceptCompany like '%한맥%'		";
			$add_sql=$add_sql."		)										";		
		}//if

	}else{
		if($ch_yearYN=="N"){
			$add_sql=         "		and                                  	";
			$add_sql=$add_sql."		(                                    	";
// 			$add_sql=$add_sql."		PQ.bidDateS < '2005-05-01'              ";
// 			$add_sql=$add_sql."		or                                   	";
			$add_sql=$add_sql."		PQ.bidDateS > '".$CheckDate."'          ";
			$add_sql=$add_sql."		)                                    	";

		}else{//$ch_yearYN=="Y"
			$add_sql=         "		and										";
			$add_sql=$add_sql."		(										";
// 			$add_sql=$add_sql."		PQ.bidDateS < '2005-05-01'				";
// 			$add_sql=$add_sql."		or                                   	";
			$add_sql=$add_sql."		PQ.bidDateS LIKE '".$ch_year."%'        ";
			$add_sql=$add_sql."		)										";
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		PQ.CommonCompany like '%한맥%'			";
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."			PQ.AcceptCompany NOT like '%한맥%'	";
			$add_sql=$add_sql."			and                                 ";
			$add_sql=$add_sql."			PQ.AcceptCompany <>''				";
			$add_sql=$add_sql."		)										";

		}
	}//if End

	$query_data = array(); 

		$sql=     "	SELECT                                   	";
		$sql=$sql."		 PQ.Code          as pq_Code         	";
		$sql=$sql."		,PQ.Part          as pq_Part         	";//분야 구분코드
		$sql=$sql."		,PQ.Kind          as pq_Kind         	";//1:설계,2:건설사업관리
		$sql=$sql."		,PQ.ProjectName   as pq_ProjectName  	";//용 역 명
		$sql=$sql."		,PQ.OrderKind     as pq_OrderKind    	";//발주방식
		$sql=$sql."		,PQ.OrderCompany  as pq_OrderCompany 	";//발주처
		$sql=$sql."		,PQ.OrderGroup    as pq_OrderGroup   	";//담당부서
		$sql=$sql."		,PQ.Payment       as pq_Payment      	";//설계가(백만원)
		$sql=$sql."		,PQ.Deadline      as pq_Deadline     	";//제출마감일자
		$sql=$sql."		,PQ.Gap           as pq_Gap          	";//1등과차이
		$sql=$sql."		,PQ.bidDateS      as pq_bidDateS     	";//입찰일시:시작시간
		$sql=$sql."		,PQ.bidDateE      as pq_bidDateE     	";//입찰일시:종료시간
		$sql=$sql."		,PQ.VestedCompany as pq_VestedCompany	";//전차회사
		$sql=$sql."		,PQ.CommonCompany as pq_CommonCompany	";//공동도급(한맥+산이)
		$sql=$sql."		,PQ.CommonRate    as pq_CommonRate   	";//공동도급 비율(60:40)
		$sql=$sql."		,PQ.AcceptCompany as pq_AcceptCompany	";//낙찰사
		$sql=$sql."		,PQ.Rank          as pq_Rank         	";//당사순위
		$sql=$sql."		,PQ.UpdateDate    as pq_UpdateDate   	";//최종수정일자
		$sql=$sql."		,PQ.UpdateUser    as pq_UpdateUser   	";//등록자 사원번호
		$sql=$sql."		,PQ.ViewDisplay   as pq_ViewDisplay  	";
		$sql=$sql."		,PQ.bidprice      as pq_bidprice     	";//낙찰가(원)
		$sql=$sql."		,S.Code           as s_Code     		";//분야코드(1:도로,2:구조,3:토질,4:환경,5:교통,6:수자원,7:항만,8:도시계획,9:안전진단,10:건설사업관리(도로),11:건설사업관리(항만))
		$sql=$sql."		,S.Name           as s_Name     		";//분야명
		$sql=$sql."	FROM                                     	";
		$sql=$sql."	 pq_bid_tbl PQ                            	";
		$sql=$sql."	LEFT JOIN systemconfig_tbl S ON             ";
		$sql=$sql."						S.SysKey='OrderPart'    ";
		$sql=$sql."						and                     ";
		$sql=$sql."						PQ.Part = S.Code        ";
		$sql=$sql."	WHERE                                    	";
		$sql=$sql."		PQ.Kind = ".$PQKind."                   ";

		$sql=$sql.$add_sql;

		$sql=$sql."	ORDER BY PQ.Deadline ASC                    ";

/////////////////
//echo "02::".$sql."<br>"; 
/////////////////
	$re = mysql_query($sql,$db);
	while($re_row = mysql_fetch_array($re)) {
		//$re_row[title_short]   = utf8_strcut($re_row[title],38,'..');
		//$re_row[comment_short] = utf8_strcut($re_row[comment],10,'..');

		$re_row[pq_Payment]  = number_format($re_row[pq_Payment]);//설계가(백만원)

		$re_row[ch_pq_bidprice]  = number_format($re_row[pq_bidprice]);//낙찰금액 : 단위(백만원)
		$re_row[ch_pq_bidprice_short]  = number_format($re_row[pq_bidprice]/1000000);//낙찰금액 : 단위(백만원)

		array_push($query_data,$re_row);
	} //while End

		/*------------------- */
		$this->smarty->assign('Info01_Kind',$Info01_Kind);
		$this->smarty->assign('nowYear',$nowYear);
		$this->smarty->assign('pop',$pop);
		$this->smarty->assign('query_data',$query_data);

		/*------------------- */
	}  //Info01() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function Info02()  //건설사업관리 입찰현황
	{
	global $MemberNo;
	global $memberID;
	global $date_today;
	global $nowYear;
	global $ch_year;
	global $ch_yearYN;
	global $CheckDate;//직전주 일요일

	global $GroupCode;
	$GroupCode = (int)$GroupCode;
	$set_GroupCode	= (int)$_GET['set_GroupCode'];
	if($set_GroupCode==""){
		$set_GroupCode	= $GroupCode;
	}//if End
	global $db;

	$PQKind = "2";

	global $Info02_Kind; //1:입찰현황, 2:낙찰현황, 3:입찰현황(전체)
	global $pop;
	/*---------------------------------*/
	if($pop=="pop"){
	
	}else{
		$pop="";
	}
	/*---------------------------------*/
	$add_sql = "";

	if($Info02_Kind=="1"){		//건설사업관리 입찰현황
		if($ch_yearYN=="N"){
			$add_sql=         "		and                                  	";
			$add_sql=$add_sql."		PQ.bidDateS LIKE '".$ch_year."%'        ";
// 			$add_sql=$add_sql."		(                                    	";
// 			$add_sql=$add_sql."		PQ.bidDateS < '2005-05-01'              ";
// 			$add_sql=$add_sql."		or                                   	";
// 			$add_sql=$add_sql."		PQ.bidDateS > '".$CheckDate."'          ";
// 			$add_sql=$add_sql."		)                                    	";
			//$add_sql=$add_sql."		and                                    	";
			//$add_sql=$add_sql."		PQ.AcceptCompany =''                  	";//낙찰사X

		}else{//$ch_yearYN=="Y"
			$add_sql=         "		and										";
			$add_sql=$add_sql."		(										";
// 			$add_sql=$add_sql."		PQ.bidDateS < '2005-05-01'				";
// 			$add_sql=$add_sql."		or                                   	";
			$add_sql=$add_sql."		PQ.bidDateS LIKE '".$ch_year."%'        ";
			$add_sql=$add_sql."		)										";
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		PQ.CommonCompany like '%한맥%'			";
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."			PQ.AcceptCompany NOT like '%한맥%'	";
			$add_sql=$add_sql."			and                                 ";
			$add_sql=$add_sql."			PQ.AcceptCompany <>''				";
			$add_sql=$add_sql."		)										";
		}//if

	}else if($Info02_Kind=="2"){ //건설사업관리 낙찰현황
		if($ch_yearYN=="N"){
			$add_sql=$add_sql."		and										";
			$add_sql=$add_sql."		PQ.bidDateS like '".$nowYear."%'        ";
			$add_sql=$add_sql."		and                                  	";
			$add_sql=$add_sql."		PQ.AcceptCompany like '%한맥%'			";

		}else{//$ch_yearYN=="Y"
			$add_sql=$add_sql."		and										";
			$add_sql=$add_sql."		PQ.bidDateS like '".$ch_year."%'        ";
			$add_sql=$add_sql."		and                                  	";
			$add_sql=$add_sql."		PQ.AcceptCompany like '%한맥%'			";
		}//if	

	}else if($Info02_Kind=="3"){ //건설사업관리 입찰현황(전체:금년 등록건 전체)
		if($ch_yearYN=="N"){
			$add_sql=         "		and										";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."		PQ.bidDateS LIKE '".$nowYear."%'        ";
			$add_sql=$add_sql."		OR                                   	";
			$add_sql=$add_sql."		PQ.Deadline LIKE '".$nowYear."%'        ";
			$add_sql=$add_sql."		)										";
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."			PQ.CommonCompany like '%한맥%'		";
			$add_sql=$add_sql."			OR                                  ";
			$add_sql=$add_sql."			PQ.AcceptCompany like '%한맥%'		";
			$add_sql=$add_sql."		)										";
		}else{//$ch_yearYN=="Y"
			$add_sql=         "		and										";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."		PQ.bidDateS LIKE '".$ch_year."%'        ";
			$add_sql=$add_sql."		OR                                   	";
			$add_sql=$add_sql."		PQ.Deadline LIKE '".$ch_year."%'        ";
			$add_sql=$add_sql."		)										";	
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."			PQ.CommonCompany like '%한맥%'		";
			$add_sql=$add_sql."			OR                                  ";
			$add_sql=$add_sql."			PQ.AcceptCompany like '%한맥%'		";
			$add_sql=$add_sql."		)										";		
		}//if

	}else{
		if($ch_yearYN=="N"){
			$add_sql=         "		and                                  	";
			$add_sql=$add_sql."		(                                    	";
// 			$add_sql=$add_sql."		PQ.bidDateS < '2005-05-01'              ";
// 			$add_sql=$add_sql."		or                                   	";
			$add_sql=$add_sql."		PQ.bidDateS > '".$CheckDate."'          ";
			$add_sql=$add_sql."		)                                    	";
			//$add_sql=$add_sql."		and                                    	";
			//$add_sql=$add_sql."		PQ.AcceptCompany =''                  	";//낙찰사X

		}else{//$ch_yearYN=="Y"
			$add_sql=         "		and										";
			$add_sql=$add_sql."		(										";
// 			$add_sql=$add_sql."		PQ.bidDateS < '2005-05-01'				";
// 			$add_sql=$add_sql."		or                                   	";
			$add_sql=$add_sql."		PQ.bidDateS LIKE '".$ch_year."%'        ";
			$add_sql=$add_sql."		)										";
	
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		PQ.CommonCompany like '%한맥%'			";
			$add_sql=$add_sql."		and                                    	";
			$add_sql=$add_sql."		(										";
			$add_sql=$add_sql."			PQ.AcceptCompany NOT like '%한맥%'	";
			$add_sql=$add_sql."			and                                 ";
			$add_sql=$add_sql."			PQ.AcceptCompany <>''				";
			$add_sql=$add_sql."		)										";
		}//if

	}//if End

	$query_data = array(); 

		$sql=     "	SELECT                                   	";
		$sql=$sql."		 PQ.Code          as pq_Code         	";//PK
		$sql=$sql."		,PQ.Part          as pq_Part         	";//분야 >> (1:도로,2:구조,3:토질,4:환경,5:교통,6:수자원,7:항만,8:도시계획,9:안전진단,10:건설사업관리(도로),11:건설사업관리(항만))
		$sql=$sql."		,PQ.Kind          as pq_Kind         	";//구분 >> 1:설계,2:건설사업관리
		$sql=$sql."		,PQ.ProjectName   as pq_ProjectName  	";//용 역 명
		$sql=$sql."		,PQ.OrderKind     as pq_OrderKind    	";//발주방식
		$sql=$sql."		,PQ.OrderCompany  as pq_OrderCompany 	";//발주처
		$sql=$sql."		,PQ.OrderGroup    as pq_OrderGroup   	";//담당부서
		$sql=$sql."		,PQ.Payment       as pq_Payment      	";//설계가(백만원)
		$sql=$sql."		,PQ.Deadline      as pq_Deadline     	";//제출마감일자
		$sql=$sql."		,PQ.Gap           as pq_Gap          	";//1등과차이
		$sql=$sql."		,PQ.bidDateS      as pq_bidDateS     	";//입찰일시:시작시간
		$sql=$sql."		,PQ.bidDateE      as pq_bidDateE     	";//입찰일시:종료시간
		$sql=$sql."		,PQ.VestedCompany as pq_VestedCompany	";//전차회사
		$sql=$sql."		,PQ.CommonCompany as pq_CommonCompany	";//공동도급(한맥+산이)
		$sql=$sql."		,PQ.CommonRate    as pq_CommonRate   	";//공동도급 비율(60:40)
		$sql=$sql."		,PQ.AcceptCompany as pq_AcceptCompany	";//낙찰사
		$sql=$sql."		,PQ.Rank          as pq_Rank         	";//당사순위
		$sql=$sql."		,PQ.UpdateDate    as pq_UpdateDate   	";//최종수정일자
		$sql=$sql."		,PQ.UpdateUser    as pq_UpdateUser   	";//등록자 사원번호
		$sql=$sql."		,PQ.ViewDisplay   as pq_ViewDisplay  	";
		$sql=$sql."		,PQ.bidprice      as pq_bidprice     	";//낙찰가(원)
		$sql=$sql."		,S.Code           as s_Code     		";//분야코드(1:도로,2:구조,3:토질,4:환경,5:교통,6:수자원,7:항만,8:도시계획,9:안전진단,10:건설사업관리(도로),11:건설사업관리(항만))
		$sql=$sql."		,S.Name           as s_Name     		";//분야명
		$sql=$sql."	FROM                                     	";
		$sql=$sql."	 pq_bid_tbl PQ                            	";
		$sql=$sql."	LEFT JOIN systemconfig_tbl S ON             ";
		$sql=$sql."						S.SysKey='OrderPart'    ";
		$sql=$sql."						and                     ";
		$sql=$sql."						PQ.Part = S.Code        ";

		$sql=$sql."	WHERE                                    	";
		$sql=$sql."		PQ.Kind = ".$PQKind."                   ";

		$sql=$sql.$add_sql;

		$sql=$sql."	ORDER BY PQ.Deadline ASC                    ";
/////////////////
//echo "02::".$sql."<br>"; 
/////////////////
		$re = mysql_query($sql,$db);

		while($re_row = mysql_fetch_array($re)) {
			$re_row[pq_Payment]  = number_format($re_row[pq_Payment]);//설계가(백만원)

			$re_row[ch_pq_bidprice]  = number_format($re_row[pq_bidprice]);//낙찰금액 : 단위(백만원)
			$re_row[ch_pq_bidprice_short]  = number_format($re_row[pq_bidprice]/1000000);//낙찰금액 : 단위(백만원)

			array_push($query_data,$re_row);
		} //while End
		/*------------------- */
		$this->smarty->assign('Info02_Kind',$Info02_Kind);
		$this->smarty->assign('pop',$pop);
		$this->smarty->assign('query_data2',$query_data);
		/*------------------- */
	}  //Info02() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function PageList()  //목록페이지로 이동
	{
	global $MemberNo;
	global $memberID;
	/*------------------- */
	global $nowYear;
	$nowYearOfToday = $nowYear;
	global $ch_year;
	global $ch_yearYN;

	$ch_year="2018";
	/*------------------- */
	global $GroupCode;
	$GroupCode = (int)$GroupCode;
	$set_GroupCode	= (int)$_GET['set_GroupCode'];
	if($set_GroupCode==""){
		$set_GroupCode	= $GroupCode;
	}//if End
	global $db;
	/*------------------- */
	$set_print	=  ($_GET['set_print']==""?"N":$_GET['set_print']);
	/*------------------- */
	$auth_bidManager = checkAuthCRUD($memberID,"입찰"); //권한체크(사원번호,권한명) return:1=권한있음, 0=권한없음
	    /*------------------- */
		$this->BidCount();	//현황카운트
		$this->Info01();	//설계입찰현황
		$this->Info02();	//건설사업관리 입찰현황
		/*------------------- */
		$this->smarty->assign('bidKind',"list");
		$this->smarty->assign('auth_bidManager',$auth_bidManager);//권한 : 입찰 수정입력
		/*------------------- */
		$this->smarty->assign('HTTP',$_SERVER['HTTP_USER_AGENT']);
		/*------------------- */
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('nowYearOfToday',$nowYearOfToday);
		$this->smarty->assign('ch_year',$ch_year);
		$this->smarty->assign('ch_yearYN',$ch_yearYN);
		/*------------------- */
		if($set_print=="Y"){
			$this->smarty->display("intranet/common_contents/work_bidPlan/listPage_print.tpl");
		}else{
			$this->smarty->display("intranet/common_contents/work_bidPlan/listPage.tpl");
		}//if
		/*------------------- */
	}  //PageList() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function PopPage01()  //
	{
		global $MemberNo;
		global $memberID;
		global $nowYear;
		global $GroupCode;
		$GroupCode = (int)$GroupCode;
		$set_GroupCode	= (int)$_GET['set_GroupCode'];
		if($set_GroupCode==""){
			$set_GroupCode	= $GroupCode;
		}//if End
		global $db;

	    /*------------------- */
		$this->BidCount();	//현황카운트
		$this->Info01();	//설계입찰현황
		//$this->Info02();	//건설사업관리 입찰현황
		/*------------------- */
		$this->smarty->assign('bidKind',"pop");
		/*------------------- */
		$this->smarty->assign('HTTP',$_SERVER['HTTP_USER_AGENT']);
		/*------------------- */
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		/*------------------- */
		$this->smarty->display("intranet/common_contents/work_bidPlan/listPage.tpl");
		/*------------------- */
	}  //PopPage01() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function PopPage02()  //
	{
	global $MemberNo;
	global $memberID;
	global $nowYear;

	global $GroupCode;
	$GroupCode = (int)$GroupCode;
	$set_GroupCode	= (int)$_GET['set_GroupCode'];
	if($set_GroupCode==""){
		$set_GroupCode	= $GroupCode;
	}//if End
	global $db;
	    /*------------------- */
		$this->BidCount();	//현황카운트
		//$this->Info01();	//설계입찰현황
		$this->Info02();	//건설사업관리 입찰현황
		/*------------------- */
		$this->smarty->assign('bidKind',"pop");
		/*------------------- */
		$this->smarty->assign('HTTP',$_SERVER['HTTP_USER_AGENT']);
		/*------------------- */
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		/*------------------- */
		$this->smarty->display("intranet/common_contents/work_bidPlan/listPage.tpl");
		/*------------------- */
	}  //PopPage02() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function ViewPage()//상세보기 페이지로 이동
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $db;
		/*---------------------------------------*/
		global $nowYear;
		/*---------------------------------------*/
		$content_id	= (int)$_GET['content_id'];
		/*---------------------------------------*/
		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT								";
		$sql=$sql."		 G.level		as g_level	    ";
		$sql=$sql."		,G.id			as g_id		   	";
		$sql=$sql."		,G.name			as g_name		";
		$sql=$sql."		,G.pass			as g_pass		";
		$sql=$sql."		,G.title		as g_title		";
		$sql=$sql."		,G.comment		as g_comment	";
		$sql=$sql."		,G.wdate		as g_wdate		";
		$sql=$sql."		,G.see			as g_see		";
		$sql=$sql."		,G.group_code	as g_group_code	";
		$sql=$sql."		,G.filename		as g_filename	";
		$sql=$sql."		,G.filesize		as g_filesize	";
		$sql=$sql."		,G.reg_member	as g_reg_member	";
		$sql=$sql."		,G.filename_tmp	as g_filename_tmp	";
		$sql=$sql."		,S.Name			as s_Name	";
		$sql=$sql."	FROM						";
		$sql=$sql."		group_board_tbl G		";
		$sql=$sql."		,systemconfig_tbl S		";
		$sql=$sql."	WHERE						";
		$sql=$sql."	id=".$content_id."			";
		$sql=$sql."	AND							";
		$sql=$sql."	G.group_code=S.Code			";
		$sql=$sql."	AND							";
		$sql=$sql."	S.SysKey='GroupCode'		";
		/*---------------------------------------*/
/////////////////
//echo "01::".$sql."<br>"; 
/////////////////
			/*-----------------------------*/
			$re = mysql_query($sql,$db);
			/*-----------------------------*/
			while($re_row = mysql_fetch_array($re)) {
				$re_row[title_short]   = utf8_strcut($re_row[g_title],40,'..');
				$re_row[comment_short] = utf8_strcut($re_row[g_comment],10,'..');

				$divfile = explode("/",$re_row[g_filename]);
				$divnum  = count($divfile);
				$extensionName = $divfile[$divnum-1];//파일이름(경로제외)
				$re_row[filename_short] = $extensionName;
 
				$readCount = (int)$re_row[g_see]; //조회수 COLUMN : see

				array_push($query_data,$re_row);
			} //while End
			/*-----------------------------*/
			$readCount = $readCount+1;
			/*-----------------------------*/
			/* 조회수 +1 업데이트 */
			/*----------------------------------------*/
			$sql02= " UPDATE group_board_tbl SET	";
			$sql02= $sql02."   see='".$readCount."'	";
			$sql02= $sql02." WHERE					";
			$sql02= $sql02."	id=".$content_id."	";
			/*----------------------------------------*/
///////////////////////
mysql_query($sql02,$db);
///////////////////////
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_bidPlan/viewPage.tpl");
		/*----------------------------------------*/
	}  //ViewPage() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function OptionValue()//셀렉트박스 옵션값
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $GroupCode;
		$GroupCode =(int)$GroupCode;
		
		global $GroupName;
		global $date_today;
		global $nowYear;
		global $db;
		//$content_id	= (int)$_GET['content_id'];
		/*분야---------------------------------------*/
		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT							";
		$sql=$sql."		 S.Code	as s_Code			";//분야코드(1:도로,2:구조,3:토질,4:환경,5:교통,6:수자원,7:항만,8:도시계획,9:안전진단,10:건설사업관리(도로),12:상하수도)
		$sql=$sql."		,S.Name	as s_Name			";//분야명
		$sql=$sql."	FROM							";
		$sql=$sql."	 systemconfig_tbl S				";
		$sql=$sql."	WHERE							";
		$sql=$sql."		S.SysKey='OrderPart'		";
		$sql=$sql."	ORDER BY CAST(S.Code AS UNSIGNED) ASC ";
		/*---------------------------------------*/
		$re = mysql_query($sql,$db);
		/*-----------------------------*/
		while($re_row = mysql_fetch_array($re)) {
			array_push($query_data,$re_row);
		} //while End

		/*발주방식---------------------------------------*/
		$query_data02 = array(); 
		/*---------------------------------------*/
		$sql02=       "	SELECT                               ";
		$sql02=$sql02."		 S.SysKey		as s_SysKey      ";
		$sql02=$sql02."		,S.Code			as s_Code        ";
		$sql02=$sql02."		,S.Name			as s_Name        ";
		$sql02=$sql02."		,S.CodeORName	as s_CodeORName  ";
		$sql02=$sql02."		,S.Description	as s_Description ";
		$sql02=$sql02."		,S.Note			as s_Note        ";
		$sql02=$sql02."		,S.orderno		as s_orderno     ";
		$sql02=$sql02."	FROM                                 ";
		$sql02=$sql02."	systemconfig_tbl S                   ";
		$sql02=$sql02."	WHERE                                ";
		$sql02=$sql02."	SysKey='OrderKind'                   ";
		$sql02=$sql02."	ORDER BY orderno ASC                 ";
		/*---------------------------------------*/
		$re02 = mysql_query($sql02,$db);
		/*-----------------------------*/
		while($re_row02 = mysql_fetch_array($re02)) {
			array_push($query_data02,$re_row02);
		} //while End
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('date_today',$date_today);
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);//분야
		$this->smarty->assign('query_data02',$query_data02);//발주방식
		/*구분---------------------------------------*/
		$query_data03 = array( '설계'=>'1', '건설사업관리'=>'2');
		$this->smarty->assign('query_data03',$query_data03);
		/*----------------------------------------*/

	}  //OptionValue() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function InsertPage()//입력페이지로 이동
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $GroupCode;
		$GroupCode =(int)$GroupCode;
		
		global $GroupName;
		global $date_today;
		global $nowYear;
		global $db;
		//$content_id	= (int)$_GET['content_id'];
		/*----------------------------------------*/
		$this->OptionValue();
		$this->smarty->display("intranet/common_contents/work_bidPlan/insertPage.tpl");
		/*----------------------------------------*/

	}  //InsertPage() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function UpdatePage()	//수정페이지로 이동
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $db;

		global $GroupCode;
		$GroupCode = (int)$GroupCode;
		global $GroupName;

		global $date_today;
		global $nowYear;
		/*---------------------------------------*/
		$content_id	= $_GET['content_id'];	//컨텐츠PK

	    $query_data07 = array();

		$sql=     "	SELECT                                   	";
		$sql=$sql."		 PQ.Code          as pq_Code         	";
		$sql=$sql."		,PQ.Part          as pq_Part         	";//분야 구분코드
		$sql=$sql."		,PQ.Kind          as pq_Kind         	";//1:설계,2:건설사업관리
		$sql=$sql."		,PQ.ProjectName   as pq_ProjectName  	";//용 역 명
		$sql=$sql."		,PQ.OrderKind     as pq_OrderKind    	";//발주방식
		$sql=$sql."		,PQ.OrderCompany  as pq_OrderCompany 	";//발주처
		$sql=$sql."		,PQ.OrderGroup    as pq_OrderGroup   	";//담당부서
		$sql=$sql."		,PQ.Payment       as pq_Payment      	";//설계가(백만원)
		$sql=$sql."		,PQ.Deadline      as pq_Deadline     	";//제출마감일자
		$sql=$sql."		,PQ.Gap           as pq_Gap          	";//1등과차이
		$sql=$sql."		,PQ.bidDateS      as pq_bidDateS      	";//입찰일시:시작시간
		$sql=$sql."		,PQ.bidDateE      as pq_bidDateE     	";//입찰일시:종료시간
		$sql=$sql."		,PQ.VestedCompany as pq_VestedCompany	";//전차회사
		$sql=$sql."		,PQ.CommonCompany as pq_CommonCompany	";//공동도급(한맥+산이)
		$sql=$sql."		,PQ.CommonRate    as pq_CommonRate   	";//공동도급 비율(60:40)
		$sql=$sql."		,PQ.AcceptCompany as pq_AcceptCompany	";//낙찰사
		$sql=$sql."		,PQ.Rank          as pq_Rank         	";//당사순위
		$sql=$sql."		,PQ.UpdateDate    as pq_UpdateDate   	";//최종수정일자
		$sql=$sql."		,PQ.UpdateUser    as pq_UpdateUser   	";//등록자 사원번호
		$sql=$sql."		,PQ.ViewDisplay   as pq_ViewDisplay  	";
		$sql=$sql."		,PQ.bidprice      as pq_bidprice     	";//낙찰가(원)
		$sql=$sql."	FROM                                     	";
		$sql=$sql."	 pq_bid_tbl PQ                            	";
		$sql=$sql."	WHERE                                    	";
		$sql=$sql."		PQ.Code = ".$content_id."               ";
/////////////////
//echo "02::".$sql."<br>"; 
/////////////////
		/*-----------------------------*/
		$re = mysql_query($sql,$db);
		/*-----------------------------*/
	while($re_row = mysql_fetch_array($re)) {
		//$re_row[title_short]   = utf8_strcut($re_row[title],38,'..');
		//$re_row[comment_short] = utf8_strcut($re_row[comment],10,'..');

		$pq_CommonCompany_array = $re_row[pq_CommonCompany];//공동도급 업체명(한맥+산이)
		$pq_CommonCompany_array = explode("+",$re_row[pq_CommonCompany]);
		$pq_CommonCompany_cnt   = count($pq_CommonCompany_array);
		/*-----------------------------*/
		for($i=0,$j=1;$i<$pq_CommonCompany_cnt;$i++,$j++){
			$re_row[pq_CommonCompany_array.$j] = $pq_CommonCompany_array[$i];
		}//for End

		$pq_CommonRate_array = $re_row[pq_CommonRate];//공동도급 비율(60:40)
		$pq_CommonRate_array = explode(":",$re_row[pq_CommonRate]);
		$pq_CommonCompany_cnt   = count($pq_CommonRate_array);
		/*-----------------------------*/
		for($i=0,$j=1;$i<$pq_CommonCompany_cnt;$i++,$j++){
			$re_row[pq_CommonRate_array.$j] = $pq_CommonRate_array[$i];
		}//for End

		array_push($query_data07,$re_row);
	} //while End
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('date_today',$date_today);
		/*----------------------------------------*/
		$this->smarty->assign('query_data07',$query_data07);
		/*----------------------------------------*/
		$this->OptionValue();
		$this->smarty->display("intranet/common_contents/work_bidPlan/updatePage.tpl");
		/*----------------------------------------*/
	}  //UpdatePage() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function UpdateDB()//수정 DB실행
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		$GroupCode =(int)$GroupCode;
		
		global $GroupName;
		global $date_today;
		global $nowYear;
		global $db;
		/* ------------------------------------------ */
		$pq_Code			= $_POST['pq_Code'];			//PK
		/* 입찰정보------------------------------------------ */
		$pq_UpdateUser		= $_POST['pq_UpdateUser'];		//등록/수정자
		$pq_UpdateDate		= $_POST['pq_UpdateDate'];		//등록/수정일자
		/* 입찰정보------------------------------------------ */
		$pq_Kind			= $_POST['pq_Kind'];			//구분:설계(1),건설사업관리(2)
		$pq_Part			= $_POST['pq_Part'];			//분야
		$pq_ProjectName		= $_POST['pq_ProjectName'];		//용역명
		$pq_OrderKind		= $_POST['pq_OrderKind'];		//발주방식
		$pq_Payment			= $_POST['pq_Payment'];			//설계금액(백만원)
		$pq_Payment = str_replace(",","",$pq_Payment);
		/* -------------------------------------------------- */
		$pq_OrderCompany	= $_POST['pq_OrderCompany'];	//발주처
		$pq_OrderGroup		= $_POST['pq_OrderGroup'];		//담당부서
		$pq_Deadline		= $_POST['pq_Deadline'];		//제출마감일자
		$pq_Gap				= $_POST['pq_Gap'];				//1등과의 차이
		/* -------------------------------------------------- */
		$pq_bidDateS_day	= $_POST['pq_bidDateS_day'];	//시작일자
		$pq_bidDateS_hour	= $_POST['pq_bidDateS_hour'];	//시작시간
		$pq_bidDateS_min	= $_POST['pq_bidDateS_min'];	//시작분
		if(strlen($pq_bidDateS_day)>0){
			$pq_bidDateS		= $pq_bidDateS_day." ".$pq_bidDateS_hour.":".$pq_bidDateS_min.":00";
		}else{
			$pq_bidDateS		= '';
		}//if End
		/* -------------------------------------------------- */
		$pq_bidDateE_day	= $_POST['pq_bidDateE_day'];	//종료일자
		$pq_bidDateE_hour	= $_POST['pq_bidDateE_hour'];	//종료시간
		$pq_bidDateE_min	= $_POST['pq_bidDateE_min'];	//종료분
		$pq_bidDateE		= $pq_bidDateE_day." ".$pq_bidDateE_hour.":".$pq_bidDateE_min.":00";
		if(strlen($pq_bidDateE_day)>0){
			$pq_bidDateE		= $pq_bidDateE_day." ".$pq_bidDateE_hour.":".$pq_bidDateE_min.":00";
		}else{
			$pq_bidDateE		= '';
		}//if End
		/* -------------------------------------------------- */
		$pq_VestedCompany	= $_POST['pq_VestedCompany'];	//전차회사
		/* 공동도급----------------------------------------- */
		/***************************************************************/
		$pq_CommonCompany_str="";//도급회사
		$pq_CommonCompany = array();
		/*----------------------------------------- */
		//for($i=1;$i<6;$i++){
		for($i=1;$i<7;$i++){ /*20150610 : 도급사 추가 (기존:5개->변경:6개) : 양순호차장 요청*/
			if($_POST['pq_CommonCompany'.$i]!=""){
			$pq_CommonCompany[$i]	=   $_POST['pq_CommonCompany'.$i];
			}
		}//for End
		/*----------------------------------------- */
		$arrayCnt01 = count($pq_CommonCompany);
		/*----------------------------------------- */
		if($arrayCnt01==1){
			$pq_CommonCompany_str =	$pq_CommonCompany[1];
		}else{
			for($i=1;$i<$arrayCnt01+1;$i++){
				if($pq_CommonCompany[$i]!=""){
					if($i!=$arrayCnt01){
						$pq_CommonCompany[$i]=$pq_CommonCompany[$i]."+";
					}else{
						$pq_CommonCompany[$i]=$pq_CommonCompany[$i];
					}//if End
					$pq_CommonCompany_str = $pq_CommonCompany_str.$pq_CommonCompany[$i];
				}//if End
			}//for End
		}//if End
		//echo $pq_CommonCompany_str."<br>";
		/***************************************************************/
		$pq_CommonRate_str="";//도급비율
		$pq_CommonRate = array();
		/*----------------------------------------- */
		//for($i=1;$i<6;$i++){
		for($i=1;$i<7;$i++){ /*20150610 : 도급사 추가 (기존:5개->변경:6개) : 양순호차장 요청*/
			if($_POST['pq_CommonRate'.$i]!=""){
			$pq_CommonRate[$i]	=   $_POST['pq_CommonRate'.$i];
			}
		}//for End
		/*----------------------------------------- */
		$arrayCnt02 = count($pq_CommonRate);
		/*----------------------------------------- */
		if($arrayCnt02==1){
			$pq_CommonRate_str =	$pq_CommonRate[1];
		}else{
			for($i=1;$i<$arrayCnt02+1;$i++){
				if($pq_CommonRate[$i]!=""){
					if($i!=$arrayCnt02){
						$pq_CommonRate[$i]=$pq_CommonRate[$i].":";
					}else{
						$pq_CommonRate[$i]=$pq_CommonRate[$i];
					}//if End
					$pq_CommonRate_str = $pq_CommonRate_str.$pq_CommonRate[$i];
				}//if End
			}//for End
			$pq_CommonRate_str = $pq_CommonRate_str;
		}//if End
		/***************************************************************/

		/* 낙찰정보 -------------------------------------- */
		$pq_AcceptCompany	= $_POST['pq_AcceptCompany'];	//낙찰업체
		/* -------------------------------------------------- */
		$pq_bidprice		= $_POST['pq_bidprice'];		//낙찰금액(원)
		$pq_bidprice		= str_replace(",","",$pq_bidprice);
		$pq_Rank			= $_POST['pq_Rank'];			//당사순위
		/* -------------------------------------------------- */

		/* -------------------------------------------------- */
		$update_db =			" UPDATE			"; 
		$update_db = $update_db." pq_bid_tbl SET	";
		$update_db = $update_db."  UpdateUser	= '".$pq_UpdateUser."'			";
		$update_db = $update_db." ,UpdateDate	= '".$pq_UpdateDate."'			";
		$update_db = $update_db." ,Kind			= '".$pq_Kind."'				";
		$update_db = $update_db." ,Part			= '".$pq_Part."'				";
		$update_db = $update_db." ,ProjectName	= '".$pq_ProjectName."'			";
		$update_db = $update_db." ,OrderKind	= '".$pq_OrderKind."'			";
		$update_db = $update_db." ,Payment		= '".$pq_Payment."'				";
		$update_db = $update_db." ,OrderCompany	= '".$pq_OrderCompany."'		";
		$update_db = $update_db." ,OrderGroup	= '".$pq_OrderGroup."'			";
		$update_db = $update_db." ,Deadline		= '".$pq_Deadline."'			";
		$update_db = $update_db." ,Gap			= '".$pq_Gap."'					";
		$update_db = $update_db." ,bidDateS		= '".$pq_bidDateS."'			";
		$update_db = $update_db." ,bidDateE		= '".$pq_bidDateE."'			";
		$update_db = $update_db." ,VestedCompany = '".$pq_VestedCompany."'		";
		$update_db = $update_db." ,CommonCompany = '".$pq_CommonCompany_str."'	";
		$update_db = $update_db." ,CommonRate	= '".$pq_CommonRate_str."'		";
		$update_db = $update_db." ,AcceptCompany = '".$pq_AcceptCompany."'		";
		$update_db = $update_db." ,bidprice		= '".$pq_bidprice."'			";
		$update_db = $update_db." ,Rank			= '".$pq_Rank."'				";
		$update_db = $update_db." WHERE											";
		$update_db = $update_db." Code = '".$pq_Code."'							";
		/* -------------------------------------------------- */

//////////////////////////////////
echo $update_db;
mysql_query($update_db,$db);
//////////////////////////////////
		/*----------------------------------------*/
//mysql_close($db);
		/*----------------------------------------*/
	}  //UpdateDB() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function DeleteDB()	//삭제
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $nowYear;
		global $db;
		/* ------------------------------------------ */
		$pq_Code			= $_POST['pq_Code'];			//PK
		/*-----------------------------*/
		$delete_query = "DELETE FROM pq_bid_tbl WHERE Code = '".$pq_Code."'";
///////////////////////
mysql_query($delete_query);
///////////////////////

	}  //DeleteDB() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function InsertDB()//입력 DB실행
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		$GroupCode =(int)$GroupCode;
		/* ------------------------- */
		global $GroupName;
		global $date_today;
		global $nowYear;
		global $db;
		/* -------------------------------------------------- */
		$pq_UpdateUser		= $_POST['pq_UpdateUser'];		//등록/수정자
		$pq_UpdateDate		= $_POST['pq_UpdateDate'];		//등록/수정일자
		/* 입찰정보------------------------------------------ */
		$pq_Kind			= $_POST['pq_Kind'];			//구분:설계(1),건설사업관리(2)
		$pq_Part			= $_POST['pq_Part'];			//분야
		$pq_ProjectName		= $_POST['pq_ProjectName'];		//용역명
		$pq_OrderKind		= $_POST['pq_OrderKind'];		//발주방식
		$pq_Payment			= $_POST['pq_Payment'];			//설계금액(백만원)
		$pq_Payment = str_replace(",","",$pq_Payment);
		/* -------------------------------------------------- */
		$pq_OrderCompany	= $_POST['pq_OrderCompany'];	//발주처
		$pq_OrderGroup		= $_POST['pq_OrderGroup'];		//담당부서
		$pq_Deadline		= $_POST['pq_Deadline'];		//제출마감일자
		$pq_Gap				= $_POST['pq_Gap'];				//1등과의 차이
		/* -------------------------------------------------- */
		$pq_bidDateS_day	= $_POST['pq_bidDateS_day'];	//시작일자
		$pq_bidDateS_hour	= $_POST['pq_bidDateS_hour'];	//시작시간
		$pq_bidDateS_min	= $_POST['pq_bidDateS_min'];	//시작분
		if(strlen($pq_bidDateS_day)>0){
			$pq_bidDateS		= $pq_bidDateS_day." ".$pq_bidDateS_hour.":".$pq_bidDateS_min.":00";
		}else{
			$pq_bidDateS		= '';
		}//if End
		/* -------------------------------------------------- */
		$pq_bidDateE_day	= $_POST['pq_bidDateE_day'];	//종료일자
		$pq_bidDateE_hour	= $_POST['pq_bidDateE_hour'];	//종료시간
		$pq_bidDateE_min	= $_POST['pq_bidDateE_min'];	//종료분
		if(strlen($pq_bidDateS_day)>0){
			$pq_bidDateE		= $pq_bidDateE_day." ".$pq_bidDateE_hour.":".$pq_bidDateE_min.":00";
		}else{
			$pq_bidDateE		= '';
		}//if End
		/* -------------------------------------------------- */
		$pq_VestedCompany	= $_POST['pq_VestedCompany'];	//전차회사
		/* 공동도급----------------------------------------- */
		/***************************************************************/
		$pq_CommonCompany_str="";//도급회사
		$pq_CommonCompany = array();
		/*----------------------------------------- */
		//for($i=1;$i<6;$i++){
		for($i=1;$i<7;$i++){ /*20150610 : 도급사 추가 (기존:5개->변경:6개) : 양순호차장 요청*/
			if($_POST['pq_CommonCompany'.$i]!=""){
			$pq_CommonCompany[$i]	=   $_POST['pq_CommonCompany'.$i];
			}
		}//for End
		/*----------------------------------------- */
		$arrayCnt01 = count($pq_CommonCompany);
		/*----------------------------------------- */
		if($arrayCnt01==1){
			$pq_CommonCompany_str =	$pq_CommonCompany[1];
		}else{
			for($i=1;$i<$arrayCnt01+1;$i++){
				if($pq_CommonCompany[$i]!=""){
					if($i!=$arrayCnt01){
						$pq_CommonCompany[$i]=$pq_CommonCompany[$i]."+";
					}else{
						$pq_CommonCompany[$i]=$pq_CommonCompany[$i];
					}//if End
					$pq_CommonCompany_str = $pq_CommonCompany_str.$pq_CommonCompany[$i];
				}//if End
			}//for End
		}//if End
		//echo $pq_CommonCompany_str."<br>";
		/***************************************************************/
		$pq_CommonRate_str="";//도급비율
		$pq_CommonRate = array();
		/*----------------------------------------- */
		//for($i=1;$i<6;$i++){
		for($i=1;$i<7;$i++){ /*20150610 : 도급사 추가 (기존:5개->변경:6개) : 양순호차장 요청*/
			if($_POST['pq_CommonRate'.$i]!=""){
			$pq_CommonRate[$i]	=   $_POST['pq_CommonRate'.$i];
			}
		}//for End
		/*----------------------------------------- */
		$arrayCnt02 = count($pq_CommonRate);
		/*----------------------------------------- */
		if($arrayCnt02==1){
			$pq_CommonRate_str =	"(".$pq_CommonRate[1].")";
		}else{
			for($i=1;$i<$arrayCnt02+1;$i++){
				if($pq_CommonRate[$i]!=""){
					if($i!=$arrayCnt02){
						$pq_CommonRate[$i]=$pq_CommonRate[$i].":";
					}else{
						$pq_CommonRate[$i]=$pq_CommonRate[$i];
					}//if End
					$pq_CommonRate_str = $pq_CommonRate_str.$pq_CommonRate[$i];
				}//if End
			}//for End
			$pq_CommonRate_str = $pq_CommonRate_str;

		}//if End
		//echo $pq_CommonRate_str."<br>";
		/***************************************************************/

		/* 낙찰정보 -------------------------------------- */
		$pq_AcceptCompany	= $_POST['pq_AcceptCompany'];	//낙찰업체
		/* -------------------------------------------------- */
		$pq_bidprice		= $_POST['pq_bidprice'];		//낙찰금액(원)
		$pq_bidprice = str_replace(",","",$pq_bidprice);
		$pq_Rank			= $_POST['pq_Rank'];			//당사순위
		/* -------------------------------------------------- */
		/* Code(코드값):PK만들기------------------------------------------ */
		$re      = mysql_query("select max(Code) num from pq_bid_tbl",$db);
		$re_row = mysql_fetch_array($re);
		//echo $re_row[num]+1;
		$maxNum = $re_row[num]+1;
		/* -------------------------------------------------------------------------------------------------------- */
		//PK,분야,구분,용역명,발주방식,발주처,담당부서,설계금액(백만원),제출마감일자,1등과의 차이,시작일시,종료일시
		//,전차회사,도급회사,도급비율,,낙찰업체,당사순위,등록/수정일자,등록/수정자,ViewDisplay,낙찰금액(원)
		/* -------------------------------------------------------------------------------------------------------- */
		$insert_db =			" INSERT INTO                       "; 
		$insert_db = $insert_db." pq_bid_tbl                        ";
		$insert_db = $insert_db." (Code,Part,Kind,ProjectName,OrderKind,OrderCompany,OrderGroup,Payment,Deadline,Gap,bidDateS,bidDateE  	";
		$insert_db = $insert_db." ,VestedCompany,CommonCompany,CommonRate,AcceptCompany,Rank,UpdateDate,UpdateUser,ViewDisplay,bidprice)	";
		$insert_db = $insert_db." VALUES                            ";
		$insert_db = $insert_db." (                                 ";
		$insert_db = $insert_db."	  '".$maxNum."'		            ";
		$insert_db = $insert_db."	, '".$pq_Part."'				";
		$insert_db = $insert_db."	, '".$pq_Kind."'				";
		$insert_db = $insert_db."	, '".$pq_ProjectName."'		    ";
		$insert_db = $insert_db."	, '".$pq_OrderKind."'			";
		$insert_db = $insert_db."	, '".$pq_OrderCompany."'		";
		$insert_db = $insert_db."	, '".$pq_OrderGroup."'			";
		$insert_db = $insert_db."	, '".$pq_Payment."'			    ";
		$insert_db = $insert_db."	, '".$pq_Deadline."'			";
		$insert_db = $insert_db."	, '".$pq_Gap."'				    ";
		$insert_db = $insert_db."	, '".$pq_bidDateS."'			";
		$insert_db = $insert_db."	, '".$pq_bidDateE."'			";
		$insert_db = $insert_db."	, '".$pq_VestedCompany."'		";
		$insert_db = $insert_db."	, '".$pq_CommonCompany_str."'	";
		$insert_db = $insert_db."	, '".$pq_CommonRate_str."'		";
		$insert_db = $insert_db."	, '".$pq_AcceptCompany."'		";
		$insert_db = $insert_db."	, '".$pq_Rank."'				";
		$insert_db = $insert_db."	, '".$pq_UpdateDate."'			";
		$insert_db = $insert_db."	, '".$depart_pw."'				";
		$insert_db = $insert_db."	, ''						    ";
		$insert_db = $insert_db."	, '".$pq_bidprice."'			";
		$insert_db = $insert_db." )                                 ";
	//////////////////////////////////
	//echo "insert_db::<br>".$insert_db;
	mysql_query($insert_db,$db);
	//////////////////////////////////
	//mysql_close($db);
	//////////////////////////////////

	}  //InsertDB() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function ValueTest()//
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		$GroupCode =(int)$GroupCode;
		
		global $GroupName;
		global $date_today;
		global $nowYear;

		global $db;
		$pq_Code			= $_POST['pq_Code'];			//PK
		$pq_UpdateUser		= $_POST['pq_UpdateUser'];		//등록/수정자
		$pq_UpdateDate		= $_POST['pq_UpdateDate'];		//등록/수정일자
		/* 입찰정보------------------------------------------ */
		$pq_Kind			= $_POST['pq_Kind'];			//구분:설계(1),건설사업관리(2)
		$pq_Part			= $_POST['pq_Part'];			//분야
		$pq_ProjectName		= $_POST['pq_ProjectName'];		//용역명
		$pq_OrderKind		= $_POST['pq_OrderKind'];		//발주방식
		$pq_Payment			= $_POST['pq_Payment'];			//설계금액(백만원)
		$pq_Payment = str_replace(",","",$pq_Payment);
		/* -------------------------------------------------- */
		$pq_OrderCompany	= $_POST['pq_OrderCompany'];	//발주처
		$pq_OrderGroup		= $_POST['pq_OrderGroup'];		//담당부서
		$pq_Deadline		= $_POST['pq_Deadline'];		//제출마감일자
		$pq_Gap				= $_POST['pq_Gap'];				//1등과의 차이
		/* -------------------------------------------------- */
		$pq_bidDateS_day	= $_POST['pq_bidDateS_day'];	//시작일자
		$pq_bidDateS_hour	= $_POST['pq_bidDateS_hour'];	//시작시간
		$pq_bidDateS_min	= $_POST['pq_bidDateS_min'];	//시작분
		$pq_bidDateS		= $pq_bidDateS_day." ".$pq_bidDateS_hour.":".$pq_bidDateS_min.":00";
		/* -------------------------------------------------- */
		$pq_bidDateE_day	= $_POST['pq_bidDateE_day'];	//종료일자
		$pq_bidDateE_hour	= $_POST['pq_bidDateE_hour'];	//종료시간
		$pq_bidDateE_min	= $_POST['pq_bidDateE_min'];	//종료분
		$pq_bidDateE		= $pq_bidDateE_day." ".$pq_bidDateE_hour.":".$pq_bidDateE_min.":00";
		/* -------------------------------------------------- */
		$pq_VestedCompany	= $_POST['pq_VestedCompany'];	//전차회사
		/* 공동도급----------------------------------------- */
		/***************************************************************/
		$pq_CommonCompany_str="";//도급회사
		$pq_CommonCompany = array();
		/*----------------------------------------- */
		//for($i=1;$i<6;$i++){
		for($i=1;$i<7;$i++){ /*20150610 : 도급사 추가 (기존:5개->변경:6개) : 양순호차장 요청*/
			if($_POST['pq_CommonCompany'.$i]!=""){
			$pq_CommonCompany[$i]	=   $_POST['pq_CommonCompany'.$i];
			}
		}//for End
		/*----------------------------------------- */
		$arrayCnt01 = count($pq_CommonCompany);
		/*----------------------------------------- */
		if($arrayCnt01==1){
			$pq_CommonCompany_str =	$pq_CommonCompany[1];
		}else{
			for($i=1;$i<$arrayCnt01+1;$i++){
				if($pq_CommonCompany[$i]!=""){
					if($i!=$arrayCnt01){
						$pq_CommonCompany[$i]=$pq_CommonCompany[$i]."+";
					}else{
						$pq_CommonCompany[$i]=$pq_CommonCompany[$i];
					}//if End
					$pq_CommonCompany_str = $pq_CommonCompany_str.$pq_CommonCompany[$i];
				}//if End
			}//for End
		}//if End
		//echo $pq_CommonCompany_str."<br>";
		/***************************************************************/
		$pq_CommonRate_str="";//도급비율
		$pq_CommonRate = array();
		/*----------------------------------------- */
      //for($i=1;$i<6;$i++){
		for($i=1;$i<7;$i++){ /*20150610 : 도급사 추가 (기존:5개->변경:6개) : 양순호차장 요청*/
			if($_POST['pq_CommonRate'.$i]!=""){
			$pq_CommonRate[$i]	=   $_POST['pq_CommonRate'.$i];
			}
		}//for End
		/*----------------------------------------- */
		$arrayCnt02 = count($pq_CommonRate);
		/*----------------------------------------- */
		if($arrayCnt02==1){
			$pq_CommonRate_str =	"(".$pq_CommonRate[1].")";
		}else{
			for($i=1;$i<$arrayCnt02+1;$i++){
				if($pq_CommonRate[$i]!=""){
					if($i!=$arrayCnt02){
						$pq_CommonRate[$i]=$pq_CommonRate[$i].":";
					}else{
						$pq_CommonRate[$i]=$pq_CommonRate[$i];
					}//if End
					$pq_CommonRate_str = $pq_CommonRate_str.$pq_CommonRate[$i];
				}//if End
			}//for End
			$pq_CommonRate_str = $pq_CommonRate_str;

		}//if End
		//echo $pq_CommonRate_str."<br>";
		/***************************************************************/

		/* 낙찰정보 -------------------------------------- */
		$pq_AcceptCompany	= $_POST['pq_AcceptCompany'];	//낙찰업체
		/* -------------------------------------------------- */
		$pq_bidprice		= $_POST['pq_bidprice'];		//낙찰금액(원)
		$pq_bidprice = str_replace(",","",$pq_bidprice);
		$pq_Rank			= $_POST['pq_Rank'];			//당사순위
		/* -------------------------------------------------- */
		$this->smarty->assign('pq_Code',$pq_Code);
		$this->smarty->assign('pq_UpdateUser',$pq_UpdateUser);
		$this->smarty->assign('pq_UpdateDate',$pq_UpdateDate);
		$this->smarty->assign('pq_Kind',$pq_Kind);
		$this->smarty->assign('pq_Part',$pq_Part);
		$this->smarty->assign('pq_ProjectName',$pq_ProjectName);
		$this->smarty->assign('pq_OrderKind',$pq_OrderKind);
		
		$this->smarty->assign('pq_Payment',$pq_Payment);
		$this->smarty->assign('pq_OrderCompany',$pq_OrderCompany);
		$this->smarty->assign('pq_OrderGroup',$pq_OrderGroup);
		$this->smarty->assign('pq_Deadline',$pq_Deadline);
		$this->smarty->assign('pq_Gap',$pq_Gap);

		$this->smarty->assign('pq_bidDateS_day',$pq_bidDateS_day);
		$this->smarty->assign('pq_bidDateS_hour',$pq_bidDateS_hour);
		$this->smarty->assign('pq_bidDateS_min',$pq_bidDateS_min);
		$this->smarty->assign('pq_bidDateS',$pq_bidDateS);
		
		$this->smarty->assign('pq_bidDateE_day',$pq_bidDateE_day);
		$this->smarty->assign('pq_bidDateE_hour',$pq_bidDateE_hour);
		$this->smarty->assign('pq_bidDateE_min',$pq_bidDateE_min);
		$this->smarty->assign('pq_bidDateE',$pq_bidDateE);

		$this->smarty->assign('pq_VestedCompany',$pq_VestedCompany);
		$this->smarty->assign('pq_CommonCompany_str',$pq_CommonCompany_str);
		$this->smarty->assign('pq_CommonRate_str',$pq_CommonRate_str);
		$this->smarty->assign('pq_AcceptCompany',$pq_AcceptCompany);
		$this->smarty->assign('pq_bidprice',$pq_bidprice);
		$this->smarty->assign('pq_Rank',$pq_Rank);

		$this->smarty->display("intranet/common_contents/work_bidPlan/valueTest.tpl");

	}  //ValueTest() End
	/* ------------------------------------------------------------------------------ */

}//class  End
/* ****************************************************************************************************************** */
?>
