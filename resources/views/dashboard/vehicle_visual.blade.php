<!DOCTYPE html>
<html lang="en" data-layout-mode="dark">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>3D Yard Live Visualizer — Warehouse Monitoring</title>

    <link rel="shortcut icon" href="{{ asset('assets/images/logo/kecap.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Share+Tech+Mono&display=swap" rel="stylesheet">

    <link href="{{ asset('material/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('material/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        :root {
            --bg-dark: #090d16;
            --card-bg: rgba(15, 23, 42, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-muted: #475569;
            --primary: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            overflow: hidden;
        }

        /* ── Floating Interface Panels ──────── */
        .header-panel {
            position: absolute;
            top: 20px; left: 24px;
            z-index: 10;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px 20px;
            width: 320px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .logo-title {
            font-weight: 800;
            font-size: 18px;
            background: linear-gradient(135deg, #fff 30%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .control-panel {
            position: absolute;
            bottom: 24px; left: 24px;
            z-index: 10;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 14px 18px;
            width: 320px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .kpi-panel {
            position: absolute;
            top: 20px; right: 24px;
            z-index: 10;
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 200px;
        }

        .kpi-card {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .kpi-label { font-size: 10px; font-weight: 600; text-transform: uppercase; color: #64748b; }
        .kpi-val { font-size: 18px; font-weight: 800; }
        .kpi-val.total { color: #f8fafc; }
        .kpi-val.wpm { color: #34d399; }
        .kpi-val.wrm { color: #fbbf24; }
        .kpi-val.wfg { color: #22d3ee; }
        .kpi-val.smu { color: #a78bfa; }
        .kpi-val.gate { color: #94a3b8; }

        /* ── WebGL Canvas Viewport ──────────── */
        .viewport-3d {
            position: absolute;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            z-index: 1;
            outline: none;
        }

        /* ── Floating HTML Labels Overlay ───── */
        #labels-overlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 5;
            overflow: hidden;
        }

        .truck-billboard {
            position: absolute;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            padding: 3px 6px;
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            transform: translate(-50%, -100%);
            transition: left 0.1s ease-out, top 0.1s ease-out;
        }

        .billboard-plate {
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            font-weight: 700;
            color: #f1f5f9;
        }

        .billboard-timer {
            font-family: 'Share Tech Mono', monospace;
            font-size: 8px;
            padding: 1px 4px;
            border-radius: 3px;
            background: rgba(255,255,255,0.06);
            color: #64748b;
        }
        .billboard-timer.tw { color: #fbbf24; background: rgba(245,158,11,0.15); }
        .billboard-timer.tc { color: #f87171; background: rgba(239,68,68,0.15); animation: blinker 1.2s linear infinite; }

        @keyframes blinker { 50% { opacity: 0.25; } }

        .truck-billboard.bottleneck {
            border-color: rgba(239, 68, 68, 0.5);
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.3);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 6px;
            color: #94a3b8;
        }
        .info-row strong { color: #f1f5f9; }

        .live-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: #10b981;
            font-weight: 600;
        }

        .live-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
            animation: pulse-live 1.5s infinite;
        }

        @keyframes pulse-live {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.5; }
        }
    </style>
</head>

<body>

    <!-- ── Floating Panel: Header & Summary ── -->
    <div class="header-panel">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="logo-title">VEHICLE YARD MAP</div>
            <div class="live-status">
                <div class="live-dot"></div> Live
            </div>
        </div>
        <div style="font-size:10px;text-transform:uppercase;color:#475569;letter-spacing:0.1em;margin-bottom:14px;">WebGL Yard Live Visualizer</div>
        
        <div class="info-row">
            <span>Clock:</span>
            <strong id="live-clock" style="font-family:'Share Tech Mono';">00:00:00</strong>
        </div>
        <div class="info-row">
            <span>Last Update:</span>
            <strong id="live-lastupdate">—</strong>
        </div>
        <div class="info-row">
            <span>Total Active Trucks:</span>
            <strong id="live-total">0</strong>
        </div>

        <div style="margin-top:14px;display:flex;gap:8px;">
            <a href="{{ route('dashboard.vehicle') }}" class="btn btn-xs btn-outline-secondary w-50 py-1" style="font-size:11px;">
                <i class="ri-table-line me-1"></i> Table View
            </a>
            <button class="btn btn-xs btn-outline-secondary w-50 py-1" id="btnRefresh" style="font-size:11px;">
                <i class="ri-refresh-line me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- ── Floating Panel: Area KPIs ── -->
    <div class="kpi-panel">
        <div class="kpi-card">
            <span class="kpi-label">Gate / Queue</span>
            <span class="kpi-val gate" id="kpi-gate">0</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">WPM (Yellow)</span>
            <span class="kpi-val wpm" id="kpi-wpm">0</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">WRM (Orange)</span>
            <span class="kpi-val wrm" id="kpi-wrm">0</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">WFG (Green)</span>
            <span class="kpi-val wfg" id="kpi-wfg">0</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">SMU (Blue)</span>
            <span class="kpi-val smu" id="kpi-smu">0</span>
        </div>
    </div>

    <!-- ── Floating Panel: Navigation Controls ── -->
    <div class="control-panel">
        <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:8px;letter-spacing:0.05em;">Map Navigation</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:10px;">
            <button class="btn btn-sm btn-dark" id="btnZoomIn" style="font-size:11px;"><i class="ri-zoom-in-line"></i> Zoom In</button>
            <button class="btn btn-sm btn-dark" id="btnZoomOut" style="font-size:11px;"><i class="ri-zoom-out-line"></i> Zoom Out</button>
        </div>
        <div style="display:flex;align-items:center;justify-content:right;font-size:11px;color:#64748b;">
            <button class="btn btn-link btn-xs p-0 text-decoration-none" id="btnResetView" style="font-size:11px;color:#3b82f6;">Reset View</button>
        </div>
    </div>

    <!-- ── ThreeJS WebGL Canvas ── -->
    <div class="viewport-3d" id="threejs-canvas-container"></div>

    <!-- ── Projected Labels Overlay ── -->
    <div id="labels-overlay"></div>

    <!-- ── Scripts ── -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('material/assets/libs/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('material/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <!-- ThreeJS dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

    <script>
    $(function () {
        // Live clock
        function tickClock() {
            $('#live-clock').text(new Date().toLocaleTimeString('en-US', { hour12: false }));
        }
        setInterval(tickClock, 1000);
        tickClock();

        // ── ThreeJS Scene Initialization ───────────────────────
        const container = document.getElementById('threejs-canvas-container');
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x090d16);

        // Perspective camera positioned straight up, looking down
        const camera = new THREE.PerspectiveCamera(30, window.innerWidth / window.innerHeight, 10, 3000);
        camera.position.set(0, 950, 0); // Directly above center

        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.shadowMap.enabled = true;
        container.appendChild(renderer.domElement);

        // Limit OrbitControls to panning and zooming (disable rotation for flat view)
        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableRotate = false; // View from top only
        controls.enableZoom = true;
        controls.minDistance = 300;
        controls.maxDistance = 1500;
        controls.target.set(0, 0, 0);
        controls.update();

        // ── Lighting ───────────────────────────────────────────
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.65);
        scene.add(ambientLight);

        const dirLight = new THREE.DirectionalLight(0xffffff, 0.4);
        dirLight.position.set(100, 300, 50);
        scene.add(dirLight);

        // ── Draw Map Plane & Roads ─────────────────────────────
        // Main Ground Plane (-475 to 475)
        const groundGeo = new THREE.PlaneGeometry(950, 950);
        const groundMat = new THREE.MeshLambertMaterial({ color: 0x0f1624 });
        const ground = new THREE.Mesh(groundGeo, groundMat);
        ground.rotation.x = -Math.PI / 2; // Flat on floor
        scene.add(ground);

        // Helpers to draw floor rectangles
        function addFloorPlane(px, py, w, d, color, heightOffset = 0.2) {
            const geo = new THREE.PlaneGeometry(w, d);
            const mat = new THREE.MeshLambertMaterial({ color: color });
            const plane = new THREE.Mesh(geo, mat);
            plane.rotation.x = -Math.PI / 2;
            // Shift 2D offset to WebGL coordinates centered at (0, 0)
            plane.position.set(px + w/2 - 475, heightOffset, py + d/2 - 475);
            scene.add(plane);
        }

        // Add asphalt roads
        const ROAD_COLOR = 0x151d30;
        addFloorPlane(560, 0, 60, 350, ROAD_COLOR);     // entrance road
        addFloorPlane(620, 130, 220, 100, ROAD_COLOR);  // timbangan lane
        addFloorPlane(340, 350, 500, 160, ROAD_COLOR);  // open yard area
        addFloorPlane(340, 250, 60, 600, ROAD_COLOR);   // left road
        addFloorPlane(780, 250, 60, 600, ROAD_COLOR);   // right road
        addFloorPlane(100, 800, 740, 100, ROAD_COLOR);  // loading lanes

        // Add lawns
        const LAWN_COLOR = 0x032a15;
        addFloorPlane(20, 20, 320, 200, LAWN_COLOR, 0.1);
        addFloorPlane(380, 20, 140, 140, LAWN_COLOR, 0.1);
        addFloorPlane(860, 20, 70, 910, LAWN_COLOR, 0.1);
        addFloorPlane(420, 250, 340, 80, LAWN_COLOR, 0.1);
        addFloorPlane(20, 390, 50, 440, LAWN_COLOR, 0.1);
        addFloorPlane(80, 530, 240, 250, LAWN_COLOR, 0.1);
        addFloorPlane(20, 920, 910, 20, LAWN_COLOR, 0.1);

        // ── extruded 3D Buildings ─────────────────────────────
        function create3DBuilding(name, px, py, w, d, h, roofColor) {
            const geometry = new THREE.BoxGeometry(w, h, d);
            const sideMat = new THREE.MeshLambertMaterial({ color: 0x1e293b });
            const roofMat = new THREE.MeshLambertMaterial({ color: roofColor });
            
            // Materials: right, left, top, bottom, front, back
            const materials = [sideMat, sideMat, roofMat, sideMat, sideMat, sideMat];
            
            const mesh = new THREE.Mesh(geometry, materials);
            mesh.position.set(px + w/2 - 475, h/2, py + d/2 - 475);
            scene.add(mesh);
        }

        // Build warehouses matching schematic map colors
        create3DBuilding('PRODUKSI', 80, 250, 200, 450, 60, 0x1f2937); // Grey
        create3DBuilding('SMU', 380, 170, 200, 160, 60, 0x0f172a);       // Blue
        create3DBuilding('TIMBANGAN', 720, 130, 110, 80, 30, 0x111827);  // Dark Grey
        create3DBuilding('WFG', 380, 530, 360, 80, 50, 0x022c22);        // Green
        create3DBuilding('WRM', 380, 630, 360, 80, 50, 0x451a03);        // Orange
        create3DBuilding('WPM', 120, 730, 240, 80, 50, 0x422006);        // Yellow
        create3DBuilding('WRM2', 480, 730, 260, 80, 50, 0x451a03);       // Orange

        // ── Parking Docks Slot Coordinates ─────────────────────
        const SLOT_COORDINATES = {
            gate: [
                { x: 590, y: 30,  rot: Math.PI / 2 },
                { x: 590, y: 90,  rot: Math.PI / 2 },
                { x: 590, y: 150, rot: Math.PI / 2 },
                { x: 670, y: 170, rot: 0 },
                { x: 740, y: 170, rot: 0 }
            ],
            wpm: [
                { x: 160, y: 840, rot: Math.PI / 2 },
                { x: 230, y: 840, rot: Math.PI / 2 },
                { x: 300, y: 840, rot: Math.PI / 2 }
            ],
            wrm: [
                { x: 420, y: 550, rot: 0 },
                { x: 500, y: 550, rot: 0 },
                { x: 580, y: 550, rot: 0 },
                { x: 520, y: 840, rot: Math.PI / 2 },
                { x: 620, y: 840, rot: Math.PI / 2 }
            ],
            wfg: [
                { x: 420, y: 450, rot: 0 },
                { x: 500, y: 450, rot: 0 },
                { x: 580, y: 450, rot: 0 }
            ],
            smu: [
                { x: 400, y: 310, rot: 0 },
                { x: 480, y: 310, rot: 0 },
                { x: 560, y: 310, rot: 0 }
            ]
        };

        const ZONE_TESTS = [
            {
                key: 'gate',
                test: (tx) => {
                    const activeStatuses = ['wpm','wrm_bongkar','antri_sampling','sampling','wfg_muat','smu'];
                    return !activeStatuses.includes(tx.status);
                }
            },
            {
                key: 'wpm',
                test: (tx) => tx.status === 'wpm' ||
                    (['antri_sampling','sampling'].includes(tx.status) && tx.target_location_code === 'C001')
            },
            {
                key: 'wrm',
                test: (tx) => tx.status === 'wrm_bongkar' ||
                    (['antri_sampling','sampling'].includes(tx.status) && tx.target_location_code !== 'C001')
            },
            {
                key: 'wfg',
                test: (tx) => tx.status === 'wfg_muat'
            },
            {
                key: 'smu',
                test: (tx) => tx.status === 'smu'
            }
        ];

        // ── Spawning 3D Truck Group ───────────────────────────
        const TRUCK_COLORS = {
            gate: { cab: 0x64748b, trailer: 0x334155 },
            wpm:  { cab: 0x10b981, trailer: 0x064e3b },
            wrm:  { cab: 0xf59e0b, trailer: 0x78350f },
            wfg:  { cab: 0x06b6d4, trailer: 0x164e63 },
            smu:  { cab: 0x8b5cf6, trailer: 0x4c1d95 },
            bottleneck: { cab: 0xef4444, trailer: 0x7f1d1d }
        };

        function create3DTruckMesh(zone, isBottleneck) {
            const group = new THREE.Group();
            const colors = isBottleneck ? TRUCK_COLORS.bottleneck : (TRUCK_COLORS[zone] || TRUCK_COLORS.gate);

            // Container (26x14x16)
            const trailerGeo = new THREE.BoxGeometry(26, 14, 14);
            const trailerMat = new THREE.MeshLambertMaterial({ color: colors.trailer });
            const trailer = new THREE.Mesh(trailerGeo, trailerMat);
            trailer.position.set(-8, 7, 0); // Offset backward
            group.add(trailer);

            // Cab (12x12x12)
            const cabGeo = new THREE.BoxGeometry(10, 12, 12);
            const cabMat = new THREE.MeshLambertMaterial({ color: colors.cab });
            const cab = new THREE.Mesh(cabGeo, cabMat);
            cab.position.set(10, 6, 0); // Offset forward
            group.add(cab);

            // Small wheels (Cylinders)
            const wheelGeo = new THREE.CylinderGeometry(3.5, 3.5, 2, 8);
            const wheelMat = new THREE.MeshLambertMaterial({ color: 0x0f172a });

            function addWheel(x, z) {
                const wheel = new THREE.Mesh(wheelGeo, wheelMat);
                wheel.rotation.x = Math.PI / 2;
                wheel.position.set(x, 3.5, z);
                group.add(wheel);
            }
            addWheel(-14, 7);
            addWheel(-14, -7);
            addWheel(-2, 7);
            addWheel(-2, -7);
            addWheel(10, 7);
            addWheel(10, -7);

            scene.add(group);
            return group;
        }

        // Active trucks dictionary
        let activeTrucks = {};

        // ── Render 3D Scene based on AJAX data ───────────────
        function processRender(trucks) {
            const overlay = $('#labels-overlay');
            const usedIds = {};
            let gateTotal = 0, wpmTotal = 0, wrmTotal = 0, wfgTotal = 0, smuTotal = 0;

            ZONE_TESTS.forEach(({ key, test }) => {
                const zoneTrucks = trucks.filter(test);

                if (key === 'gate')  gateTotal = zoneTrucks.length;
                if (key === 'wpm')   wpmTotal  = zoneTrucks.length;
                if (key === 'wrm')   wrmTotal  = zoneTrucks.length;
                if (key === 'wfg')   wfgTotal  = zoneTrucks.length;
                if (key === 'smu')   smuTotal  = zoneTrucks.length;

                zoneTrucks.forEach((tx, idx) => {
                    usedIds[tx.id] = true;
                    
                    const slots = SLOT_COORDINATES[key] || [{ x: 0, y: 0, rot: 0 }];
                    const coord = slots[idx % slots.length] || slots[0];
                    const isBn = !!tx.is_bottleneck;

                    // Apply queue line coordinates
                    const extraOffset = idx >= slots.length ? (idx - slots.length + 1) * -45 : 0;
                    let targetX = coord.x;
                    let targetZ = coord.y;
                    if (coord.rot === Math.PI / 2) targetZ += extraOffset;
                    else targetX += extraOffset;

                    // Convert to centered WebGL units
                    targetX = targetX - 475;
                    targetZ = targetZ - 475;

                    let truckObj = activeTrucks[tx.id];

                    if (!truckObj) {
                        // Spawn new truck mesh & label
                        const mesh = create3DTruckMesh(key, isBn);
                        mesh.position.set(targetX, 0, targetZ);
                        mesh.rotation.y = coord.rot;

                        const labelHtml = `
                        <div class="truck-billboard ${isBn ? 'bottleneck' : ''}" id="lbl-${tx.id}">
                            <span class="billboard-plate">${tx.no_pol || '—'}</span>
                            <span class="billboard-timer" data-start="${tx.arrival_time || ''}" data-limit="${tx.limit_minutes || 0}">—</span>
                        </div>`;
                        overlay.append(labelHtml);

                        truckObj = {
                            mesh: mesh,
                            label: document.getElementById(`lbl-${tx.id}`),
                            targetPos: new THREE.Vector3(targetX, 0, targetZ),
                            targetRot: coord.rot
                        };
                        activeTrucks[tx.id] = truckObj;
                    } else {
                        // Update existing truck targets
                        truckObj.targetPos.set(targetX, 0, targetZ);
                        truckObj.targetRot = coord.rot;
                    }
                });
            });

            // Clean up removed trucks
            Object.keys(activeTrucks).forEach(id => {
                if (!usedIds[id]) {
                    scene.remove(activeTrucks[id].mesh);
                    if (activeTrucks[id].label) {
                        activeTrucks[id].label.remove();
                    }
                    delete activeTrucks[id];
                }
            });

            // Update stats
            $('#kpi-gate').text(gateTotal);
            $('#kpi-wpm').text(wpmTotal);
            $('#kpi-wrm').text(wrmTotal);
            $('#kpi-wfg').text(wfgTotal);
            $('#kpi-smu').text(smuTotal);
            $('#live-total').text(trucks.length);
            $('#live-lastupdate').text(moment().format('HH:mm:ss'));
        }

        // Load AJAX data
        function loadData() {
            $.ajax({
                url: "{{ route('dashboard.vehicle.data') }}",
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    const queues = res.queues || {};
                    const trucks = [];
                    ['WPM','WRM','WFG','SMU'].forEach(k => {
                        (queues[k] || []).forEach(tx => trucks.push(tx));
                    });
                    processRender(trucks);
                },
                error: function (xhr) {
                    console.error('Gagal memuat data visual', xhr);
                }
            });
        }

        // ── Camera Project 3D Vector to 2D Screen ──────────────
        const tempV = new THREE.Vector3();
        function updateBillboardsProjected() {
            Object.keys(activeTrucks).forEach(id => {
                const t = activeTrucks[id];
                if (!t.label) return;

                t.mesh.getWorldPosition(tempV);
                tempV.y += 18; // Float above truck
                tempV.project(camera);

                const x = (tempV.x * .5 + .5) * window.innerWidth;
                const y = (tempV.y * -.5 + .5) * window.innerHeight;

                t.label.style.left = `${x}px`;
                t.label.style.top = `${y}px`;
            });
        }

        // ── Zoom Controls ─────────────────────────────────────
        $('#btnZoomIn').on('click', () => {
            const dist = camera.position.y;
            camera.position.y = Math.max(300, dist - 100);
            controls.update();
        });
        
        $('#btnZoomOut').on('click', () => {
            const dist = camera.position.y;
            camera.position.y = Math.min(1500, dist + 100);
            controls.update();
        });

        $('#btnResetView').on('click', () => {
            camera.position.set(0, 950, 0);
            controls.target.set(0, 0, 0);
            controls.update();
        });

        // ── Animation / Loop ──────────────────────────────────
        function animate() {
            requestAnimationFrame(animate);

            // Smoothly move meshes (lerp position and angle)
            Object.keys(activeTrucks).forEach(id => {
                const t = activeTrucks[id];
                t.mesh.position.lerp(t.targetPos, 0.1);
                
                // Rotational lerp
                let diff = t.targetRot - t.mesh.rotation.y;
                // Normalize diff to -PI to PI
                diff = Math.atan2(Math.sin(diff), Math.cos(diff));
                t.mesh.rotation.y += diff * 0.1;
            });

            controls.update();
            renderer.render(scene, camera);

            // Sync projected labels
            updateBillboardsProjected();
        }
        animate();

        // ── Timers Tick ───────────────────────────────────────
        function updateTimers() {
            $('.billboard-timer').each(function () {
                const startStr = $(this).data('start');
                if (!startStr) return;

                const limit   = parseInt($(this).data('limit')) || 0;
                const arrival = moment(startStr, 'YYYY-MM-DD HH:mm:ss');
                const diffSec = moment().diff(arrival, 'seconds');
                if (diffSec < 0) return;

                const h = Math.floor(diffSec / 3600);
                const m = Math.floor((diffSec % 3600) / 60);
                const s = diffSec % 60;

                let str = '';
                if (h > 0) str += h + 'j ';
                str += m + 'm ' + s + 'd';
                $(this).text(str);

                $(this).removeClass('tw tc');
                if (limit > 0) {
                    const diffMin = Math.floor(diffSec / 60);
                    if (diffMin >= limit) $(this).addClass('tc');
                    else if (diffMin >= Math.floor(limit * 0.75)) $(this).addClass('tw');
                }
            });
        }
        setInterval(updateTimers, 1000);

        // ── Live Echo ─────────────────────────────────────────
        function setupEcho() {
            if (typeof window.Echo === 'function') {
                window.Pusher = Pusher;
                window.Echo = new window.Echo({
                    broadcaster: 'reverb',
                    key: '{{ config('broadcasting.connections.reverb.key') }}',
                    wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}' || window.location.hostname,
                    wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                    wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                    forceTLS: '{{ config('broadcasting.connections.reverb.options.scheme', 'http') }}' === 'https',
                    enabledTransports: ['ws', 'wss'],
                });
            }

            if (window.Echo && typeof window.Echo.channel === 'function') {
                window.Echo.channel('vehicle-tracking')
                    .listen('.vehicle.updated', (payload) => {
                        if (window.toastr) {
                            toastr.info(payload.message || 'Data lokasi truk diupdate', 'Update Truk');
                        }
                        loadData();
                    });
            } else {
                setTimeout(setupEcho, 200);
            }
        }

        // ── Window Resize ─────────────────────────────────────
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // Initial Boot
        loadData();
        setupEcho();
        setInterval(loadData, 30000); // Polling fallback

        $('#btnRefresh').on('click', function () {
            loadData();
            if (window.toastr) toastr.success('Data berhasil diperbarui.');
        });
    });
    </script>
</body>
</html>
