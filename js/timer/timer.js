/**
 * Created by Florian on 28/09/2015.
 */

(function($) {
    $.fn.extend({
        timer: function (maxTime) {
            this.maxTime = maxTime;
            this.timeRemaining = maxTime;

            this.display = function() {
                return "Time remaining: " + this.timeRemaining + " minutes";
            };

            this.getTime = function() {
                var self = this;
                jQuery.ajax({
                    url: "process.php",
                    data: {getTime: true,
                        max: self.maxTime},
                    success: function(data) {
                        self.timeRemaining = jQuery.parseJSON(data);
                    }
                });
            };

            return this.each(function() {
                var el = $(this);
                el.getTime();
                var html = el.display();
                el.html(html);
            });
        }
    })
})(jQuery);
