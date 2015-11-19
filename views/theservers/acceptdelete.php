<?php

use PhangoApp\PhaModels\Forms\HiddenForm;
use PhangoApp\PhaI18n\I18n;

function acceptDeleteView($real_url, array $hidden)
{

?>
<form method="get" action="<?php echo $real_url; ?>">
<span class="error"><?php echo I18n::lang('theservers', 'action_delete', 'CLICK ONLY IF YOU ARE SURE OF DELETE THIS ELEMENT'); ?></span>: <input type="submit" value="<?php echo I18n::lang('common', 'delete', 'Delete'); ?>">
<?php

foreach($hidden as $field => $value)
{

    $form=new HiddenForm($field, $value);
    
    echo $form->form();

}

?>
</form>
<?php

}

?>