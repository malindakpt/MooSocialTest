<?php $upload_video = Configure::read('UploadVideo.uploadvideo_enabled'); ?>

<div class="content_center_home">
    <div class="mo_breadcrumb">
        <h1><?php echo __( 'My Videos')?></h1>
        <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "videos",
                                            "action" => "create",
                                            "plugin" => 'video',
   
                                        )),
             'title' => __( 'Share New Video'),
             'innerHtml'=> __( 'Share New Video'),
          'class' => 'topButton button button-action button-mobi-top'
     ));
 ?>
        <?php if($upload_video): ?>
        
        <?php
            $this->MooPopup->tag(array(
                   'href'=>$this->Html->url(array("controller" => "upload_videos",
                                                  "action" => "ajax_upload",
                                                  "plugin" => 'upload_video',

                                              )),
                   'title' => __( 'Upload Video'),
                   'innerHtml'=> __( 'Upload Video'),
                	'data-backdrop' => 'static',
                'class' => 'button button-action topButton button-mobi-top'
           ));
       ?>
        <?php endif; ?>
        	
    </div>
    <ul class="video-content-list" id="list-content">
            <?php echo $this->element( 'lists/videos_list' ); ?>
    </ul>
</div>