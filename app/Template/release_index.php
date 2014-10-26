<div class="page-header">
    <h2><?= t('Releases') ?></h2>
</div>
<?php if (! empty($releases)): ?>
<table>
    <tr>
        <th><?= t('Release Name') ?></th>
		<th><?= t('Open') ?></th>
        <th><?= t('Actions') ?></th>
    </tr>
    <?php foreach ($releases as $release): ?>
    <tr>
        <td><?= Helper\escape($release['name']) ?></td>
		<td><?= $release['closed']?'Closed':'Open' ?></td>
        <td>
            <ul>
                <li>
                    <a href="?controller=release&amp;action=edit&amp;project_id=<?= $project['id'] ?>&amp;release_id=<?= $release['id'] ?>"><?= t('Edit') ?></a>
                </li>
                <li>
                    <a href="?controller=release&amp;action=confirm&amp;project_id=<?= $project['id'] ?>&amp;release_id=<?= $release['id'] ?>"><?= t('Remove') ?></a>
                </li>
            </ul>
        </td>
    </tr>
    <?php endforeach ?>
</table>
<?php endif ?>

<h3><?= t('Add a new release') ?></h3>
<form method="post" action="?controller=release&amp;action=save&amp;project_id=<?= $project['id'] ?>" autocomplete="off">

    <?= Helper\form_csrf() ?>
    <?= Helper\form_hidden('project_id', $values) ?>

    <?= Helper\form_label(t('Release Name'), 'name') ?>
    <?= Helper\form_text('name', $values, $errors, array('autofocus required')) ?>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</form>