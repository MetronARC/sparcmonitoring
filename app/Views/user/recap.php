<?= $this->extend('template/index') ?>
<?= $this->section('page-content') ?>

<h1>Machine Recap</h1>
<div class="date"></div>
<div class="insights">
    <!-- ACTIVE AREA -->
    <div class="sales">
        <span class="material-symbols-outlined">zoom_in_map</span>
        <div class="middle">
            <div class="left">
                <h2>Input Machine</h2>
                <select id="machine-dropdown" class="machine-input">
                    <option value="" selected disabled>Select your Machine</option>
                    <!-- Dynamically add options here -->
                    <?php if (!empty($machines)): ?>
                        <?php foreach ($machines as $machine): ?>
                            <option value="<?= htmlspecialchars($machine['realName']) ?>"><?= htmlspecialchars($machine['realName']) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No machines available</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="progress">
                <!-- Progress bar can remain here -->
            </div>
        </div>
    </div>
    <div class="sales">
        <span class="material-symbols-outlined">zoom_in_map</span>
        <div class="middle">
            <div class="left">
                <h2>Input Date</h2>
                <input type="date" id="date-input" class="date-input">
            </div>
            <div class="progress">
                <a id="fetch-data" href="#">
                    <p>Enter</p>
                </a>
            </div>
        </div>
    </div>
</div>
<div class="recent-orders">
    <div id="chart-container">
        <canvas id="chart"></canvas>
        <!-- Reset Zoom and Move Buttons -->
        <button id="reset-zoom">Reset Zoom</button>
        <button id="move-left">Move Left</button>
        <button id="move-right">Move Right</button>
    </div>
</div>

<script>
    let chartInstance = null;

    document.getElementById('fetch-data').addEventListener('click', async function(event) {
        event.preventDefault();

        const machineDropdown = document.getElementById('machine-dropdown');
        const dateInput = document.getElementById('date-input');

        const machineName = machineDropdown.value;
        const date = dateInput.value;

        if (machineName && date) {
            const response = await fetch('/recap/fetchMachineData', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    machineName: machineName,
                    date: date
                })
            });
            const data = await response.json();

            // Render the chart with the fetched data and user-selected date
            renderChart(data, date);
        } else {
            alert('Please select a machine and date.');
        }
    });

    document.getElementById('reset-zoom').addEventListener('click', function() {
        if (chartInstance) {
            chartInstance.resetZoom();
        }
    });

    document.getElementById('move-left').addEventListener('click', function() {
        if (chartInstance) {
            chartInstance.pan({
                x: 100
            });
        }
    });

    document.getElementById('move-right').addEventListener('click', function() {
        if (chartInstance) {
            chartInstance.pan({
                x: -100
            });
        }
    });

    function renderChart(data, date) {
        const dataPoints = [];
        const backgroundColors = [];
        const borderColors = [];
        const hoverLabels = [];

        for (let i = 0; i < 24 * 60; i++) {
            const time = moment().startOf('day').minutes(i).format('HH:mm');
            let color = '#ebd234';
            let hoverLabel = '';

            data.forEach(interval => {
                if (interval.ArcOn && interval.ArcOff) {
                    const arcOnTime = timeToMinutes(interval.ArcOn);
                    const arcOffTime = timeToMinutes(interval.ArcOff);

                    if (arcOnTime !== null && arcOffTime !== null) {
                        if (i >= arcOnTime && i < arcOffTime) {
                            color = '#008000';
                            if (i === arcOnTime) {
                                hoverLabel = `ArcOn: ${interval.ArcOn}, ArcOff: ${interval.ArcOff}, ArcTotal: ${arcOffTime - arcOnTime} minutes`;
                            }
                        }
                    }
                }
            });

            dataPoints.push({
                x: timeToDateTime(time, date),
                y: 1,
                label: hoverLabel
            });
            backgroundColors.push(color);
            borderColors.push(color);
            hoverLabels.push(hoverLabel);
        }

        const ctx = document.getElementById('chart').getContext('2d');

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                datasets: [{
                    label: 'Machine On/Off',
                    data: dataPoints,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(tooltipItem) {
                                const label = tooltipItem.raw.label;
                                return label ? label : '';
                            }
                        }
                    },
                    zoom: {
                        pan: {
                            enabled: true,
                            mode: 'x',
                            modifierKey: 'ctrl',
                        },
                        zoom: {
                            enabled: true,
                            mode: 'x',
                            drag: {
                                enabled: true,
                                backgroundColor: 'rgba(225,225,225,0.3)',
                            },
                            wheel: {
                                enabled: true,
                            },
                            pinch: {
                                enabled: true,
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'minute',
                            displayFormats: {
                                minute: 'HH:mm'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Time'
                        },
                        ticks: {
                            source: 'data',
                            autoSkip: false,
                            maxRotation: 0,
                            minRotation: 0,
                            major: {
                                enabled: true
                            },
                            callback: function(value, index, values) {
                                const time = moment(value).format('HH:mm');
                                const specificTimes = ['00:01', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00', '23:59'];
                                return specificTimes.includes(time) ? time : '';
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 1,
                        ticks: {
                            stepSize: 1,
                            callback: value => value === 1 ? 'On' : 'Off'
                        },
                        title: {
                            display: true,
                            text: 'Status'
                        }
                    }
                }
            }
        });
    }

    function timeToMinutes(time) {
        if (!time) {
            return null;
        }
        const [hours, minutes] = time.split(':').map(Number);
        return hours * 60 + minutes;
    }

    function timeToDateTime(time, date) {
        return moment(date + ' ' + time, 'YYYY-MM-DD HH:mm').toDate();
    }
</script>


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>
<!-- Chart.js Zoom Plugin -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.0/dist/chartjs-plugin-zoom.min.js"></script>

<!-- Chart.js Date Adapter -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.0"></script>
<!-- xlsx library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
<!-- END OF INSIGHTS -->

<?= $this->endSection() ?>;