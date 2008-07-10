<?php
/* 
 * Copyright (c) Andreas Ferber <af+symfony@chaos-agency.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAjaxDebugRouting registers routes for sfAjaxDebugPlugin.
 *
 * @package    sfAjaxDebugPlugin
 * @author     Andreas Ferber <af+symfony@chaos-agency.de>
 * @version    SVN: $Id$
 */
class sfAjaxDebugRouting {

    /**
     * Listens to the routing.load_configuration event.
     *
     * @param sfEvent An sfEvent instance
     */
    static public function listenToRoutingLoadConfigurationEvent(sfEvent $event) {
        $r = $event->getSubject();

        // prepend our routes
        $r->prependRoute('sf_ajax_debug_get', '/sfAjaxDebug/:token', array('module' => 'sfAjaxDebug', 'action' => 'get'));
        $r->prependRoute('sf_ajax_debug_js', '/sfAjaxDebug/main.js', array('module' => 'sfAjaxDebug', 'action' => 'js'));
    }

}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
