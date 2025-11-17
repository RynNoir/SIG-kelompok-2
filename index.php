<?php
/**
 * Homepage - Sistem Zonasi SMP Padang
 * Fitur: Peta interaktif, pencarian, foto sekolah
 */
require_once 'koneksi.php';

// Ambil semua sekolah dari database
$query = "SELECT * FROM sekolah ORDER BY nama ASC";
$result = mysqli_query($conn, $query);
$sekolah_list = [];

while ($row = mysqli_fetch_assoc($result)) {
    $sekolah_list[] = $row;
}

// Hitung statistik untuk info
$total_sekolah = count($sekolah_list);
$total_kuota = array_sum(array_column($sekolah_list, 'kuota'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Zonasi SMP Padang - Temukan SMP Negeri terdekat berdasarkan zona tempat tinggal">
    <meta name="keywords" content="SMP Padang, Zonasi, PPDB, Sekolah Padang">
    <title>Sistem Zonasi SMP Padang - Cari Sekolah Terdekat</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <style>
        /* Map Container */
        #map {
            height: 550px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        /* School Card */
        .school-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .school-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-color: #0d6efd;
        }
        
        .school-card.active {
            border-color: #0d6efd;
            background-color: #f0f7ff;
        }
        
        /* School Image */
        .school-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #e0e0e0;
        }
        
        .school-img-placeholder {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            color: white;
            font-size: 1.5rem;
        }
        
        /* School List Container */
        .school-list {
            max-height: 550px;
            overflow-y: auto;
            padding-right: 8px;
        }
        
        /* Custom Scrollbar */
        .school-list::-webkit-scrollbar {
            width: 8px;
        }
        
        .school-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .school-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .school-list::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Badge Custom */
        .badge-in-zone {
            background-color: #198754;
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        
        .badge-out-zone {
            background-color: #ffc107;
            color: #000;
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem 0;
        }
        
        /* Stats Box */
        .stats-box {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        /* Loading Spinner */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #map {
                height: 400px;
            }
            
            .school-list {
                max-height: 400px;
            }
            
            .hero-section {
                padding: 1.5rem 0;
            }
            
            .hero-section h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-geo-alt-fill"></i> Sistem Zonasi SMP
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-house-fill"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">
                            <i class="bi bi-shield-lock-fill"></i> Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">
                        <i class="bi bi-pin-map-fill"></i> Cari SMP Terdekat di Zona Anda
                    </h1>
                    <p class="lead mb-0">
                        Temukan SMP Negeri di Kota Padang berdasarkan lokasi tempat tinggal Anda dengan sistem zonasi PPDB
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <div class="stats-box">
                        <h3 class="text-primary mb-1"><?php echo $total_sekolah; ?></h3>
                        <p class="text-muted small mb-0">SMP Negeri</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-4">
        
        <!-- Search Bar -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="bi bi-search"></i> Cari Sekolah Berdasarkan Lokasi
                </h5>
                <form id="searchForm" onsubmit="return false;">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-geo-alt"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="searchAddress" 
                                       placeholder="Masukkan alamat Anda (contoh: Jl. Proklamasi, Padang)"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-select" id="filterKecamatan">
                                <option value="">Semua Kecamatan</option>
                                <option value="Lubuk Begalung">Lubuk Begalung</option>
                                <option value="Kuranji">Kuranji</option>
                                <option value="Padang Barat">Padang Barat</option>
                                <option value="Pauh">Pauh</option>
                                <option value="Lubuk Kilangan">Lubuk Kilangan</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <button type="button" 
                                    onclick="cariSekolah()" 
                                    class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Cari Sekolah
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="showZones" 
                                   checked>
                            <label class="form-check-label" for="showZones">
                                Tampilkan zona sekolah (radius 2 km)
                            </label>
                        </div>
                        <button type="button" 
                                onclick="getCurrentLocation()" 
                                class="btn btn-success btn-sm ms-2">
                            <i class="bi bi-crosshair"></i> Gunakan Lokasi Saya
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Map and List -->
        <div class="row">
            
            <!-- Map Column -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-map"></i> Peta Lokasi Sekolah
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="map"></div>
                    </div>
                    <div class="card-footer bg-white text-muted small">
                        <i class="bi bi-info-circle"></i> 
                        Klik marker pada peta untuk melihat informasi sekolah
                    </div>
                </div>
            </div>

            <!-- School List Column -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-building"></i> 
                            <span id="listTitle">Daftar SMP (<?php echo $total_sekolah; ?>)</span>
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        
                        <!-- Loading Spinner -->
                        <div id="loadingSpinner" class="loading-spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat data sekolah...</p>
                        </div>
                        
                        <!-- School List -->
                        <div id="schoolList" class="school-list">
                            <?php foreach ($sekolah_list as $sekolah): ?>
                            <div class="school-card mb-3" 
                                 data-id="<?php echo $sekolah['id']; ?>"
                                 onclick="selectSchool(<?php echo $sekolah['id']; ?>)">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-start">
                                        
                                        <!-- Foto Sekolah -->
                                        <?php if (!empty($sekolah['foto']) && file_exists('uploads/sekolah/' . $sekolah['foto'])): ?>
                                            <img src="uploads/sekolah/<?php echo htmlspecialchars($sekolah['foto']); ?>" 
                                                 alt="<?php echo htmlspecialchars($sekolah['nama']); ?>"
                                                 class="school-img me-3">
                                        <?php else: ?>
                                            <div class="school-img-placeholder me-3">
                                                <i class="bi bi-building"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Info Sekolah -->
                                        <div class="flex-fill">
                                            <h6 class="mb-1 fw-bold">
                                                <?php echo htmlspecialchars($sekolah['nama']); ?>
                                            </h6>
                                            <p class="text-muted small mb-2">
                                                <i class="bi bi-geo-alt"></i> 
                                                <?php echo htmlspecialchars($sekolah['kecamatan']); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-award"></i> 
                                                    <?php echo $sekolah['akreditasi']; ?>
                                                </span>
                                                <small class="text-muted">
                                                    <i class="bi bi-people"></i> 
                                                    <?php echo $sekolah['kuota']; ?> siswa
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center text-muted">
            <p class="mb-2">
                <i class="bi bi-geo-alt-fill text-primary"></i> 
                <strong>Sistem Zonasi SMP Padang</strong>
            </p>
            <p class="small mb-0">
                Membantu calon siswa menemukan SMP Negeri sesuai zona tempat tinggal
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // ===================================
        // GLOBAL VARIABLES
        // ===================================
        
        // Data sekolah dari PHP
        const sekolahData = <?php echo json_encode($sekolah_list); ?>;
        
        // Map variables
        let map;
        let userMarker = null;
        let schoolMarkers = [];
        let zoneCircles = [];
        let selectedSchoolId = null;
        
        // ===================================
        // INITIALIZE MAP
        // ===================================
        
        function initMap() {
            // Create map centered on Padang
            map = L.map('map').setView([-0.9371, 100.3600], 13);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
            }).addTo(map);
            
            // Display all schools on map
            displaySchoolsOnMap();
        }
        
        // ===================================
        // DISPLAY SCHOOLS ON MAP
        // ===================================
        
        function displaySchoolsOnMap() {
            // Clear existing markers and zones
            clearMap();
            
            const showZones = document.getElementById('showZones').checked;
            const filterKec = document.getElementById('filterKecamatan').value;
            
            // Filter schools
            const filteredSchools = filterKec 
                ? sekolahData.filter(s => s.kecamatan === filterKec)
                : sekolahData;
            
            filteredSchools.forEach(school => {
                // Add zone circle
                if (showZones) {
                    const circle = L.circle([school.latitude, school.longitude], {
                        color: '#0d6efd',
                        fillColor: '#0d6efd',
                        fillOpacity: 0.1,
                        radius: parseInt(school.radius),
                        weight: 2,
                        dashArray: '5, 5'
                    }).addTo(map);
                    zoneCircles.push(circle);
                }
                
                // Add marker
                const marker = L.marker([school.latitude, school.longitude])
                    .bindPopup(createPopupContent(school))
                    .addTo(map);
                
                marker.schoolId = school.id;
                marker.on('click', function() {
                    selectSchool(school.id);
                });
                
                schoolMarkers.push(marker);
            });
            
            // Update list title
            updateListTitle(filteredSchools.length);
        }
        
        // ===================================
        // CREATE POPUP CONTENT
        // ===================================
        
        function createPopupContent(school) {
            return `
                <div class="popup-content" style="min-width: 200px;">
                    <h6 class="fw-bold mb-2">${school.nama}</h6>
                    <p class="small mb-1">
                        <i class="bi bi-geo-alt"></i> ${school.alamat}
                    </p>
                    <p class="small mb-1">
                        <i class="bi bi-pin-map"></i> ${school.kecamatan}
                    </p>
                    <p class="small mb-2">
                        <i class="bi bi-award"></i> Akreditasi: 
                        <strong>${school.akreditasi}</strong>
                    </p>
                    <a href="detail.php?id=${school.id}" 
                       class="btn btn-primary btn-sm w-100 text-white">
                        <i class="bi bi-info-circle"></i> Lihat Detail
                    </a>
                </div>
            `;
        }
        
        // ===================================
        // SEARCH FUNCTION
        // ===================================
        
        function cariSekolah() {
            const address = document.getElementById('searchAddress').value.trim();
            
            if (!address) {
                alert('Silakan masukkan alamat terlebih dahulu!');
                document.getElementById('searchAddress').focus();
                return;
            }
            
            // Simulasi geocoding (untuk demo)
            // Dalam production, gunakan API Nominatim
            const userLat = -0.9371 + (Math.random() - 0.5) * 0.04;
            const userLng = 100.3600 + (Math.random() - 0.5) * 0.04;
            
            setUserLocation(userLat, userLng);
        }
        
        // ===================================
        // GET CURRENT LOCATION
        // ===================================
        
        function getCurrentLocation() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung Geolocation!');
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    setUserLocation(
                        position.coords.latitude,
                        position.coords.longitude
                    );
                },
                function(error) {
                    alert('Tidak dapat mengakses lokasi Anda. Silakan cari alamat secara manual.');
                    console.error('Geolocation error:', error);
                }
            );
        }
        
        // ===================================
        // SET USER LOCATION
        // ===================================
        
        function setUserLocation(lat, lng) {
            // Remove old user marker
            if (userMarker) {
                map.removeLayer(userMarker);
            }
            
            // Add new user marker (green)
            userMarker = L.marker([lat, lng], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map);
            
            userMarker.bindPopup('<strong>Lokasi Anda</strong>').openPopup();
            
            // Pan map to user location
            map.setView([lat, lng], 14);
            
            // Calculate distances and update list
            calculateDistances(lat, lng);
        }
        
        // ===================================
        // CALCULATE DISTANCES
        // ===================================
        
        function calculateDistances(userLat, userLng) {
            const filterKec = document.getElementById('filterKecamatan').value;
            
            // Calculate distance for each school
            let schoolsWithDistance = sekolahData.map(school => {
                const distance = hitungJarak(
                    userLat, userLng,
                    school.latitude, school.longitude
                );
                
                return {
                    ...school,
                    distance: distance,
                    distanceFormatted: formatJarak(distance),
                    inZone: distance <= parseInt(school.radius)
                };
            });
            
            // Filter by kecamatan
            if (filterKec) {
                schoolsWithDistance = schoolsWithDistance.filter(s => s.kecamatan === filterKec);
            }
            
            // Sort by distance
            schoolsWithDistance.sort((a, b) => a.distance - b.distance);
            
            // Update school list
            displaySchoolList(schoolsWithDistance, true);
        }
        
        // ===================================
        // DISPLAY SCHOOL LIST
        // ===================================
        
        function displaySchoolList(schools, showDistance = false) {
            const listContainer = document.getElementById('schoolList');
            
            if (schools.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-3">Tidak ada sekolah ditemukan</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            schools.forEach(school => {
                const fotoHtml = (school.foto && school.foto !== '') 
                    ? `<img src="uploads/sekolah/${school.foto}" alt="${school.nama}" class="school-img me-3">`
                    : `<div class="school-img-placeholder me-3"><i class="bi bi-building"></i></div>`;
                
                const distanceBadge = showDistance && school.distance 
                    ? `<span class="badge ${school.inZone ? 'badge-in-zone' : 'badge-out-zone'} ms-2">
                            ${school.inZone ? 'Dalam Zona' : 'Luar Zona'}
                       </span>`
                    : '';
                
                const distanceInfo = showDistance && school.distance
                    ? `<small class="text-primary fw-bold">
                            <i class="bi bi-rulers"></i> ${school.distanceFormatted}
                       </small>`
                    : `<small class="text-muted">
                            <i class="bi bi-people"></i> ${school.kuota} siswa
                       </small>`;
                
                html += `
                    <div class="school-card mb-3" 
                         data-id="${school.id}"
                         onclick="selectSchool(${school.id})">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start">
                                ${fotoHtml}
                                <div class="flex-fill">
                                    <h6 class="mb-1 fw-bold">
                                        ${school.nama}
                                        ${distanceBadge}
                                    </h6>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt"></i> ${school.kecamatan}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary">
                                            <i class="bi bi-award"></i> ${school.akreditasi}
                                        </span>
                                        ${distanceInfo}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            listContainer.innerHTML = html;
            updateListTitle(schools.length);
        }
        
        // ===================================
        // SELECT SCHOOL
        // ===================================
        
        function selectSchool(schoolId) {
            // Hilangkan highlight card sebelumnya
            document.querySelectorAll('.school-card').forEach(card => {
                card.classList.remove('active');
            });

            // Tambahkan highlight pada card yang diklik
            const card = document.querySelector(`[data-id="${schoolId}"]`);
            if (card) {
                card.classList.add('active');
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            // Ambil data sekolah
            const school = sekolahData.find(s => s.id == schoolId);
            if (!school) return;

            // Zoom ke lokasi sekolah
            map.flyTo([school.latitude, school.longitude], 16, {
                duration: 1.2,
                easeLinearity: 0.25
            });

            // Cari marker sekolah
            const marker = schoolMarkers.find(m => m.schoolId == schoolId);

            if (marker) {
                // BUAT ULANG POPUP SETIAP KALI DIKLIK DARI LIST
                const popupHTML = createPopupContent(school);
                marker.bindPopup(popupHTML).openPopup();

                setTimeout(() => {
                    marker.setIcon(originalIcon);
                }, 2500);
            }

            selectedSchoolId = schoolId;
        }

        
        // ===================================
        // HELPER FUNCTIONS
        // ===================================
        
        // Calculate distance (Haversine formula)
        function hitungJarak(lat1, lon1, lat2, lon2) {
            const R = 6371000; // Earth radius in meters
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lon2 - lon1) * Math.PI / 180;

            const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                    Math.cos(φ1) * Math.cos(φ2) *
                    Math.sin(Δλ/2) * Math.sin(Δλ/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

            return R * c;
        }
        
        // Format distance
        function formatJarak(meter) {
            if (meter < 1000) {
                return Math.round(meter) + ' m';
            }
            return (meter / 1000).toFixed(2) + ' km';
        }
        
        // Clear map
        function clearMap() {
            schoolMarkers.forEach(marker => map.removeLayer(marker));
            zoneCircles.forEach(circle => map.removeLayer(circle));
            schoolMarkers = [];
            zoneCircles = [];
        }
        
        // Update list title
        function updateListTitle(count) {
            document.getElementById('listTitle').textContent = `Daftar SMP (${count})`;
        }
        
        // ===================================
        // EVENT LISTENERS
        // ===================================
        
        // Toggle zones
        document.getElementById('showZones').addEventListener('change', function() {
            displaySchoolsOnMap();
        });
        
        // Filter kecamatan
        document.getElementById('filterKecamatan').addEventListener('change', function() {
            displaySchoolsOnMap();
            
            const filterValue = this.value;
            if (filterValue) {
                const filtered = sekolahData.filter(s => s.kecamatan === filterValue);
                displaySchoolList(filtered);
            } else {
                displaySchoolList(sekolahData);
            }
        });
        
        // Enter key on search
        document.getElementById('searchAddress').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                cariSekolah();
            }
        });
        
        // ===================================
        // INITIALIZE ON PAGE LOAD
        // ===================================
        
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            console.log('Sistem Zonasi SMP Padang loaded successfully!');
            console.log('Total sekolah:', sekolahData.length);
        });
    </script>

</body>
</html>