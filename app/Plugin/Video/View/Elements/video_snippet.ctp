<?php $videoHelper = MooCore::getInstance()->getHelper('Video_Video'); ?>
<?php if (!empty($video['Video']['pc_upload'])): ?>
    <div>
        <video id="detail_video_<?php echo $video['Video']['id'] ?>" class="video-js vjs-default-skin vjs-big-play-centered"
               controls preload="none" width="<?php echo VIDEO_WIDTH ?>" height="<?php echo VIDEO_HEIGHT ?>"
               poster="<?php echo $videoHelper->getImage($video, array('prefix' => '850')) ?>"
               data-setup='{"example_option":true}'>
            <source src="<?php echo FULL_BASE_URL . $this->request->webroot ?>uploads/videos/thumb/<?php echo $video['Video']['id'] ?>/<?php echo $video['Video']['destination']; ?>" type='video/mp4' />
            <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
        </video>
    </div>
<?php else: ?>    
    <?php
    $ssl_mode = Configure::read('core.ssl_mode');
    $http = (!empty($ssl_mode)) ? 'https' : 'http';
    switch ($video['Video']['source']) {
        case VIDEO_TYPE_YOUTUBE:
            echo '<iframe width="' . VIDEO_WIDTH . '" height="' . VIDEO_HEIGHT . '" src="' . $http . '://www.youtube.com/embed/' . $video['Video']['source_id'] . '?wmode=opaque" frameborder="0" allowfullscreen></iframe>';
            break;

        case VIDEO_TYPE_VIMEO:
            echo '<iframe src="' . $http . '://player.vimeo.com/video/' . $video['Video']['source_id'] . '" width="' . VIDEO_WIDTH . '" height="' . VIDEO_HEIGHT . '" frameborder="0"></iframe>';
            break;
    }
    ?>
<?php endif; ?>