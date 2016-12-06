<?php

//if (Configure::read('Chat.event_enabled')) {
    App::uses('ChatListener', 'Chat.Lib');
    CakeEventManager::instance()->attach(new ChatListener());

//}