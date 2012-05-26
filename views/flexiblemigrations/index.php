<h3>Flexible Migrations</h3>

<? $message = Session::instance()->get('message',false); ?>
<? Session::instance()->delete('message'); ?>
<? if ($message) { ?> 
  <div class="message"><?=$message?></div>
<? } ?>

<div>
	<?= html::anchor( Route::get('migrations_new')->uri() , 'Generate NEW migration') ?>
</div>
<br>

<div>
  <?= html::anchor( Route::get('migrations_migrate')->uri() , 'RUN ALL PENDING MIGRATIONS') ?>
</div>

<div>
  <?= html::anchor( Route::get('migrations_rollback')->uri() , 'ROLLBACK') ?>
</div>

<div><h2>List of migrations</h2></div>

<table>
  <thead>
    <tr>
      <th>Migration</th><th>Status</th>
    </tr>
  </thead>
  <tbody>
    <? foreach ($migrations as $key => $migration) { ?>
    	  <tr>
         <td><?= basename($migration, EXT); ?></td> 
         <td> 
         <?  if ( array_key_exists(  substr(basename($migration, EXT), 0, 14) , $migrations_runned) ) { ?>
               <span class="ok">OK</span>
         <?  } else { ?>
               <span class="pending">PENDING</span>
         <?  }  ?>
         </td>
        </tr>
    <? } ?>
  </tbody>
</table>

<br>

<div>
	<?= html::anchor( Route::get('migrations_new')->uri() , 'Generate NEW migration') ?>
</div>