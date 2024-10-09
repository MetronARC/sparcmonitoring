<?= $this->extend('template/index') ?>
<?= $this->section('page-content') ?>

<h1>Dashboard</h1>

<div class="date"></div>

<div class="insights">
    <!-- ACTIVE AREA -->
    <div class="sales">
        <span class="material-symbols-outlined">zoom_in_map</span>
        <div class="middle">
            <div class="left">
                <h3>Active Machine</h3>
                <h1 id="activeMachineCount"><?= esc($activeMachineCount) ?></h1>
            </div>
            <a href="monitoring.php">
                <div class="progress">
                    <svg>
                        <circle cx="42" cy="42" r="36"></circle>
                    </svg>
                    <div class="number">
                        <h3>View</h3>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="income">
        <span class="material-symbols-outlined">engineering</span>
        <div class="middle">
            <div class="left">
                <h3>Machine Up Time Today</h3>
                <h1><?= esc($machineUptime) ?></h1> <!-- Dynamic uptime -->
            </div>
            <div class="progress">
                <svg>
                    <circle cx="42" cy="38" r="36"></circle>
                </svg>
                <div class="number">
                </div>
            </div>
        </div>
    </div>
    <!-- END OF MOST ACTIVE WELDERS -->
</div>
<!-- END OF INSIGHTS -->

<?= $this->endSection() ?>
