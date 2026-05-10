@props(['markers' => []])

<div id="map-wrapper" style="position: relative; width: 100%; height: 220px; border-radius: 8px; overflow: hidden;">
    <button
        id="map-toggle-size"
        type="button"
        aria-label="Ampliar mapa"
        style="position:absolute;top:8px;right:8px;z-index:1000;background:white;border:1px solid #d1d5db;border-radius:6px;padding:4px 8px;cursor:pointer;font-size:14px;line-height:1;"
    >
        <img src="/img/ampliar.png" alt="Ampliar mapa" style="width:18px;height:18px;display:block;">
    </button>
    <div id="map" style="height: 100%; width: 100%;"></div>
</div>

@push('scripts')
    <script>
        const markers = @json($markers ?? []);
        const isAuth = @json(auth()->check());

        console.log('Markers data:', markers);
        console.log('isAuth:', isAuth);

        window.addEventListener('load', () => {

            console.log('L:', typeof L);

            if (typeof L === 'undefined') {
                console.error('Leaflet no cargado');
                return;
            }

            const mapWrapper = document.getElementById('map-wrapper');
            const toggleButton = document.getElementById('map-toggle-size');
            const map = L.map('map').setView([40, -3], 3);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            console.log('Map created');

            console.log('Adding markers:', markers.length);

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

                const noticiasHtml = noticiasLimitadas.map((n, idx) => `
                <div style="display:flex; gap:10px; margin-bottom:15px; padding-bottom:10px; border-bottom: 1px solid #e5e7eb;">
                    <img 
                        src="${n.imagen}" 
                        onerror="this.style.display='none';"
                        style="width:50px;height:50px;object-fit:cover;border-radius:6px;flex-shrink:0;"
                    >
                    <div style="flex:1;">
                        <a href="${n.url}" target="_blank" style="font-weight:bold; color:#2563eb; text-decoration:none;">
                            ${n.titulo}
                        </a>
                        <p style="margin:0;font-size:12px; color:#666;">
                            ${n.descripcion?.substring(0, 70)}...
                        </p>
                        <div style="display:flex; gap:6px; margin-top:6px;">
                            <button 
                                onclick="compartirDesdePopup('${n.url}', '${n.titulo.replace(/'/g, "\\'")}')"
                                style="padding:4px 8px; background:#10b981; color:white; border:none; border-radius:4px; cursor:pointer; font-size:11px; flex:1;"
                            >
                                Compartir
                            </button>
                            <button 
                                onclick="${isAuth ? `guardarDesdePopup(${n.id})` : `window.location.href='/login'` }"
                                style="padding:4px 8px; background:#3b82f6; color:white; border:none; border-radius:4px; cursor:pointer; font-size:11px; flex:1;"
                            >
                                ${isAuth ? 'Guardar' : 'Iniciar sesión'}
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

                const verMas = (m.noticias || []).length > 3 ?
                    `<button 
                    onclick="window.open('/pais/${m.id}', '_blank')"
                    style="width:100%;padding:8px;background:#2563eb;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:500;margin-top:10px;">
                    Ver todas las noticias (${m.noticias.length})
                </button>` :
                    '';

                L.marker([m.lat, m.lng], {
                        icon: createNumberIcon(m.count)
                    })
                    .addTo(map)
                    .bindPopup(`
                <div style="max-width:350px;">
                    <h4 style="margin-top:0; color:#1f2937; font-size:16px;">${m.nombre}</h4>
                    ${noticiasHtml || '<p>No hay noticias</p>'}
                    ${verMas}
                </div>
            `);
            });

            let isExpanded = false;
            toggleButton?.addEventListener('click', () => {
                isExpanded = !isExpanded;

                if (isExpanded) {
                    mapWrapper.style.position = 'fixed';
                    mapWrapper.style.inset = '20px';
                    mapWrapper.style.height = 'auto';
                    mapWrapper.style.zIndex = '99998';
                    toggleButton.textContent = '⤡';
                    toggleButton.setAttribute('aria-label', 'Reducir mapa');
                } else {
                    mapWrapper.style.position = 'relative';
                    mapWrapper.style.inset = 'auto';
                    mapWrapper.style.height = '220px';
                    mapWrapper.style.zIndex = 'auto';
                    toggleButton.innerHTML = '<img src="/img/ampliar.png" alt="Ampliar mapa" style="width:18px;height:18px;display:block;">';
                    toggleButton.setAttribute('aria-label', 'Ampliar mapa');
                }

                setTimeout(() => map.invalidateSize(), 50);
            });

        });

        // Función para compartir desde el popup
        function compartirDesdePopup(url, titulo) {
            const opciones = [{
                    name: 'Copiar URL',
                    action: () => copiar(url)
                },
                {
                    name: 'Twitter/X',
                    action: () => window.open(
                        `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(titulo)}`
                    )
                },
                {
                    name: 'Facebook',
                    action: () => window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`)
                },
                {
                    name: 'WhatsApp',
                    action: () => window.open(`https://wa.me/?text=${encodeURIComponent(titulo + ' ' + url)}`)
                },
                {
                    name: 'LinkedIn',
                    action: () => window.open(
                        `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`)
                }
            ];

            const menu = document.createElement('div');
            menu.style.position = 'fixed';
            menu.style.inset = '0';
            menu.style.zIndex = '99999';
            menu.style.backgroundColor = 'rgba(0,0,0,0.45)';
            menu.style.display = 'flex';
            menu.style.alignItems = 'center';
            menu.style.justifyContent = 'center';

            menu.innerHTML = `
        <div class="bg-white rounded-xl p-4 w-72 space-y-2 shadow-lg" style="max-width: 90%;">
            <h3 class="font-bold text-lg mb-2">Compartir</h3>
            ${opciones.map((o, i) => `
                        <button data-index="${i}" class="w-full text-left px-3 py-2 rounded hover:bg-gray-100">
                            ${o.name}
                        </button>
                    `).join('')}
            <button id="cerrarMenu" class="w-full mt-2 text-sm text-gray-500">Cancelar</button>
        </div>
    `;

            document.body.appendChild(menu);

            menu.querySelectorAll('button[data-index]').forEach(btn => {
                btn.addEventListener('click', () => {
                    opciones[btn.dataset.index].action();
                    menu.remove();
                });
            });

            menu.querySelector('#cerrarMenu').onclick = () => menu.remove();
        }

        function copiar(texto) {
            return navigator.clipboard?.writeText(texto)
                .then(() => mostrarToast('URL copiada al portapapeles', 'ok'))
                .catch(() => mostrarToast('Error al copiar la URL', 'error'));
        }

        function mostrarToast(mensaje, tipo = 'ok') {
            const toast = document.createElement('div');
            toast.textContent = mensaje;
            toast.style.position = 'fixed';
            toast.style.bottom = '20px';
            toast.style.right = '20px';
            toast.style.padding = '10px 15px';
            toast.style.borderRadius = '8px';
            toast.style.color = 'white';
            toast.style.zIndex = '99999';
            toast.style.backgroundColor = tipo === 'ok' ? '#22c55e' : '#ef4444';
            toast.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        function guardarDesdePopup(noticiaId) {
            fetch('/mis-carpetas', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                redirect: 'manual'
            })
                .then(res => {
                    console.log('mis-carpetas response:', res.status, res.redirected, res.url, res.headers.get('content-type'));
                    if (res.status === 302 || res.status === 301) {
                        window.location.href = '/login';
                        return;
                    }
                    if (res.status === 401 || res.status === 419) {
                        window.location.href = '/login';
                        return;
                    }
                    if (!res.ok) {
                        throw new Error('No se pudieron cargar las carpetas');
                    }
                    return res.json();
                })
                .then(carpetas => {
                    if (!carpetas || !carpetas.length) {
                        mostrarToast('Crea una carpeta para guardar noticias', 'error');
                        return;
                    }
                    mostrarModalCarpetas(carpetas, noticiaId);
                })
                .catch(err => {
                    console.error('Error fetching carpetas:', err);
                    mostrarToast('Error al cargar carpetas', 'error');
                });
        }

        function mostrarModalCarpetas(carpetas, noticiaId) {
            const modal = document.createElement('div');
            modal.style.position = 'fixed';
            modal.style.inset = '0';
            modal.style.zIndex = '99999';
            modal.style.backgroundColor = 'rgba(0,0,0,0.45)';
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';

            modal.innerHTML = `
                <div class="bg-white rounded-xl p-4 w-80 shadow-lg" style="max-width: 95%;">
                    <div class="mb-4">
                        <h3 class="font-bold text-lg">Guardar noticia</h3>
                        <p class="text-sm text-gray-600">Selecciona una carpeta donde guardar esta noticia.</p>
                    </div>
                    <div class="space-y-2">
                        ${carpetas.map(carpeta => `
                            <button data-carpeta-id="${carpeta.id}" class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-blue-50 border border-gray-200 rounded-lg transition-colors">
                                ${carpeta.nombre}
                            </button>
                        `).join('')}
                    </div>
                    <button id="cerrarModalCarpetas" class="mt-4 w-full px-4 py-3 text-sm text-gray-600">Cancelar</button>
                </div>
            `;

            document.body.appendChild(modal);

            modal.querySelectorAll('button[data-carpeta-id]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const carpetaId = btn.dataset.carpetaId;
                    agregarNoticiaACarpeta(carpetaId, noticiaId, modal);
                });
            });

            modal.querySelector('#cerrarModalCarpetas').onclick = () => modal.remove();
        }

        function agregarNoticiaACarpeta(carpetaId, noticiaId, modal) {
            fetch(`/carpetas/${carpetaId}/agregar-noticia`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ noticia_id: noticiaId })
                })
                .then(res => {
                    if (res.redirected) {
                        window.location.href = res.url;
                        return;
                    }
                    if (res.status === 401 || res.status === 419) {
                        window.location.href = '/login';
                        return;
                    }
                    if (!res.ok) {
                        throw new Error('Error guardando noticia');
                    }
                    return res.json();
                })
                .then(() => {
                    if (modal) modal.remove();
                    mostrarToast('Noticia guardada');
                })
                .catch(() => {
                    mostrarToast('Error al guardar', 'error');
                });
        }
    </script>
@endpush
