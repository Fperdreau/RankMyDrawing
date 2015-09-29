/**
 * Created by Florian on 28/09/2015.
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
