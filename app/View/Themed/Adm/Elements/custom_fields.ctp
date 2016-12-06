<?php
if ( !empty( $custom_fields ) )
{
	foreach ( $custom_fields as $field )
	{
		$val = ( isset( $values[$field['ProfileField']['id']]['value'] ) ) ? $values[$field['ProfileField']['id']]['value'] : '';

		if (!in_array($field['ProfileField']['type'],array('heading','textfield','list','multilist')))
		{
			$options = array();
			if ($field['ProfileField']['plugin'])
			{
				$options = array('plugin' => $field['ProfileField']['plugin']);
			}

			echo $this->element('profile_field/' . $field['ProfileField']['type'], array('field' => $field,'show_require'=>isset($show_require) ? $show_require : null,'val'=>$val),$options);
			continue;
		}

		if ( $field['ProfileField']['type'] == 'heading' && !empty( $show_heading ) )
        {
            echo '<hr><h4>' . $field['ProfileField']['name'] . '</h4>';
            continue;
        }
		
		echo '<div class="form-group"> <label class="col-md-3 control-label">' .$field['ProfileField']['name'];
        if ( !empty( $show_require ) && $field['ProfileField']['required'] )
            echo ' *';

		echo '</label><div class="col-md-9">';
		
		switch ( $field['ProfileField']['type'] )
		{                
			case 'textfield':
				echo $this->Form->text( 'field_' .$field['ProfileField']['id'], array( 'class' => 'form-control','value' => $val ) );
				break;
			
			case 'textarea':
				echo $this->Form->textarea( 'field_' .$field['ProfileField']['id'], array( 'class' => 'form-control','value' => $val ) );
				break;
				
			case 'list':
				$options = array();
				$field_values = explode( "\n", $field['ProfileField']['values'] );
				
				foreach ( $field_values as $value )
					$options[trim($value)] = trim($value);

				/*if ( !empty( $multiple) )
					echo $this->Form->select( 'field_' .$field['ProfileField']['id'], $options, array( 'value' => $val, 'multiple' => 'multiple', 'class' => 'multi') );
				else*/
					echo $this->Form->select( 'field_' .$field['ProfileField']['id'], $options, array( 'class' => 'form-control','value' => $val ) );
				break;
				
			case 'multilist':
				$options = array();
				$field_values = explode( "\n", $field['ProfileField']['values'] );
				
				foreach ( $field_values as $value )
					$options[trim($value)] = trim($value);
					
				echo $this->Form->select( 'field_' .$field['ProfileField']['id'], $options, array('value' => explode(', ', $val), 'multiple' => 'multiple', 'class' => 'multi form-control' ) );
				break;
		}

        if ( !empty( $field['ProfileField']['description'] ) )
            echo ' <span class="help-block">' . $field['ProfileField']['description'] . '</span>';
		
		echo '</div></div>';
	}
}