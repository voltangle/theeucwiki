var map = L.map('map');

L.setView([30.0, -20.0], 3);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> and all contributors of The EUC Wiki'
}).addTo(map);
