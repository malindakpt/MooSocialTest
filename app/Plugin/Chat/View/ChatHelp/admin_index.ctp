<?php
echo $this->Html->css(array('jquery-ui', 'footable.core.min'), null, array('inline' => false));
echo $this->Html->script(array('jquery-ui', 'footable'), array('inline' => false));
$this->Html->addCrumb(__('Plugins Manager'), '/admin/plugins');
$this->Html->addCrumb(__d('chat','Chat Help'), array('controller' => 'chat_help', 'action' => 'admin_index'));
$this->startIfEmpty('sidebar-menu');
echo $this->element('admin/adminnav', array('cmenu' => 'Chat'));
$this->end();
?>
<?php echo $this->Moo->renderMenu('Chat', __d('chat','Help')); ?>


<div class="row">
    <div class="col-md-8">
        <div>
            <!-- BEGIN GENERAL PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-social-dribbble font-blue-sharp"></i>
                        <span class="caption-subject font-blue-sharp bold uppercase"><?php echo __d('chat','Chat overview'); ?></span>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo __d('chat','mooChat is a Live Chat solution for mooSocial.It allows members to converse and exchange contents in real-time.mooChat is built using Node.js and ReactJS which provide excellent performance and minimal resources consumption compare to the old technology AJAX chat products.'); ?>
                            <div class="table-scrollable">
                                <table class="table table-bordered table-hover">
                                    <tbody>
                                    <tr>
                                        <td> <?php echo __d('chat','Compatibility'); ?> </td>
                                        <td> <?php echo __d('chat','mooSocial  2.4.x, Mobile apps'); ?> </td>
                                    </tr>
                                    <tr>
                                        <td> <?php echo __d('chat','Requirement'); ?> </td>
                                        <td> <?php echo __d('chat','Node.js ( We recommend v4.4.7 or above )'); ?> </td>
                                    </tr>
                                    <tr>
                                        <td> <?php echo __d('chat','Product Version'); ?> </td>
                                        <td>  <?php echo __d('chat','1.2'); ?> </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <h3><?php echo __d('chat','How to set up Chat server'); ?> </h3>
                            <p>
                                1.<?php echo __d('chat','Make sure Node.JS is installed on your server ( We recommend v4.4.7 or above )'); ?>
                            </p>
                            <p>
                                2.<?php echo __d('chat','Connecting your server via SSH, go to mooSite/app/Plugin/Chat/webroot/js/server directory and  install module dependencies with npm :'); ?>

                            </p>
                            <div class="note note-info">
                                <p> npm install</p>
                            </div>
                            <p>
                                3.<?php echo __d('chat','After installing the dependencies , you can test the chat server by using the node command'); ?>
                            </p>
                            <div class="note note-info">
                                <p> node .</p>
                            </div>
                            <p>
                                4.<?php echo __d('chat','In case it works , you need to kill the  application which is started in step 3  and we will focus on running it as a service  so that they will automatically restart on reboot or failure, and can safely be used in a production environment.To do that , we recommend installing PM2 with npm command'); ?>
                            </p>
                            <div class="note note-info">
                                <p> sudo npm install -g pm2</p>
                            </div>
                            <p>5.<?php echo __d('chat','After that , type next command'); ?></p>
                            <div class="note note-info">
                                <p> pm2 start ChatApp.js</p>
                            </div>
                            <div class="note note-warning">
                                <p> <?php echo __d('chat','Notice that in case you can not install pm2 , you can run chat as background service with the node command %s',"\"node . >stdout.txt 2> stderr.txt &\""); ?> </p>
                            </div>
                            <p>
                                <?php $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://'; $siteUrl = $protocol.$_SERVER['HTTP_HOST'].":3000";?>
                                <?php echo __d('chat','Now your chat server is ready as %s',$siteUrl); ?>
                            </p>
                            <p> <?php echo __d('chat','Please go to: Admin Dashboard >>> Plugins Manager >>> Chat >>> Chat Settings  then enter the chat server URL by using the url %s',$siteUrl); ?> </p>
                            <p>
                                <?php echo __d('chat','For more information , you can see %s','https://www.digitalocean.com/community/tutorials/how-to-set-up-a-node-js-application-for-production-on-ubuntu-16-04'); ?>
                            </p>
                            <h3><?php echo __d('chat','New features will be added'); ?> </h3>
                            <p>
                                <ul>
                                    <li>
                                        <?php echo __d('chat','Video conference'); ?>
                                    </li>
                                </ul>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END GENERAL PORTLET-->
        <div>
            <!-- BEGIN BLOCKQUOTES PORTLET-->
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-social-dribbble font-green"></i>
                        <span class="caption-subject font-green bold uppercase"><?php echo __d('chat','Troubleshooting'); ?></span>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h3><?php echo __d('chat',"How to change chat server's listening port"); ?></h3>
                            <p>
                                <?php echo __d('chat','If you want to run chat server on a port number XXXX , you can configure it in the file mooSite/app/Plugin/Chat/webroot/js/server/ChatApp.js. Find this line'); ?>
                            </p>
                            <div class="note note-info">
                                <p> var port = process.env.PORT || 3000;</p>
                            </div>
                            <p>
                                <?php echo __d('chat','and change it like the line bellow'); ?>
                            </p>
                            <div class="note note-info">
                                <p> var port = process.env.PORT || XXXX;</p>
                            </div>
                            <p>
                                <?php echo __d('chat','Your are done . Test your chat server to make sure that it works right . Notice that you need to restart the chat server by using pm2 command'); ?>
                            </p>
                            <div class="note note-info">
                                <p> pm2 restart all</p>
                            </div>
                            <p>
                                <?php echo __d('chat','or go to mooSite/app/Plugin/Chat/webroot/js/server directory'); ?>
                            </p>
                            <div class="note note-info">
                                <p> pkill node</p>
                                <p> node . >stdout.txt 2> stderr.txt &</p>
                            </div>
                            <h3><?php echo __d('chat',"How to use HTTPS(SSL/TLS) with chat server"); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END BLOCKQUOTES PORTLET-->
        </div>
        <!-- BEGIN WELLS PORTLET-->
        <div>
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-social-dribbble font-red"></i>
                        <span class="caption-subject font-red bold uppercase">Changelogs</span>
                    </div>

                </div>
                <div class="portlet-body">
                    <h4> Version 1.2</h4>
                    <ul>
                        <li>Parse a Link Like Facebook    [MOOPLUGIN-483]</li>
                        <li>Core conversations integration [MOOPLUGIN-484]</li>
                    </ul>
                  <h4> Version 1.1</h4>
                    <ul>
                        <li>Mobi Integration - Web version  [MOOPLUGIN-470]</li>
                        <li>Fix online/offline issue to crash the website</li>
                    </ul>
                  <h4> Version 1.0</h4>
                    <ul>
                        <li>Releasing 1.0</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- END WELLS PORTLET-->
    </div>
    <div class="col-md-4">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-bars"></i><?php echo __d('chat','Topics'); ?> </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title=""> </a>
                </div>
            </div>
            <div class="portlet-body">
                <ul>
                    <li> <?php echo __d('chat','Chat overview'); ?>
                        <ul>
                            <li><?php echo __d('chat','How to set up Chat server'); ?></li>
                            <li><?php echo __d('chat','New features will be added'); ?></li>
                        </ul>
                    </li>
                    <li> <?php echo __d('chat','Troubleshooting'); ?>
                        <ul>
                            <li><?php echo __d('chat',"How to change chat server's listening port"); ?></li>
                            <li><?php echo __d('chat',"How to use HTTPS(SSL/TLS) with chat server"); ?></li>
                        </ul>
                    </li>
                    <li> <?php echo __d('chat','Changelogs'); ?>
                        <ul>
                            <li> <?php echo __d('chat','1.2'); ?> </li>
                            <li> <?php echo __d('chat','1.1'); ?> </li>
                            <li> <?php echo __d('chat','1.0'); ?> </li>
                        </ul>
                    </li>

                </ul>
            </div>
        </div>


    </div>
</div>