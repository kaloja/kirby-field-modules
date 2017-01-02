<?php

class SubpagesField extends InputField {

  protected $translation;
  protected $defaults;
  protected $modules;
  protected $origin;

  public $variant = 'modules';
  public $options = array();
  public $actions = array(
    'edit',
    'duplicate',
    'delete',
    'toggle',
  );
  public $limit = false;
  public $paste = true;
  public $copy = true;
  public $add = true;

  static public $assets = array(
    'js' => array(
      'modules.js',
    ),
    'css' => array(
      'modules.css',
    ),
  );

  // public function __construct() {
  //   $this->registry = Kirby\Elements\Registry::instance();
  //   $this->register();
  // }
  //
  // public function registry() {
  //   return $this->registry;
  // }
  //
  // public function register() {
  //
  //   $registry = $this->registry();
  //
  //   $registry->set('template', 'default', __DIR__ . DS . 'template.php');
  //   $registry->set('action', [
  //     'duplicate' => __DIR__ . DS . 'actions' . DS . 'duplicate.php',
  //     'delete' => __DIR__ . DS . 'actions' . DS . 'delete.php',
  //     'toggle' => __DIR__ . DS . 'actions' . DS . 'toggle.php',
  //     'edit' => __DIR__ . DS . 'actions' . DS . 'edit.php',
  //   ]);
  //
  //   // dump($registry->get('translation'));
  //   // dump($registry->get('template'));
  //   dump($registry->get('action'));
  //
  // }


  public function action($type) {

    $actionFile = $this->root() . DS . '..' . DS . '..' . DS . 'actions' . DS . $type . DS . $type . '.php';
    $actionName = $type . 'Action';

    if(!file_exists($actionFile)) {
      throw new Exception(l('fields.error.missing.controller'));
    }

    require_once($actionFile);

    if(!class_exists($actionName)) {
      throw new Exception(l('fields.error.missing.class'));
    }

    $action = new $actionName();

    return $action;
  }

  public function translation() {

    // Return from cache if possible
    if($this->translation) {
      return $this->translation;
    }

    $root = __DIR__ . DS . 'translations';
    $code = panel()->translation()->code();
    $variant = $this->variant();

    // Base translation
    $this->translation = data::read($root . DS . 'en' . DS . 'modules' . '.json');

    if(is_file($root . DS . $code . DS . $variant . '.json')) {
      $this->translation = a::update($this->translation, data::read($root . DS . $code . DS . $variant . '.json'));
    }

    // Load translation
    l::set($this->translation);

    return $this->translation;

  }

  public function routes($type = null) {

    if(!is_null($type)) {
      return array(
        array(
          'pattern' => '(:all)',
          'method'  => 'POST|GET',
          'action'  => 'delete',
          'filter'  => 'auth',
        ),
      );
    }

    return array(
      array(
        'pattern' => 'action/(:any)/(:all)',
        'method'  => 'POST|GET',
        'action'  => 'forAction',
        'filter'  => 'auth',
      ),
      // array(
      //   'pattern' => 'add',
      //   'method'  => 'POST|GET',
      //   'action'  => 'add',
      //   'filter'  => 'auth',
      // ),
      // array(
      //   'pattern' => '(:all)/delete',
      //   'method'  => 'POST|GET',
      //   'action'  => 'delete',
      //   'filter'  => 'auth',
      // ),
      // array(
      //   'pattern' => '(:all)/(:all)/duplicate',
      //   'method'  => 'POST|GET',
      //   'action'  => 'duplicate',
      //   'filter'  => 'auth',
      // ),
      // array(
      //   'pattern' => '(:all)/(:all)/sort',
      //   'method'  => 'POST|GET',
      //   'action'  => 'sort',
      //   'filter'  => 'auth',
      // ),
      // array(
      //   'pattern' => '(:all)/(:all)/show',
      //   'method'  => 'POST|GET',
      //   'action'  => 'show',
      //   'filter'  => 'auth',
      // ),
      // array(
      //   'pattern' => '(:all)/hide',
      //   'method'  => 'POST|GET',
      //   'action'  => 'hide',
      //   'filter'  => 'auth',
      // ),
      // array(
      //   'pattern' => 'copy',
      //   'method'  => 'POST|GET',
      //   'action'  => 'copy',
      //   'filter'  => 'auth',
      // ),
      // array(
      //   'pattern' => 'paste',
      //   'method'  => 'POST|GET',
      //   'action'  => 'paste',
      //   'filter'  => 'auth',
      // ),
    );
  }

  public function input() {

    $value = func_get_arg(0);
    $input = parent::input();
    $input->attr(array(
      'id'           => $value,
      'name'         => $this->name() . '[]',
      'type'         => 'hidden',
      'value'        => $value,
      'required'     => false,
      'autocomplete' => false,
    ));

    return $input;

  }

  public function preview($page) {

    if(!$preview = $this->options($page)->preview()) {
      return;
    }

    $module   = Kirby\Modules\Modules::instance()->get($page);
    $template = $module->path() . DS . $module->name() . '.preview.php';

    if(!is_file($template)) {
      return;
    }

    $position = $preview === true ? 'top' : $preview;

    $preview = new Brick('div');
    $preview->addClass('module__preview module__preview--' . $position);
    $preview->data('module', $module->name());
    $preview->html(tpl::load($template, array('page' => $this->orign(), 'module' => $page, 'moduleName' => $module->name())));

    return $preview;

  }

  public function actions($data) {
    // return tpl::load(__DIR__ . DS . 'template.php', array('field' => $this));
  }

  public function counter($page) {

    if(!$page->isVisible() || !$this->options($page)->limit()) {
      return null;
    }

    $modules = $this->modules()->filterBy('template', $page->intendedTemplate());
    $index   = $modules->visible()->indexOf($page) + 1;
    $limit   = $this->options($page)->limit();

    $counter = new Brick('span');
    $counter->addClass('module__counter');
    $counter->html('( ' . $index . ' / ' . $limit . ' )');

    return $counter;

  }

  public function defaults() {

    // Return from cache if possible
    if($this->defaults) {
      return $this->defaults;
    }

    // Default values
    $defaults = array(
      'duplicate' => true,
      'preview' => true,
      'delete' => true,
      'toggle' => true,
      'limit' => false,
      'edit' => true,
    );

    if(!$this->options) {
      return $defaults;
    }

    // Filter options for default values
    $options = array_filter($this->options, function($value) {
      return !is_array($value);
    });

    return $this->defaults = a::update($defaults, $options);

  }

  public function options($template) {

    if(is_a($template, 'Page')) {
      $template = $template->intendedTemplate();
    }

    // Get module specific options
    $options = a::get($this->options, $template, array());

    if(!$options) {
      return new Obj($this->defaults());
    }

    return new Obj(a::update($this->defaults(), $options));

  }

  public function content() {
    return tpl::load(__DIR__ . DS . 'template.php', array('field' => $this));
  }

  public function modules() {

		// Return from cache if possible
		if($this->modules) {
      return $this->modules;
    }

    // Filter the modules by valid module
    $modules = $this->origin()->children()->filter(function($page) {
      return Kirby\Modules\Modules::instance()->get($page);
    });

    // Sort modules
    if($modules->count() && $this->value()) {
      $i = 0;

      $order = a::merge(array_flip($this->value()), array_flip($modules->pluck('uid')));
      $order = array_map(function($value) use(&$i) {
        return $i++;
      }, $order);

      $modules = $modules->find(array_flip($order));
    }

    // Always return a collection
    if(is_a($modules, 'Page')) {
      $module  = $modules;
      $modules = new Children($this->origin());

      $modules->data[$module->id()] = $module;
    }

    return $this->modules = $modules;

  }

  public function origin() {

    // Return from cache if possible
    if($this->origin) {
      return $this->origin;
    }

    // Get parent uid
    $parentUid = Kirby\Modules\Settings::parentUid();

    // Determine the modules root
    if(!$origin = $this->page()->find($parentUid)) {
      $origin = $this->page();
    }

    return $this->origin = $origin;

  }

  public function label() {

    // Load translation
    $this->translation();

    $label = new Brick('label');
    $label->addClass('label');
    $label->html($this->i18n($this->label));

    if($this->limit()) {
      $label->append(' <span class="modules__counter">( ' . $this->modules()->visible()->count() . ' / ' . $this->limit() . ' )</span>');
    }

    $add = new Brick('a');
    $add->addClass('modules__action modules__action--add');
    $add->html('<i class="icon icon-left fa fa-plus-circle"></i>' . l('fields.modules.add'));
    $add->data('modal', true);
    $add->attr('href', $this->url('add'));

    if($this->add() === false || $this->origin()->ui()->create() === false) {
      $add->addClasS('is-disabled');
    }

    $label->append($add);

    return $label;

  }

  public function validate() {
    return true;
  }

  public function value() {

    $value = parent::value();
    if(is_array($value)) {
      return $value;
    } else {
      return str::split($value, ',');
    }

  }

  public function result() {

    $result = parent::result();
    return is_array($result) ? implode(', ', $result) : '';

  }

  public function url($action, $params = array()) {

    if($params) {
      $action = $action . '/' . implode('/', $params);
    }

    return purl($this->model(), implode('/', array(
      'field',
      $this->name(),
      $this->type(),
      'action',
      $action
    )));

  }

}
