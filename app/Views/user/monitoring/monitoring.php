<?= $this->extend('template/index') ?>
<?= $this->section('page-content') ?>

<h1>Dashboard</h1>

<div class="date"></div>

<div class="insights">
    <?php foreach ($areas as $area): ?>
        <!-- ACTIVE AREA -->
        <div class="sales">
            <span class="material-symbols-outlined">zoom_in_map</span>
            <div class="middle">
                <div class="left">
                    <h3><?= $area['name'] ?></h3>
                    <h2 id="activeMachineCount">Active Machine: <?= $area['rowCount'] ?></h2>
                </div>
                <a href="<?= base_url('monitoring/activeMachine/' . $area['name']); ?>">
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
        <!-- END OF ACTIVE AREA -->
    <?php endforeach; ?>
</div>
<!-- END OF INSIGHTS -->

<?= $this->endSection() ?>