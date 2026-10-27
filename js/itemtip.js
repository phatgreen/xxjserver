/*
 * Hop thong tin trang bi (tooltip).
 * Du lieu do PHP nanh san vao data-tip, khong goi them request.
 * May tinh: di chuot 250ms thi hien. Dien thoai: cham vao ten mon do.
 */
(function () {
    'use strict';

    var SHOW_DELAY = 250;
    var HIDE_DELAY = 120;
    var GAP = 10;

    var box = null;
    var backdrop = null;
    var trigger = null;
    var showTimer = null;
    var hideTimer = null;

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function parseData(el) {
        var raw = el.getAttribute('data-tip');
        if (!raw) return null;
        if (el.__tipData) return el.__tipData;
        var data = null;
        try { data = JSON.parse(raw); } catch (e) { data = null; }
        if (data) el.__tipData = data;
        return data;
    }

    function ensureBox() {
        if (box) return box;
        box = document.createElement('div');
        box.className = 'tcg-itemtip';
        box.setAttribute('role', 'tooltip');
        box.style.display = 'none';
        document.body.appendChild(box);
        box.addEventListener('mouseenter', function () { clearHide(); });
        box.addEventListener('mouseleave', scheduleHide);
        return box;
    }

    function statRow(row) {
        var label = esc(row[0]);
        var value = row[1];
        var suffix = esc(row[2] || '');
        var delta = row[3];
        var d = '';
        if (delta !== null && delta !== undefined && delta !== 0) {
            d = '<i class="' + (delta > 0 ? 'up' : 'down') + '">' +
                (delta > 0 ? '+' : '\u2212') + Math.abs(delta) + suffix + '</i>';
        }
        return '<div class="tcg-itemtip-row"><span>' + label + '</span>' +
            '<b>' + esc(value) + suffix + d + '</b></div>';
    }

    function tag(cls, text, color) {
        return '<span class="tcg-itemtip-tag ' + cls + '"' +
            (color ? ' style="color:' + esc(color) + ';border-color:' + esc(color) + '55"' : '') +
            '>' + esc(text) + '</span>';
    }

    function render(data, href) {
        var html = '';
        var elems = data.elem || [];
        var rar = data.rar || ['', ''];
        var elemTags = '';
        for (var ei = 0; ei < elems.length; ei++) {
            elemTags += tag('elem', elems[ei][0] + ' ' + elems[ei][1], elems[ei][2]);
        }

        html += '<div class="tcg-itemtip-head">';
        if (data.img) {
            html += '<div class="tcg-itemtip-icon"><img src="' + esc(data.img) + '" alt=""></div>';
        }
        html += '<div class="tcg-itemtip-heading">' +
            '<div class="tcg-itemtip-name" style="color:' + esc(data.color) + '">' +
            esc(data.name) + (data.qh ? '<em>+' + esc(data.qh) + '</em>' : '') + '</div>' +
            '<div class="tcg-itemtip-tags">' +
            tag('slot', data.slot || '') +
            elemTags +
            tag('rar', rar[0], rar[1]) +
            '</div></div></div>';

        html += '<div class="tcg-itemtip-meta">' +
            '<span>Cấp ' + esc(data.lv || 0) + '</span>' +
            '<span>Yêu cầu cảnh giới ' + esc(data.req || 0) + '</span>' +
            '</div>';

        if (data.canWear === false) {
            html += '<div class="tcg-itemtip-warn">Cảnh giới chưa đủ, không thể trang bị.</div>';
        }

        html += '<div class="tcg-itemtip-stats">';
        for (var i = 0; i < data.stats.length; i++) {
            html += statRow(data.stats[i]);
        }
        html += '</div>';

        if (data.versus) {
            html += '<div class="tcg-itemtip-vs">So với món đang mặc: <b>' +
                esc(data.versus.name) + (data.versus.qh ? ' +' + esc(data.versus.qh) : '') +
                '</b></div>';
        }

        if (data.text) {
            html += '<div class="tcg-itemtip-text">' + esc(data.text) + '</div>';
        }

        if (href) {
            html += '<a class="tcg-itemtip-more" href="' + esc(href) + '">Xem chi tiết →</a>';
        }

        ensureBox().innerHTML = html;
    }

    function place(el) {
        var b = ensureBox();
        b.style.visibility = 'hidden';
        b.style.display = 'block';
        var r = el.getBoundingClientRect();
        var bw = b.offsetWidth;
        var bh = b.offsetHeight;
        var vw = window.innerWidth;
        var vh = window.innerHeight;

        var left = r.left + r.width / 2 - bw / 2;
        if (left < 8) left = 8;
        if (left + bw > vw - 8) left = vw - bw - 8;

        var top = r.bottom + GAP;
        if (top + bh > vh - 8) {
            top = r.top - bh - GAP;
            if (top < 8) top = 8;
        }
        b.style.left = Math.round(left) + 'px';
        b.style.top = Math.round(top) + 'px';
        b.style.visibility = 'visible';
    }

    function clearShow() { if (showTimer) { clearTimeout(showTimer); showTimer = null; } }
    function clearHide() { if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; } }

    function show(el) {
        var data = parseData(el);
        if (!data || !data.stats) return;
        render(data, el.getAttribute('href'));
        trigger = el;
        place(el);
        clearHide();
    }

    function hide() {
        trigger = null;
        clearShow();
        clearHide();
        if (box) box.style.display = 'none';
        removeBackdrop();
    }

    function scheduleHide() {
        clearShow();
        clearHide();
        hideTimer = setTimeout(hide, HIDE_DELAY);
    }

    function scheduleShow(el) {
        clearHide();
        if (trigger === el && box && box.style.display === 'block') return;
        clearShow();
        showTimer = setTimeout(function () { show(el); }, SHOW_DELAY);
    }

    function findTrigger(node) {
        while (node && node !== document) {
            if (node.nodeType === 1 && node.hasAttribute('data-tip')) return node;
            node = node.parentNode;
        }
        return null;
    }

    function supportsHover() {
        if (window.matchMedia) {
            return window.matchMedia('(hover: hover)').matches;
        }
        return 'ontouchstart' in window === false;
    }

    function removeBackdrop() {
        if (backdrop && backdrop.parentNode) {
            backdrop.parentNode.removeChild(backdrop);
        }
        backdrop = null;
    }

    function addBackdrop() {
        if (backdrop) return;
        backdrop = document.createElement('div');
        backdrop.className = 'tcg-itemtip-backdrop';
        backdrop.addEventListener('click', hide);
        document.body.appendChild(backdrop);
    }

    function onMouseOver(e) {
        if (!supportsHover()) return;
        var el = findTrigger(e.target);
        if (!el) {
            if (trigger) scheduleHide();
            return;
        }
        if (el !== trigger) scheduleShow(el);
    }

    function onMouseOut(e) {
        if (!supportsHover()) return;
        var el = findTrigger(e.target);
        if (!el) return;
        var to = e.relatedTarget;
        if (to && findTrigger(to) === el) return;
        if (to && box && box.contains(to)) return;
        scheduleHide();
    }

    function onClick(e) {
        var el = findTrigger(e.target);
        if (!el) return;
        if (supportsHover()) return;
        if (box && box.style.display === 'block' && trigger === el) return;
        e.preventDefault();
        show(el);
        addBackdrop();
    }

    function onScroll() {
        if (trigger && box && box.style.display === 'block') place(trigger);
    }

    function onKey(e) {
        if (e.key === 'Escape' || e.keyCode === 27) hide();
    }

    function init() {
        document.addEventListener('mouseover', onMouseOver, false);
        document.addEventListener('mouseout', onMouseOut, false);
        document.addEventListener('click', onClick, true);
        window.addEventListener('scroll', onScroll, true);
        window.addEventListener('resize', hide, false);
        document.addEventListener('keydown', onKey, false);
    }

    if (document.addEventListener) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, false);
        } else {
            init();
        }
    }
})();
