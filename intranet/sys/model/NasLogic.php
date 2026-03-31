<?php
	/* **********************************
	*************************************** */
	require('../../../SmartyConfig.php');	
	require('../inc/function_intranet.php');	
	/* ----------------------------------- */
	include "../inc/getCookieOfUser.php";  //사용자에 관한 쿠키값
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

?>
<?php
class NasLogic extends Smarty {
	// 생성자
	function NasLogic()
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
	/* ----------------------------------------------------------------------------------------------- */
	
	function nasMain_page()
	{
		/*---------------------------------------------------*/
		$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
		$this->assign('myip',$myip);
		/*----------------------------- ---------------------*/
		//echo $_SERVER["REMOTE_ADDR"];

		global $tab_index; 
		global $memberID; 
		global $MemberNo;	  // 사원번호
		global $GroupCode;
		global $db;
			/////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////////////////////
			//$sql ="SELECT * from nas_server_tbl  ";
			$sql ="SELECT * from nas_detail_tbl  
						WHERE
							deleteYN='N'
			";

			$result = mysql_query($sql, $db);
			$re_num = mysql_num_rows($result);
			$arrayData = array();
			if($re_num>0){
				/////////////////////////////////////////////
				while($result_row = mysql_fetch_array($result)){
					//--------------------------------------------------------------
					$result_row[existYN] = "Y";
					//--------------------------------------------------------------
					array_push($arrayData,$result_row);
					//--------------------------------------------------------------
				}//while
			}else{
				$arrayData = array(array('existYN'=>'N'));
			}

			$this->assign('GroupList',$arrayData);
			/////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////////////////////

			$sql2 ="
				SELECT n.*
				, s.name as s_name 
				FROM 
				nas_detail_tbl n
				, systemconfig_tbl s
				WHERE 
				n.nas_dept_code=s.code 
				AND 
				(n.etc_04 is null or n.etc_04 ='')
				AND 
				n.deleteYN='N'
				AND 
				s.syskey='GroupCode' 
				ORDER BY n.etc_01
				";

			$result2 = mysql_query($sql2, $db);
			$re_num2 = mysql_num_rows($result2);
			$arrayData2 = array();
			if($re_num2>0){
				/////////////////////////////////////////////
				while($result_row2 = mysql_fetch_array($result2)){
					//--------------------------------------------------------------
					$result_row2[existYN] = "Y";
					//--------------------------------------------------------------
					array_push($arrayData2,$result_row2);
					//--------------------------------------------------------------
				}//while
			}else{
				$arrayData2 = array(array('existYN'=>'N'));
			}
			
			$this->assign('GroupList2',$arrayData2);
			/////////////////////////////////////////////////////////////////////////////////
			$this->assign('GroupCode',$GroupCode);
			$this->assign('memberID',$memberID);
			/////////////////////////////////////////////////////////////////////////////////

	$this->display("intranet/common_contents/work_nas/nas_list.tpl");
	}// nasMain_page
/*******************************************************************************************************/

	function Nas_person_insertUpdate()
	{
		global $tab_index;
		global $memberID;
		global $MemberNo;	  // 사원번호
		global $db;
		/////////////////////////////////////////////////////////////////////////////////
		$nas_seq = $_REQUEST['nas_seq']==''?'':$_REQUEST['nas_seq'];
		$nas_server_name= $_REQUEST['nas_server_name']==''?'':$_REQUEST['nas_server_name'];
		$nas_dept_code= $_REQUEST['nas_dept_code']==''?'':$_REQUEST['nas_dept_code'];
		$search01 = $_REQUEST['search01']==''?'':$_REQUEST['search01'];
		$search02 = $_REQUEST['search02']==''?'':$_REQUEST['search02'];
		$this->assign('nas_seq',$nas_seq);
		$this->assign('nas_server_name',$nas_server_name);
		$this->assign('nas_dept_code',$nas_dept_code);
		$this->assign('search01',$search01);
		$this->assign('search02',$search02);
		/////////////////////////////////////////////////////////////////////////////////
		$this->assign('memberID',$memberID);
		/////////////////////////////////////////////////////////////////////////////////
		Get_GroupInfo('','','ASSIGN','array_Get_GroupInfo',$this); //부서정보 : selectbox options
		/////////////////////////////////////////////////////////////////////////////////
		$Get_nas_person_tbl = Get_nas_person_tbl($nas_seq, '', '');
		$arrayData = array();
		if($Get_nas_person_tbl[0][existYN]=="Y"){
			for($i=0;$i<count($Get_nas_person_tbl);$i++){
				array_push($arrayData,$Get_nas_person_tbl[$i][memberno]);
			}
			$array_Get_GroupInfo_exist = Get_membersInfo($arrayData, 'IN', '', 'array_Get_GroupInfo_exist', $this);
			$this->assign('array_Get_GroupInfo_exist',$array_Get_GroupInfo_exist);
		}
		/////////////////////////////////////////////////////////////////////////////////
		
// 	echo $nas_seq."<br>";
// 	echo $memberID."<br>";
	
		$this->display("intranet/common_contents/work_nas/nas_person_insertUpdate.tpl");
	}// 
	/*******************************************************************************************************/
	
	
	function Return_Data()
	{
		global $memberID;
		global $MemberNo;	  // 사원번호
		global $db;
		/////////////////////////////////////////////////////////////////////////////////
		$DataKind = $_REQUEST['DataKind']==''?'':$_REQUEST['DataKind'];
		$search01 = $_REQUEST['search01']==''?'':$_REQUEST['search01'];
		$search02 = $_REQUEST['search02']==''?'':$_REQUEST['search02'];

		if($DataKind=="Get_membersInfo"){
			
			/////////////////////////////////////////////////////////////////////////////////
			$nas_seq = $_REQUEST['nas_seq']==''?'':$_REQUEST['nas_seq'];
			$nas_server_name= $_REQUEST['nas_server_name']==''?'':$_REQUEST['nas_server_name'];
			$nas_dept_code= $_REQUEST['nas_dept_code']==''?'':$_REQUEST['nas_dept_code'];
			$this->assign('nas_seq',$nas_seq);
			$this->assign('nas_server_name',$nas_server_name);
			$this->assign('nas_dept_code',$nas_dept_code);
			/////////////////////////////////////////////////////////////////////////////////
			$Get_nas_person_tbl = Get_nas_person_tbl($nas_seq, '', '');
			$arrayData = array();
			if($Get_nas_person_tbl[0][existYN]=="Y"){
				for($i=0;$i<count($Get_nas_person_tbl);$i++){
					array_push($arrayData,$Get_nas_person_tbl[$i][memberno]);
				}
				$array_Get_GroupInfo_exist = Get_membersInfo($arrayData, 'IN', '', 'array_Get_GroupInfo_exist', $this);
				$this->assign('array_Get_GroupInfo_exist',$array_Get_GroupInfo_exist);
			}
			/////////////////////////////////////////////////////////////////////////////////
			
 		Get_membersInfo($search01, $search02, 'ASSIGN', 'DataList', $this); ///inc/function_intranet.php : Get_membersInfo($val_01:사원번호, $val_02:부서코드, $val_03:리턴TYPE(ARRAY,ASSIGN,AJAX), $val_04:리턴 TITLE, $val_05)
			
		//	{include file="./intranet/common_contents/work_nas/nas_person_inner.tpl"}
			$this->display("intranet/common_contents/work_nas/nas_person_inner.tpl");

		}else{
			$this->assign('search01',$search01);
			$this->assign('search02',$search02);
		}
		
	}//Return_Data
	
	
	function Nas_manage_list()
	{
		global $tab_index;
		global $memberID;
		global $MemberNo;	  // 사원번호
		global $db;
		/////////////////////////////////////////////////////////////////////////////////
		$search01 = $_REQUEST['search01']==''?'':$_REQUEST['search01'];
		$search02 = $_REQUEST['search02']==''?'':$_REQUEST['search02'];
		$this->assign('search01',$search01);
		$this->assign('search02',$search02);
		
		$addQuery_01="";
		$addQuery_02="";
		
		if($search01=="1"){
			//부서
			$addQuery_01="
					AND (n.etc_04='' OR   n.etc_04 is null)
						";
		}else if($search01=="2"){
			//프로젝트
			$addQuery_01="
					AND n.etc_04='project'
					";
		}else{}
		
		if($search02!=""){
			$addQuery_02="
					AND
					(
					n.nas_server_name LIKE '%{$search02}%'
					OR
					n.nas_id LIKE '%{$search02}%'
					OR
					n.etc_01 LIKE '%{$search02}%'
					";
			if($search01=="2" || $search01==""){
				//프로젝트/전체
				$addQuery_02.="
					OR
					n.etc_01 LIKE '%{$search02}%'
					";
			}
			
			$addQuery_02.=")";
			
			
			
		}else{}
		
		
// 		/////////////////////////////////////////////////////////////////////////////////
// 		$sql ="SELECT * from nas_server_tbl  ";
	
// 		$result = mysql_query($sql, $db);
// 		$re_num = mysql_num_rows($result);
// 		$arrayData = array();
// 		if($re_num>0){
// 			/////////////////////////////////////////////
// 			while($result_row = mysql_fetch_array($result)){
// 				//--------------------------------------------------------------
// 				$result_row[existYN] = "Y";
// 				//--------------------------------------------------------------
// 				array_push($arrayData,$result_row);
// 				//--------------------------------------------------------------
// 			}//while
// 		}else{
// 			$arrayData = array(array('existYN'=>'N'));
// 		}
	
// 		$this->assign('GroupList',$arrayData);
// 		/////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////////////
	
		$sql2 ="
				SELECT 
					 n.nas_seq
					,n.nas_dept_code
					,n.nas_server_name as server_name
					,n.nas_server_name
					,n.nas_ip
					,n.nas_id
					,n.nas_pw
					,n.etc_01 as server_desc
					,n.etc_01 
					,n.etc_02
					,n.etc_03
					,n.etc_04
					,n.etc_05
					,n.deleteYN
				FROM
					nas_detail_tbl n
				WHERE
					n.deleteYN='N'
					";
		$sql2 .= $addQuery_01;
		$sql2 .= $addQuery_02;
		
		$sql2 .=	" ORDER BY 
					n.etc_04, n.nas_dept_code, n.nas_server_name
				";
			
		
		//echo $sql2;
		
		$result2 = mysql_query($sql2, $db);
		$re_num2 = mysql_num_rows($result2);
		$arrayData2 = array();
		if($re_num2>0){
			/////////////////////////////////////////////
			while($result_row2 = mysql_fetch_array($result2)){
				//--------------------------------------------------------------
				$result_row2[existYN] = "Y";
				//--------------------------------------------------------------
				$etc_04 = $result_row2[etc_04]; //project이면 프로젝트, 널이면 부서
				$etc_05 = $result_row2[etc_05]; //project이면 사원번호
				if($etc_04=="project" && $etc_05!="" ){
					$re_memberName = MemberNo2Name($etc_05);
					$result_row2[etc_05_kor] = $re_memberName;
				}
				
				array_push($arrayData2,$result_row2);
				//--------------------------------------------------------------
			}//while
		}else{
			$arrayData2 = array(array('existYN'=>'N'));
		}
			
		$this->assign('GroupList2',$arrayData2);
		/////////////////////////////////////////////////////////////////////////////////
		
		$array_Get_GroupInfo = Get_GroupInfo('','','','','');
		$this->assign('array_Get_GroupInfo',$array_Get_GroupInfo);
		
	
		$this->assign('memberID',$memberID);
		/////////////////////////////////////////////////////////////////////////////////
	
	
		$this->display("intranet/common_contents/work_nas/nas_manage_list.tpl");
	}// Nas_manage_list
	/*******************************************************************************************************/
	
	
	
	function ExecuteDB()
	{
		global $memberID;
		global $MemberNo;	  // 사원번호
		global $db;
		//---------------------------------------------------------------------------
		$queryKind = $_REQUEST['queryKind']==''?'':$_REQUEST['queryKind'];
		//---------------------------------------------------------------------------
		$UPDATE_nas_seq = $_REQUEST['UPDATE_nas_seq']==''?'':$_REQUEST['UPDATE_nas_seq'];
		$UPDATE_nas_dept_code = $_REQUEST['UPDATE_nas_dept_code']==''?'':$_REQUEST['UPDATE_nas_dept_code'];
		$UPDATE_nas_server_name = $_REQUEST['UPDATE_nas_server_name']==''?'':$_REQUEST['UPDATE_nas_server_name'];
		$UPDATE_nas_ip = $_REQUEST['UPDATE_nas_ip']==''?'':$_REQUEST['UPDATE_nas_ip'];
		$UPDATE_nas_id = $_REQUEST['UPDATE_nas_id']==''?'':$_REQUEST['UPDATE_nas_id'];
		$UPDATE_nas_pw = $_REQUEST['UPDATE_nas_pw']==''?'':$_REQUEST['UPDATE_nas_pw'];
		$UPDATE_etc_01 = $_REQUEST['UPDATE_etc_01']==''?'':$_REQUEST['UPDATE_etc_01'];
		$UPDATE_etc_02 = $_REQUEST['UPDATE_etc_02']==''?'':$_REQUEST['UPDATE_etc_02'];
		$UPDATE_etc_03 = $_REQUEST['UPDATE_etc_03']==''?'':$_REQUEST['UPDATE_etc_03'];
		$UPDATE_etc_04 = $_REQUEST['UPDATE_etc_04']==''?'':$_REQUEST['UPDATE_etc_04'];
		$UPDATE_etc_05 = $_REQUEST['UPDATE_etc_05']==''?'':$_REQUEST['UPDATE_etc_05'];
		//---------------------------------------------------------------------------
		$INSERT_nas_dept_code = $_REQUEST['INSERT_nas_dept_code']==''?'':$_REQUEST['INSERT_nas_dept_code'];
		$INSERT_nas_server_name = $_REQUEST['INSERT_nas_server_name']==''?'':$_REQUEST['INSERT_nas_server_name'];
		$INSERT_nas_ip = $_REQUEST['INSERT_nas_ip']==''?'':$_REQUEST['INSERT_nas_ip'];
		$INSERT_nas_id = $_REQUEST['INSERT_nas_id']==''?'':$_REQUEST['INSERT_nas_id'];
		$INSERT_nas_pw = $_REQUEST['INSERT_nas_pw']==''?'':$_REQUEST['INSERT_nas_pw'];
		$INSERT_etc_01 = $_REQUEST['INSERT_etc_01']==''?'':$_REQUEST['INSERT_etc_01'];
		$INSERT_etc_02 = $_REQUEST['INSERT_etc_02']==''?'':$_REQUEST['INSERT_etc_02'];
		$INSERT_etc_03 = $_REQUEST['INSERT_etc_03']==''?'':$_REQUEST['INSERT_etc_03'];
		$INSERT_etc_04 = $_REQUEST['INSERT_etc_04']==''?'':$_REQUEST['INSERT_etc_04'];
		$INSERT_etc_05 = $_REQUEST['INSERT_etc_05']==''?'':$_REQUEST['INSERT_etc_05'];
		//---------------------------------------------------------------------------
		
		$arrayQuery=array();
		$query = "";
		
		if($queryKind){
				switch($queryKind)
				{
					case "INSERT":
						//---------------------------------------------------------------------------
						$re_create_key=create_key('nas_detail_tbl', 'nas_seq', '');
						
						$query = "
										INSERT INTO nas_detail_tbl
										(
										 nas_seq
										,nas_dept_code
										,nas_server_name
										,nas_ip
										,nas_id
										,nas_pw
										,etc_01 
										,etc_02
										,etc_03
										,etc_04
										,etc_05
										)VALUES(
										 '$re_create_key'
										,'$INSERT_nas_dept_code'
										,'$INSERT_nas_server_name'
										,'$INSERT_nas_ip'
										,'$INSERT_nas_id'
										,'$INSERT_nas_pw'
										,'$INSERT_etc_01' 
										,'$INSERT_etc_02'
										,'$INSERT_etc_03'
										,'$INSERT_etc_04'
										,'$INSERT_etc_05'
										)
									";
						array_push($arrayQuery, $query);
						break;
					case "UPDATE":
						//---------------------------------------------------------------------------
						$query = "
									UPDATE nas_detail_tbl SET
									 nas_dept_code       = '$UPDATE_nas_dept_code'
									,nas_server_name   = '$UPDATE_nas_server_name'
									,nas_ip                    = '$UPDATE_nas_ip'
									,nas_id                    = '$UPDATE_nas_id'
									,nas_pw                  = '$UPDATE_nas_pw'
									,etc_01                   = '$UPDATE_etc_01' 
									,etc_02                   = '$UPDATE_etc_02'
									,etc_03                   = '$UPDATE_etc_03'
									,etc_04                   = '$UPDATE_etc_04'
									,etc_05                   = '$UPDATE_etc_05'
									WHERE
									 nas_seq                 = '$UPDATE_nas_seq'
									";
						array_push($arrayQuery, $query);
						break;
					case "DELETE":
						//---------------------------------------------------------------------------
						$query = "
									UPDATE nas_detail_tbl SET
										deleteYN = 'Y'
									WHERE
									 	nas_seq = '$UPDATE_nas_seq'
									";
						array_push($arrayQuery, $query);
						//---------------------------------------------------------------------------
						$delete_query = " DELETE FROM nas_person_tbl WHERE nas_seq='$UPDATE_nas_seq' ";
						array_push($arrayQuery, $delete_query);
						//---------------------------------------------------------------------------
						break;
				
					
					case "insert_project":
						//---------------------------------------------------------------------------
						$nas_seq = $_REQUEST['nas_seq']==''?'':$_REQUEST['nas_seq'];
						$nas_server_name= $_REQUEST['nas_server_name']==''?'':$_REQUEST['nas_server_name'];
						$nas_dept_code= $_REQUEST['nas_dept_code']==''?'':$_REQUEST['nas_dept_code'];
						$array_selectedMember = $_REQUEST['selectedMember']==''?'':$_REQUEST['selectedMember'];
						//---------------------------------------------------------------------------
						//array내 중복제거
						if($array_selectedMember){
							$array_selectedMember  = array_unique($array_selectedMember );
						}
						//---------------------------------------------------------------------------
						if($nas_seq!="" && count($array_selectedMember)>0){
							//---------------------------------------------------------------------------------------------
							$delete_query = " DELETE FROM nas_person_tbl WHERE nas_seq='$nas_seq' ";
							array_push($arrayQuery, $delete_query);
							//---------------------------------------------------------------------------------------------
							$re_create_key=create_key('nas_person_tbl', 'document_pk', '');
							$CH_create_key = $re_create_key;
							//---------------------------------------------------------------------------------------------
							for($i=0;$i<count($array_selectedMember);$i++){
								if($array_selectedMember[$i]!=""){
										$query = "
										INSERT INTO nas_person_tbl
										(
										 document_pk 
										,nas_seq         
										,memberno     
										,etc_01           
										,etc_02           
										,etc_03           
										,etc_04           
										,etc_05            
										)VALUES(
										 '$CH_create_key' 
										,'$nas_seq'         
										,'$array_selectedMember[$i]'     
										,'$etc_01'           
										,'$etc_02'           
										,'$etc_03'           
										,'$etc_04'           
										,'$etc_05'         
										)
										";
								
									array_push($arrayQuery, $query);
									$CH_create_key++;
								}
							}//for
						}
							//---------------------------------------------------------------------------------------------
						break;	
					default:
						break;
				}//switch
				
		}else{
			
		}
		
		
		// 사용시 참조
		 //트랜젝션 Start/////////////////////////////////////////////////////////////
// 		$arrayQuery=array();
// 		array_push($arrayQuery, $query);
		//array_push($arrayQuery, $sql02);
		//트랜젝션동작 FUNCTION : TransactionArrayQuery_forMysql(Param01=array쿼리값, Param02=예비)
		$resultArray = $this->TransactionArrayQuery_forMysql($arrayQuery,'');
		$result_01 = $resultArray['result_01'];
		$result_02 = $resultArray['result_02'];
		$result_03 = $resultArray['result_03'];
		//트랜젝션 End/////////////////////////////////////////////////////////////
		
		$returnValue =array('result_01'=>$result_01 ,'result_02'=>$result_02,'result_03'=>$result_03 );
		
		//echo $returnValue;
		print_r( urldecode( json_encode($returnValue) ));
		//return $returnValue;
	}//ExecuteDB
	

	////////////////////////////////////////////////////////by Moon
	//MYSQL  트랜잭션 처리
	function TransactionArrayQuery_forMysql($val01,$val02)	//트랜젝션동작 FUNCTION : TransactionArrayQuery(Param01=array쿼리값, Param02=예비)
	{
		global $db;
		//-----------------------------------------------------
		$arrayQuery = $val01; //넘겨받은 array(쿼리)
		$ReturnKind = $val02==""?"ARRAY":$val02; //실행후 return형식 : default=ARRAY   ..종류=> JSON/ECHO
	
		//-----------------------------------------------------
		$queryCount = count($arrayQuery);//array(쿼리) 갯수
		//-----------------------------------------------------
		//작업성공여부 플래그 값
		$result_01="";
		$result_02="";
		$result_03="";
		//-----------------------------------------------------
		$FaultCount = 0;
		$FailIndex="";
		//-----------------------------------------------------
		$ussingQuery="";
	
		//트랜잭션 동작 Start /////////////////////////////////////////////////////////////////////////////
		//트랜잭션 시작
		$result = @mysql_query("SET AUTOCOMMIT=0",$db);
		$result = @mysql_query("BEGIN",$db);
	
// 		FN_remainLog('111', '', 'w', '');
// 		for($i=0;$i<$queryCount;$i++){
// 			FN_remainLog($arrayQuery[$i], '', '', '');
// 		}
// 		exit();
		
		
		//-----------------------------------------------------
		for($i=0;$i<$queryCount;$i++){
			//n번째 DB실행
			$result = mysql_query($arrayQuery[$i],$db) ; // 쿼리 DB실행
			//$result = @mssql_query($arrayQuery[$i]); // 쿼리 DB실행
	
			$ussingQuery = $arrayQuery[$i];
	
			if(!$result){//쿼리실행 실패시
				$FailIndex = $i;
				$FaultCount++;
				break;
			}
		}//for
		//-----------------------------------------------------
		//성공
		if($FaultCount<1){
			$result = @mysql_query("COMMIT",$db);
			$result_01 = "1";
			$result_02 = "SUCCESS";
			$result_03 = "SUCCESS";
			//$result_03=$ussingQuery;
	
		}else{
			//실패시 자동롤백
			$result = @mysql_query("ROLLBACK",$db);
				
			$result_01 = "2";
			$result_02 = "Fail(query[".$FailIndex."])";
			$result_03 = $ussingQuery;
		}
		//-----------------------------------------------------
		//트랜잭션 동작 End /////////////////////////////////////////////////////////////////////////////
	
		$data = array(
		'result_01'=>$result_01,
		'result_02'=>$result_02,
		'result_03'=>$result_03,
		);
		////////////////////
		if($ReturnKind=="ARRAY" || $ReturnKind==""){
			return $data;
		}else if($ReturnKind=="JSON"){
			echo json_encode($data); //php배열을 json 형태로 변경해주는 php 내장함수 입니다.
		}else if($ReturnKind=="PRINT_R"){
			print_r($data); //php배열을 json 형태로 변경해주는 php 내장함수 입니다.
		}
		////////////////////
	
		/* 사용시 참조
		 //트랜젝션 Start/////////////////////////////////////////////////////////////
		 $arrayQuery=array();
		 array_push($arrayQuery, $sql01);
		 //array_push($arrayQuery, $sql02);
		 //트랜젝션동작 FUNCTION : TransactionArrayQuery_forMysql(Param01=array쿼리값, Param02=예비)
		 $resultArray = TransactionArrayQuery_forMysql($arrayQuery,'');
		 $result_01 = $resultArray['result_01'];
		 $result_02 = $resultArray['result_02'];
		 $result_03 = $resultArray['result_03'];
		 //트랜젝션 End/////////////////////////////////////////////////////////////
		 */
	
	}//TransactionArrayQuery_forMysql
	
	
	
	function QueryMode()
	{
		extract($_REQUEST);
		global $db;
		/////////////////////////////////////////////////////////////////////////////////
		$returnValue="";
		/////////////////////////////////////////////////////////////////////////////////
		$Param_01	=	$_REQUEST['user_id']==""?"":$_REQUEST['user_id']; //조회대상 : 사원번호
		$Param_02	=	$_REQUEST['user_password']==""?"":$_REQUEST['user_password']; //조회대상 : 비밀번호(INTRANET)
	
		if($Param_01 && $Param_02){
			$sql2 ="
							(
							select 
									 n.nas_seq
									,n.nas_dept_code
									,n.nas_server_name as server_name
									,n.nas_server_name
									,n.nas_ip
									,n.nas_id
									,n.nas_pw
									,n.etc_01 as server_desc
									,n.etc_01 
									,n.etc_02
									,n.etc_03
									,n.etc_04
									,n.etc_05
									,n.deleteYN
									
									from
							(SELECT 	* 	from 	member_tbl  	WHERE 	MemberNo = '$Param_01' 	AND 	Pasword = '$Param_02' ) m
							LEFT JOIN
							(SELECT * FROM  nas_detail_tbl where deleteYN='N' )n
							ON
							n.nas_dept_code =m.groupcode
							)
							union
							(
							select 
									 n.nas_seq
									,n.nas_dept_code
									,n.nas_server_name as server_name
									,n.nas_server_name
									,n.nas_ip
									,n.nas_id
									,n.nas_pw
									,n.etc_01 as server_desc
									,n.etc_01 
									,n.etc_02
									,n.etc_03
									,n.etc_04
									,n.etc_05
									,n.deleteYN
									from
							(SELECT * from nas_person_tbl where memberno= '$Param_01' ) m
							LEFT JOIN
							(SELECT * FROM  nas_detail_tbl where deleteYN='N' )n
							ON
							m.nas_seq =n.nas_seq
							)
				";
			$result2 = mysql_query($sql2, $db);
			$re_num2 = mysql_num_rows($result2);
			$arrayData2 = array();
			if($re_num2>0){
				while($result_row2 = mysql_fetch_array($result2)){
					array_push($arrayData2,$result_row2);
				}//while
				//결과값 존재
				$returnValue=$arrayData2;
			}else{
				//실패
				$returnValue="FAIL1";//FAIL : 결과값이 없습니다.
			}
	
		}else{
			//실패
			$returnValue="FAIL2";//FAIL  //아이디/비밀번호를 입력하세요
		}
	
		if(is_array($returnValue)){
			print_r( urldecode( json_encode($returnValue) ));
		}else{
			echo $returnValue;
		}
	}// QueryMode
	/*******************************************************************************************************/
	

	function QueryMode222()
	{
		global $tab_index;
		global $memberID;
		global $MemberNo;	  // 사원번호
		global $db;
		/////////////////////////////////////////////////////////////////////////////////
		$returnValue="";
		/////////////////////////////////////////////////////////////////////////////////
		$Param_01	=	$_REQUEST['user_id']==""?"":$_REQUEST['user_id']; //조회대상 : 사원번호
		$Param_02	=	$_REQUEST['user_password']==""?"":$_REQUEST['user_password']; //조회대상 : 비밀번호(INTRANET)
		

		if($Param_01 && $Param_02){
				/////////////////////////////////////////////////////////////////////////////////
				$whereQuery = "
									WHERE
										MemberNo = '{$Param_01}'
									AND
										Pasword = '{$Param_02}'
									";
				$resultYN = tableRowCount('member_tbl',$whereQuery); // 아이디/비밀번호 일치유무 : return:Y/N  : /inc/function_intranet.php : 

//echo $resultYN;

				/////////////////////////////////////////////////////////////////////////////////
				if($resultYN =="Y"){
					$GroupCode = MemberNoToGroupCode($Param_01);

					if(strlen($GroupCode )<2){
						$GroupCode= sprintf("%02d",$GroupCode);
					}

					$addQuery  = "";
					if($GroupCode!="0" && $GroupCode!=""){
						$addQuery  =" OR ( n.nas_dept_code ='{$GroupCode}') ";
					}else{
						$addQuery  = "";
					}
							/////////////////////////////////////////////////////////////////////////////////
							$sql2 ="
									SELECT 
									 n.nas_seq
									,n.nas_dept_code
									,n.nas_server_name as server_name
									,n.nas_server_name
									,n.nas_ip
									,n.nas_id
									,n.nas_pw
									,n.etc_01 as server_desc
									,n.etc_01 
									,n.etc_02
									,n.etc_03
									,n.etc_04
									,n.etc_05
									,n.deleteYN
									FROM
									nas_detail_tbl n
									WHERE
									(
										( n.etc_04= 'project' AND n.etc_05= '{$Param_01}' ) 
									";
							$sql2 .=$addQuery;
							
							$sql2 .="
									)
									AND 
									n.deleteYN='N'
									ORDER BY n.nas_dept_code, n.etc_01
									";
								
//echo $sql2;

							$result2 = mysql_query($sql2, $db);
							$re_num2 = mysql_num_rows($result2);
							$arrayData2 = array();
							if($re_num2>0){
								/////////////////////////////////////////////
								while($result_row2 = mysql_fetch_array($result2)){
									//--------------------------------------------------------------
									$result_row2[existYN] = "Y";
									//--------------------------------------------------------------
									array_push($arrayData2,$result_row2);
									//--------------------------------------------------------------
								}//while
								//결과값 존재////////////////////////////////////////////////////////////////////
								$returnValue=$arrayData2;
								/////////////////////////////////////////////////////////////////////////////////
								
							}else{
								//$arrayData2 = array(array('existYN'=>'N'));
								//실패///////////////////////////////////////////////////////////////////////
								$returnValue="";//FAIL : 결과값이 없습니다.
							}
				}else{
					//실패///////////////////////////////////////////////////////////////////////
					$returnValue="FAIL"; //FAIL //아이디/비밀번호를 확인하세요 . 입력하신 아이디/비밀번호에 해당하는 결과값이 없습니다.
				}		
		}else{
			//실패///////////////////////////////////////////////////////////////////////
			$returnValue="FAIL";//FAIL  //아이디/비밀번호를 입력하세요
		}
		
		if(is_array($returnValue)){
			print_r( urldecode( json_encode($returnValue) ));
		}else{
			echo $returnValue;
		}
		

	}// Careerdelfile_Action
	/*******************************************************************************************************/
	
	
	
	
}//class Main End
?>
