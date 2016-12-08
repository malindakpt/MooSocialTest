<?php
Router::connect('/activity_filters/:action/*', array(
    'plugin' => 'ActivityFilter',
    'controller' => 'activity_filters'
));

Router::connect('/activity_filters/*', array(
    'plugin' => 'ActivityFilter',
    'controller' => 'activity_filters',
    'action' => 'index'
));
