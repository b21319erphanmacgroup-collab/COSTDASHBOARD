<!DOCTYPE html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0" />
		<!-- 보안 관련 meta -->
	    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline'; img-src 'self' data:; worker-src blob:;" />
	    <meta http-equiv="X-Content-Type-Options" content="nosniff" />
	    <meta http-equiv="X-XSS-Protection" content="1; mode=block" />
	    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin" />
	    
		<title>DASH BOARD</title>
		
		<!-- <link rel="preconnect" href="https://fonts.googleapis.com">
	    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	    <link href="https://fonts.googleapis.com/css2?family=Saira:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet"> -->
	    
		<!-- 스타일 -->
	    <link rel="stylesheet" href="../../dashboard/css/style.css" />
		
		<link rel="stylesheet" type="text/css" href="../../css/common-mh.css?ver=1.0" />
		<link rel="stylesheet" type="text/css" href="../../css/font-awesome/css/font-awesome.min.css" />
		
		<link href="../../css/common-mh.css" rel="stylesheet" type="text/css" />
		<link href="../../css/table_code.css" rel="stylesheet" type="text/css" />
		<link href="../../css/index.css" rel="stylesheet" type="text/css" />
		<link href="../../css/myclass.css?v={$smarty.now}" rel="stylesheet" type="text/css" />
		<link href="../../js/myclass/slickSlider/slick.css" rel="stylesheet" type="text/css" />
		
		<script type="text/javascript" src="../../js/jquery/jquery-1.10.2.js"></script>
		
		<script type="text/javascript">
			let Controller = "{$Controller}";
			let ActionMode = "{$ActionMode}";
		</script>
		{literal}
		<script type="text/javascript">
			$(document).ready(function(){
				addEventToElement("login", "#login");
				addEventToElement("pwd", "#pwd");
			});	

			function addEventToElement(dvsCd, selector) {
				if (dvsCd == "login") {
					$(selector).on("click", checkLogin);
				} else if (dvsCd == "pwd") {
					$(selector).on("keydown", function(event){
						$(".login-wrap").removeClass("is-error");

						if (event.key === "Enter") {
							checkLogin();
					    };
					});
				} 
			}

			function checkLogin() {
				let id = "";
				let pwd = "";

				let params = {
					"id": $("#id").val(),
					"pwd": $("#pwd").val(),
					"ActionMode": "login",
					"SubAction": "checkLogin"
				}

				$.ajax({
					type:"POST",
					url: Controller,
					data: params,
					async: false,
					success : function(res) {
						const response = JSON.parse(res);
						
						if (response.rstCd == "200") {
							let locationParams = {
								"ActionMode": "main"
							}
							location.href = Controller + "?" + $.param(locationParams);
						} else if (response.rstCd == "403") {
							$(".login-wrap").addClass("is-error");
							alert(response.error.message);
							return false;
						} else if (response.rstCd == "500") {
							alert("로그인중 문제가발생하였습니다.\n관리자에게 문의하세요.");
							return false;
						}
					},
					error : function(xhr, status, error) {
						alert("에러발생");
						return false;
					}
				});
			}
		</script>
		{/literal}
	</head>
	<body>
		<div class="login-wrap" data-state="default">
			<!-- .login-wrap에 is-error 클래스 추가하면 에러났을때 디자인이 나옵니다.-->
            <div class="login-panel">
	            <h1 class="logo aldrich-regular">MH Dashboard</h1>
	            <form class="login-form" id="loginForm">
	                <div class="input-group">
	                    <input id="id" type="text" placeholder="사번을 입력해주세요." />
	                    <div class="error-icon"><img src="../../dashboard/images/login/ico-warning.svg" alt="에러 아이콘"></div>
	                </div>
	
	                <div class="input-group">
	                    <input id="pwd" type="password" placeholder="비밀번호를 입력해주세요." />
	                    <div class="error-icon"><img src="../../dashboard/images/login/ico-warning.svg" alt="에러 아이콘"></div>
	                </div>
	
	                <button id="login" type="button" class="btn-login">로그인</button>
	                <p class="helper-text">
	                    ※ 로그인 정보가 올바르지 않습니다.
	                </p>
	            </form>
	        </div>
		</div>
	</body>
</html>