<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ auth()->user()->hasRole('root') ? route('admin.dashboard') : route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li class="inline-flex items-center">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('recursos.index', ['doctor_id' => $doctorId]) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">
                                Recursos Compartidos
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Agenda</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Agenda de Recursos</h2>
                            <p class="mt-1 text-sm text-gray-500">Agenda visual similar a Google Calendar para reservar recursos.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if(auth()->user()->hasRole('root'))
                                <form method="GET" action="{{ route('recursos.agenda') }}" class="flex items-center gap-2">
                                    <label class="text-xs font-semibold text-gray-500">Doctor</label>
                                    <select name="doctor_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]">
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" @selected($doctorId === $doctor->id)>{{ $doctor->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                            <a href="{{ route('recursos.index', ['doctor_id' => $doctorId]) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-bold rounded-md hover:bg-gray-200 transition-colors shadow-sm">
                                <i class="fas fa-list mr-2"></i>
                                Catálogo de recursos
                            </a>
                            <a href="{{ route('recursos.permisos', ['doctor_id' => $doctorId]) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-bold rounded-md hover:bg-gray-200 transition-colors shadow-sm">
                                <i class="fas fa-user-shield mr-2"></i>
                                Permisos
                            </a>
                        </div>
                    </div>

                    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-gray-500">Recurso</label>
                            <select id="recursoFilter" class="pr-8 px-3 py-2 border border-gray-300 rounded-md text-sm bg-white focus:ring-[#0061F5] focus:border-[#0061F5]">
                                <option value="">Todos</option>
                                @foreach($recursos as $recurso)
                                    <option value="{{ $recurso->id }}">{{ $recurso->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span class="font-semibold">Vista:</span>
                            <button type="button" data-view="dayGridMonth" class="px-3 py-1 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs">Mes</button>
                            <button type="button" data-view="timeGridWeek" class="px-3 py-1 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs">Semana</button>
                            <button type="button" data-view="timeGridDay" class="px-3 py-1 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs">Día</button>
                            <button type="button" data-view="listYear" class="px-3 py-1 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs">Año</button>
                        </div>
                    </div>

                    <div id="recursos-calendar"></div>

                    <div id="reserva-modal" class="fixed inset-0 z-50 hidden">
                        <div class="absolute inset-0 bg-black bg-opacity-30"></div>
                        <div class="relative z-10 flex items-center justify-center min-h-full">
                            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold text-gray-800">Nueva reserva de recurso</h3>
                                    <button type="button" id="reserva-close" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <form id="reserva-form" class="space-y-4">
                                    <input type="hidden" id="reserva-date">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Recurso</label>
                                        <select id="reserva-recurso" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]">
                                            <option value="">Selecciona un recurso</option>
                                            @foreach($recursos as $recurso)
                                                <option value="{{ $recurso->id }}">{{ $recurso->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Asignar a</label>
                                        <select id="reserva-user" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]">
                                            <option value="">Selecciona un usuario</option>
                                            @foreach($usuarios as $usuario)
                                                <option value="{{ $usuario->id }}">{{ $usuario->name }} {{ $usuario->apellido_paterno }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Título</label>
                                        <input type="text" id="reserva-titulo" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]" placeholder="Ej. Cirugía, Junta, Procedimiento">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Fecha</label>
                                            <input type="date" id="reserva-fecha" readonly class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Hora inicio</label>
                                            <input type="time" id="reserva-hora" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Duración (minutos)</label>
                                        <input type="number" id="reserva-duracion" min="1" step="5" value="60" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Comentario</label>
                                        <textarea id="reserva-comentario" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]" placeholder="Detalles adicionales de la reserva"></textarea>
                                    </div>
                                    <div class="flex items-center justify-between pt-2">
                                        <button type="button" id="reserva-delete" class="hidden px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 rounded-md hover:bg-red-100 flex items-center gap-1">
                                            <i class="fas fa-trash text-sm"></i>
                                            Eliminar
                                        </button>
                                        <div class="flex gap-2">
                                            <button type="button" id="reserva-cancel" class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                                                Cancelar
                                            </button>
                                            <button type="button" id="reserva-save" class="px-4 py-2 text-sm font-bold text-white bg-[#0061F5] rounded-md hover:bg-[#0051CC]">
                                                Guardar reserva
                                            </button>
                                        </div>
                                    </div>
                                    <p id="reserva-error" class="mt-3 text-xs text-red-600 hidden"></p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    @php
        $defaultRecursoId = $recursos->first()?->id;
        $defaultUserId = isset($usuarios) && $usuarios->count() > 0 ? $usuarios->first()->id : null;
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('recursos-calendar');
            const doctorId = {{ (int) $doctorId }};
            const recursoFilter = document.getElementById('recursoFilter');
            const defaultRecursoId = {{ $defaultRecursoId ? (int) $defaultRecursoId : 'null' }};
            const modalEl = document.getElementById('reserva-modal');
            const closeBtn = document.getElementById('reserva-close');
            const cancelBtn = document.getElementById('reserva-cancel');
            const saveBtn = document.getElementById('reserva-save');
            const recursoSelect = document.getElementById('reserva-recurso');
            const userSelect = document.getElementById('reserva-user');
            const tituloInput = document.getElementById('reserva-titulo');
            const fechaInput = document.getElementById('reserva-fecha');
            const horaInput = document.getElementById('reserva-hora');
            const duracionInput = document.getElementById('reserva-duracion');
            const comentarioInput = document.getElementById('reserva-comentario');
            const deleteBtn = document.getElementById('reserva-delete');
            const errorBox = document.getElementById('reserva-error');
            const defaultUserId = {{ $defaultUserId ? (int) $defaultUserId : 'null' }};
            let selectedDate = null;

            let editingEventId = null;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                selectable: true,
                editable: true,
                droppable: false,
                height: 'auto',
                slotMinTime: '07:00:00',
                slotMaxTime: '22:00:00',
                events: function (info, successCallback, failureCallback) {
                    const params = new URLSearchParams();
                    params.append('doctor_id', doctorId);
                    params.append('start', info.startStr);
                    params.append('end', info.endStr);
                    if (recursoFilter.value) {
                        params.append('recurso_id', recursoFilter.value);
                    }

                    fetch('{{ route('recursos.eventos') }}?' + params.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => successCallback(data))
                        .catch(() => failureCallback());
                },
                eventDidMount: function (info) {
                    if (info.event && info.event.title) {
                        info.el.setAttribute('title', info.event.title);
                    }
                },
                dateClick: function (info) {
                    openCreateModal(info.date);
                },
                eventClick: function (info) {
                    info.jsEvent.preventDefault();
                    openEditModal(info.event);
                },
                eventDrop: function (info) {
                    updateEventTime(info.event);
                },
                eventResize: function (info) {
                    updateEventTime(info.event);
                }
            });

            calendar.render();

            recursoFilter.addEventListener('change', function () {
                calendar.refetchEvents();
            });

            document.querySelectorAll('[data-view]').forEach(button => {
                button.addEventListener('click', function () {
                    const viewName = this.getAttribute('data-view');
                    calendar.changeView(viewName);
                });
            });

            function clearError() {
                if (errorBox) {
                    errorBox.textContent = '';
                    errorBox.classList.add('hidden');
                }
            }

            function showError(message) {
                if (errorBox) {
                    errorBox.textContent = message || 'Ocurrió un error al procesar la reserva.';
                    errorBox.classList.remove('hidden');
                } else {
                    alert(message || 'Ocurrió un error al procesar la reserva.');
                }
            }

            function handleErrorResponse(response, defaultMessage) {
                const cloned = response.clone();
                return cloned.json().then(function (data) {
                    if (data) {
                        if (data.message && data.message !== 'The given data was invalid.') {
                            throw new Error(data.message);
                        }
                        if (data.errors) {
                            const keys = Object.keys(data.errors);
                            if (keys.length > 0) {
                                const firstKey = keys[0];
                                const firstError = data.errors[firstKey] && data.errors[firstKey][0];
                                if (firstError) {
                                    throw new Error(firstError);
                                }
                            }
                        }
                    }
                    throw new Error(defaultMessage);
                }).catch(function () {
                    return response.text().then(function (text) {
                        let message = defaultMessage;
                        if (text) {
                            const plain = text.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                            if (plain) {
                                message = plain.slice(0, 200);
                            }
                        }
                        throw new Error(message);
                    }).catch(function () {
                        throw new Error(defaultMessage);
                    });
                });
            }

            function tieneConflictoLocal(recursoId, inicioIso, finIso, ignorarEventoId) {
                if (!recursoId) {
                    return null;
                }
                const inicio = new Date(inicioIso);
                const fin = new Date(finIso);
                if (!(inicio instanceof Date) || isNaN(inicio.getTime()) || !(fin instanceof Date) || isNaN(fin.getTime())) {
                    return null;
                }

                const eventos = calendar.getEvents();
                for (let i = 0; i < eventos.length; i++) {
                    const ev = eventos[i];
                    if (ignorarEventoId && String(ev.id) === String(ignorarEventoId)) {
                        continue;
                    }
                    const props = ev.extendedProps || {};
                    if (!props.recurso_id || String(props.recurso_id) !== String(recursoId)) {
                        continue;
                    }
                    const evInicio = ev.start;
                    const evFin = ev.end || ev.start;
                    if (!evInicio || !evFin) {
                        continue;
                    }
                    if (evInicio < fin && evFin > inicio) {
                        const fecha = evInicio.toISOString().slice(0, 10);
                        const horaInicio = evInicio.toTimeString().slice(0, 5);
                        const horaFin = evFin.toTimeString().slice(0, 5);
                        return 'Este recurso ya está reservado entre ' + horaInicio + ' y ' + horaFin + ' en ' + fecha + '.';
                    }
                }

                return null;
            }

            function openCreateModal(date) {
                editingEventId = null;
                deleteBtn.classList.add('hidden');
                clearError();

                if (!recursoSelect.options.length || recursoSelect.options.length === 1) {
                    if (!defaultRecursoId && !recursoFilter.value) {
                        alert('Primero crea al menos un recurso.');
                        return;
                    }
                }

                selectedDate = date;

                const iso = date.toISOString();
                const fecha = iso.slice(0, 10);
                let hora = iso.slice(11, 16);
                if (hora === '00:00') {
                    hora = '09:00';
                }

                fechaInput.value = fecha;
                horaInput.value = hora;
                duracionInput.value = '60';
                tituloInput.value = '';
                comentarioInput.value = '';

                if (recursoFilter.value) {
                    recursoSelect.value = recursoFilter.value;
                } else if (defaultRecursoId) {
                    recursoSelect.value = String(defaultRecursoId);
                } else {
                    recursoSelect.value = '';
                }

                if (defaultUserId) {
                    userSelect.value = String(defaultUserId);
                } else {
                    userSelect.value = '';
                }

                modalEl.classList.remove('hidden');
            }

            function openEditModal(event) {
                editingEventId = event.id;
                deleteBtn.classList.remove('hidden');
                clearError();

                const start = event.start;
                const end = event.end || start;

                const inicioIso = start.toISOString();
                const fecha = inicioIso.slice(0, 10);
                const hora = inicioIso.slice(11, 16);

                let duracion = Math.max(1, Math.round((end.getTime() - start.getTime()) / 60000));
                if (!Number.isFinite(duracion) || duracion <= 0 || duracion > 360) {
                    duracion = 60;
                }

                fechaInput.value = fecha;
                horaInput.value = hora;
                duracionInput.value = String(duracion);
                tituloInput.value = event.title || '';
                comentarioInput.value = event.extendedProps && event.extendedProps.comentario ? event.extendedProps.comentario : '';

                if (event.extendedProps && event.extendedProps.recurso_id) {
                    recursoSelect.value = String(event.extendedProps.recurso_id);
                }

                if (event.extendedProps && event.extendedProps.user_id) {
                    userSelect.value = String(event.extendedProps.user_id);
                }

                modalEl.classList.remove('hidden');
            }

            function closeModal() {
                modalEl.classList.add('hidden');
                editingEventId = null;
                deleteBtn.classList.add('hidden');
                clearError();
            }

            function saveReserva() {
                const recursoId = recursoSelect.value;
                const userId = userSelect.value;
                const titulo = tituloInput.value.trim();
                const fecha = fechaInput.value;
                const hora = horaInput.value;
                const duracion = parseInt(duracionInput.value, 10);
                const comentario = comentarioInput.value.trim();

                if (editingEventId === null) {
                    if (!recursoId) {
                        showError('Selecciona un recurso.');
                        return;
                    }
                    if (!userId) {
                        showError('Selecciona un usuario.');
                        return;
                    }
                }
                if (!fecha || !hora || !duracion || Number.isNaN(duracion) || duracion <= 0) {
                    showError('Revisa fecha, hora y duración.');
                    return;
                }

                const inicio = fecha + 'T' + hora + ':00';
                const finDate = new Date(inicio);
                finDate.setMinutes(finDate.getMinutes() + duracion);
                const fin = finDate.toISOString().slice(0, 19);

                const conflictoLocal = tieneConflictoLocal(recursoId, inicio, fin, editingEventId);
                if (conflictoLocal) {
                    showError(conflictoLocal);
                    return;
                }

                const formData = new URLSearchParams();
                formData.append('doctor_id', doctorId);
                formData.append('titulo', titulo);
                formData.append('comentario', comentario);
                formData.append('inicio', inicio);
                formData.append('fin', fin);
                formData.append('_token', '{{ csrf_token() }}');

                if (editingEventId === null) {
                    formData.append('recurso_id', recursoId);
                    formData.append('user_id', userId);

                    fetch('{{ route('recursos.eventos.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData.toString()
                    })
                        .then(response => {
                            if (!response.ok) {
                                return handleErrorResponse(response, 'No se pudo crear la reserva.');
                            }
                            return response.json();
                        })
                        .then(() => {
                            closeModal();
                            calendar.refetchEvents();
                        })
                        .catch(error => {
                            showError(error.message || 'No se pudo crear la reserva.');
                        });
                } else {
                    fetch('{{ url("admin/recursos/eventos") }}/' + editingEventId, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData.toString()
                    })
                        .then(response => {
                            if (!response.ok) {
                                return handleErrorResponse(response, 'No se pudo actualizar la reserva.');
                            }
                        })
                        .then(() => {
                            closeModal();
                            calendar.refetchEvents();
                        })
                        .catch(error => {
                            showError(error.message || 'No se pudo actualizar la reserva.');
                        });
                }
            }

            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);
            saveBtn.addEventListener('click', function () {
                clearError();
                saveReserva();
            });
            deleteBtn.addEventListener('click', function () {
                if (editingEventId === null) {
                    return;
                }
                if (!confirm('¿Eliminar esta reserva?')) {
                    return;
                }

                const formData = new URLSearchParams();
                formData.append('doctor_id', doctorId);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ url("admin/recursos/eventos") }}/' + editingEventId, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData.toString()
                })
                    .then(response => {
                        if (!response.ok) {
                            return handleErrorResponse(response, 'No se pudo eliminar la reserva.');
                        }
                    })
                    .then(() => {
                        closeModal();
                        calendar.refetchEvents();
                    })
                    .catch(error => {
                        showError(error.message || 'No se pudo eliminar la reserva.');
                    });
            });

            function updateEventTime(event) {
                const inicio = event.start.toISOString().slice(0, 19);
                const fin = event.end ? event.end.toISOString().slice(0, 19) : inicio;

                const formData = new URLSearchParams();
                formData.append('doctor_id', doctorId);
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('inicio', inicio);
                formData.append('fin', fin);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ url("admin/recursos/eventos") }}/' + event.id, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData.toString()
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                const message = data && data.message ? data.message : 'No se pudo actualizar la reserva.';
                                throw new Error(message);
                            }).catch(() => {
                                throw new Error('No se pudo actualizar la reserva.');
                            });
                        }
                    })
                    .catch(error => {
                        alert(error.message || 'No se pudo actualizar la reserva.');
                        event.revert();
                    });
            }
        });
    </script>
</x-admin-layout>
