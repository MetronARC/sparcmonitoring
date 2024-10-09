<?= $this->extend('template/index') ?>
<?= $this->section('page-content') ?>

<h1>Dashboard</h1>

<div class="date"></div>

<div class="insights">
    <?php foreach ($machines as $machine): ?>
        <div class="sales">
            <span class="material-symbols-outlined">zoom_in_map</span>
            <div class="middle">
                <div class="left">
                    <h3>Latest Arc On: <?= $machine['lastBeat']; ?></h3>
                    <h1><?= $machine['MachineID']; ?></h1>
                </div>
                <div class="progress">
                    <a><img src="<?= base_url(); ?>img/<?= $machine['State'] === 'OFF' ? 'machineInactive.png' : ($machine['State'] === 'IDLE' ? 'machineIDLE.png' : 'machineActive.png'); ?>" alt="Machine State"></a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function updateMachineState() {
        $.ajax({
            url: "<?= base_url('monitoring/getMachineState/' . $areaName); ?>",
            method: "GET",
            dataType: "json",
            success: function(data) {
                $('.sales').each(function(index, element) {
                    var machine = data[index];
                    $(element).find('h3').text("Latest Arc On: " + machine.lastBeat);
                    $(element).find('h1').text(machine.MachineID);
                    var stateImage = machine.State === 'OFF' ? 'machineInactive.png' : (machine.State === 'IDLE' ? 'machineIDLE.png' : 'machineActive.png');
                    $(element).find('img').attr('src', '<?= base_url(); ?>img/' + stateImage);
                });
            }
        });
    }

    setInterval(updateMachineState, 1000);
</script>


<!-- END OF INSIGHTS -->

<?= $this->endSection() ?>