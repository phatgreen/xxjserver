/*
 * Bảng thao tác vật phẩm Túi Đồ (game/bag*.php, .bag-page.bag-compact).
 * Mỗi vật phẩm chỉ hiện ô (ảnh + tên + phẩm); bấm ô => bảng nổi: thông tin (trang bị dùng lại nội dung
 * hộp xem nhanh js/itemtip.js) + các nút Trang bị / Phân giải / Dùng / Bán... lấy nguyên từ .bag-item-actions.
 */
(function () {
    var mask = null;

    function close() {
        if (mask && mask.parentNode) mask.parentNode.removeChild(mask);
        mask = null;
    }

    function open(card) {
        close();
        if (window.tcgItemTip) window.tcgItemTip.hide();
        var nameEl = card.querySelector('.bag-item-name');
        var actions = card.querySelector('.bag-item-actions');
        var info = (nameEl && nameEl.hasAttribute('data-tip') && window.tcgItemTip) ? window.tcgItemTip.html(nameEl) : '';

        var html = '<div class="bag-pop-card"><a class="bag-pop-x" href="#">&times;</a>';
        if (info) {
            html += '<div class="bag-pop-info">' + info + '</div>';
        } else {
            var icon = card.querySelector('.bag-item-icon');
            var main = card.querySelector('.bag-item-main');
            html += '<div class="bag-pop-head">' + (icon ? icon.outerHTML : '') + '<div class="bag-pop-main">'
                + (main ? main.innerHTML : '') + '</div></div>';
            if (nameEl && nameEl.getAttribute('href')) {
                html += '<a class="bag-pop-more" href="' + nameEl.getAttribute('href') + '">Xem chi tiết →</a>';
            }
        }
        if (card.classList.contains('equipped')) {
            html += '<div class="bag-pop-note">Đang trang bị — tháo ở trang Trang Bị</div>';
        } else if (actions && actions.innerHTML.replace(/<!--[\s\S]*?-->/g, '').trim() !== '') {
            html += '<div class="bag-pop-actions">' + actions.innerHTML + '</div>';
        }
        html += '</div>';

        mask = document.createElement('div');
        mask.className = 'bag-pop-mask';
        mask.innerHTML = html;
        mask.addEventListener('click', function (e) {
            if (e.target === mask || (e.target.classList && e.target.classList.contains('bag-pop-x'))) {
                e.preventDefault();
                close();
            }
        });
        document.body.appendChild(mask);
    }

    document.addEventListener('click', function (e) {
        var t = e.target;
        if (!t.closest || t.closest('.bag-pop-mask')) return;
        var card = t.closest('.bag-compact .bag-item-card');
        if (!card) return;
        e.preventDefault();
        open(card);
    }, false);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) close();
    }, false);
})();
