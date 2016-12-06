<?php
Router::connect('/notes/:action/*', array(
    'plugin' => 'Note',
    'controller' => 'notes'
));

Router::connect('/notes/*', array(
    'plugin' => 'Note',
    'controller' => 'notes',
    'action' => 'index'
));
