<?php if ( !$field['ProfileField']['searchable'] ): ?>
	<?php echo h($field['ProfileFieldValue']['value'])?>
<?php else: ?>
	<?php 
	if ( $field['ProfileField']['type'] != 'multilist' ): ?>
		<a href="<?php echo $this->request->base?>/users/index/field_<?php echo $field['ProfileField']['id']?>:<?php echo urlencode(trim($field['ProfileFieldValue']['value']))?>"><?php echo h(trim($field['ProfileFieldValue']['value']))?></a>
	<?php 
	else:
		$values = explode(', ', $field['ProfileFieldValue']['value']);
		foreach ( $values as $key => $val ):
	?>
			<a href="<?php echo $this->request->base?>/users/index/field_<?php echo $field['ProfileField']['id']?>:<?php echo urlencode(trim($val))?>"><?php echo h(trim($val))?></a><?php if ( $key != (count($values) - 1) ) echo ', ';
		endforeach;
	endif; ?>
<?php endif; ?>