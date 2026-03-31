<?php

	/*
	* -----------------------------------------------------------------------------------
	*  수 정 날 짜 |    작 성 자    |  수정내용
	* -----------------------------------------------------------------------------------
	* 2017-06-21  |     문형석     |  파일생성

	*/

	include "../../util/OracleClass.php";
	include "../../../../SmartyConfig.php";
	include "../../inc/getNeedDate.php";
	include "../../inc/function_add.php";


	extract($_REQUEST);
	class Fileupload_Logic {
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

		function Fileupload_Logic($smarty)
		{
			global $emp_id;
			$this->oracle=new OracleClass($smarty);

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

			$this->userid = $_SESSION['satis_user_id'];
			$this->deptcode = $_SESSION['satis_user_deptcode'];

			extract($_REQUEST);
			if($this->userid =="") 	$this->userid=$userid;
			if($this->deptcode =="") 	$this->deptcode=$deptcode;

		}
		
		//=================================================
		// 파일 업로드
		//=================================================
			function SCREEN_01(){
				extract($_REQUEST);
				$this->PrintExcelHeader("타이틀");
				if($this->excel != "" ){
					//$this->REPORT_01_001_HTML_Ajax_01();
				}else{
					$CommonCode=new CommonCodeList($this->smarty);
					switch($MainAction){
						case "SingleFile": 	$this->SCREEN_01_001_Ajax_01();	break; //단일파일
						case "MultyFile": 	$this->SCREEN_01_001_Ajax_02();	break; //멀티파일
						default:
							break;
					}
				}
			}//SCREEN_03

			function SCREEN_01_001_Ajax_01($mode=true){
				extract($_REQUEST);
				session_start(); 
				switch($SubAction){
					//----------------------------------------------------------------------------
					case "upload":
						//=================================================================================
						//단일파일 업로드//////////////////////////////////////////////////////////////////////
						//=================================================================================
						//기본설정
						$Uploads_dir = $Uploads_dir==""?"":$Uploads_dir; //경로설정
						$fileSize_check = "104857600";//100mb //저장용량설정
// 						$fileSize_check = "10485760";//10mb
// 						$fileSize_check = "5242880";//5mb
// 						$fileSize_check = "1048576";//1mb
						$allowed_ext = array('jpg','jpeg','png','gif','txt','xlsx','doc','zip','ppt','pptx','pdf','hwp','xls');//저장가능파일설정
						$UniqFileName_boolean = false;// true/false 같은 파일 이름이 있는지 검사 후 , 유니크 파일명값 설정
						$UniqFileName_Type = "";//중복파일 존재시, 새로운 파일명 생성시 사용할 인덱스 정의(default="" => "파일명_1...) : ex) date("YmdHis")
						
						//=================================================================================
						// 파일존재 오류 확인
						if( !isset($_FILES['userfile']['error']) ) {
							echo json_encode( array(
									'status' => 'error1',
									'message' => '파일이 첨부되지 않았습니다.'
							));
							exit;
						}
						//=================================================================================
						$error = $_FILES['userfile']['error'];
						if( $error != UPLOAD_ERR_OK ) {
							switch( $error ) {
								case UPLOAD_ERR_INI_SIZE:
								case UPLOAD_ERR_FORM_SIZE:
									$message = "파일이 너무 큽니다. ($error)";
									break;
								case UPLOAD_ERR_NO_FILE:
									$message = "파일이 첨부되지 않았습니다. ($error)";
									break;
								default:
									$message = "파일이 정상적으로 업로드되지 않았습니다. ($error)";
							}
							echo json_encode( array(
									'status' => 'error2',
									'message' => $message
							));
							exit;
						}
						//================================================================================= 
						// 변수 정리
						$name = $_FILES['userfile']['name'];
						//$ext = array_pop(explode('.', $name));
						$array_filename = explode('.',$name);//파일 이름(파일명.확장자) explode
						//$filename_name = strtoupper($array_filename[0]); //파일명 추출 (대문자변환)
						$filename_name = $array_filename[0]; //파일명 추출 
						//------------------------------------------------------------------------------
						$filename_ext = strtolower($array_filename[1]); //확장자 추출 (소문자변환)
						//------------------------------------------------------------------------------ 
						$Full_filename_name = $filename_name.".".$filename_ext; //파일명+확장자 
						//=================================================================================
						
						
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
//개별설정 start
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

						switch( $DetailAction) {
							case "insa_image":
								//-----------------------------
								//인사이미지 등록////////////////////
								
								include ('AddCode01_upload_insa_image.php');
								
								
// 								//설정SET-----------------------------
// 								$Uploads_dir = $Uploads_dir==""?"./../../../PersImg/":$Uploads_dir;
// 								$fileSize_check = "5242880";//5mb
// 								$allowed_ext = array('jpg');//허용파일 확장자
// 								$UniqFileName_boolean = false;// 같은 파일 이름이 있는지 검사 후 , 유니크 파일명값 설정
// 								$UniqFileName_Type = "";//중복파일 존재시, 새로운 파일명 생성시 사용할 인덱스 정의(default="" => "파일명_1...) : ex) date("YmdHis")
// 								//-----------------------------
// 								//기타
// 								if($filename_name!=$item01){
// 									//업로드 파일명이 사원번호와 다를경우 사번으로 치환
// 									$filename_name=$item01;
// 									$Full_filename_name = $filename_name.".".$filename_ext; //파일명+확장자
// 								}
// 								//-----------------------------
								
								break;
							case "분기추가...":
								//-----------------------------
								//etc.////////////////////
								//-----------------------------
								break;
							default:
								
						}
						
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
//개별설정 end
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
						
						
						if (is_dir ($Uploads_dir)){//true:경로가 있으면
							//경로생성 안함////////
						}else{//true:경로가 없으면 : 경로생성
							mkdir($Uploads_dir, 0777, true);
						}
 						//=================================================================================
						// 확장자 확인
						if( !in_array($filename_ext, $allowed_ext) ) {
							echo json_encode( array(
									'status' => 'error3',
									'message' => '허용되지 않는 확장자입니다'
							));
							exit;
						}
						//=================================================================================
						//기타정보
						$upload_fileType = $_FILES['userfile']['type'];
						//=================================================================================
						// 파일용량 분기
						$upload_fileSize = $_FILES['userfile']['size']; //업로드 대상파일
						
						if( $upload_fileSize > $fileSize_check ) {
							echo json_encode( array(
									'status' => 'error4',
									'message' => '업로드 파일용량이 허용치를 초과합니다.('.$upload_fileSize.'>'.$fileSize_check.')'
							));
							exit;
						}
						//=================================================================================
						// 같은 파일 이름이 있는지 검사 후 , 유니크 파일명값 설정
						if($UniqFileName_boolean){
							// 같은 파일 이름이 있는지 검사 후 , 유니크값 리턴
							//GetUniqFileName($FN, $PN);//$FN =파일명(확장자포함), $PN=저장경로, $UniqFileName_Type=인덱스지정 : //inc/function_add.php
							$re_fileName = GetUniqFileName($this->HangleEncodeUTF8_EUCKR($Full_filename_name), $Uploads_dir,$UniqFileName_Type); 
							$Full_filename_name=$re_fileName;
						}	
						//=================================================================================
						// 파일 이동
						if(move_uploaded_file( $_FILES['userfile']['tmp_name'], $Uploads_dir.$Full_filename_name)){
							// 파일 정보 출력
							echo json_encode( array(
									'status' => '1',
									'message' =>'success',
									'location'=>$Uploads_dir,
									'name' => $filename_name,
									'ext' => $filename_ext
							));
						}else{
							echo json_encode( array(
									'status' => 'error5',
									'message' => 'fail : function : move_uploaded_file '
							));
							exit;
						}
						break;
						//--------------------------------------------------------------------------------------------
						
					case "delete":	
						//=================================================================================
						//단일파일 삭제//////////////////////////////////////////////////////////////////////
						//=================================================================================					
						switch( $DetailAction) {
							case "insa_image":
								//-----------------------------
								//인사이미지 삭제////////////////////
								//-----------------------------
								include ('AddCode02_delete_insa_image.php');
								
								
								
// 								$Uploads_dir = $Uploads_dir==""?"./../../../PersImg/":$Uploads_dir;
// 								$newPath = $Uploads_dir.$item01.".jpg";
// 								if(file_exists($newPath)){
// 									//해당경로에 파일존재시 //삭제한다.
// 									if(unlink($newPath)){
// 										echo json_encode( array(
// 												'status' => '1',
// 												'message' =>'success'
// 										));
// 									}else{
// 										echo json_encode( array(
// 												'status' => 'error6',
// 												'message' => 'fail : unlink '
// 										));
// 										exit;
// 									}
// 								}else{
// 										echo json_encode( array(
// 												'status' => 'error7',
// 												'message' => 'fail : file_exists '.$newPath
// 										));
// 										exit;
// 								}
								//----------------------------------
								break;
						
							case "etc...":
								//-----------------------------
								//etc.////////////////////
								//-----------------------------
								break;
							default: 
						} 
						break;
						//--------------------------------------------------------------------------------------------
						
					default:
						echo "선택값이 불분명합니다.";
						exit();
						break;
				}
			}//SCREEN_01_001_Ajax_01

			
			function SCREEN_01_001_Ajax_02($mode=true){
				extract($_REQUEST);
				session_start();
				switch($SubAction){
					//----------------------------------------------------------------------------
					case "upload":
						//=================================================================================
						//멀티파일 업로드//////////////////////////////////////////////////////////////////////
						//=================================================================================
						//기본설정1
						$Uploads_dir = $Uploads_dir==""?"":$Uploads_dir; //경로설정
						$fileSize_check = "104857600";//100mb //저장용량설정
						// 						$fileSize_check = "10485760";//10mb
						// 						$fileSize_check = "5242880";//5mb
						// 						$fileSize_check = "1048576";//1mb
						$allowed_ext = array('jpg','jpeg','png','gif','txt','xlsx','doc','zip','ppt','pptx','pdf','hwp','xls');//저장가능파일설정
						$UniqFileName_boolean = true;// true/false 같은 파일 이름이 있는지 검사 후 , 유니크 파일명값 설정
						$UniqFileName_Type = "";//중복파일 존재시, 새로운 파일명 생성시 사용할 인덱스 정의(default="" => "파일명_1...) : ex) date("YmdHis")
						$UploadAll_boolean = false;// true/false 넘어오는 모든 file 모두 필수입력설정.
						//------------------------------------------------------------------
						//기본설정2
						$Upload_fail_cnt = 0;
						$Empty_cnt = 0;
						//------------------------------------------------------------------
						
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
//개별설정
switch( $DetailAction) {
	case "test":
		//------------------------------------------------------------------
		$Uploads_dir = $Uploads_dir==""?"":$Uploads_dir; //경로설정
		$fileSize_check = "104857600";//100mb //저장용량설정
		// 						$fileSize_check = "10485760";//10mb
		// 						$fileSize_check = "5242880";//5mb
		// 						$fileSize_check = "1048576";//1mb
		
		$allowed_ext = array('jpg','jpeg','png','gif','txt','xlsx','doc','zip','ppt','pptx','pdf','hwp','xls');//저장가능파일설정

		$UniqFileName_boolean = true;// true/false 같은 파일 이름이 있는지 검사 후 , 유니크 파일명값 설정
		$UniqFileName_Type = "";//중복파일 존재시, 새로운 파일명 생성시 사용할 인덱스 정의(default="" => "파일명_1...) : ex) date("YmdHis")
		$UploadAll_boolean = false;// true/false 넘어오는 모든 file 모두 필수입력설정.
		//------------------------------------------------------------------
		break;

	case "분기추가...":
		//-----------------------------
		//etc.////////////////////
		//-----------------------------
		break;
	default:

}
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
						$filename = $_FILES['userfile'];
						//------------------------------------------------------------
						if ( $Uploads_dir==""){//true:경로가 있으면
								$Upload_fail_cnt++;
								echo json_encode( array(
										'status' => 'error',
										'message' => '파일을 업로드할 경로값을 넘겨주시기 바랍니다.'
								));
								exit;
						}else if(is_dir ($Uploads_dir)){
							//true:경로가 있으면
							
						}else{//true:경로가 없으면 : 경로생성
							//mkdir($Uploads_dir, 0777, true);
							if (!mkdir($Uploads_dir, 0777, true)){
								$Upload_fail_cnt++;
								echo json_encode( array(
										'status' => 'error0',
										'message' => '파일을 업로드할 경로생성에 실패했습니다(관리자에게 문의하세요).'
								));
								exit;
							}
						}
						//=================================================================================
						for($i=0;$i<count($filename);$i++){
							if($_FILES['userfile']['name'][$i] == "") {
								$Empty_cnt++;
							}//if
						}//for
						//=================================================================================
						if(($UploadAll_boolean && $Empty_cnt>0) || empty($_FILES['userfile'])){
							$Upload_fail_cnt++;
							echo json_encode( array(
									'status' => 'error1',
									'message' => '첨부되지 않은 파일이 존재합니다.'
							));
							exit;
						}
						//=================================================================================
						if(!empty($filename))
						{
							$filename_desc = $this->reArrayFiles($filename);
							//print_r($filename_desc);
						
							foreach($filename_desc as $val)
							{
								//=================================================================================
								$error = $val['error'];
								if( $error != UPLOAD_ERR_OK ) {
									switch( $error ) {
										case UPLOAD_ERR_INI_SIZE:
										case UPLOAD_ERR_FORM_SIZE:
											$message = "파일이 너무 큽니다. ($error)";
											break;
										case UPLOAD_ERR_NO_FILE:
											$message = "파일이 첨부되지 않았습니다. ($error)";
											break;
										default:
											$message = "파일이 정상적으로 업로드되지 않았습니다. ($error)";
									}
									$Upload_fail_cnt++;
									echo json_encode( array(
											'status' => 'error2',
											'message' => $message
									));
									break;
									exit;
								}
								//=================================================================================
								// 변수 정리
								$name = $val['name'];
								//$ext = array_pop(explode('.', $name));
								$array_filename = explode('.',$name);//파일 이름(파일명.확장자) explode
								$filename_name = strtoupper($array_filename[0]); //파일명 추출 (대문자변환)
								//------------------------------------------------------------------------------
								$filename_ext = strtolower($array_filename[1]); //확장자 추출 (소문자변환)
								//------------------------------------------------------------------------------
								// 확장자 확인
								if( !in_array($filename_ext, $allowed_ext) ) {
									$Upload_fail_cnt++;
									echo json_encode( array(
											'status' => 'error3',
											'message' => '허용되지 않는 확장자입니다'
									));
									break;
									exit;
								}
								//=================================================================================
								//기타정보
								$upload_fileType = $val['type'];
								//=================================================================================
								// 파일용량 분기
								$upload_fileSize = $val['size']; //업로드 대상파일
						
								if( $upload_fileSize > $fileSize_check ) {
									$Upload_fail_cnt++;
									echo json_encode( array(
											'status' => 'error4',
											'message' => '업로드 파일용량이 허용치를 초과합니다.('.$upload_fileSize.'>'.$fileSize_check.')'
									));
									break;
									exit;
								}
								//=================================================================================
								$Full_filename_name = $filename_name.".".$filename_ext; //파일명+확장자
								//=================================================================================
								// 같은 파일 이름이 있는지 검사 후 , 유니크 파일명값 설정
								if($UniqFileName_boolean){
									// 같은 파일 이름이 있는지 검사 후 , 유니크값 리턴
									//GetUniqFileName($FN, $PN);//$FN =파일명(확장자포함), $PN=저장경로, $UniqFileName_Type=인덱스지정 : //inc/function_add.php
									$re_fileName = GetUniqFileName($this->HangleEncodeUTF8_EUCKR($Full_filename_name), $Uploads_dir,$UniqFileName_Type);
									$Full_filename_name=$re_fileName;
								}
								//=================================================================================
								// 파일 이동
								if(move_uploaded_file( $val['tmp_name'], $Uploads_dir.$Full_filename_name)){
									// 파일 정보 출력
								}else{
									$Upload_fail_cnt++;
									echo json_encode( array(
											'status' => 'error5',
											'message' => 'fail : function : move_uploaded_file : '.$Full_filename_name
									));
									break;
									exit;
								}
								//=================================================================================
								/*
								 $val['name']
								 $val['tmp_name']
								 $val['type']
								 $val['size']
								 $val['error']
								 */
							}//foreach
							
							if($Upload_fail_cnt<1){
								echo json_encode( array(
										'status' => '1',
										'message' =>'success : multy file upload',
										'location'=>$Uploads_dir
								));
							}
						}//if
						//========================================================================================
						break;
						//--------------------------------------------------------------------------------------------
			
					case "delete":
						//=================================================================================
						//멀티파일 삭제//////////////////////////////////////////////////////////////////////
						//=================================================================================
						switch( $DetailAction) {
							case "insa_image":
								//-----------------------------
								//인사이미지 삭제////////////////////
								//-----------------------------
								$Uploads_dir = $Uploads_dir==""?"./../../../PersImg/":$Uploads_dir;
								$newPath = $Uploads_dir.$item01.".jpg";
								if(file_exists($newPath)){
									//해당경로에 파일존재시 //삭제한다.
									if(unlink($newPath)){
										echo json_encode( array(
												'status' => '1',
												'message' =>'success'
										));
									}else{
										echo json_encode( array(
												'status' => 'error6',
												'message' => 'fail : unlink '
										));
										exit;
									}
								}else{
									echo json_encode( array(
											'status' => 'error7',
											'message' => 'fail : file_exists: not exist file '.$newPath
									));
									exit;
								}
								//----------------------------------
								break;
			
							case "test":
								//-----------------------------
								//etc.////////////////////분기추가
								//-----------------------------
								echo json_encode( array(
										'status' => '1',
										'message' =>'success'
								));
								//-----------------------------
								break;
							default:
			
						}
			
			
						break;
						//--------------------------------------------------------------------------------------------
			
					default:
						echo "선택값이 불분명합니다.";
						exit();
						break;
				}
			}//SCREEN_01_001_Ajax_02		
			

			

			//=================================================
			// FTP 업로드 페이지
			//=================================================
			function SCREEN_01_FTP(){
				extract($_REQUEST);
				$this->PrintExcelHeader("외주계약관리");
			
				if($this->excel != "" ){
					//$this->SCREEN_01_001_Ajax_01();
				}else{
					$CommonCode=new CommonCodeList($this->smarty);
					switch($MainAction){
						case "Html_01": 	$this->SCREEN_01_FTP_HTML_01();	break;
						case "Ajax_01": 	$this->SCREEN_01_FTP_Ajax_01();	break;
						default:
// 							$CommonCode->QueryCodeList("진행상태","input_select_02","");
// 							$CommonCode->ProjectQueryCode("부서","input_select_03","");
// 							$CommonCode->ProjectQueryCode("계약구분","input_select_04","");
// 							$CommonCode->MakeOption("전문공정", 'JqSelect_item01', 'Query' ); // /util/CommonCodeList.php
// 							//--------------------------------------------------------------------------------
// 							//global $nowYear; 	//금년:yyyy : /inc/getNeedDate.php
// 							//global $nowMonth;  //현재 년월:yyyy-mm : /inc/getNeedDate.php
// 							global $nowYear;
// 							global $date_today;
// 							$this->smarty->assign("start_day",$nowYear."-01-01");
// 							$this->smarty->assign("end_day",$date_today);
							//--------------------------------------------------------------------------------
							break;
					}
				}
			}//SCREEN_FTP_01
			
			
			
			function ftp_mksubdirs($ftpcon,$ftpbasedir,$ftpath){
				@ftp_chdir($ftpcon, $ftpbasedir); // /var/www/uploads
				$parts = explode('/',$ftpath); // 2013/06/11/username
				foreach($parts as $part){
					if(!@ftp_chdir($ftpcon, $part)){
						ftp_mkdir($ftpcon, $part);
						ftp_chdir($ftpcon, $part);
						//ftp_chmod($ftpcon, 0777, $part);
					}
				}
			}
			
			
			
			function SCREEN_01_FTP_HTML_01($mode=true){
				extract($_REQUEST);
				session_start();
				switch($SubAction){
					//----------------------------------------------------------------------------
					//외주계약관리 : 문서내역Tab:첨부파일
					case "select":
						//PMeContContract_01 : 외주계약관리
						//PMeContOrder : 외주발주의뢰서 작성
						if($ActionType=="PMeContContract_01" || $ActionType=="PMeContOrder"){
							//echo $_SESSION['satis_user_deptcode'];
							if($_SESSION['satis_user_deptcode']=="C0101"){
								$this->smarty->assign("InputTypeDeveloper","text");
							}else{
								$this->smarty->assign("InputTypeDeveloper","hidden");
							}
							
							$this->smarty->assign("userid",$_SESSION['satis_user_id']);
							$this->smarty->assign("username",$_SESSION['satis_user_name']);
							$this->smarty->assign("deptcode",$_SESSION['satis_user_deptcode']);
							
							$this->smarty->assign("ActionMode",$ActionMode);
							$this->smarty->assign("MainAction",$MainAction);
							$this->smarty->assign("SubAction",$SubAction);
							
							$this->smarty->assign("ActionType",$ActionType);
							
							
							$this->smarty->assign("FileLocation",$FileLocation);
							$this->smarty->assign("ReturnDetect",$ReturnDetect);
							
							$this->smarty->assign("param_01",$param_01);
							$this->smarty->assign("param_02",$param_02);
							$this->smarty->assign("param_03",$param_03);
							$this->smarty->assign("param_04",$param_04);
							$this->smarty->assign("param_05",$param_05);
							
							
							$this->smarty->assign("rowid",$rowid);
							
							
							
						}
						
						if($mode)
							$this->smarty->display("Satis/Fileupload/SCREEN_01_FTP_HTML_01_mvc.tpl");
						
					
					//----------------------------------------------------------------------------
					case "upload": case "down": case "delete":
						//====================================================================================================
							// ftp는 상대경로, 절대경로가 허용되지 않으며,
							// 보통 public_html, www, html 로 시작합니다.
							// public_html/userid 에 자료를 저장한다면,
							// ftp 경로는 "public_html/userid/파일" 이 됩니다.
							
							// B 호스트에서 가져올 실제 파일
							
							// ActionType => down 인지 upload인지 구분
							// Route => 경로
							// remote_file => down(파일명 확장자까지), upload(input[file])
							
						if($ActionType=="PMeContContract_01"){
// 							$this->smarty->assign("userid",$_SESSION['satis_user_id']);
// 							$this->smarty->assign("username",$_SESSION['satis_user_name']);
// 							$this->smarty->assign("deptcode",$_SESSION['satis_user_deptcode']);
						
// 							$this->smarty->assign("ActionMode",$ActionMode);
// 							$this->smarty->assign("MainAction",$MainAction);
// 							$this->smarty->assign("SubAction",$SubAction);
						
// 							$this->smarty->assign("FileLocation",$FileLocation);
// 							$this->smarty->assign("ReturnDetect",$ReturnDetect);
						
// 							$this->smarty->assign("param_01",$param_01);
// 							$this->smarty->assign("param_02",$param_02);
// 							$this->smarty->assign("param_03",$param_03);
// 							$this->smarty->assign("param_04",$param_04);
// 							$this->smarty->assign("param_05",$param_05);
						}
						
						
						// B 호스트 정보
						$ftp_server = "118.220.172.233";	// FTP 주소
						$ftp_port = "21";	// FTP 주소
						$ftp_user_name = "admin";			// 접속 ID
						$ftp_user_pass = "sg11707808";		// 접속 PW
						
						// B 호스트 접속
						if(!($conn_id = ftp_connect($ftp_server, $ftp_port))){
							die("$ftp_server : $server_post - connect failed");
						}
						// B 호스트 로그인
						if(!ftp_login($conn_id, $ftp_user_name, $ftp_user_pass)){
							die("$ftp_user_name - login failed");
						}
						
						if($SubAction == 'upload'){
							//print_r($_FILES);
							if($FileName != 'undefined'){
								$_FILES['remote_file']['name'] = $FileName;
							}
							$remote_file = $_FILES['remote_file']['name'];
							$remote_file = iconv("UTF-8","EUC-KR",$remote_file) ? iconv("UTF-8","EUC-KR",$remote_file) : $remote_file;
							
							
							//echo '='.$remote_file;
							
							//확장자 추출
							$filename_ext = strtolower(array_pop(explode('.',$remote_file))); //확장자 추출
							
							
							//echo '='.$filename_ext;
							
							
							
							$re_array = explode(".".$filename_ext,$remote_file);
							
							//print_r($re_array[0]);
							
							//파일이름만 추출(확장자제외)
						//	$filename_only = basename($remote_file, ".".$filename_ext);//파일이름만 추출(확장자제외)
							
							$filename_only = $re_array[0];
							
			
							//echo '='.$remote_file;
							
							if($ActionType=="PMeContContract_01"){
								$CH_filename_full = $filename_only.'['.date("YmdHis").'].'.$filename_ext;
								
								$remote_file = $CH_filename_full;
								
								$vupload = "./../../../../satis_file/temp/".$remote_file;
								
							}else{
								$vupload = "./../../../../satis_file/temp/".$remote_file;
							}
	
							
							$vupload = str_replace(" ","",$vupload);
							$vupload = str_replace("#","",$vupload);
							//echo $_FILES['remote_file']['tmp_name'];
							//echo $vupload;
						
							//echo 'remote_file2='.$remote_file;
							
							//$_FILES['remote_file']['tmp_name'] = iconv("UTF-8","EUC-KR",$_FILES['remote_file']['tmp_name']) ? iconv("UTF-8","EUC-KR",$_FILES['remote_file']['tmp_name']) : $_FILES['remote_file']['tmp_name'];
						
							move_uploaded_file($_FILES['remote_file']['tmp_name'], $vupload);
						
							//============================================================
							//경로없을 시 경로 생성하고 해당경로로 이동
							$parts = explode('/',$FileLocation); // 2013/06/11/username
							foreach($parts as $part){
								if($part!=""){
									if(!@ftp_chdir($conn_id, $part)){
										ftp_mkdir($conn_id, $part);
										ftp_chdir($conn_id, $part);
										//ftp_chmod($ftpcon, 0777, $part);
									}
								}
							}
							//============================================================
							
							//기존
							//ftp_chdir($conn_id, $FileLocation);
							
							//echo 'remote_file3='.$remote_file;
							// 원격서버에 업로드될 파일명
							$filename = $remote_file;
							// B 호스트에 저장될 실제 파일
							//$tmpfile = $_FILES['remote_file']['tmp_name'];
							//echo $vupload;
						
						
							//파일을 업로드 한다.
							if(!ftp_put($conn_id, $filename, $vupload, FTP_BINARY)){
						
								echo '2';
								
								exit;
							}else{
								
							//	echo 'remote_file4='.$remote_file;
								
								$remote_file = iconv("EUC-KR","UTF-8",$remote_file) ? iconv("EUC-KR","UTF-8",$remote_file) : $remote_file;
								//print_r(array('1',$remote_file));
								echo $remote_file;
								
							}
						
							$re_unlink = unlink($vupload); //파일삭제
							
						}else if($SubAction == 'down'){
			
						
							$remote_file = iconv("UTF-8","EUC-KR",$remote_file) ? iconv("UTF-8","EUC-KR",$remote_file) : $remote_file;
						
							if($FileLocation){
								$down_file = $FileLocation.$remote_file;
							}else{
								$down_file = $remote_file;
							}
							// A 호스트로 저장하거나 브라우저로 출력해야 할 파일
							$local_file = "./../../../../satis_file/temp/".$remote_file;
						
							// 임시 파일을 엽니다.
							$fp = fopen($local_file, 'w+');
						
							// 파일을 A 호스트로 업로드하고,
							// $file 로 다운로드하거나 저장할 코드를 작성하면 됩니다.
							// ftp_get 은 로컬에서만 가능하므로 ftp_fget을 사용합니다.
							if (ftp_fget($conn_id, $fp, $down_file, FTP_BINARY, 0)) {
								while(!feof($fp)){
									$file .= fread($fp, 1024);
								}
						
								// 파일 다운로드나 파일 출력 처리 부분입니다.
								//$remote_file = iconv("UTF-8","EUC-KR",$remote_file) ? iconv("UTF-8","EUC-KR",$remote_file) : $remote_file;
								//$Downfile = iconv("UTF-8","EUC-KR",$local_file) ? iconv("UTF-8","EUC-KR",$local_file) : $local_file;
								//echo $Downfile;
								$divfile=explode("/",$local_file);
								$divnum = count($divfile);
						
								if (file_exists($local_file)) {
									header('Content-Description: File Transfer');
									header('Content-Type: application/octet-stream');
						
									//header('Content-Disposition: attachment; filename="'.basename($viewName).'"');
									header('Content-Disposition: attachment; filename="'.preg_replace( '/^.+[\\\\\\/]/', '', $remote_file ).'"');
									header('Expires: 0');
									header('Cache-Control: must-revalidate');
									header('Pragma: public');
									header('Content-Length: ' . filesize($local_file));
								}
								readfile($local_file);
								$re_unlink = unlink($local_file); //파일삭제
						
								echo 1;
								exit;
							} else {
								echo "There was a problem while downloading $remote_file to $local_file\n";
							}
						
							fclose($fp);
						}else if($SubAction == 'delete'){
							
									$remote_file = iconv("UTF-8","EUC-KR",$remote_file) ? iconv("UTF-8","EUC-KR",$remote_file) : $remote_file;
									
									
									//echo $FileLocation.$remote_file;
									
									//업로드할 폴더로 이동한다.
									ftp_chdir($conn_id, $FileLocation);
									$res = ftp_size($conn_id, $remote_file);
									if ($res != -1) {
										if(!ftp_delete($conn_id, $remote_file)){
											echo" <script> window.alert ('파일을 지정한 디렉토리에서 삭제 하는 데 실패했습니다._1');</script>";
											exit;
										}else{
											echo 1;
										}
									}
									
                          			
							
						}
						
						ftp_close($conn_id);
						
						
						
						break;
							
						//----------------------------------------------------------------------------
					case "test":
						//====================================================================================================

						break;
						//--------------------------------------------------------------------------------------------
					default:
						echo "선택값이 불분명합니다.";
						exit();
						break;
				}


				

			}//SCREEN_01_FTP_HTML_01
			
			
			function SCREEN_01_FTP_Ajax_01($mode=true){
				extract($_REQUEST);
				session_start();
				switch($SubAction){
					//----------------------------------------------------------------------------
					//외주계약관리 : 문서내역Tab:첨부파일
					case "PMeContContract_01":
			
						$this->smarty->assign("userid",$_SESSION['satis_user_id']);
						$this->smarty->assign("username",$_SESSION['satis_user_name']);
						$this->smarty->assign("deptcode",$_SESSION['satis_user_deptcode']);
			
						$this->smarty->assign("ActionMode",$ActionMode);
						$this->smarty->assign("MainAction",$MainAction);
						$this->smarty->assign("SubAction",$SubAction);
						
						$this->smarty->assign("ActionType",$ActionType);
						
						$this->smarty->assign("FileLocation",$FileLocation);
						$this->smarty->assign("ReturnDetect",$ReturnDetect);
			
						$this->smarty->assign("param_01",$param_01);
						$this->smarty->assign("param_02",$param_02);
						$this->smarty->assign("param_03",$param_03);
						$this->smarty->assign("param_04",$param_04);
						$this->smarty->assign("param_05",$param_05);
						
						// ftp는 상대경로, 절대경로가 허용되지 않으며,
						// 보통 public_html, www, html 로 시작합니다.
						// public_html/userid 에 자료를 저장한다면,
						// ftp 경로는 "public_html/userid/파일" 이 됩니다.
						
						// B 호스트에서 가져올 실제 파일
						
						// ActionType => down 인지 upload인지 구분
						// Route => 경로
						// remote_file => down(파일명 확장자까지), upload(input[file])
						
						// B 호스트 정보
						$ftp_server = "118.220.172.233";	// FTP 주소
						$ftp_port = "21";	// FTP 주소
						$ftp_user_name = "admin";			// 접속 ID
						$ftp_user_pass = "sg11707808";		// 접속 PW
						
						// B 호스트 접속
						if(!($conn_id = ftp_connect($ftp_server, $ftp_port))){
							die("$ftp_server : $server_post - connect failed");
						}
						// B 호스트 로그인
						if(!ftp_login($conn_id, $ftp_user_name, $ftp_user_pass)){
							die("$ftp_user_name - login failed");
						}
						
						if($ActionType == 'upload'){
							//print_r($_FILES);
							if($FileName != 'undefined'){
								$_FILES['remote_file']['name'] = $FileName;
							}
							$remote_file = $_FILES['remote_file']['name'];
							$remote_file = iconv("UTF-8","EUC-KR",$remote_file) ? iconv("UTF-8","EUC-KR",$remote_file) : $remote_file;
							$vupload = "./../../../../satis_file/temp/".$remote_file;
							$vupload = str_replace(" ","",$vupload);
							$vupload = str_replace("#","",$vupload);
							//echo $_FILES['remote_file']['tmp_name'];
							//echo $vupload;
						
							//$_FILES['remote_file']['tmp_name'] = iconv("UTF-8","EUC-KR",$_FILES['remote_file']['tmp_name']) ? iconv("UTF-8","EUC-KR",$_FILES['remote_file']['tmp_name']) : $_FILES['remote_file']['tmp_name'];
						
							move_uploaded_file($_FILES['remote_file']['tmp_name'], $vupload);
						
							
							$mkdir = ftp_mkdir($conn_id, $FileLocation);
							
							
							
							//업로드할 폴더로 이동한다.
							ftp_chdir($conn_id, $FileLocation);
						
							// 디비에 저장될 파일 이름
							$filename = $remote_file;
							// B 호스트에 저장될 실제 파일
							//$tmpfile = $_FILES['remote_file']['tmp_name'];
							//echo $vupload;
						
						
							//파일을 업로드 한다.
							if(!ftp_put($conn_id, $filename, $vupload, FTP_BINARY)){
								echo"파일을 지정한 디렉토리로 복사 하는 데 실패했습니다.";
								exit;
							}else{
								echo 1;
							}
						
							$re_unlink = unlink($vupload); //파일삭제
						}else if($ActionType == 'down'){
							//$remote_file = "C003175[2016823141515].pdf";
							//print_r($_REQUEST);
						
							$remote_file = iconv("UTF-8","EUC-KR",$remote_file) ? iconv("UTF-8","EUC-KR",$remote_file) : $remote_file;
						
							if($FileLocation){
								$down_file = $FileLocation.$remote_file;
							}else{
								$down_file = $remote_file;
							}
							// A 호스트로 저장하거나 브라우저로 출력해야 할 파일
							$local_file = "./../../../../satis_file/temp/".$remote_file;
						
							// 임시 파일을 엽니다.
							$fp = fopen($local_file, 'w+');
						
							// 파일을 A 호스트로 업로드하고,
							// $file 로 다운로드하거나 저장할 코드를 작성하면 됩니다.
							// ftp_get 은 로컬에서만 가능하므로 ftp_fget을 사용합니다.
							if (ftp_fget($conn_id, $fp, $down_file, FTP_BINARY, 0)) {
								while(!feof($fp)){
									$file .= fread($fp, 1024);
								}
						
								// 파일 다운로드나 파일 출력 처리 부분입니다.
								//$remote_file = iconv("UTF-8","EUC-KR",$remote_file) ? iconv("UTF-8","EUC-KR",$remote_file) : $remote_file;
								//$Downfile = iconv("UTF-8","EUC-KR",$local_file) ? iconv("UTF-8","EUC-KR",$local_file) : $local_file;
								//echo $Downfile;
								$divfile=explode("/",$local_file);
								$divnum = count($divfile);
						
								if (file_exists($local_file)) {
									header('Content-Description: File Transfer');
									header('Content-Type: application/octet-stream');
						
									//header('Content-Disposition: attachment; filename="'.basename($viewName).'"');
									header('Content-Disposition: attachment; filename="'.preg_replace( '/^.+[\\\\\\/]/', '', $remote_file ).'"');
									header('Expires: 0');
									header('Cache-Control: must-revalidate');
									header('Pragma: public');
									header('Content-Length: ' . filesize($local_file));
								}
								readfile($local_file);
								$re_unlink = unlink($local_file); //파일삭제
						
								echo 1;
								exit;
							} else {
								echo "There was a problem while downloading $remote_file to $local_file\n";
							}
						
							fclose($fp);
						}else if($ActionType == 'delete'){
							$remote_file = iconv("UTF-8","EUC-KR",$remote_file) ? iconv("UTF-8","EUC-KR",$remote_file) : $remote_file;
							//업로드할 폴더로 이동한다.
							ftp_chdir($conn_id, $FileLocation);
							$res = ftp_size($conn_id, $remote_file);
						
							if ($res != -1) {
								if(!ftp_delete($conn_id, $remote_file)){
									echo" <script> window.alert ('파일을 지정한 디렉토리에서 삭제 하는 데 실패했습니다._1');</script>";
									exit;
								}else{
									echo 1;
								}
							}
						}
						ftp_close($conn_id);
						
						break;
						//----------------------------------------------------------------------------
					case "test":
						//====================================================================================================
			
						break;
						//--------------------------------------------------------------------------------------------
					default:
						echo "선택값이 불분명합니다.";
						exit();
						break;
				}
			
				
			}//SCREEN_01_FTP_Ajax_01
						
			
//======================================================================================================
//======================================================================================================
		//파일업로드 관련 펑션
		function reArrayFiles($file)
		{
			$file_ary = array();
			$file_count = count($file['name']);
			$file_key = array_keys($file);
		
			for($i=0;$i<$file_count;$i++)
			{
				foreach($file_key as $val)
				{
					$file_ary[$i][$val] = $file[$val][$i];
				}
			}
			return $file_ary;
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

		function PrintExcelHeader($filename)
		{
			$filename = $this->HangleEncodeUTF8_EUCKR($filename);
			if($this->excel != "")
			{
				header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
				header("Content-type:   application/x-msexcel; charset=utf-8");
				header("Content-Disposition: attachment; filename=$filename.xls");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Cache-Control: private",false);
			}

		}
//======================================================================================================
//======================================================================================================

}
?>