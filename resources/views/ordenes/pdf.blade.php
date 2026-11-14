<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Reparación #{{ $orden->id }} - Lorebel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #1e293b;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .document-wrapper {
            padding: 50px;
        }

        .document-wrapper {
            padding: 50px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: #2563eb;
            border-radius: 8px;
            color: white;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .header .subtitle {
            font-size: 16px;
            opacity: 0.9;
        }

        .orden-numero {
            background: white;
            color: #2563eb;
            padding: 12px 28px;
            display: inline-block;
            border-radius: 6px;
            margin-top: 15px;
            font-size: 18px;
            font-weight: 600;
        }

        .info-section {
            margin-bottom: 30px;
            background: #f8fafc;
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #2563eb;
        }

        .info-section h2 {
            color: #2563eb;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 10px;
        }

        .info-item {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .info-item label {
            display: block;
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .info-item .value {
            font-size: 15px;
            color: #1e293b;
            font-weight: 600;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .estado-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .estado-ingresado {
            background: #dbeafe;
            color: #1e40af;
        }
        .estado-en_reparacion {
            background: #fed7aa;
            color: #9a3412;
        }
        .estado-esperando_repuesto {
            background: #fecaca;
            color: #991b1b;
        }
        .estado-listo {
            background: #d1fae5;
            color: #065f46;
        }
        .estado-entregado {
            background: #ccfbf1;
            color: #134e4a;
        }
        .estado-cancelado {
            background: #fee2e2;
            color: #991b1b;
        }

        .problema-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            margin: 15px 0;
            border-radius: 6px;
        }

        .diagnostico-box {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 15px 0;
            border-radius: 6px;
        }

        .trabajo-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 15px 0;
            border-radius: 6px;
        }

        .costos-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .costos-table td {
            padding: 15px;
            background: white;
        }

        .costos-table tr:not(:last-child) td {
            border-bottom: 1px solid #e2e8f0;
        }

        .costos-table .label {
            color: #64748b;
            width: 60%;
            font-weight: 500;
        }

        .costos-table .amount {
            text-align: right;
            font-weight: 600;
            color: #1e293b;
            font-size: 16px;
        }

        .costos-table .total-row {
            background: #2563eb;
            color: white;
            font-size: 18px;
        }

        .costos-table .total-row td {
            border-bottom: none;
            padding: 20px 15px;
        }

        .presupuesto-info {
            background: #d1fae5;
            border: 2px solid #10b981;
            padding: 25px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
        }

        .presupuesto-info .amount {
            font-size: 32px;
            color: #065f46;
            font-weight: 700;
        }

        .presupuesto-info .status {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 20px;
            background: #059669;
            color: white;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .footer {
            margin-top: 50px;
            padding: 30px;
            text-align: center;
            color: white;
            background: #2563eb;
            border-radius: 8px;
            font-size: 13px;
        }

        .footer strong {
            font-size: 15px;
            display: block;
            margin-bottom: 8px;
        }

        .signature-area {
            margin-top: 60px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 50px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #2563eb;
            margin-top: 80px;
            padding-top: 15px;
            font-weight: 600;
            color: #1e293b;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .container {
                box-shadow: none;
                max-width: 100%;
            }

            .no-print {
                display: none;
            }
        }

        /* Botón de impresión flotante */
        .print-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            z-index: 1000;
            transition: all 0.2s;
        }

        .print-button:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
        }

        .back-button {
            position: fixed;
            bottom: 30px;
            left: 30px;
            background: white;
            color: #2563eb;
            border: 2px solid #2563eb;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .back-button:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="document-wrapper">
    <!-- Encabezado -->
    <div class="header">
        <h1>🔧 Lorebel</h1>
        <div class="subtitle">Servicio Técnico Profesional</div>
        <div class="orden-numero">ORDEN #{{ $orden->id }}</div>
    </div>

    <!-- Información del Cliente -->
    <div class="info-section">
        <h2>📋 Información del Cliente</h2>
        <div class="info-grid">
            @if($orden->cliente)
                <div class="info-item">
                    <label>Nombre Completo</label>
                    <div class="value">{{ optional($orden->cliente)->nombre ?? 'Sin cliente' }}</div>
                </div>
                <div class="info-item">
                    <label>Teléfono</label>
                    <div class="value">{{ optional($orden->cliente)->telefono ?? '—' }}</div>
                </div>
                @if(optional($orden->cliente)->whatsapp)
                    <div class="info-item">
                        <label>WhatsApp</label>
                        <div class="value">{{ $orden->cliente->whatsapp }}</div>
                    </div>
                @endif
                @if(optional($orden->cliente)->email)
                    <div class="info-item">
                        <label>Email</label>
                        <div class="value">{{ $orden->cliente->email }}</div>
                    </div>
                @endif
                @if(optional($orden->cliente)->dni_cuit)
                    <div class="info-item">
                        <label>DNI / CUIT</label>
                        <div class="value">{{ $orden->cliente->dni_cuit }}</div>
                    </div>
                @endif
            @else
                <div class="info-item full-width">
                    <div class="value">Sin cliente asignado</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Información del Equipo -->
    <div class="info-section">
        <h2>📱 Información del Equipo</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Tipo de Dispositivo</label>
                <div class="value">{{ $orden->dispositivo }}</div>
            </div>
            <div class="info-item">
                <label>Marca</label>
                <div class="value">{{ $orden->marca }}</div>
            </div>
            <div class="info-item">
                <label>Modelo</label>
                <div class="value">{{ $orden->modelo }}</div>
            </div>
            @if($orden->imei_serial)
                <div class="info-item">
                    <label>IMEI / Serial</label>
                    <div class="value">{{ $orden->imei_serial }}</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Estado de la Orden -->
    <div class="info-section">
        <h2>⚙️ Estado de la Orden</h2>
        <div style="margin-top: 10px;">
            <span class="estado-badge estado-{{ $orden->estado }}">
                {{ $orden->estado_texto }}
            </span>
        </div>
    </div>

    <!-- Problema Reportado -->
    <div class="info-section">
        <h2>⚠️ Problema Reportado</h2>
        <div class="problema-box">
            {{ $orden->problema_reportado }}
        </div>
    </div>

    <!-- Diagnóstico Técnico -->
    @if($orden->diagnostico_tecnico)
        <div class="info-section">
            <h2>🔍 Diagnóstico Técnico</h2>
            <div class="diagnostico-box">
                {{ $orden->diagnostico_tecnico }}
            </div>
        </div>
    @endif

    <!-- Trabajo Realizado -->
    @if($orden->trabajo_realizado)
        <div class="info-section">
            <h2>✔️ Trabajo Realizado</h2>
            <div class="trabajo-box">
                {{ $orden->trabajo_realizado }}
            </div>
        </div>
    @endif

    <!-- Presupuesto -->
    @if($orden->presupuesto)
        <div class="info-section">
            <h2>💰 Presupuesto</h2>
            <div class="presupuesto-info">
                <div class="amount">${{ number_format($orden->presupuesto, 2) }}</div>
                <div class="status">
                    {{ $orden->presupuesto_estado_texto }}
                    @if($orden->presupuesto_aprobado === true)
                        ✓
                    @elseif($orden->presupuesto_aprobado === false)
                        ✗
                    @endif
                </div>
                @if($orden->fecha_aprobacion_presupuesto)
                    <div style="margin-top: 10px; font-size: 12px; color: #555;">
                        Fecha de aprobación: {{ $orden->fecha_aprobacion_presupuesto->format('d/m/Y') }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Desglose de Costos -->
    @if($orden->costo_repuestos > 0 || $orden->costo_mano_obra > 0)
        <div class="info-section">
            <h2>💵 Desglose de Costos</h2>
            <table class="costos-table">
                <tr>
                    <td class="label">Costo de Repuestos</td>
                    <td class="amount">${{ number_format($orden->costo_repuestos, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Costo de Mano de Obra</td>
                    <td class="amount">${{ number_format($orden->costo_mano_obra, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="label">TOTAL</td>
                    <td class="amount">${{ number_format($orden->costo_total, 2) }}</td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Técnico Asignado -->
    @if($orden->tecnico)
        <div class="info-section">
            <h2>👨‍🔧 Técnico Asignado</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Nombre</label>
                    <div class="value">{{ $orden->tecnico->nombre_completo }}</div>
                </div>
                @if($orden->tecnico->especialidad)
                    <div class="info-item">
                        <label>Especialidad</label>
                        <div class="value">{{ $orden->tecnico->especialidad }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Fechas Importantes -->
    <div class="info-section">
        <h2>📅 Fechas Importantes</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Fecha de Ingreso</label>
                <div class="value">{{ $orden->fecha_ingreso->format('d/m/Y') }}</div>
            </div>
            @if($orden->fecha_estimada_entrega)
                <div class="info-item">
                    <label>Entrega Estimada</label>
                    <div class="value">{{ $orden->fecha_estimada_entrega->format('d/m/Y') }}</div>
                </div>
            @endif
            @if($orden->fecha_entrega)
                <div class="info-item">
                    <label>Fecha de Entrega</label>
                    <div class="value">{{ $orden->fecha_entrega->format('d/m/Y') }}</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Área de Firmas -->
    <div class="signature-area">
        <div class="signature-box">
            <div class="signature-line">
                <strong>Firma del Cliente</strong><br>
                Aclaración: {{ optional($orden->cliente)->nombre ?? '—' }}
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                <strong>Firma del Técnico</strong><br>
                @if($orden->tecnico)
                    Aclaración: {{ $orden->tecnico->nombre_completo }}
                @else
                    Aclaración: ___________________________
                @endif
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <strong>Lorebel - Servicio Técnico Profesional</strong>
        <p style="margin: 8px 0;">Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p style="margin: 0; opacity: 0.9;">Este comprobante es válido como constancia de ingreso y seguimiento de la orden de reparación</p>
    </div>

        </div><!-- /document-wrapper -->
    </div><!-- /container -->

    <!-- Botones flotantes -->
    <a href="{{ route('ordenes.show', $orden) }}" class="back-button no-print">
        ← Volver a la Orden
    </a>
    <button onclick="window.print()" class="print-button no-print">
        🖨️ Imprimir Comprobante
    </button>
</body>
</html>
