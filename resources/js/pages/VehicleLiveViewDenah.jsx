import React, { useState, useEffect, useRef, useMemo } from "react";
import axios from "axios";
import { gsap } from "gsap";
import {
    Truck,
    MapPin,
    RefreshCw,
    Play,
    Pause,
    Maximize2,
    Minimize2,
    ZoomIn,
    ZoomOut,
    RotateCcw,
    Layers,
    Info,
    CheckCircle2,
    Clock,
    Shield,
    Scale,
    Building2,
    X,
    Filter,
    Compass,
    Activity,
    PlusCircle,
    ChevronRight,
    Search,
    Navigation,
    CornerDownRight,
    LogOut
} from "lucide-react";

// Top-Down Truck SVG Component
const TopDownTruck = ({
    plate,
    type = "box",
    status = "loading",
    angle = 0,
    scale = 1,
    isMoving = false,
    isSelected = false,
    onClick,
    accentColor = "#3b82f6"
}) => {
    // Dynamic styling based on status
    const statusColor =
        status === "loading" || status === "wfg" || status === "smu"
            ? "#10b981" // Green - Finished Goods / Loading
            : status === "wpm"
            ? "#6366f1" // Indigo - Packaging
            : status === "wrm" || status === "sampling" || status === "qc"
            ? "#f59e0b" // Amber - Raw Material
            : status === "parkir"
            ? "#a855f7" // Purple - Parked
            : status === "timbangan" || status === "timbangan_in"
            ? "#06b6d4" // Cyan - Weighing In
            : status === "timbangan_out"
            ? "#14b8a6" // Teal - Scale Out
            : status === "gate_out"
            ? "#ef4444" // Red - Outbound
            : accentColor;

    return (
        <g
            transform={`rotate(${angle}) scale(${scale})`}
            className="cursor-pointer transition-transform duration-75 group"
            onClick={onClick}
        >
            {/* Selection indicator ring */}
            {isSelected && (
                <rect
                    x="-18"
                    y="-34"
                    width="36"
                    height="68"
                    rx="8"
                    fill="none"
                    stroke="#60a5fa"
                    strokeWidth="2.5"
                    strokeDasharray="4,3"
                    className="animate-pulse"
                />
            )}

            {/* Headlights beam effect when moving */}
            {isMoving && (
                <polygon
                    points="-10,-30 10,-30 22,-70 -22,-70"
                    fill="url(#headlightBeam)"
                    opacity="0.4"
                />
            )}

            {/* Truck Drop Shadow */}
            <rect
                x="-13"
                y="-27"
                width="26"
                height="54"
                rx="4"
                fill="#000000"
                opacity="0.55"
                filter="url(#shadowBlur)"
            />

            {/* Wheels / Tires (Top-Down) */}
            <rect x="-14.5" y="-22" width="3" height="7" rx="1.5" fill="#020617" />
            <rect x="11.5" y="-22" width="3" height="7" rx="1.5" fill="#020617" />
            <rect x="-14.5" y="10" width="3" height="8" rx="1.5" fill="#020617" />
            <rect x="11.5" y="10" width="3" height="8" rx="1.5" fill="#020617" />
            <rect x="-14.5" y="18" width="3" height="8" rx="1.5" fill="#020617" />
            <rect x="11.5" y="18" width="3" height="8" rx="1.5" fill="#020617" />

            {/* Truck Cargo / Trailer Body */}
            <rect
                x="-12.5"
                y="-8"
                width="25"
                height="34"
                rx="2"
                fill="#1e293b"
                stroke={statusColor}
                strokeWidth="1.5"
            />
            {/* Trailer corrugated roof lines */}
            <line x1="-9" y1="-2" x2="9" y2="-2" stroke="#334155" strokeWidth="1" />
            <line x1="-9" y1="5" x2="9" y2="5" stroke="#334155" strokeWidth="1" />
            <line x1="-9" y1="12" x2="9" y2="12" stroke="#334155" strokeWidth="1" />
            <line x1="-9" y1="19" x2="9" y2="19" stroke="#334155" strokeWidth="1" />

            {/* Truck Cabin (Front) */}
            <path
                d="M -11 -8 L -11 -24 Q -11 -28 -5 -28 L 5 -28 Q 11 -28 11 -24 L 11 -8 Z"
                fill="#334155"
                stroke={statusColor}
                strokeWidth="1.2"
            />

            {/* Windshield */}
            <path
                d="M -9 -21 Q 0 -23 9 -21 L 8 -16 Q 0 -18 -8 -16 Z"
                fill="#60a5fa"
                opacity="0.85"
            />

            {/* Side Mirrors */}
            <rect x="-14.5" y="-21" width="3" height="2" rx="0.8" fill="#475569" />
            <rect x="11.5" y="-21" width="3" height="2" rx="0.8" fill="#475569" />

            {/* Cabin Roof Light */}
            <circle cx="0" cy="-25" r="1.5" fill="#facc15" />

            {/* Status Beacon Glow */}
            <circle
                cx="0"
                cy="-2"
                r="3"
                fill={statusColor}
                className={isMoving ? "animate-ping" : ""}
                opacity="0.9"
            />

            {/* License Plate Badge Tag Floating Above Truck */}
            {plate && (
                <g transform="translate(0, -35)">
                    <rect
                        x="-26"
                        y="-10"
                        width="52"
                        height="13"
                        rx="3"
                        fill="#090d16"
                        stroke={statusColor}
                        strokeWidth="1"
                        opacity="0.95"
                    />
                    <text
                        x="0"
                        y="-1.5"
                        textAnchor="middle"
                        fill="#f8fafc"
                        fontSize="7.5"
                        fontWeight="700"
                        fontFamily="'JetBrains Mono', monospace"
                    >
                        {plate}
                    </text>
                </g>
            )}
        </g>
    );
};

export default function VehicleLiveViewDenah() {
    // Data states
    const [transactions, setTransactions] = useState([]);
    const [parkingZones, setParkingZones] = useState([]);
    const [parkingSummary, setParkingSummary] = useState(null);
    const [loading, setLoading] = useState(true);
    const [lastSync, setLastSync] = useState(null);
    const [autoRefresh, setAutoRefresh] = useState(true);
    const [simulationActive, setSimulationActive] = useState(true);

    // Filter & Inspection states
    const [selectedEntity, setSelectedEntity] = useState(null); // truck, building, or slot
    const [activeFilter, setActiveFilter] = useState("all"); // all, wfg_smu, wpm, wrm, timbangan, parkir
    const [searchQuery, setSearchQuery] = useState("");

    // Pan & Zoom states
    const [zoomLevel, setZoomLevel] = useState(0.95);
    const [panOffset, setPanOffset] = useState({ x: 0, y: 0 });
    const [isPanning, setIsPanning] = useState(false);
    const [panStart, setPanStart] = useState({ x: 0, y: 0 });
    const [isFullscreen, setIsFullscreen] = useState(false);

    // Live Clock
    const [currentTime, setCurrentTime] = useState(new Date().toLocaleTimeString("id-ID"));
    useEffect(() => {
        const timer = setInterval(() => {
            setCurrentTime(new Date().toLocaleTimeString("id-ID"));
        }, 1000);
        return () => clearInterval(timer);
    }, []);

    // SVG References for Path Calculation & GSAP
    const svgRef = useRef(null);
    const pathRef1 = useRef(null);
    const pathRef2 = useRef(null);
    const pathRef3 = useRef(null);
    const pathRef4 = useRef(null);
    const pathRef5 = useRef(null);
    const checkoutPathRef = useRef(null);
    const pathDockWRMRef = useRef(null);
    const pathDockWPMRef = useRef(null);
    const pathDockWFGRef = useRef(null);

    // Standby Scale Out demo vehicle (parked on Scale 2 waiting for checkout)
    const [standbyScaleOut, setStandbyScaleOut] = useState({
        id: "standby-scale2",
        no_pol: "B 9040 TEX",
        nama_driver: "Yusup Marjuki",
        vendor: "PT Mitra Logistik",
        item: "BONGKARAN SELESAI",
        status: "timbangan_out",
        current_location_name: "Timbangan Scale 2 (Scale Out)",
        target_location_name: "Gate Out",
        no_spb: "SPB-2026-0902",
        no_antrian: "OUT-04"
    });

    // Trucks actively driving from Dock (WFG/SMU, WPM, WRM) -> Timbangan Out (Scale 2)
    const [transitToTimbanganTrucks, setTransitToTimbanganTrucks] = useState([]);
    // Trucks actively driving from Kantong Parkir -> Dock WFG/SMU (Mulai Bongkar/Muat)
    const [transitParkirToDockTrucks, setTransitParkirToDockTrucks] = useState([]);
    // Vehicles arrived and parked on Scale 2 (or queued behind it)
    const [parkedScaleOutVehicles, setParkedScaleOutVehicles] = useState([]);
    // Map of previous transactions to detect status transition from dock -> timbangan_out
    const prevTxMapRef = useRef(new Map());
    const initialFetchDoneRef = useRef(false);
    const transitTimelinesRef = useRef(new Map());

    // Exiting trucks currently animated from Timbangan -> Gate Out -> Disappear
    const [exitingTrucks, setExitingTrucks] = useState([]);
    const prevTimbanganOutRef = useRef([]);

    // Simulation Trucks Positions state controlled by GSAP
    const [truckPos, setTruckPos] = useState({
        sim1: { x: 105, y: 1120, angle: 0 },
        sim2: { x: 625, y: 650, angle: 0 },
        sim3: { x: 1055, y: 650, angle: 0 },
        sim4: { x: 390, y: 650, angle: 0 },
        sim5: { x: 1300, y: 240, angle: 180 }
    });

    // Reference to GSAP Tweens/Timelines
    const gsapTimelinesRef = useRef([]);

    // SVG Paths Data matching EXACTLY the user diagram road network:
    const routePaths = useMemo(() => ({
        // Route 1: Gate In -> Pos Security -> Timbangan -> Kantong Parkir (Column 1 & 2)
        r1: "M 105 1140 L 105 880 L 105 730 L 105 290 L 625 290 L 625 720",
        // Route 2: Kantong Parkir Col 2 -> Main Road -> WRM Dock
        r2: "M 625 580 L 625 290 L 1290 290 L 1290 240",
        // Route 3: Kantong Parkir Col 3 -> Main Road -> WPM Dock
        r3: "M 1055 580 L 1055 290 L 840 290 L 840 240",
        // Route 4: Kantong Parkir Col 1 -> Main Road -> WFG / SMU Dock
        r4: "M 105 450 L 105 290 L 390 290 L 390 240",
        // Route 5: Docks -> Top Horizontal Road -> West Road (Gate Out)
        r5: "M 1290 240 L 1290 290 L 105 290 L 105 730 L 105 880 L 105 1140",
        // Route Checkout: Scale 2 Outbound -> West Driveway -> Main Road South -> Gate Out
        rCheckout: "M 472.5 790 L 105 790 L 105 1140",
        // Direct Routes from Docks to Scale 2 (Timbangan Out)
        rDockWRM: "M 1290 235 L 1290 290 L 105 290 L 105 790 L 472.5 790",
        rDockWPM: "M 840 235 L 840 290 L 105 290 L 105 790 L 472.5 790",
        rDockWFG: "M 390 235 L 390 290 L 105 290 L 105 790 L 472.5 790"
    }), []);

    // Simulated truck metadata
    const simulatedTrucksData = useMemo(() => [
        {
            id: "sim1",
            plate: "B 9482 TEY",
            driver: "Rudi Hartono",
            vendor: "PT Sumber Rejeki",
            item: "GULA TEBU (RAW)",
            status: "Inbound -> Kantong Parkir",
            color: "#06b6d4",
            routePathId: "r1",
            duration: 18
        },
        {
            id: "sim2",
            plate: "B 9102 TEE",
            driver: "Agus Santoso",
            vendor: "PT Harum Alam Segar",
            item: "GARAM HALUS",
            status: "Menuju Dock WRM",
            color: "#f59e0b",
            routePathId: "r2",
            duration: 14
        },
        {
            id: "sim3",
            plate: "B 9308 KXA",
            driver: "Deni Prasetya",
            vendor: "PT Kemasan Mandiri",
            item: "KARTON BOX PACKAGING",
            status: "Menuju Dock WPM",
            color: "#6366f1",
            routePathId: "r3",
            duration: 13
        },
        {
            id: "sim4",
            plate: "B 5821 PL",
            driver: "Budi Setiawan",
            vendor: "PT Cuanki Logistik",
            item: "FINISHED GOODS EXPORT",
            status: "Menuju Dock WFG/SMU",
            color: "#10b981",
            routePathId: "r4",
            duration: 12
        }
    ], []);

    // GSAP Path Animation Setup for continuous loop trucks
    useEffect(() => {
        const pathElements = {
            r1: pathRef1.current,
            r2: pathRef2.current,
            r3: pathRef3.current,
            r4: pathRef4.current,
            r5: pathRef5.current
        };

        // Clear previous animations
        gsapTimelinesRef.current.forEach((tween) => tween?.kill?.());
        gsapTimelinesRef.current = [];

        if (!simulationActive) return;

        simulatedTrucksData.forEach((truck) => {
            const pathElem = pathElements[truck.routePathId];
            if (!pathElem) return;

            const totalLength = pathElem.getTotalLength();
            if (!totalLength) return;

            const proxy = { progress: 0 };

            const tween = gsap.to(proxy, {
                progress: 1,
                duration: truck.duration,
                repeat: -1,
                ease: "none",
                onUpdate: () => {
                    const currentLen = proxy.progress * totalLength;
                    const p1 = pathElem.getPointAtLength(currentLen);
                    const nextLen = Math.min(currentLen + 2, totalLength);
                    const p2 = pathElem.getPointAtLength(nextLen);

                    let angle = 0;
                    if (p2.x !== p1.x || p2.y !== p1.y) {
                        angle = Math.atan2(p2.y - p1.y, p2.x - p1.x) * (180 / Math.PI) + 90;
                    }

                    setTruckPos((prev) => ({
                        ...prev,
                        [truck.id]: {
                            x: p1.x,
                            y: p1.y,
                            angle: angle
                        }
                    }));
                }
            });

            gsapTimelinesRef.current.push(tween);
        });

        return () => {
            gsapTimelinesRef.current.forEach((tween) => tween?.kill?.());
            gsapTimelinesRef.current = [];
        };
    }, [simulationActive, simulatedTrucksData]);

    // GSAP Trigger: Animate Truck Driving from Dock (WRM, WPM, WFG/SMU) -> Timbangan Out (Scale 2)
    const triggerDockToTimbanganAnimation = (truckData, originParam) => {
        if (!truckData) return;

        const plate = truckData.no_pol || truckData.no_polisi || truckData.plate || "B 9811 KXY";

        // Prevent duplicate animation if already driving
        if (transitToTimbanganTrucks.some((t) => t.plate === plate)) {
            return;
        }

        // Determine origin dock: 'wfg', 'wpm', 'wrm'
        let origin = originParam || truckData.origin;
        if (!origin) {
            const combined = `${truckData.status || ""} ${truckData.target_location_name || ""} ${truckData.current_location_name || ""} ${truckData.message || ""}`.toLowerCase();
            if (combined.includes("wrm")) origin = "wrm";
            else if (combined.includes("wpm")) origin = "wpm";
            else origin = "wfg";
        }

        // Coordinates for each dock exit onto top horizontal road (y = 290)
        let startX = origin === "wrm" ? 1290 : origin === "wpm" ? 840 : 390;
        if (truckData.dockBayX) {
            startX = truckData.dockBayX;
        }

        // Route: Dock Bay -> Down to Top Road (y=290) -> Left to West Road (x=105) -> South to Timbangan (y=790) -> East to Scale 2 (x=472.5, y=790)
        const pathD = `M ${startX} 235 L ${startX} 290 L 105 290 L 105 790 L 472.5 790`;
        const transitId = "transit-dock-" + (truckData.id || plate) + "-" + Date.now();

        const transitItem = {
            id: transitId,
            txId: truckData.id,
            plate: plate,
            driver: truckData.nama_driver || truckData.driver || "Driver",
            vendor: truckData.vendor || "PT Mitra Logistik",
            item: truckData.item || "MUATAN SELESAI",
            origin: origin,
            status: "Selesai Bongkar/Muat -> Menuju Timbangan Out",
            x: startX,
            y: 235,
            angle: 180, // Facing south towards road
            pathD: pathD
        };

        setTransitToTimbanganTrucks((prev) => [...prev, transitItem]);

        // Calculate length of SVG Path dynamically
        const tempPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
        tempPath.setAttribute("d", pathD);
        const totalLen = tempPath.getTotalLength() || 1200;

        const proxy = { progress: 0 };
        const duration = origin === "wrm" ? 7 : origin === "wpm" ? 6 : 5;

        const tween = gsap.to(proxy, {
            progress: 1,
            duration: duration,
            ease: "power1.inOut",
            onUpdate: () => {
                const curLen = proxy.progress * totalLen;
                const p1 = tempPath.getPointAtLength(curLen);
                const nextLen = Math.min(curLen + 2, totalLen);
                const p2 = tempPath.getPointAtLength(nextLen);

                let angle = 180;
                if (p2.x !== p1.x || p2.y !== p1.y) {
                    angle = Math.atan2(p2.y - p1.y, p2.x - p1.x) * (180 / Math.PI) + 90;
                }

                setTransitToTimbanganTrucks((prev) =>
                    prev.map((t) =>
                        t.id === transitId
                            ? { ...t, x: p1.x, y: p1.y, angle: angle }
                            : t
                    )
                );
            },
            onComplete: () => {
                // Remove from transit
                setTransitToTimbanganTrucks((prev) => prev.filter((t) => t.id !== transitId));

                // Mount vehicle onto Scale 2 platform / arrived list
                const parkedItem = {
                    ...truckData,
                    id: truckData.id || "scale2-" + Date.now(),
                    no_pol: plate,
                    nama_driver: transitItem.driver,
                    vendor: transitItem.vendor,
                    item: transitItem.item,
                    status: "timbangan_out",
                    current_location_name: "Timbangan Scale 2 (Scale Out)",
                    target_location_name: "Gate Out",
                    no_spb: truckData.no_spb || "SPB-2026-DONE",
                    no_antrian: truckData.no_antrian || "OUT-01"
                };

                setParkedScaleOutVehicles((prev) => {
                    const filtered = prev.filter((p) => p.no_pol !== plate);
                    return [parkedItem, ...filtered];
                });

                // Clear standby dummy if active
                setStandbyScaleOut(null);
            }
        });

        transitTimelinesRef.current.set(transitId, tween);
    };

    // Quick trigger demo for user testing "Selesai Muat -> Timbangan Out"
    const triggerDemoDockToTimbangan = () => {
        // Pick an active dock vehicle if exists
        const dockTruck =
            vehiclesByArea.wfg_smu[0] ||
            vehiclesByArea.wpm[0] ||
            vehiclesByArea.wrm[0];

        if (dockTruck) {
            let origin = "wfg";
            if (vehiclesByArea.wrm.some((t) => t.no_pol === dockTruck.no_pol)) origin = "wrm";
            else if (vehiclesByArea.wpm.some((t) => t.no_pol === dockTruck.no_pol)) origin = "wpm";
            triggerDockToTimbanganAnimation(dockTruck, origin);
        } else {
            // Generate a demo truck departing from WRM dock
            const demoPlates = ["B 9811 KXY", "B 9320 TFR", "D 8472 BAS", "B 9705 WFG"];
            const randPlate = demoPlates[Math.floor(Math.random() * demoPlates.length)];
            triggerDockToTimbanganAnimation({
                id: "demo-dock-" + Date.now(),
                no_pol: randPlate,
                nama_driver: "Hendra Gunawan",
                vendor: "PT Indo Logistik Prima",
                item: "SELESAI BONGKAR WRM",
                status: "timbangan_out",
                no_spb: "SPB-2026-DEMO",
                no_antrian: "OUT-09"
            }, "wrm");
        }
    };

    // GSAP Trigger: Animate Truck Driving from Kantong Parkir -> Dock (WFG/SMU, WPM, WRM)
    // Sesuai alur: Truk tetap di parkiran saat ambil antrian, baru berjalan ke dock saat Action "Mulai Bongkar/Muat"
    const triggerParkirToDockAnimation = (truckData, destParam = "wfg_smu") => {
        if (!truckData) return;
        const plate = truckData.no_pol || truckData.no_polisi || truckData.plate || "B 5128 CPK";

        // Mencegah animasi ganda jika sedang berjalan
        if (transitParkirToDockTrucks.some((t) => t.plate === plate)) {
            return;
        }

        let dest = destParam || "wfg_smu";
        const combined = `${truckData.status || ""} ${truckData.target_location_name || ""} ${truckData.current_location_name || ""}`.toLowerCase();
        if (combined.includes("wrm")) dest = "wrm";
        else if (combined.includes("wpm")) dest = "wpm";
        else dest = "wfg_smu";

        // Jalur dari Kantong Parkir (Col 1: x=105, y=450) -> Top Road (y=290) -> Dock (y=240)
        let dockX = 390; // Default WFG / SMU
        if (dest === "wpm") dockX = 840;
        else if (dest === "wrm") dockX = 1290;

        const pathD = `M 105 450 L 105 290 L ${dockX} 290 L ${dockX} 240`;
        const transitId = "transit-parkir-" + (truckData.id || plate) + "-" + Date.now();

        const transitItem = {
            id: transitId,
            txId: truckData.id,
            plate: plate,
            driver: truckData.nama_driver || truckData.driver || "Driver Logistik",
            vendor: truckData.vendor || "PT Mitra Logistik",
            item: truckData.item || "BARANG BONGKAR/MUAT",
            dest: dest,
            status: "Mulai Bongkar/Muat -> Berjalan Menuju Dock",
            x: 105,
            y: 450,
            angle: 0,
            pathD: pathD
        };

        setTransitParkirToDockTrucks((prev) => [...prev, transitItem]);

        const tempPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
        tempPath.setAttribute("d", pathD);
        const totalLen = tempPath.getTotalLength() || 600;

        const proxy = { progress: 0 };
        const duration = dest === "wrm" ? 7 : dest === "wpm" ? 6 : 5.5;

        gsap.to(proxy, {
            progress: 1,
            duration: duration,
            ease: "power1.inOut",
            onUpdate: () => {
                const curLen = proxy.progress * totalLen;
                const p1 = tempPath.getPointAtLength(curLen);
                const nextLen = Math.min(curLen + 2, totalLen);
                const p2 = tempPath.getPointAtLength(nextLen);

                let angle = 0;
                if (p2.x !== p1.x || p2.y !== p1.y) {
                    angle = Math.atan2(p2.y - p1.y, p2.x - p1.x) * (180 / Math.PI) + 90;
                }

                setTransitParkirToDockTrucks((prev) =>
                    prev.map((t) =>
                        t.id === transitId
                            ? { ...t, x: p1.x, y: p1.y, angle: angle }
                            : t
                    )
                );
            },
            onComplete: () => {
                // Truk tiba di dock! Hapus dari animasi jalan dan segarkan data dock
                setTransitParkirToDockTrucks((prev) => prev.filter((t) => t.id !== transitId));
                fetchData();
            }
        });
    };

    // Quick Trigger Demo untuk user testing "Mulai Muat: Parkir -> Dock WFG/SMU"
    const triggerDemoParkirToDock = () => {
        // Ambil truk yang sedang menunggu di parkiran jika ada
        const waitingTruck =
            vehiclesByArea.parkir.find((t) => {
                const st = (t.status || "").toLowerCase();
                const tg = (t.target_location_name || "").toLowerCase();
                return st === "wfg" || st === "smu" || tg.includes("wfg") || tg.includes("smu");
            }) ||
            vehiclesByArea.parkir[0];

        if (waitingTruck) {
            triggerParkirToDockAnimation(waitingTruck, "wfg_smu");
        } else {
            const demoPlates = ["B 5128 CPK", "B 5228 PLC", "B 5228 PL", "B 5228 PP"];
            const randPlate = demoPlates[Math.floor(Math.random() * demoPlates.length)];
            triggerParkirToDockAnimation({
                id: "demo-parkir-" + Date.now(),
                no_pol: randPlate,
                nama_driver: "Sukri / Driver",
                vendor: "PT Sumber Rejeki",
                item: "FINISHED GOODS SMU/WFG",
                status: "smu",
                no_spb: "SPB-2026-DEMO",
                no_antrian: "01"
            }, "wfg_smu");
        }
    };

    // Action Trigger: Mulai Bongkar/Muat riil memanggil endpoint backend lalu animasi
    const handleStartLoading = async (vehicleId, dest = "wfg_smu", truckData = null) => {
        const url = dest === "smu"
            ? `/vehicle-monitoring/smu/start-loading/${vehicleId}`
            : `/vehicle-monitoring/wfg/start-loading/${vehicleId}`;

        // Jalankan animasi visual jalan dari parkir ke dock seketika
        if (truckData) {
            triggerParkirToDockAnimation(truckData, dest);
        }

        try {
            const res = await axios.post(url);
            if (res.data?.success) {
                setTimeout(fetchData, 800);
            }
        } catch (err) {
            console.warn("API start-loading notice (demo fallback active):", err?.message);
        }
    };

    // GSAP Trigger for Truck Checking Out at Timbangan -> Gate Out -> Disappear!
    const triggerCheckoutExitAnimation = (customTruck) => {
        const truck = customTruck || activeScaleOutVehicle || standbyScaleOut || {
            no_pol: "B 9040 TEX",
            nama_driver: "Yusup Marjuki",
            vendor: "PT Mitra Logistik",
            item: "BONGKARAN SELESAI",
            status: "timbangan_out"
        };

        const targetPlate = truck.no_pol || truck.plate || "TRUCK OUT";

        // Remove from parked vehicles and standby
        setParkedScaleOutVehicles((prev) => prev.filter((p) => p.no_pol !== targetPlate));
        if (standbyScaleOut && (standbyScaleOut.no_pol === targetPlate || !customTruck)) {
            setStandbyScaleOut(null);
        }

        const exitId = "exit-" + Date.now();
        const newExit = {
            id: exitId,
            plate: targetPlate,
            driver: truck.nama_driver || truck.driver || "Driver",
            vendor: truck.vendor || "-",
            item: truck.item || "-",
            status: "Check-Out Timbangan Selesai -> Gate Out",
            x: 472.5,
            y: 790,
            angle: 270, // facing left towards road
            opacity: 1
        };

        setExitingTrucks((prev) => [...prev, newExit]);

        const proxy = { progress: 0 };
        gsap.to(proxy, {
            progress: 1,
            duration: 6.5,
            ease: "power1.inOut",
            onUpdate: () => {
                const pathElem = checkoutPathRef.current;
                if (!pathElem) return;
                const totalLen = pathElem.getTotalLength();
                const curLen = proxy.progress * totalLen;
                const p1 = pathElem.getPointAtLength(curLen);
                const nextLen = Math.min(curLen + 2, totalLen);
                const p2 = pathElem.getPointAtLength(nextLen);

                let angle = 270;
                if (p2.x !== p1.x || p2.y !== p1.y) {
                    angle = Math.atan2(p2.y - p1.y, p2.x - p1.x) * (180 / Math.PI) + 90;
                }

                // Smoothly fade out as it arrives at Gate Out (last 18% of travel)
                let op = 1;
                if (proxy.progress > 0.8) {
                    op = Math.max(0, 1 - (proxy.progress - 0.8) / 0.2);
                }

                setExitingTrucks((prev) =>
                    prev.map((t) =>
                        t.id === exitId
                            ? { ...t, x: p1.x, y: p1.y, angle: angle, opacity: op }
                            : t
                    )
                );
            },
            onComplete: () => {
                // Vehicle arrives at gate out and is completely removed!
                setExitingTrucks((prev) => prev.filter((t) => t.id !== exitId));

                // Re-arm demo standby after 12s if scale out is completely empty
                setTimeout(() => {
                    setParkedScaleOutVehicles((prev) => {
                        if (prev.length === 0) {
                            setStandbyScaleOut({
                                id: "standby-scale2",
                                no_pol: "B 9040 TEX",
                                nama_driver: "Yusup Marjuki",
                                vendor: "PT Mitra Logistik",
                                item: "BONGKARAN SELESAI",
                                status: "timbangan_out",
                                current_location_name: "Timbangan Scale 2 (Scale Out)",
                                target_location_name: "Gate Out",
                                no_spb: "SPB-2026-0902",
                                no_antrian: "OUT-04"
                            });
                        }
                        return prev;
                    });
                }, 12000);
            }
        });
    };

    // Fetch Live Data from Backend APIs
    const fetchData = async () => {
        setLoading(true);
        try {
            const [vehicleRes, parkirRes] = await Promise.allSettled([
                axios.get("/dashboard/vehicle/data"),
                axios.get("/dashboard/vehicle/kantong-parkir-data")
            ]);

            if (vehicleRes.status === "fulfilled" && vehicleRes.value?.data) {
                const txs = vehicleRes.value.data.transactions || [];
                setTransactions(txs);

                const inTransitPlates = new Set(transitToTimbanganTrucks.map((t) => t.plate));
                const inTransitParkirPlates = new Set(transitParkirToDockTrucks.map((t) => t.plate));

                // Status Transition Detection:
                if (initialFetchDoneRef.current) {
                    txs.forEach((currTx) => {
                        const plate = currTx.no_pol || currTx.no_polisi;
                        const prev = prevTxMapRef.current.get(currTx.id);
                        const currStatus = (currTx.status || "").toLowerCase();
                        const isTimbanganOut =
                            currStatus === "timbangan_out" ||
                            currStatus.includes("timbangan_out") ||
                            currStatus === "scale_out";

                        const currStarted =
                            Boolean(currTx.start_loading_time) ||
                            currTx.unloading_status === "process" ||
                            currStatus === "bongkar_wfg" ||
                            currStatus === "muat_wfg";

                        if (prev) {
                            const prevStatus = prev.status;
                            const wasNotTimbanganOut =
                                prevStatus !== "timbangan_out" &&
                                !prevStatus.includes("timbangan_out") &&
                                prevStatus !== "scale_out";

                            // Transisi 1: Selesai Bongkar/Muat -> timbangan_out (Jalan ke Timbangan)
                            if (isTimbanganOut && wasNotTimbanganOut && !inTransitPlates.has(plate)) {
                                let origin = "wfg";
                                const combined = `${prevStatus} ${prev.target} ${prev.current}`.toLowerCase();
                                if (combined.includes("wrm")) origin = "wrm";
                                else if (combined.includes("wpm")) origin = "wpm";
                                else origin = "wfg";

                                triggerDockToTimbanganAnimation(currTx, origin);
                            }

                            // Transisi 2: Action "Mulai Bongkar/Muat" (start_loading_time terisi dari sebelumnya null/pending)
                            // Truk mulai bergerak dari Kantong Parkir ke Dock WFG/SMU
                            if (currStarted && !prev.isStarted && !inTransitParkirPlates.has(plate)) {
                                const combined = `${currStatus} ${currTx.target_location_name || ""}`.toLowerCase();
                                let dest = "wfg_smu";
                                if (combined.includes("wrm")) dest = "wrm";
                                else if (combined.includes("wpm")) dest = "wpm";

                                triggerParkirToDockAnimation(currTx, dest);
                            }
                        }
                    });

                    // Auto-detect vehicles that just checked out from timbangan_out
                    const currentTimbanganOut = txs.filter((t) => {
                        const st = (t.status || "").toLowerCase();
                        return st === "timbangan_out" || st.includes("timbangan_out");
                    });

                    if (prevTimbanganOutRef.current.length > 0) {
                        prevTimbanganOutRef.current.forEach((prevTx) => {
                            const stillThere = currentTimbanganOut.some((c) => c.id === prevTx.id);
                            if (!stillThere) {
                                // Checked out! Launch GSAP exit animation to Gate Out
                                triggerCheckoutExitAnimation(prevTx);
                            }
                        });
                    }
                    prevTimbanganOutRef.current = currentTimbanganOut;
                } else {
                    initialFetchDoneRef.current = true;
                    prevTimbanganOutRef.current = txs.filter((t) => {
                        const st = (t.status || "").toLowerCase();
                        return st === "timbangan_out" || st.includes("timbangan_out");
                    });
                }

                // Update prev transactions map
                const newMap = new Map();
                txs.forEach((tx) => {
                    const st = (tx.status || "").toLowerCase();
                    const isStarted =
                        Boolean(tx.start_loading_time) ||
                        tx.unloading_status === "process" ||
                        st === "bongkar_wfg" ||
                        st === "muat_wfg";

                    newMap.set(tx.id, {
                        status: st,
                        target: (tx.target_location_name || "").toLowerCase(),
                        current: (tx.current_location_name || "").toLowerCase(),
                        no_pol: tx.no_pol || tx.no_polisi,
                        isStarted: isStarted,
                        start_loading_time: tx.start_loading_time
                    });
                });
                prevTxMapRef.current = newMap;
            }

            if (parkirRes.status === "fulfilled" && parkirRes.value?.data?.data) {
                setParkingZones(parkirRes.value.data.data);
                setParkingSummary(parkirRes.value.data.summary_all || null);
            }
            setLastSync(new Date());
        } catch (error) {
            console.error("Error fetching live view data:", error);
        } finally {
            setLoading(false);
        }
    };

    // Real-Time Event Sync via Laravel Echo (Reverb)
    useEffect(() => {
        if (typeof window !== "undefined" && window.Echo) {
            const channel = window.Echo.channel("vehicle-tracking");
            channel.listen(".vehicle.updated", (data) => {
                const status = (data.status || "").toLowerCase();
                const msg = (data.message || "").toLowerCase();

                if (status === "timbangan_out" || status.includes("timbangan_out")) {
                    let origin = "wfg";
                    if (msg.includes("wrm")) origin = "wrm";
                    else if (msg.includes("wpm")) origin = "wpm";
                    else if (msg.includes("wfg") || msg.includes("smu")) origin = "wfg";

                    triggerDockToTimbanganAnimation({
                        id: data.transaction_id || Date.now(),
                        no_pol: data.no_pol,
                        status: "timbangan_out",
                        message: data.message
                    }, origin);
                } else if (
                    msg.includes("mulai proses") ||
                    msg.includes("mulai muat") ||
                    msg.includes("mulai bongkar") ||
                    data.start_loading_time
                ) {
                    // Action Mulai Bongkar/Muat di SMU / WFG / WRM / WPM
                    let dest = "wfg_smu";
                    if (msg.includes("wrm") || status.includes("wrm")) dest = "wrm";
                    else if (msg.includes("wpm") || status.includes("wpm")) dest = "wpm";

                    triggerParkirToDockAnimation({
                        id: data.transaction_id || Date.now(),
                        no_pol: data.no_pol,
                        status: data.status,
                        message: data.message
                    }, dest);
                }
                fetchData();
            });

            return () => {
                channel.stopListening(".vehicle.updated");
            };
        }
    }, []);

    useEffect(() => {
        fetchData();
        if (!autoRefresh) return;
        const interval = setInterval(fetchData, 8000);
        return () => clearInterval(interval);
    }, [autoRefresh]);

    // Categorize Vehicles by Plant Locations
    // Penting: Truk yang diarahkan ke SMU/WFG dan belum 'Mulai Bongkar/Muat' (start_loading_time null)
    // TETAP berada di Kantong Parkir, baru masuk ke Dock setelah aksi Mulai Bongkar/Muat!
    const vehiclesByArea = useMemo(() => {
        const areaMap = {
            wfg_smu: [],
            wpm: [],
            wrm: [],
            timbangan: [],
            timbangan_in: [],
            timbangan_out: [],
            pos_security: [],
            parkir: []
        };

        transactions.forEach((tx) => {
            const status = (tx.status || "").toLowerCase();
            const currLoc = (tx.current_location_name || "").toLowerCase();
            const targetLoc = (tx.target_location_name || "").toLowerCase();

            // Syarat status sedang aktif bongkar/muat di dock
            const isStartedLoading =
                Boolean(tx.start_loading_time) ||
                tx.unloading_status === "process" ||
                status === "bongkar_wfg" ||
                status === "muat_wfg";

            // 1. Timbangan has HIGHEST precedence over targetLoc
            if (
                status === "timbangan_out" ||
                status.includes("timbangan_out") ||
                status === "scale_out"
            ) {
                areaMap.timbangan_out.push(tx);
                areaMap.timbangan.push(tx);
            } else if (
                status === "timbangan_in" ||
                status.includes("timbangan_in") ||
                status === "scale_in" ||
                (status.includes("timbangan") && !status.includes("out")) ||
                currLoc.includes("timbangan")
            ) {
                areaMap.timbangan_in.push(tx);
                areaMap.timbangan.push(tx);
            } else if (
                status.includes("check_in") ||
                status === "gate_in" ||
                currLoc.includes("pos") ||
                currLoc.includes("security")
            ) {
                areaMap.pos_security.push(tx);
            } else if (
                status === "wfg" ||
                status === "smu" ||
                status === "bongkar_wfg" ||
                status === "muat_wfg" ||
                currLoc.includes("wfg") ||
                currLoc.includes("smu")
            ) {
                if (isStartedLoading) {
                    areaMap.wfg_smu.push(tx);
                } else {
                    areaMap.parkir.push(tx);
                }
            } else if (status === "wpm" || currLoc.includes("wpm")) {
                if (isStartedLoading) {
                    areaMap.wpm.push(tx);
                } else {
                    areaMap.parkir.push(tx);
                }
            } else if (
                status === "wrm_bongkar" ||
                status === "sampling" ||
                status === "qc" ||
                currLoc.includes("wrm")
            ) {
                if (status === "wrm_bongkar" || isStartedLoading) {
                    areaMap.wrm.push(tx);
                } else {
                    areaMap.parkir.push(tx);
                }
            } else if (targetLoc.includes("wfg") && status === "loading") {
                if (isStartedLoading) {
                    areaMap.wfg_smu.push(tx);
                } else {
                    areaMap.parkir.push(tx);
                }
            } else if (targetLoc.includes("wpm") && status === "loading") {
                if (isStartedLoading) {
                    areaMap.wpm.push(tx);
                } else {
                    areaMap.parkir.push(tx);
                }
            } else if (targetLoc.includes("wrm") && status === "loading") {
                if (isStartedLoading) {
                    areaMap.wrm.push(tx);
                } else {
                    areaMap.parkir.push(tx);
                }
            } else {
                areaMap.parkir.push(tx);
            }
        });

        return areaMap;
    }, [transactions]);

    // Active truck parked on Scale 2 (Scale Out Platform)
    // Exclude any trucks currently driving in transit towards Timbangan
    const activeScaleOutVehicle = useMemo(() => {
        const inTransitPlates = new Set(transitToTimbanganTrucks.map((t) => t.plate));
        const liveScaleOut = vehiclesByArea.timbangan_out.filter(
            (tx) => !inTransitPlates.has(tx.no_pol) && !inTransitPlates.has(tx.no_polisi)
        );

        const allParked = [...parkedScaleOutVehicles, ...liveScaleOut];
        const valid = allParked.filter((t) => !inTransitPlates.has(t.no_pol));
        if (valid.length > 0) {
            return valid[0];
        }
        return standbyScaleOut;
    }, [vehiclesByArea.timbangan_out, transitToTimbanganTrucks, parkedScaleOutVehicles, standbyScaleOut]);

    // Additional trucks waiting in queue behind Scale 2 (if multiple trucks finished dock)
    const queueScaleOutVehicles = useMemo(() => {
        const inTransitPlates = new Set(transitToTimbanganTrucks.map((t) => t.plate));
        const liveScaleOut = vehiclesByArea.timbangan_out.filter(
            (tx) => !inTransitPlates.has(tx.no_pol) && !inTransitPlates.has(tx.no_polisi)
        );

        const allParked = [...parkedScaleOutVehicles, ...liveScaleOut];
        const valid = allParked.filter((t) => !inTransitPlates.has(t.no_pol));
        if (valid.length > 1) {
            return valid.slice(1);
        }
        return [];
    }, [vehiclesByArea.timbangan_out, transitToTimbanganTrucks, parkedScaleOutVehicles]);

    // Helper to get zone data from API matching the zone configuration
    const getZoneData = (code) => {
        if (!parkingZones || !Array.isArray(parkingZones)) return null;
        const cleanCode = (code || "").trim().toLowerCase();
        return (
            parkingZones.find((z) => {
                const zCode = (z.kode_zona || "").trim().toLowerCase();
                const zName = (z.nama_zona || "").trim().toLowerCase();
                return zCode === cleanCode || zCode.includes(cleanCode) || zName.includes(cleanCode);
            }) || null
        );
    };

    // Layout configuration for the 6 Parking blocks in the user's diagram
    const parkingBlocksConfig = useMemo(() => [
        // Column 1 Top: BAS01 (17 slots)
        {
            id: "block-1",
            column: 1,
            title: "Parkir BAS01",
            x: 180,
            y: 340,
            width: 400,
            height: 155,
            zones: [{ code: "BAS01", name: "Zona BAS01", capacity: 17, cols: 9 }]
        },
        // Column 1 Mid: BAS04 (16 slots)
        {
            id: "block-2",
            column: 1,
            title: "Parkir BAS04",
            x: 180,
            y: 520,
            width: 400,
            height: 155,
            zones: [{ code: "BAS04", name: "Zona BAS04", capacity: 16, cols: 8 }]
        },
        // Column 2 Top: BAS02 (7 slots)
        {
            id: "block-3",
            column: 2,
            title: "Parkir BAS02",
            x: 660,
            y: 340,
            width: 360,
            height: 155,
            zones: [{ code: "BAS02", name: "Zona BAS02", capacity: 7, cols: 7 }]
        },
        // Column 2 Mid: BAS07 (6 slots)
        {
            id: "block-4",
            column: 2,
            title: "Parkir BAS07",
            x: 660,
            y: 520,
            width: 360,
            height: 155,
            zones: [{ code: "BAS07", name: "Zona BAS07", capacity: 6, cols: 6 }]
        },
        // Column 3 Top: BAS05 (5 slots) & BAS08 (3 slots)
        {
            id: "block-5",
            column: 3,
            title: "Parkir BAS05 & BAS08",
            x: 1090,
            y: 340,
            width: 410,
            height: 155,
            zones: [
                { code: "BAS05", name: "Zona BAS05", capacity: 5, cols: 5 },
                { code: "BAS08", name: "Zona BAS08", capacity: 3, cols: 3 }
            ]
        },
        // Column 3 Mid: BAS03 (4 slots) & BAS06 (2 slots)
        {
            id: "block-6",
            column: 3,
            title: "Parkir BAS03 & BAS06",
            x: 1090,
            y: 520,
            width: 410,
            height: 155,
            zones: [
                { code: "BAS03", name: "Zona BAS03", capacity: 4, cols: 4 },
                { code: "BAS06", name: "Zona BAS06", capacity: 2, cols: 2 }
            ]
        }
    ], []);

    // Pan & Zoom Handlers
    const handleZoomIn = () => setZoomLevel((prev) => Math.min(prev + 0.15, 2.2));
    const handleZoomOut = () => setZoomLevel((prev) => Math.max(prev - 0.15, 0.5));
    const handleResetZoom = () => {
        setZoomLevel(0.95);
        setPanOffset({ x: 0, y: 0 });
    };

    const handleMouseDown = (e) => {
        if (e.target.closest("button") || e.target.closest(".interactive-element")) return;
        setIsPanning(true);
        setPanStart({ x: e.clientX - panOffset.x, y: e.clientY - panOffset.y });
    };

    const handleMouseMove = (e) => {
        if (!isPanning) return;
        setPanOffset({
            x: e.clientX - panStart.x,
            y: e.clientY - panStart.y
        });
    };

    const handleMouseUp = () => setIsPanning(false);

    // Toggle Fullscreen
    const toggleFullscreen = () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch((err) => console.log(err));
            setIsFullscreen(true);
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
                setIsFullscreen(false);
            }
        }
    };

    return (
        <div className="relative w-full h-[calc(100vh-80px)] flex flex-col bg-[#070b12] text-slate-100 select-none overflow-hidden rounded-xl border border-slate-800 shadow-2xl">
            {/* ── TOP HUD HEADER BAR ── */}
            <div className="z-20 flex flex-wrap items-center justify-between gap-3 px-5 py-3 bg-slate-900/95 backdrop-blur-md border-b border-slate-800/80">
                {/* Brand & Live Status */}
                <div className="flex items-center gap-3">
                    <div className="p-2 rounded-lg bg-blue-600/20 border border-blue-500/30 text-blue-400">
                        <Compass className="w-5 h-5 animate-spin-slow" />
                    </div>
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-base md:text-lg font-extrabold tracking-tight bg-gradient-to-r from-white via-slate-200 to-blue-400 bg-clip-text text-transparent">
                                LIVE VIEW DENAH PABRIK & KENDARAAN
                            </h1>
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                GSAP Animated
                            </span>
                        </div>
                        <p className="text-xs text-slate-400">
                            WFG & SMU • WPM • WRM • Kantong Parkir 8 Zona (60 Slot) • Timbangan & Pos Security
                        </p>
                    </div>
                </div>

                {/* Building / Area Filters */}
                <div className="hidden lg:flex items-center gap-1.5 p-1 bg-slate-950/80 rounded-lg border border-slate-800">
                    {[
                        { key: "all", label: "Semua Titik", count: transactions.length },
                        { key: "wfg_smu", label: "WFG & SMU", count: vehiclesByArea.wfg_smu.length },
                        { key: "wpm", label: "Gedung WPM", count: vehiclesByArea.wpm.length },
                        { key: "wrm", label: "Gedung WRM", count: vehiclesByArea.wrm.length },
                        { key: "timbangan", label: "Timbangan", count: (vehiclesByArea.timbangan.length || (standbyScaleOut ? 1 : 0)) },
                        { key: "parkir", label: "Kantong Parkir", count: parkingSummary?.total_terisi ?? vehiclesByArea.parkir.length }
                    ].map((f) => (
                        <button
                            key={f.key}
                            onClick={() => setActiveFilter(f.key)}
                            className={`px-3 py-1.5 rounded-md text-xs font-medium transition-all ${
                                activeFilter === f.key
                                    ? "bg-blue-600 text-white shadow-md shadow-blue-500/20"
                                    : "text-slate-400 hover:text-slate-200 hover:bg-slate-800/50"
                            }`}
                        >
                            {f.label}
                            <span className="ml-1.5 px-1.5 py-0.2 rounded-full text-[10px] bg-black/40 font-mono">
                                {f.count}
                            </span>
                        </button>
                    ))}
                </div>

                {/* KPI HUD Stats & Controls */}
                <div className="flex items-center gap-3">
                    {/* Parking Capacity Mini Counter */}
                    <div className="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-slate-950/70 border border-slate-800 rounded-lg text-xs">
                        <span className="text-slate-400">Parkir:</span>
                        <strong className="text-purple-400 font-mono">
                            {parkingSummary?.total_terisi || 0}/60 Slot
                        </strong>
                        <span className="text-slate-500 font-mono">
                            ({parkingSummary?.global_occupancy_percentage || 0}%)
                        </span>
                    </div>

                    {/* Clock */}
                    <div className="flex items-center gap-1.5 px-3 py-1.5 bg-slate-950/90 border border-slate-800 rounded-lg text-xs font-mono text-cyan-400">
                        <Clock className="w-3.5 h-3.5 text-slate-400" />
                        <span>{currentTime}</span>
                    </div>

                    {/* Trigger Mulai Muat: Parkir -> Dock WFG/SMU Test Button */}
                    <button
                        onClick={() => triggerDemoParkirToDock()}
                        className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/30 transition shadow-sm"
                        title="Simulasi: Action Mulai Bongkar/Muat -> Truk bergerak dari Kantong Parkir ke Dock WFG/SMU"
                    >
                        <Play className="w-3.5 h-3.5 text-emerald-400" />
                        <span className="hidden xl:inline">Test Mulai Muat &rarr; Dock</span>
                    </button>

                    {/* Trigger Selesai Muat -> Timbangan Out Test Button */}
                    <button
                        onClick={() => triggerDemoDockToTimbangan()}
                        className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/40 hover:bg-amber-500/30 transition shadow-sm"
                        title="Simulasi: Truk Selesai Bongkar/Muat di Dock -> Bergerak ke Timbangan Out"
                    >
                        <Truck className="w-3.5 h-3.5 text-amber-400" />
                        <span className="hidden xl:inline">Test Selesai Muat &rarr; Timbangan</span>
                    </button>

                    {/* Trigger Checkout Test Button */}
                    <button
                        onClick={() => triggerCheckoutExitAnimation()}
                        className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/30 transition shadow-sm"
                        title="Simulasikan Truk Scale Out Checkout -> Jalan ke Gate Out -> Hilang"
                    >
                        <LogOut className="w-3.5 h-3.5 text-emerald-400" />
                        <span className="hidden xl:inline">Test Checkout Scale Out</span>
                    </button>

                    {/* Simulation Toggle */}
                    <button
                        onClick={() => setSimulationActive(!simulationActive)}
                        className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all ${
                            simulationActive
                                ? "bg-blue-500/20 text-blue-400 border-blue-500/40 hover:bg-blue-500/30"
                                : "bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700"
                        }`}
                        title={simulationActive ? "Jeda Animasi Jalur GSAP" : "Jalankan Animasi Jalur GSAP"}
                    >
                        {simulationActive ? (
                            <>
                                <Pause className="w-3.5 h-3.5" />
                                <span className="hidden md:inline">GSAP Aktif</span>
                            </>
                        ) : (
                            <>
                                <Play className="w-3.5 h-3.5" />
                                <span className="hidden md:inline">Mulai</span>
                            </>
                        )}
                    </button>

                    {/* Refresh Button */}
                    <button
                        onClick={fetchData}
                        disabled={loading}
                        className="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 transition"
                        title="Perbarui Data"
                    >
                        <RefreshCw className={`w-4 h-4 ${loading ? "animate-spin text-blue-400" : ""}`} />
                    </button>

                    {/* Fullscreen */}
                    <button
                        onClick={toggleFullscreen}
                        className="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 transition"
                        title="Toggle Fullscreen"
                    >
                        {isFullscreen ? <Minimize2 className="w-4 h-4" /> : <Maximize2 className="w-4 h-4" />}
                    </button>
                </div>
            </div>

            {/* ── MAIN INTERACTIVE MAP CANVAS CONTAINER ── */}
            <div
                className="relative flex-1 w-full h-full overflow-hidden cursor-grab active:cursor-grabbing bg-[#070b12]"
                onMouseDown={handleMouseDown}
                onMouseMove={handleMouseMove}
                onMouseUp={handleMouseUp}
            >
                {/* Floating Map Zoom / Pan Controls (Bottom Right) */}
                <div className="absolute bottom-5 right-5 z-20 flex flex-col gap-1.5 p-1.5 bg-slate-900/90 backdrop-blur-md rounded-xl border border-slate-800 shadow-xl">
                    <button
                        onClick={handleZoomIn}
                        className="p-2 hover:bg-slate-800 text-slate-300 rounded-lg transition"
                        title="Perbesar Denah (+)"
                    >
                        <ZoomIn className="w-4 h-4" />
                    </button>
                    <button
                        onClick={handleZoomOut}
                        className="p-2 hover:bg-slate-800 text-slate-300 rounded-lg transition"
                        title="Perkecil Denah (-)"
                    >
                        <ZoomOut className="w-4 h-4" />
                    </button>
                    <button
                        onClick={handleResetZoom}
                        className="p-2 hover:bg-slate-800 text-slate-300 rounded-lg transition"
                        title="Reset Tampilan (Fit)"
                    >
                        <RotateCcw className="w-4 h-4" />
                    </button>
                </div>

                {/* Floating Map Legend (Bottom Left) */}
                <div className="absolute bottom-5 left-5 z-20 hidden md:flex items-center gap-4 px-4 py-2 bg-slate-900/90 backdrop-blur-md rounded-xl border border-slate-800/80 text-xs shadow-xl">
                    <div className="flex items-center gap-1.5">
                        <span className="w-3 h-3 rounded bg-emerald-500/30 border border-emerald-500"></span>
                        <span className="text-slate-300">WFG & SMU</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="w-3 h-3 rounded bg-indigo-500/30 border border-indigo-500"></span>
                        <span className="text-slate-300">WPM</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="w-3 h-3 rounded bg-amber-500/30 border border-amber-500"></span>
                        <span className="text-slate-300">WRM</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="w-3 h-3 rounded bg-teal-500/30 border border-teal-500"></span>
                        <span className="text-slate-300">Scale Out (Timbangan)</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="w-3 h-3 rounded bg-purple-500/30 border border-purple-500"></span>
                        <span className="text-slate-300">Kantong Parkir (60 Slot)</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                        <span className="text-cyan-300 font-semibold">Truk Bergerak (GSAP)</span>
                    </div>
                </div>

                {/* ── THE 2D SVG BLUEPRINT DENAH ── */}
                <div
                    className="w-full h-full flex items-center justify-center transition-transform duration-75 origin-center"
                    style={{
                        transform: `translate(${panOffset.x}px, ${panOffset.y}px) scale(${zoomLevel})`
                    }}
                >
                    <svg
                        ref={svgRef}
                        viewBox="0 0 1600 1200"
                        className="w-full h-full max-w-[1600px] max-h-[1200px]"
                        style={{ filter: "drop-shadow(0 25px 50px rgba(0,0,0,0.7))" }}
                    >
                        <defs>
                            {/* Grid Blueprint Pattern */}
                            <pattern id="gridBlueprint" width="30" height="30" patternUnits="userSpaceOnUse">
                                <rect width="30" height="30" fill="#090e17" />
                                <path d="M 30 0 L 0 0 0 30" fill="none" stroke="#162032" strokeWidth="0.8" />
                            </pattern>

                            {/* Industrial Asphalt Pattern */}
                            <pattern id="asphaltRoad" width="20" height="20" patternUnits="userSpaceOnUse">
                                <rect width="20" height="20" fill="#131b28" />
                                <circle cx="10" cy="10" r="0.6" fill="#1e293b" />
                            </pattern>

                            {/* Corrugated Roof Pattern */}
                            <pattern id="roofPattern" width="12" height="12" patternUnits="userSpaceOnUse">
                                <rect width="12" height="12" fill="#161f30" />
                                <line x1="0" y1="0" x2="0" y2="12" stroke="#24334a" strokeWidth="2" />
                            </pattern>

                            {/* Drop Shadow Blur Filter */}
                            <filter id="shadowBlur" x="-30%" y="-30%" width="160%" height="160%">
                                <feGaussianBlur stdDeviation="3" />
                            </filter>

                            {/* Headlight Beam Gradient */}
                            <linearGradient id="headlightBeam" x1="0%" y1="100%" x2="0%" y2="0%">
                                <stop offset="0%" stopColor="#fef08a" stopOpacity="0.8" />
                                <stop offset="70%" stopColor="#fef08a" stopOpacity="0.2" />
                                <stop offset="100%" stopColor="#fef08a" stopOpacity="0" />
                            </linearGradient>
                        </defs>

                        {/* ── 1. BACKGROUND & SITE PERIMETER ── */}
                        <rect x="20" y="20" width="1560" height="1160" rx="20" fill="url(#gridBlueprint)" stroke="#1e293b" strokeWidth="2" />

                        {/* ── 2. INTERNAL ROAD NETWORK (ASPHALT & DOUBLE LINES) ── */}
                        <g id="road-network">
                            {/* Horizontal Road Base (Below Docks) */}
                            <rect x="75" y="260" width="1460" height="60" rx="6" fill="url(#asphaltRoad)" stroke="#334155" strokeWidth="1" />
                            <line x1="75" y1="262" x2="1535" y2="262" stroke="#475569" strokeWidth="2" />
                            <line x1="75" y1="318" x2="1535" y2="318" stroke="#475569" strokeWidth="2" />
                            <line x1="85" y1="290" x2="1525" y2="290" stroke="#facc15" strokeWidth="2" strokeDasharray="14,14" opacity="0.8" />

                            {/* Left Vertical Road Base (From Gate In/Out to Top) */}
                            <rect x="75" y="260" width="60" height="880" rx="6" fill="url(#asphaltRoad)" stroke="#334155" strokeWidth="1" />
                            <line x1="77" y1="260" x2="77" y2="1140" stroke="#475569" strokeWidth="2.5" />
                            <line x1="133" y1="318" x2="133" y2="1140" stroke="#475569" strokeWidth="2.5" />
                            <line x1="105" y1="290" x2="105" y2="1130" stroke="#facc15" strokeWidth="2" strokeDasharray="14,14" opacity="0.8" />

                            {/* Middle Road Aisle 1 (Between Column 1 and Column 2) */}
                            <rect x="600" y="318" width="50" height="420" rx="4" fill="url(#asphaltRoad)" stroke="#334155" strokeWidth="1" />
                            <line x1="602" y1="318" x2="602" y2="738" stroke="#475569" strokeWidth="2" />
                            <line x1="648" y1="318" x2="648" y2="738" stroke="#475569" strokeWidth="2" />
                            <line x1="625" y1="320" x2="625" y2="730" stroke="#94a3b8" strokeWidth="1.5" strokeDasharray="10,10" opacity="0.6" />

                            {/* Middle Road Aisle 2 (Between Column 2 and Column 3) */}
                            <rect x="1030" y="318" width="50" height="420" rx="4" fill="url(#asphaltRoad)" stroke="#334155" strokeWidth="1" />
                            <line x1="1032" y1="318" x2="1032" y2="738" stroke="#475569" strokeWidth="2" />
                            <line x1="1078" y1="318" x2="1078" y2="738" stroke="#475569" strokeWidth="2" />
                            <line x1="1055" y1="320" x2="1055" y2="730" stroke="#94a3b8" strokeWidth="1.5" strokeDasharray="10,10" opacity="0.6" />

                            {/* Connecting Turnaround Road at Bottom of Parking Columns */}
                            <rect x="180" y="685" width="1320" height="35" rx="4" fill="url(#asphaltRoad)" opacity="0.3" />

                            {/* Driveway from Scale Out into Left Vertical Road */}
                            <rect x="135" y="765" width="50" height="50" fill="url(#asphaltRoad)" opacity="0.6" />
                        </g>

                        {/* ── 3. SVG PATHS FOR GSAP TRUCK MOTION ── */}
                        <g id="gsap-truck-routes">
                            <path ref={pathRef1} d={routePaths.r1} fill="none" stroke="#06b6d4" strokeWidth="2" strokeDasharray="8,6" opacity="0.5" />
                            <path ref={pathRef2} d={routePaths.r2} fill="none" stroke="#f59e0b" strokeWidth="2" strokeDasharray="8,6" opacity="0.5" />
                            <path ref={pathRef3} d={routePaths.r3} fill="none" stroke="#6366f1" strokeWidth="2" strokeDasharray="8,6" opacity="0.5" />
                            <path ref={pathRef4} d={routePaths.r4} fill="none" stroke="#10b981" strokeWidth="2" strokeDasharray="8,6" opacity="0.5" />
                            <path ref={pathRef5} d={routePaths.r5} fill="none" stroke="#ef4444" strokeWidth="2" strokeDasharray="8,6" opacity="0.5" />

                            {/* Direct Routes from Docks to Scale 2 (Timbangan Out) */}
                            <path
                                ref={pathDockWRMRef}
                                d={routePaths.rDockWRM}
                                fill="none"
                                stroke="#f59e0b"
                                strokeWidth="2"
                                strokeDasharray="6,4"
                                opacity={transitToTimbanganTrucks.some((t) => t.origin === "wrm") ? "0.85" : "0.2"}
                            />
                            <path
                                ref={pathDockWPMRef}
                                d={routePaths.rDockWPM}
                                fill="none"
                                stroke="#6366f1"
                                strokeWidth="2"
                                strokeDasharray="6,4"
                                opacity={transitToTimbanganTrucks.some((t) => t.origin === "wpm") ? "0.85" : "0.2"}
                            />
                            <path
                                ref={pathDockWFGRef}
                                d={routePaths.rDockWFG}
                                fill="none"
                                stroke="#10b981"
                                strokeWidth="2"
                                strokeDasharray="6,4"
                                opacity={transitToTimbanganTrucks.some((t) => t.origin === "wfg") ? "0.85" : "0.2"}
                            />

                            {/* Route Checkout Exit (Scale Out -> Left Road -> Gate Out) */}
                            <path
                                ref={checkoutPathRef}
                                d={routePaths.rCheckout}
                                fill="none"
                                stroke="#14b8a6"
                                strokeWidth="2.5"
                                strokeDasharray="6,4"
                                opacity={exitingTrucks.length > 0 ? "0.9" : "0.35"}
                            />
                        </g>

                        {/* ── 4. GATE IN & GATE OUT (BOTTOM LEFT CORNER) ── */}
                        <g transform="translate(45, 1100)" className="interactive-element cursor-pointer" onClick={() => setSelectedEntity({ type: "gate", name: "Gate In & Gate Out (Main Entrance)" })}>
                            <rect x="0" y="0" width="120" height="55" rx="8" fill="#111827" stroke="#3b82f6" strokeWidth="2" />
                            <text x="60" y="24" textAnchor="middle" fill="#60a5fa" fontSize="10" fontWeight="900" letterSpacing="0.5">
                                GATE IN & OUT
                            </text>
                            <text x="60" y="42" textAnchor="middle" fill="#94a3b8" fontSize="8.5" fontWeight="600">
                                Pos Utama Pabrik
                            </text>
                            {/* Boom barrier */}
                            <line x1="120" y1="28" x2="145" y2="28" stroke="#ef4444" strokeWidth="4" strokeDasharray="6,4" />
                        </g>

                        {/* ── 5. TOP ROW 3 MAIN BUILDINGS & DOCKS ── */}

                        {/* 🏢 5A. GEDUNG WFG, SMU (KIRI ATAS) */}
                        <g
                            transform="translate(180, 50)"
                            className={`interactive-element cursor-pointer transition-all ${
                                activeFilter === "wfg_smu" || activeFilter === "all" ? "opacity-100" : "opacity-40"
                            }`}
                            onClick={() => setSelectedEntity({
                                type: "building",
                                name: "GEDUNG WFG, SMU (Finished Goods & SMU)",
                                count: vehiclesByArea.wfg_smu.length,
                                vehicles: vehiclesByArea.wfg_smu
                            })}
                        >
                            <rect x="0" y="0" width="420" height="150" rx="8" fill="url(#roofPattern)" stroke="#10b981" strokeWidth="2" />
                            <rect x="20" y="15" width="380" height="28" rx="6" fill="#064e3b" stroke="#10b981" strokeWidth="1" />
                            <text x="210" y="34" textAnchor="middle" fill="#ffffff" fontSize="13" fontWeight="900" letterSpacing="0.5">
                                WFG, SMU
                            </text>
                            <text x="210" y="75" textAnchor="middle" fill="#a7f3d0" fontSize="10" fontWeight="700">
                                WAREHOUSE FINISHED GOODS & SMU
                            </text>
                            <rect x="150" y="95" width="120" height="22" rx="4" fill="#022c22" stroke="#10b981" strokeWidth="1" />
                            <text x="210" y="110" textAnchor="middle" fill="#34d399" fontSize="9.5" fontWeight="800">
                                {vehiclesByArea.wfg_smu.length} TRUK DI DOCK
                            </text>

                            {/* DOCK STRIP AT BOTTOM OF BUILDING */}
                            <g transform="translate(0, 150)">
                                <rect x="0" y="0" width="420" height="55" fill="#1e293b" stroke="#334155" strokeWidth="1.5" />
                                <rect x="15" y="8" width="80" height="18" rx="4" fill="#064e3b" />
                                <text x="55" y="21" textAnchor="middle" fill="#34d399" fontSize="9" fontWeight="900">
                                    DOCK
                                </text>

                                {/* 4 Dock Bays */}
                                {[0, 1, 2, 3].map((dockIdx) => {
                                    const bayX = 110 + dockIdx * 75;
                                    const v = vehiclesByArea.wfg_smu[dockIdx];
                                    const isInTransit = v && transitToTimbanganTrucks.some((t) => t.plate === v.no_pol);
                                    const isInParkirTransit = v && transitParkirToDockTrucks.some((t) => t.plate === v.no_pol);
                                    const showTruck = v && !isInTransit && !isInParkirTransit;

                                    return (
                                        <g key={dockIdx} transform={`translate(${bayX}, 8)`}>
                                            <rect
                                                x="0"
                                                y="0"
                                                width="65"
                                                height="40"
                                                rx="4"
                                                fill="#0f172a"
                                                stroke={showTruck ? "#10b981" : "#475569"}
                                                strokeWidth="1.2"
                                                strokeDasharray={showTruck ? "none" : "3,2"}
                                            />
                                            <text x="32.5" y="12" textAnchor="middle" fill="#64748b" fontSize="7.5" fontWeight="700">
                                                DOCK 0{dockIdx + 1}
                                            </text>
                                            {showTruck && (
                                                <g transform="translate(32.5, 24)">
                                                    <TopDownTruck
                                                        plate={v.no_pol}
                                                        status="wfg"
                                                        scale={0.55}
                                                        angle={0}
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            setSelectedEntity({
                                                                type: "vehicle",
                                                                data: v,
                                                                origin: "wfg",
                                                                dockIndex: dockIdx,
                                                                dockBayX: 180 + bayX + 32.5
                                                            });
                                                        }}
                                                    />
                                                </g>
                                            )}
                                        </g>
                                    );
                                })}
                            </g>
                        </g>

                        {/* 🏢 5B. GEDUNG WPM (TENGAH ATAS) */}
                        <g
                            transform="translate(660, 50)"
                            className={`interactive-element cursor-pointer transition-all ${
                                activeFilter === "wpm" || activeFilter === "all" ? "opacity-100" : "opacity-40"
                            }`}
                            onClick={() => setSelectedEntity({
                                type: "building",
                                name: "GEDUNG WPM (Packaging Material)",
                                count: vehiclesByArea.wpm.length,
                                vehicles: vehiclesByArea.wpm
                            })}
                        >
                            <rect x="0" y="0" width="360" height="150" rx="8" fill="url(#roofPattern)" stroke="#6366f1" strokeWidth="2" />
                            <rect x="20" y="15" width="320" height="28" rx="6" fill="#312e81" stroke="#6366f1" strokeWidth="1" />
                            <text x="180" y="34" textAnchor="middle" fill="#ffffff" fontSize="13" fontWeight="900" letterSpacing="0.5">
                                WPM
                            </text>
                            <text x="180" y="75" textAnchor="middle" fill="#c7d2fe" fontSize="10" fontWeight="700">
                                WAREHOUSE PACKAGING MATERIAL
                            </text>
                            <rect x="120" y="95" width="120" height="22" rx="4" fill="#1e1b4b" stroke="#6366f1" strokeWidth="1" />
                            <text x="180" y="110" textAnchor="middle" fill="#a5b4fc" fontSize="9.5" fontWeight="800">
                                {vehiclesByArea.wpm.length} TRUK DI DOCK
                            </text>

                            {/* DOCK STRIP */}
                            <g transform="translate(0, 150)">
                                <rect x="0" y="0" width="360" height="55" fill="#1e293b" stroke="#334155" strokeWidth="1.5" />
                                <rect x="15" y="8" width="70" height="18" rx="4" fill="#312e81" />
                                <text x="50" y="21" textAnchor="middle" fill="#a5b4fc" fontSize="9" fontWeight="900">
                                    DOCK
                                </text>

                                {[0, 1, 2].map((dockIdx) => {
                                    const bayX = 100 + dockIdx * 82;
                                    const v = vehiclesByArea.wpm[dockIdx];
                                    const isInTransit = v && transitToTimbanganTrucks.some((t) => t.plate === v.no_pol);
                                    const showTruck = v && !isInTransit;

                                    return (
                                        <g key={dockIdx} transform={`translate(${bayX}, 8)`}>
                                            <rect
                                                x="0"
                                                y="0"
                                                width="70"
                                                height="40"
                                                rx="4"
                                                fill="#0f172a"
                                                stroke={showTruck ? "#6366f1" : "#475569"}
                                                strokeWidth="1.2"
                                                strokeDasharray={showTruck ? "none" : "3,2"}
                                            />
                                            <text x="35" y="12" textAnchor="middle" fill="#64748b" fontSize="7.5" fontWeight="700">
                                                DOCK 0{dockIdx + 1}
                                            </text>
                                            {showTruck && (
                                                <g transform="translate(35, 24)">
                                                    <TopDownTruck
                                                        plate={v.no_pol}
                                                        status="wpm"
                                                        scale={0.55}
                                                        angle={0}
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            setSelectedEntity({
                                                                type: "vehicle",
                                                                data: v,
                                                                origin: "wpm",
                                                                dockIndex: dockIdx,
                                                                dockBayX: 660 + bayX + 35
                                                            });
                                                        }}
                                                    />
                                                </g>
                                            )}
                                        </g>
                                    );
                                })}
                            </g>
                        </g>

                        {/* 🏢 5C. GEDUNG WRM (KANAN ATAS) */}
                        <g
                            transform="translate(1090, 50)"
                            className={`interactive-element cursor-pointer transition-all ${
                                activeFilter === "wrm" || activeFilter === "all" ? "opacity-100" : "opacity-40"
                            }`}
                            onClick={() => setSelectedEntity({
                                type: "building",
                                name: "GEDUNG WRM (Raw Material)",
                                count: vehiclesByArea.wrm.length,
                                vehicles: vehiclesByArea.wrm
                            })}
                        >
                            <rect x="0" y="0" width="410" height="150" rx="8" fill="url(#roofPattern)" stroke="#f59e0b" strokeWidth="2" />
                            <rect x="20" y="15" width="370" height="28" rx="6" fill="#78350f" stroke="#f59e0b" strokeWidth="1" />
                            <text x="205" y="34" textAnchor="middle" fill="#ffffff" fontSize="13" fontWeight="900" letterSpacing="0.5">
                                WRM
                            </text>
                            <text x="205" y="75" textAnchor="middle" fill="#fde68a" fontSize="10" fontWeight="700">
                                WAREHOUSE RAW MATERIAL
                            </text>
                            <rect x="145" y="95" width="120" height="22" rx="4" fill="#451a03" stroke="#f59e0b" strokeWidth="1" />
                            <text x="205" y="110" textAnchor="middle" fill="#fbbf24" fontSize="9.5" fontWeight="800">
                                {vehiclesByArea.wrm.length} TRUK DI DOCK
                            </text>

                            {/* DOCK STRIP */}
                            <g transform="translate(0, 150)">
                                <rect x="0" y="0" width="410" height="55" fill="#1e293b" stroke="#334155" strokeWidth="1.5" />
                                <rect x="15" y="8" width="75" height="18" rx="4" fill="#78350f" />
                                <text x="52.5" y="21" textAnchor="middle" fill="#fbbf24" fontSize="9" fontWeight="900">
                                    DOCK
                                </text>

                                {[0, 1, 2, 3].map((dockIdx) => {
                                    const bayX = 105 + dockIdx * 74;
                                    const v = vehiclesByArea.wrm[dockIdx];
                                    const isInTransit = v && transitToTimbanganTrucks.some((t) => t.plate === v.no_pol);
                                    const showTruck = v && !isInTransit;

                                    return (
                                        <g key={dockIdx} transform={`translate(${bayX}, 8)`}>
                                            <rect
                                                x="0"
                                                y="0"
                                                width="65"
                                                height="40"
                                                rx="4"
                                                fill="#0f172a"
                                                stroke={showTruck ? "#f59e0b" : "#475569"}
                                                strokeWidth="1.2"
                                                strokeDasharray={showTruck ? "none" : "3,2"}
                                            />
                                            <text x="32.5" y="12" textAnchor="middle" fill="#64748b" fontSize="7.5" fontWeight="700">
                                                DOCK 0{dockIdx + 1}
                                            </text>
                                            {showTruck && (
                                                <g transform="translate(32.5, 24)">
                                                    <TopDownTruck
                                                        plate={v.no_pol}
                                                        status="wrm"
                                                        scale={0.55}
                                                        angle={0}
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            setSelectedEntity({
                                                                type: "vehicle",
                                                                data: v,
                                                                origin: "wrm",
                                                                dockIndex: dockIdx,
                                                                dockBayX: 1090 + bayX + 32.5
                                                            });
                                                        }}
                                                    />
                                                </g>
                                            )}
                                        </g>
                                    );
                                })}
                            </g>
                        </g>

                        {/* ── 6. AREA PARKIR (6 BLOK SESUAI SKETSA GAMBAR DENGAN DATA 8 ZONA) ── */}
                        {parkingBlocksConfig.map((block) => {
                            const isFiltered = activeFilter === "parkir" || activeFilter === "all";

                            return (
                                <g
                                    key={block.id}
                                    transform={`translate(${block.x}, ${block.y})`}
                                    className={`interactive-element transition-opacity ${
                                        isFiltered ? "opacity-100" : "opacity-35"
                                    }`}
                                >
                                    <rect
                                        x="0"
                                        y="0"
                                        width={block.width}
                                        height={block.height}
                                        rx="8"
                                        fill="#0f172a"
                                        stroke="#8b5cf6"
                                        strokeWidth="1.5"
                                    />

                                    {/* Block Header Pill */}
                                    <rect x="8" y="6" width={block.width - 16} height="20" rx="4" fill="#2e1065" />
                                    <text
                                        x={block.width / 2}
                                        y="19"
                                        textAnchor="middle"
                                        fill="#c084fc"
                                        fontSize="9.5"
                                        fontWeight="900"
                                        letterSpacing="0.5"
                                    >
                                        PARKIR ({block.zones.map((z) => `${z.code}: ${z.capacity} Slot`).join(" • ")})
                                    </text>

                                    {/* Render Zones Inside this Block */}
                                    {block.zones.map((zoneCfg, zIdx) => {
                                        const zoneData = getZoneData(zoneCfg.code);
                                        const slots = zoneData?.slots || [];
                                        const zoneY = 32 + zIdx * 58;

                                        return (
                                            <g key={zoneCfg.code} transform={`translate(8, ${zoneY})`}>
                                                {block.zones.length > 1 && (
                                                    <text x="4" y="-2" fill="#a78bfa" fontSize="7.5" fontWeight="700">
                                                        {zoneCfg.name.toUpperCase()} ({zoneCfg.capacity} SLOT)
                                                    </text>
                                                )}

                                                {/* Slots Grid */}
                                                <g>
                                                    {Array.from({ length: zoneCfg.capacity }).map((_, sIdx) => {
                                                        const slotNum = sIdx + 1;
                                                        const defaultSlotCode = `${zoneCfg.code}-${String(slotNum).padStart(2, "0")}`;

                                                        // Flexible matching:
                                                        // 1. By nomor_slot (1, 2...)
                                                        // 2. By exact kode_slot (BAS01-01, A-01...)
                                                        // 3. By numeric suffix in kode_slot (e.g. A-01 -> 1)
                                                        // 4. Fallback to direct array index slots[sIdx]
                                                        const sData =
                                                            slots.find((s) => {
                                                                if (!s) return false;
                                                                if (s.nomor_slot === slotNum) return true;
                                                                if (s.kode_slot === defaultSlotCode) return true;
                                                                const parts = (s.kode_slot || "").split("-");
                                                                if (parts.length > 1) {
                                                                    const num = parseInt(parts[1], 10);
                                                                    if (!isNaN(num) && num === slotNum) return true;
                                                                }
                                                                return false;
                                                            }) || slots[sIdx] || null;

                                                        const isOccupied =
                                                            sData?.status_slot === "terisi" ||
                                                            sData?.is_tersedia === false ||
                                                            !!sData?.active_vehicle;

                                                        const truckPlate =
                                                            sData?.active_vehicle?.no_polisi ||
                                                            sData?.active_vehicle?.no_pol ||
                                                            (isOccupied ? "TERISI" : "");

                                                        const slotCode = sData?.kode_slot || defaultSlotCode;
                                                        const slotLabel = sData?.kode_slot || String(slotNum);

                                                        const slotWidth = Math.floor((block.width - 24) / zoneCfg.cols);
                                                        const col = sIdx % zoneCfg.cols;
                                                        const row = Math.floor(sIdx / zoneCfg.cols);
                                                        const slotX = col * slotWidth;
                                                        const slotY = row * 52;

                                                        return (
                                                            <g
                                                                key={sIdx}
                                                                transform={`translate(${slotX}, ${slotY})`}
                                                                className="cursor-pointer group"
                                                                onClick={(e) => {
                                                                    e.stopPropagation();
                                                                    setSelectedEntity({
                                                                        type: "slot",
                                                                        zone: `${zoneCfg.name} (${zoneData?.nama_zona || zoneCfg.code})`,
                                                                        code: slotCode,
                                                                        isOccupied,
                                                                        vehicle: sData?.active_vehicle || null
                                                                    });
                                                                }}
                                                            >
                                                                <rect
                                                                    x="1"
                                                                    y="1"
                                                                    width={slotWidth - 2}
                                                                    height="48"
                                                                    rx="3"
                                                                    fill={isOccupied ? "#1e1b4b" : "#090d16"}
                                                                    stroke={isOccupied ? "#a855f7" : "#334155"}
                                                                    strokeWidth={isOccupied ? "1.5" : "1"}
                                                                    strokeDasharray={isOccupied ? "none" : "3,2"}
                                                                />
                                                                <text
                                                                    x={(slotWidth - 2) / 2}
                                                                    y="10"
                                                                    textAnchor="middle"
                                                                    fill={isOccupied ? "#c084fc" : "#64748b"}
                                                                    fontSize="6.5"
                                                                    fontWeight="800"
                                                                >
                                                                    {slotLabel}
                                                                </text>

                                                                {isOccupied && (
                                                                    <g transform={`translate(${(slotWidth - 2) / 2}, 28)`}>
                                                                        <TopDownTruck
                                                                            plate={truckPlate}
                                                                            status="parkir"
                                                                            scale={0.42}
                                                                            angle={0}
                                                                        />
                                                                    </g>
                                                                )}
                                                            </g>
                                                        );
                                                    })}
                                                </g>
                                            </g>
                                        );
                                    })}
                                </g>
                            );
                        })}

                        {/* ── 7. TIMBANGAN (WEIGHBRIDGE) - SCALE 1 (INBOUND) & SCALE 2 (OUTBOUND / SCALE OUT) ── */}
                        <g
                            transform="translate(180, 710)"
                            className="interactive-element cursor-pointer group"
                            onClick={() => setSelectedEntity({
                                type: "timbangan",
                                name: "Area Timbangan (Weighbridge In & Out)",
                                count: (vehiclesByArea.timbangan.length || (activeScaleOutVehicle ? 1 : 0)),
                                vehicles: vehiclesByArea.timbangan
                            })}
                        >
                            <rect x="0" y="0" width="400" height="135" rx="8" fill="#0f172a" stroke="#06b6d4" strokeWidth="1.5" />
                            <rect x="15" y="8" width="370" height="22" rx="4" fill="#082f49" />
                            <text x="200" y="23" textAnchor="middle" fill="#38bdf8" fontSize="11" fontWeight="900" letterSpacing="0.5">
                                TIMBANGAN (WEIGHBRIDGE)
                            </text>

                            {/* Scale Platform 1 (Inbound) */}
                            <rect x="25" y="42" width="165" height="75" rx="4" fill="#090e17" stroke="#06b6d4" strokeWidth="1.2" strokeDasharray="4,2" />
                            <text x="107.5" y="58" textAnchor="middle" fill="#7dd3fc" fontSize="8" fontWeight="800">SCALE 1 (INBOUND)</text>

                            {/* Scale 1 Inbound Truck */}
                            {vehiclesByArea.timbangan_in.length > 0 && (
                                <g transform="translate(107.5, 82)">
                                    <TopDownTruck
                                        plate={vehiclesByArea.timbangan_in[0]?.no_pol || "TIMBANG IN"}
                                        status="timbangan_in"
                                        scale={0.65}
                                        angle={90}
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setSelectedEntity({
                                                type: "vehicle",
                                                data: vehiclesByArea.timbangan_in[0]
                                            });
                                        }}
                                    />
                                </g>
                            )}

                            {/* Scale Platform 2 (Outbound / Scale Out) */}
                            <rect
                                x="210"
                                y="42"
                                width="165"
                                height="75"
                                rx="4"
                                fill="#090e17"
                                stroke={activeScaleOutVehicle ? "#14b8a6" : "#06b6d4"}
                                strokeWidth={activeScaleOutVehicle ? "1.8" : "1.2"}
                                strokeDasharray={activeScaleOutVehicle ? "none" : "4,2"}
                            />
                            <text x="292.5" y="58" textAnchor="middle" fill={activeScaleOutVehicle ? "#2dd4bf" : "#7dd3fc"} fontSize="8" fontWeight="800">
                                SCALE 2 (OUTBOUND / SCALE OUT)
                            </text>

                            {/* Scale Out Status Tag Indicator */}
                            {activeScaleOutVehicle && (
                                <g transform="translate(292.5, 42)">
                                    <rect x="-48" y="-9" width="96" height="13" rx="3" fill="#134e4a" stroke="#14b8a6" strokeWidth="1" />
                                    <text x="0" y="0" textAnchor="middle" fill="#5eead4" fontSize="6.5" fontWeight="900" letterSpacing="0.4">
                                        TERPARKIR (SCALE OUT)
                                    </text>
                                </g>
                            )}

                            {/* Truk terparkir di depan/atas platform Scale Out (Timbangan Out) */}
                            {activeScaleOutVehicle && (
                                <g
                                    transform="translate(292.5, 82)"
                                    className="cursor-pointer group"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        setSelectedEntity({
                                            type: "scale_out_vehicle",
                                            name: `Truk Scale Out - ${activeScaleOutVehicle.no_pol}`,
                                            data: activeScaleOutVehicle
                                        });
                                    }}
                                >
                                    <TopDownTruck
                                        plate={activeScaleOutVehicle.no_pol}
                                        status="timbangan_out"
                                        scale={0.65}
                                        angle={270} // Menghadap ke kiri (arah jalan keluar)
                                        isMoving={false}
                                        isSelected={selectedEntity?.data?.no_pol === activeScaleOutVehicle.no_pol}
                                    />
                                </g>
                            )}

                            {/* Antrian Truk Menunggu di Belakang Scale 2 (Jika lebih dari 1 truk berstatus timbangan_out) */}
                            {queueScaleOutVehicles.map((qTruck, qIdx) => {
                                const qX = 292.5 - (qIdx + 1) * 62;
                                return (
                                    <g
                                        key={qTruck.id || qIdx}
                                        transform={`translate(${qX}, 82)`}
                                        className="cursor-pointer group"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setSelectedEntity({
                                                type: "scale_out_vehicle",
                                                name: `Antrian Scale Out #${qIdx + 1} - ${qTruck.no_pol}`,
                                                data: qTruck
                                            });
                                        }}
                                    >
                                        <rect x="-24" y="-34" width="48" height="11" rx="2" fill="#0f172a" stroke="#14b8a6" strokeWidth="0.8" />
                                        <text x="0" y="-26" textAnchor="middle" fill="#2dd4bf" fontSize="5.5" fontWeight="700">
                                            ANTRIAN #{qIdx + 1}
                                        </text>
                                        <TopDownTruck
                                            plate={qTruck.no_pol}
                                            status="timbangan_out"
                                            scale={0.58}
                                            angle={270}
                                            isMoving={false}
                                            isSelected={selectedEntity?.data?.no_pol === qTruck.no_pol}
                                        />
                                    </g>
                                );
                            })}
                        </g>

                        {/* ── 8. POS SECURITY (KOLOM 1 PALING BAWAH SESUAI SKETSA) ── */}
                        <g
                            transform="translate(180, 870)"
                            className="interactive-element cursor-pointer group"
                            onClick={() => setSelectedEntity({
                                type: "pos_security",
                                name: "Pos Security & Access Control Masuk/Keluar",
                                count: vehiclesByArea.pos_security.length,
                                vehicles: vehiclesByArea.pos_security
                            })}
                        >
                            <rect x="0" y="0" width="400" height="130" rx="8" fill="#0f172a" stroke="#3b82f6" strokeWidth="1.5" />
                            <rect x="15" y="8" width="370" height="22" rx="4" fill="#1e3a8a" />
                            <text x="200" y="23" textAnchor="middle" fill="#93c5fd" fontSize="11" fontWeight="900" letterSpacing="0.5">
                                POS SECURITY (SATPAM)
                            </text>

                            {/* Sub Inspection Rooms */}
                            <g transform="translate(25, 42)">
                                <rect x="0" y="0" width="165" height="70" rx="4" fill="#090e17" stroke="#334155" strokeWidth="1" />
                                <text x="82.5" y="25" textAnchor="middle" fill="#94a3b8" fontSize="8.5" fontWeight="700">RUANG SATPAM</text>
                                <text x="82.5" y="45" textAnchor="middle" fill="#60a5fa" fontSize="7.5">CCTV & Gate Control</text>
                            </g>
                            <g transform="translate(210, 42)">
                                <rect x="0" y="0" width="165" height="70" rx="4" fill="#090e17" stroke="#334155" strokeWidth="1" />
                                <text x="82.5" y="25" textAnchor="middle" fill="#94a3b8" fontSize="8.5" fontWeight="700">INSPEKSI FISIK</text>
                                <text x="82.5" y="45" textAnchor="middle" fill="#34d399" fontSize="7.5">Validasi SPB & Dokumen</text>
                            </g>

                            {/* Connection Lane to the Left Road */}
                            <line x1="0" y1="65" x2="-47" y2="65" stroke="#3b82f6" strokeWidth="2.5" strokeDasharray="4,2" />
                        </g>

                        {/* ── 9. GSAP ANIMATED TRUCKS MOVING ALONG THE ROADS ── */}
                        {simulationActive &&
                            simulatedTrucksData.map((truck) => {
                                const pos = truckPos[truck.id] || { x: 105, y: 1120, angle: 0 };
                                return (
                                    <g
                                        key={truck.id}
                                        transform={`translate(${pos.x}, ${pos.y})`}
                                        className="interactive-element cursor-pointer"
                                        onClick={() =>
                                            setSelectedEntity({
                                                type: "moving_vehicle",
                                                data: {
                                                    no_pol: truck.plate,
                                                    nama_driver: truck.driver,
                                                    vendor: truck.vendor,
                                                    item: truck.item,
                                                    status: truck.status,
                                                    is_moving: true
                                                }
                                            })
                                        }
                                    >
                                        <TopDownTruck
                                            plate={truck.plate}
                                            status={truck.routePathId === "r2" ? "wrm" : truck.routePathId === "r3" ? "wpm" : truck.routePathId === "r4" ? "wfg" : "timbangan"}
                                            angle={pos.angle}
                                            scale={0.65}
                                            isMoving={true}
                                            isSelected={selectedEntity?.data?.no_pol === truck.plate}
                                        />
                                    </g>
                                );
                            })}

                        {/* ── 9A. TRUK YANG SEDANG BERJALAN DARI KANTONG PARKIR KE DOCK (MULAI BONGKAR/MUAT) ── */}
                        {transitParkirToDockTrucks.map((truck) => (
                            <g
                                key={truck.id}
                                transform={`translate(${truck.x}, ${truck.y})`}
                                className="interactive-element cursor-pointer"
                                onClick={() =>
                                    setSelectedEntity({
                                        type: "moving_vehicle",
                                        data: {
                                            no_pol: truck.plate,
                                            nama_driver: truck.driver,
                                            vendor: truck.vendor,
                                            item: truck.item,
                                            status: truck.status,
                                            current_location_name: "Jalur Utama Pabrik (Menuju Dock)",
                                            target_location_name: "Dock " + (truck.dest === "wfg_smu" ? "WFG / SMU" : truck.dest.toUpperCase()),
                                            is_moving: true
                                        }
                                    })
                                }
                            >
                                <TopDownTruck
                                    plate={truck.plate}
                                    status={truck.dest === "wfg_smu" ? "wfg" : truck.dest}
                                    angle={truck.angle}
                                    scale={0.68}
                                    isMoving={true}
                                    isSelected={selectedEntity?.data?.no_pol === truck.plate}
                                />
                            </g>
                        ))}

                        {/* ── 9B. TRUK YANG SEDANG BERJALAN DARI DOCK KE TIMBANGAN OUT (SELESAI BONGKAR/MUAT) ── */}
                        {transitToTimbanganTrucks.map((truck) => (
                            <g
                                key={truck.id}
                                transform={`translate(${truck.x}, ${truck.y})`}
                                className="interactive-element cursor-pointer"
                                onClick={() =>
                                    setSelectedEntity({
                                        type: "moving_vehicle",
                                        data: {
                                            no_pol: truck.plate,
                                            nama_driver: truck.driver,
                                            vendor: truck.vendor,
                                            item: truck.item,
                                            status: "Selesai Bongkar/Muat -> Menuju Timbangan Out",
                                            is_moving: true
                                        }
                                    })
                                }
                            >
                                <TopDownTruck
                                    plate={truck.plate}
                                    status="timbangan_out"
                                    angle={truck.angle}
                                    scale={0.68}
                                    isMoving={true}
                                    isSelected={selectedEntity?.data?.no_pol === truck.plate}
                                />
                            </g>
                        ))}

                        {/* ── 10. TRUK CHECKOUT YANG SEDANG BERJALAN DARI TIMBANGAN KE GATE OUT LALU HILANG ── */}
                        {exitingTrucks.map((truck) => (
                            <g
                                key={truck.id}
                                transform={`translate(${truck.x}, ${truck.y})`}
                                style={{ opacity: truck.opacity }}
                                className="interactive-element cursor-pointer transition-opacity"
                                onClick={() =>
                                    setSelectedEntity({
                                        type: "moving_vehicle",
                                        data: {
                                            no_pol: truck.plate,
                                            nama_driver: truck.driver,
                                            vendor: truck.vendor,
                                            item: truck.item,
                                            status: "Check-Out Timbangan Selesai -> Gate Out",
                                            is_moving: true
                                        }
                                    })
                                }
                            >
                                <TopDownTruck
                                    plate={truck.plate}
                                    status="gate_out"
                                    angle={truck.angle}
                                    scale={0.68}
                                    isMoving={true}
                                    isSelected={selectedEntity?.data?.no_pol === truck.plate}
                                />
                            </g>
                        ))}
                    </svg>
                </div>
            </div>

            {/* ── DETAIL DRAWER / INSPECTOR MODAL ── */}
            {selectedEntity && (
                <div className="absolute top-16 right-5 z-30 w-96 max-w-[calc(100vw-40px)] bg-slate-900/95 backdrop-blur-xl border border-slate-700/80 rounded-2xl shadow-2xl p-5 animate-in fade-in slide-in-from-right duration-200">
                    {/* Header */}
                    <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <div className="flex items-center gap-2">
                            <div className="p-2 rounded-lg bg-blue-500/20 text-blue-400">
                                <Truck className="w-4 h-4" />
                            </div>
                            <div>
                                <h3 className="text-xs uppercase tracking-wider text-slate-400 font-bold">
                                    Inspector Detail
                                </h3>
                                <p className="text-sm font-extrabold text-white">
                                    {selectedEntity.name || selectedEntity.data?.no_pol || selectedEntity.code || "Detail Kendaraan"}
                                </p>
                            </div>
                        </div>
                        <button
                            onClick={() => setSelectedEntity(null)}
                            className="p-1.5 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white transition"
                        >
                            <X className="w-4 h-4" />
                        </button>
                    </div>

                    {/* Content based on entity type */}
                    {selectedEntity.type === "vehicle" || selectedEntity.type === "moving_vehicle" || selectedEntity.type === "scale_out_vehicle" ? (
                        <div className="space-y-3 text-xs">
                            {/* License Plate Banner */}
                            <div className="p-3 bg-slate-950 rounded-xl border border-teal-500/30 flex items-center justify-between">
                                <span className="font-mono text-lg font-black text-teal-300 tracking-wider">
                                    {selectedEntity.data?.no_pol || selectedEntity.data?.no_polisi || "-"}
                                </span>
                                <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30 uppercase">
                                    {selectedEntity.data?.status || "Scale Out"}
                                </span>
                            </div>

                            <div className="grid grid-cols-2 gap-2">
                                <div className="p-2.5 bg-slate-950/60 rounded-lg border border-slate-800">
                                    <div className="text-[10px] text-slate-400">Driver</div>
                                    <div className="font-semibold text-slate-200 truncate">
                                        {selectedEntity.data?.nama_driver || "N/A"}
                                    </div>
                                    <div className="text-[10px] text-slate-500">{selectedEntity.data?.no_hp_driver || "-"}</div>
                                </div>
                                <div className="p-2.5 bg-slate-950/60 rounded-lg border border-slate-800">
                                    <div className="text-[10px] text-slate-400">Vendor</div>
                                    <div className="font-semibold text-slate-200 truncate">
                                        {selectedEntity.data?.vendor || "N/A"}
                                    </div>
                                </div>
                            </div>

                            <div className="p-2.5 bg-slate-950/60 rounded-lg border border-slate-800 space-y-1">
                                <div className="flex justify-between">
                                    <span className="text-slate-400">Material / Muatan:</span>
                                    <span className="font-bold text-amber-400">{selectedEntity.data?.item || "-"}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-slate-400">No. SPB:</span>
                                    <span className="font-mono text-slate-300">{selectedEntity.data?.no_spb || "-"}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-slate-400">Status Posisi:</span>
                                    <span className="font-bold text-teal-400 font-mono">
                                        {selectedEntity.data?.status?.includes("timbangan_out")
                                            ? "Terparkir di Platform Scale Out"
                                            : selectedEntity.data?.is_moving
                                            ? "Sedang Bergerak Menuju Gate Out"
                                            : "Di Area Titik"}
                                    </span>
                                </div>
                            </div>

                            <div className="p-2.5 bg-slate-950/60 rounded-lg border border-slate-800 space-y-1">
                                <div className="flex justify-between">
                                    <span className="text-slate-400">Lokasi Sekarang:</span>
                                    <span className="font-semibold text-slate-300">
                                        {selectedEntity.data?.current_location_name || "Platform Timbangan Scale 2"}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-slate-400">Target Tujuan:</span>
                                    <span className="font-semibold text-blue-300">
                                        {selectedEntity.data?.target_location_name || "Gate Out"}
                                    </span>
                                </div>
                            </div>

                            {/* Action Button: Mulai Bongkar/Muat -> Jalan dari Parkir ke Dock WFG/SMU */}
                            {(!selectedEntity.data?.start_loading_time &&
                                selectedEntity.data?.unloading_status !== "process" &&
                                !selectedEntity.data?.status?.includes("timbangan") &&
                                !selectedEntity.data?.status?.includes("gate_out") &&
                                !selectedEntity.data?.is_moving) && (
                                <button
                                    onClick={() => {
                                        const isSmu =
                                            selectedEntity.data?.status === "smu" ||
                                            selectedEntity.data?.target_location_name?.toLowerCase()?.includes("smu") ||
                                            selectedEntity.origin === "smu";
                                        const dest = isSmu ? "smu" : "wfg";
                                        handleStartLoading(selectedEntity.data?.id, dest, selectedEntity.data);
                                        setSelectedEntity(null);
                                    }}
                                    className="w-full mt-2 py-2.5 px-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20 transition text-xs"
                                >
                                    <Play className="w-4 h-4" />
                                    Mulai Bongkar/Muat (Jalan Parkir &rarr; Dock)
                                </button>
                            )}

                            {/* Action Button: Selesai Bongkar/Muat -> Jalan ke Timbangan Out! */}
                            {(selectedEntity.data?.start_loading_time ||
                                selectedEntity.data?.unloading_status === "process" ||
                                selectedEntity.origin) &&
                                !selectedEntity.data?.status?.includes("timbangan") &&
                                !selectedEntity.data?.status?.includes("gate_out") &&
                                !selectedEntity.data?.is_moving && (
                                <button
                                    onClick={() => {
                                        triggerDockToTimbanganAnimation(
                                            selectedEntity.data,
                                            selectedEntity.origin || "wfg"
                                        );
                                        setSelectedEntity(null);
                                    }}
                                    className="w-full mt-2 py-2.5 px-3 bg-gradient-to-r from-amber-600 to-teal-600 hover:from-amber-500 hover:to-teal-500 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20 transition text-xs"
                                >
                                    <Truck className="w-4 h-4" />
                                    Selesai Bongkar/Muat (Jalan ke Timbangan Out)
                                </button>
                            )}

                            {/* Action Button: Checkout & Jalan ke Gate Out lalu Hilang */}
                            {(selectedEntity.type === "scale_out_vehicle" || selectedEntity.data?.status?.includes("timbangan")) && (
                                <button
                                    onClick={() => {
                                        triggerCheckoutExitAnimation(selectedEntity.data);
                                        setSelectedEntity(null);
                                    }}
                                    className="w-full mt-2 py-2.5 px-3 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-500 hover:to-emerald-500 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-teal-500/20 transition text-xs"
                                >
                                    <LogOut className="w-4 h-4" />
                                    Check-Out Timbangan (Jalan ke Gate Out)
                                </button>
                            )}
                        </div>
                    ) : selectedEntity.type === "slot" ? (
                        <div className="space-y-3 text-xs">
                            <div className="p-3 bg-slate-950 rounded-xl border border-purple-500/30 flex items-center justify-between">
                                <div>
                                    <div className="text-[10px] text-purple-400 uppercase font-bold">{selectedEntity.zone}</div>
                                    <div className="font-mono text-lg font-black text-white">{selectedEntity.code}</div>
                                </div>
                                <span className={`px-2.5 py-1 rounded-full text-xs font-bold ${
                                    selectedEntity.isOccupied
                                        ? "bg-purple-500/20 text-purple-300 border border-purple-500/30"
                                        : "bg-emerald-500/20 text-emerald-400 border border-emerald-500/30"
                                }`}>
                                    {selectedEntity.isOccupied ? "TERISI" : "KOSONG"}
                                </span>
                            </div>

                            {selectedEntity.isOccupied && selectedEntity.vehicle ? (
                                <div className="p-3 bg-slate-950/60 rounded-lg border border-slate-800 space-y-2">
                                    <div className="font-mono text-sm font-bold text-blue-400">
                                        {selectedEntity.vehicle.no_polisi || selectedEntity.vehicle.no_pol}
                                    </div>
                                    <div className="text-slate-300">Driver: {selectedEntity.vehicle.nama_driver || "-"}</div>
                                    {selectedEntity.vehicle.no_hp_driver && (
                                        <div className="text-slate-400 text-[11px]">No. HP: {selectedEntity.vehicle.no_hp_driver}</div>
                                    )}
                                    <div className="text-slate-300">Vendor / Info: {selectedEntity.vehicle.vendor || selectedEntity.vehicle.catatan || "-"}</div>
                                    {selectedEntity.vehicle.item && (
                                        <div className="text-slate-300">Muatan: {selectedEntity.vehicle.item}</div>
                                    )}
                                    {selectedEntity.vehicle.durasi_parkir && (
                                        <div className="text-amber-400 font-medium">Durasi: {selectedEntity.vehicle.durasi_parkir}</div>
                                    )}
                                    {selectedEntity.vehicle.waktu_masuk && (
                                        <div className="text-slate-400 text-[11px]">Waktu Masuk: {selectedEntity.vehicle.waktu_masuk}</div>
                                    )}

                                    {/* Action button langsung dari slot parkir untuk memanggil ke dock */}
                                    <button
                                        onClick={() => {
                                            const vData = selectedEntity.vehicle;
                                            const isSmu = (vData.catatan || "").toLowerCase().includes("smu") || (vData.no_pol || "").includes("CPK");
                                            const dest = isSmu ? "smu" : "wfg";
                                            handleStartLoading(vData.id, dest, vData);
                                            setSelectedEntity(null);
                                        }}
                                        className="w-full mt-3 py-2.5 px-3 bg-gradient-to-r from-purple-600 to-emerald-600 hover:from-purple-500 hover:to-emerald-500 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-purple-500/20 transition text-xs"
                                    >
                                        <Play className="w-4 h-4" />
                                        Mulai Bongkar/Muat (Panggil Truk ke Dock)
                                    </button>
                                </div>
                            ) : (
                                <p className="text-slate-400 text-center py-4">
                                    Slot parkir ini sedang kosong dan siap digunakan kendaraan inbound.
                                </p>
                            )}
                        </div>
                    ) : (
                        // Building / Area summary
                        <div className="space-y-3 text-xs">
                            <div className="p-3 bg-slate-950 rounded-xl border border-blue-500/30">
                                <div className="text-slate-400">Total Truk di Titik Ini:</div>
                                <div className="text-2xl font-black text-blue-400 font-mono">
                                    {selectedEntity.count ?? selectedEntity.vehicles?.length ?? 0} Unit
                                </div>
                            </div>

                            <div className="space-y-2 max-h-56 overflow-y-auto pr-1">
                                {selectedEntity.vehicles && selectedEntity.vehicles.length > 0 ? (
                                    selectedEntity.vehicles.map((v, i) => (
                                        <div
                                            key={i}
                                            className="p-2 bg-slate-950/60 hover:bg-slate-800/80 rounded-lg border border-slate-800 flex items-center justify-between cursor-pointer transition"
                                            onClick={() => setSelectedEntity({ type: "vehicle", data: v })}
                                        >
                                            <div>
                                                <div className="font-mono font-bold text-slate-200">{v.no_pol}</div>
                                                <div className="text-[10px] text-slate-400">{v.item} • {v.vendor}</div>
                                            </div>
                                            <ChevronRight className="w-4 h-4 text-slate-500" />
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center py-4 text-slate-500">
                                        Tidak ada truk yang sedang aktif di area ini saat ini.
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
