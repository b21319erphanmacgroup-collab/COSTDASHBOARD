<?php
	session_start();

	class LoginInfomation
	{
		function LoginInfomation()
		{
			// 내용없음
		}
		/* ******************************************************************************************************* */
		function GetLoginStatus()
		{
			global $CK_memberID;
			global $get_memberID;
			$checkLogin="";

			if($_SESSION['memberID']!=""){
				$checkLogin = $_SESSION['memberID'];
			}

			if($checkLogin=="" && $_COOKIE['CK_memberID']!=""){
				$checkLogin = $_COOKIE['CK_memberID'];
			}

			if($checkLogin=="" && $_GET['get_memberID']!=""){
				$checkLogin = $_GET['get_memberID'];

				if($checkLogin !="")
				{
					$_SESSION['memberID']=$checkLogin;
				}
			}//if End


			//if($_SESSION['memberID'] == ""){
			  if( $checkLogin== ""){
			 echo "
			 <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
			  <script>
			   parent.document.location.href='/intranet/index.php?logoutFlag=2';
				alert('로그인후 사용하세요');
			  </script>
			 ";
				//echo "<meta http-equiv='refresh' content='0; url=http://192.168.2.243/intranet/'>";
				//header("Location: http://192.168.2.243/intranet/");
				return false;
			}else{
				return true;
			}
		}//GetLoginStatus End

		/* ******************************************************************************************************* */
		function GetLoginSessionStatus()
		{
			global $CK_memberID;
			global $get_memberID;
			$checkLogin="";

			if($_SESSION['memberID']!=""){
				$checkLogin = $_SESSION['memberID'];
			}

			if($checkLogin=="" && $_COOKIE['CK_memberID']!=""){
				$checkLogin = $_COOKIE['CK_memberID'];
			}

			if($checkLogin=="" && $_GET['get_memberID']!=""){
				$checkLogin = $_GET['get_memberID'];
			}//if End

			if($_SESSION['memberID'] == ""){
			 echo "
			 <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
			  <script>
			   parent.document.location.href='/intranet/index.php?logoutFlag=2';
				alert('로그인후 사용하세요');
			  </script>
			 ";

				return false;
			}else{
				return true;
			}
		}//GetLoginStatus End
		/* ******************************************************************************************************* */
		function GetLoginStatusPop()
		{
			global $CK_memberID;
			global $get_memberID;
			$checkLogin="";

			if($get_memberID !="")
			{
				$_SESSION['memberID']=$get_memberID;
			}

			if($_SESSION['memberID']!=""){
				$checkLogin = $_SESSION['memberID'];

			}else if($_COOKIE['CK_memberID']!=""){
				$checkLogin = $_COOKIE['CK_memberID'];

			}else if($_GET['memberID']!=""){
				$checkLogin = $_GET['memberID'];

			}else if($get_memberID!=""){
				$checkLogin = $get_memberID;

			}//if End


		/*세션확인메세지 뜨는경우가 잇어 주석처리함 2015-02-16 주영진DR 확인요망
			//if($_SESSION['memberID'] == ""){
			  if( $checkLogin == ""){
				echo "
				<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
				<script>
				alert('관리자에게 문의하세요(세션값확인:GetLoginStatusPop)');
				window.close();
				</script>
				";
			return false;
			}else{
				return true;
			}
		*/
		}//GetLoginStatusPop End
		/* ******************************************************************************************************* */
		function GetLoginStatus2()
		{
			global $CK_memberID;
			global $get_memberID;
			$checkLogin="";

			if($_SESSION['memberID']!=""){
				$checkLogin = $_SESSION['memberID'];

			}else if($_COOKIE['CK_memberID']!=""){
				$checkLogin = $_COOKIE['CK_memberID'];

			}else if($_GET['memberID']!=""){
				$checkLogin = $_GET['memberID'];

			}else if($get_memberID!=""){
				$checkLogin = $get_memberID;

			}//if End



			//if($_SESSION['memberID'] == ""){
			if( $checkLogin == ""){

				 echo "
				  <script>
				   parent.document.location.href='/intranet/index.php?logoutFlag=2';
					alert('You need to LOGIN(Time expired)');
				  </script>
				 ";
				/*
				 echo "
				 <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
				  <script>
				   parent.document.location.href='/intranet/index.php?logoutFlag=2';
					alert('로그인후 사용하세요(시간만료)');
				  </script>
				 ";
				*/
				return false;
			}else{
				return true;
			}


		}//GetLoginStatus End
		/* ******************************************************************************************************* */

	}//class LoginInfomation End

	//======================================================
	// 개인별 접근 권하는 읽어오는 Class
	//======================================================
	//include "/intranet/sys/inc/dbcon.inc";
	class PersonAuthority
	{
		function PersonAuthority()
		{
			// 내용없음
		}
		/* ******************************************************************************************************* */
		//=============================================================================
		// 각 멤버별로 접근 권한을 점검하는 함수
		//=============================================================================
		function GetInfo($memberID,$Item)
		{
			global $db;

			$sql = "select * from member_tbl where memberno='$memberID' and  Certificate like '%$Item%'";
			//echo $sql."<br>";
			$re = mysql_query($sql,$db);
			if($re_row = mysql_fetch_array($re))
			{
				return true;
			}
		}//GetInfo End
		/* ******************************************************************************************************* */

	}//class PersonAuthority End
?>