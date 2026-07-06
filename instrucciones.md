# FACTURADOR PARA AUTÓNOMOS ESPAÑOLES

## DESCRIPCIÓN
Aplicación web local para gestionar facturas de autónomos en España. Laravel + SQLite para que cualquiera lo clone, haga `php artisan migrate --seed` y funcione sin configurar base de datos externa. Open source en GitHub.

## STACK
- Laravel 11+
- SQLite (archivo local, cero configuración)
- Blade + TailwindCSS (tema oscuro)
- Chart.js para gráficos
- DomPDF o Snappy para generar PDFs
- Sin dependencias externas ni APIs

## FUNCIONALIDADES

### 1. CONFIGURACIÓN INICIAL (primer uso)
Al entrar por primera vez, formulario de setup:
- Nombre completo
- DNI/NIF
- Dirección fiscal (calle, CP, ciudad, provincia)
- Teléfono
- Email
- IBAN (cuenta bancaria para mostrar en facturas)
- Logo (subir imagen, opcional)
- Tipo de IRPF: 7% (nuevos autónomos < 3 años) o 15%
- IVA por defecto: 21% (editable por factura)
- Numeración de facturas: prefijo + año automático (ejemplo: 01-2026, 02-2026...)

Estos datos se guardan en tabla `settings` y se pueden editar después desde Configuración.

### 2. CLIENTES
Tabla `clients`:
- id
- name (nombre o razón social)
- nif_cif
- address
- city
- postal_code
- province
- email (nullable)
- phone (nullable)
- notes (nullable)
- timestamps

CRUD completo con buscador por nombre o NIF.

### 3. FACTURAS
Tabla `invoices`:
- id
- invoice_number (autogenerado: siguiente número correlativo + año)
- client_id (FK)
- issue_date (fecha emisión, default hoy)
- due_date (fecha vencimiento, nullable)
- status (enum: 'borrador', 'enviada', 'pagada', 'vencida') default 'borrador'
- subtotal (calculado automático)
- iva_percentage (default 21)
- iva_amount (calculado)
- irpf_percentage (default del settings: 7 o 15)
- irpf_amount (calculado)
- total (calculado: subtotal + iva - irpf)
- notes (nullable, aparece al pie de la factura)
- payment_method (texto libre: "Transferencia bancaria", "Bizum", etc.)
- paid_at (fecha de pago, nullable)
- timestamps

Tabla `invoice_items`:
- id
- invoice_id (FK)
- description
- quantity (decimal)
- unit_price (decimal)
- total (calculado: quantity × unit_price)
- timestamps

**Funcionalidades de facturas:**
- Crear factura: seleccionar cliente (o crear nuevo inline), añadir líneas de concepto dinámicamente (añadir/quitar filas), los totales se calculan en tiempo real con JavaScript
- Editar factura: solo si está en borrador o enviada
- Cambiar estado: borrador → enviada → pagada (con fecha de pago)
- Duplicar factura: crea una copia con nuevo número y fecha de hoy (útil para facturas recurrentes)
- Marcar como pagada: botón rápido que cambia estado y pone fecha de pago = hoy
- Eliminar: solo borradores, con confirmación
- Numeración automática: al crear nueva factura, calcula el siguiente número correlativo del año actual
- Si cambia el año, la numeración empieza desde 1

### 4. GENERACIÓN DE PDF
Al hacer clic en "Descargar PDF" o "Ver PDF":
- Genera un PDF con el diseño profesional que ya hemos usado en las facturas anteriores
- Incluye: datos del autónomo (de settings), datos del cliente, número de factura, fecha, tabla de conceptos, subtotal, IVA, IRPF, total, IBAN
- Si hay logo configurado, aparece arriba a la izquierda
- Nombre del archivo: factura_{numero}.pdf

### 5. DASHBOARD
Pantalla principal al entrar:
- **Cards superiores:**
  - Facturado este mes (suma de totales de facturas pagadas del mes actual)
  - Facturado este trimestre (Q1: ene-mar, Q2: abr-jun, Q3: jul-sep, Q4: oct-dic)
  - Facturado este año
  - Pendiente de cobro (facturas enviadas no pagadas)
  - Número de facturas emitidas este mes

- **Gráfico:** facturación mensual de los últimos 12 meses (barras, Chart.js)
- **Gráfico:** facturación por trimestre del año actual (para ver de un vistazo los trimestrales)
- **Tabla:** últimas 5 facturas emitidas con estado
- **Tabla:** facturas pendientes de cobro (enviadas y vencidas)
- **Indicador de IVA trimestral:** suma del IVA repercutido (cobrado) del trimestre actual — esto es lo que toca pagar a Hacienda. Mostrar claramente: "IVA a pagar este trimestre: X€"
- **Indicador de IRPF:** suma de retenciones de IRPF del trimestre — esto ya te lo han retenido los clientes, es informativo

### 6. INFORMES / ESTADÍSTICAS
Página separada con:
- Filtro por rango de fechas
- Facturación total en el periodo
- Desglose por cliente (quién te ha pagado más)
- Base imponible total, IVA total, IRPF total del periodo
- Exportar a CSV (para pasar al gestor o al modelo 303/130)
- **Resumen trimestral** para declaraciones:
  - Q1, Q2, Q3, Q4 con: base imponible, IVA repercutido, IRPF retenido
  - Esto es exactamente lo que necesitas para rellenar el modelo 303 (IVA) y el 130 (IRPF)

### 7. GASTOS (opcional pero útil)
Tabla `expenses`:
- id
- description
- amount
- iva_amount (IVA soportado/deducible)
- date
- category (enum: 'hosting', 'software', 'material', 'transporte', 'formación', 'otros')
- deductible (boolean, default true)
- receipt_path (nullable, subir foto del ticket)
- timestamps

Si se implementan gastos:
- En el dashboard añadir: "IVA soportado (deducible) este trimestre"
- El cálculo real de IVA a pagar = IVA repercutido - IVA soportado
- En informes: beneficio real = facturado - gastos

### 8. NAVEGACIÓN
Sidebar con:
- Dashboard
- Facturas (listado + crear nueva)
- Clientes
- Informes
- Gastos (si se implementa)
- Configuración

## DISEÑO
- Tema oscuro: fondo #0f1117, cards #1a1b23, bordes #2a2b35, texto #e5e5e5, acento azul #3b82f6
- Responsive pero prioridad desktop (es herramienta de trabajo, se usa en PC)
- Fuente: Inter o system-ui
- Tablas con hover, ordenables por columna
- Botones de acción claros: "Nueva factura" siempre visible
- Estados con badges de color: borrador (gris), enviada (azul), pagada (verde), vencida (rojo)

## BASE DE DATOS — MIGRACIONES

```
settings: id, company_name, nif, address, city, postal_code, province, phone, email, iban, logo_path, irpf_default, iva_default, invoice_prefix, timestamps

clients: id, name, nif_cif, address, city, postal_code, province, email, phone, notes, timestamps

invoices: id, invoice_number, client_id, issue_date, due_date, status, subtotal, iva_percentage, iva_amount, irpf_percentage, irpf_amount, total, notes, payment_method, paid_at, timestamps

invoice_items: id, invoice_id, description, quantity, unit_price, total, timestamps

expenses: id, description, amount, iva_amount, date, category, deductible, receipt_path, timestamps
```

## SEEDERS
Crear seeder con:
- 1 configuración de ejemplo (datos ficticios de autónomo)
- 3 clientes de ejemplo
- 5 facturas de ejemplo con distintos estados
- Algunos gastos de ejemplo

## RUTAS PRINCIPALES
```
GET  /                          → Dashboard
GET  /invoices                  → Listado facturas
GET  /invoices/create           → Crear factura
POST /invoices                  → Guardar factura
GET  /invoices/{id}             → Ver factura
GET  /invoices/{id}/edit        → Editar factura
PUT  /invoices/{id}             → Actualizar factura
DELETE /invoices/{id}           → Eliminar factura
GET  /invoices/{id}/pdf         → Descargar PDF
POST /invoices/{id}/duplicate   → Duplicar factura
PATCH /invoices/{id}/status     → Cambiar estado (AJAX)
GET  /clients                   → Listado clientes
RESOURCE /clients               → CRUD clientes
GET  /reports                   → Informes con filtros
GET  /reports/export            → Exportar CSV
GET  /expenses                  → Listado gastos
RESOURCE /expenses              → CRUD gastos
GET  /settings                  → Configuración
PUT  /settings                  → Guardar configuración
```

## PARA EL README DE GITHUB
```
# Facturador para Autónomos 🇪🇸

Aplicación gratuita y open source para gestionar facturas como autónomo en España.

## Features
- Crea y gestiona facturas con IVA (21%) e IRPF (7%/15%)
- Generación de PDF profesional
- Dashboard con facturación mensual, trimestral y anual
- Gestión de clientes
- Control de gastos deducibles
- Resumen trimestral para el modelo 303 y 130
- Exportación CSV
- SQLite: cero configuración de base de datos

## Instalación
git clone https://github.com/tuusuario/facturador-autonomos.git
cd facturador-autonomos
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve

Abre http://localhost:8000 y listo.

## Stack
Laravel 11 + SQLite + Blade + TailwindCSS + Chart.js + DomPDF

## Licencia
MIT — Usa, modifica y comparte libremente.
```

## PRIORIDAD DE DESARROLLO
1. Settings + Clientes + Facturas + PDF → funcional mínimo
2. Dashboard con gráficos
3. Informes y exportación CSV
4. Gastos
5. Pulir UI y README para GitHub