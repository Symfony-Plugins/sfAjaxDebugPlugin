/* 
 * Copyright (c) Andreas Ferber <af+symfony@chaos-agency.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    sfAjaxDebugPlugin
 * @author     Andreas Ferber <af+symfony@chaos-agency.de>
 * @version    SVN: $Id$
 */

Ajax.Responders.register({
    onComplete: function(request, json) {
        var token = request.getHeader('X-sfAjaxDebug-Token') || '';
        // console.log('sfAjaxDebug: got token '+token);
        if (token) {
            var url = '<?php echo url_for('sfAjaxDebug/get?token=_placeholder_') ?>';
            url = url.replace('_placeholder_', token);
            new Ajax.Request(url, {
                method: 'get',
                asynchronous: true,
                evalScripts: false,
                onSuccess: function(request, json) {
                    var sfWebDebug = document.getElementById('sfWebDebug');
                    if (sfWebDebug) {
                        sfWebDebug.replace(request.responseText);
                    }
                }   
            });
        }
    }   
});

// vim:set ft=javascript:
