/**
 * javascript functionality for the lastfm plugin
 *
 * @author Michael Klier <chi@chimeric.de>
 */

function lastfm_ajax(chart, opts) {
    if(!document.getElementById) return;
    if(!chart) return;
    if(!opts) return;

    var ajax = new sack(DOKU_BASE+'lib/exe/ajax.php');
    ajax.AjaxFailedAlert = '';
    ajax.encodeURIString = false;

    ajax.setVar('call', 'plugin_lastfm');
    ajax.setVar('plugin_lastfm_chart', chart.id);

    for(var i = 0; i < opts.length; i++) {
        ajax.setVar(opts[i].firstChild.className, opts[i].firstChild.innerHTML);
    }

    // show loader
    lastfm_loader(chart);
 
    // define callback
    ajax.onCompletion = function(){
        var data = this.response;
        if(data === ''){ return; }
        chart.style.visibility = 'hidden';
        chart.innerHTML = data;
        chart.style.visibility = 'visible';
    };

    ajax.runAJAX();
}

/**
 * Calls the ajax function for each requested chart
 *
 * @author Michael Klier <chi@chimeric.de>
 */ 
function lastfm_get_charts(charts, opts){
    if(!document.getElementById) return;
    if(!charts) return;
    if(!opts) return;

    for(var i = 0; i < charts.length; i++) {
        lastfm_ajax(charts[i], opts);
    }
}

/**
 * shows the loading image
 *
 * @author Michael KLier <chi@chimeric.de>
 */
function lastfm_loader(obj) {
    if(!obj) return;
    obj.innerHTML = '<img src="'+DOKU_BASE+'lib/plugins/lastfm/images/loader.gif" />'; 
}

// add the init event
addInitEvent(function() {
    var objs = getElementsByClass('plugin_lastfm', document, 'div');
    if(!objs) return;
    for(var i = 0; i < objs.length; i++) {
        var opts   = getElementsByClass('plugin_lastfm_opt', objs[i], 'li');
        var charts = getElementsByClass('plugin_lastfm_chart', objs[i], 'div');
        lastfm_get_charts(charts, opts);
    }
});

// vim:ts=4:sw=4:et:enc=utf-8:
