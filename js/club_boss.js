/**
 * Boss Tông Môn - Combat JS
 * AJAX attack, auto-refresh HP/ranking, damage float effects
 */
(function() {
    'use strict';

    var attacking = false;
    var refreshTimer = null;
    var countdownTimer = null;
    var bossDead = false;

    /* === INIT === */
    document.addEventListener('DOMContentLoaded', function() {
        if (CB_CONFIG.attack_enabled && !bossDead) {
            startAutoRefresh();
        }
        if (!CB_CONFIG.is_open && !bossDead) {
            startCountdown();
        }
    });

    /* === ATTACK === */
    window.cbAttack = function() {
        if (attacking || bossDead || !CB_CONFIG.attack_enabled) return;

        var btn = document.getElementById('cb-attack-btn');
        if (!btn || btn.classList.contains('disabled')) return;

        attacking = true;
        btn.style.opacity = '0.5';
        btn.style.pointerEvents = 'none';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '?cmd=' + getCmdParam(), true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-Game-Ajax', '1');

        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) return;
            attacking = false;

            if (xhr.status === 200) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    handleAttackResult(res);
                } catch(e) {
                    console.error('Parse error:', e);
                }
            }

            setTimeout(function() {
                if (btn) {
                    btn.style.opacity = '1';
                    btn.style.pointerEvents = 'auto';
                }
            }, CB_CONFIG.cooldown * 1000);
        };

        xhr.send('attack=1&sid=' + getSidParam());
    };

    function handleAttackResult(res) {
        if (!res.ok) {
            showDodgeFloat(res.msg || 'Lỗi tấn công!');
            return;
        }

        /* Shake boss image */
        var img = document.getElementById('cb-boss-img');
        if (img) {
            img.classList.remove('shake');
            void img.offsetWidth;
            img.classList.add('shake');
        }

        /* Show damage float */
        if (res.is_dodge) {
            showDodgeFloat('Né Tránh!');
        } else if (res.damage > 0) {
            var cls = res.is_crit ? 'crit' : 'normal';
            showDamageFloat(res.damage, cls);
            if (res.pet_damage > 0) {
                setTimeout(function() {
                    showDamageFloat(res.pet_damage, 'normal', true);
                }, 200);
            }
        }

        /* Build battle log */
        buildBattleLog(res);

        /* Update HP bar */
        updateHpBar(res.boss_hp, res.boss_maxhp);

        /* Update ranking */
        if (res.rank_html) {
            document.getElementById('cb-tab-rank').innerHTML = res.rank_html;
        }

        /* Boss killed */
        if (res.boss_killed) {
            bossDead = true;
            CB_CONFIG.attack_enabled = false;
            stopAutoRefresh();

            if (img) img.classList.add('dead');

            var btn = document.getElementById('cb-attack-btn');
            if (btn) {
                btn.className = 'cb-attack-btn disabled';
                btn.innerHTML = '✓ Boss Đã Chết';
                btn.onclick = null;
            }

            var sub = document.getElementById('cb-attack-sub');
            if (sub) sub.innerHTML = 'Chúc mừng toàn tông môn!';

            /* Show rewards */
            if (res.rewards && res.rewards.length > 0) {
                var rewardHtml = '<div class="cb-reward-card"><div class="cb-reward-title">🎁 Chiến Lợi Phẩm</div>';
                for (var i = 0; i < res.rewards.length; i++) {
                    rewardHtml += '<div class="cb-reward-item">' + res.rewards[i] + '</div>';
                }
                rewardHtml += '</div>';

                var logPanel = document.getElementById('cb-tab-log');
                if (logPanel) {
                    logPanel.innerHTML = rewardHtml + logPanel.innerHTML;
                }
            }

            var badge = document.getElementById('cb-time-badge');
            if (badge) {
                badge.className = 'cb-time-badge closed';
                badge.innerHTML = 'ĐÃ KẾT THÚC';
            }
        }
    }

    /* === HP BAR UPDATE === */
    function updateHpBar(hp, maxHp) {
        var pct = maxHp > 0 ? Math.min(100, Math.round(hp * 100 / maxHp)) : 0;
        var fill = document.getElementById('cb-hp-fill');
        var text = document.getElementById('cb-hp-text');
        var label = document.getElementById('cb-hp-label');

        if (fill) {
            fill.style.width = pct + '%';
            fill.className = 'cb-hp-fill ' + (pct > 60 ? 'high' : pct > 30 ? 'mid' : pct > 10 ? 'low' : 'critical');
        }
        if (text) text.innerHTML = formatNumber(hp) + ' / ' + formatNumber(maxHp);
        if (label) label.innerHTML = 'HP còn lại: ' + pct + '%';
    }

    /* === DAMAGE FLOAT EFFECTS === */
    function showDamageFloat(dmg, cls, isPet) {
        var el = document.createElement('div');
        el.className = 'cb-dmg-float ' + cls;
        el.innerHTML = (isPet ? '🐾 ' : '-') + formatNumber(dmg);

        var img = document.getElementById('cb-boss-img');
        if (img) {
            var rect = img.getBoundingClientRect();
            el.style.left = (rect.left + rect.width / 2 + (Math.random() * 60 - 30)) + 'px';
            el.style.top = (rect.top + 20 + (Math.random() * 20 - 10)) + 'px';
        } else {
            el.style.left = '50%';
            el.style.top = '30%';
        }

        document.body.appendChild(el);
        setTimeout(function() {
            if (el.parentNode) el.parentNode.removeChild(el);
        }, 1200);
    }

    function showDodgeFloat(msg) {
        var el = document.createElement('div');
        el.className = 'cb-dmg-float dodge';
        el.innerHTML = msg;
        el.style.left = '50%';
        el.style.top = '30%';
        document.body.appendChild(el);
        setTimeout(function() {
            if (el.parentNode) el.parentNode.removeChild(el);
        }, 1200);
    }

    /* === AUTO REFRESH === */
    function startAutoRefresh() {
        if (refreshTimer) clearInterval(refreshTimer);
        refreshTimer = setInterval(refreshStatus, CB_CONFIG.refresh_interval);
    }

    function stopAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    }

    function refreshStatus() {
        if (bossDead) return;

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '?cmd=' + getCmdParam() + '&status=1', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-Game-Ajax', '1');

        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) return;
            if (xhr.status === 200) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.ok) {
                        updateHpBar(res.boss_hp, res.boss_maxhp);

                        if (res.rank_html) {
                            document.getElementById('cb-tab-rank').innerHTML = res.rank_html || '<div class="cb-empty">Chưa có sát thương nào</div>';
                        }

                        /* Update server logs only — combat feed is separate */
                        if (res.logs && res.logs.length > 0) {
                            var logHtml = '';
                            for (var i = 0; i < res.logs.length; i++) {
                                var log = res.logs[i];
                                var cls = 'cb-log-row';
                                if (log.crit) cls += ' crit';
                                if (log.dodge) cls += ' dodge';
                                var badge = '';
                                if (log.crit) badge = ' <span class="cb-crit-badge">Bạo</span>';
                                if (log.dodge) badge = ' <span class="cb-dodge-badge">Né</span>';
                                logHtml += '<div class="' + cls + '"><span class="cb-log-name">' + log.name + '</span> gây <span class="cb-log-dmg">' + formatNumber(log.damage) + '</span>' + badge + '</div>';
                            }
                            var serverLog = document.getElementById('cb-server-log');
                            if (serverLog) serverLog.innerHTML = logHtml;
                        }

                        /* Boss killed by someone else */
                        if (res.status >= 2 && !bossDead) {
                            bossDead = true;
                            CB_CONFIG.attack_enabled = false;
                            stopAutoRefresh();

                            var img = document.getElementById('cb-boss-img');
                            if (img) img.classList.add('dead');

                            var btn = document.getElementById('cb-attack-btn');
                            if (btn) {
                                btn.className = 'cb-attack-btn disabled';
                                btn.innerHTML = '✓ Boss Đã Chết';
                                btn.onclick = null;
                            }

                            var badge = document.getElementById('cb-time-badge');
                            if (badge) {
                                badge.className = 'cb-time-badge closed';
                                badge.innerHTML = 'ĐÃ KẾT THÚC';
                            }
                        }

                        /* Time check */
                        if (!res.is_open && res.time_left <= 0 && res.status < 2) {
                            bossDead = true;
                            stopAutoRefresh();

                            var btn2 = document.getElementById('cb-attack-btn');
                            if (btn2) {
                                btn2.className = 'cb-attack-btn disabled';
                                btn2.innerHTML = '⏰ Hết Giờ';
                                btn2.onclick = null;
                            }

                            var badge2 = document.getElementById('cb-time-badge');
                            if (badge2) {
                                badge2.className = 'cb-time-badge closed';
                                badge2.innerHTML = 'HẾT GIỜ';
                            }
                        }
                    }
                } catch(e) {}
            }
        };
        xhr.send();
    }

    /* === COUNTDOWN === */
    function startCountdown() {
        if (countdownTimer) clearInterval(countdownTimer);
        countdownTimer = setInterval(function() {
            if (CB_CONFIG.time_left > 0) {
                CB_CONFIG.time_left--;
            }
            CB_CONFIG.is_open = true;
            CB_CONFIG.attack_enabled = true;

            var btn = document.getElementById('cb-attack-btn');
            if (btn) {
                btn.className = 'cb-attack-btn ready';
                btn.innerHTML = '⚔ Tấn Công';
                btn.onclick = function() { window.cbAttack(); };
            }

            var badge = document.getElementById('cb-time-badge');
            if (badge) {
                badge.className = 'cb-time-badge open';
                badge.innerHTML = 'ĐANG MỞ CỬA';
            }

            var sub = document.getElementById('cb-attack-sub');
            if (sub) sub.innerHTML = '';

            startAutoRefresh();
            clearInterval(countdownTimer);
        }, CB_CONFIG.time_left * 1000);
    }

    /* === TAB SWITCH === */
    window.cbSwitchTab = function(tab) {
        var tabs = document.querySelectorAll('.cb-tab');
        var panels = document.querySelectorAll('.cb-tab-panel');
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].classList.remove('active');
        }
        for (var j = 0; j < panels.length; j++) {
            panels[j].classList.remove('active');
        }
        document.getElementById('cb-tab-' + tab).classList.add('active');
        tabs[tab === 'log' ? 0 : 1].classList.add('active');
    };

    /* === BATTLE LOG — RPG STYLE === */
    function buildBattleLog(res) {
        var feed = document.getElementById('cb-combat-feed');
        if (!feed) return;

        var entry = document.createElement('div');
        entry.className = 'cb-battle-entry';

        var lines = '';
        var pName = res.player_name || 'Bạn';
        var bName = res.boss_name || 'Boss';
        var newHp = res.boss_hp;
        var maxHp = res.boss_maxhp;
        var hpPct = maxHp > 0 ? Math.min(100, Math.round(newHp * 100 / maxHp)) : 0;

        /* Dòng 1: Player tấn công boss */
        if (res.is_dodge) {
            lines += '<div class="cb-line cb-line-player"><span class="cb-line-icon">⚔</span> <b>' + pName + '</b> tấn công <b>' + bName + '</b> — <em>Né Tránh!</em></div>';
        } else {
            var critBadge = res.is_crit ? ' <span class="cb-crit-badge">Bạo Kích!</span>' : '';
            var moveTxt = res.move_name ? ' thi triển <b>' + res.move_name + '</b>' : ' tấn công <b>' + bName + '</b>';
            lines += '<div class="cb-line cb-line-player"><span class="cb-line-icon">⚔</span> <b>' + pName + '</b>' + moveTxt + ' gây <b class="cb-dmg-red">' + formatNumber(res.damage) + '</b> sát thương' + critBadge + '</div>';
            if (res.move_text) {
                lines += '<div class="cb-line cb-line-detail"><span class="cb-line-arrow">↳</span> <i>' + res.move_text + '</i></div>';
            }
            lines += '<div class="cb-line cb-line-detail"><span class="cb-line-arrow">↳</span> <b>' + bName + '</b> mất ' + formatNumber(res.damage) + ' HP → còn <b>' + formatNumber(newHp) + '</b>/' + formatNumber(maxHp) + ' (' + hpPct + '%)</div>';
        }

        /* Dòng 2: Thú cưng tấn công (nếu có) */
        if (res.pet_damage > 0) {
            lines += '<div class="cb-line cb-line-pet"><span class="cb-line-icon">🐾</span> <b>Thú Cưng</b> tấn công <b>' + bName + '</b> gây <b class="cb-dmg-purple">' + formatNumber(res.pet_damage) + '</b> sát thương</div>';
            lines += '<div class="cb-line cb-line-detail"><span class="cb-line-arrow">↳</span> <b>' + bName + '</b> mất thêm ' + formatNumber(res.pet_damage) + ' HP</div>';
        }

        /* Dòng 3: Boss phản đòn */
        if (res.boss_is_dodge) {
            lines += '<div class="cb-line cb-line-boss"><span class="cb-line-icon">🔥</span> <b>' + bName + '</b> phản đòn — <em>Bị bạn né tránh!</em></div>';
        } else {
            var bossCritBadge = res.boss_is_crit ? ' <span class="cb-crit-badge">Bạo!</span>' : '';
            lines += '<div class="cb-line cb-line-boss"><span class="cb-line-icon">🔥</span> <b>' + bName + '</b> tấn công bạn gây <b class="cb-dmg-orange">' + formatNumber(res.boss_damage) + '</b> sát thương' + bossCritBadge + '</div>';
        }

        /* Dòng 4: Hút máu (nếu có) */
        if (res.heal > 0) {
            lines += '<div class="cb-line cb-line-heal"><span class="cb-line-icon">💚</span> Hút máu hồi phục <b class="cb-dmg-green">' + formatNumber(res.heal) + '</b> HP</div>';
        }

        entry.innerHTML = lines;
        feed.insertBefore(entry, feed.firstChild);

        /* Limit to 20 entries */
        while (feed.children.length > 20) {
            feed.removeChild(feed.lastChild);
        }
    }

    /* === HELPERS === */
    function getCmdParam() {
        var url = window.location.search;
        var match = url.match(/cmd=([^&]+)/);
        return match ? match[1] : '';
    }

    function getSidParam() {
        var url = window.location.search;
        var match = url.match(/sid=([^&]+)/);
        return match ? match[1] : '';
    }

    function formatNumber(n) {
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

})();
