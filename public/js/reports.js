(function () {
    'use strict';

    const printBtn = document.getElementById('btn-print');
    if (printBtn) {
        printBtn.addEventListener('click', () => window.print());
    }

    const exportBtn = document.getElementById('btn-export');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            const table = document.querySelector('.table-wrap table');
            if (!table) return;

            const rows  = Array.from(table.querySelectorAll('tr'));
            const csv   = rows.map(row =>
                Array.from(row.querySelectorAll('th, td'))
                    .map(cell => '"' + cell.innerText.replace(/"/g, '""').trim() + '"')
                    .join(',')
            ).join('\r\n');

            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href     = url;
            a.download = 'usep_vrs_report_' + new Date().toISOString().slice(0, 10) + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    }

    const outputPanel = document.querySelectorAll('.panel.active')[1];
    if (outputPanel) {
        const ph = outputPanel.querySelector('.panel-header');
        if (ph && outputPanel.querySelector('table')) {
            const wrap = document.createElement('div');
            wrap.style.cssText = 'display:flex;gap:8px;';

            const print = document.createElement('button');
            print.id        = 'btn-print';
            print.className = 'btn-sm';
            print.textContent = '🖨 Print';
            print.onclick   = () => window.print();

            const exp = document.createElement('button');
            exp.id        = 'btn-export';
            exp.className = 'btn-sm';
            exp.textContent = '⬇ Export CSV';
            exp.addEventListener('click', () => {
                const table = outputPanel.querySelector('table');
                const rows  = Array.from(table.querySelectorAll('tr'));
                const csv   = rows.map(r =>
                    Array.from(r.querySelectorAll('th, td'))
                        .map(c => '"' + c.innerText.replace(/"/g,'""').trim() + '"')
                        .join(',')
                ).join('\r\n');
                const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
                const url  = URL.createObjectURL(blob);
                const a    = document.createElement('a');
                a.href = url; a.download = 'report.csv';
                document.body.appendChild(a); a.click();
                document.body.removeChild(a); URL.revokeObjectURL(url);
            });

            wrap.appendChild(print);
            wrap.appendChild(exp);
            ph.appendChild(wrap);
        }
    }

})();