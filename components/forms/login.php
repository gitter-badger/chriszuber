<?php
	$session = session::load();
?>
<form name="login" action="." method="post">
	<fieldset form="login">
		<legend>Login</legend>
		<label for="user"data-icon="@"></label>
		<input type="email" name="user" id="user" placeholder="user@example.com" required/><br />
		<label for="password" data-icon="x"></label>
		<input type="password" name="password" id="password" pattern="<?=pattern('password')?>" required/>
		<input type="hidden" name="nonce" value="<?=$session->nonce?>" required readonly/><br />
		<button type="submit" data-icon="X" title="Login"></button>
		<button type="reset" data-icon="V" title="Reset Form"></button>
	</fieldset>
</form>