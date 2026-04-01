<?php

	/*
	* -----------------------------------------------------------------------------------
	*  수 정 날 짜 |    작 성 자    |  수정내용
	* -----------------------------------------------------------------------------------


	*/
	extract($_REQUEST);
	//header('Content-Type: text/html; charset=UTF-8');

	//회사별로 DB연결 다르게.
	if( $COMPANY == 'JANG' || $COMPANY == 'PTC' || $COMPANY == 'HYUNTA' ){
		include "../inc/dbcon_".$COMPANY.".inc";	//인트라넷 DB연결
	}elseif( $COMPANY == 'HANMAC' || $COMPANY == 'SAMAN' ){
		include "../util/OracleClass.php";
	}elseif( $COMPANY == 'HALLA'){
		include "../util/MssqlClass.php";
	}else{
		include "../inc/dbcon.inc";	//인트라넷 DB연결
	}
	// include "../inc/dbcon_HANMAC.inc";	//인트라넷 DB연결

	include "../../../SmartyConfig.php";
	include "../inc/function_add2.php";
	include "../inc/getNeedDate.php";
	/*include "../util/AutoGrid_Jqgrid.php";*/

	class PersonInsaReportLogic {
		var $smarty;
		var $year;
		var $start_month;
		var $start_day;
		var $end_month;
		var $end_day;
		var $memo;
		var $QueryDay;
		var $QueryDay2;
		var $oracle;

		function PersonInsaReportLogic($smarty, $COMPANY)
		{
			global $emp_id;
			if( $COMPANY == 'HANMAC' || $COMPANY == 'SAMAN' ){
				$this->oracle=new OracleClass($smarty, $COMPANY);
			}elseif( $COMPANY == 'HALLA'){
				$this->mssql=new MssqlClass($smarty);
			}else{
				$this->oracle=new OracleClass($smarty, $COMPANY);
            }

			$this->smarty=$smarty;

			$this->PRINTYN=$_REQUEST['PRINT'];
			$this->excel=$_REQUEST['excel'];
			$this->start_day=$_REQUEST['start_day'];
			$this->end_day=$_REQUEST['end_day'];
			//$this->memo=trim($_REQUEST['memo']);


			if($this->start_day == "")
				$this->start_day=date("Y").date("m").date("d");

			if($this->end_day == "")
				$this->end_day=date("Y").date("m").date("d");

			$this->start_day=str_replace("-","",$this->start_day);
			$this->start_day=str_replace(".","",$this->start_day);

			$this->end_day=str_replace("-","",$this->end_day);
			$this->end_day=str_replace(".","",$this->end_day);


			$QueryStartDate=$this->start_day;
			$QueryEndDate=$this->end_day;

			$ActionMode=$_REQUEST['ActionMode'];
			$this->smarty->assign('ActionMode',$ActionMode);
			$this->smarty->assign('excel',$this->excel);
			$this->smarty->assign('print',$this->print);
			$this->smarty->assign('search_month',date("Y")."-".date("m"));
			$this->smarty->assign('current_year',date("Y"));
			$this->smarty->assign('current_month',date("Y")."-".date("m"));
			$this->smarty->assign('current_day',date("Y")."-".date("m")."-".date("d"));
			$this->smarty->assign('now_year',date("Y"));
			$this->smarty->assign('now_month',date("m"));

			$this->smarty->assign('displaylist_01',$_REQUEST['displaylist_01']);
			$this->smarty->assign('displaylist_02',$_REQUEST['displaylist_02']);
			$this->smarty->assign('displaylist_03',$_REQUEST['displaylist_03']);
			$this->smarty->assign('displaylist_04',$_REQUEST['displaylist_04']);
			$this->smarty->assign('displaylist_05',$_REQUEST['displaylist_05']);
			$this->smarty->assign('displaylist_06',$_REQUEST['displaylist_06']);
			$this->smarty->assign('displaylist_07',$_REQUEST['displaylist_07']);

			$this->DefaultView="";
			$this->DefaultView=$this->DefaultView."&displaylist_01=".$_REQUEST['displaylist_01'];
			$this->DefaultView=$this->DefaultView."&displaylist_02=".$_REQUEST['displaylist_02'];
			$this->DefaultView=$this->DefaultView."&displaylist_03=".$_REQUEST['displaylist_03'];
			$this->DefaultView=$this->DefaultView."&displaylist_04=".$_REQUEST['displaylist_04'];
			$this->DefaultView=$this->DefaultView."&displaylist_05=".$_REQUEST['displaylist_05'];
			$this->DefaultView=$this->DefaultView."&displaylist_06=".$_REQUEST['displaylist_06'];
			$this->DefaultView=$this->DefaultView."&displaylist_07=".$_REQUEST['displaylist_07'];



			$this->userid = $_SESSION['planning_user_id'];
			$this->deptcode = $_SESSION['planning_user_deptcode'];

			extract($_REQUEST);
			if($this->userid =="") 	$this->userid=$userid;
			if($this->deptcode =="") 	$this->deptcode=$deptcode;

			$this->smarty->assign('Read_Write',$Read_Write);
			$this->smarty->assign('planning_user_id',$_SESSION['planning_user_id']);
			$this->smarty->assign('planning_user_deptcode',$_SESSION['planning_user_deptcode']);

			//$CommonCode = new CommonCodeList ( $this->smarty );
			//$this->DeveloperYN = $CommonCode->ConfirmDeveloperYN ( $_SESSION ['planning_user_id'], "DeveloperYN", "assign" );
		}

		//=================================================
		//사이트맵
		//=================================================
		function REPORT_00(){
			$this->smarty->display("planning_mng/insaReport/PersonReport_00_Main_mvc.tpl");
		}

		//=================================================
		//인사정보
		//=================================================
		function REPORT_01(){
			extract($_REQUEST);

			if($COMPANY == ''){ $COMPANY = 'SAMAN'; }
			//if($MEMBERID == ''){ $MEMBERID = '216070'; }
//print_R($_REQUEST);
			//$CommonCode=new CommonCodeList($this->smarty);
			$this->smarty->assign( 'COMPANY' , $COMPANY );
			$this->smarty->assign( 'SUBCOMPANY' , $SUBCOMPANY );
			$this->smarty->assign( 'MEMBERID' , $MEMBERID );
			$this->smarty->assign( 'EMP_NAME' , $EMP_NAME );
			$this->smarty->assign( 'MainAction' , $MainAction );
			$this->smarty->assign( 'DEPT' , $DEPT );
			$this->smarty->assign( 'PDF' , $PDF );
			$this->smarty->assign( 'PRINT_TYPE' , $PRINT_TYPE );

			$this->smarty->assign( 'test' , $test );
			switch($MainAction){
				case "HTML_Ajax_01": 	$this->REPORT_01_HTML_Ajax_01();	break;
				default:
					global $db;
					$MEMBER_ARR = array();
					if($DEPT != ''){
						$azsql = "
							SELECT Company, MemberNo, HeadType, korName, JuminNo
							FROM member_tbl
							where
								GroupCode = ".$DEPT."
								and WorkPosition = 1
							order by RankCode
						;";
						//echo $azsql;
						$re = mysql_query($azsql,$db);
						while($row=mysql_fetch_array($re)){
							if( $row['HeadType'] == '' or $row['HeadType'] == NULL ){
								$row['HeadType'] = $row['MemberNo'];
							}
							if($row['Company'] == 'BARON'){
								$row['SUBCOMPANY'] = 'BARON';
								$row['Company'] = 'HANMAC';
							}
							array_push( $MEMBER_ARR, array( "COMPANY"=>$row['Company'], "SUBCOMPANY"=>$row['SUBCOMPANY'], "MEMBERID"=>$row['HeadType'], "EMP_NAME"=>$row['korName'], "JuminNo"=>substr( $row['JuminNo'], 0, 6 ) ) );
						}
					}else{
						array_push( $MEMBER_ARR, array( "COMPANY"=>$COMPANY, "SUBCOMPANY"=>$SUBCOMPANY, "MEMBERID"=>$MEMBERID, "EMP_NAME"=>$EMP_NAME, "JuminNo"=>$JuminNo, "JuminNo2"=>$JuminNo2 ) );
					}

					//print_r($MEMBER_ARR);

					$this->smarty->assign( 'MEMBER_ARR' , $MEMBER_ARR );
					if( $test == '' ){
						if($COVER_TYPE == 'PRINT'){
							$this->smarty->display("planning_mng/insaReport/PersonReport_01_PRINT_mvc.tpl");
						}else{
							$this->smarty->display("planning_mng/insaReport/PersonReport_01_Main_mvc.tpl");
						}
					}else{
						$this->smarty->display("planning_mng/insaReport/PersonReport_01_Main_mvc_".$test.".tpl");
					}
					break;
			}
		}

		function REPORT_01_HTML_Ajax_01($mode=true){
			extract($_REQUEST);
//print_r($_REQUEST);
			$this->smarty->assign( 'test' , $test );
			if( $WORK_LIST_MODE == null or $WORK_LIST_MODE == '' ){ $WORK_LIST_MODE = 'limit'; } // none limit all
			if( $EDUCATION_LIST_MODE == null or $EDUCATION_LIST_MODE == '' ){ $EDUCATION_LIST_MODE = 'limit'; } // none limit all
			if( $ISSUANCE_LIST_MODE == null or $ISSUANCE_LIST_MODE == '' ){ $ISSUANCE_LIST_MODE = 'limit'; } // none limit all
			if( $REWARD_LIST_MODE == null or $REWARD_LIST_MODE == '' ){ $REWARD_LIST_MODE = 'limit'; } // none limit all
			if( $ETC_LIST_MODE == null or $ETC_LIST_MODE == '' ){ $ETC_LIST_MODE = 'limit'; } // none limit all

			$WORK_LIST_LIMIT = 5; //경력 정보 제한수
			$EDUCATION_LIST_LIMIT = 5; //교육 정보 제한수
			$ISSUANCE_LIST_LIMIT = 10; //발령 정보 제한수
			$REWARD_LIST_LIMIT = 4; //상벌 정보 제한수
			$ETC_LIST_LIMIT = 6; //특이사항 및 기타

			//$PDF = 'PDF';

			$this->smarty->assign( 'PDF' , $PDF );
			$this->smarty->assign( 'OPENER' , $OPENER );
			$this->smarty->assign( 'KORNAME_ori' , str_replace('<span class="word_space"></span>', "", $KORNAME) );
			$this->smarty->assign( 'WORK_LIST_MODE' , $WORK_LIST_MODE );
			$this->smarty->assign( 'EDUCATION_LIST_MODE' , $EDUCATION_LIST_MODE );
			$this->smarty->assign( 'ISSUANCE_LIST_MODE' , $ISSUANCE_LIST_MODE );
			$this->smarty->assign( 'REWARD_LIST_MODE' , $REWARD_LIST_MODE );
			$this->smarty->assign( 'ETC_LIST_MODE' , $ETC_LIST_MODE );

			//회사별로 db별로 다르게 연결
			// 회사별로 $COMPANY 변수에 값 다르게 넘겨주면 DB연결 구분
			if($COMPANY == 'HANMAC'){	//한맥
				if( $MEMBERID == 'no' ){
					//이름으로 사번 찾기
					//echo base64_decode($EMP_NAME);
					$azsql ="BEGIN Usp_plan_person_member(:entries,'".$this->HangleEncodeUTF8_EUCKR(urldecode($EMP_NAME))."', '".substr( $JuminNo, 0, 6 )."'); END;";	//기본정보
					//echo $azsql;
					$datarow00 = $this->oracle->LoadProcedure_empty($azsql,"list_data01","");
					$MEMBERID = $datarow00[0]['EMP_NO'];
					$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				}

				$azsql ="BEGIN Usp_plan_person_0100(:entries,'$MEMBERID'); END;";	//기본정보
				$datarow00 = $this->oracle->LoadProcedure_empty($azsql,"list_data01","");

				$this->smarty->assign( 'PHOTO' , 'http://erp.hanmaceng.co.kr/erpphoto/'.$MEMBERID.'.jpg' );

				$KORNAME_cnt = mb_strlen($datarow00[0]['KORNAME'], 'utf-8');
				$KORNAME = '';
				for($i=0; $i < $KORNAME_cnt; $i++){
					if($i != 0){
						$KORNAME .= '<span class="word_space"></span>';
					}
					$KORNAME .= mb_substr( $datarow00[0]['KORNAME'], $i, 1, 'utf-8' );
				}
				$this->smarty->assign( 'KORNAME' , $KORNAME );

				$ENGNAME_arr = explode( ' ', $datarow00[0]['ENGNAME'] );
				$ENGNAME = '';
				for($i=0; $i < count($ENGNAME_arr); $i++){
					if($i != 0){
						$ENGNAME .= '<span class="word_space"></span>';
					}
					$ENGNAME .= $ENGNAME_arr[$i];
				}
				$this->smarty->assign( 'ENGNAME' , $ENGNAME );

				if($SUBCOMPANY == 'BARON'){
					$this->smarty->assign( 'COMPANY_NAME' , '주식회사 바론컨설턴트' );
				}else{
					$this->smarty->assign( 'COMPANY_NAME' , '(주) 한맥기술' );
				}
				$this->smarty->assign( 'DEPT' , $datarow00[0]['DEPT'] );
				$this->smarty->assign( 'RANK' , $datarow00[0]['RANK'] );
				$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				$this->smarty->assign( 'EXTNO' , $datarow00[0]['EXTNO'] );
				$TEMP_EMAIL = explode( '@', $datarow00[0]['EMAIL'] );
				$TEMP_EMAIL_0 = $TEMP_EMAIL[0];
				unset($TEMP_EMAIL[0]);
				$this->smarty->assign( 'EMAIL' , $TEMP_EMAIL_0.'<br>@'.implode(" ", $TEMP_EMAIL) );
				$this->smarty->assign( 'BIRTH' , $this->GetDateFormat($datarow00[0]['BIRTH']) );
				$this->smarty->assign( 'MOBILE1' , $this->set_mobile_number($datarow00[0]['MOBILE1']) );
				$this->smarty->assign( 'MOBILE2' , $this->set_mobile_number($datarow00[0]['MOBILE2']) );

				$TEMP_ADDRESS = explode( ' ', $datarow00[0]['ADDRESS'] );
				$TEMP_ADDRESS_0 = $TEMP_ADDRESS[0];
				unset($TEMP_ADDRESS[0]);
				if(is_numeric($TEMP_ADDRESS_0)){
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.'<br>'.implode(" ", $TEMP_ADDRESS) );
				}else{
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.' '.implode(" ", $TEMP_ADDRESS) );
				}

				$this->smarty->assign( 'SERVICE' , $datarow00[0]['SERVICE'] );

				$azsql ="BEGIN Usp_plan_person_0101(:entries,'$MEMBERID'); END;";	//학력정보
				$this->smarty->assign( 'SCHOOL_LIST' , $this->oracle->LoadProcedure_empty($azsql,"list_data01","") );

				$azsql ="BEGIN Usp_plan_person_0102(:entries,'$MEMBERID'); END;";	//자격정보
				$this->smarty->assign( 'LICENSE_LIST' , $this->oracle->LoadProcedure_empty($azsql,"list_data01","") );

				$azsql ="BEGIN Usp_plan_person_0103(:entries,'$MEMBERID'); END;";	//경력정보
				$this->SET_LIST( $LIST_MODE, 'WORK_LIST', $this->oracle->LoadProcedure_empty($azsql,"list_data01",""), $WORK_LIST_LIMIT );

				$azsql ="BEGIN Usp_plan_person_0104(:entries,'$MEMBERID'); END;";	//교육정보
				$this->SET_LIST( $LIST_MODE, 'EDUCATION_LIST', $this->oracle->LoadProcedure_empty($azsql,"list_data01",""), $EDUCATION_LIST_LIMIT );

				$azsql ="BEGIN Usp_plan_person_0105(:entries,'$MEMBERID'); END;";	//발령정보
				$this->SET_LIST( $LIST_MODE, 'ISSUANCE_LIST', $this->oracle->LoadProcedure_empty($azsql,"list_data01",""), $ISSUANCE_LIST_LIMIT );

				$azsql ="BEGIN Usp_plan_person_0106(:entries,'$MEMBERID'); END;";	//상벌정보
				$this->SET_LIST( $LIST_MODE, 'REWARD_LIST', $this->oracle->LoadProcedure_empty($azsql,"list_data01",""), $REWARD_LIST_LIMIT );

				$azsql ="BEGIN Usp_plan_person_0107(:entries,'$MEMBERID'); END;";	//특이사항 및 기타
				$this->SET_LIST( $LIST_MODE, 'ETC_LIST', $this->oracle->LoadProcedure_empty($azsql,"list_data01",""), $ETC_LIST_LIMIT );
			}elseif($COMPANY == 'SAMAN'){	//삼안
				if( !is_numeric( substr( $MEMBERID, 0, 1 ) ) or $MEMBERID == 'no' ){
					//이름으로 사번 찾기
					//echo base64_decode($EMP_NAME);
					$azsql ="BEGIN Usp_plan_person_member(:entries,'".$this->HangleEncodeUTF8_EUCKR(urldecode($EMP_NAME))."', '".substr( $JuminNo, 0, 6 )."'); END;";	//기본정보
					// $azsql ="BEGIN Usp_plan_person_member(:entries,'".$this->HangleEncodeUTF8_EUCKR($EMP_NAME)."', '".substr( $JuminNo, 0, 6 )."'); END;";	//기본정보
					//echo $azsql;
					$datarow00 = $this->oracle->LoadProcedure_empty($azsql,"list_data01","");
					$MEMBERID = $datarow00[0]['EMP_NO'];
					$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				}
				
				$azsql ="BEGIN Usp_plan_person_0100(:entries,'$MEMBERID'); END;";	//기본정보
				$datarow00 = $this->oracle->LoadProcedure_empty($azsql,"list_data01","");
				

				$this->smarty->assign( 'PHOTO' , 'http://erp.samaneng.com/erpphoto/'.$MEMBERID.'.jpg' );

				$KORNAME_cnt = mb_strlen($datarow00[0]['KORNAME'], 'utf-8');
				$KORNAME = '';
				for($i=0; $i < $KORNAME_cnt; $i++){
					if($i != 0){
						$KORNAME .= '<span class="word_space"></span>';
					}
					$KORNAME .= mb_substr( $datarow00[0]['KORNAME'], $i, 1, 'utf-8' );
				}
				$this->smarty->assign( 'KORNAME' , $KORNAME );

				$ENGNAME_arr = explode( ' ', $datarow00[0]['ENGNAME'] );
				$ENGNAME = '';
				for($i=0; $i < count($ENGNAME_arr); $i++){
					if($i != 0){
						$ENGNAME .= '<span class="word_space"></span>';
					}
					$ENGNAME .= $ENGNAME_arr[$i];
				}
				$this->smarty->assign( 'ENGNAME' , $ENGNAME );
				$this->smarty->assign( 'COMPANY_NAME' , '(주) 삼안' );
				$this->smarty->assign( 'DEPT' , $datarow00[0]['DEPT'] );
				$this->smarty->assign( 'RANK' , $datarow00[0]['RANK'] );
				$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				$this->smarty->assign( 'EXTNO' , $datarow00[0]['EXTNO'] );
				$TEMP_EMAIL = explode( '@', $datarow00[0]['EMAIL'] );
				$TEMP_EMAIL_0 = $TEMP_EMAIL[0];
				unset($TEMP_EMAIL[0]);
				$this->smarty->assign( 'EMAIL' , $TEMP_EMAIL_0.'<br>@'.implode(" ", $TEMP_EMAIL) );
				$this->smarty->assign( 'BIRTH' , $this->GetDateFormat($datarow00[0]['BIRTH']) );
				$this->smarty->assign( 'MOBILE1' , $this->set_mobile_number($datarow00[0]['MOBILE1']) );
				$this->smarty->assign( 'MOBILE2' , $this->set_mobile_number($datarow00[0]['MOBILE2']) );

				$TEMP_ADDRESS = explode( ' ', $datarow00[0]['ADDRESS'] );
				$TEMP_ADDRESS_0 = $TEMP_ADDRESS[0];
				unset($TEMP_ADDRESS[0]);
				if(is_numeric($TEMP_ADDRESS_0)){
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.'<br>'.implode(" ", $TEMP_ADDRESS) );
				}else{
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.' '.implode(" ", $TEMP_ADDRESS) );
				}

				$this->smarty->assign( 'SERVICE' , $datarow00[0]['SERVICE'] );

				$azsql ="BEGIN Usp_plan_person_0101(:entries,'$MEMBERID'); END;";	//학력정보
				$this->smarty->assign( 'SCHOOL_LIST' , $this->oracle->LoadProcedure_empty($azsql,"list_data01","") );

				$azsql ="BEGIN Usp_plan_person_0102(:entries,'$MEMBERID'); END;";	//자격정보
				$this->smarty->assign( 'LICENSE_LIST' , $this->oracle->LoadProcedure_empty($azsql,"list_data01","") );

				$azsql ="BEGIN Usp_plan_person_0103(:entries,'$MEMBERID'); END;";	//경력정보
				$this->SET_LIST( $LIST_MODE, 'WORK_LIST', $this->oracle->LoadProcedure_empty($azsql,"list_data01",""), $WORK_LIST_LIMIT );

				$azsql ="BEGIN Usp_plan_person_0104(:entries,'$MEMBERID'); END;";	//교육정보
				$this->SET_LIST( $LIST_MODE, 'EDUCATION_LIST', $this->oracle->LoadProcedure_empty($azsql,"list_data01",""), $EDUCATION_LIST_LIMIT );

				$azsql ="BEGIN Usp_plan_person_0105(:entries,'$MEMBERID'); END;";	//발령정보
				$this->SET_LIST( $LIST_MODE, 'ISSUANCE_LIST', $this->oracle->LoadProcedure_empty($azsql,"list_data01",""), $ISSUANCE_LIST_LIMIT );

				$azsql ="BEGIN Usp_plan_person_0106(:entries,'$MEMBERID'); END;";	//상벌정보
				$this->SET_LIST( $LIST_MODE, 'REWARD_LIST', $this->oracle->LoadProcedure_empty($azsql,"list_data01",""), $REWARD_LIST_LIMIT );

				$azsql ="BEGIN Usp_plan_person_0107(:entries,'$MEMBERID'); END;";	//특이사항 및 기타
				$this->SET_LIST( $LIST_MODE, 'ETC_LIST', $this->oracle->LoadProcedure_empty($azsql,"list_data01",""), $ETC_LIST_LIMIT );
			}elseif($COMPANY == 'HALLA'){	//한라
				global $db_halla;

				if( $MEMBERID == 'no' ){
					//이름으로 사번 찾기
					//echo base64_decode($EMP_NAME);
					$azsql = " HRM.dbo.Usp_plan_person_member '".$this->HangleEncodeUTF8_EUCKR(urldecode($EMP_NAME))."', '".substr( $JuminNo, 0, 6 )."' ";	//기본정보
					//echo $azsql;
					$datarow00 = $this->mssql->LoadData2($azsql,"list_data01","array");
					$MEMBERID = $datarow00[0]['EMP_NO'];
					$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				}

				$azsql ="HRM.dbo.USP_PLAN_PERSON_0100 '$MEMBERID' ";	//기본정보
				//echo $azsql."<br>";
				$datarow00 = $this->mssql->LoadData2($azsql,"list_data01","array");

				$this->smarty->assign( 'PHOTO' , 'http://intranet.hallasanup.com/erpphoto/'.$MEMBERID.'.jpg' );
				//$this->smarty->assign( 'PHOTO' , 'http://intranet.hallasanup.com/erpphoto/noimage.gif' );

				$KORNAME_cnt = mb_strlen($datarow00[0]['KORNAME'], 'utf-8');
				$KORNAME = '';
				for($i=0; $i < $KORNAME_cnt; $i++){
					if($i != 0){
						$KORNAME .= '<span class="word_space"></span>';
					}
					$KORNAME .= mb_substr( $datarow00[0]['KORNAME'], $i, 1, 'utf-8' );
				}
				$this->smarty->assign( 'KORNAME' , $KORNAME );

				$ENGNAME_arr = explode( ' ', $datarow00[0]['ENGNAME'] );
				$ENGNAME = '';
				for($i=0; $i < count($ENGNAME_arr); $i++){
					if($i != 0){
						$ENGNAME .= '<span class="word_space"></span>';
					}
					$ENGNAME .= $ENGNAME_arr[$i];
				}
				$this->smarty->assign( 'ENGNAME' , $ENGNAME );

				$this->smarty->assign( 'COMPANY_NAME' , '한라산업개발 (주)' );
				$this->smarty->assign( 'DEPT' , $datarow00[0]['DEPT'] );
				$this->smarty->assign( 'RANK' , $datarow00[0]['RANK'] );
				$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				$this->smarty->assign( 'EXTNO' , $datarow00[0]['EXTNO'] );
				$TEMP_EMAIL = explode( '@', $datarow00[0]['EMAIL'] );
				$TEMP_EMAIL_0 = $TEMP_EMAIL[0];
				unset($TEMP_EMAIL[0]);
				$this->smarty->assign( 'EMAIL' , $TEMP_EMAIL_0.'<br>@'.implode(" ", $TEMP_EMAIL) );
				$this->smarty->assign( 'BIRTH' , $this->GetDateFormat($datarow00[0]['BIRTH']) );
				$this->smarty->assign( 'MOBILE1' , $this->set_mobile_number($datarow00[0]['MOBILE1']) );
				$this->smarty->assign( 'MOBILE2' , $this->set_mobile_number($datarow00[0]['MOBILE2']) );

				$TEMP_ADDRESS = explode( ' ', $datarow00[0]['ADDRESS'] );
				$TEMP_ADDRESS_0 = $TEMP_ADDRESS[0];
				unset($TEMP_ADDRESS[0]);
				if(is_numeric($TEMP_ADDRESS_0)){
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.'<br>'.implode(" ", $TEMP_ADDRESS) );
				}else{
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.' '.implode(" ", $TEMP_ADDRESS) );
				}
				//$this->smarty->assign( 'ADDRESS' , $datarow00[0]['ADDRESS'] );
				$this->smarty->assign( 'SERVICE' , $datarow00[0]['SERVICE'] );

				$azsql ="HRM.dbo.USP_PLAN_PERSON_0101 '$MEMBERID' ";	//학력정보
				$this->mssql->LoadData($azsql,"SCHOOL_LIST","");

				$azsql ="HRM.dbo.USP_PLAN_PERSON_0102 '$MEMBERID' ";	//자격정보
				$this->mssql->LoadData($azsql,"LICENSE_LIST","");
				//$this->smarty->assign( 'LICENSE_LIST' , $this->mssql->LoadData($azsql,"") );

				$azsql ="HRM.dbo.USP_PLAN_PERSON_0103 '$MEMBERID' ";	//경력정보
				$this->SET_LIST( $LIST_MODE, 'WORK_LIST', $this->mssql->LoadData_common($azsql,"","array"), $WORK_LIST_LIMIT );

				$azsql ="HRM.dbo.USP_PLAN_PERSON_0104 '$MEMBERID' ";	//교육정보
				$this->SET_LIST( $LIST_MODE, 'EDUCATION_LIST', $this->mssql->LoadData_common($azsql,"","array"), $EDUCATION_LIST_LIMIT );

				$azsql ="HRM.dbo.USP_PLAN_PERSON_0105 '$MEMBERID' ";	//발령정보
				$this->SET_LIST( $LIST_MODE, 'ISSUANCE_LIST', $this->mssql->LoadData_common($azsql,"","array"), $ISSUANCE_LIST_LIMIT );

				$azsql ="HRM.dbo.USP_PLAN_PERSON_0106 '$MEMBERID' ";	//상벌정보
				$this->SET_LIST( $LIST_MODE, 'REWARD_LIST', $this->mssql->LoadData_common($azsql,"","array"), $REWARD_LIST_LIMIT );

				$azsql ="HRM.dbo.USP_PLAN_PERSON_0107 '$MEMBERID' ";	//특이사항 및 기타
				$this->SET_LIST( $LIST_MODE, 'ETC_LIST', $this->mssql->LoadData_common($azsql,"","array"), $ETC_LIST_LIMIT );
			}elseif($COMPANY == 'JANG'){	//장헌
				global $db_jang;

				if( $MEMBERID == 'no' ){
					//이름으로 사번 찾기
					$sql = "
						select MemberNo from member_tbl where korName = '".urldecode($EMP_NAME)."' and JuminNo like '".substr( $JuminNo, 0, 6 )."%' and EntryDate = ( select max(EntryDate) from member_tbl where korName = '".urldecode($EMP_NAME)."' and JuminNo like '".substr( $JuminNo, 0, 6 )."%' )
					";	//기본정보
					//echo $sql;

					$result=mysql_query($sql,$db_jang);
					$row=mysql_fetch_array($result);

					$MEMBERID = $row['MemberNo'];
					$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				}

				$sql = " select
				A.*
				,(select Name from systemconfig_tbl	where Syskey='GroupCode' and Code= A.GroupCode) AS GroupName
				,(select Name from systemconfig_tbl	where Syskey='PositionCode'	and Code=A.RankCode) AS RankName
				from member_tbl A
				where MemberNo = '$MEMBERID' ";
				//echo $sql;
				$result=mysql_query($sql,$db_jang);
				$row=mysql_fetch_array($result);

				$email_arr=explode('@',$row['eMail']);
				$row['birthday']=str_replace("-",".",$row['birthday']);
				$row['Mobile']=FN_NumberToCase("전화번호", $row['Mobile'],'');
				$row['MOBILE2']=FN_NumberToCase("전화번호", $row['Phone'],'');

				if($row['WorkPosition']!=9)
				{
					$row['WorkPosition']="재직";
				}
				else
				{
					$row['WorkPosition']="퇴직";
				}

				$this->smarty->assign( 'PHOTO' , 'http://211.206.127.71/erpphoto/'.$MEMBERID.'.jpg' );
				/*$this->smarty->assign( 'PHOTO' , 'http://211.206.127.71/erpphoto/noimage.gif' );*/

				$KORNAME_cnt = mb_strlen($row['korName'], 'utf-8');
				$KORNAME = '';
				for($i=0; $i < $KORNAME_cnt; $i++){
					if($i != 0){
						$KORNAME .= '<span class="word_space"></span>';
					}
					$KORNAME .= mb_substr( $row['korName'], $i, 1, 'utf-8' );
				}
				$this->smarty->assign( 'KORNAME' , $KORNAME );

				$ENGNAME_arr = explode( ' ', $row['engName'] );
				$ENGNAME = '';
				for($i=0; $i < count($ENGNAME_arr); $i++){
					if($i != 0){
						$ENGNAME .= '<span class="word_space"></span>';
					}
					$ENGNAME .= $ENGNAME_arr[$i];
				}
				$this->smarty->assign( 'ENGNAME' , $ENGNAME );

				//$this->smarty->assign( 'KORNAME' , $row['korName'] );
				//$this->smarty->assign( 'ENGNAME' , $row['engName'] );
				$this->smarty->assign( 'COMPANY_NAME' , '(주) 장헌산업' );
				$this->smarty->assign( 'DEPT' , $row['GroupName'] );
				$this->smarty->assign( 'RANK' , $row['RankName'] );
				$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				$this->smarty->assign( 'EXTNO' , $row['ExtNo'] );
				$this->smarty->assign( 'EMAIL' , $email_arr[0]."<br>@".$email_arr[1]);
				$this->smarty->assign( 'BIRTH' , $this->GetDateFormat($row['birthday']) );
				$this->smarty->assign( 'MOBILE1' , $this->set_mobile_number($row['Mobile']) );
				$this->smarty->assign( 'MOBILE2' , $this->set_mobile_number($row['MOBILE2']) );

				$TEMP_ADDRESS = explode( ' ', $row['Address'] );
				$TEMP_ADDRESS_0 = $TEMP_ADDRESS[0];
				unset($TEMP_ADDRESS[0]);
				if(is_numeric($TEMP_ADDRESS_0)){
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.'<br>'.implode(" ", $TEMP_ADDRESS) );
				}else{
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.' '.implode(" ", $TEMP_ADDRESS) );
				}
				//$this->smarty->assign( 'ADDRESS' , $row['Address'] );
				$this->smarty->assign( 'SERVICE' , $row['WorkPosition'] );

				//학력정보 조회 시작
				//정렬은 입학일 빠른순서
				$sql2="select SchoolStart, SchoolEnd, SchoolName, Specialization, LastEducation from member_school_tbl where MemberNo='$MEMBERID' order by SchoolStart desc";

				$re2 = mysql_query($sql2,$db_jang);

				$SCHOOL_LIST=array();

				while($row2=mysql_fetch_array($re2))
				{
					$row2["DATA_01"]=$row2["SchoolName"].' '.$row2["Specialization"];
					$row2["DATA_02"]=str_replace("-",".",substr($row2["SchoolStart"],0,7));
					$row2["DATA_03"]=str_replace("-",".",substr($row2["SchoolEnd"],0,7));

					if($row2["LastEducation"]=="14"){
					    $LastEducations="(고졸)";
					}elseif($row2["LastEducation"]=="13"){
					    $LastEducations="(초대졸)";
					}elseif($row2["LastEducation"]=="12"){
					    $LastEducations="(대졸)";
					}elseif($row2["LastEducation"]=="11"){
					    $LastEducations="(석사)";
					}elseif($row2["LastEducation"]=="10"){
					    $LastEducations="(박사)";
					}else{
					    $LastEducations="";
					}
				    $row2["DATA_04"]=$LastEducations;
					array_push($SCHOOL_LIST,$row2);
				}

				$this->smarty->assign('SCHOOL_LIST',$SCHOOL_LIST);

				//자격정보 조회 시작
				//정렬은 no 빠른순서
				$sql3="select CertificationName from member_certification_tbl where MemberNo='$MEMBERID' order by ObtainDate desc";

				$re3=mysql_query($sql3,$db_jang);

				$LICENSE_LIST=array();

				while($row3=mysql_fetch_array($re3))
				{
					$row3["DATA_01"]=$row3["CertificationName"];

					array_push($LICENSE_LIST,$row3);
				}

				$this->smarty->assign('LICENSE_LIST',$LICENSE_LIST);

				//경력정보 조회 시작
				//정렬은 회사 취직일자 최신순서
				$sql4="select CareerStart, CareerEnd, CompanyName, MainJob, Position from member_career_tbl where MemberNo='$MEMBERID' order by CareerStart DESC";

				$re4=mysql_query($sql4,$db_jang);

				$WORK_LIST=array();

				while($row4=mysql_fetch_array($re4))
				{
					$row4['DATA_01']=str_replace("-",".",substr($row4['CareerStart'],0,7)).".";
					$row4['DATA_02']=str_replace("-",".",substr($row4['CareerEnd'],0,7)).".";
					$row4['DATA_03']=$row4['CompanyName'];
					$row4['DATA_04']=$row4['MainJob'];
					$row4['DATA_05']=$row4['Position'];

					array_push($WORK_LIST,$row4);
				}
				$this->SET_LIST( $LIST_MODE, 'WORK_LIST', $WORK_LIST, $WORK_LIST_LIMIT );

				//교육정보 조회 시작
				//정렬은 교육일자 최근순서
				$sql5="select EducationStart, EducationEnd, OrganizationName, EducationName from member_supplyeducation_tbl where MemberNo='$MEMBERID' order by EducationStart DESC";

				$re5=mysql_query($sql5,$db_jang);

				$EDUCATION_LIST=array();

				while($row5=mysql_fetch_array($re5))
				{
					$sdate= strtotime($row5['EducationStart']);
					$edate= strtotime($row5['EducationEnd']);
					$days= ($edate-$sdate)/86400;

					$row5['DATA_01']=str_replace("-",".",substr($row5['EducationStart'],0,7)).".";
					$row5['DATA_02']=$days*"8";
					$row5['DATA_03']=$row5['EducationName'];
					$row5['DATA_04']=$row5['OrganizationName'];

					array_push($EDUCATION_LIST,$row5);
				}

				$this->SET_LIST( $LIST_MODE, 'EDUCATION_LIST', $EDUCATION_LIST, $EDUCATION_LIST_LIMIT );

				//발령정보 조회 시작
				//정렬은 발령일자 최근순서
				$sql6="SELECT
				AnnounceDate,
				kind,
				AnnounceItem,
				Department,
				Position
				FROM
				member_dutyhistory_tbl
				WHERE
				MemberNo = '$MEMBERID'
				ORDER BY
				AnnounceDate DESC
				, ( case kind when '채용' then 9 ELSE 1 END )";

				$re6=mysql_query($sql6,$db_jang);

				$ISSUANCE_LIST=array();

				while($row6=mysql_fetch_array($re6))
				{
					$row6['DATA_01']=str_replace("-",".",$row6['AnnounceDate']).".";
					if($row6['AnnounceItem'] == ''){
						$row6['DATA_02']=$row6['kind'];
					}else{
						$row6['DATA_02']=$row6['AnnounceItem'];
					}
					$row6['DATA_03']=$row6['Department'];
					$row6['DATA_04']=$row6['Position'];

					array_push($ISSUANCE_LIST,$row6);

				}

				$this->SET_LIST( $LIST_MODE, 'ISSUANCE_LIST', $ISSUANCE_LIST, $ISSUANCE_LIST_LIMIT );

				//상벌정보 조회 시작
				//정렬은 상벌일자 최근순서
				$sql7="select AwardDate, AwardName, Organization from member_award_tbl where MemberNo='$MEMBERID' order by AwardDate DESC";

				$re7=mysql_query($sql7,$db_jang);

				$REWARD_LIST=array();

				while($row7=mysql_fetch_array($re7))
				{
					$row7['DATA_01']=str_replace("-",".",$row7['AwardDate']).".";
					$row7['DATA_02']=$row7['AwardName'];
					$row7['DATA_03']=$row7['Organization'];

					array_push($REWARD_LIST,$row7);
				}

				$this->SET_LIST( $LIST_MODE, 'REWARD_LIST', $REWARD_LIST, $REWARD_LIST_LIMIT );

				//특이사항 및 기타정보 조회 시작
				//특이사항 입력순 최근순서
				$sql8="select EtcDate, EtcContents, EtcRemark from member_etc_tbl where MemberNo='$MEMBERID' order by EtcDate DESC";

				$re8=mysql_query($sql8,$db_jang);

				$ETC_LIST=array();

				while($row8=mysql_fetch_array($re8))
				{

					$row8['DATA_01']=str_replace("-",".",$row8['EtcDate']).".";
					$row8['DATA_02']=$row8['EtcContents'];
					$row8['DATA_03']=$row8['EtcRemark'];

					array_push($ETC_LIST,$row8);
				}
				$this->SET_LIST( $LIST_MODE, 'ETC_LIST', $ETC_LIST, $ETC_LIST_LIMIT );
			}elseif($COMPANY == 'PTC'){	//PTC
				global $db_ptc;

				if( $MEMBERID == 'no' ){
					//이름으로 사번 찾기
					$sql = "
						select MemberNo from member_tbl where korName = '".urldecode($EMP_NAME)."' and JuminNo like '".substr( $JuminNo, 0, 6 )."%' and EntryDate = ( select max(EntryDate) from member_tbl where korName = '".urldecode($EMP_NAME)."' and JuminNo like '".substr( $JuminNo, 0, 6 )."%' )
					";	//기본정보
					//echo $sql;

					$result=mysql_query($sql,$db_ptc);
					$row=mysql_fetch_array($result);

					$MEMBERID = $row['MemberNo'];
					$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				}

				$sql = " select
				A.*
				,(select Name from systemconfig_tbl	where Syskey='GroupCode' and Code= A.GroupCode) AS GroupName
				,(select Name from systemconfig_tbl	where Syskey='PositionCode'	and Code=A.RankCode) AS RankName
				from member_tbl A
				where MemberNo = '$MEMBERID' ";
				//echo $sql;
				$result=mysql_query($sql,$db_ptc);
				$row=mysql_fetch_array($result);

				$email_arr=explode('@',$row['eMail']);
				$row['birthday']=str_replace("-",".",$row['birthday']);
				$row['Mobile']=FN_NumberToCase("전화번호", $row['Mobile'],'');
				$row['MOBILE2']=FN_NumberToCase("전화번호", $row['Phone'],'');

				if($row['WorkPosition']!=9)
				{
					$row['WorkPosition']="재직";
				}
				else
				{
					$row['WorkPosition']="퇴직";
				}


				$this->smarty->assign( 'PHOTO' , 'http://211.206.127.72/erpphoto/'.$MEMBERID.'.jpg' );
				/*$this->smarty->assign( 'PHOTO' , 'http://erp.samaneng.com/erpphoto/noimage.gif' );*/

				$KORNAME_cnt = mb_strlen($row['korName'], 'utf-8');
				$KORNAME = '';
				for($i=0; $i < $KORNAME_cnt; $i++){
					if($i != 0){
						$KORNAME .= '<span class="word_space"></span>';
					}
					$KORNAME .= mb_substr( $row['korName'], $i, 1, 'utf-8' );
				}
				$this->smarty->assign( 'KORNAME' , $KORNAME );
				//$this->smarty->assign( 'KORNAME' , $row['korName'] );

				$ENGNAME_arr = explode( ' ', $row['engName'] );
				$ENGNAME = '';
				for($i=0; $i < count($ENGNAME_arr); $i++){
					if($i != 0){
						$ENGNAME .= '<span class="word_space"></span>';
					}
					$ENGNAME .= $ENGNAME_arr[$i];
				}
				$this->smarty->assign( 'ENGNAME' , $ENGNAME );
				//$this->smarty->assign( 'ENGNAME' , $row['engName'] );
				$this->smarty->assign( 'COMPANY_NAME' , '주식회사 피티씨' );
				$this->smarty->assign( 'DEPT' , $row['GroupName'] );
				$this->smarty->assign( 'RANK' , $row['RankName'] );
				$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				$this->smarty->assign( 'EXTNO' , $row['ExtNo'] );
				$this->smarty->assign( 'EMAIL' , $email_arr[0]."<br>@".$email_arr[1]);
				$this->smarty->assign( 'BIRTH' , $this->GetDateFormat($row['birthday']) );
				$this->smarty->assign( 'MOBILE1' , $this->set_mobile_number($row['Mobile']) );
				$this->smarty->assign( 'MOBILE2' , $this->set_mobile_number($row['MOBILE2']) );

				$TEMP_ADDRESS = explode( ' ', $row['Address'] );
				$TEMP_ADDRESS_0 = $TEMP_ADDRESS[0];
				unset($TEMP_ADDRESS[0]);
				if(is_numeric($TEMP_ADDRESS_0)){
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.'<br>'.implode(" ", $TEMP_ADDRESS) );
				}else{
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.' '.implode(" ", $TEMP_ADDRESS) );
				}
				//$this->smarty->assign( 'ADDRESS' , $row['Address'] );
				$this->smarty->assign( 'SERVICE' , $row['WorkPosition'] );

				//학력정보 조회 시작
				//정렬은 입학일 빠른순서
				$sql2="select SchoolStart, SchoolEnd, SchoolName, Specialization, LastEducation from member_school_tbl where MemberNo='$MEMBERID' order by SchoolStart desc";
				$re2 = mysql_query($sql2,$db_ptc);

				$SCHOOL_LIST=array();

				while($row2=mysql_fetch_array($re2))
				{
					$row2["DATA_01"]=$row2["SchoolName"].' '.$row2["Specialization"];
					$row2["DATA_02"]=str_replace("-",".",substr($row2["SchoolStart"],0,7));
					$row2["DATA_03"]=str_replace("-",".",substr($row2["SchoolEnd"],0,7));
					if($row2["LastEducation"]=="14"){
					    $LastEducations="(고졸)";
					}elseif($row2["LastEducation"]=="13"){
					    $LastEducations="(초대졸)";
					}elseif($row2["LastEducation"]=="12"){
					    $LastEducations="(대졸)";
					}elseif($row2["LastEducation"]=="11"){
					    $LastEducations="(석사)";
					}elseif($row2["LastEducation"]=="10"){
					    $LastEducations="(박사)";
					}else{
					    $LastEducations="";
					}
					$row2["DATA_04"]=$LastEducations;
					array_push($SCHOOL_LIST,$row2);
				}

				$this->smarty->assign('SCHOOL_LIST',$SCHOOL_LIST);


				//자격정보 조회 시작
				//정렬은 no 빠른순서
				$sql3="select CertificationName from member_certification_tbl where MemberNo='$MEMBERID' order by ObtainDate desc";

				$re3=mysql_query($sql3,$db_ptc);

				$LICENSE_LIST=array();

				while($row3=mysql_fetch_array($re3))
				{
					$row3["DATA_01"]=$row3["CertificationName"];

					array_push($LICENSE_LIST,$row3);
				}

				$this->smarty->assign('LICENSE_LIST',$LICENSE_LIST);

				//경력정보 조회 시작
				//정렬은 회사 취직일자 최근순서
				$sql4="select CareerStart, CareerEnd, CompanyName, MainJob, Position from member_career_tbl where MemberNo='$MEMBERID' order by CareerStart DESC";

				$re4=mysql_query($sql4,$db_ptc);

				$WORK_LIST=array();

				while($row4=mysql_fetch_array($re4))
				{
					$row4['DATA_01']=str_replace("-",".",substr($row4['CareerStart'],0,7)).".";
					$row4['DATA_02']=str_replace("-",".",substr($row4['CareerEnd'],0,7)).".";
					$row4['DATA_03']=$row4['CompanyName'];
					$row4['DATA_04']=$row4['MainJob'];
					$row4['DATA_05']=$row4['Position'];

					array_push($WORK_LIST,$row4);
				}
				$this->SET_LIST( $LIST_MODE, 'WORK_LIST', $WORK_LIST, $WORK_LIST_LIMIT );

				//교육정보 조회 시작
				//정렬은 교육일자 최근순서
				$sql5="select EducationStart, EducationEnd, OrganizationName, EducationName from member_supplyeducation_tbl where MemberNo='$MEMBERID' order by EducationStart DESC";

				$re5=mysql_query($sql5,$db_ptc);

				$EDUCATION_LIST=array();

				while($row5=mysql_fetch_array($re5))
				{
					$sdate= strtotime($row5['EducationStart']);
					$edate= strtotime($row5['EducationEnd']);
					$days= ($edate-$sdate)/86400;

					$row5['DATA_01']=str_replace("-",".",substr($row5['EducationStart'],0,7)).".";
					$row5['DATA_02']=$days*"8";
					$row5['DATA_03']=$row5['EducationName'];
					$row5['DATA_04']=$row5['OrganizationName'];

					array_push($EDUCATION_LIST,$row5);
				}

				$this->SET_LIST( $LIST_MODE, 'EDUCATION_LIST', $EDUCATION_LIST, $EDUCATION_LIST_LIMIT );

				//발령정보 조회 시작
				//정렬은 발령일자 최근순서
				$sql6="SELECT
				AnnounceDate,
				kind,
				AnnounceItem,
				Department,
				Position
				FROM
				member_dutyhistory_tbl
				WHERE
				MemberNo = '$MEMBERID'
				ORDER BY
				AnnounceDate DESC
				, ( case kind when '채용' then 9 ELSE 1 END )";

				$re6=mysql_query($sql6,$db_ptc);

				$ISSUANCE_LIST=array();

				$ISSUANCE_LIST_CNT=mysql_num_rows($re6);
				$this->smarty->assign('ISSUANCE_LIST_CNT',$ISSUANCE_LIST_CNT);

				$rownum=1;

				while($row6=mysql_fetch_array($re6))
				{
						$row6['DATA_01']=str_replace("-",".",$row6['AnnounceDate']).".";
						if($row6['AnnounceItem'] == ''){
							$row6['DATA_02']=$row6['kind'];
						}else{
							$row6['DATA_02']=$row6['AnnounceItem'];
						}
						$row6['DATA_03']=$row6['Department'];
						$row6['DATA_04']=$row6['Position'];

						array_push($ISSUANCE_LIST,$row6);

					$rownum++;

				}

				$this->SET_LIST( $LIST_MODE, 'ISSUANCE_LIST', $ISSUANCE_LIST, $ISSUANCE_LIST_LIMIT );

				//상벌정보 조회 시작
				//정렬은 상벌일자 최근순서
				$sql7="select AwardDate, AwardName, Organization from member_award_tbl where MemberNo='$MEMBERID' order by AwardDate DESC";

				$re7=mysql_query($sql7,$db_ptc);

				$REWARD_LIST=array();

				while($row7=mysql_fetch_array($re7))
				{
					$row7['DATA_01']=str_replace("-",".",$row7['AwardDate']).".";
					$row7['DATA_02']=$row7['AwardName'];
					$row7['DATA_03']=$row7['Organization'];

					array_push($REWARD_LIST,$row7);
				}

				$this->SET_LIST( $LIST_MODE, 'REWARD_LIST', $REWARD_LIST, $REWARD_LIST_LIMIT );

				//특이사항 및 기타정보 조회 시작
				//정렬은 특이사항 작성일자 최근순서
				$sql8="select EtcDate, EtcContents, EtcRemark from member_etc_tbl where MemberNo='$MEMBERID' Order by EtcDate DESC";

				$re8=mysql_query($sql8,$db_ptc);

				$ETC_LIST=array();

				while($row8=mysql_fetch_array($re8))
				{

					$row8['DATA_01']=$row8['EtcDate'];
					$row8['DATA_02']=$row8['EtcContents'];
					$row8['DATA_03']=$row8['EtcRemark'];

					array_push($ETC_LIST,$row8);
				}
				$this->SET_LIST( $LIST_MODE, 'ETC_LIST', $ETC_LIST, $ETC_LIST_LIMIT );
			}elseif($COMPANY == 'HYUNTA'){	//HYUNTA
				global $db_HYUNTA;

				if( $MEMBERID == 'no' ){
					//이름으로 사번 찾기
					$sql = "
						select MemberNo from member_tbl where korName = '".urldecode($EMP_NAME)."' and JuminNo like '".substr( $JuminNo, 0, 6 )."%' and EntryDate = ( select max(EntryDate) from member_tbl where korName = '".urldecode($EMP_NAME)."' and JuminNo like '".substr( $JuminNo, 0, 6 )."%' )
					";	//기본정보
					//echo $sql;

					$result=mysql_query($sql,$db_HYUNTA);
					$row=mysql_fetch_array($result);

					$MEMBERID = $row['MemberNo'];
					$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				}

				$sql = " select
				A.*
				,(select Name from systemconfig_tbl	where Syskey='GroupCode' and Code= A.GroupCode) AS GroupName
				,(select Name from systemconfig_tbl	where Syskey='PositionCode'	and Code=A.RankCode) AS RankName
				from member_tbl A
				where MemberNo = '$MEMBERID' ";
				//echo $sql;
				$result=mysql_query($sql,$db_HYUNTA);
				$row=mysql_fetch_array($result);

				$email_arr=explode('@',$row['eMail']);
				$row['birthday']=str_replace("-",".",$row['birthday']);
				$row['Mobile']=FN_NumberToCase("전화번호", $row['Mobile'],'');
				$row['MOBILE2']=FN_NumberToCase("전화번호", $row['Phone'],'');

				if($row['WorkPosition']!=9)
				{
					$row['WorkPosition']="재직";
				}
				else
				{
					$row['WorkPosition']="퇴직";
				}


				$this->smarty->assign( 'PHOTO' , 'http://211.206.127.72/erpphoto/'.$MEMBERID.'.jpg' );
				/*$this->smarty->assign( 'PHOTO' , 'http://erp.samaneng.com/erpphoto/noimage.gif' );*/

				$KORNAME_cnt = mb_strlen($row['korName'], 'utf-8');
				$KORNAME = '';
				for($i=0; $i < $KORNAME_cnt; $i++){
					if($i != 0){
						$KORNAME .= '<span class="word_space"></span>';
					}
					$KORNAME .= mb_substr( $row['korName'], $i, 1, 'utf-8' );
				}
				$this->smarty->assign( 'KORNAME' , $KORNAME );
				//$this->smarty->assign( 'KORNAME' , $row['korName'] );

				$ENGNAME_arr = explode( ' ', $row['engName'] );
				$ENGNAME = '';
				for($i=0; $i < count($ENGNAME_arr); $i++){
					if($i != 0){
						$ENGNAME .= '<span class="word_space"></span>';
					}
					$ENGNAME .= $ENGNAME_arr[$i];
				}
				$this->smarty->assign( 'ENGNAME' , $ENGNAME );
				//$this->smarty->assign( 'ENGNAME' , $row['engName'] );
				$this->smarty->assign( 'COMPANY_NAME' , '주식회사 피티씨' );
				$this->smarty->assign( 'DEPT' , $row['GroupName'] );
				$this->smarty->assign( 'RANK' , $row['RankName'] );
				$this->smarty->assign( 'MEMBERID' , $MEMBERID );
				$this->smarty->assign( 'EXTNO' , $row['ExtNo'] );
				$this->smarty->assign( 'EMAIL' , $email_arr[0]."<br>@".$email_arr[1]);
				$this->smarty->assign( 'BIRTH' , $this->GetDateFormat($row['birthday']) );
				$this->smarty->assign( 'MOBILE1' , $this->set_mobile_number($row['Mobile']) );
				$this->smarty->assign( 'MOBILE2' , $this->set_mobile_number($row['MOBILE2']) );

				$TEMP_ADDRESS = explode( ' ', $row['Address'] );
				$TEMP_ADDRESS_0 = $TEMP_ADDRESS[0];
				unset($TEMP_ADDRESS[0]);
				if(is_numeric($TEMP_ADDRESS_0)){
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.'<br>'.implode(" ", $TEMP_ADDRESS) );
				}else{
					$this->smarty->assign( 'ADDRESS' , $TEMP_ADDRESS_0.' '.implode(" ", $TEMP_ADDRESS) );
				}
				//$this->smarty->assign( 'ADDRESS' , $row['Address'] );
				$this->smarty->assign( 'SERVICE' , $row['WorkPosition'] );

				//학력정보 조회 시작
				//정렬은 입학일 빠른순서
				$sql2="select SchoolStart, SchoolEnd, SchoolName, Specialization, LastEducation from member_school_tbl where MemberNo='$MEMBERID' order by SchoolStart desc";
				$re2 = mysql_query($sql2,$db_HYUNTA);

				$SCHOOL_LIST=array();

				while($row2=mysql_fetch_array($re2))
				{
					$row2["DATA_01"]=$row2["SchoolName"].' '.$row2["Specialization"];
					$row2["DATA_02"]=str_replace("-",".",substr($row2["SchoolStart"],0,7));
					$row2["DATA_03"]=str_replace("-",".",substr($row2["SchoolEnd"],0,7));
					if($row2["LastEducation"]=="14"){
					    $LastEducations="(고졸)";
					}elseif($row2["LastEducation"]=="13"){
					    $LastEducations="(초대졸)";
					}elseif($row2["LastEducation"]=="12"){
					    $LastEducations="(대졸)";
					}elseif($row2["LastEducation"]=="11"){
					    $LastEducations="(석사)";
					}elseif($row2["LastEducation"]=="10"){
					    $LastEducations="(박사)";
					}else{
					    $LastEducations="";
					}
					$row2["DATA_04"]=$LastEducations;
					array_push($SCHOOL_LIST,$row2);
				}

				$this->smarty->assign('SCHOOL_LIST',$SCHOOL_LIST);


				//자격정보 조회 시작
				//정렬은 no 빠른순서
				$sql3="select CertificationName from member_certification_tbl where MemberNo='$MEMBERID' order by ObtainDate desc";

				$re3=mysql_query($sql3,$db_HYUNTA);

				$LICENSE_LIST=array();

				while($row3=mysql_fetch_array($re3))
				{
					$row3["DATA_01"]=$row3["CertificationName"];

					array_push($LICENSE_LIST,$row3);
				}

				$this->smarty->assign('LICENSE_LIST',$LICENSE_LIST);

				//경력정보 조회 시작
				//정렬은 회사 취직일자 최근순서
				$sql4="select CareerStart, CareerEnd, CompanyName, MainJob, Position from member_career_tbl where MemberNo='$MEMBERID' order by CareerStart DESC";

				$re4=mysql_query($sql4,$db_HYUNTA);

				$WORK_LIST=array();

				while($row4=mysql_fetch_array($re4))
				{
					$row4['DATA_01']=str_replace("-",".",substr($row4['CareerStart'],0,7)).".";
					$row4['DATA_02']=str_replace("-",".",substr($row4['CareerEnd'],0,7)).".";
					$row4['DATA_03']=$row4['CompanyName'];
					$row4['DATA_04']=$row4['MainJob'];
					$row4['DATA_05']=$row4['Position'];

					array_push($WORK_LIST,$row4);
				}
				$this->SET_LIST( $LIST_MODE, 'WORK_LIST', $WORK_LIST, $WORK_LIST_LIMIT );

				//교육정보 조회 시작
				//정렬은 교육일자 최근순서
				$sql5="select EducationStart, EducationEnd, OrganizationName, EducationName from member_supplyeducation_tbl where MemberNo='$MEMBERID' order by EducationStart DESC";

				$re5=mysql_query($sql5,$db_HYUNTA);

				$EDUCATION_LIST=array();

				while($row5=mysql_fetch_array($re5))
				{
					$sdate= strtotime($row5['EducationStart']);
					$edate= strtotime($row5['EducationEnd']);
					$days= ($edate-$sdate)/86400;

					$row5['DATA_01']=str_replace("-",".",substr($row5['EducationStart'],0,7)).".";
					$row5['DATA_02']=$days*"8";
					$row5['DATA_03']=$row5['EducationName'];
					$row5['DATA_04']=$row5['OrganizationName'];

					array_push($EDUCATION_LIST,$row5);
				}

				$this->SET_LIST( $LIST_MODE, 'EDUCATION_LIST', $EDUCATION_LIST, $EDUCATION_LIST_LIMIT );

				//발령정보 조회 시작
				//정렬은 발령일자 최근순서
				$sql6="SELECT
				AnnounceDate,
				kind,
				AnnounceItem,
				Department,
				Position
				FROM
				member_dutyhistory_tbl
				WHERE
				MemberNo = '$MEMBERID'
				ORDER BY
				AnnounceDate DESC
				, ( case kind when '채용' then 9 ELSE 1 END )";

				$re6=mysql_query($sql6,$db_HYUNTA);

				$ISSUANCE_LIST=array();

				$ISSUANCE_LIST_CNT=mysql_num_rows($re6);
				$this->smarty->assign('ISSUANCE_LIST_CNT',$ISSUANCE_LIST_CNT);

				$rownum=1;

				while($row6=mysql_fetch_array($re6))
				{
						$row6['DATA_01']=str_replace("-",".",$row6['AnnounceDate']).".";
						if($row6['AnnounceItem'] == ''){
							$row6['DATA_02']=$row6['kind'];
						}else{
							$row6['DATA_02']=$row6['AnnounceItem'];
						}
						$row6['DATA_03']=$row6['Department'];
						$row6['DATA_04']=$row6['Position'];

						array_push($ISSUANCE_LIST,$row6);

					$rownum++;

				}

				$this->SET_LIST( $LIST_MODE, 'ISSUANCE_LIST', $ISSUANCE_LIST, $ISSUANCE_LIST_LIMIT );

				//상벌정보 조회 시작
				//정렬은 상벌일자 최근순서
				$sql7="select AwardDate, AwardName, Organization from member_award_tbl where MemberNo='$MEMBERID' order by AwardDate DESC";

				$re7=mysql_query($sql7,$db_HYUNTA);

				$REWARD_LIST=array();

				while($row7=mysql_fetch_array($re7))
				{
					$row7['DATA_01']=str_replace("-",".",$row7['AwardDate']).".";
					$row7['DATA_02']=$row7['AwardName'];
					$row7['DATA_03']=$row7['Organization'];

					array_push($REWARD_LIST,$row7);
				}

				$this->SET_LIST( $LIST_MODE, 'REWARD_LIST', $REWARD_LIST, $REWARD_LIST_LIMIT );

				//특이사항 및 기타정보 조회 시작
				//정렬은 특이사항 작성일자 최근순서
				$sql8="select EtcDate, EtcContents, EtcRemark from member_etc_tbl where MemberNo='$MEMBERID' Order by EtcDate DESC";

				$re8=mysql_query($sql8,$db_HYUNTA);

				$ETC_LIST=array();

				while($row8=mysql_fetch_array($re8))
				{

					$row8['DATA_01']=$row8['EtcDate'];
					$row8['DATA_02']=$row8['EtcContents'];
					$row8['DATA_03']=$row8['EtcRemark'];

					array_push($ETC_LIST,$row8);
				}
				$this->SET_LIST( $LIST_MODE, 'ETC_LIST', $ETC_LIST, $ETC_LIST_LIMIT );
			}

			if( $test == '' ){
				$this->smarty->display("planning_mng/insaReport/PersonReport_01_HTML_AJAX_01_mvc.tpl");
			}else{
				$this->smarty->display("planning_mng/insaReport/PersonReport_01_HTML_AJAX_01_mvc_".$test.".tpl");
			}
		}

		function REPORT_02(){
			extract($_REQUEST);
			switch($MainAction){
				case "HTML_Ajax_01": 	$this->REPORT_02_HTML_Ajax_01();	break;
			}
		}

		function REPORT_02_HTML_Ajax_01($mode=true){
			extract($_REQUEST);
			switch($SubAction){
				case "insert_worker":
					header('Content-Type: text/html; charset=UTF-8');
					include "../../../person_mng/inc/vacationfunction.php";
					include "../util/OracleClass.php";
					$this->oracle = new OracleClass($this->smarty, 'BARO');
					$ORACLE_SAMAN = new OracleClass($this->smarty, 'SAMAN');
					include "../inc/dbcon_jg.inc";	//인트라넷 DB연결
					include "../inc/dbcon_pt.inc";	//인트라넷 DB연결

					global $db;
					$action = true;	//	true	false

					if($action_test == 'test'){
						$action = false;
					}

					//$set_date = str_replace ( '-', '', $set_date) ;
					if($set_date == null or $set_date == ''){
						$set_date = date('Ym');
					}

					$set_date_ori = $set_date;

					//한맥 worker 입력
						for($c=0; $c<2; $c++){
							if($c == 0){
								$set_date = $set_date_ori;
							}else{
								$set_date = date("Ym",strtotime("-".$c." month", strtotime($set_date_ori."01")));
							}

							$set_date_bar = substr($set_date,0,4)."-".substr($set_date,4,2);
							$set_year = substr($set_date,0,4);
							$set_month = (int)substr($set_date,4,2);
							$set_date_last = $set_date.date('t', strtotime($set_date_bar."-01"));
							$set_date_last_bar = $set_date_bar.'-'.date('t', strtotime($set_date_bar."-01"));
							$set_month_Pre = date("Y-m",strtotime("-1 month", strtotime($set_date."01")));

							/* 기간 월별로 나누기 */
							$azsql_s = "
								SELECT *, date_format(start_time,'%Y-%m') as s_ym, date_format(end_time,'%Y-%m') as e_ym FROM userstate_tbl
								WHERE
									state = 1
									AND ( date_format(start_time,'%Y%m') = '".$set_date."' OR date_format(end_time,'%Y%m') = '".$set_date."' )
									/*AND memberno IN (SELECT memberno FROM member_tbl WHERE groupcode in (3, 98, 24, 25) AND ( date_format(leavedate , '%Y%m') >= '".$set_date."' OR leavedate = '0000-00-00' ))*/
									AND date_format(start_time,'%Y%m') != date_format(end_time,'%Y%m')
							;";
							if(!$action){
								echo '<br><br>기간 월별로 나누기<br>';
								echo $azsql_s;
							}

							$sql_arr = array();
							$re = mysql_query($azsql_s,$db);
							while($row=mysql_fetch_array($re)){
								/* 나누는거 해야함. */
								//print_r($row);
								if($row["s_ym"] == $set_date_bar){
									$azsql = "
										update userstate_tbl set start_time = '".$row["e_ym"]."-01' where num = ".$row["num"]."
									;";
									array_push($sql_arr, $azsql);
									$azsql = "
										insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
										values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["start_time"]."', '".$row["s_ym"].'-'.date('t', strtotime($row["start_time"]))."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
									;";
									array_push($sql_arr, $azsql);
								}else{
									$azsql = "
										update userstate_tbl set end_time = '".$row["s_ym"]."-".date('t', strtotime($row["start_time"]))."' where num = '".$row["num"]."'
									;";
									array_push($sql_arr, $azsql);
									$azsql = "
										insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
										values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["e_ym"]."-01', '".$row["end_time"]."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
									;";
									array_push($sql_arr, $azsql);
								}
							}

							for($i=0; $i<count($sql_arr); $i++){
								if($action){
									mysql_query($sql_arr[$i],$db);
								}else{
									echo '<br>'.$sql_arr[$i];
								}
							}

							/*sortkey 셋팅*/
							/* ERP 발령정보에서 정보가져와서 설정.
								발령 마지막 전날이 퇴직이 아닐때
							*/

							$azsql = "
								select
									member_id as EMP_NO
									, LPAD(DEPT_CODE,2,'0') AS DEPT_CODE
									, ( select code from total_systemconfig_tbl B where syskey = 'RANK' and comp_code = '20' and sys_code = '0' and B.name = A.rank_name ORDER BY CODE LIMIT 1 ) AS RANK_CODE
								from
									total_member_tbl A
								WHERE
									sys_comp_code = '20'
									and working_comp = '20'
									and join_date <= '".$set_date_last_bar."'
									and (
										retire_date = '0000-00-00'
										or retire_date > '".$set_date_bar."-01'
									)
								;
							";
							if(!$action){
								echo '<br><br>한맥인원<br>';
								echo $azsql;
							}
							//echo $azsql;

							$member_info = array();
							$re = mysql_query($azsql,$db);
							while($row=mysql_fetch_array($re)){
								$member_info[$row["EMP_NO"]] = $row;
							}

							/*월 삭제*/
							$azsql = "
								delete from worker_date_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
							;";
							if($action){
								mysql_query($azsql,$db);
							}else{
								echo '<br><br>월 삭제<br>';
								echo $azsql;
							}

							/*월 추가*/
							$azsql = "
								INSERT INTO worker_date_tbl (date_y, date_m, date_d, week, holy, week_cnt )
								SELECT
									date_y
									, date_m
									, COUNT(date_m) AS date_d
									, SUM(week) AS week
									, (SELECT COUNT(date) FROM holyday_tbl B WHERE a.date_y = YEAR(B.date) AND A.date_m = MONTH(B.date) and DAYOFWEEK( B.date ) in (2,3,4,5,6) ) AS holy
									, sum(week_cnt) as week_cnt
								FROM (
									SELECT
										a.date_ymd
										, YEAR(a.date_ymd) AS date_y
										, MONTH(a.date_ymd) AS date_m
										, IF( DAYOFWEEK( a.date_ymd ) = 1 OR DAYOFWEEK( a.date_ymd ) = 7, 1, 0) AS week
										, IF( DAYOFWEEK( a.date_ymd ) = 5, 1, 0) AS week_cnt
									FROM (
										SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
											SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
											UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
										) AS a
										CROSS JOIN (
											SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
											UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
										) AS b
										CROSS JOIN (
											SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
											UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
										) AS c
									) AS a
									WHERE 1 = 1
									AND a.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
								) a
								GROUP BY
									date_y, date_m
							;";
							if($action){
								mysql_query($azsql,$db);
							}else{
								echo '<br><br>월 추가<br>';
								echo $azsql;
							}

							/*사번별 추가*/

							$azsql = "
								delete from worker_total_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
							;";
							if($action){
								mysql_query($azsql,$db);
							}else{
								echo '<br><br>사번별 삭제<br>';
								echo $azsql;
							}

							if(!$action){
								echo '<br><br>사번별 추가<br>';
							}
							foreach($member_info as $key => $value){
								$azsql = "
									INSERT INTO worker_total_tbl (memberno, date_y, date_m, dept_top_code, dept_code, rank_code)
									values
									( '$key', ".$set_year.", ".$set_month.", (SELECT Code FROM systemconfig_tbl WHERE SysKey = 'GroupCode_Top' AND CodeORName LIKE CONCAT( '%g', '".$value['DEPT_CODE']."'*1, 'g%' )) , '".$value['DEPT_CODE']."', '".$value['RANK_CODE']."' ) ;
								";
								//echo '<br>'.$azsql.'<br>';
								if($action){
									mysql_query($azsql,$db);
								}else{
									echo '<br>'.$azsql;
								}
							}

							/*연차 1, 18, 30, 31 - 주말,휴일 제외*/
							$azsql = "
								UPDATE worker_total_tbl BB SET vacation1 = IFNULL((
									SELECT SUM(vacation1)
									FROM (
										SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
										FROM (
											SELECT
												memberno
												, start_time
												, end_time
												, (
													CASE
														WHEN state = 1 THEN IF(note LIKE '%반차%', 4, 8)
														WHEN state = 30 THEN 4
														WHEN state = 31 THEN 4
														ELSE sub_code
													END
												) * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
											FROM (
												SELECT
													memberno
													, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
													, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
													, state
													, note
													, sub_code
												FROM
													userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (1, 18, 30, 31)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) AAA
											ORDER BY vacation desc
										) A
										, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT '1986-12-31' FROM dual ) B
										GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
									) AA
									WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
									GROUP BY memberno, date_y, date_m
								), 0)
								WHERE date_y = ".$set_year." AND date_m = ".$set_month."
							;";
							if($action){
								mysql_query($azsql,$db);
							}else{
								echo '<br><br>연차 1, 18, 30, 31 - 주말,휴일 제외<br>';
								echo $azsql;
							}

							/*기타 주말제외. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가), 10:대기, 17:기타*/
							$azsql = "
								UPDATE worker_total_tbl BB SET vacation2 = IFNULL((
									SELECT SUM(vacation1)
									FROM (
										SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
										FROM (
											SELECT
												memberno
												, start_time
												, end_time
												, 8 * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
											FROM (
												SELECT
													memberno
													, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
													, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
												FROM
													userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (5,6,7,8,10,17)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) AAA
											ORDER BY vacation desc
										) A
										, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT date FROM date_weekend_tbl where date_format(DATE,'%Y%m') like '".$set_date."'
										) B
										GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
									) AA
									WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
									GROUP BY memberno, date_y, date_m
								), 0)
								WHERE date_y = ".$set_year." AND date_m = ".$set_month."
							;";
							if($action){
								mysql_query($azsql,$db);
							}else{
								echo '<br><br>기타 5,6,7,8,10,17 주말제외<br>';
								echo $azsql;
							}

							/*비고 입력. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가),17:기타, 20:합사, 21:합사, 9:파견, 15:파견*/
							$azsql = "
								UPDATE
									worker_total_tbl A
									, (
										SELECT
											memberno
											, ".$set_year." AS date_y
											, ".$set_month." AS date_m
											, CONCAT(
												IF(SUM(state_1) > 0, '_경조', '')
												, IF(SUM(state_2) > 0, '_보건', '')
												, IF(SUM(state_3) > 0, '_출산', '')
												, IF(SUM(state_4) > 0, '_특별', '')
												, IF(SUM(state_5) > 0, '_훈련', '')
												, IF(SUM(state_6) > 0, '_교육', '')
												, IF(SUM(state_7) > 0, '_기타', '')
												, IF(SUM(state_8) > 0, '_합사', '')
												, IF(SUM(state_9) > 0, '_파견', '')
											) AS etc
										FROM
											(

												SELECT
													memberno
													, IF(state = 7, 1, 0) AS state_1
													, 0 AS state_2
													, 0 AS state_3
													, 0 AS state_4
													, IF(state = 5, 1, 0) AS state_5
													, IF(state = 6, 1, 0) AS state_6
													, 0 AS state_7
													, 0 AS state_8
													, 0 AS state_9
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (5,6,7)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )

												UNION ALL

												SELECT
													memberno
													, 0
													, state_2
													, state_3
													, state_4
													, 0
													, 0
													, state - state_2 - state_3 - state_4
													, 0
													, 0
												FROM (
													SELECT
														memberno
														, IF(note LIKE '%보건%', 1, 0) AS state_2
														, IF(note LIKE '%출산%', 1, 0) AS state_3
														, IF(note LIKE '%특별%', 1, 0) AS state_4
														, 1 AS state
													FROM userstate_tbl
													WHERE
														'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
														AND state IN (8,17)
														AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
												) A

												UNION ALL

												SELECT
													memberno
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 1
													, 0
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (20, 21)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
												UNION ALL

												SELECT
													memberno
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 1
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (9, 15)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) C
										GROUP BY memberno
									) B
								SET
									A.etc = B.etc
								WHERE
									A.date_y = B.date_y
									AND A.date_m = B.date_m
									AND A.memberno = B.memberno
								;
							";
							if($action){
								mysql_query($azsql,$db);
							}else{
								echo '<br><br>비고 입력<br>';
								echo $azsql;
							}

							/*지각 횟수*/
							$azsql = "
								UPDATE
									worker_total_tbl BB
								SET
									tardy = IFNULL((
										SELECT tardy FROM (
											SELECT
												A.memberno
												, YEAR(A.entrytime) AS date_y
												, MONTH(A.entrytime) AS date_m
												, SUM( CASE WHEN date_format(A.entrytime,'%H') >= IFNULL(B.tardy_h, 9) AND date_format(A.entrytime,'%i') > IFNULL(B.tardy_m, 0) THEN IF( ( SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(A.entrytime,'%Y-%m-%d') ) > 0 , 0, 1) ELSE 0 end) AS tardy
											FROM (
												SELECT A.memberno, A.entrytime FROM (
													SELECT
														memberno
														, entrytime
													FROM
														dallyproject_tbl
													WHERE
														EntryTime BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
														AND DAYOFWEEK(entrytime) NOT IN (1,7)
														AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
												) A
												left join
												( SELECT memberno, start_time, end_time FROM userstate_tbl WHERE state IN ( 1, 18, 17, 30, 5, 6, 2, 3 ) AND ( date_format( start_time , '%Y%m') = '".$set_date."' OR date_format( end_time , '%Y%m') = '".$set_date."' ) ) B
												ON
													A.memberno = B.memberno
													AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.start_time AND B.end_time
												WHERE B.memberno IS null
											) A
											left join
											( SELECT * FROM worker_tardy_tbl WHERE '".$set_date."' between date_format(s_date, '%Y%m') and date_format(e_date, '%Y%m') ) B
											ON
												A.memberno = B.memberno
												AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.s_date AND B.e_date
											GROUP BY A.memberno
										) AA
										WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
									), 0)
									, BB.login_cnt = (select count(MemberNo) from dallyproject_tbl where MemberNo = BB.MemberNo and date_format(EntryTime , '%Y-%m-%d') IN (
										SELECT date_format(DATE , '%Y-%m-%d') FROM (
											SELECT date FROM holyday_tbl WHERE DATE BETWEEN '".$set_date_bar."-01' AND '".$set_date_last_bar."'
											UNION ALL
											SELECT date_format(date_ymd , '%Y-%m-%d') FROM (
												SELECT C.date_ymd, YEAR(C.date_ymd) AS date_y, MONTH(C.date_ymd) AS date_m, IF( DAYOFWEEK( C.date_ymd ) = 1 OR DAYOFWEEK( C.date_ymd ) = 7, 1, 0) AS week
												FROM (
													SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
													SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
													UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
													) AS a
													CROSS JOIN (
													SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
													UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
													) AS b
													CROSS JOIN (
													SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
													UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
													) AS c
												) AS C
												WHERE
													C.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
											) D WHERE week = 1
										) E GROUP BY date
									))
								WHERE date_y = ".$set_year." AND date_m = ".$set_month."
							;";
							if($action){
								mysql_query($azsql,$db);
							}else{
								echo '<br><br>지각 횟수 - 출장 외출 그런거 안따짐.<br>';
								echo $azsql;
							}

							/*연장근로시간 - 실제,인정, 연장근로일수 - 실제,인정*/
							if(date("Ym",strtotime("-2 month", strtotime(date('Ym')."01"))) < $set_date){
								$azsql = "
									SELECT
										C.MemberNo
										, EntryTime
										, termTime
										, min_MINUTE
										, max_MINUTE
										, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_time
										, C.RankCode
									FROM
										(
											SELECT
												MemberNo
												, ( SELECT IF(RealRankCode = '' , RankCode, ifnull(RealRankCode, RankCode)) AS RankCode FROM member_tbl WHERE MemberNo = A.MemberNo ) AS RankCode
												, EntryTime
												, date_format(OverTime,'%Y-%m-%d') AS over_date
												, IF(
													DAYOFWEEK = 0 and date_format(OverTime,'%H:%i') = '00:00'
													, 0
													, TIMESTAMPDIFF(
														MINUTE
														, IF(
															DAYOFWEEK = 0
															, OverTime
															, IF(
																date_format(EntryTime,'%H:%i') < B.start_time
																, CONCAT(date_format(EntryTime,'%Y-%m-%d '), B.start_time, ':00')
																, EntryTime
															)
														)
														, LeaveTime
													)
												) AS termTime
												, (SUBSTRING_INDEX(min_time, ':', 1) * 60 + SUBSTRING_INDEX(min_time, ':', -1) * 1 ) AS min_MINUTE
												, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_MINUTE
											FROM
												(
													SELECT
														MemberNo
														, EntryTime
														, IF( DAYOFWEEK( EntryTime ) = 1 OR DAYOFWEEK( EntryTime ) = 7, 1, (SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(dallyproject_tbl.EntryTime,'%Y-%m-%d ') ) ) AS DAYOFWEEK
														, OverTime
														, LeaveTime
													FROM dallyproject_tbl
													WHERE EntryTime BETWEEN '".$set_month_Pre."-21 00:00:00' AND '".$set_date_bar."-20 23:59:59' AND MODIFY = 1
													ORDER BY MemberNo
												) A
												, (
													SELECT * FROM overtime_basic_new_tbl WHERE code in ( 100, 101 )
												) B
											WHERE
												A.DAYOFWEEK = B.code or (A.DAYOFWEEK+100) = B.code
										) C
										, (
											SELECT * FROM overtime_basic_new_tbl WHERE code BETWEEN 102 AND 105
											union all
											select 999, '', '', '999:00', '', '-C0-C1-C2-C3-C4-C5-C6-C7-C8-C0A-C4A-C7A-C8A-', '' from dual
										) D
									WHERE
										D.RankCode LIKE CONCAT('%-', C.RankCode, '-%')
									order by MemberNo, EntryTime
								;";
								if($action){
								}else{
									echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
									echo $azsql;
								}

								$over_arr = array();

								$re = mysql_query($azsql,$db);
								while($row=mysql_fetch_array($re)){
									if($over_arr[$row['MemberNo']] == null){
										$over_arr[$row['MemberNo']] = array();
										$over_arr[$row['MemberNo']]['over_day_real'] = 0;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_day_confirm'] = 0;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_time_real'] = 0;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_time_confirm'] = 0;	//실제 일한 날짜
										$over_check = false;
									}

									if( $row['termTime'] >= $row['min_MINUTE'] ){	//최소 시간보다 클때

										$over_arr[$row['MemberNo']]['over_day_real']++;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_day_confirm']++;	//인정 날짜
										$over_arr[$row['MemberNo']]['over_time_real'] += $row['termTime'];	//실제 야근시간

										if( $row['termTime'] > $row['max_MINUTE'] ){	//하루 최대시간 보다 많을때
											$row['termTime'] = $row['max_MINUTE'];
										}

										$over_arr[$row['MemberNo']]['over_time_confirm'] += $row['termTime'];	//인정 야근시간
										if($over_arr[$row['MemberNo']]['over_time_confirm'] > $row['max_time']){	//총 야근시간이 최대 인정시간보다 많을때
											if($over_check and strpos($row['max_MINUTE'], 'C') === false){
												$over_arr[$row['MemberNo']]['over_day_confirm']--;	//인정 날짜
											}
											$over_check = true;
											//$over_arr[$row['MemberNo']]['over_time_confirm'] = $row['max_time'];	//야근시간은 인정시간만큼
										}
									}
								}
								//print_r($over_arr);

								foreach ($over_arr as $key => $value) {
									$azsql = "
										update worker_total_tbl set
											over_time_real = ".$over_arr[$key]['over_time_real']."
											, over_time_confirm = ".$over_arr[$key]['over_time_confirm']."
											, over_day_real = ".$over_arr[$key]['over_day_real']."
											, over_day_confirm = ".$over_arr[$key]['over_day_confirm']."
										where
											date_y = ".$set_year."
											AND date_m = ".$set_month."
											AND memberno = '".$key."'
									";
									if($action){
										mysql_query($azsql,$db);
									}else{
										echo '<br>'.$azsql;
									}
								}
							}else{
								$azsql = "
									UPDATE
										worker_total_tbl A
										, (
											SELECT
												(SUBSTRING_INDEX(total_time, ':', 1) * 60 + SUBSTRING_INDEX(total_time, ':', -1) * 1 ) AS over_time_real
												, (SUBSTRING_INDEX(total_tmp_apply_time, ':', 1) * 60 + SUBSTRING_INDEX(total_tmp_apply_time, ':', -1) * 1 ) AS over_time_confirm
												, ( weekday_count + holyday_count ) AS over_day_real
												, ( weekday_count + holyday_count - IFNULL(daycount, 0) ) AS over_day_confirm
												, memberno
												, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%Y')*1 AS date_y
												, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%m')*1 AS date_m
											FROM
												overtime_save_new_tbl
											WHERE
												DATE = '".$set_date_bar."'
										) B
									SET
										A.over_time_real = B.over_time_real
										, A.over_time_confirm = B.over_time_confirm
										, A.over_day_real = B.over_day_real
										, A.over_day_confirm = B.over_day_confirm
									WHERE
										A.date_y = B.date_y
										AND A.date_m = B.date_m
										AND A.memberno = B.memberno
								;";
								if($action){
									mysql_query($azsql,$db);
								}else{
									echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
									echo $azsql;
								}
							}

							//평균 추가
								/* 평균 연장근로시간 실제 - 분 */
								$azsql = "
									delete from worker_average_tbl where date_y = ".$set_year." AND date_m = ".$set_month."
								";
								if($action){
									mysql_query($azsql,$db);
								}else{
									echo '<br><br>평균 전체 삭제<br>';
									echo $azsql;
								}

								$azsql = "
									INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
									SELECT
										MAX(date_y) AS date_y
										, MAX(date_m) AS date_m
										, 'dept'
										, dept_code
										, ROUND( SUM(over_time_real) / COUNT(dept_code) , 1) AS time_average
										, ROUND( SUM(over_day_real) / COUNT(dept_code) , 1) AS day_average
										, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_code) , 1) AS per_average
									FROM
										worker_total_tbl A
									WHERE
										date_y = ".$set_year."
										AND date_m = ".$set_month."
										and dept_code is not null
									GROUP BY
										dept_code
									;
								";
								if($action){
									mysql_query($azsql,$db);
								}else{
									echo '<br><br>부서 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
									echo $azsql;
								}

								$azsql = "
									INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
									SELECT
										MAX(date_y) AS date_y
										, MAX(date_m) AS date_m
										, 'rank'
										, rank_code
										, ROUND( SUM(over_time_real) / COUNT(rank_code) , 1) AS time_average
										, ROUND( SUM(over_day_real) / COUNT(rank_code) , 1) AS day_average
										, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(rank_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(rank_code) , 1) + ROUND( SUM(login_cnt) / COUNT(rank_code) , 1) AS per_average
									FROM
										worker_total_tbl A
									WHERE
										date_y = ".$set_year."
										AND date_m = ".$set_month."
										and rank_code is not null
									GROUP BY
										rank_code
									;
								";
								if($action){
									mysql_query($azsql,$db);
								}else{
									echo '<br><br>직위 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
									echo $azsql;
								}

								$azsql = "
									INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
									SELECT
										MAX(date_y) AS date_y
										, date_m
										, 'all'
										, 'all'
										, ROUND( SUM(over_time_real) / COUNT(date_m) , 1) AS time_average
										, ROUND( SUM(over_day_real) / COUNT(date_m) , 1) AS day_average
										, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(date_m) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(date_m) , 1) + ROUND( SUM(login_cnt) / COUNT(date_m) , 1) AS per_average
									FROM
										worker_total_tbl A
									WHERE
										date_y = ".$set_year."
										AND date_m = ".$set_month."
									GROUP BY
										date_m
									;
								";
								if($action){
									mysql_query($azsql,$db);
								}else{
									echo '<br><br>전체 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
									echo $azsql;
								}

								$azsql = "
									INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
									SELECT
										MAX(date_y) AS date_y
										, date_m
										, 'dept_top'
										, dept_top_code
										, ROUND( SUM(over_time_real) / COUNT(dept_top_code) , 1) AS time_average
										, ROUND( SUM(over_day_real) / COUNT(dept_top_code) , 1) AS day_average
										, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_top_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_top_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_top_code) , 1) AS per_average
									FROM
										worker_total_tbl A
									WHERE
										date_y = ".$set_year."
										AND date_m = ".$set_month."
										and dept_top_code is not null
									GROUP BY
										dept_top_code
									;
								";
								if($action){
									mysql_query($azsql,$db);
								}else{
									echo '<br><br>본부 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
									echo $azsql;
								}

							echo '<br><br>'.$set_date.' finish';
						}

					//장헌 worker 입력
						/*장헌 정보 셋팅*/

						/*장헌 DB정보*/
						$db_hostname_jang ='erp.jangheon.co.kr';
						$db_database_jang = 'hanmacerp';
						$db_username_jang = 'root';
						$db_password_jang = 'erp';

						/*장헌 DB연결----------------------------------------------------------------------*/
						$db_jang	= mysql_connect($db_hostname_jang,$db_username_jang,$db_password_jang);
							if(!$db_jang) die ("Unable to connect to MySql : ".mysql_error());
						mysql_select_db($db_database_jang);
						mysql_set_charset("utf-8",$db_jang);
						mysql_query("set names utf8");

						for($c=0; $c<2; $c++){
							if($c == 0){
								$set_date = $set_date_ori;
							}else{
								$set_date = date("Ym",strtotime("-".$c." month", strtotime($set_date_ori."01")));
							}

							$set_date_bar = substr($set_date,0,4)."-".substr($set_date,4,2);
							$set_year = substr($set_date,0,4);
							$set_month = (int)substr($set_date,4,2);
							$set_date_last = $set_date.date('t', strtotime($set_date_bar."-01"));
							$set_date_last_bar = $set_date_bar.'-'.date('t', strtotime($set_date_bar."-01"));
							$set_month_Pre = date("Y-m",strtotime("-1 month", strtotime($set_date."01")));

							/* 기간 월별로 나누기 */
							$azsql_s = "
								SELECT *, date_format(start_time,'%Y-%m') as s_ym, date_format(end_time,'%Y-%m') as e_ym FROM userstate_tbl
								WHERE
									state = 1
									AND ( date_format(start_time,'%Y%m') = '".$set_date."' OR date_format(end_time,'%Y%m') = '".$set_date."' )
									AND date_format(start_time,'%Y%m') != date_format(end_time,'%Y%m')
							;";
							if(!$action){
								echo '<br><br>기간 월별로 나누기<br>';
								echo $azsql_s;
							}

							$sql_arr = array();
							$re = mysql_query($azsql_s,$db);
							while($row=mysql_fetch_array($re)){
								/* 나누는거 해야함. */
								//print_r($row);
								if($row["s_ym"] == $set_date_bar){
									$azsql = "
										update userstate_tbl set start_time = '".$row["e_ym"]."-01' where num = ".$row["num"]."
									;";
									array_push($sql_arr, $azsql);
									$azsql = "
										insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
										values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["start_time"]."', '".$row["s_ym"].'-'.date('t', strtotime($row["start_time"]))."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
									;";
									array_push($sql_arr, $azsql);
								}else{
									$azsql = "
										update userstate_tbl set end_time = '".$row["s_ym"]."-".date('t', strtotime($row["start_time"]))."' where num = '".$row["num"]."'
									;";
									array_push($sql_arr, $azsql);
									$azsql = "
										insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
										values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["e_ym"]."-01', '".$row["end_time"]."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
									;";
									array_push($sql_arr, $azsql);
								}
							}

							for($i=0; $i<count($sql_arr); $i++){
								if($action){
									mysql_query($sql_arr[$i],$db_jang);
								}else{
									echo '<br>'.$sql_arr[$i];
								}
							}

							$azsql = "
								select
									member_id as EMP_NO
									, LPAD(DEPT_CODE,2,'0') AS DEPT_CODE
									, ( select code from total_systemconfig_tbl B where syskey = 'RANK' and comp_code = '40' and sys_code = '0' and B.name = A.rank_name ORDER BY CODE LIMIT 1 ) AS RANK_CODE
								from
									total_member_tbl A
								WHERE
									sys_comp_code = '40'
									and working_comp = '40'
									and join_date <= '".$set_date_last_bar."'
									and (
										retire_date = '0000-00-00'
										or retire_date > '".$set_date_bar."-01'
									)
								;
							";
							if(!$action){
								echo '<br><br>장헌인원<br>';
								echo $azsql;
							}
							//echo $azsql;

							$member_info = array();
							$re = mysql_query($azsql,$db);
							while($row=mysql_fetch_array($re)){
								$member_info[$row["EMP_NO"]] = $row;
							}

							/*월 추가*/
							$azsql = "
								delete from worker_date_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
							;";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>월 삭제<br>';
								echo $azsql;
							}

							$azsql = "
								INSERT INTO worker_date_tbl (date_y, date_m, date_d, week, holy )
								SELECT
									date_y
									, date_m
									, COUNT(date_m) AS date_d
									, SUM(week) AS week
									, (SELECT COUNT(date) FROM holyday_tbl B WHERE a.date_y = YEAR(B.date) AND A.date_m = MONTH(B.date) and DAYOFWEEK( B.date ) in (2,3,4,5,6) ) AS holy
								FROM (
									SELECT a.date_ymd, YEAR(a.date_ymd) AS date_y, MONTH(a.date_ymd) AS date_m, IF( DAYOFWEEK( a.date_ymd ) = 1 OR DAYOFWEEK( a.date_ymd ) = 7, 1, 0) AS week
									FROM (
									SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
									SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
									UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
									) AS a
									CROSS JOIN (
									SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
									UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
									) AS b
									CROSS JOIN (
									SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
									UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
									) AS c
									) AS a
									WHERE 1 = 1
									AND a.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
								) a GROUP BY date_y, date_m
							;";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>월 추가<br>';
								echo $azsql;
							}

							/*사번별 추가*/

							$azsql = "
								delete from worker_total_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
							;";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>사번별 삭제<br>';
								echo $azsql;
							}

							if(!$action){
								echo '<br><br>사번별 추가<br>';
							}
							foreach($member_info as $key => $value){
								$azsql = "
									INSERT INTO worker_total_tbl (memberno, date_y, date_m, dept_top_code, dept_code, rank_code)
									SELECT '$key', ".$set_year.", ".$set_month.", (SELECT Code FROM systemconfig_tbl WHERE SysKey = 'GroupCode_Top' AND CodeORName LIKE CONCAT( '%g', '".$value['DEPT_CODE']."'*1, 'g%' )) , '".$value['DEPT_CODE']."', '".$value['RANK_CODE']."' FROM dual ;
								";
								//echo '<br>'.$azsql.'<br>';
								if($action){
									mysql_query($azsql,$db_jang);
								}else{
									echo '<br>'.$azsql;
								}
							}

							/*연차 1, 18, 30, 31 - 주말,휴일 제외*/
							$azsql = "
								UPDATE worker_total_tbl BB SET vacation1 = IFNULL((
									SELECT SUM(vacation1)
									FROM (
										SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
										FROM (
											SELECT
												memberno
												, start_time
												, end_time
												, (
													CASE
														WHEN state = 1 THEN IF(note LIKE '%반차%', 4, 8)
														WHEN state = 30 THEN 4
														WHEN state = 31 THEN 4
														ELSE sub_code
													END
												) * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
											FROM (
												SELECT
													memberno
													, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
													, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
													, state
													, note
													, sub_code
												FROM
													userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (1, 18, 30, 31)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) AAA
											ORDER BY vacation desc
										) A
										, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT '1986-12-31' FROM dual ) B
										GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
									) AA
									WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
									GROUP BY memberno, date_y, date_m
								), 0)
								WHERE date_y = ".$set_year." AND date_m = ".$set_month."
							;";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>연차 1, 18, 30, 31 - 주말,휴일 제외<br>';
								echo $azsql;
							}

							/*기타 주말제외. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가), 10:대기, 17:기타*/
							$azsql = "
								UPDATE worker_total_tbl BB SET vacation2 = IFNULL((
									SELECT SUM(vacation1)
									FROM (
										SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
										FROM (
											SELECT
												memberno
												, start_time
												, end_time
												, 8 * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
											FROM (
												SELECT
													memberno
													, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
													, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
												FROM
													userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (5,6,7,8,10,17)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) AAA
											ORDER BY vacation desc
										) A
										, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT '1986-12-31' FROM dual ) B
										GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
									) AA
									WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
									GROUP BY memberno, date_y, date_m
								), 0)
								WHERE date_y = ".$set_year." AND date_m = ".$set_month."
							;";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>기타 5,6,7,8,10,17 주말제외<br>';
								echo $azsql;
							}

							/*비고 입력. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가),17:기타, 20:합사, 21:합사, 9:파견, 15:파견*/
							$azsql = "
								UPDATE
									worker_total_tbl A
									, (
										SELECT
											memberno
											, ".$set_year." AS date_y
											, ".$set_month." AS date_m
											, CONCAT(
												IF(SUM(state_1) > 0, '_경조', '')
												, IF(SUM(state_2) > 0, '_보건', '')
												, IF(SUM(state_3) > 0, '_출산', '')
												, IF(SUM(state_4) > 0, '_특별', '')
												, IF(SUM(state_5) > 0, '_훈련', '')
												, IF(SUM(state_6) > 0, '_교육', '')
												, IF(SUM(state_7) > 0, '_기타', '')
												, IF(SUM(state_8) > 0, '_합사', '')
												, IF(SUM(state_9) > 0, '_파견', '')
											) AS etc
										FROM
											(

												SELECT
													memberno
													, IF(state = 7, 1, 0) AS state_1
													, 0 AS state_2
													, 0 AS state_3
													, 0 AS state_4
													, IF(state = 5, 1, 0) AS state_5
													, IF(state = 6, 1, 0) AS state_6
													, 0 AS state_7
													, 0 AS state_8
													, 0 AS state_9
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (5,6,7)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )

												UNION ALL

												SELECT
													memberno
													, 0
													, state_2
													, state_3
													, state_4
													, 0
													, 0
													, state - state_2 - state_3 - state_4
													, 0
													, 0
												FROM (
													SELECT
														memberno
														, IF(note LIKE '%보건%', 1, 0) AS state_2
														, IF(note LIKE '%출산%', 1, 0) AS state_3
														, IF(note LIKE '%특별%', 1, 0) AS state_4
														, 1 AS state
													FROM userstate_tbl
													WHERE
														'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
														AND state IN (8,17)
														AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
												) A

												UNION ALL

												SELECT
													memberno
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 1
													, 0
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (20, 21)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
												UNION ALL

												SELECT
													memberno
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 1
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (9, 15)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) C
										GROUP BY memberno
									) B
								SET
									A.etc = B.etc
								WHERE
									A.date_y = B.date_y
									AND A.date_m = B.date_m
									AND A.memberno = B.memberno
								;
							";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>비고 입력<br>';
								echo $azsql;
							}

							/*지각 횟수*/
							$azsql = "
								UPDATE
									worker_total_tbl BB
								SET
									tardy = IFNULL((
										SELECT tardy FROM (
											SELECT
												A.memberno
												, YEAR(A.entrytime) AS date_y
												, MONTH(A.entrytime) AS date_m
												, SUM( CASE WHEN date_format(A.entrytime,'%H') >= IFNULL(B.tardy_h, 9) AND date_format(A.entrytime,'%i') > IFNULL(B.tardy_m, 0) THEN IF( ( SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(A.entrytime,'%Y-%m-%d') ) > 0 , 0, 1) ELSE 0 end) AS tardy
											FROM (
												SELECT A.memberno, A.entrytime FROM (
													SELECT
														memberno
														, entrytime
													FROM
														dallyproject_tbl
													WHERE
														EntryTime BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
														AND DAYOFWEEK(entrytime) NOT IN (1,7)
														AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
												) A
												left join
												( SELECT memberno, start_time, end_time FROM userstate_tbl WHERE state IN ( 1, 18, 17, 5, 6, 2, 3 ) AND ( date_format( start_time , '%Y%m') = '".$set_date."' OR date_format( end_time , '%Y%m') = '".$set_date."' ) ) B
												ON
													A.memberno = B.memberno
													AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.start_time AND B.end_time
												WHERE B.memberno IS null
											) A
											left join
											( SELECT * FROM worker_tardy_tbl WHERE '".$set_date."' between date_format(s_date, '%Y%m') and date_format(e_date, '%Y%m') ) B
											ON
												A.memberno = B.memberno
												AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.s_date AND B.e_date
											GROUP BY A.memberno
										) AA
										WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
									), 0)
									, BB.login_cnt = (select count(MemberNo) from dallyproject_tbl where MemberNo = BB.MemberNo and date_format(EntryTime , '%Y-%m-%d') IN (
										SELECT date_format(DATE , '%Y-%m-%d') FROM (
											SELECT date FROM holyday_tbl WHERE DATE BETWEEN '".$set_date_bar."-01' AND '".$set_date_last_bar."'
											UNION ALL
											SELECT date_format(date_ymd , '%Y-%m-%d') FROM (
												SELECT C.date_ymd, YEAR(C.date_ymd) AS date_y, MONTH(C.date_ymd) AS date_m, IF( DAYOFWEEK( C.date_ymd ) = 1 OR DAYOFWEEK( C.date_ymd ) = 7, 1, 0) AS week
												FROM (
													SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
													SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
													UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
													) AS a
													CROSS JOIN (
													SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
													UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
													) AS b
													CROSS JOIN (
													SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
													UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
													) AS c
												) AS C
												WHERE
													C.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
											) D WHERE week = 1
										) E GROUP BY date
									))
								WHERE date_y = ".$set_year." AND date_m = ".$set_month."
							;";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>지각 횟수 - 출장 외출 그런거 안따짐.<br>';
								echo $azsql;
							}

							/*연장근로시간 - 실제,인정, 연장근로일수 - 실제,인정*/
							if(date("Ym",strtotime("-2 month", strtotime(date('Ym')."01"))) < $set_date){
								$azsql = "
									SELECT C.MemberNo, EntryTime, termTime, min_MINUTE, max_MINUTE, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_time, C.RankCode FROM
										(
											SELECT
												MemberNo
												, ( SELECT RankCode FROM member_tbl WHERE MemberNo = A.MemberNo ) AS RankCode
												, EntryTime
												, date_format(OverTime,'%Y-%m-%d') AS over_date
												, IF(
													DAYOFWEEK = 0 and date_format(OverTime,'%H:%i') = '00:00'
													, 0
													, TIMESTAMPDIFF(
														MINUTE
														, IF(
															DAYOFWEEK = 0
															, OverTime
															, IF(
																date_format(EntryTime,'%H:%i') < B.start_time
																, CONCAT(date_format(EntryTime,'%Y-%m-%d '), B.start_time, ':00')
																, EntryTime
															)
														)
														, LeaveTime
													)
												) AS termTime
												, (SUBSTRING_INDEX(min_time, ':', 1) * 60 + SUBSTRING_INDEX(min_time, ':', -1) * 1 ) AS min_MINUTE
												, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_MINUTE
											FROM
												(
													SELECT
														MemberNo
														, EntryTime
														, IF( DAYOFWEEK( EntryTime ) = 1 OR DAYOFWEEK( EntryTime ) = 7, 1, (SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(dallyproject_tbl.EntryTime,'%Y-%m-%d ') ) ) AS DAYOFWEEK
														, OverTime
														, LeaveTime
													FROM dallyproject_tbl
													WHERE EntryTime BETWEEN '".$set_month_Pre."-21 00:00:00' AND '".$set_date_bar."-20 23:59:59' AND MODIFY = 1
													ORDER BY MemberNo
												) A
												, (
													SELECT * FROM overtime_basic_new_tbl WHERE code in (100, 101)
												) B
											WHERE
												A.DAYOFWEEK = B.code or (A.DAYOFWEEK+100) = B.code
										) C
										, (
											SELECT * FROM overtime_basic_new_tbl WHERE code BETWEEN 102 AND 105
											union all
											select 999, '', '', '999:00', '', '-C0-C1-C2-C4-C5-C6-C7-C8-C9-C3A-' from dual
										) D
									WHERE
										D.RankCode LIKE CONCAT('%-', C.RankCode, '-%')
									order by MemberNo, EntryTime
								;";
								if($action){
								}else{
									echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
									echo $azsql;
								}

								$over_arr = array();

								$re = mysql_query($azsql,$db_jang);
								while($row=mysql_fetch_array($re)){
									if($over_arr[$row['MemberNo']] == null){
										$over_arr[$row['MemberNo']] = array();
										$over_arr[$row['MemberNo']]['over_day_real'] = 0;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_day_confirm'] = 0;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_time_real'] = 0;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_time_confirm'] = 0;	//실제 일한 날짜
										$over_check = false;
									}

									if( $row['termTime'] >= $row['min_MINUTE'] ){	//최소 시간보다 클때

										$over_arr[$row['MemberNo']]['over_day_real']++;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_day_confirm']++;	//인정 날짜
										$over_arr[$row['MemberNo']]['over_time_real'] += $row['termTime'];	//실제 야근시간

										if( $row['termTime'] > $row['max_MINUTE'] ){	//하루 최대시간 보다 많을때
											$row['termTime'] = $row['max_MINUTE'];
										}

										$over_arr[$row['MemberNo']]['over_time_confirm'] += $row['termTime'];	//인정 야근시간
										if($over_arr[$row['MemberNo']]['over_time_confirm'] > $row['max_time']){	//총 야근시간이 최대 인정시간보다 많을때
											if($over_check and strpos($row['max_MINUTE'], 'C') === false){
												$over_arr[$row['MemberNo']]['over_day_confirm']--;	//인정 날짜
											}
											$over_check = true;
											//$over_arr[$row['MemberNo']]['over_time_confirm'] = $row['max_time'];	//야근시간은 인정시간만큼
										}
									}
								}
								//print_r($over_arr);

								foreach ($over_arr as $key => $value) {
									$azsql = "
										update worker_total_tbl set
											over_time_real = ".$over_arr[$key]['over_time_real']."
											, over_time_confirm = ".$over_arr[$key]['over_time_confirm']."
											, over_day_real = ".$over_arr[$key]['over_day_real']."
											, over_day_confirm = ".$over_arr[$key]['over_day_confirm']."
										where
											date_y = ".$set_year."
											AND date_m = ".$set_month."
											AND memberno = '".$key."'
									";
									if($action){
										mysql_query($azsql,$db_jang);
									}else{
										echo '<br>'.$azsql;
									}
								}
							}else{
								$azsql = "
									UPDATE
										worker_total_tbl A
										, (
											SELECT
												(SUBSTRING_INDEX(total_time, ':', 1) * 60 + SUBSTRING_INDEX(total_time, ':', -1) * 1 ) AS over_time_real
												, (SUBSTRING_INDEX(total_tmp_apply_time, ':', 1) * 60 + SUBSTRING_INDEX(total_tmp_apply_time, ':', -1) * 1 ) AS over_time_confirm
												, ( weekday_count + holyday_count ) AS over_day_real
												, ( weekday_count + holyday_count - IFNULL(daycount, 0) ) AS over_day_confirm
												, memberno
												, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%Y')*1 AS date_y
												, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%m')*1 AS date_m
											FROM
												overtime_save_new_tbl
											WHERE
												DATE = '".$set_date_bar."'
										) B
									SET
										A.over_time_real = B.over_time_real
										, A.over_time_confirm = B.over_time_confirm
										, A.over_day_real = B.over_day_real
										, A.over_day_confirm = B.over_day_confirm
									WHERE
										A.date_y = B.date_y
										AND A.date_m = B.date_m
										AND A.memberno = B.memberno
								;";
								if($action){
									mysql_query($azsql,$db_jang);
								}else{
									echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
									echo $azsql;
								}
							}


							//평균 추가
							/* 평균 연장근로시간 실제 - 분 */
							$azsql = "
								delete from worker_average_tbl where date_y = ".$set_year." AND date_m = ".$set_month."
							";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>평균 전체 삭제<br>';
								echo $azsql;
							}

							$azsql = "
								INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
								SELECT
									MAX(date_y) AS date_y
									, MAX(date_m) AS date_m
									, 'dept'
									, dept_code
									, ROUND( SUM(over_time_real) / COUNT(dept_code) , 1) AS time_average
									, ROUND( SUM(over_day_real) / COUNT(dept_code) , 1) AS day_average
									, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_code) , 1) AS per_average
								FROM
									worker_total_tbl A
								WHERE
									date_y = ".$set_year."
									AND date_m = ".$set_month."
									and dept_code is not null
								GROUP BY
									dept_code
								;
							";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>부서 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
								echo $azsql;
							}

							$azsql = "
								INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
								SELECT
									MAX(date_y) AS date_y
									, MAX(date_m) AS date_m
									, 'rank'
									, rank_code
									, ROUND( SUM(over_time_real) / COUNT(rank_code) , 1) AS time_average
									, ROUND( SUM(over_day_real) / COUNT(rank_code) , 1) AS day_average
									, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(rank_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(rank_code) , 1) + ROUND( SUM(login_cnt) / COUNT(rank_code) , 1) AS per_average
								FROM
									worker_total_tbl A
								WHERE
									date_y = ".$set_year."
									AND date_m = ".$set_month."
									and rank_code is not null
								GROUP BY
									rank_code
								;
							";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>직위 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
								echo $azsql;
							}

							$azsql = "
								INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
								SELECT
									MAX(date_y) AS date_y
									, date_m
									, 'all'
									, 'all'
									, ROUND( SUM(over_time_real) / COUNT(date_m) , 1) AS time_average
									, ROUND( SUM(over_day_real) / COUNT(date_m) , 1) AS day_average
									, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(date_m) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(date_m) , 1) + ROUND( SUM(login_cnt) / COUNT(date_m) , 1) AS per_average
								FROM
									worker_total_tbl A
								WHERE
									date_y = ".$set_year."
									AND date_m = ".$set_month."
								GROUP BY
									date_m
								;
							";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>전체 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
								echo $azsql;
							}

							$azsql = "
								INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
								SELECT
									MAX(date_y) AS date_y
									, date_m
									, 'dept_top'
									, dept_top_code
									, ROUND( SUM(over_time_real) / COUNT(dept_top_code) , 1) AS time_average
									, ROUND( SUM(over_day_real) / COUNT(dept_top_code) , 1) AS day_average
									, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_top_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_top_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_top_code) , 1) AS per_average
								FROM
									worker_total_tbl A
								WHERE
									date_y = ".$set_year."
									AND date_m = ".$set_month."
									and dept_top_code is not null
								GROUP BY
									dept_top_code
								;
							";
							if($action){
								mysql_query($azsql,$db_jang);
							}else{
								echo '<br><br>본부 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
								echo $azsql;
							}
							echo '<br><br>'.$set_date.' jang finish';
						}

					//PTC worker 입력
						/*PTC 정보 셋팅*/

						/*PTC DB정보*/
						$db_hostname_PTC ='erp.pre-cast.co.kr';
						$db_database_PTC = 'hanmacerp';
						$db_username_PTC = 'root';
						$db_password_PTC = 'erp';

						/*PTC DB연결----------------------------------------------------------------------*/
						$db_PTC	= mysql_connect($db_hostname_PTC,$db_username_PTC,$db_password_PTC);
							if(!$db_PTC) die ("Unable to connect to MySql : ".mysql_error());
						mysql_select_db($db_database_PTC);
						mysql_set_charset("utf-8",$db_PTC);
						mysql_query("set names utf8");

						for($c=0; $c<2; $c++){
							if($c == 0){
								$set_date = $set_date_ori;
							}else{
								$set_date = date("Ym",strtotime("-".$c." month", strtotime($set_date_ori."01")));
							}

							$set_date_bar = substr($set_date,0,4)."-".substr($set_date,4,2);
							$set_year = substr($set_date,0,4);
							$set_month = (int)substr($set_date,4,2);
							$set_date_last = $set_date.date('t', strtotime($set_date_bar."-01"));
							$set_date_last_bar = $set_date_bar.'-'.date('t', strtotime($set_date_bar."-01"));
							$set_month_Pre = date("Y-m",strtotime("-1 month", strtotime($set_date."01")));

							/* 기간 월별로 나누기 */
							$azsql_s = "
								SELECT *, date_format(start_time,'%Y-%m') as s_ym, date_format(end_time,'%Y-%m') as e_ym FROM userstate_tbl
								WHERE
									state = 1
									AND ( date_format(start_time,'%Y%m') = '".$set_date."' OR date_format(end_time,'%Y%m') = '".$set_date."' )
									AND date_format(start_time,'%Y%m') != date_format(end_time,'%Y%m')
							;";
							if(!$action){
								echo '<br><br>기간 월별로 나누기<br>';
								echo $azsql_s;
							}

							$sql_arr = array();
							$re = mysql_query($azsql_s,$db);
							while($row=mysql_fetch_array($re)){
								/* 나누는거 해야함. */
								//print_r($row);
								if($row["s_ym"] == $set_date_bar){
									$azsql = "
										update userstate_tbl set start_time = '".$row["e_ym"]."-01' where num = ".$row["num"]."
									;";
									array_push($sql_arr, $azsql);
									$azsql = "
										insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
										values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["start_time"]."', '".$row["s_ym"].'-'.date('t', strtotime($row["start_time"]))."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
									;";
									array_push($sql_arr, $azsql);
								}else{
									$azsql = "
										update userstate_tbl set end_time = '".$row["s_ym"]."-".date('t', strtotime($row["start_time"]))."' where num = '".$row["num"]."'
									;";
									array_push($sql_arr, $azsql);
									$azsql = "
										insert into userstate_tbl (MemberNo, GroupCode, state, start_time, end_time, ProjectCode, NewProjectCode, note, sub_code)
										values ('".$row["MemberNo"]."', '".$row["GroupCode"]."', '".$row["state"]."', '".$row["e_ym"]."-01', '".$row["end_time"]."', '".$row["ProjectCode"]."', '".$row["NewProjectCode"]."', '".$row["note"]."', '".$row["sub_code"]."')
									;";
									array_push($sql_arr, $azsql);
								}
							}

							for($i=0; $i<count($sql_arr); $i++){
								if($action){
									mysql_query($sql_arr[$i],$db_PTC);
								}else{
									echo '<br>'.$sql_arr[$i];
								}
							}

							$azsql = "
								select
									member_id as EMP_NO
									, LPAD(DEPT_CODE,2,'0') AS DEPT_CODE
									, ( select code from total_systemconfig_tbl B where syskey = 'RANK' and comp_code = '60' and sys_code = '0' and B.name = A.rank_name ORDER BY CODE LIMIT 1 ) AS RANK_CODE
								from
									total_member_tbl A
								WHERE
									sys_comp_code = '60'
									and working_comp = '60'
									and join_date <= '".$set_date_last_bar."'
									and (
										retire_date = '0000-00-00'
										or retire_date > '".$set_date_bar."-01'
									)
								;
							";
							if(!$action){
								echo '<br><br>PTC인원<br>';
								echo $azsql;
							}
							//echo $azsql;

							$member_info = array();
							$re = mysql_query($azsql,$db);
							while($row=mysql_fetch_array($re)){
								$member_info[$row["EMP_NO"]] = $row;
							}

							/*월 추가*/
							$azsql = "
								delete from worker_date_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
							;";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>월 삭제<br>';
								echo $azsql;
							}

							$azsql = "
								INSERT INTO worker_date_tbl (date_y, date_m, date_d, week, holy )
								SELECT
									date_y
									, date_m
									, COUNT(date_m) AS date_d
									, SUM(week) AS week
									, (SELECT COUNT(date) FROM holyday_tbl B WHERE a.date_y = YEAR(B.date) AND A.date_m = MONTH(B.date) and DAYOFWEEK( B.date ) in (2,3,4,5,6) ) AS holy
								FROM (
									SELECT a.date_ymd, YEAR(a.date_ymd) AS date_y, MONTH(a.date_ymd) AS date_m, IF( DAYOFWEEK( a.date_ymd ) = 1 OR DAYOFWEEK( a.date_ymd ) = 7, 1, 0) AS week
									FROM (
									SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
									SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
									UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
									) AS a
									CROSS JOIN (
									SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
									UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
									) AS b
									CROSS JOIN (
									SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
									UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
									) AS c
									) AS a
									WHERE 1 = 1
									AND a.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
								) a GROUP BY date_y, date_m
							;";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>월 추가<br>';
								echo $azsql;
							}

							/*사번별 추가*/

							$azsql = "
								delete from worker_total_tbl where date_y = '".$set_year."' and date_m = '".$set_month."'
							;";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>사번별 삭제<br>';
								echo $azsql;
							}

							if(!$action){
								echo '<br><br>사번별 추가<br>';
							}
							foreach($member_info as $key => $value){
								$azsql = "
									INSERT INTO worker_total_tbl (memberno, date_y, date_m, dept_top_code, dept_code, rank_code)
									SELECT '$key', ".$set_year.", ".$set_month.", (SELECT Code FROM systemconfig_tbl WHERE SysKey = 'GroupCode_Top' AND CodeORName LIKE CONCAT( '%g', '".$value['DEPT_CODE']."'*1, 'g%' )) , '".$value['DEPT_CODE']."', '".$value['RANK_CODE']."' FROM dual ;
								";
								//echo '<br>'.$azsql.'<br>';
								if($action){
									mysql_query($azsql,$db_PTC);
								}else{
									echo '<br>'.$azsql;
								}
							}

							/*연차 1, 18, 30, 31 - 주말,휴일 제외*/
							$azsql = "
								UPDATE worker_total_tbl BB SET vacation1 = IFNULL((
									SELECT SUM(vacation1)
									FROM (
										SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
										FROM (
											SELECT
												memberno
												, start_time
												, end_time
												, (
													CASE
														WHEN state = 1 THEN IF(note LIKE '%반차%', 4, 8)
														WHEN state = 30 THEN 4
														WHEN state = 31 THEN 4
														ELSE sub_code
													END
												) * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
											FROM (
												SELECT
													memberno
													, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
													, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
													, state
													, note
													, sub_code
												FROM
													userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (1, 18, 30, 31)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) AAA
											ORDER BY vacation desc
										) A
										, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT '1986-12-31' FROM dual ) B
										GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
									) AA
									WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
									GROUP BY memberno, date_y, date_m
								), 0)
								WHERE date_y = ".$set_year." AND date_m = ".$set_month."
							;";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>연차 1, 18, 30, 31 - 주말,휴일 제외<br>';
								echo $azsql;
							}

							/*기타 주말제외. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가), 10:대기, 17:기타*/
							$azsql = "
								UPDATE worker_total_tbl BB SET vacation2 = IFNULL((
									SELECT SUM(vacation1)
									FROM (
										SELECT A.memberno, YEAR(A.start_time) AS date_y, MONTH(A.start_time) AS date_m, A.start_time, A.end_time, (A.vacation - SUM(IF ( B.holy BETWEEN A.start_time AND A.end_time, 8, 0 ))) AS vacation1
										FROM (
											SELECT
												memberno
												, start_time
												, end_time
												, 8 * ((DATEDIFF(end_time, start_time) + 1) - ((WEEK(end_time) - WEEK(start_time)) * 2) ) AS vacation
											FROM (
												SELECT
													memberno
													, IF( date_format(start_time,'%Y%m') < '".$set_date."', '".$set_date_bar."-01', start_time ) AS start_time
													, IF( date_format(end_time,'%Y%m') > '".$set_date."', '".$set_date_last_bar."', end_time ) AS end_time
												FROM
													userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (5,6,7,8,10,17)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) AAA
											ORDER BY vacation desc
										) A
										, ( SELECT DATE AS holy FROM holyday_tbl WHERE date_format(DATE,'%Y%m') = '".$set_date."' AND weekday(DATE) NOT IN (5,6) UNION ALL SELECT '1986-12-31' FROM dual ) B
										GROUP BY A.memberno, A.start_time, A.end_time, A.vacation
									) AA
									WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
									GROUP BY memberno, date_y, date_m
								), 0)
								WHERE date_y = ".$set_year." AND date_m = ".$set_month."
							;";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>기타 5,6,7,8,10,17 주말제외<br>';
								echo $azsql;
							}

							/*비고 입력. 5:훈련,6:교육,7:경조,8:기타(보건휴가, 출산휴가, 특별휴가),17:기타, 20:합사, 21:합사, 9:파견, 15:파견*/
							$azsql = "
								UPDATE
									worker_total_tbl A
									, (
										SELECT
											memberno
											, ".$set_year." AS date_y
											, ".$set_month." AS date_m
											, CONCAT(
												IF(SUM(state_1) > 0, '_경조', '')
												, IF(SUM(state_2) > 0, '_보건', '')
												, IF(SUM(state_3) > 0, '_출산', '')
												, IF(SUM(state_4) > 0, '_특별', '')
												, IF(SUM(state_5) > 0, '_훈련', '')
												, IF(SUM(state_6) > 0, '_교육', '')
												, IF(SUM(state_7) > 0, '_기타', '')
												, IF(SUM(state_8) > 0, '_합사', '')
												, IF(SUM(state_9) > 0, '_파견', '')
											) AS etc
										FROM
											(

												SELECT
													memberno
													, IF(state = 7, 1, 0) AS state_1
													, 0 AS state_2
													, 0 AS state_3
													, 0 AS state_4
													, IF(state = 5, 1, 0) AS state_5
													, IF(state = 6, 1, 0) AS state_6
													, 0 AS state_7
													, 0 AS state_8
													, 0 AS state_9
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (5,6,7)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )

												UNION ALL

												SELECT
													memberno
													, 0
													, state_2
													, state_3
													, state_4
													, 0
													, 0
													, state - state_2 - state_3 - state_4
													, 0
													, 0
												FROM (
													SELECT
														memberno
														, IF(note LIKE '%보건%', 1, 0) AS state_2
														, IF(note LIKE '%출산%', 1, 0) AS state_3
														, IF(note LIKE '%특별%', 1, 0) AS state_4
														, 1 AS state
													FROM userstate_tbl
													WHERE
														'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
														AND state IN (8,17)
														AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
												) A

												UNION ALL

												SELECT
													memberno
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 1
													, 0
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (20, 21)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
												UNION ALL

												SELECT
													memberno
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 0
													, 1
												FROM userstate_tbl
												WHERE
													'".$set_date."' between date_format(start_time,'%Y%m') and date_format(end_time,'%Y%m')
													AND state IN (9, 15)
													AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
											) C
										GROUP BY memberno
									) B
								SET
									A.etc = B.etc
								WHERE
									A.date_y = B.date_y
									AND A.date_m = B.date_m
									AND A.memberno = B.memberno
								;
							";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>비고 입력<br>';
								echo $azsql;
							}

							/*지각 횟수*/
							$azsql = "
								UPDATE
									worker_total_tbl BB
								SET
									tardy = IFNULL((
										SELECT tardy FROM (
											SELECT
												A.memberno
												, YEAR(A.entrytime) AS date_y
												, MONTH(A.entrytime) AS date_m
												, SUM( CASE WHEN date_format(A.entrytime,'%H') >= IFNULL(B.tardy_h, 9) AND date_format(A.entrytime,'%i') > IFNULL(B.tardy_m, 0) THEN IF( ( SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(A.entrytime,'%Y-%m-%d') ) > 0 , 0, 1) ELSE 0 end) AS tardy
											FROM (
												SELECT A.memberno, A.entrytime FROM (
													SELECT
														memberno
														, entrytime
													FROM
														dallyproject_tbl
													WHERE
														EntryTime BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
														AND DAYOFWEEK(entrytime) NOT IN (1,7)
														AND memberno IN ( select memberno from worker_total_tbl where date_y = ".$set_year." and date_m = ".$set_month." )
												) A
												left join
												( SELECT memberno, start_time, end_time FROM userstate_tbl WHERE state IN ( 1, 18, 17, 5, 6, 2, 3 ) AND ( date_format( start_time , '%Y%m') = '".$set_date."' OR date_format( end_time , '%Y%m') = '".$set_date."' ) ) B
												ON
													A.memberno = B.memberno
													AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.start_time AND B.end_time
												WHERE B.memberno IS null
											) A
											left join
											( SELECT * FROM worker_tardy_tbl WHERE '".$set_date."' between date_format(s_date, '%Y%m') and date_format(e_date, '%Y%m') ) B
											ON
												A.memberno = B.memberno
												AND date_format(A.entrytime,'%Y-%m-%d') BETWEEN B.s_date AND B.e_date
											GROUP BY A.memberno
										) AA
										WHERE BB.memberno = AA.memberno AND BB.date_y = AA.date_y AND BB.date_m = AA.date_m
									), 0)
									, BB.login_cnt = (select count(MemberNo) from dallyproject_tbl where MemberNo = BB.MemberNo and date_format(EntryTime , '%Y-%m-%d') IN (
										SELECT date_format(DATE , '%Y-%m-%d') FROM (
											SELECT date FROM holyday_tbl WHERE DATE BETWEEN '".$set_date_bar."-01' AND '".$set_date_last_bar."'
											UNION ALL
											SELECT date_format(date_ymd , '%Y-%m-%d') FROM (
												SELECT C.date_ymd, YEAR(C.date_ymd) AS date_y, MONTH(C.date_ymd) AS date_m, IF( DAYOFWEEK( C.date_ymd ) = 1 OR DAYOFWEEK( C.date_ymd ) = 7, 1, 0) AS week
												FROM (
													SELECT DATE('".$set_date_last_bar."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
													SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
													UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
													) AS a
													CROSS JOIN (
													SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
													UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
													) AS b
													CROSS JOIN (
													SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
													UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
													) AS c
												) AS C
												WHERE
													C.date_ymd BETWEEN '".$set_date_bar."-01 00:00:00' AND '".$set_date_last_bar." 23:59:59'
											) D WHERE week = 1
										) E GROUP BY date
									))
								WHERE date_y = ".$set_year." AND date_m = ".$set_month."
							;";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>지각 횟수 - 출장 외출 그런거 안따짐.<br>';
								echo $azsql;
							}

							/*연장근로시간 - 실제,인정, 연장근로일수 - 실제,인정*/
							if(date("Ym",strtotime("-2 month", strtotime(date('Ym')."01"))) < $set_date){
								$azsql = "
									SELECT C.MemberNo, EntryTime, termTime, min_MINUTE, max_MINUTE, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_time, C.RankCode FROM
										(
											SELECT
												MemberNo
												, ( SELECT RankCode FROM member_tbl WHERE MemberNo = A.MemberNo ) AS RankCode
												, EntryTime
												, date_format(OverTime,'%Y-%m-%d') AS over_date
												, IF(
													DAYOFWEEK = 0 and date_format(OverTime,'%H:%i') = '00:00'
													, 0
													, TIMESTAMPDIFF(
														MINUTE
														, IF(
															DAYOFWEEK = 0
															, OverTime
															, IF(
																date_format(EntryTime,'%H:%i') < B.start_time
																, CONCAT(date_format(EntryTime,'%Y-%m-%d '), B.start_time, ':00')
																, EntryTime
															)
														)
														, LeaveTime
													)
												) AS termTime
												, (SUBSTRING_INDEX(min_time, ':', 1) * 60 + SUBSTRING_INDEX(min_time, ':', -1) * 1 ) AS min_MINUTE
												, (SUBSTRING_INDEX(max_time, ':', 1) * 60 + SUBSTRING_INDEX(max_time, ':', -1) * 1 ) AS max_MINUTE
											FROM
												(
													SELECT
														MemberNo
														, EntryTime
														, IF( DAYOFWEEK( EntryTime ) = 1 OR DAYOFWEEK( EntryTime ) = 7, 1, (SELECT COUNT(num) FROM holyday_tbl WHERE DATE = date_format(dallyproject_tbl.EntryTime,'%Y-%m-%d ') ) ) AS DAYOFWEEK
														, OverTime
														, LeaveTime
													FROM dallyproject_tbl
													WHERE EntryTime BETWEEN '".$set_month_Pre."-21 00:00:00' AND '".$set_date_bar."-20 23:59:59' AND MODIFY = 1
													ORDER BY MemberNo
												) A
												, (
													SELECT * FROM overtime_basic_new_tbl WHERE code in (100, 101)
												) B
											WHERE
												A.DAYOFWEEK = B.code or (A.DAYOFWEEK+100) = B.code
										) C
										, (
											SELECT * FROM overtime_basic_new_tbl WHERE code BETWEEN 102 AND 105
											union all
											select 999, '', '', '999:00', '', '-C0-C1-C2-C3-C4-C5-C6-C7-C8-C9-' from dual
										) D
									WHERE
										D.RankCode LIKE CONCAT('%', C.RankCode, '%')
									order by MemberNo, EntryTime
								;";
								if($action){
								}else{
									echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
									echo $azsql;
								}

								$over_arr = array();

								$re = mysql_query($azsql,$db_PTC);
								while($row=mysql_fetch_array($re)){
									if($over_arr[$row['MemberNo']] == null){
										$over_arr[$row['MemberNo']] = array();
										$over_arr[$row['MemberNo']]['over_day_real'] = 0;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_day_confirm'] = 0;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_time_real'] = 0;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_time_confirm'] = 0;	//실제 일한 날짜
										$over_check = false;
									}

									if( $row['termTime'] >= $row['min_MINUTE'] ){	//최소 시간보다 클때

										$over_arr[$row['MemberNo']]['over_day_real']++;	//실제 일한 날짜
										$over_arr[$row['MemberNo']]['over_day_confirm']++;	//인정 날짜
										$over_arr[$row['MemberNo']]['over_time_real'] += $row['termTime'];	//실제 야근시간

										if( $row['termTime'] > $row['max_MINUTE'] ){	//하루 최대시간 보다 많을때
											$row['termTime'] = $row['max_MINUTE'];
										}

										$over_arr[$row['MemberNo']]['over_time_confirm'] += $row['termTime'];	//인정 야근시간
										if($over_arr[$row['MemberNo']]['over_time_confirm'] > $row['max_time']){	//총 야근시간이 최대 인정시간보다 많을때
											if($over_check and strpos($row['max_MINUTE'], 'C') === false){
												$over_arr[$row['MemberNo']]['over_day_confirm']--;	//인정 날짜
											}
											$over_check = true;
											//$over_arr[$row['MemberNo']]['over_time_confirm'] = $row['max_time'];	//야근시간은 인정시간만큼
										}
									}
								}
								//print_r($over_arr);

								foreach ($over_arr as $key => $value) {
									$azsql = "
										update worker_total_tbl set
											over_time_real = ".$over_arr[$key]['over_time_real']."
											, over_time_confirm = ".$over_arr[$key]['over_time_confirm']."
											, over_day_real = ".$over_arr[$key]['over_day_real']."
											, over_day_confirm = ".$over_arr[$key]['over_day_confirm']."
										where
											date_y = ".$set_year."
											AND date_m = ".$set_month."
											AND memberno = '".$key."'
									";
									if($action){
										mysql_query($azsql,$db_PTC);
									}else{
										echo '<br>'.$azsql;
									}
								}
							}else{
								$azsql = "
									UPDATE
										worker_total_tbl A
										, (
											SELECT
												(SUBSTRING_INDEX(total_time, ':', 1) * 60 + SUBSTRING_INDEX(total_time, ':', -1) * 1 ) AS over_time_real
												, (SUBSTRING_INDEX(total_tmp_apply_time, ':', 1) * 60 + SUBSTRING_INDEX(total_tmp_apply_time, ':', -1) * 1 ) AS over_time_confirm
												, ( weekday_count + holyday_count ) AS over_day_real
												, ( weekday_count + holyday_count - IFNULL(daycount, 0) ) AS over_day_confirm
												, memberno
												, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%Y')*1 AS date_y
												, DATE_FORMAT(STR_TO_DATE(DATE, '%Y-%m'),'%m')*1 AS date_m
											FROM
												overtime_save_new_tbl
											WHERE
												DATE = '".$set_date_bar."'
										) B
									SET
										A.over_time_real = B.over_time_real
										, A.over_time_confirm = B.over_time_confirm
										, A.over_day_real = B.over_day_real
										, A.over_day_confirm = B.over_day_confirm
									WHERE
										A.date_y = B.date_y
										AND A.date_m = B.date_m
										AND A.memberno = B.memberno
								;";
								if($action){
									mysql_query($azsql,$db_PTC);
								}else{
									echo '<br><br>연장근로시간 - 실제,인정, 연장근로일수 - 실제<br>';
									echo $azsql;
								}
							}


							//평균 추가
							/* 평균 연장근로시간 실제 - 분 */
							$azsql = "
								delete from worker_average_tbl where date_y = ".$set_year." AND date_m = ".$set_month."
							";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>평균 전체 삭제<br>';
								echo $azsql;
							}

							$azsql = "
								INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
								SELECT
									MAX(date_y) AS date_y
									, MAX(date_m) AS date_m
									, 'dept'
									, dept_code
									, ROUND( SUM(over_time_real) / COUNT(dept_code) , 1) AS time_average
									, ROUND( SUM(over_day_real) / COUNT(dept_code) , 1) AS day_average
									, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_code) , 1) AS per_average
								FROM
									worker_total_tbl A
								WHERE
									date_y = ".$set_year."
									AND date_m = ".$set_month."
									and dept_code is not null
								GROUP BY
									dept_code
								;
							";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>부서 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
								echo $azsql;
							}

							$azsql = "
								INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
								SELECT
									MAX(date_y) AS date_y
									, MAX(date_m) AS date_m
									, 'rank'
									, rank_code
									, ROUND( SUM(over_time_real) / COUNT(rank_code) , 1) AS time_average
									, ROUND( SUM(over_day_real) / COUNT(rank_code) , 1) AS day_average
									, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(rank_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(rank_code) , 1) + ROUND( SUM(login_cnt) / COUNT(rank_code) , 1) AS per_average
								FROM
									worker_total_tbl A
								WHERE
									date_y = ".$set_year."
									AND date_m = ".$set_month."
									and rank_code is not null
								GROUP BY
									rank_code
								;
							";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>직위 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
								echo $azsql;
							}

							$azsql = "
								INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
								SELECT
									MAX(date_y) AS date_y
									, date_m
									, 'all'
									, 'all'
									, ROUND( SUM(over_time_real) / COUNT(date_m) , 1) AS time_average
									, ROUND( SUM(over_day_real) / COUNT(date_m) , 1) AS day_average
									, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(date_m) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(date_m) , 1) + ROUND( SUM(login_cnt) / COUNT(date_m) , 1) AS per_average
								FROM
									worker_total_tbl A
								WHERE
									date_y = ".$set_year."
									AND date_m = ".$set_month."
								GROUP BY
									date_m
								;
							";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>전체 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
								echo $azsql;
							}

							$azsql = "
								INSERT INTO worker_average_tbl ( date_y, date_m, split_type, average_code, time_average, day_average, per_average)
								SELECT
									MAX(date_y) AS date_y
									, date_m
									, 'dept_top'
									, dept_top_code
									, ROUND( SUM(over_time_real) / COUNT(dept_top_code) , 1) AS time_average
									, ROUND( SUM(over_day_real) / COUNT(dept_top_code) , 1) AS day_average
									, (SELECT date_d - week - holy FROM worker_date_tbl B WHERE B.date_y = A.date_y AND B.date_m = A.date_m ) - ROUND( SUM(vacation1) / 8 / COUNT(dept_top_code) , 1) - ROUND( SUM(vacation2) / 8 / COUNT(dept_top_code) , 1) + ROUND( SUM(login_cnt) / COUNT(dept_top_code) , 1) AS per_average
								FROM
									worker_total_tbl A
								WHERE
									date_y = ".$set_year."
									AND date_m = ".$set_month."
									and dept_top_code is not null
								GROUP BY
									dept_top_code
								;
							";
							if($action){
								mysql_query($azsql,$db_PTC);
							}else{
								echo '<br><br>본부 평균 연장근로시간 실제, 실제 연장일수, 근무비율 - 분<br>';
								echo $azsql;
							}
							echo '<br><br>'.$set_date.' PTC finish';
						}


					$mysql_end = mysql_close($db_jang);
					$mysql_end = mysql_close($db_PTC);
					$mysql_end = mysql_close($db);

					break;

				default:
					break;
			}
		}

		function REPORT_03(){
			extract($_REQUEST);
			$this->smarty->assign( 'PDF' , $PDF );
			$this->smarty->assign( 'OPENER' , $OPENER );
			$this->smarty->assign( 'COMPANY' , $COMPANY );
			//if( $WORKING_COMP == 'SAMAN' or $WORKING_COMP == 'HALLA'){
			if( $WORKING_COMP == 'SAMAN' or $WORKING_COMP == '10' or $WORKING_COMP == 'HALLA' or $WORKING_COMP == '50'){
				$this->smarty->display("planning_mng/Common/Deny.tpl");
				return false;
			}
			switch($MainAction){
				case "HTML_Ajax_01": 	$this->REPORT_03_HTML_Ajax_01();	break;
				default:
					global $db;

					if(date('m') == 1){
						$max_year = date('Y')-1;
					}else{
						$max_year = date('Y');
					}
					/*
					if($set_year == null or $set_year == ''){
						if( 1 < date('m') and date('m') < 4 ){
							$set_year = $max_year-1;
						}else{
							$set_year = $max_year;
						}
					}
					*/
					$YEAR_ARR = array();
					for($i=$max_year; $i>2019; $i--){
						array_push( $YEAR_ARR, $i );
					}

					$MEMBER_ARR = array();
					if($DEPT != ''){
						$azsql = "
							SELECT MemberNo
							FROM member_tbl
							where
								GroupCode = ".$DEPT."
								and WorkPosition = 1
							order by RankCode
						;";
						//echo $azsql;
						$re = mysql_query($azsql,$db);
						while($row=mysql_fetch_array($re)){
							array_push( $MEMBER_ARR, array( "set_year"=>$set_year, "MEMBERID"=>$row['MemberNo'] ) );
						}
					}else{
						array_push( $MEMBER_ARR, array( "set_year"=>$set_year, "MEMBERID"=>$memberno ) );
					}

					//print_r($MEMBER_ARR);
					$this->smarty->assign( 'set_year' , $set_year );
					$this->smarty->assign( 'search_month_s' , $search_month_s );
					$this->smarty->assign( 'search_month_e' , $search_month_e );
					$this->smarty->assign( 'DEPT' , $DEPT );
					$this->smarty->assign( 'memberno' , $memberno );
					$this->smarty->assign( 'YEAR_ARR' , $YEAR_ARR );
					$this->smarty->assign( 'MEMBER_ARR' , $MEMBER_ARR );
					$this->smarty->display("planning_mng/insaReport/PersonReport_03_Main_mvc.tpl");
			}
		}

		function REPORT_03_HTML_Ajax_01(){
			global $db;
			extract($_REQUEST);
			//print_r($_REQUEST);

			$this->smarty->assign('current_date',date("Y").".".date("m").".".date("d").".");

			//$set_year = 2020
			if($set_year == null or $set_year == ''){
				if(date('m') == 1){
					$set_year = date('Y')-1;
				}else{
					$set_year = date('Y');
				}
			}

			if($set_year == date('Y')){
				$max_month = date('m')-1;
			}else{
				$max_month = 13;
			}
			$set_date = $set_year.sprintf('%02d',$max_month);
			//echo "<div style='display:none'>$set_date</div>";

			$search_month = array();
			for($i=1; $i<13; $i++){
				array_push($search_month, $i);
			}
			$this->smarty->assign( 'search_month' , $search_month );

			//월별 근무세부내역 기간
			if( $search_month_s == '' ){
				$search_month_s = 1;
			}
			if( $search_month_e == '' ){
				$search_month_e = 12;
			}

			$this->smarty->assign( 'search_month_s' , $search_month_s );
			$this->smarty->assign( 'search_month_e' , $search_month_e );

			//$memberno = M06203
			$this->smarty->assign( 'memberno' , $memberno );
			$azsql = "
				select korName, RankCode, EntryDate
				FROM (
					SELECT korName, IF(RealRankCode = '' , RankCode, RealRankCode) AS RankCode, EntryDate
					FROM member_tbl
					WHERE
						MemberNo = '".$memberno."'
				) A
			;";
			//echo $azsql;
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				$this->smarty->assign( 'korName' , $row['korName'] );
				$EntryDate = $row['EntryDate'];
				//$max_time = $row['max_time'];
				//$this->smarty->assign( 'max_time' , $max_time );
			}

			// 2023.01.02 정명준 기간별 max_time이 달라서 날짜 추가하고 max_time가져오는 방법 수정.

			$azsql = "
				select * from (
					select
						setdate
						, SUBSTRING_INDEX ( max_time, ':', 1) AS max_time
						, SUBSTRING_INDEX (SUBSTRING_INDEX(akukka.rankcode,'-',numbers.n),'-',-1) rankcode
					from
						(
						select  1 n union  all  select 2
						union  all  select  3  union  all select 4
						union  all  select  5  union  all  select  6
						union  all  select  7  union  all  select  8
						union  all  select  9 union  all  select  10
						) numbers
						INNER JOIN
						( select * from overtime_basic_new_tbl where start_time = '' or start_time is null ) akukka
						on CHAR_LENGTH ( akukka.rankcode ) - CHAR_LENGTH ( REPLACE ( akukka.rankcode ,  '-' ,  '' ))>= numbers.n-1
				) b
				where
					b.rankcode != ''
				order by setdate, rankcode
			;";
			//echo $azsql;
			$max_time_arr = array();
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				if( $max_time_arr[$row['setdate']] == NULL ){
					$max_time_arr[$row['setdate']] = array();
				}
				$max_time_arr[ $row['setdate'] ][ $row['rankcode'] ] = $row['max_time'];
			}
			//print_r($max_time_arr);

			//$set_year = substr($set_date,0,4);
			$EntryYM = substr($EntryDate,0,4).substr($EntryDate,5,2);

			$this->smarty->assign( 'set_year' , $set_year );

			//년도별 근무내역
			$azsql = "
				SELECT * FROM worker_date_tbl
				WHERE
					date_y = ".$set_year."
				order by
					date_m
			;";
			$work_total = array();	//년 근무내역
			$work_month = array();	//월별 근무 세부 내역
			$worker_date = array();
			$work_month_sum = array();	//월별 근무 세부 내역 소계
			$temp_entry_arr = array();	//입사월 데이터
			$temp_now_arr = array();	//현재월 데이터

			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				$work_total[1] += $row['date_d'];	//전체일수
				$work_total[2] += $row['week'];	//주말
				$work_total[3] += $row['holy'];	//공휴일
			}
			$work_total[4] = $work_total[1] - ( $work_total[2] + $work_total[3] );	//기준근무일

			//월별 근무 세부 내역
			$work_month = array();
			$azsql = "
				SELECT * FROM (
					SELECT ms, work_d FROM (
						SELECT 1 AS ms FROM dual UNION SELECT 2 FROM dual UNION SELECT 3 FROM dual UNION SELECT 4 FROM dual UNION SELECT 5 FROM dual UNION SELECT 6 FROM dual UNION SELECT 7 FROM dual UNION SELECT 8 FROM dual UNION SELECT 9 FROM dual UNION SELECT 10 FROM dual UNION SELECT 11 FROM dual UNION SELECT 12 FROM dual
					) A
					left join
					(
						SELECT (date_d - week - holy) AS work_d, date_m FROM worker_date_tbl WHERE date_y = '".$set_year."'
					) B
					ON A.ms = B.date_m
				) A
				left join
				(
					SELECT * FROM
						worker_total_tbl
					WHERE
						date_y = ".$set_year."
						and memberno = '".$memberno."'
						and CONCAT(date_y,LPAD(date_m, 2, 0)) >= '".$EntryYM."'
						and CONCAT(date_y,LPAD(date_m, 2, 0)) <= '".$set_date."'
						and date_m between $search_month_s and $search_month_e
				) B
				ON A.ms = B.date_m
				order by
					ms
			;";
			//echo $azsql;

			$i=0;
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				if($row['memberno'] != null){
					$work_month_sum[1] += $row['vacation1']/8;
					$work_month_sum[2] += $row['vacation2']/8;
					$work_month_sum[3] += $row['vacation1']/8 + $row['vacation2']/8;

					$row['vacation1'] = sprintf('%0.1f', $row['vacation1']/8);	//연차
					$row['vacation2'] = sprintf('%0.1f', $row['vacation2']/8);	//기타
					$row['vacation'] = sprintf('%0.1f', $row['vacation1'] + $row['vacation2'] );	//합계

					$row['real_time'] = sprintf('%0.1f',($row['work_d'] - $row['vacation'] + $row['login_cnt'])).' / '.$row['work_d'] ;	//월 근무(기준일 - 연차 + 휴일근무) / 기준근무

					$row['over_real'] = floor($row['over_time_real']/60).':'.sprintf('%02d',floor($row['over_time_real']%60));	//
					//$row['over_confirm'] = floor($row['over_time_confirm']/60).':'.sprintf('%02d',floor($row['over_time_confirm']%60));	//

					//해당 월의 한도시간 구하기.

					foreach ( $max_time_arr as $max_time_arr_key => $max_time_arr_value ){
						if( $max_time_arr_key <= $row['date_y'].sprintf('%02d',$row['date_m']).'01' ){
							$max_time = $max_time_arr[$max_time_arr_key][$row['rank_code']];
						}
					}
					//if(strpos($row['rank_code'], 'C') !== false){
					/*
					if( substr( $row['rank_code'], 1, 1 ) == 'C' ){
						$this->smarty->assign( 'RankCode' , true );
					}else{
						$this->smarty->assign( 'RankCode' , false );
					}
					*/
					$row['RankCode'] = substr( $row['rank_code'], 0, 1 );

					if( $max_time < round($row['over_time_confirm']/60) ){
						$row['over_confirm'] = $max_time.':00';	//
					}else{
						$row['over_confirm'] = round($row['over_time_confirm']/60).':00';	//
					}

					$row['etc'] = substr(str_replace ( '_', ',', $row['etc']), 1) ;
					$work_total[9] += $row['login_cnt'];
				}

				array_push($work_month,$row);

				if($row['memberno'] != null){

					$work_month_sum[4] += $row['tardy'];
					$work_month_sum[51] += sprintf('%0.1f',($row['work_d'] - $row['vacation'] + $row['login_cnt']));
					$work_month_sum[52] += $row['work_d'];
					$work_month_sum[6] += $row['over_time_real'];
					if( $max_time < round($row['over_time_confirm']/60) ){
						$work_month_sum[7] += $max_time;
					}else{
						$work_month_sum[7] += round($row['over_time_confirm']/60);
					}
					$work_month_sum[8] += $row['over_day_real'];
					$i++;
					//$work_month_sum[9] += $row['over_day_confirm'];
				}
				if( $row['date_y'].sprintf('%02d',$row['date_m']) == $EntryYM ){
					$temp_entry_arr = $row;
				}
				if( $row['date_y'] == date('Y') and sprintf('%02d',$row['date_m']) == date('m') ){
					$temp_now_arr = $row;
				}
			}
			//print_r($work_month);
			//print_r($work_month_sum);

			//print_r($temp_arr);
			$work_month_sum_6 = $work_month_sum[6];
			$work_month_sum_7 = $work_month_sum[7];
			$work_month_sum_8 = $work_month_sum[8];

			if( $temp_entry_arr ){
				$work_month_sum_6 -= $temp_entry_arr['over_time_real'];
				if( $max_time < round($temp_entry_arr['over_time_confirm']/60) ){
					$work_month_sum_7 -= $max_time;
				}else{
					$work_month_sum_7 -= round($temp_entry_arr['over_time_confirm']/60);
				}
				$work_month_sum_8 -= $temp_entry_arr['over_day_real'];
				$i--;
			}

			if( $temp_now_arr ){
				$work_month_sum_6 -= $temp_now_arr['over_time_real'];
				if( $max_time < round($temp_now_arr['over_time_confirm']/60) ){
					$work_month_sum_7 -= $max_time;
				}else{
					$work_month_sum_7 -= round($temp_now_arr['over_time_confirm']/60);
				}
				$work_month_sum_8 -= $temp_now_arr['over_day_real'];
				$i--;
			}

			//print_r($work_month_sum);

			$work_total[8] = sprintf('%0.1f', $work_total[4] - $work_month_sum[3] );
			$work_total[10] = $work_total[8] + $work_total[9];	//평일근무일 + 휴일근무일

			$work_month_sum[1] = sprintf('%0.1f', $work_month_sum[1]);
			$work_month_sum[2] = sprintf('%0.1f', $work_month_sum[2]);
			$work_month_sum[3] = sprintf('%0.1f', $work_month_sum[3]);
			$work_month_sum[5] = sprintf('%0.1f',$work_month_sum[51]).' / '.$work_month_sum[52];

			$work_month_sum[6] = floor($work_month_sum[6]/60).':'.sprintf('%02d',($work_month_sum[6]%60));
			$work_month_sum[7] = floor($work_month_sum[7]).':00';

			/*
			$work_month_sum[11] = round($work_month_sum[1]/$i, 1);
			$work_month_sum[12] = round($work_month_sum[2]/$i, 1);
			$work_month_sum[13] = round($work_month_sum[3]/$i, 1);
			$work_month_sum[14] = round($work_month_sum[4]/$i, 1);
			$work_month_sum[15] = sprintf('%0.1f',$work_month_sum[51]/$i).' / '.sprintf('%0.1f',$work_month_sum[52]/$i);
			*/
			if($i == 0){
				$work_month_sum[16] = 0;
				$work_month_sum[17] = 0;
				$work_month_sum[18] = 0;
			}else{
				$work_month_sum[16] = $work_month_sum_6/$i;
				$work_month_sum[17] = $work_month_sum_7/$i;
				$work_month_sum[16] = floor($work_month_sum[16]/60).':'.sprintf('%02d',floor($work_month_sum[16]%60));
				$work_month_sum[17] = floor($work_month_sum[17]).':'.sprintf('%02d',$work_month_sum[17]*60%60);
				$work_month_sum[18] = round($work_month_sum_8/$i, 1);
			}
			//$work_month_sum[19] = round($work_month_sum[9]/$i, 1);

			//print_r($work_total);
			//print_r($work_month);

			$this->smarty->assign( 'work_total' , $work_total );
			$this->smarty->assign( 'work_month' , $work_month );
			$this->smarty->assign( 'work_month_sum' , $work_month_sum );

			//최근 5년간 근무내역
			$work_year = array();
			$azsql = "
				SELECT
					date_y
					, position
					, EntryDate
					, vacation1
					, vacation2
					, vacation
					, tardy
					, work_day
					/*
					, IF( date_format(EntryDate , '%Y') > date_y, '0.0', work_day) AS work_day
					, IF( date_format(EntryDate , '%Y') > date_y, '0.0', work_day_per) AS work_day_per
					, IF( date_format(EntryDate , '%Y') > date_y, '0.0', work_month) AS work_month
					*/
					, over_time_real
					, over_day_real
					, work_day_per
					, month_cnt
				FROM
					(
						SELECT
							date_y
							, vacation1
							, vacation2
							, ( SELECT EntryDate FROM member_tbl WHERE MemberNo = '".$memberno."' ) as EntryDate
							, ( B.vacation1 + B.vacation2 ) AS vacation
							, B.tardy
							, CONCAT( ROUND( A.date_d - ( A.week + A.holy + B.vacation1 + B.vacation2 ) + B.login_cnt, 1), ' / ', A.date_d - ( A.week + A.holy ) ) AS work_day
							/*
							, ( A.date_d - ( A.week + A.holy + B.vacation1 + B.vacation2 ) ) AS work_day
							*/
							, ROUND( ( A.date_d - ( A.week + A.holy + B.vacation1 + B.vacation2 ) + B.login_cnt ) / date_d * 100, 1 ) AS work_day_per
							/*
							, ( A.date_d - ( A.week + A.holy + B.vacation1 + B.vacation2 ) ) * 8 AS work_month
							*/
							, ROUND( B.over_day_real / B.month_cnt, 1 ) AS over_day_real
							, CONCAT(floor((B.over_time_real / B.month_cnt)/60), ':', LPAD( floor((B.over_time_real / B.month_cnt)%60), 2, '0')) as over_time_real
							, B.month_cnt
							, (select Name from systemconfig_tbl where Syskey in ( 'PositionCode' ) and Code = B.position) as position
						from (
							SELECT
								date_y
								, sum(date_d) as date_d
								, sum(week) as week
								, sum(holy) as holy
							FROM
								worker_date_tbl
							where
								date_y <= ".$set_year."
							group BY
								date_y
						) A
						, (
							SELECT
								date_y as date_y2
								, ROUND(sum(vacation1 / 8), 1) as vacation1
								, ROUND(sum(vacation2 / 8), 1) as vacation2
								, sum(tardy) as tardy
								, SUM(login_cnt) AS login_cnt
								, sum(
									CASE	WHEN CONCAT(date_y,LPAD(date_m, 2, 0)) = '".$EntryYM."' THEN 0
											WHEN CONCAT(date_y,LPAD(date_m, 2, 0)) = '".$set_date."' THEN 0
											ELSE over_time_real END
								) as over_time_real
								, sum(
									CASE	WHEN CONCAT(date_y,LPAD(date_m, 2, 0)) = '".$EntryYM."' THEN 0
											WHEN CONCAT(date_y,LPAD(date_m, 2, 0)) = '".$set_date."' THEN 0
											ELSE over_day_real END
								) as over_day_real
								, COUNT(date_y) - SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = '".$EntryYM."', 1, 0)) - SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = '".$set_date."', 1, 0)) AS month_cnt
								, ( select rank_code from worker_total_tbl where memberno = D.memberno and date_y = D.date_y and IF(date_y = YEAR( CURDATE() ), date_m = ".$max_month.", date_m = 12) )as position
							FROM
								worker_total_tbl D
							WHERE
								memberno = '".$memberno."'
								and CONCAT(date_y,LPAD(date_m, 2, 0)) >= '".$EntryYM."'
								and CONCAT(date_y,LPAD(date_m, 2, 0)) <= '".$set_date."'
							group BY
								date_y
						) B
						where
							A.date_y = B.date_y2
					) C
				order by C.date_y DESC
			;";
			//echo $azsql;

			$i = 0;
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				if($i < 5 and $row['date_y'] >= substr($row['EntryDate'], 0, 4)){
					$row['EntryDate'] = str_replace ( '-', '.', $row['EntryDate']).'.';
					$row['position_length'] = mb_strlen($row['position'], "UTF-8");
					if($row['month_cnt'] == 0){
						$row['work_month'] = 0;
					}else{
						$row['work_month'] = floor($row['work_month']/$row['month_cnt']).':'.sprintf('%02d',floor(($row['work_month']/$row['month_cnt']*60)%60));
					}
					array_push($work_year,$row);
					$i++;
				}
			}
			if(count($work_year) < 5){
				for($i; $i<5; $i++){
					array_push($work_year,array(''));
				}
			}
			//print_r($work_year);
			$this->smarty->assign( 'work_year' , $work_year );

			$this->smarty->display("planning_mng/insaReport/PersonReport_03_HTML_AJAX_01_mvc.tpl");
		}

		function REPORT_04(){
			extract($_REQUEST);
			switch($MainAction){
				case "HTML_Ajax_01": 	$this->REPORT_04_HTML_Ajax_01();	break;
				default:
					global $db;
					if($set_date == null or $set_date == ''){
						$set_date = date('Y');
					}
					$work_year = array();
					$azsql = "
						select
							date_y																				as item_1 /* 년도 */
							, GroupName																			as item_2 /* 부서 */
							, korName																			as item_3 /* 이름 */
							, memberno																			as item_4 /* 사번 */
							, position																			as item_5 /* 직급 */
							, EntryDate																			as item_6 /* 입사일 */
							, date_d																			as item_7 /* 전체일수 */
							, week																				as item_8 /* 토,일요일 */
							, holy																				as item_9 /* 공휴일 */
							, date_d - ( week + holy )															as item_10 /* 기준근무일 */
							, ROUND( vacation1/8 , 1)															as item_11 /* 사용연차 */
							, ROUND( vacation2/8 , 1)															as item_12 /* 기타휴가,교육 */
							, ROUND( vacation1/8 + vacation2/8 , 1)												as item_13 /* 소계 */
							, date_d - ( week + holy + ROUND( vacation1/8 + vacation2/8 , 1) )					as item_14 /* 평일근무일 */
							, login_cnt																			as item_15 /* 휴일근무일 */
							, date_d - ( week + holy + ROUND( vacation1/8 + vacation2/8 , 1) ) + login_cnt		as item_16 /* 총근무일 */
							, date_d - (
								week + holy + ROUND( vacation1/8 + vacation2/8 , 1)
								+ ifnull( (
									select sum(date_d) - sum(week) - sum(holy) from worker_date_tbl where date_y = ".$set_date." AND CONCAT(date_y,LPAD(date_m, 2, 0)) < date_format(EntryDate , '%Y%m')
								), 0)
							) + login_cnt									as item_17 /* 월근무합계 */
							, over_time_real2																								as item_18 /* 연장근무시간(실제) */
							, over_time_confirm																								as item_19 /* 연장근무시간(인정) */
							, if(
								month_cnt2 = 0
								, 0
								, ROUND(
									IF(
										date_format(EntryDate , '%Y') = ".$set_date."
										, over_day_real - (select over_day_real from worker_total_tbl where memberno = A.memberno and date_y = A.date_y and date_m = date_format(EntryDate , '%m'))
										,over_day_real
									)/month_cnt2
								, 1)
							)																									as item_20 /* 평균연장근무일수 */
							, ROUND( ( date_d - ( week + holy + ROUND( vacation1/8 + vacation2/8 , 1) ) + login_cnt ) / date_d * 100 , 1)	as item_21 /* 근무비율 */
							, case when LeaveDate = '0000-00-00' then '' when LeaveDate < date(now()) then 'o' else '' end as item_22 /* 퇴직 */
							, if(
								month_cnt2 = 0
								, 0
								, ROUND(
									IF(
										date_format(EntryDate , '%Y') = ".$set_date."
										, over_time_real - (select over_time_real from worker_total_tbl where memberno = A.memberno and date_y = A.date_y and date_m = date_format(EntryDate , '%m'))
										,over_time_real
									)/month_cnt2
								, 0)
							)													as item_23 /* 평균연장근무시간(실제) */
							, Company as item_24 /*  */
							, (date_d - ( week + holy + ROUND( vacation1/8 + vacation2/8 , 1) ) + login_cnt)/week_cnt as item_25 /* 근무일수/주 */
							, ( TIMESTAMPDIFF(MONTH, if( EntryDate < '".$set_date."-01-01' , '".$set_date."-01-01', EntryDate), '".($set_date+1)."-01-31') ) as item_26 /* 근무월 */
							, etc  as item_27 /* 기타 */
							, out_work as item_28 /* 출장일 */
							, LeaveDate as item_29 /* 퇴사일 */
						from
							(
								select
									*
									, ifnull( position1, ifnull( position2, position3 ) ) as position
									, IF( month_cnt < 0 , 0, month_cnt) as month_cnt2
								from
									(
										SELECT
											date_y
											, memberno
											, SUM(vacation1) AS vacation1
											, SUM(vacation2) AS vacation2
											, SUM(login_cnt) AS login_cnt
											, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_time_real)) AS over_time_real
											, sum(over_time_real) AS over_time_real2
											, sum(if(over_time_confirm > limit_time, limit_time, over_time_confirm)) AS over_time_confirm
											, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_day_real)) AS over_day_real
											, GROUP_CONCAT(etc SEPARATOR '&') AS etc
										FROM
											(
												select
													date_y
													, date_m
													, memberno
													, vacation1
													, vacation2
													, login_cnt
													, over_time_real
													, (floor((over_time_confirm+30)/60)*60) as over_time_confirm
													, ifnull((select SUBSTRING_INDEX(max_time, ':', 1) from overtime_basic_new_tbl C WHERE code between 10 AND 55 AND C.desc like concat('%', (SELECT IF(RealRankCode = '' , RankCode, RealRankCode) FROM member_tbl B WHERE B.MemberNo = A.memberno), '%'))*60, 0) as limit_time
													, over_day_real
													, etc
												from
													worker_total_tbl A
												WHERE
													date_y = ".$set_date."
											) CC
										GROUP BY
											date_y, memberno
									) AA

									left join

									(
										select * from
										(
											SELECT
												( SELECT Name FROM systemconfig_tbl WHERE SysKey = 'GroupCode' AND Code = GroupCode ) AS GroupName
												, korName
												, MemberNo as MemberNo2
												, ( select position from worker_position_tbl where memberno = Z.MemberNo and date_y = ".$set_date." ORDER BY date_y DESC LIMIT 1 ) as position1
												, ( SELECT position FROM member_promotion_tbl WHERE MemberNo = Z.MemberNo AND AnnounceDate < '".$set_date."-12-31' ORDER BY AnnounceDate desc LIMIT 1 ) as position2
												, ( select Name FROM systemconfig_tbl WHERE SysKey = 'PositionCode' AND Code = RankCode ) as position3
												, EntryDate
												, LeaveDate
												, 12 - IF(date_format(EntryDate , '%Y') = ".$set_date.", date_format(EntryDate , '%m'), 0) - IF(date_format(now() , '%Y') = ".$set_date.", 13-date_format(now() , '%m'), 0) AS month_cnt
												, Company
											FROM
												member_tbl Z
											WHERE
												date_format(EntryDate , '%Y') <= ".$set_date." or EntryDate = '0000-00-00'
										) ZZ
										left join
										(
											SELECT memberno as MemberNo3, sum( DATEDIFF(end_time, start_time) + 1 ) as out_work FROM userstate_tbl WHERE date_format(start_time , '%Y') = ".$set_date." AND state = 3 group by memberno
										)ZZZ
										on ZZ.MemberNo2 = ZZZ.MemberNo3

									) BB
									on AA.memberno = BB.MemberNo2

							) A
							, (
								SELECT
									date_y as date_y2
									, SUM(date_d) AS date_d
									, SUM(week) AS week
									, SUM(holy) AS holy
									, SUM(week_cnt) AS week_cnt
								FROM
									worker_date_tbl
								WHERE
									date_y = ".$set_date."
								GROUP BY
									date_y
							) B
						where
							A.date_y = B.date_y2
						order by
							A.LeaveDate, A.GroupName, A.position, A.korName
					;";
					//echo $azsql;

					$re = mysql_query($azsql,$db);
					while($row=mysql_fetch_array($re)){
						array_push($work_year,$row);
					}
					//print_r($work_year);
					//echo count($work_year);

					$this->smarty->assign( 'work_year' , $work_year );

					$this->smarty->display("planning_mng/insaReport/PersonReport_04_Main_mvc.tpl");
			}
		}

		function REPORT_05(){
			extract($_REQUEST);
			$this->smarty->assign( 'MainAction' , $MainAction );
			$this->smarty->assign( 'set_date' , $set_date );
			$this->smarty->assign( 'set_dept' , $set_dept );
			$this->smarty->assign( 'set_position' , $set_position );
			$this->smarty->assign( 'pdf' , $pdf );
			switch($MainAction){
				case "HTML_Ajax_01": 	$this->REPORT_05_HTML_Ajax_01();	break;	//월평균연장근무시간
				case "HTML_Ajax_02": 	$this->REPORT_05_HTML_Ajax_02();	break;	//월평균연장근무일수
				case "HTML_Ajax_03": 	$this->REPORT_05_HTML_Ajax_03();	break;	//연간근무비율
				case "HTML_Ajax_04": 	$this->REPORT_05_HTML_Ajax_04();	break;	//주평균연장근무시간
				default:
					global $db;
					$azsql = " select Code, Name from systemconfig_tbl where SysKey = 'GroupCode' order by orderno; ";
					//echo $azsql;
					$group_list = array();
					$re = mysql_query($azsql,$db);
					while($row=mysql_fetch_array($re)){
						array_push( $group_list, $row );
					}
					$this->smarty->assign( 'group_list' , $group_list );

					if(date('m') == 1){
						$max_year = date('Y')-1;
					}else{
						$max_year = date('Y');
					}


					if($set_year == null or $set_year == ''){
						if( date('m') < 4 ){
							$set_year = $max_year-1;
						}else{
							$set_year = $max_year;
						}
					}
					$this->smarty->assign( 'set_year' , $set_year );

					$YEAR_ARR = array();
					for($i=0; $i<2; $i++){
						array_push( $YEAR_ARR, $max_year-$i );
					}

					$this->smarty->assign( 'YEAR_ARR' , $YEAR_ARR );

					$azsql = " select Code, concat(Name, Note, ' - ', Code) as Name from systemconfig_tbl where SysKey = 'PositionCode' and orderno != 0 order by Code; ";
					//echo $azsql;
					$position_list = array();
					$re = mysql_query($azsql,$db);
					while($row=mysql_fetch_array($re)){
						array_push( $position_list, $row );
					}
					$this->smarty->assign( 'position_list' , $position_list );

					if( $test == '' ){
						$this->smarty->display("planning_mng/insaReport/PersonReport_05_Main_mvc.tpl");
					}else{
						$this->smarty->display("planning_mng/insaReport/PersonReport_05_Main_mvc_".$test.".tpl");
					}
			}
		}

		//월평균연장근무시간
		function REPORT_05_HTML_Ajax_01(){
			extract($_REQUEST);
			global $db;

			//	년도
			if($set_year == null or $set_year == ''){
				$set_year = date('Y');
			}
			$this->smarty->assign( 'set_year' , $set_year );
			if( $set_year == date('Y') ){
				$set_month = date('m')-1;
			}else{
				$set_month = 12;
			}
			$set_date = $set_year.sprintf('%02d',$set_month);
			//	부서코드
			if($set_dept == null or $set_dept == ''){
				$set_dept = 98;
			}
			$this->smarty->assign( 'set_dept' , $set_dept );
			//	직급
			if($set_position == null or $set_position == ''){
				$set_position = '%';
			}
			$this->smarty->assign( 'set_position' , $set_position );

			$azsql = " select Name from systemconfig_tbl where SysKey = 'GroupCode' and Code = '".sprintf('%02d',$set_dept)."'; ";
			//echo $azsql;
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				$this->smarty->assign( 'set_dept_name' , $row['Name'] );
			}

			if($set_position == '%'){
				$set_position_name = '전체';
				$this->smarty->assign( 'set_position_name' , $set_position_name );
			}else{
				$azsql = " select Name from systemconfig_tbl where SysKey = 'PositionCode' and Code = '".$set_position."'; ";
				$re = mysql_query($azsql,$db);
				while($row=mysql_fetch_array($re)){
					$set_position_name = $row['Name'];
					$this->smarty->assign( 'set_position_name' , $row['Name'] );
				}

				$azsql = " select max_time from overtime_basic_new_tbl A where A.desc like '% ".$set_position." %' order by code desc limit 1 ";
				//echo $azsql;
				$re = mysql_query($azsql,$db);
				while($row=mysql_fetch_array($re)){
					$this->smarty->assign( 'limit_time' , str_replace(":00", "", $row['max_time'])*60 );
				}
			}

			$work_position = array();
			$azsql = "
				select * from (
					select
						date_y																											as item_1
						, IF('%%%' = '".$set_dept."', '전체', ( SELECT Name FROM systemconfig_tbl WHERE SysKey = 'GroupCode' AND Code = '".$set_dept."' ) ) 					as item_2
						, korName																										as item_3
						, memberno																										as item_4
						, position																										as item_5
						, if(
							month_cnt2 = 0
							, 0
							, ROUND(
								IF(
									date_format(EntryDate , '%Y%m') = '".$set_date."'
									, over_time_real - (select over_time_real from worker_total_tbl where memberno = A.memberno and date_y = A.date_y and date_m = date_format(EntryDate , '%m'))
									, over_time_real
								)/month_cnt2
							, 0)
						)													as item_6
					from
						(
							select
								*
								, ( select Name from systemconfig_tbl where Syskey = 'PositionCode' and Code = AA.rank_code ) as position
								, IF( month_cnt < 0 , 0, month_cnt) as month_cnt2
							from
								(
									SELECT
										date_y
										, memberno
										, SUM(vacation1) AS vacation1
										, SUM(vacation2) AS vacation2
										, SUM(login_cnt) AS login_cnt
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_time_real)) AS over_time_real
										, sum(over_time_real) AS over_time_real2
										, sum(if(over_time_confirm > limit_time, limit_time, over_time_confirm)) AS over_time_confirm
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_day_real)) AS over_day_real
										, max(dept_code) as GroupCode
										, max(rank_code) as rank_code
									FROM
										(
											select
												date_y
												, date_m
												, memberno
												, vacation1
												, vacation2
												, login_cnt
												, over_time_real
												, (floor((over_time_confirm+30)/60)*60) as over_time_confirm
												, ifnull((select SUBSTRING_INDEX(max_time, ':', 1) from overtime_basic_new_tbl C WHERE code between 10 AND 55 AND C.desc like concat('%', (SELECT IF(RealRankCode = '' , RankCode, RealRankCode) FROM member_tbl B WHERE B.MemberNo = A.memberno), '%'))*60, 0) as limit_time
												, over_day_real
												, ( CASE A.date_m WHEN ".$set_month." THEN dept_code ELSE '' END ) as dept_code
												, ( CASE A.date_m WHEN ".$set_month." THEN rank_code ELSE '' END ) as rank_code
											from
												worker_total_tbl A
											WHERE
												date_y = ".$set_year."
												and A.memberno in ( select memberno from worker_total_tbl B where B.date_y = ".$set_year." and B.date_m = ".$set_month." and B.dept_code like '".$set_dept."' )
										) CC
									GROUP BY
										date_y, memberno
								) AA
								, (
									SELECT
										korName
										, MemberNo as MemberNo2
										, EntryDate
										, LeaveDate
										, IF(date_format(EntryDate , '%Y') = '".$set_year."', ".$set_month."-date_format(EntryDate , '%m'), ".$set_month.") AS month_cnt
									FROM
										member_tbl Z
									WHERE
										date_format(EntryDate , '%Y%m') <= ".$set_date."
								) BB
							where AA.memberno = BB.MemberNo2
						) A
					where
						IF('%' = '".$set_position."', 1=1, position = '".$set_position_name."')
				) Z
				order by
					Z.item_6 desc, Z.item_3
			;";
			//echo $azsql;

			$total_sum = 0;
			$max_time = 0;
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				$total_sum += $row['item_6'];
				if( $max_time < $row['item_6'] ){
					$max_time = $row['item_6'];
				}

				if(mb_strlen($row['item_3'], 'utf-8') < 3){
					$row['item_3'] = iconv_substr($row['item_3'], 0, 1, 'utf-8').' '.iconv_substr($row['item_3'], 1, 1, 'utf-8');
				}
				if( $row['item_5'] == '책임연구원' ){
					$row['item_5'] = '책임';
				}elseif( $row['item_5'] == '수석연구원' ){
					$row['item_5'] = '수석';
				}elseif( $row['item_5'] == '선임연구원' ){
					$row['item_5'] = '선임';
				}
				if(mb_strlen($row['item_5'], 'utf-8') < 3){
					$row['item_5'] = iconv_substr($row['item_5'], 0, 1, 'utf-8').' '.iconv_substr($row['item_5'], 1, 1, 'utf-8');
				}

				$member_name = $row['item_3'];
				for( $i = mb_strlen($row['item_3'], 'utf-8'); $i<4; $i++ ){
					$member_name .= ' ';
				}
				$member_name .= $row['item_5'];

				array_push($work_position, array(
					"member" => $member_name
					, "overtime" => $row['item_6']
					, "overtime_view" => floor($row['item_6']/60).':'.sprintf('%02d',($row['item_6']%60))
				));
			}
			//echo $total_sum;
			$total_cnt = mysql_num_rows($re);
			//echo $total_cnt;
			$total_avg = 0;
			if($total_cnt > 0){
				$total_avg = round($total_sum / $total_cnt, 2);
			}
			$this->smarty->assign( 'total_avg' , $total_avg );
			$this->smarty->assign( 'max_time' , $max_time );

			$this->smarty->assign( 'work_position' , json_encode($work_position) );
			$this->smarty->display("planning_mng/insaReport/PersonReport_05_HTML_AJAX_01_mvc.tpl");
		}

		//월평균연장근무일수
		function REPORT_05_HTML_Ajax_02(){
			extract($_REQUEST);
			global $db;

			//	년도
			if($set_year == null or $set_year == ''){
				$set_year = date('Y')-1;
			}
			$this->smarty->assign( 'set_year' , $set_year );
			if( $set_year == date('Y') ){
				$set_month = date('m')-1;
			}else{
				$set_month = 12;
			}
			$set_date = $set_year.str_pad($set_month, 2 , "0", STR_PAD_LEFT);
			//	부서코드
			if($set_dept == null or $set_dept == ''){
				$set_dept = 98;
			}
			$this->smarty->assign( 'set_dept' , $set_dept );
			//	직급
			if($set_position == null or $set_position == ''){
				$set_position = '%';
			}
			$this->smarty->assign( 'set_position' , $set_position );

			$azsql = " select Name from systemconfig_tbl where SysKey = 'GroupCode' and Code = '".sprintf('%02d',$set_dept)."'; ";
			//echo $azsql;
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				$this->smarty->assign( 'set_dept_name' , $row['Name'] );
			}

			if($set_position == '%'){
				$set_position_name = '전체';
				$this->smarty->assign( 'set_position_name' , $set_position_name );
			}else{
				$azsql = " select Name from systemconfig_tbl where SysKey = 'PositionCode' and Code = '".$set_position."'; ";
				$re = mysql_query($azsql,$db);
				while($row=mysql_fetch_array($re)){
					$set_position_name = $row['Name'];
					$this->smarty->assign( 'set_position_name' , $row['Name'] );
				}
			}

			$work_position = array();
			$azsql = "
				select * from (
					select
						date_y																											as item_1
						, IF('%%%' = '".$set_dept."', '전체', ( SELECT Name FROM systemconfig_tbl WHERE SysKey = 'GroupCode' AND Code = '".$set_dept."' ) ) 					as item_2
						, korName																										as item_3
						, memberno																										as item_4
						, position																										as item_5
						, if(
							month_cnt2 = 0
							, 0
							, ROUND(
								IF(
									date_format(EntryDate , '%Y%m') = '".$set_date."'
									, over_day_real - (select over_day_real from worker_total_tbl where memberno = A.memberno and date_y = A.date_y and date_m = date_format(EntryDate , '%m'))
									, over_day_real
								)/month_cnt2
							, 0)
						)													as item_6
					from
						(
							select
								*
								, ( select Name from systemconfig_tbl where Syskey = 'PositionCode' and Code = AA.rank_code ) as position
								, IF( month_cnt < 0 , 0, month_cnt) as month_cnt2
							from
								(
									SELECT
										date_y
										, memberno
										, SUM(vacation1) AS vacation1
										, SUM(vacation2) AS vacation2
										, SUM(login_cnt) AS login_cnt
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_time_real)) AS over_time_real
										, sum(over_time_real) AS over_time_real2
										, sum(if(over_time_confirm > limit_time, limit_time, over_time_confirm)) AS over_time_confirm
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_day_real)) AS over_day_real
										, max(dept_code) as GroupCode
										, max(rank_code) as rank_code
									FROM
										(
											select
												date_y
												, date_m
												, memberno
												, vacation1
												, vacation2
												, login_cnt
												, over_time_real
												, (floor((over_time_confirm+30)/60)*60) as over_time_confirm
												, ifnull((select SUBSTRING_INDEX(max_time, ':', 1) from overtime_basic_new_tbl C WHERE code between 10 AND 55 AND C.desc like concat('%', (SELECT IF(RealRankCode = '' , RankCode, RealRankCode) FROM member_tbl B WHERE B.MemberNo = A.memberno), '%'))*60, 0) as limit_time
												, over_day_real
												, ( CASE A.date_m WHEN ".$set_month." THEN dept_code ELSE '' END ) as dept_code
												, ( CASE A.date_m WHEN ".$set_month." THEN rank_code ELSE '' END ) as rank_code
											from
												worker_total_tbl A
											WHERE
												date_y = ".$set_year."
												and A.memberno in ( select memberno from worker_total_tbl B where B.date_y = ".$set_year." and B.date_m = ".$set_month." and B.dept_code like '".$set_dept."' )
										) CC
									GROUP BY
										date_y, memberno
								) AA
								, (
									SELECT
										korName
										, MemberNo as MemberNo2
										, EntryDate
										, LeaveDate
										, IF(date_format(EntryDate , '%Y') = '".$set_year."', ".$set_month."-date_format(EntryDate , '%m'), ".$set_month.") AS month_cnt
									FROM
										member_tbl Z
									WHERE
										date_format(EntryDate , '%Y%m') <= ".$set_date."
								) BB
							where AA.memberno = BB.MemberNo2
						) A
					where
						IF('%' = '".$set_position."', 1=1, position = '".$set_position_name."')
				) Z
				order by
					Z.item_6 desc, Z.item_3
			;";
			//echo $azsql;

			$total_sum = 0;
			$max_time = 0;
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				$total_sum += $row['item_6'];
				if( $max_time < $row['item_6'] ){
					$max_time = $row['item_6'];
				}

				if(mb_strlen($row['item_3'], 'utf-8') < 3){
					$row['item_3'] = iconv_substr($row['item_3'], 0, 1, 'utf-8').' '.iconv_substr($row['item_3'], 1, 1, 'utf-8');
				}
				if( $row['item_5'] == '책임연구원' ){
					$row['item_5'] = '책임';
				}elseif( $row['item_5'] == '수석연구원' ){
					$row['item_5'] = '수석';
				}elseif( $row['item_5'] == '선임연구원' ){
					$row['item_5'] = '선임';
				}
				if(mb_strlen($row['item_5'], 'utf-8') < 3){
					$row['item_5'] = iconv_substr($row['item_5'], 0, 1, 'utf-8').' '.iconv_substr($row['item_5'], 1, 1, 'utf-8');
				}

				$member_name = $row['item_3'];
				for( $i = mb_strlen($row['item_3'], 'utf-8'); $i<4; $i++ ){
					$member_name .= ' ';
				}
				$member_name .= $row['item_5'];

				array_push($work_position, array(
					"member" => $member_name
					, "overtime" => $row['item_6']
					, "overtime_view" => floor($row['item_6']/60).':'.sprintf('%02d',($row['item_6']%60))
				));
			}
			//echo $total_sum;
			$total_cnt = mysql_num_rows($re);
			//echo $total_cnt;
			$total_avg = 0;
			if($total_cnt > 0){
				$total_avg = round($total_sum / $total_cnt, 2);
			}
			$this->smarty->assign( 'total_avg' , $total_avg );
			$this->smarty->assign( 'max_time' , $max_time );

			$this->smarty->assign( 'work_position' , json_encode($work_position) );
			$this->smarty->display("planning_mng/insaReport/PersonReport_05_HTML_AJAX_02_mvc.tpl");
		}

		//연간근무비율
		function REPORT_05_HTML_Ajax_03(){
			extract($_REQUEST);
			global $db;

			//	년도
			if($set_year == null or $set_year == ''){
				$set_year = date('Y')-1;
			}
			$this->smarty->assign( 'set_year' , $set_year );
			if( $set_year == date('Y') ){
				$set_month = date('m')-1;
			}else{
				$set_month = 12;
			}
			$set_date = $set_year.str_pad($set_month, 2 , "0", STR_PAD_LEFT);
			//	부서코드
			if($set_dept == null or $set_dept == ''){
				$set_dept = 98;
			}
			$this->smarty->assign( 'set_dept' , $set_dept );
			//	직급
			if($set_position == null or $set_position == ''){
				$set_position = '%';
			}
			$this->smarty->assign( 'set_position' , $set_position );

			$azsql = " select Name from systemconfig_tbl where SysKey = 'GroupCode' and Code = '".sprintf('%02d',$set_dept)."'; ";
			//echo $azsql;
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				$this->smarty->assign( 'set_dept_name' , $row['Name'] );
			}

			if($set_position == '%'){
				$set_position_name = '전체';
				$this->smarty->assign( 'set_position_name' , $set_position_name );
			}else{
				$azsql = " select Name from systemconfig_tbl where SysKey = 'PositionCode' and Code = '".$set_position."'; ";
				$re = mysql_query($azsql,$db);
				while($row=mysql_fetch_array($re)){
					$set_position_name = $row['Name'];
					$this->smarty->assign( 'set_position_name' , $row['Name'] );
				}
			}

			$work_position = array();
			$azsql = "
				select * from (
					select
						date_y																											as item_1
						, IF('%%%' = '".$set_dept."', '전체', ( SELECT Name FROM systemconfig_tbl WHERE SysKey = 'GroupCode' AND Code = '".$set_dept."' ) ) 					as item_2
						, korName																										as item_3
						, memberno																										as item_4
						, position																										as item_5
						, ROUND( ( date_d - ( week + holy + ROUND( vacation1/8 + vacation2/8 , 1) ) + login_cnt ) / date_d * 100 , 1)	as item_6
					from
						(
							select
								*
								, ( select Name from systemconfig_tbl where Syskey = 'PositionCode' and Code = AA.rank_code ) as position
								, IF( month_cnt < 0 , 0, month_cnt) as month_cnt2
							from
								(
									SELECT
										date_y
										, memberno
										, SUM(vacation1) AS vacation1
										, SUM(vacation2) AS vacation2
										, SUM(login_cnt) AS login_cnt
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_time_real)) AS over_time_real
										, sum(over_time_real) AS over_time_real2
										, sum(if(over_time_confirm > limit_time, limit_time, over_time_confirm)) AS over_time_confirm
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_day_real)) AS over_day_real
										, max(dept_code) as GroupCode
										, max(rank_code) as rank_code
									FROM
										(
											select
												date_y
												, date_m
												, memberno
												, vacation1
												, vacation2
												, login_cnt
												, over_time_real
												, (floor((over_time_confirm+30)/60)*60) as over_time_confirm
												, ifnull((select SUBSTRING_INDEX(max_time, ':', 1) from overtime_basic_new_tbl C WHERE code between 10 AND 55 AND C.desc like concat('%', (SELECT IF(RealRankCode = '' , RankCode, RealRankCode) FROM member_tbl B WHERE B.MemberNo = A.memberno), '%'))*60, 0) as limit_time
												, over_day_real
												, ( CASE A.date_m WHEN ".$set_month." THEN dept_code ELSE '' END ) as dept_code
												, ( CASE A.date_m WHEN ".$set_month." THEN rank_code ELSE '' END ) as rank_code
											from
												worker_total_tbl A
											WHERE
												date_y = ".$set_year."
												and A.memberno in ( select memberno from worker_total_tbl B where B.date_y = ".$set_year." and B.date_m = ".$set_month." and B.dept_code like '".$set_dept."' )
										) CC
									GROUP BY
										date_y, memberno
								) AA
								, (
									SELECT
										korName
										, MemberNo as MemberNo2
										, EntryDate
										, LeaveDate
										, IF(date_format(EntryDate , '%Y') = '".$set_year."', ".$set_month."-date_format(EntryDate , '%m'), ".$set_month.") AS month_cnt
									FROM
										member_tbl Z
									WHERE
										date_format(EntryDate , '%Ym') <= ".$set_date."
								) BB
							where AA.memberno = BB.MemberNo2
						) A
						, (
							SELECT
								date_y as date_y2
								, SUM(date_d) AS date_d
								, SUM(week) AS week
								, SUM(holy) AS holy
							FROM
								worker_date_tbl
							WHERE
								date_y = ".$set_year."
							GROUP BY
								date_y
						) B
					where
						A.date_y = B.date_y2
						and IF('%' = '".$set_position."', 1=1, position = '".$set_position_name."')
				) Z
				order by
					Z.item_6 desc, Z.item_3
			;";
			//echo $azsql;

			$total_sum = 0;
			$max_time = 0;
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				$total_sum += $row['item_6'];
				if( $max_time < $row['item_6'] ){
					$max_time = $row['item_6'];
				}

				if(mb_strlen($row['item_3'], 'utf-8') < 3){
					$row['item_3'] = iconv_substr($row['item_3'], 0, 1, 'utf-8').' '.iconv_substr($row['item_3'], 1, 1, 'utf-8');
				}
				if( $row['item_5'] == '책임연구원' ){
					$row['item_5'] = '책임';
				}elseif( $row['item_5'] == '수석연구원' ){
					$row['item_5'] = '수석';
				}elseif( $row['item_5'] == '선임연구원' ){
					$row['item_5'] = '선임';
				}
				if(mb_strlen($row['item_5'], 'utf-8') < 3){
					$row['item_5'] = iconv_substr($row['item_5'], 0, 1, 'utf-8').' '.iconv_substr($row['item_5'], 1, 1, 'utf-8');
				}

				$member_name = $row['item_3'];
				for( $i = mb_strlen($row['item_3'], 'utf-8'); $i<4; $i++ ){
					$member_name .= ' ';
				}
				$member_name .= $row['item_5'];

				array_push($work_position, array(
					"member" => $member_name
					, "overtime" => $row['item_6']
					, "overtime_view" => floor($row['item_6']/60).':'.sprintf('%02d',($row['item_6']%60))
				));
			}
			//echo $total_sum;
			$total_cnt = mysql_num_rows($re);
			//echo $total_cnt;
			$total_avg = 0;
			if($total_cnt > 0){
				$total_avg = round($total_sum / $total_cnt, 2);
			}
			$this->smarty->assign( 'total_avg' , $total_avg );
			$this->smarty->assign( 'max_time' , $max_time );

			$this->smarty->assign( 'work_position' , json_encode($work_position) );
			$this->smarty->display("planning_mng/insaReport/PersonReport_05_HTML_AJAX_03_mvc.tpl");
		}

		//주평균연장근무시간
		function REPORT_05_HTML_Ajax_04(){
			extract($_REQUEST);
			global $db;

			//	년도
			if($set_year == null or $set_year == ''){
				$set_year = date('Y');
			}
			$this->smarty->assign( 'set_year' , $set_year );
			if( $set_year == date('Y') ){
				$set_month = date('m')-1;
			}else{
				$set_month = 12;
			}
			$set_date = $set_year.sprintf('%02d',$set_month);
			//	부서코드
			if($set_dept == null or $set_dept == ''){
				$set_dept = 98;
			}
			$this->smarty->assign( 'set_dept' , $set_dept );
			//	직급
			if($set_position == null or $set_position == ''){
				$set_position = '%';
			}
			$this->smarty->assign( 'set_position' , $set_position );

			$set_date_last = $set_year.'-'.sprintf('%02d',$set_month).'-'.date('t', strtotime($set_date."01"));
			//echo $set_date_last;

			$azsql = " select Name from systemconfig_tbl where SysKey = 'GroupCode' and Code = '".sprintf('%02d',$set_dept)."'; ";
			//echo $azsql;
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				$this->smarty->assign( 'set_dept_name' , $row['Name'] );
			}

			if($set_position == '%'){
				$set_position_name = '전체';
				$this->smarty->assign( 'set_position_name' , $set_position_name );
			}else{
				$azsql = " select Name from systemconfig_tbl where SysKey = 'PositionCode' and Code = '".$set_position."'; ";
				$re = mysql_query($azsql,$db);
				while($row=mysql_fetch_array($re)){
					$set_position_name = $row['Name'];
					$this->smarty->assign( 'set_position_name' , $row['Name'] );
				}

				$azsql = " select max_time from overtime_basic_new_tbl A where A.desc like '% ".$set_position." %' order by code desc limit 1 ";
				//echo $azsql;
				$re = mysql_query($azsql,$db);
				while($row=mysql_fetch_array($re)){
					$this->smarty->assign( 'limit_time' , str_replace(":00", "", $row['max_time'])*60 );
				}
			}

			$work_position = array();
			$azsql = "
				select * from (
					select
						date_y																											as item_1
						, IF('%%%' = '".$set_dept."', '전체', ( SELECT Name FROM systemconfig_tbl WHERE SysKey = 'GroupCode' AND Code = '".$set_dept."' ) ) 					as item_2
						, korName																										as item_3
						, memberno																										as item_4
						, position																										as item_5
						, if(
							month_cnt2 = 0
							, 0
							, ROUND(
								IF(
									date_format(EntryDate , '%Y%m') = '".$set_date."'
									, over_time_real - (select over_time_real from worker_total_tbl where memberno = A.memberno and date_y = A.date_y and date_m = date_format(EntryDate , '%m'))
									, over_time_real
								)/month_cnt2
							, 0)
						)													as item_6
					from
						(
							select
								*
								, ( select Name from systemconfig_tbl where Syskey = 'PositionCode' and Code = AA.rank_code ) as position
								, IF( week_cnt < 0 , 0, week_cnt) as month_cnt2
							from
								(
									SELECT
										date_y
										, memberno
										, SUM(vacation1) AS vacation1
										, SUM(vacation2) AS vacation2
										, SUM(login_cnt) AS login_cnt
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_time_real)) AS over_time_real
										, sum(over_time_real) AS over_time_real2
										, sum(if(over_time_confirm > limit_time, limit_time, over_time_confirm)) AS over_time_confirm
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_day_real)) AS over_day_real
										, max(dept_code) as GroupCode
										, max(rank_code) as rank_code
									FROM
										(
											select
												date_y
												, date_m
												, memberno
												, vacation1
												, vacation2
												, login_cnt
												, over_time_real
												, (floor((over_time_confirm+30)/60)*60) as over_time_confirm
												, ifnull((select SUBSTRING_INDEX(max_time, ':', 1) from overtime_basic_new_tbl C WHERE code between 10 AND 55 AND C.desc like concat('%', (SELECT IF(RealRankCode = '' , RankCode, RealRankCode) FROM member_tbl B WHERE B.MemberNo = A.memberno), '%'))*60, 0) as limit_time
												, over_day_real
												, ( CASE A.date_m WHEN ".$set_month." THEN dept_code ELSE '' END ) as dept_code
												, ( CASE A.date_m WHEN ".$set_month." THEN rank_code ELSE '' END ) as rank_code
											from
												worker_total_tbl A
											WHERE
												date_y = ".$set_year."
												and A.memberno in ( select memberno from worker_total_tbl B where B.date_y = ".$set_year." and B.date_m = ".$set_month." and B.dept_code like '".$set_dept."' )
										) CC
									GROUP BY
										date_y, memberno
								) AA
								, (
									SELECT
										max(korName) as korName
										, MemberNo as MemberNo2
										, max(EntryDate) as EntryDate
										, max(LeaveDate) as LeaveDate
										, sum(week) AS week_cnt
									FROM
										member_tbl Z
										, (
											SELECT C.date_ymd, YEAR(C.date_ymd) AS date_y, MONTH(C.date_ymd) AS date_m, 1 AS week
											FROM (
												SELECT DATE('".$set_date_last."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
												SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS a
												CROSS JOIN (
												SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS b
												CROSS JOIN (
												SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS c
											) AS C
											WHERE
												C.date_ymd BETWEEN '".$set_year."-01-01 00:00:00' AND '".$set_date_last." 23:59:59'
												and DAYOFWEEK( C.date_ymd ) = 5
										) W
									WHERE
										date_format(EntryDate , '%Y%m') <= '".$set_year.$set_month."'
										and date_format(EntryDate , '%Y%m') < date_format(W.date_ymd , '%Y%m')
									group by MemberNo
									order by EntryDate desc
								) BB
							where AA.memberno = BB.MemberNo2
						) A
					where
						IF('%' = '".$set_position."', 1=1, position = '".$set_position_name."')
				) Z
				order by
					Z.item_6 desc, Z.item_3
			;";
			//echo $azsql;

			$total_sum = 0;
			$max_time = 0;
			$re = mysql_query($azsql,$db);
			while($row=mysql_fetch_array($re)){
				$total_sum += $row['item_6'];
				if( $max_time < $row['item_6'] ){
					$max_time = $row['item_6'];
				}

				if(mb_strlen($row['item_3'], 'utf-8') < 3){
					$row['item_3'] = iconv_substr($row['item_3'], 0, 1, 'utf-8').' '.iconv_substr($row['item_3'], 1, 1, 'utf-8');
				}
				if( $row['item_5'] == '책임연구원' ){
					$row['item_5'] = '책임';
				}elseif( $row['item_5'] == '수석연구원' ){
					$row['item_5'] = '수석';
				}elseif( $row['item_5'] == '선임연구원' ){
					$row['item_5'] = '선임';
				}
				if(mb_strlen($row['item_5'], 'utf-8') < 3){
					$row['item_5'] = iconv_substr($row['item_5'], 0, 1, 'utf-8').' '.iconv_substr($row['item_5'], 1, 1, 'utf-8');
				}

				$member_name = $row['item_3'];
				for( $i = mb_strlen($row['item_3'], 'utf-8'); $i<4; $i++ ){
					$member_name .= ' ';
				}
				$member_name .= $row['item_5'];

				array_push($work_position, array(
					"member" => $member_name
					, "overtime" => $row['item_6']
					, "overtime_view" => floor($row['item_6']/60).':'.sprintf('%02d',($row['item_6']%60))
				));
			}
			//echo $total_sum;
			$total_cnt = mysql_num_rows($re);
			//echo $total_cnt;
			$total_avg = 0;
			if($total_cnt > 0){
				$total_avg = round($total_sum / $total_cnt, 2);
			}
			$this->smarty->assign( 'total_avg' , $total_avg );
			$this->smarty->assign( 'max_time' , $max_time );

			$this->smarty->assign( 'work_position' , json_encode($work_position) );
			$this->smarty->display("planning_mng/insaReport/PersonReport_05_HTML_AJAX_04_mvc.tpl");
		}


////
		function REPORT_05_1(){
			extract($_REQUEST);
			$this->smarty->assign( 'MainAction' , $MainAction );
			$this->smarty->assign( 'set_date' , $set_date );
			$this->smarty->assign( 'set_dept' , $set_dept );
			$this->smarty->assign( 'set_position' , $set_position );
			$this->smarty->assign( 'pdf' , $pdf );
			$this->smarty->assign( 'SET_COMPANY' , $SET_COMPANY );
			switch($MainAction){
				case "HTML_Ajax_01": 	$this->REPORT_05_1HTML_Ajax_01();	break;	//월평균연장근무시간
				case "HTML_Ajax_02": 	$this->REPORT_05_1HTML_Ajax_02();	break;	//월평균연장근무일수
				case "HTML_Ajax_03": 	$this->REPORT_05_1HTML_Ajax_03();	break;	//연간근무비율
				case "HTML_Ajax_04": 	$this->REPORT_05_1HTML_Ajax_04();	break;	//주평균연장근무시간
				default:
					global $db;
					$azsql = " select Code, Name from systemconfig_tbl where SysKey = 'GroupCode' order by orderno; ";
					//echo $azsql;
					$group_list = array();
					$re = mysql_query($azsql,$db);
					while($row=mysql_fetch_array($re)){
						array_push( $group_list, $row );
					}
					$this->smarty->assign( 'group_list' , $group_list );

					if(date('m') == 1){
						$max_year = date('Y')-1;
					}else{
						$max_year = date('Y');
					}


					if($set_year == null or $set_year == ''){
						if( date('m') < 4 ){
							$set_year = $max_year-1;
						}else{
							$set_year = $max_year;
						}
					}
					$this->smarty->assign( 'set_year' , $set_year );

					$YEAR_ARR = array();
					for($i=0; $i<2; $i++){
						array_push( $YEAR_ARR, $max_year-$i );
					}

					$this->smarty->assign( 'YEAR_ARR' , $YEAR_ARR );

					$azsql = " select Code, concat(Name, Note, ' - ', Code) as Name from systemconfig_tbl where SysKey = 'PositionCode' and orderno != 0 order by Code; ";
					//echo $azsql;
					$position_list = array();
					$re = mysql_query($azsql,$db);
					while($row=mysql_fetch_array($re)){
						array_push( $position_list, $row );
					}
					$this->smarty->assign( 'position_list' , $position_list );

					if( $test == '' ){
						$this->smarty->display("planning_mng/insaReport/PersonReport_05_Main_mvc.tpl");
					}else{
						$this->smarty->display("planning_mng/insaReport/PersonReport_05_Main_mvc_".$test.".tpl");
					}
			}
		}

		//월평균연장근무시간
		function REPORT_05_1HTML_Ajax_01(){
			extract($_REQUEST);
			global $db_jang;
			global $db_ptc;
			global $db;

			//	년도
			if($set_year == null or $set_year == ''){
				$set_year = date('Y');
			}
			$this->smarty->assign( 'set_year' , $set_year );
			if( $set_year == date('Y') ){
				$set_month = date('m')-1;
			}else{
				$set_month = 12;
			}
			$set_date = $set_year.sprintf('%02d',$set_month);
			//	부서코드
			if($set_dept == null or $set_dept == ''){
				$set_dept = 98;
			}
			$this->smarty->assign( 'set_dept' , $set_dept );
			//	직급
			if($set_position == null or $set_position == ''){
				$set_position = '%';
			}
			$this->smarty->assign( 'set_position' , $set_position );

			$azsql = " select Name from systemconfig_tbl where SysKey = 'GroupCode' and Code = '".sprintf('%02d',$set_dept)."'; ";
			//echo $azsql;
			if( $COMPANY == 'JANG' ){
				$re = mysql_query($azsql,$db_jang);
			}elseif( $COMPANY == 'PTC' ){
				$re = mysql_query($azsql,$db_ptc);
			}else{
				$re = mysql_query($azsql,$db);
			}
			while($row=mysql_fetch_array($re)){
				$this->smarty->assign( 'set_dept_name' , $row['Name'] );
			}

			if($set_position == '%'){
				$set_position_name = '전체';
				$this->smarty->assign( 'set_position_name' , $set_position_name );
			}else{
				$azsql = " select Name from systemconfig_tbl where SysKey = 'PositionCode' and Code = '".$set_position."'; ";
				$re = mysql_query($azsql,$db);
				while($row=mysql_fetch_array($re)){
					$set_position_name = $row['Name'];
					$this->smarty->assign( 'set_position_name' , $row['Name'] );
				}

				$azsql = " select max_time from overtime_basic_new_tbl A where A.desc like '% ".$set_position." %' order by code desc limit 1 ";
				//echo $azsql;
				$re = mysql_query($azsql,$db);
				while($row=mysql_fetch_array($re)){
					$this->smarty->assign( 'limit_time' , str_replace(":00", "", $row['max_time'])*60 );
				}
			}

			$work_position = array();
			$azsql = "
				select * from (
					select
						date_y																											as item_1
						, IF('%%%' = '".$set_dept."', '전체', ( SELECT Name FROM systemconfig_tbl WHERE SysKey = 'GroupCode' AND Code = '".$set_dept."' ) ) 					as item_2
						, korName																										as item_3
						, memberno																										as item_4
						, position																										as item_5
						, if(
							month_cnt2 = 0
							, 0
							, ROUND(
								IF(
									date_format(EntryDate , '%Y%m') = '".$set_date."'
									, over_time_real - (select over_time_real from worker_total_tbl where memberno = A.memberno and date_y = A.date_y and date_m = date_format(EntryDate , '%m'))
									, over_time_real
								)/month_cnt2
							, 0)
						)													as item_6
					from
						(
							select
								*
								, ( select Name from systemconfig_tbl where Syskey = 'PositionCode' and Code = AA.rank_code ) as position
								, IF( month_cnt < 0 , 0, month_cnt) as month_cnt2
							from
								(
									SELECT
										date_y
										, memberno
										, SUM(vacation1) AS vacation1
										, SUM(vacation2) AS vacation2
										, SUM(login_cnt) AS login_cnt
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_time_real)) AS over_time_real
										, sum(over_time_real) AS over_time_real2
										, sum(if(over_time_confirm > limit_time, limit_time, over_time_confirm)) AS over_time_confirm
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_day_real)) AS over_day_real
										, max(dept_code) as GroupCode
										, max(rank_code) as rank_code
									FROM
										(
											select
												date_y
												, date_m
												, memberno
												, vacation1
												, vacation2
												, login_cnt
												, over_time_real
												, (floor((over_time_confirm+30)/60)*60) as over_time_confirm
												, ifnull((select SUBSTRING_INDEX(max_time, ':', 1) from overtime_basic_new_tbl C WHERE code between 102 AND 105 AND C.rankcode like concat('%-', (SELECT RankCode FROM member_tbl B WHERE B.MemberNo = A.memberno), '-%'))*60, 0) as limit_time
												, over_day_real
												, ( CASE A.date_m WHEN ".$set_month." THEN dept_code ELSE '' END ) as dept_code
												, ( CASE A.date_m WHEN ".$set_month." THEN rank_code ELSE '' END ) as rank_code
											from
												worker_total_tbl A
											WHERE
												date_y = ".$set_year."
												and A.memberno in ( select memberno from worker_total_tbl B where B.date_y = ".$set_year." and B.date_m = ".$set_month." and B.dept_code like '".$set_dept."' )
										) CC
									GROUP BY
										date_y, memberno
								) AA
								, (
									SELECT
										korName
										, MemberNo as MemberNo2
										, EntryDate
										, LeaveDate
										, IF(date_format(EntryDate , '%Y') = '".$set_year."', ".$set_month."-date_format(EntryDate , '%m'), ".$set_month.") AS month_cnt
									FROM
										member_tbl Z
									WHERE
										date_format(EntryDate , '%Y%m') <= ".$set_date."
								) BB
							where AA.memberno = BB.MemberNo2
						) A
					where
						IF('%' = '".$set_position."', 1=1, position = '".$set_position_name."')
				) Z
				order by
					Z.item_6 desc, Z.item_3
			;";
			//echo $azsql;

			$total_sum = 0;
			$max_time = 0;
			if( $COMPANY == 'JANG' ){
				$re = mysql_query($azsql,$db_jang);
			}elseif( $COMPANY == 'PTC' ){
				$re = mysql_query($azsql,$db_ptc);
			}else{
				$re = mysql_query($azsql,$db);
			}
			while($row=mysql_fetch_array($re)){
				$total_sum += $row['item_6'];
				if( $max_time < $row['item_6'] ){
					$max_time = $row['item_6'];
				}

				if(mb_strlen($row['item_3'], 'utf-8') < 3){
					$row['item_3'] = iconv_substr($row['item_3'], 0, 1, 'utf-8').' '.iconv_substr($row['item_3'], 1, 1, 'utf-8');
				}
				if( $row['item_5'] == '책임연구원' ){
					$row['item_5'] = '책임';
				}elseif( $row['item_5'] == '수석연구원' ){
					$row['item_5'] = '수석';
				}elseif( $row['item_5'] == '선임연구원' ){
					$row['item_5'] = '선임';
				}
				if(mb_strlen($row['item_5'], 'utf-8') < 3){
					$row['item_5'] = iconv_substr($row['item_5'], 0, 1, 'utf-8').' '.iconv_substr($row['item_5'], 1, 1, 'utf-8');
				}

				$member_name = $row['item_3'];
				for( $i = mb_strlen($row['item_3'], 'utf-8'); $i<4; $i++ ){
					$member_name .= ' ';
				}
				$member_name .= $row['item_5'];

				array_push($work_position, array(
					"member" => $member_name
					, "overtime" => $row['item_6']
					, "overtime_view" => floor($row['item_6']/60).':'.sprintf('%02d',($row['item_6']%60))
				));
			}
			//echo $total_sum;
			$total_cnt = mysql_num_rows($re);
			//echo $total_cnt;
			$total_avg = 0;
			if($total_cnt > 0){
				$total_avg = round($total_sum / $total_cnt, 2);
			}
			$this->smarty->assign( 'total_avg' , $total_avg );
			$this->smarty->assign( 'max_time' , $max_time );

			$this->smarty->assign( 'work_position' , json_encode($work_position) );
			$this->smarty->display("planning_mng/insaReport/PersonReport_05_HTML_AJAX_01_mvc.tpl");
		}

		//월평균연장근무일수
		function REPORT_05_1HTML_Ajax_02(){
			extract($_REQUEST);
			global $db_jang;
			global $db_ptc;
			global $db;

			//	년도
			if($set_year == null or $set_year == ''){
				$set_year = date('Y')-1;
			}
			$this->smarty->assign( 'set_year' , $set_year );
			if( $set_year == date('Y') ){
				$set_month = date('m')-1;
			}else{
				$set_month = 12;
			}
			$set_date = $set_year.str_pad($set_month, 2 , "0", STR_PAD_LEFT);
			//	부서코드
			if($set_dept == null or $set_dept == ''){
				$set_dept = 98;
			}
			$this->smarty->assign( 'set_dept' , $set_dept );
			//	직급
			if($set_position == null or $set_position == ''){
				$set_position = '%';
			}
			$this->smarty->assign( 'set_position' , $set_position );

			$azsql = " select Name from systemconfig_tbl where SysKey = 'GroupCode' and Code = '".sprintf('%02d',$set_dept)."'; ";
			//echo $azsql;
			if( $COMPANY == 'JANG' ){
				$re = mysql_query($azsql,$db_jang);
			}elseif( $COMPANY == 'PTC' ){
				$re = mysql_query($azsql,$db_ptc);
			}else{
				$re = mysql_query($azsql,$db);
			}
			while($row=mysql_fetch_array($re)){
				$this->smarty->assign( 'set_dept_name' , $row['Name'] );
			}

			if($set_position == '%'){
				$set_position_name = '전체';
				$this->smarty->assign( 'set_position_name' , $set_position_name );
			}else{
				$azsql = " select Name from systemconfig_tbl where SysKey = 'PositionCode' and Code = '".$set_position."'; ";
				if( $COMPANY == 'JANG' ){
					$re = mysql_query($azsql,$db_jang);
				}elseif( $COMPANY == 'PTC' ){
					$re = mysql_query($azsql,$db_ptc);
				}else{
					$re = mysql_query($azsql,$db);
				}
				while($row=mysql_fetch_array($re)){
					$set_position_name = $row['Name'];
					$this->smarty->assign( 'set_position_name' , $row['Name'] );
				}
			}

			$work_position = array();
			$azsql = "
				select * from (
					select
						date_y																											as item_1
						, IF('%%%' = '".$set_dept."', '전체', ( SELECT Name FROM systemconfig_tbl WHERE SysKey = 'GroupCode' AND Code = '".$set_dept."' ) ) 					as item_2
						, korName																										as item_3
						, memberno																										as item_4
						, position																										as item_5
						, if(
							month_cnt2 = 0
							, 0
							, ROUND(
								IF(
									date_format(EntryDate , '%Y%m') = '".$set_date."'
									, over_day_real - (select over_day_real from worker_total_tbl where memberno = A.memberno and date_y = A.date_y and date_m = date_format(EntryDate , '%m'))
									, over_day_real
								)/month_cnt2
							, 0)
						)													as item_6
					from
						(
							select
								*
								, ( select Name from systemconfig_tbl where Syskey = 'PositionCode' and Code = AA.rank_code ) as position
								, IF( month_cnt < 0 , 0, month_cnt) as month_cnt2
							from
								(
									SELECT
										date_y
										, memberno
										, SUM(vacation1) AS vacation1
										, SUM(vacation2) AS vacation2
										, SUM(login_cnt) AS login_cnt
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_time_real)) AS over_time_real
										, sum(over_time_real) AS over_time_real2
										, sum(if(over_time_confirm > limit_time, limit_time, over_time_confirm)) AS over_time_confirm
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_day_real)) AS over_day_real
										, max(dept_code) as GroupCode
										, max(rank_code) as rank_code
									FROM
										(
											select
												date_y
												, date_m
												, memberno
												, vacation1
												, vacation2
												, login_cnt
												, over_time_real
												, (floor((over_time_confirm+30)/60)*60) as over_time_confirm
												, ifnull((select SUBSTRING_INDEX(max_time, ':', 1) from overtime_basic_new_tbl C WHERE code between 102 AND 105 AND C.rankcode like concat('%-', (SELECT RankCode FROM member_tbl B WHERE B.MemberNo = A.memberno), '-%'))*60, 0) as limit_time
												, over_day_real
												, ( CASE A.date_m WHEN ".$set_month." THEN dept_code ELSE '' END ) as dept_code
												, ( CASE A.date_m WHEN ".$set_month." THEN rank_code ELSE '' END ) as rank_code
											from
												worker_total_tbl A
											WHERE
												date_y = ".$set_year."
												and A.memberno in ( select memberno from worker_total_tbl B where B.date_y = ".$set_year." and B.date_m = ".$set_month." and B.dept_code like '".$set_dept."' )
										) CC
									GROUP BY
										date_y, memberno
								) AA
								, (
									SELECT
										korName
										, MemberNo as MemberNo2
										, EntryDate
										, LeaveDate
										, IF(date_format(EntryDate , '%Y') = '".$set_year."', ".$set_month."-date_format(EntryDate , '%m'), ".$set_month.") AS month_cnt
									FROM
										member_tbl Z
									WHERE
										date_format(EntryDate , '%Y%m') <= ".$set_date."
								) BB
							where AA.memberno = BB.MemberNo2
						) A
					where
						IF('%' = '".$set_position."', 1=1, position = '".$set_position_name."')
				) Z
				order by
					Z.item_6 desc, Z.item_3
			;";
			//echo $azsql;

			$total_sum = 0;
			$max_time = 0;
			if( $COMPANY == 'JANG' ){
				$re = mysql_query($azsql,$db_jang);
			}elseif( $COMPANY == 'PTC' ){
				$re = mysql_query($azsql,$db_ptc);
			}else{
				$re = mysql_query($azsql,$db);
			}
			while($row=mysql_fetch_array($re)){
				$total_sum += $row['item_6'];
				if( $max_time < $row['item_6'] ){
					$max_time = $row['item_6'];
				}

				if(mb_strlen($row['item_3'], 'utf-8') < 3){
					$row['item_3'] = iconv_substr($row['item_3'], 0, 1, 'utf-8').' '.iconv_substr($row['item_3'], 1, 1, 'utf-8');
				}
				if( $row['item_5'] == '책임연구원' ){
					$row['item_5'] = '책임';
				}elseif( $row['item_5'] == '수석연구원' ){
					$row['item_5'] = '수석';
				}elseif( $row['item_5'] == '선임연구원' ){
					$row['item_5'] = '선임';
				}
				if(mb_strlen($row['item_5'], 'utf-8') < 3){
					$row['item_5'] = iconv_substr($row['item_5'], 0, 1, 'utf-8').' '.iconv_substr($row['item_5'], 1, 1, 'utf-8');
				}

				$member_name = $row['item_3'];
				for( $i = mb_strlen($row['item_3'], 'utf-8'); $i<4; $i++ ){
					$member_name .= ' ';
				}
				$member_name .= $row['item_5'];

				array_push($work_position, array(
					"member" => $member_name
					, "overtime" => $row['item_6']
					, "overtime_view" => floor($row['item_6']/60).':'.sprintf('%02d',($row['item_6']%60))
				));
			}
			//echo $total_sum;
			$total_cnt = mysql_num_rows($re);
			//echo $total_cnt;
			$total_avg = 0;
			if($total_cnt > 0){
				$total_avg = round($total_sum / $total_cnt, 2);
			}
			$this->smarty->assign( 'total_avg' , $total_avg );
			$this->smarty->assign( 'max_time' , $max_time );

			$this->smarty->assign( 'work_position' , json_encode($work_position) );
			$this->smarty->display("planning_mng/insaReport/PersonReport_05_HTML_AJAX_02_mvc.tpl");
		}

		//연간근무비율
		function REPORT_05_1HTML_Ajax_03(){
			extract($_REQUEST);
			global $db_jang;
			global $db_ptc;
			global $db;

			//	년도
			if($set_year == null or $set_year == ''){
				$set_year = date('Y')-1;
			}
			$this->smarty->assign( 'set_year' , $set_year );
			if( $set_year == date('Y') ){
				$set_month = date('m')-1;
			}else{
				$set_month = 12;
			}
			$set_date = $set_year.str_pad($set_month, 2 , "0", STR_PAD_LEFT);
			//	부서코드
			if($set_dept == null or $set_dept == ''){
				$set_dept = 98;
			}
			$this->smarty->assign( 'set_dept' , $set_dept );
			//	직급
			if($set_position == null or $set_position == ''){
				$set_position = '%';
			}
			$this->smarty->assign( 'set_position' , $set_position );

			$azsql = " select Name from systemconfig_tbl where SysKey = 'GroupCode' and Code = '".sprintf('%02d',$set_dept)."'; ";
			//echo $azsql;
			if( $COMPANY == 'JANG' ){
				$re = mysql_query($azsql,$db_jang);
			}elseif( $COMPANY == 'PTC' ){
				$re = mysql_query($azsql,$db_ptc);
			}else{
				$re = mysql_query($azsql,$db);
			}
			while($row=mysql_fetch_array($re)){
				$this->smarty->assign( 'set_dept_name' , $row['Name'] );
			}

			if($set_position == '%'){
				$set_position_name = '전체';
				$this->smarty->assign( 'set_position_name' , $set_position_name );
			}else{
				$azsql = " select Name from systemconfig_tbl where SysKey = 'PositionCode' and Code = '".$set_position."'; ";
				if( $COMPANY == 'JANG' ){
					$re = mysql_query($azsql,$db_jang);
				}elseif( $COMPANY == 'PTC' ){
					$re = mysql_query($azsql,$db_ptc);
				}else{
					$re = mysql_query($azsql,$db);
				}
				while($row=mysql_fetch_array($re)){
					$set_position_name = $row['Name'];
					$this->smarty->assign( 'set_position_name' , $row['Name'] );
				}
			}

			$work_position = array();
			$azsql = "
				select * from (
					select
						date_y																											as item_1
						, IF('%%%' = '".$set_dept."', '전체', ( SELECT Name FROM systemconfig_tbl WHERE SysKey = 'GroupCode' AND Code = '".$set_dept."' ) ) 					as item_2
						, korName																										as item_3
						, memberno																										as item_4
						, position																										as item_5
						, ROUND( ( date_d - ( week + holy + ROUND( vacation1/8 + vacation2/8 , 1) ) + login_cnt ) / date_d * 100 , 1)	as item_6
					from
						(
							select
								*
								, ( select Name from systemconfig_tbl where Syskey = 'PositionCode' and Code = AA.rank_code ) as position
								, IF( month_cnt < 0 , 0, month_cnt) as month_cnt2
							from
								(
									SELECT
										date_y
										, memberno
										, SUM(vacation1) AS vacation1
										, SUM(vacation2) AS vacation2
										, SUM(login_cnt) AS login_cnt
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_time_real)) AS over_time_real
										, sum(over_time_real) AS over_time_real2
										, sum(if(over_time_confirm > limit_time, limit_time, over_time_confirm)) AS over_time_confirm
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_day_real)) AS over_day_real
										, max(dept_code) as GroupCode
										, max(rank_code) as rank_code
									FROM
										(
											select
												date_y
												, date_m
												, memberno
												, vacation1
												, vacation2
												, login_cnt
												, over_time_real
												, (floor((over_time_confirm+30)/60)*60) as over_time_confirm
												, ifnull((select SUBSTRING_INDEX(max_time, ':', 1) from overtime_basic_new_tbl C WHERE code between 102 AND 105 AND C.rankcode like concat('%-', (SELECT RankCode FROM member_tbl B WHERE B.MemberNo = A.memberno), '-%'))*60, 0) as limit_time
												, over_day_real
												, ( CASE A.date_m WHEN ".$set_month." THEN dept_code ELSE '' END ) as dept_code
												, ( CASE A.date_m WHEN ".$set_month." THEN rank_code ELSE '' END ) as rank_code
											from
												worker_total_tbl A
											WHERE
												date_y = ".$set_year."
												and A.memberno in ( select memberno from worker_total_tbl B where B.date_y = ".$set_year." and B.date_m = ".$set_month." and B.dept_code like '".$set_dept."' )
										) CC
									GROUP BY
										date_y, memberno
								) AA
								, (
									SELECT
										korName
										, MemberNo as MemberNo2
										, EntryDate
										, LeaveDate
										, IF(date_format(EntryDate , '%Y') = '".$set_year."', ".$set_month."-date_format(EntryDate , '%m'), ".$set_month.") AS month_cnt
									FROM
										member_tbl Z
									WHERE
										date_format(EntryDate , '%Ym') <= ".$set_date."
								) BB
							where AA.memberno = BB.MemberNo2
						) A
						, (
							SELECT
								date_y as date_y2
								, SUM(date_d) AS date_d
								, SUM(week) AS week
								, SUM(holy) AS holy
							FROM
								worker_date_tbl
							WHERE
								date_y = ".$set_year."
							GROUP BY
								date_y
						) B
					where
						A.date_y = B.date_y2
						and IF('%' = '".$set_position."', 1=1, position = '".$set_position_name."')
				) Z
				order by
					Z.item_6 desc, Z.item_3
			;";
			//echo $azsql;

			$total_sum = 0;
			$max_time = 0;
			if( $COMPANY == 'JANG' ){
				$re = mysql_query($azsql,$db_jang);
			}elseif( $COMPANY == 'PTC' ){
				$re = mysql_query($azsql,$db_ptc);
			}else{
				$re = mysql_query($azsql,$db);
			}
			while($row=mysql_fetch_array($re)){
				$total_sum += $row['item_6'];
				if( $max_time < $row['item_6'] ){
					$max_time = $row['item_6'];
				}

				if(mb_strlen($row['item_3'], 'utf-8') < 3){
					$row['item_3'] = iconv_substr($row['item_3'], 0, 1, 'utf-8').' '.iconv_substr($row['item_3'], 1, 1, 'utf-8');
				}
				if( $row['item_5'] == '책임연구원' ){
					$row['item_5'] = '책임';
				}elseif( $row['item_5'] == '수석연구원' ){
					$row['item_5'] = '수석';
				}elseif( $row['item_5'] == '선임연구원' ){
					$row['item_5'] = '선임';
				}
				if(mb_strlen($row['item_5'], 'utf-8') < 3){
					$row['item_5'] = iconv_substr($row['item_5'], 0, 1, 'utf-8').' '.iconv_substr($row['item_5'], 1, 1, 'utf-8');
				}

				$member_name = $row['item_3'];
				for( $i = mb_strlen($row['item_3'], 'utf-8'); $i<4; $i++ ){
					$member_name .= ' ';
				}
				$member_name .= $row['item_5'];

				array_push($work_position, array(
					"member" => $member_name
					, "overtime" => $row['item_6']
					, "overtime_view" => floor($row['item_6']/60).':'.sprintf('%02d',($row['item_6']%60))
				));
			}
			//echo $total_sum;
			$total_cnt = mysql_num_rows($re);
			//echo $total_cnt;
			$total_avg = 0;
			if($total_cnt > 0){
				$total_avg = round($total_sum / $total_cnt, 2);
			}
			$this->smarty->assign( 'total_avg' , $total_avg );
			$this->smarty->assign( 'max_time' , $max_time );

			$this->smarty->assign( 'work_position' , json_encode($work_position) );
			$this->smarty->display("planning_mng/insaReport/PersonReport_05_HTML_AJAX_03_mvc.tpl");
		}

		//주평균연장근무시간
		function REPORT_05_1HTML_Ajax_04(){
			extract($_REQUEST);
			global $db_jang;
			global $db_ptc;
			global $db;

			//	년도
			if($set_year == null or $set_year == ''){
				$set_year = date('Y');
			}
			$this->smarty->assign( 'set_year' , $set_year );
			if( $set_year == date('Y') ){
				$set_month = date('m')-1;
			}else{
				$set_month = 12;
			}
			$set_date = $set_year.sprintf('%02d',$set_month);
			//	부서코드
			if($set_dept == null or $set_dept == ''){
				$set_dept = 98;
			}
			$this->smarty->assign( 'set_dept' , $set_dept );
			//	직급
			if($set_position == null or $set_position == ''){
				$set_position = '%';
			}
			$this->smarty->assign( 'set_position' , $set_position );

			$set_date_last = $set_year.'-'.sprintf('%02d',$set_month).'-'.date('t', strtotime($set_date."01"));
			//echo $set_date_last;

			$azsql = " select Name from systemconfig_tbl where SysKey = 'GroupCode' and Code = '".sprintf('%02d',$set_dept)."'; ";
			if( $COMPANY == 'JANG' ){
				$re = mysql_query($azsql,$db_jang);
			}elseif( $COMPANY == 'PTC' ){
				$re = mysql_query($azsql,$db_ptc);
			}else{
				$re = mysql_query($azsql,$db);
			}
			while($row=mysql_fetch_array($re)){
				$this->smarty->assign( 'set_dept_name' , $row['Name'] );
			}

			if($set_position == '%'){
				$set_position_name = '전체';
				$this->smarty->assign( 'set_position_name' , $set_position_name );
			}else{
				$azsql = " select Name from systemconfig_tbl where SysKey = 'PositionCode' and Code = '".$set_position."'; ";
				$re = mysql_query($azsql,$db);
				while($row=mysql_fetch_array($re)){
					$set_position_name = $row['Name'];
					$this->smarty->assign( 'set_position_name' , $row['Name'] );
				}

				$azsql = " select max_time from overtime_basic_new_tbl A where A.desc like '% ".$set_position." %' order by code desc limit 1 ";
				//echo $azsql;
				$re = mysql_query($azsql,$db);
				while($row=mysql_fetch_array($re)){
					$this->smarty->assign( 'limit_time' , str_replace(":00", "", $row['max_time'])*60 );
				}
			}

			$work_position = array();
			$azsql = "
				select * from (
					select
						date_y																											as item_1
						, IF('%%%' = '".$set_dept."', '전체', ( SELECT Name FROM systemconfig_tbl WHERE SysKey = 'GroupCode' AND Code = '".$set_dept."' ) ) 					as item_2
						, korName																										as item_3
						, memberno																										as item_4
						, position																										as item_5
						, if(
							month_cnt2 = 0
							, 0
							, ROUND(
								IF(
									date_format(EntryDate , '%Y%m') = '".$set_date."'
									, over_time_real - (select over_time_real from worker_total_tbl where memberno = A.memberno and date_y = A.date_y and date_m = date_format(EntryDate , '%m'))
									, over_time_real
								)/month_cnt2
							, 0)
						)													as item_6
					from
						(
							select
								*
								, ( select Name from systemconfig_tbl where Syskey = 'PositionCode' and Code = AA.rank_code ) as position
								, IF( week_cnt < 0 , 0, week_cnt) as month_cnt2
							from
								(
									SELECT
										date_y
										, memberno
										, SUM(vacation1) AS vacation1
										, SUM(vacation2) AS vacation2
										, SUM(login_cnt) AS login_cnt
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_time_real)) AS over_time_real
										, sum(over_time_real) AS over_time_real2
										, sum(if(over_time_confirm > limit_time, limit_time, over_time_confirm)) AS over_time_confirm
										, SUM(IF(CONCAT(date_y,LPAD(date_m, 2, 0)) = date_format(now() , '%Y%m'), 0, over_day_real)) AS over_day_real
										, max(dept_code) as GroupCode
										, max(rank_code) as rank_code
									FROM
										(
											select
												date_y
												, date_m
												, memberno
												, vacation1
												, vacation2
												, login_cnt
												, over_time_real
												, (floor((over_time_confirm+30)/60)*60) as over_time_confirm
												, ifnull((select SUBSTRING_INDEX(max_time, ':', 1) from overtime_basic_new_tbl C WHERE code between 102 AND 105 AND C.rankcode like concat('%-', (SELECT RankCode FROM member_tbl B WHERE B.MemberNo = A.memberno), '-%'))*60, 0) as limit_time
												, over_day_real
												, ( CASE A.date_m WHEN ".$set_month." THEN dept_code ELSE '' END ) as dept_code
												, ( CASE A.date_m WHEN ".$set_month." THEN rank_code ELSE '' END ) as rank_code
											from
												worker_total_tbl A
											WHERE
												date_y = ".$set_year."
												and A.memberno in ( select memberno from worker_total_tbl B where B.date_y = ".$set_year." and B.date_m = ".$set_month." and B.dept_code like '".$set_dept."' )
										) CC
									GROUP BY
										date_y, memberno
								) AA
								, (
									SELECT
										max(korName) as korName
										, MemberNo as MemberNo2
										, max(EntryDate) as EntryDate
										, max(LeaveDate) as LeaveDate
										, sum(week) AS week_cnt
									FROM
										member_tbl Z
										, (
											SELECT C.date_ymd, YEAR(C.date_ymd) AS date_y, MONTH(C.date_ymd) AS date_m, 1 AS week
											FROM (
												SELECT DATE('".$set_date_last."') - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY as date_ymd FROM (
												SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS a
												CROSS JOIN (
												SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS b
												CROSS JOIN (
												SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
												UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
												) AS c
											) AS C
											WHERE
												C.date_ymd BETWEEN '".$set_year."-01-01 00:00:00' AND '".$set_date_last." 23:59:59'
												and DAYOFWEEK( C.date_ymd ) = 5
										) W
									WHERE
										date_format(EntryDate , '%Y%m') <= '".$set_year.$set_month."'
										and date_format(EntryDate , '%Y%m') < date_format(W.date_ymd , '%Y%m')
									group by MemberNo
									order by EntryDate desc
								) BB
							where AA.memberno = BB.MemberNo2
						) A
					where
						IF('%' = '".$set_position."', 1=1, position = '".$set_position_name."')
				) Z
				order by
					Z.item_6 desc, Z.item_3
			;";
			//echo $azsql;

			$total_sum = 0;
			$max_time = 0;
			if( $COMPANY == 'JANG' ){
				$re = mysql_query($azsql,$db_jang);
			}elseif( $COMPANY == 'PTC' ){
				$re = mysql_query($azsql,$db_ptc);
			}else{
				$re = mysql_query($azsql,$db);
			}
			while($row=mysql_fetch_array($re)){
				$total_sum += $row['item_6'];
				if( $max_time < $row['item_6'] ){
					$max_time = $row['item_6'];
				}

				if(mb_strlen($row['item_3'], 'utf-8') < 3){
					$row['item_3'] = iconv_substr($row['item_3'], 0, 1, 'utf-8').' '.iconv_substr($row['item_3'], 1, 1, 'utf-8');
				}
				if( $row['item_5'] == '책임연구원' ){
					$row['item_5'] = '책임';
				}elseif( $row['item_5'] == '수석연구원' ){
					$row['item_5'] = '수석';
				}elseif( $row['item_5'] == '선임연구원' ){
					$row['item_5'] = '선임';
				}
				if(mb_strlen($row['item_5'], 'utf-8') < 3){
					$row['item_5'] = iconv_substr($row['item_5'], 0, 1, 'utf-8').' '.iconv_substr($row['item_5'], 1, 1, 'utf-8');
				}

				$member_name = $row['item_3'];
				for( $i = mb_strlen($row['item_3'], 'utf-8'); $i<4; $i++ ){
					$member_name .= ' ';
				}
				$member_name .= $row['item_5'];

				array_push($work_position, array(
					"member" => $member_name
					, "overtime" => $row['item_6']
					, "overtime_view" => floor($row['item_6']/60).':'.sprintf('%02d',($row['item_6']%60))
				));
			}
			//echo $total_sum;
			$total_cnt = mysql_num_rows($re);
			//echo $total_cnt;
			$total_avg = 0;
			if($total_cnt > 0){
				$total_avg = round($total_sum / $total_cnt, 2);
			}
			$this->smarty->assign( 'total_avg' , $total_avg );
			$this->smarty->assign( 'max_time' , $max_time );

			$this->smarty->assign( 'work_position' , json_encode($work_position) );
			$this->smarty->display("planning_mng/insaReport/PersonReport_05_HTML_AJAX_04_mvc.tpl");
		}
////


		//개인-평균 비교 그래프
		function REPORT_06(){
			extract($_REQUEST);
			$this->smarty->assign( 'MainAction' , $MainAction );
			$this->smarty->assign( 'memberno' , $memberno );
			$this->smarty->assign( 'date_y' , $date_y );
			$this->smarty->assign( 'pdf' , $pdf );

			switch($MainAction){
				case "Ajax_1": 	$this->REPORT_06_Ajax_1();	break;	//연장시간
				case "Ajax_2": 	$this->REPORT_06_Ajax_2();	break;	//연장일수
				case "Ajax_3": 	$this->REPORT_06_Ajax_3();	break;	//근무비율
				default:
					global $db;
					$azsql = "
						select
							korName
							, GroupCode
						from
							member_tbl
						where
							MemberNo = '$memberno'
					";
					$re = mysql_query($azsql,$db);
					while($row=mysql_fetch_array($re)){
						$this->smarty->assign( 'korName' , $row['korName'] );
						$this->smarty->assign( 'GroupCode' , $row['GroupCode'] );
					}

					$this->smarty->assign( 'set_MainAction' , $set_MainAction );
					$this->smarty->assign( 'set_SubAction' , $set_SubAction );
					$this->smarty->assign( 'set_1_date_y' , $set_1_date_y );
					$this->smarty->assign( 'set_2_date_y' , $set_2_date_y );
					$this->smarty->display("planning_mng/insaReport/PersonREPORT_06_Main_mvc.tpl");
			}
		}

		//연장시간
		function REPORT_06_Ajax_1(){
			extract($_REQUEST);
			global $db;
			//print_r($_REQUEST);
			switch($SubAction){
				case "1":	//부서
					$column_name = 'A.dept_code';
					$split_type = 'dept';
					break;
				case "2":	//직위
					$column_name = 'A.rank_code';
					$split_type = 'rank';
					break;
				case "3":	//전체
					$column_name = "'all'";
					$split_type = 'all';
					break;
				case "4":	//본부
					$column_name = 'A.dept_top_code';
					$split_type = 'dept_top';
					break;
			}

			$work_position = array();

			if($target_div == 'chartdiv_1'){
				$azsql = "
					select
						A.date_y
						, ROUND( SUM( A.over_time_real )/720, 1 ) AS member
						, ROUND( SUM( B.time_average )/720, 1 ) AS average
					from
						( SELECT date_y, date_m, over_time_real, ".$column_name." as average_code FROM worker_total_tbl A WHERE date_y <= $year and memberno = '$memberno' ) A
						, ( SELECT date_y, date_m, average_code, time_average FROM worker_average_tbl WHERE date_y <= $year and split_type = '".$split_type."' ) B
					where
						A.date_y = B.date_y
						AND A.date_m = B.date_m
						AND A.average_code = B.average_code
					GROUP BY
						date_y
					ORDER BY
						date_y
				";
				//echo $azsql;

				$re = mysql_query($azsql,$db);
				$re_num = mysql_num_rows($re);
				$row_num = 0;
				if($re_num < 5){
					for( $i=1; $i<=(5-$re_num); $i++ ){
						array_push($work_position, array(
							"date_y" => ($year-5+$i)
							, "member" => 0
							, "average" => 0
						));
						$row_num++;
					}
				}
				while($row=mysql_fetch_array($re)){
					array_push( $work_position, $row );
					$row_num++;
					if($row_num > 5){
						break;
					}
				}
			}else{
				$azsql = "
					select
						A.date_m
						, ROUND( A.over_time_real/60, 1) AS member
						, ROUND( B.time_average/60, 1) AS average
					from
						(SELECT date_y, date_m, over_time_real, ".$column_name." as average_code FROM worker_total_tbl A WHERE date_y = $year and memberno = '$memberno') A
						, (SELECT date_y, date_m, average_code, time_average FROM worker_average_tbl WHERE date_y = $year and split_type = '".$split_type."') B
					where
						A.date_y = B.date_y
						AND A.date_m = B.date_m
						AND A.average_code = B.average_code
					ORDER BY
						date_m
				";
				//echo $azsql;

				$re = mysql_query($azsql,$db);
				$cnt = 0;
				while($row=mysql_fetch_array($re)){
					if( $cnt == 0 and $row['date_m'] != 1 ){
						for( $i=1; $i<$row['date_m']; $i++ ){
							array_push($work_position, array(
								"date_m" => $i
								, "member" => 0
								, "average" => 0
							));
						}
					}
					array_push( $work_position, $row );
					$cnt++;
				}
				if($cnt < 12){
					$cnt++;
					for( $i=$cnt; $i<13; $i++ ){
						array_push($work_position, array(
							"date_m" => $i
							, "member" => 0
							, "average" => 0
						));
					}
				}
			}

			echo json_encode($work_position);
		}

		//연장일수
		function REPORT_06_Ajax_2(){
			extract($_REQUEST);
			global $db;
			//print_r($_REQUEST);
			switch($SubAction){
				case "1":	//부서
					$column_name = 'A.dept_code';
					$split_type = 'dept';
					break;
				case "2":	//직위
					$column_name = 'A.rank_code';
					$split_type = 'rank';
					break;
				case "3":	//전체
					$column_name = "'all'";
					$split_type = 'all';
					break;
				case "4":	//본부
					$column_name = 'A.dept_top_code';
					$split_type = 'dept_top';
					break;
			}

			$work_position = array();

			if($target_div == 'chartdiv_1'){
				$azsql = "
					select
						A.date_y
						, ROUND( SUM( A.over_day_real )/12, 1 ) AS member
						, ROUND( SUM( B.day_average )/12, 1 ) AS average
					from
						( SELECT date_y, date_m, over_day_real, ".$column_name." as average_code FROM worker_total_tbl A WHERE date_y <= $year and memberno = '$memberno' ) A
						, ( SELECT date_y, date_m, average_code, day_average FROM worker_average_tbl WHERE date_y <= $year and split_type = '".$split_type."' ) B
					where
						A.date_y = B.date_y
						AND A.date_m = B.date_m
						AND A.average_code = B.average_code
					GROUP BY
						date_y
					ORDER BY
						date_y
				";
				//echo $azsql;

				$re = mysql_query($azsql,$db);
				$re_num = mysql_num_rows($re);
				$row_num = 0;
				if($re_num < 5){
					for( $i=1; $i<=(5-$re_num); $i++ ){
						array_push($work_position, array(
							"date_y" => ($year-5+$i)
							, "member" => 0
							, "average" => 0
						));
						$row_num++;
					}
				}
				while($row=mysql_fetch_array($re)){
					array_push( $work_position, $row );
					$row_num++;
					if($row_num > 5){
						break;
					}
				}
			}else{
				$azsql = "
					select
						A.date_m
						, A.over_day_real AS member
						, B.day_average AS average
					from
						(SELECT date_y, date_m, over_day_real, ".$column_name." as average_code FROM worker_total_tbl A WHERE date_y = $year and memberno = '$memberno') A
						, (SELECT date_y, date_m, average_code, day_average FROM worker_average_tbl WHERE date_y = $year and split_type = '".$split_type."') B
					where
						A.date_y = B.date_y
						AND A.date_m = B.date_m
						AND A.average_code = B.average_code
					ORDER BY
						date_m
				";
				//echo $azsql;

				$re = mysql_query($azsql,$db);
				$cnt = 0;
				while($row=mysql_fetch_array($re)){
					if( $cnt == 0 and $row['date_m'] != 1 ){
						for( $i=1; $i<$row['date_m']; $i++ ){
							array_push($work_position, array(
								"date_m" => $i
								, "member" => 0
								, "average" => 0
							));
						}
					}
					array_push( $work_position, $row );
					$cnt++;
				}
				if($cnt < 12){
					$cnt++;
					for( $i=$cnt; $i<13; $i++ ){
						array_push($work_position, array(
							"date_m" => $i
							, "member" => 0
							, "average" => 0
						));
					}
				}
			}

			echo json_encode($work_position);
		}

		//연장일수
		function REPORT_06_Ajax_3(){
			extract($_REQUEST);
			global $db;
			//print_r($_REQUEST);
			switch($SubAction){
				case "1":	//부서
					$column_name = 'A.dept_code';
					$split_type = 'dept';
					break;
				case "2":	//직위
					$column_name = 'A.rank_code';
					$split_type = 'rank';
					break;
				case "3":	//전체
					$column_name = "'all'";
					$split_type = 'all';
					break;
				case "4":	//본부
					$column_name = 'A.dept_top_code';
					$split_type = 'dept_top';
					break;
			}

			$work_position = array();

			if($target_div == 'chartdiv_1'){
				$azsql = "
					select
						A.date_y
						, ROUND( ( SUM( C.date_d - C.week - C.holy ) + SUM( A.vacation ) ) / SUM( C.date_d ) * 100, 1 ) AS member
						, ROUND( ( SUM( B.per_average ) / SUM( C.date_d ) ) * 100, 1 ) AS average
					from
						(SELECT date_y, date_m, ( login_cnt - (vacation1/8) - (vacation2/8) ) as vacation, ".$column_name." as average_code  FROM worker_total_tbl A WHERE date_y <= $year and memberno = '$memberno') A
						, (SELECT date_y, date_m, average_code, per_average FROM worker_average_tbl WHERE date_y <= $year and split_type = '".$split_type."') B
						, (SELECT date_y, date_m, date_d, week, holy FROM worker_date_tbl WHERE date_y <= $year ) C
					where
						A.date_y = B.date_y
						AND A.date_m = B.date_m
						AND A.average_code = B.average_code
						and A.date_y = C.date_y
						AND A.date_m = C.date_m
					GROUP BY
						date_y
				";
				//echo $azsql;

				$re = mysql_query($azsql,$db);
				$re_num = mysql_num_rows($re);
				$row_num = 0;
				if($re_num < 5){
					for( $i=1; $i<=(5-$re_num); $i++ ){
						array_push($work_position, array(
							"date_y" => ($year-5+$i)
							, "member" => 0
							, "average" => 0
						));
						$row_num++;
					}
				}
				while($row=mysql_fetch_array($re)){
					array_push( $work_position, $row );
					$row_num++;
					if($row_num > 5){
						break;
					}
				}
			}else{
				$azsql = "
					select
						A.date_m
						, ROUND( ( ( C.date_d - C.week - C.holy ) + ( A.vacation ) ) / ( C.date_d ) * 100, 1 ) AS member
						, ROUND( ( ( B.per_average ) / ( C.date_d ) ) * 100, 1 ) AS average
					from
						(SELECT date_y, date_m, ( login_cnt - (vacation1/8) - (vacation2/8) ) as vacation, ".$column_name." as average_code  FROM worker_total_tbl A WHERE date_y = $year and memberno = '$memberno') A
						, (SELECT date_y, date_m, average_code, per_average FROM worker_average_tbl WHERE date_y = $year and split_type = '".$split_type."') B
						, (SELECT date_y, date_m, date_d, week, holy FROM worker_date_tbl WHERE date_y = $year ) C
					where
						A.date_y = B.date_y
						AND A.date_m = B.date_m
						AND A.average_code = B.average_code
						and A.date_y = C.date_y
						AND A.date_m = C.date_m
					ORDER BY
						date_m
				";
				//echo $azsql;

				$re = mysql_query($azsql,$db);
				$cnt = 0;
				while($row=mysql_fetch_array($re)){
					if( $cnt == 0 and $row['date_m'] != 1 ){
						for( $i=1; $i<$row['date_m']; $i++ ){
							array_push($work_position, array(
								"date_m" => $i
								, "member" => 0
								, "average" => 0
							));
						}
					}
					array_push( $work_position, $row );
					$cnt++;
				}
				if($cnt < 12){
					$cnt++;
					for( $i=$cnt; $i<13; $i++ ){
						array_push($work_position, array(
							"date_m" => $i
							, "member" => 0
							, "average" => 0
						));
					}
				}
			}

			echo json_encode($work_position);
		}

		//데이터 확인 화면
		function REPORT_07(){
			extract($_REQUEST);

			switch($MainAction){
				case "Grid_1": 	$this->smarty->display("planning_mng/insaReport/PersonREPORT_07_Grid_mvc.tpl");	break;	//
				case "Ajax_1": 	$this->REPORT_07_Ajax_1();	break;	//
				default:
					$this->smarty->display("planning_mng/insaReport/PersonREPORT_07_Main_mvc.tpl");
			}
		}

		function REPORT_07_Ajax_1(){
			extract($_REQUEST);
			$sql = "
				SELECT
					A.EMP_NO AS ERP_MEMBERNO
					, CASE
						WHEN A.SERVICE_DIV = '3' AND TO_CHAR(SYSDATE, 'YYYYMMDD') > NVL(A.RETIRE_DATE, '00000000') THEN '9'
						WHEN B.PAY_DIV = '20' AND B.WORKING_COMPANY <> '20' THEN '2'
						WHEN B.PAY_DIV = '30' AND B.WORKING_COMPANY <> '20' THEN '2'
						ELSE '1'
					END AS ERP_WORKPOSITION
					, ( select GRADE_NAME from HR_CODE_GRADE where B.GRADE_CODE = GRADE_CODE ) AS ERP_GRADE_NAME
					, A.EMP_NAME AS ERP_KORNAME
					, A.EMPNAME_CHI AS ERP_CHINAME
					, A.EMPNAME_ENG AS ERP_ENGNAME
					, DECODE( A.SEX_DIV, 1, 'M', 'F' ) AS ERP_GENDER
					, A.GROUP_JOIN_DATE AS ERP_ENTRYDATE
					, A.RRN_PRE || '-' || A.RRN_POST AS ERP_JUMINNO
					, replace(A.PHONE_PRE, '-', '')||replace(A.PHONE_POST, '-', '') AS ERP_PHONE
					, A.CELL_PRE || A.CELL_POST AS ERP_MOBILE
					, A.E_MAIL AS ERP_EMAIL
					, A.BORN_ADDR1 || ' ' || A.BORN_ADDR2 AS ERP_ORIGNADDRESS
					, A.ADDR1 || ' ' || A.ADDR2 AS ERP_ADDRESS
					, NVL( A.BIRTHDAY, '00000000' ) AS ERP_BIRTHDAY
					, ( select max(proj_code) from CS_CONT_MAP_MASTER where a.dept_code = proj_org_code ) AS ERP_SITECODE
					, A.TITLE_CODE AS ERP_DUTYCODE
					, B.ERP_DEPT_CODE AS ERP_GROUPCODE
					, NVL( A.RETIRE_DATE, '00000000' ) AS ERP_LEAVEDATE

					, B.REALRANk AS ERP_AAAAA
					, B.GRADE_CODE
					, B.PAY_DIV
					, B.WORKING_COMPANY
					, B.WORKING_DEPT
					, B.WORKING_RANK_CODE
					, B.WORKING_RANK_NAME
					, CASE B.PAY_DIV
						WHEN '10' THEN 'SAMAN'
						WHEN '20' THEN 'HANMAC'
						WHEN '30' THEN 'BARON'
						WHEN '40' THEN 'JANG'
						WHEN '50' THEN 'HALLA'
						WHEN '60' THEN 'PTC'
						WHEN '99' THEN 'ETC'
					END AS ERP_COMPANY
				FROM
					HR_PERS_MASTER A
					, HR_ORDE_MASTER B
				WHERE
					B.FINAL_TAG = 'Y'
					AND B.CONFIRM_TAG = 'Y'
					AND A.EMP_NO = B.EMP_NO(+)
				ORDER BY
					ERP_WORKPOSITION, ERP_GROUPCODE
			";
			//echo $sql;
			$sql = "
				SELECT
					MemberNo as intra_MEMBERNO
					, WorkPosition as intra_WORKPOSITION
					, korName as intra_KORNAME
					, chiName as intra_CHINAME
					, engName as intra_ENGNAME
					, entrydate as intra_ENTRYDATE
					, juminno as intra_JUMINNO
					, phone as intra_PHONE
					, email as intra_EMAIL
					, orignaddress as intra_ORIGNADDRESS
					, address as intra_ADDRESS
					, birthday as intra_BIRTHDAY
					, LeaveDate as intra_LEAVEDATE
					, Company as intra_COMPANY
					, RankCode as intra_GRADE_NAME
					, RealRankCode as intra_AAAAA
					, groupcode as intra_GROUPCODE
					, mobile as intra_MOBILE
				FROM
					member_tbl A
			";
		}

		function REPORT_CHECK(){
			header('Content-Type: text/html; charset=UTF-8');
			extract($_REQUEST);
			include "../../../person_mng/inc/vacationfunction.php";
			include "../util/OracleClass.php";
			$this->oracle = new OracleClass($this->smarty, 'HANMAC');
			$ORACLE_SAMAN = new OracleClass($this->smarty, 'SAMAN');
			include "../inc/dbcon_JANG.inc";	//인트라넷 DB연결
			include "../inc/dbcon_PTC.inc";	//인트라넷 DB연결
			global $db;
			$action = true;	//	true	false

			if($action_test == 'test'){
				$action = false;
			}

			//$set_date = str_replace ( '-', '', $set_date) ;
			if($set_date == null or $set_date == ''){
				$set_date = date('Ym');
			}

			$set_date_ori = $set_date;
			for($c=0; $c<2; $c++){
				if($c == 0){
					$set_date = $set_date_ori;
				}else{
					$set_date = date("Ym",strtotime("-".$c." month", strtotime($set_date_ori."01")));
				}

				$set_date_bar = substr($set_date,0,4)."-".substr($set_date,4,2);
				$set_year = substr($set_date,0,4);
				$set_month = (int)substr($set_date,4,2);
				$set_date_last = $set_date.date('t', strtotime($set_date_bar."-01"));
				$set_date_last_bar = $set_date_bar.'-'.date('t', strtotime($set_date_bar."-01"));
				$set_month_Pre = date("Y-m",strtotime("-1 month", strtotime($set_date."01")));
			}
		}

		//화면에 출력할 데이터
		function SET_LIST( $LIST_MODE, $LIST_NAME, $daterow, $LIMIT_CNT ){
			$daterow_cnt = count($daterow);
			$this->smarty->assign( $LIST_NAME.'_CNT' , $daterow_cnt );
			/*
			if($daterow_cnt > $LIMIT_CNT and $LIST_MODE == 'limit' ){	//제한상태면 목록에서 안나오도록 제거
				$daterow[$daterow_cnt-1]['LAST'] = true;
				$daterow[$LIMIT_CNT-2] = $daterow[$daterow_cnt-1];
				for($i=($LIMIT_CNT-1); $i<$daterow_cnt; $i++){
					unset($daterow[$i]);
				}
			}
			*/
			$this->smarty->assign( $LIST_NAME.'_LIMIT' , $LIMIT_CNT );
			$this->smarty->assign( $LIST_NAME , $daterow );
		}

		//======================================================================================================

		function HangleEncode($item)
		{
				$result=trim(ICONV("EUC-KR","UTF-8",$item));
				if(trim($result)=="") 	$result="&nbsp";
				return $result;
		}

		function HangleEncodeUTF8_EUCKR($item)
		{
				$result=trim(ICONV("UTF-8","EUC-KR",$item));
				return $result;
		}

		function bear3StrCut($str,$len,$tail="..."){
			$rtn = array();
			return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str;
		}
		//=================================================
		// POST로 입력받은 자료를 처리하는 함수
		//=================================================
		function GetPOST_Item($Section)
		{
				$query_item=$_POST[$Section];
				$query_item=$this->HangleEncodeUTF8_EUCKR($query_item);
				return $query_item;
		}

		function PrintExcelHeader($filename)
		{

			$filename = $this->HangleEncodeUTF8_EUCKR($filename);
			if($this->excel != "")
			{
				header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
				header("Content-type:   application/x-msexcel; charset=utf-8");
				header("Content-Disposition: attachment; filename=\"$filename.xls\"");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false);
			}

		}

		function ExistFile($filename)
		{
			if(is_file($filename)==false){
				echo "파일없음";
				exit();
			}
		}

		function GetDateFormat($i_data)
		{
			if($i_data == "")
				return "";
			$data= str_replace("-", "", $i_data);
			$data= str_replace(".", "", $data);

			if(strlen($data) ==6)
				return substr($data,0,4).".".substr($data,4,2).".";
			else
				return substr($data,0,4).".".substr($data,4,2).".".substr($data,6,2).".";
		}

		function set_mobile_number($mobile){
			$re_mobile = '';
			if($mobile != ''){
				$mobile = str_replace(" ","",str_replace("-","",$mobile));
				$re_mobile = preg_replace("/(^02.{0}|^01.{1}|[0-9]{3})([0-9]+)([0-9]{4})/", "$1-$2-$3", $mobile);
			}
			return $re_mobile;
		}

}
?>