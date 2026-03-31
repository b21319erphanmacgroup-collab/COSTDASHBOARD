<?php
	
	/******************************************************************************
	* 삼안 SATIS에서 변경된 프로젝트코드 정보를 인트라넷에 적용하기 위한 클래스
	* -----------------------------------------------------------------------------
	*  작업일자   |  작업자   | 작업 내용
	* 2016-08-30 :   신지호   : 프로젝트 생성 및 기능구현 
	*******************************************************************************/ 
	include "../inc/dbcon.inc";	
	include "../model/OracleClass.php";
	include "../../../SmartyConfig.php";

	extract($_REQUEST);
	class ProjcodeLinkLogic {
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

		function ProjcodeLinkLogic($smarty)
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
		// 추가 및 변경된 프로젝트 코드 정보
		//=================================================
		function LinkProcess()
		{
			global $db;
			extract($_REQUEST);
			$azsql ="select * from CS_CONT_MAP_MASTER where PROJ_MAP_TAG  <>'Y'";
			$this->oracle->ChangeDBConnection();
			$datalist=$this->oracle->LoadData($azsql,"");


			for($index=0;$index<count($datalist);$index++)
			{
				$proj_map_tag=$datalist[$index]['PROJ_MAP_TAG'];
				
				$proj_code=$datalist[$index]['PROJ_CODE'];
				$proj_code_kor=$this->HangleEncode($datalist[$index]['PROJ_CODE_KOR']);
				$proj_name=$this->HangleEncode($datalist[$index]['PROJ_NAME']);
				$proj_order_name=$this->HangleEncode($datalist[$index]['PROJ_ORDER_NAME']);
				$proj_sdate=$datalist[$index]['PROJ_SDATE'];
				$proj_edate=$datalist[$index]['PROJ_EDATE'];

				$proj_ing_tag= $datalist[$index]['PROJ_ING_TAG'];
				$workstatus = '10';
				$PROJ_ORG_CODE=$datalist[$index]['PROJ_ORG_CODE'];

												
				//==========================================
				// 변경된 정보를 갱신하는 부분
				//==========================================			
				// SATIS 에서 넘어오는 수정/추가 정보를 사용하지 않고, DB에서 정보를 추가 한다
				$proj_map_tag="N";

				$azsql="select * from project_tbl where ProjectCode ='$proj_code'";
				$re = mysql_query($azsql,$db);
				$re_num = mysql_num_rows($re);
				if($re_num != 0)
				{	
					if($re_row = mysql_fetch_array($re)) 
						$proj_map_tag="U";
				}

				if($proj_map_tag == 'U')
				{					
					if($proj_ing_tag == $this->HangleEncodeUTF8_EUCKR("준공"))
					{
						//$workstatus = '12';
						$workstatus = '준공완료';
					}else
					{
						$workstatus = '수행중';
					}

					/*
					$azsql =  "update project_tbl set projectViewCode='$proj_code_kor',
													  ProjectName='$proj_name',
													  ProjectNickname='$proj_name',
													  OrderCompany='$proj_order_name',
													  ContractDate='$proj_sdate',
													  ContractStart='$proj_sdate',
													  ContractEnd='$proj_edate',
													  WorkStatus='$workstatus',
													  oldProjectCode='$PROJ_ORG_CODE'
								WHERE ProjectCode ='$proj_code'";
					*/
					$azsql =  "update project_tbl set ProjectName='$proj_name',
													  ProjectNickname='$proj_name',
													  OrderCompany='$proj_order_name',
													  ContractDate='$proj_sdate',
													  ContractStart='$proj_sdate',
													  ContractEnd='$proj_edate',
													  WorkStatus='$workstatus',
													  oldProjectCode='$PROJ_ORG_CODE'
								WHERE ProjectCode ='$proj_code'";

					mysql_query($azsql);
					//echo $azsql;
				}
				else
				{					
					//================================================
					// 신규 프로젝트에 대한 정보를 추가 한다
					//================================================
					/*
					$azsql="insert into project_tbl (ProjectCode,projectViewCode,ProjectName,ProjectNickname,OrderCompany,ContractDate,ContractStart,ContractEnd,WorkStatus, oldProjectCode)
						values
						(
						'$proj_code',
						'$proj_code_kor',
						'$proj_name',
						'$proj_name',
						'$proj_order_name',
						'$proj_sdate',
						'$proj_sdate',
						'$proj_edate',
						'$workstatus',
						'$PROJ_ORG_CODE'
						)";						
					*/
					$workstatus="수행중";
					$azsql="insert into project_tbl (ProjectCode,ProjectName,ProjectNickname,OrderCompany,ContractDate,ContractStart,ContractEnd,WorkStatus, oldProjectCode)
						values
						(
						'$proj_code',
						'$proj_name',
						'$proj_name',
						'$proj_order_name',
						'$proj_sdate',
						'$proj_sdate',
						'$proj_edate',
						'$workstatus',
						'$PROJ_ORG_CODE'
						)";						

					mysql_query($azsql);								
					//echo $azsql;
				}
				$log_txt = date("Y-m-d H:i:s",time()).",".$proj_code.",".$azsql."/n/r";
				$log_file = "../log/project_link_log.txt";
				if(is_dir($log_file)){
					$log_option = 'w';
				}else{
					$log_option = 'a';
				}

				$log_file = fopen($log_file, $log_option);
				fwrite($log_file, $log_txt."\r\n");
				fclose($log_file);

				// 수정된 정보에 대한 내용을 update한다
				$azsql ="update CS_CONT_MAP_MASTER set PROJ_MAP_TAG = 'Y' WHERE PROJ_CODE = '$proj_code'";				
				$this->oracle->ProcedureExcuteQuery($azsql);
				//echo $azsql;

				$log_txt = date("Y-m-d H:i:s",time()).",".$proj_code.",".$azsql."/n/r";
				$log_file = "../log/project_link_log.txt";
				if(is_dir($log_file)){
					$log_option = 'w';
				}else{
					$log_option = 'a';
				}

				$log_file = fopen($log_file, $log_option);
				fwrite($log_file, $log_txt."\r\n");
				fclose($log_file);
			}
			
			echo count($datalist).$this->HangleEncodeUTF8_EUCKR("건 갱신이 완료되었습니다. // ".$now_time = date("Y-m-d H:i:s",time()));

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
	

}
?>
