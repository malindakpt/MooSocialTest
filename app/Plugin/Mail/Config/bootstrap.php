<?php
	Cache::config('mail', array('engine' => Configure::read('App.mooCacheEngine'), 'groups' => array('mail')));
	MooComponent::register('Mail.MooMail'); 
?>