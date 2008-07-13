<?php
/* 
 * Copyright (c) Andreas Ferber <af+symfony@chaos-agency.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Actions for sfAjaxDebug module
 *
 * @package    sfAjaxDebugPlugin
 * @author     Andreas Ferber <af+symfony@chaos-agency.de>
 * @version    SVN: $Id$
 */
class sfAjaxDebugActions extends sfActions {

    public function executeJs($request) {
        sfConfig::set('sf_web_debug', false);
        $request->setRequestFormat('js');
    }

    public function executeCss($request) {
        sfConfig::set('sf_web_debug', false);
        $request->setRequestFormat('css');
    }

    public function executeGet() {
        sfConfig::set('sf_web_debug', false);

        $token = $this->getRequestParameter('token');

        $cache = sfAjaxDebugLogger::getCache();
        $content = $cache->get($token, null);
        $cache->remove($token);

        if (is_null($content)) {
            $this->getResponse()->setContent('No web debug toolbar data found');
            $this->getResponse()->setContentType('text/plain');
            $this->getResponse()->setStatusCode(404);
        }
        else {
            $this->getResponse()->setContent($content);
        }

        return sfView::NONE;
    }

}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
