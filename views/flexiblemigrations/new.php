<?php $message = Session::instance()->get('message',false); ?>
<?php Session::instance()->delete('message'); ?>
<?php if ($message) { ?> 
  <div class="message"><?php echo $message?></div>
<?php } ?>

<div>Create New Migration</div>

<form method="post" action="/<?php echo Route::get('migrations_create')->uri() ?>">
  <?php echo  Form::input('migration_name') ?>
  <?php echo  Form::submit('submit','Create Migration') ?>
</form>

<br>
<div>Please use only alphanumeric characters and spaces</div>