<?php

	/***************************************
	* 부서장 결재 메시지
	****************************************/

include "../inc/dbcon.inc";
//	include "../inc/dbconForMystation.inc";
include "../../../SmartyConfig.php";
include "../util/HanamcPageControl.php";
include "../util/OracleClass.php";

extract($_POST);
class NotifyMessageLogic {
	var $smarty;
	var $oracle;
	function NotifyMessageLogic($smarty)
	{
		$this->smarty=$smarty;
		$this->oracle=new OracleClass($smarty);
	}

	//오라클 서버에 한글입력.
	function HangleEncodeUTF8_EUCKR($item)
	{
		$result=trim(ICONV("UTF-8","EUC-KR",$item));
		return $result;
	}

	//============================================================================
	// 부서장인지 체크
	//============================================================================
	function SanctionAuth(){
		global $db;
		extract($_REQUEST);
		//print_r($_REQUEST);

		//$test1 = base64_encode ( $MemberID ); //암호화
		$MemberID = base64_decode ( $MemberID ); //복호화

		if($MemberID == "216091" || $MemberID =="216009")
		{
			// 현재 관리 임원의 경우 출금전표의 양이 너무 많아서 알림을 정지해 달라고 요청옴
			// 임시로 검검 하지 않도로 처리

			$query_data = array(
				'status' => 'fail'
				,'time' => '2'
			);
			echo "[".json_encode($query_data)."]";
			return;

		}


		$sql = "
			SELECT
				Name
			FROM
				systemconfig_tbl
			WHERE
				SysKey LIKE 'groupcode'
				AND Remark like '$MemberID'
		;";
		//echo $sql;

		$re = @mysql_query($sql,$db);
		if(mysql_num_rows($re) > 0){
			$query_data = array(
				'status' => 'success'
			);
			//echo "success";
		}else{
			$query_data = array(
				'status' => 'fail'
				,'time' => '2'
			);
			//echo "fail";
		}
		echo "[".json_encode($query_data)."]";
	}

	//============================================================================
	// 사용자 체크
	//============================================================================
	function UserCheck(){
		global $db;
		extract($_REQUEST);
		//print_r($_REQUEST);

		//$test1 = base64_encode ( $MemberID ); //암호화
		$MemberID = base64_decode ( $MemberID ); //복호화
		$Password = base64_decode ( $Password ); //복호화

		//$MemberID = '216070';
		//$Pasword = '21671';
		$sql = "
			SELECT
				korName
			FROM
				member_tbl
			WHERE
				MemberNo = '$MemberID'
				AND Pasword = '$Password'
				AND WorkPosition != '9'
		;";
		//echo $sql;

		$re = @mysql_query($sql,$db);
		if(mysql_num_rows($re) > 0){
			$query_data = 'OK';
		}else{
			$query_data = 'FAIL';
		}
		echo $query_data;
		//echo base64_encode($query_data);
	}



	//============================================================================
	// 결재할 문서 체크
	//============================================================================
	function SanctionList_new(){
		include "../inc/approval_var.php";
		global $db;
		extract($_REQUEST);

		$MemberID = base64_decode ( $MemberID ); //복호화

		//$MemberID = '216091';

		$query_data = array(
			'status' => 'N'
			,'last_DocSN' => ''
			,'notify_message' => ''
		);

		if($MemberID != ''){
			//satis 건수 체크
			$azsql ="BEGIN USP_MAIN_INIT_07(:entries,'$MemberID'); END;";
			$list_data07 = $this->oracle->LoadProcedure($azsql,"list_data07","");
			$satis_count = ((int)$list_data07[0]['item01']) + ((int)$list_data07[0]['item02']) + ((int)$list_data07[0]['item05']) + ((int)$list_data07[0]['item06']);
			//print_r($satis_count);

			
			$sql0="select * from approval_tbl where ReceiveMember='$MemberID'  ";
			$re0 = mysql_query($sql0,$db);
			$re_row0 = mysql_num_rows($re0);//총 개수 저장
			/* ----------------------------- */
			while($re_row0 = mysql_fetch_array($re0)){
				if(strpos($re_row0[FormName], "HMF-5") === false){
					$FormList=$FormList."'".$re_row0[FormName]."',";
				}else{
					$FormList_Account=$FormList_Account."'".$re_row0[FormName]."',";
				}
			}//while End
			$FormList=substr($FormList,0,strlen($FormList)-1);
			
			if($FormList == "") {  //일반사용자
				$sql = "select * from SanctionDoc_tbl where RT_SanctionState like '%".$SANCTION_CODE.":".$MemberID."%' or  RT_SanctionState like '%".$SANCTION_CODE2.":".$MemberID."%'";
			}else {               //처리부서 접수담당자
				
				$sql2 = "select GroupCode from member_tbl where MemberNo='$MemberID'";
				//echo $sql2."<Br>";
				$re2 = mysql_query($sql2,$db);
		        $MyGroupCode= mysql_result($re2,0,"GroupCode");
				$MyGroupCode = sprintf("%02d",$MyGroupCode);
			
				$sql = "select * from SanctionDoc_tbl where (RT_SanctionState like '%".$SANCTION_CODE.":".$MemberID."%' or RT_SanctionState like '%".$SANCTION_CODE2.":".$MemberID."%') or (PG_Code='".$MyGroupCode."' and RT_SanctionState like '%".$PROCESS_RECEIVE."%' and FormNum in ($FormList))";
			}

			//echo $sql."<br>";

			$re = @mysql_query($sql,$db);
			$query_num = mysql_num_rows($re);
			$re_row = mysql_fetch_array($re);
			$DocSN = $re_row['DocSN'];

			if($query_num > 0){
				$query_data = array(
					'status' => 'Y'
					,'last_DocSN' => $DocSN
					,'notify_message' => '인트라넷 : '.$query_num.'건 / SATIS : '.$satis_count.'건'
				);
			}
			//echo "[".json_encode($query_data)."]";
		}

		echo "[".json_encode($query_data)."]";

		/*
		status=Y	//결재할 문서 체크
		last_time = 시간 //최근 시간
		notify_message=ㅌㅌㅌㅌㅌㅌㅌ	//출력할 메시지.
		*/
	}
	


	//============================================================================
	// 인트라넷 웹에서 결재할 문서 체크
	//============================================================================
	function SanctionCount(){
		include "../inc/approval_var.php";
		global $db;
		extract($_REQUEST);

		//$memberID = 'M02107';

		$intranet_count=0;
		$satis_count=0;
	
		if($memberID != ''){
			//satis 건수 체크
			$azsql ="BEGIN USP_MAIN_INIT_07(:entries,'$memberID'); END;";
			$list_data07 = $this->oracle->LoadProcedure($azsql,"list_data07","");
			$satis_count = ((int)$list_data07[0]['item01']) + ((int)$list_data07[0]['item02']) + ((int)$list_data07[0]['item05']) + ((int)$list_data07[0]['item06']);
		
				
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
				if($count > 0){
					while($re_row = mysql_fetch_array($re)) {
						$DocSN=$re_row[DocSN];
						$sql2 = "select * from sanctionapproval_tbl where DocSN='$DocSN' and MemberNo='$memberID'";
						//echo $sql2."<br>"; 
						$re2 = mysql_query($sql2,$db);
						$count2 = mysql_num_rows($re2);
						if($count2 == 0){

							$intranet_count++;
							$sql3  = "insert into  sanctionapproval_tbl (DocSN,MemberNo,InputDate) values ('$DocSN','$memberID',now())";
							//echo $sql3."<br>";
							mysql_query($sql3,$db);
						}

					}
				}else{
					$intranet_count=0;
				}//if End
		}
		$return_msg=$intranet_count."/".$satis_count;
		echo trim($return_msg);
	}
}
?>