<!-- 근태 현황 -->
<div class="summary-card status {if $loginUser.POSITION_CODE == "40"}is-leader{else}is-normal{/if}">
    <!-- is-normal:팀장,팀원 정상화면, is-warning: 팀원만 보이는 관리필요, is-leader: 팀장만 보이는 팀원 근태현황 -->
    <h4>근태 현황(종료일 기준 마감월 집계)</h4>

    <!-- ① 기본 / ② 관리 필요 공용 -->
    <div class="status-wrap">
        <p class="status-text">
            <span class="text-normal">
                모든 근태 현황이 <em>정상</em>입니다.
            </span>

            <span class="text-warning">
                근태 관리가 <em>필요</em>합니다.
            </span>
        </p>

        <div class="status-icon">
        <img src="../../dashboard/images/common/icon-good.svg" alt="">
        </div>
    </div>

    <!-- ③ 팀장 상세 -->
    <div class="attendance-list">
        <!-- 지각 -->
        <div class="attendance-item late">
        <span class="badge">지각</span>
        <ul>
        	{foreach from=$attendance item=row}
            <li>{$row.USER_NAME} {$GRADE_NAME}<strong>{$row.TARDY}회</strong></li>
            {/foreach}
        </ul>
        </div>

        <!-- 연차 -->
        <div class="attendance-item leave">
        <span class="badge">연차</span>
        <ul>
        	{foreach from=$attendance item=row}
            <li>{$row.USER_NAME} {$GRADE_NAME}<strong>{$row.REMAIN_DAY}일 {$row.REMAIN_TIME}시간</strong></li>
            {/foreach}
        </ul>
        </div>
    </div>
</div>