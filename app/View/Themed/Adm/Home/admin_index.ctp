<?php
echo $this->Html->css(array('visualize'), null, array('inline' => false));
echo $this->Html->script(
    array(
        'enhance',
        'excanvas',
        'visualize.jQuery',
        'global/flot/jquery.flot.min',
        'global/flot/jquery.flot.resize.min',
        'global/flot/jquery.flot.categories.min',
        'admin/controller/index'
    ),
    array('inline' => false )
);

$this->Html->addCrumb(__('System Admin'));
$this->Html->addCrumb(__('Admin Home'), array('controller' => 'home', 'action' => 'admin_index'));

$this->startIfEmpty('sidebar-menu');
echo $this->element('admin/adminnav', array("cmenu" => "dashboard"));
$this->end();
?>

<?php
    $active = '';
    $regiter = '';
    $i = 1;
    foreach($stats as $day => $stat){
        $sUser = $stat['users'];
        $sActivities= $stat['activities'];
        if($i>1){
            $active.=  ",['$day', $sActivities]";
            $regiter.= ",['$day', $sUser]";
        }else{
            $i=2;
            $active =  "['$day', $sActivities]";
            $regiter=  "['$day', $sUser]";
        }

    }
?>
<?php $this->Html->scriptStart(array('inline' => false)); ?>

mooPhrase.add("users", '<?php echo __('users');?>');
function clearNotifications()
{
    $.get('<?php echo  $this->request->base ?>/admin/admin_notifications/ajax_clear');
    $("#notifications_list").slideUp();
}

var activities = [<?php echo $active?>];
var registration = [<?php echo $regiter?>];
mooIndex.init(activities,registration);
$('#site-stats a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    mooIndex.init(activities,registration);
})
<?php $this->Html->scriptEnd(); ?>
<div class="row">
<div class="col-md-9">
<div class="row">
    <div class="col-md-12">
        <div class="note note-info">
            <h4 class="block">
                <iframe src="https://www.moosocial.com/version.php?version=<?php echo  Configure::read('core.version') ?>"
                        frameborder="0" scrolling="no" height="25" width="100%"></iframe>
            </h4>
        </div>

    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="tiles">
            <div class="tile  bg-gray">
                <div class="tile-title">
                        <?php echo  __('Users');?>
                </div>
                <a href="<?php echo  $this->Html->url(array('controller'=>'users','action'=>'admin_index')); ?>">
                <div class="tile-body">
                    <i class="icon-users"></i>
                </div>
                <div class="tile-object">        
                    <div class="number">
                        <?php echo  $user_count ?>
                    </div>
                </div>
                 </a>
            </div>
            <div class="tile bg-gray">
                 <div class="tile-title">
                        <?php echo  __('Blogs');?>
                </div>
                <a href="<?php echo  $this->Html->url(array('admin' => true,'plugin' => 'blog', 'controller'=>'blog_plugins')) ?>">
                <div class="tile-body">
                    <i class="icon-blog"></i>
                </div>
                <div class="tile-object">
                    <div class="number">
                        <?php echo  $blog_count ?>
                    </div>
                </div>
                </a>
            </div>
            <div class="tile bg-gray">
                <div class="tile-title">
                    <?php echo  __('Photos');?>
                </div>
                <a href="<?php echo  $this->Html->url(array('admin' => true,'plugin' => 'photo', 'controller'=>'photo_plugins')) ?>">
                <div class="tile-body">
                    <i class="icon-camera"></i>
                </div>
                <div class="tile-object">
                    <div class="number">
                        <?php echo  $photo_count ?>
                    </div>
                </div>
                </a>
            </div>
            <div class="tile bg-gray">
                <div class="tile-title">
                    <?php echo  __('Videos');?>
                </div>
                <a href="<?php echo  $this->Html->url(array('admin' => true,'plugin' => 'video', 'controller'=>'video_plugins')) ?>">
                <div class="tile-body">
                    <i class="icon-facetime-video"></i>
                </div>
                <div class="tile-object">
                    <div class="number">
                        <?php echo  $video_count ?>
                    </div>
                </div>
                </a>
            </div>
            <div class="tile bg-gray">
                 <div class="tile-title">
                        <?php echo  __('Topics');?>
                    </div>
                <a href="<?php echo  $this->Html->url(array('admin' => true,'plugin' => 'topic', 'controller'=>'topic_plugins')) ?>">
                <div class="tile-body">
                    <i class="icon-topic"></i>
                </div>
                <div class="tile-object">                  
                    <div class="number">
                        <?php echo  $topic_count ?>
                    </div>
                </div>
                </a>
            </div>
            <div class="tile bg-gray">
                 <div class="tile-title">
                        <?php echo  __('Groups'); ?>
                    </div>
                <a href="<?php echo  $this->Html->url(array('admin' => true,'plugin' => 'group', 'controller'=>'group_plugins')) ?>">
                <div class="tile-body">
                    <i class="icon-group"></i>
                </div>
                <div class="tile-object">                
                    <div class="number">
                        <?php echo  $group_count ?>
                    </div>
                </div>
                </a>
            </div>
            <div class="tile bg-gray">
                 <div class="tile-title">
                        <?php echo  __('Events') ?>
                 </div>
                <a href="<?php echo  $this->Html->url(array('admin' => true,'plugin' => 'event', 'controller'=>'event_plugins')) ?>">
                <div class="tile-body">
                    <i class="icon-calendar"></i>
                </div>
                <div class="tile-object">                  
                    <div class="number">
                        <?php echo  $event_count ?>
                    </div>
                </div>
                </a>
            </div>
            <?php if(count($plugin_statistics)): ?>
                <?php foreach ($plugin_statistics as $statis): ?>
                       <div class="tile bg-gray">
                           <div class="tile-title">
                                    <?php echo $statis['name'] ?>
                                </div>
                            <a href="<?php echo  $statis['href'] ?>">
                            <div class="tile-body">
                                <?php echo $statis['icon'] ?>
                            </div>
                            <div class="tile-object">                             
                                <div class="number">
                                    <?php echo  $statis['item_count'] ?>
                                </div>
                            </div>
                            </a>
                        </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class='tabbable tabbable-custom boxless tabbable-reversed'>
            <ul id="site-stats" class="nav nav-tabs">
                <li class="active">
                    <a href="#tab_registration" data-toggle="tab">
                        <?php echo  __('Registration') ?> </a>
                </li>
                <li class="">
                    <a href="#tab_activities" data-toggle="tab">
                        <?php echo  __('Activities') ?> </a>
                </li>
            </ul>
            <div class="tab-content">

                <div id="tab_registration" class="tab-pane active">
                    <h2><?php echo  __('Site Stats - Registration Over Past 7 Days');?></h2>
                    <div id="site_registration" class="chart" style="width:100%" ></div>
                </div>
                <div id="tab_activities" class="tab-pane " >
                    <h2><?php echo  __('Site Stats - Activities Over Past 7 Days');?></h2>
                    <div id="site_activities" class="chart" style="width:100%;"></div>

                </div>
            </div>







        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light bg-inverse">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-edit"></i>
                    <span class="caption-subject font-red-sunglo bold uppercase"><?php echo  __('Admin Notes')?></span>

                </div>

            </div>
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <form class="form-horizontal" action="<?php echo  $this->request->base ?>/admin/system_settings/quick_save" method="post">

                    <div class="form-body">
                        
                        <?php foreach ($settings as $setting): 
                                    $setting = $setting['Setting']; ?>
                        
                        
                        <div class="form-group">
                            <?php echo $this->Form->hidden('setting_id.', array('value' => $setting['id'], 'id' => false)); ?>
                            <?php echo $this->Form->hidden('type_id.'.$setting['id'], array('value' => $setting['type_id'], 'id' => false)); ?>
                            <label class="col-md-3 control-label">
                                <?php echo __d('setting',$setting['label']);?>
                            </label>
                            <div class="col-md-9">
                                <?php 
                                    switch($setting['type_id'])
                                    {
                                        case 'textarea':
                                            echo $this->Form->textarea('textarea.'.$setting['id'], array(
                                                    'value' => $setting['value_actual'],
                                                    'class' => 'form-control',
                                                    'label' => false
                                                ));
                                            break;

                                    }
                                ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-4">
                                    <input type="submit" class="btn green" value="<?php echo  __('Save Notes');?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- END FORM-->
            </div>
        </div>
    </div>
</div>
</div>
<div class="col-md-3">
    
        <div class="portlet-title row">
            <div class="caption col-md-10">
                <h4 class="block"><?php echo  __('Admin Notifications'); ?></h4>
            </div>
            <div class="actions col-md-2">
                <h4 class="block">
                <a class="btn-lg" style="padding:10px 0px;" href="javascript:void(0)" title="<?php echo  __('Clear all notifications'); ?>"
                   onclick="clearNotifications()"><i class="fa fa-check-square"></i></a>
                </h4>
            </div>

        </div>
        <div class="portlet-body">
                <?php if (empty($admin_notifications)): ?>
                    <div style="margin-bottom:10px"><?php echo  __('No new notifications') ?></div>
                <?php else: ?>
                    <ul class="list2 list-group" id="notifications_list"
                        style="margin: 8px 0 10px 0;max-height: 190px;">

                        <?php foreach ($admin_notifications as $noti): ?>
                            <li style="border-bottom:1px solid #DFDFDF; list-style-type: none" <?php if (!$noti['AdminNotification']['read']) echo 'class="unread"'; ?>>
                                <a href="<?php echo  $this->request->base ?>/admin/admin_notifications/ajax_view/<?php echo  $noti['AdminNotification']['id'] ?>"
                                   <?php if (!empty($noti['AdminNotification']['message'])): ?>data-toggle="modal" data-target="#ajax"
                                   title="<?php echo  __('Notification Detail');?>"<?php endif; ?>>
                                    <b><?php echo  h($noti['User']['name']) ?></b> <?php echo  $noti['AdminNotification']['text'] ?>
                                    <br/>
                                    <span
                                        class="date"><?php echo  $this->Moo->getTime($noti['AdminNotification']['created'], Configure::read('core.date_format'), $utz) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>


        </div>

        <div class="portlet-body">
            <h4 class="block"><?php echo  __('Tools') ?></h4>
            <ul class="list-group">
                <li class="list-group-item">
                    <a href="<?php echo  $this->request->base ?>/admin/tools/clear_cache"><?php echo  __('Clear Global Caches');?></a>
                </li>
                <li class="list-group-item">
                    <?php
      $this->MooPopup->tag(array(
             'href'=>$this->Html->url(array("controller" => "tools",
                                            "action" => "admin_clean_tmp",
                                            "plugin" => false,
                                          
                                        )),
             'title' => __('Clean Temp Upload Folder'),
             'innerHtml'=> __('Clean Temp Upload Folder'),
     ));
 ?>

                </li>
                <li class="list-group-item">
                    <a href="<?php echo  $this->request->base ?>/admin/tools/remove_notifications"><?php echo  __('Remove Old Notifications')?></a>
                </li>
            </ul>
            <h4 class="block"><?php echo  __('Support');?></h4>
            <ul class="list-group">
                <li class="list-group-item">
                    <?php echo  $this->Html->link('mooSocial.com', "http://www.moosocial.com", array('target'=>'_blank')); ?>
                </li>
                <li class="list-group-item">
                    <?php echo  $this->Html->link(__('Clients Area'), "http://clients.moosocial.com", array('target'=>'_blank')); ?>
                </li>
                <li class="list-group-item">
                    <?php echo  $this->Html->link('mooWiki', "https://www.moosocial.com/wiki/doku.php?id=start", array('target'=>'_blank')); ?>
                </li>
                <li class="list-group-item">
                    <?php echo  $this->Html->link('mooCommunity', "http://community.moosocial.com", array('target'=>'_blank')); ?>
                </li>
                <li class="list-group-item">
                    <?php echo  $this->Html->link(__('Themes & Plugins'), "http://community.moosocial.com/topics", array('target'=>'_blank')); ?>
                </li>
            </ul>


        </div>
    <!--</div>-->
</div>
</div>