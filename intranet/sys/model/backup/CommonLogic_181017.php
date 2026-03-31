<?php
/***************************************
* common 기능
* ------------------------------------
****************************************/
include "../inc/dbcon.inc";
include "../../../SmartyConfig.php";
//include "excel/excel_reader2.php";
//---------------------------------------------------
$this_year=date("Y");
$this_month=date("m");
//---------------------------------------------------
//Global 변수 지정 Start -------------------
$GB_user_id 	= $_SESSION['SS_memberID'];		//사원번호
$GB_user_korName 	= $_SESSION['SS_korName'];			//한글이름
$GB_user_RankCode 	= $_SESSION['SS_RankCode'];		//직급코드
$GB_user_position 		= $_SESSION['SS_position'];			//직위명

$GB_user_GroupCode 	= $_SESSION['SS_GroupCode'];		//부서코드
$GB_user_GroupName	= $_SESSION['SS_GroupName'];		//부서명

$GB_user_SortKey 		= $_SESSION['SS_SortKey'];			//직급+부서코드

$GB_user_EntryDate 	= $_SESSION['SS_EntryDate'];		//입사일자

$GB_date_today		= date("Y-m-d");	// 오늘날짜 년월일 : yyyy-mm-dd
$GB_date_today2		= $GB_date_today." 00:00:00";
$GB_date_today_yyyy	= date("Y");	// 오늘날짜 년 : yyyy
$GB_date_today_mm		= date("m");	// 오늘날짜 월 : mm
$GB_date_today_yyyymmdd = $GB_date_today_yyyy.$GB_date_today_mm.$GB_date_today_dd;// 오늘날짜 년월일 : yyyymmdd
$GB_date_today_FULL = date("Y-m-d H:i:s");
//Global 변수 지정 End -------------------
//---------------------------------------------------
extract($_REQUEST);
//---------------------------------------------------
class CommonLogic {
	//=============
	var $smarty;
	//=============
	////////////////////////////////////////////////////////////////
	function CommonLogic($smarty)
	{
		$this->smarty=$smarty;
	}//CommonLogic
	////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////
	//팝업 TPL분기
	function PageView()
	{
		extract($_REQUEST);
		global $GB_user_id, $GB_user_GroupCode, $GB_date_today, $GB_date_today2, $GB_date_today_yyyy, $GB_date_today_mm, $GB_date_today_yyyymmdd;
		global $itemkey1,$itemkey2 ,$itemkey3 ,$itemkey4 ,$itemkey5 ,$returnType ,$callback;
		//------------------------
		global $itemkey1;
		global $itemkey2;
		global $itemkey3;
		global $returnType;
		global $callback;

		$this->smarty->assign('itemkey1',$itemkey1);
		$this->smarty->assign('itemkey2',$itemkey2);
		$this->smarty->assign('itemkey3',$itemkey3);
		$this->smarty->assign('itemkey4',$itemkey4);
		$this->smarty->assign('itemkey5',$itemkey5);
		$this->smarty->assign('returnType',$returnType);
		$this->smarty->assign('callback',$callback);

		// 타입 선택
		switch($ActionMode)
		{
			//=========================================================================
			// 팝업
			//사용화면 :  : 프로젝트 코드 선택 팝업
			case "popup":
				//$itemkey1 : project
				//$itemkey2 : 사원번호
				//$itemkey3 : ''
				if($itemkey1=="project"){
// 					$array_getLastAddWork = getLastAddWork($GB_user_id, '');
// 					$this->smarty->assign('projectHistory',$array_getLastAddWork);

					$this->selectItem("project_code_product_tbl","","optionList_01"); //사업 종류[설계(D),감리(S),수주영업(P),제조(F),시공(C),연구개발(R),경영지원(M),기타사업(E),...]
					$this->selectItem("project_code_job_tbl","","optionList_02"); //사업부문 종류[하천,방재,수력,상수,...]

					$this->selectItem("year", "10", "optionList_year");
					$this->smarty->assign('selectedYear',$GB_date_today_yyyy);

					$this->smarty->assign('title',"프로젝트 코드 선택");
					$this->smarty->display("intranet/common/searchPop_projectCode.tpl");

				}else if($itemkey1=="test"){	 
					
					//$re_projectViewCode = projectToColumn('H05-IT-04','NewProjectCode');
					//echo '='.$re_projectViewCode.'=';
					
					$this->smarty->display("intranet/common/test.tpl"); 
				}else{

				}
				break;

			//=========================================================================
			//사용화면 :  좌측메뉴-팀관리-프로젝트 인력관리
			case "view":
				//$itemkey1 : project_person_manage
				//$itemkey2 : 사원번호
				//$itemkey3 : ''
				if($itemkey1=="project_person_manage"){
					$this->smarty->assign('title',"프로젝트 인력관리");
					$this->smarty->display("intranet/common/project_person_manage.tpl");
				}else if($itemkey1=="other9999"){

				}
				break;


					
			//=========================================================================
			case "AAAAA":
				$this->smarty->display("miso/Common/AAAAA.tpl");
				break;

			//=========================================================================
			default:
				break;
		}//switch
		//-----------------------------------------
	}//PageView




	////////////////////////////////////////////////////////////////
	function ResultProsesure()
	{
		extract($_REQUEST);
		global $GB_user_id, $GB_user_GroupCode, $GB_date_today, $GB_date_today2, $GB_date_today_yyyy, $GB_date_today_mm, $GB_date_today_yyyymmdd;
		global $itemkey1,$itemkey2 ,$itemkey3 ,$itemkey4 ,$itemkey5 ,$returnType ,$callback;
		//------------------------
		global $db; //DB conn
		global $ActionMode;
		global $ajaxJson;//POPUP창으로 결과값(JSON)리턴 여부 : Y
		global $requestDataYN;

		switch($ActionMode)
		{
			case "json":
				//$itemkey1 : project
				//$itemkey2 : 사원번호
				//$itemkey3 : projectCode/activityCode
				//$itemkey4 : 선택된프로젝트코드
				//$itemkey5 :
				if($itemkey1=="project" && $itemkey3=="projectCode"){
					$this->returnData_Json($itemkey3);

				}else if($itemkey1=="project" && $itemkey3=="activityCode"){
					$this->returnData_Json($itemkey3);

				}else if($itemkey1=="project" && $itemkey3=="currentWorkProject"){
					$this->returnData_Json($itemkey3);

				}else if($itemkey1=="changeOption"){
					$this->returnData_Json($itemkey2);

					//Activity코드 option 호출--------------
					//ActionMode : json
					//itemkey1=분기값1 : changeOption
					//itemkey2=분기값2 : projectCodeActivity
					//itemkey3=
					//itemkey4=리턴액션적용 ID : EntryJobCode
					//itemkey5=프로젝트코드
					//returnType
					//callback
					//-------------------------------------

				}else if($itemkey1=="getProjectInfoAll"){
					$this->returnData_Json($itemkey1);
					//Activity코드 option 호출--------------
					//ActionMode : json
					//itemkey1=분기값1 : projectInfo
					//itemkey2=입력된 프로젝트번호
					//itemkey3=
					//itemkey4=
					//itemkey5=
					//returnType
					//callback
					//-------------------------------------


				}else{

				}
				break;

			//=========================================================================
			case "PreCheck_typefrm11501_pop":
				break;
			//=========================================================================
			default:

				break;
		}//switch
		//-----------------------------------------------------------------------


	}//ResultProsesure

	//2018-09-04 정명준 프로젝트코드 사업종류 년도 통일(연구개발, 경영지원, 전사공통)으로 인한 수정. pjt_product_cd가 R,M,V일때 년도 0000으로 변환.
	function returnData_Json($Param_01)
	{
		extract($_REQUEST);
		global $GB_user_id, $GB_user_GroupCode, $GB_date_today, $GB_date_today2, $GB_date_today_yyyy, $GB_date_today_mm, $GB_date_today_yyyymmdd;

		global $db; //DB conn

		global $itemkey1,$itemkey2 ,$itemkey3 ,$itemkey4 ,$itemkey5 ,$returnType ,$callback;

		/* -----------------*/
		switch($Param_01)
		{
			case "projectCode":
				
				
				
				
				
				
				
// 							//프로젝트 코드검색 : 프로젝트 검색
// 							//--------------------------------------------------------------------------------------------------
// 							$pjt_product_cd      = $_REQUEST['pjt_product_cd']==""?"":$_REQUEST['pjt_product_cd']; 			//사업종류 코드
// 							$pjt_year               = $_REQUEST['pjt_year']==""?"":$_REQUEST['pjt_year']; 								//사업년도
// 							$pjt_job_cd            = $_REQUEST['pjt_job_cd']==""?"":$_REQUEST['pjt_job_cd']; 						//사업부문 코드
// 							$pjt_index            = $_REQUEST['pjt_index']==""?"":$_REQUEST['pjt_index']; 								//일련번호
// 							$pjt_codeAndName   = $_REQUEST['pjt_codeAndName']==""?"":$_REQUEST['pjt_codeAndName']; 	//프로젝트 코드/명
// 							//============================================================================
// 							if($pjt_product_cd == 'R' or $pjt_product_cd == 'M' or $pjt_product_cd == 'V'){
// 								$pjt_year = '0000';
// 							}
// 							//============================================================================
// 							$sql_add_01 = "";
// 							$sql_add_02 = "";
// 							$sql_add_03 = "";
// 							$sql_add_04 = "";
// 							$sql_add_05 = "";
// 							//프로젝트 코드/명
// 							if($pjt_codeAndName && $pjt_codeAndName!=""){
// 								$sql_add_01 .= "	 	(   																";
// 								$sql_add_01 .= "	 	P.projectCode LIKE '%$pjt_codeAndName%'			";
// 								$sql_add_01 .= "		OR																	";
// 								$sql_add_01 .= "	 	P.projectViewCode LIKE '%$pjt_codeAndName%'	";
// 								$sql_add_01 .= "		OR																	";
// 								$sql_add_01 .= "	 	P.projectName LIKE '%$pjt_codeAndName%'			";
// 								$sql_add_01 .= "		OR																	";
// 								$sql_add_01 .= "	 	P.ProjectNickname LIKE '%$pjt_codeAndName%'		";
// 								$sql_add_01 .= "		OR																	";
// 								$sql_add_01 .= "	 	P.oldProjectCode LIKE '%$pjt_codeAndName%'		";
// 								$sql_add_01 .= "	 	)    																";
// 							}else{
// 								$sql_add_01 .= "	 	(P.projectViewCode <> ''	)	";
// 							}
// 							//사업종류 코드
// 							if($pjt_product_cd && $pjt_product_cd!=""){
// 								$sql_add_02 .= "	AND																	";
// 								$sql_add_02 .= "	substring(P.projectCode, 2, 1) ='$pjt_product_cd'		";
// 							}
// 							//사업년도
// 							if($pjt_year && $pjt_year!=""){
// 								$pjt_year = substr($pjt_year,2,2);
// 								$sql_add_03 .= "	AND															";
// 								$sql_add_03 .= "	substring(P.projectCode, 3, 2) ='$pjt_year'		";
// 							}
// 							//사업부문 코드
// 							if($pjt_job_cd && $pjt_job_cd!=""){
// 								$sql_add_04 .= "	AND															";
// 								$sql_add_04 .= "	substring(P.projectCode, 5, 2) ='$pjt_job_cd'		";
// 							}
// 							//일련번호
// 							if($pjt_index && $pjt_index!=""){
// 								$sql_add_05 .= "	AND															";
// 								$sql_add_05 .= "	substring(P.projectCode, 7, 2) ='$pjt_index'		";
// 							}
// 							//============================================================================
// 							$sql  = "	 SELECT										";
// 							$sql .= "	 	 P.projectCode							";
// 							$sql .= "	 	,P.projectViewCode						";
// 							$sql .= "	 	,P.oldProjectCode						";
							
// 							$sql .= "	 	,P.oldProjectCode2						";
							
// 							$sql .= "	 	,P.projectName							";
// 							$sql .= "	 	,P.ProjectNickname  as ProjectNickname	";
// 							$sql .= "	 FROM										";
							
// 							$sql .= "	 	 project_tbl P							";
							
// 							$sql .= "	 WHERE										";
// 							$sql .= $sql_add_01;
// 							$sql .= $sql_add_02;
// 							$sql .= $sql_add_03;
// 							$sql .= $sql_add_04;
// 							$sql .= $sql_add_05;
// 							$sql .= "	 	 and P.visible_YN = 'Y'					";
							
							
// 							$sql .= "	ORDER BY P.projectCode						";
// 							//============================================================================

							
									
						//프로젝트 코드검색 : 프로젝트 검색
						//--------------------------------------------------------------------------------------------------
						$pjt_product_cd      = $_REQUEST['pjt_product_cd']==""?"":$_REQUEST['pjt_product_cd']; 			//사업종류 코드
						$pjt_year               = $_REQUEST['pjt_year']==""?"":$_REQUEST['pjt_year']; 								//사업년도
						$pjt_job_cd            = $_REQUEST['pjt_job_cd']==""?"":$_REQUEST['pjt_job_cd']; 						//사업부문 코드
						$pjt_index            = $_REQUEST['pjt_index']==""?"":$_REQUEST['pjt_index']; 								//일련번호
						$pjt_codeAndName   = $_REQUEST['pjt_codeAndName']==""?"":$_REQUEST['pjt_codeAndName']; 	//프로젝트 코드/명
						//============================================================================
						if($pjt_product_cd == 'R' or $pjt_product_cd == 'M' or $pjt_product_cd == 'V'){
							$pjt_year = '0000';
						}
						//============================================================================
						$sql_add_01 = "";
						$sql_add_02 = "";
						$sql_add_03 = "";
						$sql_add_04 = "";
						$sql_add_05 = "";
						//프로젝트 코드/명
						
						
						// projectCode-->oldProjectCode2
						
						if($pjt_codeAndName && $pjt_codeAndName!=""){
							$sql_add_01 .= "	 	(   																";
							$sql_add_01 .= "	 	P.NewProjectCode LIKE '%$pjt_codeAndName%'			";
							$sql_add_01 .= "		OR																	";
							$sql_add_01 .= "	 	P.projectViewCode LIKE '%$pjt_codeAndName%'	";
							$sql_add_01 .= "		OR																	";
							$sql_add_01 .= "	 	P.projectName LIKE '%$pjt_codeAndName%'			";
							$sql_add_01 .= "		OR																	";
							$sql_add_01 .= "	 	P.ProjectNickname LIKE '%$pjt_codeAndName%'		";
							$sql_add_01 .= "		OR																	";
							$sql_add_01 .= "	 	P.oldProjectCode LIKE '%$pjt_codeAndName%'		";
							$sql_add_01 .= "	 	)    																";
						}else{
							$sql_add_01 .= "	 	(P.projectViewCode <> ''	)	";
						}
						//사업종류 코드
						if($pjt_product_cd && $pjt_product_cd!=""){
							$sql_add_02 .= "	AND																	";
							$sql_add_02 .= "	substring(P.NewProjectCode, 2, 1) ='$pjt_product_cd'		";
						}
						//사업년도
						if($pjt_year && $pjt_year!=""){
							$pjt_year = substr($pjt_year,2,2);
							$sql_add_03 .= "	AND															";
							$sql_add_03 .= "	substring(P.NewProjectCode, 3, 2) ='$pjt_year'		";
						}
						//사업부문 코드
						if($pjt_job_cd && $pjt_job_cd!=""){
							$sql_add_04 .= "	AND															";
							$sql_add_04 .= "	substring(P.NewProjectCode, 5, 2) ='$pjt_job_cd'		";
						}
						//일련번호
						if($pjt_index && $pjt_index!=""){
							$sql_add_05 .= "	AND															";
							$sql_add_05 .= "	substring(P.NewProjectCode, 7, 2) ='$pjt_index'		";
						}
						//============================================================================
						$sql  = "	 SELECT										";
						$sql .= "	 	 P.projectCode							";
						$sql .= "	 	,P.projectViewCode						";
						$sql .= "	 	,P.oldProjectCode						";
							
						$sql .= "	 	,P.NewProjectCode						";//P.oldProjectCode2
							
						$sql .= "	 	,P.projectName							";
						$sql .= "	 	,P.ProjectNickname  as ProjectNickname	";
						$sql .= "	 FROM										";
							
						//$sql .= "	 	 project_tbl P							";
						$sql .= "	 	 project_tbl_copy P						";
							
						$sql .= "	 WHERE										";
						$sql .= $sql_add_01;
						$sql .= $sql_add_02;
						$sql .= $sql_add_03;
						$sql .= $sql_add_04;
						$sql .= $sql_add_05;
						
						$sql .= "	 	 and P.projectViewCode is not null		";
						
						$sql .= "	 	 and P.visible_YN = 'Y'					";
						
						$sql .= "	ORDER BY P.projectCode						";
						//============================================================================
						
						//--------------------------------------------------------------------------------------------------
						$query_data01 = array();
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
								//$strCheck = $re_row[P_CodeCheck01];
								$re_row[ProjectNicknameShort] = getStrShort($re_row[ProjectNickname], 20, '');
						
								$re_row[existYN] ="Y";
								/*---------------------------------*/
								array_push($query_data01,$re_row);
								/*---------------------------------*/
							}//while
						}else{
							
							$query_data01 = array('existYN'=>'N');
						}//if
						/*---------------------------------*/
						//$this->assign($assignName,$query_data01);
						return print_r( urldecode( json_encode( $query_data01 ) ) );
						
				break;

			//=========================================================================
			case "activityCode":
				//최근에 관여한 프로젝트를 자동 조회
				$query_data01 = array();
				$query_data01 = getActivityCodeList($itemkey4, '', '');//Activity 코드 불러오기  //$itemkey4==H05-IT-04(기존 한맥프로젝트코드) /inc/function_intranet.php : Activity 코드 불러오기
				return print_r( urldecode( json_encode( $query_data01 ) ) );
				break;


			//=========================================================================
			case "currentWorkProject":
				
				
				//최근에 관여한 프로젝트를 자동 조회
				$query_data01 = array();
				$query_data01 = getLastAddWork($GB_user_id, '');
				return print_r( urldecode( json_encode( $query_data01 ) ) );
				break;


			//=========================================================================
			case "projectCodeJob":
				//프로젝트검색 :  사업부문 option값 호출
				$query_data01 = array();
				$query_data01 = getSelectOption('projectCodeJob', $itemkey5, '');///inc/function_intranet.php
				return print_r( urldecode( json_encode( $query_data01 ) ) );
				break;

			//=========================================================================
			case "projectCodeActivity": //itemkey2
				//  Activity option값 호출
				//Activity코드 option 호출--------------
				//$ActionMode : json
				//$itemkey1=분기값1 : changeOption
				//$itemkey2=분기값2 : projectCodeActivity
				//$itemkey3=
				//$itemkey4=리턴액션적용 ID : EntryJobCode
				//$itemkey5=프로젝트코드
				//$returnType
				//$callback
				//-------------------------------------
				$query_data01 = array();

				$query_data01 = getActivityCodeList($itemkey5, '', '');//(프로젝트코드,예비,예비) /inc/function_intranet.php : Activity 코드 불러오기
				return print_r( urldecode( json_encode( $query_data01 ) ) );


				break;
			//=========================================================================
			case "getProjectInfoAll": //  $itemkey2
					//Activity코드 option 호출--------------
					//ActionMode : json
					//itemkey1=분기값1 : getProjectInfoAll
					//itemkey2=입력된 프로젝트번호
					//itemkey3=
					//itemkey4=
					//itemkey5=
					//returnType
					//callback
					//-------------------------------------
				$query_data01 = array();

				$query_data01 = getProjectInfoAll($itemkey2, 'projectViewCode', '');//(프로젝트코드, 조회대상 컬럼 존재시 입력 ,예비) /inc/function_intranet.php : Activity 코드 불러오기
				return print_r( urldecode( json_encode( $query_data01 ) ) );


				break;
			//=========================================================================
			case "aaaa":
				break;
				//=========================================================================
			default:
				break;
		}//switch
		//-----------------------------------------------------------------------




	}  //returnData_Json End



	//SELECT BOX : OPTIONS 값
	////////////////////////////////////////////////////////////////
	function selectItem($Param_01,$Param_02,$assignName)
	{
		global $GB_date_today_yyyy;
		global $db; //DB conn
		switch($Param_01)
		{
			case "project_code_product_tbl":
				// 프로젝트 코드 중 사업의 종류를 정의하는 테이블
				//selectItem("project_code_product_tbl", "", "optionList_01")
				$assignName= $assignName==""?$Param_01:$assignName;
				//  Start *********************************************************** */
				$sql = " SELECT  * FROM project_code_product_tbl  ";
				/*---------------------------------*/
				$query_data01 = array();
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
						$re_row[re_id] = $re_row[product_code];
						$re_row[re_name] = $re_row[product_name];
						/*---------------------------------*/
						array_push($query_data01,$re_row);

					}//while
				}//if

				/*---------------------------------*/
				$this->smarty->assign($assignName,$query_data01);
				break;
			//=========================================================================
			case "project_code_job_tbl":
				// 프로젝트 코드 중 사업부문 종류를 정의하는 테이블
				//selectItem("project_code_job_tbl", "", "optionList_01")
				$assignName= $assignName==""?$Param_01:$assignName;
				//  Start *********************************************************** */
				$sql = " SELECT  * FROM project_code_job_tbl  ";
				/*---------------------------------*/
				$query_data01 = array();
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
						$re_row[re_id] = $re_row[job_code];
						$re_row[re_name] = $re_row[job_name];
// 						$re_row[job_dept] = $re_row[job_dept];
// 						$re_row[job_desc] = $re_row[job_desc];
						/*---------------------------------*/
						array_push($query_data01,$re_row);

					}//while
				}//if
				/*---------------------------------*/
				$this->smarty->assign($assignName,$query_data01);
				break;

			//=========================================================================
			case "year":
				// 년도값을 리턴 :  현재년도+-$Param_02값
				//selectItem("year", "10", "optionList_01")
				$assignName= $assignName==""?$Param_01:$assignName;
				$Param_02=$Param_02==""?"10":$Param_02;

				$from = intval($GB_date_today_yyyy)-intval($Param_02);
				$to = intval($GB_date_today_yyyy)+intval($Param_02);

				$query_data01 = array();
				for($i=$from;$i<=$to;$i++){
					array_push($query_data01, strval($i));
				}
				$this->smarty->assign($assignName,$query_data01);
				break;

			//=========================================================================
			case "month":
				// month값을 리턴 : 01~12
				//selectItem("month", "", "optionList_01")
				$assignName= $assignName==""?$Param_01:$assignName;
				$query_data01 = array();
				for($i=1;$i<=13;$i++){
					array_push($query_data01,$i);
				}
				$this->smarty->assign($assignName,$query_data01);
				break;

			//=========================================================================
			default:
				break;
		}//switch

	}//selectItem









	////////////////////////////////////////////////////////////////
	//사용쿼리 선택
	function Query_select($Param01, $Param02)
	{
		global $db;
		global $GB_user_id, $GB_user_GroupCode, $GB_date_today, $GB_date_today2, $GB_date_today_yyyy, $GB_date_today_mm, $GB_date_today_yyyymmdd,$GB_date_today_FULL;
		extract($_REQUEST);
		//------------------------
		//global $db; //DB conn
		global $ActionMode;
		global $ajaxJson;//POPUP창으로 결과값(JSON)리턴 여부 : Y
		global $requestDataYN;

		//$Param01, $Param02
		$Param01 = $Param01==""?"":$Param01;//사용분기
		$Param02 = $Param02==""?"":$Param02;//예비1
		//------------------------------------
		$returnQuery = "";
		//------------------------------------
		if($Param01){
			//=========================================================================================
			if($Param01=="personProposal"){
					//개인 건의사항
					//Activity option값 호출
					//Activity코드 option 호출--------------
					//$ActionMode : json
					//$itemkey1=분기값1 : personProposal
					//$itemkey2=분기값2 : 쿼리구분(insert/update/delete)
					//$itemkey3=
					//$itemkey4=
					//$itemkey5=
					//$returnType
					//$callback
					//-------------------------------------
					if($Param02=="insert"){
						//호출 : searchCommon(ActionMode, 'personProposal', 'insert', '', '', '', '', '')
						$proposal 	= $_REQUEST['proposal']==""?"":$_REQUEST['proposal']; //건의의견

// 						$returnQuery  = "
// 										INSERT INTO person_proposal_tbl
// 										(memberNo, GroupCode, write_dt, proposal)
// 										 VALUES
// 										 (
// 										 '$GB_user_id'
// 										 ,'$GB_user_GroupCode'
// 										 ,'$GB_date_today_FULL'
// 										 ,'$proposal'
// 										 )
// 										";

						//------------------------------------------------------------------------
						$sql2 = "select max(thread) as thread  from person_proposal_tbl";
						$re2 = mysql_query($sql2,$db);
						$thread= mysql_result($re2,0,"thread");
						$max_thread = ceil($thread/10)*10+10;
						$depth="0";
						//------------------------------------------------------------------------
						$returnQuery = "
												INSERT INTO person_proposal_tbl
												(thread,depth,memberNo,GroupCode,write_dt,proposal)
												VALUES
												('$max_thread', '$depth', '$GB_user_id', '$GB_user_GroupCode', now(), '$proposal')
												";
						//------------------------------------------------------------------------


						//echo $returnQuery;
						//-----------------------------------------
						$array_returnQuery = array();
						//-----------------------------------------
						array_push($array_returnQuery,$returnQuery);
						//-----------------------------------------

					}else if($Param02=="update"){

					}else if($Param02=="delete"){

					}

			}else if($Param01=="test"){
				//$ActionMode=executeDB
				//$ActionDetail_01=typefrm11501_pop
				//$ActionDetail_02=save
				// 실행예산 종결 및 잔액처리
				//사용화면 :  : 직접경비조회(Screen_01_201) : 직접경비내역저장 버튼클릭시 : 팝업발생 : 저장
				//usp_ys_pbudget_close_when
				$pjt_no 	= $_REQUEST['pjt_no']==""?"":$_REQUEST['pjt_no'];
				$main 		= $_REQUEST['main']==""?"":$_REQUEST['main'];
				$sub 		= $_REQUEST['sub']==""?"":$_REQUEST['sub'];
				$bud_seq = $_REQUEST['bud_seq']==""?"":$_REQUEST['bud_seq'];
				//----------------------------
				$closing_yn     = $_REQUEST['closing_yn']==""?"":$_REQUEST['closing_yn'];            //예산종결 여부
				$remain_when  = $_REQUEST['remain_when']==""?"":$_REQUEST['remain_when'];		//잔여사용[When]
				$remain_when=str_replace("-",".",$remain_when); //형식변환(YYYY-M-DD => YYYY.MM.DD)
				$remain_how    = $_REQUEST['remain_how']==""?"":$_REQUEST['remain_how'];		//잔여사용[How]
				$remain_how    = $this->HangleEncodeUTF8_EUCKR($remain_how);

				////////////////////////////////////////
				if($Param02=="save"){
					// 						ALTER PROCEDURE dbo.usp_ys_pbudget_close_when
					// 						@pjt_no			CHAR(8)
					// 						,	@main			CHAR(4)
					// 						,	@sub			CHAR(3)
					// 						,	@bud_seq		INT
					// 						,	@closing_yn		CHAR(1)
					// 						,	@remain_when	DATETIME
					// 						,	@remain_how	VARCHAR(200)
					$returnQuery  = "  HPOIMS.dbo.usp_ys_pbudget_close_when   ";
					$returnQuery .= " 	  '$pjt_no'  			";
					$returnQuery .= " 	, '$main' 	 			";
					$returnQuery .= " 	, '$sub'  				";
					$returnQuery .= " 	, '$bud_seq'  		";
					$returnQuery .= " 	, '$closing_yn'  	";
					$returnQuery .= " 	, '$remain_when'  ";
					$returnQuery .= " 	, '$remain_how'  	";
					//-----------------------------------------
					$array_returnQuery = array();
					//-----------------------------------------
					array_push($array_returnQuery,$returnQuery);

					//-----------------------------------------
				}
				////////////////////////////////////////

			}else if($Param01=="bbbb"){
				////////////////////////////////////////
				////////////////////////////////////////

			}

		}else{
			$array_returnQuery = "";
		}
		return $array_returnQuery;
	}//Query_select


	////////////////////////////////////////////////////////by Moon
	//MYSQL  트랜잭션 처리
	function TransactionArrayQuery_forMysql($val01,$val02)	//트랜젝션동작 FUNCTION : TransactionArrayQuery(Param01=array쿼리값, Param02=예비)
	{
		global $db;
		//-----------------------------------------------------
		$arrayQuery = $val01; //넘겨받은 array(쿼리)
		$val02 = $val02;
		//-----------------------------------------------------

			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			$ResultDB_Query_01_Array = $arrayQuery;
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			$Array_Count = count($ResultDB_Query_01_Array);
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			//트랜잭션 동작 Start /////////////////////////////////////////////////////////////////////////////
			//작업성공여부 플래그 값
			$success01 = true;
			//트랜잭션 시작
			$result = @mysql_query("SET AUTOCOMMIT=0",$db);
			$result = @mysql_query("BEGIN",$db);

			for($q=0;$q<$Array_Count;$q++){
					//첫번째 DB실행
					$result = mysql_query($ResultDB_Query_01_Array[$q], $db) ; // 내용입력 쿼리  // INSERT 쿼리

					if(!$result ){//쿼리실행 실패시
						//echo "222";
						$success01 = false;
						break;
					}else{
						//쿼리실행 성공시
					}
			}//for

			if(!$success01){
				//실패
				$result = @mysql_query("ROLLBACK",$db);

				$result_01 = "4";
				$result_02 = "Fail(Execute_DB)";
				$result_03 = $ResultDB_Query_01_Array[0];

			}else{
				//성공
				$result = @mysql_query("COMMIT",$db);
				$result_01 = "1";
				$result_02 = "SUCCESS(Execute_DB)";
				//$result_03 = "";
				$result_03 = '';
			}//if
			//트랜잭션 동작 End /////////////////////////////////////////////////////////////////////////////
			/* ============================================================================================ */

		$data = array(
		'result_01'=>$result_01,
		'result_02'=>$result_02,
		'result_03'=>$result_03,
		);
		////////////////////
		return $data;
		////////////////////

		/* 사용시 참조
		 //트랜젝션 Start/////////////////////////////////////////////////////////////
		 $arrayQuery=array();
		 array_push($arrayQuery, $sql01);
		 //array_push($arrayQuery, $sql02);
		 //트랜젝션동작 FUNCTION : TransactionArrayQuery_forMysql(Param01=array쿼리값, Param02=예비)
		 $resultArray = TransactionArrayQuery_forMysql($arrayQuery,'');
		 $result_01 = $resultArray['result_01'];
		 $result_02 = $resultArray['result_02'];
		 $result_03 = $resultArray['result_03'];
		 //트랜젝션 End/////////////////////////////////////////////////////////////
		 */

	}//TransactionArrayQuery_forMysql


	////////////////////////////////////////////////////////////////
	//MY SQL용
	function ExecuteDB()
	{
		global $GB_user_id, $GB_user_GroupCode, $GB_date_today, $GB_date_today2, $GB_date_today_yyyy, $GB_date_today_mm, $GB_date_today_yyyymmdd;
		extract($_REQUEST);
		//------------------------
		global $db; //DB conn
		global $ActionMode;

		global $itemkey1,$itemkey2 ,$itemkey3 ,$itemkey4 ,$itemkey5 ,$returnType ,$callback;
		switch($ActionMode)
		{
			//=========================================================================
			case "executeDB":

				if($itemkey1=="personProposal"){
					//개인 건의사항
					//$ActionMode : json
					//$itemkey1=분기값1 : personProposal
					//$itemkey2=분기값2 : 쿼리구분(insert/update/delete)
					//$itemkey3=
					//$itemkey4=
					//$itemkey5=
					//$returnType
					//$callback
					//-------------------------------------
					//-----------------------------------------
					$use_query = $this->Query_select($itemkey1, $itemkey2);//쿼리생성(P1,P2)
					//-----------------------------------------
					//echo $use_query;
					//트랜젝션 Start/////////////////////////////////////////////////////////////
					//트랜젝션동작 FUNCTION : TransactionArrayQuery(Param01=array쿼리값, Param02=예비)
					$resultArray = $this->TransactionArrayQuery_forMysql($use_query,'');
					$result_01 = $resultArray['result_01'];
					$result_02 = $resultArray['result_02'];
					$result_03 = $resultArray['result_03'];
					//트랜젝션 End/////////////////////////////////////////////////////////////
					$resultArray = array(
					'result_01'=>$result_01
					,'result_02'=>$result_02
					,'result_03'=>$result_03
					);
					return print_r( urldecode( json_encode( $resultArray ) ) );
					//echo urldecode( json_encode( $resultArray ) ); //동작

				}else if($itemkey1=="testForm"){

					// 		$call_sql="  call Procedure_pro_test_insert('qwe','123','sdf') ";
					// 		$result111 = mysql_query($call_sql, $db) ;


// 											//$ActionMode=executeDB
// 											//$ActionDetail_01=typefrm11501_pop
// 											//$ActionDetail_02=save
// 											// 실행예산 종결 및 잔액처리 : 저장
// 											//사용화면 :  : 직접경비조회(Screen_01_201) : 직접경비내역저장 버튼클릭시 : 팝업발생 : 저장
// 											//usp_ys_pbudget_close_when
// 											//-----------------------------------------
// 											$use_query = $this->Query_select($ActionDetail_01, $ActionDetail_02);//쿼리생성(P1,P2)
// 											//-----------------------------------------
// 											//echo $use_query;
// 											//트랜젝션 Start/////////////////////////////////////////////////////////////
// 											//트랜젝션동작 FUNCTION : TransactionArrayQuery(Param01=array쿼리값, Param02=예비)
// 											$resultArray = $this->TransactionArrayQuery_forMysql($use_query,'');
// 											$result_01 = $resultArray['result_01'];
// 											$result_02 = $resultArray['result_02'];
// 											$result_03 = $resultArray['result_03'];
// 											//트랜젝션 End/////////////////////////////////////////////////////////////
// 											$resultArray = array(
// 											'result_01'=>$result_01
// 											,'result_02'=>$result_02
// 											,'result_03'=>$result_03
// 											);
// 											return print_r( urldecode( json_encode( $resultArray ) ) );
// 											//echo urldecode( json_encode( $resultArray ) ); //동작
// 											//결과확인용 쿼리
// 											// 						SELECT * FROM
// 											// 							HPOIMS.dbo.ys_pbudget
// 											// 						WHERE
// 											// 							pjt_no = 'V121T205'
// 											// 							AND main = '9D'
// 											// 							AND sub = '016'
// 											// 							AND bud_seq = '1'


				}
				break;


				//=========================================================================
			default:

				break;
		}//switch
		//-----------------------------------------------------------------------

	}//ExecuteDB




	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	//공통기능
	////////////////////////////////////////////////////////////////
	function HangleEncodeUTF8_EUCKR($item)
	{
		$result=trim(ICONV("UTF-8","EUC-KR",$item));
		return $result;
	}

	////////////////////////////////////////////////////////////////
	function HangleEncode($item)
	{
		$result=trim(ICONV("EUC-KR","UTF-8",$item));
		if(trim($result)=="") 	$result="&nbsp;";
		return $result;
	}

	////////////////////////////////////////////////////////////////
	function bear3StrCut($str,$len,$tail="..."){
		$rtn = array();
		return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str;
	}

	////////////////////////////////////////////////////////////////
	function GetDateFormat($i_date)
	{
		$ret="";
		$ret=str_replace("-","",$i_date);
		$ret=str_replace(".","",$ret);
		return $ret;
	}

//========================================================================================
} // class END






?>
