/**
 * battle_sim.js — Mô phỏng một lượt đánh trên màn chiến đấu (pve.php, boss.php).
 *
 * Quy ước HTML:
 *   [data-sim-root]            khung trận; data-fast="1" khi trang sẽ tự làm mới (rút gọn thời gian)
 *   .pve-step                  từng dòng diễn biến (hiện lần lượt)
 *   [data-side="1"|"2"]        bên quái/boss (1) và nhân vật (2): nơi bay số sát thương, rung khi trúng
 *   [data-sim="1"|"2"]         phần tử máu: <i> đổi width theo data-to (%), phần tử khác đổi chữ theo data-to
 *   [data-sim-banner]          dòng tên chiêu ở giữa
 *   #pve-pop                   popup kết quả (nếu có) — chỉ bật sau khi diễn xong
 */
(function () {
    var root = document.querySelector('[data-sim-root]');
    if (!root) return;
    var steps = root.querySelectorAll('.pve-step');
    var pop = document.getElementById('pve-pop');
    var banner = root.querySelector('[data-sim-banner]');
    var sides = { 1: root.querySelector('[data-side="1"]'), 2: root.querySelector('[data-side="2"]') };

    // Cập nhật máu bên g về giá trị cuối lượt
    function hp(g) {
        var els = root.querySelectorAll('[data-sim="' + g + '"]');
        for (var i = 0; i < els.length; i++) {
            var e = els[i];
            if (e.tagName === 'I') e.style.width = e.getAttribute('data-to') + '%';
            else e.innerHTML = e.getAttribute('data-to');
        }
    }
    // Số bay lên + rung bên trúng đòn
    function fx(g, txt, cls) {
        var sd = sides[g];
        if (!sd) return;
        var f = document.createElement('span');
        f.className = 'pva-float ' + cls;
        f.textContent = txt;
        sd.appendChild(f);
        setTimeout(function () { if (f.parentNode) f.parentNode.removeChild(f); }, 1400);
        if (cls !== 'heal') {
            sd.className = sd.className.replace(/ hit/g, '');
            void sd.offsetWidth;
            sd.className += ' hit';
        }
    }
    function say(t) {
        if (!banner) return;
        banner.textContent = t;
        banner.className = banner.className.replace(/ on/g, '');
        void banner.offsetWidth;
        banner.className += ' on';
    }
    function showPop(delay) {
        if (pop) setTimeout(function () { pop.className = 'pve-pop show'; }, delay);
    }

    var n = steps.length, crit = false, doneMob = false, doneMe = false;
    if (pop) pop.className = 'pve-pop';
    if (!n) { hp(1); hp(2); showPop(500); return; }
    // Trận kết thúc / không tự làm mới: chạy chậm cho dễ theo dõi; trang tự làm mới sau 2s: gói gọn ≤ ~1.6s
    var gap = (pop || root.getAttribute('data-fast') !== '1') ? 520 : Math.min(320, Math.floor(1300 / n));
    for (var i = 0; i < n; i++) { steps[i].style.animation = 'none'; steps[i].style.opacity = 0; }

    function run(k) {
        var t = steps[k].textContent, m;
        steps[k].style.animation = 'pveStepIn .35s ease-out both';
        steps[k].style.opacity = '';
        if (/Bạo kích/.test(t)) crit = true;
        if ((m = t.match(/«([^»]+)»/))) say(m[1]);
        else if (/Tung chiêu|Thi triển/.test(t)) say(t.replace(/^[^:]*:\s*/, '').replace(/[!(].*$/, ''));
        if ((m = t.match(/(?:Gây|gây)\s*([\d.,]+)\s*sát thương/))) {
            fx(1, '-' + m[1], crit ? 'crit' : 'dmg'); hp(1); doneMob = true; crit = false;
        } else if ((m = t.match(/Nhận\s*([\d.,]+)\s*sát thương/))) {
            fx(2, '-' + m[1], /bạo kích|trọng kích/.test(t) ? 'crit' : 'dmg'); hp(2); doneMe = true;
        } else if ((m = t.match(/(?:Hồi phục|hồi|hút máu thêm \+)\s*([\d.,]+)/))) {
            fx(2, '+' + m[1], 'heal');
        } else if (/vung trúng khoảng không|né khỏi/.test(t)) {
            fx(2, 'Né', 'miss');
        } else if (/đánh trượt/.test(t)) {
            fx(1, 'Trượt', 'miss');
        }
        if (/giết chết/.test(t) && sides[1]) sides[1].className += ' dead';
        if (k === n - 1) {
            if (!doneMob) hp(1);
            if (!doneMe) hp(2);
            showPop(900);
        }
    }
    for (var j = 0; j < n; j++) (function (k) { setTimeout(function () { run(k); }, 250 + k * gap); })(j);
})();
