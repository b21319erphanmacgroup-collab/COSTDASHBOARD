<?php
	/* ----------------------------------- */
	require('../../../SmartyConfig.php');
	require_once($SmartyClassPath);
	
	/* ----------------------------------- */
	require('../inc/function_intranet.php');//자주쓰는 기능 Function
	/* ----------------------------------- */
	//include "../inc/getCookieOfUser.php";  //사용자에 관한 쿠키값
	include "../inc/getNeedDate.php";      //로직에 사용되는 PHP시간&날짜 정의
	/* ----------------------------------- */

?>
<?php
	extract($_GET);
?>
<?php
class LunchMenuPopLogic extends Smarty {
	// 생성자
	function LunchMenuPopLogic()
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
	/* ****************************************************************** */




	/* ******************************************************************************************* */
	function lunchMini()		//메인화면 오늘의 점심식단
	{
		/* -----------------*/
		global	$MemberNo;		// 사원번호
		global	$korName;		// 한글이름
		global	$GroupCode;		// 부서
		global	$RankCode;		// 직급
		global	$SortKey;		// 직급+부서
		/* ----------------- */
		global	$date_today;	// 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1;	// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$nowYear;		// 오늘날짜 년          : yyyy 
		global	$nowMonth;		// 오늘날짜 년월        : yyyy-mm
		global	$nowHour;		// 현재 시
		global	$nowMin;		// 현재 분
		global	$nowTime;		// 현재 시:분
		global	$todayName;		//오늘의 요일(일:0, 월:1,화:2,수:3,목:4,금:5,토:6)
		/* ----------------- */
		global $db;
		/* ----------------- */
		$lunch_menu_main_short;
		$lunch_menu_sub_short;
		/* ----------------- */
		// 점심식단 Start *********************************************************** */
		$sql= "SELECT										";
		$sql= $sql."  L.menu_num   as lunch_menu_num		";		// num
		$sql= $sql." ,L.menu_day   as lunch_menu_day 		";		// 요일
		$sql= $sql." ,L.menu_main  as lunch_menu_main		";		// 메인메뉴
		$sql= $sql." ,L.menu_sub   as lunch_menu_sub 		";		// 서브메뉴
		$sql= $sql." ,L.menu_add   as lunch_menu_add 		";		// 기타추가 항목 필요시
		$sql= $sql." FROM									";
		$sql= $sql."      lunch_menu_tbl L					";
		$sql= $sql." WHERE									";
		$sql= $sql."	L.menu_num = '".$todayName."'		";
		/* ----------------------------- */
		/* 장헌인트라넷*/
		//$db_hostname01 ='192.168.2.250';
		//$db_hostname01 ='1.233.130.26';
		$db_hostname01 ='211.206.127.71';
		$db_database01 ='hanmacerp';
		$db_username01 ='root';
		$db_password01 ='erp';
		/*-----------------------------------------------------------------------*/
		$db01	= mysql_connect($db_hostname01,$db_username01,$db_password01);
			//if(!$db01) die ("Unable to connect to MySql : 장헌인트라넷 DB장애".mysql_error()); 

		if($db01){
			$lunch_serverYN = "Y";
			/*-----------------------------------------------------------------------*/
			mysql_select_db($db_database01);
			/*-----------------------------------------------------------------------*/
			mysql_set_charset("utf-8",$db01);
			mysql_query("set names utf8");
			/*-----------------------------------------------------------------------*/
			$re = mysql_query($sql,$db01);
			$re_num = mysql_num_rows($re);

			/* ----------------------------- */
			if($re_num>0){
				/* ----------------- */
				$lunch_menu_num  = mysql_result($re,0,"lunch_menu_num");
				$lunch_menu_main = mysql_result($re,0,"lunch_menu_main");
				$lunch_menu_sub	 = mysql_result($re,0,"lunch_menu_sub");

				/* ----------------- */
				$len01 = mb_strlen($lunch_menu_main,"UTF-8");
				if($len01>35){
					$lunch_menu_main_short = mb_substr($lunch_menu_main,0,35,"UTF-8");
					$lunch_menu_main_short = $lunch_menu_main_short."..";
					$lunch_menu_main_short =  str_replace("\n","<br>", $lunch_menu_main_short);

				}else{
					$lunch_menu_main_short = $lunch_menu_main;
					$lunch_menu_main_short =  str_replace("\n","<br>", $lunch_menu_main_short);
				}
				/* ----------------- */
				$len02 = mb_strlen($lunch_menu_sub,"UTF-8");
				if($len02>35){
					$lunch_menu_sub_short = mb_substr($lunch_menu_sub,0,35,"UTF-8");
					$lunch_menu_sub_short = $lunch_menu_sub_short."..";
					$lunch_menu_sub_short =  str_replace("\n","<br>", $lunch_menu_sub_short);
				}else{
					$lunch_menu_sub_short = $lunch_menu_sub;
					$lunch_menu_sub_short =  str_replace("\n","<br>", $lunch_menu_sub_short);
				}
				/* ----------------- */
			}else{
				$lunch_menu_main = "금주 등록된 메뉴없음";
				$lunch_menu_sub	 = "(미등록상태)";
			} //if End
			//////////////
			mysql_close();
			//////////////		

	}else{
		$lunch_serverYN = "N";
		$lunch_menu_main = "식단관련 DB서버 확인필요";
		$lunch_menu_sub	 = "관리자에게 확인";
	}

		/* ----------------------------- */
		$this->assign('lunch_serverYN',$lunch_serverYN);					// 식단DB 관련 서버 동작유무(Y=정상동작, N=미동작)
		$this->assign('lunch_menu_num',$lunch_menu_num);					// 메인메뉴
		$this->assign('lunch_menu_main',$lunch_menu_main);					// 메인메뉴
		$this->assign('lunch_menu_sub',$lunch_menu_sub);					// 서브메뉴
		$this->assign('lunch_menu_main_short',$lunch_menu_main_short);		// 메인메뉴(35자)
		$this->assign('lunch_menu_sub_short',$lunch_menu_sub_short);		// 서브메뉴(35자)
		/* ----------------------------- */
	} //lunchMini End
	/* 점심식단 End----------------------------------------------------------------------- */
	/* ******************************************************************************************* */



	function lunchPop() 
	{
		global $memberID;
/*
		global $WorkPosition;
		$this->assign('WorkPosition',$WorkPosition);
*/

		$this->lunchMini();				//점심식단


		$this->display("intranet/common_layout/lunchPop.tpl");
	}//MainHomeProcess22222 End



}//class Main End/* ******************************************************************************************* */
?>
<?php
	/*점검용
	$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
	$ipStr  = "192.168.2.62";
	$myCompany   = searchCompanyKind();
	if($myip==$ipStr){
		echo $myip."===".$myCompany."===<BR>";
	}//if End
	*/
?>

