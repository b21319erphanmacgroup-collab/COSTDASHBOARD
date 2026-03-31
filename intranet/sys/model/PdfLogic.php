<?
class PdfLogic {
	var $smarty;
	var $oracle;
	function PdfLogic($smarty)
	{
		$this->smarty=$smarty;
	}

	function MakePDF()
	{
		global $TargetUrl;
		global $OutputName;
		global $SetOption;
		extract($_REQUEST);

		$OutputName = $OutputName==""?"MakePDF":$OutputName;
		$OutputName = $OutputName.".pdf"; //다운로드될 파일명(노출될 파일명)

		$TargetUrl="\"".$TargetUrl."\"";
		//$StoredFile = "D:/temp/Util/output/".date("YmdHis", time())."_".rand(111,999).".pdf";  // 서버저장후 삭제될, 파일명 생성 : rand(111,999) 111~999 사이의 랜덤숫자(세자리);
		
		$baseDir = join(DIRECTORY_SEPARATOR, array('D:', 'temp', 'Util', 'output'));
		$StoredFile = $baseDir . DIRECTORY_SEPARATOR . date('YmdHis') . '_' . rand(111, 999) . '.pdf';

		if(!$SetOption){
			// ********************************************************************************
			//출력옵션
			//Synopsis:
			//wkhtmltopdf [GLOBAL OPTION]... [OBJECT]... <output file>
			// Global Options:
			//       --collate                       Collate when printing multiple copies  (default)
			//       --no-collate                    Do not collate when printing multiple  copies
			//       --copies <number>               Number of copies to print into the pdf file (default 1)
			//   -H, --extended-help                 Display more extensive help, detailing  less common command switches
			//   -g, --grayscale                     PDF will be generated in grayscale
			//   -h, --help                          Display help
			//       --license                       Output license information and exit
			//   -l, --lowquality                    Generates lower quality pdf/ps. Useful to
			//                                       shrink the result document space
			//   -O, --orientation <orientation>     Set orientation to Landscape or Portrait (default Portrait)
			//   -s, --page-size <Size>              Set paper size to: A4, Letter, etc.  (default A4)
			//   -q, --quiet                         Be less verbose
			//       --read-args-from-stdin          Read command line arguments from stdin
			//       --title <text>                  The title of the generated pdf file (The title of the first document is used if not specified)
			//   -V, --version                       Output version information and exit
			// *********************************************************************************
			$SetOption  = " -O Portrait"; // -O, 용지 출력방향(Landscape/Portrait,가로/세로(default:Portrait)
			$SetOption .= " -s A4";    		//-s, 용지 사이즈          (default:A4)
			//$SetOption .= " -q 2>&1";    		//-q, 에러 로그 기록
		}

		//shell_exec("D:/temp/Util/wkhtmltopdf.exe ".$sendUrl." ".$StoredFile." ");
		
		$wkhtmltopdf = join(DIRECTORY_SEPARATOR, array('D:', 'temp', 'Util', 'wkhtmltopdf.exe'));
		$cmd = $wkhtmltopdf . $SetOption . " " . $TargetUrl . " "  . $StoredFile ." ";
		$execResult = exec($cmd, $out, $ret);
		//var_dump($execResult);
		
		if (file_exists($StoredFile)) {
			$this->PdfDownloadLink($StoredFile,$OutputName);
		} else {
			echo $this->getHtmlErrorMessage();
		}
		
	}//MakePDF

	function PdfDownloadLink($StoredFile,$viewName){
		extract($_REQUEST);
		if (file_exists($StoredFile)) {
			$viewName=trim($this->html_enc_convert($viewName,"UTF-8","CP949"));
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.preg_replace( '/^.+[\\\\\\/]/', '', $viewName ).'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($StoredFile));
		}
		readfile($StoredFile);
		$re_unlink=unlink($StoredFile); //파일삭제
		exit;

	}//PdfDownloadLink

	function html_enc_convert($str, $before_enc, $after_enc)
	{
		// 여기서 나오는 mb는 멀티바이트(multi-byte)의 약자.

		// $before_enc
		// 변환 전의 인코딩. 웹페이지의 인코딩에 따라 알맞게 변경한다.

		// $after_enc
		// 변환 후의 인코딩. 웹페이지의 인코딩에 따라 알맞게 변경한다.
		// 한국어라면 cp949보다는 euc-kr을 더 권장한다.
		// cp949는 확장 한글(ㄸㅗㅁ방각하)도 포함되어 있는 한글 코드,
		// euc-kr은 확장 한글이 들어있지 않은 반쪽짜리 한글 코드이므로 변환되는 문자가 더 많아져 깨지는 문자도 적을 것이다.

		$i=0;
		// 제일 처음부터 한 글자씩 시작해야 하므로 0으로 설정한다.

		$result = "";
		// 초기화

		$len = mb_strlen($str, $before_enc);
		// mb_strlen은 글자 단위, strlen은 바이트 단위로 글자 수를 인식한다.

		while ($i <= $len)
		{
				// 한 글자씩 가면서 한 글자씩 변환시킨다.
				// mb_convert_encoding은 변환 시 코드값에 오류가 생기는 경우 다른 무작위의 문자로 변환하여 다른 문자에 지장이 가지 않게 하고
				// iconv는 변환 시 코드값에 오류가 생기는 경우 변환을 멈추게 된다.

			if (mb_convert_encoding(mb_substr($str,$i,1,$before_enc), $after_enc, $before_enc)
				==iconv($before_enc,$after_enc,mb_substr($str,$i,1,$before_enc)))
			$result .= mb_convert_encoding(mb_substr($str,$i,1,$before_enc), $after_enc, $before_enc);
				// mb_convert_encoding과 iconv로 변환한 값을 비교하여 문자가 같을 경우(손상되지 않았을 경우)
				// 그대로 출력시킨다.

			else
			$result .= mb_convert_encoding(mb_substr($str,$i,1,$before_enc), "html-entities", $before_enc);
				// mb_convert_encoding과 iconv로 변환한 값을 비교하여 문자가 다를 경우(손상되었을 경우)
				// HTML 엔티티 문자로 변환시킨다.

			$i++;
				// 글자 변환 후 다음 글자로 나아간다.
		}
		return $result;
		// 변환이 모두 끝나면 최종값을 반환한다.
	} // html_enc_convert 끝

		function getHtmlErrorMessage($message = null) {
		if (empty($message)) {
			$message = "처리중 오류가 발생하였습니다.\n문제가 지속될경우 관리자에게 문의하세요.";
		}
		
		$html = "<html>";
			$html .= "<head>";
				$html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
			$html .= "<head>";
			$html .= "<body>";
				$html .= $message;
			$html .= "</body>";
		$html = "</html>";
	}
}
?>