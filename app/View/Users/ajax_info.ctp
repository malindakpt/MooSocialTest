<?php if($this->request->is('ajax')) $this->setCurrentStyle(4) ?>
<div class="bar-content full_content p_m_10">
    <div class="content_center">
        <div class="post_body">
            <h2 class="header_h2" style="margin-top: 0px;"><?php echo __('Basic Information')?></h2>
            <ul class="list6 info info2">
                <?php if ( !empty( $user['User']['username'] ) ): ?>
                <li><label><?php echo __('Profile URL')?>:</label> <?php echo $this->Text->autoLink(FULL_BASE_URL . $this->request->base . '/-' . $user['User']['username'])?></li>
                <?php endif; ?>	
                <li><label><?php echo __('Gender')?>:</label> <?php $this->Moo->getGenderTxt($user['User']['gender']); ?></li>
                <?php if ( !empty($user['User']['birthday']) && $user['User']['birthday'] != '0000-00-00' ): ?>
                <li><label><?php echo __('Birthday')?>:</label> <?php echo $this->Time->event_format($user['User']['birthday'], '%B %d')?></li>
                <?php endif; ?>
                <li><label><?php echo __('Registered Date')?></label><?php echo $this->Moo->getTime($user['User']['created'], Configure::read('core.date_format'), $utz)?></li>
                <li><label><?php echo __('Last Online')?></label><?php echo $this->Moo->getTime($user['User']['last_login'], Configure::read('core.date_format'), $utz)?></li>
                <?php if ( !empty( $user['User']['about'] ) ): ?>
                <li><label><?php echo __('About')?>:</label><div><?php echo $this->Moo->formatText( $user['User']['about'] , false, true, array('no_replace_ssl' => 1))?></div></li>
                <?php endif; ?>	
                <?php
                foreach ($fields as $field):
                    if (!in_array($field['ProfileField']['type'],array('heading','textfield','list','multilist')))
                    {
                        $options = array();
                        if ($field['ProfileField']['plugin'])
                        {
                            $options = array('plugin' => $field['ProfileField']['plugin']);
                        }

                        echo $this->element('profile_field/' . $field['ProfileField']['type'].'_info', array('field' => $field,'user'=>$user),$options);
                        continue;
                    }
                    if ( $field['ProfileField']['type'] == 'heading' ):
                ?>
                <li class="fields_heading"><h2><?php echo $field['ProfileField']['name']?></h2></li>
                <?php
                    elseif ( !empty( $field['ProfileFieldValue']['value'] ) ) :
                ?>
                <li><label><?php echo $field['ProfileField']['name']?>:</label>
                    <div><?php echo $this->element( 'misc/custom_field_value', array( 'field' => $field ) ); ?></div>
                </li>
                <?php
                    endif;
                endforeach;
                ?>
            </ul>
        </div>
    </div>
</div>
    <div class="bar-content full_content p_m_10 ">
        <div class="content_center">
            <h2 class="header_h2"><?php echo __('Recent Likes')?></h2>
            <?php 
            if ( !empty( $items ) )
            {
                foreach ($items as $type => $items)
                { 
                        echo '<div class="info_recent_like_item"><div class="title-item">';

                        switch ( $type )
                        {
                                case 'blog':
                                        echo __('Blogs');					
                                        break;

                                case 'topic':
                                        echo __('Topics');
                                        break;

                                case 'album':
                                        echo __('Albums');
                                        break;

                                case 'video':
                                        echo __('Videos');
                                        break;
                        }

                        echo '</div><div> ';
                        $model = ucfirst( $type );

                        foreach ( $items as $key => $item )
                        {
                                echo '<a href="' . $this->request->base . '/' . $type . 's/view/' . $item[$model]['id'] . '" class="tip" title="' . __('by %s', h($item['User']['name'])) . ', ' . __n( '%s like', '%s likes', $item[$model]['like_count'], $item[$model]['like_count'] ) . '">' . h($item[$model]['title']) . '</a>';						
                                if ( $key != ( count( $items ) - 1 ) ) 
                                        echo ', ';
                        }

                        echo '</div></div>';	 
                }	
            } 
            else
                echo '<div align="center">' . __('Nothing found') . '</div>';
            ?>
        </div>
    </div>
