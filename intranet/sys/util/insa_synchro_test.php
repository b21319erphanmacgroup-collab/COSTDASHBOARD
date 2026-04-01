<?php
	//외부서버에서 ajax 허용
	header('Access-Control-allow-Origin:*');
	header('Content-Type: text/html; charset=UTF-8');

	/******************************************************************************
	* 삼안 SATIS에서 변경된 인사관련된 정보를 인트라넷에 적용하기 위한 클래스
	* -----------------------------------------------------------------------------
	*  작업일자   |  작업자   | 작업 내용
	* 2021-02-17  |  정명준   | 생성
	*******************************************************************************/
	include "../inc/dbcon.inc";

	include "./OracleClass.php";

	extract($_REQUEST);
	class InsaSynchronization {
		function InsaSynchronization(){
			$this->oracle=new OracleClass('');
		}

		//=================================================
		// 로그인에 대한 정보
		//=================================================
		function SynchProcess(){
			global $db;
			extract($_REQUEST);
			$action_sql = true;		// true false
			if($test=='test'){
				$action_sql = false;
			}
			if(!$action_sql){
				print_r($_REQUEST);
			}

			// $contents 기본은 N. 배치돌아가는건 D 날짜로 치환.( 20210219 ).
			if( $contents == '' or $contents == null or $contents == 'D' ){
				$contents = date("Ymd");
			}

			$ToDayStartTime = date('Y-m-d H:i:s');

			//시스템회사 == 소속회사 동기화
			//$azsql ="select * from HR_PERS_MASTER_MAPPING where contents = '$contents' order by decode(MODE_TAG, '11', 3, '99', 3, '17', 2, 1), MODE_TAG";
			$azsql ="select EMP_NO, CONTENTS, SUM( DECODE( MODE_TAG, '12', 1, 0 ) ) AS MODE_TAG_CHECK from HR_PERS_MASTER_MAPPING where CONTENTS = '$contents' group by EMP_NO, CONTENTS";
			//$azsql ="SELECT EMP_NO, 'N' as CONTENTS FROM hr_orde_master WHERE confirm_tag = 'Y' and final_tag = 'Y' and apply_order_date > '20160000'";
			if(!$action_sql){
				echo '<BR><BR><DIV>'.$azsql.'</DIV>';
			}
			$this->insert_insa_log($azsql);
			$datalist=$this->oracle->LoadData($azsql,"");

			for( $index = 0; $index < count($datalist); $index++ ){
				if(!$action_sql){
					ECHO '<BR>';
					print_r($datalist[$index]);
					ECHO '<BR>';
				}
				$CONTENTS = $datalist[$index]['CONTENTS'];
				$EMP_NO = $datalist[$index]['EMP_NO'];
				$MODE_TAG_CHECK = $datalist[$index]['MODE_TAG_CHECK'];	//사간전입 체크

				//정보 가져오기
				$azsql = "
					SELECT
						A.EMP_NO AS MEMBERNO
						, CASE
							WHEN A.SERVICE_DIV = '3' AND TO_CHAR(SYSDATE, 'YYYYMMDD') > NVL(A.RETIRE_DATE, '00000000') THEN '9'
							WHEN B.PAY_DIV = '20' AND B.WORKING_COMPANY <> '20' THEN '2'
							WHEN B.PAY_DIV = '30' AND B.WORKING_COMPANY <> '20' THEN '2'
							ELSE '1'
						END AS WORKPOSITION
						, ( select GRADE_NAME from HR_CODE_GRADE where B.GRADE_CODE = GRADE_CODE ) AS GRADE_NAME
						, A.EMP_NAME AS KORNAME
						, A.EMPNAME_CHI AS CHINAME
						, A.EMPNAME_ENG AS ENGNAME
						, DECODE( A.SEX_DIV, 1, 'M', 'F' ) AS GENDER
						, A.GROUP_JOIN_DATE AS ENTRYDATE
						, A.RRN_PRE || '-' || A.RRN_POST AS JUMINNO
						, replace(A.PHONE_PRE, '-', '')||replace(A.PHONE_POST, '-', '') AS PHONE
						, A.CELL_PRE || A.CELL_POST AS MOBILE
						, A.E_MAIL AS EMAIL
						, A.RRN_ADDR1 || ' ' || A.RRN_ADDR2 AS ORIGNADDRESS
						, A.ADDR1 || ' ' || A.ADDR2 AS ADDRESS
						, NVL( A.BIRTHDAY, '00000000' ) AS BIRTHDAY
						, ( select max(proj_code) from CS_CONT_MAP_MASTER where a.dept_code = proj_org_code ) as SITECODE
						, A.TITLE_CODE AS DUTYCODE
						, B.ERP_DEPT_CODE AS GROUPCODE
						, NVL( A.RETIRE_DATE, '00000000' ) AS LEAVEDATE

						, B.REALRANk as AAAAA
						, B.GRADE_CODE
						, B.PAY_DIV
						, B.WORKING_COMPANY
						, B.WORKING_DEPT
						, B.WORKING_RANK_CODE
						, B.WORKING_RANK_NAME
						, CASE B.PAY_DIV
							WHEN '10' THEN 'SAMAN'
							WHEN '20' THEN 'HANMAC'
							WHEN '30' THEN 'BARON'
							WHEN '40' THEN 'JANG'
							WHEN '50' THEN 'HALLA'
							WHEN '60' THEN 'PTC'
						END AS COMPANY
					FROM
						HR_PERS_MASTER A
						, HR_ORDE_MASTER B
					WHERE
						A.EMP_NO = '$EMP_NO'
						AND B.EMP_NO = '$EMP_NO'
						AND B.FINAL_TAG = 'Y'
						AND B.CONFIRM_TAG = 'Y'
						AND A.EMP_NO = B.EMP_NO
				";
				if(!$action_sql){
					echo '<br><DIV>'.$azsql.'</DIV>';
				}

				//$this->insert_insa_log($azsql);
				$onedata = $this->oracle->LoadData($azsql,"");

				if(!$action_sql){
					//print_r($onedata);
				}

				//확정이면서 최종인 데이터가 없을때 패스
				if($onedata[0]['KORNAME'] == ''){
					continue;
				}

				//운영회사 정보수정
				$azsql ="
					INSERT INTO member_tbl (
						MemberNo
						, Pasword
						, WorkPosition
						, korName
						, chiName
						, engName
						, entrydate
						, juminno
						, phone
						, email
						, orignaddress
						, address
						, birthday
						, LeaveDate
						, Company
				";

				if( $onedata[0]['PAY_DIV'] == '20' or $onedata[0]['PAY_DIV'] == '30' ){
					$azsql .="
						, RankCode
						, RealRankCode
						, groupcode
						, mobile
					";
				}elseif( $MODE_TAG_CHECK > 0 ){
					$azsql .="
						, RankCode
						, RealRankCode
						, groupcode
					";
				}

				$azsql .="
					) VALUES (
						/* memberNo		*/   '".$onedata[0]['MEMBERNO']."'
						/* Pasword		*/ , '00000'
						/* WorkPosition	*/ , ".$onedata[0]['WORKPOSITION']."
						/* korName		*/ , '".$this->HangleEncode($onedata[0]['KORNAME'])."'
						/* chiName		*/ , '".$this->HangleEncode($onedata[0]['CHINAME'])."'
						/* engName		*/ , '".$this->HangleEncode($onedata[0]['ENGNAME'])."'
						/* entrydate	*/ , '".$onedata[0]['ENTRYDATE']."'
						/* juminno		*/ , '".$onedata[0]['JUMINNO']."'
						/* phone		*/ , '".str_replace(" ","",$onedata[0]['PHONE'])."'
						/* email		*/ , '".$onedata[0]['EMAIL']."'
						/* orignaddress	*/ , '".$this->HangleEncode($onedata[0]['ORIGNADDRESS'])."'
						/* address		*/ , '".$this->HangleEncode($onedata[0]['ADDRESS'])."'
						/* birthday		*/ , '".$onedata[0]['BIRTHDAY']."'
						/* LeaveDate	*/ , '".$onedata[0]['LEAVEDATE']."'
						/* Company		*/ , '".$onedata[0]['COMPANY']."'
				";

				if( $onedata[0]['PAY_DIV'] == '20' or $onedata[0]['PAY_DIV'] == '30' ){
					$azsql .="
						/* RankCode		*/ , ( select Code from systemconfig_tbl where SysKey = 'PositionCode' and Name = '".$this->HangleEncode($onedata[0]['GRADE_NAME'])."' order by Code limit 1 )
						/* RealRankCode	*/ , ( select Code from systemconfig_tbl where SysKey = 'PositionCode' and Name = '".$this->HangleEncode($onedata[0]['AAAAA'])."' order by Code limit 1 )
						/* groupcode	*/ , ".$onedata[0]['GROUPCODE']."
						/* mobile		*/ , '".$onedata[0]['MOBILE']."'
					";
				}elseif( $MODE_TAG_CHECK > 0 ){
					$azsql .="
						/* RankCode		*/ , ( select Code from systemconfig_tbl where SysKey = 'PositionCode' and Name = '".$this->HangleEncode($onedata[0]['GRADE_NAME'])."' order by Code limit 1 )
						/* RealRankCode	*/ , ( select Code from systemconfig_tbl where SysKey = 'PositionCode' and Name = '".$this->HangleEncode($onedata[0]['AAAAA'])."' order by Code limit 1 )
						/* groupcode	*/ , ".$onedata[0]['GROUPCODE']."
					";
				}

				$azsql .="
					)
					ON DUPLICATE KEY
					UPDATE
						WorkPosition	= '".$onedata[0]['WORKPOSITION']."'
						, chiName		= '".$this->HangleEncode($onedata[0]['CHINAME'])."'
						, engName		= '".$this->HangleEncode($onedata[0]['ENGNAME'])."'
						, entrydate		= '".$onedata[0]['ENTRYDATE']."'
						, juminno		= '".$onedata[0]['JUMINNO']."'
						, phone			= '".str_replace(" ","",$onedata[0]['PHONE'])."'
						, email			= '".$onedata[0]['EMAIL']."'
						, orignaddress	= '".$this->HangleEncode($onedata[0]['ORIGNADDRESS'])."'
						, address		= '".$this->HangleEncode($onedata[0]['ADDRESS'])."'
						, birthday		= '".$onedata[0]['BIRTHDAY']."'
						, LeaveDate		= '".$onedata[0]['LEAVEDATE']."'
						, Company		= '".$onedata[0]['COMPANY']."'
				";

				if( $onedata[0]['PAY_DIV'] == '20' or $onedata[0]['PAY_DIV'] == '30' ){
					$azsql .="
						, RankCode		= ( select Code from systemconfig_tbl where SysKey = 'PositionCode' and Name = '".$this->HangleEncode($onedata[0]['GRADE_NAME'])."' order by Code limit 1 )
						, RealRankCode	= ( select Code from systemconfig_tbl where SysKey = 'PositionCode' and Name = '".$this->HangleEncode($onedata[0]['AAAAA'])."' order by Code limit 1 )
						, groupcode		= ".$onedata[0]['GROUPCODE']."
						, mobile		= '".$onedata[0]['MOBILE']."'
					";
				}elseif( $MODE_TAG_CHECK > 0 ){
					$azsql .="
						, RankCode		= ( select Code from systemconfig_tbl where SysKey = 'PositionCode' and Name = '".$this->HangleEncode($onedata[0]['GRADE_NAME'])."' order by Code limit 1 )
						, RealRankCode	= ( select Code from systemconfig_tbl where SysKey = 'PositionCode' and Name = '".$this->HangleEncode($onedata[0]['AAAAA'])."' order by Code limit 1 )
						, groupcode		= ".$onedata[0]['GROUPCODE']."
					";
				}

				if($action_sql){
					$this->insert_insa_log($azsql);
					mysql_query($azsql, $db);
				}else{
					echo '<br><DIV>'.$azsql.'</DIV>';
				}

				//근무회사 정보수정
				if( ( $onedata[0]['PAY_DIV'] == '20' or $onedata[0]['PAY_DIV'] == '30' ) and $onedata[0]['WORKING_COMPANY'] != '20' ){
					$azsql =" INSERT INTO HR_PERS_MASTER_MAPPING ( emp_no, mode_tag, contents, input_dt ) values ( '$EMP_NO', 'OUT', 'OUT', TO_CHAR( SYSDATE, 'YYYYMMDDHH24MISS' ) ) ";
					if($action_sql){
						$this->insert_insa_log($azsql);
						$this->oracle->ProcedureExcuteQuery($azsql);
					}else{
						echo '<BR><DIV>'.$azsql.'</DIV>';
					}
				}

				// 수정된 정보에 대한 내용을 삭제한다
				$azsql =" UPDATE HR_PERS_MASTER_MAPPING SET CONTENTS = 'Y_'||CONTENTS WHERE EMP_NO = '$EMP_NO' AND CONTENTS = '$contents' ";
				if($action_sql){
					$this->insert_insa_log($azsql);
					$this->oracle->ProcedureExcuteQuery($azsql);
				}else{
					echo '<BR><DIV>'.$azsql.'</DIV>';
				}
			}

			if( $contents != 'N' ){
				$azsql ="select EMP_NO, CONTENTS from HR_PERS_MASTER_MAPPING where CONTENTS = 'OUT' group by EMP_NO, CONTENTS";
				if(!$action_sql){
					echo '<BR><BR><DIV>'.$azsql.'</DIV>';
				}
				$this->insert_insa_log($azsql);
				$datalist=$this->oracle->LoadData($azsql,"");

				if($action_sql){
					/*삼안 DB정보*/
					$db_hostname_SAMAN = 'erp.samaneng.com';
					$db_database_SAMAN = 'hallaerp';
					$db_username_SAMAN = 'root';
					$db_password_SAMAN = 'vbxsystem';

					/*삼안 DB연결----------------------------------------------------------------------*/
					$db_saman	= mysql_connect($db_hostname_SAMAN,$db_username_SAMAN,$db_password_SAMAN);
						if(!$db_saman) die ("Unable to connect to MySql : ".mysql_error());
					/*-----------------------------------------------------------------------*/

					mysql_select_db($db_database_SAMAN);
					mysql_set_charset("utf-8",$db_saman);

					/*장헌 DB정보*/
					$db_hostname_JANG = 'erp.jangheon.co.kr';
					$db_database_JANG = 'hanmacerp';
					$db_username_JANG = 'root';
					$db_password_JANG = 'erp';

					/*장헌 DB연결----------------------------------------------------------------------*/
					$db_jang	= mysql_connect($db_hostname_JANG,$db_username_JANG,$db_password_JANG);
						if(!$db_jang) die ("Unable to connect to MySql : ".mysql_error());
					/*-----------------------------------------------------------------------*/

					mysql_select_db($db_database_JANG);
					mysql_set_charset("utf-8",$db_jang);

					/*한라 DB정보*/
					$db_hostname_halla = 'intranet.hallasanup.com';
					$db_database_halla = 'hallaerp';
					$db_username_halla = 'root';
					$db_password_halla = 'vbxsystem';

					/*한라 DB연결----------------------------------------------------------------------*/
					$db_halla	= mysql_connect($db_hostname_halla,$db_username_halla,$db_password_halla);
						if(!$db_halla) die ("Unable to connect to MySql : ".mysql_error());
					/*-----------------------------------------------------------------------*/

					mysql_select_db($db_database_halla);
					mysql_set_charset("utf-8",$db_halla);

					/*PTC DB정보*/
					$db_hostname_ptc = 'erp.pre-cast.co.kr';
					$db_database_ptc = 'hanmacerp';
					$db_username_ptc = 'root';
					$db_password_ptc = 'erp';

					/*PTC DB연결----------------------------------------------------------------------*/
					$db_ptc	= mysql_connect($db_hostname_ptc,$db_username_ptc,$db_password_ptc);
						if(!$db_ptc) die ("Unable to connect to MySql : ".mysql_error());
					/*-----------------------------------------------------------------------*/

					mysql_select_db($db_database_ptc);
					mysql_set_charset("utf-8",$db_ptc);
				}

				for( $index = 0; $index < count($datalist); $index++ ){
					if(!$action_sql){
						ECHO '<BR>';
						print_r($datalist[$index]);
						ECHO '<BR>';
					}
					$CONTENTS = $datalist[$index]['CONTENTS'];
					$EMP_NO = $datalist[$index]['EMP_NO'];

					//정보 가져오기
					$azsql = "
						SELECT
							A.EMP_NO AS MEMBERNO
							, CASE
								WHEN A.SERVICE_DIV = '3' AND TO_CHAR(SYSDATE, 'YYYYMMDD') > NVL(A.RETIRE_DATE, '00000000') THEN '9'
								ELSE A.SERVICE_DIV
							END AS WORKPOSITION
							, ( select GRADE_NAME from HR_CODE_GRADE where B.GRADE_CODE = GRADE_CODE ) AS GRADE_NAME
							, A.EMP_NAME AS KORNAME
							, A.EMPNAME_CHI AS CHINAME
							, A.EMPNAME_ENG AS ENGNAME
							, DECODE( A.SEX_DIV, 1, 'M', 'F' ) AS GENDER
							, A.GROUP_JOIN_DATE AS ENTRYDATE
							, A.RRN_PRE || '-' || A.RRN_POST AS JUMINNO
							, replace(A.PHONE_PRE, '-', '')||replace(A.PHONE_POST, '-', '') AS PHONE
							, A.CELL_PRE || A.CELL_POST AS MOBILE
							, A.E_MAIL AS EMAIL
							, A.RRN_ADDR1 || ' ' || A.RRN_ADDR2 AS ORIGNADDRESS
							, A.ADDR1 || ' ' || A.ADDR2 AS ADDRESS
							, NVL( A.BIRTHDAY, '00000000' ) AS BIRTHDAY
							, ( select max(proj_code) from CS_CONT_MAP_MASTER where a.dept_code = proj_org_code ) as SITECODE
							, A.TITLE_CODE AS DUTYCODE
							, B.ERP_DEPT_CODE AS GROUPCODE
							, NVL( A.RETIRE_DATE, '00000000' ) AS LEAVEDATE

							, B.REALRANk as AAAAA
							, B.REALRANk
							, B.GRADE_CODE
							, B.PAY_DIV
							, B.WORKING_COMPANY
							, B.WORKING_DEPT
							, B.WORKING_RANK_CODE
							, B.WORKING_RANK_NAME
							, CASE B.PAY_DIV
								WHEN '10' THEN 'SAMAN'
								WHEN '20' THEN 'HANMAC'
								WHEN '30' THEN 'BARON'
								WHEN '40' THEN 'JANG'
								WHEN '50' THEN 'HALLA'
								WHEN '60' THEN 'PTC'
							END AS COMPANY
						FROM
							HR_PERS_MASTER A
							, HR_ORDE_MASTER B
						WHERE
							A.EMP_NO = '$EMP_NO'
							AND B.EMP_NO = '$EMP_NO'
							AND B.FINAL_TAG = 'Y'
							AND B.CONFIRM_TAG = 'Y'
							AND A.EMP_NO = B.EMP_NO
					";
					if(!$action_sql){
						echo '<br><DIV>'.$azsql.'</DIV>';
					}

					//$this->insert_insa_log($azsql);
					$onedata = $this->oracle->LoadData($azsql,"");

					if(!$action_sql){
						//print_r($onedata);
					}

					//확정이면서 최종인 데이터가 없을때 패스
					if($onedata[0]['KORNAME'] == ''){
						continue;
					}

					//부서, 직위, 휴대폰
					$azsql = "
						update member_tbl set
							Mobile			= '".$onedata[0]['MOBILE']."'
							, GroupCode		= '".$onedata[0]['WORKING_DEPT']."'
							, RankCode		= '".$onedata[0]['WORKING_RANK_CODE']."'
					";
					if( $onedata[0]['WORKING_COMPANY'] == '10' or $onedata[0]['WORKING_COMPANY'] == '50' ){	//삼안, 한라는 직위명칭도 있음.
						$azsql .= "
							, ViewRankName		= '".$this->HangleEncode($onedata[0]['WORKING_RANK_NAME'])."'
						";
					}

					$azsql .= "
						where
							korName = '".$this->HangleEncode($onedata[0]['KORNAME'])."'
							and juminno = '".$onedata[0]['JUMINNO']."'
							and EntryDate = ( select max(EntryDate) from member_tbl where juminno = '".$onedata[0]['JUMINNO']."' and korName = '".$this->HangleEncode($onedata[0]['KORNAME'])."' )
					";

					if($action_sql){
						$this->insert_insa_log($azsql);
						if( $onedata[0]['WORKING_COMPANY'] == '10' ){	//삼안
							mysql_query($azsql, $db_saman);
						}elseif( $onedata[0]['WORKING_COMPANY'] == '20' or $onedata[0]['WORKING_COMPANY'] == '30' ){	//한맥, 바론
							mysql_query($azsql, $db);
						}elseif( $onedata[0]['WORKING_COMPANY'] == '40' ){	//장헌
							mysql_query($azsql, $db_jang);
						}elseif( $onedata[0]['WORKING_COMPANY'] == '50' ){	//한라
							mysql_query($azsql, $db_halla);
						}elseif( $onedata[0]['WORKING_COMPANY'] == '60' ){	//PTC
							mysql_query($azsql, $db_ptc);
						}
					}else{
						echo '<BR><DIV>'.$onedata[0]['WORKING_COMPANY'].' - '.$azsql.'</DIV>';
					}

					// 수정된 정보에 대한 내용을 삭제한다
					$azsql =" UPDATE HR_PERS_MASTER_MAPPING SET CONTENTS = 'Y_'||CONTENTS WHERE EMP_NO = '$EMP_NO' AND CONTENTS = 'OUT' ";
					if($action_sql){
						$this->insert_insa_log($azsql);
						$this->oracle->ProcedureExcuteQuery($azsql);
					}else{
						echo '<BR><DIV>'.$azsql.'</DIV>';
					}
				}

				if($action_sql){
					mysql_close($db_saman);
					mysql_close($db_jang);
					mysql_close($db_halla);
					mysql_close($db_ptc);
				}
			}

			$ToDayEndTime = date('Y-m-d H:i:s');
			$aa = $ToDayStartTime."~".$ToDayEndTime." ".(strtotime($ToDayEndTime) - strtotime($ToDayStartTime))."Sec  ===JMJ=WorkSynchronizationActionEnd/InsaAction";

			if( $contents != 'N' ){
				$cfile="../../InputUserState.txt";
				$fd=fopen($cfile,'r');
				$con=fread($fd,filesize($cfile));
				fclose($fd);

				$fp=fopen($cfile,'w');
				$cond=$con.$aa."\n";
				fwrite($fp,$cond);
				fclose($fp);
			}
			echo $aa."<br>";
		}

		function HangleEncode($item)
		{
				$result=trim(ICONV("EUC-KR","UTF-8",$item));
				//if(trim($result)=="") 	$result="&nbsp";
				return $result;
		}

		function HangleEncodeUTF8_EUCKR($item)
		{
				$result=trim(ICONV("UTF-8","EUC-KR",$item));
				return $result;
		}

		function bear3StrCut($str,$len,$tail="..."){
			$rtn = array();
			return preg_match('/.{'.$len.'}/su', $str, $rtn) ? $rtn[0].$tail : $str;
		}
		//=================================================
		// POST로 입력받은 자료를 처리하는 함수
		//=================================================
		function GetPOST_Item($Section)
		{
				$query_item=$_POST[$Section];
				$query_item=$this->HangleEncodeUTF8_EUCKR($query_item);
				return $query_item;
		}

		function insert_insa_log($azsql){
			$log_txt = date("Y-m-d H:i:s",time()).", ".$azsql."/n/r";
			$log_file = "../log/".date("Y-m-d",time())."_insa_synchro_log.txt";
			if(is_dir($log_file)){
				$log_option = 'w';
			}else{
				$log_option = 'a';
			}

			$log_file = fopen($log_file, $log_option);
			fwrite($log_file, $log_txt."\r\n");
			fclose($log_file);
		}
	}

	$dept = new InsaSynchronization();
	$dept->SynchProcess();

?>
