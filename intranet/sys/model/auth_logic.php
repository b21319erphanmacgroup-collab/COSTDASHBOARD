<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php

	/***************************************
	* 권한설정
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH
	****************************************/

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../util/HanamcPageControl.php";


	extract($_POST);
	class AuthLogic {
		var $smarty;
		function AuthLogic($smarty)
		{
			$this->smarty=$smarty;
		}


		//============================================================================
		// 개인별 설정보기
		//============================================================================
		function UpdateReadPage()
		{
			include "../inc/approval_function.php";
			global $db,$memberID,$SearchID;

			$MemberName=MemberNo2Name($SearchID);


			$sql="select * from member_tbl where MemberNo='$SearchID'";

			$re = mysql_query($sql,$db);
			$Certificate = mysql_result($re,0,"Certificate");

			// 권한 colspan 사이즈
			$row_cnt = array();
			$sql ="select Note, count(Note) as row_cnt from systemconfig_tbl where SysKey='intra_auth' group by Note";
			$re = mysql_query($sql);
			while( $re_row = mysql_fetch_array($re) ){
				$row_cnt[$re_row['Note']] = $re_row['row_cnt'];
			}

			//권한설정 목록
			$list_data = array();
			$ex_note = '';
			$sql ="select * from systemconfig_tbl where SysKey='intra_auth' order by orderno";
			$re = mysql_query($sql);
			while( $re_row = mysql_fetch_array($re) ){
				if( $ex_note != $re_row['Note'] ){	//권한 colspan
					$re_row['row_cnt'] = $row_cnt[$re_row['Note']];
					$ex_note = $re_row['Note'];
				}else{
					$re_row['row_cnt'] = 0;
				}

				if($Certificate) {	//권한 체크
					if(strpos($Certificate,$re_row['Code']) !== false) { $re_row['checked'] = true; }
				}


				array_push( $list_data, $re_row );
			}
			//print_r($list_data);

			$this->smarty->assign('list_data',$list_data);
			$this->smarty->assign('MemberName',$MemberName);
			$this->smarty->assign('SearchID',$SearchID);

			$this->smarty->display("intranet/common_contents/work_auth/auth_detail.tpl");
		}

		//============================================================================
		// 개인별 설정저장
		//============================================================================
		function UpdateAction()
		{

			global $db,$memberID,$SearchID;
			global $cb,$cbv,$MemberName;

			$Certificate = ',';
			foreach($cb as $key => $value){
				$Certificate .= $value.",";
			}
			$sql = "update member_tbl set Certificate='$Certificate' where MemberNo ='$SearchID'";
			//echo $sql;

			$re = mysql_query($sql,$db);

			$cfile="../log/auth_loginInfo.txt";
			$exist = file_exists($cfile);
			if($exist) {
				$fd=fopen($cfile,'r');
				$con=fread($fd,filesize($cfile));
				fclose($fd);
			}

			$fp=fopen($cfile,'w');
			$aa=date("Y-m-d H:i");

			$ip=$_SERVER['REMOTE_ADDR'];

			$cond=$con.$aa." ".$SearchID."-".$MemberName."-".$Certificate."-".$memberID." \n";
			fwrite($fp,$cond);
			fclose($fp);

			$this->smarty->assign('target',"opener");
			$this->smarty->assign('MoveURL',"auth_controller.php?ActionMode=view");
			$this->smarty->display("intranet/move_page.tpl");

		}

		//============================================================================
		// 전체 사용자 설정 보기
		//============================================================================
		function View()
		{

			global $db,$memberID;
			$query_data = array();

			$sql= "      SELECT																		";
			$sql= $sql."	 a.MemberNo			as a_MemberNo									";	//사원번호
			$sql= $sql."	,a.korName			as a_korName									";	//한글이름
			$sql= $sql."	,a.Certificate		as a_Certificate								";	//권한
			$sql= $sql."	,a.Name				as a_position									";	//직위
			$sql= $sql."	,b.Name				as a_GroupName									";	//부서명
			$sql= $sql." FROM																	";
			$sql= $sql." (                                                                 		";
			$sql= $sql." 	select * from                                                 		";
			$sql= $sql." 	( select * from member_tbl where WorkPosition = 1 order by GroupCode,RankCode,MemberNo )a1     ";
			$sql= $sql." 	 left JOIN                                                 		    ";
			$sql= $sql." 	( select * from systemconfig_tbl where SysKey='PositionCode' )a2	";
			$sql= $sql." 	 on a1.RankCode = a2.code                                  		    ";
			$sql= $sql." ) a left JOIN                                                     		";
			$sql= $sql." ( select * from systemconfig_tbl where SysKey='GroupCode'  )b          ";
			$sql= $sql."  on a.GroupCode = b.code												";

			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			while($re_row = mysql_fetch_array($re))
			{
				array_push($query_data,$re_row);
			}


			if(!$_SESSION[n_num]){
				echo "<script>location.href='http://erp.hanmaceng.co.kr/auth_mng/index.php'</script>";
			}

			$this->smarty->assign('query_data',$query_data);				$this->smarty->display("intranet/common_contents/work_auth/auth_mvc.tpl");


		}


        //============================================================================
        // 토큰 가져오기  2025.05.09 김진선
        // $RANDSTR : 월별 암호화키값
        // $token_data : 암호화할 데이터
        //============================================================================
        function getToken() {
            $token_data = array(
                'COMP_NAME'    => '바론',
                'COMP_CODE'    => $_SESSION['SS_CompanyKind'],
                'DEPT_NAME'    => $_SESSION['SS_GroupName'],
                'SITE_CODE'    => 'I',
                'EMP_NO'       => $_SESSION['SS_memberID'],
                'EMP_NAME'     => $_SESSION['SS_korName'],
                'RANK_NAME'    => $_SESSION['SS_position'],
                'AUTH_PAGE_ID' => ''
            );
            $RANDSTR = array(
                "KYyxEhGpfyIFyUK5sGS6", "nmWpDCe2hnGo0amx9rLx", "lQglH1tkmmfALBTsskvy",
                "0RVNIw3WtNQzIDHjwMUY", "Q1ZQLdWiockxXKFfRYG4", "g2f8wim4GceCy9an7Ia4",
                "nn3V4Br047CVNFliOB4T", "Wt7yEIjAMG3j3ICq3qA2", "zDMskk5JQNDDkv8safEL",
                "39Fdi9owMSq1FlemcTRt", "DEkH6URm1pMPfQfCewj4", "efZ5N0En5SscGeDBkYCt"
            );

            $token_data = $this->json_encode_unescaped_unicode($token_data);

            $MONTH_STR = date('m');
            $SECRET_KEY = $RANDSTR[(int)$MONTH_STR - 1];
            $encryption_key = $SECRET_KEY . date('YmdHi');
            $iv = substr(hash('sha256', $encryption_key), 0, 16);

            $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encryption_key, $token_data, MCRYPT_MODE_CBC, $iv);
            $result = rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');

            echo $result;
        }

        /** php5.1미만 버전
         * json_encode($save_data, JSON_UNESCAPED_UNICODE) 대응
         */
        function json_encode_unescaped_unicode($data) {
            return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', array($this, 'json_unescaped_unicode_callback'), json_encode($data));
        }
        function json_unescaped_unicode_callback($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
        }

}
?>