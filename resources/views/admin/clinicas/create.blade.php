<x-admin-layout>
    <div class="py-10" x-data="clinicaForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('clinicas.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">Clínicas</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Crear</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form action="{{ route('clinicas.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label for="nombre" class="block text-sm font-bold text-gray-700">Nombre</label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>

                            <!-- Dirección -->
                            <div>
                                <label for="direccion" class="block text-sm font-bold text-gray-700">Dirección</label>
                                <input x-ref="addressInput" type="text" name="direccion" id="direccion" x-model="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" placeholder="Escribe la dirección para buscar..." required>
                                <input type="hidden" name="lat" x-model="lat">
                                <input type="hidden" name="lng" x-model="lng">
                            </div>

                            @if(Auth::user()->hasRole('root') || Auth::user()->active_package_type === 'clinica')
                            <!-- Mapa -->
                            <div class="h-96 w-full rounded-lg border border-gray-300 overflow-hidden">
                                <div x-ref="mapContainer" class="w-full h-full"></div>
                            </div>
                            @endif

                            <!-- Teléfono -->
                            <div>
                                <label for="telefono" class="block text-sm font-bold text-gray-700">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>

                            <!-- Ubicación (Notas) -->
                            <div>
                                <label for="ubicacion" class="block text-sm font-bold text-gray-700">Detalles de Ubicación (Opcional)</label>
                                <textarea name="ubicacion" id="ubicacion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">{{ old('ubicacion') }}</textarea>
                            </div>

                            <!-- Logotipo -->
                            <div>
                                <label for="logo" class="block text-sm font-bold text-gray-700">Logo</label>
                                <input type="file" name="logo" id="logo" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#E6F0FF] file:text-[#0061F5] hover:file:bg-[#CCE0FF]">
                            </div>

                            <!-- Activo -->
                            <div class="flex items-center">
                                <input type="checkbox" name="activo" id="activo" value="1" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" {{ old('activo', '1') ? 'checked' : '' }}>
                                <label for="activo" class="ml-2 block text-sm font-medium text-gray-700">Activo</label>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <a href="{{ route('clinicas.index') }}" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors mr-2">Cancelar</a>
                            <x-primary-button>Guardar</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places" async defer></script>
    <script>
        function clinicaForm() {
            return {
                address: "{{ old('direccion') }}",
                lat: "{{ old('lat') }}",
                lng: "{{ old('lng') }}",
                map: null,
                marker: null,
                init() {
                    const checkGoogle = setInterval(() => {
                        if (window.google && window.google.maps) {
                            clearInterval(checkGoogle);
                            this.initMap();
                        }
                    }, 100);
                },
                initMap() {
                    if (!this.$refs.mapContainer) return;

                    const defaultLocation = { lat: 19.4326, lng: -99.1332 }; // CDMX default
                    let latVal = (this.lat && this.lat !== 'null') ? parseFloat(this.lat) : null;
                    let lngVal = (this.lng && this.lng !== 'null') ? parseFloat(this.lng) : null;

                    const initialLocation = (latVal && lngVal) ? { lat: latVal, lng: lngVal } : defaultLocation;
                    
                    this.map = new google.maps.Map(this.$refs.mapContainer, {
                        center: initialLocation,
                        zoom: (latVal && lngVal) ? 17 : 13,
                    });

                    if (latVal && lngVal) {
                        this.placeMarker(initialLocation);
                    }
                    
                    const autocomplete = new google.maps.places.Autocomplete(this.$refs.addressInput);
                    autocomplete.bindTo('bounds', this.map);
                    autocomplete.addListener('place_changed', () => {
                        const place = autocomplete.getPlace();
                        if (!place.geometry || !place.geometry.location) return;
                        
                        this.address = place.formatted_address; // Sync input
                        this.placeMarker(place.geometry.location);
                        this.map.setCenter(place.geometry.location);
                        this.map.setZoom(17);
                    });

                    this.map.addListener('click', (e) => {
                        this.placeMarker(e.latLng);
                    });
                },
                placeMarker(location) {
                    this.lat = location.lat();
                    this.lng = location.lng();
                    
                    if (this.marker) {
                        this.marker.setPosition(location);
                    } else {
                        this.marker = new google.maps.Marker({
                            position: location,
                            map: this.map,
                            draggable: true
                        });
                        
                        this.marker.addListener('dragend', () => {
                            const pos = this.marker.getPosition();
                            this.lat = pos.lat();
                            this.lng = pos.lng();
                        });
                    }
                }
            }
        }
    </script>
</x-admin-layout>
