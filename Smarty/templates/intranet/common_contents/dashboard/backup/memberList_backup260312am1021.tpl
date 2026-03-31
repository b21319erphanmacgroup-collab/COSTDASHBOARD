<div class="title-text-wrap">
	<img src="../../dashboard/images/common/title-bullet.svg" alt="타이틀 불렛">
	<h3>인원별 현황</h3>
</div>

<div class="member-table-wrap">
	<div class="member-table">
	    <!-- HEADER -->
	    <div class="member-table-head">
	        <div class="col name">이름</div>
	        <div class="col project">투입 프로젝트</div>
		</div>
		
		<div class="user_list" id="team_{$deptNo}">
			{foreach from=$users item=user}
			<div class="member-table-row" user="{$user.USER_ID}">
                <!-- 이름 -->
                <div class="col name">
                    <div class="name-wrap">
                        <span class="people-name">{$user.USER_NAME} {$user.GRADE_NAME}</span>
						{if $user.POSITION_CODE == "040"}
                        <span class="badge leader">{$user.TITLE_NAME}</span>
						{/if}
                    </div>
                    <div class="overtime-wrap">
                        {if $userWorkTime[$user.USER_ID].OVER_MH > 0}
                        <img src="../../dashboard/images/common/icon-overtime.svg" alt="오버타임 아이콘">
                        <span>
                        초과근무 {$userWorkTime[$user.USER_ID].OVER_MH_LABEL} 시간
                        </span>
                        {/if}
                    </div>
                    <div class="overtime-num">
                        <span class="over-time">{$userWorkTime[$user.USER_ID].MH_LABEL|@default:0}/</span>
                        <span class="default-time">{$total}h</span>
                    </div>
                </div>
                
				<!-- 투입 프로젝트 -->
				<div class="col project" user="{$user.USER_ID}">
	            	{foreach from=$userWorkData[$user.USER_ID] key=projCode item=row}
						<!-- 출, 퇴근 -->
						<div class="project-item" type="CLOCK_IN">
							<div class="project-title">
								<span class="dot {if $row.0.OVER_MH != 0}red{/if}"></span>
								<strong>{$row.0.PROJ_NAME}</strong>
							</div>
							<div class="calendar-wrap">
								<img src="../../dashboard/images/common/icon-calendar.svg" alt="달력 아이콘">
								<span class="time-date">{$row.INITALENTRY_TIME} ~ 24.02.28</span>
							</div>
							<div class="project-text-wrap">
								<img src="../../dashboard/images/common/icon-clipboard.svg" alt="작업한 내용 아이콘">
								<span>{$row.0.JOB_NAME} / {$row.0.ACTIVITY_NAME}</span>
							</div>
							<div class="time-wrap">
								<img src="../../dashboard/images/common/icon-clock.svg" alt="투입 시간 아이콘">
								<span class="time">
									{$row.0.MH_SUM/60|string_format:"%.1f"}{if $row.0.OVER_MH != 0}<span class="add-time">+{$row.0.OVER_MH/60|string_format:"%.1f"}</span>{/if}
									시간
								</span>
							</div>
							<div class="line-graph">
								<span class="total-time">{$row.0.MH_SUM/60|string_format:"%.1f"} 시간</span>
								<div class="graph-wrap progress">
									<div class="fill blue" style="width: {$row.0.MH_SUM/60/$total*100}%;"></div>
									<!--blue: 기준시간, red: 초과근무,주말근무, green:출장 -->
								</div>
							</div>
						</div>
					{/foreach}
					
					{foreach from=$businessTrip[$user.USER_ID] key=projCode item=row}
						<!-- 출장 -->
						<div class="project-item" type="CLOCK_IN">
							<div class="project-title">
								<span class="dot green"></span>
								<strong>{$row.0.PROJ_NAME}</strong>
							</div>
							<div class="calendar-wrap">
								<img src="../../dashboard/images/common/icon-calendar.svg" alt="달력 아이콘">
								<span class="time-date">{$row.INITALENTRY_TIME} ~ 24.02.28</span>
							</div>
							<div class="project-text-wrap">
								<img src="../../dashboard/images/common/icon-clipboard.svg" alt="작업한 내용 아이콘">
								<span>{$row.0.JOB_NAME} / {$row.0.ACTIVITY_NAME}</span>
							</div>
							<div class="time-wrap">
								<img src="../../dashboard/images/common/icon-clock.svg" alt="투입 시간 아이콘">
								<span class="time">
									{$row.0.MH_SUM/60|string_format:"%.1f"}{if $row.0.OVER_MH != 0}<span class="add-time">+{$row.0.OVER_MH/60|string_format:"%.1f"}</span>{/if}
									시간
								</span>
							</div>
							<div class="line-graph">
								<span class="total-time">{$row.0.MH_SUM/60|string_format:"%.1f"} 시간</span>
								<div class="graph-wrap progress">
									<div class="fill blue" style="width: {$row.0.MH_SUM/60/$total*100}%;"></div>
									<!--blue: 기준시간, red: 초과근무,주말근무, green:출장 -->
								</div>
							</div>
						</div>
					{/foreach}
					</div>
            </div>
			{/foreach}
        </div>
	</div>
</div>