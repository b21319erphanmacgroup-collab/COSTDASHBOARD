<?

	/***************************************
	* 일일 자금 일보
	* ------------------------------------
	* 2016-01-04 : 작업
	****************************************/

	include "../inc/dbcon.inc";
	include "../inc/function_mysql.php";
	include "../../../SmartyConfig.php";

	extract($_REQUEST);
	class Attachments_logic {
		var $smarty;
		var $year;
		var $today;
		var $start_month;
		var $start_day;
		var $end_month;
		var $end_day;
		var $memo;
		var $QueryDay;
		var $QueryDay2;
		var $oracle;

		function Attachments_logic($smarty)
		{
			global $emp_id;
			

			$this->smarty=$smarty;

			$this->PRINTYN=$_REQUEST['PRINT'];
			$this->start_day=$_REQUEST['start_day'];
			$this->end_day=$_REQUEST['end_day'];

		}

		//============================================================================
		// 첨부파일 업로드 화면
		//============================================================================
		function FileInput(){
			extract($_REQUEST);
			
			global $db,$memberID;

			$this->smarty->assign('memberID',$memberID);
			
			//증빙자료 존재 유무 확인
			
			$Detail1_arr=explode('_',$Detail1);
			
			$PJT_CODE=$Detail1_arr[0];
			$DGREE=$Detail1_arr[1];
			$WBS_CODE=$Detail1_arr[2];
						
			$Doc_Code = $PJT_CODE.'-'.$DGREE.'-'.$WBS_CODE;

			//첨부파일 존재 유무 확인
			$AddLocation = "./../../../intranet_file/documents/".$FormNum."/".$Doc_Code."/";
			
			if(file_exists($AddLocation)){
				
				$handle  = opendir($AddLocation);
				$files = array();
				
				// 디렉터리에 포함된 파일을 저장한다.
				while (false !== ($filename = readdir($handle))) {
					if($filename == "." || $filename == ".."){
						continue;
					}

					// 파일인 경우만 목록에 추가한다.
					if(is_file($AddLocation . "/" . $filename)){
						$files[] = $filename;
					}
				}
				//print_r($files);

				// 핸들 해제
				closedir($handle);
				$this->smarty->assign('attachfile',$files[0]);
				$attachfile = $files[0];
			}

			if($attachfile != ""){
				$this->smarty->assign('Doc_Code',$Doc_Code);
				$this->smarty->assign('FileLocation',$FileLocation);
				$this->smarty->assign('Addfile',$Addfile);
				$this->smarty->assign('DocSN',$DocSN);
			}
			
			$this->smarty->assign('doc_kind',$doc_kind);
			$this->smarty->assign('PJT_CODE',$PJT_CODE);
			$this->smarty->assign('DGREE',$DGREE);
			$this->smarty->assign('WBS_CODE',$WBS_CODE);
			$this->smarty->assign('FormNum',$FormNum);

			$this->smarty->display("intranet/common/file_Input_mvc.tpl");
		}

		//============================================================================
		// 첨부파일 업로드 실행
		//============================================================================
		function FileUploadAction(){
			global $db,$memberID;
			extract($_REQUEST);

			$dbinsert = true;
			//$dbinsert = false;

			$Doc_Code = $PJT_CODE.'-'.$DGREE.'-'.$WBS_CODE;
			//print_r($_FILES);
			if (!empty($_FILES)) {
				$files_data = array();
				$image_count = count($_FILES);

				$files_data['file']['name'][0] = $_FILES['AddFile']['name'];
				$files_data['file']['type'][0] = $_FILES['AddFile']['type'];
				$files_data['file']['tmp_name'][0] = $_FILES['AddFile']['tmp_name'];
				$files_data['file']['error'][0] = $_FILES['AddFile']['error'];
				$files_data['file']['size'][0] = $_FILES['AddFile']['size'];

				//print_r($files_data);
				$savefile = $this->file_upload($files_data, $Doc_Code, $Addfile, "./../../../intranet_file/documents/$FormNum/", 'file');
			}
			

			$sql = "select DocSN, Addfile from sanctiondoc_tbl where FormNum like '$FormNum'  and Detail1 like '$PJT_CODE' and Detail2 like '$DGREE' and Detail3 like '$WBS_CODE'";
			$re = mysql_query($sql,$db);
			if(mysql_num_rows($re) > 0){
				
				$DocSN		= @mysql_result($re,0,"DocSn");
				$Addfile	= @mysql_result($re,0,"Addfile");
				$temp = split('/n',$Addfile);
				$sql2 = "update SanctionDoc_tbl set Addfile ='".$temp[0]."/n".$savefile."' where DocSN = '$DocSN'";
				if($dbinsert){
					mysql_query($sql2,$db);
				}else{
					echo "[sql2--- ".$sql2."<br>";
				}
			}

			if($dbinsert){
				$filenamecnt=strlen($savefile);
				$savefile=substr($savefile,0,$filenamecnt-2);
				
				echo "<script>";
				//echo " window.opener.document.location.reload();";
				echo " alert('".iconv("UTF-8", "EUC-KR",'처리되었습니다.')."');";
				echo " window.opener.document.location.reload();";
				echo " self.close();";
				echo "</script>";
				
			}else{
				echo "[sql--- ".$sql."<br>";
			}

		}

		//============================================================================
		// 첨부파일 삭제 실행
		//============================================================================
		function AttachfileDel(){
			global $db,$memberID;
			extract($_REQUEST);

			$dbinsert = true;
			//$dbinsert = false;

			$doc_zero = '';
			$doc_count = 3-strlen($seq);
			for($f=0; $f<$doc_count; $f++){
				$doc_zero = '0'.$doc_zero;
			}
			$Doc_Code = $PJT_CODE.'-'.$DGREE.'-'.$WBS_CODE;
			//echo $Doc_Code ;

			$sql = "select DocSN, Addfile from sanctiondoc_tbl where FormNum like '$FormNum' and Detail1 like '$PJT_CODE' and Detail2 like '$DGREE' and Detail3 like '$WBS_CODE'";
			$re = mysql_query($sql,$db);
			if(mysql_num_rows($re) > 0){
				$DocSN	= @mysql_result($re,0,"DocSn");
				$temp_Addfile = split('/n', @mysql_result($re,0,"Addfile"));

				$sql2 = "update SanctionDoc_tbl set Addfile ='".$temp_Addfile[0]."/n' where DocSN = '$DocSN'";
				if($dbinsert){
					mysql_query($sql2,$db);
				}else{
					echo "[sql2--- ".$sql2."<br>";
				}
			}

			if($dbinsert){
				$this->removeDir("./../../../intranet_file/documents/".$FormNum.'/'.$Doc_Code);
				echo "<script>";
				echo " window.opener.document.location.reload();";
				echo " alert('".iconv("UTF-8", "EUC-KR",'처리되었습니다.')."');";
				echo " self.close();";
				echo "</script>";
			}else{
				echo "./../../../intranet_file/documents/".$FormNum.'/'.$Doc_Code;
			}
		}

		//============================================================================
		// 파일 업로드
		//============================================================================
		function file_upload($multyfile, $Doc_Code, $ex_Addfile, $path_top, $type = null){
			
			$path		= $path_top.$Doc_Code."/";			
			$path_is	= $path_top.$Doc_Code;
			
			if (!is_dir($path_is)){
				mkdir($path_is, 777);
			}

			$filename = "";
			//----------첨부파일 여러개 올리기------------------------------
			for($i=0; $i<count($multyfile['file']['name']); $i++) {
				

				if($type == 'file'){	//첨부파일

					//이미지 파일 삭제
					$this->removeDir($path_is);

					$multyfile[$i]=stripslashes($multyfile[$i]);
					$multyfileName = substr(strrchr($multyfile['file']['name'][$i],"."),1);

					$vupload = $path.$Doc_Code.'.'.$multyfileName;
					$vupload = str_replace(" ","",$vupload);
					$vupload = str_replace("#","",$vupload);
					$multyfileTmpName = iconv("UTF-8", "EUC-KR",$multyfile['file']['tmp_name'][$i]);

					move_uploaded_file($multyfileTmpName, $vupload);
					$multyfileName = $Doc_Code.'.'.$multyfileName;
				}else{	//증빙자료 이미지 업로드

					$multyfile[$i]=stripslashes($multyfile[$i]);
					$multyfileName = iconv("UTF-8", "EUC-KR",$multyfile['file']['name'][$i]);
					//$multyfileName = substr(strrchr($multyfile['multyfile']['name'][$i],"."),1);
					$vupload = $path.$multyfileName;
					$vupload = str_replace(" ","",$vupload);
					$vupload = str_replace("#","",$vupload);
					//echo "multyfile['multyfile']['tmp_name'][$i] : ".$multyfile['multyfile']['tmp_name'][$i]."<br>";
					$multyfileTmpName = iconv("UTF-8", "EUC-KR",$multyfile['file']['tmp_name'][$i]);
					//echo "multyfileTmpName : ".$multyfileTmpName."<br>" ;

					move_uploaded_file($multyfileTmpName, $vupload);
				}
				$filename_m = $multyfileName;
				$filename_m = str_replace(" ","",$filename_m);
				$filename_m = str_replace("#","",$filename_m);

				$filename .= $filename_m."/n";
			}

			return $filename;
		}

		function HangleEncode($item){
				$result=trim(ICONV("EUC-KR","UTF-8",$item));
				if(trim($result)=="") 	$result="&nbsp";
				return $result;
		}


		function bear3StrCut($str,$len,$tail=""){
			$rtn = array();
			return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str;
		}

		function removeDir ($path)
		{
			// 디렉토리 구분자를 하나로 통일시킴
			$path = str_replace('\'', '/', $path);

			// 경로 마지막에 존재하는 디렉토리 구분자는 삭제
			if ($path[(strlen($path)-1)] == '/') {
				$tmp = '';
				for ($i=0; $i < (strlen($path) -1); $i++) {
					$tmp .= $path[$i];
				}
				$path = $tmp;
			}

			// 존재하는 디렉토리인지 확인
			// 존재하지 않으면 false를 반환
			if (!file_exists($path)) {
				return false;
			}

			// 디렉토리 핸들러 생성
			$oDir = dir($path);

			// 디렉토리 하부 컨텐츠 각각에 대하여 분석하여 삭제
			while (($entry = $oDir->read())) {
				// 상위 디렉토리를 나타내는 문자열인 경우 처리하지 않고 continue
				if ($entry == '.' || $entry == '..') {
					continue;
				}

				// 또 다른 디렉토리인 경우 함수 실행
				// 파일인 경우 즉시 삭제
				if (is_dir($path.'/'.$entry)) {
					$this->removeDir($path.'/'.$entry);
				} else {
					unlink($path.'/'.$entry);
				}
			}

			// 해당 디렉토리 삭제
			//rmdir($path);

			// 결과에 따라 해당 디렉토리가 삭제되지 않고 존재하면 false를 반환 반대의 경우에는 true를 반환
			if (file_exists($path)) {
				return false;
			} else {
				return true;
			}
		}

}

?>