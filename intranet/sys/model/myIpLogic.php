<?php
	/* **********************************
	* 메인화면 : LEFT : 개인정보 : IP입력창
	
	* ------------------------------------
	require('../../../SmartyConfig.php');
	require('../inc/function_intranet.php');
	/* ----------------------------------- */
	//include "../inc/getCookieOfUser.php";  //사용자에 관한 쿠키값
	include "../inc/getNeedDate.php";      //로직에 사용되는 PHP시간&날짜 정의
	/* ----------------------------------- */
	require_once($SmartyClassPath);
	/* ----------------------------------- */
?>
<?php
	extract($_GET);
	$MemberNo	=	"";	//사원번호
	if($_SESSION['SS_memberID']!=""){
		/* SET SESSION ----------------------- */
		$MemberNo   =   $_SESSION['SS_memberID'];		//사원번호
		$memberID	=   $_SESSION['SS_memberID'];		//사원번호

	}else if($_COOKIE['CK_memberID']!=""){				//쿠키값 유무확인
		/* SET COOKIE --------------------------------- */
		$MemberNo   =   $_COOKIE['CK_memberID'];	//사원번호
		$memberID	=   $_COOKIE['CK_memberID'];	//사원번호

	}else{
		/* ----------------------------------- */
		$memberID	=	$_GET['memberID'];
		$MemberNo	=	$memberID;
		$MemberNo	=	strtoupper($MemberNo);
		/* ----------------------------------- */
	}//if End
?>
<?
	class MyIpLogic extends Smarty {
		function MyIpLogic(){
			global $SmartyClass_TemplateDir;
			global $SmartyClass_CompileDir;
			global $SmartyClass_ConfigDir;
			global $SmartyClass_CacheDir;

			$this->Smarty();

			$this->template_dir		=$SmartyClass_TemplateDir;
			$this->compile_dir		=$SmartyClass_CompileDir;
			$this->config_dir		=$SmartyClass_ConfigDir;
			$this->cache_dir		=$SmartyClass_CacheDir;


		}
		//==================================================================//
		//Page data load function
		function Ipinsert_page(){
			global  $db;
			global	$MemberNo;
			// global	$date_today1;

			$Member_Ip = $_SERVER['REMOTE_ADDR'];

			//M.ManageIP = 접속자 IP 
			$sql01 = "SELECT M.MemberNo as m_MemberNo,M.ManageIP as m_ManageIP FROM member_tbl M	WHERE MemberNo='$MemberNo'";

			$result_member01 = mysql_query($sql01,$db);
			$re_row_member01 = mysql_num_rows($result_member01);

			if($re_row_member01 != 0){
				$m_MemberNo = @mysql_result($result_member01,0,"m_MemberNo");
				$m_ManageIP = @mysql_result($result_member01,0,"m_ManageIP");
			}
			// $WP_Status = $this->user_status_check($MemberNo);
			$this->assign('WP_Status' ,$WP_Status);
			$this->assign('m_MemberNo' ,$m_MemberNo);
			$this->assign('m_ManageIP' ,$m_ManageIP); //
			$this->assign('Chg_Member_Ip' ,$Member_Ip);//접속자 IP 정보
			//print_r($m_office_type);
			//==========================================================================//
			//변경IP등록정보 LOAD

			$sql02 = "SELECT M.ChangeDate as m_date_today, M.OriginIP as m_OriginIP, M.ChangeIP as m_ChangeIP, reason FROM changeiprecord_tbl M WHERE MemberNo='$MemberNo' ORDER BY 1 DESC Limit 5";
			
			$member_data02= array();
			$result_member02 = mysql_query($sql02,$db);
			$cnt02 = mysql_num_rows($result_member02);
			$this->assign('cnt02' ,$cnt02);

			while($re_row_member02 = mysql_fetch_array($result_member02)){
			array_push($member_data02,$re_row_member02);
			}

			$this->assign('member_data02',$member_data02);

			$this->assign('m_date_today' ,$m_date_today);
			$this->assign('m_OriginIP' ,$m_OriginIP); //
			$this->assign('m_ChangeIP' ,$m_ChangeIP); // 근무지 확인(합사유무)

			$this->display("intranet/common_contents/work_myInfo/myIpInfo.tpl");
		}

		//==================================================================//
		//IP Change function
		function InsertAction(){
			extract($_POST);
			global  $db;
			global	$MemberNo;
			global	$date_today4;

			$m_MemberNo		 	=	($_POST['m_memberID']==""?"":$_POST['m_memberID']);
			$m_ManageIP		 	=	($_POST['m_ManageIP']==""?"":$_POST['m_ManageIP']);
			$Chg_Member_Ip	 	=	($_POST['ChgMemberIp']==""?"":$_POST['ChgMemberIp']);
			$m_date_today	 	=	$date_today4;

			$sql01 = "UPDATE member_tbl SET ManageIP = '$Chg_Member_Ip' WHERE MemberNo = '$m_MemberNo'";
			//print_r($sql01);
			$result01 = mysql_query($sql01,$db);

			echo $result01;

			$sql02 = "INSERT INTO changeiprecord_tbl(MemberNo, ChangeDate, OriginIP, ChangeIP, Reason)";
			$sql02 = $sql02."VALUES ('$m_MemberNo', '$m_date_today', '$m_ManageIP', '$Chg_Member_Ip', '$reason')";

			$result02 = mysql_query($sql02,$db);

		}

		function ip_check(){
			extract($_REQUEST);
			global  $db;
			// $sql02 = "SELECT ManageIP FROM member_tbl WHERE MemberNo='$empno'";
			$sql02 = "SELECT ManageIP FROM member_tbl WHERE MemberNo='$empno'";
//echo $sql02;
			$result_member01 = mysql_query($sql02,$db);
			$ManageIP = @mysql_result($result_member01,0,"ManageIP");
			
			if($ManageIP != $_SERVER['REMOTE_ADDR']){
                
                // 한맥빌딩 네트워트 변동 이슈로 242, 243 IP 예외 체크 24.05.10 서승완
                // 61.98.205.242, 61.98.205.243
                if($_SERVER['REMOTE_ADDR'] == "61.98.205.242" && $ManageIP == "61.98.205.243"){
                    exit();
                } 
                if($_SERVER['REMOTE_ADDR'] == "61.98.205.243" && $ManageIP == "61.98.205.242"){
                    exit();
                }
				echo $_SERVER['REMOTE_ADDR'];
			}
            
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

	}
?>
