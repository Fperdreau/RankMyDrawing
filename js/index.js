/*
Copyright © 2014, F. Perdreau, Radboud University Nijmegen
=======
This file is part of RankMyDrawings.

RankMyDrawings is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

RankMyDrawings is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with RankMyDrawings.  If not, see <http://www.gnu.org/licenses/>.

*/

/*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 General functions
 %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
var progressbar = function(id,value) {
    var el = $('#'+id);
    var size = $(el).width();
    var linearprogress = value*100;
    var text = "Progression: "+Math.round(value*100)+"%";

    $(el)
        .show()
        .text(text)
        .css({
            background: "linear-gradient(to right, rgba(200,200,200,.7) "+linearprogress+"%, rgba(200,200,200,0) "+linearprogress+"%)"
        });
};

var logout = function() {
    loadpageonclick('logout');
    $('.warningmsg')
        .show()
        .html("You have been logged out!");
};

$( document ).ready(function() {

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Main body
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
    $('.mainbody')


    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Home
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('click','.user_form',function(e) {
            e.preventDefault();
            var input = $(this);
            var form = input.length > 0 ? $(input[0].form) : $();
            if (!checkform(form)) {return false;}
            var data = form.serialize();
            var callback = function(result) {
                loadpageonclick('participation');
            };
            processAjax(form,data,callback);
        })

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Participation
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('click','.agree', function() {
            loadpageonclick('instructions');
        })

        .on('click','.decline', function() {
            loadpageonclick('home');
        })

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Instruction
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('click','.start_btn', function() {
            var userid = $(this).attr('data-user');
            var refid = $(this).attr('data-ref');
            jQuery.ajax({
                url: 'php/form.php',
                type: 'POST',
                async: false,
                data: {
                    startexp: true,
                    userid: userid,
                    refid: refid
                    },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    loadpageonclick('experiment');
                }
            });
        })

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Experiment
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

        .on('click','.drawing_img',function() {
            var winner = $(this).attr('data-item');
            var loser = $(this).attr('data-opp');
            var refid = $('.original').attr('id');
            var userid = $('#experiment_frame').attr('data-user');
            jQuery.ajax({
                url: 'php/form.php',
                type: 'POST',
                async: false,
                data: {
                    endtrial: true,
                    userid: userid,
                    refid: refid,
                    winner: winner,
                    loser: loser
                    },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    var trial = result.trial;
                    var item1 = result.item1;
                    var item2 = result.item2;
                    var img1 = result.img1;
                    var img2 = result.img2;
                    var progress = result.progress;

                    if (result.stopexp == false) {
                        $('#item1')
                        .html("<img src='"+img1+"' class='drawing_img' data-item='"+item1+"' data-opp='"+item2+"'>");
                        $('#item2')
                        .html("<img src='"+img2+"' class='drawing_img' data-item='"+item2+"' data-opp='"+item1+"'>");
                        progressbar('progressbar',progress);
                    } else {
                        loadpageonclick('logout');
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
                }
            });
        });

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Contact form
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
       // Send an email to the chosen organizer
       $('.contact_container').on('click','.contact_send',function(e) {
            e.preventDefault();
            var message = $("textarea#message").val();
            var contact_mail = $("input#contact_mail").val();
            var contact_name = $("input#contact_name").val();

            if (contact_mail == "Your email") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#contact_mail").focus();
                return false;
            }

            if (!checkemail(contact_mail)) {
                showfeedback('<p id="warning">Invalid email</p>');
                $("input#contact_mail").focus();
                return false;
            }

            if (contact_name == "Your name") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#contact_name").focus();
                return false;
            }

            jQuery.ajax({
                url: 'php/form.php',
                type: 'POST',
                async: true,
                data: {
                    contact_send: true,
                    message: message,
                    name: contact_name,
                    mail: contact_mail},
                success: function(data){
                    var result = jQuery.parseJSON(data);

                    if (result === "sent") {
                        $('.send_msg').html('<p id="success">Your message has been sent!</p>');
                        close_modal('.contact_container');
                    } else if (result === "not_sent") {
                        showfeedback('<p id="warning">Oops, something went wrong!</p>');
                    }
                }
            });
            return false;
        })
});

