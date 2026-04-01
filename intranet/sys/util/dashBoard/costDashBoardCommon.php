<?php 
	function validateDateFormat($date, $formats = array('Y-m-d')) {
		foreach ($formats as $format) {
			if (matchFormat($date, $format)) {
				return true;
			}
		}
		
		return false;
	}
	
	function matchFormat($date, $format) {
		// 포맷별 정규식 정의
		$patterns = array(
			'Y-m-d' => '/^\d{4}-\d{2}-\d{2}$/',
			'Ymd'   => '/^\d{8}$/',
			'Y/m/d' => '/^\d{4}\/\d{2}\/\d{2}$/'
		);
		
		if (!isset($patterns[$format])) {
			return false;
		}
		
		if (!preg_match($patterns[$format], $date)) {
			return false;
		}
		
		// 날짜 분리
		if ($format == 'Y-m-d') {
			list($y, $m, $d) = explode('-', $date);
		} else if ($format == 'Ymd') {
			$y = substr($date, 0, 4);
			$m = substr($date, 4, 2);
			$d = substr($date, 6, 2);
		} else if ($format == 'Y/m/d') {
			list($y, $m, $d) = explode('/', $date);
		}
		
		return checkdate((int)$m, (int)$d, (int)$y);
	}
	
	function getConvertedProjCode($projCode) {
		$projCodeArr = explode("-", $projCode);
		
		if (Count($projCodeArr) < 3) {
			return $projCode;
		}
		
		$prefix = $projCodeArr[0];
		$mid = $projCodeArr[1];
		$last = $projCodeArr[2];
		
		if (!isXXCode($this->companyCode, $mid)) {
			return $projCode;
		}
		
		return getXXCode($this->companyCode) . "-" . $mid . "-" . $last;
	}
	
	function convertDateFormat($date) {
		$result = array(
			'original'       => $date,
			'yyyymmdd'       => '',
			'yyyy_mm_dd'     => '',
			'year'           => '',
			'year_month'     => '',
			'day'            => '',
			'valid'          => false
		);
		
		if (!$date) {
			return $result;
		}
		
		$base = str_replace('-', '', $date);
		
		if (!preg_match('/^\d{8}$/', $base)) {
			return $result;
		}
		
		$year  = substr($base, 0, 4);
		$month = substr($base, 4, 2);
		$day   = substr($base, 6, 2);
		
		if (!checkdate((int)$month, (int)$day, (int)$year)) {
			return $result;
		}
		
		$yyyymmdd   = $base;
		$yyyy_mm_dd = $year . '-' . $month . '-' . $day;
		
		$result['yyyymmdd']   = $yyyymmdd;
		$result['yyyy_mm_dd'] = $yyyy_mm_dd;
		$result['year']       = $year;
		$result['year_month'] = $year . $month;
		$result['day']        = $day;
		$result['valid']      = true;
		
		return $result;
	}
	
	function writeLog($basePath, $message, $fileName = null) {
		// 1. 허용 루트
		$allowedRoot = 'D:/APM_Setup/htdocs/intranet/sys/log';
		
		// 2. 경로 통일 (윈도우 대응)
		$basePath = str_replace('\\', '/', $basePath);
		$allowedRoot = str_replace('\\', '/', $allowedRoot);
		
		$basePath = rtrim($basePath, '/');
		$allowedRoot = rtrim($allowedRoot, '/');
		
		// 3. realpath 처리
		$allowedRootReal = realpath($allowedRoot);
		$targetPath = realpath($basePath);
		
		// 존재하지 않는 경우 부모 기준
		if ($targetPath === false) {
			$parent = realpath(dirname($basePath));
			if ($parent === false) {
				return false;
			}
			$targetPath = $parent . '/' . basename($basePath);
		}
		
		// 4. 경로 통일 
		$targetPath = str_replace('\\', '/', $targetPath);
		$allowedRootReal = str_replace('\\', '/', $allowedRootReal);
		
		if (strpos($targetPath, $allowedRootReal) !== 0) {
			return false; 
		}
		
		// 6. 날짜 폴더 생성
		$year  = date('Y');
		$month = date('m');
		$date  = date('Y-m-d');
		$time  = date('H:i:s');
		
		$dirPath = $basePath . '/' . $year . '/' . $month;
		if (!is_dir($dirPath)) {
			mkdir($dirPath, 0777, true);
		}
		
		// 7. 파일 생성
		$filePath;
		if (isset($fileName)) {
			$filePath = $dirPath . '/' . $fileName . '_log.txt';
		} else {
			$filePath = $dirPath . '/' . $date . '_log.txt';
		}
		
		$logMessage = "[" . "$date $time" . "] " . $message . "\n";
		file_put_contents($filePath, $logMessage, FILE_APPEND);
		
		return true;
	}
?>