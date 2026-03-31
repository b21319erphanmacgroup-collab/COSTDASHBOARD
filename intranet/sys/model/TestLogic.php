<?php
	session_start();
	//스마티 설정파일 *****************************************
	include "../inc/dbcon.inc";
	include "../inc/function_intranet.php";
	include "../../../SmartyConfig.php";
	//include "../inc/getCookieOfUser.php";
	include "../util/HanamcPageControl.php";
	include "../inc/setMH.php";	//Man hour 입력 함수

	require_once($SmartyClassPath);
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
	}else if($_COOKIE['CK_memberID']!=""){				//쿠키값 유무확인
		/* SET COOKIE --------------------------------- */
		$MemberNo   =   $_COOKIE['CK_memberID'];	//사원번호
		$memberID	=   $_COOKIE['CK_memberID'];	//사원번호

		$CompanyKind=	$_COOKIE['CK_CompanyKind'];	//회사코드(장헌산업:JANG,파일테크:PILE,바론컨설턴트:HANM)
		$korName	=	$_COOKIE['CK_korName'];		//한글이름
		$RankCode	=	$_COOKIE['CK_RankCode'];	//직급코드
		$GroupCode	=	$_COOKIE['CK_GroupCode'];	//부서코드
		$SortKey	=	$_COOKIE['CK_SortKey'];		//직급+부서코드
		$EntryDate	=	$_COOKIE['CK_EntryDate'];	//입사일자
		$position	=	$_COOKIE['CK_position'];	//직위명
		$GroupName	=	$_COOKIE['CK_GroupName'];	//부서명
	}else{
		/* ----------------------------------- */
		$memberID	=	$_GET['memberID'];
		$MemberNo	=	$memberID;
		$MemberNo	=	strtoupper($MemberNo);
		/* ----------------------------------- */
		require('../../sys/popup/setInfo.php');
		/* ----------------------------------- */
	}//if End
	/* ----------------------------------- */
	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
	/* ----------------------------------- */

		$date_today  = date("Y-m-d");				// 오늘날짜 년월일      : yyyy-mm-dd

		$today = $date_today;

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
		$nowHour	 = date("H");					// 현재 시
		$nowMin		 = date("i");					// 현재 분
		$nowTime	 = $nowHour.":".$nowMin;		// 현재 시:분
		/* 수정을 위해 넘어온 GET VALUE****************************** */
		/* get 파라미터 한글깨짐 방지----------------------------------- */
		/*$edit_main_p_code	= iconv('euc-kr', 'utf-8', $_GET['main_p_code']); */

		/* ************************************************************** */
?>
<?php
	/* *********************************** */
	$edit_main_p_code	= $_GET['aaa'];
	$edit_main_sub_code	= $_GET['bbb'];
	$edit_main_p_name	= $_GET['ccc'];
	/* ----------------------------------- */
?>
<?php


class TestLogic {
	// 생성자
	var $smarty;

	function TestLogic($smarty)
	{
		$this->smarty=$smarty;
	}


	function ListPage()
	{
	global $db;
	//global $MemberNo;

	//$this->smarty->assign('query_data',$query_data);
	$this->smarty->display("intranet/common_contents/work_test/listPage.tpl");

	}// ListPage End


/************************************************/
	/* ------------------------------------------------------------------------------ */
	function GoCookiePage()//쿠키값 확인
	{
		global $PHPSESSID;

		global $CompanyKind;
		global $CK_memberID;
		global $CK_korName;
		global $CK_RankCode;
		global $CK_GroupCode;
		global $CK_SortKey;
		global $CK_EntryDate;
		global $CK_position;
		global $CK_GroupName;
		global $user_id;

		$companyIP = "";
	if(array_key_exists('SERVER_ADDR', $_SERVER))
		$companyIP = $_SERVER['SERVER_ADDR'];
	elseif(array_key_exists('LOCAL_ADDR', $_SERVER))
		$companyIP = $_SERVER['LOCAL_ADDR'];
	elseif(array_key_exists('SERVER_NAME', $_SERVER))
		$companyIP = gethostbyname($_SERVER['SERVER_NAME']);
	else{
		$companyIP ="미확인된  IP";
	}//if End

		$this->smarty->assign('companyIP',$companyIP);

		$this->smarty->assign('PHPSESSID',$PHPSESSID);

		$this->smarty->assign('CompanyKind',$CompanyKind);
		$this->smarty->assign('CK_memberID',$CK_memberID);
		$this->smarty->assign('CK_korName',$CK_korName);
		$this->smarty->assign('CK_RankCode',$CK_RankCode);
		$this->smarty->assign('CK_GroupCode',$CK_GroupCode);
		$this->smarty->assign('CK_SortKey',$CK_SortKey);
		$this->smarty->assign('CK_EntryDate',$CK_EntryDate);
		$this->smarty->assign('CK_position',$CK_position);
		$this->smarty->assign('CK_GroupName',$CK_GroupName);
		$this->smarty->assign('user_id',$user_id);

		$this->smarty->display("intranet/common_contents/work_test/goCookiePage.tpl");
	}// InsertPage End
	/* ------------------------------------------------------------------------------ */

	/* ------------------------------------------------------------------------------ */
	function GoSessionPage() //세션값 확인
	{

		global $memberID;
		$companyIP = "";
	if(array_key_exists('SERVER_ADDR', $_SERVER))
		$companyIP = $_SERVER['SERVER_ADDR'];
	elseif(array_key_exists('LOCAL_ADDR', $_SERVER))
		$companyIP = $_SERVER['LOCAL_ADDR'];
	elseif(array_key_exists('SERVER_NAME', $_SERVER))
		$companyIP = gethostbyname($_SERVER['SERVER_NAME']);
	else{
		$companyIP ="미확인된  IP";
	}//if End


$SS_memberID	= $_SESSION['SS_memberID'];  	//사원번호
$SS_korName		= $_SESSION['SS_korName'];   	//한글이름
$SS_RankCode	= $_SESSION['SS_RankCode'];  	//직급코드
$SS_GroupCode	= $_SESSION['SS_GroupCode']; 	//부서코드
$SS_SortKey		= $_SESSION['SS_SortKey'];   	//직급+부서코드
$SS_EntryDate	= $_SESSION['SS_EntryDate']; 	//입사일자
$SS_position	= $_SESSION['SS_position'];  	//직위명
$SS_GroupName	= $_SESSION['SS_GroupName']; 	//부서명


	$this->smarty->assign('aaa',"aaa");

		$this->smarty->assign('memberID',$memberID);

		$this->smarty->assign('companyIP',$companyIP);


		$this->smarty->assign('SS_memberID',$SS_memberID);
		$this->smarty->assign('SS_korName',$SS_korName);
		$this->smarty->assign('SS_RankCode',$SS_RankCode);
		$this->smarty->assign('SS_GroupCode',$SS_GroupCode);
		$this->smarty->assign('SS_SortKey',$SS_SortKey);
		$this->smarty->assign('SS_EntryDate',$SS_EntryDate);
		$this->smarty->assign('SS_position',$SS_position);
		$this->smarty->assign('SS_GroupName',$SS_GroupName);



		$this->smarty->display("intranet/common_contents/work_test/goSessionPage.tpl");
	}// InsertPage End
	/* ------------------------------------------------------------------------------ */


	/* ------------------------------------------------------------------------------ */
	function GoGetParamPage() //GET값 확인
	{

		global $memberID;
		global $db;

		require_once('../popup/setInfo.php');


		$this->smarty->assign('MemberNo',$MemberNo);//사원번호

		$this->smarty->assign('CompanyKind',$CompanyKind);//회사코드
		$this->smarty->assign('korName',$korName);		//한글이름
		$this->smarty->assign('RankCode',$RankCode);		//직급코드
		$this->smarty->assign('GroupCode',$GroupCode);	//부서코드
		$this->smarty->assign('SortKey',$SortKey);//직급코드+부서코드
		$this->smarty->assign('EntryDate',$EntryDate);//입사일자
		$this->smarty->assign('position',$position);//직위명
		$this->smarty->assign('GroupName',$GroupName);//부서명
		$this->smarty->assign('ExtNo',$ExtNo);//부서명



		$this->smarty->display("intranet/common_contents/work_test/goGetParamPage.tpl");
	}// GoGetParamPage End
	/* ------------------------------------------------------------------------------ */










	/* ------------------------------------------------------------------------------ */
	function JsonTest()
	{
	global $db;

		$sql=     "	SELECT						";
		$sql=$sql."		 MemberNo				";
		$sql=$sql."		,Pasword				";
		$sql=$sql."		,korName				";
		$sql=$sql."		,RankCode				";
		$sql=$sql."		,GroupCode				";
		$sql=$sql."		,WorkPosition			";
		$sql=$sql."		,chiName				";
		$sql=$sql."		,engName				";
		$sql=$sql."		,Degree					";
		$sql=$sql."		,Technical				";
		$sql=$sql."		,ExtNo					";
		$sql=$sql."		,EntryDate				";
		$sql=$sql."		,LeaveDate				";
		$sql=$sql."		,JuminNo				";
		$sql=$sql."		,Phone					";
		$sql=$sql."		,Mobile					";
		$sql=$sql."		,eMail					";
		$sql=$sql."		,OrignAddress			";
		$sql=$sql."	FROM 						";
		$sql=$sql."		member_tbl				";
		$sql=$sql."	LIMIT 1,10";




$data = array();

		$re = mysql_query($sql,$db);
$i=0;
	while($re_row = mysql_fetch_array($re)) {

		$data['data'][$i] = array('id'=>$re_row[MemberNo], 'name'=>$re_row[korName]);

$i++;
	} //while End

/*
$data['data'][0] = array('id'=>'test1', 'name'=>'테스트1');
$data['data'][1] = array('id'=>'test2', 'name'=>'테스트2');
$data['data'][2] = array('id'=>'test3', 'name'=>'테스트3');
$data['data'][3] = array('id'=>'test4', 'name'=>'테스트4');
$data['data'][4] = array('id'=>'test5', 'name'=>'테스트5');
*/

echo json_encode($data); //php배열을 json 형태로 변경해주는 php 내장함수 입니다.



	}// JsonTest End
/************************************************/


	function JsonPage()
	{
	global $db;
	//global $MemberNo;

	//$this->smarty->assign('query_data',$query_data);


$aaa ="<td>1234</td>";
$aaa =htmlspecialchars($aaa, ENT_QUOTES,"UTF-8");

$bbb ="<td>1234</td>";//output : <td>1234</td>

echo $aaa;
//OUTPUT : &lt;td&gt;
echo $bbb;
//OUTPUT : <td>1234</td>



$this->smarty->assign('aaa',$aaa);
$this->smarty->assign('bbb',$bbb);

	$this->smarty->display("intranet/common_contents/work_test/jsonPage.tpl");

	}// JsonPage End



	/* ------------------------------------------------------------------------------ */
	function InsertPage()
	{
	global $db;
	//global $MemberNo;

	//$this->smarty->assign('query_data',$query_data);
	$this->smarty->display("intranet/common_contents/work_test/insertPage.tpl");

	}// InsertPage End
	/* ------------------------------------------------------------------------------ */


	/* ------------------------------------------------------------------------------ */
	function PageList()
	{
	global $MemberNo;

	global $db;
	global $start;
	global $page;
	global $currentPage;

	global $last_page;
	global $sub_index;

	$searchKind		= ($_GET['searchKind']==""?"":$_GET['searchKind']);

//echo $searchKind."<br>";

	$searchStr		= ($_GET['searchStr']==""?"":$_GET['searchStr']);

//echo $searchStr."<br>";


	$addQuery01 = "";
	$addQuery02 = "";

	if($searchKind==""){
		$addQuery01 = "";
		$addQuery02 = "	ORDER BY korName			";

	}else if($searchKind==1){// searchKind : 이름 : 1
		$addQuery01 = " WHERE korName like '%".$searchStr."%' ";
		$addQuery02 = "	ORDER BY korName			";

	}else if($searchKind==2){// searchKind : 사원번호 : 2
		$addQuery01 = " WHERE MemberNo like '%".$searchStr."%' ";
		$addQuery02 = "	ORDER BY MemberNo			";

	}else{
		$addQuery01 = "";
		$addQuery02 = "	ORDER BY korName			";

	}
	/*-----------------------------------------------*/
	$page=15; //한페이지에 표시될 로우의 갯수

	if($currentPage==""){
		$start = 0;
		$currentPage = 1;
	}else{
		$start=$page*($currentPage-1);
	}
	$query_data = array();

		$sql_count  = "select COUNT(*) CNT from member_tbl  ";
		$re         = mysql_query($sql_count);
		$re_count = mysql_result($re,0,"CNT");

		$TotalRow   = $re_count;              //총 개수 저장
		//마지막페이지
		$last_start = ceil($TotalRow/10)*10+1;
		$last_page  = ceil($TotalRow/$page);

		//$sql="select * from member_tbl order by MemberNo limit ".$start." , ".$page;

		$sql=     "	SELECT						";
		$sql=$sql."		 MemberNo				";
		$sql=$sql."		,Pasword				";
		$sql=$sql."		,korName				";
		$sql=$sql."		,RankCode				";
		$sql=$sql."		,GroupCode				";
		$sql=$sql."		,WorkPosition			";
		$sql=$sql."		,chiName				";
		$sql=$sql."		,engName				";
		$sql=$sql."		,Degree					";
		$sql=$sql."		,Technical				";
		$sql=$sql."		,ExtNo					";
		$sql=$sql."		,EntryDate				";
		$sql=$sql."		,LeaveDate				";
		$sql=$sql."		,JuminNo				";
		$sql=$sql."		,Phone					";
		$sql=$sql."		,Mobile					";
		$sql=$sql."		,eMail					";
		$sql=$sql."		,OrignAddress			";
		$sql=$sql."	FROM 						";
		$sql=$sql."		member_tbl				";

		$sql=$sql.$addQuery01;

		$sql=$sql.$addQuery02;


		$sql=$sql."	LIMIT ".$start." , ".$page."";




/////////////////
echo "02::".$sql."<br>";
/////////////////

		$re = mysql_query($sql,$db);

	while($re_row = mysql_fetch_array($re)) {
		array_push($query_data,$re_row);

	} //while End


	/* 페이지네비 관련SET Start ------------------- */
	$PageHandler = new PageControl($this->smarty);
	$PageHandler-> SetMaxRow($TotalRow);
	$PageHandler-> SetCurrentPage($currentPage);
	$PageHandler-> PutTamplate();
	/* 페이지네비 관련SET End ------------------- */

	$this->smarty->assign("page_action","test_controller.php");


	$this->smarty->assign('memberID',$memberID);
	$this->smarty->assign('GroupCode',$GroupCode);



	$this->smarty->assign('start',$start);
	$this->smarty->assign('TotalRow',$TotalRow);
	$this->smarty->assign('last_start',$last_start);
	$this->smarty->assign('last_page',$last_page);
	$this->smarty->assign('currentPage',$currentPage);

	$this->smarty->assign('sub_index',$sub_index);

	$this->smarty->assign('query_data',$query_data);

	$this->smarty->display("intranet/common_contents/work_test/testList.tpl");
	}  //LunchPop() End
	/* ------------------------------------------------------------------------------ */

























	/* ------------------------------------------------------------------------------ */
	function TestInsertPop()
	{
	$this->smarty->display("intranet/common_contents/work_test/testPop.tpl");
	}  //LunchPop() End
	/* ------------------------------------------------------------------------------ */
	function TestInsert()  // DB실행
	{
	$test01;
	$test02;
	$test03;
	$test01  =	$_POST['test01'];
	$test02  =	$_POST['test02'];
	$test03  =	$_POST['test03'];
	/* ------------------------------------------------------------------------------ */
		$lunch_sql= " UPDATE lunch_menu_tbl SET									";
		$lunch_sql= $lunch_sql."  menu_main	='".$edit_lunch_menu_main[$j]."'	";
		$lunch_sql= $lunch_sql." ,menu_sub	='".$edit_lunch_menu_sub[$j]."'		";
		$lunch_sql= $lunch_sql." WHERE											";
		$lunch_sql= $lunch_sql." menu_num = '".$edit_lunch_menu_num[$j]."'		";
		////////////////////////
		//mysql_query($lunch_sql);
		////////////////////////

echo $test01.$test02.$test03;  //DB작업 성공

//echo "1";  //DB작업 성공
	}  //End
	/* ------------------------------------------------------------------------------ */

	function test_mh(){
		mh_update( 'M19312', '20191101' );
	}

	function test_node(){
		$this->smarty->display("intranet/common_contents/work_test/test_node.tpl");
	}



	//////////////////////////////////////
	//=============================================================================
	//기    능 : 인원현황(가족사)test
	//관 련 DB :
	//프로시져 :
	//기    타 :
	//=============================================================================
	//////////////////////////////////////
	function PlanningHr7()
	{
		extract($_REQUEST);

		if($this->excel != "" ){
		}else{

			switch($MainAction){
				case "Ajax_01": $this->PlanningHr7_Ajax_01();	break;
				case "Ajax_02": $this->PlanningHr7_Ajax_02();	break;
				case "Ajax_03": $this->PlanningHr7_Ajax_03();	break;
				default:
					$this->smarty->display("intranet/common_contents/work_test/PlanningHr7_Main.tpl");
					break;
			}
		}
	}

	function PlanningHr7_Ajax_01($mode=true){
		global $db;
		global $ActionMode, $planning_user_id,$excel;

		extract($_REQUEST);
		if($input_select_02=='1'){
			$input_select_02r= "working_comp";
		}elseif($input_select_02=='2'){
			$input_select_02r = "belong_comp";
		}

		switch($SubAction){
			case "select":
				switch($input_radio_01){
					case "1":
						$sql  = "select
								rank_name as 'item01'
								,sum(case $input_select_02r when '20' then 1 else 0 end) as 'item02'
								,sum(case $input_select_02r when '10' then 1 else 0 end) as 'item03'
								,sum(case $input_select_02r when '40' then 1 else 0 end) as 'item04'
								,sum(case $input_select_02r when '60' then 1 else 0 end) as 'item05'
								,sum(case $input_select_02r when '50' then 1 else 0 end) as 'item06'
								,sum(case $input_select_02r when '30' then 1 else 0 end) as 'item07'
								,sum(1) as 'item08'
								from (
									select
									sys_comp_code
									,(CASE $input_select_02r
										 WHEN 30 THEN 20
										 WHEN 99 THEN sys_comp_code
										 ELSE $input_select_02r
										 end) as $input_select_02r
									,rank_name, rank_code
									from total_member_tbl
									where work_position in ('1','2')
								)x group by rank_name order by rank_code";
						break;
					case "2":
						$sql  = "select
								graduate_last as 'item01'
								,sum(case $input_select_02r when '20' then 1 else 0 end) as 'item02'
								,sum(case $input_select_02r when '10' then 1 else 0 end) as 'item03'
								,sum(case $input_select_02r when '40' then 1 else 0 end) as 'item04'
								,sum(case $input_select_02r when '60' then 1 else 0 end) as 'item05'
								,sum(case $input_select_02r when '50' then 1 else 0 end) as 'item06'
								,sum(case $input_select_02r when '30' then 1 else 0 end) as 'item07'
								,sum(1) as 'item08'
								from (
									select
									sys_comp_code
									,(CASE $input_select_02r
										 WHEN 30 THEN 20
										 WHEN 99 THEN sys_comp_code
										 ELSE $input_select_02r
										 end) as $input_select_02r
									,(case graduate_last
										when 0010 then '고졸'
										when 0020 then '초대졸'
										when 0030 then '대졸'
										when 0040 then '석사'
										when 0050 then '박사'
										when '' then '-' else graduate_last end) as graduate_last
									from total_member_tbl
									where work_position in ('1','2')
								)x group by graduate_last";
						break;
					case "3":
						$sql  = "select
								age as 'item01'
								,sum(case $input_select_02r when '20' then 1 else 0 end) as 'item02'
								,sum(case $input_select_02r when '10' then 1 else 0 end) as 'item03'
								,sum(case $input_select_02r when '40' then 1 else 0 end) as 'item04'
								,sum(case $input_select_02r when '60' then 1 else 0 end) as 'item05'
								,sum(case $input_select_02r when '50' then 1 else 0 end) as 'item06'
								,sum(case $input_select_02r when '30' then 1 else 0 end) as 'item07'
								,sum(1) as 'item08'
								from (
									select
									sys_comp_code
									,(CASE $input_select_02r
										 WHEN 30 THEN 20
										 WHEN 99 THEN sys_comp_code
										 ELSE $input_select_02r
										 end) as $input_select_02r
									,(case
										truncate(substr(CURDATE(),1,4) - (if(substr(jumin_no, 8, 1) = '1' or substr(jumin_no, 8, 1) = '2' or
										substr(jumin_no, 8, 1) = '3' or substr(jumin_no, 8, 1) = '5' or substr(jumin_no, 8, 1) = '6', 1900, 2000) + left(jumin_no, 2)) + 1,-1)
										when 10 then '10대'
										when 20 then '20대'
										when 30 then '30대'
										when 40 then '40대'
										when 50 then '50대'
										when 60 then '60대'
										when 70 then '70대'
										when 80 then '80대'
										when 90 then '90대'else '기타' end) as age
									from total_member_tbl
									where work_position in ('1','2')
								)x group by item01 order by item01";
						break;
					case "4":
						$sql  = "select
								item01 as 'item01'
								,sum(case $input_select_02r when '20' then 1 else 0 end) as 'item02'
								,sum(case $input_select_02r when '10' then 1 else 0 end) as 'item03'
								,sum(case $input_select_02r when '40' then 1 else 0 end) as 'item04'
								,sum(case $input_select_02r when '60' then 1 else 0 end) as 'item05'
								,sum(case $input_select_02r when '50' then 1 else 0 end) as 'item06'
								,sum(case $input_select_02r when '30' then 1 else 0 end) as 'item07'
								,sum(1) as 'item08'
								from (
									select
									sys_comp_code
									,(CASE $input_select_02r
										 WHEN 30 THEN 20
										 WHEN 99 THEN sys_comp_code
										 ELSE $input_select_02r
										 end) as $input_select_02r
									,(case when mod(substr(jumin_no,8,1),2) = 1 then '남'
										   when mod(substr(jumin_no,8,1),2) = 0 then '여'
										   else '기타' end ) as item01
									from total_member_tbl
									where work_position in ('1','2')
								)x group by item01 order by item01";
						break;
					default:
						break;
				}

				//echo "input_select_01:".$input_select_01."<br>";
				//echo "start_date:".$start_date."<br>";
				//echo $presql."<BR>";
				//echo $sql."<BR>";
				$re = mysql_query($sql);
				$full_data = array();
				$list_data = array();
				$list_cnt = 0;

				$item02_sum = 0;$item03_sum = 0;$item04_sum = 0;$item05_sum = 0;
				$item06_sum = 0;$item07_sum = 0;$item08_sum = 0;
				while($re_row = mysql_fetch_array($re))
				{
					array_push($list_data,$re_row);

					$item02_sum += $list_data[$list_cnt][1];
					$item03_sum += $list_data[$list_cnt][2];
					$item04_sum += $list_data[$list_cnt][3];
					$item05_sum += $list_data[$list_cnt][4];
					$item06_sum += $list_data[$list_cnt][5];
					$item07_sum += $list_data[$list_cnt][6];
					$item08_sum += $list_data[$list_cnt][7];
					$list_cnt++;
					//echo $list_cnt;
				}
				//===================================================================

				//===================================================================

				$full_data["sum"] = array(
					"item01" => "총 ".count($list_data)."건",
					"item02" => $item02_sum,
					"item03" => $item03_sum,
					"item04" => $item04_sum,
					"item05" => $item05_sum,
					"item06" => $item06_sum,
					"item07" => $item07_sum,
					"item08" => $item08_sum
				);
				$full_data["list_data"] = $list_data;

				echo json_encode($full_data);
				break;
				//----------------------------------------------------------------------------

			case "DataSync":
				//--------------------------------------------
				echo 1;
				break;
				//--------------------------------------------------------------------------------------------
			default:
				echo "선택값이 불분명합니다.";
				exit();
				break;
		}
	}


	function PlanningHr7_Ajax_02($mode=true){
		global $db;
		global $ActionMode, $planning_user_id,$excel;

		extract($_REQUEST);
		switch($SubAction){
			case "select":
				//근무회사1      , 소속회사2
				//working_comp , belong_comp
				if($input_select_02=='1'){
					$input_select_02r= "working_comp";
				}elseif($input_select_02=='2'){
					$input_select_02r = "belong_comp";
				}

				$sql = "
						select
						(case sys_comp_code
							when 20 then '한맥'
							when 10 then '삼안'
							when 40 then '장헌'
							when 60 then 'PTC'
							when 50 then '한라'
							when 30 then '바론' else '' end) as companyname
						,dept_name as DeptName
						,kor_name as KorName
						,level_name as LevelCode
						,rank_name as PositionCode
						,substr(CURDATE(),1,4) - (if(substr(jumin_no, 8, 1) = '1' or substr(jumin_no, 8, 1) = '2' or substr(jumin_no, 8, 1) = '3' or substr(jumin_no, 8, 1) = '5' or substr(jumin_no, 8, 1) = '6', 1900, 2000) + left(jumin_no, 2)) + 1 as age
						,(case graduate_last
							when 0010 then '고졸'
							when 0020 then '초대졸'
							when 0030 then '대졸'
							when 0040 then '석사'
							when 0050 then '박사' else '-' end) as SchoolCar

						,'-기술직' as JobkindCode
						,'-연구직' as DutyCode

						,case when mod(substr(jumin_no,8,1),2) = 1 then '남'
								when mod(substr(jumin_no,8,1),2) = 0 then '여'
								else '기타' end  as sex
						,'2022-02-11'selectdate
						,member_id as MemberNo
						,jumin_no as JuminNo
						,retire_date as LeaveDate
						from total_member_tbl
				";

				$sql .= "where $input_select_02r = sys_comp_code and work_position in ('1','2') ";
				//print_R($sql);

				switch($input_radio_01){
					case "1":  //직업
						$sql .= " and rank_name like '$item01'
						";
						break;

					case "2":  //학력
						$sql .= " and
							(case graduate_last
							when 0010 then '고졸'
							when 0020 then '초대졸'
							when 0030 then '대졸'
							when 0040 then '석사'
							when 0050 then '박사' else '-' end) like '$item01'
						";
						break;

					case "3":  //연령
						if($item01=='기타'){
							$sql .= " and concat((truncate(substr(CURDATE(),1,4) - (if(substr(jumin_no, 8, 1) = '1' or substr(jumin_no, 8, 1) = '2' or substr(jumin_no, 8, 1) = '3' or substr(jumin_no, 8, 1) = '5' or substr(jumin_no, 8, 1) = '6', 1900, 2000) + left(jumin_no, 2)) + 1,-1)),'대') not in ('20대','30대','40대','50대','60대','70대','80대','90대')
							";
						}else{
							$sql .= " and concat((truncate(substr(CURDATE(),1,4) - (if(substr(jumin_no, 8, 1) = '1' or substr(jumin_no, 8, 1) = '2' or substr(jumin_no, 8, 1) = '3' or substr(jumin_no, 8, 1) = '5' or substr(jumin_no, 8, 1) = '6', 1900, 2000) + left(jumin_no, 2)) + 1,-1)),'대') like '$item01'
							";
						}
						break;


					case "4":  //성별
						if( $item01 == '남' ){
							$item01 = 1;
						}elseif( $item01 == '여' ){
							$item01 = 0;
						}else{
							$item01 = '%';
						}
						$sql .= "and
								mod(substr(jumin_no,8,1),2) like '$item01'
						";
						break;
						/*
						 from
						 family_member_status_tbl
						 where
						 mod(substr(juminno,8,1),2) like '$item01'
						 */
					default:
						break;
				}
				/*
				 $sql .= "
				 and DeptName like '$input_select_01'
				 and SelectDate = '$start_date'
				 order by companyname, DeptName
				 ";
				 */
				//$sql .= "limit 400;";
				$sql .= ";";

				//echo "input_select_01:".$input_select_01."<br>";
				//echo "start_date:".$start_date."<br>";
				//echo $presql."<BR>";
				//echo $sql."<BR>";
				//print_r($sql);
				//444444444444444444444444444
				$re = mysql_query($sql);
				$full_data = array();
				$list_data = array();
				$list_cnt = 0;
				while($re_row = mysql_fetch_array($re))
				{
					array_push($list_data,$re_row);
				}
				$full_data["sum"] = array(
					"companyname" => "총 ".count($list_data)."건"
				);
				$full_data["list_data"] = $list_data;

				echo json_encode($full_data);
				break;
				//----------------------------------------------------------------------------

			case "DataSync":
				//--------------------------------------------
				echo 1;
				break;
				//--------------------------------------------------------------------------------------------
			default:
				echo "선택값이 불분명합니다.";
				exit();
				break;
		}
	}


	function PlanningHr7_Ajax_03(){
		extract($_REQUEST);
		switch($SubAction){
			case "chartdiv_1":
				//근무회사1      , 소속회사2
				//working_comp , belong_comp
				if($input_select_02=='1'){
					$input_select_02r= "working_comp";
				}elseif($input_select_02=='2'){
					$input_select_02r = "belong_comp";
				}

				if( $input_radio_01 == '1' ){
					$sql  = "select
								(case ITEM_NAME
										when 20 then '한맥'
										when 10 then '삼안'
										when 40 then '장헌'
										when 60 then 'PTC'
										when 50 then '한라'
										when 30 then '바론' else sys_comp_code end) as ITEM_NAME
								,COUNT(ITEM_NAME) AS ITEM_CNT
								from (
									select
									sys_comp_code
									,(CASE $input_select_02r
										 WHEN 30 THEN 20
										 WHEN 99 THEN sys_comp_code
										 ELSE $input_select_02r
										 end) as ITEM_NAME
									from total_member_tbl
									where work_position in ('1','2')
									and rank_name like '$item01'
								)x group by ITEM_NAME";
				}elseif( $input_radio_01 == '2' ){
					$sql  = "select
								(case ITEM_NAME
										when 20 then '한맥'
										when 10 then '삼안'
										when 40 then '장헌'
										when 60 then 'PTC'
										when 50 then '한라'
										when 30 then '바론' else sys_comp_code end) as ITEM_NAME
								,COUNT(ITEM_NAME) AS ITEM_CNT
								from (
									select
									sys_comp_code
									,(CASE $input_select_02r
										 WHEN 30 THEN 20
										 WHEN 99 THEN sys_comp_code
										 ELSE $input_select_02r
										 end) as ITEM_NAME
									from total_member_tbl
									where work_position in ('1','2')
									and (case graduate_last
											when 0010 then '고졸'
											when 0020 then '초대졸'
											when 0030 then '대졸'
											when 0040 then '석사'
											when 0050 then '박사' else '-' end) like '$item01'
								)x group by ITEM_NAME";
				}elseif( $input_radio_01 == '3' ){
					$sql  = "select
								(case ITEM_NAME
										when 20 then '한맥'
										when 10 then '삼안'
										when 40 then '장헌'
										when 60 then 'PTC'
										when 50 then '한라'
										when 30 then '바론' else sys_comp_code end) as ITEM_NAME
								,COUNT(ITEM_NAME) AS ITEM_CNT
								from (
									select
									sys_comp_code
									,(CASE $input_select_02r
										 WHEN 30 THEN 20
										 WHEN 99 THEN sys_comp_code
										 ELSE $input_select_02r
										 end) as ITEM_NAME
									from total_member_tbl
									where work_position in ('1','2')";
						if($item01=='기타'){
							$sql .= " and concat((truncate(substr(CURDATE(),1,4) - (if(substr(jumin_no, 8, 1) = '1' or substr(jumin_no, 8, 1) = '2' or substr(jumin_no, 8, 1) = '3' or substr(jumin_no, 8, 1) = '5' or substr(jumin_no, 8, 1) = '6', 1900, 2000) + left(jumin_no, 2)) + 1,-1)),'대') not in ('20대','30대','40대','50대','60대','70대','80대','90대')
							";
						}else{
							$sql .= " and concat((truncate(substr(CURDATE(),1,4) - (if(substr(jumin_no, 8, 1) = '1' or substr(jumin_no, 8, 1) = '2' or substr(jumin_no, 8, 1) = '3' or substr(jumin_no, 8, 1) = '5' or substr(jumin_no, 8, 1) = '6', 1900, 2000) + left(jumin_no, 2)) + 1,-1)),'대') like '$item01'
							";
						}
						$sql .= "
						)x group by ITEM_NAME";

				}elseif( $input_radio_01 == '4' ){
					if( $item01 == '남' ){
						$item01 = 1;
					}elseif( $item01 == '여' ){
						$item01 = 0;
					}else{
						$item01 = '%';
					}
					$sql  = "select
								(case ITEM_NAME
										when 20 then '한맥'
										when 10 then '삼안'
										when 40 then '장헌'
										when 60 then 'PTC'
										when 50 then '한라'
										when 30 then '바론' else sys_comp_code end) as ITEM_NAME
								,COUNT(ITEM_NAME) AS ITEM_CNT
								from (
									select
									sys_comp_code
									,(CASE $input_select_02r
										 WHEN 30 THEN 20
										 WHEN 99 THEN sys_comp_code
										 ELSE $input_select_02r
										 end) as ITEM_NAME
									from total_member_tbl
									where work_position in ('1','2')
									and mod(substr(jumin_no,8,1),2) like '$item01'
						)x group by ITEM_NAME";
				}

				$list_data = array();
				$re = mysql_query($sql);
				while($re_row = mysql_fetch_array($re)){
					array_push($list_data,$re_row);
				}
				echo json_encode($list_data);
				break;
			case "chartdiv_2":
				if( $input_radio_01 == '1' ){
					if($input_select_02=='1'){
						$sql = "select (case working_rank_name
											when '' then rank_name
											else working_rank_name end) ITEM_NAME
								,count(case working_rank_name
											when '' then rank_name
											else working_rank_name end) as ITEM_CNT
								,(select count(*) from total_member_tbl where work_position in ('1','2')) AS ALL_CNT
								from total_member_tbl
								where work_position in ('1','2')
								group by working_rank_name
								order by working_rank_code ";
					}elseif($input_select_02=='2'){
						$sql = "select
								rank_name as ITEM_NAME
								,count(rank_name) as ITEM_CNT
								,(select count(*) from total_member_tbl where work_position in ('1','2')) AS ALL_CNT
								from total_member_tbl
								where work_position in ('1','2')
								group by rank_name
								order by rank_code";
					}
				}elseif( $input_radio_01 == '2' ){
					$sql = "select
							(case graduate_last
										when 0010 then '고졸'
										when 0020 then '초대졸'
										when 0030 then '대졸'
										when 0040 then '석사'
										when 0050 then '박사' else '-' end) AS ITEM_NAME
							,count(case graduate_last
										when 0010 then '고졸'
										when 0020 then '초대졸'
										when 0030 then '대졸'
										when 0040 then '석사'
										when 0050 then '박사' else '-' end) AS ITEM_CNT
							,(select count(*) from total_member_tbl where work_position in ('1','2')) AS ALL_CNT
							from total_member_tbl
							where work_position in ('1','2')
							group by graduate_last
							order by graduate_last
							";
				}elseif( $input_radio_01 == '3' ){
					$sql = "select age AS ITEM_NAME , COUNT(age) AS ITEM_CNT ,(select count(*) from total_member_tbl where work_position in ('1','2')) AS ALL_CNT
							from
							(select *,
							truncate(substr(CURDATE(),1,4) - (if(substr(jumin_no, 8, 1) = '1' or substr(jumin_no, 8, 1) = '2' or substr(jumin_no, 8, 1) = '3' or substr(jumin_no, 8, 1) = '5' or substr(jumin_no, 8, 1) = '6', 1900, 2000) + left(jumin_no, 2)) + 1,-1) as age from total_member_tbl where substr(jumin_no, 8, 1) not in ('')) a
							where work_position in ('1','2')
							group by age";
				}elseif( $input_radio_01 == '4' ){
					$sql = "select
								(case when mod(substr(jumin_no,8,1),2) = 1 then '남'
												when mod(substr(jumin_no,8,1),2) = 0 then '여'
												else '기타' end) AS ITEM_NAME
								,COUNT(case when mod(substr(jumin_no,8,1),2) = 1 then '남'
												when mod(substr(jumin_no,8,1),2) = 0 then '여'
												else '기타' end) AS ITEM_CNT
								,(select count(*) from total_member_tbl where work_position in ('1','2')) AS ALL_CNT
							from
							( select *, IF( mod(substr(jumin_no,8,1),2) = 1, '남', '여' ) as sex from total_member_tbl ) A
							where work_position in ('1','2')
							group by sex
							";
				}
				$list_data = array();
				$re2 = mysql_query($sql);
				//print_R($sql);
				while($re_row = mysql_fetch_array($re2)){
					$re_row['color'] = $colors[count($list_data)];
					$re_row['tar_percents'] =  sprintf( '%0.2f', $re_row['ITEM_CNT'] / $re_row['ALL_CNT'] * 100 ) ;

					$ITEM_NAMES = array();
					$cnt = mb_strlen($re_row['ITEM_NAME'], 'UTF-8');
					//echo $cnt;
					for( $i=0; $i<$cnt; $i++ ){
						//echo iconv_substr($re_row['ITEM_NAME'], $i, 1, "UTF-8");
						array_push($ITEM_NAMES, iconv_substr($re_row['ITEM_NAME'], $i, 1, "UTF-8"));
					}
					//print_r( $ITEM_NAMES );
					$re_row['ITEM_NAME'] = '';
					foreach($ITEM_NAMES as $key => $value){
						$re_row['ITEM_NAME'] .= $value.'';
					}

					array_push($list_data,$re_row);
				}
				echo json_encode($list_data);
				break;
			default:
				break;
		}
	}

}//class  End
/* ****************************************************************************************************************** */
?>
