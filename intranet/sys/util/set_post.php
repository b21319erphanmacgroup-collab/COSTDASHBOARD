<html>
	<head>
		<meta charset="utf-8" />
		<script type="text/javascript">
		<!--
			function submit_post(){
				//post로 받은건 안됨.
				var form = document.createElement("form");

				form.setAttribute("charset", "UTF-8");
				form.setAttribute("method", "Post");  //Post 방식
				form.setAttribute("action", "<?=$_GET['Controller']?>"); //요청 보낼 주소

				<?
					foreach( $_GET as $key => $value ){
					?>
						var hiddenField = document.createElement("input");
						hiddenField.setAttribute("type", "hidden");
						hiddenField.setAttribute("name", "<?=$key?>");
						hiddenField.setAttribute("value", "<?=$value?>");
						form.appendChild(hiddenField);
					<?
					}
				?>

				document.body.appendChild(form);
				form.submit();
			}
		//-->
		</script>
	</head>
	<body onload='submit_post();'></body>
</html>