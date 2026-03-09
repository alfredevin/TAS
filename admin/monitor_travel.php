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

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <style>
        body {
            background-color: #f4f6f9;
        }

        /* Map Container */
        .map-wrapper {
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            background: #fff;
            padding: 8px;
            position: relative;
        }

        #map {
            height: 75vh;
            border-radius: 18px;
            z-index: 1;
        }

        .btn-focus-all {
            position: absolute;
            bottom: 25px;
            left: 25px;
            z-index: 1000;
            background: #ffffff;
            border: 2px solid #800000;
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 700;
            color: #800000;
            box-shadow: 0 8px 20px rgba(128, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .btn-focus-all:hover {
            background: #800000;
            color: #fff;
            transform: translateY(-3px);
        }

        /* Sidebar Styling */
        .sidebar-card {
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            border: none;
            background: #fff;
            height: 77vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            background: linear-gradient(135deg, #800000 0%, #a00000 100%);
            color: white;
            padding: 24px;
        }

        /* Filter Pills (Nasa Itaas Na) */
        .filter-pills {
            background: #fff;
            padding: 6px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .filter-pills .btn-check:checked+.btn {
            background-color: #800000;
            color: #fff;
            font-weight: bold;
            border-color: transparent;
            box-shadow: 0 4px 10px rgba(128, 0, 0, 0.2);
        }

        .filter-pills .btn {
            color: #64748b;
            border: 1px solid transparent;
            border-radius: 50px;
            font-size: 13px;
            padding: 8px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .filter-pills .btn:hover {
            color: #800000;
            background-color: #f8fafc;
        }

        /* Search Box */
        .search-box {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            margin-top: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: 0.3s;
        }

        .search-box:focus-within {
            background: #fff;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }

        .search-box input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            margin-left: 10px;
            font-size: 14px;
            color: white;
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-box:focus-within input {
            color: #800000;
        }

        .search-box:focus-within i {
            color: #800000 !important;
        }

        /* List Cards */
        .traveler-card {
            border: none;
            border-bottom: 1px solid #f1f5f9;
            padding: 18px 24px;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #fff;
        }

        .traveler-card:hover {
            background-color: #f8fafc;
        }

        .traveler-card.active-card {
            background-color: #fff1f2;
            border-left: 5px solid #800000 !important;
        }

        .emp-photo-small {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }

        /* Marker Styles */
        .custom-profile-marker {
            background: transparent;
            border: none;
        }

        .marker-photo-container {
            width: 50px;
            height: 50px;
            background: #fff;
            border-radius: 50%;
            border: 3px solid #800000;
            box-shadow: 0 6px 15px rgba(128, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .marker-photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .marker-pointer {
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-top: 14px solid #800000;
            z-index: 1;
        }

        .pulse-ring {
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border-radius: 50%;
            border: 3px solid #800000;
            animation: pulsate 2s infinite ease-out;
            opacity: 0;
            z-index: 0;
        }

        @keyframes pulsate {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }

            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        .pulse-live {
            animation: pulseLiveBadge 2s infinite;
        }

        @keyframes pulseLiveBadge {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

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

        /* Modern Vertical Timeline (4 Steps) */
        .timeline-popup {
            position: relative;
            padding-left: 20px;
            margin: 15px 0 5px 0;
            border-left: 2px dashed #cbd5e1;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 12px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: -27px;
            top: 3px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #cbd5e1;
            box-shadow: 0 0 0 3px #fff;
        }

        .timeline-marker.active {
            background: #10b981;
        }

        .timeline-marker.current {
            background: #f59e0b;
            animation: pulseLiveBadge 2s infinite;
        }

        .timeline-content {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .timeline-title {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            justify-content: space-between;
        }

        .timeline-time {
            font-size: 10px;
            color: #10b981;
            font-weight: bold;
        }

        .timeline-address {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
        }
    </style>

    <main id="main" class="main">
        <div class="pagetitle d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="fw-bolder" style="color: #1e3c72; font-size: 1.8rem;">Live Fleet Track
                    <span class="badge bg-success pulse-live ms-2 fw-bold rounded-pill px-3 py-2"
                        style="font-size: 12px; letter-spacing: 1px;">
                        <i class="bi bi-broadcast me-1"></i> ACTIVE
                    </span>
                </h1>
                <nav>
                    <ol class="breadcrumb bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-muted">Admin</a></li>
                        <li class="breadcrumb-item active fw-bold text-primary">Monitor Travel</li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex gap-1 filter-pills">
                <input type="radio" class="btn-check" name="typeFilter" id="filterAll" value="ALL" checked
                    onchange="filterList()">
                <label class="btn" for="filterAll">All Logs</label>

                <input type="radio" class="btn-check" name="typeFilter" id="filterTA" value="TA"
                    onchange="filterList()">
                <label class="btn" for="filterTA"><i class="bi bi-briefcase me-1"></i>Travel Auth</label>

                <input type="radio" class="btn-check" name="typeFilter" id="filterPS" value="PS"
                    onchange="filterList()">
                <label class="btn" for="filterPS"><i class="bi bi-ticket-detailed me-1"></i>Pass Slip</label>
            </div>
        </div>

        <section class="section">
            <div class="row align-items-stretch flex-column-reverse flex-lg-row">

                <div class="col-xl-4 col-lg-5 mb-4">
                    <div class="sidebar-card">
                        <div class="sidebar-header">
                            <h5 class="m-0 mb-1 fw-bolder d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-radar me-2"></i>Field Personnel</span>
                                <span id="traveler-count"
                                    class="badge bg-white text-maroon rounded-pill px-3 py-2 shadow-sm">0</span>
                            </h5>

                            <div class="search-box">
                                <i class="bi bi-search text-white-50" id="searchIcon"></i>
                                <input type="text" id="searchInput" placeholder="Search name or location..."
                                    onkeyup="filterList()">
                            </div>
                        </div>

                        <div class="card-body p-0 custom-scroll"
                            style="overflow-y: auto; flex-grow: 1; background: #f8fafc;">
                            <div class="list-group list-group-flush" id="travelers-list">
                                <div
                                    class="text-center p-5 text-muted h-100 d-flex flex-column justify-content-center align-items-center">
                                    <div class="spinner-border text-maroon mb-3"
                                        style="width: 2.5rem; height: 2.5rem; opacity: 0.5;"></div>
                                    <h6 class="fw-bold text-secondary mb-1">Connecting to GPS...</h6>
                                    <small class="text-muted">Locating active personnel.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-lg-7 mb-4">
                    <div class="map-wrapper">
                        <div id="map"></div>
                        <button class="btn-focus-all" onclick="focusAllMarkers()" title="View All Deployments">
                            <i class="bi bi-aspect-ratio me-2"></i>Focus All
                        </button>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>

    <script>
        // 1. INITIALIZE MAP
        var map = L.map('map', { zoomControl: false }).setView([13.3858, 121.9563], 11);
        L.control.zoom({ position: 'topright' }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 20
        }).addTo(map);

        var markers = {};
        var markerGroup = L.featureGroup().addTo(map);
        var addressCache = {};

        // 2. CREATE IMAGE MARKER (Generic Photo Fallback)
        function createProfileMarker(photoUrl) {
            let defaultAvatar = 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
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

        // 3. GET REAL ADDRESS WITH CACHING
        async function fetchAddress(lat, lng, elementId, fallbackText = "Fetching location...") {
            if (!lat || !lng || lat === 'null') {
                let el = document.getElementById(elementId);
                if (el) el.innerHTML = `<span class="text-muted"><i class="bi bi-geo-alt"></i> ${fallbackText}</span>`;
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
                let address = data.address.village || data.address.town || data.address.city || data.display_name.split(',').slice(0, 3).join(',');

                addressCache[coordKey] = address; // Save to cache
                if (el) el.innerHTML = `<i class="bi bi-geo-alt-fill text-danger me-1"></i> ${address}`;
            } catch (error) {
                if (el) el.innerHTML = `<i class="bi bi-geo-alt-fill text-danger me-1"></i> GPS: ${lat}, ${lng}`;
            }
        }

        // 4. MAP INTERACTIONS
        function focusAllMarkers() {
            if (Object.keys(markers).length > 0) { map.flyToBounds(markerGroup.getBounds(), { padding: [50, 50], duration: 1.5 }); }
        }

        function focusMap(lat, lng, id) {
            document.querySelectorAll('.traveler-card').forEach(card => card.classList.remove('active-card'));
            let clickedCard = document.getElementById('card-' + id);
            if (clickedCard) clickedCard.classList.add('active-card');

            map.flyTo([lat, lng], 17, { animate: true, duration: 1.5 });
            if (markers[id]) { setTimeout(() => markers[id].openPopup(), 1200); }
        }

        // FILTER & SEARCH FUNCTION
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

        // 5. PARSE STEP DATA (Coordinates + Time)
        function parseStepData(dataStr) {
            if (!dataStr) return { time: '--:--', lat: null, lng: null };
            let parts = dataStr.split('|');
            if (parts.length < 2) return { time: '--:--', lat: null, lng: null };
            let coords = parts[0].split(',');
            return { time: parts[1], lat: coords[0], lng: coords[1] };
        }

        // 6. CORE LOCATION FETCHER
        function loadActiveLocations() {
            fetch('get_active_locations.php')
                .then(res => res.json())
                .then(data => {
                    const listContainer = document.getElementById('travelers-list');

                    if (data.length === 0) {
                        listContainer.innerHTML = `<div class="text-center p-5 mt-4 text-muted"><i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i><h6 class="mt-3 fw-bold">All Clear</h6><small>No personnel currently deployed.</small></div>`;
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
                                ? `<span class="badge bg-warning text-dark"><i class="bi bi-ticket-detailed me-1"></i>Pass Slip</span>`
                                : `<span class="badge bg-primary"><i class="bi bi-briefcase me-1"></i>Travel Auth</span>`;

                            // Extract parsed data for 4 steps
                            let s1 = parseStepData(emp.step1_data);
                            let s2 = parseStepData(emp.step2_data);
                            let s3 = parseStepData(emp.step3_data);
                            let s4 = parseStepData(emp.step4_data);

                            let currentStep = parseInt(emp.tracking_step);
                            let stepText = "In Transit"; let stepColor = "#3b82f6";
                            if (currentStep == 2) { stepText = "Arrived"; stepColor = "#10b981"; }
                            else if (currentStep == 3) { stepText = "Returning"; stepColor = "#f59e0b"; }

                            // Tiyaking tama ang folder path! (Halimbawa: '../uploads/')
                            let avatarSrc = (emp.photo && emp.photo !== '') ? '../uploads/' + emp.photo : '';

                            // POPUP HTML WITH 4-STEP VERTICAL TIMELINE
                            let popupHTML = `
                                <div style="min-width: 260px; font-family: 'Inter', sans-serif;">
                                    <div style="display:flex; align-items:center; margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid #e2e8f0;">
                                        <img src="${avatarSrc}" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'" style="width:45px; height:45px; border-radius:50%; object-fit:cover; border:2px solid #800000; margin-right:10px;">
                                        <div>
                                            <strong style="display:block; font-size:14px; color:#0f172a;">${emp.name}</strong>
                                            ${typeBadge}
                                        </div>
                                    </div>
                                    <div style="font-size:12px; margin-bottom:10px;">
                                        <span style="color:#64748b; display:block;">Target Destination:</span>
                                        <b style="color:#1e293b;">${emp.destination}</b>
                                    </div>
                                    
                                    <div class="timeline-popup">
                                        <div class="timeline-item">
                                            <div class="timeline-marker ${currentStep >= 1 ? 'active' : ''}"></div>
                                            <div class="timeline-content">
                                                <div class="timeline-title">Departed <span class="timeline-time">${s1.time}</span></div>
                                                <div class="timeline-address" id="pop-s1-${uniqueId}">Awaiting location...</div>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker ${currentStep >= 2 ? 'active' : ''} ${currentStep == 1 ? 'current' : ''}"></div>
                                            <div class="timeline-content">
                                                <div class="timeline-title">Arrived <span class="timeline-time">${s2.time}</span></div>
                                                <div class="timeline-address" id="pop-s2-${uniqueId}">Awaiting location...</div>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker ${currentStep >= 3 ? 'active' : ''} ${currentStep == 2 ? 'current' : ''}"></div>
                                            <div class="timeline-content">
                                                <div class="timeline-title">Left Dest <span class="timeline-time">${s3.time}</span></div>
                                                <div class="timeline-address" id="pop-s3-${uniqueId}">Awaiting location...</div>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker ${currentStep >= 4 ? 'active' : ''} ${currentStep == 3 ? 'current' : ''}"></div>
                                            <div class="timeline-content">
                                                <div class="timeline-title">Returned <span class="timeline-time">${s4.time}</span></div>
                                                <div class="timeline-address" id="pop-s4-${uniqueId}">Awaiting return...</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // MARKER UPDATE
                            if (markers[uniqueId]) {
                                markers[uniqueId].slideTo([currentLat, currentLng], { duration: 2000, keepAtCenter: false });
                                markers[uniqueId].setPopupContent(popupHTML);
                            } else {
                                let customIcon = createProfileMarker(avatarSrc);
                                markers[uniqueId] = L.marker([currentLat, currentLng], { icon: customIcon }).bindPopup(popupHTML);
                                markerGroup.addLayer(markers[uniqueId]);
                            }

                            // Trigger fetching ng addresses sa popup (4 steps)
                            if (currentStep >= 1) fetchAddress(s1.lat, s1.lng, `pop-s1-${uniqueId}`);
                            if (currentStep >= 2) fetchAddress(s2.lat, s2.lng, `pop-s2-${uniqueId}`);
                            if (currentStep >= 3) fetchAddress(s3.lat, s3.lng, `pop-s3-${uniqueId}`);
                            if (currentStep >= 4) fetchAddress(s4.lat, s4.lng, `pop-s4-${uniqueId}`);

                            // SIDEBAR CARD (With data-type for Filtering)
                            let addressElementId = `sidebar-loc-${uniqueId}`;
                            let cardHTML = `
                                <div class="d-flex w-100 align-items-center">
                                    <img src="${avatarSrc}" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'" class="emp-photo-small me-3 shadow-sm">
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 14px;">${emp.name}</h6>
                                            <span class="badge" style="background-color: ${stepColor}15; color: ${stepColor}; border: 1px solid ${stepColor}40;">${stepText}</span>
                                        </div>
                                        <div class="text-truncate text-muted mb-1" style="font-size: 11px; font-weight: 500;">
                                            To: ${emp.destination}
                                        </div>
                                        <div id="${addressElementId}" class="text-truncate text-muted mt-1" style="font-size: 10px;">
                                            <i class="bi bi-geo-alt text-danger me-1"></i> Locating current position...
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
                                newDiv.className = 'list-group-item list-group-item-action traveler-card';
                                newDiv.setAttribute('data-type', emp.travel_type); // DATA ATTRIBUTE PARA SA FILTER
                                newDiv.onclick = () => focusMap(currentLat, currentLng, uniqueId);
                                newDiv.innerHTML = cardHTML;

                                if (document.getElementById('travelers-list').innerText.includes('Connecting')) { listContainer.innerHTML = ''; }
                                listContainer.appendChild(newDiv);
                            }

                            // Fetch sidebar current location
                            fetchAddress(currentLat, currentLng, addressElementId);
                        }
                    });

                    // Remove inactive ones
                    for (let id in markers) {
                        if (!currentFetchedIDs.includes(id)) {
                            markerGroup.removeLayer(markers[id]); delete markers[id];
                            let cardToRemove = document.getElementById('card-' + id);
                            if (cardToRemove) cardToRemove.remove();
                        }
                    }

                    filterList(); // I-apply ang filter kapag nag-refresh ang list para updated lagi ang Count
                    if (isFirstLoad) { focusAllMarkers(); }
                })
                .catch(err => console.error("Error fetching map data:", err));
        }

        document.addEventListener("DOMContentLoaded", () => {
            loadActiveLocations();
            setInterval(loadActiveLocations, 5000);
        });
    </script>
</body>

</html>