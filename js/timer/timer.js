/**
 * File for js and jquery functions
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
 * along with Journal Club Manager.  If not, see <http://www.gnu.org/licenses/>.
 */

(function($){
    var ExperimentTimer = function(element, options) {
        var defaults = {
            style: {
                'padding':'5px',
                'color': 'rgba(255, 255, 255, .8)',
                'font-size': '20px',
                'background': 'rgba(64, 64, 64, .9',
                'font-weight': 500
                },
            maxtime: 30,
            interval: 1000,
            afterend: function() {}
        };
        this.status = false;
        var elem = $(element);
        elem.data('status',false);
        var obj = this;
        var settings = $.extend(defaults, options || {});

        elem.html("<span class='ExperimentTimer'></span>");
        obj.timerDiv = elem.find('.ExperimentTimer');
        obj.timerDiv.css(settings.style);

        this.start = function() {
            elem.data('status',true);
            var currentTime = new Date();
            var endTime = new Date(currentTime.getTime() + settings.maxtime*60000);
            jQuery.ajax({
                url: 'js/timer/process.php',
                type: 'POST',
                data: {
                    getTime: true,
                    max: endTime},
                success: function(data) {
                    var result = jQuery.parseJSON(data);
                    obj.refreshtimer = setInterval(function() {
                        var currentTime = new Date();
                        var maxTime = new Date(result);
                        var remainingTime = maxTime - currentTime;
                        if (remainingTime >= 0) {
                            var ms = 1000*Math.round(remainingTime/1000); // round to nearest second
                            var d = new Date(ms);
                            var minutes = (d.getUTCMinutes() < 10) ? '0'+d.getUTCMinutes(): d.getUTCMinutes();
                            var secondes = (d.getUTCSeconds() < 10) ? '0'+d.getUTCSeconds(): d.getUTCSeconds();
                            obj.timerDiv.html(minutes + ':' + secondes);
                        } else {
                            obj.stop();
                        }
                    }, settings.interval);
                }
            });
        };

        this.stop = function() {
            obj.timerDiv.html("Time's up!");
            clearInterval(obj.refreshtimer);
            obj.settings.afterend();
        }

    };

    // Wrapper function
    $.fn.experimenttimer = function(options) {
        return this.each(function() {
            var element = $(this);

            // Return early if this element already has a plugin instance
            if (element.data('experimenttimer')) return this;

            // pass options to plugin constructor
            var myplugin = new ExperimentTimer(this, options);

            // Store plugin object in this element's data
            element.data('experimenttimer', myplugin);
        });
    };
})(jQuery);
