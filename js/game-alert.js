/**
 * GameAlert - Notification module for Tu Chan Gioi
 * Dark gaming theme matching the game's visual style.
 *
 * API:
 *   GameAlert.show(msg, type, duration)
 *   GameAlert.success(msg)
 *   GameAlert.error(msg)
 *   GameAlert.info(msg)
 *   GameAlert.warning(msg)
 *   GameAlert.ajax(url, opts)
 *   GameAlert.confirm(msg, onYes, onNo)
 *   GameAlert.action(url)
 *   GameAlert.form(f)
 */
var GameAlert = (function() {
    var _overlay = null;
    var _box = null;

    function _remove() {
        if (_overlay) { _overlay.parentNode.removeChild(_overlay); _overlay = null; }
        if (_box) { _box.parentNode.removeChild(_box); _box = null; }
    }

    function _icon(type) {
        switch (type) {
            case 'success': return '\u2713';
            case 'error':   return '\u2717';
            case 'warning': return '\u26A0';
            default:        return '\u2139';
        }
    }

    function _label(type) {
        switch (type) {
            case 'success': return 'Th\u00e0nh c\u00f4ng';
            case 'error':   return 'L\u1ed7i';
            case 'warning': return 'C\u1ea3nh b\u00e1o';
            default:        return 'Th\u00f4ng b\u00e1o';
        }
    }

    function show(msg, type, duration) {
        _remove();
        type = type || 'info';
        duration = duration || 0;

        _overlay = document.createElement('div');
        _overlay.className = 'ga-overlay';
        _overlay.onclick = _remove;

        _box = document.createElement('div');
        _box.className = 'ga-box ga-' + type;

        var ico = _icon(type);
        var lbl = _label(type);

        _box.innerHTML =
            '<div class="ga-icon-wrap">' +
                '<div class="ga-icon">' + ico + '</div>' +
            '</div>' +
            '<div class="ga-label">' + lbl + '</div>' +
            '<div class="ga-msg">' + msg + '</div>' +
            '<button class="ga-btn">X\u00e1c nh\u1eadn</button>';

        _box.querySelector('.ga-btn').onclick = _remove;

        document.body.appendChild(_overlay);
        document.body.appendChild(_box);

        _overlay.offsetHeight;
        _box.offsetHeight;

        if (duration > 0) {
            setTimeout(_remove, duration);
        }
    }

    function success(msg) { show(msg, 'success'); }
    function error(msg)   { show(msg, 'error'); }
    function info(msg)    { show(msg, 'info'); }
    function warning(msg) { show(msg, 'warning'); }

    function ajax(url, opts) {
        opts = opts || {};
        var method = opts.method || 'GET';
        var body = opts.body || null;
        var onSuccess = opts.onSuccess || null;
        var onError = opts.onError || null;
        var reload = opts.reload !== false;
        var reloadDelay = opts.reloadDelay || 1200;

        var xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-Game-Ajax', '1');

        if (method === 'POST' && body && !(body instanceof FormData)) {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }

        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) return;
            try {
                var d = JSON.parse(xhr.responseText);
                show(d.msg, d.ok ? 'success' : 'error');
                if (d.ok) {
                    if (onSuccess) onSuccess(d);
                    if (reload) setTimeout(function() { location.reload(); }, reloadDelay);
                } else {
                    if (onError) onError(d);
                }
            } catch (e) {
                show('L\u1ed7i k\u1ebft n\u1ed1i', 'error');
                if (onError) onError(e);
            }
        };

        xhr.send(body);
        return false;
    }

    function confirm(msg, onYes, onNo) {
        _remove();

        _overlay = document.createElement('div');
        _overlay.className = 'ga-overlay';

        _box = document.createElement('div');
        _box.className = 'ga-box ga-confirm';

        _box.innerHTML =
            '<div class="ga-icon-wrap">' +
                '<div class="ga-icon ga-icon-confirm">?</div>' +
            '</div>' +
            '<div class="ga-label">X\u00e1c nh\u1eadn</div>' +
            '<div class="ga-msg">' + msg + '</div>' +
            '<div class="ga-btns">' +
                '<button class="ga-btn ga-btn-cancel">H\u1ee7y</button>' +
                '<button class="ga-btn ga-btn-ok">X\u00e1c nh\u1eadn</button>' +
            '</div>';

        _box.querySelector('.ga-btn-cancel').onclick = function() {
            _remove();
            if (onNo) onNo();
        };

        _box.querySelector('.ga-btn-ok').onclick = function() {
            _remove();
            if (onYes) onYes();
        };

        _overlay.onclick = function() {
            _remove();
            if (onNo) onNo();
        };

        document.body.appendChild(_overlay);
        document.body.appendChild(_box);
    }

    function _addAjaxParam(url) {
        if (url.indexOf('ajax=') !== -1) return url;
        return url + (url.indexOf('?') !== -1 ? '&' : '?') + 'ajax=1';
    }

    function action(url, method, body) {
        url = _addAjaxParam(url);
        ajax(url, { method: method || 'GET', body: body || null });
        return false;
    }

    function form(f) {
        var fd = new FormData(f);
        ajax(_addAjaxParam(f.action), { method: 'POST', body: fd });
        return false;
    }

    return {
        show: show,
        success: success,
        error: error,
        info: info,
        warning: warning,
        ajax: ajax,
        confirm: confirm,
        action: action,
        form: form,
        hide: _remove
    };
})();

(function() {
    function convertNotice(el) {
        var text = (el.textContent || el.innerText || '').trim();
        if (!text) return;
        var isOk = el.classList.contains('success');
        GameAlert.show(text, isOk ? 'success' : 'error');
        el.parentNode.removeChild(el);
    }

    function scan() {
        var els = document.querySelectorAll('.tcg-club-notice');
        for (var i = 0; i < els.length; i++) convertNotice(els[i]);
    }

    function startObs() {
        if (typeof MutationObserver !== 'undefined' && document.body) {
            var obs = new MutationObserver(function(mutations) {
                for (var i = 0; i < mutations.length; i++) {
                    var added = mutations[i].addedNodes;
                    for (var j = 0; j < added.length; j++) {
                        var node = added[j];
                        if (node.nodeType !== 1) continue;
                        if (node.classList && node.classList.contains('tcg-club-notice')) {
                            convertNotice(node);
                        }
                        var inner = node.querySelectorAll ? node.querySelectorAll('.tcg-club-notice') : [];
                        for (var k = 0; k < inner.length; k++) convertNotice(inner[k]);
                    }
                }
            });
            obs.observe(document.body, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { scan(); startObs(); });
    } else {
        scan();
        startObs();
    }
})();
