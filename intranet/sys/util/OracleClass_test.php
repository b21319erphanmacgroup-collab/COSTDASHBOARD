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
		function OracleClass($smarty)
		{
			$this->smarty=$smarty;
			$this->connection = oci_connect('satis', 'SATIS11707808', 'satisdb');
		}

		function ChangeDBConnection()
		{
		}

		function Query($i_azsql)
		{
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
			$result = oci_parse($this->connection, $i_azsql);
			oci_execute($result);
		}

		//==================================================================
		// 프로지셔을 이용한 Ajax 자료 조회
		//==================================================================
		function LoadProcedureAjax($i_azsql)
		{
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
			$result = oci_parse($this->connection, $i_azsql);
			oci_execute($result);
		}

		function AjaxLoadData($i_azsql)
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


	}
?>