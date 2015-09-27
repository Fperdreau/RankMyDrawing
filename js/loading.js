/**
 * File for javascript/jQuery functions
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2014 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of RankMyDrawings.
 *
 * RankMyDrawings is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * RankMyDrawings is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with RankMyDrawings.  If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * Parse url and get page content accordingly
 * @param page
 * @param urlparam
 */
function getPage(page, urlparam) {
    if (page == undefined) {
        var params = getParams();
        page = (params.page == undefined) ? 'home':params.page;
    }

    urlparam = (urlparam == undefined) ? parseurl():urlparam;
    urlparam = (urlparam === false || urlparam === "") ? false: urlparam;

    jQuery.ajax({
        url: 'php/form.php',
        data: {get_app_status: true},
        type: 'POST',
        async: true,
        success: function(data) {
            var json = jQuery.parseJSON(data);
            if (json === 'Off' && page != 'admin') {
                $('#pagecontent')
                    .html("<div id='content'><div style='vertical-align: middle; margin-top: 20%; text-align: center;'>" +
                    "<div style='font-size: 1.6em; font-weight: 600; margin-bottom: 20px;'>Sorry</div><div> the website is currently under maintenance.</div></div></div>")
                    .fadeIn(200);
            } else {
                displayPage(page,urlparam);
            }
        }
    })
}

/**
 * Load page content by clicking on a menu section
 *
 * @param page
 * @param param
 */
function displayPage(page,param) {
    var stateObj = { page: page };
    var url = (param === false) ? "index.php?page="+page:"index.php?page="+page+"&"+param;
    var el = $('#pagecontent');
    jQuery.ajax({
        url: 'pages/'+page+'.php',
        type: 'GET',
        async: true,
        data: param,
        beforeSend: function() {
            loadingDiv(el);
        },
        complete: function () {
            removeLoading(el);
        },
        success: function(data){
            var json = jQuery.parseJSON(data);
            history.pushState(stateObj, page, url);
            el.hide().html(json).fadeIn(200);
            tinymcesetup();
        }
    });
}

/**
 * Parse URL
 * @returns {Array}
 */
function parseurl() {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    vars = vars.slice(1,vars.length);
    vars = vars.join("&");
    return vars;
}

/**
 * Get URL parameters ($_GET)
 * @returns {{}}
 */
function getParams() {
    var url = window.location.href;
    var splitted = url.split("?");
    if(splitted.length === 1) {
        return {};
    }
    var paramList = decodeURIComponent(splitted[1]).split("&");
    var params = {};
    for(var i = 0; i < paramList.length; i++) {
        var paramTuple = paramList[i].split("=");
        params[paramTuple[0]] = paramTuple[1];
    }
    return params;
}

/**
 * Display loading animation during AJAX request
 * @param el: DOM element in which we show the animation
 */
function loadingDiv(el) {
    el
        .css('position','relative')
        .append("<div class='loadingDiv' style='width: 100%; height: 100%;'></div>")
        .show();
}

/**
 * Remove loading animation at the end of an AJAX request
 * @param el: DOM element in which we show the animation
 */
function removeLoading(el) {
    el.fadeIn(200);
    el.find('.loadingDiv')
        .fadeOut(1000)
        .remove();
}

/**
 * Responsive design part: adapt page display to the window
 */
function adapt() {
    var height = $(window).height();
    var winWidth = $(window).width();
    $('#core').css('min-height',height+"px");

    var modal = $(".modalContainer");
    var modalWidth = modal.outerWidth();
    var modalMargin = (modalWidth<winWidth) ? modalWidth/2:0;
    var modalLeft = (modalWidth<winWidth) ? 50:0;
    modal
        .css({
            'margin-left':-modalMargin+'px',
            'left':modalLeft+'%'});

}

$( document ).ready(function() {

    $(window).resize(function () {
        adapt();
    });

    $('body').ready(function() {
        // Automatically parse url and load the corresponding page
        getPage();
        adapt();
    })

});