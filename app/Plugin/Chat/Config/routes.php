<?php
// Core conversations integration

if(Configure::read('Chat.chat_turn_on_notification')==1){
    Router::connect('/conversations/show', array(
        'plugin' => 'Chat',
        'controller' => 'chat_integration',
        'action'=>'show'
    ));
    Router::connect('/conversations/ajax_browse/*', array(
        'plugin' => 'Chat',
        'controller' => 'chat_integration',
        'action'=>'ajax_browse'
    ));
    Router::connect('/conversations/mark_all_read', array(
        'plugin' => 'Chat',
        'controller' => 'chat_integration',
        'action'=>'mark_all_read'
    ));
    Router::connect('/conversations/mark_read', array(
        'plugin' => 'Chat',
        'controller' => 'chat_integration',
        'action'=>'mark_read'
    ));
    // Api integration

    Router::connect('/api/notification/me', array(
            'plugin' => 'Chat',
            'controller' => 'chat_api_integration',
            '[method]' => 'GET',
            'action' => 'api_me',
            'ext' => 'json',
        )
    );

    Router::connect('/api/message/me/show', array(
            'plugin' => 'Chat',
            'controller' => 'chat_api_integration',
            '[method]' => 'GET',
            'action' => 'show',
            'ext' => 'json',
        )
    );
}



Router::connect('/chat/:action/*', array(
    'plugin' => 'Chat',
    'controller' => 'chats'
));

/*
Router::connect('/chat/*', array(
    'plugin' => 'Chat',
    'controller' => 'chats',
    'action' => 'index'
));

*/
Router::connect('/chats/settings/blocking', array(
    'plugin' => 'Chat',
    'controller' => 'chat_settings',
    'action' => 'blocking'
));
Router::connect('/chats/unblock/:id', array(
        'plugin' => 'Chat',
        'controller' => 'chat_settings',
        '[method]' => 'GET',
        'action' => 'unblock',
        'ext' => 'json',
    ),
    array(
        'pass' => array('id'),
        'id' => '[0-9]+'
    )
);
Router::connect('/chats/send-picture', array(
        'plugin' => 'Chat',
        'controller' => 'Chats',
        '[method]' => 'POST',
        'action' => 'sendPicture',
        'ext' => 'json',
    )
);
Router::connect('/chats/send-files', array(
        'plugin' => 'Chat',
        'controller' => 'Chats',
        '[method]' => 'POST',
        'action' => 'sendFiles',
        'ext' => 'json',
    )
);
Router::connect('/chats/save-user-settings', array(
        'plugin' => 'Chat',
        'controller' => 'Chats',
        '[method]' => 'POST',
        'action' => 'saveUserSettings',
        'ext' => 'json',
    )
);
Router::connect('/chats/embed', array(
        'plugin' => 'Chat',
        'controller' => 'Chats',
        //'[method]' => 'POST',
        'action' => 'embed',
        'ext' => 'json',
    )
);