<div id="<?php echo $field->id(); ?>" class="modules" data-field="modules" data-api="<?php echo purl($field->model(), implode('/', array('field', $field->name(), 'modules'))); ?>">

  <?php $i = 0; $n = 0; foreach($field->modules() as $module): $i++; if($module->isVisible()) $n++; ?>
    <div class="module" data-visible="<?php echo $module->isVisible() ? 'true' : 'false'; ?>" data-uid="<?php echo $module->uid(); ?>">
      <?php echo $field->preview($module); ?>
      <nav class="module__navigation">
        <div class="module__title">
          <?php echo $module->icon(); ?>
          <?php echo $module->title(); ?>
          <?php echo $field->counter($module); ?>
        </div>
        <a class="module__button" href="<?php echo $module->url('edit'); ?>" title="Edit"><?php i('pencil', 'left'); ?> Edit</a>
        <a class="module__button" href="<?php echo $field->url('delete', array('uid' => $module->uid())); ?>" data-modal title="Delete"><i class="icon icon-left fa fa-trash-o"></i> Delete</a>
        <button class="module__button" data-action="<?php echo $field->url('duplicate', array('uid' => $module->uid(), 'to' => $i + 1)); ?>" type="button" tabindex="-1" title="Duplicate"><?php i('copy'); ?></button>
        <?php if($module->isVisible()): ?>
          <button class="module__button" data-action="<?php echo $field->url('hide', array('uid' => $module->uid())); ?>" type="button" tabindex="-1" title="Hide"><?php i('toggle-on'); ?></button>
        <?php else: ?>
          <button class="module__button" data-action="<?php echo $field->url('show', array('uid' => $module->uid(), 'to' => $n + 1)); ?>" type="button" tabindex="-1" title="Show"><?php i('toggle-off'); ?></button>
          <!-- <a class="module__button" data-modal href="<?php echo $field->url('show', array('uid' => $module->uid(), 'to' => $n + 1)); ?>" title="Show"><?php i('toggle-off'); ?>m</a>
          <a class="module__button" data-action="<?php echo $field->url('show', array('uid' => $module->uid(), 'to' => $n + 1)); ?>" href="#<?php echo $field->id(); ?>" title="Show"><?php i('toggle-off'); ?>a</a> -->
        <?php endif; ?>
      </nav>
      <?php echo $field->input($module->uid()); ?>
    </div>
  <?php endforeach; ?>
</div>

<div class="modules__add">
  <a href="#" data-context><?php i('plus-circle', 'left'); ?> Add</a>
</div>
