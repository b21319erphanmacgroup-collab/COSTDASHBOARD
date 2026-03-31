/* design.js - 공통 UI 기능
=============================================================
  0. aside menu      — nav-wrap 내 1Depth~nDepth 토글/active
  1. 툴팁/팝업 모달  — data-tooltip-* 속성 기반 호버·클릭 제어
  2. 업무 검토 모달  — .confirm-modal 열기/닫기
  3. 바 그래프       — .total 공간 부족 시 .is-above 토글
  4. 권한 설정 모달  — #modalAuthority 열기/닫기/저장/취소

============================================================= */



/* =========================================================
0. aside menu (1Depth ~ nDepth)
========================================================= */
document.addEventListener('DOMContentLoaded', () => {

    const navWrap = document.querySelector('.nav-wrap');
    if (!navWrap) return;

    // 초기 DOM 캐싱 — 매 클릭마다 쿼리하지 않도록
    const allDepth1    = navWrap.querySelectorAll('.nav-item.has-submenu');
    const allMenuItems = navWrap.querySelectorAll('.nav-item, .sub-item');

    navWrap.addEventListener('click', (e) => {

        const target       = e.target;
        const depth1HasSub = target.closest('.nav-item.has-submenu');
        const subInner     = target.closest('.sub-item-inner');
        const depth1Link   = target.closest('.nav-item.link');

        // 1depth — 서브메뉴 있는 항목 토글 (서브메뉴 밖 클릭만)
        if (depth1HasSub && !target.closest('.sub-menu')) {
            e.preventDefault();

            const isOpen = depth1HasSub.classList.contains('open');
            allDepth1.forEach(item => item.classList.remove('open'));
            if (!isOpen) depth1HasSub.classList.add('open');
            return;
        }

        // 서브메뉴 항목 or 링크형 1depth 클릭
        if (subInner || depth1Link) {
            e.preventDefault();

            allMenuItems.forEach(item => item.classList.remove('active'));

            const currentItem = subInner ? subInner.closest('.sub-item') : depth1Link;
            if (!currentItem) return;

            currentItem.classList.add('active');

            // 부모 nav-item active + open
            const parentNavItem = currentItem.closest('.nav-item');
            if (parentNavItem) parentNavItem.classList.add('active', 'open');

            // 중첩 sub-menu 상위 순회
            let parentSub = currentItem.closest('.sub-menu');
            while (parentSub) {
                const parentItem = parentSub.closest('.sub-item, .nav-item');
                if (!parentItem) break;
                parentItem.classList.add('active', 'open');
                parentSub = parentItem.closest('.sub-menu');
            }
        }
    });
});



/* =========================================================
1. 툴팁/팝업 모달
   data-tooltip-trigger="ID"   — 트리거 버튼 (클릭 시 토글)
   data-tooltip-hover          — 트리거에 추가 시 호버로 열고 벗어나면 닫힘
   data-tooltip-hover-open     — 트리거에 추가 시 호버로 열기만 (닫기는 버튼/외부클릭)
   data-tooltip-target="ID"    — 팝업 패널 (내부 클릭 시 닫히지 않음)
   data-tooltip-close          — 패널 내부 닫기 버튼
========================================================= */

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('[data-tooltip-trigger]').forEach(trigger => {

        const targetId = trigger.dataset.tooltipTrigger;
        const modal = document.querySelector(`[data-tooltip-target="${targetId}"]`);

        if (!modal) return;

        let isOpen = false;
        let closeTimer = null;

        const open  = () => { clearTimeout(closeTimer); modal.style.display = 'block'; isOpen = true; };
        const close = () => { modal.style.display = 'none';  isOpen = false; };

        // data-tooltip-hover: 마우스 올리면 열고, 벗어나면 닫기
        if (trigger.hasAttribute('data-tooltip-hover')) {
            const scheduleClose = () => { closeTimer = setTimeout(close, 100); };
            trigger.addEventListener('mouseenter', () => { if (!isOpen) open(); });
            trigger.addEventListener('mouseleave', scheduleClose);
            modal.addEventListener('mouseenter', () => clearTimeout(closeTimer));
            modal.addEventListener('mouseleave', scheduleClose);
        }

        // data-tooltip-hover-open: 마우스 올리면 열기만 (닫기는 버튼/외부클릭만)
        if (trigger.hasAttribute('data-tooltip-hover-open')) {
            trigger.addEventListener('mouseenter', () => { if (!isOpen) open(); });
        }

        // 클릭 시 토글
        trigger.addEventListener('click', e => { e.stopPropagation(); isOpen ? close() : open(); });

        // 모달 내부 클릭 시 닫히지 않도록
        modal.addEventListener('click', e => e.stopPropagation());

        // 닫기 버튼
        modal.querySelectorAll('[data-tooltip-close]').forEach(btn => {
            btn.addEventListener('click', close);
        });

        // 외부 클릭 시 닫기
        document.addEventListener('click', close);
    });

});


/* =========================================================
2. 업무 검토 모달 (범용 confirm-modal)
   열기:   data-confirm-trigger="modalId" — 트리거에 지정
            또는 JS에서 직접 openConfirmModal(el) 호출
   모달:   .confirm-modal + id="modalId"
   닫기:   .confirm-modal-close / .confirm-modal-overlay / .btn-white
   확인:   .confirm-modal-footer .btn-primary
            — data-confirm-action 속성이 있으면 'confirm-modal:ok' 커스텀 이벤트 발생
   이벤트 수신 예)
   document.getElementById('reviewModal').addEventListener('confirm-modal:ok', e => {
       console.log(e.detail.action); // data-confirm-action 값
   });
========================================================= */

document.addEventListener('DOMContentLoaded', () => {

    /* =============================
    열기 / 닫기 유틸
    ============================= */
    function openConfirmModal(modal) {
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeConfirmModal(modal) {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    // 외부에서도 호출 가능하도록 전역 노출
    window.openConfirmModal  = openConfirmModal;
    window.closeConfirmModal = closeConfirmModal;


    /* =============================
    열기 트리거 바인딩
    ============================= */
    document.querySelectorAll('[data-confirm-trigger]').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modal = document.getElementById(trigger.dataset.confirmTrigger);
            if (modal) openConfirmModal(modal);
        });
    });


    /* =============================
    닫기 / 확인 이벤트 처리
    ============================= */
    document.querySelectorAll('.confirm-modal').forEach(modal => {

        modal.addEventListener('click', e => {

            const isClose   = e.target.closest('.confirm-modal-close');
            const isCancel  = e.target.closest('.confirm-modal-footer .btn-white');
            const isOverlay = e.target.classList.contains('confirm-modal-overlay');
            const isConfirm = e.target.closest('.confirm-modal-footer .btn-primary');

            if (isConfirm) {
                e.preventDefault();
                // data-confirm-action 속성이 있으면 커스텀 이벤트 발생
                const action = modal.dataset.confirmAction;
                if (action) {
                    modal.dispatchEvent(new CustomEvent('confirm-modal:ok', {
                        bubbles: true,
                        detail: { action }
                    }));
                }
                closeConfirmModal(modal);
                return;
            }

            if (isClose || isCancel || isOverlay) {
                e.preventDefault();
                closeConfirmModal(modal);
            }
        });
    });

});



/* =========================================================
3. 바 그래프 .total 오버플로우 처리
   .project-section .graph 안에서 .bar + .total 합이 너비를 초과하면
   .total에 .is-above 클래스를 추가 → position:absolute로 그래프 위에 오버레이
========================================================= */

document.addEventListener('DOMContentLoaded', () => {

    function updateBarTotalPosition() {
        document.querySelectorAll('.project-section .graph').forEach(graph => {
            const bar   = graph.querySelector('.bar');
            const total = graph.querySelector('.total');
            if (!bar || !total) return;

            const overflow = bar.offsetWidth + total.offsetWidth > graph.offsetWidth;
            total.classList.toggle('is-above', overflow);
        });
    }

    // 윈도우 리사이즈 시 재계산
    window.addEventListener('resize', updateBarTotalPosition);

    // .project-section에 AJAX로 내용이 삽입되면 자동 감지 후 실행
    const projectSection = document.querySelector('.project-section');
    if (projectSection) {
        new MutationObserver(updateBarTotalPosition).observe(projectSection, { childList: true, subtree: true });
    }

});



/* =========================================================
4. 권한 설정 모달 - 이건 그냥 프로토타입예시입니다(개발시작하면 다시 만드는게 나으실듯)
   data-modal-target="modalAuthority" — 열기 트리거
   id="modalAuthority"                — 모달 본체
   .modal-close-btn                   — X버튼: 확인 없이 즉시 닫기
   .modal-footer .btn-white           — 취소: 변경사항 있으면 확인 후 닫기
   .modal-footer .btn-primary         — 저장: 저장 확인 후 닫기
   backdrop 클릭                      — 취소와 동일 처리
========================================================= */

document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('modalAuthority');
    if (!modal) return;

    const trigger      = document.querySelector('[data-modal-target="modalAuthority"]');
    const closeBtn     = modal.querySelector('.modal-close-btn');
    const modalFooter  = modal.querySelector('.modal-footer');
    const cancelBtn    = modalFooter ? modalFooter.querySelector('.btn-white')  : null;
    const saveBtn      = modalFooter ? modalFooter.querySelector('.btn-primary') : null;
    const selectedList = modal.querySelector('.selected-list');
    const searchInput  = modal.querySelector('.search-box input');
    const tabs         = modal.querySelectorAll('.tab-list li');

    const roleMap = {
        integrated: '통합',
        personnel:  '인사',
        overall:    '전체',
        general:    '일반'
    };

    let initialNames = [];

    // 초기 숨김 — SCSS 충돌 방지를 위해 인라인으로 확실히 제어
    modal.style.cssText = 'display: none !important;';


    /* =============================
    열기 / 닫기
    ============================= */
    function openModal() {
        modal.style.cssText = `
            display: flex !important;
            align-items: center;
            justify-content: center;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
        `;
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        initialNames = Array.from(
            selectedList.querySelectorAll('li .name')
        ).map(el => el.childNodes[0].textContent.trim());

        const activeTab = modal.querySelector('.tab-list li.active');
        if (activeTab) filterUsersByRole(activeTab.dataset.role);

        updateTabCounts();
        syncUserListActive();
    }

    function closeModal() {
        modal.style.cssText = 'display: none !important;';
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }


    /* =============================
    확인 모달 (저장/취소)
    ============================= */
    function showConfirmModal(type, onConfirm) {

        const count = getChangeCount();

        const message = type === 'save'
            ? `${count}개의 변경사항을 저장하시겠습니까?`
            : `${count}개의 변경사항을 저장하지 않고 취소하시겠습니까?`;

        const existing = document.getElementById('authorityConfirmModal');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.id = 'authorityConfirmModal';
        overlay.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        `;

        overlay.innerHTML = `
            <div style="background:#fff;border-radius:0.8rem;box-shadow:0 8px 32px rgba(0,0,0,0.2);padding:1.2rem 1.6rem;min-width:34rem;max-width:90vw;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:0.4rem;margin-bottom:1.6rem;border-bottom:1px solid #e0e0e0;">
                    <span style="font-size:1.5rem;font-weight:700;color:#222;">관리자 설정</span>
                    <button id="confirmModalClose" style="background:none;border:none;cursor:pointer;font-size:2.8rem;color:#888;line-height:1;padding:0 0.4rem;">×</button>
                </div>
                <p style="font-size:1.4rem;color:#555;margin-bottom:2.4rem;line-height:1.6;">${message}</p>
                <div style="display:flex;gap:0.8rem;justify-content:flex-end;">
                    <button id="confirmModalCancel" style="min-width:8rem;padding:0.9rem 1.6rem;border-radius:0.4rem;border:1px solid #ccc;background:#f5f5f5;font-size:1.4rem;font-weight:600;color:#555;cursor:pointer;">취소</button>
                    <button id="confirmModalOk" style="min-width:8rem;padding:0.9rem 1.6rem;border-radius:0.4rem;border:none;background:#555e7b;font-size:1.4rem;font-weight:600;color:#fff;cursor:pointer;">확인</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        const remove = () => overlay.remove();

        overlay.querySelector('#confirmModalClose').addEventListener('click', remove);
        overlay.querySelector('#confirmModalCancel').addEventListener('click', remove);
        overlay.querySelector('#confirmModalOk').addEventListener('click', () => { remove(); onConfirm(); });
        overlay.addEventListener('click', e => { if (e.target === overlay) remove(); });
    }


    /* =============================
    변경사항 카운트
    ============================= */
    function getChangeCount() {
        const currentNames = Array.from(
            selectedList.querySelectorAll('li .name')
        ).map(el => el.childNodes[0].textContent.trim());

        return currentNames.filter(n => !initialNames.includes(n)).length
             + initialNames.filter(n => !currentNames.includes(n)).length;
    }


    /* =============================
    트리거 / 닫기 버튼 바인딩
    ============================= */
    if (trigger)   trigger.addEventListener('click', openModal);
    if (closeBtn)  closeBtn.addEventListener('click', closeModal);

    if (cancelBtn) cancelBtn.addEventListener('click', () => showConfirmModal('cancel', closeModal));

    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            showConfirmModal('save', () => {
                const result = Array.from(selectedList.querySelectorAll('li')).map(li => ({
                    name: li.querySelector('.name').childNodes[0].textContent.trim(),
                    role: li.dataset.role
                }));
                console.log('권한 저장 데이터', result);
                initialNames = result.map(r => r.name);
                closeModal();
            });
        });
    }

    // 배경(backdrop) 클릭 → 취소 확인
    modal.addEventListener('click', e => {
        if (e.target === modal) showConfirmModal('cancel', closeModal);
    });


    /* =============================
    부서 트리 펼침/접기
    ============================= */
    const deptTree = modal.querySelector('.dept-tree');

    if (deptTree) {
        deptTree.addEventListener('click', e => {

            if (e.target.closest('.check-btn') || e.target.closest('.user-list li')) return;

            const deptRow = e.target.closest('.dept-row');
            if (!deptRow) return;

            const node = deptRow.closest('.dept-node');
            if (!node) return;

            const children = node.querySelector(':scope > .dept-children');
            if (!children) return;

            deptRow.querySelector('.toggle-btn')?.classList.toggle('is-close');
            children.classList.toggle('is-hidden');
        });
    }


    /* =============================
    유틸 함수
    ============================= */
    function isUserExists(name) {
        return Array.from(selectedList.querySelectorAll('.name'))
            .some(el => el.childNodes[0].textContent.trim() === name);
    }

    function getActiveRole() {
        return modal.querySelector('.tab-list li.active')?.dataset.role ?? 'general';
    }

    function filterUsersByRole(role) {
        selectedList.querySelectorAll('li').forEach(li => {
            li.style.display = li.dataset.role === role ? '' : 'none';
        });
    }

    function syncUserListActive() {
        const selectedNames = new Set(
            Array.from(selectedList.querySelectorAll('li .name'))
                .map(el => el.childNodes[0].textContent.trim())
        );
        modal.querySelectorAll('.user-list li').forEach(li => {
            const nameEl = li.querySelector('.name');
            if (nameEl) li.classList.toggle('active', selectedNames.has(nameEl.childNodes[0].textContent.trim()));
        });
    }

    function updateTabCounts() {
        const counts = { integrated: 0, personnel: 0, overall: 0, general: 0 };
        selectedList.querySelectorAll('li').forEach(li => {
            if (counts[li.dataset.role] !== undefined) counts[li.dataset.role]++;
        });
        modal.querySelectorAll('.tab-list li').forEach(tab => {
            let countEl = tab.querySelector('.count');
            if (!countEl) {
                countEl = document.createElement('span');
                countEl.className = 'count';
                tab.appendChild(countEl);
            }
            countEl.textContent = `(${counts[tab.dataset.role]})`;
        });
    }

    function buildUserItem(nameEl, companyEl, imgEl, role) {
        const li = document.createElement('li');
        li.dataset.role = role;
        li.innerHTML = `
            <div class="selected-list-wrap">
                <button type="button" class="remove-btn">
                    <img src="../../dashboard/images/common/icon-delete.svg" alt="">
                </button>
                <img src="${imgEl.src}" alt="">
                <div class="info">
                    <p class="company-name">${companyEl.innerHTML}</p>
                    <p class="name">${nameEl.innerHTML}</p>
                </div>
            </div>
            <span class="badge ${role}">${roleMap[role]}</span>
        `;
        return li;
    }

    function afterChange() {
        updateTabCounts();
        filterUsersByRole(getActiveRole());
        syncUserListActive();
    }


    /* =============================
    탭 전환
    ============================= */
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            filterUsersByRole(tab.dataset.role);
        });
    });


    /* =============================
    팀 체크박스 선택/해제
    ============================= */
    modal.addEventListener('click', e => {

        const checkBtn = e.target.closest('.check-btn');
        if (!checkBtn) return;

        e.stopPropagation();

        const isChecked = checkBtn.classList.toggle('checked');
        const node = checkBtn.closest('.dept-node');
        if (!node) return;

        const role = getActiveRole();

        node.querySelectorAll('.user-list li').forEach(li => {
            const nameEl    = li.querySelector('.name');
            const companyEl = li.querySelector('.company-name');
            const imgEl     = li.querySelector('.thumb');
            if (!nameEl || !companyEl || !imgEl) return;

            const name = nameEl.childNodes[0].textContent.trim();

            if (isChecked) {
                if (!isUserExists(name)) selectedList.appendChild(buildUserItem(nameEl, companyEl, imgEl, role));
            } else {
                selectedList.querySelectorAll('li').forEach(sel => {
                    if (sel.querySelector('.name')?.childNodes[0].textContent.trim() === name) sel.remove();
                });
            }
        });

        afterChange();
    });


    /* =============================
    개별 사용자 추가/제거
    ============================= */
    modal.addEventListener('click', e => {

        const userLi = e.target.closest('.user-list li');
        if (!userLi) return;

        const nameEl    = userLi.querySelector('.name');
        const companyEl = userLi.querySelector('.company-name');
        const imgEl     = userLi.querySelector('.thumb');
        if (!nameEl || !companyEl || !imgEl) return;

        const name = nameEl.childNodes[0].textContent.trim();

        if (isUserExists(name)) {
            selectedList.querySelectorAll('li').forEach(sel => {
                if (sel.querySelector('.name')?.childNodes[0].textContent.trim() === name) sel.remove();
            });
        } else {
            selectedList.appendChild(buildUserItem(nameEl, companyEl, imgEl, getActiveRole()));
        }

        afterChange();
    });


    /* =============================
    선택 목록에서 삭제
    ============================= */
    modal.addEventListener('click', e => {

        const selectedLi = e.target.closest('.selected-list li');
        if (!selectedLi) return;

        selectedLi.remove();
        afterChange();
    });


    /* =============================
    사용자 검색
    ============================= */
    if (searchInput) {
        searchInput.addEventListener('input', e => {
            const kw = e.target.value.toLowerCase();
            modal.querySelectorAll('.user-list li').forEach(li => {
                li.style.display = li.querySelector('.name').textContent.toLowerCase().includes(kw) ? '' : 'none';
            });
        });
    }

});



