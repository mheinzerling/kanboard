<section id="main">
    <div class="page-header">
        <h2><?= t('Remove a release') ?></h2>
    </div>

    <div class="confirm">
        <p class="alert alert-info">
            <?= t('Do you really want to remove this release: "%s"?', $release['name']) ?>
        </p>

        <div class="form-actions">
            <a href="?controller=release&amp;action=remove&amp;project_id=<?= $project['id'] ?>&amp;release_id=<?= $release['id'].Helper\param_csrf() ?>" class="btn btn-red"><?= t('Yes') ?></a>
            <?= t('or') ?> <a href="?controller=release&amp;project_id=<?= $project['id'] ?>"><?= t('cancel') ?></a>
            <?= t('or') ?> <a href="?controller=release&amp;project_id=<?= $project['id'] ?>"><?= t('cancel') ?></a>
        </div>
    </div>
</section>