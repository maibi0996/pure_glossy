
@extends('layouts.nav')

@section('title', 'Clientes')

@section('ladoizq')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row h-100">
    <div class="col-lg-8 d-flex flex-column">
        <!-- Cuadro de clientes -->
        <div class="card mb-3 flex-grow-1 left-table border-secondary">
            <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white border-bottom border-secondary py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fas fa-address-book me-2"></i>Clientes con Cuenta Corriente
                </h5>
                <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#veragregarcliente">
                    <i class="fas fa-plus me-1"></i> Agregar Cliente
                </button>
            </div>
            <div class="card-body d-flex flex-column p-0">
                <div class="table-responsive flex-grow-1 table-scrollgr">
                    <table class="table table-dark table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre y Apellido</th>
                                <th>DNI / Teléfono</th>
                                <th>Estado Cuenta</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clientes as $cliente)
                            @php
                                $saldo = (float) ($cliente->saldo_actual ?? 0);
                                $alDia = $saldo <= 0;
                            @endphp
                            <tr>
                                <td>{{ $cliente->id_cliente }}</td>
                                <td>
                                    <div class="fw-bold">{{ $cliente->nombre_y_apellido }}</div>
                                    <small class="text-white-50">
                                        {{ $cliente->localidad ?: 'Sin localidad' }}
                                        @if($cliente->email) · {{ $cliente->email }} @endif
                                    </small>
                                </td>
                                <td>
                                    <div>DNI: {{ $cliente->DNI }}</div>
                                    <small class="text-white-50">Tel: {{ $cliente->telefono }}</small>
                                </td>
                                <td>
                                    @if($alDia)
                                        <span class="badge bg-success">Al día</span>
                                        @if($saldo < 0)
                                            <div class="small text-info">A favor: ${{ number_format(abs($saldo), 2) }}</div>
                                        @endif
                                    @else
                                        <span class="badge bg-warning text-dark">Saldo pendiente</span>
                                        <div class="small text-warning fw-bold">${{ number_format($saldo, 2) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $cliente->estado === 'activo' ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ ucfirst($cliente->estado) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center flex-wrap gap-1">
                                        <button type="button"
                                            class="btn btn-info btn-sm text-dark fw-bold btn-ver-ficha"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalFichaCliente"
                                            data-id="{{ $cliente->id_cliente }}">
                                            Ficha
                                        </button>

                                        <button type="button"
                                            class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#vermodificarcliente"
                                            data-id="{{ $cliente->id_cliente }}"
                                            data-nombre_y_apellido="{{ $cliente->nombre_y_apellido }}"
                                            data-dni="{{ $cliente->DNI }}"
                                            data-telefono="{{ $cliente->telefono }}"
                                            data-direccion="{{ $cliente->direccion }}"
                                            data-localidad="{{ $cliente->localidad }}"
                                            data-email="{{ $cliente->email }}"
                                            data-estado="{{ $cliente->estado }}">
                                            Modificar
                                        </button>

                                        <form class="m-0 d-inline" action="{{ route('clientes.borrar', ['id_cliente' => $cliente->id_cliente]) }}" method="POST" onsubmit="return confirm('¿Desactivar este cliente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Desactivar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna derecha superior -->
    <div class="col-lg-4 right-column">
        @include('parciales.columna_derecha')
    </div>
</div>

<!-- Modal agregar cliente -->
<div class="modal fade" id="veragregarcliente" tabindex="-1" aria-labelledby="veragregarclienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white border-secondary">
            <form action="{{ route('clientes.agregar') }}" method="POST">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="veragregarclienteLabel">Agregar cliente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="add_nombre_y_apellido" class="form-label">Nombre y apellido</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="add_nombre_y_apellido" name="nombre_y_apellido" required>
                        </div>
                        <div class="col-md-3">
                            <label for="add_dni" class="form-label">DNI</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="add_dni" name="dni" required>
                        </div>
                        <div class="col-md-3">
                            <label for="add_telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="add_telefono" name="telefono" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add_direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="add_direccion" name="direccion">
                        </div>
                        <div class="col-md-6">
                            <label for="add_localidad" class="form-label">Localidad</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="add_localidad" name="localidad">
                        </div>
                        <div class="col-md-12">
                            <label for="add_email" class="form-label">E-mail</label>
                            <input type="email" class="form-control bg-dark text-white border-secondary" id="add_email" name="email">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" class="btn btn-success">Agregar cliente</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal modificar cliente -->
<div class="modal fade" id="vermodificarcliente" tabindex="-1" aria-labelledby="vermodificarclienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white border-secondary">
            <form action="{{ route('clientes.modificar') }}" method="POST">
                @csrf
                <input type="hidden" name="modal_id_cliente" id="mod_id_cliente">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="vermodificarclienteLabel">Modificar Cliente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="mod_nombre_y_apellido" class="form-label">Nombre y apellido</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="mod_nombre_y_apellido" name="nombre_y_apellido" required>
                        </div>
                        <div class="col-md-3">
                            <label for="mod_dni" class="form-label">DNI</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="mod_dni" name="dni" required>
                        </div>
                        <div class="col-md-3">
                            <label for="mod_telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="mod_telefono" name="telefono" required>
                        </div>
                        <div class="col-md-6">
                            <label for="mod_direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="mod_direccion" name="direccion">
                        </div>
                        <div class="col-md-6">
                            <label for="mod_localidad" class="form-label">Localidad</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="mod_localidad" name="localidad">
                        </div>
                        <div class="col-md-6">
                            <label for="mod_email" class="form-label">E-mail</label>
                            <input type="email" class="form-control bg-dark text-white border-secondary" id="mod_email" name="email">
                        </div>
                        <div class="col-md-6">
                            <label for="mod_estado" class="form-label">Estado</label>
                            <select name="estado" id="mod_estado" class="form-select bg-dark text-white border-secondary">
                                <option value="activo">Activo</option>
                                <option value="desactivado">Desactivado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" class="btn btn-primary">Modificar cliente</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ficha e Historial Imprimible -->
<div class="modal fade" id="modalFichaCliente" tabindex="-1" aria-labelledby="modalFichaClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="modalFichaClienteLabel">
                    <i class="fas fa-id-card me-2"></i>Ficha y Cuenta Corriente del Cliente
                </h5>
                <div class="d-flex gap-2 no-print">
                    <button type="button" class="btn btn-outline-light btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Imprimir Ficha
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <!-- Datos de la ficha -->
                <div class="row g-3 mb-4 p-3 rounded border border-secondary">
                    <div class="col-md-6">
                        <div><strong>Nombre y Apellido:</strong> <span id="ficha_nombre">-</span></div>
                        <div><strong>DNI:</strong> <span id="ficha_dni">-</span></div>
                        <div><strong>Teléfono:</strong> <span id="ficha_telefono">-</span></div>
                        <div><strong>Estado del cliente:</strong> <span id="ficha_estado">-</span></div>
                    </div>
                    <div class="col-md-6">
                        <div><strong>Dirección:</strong> <span id="ficha_direccion">-</span></div>
                        <div><strong>Localidad:</strong> <span id="ficha_localidad">-</span></div>
                        <div><strong>E-mail:</strong> <span id="ficha_email">-</span></div>
                        <div class="mt-1">
                            <strong>Estado de cuenta:</strong> <span id="ficha_estado_cta" class="badge">-</span>
                            <strong class="ms-2">Saldo:</strong> <span id="ficha_saldo" class="fw-bold">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Registrar pago o ajuste -->
                <form id="formMovimientoCC" class="row g-2 align-items-end mb-4 p-3 rounded border border-secondary ficha-resumen d-print-none">
                    <input type="hidden" id="mov_id_cliente">
                    <div class="col-md-2">
                        <label for="mov_tipo" class="form-label">Tipo</label>
                        <select id="mov_tipo" name="tipo" class="form-select" required onchange="toggleSentidoAjuste()">
                            <option value="pago">Pago</option>
                            <option value="ajuste">Ajuste</option>
                        </select>
                    </div>
                    <div class="col-md-2" id="contenedor_sentido_ajuste" style="display: none;">
                        <label for="mov_sentido" class="form-label">Sentido</label>
                        <select id="mov_sentido" name="sentido_ajuste" class="form-select">
                            <option value="descuento">Resta deuda</option>
                            <option value="cargo">Suma deuda</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="mov_monto" class="form-label">Monto ($)</label>
                        <input type="number" step="0.01" min="0.01" id="mov_monto" name="monto" class="form-control" required>
                    </div>
                    <div class="col-md-3" id="contenedor_concepto_mov">
                        <label for="mov_concepto" class="form-label">Concepto / Detalle</label>
                        <input type="text" id="mov_concepto" name="concepto" class="form-control" placeholder="Ej: Pago efectivo, Bonificación...">
                    </div>
                    <div class="col-md-3" id="contenedor_btn_registrar">
                        <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                            <i class="fas fa-check me-1"></i> Registrar
                        </button>
                    </div>
                </form>

                <!-- Historial de movimientos -->
                <h6 class="fw-bold mb-2">Historial de Movimientos</h6>
                <div class="table-responsive">
                    <table class="table table-dark table-striped table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Concepto</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody id="tablaMovimientosCliente">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Cargando movimientos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-secondary no-print">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modificarclienteModal = document.getElementById('vermodificarcliente');
        modificarclienteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('mod_id_cliente').value = button.getAttribute('data-id');
            document.getElementById('mod_nombre_y_apellido').value = button.getAttribute('data-nombre_y_apellido') || '';
            document.getElementById('mod_dni').value = button.getAttribute('data-dni') || '';
            document.getElementById('mod_telefono').value = button.getAttribute('data-telefono') || '';
            document.getElementById('mod_direccion').value = button.getAttribute('data-direccion') || '';
            document.getElementById('mod_localidad').value = button.getAttribute('data-localidad') || '';
            document.getElementById('mod_email').value = button.getAttribute('data-email') || '';
            document.getElementById('mod_estado').value = button.getAttribute('data-estado') || 'activo';
        });

        const modalFicha = document.getElementById('modalFichaCliente');
        modalFicha.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            const idCliente = button.getAttribute('data-id');
            document.getElementById('mov_id_cliente').value = idCliente;
            cargarFichaCliente(idCliente);
        });

        const formMov = document.getElementById('formMovimientoCC');
        formMov.addEventListener('submit', function (e) {
            e.preventDefault();
            const idCliente = document.getElementById('mov_id_cliente').value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch(`/clientes/${idCliente}/movimientos`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tipo: document.getElementById('mov_tipo').value,
                    sentido_ajuste: document.getElementById('mov_sentido').value,
                    monto: document.getElementById('mov_monto').value,
                    concepto: document.getElementById('mov_concepto').value
                })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'No se pudo registrar el movimiento.');
                    return;
                }
                formMov.reset();
                toggleSentidoAjuste();
                cargarFichaCliente(idCliente);
            })
            .catch(err => console.error('Error registrando movimiento:', err));
        });
    });

    function toggleSentidoAjuste() {
        const tipo = document.getElementById('mov_tipo').value;
        const contSentido = document.getElementById('contenedor_sentido_ajuste');
        const contConcepto = document.getElementById('contenedor_concepto_mov');
        const contBtn = document.getElementById('contenedor_btn_registrar');

        if (tipo === 'ajuste') {
            contSentido.style.display = 'block';
            contConcepto.className = 'col-md-3';
            contBtn.className = 'col-md-3';
        } else {
            contSentido.style.display = 'none';
            contConcepto.className = 'col-md-5';
            contBtn.className = 'col-md-3';
        }
    }

    function cargarFichaCliente(idCliente) {
        const tbody = document.getElementById('tablaMovimientosCliente');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr>';

        fetch(`/clientes/${idCliente}/movimientos`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-3">${data.error}</td></tr>`;
                    return;
                }

                const c = data.cliente;
                document.getElementById('ficha_nombre').textContent = c.nombre_y_apellido || '-';
                document.getElementById('ficha_dni').textContent = c.DNI || '-';
                document.getElementById('ficha_telefono').textContent = c.telefono || '-';
                document.getElementById('ficha_estado').textContent = c.estado || '-';
                document.getElementById('ficha_direccion').textContent = c.direccion || '-';
                document.getElementById('ficha_localidad').textContent = c.localidad || '-';
                document.getElementById('ficha_email').textContent = c.email || '-';

                const badgeCta = document.getElementById('ficha_estado_cta');
                badgeCta.textContent = data.estado_cta;
                badgeCta.className = 'badge ' + (data.saldo > 0 ? 'bg-warning text-dark' : 'bg-success');

                const saldoSpan = document.getElementById('ficha_saldo');
                saldoSpan.textContent = `$${parseFloat(data.saldo || 0).toFixed(2)}`;
                saldoSpan.className = 'fw-bold ' + (data.saldo > 0 ? 'text-warning' : 'text-success');

                if (!data.movimientos || data.movimientos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Sin movimientos registrados.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                data.movimientos.forEach(mov => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${mov.fecha}</td>
                        <td class="text-uppercase">${mov.tipo}</td>
                        <td>${mov.concepto || '-'}</td>
                        <td class="text-end fw-bold">$${parseFloat(mov.monto).toFixed(2)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            })
            .catch(err => {
                console.error('Error cargando ficha:', err);
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Error al cargar movimientos.</td></tr>';
            });
    }
</script>
@endsection