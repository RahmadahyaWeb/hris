<?php

use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component {
    public float $officeLatitude = -3.3186;
    public float $officeLongitude = 114.5944;
    public int $radius = 200;

    #[Computed]
    public function mapData(): array
    {
        return [
            'lat' => $this->officeLatitude,
            'lng' => $this->officeLongitude,
            'radius' => $this->radius,
        ];
    }
};
?>

<div wire:ignore x-data="leafletMap(@js($this->mapData))" x-init="init()" class="w-full flex flex-col gap-3">

    <div class="w-full h-[420px] sm:h-[500px]">
        <div id="map" class="w-full h-full rounded-lg"></div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 w-full">

        <button type="button" class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded-md"
            @click="getUserLocation()">
            Ambil Lokasi
        </button>

        <button type="button" class="w-full px-3 py-2 text-sm bg-green-600 text-white rounded-md" @click="fitRadius()">
            Lihat Area Kantor
        </button>

        <button type="button" class="w-full px-3 py-2 text-sm bg-gray-700 text-white rounded-md"
            @click="setView(lat,lng,17)">
            Center Kantor
        </button>

        <button type="button" class="w-full px-3 py-2 text-sm bg-yellow-600 text-white rounded-md"
            @click="checkAttendance()">
            Cek Radius
        </button>

        <button type="button" class="w-full px-3 py-2 text-sm bg-purple-600 text-white rounded-md"
            @click="attendance()">
            Presensi
        </button>

    </div>

</div>

@push('scripts')
    <script>
        function leafletMap(config) {
            return {

                map: null,
                marker: null,
                circle: null,
                userMarker: null,

                defaultLat: config.lat,
                defaultLng: config.lng,

                lat: config.lat,
                lng: config.lng,
                radius: config.radius,

                userLat: null,
                userLng: null,

                init() {

                    if (this.map !== null) return;

                    const container = L.DomUtil.get('map');

                    if (container !== null) {
                        container._leaflet_id = null;
                    }

                    this.map = L.map('map').setView([this.lat, this.lng], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    this.drawMarker();
                    this.drawCircle();

                    this.getUserLocation();
                },

                drawMarker() {

                    if (this.marker) {
                        this.map.removeLayer(this.marker);
                    }

                    this.marker = L.marker([this.lat, this.lng]).addTo(this.map);
                },

                drawCircle() {

                    if (this.circle) {
                        this.map.removeLayer(this.circle);
                    }

                    this.circle = L.circle([this.lat, this.lng], {
                        radius: this.radius,
                        color: '#2563eb',
                        fillColor: '#2563eb',
                        fillOpacity: 0.2
                    }).addTo(this.map);
                },

                setLocation(lat, lng) {

                    this.lat = lat;
                    this.lng = lng;

                    this.drawMarker();
                    this.drawCircle();
                },

                setRadius(radius) {

                    this.radius = radius;

                    this.drawCircle();
                    this.fitRadius();
                },

                setView(lat, lng, zoom = 15) {

                    this.map.setView([lat, lng], zoom);
                },

                fitRadius() {

                    if (!this.circle) return;

                    this.map.fitBounds(this.circle.getBounds(), {
                        padding: [30, 30],
                        maxZoom: 17
                    });
                },

                getUserLocation() {

                    if (!navigator.geolocation) return;

                    navigator.geolocation.getCurrentPosition((position) => {

                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        this.userLat = lat;
                        this.userLng = lng;

                        if (this.userMarker) {
                            this.map.removeLayer(this.userMarker);
                        }

                        this.userMarker = L.marker([lat, lng]).addTo(this.map);

                        this.map.setView([lat, lng], 17);

                    });
                },

                calculateDistance(lat1, lng1, lat2, lng2) {

                    const R = 6371000;

                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLng = (lng2 - lng1) * Math.PI / 180;

                    const a =
                        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(lat1 * Math.PI / 180) *
                        Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLng / 2) *
                        Math.sin(dLng / 2);

                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                    return R * c;
                },

                checkAttendance() {

                    if (!this.userLat || !this.userLng) {
                        alert('Lokasi user belum tersedia');
                        return;
                    }

                    const distance = this.calculateDistance(
                        this.userLat,
                        this.userLng,
                        this.defaultLat,
                        this.defaultLng
                    );

                    if (distance <= this.radius) {
                        alert('Anda berada dalam radius presensi');
                    } else {
                        alert('Anda berada di luar radius presensi');
                    }

                },

                attendance() {

                    if (!this.userLat || !this.userLng) {
                        alert('Lokasi user belum tersedia');
                        return;
                    }

                    const distance = this.calculateDistance(
                        this.userLat,
                        this.userLng,
                        this.defaultLat,
                        this.defaultLng
                    );

                    if (distance > this.radius) {
                        alert('Presensi gagal, di luar radius');
                        return;
                    }

                    console.log({
                        latitude: this.userLat,
                        longitude: this.userLng,
                        office_latitude: this.defaultLat,
                        office_longitude: this.defaultLng,
                        radius: this.radius,
                        distance: distance
                    });

                    alert('Presensi berhasil');

                }
            }
        }
    </script>
@endpush
