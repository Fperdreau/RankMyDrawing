/**
 * Created by Florian on 27/09/2015.
 */

(function($) {
    $.fn.extend({
        rankMyDrawings: function () {
            var user = this.data('user');
            var ref = this.data('ref');
            var item1 = null;
            var item2 = null;
            var original = null;
            var img1 = null;
            var img2 = null;
            var progress = 0;

            var progressBarEl = $("#progressbar");
            this.progressbar = function(value) {
                var el = progressBarEl;
                var linearprogress = value*100;
                var text = "Progression: " + Math.round(value*100) + "%";

                if (!el.is(':visible')) el.show();
                el
                    .text(text)
                    .css({
                        background: "linear-gradient(to right, rgba(200,200,200,.7) "+linearprogress+"%, rgba(200,200,200,0) "+linearprogress+"%)"
                    });
            };

            this.display = function() {
                return "" +
                    "<div id='experiment_frame' data-user='"+user+"'>" +
                    "<div class='original_container'>" +
                    "<div class='picture original' id='"+ref+"'>" +
                    "<img src='"+original+"' class='drawing_img'>" +
                    "</div>" +
                    "</div>" +
                    "<div class='progress'><div id='progressbar' style='display: none;'></div></div>" +
                    "<div class='img_container'>" +
                    "<div class='picture drawing' id='item1'>" +
                    "<img src='"+img1+"' class='drawing_img' data-item='"+item1+"' data-opp='"+item2+"'>" +
                    "</div>" +
                    "<div class='picture drawing' id='item2'>" +
                    "<img src='"+img2+"' class='drawing_img' data-item='"+item2+"' data-opp='"+item1+"'>" +
                    "</div>" +
                    "</div>" +
                    "</div>";
            };

            this.each(function() {
                var self = this;
                console.log(self);
                $(this).find('drawing_img').click(function() {
                    var el = $(this);
                    console.log(el);
                    var winner = el.data('item');
                    var loser = el.data('opp');
                    var userid = el.data('user');
                    var data =  {
                       endtrial: true,
                       userid: userid,
                       refid: ref,
                       winner: winner,
                       loser: loser
                    };
                    var callback =  function(result) {
                       item1 = result.item1;
                       item2 = result.item2;
                       img1 = result.img1;
                       img2 = result.img2;
                       progress = result.progress;

                       if (result.stopexp == false) {
                           self.progressbar(progress);
                       } else {
                           displayPage('logout');
                           var count = 5;
                           var countdown = setInterval(function(){
                               $("#countdown").html("You are going to be redirected in "+ count + " seconds!");
                               if (count == 0) {
                                   clearInterval(countdown);
                                   window.location.href = result.redirecturl;
                               }
                               count--;
                           }, 1000);
                       }
                    };
                    processAjax(el,data,callback);
                });

                self.progressbar(progress);
                var html = self.display();
                console.log(html);
                self.html(html);
            });
            return this;
        }
    })
})(jQuery);

