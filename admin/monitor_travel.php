<?php include './../config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include '../template/header.php'; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

</head>

<body>
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>
    <style>
        /* General Page Tweaks */
        body {
            background-color: #f4f6f9;
        }

        /* Modern Map Container - Seamless Look */
        .map-container {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            background: #fff;
            padding: 5px;
        }

        #map {
            height: 72vh;
            border-radius: 12px;
            z-index: 1;
        }

        /* Sidebar Styling - Premium Feel */
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
            padding: 20px 24px;
        }

        .sidebar-header h5 {
            color: #2c3e50;
        }

        .traveler-card {
            transition: all 0.25s ease;
            cursor: pointer;
            border: none;
            border-bottom: 1px solid #f8f9fa;
            padding: 16px 24px;
            background-color: transparent;
        }

        .traveler-card:hover {
            background-color: #fafbfc;
            transform: translateX(4px);
            border-left: 4px solid #800000 !important;
        }

        .traveler-card:last-child {
            border-bottom: none;
        }

        /* Status Indicator Animations */
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

        /* Custom Popup Styling - App Like */
        .leaflet-popup-content-wrapper {
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 0;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .leaflet-popup-content {
            margin: 0;
            width: 280px !important;
        }

        .popup-header {
            background: #fff;
            padding: 16px;
            border-bottom: 1px solid #f0f2f5;
            display: flex;
            align-items: center;
        }

        .popup-avatar {
            width: 42px;
            height: 42px;
            background: #800000;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            margin-right: 14px;
            flex-shrink: 0;
        }

        .popup-body {
            padding: 16px;
            font-size: 13px;
            line-height: 1.5;
            background: #fafbfc;
            color: #4a5568;
        }

        .leaflet-popup-tip-container {
            display: none;
        }

        /* Scrollbar Styling for Sidebar */
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: #e2e8f0;
            border-radius: 20px;
        }
    </style>
    <main id="main" class="main">
        <div class="pagetitle d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold" style="color: #1a202c; font-size: 1.5rem;">Live Monitoring
                    <span class="badge bg-success pulse-live ms-2 fw-normal rounded-pill px-3 py-2"
                        style="font-size: 11px;">
                        <i class="bi bi-record-circle me-1"></i> ONLINE
                    </span>
                </h1>
                <nav>
                    <ol class="breadcrumb mb-0 mt-1">
                        <li class="breadcrumb-item"><a href="index.php" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item active" style="color: #800000; font-weight: 500;">Field Track</li>
                    </ol>
                </nav>
            </div>
        </div>

        <section class="section">
            <div class="row align-items-stretch">
                <div class="col-xl-8 col-lg-7 mb-4">
                    <div class="map-container">
                        <div id="map"></div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-5">
                    <div class="card sidebar-card" style="height: 73vh; display: flex; flex-direction: column;">
                        <div class="sidebar-header">
                            <h5 class="card-title m-0 p-0 fw-bold d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-people text-maroon me-2"></i>Active Employee</span>
                                <span id="traveler-count" class="badge rounded-pill"
                                    style="background: #f1f5f9; color: #475569; font-size: 14px; padding: 6px 12px;">0</span>
                            </h5>
                        </div>

                        <div class="card-body p-0 custom-scroll" style="overflow-y: auto; flex-grow: 1;">
                            <div class="list-group list-group-flush" id="travelers-list">
                                <div class="text-center p-5 text-muted h-100 d-flex flex-column justify-content-center align-items-center"
                                    id="loading-state">
                                    <div class="spinner-border text-maroon mb-3"
                                        style="width: 2.5rem; height: 2.5rem; opacity: 0.5;" role="status"></div>
                                    <h6 class="fw-bold text-secondary mb-1">Syncing Data</h6>
                                    <small style="color: #94a3b8;">Connecting to active devices...</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>

    <script>
        var map = L.map('map', { zoomControl: false }).setView([13.3858, 121.9563], 10);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        var redIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        var markers = {};

        function getInitials(name) {
            let initials = name.match(/\b\w/g) || [];
            initials = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
            return initials;
        }

        function loadActiveLocations() {
            fetch('get_active_locations.php')
                .then(res => res.json())
                .then(data => {
                    const listContainer = document.getElementById('travelers-list');
                    document.getElementById('traveler-count').innerText = data.length;

                    if (data.length === 0) {
                        listContainer.innerHTML = `
                            <div class="text-center p-5 mt-4 h-100 d-flex flex-column justify-content-center align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background: #f8fafc;">
                                    <i class="bi bi-geo-slash fs-2" style="color: #cbd5e1;"></i>
                                </div>
                                <h6 class="fw-bold" style="color: #475569;">No Active Travel</h6>
                                <p class="small text-muted" style="max-width: 200px;">There are no staff members currently on the field.</p>
                            </div>`;

                        for (let id in markers) { map.removeLayer(markers[id]); delete markers[id]; }
                        return;
                    }

                    let newListHTML = '';
                    let activeIDs = [];

                    data.forEach(emp => {
                        const lat = parseFloat(emp.current_lat);
                        const lng = parseFloat(emp.current_lng);
                        activeIDs.push(emp.ta_id);

                        if (!isNaN(lat) && !isNaN(lng)) {
                            let timeString = "Just now";
                            if (emp.location_updated_at) {
                                const dateObj = new Date(emp.location_updated_at);
                                timeString = dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                            }

                            const initials = getInitials(emp.name);

                            // Popup Content Design
                            const popupContent = `
                                <div>
                                    <div class="popup-header">
                                        <div class="popup-avatar">${initials}</div>
                                        <div>
                                            <h6 style="margin: 0; font-weight: 700; color: #1e293b; font-size: 15px;">${emp.name}</h6>
                                            <div style="font-size: 11px; color: #64748b; font-weight: 500; margin-top: 2px;">${emp.memo_no}</div>
                                        </div>
                                    </div>
                                    <div class="popup-body">
                                        <div style="margin-bottom: 8px; display: flex; align-items: flex-start;">
                                            <i class="bi bi-geo-alt-fill text-danger me-2 mt-1"></i>
                                            <span><b style="color: #334155;">To:</b> ${emp.destination}</span>
                                        </div>
                                        <div style="display: flex; align-items: center; color: #3b82f6;">
                                            <i class="bi bi-clock-history me-2"></i>
                                            <span style="font-weight: 500;">Updated: ${timeString}</span>
                                        </div>
                                    </div>
                                </div>
                            `;

                            if (markers[emp.ta_id]) {
                                markers[emp.ta_id].setLatLng([lat, lng]);
                                markers[emp.ta_id].setPopupContent(popupContent);
                            } else {
                                markers[emp.ta_id] = L.marker([lat, lng], { icon: redIcon })
                                    .addTo(map)
                                    .bindPopup(popupContent, { closeButton: false });
                            }

                            // Sidebar Card Design
                            newListHTML += `
                                <div class="list-group-item list-group-item-action traveler-card" onclick="focusMap(${lat}, ${lng}, ${emp.ta_id})">
                                    <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold" style="color: #1e293b; font-size: 14px;">
                                            ${emp.name}
                                        </h6>
                                        <span style="font-size: 10px; color: #64748b; font-weight: 600;">${emp.memo_no}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div class="text-truncate" style="max-width: 65%; font-size: 12px; color: #475569;">
                                            <i class="bi bi-pin-map text-maroon me-1"></i>${emp.destination}
                                        </div>
                                        <div style="font-size: 11px; color: #059669; font-weight: 600; background: #ecfdf5; padding: 2px 8px; border-radius: 12px;">
                                            ${timeString}
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    });

                    for (let id in markers) {
                        if (!activeIDs.includes(id)) { map.removeLayer(markers[id]); delete markers[id]; }
                    }
                    listContainer.innerHTML = newListHTML;
                })
                .catch(err => console.error("Error fetching locations:", err));
        }

        function focusMap(lat, lng, id) {
            map.flyTo([lat, lng], 15, { animate: true, duration: 1 });
            if (markers[id]) { setTimeout(() => markers[id].openPopup(), 1000); }
        }

        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(loadActiveLocations, 500);
            setInterval(loadActiveLocations, 10000);
        });
    </script>
</body>

</html>