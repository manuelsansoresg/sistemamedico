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
                            <a href="{{ route('recursos.index', ['doctor_id' => $doctorId]) }}" class="inline-flex items-center px-4 py-2 bg-white text-[#0061F5] text-sm font-bold rounded-md border border-[#0061F5] hover:bg-[#F8FAFC] transition-colors shadow-sm">
                                <i class="fas fa-list mr-2"></i>
                                Catálogo de recursos
                            </a>
                            <a href="{{ route('recursos.permisos', ['doctor_id' => $doctorId]) }}" class="inline-flex items-center px-4 py-2 bg-white text-[#0061F5] text-sm font-bold rounded-md border border-[#0061F5] hover:bg-[#F8FAFC] transition-colors shadow-sm">
                                <i class="fas fa-user-shield mr-2"></i>
                                Permisos
                            </a>
                            <a
                                id="exportPdfBtn"
                                data-export-base-url="{{ route('recursos.agenda.export_pdf') }}"
                                href="{{ route('recursos.agenda.export_pdf', ['doctor_id' => $doctorId]) }}"
                                class="inline-flex items-center px-4 py-2 bg-white text-[#0061F5] text-sm font-bold rounded-md border border-[#0061F5] hover:bg-blue-50 transition-colors shadow-sm"
                            >
                                <i class="fas fa-file-pdf mr-2"></i>
                                Exportar PDF
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
                            <button type="button" data-view="dayGridMonth" class="agenda-view-btn px-3 py-1 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-[#F8FAFC] text-xs transition-colors">Mes</button>
                            <button type="button" data-view="timeGridWeek" class="agenda-view-btn px-3 py-1 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-[#F8FAFC] text-xs transition-colors">Semana</button>
                            <button type="button" data-view="timeGridDay" class="agenda-view-btn px-3 py-1 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-[#F8FAFC] text-xs transition-colors">Día</button>
                            <button type="button" data-view="listYear" class="agenda-view-btn px-3 py-1 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-[#F8FAFC] text-xs transition-colors">Año</button>
                        </div>
                    </div>

                    @php
                        $defaultRecursoId = $recursos->first()?->id;
                        $defaultUserId = isset($usuarios) && $usuarios->count() > 0 ? $usuarios->first()->id : null;
                    @endphp

                    <div
                        id="recursos-calendar"
                        data-doctor-id="{{ (int) $doctorId }}"
                        data-default-recurso-id="{{ $defaultRecursoId ? (int) $defaultRecursoId : '' }}"
                        data-default-user-id="{{ $defaultUserId ? (int) $defaultUserId : '' }}"
                        data-csrf-token="{{ csrf_token() }}"
                        data-eventos-url="{{ route('recursos.eventos') }}"
                        data-eventos-store-url="{{ route('recursos.eventos.store') }}"
                        data-eventos-base-url="{{ url('admin/recursos/eventos') }}"
                    ></div>

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
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.global.min.js"></script>

    <style>
        #recursos-calendar {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 12px;
        }

        #recursos-calendar .fc {
            color: #1E293B;
        }

        #recursos-calendar .fc .fc-toolbar-title {
            color: #1E293B;
            font-weight: 800;
        }

        #recursos-calendar .fc .fc-button-primary {
            background: #0061F5;
            border-color: #0061F5;
            font-weight: 700;
            border-radius: 8px;
        }

        #recursos-calendar .fc .fc-button-primary:hover {
            background: #0051CC;
            border-color: #0051CC;
        }

        #recursos-calendar .fc .fc-button-primary:disabled {
            background: #94A3B8;
            border-color: #94A3B8;
        }

        #recursos-calendar .fc .fc-button-primary.fc-button-active {
            background: #27ADFA;
            border-color: #27ADFA;
        }

        #recursos-calendar .fc .fc-button-primary.fc-button-active:hover {
            background: #1D9EEA;
            border-color: #1D9EEA;
        }

        #recursos-calendar .fc .fc-day-today {
            background: rgba(39, 173, 250, 0.08);
        }

        #recursos-calendar .fc .fc-timegrid-col.fc-day-today {
            background: rgba(39, 173, 250, 0.06);
        }

        #recursos-calendar .fc .fc-highlight {
            background: rgba(39, 173, 250, 0.15);
        }

        #recursos-calendar .fc a {
            color: #0061F5;
        }

        #recursos-calendar .fc .fc-event {
            background: #0061F5;
            border-color: #0061F5;
            color: #FFFFFF;
        }

        #recursos-calendar .fc .fc-event:hover {
            filter: brightness(0.98);
        }

        #recursos-calendar .fc .fc-now-indicator-line,
        #recursos-calendar .fc .fc-now-indicator-arrow {
            border-color: #FA7427;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('recursos-calendar');
            const doctorId = parseInt(calendarEl.dataset.doctorId || '0', 10);
            const recursoFilter = document.getElementById('recursoFilter');
            const exportPdfBtn = document.getElementById('exportPdfBtn');
            const defaultRecursoId = calendarEl.dataset.defaultRecursoId ? parseInt(calendarEl.dataset.defaultRecursoId, 10) : null;
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
            const defaultUserId = calendarEl.dataset.defaultUserId ? parseInt(calendarEl.dataset.defaultUserId, 10) : null;
            const csrfToken = calendarEl.dataset.csrfToken || '';
            const eventosUrl = calendarEl.dataset.eventosUrl || '';
            const eventosStoreUrl = calendarEl.dataset.eventosStoreUrl || '';
            const eventosBaseUrl = calendarEl.dataset.eventosBaseUrl || '';
            let selectedDate = null;

            let editingEventId = null;
            const viewButtons = Array.from(document.querySelectorAll('.agenda-view-btn'));

            function setActiveViewButton(viewType) {
                viewButtons.forEach(btn => {
                    const isActive = btn.getAttribute('data-view') === viewType;
                    btn.classList.toggle('bg-[#0061F5]', isActive);
                    btn.classList.toggle('text-white', isActive);
                    btn.classList.toggle('border-[#0061F5]', isActive);
                    btn.classList.toggle('bg-white', !isActive);
                    btn.classList.toggle('text-gray-700', !isActive);
                    btn.classList.toggle('border-gray-300', !isActive);
                });
            }

            function updateExportPdfHref() {
                if (!exportPdfBtn) {
                    return;
                }
                const baseUrl = exportPdfBtn.dataset.exportBaseUrl || '';
                if (!baseUrl) {
                    return;
                }
                const params = new URLSearchParams();
                params.append('doctor_id', doctorId);

                const view = calendar.view;
                const startDate = view && view.activeStart ? view.activeStart : null;
                const endDate = view && view.activeEnd ? new Date(view.activeEnd.getTime() - 1) : null;

                if (startDate) {
                    params.append('start', startDate.toISOString().slice(0, 10));
                }
                if (endDate) {
                    params.append('end', endDate.toISOString().slice(0, 10));
                }
                if (recursoFilter && recursoFilter.value) {
                    params.append('recurso_id', recursoFilter.value);
                }

                const currentDate = calendar.getDate ? calendar.getDate() : null;
                if (currentDate) {
                    params.append('mes', String(currentDate.getMonth() + 1));
                    params.append('anio', String(currentDate.getFullYear()));
                }

                exportPdfBtn.href = baseUrl + '?' + params.toString();
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día',
                    year: 'Año',
                    list: 'Lista'
                },
                allDayText: 'Todo el día',
                noEventsText: 'No hay reservas para mostrar',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                selectable: true,
                editable: true,
                droppable: false,
                height: 'auto',
                slotMinTime: '06:00:00',
                slotMaxTime: '22:00:00',
                slotDuration: '01:00:00',
                slotLabelInterval: '01:00:00',
                slotLabelFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short',
                    hour12: true
                },
                dayHeaderFormat: {
                    weekday: 'short',
                    day: 'numeric'
                },
                nowIndicator: true,
                scrollTime: '07:00:00',
                datesSet: function (info) {
                    setActiveViewButton(info.view.type);
                    updateExportPdfHref();
                },
                events: function (info, successCallback, failureCallback) {
                    const params = new URLSearchParams();
                    params.append('doctor_id', doctorId);
                    params.append('start', info.startStr);
                    params.append('end', info.endStr);
                    if (recursoFilter.value) {
                        params.append('recurso_id', recursoFilter.value);
                    }

                    fetch(eventosUrl + '?' + params.toString(), {
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
            setActiveViewButton(calendar.view.type);
            updateExportPdfHref();

            recursoFilter.addEventListener('change', function () {
                calendar.refetchEvents();
                updateExportPdfHref();
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

                const formData = new URLSearchParams();
                formData.append('doctor_id', doctorId);
                formData.append('titulo', titulo);
                formData.append('comentario', comentario);
                formData.append('inicio', inicio);
                formData.append('fin', fin);
                formData.append('duracion', String(duracion));
                formData.append('_token', csrfToken);

                if (editingEventId === null) {
                    formData.append('recurso_id', recursoId);
                    formData.append('user_id', userId);

                    fetch(eventosStoreUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData.toString()
                    })
                        .then(async response => {
                            if (!response.ok) {
                                let message = 'No se pudo crear la reserva.';
                                try {
                                    const data = await response.json();
                                    if (data && typeof data.message === 'string') {
                                        message = data.message;
                                    }
                                } catch (e) {
                                }
                                throw new Error(message);
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
                    fetch(eventosBaseUrl + '/' + editingEventId, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData.toString()
                    })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(function (data) {
                                    if (data && data.message) {
                                        throw new Error(data.message);
                                    }
                                    throw new Error('No se pudo actualizar la reserva.');
                                }).catch(function () {
                                    throw new Error('No se pudo actualizar la reserva.');
                                });
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
                formData.append('_token', csrfToken);

                fetch(eventosBaseUrl + '/' + editingEventId, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken,
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
                formData.append('_token', csrfToken);
                formData.append('inicio', inicio);
                formData.append('fin', fin);
                formData.append('_token', csrfToken);

                fetch(eventosBaseUrl + '/' + event.id, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken,
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
