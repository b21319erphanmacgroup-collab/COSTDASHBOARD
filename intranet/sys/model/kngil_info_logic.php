<?php
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";
	include "../inc/function_intranet.php";
	
	extract($_GET);
	class KngilInfoLogic {
		var $smarty;
		function KngilInfoLogic($smarty){
			$this->smarty=$smarty;
		}
		
		//================================================================================
		// KNGIL 사용자 기본 정보 조회 - KNGIL 페이지에 넘겨줄 데이터 조회
		//================================================================================
		
		function UserInfoData($MemberID){
			global $db;
			
			extract($_REQUEST);
			
			$nowDate = date("Y-m-d");
			
			$sql = "
						SELECT
							mt.MemberNo AS member_id,
							mt.korName AS kor_name,
							(SELECT
								Name
							FROM
								systemconfig_tbl
							where
								SysKey='PositionCode'
							AND Code=mt.RankCode) AS rank_name,
							(SELECT
								Code
							FROM
								systemconfig_tbl
							WHERE
								SysKey='CompanyType'
							AND Name='바론') AS comp_code,							
							(SELECT
								Name
							FROM
								systemconfig_tbl
							WHERE
								SysKey='GroupCode' 
							AND CODE=mt.GroupCode)dept_name
						FROM 
							member_tbl mt
						WHERE
							mt.MemberNo = '".$MemberID."' 
					";
			
			$re = mysql_query($sql,$db);
			
			if(!$re){
				$this->record_log("kngil", "KNGIL_UserInfo_int-004_".$MemberID, $sql);
				return "int-004"; //사용자 정보 조회 쿼리 오류 또는 사용자 정보 없을 때 
				exit;
			}
			
			if(mysql_num_rows($re)>0){
				$this->record_log("kngil", "KNGIL_UserInfo_Success_".$MemberID, $sql);
				return mysql_fetch_assoc($re);
			}
			else if(mysql_num_rows($re)<1){
				$this->record_log("kngil", "KNGIL_UserInfo_int-005_".$MemberID, $sql);
				return "int-005";	//조회는 했지만 사용자 정보가 없음
			}
		}
		
		
		//================================================================================
		// KNGIL 사이트 접속 확인
		//================================================================================
		function KngilSiteStatus(){
			extract($_REQUEST);
			
			// cURL 세션 초기화
			$ch = curl_init();
			
			if($MemberID!="M22014"){
			$KngilSiteCheck = "http://110.8.170.21/kngil/sys/controller/location/Location_controller.php?ActionMode=SCREEN_01";
			}
			else if($MemberID=="M22014"){
				$KngilSiteCheck = "http://110.8.170.21/kngil_khg/sys/controller/location/Location_controller.php?ActionMode=SCREEN_01";
			}
				
			// 전송할 데이터 설정 (예: POST 요청에 포함할 데이터)
			$KngilSendData = array(
					"MainAction" => "KngilSiteCheck"
			);
				
			// cURL 옵션 설정
			curl_setopt($ch, CURLOPT_URL, $KngilSiteCheck); // 요청을 보낼 URL
			curl_setopt($ch, CURLOPT_POST, true); // POST 방식 사용
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($KngilSendData)); // 보낼 데이터
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 응답을 문자열로 반환받도록 설정
				
			$siteStatus = curl_exec($ch);
			
			if(strstr($siteStatus,'success') == "success"){
				$this->record_log("kngil", "KNGIL_CONNECTION_".$MemberID, "SUCCESS");
				
				$reUserInfo = $this->UserInfoData($MemberID);
				
				if($reUserInfo == "int-005" || $reUserInfo == "int-004"){
					echo $reUserInfo;
				}
				else{
					$reWorkSatusInfo = $this->KngilWorkStatus($reUserInfo);
				}				
			}
			else{
				$this->record_log("kngil", "KNGIL_CONNECTION_".$MemberID, "FAIL");
				
				echo "disable";
			}
			
			curl_close($ch);
			
			exit;
		}

		
		//================================================================================
		// KNGIL 사용자 서비스 신청 내역 조회
		//================================================================================
		
		function KngilWorkStatus($MemberInfo){
			global $db;
			
			extract($_REQUEST);
						
			// cURL 세션 초기화
			$ch = curl_init();			
			
			if($MemberInfo["member_id"]!="M22014"){
				$KngilURL = "http://110.8.170.21/kngil/sys/controller/location/Location_controller.php?";
			}
			else if($MemberInfo["member_id"]=="M22014"){
				$KngilURL = "http://110.8.170.21/kngil_khg/sys/controller/location/Location_controller.php?";
			}
			
			
			// 전송할 데이터 설정 (예: POST 요청에 포함할 데이터)
			$KngilSendData = array(
					"ActionMode" => "SCREEN_01",
					"MainAction" => "KngilReportFileCheck",
					"kngil_request_cd" => $MemberInfo["member_id"],
					"kngil_mbr_info_03" => $MemberInfo["comp_code"],
			);
			
			// cURL 옵션 설정
			curl_setopt($ch, CURLOPT_URL, $KngilURL); // 요청을 보낼 URL
			curl_setopt($ch, CURLOPT_POST, true); // POST 요청을 사용
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($KngilSendData)); // POST 요청의 데이터
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 응답을 문자열로 반환받도록 설정
					
			
			// 요청 실행 및 응답 받기
			$response = curl_exec($ch);
			
			//$responseArr[8] - kngil_url(파일경로)
			//$responseArr[9] - processcomplete(파일생성 여부) 코드 값 - 0:생성중 / 1:생성완료 / 2,3:생성실패 / 10:파일생성 취소
			//$responseArr[10] - kngil_dw_sttus(파일다운로드 여부) 코드 값 - null / 1:다운로드 완료
			//$responseArr[11] - kngil_dw_str(파일다운로드 일자)
			
			if(strpos($response,"int-001") > -1){	
				$this->record_log("kngil", "KNGIL_WorkStatus_int-001_".$MemberInfo["member_id"], "QueryFail");
				
				echo "int-001";	//쿼리 조회 실패
			}
			else if(strpos($response,"success") > -1){
				$response = str_replace('\\','/',$response);
				
				$responseArr = explode("^&",$response);
			
				// new : KNGIL 페이지 접속이 가능한 상태(파일 생성 중 또는 다운로드 파일이 없는 경우)
				if($responseArr[1] == "new" ){
					echo $responseArr[1].'^&'.implode($MemberInfo,'^&');
				}
				//파일 경로가 빈 값이 아니거나 FILE_CANCEL 단어가 없고, 생성여부가 완료일 때
				else if(($responseArr[8] != ""  &&  strpos($responseArr[8],"FILE_CANCEL") == "") && $responseArr[9] == "1"){		
					$this->record_log("kngil", "KNGIL_WorkStatus_FileComplete_".$MemberInfo["member_id"], "FileComplete");
					echo $response;
				}
				//파일경로가 비어있고, 생성여부가 생성 중일 때
				else if($responseArr[8] == "" && $responseArr[9] == "0"){
					$this->record_log("kngil", "KNGIL_WorkStatus_FileCreate_".$MemberInfo["member_id"], "Creating File");
					echo $response;
				}
				//파일경로가 비어있고, 생성 실패 일 때
				else if($responseArr[8] == "" && ($responseArr[9] == "2" || $responseArr[9] == "3")){
					echo 'new2^&'.implode($MemberInfo,'^&')."^&".$responseArr[9];
				}				
				//파일경로에 FILE_CANCEL 단어가 있고, 생성 여부가 파일생성 취소일 때
				else if(strpos($responseArr[8],"FILE_CANCEL") == 0 && $responseArr[9] == "10"){
					echo 'new2^&'.implode($MemberInfo,'^&')."^&".$responseArr[9];
				}
				else{					
					echo "int-002"; //파일 상태 확인 필요
				}
			}
			
			// cURL 세션 닫기
			curl_close($ch);
			
		}
		
		//================================================================================
		//  KNGIL 테이블에 보고서 파일 다운로드 현황 완료 처리
		//================================================================================
		
		function KngilFileDwStInput(){
			global $db;
				
			extract($_REQUEST);
				
			// cURL 세션 초기화
			$ch = curl_init();
			
			if($request_memberno!="M22014"){
				$KngilURL = "http://110.8.170.21/kngil/sys/controller/location/Location_controller.php?ActionMode=SCREEN_01&MainAction=KngilReportFileDwComplete";
			}
			else if($request_memberno=="M22014"){
				$KngilURL = "http://110.8.170.21/kngil_khg/sys/controller/location/Location_controller.php?ActionMode=SCREEN_01&MainAction=KngilReportFileDwComplete";
			}
				
			
			
			// 전송할 데이터 설정 (예: POST 요청에 포함할 데이터)
			$KngilSendData = array(
					'kngil_request_cd' => $request_memberno,
					'kngil_request_tm' => $request_datetime
			);
				
			// cURL 옵션 설정
			curl_setopt($ch, CURLOPT_URL, $KngilURL); // 요청을 보낼 URL
			curl_setopt($ch, CURLOPT_POST, true); // POST 요청을 사용
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($KngilSendData)); // POST 요청의 데이터
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 응답을 문자열로 반환받도록 설정
				
				
			// 요청 실행 및 응답 받기
			$response = curl_exec($ch);
				
			if(strstr($response,"int-008") == "int-008"){
				$this->record_log("kngil", "KNGIL_KngilFileDwStInput_int-008_".$request_memberno, "int-008");
				echo "int-008";
			}
			else if(strstr($response,"success") == "success"){
				$this->record_log("kngil", "KNGIL_KngilFileDwStInput_success_".$request_memberno, "success");
				echo "success";
				
			}
				
			// cURL 세션 닫기
			curl_close($ch);
		}
		
		//================================================================================
		//  KNGIL 파일 생성 중 또는 생성 완료인 경우 취소 버튼 누르면 파일 다운로드 없이 새로 생성할 수 있게 바꾸는 함수
		//================================================================================
		
		function KngilFileCreateCancel(){
			global $db;
		
			extract($_REQUEST);
		
			// cURL 세션 초기화
			$ch = curl_init();
				
			if($request_memberno!="M22014"){
				$KngilURL = "http://110.8.170.21/kngil/sys/controller/location/Location_controller.php?ActionMode=SCREEN_01&MainAction=KngilFileCreateCancel";
			}
			else if($request_memberno=="M22014"){
				$KngilURL = "http://110.8.170.21/kngil_khg/sys/controller/location/Location_controller.php?ActionMode=SCREEN_01&MainAction=KngilFileCreateCancel";
			}
		
				
				
			// 전송할 데이터 설정 (예: POST 요청에 포함할 데이터)
			$KngilSendData = array(
					'kngil_request_cd' => $request_memberno,
					'kngil_request_tm' => $request_datetime
			);
		
			// cURL 옵션 설정
			curl_setopt($ch, CURLOPT_URL, $KngilURL); // 요청을 보낼 URL
			curl_setopt($ch, CURLOPT_POST, true); // POST 요청을 사용
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($KngilSendData)); // POST 요청의 데이터
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 응답을 문자열로 반환받도록 설정
		
		
			// 요청 실행 및 응답 받기
			$response = curl_exec($ch);
			
		
			if(strstr($response,"int-009") == "int-009"){
				$this->record_log("kngil", "KNGIL_KngilFileCreateCancel_int-009_".$request_memberno, "int-009");
				echo "int-009";
			}
			else if(strstr($response,"success") == "success"){
				$this->record_log("kngil", "KNGIL_KngilFileCreateCancel_success_".$request_memberno, "success");
				echo "success";
			}
		
			// cURL 세션 닫기
			curl_close($ch);
		}
		
		
		//============================================================================
		//함수
		//============================================================================
		
		function HangleEncodeUTF8_EUCKR($item){
			$result=trim(ICONV("UTF-8","EUC-KR",$item));
			return $result;
		}
		
		function PrintExcelHeader02($filename,$excel){
			$filename = $this->HangleEncodeUTF8_EUCKR($filename);
			if($excel != ""){
				header("Content-Type:application/vnd.ms-excel;charset=utf-8");
				header("Content-type:application/x-msexcel;charset=utf-8");
				header("Content-Disposition:attachment;filename=\"$filename.xls\"");
				header("Expires:0");
				header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
				header("Cache-Control:private",false);
			}
		
		}
		
		//로그남기는 함수
		function record_log($record_type, $position, $sql){
			$log_txt = date("Y-m-d H:i:s",time())." , ".$position." , ".preg_replace('/\r\n|\r|\n/',' ',$sql);
			$log_file = "../log/".date("Y-m-d",time())."_mysql_".$record_type.".txt";
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