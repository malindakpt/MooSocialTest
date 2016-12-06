<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('CakeEventListener', 'Event');

class BillingListener implements CakeEventListener
{

    public function implementedEvents()
    {
        return array(
            'AppController.doBeforeFilter' => 'doBeforeFilter',

        );
    }

    public function doBeforeFilter($event)
    {
        $e = $event->subject();
        $e->loadModel('Billing.Currency');
        $currency = $e->Currency->findByIsDefault(1);
        Configure::write('Config.currency', $currency);
    }


}