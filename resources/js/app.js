import {
    Chart,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
} from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

const euro = (value) =>
    new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(value);

/* ------------------------------------------------------------------ */
/* Gráficos de barras (dashboard)                                      */
/* ------------------------------------------------------------------ */
document.querySelectorAll('canvas[data-chart="bar"]').forEach((canvas) => {
    const labels = JSON.parse(canvas.dataset.labels);
    const values = JSON.parse(canvas.dataset.values);

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    data: values,
                    backgroundColor: 'rgba(59, 130, 246, 0.55)',
                    hoverBackgroundColor: 'rgba(59, 130, 246, 0.85)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 48,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    backgroundColor: '#1a1b23',
                    borderColor: '#2a2b35',
                    borderWidth: 1,
                    titleColor: '#e5e5e5',
                    bodyColor: '#e5e5e5',
                    callbacks: {
                        label: (ctx) => euro(ctx.parsed.y),
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#9ca3af' },
                    border: { color: '#2a2b35' },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(42, 43, 53, 0.6)' },
                    ticks: {
                        color: '#9ca3af',
                        callback: (value) => euro(value),
                    },
                    border: { display: false },
                },
            },
        },
    });
});

/* ------------------------------------------------------------------ */
/* Formulario de factura: líneas dinámicas y totales en tiempo real    */
/* ------------------------------------------------------------------ */
const invoiceForm = document.querySelector('[data-invoice-form]');

if (invoiceForm) {
    const rowsContainer = invoiceForm.querySelector('#item-rows');
    const template = document.getElementById('item-row-template');
    const addButton = invoiceForm.querySelector('#add-item');

    const renumberRows = () => {
        rowsContainer.querySelectorAll('[data-item-row]').forEach((row, index) => {
            row.querySelectorAll('input').forEach((input) => {
                input.name = input.name.replace(/items\[[^\]]*\]/, `items[${index}]`);
            });
        });
    };

    const recalc = () => {
        let subtotal = 0;

        rowsContainer.querySelectorAll('[data-item-row]').forEach((row) => {
            const qty = parseFloat(row.querySelector('[data-field="quantity"]').value) || 0;
            const price = parseFloat(row.querySelector('[data-field="unit_price"]').value) || 0;
            const lineTotal = qty * price;
            row.querySelector('[data-field="line-total"]').textContent = euro(lineTotal);
            subtotal += lineTotal;
        });

        const ivaPct = parseFloat(invoiceForm.querySelector('[name="iva_percentage"]').value) || 0;
        const irpfPct = parseFloat(invoiceForm.querySelector('[name="irpf_percentage"]').value) || 0;
        const iva = subtotal * (ivaPct / 100);
        const irpf = subtotal * (irpfPct / 100);

        invoiceForm.querySelector('[data-total="subtotal"]').textContent = euro(subtotal);
        invoiceForm.querySelector('[data-total="iva"]').textContent = euro(iva);
        invoiceForm.querySelector('[data-total="irpf"]').textContent = `−${euro(irpf)}`;
        invoiceForm.querySelector('[data-total="total"]').textContent = euro(subtotal + iva - irpf);
        invoiceForm.querySelector('[data-label="iva"]').textContent = `IVA (${ivaPct}%)`;
        invoiceForm.querySelector('[data-label="irpf"]').textContent = `IRPF (−${irpfPct}%)`;
    };

    const addRow = () => {
        rowsContainer.appendChild(template.content.cloneNode(true));
        renumberRows();
        recalc();
    };

    addButton.addEventListener('click', addRow);

    rowsContainer.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-row]');
        if (!removeButton) return;

        if (rowsContainer.querySelectorAll('[data-item-row]').length > 1) {
            removeButton.closest('[data-item-row]').remove();
            renumberRows();
            recalc();
        }
    });

    invoiceForm.addEventListener('input', recalc);

    // Cliente inline: mostrar los campos de nuevo cliente
    const clientSelect = invoiceForm.querySelector('[name="client_id"]');
    const newClientFields = invoiceForm.querySelector('#new-client-fields');

    if (clientSelect && newClientFields) {
        const toggleNewClient = () => {
            newClientFields.classList.toggle('hidden', clientSelect.value !== 'new');
        };
        clientSelect.addEventListener('change', toggleNewClient);
        toggleNewClient();
    }

    if (rowsContainer.querySelectorAll('[data-item-row]').length === 0) {
        addRow();
    }

    recalc();
}

/* ------------------------------------------------------------------ */
/* Tablas ordenables por columna                                       */
/* ------------------------------------------------------------------ */
document.querySelectorAll('table[data-sortable]').forEach((table) => {
    table.querySelectorAll('thead th[data-sort]').forEach((th) => {
        th.classList.add('cursor-pointer', 'select-none');
        th.addEventListener('click', () => {
            const tbody = table.querySelector('tbody');
            const index = [...th.parentNode.children].indexOf(th);
            const type = th.dataset.sort;
            const asc = th.dataset.dir !== 'asc';

            table.querySelectorAll('thead th').forEach((h) => delete h.dataset.dir);
            th.dataset.dir = asc ? 'asc' : 'desc';

            [...tbody.querySelectorAll('tr')]
                .sort((a, b) => {
                    let va = a.children[index]?.dataset.value ?? a.children[index]?.textContent.trim() ?? '';
                    let vb = b.children[index]?.dataset.value ?? b.children[index]?.textContent.trim() ?? '';
                    if (type === 'number') {
                        va = parseFloat(va) || 0;
                        vb = parseFloat(vb) || 0;
                        return asc ? va - vb : vb - va;
                    }
                    return asc ? String(va).localeCompare(vb, 'es') : String(vb).localeCompare(va, 'es');
                })
                .forEach((tr) => tbody.appendChild(tr));
        });
    });
});
