<a class="entities__action entities__action--add<?php if($action->isDisabled()) echo ' is-disabled'; ?>" data-modal href="<?= $action->url('add'); ?>">
  <?= $action->icon('left'); ?><?= $action->label(); ?>
</a>