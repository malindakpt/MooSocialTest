<?php
Router::connect('/topic2s/:action/*', array(
    'plugin' => 'Topic2',
    'controller' => 'topic2s'
));

Router::connect('/topic2s/*', array(
    'plugin' => 'Topic2',
    'controller' => 'topic2s',
    'action' => 'index'
));
