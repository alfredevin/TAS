<?php include './../config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include '../template/header.php'; ?>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
</head>

<body style="background-color: #f8fafc;">
    <?php include '../template/navbar.php'; ?>
    <?php include '../template/sidebar.php'; ?>

    <style>
        /* Card Styling */
        .history-card {
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border: 1px solid #edf2f7;
            background: #fff;
            padding: 25px;
        }

        .table-header-custom {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(30, 60, 114, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Filter Area */
        .filter-area {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }

        .form-select-custom {
            border-radius: 10px;
            padding: 10px 15px;
            border-color: #e2e8f0;
            min-width: 200px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        }

        /* Modern Table Adjustments */
        .table-custom {
            border-collapse: separate;
            border-spacing: 0 8px;
            margin-top: -8px;
        }

        .table-custom thead th {
            color: #64748b;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            border: none;
            padding-bottom: 0;
        }

        .table-custom tbody tr {
            background: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            border-radius: 12px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .table-custom tbody tr td {
            vertical-align: middle;
            font-size: 14px;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            padding: 16px 15px;
        }

        .table-custom tbody tr td:first-child {
            border-left: 1px solid #f1f5f9;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .table-custom tbody tr td:last-child {
            border-right: 1px solid #f1f5f9;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .table-custom tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
            border-color: #e2e8f0;
            z-index: 1;
            position: relative;
        }

        .emp-photo {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }

        /* DataTables Customization */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 15px;
            margin-left: 10px;
            outline: none;
            transition: 0.3s;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 5px 10px;
        }

        .page-item.active .page-link {
            background-color: #1e3c72;
            border-color: #1e3c72;
        }

        .page-link {
            color: #1e3c72;
            border-radius: 8px;
            margin: 0 3px;
        }

        /* Map Modal Styling */
        .modal-map-container {
            height: 450px;
            width: 100%;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            background: #e2e8f0;
        }

        #modalMap {
            height: 100%;
            width: 100%;
            z-index: 1;
        }

        .leaflet-routing-container {
            display: none !important;
        }

        .route-marker {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .bg-start {
            background-color: #3b82f6;
        }

        .bg-dest {
            background-color: #10b981;
        }

        .bg-end {
            background-color: #800000;
        }

        .duration-badge {
            display: inline-flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #475569;
            font-weight: 700;
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 8px;
        }
    </style>

    <main id="main" class="main">
        <div class="pagetitle mb-4">
            <h1 class="fw-bolder" style="color: #1e293b; font-size: 1.8rem;">Travel Archives</h1>
            <nav>
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-muted">Admin</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary">Completed Travels</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="history-card">

                        <div class="table-header-custom">
                            <div>
                                <h4 class="m-0 fw-bolder"><i class="bi bi-journal-check me-2"></i>Completed Route Logs
                                </h4>
                                <p class="m-0 mt-1 small" style="opacity: 0.8;">View historical GPS trails mapped
                                    directly to road networks.</p>
                            </div>
                        </div>

                        <div class="filter-area shadow-sm">
                            <div class="fw-bold text-muted small text-uppercase me-2"><i
                                    class="bi bi-funnel-fill me-1"></i> Filters:</div>

                            <!-- <select id="typeFilter" class="form-select form-select-custom">
                                <option value="">All Travel Types</option>
                                <option value="Travel Auth">Travel Authority (TA)</option>
                                <option value="Pass Slip">Pass Slip (PS)</option>
                            </select> -->

                            <select id="deptFilter" class="form-select form-select-custom">
                                <option value="">All Departments</option>
                            </select>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-custom w-100" id="historyTable">
                                <thead>
                                    <tr>
                                        <th>Employee Info</th>
                                        <th>Travel Type</th>
                                        <th>Destination</th>
                                        <th>Date & Duration</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center py-4">Loading data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 p-4 pb-3" style="background: #1e293b; color: white;">
                    <div>
                        <h5 class="modal-title fw-bold mb-1"><i class="bi bi-map me-2"></i>Road-Snap Route Tracker</h5>
                        <p class="mb-0 small text-white-50" id="modal-emp-details"></p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <div class="d-flex justify-content-center gap-4 mb-3 small fw-bold text-muted bg-white py-2 rounded-pill shadow-sm mx-auto"
                        style="max-width: 400px;">
                        <div class="d-flex align-items-center">
                            <div class="route-marker bg-start me-2" style="width:14px;height:14px;"></div> Start
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="route-marker bg-dest me-2" style="width:14px;height:14px;"></div> Destination
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="route-marker bg-end me-2" style="width:14px;height:14px;"></div> End
                        </div>
                    </div>

                    <div class="modal-map-container shadow-sm border border-light">
                        <div id="modalMap"></div>
                        <div id="routingLoader"
                            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 1000; display: none; align-items: center; justify-content: center; flex-direction: column;">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <span class="fw-bold text-primary">Computing road network...</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light p-3 pt-0 justify-content-between">
                    <div id="modal-duration-badge"
                        class="duration-badge bg-white shadow-sm border-0 px-3 py-2 text-dark"></div>
                    <button type="button" class="btn btn-secondary rounded-pill px-5 fw-bold"
                        data-bs-dismiss="modal">Close Map</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../template/footer.php'; ?>
    <?php include '../template/script.php'; ?>

    <script>
        var modalMap = null;
        var routingControl = null;
        var dataTableInstance = null;

        function createStepIcon(bgClass, number) {
            return L.divIcon({
                className: 'custom-step-marker',
                html: `<div class="route-marker ${bgClass}">${number}</div>`,
                iconSize: [26, 26], iconAnchor: [13, 13], popupAnchor: [0, -10]
            });
        }

        // Helper: SMART Time Difference Calculator
        function calculateDuration(startTimeStr, endTimeStr) {
            if (!startTimeStr || startTimeStr === 'null' || !startTimeStr.includes('|')) return "Legacy Record";
            if (!endTimeStr || endTimeStr === 'null' || !endTimeStr.includes('|')) return "Trip Ongoing";

            try {
                let startRaw = startTimeStr.split('|')[1].trim();
                let endRaw = endTimeStr.split('|')[1].trim();

                let baseDate = new Date().toLocaleDateString('en-US');
                let d1 = new Date(baseDate + " " + startRaw);
                let d2 = new Date(baseDate + " " + endRaw);

                if (isNaN(d1) || isNaN(d2)) return "Time Format Error";

                let diffMs = d2 - d1;
                if (diffMs < 0) { diffMs += 24 * 60 * 60 * 1000; }

                let diffMins = Math.round(diffMs / 60000);
                let hrs = Math.floor(diffMins / 60);
                let mins = diffMins % 60;

                if (hrs === 0) return `${mins} mins`;
                if (mins === 0) return `${hrs} hrs`;
                return `${hrs}h ${mins}m`;
            } catch (e) {
                return "Calc Error";
            }
        }

        function loadTravelHistory() {
            fetch('get_travel_route_history.php')
                .then(res => res.json())
                .then(data => {
                    populateDepartmentFilter(data);
                    renderTable(data);
                })
                .catch(err => console.error("Error fetching history:", err));
        }

        function populateDepartmentFilter(data) {
            let deptSelect = document.getElementById('deptFilter');
            let departments = [...new Set(data.map(item => item.department_name ? item.department_name.trim() : 'Unassigned'))].filter(Boolean);

            deptSelect.innerHTML = '<option value="">All Departments</option>';

            departments.forEach(dept => {
                let option = document.createElement('option');
                option.value = dept;
                option.text = dept;
                deptSelect.appendChild(option);
            });
        }

        function renderTable(data) {
            const tbody = document.getElementById('historyTableBody');
            let rowsHTML = '';

            data.forEach(emp => {
                let typeBadge = emp.travel_type === 'PS'
                    ? `<span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-ticket-detailed me-1"></i> Pass Slip</span>`
                    : `<span class="badge bg-primary px-3 py-2 rounded-pill"><i class="bi bi-briefcase me-1"></i> Travel Auth</span>`;

                let avatarSrc = (emp.photo && emp.photo !== '') ? '../uploads/' + emp.photo : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                let travelDateFmt = new Date(emp.date_of_travel).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

                let durationStr = calculateDuration(emp.loc1, emp.loc4);
                let durationBadge = `<div class="duration-badge mt-1"><i class="bi bi-stopwatch text-danger me-1"></i> ${durationStr}</div>`;

                let routeDataEncoded = encodeURIComponent(JSON.stringify([emp.loc1, emp.loc2, emp.loc3, emp.loc4]));
                let safeName = emp.name.replace(/'/g, "\\'");
                let safeDest = emp.destination.replace(/'/g, "\\'");
                let safeDept = emp.department_name ? emp.department_name.trim() : 'Unassigned';

                // 🟢 FIX: Tinanggal na ang "data-search" sa <td> para basahin ng normal ng DataTables
                rowsHTML += `
                    <tr onclick="openMapModal('${safeName}', '${safeDest}', '${emp.travel_type}', '${routeDataEncoded}', '${durationStr}')" title="Click to view route">
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${avatarSrc}" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'" class="emp-photo me-3 shadow-sm">
                                <div>
                                    <div class="fw-bolder text-dark" style="font-size:15px;">${emp.name}</div>
                                    <div class="text-muted small"><i class="bi bi-building me-1"></i><span class="dept-text">${safeDept}</span></div>
                                </div>
                            </div>
                        </td>
                        <td>${typeBadge}</td>
                        <td><div class="fw-bold text-secondary text-truncate" style="max-width: 250px;">${emp.destination}</div></td>
                        <td>
                            <div class="text-dark fw-bold"><i class="bi bi-calendar-event me-1 text-muted"></i> ${travelDateFmt}</div>
                            ${durationBadge}
                        </td>
                        <td class="text-center">
                            <button class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" style="pointer-events: none;"> <i class="bi bi-map-fill me-1"></i> Map </button>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = rowsHTML;

            if (dataTableInstance) { dataTableInstance.destroy(); }

            dataTableInstance = $('#historyTable').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [],
                language: { search: "_INPUT_", searchPlaceholder: "Search records..." },
                columnDefs: [{ orderable: false, targets: 4 }]
            });

            // 🟢 FIXED FILTERING SCRIPT 🟢
            // Simpleng text matching. Hahanapin ng DataTables ang "Travel Auth" o "Pass Slip" sa column 1.
            $('#typeFilter').off('change').on('change', function () {
                let val = $(this).val();
                dataTableInstance.column(1).search(val).draw();
            });

            // Hahanapin ng DataTables ang text ng Department sa column 0
            $('#deptFilter').off('change').on('change', function () {
                let val = $(this).val();
                dataTableInstance.column(0).search(val).draw();
            });
        }

        // TRIGGER MODAL AND DRAW "ROAD SNAP" ROUTE
        function openMapModal(name, destination, type, routeDataEncoded, duration) {
            let routeData = JSON.parse(decodeURIComponent(routeDataEncoded));
            let fullType = (type === 'PS') ? "Pass Slip" : "Travel Authority";

            document.getElementById('modal-emp-details').innerHTML = `<b>${name}</b> &nbsp;•&nbsp; ${fullType} to ${destination}`;
            document.getElementById('modal-duration-badge').innerHTML = `<i class="bi bi-stopwatch-fill text-danger me-2 fs-5"></i> Total Travel Time: <b class="ms-1 text-dark fs-6">${duration}</b>`;

            let myModal = new bootstrap.Modal(document.getElementById('mapModal'));
            myModal.show();

            document.getElementById('routingLoader').style.display = 'flex';

            setTimeout(() => {
                if (!modalMap) {
                    modalMap = L.map('modalMap', { zoomControl: false }).setView([13.3858, 121.9563], 11);
                    L.control.zoom({ position: 'topright' }).addTo(modalMap);
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(modalMap);
                } else {
                    modalMap.invalidateSize();
                }

                if (routingControl) {
                    modalMap.removeControl(routingControl);
                    routingControl = null;
                }

                let waypoints = [];
                let labels = ['1. Departed', '2. Arrived', '3. Left Dest', '4. Returned'];
                let classes = ['bg-start', 'bg-dest', 'bg-dest', 'bg-end'];

                routeData.forEach((locStr, index) => {
                    if (locStr && locStr !== 'null' && locStr.includes(',')) {
                        let parts = locStr.split('|');
                        let coords = parts[0].split(',');
                        let timeStr = parts.length > 1 ? parts[1] : '';

                        let lat = parseFloat(coords[0]);
                        let lng = parseFloat(coords[1]);

                        if (!isNaN(lat) && !isNaN(lng)) {
                            let wLngLat = L.latLng(lat, lng);
                            waypoints.push(wLngLat);
                            wLngLat.customIndex = index;
                            wLngLat.customTime = timeStr;
                        }
                    }
                });

                if (waypoints.length > 1) {
                    routingControl = L.Routing.control({
                        waypoints: waypoints,
                        router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' }),
                        lineOptions: { styles: [{ color: '#ef4444', opacity: 0.8, weight: 5, dashArray: '10, 10' }] },
                        createMarker: function (i, waypoint, n) {
                            let idx = waypoint.latLng.customIndex;
                            let timeTxt = waypoint.latLng.customTime ? `<br><small class="text-muted"><i class="bi bi-clock me-1"></i>${waypoint.latLng.customTime}</small>` : '';
                            let icon = createStepIcon(classes[idx], idx + 1);

                            return L.marker(waypoint.latLng, { icon: icon }).bindPopup(`<div class="text-center fw-bold">${labels[idx]}${timeTxt}</div>`);
                        },
                        fitSelectedRoutes: true,
                        show: false
                    }).addTo(modalMap);

                    routingControl.on('routesfound', function () { document.getElementById('routingLoader').style.display = 'none'; });
                    routingControl.on('routingerror', function () {
                        document.getElementById('routingLoader').style.display = 'none';
                        Swal.fire('Routing Error', 'Could not snap to roads. Using straight lines instead.', 'warning');
                    });

                } else if (waypoints.length === 1) {
                    document.getElementById('routingLoader').style.display = 'none';
                    let idx = waypoints[0].customIndex;
                    let icon = createStepIcon(classes[idx], 1);
                    L.marker(waypoints[0], { icon: icon }).addTo(modalMap);
                    modalMap.flyTo(waypoints[0], 16, { duration: 1.5 });
                } else {
                    document.getElementById('routingLoader').style.display = 'none';
                    Swal.fire('No GPS Data', 'Coordinates for this trip were not captured properly.', 'info');
                }

            }, 400);
        }

        document.getElementById('mapModal').addEventListener('shown.bs.modal', function () {
            if (modalMap) { modalMap.invalidateSize(); }
        });

        document.addEventListener("DOMContentLoaded", loadTravelHistory);
    </script>
</body>

</html>