<x-layouts.app title="Nuevo Producto - ABARROTESBASE">
    {{-- Librería HTML5-QRCode --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <div class="mx-auto max-w-4xl space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Alta de Nuevo Producto</h1>
                <p class="mt-1 text-xs font-semibold text-slate-500">Ingresa la información comercial y técnica de tu producto.</p>
            </div>
            <a href="{{ route('products.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                ← Volver al catálogo
            </a>
        </div>

        <form method="POST" action="{{ route('products.store') }}" class="space-y-6">
            @csrf

            {{-- Bloque 1: Información Básica --}}
            <x-ui.card padding="p-6 sm:p-8" class="shadow-sm border-slate-200 bg-white space-y-5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-700">1. Identificación del Producto</h2>

                <div class="grid gap-5 sm:grid-cols-2">
                    {{-- Código de Barras con Botón de Escáner --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="barcode" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Código de Barras *
                            </label>
                            <button type="button" onclick="startScanner()" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700 transition">
                                📷 Escanear con Cámara
                            </button>
                        </div>
                        <div class="relative">
                            <x-ui.input id="barcode" name="barcode" :value="old('barcode')" required placeholder="7501000000000" onchange="fetchProductInfo(this.value)" :error="$errors->has('barcode')" />
                            <div id="barcodeLoader" class="absolute right-3 top-2.5 hidden">
                                <svg class="animate-spin h-4 w-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        @error('barcode')<p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- SKU --}}
                    <div>
                        <label for="sku" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Código Interno / SKU
                        </label>
                        <x-ui.input id="sku" name="sku" :value="old('sku')" placeholder="Ej. GAL-001" :error="$errors->has('sku')" />
                        @error('sku')<p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Nombre del Producto --}}
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nombre del producto *
                        </label>
                        <x-ui.input id="name" name="name" :value="old('name')" required placeholder="Ej. Galletas Chokis 100g" :error="$errors->has('name')" />
                        @error('name')<p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </x-ui.card>

            {{-- Bloque 2: Clasificación y Precios --}}
            <x-ui.card padding="p-6 sm:p-8" class="shadow-sm border-slate-200 bg-white space-y-5">
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-700">2. Clasificación & Venta</h2>

                <div class="grid gap-5 sm:grid-cols-2">
                    {{-- Categoría --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="category_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Categoría
                            </label>
                            <button type="button" onclick="openQuickModal('category')" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 transition">
                                + Nueva
                            </button>
                        </div>
                        <select id="category_id" name="category_id" class="app-input">
                            <option value="">-- Sin categoría --</option>
                            @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id')===$cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Marca --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="brand_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Marca
                            </label>
                            <button type="button" onclick="openQuickModal('brand')" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 transition">
                                + Nueva
                            </button>
                        </div>
                        <select id="brand_id" name="brand_id" class="app-input">
                            <option value="">-- Sin marca --</option>
                            @foreach ($brands as $b)
                            <option value="{{ $b->id }}" @selected(old('brand_id')===$b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Unidad de medida --}}
                    <div>
                        <label for="unit_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Unidad de venta *
                        </label>
                        <select id="unit_id" name="unit_id" required class="app-input">
                            @foreach ($units as $u)
                            <option value="{{ $u->id }}" @selected(old('unit_id')===$u->id)>{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tipo de Producto --}}
                    <div>
                        <label for="product_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tipo de Producto *
                        </label>
                        <select id="product_type" name="product_type" required class="app-input">
                            <option value="simple" @selected(old('product_type')==='simple' )>Pieza Individual (Entero)</option>
                            <option value="bulk" @selected(old('product_type')==='bulk' )>Granel / Fraccionado (Permite decimales)</option>
                        </select>
                    </div>

                    {{-- Precio Venta --}}
                    <div>
                        <label for="price" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Precio Público (MXN) *
                        </label>
                        <x-ui.input id="price" type="number" step="0.01" name="price" :value="old('price')" required placeholder="0.00" :error="$errors->has('price')" />
                        @error('price')<p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Impuesto --}}
                    <div>
                        <label for="tax_rate_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tasa de Impuesto
                        </label>
                        <select id="tax_rate_id" name="tax_rate_id" class="app-input">
                            <option value="">-- Sin impuesto aplicable --</option>
                            @foreach ($taxRates as $tax)
                            <option value="{{ $tax->id }}" @selected(old('tax_rate_id')===$tax->id)>{{ $tax->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-ui.card>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('products.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50">
                    Cancelar
                </a>
                <x-ui.button type="submit" variant="primary" class="px-6 py-2.5">
                    Guardar Producto
                </x-ui.button>
            </div>
        </form>
    </div>

    <!-- MODAL CÁMARA ESCÁNER (AJUSTADO) -->
    <div id="scannerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl border border-slate-100 space-y-4 text-center">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Escaneando Código
                </h3>
                <button type="button" onclick="stopScanner()" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
            </div>

            {{-- Contenedor con dimensiones estrictas para evitar desbordes --}}
            <div class="relative w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-950 min-h-[220px] max-h-[300px] flex items-center justify-center">
                <div id="reader" class="w-full h-full [&>video]:max-h-[280px] [&>video]:object-cover"></div>
            </div>

            {{-- Retroalimentación visual de lectura --}}
            <div id="scannerFeedback" class="min-h-[38px] flex items-center justify-center rounded-xl bg-slate-50 border border-slate-100 px-3 py-1.5 text-xs font-mono text-slate-600">
                Apunta al código de barras...
            </div>

            <button type="button" onclick="stopScanner()" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                Cerrar Escáner
            </button>
        </div>
    </div>

    <!-- Modal Rápido Categoría/Marca -->
    <div id="quickModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/40 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl border border-slate-100 space-y-4">
            <h3 id="quickModalTitle" class="text-sm font-bold text-slate-900">Crear Nuevo Registro</h3>
            <div>
                <label for="quickModalInput" class="block text-xs font-semibold text-slate-500 mb-1">Nombre</label>
                <input type="text" id="quickModalInput" class="app-input w-full" placeholder="Nombre...">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeQuickModal()" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500 transition hover:bg-slate-100">
                    Cancelar
                </button>
                <button type="button" onclick="submitQuickModal()" class="rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-slate-950 shadow-sm transition hover:bg-emerald-400">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</x-layouts.app>

<script>
    let html5QrCode = null;

    // --- CÁMARA Y ESCÁNER ---
    function startScanner() {
        const modal = document.getElementById('scannerModal');
        const feedback = document.getElementById('scannerFeedback');

        feedback.className = "min-h-[38px] flex items-center justify-center rounded-xl bg-slate-50 border border-slate-100 px-3 py-1.5 text-xs font-mono text-slate-600";
        feedback.innerText = "Buscando código...";

        modal.classList.remove('hidden');

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        const config = {
            fps: 15,
            qrbox: {
                width: 220,
                height: 120
            },
            aspectRatio: 1.0
        };

        html5QrCode.start({
                facingMode: "environment"
            },
            config,
            (decodedText) => {
                // Retroalimentación visual inmediata
                feedback.className = "min-h-[38px] flex items-center justify-center rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-xs font-bold text-emerald-700 animate-pulse";
                feedback.innerText = `¡Leído!: ${decodedText}`;

                document.getElementById('barcode').value = decodedText;

                // Pequeño delay para que el usuario perciba la confirmación
                setTimeout(() => {
                    stopScanner();
                    fetchProductInfo(decodedText);
                }, 400);
            },
            (errorMessage) => {
                // Cuadro activo buscando barras
            }
        ).catch(err => {
            console.error("Error al iniciar cámara:", err);
            alert("No se pudo acceder a la cámara. Verifica los permisos de tu navegador.");
            stopScanner();
        });
    }

    function stopScanner() {
        const modal = document.getElementById('scannerModal');
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                modal.classList.add('hidden');
            }).catch(err => {
                console.error("Error al detener cámara:", err);
                modal.classList.add('hidden');
            });
        } else {
            modal.classList.add('hidden');
        }
    }

    // --- CONSULTA A API (AUTO-LLENADO) ---
    async function fetchProductInfo(barcode) {
        barcode = barcode.trim();
        if (!barcode) return;

        const loader = document.getElementById('barcodeLoader');
        loader.classList.remove('hidden');

        try {
            const response = await fetch(`/api/products/lookup/${barcode}`);
            const data = await response.json();

            if (data.found) {
                // 1. Nombre del Producto
                const nameInput = document.getElementById('name');
                if (!nameInput.value && data.name) {
                    nameInput.value = data.name;
                }

                // 2. Auto-generar SKU si está vacío (ej. BON-1.5L-7581)
                const skuInput = document.getElementById('sku');
                if (!skuInput.value && data.name) {
                    const prefix = data.name.substring(0, 3).toUpperCase();
                    const suffix = barcode.slice(-4);
                    skuInput.value = `${prefix}-${suffix}`;
                }

                // 3. Mapeo Automático de MARCA (Busca coincidencia por texto en tu <select>)
                if (data.brand) {
                    const brandSelect = document.getElementById('brand_id');
                    const brandOption = Array.from(brandSelect.options).find(
                        opt => opt.text.toLowerCase().includes(data.brand.toLowerCase())
                    );
                    if (brandOption) {
                        brandSelect.value = brandOption.value;
                    }
                }

                // 4. Ajuste Inteligente de Unidad de Venta (evitar que quede en KG por defecto)
                const unitSelect = document.getElementById('unit_id');
                const unitOptions = Array.from(unitSelect.options);

                // Buscar unidad "Pieza" / "Unidad" para abarrotes empaquetados
                const defaultPieceOption = unitOptions.find(opt =>
                    opt.text.toLowerCase().includes('pieza') ||
                    opt.text.toLowerCase().includes('pza') ||
                    opt.text.toLowerCase().includes('unidad')
                );

                if (defaultPieceOption) {
                    unitSelect.value = defaultPieceOption.value;
                }

                // 5. Previsualización de Imagen (Si existe)
                if (data.image) {
                    showProductPreviewImage(data.image);
                }
            }
        } catch (e) {
            console.error("Error al consultar producto:", e);
        } finally {
            loader.classList.add('hidden');
        }
    }

    // Función auxiliar para mostrar la miniatura del producto escaneado
    function showProductPreviewImage(imageUrl) {
        let previewBox = document.getElementById('productImagePreview');
        if (!previewBox) {
            previewBox = document.createElement('div');
            previewBox.id = 'productImagePreview';
            previewBox.className = 'mt-3 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50/50 p-2.5 text-xs text-emerald-800';

            document.getElementById('name').parentNode.appendChild(previewBox);
        }

        previewBox.innerHTML = `
        <img src="${imageUrl}" class="h-12 w-12 rounded-lg object-cover border border-slate-200 shadow-sm bg-white" alt="Vista previa">
        <div>
            <p class="font-bold text-emerald-900">Producto verificado en base pública</p>
        </div>
    `;
    }

    // --- MODALES CATEGORIA/MARCA ---
    let activeType = null;

    function openQuickModal(type) {
        activeType = type;
        document.getElementById('quickModalTitle').innerText = type === 'category' ? 'Nueva Categoría' : 'Nueva Marca';
        document.getElementById('quickModalInput').value = '';
        document.getElementById('quickModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('quickModalInput').focus(), 50);
    }

    function closeQuickModal() {
        document.getElementById('quickModal').classList.add('hidden');
    }

    async function submitQuickModal() {
        const name = document.getElementById('quickModalInput').value.trim();
        if (!name) return;

        const url = activeType === 'category' ? "{{ route('categories.quick-store') }}" : "{{ route('brands.quick-store') }}";

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    name
                })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                const select = document.getElementById(activeType === 'category' ? 'category_id' : 'brand_id');
                const newOption = new Option(data[activeType].name, data[activeType].id, true, true);
                select.add(newOption);
                closeQuickModal();
            } else {
                alert('Error: ' + (data.message || 'No se pudo guardar'));
            }
        } catch (e) {
            console.error('Error:', e);
            alert('Ocurrió una falla en el servidor.');
        }
    }
</script>
