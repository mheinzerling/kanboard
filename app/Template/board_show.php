<table id="board" data-project-id="<?= $current_project_id ?>" data-time="<?= time() ?>" data-check-interval="<?= $board_private_refresh_interval ?>" data-csrf-token=<?= \Core\Security::getCSRFToken() ?>>
<?php foreach ($board as $release): ?>
<tr><th style="background-color: white; margin:0;padding: 0;border:0" colspan="<?=count($release['columns'])?>"><h1><?=$release['name']?></h1></th></tr>
<tr>
    <?php $column_with = round(100 / count($release['columns']), 2); ?>
    <?php foreach ($release['columns'] as $column): ?>
    <th width="<?= $column_with ?>%">
        <div class="board-add-icon">
	    <a href="?controller=task&amp;action=create&amp;project_id=<?= $column['project_id'] ?>&amp;column_id=<?= $column['id'] ?>&amp;release_id=<?= $release['id'] ?>" title="<?= t('Add a new task') ?>">+</a>
        </div>
        <?= Helper\escape($column['title']) ?>
        <?php if ($column['task_limit']): ?>
            <span title="<?= t('Task limit') ?>" class="task-limit">
                (
                 <span id="task-number-column-<?= $column['id'] ?>"><?= count($column['tasks']) ?></span>
                 /
                 <?= Helper\escape($column['task_limit']) ?>
                )
            </span>
        <?php else: ?>
            <span title="<?= t('Task count') ?>" class="task-count">
                (<span id="task-number-column-<?= $column['id'] ?>"><?= count($column['tasks']) ?></span>)
            </span>
        <?php endif ?>
    </th>
    <?php endforeach ?>
</tr>
<tr>
    <?php foreach ($release['columns'] as $column): ?>
    <td
        id="column-<?= $column['id'] ?>"
        class="column <?= $column['task_limit'] && count($column['tasks']) > $column['task_limit'] ? 'task-limit-warning' : '' ?>"
        data-column-id="<?= $column['id'] ?>"
		data-release-id="<?= $release['id'] ?>"
        data-task-limit="<?= $column['task_limit'] ?>"
        >
        <?php foreach ($column['tasks'] as $task): ?>
        <div class="task-board draggable-item task-<?= $task['color_id'] ?> <?= $task['date_modification'] > time() - $board_highlight_period ? 'task-board-recent' : '' ?>"
             data-task-id="<?= $task['id'] ?>"
             data-owner-id="<?= $task['owner_id'] ?>"
             data-category-id="<?= $task['category_id'] ?>"
             data-due-date="<?= $task['date_due'] ?>"
             title="<?= t('View this task') ?>">

            <?= Helper\template('board_task', array('task' => $task, 'categories' => $categories)) ?>

        </div>
        <?php endforeach ?>
    </td>
    <?php endforeach ?>
</tr>
<?php endforeach ?>
</table>