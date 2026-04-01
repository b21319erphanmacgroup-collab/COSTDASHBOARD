<?php
	class SessionAuth {
		private $sessionKey = 'CBD_INTRA_USER_ID';
		private $timeout = 36000; 
		
		public function __construct() {
		}
		
		// 로그인 세션 생성
		public function login($userInfo) {
			if (empty($userInfo["INTRANET"]) || empty($userInfo["SATIS"])) {
				return false;
			}
			
			$_SESSION[$this->sessionKey] = $userInfo["INTRANET"]["MemberNo"];
			
			$_SESSION['CBD_LOGIN_IP'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['CBD_LOGIN_TIME'] = time();
			
			// intranet, satis
			$_SESSION['CBD_INTRA_COMPANY_ID'] = $userInfo["INTRANET"]["INTRANET_COMPANY_CODE"];
			$_SESSION['CBD_INTRA_USER_NAME'] = $userInfo["INTRANET"]["korName"];
			$_SESSION['CBD_INTRA_RANK_CODE'] = $userInfo["INTRANET"]["RankCode"];
			$_SESSION['CBD_INTRA_RANK_NAME'] = $userInfo["INTRANET"]["INTRANET_RANK_NAME"];
			$_SESSION['CBD_INTRA_GROUP_CODE'] = $userInfo["INTRANET"]["GroupCode"];
			$_SESSION['CBD_INTRA_GROUP_NAME'] = $userInfo["INTRANET"]["INTRANET_GROUP_NAME"];
			$_SESSION['CBD_INTRA_ENTRY_DATE'] = $userInfo["INTRANET"]["EntryDate"];
			$_SESSION['CBD_INTRA_LEAVE_DATE'] = $userInfo["INTRANET"]["LeaveDate"];
			$_SESSION['CBD_SATIS_TEAM_CODE'] = $userInfo["SATIS"]["TEAM_CODE"];
			$_SESSION['CBD_SATIS_TEAM_NAME'] = $userInfo["SATIS"]["TEAM_NAME"];
			
			return true;
		}
		
		// 세션 체크
		public function check() {
			if (empty($_SESSION[$this->sessionKey])) {
				return false;
			}
			
			// 세션 타임아웃 체크
			if ((time() - $_SESSION['CBD_LOGIN_TIME']) > $this->timeout) {
				$this->logout();
				return false;
			}
			
			// 활동 시간 갱신
			$_SESSION['CBD_LOGIN_TIME'] = time();
			
			return true;
			
		}
		
		// 로그인 필요 페이지
		public function requireLogin($redirect = '/intranet/sys/controller/costDashBoard_controller.php?ActionMode=login') {
			if (!$this->check()) {
				header("Location: ".$redirect);
				exit;
			}
			
		}
		
		// 사용자 정보 가져오기
		public function getUser() {
			
			if($this->check()){
				return $_SESSION[$this->sessionKey];
			}
			
			return null;
			
		}
		
		// 권한 체크
		public function requireRole($role, $redirect='/noauth.php') {
			
			if(!$this->check()){
				header("Location: /login.php");
				exit;
			}
			
			$user = $_SESSION[$this->sessionKey];
			
			if($user['ROLE'] != $role){
				header("Location: ".$redirect);
				exit;
			}
			
		}
		
		// 로그아웃
		public function logout($redirect='/intranet/sys/controller/costDashBoard_controller.php?ActionMode=login') {
			if (session_id() != "") {
				session_destroy();
			}
			
			header("Location: ".$redirect);
			exit;
		}
		
	}
?>