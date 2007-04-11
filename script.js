/**
 * javascript functionality for the lastfm plugin
 *
 * @author Michael Klier <chi@chimeric.de>
 */

/**
 * performs the ajax call
 *
 * @author Michael Klier <chi@chimeric.de>
 */ 
function lastfm_ajax(obj){
    if(!document.getElementById) return;
    if(!obj) return;

    // We use SACK to do the AJAX requests
    var ajax = new sack(DOKU_BASE+'lib/plugins/lastfm/ajax.php');
    ajax_qsearch.sack.AjaxFailedAlert = '';
    ajax_qsearch.sack.encodeURIString = false;

    // show loader
    lastfm_loader(obj);
 
    // define callback
    ajax.onCompletion = function(){
        var data = this.response;
        if(data === ''){ return; }
        obj.style.visibility = 'hidden';
        obj.innerHTML = data;
        obj.style.visibility = 'visible';
    };

    ajax.runAJAX('user='+plugin_lastfm_user+'&chart='+obj.id+'&limit='+plugin_lastfm_limit+'&dformat='+plugin_lastfm_dformat+'&utc_offset='+plugin_lastfm_utc_offset+'&cols='+plugin_lastfm_cols);
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
    var obj = $('plugin_lastfm');
    if(!obj) return;
    var charts = getElementsByClass('plugin_lastfm_chart',obj,'div');
    for(var i=0;i<charts.length;i++) {
        lastfm_ajax(charts[i]);
    }
});
