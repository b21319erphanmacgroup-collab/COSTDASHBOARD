<?php
	/* ***********************************
	* 로그인 관련기능
	* 2014-12-   :
	* 2014-12-16 : 파일정리: SUK 
	*************************************** */
	require('../SmartyConfig.php');	
	require_once($SmartyClassPath);
?>   
<?PHP
/* **********************************
* 로그인 페지지 접근하기 전,
* systemconfig_tbl 에 접근, IP를 매칭하여 사원정보 조회 
************************************ */
?>
<?php
	/* ----------------------------------- */
	$MemberNo;	//사원번호
	$korName;	//한글이름
	$RankCode;	//직급코드
	$GroupCode;	//부서코드
	$SortKey;	//직급+부서코드
	$EntryDate;	//입사일자
	$position;	//직위명
	$GroupName;	//부서명
	/* ----------------------------------- */
	$date_today  = date("Y-m-d");				// 오늘날짜 년월일      : yyyy-mm-dd
	$date_today1 = date("Y-m-d H:i");			// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
	$date_today2 = $date_today1.":"."00";		// 오늘날짜
	$date_today3 = $date_today." 00:00:00";
	$nowYear     = date("Y");					// 오늘날짜 년          : yyyy
	$nowMonth    = date("Y-m");					// 오늘날짜 년월        : yyyy-mm
	$todayName   = date("w",strtotime($date_today)); //오늘의 요일(일:0, 월:1,화:2,수:3,목:4,금:5,토:6)
	/* ----------------------------------- */
	$MonthAgo1    = date("Y-m",strtotime("-1 months"));	
	$MonthAgo2    = date("Y-m",strtotime("-2 months"));
	/* ----------------------------------- */
	$MonthNext1    = date("Y-m",strtotime("-1 months"));	
	/* ----------------------------------- */
	$nowHour	 = date("H");					// 현재 시
	$nowMin		 = date("i");					// 현재 분
	$nowTime	 = $nowHour.":".$nowMin;		// 현재 시:분
	/* ----------------------------------- */
	$MemberPic;
	/* ----------------------------------- */
	//============================================================================
	// MAC주소,IP로 사번가져와서 사진,이름부서 표시하기
	//============================================================================
	$ck_id = $_SESSION[CKid];
	/* ----------------------------------- */
	$user_ip   = $HTTP_SERVER_VARS["REMOTE_ADDR"];   // 접근 ip 저장
	$user_ip_length = strlen(trim($user_ip));
		if($user_ip_length>0){
			$ipKind = substr($user_ip, $user_ip_length-2, 2); //접속자IP 마지막 2자리 확인($ipKind != ".1")
		}//if
	/* ----------------------------------- */
	$contact_area = "IN";
	/* ----------------------------------- */

	if( substr($user_ip,0,6) <> "172.16" ){
		$contact_area = "OUT";
	}//if End

	/* ***************************************************************************************************** */
	if( $contact_area =="IN"  && $ipKind != ".1") {						//회사내부에서 인트라넷 접속(ip: 192.168.~.~)  &&  접속자IP 마지막 2자리 확인($ipKind != ".1")
		$sql = "select * from intranet_tbl where UseIP = '".$user_ip."'  order by MemberNo asc ,ToDay desc";
		/* ***************************************************************************************************** */
		$re     = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);

			/* ----------------------------------- */
			if($re_num != 0){	
				$MemberNo = mysql_result($re,0,"MemberNo"); 
					//============================================================================
					// 이름,부서,직급
					//============================================================================
					$sql =      " select a.korName as Name,b.Name as GroupName,a.Name as Position		 ";
					$sql = $sql." from                                                                   ";
					$sql = $sql."	(                                                                    ";
					$sql = $sql."		select * from                                                    ";
					$sql = $sql."		(                                                                ";
					$sql = $sql."			select * from member_tbl where MemberNo = '".$MemberNo."'    ";
					$sql = $sql."		)a1 left JOIN                                                    ";
					$sql = $sql."		(                                                                ";
					$sql = $sql."			select * from systemconfig_tbl where SysKey='PositionCode'   ";
					$sql = $sql."		)a2 on a1.RankCode = a2.code                                     ";
					$sql = $sql."	                                                                     ";
					$sql = $sql."	) a left JOIN                                                        ";
					$sql = $sql."	(                                                                    ";
					$sql = $sql."		select * from systemconfig_tbl where SysKey='GroupCode'          ";
					$sql = $sql."	)b on a.GroupCode = b.code											 ";
					/* ----------------------------------- */
					$re = mysql_query($sql,$db);
					$re_num = mysql_num_rows($re);
					/* ----------------------------------- */
					if($re_num != 0){	
						$Name      = mysql_result($re,0,"Name");
						$GroupName = mysql_result($re,0,"GroupName");
						$Position  = mysql_result($re,0,"Position");
					}//if End
					//============================================================================
					// 사진
					//============================================================================
					$src_photo  = "../erpphoto/".$MemberNo.".jpg";
					$src_photo1 = "../erpphoto/".$MemberNo.".gif";

					if(file_exists($src_photo)) {
						$MemberPic=$src_photo;
					}else if(file_exists($src_photo1)){ 
						$MemberPic=$src_photo2;
					}else{
						$MemberPic="../erpphoto/noimage.gif";
					} //if End
			}else{
				$MemberPic="../erpphoto/noimage.gif";

			}//if End

	} else {

		$MemberPic="../erpphoto/noimage.gif";

	}//if End


?>
<?php
class LoginLogic extends Smarty {
	// 생성자
	function LoginLogic()
	{ 
		global $SmartyClass_TemplateDir;
		global $SmartyClass_CompileDir;
		global $SmartyClass_ConfigDir;
		global $SmartyClass_CacheDir;

		$this->Smarty();

		$this->template_dir		=$SmartyClass_TemplateDir;
		$this->compile_dir		=$SmartyClass_CompileDir;
		$this->config_dir		=$SmartyClass_ConfigDir;	
		$this->cache_dir		=$SmartyClass_CacheDir;
	}//Main End

	/* ********************************************************** */
	function GoLogin()
	{
		extract($_REQUEST);
		/* -------------- */
		global $MemberNo;    //사원번호       
		global $user_ip;	 //접속자IP       
		global $Name;		 //성명           
		global $GroupName;	 //부서명         
		global $Position;	 //직위           
		global $MemberPic;	 //프로필사진경로 
		/* -------------- */
		global $connectFlag;	//웹브라우저를 통한 일반사용자의 접근(connectFlag=web)
		/* -------------- */
		global $contact_area; //접속위치 (회사내부:IN,회사외부:OUT)
		/* -------------- */
		$storeId    = $_SESSION['CK_MemberNo'];
		/* ----------------------------------- */
		$this->assign('connectFlag',$connectFlag);
		/* ----------------------------------- */
		$logoutFlag	=	$_GET['logoutFlag'];
		$this->assign('logoutFlag',$logoutFlag);
		/* ----------------------------------- */
		$this->assign('storeId',$storeId);
		/* ----------------------------------- */
		$this->assign('contact_area',$contact_area);
		/* ----------------------------------- */
		$this->assign('L_MemberNo',$MemberNo);		//사원번호
		$this->assign('L_userIp',$user_ip);			//접속자IP
 		$this->assign('L_Name',$Name);				//성명
		$this->assign('L_GroupName',$GroupName);	//부서명
		$this->assign('L_Position',$Position);		//직위
		$this->assign('L_MemberPic',$MemberPic);	//프로필사진경로
		/* ----------------------------------- */

		//==============================================
		//자동로그인 체크 START 191119
		//==============================================
		//$autologin = base64_decode($autologin);
		$this->assign('autologin',$autologin);	//자동로그인 체크 
		
		if($autologin == 'USE'){
			//$userid = base64_decode($userid);
			//$userpassword = base64_decode($userpassword);
			$this->assign('userid',$userid);	//사번
			$this->assign('userpassword',$userpassword);	//비번
		}
		//==============================================
		//자동로그인 체크 END
		//==============================================
		
		$this->display("intranet/common_layout/login.tpl");
		//$this->display("intranet/common_layout/left.tpl");
		/* ----------------------------------- */
	}//Process End
	/* ********************************************************** */

/* ------------------------------------------------------------------------------ */
}//class Main End
/* ------------------------------------------------------------------------------ */
?>
