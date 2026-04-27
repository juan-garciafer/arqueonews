<div id="map" style="height: 500px;"></div>

{{-- 
@push('scripts')
    <script>
        const markers = @json($markers ?? []);
    </script>
@endpush --}}

<div id="map" style="height: 500px; width: 100%;"></div>

@push('scripts')
    <script>
        const markers = @json($markers ?? []);

        window.addEventListener('load', () => {

            if (typeof L === 'undefined') {
                console.error('Leaflet no cargado');
                return;
            }

            const map = L.map('map').setView([40, -3], 3);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            const createNumberIcon = (count) => {
                return L.divIcon({
                    className: 'custom-marker',
                    html: `
                    <div style="
                        background:#2563eb;
                        color:white;
                        border-radius:50%;
                        width:30px;
                        height:30px;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        font-size:12px;
                        font-weight:bold;
                        border:2px solid white;
                        box-shadow:0 2px 6px rgba(0,0,0,0.3);
                    ">
                        ${count}
                    </div>
                `,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
            };

            markers.forEach(m => {

                if (!m.lat || !m.lng) return;

                const noticiasLimitadas = (m.noticias || []).slice(0, 3);

                const noticiasHtml = noticiasLimitadas.map(n => `
                <div style="display:flex; gap:10px; margin-bottom:10px;">
                    <img 
                        src="${n.imagen}" 
                        onerror="this.onerror=null; this.src='/img/default.jpg';"
                        style="width:50px;height:50px;object-fit:cover;border-radius:6px;"
                    >
                    <div>
                        <a href="${n.url}" target="_blank" style="font-weight:bold;">
                            ${n.titulo}
                        </a>
                        <p style="margin:0;font-size:12px;">
                            ${n.descripcion?.substring(0, 70)}...
                        </p>
                    </div>
                </div>
            `).join('');

                const verMas = (m.noticias || []).length > 3 ?
                    `<button 
                    onclick="window.open('/pais/${m.id}', '_blank')"
                    style="width:100%;padding:6px;background:#2563eb;color:white;border:none;border-radius:6px;cursor:pointer;">
                    Ver todas las noticias (${m.noticias.length})
                </button>` :
                    '';

                L.marker([m.lat, m.lng], {
                        icon: createNumberIcon(m.count)
                    })
                    .addTo(map)
                    .bindPopup(`
                <div style="max-width:320px;">
                    <h4>${m.nombre}</h4>
                    ${noticiasHtml || '<p>No hay noticias</p>'}
                    ${verMas}
                </div>
            `);
            });

        });
    </script>
@endpush

