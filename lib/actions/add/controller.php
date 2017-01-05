<?php

class AddActionController extends EntitiesFieldController {

  public function add() {

    // Load translation
    $this->field()->translation();

    $self   = $this;
    $parent = $this->field()->origin();

    if($parent->ui()->create() === false) {
      throw new PermissionsException();
    }

    $form = $this->form('add', array($parent, $this->model()), function($form) use($parent, $self) {

      try {

        $form->validate();

        if(!$form->isValid()) {
          throw new Exception(l('fields.modules.add.error.template'));
        }

        $data = $form->serialize();
        $template = $data['template'];

        $page = $parent->children()->create($self->uid($template), $template, array(
          'title' => i18n($parent->blueprint()->pages()->template()->findBy('name', $template)->title())
        ));

        $self->update($self->field()->modules()->pluck('uid'));
        $self->notify(':)');
        $self->redirect($self->model());
        // $this->redirect($page, 'edit');

      } catch(Exception $e) {
        $form->alert($e->getMessage());
      }

    });

    return $this->modal('add', compact('form'));

  }

}