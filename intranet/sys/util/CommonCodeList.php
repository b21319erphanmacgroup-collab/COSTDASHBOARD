<?php
/***************************************
 * 프로젝트 검색
 * ------------------------------------
 ****************************************/
include "../../../SmartyConfig.php";

$this_year=date("Y");
$this_month=date("m");

extract($_REQUEST);
class CommonCodeList {
	var $smarty;
	function CommonCodeList($smarty)
	{
		$this->smarty=$smarty;
		$this->oracle=new OracleClass($smarty);
		
	}
	
	
	//부서권한
	function QueryDeptList($mode,$userdept,$userid,$value_name,$output_type="")
	{
		$allmode=$this->HangleEncodeUTF8_EUCKR("전체");
		switch($mode)
		{
			case "공통부서":
				$azsql="
				 SELECT DEPT_CODE as code,
						 DEPT_NAME as name,
						 DEPT_CODE AS ORDERS
					FROM SM_CODE_DEPT
				 WHERE USE_YN = 'Y'
					 AND COMPANY_CODE = '11'
					 AND DEPT_CODE LIKE F_PM_GET_AUTH_DEPT( COMPANY_CODE, '%','$userdept','$userid')
					 
				 UNION ALL
				 
				 SELECT CD.DEPT_CODE,
						 CD.DEPT_NAME,
						 CD.DEPT_CODE AS ORDERS
					FROM SM_CODE_DEPT CD,
						 SM_CODE_HEADQUATER CH
				 WHERE CD.COMPANY_CODE = CH.COMPANY_CODE
					 AND CD.HEADQUATER_CODE = CH.HEADQUATER_CODE
					 AND CD.USE_YN = 'Y'
					 AND CD.COMPANY_CODE = '11'
					 AND CD.DEPT_CODE <> '$userdept'
					 AND CH.HEADQUATER_CODE = F_PM_GET_AUTH_DEPT_2( CD.COMPANY_CODE, '%', '$userdept','$userid')
					 
				 UNION ALL
				 
				 SELECT '%',
						 '$allmode',
						 '00000'
					FROM DUAL
				 WHERE '%' = F_PM_GET_AUTH_DEPT( '11', '%', '$userdept','$userid')
				ORDER BY ORDERS";
				
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "사업부서":  //수금예상현황
				$azsql="
			 SELECT DEPT_CODE as code, DEPT_NAME as name
			   FROM SM_CODE_DEPT
			  WHERE USE_YN = 'Y'
				AND F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3')
				AND COMPANY_CODE = '11'
				
				AND DEPT_CODE LIKE F_PM_GET_AUTH_DEPT( COMPANY_CODE, '%','$userdept','$userid')
				
			 UNION ALL
			 
			 SELECT CD.DEPT_CODE as code,
					CD.DEPT_NAME
			   FROM SM_CODE_DEPT CD,
			        SM_CODE_HEADQUATER CH
			 WHERE CD.COMPANY_CODE = CH.COMPANY_CODE
			   AND CD.HEADQUATER_CODE = CH.HEADQUATER_CODE
			   AND CD.USE_YN = 'Y'
			   AND CD.COMPANY_CODE = '11'
			   AND CD.DEPT_CODE <> '$userdept'
			   AND CH.HEADQUATER_CODE = F_PM_GET_AUTH_DEPT_2( CD.COMPANY_CODE, '%', '$userdept','$userid')
			   
			 UNION ALL
			 
			 SELECT '%',
					 '$allmode'
			   FROM DUAL
			  WHERE '%' = F_PM_GET_AUTH_DEPT( '11', '%', '$userdept','$userid')
			  
			 ORDER BY CODE";
				//echo $azsql;
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
		}
		
	}
	
	
	
	
	
	function QueryCodeList($mode,$value_name,$output_type,$user_dept="")
	{
		switch($mode)
		{
			case "연말정산구분":
				$query_data = array();
				$item=array('CODE'=>'N','NAME'=>"연말정산");
				array_push($query_data,$item);
				$item=array('CODE'=>'Y','NAME'=>"중도정산");
				array_push($query_data,$item);
				//$item=array('CODE'=>'C','NAME'=>"소속변경");
				//array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
			case "결재문서구분":
				$query_data = array();
				$item=array('CODE'=>'R2','NAME'=>"물품구매요구서");
				array_push($query_data,$item);
				$item=array('CODE'=>'P1','NAME'=>"외주기성검토(기술)");
				array_push($query_data,$item);
				$item=array('CODE'=>'P2','NAME'=>"외주기성검토(외주)");
				array_push($query_data,$item);
				$item=array('CODE'=>'P5','NAME'=>"인쇄대금지불의뢰서"); //인쇄대금지불의뢰서 P5/P6/P7 사용
				array_push($query_data,$item);
				$item=array('CODE'=>'P9','NAME'=>"복사외주의뢰서");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
			case "YN":
				$query_data = array();
				$item=array('CODE'=>'Y','NAME'=>"Y");
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>"N");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
			case "YN2":
				$query_data = array();
				$item=array('CODE'=>'Y','NAME'=>"");
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>"N");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
			case "사업장코드":
				$azsql ="
							select   company_code	   		as code, company_name			as name
							from sm_code_company
							";
				//echo $azsql;
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "거래처sm": //170901
				$azsql ="select cust_code as code , cust_name as name from	sm_code_cust";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "사업구분":
				$azsql ="select trim(class_code) as code, trim(class_name) as name from vw_cs_code_class_proj_tag where company_code = 11 order by sort_order asc";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "발주구분":
				$azsql ="select class_code as code, class_name as name from vw_cs_code_class_order_method where company_code =11 order by sort_order";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "진행구분":
				$azsql ="select class_code as code, class_name as name from vw_cs_code_class_completion order by sort_order";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "계획구분":
				$azsql ="select class_code as code, class_name as name from vw_cs_code_class_plan_tag where company_code = 11 order by sort_order asc";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "수주추진진행상태":
				$azsql ="select class_code as code, class_name as name from cs_code_class where class_tag = 'VB' order by sort_order ";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "발주처구분":
				$azsql ="select class_code as code, class_name as name from vw_cs_code_class_order_class order by sort_order asc";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "사업부서":
				$azsql ="select dept_code as code, dept_name as name from sm_code_dept where f_cs_dept_tag(dept_code) in ( '1','2','3') and use_yn = 'Y' and company_code = 11 order by dept_code";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "사업부서2":
				$azsql ="select dept_code as code, (' [' || dept_code|| '] ' || dept_name) as name from sm_code_dept where f_cs_dept_tag(dept_code) in ( '1','2','3') and use_yn = 'Y' and company_code = 11 order by dept_code";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "기준년월":
				$azsql ="select substr(max(yyyymm),0,4)||'-'||substr(max(yyyymm),5,2) as name from pm_cost_evaldept";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "준공여부":
				$azsql ="select class_code as code, class_name as name,1 as sort_tag,sort_order from vw_cs_code_class_completion where company_code = '11' ";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "진행상태":
				$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE='11' AND SYS_ID='PM' AND ETC_DIV='11' AND HIDE_YN='N' AND USE_YN='Y'";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "전문공정":
				$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE='11' AND SYS_ID='PM' AND ETC_DIV='03' AND HIDE_YN='N' AND USE_YN='Y' order by ORDERS";
				//echo $azsql;
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "전문공정ALL":
				$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE='11' AND SYS_ID='PM' AND ETC_DIV='03'  order by ORDERS";
				//echo $azsql;
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "계약구분":
				$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE='11' AND SYS_ID='PM' AND ETC_DIV='09' AND HIDE_YN='N' AND USE_YN='Y' order by ORDERS";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "지급조건":
				$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE='11' AND SYS_ID='PM' AND ETC_DIV='13' AND HIDE_YN='N' AND USE_YN='Y' order by ORDERS";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "기성구분":
				$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name FROM VW_CS_CODE_CLASS_EXTABLISHED WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
				
			case "차수":
				$query_data = array();
				$item=array('CODE'=>'','NAME'=>"");
				array_push($query_data,$item);
				$item=array('CODE'=>'0','NAME'=>"당초");
				array_push($query_data,$item);
				$item=array('CODE'=>'00','NAME'=>"당초");
				array_push($query_data,$item);
				for($i=1;$i<50;$i++){
					if($i < 10){ $temp = "0".$i; }else{ $temp = $i; }
					$item=array('CODE'=>$temp,'NAME'=>$temp."차");
					array_push($query_data,$item);
				}//for
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "부서구분":
				$azsql ="SELECT VALID_VALUE_CODE as code, VALID_VALUE_NAME as name FROM  AM_CODE_VALIDATION WHERE  USE_YN = 'Y' AND VALIDATION_CODE = '60' ORDER BY VALID_VALUE_NAME ";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
			case "부서구분(프로젝트)":
				$azsql ="SELECT VALID_VALUE_CODE as code, VALID_VALUE_NAME as name FROM  AM_CODE_VALIDATION WHERE  USE_YN = 'Y' AND VALIDATION_CODE = '59' ORDER BY VALID_VALUE_NAME ";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
			case "본부코드":
				$azsql ="SELECT HEADQUATER_CODE as code, HEADQUATER_NAME as name  FROM  SM_CODE_HEADQUATER WHERE COMPANY_CODE ='11' ORDER BY HEADQUATER_CODE";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
			case "예산실적":
				$query_data = array();
				$item=array('CODE'=>'Y','NAME'=>"Y");
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>"N");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
				
			case "사업종류":
				$azsql ="SELECT VALID_VALUE_CODE as code, VALID_VALUE_NAME as name FROM  AM_CODE_VALIDATION WHERE  USE_YN = 'Y' AND VALIDATION_CODE = '61' ORDER BY VALID_VALUE_CODE";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
			case "원가코드":
				$query_data = array();
				$item=array('CODE'=>'51','NAME'=>"용역원가");
				array_push($query_data,$item);
				$item=array('CODE'=>'11','NAME'=>"일반관리비");
				array_push($query_data,$item);
				$item=array('CODE'=>'52','NAME'=>"임대원가");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "구분"://Menu_Auth_01 구분
				$query_data = array();
				$item=array('CODE'=>'Y','NAME'=>"사용");
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>"&nbsp");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "시스템"://Menu_Auth_02 시스템
				$query_data = array();
				$item=array('CODE'=>'AM','NAME'=>"회계자금");
				array_push($query_data,$item);
				$item=array('CODE'=>'CS','NAME'=>"수주영업");
				array_push($query_data,$item);
				$item=array('CODE'=>'HR','NAME'=>"인력정보");
				array_push($query_data,$item);
				$item=array('CODE'=>'PM','NAME'=>"프로젝트");
				array_push($query_data,$item);
				$item=array('CODE'=>'PQ','NAME'=>"PQ관리");
				array_push($query_data,$item);
				$item=array('CODE'=>'RB','NAME'=>"기타업무");
				array_push($query_data,$item);
				$item=array('CODE'=>'RM','NAME'=>"자산관리");
				array_push($query_data,$item);
				$item=array('CODE'=>'SM','NAME'=>"공통관리");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "유형"://Menu_Auth_04 유형
				$query_data = array();
				$item=array('CODE'=>'%','NAME'=>"전체");
				array_push($query_data,$item);
				$item=array('CODE'=>'E','NAME'=>"등록");
				array_push($query_data,$item);
				$item=array('CODE'=>'Q','NAME'=>"조회");
				array_push($query_data,$item);
				$item=array('CODE'=>'R','NAME'=>"출력");
				array_push($query_data,$item);
				$item=array('CODE'=>'S','NAME'=>"집계");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "기본"://Menu_Auth_04 기본
				$query_data = array();
				$item=array('CODE'=>'Y','NAME'=>"Yes");
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>"No");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "기본2"://PersonLicense_Screen01
				$query_data = array();
				$item=array('CODE'=>'Y','NAME'=>"Yes");
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>"NO");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "기본분류"://Menu_Auth_04 기본분류
				$azsql ="SELECT VALID_VALUE_CODE as code, VALID_VALUE_NAME as name FROM  AM_CODE_VALIDATION WHERE  USE_YN = 'Y' AND VALIDATION_CODE = 'SM' ORDER BY VALID_VALUE_CODE";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
			case "사업장"://Menu_Auth_05 사업장
				$query_data = array();
				//$item=array('CODE'=>'11','NAME'=>"(주)삼안");
				$item=array('CODE'=>'11','NAME'=>"(주)바론");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "회사권한"://Menu_Auth_05 회사권한
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>"그룹마스터");
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>"회사마스터");
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>"일반사용자");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "인사기본정보"://급여탭 권한 '2'
				$query_data = array();
				//$item=array('CODE'=>'1','NAME'=>"미사용");
				//array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>"급여사용자");
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>"일반사용자");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "회계권한"://Menu_Auth_05 회계권한
				$query_data = array();
				$item=array('CODE'=>'Y','NAME'=>"회계마스터");
				array_push($query_data,$item);
				$item=array('CODE'=>'M','NAME'=>"부서마스터");
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>"일반사용자");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "구분2"://Person_Code_02 구분
				$query_data = array();
				$item=array('CODE'=>'%','NAME'=>"전체");
				array_push($query_data,$item);
				$item=array('CODE'=>'1','NAME'=>"부서");
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>"현장");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "구분3"://PersonCode_Screen_08 구분3
				$query_data = array();
				$item=array('CODE'=>'01','NAME'=>"한줄");
				array_push($query_data,$item);
				$item=array('CODE'=>'02','NAME'=>"여러줄");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "접수"://Person_Screen_02 구분
				$query_data = array();
				$item=array('CODE'=>'Y','NAME'=>"접수");
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>"미접수");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "문서찾기"://PersonAppointment_Screen05
				$query_data = array();
				$item=array('CODE'=>'no','NAME'=>"문서번호");
				array_push($query_data,$item);
				$item=array('CODE'=>'title','NAME'=>"문서제목");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "기술분야"://PersonLicense_Screen01
				$azsql ="SELECT tech_field_name as name, tech_field_code as code FROM hr_code_tech_field ORDER BY tech_field_code";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
			case "전문분야"://PersonLicense_Screen01
				$azsql ="SELECT spec_field_name as name, spec_field_code as code FROM hr_code_spec_field ORDER BY spec_field_code";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
			case "자격 및 학력"://PersonLicense_Screen01
				$azsql ="SELECT ref_code as code, ref_name as name FROM  hr_code_ref WHERE  ref_gbn_code='57' AND ref_code !='00' ORDER BY ref_name";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
				
			case "사용권한":
				$query_data = array();
				$item=array('CODE'=>'F','NAME'=>"전체");
				array_push($query_data,$item);
				$item=array('CODE'=>'I','NAME'=>"입력");
				array_push($query_data,$item);
				$item=array('CODE'=>'D','NAME'=>"삭제");
				array_push($query_data,$item);
				$item=array('CODE'=>'U','NAME'=>"수정");
				array_push($query_data,$item);
				$item=array('CODE'=>'P','NAME'=>"출력");
				array_push($query_data,$item);
				$item=array('CODE'=>'R','NAME'=>"조회");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "진행상태1":
				//20180220 이력관리_수주추진형황이력관리_진행상태
				$query_data = array();
				$item=array('CODE'=>'Y','NAME'=>"공고확정");
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>"미공고");
				array_push($query_data,$item);
				$item=array('CODE'=>'Z','NAME'=>"사업소멸");
				array_push($query_data,$item);
				$item=array('CODE'=>'S','NAME'=>"당사낙찰");
				array_push($query_data,$item);
				$item=array('CODE'=>'Q','NAME'=>"타사낙찰");
				array_push($query_data,$item);
				$item=array('CODE'=>'P','NAME'=>"계약");
				array_push($query_data,$item);
				$item=array('CODE'=>'A','NAME'=>"불참");
				array_push($query_data,$item);
				$item=array('CODE'=>'M','NAME'=>"미계약");
				array_push($query_data,$item);
				$item=array('CODE'=>'D','NAME'=>"유보");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "문서결재상태"://PersonLicense_Screen01
				$azsql ="SELECT doc_code as code, doc_codename as name FROM DOC_COMCODE WHERE com_code = '30' AND doc_codeyn = 'Y' ORDER BY 1";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
				
			case "부서99"://
				$azsql ="SELECT HR_CODE_DEPT.dept_code AS code,
						( HR_CODE_DEPT.dept_name || '  (' || HR_CODE_DEPT.dept_code || ')' ) AS NAME
						FROM HR_CODE_DEPT
						WHERE HR_CODE_DEPT.use_yn = 'Y'
						and DEPT_DIV_CODE = '1'
						ORDER BY  HR_CODE_DEPT.dept_div_code, HR_CODE_DEPT.dept_code";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
				
			case "알림구분":
				$azsql ="SELECT INFO_DIV_CODE as code, INFO_DIV_NAME as name FROM SM_INFO_DIV";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
			case "대상자99":
				$query_data = array();
				$item=array('CODE'=>'A','NAME'=>"전체");
				array_push($query_data,$item);
				$item=array('CODE'=>'U','NAME'=>"선택");
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
		}
	}
	
	
	//인력정보↔인사  : 2016/12/09
	function PersonQueryCode($mode,$value_name,$output_type="",$etc_param="")
	{
		$allmode=$this->HangleEncodeUTF8_EUCKR("전체");
		//		사용법
		// 		include "../../util/CommonCodeList.php";
		// 		$CommonCode=new CommonCodeList($this->smarty);
		// 		$CommonCode->PersonQueryCode("구분1","input_select_01","","");
		switch($mode)
		{
			case "연말정산구분":
				$query_data = array();
				$item=array('CODE'=>'N','NAME'=>"연말정산");
				array_push($query_data,$item);
				$item=array('CODE'=>'Y','NAME'=>"중도정산");
				array_push($query_data,$item);
				//$item=array('CODE'=>'C','NAME'=>"소속변경");
				//array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				break;
				
			case "연말정산_세부내역_연금저축_중기출자년도":
				$query_data = array();
				$item=array('CODE'=>'2016','NAME'=>'2016년');
				array_push($query_data,$item);
				$item=array('CODE'=>'2017','NAME'=>'2017년');
				array_push($query_data,$item);
				$item=array('CODE'=>'2018','NAME'=>'2018년');
				array_push($query_data,$item);
				$item=array('CODE'=>'2019','NAME'=>'2019년');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "연말정산_세부내역_연금저축_중기출자구분":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'조합');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'벤처');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "연말정산_소득공제신고서_특별기타_공제구분":
				$query_data = array();
				$item=array('CODE'=>'2','NAME'=>'특별공제');
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>'기타공제');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "연말정산_소득공제신고서_인적공제_출산입양":
				$query_data = array();
				$item=array('CODE'=>'N','NAME'=>'x');
				array_push($query_data,$item);
				$item=array('CODE'=>'1','NAME'=>'첫째');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'둘째');
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>'셋째이상');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "연말정산_소득공제신고서_인적공제_장애인":
				$query_data = array();
				$item=array('CODE'=>'N','NAME'=>'x');
				array_push($query_data,$item);
				$item=array('CODE'=>'1','NAME'=>'1');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'2');
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>'3');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "연말정산_소득공제신고서_인적공제_외국인":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'내국인');
				array_push($query_data,$item);
				$item=array('CODE'=>'9','NAME'=>'외국인');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "연말정산_소득공제신고서_인적공제_관계":
				$Array_etc_param = split(":",$etc_param);
				$azsql = "
					SELECT
						REF_CODE as code, REF_NAME as name
					FROM
						HR_CODE_REF
					WHERE
						REF_CODE != '00'
						AND
						REF_GBN_CODE = '50'
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				//echo $azsql;
				break;
				
			case "연말정산_세부내역_기부금유형":
				$azsql ="
					SELECT
						(A.REF_CODE||'-'||A.REF_NAME||'-'||A.REF_NAME2)	as code,
						A.REF_NAME as name
					FROM
					HR_CODE_REF A
					WHERE
					A.REF_CODE NOT IN ('00','31','30','50')
					AND	A.REF_GBN_CODE = '69'
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "연말정산_세부내역_기부금유형2":
				$azsql ="
				SELECT
					(A.REF_CODE||'-'||A.REF_NAME||'-'||A.REF_NAME2)	as code,
					A.REF_NAME as name
				FROM
				HR_CODE_REF A
				WHERE
				A.REF_CODE NOT IN ('00','31','30','50')
				AND	A.REF_GBN_CODE = '69'
			";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "연말정산_세부내역_보험종류":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'일반보장성보험');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'장애인전용보험');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "연말정산_세부내역_은행코드_multi":
				$azsql ="
				SELECT
				CONCAT( CONCAT(REF_CODE, '-') , REF_NAME) as code,
				'['||REF_CODE||'] '||REF_NAME as name
				FROM HR_CODE_REF
				WHERE REF_GBN_CODE = '75'
				AND REF_CODE != '00'
				ORDER BY 1
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "연말정산_교육구분":
				$Array_etc_param = split(":",$etc_param);
				$azsql = "
						SELECT REF_CODE  as code, REF_NAME  as name
						FROM HR_CODE_REF
						WHERE REF_GBN_CODE = '52' AND REF_CODE != '00' AND REF_CODE != '41' ORDER BY REF_CODE
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "연말정산_세부내역_공제대상_기부금_multi":
				$Array_etc_param = split(":",$etc_param);
				$azsql = "
						SELECT
						(
						a.item_code||'-'||(DECODE(a.relation_code,'0','1','3','2','4','3','5','3','1','4','2','4','6','5','7','6','8','6'))||'-'||a.name||'-'||a.RRN||'-'||a.FOREIGN_YN
						) as code,
						(
							a.name
							||' '||
							DECODE(
								DECODE(a.relation_code,'0','1','3','2','4','3','5','3','1','4','2','4','6','5','7','6','8','6')
								,'1','(본인)'
								,'2','(배우자)'
								,'3','(직계비속)'
								,'4','(직계존속)'
								,'5','(형제자매)'
								,'6','(그외)'
							)
						) as name
						
						FROM
						HR_YETA_SELF A
						WHERE
						A.COMPANY_CODE = '11'
						AND SUBSTR(A.WORK_YEAR,1,4)      = '$Array_etc_param[0]'
						AND A.MTA_YN  = '$Array_etc_param[1]'
						AND A.EMP_NO      = '$Array_etc_param[2]'
						AND A.RELATION_CODE  >= '0'
					";
				$azsql=$this->HangleEncodeUTF8_EUCKR($azsql);
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				//echo $azsql;
				break;
				
			case "연말정산_세부내역_공제대상_multi2":
				$Array_etc_param = split(":",$etc_param);
				$azsql = "
				SELECT
				CONCAT( CONCAT(a.item_code, '-') ,CONCAT(CONCAT(a.relation_code, '-'),a.name) )  as code
				FROM
				HR_YETA_SELF A
				WHERE
				A.COMPANY_CODE = '11'
				AND SUBSTR(A.WORK_YEAR,1,4)      = '$Array_etc_param[0]'
				AND A.MTA_YN  = '$Array_etc_param[1]'
				AND A.EMP_NO      = '$Array_etc_param[2]'
				AND A.RELATION_CODE  >= '0'
				order by 1
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				//echo $azsql;
				break;
				
			case "연말정산_세부내역_공제대상_multi":
				$Array_etc_param = split(":",$etc_param);
				$azsql = "
				SELECT
				CONCAT( CONCAT(a.item_code, '-') ,CONCAT(CONCAT(a.relation_code, '-'),a.name) ) as code,
				a.name as name
				FROM
				HR_YETA_SELF A
				WHERE
				A.COMPANY_CODE = '11'
				AND SUBSTR(A.WORK_YEAR,1,4)      = '$Array_etc_param[0]'
				AND A.MTA_YN  = '$Array_etc_param[1]'
				AND A.EMP_NO      = '$Array_etc_param[2]'
				AND A.RELATION_CODE  >= '0'
				
				order by 1
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				//echo $azsql;
				break;
				
			case "연말정산_세부내역_의료비_공제대상_multi":
				$Array_etc_param = split(":",$etc_param);
				$azsql = "
				SELECT
				(a.item_code||'-'||a.relation_code||'-'||a.RRN) as code,
				a.name as name
				FROM
				HR_YETA_SELF A
				WHERE
				A.COMPANY_CODE = '11'
				AND SUBSTR(A.WORK_YEAR,1,4)      = '$Array_etc_param[0]'
				AND A.MTA_YN  = '$Array_etc_param[1]'
				AND A.EMP_NO      = '$Array_etc_param[2]'
				AND A.RELATION_CODE  >= '0'
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				//echo $azsql;
				break;
				
			case "연말정산_세부내역_의료비_구분":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'본인.65세이상.장애인.건강보험산정특례자');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'그외 기본공제대상자');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "연말정산_세부내역_의료비_의료증빙코드":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'1.국세청장이제공하는의료비자료');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'2.국민건강보험공단의의료비부담명세서');
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>'3.진료비계산서,약제비계산서');
				array_push($query_data,$item);
				$item=array('CODE'=>'4','NAME'=>'4.장기요양급여비용명세서');
				array_push($query_data,$item);
				$item=array('CODE'=>'5','NAME'=>'5.기타의료비영수증');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "세부내역_신용카드_공제대상":
				$Array_etc_param = split(":",$etc_param);
				$azsql = "
					SELECT
						A.ITEM_CODE as code,
						A.NAME as name
					FROM
						HR_YETA_SELF A
					WHERE
							A.COMPANY_CODE = '11'
						AND SUBSTR(A.WORK_YEAR,1,4)      = '$Array_etc_param[0]'
						AND A.MTA_YN  = '$Array_etc_param[1]'
						AND A.EMP_NO      = '$Array_etc_param[2]'
						AND A.RELATION_CODE  >= '0'
				";
				//echo $azsql;
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "세부내역_신용카드_구분":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'신용카드(시장·교통분제외)');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'직불·선불카드(시장·교통분제외)');
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>'현금영수증(시장·교통분제외)');
				array_push($query_data,$item);
				$item=array('CODE'=>'4','NAME'=>'전통시장사용분');
				array_push($query_data,$item);
				$item=array('CODE'=>'5','NAME'=>'대중교통이용분');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "주택유형":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'단독주택');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'다가구');
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>'다세대주택');
				array_push($query_data,$item);
				$item=array('CODE'=>'4','NAME'=>'연립주택');
				array_push($query_data,$item);
				$item=array('CODE'=>'5','NAME'=>'아파트');
				array_push($query_data,$item);
				$item=array('CODE'=>'6','NAME'=>'오피스텔');
				array_push($query_data,$item);
				$item=array('CODE'=>'7','NAME'=>'고시원');
				array_push($query_data,$item);
				$item=array('CODE'=>'8','NAME'=>'기타');
				array_push($query_data,$item);
				
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "HR_CODE_REF":
				/*
				 * REF_GBN_CODE=14=양음구분
				 * REF_GBN_CODE=16=동거구분
				 * REF_GBN_CODE=14=양음구분
				 * REF_GBN_CODE=17=학력취득시점
				 * REF_GBN_CODE=24=교육판정
				 * REF_GBN_CODE=19=보험구분
				 * REF_GBN_CODE=21=우선순위
				 * REF_GBN_CODE=23=회화 : 상중하
				 * REF_GBN_CODE=44=중도, 퇴직 구분
				 * REF_GBN_CODE=36=정기승호일괄발령 기준월일(03-01, 07-01)
				 * REF_GBN_CODE=06=정기승호일괄발령 찾기(사번,성명)
				 *
				 */
				$azsql ="
				SELECT
				REF_CODE  as code,
				REF_NAME  as name
				FROM HR_CODE_REF
				WHERE REF_GBN_CODE = '$etc_param'
				AND REF_CODE != '00'
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "HR_CODE_REF1":
				//인력정보→연말정산→목등록
				$azsql ="SELECT ref_code as code, ref_name as name FROM hr_code_ref Where ref_code !='00' and ref_gbn_code= '70'";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
				//-------------------------------------
			case "시간선택":
				$query_data = array();
				for($i=0;$i<24;$i++){
					$item=array('CODE'=>sprintf('%02d',$i),'NAME'=>sprintf('%02d',$i));
					array_push($query_data,$item);
				}//for
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				//-------------------------------------
			case "분선택":
				$query_data = array();
				for($i=0;$i<60;$i++){
					$item=array('CODE'=>sprintf('%02d',$i),'NAME'=>sprintf('%02d',$i));
					array_push($query_data,$item);
				}//for
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				//-------------------------------------
			case "근태사유":
				$azsql ="
				SELECT
				LABSTA_REASON_CODE as code,
				LABSTA_REASON_CODE||'  '||LABSTA_REASON_NAME   as name
				FROM
				HR_CODE_REASON
				WHERE
				COMPANY_CODE = '11'
				--AND LABSTA_CODE  = AS_LABSTA_CODE
				ORDER BY
				LABSTA_REASON_NAME
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				//-------------------------------------
			case "근태코드":
				$azsql ="
					SELECT
						ETC_CODE as code,
						ETC_NAME as name,
						  NVL(ORDERS,0) ORDERS
					FROM
					RB_COMM_CODE
					WHERE
					COMPANY_CODE = '11'
					AND SYS_ID       = 'HR'
					AND ETC_DIV      = '01'
					AND USE_YN      = 'Y'
						
					ORDER BY
					1,2
				";
				
				// 				SELECT
				// 				ETC_CODE as code,
				// 				ETC_NAME as name,
				// 				NVL(ORDERS,0) ORDERS
				// 				FROM
				// 				RB_COMM_CODE
				// 				WHERE
				// 				COMPANY_CODE = '11'
				// 						AND SYS_ID       = 'HR'
				// 								AND ETC_DIV      = '01'
				// 										AND USE_YN      = 'Y'
				// 												UNION ALL
				// 												SELECT '%',
				// 												'$allmode',
				// 												0
				// 												FROM DUAL
				// 												ORDER BY
				// 												1,2
				
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				//-------------------------------------
			case "근태관리자":
				/*
				 * 0 = 비관리자
				 * 1 = 관리자
				 */
				$azsql ="
					SELECT COUNT(etc_code) as code, COUNT(etc_code) as name  FROM RB_COMM_CODE WHERE sys_id = 'HR' AND etc_div = '99' AND etc_code = '$etc_param'
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				//-------------------------------------
			case "사용자명":
				$azsql ="
				SELECT user_id as code,  user_name as name
				  FROM sm_auth_user
				 WHERE company_code='11'
				   AND dept_code = '$etc_param'
				   AND USE_YN = 'Y'
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				//-------------------------------------
			case "사용자명2":
				$azsql ="
				SELECT user_id as code,  user_name as name
				  FROM sm_auth_user
				 WHERE company_code='11'
				   AND dept_code = '$etc_param'
				   AND USE_YN = 'Y'
				order by name
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				//-------------------------------------
			case "지급공제수당항목":
				$azsql ="
				   SELECT
					  allow_code as code,
					  allow_name as name
				   FROM HR_CODE_ALLOW
				   ORDER BY allow_code
				   ";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "외국어종류":
				$azsql ="
				SELECT
				hr_code_flang.flang_code as code,
				hr_code_flang.flang_name as name
				FROM
				hr_code_flang
				ORDER BY
				hr_code_flang.flang_name
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "외국어자격":
				$azsql ="
				SELECT
				HR_CODE_FLICENSE.FLICENSE_CODE AS CODE,
				HR_CODE_FLICENSE.FLICENSE_NAME AS NAME
				FROM
				HR_CODE_FLICENSE
				ORDER BY
				HR_CODE_FLICENSE.FLICENSE_NAME
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "기술분야":
				$azsql ="
				SELECT
				HR_CODE_MANG_TECH.MANG_FIELD_CODE as code,
				HR_CODE_MANG_TECH.MANG_FIELD_NAME as name
				FROM HR_CODE_MANG_TECH
				ORDER BY
				HR_CODE_MANG_TECH.MANG_FIELD_NAME
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "자격등급":
				//초급/중급/고급/특급
				$azsql ="
				SELECT
				HR_CODE_MANG_LEVEL.MANG_LEVEL_CODE as code,
				HR_CODE_MANG_LEVEL.MANG_LEVEL_NAME as name
				FROM HR_CODE_MANG_LEVEL
				ORDER BY
				HR_CODE_MANG_LEVEL.MANG_LEVEL_CODE
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "경력구분":
				$azsql ="
				SELECT
				HR_CODE_CAREER_DIV.CAREER_DIV_CODE as code,
				HR_CODE_CAREER_DIV.CAREER_DIV_NAME as name
				FROM HR_CODE_CAREER_DIV
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "총괄사업장":
				$azsql ="
				SELECT
				BUSINESS_CODE as code,
				BUSINESS_NAME as name
				FROM
				SM_CODE_BUSINESS
				ORDER BY
				BUSINESS_NAME
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "관계처":
				$azsql ="
				SELECT
				HR_CODE_AFFILIATE.AFFILIATE_CODE as code,
				HR_CODE_AFFILIATE.AFFILIATE_NAME as name
				FROM
				HR_CODE_AFFILIATE
				ORDER BY
				HR_CODE_AFFILIATE.AFFILIATE_NAME
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "주거상황":
				$azsql ="
				SELECT
				hr_code_live_type.live_type_code as code,
				hr_code_live_type.live_type_name  as name
				FROM
				hr_code_live_type
				ORDER BY
				hr_code_live_type.live_type_code
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "상벌종류":
				$azsql ="
				SELECT
				RNP_CODE as code,
				DECODE(IN_OUT_DIV,'1','".$this->HangleEncodeUTF8_EUCKR('[대내]')."','2','".$this->HangleEncodeUTF8_EUCKR('[대외]')."')||RNP_NAME  as name
				FROM
				HR_CODE_RNP
				ORDER BY
				IN_OUT_DIV, RNP_NAME
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "보험회사":
				$azsql ="
				SELECT
				hr_code_insu_comp.insu_comp_code as code,
				hr_code_insu_comp.insu_comp_name as name
				FROM
				hr_code_insu_comp
				ORDER BY
				hr_code_insu_comp.insu_comp_name
			";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "군필종류":
				$azsql ="
				SELECT
				hr_code_end_army.end_army_code as code,
				hr_code_end_army.end_army_name as name
				FROM hr_code_end_army
				ORDER BY
				hr_code_end_army.end_army_name
			";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "제대구분":
				$azsql ="
				SELECT
				hr_code_discharge.discharge_code as code,
				hr_code_discharge.discharge_name as name
				FROM hr_code_discharge
				ORDER BY
				hr_code_discharge.discharge_name
			";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				
				break;
				
			case "역종":
				$azsql ="
				SELECT
				hr_code_cmss.cmss_code as code,
				hr_code_cmss.cmss_name as name
				FROM hr_code_cmss
				ORDER BY
				hr_code_cmss.cmss_name
			";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
				
				
				
			case "군별":
				$azsql ="
				SELECT
				hr_code_kind_army.kind_army_code as code,
				hr_code_kind_army.kind_army_name as name
				FROM hr_code_kind_army
				ORDER BY
				hr_code_kind_army.kind_army_name
			";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "계급":
				$azsql ="
				SELECT
				hr_code_army_class.class_code as code,
				hr_code_army_class.class_name as name
				FROM hr_code_army_class
				ORDER BY
				hr_code_army_class.class_name
			";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "병과":
				$azsql ="
				SELECT
				hr_code_arm.arm_code as code,
				hr_code_arm.arm_name as name
				FROM hr_code_arm
				ORDER BY
				hr_code_arm.arm_name
			";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
				
			case "여권종류":
				$azsql ="
				SELECT
				hr_code_passport_type.passport_type_code as code,
				hr_code_passport_type.passport_type_name as name
				FROM
				hr_code_passport_type
				ORDER BY
				hr_code_passport_type.passport_type_name
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "비자종류":
				$azsql ="
				SELECT
				hr_code_visa_div.visa_div_code as code,
				hr_code_visa_div.visa_div_name as name
				FROM
				hr_code_visa_div
				ORDER BY
				hr_code_visa_div.visa_div_name
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "국가코드":
				$azsql ="
				SELECT
				hr_code_nation.nation_code as code,
				hr_code_nation.nation_name as name
				FROM hr_code_nation
				ORDER BY hr_code_nation.nation_name
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "종교":
				$azsql ="
				SELECT
				hr_code_religion.religion_code as code,
				hr_code_religion.religion_name as name
				FROM hr_code_religion
				ORDER BY hr_code_religion.religion_name
				";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "가족관계":
				$azsql ="
					SELECT
					hr_code_relation.relation_code as code,
					hr_code_relation.relation_name as name
					FROM hr_code_relation
					ORDER BY hr_code_relation.relation_name
					";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "지급수당항목":
				$azsql ="
					SELECT
						allow_code as code,
						allow_name||' ('||allow_code||')' as name
					FROM HR_CODE_ALLOW
					WHERE  PAY_DIV LIKE '0'
						ORDER BY allow_code
					";
				
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "공제수당항목":
				$azsql ="
					SELECT
						allow_code as code,
						allow_name||' ('||allow_code||')' as name
					FROM HR_CODE_ALLOW
					WHERE  PAY_DIV LIKE '1'
						ORDER BY allow_code
					";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
					
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
				
			case "학교구분코드":
				$azsql ="
					SELECT
					school_div_code as code,
					school_div_name as name
					FROM
					hr_code_school_div
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
				
			case "교육분야":
				$azsql ="
				SELECT
				edu_part_code as code,
				edu_part_name as name
				FROM
				hr_code_edu_part
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "인원10":
				$query_data = array();
				for($i=0;$i<11;$i++){
					$item=array('CODE'=>$i,'NAME'=>$i."명");
					array_push($query_data,$item);
				}//for
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "인원15":
				$query_data = array();
				for($i=0;$i<16;$i++){
					$item=array('CODE'=>$i,'NAME'=>$i."명");
					array_push($query_data,$item);
				}//for
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "인원20":
				$query_data = array();
				for($i=0;$i<21;$i++){
					$item=array('CODE'=>$i,'NAME'=>$i."명");
					array_push($query_data,$item);
				}//for
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "은행계좌수": //인력정보-급여관리-기준관리-급여마스터 등록
				$azsql ="
					SELECT
						a.emp_no as code,
						a.account_no as name,
						a.bank_code,
						a.depositor,
						a.order_seq,
						a.seq,
						'0' rowstatus
					FROM hr_payx_deposit a
					Where a.emp_no = '$etc_param'
					";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "계좌구분":
				$query_data = array();
				$item=array('CODE'=>'급여계좌','NAME'=>"급여계좌");
				array_push($query_data,$item);
				$item=array('CODE'=>'IRP계좌','NAME'=>"IRP계좌");
				array_push($query_data,$item);
				$item=array('CODE'=>'출장비계좌','NAME'=>"출장비계좌");
				array_push($query_data,$item);
				$item=array('CODE'=>'기타계좌','NAME'=>"기타계좌");
				array_push($query_data,$item);
				
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "은행코드": //인력정보-급여관리-기준관리-급여마스터 등록
				$azsql ="
					SELECT 	a.bank_code as code,   a.bank_name as name
					FROM 	hr_code_bank 	a
					";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "은행코드2": //인력정보-급여관리-기준관리-급여마스터 등록 -> 사용안함 계좌 제외
				$azsql  = " SELECT      ";
				$azsql .= "     a.bank_code as code,    ";
				$azsql .= "     a.bank_name as name ";
				$azsql .= " FROM      ";
				$azsql .= "     hr_code_bank     a ";
				$azsql .= " Where bank_name Not Like '%" . $this->HangleEncodeUTF8_EUCKR('사용안함') . "%' ";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "세금계산방법": //인력정보-급여관리-기준관리-급여마스터 등록
				$azsql ="
					SELECT a.ref_code as code, a.ref_name as name
					FROM hr_code_ref a
					Where a.ref_code != '00'
					and	a.ref_gbn_code = '73'
					";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "급여지급방법": //인력정보-급여관리-기준관리-급여마스터 등록
				$azsql ="
						SELECT a.ref_code as code,    a.ref_name as name
						FROM hr_code_ref a
						Where a.ref_code != '00'
						and	a.ref_gbn_code = '02'
					";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
				
			case "자격구분": //
				$azsql ="
				SELECT
				license_area_code as code,
				license_area_name as name
				FROM hr_code_license_area
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "자격면허종류": //
				$azsql ="
				SELECT
					license_type_code as code,
					license_type_name as name
				FROM 	hr_code_license_type
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "임직원구분":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'임원');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'직원');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "졸업구분코드":
				/*
				 $query_data = array();
				 $item=array('CODE'=>'0010','NAME'=>'고졸');
				 array_push($query_data,$item);
				 $item=array('CODE'=>'0020','NAME'=>'초대졸');
				 array_push($query_data,$item);
				 $item=array('CODE'=>'0030','NAME'=>'대졸');
				 array_push($query_data,$item);
				 $item=array('CODE'=>'0040','NAME'=>'석사');
				 array_push($query_data,$item);
				 $item=array('CODE'=>'0050','NAME'=>'박사');
				 array_push($query_data,$item);
				 
				 //$this->smarty->assign($value_name,$query_data);
				 if($output_type=="json" || $output_type=="JSON"){
				 $query_data= urldecode(json_encode($query_data));
				 echo $query_data;
				 }else if($output_type=="array" || $output_type=="ARRAY"){
				 return $query_data;
				 }else{
				 $this->smarty->assign($value_name,$query_data);
				 }
				 */
				$azsql ="
					select  ref_code as CODE,ref_name as NAME
					from  hr_code_ref
					where  ref_gbn_code = '31'
					and  ref_code != '00'
					order by ref_code
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "식대과세":
				$query_data = array();
				$item=array('CODE'=>'','NAME'=>'');
				array_push($query_data,$item);
				$item=array('CODE'=>'Y','NAME'=>'과세');
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>'비과세');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "쓰기권한":
				$query_data = array();
				$item=array('CODE'=>'R','NAME'=>'읽기');
				array_push($query_data,$item);
				$item=array('CODE'=>'U','NAME'=>'수정');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "정산구분":
				$query_data = array();
				$item=array('CODE'=>'N','NAME'=>'연말');
				array_push($query_data,$item);
				$item=array('CODE'=>'Y','NAME'=>'중도');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "정산연도":
				$azsql ="
						select distinct substr(work_year,0,4) as code, substr(work_year,0,4) as name
						from HR_YETA_RESULT
						where emp_no like '$etc_param'||'%'
						order by 1 desc
				";
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
				
			case "대내외구분":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'대내');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'대외');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
				
			case "성별":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'남자');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'여자');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "YN선택":
				$query_data = array();
				$item=array('CODE'=>'Y','NAME'=>'YES');
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>'NO');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "년도선택":
				$query_data = array();
				for($i=1945;$i<2100;$i++){
					$item=array('CODE'=>$i,'NAME'=>$i."년");
					array_push($query_data,$item);
				}//for
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "년도선택2":
				$query_data = array();
				$ThisYear=date("Y");
				for($i=$ThisYear;$i>=$ThisYear-20;$i--){
					$item=array('CODE'=>$i,'NAME'=>$i."년");
					array_push($query_data,$item);
				}//for
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "년도선택3":
				$query_data = array();
				$ThisYear=date("Y")+1;
				for($i=$ThisYear;$i>=$ThisYear-20;$i--){
					$item=array('CODE'=>$i,'NAME'=>$i."년");
					array_push($query_data,$item);
				}//for
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "년도선택4":
				$query_data = array();
				$ThisYear=date("Y")+1;
				for($i=$ThisYear;$i>=2017;$i--){
					$item=array('CODE'=>$i,'NAME'=>$i."년");
					array_push($query_data,$item);
				}//for
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "월선택":
				$query_data = array();
				for($jj=1;$jj<13;$jj++){
					$item=array('CODE'=>sprintf("%02d",$jj),'NAME'=>sprintf("%02d",$jj).'월');
					array_push($query_data,$item);
				}//for
				
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "년월선택":
				$query_data = array();
				$ThisYear=date("Y");
				$ThisYear_1=$ThisYear-20;
				for($i=$ThisYear;$i>=$ThisYear_1;$i--){
					for($jj=1;$jj<13;$jj++){
						$item=array('CODE'=>$i.sprintf("%02d",$jj),'NAME'=>$i.'년'.sprintf("%02d",$jj).'월');
						array_push($query_data,$item);
					}//for
				}//for
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "기준년도":
				
				$azsql ="SELECT DISTINCT APPLY_YYMM as code,  APPLY_YYMM as name FROM HR_PAYX_BASE order by 1 desc ";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "수당": //인력정보-급여관리-기준관리-고정수당등록
				$azsql ="
					select allow_code as code ,allow_name as name
					from hr_code_allow
					order by allow_code,pay_div
					";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "구분": 		//인력정보-급여관리-기준관리-고정수당등록
			case "근무구분":	//인력정보-급여관리-기준관리-급여마스터등록
				$azsql ="
				select  ref_code as code,ref_name as name
				from  hr_code_ref
				where  ref_gbn_code = '04'
				and  ref_code != '00'
				order by ref_code
			";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "근무구분2": //(상주,비상주)
				$azsql ="
				SELECT
				ref_code  as code,
				ref_name  as name
				FROM hr_code_ref
				where ref_gbn_code = '05'
				and ref_code != '00'
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				//-------------------------------------
				
			case "지급종류":
				$azsql ="
				select  ref_code as code,ref_name as name
				from  hr_code_ref
				where  ref_gbn_code ='01'
						and  ref_code != '00'
						order by ref_code
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
				
			case "조회년월":
				$azsql ="
					SELECT DISTINCT SUBSTR(REAL_ORDER_DATE, 0, 6) as code,SUBSTR(REAL_ORDER_DATE, 0, 4)||'-'||SUBSTR(REAL_ORDER_DATE, 5, 2) as name
					FROM HR_ORDE_MASTER
					WHERE (ORDER_CODE = '11' OR ORDER_CODE = '01' OR ORDER_CODE = 'ZZ' OR ORDER_CODE = '18' OR ORDER_CODE = '80' OR ORDER_CODE = '19' OR ORDER_CODE = '20' OR ORDER_CODE = '21' )
					order by 1 desc
				";
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "지급년월":
				$azsql ="
						SELECT DISTINCT A.WORK_YYMM as code , substr(a.work_yymm,0,4)||'-'||substr(a.work_yymm,5,2) as name
						FROM HR_PAYX_RESULT_MST A,
							 HR_PAYX_END B
						WHERE (A.COMPANY_CODE = B.COMPANY_CODE)
						  AND (A.PAY_KIND = B.PAY_KIND)
						  AND (A.WORK_YYMM = B.WORK_YYMM)
						  AND QUERY_YN = 'Y'
						  AND A.PAY_KIND = 'P'
						  and a.emp_no like '$etc_param'||'%'
						order by a.work_yymm desc
				";
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "지급년월2":
				$azsql ="
						SELECT DISTINCT A.WORK_YYMM as code , substr(a.work_yymm,0,4)||'-'||substr(a.work_yymm,5,2) as name
						FROM HR_PAYX_RESULT_MST A,
							 HR_PAYX_END B
						WHERE (A.COMPANY_CODE = B.COMPANY_CODE)
						  AND (A.PAY_KIND = B.PAY_KIND)
						  AND (A.WORK_YYMM = B.WORK_YYMM)
						  AND A.PAY_KIND = 'P'
						  and a.emp_no like '$etc_param'||'%'
						order by a.work_yymm desc
				";
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "지급년월3":
				$azsql ="
					SELECT DISTINCT WORK_YYMM as code , substr(work_yymm,0,4)||'-'||substr(work_yymm,5,2) as name
					FROM HR_PAYX_RESULT_DETAIL
					order by work_yymm desc
					";
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "수당구분":
				$azsql ="SELECT ALLOW_CODE as code, ALLOW_NAME as name FROM HR_CODE_ALLOW WHERE PAY_DIV LIKE $output_type ORDER BY ALLOW_CODE";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,"ajax");
				}
				break;
			case "승인여부":
				$query_data = array();
				$item=array('CODE'=>'Y','NAME'=>'승인');
				array_push($query_data,$item);
				$item=array('CODE'=>'N','NAME'=>'미승인');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "분야구분":
				$azsql ="
				SELECT
				ref_code  as code,
				ref_name  as name
				FROM hr_code_ref
				where ref_gbn_code = '03'
				and ref_code != '00'
				";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				//-------------------------------------
			case "부서현장구분":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'부서');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'현장');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "지역구분":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'서울');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'지방(군산)');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "사용여부":
				$query_data = array();
				$item=array('CODE'=>'N','NAME'=>'미사용');
				array_push($query_data,$item);
				$item=array('CODE'=>'Y','NAME'=>'사용');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "계정코드": case "급여계정코드": case "상여계정코드":
				/*
				 20310901	퇴직급여충당금
				 50120101	원가)급여(임.직원)
				 50120103	원가)급여(연,월차)
				 50120501	원가)상여금(임.직원)
				 50121501	원가)연구비(급여)
				 .
				 .
				 * */
				$azsql ="
						select
							a.acnt_code as code
							,a.acnt_name  as name
						  from am_code_acnt a,
							 	 vw_am_calc_list_acnt_join b
						 where a.acnt_code = b.acnt_code
						 and   b.calc_kind_code = '000'
						 and	  b.calc_list_code = 'hr0001'
						 order by a.acnt_code
				";
				
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "급상여계정부서": //
				$azsql ="
				SELECT DEPT_CODE as code, DEPT_NAME as name
				FROM SM_CODE_DEPT
				WHERE USE_YN = 'Y' and dept_code not in ('A0100','A0200','B0301','B0702','C0200','S0100','G0301','ZZZZZZ') ORDER BY DEPT_CODE";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "급상여본부": //
				$azsql ="
				select HEADQUATER_CODE as code,
					   HEADQUATER_NAME as name
				from SM_CODE_HEADQUATER
				where use_yn='Y'
				  and HEADQUATER_CODE not in ( '00000')
				order by 1 ";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "본부구분":
				/*
				 HEADQUATER_NAME	HEADQUATER_CODE
				 -------------------
				 (주)바론		00000
				 경영지원본부	A0000
				 대표이사		A1000
				 사업총괄지원본부	B0000
				 감사실		B9000
				 .
				 .
				 * */
				$azsql ="
				select
					headquater_code as code
					,headquater_name as name
				from sm_code_headquater
				where
					company_code = '11'
				order by headquater_code
				";
				
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "상위부서":
				/*
				 CODE	NAME
				 ---------------
				 A0000	경영지원본부
				 A0100	대표이사
				 A0300	감사실
				 .
				 .
				 * */
				$azsql ="
				select
					dept_code as code
					,dept_name as name
				from hr_code_dept
				where company_code = '11'
				  and dept_div_code = '1'
				order by dept_code
				";
				
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "소속부서":
				
				$col_name = '전체';
				$col_name = trim(ICONV("UTF-8","EUC-KR",$col_name));
				
				$azsql ="
					SELECT '%' AS CODE,
						   '$col_name' AS NAME
					  FROM DUAL
					UNION ALL
					 SELECT DEPT_CODE as CODE,
							DEPT_NAME||'('||DEPT_CODE||')' as NAME
					   FROM SM_CODE_DEPT
					  WHERE USE_YN = 'Y'
						AND COMPANY_CODE = '11'
					ORDER BY CODE
				";
				//					 WHERE '".$etc_param."' = 'ALL'
				
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "전문분야":
				$azsql ="select class_code as code, class_code||':'||class_name as name from cs_code_class where class_tag = 'KA' order by sort_order";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
			case "전문분야2":
				$azsql ="select class_code as code, class_name as name from cs_code_class where class_tag = 'KA' order by sort_order";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "근무형태":
				$azsql ="select class_code as code, class_code||':'||class_name as name from cs_code_class where class_tag = 'KD' order by sort_order";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
			case "근무형태2":
				$azsql ="select class_code as code, class_name as name from cs_code_class where class_tag = 'KD' order by sort_order";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				break;
				
			case "결재상태":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'작성중');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'결재중');
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>'결재완료');
				array_push($query_data,$item);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					$this->smarty->assign($value_name,$query_data);
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "재직자현황":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'재직');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'휴직');
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>'퇴직');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "정렬조건":
				$query_data = array();
				$item=array('CODE'=>'dept_code','NAME'=>'부서');
				array_push($query_data,$item);
				$item=array('CODE'=>'grade_code','NAME'=>'직위');
				array_push($query_data,$item);
				$item=array('CODE'=>'emp_name','NAME'=>'성명');
				array_push($query_data,$item);
				
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "조회구분1":
				$query_data = array();
				$item=array('CODE'=>'0','NAME'=>'부서별');
				array_push($query_data,$item);
				$item=array('CODE'=>'1','NAME'=>'본부별');
				array_push($query_data,$item);
				
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "조회구분2":
				$query_data = array();
				$item=array('CODE'=>'0','NAME'=>'월별/직급별');
				array_push($query_data,$item);
				$item=array('CODE'=>'1','NAME'=>'월별/분야별');
				array_push($query_data,$item);
				
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "조회구분3":
				$query_data = array();
				$item=array('CODE'=>'0','NAME'=>'직종별');
				array_push($query_data,$item);
				$item=array('CODE'=>'1','NAME'=>'연도별');
				array_push($query_data,$item);
				
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "조회구분4":
				$query_data = array();
				$item=array('CODE'=>'0','NAME'=>'당해년도');
				array_push($query_data,$item);
				$item=array('CODE'=>'1','NAME'=>'소급1년');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'당해년도(평균)');
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>'소급1년(평균)');
				array_push($query_data,$item);
				
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "구분1":
				/*
				 CODE	NAME	REF_NAME2	REF_GBN_CODE
				 B	상여		01
				 S	성과급		01
				 P	급여		01
				 * */
				$azsql ="
					SELECT REF_CODE as code, REF_NAME as name, REF_NAME2, REF_GBN_CODE
					FROM hr_code_ref
					WHERE
						REF_GBN_CODE = '01' AND REF_CODE != '00'
					ORDER BY REF_CODE
					";
				
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "구분2":
				/*
				 CODE	NAME	REF_NAME2	REF_GBN_CODE
				 P	급여	1	70
				 B	상여	2	70
				 S	성과급	3	70
				 Z	상여+성과급	4	70
				 * */
				$azsql ="
				SELECT REF_CODE as code, REF_NAME as name, REF_NAME2, REF_GBN_CODE
				FROM HR_CODE_REF
				WHERE  REF_GBN_CODE = '70' AND REF_CODE != '00'
				ORDER BY REF_NAME2
				";
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "구분3":
				$query_data = array();
				$item=array('CODE'=>'P','NAME'=>'급여');
				array_push($query_data,$item);
				$item=array('CODE'=>'B','NAME'=>'상여');
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "구분33":
				$query_data = array();
				$item=array('CODE'=>'P','NAME'=>'급여');
				array_push($query_data,$item);
				$item=array('CODE'=>'B','NAME'=>'상여');
				array_push($query_data,$item);
				$item=array('CODE'=>'R','NAME'=>'퇴직');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "구분4":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'인건비');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'평균인건비');
				array_push($query_data,$item);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "구분5":
				$query_data = array();
				$item=array('CODE'=>'1','NAME'=>'전직원');
				array_push($query_data,$item);
				$item=array('CODE'=>'2','NAME'=>'현근무부서별');
				array_push($query_data,$item);
				$item=array('CODE'=>'3','NAME'=>'원소속부서별');
				array_push($query_data,$item);
				$item=array('CODE'=>'4','NAME'=>'직급별');
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
			case "구분6":
				/*
				 CODE	NAME
				 재직	1
				 경력	2
				 퇴직	3
				 * */
				$azsql ="
				SELECT REF_NAME AS NAME, REF_CODE AS CODE
				FROM HR_CODE_REF
				WHERE REF_GBN_CODE = '35' AND REF_CODE != '00'
				ORDER BY REF_CODE
				";
				$this->oracle->LoadData($azsql,$value_name,$output_type);
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
				
			case "휴대폰구분":
				$query_data = array();
				$item=array('CODE'=>'010','NAME'=>'010');
				array_push($query_data,$item);
				$item=array('CODE'=>'011','NAME'=>'011');
				array_push($query_data,$item);
				$item=array('CODE'=>'015','NAME'=>'015');
				array_push($query_data,$item);
				$item=array('CODE'=>'016','NAME'=>'016');
				array_push($query_data,$item);
				$item=array('CODE'=>'017','NAME'=>'017');
				array_push($query_data,$item);
				$item=array('CODE'=>'018','NAME'=>'018');
				array_push($query_data,$item);
				$this->smarty->assign($value_name,$query_data);
				//$this->smarty->assign($value_name,$query_data);
				if($output_type=="json" || $output_type=="JSON"){
					$query_data= urldecode(json_encode($query_data));
					echo $query_data;
				}else if($output_type=="array" || $output_type=="ARRAY"){
					return $query_data;
				}else{
					$this->smarty->assign($value_name,$query_data);
				}
				break;
				
			case "직위":
				/*
				 CODE	NAME
				 A1	명예회장
				 A2	회장
				 A3	부회장
				 ...
				 * */
				$azsql ="
				SELECT GRADE_CODE as code,  GRADE_NAME as name FROM HR_CODE_GRADE  where use_yn = 'Y' ORDER BY  GRADE_CODE
				";
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "직위2":
				$azsql = " SELECT GRADE_CODE as code,  GRADE_NAME as name FROM HR_CODE_GRADE  where use_yn = 'Y' and GRADE_CODE not in ( 'C0', 'E0', 'F0', 'J0' ) ORDER BY  GRADE_CODE desc ";
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "직위3":
				$azsql = " SELECT GRADE_CODE as code,  GRADE_NAME as name FROM HR_CODE_GRADE  where use_yn = 'Y' and GRADE_CODE not in ( 'C0', 'E0', 'F0', 'J0' ) and GRADE_CODE > 'B2' ORDER BY  GRADE_CODE asc ";
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "자격종류":
				/*
				 CODE	NAME
				 10	기술사
				 11	건축사
				 20	기능장
				 30	기사
				 ...
				 * */
				$azsql ="
				SELECT  LICENSE_TYPE_CODE as code, LICENSE_TYPE_NAME as name
				FROM  HR_CODE_LICENSE_TYPE
				ORDER BY    LICENSE_TYPE_CODE
				";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "자격증":
				/*
				 CODE	NAME
				 10	기술사
				 11	건축사
				 20	기능장
				 30	기사
				 ...
				 * */
				$azsql ="
				SELECT license_name as name, license_code as code
				FROM hr_code_license
				ORDER BY license_code
				";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "교육기관":
				/*
				 CODE	NAME
				 100	건설기술교육원(인천)
				 101	건설기술교육원(서울)
				 102	건설기술교육원(강북)
				 103	건설기술교육원(대전)
				 ...
				 * */
				$azsql ="
				SELECT  EDU_OFFICE_CODE as code,  EDU_OFFICE_NAME as name
				FROM HR_CODE_EDU_OFFICE
				ORDER BY EDU_OFFICE_CODE
				";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "면허":
				/*
				 CODE	NAME
				 01	교통영향분석개선대책수립대행자
				 02	공공측량업
				 03	수치지도제작업
				 04	수질방지시설업
				 ...
				 * */
				$azsql ="
				SELECT MANG_LICENSE_CODE as code,  MANG_LICENSE_NAME as name
				FROM HR_CODE_MANG_LICENSE
				ORDER BY MANG_LICENSE_CODE
				";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "은행명":
				/*
				 CODE	NAME
				 1	녹십자생명
				 10	수협
				 11	기업은행
				 2	교보생명
				 ...
				 * */
				$azsql ="SELECT ref_name as name, ref_code as code FROM hr_code_ref WHERE ref_gbn_code = '43' AND ref_code != '00' ORDER BY ref_code";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "은행명2":
				/*
				 CODE	NAME
				 05	한국외환은행
				 32	부산은행
				 37	전북은행
				 39	경남은행
				 ...
				 * */
				$azsql ="SELECT bank_code as code ,bank_name as name FROM hr_code_bank ";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "작업년도":
				/*
				 CODE	NAME
				 20160811	20160811
				 20160602	20160602
				 20160131	20160131
				 20150501	20150501
				 ...
				 * */
				$azsql ="select work_yyyy as code,work_yyyy as name from hr_reti_trust group by work_yyyy order by work_yyyy desc";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "발급기간":
				/*
				 CODE
				 2016
				 2015
				 2014
				 2013
				 ...
				 * */
				$azsql ="SELECT DISTINCT SUBSTR(WORK_YYMM, 0, 4) as code  FROM HR_PAYX_RESULT_MST order by 1 desc";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "사원영문정보":	/*제 증명서(영문)*/
				/*
				 e_name		join_date	last_date	level_name
				 * */
				$azsql ="SELECT A.empname_eng as e_name, A.join_date as join_date, nvl(A.retire_date, '".date("Ymd")."') as last_date, decode(A.jobkind_code, '20', B.grade_eng_tech, '60', B.grade_eng_tech, B.grade_eng_mang) as level_name
							 FROM HR_PERS_MASTER A, HR_CODE_GRADE B
							 WHERE A.emp_no like '$output_type'
								AND A.company_code like '11'
								AND A.grade_code = B.grade_code";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					echo json_encode($this->oracle->LoadData($azsql,$value_name,""));
				}
				break;
			case "사원정보":	/*재직,경력,퇴직 증명서*/
				/*
				 e_name		join_date	last_date	level_name
				 
				 real_dept_code로 수정
				 * */
				/*
				 $azsql ="SELECT
				 B.grade_code as grade_code,
				 A.real_dept_code as dept_code,
				 C.dept_name as dept_name,
				 A.join_date as join_date,
				 nvl(A.retire_date, '".date("Ymd")."') as retire_date,
				 A.addr1 , A.addr2,
				 A.birthday,
				 (select represent_name from sm_code_company) as represent_name,
				 D.duty_name
				 FROM HR_PERS_MASTER A, HR_CODE_GRADE B, hr_code_dept C, HR_CODE_DUTY D
				 WHERE A.emp_no like '$output_type'
				 AND A.company_code like '11'
				 AND A.grade_code = B.grade_code
				 AND A.real_dept_code = C.dept_code
				 AND A.duty_code = D.duty_code
				 ";
				 */
				//직무없어도 검색되게수정
				$azsql ="SELECT
								B.grade_code as grade_code,
								A.real_dept_code as dept_code,
								C.dept_name as dept_name,
								A.join_date as join_date,
								nvl(A.retire_date, '".date("Ymd")."') as retire_date,
								A.addr1 , A.addr2,
								A.birthday,
								(select represent_name from sm_code_company) as represent_name
							FROM HR_PERS_MASTER A, HR_CODE_GRADE B, hr_code_dept C
							WHERE A.emp_no like '$output_type'
								AND A.company_code like '11'
								AND A.grade_code = B.grade_code
								AND A.real_dept_code = C.dept_code
							";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					echo json_encode($this->oracle->LoadData($azsql,$value_name,""));
				}
				break;
				
				
			case "최종학력":
				/*
				 CODE	NAME
				 0		기타
				 1		초졸
				 2		중졸
				 3		고졸
				 ...
				 * */
				$azsql ="
						SELECT
						school_car_code as code,
						school_car_name as name
							FROM HR_CODE_SCHOOL_CAR
							ORDER BY school_car_code
						";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "전공분야2":
				/*
				 CODE	NAME
				 001		가정관리학과
				 002		가정학
				 003		간호학
				 ...
				 * */
				$azsql ="SELECT major_name as name, major_code as code
							FROM HR_CODE_MAJOR
							ORDER BY major_code";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "협회구분":
				/*
				 CODE	NAME
				 1		협회
				 3		학회
				 5		조합
				 8		기타회원
				 9		기타비회원
				 ...
				 * */
				$azsql ="
					SELECT
						rb_comm_code.etc_code as code,
						rb_comm_code.etc_name as name
					FROM
						rb_comm_code
					WHERE
						( rb_comm_code.company_code = '11' ) AND
						( rb_comm_code.sys_id = 'SO' ) AND
						( rb_comm_code.etc_div = '20' )AND
						( rb_comm_code.use_yn = 'Y' )
					ORDER BY
						nvl(rb_comm_code.orders,0) ASC,
						rb_comm_code.etc_code ASC
				";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "가입형태":
				/*
				 CODE	NAME
				 10		일반회원사
				 15		임원사
				 20		특별회원사
				 ...
				 * */
				$azsql ="
					SELECT
						rb_comm_code.etc_code as code,
						rb_comm_code.etc_name as name
					FROM
						rb_comm_code
					WHERE
						( rb_comm_code.company_code = '11' ) AND
						( rb_comm_code.sys_id = 'SO' ) AND
						( rb_comm_code.etc_div = '30' )AND
						( rb_comm_code.use_yn = 'Y' )
					ORDER BY
						nvl(rb_comm_code.orders,0) ASC,
						rb_comm_code.etc_code ASC
				";
				
				//$this->oracle->LoadData($azsql,$value_name,$output_type);
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
			case "부서":
				$azsql ="
							select
							hr_code_dept.dept_code as code,
							hr_code_dept.dept_name as name
							from hr_code_dept
							where hr_code_dept.use_yn = 'Y'
							order by hr_code_dept.dept_code
						";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "전체부서":
				//사용안하는 부서까지 전부
				$azsql ="
							select
							hr_code_dept.dept_code as code,
							hr_code_dept.dept_name as name
							from hr_code_dept
							order by hr_code_dept.dept_code
						";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "직종":
				$azsql ="
							SELECT
							hr_code_jobkind.jobkind_code as code,
							hr_code_jobkind.jobkind_name as name
							FROM hr_code_jobkind
						";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
			case "직무":
				$azsql ="
							SELECT
							hr_code_duty.duty_code as code,
							hr_code_duty.duty_name as name
							FROM hr_code_duty
						";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				
				
			case "직급":
				/*
				 CODE	NAME
				 00	임원
				 10	1급
				 20	2급
				 30	3급
				 ...
				 * */
				$azsql ="
							select
							level_code as code,
							level_name as name
							from hr_code_level
							order by level_code
						";
				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				if($output_type=="array" || $output_type=="ARRAY"){
					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
					return $re_data;
				}else{
					$this->oracle->LoadData($azsql,$value_name,$output_type);
				}
				break;
				//			case "직위":
				//				$azsql ="
				//							SELECT
				//							hr_code_grade.grade_code as code ,
				//							hr_code_grade.grade_name as name
				//							FROM hr_code_grade
				//						";
				//				//$this->oracle->LoadData($azsql,$value_name,"ajax");
				//				if($output_type=="array" || $output_type=="ARRAY"){
				//					$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				//					return $re_data;
				//				}else{
				//					$this->oracle->LoadData($azsql,$value_name,$output_type);
				//				}
				//				break;
		case "직책":
			$azsql ="
							SELECT
							hr_code_title.title_code as code ,
							hr_code_title.title_name as name
							FROM hr_code_title
						";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
		case "직책2":
			$query_data = array();
			$item=array('CODE'=>'','NAME'=>'');
			array_push($query_data,$item);
			$item=array('CODE'=>'910','NAME'=>'책임');
			array_push($query_data,$item);
			$item=array('CODE'=>'950','NAME'=>'보조');
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			if($output_type=="json" || $output_type=="JSON"){
				$query_data= urldecode(json_encode($query_data));
				echo $query_data;
			}else if($output_type=="array" || $output_type=="ARRAY"){
				return $query_data;
			}else{
				$this->smarty->assign($value_name,$query_data);
			}
			break;
		case "투입가능여부":
			$query_data = array();
			$item=array('CODE'=>'Y','NAME'=>'가능');
			array_push($query_data,$item);
			$item=array('CODE'=>'N','NAME'=>'불가능');
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			if($output_type=="json" || $output_type=="JSON"){
				$query_data= urldecode(json_encode($query_data));
				echo $query_data;
			}else if($output_type=="array" || $output_type=="ARRAY"){
				return $query_data;
			}else{
				$this->smarty->assign($value_name,$query_data);
			}
			break;
		case "사진여부":
			$query_data = array();
			$item=array('CODE'=>'N','NAME'=>'N');
			array_push($query_data,$item);
			$item=array('CODE'=>'Y','NAME'=>'Y');
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			//$this->smarty->assign($value_name,$query_data);
			if($output_type=="json" || $output_type=="JSON"){
				$query_data= urldecode(json_encode($query_data));
				echo $query_data;
			}else if($output_type=="array" || $output_type=="ARRAY"){
				return $query_data;
			}else{
				$this->smarty->assign($value_name,$query_data);
			}
			break;
			//쿼리 case추가시 사용편의성을 위하여 결과값 2~3개정도 기입바람
		case "감리원등급":
			$azsql ="select class_code as code, class_code||':'||class_name as name from cs_code_class where class_tag = 'KB' order by sort_order";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "감리원등급2":
			$azsql ="select class_code as code, class_name as name from cs_code_class where class_tag = 'KB' order by sort_order";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "투입등급":
			$azsql ="select class_code as code, class_code||':'||class_name as name from cs_code_class where class_tag = 'KC' order by sort_order";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "투입등급2":
			$azsql ="select class_code as code, class_name as name from cs_code_class where class_tag = 'KC' order by sort_order";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "준공여부":
			$azsql ="select class_code as code, class_name as name,1 as sort_tag,sort_order from vw_cs_code_class_completion where company_code = '11' ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "평가":
			$query_data = array();
			$item=array('CODE'=>'A','NAME'=>'수');
			array_push($query_data,$item);
			$item=array('CODE'=>'B','NAME'=>'우');
			array_push($query_data,$item);
			$item=array('CODE'=>'C','NAME'=>'미');
			array_push($query_data,$item);
			$item=array('CODE'=>'D','NAME'=>'양');
			array_push($query_data,$item);
			$item=array('CODE'=>'E','NAME'=>'가');
			array_push($query_data,$item);
			
			//$this->smarty->assign($value_name,$query_data);
			if($output_type=="json" || $output_type=="JSON"){
				$query_data= urldecode(json_encode($query_data));
				echo $query_data;
			}else if($output_type=="array" || $output_type=="ARRAY"){
				return $query_data;
			}else{
				$this->smarty->assign($value_name,$query_data);
			}
			break;
			
		case "퇴직사유":
			$query_data = array();
			$item=array('CODE'=>'1','NAME'=>'정년퇴직');
			array_push($query_data,$item);
			$item=array('CODE'=>'2','NAME'=>'정리해고');
			array_push($query_data,$item);
			$item=array('CODE'=>'3','NAME'=>'자발적퇴직');
			array_push($query_data,$item);
			$item=array('CODE'=>'4','NAME'=>'임원퇴직');
			array_push($query_data,$item);
			$item=array('CODE'=>'5','NAME'=>'중간정산');
			array_push($query_data,$item);
			$item=array('CODE'=>'6','NAME'=>'기타');
			array_push($query_data,$item);
			
			//$this->smarty->assign($value_name,$query_data);
			if($output_type=="json" || $output_type=="JSON"){
				$query_data= urldecode(json_encode($query_data));
				echo $query_data;
			}else if($output_type=="array" || $output_type=="ARRAY"){
				return $query_data;
			}else{
				$this->smarty->assign($value_name,$query_data);
			}
			break;
			
		case "등급":
			$query_data = array();
			$item=array('CODE'=>'A','NAME'=>'A');
			array_push($query_data,$item);
			$item=array('CODE'=>'B','NAME'=>'B');
			array_push($query_data,$item);
			$item=array('CODE'=>'C','NAME'=>'C');
			array_push($query_data,$item);
			$item=array('CODE'=>'D','NAME'=>'D');
			array_push($query_data,$item);
			$item=array('CODE'=>'E','NAME'=>'E');
			array_push($query_data,$item);
			
			//$this->smarty->assign($value_name,$query_data);
			if($output_type=="json" || $output_type=="JSON"){
				$query_data= urldecode(json_encode($query_data));
				echo $query_data;
			}else if($output_type=="array" || $output_type=="ARRAY"){
				return $query_data;
			}else{
				$this->smarty->assign($value_name,$query_data);
			}
			break;
			
		case "적용년월": // 인력정보→급여관리→기초자료→수당별 세부항목 등록
			$azsql ="
						select
						apply_yymm as code
						,substr(apply_yymm,1,4)||'년'||substr(apply_yymm,5,2)||'월' as name
						from hr_payx_allow_amt
						group by apply_yymm
						order by apply_yymm desc
				";
			
			$azsql=$this->HangleEncodeUTF8_EUCKR($azsql);
			//,substr(apply_yymm,1,4)||'년'||substr(apply_yymm,5,2)||'월' as name
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,"");
			}
			
			break;
			
		case "적용년월2": // 인력정보→급여관리→기초자료→호봉테이블 등록
			$azsql ="
						SELECT
							distinct company_code
							,apply_yymm as code
							,substr(apply_yymm,1,4)||'년'||substr(apply_yymm,5,2)||'월' as name
						FROM hr_payx_base
						WHERE
							company_code like '11'
						ORDER BY apply_yymm DESC
				";
			
			$azsql=$this->HangleEncodeUTF8_EUCKR($azsql);
			//,substr(apply_yymm,1,4)||'년'||substr(apply_yymm,5,2)||'월' as name
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,"");
			}
			
			break;
			
		case "적용년월3": // 인력정보→급여관리→기준관리→고정/변동 수당별 등록
			$azsql ="
					select
					to_char(sysdate,'yyyymm') as code,
					to_char(sysdate,'yyyy-mm') as name
					from dual
					
					union all
					
					SELECT
					distinct work_yymm as code
					,substr(work_yymm,1,4)||'-'||substr(work_yymm,5,2) as name
					FROM hr_payx_var_allow
					ORDER BY 1 desc
				";
			
			$azsql=$this->HangleEncodeUTF8_EUCKR($azsql);
			//,substr(apply_yymm,1,4)||'년'||substr(apply_yymm,5,2)||'월' as name
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,"");
			}
			
			break;
			
			
			
		case "과세/비과세":
			//인력정보→급여관리→기초자료→수당항목등록
			$azsql ="
						SELECT
							REF_GBN_CODE
							,REF_CODE as code
							,REF_NAME as name
							,REF_NAME2
						FROM HR_CODE_REF
						WHERE REF_GBN_CODE = '38'
						  AND REF_CODE != '00'
						";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
		case "비과세구분":
			//인력정보→급여관리→기초자료→수당항목등록
			$azsql ="
						SELECT
							REF_GBN_CODE
							,REF_CODE as code
							,REF_NAME as name
							,REF_NAME2
						FROM HR_CODE_REF
						WHERE REF_GBN_CODE = '39'
						  AND REF_CODE != '00'
						";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
		case "지급구분":
			//인력정보→급여관리→기초자료→수당항목등록
			$azsql ="
					SELECT
						REF_GBN_CODE
						,REF_CODE as code
						,REF_NAME as name
						,REF_NAME2
					FROM HR_CODE_REF
					WHERE REF_GBN_CODE = '27'
					  AND REF_CODE != '00'
					";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
		case "자료구분1":
			//인력정보→급여관리→기초자료→수당항목등록
			$azsql ="
					SELECT
					DISTINCT PAY_TAG
					,PAY_DATA1_TAG as code
					,PAY_DATA1_DESC as name
					FROM
					HR_PAYX_ALLOW_PAYTAG
					";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
		case "자료구분2":
			//인력정보→급여관리→기초자료→수당항목등록
			$azsql ="
					SELECT
					DISTINCT PAY_TAG
					,PAY_DATA1_TAG
					,PAY_DATA2_TAG as code
					,PAY_DATA2_DESC as name
					FROM HR_PAYX_ALLOW_PAYTAG
					";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
		case "발령종류":
			$azsql ="select order_name AS name, order_code AS code from hr_code_order order by code";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			//-------------------------------------
		case "발령종류2":
			$azsql ="select order_name AS name, order_code AS code, code_info AS info, use_yn as use_yn from hr_code_order order by orderno, name";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			//-------------------------------------
		case "채용구분":
			$azsql ="
						SELECT
						employ_code  as code,
						employ_name  as name
						FROM hr_code_employ
						";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
			//-------------------------------------
		case "인원구분":
			$azsql ="
						SELECT
						ref_code  as code,
						ref_name  as name
						FROM hr_code_ref
						where ref_gbn_code = '08'
						and ref_code != '00'
						";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
			//-------------------------------------
		case "신입구분":
			$azsql ="
						SELECT
						ref_code  as code,
						ref_name  as name
						FROM hr_code_ref
						where ref_gbn_code = '09'
						and ref_code != '00'
						";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
			//-------------------------------------
		case "소속구분":
			/* 2019.12.19
			 * 10 : 삼안
			 * 20 : 한맥
			 * 30 : 바론
			 * 40 : 장헌
			 * 99 : 기타
			 */
			$azsql ="
						  SELECT ref_code as code, ref_name as name
							FROM  hr_code_ref
						   WHERE  ref_gbn_code='80' AND ref_code !='00' ORDER BY ref_name2, ref_code
						";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
			//-------------------------------------
		case "퇴직연금기준일자":
			/* 2020.01.06 */
			$azsql ="
						  SELECT data_name as code, data_name as name
							FROM  HR_CODE_SYSCNFG
						   where seq = '14' and lineno = '01'
						";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
			//-------------------------------------
			
		case "세금환경설정기준일":
			$azsql ="
						select work_yymm as code,
							   substr(work_yymm,1,4)||'-'||substr(work_yymm,5,2) as name
						from hr_payx_tax_config
						group by work_yymm
						order by 1 desc
						";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
			//-------------------------------------
		case "겸직부서":
			$azsql ="
						SELECT
						hr_code_dept.dept_code as code,
						hr_code_dept.dept_name as name,
						hr_code_dept.use_yn,
						hr_code_dept.dept_div_code
						FROM hr_code_dept
						union all
						SELECT '','','',''
						FROM dual
						";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
			//-------------------------------------
		case "교육구분":
			$azsql ="
						SELECT edu_name AS name, edu_code AS code
						FROM hr_code_edu
						ORDER BY edu_code
						";
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
			//-------------------------------------
		case "PQ교육여부":
			$query_data = array();
			$item=array('CODE'=>'3','NAME'=>'PQ가점(설계감리)');
			array_push($query_data,$item);
			$item=array('CODE'=>'5','NAME'=>'PQ가점(설계)');
			array_push($query_data,$item);
			$item=array('CODE'=>'9','NAME'=>'기타교육');
			array_push($query_data,$item);
			//$this->smarty->assign($value_name,$query_data);
			if($output_type=="json" || $output_type=="JSON"){
				$query_data= urldecode(json_encode($query_data));
				echo $query_data;
			}else if($output_type=="array" || $output_type=="ARRAY"){
				return $query_data;
			}else{
				$this->smarty->assign($value_name,$query_data);
			}
			break;
			
		case "부서조회": //부서 근태현황 - 부서조회
			$azsql ="SELECT a.dept_code as code, b.dept_name as name,'2' AS sort_seq
				FROM (
				SELECT DEPT_CODE FROM sm_auth_user WHERE user_id = '$etc_param'
				UNION ALL
				SELECT ADD_DEPT_CODE1 FROM HR_PERS_MASTER WHERE emp_no = '$etc_param'
				UNION ALL
				SELECT ADD_DEPT_CODE2 FROM HR_PERS_MASTER WHERE emp_no = '$etc_param'
				) a,
				HR_CODE_DEPT b
				WHERE a.dept_code = b.dept_code
				UNION ALL
				SELECT a.dept_code, a.dept_name,'1' FROM (
				SELECT '%' AS DEPT_CODE, '$allmode' AS DEPT_NAME
				FROM DUAL
					UNION ALL
				SELECT DEPT_CODE, DEPT_NAME FROM SM_CODE_DEPT WHERE USE_YN       = 'Y'
				AND dept_code NOT IN ('A0100','A0200','B0301','B0702','C0200','S0000','S0100','G0301','ZZZZZZ')
				) a,
				(SELECT COUNT(etc_code) cnt FROM RB_COMM_CODE WHERE sys_id = 'HR' AND etc_div = '99' AND etc_code = '$etc_param') b
				WHERE b.cnt > 0
				ORDER BY 1 ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "부서조회_title": //사원명부 - 부서장 부서조회
			$azsql ="
				SELECT a.dept_code as code, b.dept_name as name,'2' AS sort_seq
				FROM (
				SELECT DEPT_CODE FROM sm_auth_user WHERE user_id = '$etc_param'
				UNION ALL
				SELECT ADD_DEPT_CODE1 FROM HR_PERS_MASTER WHERE emp_no = '$etc_param'
				UNION ALL
				SELECT ADD_DEPT_CODE2 FROM HR_PERS_MASTER WHERE emp_no = '$etc_param'
				) a,
				HR_CODE_DEPT b
				WHERE a.dept_code = b.dept_code
				
				UNION ALL
				
				SELECT a.dept_code, a.dept_name, '1'
				FROM (
					SELECT '%' AS DEPT_CODE, '$allmode' AS DEPT_NAME
					FROM DUAL
					
					UNION ALL
					
					SELECT DEPT_CODE, DEPT_NAME
					  FROM SM_CODE_DEPT
					 WHERE USE_YN       = 'Y'
					   AND dept_code NOT IN ('A0100','A0200','B0301','B0702','C0200','S0000','S0100','G0301','ZZZZZZ')
					) a
					,(SELECT COUNT(title_code) cnt FROM hr_pers_master where service_div <> '3'   and title_code = '030' AND emp_no = '$etc_param') b
				WHERE b.cnt = 0
				ORDER BY 1 ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "사원조회": //부서 근태현황 - 사원조회
			$azsql ="SELECT a.emp_no AS code, a.emp_name || '[' || SUBSTR(a.emp_no,1,3) || '***]'  AS name
				FROM HR_PERS_MASTER a, SM_AUTH_USER b
				WHERE a.emp_no = b.user_id
				AND a.company_code = '11'
				AND a.dept_code  LIKE '$etc_param'
				AND b.use_yn = 'Y'
				ORDER BY 2,1";
			//echo $azsql;
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
	}//switch
}//PersonQueryCode


//=============================================================================================
// 회계관련된 조회에서 사용하는 공통코드
//=============================================================================================
function AccountQueryCode($mode, $value_name, $output_type, $etc_param="")
{
	switch($mode)
	{
		//181112 차량관리 추가 S----------------------------------------------------------------------
		case "차량관리_사용현장":
			//AccountQueryCode("현장_차량관리", $value_name, $output_type, $etc_param="")
			$azsql ="
					SELECT
					distinct A.DEPT_CODE as code
					,B.DEPT_NAME as name
					FROM RB_DEPT_CARS  A
					JOIN AM_CODE_DEPT B ON A.DEPT_CODE = B.DEPT_CODE
					ORDER BY B.DEPT_NAME
					";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "차량관리_현장별차량":
			//AccountQueryCode(차량관리_현장별차량", $value_name, $output_type, $etc_param="")
			$azsql ="
				SELECT  A.CAR_NO as code ,A.CAR_NO||' ('||C.CAR_KIND||')'	 as name
				FROM RB_DEPT_CARS  A
				JOIN AM_CODE_DEPT B ON A.DEPT_CODE = B.DEPT_CODE
				JOIN RB_CAR_MASTER C  ON A.CAR_NO = C.CAR_NO
				WHERE ( A.DEPT_CODE = '$etc_param' )
				ORDER BY B.DEPT_NAME, A.CAR_NO
				";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			//181112 차량관리 추가 E----------------------------------------------------------------------
			
			//신규추가-------------------------------------------------------------------------
		case "기간구분2":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"발행");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"확정");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "심사기준2":
			$azsql ="SELECT CLASS_CODE as code,CLASS_NAME as name FROM VW_CS_CODE_CLASS_JUDGE_BASE  WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
		case "결재선명":  //171212 -- 전자결재 결재선 등록
			$azsql ="SELECT SLINE_CODE as code, SLINE_NAME as name  FROM  SM_SIGN_LINE_NAME WHERE EMP_NO ='$etc_param' ORDER BY SLINE_CODE";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
		case "사원이름":
			//사번,이름
			$azsql ="select '' as code, '&nbsp;' as name from DUAL
				UNION select EMP_NO as code, EMP_NAME as name from HR_PERS_MASTER where company_code='11' order by CODE DESC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "실행예산편성여부":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"Y");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"N");
			array_push($query_data,$item);
			$item=array("CODE"=>"A","NAME"=>"설계");
			array_push($query_data,$item);
			$item=array("CODE"=>"B","NAME"=>"감리");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
			
		case "신고세무서":
			$azsql ="SELECT tax_office_code as code,tax_office_name as name FROM AM_CODE_TAX_OFFICE WHERE use_yn = 'Y'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "과세분류":
			$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code = '32' ORDER BY valid_value_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
		case "매출매입":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"매입");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"매출");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "부서코드전체":
			$azsql ="select dept_code as code, dept_name as name from sm_code_dept order by dept_code";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "사업장세무":
			$azsql ="SELECT tax_comp_code as code,tax_comp_name as name FROM am_code_taxcomp ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "사업장지점":
			$azsql ="SELECT tax_comp_code as code,vendor_name as name FROM am_code_taxcomp ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "사업장지점2":
			$azsql ="SELECT tax_comp_code as code,substr(vendor_no,1,3)||'-'||substr(vendor_no,4,2)||'-'||substr(vendor_no,6,5) as name FROM am_code_taxcomp ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "과세면세":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"과세");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"면세");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"공통");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "부서종류":
			$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code = '11' ORDER BY valid_value_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "절사구분":
			$azsql ="select valid_value_code as code, valid_value_name as name from am_code_validation where validation_code='30' order by valid_value_code asc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "금액구분":
			$azsql ="select valid_value_code as code, valid_value_name as name from am_code_validation where validation_code='78' order by valid_value_code asc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "좌우":
			$query_data = array();
			$item=array("CODE"=>"L","NAME"=>"좌");
			array_push($query_data,$item);
			$item=array("CODE"=>"R","NAME"=>"우");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "계산모드":
			$query_data = array();
			$item=array("CODE"=>"=","NAME"=>"=");
			array_push($query_data,$item);
			$item=array("CODE"=>"+","NAME"=>"+");
			array_push($query_data,$item);
			$item=array("CODE"=>"-","NAME"=>"-");
			array_push($query_data,$item);
			$item=array("CODE"=>"*/","NAME"=>"*");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "상각비계정":
			$azsql ="SELECT am_code_calc_acnt.acnt_code as code,
						( am_code_acnt.acnt_name || '  (' || am_code_calc_acnt.acnt_code  || ')' ) as name
					FROM am_code_calc_acnt,
						 am_code_acnt
				    WHERE ( am_code_calc_acnt.acnt_code = am_code_acnt.acnt_code ) and
						 ( ( am_code_calc_acnt.calc_kind_code = '000' ) AND
						 ( am_code_calc_acnt.calc_list_code = '6112800' ) )
					ORDER BY 2 desc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
		case "거주구분":
			$azsql =" SELECT valid_value_code as code,
								valid_value_name as name
							FROM am_code_validation
							WHERE validation_code = '13'
							ORDER BY valid_value_code ASC ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
		case "소득구분2":
			$azsql =" SELECT valid_value_code as code,
							valid_value_name as name
							FROM am_code_validation
							WHERE validation_code = '06' and valid_value_level='2'
							ORDER BY valid_value_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "전표처리시스템":
			$azsql ="SELECT SYS_ID as code,SYS_NAME as name FROM SM_AUTH_SYS WHERE USE_YN = 'Y' ORDER BY 1";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "재무결산구분2":
			$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code='16'";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "매출증빙발행":
			$query_data = array();
			$item=array("CODE"=>"001","NAME"=>"전표->증빙작성");
			array_push($query_data,$item);
			$item=array("CODE"=>"002","NAME"=>"증빙->전표연계");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "YN":
			//Y,N
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"Y");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"N");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "YN2":
			//Y,N
			$query_data = array();
			$item=array("CODE"=>"","NAME"=>"");
			array_push($query_data,$item);
			$item=array("CODE"=>"Y","NAME"=>"Y");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"N");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "은행분류":
			
			$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation  WHERE validation_code = '28' ORDER BY valid_value_code ASC   ";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
		case "출력여부":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"출력");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"제외");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "카드사용구분":
			
			$azsql =" SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code = 'C2' ORDER BY valid_value_code ASC ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
		case "법인카드구분":
			
			$azsql =" SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code = 'C1' ORDER BY valid_value_code ASC ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "출력위치":
			
			$azsql ="select valid_value_code as code, valid_value_name as name from am_code_validation where validation_code='23' order by valid_value_code asc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "출력라인":
			
			$azsql ="select valid_value_code as code, valid_value_name as name from am_code_validation where validation_code='22' order by valid_value_code asc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "출력차대":
			$query_data = array();
			$item=array("CODE"=>"0","NAME"=>"차/대");
			array_push($query_data,$item);
			$item=array("CODE"=>"1","NAME"=>"차변");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"대변");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "출력차대2":
			$query_data = array();
			$item=array("CODE"=>"","NAME"=>"");
			array_push($query_data,$item);
			$item=array("CODE"=>"0","NAME"=>"차/대");
			array_push($query_data,$item);
			$item=array("CODE"=>"1","NAME"=>"차변");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"대변");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "산식종류":
			
			$azsql ="SELECT calc_kind_code  as code,calc_kind_name as name FROM am_code_calc_kind ORDER BY am_code_calc_kind.calc_kind_code ASC   		";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "원가종류구분":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"일반");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"원가");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			//신규추가-------------------------------------------------------------------------
			
			
			
		case "고정자산계정":
			$azsql ="
					  SELECT '0' rowstatus,
						   	am_code_kind.company_code,
						   	am_code_kind.fa_kind_code,
					         am_code_kind.fa_kind_name  as name,
					         am_code_kind.acnt_code  as code,
					         am_code_kind.acnt_code_appro,
					         am_code_kind.fa_repay_div ,
						   	am_code_kind.fa_div,
						   	am_code_kind.acnt_code_treasury,
						   	am_code_kind.materiality_asset_tag
					    FROM am_code_kind
						WHERE am_code_kind.company_code ='11'
					ORDER BY am_code_kind.fa_kind_code ASC
					";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "고정자산계정2":
			$azsql ="
						select distinct acnt_code as code,
						( select acnt_name
							   from am_code_acnt
							   where acnt_code = b.acnt_code ) as name
						from am_asst_master	 b
						order by 1
					";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "고정자산계정3":
			$azsql ="
					SELECT am_code_calc_acnt.acnt_code AS CODE,
           				am_code_acnt.acnt_name AS NAME
        			FROM am_code_calc_acnt,
           				 am_code_acnt
					WHERE (am_code_calc_acnt.acnt_code =am_code_acnt.acnt_code)
					AND   ((am_code_calc_acnt.calc_kind_code = '000')
					AND   (am_code_calc_acnt.calc_list_code = '1220100'))
					ORDER BY 1 ASC
				";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "상각구분":
			$azsql ="
					  SELECT am_code_validation.valid_value_code as code,
					         am_code_validation.valid_value_name as name
					    FROM am_code_validation
					   WHERE am_code_validation.validation_code = '15'
					ORDER BY am_code_validation.valid_value_code ASC
					";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "세무사업장":
			$azsql =" SELECT tax_comp_code as code, tax_comp_name as name FROM am_code_taxcomp where company_code = '11' ORDER BY tax_comp_code ASC ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "REPAY_YYMM":  //171020
			$azsql =" SELECT max(REPAY_YYMM) as code, max(REPAY_YYMM) as name FROM AM_ASST_SUM_MONTH WHERE COMPANY_CODE = '11' ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "증빙종류": //170914
			$azsql ="SELECT valid_value_code as code, valid_value_name as name FROM am_code_validation WHERE validation_code = '12' and valid_value_level = '1' ORDER BY valid_value_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "증빙종류2":
			$azsql ="SELECT valid_value_code as code, valid_value_name as name FROM am_code_validation WHERE validation_code = '12'  ORDER BY valid_value_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "매출분류": //170926
			$azsql ="Select Valid_Value_Code as code
						  , Valid_Value_Name as name
					  From Am_Code_Validation
					 Where Validation_Code = '44'
					   And Use_Yn = 'Y'
					 Union All
					Select Null
						 , Null
					  From Dual";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "건물관리구분":
			$azsql ="SELECT valid_value_code as code, valid_value_name as name FROM am_code_validation WHERE validation_code = '38' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "카드회사": //170925
			$azsql ="SELECT   cd_card_comp_code as code, cd_card_comp_name as name FROM am_code_cd_comp ORDER BY cd_card_comp_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "소득구분": //170927
			$azsql ="
			      Select am_code_income.income_code       as code
						 , am_code_income.income_name     as name
						 , am_code_validation.valid_value_name
					  From am_code_income
						 , am_code_validation
					 Where am_code_validation.validation_code = '06'
					   And am_code_validation.valid_value_level = '2'
					   And am_code_validation.use_yn = 'Y'
					   And am_code_income.income_div = am_code_validation.valid_value_code
			";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
		case "고정자산분류":
			$azsql ="
			     SELECT am_code_validation.valid_value_code as code,
				         am_code_validation.valid_value_name as name
				    FROM am_code_validation
				   WHERE am_code_validation.validation_code = '48'
				ORDER BY am_code_validation.valid_value_code ASC
			";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "고정자산충당금계정":
			$azsql ="
					SELECT  am_code_calc_acnt.acnt_code as code ,
					am_code_acnt.acnt_name as name
					FROM am_code_calc_acnt ,
					am_code_acnt
					WHERE ( am_code_calc_acnt.acnt_code = am_code_acnt.acnt_code ) and          ( ( am_code_calc_acnt.calc_kind_code = '000' ) And          ( am_code_calc_acnt.calc_list_code = '1220301' ) )
					ORDER BY am_code_calc_acnt.acnt_code          ASC
					";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "고정자산계정과목": //171024
			$azsql ="
					SELECT  am_code_calc_acnt.acnt_code as code ,
					am_code_acnt.acnt_name as name
					FROM am_code_calc_acnt ,
					am_code_acnt
					WHERE ( am_code_calc_acnt.acnt_code = am_code_acnt.acnt_code ) and          ( ( am_code_calc_acnt.calc_kind_code = '000' ) And          ( am_code_calc_acnt.calc_list_code = '1220100' ) )
					ORDER BY am_code_calc_acnt.acnt_code          ASC
					";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "국고보조금계정":
			$azsql ="
			  SELECT am_code_calc_acnt.acnt_code as code ,
			         am_code_acnt.acnt_name  as name
			    FROM am_code_calc_acnt ,
			         am_code_acnt
			   WHERE ( am_code_calc_acnt.acnt_code =am_code_acnt.acnt_code )
				  and ( am_code_calc_acnt.calc_kind_code = '000' )
			     And ( am_code_calc_acnt.calc_list_code = '50151501' )
			ORDER BY am_code_calc_acnt.acnt_code          ASC
					
			";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "유/무형자산":
			$query_data = array();
			$item=array("CODE"=>"","NAME"=>"");
			array_push($query_data,$item);
			$item=array("CODE"=>"0","NAME"=>"유형");
			array_push($query_data,$item);
			$item=array("CODE"=>"1","NAME"=>"무형");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
		case "본부별부서": //171017
			$azsql ="   SELECT A.DEPT_CODE as code,
								   A.DEPT_NAME as name
							  FROM SM_CODE_DEPT A,
								   (SELECT HEADQUATER_CODE
									  FROM SM_CODE_DEPT
									 WHERE COMPANY_CODE = '11'
									   AND DEPT_CODE    = '$etc_param') B
							 WHERE A.HEADQUATER_CODE 	= B.HEADQUATER_CODE
							   AND A.USE_YN = 'Y'	";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "상위계정":
			$azsql ="SELECT
						am_code_acnt_mast.acnt_code as code
						,( am_code_acnt_mast.acnt_name || '  (' || am_code_acnt_mast.acnt_code  || ')' ) as name
						FROM am_code_acnt_mast ORDER BY 2 ASC ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "계정레벨":
			$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code='01' and valid_value_code < 5 ORDER BY valid_value_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "정산차대":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"차변");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"대변");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
		case "웹상태":  //170914
			$query_data = array();
			$item=array("CODE"=>"RDY","NAME"=>"준비");
			array_push($query_data,$item);
			$item=array("CODE"=>"SND","NAME"=>"전달");
			array_push($query_data,$item);
			$item=array("CODE"=>"RCV","NAME"=>"수신");
			array_push($query_data,$item);
			$item=array("CODE"=>"ACK","NAME"=>"승인");
			array_push($query_data,$item);
			$item=array("CODE"=>"CAN","NAME"=>"반려");
			array_push($query_data,$item);
			$item=array("CODE"=>"ERR","NAME"=>"에러");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "전표결재상태":  //170914
			$query_data = array();
			$item=array("CODE"=>"0","NAME"=>"");
			array_push($query_data,$item);
			$item=array("CODE"=>"1","NAME"=>"반려");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"취소");
			array_push($query_data,$item);
			$item=array("CODE"=>"4","NAME"=>"신청부서");
			array_push($query_data,$item);
			$item=array("CODE"=>"5","NAME"=>"검토부서");
			array_push($query_data,$item);
			$item=array("CODE"=>"6","NAME"=>"결재완료");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "청구영수구분": //170914
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"청구");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"영수");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
		case "예산통제구분":
			$query_data = array();
			$item=array("CODE"=>"0","NAME"=>"통제안함");
			array_push($query_data,$item);
			$item=array("CODE"=>"1","NAME"=>"통제");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "잔액명세종류":
			$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code='04' ORDER BY valid_value_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "잔액명세종류":
			$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code='04' ORDER BY valid_value_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "계정코드마스트계정구분":
			$azsql ="SELECT valid_value_code as code,( am_code_validation.valid_value_name || '  (' || am_code_validation.valid_value_code || ')' ) as name FROM am_code_validation  WHERE validation_code = '03' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "배부기준":
			$azsql ="select valid_value_code as code, valid_value_name as name from am_code_validation where validation_code='83' and valid_value_code > '4' order by valid_value_code asc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "배부차수":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"공통배부");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"차수배부");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "배부회차":
			$query_data = array();
			$item=array("CODE"=>"0","NAME"=>"직접");
			array_push($query_data,$item);
			$item=array("CODE"=>"1","NAME"=>"공통배부");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"차수배부");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"기타배부");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "기준":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"PM");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"배분");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "원가구분":
			$query_data = array();
			$item=array("CODE"=>"9","NAME"=>"총원가");
			array_push($query_data,$item);
			$item=array("CODE"=>"0","NAME"=>"직접");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"간접");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "원가종류":
			$azsql ="select cost_kind_code as code, cost_kind_name as name from am_code_cost_kind order by 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "원가종류2":
			$azsql ="select '' as code, '&nbsp;' as name from DUAL
					UNION
					select cost_kind_code as code, cost_kind_name as name from am_code_cost_kind
					order by 1 desc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "양식종류":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"사업코드별");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"원가부서별");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "연산자":
			$query_data = array();
			$item=array("CODE"=>"+","NAME"=>"+");
			array_push($query_data,$item);
			$item=array("CODE"=>"-","NAME"=>"-");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "예적금계정": //계정
			$azsql ="select acnt_code as code, acnt_name as name from am_code_acnt where nvl(bank_yn,'N') = 'Y' ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "계정":
			//array[0] : 10112901|관계회사단기대여금
			//case추가시 사용편의성을 위하여 결과값 1~3개정도 기입바람
			$azsql ="select acnt_code as code, acnt_name as name from am_code_acnt where bank_yn is not null and deposit_yn is not null order by 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "계정1":
			//array[0] : 101305|가설재
			//array[1] : 10130501|가설재
			//case추가시 사용편의성을 위하여 결과값 1~3개정도 기입바람
			$azsql ="SELECT distinct c.acnt_code as code, ( c.acnt_name || ' (' || c.acnt_code || ')' ) as name FROM am_code_acnt a, vw_am_code_acnt_upper b,am_code_acnt_mast c WHERE a.acnt_code = b.acnt_code and b.acnt_level > 3 and b.acnt_parent = c.acnt_code and a.use_yn = 'Y' and a.cust_yn = 'Y' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "계정2":
			//array[0] : 101305|가설재
			//array[1] : 201117|가수금
			//case추가시 사용편의성을 위하여 결과값 1~3개정도 기입바람
			$azsql ="select m.acnt_code as code, ( m.acnt_name || ' (' || m.acnt_code || ')' ) as name from am_code_acnt_mast m where m.acnt_level = '004' and m.acnt_div < '4' union all select c.acnt_code, (m.acnt_name || ' (' || c.acnt_code || ')' ) as acnt_name from am_code_calc_acnt c, am_code_acnt_mast m where c.acnt_code = m.acnt_code and calc_kind_code = '000' and 	calc_list_code = '90110101' order by 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "계정3":
			$query_data = array();
			$item=array("CODE"=>"10114901","NAME"=>"전도금(현장사무실)");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "계정4":
			$query_data = array();
			$item=array("CODE"=>"10112301","NAME"=>"받을어음");
			array_push($query_data,$item);
			$item=array("CODE"=>"10112305","NAME"=>"받을어음(전자결재)");
			array_push($query_data,$item);
			$item=array("CODE"=>"20110301","NAME"=>"지급어음(외상매입금)");
			array_push($query_data,$item);
			$item=array("CODE"=>"20110303","NAME"=>"지급어음(일반미지급)");
			array_push($query_data,$item);
			$item=array("CODE"=>"20110305","NAME"=>"지급어음(전자결재)");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "계정구분":
			//array[0] : 1|원가
			//array[1] : 2|손익
			//case추가시 사용편의성을 위하여 결과값 1~3개정도 기입바람
			$azsql ="select valid_value_code as code, valid_value_name as name from am_code_validation where validation_code='81' order by valid_value_code asc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "계정구분1":
			//array[0] : 1|자산
			//array[1] : 2|부채
			//case추가시 사용편의성을 위하여 결과값 1~3개정도 기입바람
			$azsql ="SELECT a.valid_value_code as code, a.valid_value_name as name FROM am_code_validation a WHERE a.validation_code = '03' and a.valid_value_level = '1' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "계정구분1_sub":
			$azsql ="SELECT am_code_acnt_mast.acnt_code as code, (am_code_acnt_mast.acnt_name||'('||am_code_acnt_mast.acnt_code||')') as name FROM am_code_acnt_mast WHERE acnt_level = '004'  and acnt_code like '$output_type%' ORDER BY 2 ASC";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "계정구분1_sub2":
			$azsql ="SELECT am_code_acnt_mast.acnt_code as code, (am_code_acnt_mast.acnt_name||'('||am_code_acnt_mast.acnt_code||')') as name FROM am_code_acnt_mast WHERE acnt_level = '004'  and acnt_code like '$output_type%' ORDER BY 2 ASC";
			$this->oracle->LoadData($azsql,$value_name,"");
			break;
		case "계정구분1_dtl":
			$azsql ="SELECT am_code_acnt_mast.acnt_code as code, (am_code_acnt_mast.acnt_name||'('||am_code_acnt_mast.acnt_code||')') as name FROM am_code_acnt_mast WHERE acnt_level = '005'  and acnt_code like '$output_type%' ORDER BY 2 ASC";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "계정구분2":
			//array[0] : 1|자산
			//array[1] : 101|유동자산
			//array[2] : 103|고정자산
			//case추가시 사용편의성을 위하여 결과값 1~3개정도 기입바람
			$azsql ="SELECT a.valid_value_code as code, ( a.valid_value_name || ' (' || a.valid_value_code || ')' ) as name FROM am_code_validation a WHERE a.validation_code = '03' and a.valid_value_level in ('1', '2') ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
		case "계정구분3":
			//array[0] : 1|자산
			//array[1] : 101|유동자산
			//array[2] : 1011|당좌자산
			//case추가시 사용편의성을 위하여 결과값 1~3개정도 기입바람
			$azsql =" SELECT a.valid_value_code as code, ( a.valid_value_name || ' (' || a.valid_value_code || ')' ) as name FROM am_code_validation a WHERE a.validation_code = '03' and a.valid_value_level in ('1', '2') union select a.acnt_code as code, a.acnt_name as name from am_code_acnt_mast a , am_code_validation b where substr(a.acnt_code,1,3) = b.valid_value_code and b.valid_value_level in ('1', '2') and a.acnt_level ='003' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "목계정":
			//array[0] : 101101|현금
			//array[1] : 101103|당좌예금
			//case추가시 사용편의성을 위하여 결과값 1~3개정도 기입바람
			$azsql ="SELECT ACNT_CODE as code, ACNT_NAME as name FROM AM_CODE_ACNT_MAST WHERE ACNT_LEVEL = '004' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "재무제표종류":
			$azsql =" SELECT a.calc_kind_code as code, a.calc_kind_name as name
							FROM am_code_calc_kind a
						 WHERE a.calc_kind_code >= '100' AND (a.calc_kind_code > '399' OR a.calc_kind_code < '300' )
						ORDER BY a.calc_kind_code ASC ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "재무결산구분":
			$query_data = array();
			$item=array("CODE"=>"001","NAME"=>"001|재무");
			array_push($query_data,$item);
			$item=array("CODE"=>"002","NAME"=>"002|결산");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "부서"://(원가부서)
			$azsql ="select dept_code as code, dept_name as name from sm_code_dept where use_yn='Y' order by dept_code";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "부서명":
			$azsql ="select dept_code as code, dept_name as name from sm_code_dept where use_yn='Y' and f_cs_dept_tag(dept_code) in ('1','2','3') order by dept_code";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "부서코드":
			$azsql ="select dept_code as code, dept_name as name from sm_code_dept where use_yn='Y' order by dept_code" ;
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "부서코드2": //170914
			$azsql ="SELECT dept_code as code ,( dept_name || '  (' || dept_code || ')' ) as name, company_code  FROM sm_code_dept WHERE company_code = '11' ORDER BY 1" ;
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "부서코드3": //170914
			$azsql ="SELECT dept_code as code ,( dept_name || '  (' || dept_code || ')' ) as name, company_code FROM sm_code_dept WHERE company_code = '11' AND	use_yn ='Y' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "부서am":
			$azsql ="  SELECT dept_code as code, dept_name as name FROM am_code_dept";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "부서am2": //171010
			$azsql ="SELECT dept_code as code ,( dept_name || '  (' || dept_code || ')' ) as name, company_code FROM am_code_dept WHERE company_code = '11' AND	use_yn ='Y' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "부서am3": //180524
			$azsql ="SELECT dept_code as code ,( dept_name || '  (' || dept_code || ')' ) as name, company_code FROM am_code_dept WHERE company_code = '11'  ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "계정am": //170901
			$azsql ="select acnt_code as code, acnt_name as name  from 	am_code_acnt";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "세무코드":
			$azsql ="  SELECT a.EVIDENCE_CODE as code, a.EVIDENCE_NAME as name, a.SALES_DIV as etc1  FROM AM_CODE_EVIDENCE a  order by 3,1 " ;
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "세무코드2": //170926
			$azsql ="Select
							evidence_code as code
							, evidence_name as name
							, sales_div
							, vat_yn
							, vat_add_yn
							, evidence_kind
							, evidence_div
						From am_code_evidence
						Where use_yn = 'Y'
							and sales_div = '".$etc_param."'
						order by
							vat_yn desc
							, evidence_div
							, evidence_kind
							, code" ;
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "은행":
			$azsql ="select bank_code as code, bank_name as name from am_code_bank order by 2";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "은행1": //170921
			$azsql ="SELECT bank_code as code, bank_name as name FROM am_code_bank WHERE deposit_trans_yn = 'Y' ORDER BY bank_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "어음은행":
			$azsql ="select bank_code as code, bank_code||':'||bank_name as name from am_code_bank where bill_trans_yn = 'Y' order by bank_code asc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "계좌":
			$azsql ="select deposit_no as code, deposit_no||'-'||deposit_name as name from am_code_deposit order by deposit_no asc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "내용년수":
			$azsql ="SELECT contents_year as code, contents_year as name,
							   fixed_rate5,
							   fixed_rate10,
							   fixed_amt
						   FROM am_code_rate
						ORDER BY contents_year ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "계좌선택":
			$azsql ="select bank_code as bank, acnt_code as acnt from am_code_deposit where deposit_no like '$output_type'";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "은행선택":
			$azsql ="select acnt_code as code, deposit_no as name  from am_code_deposit where bank_code like '$output_type'";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "계정과목":
			$azsql ="SELECT ACNT_CODE, ACNT_NAME FROM AM_CODE_ACNT_MAST WHERE ( ( SUBSTR('$output_type',1,1) = '1' AND COST_KIND_CODE <> '11' ) OR (SUBSTR('$output_type',1,1) = '2' AND COST_KIND_CODE = '11')) AND ( ( SUBSTR('$output_type',2,1) = '1' AND LENGTH(ACNT_CODE) <= 6 ) OR (SUBSTR('$output_type',2,1) = '2' AND LENGTH(ACNT_CODE) <= 4)) ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "계정과목2":
			$azsql ="SELECT ACNT_CODE as CODE, ACNT_NAME as NAME FROM AM_CODE_ACNT_MAST WHERE ( ( SUBSTR('$output_type',1,1) = '1' AND COST_KIND_CODE <> '11' ) OR (SUBSTR('$output_type',1,1) = '2' AND COST_KIND_CODE = '11')) AND ( ( SUBSTR('$output_type',2,1) = '1' AND LENGTH(ACNT_CODE) <= 6 ) OR (SUBSTR('$output_type',2,1) = '2' AND LENGTH(ACNT_CODE) <= 4)) ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "계정과목3": //170921
			$azsql ="Select Acnt_Code as code, Acnt_Name as name From Am_Code_Acnt Where Acnt_Code In (Select Acnt_Code From Am_Code_Calc_Acnt Where Calc_Kind_Code = '000' And Calc_List_Code = '50151909') ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "계약사업": //170921
			$azsql ="SELECT PROJ_CODE as code, PROJ_NAME as name FROM SM_CODE_PROJECT WHERE ( SUBSTR(PROJ_CODE,1,1) in ('Y','Z') ) AND ( USE_YN = 'Y' )";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "사전사업": //170921
			$azsql ="SELECT P.PROJ_CODE as code
						 , P.PROJ_NAME as name
					  FROM SM_CODE_PROJECT P, (
					SELECT DISTINCT D.COMPANY_CODE, D.DEPT_CODE
					  FROM AM_SLIP_DETAIL D, SM_CODE_COMPANY C
					 WHERE D.WORK_COMP = C.COMPANY_CODE
					   AND D.CHECK_STATUS = '2'
					   AND D.SLIP_DATE Like SUBSTR(AM_S_DATE,1,4) || '%'
					   AND D.DEPT_CODE LIKE 'X%'
					   AND D.ACNT_CODE LIKE '5%'
					   AND D.ACNT_CODE NOT IN
						( SELECT ACNT_CODE
						   FROM AM_CODE_CALC_ACNT
						  WHERE CALC_KIND_CODE = '000'
							AND CALC_LIST_CODE = '50151909' ) ) D
					 WHERE P.COMPANY_CODE = D.COMPANY_CODE
					   AND P.PROJ_CODE = D.DEPT_CODE";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "변경구분":
			$azsql ="select valid_value_code as code, valid_value_name as name from am_code_validation where validation_code='08' order by valid_value_code asc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "전표종류":
			$satis_user_id= $_SESSION['satis_user_id'];
			
			$azsql =" SELECT
						a.slip_kind_code as code
						, a.slip_kind_name||'['||a.slip_kind_code||']' as name
							FROM am_code_slip_kind a
						   WHERE a.slip_kind_code <> (select decode(am_user_div, 'Y', 'A', 'M', 'A','AM9') from sm_auth_user where user_id = '".$satis_user_id."' )
						ORDER BY a.slip_kind_code ASC   ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "전표종류2":  //171010
			$azsql ="SELECT slip_kind_code as code, slip_kind_name||'['||slip_kind_code||']' as name FROM am_code_slip_kind";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "전표종류3":   //////////자동전표 제외(HR7:임원식대,PM2:외주기성,HR1:급여,RB3:출장,CS1:보험료,AM6:매출,CS2:매출,CM1:외주비)
			$satis_user_id= $_SESSION['satis_user_id'];
			$azsql =" SELECT
						a.slip_kind_code as code
						, a.slip_kind_name||'['||a.slip_kind_code||']' as name
							FROM am_code_slip_kind a
						   WHERE a.slip_kind_code <> (select decode(am_user_div, 'Y', 'A', 'M', 'A','AM9') from sm_auth_user where user_id = '".$satis_user_id."' )
						     and a.auto_yn = 'N'
						ORDER BY a.slip_kind_code ASC   ";
			// and a.slip_kind_code not in ('HR7','PM2','HR1','RB3','CS1','AM6','CS2','CM1') 전표종류등록의 자동발생여부로 수정함. 2020.04.14 류
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "자동전표종류":  //170831
			$query_data = array();
			$item=array("CODE"=>"HR1","NAME"=>"급여자동전표");
			array_push($query_data,$item);
			$item=array("CODE"=>"HR2","NAME"=>"상여자동전표");
			array_push($query_data,$item);
			$item=array("CODE"=>"HR3","NAME"=>"퇴직충당금자동전표");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "검수상태":  //170831
			$query_data = array();
			$item=array("CODE"=>"0","NAME"=>"작성중");
			array_push($query_data,$item);
			$item=array("CODE"=>"1","NAME"=>"가전표");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"확정전표");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "전표구분":  //170914
			$query_data = array();
			$item=array("CODE"=>"001","NAME"=>"지급");
			array_push($query_data,$item);
			$item=array("CODE"=>"002","NAME"=>"대체");
			array_push($query_data,$item);
			$item=array("CODE"=>"003","NAME"=>"수입");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "총괄상태":  //170925
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"진행");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"중지");
			array_push($query_data,$item);
			$item=array("CODE"=>"E","NAME"=>"준공");
			array_push($query_data,$item);
			$item=array("CODE"=>"H","NAME"=>"해제");
			array_push($query_data,$item);
			$item=array("CODE"=>"C","NAME"=>"해지");
			array_push($query_data,$item);
			$item=array("CODE"=>"D","NAME"=>"유보");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "진행상태":
			//진행,중지,준공,유보,해제,해지
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_COMPLETION WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "자산종류":
			$azsql ="select fa_kind_code as code, fa_kind_name as name from am_code_kind order by fa_kind_name";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "자산품목":  //171012
			$azsql ="SELECT a.fa_item_code as code, a.fa_item_name as name FROM am_code_item_asst a WHERE a.company_code = '11' AND a.fa_kind_code = '$output_type' ORDER BY a.fa_item_name";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "자산코드":  //171012
			$azsql ="SELECT a.fa_asset_code as code, a.fa_asset_name as name FROM am_code_asset a WHERE a.company_code = '11' AND a.fa_item_code = '$output_type' ORDER BY a.fa_asset_name";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
			
		case "차입종류": //170921
			$azsql ="  SELECT valid_value_code as code, valid_value_name as name FROM am_code_validation WHERE validation_code = '62' ORDER BY valid_value_code ASC  ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "증권종류": //170927
			$azsql ="SELECT SECU_TYPE_CODE  as code, STOCK_TYPE_NAME||' ['||SECU_TYPE_CODE||']' as name FROM AM_STCK_TYPE";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "am_code_validation": //170921
			//validation_code = 47  : 본사현장구분
			//validation_code = 62  : 차입종류
			//validation_code = 71	: 차입이자지급방법
			//validation_code = 72	: 통화구분
			//validation_code = C2	: 법인카드-사용구분
			//validation_code = C1	: 법인카드-구분
			//validation_code = 97	: 결재상태
			$azsql =" SELECT valid_value_code as code, valid_value_name as name FROM am_code_validation WHERE validation_code = '$etc_param'  ORDER BY valid_value_code ASC ";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "am_code_validation2": //170927
			//validation_code = 62  : 차입종류
			//validation_code = 65  : 매출구분
			//validation_code = 66  : 접대지역
			//validation_code = 67  : 영수증수취구분
			//validation_code = 68  : 어음종류
			//validation_code = 71	: 차입이자지급방법
			//validation_code = 72	: 통화구분
			//validation_code = 79  : 어음구분
			//validation_code = C2	: 법인카드-사용구분
			//validation_code = C1	: 법인카드-구분
			$azsql =" SELECT valid_value_code as code, valid_value_name as name FROM am_code_validation WHERE validation_code = '$etc_param' And use_yn = 'Y' ORDER BY valid_value_code ASC ";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "차입계정": //170921
			$azsql ="select a.acnt_code as code, a.acnt_name as name
						  from am_code_acnt a,
								 vw_am_calc_list_acnt_join b
						 where a.acnt_code = b.acnt_code
						 and   b.calc_kind_code = '000'
						 and	  b.calc_list_code = '99990701'
						order by a.acnt_code";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "전문분야":
			$azsql ="select class_code as code, class_name as name from cs_code_class where class_tag = 'KA' order by sort_order";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "근무형태":
			$azsql ="select class_code as code, class_name as name from cs_code_class where class_tag = 'KD' order by sort_order";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "직위":
			$azsql ="select grade_code as code, grade_name as name from hr_code_grade order by grade_code";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "감리원등급":
			$azsql ="select class_code as code, class_name as name from cs_code_class where class_tag = 'KB' order by sort_order";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "투입등급":
			$azsql ="select class_code as code, class_name as name from cs_code_class where class_tag = 'KC' order by sort_order";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "납부코드":
			$azsql ="select rb_comm_code.etc_code as code, rb_comm_code.etc_name as name from rb_comm_code where ( rb_comm_code.company_code = '11' ) and ( rb_comm_code.sys_id = 'SO' ) and ( rb_comm_code.etc_div = '10' ) and ( rb_comm_code.use_yn = 'Y' ) order by nvl(rb_comm_code.orders,0) asc, rb_comm_code.etc_code asc ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "원천세신고처":
			$azsql ="SELECT sour_report_div as code, sour_report_name as name FROM am_code_sour_div ORDER BY sour_report_name ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "잔액관리방법":
			$azsql ="SELECT a.valid_value_code as code, a.valid_value_name as name FROM am_code_validation a WHERE a.validation_code = '41' ORDER BY a.valid_value_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "잔액관리방법_sub":
			//$azsql ="SELECT a.acnt_code as code, ( a.acnt_name || '  (' || a.acnt_code  || ')' ) as name FROM am_code_acnt a WHERE a.bond_method = '$output_type' AND a.bond_yn = 'Y'  order by 1 ";
			$azsql ="SELECT a.acnt_code as code, (  a.acnt_code || ' | '  ||  a.acnt_name || '  (' || a.acnt_code  || ')' ) as name FROM am_code_acnt a WHERE a.bond_method = '$etc_param' AND a.bond_yn = 'Y'  order by 1 ";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "잔액관리방법_all":
			//$azsql ="SELECT a.acnt_code as code, ( a.acnt_name || '  (' || a.acnt_code  || ')' ) as name FROM am_code_acnt a WHERE a.bond_method = '$output_type' AND a.bond_yn = 'Y'  order by 1 ";
			$azsql ="SELECT a.acnt_code as code, (  a.acnt_code || ' | '  ||  a.acnt_name || '  (' || a.acnt_code  || ')' ) as name FROM am_code_acnt a WHERE a.bond_method = '$etc_param' order by 1 ";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			//$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
			
		case "차입은행"://PersonLicense_Screen01
			$azsql ="SELECT am_code_bank.bank_code as code, am_code_bank.bank_name as name FROM am_code_bank ORDER BY 2";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "부문":
			$query_data = array();
			$item=array("CODE"=>"11","NAME"=>"조달-경상부문");
			array_push($query_data,$item);
			$item=array("CODE"=>"12","NAME"=>"조달-재무부문");
			array_push($query_data,$item);
			$item=array("CODE"=>"21","NAME"=>"운용-경상부문");
			array_push($query_data,$item);
			$item=array("CODE"=>"22","NAME"=>"운용-재무부문");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "증빙종류3"://매출/매입 명세서_증빙종류
			$query_data = array();
			$item=array("CODE"=>"01","NAME"=>"세금계산서");
			array_push($query_data,$item);
			$item=array("CODE"=>"02","NAME"=>"계산서");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "세무코드3"://매출/매입 명세서_증빙코드
			$azsql ="SELECT am_code_evidence.evidence_code AS CODE,
 			        am_code_evidence.evidence_name AS NAME,
         			am_code_evidence.evidence_kind,
         			am_code_evidence.sales_div
    				FROM am_code_evidence
					ORDER BY am_code_evidence.evidence_code ASC" ;
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "명세서정렬":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"전표번호 순");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"사업자번호 순");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"증빙일자 순");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "분기":
			$query_data = array();
			$item=array("CODE"=>"0103","NAME"=>"1/4분기");
			array_push($query_data,$item);
			$item=array("CODE"=>"0406","NAME"=>"2/4분기");
			array_push($query_data,$item);
			$item=array("CODE"=>"0709","NAME"=>"3/4분기");
			array_push($query_data,$item);
			$item=array("CODE"=>"1012","NAME"=>"4/4분기");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "주관여부":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"주관");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"비주관");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "납부상태":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"미납");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"납부");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"완료");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "차량렌탈업체":
			$azsql ="SELECT DISTINCT A.CUST_CODE AS CODE, B.CUST_NAME AS NAME FROM RB_CAR_MASTER A LEFT OUTER JOIN SM_CODE_CUST B ON A.CUST_CODE = B.CUST_CODE ORDER BY B.CUST_NAME";
			if($output_type=="array" || $output_type=="ARRAY"){
				$re_data = $this->oracle->LoadData($azsql,$value_name,$output_type);
				return $re_data;
			}else{
				$this->oracle->LoadData($azsql,$value_name,$output_type);
			}
			break;
		case "차량사용여부":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"사용");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"반납");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "최근마감일자":
			//AccountQueryCode("현장_차량관리", $value_name, $output_type, $etc_param="")
			$azsql ="
				SELECT
				MAX(END_DATE) as code
				FROM AM_ENDX_HIS_DAY
				";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
	}
}//AccountQueryCode

//=============================================================================================
// 구매관련 공통 코드
//=============================================================================================
function PurchaseQueryCode($mode,$value_name,$output_type, $etc="")
{
	extract($_REQUEST);
	switch($mode)
	{
		case "납품장소":
			$query_data = array();
			$item=array("CODE"=>"유니온빌딩 4층","NAME"=>"유니온빌딩 4층");
			array_push($query_data,$item);
			$item=array("CODE"=>"유니온빌딩 5층","NAME"=>"유니온빌딩 5층");
			array_push($query_data,$item);
			$item=array("CODE"=>"유니온빌딩 6층","NAME"=>"유니온빌딩 6층");
			array_push($query_data,$item);
			$item=array("CODE"=>"영덕빌딩 1층","NAME"=>"영덕빌딩 1층");
			array_push($query_data,$item);
			$item=array("CODE"=>"영덕빌딩 2층","NAME"=>"영덕빌딩 2층");
			array_push($query_data,$item);
			$item=array("CODE"=>"영덕빌딩 3층","NAME"=>"영덕빌딩 3층");
			array_push($query_data,$item);
			$item=array("CODE"=>"영덕빌딩 4층","NAME"=>"영덕빌딩 4층");
			array_push($query_data,$item);
			$item=array("CODE"=>"영덕빌딩 5층","NAME"=>"영덕빌딩 5층");
			array_push($query_data,$item);
			$item=array("CODE"=>"영덕빌딩 6층","NAME"=>"영덕빌딩 6층");
			array_push($query_data,$item);
			$item=array("CODE"=>"영덕빌딩 7층","NAME"=>"영덕빌딩 7층");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			//구매
		case "관리부서":
			$azsql ="SELECT rb_comm_code.etc_code as code,
						 rb_comm_code.etc_name as name
					FROM rb_comm_power,
							( SELECT etc_code,
								 etc_name,
										orders
							 FROM rb_comm_code
							WHERE company_code = '11'
							 AND sys_id = 'RB'
							 AND etc_div = '01' ) rb_comm_code
				 WHERE ( rb_comm_code.etc_code = rb_comm_power.manage_dept_div ) AND
						 ( ( rb_comm_power.company_code = '11' ) AND
						 ( rb_comm_power.emp_no = '$etc' ) AND
						 ( rb_comm_power.sys_id = 'RB' ) AND
						 ( rb_comm_power.use_yn = 'Y' ) )
				ORDER BY rb_comm_code.orders ASC,
						 rb_comm_code.etc_code ASC	";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "관리부서2":
			$azsql =" SELECT etc_code as code, etc_name as name FROM rb_comm_code WHERE company_code = '11'	 AND sys_id = 'RB'	 AND etc_div = '01' 	";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "등록상태":
			//1.작성,2.결재,3.청구,4.접수,5.발주,6.납품,7.정산
			$azsql ="SELECT etc_code as code, etc_name as name FROM rb_comm_code WHERE ( company_code = '11' ) AND ( sys_id = 'RB' ) AND ( etc_div = '02' )AND ( use_yn = 'Y' )";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "등록상태2":
			//1.작성,2.결재,3.청구,4.접수,5.발주
			$azsql ="SELECT etc_code as code, etc_name as name FROM rb_comm_code WHERE ( company_code = '11' ) AND ( sys_id = 'RB' ) AND ( etc_div = '02' )AND ( use_yn = 'Y' and etc_code <6 )";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "본사현장구분":
			$azsql ="  SELECT rb_comm_code.etc_code as code, rb_comm_code.etc_name as name
							FROM rb_comm_code
						   WHERE ( rb_comm_code.company_code = '11' ) AND
								 ( rb_comm_code.sys_id = 'RB' ) AND
								 ( rb_comm_code.etc_div = '03' )AND
								 ( rb_comm_code.use_yn = 'Y' )
						   ORDER BY nvl(rb_comm_code.orders,0) ASC,
								 rb_comm_code.etc_code ASC  ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//자산
		case "이력구분":  //171031
			//01.신규,02.이동,03.교정,04.수리,05.변경,06.매각,07.폐기,08.손망실
			$azsql ="  SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID  = 'RM' AND ETC_DIV = '04' AND USE_YN  = 'Y'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "취득구분":  //171031
			$azsql ="  SELECT etc_code as code, etc_name as name FROM rb_comm_code WHERE company_code = '11' AND sys_id  = 'RM' AND etc_div = '02' AND use_yn  = 'Y'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "자산위치":  //171031
			$azsql =" SELECT place_code as code,  place_name as name FROM rm_code_place WHERE company_code = '11' ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "자산관리분류":  //171031
			$azsql =" SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID  = 'RM' AND ETC_DIV = '08' AND USE_YN  = 'Y' order by 1 ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "자산처분구분":  //171031
			$azsql =" SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID  = 'RM' AND ETC_DIV = '03' AND USE_YN  = 'Y' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "자산구분":  //171031
			$azsql =" SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID  = 'RM' AND ETC_DIV = '01' AND USE_YN  = 'Y' ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "자산상태":  //171031
			$azsql =" SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID  = 'RM' AND ETC_DIV = '05' AND USE_YN  = 'Y' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "명칭코드":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"명칭");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"코드");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "전산자산거래처": //190404
			$azsql ="
						  SELECT DISTINCT a.CUST_CODE as code,  b.CUST_NAME as name
						    FROM RM_CODE_ITEM a,
						         SM_CODE_CUST b
						   WHERE ( a.CUST_CODE = b.CUST_CODE ) and
						         ( ( a.company_code = '11') AND
						         ( a.manage_dept_div = '50' ) AND
						         ( a.acnt_code is not null ) AND
						         ( a.use_yn = 'Y' ) )
						";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "자산관리부서":  //171031
			//				$azsql =" SELECT rb_comm_code.etc_code as code,
			//								 rb_comm_code.etc_name as name
			//							FROM rb_comm_power,
			//									(  SELECT etc_code,
			//											  etc_name,
			//													orders
			//										 FROM rb_comm_code
			//										WHERE company_code = '11'
			//										  AND sys_id  = 'RM'
			//										  AND etc_div = '09'
			//										  AND use_yn  = 'Y') rb_comm_code
			//						   WHERE ( rb_comm_code.etc_code = rb_comm_power.manage_dept_div ) AND
			//								 ( ( rb_comm_power.company_code = '11' ) AND
			//								   ( rb_comm_power.emp_no = '$userid' ) AND
			//								   ( rb_comm_power.sys_id = 'RM' ) AND
			//								   ( rb_comm_power.use_yn = 'Y' ) )
			//						ORDER BY rb_comm_code.orders ASC,
			//								 rb_comm_code.etc_code ASC   	";
			
			$azsql ="  SELECT etc_code as code,
								  etc_name as name,
										orders
							 FROM rb_comm_code
							WHERE company_code = '11'
							  AND sys_id  = 'RM'
							  AND etc_div = '09'
							  AND use_yn  = 'Y'
							order by 1					 ";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
	}
}
//=============================================================================================
// 도서관련 공통 코드
//=============================================================================================
function BookQueryCode($mode,$value_name,$output_type)
{
	extract($_REQUEST);
	switch($mode)
	{
		case "대출구분":
			$azsql ="SELECT rb_comm_code.etc_code as code,
							 rb_comm_code.etc_name as name,
							 nvl(rb_comm_code.orders,0) orders
						 FROM rb_comm_code
						 WHERE ( rb_comm_code.company_code = '11' )
						 AND ( rb_comm_code.sys_id = 'LI' )
						 AND ( rb_comm_code.etc_div = '09' )
						 AND ( rb_comm_code.use_yn = 'Y' )";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "자료구분":
			$azsql ="	SELECT rb_comm_code.etc_code as code,
						 rb_comm_code.etc_name as name,
						 nvl(rb_comm_code.orders,0) orders
					FROM rb_comm_code
				 WHERE ( rb_comm_code.company_code = '11' ) AND
						 ( rb_comm_code.sys_id = 'LI' ) AND
						 ( rb_comm_code.etc_div = '03' ) AND
						 ( rb_comm_code.use_yn = 'Y' ) ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "관리상태":
			$azsql =" SELECT rb_comm_code.etc_code as code,
						 rb_comm_code.etc_name as name ,
						 nvl(rb_comm_code.orders,0) orders
					FROM rb_comm_code
				 WHERE ( rb_comm_code.company_code = '11' ) AND
						 ( rb_comm_code.sys_id = 'LI' ) AND
						 ( rb_comm_code.etc_div = '04' ) AND
						 ( rb_comm_code.use_yn = 'Y' ) ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "문서분류대":
			$azsql ="select class_cd as code, class_name as name from doc_class where class_level = 1 order by 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "문서분류중":
			$azsql ="select class_cd as code, class_name as name from doc_class where class_level = 2 and class_cd like '$output_type%' order by 1";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "문서분류소":
			$azsql ="select class_cd as code, class_name as name from doc_class where class_level = 3 and class_cd like '$output_type%' order by 1";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
			
			
			
	}
}

//=============================================================================================
// 프로젝트 공통 코드
//=============================================================================================
function ProjectQueryCode($mode,$value_name,$output_type)
{
	extract($_REQUEST);
	switch($mode)
	{
		
		case "기성확정":
			$query_data = array();
			$item=array("CODE"=>"00","NAME"=>"미청구");
			array_push($query_data,$item);
			$item=array("CODE"=>"01","NAME"=>"청구");
			array_push($query_data,$item);
			$item=array("CODE"=>"02","NAME"=>"확정");
			array_push($query_data,$item);
			$item=array("CODE"=>"03","NAME"=>"취소");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "진행상태2":
			//진행,중지,준공,유보,해제,해지
			$azsql =" SELECT ETC_CODE as code,ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM'  AND ETC_DIV = '14' AND HIDE_YN = 'N' AND USE_YN = 'Y' ORDER BY ORDERS ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "MH작업부서":
			$azsql ="SELECT DEPT_CODE AS CODE, DEPT_NAME AS NAME FROM SM_CODE_DEPT WHERE DEPT_DIV = 'S' AND USE_YN   = 'Y'  AND DEPT_CODE <> 'ZZZZZZ'  AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "설계감리공통":
			$query_data = array();
			$item=array("CODE"=>"%","NAME"=>"설계/감리공통");
			array_push($query_data,$item);
			$item=array("CODE"=>"A","NAME"=>"설계공통");
			array_push($query_data,$item);
			$item=array("CODE"=>"B","NAME"=>"감리공통");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "작업공종":  //activity_code로 변경할것.
			$azsql ="select work_code as code,work_name as name from pm_code_work where company_code ='11' and level_div='2' and use_tag= 'Y'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "activity_code": //18.01.31  //작업공종 NEW
			//$azsql ="SELECT  PRODUCT_CODE||ACTIVITY_CODE AS code,  '['||PRODUCT_NAME||'] '||ACTIVITY_NAME AS name FROM CS_CONT_MAP_ACTIVITY";
			//200228 감리간접,대기,자기개발 제외(선우현)
			$azsql ="SELECT  PRODUCT_CODE||ACTIVITY_CODE AS code,  '['||PRODUCT_NAME||'] '||ACTIVITY_NAME AS name FROM CS_CONT_MAP_ACTIVITY WHERE PRODUCT_CODE||ACTIVITY_CODE NOT in ('Vh','Vg','Vf')";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "미달구분":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"전체");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"미달분");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "기간구분":  //2018.01.29
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"지불일자");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"발행일자");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "투입구분":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"투입");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"미투입");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "본부":
			//수자원개발사업본부,~상하수도사업본부
			$azsql =" SELECT HEADQUATER_CODE as code,
							HEADQUATER_NAME as name
					 FROM SM_CODE_HEADQUATER
					 WHERE USE_YN = 'Y'
						AND HEADQUATER_code >= 'J0000' ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "부서":
			//해외사업실~공통
			$azsql ="select dept_code as code, dept_name as name from sm_code_dept where use_yn='Y' order by dept_code";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "직급":
			$azsql ="SELECT LEVEL_CODE as code, LEVEL_NAME as name FROM HR_CODE_LEVEL WHERE USE_YN = 'Y' ORDER BY ORDER_SEQ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "직급2":
			$azsql ="SELECT LEVEL_CODE as code, LEVEL_NAME as name FROM HR_CODE_LEVEL WHERE USE_YN = 'Y' or LEVEL_CODE='01' ORDER BY CODE";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "사용여부":
			$query_data = array();
			$item=array('CODE'=>'Y','NAME'=>'Y');
			array_push($query_data,$item);
			$item=array('CODE'=>'N','NAME'=>'N');
			array_push($query_data,$item);
			if($output_type=="json" || $output_type=="JSON"){
				$query_data= urldecode(json_encode($query_data));
				echo $query_data;
			}else if($output_type=="array" || $output_type=="ARRAY"){
				return $query_data;
			}else{
				$this->smarty->assign($value_name,$query_data);
			}
			break;
			
		case "사업구분": case "부서구분":
			//설계,감리
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_PROJ_TAG WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "이월구분": case "이월진행":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"이월사업");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"종료사업");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "진행상태": case "진행구분":
			//진행,중지,준공,유보,해제,해지
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_COMPLETION WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
			
			
		case "작성구분":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"미작성");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"작성중");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"승인완료");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			//미작성,작성중,승인완료
			break;
			
		case "작성구분2":
			$query_data = array();
			$item=array("CODE"=>"00","NAME"=>"미작성");
			array_push($query_data,$item);
			$item=array("CODE"=>"10","NAME"=>"과업수행 작성중");
			array_push($query_data,$item);
			$item=array("CODE"=>"20","NAME"=>"과업수행 배분중");
			array_push($query_data,$item);
			$item=array("CODE"=>"30","NAME"=>"과업수행 배분완료");
			array_push($query_data,$item);
			$item=array("CODE"=>"40","NAME"=>"실행예산 편성중");
			array_push($query_data,$item);
			$item=array("CODE"=>"50","NAME"=>"실행예산 편성완료");
			array_push($query_data,$item);
			$item=array("CODE"=>"60","NAME"=>"승인 대기중");
			array_push($query_data,$item);
			$item=array("CODE"=>"70","NAME"=>"승인 완료");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "계약구분":
			//일반계약,직접경비,공동도급,외국회사,턴키설계,개인계약
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE='11' AND SYS_ID='PM' AND ETC_DIV='09' AND HIDE_YN='N' AND USE_YN='Y' order by ORDERS";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
		case "최종결재자":
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM' AND ETC_DIV = '01' AND HIDE_YN = 'N' AND USE_YN = 'Y' ORDER BY ORDERS";
			$temp = $this->oracle->LoadData($azsql,$value_name,$output_type);
			return $temp;
			//A1 담당자
			//A2 PM
			//A3 사업부서장
			//A4 사업본부장
			//A5 사업관리담당자
			//A6 사업관리담당2
			//A7 사업관리부서장
			//A8 사업관리본부장
			//AZ 대표이사
			//B1 작성자(외주)
			//B2 검토자(외주)
			//B3 부서장(외주)
			//B4 본부장(외주)
			break;
			
		case "결재구분":
			//00 결재대기,10 결재완료,20 반려
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM' AND ETC_DIV = '06' AND HIDE_YN = 'N' AND USE_YN = 'Y' ORDER BY ORDERS";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "배분여부":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"배분");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"미배분");
			array_push($query_data,$item);
			$item=array("CODE"=>"C","NAME"=>"반려");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "과업수행계획":
			$azsql ="SELECT ETC_CODE AS CODE, ETC_NAME AS NAME FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM' AND ETC_DIV = '04' AND HIDE_YN = 'N' AND USE_YN = 'Y' ORDER BY ORDERS";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			//00 과업수행 미작성
			//10 과업수행 작성중
			//20 과업수행 배분중
			//30 과업수행 배분완료
			//40 실행예산 편성중
			//50 실행예산 편성완료
			//60 승인 대기중
			//70 작성 완료
			break;
			
			
			
			//외주기성
			//외주계약관리
		case "보증구분":
			//선급보증, 계약보증, 하자보증
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM' AND ETC_DIV = '10' AND HIDE_YN = 'N' AND USE_YN = 'Y' ORDER BY ORDERS";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "문서구분":
			//계약문서, 변경계약문서, 기타문서, 기성조서(기성금), 기성조서(준공금), 기성조서(선급금)
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM' AND ETC_DIV = '20' AND HIDE_YN = 'N' AND USE_YN = 'Y' ORDER BY ORDERS";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "관리부서":
		case "관리구분":
			//계약담당, 검사담당, 부서장, PM, 본부장
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM' AND ETC_DIV = '15' AND HIDE_YN = 'N' AND USE_YN = 'Y' ORDER BY ORDERS";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//업체별 견적내역 개찰
		case "외주전문공종":
			//계약담당, 검사담당, 부서장, PM, 본부장
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM' AND ETC_DIV = '03' AND HIDE_YN = 'N' AND USE_YN = 'Y' ORDER BY ORDERS";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			//외주품의서 작성 및 조회
			//외주계약관리
		case "과세구분":
			//과세,비과서,영세,면세,원천징수세포함,원천징수세별도
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM' AND ETC_DIV = '07' AND HIDE_YN = 'N' AND USE_YN = 'Y' ORDER BY ORDERS";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//외주계약관리
		case "인지첨부":
			//붙임완료,붙임면제,미붙임
			$azsql ="
							SELECT  ETC_CODE as code,   ETC_NAME as name FROM RB_COMM_CODE
							WHERE COMPANY_CODE = '11'  AND SYS_ID = 'PM'  AND ETC_DIV = '05' AND HIDE_YN = 'N' AND USE_YN = 'Y'
							ORDER BY ORDERS
						";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//외주계약관리
		case "기성지급조건":
			$azsql ="
						SELECT ETC_CODE as code ,ETC_NAME as name FROM RB_COMM_CODE
						WHERE COMPANY_CODE = '11'  AND SYS_ID = 'PM'  AND ETC_DIV = '13' AND HIDE_YN = 'N' AND USE_YN = 'Y'
						ORDER BY ORDERS
					";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//거래처지급계좌등록
		case "거래처구분30":
			//사업자번호,주민등록번호,부서코드..
			$azsql ="SELECT valid_value_code as code, valid_value_name as name FROM am_code_validation WHERE validation_code = '10' ORDER BY valid_value_code ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "부서30":
			//인사총무부(B0300),재경부(C0205)
			$azsql ="SELECT dept_code as code, ( dept_name || '  (' || dept_code || ')' ) as name FROM am_code_dept WHERE company_code = '11' AND	dept_kind in ( '002', '003') AND use_yn ='Y' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "은행30":
			//2자리(숫자,영문) 코드 은행
			$azsql ="SELECT bank_head_code as code, (bank_head_name || ' [' ||bank_head_code || ']') as name FROM am_code_bank_head ORDER BY 2";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//외주기성검토
		case "진행상태31":
			//청구,확정,취소
			$query_data = array();
			$item=array("CODE"=>"01","NAME"=>"청구");
			array_push($query_data,$item);
			$item=array("CODE"=>"02","NAME"=>"확정");
			array_push($query_data,$item);
			$item=array("CODE"=>"03","NAME"=>"취소");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			//외주기성 검사조서 확정(전표처리)
		case "진행상태32":
			//확정,지급확정,유보금지급확정,완료
			$query_data = array();
			$item=array("CODE"=>"02","NAME"=>"확정");
			array_push($query_data,$item);
			$item=array("CODE"=>"06","NAME"=>"지급확정");
			array_push($query_data,$item);
			$item=array("CODE"=>"07","NAME"=>"유보금지급확정");
			array_push($query_data,$item);
			$item=array("CODE"=>"04","NAME"=>"완료");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "진행상태13":  //18.01.29
			//확정,지급확정,완료
			$query_data = array();
			$item=array("CODE"=>"02","NAME"=>"확정");
			array_push($query_data,$item);
			$item=array("CODE"=>"06","NAME"=>"지급확정");
			array_push($query_data,$item);
			$item=array("CODE"=>"04","NAME"=>"완료");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "공동도급구분":  //18.01.29
			//전체(공동도급 제외), 공동도급
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"전체(공동도급 제외)");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"공동도급");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "계약구분32":
			//일반계약,직접경비,공동도급,외국회사,턴키설계,개인계약
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM' AND ETC_DIV = '09' AND HIDE_YN = 'N' AND USE_YN = 'Y' ORDER BY ORDERS";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "지불구분32":
			//현금,어음,현금+어음,구매자금,구매카드,발주처지급조건
			$azsql ="select etc_code as code, etc_name as name from rb_comm_code where sys_id = 'PM' and etc_div = '13' and use_yn = 'Y' and company_code = '11' order by etc_code";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			/*
			 case "사원32":
			 //사번,이름
			 $azsql ="select EMP_NO as code, EMP_NAME as name from HR_PERS_MASTER where company_code='11'";
			 $this->oracle->LoadData($azsql,$value_name,$output_type);
			 break;
			 */
			
			
			
			//외주기성 청구내역 등록
		case "진행상태33":
			//진행중,준공,보류,중지,해지
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE='11' AND SYS_ID='PM' AND ETC_DIV='11' AND HIDE_YN='N' AND USE_YN='Y'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "기성구분33":
			//진행중,준공,보류,중지,해지
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE='11' AND SYS_ID='PM' AND ETC_DIV='12' AND HIDE_YN='N' AND USE_YN='Y' order by ORDERS";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//전도금
			//전도금사전사업연결
		case "전도금처리상태":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"데이타변경");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"미처리");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			//원가집계
			//원가집계
		case "계정구분50":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"원가");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"손익");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
			
			//인쇄복사
			//인쇄복사발주검토
		case "발주구분70":
			//인쇄,복사
			$query_data = array();
			$item=array("CODE"=>"*","NAME"=>"전체");
			array_push($query_data,$item);
			$item=array("CODE"=>"1","NAME"=>"인쇄");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"복사");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "진행상태70":
			//의뢰,발주,수령,정산
			$query_data = array();
			$item=array("CODE"=>"*","NAME"=>"전체");
			array_push($query_data,$item);
			$item=array("CODE"=>"01","NAME"=>"의뢰");
			array_push($query_data,$item);
			$item=array("CODE"=>"02","NAME"=>"발주");
			array_push($query_data,$item);
			$item=array("CODE"=>"03","NAME"=>"수령");
			array_push($query_data,$item);
			$item=array("CODE"=>"04","NAME"=>"정산");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
		case "진행70":
			//의뢰,발주,수령,정산
			$query_data = array();
			$item=array("CODE"=>"00","NAME"=>"작성");
			array_push($query_data,$item);
			$item=array("CODE"=>"01","NAME"=>"의뢰");
			array_push($query_data,$item);
			$item=array("CODE"=>"02","NAME"=>"발주");
			array_push($query_data,$item);
			$item=array("CODE"=>"99","NAME"=>"취소");
			array_push($query_data,$item);
			$item=array("CODE"=>"03","NAME"=>"수령");
			array_push($query_data,$item);
			$item=array("CODE"=>"04","NAME"=>"정산");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "발주70":
			//인쇄,복사
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"인쇄");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"복사");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
			//지출결의서작성
		case "진행상태71":
			//수령,정산
			$query_data = array();
			$item=array("CODE"=>"*","NAME"=>"전체");
			array_push($query_data,$item);
			$item=array("CODE"=>"03","NAME"=>"수령");
			array_push($query_data,$item);
			$item=array("CODE"=>"04","NAME"=>"정산");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			//성과품수령 및 지불의뢰
			
		case "기간구분72":
			//발주기간,수령기간
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"발주기간");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"수령기간");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
		case "진행상태72":
			//발주,결재중,수령
			$query_data = array();
			/*
			 $item=array("CODE"=>"*","NAME"=>"전체");
			 array_push($query_data,$item);
			 */
			$item=array("CODE"=>"02","NAME"=>"발주");
			array_push($query_data,$item);
			$item=array("CODE"=>"10","NAME"=>"결재중");
			array_push($query_data,$item);
			$item=array("CODE"=>"03","NAME"=>"수령");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			//협력업체
			//협력업체 관리등록(현업조회)
		case "전문공정80":
			//건축분야~과업일반(공동)
			$azsql ="SELECT ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE='11' AND SYS_ID='PM' AND ETC_DIV='03' AND HIDE_YN='N' AND USE_YN='Y' order by CODE";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "등록여부80":
			//등록,가등록,취소
			$query_data = array();
			$item=array("CODE"=>"Y%","NAME"=>"등록");
			array_push($query_data,$item);
			$item=array("CODE"=>"N%","NAME"=>"가등록");
			array_push($query_data,$item);
			$item=array("CODE"=>"D%","NAME"=>"취소");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "거래처구분81":
			//사업자번호,주민등록번호,부서코드..
			$azsql ="SELECT valid_value_code as code, valid_value_name as name FROM AM_CODE_VALIDATION WHERE VALIDATION_CODE = '10' AND USE_YN = 'Y' order by 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "진행상태22":
			//진행,중지,유보,해제,해지
			$azsql ="  SELECT class_code as code, class_name as name FROM VW_CS_CODE_CLASS_COMPLETION WHERE COMPANY_CODE = '11' AND CLASS_CODE <> 'E' ORDER BY SORT_ORDER";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//기준정보
			//사전사업등록
		case "사업부서90":
			//기획실~플렌트부
			$azsql ="SELECT DEPT_CODE as code, DEPT_NAME as name FROM SM_CODE_DEPT WHERE USE_YN = 'Y' AND F_CS_DEPT_TAG(DEPT_CODE)  IN ( '1','2','3') AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "진행상태90":
			//진행,중지,준공,유보,해제,해지
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name FROM VW_CS_CODE_CLASS_COMPLETION WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "해외사업90":
			//Y,N
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"Y");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"N");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "사업구분90":
			//하관,구조,지반,설비,환평,도발
			$azsql ="select null as code, null as name from dual union all select  distinct map_dcode as code ,map_dname as name from cs_cont_map_std WHERE map_end_div = 'Y' and map_mcode in ('HD', 'HS','HE')";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "결재라인90":
			//하관,구조,지반,설비,환평,도발
			$azsql ="select null as code, null as name from dual union all select etc_code as code, etc_name as name from rb_comm_code where sys_id = 'PM' and etc_div = '01' and use_yn = 'Y'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//협력업체 평가코드 등록
		case "구분91":
			//업무수행 평가,등록신청 평가
			$query_data = array();
			$item=array("CODE"=>"A","NAME"=>"업무수행 평가");
			array_push($query_data,$item);
			$item=array("CODE"=>"B","NAME"=>"등록신청 평가");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			//표준인건비 등록
		case "국내외구분92":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"국내");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"해외(동남아)");
			array_push($query_data,$item);
			$item=array("CODE"=>"5","NAME"=>"해외(동남아외)");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			//국내,해외(동남아),해외(동남아제외)
			break;
			
			//개인별 M/H작업공종 등록
		case "작업구분93":
			//직접 가동작업,간접 가동작업
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"직접가동작업");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"간접가동작업");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			//작업일보공종등록
		case "작업구분94":
			//직접 가동작업,간접 가동작업,비가동작업
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"직접가동작업");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"간접가동작업");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"비가동작업");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "사용여부94":
			//사용,미사용
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"사용");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"미사용");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			//목표관리
			//월별감리대기자원가투입부서등록
		case "인건비투입부서101":
			//건설사업본부~공통
			$azsql ="SELECT dept_code as code,dept_name as name FROM sm_code_dept where dept_div = 'S' and use_yn  = 'Y' order by code";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "인건비투입부서102":
			//건설사업본부~공통
			$azsql ="SELECT dept_code as code,'['||dept_code||'] '||dept_name as name  FROM sm_code_dept where dept_div = 'S' and use_yn  = 'Y' order by code";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//감리사업비상주율등록
		case "준공여부103":
			//진행,중지,준공,유보,해제
			break;
		case "전문공종":
			//강교감리,강재설비,건축기계설비...
			$azsql ="SELECT  ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM' AND ETC_DIV = '03' AND HIDE_YN = 'N' AND USE_YN = 'Y' ORDER BY ORDERS ";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//인쇄복사 발주의뢰
		case "진행상태91":
			$query_data = array();
			$item=array("CODE"=>"00","NAME"=>"작성중");
			array_push($query_data,$item);
			$item=array("CODE"=>"01","NAME"=>"의뢰");
			array_push($query_data,$item);
			$item=array("CODE"=>"02","NAME"=>"발주");
			array_push($query_data,$item);
			$item=array("CODE"=>"03","NAME"=>"수령");
			array_push($query_data,$item);
			$item=array("CODE"=>"04","NAME"=>"정산");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
			//M/H>작업일보등록현황>출력대상
		case "출력대상":
			$query_data = array();
			$item=array("CODE"=>"dwList","NAME"=>"상위");
			array_push($query_data,$item);
			$item=array("CODE"=>"dwMain","NAME"=>"하위");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "인원현황 구분":
			//행정/서무, 고문/비상근, 수주/영업임원, 가족사, 기타
			$azsql ="SELECT  ETC_CODE as code, ETC_NAME as name FROM RB_COMM_CODE WHERE COMPANY_CODE = '11' AND SYS_ID = 'PM' AND ETC_DIV = '50' ORDER BY ORDERS ";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
	}
}

//===========================================================
// 발주처 코드 상세조회 Level 2
//===========================================================
function QueryOrderOfficeTypeLevel2($type1)
{
	$azsql ="select area_class_code as code, area_class_name as name from cs_code_area_class where company_code = 11 and foregin_tag like '$type1%' order by	area_class_code";
	$this->oracle->AjaxLoadData($azsql);
}
//===========================================================
// 발주처 코드 상세조회 Level 3
//===========================================================
function QueryOrderOfficeTypeLevel3($type2)
{
	$azsql ="select area_code as code, area_name as name from cs_code_area where company_code = 11 and area_class_code like '$type2%' order by	area_code";
	$this->oracle->AjaxLoadData($azsql);
}


//===========================================================
// 수주영업(업무)관련 공통 코드
//===========================================================
function SaleQueryCode($mode,$value_name,$output_type, $auth_exception="")
{
	switch($mode){
		
		//신규추가-------------------------------------------------------------------------
		//입찰금액구분
		case "입찰금액구분":
			//직접 가동작업,간접 가동작업,비가동작업
			$query_data = array();
			$item=array("CODE"=>"21","NAME"=>"2억이상");
			array_push($query_data,$item);
			$item=array("CODE"=>"22","NAME"=>"2억미만");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
		case "출력구분":
			$query_data = array();
			$item=array('CODE'=>'1','NAME'=>"비고");
			array_push($query_data,$item);
			$item=array('CODE'=>'2','NAME'=>"추진현황");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
		case "추진등급":
			$query_data = array();
			$item=array('CODE'=>'A','NAME'=>"A");
			array_push($query_data,$item);
			$item=array('CODE'=>'B','NAME'=>"B");
			array_push($query_data,$item);
			$item=array('CODE'=>'C','NAME'=>"C");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
		case "일자구분":
			$query_data = array();
			$item=array('CODE'=>'1','NAME'=>"발행일자");
			array_push($query_data,$item);
			$item=array('CODE'=>'2','NAME'=>"작성일자");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
			
			
		case "증빙종류2":
			if($auth_exception == 'Sale'){
				$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code = '12' and valid_value_level = '1' ORDER BY valid_value_code ASC";
			}elseif($_SESSION['satis_user_auth_div'] == '40'){
				$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code = '12' and valid_value_level = '1' ORDER BY valid_value_code ASC";
			}elseif($_SESSION['satis_user_auth_div'] == '30'){
				$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code = '12' and valid_value_level = '1' ORDER BY valid_value_code ASC";
			}elseif($_SESSION['satis_user_auth_div'] == '20'){
				$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code = '12' and valid_value_level = '1' ORDER BY valid_value_code ASC";
			}else{
				$azsql ="SELECT valid_value_code as code,valid_value_name as name FROM am_code_validation WHERE validation_code = '12' and valid_value_level = '1' ORDER BY valid_value_code ASC";
			}
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "변경차수":
			$azsql ="SELECT CLASS_CODE as code,CLASS_NAME as name FROM VW_CS_CODE_CLASS_CHG_DEGREE WHERE COMPANY_CODE ='11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			
		case "변경구분2":
			$azsql ="SELECT CLASS_CODE as code,CLASS_NAME as name FROM VW_CS_CODE_CLASS_CHG_TAG WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "변경종류":
			$azsql ="SELECT CLASS_CODE  as code,CLASS_NAME as name FROM VW_CS_CODE_CLASS_CHG_KIND WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
			//신규추가-------------------------------------------------------------------------
			
			
		case "사업계획작성상태":
			$azsql ="select decode(nvl(count(*),0),0,'3', '1') as code, decode(nvl(count(*),0),0,'검토확정', '작성중') as name
                         from cs_plan_ordsm where company_code  = '11' and plan_year  = to_char(sysdate,'yyyy') and plan_seq  = 1 and nvl(pmdept_yn,'N')  = 'N'";
			$this->oracle->LoadData(trim(ICONV("UTF-8","EUC-KR",$azsql)),$value_name,$output_type);
			break;
		case "PQ발주":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name FROM CS_CODE_CLASS WHERE ( COMPANY_CODE = '11' ) AND CLASS_TAG like 'PQ'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "PQ발주2":
			$azsql ="SELECT '' as code, '' as name FROM DUAL UNION ALL SELECT CLASS_CODE as code, CLASS_NAME as name FROM CS_CODE_CLASS WHERE ( COMPANY_CODE = '11' ) AND CLASS_TAG like 'PQ'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "PQ발주3":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name FROM CS_CODE_CLASS WHERE ( COMPANY_CODE = '11' ) AND CLASS_TAG like 'PQ'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "지역명":
			$azsql ="SELECT AREA_CLASS_CODE as code, AREA_CLASS_NAME as name FROM CS_CODE_AREA_CLASS WHERE ( COMPANY_CODE = '11' ) AND FOREGIN_TAG like '%'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "발주처분류대":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_ORDER_CLASS WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "발주처분류중":
			$azsql ="SELECT AREA_CLASS_CODE as code, AREA_CLASS_NAME as name FROM CS_CODE_AREA_CLASS WHERE ( COMPANY_CODE = '11' ) AND FOREGIN_TAG like '$output_type' order by 1 ";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "발주처분류소":
			$azsql ="SELECT AREA_CODE as code, AREA_NAME as name, AREA_CLASS_CODE FROM CS_CODE_AREA WHERE ( COMPANY_CODE = '11' ) AND AREA_CLASS_CODE LIKE '$output_type' order by 2";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "진행상태2":
			$azsql ="SELECT CLASS_CODE, CLASS_NAME, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_PROCESS_TAG WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "심사기준":
			$azsql ="SELECT A.JUDGE_BASE, A.JUDGE_SEQ, B.CLASS_NAME, A.JUDGE_DESCRIPT, B.CLASS_NAME || ' ' || A.JUDGE_DESCRIPT AS NAME, A.JUDGE_BASE || A.JUDGE_SEQ AS CODE FROM CS_BIDS_EXPR A, VW_CS_CODE_CLASS_JUDGE_BASE B   WHERE A.COMPANY_CODE = B.COMPANY_CODE AND A.JUDGE_BASE = B.CLASS_CODE AND A.COMPANY_CODE = '11' order by CODE asc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "심사기준2":
			$azsql ="SELECT A.JUDGE_BASE, A.JUDGE_SEQ, B.CLASS_NAME, A.JUDGE_DESCRIPT, B.CLASS_NAME || ' ' || A.JUDGE_DESCRIPT AS NAME, A.JUDGE_BASE || A.JUDGE_SEQ AS CODE FROM CS_BIDS_EXPR A, VW_CS_CODE_CLASS_JUDGE_BASE B   WHERE A.COMPANY_CODE = B.COMPANY_CODE AND A.JUDGE_BASE = B.CLASS_CODE AND A.COMPANY_CODE = '11' AND USE_YN = 'Y' order by CODE asc";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "예가범위":
			$azsql ="SELECT LIMIT_SEQ as CODE, LIMIT_NAME as NAME, PLAN_RATE_LOW, PLAN_RATE_HIGH FROM CS_BIDS_PLANAMT_LMT WHERE COMPANY_CODE = '11' ORDER BY LIMIT_SEQ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "예가폭":
			$query_data = array();
			$item=array("CODE"=>"0.1","NAME"=>"0.1");
			array_push($query_data,$item);
			$item=array("CODE"=>"0.2","NAME"=>"0.2");
			array_push($query_data,$item);
			$item=array("CODE"=>"0.3","NAME"=>"0.3");
			array_push($query_data,$item);
			$item=array("CODE"=>"0.4","NAME"=>"0.4");
			array_push($query_data,$item);
			$item=array("CODE"=>"0.5","NAME"=>"0.5");
			array_push($query_data,$item);
			$item=array("CODE"=>"0.6","NAME"=>"0.6");
			array_push($query_data,$item);
			$item=array("CODE"=>"0.7","NAME"=>"0.7");
			array_push($query_data,$item);
			$item=array("CODE"=>"0.8","NAME"=>"0.8");
			array_push($query_data,$item);
			$item=array("CODE"=>"0.9","NAME"=>"0.9");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "예가구분": //171017
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"15/4");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"10/3");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"4/4");
			array_push($query_data,$item);
			$item=array("CODE"=>"4","NAME"=>"3/3");
			array_push($query_data,$item);
			$item=array("CODE"=>"5","NAME"=>"1/1");
			array_push($query_data,$item);
			$item=array("CODE"=>"6","NAME"=>"2/5");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "사업부서2": case "부서명3":
			
			if($auth_exception == 'Sale'){
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			}elseif($_SESSION['satis_user_auth_div'] == '40'){
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			}elseif($_SESSION['satis_user_auth_div'] == '30'){
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			}elseif($_SESSION['satis_user_auth_div'] == '20'){
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			}else{
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			}
			
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "사업부서3":
			
			if($auth_exception == 'RT'){ //도시계획부,환경평가부
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' AND DEPT_CODE IN('R0200','T0100') ORDER BY DEPT_CODE";
			}elseif($auth_exception == 'LL'){ //상하수도1부,상하수도2부,환경평가부
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' AND DEPT_CODE IN('L0301','L0303','L0500') ORDER BY DEPT_CODE";
			}elseif($auth_exception == 'ALL'){ //서길동,한정규
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			}elseif($_SESSION['satis_user_auth_div'] == '40'){
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			}elseif($_SESSION['satis_user_auth_div'] == '30'){
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			}elseif($_SESSION['satis_user_auth_div'] == '20'){
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' AND DEPT_CODE='".$_SESSION['satis_user_deptcode']."'";
			}else{
				$azsql ="SELECT DEPT_CODE as CODE, DEPT_NAME as NAME FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' AND DEPT_CODE='".$_SESSION['satis_user_deptcode']."'";
			}
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "본부":
			$azsql ="SELECT DISTINCT(B.HEADQUATER_CODE) as CODE, B.HEADQUATER_NAME as NAME FROM SM_CODE_DEPT A, SM_CODE_HEADQUATER B WHERE A.COMPANY_CODE = B.COMPANY_CODE AND A.HEADQUATER_CODE = B.HEADQUATER_CODE AND F_CS_DEPT_TAG(A.DEPT_CODE) IN ( '1','2','3') AND A.DEPT_DIV = 'S' AND A.USE_YN ='Y' AND B.USE_YN= 'Y' AND A.HEADQUATER_CODE <> '00000' AND A.COMPANY_CODE = '1' ORDER BY 1";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "PQ참여여부":
			$azsql ="SELECT CLASS_CODE AS CODE, CLASS_NAME AS NAME, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_PARTICIPATE WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "PQ참여여부2":
			$azsql ="SELECT CLASS_CODE AS CODE, CLASS_NAME AS NAME, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_PARTICIPATE WHERE COMPANY_CODE = '11' UNION ALL SELECT '','',0,''FROM DUAL ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "변경차수":  //170904
			$azsql ="SELECT CLASS_CODE as code,CLASS_NAME as name,SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_CHG_DEGREE WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "변경차수2":  //170905
			$azsql =" SELECT 'BA0' as code,
								 ''	   as name,
								 1	   as sort_order,
								 ''	   as remark
							FROM DUAL
						 UNION ALL
						  SELECT CLASS_CODE,
								 CLASS_NAME,
								 SORT_ORDER,
								 REMARK
							FROM VW_CS_CODE_CLASS_CHG_DEGREE
						   WHERE COMPANY_CODE = '11'
						ORDER BY 3 ASC ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "사원명":  //170905
			$azsql ="SELECT USER_ID as code, USER_NAME as name FROM SM_AUTH_USER WHERE COMPANY_CODE = '11' AND USE_YN = 'Y'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "변경종류":  //170904
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK  FROM VW_CS_CODE_CLASS_CHG_KIND WHERE COMPANY_CODE = '11'  ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "변경구분":  //170904
			$azsql ="SELECT CLASS_CODE as code,  CLASS_NAME as name, SORT_ORDER,  REMARK    FROM VW_CS_CODE_CLASS_CHG_TAG  WHERE COMPANY_CODE = '11'ORDER BY SORT_ORDER ASC  ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "보증구분":  //170905
			$azsql =" SELECT CLASS_CODE as code,CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_GUARANTEE_TAG WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "보증기관":  //170905
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK  FROM VW_CS_CODE_CLASS_GUARANTEE_COM WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "이행방식":  //170905
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK  FROM VW_CS_CODE_CLASS_JV_TAG WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "주관사":  //170905
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_JV_METHOD WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "사업부서": case "부서명": case "부서2":
			$azsql ="SELECT DEPT_CODE as code, DEPT_NAME as name FROM SM_CODE_DEPT WHERE USE_YN = 'Y' AND F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "사업구분": case "부서구분": case "구분2":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_PROJ_TAG WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;

			//221129 FAD-공동도급하도급 > 업무분담
		case "발주방법": case "발주": case "입찰구분":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_ORDER_METHOD WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "입찰구분5":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name,SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_BID_TAG WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "발주처분류소all":
			$azsql ="SELECT AREA_CODE as code, AREA_NAME as name, AREA_CLASS_CODE FROM CS_CODE_AREA WHERE ( COMPANY_CODE = '11' ) AND AREA_CLASS_CODE LIKE '$output_type%' order by AREA_CODE";
			$this->oracle->LoadData($azsql,$value_name,"ajax");
			break;
		case "기성구분": case "청구구분":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_EXTABLISHED WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "사업계획진행상태":
			$azsql ="select class_code as code, class_name as name from cs_code_class where class_tag = 'VB' order by sort_order ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "경쟁업체수주현황기준월":
			$azsql ="select  distinct base_date as code,  base_date as name from CS_COMPETITION_CONT1 order by base_Date desc ";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "진행상태": case "진행구분":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_COMPLETION WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "공종구분":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_PART_TAG WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "입찰방식":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_CBID_TYPE WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "입찰방식2":
			$azsql =" SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER,REMARK FROM VW_CS_CODE_CLASS_BID_TYPE WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "전문분야":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_BID_LIMIT WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "대가산출방식":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_OUTPUT_METHOD WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "문서구분": //170905
			$azsql ="  SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK  FROM CS_CODE_CLASS WHERE COMPANY_CODE = '11' AND CLASS_TAG = 'UA'ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "업무범위": //170905
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER FROM VW_CS_CODE_CLASS_BID_LIMIT WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "외화구분": //170905
			//$azsql ="SELECT CLASS_CODE as code,CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_CURRENCY_TAG WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC ";
			$azsql ="select null as code, null as name, 0 as SORT_ORDER, null as REMARK from dual union all SELECT CLASS_CODE as code,CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_CURRENCY_TAG WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "사업종류": //170905
			$azsql ="select  distinct map_mcode as code,map_mname as name from cs_cont_map_std WHERE map_end_div = 'Y' and map_mcode in ('HD', 'HS')";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "사업부문": //170905
			$azsql ="select  distinct map_dcode as code,map_dname as name from cs_cont_map_std WHERE map_end_div = 'Y' and map_mcode in ('HD', 'HS')";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "지역":
			$azsql ="select area_key as code , area_name as name, level_code from cs_code_local where substr(level_code,1,1) = 'A' union all select 'NULL00' as area_key, '".$this->HangleEncodeUTF8_EUCKR('미입력')."' as area_name, 'ZZZ' as level_code from dual order by level_code";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "부서":
			$azsql ="SELECT DEPT_CODE as code, DEPT_NAME as name FROM SM_CODE_DEPT WHERE F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' and use_yn = 'Y' and dept_code > 'E0100' ORDER BY DEPT_CODE";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "부서명2":
			$azsql ="SELECT DEPT_CODE as code, DEPT_NAME as name FROM SM_CODE_DEPT WHERE USE_YN = 'Y' AND F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2') AND COMPANY_CODE = '11' ORDER BY DEPT_CODE";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "사업장":
			$azsql ="select company_CODE as code, company_name as name from sm_code_company where use_yn = 'Y' and company_code = '11'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "계정과목":
			$azsql ="select acnt_CODE as code, acnt_name as name from am_code_acnt_mast where acnt_code like '101111%'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "정리구분":
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_OFF_CODE WHERE COMPANY_CODE = '11' ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "공채구분": //170905
			$azsql =" SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_SECURITS_TAG WHERE COMPANY_CODE = '11'ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
			
		case "공채구분2":
			$azsql =" SELECT CLASS_CODE as code, CLASS_NAME as name, SORT_ORDER, REMARK FROM VW_CS_CODE_CLASS_SECURITS_TAG WHERE COMPANY_CODE = '11' UNION ALL SELECT '','',0,''FROM DUAL ORDER BY SORT_ORDER ASC";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "발주국가": //170913
			$azsql ="SELECT CLASS_CODE as code, CLASS_NAME as name,  SORT_ORDER, REMARK   FROM CS_CODE_CLASS WHERE class_tag = 'AC' order by CLASS_NAME";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "도급분야":
			$azsql ="select class_code as code, class_name as name from cs_code_class where class_tag= 'OB'";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
		case "금액단위": case "단위":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"원");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"십원");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"백원");
			array_push($query_data,$item);
			$item=array("CODE"=>"4","NAME"=>"천원");
			array_push($query_data,$item);
			$item=array("CODE"=>"5","NAME"=>"만원");
			array_push($query_data,$item);
			$item=array("CODE"=>"6","NAME"=>"십만원");
			array_push($query_data,$item);
			$item=array("CODE"=>"7","NAME"=>"백만원");
			array_push($query_data,$item);
			$item=array("CODE"=>"8","NAME"=>"천만원");
			array_push($query_data,$item);
			$item=array("CODE"=>"9","NAME"=>"억원");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "금액단위2": case "단위2":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"원");
			array_push($query_data,$item);
			$item=array("CODE"=>"10","NAME"=>"십원");
			array_push($query_data,$item);
			$item=array("CODE"=>"100","NAME"=>"백원");
			array_push($query_data,$item);
			$item=array("CODE"=>"1000","NAME"=>"천원");
			array_push($query_data,$item);
			$item=array("CODE"=>"10000","NAME"=>"만원");
			array_push($query_data,$item);
			$item=array("CODE"=>"100000","NAME"=>"십만원");
			array_push($query_data,$item);
			$item=array("CODE"=>"1000000","NAME"=>"백만원");
			array_push($query_data,$item);
			$item=array("CODE"=>"10000000","NAME"=>"천만원");
			array_push($query_data,$item);
			$item=array("CODE"=>"100000000","NAME"=>"억원");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "계획차수":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"연간계획");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"하반기계획");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "출력물":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"낙찰결과보고서");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"입찰진행현황");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "신규배서구분":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"신규");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"배서");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "보고서양식":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"부서별정렬");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"금액별정렬");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "수금구분":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"기계약사업");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"신규사업");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "해외사업":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"해외사업");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"국내사업");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "해외구분":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"해외");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"국내");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "연차사업": //170905
			$query_data = array();
			$item=array("CODE"=>"","NAME"=>"");
			array_push($query_data,$item);
			$item=array("CODE"=>"Y","NAME"=>"예");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"아니오");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "출력2":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"집계표");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"명세서");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "부가가치세":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"포함");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"제외");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "이월구분":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"이월사업");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"종료사업");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "이월사업":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"이월사업");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"이월종료");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "출력":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"총괄");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"중지사유");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "수금잔액":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"있음");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"없음");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "현금구분":
			$query_data = array();
			$item=array("CODE"=>"C","NAME"=>"현금");
			array_push($query_data,$item);
			$item=array("CODE"=>"B","NAME"=>"어음");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "진행상태3":
			$query_data = array();
			$item=array("CODE"=>"N","NAME"=>"중지");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "구분":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"당사");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"타사");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "사업구분2":
			$query_data = array();
			$item=array("CODE"=>"CAA","NAME"=>"설계");
			array_push($query_data,$item);
			$item=array("CODE"=>"CAB","NAME"=>"감리");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "합사구분2":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"합사운영 외용역");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"합사운영 용역");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "입찰결과":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"당사");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"타사");
			array_push($query_data,$item);
			$item=array("CODE"=>"A","NAME"=>"진행");
			array_push($query_data,$item);
			$item=array("CODE"=>"I","NAME"=>"유찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"","NAME"=>"");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "입찰결과2":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"당사낙찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"타사낙찰");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "업무구분":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"PQ정보");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"입찰정보");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "공고구분": //170913
			$query_data = array();
			$item=array("CODE"=>"LOI","NAME"=>"LOI공고");
			array_push($query_data,$item);
			$item=array("CODE"=>"EOI","NAME"=>"EOI공고");
			array_push($query_data,$item);
			$item=array("CODE"=>"PQ","NAME"=>"PQ공고");
			array_push($query_data,$item);
			$item=array("CODE"=>"BID","NAME"=>"입찰공고");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "해외입찰결과": //170913
			$query_data = array();
			$item=array("CODE"=>"A","NAME"=>"EOI통과");
			array_push($query_data,$item);
			$item=array("CODE"=>"E","NAME"=>"EOI탈락");
			array_push($query_data,$item);
			$item=array("CODE"=>"B","NAME"=>"PQ통과");
			array_push($query_data,$item);
			$item=array("CODE"=>"P","NAME"=>"PQ탈락");
			array_push($query_data,$item);
			$item=array("CODE"=>"Y","NAME"=>"당사낙찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"타사낙찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"D","NAME"=>"유보");
			array_push($query_data,$item);
			$item=array("CODE"=>"I","NAME"=>"재입찰");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "해외사업진행상태": //170913
			$query_data = array();
			$item=array("CODE"=>"A","NAME"=>"기술부서통보");
			array_push($query_data,$item);
			$item=array("CODE"=>"C","NAME"=>"LOI작성중");
			array_push($query_data,$item);
			$item=array("CODE"=>"E","NAME"=>"LOI제출및대기");
			array_push($query_data,$item);
			$item=array("CODE"=>"G","NAME"=>"EOI작성중");
			array_push($query_data,$item);
			$item=array("CODE"=>"J","NAME"=>"EOI제출및대기");
			array_push($query_data,$item);
			$item=array("CODE"=>"P","NAME"=>"PQ작성중");
			array_push($query_data,$item);
			$item=array("CODE"=>"Q","NAME"=>"PQ제출및대기");
			array_push($query_data,$item);
			$item=array("CODE"=>"R","NAME"=>"입찰참여대기");
			array_push($query_data,$item);
			$item=array("CODE"=>"S","NAME"=>"입찰서작성");
			array_push($query_data,$item);
			$item=array("CODE"=>"T","NAME"=>"입찰서제출및대기");
			array_push($query_data,$item);
			$item=array("CODE"=>"U","NAME"=>"개찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"V","NAME"=>"우선협상대상자선정");
			array_push($query_data,$item);
			$item=array("CODE"=>"W","NAME"=>"계약NEGO");
			array_push($query_data,$item);
			$item=array("CODE"=>"Y","NAME"=>"계약");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "PQ결과":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"합격");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"불합격");
			array_push($query_data,$item);
			$item=array("CODE"=>"A","NAME"=>"진행");
			array_push($query_data,$item);
			$item=array("CODE"=>"I","NAME"=>"유찰");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "PQ결과2":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"합격");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"불합격");
			array_push($query_data,$item);
			$item=array("CODE"=>"A","NAME"=>"진행");
			array_push($query_data,$item);
			$item=array("CODE"=>"I","NAME"=>"일반경쟁");
			array_push($query_data,$item);
			$item=array("CODE"=>"","NAME"=>"");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "낙찰결과":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"당사낙찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"타사낙찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"A","NAME"=>"진행중");
			array_push($query_data,$item);
			$item=array("CODE"=>"I","NAME"=>"유찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"","NAME"=>"");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "용역규모":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"5억미만");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"5억이상 10억미만");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"10억이상");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "참여여부":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"참여");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"불참");
			array_push($query_data,$item);
			$item=array("CODE"=>"A","NAME"=>"미결정");
			array_push($query_data,$item);
			$item=array("CODE"=>"I","NAME"=>"유찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"M","NAME"=>"자격무");
			array_push($query_data,$item);
			$item=array("CODE"=>"X","NAME"=>"취소");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "참여여부2":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"참여");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"불참");
			array_push($query_data,$item);
			$item=array("CODE"=>"A","NAME"=>"미결정");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "입찰참여":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"참여");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"불참");
			array_push($query_data,$item);
			$item=array("CODE"=>"A","NAME"=>"진행");
			array_push($query_data,$item);
			$item=array("CODE"=>"I","NAME"=>"일반경쟁");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "순위개수":
			$query_data = array();
			$item=array("CODE"=>"2","NAME"=>"2개");
			array_push($query_data,$item);
			$item=array("CODE"=>"5","NAME"=>"5개");
			array_push($query_data,$item);
			$item=array("CODE"=>"10","NAME"=>"10개");
			array_push($query_data,$item);
			$item=array("CODE"=>"20","NAME"=>"20개");
			array_push($query_data,$item);
			$item=array("CODE"=>"30","NAME"=>"30개");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "업무분담":
			$query_data = array();
			$item=array("CODE"=>"N","NAME"=>"미확정");
			array_push($query_data,$item);
			$item=array("CODE"=>"Y","NAME"=>"확정");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "업무분담2":
			$query_data = array();
			$item=array("CODE"=>"%","NAME"=>"전체");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"미확정");
			array_push($query_data,$item);
			$item=array("CODE"=>"Y","NAME"=>"확정");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "2억이상":
			$query_data = array();
			$item=array("CODE"=>"21","NAME"=>"2억이상");
			array_push($query_data,$item);
			$item=array("CODE"=>"22","NAME"=>"2억미만");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "발행유무": //170905
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"발행");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"미발행");
			array_push($query_data,$item);
			$item=array("CODE"=>"C","NAME"=>"취소");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "발행일자": //170914
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"발행일자");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"작성일자");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "이체유무":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"이체");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"미이체");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "계약여부": case "계약구분":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"계약");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"미계약");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "계약상태": //171010
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"계약완료");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"미계약");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "입찰구분2":
			$query_data = array();
			$item=array("CODE"=>"11","NAME"=>"PQ입찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"21","NAME"=>"사후입찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"22","NAME"=>"일반입찰");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "입찰구분3":
			$query_data = array();
			$item=array("CODE"=>"21","NAME"=>"사후PQ");
			array_push($query_data,$item);
			$item=array("CODE"=>"22","NAME"=>"일반경쟁");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "입찰구분4":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"PQ");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"일반");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "입찰구분6":
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"지명");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"일반");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "입찰구분7":
			$query_data = array();
			$item=array("CODE"=>"FAA","NAME"=>"지명");
			array_push($query_data,$item);
			$item=array("CODE"=>"FAB","NAME"=>"일반");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "입찰구분8":
			$query_data = array();
			$item=array("CODE"=>"11","NAME"=>"PQ입찰");
			array_push($query_data,$item);
			$item=array("CODE"=>"21","NAME"=>"사후PQ");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "년월일": //170905
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"일");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"월");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"년");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "설계구분": //170905
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"기본설계");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"실시설계");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"기본/실시설계");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "사업진행구분": //170905
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"진행중");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"탈락");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"미계약");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"완료");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "성공유무": //170905
			$query_data = array();
			$item=array("CODE"=>"1","NAME"=>"성공");
			array_push($query_data,$item);
			$item=array("CODE"=>"2","NAME"=>"탈락");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"후속사업없음");
			array_push($query_data,$item);
			$item=array("CODE"=>"3","NAME"=>"진행중");
			array_push($query_data,$item);
			$item=array("CODE"=>"0","NAME"=>"");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "채권종류": //171102
			$query_data = array();
			$item=array("CODE"=>"","NAME"=>"");
			array_push($query_data,$item);
			$item=array("CODE"=>"10","NAME"=>"정상채권");
			array_push($query_data,$item);
			$item=array("CODE"=>"30","NAME"=>"부실채권");
			array_push($query_data,$item);
			$item=array("CODE"=>"50","NAME"=>"악성채권");
			array_push($query_data,$item);
			$item=array("CODE"=>"70","NAME"=>"회생채권");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "구분2":
			$query_data = array();
			$item=array("CODE"=>"A","NAME"=>"설계");
			array_push($query_data,$item);
			$item=array("CODE"=>"B","NAME"=>"감리");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "설계감리2":
			$query_data = array();
			$item=array("CODE"=>"A","NAME"=>"설계");
			array_push($query_data,$item);
			$item=array("CODE"=>"B","NAME"=>"감리");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "문서구분2":
			$query_data = array();
			$item=array("CODE"=>"%","NAME"=>"전체");
			array_push($query_data,$item);
			$item=array("CODE"=>"10","NAME"=>"총괄계약서");
			array_push($query_data,$item);
			$item=array("CODE"=>"20","NAME"=>"차수계약서");
			array_push($query_data,$item);
			$item=array("CODE"=>"30","NAME"=>"변경계약서");
			array_push($query_data,$item);
			$item=array("CODE"=>"40","NAME"=>"총괄계약서");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
		case "조회구분5":
			$query_data = array();
			$item=array("CODE"=>"Y","NAME"=>"확인");
			array_push($query_data,$item);
			$item=array("CODE"=>"N","NAME"=>"미확인");
			array_push($query_data,$item);
			$this->smarty->assign($value_name,$query_data);
			break;
	}
}


function ConfirmDeveloperYN($user_id="",$value_name="DeveloperYN", $output_type="")
{
	$DeveloperArray = array('203155','216173','209171');//,'216070','216011','209171','217081','217099'
	$DeveloperYN="N";
	if($user_id!=""){
		for($i=0;$i<count($DeveloperArray);$i++){
			if($DeveloperArray[$i]==$user_id){
				$DeveloperYN="Y";
				break;
			}
		}
	}
	if($output_type=="assign" || $output_type==""){
		$this->smarty->assign($value_name,$DeveloperYN);
		return $DeveloperYN;
	}else{
		return $DeveloperYN;
	}
}//GetDeveloperList


function HangleEncodeUTF8_EUCKR($item)
{
	$result=trim(ICONV("UTF-8","EUC-KR",$item));
	return $result;
}


function HangleEncode($item)
{
	$result=trim(ICONV("EUC-KR","UTF-8",$item));
	//		if(trim($result)=="") 	$result="&nbsp;";
	return $result;
}


//TPL select박스 구성에 필요한 옵션값 생성 : by Moon 20160103
function MakeOption($Case, $Title, $type, $temp="", $temp2=""){
	$ArrayData = "";
	switch($type){
		case "Dept": 	$ArrayData = $this->QueryDeptList($Case,$Title,"",$temp);	break;
		case "Query": 	$ArrayData = $this->QueryCodeList($Case,$Title, $temp);	break;
		case "Person": 	$ArrayData = $this->PersonQueryCode($Case,$Title,"", $temp);	break;
		case "Account": $ArrayData = $this->AccountQueryCode($Case,$Title, $temp, $temp2);	break;
		case "Purchase":$ArrayData = $this->PurchaseQueryCode($Case,$Title, $temp);	break;
		case "Book": 	$ArrayData = $this->BookQueryCode($Case,$Title, $temp);	break;
		case "Project": $ArrayData = $this->ProjectQueryCode($Case,$Title, $temp);	break;
		case "Sale": 	$ArrayData = $this->SaleQueryCode($Case,$Title, $temp);	break;
	}
	
}//MakeOption

function MakeOption2($Case, $Title, $type, $temp=""){
	$ArrayData = "";
	switch($type){
		case "Dept": 	$ArrayData = $this->QueryDeptList($Case,$Title,"array");	break;
		case "Query": 	$ArrayData = $this->QueryCodeList($Case,$Title,"array");	break;
		case "Person": 	$ArrayData = $this->PersonQueryCode($Case,$Title,"array");	break;
		case "Account": 	$ArrayData = $this->AccountQueryCode($Case,$Title,"array");	break;
		case "Purchase": 	$ArrayData = $this->PurchaseQueryCode($Case,$Title,"array");	break;
		case "Book": 	$ArrayData = $this->BookQueryCode($Case,$Title,"array");	break;
		case "Project": 	$ArrayData = $this->ProjectQueryCode($Case,$Title,"array");	break;
		case "Sale": 	$ArrayData = $this->SaleQueryCode($Case,$Title,"array");	break;
	}
	
	$str_join = "";
	for($i = 0 ; $i<count($ArrayData) ; $i++){
		$str_join .= $ArrayData[$i][CODE].":".$ArrayData[$i][NAME];
		if($i < count($ArrayData)-1){
			$str_join .= ";";
		}
	}
	echo $str_join;
	//$this->smarty->assign($Title,$str_join);
}//MakeOption2


//삭제예정코드 : 201704월까지 모니터링후 장애 미발생시
// 	function MakeOption3($Case, $Title, $type, $temp=""){
// 		$ArrayData = "";
// 		switch($type){
// 			case "Dept": 	$ArrayData = $this->QueryDeptList($Case,$Title,"");	break;
// 			case "Query": 	$ArrayData = $this->QueryCodeList($Case,$Title,"");	break;
// 			case "Person": 	$ArrayData = $this->PersonQueryCode($Case,$Title,"");	break;
// 			case "Account": 	$ArrayData = $this->AccountQueryCode($Case,$Title,"");	break;
// 			case "Purchase": 	$ArrayData = $this->PurchaseQueryCode($Case,$Title,"");	break;
// 			case "Book": 	$ArrayData = $this->BookQueryCode($Case,$Title,"");	break;
// 			case "Project": 	$ArrayData = $this->ProjectQueryCode($Case,$Title,"");	break;
// 			case "Sale": 	$ArrayData = $this->SaleQueryCode($Case,$Title,"");	break;
// 		}
// 		//$this->smarty->assign($Title,$str_join);
// 	}//MakeOption3



function Project_info_dgree($Code){
	$azsql ="BEGIN usp_Common_Project_info01(:entries,'$Code'); END;"; ///프로젝트 차수정보
	$this->oracle->LoadProcedureAjax($azsql);
}
function Project_info_state($Code, $dgree){
	$azsql ="BEGIN usp_Common_Project_info02(:entries,'$Code','$dgree'); END;"; ///프로젝트 진행상태
	$this->oracle->LoadProcedureAjax($azsql);
}
function Project_info_dept($Code, $dgree){
	$azsql ="BEGIN usp_Common_Project_info03(:entries,'$Code','$dgree'); END;"; ///프로젝트 부서정보
	$this->oracle->LoadProcedureAjax($azsql);
}
function Project_info_all($Code){
	$azsql ="BEGIN usp_Common_Project_info_all(:entries,'$Code'); END;"; //
	$this->oracle->LoadProcedureAjax($azsql);
}


function CUST_CODE_STATUS($Code){
	$azsql ="BEGIN Usp_Pm_Partner_05_status(:entries,'$Code'); END;"; //
	//echo $azsql;
	$this->oracle->LoadProcedureAjax($azsql);
}



function Project_info_get_auth($AS_PROJ_CODE, $AS_DEPT_CODE, $AS_EMPNO){
	$azsql ="BEGIN usp_Common_Project_GET_AUTH(:entries, '11', '$AS_PROJ_CODE', '$AS_DEPT_CODE', '$AS_EMPNO'); END;"; //
	$this->oracle->LoadProcedureAjax($azsql);
}


function EtcQueryCode($mode, $value_name, $output_type, $param1="", $param2="", $param3=""  )
{
	switch($mode)
	{
		
		case "알림정보제목":
			$azsql ="SELECT
				write_date as code , info_title as name
				FROM
				SM_INFO_DESC
				WHERE
				company_code = '11'
				AND SUBSTR(write_date,1,8) BETWEEN '$param1' AND '$param2'
				AND write_id = '$param3' ";
			
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			
			break;
	}
}

//=============================================================================================
// 쿼리결과
//=============================================================================================
function QueryResultCode($mode, $value_name, $output_type, $param1="")
{
	switch($mode)
	{
		case "F_HR_PAYX_END_YN":
			//$azsql ="select F_HR_PAYX_END_YN('11', '$param1', 'P')as code, F_HR_PAYX_END_YN('11', '$param1', 'P')as name from dual";
			$azsql ="";
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			break;
	}
}

//=============================================================================================
// 권한에따른부서표시
//=============================================================================================
function GroupCodeByAuth($mode, $value_name,$userdept,$userauth, $output_type="")
{
	switch($mode)
	{
		case "권한별부서":
			
			$allmode=$this->HangleEncodeUTF8_EUCKR("전체");
			
			/*
			 $azsql ="SELECT DEPT_CODE AS CODE,DEPT_NAME AS NAME
			 FROM SM_CODE_DEPT
			 WHERE USE_YN = 'Y'
			 AND F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3')
			 AND DEPT_CODE = '$userdept'
			 AND 20 >= '$userauth'
			 UNION ALL
			 SELECT DEPT_CODE AS CODE,
			 DEPT_NAME AS NAME
			 FROM SM_CODE_DEPT
			 WHERE USE_YN = 'Y'
			 AND F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3')
			 AND COMPANY_CODE = '11'
			 AND HEADQUATER_CODE IN (SELECT A.HEADQUATER_CODE
			 FROM SM_CODE_DEPT A
			 WHERE A.DEPT_CODE = '$userdept')
			 AND 30 = '$userauth'
			 UNION ALL
			 SELECT DEPT_CODE AS CODE,
			 DEPT_NAME AS NAME
			 FROM SM_CODE_DEPT
			 WHERE USE_YN = 'Y'
			 AND F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3')
			 AND '40' = '$userauth'
			 UNION ALL
			 SELECT '%',
			 '$allmode'
			 FROM DUAL
			 WHERE '40' = '$userauth'
			 ORDER BY CODE";
			 $this->oracle->LoadData($azsql,$value_name,$output_type);
			 */
			
			
			$azsql ="SELECT DEPT_CODE as code, DEPT_NAME as name FROM SM_CODE_DEPT WHERE USE_YN = 'Y' AND F_CS_DEPT_TAG(DEPT_CODE) IN ( '1','2','3') AND COMPANY_CODE = '11' UNION ALL
						SELECT '%',
							   '$allmode'
						FROM DUAL
						ORDER BY CODE";
			
			
			$this->oracle->LoadData($azsql,$value_name,$output_type);
			
			break;
	}
}

}// end
