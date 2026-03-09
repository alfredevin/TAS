<?php include './../config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include '../template/header.php'; ?>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.marker.slideto@0.2.0/Leaflet.Marker.SlideTo.js"></script>
</head>

<body style="background-color: #f1f5f9; overflow: hidden;">
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <style>
        /* =========================================
           ENTERPRISE FULL-SCREEN MAP
           ========================================= */
        #main {
            padding: 0 !important;
            margin-top: 60px;
        }

        .section {
            padding: 0;
        }

        .map-container-wrapper {
            position: relative;
            height: calc(100vh - 60px);
            width: 100%;
        }

        #map {
            height: 100%;
            width: 100%;
            z-index: 1;
        }

        .leaflet-control-zoom {
            border: none !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15) !important;
            margin-right: 20px !important;
            margin-bottom: 90px !important;
        }

        .leaflet-control-zoom a {
            color: #1e3c72 !important;
            font-weight: bold;
            border-radius: 8px !important;
            margin-bottom: 5px;
        }

        /* =========================================
           SMART COLLAPSIBLE PANEL (BOTTOM SHEET)
           ========================================= */
        .glass-ops-panel {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 380px;
            height: calc(100% - 40px);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255, 255, 255, 0.6);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .panel-drag-handle {
            display: none;
            width: 40px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            margin: 0 auto 10px auto;
        }

        .panel-header {
            background: linear-gradient(135deg, #800000 0%, #b91c1c 100%);
            padding: 20px 20px 25px 20px;
            color: white;
            cursor: pointer;
        }

        .live-badge-glow {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.5);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1px;
            animation: breatheGreen 2s infinite ease-in-out;
        }

        @keyframes breatheGreen {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
            }

            50% {
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }
        }

        /* Modern Segmented Filters */
        .segment-filter-wrapper {
            background: rgba(255, 255, 255, 0.15);
            padding: 5px;
            border-radius: 12px;
            display: flex;
            margin-top: 15px;
        }

        .segment-filter-wrapper .btn-check:checked+.btn {
            background: #fff;
            color: #800000;
            font-weight: 800;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .segment-filter-wrapper .btn {
            flex: 1;
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
            padding: 6px 0;
        }

        /* Search Box */
        .search-container {
            padding: 15px 20px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }

        .search-box {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            transition: 0.3s;
            border: 1px solid transparent;
        }

        .search-box:focus-within {
            background: #fff;
            border-color: #800000;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
        }

        .search-box input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            margin-left: 10px;
            font-size: 13px;
            color: #1e293b;
        }

        /* Personnel List Custom Scroll */
        .personnel-list {
            flex-grow: 1;
            overflow-y: auto;
            background: #f8fafc;
            padding-bottom: 15px;
            transition: opacity 0.3s;
        }

        .personnel-list::-webkit-scrollbar {
            width: 5px;
        }

        .personnel-list::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }

        /* Interactive Personnel Cards */
        .traveler-card {
            margin: 12px 15px 0 15px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .traveler-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .traveler-card.active-card {
            background: #fff1f2;
            border: 2px solid #800000;
            box-shadow: 0 10px 25px rgba(128, 0, 0, 0.1);
        }

        .emp-photo-small {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background-color: #e2e8f0;
        }

        /* Floating Recenter Map Button */
        .btn-map-control {
            position: absolute;
            bottom: 25px;
            right: 20px;
            z-index: 1000;
            background: #fff;
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            font-weight: 800;
            color: #1e3c72;
            font-size: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            transition: 0.2s;
        }

        .btn-map-control:active {
            transform: scale(0.95);
        }

        /* =========================================
           MOBILE SMART BEHAVIORS
           ========================================= */
        @media (max-width: 991px) {
            .glass-ops-panel {
                width: calc(100% - 30px);
                height: 55vh;
                top: auto;
                bottom: 15px;
                left: 15px;
                border-radius: 28px;
            }

            .panel-drag-handle {
                display: block;
            }

            .leaflet-control-zoom {
                margin-bottom: 80px !important;
            }

            .glass-ops-panel.collapsed {
                height: 90px;
                transform: translateY(0);
            }

            .glass-ops-panel.collapsed .search-container,
            .glass-ops-panel.collapsed .personnel-list,
            .glass-ops-panel.collapsed .segment-filter-wrapper {
                opacity: 0;
                pointer-events: none;
            }

            .glass-ops-panel.collapsed .panel-header {
                padding-bottom: 15px;
                border-radius: 24px;
            }

            .btn-map-control {
                bottom: 120px;
            }
        }

        /* =========================================
           MAP MARKER PREMIUM DESIGN
           ========================================= */
        .custom-profile-marker {
            background: transparent;
            border: none;
        }

        .marker-photo-container {
            width: 48px;
            height: 48px;
            background: #e2e8f0;
            border-radius: 50%;
            border: 3px solid #800000;
            box-shadow: 0 8px 20px rgba(128, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .marker-photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            border-top: 12px solid #800000;
            z-index: 1;
        }

        .pulse-ring {
            position: absolute;
            top: -6px;
            left: -6px;
            right: -6px;
            bottom: -6px;
            border-radius: 50%;
            border: 2px solid #800000;
            animation: pulsateMarker 2s infinite ease-out;
            z-index: 0;
        }

        @keyframes pulsateMarker {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }

            100% {
                transform: scale(1.6);
                opacity: 0;
                border-width: 0;
            }
        }

        /* Map Popup Styling Redesign */
        .leaflet-popup-content-wrapper {
            border-radius: 16px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: none;
        }

        .leaflet-popup-content {
            margin: 0;
            width: 280px !important;
        }

        .popup-header {
            background: #f8fafc;
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
        }

        .popup-body {
            padding: 15px;
        }

        .timeline-popup {
            position: relative;
            padding-left: 20px;
            margin-top: 10px;
            border-left: 2px dashed #cbd5e1;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 15px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: -27px;
            top: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #cbd5e1;
            box-shadow: 0 0 0 4px #fff;
        }

        .timeline-marker.active {
            background: #10b981;
        }

        .timeline-content {
            line-height: 1.2;
        }

        .timeline-title {
            font-size: 12px;
            font-weight: 800;
            color: #1e293b;
            display: flex;
            justify-content: space-between;
        }

        .timeline-address {
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
            display: block;
            font-weight: 500;
        }
    </style>

    <main id="main">
        <div class="map-container-wrapper">
            <div id="map"></div>

            <button class="btn-map-control" onclick="focusAllMarkers()" id="focusBtn">
                <i class="bi bi-aspect-ratio me-2"></i> Focus All
            </button>

            <div class="glass-ops-panel" id="opsPanel">
                <div class="panel-header" onclick="toggleMobilePanel()">
                    <div class="panel-drag-handle"></div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="fw-bolder m-0 d-flex align-items-center" style="letter-spacing: -0.5px;">
                                Live Operations
                                <i class="bi bi-chevron-down ms-2 d-lg-none" id="panelToggleIcon"
                                    style="font-size: 14px; opacity: 0.7;"></i>
                            </h4>
                            <div class="text-white-50 small fw-bold mt-1">Field Personnel Tracker</div>
                        </div>
                        <div class="badge rounded-pill live-badge-glow px-3 py-2">
                            <span id="traveler-count" class="fs-6 me-1">0</span> ON ROAD
                        </div>
                    </div>

                    <div class="segment-filter-wrapper" onclick="event.stopPropagation();">
                        <input type="radio" class="btn-check" name="typeFilter" id="filterAll" value="ALL" checked
                            onchange="filterList()">
                        <label class="btn" for="filterAll">All</label>

                        <input type="radio" class="btn-check" name="typeFilter" id="filterTA" value="TA"
                            onchange="filterList()">
                        <label class="btn" for="filterTA">T.A.</label>

                        <input type="radio" class="btn-check" name="typeFilter" id="filterPS" value="PS"
                            onchange="filterList()">
                        <label class="btn" for="filterPS">Pass Slip</label>
                    </div>
                </div>

                <div class="search-container">
                    <div class="search-box">
                        <i class="bi bi-search text-muted"></i>
                        <input type="text" id="searchInput" placeholder="Find personnel or location..."
                            onkeyup="filterList()">
                    </div>
                </div>

                <div class="personnel-list" id="travelers-list">
                    <div
                        class="text-center p-5 text-muted d-flex flex-column justify-content-center align-items-center h-100">
                        <div class="spinner-grow text-danger mb-3" style="width: 2.5rem; height: 2.5rem;" role="status">
                        </div>
                        <h6 class="fw-bolder text-dark mb-1">Establishing Uplink...</h6>
                        <small>Locating active GPS signals.</small>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>

    <script>
        // --- UX ENHANCEMENT: TOGGLE PANEL ON MOBILE ---
        const opsPanel = document.getElementById('opsPanel');
        const toggleIcon = document.getElementById('panelToggleIcon');
        const defaultAvatar = 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; // Premium default icon silhouette

        function toggleMobilePanel(forceCollapse = false) {
            if (window.innerWidth > 991) return;

            if (forceCollapse || !opsPanel.classList.contains('collapsed')) {
                opsPanel.classList.add('collapsed');
                toggleIcon.classList.remove('bi-chevron-down');
                toggleIcon.classList.add('bi-chevron-up');
            } else {
                opsPanel.classList.remove('collapsed');
                toggleIcon.classList.remove('bi-chevron-up');
                toggleIcon.classList.add('bi-chevron-down');
            }
        }

        var map = L.map('map', { zoomControl: false }).setView([13.3858, 121.9563], 12);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap', maxZoom: 20
        }).addTo(map);

        var markers = {};
        var markerGroup = L.featureGroup().addTo(map);
        var addressCache = {};

        // 2. PREMIUM IMAGE MARKER GENERATOR (UPDATED TO USE DEFAULT IMAGE)
        function createProfileMarker(photoUrl) {
            let imgSource = (photoUrl && photoUrl !== '') ? photoUrl : defaultAvatar;

            return L.divIcon({
                className: 'custom-profile-marker',
                html: `
                    <div class="pulse-ring"></div>
                    <div class="marker-photo-container">
                        <img src="${imgSource}" onerror="this.src='${defaultAvatar}'">
                    </div>
                    <div class="marker-pointer"></div>
                `,
                iconSize: [50, 65], iconAnchor: [25, 65], popupAnchor: [0, -60]
            });
        }

        // 3. REVERSE GEOCODING
        async function fetchAddress(lat, lng, elementId, fallbackText = "Locating...") {
            if (!lat || !lng || lat === 'null') {
                let el = document.getElementById(elementId);
                if (el) el.innerHTML = `<i class="bi bi-broadcast text-warning me-1"></i> ${fallbackText}`;
                return;
            }

            let coordKey = `${lat},${lng}`;
            let el = document.getElementById(elementId);

            if (addressCache[coordKey]) {
                if (el) el.innerHTML = `<i class="bi bi-geo-alt-fill text-danger me-1"></i> ${addressCache[coordKey]}`;
                return;
            }

            try {
                let response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=16`);
                let data = await response.json();
                let address = data.address.road || data.address.village || data.address.town || data.address.city || "Unknown Location";

                addressCache[coordKey] = address;
                if (el) el.innerHTML = `<i class="bi bi-geo-alt-fill text-danger me-1"></i> ${address}`;
            } catch (error) {
                if (el) el.innerHTML = `<i class="bi bi-pin-map text-primary me-1"></i> GPS Fixed`;
            }
        }

        function focusAllMarkers() {
            if (Object.keys(markers).length > 0) {
                map.flyToBounds(markerGroup.getBounds(), { padding: [80, 80], duration: 1.5 });
                toggleMobilePanel(true);
            } else {
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'No active personnel right now.', showConfirmButton: false, timer: 2000 });
            }
        }

        function focusMap(lat, lng, id) {
            document.querySelectorAll('.traveler-card').forEach(card => card.classList.remove('active-card'));
            let clickedCard = document.getElementById('card-' + id);
            if (clickedCard) clickedCard.classList.add('active-card');

            toggleMobilePanel(true);

            map.flyTo([lat, lng], 17, { animate: true, duration: 1.5 });
            if (markers[id]) { setTimeout(() => markers[id].openPopup(), 1200); }
        }

        function filterList() {
            let searchText = document.getElementById('searchInput').value.toLowerCase();
            let filterType = document.querySelector('input[name="typeFilter"]:checked').value;
            let cards = document.querySelectorAll('.traveler-card');
            let visibleCount = 0;

            cards.forEach(card => {
                let text = card.innerText.toLowerCase();
                let type = card.getAttribute('data-type');
                let matchesSearch = text.includes(searchText);
                let matchesType = (filterType === 'ALL' || type === filterType);

                if (matchesSearch && matchesType) {
                    card.style.display = "block";
                    visibleCount++;
                } else {
                    card.style.display = "none";
                }
            });
            document.getElementById('traveler-count').innerText = visibleCount;
        }

        function parseStepData(dataStr) {
            if (!dataStr) return { time: '--:--', lat: null, lng: null };
            let parts = dataStr.split('|');
            if (parts.length < 2) return { time: '--:--', lat: null, lng: null };
            let coords = parts[0].split(',');
            return { time: parts[1], lat: coords[0], lng: coords[1] };
        }

        function loadActiveLocations() {
            fetch('get_active_locations.php')
                .then(res => res.json())
                .then(data => {
                    const listContainer = document.getElementById('travelers-list');

                    if (data.length === 0) {
                        listContainer.innerHTML = `<div class="text-center p-5 text-muted h-100 d-flex flex-column justify-content-center align-items-center">
                            <i class="bi bi-emoji-sunglasses fs-1 mb-2 text-secondary"></i>
                            <h6 class="fw-bold">No Active Deployments</h6>
                            <small>All personnel are currently at the office.</small>
                        </div>`;
                        document.getElementById('traveler-count').innerText = 0;
                        markerGroup.clearLayers(); markers = {}; return;
                    }

                    let currentFetchedIDs = [];
                    let isFirstLoad = Object.keys(markers).length === 0;

                    data.forEach(emp => {
                        const currentLat = parseFloat(emp.current_lat);
                        const currentLng = parseFloat(emp.current_lng);
                        const uniqueId = emp.travel_type + '-' + emp.id;
                        currentFetchedIDs.push(uniqueId);

                        if (!isNaN(currentLat) && !isNaN(currentLng)) {
                            let typeBadge = emp.travel_type === 'PS'
                                ? `<span class="badge bg-warning text-dark px-2 rounded-pill"><i class="bi bi-ticket-detailed"></i> PS</span>`
                                : `<span class="badge bg-primary px-2 rounded-pill"><i class="bi bi-briefcase"></i> TA</span>`;

                            let s1 = parseStepData(emp.step1_data);
                            let s2 = parseStepData(emp.step2_data);
                            let s3 = parseStepData(emp.step3_data);
                            let s4 = parseStepData(emp.step4_data);

                            let currentStep = parseInt(emp.tracking_step);
                            let stepText = "In Transit"; let stepColor = "#3b82f6";
                            if (currentStep == 2) { stepText = "At Destination"; stepColor = "#10b981"; }
                            else if (currentStep == 3) { stepText = "Returning"; stepColor = "#f59e0b"; }

                            let avatarSrc = (emp.photo && emp.photo !== '') ? '../uploads/' + emp.photo : defaultAvatar;

                            // POPUP HTML (UPDATED AVATAR)
                            let popupHTML = `
                                <div class="popup-header">
                                    <img src="${avatarSrc}" onerror="this.src='${defaultAvatar}'" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-right:12px; background-color:#e2e8f0;">
                                    <div>
                                        <div class="fw-bolder text-dark" style="font-size:14px; line-height:1.1;">${emp.name}</div>
                                        <div class="mt-1">${typeBadge} <span class="badge" style="background:${stepColor}15; color:${stepColor};">${stepText}</span></div>
                                    </div>
                                </div>
                                <div class="popup-body">
                                    <div class="small fw-bold text-uppercase text-muted" style="font-size:10px; letter-spacing:0.5px;">Target Destination</div>
                                    <div class="fw-bolder text-dark mb-2">${emp.destination}</div>
                                    
                                    <div class="timeline-popup">
                                        <div class="timeline-item">
                                            <div class="timeline-marker ${currentStep >= 1 ? 'active' : ''}"></div>
                                            <div class="timeline-content">
                                                <div class="timeline-title">Departed Campus <span style="color:#10b981;">${s1.time}</span></div>
                                                <span class="timeline-address" id="pop-s1-${uniqueId}">Processing...</span>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker ${currentStep >= 2 ? 'active' : ''}"></div>
                                            <div class="timeline-content">
                                                <div class="timeline-title">Arrived at Target <span style="color:#10b981;">${s2.time}</span></div>
                                                <span class="timeline-address" id="pop-s2-${uniqueId}">Processing...</span>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker ${currentStep >= 3 ? 'active' : ''}"></div>
                                            <div class="timeline-content">
                                                <div class="timeline-title">Left Target <span style="color:#10b981;">${s3.time}</span></div>
                                                <span class="timeline-address" id="pop-s3-${uniqueId}">Processing...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            if (markers[uniqueId]) {
                                markers[uniqueId].slideTo([currentLat, currentLng], { duration: 2000, keepAtCenter: false });
                                markers[uniqueId].setPopupContent(popupHTML);
                            } else {
                                let customIcon = createProfileMarker(avatarSrc);
                                markers[uniqueId] = L.marker([currentLat, currentLng], { icon: customIcon }).bindPopup(popupHTML);
                                markerGroup.addLayer(markers[uniqueId]);
                            }

                            if (currentStep >= 1) fetchAddress(s1.lat, s1.lng, `pop-s1-${uniqueId}`);
                            if (currentStep >= 2) fetchAddress(s2.lat, s2.lng, `pop-s2-${uniqueId}`);
                            if (currentStep >= 3) fetchAddress(s3.lat, s3.lng, `pop-s3-${uniqueId}`);

                            // SIDEBAR CARD (UPDATED AVATAR)
                            let addressElementId = `sidebar-loc-${uniqueId}`;
                            let cardHTML = `
                                <div class="d-flex align-items-center">
                                    <div class="position-relative me-3">
                                        <img src="${avatarSrc}" onerror="this.src='${defaultAvatar}'" class="emp-photo-small">
                                        <span class="position-absolute bottom-0 end-0 p-1 rounded-circle" style="background:${stepColor}; border:2px solid #fff;"></span>
                                    </div>
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bolder text-dark text-truncate" style="font-size: 14px; max-width:150px;">${emp.name}</h6>
                                            ${typeBadge}
                                        </div>
                                        <div class="text-truncate fw-bold text-secondary mb-1" style="font-size: 11px;">
                                            <i class="bi bi-pin-map-fill me-1 opacity-50"></i>${emp.destination}
                                        </div>
                                        <div id="${addressElementId}" class="text-truncate fw-bold mt-1" style="font-size: 10px; color:${stepColor}; background:${stepColor}10; padding:4px 8px; border-radius:6px; display:inline-block;">
                                            <i class="bi bi-radar me-1"></i> Scanning...
                                        </div>
                                    </div>
                                </div>
                            `;

                            let existingCard = document.getElementById('card-' + uniqueId);
                            if (existingCard) {
                                if (existingCard.innerHTML.indexOf(emp.destination) === -1) {
                                    existingCard.innerHTML = cardHTML;
                                }
                            } else {
                                let newDiv = document.createElement('div');
                                newDiv.id = 'card-' + uniqueId;
                                newDiv.className = 'traveler-card';
                                newDiv.setAttribute('data-type', emp.travel_type);
                                newDiv.onclick = () => focusMap(currentLat, currentLng, uniqueId);
                                newDiv.innerHTML = cardHTML;

                                if (document.getElementById('travelers-list').innerText.includes('Uplink')) { listContainer.innerHTML = ''; }
                                listContainer.appendChild(newDiv);
                            }

                            fetchAddress(currentLat, currentLng, addressElementId);
                        }
                    });

                    for (let id in markers) {
                        if (!currentFetchedIDs.includes(id)) {
                            markerGroup.removeLayer(markers[id]); delete markers[id];
                            let cardToRemove = document.getElementById('card-' + id);
                            if (cardToRemove) cardToRemove.remove();
                        }
                    }

                    filterList();
                    if (isFirstLoad) { focusAllMarkers(); }
                })
                .catch(err => console.error("Data Intercept Error:", err));
        }

        document.addEventListener("DOMContentLoaded", () => {
            loadActiveLocations();
            setInterval(loadActiveLocations, 6000);
        });
    </script>
</body>

</html>