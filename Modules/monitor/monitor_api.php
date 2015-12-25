<?php global $path, $session, $user; ?>
<br>
<h2><?php echo _('Monitor API'); ?></h2>
<h3><?php echo _('Apikey authentication'); ?></h3>
<p><?php echo _('If you want to call any of the following actions when your not logged in, add an apikey to the URL of your request: &apikey=APIKEY.'); ?></p>
<p><b><?php echo _('Read only:'); ?></b><br>
<input type="text" style="width:255px" readonly="readonly" value="<?php echo $user->get_apikey_read($session['userid']); ?>" />
</p>
<p><b><?php echo _('Read & Write:'); ?></b><br>
<input type="text" style="width:255px" readonly="readonly" value="<?php echo $user->get_apikey_write($session['userid']); ?>" />
</p>

<h3><?php echo _('Posting data'); ?></h3>
<p>O módulo monitor aceita um vetor de valores de byte (0-256) separados por vírgula. Este vetor de bytes é então decodificado pelo módulo monitor de acordo com o decodificador selecionado.</p>
<table class="table">
    <tr><td></td><td><a href="<?php echo $path; ?>monitor/set.json?monitorid=10&data=20,20,20,20"><?php echo $path; ?>monitor/set.json?monitorid=10&data=20,20,20,20</a></td></tr>
    <tr><td>With write apikey: </td><td><a href="<?php echo $path; ?>monitor/set.json?monitorid=10&data=20,20,20,20&apikey=<?php echo $user->get_apikey_write($session['userid']); ?>"><?php echo $path; ?>monitor/set.json?monitorid=10&data=20,20,20,20&<b>apikey=<?php echo $user->get_apikey_write($session['userid']); ?></b></a></td></tr>
	<tr><td>With write apikey: </td><td><a href="<?php echo $path; ?>monitor/set.json?monitorid=10&time=<?php echo time(); ?>&data=20,20,20,20&apikey=<?php echo $user->get_apikey_write($session['userid']); ?>"><?php echo $path; ?>monitor/set.json?monitorid=10&time=<?php echo time(); ?>&data=20,20,20,20&<b>apikey=<?php echo $user->get_apikey_write($session['userid']); ?></b></a></td></tr>
    
</table>
