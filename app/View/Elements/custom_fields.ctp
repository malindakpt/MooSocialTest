<?php
if(!isset($custom_fields)){
    $custom_fields = Cache::read('custom_fields');
    if(!$custom_fields){
        $custom_fields = $this->requestAction('/users/getCustomField');
        Cache::write('custom_fields',$custom_fields);
    }
}

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

			echo $this->element('profile_field/' . $field['ProfileField']['type'], array('field' => $field,'is_search'=>isset($is_search) ? $is_search : null,'show_require'=>isset($show_require) ? $show_require : null,'val'=>$val),$options);
			continue;
		}

		if ( $field['ProfileField']['type'] == 'heading' && !empty( $show_heading ) )
        {
            echo '<h2 class="page-header">' . $field['ProfileField']['name'] . '</h2>';
            continue;
        }
		
		echo '<div class="form-group"><div class="col-sm-3"><label>' .$field['ProfileField']['name'];
		
		if ( !empty( $field['ProfileField']['description'] ) )
			echo ' <a href="javascript:void(0)" class="tip" title="' . $field['ProfileField']['description'] . '">(?)</a>';
		echo '</label></div><div class="col-sm-9">';
		
		switch ( $field['ProfileField']['type'] )
		{                
			case 'textfield':
				echo $this->Form->text( 'field_' .$field['ProfileField']['id'], array( 'class' => 'form-control','value' => $val , 'name' => 'field_' .$field['ProfileField']['id']) );
				break;
			
			case 'textarea':
				echo $this->Form->textarea( 'field_' .$field['ProfileField']['id'], array( 'class' => 'form-control','value' => $val , 'name' => 'field_' .$field['ProfileField']['id']) );
				break;
				
			case 'list':
				$options = array();
				$field_values = explode( "\n", $field['ProfileField']['values'] );
				
				foreach ( $field_values as $value )
					$options[trim($value)] = trim($value);

				/*if ( !empty( $multiple) )
					echo $this->Form->select( 'field_' .$field['ProfileField']['id'], $options, array( 'value' => $val, 'multiple' => 'multiple', 'class' => 'multi') );
				else*/
					echo $this->Form->select( 'field_' .$field['ProfileField']['id'], $options, array( 'class' => 'form-control','value' => $val, 'name'=>'field_' .$field['ProfileField']['id'] ) );
				break;
				
			case 'multilist':
				$options = array();
				$field_values = explode( "\n", $field['ProfileField']['values'] );
				
				foreach ( $field_values as $value )
					$options[trim($value)] = trim($value);


				echo $this->Form->select( 'field_' .$field['ProfileField']['id'], $options, array( 'class' => 'multi form-control','value' => explode(', ', $val), 'multiple' => 'multiple',  'name' =>'field_' .$field['ProfileField']['id'] ) );
				break;
		}
		
		if ( !empty( $show_require ) && $field['ProfileField']['required'] )
			echo '<span class="profile-tip"> *</span>';
		
		echo '</div><div class="clear"></div></div>';
	}
}