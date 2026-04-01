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
	
	<!-- 스타일2 -->
    <link rel="stylesheet" type="text/css" href="../../dashboard/css/style.css" />
	<link rel="stylesheet" type="text/css" href="../../css/common-mh.css?ver=1.0" />
	
	<link rel="stylesheet" type="text/css" href="../../css/common-mh.css"/>
	<link rel="stylesheet" type="text/css" href="../../css/table_code.css"/>
	<link rel="stylesheet" type="text/css" href="../../css/index.css" />
	
	<!-- dataPicker -->
	<link rel="stylesheet" type="text/css" href="../../dashboard/css/jquery/ui/1.13.2/themes/base/jquery-ui.css" />
	
	<script src="../../dashboard/js/apexcharts.js"></script>
	<script src="../../dashboard/js/jquery/jquery-3.6.0.min.js"></script>
	<script src="../../dashboard/css/jquery/ui/1.13.2/jquery-ui.min.js"></script>
	<script src="../../dashboard/js/jquery/jquery_calendar_set.js"></script>
	
	{* <script type="text/javascript" src="../../js/jquery/jquery-1.10.2.js"></script> *}
	<script src="../../dashboard/js/design.js"></script>
	<script src="../../dashboard/js/dev.js"></script>
	
	
	<script type="text/javascript">
		let Controller = "{$Controller}";
		let ActionMode = "{$ActionMode}";
		let userDept = "{$userDept}";
		let userPrentDepts = {$userPrentDepts|@json_encode};
		let depts = {$depts|@json_encode};
		let users = {$users|@json_encode};
		let summary = {$summary|@json_encode};
		let mhChart; 
	</script>
	{literal}
	<script type="text/javascript"></script>
	<style>
		/* 툴팁이 화면 (0,0)에서 이동하는 듯한 잔상 제거 */
		.apexcharts-tooltip {
			transition: none !important;
			top: 0;
			left: 0;
		}
		.display_none {
			display: none;
		}
	</style>
	
	<script>
		$(document).ready(function(){
			drawDeptHtml("TDC");
			drawDeptHtml("GPD");
			getAttendance();
			getProjectSection();
			getMemberListSection();
			addEventToElement("tab-btn");
			addEventToElement("navSearch");
			addEventToElement("team");
			addEventToElement("retrieve");
			addEventToElement("retrieveDate");
			addEventToElement("logout");
			toggleDeptTree("open", userPrentDepts);
			showTeam();
		});

		function retrieve() {
			const startDay = $("#startDay").val();
			const endDay = $("#endDay").val();
			const maxDays = 31;

			let validResult = validatePeriod(startDay, endDay, maxDays);
			if (!validResult["valid"]) {
				alert(validResult["message"]);
				return;
			}
			
			
			getAttendance();
			getProjectSection();
			getMemberListSection();
		}

		function toggleDeptTree(cmd, userPrentDepts) {
			switch (cmd) {
				case "open":
					for (let i = 0; i < userPrentDepts.length; i++) {
						const row = userPrentDepts[i];

						if (row["LVL"] == "1") {
							$("div[container_id=" + row["DEPT_NAME"] + "]").addClass("active");
							showTeam();
						}
	
						$(".nav-menu-wrap").find(".has-submenu[dept_no=" + row["DEPT_NO"] + "]").trigger("click");
						// $('[data-id="B23064"]').closest("li").addClass("active");
					}
					break;
				case "hide":
					$(".has-submenu.open").removeClass("open");
					$(".sub-item.active").removeClass("active");
				default: 
					break;
			}
		}

		function showTeam() {
			$.each($(".tab-btn"), function(index, element){
				let container = $(element).attr("container_id");
				
				if ($(element).hasClass("active")) {
					$("#" + container).removeClass("display_none");
				} else {
					$("#" + container).addClass("display_none");
				}
			});
		}
		
		function addEventToElement(dvsCd) {
			if (dvsCd == "team") {
				$.each($(".has-submenu"), function(index, element){
					$(element).on("click", function(event){
						event.stopPropagation();

						const clickedElement = $(event.target);
						if (clickedElement.attr("data-type") == "user") {
							const activeElement = event.target.closest('.sub-item');
							$(".sub-item.active").removeClass("active");
							$(activeElement).addClass("active");
							return;
						}
						
						$(element).toggleClass("open").siblings().removeClass("open");

						/* 추후 필요한경우 사용 
						let deptNo = $(element).attr("dept_no");
						if (!isNull(users[deptNo])) {
							if (users[deptNo].length == 0) {
								return;
							}

							if ($(element).find("ul[up_dept_no=" + deptNo + "]").children().length > 0) {
								return;
							}

							let html = "";
							for (let i = 0; i < users[deptNo].length; i++) {
								const row = users[deptNo][i];
								
								html += `<li class="sub-item">`;
									html += `<div class="sub-item-inner" data-type="user" data-id="${row["USER_ID"]}">${row["USER_NAME"]}</div>`;
									html += `<ul class="sub-menu depth-4"></ul>`;
								html += `</li>`;
							}

							$(element).find("ul[up_dept_no=" + deptNo + "]").html(html);
						}
						*/
					});
				});
			} else if (dvsCd == "tab-btn") {
				$.each($(".tab-btn"), function(index, element){
					$(element).on("click", function(event){
						event.stopPropagation();
						$(element).toggleClass("active");

						showTeam();
					});
				});
			} else if (dvsCd == "navSearch") {
				$("#navSearch").on("keydown", function(event){
					if (event.key === "Enter") {
				        event.preventDefault();
				        searchUser($(this).val());
				    };
				});
			} else if (dvsCd == "retrieveDate") {
				 $("#startDay").datepicker(
			        $.extend({}, null, {
			            onSelect: function(dateText){
			                $("#startDayText").text(dateText);

			            }
			        })
			    );
			    $("#startDayBtn").click(function(){
			    	$("#startDay").datepicker("setDate", $("#startDay").val());
			        $("#startDay").datepicker("show");
			    });

			    $("#endDay").datepicker(
			        $.extend({}, null, {
			            onSelect: function(dateText, element){
			                $("#endDayText").text(dateText);
			            }
			        })
			    );
			    $("#endDayBtn").click(function(){
			    	$("#endDayBtn").datepicker("setDate", $("#endDayBtn").val());
			        $("#endDay").datepicker("show");
			    });
			} else if (dvsCd == "logout") {
                $(".footer-container .right-wrap").on("click", function(){
                    let params = {
						"ActionMode": "login",
						"SubAction": "logout"
                    }

                    let logoutMessage = "로그아웃 하시겠습니까?";
					if (!confirm(logoutMessage)) {
						return;
					}
					
                	window.location.href = Controller + "?" + $.param(params);
                });
			} else if (dvsCd == "retrieve") {
				$("#retrieve_btn").on("click", retrieve);
			}
		}

		function searchUser(text) {
			let url = Controller;
			let params = {
				"ActionMode": ActionMode,
				"SubAction": "searchUser",
				"searchText": text
			}
			return;
			
			$.ajax({
				type:"POST",
				url: url,
				data: params,
				async: false,
				success : function(res) {
					const response = JSON.parse(res);

					if (response["rstCd"] != "200") {
						alert("조회중 오류가 발생하였습니다.\n관리자에게 문의하세요.");
						return;
					}

					depts["TDC"] = response["data"]["TDC"];
					depts["GPD"] = response["data"]["GPD"];

					drawDeptHtml("TDC");
					drawDeptHtml("GPD");
					addEventToElement("team");
				},
				error : function(xhr, status, error) {
					alert("에러발생");
					return false;
				}
			});
		}


		function getMemberListSection() {
			let url = Controller;
			let params = {
				"ActionMode": ActionMode,
				"SubAction": "memberList",
				"startDay": $("#startDay").val(),
				"endDay": $("#endDay").val(),
				"deptNo": userDept
			}
			
			$.ajax({
				type:"POST",
				url: url,
				data: params,
				async: false,
				success : function(res) {
					$(".member-status").html(res);
				},
				error : function(xhr, status, error) {
					alert("에러발생");
					return false;
				}
			});
		}

		function setProjectCnt() {
	      	const primaryCnt = $('.project-table-row-wrap > .project-table-row[pm="T"]').length; 
	      	const assistCnt = $('.project-table-row-wrap > .project-table-row[pm="F"]').length;

        	$("#primary_value").text(primaryCnt);
        	$("#assist_value").text(assistCnt);
		}

		function setProjectRate() {
	      	let projectRate = [];
	      	let totalMH = 0;
	      	let colors = [
      			"blue",
      			"pink",
      			"green",
      			"orange",
      			"gray"
	      	];
	      	colors = [];

			$.each($(".project-table-row-wrap .project-table-row"), function(index, element){
		      	const projCode = $(element).attr("proj_code");
		      	const projName = $(element).attr("proj_name");
		      	const mh = $(element).attr("MH");
		      	const row = {
				   "projCode": projCode,
				   "projName": projName,
				   "mh": parseFloat(mh)
		      	}
		      	totalMH += parseFloat(mh);

		      	projectRate.push(row);
			});
			projectRate.sort((a, b) => b.mh - a.mh);

			let html = "";
			for (let i = 0; i < projectRate.length; i++) {
				const row = projectRate[i];

				if (totalMH != 0) {
					row["percent"] = parseFloat(row["mh"] / totalMH * 100).toFixed(2);
				}

				const color = colors[i % colors.length];
				html += `<li class="item ${color}">`;
					html += `<span class="dot"></span>`;
					html += `<strong>${row["projName"]}</strong>`;
					html += `<span class="percent">${row["percent"]}% (${row["mh"]}h)</span>`;
				html += `</li>`;
			}

			$(".ratio-list").html(html);
			$(".ratio-summary #project_cnt").html(projectRate.length);
			$(".ratio-summary #project_mh").html(String(totalMH.toFixed(1)) + "h");
		}

		function getProjectSection() {
			let url = Controller;
			let params = {
				"ActionMode": ActionMode,
				"SubAction": "projectDetailedSummary",
				"startDay": $("#startDay").val(),
				"endDay": $("#endDay").val(),
				"deptNo": userDept
			}
			
			$.ajax({
				type:"POST",
				url: url,
				data: params,
				async: false,
				success : function(res) {
					$(".project-section").html(res);
					setProjectCnt();
					setProjectRate();
					setMhChart();
					setCostSection();
				},
				error : function(xhr, status, error) {
					alert("에러발생");
					return false;
				}
			});
		}

		function getAttendance() {
			let url = Controller;
			let params = {
				"ActionMode": ActionMode,
				"SubAction": "attendanceStatus",
				"startDay": $("#startDay").val(),
				"endDay": $("#endDay").val(),
				"deptNo": userDept
			}
			
			$.ajax({
				type:"POST",
				url: url,
				data: params,
				async: false,
				success : function(res) {
					$(".attendance_container").html(res);
				},
				error : function(xhr, status, error) {
					alert("에러발생");
					return false;
				}
			});
		}

		function drawDeptHtml(dvsCd) {
			let html = "";

			if (isNull(dvsCd)) {
				dvsCd = "TDC";
			}

			$(".nav-menu-wrap" + "#" + dvsCd).empty();
			for (let i = 0; i < depts[dvsCd].length; i++) {
				let row =  depts[dvsCd][i];
				let html = "";

				if (row.LVL == "1") {
					continue;
				}
				
				html = getDeptHtml(row);
				if (row.LVL == "2") {
					$(".nav-menu-wrap" + "#" + dvsCd).append(html);
				} else {
					$(".nav-menu-wrap" + "#" + dvsCd).find("ul[up_dept_no=" + row.UP_DEPT_NO + "]").append(html);
				}
			}
		}

		function getDeptHtml(row) {
			let html = "";
			
			if (row.LVL == "2") {
				html += `<li class="nav-item has-submenu" level="${row.LVL}" dept_no="${row.DEPT_NO}">`;
					html += `<div class="nav-text">`;
						html += `<div class="nav-icon">`;
							if (!isNull(row["ICON_PATH"])) {
								html += `<img src="${row.ICON_PATH}" alt="">`;
							} else {
								html += `<img alt="">`;
							}
						html += `</div>`;
						html += `<div class="nav-name">${row.DEPT_NAME}</div>`;
					html += `</div>`;

					html += `<div class="nav-arrow">`;
						html += `<img src="../../dashboard/images/aside/menu-arrow.svg" alt="">`;
					html += `</div>`;

					html += `<ul class="sub-menu depth-2" up_dept_no="${row.DEPT_NO}"></ul>`;
				html += `</li>`;

				return html;
			} else if (row.LVL == "3") {
				html += `<li class="sub-item has-submenu " level="${row.LVL}" dept_no="${row.DEPT_NO}">`;
					html += `<div class="sub-item-inner" dept_no="${row.DEPT_NO}">`;
						html += `${row.DEPT_NAME}`;
						html += `<div class="nav-arrow">`;
							html += `<img src="../../dashboard/images/aside/menu-arrow.svg" alt="">`;
						html += `</div>`;	
					html += `</div>`;
					html += `<ul class="sub-menu depth-3" up_dept_no="${row.DEPT_NO}"></ul>`;	
				html += `</li>`;

				return html;
			}
		}

		function getUserHtml() {
			
		}

		function isNull(value) {
			if (value == null || value == undefined || value.toString().replace(/\s/g,"") == "") { 
				return true;
			}

			return false;
		}

		function setCostSection() {
			let summary = {
				"labor": 0,
				"business_trip": 0,
				"welfare": 0,
				"etc": 0
			};
			const unit = 1000;
			$.each($(".cost-box .item.labor"), function(index, element){
		      	const cost = parseFloat($(element).attr("cost")) | 0;
		      	summary["labor"] += cost;
			});

			$.each($(".cost-box .item.business_trip"), function(index, element){
		      	const cost = parseFloat($(element).attr("cost")) | 0;
		      	summary["business_trip"] += cost;
			});

			$.each($(".cost-box .item.welfare"), function(index, element){
		      	const cost = parseFloat($(element).attr("cost")) | 0;
		      	summary["welfare"] += cost;
			});

			$.each($(".cost-box .item.etc"), function(index, element){
		      	const cost = parseFloat($(element).attr("cost")) | 0;
		      	summary["etc"] += cost;
			});

	      	$("#primary_cost_value").text(formatCurrency(summary["labor"]/unit));

			const assitSum = summary["business_trip"] + summary["welfare"] + summary["etc"];
	      	$("#assist_cost_value").text(formatCurrency(assitSum/unit));

	      	getMhUnitPrint();
		}

		function getMhUnitPrint() {
			let url = Controller;
			let params = {
				"ActionMode": ActionMode,
				"SubAction": "getMhUnitPrint",
				"startDay": $("#startDay").val(),
				"endDay": $("#endDay").val(),
				"deptNo": userDept
			}
			
			$.ajax({
				type:"POST",
				url: url,
				data: params,
				async: false,
				success : function(res) {
					const response = JSON.parse(res);

				
					let html = "직접비 산정 근거 (표준단가) / 기준년월별<br>";


					for (const ym in response["data"]) {
						html += `기준년월: ${ym}<br>`;
						for (const grade in response["data"][ym]) {
							const row = response["data"][ym][grade];
							const gradeName = row["GRADE_NAME"];
							const amt = formatCurrency(row["TIME_AMT"]);
							
							html += `${gradeName}: ${amt}<br>`;
						}
					}

					$(".tooltip-body").html(html);
				},
				error : function(xhr, status, error) {
					alert("에러발생");
					return false;
				}
			});
		}

		function setMhChart() {
			let chartData = {
				"REVENUE": 0,
				"NON_REVENUE": 0,
				"COMMON": 0,
				"TOTAL": 0
			}
			
			$.each($(".project-table-row-wrap .project-table-row"), function(index, element){
		      	const revenueType = $(element).attr("revenue_type");
		      	const mh = $(element).attr("MH");
		      	chartData[revenueType] += parseFloat(mh);
		      	chartData["TOTAL"] += parseFloat(mh);
			});

			drawChart(chartData);
		}

		function safePercent(value, total) {
			if (isNaN(value) || isNaN(total)) {
				return 0;
			} 
			
		    return total ? (value / total * 100) : 0;
		}

		function drawChart(data) {
			if (data === null) {
				return;
			}

			if (mhChart) {
		        mhChart.destroy(); 
		    }
			
			 /* ======================================
            1. 기본 데이터 정의
            - seriesValues  : 차트 계산용 (숫자 필수)
            - displayValues : 중앙 텍스트 표시용
            ====================================== */
            const labels = ['매출', '비매출', '공통'];

			let seriesValues = [
				parseFloat(safePercent(data["REVENUE"], data["TOTAL"]).toFixed(1)), 
    			parseFloat(safePercent(data["NON_REVENUE"], data["TOTAL"]).toFixed(1)), 
    			parseFloat(safePercent(data["COMMON"], data["TOTAL"]).toFixed(1))
			];


			/* 보정값사용시 
            // 차트 비율 계산용 값 (퍼센트 기준)
			const MIN_PERCENT = 25;
			// 최소값 보정
			let adjusted = seriesValues.map(v => v < MIN_PERCENT ? MIN_PERCENT : v);
			const total = adjusted.reduce((a, b) => a + b, 0);
			seriesValues = adjusted.map(v => v / total * 100);
			*/

            /* 중앙 텍스트에 보여줄 값 */
            const displayValues = [
	                {
		                text: parseFloat(data["REVENUE"]).toFixed(1) + "시간",
		                percent: safePercent(data["REVENUE"], data["TOTAL"]).toFixed(1) + "%"
	            	}, 
	            	{
		                text: parseFloat(data["NON_REVENUE"]).toFixed(1) + "시간",
		                percent: safePercent(data["NON_REVENUE"], data["TOTAL"]).toFixed(1) + "%"
	            	}, 
	            	{
		                text: parseFloat(data["COMMON"]).toFixed(1) + "시간",
		                percent: safePercent(data["COMMON"], data["TOTAL"]).toFixed(1) + "%"
	            	}
            	];

            /* 컬러 매칭 */
            const colors = ['#3A5A53', '#B88E5D', '#888888'];
            

            /* 총 시간 고정 값 */
            const TOTAL_LABEL = '총 시간';
            const TOTAL_VALUE = parseFloat(data["TOTAL"]).toFixed(1);

            /* 중앙 텍스트 DOM */
            const centerLabel = document.querySelector('.mh-chart-center .label');
            const centerValue = document.querySelector('.mh-chart-center .value');

            /* ======================================
            2. 중앙 텍스트 초기화 함수
            - 차트에서 마우스가 벗어났을 때 항상 복원
            ====================================== */
            function resetCenterText() {
                centerLabel.textContent = TOTAL_LABEL;
                centerValue.textContent = TOTAL_VALUE;
                centerValue.style.color = ''; // 기본 컬러로 복원
            }

            /* ======================================
            3. ApexCharts 옵션
            ====================================== */
            var options = {
                /* 차트에 실제로 사용되는 값 */
                series: seriesValues,
                chart: {
                    type: 'donut',
                    height: 200,
                    fontFamily: '"Saira", "Pretendard", sans-serif',
                    animations: {
                        enabled: false
                    },

                    /*  마우스 인터랙션 제어 */
                    events: {
                        /* 조각 호버 시 */
                        dataPointMouseEnter: function(event, chartContext, config) {
                            const idx = config.dataPointIndex;

                            centerLabel.textContent = labels[idx];
                            centerValue.innerHTML = `${displayValues[idx]
                                    .text} <span class="percent">(${displayValues[idx]
                                    .percent})</span>`;
                            centerValue.style.color = colors[idx];
                        },

                        /* 조각에서 벗어났을 때 */
                        dataPointMouseLeave: function() {
                            resetCenterText();
                        },

                        /* 차트 영역 전체를 벗어났을 때 (안전 복원) */
                        mouseLeave: function() {
                            resetCenterText();
                        }
                    }
                },

                /* ======================================
                4. 반원 도넛 설정
                ====================================== */
                plotOptions: {
                    pie: {
                        startAngle: -90,
                        endAngle: 90,
                        offsetY: -8,

                        /* ▶ 클릭 시 도넛 커지는 현상 제거 */
                        expandOnClick: false,

                        donut: {
                            size: '62%',
                            labels: {
                                show: false // 중앙 텍스트는 HTML에서 직접 제어
                            }
                        }
                    }
                },

                /* ======================================
                5. 데이터 라벨 (조각 내부 텍스트)
                ====================================== */
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        return labels[opts.seriesIndex];
                    },
                    style: {
                        fontSize: '12px',
                        fontWeight: 600,
                        colors: ['#fff']
                    }
                },

                labels: labels,
                colors: colors,

                /* ======================================
                6. 툴팁 완전 제거
                ====================================== */
                tooltip: {
                    enabled: false
                },

                legend: {
                    show: false
                },

                /* ======================================
                7. 상태 효과 제어
                ====================================== */
                states: {
                    hover: {
                        filter: {
                            type: 'darken',
                            value: 0.9
                        }
                    },
                    active: {
                        filter: {
                            type: 'darken',
                            value: 0.9
                        }
                    }
                }
            };

            /* ======================================
            8. 차트 생성 및 렌더링
            ====================================== */
            mhChart = new ApexCharts(document.querySelector('#mhChart'), options);
            mhChart.render();

            /* ======================================
            9. 최초 진입 시 중앙 텍스트 세팅
            ====================================== */
            resetCenterText();

            let REVENUE_TEXT = parseFloat(data["REVENUE"]).toFixed(1) + "시간" + "(" +  safePercent(data["REVENUE"], data["TOTAL"]).toFixed(1) + "%)";
            let NONE_REVENUE_TEXT = parseFloat(data["NON_REVENUE"]).toFixed(1) + "시간" + "(" +  safePercent(data["NON_REVENUE"], data["TOTAL"]).toFixed(1) + "%)";
            let COMMON_TEXT = parseFloat(data["COMMON"]).toFixed(1) + "시간" + "(" +  safePercent(data["COMMON"], data["TOTAL"]).toFixed(1) + "%)";
            $(".summary-legend .is-sales .legend-text").text(REVENUE_TEXT);
			$(".summary-legend .is-non-sales .legend-text").text(NONE_REVENUE_TEXT);
			$(".summary-legend .is-common .legend-text").text(COMMON_TEXT);
		}
	</script>
	
	<style>
	.ratio-summary{
	    display:flex;
	    flex-direction:column;
	    gap:15px;
	}
	
	.summary-box{
	    background:#f8f9fb;
	    padding:5px 16px;
	    border-radius:8px;
	    width:160px;
	}
	
	.summary-box.highlight{
	    background:#eef4ff;
	}
	
	.label{
	    font-size:12px;
	    color:#888;
	    display:block;
	}
	
	.summary-box strong{
	    display:block;
	    font-size:18px;
	    margin-top:3px;
	}
	
	.summary-box em{
	    font-size:13px;
	    color:#3b82f6;
	}
	
	#startDay, #endDay {
		position: relative;
		left: -100px;
		top: 5px;
	}
	</style>
	{/literal}	
</head>
	<body class="page-main">
	    <!-- aside 시작 -->
		<aside class="sidebar-container">
			<div class="sidebar">
	            <!-- 로고 영역 -->
	            <div class="brand">
	                <img src="../../dashboard/images/common/logo.svg" alt="맨아워 로고">
	                <!--<img src="../../dashboard/images/aside/icon-toggle.svg" alt="사이드바 토글">-->
	            </div>
	            <!-- //로고 영역 -->
	
	            <div class="nav-wrap">
	                <!-- 메뉴 영역 -->
	                <div class="nav-container">
						<ul class="nav-menu">
							<li class="nav-item link">
	                            <div class="nav-text">
	                                <div class="nav-icon"><img src="../../dashboard/images/aside/menu-icon-integration.svg" alt=""></div>
	                                <div class="nav-name">전체 프로젝트 통합</div>
	                            </div>
	                            <div class="nav-arrow"><img src="../../dashboard/images/aside/menu-arrow.svg" alt=""></div>
	                        </li>
	
	                        <li class="nav-item link active">
	                            <div class="nav-text">
	                                <div class="nav-icon"><img src="../../dashboard/images/aside/menu-icon-person.svg" alt=""></div>
	                                <div class="nav-name">팀/개인별 분석</div>
	                            </div>
	                            <div class="nav-arrow"><img src="../../dashboard/images/aside/menu-arrow.svg" alt=""></div>
	                        </li>
	
	                        <!-- 탭 버튼 영역 시작 -->
	                        <li class="tab-button-wrap" role="tablist">
	                            <div class="box"><img src="../../dashboard/images/aside/logo-br.svg" alt="바론 로고"></div>
	                            <div class="btn tab-btn calc active" container_id="GPD">GPD</div>
	                            <div class="btn tab-btn calc active" container_id="TDC">TDC</div>
	                        </li>
	                        <!-- 탭 버튼 영역 끝 -->
	
	                        <!-- 검색창 시작 -->
	                        <li class="nav-search">
	                            <form class="nav-search-form" role="search">
	                                <div class="nav-search-box">
	                                    <span class="nav-search-icon" aria-hidden="true">
	                                        <img src="../../dashboard/images/aside/icon-search.svg" alt="">
	                                    </span>
	                                    <input type="search" id="navSearch" class="nav-search-input" placeholder="팀명 또는 이름을 검색해주세요." autocomplete="off" />
	                                </div>
	                            </form>
	                        </li>
	                        <!-- 검색창 끝 -->
	                        
	                        <!--  부서 TDC -->
	                        <ul class="nav-menu-wrap display_none" id="TDC"></ul>
	                        <!--  부서 GPD -->
	                        <ul class="nav-menu-wrap display_none" id="GPD"></ul>
						</ul>
					</div>
					<!-- //메뉴 영역 -->
	
					<!-- 하단 사용자 정보 -->
					<div class="footer-container">
					    <div class="left-wrap">
					        <div class="user-info">
					            <span class="user-name">{$session.CBD_INTRA_USER_NAME} {$session.CBD_INTRA_RANK_NAME}</span>
					            <span class="user-role"></span>
					        </div>
					        <div class="company-name">{$session.CBD_SATIS_TEAM_NAME}</div>
					    </div>
					    <div class="right-wrap">
					        <img src="../../dashboard/images/aside/icon-logout.svg" alt="" class="logout">
					    </div>
					</div>
					<!-- //하단 사용자 정보 -->
            	</div>
			</div>
		</aside>
	    <!-- aside 끝 -->
	
	    <!-- 메인 컨텐츠 시작 -->
	    <main class="main-container">
	        <!-- header -->
	        <div class="page-header">
	            <div class="page-header__left">
	                <h3 class="page-header__title">{$session.CBD_SATIS_TEAM_NAME}
	                    <img src="../../dashboard/images/common/icon-information.svg" alt="이력관리" class="tool-tip-history" id="toolTipHistory" data-tooltip-trigger="historyModal" data-tooltip-hover-open>
	                </h3>
	                <!-- 이력관리 모달 시작 -->
	                <div class="history-modal" id="historyModal" data-tooltip-target="historyModal">
	                    <div class="history-modal__header">
	                        <span>특이사항</span>
	                        <button type="button" class="history-modal__close" data-tooltip-close><img src="../../dashboard/images/common/icon-tooltip-close.svg" alt=""></button>
	                    </div>
	                    <div class="history-modal__body">
	                        <div class="history-table">
	                            <div class="history-table__head">
	                                <span>날짜</span>
	                                <span>내용</span>
	                            </div>
	
	                            <div class="history-table__row">
	                                <span>2026.01.12</span>
	                                <span>김신지 연구원 팀 이동 (상하수도→기술기획)</span>
	                            </div>
	                            <div class="history-table__row">
	                                <span>2026.01.02</span>
	                                <span>김신지 연구원 퇴사 (2025.12.18)</span>
	                            </div>
	                            <div class="history-table__row">
	                                <span>2025.12.18</span>
	                                <span>김신지 연구원 휴직 (25.12.18~26.02.22)</span>
	                            </div>
	                            <div class="history-table__row">
	                                <span>2025.12.17</span>
	                                <span>그룹 변경 (구조→도로)</span>
	                            </div>
	                            <div class="history-table__row">
	                                <span>2025.12.11</span>
	                                <span>PM변경 (GAIA, 천지인(김신지))</span>
	                            </div>
	                            <div class="history-table__row">
	                                <span>2025.12.11</span>
	                                <span>PM변경 (GAIA, 천지인(김신지))</span>
	                            </div>
	                            <div class="history-table__row">
	                                <span>2025.12.11</span>
	                                <span>PM변경 (GAIA, 천지인(김신지))</span>
	                            </div>
	                            <div class="history-table__row">
	                                <span>2025.12.11</span>
	                                <span>PM변경 (GAIA, 천지인(김신지))</span>
	                            </div>
	                            <div class="history-table__row">
	                                <span>2025.12.11</span>
	                                <span>PM변경 (GAIA, 천지인(김신지))</span>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	                <!-- 이력관리 모달 끝 -->
	                <div class="br"></div>
	                <div class="date-picker-container">
						<span class="date-label">시작일 :</span>
						<button id="startDayBtn" type="button" class="custom-date-btn">
							<span class="date-value" id="startDayText">{$startDay}</span>
							<img src="../../dashboard/images/common/icon-calendar.svg" alt="달력 아이콘" class="calendar-icon">
							<input type="text" id="startDay" value="{$startDay}" style="width: 0;">
						</button>
						
						<span class="date-label">종료일 :</span>
						<button id="endDayBtn" type="button" class="custom-date-btn">
							<span class="date-value" id="endDayText">{$endDay}</span>
							<img src="../../dashboard/images/common/icon-calendar.svg" alt="달력 아이콘" class="calendar-icon">
							<input type="text" id="endDay" value="{$endDay}" style="width: 0;">
						</button>
						
						<button id="retrieve_btn" type="button" class="page-header__search-btn btn" aria-label="조회">
                            <img src="../../dashboard/images/common/icon-search-white.svg" alt="" />
                            <span>조회</span>
                        </button>
					</div>
	            </div>
	
	            <div class="page-header__right">
	                <button type="button" class="page-header__setting-btn btn btn-gray" aria-label="권한 설정" data-modal-target="modalAuthority">
	                    <img src="../../dashboard/images/common/icon-user-setting.svg" alt="권한설정아이콘" />
	                    <span>권한 설정</span>
	                </button>


					<!-- 권한 설정 모달 시작 -->
					<div id="modalAuthority" class="modal-overlay" aria-hidden="true">
						<div class="modal-content">
							<header class="modal-header">
								<ul class="tab-list">
									<li class="active" data-role="integrated">통합 관리자</li>
									<li data-role="personnel">인사 관리자</li>
									<li data-role="overall">전체 관리자</li>
									<li data-role="general">일반 관리자</li>
								</ul>
								<div class="modal-btn-wrap">
									<button type="button" class="help-btn btn">
										<img src="../../dashboard/images/common/icon-help.svg" alt="">
										도움말
									</button>
									<button type="button" class="modal-close-btn">
										<img src="../../dashboard/images/common/modal-close.svg" alt="">
									</button>
								</div>
							</header>

							<main class="modal-body">
								<section class="user-selection-left">
									<div class="search-box">
										<div class="search-icon">
											<img src="../../dashboard/images/common/icon-search.svg" alt="검색">
										</div>
										<input type="search" placeholder="이름을 검색해주세요.">
									</div>

									<div class="dept-tree">

										<!-- ===== 1 DEPTH - 기술개발센터 ===== -->
										<div class="dept-node depth-1">
											<!-- .dept-row : 한 줄 행 - 여기에 padding 적용 (dept-node에 padding 없음) -->
											<div class="dept-row">
												<p class="dept-name">기술개발센터</p>
												<button class="toggle-btn">
													<img src="../../dashboard/images/common/modal-toggle.svg" alt="">
												</button>
											</div>

											<div class="dept-children">

												<!-- ===== 2 DEPTH - 구조물계획팀 ===== -->
												<div class="dept-node depth-2">
													<div class="dept-row">
														<div class="team-wrap">
															<button class="check-btn">
																<img src="../../dashboard/images/common/checkbox.svg" alt="">
															</button>
															<p class="dept-name">구조물계획팀 <span class="member-count">6</span></p>
														</div>
														<button class="toggle-btn">
															<img src="../../dashboard/images/common/modal-toggle.svg" alt="">
														</button>
													</div>

													<div class="dept-children">
														<ul class="user-list">
															<li>
																<button type="button" class="add-btn">
																	<img src="../../dashboard/images/common/icon-add.svg" alt="">
																</button>
																<img src="../../dashboard/images/common/people-img.svg" alt="" class="thumb">
																<div class="info">
																	<p class="company-name">
																		<img src="../../dashboard/images/common/logo-hanmac.svg" alt="">(주)한맥기술
																	</p>
																	<p class="name">김원기<span>수석연구원</span></p>
																</div>
															</li>
															<li>
																<button type="button" class="add-btn">
																	<img src="../../dashboard/images/common/icon-add.svg" alt="">
																</button>
																<img src="../../dashboard/images/common/people-img.svg" alt="" class="thumb">
																<div class="info">
																	<p class="company-name">
																		<img src="../../dashboard/images/common/logo-hanmac.svg" alt="">(주)한맥기술
																	</p>
																	<p class="name">김신지<span>연구원</span></p>
																</div>
															</li>
															<li>
																<button type="button" class="add-btn">
																	<img src="../../dashboard/images/common/icon-add.svg" alt="">
																</button>
																<img src="../../dashboard/images/common/people-img.svg" alt="" class="thumb">
																<div class="info">
																	<p class="company-name">
																		<img src="../../dashboard/images/common/logo-hanmac.svg" alt="">(주)한맥기술
																	</p>
																	<p class="name">이재원<span>선임연구원</span></p>
																</div>
															</li>
															<li>
																<button type="button" class="add-btn">
																	<img src="../../dashboard/images/common/icon-add.svg" alt="">
																</button>
																<img src="../../dashboard/images/common/people-img.svg" alt="" class="thumb">
																<div class="info">
																	<p class="company-name">
																		<img src="../../dashboard/images/common/logo-hanmac.svg" alt="">(주)한맥기술
																	</p>
																	<p class="name">홍길수<span>연구원</span></p>
																</div>
															</li>
															<li>
																<button type="button" class="add-btn">
																	<img src="../../dashboard/images/common/icon-add.svg" alt="">
																</button>
																<img src="../../dashboard/images/common/people-img.svg" alt="" class="thumb">
																<div class="info">
																	<p class="company-name">
																		<img src="../../dashboard/images/common/logo-sanman.svg" alt="">삼안
																	</p>
																	<p class="name">이지율<span>연구원</span></p>
																</div>
															</li>
															<li>
																<button type="button" class="add-btn">
																	<img src="../../dashboard/images/common/icon-add.svg" alt="">
																</button>
																<img src="../../dashboard/images/common/people-img.svg" alt="" class="thumb">
																<div class="info">
																	<p class="company-name">
																		<img src="../../dashboard/images/common/logo-ptc.svg" alt="">피티씨
																	</p>
																	<p class="name">이해랑<span>연구원</span></p>
																</div>
															</li>
														</ul>
													</div>
												</div>
												<!-- //2 DEPTH 구조물계획팀 끝 -->

												<!-- ===== 2 DEPTH - 일반구조물팀 ===== -->
												<div class="dept-node depth-2">
													<div class="dept-row">
														<div class="team-wrap">
															<button class="check-btn">
																<img src="../../dashboard/images/common/checkbox.svg" alt="">
															</button>
															<p class="dept-name">일반구조물팀 <span class="member-count">12</span></p>
														</div>
														<button class="toggle-btn">
															<img src="../../dashboard/images/common/modal-toggle.svg" alt="">
														</button>
													</div>

													<div class="dept-children">
														<ul class="user-list">
															<li>
																<button type="button" class="add-btn">
																	<img src="../../dashboard/images/common/icon-add.svg" alt="">
																</button>
																<img src="../../dashboard/images/common/people-img.svg" alt="" class="thumb">
																<div class="info">
																	<p class="company-name">
																		<img src="../../dashboard/images/common/logo-hanmac.svg" alt="">(주)한맥기술
																	</p>
																	<p class="name">홍길동<span>연구원</span></p>
																</div>
															</li>
															<li>
																<button type="button" class="add-btn">
																	<img src="../../dashboard/images/common/icon-add.svg" alt="">
																</button>
																<img src="../../dashboard/images/common/people-img.svg" alt="" class="thumb">
																<div class="info">
																	<p class="company-name">
																		<img src="../../dashboard/images/common/logo-hanmac.svg" alt="">(주)한맥기술
																	</p>
																	<p class="name">박민준<span>연구원</span></p>
																</div>
															</li>
															<li>
																<button type="button" class="add-btn">
																	<img src="../../dashboard/images/common/icon-add.svg" alt="">
																</button>
																<img src="../../dashboard/images/common/people-img.svg" alt="" class="thumb">
																<div class="info">
																	<p class="company-name">
																		<img src="../../dashboard/images/common/logo-hanmac.svg" alt="">(주)한맥기술
																	</p>
																	<p class="name">최유진<span>선임연구원</span></p>
																</div>
															</li>
														</ul>
													</div>
												</div>
												<!-- //2 DEPTH 일반구조물팀 끝 -->

											</div>
										</div>
										<!-- //1 DEPTH 기술개발센터 끝 -->

										<!-- ===== 1 DEPTH - 총괄기획실 ===== -->
										<div class="dept-node depth-1">
											<div class="dept-row">
												<p class="dept-name">총괄기획실</p>
												<button class="toggle-btn">
													<img src="../../dashboard/images/common/modal-toggle.svg" alt="">
												</button>
											</div>

											<div class="dept-children">

												<!-- ===== 2 DEPTH - 기획팀 ===== -->
												<div class="dept-node depth-2">
													<div class="dept-row">
														<div class="team-wrap">
															<button class="check-btn">
																<img src="../../dashboard/images/common/checkbox.svg" alt="">
															</button>
															<p class="dept-name">기획팀 <span class="member-count">4</span></p>
														</div>
														<button class="toggle-btn">
															<img src="../../dashboard/images/common/modal-toggle.svg" alt="">
														</button>
													</div>

													<div class="dept-children">
														<ul class="user-list">
															<li>
																<button type="button" class="add-btn">
																	<img src="../../dashboard/images/common/icon-add.svg" alt="">
																</button>
																<img src="../../dashboard/images/common/people-img.svg" alt="" class="thumb">
																<div class="info">
																	<p class="company-name">
																		<img src="../../dashboard/images/common/logo-hanmac.svg" alt="">(주)한맥기술
																	</p>
																	<p class="name">홍길동<span>연구원</span></p>
																</div>
															</li>
															<li>
																<button type="button" class="add-btn">
																	<img src="../../dashboard/images/common/icon-add.svg" alt="">
																</button>
																<img src="../../dashboard/images/common/people-img.svg" alt="" class="thumb">
																<div class="info">
																	<p class="company-name">
																		<img src="../../dashboard/images/common/logo-hanmac.svg" alt="">(주)한맥기술
																	</p>
																	<p class="name">김지수<span>연구원</span></p>
																</div>
															</li>
														</ul>
													</div>
												</div>
												<!-- //2 DEPTH 기획팀 끝 -->

											</div>
										</div>
										<!-- //1 DEPTH 총괄기획실 끝 -->

									</div>
									<!-- //dept-tree 끝 -->

								</section>

								<section class="user-selection-right">
									<ul class="selected-list">
										<li data-role="integrated">
											<div class="selected-list-wrap">
												<button type="button" class="remove-btn">
													<img src="../../dashboard/images/common/icon-delete.svg" alt="">
												</button>
												<img src="../../dashboard/images/common/people-no-img.svg" alt="">
												<div class="info">
													<p class="company-name">
														<img src="../../dashboard/images/common/logo-hanmac.svg" alt="">(주)한맥기술
													</p>
													<p class="name">김신지<span>연구원</span></p>
												</div>
											</div>
											<span class="badge integrated">통합</span>
										</li>
										<li data-role="integrated">
											<div class="selected-list-wrap">
												<button type="button" class="remove-btn">
													<img src="../../dashboard/images/common/icon-delete.svg" alt="">
												</button>
												<img src="../../dashboard/images/common/people-no-img.svg" alt="">
												<div class="info">
													<p class="company-name">
														<img src="../../dashboard/images/common/logo-br.svg" alt="">바론
													</p>
													<p class="name">김신지<span>연구원</span></p>
												</div>
											</div>
											<span class="badge integrated">통합</span>
										</li>
										<li data-role="integrated">
											<div class="selected-list-wrap">
												<button type="button" class="remove-btn">
													<img src="../../dashboard/images/common/icon-delete.svg" alt="">
												</button>
												<img src="../../dashboard/images/common/people-no-img.svg" alt="">
												<div class="info">
													<p class="company-name">
														<img src="../../dashboard/images/common/logo-halla.svg" alt="">한라산업
													</p>
													<p class="name">김신지<span>연구원</span></p>
												</div>
											</div>
											<span class="badge integrated">통합</span>
										</li>
										<li data-role="integrated">
											<div class="selected-list-wrap">
												<button type="button" class="remove-btn">
													<img src="../../dashboard/images/common/icon-delete.svg" alt="">
												</button>
												<img src="../../dashboard/images/common/people-no-img.svg" alt="">
												<div class="info">
													<p class="company-name">
														<img src="../../dashboard/images/common/logo-hanmac.svg" alt="">(주)한맥기술
													</p>
													<p class="name">김신지<span>연구원</span></p>
												</div>
											</div>
											<span class="badge integrated">통합</span>
										</li>
										<li data-role="personnel">
											<div class="selected-list-wrap">
												<button type="button" class="remove-btn">
													<img src="../../dashboard/images/common/icon-delete.svg" alt="">
												</button>
												<img src="../../dashboard/images/common/people-no-img.svg" alt="">
												<div class="info">
													<p class="company-name">
														<img src="../../dashboard/images/common/logo-jh.svg" alt="">장헌
													</p>
													<p class="name">김신지<span>연구원</span></p>
												</div>
											</div>
											<span class="badge personnel">인사</span>
										</li>
										<li data-role="overall">
											<div class="selected-list-wrap">
												<button type="button" class="remove-btn">
													<img src="../../dashboard/images/common/icon-delete.svg" alt="">
												</button>
												<img src="../../dashboard/images/common/people-no-img.svg" alt="">
												<div class="info">
													<p class="company-name">
														<img src="../../dashboard/images/common/logo-ptc.svg" alt="">피티씨
													</p>
													<p class="name">김신지<span>연구원</span></p>
												</div>
											</div>
											<span class="badge overall">전체</span>
										</li>
										<li data-role="general">
											<div class="selected-list-wrap">
												<button type="button" class="remove-btn">
													<img src="../../dashboard/images/common/icon-delete.svg" alt="">
												</button>
												<img src="../../dashboard/images/common/people-no-img.svg" alt="">
												<div class="info">
													<p class="company-name">
														<img src="../../dashboard/images/common/logo-sanman.svg" alt="">삼안
													</p>
													<p class="name">김신지<span>연구원</span></p>
												</div>
											</div>
											<span class="badge general">일반</span>
										</li>
									</ul>
								</section>

							</main>

							<footer class="modal-footer">
								<button type="button" class="btn btn-white">닫기</button>
								<button type="button" class="btn btn-primary">저장</button>
							</footer>
						</div>
					</div>
					<!-- 권한 설정 모달 끝 -->
	
	                <button type="button" class="page-header__download-btn btn" aria-label="데이터 다운로드">
	                    <img src="../../dashboard/images/common/icon-download.svg" alt="" />
	                    <span>데이터 다운로드</span>
	                </button>
	            </div>
	        </div>
	
	        <!-- dashboard-content -->
	        <section class="dashboard-content">
	            <!-- 상단 요약 카드 -->
	            <div class="summary-grid" aria-label="진행 프로젝트">
	
	                <!-- ① 진행 프로젝트 -->
	                <div class="summary-card project-status">
	                    <h4>진행 프로젝트</h4>
	                    <div class="project-count">
	                        <div class="item">
	                            <span class="badge primary">주관</span>
	                            <div class="value">
	                                <strong id="primary_value"></strong>
	                                <span>개</span>
	                            </div>
	                        </div>
	
	                        <div class="item">
	                            <span class="badge assist">지원</span>
	                            <div class="value">
	                                <strong id="assist_value"></strong>
	                                <span>개</span>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	
	                <!-- ② 프로젝트 유형별 M/H -->
	                <div class="summary-card mh-chart">
	                    <h4>프로젝트 유형별 M/H</h4>
	                    <ul class="summary-legend">
	                        <li class="legend-item is-sales">
	                            <span class="legend-dot"></span>
	                            <span class="legend-text"></span>
	                        </li>
	                        <li class="legend-item is-non-sales">
	                            <span class="legend-dot"></span>
	                            <span class="legend-text"></span>
	                        </li>
	                        <li class="legend-item is-common">
	                            <span class="legend-dot"></span>
	                            <span class="legend-text"></span>
	                        </li>
	                    </ul>
	
	                    <!-- 반원 차트 시작 -->
	                    <div class="mh-chart-mask">
	                        <div id="mhChart"></div>
	                        <div class="mh-chart-center">
	                            <span class="label"></span>
	                            <strong class="value"></strong>
	                        </div>
	                    </div>
	                </div>
	
	                <!-- ③ 비용 총합 -->
	                <div class="summary-card cost-summary">
	                    <h4>비용 총합</h4>
	                    <div class="cost-list">
	                        <div class="item">
	                            <div class="bage-info">
	                                <span class="badge primary">직접비</span>
	                                <div class="tooltip-container">
	                                    <img src="../../dashboard/images/common/icon-information.svg" alt="정보" class="tool-tip-trigger" data-tooltip-trigger="directCostTooltip" data-tooltip-hover>
	
	                                    <div class="tooltip-box" data-tooltip-target="directCostTooltip">
	                                        <div class="tooltip-header">
	                                            <span>정보</span>
	                                            <button type="button" class="tooltip-close" data-tooltip-close><img src="../../dashboard/images/common/icon-tooltip-close.svg" alt=""></button>
	                                        </div>
	                                        <div class="tooltip-body">
	                                            직접비 산정 근거 (표준단가) / 기준년월별
	                                        </div>
	                                    </div>
	                                </div>
	                            </div>
	                            <div class="value">
	                                <strong id="primary_cost_value"></strong>
	                                <span>천원</span>
	                            </div>
	                        </div>
	
	                        <div class="item">
	                            <span class="badge assist">간접비</span>
	                            <div class="value">
	                                <strong id="assist_cost_value"></strong>
	                                <span>천원</span>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	                <!-- 근태 현황 -->
	                <div class="attendance_container"></div>
	            </div>
	
	            <!-- 프로젝트별 상세 집행 현황 시작 -->
	            <div class="project-section"></div>
	            <!-- 프로젝트별 상세 집행 현황 끝 -->
	
	            <div class="member-section">
		            <!-- 인원별 현황 시작 -->
		            <section class="member-status"></section>
		            <!-- 인원별 현황 끝 -->
		            
		            <!-- 프로젝트별 점유율 시작 -->
		            <section class="project-ratio">
		                <div class="title-text-wrap">
		                    <img src="../../dashboard/images/common/title-bullet.svg" alt="">
		                    <h3>프로젝트별 점유율</h3>
		                </div>
		
		                <div class="ratio-sub-title">투입시간 비율</div>
		
		                <div class="ratio-content">
		                    <!-- LEFT : LIST -->
							<div class="ratio-list-wrap">
		                        <ul class="ratio-list">  
			                        {*
			                        <li class="item blue">
			                            <span class="dot"></span>
			                            <strong>공학용 사이니지</strong>
			                            <span class="percent">26.9% (64h)</span>
			                            <span class="trend down">2.5% ↓</span>
			                        </li>
			
			                        <li class="item pink">
			                            <span class="dot"></span>
			                            <strong>대산~당진 시공 2공구</strong>
			                            <span class="percent">26.1% (62h)</span>
			                            <span class="trend up">3.8% ↑</span>
			                        </li>
			
			                        <li class="item green">
			                            <span class="dot"></span>
			                            <strong>GIS Mapper</strong>
			                            <span class="percent">21.8% (52h)</span>
			                            <span class="trend up">6.2% ↑</span>
			                        </li>
			
			                        <li class="item orange">
			                            <span class="dot"></span>
			                            <strong>GAIA</strong>
			                            <span class="percent">17.6% (42h)</span>
			                            <span class="trend down">10.5% ↓</span>
			                        </li>
			
			                        <li class="item gray">
			                            <span class="dot"></span>
			                            <strong>KNGIL</strong>
			                            <span class="percent">4.2% (10h)</span>
			                            <span class="trend up">3.5% ↑</span>
			                        </li>
			                        *}
								</ul>
							</div>
							<div class="ratio-summary">
								 <div class="summary-box">
					                <span class="label">총 투입시간</span>
					                <strong id="project_mh"></strong>
					            </div>
					            
					            <div class="summary-box">
					                <span class="label">프로젝트 수</span>
					                <strong id="project_cnt"></strong>
					            </div>
							</div>
		                    {*
		                    
		                    <!-- RIGHT : DONUT -->
		                    <div class="donut-wrap">
		                        <img src="../../dashboard/images/common/persent-graph.svg" alt="">
		                    </div>
		                    *}
		                </div>
		            </section>
		            <!-- 프로젝트별 점유율 끝 -->
	            </div>
	            <!-- ===============================
	                1) 업무 검토 모달
	            =============================== -->
	            <div class="confirm-modal confirm-modal--review"><!-- is-open 클래스 추가시 모달 오픈-->
	            <div class="confirm-modal-overlay"></div>
	
	            <div class="confirm-modal-content">
	                <!-- Header -->
	                <div class="confirm-modal-header">
	                <span>확인 및 점검</span>
	                <button class="confirm-modal-close" type="button">
	                    <img src="../../dashboard/images/common/icon-modal-close.svg" alt="">
	                </button>
	                </div>
	
	                <!-- Body -->
	                <div class="confirm-modal-body">
	                <div class="confirm-modal-icon">
	                    <img src="../../dashboard/images/common/icon-warning.svg" alt="">
	                </div>
	                <p class="confirm-modal-message">
	                    <strong>인트라넷</strong>을 통해 <strong>업무 검토</strong>를 진행해 주세요.
	                </p>
	                </div>
	
	                <!-- Footer -->
	                <div class="confirm-modal-footer">
	                <button type="button" class="btn btn-primary">확인</button>
	                </div>
	            </div>
	            </div>
	
	
	            <!-- ===============================
	                2) 근태 현황 확인 모달
	            =============================== -->
	            <div class="confirm-modal confirm-modal--attendance "><!-- is-open 클래스 추가시 모달 오픈-->
	            <div class="confirm-modal-overlay"></div>
	
	            <div class="confirm-modal-content">
	                <!-- Header -->
	                <div class="confirm-modal-header">
	                <span>확인 및 점검</span>
	                <button class="confirm-modal-close" type="button">
	                    <img src="../../dashboard/images/common/icon-modal-close.svg" alt="">
	                </button>
	                </div>
	
	                <!-- Body -->
	                <div class="confirm-modal-body">
	                <div class="confirm-modal-icon">
	                    <img src="../../dashboard/images/common/icon-warning.svg" alt="">
	                </div>
	                <p class="confirm-modal-message">
	                    근태 현황의 <strong>확인</strong>이 필요합니다.
	                </p>
	                </div>
	
	                <!-- Footer -->
	                <div class="confirm-modal-footer">
	                <button type="button" class="btn btn-primary">확인</button>
	                </div>
	            </div>
	            </div>
	        </section>
	        <footer>
	            <p>Copyright(c) BARON Consultant Co.,Ltd All Rights Reserved.</p>
	        </footer>
	    </main>
	    <!-- 메인 컨텐츠 끝 -->
	     
	</body>
</html>
