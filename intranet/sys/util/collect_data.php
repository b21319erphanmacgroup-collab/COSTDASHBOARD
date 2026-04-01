<?php
	extract($_POST);
	//echo "_POST";
	//print_r($_POST);

	//세션 시작
	session_start();
	//echo "_SESSION";
	//print_r($_SESSION);

	// 로그 시작 //////////////////////////////////////////////////////////////

	$log_file = "../log/".date("Y-m-d")."_salary.txt";
	if(is_dir($log_file)){
		$log_option = 'w';
	}else{
		$log_option = 'a';
	}

	$log_txt = date("Y-m-d H:i:s",time())." collect_data.php SESSION(memberID) : ".$_SESSION['memberID']." ".$_SERVER["HTTP_REFERER"]." ".$_SERVER["REMOTE_ADDR"]." ".$security." ".base64_encode($_SESSION['memberID'])." ".json_encode($_POST);
	$log_file = fopen($log_file, $log_option);
	fwrite($log_file, $log_txt."\r\n");
	fclose($log_file);

	// 로그 종료 //////////////////////////////////////////////////////////////

	if( $_SESSION['memberID'] == '' or base64_encode($_SESSION['memberID']) != $_POST['member_id']){
		?>
		<html><head><meta charset="utf-8" /><script type="text/javascript"><!--
			alert('사번을 찾을 수 없습니다. 재로그인 해주시기바랍니다.');
			window.close();
		//--></script></head></html><?
		return false;
	}

	include "../inc/dbcon.inc";

	$azsql =" select JuminNo from member_tbl where memberno = '".$_SESSION['memberID']."' ";

	$re = mysql_query($azsql,$db);
	while($re_row = mysql_fetch_array($re)) {
		$jumin = $re_row['JuminNo'];
	}


	//
	$url = 'http://erp.hanmaceng.co.kr/CompanyMember/sys/controller/filtering_controller.php';
	$values_array = array(
		'comp'		=> 'saman_intra',
		'ActionMode'	=> 'month_money_report_temp',
		// 서버 구분코드 10:삼안 20:한맥  40:장헌 50:한라 60:PTC 70:현타
		//'sys_comp_code'	=> $_POST['sys_comp_code'],
		'sys_comp_code'	=> base64_encode( '30' ),
		//'member_id'		=> $_POST['member_id'],
		'memberID'		=> base64_encode( $_SESSION['memberID'] ),
		'jumin'			=> base64_encode( $jumin )
	);

	echo("<form name='redir_form' action='$url' method='post'>");
	foreach ($values_array as $key => $value) {
		echo("<input type='hidden' name='$key' value='$value'>");
	}
	echo("</form> <script language='javascript'> document.redir_form.submit(); </script>");

?>