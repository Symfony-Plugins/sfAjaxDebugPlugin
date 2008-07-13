<?php
/* 
 * Copyright (c) 2008 Andreas Ferber <af+symfony@chaos-agency.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAjaxDebugLogger stores the web debug toolbar for XMLHttpRequests
 * into a cache for later retrieval.
 *
 * @package    sfAjaxDebugPlugin
 * @author     Andreas Ferber <af+symfony@chaos-agency.de>
 * @version    SVN: $Id$
 */
class sfAjaxDebugLogger extends sfWebDebugLogger {

    protected
        $token = null;

    protected static $cache = null;

    /**
     * Initializes this logger.
     *
     * @param   sfEventDispatcher $dispatcher   A sfEventDispatcher instance
     * @param   array             $options      An array of options.
     *
     * @return  boolean  true, if initialization completes successfully, otherwise false
     */
    public function initialize(sfEventDispatcher $dispatcher, $options = array()) {
        $this->token = uniqid();
        $dispatcher->connect('response.filter_headers', array($this, 'filterResponseHeaders'));

        return parent::initialize($dispatcher, $options);
    }

    /**
     * Listens to the response.filter_headers event.
     *
     * @param   sfEvent $event   The sfEvent instance
     * @param   array   $headers The response headers
     *
     * @return  array   The filtered response headers
     */
    public function filterResponseHeaders(sfEvent $event, $headers) {
        if (!sfConfig::get('sf_web_debug') || !sfConfig::get('app_sf_ajax_debug_enable', true)) {
            return $headers;
        }

        if ($this->context->has('request')
            && $this->context->has('response')
            && $this->context->getRequest()->isXmlHttpRequest()
        ) {
            if ($firstEntry = $this->context->getActionStack()->getFirstEntry()) {
                $headers['X-sfAjaxDebug-Module'] = $firstEntry->getModuleName();
                $headers['X-sfAjaxDebug-Action'] = $firstEntry->getActionName();
            }
            $headers['X-sfAjaxDebug-Token'] = $this->token;
        }

        return $headers;
    }

    /**
     * Listens to the response.filter_content event.
     *
     * @param   sfEvent $event   The sfEvent instance
     * @param   string  $content The response content
     *
     * @return  string  The filtered response content
     */
    public function filterResponseContent(sfEvent $event, $content) {
        $content = parent::filterResponseContent($event, $content);

        if (!sfConfig::get('sf_web_debug') || !sfConfig::get('app_sf_ajax_debug_enable', true)) {
            return $content;
        }

        if ($this->context->has('request')
            && $this->context->has('response')
            && $this->context->getRequest()->isXmlHttpRequest()
        ) {
            self::getCache()->set($this->token, $this->webDebug->getResults());
            return $content;
        }

        // taken from sfWebDebugLogger
        $response = $event->getSubject();
        if (!$this->context->has('request') || !$this->context->has('response') || !$this->context->has('controller') ||
            $this->context->getRequest()->isXmlHttpRequest() ||
            strpos($response->getContentType(), 'html') === false ||
            $response->getStatusCode() == 304 ||
            $this->context->getController()->getRenderMode() != sfView::RENDER_CLIENT ||
            $response->isHeaderOnly()
        ) {
            return $content;
        }

        $scriptUrl = $this->context->getController()->genUrl('sfAjaxDebug/js');
        $assets = sprintf('<script type="text/javascript" src="%s"></script>', $scriptUrl);
        $cssUrl = $this->context->getController()->genUrl('sfAjaxDebug/css');
        $assets .= sprintf('<link rel="stylesheet" type="text/css" media="screen" href="%s" />', $cssUrl);
        $content = str_ireplace('</head>', $assets.'</head>', $content);

        return $content;
    }

    /**
     * Get the cache for stored web debug toolbars
     *
     * @return  sfCache  The sfCache instance used for storing the data
     */
    public static function getCache() {
        if (!self::$cache) {
            self::$cache = new sfFileCache(array(
                'cache_dir' => sfConfig::get('sf_app_cache_dir').'/ajax_debug',
                'lifetime' => 300
            ));
        }
        return self::$cache;
    }

}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
