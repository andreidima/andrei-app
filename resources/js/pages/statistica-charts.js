import Chart from 'chart.js/auto';
import confetti from 'canvas-confetti';

const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
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

const formatSecondsAsTime = (seconds) => {
    const safeSeconds = Number.isFinite(seconds) ? Math.max(seconds, 0) : 0;
    const totalMinutes = Math.round(safeSeconds / 60);
    const hours = Math.floor(totalMinutes / 60);
    const minutes = Math.abs(totalMinutes % 60);
    return `${hours}:${minutes.toString().padStart(2, '0')}`;
};

const formatDecimalHoursAsTime = (hoursValue) => {
    const safeValue = Number.isFinite(hoursValue) ? hoursValue : 0;
    return formatSecondsAsTime(safeValue * 3600);
};

const animateTimeValue = (element, targetSeconds, duration = 800) => {
    if (!element) {
        return;
    }

    const safeTarget = Math.max(Number.parseInt(targetSeconds, 10) || 0, 0);
    const start = performance.now();

    const step = (timestamp) => {
        const progress = Math.min((timestamp - start) / duration, 1);
        const currentSeconds = Math.round(safeTarget * progress);
        element.textContent = formatSecondsAsTime(currentSeconds);

        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };

    element.textContent = formatSecondsAsTime(0);
    requestAnimationFrame(step);
};

const buildGoalChart = (canvas, summary, primaryColor, successColor) => {
    if (!canvas || !summary) {
        return;
    }

    const context = canvas.getContext('2d');
    if (!context) {
        return;
    }

    const goalSeconds = Math.max(Number(summary.goal_seconds ?? 0), 0);
    const totalSeconds = Math.max(Number(summary.seconds ?? 0), 0);
    const overageSeconds = Math.max(Number(summary.overage_seconds ?? 0), 0);

    const cappedSeconds = goalSeconds > 0 ? Math.min(totalSeconds, goalSeconds) : totalSeconds;
    const remainingSeconds = Math.max(goalSeconds - cappedSeconds, 0);

    const data = [cappedSeconds];
    const colors = [primaryColor];

    if (goalSeconds > 0) {
        data.push(remainingSeconds);
        colors.push(toRgba(primaryColor, 0.15));
    }

    if (overageSeconds > 0) {
        data.push(overageSeconds);
        colors.push(toRgba(successColor, 0.75));
    }

    if (canvas.goalChart instanceof Chart) {
        canvas.goalChart.destroy();
    }

    canvas.goalChart = new Chart(context, {
        type: 'doughnut',
        data: {
            datasets: [
                {
                    data,
                    backgroundColor: colors,
                    borderWidth: 0,
                },
            ],
        },
        options: {
            cutout: '68%',
            rotation: -90,
            circumference: 360,
            maintainAspectRatio: false,
            animation: {
                duration: 900,
                easing: 'easeOutQuart',
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const labels = ['Progres', 'Țintă rămasă', 'Bonus'];
                            const label = labels[context.dataIndex] ?? 'Valoare';
                            return `${label}: ${formatSecondsAsTime(context.raw)}`;
                        },
                    },
                },
            },
        },
    });

    if (summary.achieved && !canvas.dataset.confettiFired) {
        confetti({
            particleCount: 120,
            spread: 65,
            startVelocity: 35,
            origin: { y: 0.7 },
        });
        canvas.dataset.confettiFired = '1';
    }
};

const hydrateGoalCard = (type, summary, colors) => {
    if (!summary) {
        return;
    }

    const card = document.getElementById(`${type}GoalCard`);
    if (!card) {
        return;
    }

    const meter = card.querySelector('.goal-meter');
    const canvas = meter?.querySelector('canvas');
    if (canvas) {
        const baseColor = type === 'monthly' ? colors.success : colors.primary;
        buildGoalChart(canvas, summary, baseColor, colors.success);
    }

    const valueElement = card.querySelector(`#${type}GoalValue`);
    if (valueElement) {
        animateTimeValue(valueElement, summary.seconds ?? 0);
    }

    const goalElement = card.querySelector(`#${type}GoalGoal`);
    if (goalElement && summary.goal_formatted) {
        goalElement.textContent = summary.goal_formatted;
    }

    const rangeElement = card.querySelector(`#${type}GoalRange`);
    if (rangeElement) {
        const fragments = [summary.period_label, summary.range].filter(Boolean);
        rangeElement.textContent = fragments.join(' · ');
    }

    const statusElement = card.querySelector(`#${type}GoalStatus`);
    if (statusElement && summary.status) {
        statusElement.textContent = summary.status;
    }

    const levelElement = card.querySelector(`#${type}GoalLevel`);
    if (levelElement && summary.level) {
        const baseClass = levelElement.dataset.baseClass ?? 'badge rounded-pill';
        const successClass = 'text-bg-success';
        const primaryClass = 'text-bg-primary';
        const targetClass = summary.achieved ? successClass : primaryClass;
        levelElement.textContent = summary.level;
        levelElement.className = `${baseClass} ${targetClass}`;
    }
};

ready(() => {
    const points = Array.isArray(window.chartPoints) ? window.chartPoints : [];
    const goalSummaries = typeof window.goalProgress === 'object' && window.goalProgress !== null
        ? window.goalProgress
        : {};

    const rootStyles = getComputedStyle(document.documentElement);
    const primaryColor = (rootStyles.getPropertyValue('--bs-primary') || '#0d6efd').trim();
    const successColor = (rootStyles.getPropertyValue('--bs-success') || '#198754').trim();
    const gridColor = (rootStyles.getPropertyValue('--bs-border-color') || 'rgba(0,0,0,0.1)').trim();
    const textColor = (rootStyles.getPropertyValue('--bs-body-color') || '#212529').trim();

    const dailyCanvas = document.getElementById('dailyHoursChart');
    const dailyFallback = document.getElementById('dailyHoursChartFallback');

    const dailyDataset = points.map((point) => ({
        x: point.label,
        y: Number.parseFloat(point?.hours ?? 0) || 0,
        seconds: Number.parseInt(point?.hours_seconds ?? 0, 10) || 0,
    }));

    const totalWorkedSeconds = dailyDataset.reduce((total, item) => total + (item.seconds || 0), 0);
    const hasChartData = dailyDataset.length > 0 && totalWorkedSeconds > 0;

    if (dailyCanvas) {
        if (!hasChartData) {
            if (dailyFallback) {
                dailyFallback.style.display = 'block';
            }
            dailyCanvas.style.display = 'none';
        } else {
            if (dailyFallback) {
                dailyFallback.style.display = 'none';
            }
            dailyCanvas.style.display = 'block';

            if (window.dailyHoursChartInstance instanceof Chart) {
                window.dailyHoursChartInstance.destroy();
            }

            const labels = dailyDataset.map((item) => item.x);
            const maxValue = Math.max(...dailyDataset.map((item) => item.y), 0);

            window.dailyHoursChartInstance = new Chart(dailyCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Ore lucrate',
                            data: dailyDataset,
                            backgroundColor: primaryColor,
                            borderRadius: 6,
                            maxBarThickness: 26,
                            parsing: {
                                xAxisKey: 'x',
                                yAxisKey: 'y',
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
                                    return formatDecimalHoursAsTime(value);
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
                                    const seconds = context.raw?.seconds ?? Math.round((context.parsed.y || 0) * 3600);
                                    return `${context.dataset.label}: ${formatSecondsAsTime(seconds)}`;
                                },
                            },
                        },
                    },
                },
            });
        }
    }

    hydrateGoalCard('weekly', goalSummaries.weekly, { primary: primaryColor, success: successColor });
    hydrateGoalCard('monthly', goalSummaries.monthly, { primary: primaryColor, success: successColor });
});
