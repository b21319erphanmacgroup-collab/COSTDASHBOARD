<?php
	//외부서버에서 ajax 허용
	//header('Access-Control-allow-Origin:*');
	//header('Content-Type: text/html; charset=UTF-8');
	/******************************************************************************
	* 삼안 SATIS에서 변경된 인사관련된 정보를 인트라넷에 적용하기 위한 클래스
	* -----------------------------------------------------------------------------
	*  작업일자   |  작업자   | 작업 내용
	* 2021-02-17  |  정명준   | 생성
	*******************************************************************************/
	include "../inc/dbcon.inc";

	include "../util/OracleClass.php";

	extract($_REQUEST);
	class Synchronization {
		function Synchronization(){
			$this->oracle=new OracleClass('', 'SAMAN');
		}

		//=================================================
		// 로그인에 대한 정보
		//=================================================
		function Process($id){
			global $db;
			extract($_REQUEST);
			$action_sql = true;		// true false
			if(!$action_sql){
				print_r($_REQUEST);
				echo '<br><br>';
			}

			$azsql = "select id from notice_new_tbl where MemberNo = '$memberID' order by id desc limit 1";
			//문자정보 가져오기.
			$azsql ="
				select
					id
					, title
					, comment
					, MemberNo
					, IFNULL( ( select IFNULL( IFNULL( Mobile, Phone ), '01033227515') from member_tbl WHERE WorkPosition in ( 1, 2, 8 )  AND MemberNo = A.MemberNo ), '01033227515') as Mobile
				from
					notice_new_tbl A
				where
					group_code = 99
					and email = '1'
					and MemberNo = '$memberID'
					and sms_check = 0
				order by
					id desc
				limit 1
				;
			";
			if(!$action_sql){
				echo $azsql.'<br><br>';
			}
			$re = mysql_query($azsql,$db);
			while($re_row = mysql_fetch_array($re)) {
				//print_r($re_row);

				$accesskey = $this->GenerateString(12);
				$send_text = '[바론컨설턴트]'.$this->add_enter();
				//if( strpos( $re_row['comment'], '<img src=' ) !== false ) {
					//echo $re_row['id'];
					//echo '<br>';
					//echo " 이미지 ";
					//echo '<br>';

					$send_text .= $re_row['title'].$this->add_enter()."http://erp.hanmaceng.co.kr/view/?id=".$re_row['id']."&key=$accesskey";
				//} else {
					//echo $re_row['id'];
					//echo '<br>';
					//echo " 텍스트 ";
					//echo '<br>';

					//$send_text .= $re_row['comment'];
				//}

				//echo '<br>';

				// 문자내용 SATIS에 전송 -> 받는사람 번호, 보낼사람 번호, 내용, 문자종류(SMS:일반문자, LML:장문문자)
				if( $re_row['MemberNo'] == 'M20330' ){
					//정명준
					$azsql = " BEGIN usp_sms_send_iu( '01030622026', '".$re_row['Mobile']."', '".$this->HangleEncodeUTF8_EUCKR( $send_text )."', 'LMS' ); END; " ;
					if($action_sql){
						$this->oracle->ProcedureExcuteQuery($azsql);
					}else{
						echo $azsql.'<br><br>';
					}
					//김병철
					$azsql = " BEGIN usp_sms_send_iu( '01030167065', '".$re_row['Mobile']."', '".$this->HangleEncodeUTF8_EUCKR( $send_text )."', 'LMS' ); END; " ;
					if($action_sql){
						//$this->oracle->ProcedureExcuteQuery($azsql);
					}else{
						echo $azsql.'<br><br>';
					}
				}else{
					//정명준
					$azsql = " BEGIN usp_sms_send_iu( '01030622026', '".$re_row['Mobile']."', '".$this->HangleEncodeUTF8_EUCKR( $send_text )."', 'LMS' ); END; " ;
					if($action_sql){
						$this->oracle->ProcedureExcuteQuery($azsql);
					}else{
						echo $azsql.'<br><br>';
					}
					//김윤하
					$azsql = " BEGIN usp_sms_send_iu( '01033227515', '".$re_row['Mobile']."', '".$this->HangleEncodeUTF8_EUCKR( $send_text )."', 'LMS' ); END; " ;
					if($action_sql){
						$this->oracle->ProcedureExcuteQuery($azsql);
					}else{
						echo $azsql.'<br><br>';
					}
					//장종찬
					$azsql = " BEGIN usp_sms_send_iu( '01054631677', '".$re_row['Mobile']."', '".$this->HangleEncodeUTF8_EUCKR( $send_text )."', 'LMS' ); END; " ;
					if($action_sql){
						$this->oracle->ProcedureExcuteQuery($azsql);
					}else{
						echo $azsql.'<br><br>';
					}
					//한형관
					// $azsql = " BEGIN usp_sms_send_iu( '01037181890', '".$re_row['Mobile']."', '".$this->HangleEncodeUTF8_EUCKR( $send_text )."', 'LMS' ); END; " ;
					// if($action_sql){
					// 	//$this->oracle->ProcedureExcuteQuery($azsql);
					// }else{
					// 	echo $azsql.'<br><br>';
					// }
				}



				//echo '<br><br><br>';

				$azsql = "update notice_new_tbl set sms_check = 1, accesskey = '$accesskey' where id = ".$re_row['id'];

				if($action_sql){
					//$this->insert_insa_log($azsql);
					mysql_query($azsql, $db);
				}else{
					echo $azsql.'<br><br>';
				}
			}
		}

		function add_enter(){
			return '
';
		}

		//----문자생성------------------
		function GenerateString($nmr_loops){
			$characters = "0123456789";
			$characters .= "abcdefghijklmnopqrstuvwxyz";
			$characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$characters .= "_";
			$string_generated = "";
			for( $i=0; $i<$nmr_loops; $i++ ){
				$string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];
			}
			return $string_generated;
		}
		//----문자생성------------------
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
			$log_file = "../log/".date("Y-m-d",time())."_insa_link_log.txt";
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

	//$dept = new Synchronization();
	//$dept->Process();

?>
