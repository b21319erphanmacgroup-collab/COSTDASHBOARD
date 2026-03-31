<?php
	/* **********************************
	* 메인화면 : LEFT : 개인정보
	* 개인정보 상세
	* ------------------------------------
	* 2014-12-   :
	* 2014-12-18 : 세션값을 쿠키값으로 대체(/sys/inc/getCookieOfUser.php : 파일생성) : SUK
	* 2014-12-18 : php.날짜변수 관련 공통 페이지 삽입(/sys/inc/getNeedDate.php : 파일생성) : SUK
	* 2014-12-16 : 파일정리: SUK
	*************************************** */
	/* ----------------------------------- */
	require('../../../SmartyConfig.php');
	require_once($SmartyClassPath);
	/* ----------------------------------- */
	require('../inc/function_intranet.php');//자주쓰는 기능 Function
	include "../../../person_mng/inc/vacationfunction.php";
	include "../../../person_mng/inc/vacationfunction_v3.php";
	/* ----------------------------------- */
	//include "../inc/getCookieOfUser.php";  //사용자에 관한 쿠키값
	include "../inc/getNeedDate.php";      //로직에 사용되는 PHP시간&날짜 정의
	include "../util/OracleClass.php";
	/* ----------------------------------- */
?>
<?php
	extract($_GET);
		$MemberNo	=	"";	//사원번호
	if($_SESSION['SS_memberID']!=""){
		/* SET SESSION ----------------------- */
		$MemberNo   =   $_SESSION['SS_memberID'];		//사원번호
		$memberID	=   $_SESSION['SS_memberID'];		//사원번호

		$CompanyKind=   $_SESSION['SS_CompanyKind'];//장헌산업(JANG),파일테크(PILE),바론컨설턴트(HANM)
		$korName	=	$_SESSION['SS_korName'];	//한글이름
		$RankCode	=	$_SESSION['SS_RankCode'];	//직급코드
		$GroupCode	=	$_SESSION['SS_GroupCode'];	//부서코드
		$SortKey	=	$_SESSION['SS_SortKey'];	//직급+부서코드
		$EntryDate	=	$_SESSION['SS_EntryDate'];	//입사일자
		$position	=	$_SESSION['SS_position'];	//직위명
		$GroupName	=	$_SESSION['SS_GroupName'];	//부서명
	}else if($_SESSION['CK_memberID']!=""){				//쿠키값 유무확인
		//쿠키정보 세션으로 대체 250626 김진선
		/* SET COOKIE --------------------------------- */
		$MemberNo   =   $_SESSION['CK_memberID'];	//사원번호
		$memberID	=   $_SESSION['CK_memberID'];	//사원번호

		$CompanyKind=	$_SESSION['CK_CompanyKind'];	//회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		$korName	=	$_SESSION['CK_korName'];		//한글이름
		$RankCode	=	$_SESSION['CK_RankCode'];	//직급코드
		$GroupCode	=	$_SESSION['CK_GroupCode'];	//부서코드
		$SortKey	=	$_SESSION['CK_SortKey'];		//직급+부서코드
		$EntryDate	=	$_SESSION['CK_EntryDate'];	//입사일자
		$position	=	$_SESSION['CK_position'];	//직위명
		$GroupName	=	$_SESSION['CK_GroupName'];	//부서명
	}else{
		/* ----------------------------------- */
		$memberID	=	$_GET['memberID'];
		$MemberNo	=	$memberID;
		$MemberNo	=	strtoupper($MemberNo);
		/* ----------------------------------- */
		require('../popup/setInfo.php');
		/* ----------------------------------- */
	}//if End
	/* ----------------------------------- */
	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
	/* ----------------------------------- */
	$WorkPosition = getWorkPositionByMemberNo($memberID); //워크포지션(WorkPosition)
?>
<?php
class MyInfoLogic extends Smarty {
	// 생성자
	function MyInfoLogic()
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
	/* ------------------------------------------------------------------------------ */

	/* 기본정보 *********************************************************************************************** */
	function DefaultInfo()
	{
		global	$CompanyKind;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		/*----------------------------------------*/
		global $MemberNo;			//사원번호
		global $korName;			//한글이름
		global $RankCode;			//직급코드
		global $GroupCode;			//부서코드
		global $SortKey;			//직급+부서코드
		global $EntryDate;			//입사일자
		//global $member_EntryDate;	//입사일자 //20141222수정:20141226부터 삭제가능
		/*----------------------------------------*/
		global $position;			//직위명
		global $GroupName;			//부서명
		/* ----------------------------------- */
		global $date_today;			// 오늘날짜 년월일      : yyyy-mm-dd
		global $date_today1;		// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global $date_today2;		// 오늘날짜 :$date_today1.":"."00"
		global $date_today3;		// 오늘날짜 :$date_today." 00:00:00"
		global $nowYear;			// 오늘날짜 년          : yyyy
		global $nowMonth;			// 오늘날짜 년월        : yyyy-mm
		global $todayName;			//오늘의 요일(일:0, 월:1,화:2,수:3,목:4,금:5,토:6)
		global $day_count;			//월의 마지막 날짜
		/* ----------------------------------- */
		global $MonthAgo1;			//한달전 yyyy-mm
		global $MonthAgo2;			//두달전 yyyy-mm
		/* ----------------------------------- */
		global $nowHour;			// 현재 시
		global $nowMin;				// 현재 분
		global $nowTime;			// 현재 시:분
		/*----------------------------------------*/
		global $db;
		/*----------------------------------------*/
		$Pasword;
		$MemberPic;
		/*----------------------------------------*/
		$CPU;
		$RAM;
		$HDD;
		/*----------------------------------------*/
		$ip_addr;
		$sub_addr;
		$gw_addr;
		$dns_addr;
		/*----------------------------------------*/
		$rest_day;		//전년이월
		$new_day;		//생성연차
		$sum_day;		//계(전월+생성)
		/*----------------------------------------*/
		$absence_num;	//결근
		$spend_day;		//사용휴가
		$remain;		//잔여휴가(연차계-사용휴가)
		/*----------------------------------------*/
		//============================================================================
		// 내정보 PASSWORD
		//============================================================================

		//$todayYears = substr($nowMonth,0,4);
		//$todayMonth = substr($nowMonth,5,2);
		//$this->assign('todayYear',$todayYear);

		$sql= "SELECT									";
		$sql= $sql." Pasword as Pasword,                ";
		$sql= $sql." vacation,                          ";
		$sql= $sql." birthday,                          ";
		$sql= $sql." birthdayType,                      ";
		$sql= $sql." birthdayViewYn,                    ";
		$sql= $sql." postcode,                          ";
		$sql= $sql." Address,                           ";
		$sql= $sql." Mobile,                            ";
		$sql= $sql." ExtNo                              ";
		$sql= $sql." FROM                               ";
		$sql= $sql." member_tbl                         ";
		$sql= $sql." WHERE MemberNo = '".$MemberNo."'   ";

		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		if($re_num != 0)
		{
			$Pasword   = mysql_result($re,0,"Pasword");
			//$vacation   = mysql_result($re,0,"vacation"); // 별도 분리
			$birthday	= mysql_result($re, 0, "birthday");
			$birthdayType	= mysql_result($re, 0, "birthdayType");
			$birthdayViewYn	= mysql_result($re, 0, "birthdayViewYn");
			$postcode	= mysql_result($re, 0, "postcode");
			$addr	= mysql_result($re, 0, "Address");
			$Mobile	= mysql_result($re, 0, "Mobile");
			$ExtNo	= mysql_result($re, 0, "ExtNo");

			$addr = explode("   ",$addr);
			$Address = $addr[0];
			$Address2 = $addr[1]? $addr[1] : '';
			$ExtNo  = strlen($ExtNo)=='3'? '02-2141-7'.$ExtNo : '';
		} //if End

		//============================================================================
		// 연차정보
		//============================================================================
		$sql2= "select * from vacation_set WHERE MemberNo = '".$MemberNo."' and year = '".$nowYear."' ";
		//echo $sql2;
		$re2 = mysql_query($sql2,$db);
		$re_num2 = mysql_num_rows($re2);
		if($re_num2 != 0)
		{
			$vacation   = mysql_result($re2,0,"vacationplus");
		} //if End

		//============================================================================
		// 사진
		//============================================================================
		$src_photo = "../../../erpphoto/".$MemberNo.".jpg";
		$src_photo1 = "../../../erpphoto/".$MemberNo.".gif";
		if(file_exists($src_photo)) {
			$MemberPic=$src_photo;
		}else if(file_exists($src_photo1))
		{
			$MemberPic=$src_photo2;
		}
		else
		{
			$MemberPic="../../../erpphoto/noimage.gif";
		}
		//============================================================================
		// MY Hardware
		//============================================================================
		$sql = "select * from hardwarelist_tbl where memberno = '".$MemberNo."'";
		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		if($re_num > 0)
		{
			$re_row = mysql_fetch_array($re);
			$CPU=$re_row[CPU];
			$RAM=$re_row[RAM]." M";
			$HDD=$re_row[HDD]." G";
		}else
		{
			$CPU="정보없음";
			$RAM="정보없음";
			$HDD="정보없음";
		}
		//============================================================================
		// MY Network
		//============================================================================
		$sql = "select * from intranet_tbl where memberno = '".$MemberNo."'";
		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		if($re_num > 0)
		{
			$re_row = mysql_fetch_array($re);
			$ip_addr = $re_row[UseIP];
			$sub_addr = "255.255.255.0";
			$gw_addr_ex = explode(".",$ip_addr);
			$gw_addr = $gw_addr_ex[0].".".$gw_addr_ex[1].".".$gw_addr_ex[2].".1";
			$dns_addr = "203.248.252.2";
		}else
		{
			$ip_addr = "&nbsp;";
			$sub_addr = "&nbsp;";
			$gw_addr = "&nbsp;";
			$dns_addr = "&nbsp;";
		}
		//============================================================================
		// MY Vacation 전년이월
		//============================================================================
		$sql = "select * from diligence_tbl where MemberNo = '".$MemberNo."' and date like '%".$nowYear."%'";
		//echo $sql."<br>";
		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		if($re_num > 0)
		{
			$rest_day = mysql_result($re,0,"rest_day");

			if($rest_day > 0)  //전년월차 남아있으면 모두 0으로처리
			{
				$rest_day=0;
			}

			$rest_day = $rest_day + (double)mysql_result($re,0,"spend_day");
		}
		else
		{
			$rest_day = "&nbsp;";
		}

		//============================================================================
		// MY Vacation 연차생성  -임원은 표시안함   //J07202(김회성),T02303(신현우) +1
		//============================================================================
		$EnterYear = substr($EntryDate,0,4);  //입사년도
		$EnterMonth = substr($EntryDate,5,2);  //입사월
		$EnterDay = substr($EntryDate,8,2);  //입사일

		if(substr($RankCode,0,1) =="C") //임원(전무,상무,이사)
		{
			$rest_day=0;
			$new_day=0;
			$sum_day=0;
			$employee=false;
		}else
		{
			$employee=true;
			

 
			$VacationInfo = GetVacationInfo($db, date("Y"), $MemberNo );
			
			if($MemberNo=="B22016"){
			    
			   // echo $MemberNo;
			  //print_r($VacationInfo);
			}
			
			//[rest_day] => -2.125 [rest_day_text] => -2일 -1시간 [rest_time] => -17
			
			$rest_day = $VacationInfo['rest_day_text'];		// 전년이월
			$new_day = TimeToText( $VacationInfo['new_time'] + $VacationInfo['plus_time'] ) ;		// 생성
			$sum_day = $VacationInfo['total_day_text'];		// 총계
			$use_day = $VacationInfo['use_day_text'];		// 사용
			$remaind_day = $VacationInfo['left_day_text'];	// 잔여

		}

		//============================================================================
		// MY Vacation 결근표시
		//============================================================================
		$sql = "select * from userstate_tbl where state = 13 and MemberNo = '".$MemberNo."' and start_time like '".$nowYear."%'";
		$re = mysql_query($sql,$db);
		$absence_num = mysql_num_rows($re);

		if($EntryDate != "0000-00-00"){
			$dateDifference = abs(strtotime(date("Y-m-d")) - strtotime($EntryDate));

			$years  = floor($dateDifference / (365 * 60 * 60 * 24));
			$months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
			$days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

			$totalDays = ($years*365)+($months*30)+$days;

			$TenuerPeriod = $years."년 ".$months."개월 ".$days."일"."(".$totalDays."일)";
		}
		else{
			$TenuerPeriod="";
		}
		global $WorkPosition;
		$this->assign('WorkPosition',$WorkPosition);

		//============================================================================
		// WP_status 근무상태 확인
		//============================================================================
		// $WP_Status =  $this->user_status_check($MemberNo);
		// $this->assign('WP_Status', $WP_Status);
		//내정보
		$this->assign('my_GroupName',$GroupName);
		$this->assign('my_Position',$position);
		$this->assign('my_Name',$korName);
		$this->assign('my_MemberNo',$MemberNo);
		$this->assign('my_Mobile', preg_replace("/(\d{3})(\d{4})(\d{4})/", "$1-$2-$3", $Mobile));
		$this->assign('my_ExtNo', $ExtNo);
		$this->assign('my_birthday', $birthday);
		$this->assign('my_birthdayType', $birthdayType);
		$this->assign('my_birthdayViewYn', $birthdayViewYn);
		$this->assign('my_postcode', $postcode);
		$this->assign('my_Address', $Address);
		$this->assign('my_Address2', $Address2);

		//$this->assign('my_EntryDate',$member_EntryDate);  //20141222수정:20141226부터 삭제가능
		$this->assign('my_EntryDate',$EntryDate);
		$this->assign('my_TenuerPeriod',$TenuerPeriod);
		$this->assign('my_Pasword',$Pasword);
		$this->assign('my_MemberPic',$MemberPic);
		//MY Hardware
		$this->assign('my_CPU',$CPU);
		$this->assign('my_RAM',$RAM);
		$this->assign('my_HDD',$HDD);
		$this->assign('my_ip_addr',$ip_addr);
		$this->assign('my_sub_addr',$sub_addr);
		$this->assign('my_gw_addr',$gw_addr);
		$this->assign('my_dns_addr',$dns_addr);
		//MY Vacation
		$this->assign('my_rest_day',$rest_day);
		$this->assign('my_new_day',$new_day);
		$this->assign('my_sum_day',$sum_day);

		$this->assign('rest_day',$rest_day);
		$this->assign('new_day',$new_day);
		$this->assign('sum_day',$sum_day);
		$this->assign('use_day',$use_day);
		$this->assign('remaind_day',$remaind_day);


		$this->assign('my_absence_num',$absence_num);
		$this->assign('my_spend_day',$spend_day);
		$this->assign('my_remain',$remain);

		$todayYear = substr($nowMonth,0,4);
		$todayMonth = substr($nowMonth,5,2);
		$this->assign('todayYear',$todayYear);
		$this->assign('todayMonth',$todayMonth);

		$preMonth = substr($MonthAgo1,5,2);
		$this->assign('preMonth',$preMonth);
		$this->assign('day_count',$day_count);
		$this->assign('employee',$employee);

	}
	function DefaultInfo_230314()
	{
		global	$CompanyKind;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		/*----------------------------------------*/
		global $MemberNo;			//사원번호
		global $korName;			//한글이름
		global $RankCode;			//직급코드
		global $GroupCode;			//부서코드
		global $SortKey;			//직급+부서코드
		global $EntryDate;			//입사일자
		//global $member_EntryDate;	//입사일자 //20141222수정:20141226부터 삭제가능
		/*----------------------------------------*/
		global $position;			//직위명
		global $GroupName;			//부서명
		/* ----------------------------------- */
		global $date_today;			// 오늘날짜 년월일      : yyyy-mm-dd
		global $date_today1;		// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global $date_today2;		// 오늘날짜 :$date_today1.":"."00"
		global $date_today3;		// 오늘날짜 :$date_today." 00:00:00"
		global $nowYear;			// 오늘날짜 년          : yyyy
		global $nowMonth;			// 오늘날짜 년월        : yyyy-mm
		global $todayName;			//오늘의 요일(일:0, 월:1,화:2,수:3,목:4,금:5,토:6)
		global $day_count;			//월의 마지막 날짜
		/* ----------------------------------- */
		global $MonthAgo1;			//한달전 yyyy-mm
		global $MonthAgo2;			//두달전 yyyy-mm
		/* ----------------------------------- */
		global $nowHour;			// 현재 시
		global $nowMin;				// 현재 분
		global $nowTime;			// 현재 시:분
		/*----------------------------------------*/
		global $db;
		/*----------------------------------------*/
		$Pasword;
		$MemberPic;
		/*----------------------------------------*/
		$CPU;
		$RAM;
		$HDD;
		/*----------------------------------------*/
		$ip_addr;
		$sub_addr;
		$gw_addr;
		$dns_addr;
		/*----------------------------------------*/
		$rest_day;		//전년이월
		$new_day;		//생성연차
		$sum_day;		//계(전월+생성)
		/*----------------------------------------*/
		$absence_num;	//결근
		$spend_day;		//사용휴가
		$remain;		//잔여휴가(연차계-사용휴가)
		/*----------------------------------------*/
		//============================================================================
		// 내정보 PASSWORD
		//============================================================================

		//$todayYears = substr($nowMonth,0,4);
		//$todayMonth = substr($nowMonth,5,2);
		//$this->assign('todayYear',$todayYear);

		$sql= "SELECT									";
		$sql= $sql." Pasword as Pasword,                ";
		$sql= $sql." vacation                           ";
		$sql= $sql." FROM                               ";
		$sql= $sql." member_tbl                         ";
		$sql= $sql." WHERE MemberNo = '".$MemberNo."'   ";

		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		if($re_num != 0)
		{
			$Pasword   = mysql_result($re,0,"Pasword");
			//$vacation   = mysql_result($re,0,"vacation"); // 별도 분리
		} //if End

		//============================================================================
		// 연차정보
		//============================================================================
		$sql2= "select * from vacation_set WHERE MemberNo = '".$MemberNo."' and year = '".$nowYear."' ";
		//echo $sql2;
		$re2 = mysql_query($sql2,$db);
		$re_num2 = mysql_num_rows($re2);
		if($re_num2 != 0)
		{
			$vacation   = mysql_result($re2,0,"vacationplus");
		} //if End

		//============================================================================
		// 사진
		//============================================================================
		$src_photo = "../../../erpphoto/".$MemberNo.".jpg";
		$src_photo1 = "../../../erpphoto/".$MemberNo.".gif";
		if(file_exists($src_photo)) {
			$MemberPic=$src_photo;
		}else if(file_exists($src_photo1))
		{
			$MemberPic=$src_photo2;
		}
		else
		{
			$MemberPic="../../../erpphoto/noimage.gif";
		}
		//============================================================================
		// MY Hardware
		//============================================================================
		$sql = "select * from hardwarelist_tbl where memberno = '".$MemberNo."'";
		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		if($re_num > 0)
		{
			$re_row = mysql_fetch_array($re);
			$CPU=$re_row[CPU];
			$RAM=$re_row[RAM]." M";
			$HDD=$re_row[HDD]." G";
		}else
		{
			$CPU="정보없음";
			$RAM="정보없음";
			$HDD="정보없음";
		}
		//============================================================================
		// MY Network
		//============================================================================
		$sql = "select * from intranet_tbl where memberno = '".$MemberNo."'";
		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		if($re_num > 0)
		{
			$re_row = mysql_fetch_array($re);
			$ip_addr = $re_row[UseIP];
			$sub_addr = "255.255.255.0";
			$gw_addr_ex = explode(".",$ip_addr);
			$gw_addr = $gw_addr_ex[0].".".$gw_addr_ex[1].".".$gw_addr_ex[2].".1";
			$dns_addr = "203.248.252.2";
		}else
		{
			$ip_addr = "&nbsp;";
			$sub_addr = "&nbsp;";
			$gw_addr = "&nbsp;";
			$dns_addr = "&nbsp;";
		}
		//============================================================================
		// MY Vacation 전년이월
		//============================================================================
		$sql = "select * from diligence_tbl where MemberNo = '".$MemberNo."' and date like '%".$nowYear."%'";
		//echo $sql."<br>";
		$re = mysql_query($sql,$db);
		$re_num = mysql_num_rows($re);
		if($re_num > 0)
		{
			$rest_day = mysql_result($re,0,"rest_day");

			if($rest_day > 0)  //전년월차 남아있으면 모두 0으로처리
			{
				$rest_day=0;
			}

			$rest_day = $rest_day + (double)mysql_result($re,0,"spend_day");
		}
		else
		{
			$rest_day = "&nbsp;";
		}

		//============================================================================
		// MY Vacation 연차생성  -임원은 표시안함   //J07202(김회성),T02303(신현우) +1
		//============================================================================
		$EnterYear = substr($EntryDate,0,4);  //입사년도
		$EnterMonth = substr($EntryDate,5,2);  //입사월
		$EnterDay = substr($EntryDate,8,2);  //입사일

		if(substr($RankCode,0,1) =="C") //임원(전무,상무,이사)
		{
			$rest_day=0;
			$new_day=0;
			$sum_day=0;
			$employee=false;
		}else
		{
				$employee=true;

				if($EntryDate <"2017-05-30")
				{
					$JoinYear = date("Y") - $EnterYear; //현제년-입사년
					if($JoinYear <= 0) //1년미만은 없음
					{
						$new_day = 0;
					}
					elseif($JoinYear == 1) //1년이상은 월별 차등지급
					{
						if($EnterMonth == "01"){$new_day = 15;}
						elseif($EnterMonth == "02"){$new_day = 14;}
						elseif($EnterMonth == "03"){$new_day = 13;}
						elseif($EnterMonth == "04"){$new_day = 11;}
						elseif($EnterMonth == "05"){$new_day = 10;}
						elseif($EnterMonth == "06"){$new_day = 9;}
						elseif($EnterMonth == "07"){$new_day = 7;}
						elseif($EnterMonth == "08"){$new_day = 6;}
						elseif($EnterMonth == "09"){$new_day = 5;}
						elseif($EnterMonth == "10"){$new_day = 3;}
						elseif($EnterMonth == "11"){$new_day = 2;}
						elseif($EnterMonth == "12"){$new_day = 0;}
					}
					else  //그외는 2년에 1일씩 증가
					{
						$remainder=$JoinYear % 2;
						if ($remainder == 0 )
						{
							$division=(int)($JoinYear/2);
							$new_day= $division-1+15;
						}
						else
						{
							$division=(int)($JoinYear/2);
							$new_day= $division+15-1;
						}
					}
					$new_day=$new_day+$vacation;
					$sum_day=$new_day + $rest_day;


						//============================================================================
						// MY Vacation 사용휴가
						//============================================================================
						$StartDay = $nowYear."-01-01";
						$EndDay   = $nowYear."-12-31";

						$sql = "select * from userstate_tbl where (state = 1 or state = 18 or state = 30 or state = 31) and MemberNo = '".$MemberNo."' and start_time like '".$nowYear."%'";
						//echo $sql."<br>";
						$re  = mysql_query($sql,$db);
						$re_num = mysql_num_rows($re);
						if($re_num > 0)
						{
							while($re_row = mysql_fetch_array($re))
							{

								if($re_row[state]=="1")
								{
									if($re_row[start_time] >= $StartDay && $re_row[end_time] <= $EndDay)
									{
										$spend = calculate($re_row[start_time],$re_row[end_time],$re_row[note],$re_row[state]);
									}
									elseif($re_row[start_time] < $StartDay)
									{
										if($re_row[end_time] > $EndDay)
										{
											$spend = calculate($StartDay,$EndDay,$re_row[note],$re_row[state]);
										}
										else
										{
											$spend = calculate($StartDay,$re_row[end_time],$re_row[note],$re_row[state]);
										}
									}
									else
									{
										$spend = calculate($re_row[start_time],$EndDay,$re_row[note],$re_row[state]);
									}
									$spend_day = $spend_day + $spend;
								}else if( $re_row[state] == '30' or $re_row[state] == '31' ){
									$spend_day = $spend_day + 0.5;
									//echo $spend_day;
								}else
								{
									$spend_hour+=$re_row[sub_code];
								}
							}
						}

						$rest_day=$rest_day*8;
						$new_day=$new_day*8;
						$sum_day=$rest_day+$new_day;
						$use_day=$spend_day*8+$spend_hour;
						$remaind_day=$sum_day-$use_day;

						//--일 시간 으로 변환-----
						$rest_day=hourtodatehour($rest_day);
						$new_day=hourtodatehour($new_day);
						$sum_day=hourtodatehour($sum_day);
						$use_day=hourtodatehour($use_day);
						$remaind_day=hourtodatehour($remaind_day);
						//--일 시간 으로 변환-----

				}else{

						$this->assign('vac_type','enterdate');
						$this_year = date("Y");
						//입사일
						$enter_start=$this_year."-".$EnterMonth."-".$EnterDay;
						//오늘날짜
						$now_start=$this_year."-".date("m-d");

						if($EntryDate>$now_start)
						{
							$rest_day=""; //이월연차
							$new_day="";  //생성연차
							$new_day="";  //연차합계
							$sum_day="";  //사용연차
							$remain=""; //잔여연차
						}else
						{
							if($enter_start > $now_start)
							{
								$this_year2=$this_year-1;
								$year_start=$this_year2."-".$EnterMonth."-".$EnterDay;

							}else
							{
								$year_start=$enter_start;
							}

							$year_end = date("Y-m-d", strtotime("+1 year", strtotime($year_start)));
							$year_end = date("Y-m-d", strtotime("-1 day", strtotime($year_end)));

							$ThisDay=$this_year."-".date("m-d");
							if($ThisDay < $year_end )
							{
								$ThisDay=$year_end;
							}

							$arryear=getDiffdate_v3($Today, $EntryDate);
							$yeargap=$arryear[yeargap];

							if($yeargap==0)  //1년미만
							{
								$ThisDay=$this_year."-".date("m-d");
							}
							//echo $this_year;
							//$tmpData=getAnnualLeaveNew2($ThisDay,$EntryDate,$MemberNo);
							$tmpData=getAnnualLeaveNew2_v3($ThisDay,$EntryDate,$MemberNo,$vacation,$this_year);
							$rest_day=$tmpData[5]; //이월연차
							$new_day=$tmpData[11];  //생성연차
							$createvacation_sum=($tmpData[0]+$tmpData[10]);
							$sum_day=hourtodatehour_v3($createvacation_sum);  //총계
							$use_day=$tmpData[8];  //사용연차
							$rest_day_e= ($createvacation_sum)-($tmpData[3]);
							$f_rest_day_e=hourtodatehour_v3($rest_day_e);
							$remaind_day=$f_rest_day_e; //잔여연차  */
						}

						$StartDay= $year_start;
						$EndDay= $year_end;

				}

					/*
				list($use_day,$use_hour,$remain_hour)=UsedAnnualDayPeriod($StartDay,$EndDay,$MemberNo);

				$spend_day=$spend_day+$use_day;
				$remain=$remain-$use_day;

				if($use_hour>0 && $use_hour <4)
				{
					if($remain<0)
					{
						$remain_hour=$remain_hour-8;
					}
				}
				else if($use_hour ==4)
				{
					$remain=$remain-0.5;
					$remain_hour="";
					$spend_day=$spend_day+0.5;
					$use_hour="";

				}else if($use_hour>=4 && $use_hour<=8)
				{
					$remain=$remain-1;
					//$remain_hour="";
				}
				else if($use_hour==0)
				{
					$remain_hour="";
				}


				$this->assign('use_hour',$use_hour);
				$this->assign('remain_hour',$remain_hour);
				*/
		}

		//============================================================================
		// MY Vacation 결근표시
		//============================================================================
		$sql = "select * from userstate_tbl where state = 13 and MemberNo = '".$MemberNo."' and start_time like '".$nowYear."%'";
		$re = mysql_query($sql,$db);
		$absence_num = mysql_num_rows($re);

		if($EntryDate != "0000-00-00"){
			$dateDifference = abs(strtotime(date("Y-m-d")) - strtotime($EntryDate));

			$years  = floor($dateDifference / (365 * 60 * 60 * 24));
			$months = floor(($dateDifference - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
			$days   = floor(($dateDifference - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 *24) / (60 * 60 * 24));

			$totalDays = ($years*365)+($months*30)+$days;

			$TenuerPeriod = $years."년 ".$months."개월 ".$days."일"."(".$totalDays."일)";
		}
		else{
			$TenuerPeriod="";
		}
		global $WorkPosition;
		$this->assign('WorkPosition',$WorkPosition);

		//내정보
		$this->assign('my_GroupName',$GroupName);
		$this->assign('my_Position',$position);
		$this->assign('my_Name',$korName);
		$this->assign('my_MemberNo',$MemberNo);
		//$this->assign('my_EntryDate',$member_EntryDate);  //20141222수정:20141226부터 삭제가능
		$this->assign('my_EntryDate',$EntryDate);
		$this->assign('my_TenuerPeriod',$TenuerPeriod);
		$this->assign('my_Pasword',$Pasword);
		$this->assign('my_MemberPic',$MemberPic);
		//MY Hardware
		$this->assign('my_CPU',$CPU);
		$this->assign('my_RAM',$RAM);
		$this->assign('my_HDD',$HDD);
		$this->assign('my_ip_addr',$ip_addr);
		$this->assign('my_sub_addr',$sub_addr);
		$this->assign('my_gw_addr',$gw_addr);
		$this->assign('my_dns_addr',$dns_addr);
		//MY Vacation
		$this->assign('my_rest_day',$rest_day);
		$this->assign('my_new_day',$new_day);
		$this->assign('my_sum_day',$sum_day);

		$this->assign('rest_day',$rest_day);
		$this->assign('new_day',$new_day);
		$this->assign('sum_day',$sum_day);
		$this->assign('use_day',$use_day);
		$this->assign('remaind_day',$remaind_day);


		$this->assign('my_absence_num',$absence_num);
		$this->assign('my_spend_day',$spend_day);
		$this->assign('my_remain',$remain);

		$todayYear = substr($nowMonth,0,4);
		$todayMonth = substr($nowMonth,5,2);
		$this->assign('todayYear',$todayYear);
		$this->assign('todayMonth',$todayMonth);

		$preMonth = substr($MonthAgo1,5,2);
		$this->assign('preMonth',$preMonth);
		$this->assign('day_count',$day_count);
		$this->assign('employee',$employee);

	}



	/* ******************************************************************************************************** */
	/* 연장근무 합산시간 평일/휴일***************************************************************************** */
	function OverworkData()
	{
		global	$CompanyKind;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		/* ------------------ */
		global	$MemberNo;			//사원번호
		global	$korName;			//한글이름
		/* ------------------ */
		global	$date_today;		// 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1;		// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$date_today2;		// 오늘날짜
		global	$date_today3;
		global	$nowYear;			// 오늘날짜 년          : yyyy
		global	$nowMonth;			// 오늘날짜 년월        : yyyy-mm
		global	$todayName;			//오늘의 요일(일:0, 월:1,화:2,수:3,목:4,금:5,토:6)
		/* ------------------ */
		global	$MonthAgo1;
		global	$MonthAgo2;
		/* ------------------ */
		$weekdayOTcount; //평일연장근무 횟수
		$holydayOTcount; //휴일연장근무 횟수
		/*----------------------------------------------------------------------------*/
		/*/////////////////////////////////////////////////////////////////// */
		//금월의 연장근무 산정기간은 전월21일부터 현월20일까지를 집계한다.
		/*/////////////////////////////////////////////////////////////////// */
		///////////////////////////////////

		if($nowMonth=="2023-02"){
			$date_start  = $MonthAgo1."-18";
			$date_end    = $nowMonth."-20"; // 1월은 12/21~01/17, 2월은 01/18~02/20
		}else{
			$date_start  = $MonthAgo1."-21";
			$date_end    = $nowMonth."-20";
		}
		$this->assign('nowMonth',$nowMonth);

		///////////////////////////////////
		$sql01=        " SELECT	*									    ";
		$sql01= $sql01."	FROM										";
		$sql01= $sql01."		DallyProject_tbl D						";
		$sql01= $sql01."	WHERE										";
		$sql01= $sql01."		D.MemberNo = '".$MemberNo."'			";
		$sql01= $sql01."		AND										";
		$sql01= $sql01."		D.EntryTime >='".$date_start."'			";
		$sql01= $sql01."		AND										";
		$sql01= $sql01."		D.EntryTime <='".$date_end." 23:00:00'	";

	/* 코드사용분기 Start  *************** */
	if($CompanyKind=="PILE" || $CompanyKind=="HANM" || $CompanyKind=="BARO"){//파일테크(PILE),바론컨설턴트(HANM)
		$sql01= $sql01."		AND										";
		$sql01= $sql01."		D.modify ='1'							";

		//echo $sql01;
		/*-----------------------------------------------------------------*/
		$re01 = mysql_query($sql01);
		/*-----------------------------------------------------------------*/
		while($re_row01 = mysql_fetch_array($re01)){
			$holy_sc = holy(substr($re_row01[EntryTime],0,10));
			/*--------------------------------------------------*/
			if($holy_sc == "weekday"){	// 평일 일 때  OverTime 야근시작시간  LeaveTime퇴근시간
				//야근시작시간이 있을때 (평일은 야근시작시간)
				/* 파일테크*/
				if($re_row01[OverTime] != "0000-00-00 00:00:00" && $re_row01[modify]=="1"){
					$weekdayOTcount++;
				}//if End
			}elseif($holy_sc == "holyday"){ ////휴일 일 때  EntryTime 야근시작시간  LeaveTime퇴근시간
				//출근시간이 있을때
				if($re_row01[EntryTime] != "0000-00-00 00:00:00" && $re_row01[modify]=="1"){
					$holydayOTcount++;
				}//if End
			}//if End
		}//while End
		/*-----------------------------------------------------------------*/
	}else if($CompanyKind=="JANG"){//장헌산업(JANG)
		/*-----------------------------------------------------------------*/
		$re01 = mysql_query($sql01);
		/*-----------------------------------------------------------------*/
		while($re_row01 = mysql_fetch_array($re01)){
			/*--------------------------------------- -----------*/
			$entrydate = substr($re_row01[EntryTime],5,2)."-".substr($re_row01[EntryTime],8,2);
			$overdate  = substr($re_row01[OverTime],5,2)."-".substr($re_row01[OverTime],8,2);
			$leavedate = substr($re_row01[LeaveTime],5,2)."-".substr($re_row01[LeaveTime],8,2);
			/*--------------------------------------------------*/
			$e_time = substr($re_row01[EntryTime],11,5);
			$o_time = substr($re_row01[OverTime],11,5);
			$l_time = substr($re_row01[LeaveTime],11,5);
			$holy_sc = holy(substr($re_row01[EntryTime],0,10));
			/*--------------------------------------------------*/
			 //// 평일 일 때  OverTime 야근시작시간  LeaveTime퇴근시간
			if($holy_sc == "weekday"){
				//야근시작시간이 있을때 (평일은 야근시작시간)
				if($re_row01[OverTime] != "0000-00-00 00:00:00"){
					//야근시작시간이 19:00 이전이면 19:00 부터야근시작시간
					if($o_time < $weekday_start){
						$o_time=$weekday_start;
					}//if End
					/*--------------------------------------------------*/
					$sl_time = strtotime($l_time);
					$so_time = strtotime($o_time);
					/*--------------------------------------------------*/
					$ottime = sec_time00($sl_time - $so_time);
					$ottime_tmp= $sl_time - $so_time;
					/*--------------------------------------------------*/
					if ($leavedate > $overdate || $ottime >= $weekday_min){ //다음날새벽에 끝나는 경우야근처리 ,//최소근무시간 2시간이상이면 야근표시
						$weekdayOTcount++;
					}//if End
					/*--------------------------------------------------*/
				}//if End
			}elseif($holy_sc == "holyday"){ ////휴일 일 때  EntryTime 야근시작시간  LeaveTime퇴근시간
				//출근시간이 있을때
				if($re_row01[EntryTime] != "0000-00-00 00:00:00"){
					//출근시작시간이 09:00 이전이면 09:00 부터야근시작시간
					if($e_time < $holy_start){
						$e_time=$holy_start;
					}//if End
					/*--------------------------------------------------*/
					$sl_time = strtotime($l_time);
					$se_time = strtotime($e_time);
					$ottime = sec_time00($sl_time - $se_time);
					/*--------------------------------------------------*/
					if ($leavedate > $entrydate || $ottime >= $holy_min){ //다음날새벽에 끝나는 경우야근처리///휴일최소근무시간 3시간이상이면 야근표시
						$holydayOTcount++;
					}//if End
					/*--------------------------------------------------*/
				}//if End
			}//if End
		}//while End
		/*--------------------------------------------------*/
	}//if End
	/* 코드사용분기 End  *************** */


		$this->assign('CompanyKind',$CompanyKind);

		$this->assign('weekdayOTcount',$weekdayOTcount);
		$this->assign('holydayOTcount',$holydayOTcount);
		/*--------------------------------------------------*/
	}//OverworkData


	/* ******************************************************************************************************** */
	/* 연장근무 정보 시간:분 평일/휴일***************************************************************************** */
	function OverworkData_time()
	{
		global	$CompanyKind;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		//----------------------------------------------------------------------------
		global	$MemberNo;			//사원번호
		global	$RankCode;
		global	$korName;			//한글이름
		//----------------------------------------------------------------------------
		global	$date_today;		// 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1;		// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$date_today2;		// 오늘날짜
		global	$date_today3;
		global	$nowYear;			// 오늘날짜 년          : yyyy
		global	$nowMonth;			// 오늘날짜 년월        : yyyy-mm
		global	$todayName;			//오늘의 요일(일:0, 월:1,화:2,수:3,목:4,금:5,토:6)
		//----------------------------------------------------------------------------
		global	$MonthAgo1;
		global	$MonthAgo2;
		//----------------------------------------------------------------------------
		$weekdayOTcount; //평일연장근무 횟수
		$holydayOTcount; //휴일연장근무 횟수
		//----------------------------------------------------------------------------
		$return_hour=0;		//인정 : 연장근무시간 : 시간 : 평일
		$return_min=0;		//인정 : 연장근무시간 : 분 : 평일
		$return_hour2=0;	//인정 : 연장근무시간 : 시간 : 휴일
		$return_min2=0;		//인정 : 연장근무시간 : 분 : 휴일

		$return_hour3=0;	//실제 : 연장근무시간 : 시간 : 평일
		$return_min3=0;		//실제 : 연장근무시간 : 분 : 평일
		$return_hour4=0;	//실제 : 연장근무시간 : 시간 : 휴일
		$return_min2=0;		//실제 : 연장근무시간 : 분 : 휴일

		$weekday_cnt=0; //평일야근횟수
		$holyday_cnt=0;  //휴일야근횟수
		//----------------------------------------------------------------------------
		/*/////////////////////////////////////////////////////////////////// */
		//금월의 연장근무 산정기간은 전월21일부터 현월20일까지를 집계한다.
		/*/////////////////////////////////////////////////////////////////// */
		///////////////////////////////////
		if($nowMonth=="2023-02"){
			$date_start  = $MonthAgo1."-18";
			$date_end    = $nowMonth."-20";  //1월은 12/21~01/17, 2월은 01/18~02/20
		}else{
			$date_start  = $MonthAgo1."-21";
			$date_end    = $nowMonth."-20";
		}
		$this->assign('nowMonth',$nowMonth);

		///////////////////////////////////
		$sql01= "		SELECT													";
		$sql01 .= "  D.MemberNo MemberNo 								";//사원번호
		$sql01 .= " ,DATE_FORMAT(D.EntryTime, '%Y-%m-%d') EntryTime_yyyymmdd 								";//업무시작 시간
		$sql01 .= " ,DATE_FORMAT(D.LeaveTime, '%Y-%m-%d') LeaveTime_yyyymmdd 								";//업무종료 시간
		$sql01 .= " ,DATE_FORMAT(D.OverTime, '%Y-%m-%d') OverTime_yyyymmdd 									";//연장근무시작 시간
		$sql01 .= "		From													";
		$sql01 .= "		dallyproject_tbl D								";
		$sql01 .= "	WHERE														";
		$sql01 .= "		D.MemberNo = '".$MemberNo."'					";
		$sql01 .= "		AND														";
		$sql01 .= "		D.EntryTime >='".$date_start."'					";
		$sql01 .= "		AND														";
		$sql01 .= "		D.EntryTime <='".$date_end." 23:00:00'		";
		//echo $sql01;
		//----------------------------------------------------------------------------
		$re01 = mysql_query($sql01);
		//----------------------------------------------------------------------------
		while($re_row01 = mysql_fetch_array($re01)){
			$EntryTime_yyyymmdd = $re_row01[EntryTime_yyyymmdd];
			$array_overtimeCheck_time = overtimeCheck_time($EntryTime_yyyymmdd, $MemberNo, $RankCode);//개인별&일자별 연장근무 정보(//return = 연장근무Y/N:일자:인정연장근무(시간):인정연장근무(분):실제연장근무(시간):실제연장근무(분)
			//echo $array_overtimeCheck_time[0].' : '.$array_overtimeCheck_time[1].' : '.$array_overtimeCheck_time[2].' : '.$array_overtimeCheck_time[3].'<br>';

			if($array_overtimeCheck_time[0]=="Y"){
				$holy_sc = holy($array_overtimeCheck_time[1]);
				if($holy_sc == "weekday") {
					// 평일
					$return_hour = $return_hour+(int)$array_overtimeCheck_time[2];
					$return_min = $return_min+(int)$array_overtimeCheck_time[3];
					//--------------------------------------------------------------------------------
					$return_hour3=$return_hour3+(int)$array_overtimeCheck_time[4];		//실제 : 연장근무시간 : 시간 : 평일
					$return_min3=$return_min3+(int)$array_overtimeCheck_time[5];		//실제 : 연장근무시간 : 분 : 평일
					//--------------------------------------------------------------------------------
				}else{
					// 휴일
					//--------------------------------------------------------------------------------
					$return_hour2 = $return_hour2+(int)$array_overtimeCheck_time[2];
					$return_min2 = $return_min2+(int)$array_overtimeCheck_time[3];
					//--------------------------------------------------------------------------------
					$return_hour4=$return_hour4+(int)$array_overtimeCheck_time[4];		//실제 : 연장근무시간 : 시간 : 휴일
					$return_min4=$return_min4+(int)$array_overtimeCheck_time[5];		//실제 : 연장근무시간 : 분 : 휴일
					//--------------------------------------------------------------------------------
				}
			}
		}//while End
		//----------------------------------------------------------------------------
		$array_calculateDivision = calculateDivision((int)$return_min,60);
		$return_hour=$return_hour+$array_calculateDivision[0];//연장근무시간 : 시간 : 평일
		$return_min=$array_calculateDivision[1];//연장근무시간 : 분 : 평일
		//----------------------------------------------------------------------------
		$array_calculateDivision2 = calculateDivision((int)$return_min2,60);
		$return_hour2=$return_hour2+$array_calculateDivision2[0];//연장근무시간 : 시간 : 휴일
		$return_min2=$array_calculateDivision2[1];//연장근무시간 : 분 : 휴일

		//----------------------------------------------------------------------------
		$array_calculateDivision3 = calculateDivision((int)$return_min3,60);
		$return_hour3=$return_hour3+$array_calculateDivision3[0];//연장근무시간 : 시간 : 평일
		$return_min3=$array_calculateDivision3[1];//연장근무시간 : 분 : 휴일
		//----------------------------------------------------------------------------
		$array_calculateDivision4 = calculateDivision((int)$return_min4,60);
		$return_hour4=$return_hour4+$array_calculateDivision4[0];//실제 : 연장근무시간 : 시간 : 휴일
		$return_min4=$array_calculateDivision4[1];//실제 : 연장근무시간 : 분 : 휴일
		//----------------------------------------------------------------------------
		/*
		echo '=====================================<br>';
		echo '평일횟수 : '.$weekday_cnt.' <br>';
		echo '실제시간 : 평일 : '.sprintf("%02d",$return_hour3).' : '.sprintf("%02d",$return_min3).'  <br>';
		echo '인정시간 : 평일 : '.sprintf("%02d",$return_hour).' : '.sprintf("%02d",$return_min).'  <br>';
		echo '=====================================<br>';
		echo '휴일횟수 : '.$holyday_cnt.' <br>';
		echo '실제시간 : 휴일 : '. sprintf("%02d",$return_hour4).' : '. sprintf("%02d",$return_min4).'  <br>';
		echo '인정시간 : 휴일 : '. sprintf("%02d",$return_hour2).' : '. sprintf("%02d",$return_min2).'  <br>';
		echo '=====================================<br>';
		*/
		//----------------------------------------------------------------------------
		//$this->assign('CompanyKind',$CompanyKind);
		$this->assign('return_hour', sprintf("%02d",$return_hour));
		$this->assign('return_min', sprintf("%02d",$return_min));
		$this->assign('return_hour2', sprintf("%02d",$return_hour2));
		$this->assign('return_min2', sprintf("%02d",$return_min2));
		//----------------------------------------------------------------------------
	}//OverworkData_time


	/* ******************************************************************************************************** */
	/* 연장근무 정보 시간:분 평일/휴일***************************************************************************** */
	function OverworkData_time_test()
	{
		global	$CompanyKind;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		//----------------------------------------------------------------------------
		global	$MemberNo;			//사원번호
		global	$RankCode;
		global	$korName;			//한글이름
		//----------------------------------------------------------------------------
		global	$date_today;		// 오늘날짜 년월일      : yyyy-mm-dd
		global	$date_today1;		// 오늘날짜 년월일 시분 : yyyy-mm-dd 시 분
		global	$date_today2;		// 오늘날짜
		global	$date_today3;
		global	$nowYear;			// 오늘날짜 년          : yyyy
		global	$nowMonth;			// 오늘날짜 년월        : yyyy-mm
		global	$todayName;			//오늘의 요일(일:0, 월:1,화:2,수:3,목:4,금:5,토:6)
		//----------------------------------------------------------------------------
		global	$MonthAgo1;
		global	$MonthAgo2;
		//----------------------------------------------------------------------------
		$weekdayOTcount; //평일연장근무 횟수
		$holydayOTcount; //휴일연장근무 횟수
		//----------------------------------------------------------------------------
		$return_hour=0;		//인정 : 연장근무시간 : 시간 : 평일
		$return_min=0;		//인정 : 연장근무시간 : 분 : 평일
		$return_hour2=0;	//인정 : 연장근무시간 : 시간 : 휴일
		$return_min2=0;		//인정 : 연장근무시간 : 분 : 휴일

		$return_hour3=0;	//실제 : 연장근무시간 : 시간 : 평일
		$return_min3=0;		//실제 : 연장근무시간 : 분 : 평일
		$return_hour4=0;	//실제 : 연장근무시간 : 시간 : 휴일
		$return_min2=0;		//실제 : 연장근무시간 : 분 : 휴일

		$weekday_cnt=0; //평일야근횟수
		$holyday_cnt=0;  //휴일야근횟수
		//----------------------------------------------------------------------------
		/*/////////////////////////////////////////////////////////////////// */
		//금월의 연장근무 산정기간은 전월21일부터 현월20일까지를 집계한다.
		/*/////////////////////////////////////////////////////////////////// */
		///////////////////////////////////
		$date_start  = $MonthAgo1."-21";
		$date_end    = $nowMonth."-20";
		///////////////////////////////////
		$sql01= "		SELECT													";
		$sql01 .= "  D.MemberNo MemberNo 								";//사원번호
		$sql01 .= " ,DATE_FORMAT(D.EntryTime, '%Y-%m-%d') EntryTime_yyyymmdd 								";//업무시작 시간
		$sql01 .= " ,DATE_FORMAT(D.LeaveTime, '%Y-%m-%d') LeaveTime_yyyymmdd 								";//업무종료 시간
		$sql01 .= " ,DATE_FORMAT(D.OverTime, '%Y-%m-%d') OverTime_yyyymmdd 									";//연장근무시작 시간
		$sql01 .= "		From													";
		$sql01 .= "		dallyproject_tbl D								";
		$sql01 .= "	WHERE														";
		$sql01 .= "		D.MemberNo = '".$MemberNo."'					";
		$sql01 .= "		AND														";
		$sql01 .= "		D.EntryTime >='".$date_start."'					";
		$sql01 .= "		AND														";
		$sql01 .= "		D.EntryTime <='".$date_end." 23:00:00'		";
		//echo $sql01;
		//----------------------------------------------------------------------------
		$re01 = mysql_query($sql01);
		//----------------------------------------------------------------------------
		while($re_row01 = mysql_fetch_array($re01)){
			$EntryTime_yyyymmdd = $re_row01[EntryTime_yyyymmdd];

	$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
	if($myip=="1.229.157.66"){
			$array_overtimeCheck_time = overtimeCheck_time_test($EntryTime_yyyymmdd, $MemberNo, $RankCode);//개인별&일자별 연장근무 정보(//return = 연장근무Y/N:일자:인정연장근무(시간):인정연장근무(분):실제연장근무(시간):실제연장근무(분)
	}else{
			$array_overtimeCheck_time = overtimeCheck_time($EntryTime_yyyymmdd, $MemberNo, $RankCode);//개인별&일자별 연장근무 정보(//return = 연장근무Y/N:일자:인정연장근무(시간):인정연장근무(분):실제연장근무(시간):실제연장근무(분)
			//echo $array_overtimeCheck_time[0].' : '.$array_overtimeCheck_time[1].' : '.$array_overtimeCheck_time[2].' : '.$array_overtimeCheck_time[3].'<br>';
	}

			if($array_overtimeCheck_time[0]=="Y"){
				$holy_sc = holy($array_overtimeCheck_time[1]);
				if($holy_sc == "weekday") {
					// 평일
					$weekday_cnt++;
					$return_hour = $return_hour+(int)$array_overtimeCheck_time[2];
					$return_min = $return_min+(int)$array_overtimeCheck_time[3];
					//--------------------------------------------------------------------------------
					$return_hour3=$return_hour3+(int)$array_overtimeCheck_time[4];		//실제 : 연장근무시간 : 시간 : 평일
					$return_min3=$return_min3+(int)$array_overtimeCheck_time[5];		//실제 : 연장근무시간 : 분 : 평일
					//--------------------------------------------------------------------------------
				}else{
					// 휴일
					$holyday_cnt++;
					//--------------------------------------------------------------------------------
					$return_hour2 = $return_hour2+(int)$array_overtimeCheck_time[2];
					$return_min2 = $return_min2+(int)$array_overtimeCheck_time[3];
					//--------------------------------------------------------------------------------
					$return_hour4=$return_hour4+(int)$array_overtimeCheck_time[4];		//실제 : 연장근무시간 : 시간 : 휴일
					$return_min4=$return_min4+(int)$array_overtimeCheck_time[5];		//실제 : 연장근무시간 : 분 : 휴일
					//--------------------------------------------------------------------------------
				}
			}
		}//while End
		//----------------------------------------------------------------------------
		$array_calculateDivision = calculateDivision((int)$return_min,60);
		$return_hour=$return_hour+$array_calculateDivision[0];//연장근무시간 : 시간 : 평일
		$return_min=$array_calculateDivision[1];//연장근무시간 : 분 : 평일
		//----------------------------------------------------------------------------
		$array_calculateDivision2 = calculateDivision((int)$return_min2,60);
		$return_hour2=$return_hour2+$array_calculateDivision2[0];//연장근무시간 : 시간 : 휴일
		$return_min2=$array_calculateDivision2[1];//연장근무시간 : 분 : 휴일

		//----------------------------------------------------------------------------
		$array_calculateDivision3 = calculateDivision((int)$return_min3,60);
		$return_hour3=$return_hour3+$array_calculateDivision3[0];//연장근무시간 : 시간 : 평일
		$return_min3=$array_calculateDivision3[1];//연장근무시간 : 분 : 휴일
		//----------------------------------------------------------------------------
		$array_calculateDivision4 = calculateDivision((int)$return_min4,60);
		$return_hour4=$return_hour4+$array_calculateDivision4[0];//실제 : 연장근무시간 : 시간 : 휴일
		$return_min4=$array_calculateDivision4[1];//실제 : 연장근무시간 : 분 : 휴일
		//----------------------------------------------------------------------------
		/*
		echo '=====================================<br>';
		echo '평일횟수 : '.$weekday_cnt.' <br>';
		echo '실제시간 : 평일 : '.sprintf("%02d",$return_hour3).' : '.sprintf("%02d",$return_min3).'  <br>';
		echo '인정시간 : 평일 : '.sprintf("%02d",$return_hour).' : '.sprintf("%02d",$return_min).'  <br>';
		echo '=====================================<br>';
		echo '휴일횟수 : '.$holyday_cnt.' <br>';
		echo '실제시간 : 휴일 : '. sprintf("%02d",$return_hour4).' : '. sprintf("%02d",$return_min4).'  <br>';
		echo '인정시간 : 휴일 : '. sprintf("%02d",$return_hour2).' : '. sprintf("%02d",$return_min2).'  <br>';
		echo '=====================================<br>';
		*/
		//----------------------------------------------------------------------------
		//$this->assign('CompanyKind',$CompanyKind);
		$this->assign('return_hour', sprintf("%02d",$return_hour));
		$this->assign('return_min', sprintf("%02d",$return_min));
		$this->assign('return_hour2', sprintf("%02d",$return_hour2));
		$this->assign('return_min2', sprintf("%02d",$return_min2));
		//----------------------------------------------------------------------------
	}//OverworkData_time_test

	/* ******************************************************************************************************** */
	function ListAction()			//나의정보 페이지 TPL 경로지정
	{
		global	$CompanyKind, $MemberNo;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)

		$this->display("intranet/common_contents/work_myInfo/myInfo.tpl");
	}//function ListAction End

	function MyInfoSave()
	{
		global $db, $MemberNo, $smarty;
		extract($_REQUEST);
		$oracle=new OracleClass($smarty);

		switch ($SubAction){
			case 'save':
				//주소, 세부주소분리는 띄어쓰기 세칸으로 함(그냥 보기에 불편하지않고 분리하기 쉽게)
				$sql = "UPDATE member_tbl SET
					  postcode = '{$postcode}',
					  Address = '{$address}   {$address2}',
					  BirthdayViewYn = '{$birthdayViewYn}',
					  BirthdayType = '{$birthdayType}',
					  Birthday = '{$birthday}'
				WHERE MemberNo = '{$MemberNo}' LIMIT 1";
				$re=mysql_query($sql,$db);
				//echo $re;

				$birthday_short = str_replace('-', '', $birthday);
				$prosql2 ="BEGIN Usp_Hr_Manager_0402_Mini_Up('{$MemberNo}','{$postcode}','{$address}','{$address2}','{$birthdayType}','{$birthday_short}','{$MemberNo}'); END;";
				$oracle->ProcedureExcuteQuery($oracle->HangleEncodeUTF8_EUCKR($prosql2));

				echo 1;
				break;
			default:
				echo "No SubAction";
				break;
		}
	}
	/* ------------------------------------- */
	/* ******************************************************************************************************** */
	function LateInfoAction()   //지각표시 관련 올해월별
	{
		global $CompanyKind;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		global $db;
		global $nowYear;
		global $MemberNo;
		//============================================================================
		// MY Vacation 지각표시
		//============================================================================
		$ThisMonth = sprintf('%04d-%02d',date("Y"),date("m"));
		/*--------------------------------------------------*/
		$sql="
			/*
			select DATE_FORMAT(EntryTime, '%Y-%m-%d') as EntryTime from dallyproject_tbl where MemberNo = '".$MemberNo."' and EntryTime like '".$ThisMonth."%'  and DATE_FORMAT(EntryTime, '%H:%i') > '09:00'
			*/
			select
				EntryTime
				,DATE_FORMAT(EntryTime, '%Y-%m-%d') as EntryTime_yyyymmdd
			from
				( select * from dallyproject_tbl where MemberNo = '".$MemberNo."' and DATE_FORMAT(EntryTime, '%Y-%m') = '".$ThisMonth."' ) A
				left join
				worker_tardy_tbl B
			on
				A.MemberNo = B.memberno
				and DATE_FORMAT(A.EntryTime, '%Y-%m-%d') between B.s_date and B.e_date
			where
				DATE_FORMAT(A.EntryTime, '%H:%i') > concat(LPAD(IFNULL(B.tardy_h, 9),2,'0') , ':', LPAD(IFNULL(B.tardy_m, 0),2,'0'))
		";

		//echo $sql;

		$re = mysql_query($sql);
		$re_num = mysql_num_rows($re);
		if($re_num > 0)
		{
			while($re_row = mysql_fetch_array($re))
			{
				$EntryTime=$re_row[EntryTime_yyyymmdd];
				if(holy($EntryTime) == "weekday") // 평일인경우만  -> 월~금,holyday_tbl에 없는날
				{
					$sql2 = "select * from userstate_tbl where MemberNo = '".$MemberNo."' and (start_time <= '".$EntryTime."' and end_time >= '".$EntryTime."') order by num";
					$re2 = mysql_query($sql2,$db);
					$re_num2 = mysql_num_rows($re2);
					if($re_num2 > 0)  /// 값이 있을 때
					{
						$u_note = mysql_result($re2,0,"note");
						$u_note = str_replace(" ","",$u_note);
						if($u_note == "오후반차")
						{
							$late_num++;
						}else{}
					}
					else
					{
						$late_num++;
					}
				}
			}
		}
		/*--------------------------------------------------*/
		$this->assign('late_num',$late_num);
		/*--------------------------------------------------*/
	}//function DetailListAction End
	/* ******************************************************************************************************** */
	function LateInfoAction_back()  //형석   //지각표시 관련 해당월
	{
		global $CompanyKind;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		global $db;
		global $MemberNo;
		global $nowMonth;
		$late_num;
		//============================================================================
		// MY Vacation 지각표시
		//============================================================================
		$sql="SELECT													";
		$sql=$sql."	DATE_FORMAT(EntryTime, '%Y-%m-%d') as EntryTime		";

		$sql=$sql."	,DATE_FORMAT(EntryTime, '%Y') as ETyyyy				";
		$sql=$sql."	,DATE_FORMAT(EntryTime, '%m') as ETmm				";
		$sql=$sql."	,DATE_FORMAT(EntryTime, '%d') as ETdd				";

		$sql=$sql."	FROM												";
		$sql=$sql."	dallyproject_tbl									";
		$sql=$sql."	where												";
		$sql=$sql."	MemberNo = '".$MemberNo."'							";
		$sql=$sql."	and													";
		$sql=$sql."	EntryTime like '".$nowMonth."%'						";
		$sql=$sql."	and													";
		$sql=$sql."	DATE_FORMAT(EntryTime, '%H:%i') > '09:00'			";
		/*--------------------------------------------------*/
		$re = mysql_query($sql);
		$re_num = mysql_num_rows($re);
		if($re_num > 0)
		{
			$late_num=0;
			while($re_row = mysql_fetch_array($re))
			{
				$e_EntryTime=$re_row[EntryTime];

					/*
					$tday= date("D",mktime(0,0,0,$re_row[ETmm],$re_row[ETdd],$re_row[ETyyyy]));
					echo $e_EntryTime.":".$tday."<br>";
					*/

				if(holy($e_EntryTime) == "weekday") // 평일인경우만  -> 월~금,holyday_tbl에 없는날
				{
					/*
					$tday= date("D",mktime(0,0,0,$re_row[ETmm],$re_row[ETdd],$re_row[ETyyyy]));
					echo $e_EntryTime.":".$tday."<br>";
					*/
					$late_num++;
				}
			}
		}
		/*--------------------------------------------------*/
		$this->assign('late_num',$late_num);
		/*--------------------------------------------------*/
	}//function DetailListAction End
	/* ******************************************************************************************************** */
	function DetailListAction()
	{
		global	$CompanyKind;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		/*--------------------------------------------------*/
		$this->display("intranet/common_contents/work_myInfo/myInfo_detail.tpl");
		/*--------------------------------------------------*/
	}//function DetailListAction End
	/* ------------------------------------------------------------------------------ */
	function CheckPwMainPage()// 개인정보로 가는 비밀번호 확인 페이지
	{
		global	$CompanyKind;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		global	$MemberNo;	  // 사원번호

		$this->assign('MemberNo',$MemberNo);
		$this->display("intranet/common_layout/checkPwMain.tpl");
	}  //CheckPwMainPage() End
	/* ------------------------------------------------------------------------------ */
	function CheckPw()// 개인정보로 가는 비밀번호 확인 db
	{
		global	$CompanyKind;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		$user_id = $_POST['user_id'];
		$user_pw = $_POST['user_pw'];
		/*--------------------------------------------------*/
		$sql="select * from member_tbl where MemberNo = '".$user_id."' and UserInfoPW='".$user_pw."'";

/*
		if($user_id=="Tadmin" || $user_id=="M21420" || $user_id=="M21421" || $user_id=="B20334"){
			$sql="select * from member_tbl where MemberNo = '".$user_id."' and UserInfoPW='".$user_pw."'";
		}else{
			$sql="select * from member_tbl where MemberNo = '".$user_id."' and Pasword='".$user_pw."'";
		}
*/
		/*--------------------------------------------------*/
		$result = mysql_query($sql);
		$re_num = mysql_num_rows($result);
        $returnStr;
		/*--------------------------------------------------*/
		if($re_num != 0){
			echo "1";
		}else{
			echo "2";
		}
		/*--------------------------------------------------*/
	}  //CheckPw() End
	/* ------------------------------------------------------------------------------ */
	function EditMailPW(){
		extract($_REQUEST);
		$this->assign('memberID',$memberID);
		$sql="SELECT korName, eMail from member_tbl where MemberNo = '$memberID'";
		$re = mysql_query($sql);
		while($re_row = mysql_fetch_array($re)){
			$this->assign('korName',$re_row[korName]);
			$email = explode( '@', $re_row[eMail] );
			$this->assign('email_id',$email[0]);
			$this->assign('email',$re_row[eMail]);
		}
		$this->display("intranet/common_contents/work_myInfo/email_pw_edit.tpl");
	}  //EditMailPW() End
	/* ------------------------------------------------------------------------------ */
	function EditMailPW_Action(){
		extract($_REQUEST);


		//비밀번호에 # 포함되어있을경우 urlencode로 변경해서 전송
		$email_pw = str_replace('#' , '%23', $email_pw);
/*
		$korName = '테스트';
		$email_id = 'test1';
		$email_pw = '1q2w3e4r!!';
*/
		$service_full_url = 'http://mail.hanmaceng.co.kr/admin/xml_user_control.php';
		$service_full_url = $service_full_url . ('?cmd=user_modify');
		$service_full_url = $service_full_url . ('&userinfo[name]=' . $korName);
		$service_full_url = $service_full_url . ('&userinfo[id]=' . $email_id);
		$service_full_url = $service_full_url . ('&userinfo[mbox_host]=' . "hanmaceng.co.kr");
		$service_full_url = $service_full_url . ('&userinfo[passwd]=' . $email_pw);
		$service_full_url = $service_full_url . ('&userinfo[common]=N');


		if($email_id == 'mjjeong1'){
			echo $service_full_url;
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $service_full_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		$response = curl_exec($ch);
/*
		echo var_dump($response); //결과 값 출력
		print_r(curl_getinfo($ch));    //모든 정보 출력
		echo curl_error($ch);           //에러 정보 출력
*/
		$errno = curl_errno($ch);
		if ($errno > 0) {
			if ($errno === 28) {
			  echo "Connection timed out.";
			}
			else {
			  echo "ERROR #" . $errno . ": " . curl_error($ch);
			}

			exit(0);
		}

		if (!$response) {	//빈값을때
			echo "ERROR - 1";
			exit(0);
		}

		//echo $response;

		$json_list = XML_To_JSON_Parse($response);	//function_intranet.php

		if (!$json_list) {	//xml이 비어있을때
			echo "ERROR - 2";
			exit(0);
		}

		$json_list= json_decode($json_list, true);
		curl_close($ch);

		if (!$json_list) {
			echo "ERROR - 3";
			exit(0);
		}

		//if(strcmp($json_list['result_code'],'-1') != 0 ) {
		if($json_list['result_code'] == '-1') {
			echo $json_list["msg"];
			//var_dump($json_list);
			//return 0; //success
		}else{
			//var_dump($json_list);
		}
		//return 1; //failed
	}  //EditMailPW_Action() End
	/* ------------------------------------------------------------------------------ */

/* ******************************************************************************************************** */
	function MOVE_PW_MANAGE()
	{
		global $db;
		extract($_REQUEST);
		global	$CompanyKind;    //회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		/*--------------------------------------------------*/
		global	$MemberNo;	  // 사원번호

		$tabmenu_kd = $tabmenu_kd==""?"intranet":$tabmenu_kd;


		/*
		$korName="";
		$email_id="";
		$sql="SELECT korName,eMail FROM member_tbl WHERE memberNo='$MemberNo'";
		$re=mysql_query($sql,$db);
		$renum=mysql_num_rows($re);
		if($renum>0){
			$korName=mysql_result($re,0,'korName');
			$email_id=mysql_result($re,0,'eMail');
		}

		*/

		$korName   	="";
		$email		="";
		$email_id	="";
		$temp_array = array();


		$sql="SELECT korName,eMail FROM member_tbl WHERE memberNo='$MemberNo'";
		$azRecord = mysql_query($sql,$db);
		if(mysql_num_rows($azRecord) > 0){
			$korName=	 mysql_result($azRecord,0,"korName");
			$email=	 mysql_result($azRecord,0,"eMail");
			$temp_array=	  explode( '@', $email );
			$email_id=$temp_array[0];

		}else{	}


		$this->assign('korName',$korName);
		$this->assign('email',$email);
		$this->assign('email_id',$email_id);


		/*
		$this->assign('memberID',$memberID);
		$sql="SELECT korName, eMail from member_tbl where MemberNo = '$memberID'";
		$re = mysql_query($sql);
		while($re_row = mysql_fetch_array($re)){
			$this->assign('korName',$re_row[korName]);
			$email = explode( '@', $re_row[eMail] );
			$this->assign('email_id',$email[0]);
			$this->assign('email',$re_row[eMail]);
		}
		$this->display("intranet/common_contents/work_myInfo/email_pw_edit.tpl");
		*/




		$this->assign('MemberNo',$MemberNo);
		$this->assign('tabmenu_kd',$tabmenu_kd);



		$this->display("intranet/common_contents/work_myInfo/menu_pw_edit.tpl");
		/*--------------------------------------------------*/
	}//MOVE_PW_MANAGE
	/* ------------------------------------------------------------------------------ */



	function DB_PW_MANAGE(){
		global $db;
		extract($_REQUEST);
		//if($tabmenu_kd==""){$tabmenu_kd="intranet";}

		$db_go = true; // true false
		//$db_go = false; // test

		if($SubAction=="save"){
			if( $tabmenu_kd=="intranet" || $tabmenu_kd=="user" ){
				if($tabmenu_kd == "intranet"){ //로그인 비밀번호
					$sql=" UPDATE member_tbl SET
					Pasword='$set_pw'
					WHERE
					memberNo='$MemberNo'";
				}else if($tabmenu_kd=="user"){	//개인정보 비밀번호
					$sql=" UPDATE member_tbl SET
					UserInfoPW='$set_pw'
					WHERE
					memberNo='$MemberNo'";
				}

				if($db_go ==true){
					$re=mysql_query($sql,$db);
					echo "1";
				}else{
					echo $sql;
				}

			} else if( $tabmenu_kd == "email" ){
				//이메일
				//비밀번호에 # 포함되어있을경우 urlencode로 변경해서 전송
				$email_pw = str_replace('#' , '%23', $email_pw);

				$service_full_url = 'http://mail.samaneng.com/admin/xml_user_control.php';
				$service_full_url = $service_full_url . ('?cmd=user_modify');
				$service_full_url = $service_full_url . ('&userinfo[name]=' . $korName);
				$service_full_url = $service_full_url . ('&userinfo[id]=' . $email_id);
				$service_full_url = $service_full_url . ('&userinfo[mbox_host]=' . "samaneng.com");
				$service_full_url = $service_full_url . ('&userinfo[passwd]=' . $email_pw);
				$service_full_url = $service_full_url . ('&userinfo[common]=N');

				record_log('Myinfo', $memberID, $service_full_url);

				if($email_id == 'mjjeong1'){
					echo $service_full_url;
				}

				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $service_full_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				$response = curl_exec($ch);

				$errno = curl_errno($ch);
				if ($errno > 0) {
					if ($errno === 28) {
					  echo "Connection timed out.";
					}
					else {
					  echo "ERROR #" . $errno . ": " . curl_error($ch);
					}

					exit(0);
				}

				if (!$response) {	//빈값을때
					echo "ERROR - 1";
					exit(0);
				}

				//echo $response;

				$json_list = XML_To_JSON_Parse($response);	//function_intranet.php

				if (!$json_list) {	//xml이 비어있을때
					echo "ERROR - 2";
					exit(0);
				}

				$json_list= json_decode($json_list, true);
				curl_close($ch);

				if (!$json_list) {
					echo "ERROR - 3";
					exit(0);
				}

				//if(strcmp($json_list['result_code'],'-1') != 0 ) {
				if($json_list['result_code'] == '-1') {
					echo $json_list["msg"];
					//var_dump($json_list);
					//return 0; //success
				}else{
					//var_dump($json_list);
				}
			}
		}
	}//DB_PW_MANAGE

	/* ------------------------------------------------------------------------------ */
	function UserPWChange(){
		global $db;
		extract($_REQUEST);
		if($tabmenu==""){$tabmenu="intLogin";}

		if($SubAction=="save")
		{
			if($tabmenu=="intLogin")
			{
				$sql="UPDATE
				member_tbl
				SET
				Pasword='$newPW'
				WHERE
				memberNo='$memberID'";

			}
			elseif($tabmenu="userInfo")
			{
				$sql="UPDATE
				member_tbl
				SET
				UserInfoPW='$newPW'
				WHERE
				memberNo='$memberID'";
			}

			$re=mysql_query($sql,$db);
			echo $re;

			//echo $sql;

		}
		elseif($SubAction=="")
		{
			if($tabmenu=="emailPW")
			{
				$sql="SELECT korName,eMail FROM member_tbl WHERE memberNo='$memberID'";
				$re=mysql_query($sql,$db);
				$renum=mysql_num_rows($re);
				if($renum>0){
					$korName=mysql_result($re,0,'korName');
					$email_id=mysql_result($re,0,'eMail');
				}

			}
			$this->assign('tabmenu',$tabmenu);
			$this->assign('memberID',$memberID);
			$this->assign('korName',$korName);
			$this->assign('email_id',$email_id);
			$this->display("intranet/common_contents/work_myInfo/menu_pw_edit.tpl");
		}
	}

	function user_status_check($memberID){
		global $db;

		$Status    = "1";
		$StartTime = date("H:i");
		$Today     = date("Y-m-d");

		/* ----------------------------------- */
		if($StartTime < "06:00") {           /// 6시 이전은 어제날짜로 처리
			$Today = find_lastday($Today);
		}
		/* ----------------------------------- */
		$FiveDay= find_sevendays1($Today);   /// 오늘날짜에서 5일전 구하는곳
		/* ----------------------------------- */
		$sql = " SELECT															";
		$sql.= "	 a.EntryTime as EntryTime_full								";	//업무시작 시간
		$sql.= "	,DATE_FORMAT(a.EntryTime, '%Y-%m-%d') as EntryTime			";	//업무시작 시간
		$sql.= "	,(cast((DATE_FORMAT(a.EntryTime,'%H:%i:%s')) as char))  as EntryTime_hms		";	//시분초 00:00:00  /*2015-01-09추가*/
		$sql.= "	,DATE_FORMAT(a.OverTime, '%Y-%m-%d')  as OverTime			";	//연장근무시작 시간
		$sql.= "	,DATE_FORMAT(a.LeaveTime, '%Y-%m-%d') as LeaveTime			";	//업무종료 시간

 		$sql.= "	,a.EntryPCode												";	//프로젝트코드
 		$sql.= "	,a.EntryJobCode												";	//프로젝트서브코드
 		//$sql.= "	,b.ProjectNickname as ProjectNickname						";	//프로젝트 닉네임
		$sql.= "	,(select bb.ProjectNickname from Project_tbl bb where a.EntryPCode = replace(bb.ProjectCode, 'XX','".$shortY."') LIMIT 1) as ProjectNickname 	";	//프로젝트 닉네임
 		$sql.= "	,a.EntryJob													";	//업무내용

 		$sql.= "	,a.OverWorkIP												";	//
 		$sql.= "	,a.EndWorkIP												";	//

		$sql.= "	,a.LeavePCode                         						";	//프로젝트코드
		$sql.= "	,a.LeaveJobCode                       						";	//프로젝트서브코드
		$sql.= "	,(select  bb.ProjectNickname from Project_tbl bb where a.LeavePCode = replace(bb.ProjectCode, 'XX','".$shortY."') LIMIT 1) as ProjectNickname2 	";	//프로젝트 닉네임
		$sql.= "	,a.LeaveJob                           						";	//업무내용

		$sql.= " from															";
		$sql.= " (																";
		$sql.= "	select * from dallyproject_tbl								";
		$sql.= "	where														";
		$sql.= "	MemberNo = '".$memberID."'									";
		//$sql.= "	and EntryTime > '".$FiveDay." 00:00:00'						";
		$sql.= "	and EntryTime < '".$Today." 23:59:59'						";
		$sql.= "	order by EntryTime Desc limit 1								";
		$sql.= " ) a															";
		
		/* ----------------------------------- */
		$result = mysql_query($sql,$db);
		$result_num = mysql_num_rows($result);

		if($result_num != 0) {
			/* ------------------------------------------------------------- */
			//WP = WorkPlan

			$OverWorkIP			= mysql_result($result,0,"OverWorkIP"); 		//연장근무시작 IP
			$EndWorkIP			= mysql_result($result,0,"EndWorkIP"); 			//업무종료 IP

			$EntryTime_full     = mysql_result($result,0,"EntryTime_full"); 	//업무시작 시간
			$EntryTime			= mysql_result($result,0,"EntryTime"); 			//업무시작 시간
			$OverTime			= mysql_result($result,0,"OverTime");			//연장근무시작 시간
			$LeaveTime			= mysql_result($result,0,"LeaveTime");			//업무종료시작 시간

			$code_EntryPCode	= mysql_result($result,0,"EntryPCode");			//프로젝트코드
			$EntryJobCode		= mysql_result($result,0,"EntryJobCode");		//프로젝트서브코드
			$ProjectNickname	= mysql_result($result,0,"ProjectNickname");	//프로젝트 닉네임
			$EntryJob			= mysql_result($result,0,"EntryJob");			//업무내용

			$code_EntryPCode2	= mysql_result($result,0,"LeavePCode");			//프로젝트코드
			$EntryJobCode2		= mysql_result($result,0,"LeaveJobCode");		//프로젝트서브코드
			$ProjectNickname2	= mysql_result($result,0,"ProjectNickname2");	//프로젝트 닉네임
			$EntryJob2			= mysql_result($result,0,"LeaveJob");			//업무내용

			$EntryTime_hms = mysql_result($result,0,"EntryTime_hms"); //시분초 00:00:00  2015-01-09추가


			/* 업무미시작------------------------------------------------------------- */
			if($Today != $EntryTime && $Today != $OverTime && $Today != $LeaveTime ){
				$Status="1";		//업무시작X : 연장근무X : 업무종료X
				$Status_detail="업무시작X : 연장근무X : 업무종료X";
			} //if End
			/* 업무시작------------------------------------------------------------- */
			if($Today == $EntryTime && $Today != $OverTime && $Today != $LeaveTime){
				$Status="2";		//업무시작0 : 연장근무X : 업무종료X
				$Status_detail="업무시작0 : 연장근무X : 업무종료X";
			} //if End
			/* 업무시작 후 연장근무 중인 상태 ------------------------------------------------------------- */
			if($Today == $OverTime && $Today == $OverTime && $Today != $LeaveTime){ //연장근무0 : 업무종료 X
				$Status="3";		//업무시작0 : 연장근무0 : 업무종료X
				$Status_detail="업무시작0 : 연장근무0 : 업무종료X";
			} //if End
			/*업무시작 후 연장근무하고 업무종료 상태 -------------------------------------------------------- */
			if($Today == $EntryTime && $Today == $OverTime && $Today == $LeaveTime  ){
				$Status="4";   //업무시작0 : 연장근무0 : 업무종료0
				$Status_detail="업무시작0 : 연장근무0 : 업무종료0";
			} //if End
			/*업무시작 후 업무종료상태(연장근무안함) ---------------------------------------------------------- */
			if($Today == $EntryTime && $Today != $OverTime && $Today == $LeaveTime  ){
				$Status="5";   //업무시작0 : 연장근무X : 업무종료0
				$Status_detail="업무시작0 : 연장근무X : 업무종료0";
			} //if End

			/* 2015-01-09추가 Start*** */
			if( (($Today == $EntryTime && $EntryTime_hms=="00:00:00")&& $Today != $OverTime && $Today != $LeaveTime ) ) {
				$Status="6";   //업무시작0 : 연장근무X : 업무종료0
				$Status_detail="업무시작가능 : 출장 및 기타사유로 EntryTime에 현재날짜와 시분초가 00:00:00 로 임의 저장되어 있는 상태, 업무시작X : 연장근무X : 업무종료X";
			} //if End

		} //if End
		// echo $sql;
		return $Status;
	}



















}//class NoticeLogic ENd /////////////////

