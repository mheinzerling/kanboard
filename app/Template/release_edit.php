<div class="page-header">
    <h2><?= t('Release modification for the project "%s"', $project['name']) ?></h2>
</div>

<form method="post" action="?controller=release&amp;action=update&amp;project_id=<?= $project['id'] ?>" autocomplete="off">
    <?= Helper\form_csrf() ?>
    <?= Helper\form_hidden('id', $values) ?>
    <?= Helper\form_hidden('project_id', $values) ?>

    <?= Helper\form_label(t('Release Name'), 'name') ?>
    <?= Helper\form_text('name', $values, $errors, array('autofocus required')) ?>
	<?= Helper\form_label(t('Status'), 'closed') ?>
	<?= Helper\form_select('closed', array(1=>'Closed',0=>'Open'), $values, $errors, 'autofocus required') ?>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</form>