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

var sfAjaxDebug = {

    keepRequests: <?php echo sfConfig::get('sf_ajax_debug_requests') ?>,

    requestCount: 0,

    current: null,
    toplevel: { id: 'toplevel', loaded: true, title: '[000] initial request', content: '' },
    toolbars: [],
    toolbarsById: {},

    initialize: function() {
        var sfWebDebug = document.getElementById('sfWebDebug');
        if (sfWebDebug) {
            this.toplevel.content = sfWebDebug;
            this.current = this.toplevel;
            this.toolbarsById['toplevel'] = this.toplevel;
            this.insertRequestMenu();
        }
    },

    newRequest: function(module, action, token) {
        if (this.toolbars.length >= this.keepRequests) {
            var oldReq = this.toolbars.shift();
            delete this.toolbarsById[oldReq.id];
        }
        this.requestCount++;
        var fmtReqCount = this.requestCount.toString();
        while (fmtReqCount.length < 3) {
            fmtReqCount = '0'+fmtReqCount;
        }
        var newReq = {
            id: token,
            loaded: false,
            title: '['+fmtReqCount+'] M: '+module+' A: '+action,
            content: ''
        };
        this.toolbars.push(newReq);
        this.toolbarsById[token] = newReq;
        this.insertRequestMenu();

        var url = '<?php echo url_for('sfAjaxDebug/get?token=_placeholder_') ?>';
        url = url.replace('_placeholder_', token);
        var self = this;
        new Ajax.Request(url, {
            method: 'get',
            asynchronous: true,
            evalScripts: false,
            onSuccess: function(request, json) {
                var toolbar = self.getToolbar(token);
                if (toolbar) {
                    toolbar.content = request.responseText;
                    toolbar.loaded = true;
                    self.switchToolbar(token);
                }
            }
        });
    },

    getToolbar: function(id) {
        if (id == 'toplevel') {
            return this.toplevel;
        }
        for (var i = 0; i < this.toolbars.length; i++) {
            if (this.toolbars[i].id == id) {
                return this.toolbars[i];
            }
        }
        return false;
    },

    switchToolbar: function(id) {
        var toolbar = this.getToolbar(id);
        if (!toolbar) {
            alert('sfAjaxDebug: toolbar "'+id+'" not found');
            return;
        }
        if (!toolbar.loaded) {
            alert('sfAjaxDebug: The requested toolbar hasn\'t been loaded yet!');
            return;
        }
        this.current = toolbar;
        document.getElementById('sfWebDebug').replace(toolbar.content);
        this.insertRequestMenu();
    },

    insertRequestMenu: function() {
        if (!document.getElementById('sfWebDebug')) {
            alert('sfAjaxDebug: could not find the web debug toolbar');
            return;
        }

        var oldNode;

        var menu = document.createElement('div');
        menu.setAttribute('id', 'sfAjaxDebug');
        menu.setAttribute('class', 'sfWebDebugTop');
        menu.style.display = 'none';
        
        var head = document.createElement('h1');
        head.appendChild(document.createTextNode('Ajax Requests'));
        menu.appendChild(head);
        
        var list = document.createElement('li');
        list.setAttribute('id', 'sfAjaxDebugMenu');

        list.appendChild(this.makeRequestMenuEntry(this.toplevel));
        for (var i = 0; i < this.toolbars.length; i++) {
            list.appendChild(this.makeRequestMenuEntry(this.toolbars[i]));
        }

        menu.appendChild(list);

        if (oldNode = document.getElementById('sfAjaxDebug')) {
            oldNode.replace(menu);
        }
        else {
            document.getElementById('sfWebDebugBar').insert({ after: menu });
        }

        var toolbarBtn = document.createElement('li');
        toolbarBtn.setAttribute('id', 'sfAjaxDebugButton');
        var toolbarLink = document.createElement('a');
        toolbarLink.setAttribute('href', '#');
        toolbarLink.setAttribute('onclick', "sfWebDebugShowDetailsFor('sfAjaxDebug'); return false;");
        toolbarLink.appendChild(document.createTextNode(this.current?this.current.title:'ajax requests'));
        toolbarBtn.appendChild(toolbarLink);

        if (oldNode = document.getElementById('sfAjaxDebugButton')) {
            oldNode.replace(toolbarBtn);
        }
        else {
            document.getElementById('sfWebDebugDetails').insert({ top: toolbarBtn });
        }
    },

    makeRequestMenuEntry: function(entry) {
        var liNode = document.createElement('li');
        if (this.current == entry) {
            liNode.setAttribute('class', 'sfAjaxDebugCurrent');
        }
        var aNode = document.createElement('a');
        aNode.setAttribute('href', '#');
        aNode.setAttribute('onclick', 'sfAjaxDebug.switchToolbar("'+entry.id+'"); return false;');
        aNode.appendChild(document.createTextNode(entry.title));
        liNode.appendChild(aNode);
        return liNode;
    }
};

if (Ajax && Ajax.Responders) {
    document.observe('dom:loaded', function() {
        sfAjaxDebug.initialize();
    });

    Ajax.Responders.register({
        onComplete: function(request, json) {
            var token = request.getHeader('X-sfAjaxDebug-Token') || '';
            // console.log('sfAjaxDebug: got token '+token);
            if (token) {
                var module = request.getHeader('X-sfAjaxDebug-Module') || '[unknown]';
                var action = request.getHeader('X-sfAjaxDebug-Action') || '[unknown]';
                sfAjaxDebug.newRequest(module, action, token);
            }
        }   
    });
}

// vim:set ft=javascript:
