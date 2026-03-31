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
	}else{
		/* ----------------------------------- */
		//$memberID	=	$_GET['memberID'];
		$memberID = ($_GET['memberID']==""?$_POST['ajax_out_p_code']:$_GET['memberID']);

		$MemberNo	=	$memberID;
		$MemberNo	=	strtoupper($MemberNo);
		/* ----------------------------------- */
		require('../../sys/popup/setInfo.php');
		/* ----------------------------------- */
	}//if End
	/* ----------------------------------- */
		/* 수정을 위해 넘어온 GET VALUE****************************** */
		/* get 파라미터 한글깨짐 방지----------------------------------- */
		/*$edit_main_p_code	= iconv('euc-kr', 'utf-8', $_GET['main_p_code']); */
		/* ************************************************************** */
	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
?>

<?php
class PmResisterLogic {
	var $smarty;// 생성자

	function PmResisterLogic($smarty)
	{ 
		$this->smarty=$smarty;
	}
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function ProjectKind()  //프로젝트구분
	{
		global $MemberNo;
		global $memberID;
		global $nowYear;
		global $GroupCode;
		global $CompanyKind;
		global $db;
		/*-------------------------------------*/
		$projectKind = array(); 
		/*-------------------------------------*/
		/*업체분류 관련 셀렉트*/
		//  Start ************************************************* */
		$sql01=        " SELECT										";// 
		$sql01= $sql01."  SYS.SysKey as sys_SysKey					";// 
		$sql01= $sql01." ,SYS.Code as sys_Code						";// 
		$sql01= $sql01." ,SYS.Name as sys_Name						";// 
		$sql01= $sql01." ,SYS.Note as sys_Note						";// 
		$sql01= $sql01." ,SYS.CodeORName as sys_CodeORName			";// 
		$sql01= $sql01." ,SYS.Description as sys_Description		";// 
		$sql01= $sql01." FROM										";// 
		$sql01= $sql01." SYSTEMCONFIG_TBL SYS						";// 
		$sql01= $sql01." where										";// 
		$sql01= $sql01." SysKey = 'ProjectCode'						";// 
		$sql01= $sql01." ORDER BY SYS.CodeORName ASC,SYS.Name ASC	";// 
//echo $sql01;
		/////////////////
		//echo "03::<br>".$sql01."<br>"; 
		/////////////////
		/* ----------------------------------- */
		$CodeName="";
		/* ----------------------------------- */
		$re01 = mysql_query($sql01,$db);
		/* ----------------------------------- */
		while($re_row01 = mysql_fetch_array($re01)) {
			/* ----------------------------------- */
			$CodeName = $re_row01[sys_CodeORName];
			/* ----------------------------------- */
			if($CodeName=="01"){
				$re_row01[CodeName] = "설계";
			}else if($CodeName=="02"){
				$re_row01[CodeName] = "감리";
			}else if($CodeName=="03"){
				$re_row01[CodeName] = "시공";
			}else if($CodeName=="04"){
				$re_row01[CodeName] = "정통";
			}else if($CodeName=="05"){
				$re_row01[CodeName] = "R&D";
			}else if($CodeName=="06"){
				$re_row01[CodeName] = "영업";
			}else if($CodeName=="07"){
				$re_row01[CodeName] = "경영<br>지원";
			}//if End
			/* ----------------------------------- */
			$re_row01[sys_Note_short] = utf8_strcut($re_row01[sys_Note],11,'..');
			/* ----------------------------------- */
			array_push($projectKind,$re_row01);
			/* ----------------------------------- */
		} //while End
		/* ----------------------------------- */
		$this->smarty->assign('projectKind',$projectKind);	//프로젝트구분
		/*-----------------------------------------------*/
	}  //ProjectKind() End
	/* ***************************************************************************************** */


	/* ***************************************************************************************** */
	function PageList()  //목록페이지로 이동
	{

//echo $_SERVER['PHP_SELF'];

		global $MemberNo;
		global $memberID;
		global $GroupCode;
		global $CompanyKind;
		//$GroupCode = (int)$GroupCode;
		/*-----------------*/
		global $db;
		/*-----------------*/
		global $start;
		global $page;	
		global $currentPage;
		/*-----------------*/
		global $last_page;
		global $sub_index;
		/*-----------------*/
		$currentPage	=	$_GET['currentPage'];
		$start			=	$_GET['start'];
		/*-----------------------------------------------*/
		$page=15; //한페이지에 표시될 로우의 갯수
		/*-----------------------------------------------*/
		if($currentPage==""){
			$start = 0;
			$currentPage = 1;
		}else{
			$start=$page*($currentPage-1);
		}//if End
		/*-----------------------------------------------*/
		$str_val01   = ($_GET['str_val01']==""?"고속":$_GET['str_val01']);	//프로젝트코드 H15-고속-01 >> 고속
		$str_val02   = ($_GET['str_val02']==""?"1":$_GET['str_val02']);		//프로젝트 수행상태 : 1=수행중/수행중지, 2=수주활동중, 3=준공완료
		$searchStr   = ($_GET['searchStr']==""?"":$_GET['searchStr']);		//프로젝트명,발주처 검색값
		/* ------------------------------- */
		$mine   = ($_GET['mine']==""?"0":$_GET['mine']);	
		$add_where03 = "";
		if($mine=="1"){
			$add_where03 = " AND P.Name ='".$memberID."' ";
		}else{
			$add_where03 = "";
		}//if End
		/* ------------------------------- */
	

		/* ------------------------------- */
		$add_where01 = ""; //조건절 추가01 : 프로젝트 수행상태
			if($searchStr=="" && $mine <> "1")
			{
				$add_where00 =" AND";
			}
				
			if($str_val02==1){
				$add_where01 = " WorkStatus in('수행중','수행중지') ";
			}elseif($str_val02==2){
				$add_where01 = " WorkStatus in('수주활동중') ";
			}elseif($str_val02==3){
				$add_where01 = " WorkStatus in('준공완료') ";
			}//if End
		
		/* ------------------------------- */
		$add_where02 = ""; //조건절 추가02 : 검색어
			if(strlen($searchStr)>0){ //검색어 존재
				$add_where02 = " AND													 ";
				$add_where02 = $add_where02." (  										 ";
				$add_where02 = $add_where02."   ProjectName like '%".$searchStr."%'		 ";
				$add_where02 = $add_where02." OR ProjectNickname like '%".$searchStr."%' ";
				$add_where02 = $add_where02." OR OrderCompany like '%".$searchStr."%'	 ";
				$add_where02 = $add_where02." OR OrderNickname like '".$searchStr."' 	 ";
				$add_where02 = $add_where02." )  										 ";

			}else if($mine == "1"){ 
				$add_where02 = " AND Name ='".$memberID."' ";
			}else{//검색어 없음
				$add_where02 = "";
			}//if End
		/*-----------------------------------------------*/
		if($searchStr == "" && $mine <> "1"){ //검색어 없으면
			$sql_count  = "select COUNT(*) CNT from project_tbl WHERE ProjectCode like '%".$str_val01."%' ".$add_where00.$add_where01.$add_where02;
		}else{
			$sql_count  = "select COUNT(*) CNT from project_tbl WHERE ".$add_where01.$add_where02;
		}
//echo "01::".$sql_count."<br>"; 
		/*-----------------------------------------------*/
		$re       = mysql_query($sql_count);
		$re_count = mysql_result($re,0,"CNT"); 	
		/*-----------------------------------------------*/
		$TotalRow   = $re_count;              //총 개수 저장
//echo "01::".$TotalRow."<br>"; 

		//마지막페이지 
		$last_start = ceil($TotalRow/10)*10+1;
		$last_page  = ceil($TotalRow/$page);
		/*-----------------------------------------------*/
		$query_data = array(); 
		/*-----------------------------------------------*/
		$sql=     "	SELECT                                              ";
		$sql=$sql."  P.ProjectCode				as p_ProjectCode		";//용역_프로젝트코드
		$sql=$sql."	,P.ProjectName				as p_ProjectName		";//용역_프로젝트 네임
		$sql=$sql."	,P.ProjectNickname			as p_ProjectNickname	";//용역_프로젝트 닉네임
		$sql=$sql."	,P.OrderCompany				as p_OrderCompany		";//용역_발주처 네임
		$sql=$sql."	,P.OrderNickname			as p_OrderNickname		";//용역_발주처 닉네임
		$sql=$sql."	,P.ContractPayment			as p_ContractPayment	";//용역_계약금액
		$sql=$sql."	,P.WorkStatus				as p_WorkStatus			";//용역_진행상태
		$sql=$sql."	,P.Name              		as p_Name              	";//용역_사내담당자:사원번호
		$sql=$sql."	,P.ActualityRatio    		as p_ActualityRatio    	";//용역_
		$sql=$sql."	FROM 												";
		$sql=$sql."		project_tbl	P									";
		$sql=$sql."	WHERE												";//
		if($searchStr == "" && $mine <> "1"){ //모두,담당자 사업아니면
		$sql=$sql."	ProjectCode like '%".$str_val01."%'					";//
		}
		/*-----------------------------------------------*/
		$sql=$sql.$add_where00;
		$sql=$sql.$add_where01;
		if($mine <> "1")
		{
		$sql=$sql.$add_where02;
		}
		/*-----------------------------------------------*/
		$sql=$sql.$add_where03;

		/*-----------------------------------------------*/
		$sql=$sql."	ORDER BY year DESC, ProjectCode DESC	";
		$sql=$sql."	LIMIT ".$start." , ".$page."			";
	/*-----------------------------------------------*/
	/////////////////
//echo "01::".$sql."<br>"; 
	/////////////////
		$re = mysql_query($sql,$db);
	/*-----------------------------------------------*/
	while($re_row = mysql_fetch_array($re)) {
		$result_contract_payment = $re_row[p_ContractPayment];//전체 용역공사금액
		$result_ActualityRatio   = $re_row[p_ActualityRatio];//지분 계약금액
		/*-----------------------------------------------*/
		//$sum = $result_contract_payment * $result_contract_ratio / 100; //계약지분율
		$sum = $result_contract_payment * $result_ActualityRatio / 100;	//실지분율로 변경
		/*-----------------------------------------------*/

		//수금액
		$sql02 = "select sum(CP.CollectionPayment) cp_sum from collectionpayment_tbl CP where CP.ProjectCode = '".$re_row[p_ProjectCode]."' and CP.CollectionDate <> ''";
//echo $sql02."<br>";
		$result02 = mysql_query($sql02,$db);
		$result_collection = mysql_result($result02,0,"cp_sum");  

			if($sum>0){
				$collectionrate=($result_collection/$sum)*100;
			}else{
				$collectionrate=0;
			}
				
			$collectionrate=sprintf("%.0f",round($collectionrate,1));
			$re_row[collectionrate] = $collectionrate;

		/*권한01------------------------------*/
		$contentAuth="0"; //default:0권한없음, 1:수정및상세보기 권한
		if($re_row[p_Name] <> ""){
			$re_row[p_MemberName] = MemberNo2Name($re_row[p_Name]);//사원명
			if($memberID==$re_row[p_Name]){
				$contentAuth="1";
			}
		}//if End
		$re_row[p_contentAuth] = $contentAuth;
		/*------------------------------*/

		/* 외주비:총괄내역-버튼유무*/
		$whereQuery01 = "  WHERE ProjectCode ='".$re_row[p_ProjectCode]."' ";
		$re_tableRowYN01 = tableRowCount("outsideordercontract_tbl", $whereQuery01); //tableRowCount(테이블명, 쿼리) | return:Y=있음, N=없음
		//echo $re_tableRowCount;
		$re_row[re_tableRowYN01] = $re_tableRowYN01;
		/*------------------------------*/

		/* 외주비:지급내역-버튼유무*/
		$whereQuery02 = "  WHERE ProjectCode ='".$re_row[p_ProjectCode]."' ";
		$re_tableRowYN02 = tableRowCount("Outside_Pay_Disbursement_tbl", $whereQuery02); //tableRowCount(테이블명, 쿼리) | return:Y=있음, N=없음
		//echo $re_tableRowCount;
		$re_row[re_tableRowYN02] = $re_tableRowYN02;
		/*------------------------------*/

		array_push($query_data,$re_row);
	} //while End
	/*-----------------------------------------------*/

	$auth_business = checkAuthCRUD($memberID,"사업"); //권한체크(사원번호,권한명) return:1=권한있음, 0=권한없음
	/*세션 끊긴다는 클레임발생하여 펑션생성하여 대체=>checkAuthCRUD()
	if ($_SESSION['auth_business']){//권한 : 사업
	//echo "트루";
		$auth_business = "1";
	}else{
	//echo "펄스";
		$auth_business = "0";
	}//if End
	*/
	/* tableRowCount($mCode,$mCode2) { //$mCode: 테이블명,$mCode2:쿼리 */







	/*-----------------------------------------------*/
	/* 페이지네비 관련SET Start ------------------- */
	$PageHandler = new PageControl($this->smarty);
	$PageHandler-> SetMaxRow($TotalRow);
	$PageHandler-> SetCurrentPage($currentPage);
	$PageHandler-> PutTamplate();
	/* 페이지네비 관련SET End ------------------- */
		$this->smarty->assign('HTTP',$_SERVER['HTTP_USER_AGENT']);
		/*------------------- */
		$this->smarty->assign("page_action","pmResister_controller.php");
		/*------------------- */
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('set_GroupCode',$set_GroupCode);
		$this->smarty->assign('auth_business',$auth_business);//권한 : 사업
		/*------------------- */
		$this->smarty->assign('start',$start);
		$this->smarty->assign('TotalRow',$TotalRow);
		$this->smarty->assign('last_start',$last_start);
		$this->smarty->assign('last_page',$last_page);
		$this->smarty->assign('currentPage',$currentPage);
		/*------------------- */
		$this->smarty->assign('str_val01',$str_val01);//default:고속    H15-고속-01 >> 고속 
		$this->smarty->assign('str_val02',$str_val02);//프로젝트 수행상태 : 1=수행중/수행중지, 2=수주활동중, 3=준공완료

		$this->smarty->assign('sub_index',$sub_index);
		/*------------------- */
		$this->smarty->assign('query_data',$query_data);
		/*------------------- */
		$this->ProjectKind();
		$this->smarty->display("intranet/common_contents/work_pmResister/listPage.tpl");
		/*------------------- */

	}  //PageList() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function ViewPage()//상세보기 페이지로 이동
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		global $db;
		/*---------------------------------------*/
		$content_id	= (int)$_GET['content_id'];
		/*---------------------------------------*/
		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT										";
		$sql=$sql."  OC.Company			as oc_Company		  	";//업체명
		$sql=$sql."	,OC.CompanyNicName	as oc_CompanyNicName  	";//약칭
		$sql=$sql."	,OC.Part			as oc_Part			  	";//분야
		$sql=$sql."	,OC.DetailPart		as oc_DetailPart	  	";//세부공종
		$sql=$sql."	,OC.Phone			as oc_Phone		      	";//업체대표번호 
		$sql=$sql."	,OC.Fax				as oc_Fax			  	";//업체FAX
		$sql=$sql."	,OC.Name			as oc_Name			  	";//업체담당자    컬럼 네이밍 정말 ..!!
		$sql=$sql."	,OC.MemberNo		as oc_MemberNo		  	";//사내담당자 사원번호(한맥직원)
		$sql=$sql."	,OC.MainGroup		as oc_MainGroup	      	";//담당부서코드
		$sql=$sql."	,OC.Address			as oc_Address		  	";//주소
		$sql=$sql."	,OC.P_Name			as oc_P_Name		  	";//대표자
		$sql=$sql."	,OC.P_Mobile		as oc_P_Mobile		  	";//대표자연락처
		$sql=$sql."	,OC.ProviderNo		as oc_ProviderNo	  	";//사업자번호
		$sql=$sql."	,OC.Staff			as oc_Staff		      	";//종업원규모
		$sql=$sql."	,OC.BeginningDate	as oc_BeginningDate   	";//최초영업일자
		$sql=$sql."	,OC.Fortune			as oc_Fortune		  	";//회사자산
		$sql=$sql."	,OC.SalesAmount		as oc_SalesAmount	  	";//매출액
		$sql=$sql."	,OC.TaxArrear		as oc_TaxArrear	      	";//세금체납여부(1:체납, default:0:체납없음)
		$sql=$sql."	,OC.RegisterDate	as oc_RegisterDate	  	";//업체등록일자
		$sql=$sql."	,OC.UpdateDate		as oc_UpdateDate	  	";//등록일자
		$sql=$sql."	,OC.UpdateUser		as oc_UpdateUser	  	";//등록자 사원번호
		$sql=$sql."	,OC.ListDisplay		as oc_ListDisplay	  	";//리스트에서 숨김처리 checked:숨김:1,  default:0
		$sql=$sql."	,OC.no				as oc_no			";//PK
		$sql=$sql."	FROM 										";
		$sql=$sql."		outside_cooperation_tbl	OC				";
		$sql=$sql."	WHERE										";
		$sql=$sql."	OC.no=".$content_id."						";
		/*---------------------------------------*/
		/////////////////
		//echo "01::".$sql."<br>"; 
		/////////////////
			/*-----------------------------*/
			$re = mysql_query($sql,$db);
			/*-----------------------------*/
			while($re_row = mysql_fetch_array($re)) {
				$oc_MainGroup = $re_row[oc_MainGroup];
				$re_row[oc_MainGroupName] = Code2Name($oc_MainGroup,"GroupCode","");//return description 그룹명
				/*-----------------------------*/
				$oc_MemberNo = $re_row[oc_MemberNo];
				$re_row[oc_MemberName] = MemberNo2Name($oc_MemberNo);//return 사원명
				/*-----------------------------*/
				array_push($query_data,$re_row);
			} //while End
		
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_pmResister/viewPage.tpl");
		/*----------------------------------------*/
	}  //ViewPage() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function PageList02()// // 관리대장 -> 페이지이동 : 외주비 지급내역서
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		global $date_today;
		/*---------------------------------------*/
		global $db;
		/*---------------------------------------*/
		$content_id	= $_GET['content_id'];
		//echo "01::".$content_id."<br>"; 
		/*---------------------------------------*/
		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT                                          ";
		$sql=$sql."  OPD.ProjectCode    	as opd_ProjectCode     	";//프로젝트코드
		$sql=$sql."	,OPD.Part           	as opd_Part            	";//분야
		$sql=$sql."	,OPD.DetailPart     	as opd_DetailPart      	";//분야 : 세부공종
		$sql=$sql."	,OPD.Company        	as opd_Company          ";//업체명
		$sql=$sql."	,OPD.Kind           	as opd_Kind             ";//지급액 종류(준공/기성)
		$sql=$sql."	,OPD.DemandDate     	as opd_DemandDate      	";//청구일
		$sql=$sql."	,OPD.DisburseDate   	as opd_DisburseDate    	";//지급일
		$sql=$sql."	,OPD.DisbursePayment	as opd_DisbursePayment 	";//청구/지급액
		$sql=$sql."	,OPD.TaxFree        	as opd_TaxFree         	";//세금 1=비과세
		$sql=$sql."	,OPD.Note           	as opd_Note            	";//비고(미지급금)
		$sql=$sql."	,OPD.No             	as opd_No              	";//
		$sql=$sql."	,OPD.UpdateDate     	as opd_UpdateDate      	";//년월일 00:00:00
		$sql=$sql."	,OPD.UpdateUser     	as opd_UpdateUser      	";//사원번호
		$sql=$sql."	FROM                                            ";
		$sql=$sql."	Outside_Pay_Disbursement_tbl OPD                ";
		$sql=$sql."	WHERE                                           ";
		$sql=$sql."	OPD.ProjectCode ='".$content_id."'              ";
		$sql=$sql."	ORDER BY OPD.Company ASC, OPD.Part ASC, OPD.DetailPart ASC, OPD.DemandDate ASC   ";
		/*---------------------------------------*/


/////////////////
//echo "01::".$sql."<br>"; 
			/////////////////
			/*-----------------------------*/
			$re = mysql_query($sql,$db);
			/*-----------------------------*/
			$cnt=0;
			while($re_row = mysql_fetch_array($re)) {
				//$re_row[p_ContractPayment]; 

				$opd_TaxFree = $re_row[opd_TaxFree]; 
				if($opd_TaxFree=="1"){
					$re_row[opd_TaxFreeName] = "비과세"; 
				}else{
					$re_row[opd_TaxFreeName] = "과세"; 
				}//if End

				$re_row[opd_DisbursePayment]  = number_format($re_row[opd_DisbursePayment]);//용역_계약금액 : 단위(백만원)



				array_push($query_data,$re_row);
				$cnt++;
			} //while End
			/*-----------------------------*/
		$ProjectNickname = projectToColumn($content_id,"ProjectNickname");//프로젝트 닉네임 

		//지급액 합계 
		$sql02 = "select sum(DisbursePayment) DisbursePaymentSum from Outside_Pay_Disbursement_tbl where ProjectCode='".$content_id."'  ";
//echo $sql02."<br>";
		$result02 = mysql_query($sql02,$db);
		$DisbursePaymentSum = mysql_result($result02,0,"DisbursePaymentSum"); 
		$DisbursePaymentSum  = number_format($DisbursePaymentSum);

		/*-----------------------------*/
		$this->smarty->assign('DisbursePaymentSum',$DisbursePaymentSum);//지급액 합계 
		$this->smarty->assign('ProjectNickname',$ProjectNickname);
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('cnt',$cnt);
		$this->smarty->assign('date_today',$date_today);
		$this->smarty->assign('content_id',$content_id);
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_pmResister/listPage02.tpl");
		/*----------------------------------------*/

	}  //PageList02() End
	/* ***************************************************************************************** */




	/* ***************************************************************************************** */
	function PageList03()// [페이지이동] 관리대장 -> 수금현황 -> 수금내역 ->수금현황 목록
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		global $date_today;
		/*---------------------------------------*/
		global $db;
		/*---------------------------------------*/
		$content_id	= $_GET['content_id'];

		//$content_id	= iconv("EUC-KR", "UTF-8", $content_id);
		//echo "::".$content_id."<br>"; 

		/*---------------------------------------*/
		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT												";
		$sql=$sql."  CP.ProjectCode          as cp_ProjectCode        	";//
		$sql=$sql."	,CP.ContractStep         as cp_ContractStep       	";//
		$sql=$sql."	,CP.Kind                 as cp_Kind               	";//
		$sql=$sql."	,CP.DemandDate           as cp_DemandDate         	";//
		$sql=$sql."	,CP.CollectionDate       as cp_CollectionDate     	";//
		$sql=$sql."	,CP.CollectionPayment    as cp_CollectionPayment  	";//
		$sql=$sql."	,CP.Note                 as cp_Note               	";//
		$sql=$sql."	,CP.No                   as cp_No                 	";//
		$sql=$sql."	,CP.UpdateDate           as cp_UpdateDate         	";//
		$sql=$sql."	,CP.UpdateUser           as cp_UpdateUser         	";//
		$sql=$sql."	,CP.PaymentCompany       as cp_PaymentCompany     	";//
		$sql=$sql."	,CP.CollPaymentType      as cp_CollPaymentType    	";//
		$sql=$sql."	,CP.BillDate             as cp_BillDate           	";//
		$sql=$sql."	,CP.BillInputDate        as cp_BillInputDate      	";//
		$sql=$sql."	,CP.BillExpiryDate       as cp_BillExpiryDate     	";//
		$sql=$sql."	,CP.BillBank             as cp_BillBank           	";//
		$sql=$sql."	,CP.BillDiscountDate     as cp_BillDiscountDate   	";//
		$sql=$sql."	,CP.BillDiscountAmount   as cp_BillDiscountAmount 	";//
		$sql=$sql."	,CP.BillDiscountRate     as cp_BillDiscountRate   	";//
		$sql=$sql."	,CP.IncomAmount          as cp_IncomAmount        	";//
		$sql=$sql."	,CP.BillAmount           as cp_BillAmount         	";//
		$sql=$sql."	,CP.DemandSendDate       as cp_DemandSendDate     	";//
		$sql=$sql."	,CP.filename             as cp_filename           	";//
		$sql=$sql."	,CP.account              as cp_account            	";//

		$sql=$sql."	FROM												";
		$sql=$sql."	collectionpayment_tbl CP							";
		$sql=$sql."	WHERE												";
		$sql=$sql."	CP.ProjectCode ='".$content_id."'					";
		$sql=$sql."	ORDER BY CP.DemandDate ASC							";
		/*---------------------------------------*/
		/////////////////
//echo "01::".$sql."<br>"; 
		/////////////////
		/*-----------------------------*/
		$re = mysql_query($sql,$db);
		/*-----------------------------*/
		$cnt=0;
		while($re_row = mysql_fetch_array($re)) {
			$re_row[cp_CollectionPayment]; 
			$re_row[cp_CollectionPayment]  = number_format($re_row[cp_CollectionPayment]);

			array_push($query_data,$re_row);
			$cnt++;
		} //while End
		/*---------------------------------------*/
		//프로젝트 닉네임  
		$ProjectNickname = projectToColumn($content_id,"ProjectNickname");
		/*-----------------------------*/
		//용역 총계약금액 
		$ContractPayment = projectToColumn($content_id,"ContractPayment");

		$ActualityRatio = projectToColumn($content_id,"ActualityRatio");
		$per_ContractPayment =$ContractPayment*$ActualityRatio/100;
		$ch_per_ContractPayment = number_format($per_ContractPayment);
		/*-----------------------------*/
		//수금액합계
		$sql02 = "select sum(CollectionPayment) CollectionPaymentSum from collectionpayment_tbl where ProjectCode='".$content_id."'  ";
		$result02 = mysql_query($sql02,$db);
		$CollectionPaymentSum = mysql_result($result02,0,"CollectionPaymentSum"); 
		$ch_CollectionPaymentSum  = number_format($CollectionPaymentSum);
		//echo $per_ContractPayment;
		if($per_ContractPayment==0){
			$getPercent  =0;
		}else{
			$getPercent = ($CollectionPaymentSum / $per_ContractPayment)*100;
		}
		/*-----------------------------*/
		$getPercent = sprintf("%2.1f" ,$getPercent);
		/*-----------------------------*/
		$this->smarty->assign('ProjectNickname',$ProjectNickname);//프로젝트 닉네임 
		$this->smarty->assign('ContractPayment',$ch_per_ContractPayment);//용역 지분 계약금액 
		$this->smarty->assign('CollectionPaymentSum',$ch_CollectionPaymentSum);//수금액합계
		$this->smarty->assign('getPercent',$getPercent);//
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('cnt',$cnt);
		$this->smarty->assign('date_today',$date_today);
		$this->smarty->assign('content_id',$content_id);
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_pmResister/listPage03.tpl");
		/*----------------------------------------*/

	}  //PageList03() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function PageList04()// [페이지이동] 관리대장 -> 외주비 지급 총괄내역서
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		global $date_today;
		/*---------------------------------------*/
		global $db;
		/*---------------------------------------*/
		//$content_id	= $_GET['content_id'];
		$content_id = ($_GET['content_id']==""?$_POST['content_id']:$_GET['content_id']);// 프로젝트코드
//echo "01::".$content_id."<br>"; 
		/*---------------------------------------*/

/*select * from outsideordercontract_tbl where ProjectCode='H14-고속-04' order by SuperviseGroup DESC,ContractAmount DESC*/

		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT														";
		$sql=$sql."	 OOC.ProjectCode       as ooc_ProjectCode					";//
		$sql=$sql."	,OOC.Company           as ooc_Company						";//
		$sql=$sql."	,OOC.SuperviseGroup    as ooc_SuperviseGroup				";//관리부서(한글명)
		$sql=$sql."	,OOC.Part              as ooc_Part							";//
		$sql=$sql."	,OOC.DetailPart        as ooc_DetailPart					";//
		$sql=$sql."	,OOC.ForecastAmount    as ooc_ForecastAmount				";//
		$sql=$sql."	,OOC.ContractAmount    as ooc_ContractAmount				";//외주계약금액 VAT포함
		$sql=$sql."	,OOC.ContractStart     as ooc_ContractStart					";//
		$sql=$sql."	,OOC.ContractEnd       as ooc_ContractEnd					";//
		$sql=$sql."	,OOC.TaxFree           as ooc_TaxFree						";//// 1:비과세, 그외:과세 (비과세->원금액 ,과세-> 지급액/1.1 한 금액 표시 수정)
		$sql=$sql."	,OOC.Note              as ooc_Note							";//
		$sql=$sql."	,OOC.No                as ooc_No							";//
		$sql=$sql."	,OOC.Updatedate        as ooc_Updatedate					";//
		$sql=$sql."	,OOC.UpdateUser        as ooc_UpdateUser					";//
		$sql=$sql."	FROM														";
		$sql=$sql."	outsideordercontract_tbl OOC								";
		$sql=$sql."	WHERE														";
		$sql=$sql."	OOC.ProjectCode ='".$content_id."'							";
		$sql=$sql."	AND OOC.ContractAmount >'0'							";
		$sql=$sql."	ORDER BY OOC.SuperviseGroup DESC ,OOC.ContractAmount DESC	";
		/*---------------------------------------*/
		/////////////////
//echo "01::".$sql."<br>"; 


		/////////////////
		/*-----------------------------*/
		$re = mysql_query($sql,$db);
		/*-----------------------------*/
		$re_num = mysql_num_rows($re);

		$cnt=0;

$sum_ooc_DisbursePaymentSum = 0;
$sum_ooc_ContractAmount = 0;
		while($re_row = mysql_fetch_array($re)) {

			//$re_row[ooc_TaxFree]
			
				$re_row[ooc_DetailPart_short] = utf8_strcut($re_row[ooc_DetailPart],7,'..');
				$re_row[ooc_Company_short] = utf8_strcut($re_row[ooc_Company],8,'..');

			$ooc_ContractAmount = $re_row[ooc_ContractAmount];//외주계약금액
			if( $re_row[ooc_TaxFree]==1){ //비과세
				  ///////////////////////////////
			}else{ //과세
				$re_row[ooc_ContractAmount] =number_format(round($ooc_ContractAmount/1.1)); //외주계약금액(VAT제외)
				$ooc_ContractAmount = $ooc_ContractAmount/1.1;
			}//if End

		/*-----------------------------*/
		//외주지급액합계
		$sql02 = "select sum(DisbursePayment) DisbursePaymentSum from outside_pay_disbursement_tbl where ProjectCode = '".$content_id."' and Company = '".$re_row[ooc_Company]."' and Part = '".$re_row[ooc_Part]."' and DetailPart = '".$re_row[ooc_DetailPart]."' and DisburseDate > '1950-01-01' ";
		$result02 = mysql_query($sql02,$db);

		$ooc_DisbursePaymentSum = mysql_result($result02,0,"DisbursePaymentSum"); 

			if( $re_row[ooc_TaxFree]==1){ //비과세
				  ///////////////////////////////
			}else{ //과세
				$re_row[ooc_DisbursePaymentSum] =number_format(round($ooc_DisbursePaymentSum/1.1)); //외주지급액합계(VAT제외)
				$ooc_DisbursePaymentSum         = $ooc_DisbursePaymentSum/1.1;
			}//if End

	if($ooc_ContractAmount!=0){
		$ooc_getPercent = ($ooc_DisbursePaymentSum / $ooc_ContractAmount)*100;
		$re_row[ooc_getPercent] = sprintf("%.0f",$ooc_getPercent);
	}else{
		$re_row[ooc_getPercent] = 0;
	}

		//$ooc_getPercent=sprintf("%.2f",$ooc_getPercent);sprintf("%02d",$ooc_getPercent);

		//$ch_CollectionPaymentSum_delTax  = number_format($DisbursePaymentSum);

		//최근수금일
		$sql05 = "select max(DisburseDate) maxDate from outside_pay_disbursement_tbl where ProjectCode = '".$content_id."' and Company = '".$re_row[ooc_Company]."' and Part = '".$re_row[ooc_Part]."' and DetailPart = '".$re_row[ooc_DetailPart]."' and DisburseDate > '1950-01-01' ";
		$result05 = mysql_query($sql05,$db);
		$ooc_maxDate = mysql_result($result05,0,"maxDate"); //최근수금일자
		$re_row[ooc_maxDate] = $ooc_maxDate;

$sum_ooc_DisbursePaymentSum += $ooc_DisbursePaymentSum;
$sum_ooc_ContractAmount += $ooc_ContractAmount;

			array_push($query_data,$re_row);
			$cnt++;
		} //while End
		/*---------------------------------------*/
		$cnt = 27-$cnt;

$sum_ooc_DisbursePaymentSum = $sum_ooc_DisbursePaymentSum;
$sum_ooc_ContractAmount = $sum_ooc_ContractAmount;

	if($sum_ooc_ContractAmount!=0){
		$sum_ooc_getPercent = ($sum_ooc_DisbursePaymentSum / $sum_ooc_ContractAmount)*100;
		$sum_ooc_getPercent = sprintf("%.0f",$sum_ooc_getPercent);
	}else{
		$sum_ooc_getPercent = 0;
	}

		/*-----------------------------*/
		$sum_ooc_DisbursePaymentSum =number_format(round($sum_ooc_DisbursePaymentSum)); //
		$sum_ooc_ContractAmount =number_format(round($sum_ooc_ContractAmount)); //

		$this->smarty->assign('sum_ooc_DisbursePaymentSum',$sum_ooc_DisbursePaymentSum);//
		$this->smarty->assign('sum_ooc_ContractAmount',$sum_ooc_ContractAmount);//
		$this->smarty->assign('sum_ooc_getPercent',$sum_ooc_getPercent);//
		/*-----------------------------*/

		/*-----------------------------*/
		//프로젝트 닉네임  
		$ProjectNickname = projectToColumn($content_id,"ProjectNickname");
		//발주처 닉네임  
		$OrderNickname   = projectToColumn($content_id,"OrderNickname");
		//시작일자  
		$ContractStart   = projectToColumn($content_id,"ContractStart");
		//종료일자  
		$ContractEnd   = projectToColumn($content_id,"ContractEnd");
		//실지분률
		$ActualityRatio   = projectToColumn($content_id,"ActualityRatio");

		/*-----------------------------*/
		//용역 총계약금액 
		$ContractPayment = projectToColumn($content_id,"ContractPayment");//VAT포함
		$ContractPayment_delTax = $ContractPayment/1.1; //VAT제외

		$sum = $ContractPayment_delTax * $ActualityRatio / 100;

		$ch_ContractPayment_delTax = number_format($sum);
		/*-----------------------------*/
		//수금액합계
		$sql03 = "select sum(CollectionPayment) CollectionPaymentSum from collectionpayment_tbl where ProjectCode='".$content_id."' and (CollectionDate > '1980-01-01') ";
//echo  $sql03;
		$result02 = mysql_query($sql03,$db);
		$CollectionPaymentSum = mysql_result($result02,0,"CollectionPaymentSum"); //VAT포함
		$CollectionPaymentSum_delTax = $CollectionPaymentSum/1.1; //VAT제외

		$ch_CollectionPaymentSum_delTax  = number_format($CollectionPaymentSum_delTax);


	if($ContractPayment!=0){
		$getPercent = ($CollectionPaymentSum / $ContractPayment)*100;
	}else{
		$getPercent = 0;
	}
	$getPercent = sprintf("%2.0f" ,$getPercent);

		/*-----------------------------*/
		//최근수금일
		$sql03 = "select max(CollectionDate) maxDate from collectionpayment_tbl where ProjectCode='".$content_id."' and CollectionDate > '1980-01-01' and CollectionDate<> '0000-00-00' ";
		$result03 = mysql_query($sql03,$db);
		$maxDate = mysql_result($result03,0,"maxDate"); //최근수금일자

		/*-----------------------------*/
		$this->smarty->assign('ProjectNickname',$ProjectNickname);//프로젝트 닉네임 
		$this->smarty->assign('OrderNickname',$OrderNickname);//발주처 닉네임
		$this->smarty->assign('ContractStart',$ContractStart);//시작일자
		$this->smarty->assign('ContractEnd',$ContractEnd);//종료일자
		$this->smarty->assign('ActualityRatio',$ActualityRatio);//실지분율

		$this->smarty->assign('CollectionPaymentSum',$ch_CollectionPaymentSum_delTax);//수금액합계
		$this->smarty->assign('ContractPayment',$ch_ContractPayment_delTax);//용역 총계약금액 


	if($ch_ContractPayment_delTax!=0){
		$aa_getPercent = ($ch_CollectionPaymentSum_delTax / $ch_ContractPayment_delTax)*100;
	}else{
		$aa_getPercent = 0;
	}
	$aa_getPercent = sprintf("%2.0f" ,$aa_getPercent);
	$this->smarty->assign('aa_getPercent',$aa_getPercent);//

		


		$this->smarty->assign('getPercent',$getPercent);//

		$this->smarty->assign('maxDate',$maxDate);//최근수금일자
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('cnt',$cnt);
		$this->smarty->assign('date_today',$date_today);
		$this->smarty->assign('content_id',$content_id);
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_pmResister/listPage04.tpl");
		/*----------------------------------------*/

	}  //PageList04() End
	/* ***************************************************************************************** */



	/* ***************************************************************************************** */
	function PageList05()// // 관리대장 -> 페이지이동 : 수행상태
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		global $date_today;
		/*---------------------------------------*/
		global $db;
		/*---------------------------------------*/
		$content_id	= $_GET['content_id'];
		//echo "01::".$content_id."<br>"; 
		/*---------------------------------------*/
		
		$WorkStatus = projectToColumn($content_id,"WorkStatus");//프로젝트 수행상태(한글값)
		$ProjectNickname = projectToColumn($content_id,"ProjectNickname");//프로젝트 닉네임 
		$Name = projectToColumn($content_id,"Name");//사내 담당자사원번호 
		if(strlen($Name)>0){
			$p_memberCode = $Name;					//사내 담당자사원번호 
			$p_memberName = MemberNo2Name($Name);	//사내 담당자이름
		}else{
			$p_memberCode = "";
			$p_memberName = "";
		}
		/*-----------------------------*/
		$this->smarty->assign('WorkStatus',$WorkStatus);//프로젝트 수행상태(한글값)
		$this->smarty->assign('ProjectNickname',$ProjectNickname);//사내 담당자사원번호 
		$this->smarty->assign('p_memberCode',$p_memberCode);//사내 담당자사원번호 
		$this->smarty->assign('p_memberName',$p_memberName);//사내 담당자이름
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('cnt',$cnt);
		$this->smarty->assign('date_today',$date_today);
		$this->smarty->assign('content_id',$content_id);//프로젝트 코드
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_pmResister/listPage05.tpl");
		/*----------------------------------------*/

	}  //PageList05() End
	/* ***************************************************************************************** */




	/* ***************************************************************************************** */
	function insertDB05()	//관리대장 : 수행상태 변경DB
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		global $date_today4; //년월일 시분초
		global $db;
		/*-----------------------------*/
		$p_projectCode	= $_GET['p_projectCode'];	//프로젝트코드
		$WorkStatus	    = $_GET['WorkStatus'];		//컨텐츠PK
		$p_memberCode   = $_GET['p_memberCode'];	//사내담당자

		/***********************************************************************************/
		$update_query =			      " UPDATE							";
		$update_query = $update_query." project_tbl SET					";
		$update_query = $update_query."   WorkStatus='".$WorkStatus."'  ";
		$update_query = $update_query."  ,Name='".$p_memberCode."'      ";
		$update_query = $update_query."  ,UpdateDate='".$date_today4."' ";
		$update_query = $update_query."  ,UpdateUser='".$MemberNo."'    ";
		$update_query = $update_query." WHERE							";
		$update_query = $update_query." ProjectCode='".$p_projectCode."'";
		/***********************************************************************************/

//echo $update_query."<br>";
		/*-----------------------------*/
$result = mysql_query($update_query);
		if($result){
			echo "1";	//변경 성공
		}else{
			echo "2";	//변경 실패
		}
		/*-----------------------------*/
	}  //insertDB05() End




	/* ***************************************************************************************** */
	function PageList06()// [페이지이동] 관리대장 -> 관리대장 : 상세보기
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		global $date_today;
		/*---------------------------------------*/
		global $db;
		/*---------------------------------------*/
		//$content_id	= $_GET['content_id'];
		$content_id = ($_GET['content_id']==""?$_POST['content_id']:$_GET['content_id']);// 프로젝트코드
//echo "01::".$content_id."<br>"; 
		/*---------------------------------------*/

	//1.일반현황 Start ///////////////////////////////////////////////////////////////////////////
		$query_data = array(); 
		/*-----------------------------------------------*/
		$sql=     "	SELECT													";
		$sql=$sql."  P.ProjectCode				as p_ProjectCode			";//용역_프로젝트코드
		$sql=$sql."	,P.oldProjectCode			as p_oldProjectCode			";//용역_프로젝트 네임
		$sql=$sql."	,P.ProjectName				as p_ProjectName			";//용역_프로젝트 네임
		$sql=$sql."	,P.ProjectNickname			as p_ProjectNickname		";//용역_프로젝트 닉네임
		$sql=$sql."	,P.OrderCompany				as p_OrderCompany			";//용역_발주처 네임
		$sql=$sql."	,P.OrderNickname			as p_OrderNickname			";//용역_발주처 닉네임
		$sql=$sql."	,P.ContractPayment			as p_ContractPayment		";//용역_계약금액
		$sql=$sql."	,P.WorkStatus				as p_WorkStatus				";//용역_진행상태
		$sql=$sql."	,P.Note						as p_Note					";
		$sql=$sql."	,P.ActualityRatio    		as p_ActualityRatio			";//용역_
		$sql=$sql."	,P.ContractStart    		as p_ContractStart			";//용역_시작
		$sql=$sql."	,P.ContractEnd    			as p_ContractEnd			";//용역_종료
		$sql=$sql."	,P.ContractPeriod    		as p_ContractPeriod			";//용역_절대공기
		$sql=$sql."	,P.ContractRatio			as p_ContractRatio			";
		$sql=$sql."	,P.MainGroup				as p_MainGroup				";
		$sql=$sql."	,P.Name              		as p_Name					";//용역_사내담당자:사원번호
		$sql=$sql."	,P.ContractSeal				as p_ContractSeal			";
		$sql=$sql."	,P.CommonCompany			as p_CommonCompany			";
		$sql=$sql."	FROM													";
		$sql=$sql."		project_tbl	P										";
		$sql=$sql."	WHERE													";
		$sql=$sql."	P.ProjectCode = '".$content_id."'						";
//////////////////////////
//echo "01::".$sql."<br>";
//////////////////////////
		/*-----------------------------------------------*/
		$result = mysql_query($sql,$db);
		/*-----------------------------------------------*/
		$count = mysql_num_rows($result);
		/*-----------------------------------------------*/
		$p_oldProjectCode	= "";
		$p_ProjectName 		= "";
		$p_WorkStatus		= "";
		$p_OrderCompany		= "";
		$p_OrderNickname	= "";
		$p_ContractPayment	= "";
		$p_ContractStart	= "";
		$p_ContractEnd		= "";
		$p_ContractPeriod	= "";
		// 업체구성
		$p_ContractRatio	= "";
		$p_MainGroup		= "";
		$p_Name				= "";
		$p_ContractSeal		= "";
		$p_CommonCompany	= "";

		$p_Note				= "";
		
		/*-----------------------------------------------*/
		if($count>0){//결과0
			//---------------------------------------------------------------------*/
			// 기존프로젝트 코드 
			$p_oldProjectCode = mysql_result($result,0,"p_oldProjectCode");  
//echo "01::".$p_oldProjectCode."<br>"; 
			//---------------------------------------------------------------------*/
			// 프로젝트 네임(한글값) 
			$p_ProjectName = mysql_result($result,0,"p_ProjectName");  
//echo "01::".$p_ProjectName."<br>"; 
			//---------------------------------------------------------------------*/
			// 진행상태
			$p_WorkStatus = mysql_result($result,0,"p_WorkStatus"); 
//echo "01::".$p_WorkStatus."<br>"; 
			//---------------------------------------------------------------------*/
			// 발주처 (한글명)
			$p_OrderCompany = mysql_result($result,0,"p_OrderCompany"); 
//echo "01::".$p_OrderCompany."<br>"; 
			//---------------------------------------------------------------------*/
			// 발주처 닉네임 (한글명)
			$p_OrderNickname = mysql_result($result,0,"p_OrderNickname"); 
//echo "01::".$p_OrderNickname."<br>"; 
			//---------------------------------------------------------------------*/
			// 계약금액 
			$p_ContractPayment = mysql_result($result,0,"p_ContractPayment"); 
//echo "01::".$p_ContractPayment."<br>"; 
			/*---------------------------------------------------------------------*/
			// 프로젝트 시작일자 
			$p_ContractStart = mysql_result($result,0,"p_ContractStart"); 
//echo "01::".$p_ContractStart."<br>"; 
			/*---------------------------------------------------------------------*/
			// 프로젝트 종료일자 
			$p_ContractEnd = mysql_result($result,0,"p_ContractEnd"); 
//echo "01::".$p_ContractEnd."<br>"; 
			/*---------------------------------------------------------------------*/
			// 프로젝트 절대공기
			$p_ContractPeriod = mysql_result($result,0,"p_ContractPeriod"); 
//echo "01::".$p_ContractPeriod."<br>"; 
			//---------------------------------------------------------------------*/

		// 업체구성 출력시 사용됨
			/*---------------------------------------------------------------------*/
			// 
			$p_ContractRatio = (double)mysql_result($result,0,"p_ContractRatio"); 
//echo "01::".$p_ContractRatio."<br>"; 
			/*---------------------------------------------------------------------*/
			// 그룹코드를 그룹명으로 변환
			$p_MainGroup = Code2Name(mysql_result($result,0,"p_MainGroup"),"GroupCode", 1);
//echo "01::".$p_MainGroup."<br>"; 
			/*---------------------------------------------------------------------*/
			//사내담당 사원번호를 이름으로 변환
			$p_Name = MemberNo2Name(mysql_result($result,0,"p_Name"));
//echo "01::".$p_Name."<br>"; 
			/*---------------------------------------------------------------------*/
			// 
			$p_ContractSeal = mysql_result($result,0,"p_ContractSeal"); 
//echo "01::".$p_ContractSeal."<br>"; 
			/*---------------------------------------------------------------------*/
			// 
			$p_CommonCompany = mysql_result($result,0,"p_CommonCompany"); 
//echo "p_CommonCompany::".$p_CommonCompany."<br>"; 

			$p_Note = mysql_result($result,0,"p_Note"); 
//echo "p_Note::".$p_Note."<br>"; 

		}else{
				echo "프로젝트 코드(".$content_id.")가 잘못되었거나 없는 프로젝트입니다!";
				exit;
		}//if End

		$query_data = array(); 
		/*-----------------------------------------------*/
		$sql11=     "	SELECT													";
		$sql11=$sql11."  CPT.id           	as cpt_id							";
		$sql11=$sql11."	,CPT.ProjectCode  	as cpt_ProjectCode					";
		$sql11=$sql11."	,CPT.Kind         	as cpt_Kind							";
		$sql11=$sql11."	,CPT.Company      	as cpt_Company						";
		$sql11=$sql11."	,CPT.RelationGroup	as cpt_RelationGroup				";
		$sql11=$sql11."	,CPT.Name         	as cpt_Name							";
		$sql11=$sql11."	,CPT.Phone        	as cpt_Phone						";
		$sql11=$sql11."	,CPT.Fax          	as cpt_Fax							";
		$sql11=$sql11."	,CPT.Seal         	as cpt_Seal							";
		$sql11=$sql11."	,CPT.Note         	as cpt_Note							";
		$sql11=$sql11." ,CPT.UpdateDate   	as cpt_UpdateDate					";
		$sql11=$sql11."	,CPT.UpdateUser   	as cpt_UpdateUser					";
		$sql11=$sql11."	FROM													";
		$sql11=$sql11."		contact_point_tbl	CPT								";
		$sql11=$sql11."	WHERE													";
		$sql11=$sql11."	CPT.ProjectCode like '%".$content_id."%'				";
		$sql11=$sql11." AND														";
		$sql11=$sql11." CPT.Kind = 1 and CPT.Company ='".$p_OrderNickname."'	";
		//echo "01::".$sql11."<br>"; 
		/*-----------------------------------------------*/
		$result11 = mysql_query($sql11,$db);
		/*-----------------------------------------------*/
		$count11 = mysql_num_rows($result11);
		/*-----------------------------------------------*/
		$cpt_RelationGroup	= "";//발주부서 (한글명)
		$cpt_Name			= "";//담당자 (한글명)
		$cpt_Phone			= "";//Tel
		$cpt_Fax			= "";//Fax
		/*-----------------------------------------------*/
		if($count11>0){//결과0
			//echo "01::값있음::".$count11."<br>"; 

			/* 발주부서 (한글명) *********************************************************** */
			$cpt_RelationGroup = mysql_result($result11,0,"cpt_RelationGroup");  
			echo "01::".$cpt_RelationGroup."<br>"; 
			/*------------------------------------------------------------------------------*/
			/* 담당자 (한글명) *********************************************************** */
			$cpt_Name = mysql_result($result,0,"cpt_Name");
			echo "01::".$cpt_Name."<br>"; 
			/*------------------------------------------------------------------------------*/
			/* Tel *********************************************************** */
			$cpt_Phone = mysql_result($result,0,"cpt_Phone");
			echo "01::".$cpt_Phone."<br>"; 
			/*------------------------------------------------------------------------------*/
			/* Fax *********************************************************** */
			$cpt_Fax = mysql_result($result,0,"cpt_Fax");
			echo "01::".$cpt_Fax."<br>"; 
			/*------------------------------------------------------------------------------*/
		}else{ 
			//echo "01::값없음::".$count11."<br>";
		}//if End



	//일반현황 End ///////////////////////////////////////////////////////////////////////////


	//2.계약내역 Start ///////////////////////////////////////////////////////////////////////////

		/*------------------------------------------------------------------------------*/
		$query_data01 = array(); 
		//$azSQL = "select * from contract_change_tbl where ProjectCode = '".$content_id."' order by no";
		$sql01=        " SELECT											";//
		$sql01= $sql01."  CCT.ProjectCode    	as cct_ProjectCode		";//
		$sql01= $sql01." ,CCT.ContractStep   	as cct_ContractStep		";//
		$sql01= $sql01." ,CCT.ContractPayment	as cct_ContractPayment	";//
		$sql01= $sql01." ,CCT.Payment        	as cct_Payment			";//
		$sql01= $sql01." ,CCT.Vat            	as cct_Vat				";//
		$sql01= $sql01." ,CCT.ContractStart  	as cct_ContractStart	";//
		$sql01= $sql01." ,CCT.ContractEnd    	as cct_ContractEnd		";//
		$sql01= $sql01." ,CCT.ContractPeriod 	as cct_ContractPeriod	";//
		$sql01= $sql01." ,CCT.no             	as cct_no				";//
		$sql01= $sql01." ,CCT.UpdateDate     	as cct_UpdateDate		";//
		$sql01= $sql01." ,CCT.UpdateUser     	as cct_UpdateUser		";//
		$sql01= $sql01." FROM											";//
		$sql01= $sql01." contract_change_tbl CCT						";//
		$sql01= $sql01." where											";//
		$sql01= $sql01." CCT.ProjectCode = '".$content_id."'			";//
		$sql01= $sql01." ORDER BY CCT.no								";//
		/////////////////
//echo "01::".$sql01."<br>"; 
		/////////////////
			/*-----------------------------*/
			$re01 = mysql_query($sql01,$db);
			/*-----------------------------*/
			$count01 = mysql_num_rows($re01);
			$query_data01_cnt =9;
			/*-----------------------------------------------*/
			if($count01>0){//결과0
			$query_data01_cnt = $query_data01_cnt-$count01;
				while($re_row01 = mysql_fetch_array($re01)) {

					$ch_cct_ContractPayment          = $re_row01[cct_ContractPayment];
					$re_row01[ch_cct_ContractPayment]  = number_format($ch_cct_ContractPayment); //세자리구분자
		
					array_push($query_data01,$re_row01);
				} //while End
			}else{
				//결과없음
			}
			/*----------------------------------------*/
	//계약내역 End ///////////////////////////////////////////////////////////////////////////


	//3.내역의 구성 Start ///////////////////////////////////////////////////////////////////////////
		//$azSQL = "select * from contract_detail_tbl where ProjectCode = '".$Key_PCode."' order by no";
		$query_data02 = array(); 
		$sql02=        " SELECT									";//
		$sql02= $sql02."  CDT.ProjectCode as cdt_ProjectCode 	";//
		$sql02= $sql02." ,CDT.Detail_item as cdt_Detail_item 	";//
		$sql02= $sql02." ,CDT.Payment     as cdt_Payment     	";//
		$sql02= $sql02." ,CDT.Note        as cdt_Note        	";//
		$sql02= $sql02." ,CDT.no          as cdt_no          	";//
		$sql02= $sql02." ,CDT.UpdateDate  as cdt_UpdateDate  	";//
		$sql02= $sql02." ,CDT.UpdateUser  as cdt_UpdateUser  	";//

		$sql02= $sql02." FROM									";//
		$sql02= $sql02." contract_detail_tbl CDT				";//
		$sql02= $sql02." where									";//
		$sql02= $sql02." CDT.ProjectCode = '".$content_id."'	";//
		$sql02= $sql02." ORDER BY CDT.no						";//

		/////////////////
//echo "02::".$sql02."<br>"; 
		/////////////////
			/*-----------------------------*/
			$re02 = mysql_query($sql02,$db);
			/*-----------------------------*/

			$count02 = mysql_num_rows($re02);
			$query_data02_cnt =9;
			/*-----------------------------------------------*/
			if($count02>0){//결과0
			$query_data02_cnt = $query_data02_cnt-$count02;
				while($re_row02 = mysql_fetch_array($re02)) {

					/*-----------------------------------------------*/
					$ch_cdt_Payment          = $re_row02[cdt_Payment];
					$re_row02[ch_cdt_Payment]  = number_format($ch_cdt_Payment); //세자리구분자
					/*-----------------------------------------------*/
					$cdt_Detail_item = $re_row02[cdt_Detail_item];
					/*-----------------------------------------------*/
//echo "01::".$cdt_Detail_item.":::".$ch_cdt_Payment."<br>"; 

					array_push($query_data02,$re_row02);
				} //while End
			}else{
				//결과없음
			}//if End

			/*----------------------------------------*/
			// 내역의 구성:합계(result_cdt_sum)
			//$query = "select sum(CollectionPayment) A from collectionpayment_tbl 
			//                 where ProjectCode = '$pjcode' and DemandDate <> '0000-00-00' and DemandDate <> ''";
			$sql03 = "select sum(CDT.Payment) cdt_sum from contract_detail_tbl CDT where CDT.ProjectCode = '".$content_id."' ";
			//echo $sql03."<br>";
			$result03 = mysql_query($sql03,$db);
			$count03 = mysql_num_rows($result03);
			$ch_result_cdt_sum = 0;
			/*-----------------------------------------------*/
			if($count03>0){//결과0
				$result_cdt_sum = mysql_result($result03,0,"cdt_sum");  
				$ch_result_cdt_sum = number_format($result_cdt_sum); //세자리구분자
//echo "01::".$ch_result_cdt_sum."<br>"; 

			}else{
				//결과없음
			}//if End

		/*------------------------------------------------------------------------------*/

	//3.내역의 구성 End ///////////////////////////////////////////////////////////////////////////



//4.업체구성 Start ///////////////////////////////////////////////////////////////////////////

$val01  = array("","","");	//$val01회사명
$val02	= array("","","");			//$val02지분	
$val03	= array("","","");			//$val03비율	
$val04	= array("","","");			//$val04부서	
$val05	= array("","","");			//$val05담당	
$val06	= array("","","");			//$val06전화	
$val07	= array("","","");			//$val07팩스	
$val08	= array("","","");			//$val08인감	

//회사명","지 분","비 율","부 서","담 당","전 화","팩 스","인 감");

	if($ProjectContractRatio == 0) {
		$ProjectContractRatio = 100;
	}
	$tmppayment = $ProjectContractPayment * $ProjectContractRatio / 100;
////////////////////////////////////
	if($p_ContractRatio == 0) {
		$p_ContractRatio = 100;
	}
	$tmppayment = $p_ContractPayment * $p_ContractRatio / 100; //용역계약금액*우리회사지분/100
	/*---------------------------------*/
	if($tmppayment > 0)	{ 
		$val02[0] = number_format($tmppayment); //$val02지분[0]
	}
	/*---------------------------------*/
	if($p_ContractRatio > 0) { 
		$val03[0] = number_format($p_ContractRatio,2)." %"; //$val03비율[0]
	}
	/*---------------------------------*/
	$val01[0] = "바론컨설턴트";			//$val01회사명[0]
	$val04[0] = $p_MainGroup ;		//$val04부서[0] =
	$val05[0] = $p_Name;			//$val05담당[0] =
	$val08[0] = $p_ContractSeal;	//$val08인감[0] =
	/*---------------------------------*/

			/*공동도급01----------------------------------------*/
			$val01[1]= $p_CommonCompany;	//$val01회사명[1] 
//echo "회사명==".$p_CommonCompany."<br>";
			$sql04 = "select * from contact_point_tbl where ProjectCode = '".$content_id."' and Kind=2";
			//echo $sql04."<br>";
			$result04 = mysql_query($sql04,$db);
			$count04 = mysql_num_rows($result04);
			/*-----------------------------------------------*/
			if($count04>0){//결과0
				$re04_RelationGroup = mysql_result($result04,0,"RelationGroup");//부서
				$re04_Name	= mysql_result($result04,0,"Name");		//담당
				$re04_Phone = mysql_result($result04,0,"Phone");	//Tel
				$re04_Fax	= mysql_result($result04,0,"Fax");		//Fax
				$re04_Seal	= mysql_result($result04,0,"Seal");		//인감
				/*-----------------------------------------------*/
				$val04[1]= $re04_RelationGroup;	//$val04부서[1]	
				$val05[1]= $re04_Name;			//$val05담당[1]	
				$val06[1]= $re04_Phone;			//$val06전화[1]	
				$val07[1]= $re04_Fax;			//$val07팩스[1]	
				$val08[1]= $re04_Seal;			//$val08인감[1]	
				/*-----------------------------------------------*/
			}else{
				//결과없음
			}//if End
			/*-----------------------------------------------*/
			//echo $p_ContractRatio."<br>";
			$Kind2Ratio = 100 - $p_ContractRatio;
			if($Kind2Ratio != 0) {
				$val02[1] = number_format($p_ContractPayment * $Kind2Ratio / 100);//$val02지분
				$val03[1] = number_format(round($Kind2Ratio, 2))." %";			  //$val03비율
				//echo "val02[1]=".$val02[1]."<br>";
				//echo "val03[1]=".$val03[1]."<br>";

			}//if End
			/*-----------------------------------------------*/

			/*공동도급02----------------------------------------*/
			$sql05 = "select * from contact_point_tbl where ProjectCode = '".$content_id."' and Kind=3";
//echo $sql05."<br>";
			$result05 = mysql_query($sql05,$db);
			$count05 = mysql_num_rows($result05);
			/*-----------------------------------------------*/
			if($count05>0){//결과0
				$re05_Company = mysql_result($result05,0,"Company");//회사명
//echo $re05_Company."<br>";
				$re05_RelationGroup = mysql_result($result05,0,"RelationGroup");//부서
				$re05_Name	= mysql_result($result05,0,"Name");		//담당
				$re05_Phone = mysql_result($result05,0,"Phone");	//Tel
				$re05_Fax	= mysql_result($result05,0,"Fax");		//Fax
				$re05_Seal	= mysql_result($result05,0,"Seal");		//인감
				/*-----------------------------------------------*/
				$val01[2]= $re05_Company;        //$val01회사명[2] 
				$val04[2]= $re05_RelationGroup;	//$val04부서[2]	
				$val05[2]= $re05_Name;			//$val05담당[2]	
				$val06[2]= $re05_Phone;			//$val06전화[2]	
				$val07[2]= $re05_Fax;			//$val07팩스[2]	
				$val08[2]= $re05_Seal;			//$val08인감[2]	
				/*-----------------------------------------------*/
			}else{
				//결과없음
			}//if End
$this->smarty->assign('val01',$val01);//$val01회사명   
$this->smarty->assign('val02',$val02);//$val02지분	 
$this->smarty->assign('val03',$val03);//$val03비율	 
$this->smarty->assign('val04',$val04);//$val04부서	 
$this->smarty->assign('val05',$val05);//$val05담당	 
$this->smarty->assign('val06',$val06);//$val06전화	 
$this->smarty->assign('val07',$val07);//$val07팩스	 
$this->smarty->assign('val08',$val08);//$val08인감	 

//4.업체구성 End ///////////////////////////////////////////////////////////////////////////







//5.책임기술자 및 과업의 개요 ///////////////////////////////////////////////////////////////////////////
		/*책임기술자------------------------------------------------------------------------------*/
		$query_data06 = array(); 
		//$azSQL = "select * from worker_part_tbl where ProjectCode = '".$Key_PCode."'";
		$sql06=       "	SELECT									";
		$sql06=$sql06."  WPT.ProjectCode	as wpt_ProjectCode	";
		$sql06=$sql06."	,WPT.Business		as wpt_Business		";
		$sql06=$sql06."	,WPT.Part			as wpt_Part			";
		$sql06=$sql06."	,WPT.Name			as wpt_Name			";
		$sql06=$sql06."	,WPT.No				as wpt_No			";
		$sql06=$sql06."	,WPT.Memo			as wpt_Memo			";
		$sql06=$sql06."	FROM									";
		$sql06=$sql06."		worker_part_tbl	WPT					";
		$sql06=$sql06."	WHERE									";
		$sql06=$sql06."	WPT.ProjectCode like '%".$content_id."%'";
		/////////////////
//echo "01::".$sql06."<br>"; 
		/////////////////
			/*-----------------------------*/
			$re06 = mysql_query($sql06,$db);
			/*-----------------------------*/
			$count06 = mysql_num_rows($re06);
			$query_data06_cnt =8;
			/*-----------------------------------------------*/
			if($count06>0){//결과0
			$query_data06_cnt = $query_data06_cnt-$count06;
				while($re_row06 = mysql_fetch_array($re06)) {

					
					$wpt_Part = mb_substr($re_row06[wpt_Part],0,4,"UTF-8");

					if(strlen($wpt_Part)>6){
						$re_row06[wpt_Part_short] = $wpt_Part."..";
					}else{
						$re_row06[wpt_Part_short] = $wpt_Part;
					}

					//$ch_cct_ContractPayment          = $re_row06[cct_ContractPayment];
					//$re_row06[ch_cct_ContractPayment]  = number_format($ch_cct_ContractPayment); //세자리구분자
		
					array_push($query_data06,$re_row06);
				} //while End
			}else{
				//결과없음
			}
			/*----------------------------------------*/



		/*과업의 개요------------------------------------------------------------------------------*/
		$query_data07 = array(); 
		//$azSQL = "select * from worker_part_tbl where ProjectCode = '".$Key_PCode."'";
		$sql07=       "	SELECT	*								";
		$sql07=$sql07."	FROM									";
		$sql07=$sql07."		Project_Summary_new_tbl	PSNT		";
		$sql07=$sql07."	WHERE									";
		$sql07=$sql07."	PSNT.ProjectCode = '".$content_id."'";
		/////////////////
//echo "01::".$sql07."<br>"; 
		/////////////////
			/*-----------------------------*/
			$re07 = mysql_query($sql07,$db);
			/*-----------------------------*/
			$count07 = mysql_num_rows($re07);
			$query_data07_cnt =8;
			/*-----------------------------------------------*/
			if($count07>0){//결과0
				while($re_row07 = mysql_fetch_array($re07)) {
					/*-----------------------------*/
?>
<?php
					if ($re_row07[Length] <>""){
						$psnt_val01[$ii]  =array("연장",$re_row07[Length],"km");
						$ii++;
					} //if End
					/*****************************************************/

					if ($re_row07[Width] <>""){
						$psnt_val01[$ii]  =array("폭원",$re_row07[Width],"m");
						$ii++;
					} //if End
					if ($re_row07[Speed] <>""){
						$psnt_val01[$ii]  =array("설계속도",$re_row07[Speed],"km/hr");
						$ii++;
						
					} //if End
					if ($re_row07[Bridge] <>""){
						$psnt_val01[$ii]  =array("교량",$re_row07[Bridge],"m");

						$ii++;
						
					} //if End
					if ($re_row07[Tunnel] <>""){
						$psnt_val01[$ii]  =array("터널",$re_row07[Tunnel],"m");
						$ii++;
						
					} //if End
					if ($re_row07[BusinessArea] <>""){
						$psnt_val01[$ii]  =array("사업면적",$re_row07[BusinessArea],"m2");
						$ii++;
						
					} //if End
					if ($re_row07[LandfillArea] <>""){
						$psnt_val01[$ii]  =array("매립면적",$re_row07[LandfillArea],"m2");
						$ii++;
						
					} //if End
					if ($re_row07[FacilitieArea] <>""){
						$psnt_val01[$ii]  =array("시설면적",$re_row07[FacilitieArea],"m2");
						$ii++;
						
					} //if End
					if ($re_row07[FacilitieCap] <>""){
						$psnt_val01[$ii]  =array("시설용량",$re_row07[FacilitieCap],"ton/일");
						$ii++;
						
					} //if End
					if ($re_row07[Road] <>""){
						$psnt_val01[$ii]  =array("차로수",$re_row07[Road],"차로");
						$ii++;
						
					} //if End
					if ($re_row07[Underpass] <>""){
						$psnt_val01[$ii]  =array("지하차도",$re_row07[Length],"m");
						$ii++;
						
					} //if End
					if ($re_row07[EnvPayment] <>""){
						$psnt_val01[$ii]  =array("환경영향평가비",$re_row07[EnvPayment],"천원");
						$ii++;
						
					} //if End
					if ($re_row07[TrafficPayment] <>""){
						$psnt_val01[$ii]  =array("교통영향평가비",$re_row07[TrafficPayment],"천원");
						$ii++;
						
					} //if End
?>
<?php
					/*-----------------------------*/
					
					/*-----------------------------*/
				} //while End
			}else{
				//결과없음
			}
			/*----------------------------------------*/

$this->smarty->assign('query_data07',$psnt_val01);
$query_data07_cnt = $query_data07_cnt-$ii;
$this->smarty->assign('query_data07_cnt',$query_data07_cnt);





//5.책임기술자 및 과업의 개요 ///////////////////////////////////////////////////////////////////////////






//6.계약변경사항 ///////////////////////////////////////////////////////////////////////////
		$query_data08 = array(); 
		//$azSQL = "select * from change_list_tbl where ProjectCode = '".$Key_PCode."' order by ChangeDate";
		$sql08=       "	SELECT									";
		$sql08=$sql08."  CLT.ProjectCode 	as clt_ProjectCode	";
		$sql08=$sql08."	,CLT.ChangeDate  	as clt_ChangeDate	";
		$sql08=$sql08."	,CLT.ChangeItem  	as clt_ChangeItem	";
		$sql08=$sql08."	,CLT.ChangeBefore	as clt_ChangeBefore	";
		$sql08=$sql08."	,CLT.ChangeAfter 	as clt_ChangeAfter	";
		$sql08=$sql08."	,CLT.Note        	as clt_Note			";
		$sql08=$sql08."	,CLT.No          	as clt_No			";
		$sql08=$sql08."	,CLT.UpdateDate  	as clt_UpdateDate	";
		$sql08=$sql08."	,CLT.UpdateUser  	as clt_UpdateUser	";
		$sql08=$sql08."	FROM									";
		$sql08=$sql08."		change_list_tbl	CLT					";
		$sql08=$sql08."	WHERE									";
		$sql08=$sql08."	CLT.ProjectCode = '".$content_id."'";
		$sql08=$sql08."	order by CLT.ChangeDate,CLT.No 			";
		$sql08= $sql08." limit 0,9								";//
		/////////////////
//echo "01::".$sql08."<br>"; 
		/////////////////
			/*-----------------------------*/
			$re08 = mysql_query($sql08,$db);
			/*-----------------------------*/
			$count08 = mysql_num_rows($re08);
			$query_data08_cnt =9;
			/*-----------------------------------------------*/
			if($count08>0){//결과0
			$query_data08_cnt = $query_data08_cnt-$count08;
				while($re_row08 = mysql_fetch_array($re08)) {

					//$ch_cct_ContractPayment          = $re_row08[cct_ContractPayment];
					//$re_row08[ch_cct_ContractPayment]  = number_format($ch_cct_ContractPayment); //세자리구분자
		
					array_push($query_data08,$re_row08);
				} //while End
			}else{
				//결과없음
			}
			/*----------------------------------------*/



//echo "01::".$query_data08_cnt."<br>"; 
		$this->smarty->assign('query_data08',$query_data08);//6.계약변경사항
		$this->smarty->assign('query_data08_cnt',$query_data08_cnt);//6.계약변경사항 row 카운트
//6.계약변경사항 ///////////////////////////////////////////////////////////////////////////






//7.특기사항 ///////////////////////////////////////////////////////////////////////////
//7.특기사항 ///////////////////////////////////////////////////////////////////////////





// 기존프로젝트 코드 
$this->smarty->assign('p_oldProjectCode',$p_oldProjectCode);
//---------------------------------------------------------------------*/
// 프로젝트 네임(한글값) 
$this->smarty->assign('p_ProjectName',$p_ProjectName);
//---------------------------------------------------------------------*/
// 진행상태
$this->smarty->assign('p_WorkStatus',$p_WorkStatus);
//---------------------------------------------------------------------*/
// 발주처 (한글명)
$this->smarty->assign('p_OrderCompany',$p_OrderCompany);
//---------------------------------------------------------------------*/
// 발주처 닉네임 (한글명)
$this->smarty->assign('p_OrderNickname',$p_OrderNickname);
//---------------------------------------------------------------------*/
// 계약금액 
$ch_p_ContractPayment = number_format($p_ContractPayment);
$this->smarty->assign('p_ContractPayment',$ch_p_ContractPayment);
/*---------------------------------------------------------------------*/
// 프로젝트 시작일자 
$this->smarty->assign('p_ContractStart',$p_ContractStart);
/*---------------------------------------------------------------------*/
// 프로젝트 종료일자 
$this->smarty->assign('p_ContractEnd',$p_ContractEnd);
/*---------------------------------------------------------------------*/
// 프로젝트 절대공기
$this->smarty->assign('p_ContractPeriod',$p_ContractPeriod);
//---------------------------------------------------------------------*/

//$p_Note_cnt = strLen($p_Note);
//echo $p_Note_cnt."<br>";
//$p_Note=mb_substr( $p_Note,0,100);
//$p_Note_cnt = strLen($p_Note);
//echo $p_Note_cnt;
// 프로젝트 특이사항
$this->smarty->assign('p_Note',$p_Note);
//---------------------------------------------------------------------*/




// 업체구성 출력시 사용됨
/*---------------------------------------------------------------------*/
$this->smarty->assign('p_ContractRatio',$p_ContractRatio);
/*---------------------------------------------------------------------*/
// 그룹코드를 그룹명으로 변환
$this->smarty->assign('p_MainGroup',$p_MainGroup);
/*---------------------------------------------------------------------*/
//사내담당 사원번호를 이름으로 변환
$this->smarty->assign('p_Name',$p_Name);
/*---------------------------------------------------------------------*/
$this->smarty->assign('p_ContractSeal',$p_ContractSeal);
/*---------------------------------------------------------------------*/
$this->smarty->assign('p_CommonCompany',$p_CommonCompany);
/*---------------------------------------------------------------------*/


/*---------------------------------------------------------------------*/
//발주부서 (한글명)
$this->smarty->assign('cpt_RelationGroup',$cpt_RelationGroup);
/*---------------------------------------------------------------------*/
//담당자 (한글명)
$this->smarty->assign('cpt_Name',$cpt_Name);
/*---------------------------------------------------------------------*/
//Tel
$this->smarty->assign('cpt_Phone',$cpt_Phone);
/*---------------------------------------------------------------------*/
//Fax
$this->smarty->assign('cpt_Fax',$cpt_Fax);
/*---------------------------------------------------------------------*/



		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('cnt',$cnt);
		$this->smarty->assign('date_today',$date_today);
		$this->smarty->assign('content_id',$content_id);
		/*----------------------------------------*/

		
		$this->smarty->assign('query_data01',$query_data01);//2.계약내역
		$this->smarty->assign('query_data01_cnt',$query_data01_cnt);//2.계약내역 row 카운트

		$this->smarty->assign('query_data02',$query_data02);//3.내역의 구성
		$this->smarty->assign('query_data02_cnt',$query_data02_cnt);//3.내역의 구성 row 카운트
		$this->smarty->assign('ch_result_cdt_sum',$ch_result_cdt_sum);//3.내역의 구성 :내역의 구성:합계(result_cdt_sum)

		$this->smarty->assign('query_data06',$query_data06);//5.책임기술자
		$this->smarty->assign('query_data06_cnt',$query_data06_cnt);//5.책임기술자 row 카운트


		$this->smarty->assign('query_data04',$query_data04);//5.과업의 개요
		$this->smarty->assign('query_data04_cnt',$query_data04_cnt);//5.과업의 개요 row 카운트

		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_pmResister/listPage06.tpl");
		/*----------------------------------------*/

	}  //PageList06() End
	/* ***************************************************************************************** */



	/* ***************************************************************************************** */
	function PageList07()	//관리대장 :  수금계획집계표
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		global $db;
		/*--------------------*/
		global $GroupCode;
		$GroupCode = (int)$GroupCode;
		global $GroupName;
		/*--------------------*/
		global $date_today;
		global $nowYear; //당해년도 : yyyy
		$date_todayYYYYMM = substr($date_today,0,7);//YYYY-MM

		$date_todayMM_int = (int)substr($date_today,5,2);//YYYY-MM

		/*---------------------------------------*/
		//$content_id	= $_GET['content_id'];
		//$content_id = ($_GET['content_id']==""?$_POST['content_id']:$_GET['content_id']);// 프로젝트코드
//echo "01::".$content_id."<br>"; 
		/*---------------------------------------*/

		/*------------------------------------------------------------------------------*/
		$query_data01 = array(); 
		/*---------------------------------------*/
		$sql01=$sql01."	SELECT															";	
		$sql01=$sql01."		  P.projectCode		as  p_projectCode						";	//프로젝트코드	
		$sql01=$sql01."		, P.MainGroup		as  p_MainGroup							";	//주관부서		
		$sql01=$sql01."		, P.OrderNickname	as  p_OrderNickname						";	//발주처명 약칭	
		$sql01=$sql01."		, P.ProjectNickname	as  p_ProjectNickname					";	//프로젝트명 약칭
		$sql01=$sql01."		, P.ContractEnd		as  p_ContractEnd						";	//계약종료일	
		$sql01=$sql01."		,(P.ContractPayment*P.ActualityRatio/100) as p_ContractPayment_Real 	";//용역 지분계약금액
		$sql01=$sql01."		, P.orderCompany	as  p_orderCompany						";	//발주처명		
		$sql01=$sql01."		, P.projectName		as  p_projectName						";	//프로젝트명	
		$sql01=$sql01."		, P.workstatus		as  p_workstatus						";	//용역상태		
		$sql01=$sql01."		, P.name			as  p_name								";	//사내담당자	
		$sql01=$sql01."		, P.ActualityRatio	as  p_ActualityRatio					";	//당사지분율	
		$sql01=$sql01."		, P.ContractPayment as  p_ContractPayment 					";	//용역계약금액	
		$sql01=$sql01."	FROM															";
		$sql01=$sql01."		project_tbl P												";
		$sql01=$sql01."	WHERE															";
		$sql01=$sql01."		P.ContractEnd > '2010-12-31'								";
		$sql01=$sql01."	AND																";
		$sql01=$sql01."		P.MainGroup	='11'											";
		$sql01=$sql01."	AND																";
		$sql01=$sql01."		P.workstatus in('수행중', '수행중지' ,'준공완료' )						";
		$sql01=$sql01."	AND																";
		$sql01=$sql01."		P.MainGroup<>''												";
		$sql01=$sql01."	AND																";
		$sql01=$sql01."		P.ContractPayment <>''										";
		$sql01=$sql01."	AND																";
		$sql01=$sql01."		P.ActualityRatio<>'0'										";
		$sql01=$sql01."	AND																";
		$sql01=$sql01."		(															";
		$sql01=$sql01."			(P.ContractPayment*P.ActualityRatio/100)-				";
		$sql01=$sql01."			(														";
		$sql01=$sql01."			SELECT													";
		$sql01=$sql01."			sum(CP.CollectionPayment)CP								";
		$sql01=$sql01."			from													";
		$sql01=$sql01."			collectionpayment_tbl CP								";
		$sql01=$sql01."			WHERE													";
		$sql01=$sql01."			CP.projectCode= P.projectCode							";
		$sql01=$sql01."			)														";
		$sql01=$sql01."		)<>0														";
		$sql01=$sql01."	ORDER BY  P.year, P.Name, P.ContractEnd		";
		/////////////////
//echo "01::".$sql01."<br>"; 
		///////////////// 
			/*-----------------------------*/
			$re01 = mysql_query($sql01,$db);
			/*-----------------------------*/
			$count01 = mysql_num_rows($re01);
			/*-----------------------------------------------*/
			if($count01>0){//결과0
				while($re_row01 = mysql_fetch_array($re01)) {

					/*프로젝트코드-----------------------------*/
					$p_projectCode = $re_row01[p_projectCode];

					/*발주처-----------------------------*/
					$p_OrderNickname = $re_row01[p_OrderNickname];
					$ch_p_OrderNickname = mb_substr($re_row01[p_OrderNickname],0,6,"UTF-8");
						if(strlen($p_OrderNickname)>15){
							$re_row01[ch_p_OrderNickname] = $ch_p_OrderNickname."..";
						}else{
							$re_row01[ch_p_OrderNickname] = $p_OrderNickname;
						}//if End
					
					/*용역명-----------------------------*/
					$p_ProjectNickname = $re_row01[p_ProjectNickname];
					$ch_p_ProjectNickname = mb_substr($re_row01[p_ProjectNickname],0,9,"UTF-8");
						if(strlen($p_ProjectNickname)>24){
							$re_row01[ch_p_ProjectNickname] = $ch_p_ProjectNickname."..";
						}else{
							$re_row01[ch_p_ProjectNickname] = $p_ProjectNickname;
						}//if End
					
					/*계약금액(당사지분)-----------------------------*/
					$p_ContractPayment_Real    = $re_row01[p_ContractPayment_Real];
					$ch_p_ContractPayment_Real = (float)$p_ContractPayment_Real/1000000;
					$ch_p_ContractPayment_Real = sprintf('%1f',$ch_p_ContractPayment_Real);
					$re_row01[ch_p_ContractPayment_Real] = number_format($ch_p_ContractPayment_Real); //세자리구분자
					
					/*수금액 합계(프로젝트별)-----------------------------*/
					$sql02=       "		SELECT sum(CP.CollectionPayment) cp_sum FROM collectionpayment_tbl CP	";
					$sql02=$sql02."		WHERE                                                               	";
					$sql02=$sql02."		CP.ProjectCode = '".$p_projectCode."'                               	";
					$sql02=$sql02."		and CP.DemandDate <> '0000-00-00'                                   	";
					$sql02=$sql02."		and CP.DemandDate <> ''                                             	";
					$sql02=$sql02."		and CP.CollectionDate <> '0000-00-00'                               	";
					$sql02=$sql02."		and CP.CollectionDate <> ''                                         	";
					//echo $sql02."<br>";
					$result_pay02 = 0;
					$result02     = mysql_query($sql02,$db);
					$result_pay02 = mysql_result($result02,0,"cp_sum"); 
					$re_row01[result_pay02] = $result_pay02;

					$ch_result_pay02 = (float)$result_pay02/1000000;
					$ch_result_pay02 = number_format($ch_result_pay02); //세자리구분자
					//echo "미수금 합계::".$ch_result_pay02."<br>"; 
					$re_row01[ch_result_pay02] = $ch_result_pay02;
					
					/*잔액(프로젝트별)-----------------------------*/
					$p_remainPay = 0;
					$p_remainPay = round($p_ContractPayment_Real)-round($result_pay02);
						if($p_remainPay<=0){
							$ch_p_remainPay = 0;
						}else{
							$ch_p_remainPay = (float)$p_remainPay/1000000;
							$ch_p_remainPay = number_format($ch_p_remainPay); //세자리구분자
						}//if end
					$re_row01[ch_p_remainPay] = $ch_p_remainPay;
					
					/*수금율--------------------------------------*/
					$p_collectRate = ($result_pay02/$p_ContractPayment_Real)*100;
					//echo "p_collectRate::".$p_collectRate."<br>"; 
					$p_collectRate = sprintf("%.0f",round($p_collectRate,1));
					$re_row01[p_collectRate] = $p_collectRate;
					
					/* 공정율 --------------------------------------*/
					$cpp_processRate="0";
					$sql03 = "select CPP.CollectionPayment cpp_processRate from collectionpaymentplan_tbl CPP where CPP.ProjectCode = '".$p_projectCode."' and CPP.PlanDateKey = '공정율'";//공정율
					//echo $sql03."<br>";
					$result03 = mysql_query($sql03,$db);
					$cpp_processRate = mysql_result($result03,0,"cpp_processRate"); 
					$re_row01[cpp_processRate] = round((float)$cpp_processRate);
					
					/*현재월 청구액(프로젝트별) : collectionpayment_tbl-----------------------------*/
					$sql04=       "	SELECT sum(CP.CollectionPayment) cp_sum FROM collectionpayment_tbl CP	";
					$sql04=$sql04."	WHERE                                                               	";
					$sql04=$sql04."	CP.ProjectCode = '".$p_projectCode."'                               	";
					$sql04=$sql04."	AND												                        ";
					$sql04=$sql04."	CP.DemandDate like '".$date_todayYYYYMM."%'								";
					//echo $sql04."<br>";
					$result_pay04 = 0;
					$result04     = mysql_query($sql04,$db);
					$result_pay04 = mysql_result($result04,0,"cp_sum");  
					$ch_result_pay04 = (float)$result_pay04/1000000;
					$ch_result_pay04 = number_format($ch_result_pay04); //세자리구분자
					//echo "청구액 합계::".$ch_result_pay04."<br>"; 
					$re_row01[ch_result_pay04] = $ch_result_pay04;

					/*현재월 수금액(프로젝트별) : collectionpayment_tbl-----------------------------*/
					$sql05=       "	SELECT sum(CP.CollectionPayment) cp_sum FROM collectionpayment_tbl CP	";
					$sql05=$sql05."	WHERE                                                               	";
					$sql05=$sql05."	CP.ProjectCode = '".$p_projectCode."'                               	";
					$sql05=$sql05."	AND												                        ";
					$sql05=$sql05."	CP.DemandDate like '".$date_todayYYYYMM."%'								";
					$sql05=$sql05."	AND												                        ";
					$sql05=$sql05."	CP.CollectionDate like '".$date_todayYYYYMM."%'							";
					//echo $sql05."<br>";
					$result_pay05 = 0;
					$result05     = mysql_query($sql05,$db);
					$result_pay05 = mysql_result($result05,0,"cp_sum");  
					$ch_result_pay05 = (float)$result_pay05/1000000;
					$ch_result_pay05 = number_format($ch_result_pay05); //세자리구분자
					//echo "수금액 합계::".$ch_result_pay05."<br>"; 
					$re_row01[ch_result_pay05] = $ch_result_pay05;

					/*현재월 미수금(프로젝트별) : collectionpayment_tbl-----------------------------*/
					$sql06=       "	SELECT sum(CP.CollectionPayment) cp_sum FROM collectionpayment_tbl CP	";
					$sql06=$sql06."	WHERE                                                               	";
					$sql06=$sql06."	CP.ProjectCode = '".$p_projectCode."'                               	";
					$sql06=$sql06."	AND												                        ";
					$sql06=$sql06."	CP.DemandDate like '".$date_todayYYYYMM."%'								";
					$sql06=$sql06."	AND												                        ";
					$sql06=$sql06."	(CP.CollectionDate = '' or CP.CollectionDate = '0000-00-00')            ";
					//echo $sql06."<br>";
					$result_pay06 = 0;
					$result06     = mysql_query($sql06,$db);
					$result_pay06 = mysql_result($result06,0,"cp_sum");  
					$ch_result_pay06 = (float)$result_pay06/1000000;
					$ch_result_pay06 = number_format($ch_result_pay06); //세자리구분자
					//echo "미수금 합계::".$ch_result_pay06."<br>"; 
					$re_row01[ch_result_pay06] = $ch_result_pay06;

					/*월별 수금계획(프로젝트별) : collectionpaymentplan_tbl-----------------------------*/
					$monthPlan=array();
					$query_data07 = array(); 
					$num07 = 1;
					for($i=1;$i<13;$i++){
						/*---------------------------------------*/
						$ch_i = sprintf('%02d',$i);
						/*---------------------------------------*/
						$sql07=     "	SELECT												";
						$sql07=$sql07."  CPP.ProjectCode      	as cpp_ProjectCode      	";//
						$sql07=$sql07."	,CPP.PlanDateKey      	as cpp_PlanDateKey      	";//
						$sql07=$sql07."	,CPP.PaymentDate      	as cpp_PaymentDate      	";//
						$sql07=$sql07."	,CPP.CollectionPayment	as cpp_CollectionPayment	";//
						$sql07=$sql07."	,CPP.Note             	as cpp_Note             	";//
						$sql07=$sql07."	,CPP.UpdateDate       	as cpp_UpdateDate       	";//
						$sql07=$sql07."	,CPP.UpdateUser       	as cpp_UpdateUser       	";//
						$sql07=$sql07."	,substring(CPP.PlanDateKey,6,2) as cpp_month        ";//
						$sql07=$sql07."	FROM												";
						$sql07=$sql07."		collectionpaymentplan_tbl	CPP					";
						$sql07=$sql07."	WHERE												";
						$sql07=$sql07."		CPP.ProjectCode='".$p_projectCode."'				";
						$sql07=$sql07."	AND													";
						$sql07=$sql07."		substring(CPP.PlanDateKey,1,4) = '".$nowYear."'	";
						$sql07=$sql07."	AND													";
						$sql07=$sql07."		CPP.PlanDateKey <> '공정율'						";
						$sql07=$sql07."	AND													";
						$sql07=$sql07."		substring(CPP.PlanDateKey,6,2) = '".$ch_i."'	";
						/////////////////////////
						//echo "01::".$sql07."<br>";
						/////////////////////////
						/*-----------------------------*/
						$re07 = mysql_query($sql07,$db);
						/*-----------------------------*/
						$re_num07 = mysql_num_rows($re07);
						/*-----------------------------*/
						if($re_num07>0){
							$result_pay07    = mysql_result($re07,0,"cpp_CollectionPayment");
							$ch_result_pay07 = (float)$result_pay07/1000000;
							$ch_result_pay07 = number_format($ch_result_pay07); //세자리구분자
							$monthPlan[$i] = $ch_result_pay07;
						}else{
							$monthPlan[$i] = 0;
						}//if End

						$num07++;
					}//for End
					$re_row01[monthPlan] = $monthPlan; //월별 수금계획

					/* 내년도 수금예정금액 =(잔액)-수금예정 금액 합계(현재월~12월)--------------------------------  */
					$p_nextYearPay=0;
					$cpp_sum08=0;
					$sql08=       "	 SELECT sum(CPP.CollectionPayment) cpp_sum                   	";//
					$sql08=$sql08."	 FROM                                                        	";//
					$sql08=$sql08."	 collectionpaymentplan_tbl CPP                               	";//
					$sql08=$sql08."	 WHERE                                                       	";//
					$sql08=$sql08."	 CPP.ProjectCode = '".$p_projectCode."'                      	";//
					$sql08=$sql08."	 AND substring(CPP.PlanDateKey,1,4) = '".$nowYear."'         	";//
					$sql08=$sql08."	 AND substring(CPP.PlanDateKey,6,2) >= ".$date_todayMM_int."	";//
					$sql08=$sql08."	 AND CPP.PlanDateKey <> '공정율'                             	";//

//echo $sql08."<br>";
					$result08  = mysql_query($sql08,$db);
					$re_num08 = mysql_num_rows($result08);
						if($re_num08>0){
							$cpp_sum08 = mysql_result($result08,0,"cpp_sum"); //수금예정 금액 합계(당해년도 1월~12월)
						}else{}
						//echo $cpp_sum08."<br>";
					/*-----------------------------*/
					$p_remainPay2 = 0;
					$p_remainPay2 = round($p_ContractPayment_Real)-round($result_pay02);//잔액(프로젝트별)
					//echo "잔액".$p_remainPay2."<br>";
						if($p_remainPay2<=0){
							$p_nextYearPay=0;
							$ch_p_nextYearPay=0;
						}else{
							if($p_remainPay2>$cpp_sum08){ //잔액이 크면
								$p_nextYearPay=$p_remainPay2-$cpp_sum08;
								$ch_p_nextYearPay = (float)$p_nextYearPay/1000000;
								$ch_p_nextYearPay = number_format($ch_p_nextYearPay); //세자리구분자
							}else{
								$p_nextYearPay=0;
								$ch_p_nextYearPay=0;
							}//if End

						}//if end
					$re_row01[ch_p_nextYearPay] = $ch_p_nextYearPay;//내년도 수금예정금액 =(잔액)-수금예정 금액 합계(1월~12월) 

					/* 담당자--------------------------------  */
					$p_name = $re_row01[p_name];
					if($p_name!=""){
						$p_name_Name = MemberNo2Name($p_name);
					}else{
						$p_name_Name = "";
					}//if End
					//echo $p_name_Name."<br>";
					$re_row01[p_name_Name] = $p_name_Name;

					/* 최종변경일자--------------------------------  */
					$sql09 = "select max(UpdateDate) cp_UpdateDate from collectionpaymentplan_tbl where projectcode = '".$p_projectCode."' ";
					//echo $sql09."<br>";
					$result_salesAccount = 0;
					$result09 = mysql_query($sql09,$db);
					if($result09){
					$cp_UpdateDate = mysql_result($result09,0,"cp_UpdateDate");  
					}else{
					$cp_UpdateDate="noDate";
					}//if End
					$re_row01[cp_UpdateDate] = $cp_UpdateDate;
					/*----------------------------------------*/




					/*----------------------------------------*/
					$re_row01[p_MainGroupName] = $p_MainGroup;


					/*----------------------------------------*/


					array_push($query_data01,$re_row01);
				} //while End
			}else{
				//결과없음
			}
			/*----------------------------------------*/




		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		//$this->smarty->assign('content_id',$content_id);//프로젝트 코드
		/*----------------------------------------*/
		$this->smarty->assign('query_data01',$query_data01);
		/*----------------------------------------*/
		$this->smarty->assign('date_today',$date_today);
		$this->smarty->assign('nowYear',$nowYear);//당해년도 : yyyy
		$this->smarty->assign('date_todayMM_int',$date_todayMM_int);//당해년도 : MM ->int값으로 변형
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_pmResister/listPage07.tpl");
		/*----------------------------------------*/
	}  //PageList07() End
	/* ***************************************************************************************** */




	/* ***************************************************************************************** */
	function UpdatePage()	//관리대장 :  수금계획 입력/수정
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		global $db;
		/*--------------------*/
		global $GroupCode;
		$GroupCode = (int)$GroupCode;
		global $GroupName;
		/*--------------------*/
		global $date_today;
		global $nowYear; //당해년도 : yyyy
		/*---------------------------------------*/
		$content_id	= $_GET['content_id'];
		//$content_id = ($_GET['content_id']==""?$_POST['content_id']:$_GET['content_id']);// 프로젝트코드
//echo "01::".$content_id."<br>"; 
		/*---------------------------------------*/

/* 프로젝트 닉네임(한글값) *********************************************************** */
$ptc_ProjectNickname = projectToColumn($content_id,"ProjectNickname");
//echo "01::".$ptc_ProjectNickname."<br>"; 
/*------------------------------------------------------------------------------*/

/* 계약금액 **************************************************************************** */
$ptc_ContractPayment = projectToColumn($content_id,"ContractPayment");//계약금액
$ptc_ActualityRatio  = projectToColumn($content_id,"ActualityRatio");//
$sum_ContractPayment = $ptc_ContractPayment*$ptc_ActualityRatio/100; //실지분율 계약금액
$ch_sum_ContractPayment = number_format($sum_ContractPayment); //세자리구분자
//echo "01::".$ch_sum_ContractPayment."<br>"; 
/*------------------------------------------------------------------------------*/

/* 매출금액(result_salesAccount) ***************************************************** */
//$query = "select sum(CollectionPayment) A from collectionpayment_tbl where ProjectCode = '$pjcode' and DemandDate <> '0000-00-00' and DemandDate <> ''";
$sql01 = "select sum(CP.CollectionPayment) cp_sum from collectionpayment_tbl CP where CP.ProjectCode = '".$content_id."' and CP.DemandDate <> '0000-00-00' and CP.DemandDate <> ''";
//echo $sql01."<br>";
$result_salesAccount = 0;
$result01 = mysql_query($sql01,$db);
$result_salesAccount = mysql_result($result01,0,"cp_sum");  
$ch_result_salesAccount = number_format($result_salesAccount); //세자리구분자
//echo "01::".$ch_result_salesAccount."<br>"; 
/*------------------------------------------------------------------------------*/

/* 수금액 ********************************************************* */
//$query = "select sum(CollectionPayment) A from collectionpayment_tbl where ProjectCode = '$pjcode' and DemandDate <> '0000-00-00' and DemandDate <> ''";
$sql02 = "select sum(CP.CollectionPayment) cp_sum from collectionpayment_tbl CP where CP.ProjectCode = '".$content_id."' and CP.CollectionDate <> '0000-00-00' and CP.CollectionDate <> ''";
//echo $sql02."<br>";
$result_collection = 0;
$result02 = mysql_query($sql02,$db);
$result_collection = mysql_result($result02,0,"cp_sum");  
$ch_result_collection = number_format($result_collection); //세자리구분자
//echo "01::".$ch_result_collection."<br>"; 
/*------------------------------------------------------------------------------*/

/* 미수금(매출금액-수금액) ********************************************************* */
$remainPay = $result_salesAccount-$result_collection;
$ch_remainPay = number_format($remainPay); //세자리구분자
//echo "01::".$ch_remainPay."<br>"; 
/*------------------------------------------------------------------------------*/

/* 잔액(계약금액-매출금액) ********************************************************* */
$remainPay02 = $sum_ContractPayment-$result_salesAccount;
$ch_remainPay02 = number_format($remainPay02); //세자리구분자
//echo "01::".$ch_remainPay02."<br>"; 
/*------------------------------------------------------------------------------*/

/* 공정율 **************************************************************** */
//$query = "select CollectionPayment from collectionpaymentplan_tbl where ProjectCode = '".$content_id."' and PlanDateKey = '공정율'";//공정율
$sql03 = "select CPP.CollectionPayment cpp_sum from collectionpaymentplan_tbl CPP where CPP.ProjectCode = '".$content_id."' and CPP.PlanDateKey = '공정율'";//공정율
//echo $sql03."<br>";
$result03 = mysql_query($sql03,$db);
$cpp_sum = mysql_result($result03,0,"cpp_sum");  
//echo "cpp_sum::".$cpp_sum."<br>"; 
/*------------------------------------------------------------------------------*/

/* 용역 진행상태 **************************************************************** */
$ptc_WorkStatus = projectToColumn($content_id,"WorkStatus");
//echo "ptc_WorkStatus::".$ptc_WorkStatus."<br>"; 
/*------------------------------------------------------------------------------*/

/* 수금예정 금액 합계(당해년도 1월~12월) **************************************************************** */
$sql04 = "select sum(CPP.CollectionPayment) cpp_sum from collectionpaymentplan_tbl CPP where CPP.ProjectCode = '".$content_id."' and CPP.PlanDateKey like '".$nowYear."%' and CPP.PlanDateKey <> '공정율'";
//echo $sql04."<br>";
$result04 = mysql_query($sql04,$db);
$cpp_sum04 = mysql_result($result04,0,"cpp_sum");  
$ch_cpp_sum04 = number_format($cpp_sum04); //세자리구분자
//echo "ch_cpp_sum04::".$ch_cpp_sum04."<br>"; 
/*------------------------------------------------------------------------------*/

/* 내년 수금예정 잔액=잔액-올해수금예정금액합계 ********************************************************* */
$remainPayNext = $remainPay02-$cpp_sum04;
$ch_remainPayNext = number_format($remainPayNext); //세자리구분자
//echo "ch_remainPayNext::".$ch_remainPayNext."<br>"; 
/*------------------------------------------------------------------------------*/

		$query_data = array(); 
$num = 1;
		for($i=1;$i<13;$i++){
			/*---------------------------------------*/
			$ch_i = sprintf('%02d',$i);
			
			/*---------------------------------------*/
			$sql=     "	SELECT												";
			$sql=$sql."  CPP.ProjectCode      	as cpp_ProjectCode      	";//
			$sql=$sql."	,CPP.PlanDateKey      	as cpp_PlanDateKey      	";//
			$sql=$sql."	,CPP.PaymentDate      	as cpp_PaymentDate      	";//
			$sql=$sql."	,CPP.CollectionPayment	as cpp_CollectionPayment	";//
			$sql=$sql."	,CPP.Note             	as cpp_Note             	";//
			$sql=$sql."	,CPP.UpdateDate       	as cpp_UpdateDate       	";//
			$sql=$sql."	,CPP.UpdateUser       	as cpp_UpdateUser       	";//
			$sql=$sql."	,substring(CPP.PlanDateKey,6,2)       	as cpp_month       	";//
			$sql=$sql."	FROM												";
			$sql=$sql."		collectionpaymentplan_tbl	CPP					";
			$sql=$sql."	WHERE												";
			$sql=$sql."		CPP.ProjectCode='".$content_id."'				";
			$sql=$sql."	AND													";
			$sql=$sql."		substring(CPP.PlanDateKey,1,4) = '".$nowYear."'			";
			$sql=$sql."	AND													";
			$sql=$sql."		CPP.PlanDateKey <> '공정율'						";
			$sql=$sql."	AND													";
			$sql=$sql."		substring(CPP.PlanDateKey,6,2) = '".$ch_i."'	";
/////////////////////////
//echo "01::".$sql."<br>";
/////////////////////////
			/*-----------------------------*/
			$re = mysql_query($sql,$db);
			/*-----------------------------*/
			$re_num = mysql_num_rows($re);
			/*-----------------------------*/
			if($re_num>0){
				/*-----------------------------*/
				$cpp_PlanDateKey = mysql_result($re,0,"cpp_PlanDateKey");
				$cpp_PlanDateKey = mb_substr($cpp_PlanDateKey,5,2,"UTF-8");
				$int_cpp_PlanDateKey = (int)$cpp_PlanDateKey;
				/*-----------------------------*/
				$ch_cpp_CollectionPayment = number_format(mysql_result($re,0,"cpp_CollectionPayment"));
				$cpp_Note = mysql_result($re,0,"cpp_Note");
				/*-----------------------------*/
				$ItemData=array("int_cpp_PlanDateKey" =>$int_cpp_PlanDateKey,"ch_cpp_CollectionPayment" =>$ch_cpp_CollectionPayment,"cpp_Note" =>$cpp_Note);
				array_push($query_data,$ItemData);

			}else{
				$ItemData=array("int_cpp_PlanDateKey" =>$num,"ch_cpp_CollectionPayment" =>"","cpp_Note" =>"");
				array_push($query_data,$ItemData);
			}//if End

			$num++;
		}//for End
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('content_id',$content_id);//프로젝트 코드
		$this->smarty->assign('ptc_ProjectNickname',$ptc_ProjectNickname);//프로젝트 닉네임
		$this->smarty->assign('ch_sum_ContractPayment',$ch_sum_ContractPayment);//계약금액
		$this->smarty->assign('ch_result_salesAccount',$ch_result_salesAccount);//매출금액
		$this->smarty->assign('ch_result_collection',$ch_result_collection);//수금액
		$this->smarty->assign('ch_remainPay',$ch_remainPay);//미수금(매출금액-수금액)
		$this->smarty->assign('ch_remainPay02',$ch_remainPay02);//잔액(계약금액-매출금액)
		$this->smarty->assign('ch_remainPayNext',$ch_remainPayNext);//내년 수금예정 잔액=잔액-올해수금예정금액합계
		


		if($ch_remainPay02!=0){
				$this->smarty->assign('ch_remainPay02_Kind','Y');//잔액유무(Y)
		}else{
			$this->smarty->assign('ch_remainPay02_Kind','N');//잔액유무(N)
		}//if End
		$this->smarty->assign('cpp_sum',$cpp_sum);//공정율
		$this->smarty->assign('ptc_WorkStatus',$ptc_WorkStatus);//용역 진행상태
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->assign('nowYear',$nowYear);//당해년도 : yyyy
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_pmResister/updatePage.tpl");
		/*----------------------------------------*/
	}  //UpdatePage() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function UpdateDB()//수정 DB실행
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		$GroupCode =(int)$GroupCode;
		/*-------------------------*/
		global $GroupName;
		global $date_today;		// 오늘날짜 년월일       : yyyy-mm-dd
		global $date_today4;	// 오늘날짜 년월일 시분초: yyyy-mm-dd-ss 시 분 초
		global $nowYear; //당해년도 : yyyy
		/*-------------------------*/
		global $db;
		/*---------------------------------------*/
		//$content_id	= (int)$_GET['content_id'];
		/*---------------------------------------*/
		$content_id				= $_POST['content_id']; //프로젝트 코드
		/*---------------------------------------*/
		$cpp_sum				= $_POST['cpp_sum']; //공정율
		/*---------------------------------------*/
		//echo "01::".$content_id."\n";
		//echo "01::".$cpp_sum."\n";
		/* -------------------------------------------------- */
		/* 월별 수금예정금액, 비고*/
		for($i=1;$i<13;$i++){
			$cpp_CollectionPayment[$i]	= ($_POST['cpp_CollectionPayment'.$i]==""?"":$_POST['cpp_CollectionPayment'.$i]);	//월별 수금예정금액
			$cpp_Note[$i]				= ($_POST['cpp_Note'.$i]==""?"22":$_POST['cpp_Note'.$i]);	// 비고
			//echo "::".$i."===".$cpp_CollectionPayment[$i].":::".$cpp_Note[$i]."\n";
		}//for End
		/* -------------------------------------------------- */
		/* 프로젝트 명 조건으로 관련 로우 전체 삭제후 재입력*/
		$delete_query = "DELETE FROM collectionpaymentplan_tbl WHERE ProjectCode = '".$content_id."' ";
		/*-----------------------------*/
		$del_result = mysql_query($delete_query);
		/* -------------------------------------------------- */
		for($j=1;$j<13;$j++){
			if( $cpp_CollectionPayment[$j] != ""){
				/*---------------------------------------*/
				$ch_cpp_CollectionPayment = $cpp_CollectionPayment[$j];
				$ch_cpp_CollectionPayment = (int)str_replace(",","",$ch_cpp_CollectionPayment);//금액단위의 ','제거
				/*---------------------------------------*/
				$ch_j = sprintf('%02d',$j);
				$joinStr = $nowYear."년".$ch_j."월";
				/*---------------------------------------*/
				$query41= "			INSERT INTO collectionpaymentplan_tbl 																			";
				$query41= $query41." (ProjectCode,PlanDateKey,PaymentDate,CollectionPayment,Note,UpdateDate,UpdateUser)								";
				$query41= $query41." VALUES																											";
				$query41= $query41." ('".$content_id."','".$joinStr."','','".$ch_cpp_CollectionPayment."','', '".$date_today4."','".$MemberNo."')	";
				//echo "query41::".$query41;
			////////////////////////
			mysql_query($query41);
			////////////////////////
			} //if End
		}//for End
		/* 공정율 재입력-------------------------------------------------- */
		$query42= "			INSERT INTO collectionpaymentplan_tbl 																			";
		$query42= $query42." (ProjectCode,PlanDateKey,PaymentDate,CollectionPayment,Note,UpdateDate,UpdateUser)								";
		$query42= $query42." VALUES																											";
		$query42= $query42." ('".$content_id."','공정율','','".$cpp_sum."','', '".$date_today4."','".$MemberNo."')	";
		//echo "query42::".$query42;
		////////////////////////
		mysql_query($query42);
		////////////////////////
		echo "1";
		/* -------------------------------------------------- */
	}  //UpdateDB() End
	/* ***************************************************************************************** */























	/* ***************************************************************************************** */
	function ViewPage03()// 특정업체 외주 평가현황 : 상세보기 페이지로 이동
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		global $db;
		/*---------------------------------------*/
		$content_id	= $_GET['content_id'];
		/*---------------------------------------*/
		$ocs_company_no = ""; //회사명

		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT                                      ";
		$sql=$sql."	 OCS.num				as ocs_num			";//
		$sql=$sql."	,OCS.company_no  		as ocs_company_no 	";//
		$sql=$sql."	,OCS.wdate       		as ocs_wdate      	";//
		$sql=$sql."	,OCS.writer      		as ocs_writer       ";//
		$sql=$sql."	,OCS.writerinfo  		as ocs_writerinfo   ";//
		$sql=$sql."	,OCS.ProjectCode 		as ocs_ProjectCode	";//
		$sql=$sql."	,OCS.cv1         		as ocs_cv1			";//
		$sql=$sql."	,OCS.cv2         		as ocs_cv2			";//
		$sql=$sql."	,OCS.cv3         		as ocs_cv3			";//
		$sql=$sql."	,OCS.cv4         		as ocs_cv4			";//
		$sql=$sql."	,OCS.cv5         		as ocs_cv5			";//
		$sql=$sql."	,OCS.cv6         		as ocs_cv6			";//
		$sql=$sql."	,OCS.cv7         		as ocs_cv7			";//
		$sql=$sql."	,OCS.cv1+OCS.cv2+OCS.cv3+OCS.cv4+OCS.cv5+OCS.cv6+OCS.cv7 as ocs_cvsum		";//
		$sql=$sql."	,OCS.tv1         		as ocs_tv1          ";//
		$sql=$sql."	,OCS.tv2         		as ocs_tv2          ";//
		$sql=$sql."	,OCS.tv3         		as ocs_tv3			";//
		$sql=$sql."	,OCS.tv4         		as ocs_tv4			";//
		$sql=$sql."	,OCS.tv5         		as ocs_tv5			";//
		$sql=$sql."	,OCS.tv6         		as ocs_tv6			";//
		$sql=$sql."	,OCS.tv7         		as ocs_tv7			";//
		$sql=$sql."	,OCS.tv8         		as ocs_tv8			";//
		$sql=$sql."	,OCS.tv9         		as ocs_tv9          ";//
		$sql=$sql."	,OCS.tv10        		as ocs_tv10			";//
		$sql=$sql."	,OCS.tv11        		as ocs_tv11			";//
		$sql=$sql."	,OCS.tv1+OCS.tv2+OCS.tv3+OCS.tv4+OCS.tv5+OCS.tv6+OCS.tv7+OCS.tv8+OCS.tv9+OCS.tv10+OCS.tv11 as ocs_tvsum        ";//
		//$sql=$sql."	,OCS.total       		as ocs_total			";//
		$sql=$sql."	,P.ProjectCode     		as p_ProjectCode    		";//
		$sql=$sql."	,P.ProjectNickname 		as p_ProjectNickname		";//
		$sql=$sql."	,P.OrderCompany    		as p_OrderCompany   		";//
		$sql=$sql."	FROM                                                ";
		$sql=$sql."	outside_cooperation_sheet_tbl OCS                   ";
		$sql=$sql."	,project_tbl P                                      ";
		$sql=$sql."	WHERE                                               ";
		$sql=$sql."	OCS.ProjectCode = P.ProjectCode                     ";
		$sql=$sql."	AND                                                 ";
		$sql=$sql." OCS.num=".$content_id."								";//
		/*---------------------------------------*/
		/////////////////
		//echo "01::".$sql."<br>"; 
		/////////////////
			/*-----------------------------*/
			$re = mysql_query($sql,$db);
			/*-----------------------------*/
			while($re_row = mysql_fetch_array($re)) {
				$re_row[ocs_total]  = $re_row[ocs_cvsum]+$re_row[ocs_tvsum]; //공통점수+기술점수합계(x/100)

				$ocs_writer  = $re_row[ocs_writer];
				$re_row[ocs_writerName] = MemberNo2Name($ocs_writer);//return 사원명
				$re_row[ocs_GroupName] = memberNoToGroupName($ocs_writer);//return 소속부서명
				$re_row[ocs_PositionName] = memberNoToPositionName($ocs_writer);//return 직위명

				$ocs_company_no = $re_row[ocs_company_no];

				array_push($query_data,$re_row);
			} //while End
		/*----------------------------------------*/
		$sql03=       "	SELECT								";
		$sql03=$sql03."	 OC.Company    		as oc_Company	";//
		$sql03=$sql03."	,OC.no				as oc_no		";//PK  
		$sql03=$sql03."	FROM								";
		$sql03=$sql03."	outside_cooperation_tbl OC			";
		$sql03=$sql03."	WHERE								";
		$sql03=$sql03." OC.no ='".$ocs_company_no."'			";
		/////////////////
		//echo "01::<br>".$sql03."<br>"; 
		/////////////////
		$result03 = mysql_query($sql03,$db);
		$result_num03 = mysql_num_rows($result03);
		/* ----------------------------------- */
		if($result_num03 != 0) {
			$oc_Company			= mysql_result($result03,0,"oc_Company"); //업체명
		} //if End
		$this->smarty->assign('oc_Company',$oc_Company); //업체명
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('content_id',$content_id);
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_pmResister/viewPage03.tpl");
		/*----------------------------------------*/

	}  //ViewPage03() End
	/* ***************************************************************************************** */


	/* ***************************************************************************************** */
	function UpdatePage03()	//수정페이지로 이동
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;

		global $date_today;
		global $position;
		global $GroupName;
		/*------------------*/
		global $db;
		/*---------------------------------------*/
		$content_id	= $_GET['content_id'];
		/*---------------------------------------*/
		$ocs_company_no = ""; //회사명
		/*------------------*/
		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT                                      ";
		$sql=$sql."	 OCS.num				as ocs_num			";//테이블PK
		$sql=$sql."	,OCS.company_no  		as ocs_company_no 	";//협력업체 코드
		$sql=$sql."	,OCS.wdate       		as ocs_wdate      	";//등록/수정일자
		$sql=$sql."	,OCS.writer      		as ocs_writer       ";//등록자 사원번호
		$sql=$sql."	,OCS.writerinfo  		as ocs_writerinfo   ";//
		$sql=$sql."	,OCS.ProjectCode 		as ocs_ProjectCode	";//프로젝트코드
		$sql=$sql."	,OCS.cv1         		as ocs_cv1			";//공통평가점수01
		$sql=$sql."	,OCS.cv2         		as ocs_cv2			";//공통평가점수02
		$sql=$sql."	,OCS.cv3         		as ocs_cv3			";//공통평가점수03
		$sql=$sql."	,OCS.cv4         		as ocs_cv4			";//공통평가점수04
		$sql=$sql."	,OCS.cv5         		as ocs_cv5			";//공통평가점수05
		$sql=$sql."	,OCS.cv6         		as ocs_cv6			";//공통평가점수06
		$sql=$sql."	,OCS.cv7         		as ocs_cv7			";//공통평가점수07
		$sql=$sql."	,OCS.cv1+OCS.cv2+OCS.cv3+OCS.cv4+OCS.cv5+OCS.cv6+OCS.cv7 as ocs_cvsum ";//공통평가점수 합계
		$sql=$sql."	,OCS.tv1         		as ocs_tv1          ";//기술평가점수01
		$sql=$sql."	,OCS.tv2         		as ocs_tv2          ";//기술평가점수02
		$sql=$sql."	,OCS.tv3         		as ocs_tv3			";//기술평가점수03
		$sql=$sql."	,OCS.tv4         		as ocs_tv4			";//기술평가점수04
		$sql=$sql."	,OCS.tv5         		as ocs_tv5			";//기술평가점수05
		$sql=$sql."	,OCS.tv6         		as ocs_tv6			";//기술평가점수06
		$sql=$sql."	,OCS.tv7         		as ocs_tv7			";//기술평가점수07
		$sql=$sql."	,OCS.tv8         		as ocs_tv8			";//기술평가점수08
		$sql=$sql."	,OCS.tv9         		as ocs_tv9          ";//기술평가점수09
		$sql=$sql."	,OCS.tv10        		as ocs_tv10			";//기술평가점수10
		$sql=$sql."	,OCS.tv11        		as ocs_tv11			";//기술평가점수11
		$sql=$sql."	,OCS.tv1+OCS.tv2+OCS.tv3+OCS.tv4+OCS.tv5+OCS.tv6+OCS.tv7+OCS.tv8+OCS.tv9+OCS.tv10+OCS.tv11 as ocs_tvsum ";//기술평가점수 합계
		//$sql=$sql."	,OCS.total       		as ocs_total			";//
		$sql=$sql."	,P.ProjectCode     		as p_ProjectCode    		";//프로젝트코드
		$sql=$sql."	,P.ProjectNickname 		as p_ProjectNickname		";//프로젝트 닉네임
		$sql=$sql."	,P.OrderCompany    		as p_OrderCompany   		";//발주처이름

		$sql=$sql."	FROM                                                ";
		$sql=$sql."	outside_cooperation_sheet_tbl OCS                   ";
		$sql=$sql."	,project_tbl P                                      ";
		
		$sql=$sql."	WHERE                                               ";
		$sql=$sql."	OCS.ProjectCode = P.ProjectCode                     ";
		$sql=$sql."	AND                                                 ";
		$sql=$sql." OCS.num=".$content_id."								";//
		/////////////////
		//echo "01::".$sql."<br>"; 
		/////////////////
			/*-----------------------------*/
			$re = mysql_query($sql,$db);
			/*-----------------------------*/
			while($re_row = mysql_fetch_array($re)) {
				$re_row[ocs_total]  = $re_row[ocs_cvsum]+$re_row[ocs_tvsum]; //공통점수+기술점수합계(x/100)

				$ocs_writer  = $re_row[ocs_writer];
				$re_row[ocs_writerName] = MemberNo2Name($ocs_writer);//return 사원명
				$re_row[ocs_GroupName] = memberNoToGroupName($ocs_writer);//return 소속부서명
				$re_row[ocs_PositionName] = memberNoToPositionName($ocs_writer);//return 직위명

				$ocs_company_no = $re_row[ocs_company_no];

				array_push($query_data,$re_row);
			} //while End
		/*----------------------------------------*/
		$sql03=       "	SELECT								";
		$sql03=$sql03."	 OC.Company    		as oc_Company	";//
		$sql03=$sql03."	,OC.no				as oc_no		";//PK  
		$sql03=$sql03."	FROM								";
		$sql03=$sql03."	outside_cooperation_tbl OC			";
		$sql03=$sql03."	WHERE								";
		$sql03=$sql03." OC.no ='".$ocs_company_no."'			";
		/////////////////
		//echo "01::<br>".$sql03."<br>"; 
		/////////////////
		$result03 = mysql_query($sql03,$db);
		$result_num03 = mysql_num_rows($result03);
		/* ----------------------------------- */
		if($result_num03 != 0) {
			$oc_Company			= mysql_result($result03,0,"oc_Company"); //업체명
		} //if End
		$this->smarty->assign('oc_Company',$oc_Company); //업체명
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('position',$position);
		$this->smarty->assign('GroupName',$GroupName);
		/*----------------------------------------*/
		$this->smarty->assign('date_today',$date_today);
		/*----------------------------------------*/
		$this->smarty->assign('content_id',$content_id);
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_pmResister/updatePage03.tpl");
		/*----------------------------------------*/
	}  //UpdatePage03() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function UpdateDB03()//수정 DB실행 : 협력업체 평가내역 수정
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $CompanyKind;
		$GroupCode =(int)$GroupCode;
		/*--------------------*/
		global $GroupName;
		global $date_today;		// 오늘날짜 년월일       : yyyy-mm-dd
		global $date_today4;	// 오늘날짜 년월일 시분초: yyyy-mm-dd-ss 시 분 초
		/*--------------------*/
		global $db;
		/*---------------------------------------*/
		//$content_id	= (int)$_GET['content_id'];
		/*---------------------------------------*/
		$ocs_num		= $_POST['content_id'];		//테이블PK       
		/* -------------------------------------------------- */
		$ocs_company_no = $_POST['ocs_company_no'];	//협력업체 코드  
		$ocs_wdate		= $_POST['ocs_wdate'];		//등록/수정일자  
		$ocs_writer		= $_POST['ocs_writer'];		//등록자 사원번호
		$ocs_ProjectCode= $_POST['ocs_ProjectCode'];//프로젝트코드   
		/* -------------------------------------------------- */
		$ocs_cv1		= $_POST['ocs_cv1'];		//공통평가점수01 
		$ocs_cv2		= $_POST['ocs_cv2'];		//공통평가점수02 
		$ocs_cv3		= $_POST['ocs_cv3'];		//공통평가점수03 
		$ocs_cv4		= $_POST['ocs_cv4'];		//공통평가점수04 
		$ocs_cv5		= $_POST['ocs_cv5'];		//공통평가점수05 
		$ocs_cv6		= $_POST['ocs_cv6'];		//공통평가점수06 
		$ocs_cv7		= $_POST['ocs_cv7'];		//공통평가점수07 
		/* -------------------------------------------------- */
		$ocs_tv1		= $_POST['ocs_tv1'];		//기술평가점수01 
		$ocs_tv2		= $_POST['ocs_tv2'];		//기술평가점수02 
		$ocs_tv3		= $_POST['ocs_tv3'];		//기술평가점수03 
		$ocs_tv4		= $_POST['ocs_tv4'];		//기술평가점수04 
		$ocs_tv5		= $_POST['ocs_tv5'];		//기술평가점수05 
		$ocs_tv6		= $_POST['ocs_tv6'];		//기술평가점수06 
		$ocs_tv7		= $_POST['ocs_tv7'];		//기술평가점수07 
		$ocs_tv8		= $_POST['ocs_tv8'];		//기술평가점수08 
		$ocs_tv9		= $_POST['ocs_tv9'];		//기술평가점수09 
		$ocs_tv10		= $_POST['ocs_tv10'];		//기술평가점수10 
		$ocs_tv11		= $_POST['ocs_tv11'];		//기술평가점수11 
		/* -------------------------------------------------- */
		$ocs_writerinfo = "";
		$ocs_cvsum = "";
		$ocs_tvsum = "";
		$ocs_total = "";
		/***********************************************************************************/
		$update_db =			" UPDATE                                  	"; 
		$update_db = $update_db." outside_cooperation_sheet_tbl SET         ";
		$update_db = $update_db."  company_no		= '".$ocs_company_no."' ";	//협력업체 코드  
		$update_db = $update_db." ,wdate	= '".$ocs_wdate."'				";	//등록/수정일자  
		$update_db = $update_db." ,writer	= '".$ocs_writer."'				";	//등록자 사원번호
		$update_db = $update_db." ,ProjectCode	= '".$ocs_ProjectCode."'	";	//프로젝트코드   
		$update_db = $update_db." ,cv1	= '".$ocs_cv1."'					";	//공통평가점수01
		$update_db = $update_db." ,cv2	= '".$ocs_cv2."'					";	//공통평가점수02
		$update_db = $update_db." ,cv3	= '".$ocs_cv3."'					";	//공통평가점수03
		$update_db = $update_db." ,cv4	= '".$ocs_cv4."'					";	//공통평가점수04
		$update_db = $update_db." ,cv5	= '".$ocs_cv5."'					";	//공통평가점수05
		$update_db = $update_db." ,cv6	= '".$ocs_cv6."'					";	//공통평가점수06 
		$update_db = $update_db." ,cv7	= '".$ocs_cv7."'					";	//공통평가점수07 
		$update_db = $update_db." ,tv1	= '".$ocs_tv1."'					";	//기술평가점수01  
		$update_db = $update_db." ,tv2	= '".$ocs_tv2."'					";	//기술평가점수02  
		$update_db = $update_db." ,tv3	= '".$ocs_tv3."'					";	//기술평가점수03  
		$update_db = $update_db." ,tv4	= '".$ocs_tv4."'					";	//기술평가점수04 
		$update_db = $update_db." ,tv5	= '".$ocs_tv5."'					";	//기술평가점수05 
		$update_db = $update_db." ,tv6	= '".$ocs_tv6."'					";	//기술평가점수06 
		$update_db = $update_db." ,tv7	= '".$ocs_tv7."'					";	//기술평가점수07 
		$update_db = $update_db." ,tv8	= '".$ocs_tv8."'					";	//기술평가점수08 
		$update_db = $update_db." ,tv9	= '".$ocs_tv9."'					";	//기술평가점수09 
		$update_db = $update_db." ,tv10	= '".$ocs_tv10."'					";	//기술평가점수10 
		$update_db = $update_db." ,tv11	= '".$ocs_tv11."'					";	//기술평가점수11 
		/*----------------------------------------*/
		$update_db = $update_db." ,writerinfo	= '".$ocs_writerinfo."'		";	//기술평가점수06 
		$update_db = $update_db." ,total		= '".$ocs_total."'			";	//기술평가점수07 
		$update_db = $update_db." ,cvsum		= '".$ocs_cvsum."'			";	//기술평가점수08 
		$update_db = $update_db." ,tvsum		= '".$ocs_tvsum."'			";	//기술평가점수09 
		/*----------------------------------------*/
		$update_db = $update_db." WHERE										";	//   
		$update_db = $update_db." num='".$ocs_num."'						";	//테이블PK
		/*----------------------------------------*/
	//////////////////////////////////
	//echo $update_db;
	mysql_query($update_db,$db);
	//////////////////////////////////
		/*----------------------------------------*/
	}  //UpdateDB03() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function InsertPage03()//입력페이지로 이동
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $GroupCode;
		$GroupCode =(int)$GroupCode;
		/*---------------------*/
		global $date_today;
		global $position;
		global $GroupName;
		/*---------------------*/
		global $db;
		/*---------------------*/
		$content_id	= (int)$_GET['content_id'];
		/*----------------------------------------*/
		$sql03=       "	SELECT								";
		$sql03=$sql03."	 OC.Company    		as oc_Company	";//
		$sql03=$sql03."	,OC.no				as oc_no		";//PK  
		$sql03=$sql03."	FROM								";
		$sql03=$sql03."	outside_cooperation_tbl OC			";
		$sql03=$sql03."	WHERE								";
		$sql03=$sql03." OC.no ='".$content_id."'			";
		/////////////////
		//echo "01::<br>".$sql03."<br>"; 
		/////////////////
		$result03 = mysql_query($sql03,$db);
		$result_num03 = mysql_num_rows($result03);
		/* ----------------------------------- */
		if($result_num03 != 0) {
			$oc_Company		= mysql_result($result03,0,"oc_Company"); //업체명
			$oc_no			= mysql_result($result03,0,"oc_no"); //업체명

		} //if End
		$this->smarty->assign('oc_Company',$oc_Company); //업체명
		$this->smarty->assign('oc_no',$oc_no); //업체코드
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		$this->smarty->assign('GroupCode',$GroupCode);
		$this->smarty->assign('GroupName',$GroupName);
		$this->smarty->assign('position',$position);
		/*----------------------------------------*/
		$this->smarty->assign('date_today',$date_today);
		/*----------------------------------------*/
		$this->DepartKind();
		$this->CpCompanyKind();
		$this->smarty->display("intranet/common_contents/work_pmResister/insertPage03.tpl");
		/*----------------------------------------*/

	}  //InsertPage03() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function InsertDB03()//입력 DB실행
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		$GroupCode =(int)$GroupCode;
		/*---------------------*/
		global $GroupName;
		global $date_today;		// 오늘날짜 년월일       : yyyy-mm-dd
		global $date_today4;	// 오늘날짜 년월일 시분초: yyyy-mm-dd-ss 시 분 초
		/*---------------------*/
		global $db;
		/*---------------------------------------*/
		//$content_id	= (int)$_GET['content_id'];
		/*---------------------------------------*/
		$ocs_num		= $_POST['content_id'];		//테이블PK       
		/* -------------------------------------------------- */
		$ocs_company_no = $_POST['ocs_company_no'];	//협력업체 코드  
		$ocs_wdate		= $_POST['ocs_wdate'];		//등록/수정일자  
		$ocs_writer		= $_POST['ocs_writer'];		//등록자 사원번호
		$ocs_ProjectCode= $_POST['ocs_ProjectCode'];//프로젝트코드   
		/* -------------------------------------------------- */
		$ocs_cv1		= $_POST['ocs_cv1'];		//공통평가점수01 
		$ocs_cv2		= $_POST['ocs_cv2'];		//공통평가점수02 
		$ocs_cv3		= $_POST['ocs_cv3'];		//공통평가점수03 
		$ocs_cv4		= $_POST['ocs_cv4'];		//공통평가점수04 
		$ocs_cv5		= $_POST['ocs_cv5'];		//공통평가점수05 
		$ocs_cv6		= $_POST['ocs_cv6'];		//공통평가점수06 
		$ocs_cv7		= $_POST['ocs_cv7'];		//공통평가점수07 
		/* -------------------------------------------------- */
		$ocs_tv1		= $_POST['ocs_tv1'];		//기술평가점수01 
		$ocs_tv2		= $_POST['ocs_tv2'];		//기술평가점수02 
		$ocs_tv3		= $_POST['ocs_tv3'];		//기술평가점수03 
		$ocs_tv4		= $_POST['ocs_tv4'];		//기술평가점수04 
		$ocs_tv5		= $_POST['ocs_tv5'];		//기술평가점수05 
		$ocs_tv6		= $_POST['ocs_tv6'];		//기술평가점수06 
		$ocs_tv7		= $_POST['ocs_tv7'];		//기술평가점수07 
		$ocs_tv8		= $_POST['ocs_tv8'];		//기술평가점수08 
		$ocs_tv9		= $_POST['ocs_tv9'];		//기술평가점수09 
		$ocs_tv10		= $_POST['ocs_tv10'];		//기술평가점수10 
		$ocs_tv11		= $_POST['ocs_tv11'];		//기술평가점수11 
		/* -------------------------------------------------- */
		$ocs_writerinfo = "";
		$ocs_cvsum = "";
		$ocs_tvsum = "";
		$ocs_total = "";
		/***********************************************************************************/
		/* ------------------------------------------------------------------------------- */
		$insert_db =			"INSERT INTO										"; 
		$insert_db = $insert_db." outside_cooperation_sheet_tbl						";
		$insert_db = $insert_db." (company_no,wdate,writer,ProjectCode,				";
		$insert_db = $insert_db." cv1,cv2,cv3,cv4,cv5,cv6,cv7,						";
		$insert_db = $insert_db." tv1,tv2,tv3,tv4,tv5,tv6,tv7,tv8,tv9,tv10,tv11,	";
		$insert_db = $insert_db." writerinfo,cvsum,tvsum,total)                 	";
		$insert_db = $insert_db." VALUES						";
		$insert_db = $insert_db." (								";
		$insert_db = $insert_db."    '".$ocs_company_no ."'		";	//
		$insert_db = $insert_db."   ,'".$ocs_wdate."'			";	//
		$insert_db = $insert_db."   ,'".$ocs_writer."'			";	//
		$insert_db = $insert_db."   ,'".$ocs_ProjectCode."'		";	//
		$insert_db = $insert_db."   ,'".$ocs_cv1."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_cv2."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_cv3."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_cv4."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_cv5."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_cv6."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_cv7."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_tv1."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_tv2."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_tv3."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_tv4."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_tv5."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_tv6."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_tv7."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_tv8."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_tv9."'				";	//
		$insert_db = $insert_db."   ,'".$ocs_tv10."'			";	//
		$insert_db = $insert_db."   ,'".$ocs_tv11."'			";	//
		$insert_db = $insert_db."   ,'".$ocs_writerinfo."'		";	//
		$insert_db = $insert_db."   ,'".$ocs_cvsum."'			";	//
		$insert_db = $insert_db."   ,'".$ocs_tvsum."'			";	//
		$insert_db = $insert_db."   ,'".$ocs_total."'			";	//
		$insert_db = $insert_db." )								";
		/* ------------------------------------------------------------------------------- */
	//////////////////////////////////
	//echo "<br>".$insert_db;
	mysql_query($insert_db,$db);
	//////////////////////////////////
	//mysql_close($db);
	//////////////////////////////////
		/*----------------------------------------*/
	}  //InsertDB03() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function DeleteDB03()	//삭제DB : 평가내역
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $db;
		/*-----------------------------*/
		$content_id			= (int)$_POST['content_id'];	//컨텐츠PK
		/*-----------------------------*/
		//$delete_query = "DELETE FROM outside_cooperation_sheet_tbl WHERE num = '".$content_id."'";
		/***********************************************************************************/
		$delete_query =			      " UPDATE                              ";
		$delete_query = $delete_query." outside_cooperation_sheet_tbl SET	";
		$delete_query = $delete_query."  del_flag = '1'						";
		$delete_query = $delete_query." WHERE								";
		$delete_query = $delete_query." num = '".$content_id."'				";
		/***********************************************************************************/
		/*-----------------------------*/
		$result = mysql_query($delete_query);
		if($result){
			echo "1";	//삭제 성공
		}else{
			echo "2";	//삭제 실패
		}
		/*-----------------------------*/
	}  //DeleteDB() End

	/* ***************************************************************************************** */
	function InsertPage()//입력페이지로 이동
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $GroupCode;
		$GroupCode =(int)$GroupCode;
		/*---------------------------*/
		global $GroupName;
		global $date_today;
		/*---------------------------*/
		global $db;
		//$content_id	= (int)$_GET['content_id'];
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		$this->smarty->assign('GroupCode',$GroupCode);
		$this->smarty->assign('GroupName',$GroupName);
		/*----------------------------------------*/
		$this->smarty->assign('date_today',$date_today);
		/*----------------------------------------*/
		$this->DepartKind();
		$this->CpCompanyKind();
		$this->smarty->display("intranet/common_contents/work_pmResister/insertPage.tpl");
		/*----------------------------------------*/

	}  //InsertPage() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function InsertDB()//입력 DB실행
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		$GroupCode =(int)$GroupCode;
		/*---------------------------*/
		global $GroupName;
		global $date_today;		// 오늘날짜 년월일       : yyyy-mm-dd
		global $date_today4;	// 오늘날짜 년월일 시분초: yyyy-mm-dd-ss 시 분 초
		/*---------------------------*/
		global $db;
		/* -------------------------------------------------- */
		$oc_Company			= $_POST['oc_Company'];			//*업체명
		$oc_CompanyNicName	= $_POST['oc_CompanyNicName'];	//약칭
		$oc_Part			= $_POST['oc_Part'];			//*분야
		$oc_DetailPart		= $_POST['oc_DetailPart'];		//세부공종
		/* -------------------------------------------------- */
		$oc_ProviderNo1		= $_POST['oc_ProviderNo1'];		//*사업자번호1
		$oc_ProviderNo2		= $_POST['oc_ProviderNo2'];		//*사업자번호2
		$oc_ProviderNo3		= $_POST['oc_ProviderNo3'];		//*사업자번호3
		$oc_ProviderNo		= $oc_ProviderNo1."-".$oc_ProviderNo2."-".$oc_ProviderNo3;
		/* -------------------------------------------------- */
		$oc_Staff			= $_POST['oc_Staff'];			//종업원규모
		$oc_P_Name			= $_POST['oc_P_Name'];			//회사대표:성명
		/* -------------------------------------------------- */
		$oc_P_Mobile1		= $_POST['oc_P_Mobile1'];		//회사대표:연락처1
		$oc_P_Mobile2		= $_POST['oc_P_Mobile2'];		//회사대표:연락처2
		$oc_P_Mobile3		= $_POST['oc_P_Mobile3'];		//회사대표:연락처3
		$oc_P_Mobile		= $oc_P_Mobile1."-".$oc_P_Mobile2."-".$oc_P_Mobile3;
		/* -------------------------------------------------- */
		$oc_Phone1			= $_POST['oc_Phone1'];			//업체:전화번호1
		$oc_Phone2			= $_POST['oc_Phone2'];			//업체:전화번호2
		$oc_Phone3			= $_POST['oc_Phone3'];			//업체:전화번호3
		$oc_Phone			= $oc_Phone1."-".$oc_Phone2."-".$oc_Phone3;
		/* -------------------------------------------------- */
		$oc_Name			= $_POST['oc_Name'];			//업체:담당자명
		/* -------------------------------------------------- */
		$oc_Fax1			= $_POST['oc_Fax1'];			//업체:FAX1
		$oc_Fax2			= $_POST['oc_Fax2'];			//업체:FAX2
		$oc_Fax3			= $_POST['oc_Fax3'];			//업체:FAX3
		$oc_Fax				= $oc_Fax1."-".$oc_Fax2."-".$oc_Fax3;
		/* -------------------------------------------------- */
		$oc_Address			= $_POST['oc_Address'];			//주소
		/* -------------------------------------------------- */
		$oc_BeginningDate	= $_POST['oc_BeginningDate'];	//최초영업일자
		$oc_RegisterDate	= $_POST['oc_RegisterDate'];	//등록일자
		/* -------------------------------------------------- */
		$oc_Fortune			= $_POST['oc_Fortune'];			//회사자산:자산규모
		$oc_SalesAmount		= $_POST['oc_SalesAmount'];		//회사자산:매출액
		/* -------------------------------------------------- */
		$oc_MainGroupName	= $_POST['oc_MainGroupName'];	//사내담당:관리부서
		$oc_MemberNo		= $_POST['oc_MemberNo'];		//사내담당:담당자
		/* -------------------------------------------------- */
		if( $oc_ProviderNo1=="" ||$oc_ProviderNo2=="" ){
			$oc_ProviderNo = "";
		}
		if( $oc_P_Mobile1=="" ||$oc_P_Mobile2=="" ){
			$oc_P_Mobile = "";
		}
		if( $oc_Phone1=="" ||$oc_Phone2=="" ){
			$oc_Phone = "";
		}
		if( $oc_Fax1=="" ||$oc_Fax2=="" ){
			$oc_Fax = "";
		}
		/***********************************************************************************/
		/* ------------------------------------------------------------------------------- */
		$insert_db =			"INSERT INTO																							"; 
		$insert_db = $insert_db." Outside_cooperation_tbl																				";
		$insert_db = $insert_db." (Company,CompanyNicName,Part,DetailPart,Phone,Fax,Name												";
		$insert_db = $insert_db."  ,MemberNo,MainGroup,Address,P_Name,P_Mobile,ProviderNo												";
		$insert_db = $insert_db."  ,Staff,BeginningDate,Fortune,SalesAmount,TaxArrear,RegisterDate,UpdateDate,UpdateUser,ListDisplay)	";
		$insert_db = $insert_db." VALUES						";
		$insert_db = $insert_db." (								";
		$insert_db = $insert_db."    '".$oc_Company."'			";	//*업체명          
		$insert_db = $insert_db."   ,'".$oc_CompanyNicName."'	";	//약칭             
		$insert_db = $insert_db."   ,'".$oc_Part."'				";	//*분야            
		$insert_db = $insert_db."   ,'".$oc_DetailPart."'		";	//세부공종         
		$insert_db = $insert_db."   ,'".$oc_Phone."'			";	//업체:전화번호    
		$insert_db = $insert_db."   ,'".$oc_Fax."'				";	//업체:FAX         
		$insert_db = $insert_db."   ,'".$oc_Name."'				";	//업체:담당자명    
		$insert_db = $insert_db."   ,'".$oc_MemberNo."'			";	//사내담당:담당자  
		$insert_db = $insert_db."   ,'".$oc_MainGroupName."'	";	//사내담당:관리부서
		$insert_db = $insert_db."   ,'".$oc_Address."'			";	//주소             
		$insert_db = $insert_db."   ,'".$oc_P_Name."'			";	//회사대표:성명    
		$insert_db = $insert_db."   ,'".$oc_P_Mobile."'			";	//회사대표:연락처  
		$insert_db = $insert_db."   ,'".$oc_ProviderNo."'		";	//*사업자번호      
		$insert_db = $insert_db."   ,'".$oc_Staff."'			";	//종업원규모       
		$insert_db = $insert_db."   ,'".$oc_BeginningDate."'	";	//최초영업일자     
		$insert_db = $insert_db."   ,'".$oc_Fortune."'			";	//회사자산:자산규모
		$insert_db = $insert_db."   ,'".$oc_SalesAmount."'		";	//회사자산:매출액  
		$insert_db = $insert_db."   ,'0'						";	//체납세금여부	   
		$insert_db = $insert_db."   ,'".$oc_RegisterDate."'		";	//회사등록일자     
		$insert_db = $insert_db."   ,'".$date_today4."'			";	//업데이트일자     
		$insert_db = $insert_db."   ,'".$memberID."'			";	//업데이트유저     
		$insert_db = $insert_db."   ,'0'						";	//숨김처리         
		$insert_db = $insert_db." )								";
		/* ------------------------------------------------------------------------------- */
	//////////////////////////////////
	mysql_query($insert_db,$db);
	//////////////////////////////////
	//mysql_close($db);
	//////////////////////////////////
		/*----------------------------------------*/
	}  //InsertDB() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function DeleteDB()	//삭제
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $db;
		/*-----------------------------*/
		$content_id			= (int)$_POST['content_id'];	//컨텐츠PK
		/*-----------------------------*/
		$delete_query = "DELETE FROM outside_cooperation_tbl WHERE no = '".$content_id."'";
		/*-----------------------------*/
		$result = mysql_query($delete_query);
		if($result){
			echo "1";	//삭제 성공
		}else{
			echo "2";	//삭제 실패
		}
		/*-----------------------------*/
	}  //DeleteDB() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function ConfirmPw()	//비밀번호 확인
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $db;

		$content_id	= (int)$_GET['content_id'];	//컨텐츠PK
		$depart_pw	= $_GET['depart_pw'];		//비밀번호
		/*---------------------------------------*/
		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT G.id, G.pass		";
		$sql=$sql."	FROM					";
		$sql=$sql."		group_board_tbl G	";
		$sql=$sql."	WHERE					";
		$sql=$sql."	G.id='".$content_id."'	";
		$sql=$sql."	AND						";
		$sql=$sql."	G.pass='".$depart_pw."'	";
		//echo $sql;
		/*---------------------------------------*/
		$re = mysql_query($sql,$db);
		/*-----------------------------*/
		$count = mysql_num_rows($re);

		if($count>0){//비밀번호 OK
			echo "1"; 

		}else{ //비밀번호 오류
			echo "2"; 	//

		}//if End

	}  //ConfirmPw() End
	/* ***************************************************************************************** */


	/* ***************************************************************************************** */
	function ValueTest()//
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		$GroupCode =(int)$GroupCode;
		
		global $GroupName;
		global $date_today;		// 오늘날짜 년월일       : yyyy-mm-dd
		global $date_today4;	// 오늘날짜 년월일 시분초: yyyy-mm-dd-ss 시 분 초

		global $db;

		/*---------------------------------------*/
		//$content_id	= (int)$_GET['content_id'];
		/*---------------------------------------*/
		$ocs_num		= $_POST['content_id'];		//테이블PK       
		/* -------------------------------------------------- */
		$ocs_company_no = $_POST['ocs_company_no'];	//협력업체 코드  
		$ocs_wdate		= $_POST['ocs_wdate'];		//등록/수정일자  
		$ocs_writer		= $_POST['ocs_writer'];		//등록자 사원번호
		$ocs_ProjectCode= $_POST['ocs_ProjectCode'];//프로젝트코드   
		/* -------------------------------------------------- */
		$ocs_cv1		= $_POST['ocs_cv1'];		//공통평가점수01 
		$ocs_cv2		= $_POST['ocs_cv2'];		//공통평가점수02 
		$ocs_cv3		= $_POST['ocs_cv3'];		//공통평가점수03 
		$ocs_cv4		= $_POST['ocs_cv4'];		//공통평가점수04 
		$ocs_cv5		= $_POST['ocs_cv5'];		//공통평가점수05 
		$ocs_cv6		= $_POST['ocs_cv6'];		//공통평가점수06 
		$ocs_cv7		= $_POST['ocs_cv7'];		//공통평가점수07 
		/* -------------------------------------------------- */
		$ocs_tv1		= $_POST['ocs_tv1'];		//기술평가점수01 
		$ocs_tv2		= $_POST['ocs_tv2'];		//기술평가점수02 
		$ocs_tv3		= $_POST['ocs_tv3'];		//기술평가점수03 
		$ocs_tv4		= $_POST['ocs_tv4'];		//기술평가점수04 
		$ocs_tv5		= $_POST['ocs_tv5'];		//기술평가점수05 
		$ocs_tv6		= $_POST['ocs_tv6'];		//기술평가점수06 
		$ocs_tv7		= $_POST['ocs_tv7'];		//기술평가점수07 
		$ocs_tv8		= $_POST['ocs_tv8'];		//기술평가점수08 
		$ocs_tv9		= $_POST['ocs_tv9'];		//기술평가점수09 
		$ocs_tv10		= $_POST['ocs_tv10'];		//기술평가점수10 
		$ocs_tv11		= $_POST['ocs_tv11'];		//기술평가점수11 
		/* -------------------------------------------------- */
		$ocs_writerinfo = "";
		$ocs_total = "";
		$ocs_cvsum = "";
		$ocs_tvsum = "";

echo "<br>-ocs_num			=====".$ocs_num;         
echo "<br>-ocs_company_no 	=====".$ocs_company_no;  
echo "<br>-ocs_wdate		=====".$ocs_wdate;       
echo "<br>-ocs_writer		=====".$ocs_writer;      
echo "<br>-ocs_ProjectCode	=====".$ocs_ProjectCode; 
echo "<br>-ocs_cv1			=====".$ocs_cv1;         
echo "<br>-ocs_cv2			=====".$ocs_cv2;         
echo "<br>-ocs_cv3			=====".$ocs_cv3;         
echo "<br>-ocs_cv4			=====".$ocs_cv4;         
echo "<br>-ocs_cv5			=====".$ocs_cv5;         
echo "<br>-ocs_cv6			=====".$ocs_cv6;         
echo "<br>-ocs_cv7			=====".$ocs_cv7;         
echo "<br>-ocs_tv1			=====".$ocs_tv1;         
echo "<br>-ocs_tv2			=====".$ocs_tv2;         
echo "<br>-ocs_tv3			=====".$ocs_tv3;         
echo "<br>-ocs_tv4			=====".$ocs_tv4;         
echo "<br>-ocs_tv5			=====".$ocs_tv5;         
echo "<br>-ocs_tv6			=====".$ocs_tv6;         
echo "<br>-ocs_tv7			=====".$ocs_tv7;         
echo "<br>-ocs_tv8			=====".$ocs_tv8;         
echo "<br>-ocs_tv9			=====".$ocs_tv9;         
echo "<br>-ocs_tv10		=====".$ocs_tv10;        
echo "<br>-ocs_tv11		=====".$ocs_tv11;         
		$this->smarty->display("intranet/common_contents/work_pmResister/valueTest.tpl");

	}  //ValueTest() End
	/* ------------------------------------------------------------------------------ */







}//class  End
/* ****************************************************************************************************************** */
?>
