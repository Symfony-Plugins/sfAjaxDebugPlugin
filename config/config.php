<?php
/* 
 * Copyright (c) 2008 Andreas Ferber <af+symfony@chaos-agency.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Configuration for sfAjaxDebugPlugin
 *
 * @package    sfAjaxDebugPlugin
 * @author     Andreas Ferber <af+symfony@chaos-agency.de>
 * @version    SVN: $Id$
 */

if (sfConfig::get('sf_web_debug') && sfConfig::get('app_sf_ajax_debug_enable', true)) {
    if (!in_array('sfAjaxDebug', sfConfig::get('sf_enabled_modules', array()))) {
        $this->dispatcher->notify(new sfEvent($this, 'application.log', array('sfAjaxDebug module not enabled', 'priority' => sfLogger::DEBUG)));
        sfConfig::set('app_sf_ajax_debug_enable', false);
    }
    elseif (sfConfig::get('app_sf_ajax_debug_routes_register', true)) {
        $this->dispatcher->connect('routing.load_configuration', array('sfAjaxDebugRouting', 'listenToRoutingLoadConfigurationEvent'));
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
