import {
    Chart,
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    LinearScale,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    LinearScale,
    Tooltip,
    Legend,
);

const data = window.GRAFIK;

if (data) {
    const warnaStatus = {
        hadir: '#10b981',
        terlambat: '#f59e0b',
        izin: '#3b82f6',
        sakit: '#8b5cf6',
        alfa: '#f43f5e',
    };

    // 1. Doughnut kehadiran hari ini
    const ctxHadir = document.getElementById('grafik-kehadiran');
    if (ctxHadir) {
        const labels = Object.keys(data.kehadiran).filter((k) => data.kehadiran[k] > 0);
        new Chart(ctxHadir, {
            type: 'doughnut',
            data: {
                labels: labels.map((k) => data.labelStatus[k] ?? k),
                datasets: [{
                    data: labels.map((k) => data.kehadiran[k]),
                    backgroundColor: labels.map((k) => warnaStatus[k] ?? '#94a3b8'),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                },
            },
        });
    }

    // 2. Bar pelanggaran per bulan (6 bulan)
    const ctxBulan = document.getElementById('grafik-pelanggaran-bulan');
    if (ctxBulan) {
        new Chart(ctxBulan, {
            type: 'bar',
            data: {
                labels: data.bulan.labels,
                datasets: [{
                    label: 'Total poin',
                    data: data.bulan.poin,
                    backgroundColor: 'rgb(244 63 94 / 0.75)',
                    borderRadius: 6,
                    maxBarThickness: 36,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }

    // 3. Bar horizontal top siswa poin tertinggi
    const ctxSiswa = document.getElementById('grafik-top-siswa');
    if (ctxSiswa) {
        new Chart(ctxSiswa, {
            type: 'bar',
            data: {
                labels: data.topSiswa.map((s) => s.nama),
                datasets: [{
                    label: 'Poin',
                    data: data.topSiswa.map((s) => s.poin),
                    backgroundColor: data.topSiswa.map((s) => (s.poin >= 40 ? '#e11d48' : s.poin >= 20 ? '#f97316' : '#f59e0b')),
                    borderRadius: 6,
                    maxBarThickness: 22,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }
}
