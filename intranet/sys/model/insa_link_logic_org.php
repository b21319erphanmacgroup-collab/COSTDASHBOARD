<?php

	/******************************************************************************
	* 삼안 SATIS에서 변경된 인사관련된 정보를 인트라넷에 적용하기 위한 클래스
	* -----------------------------------------------------------------------------
	*  작업일자   |  작업자   | 작업 내용
	* 2016-04-06  |  장계석   | 프로젝트 생성 및 기능구현
	* 2018-10-12  |  정명준   | 휴직일 경우 work_position을 2로 수정하도록 select order_no from HR_ORDE_MASTER where emp_no = '$emp_no' and final_tag = 'Y' 추가, $workstatus = "2" if문 추가
	*******************************************************************************/
	include "../inc/dbcon.inc";
	include "../model/OracleClass.php";
	include "../../../SmartyConfig.php";

	extract($_REQUEST);
	class InsaLinkLogic {
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

		function InsaLinkLogic($smarty)
		{
			global $emp_id;
			$this->oracle=new OracleClass($smarty);

			$this->smarty=$smarty;

			$this->PRINTYN=$_REQUEST['PRINT'];
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


			$this->QueryDay="'".$QueryStartDate."'";

			$ActionMode=$_REQUEST['ActionMode'];
			$this->smarty->assign('ActionMode',$ActionMode);
			$this->smarty->assign('start_day',substr($this->start_day,0,4).".".substr($this->start_day,4,2).".".substr($this->start_day,6,2));
			$this->smarty->assign('end_day',substr($this->end_day,0,4).".".substr($this->end_day,4,2).".".substr($this->end_day,6,2));
			//$this->smarty->assign('end_day',$this->end_day);

		}

		//=================================================
		// 로그인에 대한 정보
		//=================================================
		function LinkProcess()
		{
			global $db;
			extract($_REQUEST);
			$azsql ="select * from HR_PERS_MASTER_MAPPING order by MODE_TAG";
			//echo $azsql;
			$this->oracle->ChangeDBConnection();
			$datalist=$this->oracle->LoadData($azsql,"");

			//print_r($datalist);


			for($index=0;$index<count($datalist);$index++)
			{
				$mode=$datalist[$index]['MODE_TAG'];
				$emp_no=$datalist[$index]['EMP_NO'];
				$contents=$datalist[$index]['CONTENTS'];

				$azsql ="select
					(select GRADE_NAME from HR_CODE_GRADE where a.GRADE_CODE=GRADE_CODE) as GRADE_NAME,
					(select USER_PASSWORD from SM_AUTH_USER where a.EMP_NO=USER_ID) as USER_PASSWORD,
					(select max(proj_code) from CS_CONT_MAP_MASTER where a.dept_code = proj_org_code ) as SITECODE,
					(select dept_name from HR_CODE_DEPT where a.real_dept_code = dept_code ) as DEPT_NAME,
					(select order_no from HR_ORDE_MASTER where emp_no = '$emp_no' and final_tag = 'Y' and confirm_tag = 'Y') as ORDER_NO,
					(select ERP_DEPT_CODE from HR_ORDE_MASTER where emp_no = '$emp_no' and final_tag = 'Y' and confirm_tag = 'Y') as ERP_DEPT_CODE,
					a.*  from HR_PERS_MASTER a where a.emp_no='$emp_no'";
					//echo $azsql;

				$onedata=$this->oracle->LoadData($azsql,"");
				$retire_date=$onedata[0]['RETIRE_DATE'];
				$user_password=$onedata[0]['USER_PASSWORD'];
				$dept_code=$onedata[0]['ERP_DEPT_CODE'];
				$grade_name=$this->HangleEncode($onedata[0]['GRADE_NAME']);

				// 부서가 변경된 정보를 저장하는 기능
				/*
				$dept_code="";
				$dept_name = $this->HangleEncode($onedata[0]['DEPT_NAME']);
				$azsql =  "select * from systemconfig_tbl where syskey='GroupCode' and description='$dept_name'";
				$re = mysql_query($azsql,$db);
				if($re_row = mysql_fetch_array($re))
					$dept_code=$re_row[Code];
				*/

				switch($mode)
				{
					case "0":
						//==========================================
						// 퇴사/입사/직급 정보를 갱신하는 부분
						//==========================================
						$workstatus="1";
						if($onedata[0]['ORDER_NO'] == '51' or $onedata[0]['ORDER_NO'] == '52' or $onedata[0]['ORDER_NO'] == '53' or $onedata[0]['ORDER_NO'] == '54' or $onedata[0]['ORDER_NO'] == '55' or $onedata[0]['ORDER_NO'] == '56' or $onedata[0]['ORDER_NO'] == '57' or $onedata[0]['ORDER_NO'] == '58' or $onedata[0]['ORDER_NO'] == '59'){	//휴직, 휴가(출산), 병가 일 경우
							$workstatus = "2";
						}
						if($retire_date !="")
							$workstatus ="9";

						$RandCode="E00";
						$azsql =  "select * from systemconfig_tbl where syskey='PositionCode' and name='$grade_name'";
						$re = mysql_query($azsql,$db);
						if($re_row = mysql_fetch_array($re))
							$RandCode=$re_row[Code];


						$azsql =  "select * from member_tbl where memberNo='$emp_no'";
						$re = mysql_query($azsql,$db);
						if(mysql_num_rows($re) >0)
						{
							$memberNo="$emp_no";
							$pasword="$user_password";
							$WorkPosition="1";
							$RankCode=$RandCode;
							$korName=$this->HangleEncode($onedata[0]['EMP_NAME']);
							$chiName=$this->HangleEncode($onedata[0]['EMPNAME_CHI']);
							$engName=$this->HangleEncode($onedata[0]['EMPNAME_ENG']);

							if($onedata[0]['SEX_DIV'] ==  "1")
								$Gender="M";
							else
								$Gender="F";
							$detailcode="1";
							$employcode="2";
							$joincode="2";
							$entrydate=$onedata[0]['JOIN_DATE'];
							if($onedata[0]['RRN_PRE'] !="")
								$juminno=$onedata[0]['RRN_PRE']."-".$onedata[0]['RRN_POST'];
							else
								$juminno="";
							$phone=str_replace(" ","",$onedata[0]['PHONE_PRE']).str_replace(" ","",$onedata[0]['PHONE_POST']);
							$mobile=$onedata[0]['CELL_PRE'].$onedata[0]['CELL_POST'];
							$email=$onedata[0]['E_MAIL'];
							$orignaddress=$this->HangleEncode($onedata[0]['RRN_ADDR1'])." ".$this->HangleEncode($onedata[0]['RRN_ADDR2']);
							$address=$this->HangleEncode($onedata[0]['ADDR1'])." ".$this->HangleEncode($onedata[0]['ADDR2']);

							$birthday=$onedata[0]['BIRTHDAY'];

							$sitecode=$onedata[0]['SITECODE'];
							$dutycode=$onedata[0]['TITLE_CODE'];

							$access_check="1";

								$azsql =  "update member_tbl set
									RankCode='$RandCode',
									WorkPosition='$workstatus',
									leaveDate='$retire_date',
									pasword='$user_password',
									korName= '$korName',
									chiName= '$chiName',
									engName= '$engName',
									Gender= '$Gender',
									detailcode= '$detailcode',
									employcode= '$employcode',
									joincode= '$joincode',
									entrydate= '$entrydate',
									juminno= '$juminno',
									phone= '$phone',
									mobile= '$mobile',
									email= '$email',
									orignaddress= '$orignaddress',
									address= '$address',
									birthday=       '$birthday',
									SiteCode=       '$sitecode',
									DutyCode=       '$dutycode'
								where memberNo='$emp_no'";
								//$this->insert_insa_log($azsql);
								mysql_query($azsql);
						}
						else
						{

							$memberNo="$emp_no";
							$pasword="$user_password";
							$WorkPosition="1";
							$RankCode=$RandCode;
							$korName=$this->HangleEncode($onedata[0]['EMP_NAME']);
							$chiName=$this->HangleEncode($onedata[0]['EMPNAME_CHI']);
							$engName=$this->HangleEncode($onedata[0]['EMPNAME_ENG']);

							if($onedata[0]['SEX_DIV'] ==  "1")
								$Gender="M";
							else
								$Gender="F";
							$detailcode="1";
							$employcode="2";
							$joincode="2";
							$entrydate=$onedata[0]['JOIN_DATE'];
							if($onedata[0]['RRN_PRE'] !="")
								$juminno=$onedata[0]['RRN_PRE']."-".$onedata[0]['RRN_POST'];
							else
								$juminno="";
							$phone=str_replace(" ","",$onedata[0]['PHONE_PRE']).str_replace(" ","",$onedata[0]['PHONE_POST']);
							$mobile=$onedata[0]['CELL_PRE'].$onedata[0]['CELL_POST'];
							$email=$onedata[0]['E_MAIL'];
							$orignaddress=$this->HangleEncode($onedata[0]['RRN_ADDR1'])." ".$this->HangleEncode($onedata[0]['RRN_ADDR2']);
							$address=$this->HangleEncode($onedata[0]['ADDR1'])." ".$this->HangleEncode($onedata[0]['ADDR2']);

							$birthday=$onedata[0]['BIRTHDAY'];

							$sitecode=$onedata[0]['SITECODE'];
							$dutycode=$onedata[0]['TITLE_CODE'];

							$access_check="1";
							//================================================
							// 신규 직원에 대한 정보를 추가 한다
							//================================================
							$azsql="insert into member_tbl(memberNo,pasword,WorkPosition,RankCode,korName
								,chiName,engName,Gender,detailcode,employcode,joincode
								,entrydate,juminno,phone,mobile,email,orignaddress,address,birthday,SiteCode,DutyCode,access_check,orderseq
								,groupcode
								)
								values
								(
								'$memberNo',
								'$pasword',
								'$WorkPosition',
								'$RankCode',
								'$korName',
								'$chiName',
								'$engName',
								'$Gender',
								'$detailcode',
								'$employcode',
								'$joincode',
								'$entrydate',
								'$juminno',
								'$phone',
								'$mobile',
								'$email',
								'$orignaddress',
								'$address',
								'$birthday',
								'$sitecode',
								'$dutycode',
								'$access_check',
								'0',
								'$dept_code'
								)";

							$this->insert_insa_log($azsql);
							mysql_query($azsql);
							//echo $azsql;
						}
						$azsql="update systemconfig_tbl set name=now() where syskey='PhoneBook-Update'";
						$this->insert_insa_log($azsql);
						mysql_query($azsql);

						break;
					case "1":
						// 휴가 및 출장에 관련된 내용

						$item=split(",", $contents);

						$querytime=$item[0];
						$query_project=$item[1];

						$azsql ="select * from HR_TIME_HOLIDAYS where emp_no='$emp_no' and  LABSTA_FROM_DATE='$querytime'";
						$itemdata=$this->oracle->LoadData($azsql,"");

						$azsql =  "select * from member_tbl where memberNo='$emp_no'";
						$re = mysql_query($azsql,$db);
						if($re_row = mysql_fetch_array($re))
							$groupcode=$re_row[GroupCode];


						for($row_index=0;$row_index<count($itemdata);$row_index++)
						{
							$status_mode=$itemdata[$row_index]['LABSTA_CODE'];
							$start_time=$itemdata[$row_index]['LABSTA_FROM_DATE'];
							$end_time=$itemdata[$row_index]['LABSTA_TO_DATE'];
							$note=$this->HangleEncode($itemdata[$row_index]['REASON']);

							$status="";
							$active_code="f";
							switch($status_mode)
							{
								case "22":			// 년차
									$status="01";
									$projectcode="SV".substr($start_time,2,2)."9104";
									break;
								case "25":			// 반차
									$status="01";
									$projectcode="SV".substr($start_time,2,2)."9104";
									break;
								case "31":			// 출장
									$status="03";
									$active_code="d";
									$projectcode=$query_project;
									break;
								case "34":			// 훈련
									$status="05";
									$active_code="a";
									$projectcode="SV".substr($start_time,2,2)."9101";
									break;

							}
							if($status != "")
							{
								$azsql="insert into userstate_tbl(memberno,groupcode,state,start_time,end_time,projectcode,note,active_code) values(
									'$emp_no','$groupcode','$status','$start_time','$end_time','$projectcode','$note','$active_code')";
								$this->insert_insa_log($azsql);
								mysql_query($azsql);

							}
						}

						break;
					case "2":
						break;
					case "3":
						break;
					case "4":

						/*	윗부분에서 처리
						// 부서가 변경된 정보를 저장하는 기능
						$dept_code="";
							$dept_name=$this->HangleEncode($contents);
							$azsql =  "select * from systemconfig_tbl where syskey='GroupCode' and name='$dept_name'";
							$re = mysql_query($azsql,$db);
							if($re_row = mysql_fetch_array($re))
								$dept_code=$re_row[Code];
						*/

						$RandCode="E00";
						$azsql =  "select * from systemconfig_tbl where syskey='PositionCode' and name='$grade_name'";
						$re = mysql_query($azsql,$db);
						if($re_row = mysql_fetch_array($re))
							$RandCode=$re_row[Code];

						$azsql="update member_tbl set
							RankCode='$RandCode'";

						if($dept_code !="")
						{
							$azsql.=", groupcode='$dept_code'";
						}
						else
						{
							echo  $this->HangleEncodeUTF8_EUCKR("부서 정보를 확인해 주시기 바랍니다.====>".$emp_no."<br>");
						}

						$azsql.="where memberno='$emp_no'";
						$this->insert_insa_log($azsql);
						mysql_query($azsql);

						$azsql="update systemconfig_tbl set name=now() where syskey='PhoneBook-Update'";
						$this->insert_insa_log($azsql);
						mysql_query($azsql);

						break;
				}
				// 수정된 정보에 대한 내용을 삭제한다
				$azsql ="delete from HR_PERS_MASTER_MAPPING  where emp_no='$emp_no' and mode_tag='$mode'";
				$this->insert_insa_log($azsql);
				$this->oracle->ProcedureExcuteQuery($azsql);

			}
			$this->oracle->db_close_oracle();

			echo $this->HangleEncodeUTF8_EUCKR("갱신이 완료되었습니다. // ".$now_time = date("Y-m-d H:i:s",time()));

		}


		//=================================================
		// 로그인에 대한 정보
		//=================================================
		function LinkProcess_test()
		{
			global $db;
			extract($_REQUEST);
			//$azsql ="select * from HR_PERS_MASTER_MAPPING order by MODE_TAG";
			//$azsql ="select * from HR_PERS_MASTER_MAPPING where EMP_NO IN ('B14306','M18201','M16208','M18501') order by MODE_TAG";
			$azsql ="select * from HR_PERS_MASTER_MAPPING where EMP_NO IN ('B14306') order by MODE_TAG";
			//echo $azsql;
			$this->oracle->ChangeDBConnection();
			$datalist=$this->oracle->LoadData($azsql,"");

			//print_r($datalist);


			for($index=0;$index<count($datalist);$index++)
			{
				$mode=$datalist[$index]['MODE_TAG'];
				$emp_no=$datalist[$index]['EMP_NO'];
				$contents=$datalist[$index]['CONTENTS'];

				$azsql ="select
					(select GRADE_NAME from HR_CODE_GRADE where a.GRADE_CODE=GRADE_CODE) as GRADE_NAME,
					(select USER_PASSWORD from SM_AUTH_USER where a.EMP_NO=USER_ID) as USER_PASSWORD,
					(select max(proj_code) from CS_CONT_MAP_MASTER where a.dept_code = proj_org_code ) as SITECODE,
					(select dept_name from HR_CODE_DEPT where a.real_dept_code = dept_code ) as DEPT_NAME,
					(select order_no from HR_ORDE_MASTER where emp_no = '$emp_no' and final_tag = 'Y' and confirm_tag = 'Y') as ORDER_NO,
					(select ERP_DEPT_CODE from HR_ORDE_MASTER where emp_no = '$emp_no' and final_tag = 'Y' and confirm_tag = 'Y') as ERP_DEPT_CODE,
					a.*  from HR_PERS_MASTER a where a.emp_no='$emp_no'";
					//echo $azsql;

				$onedata=$this->oracle->LoadData($azsql,"");
				$retire_date=$onedata[0]['RETIRE_DATE'];
				$user_password=$onedata[0]['USER_PASSWORD'];
				$dept_code=$onedata[0]['ERP_DEPT_CODE'];
				$grade_name=$this->HangleEncode($onedata[0]['GRADE_NAME']);

				// 부서가 변경된 정보를 저장하는 기능
				/*
				$dept_code="";
				$dept_name = $this->HangleEncode($onedata[0]['DEPT_NAME']);
				$azsql =  "select * from systemconfig_tbl where syskey='GroupCode' and description='$dept_name'";
				$re = mysql_query($azsql,$db);
				if($re_row = mysql_fetch_array($re))
					$dept_code=$re_row[Code];
				*/

				switch($mode)
				{
					case "0":
						//==========================================
						// 퇴사/입사/직급 정보를 갱신하는 부분
						//==========================================
						$workstatus="1";
						if($onedata[0]['ORDER_NO'] == '51' or $onedata[0]['ORDER_NO'] == '52' or $onedata[0]['ORDER_NO'] == '53' or $onedata[0]['ORDER_NO'] == '54' or $onedata[0]['ORDER_NO'] == '55' or $onedata[0]['ORDER_NO'] == '56' or $onedata[0]['ORDER_NO'] == '57' or $onedata[0]['ORDER_NO'] == '58' or $onedata[0]['ORDER_NO'] == '59'){	//휴직, 휴가(출산), 병가 일 경우
							$workstatus = "2";
						}
						if($retire_date !="")
							$workstatus ="9";

						$RandCode="E00";
						$azsql =  "select * from systemconfig_tbl where syskey='PositionCode' and name='$grade_name'";
						$this->insert_insa_log($azsql);
						$re = mysql_query($azsql,$db);
						if($re_row = mysql_fetch_array($re))
							$RandCode=$re_row[Code];


						$azsql =  "select * from member_tbl where memberNo='$emp_no'";
						$re = mysql_query($azsql,$db);
						if(mysql_num_rows($re) >0)
						{
							$memberNo="$emp_no";
							$pasword="$user_password";
							$WorkPosition="1";
							$RankCode=$RandCode;
							$korName=$this->HangleEncode($onedata[0]['EMP_NAME']);
							$chiName=$this->HangleEncode($onedata[0]['EMPNAME_CHI']);
							$engName=$this->HangleEncode($onedata[0]['EMPNAME_ENG']);

							if($onedata[0]['SEX_DIV'] ==  "1")
								$Gender="M";
							else
								$Gender="F";
							$detailcode="1";
							$employcode="2";
							$joincode="2";
							$entrydate=$onedata[0]['JOIN_DATE'];
							if($onedata[0]['RRN_PRE'] !="")
								$juminno=$onedata[0]['RRN_PRE']."-".$onedata[0]['RRN_POST'];
							else
								$juminno="";
							$phone=str_replace(" ","",$onedata[0]['PHONE_PRE']).str_replace(" ","",$onedata[0]['PHONE_POST']);
							$mobile=$onedata[0]['CELL_PRE'].$onedata[0]['CELL_POST'];
							$email=$onedata[0]['E_MAIL'];
							$orignaddress=$this->HangleEncode($onedata[0]['RRN_ADDR1'])." ".$this->HangleEncode($onedata[0]['RRN_ADDR2']);
							$address=$this->HangleEncode($onedata[0]['ADDR1'])." ".$this->HangleEncode($onedata[0]['ADDR2']);

							$birthday=$onedata[0]['BIRTHDAY'];

							$sitecode=$onedata[0]['SITECODE'];
							$dutycode=$onedata[0]['TITLE_CODE'];

							$access_check="1";

								$azsql =  "update member_tbl set
									RankCode='$RandCode',
									WorkPosition='$workstatus',
									leaveDate='$retire_date',
									pasword='$user_password',
									korName= '$korName',
									chiName= '$chiName',
									engName= '$engName',
									Gender= '$Gender',
									detailcode= '$detailcode',
									employcode= '$employcode',
									joincode= '$joincode',
									entrydate= '$entrydate',
									juminno= '$juminno',
									phone= '$phone',
									mobile= '$mobile',
									email= '$email',
									orignaddress= '$orignaddress',
									address= '$address',
									birthday=       '$birthday',
									SiteCode=       '$sitecode',
									DutyCode=       '$dutycode'
								where memberNo='$emp_no'";
								//$this->insert_insa_log($azsql);
								//mysql_query($azsql);
								//echo $azsql."<BR>";


								/*
										pasword='$user_password',
									Gender= '$Gender',
									detailcode= '$detailcode',
									employcode= '$employcode',
									joincode= '$joincode',
									SiteCode=       '$sitecode',
									DutyCode=       '$dutycode'

									*/
						}
						else
						{

							$memberNo="$emp_no";
							$pasword="$user_password";
							$WorkPosition="1";
							$RankCode=$RandCode;
							$korName=$this->HangleEncode($onedata[0]['EMP_NAME']);
							$chiName=$this->HangleEncode($onedata[0]['EMPNAME_CHI']);
							$engName=$this->HangleEncode($onedata[0]['EMPNAME_ENG']);

							if($onedata[0]['SEX_DIV'] ==  "1")
								$Gender="M";
							else
								$Gender="F";
							$detailcode="1";
							$employcode="2";
							$joincode="2";
							$entrydate=$onedata[0]['JOIN_DATE'];
							if($onedata[0]['RRN_PRE'] !="")
								$juminno=$onedata[0]['RRN_PRE']."-".$onedata[0]['RRN_POST'];
							else
								$juminno="";
							$phone=str_replace(" ","",$onedata[0]['PHONE_PRE']).str_replace(" ","",$onedata[0]['PHONE_POST']);
							$mobile=$onedata[0]['CELL_PRE'].$onedata[0]['CELL_POST'];
							$email=$onedata[0]['E_MAIL'];
							$orignaddress=$this->HangleEncode($onedata[0]['RRN_ADDR1'])." ".$this->HangleEncode($onedata[0]['RRN_ADDR2']);
							$address=$this->HangleEncode($onedata[0]['ADDR1'])." ".$this->HangleEncode($onedata[0]['ADDR2']);

							$birthday=$onedata[0]['BIRTHDAY'];

							$sitecode=$onedata[0]['SITECODE'];
							$dutycode=$onedata[0]['TITLE_CODE'];

							$access_check="1";
							//================================================
							// 신규 직원에 대한 정보를 추가 한다
							//================================================
							$azsql="insert into member_tbl(memberNo,pasword,WorkPosition,RankCode,korName
								,chiName,engName,Gender,detailcode,employcode,joincode
								,entrydate,juminno,phone,mobile,email,orignaddress,address,birthday,SiteCode,DutyCode,access_check,orderseq
								,groupcode
								)
								values
								(
								'$memberNo',
								'$pasword',
								'$WorkPosition',
								'$RankCode',
								'$korName',
								'$chiName',
								'$engName',
								'$Gender',
								'$detailcode',
								'$employcode',
								'$joincode',
								'$entrydate',
								'$juminno',
								'$phone',
								'$mobile',
								'$email',
								'$orignaddress',
								'$address',
								'$birthday',
								'$sitecode',
								'$dutycode',
								'$access_check',
								'0',
								'$dept_code'
								)";

							//$this->insert_insa_log($azsql);
							//mysql_query($azsql);
							//echo $azsql."<BR>";
						}
						$azsql="update systemconfig_tbl set name=now() where syskey='PhoneBook-Update'";
						//$this->insert_insa_log($azsql);
						//mysql_query($azsql);

						break;
					case "1":
						// 휴가 및 출장에 관련된 내용

						$item=split(",", $contents);

						$querytime=$item[0];
						$query_project=$item[1];

						$azsql ="select * from HR_TIME_HOLIDAYS where emp_no='$emp_no' and  LABSTA_FROM_DATE='$querytime'";
						$itemdata=$this->oracle->LoadData($azsql,"");

						$azsql =  "select * from member_tbl where memberNo='$emp_no'";
						$re = mysql_query($azsql,$db);
						if($re_row = mysql_fetch_array($re))
							$groupcode=$re_row[GroupCode];


						for($row_index=0;$row_index<count($itemdata);$row_index++)
						{
							$status_mode=$itemdata[$row_index]['LABSTA_CODE'];
							$start_time=$itemdata[$row_index]['LABSTA_FROM_DATE'];
							$end_time=$itemdata[$row_index]['LABSTA_TO_DATE'];
							$note=$this->HangleEncode($itemdata[$row_index]['REASON']);

							$status="";
							$active_code="f";
							switch($status_mode)
							{
								case "22":			// 년차
									$status="01";
									$projectcode="SV".substr($start_time,2,2)."9104";
									break;
								case "25":			// 반차
									$status="01";
									$projectcode="SV".substr($start_time,2,2)."9104";
									break;
								case "31":			// 출장
									$status="03";
									$active_code="d";
									$projectcode=$query_project;
									break;
								case "34":			// 훈련
									$status="05";
									$active_code="a";
									$projectcode="SV".substr($start_time,2,2)."9101";
									break;

							}
							if($status != "")
							{
								$azsql="insert into userstate_tbl(memberno,groupcode,state,start_time,end_time,projectcode,note,active_code) values(
									'$emp_no','$groupcode','$status','$start_time','$end_time','$projectcode','$note','$active_code')";
								$this->insert_insa_log($azsql);
								//mysql_query($azsql);

							}
						}

						break;
					case "2":
						break;
					case "3":
						break;
					case "4":

						/*	윗부분에서 처리
						// 부서가 변경된 정보를 저장하는 기능
						$dept_code="";
							$dept_name=$this->HangleEncode($contents);
							$azsql =  "select * from systemconfig_tbl where syskey='GroupCode' and name='$dept_name'";
							$re = mysql_query($azsql,$db);
							if($re_row = mysql_fetch_array($re))
								$dept_code=$re_row[Code];
						*/

						$RandCode="E00";
						$azsql =  "select * from systemconfig_tbl where syskey='PositionCode' and name='$grade_name'";
						$re = mysql_query($azsql,$db);
						if($re_row = mysql_fetch_array($re))
							$RandCode=$re_row[Code];

						$azsql="update member_tbl set
							RankCode='$RandCode'";

						if($dept_code !="")
						{
							$azsql.=", groupcode='$dept_code'";
						}
						else
						{
							echo  $this->HangleEncodeUTF8_EUCKR("부서 정보를 확인해 주시기 바랍니다.====>".$emp_no."<br>");
						}

						$azsql.="where memberno='$emp_no'";
						$this->insert_insa_log($azsql);
						//mysql_query($azsql);

						$azsql="update systemconfig_tbl set name=now() where syskey='PhoneBook-Update'";
						$this->insert_insa_log($azsql);
						//mysql_query($azsql);

						break;
				}
				// 수정된 정보에 대한 내용을 삭제한다
				$azsql ="delete from HR_PERS_MASTER_MAPPING  where emp_no='$emp_no' and mode_tag='$mode'";
				//$this->insert_insa_log($azsql);
				//$this->oracle->ProcedureExcuteQuery($azsql);

			}

			$this->oracle->db_close_oracle();
			echo $this->HangleEncodeUTF8_EUCKR("갱신이 완료되었습니다. // ".$now_time = date("Y-m-d H:i:s",time()));
		}



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

		function insert_insa_log($azsql){
			$log_txt = date("Y-m-d H:i:s",time()).", ".$azsql."/n/r";
			$log_file = "../log/insa_link_log_".date("Y-m").".txt";
			//$log_file = "../log/insa_link_log.txt";
	
			if(is_dir($log_file)){
				$log_option = 'w';
			}else{
				$log_option = 'a';
			}

			$log_file = fopen($log_file, $log_option);
			fwrite($log_file, $log_txt."\r\n");
			fclose($log_file);
		}


}
?>