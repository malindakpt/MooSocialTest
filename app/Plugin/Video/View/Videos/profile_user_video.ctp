<?php $upload_video = Configure::read('UploadVideo.uploadvideo_enabled'); ?>
<?php if ($upload_video): ?>
    <?php
    echo $this->Html->script(array('jquery.fileuploader'), array('inline' => false));
    echo $this->Html->css(array('fineuploader'));
    ?>
<?php endif; ?>

<div class="bar-content">
    <div class="content_center">
        <?php if ($user_id == $uid): ?>
            <div class="bar-content profile-sub-menu">
                <?php
                $this->MooPopup->tag(array(
                    'href' => $this->Html->url(array("controller" => "videos",
                        "action" => "create",
                        "plugin" => 'video',
                    )),
                    'title' => __('Share New Video'),
                    'innerHtml' => __('Share New Video'),
                    'class' => 'topButton button button-action'
                ));
                ?>

                <?php if ($upload_video): ?>
                    <!-- check enabled upload video from pc -->
                    <?php
                    $this->MooPopup->tag(array(
                        'href' => $this->Html->url(array("controller" => "upload_videos",
                            "action" => "ajax_upload",
                            "plugin" => 'upload_video',
                        )),
                        'title' => __('Upload Video'),
                        'innerHtml' => __('Upload Video'),
                        'data-backdrop' => 'static',
                        'data-keyboard' => 'false',
                        'class' => 'button button-action topButton button-mobi-top'
                    ));
                    ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <ul class="albums" id="list-content">
        <?php echo $this->element('lists/videos_list'); ?>
        </ul>
    </div>
</div>