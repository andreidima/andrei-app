import Chart from 'chart.js/auto';

const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
};

const formatSecondsAsTime = (seconds) => {
    const safeSeconds = Number.isFinite(seconds) ? Math.max(seconds, 0) : 0;
    const hours = Math.floor(safeSeconds / 3600);
    const minutes = Math.floor((safeSeconds % 3600) / 60);

    return `${hours}:${minutes.toString().padStart(2, '0')}`;
};

const extractSeconds = (point) => {
    if (!point || typeof point !== 'object') {
        return 0;
    }

    if (Number.isFinite(point.seconds)) {
        return Math.max(point.seconds, 0);
    }

    const fallback = Number.parseInt(point.hours_seconds ?? 0, 10);
    return Number.isFinite(fallback) ? Math.max(fallback, 0) : 0;
};

ready(() => {
    const points = Array.isArray(window.chartPoints) ? window.chartPoints : [];

    const rootStyles = getComputedStyle(document.documentElement);
    const primaryColor = (rootStyles.getPropertyValue('--bs-primary') || '#0d6efd').trim();
    const gridColor = (rootStyles.getPropertyValue('--bs-border-color') || 'rgba(0,0,0,0.1)').trim();
    const textColor = (rootStyles.getPropertyValue('--bs-body-color') || '#212529').trim();

    const dailyCanvas = document.getElementById('dailyHoursChart');
    const dailyFallback = document.getElementById('dailyHoursChartFallback');

    if (!dailyCanvas) {
        return;
    }

    const dailyDataset = points
        .map((point) => {
            const seconds = extractSeconds(point);

            return {
                x: point.label,
                hours: seconds / 3600,
                seconds,
                formatted: point.hours_formatted ?? formatSecondsAsTime(seconds),
            };
        })
        .filter((item) => item.seconds > 0);

    if (dailyDataset.length === 0) {
        if (dailyFallback) {
            dailyFallback.style.display = 'block';
        }
        dailyCanvas.style.display = 'none';
        if (window.dailyHoursChartInstance instanceof Chart) {
            window.dailyHoursChartInstance.destroy();
            window.dailyHoursChartInstance = undefined;
        }
        return;
    }

    if (dailyFallback) {
        dailyFallback.style.display = 'none';
    }
    dailyCanvas.style.display = 'block';

    if (window.dailyHoursChartInstance instanceof Chart) {
        window.dailyHoursChartInstance.destroy();
    }

    const labels = dailyDataset.map((item) => item.x);
    const maxValue = Math.max(...dailyDataset.map((item) => item.hours), 0);

    window.dailyHoursChartInstance = new Chart(dailyCanvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Ore lucrate',
                    data: dailyDataset,
                    backgroundColor: primaryColor,
                    borderRadius: 8,
                    maxBarThickness: 40,
                    parsing: {
                        xAxisKey: 'x',
                        yAxisKey: 'hours',
                    },
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 800,
                easing: 'easeOutQuart',
            },
            scales: {
                x: {
                    ticks: {
                        color: textColor,
                        autoSkip: true,
                        maxRotation: 0,
                        minRotation: 0,
                    },
                    grid: {
                        display: false,
                    },
                },
                y: {
                    ticks: {
                        color: textColor,
                        callback(value) {
                            return formatSecondsAsTime(Math.round(value * 3600));
                        },
                    },
                    grid: {
                        color: gridColor,
                        drawBorder: false,
                    },
                    title: {
                        display: true,
                        text: 'Ore',
                        color: textColor,
                        font: {
                            weight: '600',
                        },
                    },
                    suggestedMax: Math.max(maxValue, 2),
                },
            },
            plugins: {
                legend: {
                    labels: {
                        color: textColor,
                    },
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label(context) {
                            const formatted =
                                context.raw?.formatted ?? formatSecondsAsTime(Math.round(context.parsed.y * 3600));
                            return `${context.dataset.label}: ${formatted}`;
                        },
                    },
                },
            },
        },
    });
});
