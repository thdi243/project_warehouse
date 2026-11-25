/*!
 * Edu Ticker — Motion edukasi alur panggilan supir untuk TV Display Antrian PT BAS.
 * Dibangun dengan GSAP Timeline + SVG inline (tanpa file video, ringan untuk Smart TV).
 *
 * Pemakaian:
 *   EduTicker.init({ areaLabel: 'WFG', onNotif: playChime });
 *   EduTicker.evaluate(data.calling);   // panggil setiap selesai fetch data antrian (smart trigger)
 *   EduTicker.play();                   // putar manual
 *
 * Parameter URL (untuk uji coba):
 *   ?edu=off        → nonaktifkan ticker
 *   ?edu_test=1     → langsung putar 1.5 detik setelah halaman dibuka
 *   ?edu_seek=8.5   → bekukan animasi di detik ke-8.5 (debug visual)
 */
(function (global) {
    'use strict';

    // ------------------------------------------------------------------
    // Konstanta layout (satuan = viewBox 1600 x 220)
    // ------------------------------------------------------------------
    var STATIONS = [
        { x: 190,  color: '#38bdf8', title: 'Daftar di Timbangan', sub: 'Pastikan nomor WhatsApp aktif' },
        { x: 495,  color: '#fb923c', title: 'Serahkan Dokumen',    sub: 'Serahkan ke Admin & ambil antrian' },
        { x: 800,  color: '#f59e0b', title: 'Tunggu di Parkir',    sub: 'Standby & pantau antrian / HP' },
        { x: 1105, color: '#22c55e', title: 'Notif WA Masuk!',     sub: 'Panggilan giliran ke dock' },
        { x: 1410, color: '#a78bfa', title: 'Balas & Merapat',     sub: 'Segera menuju dock tujuan' }
    ];
    var TRUCK_LEN = 116;   // panjang truk
    var TRUCK_Y = 87;      // posisi Y truk (roda tepat di atas jalan)
    var TRUCK_START = -180;
    var ROAD_X = 60;
    var ROAD_W = 1480;

    var DEFAULTS = {
        mountTo: null,           // elemen induk (default: document.body)
        top: null,               // offset px dari atas layar (default CSS: 38px, di bawah navbar)
        intervalMinutes: 10,     // putar rutin setiap X menit (0 = nonaktif)
        smartDelayMinutes: 5,    // truk sudah dipanggil >= X menit tapi belum merapat → putar
        cooldownMinutes: 3,      // jeda minimal antar pemutaran
        maxPlaysPerTruck: 3,     // batas pemutaran smart trigger untuk truk yang sama
        areaLabel: 'WFG',        // label dock default
        onNotif: null,           // callback saat scene notifikasi WA (mis. bunyikan chime)
        onStart: null,
        onEnd: null
    };

    var cfg = Object.assign({}, DEFAULTS);
    var root = null, el = {}, tl = null, wheelSpin = null, schedulerId = null;
    var state = { playing: false, lastPlayAt: 0, lastIntervalAt: Date.now(), plays: {} };

    // ------------------------------------------------------------------
    // Markup SVG
    // ------------------------------------------------------------------
    function wheel(cx) {
        return '<g class="edu-wheel">' +
            '<circle cx="' + cx + '" cy="50" r="9" fill="#0f172a" stroke="#94a3b8" stroke-width="3"/>' +
            '<line x1="' + cx + '" y1="43" x2="' + cx + '" y2="57" stroke="#94a3b8" stroke-width="2"/>' +
            '<line x1="' + (cx - 7) + '" y1="50" x2="' + (cx + 7) + '" y2="50" stroke="#94a3b8" stroke-width="2"/>' +
            '</g>';
    }

    function stationIcon(i, c) {
        switch (i) {
            case 0: // Kartu registrasi
                return '<rect x="-18" y="56" width="36" height="27" rx="4" fill="none" stroke="' + c + '" stroke-width="3"/>' +
                    '<circle cx="-8" cy="65" r="4.5" fill="' + c + '"/>' +
                    '<path d="M-14 78 q6 -8 12 0" fill="none" stroke="' + c + '" stroke-width="2.5"/>' +
                    '<line x1="2" y1="64" x2="12" y2="64" stroke="' + c + '" stroke-width="3" stroke-linecap="round"/>' +
                    '<line x1="2" y1="72" x2="12" y2="72" stroke="' + c + '" stroke-width="3" stroke-linecap="round"/>';
            case 1: // Serahkan dokumen ke Admin
                return '<rect x="-15" y="52" width="30" height="36" rx="4" fill="#0b1220" stroke="' + c + '" stroke-width="3"/>' +
                    '<path d="M-7 52 v-3 a3 3 0 0 1 3 -3 h8 a3 3 0 0 1 3 3 v3" fill="none" stroke="' + c + '" stroke-width="2.5"/>' +
                    '<line x1="-8" y1="63" x2="8" y2="63" stroke="' + c + '" stroke-width="2.5" stroke-linecap="round"/>' +
                    '<line x1="-8" y1="71" x2="8" y2="71" stroke="' + c + '" stroke-width="2.5" stroke-linecap="round"/>' +
                    '<line x1="-8" y1="79" x2="2" y2="79" stroke="' + c + '" stroke-width="2.5" stroke-linecap="round"/>';
            case 2: // Rambu parkir
                return '<rect x="-16" y="54" width="32" height="32" rx="7" fill="' + c + '"/>' +
                    '<text x="0" y="79" text-anchor="middle" class="edu-t-p">P</text>';
            case 3: // Handphone + badge notif
                return '<g class="edu-phone">' +
                    '<rect x="-12" y="49" width="24" height="42" rx="5" fill="#0b1220" stroke="' + c + '" stroke-width="3"/>' +
                    '<rect x="-8" y="55" width="16" height="27" rx="2" fill="' + c + '" opacity="0.3"/>' +
                    '<circle cx="0" cy="86" r="1.8" fill="' + c + '"/>' +
                    '<g class="edu-notif-dot">' +
                    '<circle cx="12" cy="51" r="7.5" fill="#ef4444" stroke="#0b1220" stroke-width="2"/>' +
                    '<text x="12" y="54.5" text-anchor="middle" style="font-size:9.5px;font-weight:900;fill:#fff">1</text>' +
                    '</g></g>';
            default: // Gudang / dock + pintu rolling
                return '<path d="M-21 63 L0 50 L21 63 Z" fill="' + c + '"/>' +
                    '<rect x="-18" y="63" width="36" height="25" fill="#0b1220" stroke="' + c + '" stroke-width="3"/>' +
                    '<rect class="edu-door" x="-9" y="70" width="18" height="18" fill="' + c + '"/>' +
                    '<g class="edu-check">' +
                    '<circle cx="18" cy="52" r="10" fill="#22c55e" stroke="#0b1220" stroke-width="2"/>' +
                    '<path d="M13 52 l3.5 3.5 l6.5 -7" fill="none" stroke="#fff" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>' +
                    '</g>';
        }
    }

    function stationsMarkup() {
        return STATIONS.map(function (s, i) {
            var waves = i === 3
                ? '<circle class="edu-wave" cx="0" cy="70" r="34" fill="none" stroke="' + s.color + '" stroke-width="3" opacity="0"/>' +
                  '<circle class="edu-wave" cx="0" cy="70" r="34" fill="none" stroke="' + s.color + '" stroke-width="3" opacity="0"/>'
                : '';
            return '<g class="edu-station" transform="translate(' + s.x + ',4)">' +
                '<line x1="0" y1="106" x2="0" y2="140" stroke="#334155" stroke-width="2" stroke-dasharray="4 4"/>' +
                waves +
                '<circle class="edu-ring" cx="0" cy="70" r="34" fill="none" stroke="' + s.color + '" stroke-width="2.5" opacity="0"/>' +
                '<g class="edu-badge">' +
                '<circle cx="0" cy="70" r="34" fill="#0f172a" stroke="' + s.color + '" stroke-width="3"/>' +
                stationIcon(i, s.color) +
                '</g>' +
                '<circle cx="-27" cy="44" r="11" fill="' + s.color + '"/>' +
                '<text x="-27" y="48" text-anchor="middle" class="edu-t-num">' + (i + 1) + '</text>' +
                '<text x="0" y="180" text-anchor="middle" class="edu-t-title">' + s.title + '</text>' +
                '<text x="0" y="201" text-anchor="middle" class="edu-t-sub">' + s.sub.replace('&', '&amp;') + '</text>' +
                '</g>';
        }).join('');
    }

    function buildMarkup() {
        return '' +
            '<defs>' +
            '<linearGradient id="eduBg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0b1220"/><stop offset="1" stop-color="#13284f"/></linearGradient>' +
            '<linearGradient id="eduProgress" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#38bdf8"/><stop offset="0.5" stop-color="#22c55e"/><stop offset="1" stop-color="#a78bfa"/></linearGradient>' +
            '<linearGradient id="eduCta" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#b91c1c"/><stop offset="1" stop-color="#ea580c"/></linearGradient>' +
            '<clipPath id="eduClip"><rect x="0" y="0" width="1600" height="220"/></clipPath>' +
            '</defs>' +
            '<g clip-path="url(#eduClip)">' +

            // Background
            '<rect width="1600" height="220" fill="url(#eduBg)"/>' +
            '<circle cx="1500" cy="-40" r="180" fill="#3b82f6" opacity="0.07"/>' +
            '<circle cx="80" cy="260" r="160" fill="#22c55e" opacity="0.05"/>' +

            // Chip judul + indikator langkah
            '<rect x="20" y="8" width="332" height="26" rx="13" fill="#172554" stroke="#3b82f6" stroke-opacity="0.6"/>' +
            '<circle class="edu-live" cx="38" cy="21" r="5" fill="#22c55e"/>' +
            '<text x="52" y="26" class="edu-t-chip">PANDUAN SUPIR • ALUR PANGGILAN DOCK</text>' +
            '<text x="1580" y="27" text-anchor="end" class="edu-t-step" id="edu-step">LANGKAH 1 / 5</text>' +

            // Jalan + progress
            '<rect x="' + ROAD_X + '" y="146" width="' + ROAD_W + '" height="10" rx="5" fill="#1e293b"/>' +
            '<line x1="' + ROAD_X + '" y1="151" x2="' + (ROAD_X + ROAD_W) + '" y2="151" stroke="#475569" stroke-width="2" stroke-dasharray="14 12" opacity="0.7"/>' +
            '<rect id="edu-progress" x="' + ROAD_X + '" y="146" width="0" height="10" rx="5" fill="url(#eduProgress)"/>' +

            // Stasiun 1-4
            stationsMarkup() +

            // Truk
            '<g id="edu-truck">' +
            '<g class="edu-truck-body">' +
            '<rect x="0" y="0" width="84" height="42" rx="4" fill="#f8fafc" stroke="#cbd5e1" stroke-width="1.5"/>' +
            '<rect x="6" y="6" width="72" height="9" rx="2" fill="#dc2626"/>' +
            '<text x="42" y="33" text-anchor="middle" class="edu-t-truck">PT BAS</text>' +
            '<path d="M86 10 H104 L116 25 V42 H86 Z" fill="#2563eb"/>' +
            '<path d="M90 14 H102 L110 25 H90 Z" fill="#bfdbfe"/>' +
            '<circle cx="114" cy="34" r="2.6" fill="#fde047"/>' +
            '<rect x="-2" y="41" width="120" height="6" rx="2" fill="#334155"/>' +
            '</g>' +
            wheel(20) + wheel(62) + wheel(100) +
            '</g>' +

            // Panel chat WhatsApp
            '<g transform="translate(1148,14)"><g id="edu-chat">' +
            '<rect x="0" y="0" width="280" height="118" rx="12" fill="#0b141a" stroke="#22c55e" stroke-opacity="0.6" stroke-width="1.5"/>' +
            '<rect x="0" y="0" width="280" height="30" rx="12" fill="#202c33"/>' +
            '<rect x="0" y="18" width="280" height="12" fill="#202c33"/>' +
            '<circle cx="16" cy="15" r="9.5" fill="#22c55e"/>' +
            '<text x="16" y="18.5" text-anchor="middle" style="font-size:8.5px;font-weight:900;fill:#0b141a">BAS</text>' +
            '<text x="32" y="14" class="edu-t-wa-name">PT BAS Warehouse</text>' +
            '<text x="32" y="25" class="edu-t-wa-status">WhatsApp • online</text>' +
            '<g id="edu-msg-in">' +
            '<rect x="10" y="38" width="228" height="40" rx="8" fill="#202c33"/>' +
            '<text x="20" y="54" class="edu-t-wa-strong" id="edu-msg-title">Truk Anda — Giliran Anda!</text>' +
            '<text x="20" y="70" class="edu-t-wa-body" id="edu-msg-body">Segera menuju Dock WFG.</text>' +
            '</g>' +
            '<g id="edu-typing">' +
            '<rect x="216" y="86" width="54" height="24" rx="8" fill="#005c4b"/>' +
            '<circle class="edu-dot" cx="232" cy="98" r="3" fill="#d1fae5"/>' +
            '<circle class="edu-dot" cx="243" cy="98" r="3" fill="#d1fae5"/>' +
            '<circle class="edu-dot" cx="254" cy="98" r="3" fill="#d1fae5"/>' +
            '</g>' +
            '<g id="edu-msg-out">' +
            '<rect x="118" y="86" width="152" height="24" rx="8" fill="#005c4b"/>' +
            '<text x="130" y="102" class="edu-t-wa-reply">Siap, meluncur!</text>' +
            '<path d="M242 98 l3 3 l5 -6 M248 98 l3 3 l5 -6" fill="none" stroke="#53bdeb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>' +
            '</g>' +
            '</g></g>' +

            // CTA penutup
            '<g id="edu-cta">' +
            '<rect x="0" y="0" width="1600" height="220" fill="url(#eduCta)"/>' +
            '<circle cx="1490" cy="30" r="150" fill="#ffffff" opacity="0.06"/>' +
            '<circle cx="1330" cy="230" r="110" fill="#ffffff" opacity="0.05"/>' +
            '<g transform="translate(120,110)"><g id="edu-cta-phone">' +
            '<circle cx="0" cy="0" r="64" fill="#ffffff" opacity="0.16"/>' +
            '<rect x="-21" y="-36" width="42" height="72" rx="8" fill="#ffffff"/>' +
            '<rect x="-16" y="-27" width="32" height="48" rx="3" fill="#16a34a"/>' +
            '<path d="M-8 -6 h16 v10 h-9 l-4 4 v-4 h-3 z" fill="#ffffff"/>' +
            '<circle cx="0" cy="28" r="2.6" fill="#94a3b8"/>' +
            '<circle cx="20" cy="-34" r="11" fill="#facc15" stroke="#ffffff" stroke-width="2.5"/>' +
            '<text x="20" y="-29.5" text-anchor="middle" style="font-size:13px;font-weight:900;fill:#7c2d12">!</text>' +
            '</g></g>' +
            '<text x="222" y="88" class="edu-t-cta-title" id="edu-cta-title">CEK WHATSAPP ANDA SEKARANG!</text>' +
            '<text x="224" y="128" class="edu-t-cta-sub">Balas notifikasi panggilan &amp; segera merapat ke dock agar bongkar-muat lebih cepat.</text>' +
            '<text x="224" y="170" class="edu-t-cta-target" id="edu-cta-target"></text>' +
            '</g>' +
            '</g>'; // end clip group
    }

    // ------------------------------------------------------------------
    // Mount & referensi elemen
    // ------------------------------------------------------------------
    function mount() {
        root = document.createElement('div');
        root.className = 'edu-ticker';
        root.setAttribute('aria-hidden', 'true');
        if (cfg.top !== null && cfg.top !== undefined) {
            root.style.setProperty('--edu-top', cfg.top + 'px');
        }
        root.innerHTML = '<div class="edu-ticker__inner">' +
            '<svg class="edu-ticker__svg" viewBox="0 0 1600 220" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg">' +
            buildMarkup() +
            '</svg></div>';
        (cfg.mountTo || document.body).appendChild(root);

        var q = function (s) { return root.querySelector(s); };
        var qa = function (s) { return Array.prototype.slice.call(root.querySelectorAll(s)); };

        el.stations = qa('.edu-station');
        el.badges = el.stations.map(function (s) { return s.querySelector('.edu-badge'); });
        el.rings = el.stations.map(function (s) { return s.querySelector('.edu-ring'); });
        el.waves = qa('.edu-wave');
        el.wheels = qa('.edu-wheel');
        el.truck = q('#edu-truck');
        el.truckBody = q('.edu-truck-body');
        el.progress = q('#edu-progress');
        el.step = q('#edu-step');
        el.live = q('.edu-live');
        el.phone = q('.edu-phone');
        el.notifDot = q('.edu-notif-dot');
        el.door = q('.edu-door');
        el.check = q('.edu-check');
        el.chat = q('#edu-chat');
        el.msgIn = q('#edu-msg-in');
        el.msgTitle = q('#edu-msg-title');
        el.msgBody = q('#edu-msg-body');
        el.typing = q('#edu-typing');
        el.dots = qa('.edu-dot');
        el.msgOut = q('#edu-msg-out');
        el.cta = q('#edu-cta');
        el.ctaPhone = q('#edu-cta-phone');
        el.ctaTitle = q('#edu-cta-title');
        el.ctaTarget = q('#edu-cta-target');
    }

    // ------------------------------------------------------------------
    // Timeline GSAP
    // ------------------------------------------------------------------
    function setStep(i) {
        if (el.step) el.step.textContent = 'LANGKAH ' + (i + 1) + ' / ' + STATIONS.length;
    }

    function truckXFor(stationIdx) {
        // Truk berhenti tepat di kiri stasiun (tidak menutupi ikon)
        return STATIONS[stationIdx].x - TRUCK_LEN - 40;
    }

    function buildTimeline() {
        var gsap = global.gsap;
        if (tl) tl.kill();
        if (wheelSpin) wheelSpin.kill();

        wheelSpin = gsap.to(el.wheels, {
            rotation: '+=360', transformOrigin: '50% 50%',
            duration: 0.45, ease: 'none', repeat: -1, paused: true
        });
        gsap.to(el.live, { opacity: 0.25, duration: 0.6, yoyo: true, repeat: -1, ease: 'sine.inOut' });

        tl = gsap.timeline({ paused: true, defaults: { ease: 'power2.out' }, onComplete: finish });

        // --- Reset state (posisi 0) -----------------------------------
        tl.set(root, { display: 'block' })
            .set(el.stations, { opacity: 0.35 })
            .set(el.rings.concat(el.waves), { opacity: 0 })
            .set(el.truck, { x: TRUCK_START, y: TRUCK_Y })
            .set(el.truckBody, { y: 0 })
            .set(el.progress, { attr: { width: 0 } })
            .set(el.door, { scaleY: 1, transformOrigin: '50% 0%' })
            .set(el.check, { scale: 0, transformOrigin: '50% 50%' })
            .set(el.notifDot, { scale: 0, transformOrigin: '50% 50%' })
            .set(el.phone, { rotation: 0, transformOrigin: '50% 50%' })
            .set(el.chat, { scale: 0, autoAlpha: 0, transformOrigin: '0% 50%' })
            .set(el.msgIn, { autoAlpha: 0, y: 8 })
            .set(el.typing, { autoAlpha: 0 })
            .set(el.dots, { y: 0 })
            .set(el.msgOut, { autoAlpha: 0, scale: 0.7, transformOrigin: '100% 50%' })
            .set(el.cta, { x: 1600 })
            .set(el.ctaPhone, { rotation: 0, transformOrigin: '50% 50%' })
            .set(el.ctaTitle, { scale: 1, transformOrigin: '0% 50%' })
            .set(el.step, { textContent: 'LANGKAH 1 / ' + STATIONS.length });

        // Ticker turun
        tl.fromTo(root, { yPercent: -120, autoAlpha: 0 }, { yPercent: 0, autoAlpha: 1, duration: 0.55 });

        function drive(stationIdx, dur) {
            var x = truckXFor(stationIdx);
            var legs = Math.max(2, Math.round(dur / 0.09));
            if (legs % 2 !== 0) legs++; // genap → truk kembali ke posisi semula
            tl.call(function () { wheelSpin.play(); })
                .to(el.truck, { x: x, duration: dur, ease: 'power1.inOut' })
                .to(el.progress, { attr: { width: Math.max(0, x + TRUCK_LEN - ROAD_X) }, duration: dur, ease: 'power1.inOut' }, '<')
                .to(el.truckBody, { y: -1.6, duration: dur / legs, yoyo: true, repeat: legs - 1, ease: 'sine.inOut' }, '<')
                .call(function () { wheelSpin.pause(); });
        }

        function activate(i) {
            tl.set(el.step, { textContent: 'LANGKAH ' + (i + 1) + ' / ' + STATIONS.length })
                .to(el.stations[i], { opacity: 1, duration: 0.3 })
                .fromTo(el.badges[i], { scale: 0.82 }, { scale: 1, duration: 0.5, ease: 'back.out(3)', transformOrigin: '50% 50%' }, '<')
                .fromTo(el.rings[i], { scale: 1, opacity: 0.9 }, {
                    scale: 1.65, opacity: 0, duration: 0.8, ease: 'power1.out',
                    transformOrigin: '50% 50%', immediateRender: false
                }, '<');
        }

        function hold(sec) { tl.to({}, { duration: sec }); }

        // --- Scene 1: Daftar di timbangan -----------------------------
        drive(0, 1.2);
        activate(0);
        hold(0.8);

        // --- Scene 2: Serahkan dokumen ke Admin -----------------------
        drive(1, 1.2);
        activate(1);
        hold(0.8);

        // --- Scene 3: Tunggu di parkir --------------------------------
        drive(2, 1.2);
        activate(2);
        hold(0.7);

        // --- Scene 4: Notifikasi WhatsApp masuk ------------------------
        activate(3);
        tl.call(function () { if (typeof cfg.onNotif === 'function') { try { cfg.onNotif(); } catch (e) { /* abaikan */ } } })
            .to(el.notifDot, { scale: 1, duration: 0.3, ease: 'back.out(4)' }, '<')
            .fromTo(el.waves, { scale: 1, opacity: 0.9 }, {
                scale: 1.9, opacity: 0, duration: 0.85, stagger: 0.25, repeat: 1,
                ease: 'power1.out', transformOrigin: '50% 50%', immediateRender: false
            }, '<')
            .fromTo(el.phone, { rotation: -10 }, {
                rotation: 10, duration: 0.06, yoyo: true, repeat: 11, ease: 'none',
                transformOrigin: '50% 50%', immediateRender: false
            }, '<')
            .to(el.phone, { rotation: 0, duration: 0.08 })
            .to(el.chat, { scale: 1, autoAlpha: 1, duration: 0.45, ease: 'back.out(1.7)' }, '-=0.45')
            .to(el.msgIn, { autoAlpha: 1, y: 0, duration: 0.3 })
            .to(el.typing, { autoAlpha: 1, duration: 0.18 }, '+=0.25')
            .to(el.dots, { y: -4, duration: 0.15, yoyo: true, repeat: 3, stagger: 0.08, ease: 'sine.inOut' }, '<')
            .to(el.typing, { autoAlpha: 0, duration: 0.12 })
            .to(el.msgOut, { autoAlpha: 1, scale: 1, duration: 0.3, ease: 'back.out(2)' }, '<');
        hold(1.2);
        tl.to(el.chat, { scale: 0, autoAlpha: 0, duration: 0.3, ease: 'power2.in' });

        // --- Scene 5: Merapat ke dock ---------------------------------
        drive(4, 1.6);
        activate(4);
        tl.to(el.door, { scaleY: 0.08, duration: 0.5, ease: 'power2.inOut' }, '<')
            .to(el.check, { scale: 1, duration: 0.4, ease: 'back.out(3)' });
        hold(0.7);

        // --- CTA penutup ----------------------------------------------
        tl.to(el.cta, { x: 0, duration: 0.55, ease: 'power3.out' })
            .fromTo(el.ctaPhone, { rotation: -12 }, {
                rotation: 12, duration: 0.07, yoyo: true, repeat: 9, ease: 'none',
                transformOrigin: '50% 50%', immediateRender: false
            })
            .to(el.ctaPhone, { rotation: 0, duration: 0.08 })
            .to(el.ctaTitle, { scale: 1.035, duration: 0.4, yoyo: true, repeat: 3, ease: 'sine.inOut' }, '<');
        hold(0.8);

        // Ticker naik & disembunyikan
        tl.to(root, { yPercent: -120, autoAlpha: 0, duration: 0.45, ease: 'power2.in' });
    }

    function finish() {
        if (wheelSpin) wheelSpin.pause();
        if (root) root.style.display = 'none';
        state.playing = false;
        if (typeof cfg.onEnd === 'function') cfg.onEnd();
    }

    // ------------------------------------------------------------------
    // Konten dinamis (nopol, dock, daftar truk dipanggil)
    // ------------------------------------------------------------------
    function personalize(d) {
        var nopol = d.nopol || 'Truk Anda';
        var dock = d.dock || cfg.areaLabel;
        el.msgTitle.textContent = nopol + ' — Giliran Anda!';
        el.msgBody.textContent = 'Segera menuju Dock ' + dock + '.';

        var targets = d.targets || [];
        if (targets.length) {
            var shown = targets.slice(0, 3).map(function (t) {
                return t.no_pol + (t.called_at ? ' (dipanggil ' + t.called_at + ')' : '');
            });
            var more = targets.length > 3 ? '  +' + (targets.length - 3) + ' lainnya' : '';
            el.ctaTarget.textContent = 'Belum merapat: ' + shown.join('  •  ') + more;
        } else {
            el.ctaTarget.textContent = 'Respon cepat = antrian lancar untuk semua supir.';
        }
    }

    function dockLabel(t) {
        if (t.category_label) return t.category_label;
        if (t.area) return t.area;
        return cfg.areaLabel;
    }

    // ------------------------------------------------------------------
    // API publik
    // ------------------------------------------------------------------
    function play(data) {
        if (!tl || cfg.disabled) return false;
        data = data || {};
        if (state.playing && !data.force) return false;
        if (document.hidden && !data.force) return false;

        personalize(data);
        state.playing = true;
        state.lastPlayAt = Date.now();
        state.lastIntervalAt = Date.now();
        if (typeof cfg.onStart === 'function') cfg.onStart(data);
        tl.restart();
        return true;
    }

    function stop() {
        if (tl) tl.pause();
        finish();
    }

    /**
     * Smart trigger: dipanggil setiap selesai fetch data antrian.
     * Putar ticker jika ada truk yang sudah dipanggil >= smartDelayMinutes
     * tetapi masih di parkir (belum merapat ke dock).
     */
    function evaluate(callingList) {
        if (!tl || cfg.disabled) return;
        callingList = Array.isArray(callingList) ? callingList : [];

        // Bersihkan hitungan truk yang sudah tidak ada di daftar panggilan
        var activeIds = {};
        callingList.forEach(function (t) { if (t && t.id != null) activeIds[t.id] = true; });
        Object.keys(state.plays).forEach(function (id) { if (!activeIds[id]) delete state.plays[id]; });

        if (state.playing) return;

        var late = callingList.filter(function (t) {
            return t && t.called_minutes !== null && t.called_minutes !== undefined &&
                t.called_minutes >= cfg.smartDelayMinutes &&
                (state.plays[t.id] || 0) < cfg.maxPlaysPerTruck;
        });
        if (!late.length) return;
        if (state.lastPlayAt > 0 && (Date.now() - state.lastPlayAt < cfg.cooldownMinutes * 60000)) return;

        late.sort(function (a, b) { return b.called_minutes - a.called_minutes; });
        late.forEach(function (t) { state.plays[t.id] = (state.plays[t.id] || 0) + 1; });

        var first = late[0];
        play({ nopol: first.no_pol, dock: dockLabel(first), targets: late, reason: 'smart' });
    }

    function startScheduler() {
        if (schedulerId) clearInterval(schedulerId);
        schedulerId = setInterval(function () {
            if (!cfg.intervalMinutes || cfg.intervalMinutes <= 0 || state.playing) return;
            if (Date.now() - state.lastIntervalAt >= cfg.intervalMinutes * 60000) {
                play({ reason: 'interval' });
            }
        }, 30000);
    }

    function setArea(label) {
        cfg.areaLabel = label || cfg.areaLabel;
    }

    function seekDebug(t) {
        personalize({
            nopol: 'B 1234 XYZ', dock: 'Slipsheet',
            targets: [{ no_pol: 'B 1234 XYZ', called_at: '14:02' }, { no_pol: 'B 9876 KLM', called_at: '14:05' }]
        });
        tl.pause(t);
    }

    function init(opts) {
        if (!global.gsap) {
            console.warn('[EduTicker] GSAP tidak ditemukan, ticker dinonaktifkan.');
            return api;
        }
        cfg = Object.assign({}, DEFAULTS, opts || {});

        var params = new URLSearchParams(global.location.search);
        if (params.get('edu') === 'off') {
            cfg.disabled = true;
            return api;
        }

        if (!root) mount();
        buildTimeline();

        if (params.has('edu_seek')) {
            seekDebug(parseFloat(params.get('edu_seek')) || 0);
            return api;
        }
        if (params.get('edu_test') === '1') {
            setTimeout(function () {
                play({
                    force: true, nopol: 'B 1234 XYZ', dock: 'Slipsheet',
                    targets: [{ no_pol: 'B 1234 XYZ', called_at: '14:02' }]
                });
            }, 1500);
        }

        startScheduler();
        return api;
    }

    var api = {
        init: init,
        play: play,
        stop: stop,
        evaluate: evaluate,
        setArea: setArea,
        isPlaying: function () { return state.playing; },
        duration: function () { return tl ? tl.duration() : 0; }
    };

    global.EduTicker = api;
})(window);
