 <!-- 프로젝트별 상세 집행 현황 -->
    <div class="title-text-wrap">
        <img src="../../dashboard/images/common/title-bullet.svg" alt="">
        <h3>프로젝트별 상세 집행 현황</h3>
    </div>

    <!-- 가로 스크롤 전용 -->
    <div class="project-table-wrap">
        <div class="project-table">
            <!-- HEADER -->
            <div class="project-table-head">
                <div class="col name">프로젝트 정보</div>
                <div class="col project">투입시간/인원</div>
                <div class="cost-header">
                    <div class="col mh">투입비용(천원)</div>
                    <div class="col mh">팀 투입 비용 / 프로젝트 총 투입 비용</div>
                </div>
                <!-- <div class="col mh">투입비용(천원)</div>
                <div class="col mh">팀 투입 비용 / 프로젝트 총 투입 비용</div> -->
            </div>
            <div class="project-table-row-wrap">
            	<!-- var rows = $('.project-table-row-wrap > .project-table-row[pm="T"]'); -->
            	<!-- var rows = $('.project-table-row-wrap > .project-table-row[pm="F"]'); -->
            	
            	{foreach from=$summary key=projCode item=rankCodes}
	            <!-- ROW 1 -->
	            <div class="project-table-row" PROJ_NAME="{$projectInfo[$projCode].PROJ_NAME}" 
	            	PROJ_CODE="{$projCode}" PM="{$projectInfo[$projCode].isPM}" MH="{$projectInfo[$projCode].MH}" REVENUE_TYPE="{$projectInfo[$projCode].REVENUE_TYPE}"
	            	normal="{$projectInfo[$projCode].NORMAL_MH}" over_mh="{$projectInfo[$projCode].OVER_MH}">
	                <!-- 프로젝트 정보 -->
	                <div class="project-info">
	                    <div class="project-title">
	                        <span class="dot {$projectInfo[$projCode].CLASS_NAME}"></span>
	                        <strong title="{$projectInfo[$projCode].PROJ_NAME}">{$projectInfo[$projCode].PROJ_NAME}</strong>
	                    </div>
	
	                    <div class="project-meta">
	                        <div class="meta-item">
	                            <img src="../../dashboard/images/common/icon-clipboard.svg" alt="">
	                            <span>{$projectInfo[$projCode].JOB_NAME}</span>
	                        </div>
	                        <div class="meta-item">
	                            <img src="../../dashboard/images/common/icon-clock-color.svg" alt="">
	                            <span>00.00.00</span>
	                        </div>
	                    </div>
	                </div>
	
	                <!-- 투입시간 / 인원 (Gantt 영역) -->
	            <div class="gantt-wrapper">
	
	            <!-- ================= LEFT : 수석 / 책임 ================= -->
	            <div class="gantt-col">
	                <!-- 눈금 -->
	                <div class="scale">
	                <span>00</span><span>10</span><span>20</span><span>30</span><span>40</span>
	                <span>50</span><span>60</span><span>70</span>
	                </div>
	
	                <div class="rows">
	                <!-- 수석 -->
	                <div class="row lead">
	                    <div class="info" title="{$rankCodes.C8A.0.LABEL|@default:""}">수석({$rankCodes.C8A.0.USER_CNT|@default:0})</div>
	                    <div class="graph">
	                    <div class="bar blue" data-mh="22" style="width: {$rankCodes.C8A.0.MH_SUM/$stdHour*100|string_format:"%.1f"}%;">
	                        <span class="bar-text" title="{$rankCodes.C8A.0.LABEL|@default:""}">
	                        {$rankCodes.C8A.0.LABEL|@default:""}
	                        </span>
	                    </div>
	                    <span class="total blue">(Total {$rankCodes.C8A.0.MH_SUM|@default:0}MH)</span>
	                    </div>
	                </div>
	
	                <!-- 책임 -->
	                <div class="row manager">
	                    <div class="info" title="{$rankCodes.E0.0.LABEL|@default:""}">책임({$rankCodes.E0.0.USER_CNT|@default:0})</div>
	                    <div class="graph">
	                    <div class="bar orange" data-mh="22" style="width: {$rankCodes.E0.0.MH_SUM/$stdHour*100|string_format:"%.1f"}%;">
	                        <span class="bar-text" title="{$rankCodes.E0.0.LABEL|@default:""}">
	                        {$rankCodes.E0.0.LABEL|@default:""}
	                        </span>
	                    </div>
	                    <span class="total orange">(Total {$rankCodes.E0.0.MH_SUM|@default:0}MH)</span>
	                    </div>
	                </div>
	                </div>
	            </div>
	
	            <!-- ================= RIGHT : 선임 / 연구원 ================= -->
	            <div class="gantt-col">
	
	                <!-- 눈금 -->
	                <div class="scale">
	                <span>00</span><span>10</span><span>20</span><span>30</span><span>40</span>
	                <span>50</span><span>60</span><span>70</span>
	                </div>
	
	                <div class="rows">
	
	                <!-- 선임 -->
	                <div class="row senior">
	                    <div class="info" title="{$rankCodes.E1C.0.LABEL|@default:""}">선임({$rankCodes.E1C.0.USER_CNT|@default:0})</div>
	                    <div class="graph">
	                    <div class="bar purple" data-mh="22" style="width: {$rankCodes.E1C.0.MH_SUM/$stdHour*100|string_format:"%.1f"}%;">
	                        <span class="bar-text" title="{$rankCodes.E1C.0.LABEL|@default:""}">
	                        {$rankCodes.E1C.0.LABEL|@default:""}
	                        </span>
	                    </div>
	                    <span class="total purple">(Total {$rankCodes.E1C.0.MH_SUM|@default:0}MH)</span>
	                    </div>
	                </div>
	
	                <!-- 연구원 -->
	                <div class="row researcher">
	                    <div class="info" title="{$rankCodes.E4A.0.LABEL|@default:""}">연구원({$rankCodes.E4A.0.USER_CNT|@default:0})</div>
	                    <div class="graph">
	                    <div class="bar green" data-mh="22" style="width: {$rankCodes.E4A.0.MH_SUM/$stdHour*100|string_format:"%.1f"}%;">
	                        <span class="bar-text" title="{$rankCodes.E4A.0.LABEL|@default:""}">
	                        {$rankCodes.E4A.0.LABEL|@default:""}
	                        </span>
	                    </div>
	                    <span class="total green">(Total {$rankCodes.E4A.0.MH_SUM|@default:0}MH)</span>
	                    </div>
	                </div>
	                </div>
	            </div>
	            </div>
	                <!-- 투입 비용 -->
	                <div class="cost-box ">
	                    <div class="cost-grid">
	                        <div class="item labor" cost="{$projectInfo[$projCode].LABORCOST}">
	                            <span>인건비</span>
	                            <div class="item-num" >
	                                 <strong>
	                                 	{$projectInfo[$projCode].LABORCOST|number_format:0:'.':','}
                                 	</strong>
	                                <em>/{$projectInfo[$projCode].LABORCOST_TOTAL|number_format:0:'.':','}</em>
	                            </div>
	                        </div>
	                        <div class="item business_trip" cost="{$projectInfo[$projCode].BUSINESS_TRIP}">
	                            <span>출장비</span>
	                            <div class="item-num">
	                                 <strong>{$projectInfo[$projCode].BUSINESS_TRIP|number_format:0:'.':','}</strong>
	                                <em>/{$projectInfo[$projCode].BUSINESS_TRIP_TOTAL|number_format:0:'.':','}</em>
	                            </div>
	                        </div>
	                        <div class="item welfare" cost="{$projectInfo[$projCode].WELFARE}">
	                            <span>복리후생비</span>
	                            <div class="item-num">
	                                 <strong>{$projectInfo[$projCode].WELFARE|number_format:0:'.':','}</strong>
	                                <em>/{$projectInfo[$projCode].WELFARE_TOTAL|number_format:0:'.':','}</em>
	                            </div>
	                        </div>
	                        <div class="item etc">
	                            <span>기타</span>
	                            <div class="item-num" cost="{$projectInfo[$projCode].ETC}">
	                                 <strong>{$projectInfo[$projCode].ETC|number_format:0:'.':','}</strong>
	                                <em>/{$projectInfo[$projCode].ETC_TOTAL|number_format:0:'.':','}</em>
	                            </div>
	                        </div>
	                    </div>
	
	                    <div class="total" cost="{$projectInfo[$projCode].SLIP_SUM}">
	                        <span>합계</span>
	                        <strong>{$projectInfo[$projCode].SLIP_SUM|number_format:0:'.':','}</strong>
	                    </div>
	                </div>
	            </div>
	            {/foreach}
            </div>           
        </div>
    </div>