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


(function($) {

    var display = function(self) {
        var currentTime = new Date();
        var endTime = new Date(currentTime.getTime() + self.maxTime*60000);
        jQuery.ajax({
            url: 'js/timer/process.php',
            type: 'POST',
            data: {
                getTime: true,
                max: endTime},
            success: function(data) {
                var result = jQuery.parseJSON(data);
                setInterval(function() {
                    var currentTime = new Date();
                    var maxTime = new Date(result);
                    var remainingTime = maxTime - currentTime;
                    var ms = 1000*Math.round(remainingTime/1000); // round to nearest second
                    var d = new Date(ms);
                    var minutes = (d.getUTCMinutes() < 10) ? '0'+d.getUTCMinutes(): d.getUTCMinutes();
                    var secondes = (d.getUTCSeconds() < 10) ? '0'+d.getUTCSeconds(): d.getUTCSeconds();
                    self.timerDiv.html(minutes + ':' + secondes);

                }, self.interval);
            }
        });
    };

    $.fn.ExperimentTimer = function (maxTime, interval) {
        this.maxTime = maxTime;
        this.interval = interval;
        this.style = {
            'padding':'5px',
            'color': 'rgba(255, 255, 255, .8)',
            'font-size': '20px',
            'background': 'rgba(64, 64, 64, .9',
            'font-weight': 500
        };
        this.html("<span class='ExperimentTimer'></span>");
        this.timerDiv = this.find('.ExperimentTimer');
        this.timerDiv.css(this.style);
        var self = this;
        display(self);
        this.fadeIn();

        return this;
    }

}(jQuery));
