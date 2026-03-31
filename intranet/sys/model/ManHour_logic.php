<?php

	/***************************************
	* 전자결재 리스트
	****************************************/

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../inc/approval_function2.php";
	include "../util/OracleClass.php";
	include "../inc/setMH.php";	//Man hour 입력 함수

	/*
	extract($_GET);
	$memberID	=	$_REQUEST['memberID'];
	$WorkPosition = getWorkPositionByMemberNo($memberID); //워크포지션(WorkPosition)
	*/

	class ManHourLogic {
		var $smarty;
		var $oracle;
		function ManHourLogic($smarty)
		{
			$this->smarty=$smarty;
			$this->oracle=new OracleClass($smarty);
		}


		function PersonMain(){
			$dbinsert = true; // true false
			extract($_REQUEST);
			if(!$memberID){ $memberID = '201030'; }
			if(!$sdate){ $sdate = date("Ymd", strtotime("-1 month", time())); }
			//if(!$sdate){ $sdate = '20161201'; }
			if(!$edate){ $edate = date("Ymd", strtotime("-1 day", time())); }
			//if(!$edate){ $edate = '20161231'; }
			$sdate = str_replace("-", "", $sdate);
			$edate = str_replace("-", "", $edate);
			$this->smarty->assign('sdate',$sdate);
			$this->smarty->assign('edate',$edate);
			$this->smarty->assign('memberID',$memberID);

			if(!$tar_date){ $tar_date = date("Ym"); }
			$tar_date = str_replace("-", "", $tar_date);
			$this->smarty->assign('tar_date',$tar_date);

			$this->smarty->display("intranet/common_contents/work_manhour/PersonMain_mvc.tpl");
		}

		function pre_month($tar_date){
		//전달 구하기
			$s_year = substr($tar_date, 0, 4);
			$s_month = (int)substr($tar_date, 4, 2)-1;


			if((int)$s_month < 1){
				$s_month = "12";
				$s_year = (int)$s_year-1;
			}
			return $s_year.$s_month.'26';
		}

		function term(){
			$dbinsert = true; // true false
			extract($_REQUEST);
			//print_r($_SESSION);

			if(!$memberID){ $memberID = '201030'; }

			$tar_date = str_replace("-", "", $tar_date);
			$sdate = $tar_date.'01';
			$edate = $tar_date.date("t", mktime(0, 0, 0, substr($tar_date, -2), 1, substr($tar_date, 0, 4)));

			$prosql1 ="BEGIN Usp_MH_Employee_Term(:entries,'$memberID','$sdate','$edate'); END;";
			$prosql2 ="BEGIN Usp_MH_Employee_Workdays(:entries,'$sdate','$edate'); END;";
			if($dbinsert){
				$datarow1 = $this->oracle->LoadProcedure($prosql1,"list_data01","");
				$datarow2 = $this->oracle->LoadProcedure($prosql2,"list_data02","");
				$total_manhour = 0;
				$work_days = count($datarow2);
				$full_manhour = $work_days*8;
				for($i=0; $i<count($datarow1); $i++) {
					$total_manhour += (int)$datarow1[$i]["MANHOUR"];
				}//for

				for($i=0; $i<count($datarow1); $i++) {
					$datarow1[$i]["MANHOUR_PER"] = (int)$datarow1[$i]["MANHOUR"] / $total_manhour * 100;
					if($datarow1[$i]["CONT_NO"] == 'ZZZZZZ'){
						$datarow1[$i]["CONT_NAME"] = '공통';
						$datarow1[$i]["PROJ_NAME"] = '공통';
					}
				}//for
				$this->smarty->assign('sdate',$sdate);
				$this->smarty->assign('edate',$edate);
				$this->smarty->assign('total_manhour',$total_manhour);
				$this->smarty->assign('work_days',$work_days);
				$this->smarty->assign('full_manhour',$full_manhour);
				$this->smarty->assign('list_data01',$datarow1);
			}else{
				echo "oracle : ".$prosql1."<br>";
				echo "oracle : ".$prosql2."<br>";
			}
			$this->smarty->display("intranet/common_contents/work_manhour/PersonTerm_mvc.tpl");
		}

		function daily(){
			$dbinsert = true; // true false
			extract($_REQUEST);
			//print_r($_SESSION);

			if(!$memberID){ $memberID = '201030'; }
			$tar_date = str_replace("-", "", $tar_date);
			$sdate = $tar_date.'01';
			$edate = $tar_date.date("t", mktime(0, 0, 0, substr($tar_date, -2), 1, substr($tar_date, 0, 4)));

			$prosql1 ="BEGIN Usp_MH_Employee_Daily(:entries,'$memberID','$sdate','$edate'); END;";
			$prosql2 ="BEGIN Usp_MH_Employee_Workdays(:entries,'$sdate','$edate'); END;";
			if($dbinsert){
				$datarow1 = $this->oracle->LoadProcedure($prosql1,"list_data01","");
				$datarow2 = $this->oracle->LoadProcedure($prosql2,"list_data02","");

				$list_data = array();
				$list_graph = array();
				$list_project = array();
				$total_manhour = 0;
				$work_days = count($datarow2);
				$full_manhour = $work_days*8;
				for($i=0; $i<count($datarow1); $i++) {
					if($datarow1[$i]["CONT_NO"] == 'ZZZZZZ'){
						$datarow1[$i]["CONT_NAME"] = '공통';
						$datarow1[$i]["PROJ_NAME"] = '공통';
					}
					if($datarow1[$i]["CONT_NO"] != "&nbsp;"){
						$list_project[$datarow1[$i]["CONT_NO"]] = $datarow1[$i]["CONT_NAME"];
					}
					$list_graph[$datarow1[$i]["WORK_DATE"]][$datarow1[$i]["CONT_NO"]] = $datarow1[$i]["MANHOUR"];
					$datarow1[$i]["MANHOUR_PER"] = ((int)$datarow1[$i]["MANHOUR"])/$full_manhour*100;
					$total_manhour += (int)$datarow1[$i]["MANHOUR"];
					array_push($list_data,$datarow1[$i]);
				}//for
				//print_r($list_graph);

				$this->smarty->assign('sdate',$sdate);
				$this->smarty->assign('edate',$edate);
				$this->smarty->assign('total_manhour',$total_manhour);
				$this->smarty->assign('work_days',$work_days);
				$this->smarty->assign('full_manhour',$full_manhour);
				$this->smarty->assign('list_project',$list_project);
				$this->smarty->assign('list_graph',$list_graph);
				$this->smarty->assign('list_data',$list_data);
			}else{
				echo "oracle : ".$prosql1."<br>";
			}
			$this->smarty->display("intranet/common_contents/work_manhour/PersonDaily_mvc.tpl");
		}

		function manage_main(){
			$dbinsert = true; // true false
			extract($_REQUEST);
			//print_r($_REQUEST);

			//$now_day=date("Y-m-d h:i:s");
			//인원관리 시작일 종료일 설정
			if(!$memberID){ $memberID = '199093'; }	//216008 199093 201030
			if(!$sdate){ $sdate = date("Ymd", strtotime("-1 month", time())); }
			//if(!$sdate){ $sdate = '20161201'; }
			if(!$edate){ $edate = date("Ymd", strtotime("-1 day", time())); }
			//if(!$edate){ $edate = '20161231'; }
			$sdate = str_replace("-", "", $sdate);
			$edate = str_replace("-", "", $edate);
			$this->smarty->assign('sdate',$sdate);
			$this->smarty->assign('edate',$edate);
			$this->smarty->assign('memberID',$memberID);

			if(!$tar_date){ $tar_date = date("Ym"); }
			$tar_date = str_replace("-", "", $tar_date);
			$this->smarty->assign('tar_date',$tar_date);

			$prosql1 ="BEGIN Usp_MH_Employee_Info(:entries,'$memberID'); END;";
			$datarow1 = $this->oracle->LoadProcedure($prosql1,"list_data01","");
			$dept_code = '';
			$real_dept_code = '';
			$title_code = "";
			$dept_list = array();
			for($i=0; $i<count($datarow1); $i++) {
				if($admin != ''){
					array_push($dept_list,$datarow1[$i]);
				}else{
					if($datarow1[$i]["REAL_DEPT_CODE"] != '&nbsp'){
						if($datarow1[$i]["TITLE_CODE"] == '020'){
							if($i != 0){ $dept_code .= ','; }
							$dept_code .= $datarow1[$i]["DEPT_CODE"];
						}else{
							$dept_code = $datarow1[$i]["DEPT_CODE"];
						}
						$title_code = $datarow1[$i]["TITLE_CODE"];

						if($datarow1[$i]["DEPT_CODE"] == $datarow1[$i]["REAL_DEPT_CODE"] or $datarow1[$i]["TITLE_CODE"] == '020'){
							$real_dept_code = $datarow1[$i]["DEPT_CODE"];
							array_push($dept_list,$datarow1[$i]);
						}
					}
				}
			}//for
			$this->smarty->assign('dept_code',$dept_code);
			$this->smarty->assign('title_code',$title_code);
			$this->smarty->assign('dept_list',$dept_list);
			$this->smarty->assign('real_dept_code',$real_dept_code);
			$this->smarty->assign('admin',$admin);

			$this->smarty->display("intranet/common_contents/work_manhour/ManageMain_mvc.tpl");
		}

		function manage_person(){
			$dbinsert = true; // true false
			extract($_REQUEST);
			$sdate = str_replace("-", "", $sdate);
			$edate = str_replace("-", "", $edate);

			$prosql1 ="BEGIN Usp_MH_Manage_Person(:entries,'$memberID','$real_dept_code','$sdate','$edate'); END;";
			if($dbinsert){
				$datarow1 = $this->oracle->LoadProcedure($prosql1,"list_data01","");

				$list_project = array();
				$list_total = array();
				$list_detail = array();
				$ex_emp_no = '';
				$ex_emp_name = '';
				$ex_sort_no = '';
				$ex_grade_name = '';

				for($i=0; $i<count($datarow1); $i++) {
					if($ex_emp_no != $datarow1[$i]["EMP_NO"]){
						if($i != 0){
							//array_push($list_total,$list_data);
							$list_total[$ex_emp_no]["LIST_DATA"] = $list_data;
							$list_total[$ex_emp_no]["EMP_NAME"] = $ex_emp_name;
							$list_total[$ex_emp_no]["SORT_NO"] = $ex_sort_no;
							$list_total[$ex_emp_no]["EMP_MANHOUR"] = $emp_manhour;
							$list_total[$ex_emp_no]["GRADE_NAME"] = $ex_grade_name;
						}

						$list_detail[$datarow1[$i]["EMP_NO"]] = array();
						$list_data = array();
						$emp_manhour = 0;
						$j = 0;
					}

					$list_data[$j]["MH"] = $datarow1[$i]["MH"];
					if($datarow1[$i]["PROJ_CODE"] == '&nbsp'){ $datarow1[$i]["PROJ_CODE"] = 'ZZZZZZ'; }
					$list_data[$j]["PROJ_CODE"] = $datarow1[$i]["PROJ_CODE"];
					if($datarow1[$i]["PROJ_NAME"] == '&nbsp'){ $datarow1[$i]["PROJ_NAME"] = '공통'; }
					$list_data[$j]["PROJ_NAME"] = $datarow1[$i]["PROJ_NAME"];

					$ex_emp_no = $datarow1[$i]["EMP_NO"];
					$ex_emp_name = $datarow1[$i]["EMP_NAME"];
					$ex_sort_no = $datarow1[$i]["SORT_NO"];
					$ex_grade_name = $datarow1[$i]["GRADE_NAME"];
					$emp_manhour += (float)$datarow1[$i]["MH"];
					$j++;

					array_push($list_detail[$datarow1[$i]["EMP_NO"]],$datarow1[$i]);
					$list_project[$datarow1[$i]["PROJ_CODE"]] = $datarow1[$i]["PROJ_NAME"];
					//프로젝트 배열 추가
				}//for
				$list_total[$ex_emp_no]["LIST_DATA"] = $list_data;
				$list_total[$ex_emp_no]["EMP_NAME"] = $ex_emp_name;
				$list_total[$ex_emp_no]["SORT_NO"] = $ex_sort_no;
				$list_total[$ex_emp_no]["EMP_MANHOUR"] = $emp_manhour;
				$list_total[$ex_emp_no]["GRADE_NAME"] = $ex_grade_name;
				//print_r($list_total);

				//print_r($list_detail);
				$this->smarty->assign('list_project',$list_project);
				$this->smarty->assign('list_total',$list_total);
				$this->smarty->assign('list_detail',$list_detail);
				$this->smarty->assign('memberID',$memberID);
			}else{
				echo "oracle : ".$prosql1."<br>";
			}

			$this->smarty->display("intranet/common_contents/work_manhour/ManagePerson_mvc.tpl");
		}

		function manage_person_favorites(){
			$dbinsert = true; // true false
			extract($_REQUEST);
			$prosql ="BEGIN USP_MH_MANAGE_PERSON_FAVORITES( '$manager_empno', '$empno', '$solt_no' ); END;";
			if($dbinsert){
				//$datarow1 = $this->oracle->LoadProcedure($prosql,"list_data01","");
				$this->oracle->ProcedureExcuteQuery($prosql);
			}else{
				print_r($_REQUEST);
				echo "oracle : ".$prosql."<br>";
			}
		}

		function manage_project(){
			$dbinsert = true; // true false
			extract($_REQUEST);

			$prosql2 ="BEGIN USP_MH_MANAGE_PROJ_PM(:entries,'$memberID', '$real_dept_code'); END;";
			//echo $prosql2;
			$datarow2 = $this->oracle->LoadProcedure($prosql2,"list_data02","");
			$proj_data_pm = array();
			$favorites_data = array();
			for($i=0; $i<count($datarow2); $i++) {
				$datarow2[$i]["PROJ_TYPE"] = "PM";
				if($datarow2[$i]["SORT_NO"] != '&nbsp'){
					array_push($favorites_data,$datarow2[$i]);
				}
				array_push($proj_data_pm,$datarow2[$i]);
			}
			$this->smarty->assign('proj_data_pm',$proj_data_pm);

			$prosql3 ="BEGIN USP_MH_MANAGE_PROJ_SUB(:entries,'$memberID', '$real_dept_code'); END;";
			//echo $prosql3;
			$datarow3 = $this->oracle->LoadProcedure($prosql3,"list_data02","");
			$proj_data_sub = array();
			for($i=0; $i<count($datarow3); $i++) {
				$datarow3[$i]["PROJ_TYPE"] = "배분";
				if($datarow3[$i]["SORT_NO"] != '&nbsp'){
					array_push($favorites_data,$datarow3[$i]);
				}
				array_push($proj_data_sub,$datarow3[$i]);
			}
			$this->smarty->assign('proj_data_sub',$proj_data_sub);
			$this->smarty->assign('favorites_data',$favorites_data);
			$this->smarty->display("intranet/common_contents/work_manhour/ManageProject_mvc.tpl");
		}

		function manage_project_sub(){
			$dbinsert = true; // true false
			extract($_REQUEST);

			$sdate = str_replace("-", "", $sdate);
			$edate = str_replace("-", "", $edate);
			if($proj_type == 'pm'){ $real_dept_code = 'all'; }

			$prosql ="BEGIN USP_MH_MANAGE_PROJ_MEMBERS(:entries, '$project_code', '$real_dept_code', '$sdate', '$edate'); END;";

			if($dbinsert){
				$datarow = $this->oracle->LoadProcedure($prosql,"list_data0","");
				$member_data = array();
				$dept_data = array();
				$DEPT_NAME = '';
				$DEPT_MH = 0;
				$TOTAL_MH = 0;
				for($i=0; $i<count($datarow); $i++){
					array_push($member_data,$datarow[$i]);
					if($DEPT_NAME != $datarow[$i]["DEPT_NAME"]){
						$dept_data[$DEPT_NAME] = $DEPT_MH;
						$TOTAL_MH += $DEPT_MH;
						$DEPT_MH = 0;
					}
					$DEPT_NAME = $datarow[$i]["DEPT_NAME"];
					$DEPT_MH += (double)$datarow[$i]["MH"];
				}
				$TOTAL_MH += $DEPT_MH;
				$dept_data[$DEPT_NAME] = $DEPT_MH;
				//print_r($dept_data);
				$this->smarty->assign('member_data',$member_data);
				$this->smarty->assign('dept_data',$dept_data);
				$this->smarty->assign('TOTAL_MH',$TOTAL_MH);
			}else{
				print_r($_REQUEST);
				echo $prosql;
			}

			$this->smarty->display("intranet/common_contents/work_manhour/ManageProject_sub_mvc.tpl");
		}

		function manage_project_favorites(){
			$dbinsert = true; // true false
			extract($_REQUEST);
			$prosql ="BEGIN USP_MH_MANAGE_PROJ_FAVORITES( '$manager_empno', '$proj_code', '$solt_no' ); END;";
			if($dbinsert){
				//$datarow1 = $this->oracle->LoadProcedure($prosql,"list_data01","");
				$this->oracle->ProcedureExcuteQuery($prosql);
			}else{
				print_r($_REQUEST);
				echo "oracle : ".$prosql."<br>";
			}
		}

		function leader_main(){
			$dbinsert = true; // true false
			extract($_REQUEST);
			//print_r($_REQUEST);

			$prosql1 = "BEGIN USP_MH_LEADERS_INFO(:entries,'$real_dept_code'); END;";
			//echo $prosql1 ;
			$datarow1 = $this->oracle->LoadProcedure($prosql1,"list_data01","");
			$list_data = array();
			for($i=0; $i<count($datarow1); $i++) {
				array_push($list_data,$datarow1[$i]);
			}//for

			$this->smarty->assign('list_data',$list_data);
			$this->smarty->display("intranet/common_contents/work_manhour/LeaderMain_mvc.tpl");
		}

		function leader_favorites(){
			$dbinsert = true; // true false
			extract($_REQUEST);
			$prosql ="BEGIN USP_MH_LEADER_FAVORITES( '$empno', '$solt_no' ); END;";
			if($dbinsert){
				//$datarow1 = $this->oracle->LoadProcedure($prosql,"list_data01","");
				$this->oracle->ProcedureExcuteQuery($prosql);
			}else{
				print_r($_REQUEST);
				echo "oracle : ".$prosql."<br>";
			}
		}
		




		//맨아워관리st----------------------------------------
		function edit_main(){
			$dbinsert = true; // true false
			extract($_REQUEST);
			//print_r($_REQUEST);
			//시작
			$sdate = date("Y-m-d");
			$this->smarty->assign('sdate',$sdate);
			//종료
			$edate = date("Y-m-d");
			$this->smarty->assign('edate',$edate);

			$prosql1 ="BEGIN Usp_MH_Employee_Info(:entries,'$memberID'); END;";
			$datarow1 = $this->oracle->LoadProcedure($prosql1,"list_data01","");
			
			$dept_list = array();
			for($i=0; $i<count($datarow1); $i++) {
				array_push($dept_list,$datarow1[$i]);
			}//for
			$this->smarty->assign('dept_list',$dept_list);

			$this->smarty->assign('list_data',$list_data);
			$this->smarty->display("intranet/common_contents/work_manhour/EditMain_mvc.tpl");
		}

		function edit_get_member(){
			global $db;
			$dbinsert = true; // true false
			extract($_REQUEST);

			$prosql1 ="BEGIN USP_PM_MANHOUR_member(:entries,'$dept_code'); END;";
			$datarow1 = $this->oracle->LoadProcedure($prosql1,"list_data01","");

			print_r(json_encode($datarow1));
		}

		function edit_view(){
			extract($_REQUEST);
			$sdate = str_replace("-", "", $sdate);
			$tar_sdate = substr($sdate, 0, 4).'-'.substr($sdate, 4, 2).'-'.substr($sdate, 6, 2);

			if($edate == '' or $edate == 'undefined' or $edate == null){
				$edate = $sdate;
			}
			$edate = str_replace("-", "", $edate);
			$tar_edate = substr($edate, 0, 4).'-'.substr($edate, 4, 2).'-'.substr($edate, 6, 2);


			if($real_member_code == "ALL"){
				//전체일경우
				$prosql1 ="BEGIN USP_PM_MANHOUR_member(:entries,'$real_dept_code'); END;";
				$datarow1 = $this->oracle->LoadProcedure($prosql1,"list_data01","");

				for($i=0; $i<count($datarow1); $i++) {
					$codes[CODE] = $datarow1[$i]["CODE"];
					//print_R("</br>[".$codes[CODE]."]</br>");
					mh_update_hanmac( $codes[CODE], $tar_sdate, $tar_edate, $check_type );
				}
			}else{
				mh_update_hanmac( $real_member_code, $tar_sdate, $tar_edate, $check_type );
			}

//print_r($_REQUEST);
//print_r("</br></br>");
//print_r($real_member_code."|".$tar_sdate."|".$tar_edate."|".$check_type."</br></br>");

			//mh_update( $real_member_code, $tar_sdate, $tar_edate, $check_type );
			//mh_update_hanmac( $real_member_code, $tar_sdate, $tar_edate, $check_type );
			if($check_type == 'satis'){	//satis에서 근태입력에서 호출하면 창닫기.
				?>
					<script type="text/javascript">
					<!--
						window.close();
					//-->
					</script>
				<?
			}
		}

		function proj_permission_test(){
			global $db;
			$dbinsert = true; // true false
			extract($_REQUEST);


			$ResultValue="";


			if($empno=="B14306"){

				//$empno="M03228";
				//$empno="M18310";

			}

			$cnt=0;
			$prosql1 ="BEGIN USP_HR_PERS_MASTER_COUNT (:entries, '$empno' ); END;";
			if($dbinsert){
				$datarow11 = $this->oracle->LoadProcedure($prosql1,"list_data01","");
				$cnt = $datarow11[0][item01];
			}

			if($cnt>0){
					$check_product = 1; //1=관리부서, 0=일반부서
					//echo $product_code;
					if($product_code == 'R' or $product_code == 'M'){ //프로젝트 코드 둘째자리가 'R' 또는 'M'
						$prosql ="BEGIN USP_PM_MANHOUR_CHECK_1 (:entries, '$empno' ); END;";
						if($dbinsert){
							$datarow1 = $this->oracle->LoadProcedure($prosql,"list_data01","");
							$check_product = $datarow1[0][0];
							//print_r($datarow1[0][0]);
						}else{
							print_r($_REQUEST);
							echo "oracle : ".$prosql."<br>";
						}
					}

					//$sql  = "SELECT oldProjectCode from project_tbl where ProjectCode = '$Proj_Code'";// 삼안 : ProjectCode==SR00SW06
					$sql  = "SELECT oldProjectCode, projectName from project_tbl where NewProjectCode = '$Proj_Code'";//한맥 : NewProjectCode==HM008301

					//echo $sql;
					$result = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($result)) {
						$oldProjectCode = $re_row[oldProjectCode]; // ZZZZZZ  == SR00SW06
						$projectName = $re_row[projectName];
					}//while

					if($check_product == 0){ //[일반부서]인원이 관리부서 프로젝트(연구개발== "R",  경영지원== "M")선택시 FALSE
						//echo 'zzzz';
						$ResultValue="FALSE : 일반인원이 관리부서 프로젝트선택";
						echo 0;

					}elseif($oldProjectCode == "" or $oldProjectCode == "0"){
						$ResultValue="TRUE : oldProjectCode값이 널또는 0";
						echo 1;
					}else{
						$sql  = "SELECT GroupCode from member_tbl where MemberNo = '$empno'"; //
						//감리 체크
						//echo $sql;
						$result = mysql_query($sql,$db);
						while($re_row = mysql_fetch_array($result)) {
							$GroupCode = $re_row[GroupCode];
						}//while
						//echo 'zzzz';

						//if($GroupCode == '102' or $GroupCode == '103'){//삼안 : 건설사업관리-대기 == '102' or 건설사업관리-현장 == '103'
						if($GroupCode == '31' || $GroupCode == '1' ){ //한맥 : 감리부(31), 임원실(1) //모든 프로젝트 선택 가능 부서 설정 : 해당부서의 인원은 모든프로젝트 선택가능(SATIS프로그램에서 별도로 맨아워 처리 )
							$ResultValue="TRUE : 예외부서(임원,감리)인원";
							echo 1;
						}else{
							$prosql ="BEGIN USP_PM_MANHOUR_CHECK(:entries, '$empno', '$oldProjectCode' ); END;";
							if($dbinsert){
								$datarow1 = $this->oracle->LoadProcedure($prosql,"list_data01","");

								$ResultValue=$datarow1[0][0];
								if($ResultValue=="0"){
									$ResultValue="FALSE : 미배분 프로젝트 또는 선택권한 없음";
								}else{
									$ResultValue="TRUE : COUNT=".$datarow1[0][0]." : 선택성공";
								}

								print_r($datarow1[0][0]);
							}else{
								print_r($_REQUEST);
								echo "oracle : ".$prosql."<br>";
							}
						}
					}

					$setValue = $Proj_Code.' : '.$projectName;
					$azSQL = "insert into test_tbl (regdate,memberid, etc_01, etc_02, etc_03, etc_04, etc_05) values (SYSDATE(),'$empno', '$check_product', $GroupCode, '$ResultValue', '$oldProjectCode', '$setValue')";
					$azRecord2 = mysql_query($azSQL,$db);

			}else{

				//alert("한맥ERP에 등록되지 않은 인원입니다.");
				$azSQL = "insert into test_tbl (regdate,memberid, etc_01, etc_02, etc_03, etc_04, etc_05) values (SYSDATE(),'$empno', '한맥ERP에 인원등록필요',  '', '', '', '$Proj_Code')";
				$azRecord2 = mysql_query($azSQL,$db);

				echo 99;

			}

		}//proj_permission


		function proj_permission(){
			global $db;
			$dbinsert = true; // true false
			extract($_REQUEST);

			$ResultValue="";



			$cnt=0;
			$prosql1 ="BEGIN USP_HR_PERS_MASTER_COUNT (:entries, '$empno' ); END;";
			if($dbinsert){
				$datarow11 = $this->oracle->LoadProcedure($prosql1,"list_data01","");
				$cnt = $datarow11[0][item01];
			}else{
			}




			if($cnt>0){

					$check_product = 1; //1=관리부서, 0=일반부서
					if($product_code == 'R' or $product_code == 'M'){ //프로젝트 코드 둘째자리가 'R' 또는 'M'
						$prosql ="BEGIN USP_PM_MANHOUR_CHECK_1 (:entries, '$empno' ); END;";
						if($dbinsert){
							$datarow1 = $this->oracle->LoadProcedure($prosql,"list_data01","");
							$check_product = $datarow1[0][0];
							if($check_product=="null"){
								$check_product = 0;
							}
							//print_r($datarow1[0][0]);
						}else{

						}
					}

					//$sql  = "SELECT oldProjectCode from project_tbl where ProjectCode = '$Proj_Code'";// 삼안 : ProjectCode==SR00SW06
					$sql  = "SELECT oldProjectCode, projectName from project_tbl where NewProjectCode = '$Proj_Code'";//한맥 : NewProjectCode==HM008301

					//echo $sql;
					$oldProjectCode="";
					$projectName="";
					$result = mysql_query($sql,$db);
					while($re_row = mysql_fetch_array($result)) {
						$oldProjectCode = $re_row[oldProjectCode]; // ZZZZZZ  == SR00SW06
						$projectName = $re_row[projectName];
					}//while

					if($check_product == 0){ //[일반부서]인원이 관리부서 프로젝트(연구개발== "R",  경영지원== "M")선택시 FALSE
						//echo 0;
						$ResultValue="FALSE : 일반인원이 관리부서 프로젝트선택";
					}elseif($oldProjectCode == "" or $oldProjectCode == "0"){
						//echo 1;
						$ResultValue="TRUE : oldProjectCode값이 널또는 0";
					}else{
						$sql  = "SELECT GroupCode from member_tbl where MemberNo = '$empno'"; //
						//감리 체크
						//echo $sql;
						$result = mysql_query($sql,$db);
						while($re_row = mysql_fetch_array($result)) {
							$GroupCode = $re_row[GroupCode];
						}//while

						//if($GroupCode == '102' or $GroupCode == '103'){//삼안 : 건설사업관리-대기 == '102' or 건설사업관리-현장 == '103'
						if($GroupCode == '31' || $GroupCode == '1' ){ //한맥 : 감리부(31), 임원실(1) //모든 프로젝트 선택 가능 부서 설정 : 해당부서의 인원은 모든프로젝트 선택가능(SATIS프로그램에서 별도로 맨아워 처리 )
							$ResultValue="TRUE : 예외부서(임원,감리)인원";
						}else{
							$prosql ="BEGIN USP_PM_MANHOUR_CHECK(:entries, '$empno', '$oldProjectCode' ); END;";
							if($dbinsert){
								$datarow1 = $this->oracle->LoadProcedure($prosql,"list_data01","");
								//print_r($datarow1[0][0]);
								$ResultValue=$datarow1[0][0];

								if($ResultValue=="0"){
									$ResultValue="FALSE : 미배분 프로젝트 또는 선택권한 없음";
								}else{
									$ResultValue="TRUE : COUNT=".$datarow1[0][0]." : 선택성공";
								}

							}else{

							}
						}
					}

					$setValue = $Proj_Code.' : '.$projectName;
					$azSQL = "insert into test_tbl (regdate,memberid, etc_01, etc_02, etc_03, etc_04, etc_05) values (SYSDATE(),'$empno', '$check_product', $GroupCode, '$ResultValue', '$oldProjectCode', '$setValue')";
					$azRecord2 = mysql_query($azSQL,$db);

					echo "1";
			}else{

				//alert("한맥ERP에 등록되지 않은 인원입니다.");
				$azSQL = "insert into test_tbl (regdate,memberid, etc_01, etc_02, etc_03, etc_04, etc_05) values (SYSDATE(),'$empno', '한맥ERP에 인원등록필요',  '', '', '', '$Proj_Code')";
				$azRecord2 = mysql_query($azSQL,$db);

				//echo 99;
				echo "1";
			}
		}//proj_permission_test




		function manhour_insert(){
			header("Content-Type: text/html; charset=UTF-8");

			global $db;
			$dbinsert = true; // true false
			extract($_REQUEST);
			if($date == null or $date == ''){
				$date = date("Y-m-d", strtotime("-1 day", time()));
				$yesterday = date("Y-m-d", strtotime("-2 day", time()));
			}
			$sql  = "
				SELECT
					A.MemberNo
				FROM
					(
						SELECT
							MemberNo
						FROM
							member_tbl
						WHERE
							WorkPosition <> '9'
							or LeaveDate > '$date'
					) A
					LEFT JOIN
					(
						SELECT
							MemberNo
						FROM
							dallyproject_tbl BB
						WHERE
							EntryTime like '$date%'
							AND LeaveTime = '0000-00-00'
							AND 1 > (
								SELECT
									COUNT(memberno)
								FROM
									official_plan_tbl
								WHERE
									memberno like BB.MemberNo
									AND o_start like '$date%'
							)

					) B
				ON A.MemberNo = B.MemberNo
				WHERE B.MemberNo IS NULL
			";
			if(!$dbinsert){
				echo "재직중이면서 업무시작은 하고 업무종료를 안누를 사람들 제외<br>";
				echo $sql."<br>";
			}
			$result = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($result)) {
				//echo "mh_update( '$re_row[MemberNo]', '$date', '$date', 'arrangement' )<br>";
				if($dbinsert){
					mh_update( $re_row[MemberNo], $date, $date, 'arrangement' );
				}else{
					echo "<br>MemberNo : ".$re_row[MemberNo].",date : ".$date;
				}
			}//while

			if($yesterday != null){
				$sql  = "
					SELECT
						A.MemberNo
					FROM
						(
							SELECT
								MemberNo
							FROM
								member_tbl
							WHERE
								WorkPosition <> '9'
								or LeaveDate > '$yesterday'
						) A
						LEFT JOIN
						(
							SELECT
								MemberNo
							FROM
								dallyproject_tbl BB
							WHERE
								EntryTime like '$yesterday%'
								AND LeaveTime = '0000-00-00'
								AND 1 > (
									SELECT
										COUNT(memberno)
									FROM
										official_plan_tbl
									WHERE
										memberno like BB.MemberNo
										AND o_start like '$yesterday%'
								)

						) B
					ON A.MemberNo = B.MemberNo
					WHERE B.MemberNo IS NULL
				";
				if(!$dbinsert){
					echo $sql."<br>";
				}
				$result = mysql_query($sql,$db);
				while($re_row = mysql_fetch_array($result)) {
					//echo "mh_update( '$re_row[MemberNo]', '$yesterday', '$yesterday', 'arrangement' )<br>";
					if($dbinsert){
						mh_update( $re_row[MemberNo], $yesterday, $yesterday, 'arrangement' );
					}else{
						echo "<br>MemberNo : ".$re_row[MemberNo].",date : ".$date;
					}
				}//while
			}

			$log_txt = date("Y-m-d H:i:s",time()).",date:".$date.",yesterday:".$yesterday.",".$_SERVER['REMOTE_ADDR']."/n/r";
			$log_file = "../log/manhour_arrangement_log.txt";
			if(is_dir($log_file)){
				$log_option = 'w';
			}else{
				$log_option = 'a';
			}

			$log_file = fopen($log_file, $log_option);
			fwrite($log_file, $log_txt."\r\n");
			fclose($log_file);

			//echo trim(ICONV("UTF-8","EUC-KR","MH 입력이 완료되었습니다. // ".$now_time = date("Y-m-d H:i:s",time())));
			echo "MH 입력이 완료되었습니다".$now_time = date("Y-m-d H:i:s",time());
		}

		function manhour_insert_test($get_date = null){
			global $db;
			$dbinsert = true; // true false
			extract($_REQUEST);
			if($get_date != null){
				$date = $get_date;
			}
			$sql  = "
				SELECT
					A.MemberNo
				FROM
					(
						SELECT
							MemberNo
						FROM
							member_tbl
						WHERE
							WorkPosition <> '9'
							or LeaveDate > '$yesterday'
					) A
					LEFT JOIN
					(
						SELECT
							MemberNo
						FROM
							dallyproject_tbl BB
						WHERE
							EntryTime like '$yesterday%'
							AND LeaveTime = '0000-00-00'
							AND 1 > (
								SELECT
									COUNT(memberno)
								FROM
									official_plan_tbl
								WHERE
									memberno like BB.MemberNo
									AND o_start like '$yesterday%'
							)

					) B
				ON A.MemberNo = B.MemberNo
				WHERE B.MemberNo IS NULL
			";
			if(!$dbinsert){
				echo "재직중이면서 업무시작은 하고 업무종료를 안누를 사람들 제외<br>";
				echo $sql."<br>";
			}
			$result = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($result)) {
				if($dbinsert){
					mh_update( $re_row[MemberNo], $date, $date, 'arrangement' );
				}else{
					echo "<br>MemberNo : ".$re_row[MemberNo].",date : ".$date;
				}
			}//while

			//echo trim(ICONV("UTF-8","EUC-KR","MH 입력이 완료되었습니다. // ".$now_time = date("Y-m-d H:i:s",time())));
			echo "<br>MH 입력이 완료되었습니다".$now_time = date("Y-m-d H:i:s",time());
		}

		function month_manhour_insert(){
			global $db;
			$dbinsert = true; // true false
			extract($_REQUEST);

			if(strlen($date) == 10){
				$end_day = date("t", strtotime($date));
				for($count = 1; $count <= $end_day; $count++){
					//$insert_date = date("Y-m-d", strtotime($date."+".$count."day"));
					$insert_date = substr($date, 0, 7);
					if($count < 10){
						$insert_date .= "-0".$count;
					}else{
						$insert_date .= "-".$count;
					}
					echo $insert_date."<br>";
					//$this->manhour_insert_test($insert_date);
				}
			}else{
				echo $date." 날짜오류";
			}
		}


		function manage_check(){
			global $db;
			$dbinsert = true; // true false
			extract($_REQUEST);
			//$memberID = '201030';//216008 199093 201030

			$prosql ="BEGIN Usp_MH_Employee_Info(:entries,'$memberID'); END;";
			$datarow = $this->oracle->LoadProcedure($prosql,"list_data01","");
			//print_r($datarow);
			//print_r($datarow[0]);
			if($datarow[0]['TITLE_CODE'] == '020' or $datarow[0]['TITLE_CODE'] == '030' or $datarow[0]['MANAGER_EMP'] <> '&nbsp'){
				echo 1;
			}else{
				echo 0;
			}
		}

		function no_insert_check(){
			global $db;
			$dbinsert = true; // true false
			extract($_REQUEST);
			$prosql ="BEGIN USP_PM_MANHOUR_END_CHECK(:entries); END;";

			$datarow = $this->oracle->LoadProcedure($prosql,"list_data01","");
			$finish_date = substr($datarow[0]['END_DATE'], 0, 6);
			if($memberID == '216070'){
				//echo $finish_date;
			}
			//echo "finish_date : ".$finish_date;
			$now_day = date("d", time());	//이번달 일 구하기
			$now_month = date("Ym", time());	//이번달 월 구하기
			$now_time = mktime(0, 0, 0, substr($now_month, 4, 2), 1, substr($now_month, 0, 4) );	//이번달 1일 설정
			$check_month = date("Ym", strtotime("-1 month", $now_time));	//이번달 1일 한달전 구하기
			//echo "check_month : ".$check_month;
			if($finish_date <> $check_month and $now_day > 1){
				$prosql ="BEGIN Usp_MH_INSERT_CHECK(:entries,'$memberID', '$check_month', '".($check_month+1)."'); END;";
				$datarow = $this->oracle->LoadProcedure($prosql,"list_data01","");
				if(count($datarow) > 0){
					print_r(json_encode($datarow));
				}
				/*
				$prosql ="BEGIN Usp_MH_INSERT_CHECK(:entries,'$memberID', '$check_month', '".($check_month+1)."'); END;";
				$datarow = $this->oracle->LoadProcedure($prosql,"list_data01","");
				//print_r($datarow);
				if(count($datarow) > 0){
					if($datarow[0]['NO_IN'] != null and $datarow[0]['NO_IN'] <> '&nbsp' and $datarow[0]['NO_IN'] > 0){
						echo $check_month.'-'.$datarow[0]['PT'].'-'.$datarow[0]['NO_IN'];
					}
				}
				*/
			}
		}
	}
?>