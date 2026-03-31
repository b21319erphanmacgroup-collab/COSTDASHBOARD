<?php
	include "../inc/dbcon.inc";
	include "../inc/approval_var.php";
	include "../util/OracleClass_test.php";

	include "../../../SmartyConfig.php";
	require_once($SmartyClassPath);

    $smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;


	//Login Log 남김
	/*
	$user_ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];   /// remote ip 저장

	$cfile="../log/".date("Y-m")."_approval.txt";

	$exist = file_exists("$cfile");
	if($exist) {
		$fd=fopen($cfile,'r');
		$con=fread($fd,filesize($cfile));
		fclose($fd);
	}
	$fp=fopen($cfile,'w');
	$aa=date("Y-m-d H:i");
	//$cond=$con.$aa." ".$n_num." ".$user_ip."\n";
	$cond=$con.$aa." ".$memberID." ".$user_ip."\n";
	fwrite($fp,$cond);
	fclose($fp);
	*/

	if($memberID <>"")
	{
			$WaitDoc_Count2=0;
				
			$sql0="select * from approval_tbl where ReceiveMember='$memberID'";
			//echo $sql0."<br>"; 
			$re0 = mysql_query($sql0,$db);
			$re_row0 = mysql_num_rows($re0);//총 개수 저장
			/* ----------------------------- */
			while($re_row0 = mysql_fetch_array($re0)){
				$FormList=$FormList."'".$re_row0[FormName]."',";
			}//while End
			/* ----------------------------- */
			$FormList=substr($FormList,0,strlen($FormList)-1);

			
			if($FormList == "") {  //일반사용자	
				$sql = "select * from SanctionDoc_tbl where RT_SanctionState like '%".$SANCTION_CODE.":".$memberID."%' or  RT_SanctionState like '%".$SANCTION_CODE2.":".$memberID."%'";
			}
			else  //처리부서 접수담당자
			{
				$azsql = "SELECT * FROM member_tbl WHERE MemberNo='$memberID' and WorkPosition <> '9'";
				$azRecord = mysql_query($azsql,$db);
				if(mysql_num_rows($azRecord) > 0) 
				{
					$GroupCode = mysql_result($azRecord,0,"GroupCode");
					$MyGroupCode=sprintf("%02d",$GroupCode);
				}

				
				$sql = "select * from SanctionDoc_tbl where (RT_SanctionState like '%".$SANCTION_CODE.":".$memberID."%' or RT_SanctionState like '%".$SANCTION_CODE2.":".$memberID."%') or (PG_Code='".$MyGroupCode."' and RT_SanctionState like '%".$PROCESS_RECEIVE."%' and FormNum in ($FormList))";

				
			}
				//echo $sql."<br>"; 
				$re = mysql_query($sql,$db);
				$count = mysql_num_rows($re);
				//echo "count=".$count."<br>"; 
				if($count > 0){


					while($re_row = mysql_fetch_array($re)) {
						$DocSN=$re_row[DocSN];
						$sql2 = "select * from sanctionapproval_tbl where DocSN='$DocSN' and MemberNo='$memberID'";
						//echo $sql2."<br>"; 
						$re2 = mysql_query($sql2,$db);
						$count2 = mysql_num_rows($re2);
						if($count2 == 0){

							$WaitDoc_Count2++;

							$sql3  = "insert into  sanctionapproval_tbl (DocSN,MemberNo,InputDate) values ('$DocSN','$memberID',now())";
							//echo $sql3."<br>";
							//mysql_query($sql3,$db);
						}

					}
				}else{
					$WaitDoc_Count2=0;
				}//if End
			

			//ERP 카운트
			
			$azsql ="BEGIN USP_MAIN_INIT_07(:entries,'$memberID'); END;";

		//	$list_data07 = $this->oracle->LoadProcedure($azsql,"list_data07","");
		//	$list_data07 = $this->oracle->LoadProcedure($azsql,"list_data07","");

			//echo $azsql;
			$list_data07 = $oracle->LoadProcedure($azsql,"list_data07","");
			//$satis_count = ((int)$list_data07[0]['item01']) + ((int)$list_data07[0]['item02']) + ((int)$list_data07[0]['item05']) + ((int)$list_data07[0]['item06']);

			echo trim($WaitDoc_Count2);
	}


?>