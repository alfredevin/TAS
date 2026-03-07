<?php include './../config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.marker.slideto@0.2.0/Leaflet.Marker.SlideTo.js"></script>
</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <style>
        /* General Page & Map Tweaks */
        body {
            background-color: #f4f6f9;
        }

        .map-container {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            background: #fff;
            padding: 5px;
            position: relative;
        }

        #map {
            height: 72vh;
            border-radius: 12px;
            z-index: 1;
        }

        /* Interactive "Focus All" Button on Map */
        .btn-focus-all {
            position: absolute;
            bottom: 20px;
            left: 20px;
            z-index: 1000;
            background: #fff;
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: bold;
            color: #800000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transition: 0.3s;
        }

        .btn-focus-all:hover {
            background: #800000;
            color: #fff;
            transform: translateY(-2px);
        }

        /* Sidebar & Search Styling */
        .sidebar-card {
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            border: none;
            background: #fff;
        }

        .sidebar-header {
            background: #fff;
            border-bottom: 1px solid #f0f2f5;
            border-radius: 16px 16px 0 0 !important;
            padding: 20px 24px 15px 24px;
        }

        .search-box {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 8px 15px;
            display: flex;
            align-items: center;
            margin-top: 15px;
            border: 1px solid transparent;
            transition: 0.3s;
        }

        .search-box:focus-within {
            border-color: #800000;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        }

        .search-box input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            margin-left: 10px;
            font-size: 13px;
        }

        /* Interactive Traveler Cards */
        .traveler-card {
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            border-bottom: 1px solid #f8f9fa;
            padding: 16px 24px;
            background-color: transparent;
            position: relative;
        }

        .traveler-card:hover {
            background-color: #f8fafc;
        }

        .traveler-card.active-card {
            background-color: #fff1f2;
            border-left: 4px solid #800000 !important;
        }

        .pulse-live {
            animation: pulseLive 2s infinite;
        }

        @keyframes pulseLive {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(40, 167, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }

        /* Modern App-Style Timeline (Shopee/Grab Style) */
        .history-timeline {
            border-left: 2px dashed #cbd5e1;
            margin-left: 25px;
            padding-left: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .history-item {
            margin-bottom: 30px;
            position: relative;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .history-item::before {
            content: '';
            position: absolute;
            left: -34px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #800000;
            box-shadow: 0 0 0 3px #fff;
        }

        .history-item:first-child::before {
            background: #800000;
            border-color: #800000;
            box-shadow: 0 0 0 3px #fee2e2;
            animation: pulseLive 2s infinite;
        }

        .history-time {
            font-size: 12px;
            font-weight: 700;
            color: #800000;
            margin-bottom: 3px;
            display: block;
            background: #fff1f2;
            padding: 3px 10px;
            border-radius: 20px;
            width: fit-content;
        }

        .history-action {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 5px;
            margin-bottom: 2px;
        }

        .history-location {
            font-size: 12px;
            color: #475569;
            display: flex;
            align-items: flex-start;
            background: #f8fafc;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-top: 5px;
        }

        /* Scrollbar */
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }

        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }

        /* Custom Profile Marker Styles */
        .custom-profile-marker {
            background: transparent;
            border: none;
        }

        .marker-photo-container {
            width: 45px;
            height: 45px;
            background: #fff;
            border-radius: 50%;
            border: 3px solid #800000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .marker-photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .marker-initials {
            background: #800000;
            color: #fff;
            font-weight: bold;
            font-size: 16px;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .marker-pointer {
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-top: 10px solid #800000;
        }
    </style>

    <main id="main" class="main">
        <div class="pagetitle d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold" style="color: #1a202c; font-size: 1.5rem;">Live Fleet Track
                    <span class="badge bg-success pulse-live ms-2 fw-normal rounded-pill px-3 py-2"
                        style="font-size: 11px;">
                        <i class="bi bi-record-circle me-1"></i> LIVE
                    </span>
                </h1>
                <nav>
                    <ol class="breadcrumb mb-0 mt-1">
                        <li class="breadcrumb-item"><a href="index.php" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item active" style="color: #800000; font-weight: 500;">Monitor Travel</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="row align-items-stretch">
                <div class="col-xl-8 col-lg-7 mb-4">
                    <div class="map-container">
                        <div id="map"></div>
                        <button class="btn-focus-all" onclick="focusAllMarkers()" title="View All Deployments">
                            <i class="bi bi-arrows-fullscreen me-2"></i>View All
                        </button>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-5">
                    <div class="card sidebar-card" style="height: 73vh; display: flex; flex-direction: column;">
                        <div class="sidebar-header">
                            <h5 class="card-title m-0 p-0 fw-bold d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-people text-maroon me-2"></i>On The Field</span>
                                <span id="traveler-count" class="badge rounded-pill"
                                    style="background: #f1f5f9; color: #475569; font-size: 14px; padding: 6px 12px;">0</span>
                            </h5>
                            <div class="search-box">
                                <i class="bi bi-search text-muted"></i>
                                <input type="text" id="searchInput" placeholder="Search employee or destination..."
                                    onkeyup="filterList()">
                            </div>
                        </div>

                        <div class="card-body p-0 custom-scroll" style="overflow-y: auto; flex-grow: 1;">
                            <div class="list-group list-group-flush" id="travelers-list">
                                <div class="text-center p-5 text-muted h-100 d-flex flex-column justify-content-center align-items-center"
                                    id="loading-state">
                                    <div class="spinner-border text-maroon mb-3"
                                        style="width: 2.5rem; height: 2.5rem; opacity: 0.5;" role="status"></div>
                                    <h6 class="fw-bold text-secondary mb-1">Locating Personnel...</h6>
                                    <small style="color: #94a3b8;">Establishing secure connection.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded-circle p-2 me-3 d-flex justify-content-center align-items-center"
                            style="width:45px; height:45px;">
                            <i class="bi bi-signpost-split text-maroon fs-4" style="color:#800000;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Travel Logbook</h5>
                            <small id="history-emp-name" class="text-muted fw-bold"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div id="history-loading" class="text-center py-5 d-none">
                        <div class="spinner-grow text-maroon" role="status"
                            style="width: 3rem; height: 3rem; opacity:0.2;"></div>
                        <p class="small text-muted mt-3 fw-bold">Translating GPS coordinates to address...</p>
                    </div>
                    <div id="history-content" class="history-timeline">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 pt-0 justify-content-center">
                    <small class="text-muted"><i class="bi bi-shield-lock me-1"></i> Data is logged securely via
                        GPS</small>
                </div>
            </div>
        </div>
    </div>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>

    <script>
        // 1. INITIALIZE MAP
        var map = L.map('map', { zoomControl: false }).setView([13.3858, 121.9563], 10);
        L.control.zoom({ position: 'topright' }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 20
        }).addTo(map);

        var redIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });
        var markers = {};
        var markerGroup = L.featureGroup().addTo(map);
        var allTravelersData = [];
        var addressCache = {};
        function createProfileMarker(picUrl, initials) {
            let htmlContent = picUrl && picUrl !== ''
                ? `<img src="${picUrl}" onerror="this.style.display='none'">`
                : `<div class="marker-initials">${initials}</div>`;

            return L.divIcon({
                className: 'custom-profile-marker',
                html: `
            <div class="marker-photo-container">${htmlContent}</div>
            <div class="marker-pointer"></div>
        `,
                iconSize: [45, 55],
                iconAnchor: [22, 55],
                popupAnchor: [0, -50]
            });
        }
        // 3. UTILITY FUNCTIONS
        function getInitials(name) {
            let initials = name.match(/\b\w/g) || [];
            return ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
        }

        async function getRealAddress(lat, lng) {
            try {
                let response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                let data = await response.json();
                // Kunin lang yung maikling address para hindi masyadong mahaba
                let parts = data.display_name.split(', ');
                return parts.slice(0, 3).join(', ') || `${lat}, ${lng}`;
            } catch (error) {
                return `${lat}, ${lng}`;
            }
        }

        // 4. MAP INTERACTIONS
        function focusAllMarkers() {
            if (Object.keys(markers).length > 0) {
                map.flyToBounds(markerGroup.getBounds(), { padding: [50, 50], duration: 1.5 });
            }
        }

        function focusMap(lat, lng, id) {
            // Interactive Highlight sa Sidebar
            document.querySelectorAll('.traveler-card').forEach(card => card.classList.remove('active-card'));
            let clickedCard = document.getElementById('card-' + id);
            if (clickedCard) clickedCard.classList.add('active-card');

            // Map Animation
            map.flyTo([lat, lng], 16, { animate: true, duration: 1.5 });
            if (markers[id]) {
                setTimeout(() => markers[id].openPopup(), 1200);
            }
        }

        // 5. SEARCH FILTER FUNCTION
        function filterList() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.querySelectorAll('.traveler-card');

            cards.forEach(card => {
                let text = card.innerText.toLowerCase();
                card.style.display = text.includes(input) ? "block" : "none";
            });
        }

        // 6. HISTORY MODAL INTERACTION
        function viewHistory(taId, empName, event) {
            if (event) event.stopPropagation();

            document.getElementById('history-emp-name').innerText = empName;
            document.getElementById('history-content').innerHTML = "";
            document.getElementById('history-loading').classList.remove('d-none');

            var modal = new bootstrap.Modal(document.getElementById('historyModal'));
            modal.show();

            fetch(`get_travel_history.php?ta_id=${taId}`)
                .then(res => res.json())
                .then(async data => {
                    if (data.status === 'success' && data.data.length > 0) {
                        // Reverse the array para pinakabago ang nasa taas
                        let logs = data.data.reverse();
                        let html = '';

                        for (let i = 0; i < logs.length; i++) {
                            let log = logs[i];
                            let timeFmt = new Date(log.logged_at).toLocaleString('en-US', {
                                month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                            });

                            let address = await getRealAddress(log.lat, log.lng);

                            html += `
                            <div class="history-item">
                                <span class="history-time"><i class="bi bi-clock-history me-1"></i> ${timeFmt}</span>
                                <div class="history-action">${log.action_name}</div>
                                <div class="history-location shadow-sm">
                                    <i class="bi bi-geo-alt-fill text-danger me-2 mt-1 fs-6"></i>
                                    <div>
                                        <strong class="d-block text-dark">${address}</strong>
                                        <small class="text-muted" style="font-size:10px;"><i class="bi bi-satellite me-1"></i>GPS: ${log.lat}, ${log.lng}</small>
                                    </div>
                                </div>
                            </div>`;
                        }

                        document.getElementById('history-loading').classList.add('d-none');
                        document.getElementById('history-content').innerHTML = html;
                    } else {
                        document.getElementById('history-loading').classList.add('d-none');
                        document.getElementById('history-content').innerHTML = `
                            <div class="text-center py-5">
                                <i class="bi bi-clipboard-x text-muted fs-1"></i>
                                <p class="text-muted mt-2">No location milestones logged yet.</p>
                            </div>`;
                    }
                });
        }

        // 7. CORE DATA FETCHER
        // Utility function to split the database string
        function parseStep(data) {
            if (!data) return { time: '--:--', lat: null, lng: null };
            let parts = data.split('|');
            let coords = parts[0].split(',');
            return { time: parts[1], lat: coords[0], lng: coords[1] };
        }

        function loadActiveLocations() {
            fetch('get_active_locations.php')
                .then(res => res.json())
                .then(data => {
                    allTravelersData = data;
                    const listContainer = document.getElementById('travelers-list');
                    document.getElementById('traveler-count').innerText = data.length;

                    if (data.length === 0) {
                        listContainer.innerHTML = `<div class="text-center p-5 mt-4 text-muted"><i class="bi bi-geo-slash fs-2"></i><h6>No Active Deployments</h6></div>`;
                        markerGroup.clearLayers();
                        markers = {};
                        return;
                    }

                    let newListHTML = '';
                    let activeIDs = [];

                    data.forEach(emp => {
                        const lat = parseFloat(emp.current_lat);
                        const lng = parseFloat(emp.current_lng);
                        const uniqueMarkerId = emp.travel_type.substring(0, 2) + '-' + emp.id;
                        activeIDs.push(uniqueMarkerId);

                        if (!isNaN(lat) && !isNaN(lng)) {
                            const initials = getInitials(emp.name);

                            // Parse step data from backend
                            let s1 = parseStep(emp.step1_data);
                            let s2 = parseStep(emp.step2_data);
                            let s3 = parseStep(emp.step3_data);
                            let s4 = parseStep(emp.step4_data);

                            // Status logic
                            let statusText = "En Route (Going)";
                            let statusColor = "#3b82f6";
                            if (emp.tracking_step == 2) { statusText = "Arrived at Destination"; statusColor = "#10b981"; }
                            else if (emp.tracking_step == 3) { statusText = "En Route (Returning)"; statusColor = "#f59e0b"; }

                            // 4-STEP TIMELINE UI (ENGLISH + LOCATION SPANS)
                            const popupContent = `
                                <div style="font-family: 'Segoe UI', Tahoma, sans-serif; min-width: 250px; padding: 5px;">
                                    <div style="display:flex; align-items:center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #f0f2f5;">
                                        <div style="width:38px; height:38px; background:#800000; color:#fff; border-radius:50%; text-align:center; line-height:38px; font-weight:bold; font-size:14px; margin-right:12px; flex-shrink:0;">${initials}</div>
                                        <div>
                                            <strong style="display:block; font-size:14px; color:#1e293b; line-height:1.2;">${emp.name}</strong>
                                            <span style="font-size:11px; color:#64748b; font-weight:600;"><i class="bi bi-geo-alt-fill text-danger"></i> To: ${emp.destination}</span>
                                        </div>
                                    </div>
                                    
                                    <div style="position: relative; padding-left: 20px; border-left: 2px dashed #cbd5e1; margin-left: 8px;">
                                        
                                        <div style="position: relative; margin-bottom: 15px;">
                                            <div style="position: absolute; left: -26px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: ${emp.tracking_step >= 1 ? '#10b981' : '#cbd5e1'}; box-shadow: 0 0 0 3px #fff;"></div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                                <span style="font-size: 12px; font-weight: bold; color: ${emp.tracking_step >= 1 ? '#0f172a' : '#94a3b8'};">Departed (Start)</span>
                                                <span style="font-size: 10px; font-weight: bold; color: ${emp.tracking_step >= 1 ? '#10b981' : '#94a3b8'}; background: #f8fafc; padding: 2px 6px; border-radius: 4px;">${s1.time}</span>
                                            </div>
                                            <div style="font-size: 10px; color: #64748b; line-height: 1.2;">
                                                <i class="bi bi-pin-map-fill me-1"></i><span class="async-loc" data-lat="${s1.lat}" data-lng="${s1.lng}">Loading address...</span>
                                            </div>
                                        </div>

                                        <div style="position: relative; margin-bottom: 15px;">
                                            <div style="position: absolute; left: -26px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: ${emp.tracking_step >= 2 ? '#10b981' : '#cbd5e1'}; box-shadow: 0 0 0 3px #fff;"></div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                                <span style="font-size: 12px; font-weight: bold; color: ${emp.tracking_step >= 2 ? '#0f172a' : '#94a3b8'};">Arrived at Destination</span>
                                                <span style="font-size: 10px; font-weight: bold; color: ${emp.tracking_step >= 2 ? '#10b981' : '#94a3b8'}; background: #f8fafc; padding: 2px 6px; border-radius: 4px;">${s2.time}</span>
                                            </div>
                                            <div style="font-size: 10px; color: #64748b; line-height: 1.2;">
                                                <i class="bi bi-pin-map-fill me-1"></i><span class="async-loc" data-lat="${s2.lat}" data-lng="${s2.lng}">Tap marker to load address...</span>
                                            </div>
                                        </div>

                                        <div style="position: relative; margin-bottom: 15px;">
                                            <div style="position: absolute; left: -26px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: ${emp.tracking_step >= 3 ? '#f59e0b' : '#cbd5e1'}; box-shadow: 0 0 0 3px #fff;"></div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                                <span style="font-size: 12px; font-weight: bold; color: ${emp.tracking_step >= 3 ? '#0f172a' : '#94a3b8'};">Leaving Destination</span>
                                                <span style="font-size: 10px; font-weight: bold; color: ${emp.tracking_step >= 3 ? '#f59e0b' : '#94a3b8'}; background: #f8fafc; padding: 2px 6px; border-radius: 4px;">${s3.time}</span>
                                            </div>
                                            <div style="font-size: 10px; color: #64748b; line-height: 1.2;">
                                                <i class="bi bi-pin-map-fill me-1"></i><span class="async-loc" data-lat="${s3.lat}" data-lng="${s3.lng}">Tap marker to load address...</span>
                                            </div>
                                        </div>

                                        <div style="position: relative;">
                                            <div style="position: absolute; left: -26px; top: 2px; width: 10px; height: 10px; border-radius: 50%; background: ${emp.tracking_step >= 4 ? '#800000' : '#cbd5e1'}; box-shadow: 0 0 0 3px #fff;"></div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                                <span style="font-size: 12px; font-weight: bold; color: ${emp.tracking_step >= 4 ? '#0f172a' : '#94a3b8'};">Returned to Office</span>
                                                <span style="font-size: 10px; font-weight: bold; color: ${emp.tracking_step >= 4 ? '#800000' : '#94a3b8'}; background: #f8fafc; padding: 2px 6px; border-radius: 4px;">${s4.time}</span>
                                            </div>
                                            <div style="font-size: 10px; color: #64748b; line-height: 1.2;">
                                                <i class="bi bi-pin-map-fill me-1"></i><span class="async-loc" data-lat="${s4.lat}" data-lng="${s4.lng}">Tap marker to load address...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            let employeePic = emp.profile_image || '';
                            let customIcon = createProfileMarker(employeePic, initials);

                            if (markers[uniqueMarkerId]) {
                                markers[uniqueMarkerId].slideTo([lat, lng], { duration: 3000, keepAtCenter: false });
                                markers[uniqueMarkerId].setIcon(customIcon); // Update in case nagbago
                                markers[uniqueMarkerId].setPopupContent(popupContent);
                            } else {
                                markers[uniqueMarkerId] = L.marker([lat, lng], { icon: customIcon })
                                    .bindPopup(popupContent, { closeButton: false });
                                markerGroup.addLayer(markers[uniqueMarkerId]);
                            }

                            // Generate Sidebar Card (Same as before)
                            let isActiveClass = document.getElementById('card-' + uniqueMarkerId)?.classList.contains('active-card') ? 'active-card' : '';
                            newListHTML += `<div id="card-${uniqueMarkerId}" class="list-group-item list-group-item-action traveler-card ${isActiveClass}" onclick="focusMap(${lat}, ${lng}, '${uniqueMarkerId}')">
                                                <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0 fw-bold" style="color: #1e293b; font-size: 14px;">${emp.name}</h6>
                                                    <span class="badge bg-success rounded-pill pulse-live" style="font-size: 9px;"><i class="bi bi-record-circle me-1"></i>Active</span>
                                                </div>
                                                <div class="text-truncate" style="font-size: 12px; color: #475569;"><i class="bi bi-pin-map-fill text-maroon me-1"></i>To: ${emp.destination}</div>
                                            </div>`;
                        }
                    });

                    for (let mId in markers) {
                        if (!activeIDs.includes(mId)) { markerGroup.removeLayer(markers[mId]); delete markers[mId]; }
                    }
                    if (document.getElementById('searchInput').value === '') { listContainer.innerHTML = newListHTML; }
                })
                .catch(err => console.error("Error:", err));
        }

        // --- AUTOMATIC GEOCODING WHEN POPUP OPENS ---
        // --- AUTOMATIC GEOCODING CACHE LOGIC ---
        map.on('popupopen', async function (e) {
            let popupNode = e.popup._contentNode;
            let locSpans = popupNode.querySelectorAll('.async-loc');

            for (let span of locSpans) {
                let lat = span.getAttribute('data-lat');
                let lng = span.getAttribute('data-lng');
                let coordKey = `${lat},${lng}`;

                if (lat && lng && lat !== 'null') {
                    // Kung nasa cache na, instant load
                    if (addressCache[coordKey]) {
                        span.innerText = addressCache[coordKey];
                        span.setAttribute('data-loaded', 'true');
                    }
                    // Kung wala pa, mag-fetch sa API
                    else if (!span.hasAttribute('data-loaded')) {
                        span.innerHTML = `<span class="spinner-border spinner-border-sm text-maroon" role="status" aria-hidden="true"></span> Translating...`;

                        let address = await getRealAddress(lat, lng);
                        addressCache[coordKey] = address; // I-save sa cache

                        span.innerText = address;
                        span.setAttribute('data-loaded', 'true');
                    }
                } else if (lat === 'null') {
                    span.innerText = "--";
                }
            }
        });

        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(loadActiveLocations, 500);
            setInterval(loadActiveLocations, 8000); // 8 second background refresh
        });
    </script>
</body>

</html>