<?php
	include "../../../SmartyConfig.php";
	/***************************************
	* 오라클 관련된 클래스
	* ------------------------------------
	****************************************/
	extract($_GET);
	class OracleClass {
		var $smarty;
		var $connection;
		function OracleClass($smarty, $target='')
		{
			$this->smarty=$smarty;
			//$this->connection = oci_connect('satis', 'SATIS11707808', 'satisdb');
			//$this->connection = oci_connect('satis', 'SATIS11707808', 'satisdb');  //기존
			if( $target == 'SAMAN'){	//삼안서버
				$this->connection = oci_connect('satis', 'SATIS11707808', 'samandb');	//삼안 실서버
			}else{				
				$this->connection = oci_connect('brerp', 'brerp11707808', 'brerp');  //변경클라우드
			}
		}

		function ChangeDBConnection()
		{
		}

		function Query($i_azsql)
		{
			$this->record_log('Query', 'start', $i_azsql);
			$stmt = oci_parse($this->connection, $i_azsql);
			oci_execute($stmt);
			return $stmt;
		}

		function fetch_array($stmt)
		{
			return oci_fetch_array($stmt,OCI_BOTH);
		}

		function __destruct()
		{
			if($this->stmt)
				oci_free_statement($this->stmt);

			if($this->connection)
				oci_close($this->connection);
		}


		function LoadProcedure($i_azsql,$title,$short_array=null, $temp=null)
		{
			$this->record_log('LoadProcedure', 'start', $i_azsql);
			$result = oci_parse($this->connection, $i_azsql);

			$entries = oci_new_cursor($this->connection);
			oci_bind_by_name($result,":entries",$entries,-1,OCI_B_CURSOR);


//			echo $i_azsql;
			oci_execute($result);
			oci_execute($entries);

			$query_data = array();
			$colume_data = array();




			$i=1;

			while ($i <= oci_num_fields($entries))
			{

				$name = oci_field_name($entries, $i);
				$i++;
				array_push($colume_data,$this->HangleEncode($name));

			}
			if($temp == null){
				$this->smarty->assign('colume_data',$colume_data);
			}

			while($rec_device = oci_fetch_array($entries))
			{
				for($Count=1;$Count<=oci_num_fields($entries);$Count++)
				{
						$rec_device[$Count]=$this->HangleEncode($rec_device[$Count]);
						$name = oci_field_name($entries, $Count);
						$rec_device[$name]=$this->HangleEncode($rec_device[$name]);

						$short_name=$name."_short";
						if($short_array != null)
						{
							if($Count <=count($short_array))
							{
								$rec_device[$short_name]=$this->bear3StrCut($rec_device[$name],$short_array[$Count]);
							}
							else
								$rec_device[$short_name]=$rec_device[$name];
						}
						else
							$rec_device[$short_name]=$rec_device[$name];
				}


				array_push($query_data,$rec_device);
			}
			if($temp == null){
				$this->smarty->assign($title,$query_data);
			}
			return $query_data;

		}

		//==================================================================
		// 프로지셔을 이용한 처리
		//==================================================================

		function ProcedureExcuteQuery($i_azsql)
		{
			$this->record_log('ProcedureExcuteQuery', 'start', $i_azsql);
			$result = oci_parse($this->connection, $i_azsql);
			oci_execute($result);
		}

		//==================================================================
		// 프로지셔을 이용한 Ajax 자료 조회
		//==================================================================
		function LoadProcedureAjax($i_azsql)
		{
			$this->record_log('LoadProcedureAjax', 'start', $i_azsql);
			$result = oci_parse($this->connection, $i_azsql);

			$entries = oci_new_cursor($this->connection);
			oci_bind_by_name($result,":entries",$entries,-1,OCI_B_CURSOR);


			oci_execute($result);
			oci_execute($entries);

			$query_data = array();
			$colume_data = array();

			$i=1;

			while($rec_device = oci_fetch_array($entries))
			{
				for($Count=1;$Count<=oci_num_fields($entries);$Count++)
				{
						$name = oci_field_name($entries, $Count);
						$rec_device[$name]=$this->HangleEncodeAjax($rec_device[$name]);
				}
				array_push($query_data,$rec_device);
			}
			print_r( urldecode( json_encode($query_data) ));

		}


		function LoadConfigData($i_azsql)
		{
			$this->record_log('LoadConfigData', 'start', $i_azsql);
			$result = oci_parse($this->connection, $i_azsql);
			oci_execute($result);
			$query_data = array();
			$colume_data = array();
			$i=1;
			while($rec_device = oci_fetch_array($result))
			{
				for($Count=1;$Count<=oci_num_fields($result);$Count++)
				{
						$rec_device[$Count]=$this->HangleEncode($rec_device[$Count]);
						$name = oci_field_name($result, $Count);
						echo $name."=".$rec_device[$name]."\t";
				}
				echo "<BR>";
			}
		}



		function ExcuteQuery($i_azsql)
		{
			$this->record_log('ExcuteQuery', 'start', $i_azsql);
			$result = oci_parse($this->connection, $i_azsql);
			oci_execute($result);
		}

		function AjaxLoadData($i_azsql)
		{
			$this->record_log('AjaxLoadData', 'start', $i_azsql);
			$result = oci_parse($this->connection, $i_azsql);
			oci_execute($result);
			$query_data = array();
			$colume_data = array();

			$i=1;

			while($rec_device = oci_fetch_array($result))
			{
				for($Count=1;$Count<=oci_num_fields($result);$Count++)
				{
						$name = oci_field_name($result, $Count);
						$rec_device[$name]=$this->HangleEncodeAjax($rec_device[$name]);
				}
				array_push($query_data,$rec_device);
			}
			print_r( urldecode( json_encode($query_data) ));
		}
/*
		function LoadData($i_azsql,$title)
		{
			$result = oci_parse($this->connection, $i_azsql);
			oci_execute($result);
			$query_data = array();
			$colume_data = array();

			$i=1;

			while($rec_device = oci_fetch_array($result))
			{
				for($Count=1;$Count<=oci_num_fields($result);$Count++)
				{
						$rec_device[$Count]=$this->HangleEncode($rec_device[$Count]);

				}
				array_push($query_data,$rec_device);
			}
			//$this->smarty->assign($title,$query_data);
			return $query_data;
		}
*/
		function LoadData($i_azsql,$title,$output_type = "")
		{
			$this->record_log('LoadData', 'start', $i_azsql);
			$result = oci_parse($this->connection, $i_azsql);
			oci_execute($result);
			$query_data = array();
			$colume_data = array();

			$i=1;

			if($title !="")
			{
				while ($i <= oci_num_fields($result))
				{

					$name = oci_field_name($result, $i);
					$i++;
					array_push($colume_data,$this->HangleEncode($name));

				}
				if($output_type == "")
					$this->smarty->assign('colume_data',$colume_data);

				while($rec_device = oci_fetch_array($result))
				{
					for($Count=1;$Count<=oci_num_fields($result);$Count++)
					{
							$rec_device[$Count]=$this->HangleEncode($rec_device[$Count]);
							$name = oci_field_name($result, $Count);
							$rec_device[$name]=$this->HangleEncode($rec_device[$name]);
					}
					array_push($query_data,$rec_device);
				}

				if($output_type == "ajax")
					print_r( urldecode( json_encode( $query_data ) ) );
				else
					$this->smarty->assign($title,$query_data);
			}
			else
			{
				while($rec_device = oci_fetch_array($result))
				{
					for($Count=1;$Count<=oci_num_fields($result);$Count++)
					{
							$rec_device[$Count]=$this->HangleEncode($rec_device[$Count]);

					}
					array_push($query_data,$rec_device);
				}

			}

			return $query_data;

		}
		//=======================================================================================
		// OTP 조회를 위한 함수
		//=======================================================================================
		function CheckOTPAuthentication($user_id,$menu_id,$sub_id="default",$OTP)
		{
			$azsql="select * from SM_ERPURL_OTP where user_id='$user_id' and menu_id='$menu_id' and sub_id='$sub_id' and otp='$OTP' and updatetime >= to_char(sysdate - 1/24/60,'yyyymmdd hh24:mi:ss') ";
			$query_data=$this->LoadData($azsql,$value_name,$output_type);
			if(count($query_data) >0)
				return $query_data[0].OTP;
			else
				return "";
		}

		function UpdateOTPAuthentication($userid,$menu_id,$sub_id="default")
		{

			$azsql ="BEGIN usp_interface_otp_iu('$userid','$menu_id','$sub_id'); END;";
			$this->oracle->ProcedureExcuteQuery($azsql);
		}

		function HangleEncodeUTF8_EUCKR($item)
		{
				$result=trim(ICONV("UTF-8","EUC-KR",$item));
				return $result;
		}

		function HangleEncode($item)
		{		$result=trim(ICONV("EUC-KR","UTF-8",$item));
				if(trim($result)=="") 	$result="&nbsp";
				return $result;
		}

		function HangleEncodeAjax($item)
		{		$result=trim(ICONV("EUC-KR","UTF-8",$item));
				return $result;
		}

		function bear3StrCut($str,$len,$tail="..."){
			$rtn = array();
			return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str;
		}

		function record_log($record_type, $position, $sql){
			$log_txt = date("Y-m-d H:i:s",time())." , ".$position." , ".$sql;
			$log_file = "../log/".date("Y-m-d",time())."_oracle_".$record_type.".txt";
			if(is_dir($log_file)){
				$log_option = 'w';
			}else{
				$log_option = 'a';
			}

			$log_file = fopen($log_file, $log_option);
			fwrite($log_file, $log_txt."\r\n");
			fclose($log_file);
		}
		
		function custom_error_handler($errno, $errstr, $errfile, $errline) {
			if ($errno == E_WARNING) {
				throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
			}
			return false;
		}
		
		/******************************************************************************
		 기    능 : Oracle SQL Execute For Select
		 관 련 DB :
		 프로시져 :
		 사용메뉴 :
		 기    타 :
			1. Oracle SQL 실행 및 에러를 제어하기위함(트랜잭션용)
		 변경이력 :
			1. 2025-07-25 / - / 김병철 / 최초작성
		 ******************************************************************************/
		function executeSelect($sql) {
			try {
				set_error_handler(array($this, 'custom_error_handler'));
				
				$result = $this->initResult();
				$rows = array();
				$columns = array();
				$stmt = oci_parse($this->connection, $this->HangleEncodeUTF8_EUCKR($sql));
				
				if (empty($sql)) {
					throw new Exception("Param error sql is null");
				}
				
				if (!$stmt) {
					throw new Exception("Connection error");
				}
				
				if (strpos($sql, ":entries") !== false) {
					$entries = @oci_new_cursor($this->connection);
					oci_bind_by_name($stmt,":entries", $entries, -1, OCI_B_CURSOR);
					
					if (!oci_execute($stmt, OCI_DEFAULT)) {
						throw new Exception("oci_execute SELECT error");
					}
					
					if (!oci_execute($entries)) {
						throw new Exception("oci_execute(bind entries) error");
					}
					
					$fetchResult = $this->fetchResults($entries, $result);
					$rows = $fetchResult[0];
					$columns = $fetchResult[1];
				} else {
					if (!oci_execute($stmt, OCI_DEFAULT)) {
						throw new Exception("oci_execute error");
					}
					
					$fetchResult = $this->fetchResults($stmt, $result);
					$rows = $fetchResult[0];
					$columns = $fetchResult[1];
				}
				
				$result["rows"] = $rows;
				$result["columns"] = $columns;
			} catch (Exception $e) {
				oci_rollback($this->connection);
				$this->makeErrorResult($result, $stmt, $e->getMessage());
				
				if (!$result["success"]) {
					if ($result["error"]["datas"]["oci_error_data"]["code"] >= 20000) {
						$userId = $_SESSION["satis_user_id"];
						$errorMessage = $result["error"]["oci_error_data"]["message"];
						
						$this->insertSmErrorLog($userId, $this->HangleEncodeUTF8_EUCKR($errorMessage));
					}
				}
			}
			
			restore_error_handler();
			return $result;
		}
		
		/******************************************************************************
		 기    능 : Oracle SQL Execute For Dml
		 관 련 DB :
		 프로시져 :
		 사용메뉴 :
		 기    타 :
			1. Oracle SQL 실행 및 에러를 제어하기위함(트랜잭션용)
		 변경이력 :
			1. 2026-01-29 / - / 김병철 / 최초작성
		 ******************************************************************************/
		function executeDml($sql) {
			try {
				set_error_handler(array($this, 'custom_error_handler'));
				
				$result = $this->initResult();
				$rows = array();
				$columns = array();
				$stmt = oci_parse($this->connection, $this->HangleEncodeUTF8_EUCKR($sql));
				
				if (empty($sql)) {
					throw new Exception("Param error sql is null");
				}
				
				if (!$stmt) {
					throw new Exception("Connection error");
				}
				
				if (strpos($sql, ":entries") !== false) {
					$entries = @oci_new_cursor($this->connection);
					oci_bind_by_name($stmt,":entries", $entries, -1, OCI_B_CURSOR);
					
					if (!oci_execute($stmt, OCI_DEFAULT)) {
						throw new Exception("oci_execute dml error");
					}
					
					if (!oci_execute($entries)) {
						throw new Exception("oci_execute(bind entries) error");
					}
					
					$fetchResult = $this->fetchResults($entries, $result);
					$result["rows"] = $fetchResult[0];
					$result["columns"] = $fetchResult[1];
					
					// rows 정보에따라 Error Control 로직구현
				} else {
					if (!oci_execute($stmt, OCI_DEFAULT)) {
						throw new Exception("oci_execute error");
					}
				}
				
				oci_commit($this->connection);
			} catch (Exception $e) {
				oci_rollback($this->connection);
				$this->makeErrorResult($result, $stmt, $e->getMessage());
				
				if (!$result["success"]) {
					if ($result["error"]["datas"]["oci_error_data"]["code"] >= 20000) {
						$userId = $_SESSION["satis_user_id"];
						$errorMessage = $result["error"]["oci_error_data"]["message"];
						
						$this->insertSmErrorLog($userId, $this->HangleEncodeUTF8_EUCKR($errorMessage));
					}
				}
			}
			
			restore_error_handler();
			return $result;
		}
		
		/******************************************************************************
		 기    능 : Oracle SQL Execute Init
		 관 련 DB :
		 프로시져 :
		 사용메뉴 :
		 기    타 :
				 1. 구조
				 success = 성공여부
				 rows = 데이타
				 columns = 컬럼정보
				 error = 에러
		 변경이력 :
			1. 2026-01-29 / - / 김병철 / 최초작성
		 ******************************************************************************/
		function initResult() {
			return array(
					"success"=> true,
					"rows"=> null,
					"columns"=> null,
					"error"=> array(
							"code"=> null,
							"datas"=> null,
							"message"=> ""
					)
			);
		}
		
		function insertSmErrorLog($userId, $message) {
			if (empty($userId)) {
				return;
			}
			
			$sql  = " Insert Into SM_ERROR_TEMP  ";
			$sql .= " Values ";
			$sql .= " ( ";
			$sql .= "     :userId,  ";
			$sql .= "     :message,  ";
			$sql .= "     To_Char(Sysdate, 'YYYY-MM-DD HH24:MI:SS') ";
			$sql .= " ) ";
			$stmt = oci_parse($this->connection, $sql);
			
			oci_bind_by_name($stmt, ":userId", $userId);
			oci_bind_by_name($stmt, ":message", $message);
			
			oci_execute($stmt);
		}
		
		/******************************************************************************
		 기    능 : Oracle SQL Error 생성
		 관 련 DB :
		 프로시져 :
		 사용메뉴 :
		 기    타 :
		 변경이력 :
		 	1. 2026-01-29 / - / 김병철 / 최초작성
		 ******************************************************************************/
		function makeErrorResult(&$result, $stmt, $message = null) {
			$error = oci_error($stmt);
			$result["success"] = false;
			$result["error"]["datas"]["oci_error_data"] = $error;
			$result["error"]["datas"]["oci_error_data"]["message"] =  $this->convert_to_utf8($error["message"]);
			$result["error"]["message"] = $this->convert_to_utf8($message);
			$result["error"]["common_message"] = $this->convert_to_utf8("처리도중 오류가 발생하였습니다.\n관리자에게문의하세요.");
			
			return $result;
		}
		
		/******************************************************************************
		 기    능 : Oracle SQL Select결과 생성
		 관 련 DB :
		 프로시져 :
		 사용메뉴 :
		 기    타 :
		 변경이력 :
			1. 2026-01-29 / - / 김병철 / 최초작성
		 ******************************************************************************/
		function fetchResults($stmt, &$result) {
			$rows = array();
			$columns = array();
			
			try {
				$numFields = oci_num_fields($stmt);
				for ($i = 1; $i <= $numFields; $i++) {
					$field = oci_field_name($stmt, $i);
					$columns[] = $this->HangleEncodeAjax($field);
				}
				
				while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
					$encodedRow = array();
					
					foreach ($row as $key => $value) {
						$encodedRow[$key] = $this->HangleEncodeAjax($value);
					}
					$rows[] = $encodedRow;
				}
				
				return array($rows, $columns);
			} catch (Exception $e) {
				$this->makeErrorResult($result, $this->connection, "fetchResults error : \n" . $this->HangleEncodeAjax($e->getMessage()));
				return array($rows, $columns);
			}
		}
		
		/******************************************************************************
		 기    능 : Oracle DB 인코딩변환(CP949 -> UTF-8)
		 관 련 DB :
		 프로시져 :
		 사용메뉴 :
		 기    타 :
		 변경이력 :
			1. 2026-01-29 / - / 김병철 / 최초작성
		 ******************************************************************************/
		function convert_to_utf8($str) {
			if (mb_detect_encoding($str, 'UTF-8', true)) {
				return $str;
			}
			
			$encodings = array("CP949", "EUC-KR", "MS949", "ISO-8859-1");
			foreach ($encodings As $enc) {
				$converted = @iconv($enc, "UTF-8//IGNORE", $str);
				$round_trip = @iconv("UTF-8", $enc, $converted);
				
				if ($round_trip === $str) {
					return $converted;
				}
			}
			
			return $str;
		}

	}
?>