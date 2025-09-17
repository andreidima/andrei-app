import Chart from 'chart.js/auto';

const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
};

const toNumberArray = (source = [], key) => {
    return source.map((point) => {
        const value = Number.parseFloat(point?.[key] ?? 0);
        return Number.isFinite(value) ? value : 0;
    });
};

const toRgba = (color, alpha) => {
    if (!color) {
        return `rgba(13, 110, 253, ${alpha})`;
    }

    const trimmed = color.trim();
    const shortHexMatch = /^#([0-9a-f]{3})$/i.exec(trimmed);
    if (shortHexMatch) {
        const [r, g, b] = shortHexMatch[1].split('').map((ch) => parseInt(ch + ch, 16));
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    const longHexMatch = /^#([0-9a-f]{6})$/i.exec(trimmed);
    if (longHexMatch) {
        const r = parseInt(longHexMatch[1].substring(0, 2), 16);
        const g = parseInt(longHexMatch[1].substring(2, 4), 16);
        const b = parseInt(longHexMatch[1].substring(4, 6), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    const rgbMatch = /^rgba?\((\d+)\s*,\s*(\d+)\s*,\s*(\d+)(?:\s*,\s*([0-9.]+))?\)$/i.exec(trimmed);
    if (rgbMatch) {
        return `rgba(${rgbMatch[1]}, ${rgbMatch[2]}, ${rgbMatch[3]}, ${alpha})`;
    }

    return `rgba(25, 135, 84, ${alpha})`;
};

ready(() => {
    const points = Array.isArray(window.chartPoints) ? window.chartPoints : [];

    if (points.length === 0) {
        const dailyFallback = document.getElementById('dailyHoursChartFallback');
        const cumulativeFallback = document.getElementById('cumulativeHoursChartFallback');

        if (dailyFallback) {
            dailyFallback.style.display = 'block';
        }
        if (cumulativeFallback) {
            cumulativeFallback.style.display = 'block';
        }
        return;
    }

    const labels = points.map((point) => point.label);
    const dailyHours = toNumberArray(points, 'hours');
    const cumulativeHours = toNumberArray(points, 'cumulative_hours');

    const totalWorkedHours = dailyHours.reduce((total, value) => total + value, 0);

    const rootStyles = getComputedStyle(document.documentElement);
    const primaryColor = (rootStyles.getPropertyValue('--bs-primary') || '#0d6efd').trim();
    const successColor = (rootStyles.getPropertyValue('--bs-success') || '#198754').trim();
    const gridColor = (rootStyles.getPropertyValue('--bs-border-color') || 'rgba(0,0,0,0.1)').trim();
    const textColor = (rootStyles.getPropertyValue('--bs-body-color') || '#212529').trim();

    const dailyCanvas = document.getElementById('dailyHoursChart');
    const cumulativeCanvas = document.getElementById('cumulativeHoursChart');

    const commonOptions = {
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
                        return value.toLocaleString('ro-RO', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 1,
                        });
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
                        const value = context.parsed.y || 0;
                        return `${context.dataset.label}: ${value.toLocaleString('ro-RO', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2,
                        })} h`;
                    },
                },
            },
        },
    };

    if (dailyCanvas) {
        const dailyFallback = document.getElementById('dailyHoursChartFallback');
        if (totalWorkedHours === 0 && dailyFallback) {
            dailyFallback.style.display = 'block';
            dailyCanvas.style.display = 'none';
        } else {
            const datasetColor = primaryColor || '#0d6efd';
            if (window.dailyHoursChartInstance instanceof Chart) {
                window.dailyHoursChartInstance.destroy();
            }
            window.dailyHoursChartInstance = new Chart(dailyCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Ore lucrate',
                            data: dailyHours,
                            backgroundColor: datasetColor,
                            borderRadius: 6,
                            maxBarThickness: 26,
                        },
                    ],
                },
                options: {
                    ...commonOptions,
                    scales: {
                        ...commonOptions.scales,
                        y: {
                            ...commonOptions.scales.y,
                            suggestedMax: Math.max(...dailyHours, 2),
                        },
                    },
                },
            });
        }
    }

    if (cumulativeCanvas) {
        const cumulativeFallback = document.getElementById('cumulativeHoursChartFallback');
        if (cumulativeHours.every((value) => value === 0) && cumulativeFallback) {
            cumulativeFallback.style.display = 'block';
            cumulativeCanvas.style.display = 'none';
        } else {
            const context = cumulativeCanvas.getContext('2d');
            const gradient = context.createLinearGradient(0, 0, 0, cumulativeCanvas.height);
            gradient.addColorStop(0, toRgba(successColor, 0.35));
            gradient.addColorStop(1, toRgba(successColor, 0));

            if (window.cumulativeHoursChartInstance instanceof Chart) {
                window.cumulativeHoursChartInstance.destroy();
            }

            window.cumulativeHoursChartInstance = new Chart(cumulativeCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Ore cumulative',
                            data: cumulativeHours,
                            borderColor: successColor,
                            backgroundColor: gradient,
                            fill: 'start',
                            tension: 0.3,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: successColor,
                        },
                    ],
                },
                options: {
                    ...commonOptions,
                    scales: {
                        ...commonOptions.scales,
                        y: {
                            ...commonOptions.scales.y,
                            suggestedMin: 0,
                        },
                    },
                },
            });
        }
    }
});
