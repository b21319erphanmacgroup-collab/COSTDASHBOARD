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
			$this->connection = oci_connect('satis', 'SATIS11707808', 'samandb');

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
	
		function LoadProcedure($i_azsql,$title,$short_array=null)
		{
			$result = oci_parse($this->connection, $i_azsql);

			$entries = oci_new_cursor($this->connection);
			oci_bind_by_name($result,":entries",$entries,-1,OCI_B_CURSOR);


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
			$this->smarty->assign('colume_data',$colume_data);
		
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
			$this->smarty->assign($title,$query_data);

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

		function ExcuteQuery($i_azsql)
		{
			$result = oci_parse($this->connection, $i_azsql);
			oci_execute($result);
		}

		function LoadData($i_azsql,$title)
		{
			$result = oci_parse($this->connection, $i_azsql);
			
			oci_execute($result);
			
			$query_data = array(); 
			$colume_data = array(); 
			
			$i=1;			

			while ($i <= oci_num_fields($result))
			{

				$name = oci_field_name($result, $i);
				$i++;
				array_push($colume_data,$this->HangleEncode($name));

			}
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

			$this->smarty->assign($title,$query_data);
			
		}

		function LoadData2($i_azsql,$title,$output)
		{
			$result = oci_parse($this->connection, $i_azsql);
			oci_execute($result);
			
			$query_data = array(); 
			$colume_data = array(); 
			
			$i=1;			

			while ($i <= oci_num_fields($result))
			{

				$name = oci_field_name($result, $i);
				$i++;
				array_push($colume_data,$this->HangleEncode($name));

			}
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

			if ($output=="")
			{
				$this->smarty->assign($title,$query_data);
			}
			else
			{
				return $query_data;
			}
		}

		function LoadData3($i_azsql,$title,$short_array=null)
		{
			$result = oci_parse($this->connection, $i_azsql);
			oci_execute($result);
			
			$query_data = array(); 
			$colume_data = array(); 
			
			$i=1;			

			while ($i <= oci_num_fields($result))
			{

				$name = oci_field_name($result, $i);
				$i++;
				array_push($colume_data,$this->HangleEncode($name));

			}
			$this->smarty->assign('colume_data',$colume_data);

			while($rec_device = oci_fetch_array($result)) 
			{
				for($Count=1;$Count<=oci_num_fields($result);$Count++)
				{
						$rec_device[$Count]=$this->HangleEncode($rec_device[$Count]);
						$name = oci_field_name($result, $Count);
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

			$this->smarty->assign($title,$query_data);
			return $query_data;
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
						// 동일한 정보가 전송되는것을 방지 해야 한다.
						$rec_device[$Count]="";
						$name = oci_field_name($entries, $Count);
						$rec_device[$name]=$this->HangleEncodeAjax($rec_device[$name]);
				}
				array_push($query_data,$rec_device);
			}
			print_r( urldecode( json_encode($query_data) )); 

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
		function bear3StrCut($str,$len,$tail=""){ 
			$rtn = array(); 
			return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str; 
		}
		function HangleEncodeAjax($item)
		{		$result=trim(ICONV("EUC-KR","UTF-8",$item));
				return $result;
		}


	}
