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
		$memberID	=	$_GET['memberID'];
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
		$searchStr   = ($_GET['searchStr']==""?"":$_GET['searchStr']);			//프로젝트명,발주처 검색값
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
			if($str_val02==1){
				$add_where01 = " AND WorkStatus in('수행중','수행중지') ";
			}elseif($str_val02==2){
				$add_where01 = " AND WorkStatus in('수주활동중') ";
			}elseif($str_val02==3){
				$add_where01 = " AND WorkStatus in('준공완료') ";
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

			}else{//검색어 없음
				$add_where02 = "";
			}//if End
		/*-----------------------------------------------*/
		$sql_count  = "select COUNT(*) CNT from project_tbl WHERE ProjectCode like '%".$str_val01."%' ".$add_where01.$add_where02;
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
		$sql=$sql."	ProjectCode like '%".$str_val01."%'					";//
		/*-----------------------------------------------*/
		$sql=$sql.$add_where01;
		$sql=$sql.$add_where02;
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
		array_push($query_data,$re_row);
	} //while End
	/*-----------------------------------------------*/
		if ($_SESSION['auth_business']){//권한 : 사업
		//echo "트루";
			$auth_business = "1";
		}else{
		//echo "펄스";
			$auth_business = "0";
		}//if End
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
		/* * **********************************************************************
		*	$sql=$sql."	,P.oldProjectCode    	as p_oldProjectCode    	";//용역_
		*	$sql=$sql."	,P.oldProjectCode2   	as p_oldProjectCode2   	";//용역_
		*	$sql=$sql."	,P.ContractDate      	as p_ContractDate      	";//용역_
		*	$sql=$sql."	,P.ContractStart     	as p_ContractStart     	";//용역_
		*	$sql=$sql."	,P.ContractEnd       	as p_ContractEnd       	";//용역_
		*	$sql=$sql."	,P.ContractPeriod    	as p_ContractPeriod    	";//용역_
		*	$sql=$sql."	,P.OrgContractPayment	as p_OrgContractPayment	";//용역_
		*	$sql=$sql."	,P.Payment           	as p_Payment           	";//용역_
		*	$sql=$sql."	,P.Vat               	as p_Vat               	";//용역_
		*	$sql=$sql."	,P.CommonCompany     	as p_CommonCompany     	";//용역_
		*	$sql=$sql."	,P.ContractRatio     	as p_ContractRatio     	";//용역_
		*	$sql=$sql."	,P.ActualityRatio    	as p_ActualityRatio    	";//용역_
		*	$sql=$sql."	,P.MainGroup         	as p_MainGroup         	";//용역_
		*	$sql=$sql."	,P.ContractSeal      	as p_ContractSeal      	";//용역_
		*	$sql=$sql."	,P.Note              	as p_Note              	";//용역_
		*	$sql=$sql."	,P.KindOrderCompany  	as p_KindOrderCompany  	";//용역_
		*	$sql=$sql."	,P.Part              	as p_Part              	";//용역_
		*	$sql=$sql."	,P.DesignStep        	as p_DesignStep        	";//용역_
		*	$sql=$sql."	,P.UpdateDate        	as p_UpdateDate        	";//용역_
		*	$sql=$sql."	,P.UpdateUser        	as p_UpdateUser        	";//용역_
		*	$sql=$sql."	,P.year              	as p_year              	";//용역_
		*	$sql=$sql."	,P.Businesstype      	as p_Businesstype      	";//용역_
		*	$sql=$sql."	,P.Outside           	as p_Outside           	";//용역_
		*	$sql=$sql."	,P.StopDate          	as p_StopDate          	";//용역_
		* * ********************************************************************** */

	}  //PageList() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function ViewPage()//상세보기 페이지로 이동
	{
		global $MemberNo;
		global $memberID;
		global $korName;
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
		global $date_today;
		/*---------------------------------------*/
		global $db;
		/*---------------------------------------*/
		$content_id	= $_GET['content_id'];
		//echo "01::".$content_id."<br>"; 
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

		/*-----------------------------*/
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

		$getPercent = ($CollectionPaymentSum / $per_ContractPayment)*100;

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
	function PageList04()// [페이지이동] 관리대장 -> 외주비 지급내역서
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $date_today;
		/*---------------------------------------*/
		global $db;
		/*---------------------------------------*/
		$content_id	= $_GET['content_id'];
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
		$sql=$sql."	ORDER BY OOC.SuperviseGroup DESC ,OOC.ContractAmount DESC	";
		/*---------------------------------------*/
		/////////////////
//echo "01::".$sql."<br>"; 
		/////////////////
		/*-----------------------------*/
		$re = mysql_query($sql,$db);
		/*-----------------------------*/
		$cnt=0;
		while($re_row = mysql_fetch_array($re)) {

			//$re_row[ooc_TaxFree]
			
				$re_row[ooc_DetailPart_short] = utf8_strcut($re_row[ooc_DetailPart],7,'..');
				$re_row[ooc_Company_short] = utf8_strcut($re_row[ooc_Company],9,'..');

				

			$ooc_ContractAmount = $re_row[ooc_ContractAmount];//외주계약금액
			if( $re_row[ooc_TaxFree]==1){ //비과세
				  ///////////////////////////////
			}else{ //과세
				$re_row[ooc_ContractAmount]=$ooc_ContractAmount/1.1; //외주계약금액(VAT제외)
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
				$re_row[ooc_DisbursePaymentSum] = $ooc_DisbursePaymentSum/1.1; //외주지급액합계(VAT제외)
				$ooc_DisbursePaymentSum         = $ooc_DisbursePaymentSum/1.1;
			}//if End

		$ooc_getPercent = ($ooc_DisbursePaymentSum / $ooc_ContractAmount)*100;
		$re_row[ooc_getPercent] = $ooc_getPercent;

		//$ch_CollectionPaymentSum_delTax  = number_format($DisbursePaymentSum);

		//최근수금일
		$sql05 = "select max(DisburseDate) maxDate from outside_pay_disbursement_tbl where ProjectCode = '".$content_id."' and Company = '".$re_row[ooc_Company]."' and Part = '".$re_row[ooc_Part]."' and DetailPart = '".$re_row[ooc_DetailPart]."' and DisburseDate > '1950-01-01' ";
		$result05 = mysql_query($sql05,$db);
		$ooc_maxDate = mysql_result($result05,0,"maxDate"); //최근수금일자
		$re_row[ooc_maxDate] = $ooc_maxDate;


			array_push($query_data,$re_row);
			$cnt++;
		} //while End
		/*---------------------------------------*/

		/*-----------------------------*/
		//프로젝트 닉네임  
		$ProjectNickname = projectToColumn($content_id,"ProjectNickname");
		//발주처 닉네임  
		$OrderNickname   = projectToColumn($content_id,"OrderNickname");
		//시작일자  
		$ContractStart   = projectToColumn($content_id,"ContractStart");
		//종료일자  
		$ContractEnd   = projectToColumn($content_id,"ContractEnd");
		/*-----------------------------*/
		//용역 총계약금액 
		$ContractPayment = projectToColumn($content_id,"ContractPayment");//VAT포함
		$ContractPayment_delTax = $ContractPayment/1.1; //VAT제외
		$ch_ContractPayment_delTax = number_format($ContractPayment_delTax);
		/*-----------------------------*/
		//수금액합계
		$sql03 = "select sum(CollectionPayment) CollectionPaymentSum from collectionpayment_tbl where ProjectCode='".$content_id."' and (CollectionDate > '1980-01-01') ";
		$result02 = mysql_query($sql03,$db);
		$CollectionPaymentSum = mysql_result($result02,0,"CollectionPaymentSum"); //VAT포함
		$CollectionPaymentSum_delTax = $CollectionPaymentSum/1.1; //VAT제외
		$ch_CollectionPaymentSum_delTax  = number_format($CollectionPaymentSum_delTax);

		$getPercent = ($CollectionPaymentSum / $ContractPayment)*100;
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


		$this->smarty->assign('ContractPayment',$ch_ContractPayment_delTax);//용역 총계약금액 
		$this->smarty->assign('CollectionPaymentSum',$ch_CollectionPaymentSum_delTax);//수금액합계
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
	function PageList033()// 특정업체 외주 평가현황 리스트
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $date_today;
		global $db;
		/*---------------------------------------*/
		$content_id	= $_GET['content_id'];
		/*---------------------------------------*/
		$query_data = array(); 
		//echo "01::".$content_id."<br>"; 
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
		$sql=$sql."	OCS.company_no ='".$content_id."'                   ";
		$sql=$sql."	AND                                                 ";
		$sql=$sql."	OCS.del_flag <> '1'									";  //삭제 플래그 default:0,  삭제:1, 
		$sql=$sql."	ORDER BY  OCS.ProjectCode DESC, OCS.wdate DESC      ";
		/*---------------------------------------*/
		/////////////////
		//echo "01::<br>".$sql."<br>"; 
		/////////////////
			/*-----------------------------*/
			$re = mysql_query($sql,$db);
			/*-----------------------------*/
			while($re_row = mysql_fetch_array($re)) {
				$re_row[ocs_total]  = $re_row[ocs_cvsum]+$re_row[ocs_tvsum]; //공통점수+기술점수합계(x/100)
				/*----------------------------------------*/
				$ocs_writer  = $re_row[ocs_writer];
				$re_row[ocs_writerName] = MemberNo2Name($ocs_writer);//return 사원명
				$re_row[ocs_GroupName] = memberNoToGroupName($ocs_writer);//return 소속부서명
				$re_row[ocs_PositionName] = memberNoToPositionName($ocs_writer);//return 직위명
				/*----------------------------------------*/
				$re_row[status]="";
				/*----------------------------------------*/
				if($re_row[ocs_cvsum]<15){
					$re_row[status]="공통:미달";

				}else if($re_row[ocs_tvsum]<50){
						$re_row[status]="기술:미달";
				}else{
					if($re_row[ocs_total]<65){
						$re_row[status]="미달";
					}else if($re_row[ocs_total]<=80){
						$re_row[status]="보통";
					}else if($re_row[ocs_total]<=90){
						$re_row[status]="양호";
					}else if($re_row[ocs_total]<=100){
						$re_row[status]="매우양호";
					}
				}
				/*----------------------------------------*/
				array_push($query_data,$re_row);
				/*----------------------------------------*/
			} //while End
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
		$this->smarty->assign('date_today',$date_today);
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_pmResister/listPage03.tpl");
		/*----------------------------------------*/
	}  //PageList03() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function ViewPage03()// 특정업체 외주 평가현황 : 상세보기 페이지로 이동
	{
		global $MemberNo;
		global $memberID;
		global $korName;
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
	function UpdatePage()	//수정페이지로 이동
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		global $db;
		/*--------------------*/
		global $GroupCode;
		$GroupCode = (int)$GroupCode;
		global $GroupName;
		/*--------------------*/
		global $date_today;
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
		$sql=$sql."	,OC.no				as oc_no				";//PK
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
	
				$oc_MemberNo = $re_row[oc_MemberNo];
				$re_row[oc_MemberName] = MemberNo2Name($oc_MemberNo);//return 사원명

				/*사업자번호 ----------------------------------*/
				$divfile1 = explode("-",$re_row[oc_ProviderNo]);
				$divnum1  = count($divfile1);
				for($i=0,$j=1;$i<$divnum1;$i++,$j++){
					$re_row[oc_ProviderNo.$j] = $divfile1[$i];
				}//for End
				/*회사대표:연락처 ----------------------------------*/
				$divfile2 = explode("-",$re_row[oc_P_Mobile]);
				$divnum2  = count($divfile2);
				for($i=0,$j=1;$i<$divnum2;$i++,$j++){
					$re_row[oc_P_Mobile.$j] = $divfile2[$i];
				}//for End
				/*업체:전화번호 ----------------------------------*/
				$divfile3 = explode("-",$re_row[oc_Phone]);
				$divnum3  = count($divfile3);
				for($i=0,$j=1;$i<$divnum3;$i++,$j++){
					$re_row[oc_Phone.$j] = $divfile3[$i];
				}//for End
				/*업체:FAX ----------------------------------*/
				$divfile4 = explode("-",$re_row[oc_Fax]);
				$divnum4  = count($divfile4);
				for($i=0,$j=1;$i<$divnum4;$i++,$j++){
					$re_row[oc_Fax.$j] = $divfile4[$i];
				}//for End
				/*---------------------------------------------*/
				array_push($query_data,$re_row);
			} //while End
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->DepartKind();
		$this->CpCompanyKind();
		$this->smarty->assign('query_data',$query_data);
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
		$GroupCode =(int)$GroupCode;
		
		global $GroupName;
		global $date_today;		// 오늘날짜 년월일       : yyyy-mm-dd
		global $date_today4;	// 오늘날짜 년월일 시분초: yyyy-mm-dd-ss 시 분 초

		global $db;
		/*---------------------------------------*/
		//$content_id	= (int)$_GET['content_id'];
		/*---------------------------------------*/
		$oc_no				= $_POST['oc_no'];				//*업체명
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
		$update_db =			" UPDATE										"; 
		$update_db = $update_db." Outside_cooperation_tbl SET					";
		$update_db = $update_db."  Company			= '".$oc_Company."'			";	//*업체명           
		$update_db = $update_db." ,CompanyNicName	= '".$oc_CompanyNicName."'	";	//약칭              
		$update_db = $update_db." ,Part				= '".$oc_Part."'			";	//*분야             
		$update_db = $update_db." ,DetailPart		= '".$oc_DetailPart."'		";	//세부공종          
		$update_db = $update_db." ,Phone			= '".$oc_Phone."'			";	//업체:전화번호     
		$update_db = $update_db." ,Fax				= '".$oc_Fax."'				";	//업체:FAX          
		$update_db = $update_db." ,Name				= '".$oc_Name."'			";	//업체:담당자명     
		$update_db = $update_db." ,MemberNo			= '".$oc_MemberNo."'		";	//사내담당:담당자   
		$update_db = $update_db." ,MainGroup		= '".$oc_MainGroupName."'	";	//사내담당:관리부서 
		$update_db = $update_db." ,Address			= '".$oc_Address."'			";	//주소               
		$update_db = $update_db." ,P_Name			= '".$oc_P_Name."'			";	//회사대표:성명      
		$update_db = $update_db." ,P_Mobile			= '".$oc_P_Mobile."'		";	//회사대표:연락처    
		$update_db = $update_db." ,ProviderNo		= '".$oc_ProviderNo."'		";	//*사업자번호        
		$update_db = $update_db." ,Staff			= '".$oc_Staff."'			";	//종업원규모         
		$update_db = $update_db." ,BeginningDate	= '".$oc_BeginningDate."'	";	//최초영업일자      
		$update_db = $update_db." ,Fortune			= '".$oc_Fortune."'			";	//회사자산:자산규모 
		$update_db = $update_db." ,SalesAmount		= '".$oc_SalesAmount."'		";	//회사자산:매출액   
		$update_db = $update_db." ,TaxArrear		= '0'						";	//체납세금여부	    
		$update_db = $update_db." ,RegisterDate		= '".$oc_RegisterDate."'	";	//회사등록일자      
		$update_db = $update_db." ,UpdateDate		= '".$date_today4."'		";	//업데이트일자      
		$update_db = $update_db." ,UpdateUser		= '".$memberID."'			";	//업데이트유저      
		$update_db = $update_db." ,ListDisplay		= '0'						";	//숨김처리          
		$update_db = $update_db." WHERE											";
		$update_db = $update_db." no='".$oc_no."'								"; //PK
		/***********************************************************************************/
		//////////////////////////////////
		//echo $update_db;
		mysql_query($update_db,$db);
		//////////////////////////////////
		/*----------------------------------------*/
	}  //UpdateDB() End
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
