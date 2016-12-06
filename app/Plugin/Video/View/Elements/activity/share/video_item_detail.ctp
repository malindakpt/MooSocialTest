<?php $videoHelper = MooCore::getInstance()->getHelper('Video_Video'); ?>

<div class="">
    <div class="video-feed-content">
        <?php echo $this->element('Video./video_snippet', array('video' => $object)); ?>
    </div>
    <div class="video-feed-info video_feed_content">
        <div class="video-title">
            <a target="_blank" class="feed_title" href="<?php if (!empty($object['Video']['group_id'])): ?><?php echo $this->request->base ?>/groups/view/<?php echo $object['Video']['group_id'] ?>/video_id:<?php echo $object['Video']['id'] ?><?php else: ?><?php echo $this->request->base ?>/videos/view/<?php echo $object['Video']['id'] ?>/<?php echo seoUrl($object['Video']['title']) ?><?php endif; ?>">
                <?php echo h($object['Video']['title']) ?>
            </a>
        </div>
        <div class="video-description comment_message feed_detail_text">
            <?php echo $this->Text->convert_clickable_links_for_hashtags($this->Text->truncate(strip_tags(str_replace(array('<br>', '&nbsp;'), array(' ', ''), $object['Video']['description'])), 200, array('exact' => false)), Configure::read('Video.video_hashtag_enabled')) ?>

        </div>
    </div>
</div>