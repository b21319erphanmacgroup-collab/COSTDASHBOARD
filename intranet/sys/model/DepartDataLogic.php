<?php
	session_start();
	//스마티 설정파일 *****************************************
	include "../inc/dbcon.inc";
	include "../inc/function_intranet.php";  
	include "../../../SmartyConfig.php";
	//include "../inc/getCookieOfUser.php";  
	include "../util/HanamcPageControl.php";
	include "../inc/getNeedDate.php";      //로직에 사용되는 PHP시간&날짜 정의

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
	}else if($_SESSION['CK_memberID']!=""){				//쿠키값 유무확인
		/* SET COOKIE --------------------------------- */
		//쿠키정보 세션으로 대체 250426 김진선
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
		$memberID	=	$_REQUEST['memberID'];
		$MemberNo	=	$memberID;
		$MemberNo	=	strtoupper($MemberNo);
		/* ----------------------------------- */
		require('../../sys/popup/setInfo.php');
		/* ----------------------------------- */
	}//if End
	/* ----------------------------------- */
	$CompanyKind   = searchCompanyKind();//회사코드 찾기 return 4자리 영어대문자 회사코드
	/* ----------------------------------- */
		/* 수정을 위해 넘어온 GET VALUE****************************** */
		/* get 파라미터 한글깨짐 방지----------------------------------- */
		/*$edit_main_p_code	= iconv('euc-kr', 'utf-8', $_GET['main_p_code']); */
		/* ************************************************************** */
?>

<?php
class DepartDataLogic {
	var $smarty;// 생성자

	function DepartDataLogic($smarty)
	{ 
		$this->smarty=$smarty;
	}
	/* ***************************************************************************************** */
	function PageList()  //목록페이지로 이동
	{
	global $MemberNo, $memberID;

	global $GroupCode;
	$GroupCode = (int)$GroupCode;
	$set_GroupCode	= (int)$_GET['set_GroupCode'];
	if($set_GroupCode==""){
		$set_GroupCode	= $GroupCode;
	}//if End
	global $db;
	global $start, $page, $currentPage, $last_page;
	global $sub_index;

	$currentPage	= $_GET['currentPage'];
	$start	        = $_GET['start'];
	$searchKind		= ($_GET['searchKind']==""?"":$_GET['searchKind']);	
	$searchStr		= ($_GET['searchStr']==""?"":$_GET['searchStr']);	
	$addQuery01		= "";
	$addQuery02		= "";

	if($searchKind==""){
		$addQuery01 = " WHERE group_code ='".$set_GroupCode."' ";
		$addQuery02 = "	ORDER BY id DESC			";
	}else if($searchKind==1){// searchKind : 이름 : 1
		$addQuery01 = " WHERE group_code ='".$set_GroupCode."' ";
		$addQuery02 = "	ORDER BY id DESC			";
	}else if($searchKind==2){// searchKind : 사원번호 : 2
		$addQuery01 = " WHERE group_code ='".$set_GroupCode."' ";
		$addQuery02 = "	ORDER BY id DESC			";
	}else{
		$addQuery01 = "";
		$addQuery02 = "	ORDER BY id DESC			";
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

		$sql_count  = "select COUNT(*) CNT from group_board_tbl WHERE group_code ='".$set_GroupCode."'  ";
		$re         = mysql_query($sql_count);
		$re_count = mysql_result($re,0,"CNT"); 	
		
		$TotalRow   = $re_count;              //총 개수 저장
		//마지막페이지 
		$last_start = ceil($TotalRow/10)*10+1;
		$last_page  = ceil($TotalRow/$page);

		$sql=     "	SELECT					";
		$sql=$sql."		 level				";
		$sql=$sql."		,id					";
		$sql=$sql."		,name				";
		$sql=$sql."		,pass				";
		$sql=$sql."		,title				";
		$sql=$sql."		,comment			";
		$sql=$sql."		,wdate				";
		$sql=$sql."		,see				";
		$sql=$sql."		,group_code			";
		$sql=$sql."		,filename			";
		$sql=$sql."		,filesize			";
		$sql=$sql."		,reg_member			";
		$sql=$sql."		,filename_tmp		";
		$sql=$sql."	FROM 					";
		$sql=$sql."		group_board_tbl		";
		$sql=$sql.$addQuery01;
		$sql=$sql.$addQuery02;
		$sql=$sql."	LIMIT ".$start." , ".$page."";
/////////////////
//echo "02::".$sql."<br>"; 
		$re = mysql_query($sql,$db);
	while($re_row = mysql_fetch_array($re)) {
		$re_row[title_short]   = utf8_strcut($re_row[title],38,'..');
		$re_row[comment_short] = utf8_strcut($re_row[comment],10,'..');
		//$filenameArray = explode("/",$re_row[filename]); 
		//$re_row[filename_short] = utf8_strcut($filenameArray[2],10,'..');
		$divfile = explode("/",$re_row[filename]);
		$divnum  = count($divfile);
		$extensionName = $divfile[$divnum-1];//파일이름(경로제외)
		$re_row[filename_short] = $extensionName;

		array_push($query_data,$re_row);
	} //while End
		/*-------------------------------------*/
		$query_data02 = array(); 
		/*-------------------------------------*/
		/*조직도 관련 셀렉트*/
		$sql02=       "	SELECT					";
		$sql02=$sql02."		SysKey				";
		$sql02=$sql02."		,Code				";
		$sql02=$sql02."		,Name				";
		$sql02=$sql02."		,CodeORName			";
		$sql02=$sql02."		,Description		";
		$sql02=$sql02."		,Note				";
		$sql02=$sql02."		,orderno			";
		$sql02=$sql02."	FROM					";
		$sql02=$sql02."	systemconfig_tbl		";
		$sql02=$sql02."	WHERE					";
		$sql02=$sql02."	SysKey='GroupCode'		";
		$sql02=$sql02."	ORDER BY orderno ASC	";
/////////////////
//echo "03::<br>".$sql02."<br>"; 
		$re02 = mysql_query($sql02,$db);
	while($re_row02 = mysql_fetch_array($re02)) {
		if($set_GroupCode==(int)$re_row02[Code]){
			$this->smarty->assign('set_GroupName',$re_row02[Name]);
		}
		$re_row02[Code] = (int)$re_row02[Code];
		$re_row02[orderno_kind] = substr($re_row02[orderno],0,1);
		array_push($query_data02,$re_row02);

	} //while End

	/* 페이지네비 관련SET Start ------------------- */
	$PageHandler = new PageControl($this->smarty);
	$PageHandler-> SetMaxRow($TotalRow);
	$PageHandler-> SetCurrentPage($currentPage);
	$PageHandler-> PutTamplate();
	/* 페이지네비 관련SET End ------------------- */
		$this->smarty->assign('HTTP',$_SERVER['HTTP_USER_AGENT']);
		/*------------------- */
		$this->smarty->assign("page_action","departData_controller.php");
		/*------------------- */
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('set_GroupCode',$set_GroupCode);
		/*------------------- */
		$this->smarty->assign('start',$start);
		$this->smarty->assign('TotalRow',$TotalRow);
		$this->smarty->assign('last_start',$last_start);
		$this->smarty->assign('last_page',$last_page);
		$this->smarty->assign('currentPage',$currentPage);
		/*------------------- */
		$this->smarty->assign('sub_index',$sub_index);
		/*------------------- */
		$this->smarty->assign('query_data',$query_data);
		$this->smarty->assign('query_data02',$query_data02);
		/*------------------- */
		$this->smarty->display("intranet/common_contents/work_departData/listPage.tpl");
		/*------------------- */
	}  //PageList() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function ViewPage()//상세보기 페이지로 이동
	{
		global $MemberNo, $memberID,  $korName;
		global $db;
		$content_id	= (int)$_GET['content_id'];
		/*---------------------------------------*/
		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT									";
		$sql=$sql."		 G.level		as g_level			";
		$sql=$sql."		,G.id			as g_id				";
		$sql=$sql."		,G.name			as g_name			";
		$sql=$sql."		,G.pass			as g_pass			";
		$sql=$sql."		,G.title		as g_title			";
		$sql=$sql."		,G.comment		as g_comment		";
		$sql=$sql."		,G.wdate		as g_wdate			";
		$sql=$sql."		,G.see			as g_see			";
		$sql=$sql."		,G.group_code	as g_group_code		";
		$sql=$sql."		,G.filename		as g_filename		";
		$sql=$sql."		,G.filesize		as g_filesize		";
		$sql=$sql."		,G.reg_member	as g_reg_member		";
		$sql=$sql."		,G.filename_tmp	as g_filename_tmp	";
		$sql=$sql."		,S.Name			as s_Name			";
		$sql=$sql."	FROM									";
		$sql=$sql."		group_board_tbl G					";
		$sql=$sql."		,systemconfig_tbl S					";
		$sql=$sql."	WHERE									";
		$sql=$sql."	id=".$content_id."						";
		$sql=$sql."	AND										";
		$sql=$sql."	G.group_code=S.Code						";
		$sql=$sql."	AND										";
		$sql=$sql."	S.SysKey='GroupCode'					";
/////////////////
//echo "01::".$sql."<br>"; 
			/*-----------------------------*/
			$re = mysql_query($sql,$db);
			/*-----------------------------*/
			while($re_row = mysql_fetch_array($re)) {
				$re_row[title_short]   = utf8_strcut($re_row[g_title],40,'..');
				$re_row[comment_short] = utf8_strcut($re_row[g_comment],10,'..');

				$divfile = explode("/",$re_row[g_filename]);
				$divnum  = count($divfile);
				$extensionName = $divfile[$divnum-1];//파일이름(경로제외)
				$re_row[filename_short] = $extensionName;
 
				$readCount = (int)$re_row[g_see]; //조회수 COLUMN : see

				array_push($query_data,$re_row);
			} //while End
			/*-----------------------------*/
			$readCount = $readCount+1;
			/*-----------------------------*/
			/* 조회수 +1 업데이트 */
			/*----------------------------------------*/
			$sql02= " UPDATE group_board_tbl SET	";
			$sql02= $sql02."   see='".$readCount."'	";
			$sql02= $sql02." WHERE					";
			$sql02= $sql02."	id=".$content_id."	";
			/*----------------------------------------*/
///////////////////////
mysql_query($sql02,$db);
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_departData/viewPage.tpl");
		/*----------------------------------------*/

	}  //ViewPage() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function InsertPage()//입력페이지로 이동
	{
		global $MemberNo, $memberID, $korName;
		global $GroupCode;
		$GroupCode =(int)$GroupCode;
		
		global $GroupName;
		global $date_today;

		global $db;

		//$content_id	= (int)$_GET['content_id'];

		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);
		$this->smarty->assign('GroupCode',$GroupCode);
		$this->smarty->assign('GroupName',$GroupName);
		/*----------------------------------------*/
		$this->smarty->assign('date_today',$date_today);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_departData/insertPage.tpl");
		/*----------------------------------------*/

	}  //InsertPage() End
	/* ***************************************************************************************** */













	/* ***************************************************************************************** */
	function InsertDB()//입력 DB실행
	{
		global $MemberNo, $memberID, $korName;
		$GroupCode =(int)$GroupCode;
		
		global $GroupName;
		global $date_today;
		global $db;
		/* -------------------------------------------------- */
		$depart_pw			= $_POST['depart_pw'];			//비밀번호
		$depart_korName		= $_POST['depart_korName'];		//사원번호
		$depart_MemberNo	= $_POST['depart_MemberNo'];	//사원번호
		$depart_GroupCode	= $_POST['depart_GroupCode'];	//부서코드
		$depart_date_today	= $_POST['depart_date_today'];	//등록일자
		$depart_title	    = $_POST['depart_title'];		//제목
		$depart_Content	    = $_POST['depart_Content'];		//내용
		/* -------------------------------------------------- */
		$level="a9999999";
		$depart_File_Name	= "";
		$newnamefile		= "";
		$file_size			= "";
		$path				= "";
		$newnamefile		= "";
		/* -------------------------------------------------- */
		$depart_File_Name	    = $_FILES['depart_File']['name'];		//파일명
		/* -------------------------------------------------- */
		if($depart_File_Name!=""){
			/*------------------------------------------------------------*/
			$divfile = explode(".",$depart_File_Name);
			$divnum  = count($divfile);
			$extensionName = strtolower($divfile[$divnum-1]);//확장자명
			/*------------------------------------------------------------*/
			$extensionKind ="jpg,jpeg,hwp";  //저장가능한 확장자 체크
			/*------------------------------------------------------------*/
			if(eregi($extensionName,$extensionKind)){//저장가능한 확장자이면(true)
			//echo $extensionName;
			}//if End
			/*------------------------------------------------------------*/
			$pathAdd = $depart_GroupCode;
			
			$path ="../../../intranet_file/departData/".$pathAdd."/";     //파일경로
			$pathCreate ="../../../intranet_file/departData/".$pathAdd;   //경로생성

			$path22 ="./".$pathAdd."/";     //파일경로
			/*------------------------------------------------------------*/
			if (!is_dir($pathCreate)){
				mkdir($pathCreate, 777);
			}//if End
			/*------------------------------------------------------------*/
//			if(file_exists($path.$depart_File_Name)) {
//				//echo "파일존재";
//			}//if End

				//==============================================================================
				$insert_db =			"INSERT INTO																						"; 
				$insert_db .= " group_board_tbl																							";
				$insert_db .= " ( level, sub, id, name, email, home, pass, title, comment, wdate, see, group_code	";
				$insert_db .= "   , filename, filesize, reg_member, filename_tmp )												";
				$insert_db .= " values																										";
				$insert_db .= " (																												";
				$insert_db .= "    '".$level."'																								";
				$insert_db .= "   ,''																											";
				$insert_db .= "	,''																												";
				$insert_db .= "	,'".$depart_korName."'																					";
				$insert_db .= "	,''																												";
				$insert_db .= "	,''																												";
				$insert_db .= "	,'".$depart_pw."'																							";
				$insert_db .= "	,'".$depart_title."'																							";
				$insert_db .= "	,'".$depart_Content."'																					";
				$insert_db .= "	,'".$depart_date_today."'																				";
				$insert_db .= "	,0																												";
				$insert_db .= "	,'".$depart_GroupCode."'																				";
				//----------------------------------------------------------------------------------------------------
				$addQuery = "";
				//==============================================================================

				$newnamefile = date("Ymd")."-".time()."-".rand(11111,99999).rand(11111,99999).rand(11111,99999).".".$extensionName;  // 새 파일명 생성

				if( move_uploaded_file($_FILES['depart_File']['tmp_name'], $path.$newnamefile) ) {
					$file_size = filesize($path.$newnamefile); 
					$file_size = number_format($file_size);
 
						//==============================================================================
						$addQuery .= "	,'".$path22.$depart_File_Name."'																		";
						$addQuery .= "	,'".$file_size."'																								";
						$addQuery .= "	,'".$depart_MemberNo."'																					";
						$addQuery .= "	,'".$path22.$newnamefile."'																				";
						$addQuery .= " )																													";
						//----------------------------------------------------------------------------------------------------
						$insert_db .= $addQuery;
						mysql_query($insert_db,$db); 
						//mysql_close($db);
						//==============================================================================
					echo "1"; //success
					//////////////////////////////
				}else{
					//////////////////////////////
					echo "2"; // fail send file
					//////////////////////////////
				}

		}else{
			$depart_File_Name	= "";
			$newnamefile		= "";
			$file_size			= "";

					//==============================================================================
					$addQuery .= "	,'".$path22.$depart_File_Name."'																		";
					$addQuery .= "	,'".$file_size."'																								";
					$addQuery .= "	,'".$depart_MemberNo."'																					";
					$addQuery .= "	,'".$path22.$newnamefile."'																				";
					$addQuery .= " )																													";
					//----------------------------------------------------------------------------------------------------
					$insert_db .= $addQuery;
					mysql_query($insert_db,$db); 
					//mysql_close($db);
					//==============================================================================
			//////////////////////////////
			echo "1"; //success
			//////////////////////////////
		}//if End



		/*----------------------------------------*/
	}  //InsertDB() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function UpdatePage()	//수정페이지로 이동
	{
		global $MemberNo, $memberID, $korName;
		global $db;

		global $GroupCode;
		$GroupCode = (int)$GroupCode;
		global $GroupName;

		global $date_today;
		/*---------------------------------------*/
		$content_id	= (int)$_GET['content_id'];	//컨텐츠PK
		$depart_pw	= $_GET['depart_pw'];		//비밀번호
		/*---------------------------------------*/
		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT									";
		$sql=$sql."		 G.level		as g_level			";
		$sql=$sql."		,G.id			as g_id				";
		$sql=$sql."		,G.name			as g_name			";
		$sql=$sql."		,G.pass			as g_pass			";
		$sql=$sql."		,G.title		as g_title			";
		$sql=$sql."		,G.comment		as g_comment		";
		$sql=$sql."		,G.wdate		as g_wdate			";
		$sql=$sql."		,G.see			as g_see			";
		$sql=$sql."		,G.group_code	as g_group_code		";
		$sql=$sql."		,G.filename		as g_filename		";
		$sql=$sql."		,G.filename_tmp	as g_filename_tmp	";
		$sql=$sql."		,G.filesize		as g_filesize		";
		$sql=$sql."		,G.reg_member	as g_reg_member		";
		$sql=$sql."		,S.Name			as s_Name			";
		$sql=$sql."	FROM									";
		$sql=$sql."		group_board_tbl G					";
		$sql=$sql."		,systemconfig_tbl S					";
		$sql=$sql."	WHERE									";
		$sql=$sql."	G.id=".$content_id."					";
		$sql=$sql."	AND										";
		$sql=$sql."	G.group_code=S.Code						";
		$sql=$sql."	AND										";
		$sql=$sql."	S.SysKey='GroupCode'					";
		$sql=$sql."	AND										";
		$sql=$sql."	G.pass='".$depart_pw."'					";
		/*---------------------------------------*/
/////////////////
//echo "01::".$sql."<br>"; 
		/*-----------------------------*/
		$re = mysql_query($sql,$db);
		/*-----------------------------*/
			while($re_row = mysql_fetch_array($re)) {
				$re_row[title_short]   = utf8_strcut($re_row[g_title],40,'..');
				$re_row[comment_short] = utf8_strcut($re_row[g_comment],10,'..');
				$readCount = (int)$re_row[g_see]; //조회수 COLUMN : see

				array_push($query_data,$re_row);
			} //while End
		/*----------------------------------------*/
		$this->smarty->assign('memberID',$memberID);
		$this->smarty->assign('MemberNo',$MemberNo);
		$this->smarty->assign('korName',$korName);

		$this->smarty->assign('GroupCode',$GroupCode);
		$this->smarty->assign('GroupName',$GroupName);

		$this->smarty->assign('date_today',$date_today);
		/*----------------------------------------*/
		$this->smarty->assign('query_data',$query_data);
		/*----------------------------------------*/
		$this->smarty->display("intranet/common_contents/work_departData/updatePage.tpl");
		/*----------------------------------------*/

	}  //UpdatePage() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function UpdateDB()//수정 DB실행
	{
		global $MemberNo, $memberID, $korName;
		$GroupCode =(int)$GroupCode;
		
		global $GroupName;
		global $date_today;
		global $db;
		/* -------------------------------------------------- */
		$depart_id			= $_POST['depart_id'];			//PK
		$depart_pw			= $_POST['depart_pw'];			//비밀번호
		$depart_korName		= $_POST['depart_korName'];		//사원번호
		$depart_MemberNo	= $_POST['depart_MemberNo'];	//사원번호
		$depart_GroupCode	= $_POST['depart_GroupCode'];	//부서코드
		$depart_date_today	= $_POST['depart_date_today'];	//등록일자
		$depart_title	    = $_POST['depart_title'];		//제목
		$depart_Content	    = $_POST['depart_Content'];		//내용
		/* -------------------------------------------------- */
		$update_db =			" UPDATE								"; 
		$update_db = $update_db." group_board_tbl SET					";
		$update_db = $update_db." pass = '".$depart_pw."'				";
		$update_db = $update_db." ,name = '".$depart_korName."'			";
		$update_db = $update_db." ,reg_member = '".$depart_MemberNo."'	";
		$update_db = $update_db." ,group_code = '".$depart_GroupCode."'	";
		$update_db = $update_db." ,wdate = '".$depart_date_today."'		";
		$update_db = $update_db." ,title = '".$depart_title."'			";
		$update_db = $update_db." ,comment = '".$depart_Content."'		";
		$update_db = $update_db." WHERE									";
		$update_db = $update_db." id = '".$depart_id."'					";

//////////////////////////////////
//echo $update_db;
mysql_query($update_db,$db);
//////////////////////////////////
//mysql_close($db);
	}  //UpdateDB() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function DeleteDB()	//삭제
	{
		global $MemberNo, $memberID, $korName;
		global $db;
		/*-----------------------------*/
		$content_id	= (int)$_GET['content_id'];	//컨텐츠PK
		$depart_pw	= $_GET['depart_pw'];		//비밀번호
		/*-----------------------------*/
		$delete_query = "DELETE FROM group_board_tbl WHERE id = '".$content_id."'";
///////////////////////
mysql_query($delete_query);
///////////////////////
echo "1"; 
///////////////////////

	}  //DeleteDB() End
	/* ***************************************************************************************** */

	/* ***************************************************************************************** */
	function ConfirmPw()	//비밀번호 확인
	{
		global $MemberNo, $memberID, $korName;
		global $db;

		$content_id	= (int)$_GET['content_id'];	//컨텐츠PK
		$depart_pw	= $_GET['depart_pw'];		//비밀번호
		/*---------------------------------------*/
		$query_data = array(); 
		/*---------------------------------------*/
		$sql=     "	SELECT G.id, G.pass		";
		$sql=$sql."	FROM					";
		$sql=$sql."		group_board_tbl G	";
		$sql=$sql."	WHERE					";
		$sql=$sql."	G.id='".$content_id."'	";
		$sql=$sql."	AND						";
		$sql=$sql."	G.pass='".$depart_pw."'	";
		/*---------------------------------------*/
		$re = mysql_query($sql,$db);
		/*-----------------------------*/
		$count = mysql_num_rows($re);

		if($count>0){//비밀번호 OK
			echo "1"; 

		}else{ //비밀번호 오류
			echo "2"; 	//

		}//if End

	}  //ConfirmPw() End
	/* ***************************************************************************************** */











	/* ***************************************************************************************** */
	function ValueTest()//
	{
		global $MemberNo;
		global $memberID;
		global $korName;
		$GroupCode =(int)$GroupCode;
		
		global $GroupName;
		global $date_today;

		global $db;
		/* -------------------------------------------------- */
		$depart_pw			= $_POST['depart_pw'];			//비밀번호
		$depart_korName		= $_POST['depart_korName'];		//사원번호
		$depart_MemberNo	= $_POST['depart_MemberNo'];	//사원번호
		$depart_GroupCode	= $_POST['depart_GroupCode'];	//부서코드
		$depart_date_today	= $_POST['depart_date_today'];	//등록일자
		$depart_title	    = $_POST['depart_title'];		//제목
		$depart_Content	    = $_POST['depart_Content'];		//내용
		/* -------------------------------------------------- */
		$depart_File_Name	    = $_FILES['depart_File']['name'];		//파일명
		/* -------------------------------------------------- */
		if($depart_File_Name!=""){
			/*------------------------------------------------------------*/
			$divfile = explode(".",$depart_File_Name);
			$divnum  = count($divfile);
			$extensionName = strtolower($divfile[$divnum-1]);//확장자명
			/*------------------------------------------------------------*/
			$extensionKind ="jpg,jpeg,hwp";  //저장가능한 확장자 체크
			/*------------------------------------------------------------*/
			if(eregi($extensionName,$extensionKind)){//저장가능한 확장자이면(true)
			//echo $extensionName;
			}//if End
			/*------------------------------------------------------------*/
			$pathAdd = $depart_GroupCode;
			$path ="../../../intranet_file/departData/".$pathAdd."/";     //파일경로
			$pathCreate ="../../../intranet_file/departData/".$pathAdd;   //경로생성
			/*------------------------------------------------------------*/
			if (!is_dir($pathCreate)){
				mkdir($pathCreate, 777);
			}//if End
			/*------------------------------------------------------------*/
			if(file_exists($path.$depart_File_Name)) {
				//echo "파일존재";
			}else{//파일없음
				$newnamefile = date("Ymd")."-".time()."-".rand(11111,99999).rand(11111,99999).rand(11111,99999).".".$extensionName;  // 새 파일명 생성
				 move_uploaded_file($_FILES['depart_File']['tmp_name'], $path.$newnamefile);
			}//if End

			$file_size = filesize($path.$newnamefile); 
			$file_size = number_format($file_size);

		}else{
			$depart_File_Name	= "";
			$newnamefile		= "";
			$file_size			= "";
		}//if End
		/* -------------------------------------------------- */

/***********************************************************************************/
/*
	if($userfile_name <>"" && $userfile_size <>0)
	{
		$userfile=stripslashes($userfile);
		$vupload = $path. $_FILES['depart_File']['name'];
		move_uploaded_file($_FILES['depart_File']['tmp_name'], $vupload);
		$userfile_size = number_format($userfile_size);
	}
*/

		$this->smarty->assign('depart_pw',$depart_pw);
		$this->smarty->assign('depart_korName',$depart_korName);
		$this->smarty->assign('depart_MemberNo',$depart_MemberNo);
		$this->smarty->assign('depart_GroupCode',$depart_GroupCode);
		$this->smarty->assign('depart_date_today',$depart_date_today);
		$this->smarty->assign('depart_title',$depart_title);
		$this->smarty->assign('depart_Content',$depart_Content);

		$this->smarty->assign('depart_File_Name',$depart_File_Name);

		$this->smarty->assign('file_size',$file_size);


		$this->smarty->display("intranet/common_contents/work_departData/valueTest.tpl");

	}  //ValueTest() End
	/* ------------------------------------------------------------------------------ */







}//class  End
/* ****************************************************************************************************************** */
?>
