let barChart, pieChart, lineChart;

async function fetchAnalytics() {
    const response = await fetch('dashboard.php?mode=json');
    if (!response.ok) {
        throw new Error('Failed to load analytics data');
    }
    return response.json();
}

function buildBarChart(ctx, data) {
    const labels = data.map(d => d.file_type);
    const values = data.map(d => Number(d.total_files));

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Files per type',
                data: values,
                backgroundColor: 'rgba(59,130,246,0.6)',
                borderColor: 'rgba(59,130,246,1)',
                borderWidth: 1.5,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    ticks: { color: '#9ca3af' },
                    grid: { display: false }
                },
                y: {
                    ticks: { color: '#9ca3af', precision: 0 },
                    grid: { color: 'rgba(31,41,55,0.7)' }
                }
            }
        }
    });
}

function buildPieChart(ctx, data) {
    const labels = data.map(d => d.file_type);
    const values = data.map(d => Number(d.total_files));
    const colors = [
        'rgba(59,130,246,0.7)',
        'rgba(34,197,94,0.7)',
        'rgba(234,179,8,0.7)',
        'rgba(239,68,68,0.7)',
        'rgba(139,92,246,0.7)',
        'rgba(16,185,129,0.7)'
    ];

    return new Chart(ctx, {
        type: 'pie',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors.slice(0, values.length),
                borderColor: '#020617',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#9ca3af' }
                }
            }
        }
    });
}

function buildLineChart(ctx, data) {
    const labels = data.map(d => d.upload_day);
    const values = data.map(d => Number(d.total_uploads));

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Uploads per day',
                data: values,
                fill: false,
                borderColor: 'rgba(248,250,252,0.85)',
                backgroundColor: 'rgba(248,250,252,0.9)',
                tension: 0.25,
                pointRadius: 3,
                pointBackgroundColor: 'rgba(248,250,252,0.9)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    ticks: { color: '#9ca3af' },
                    grid: { display: false }
                },
                y: {
                    ticks: { color: '#9ca3af', precision: 0 },
                    grid: { color: 'rgba(31,41,55,0.7)' }
                }
            }
        }
    });
}

async function loadCharts() {
    try {
        const data = await fetchAnalytics();
        const barCtx = document.getElementById('barChart').getContext('2d');
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        const lineCtx = document.getElementById('lineChart').getContext('2d');

        if (barChart) barChart.destroy();
        if (pieChart) pieChart.destroy();
        if (lineChart) lineChart.destroy();

        barChart = buildBarChart(barCtx, data.filesPerType);
        pieChart = buildPieChart(pieCtx, data.filesPerType);
        lineChart = buildLineChart(lineCtx, data.uploadsPerDay);

        const totalFilesSpan = document.getElementById('totalFiles');
        const totalTypesSpan = document.getElementById('totalTypes');
        const lastUploadSpan = document.getElementById('lastUpload');

        if (totalFilesSpan && totalTypesSpan && lastUploadSpan) {
            totalFilesSpan.textContent = data.summary.total_files;
            totalTypesSpan.textContent = data.summary.distinct_types;
            lastUploadSpan.textContent = data.summary.last_upload || '—';
        }
    } catch (err) {
        console.error(err);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('barChart')) {
        loadCharts();
    }
});
