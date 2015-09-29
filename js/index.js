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
var progressbar = function(value) {
    var el = $("#progressbar");
    var linearprogress = value*100;
    var text = "Progression: " + Math.round(value*100) + "%";

    if (!el.is(':visible')) el.show();
    el
        .show()
        .text(text)
        .css({
            background: "linear-gradient(to right, rgba(100,100,100,.5) "+linearprogress+"%, rgba(100,100,100,0) "+linearprogress+"%)"
        });
};

function setTimer() {
    jQuery.ajax({
        'url':'php/form.php',
        'type':'POST',
        'data': {setTimer: true},
        success: function(data) {
            var result = jQuery.parseJSON(data);
            if (result.status == true) {
                $('.Timer').ExperimentTimer(result.maxtime,1000);
                $('.progress').show();
            }
        }
    });
}

$( document ).ready(function() {

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
            var callback = function() {
                displayPage('participation');
            };
            processAjax(form,data,callback);
        })

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Participation
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('click','.agree', function() {
            displayPage('instructions');
        })

        .on('click','.decline', function() {
            displayPage('home');
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
                success: function(){
                    displayPage('experiment');
                    setTimer();
                }
            });
        })

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Experiment
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

        .on('click','.drawing_img',function() {
            var form = $('#experiment_frame');
            var winner = $(this).data('item');
            var loser = $(this).data('opp');
            var refid = $('.original').attr('id');
            var userid = form.data('user');
            var data =  {
                    endtrial: true,
                    userid: userid,
                    refid: refid,
                    winner: winner,
                    loser: loser
            };
            var callback =  function(result) {
                if (result.stopexp == false) {
                    $('#item1')
                        .html("<img src='"+result.img1+"' class='drawing_img' data-item='"+result.item1+"' data-opp='"+result.item2+"'>");
                    $('#item2')
                        .html("<img src='"+result.img2+"' class='drawing_img' data-item='"+result.item2+"' data-opp='"+result.item1+"'>");
                    progressbar(result.progress);
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
            processAjax(form,data,callback);
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Contact form
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
       // Send an email to the chosen organizer
       .on('click','.contact_send',function(e) {
            e.preventDefault();
           var input = $(this);
           var form = input.length > 0 ? $(input[0].form) : $();
           if (!checkform(form)) {return false;}
           var data = form.serialize();
           var callback = function(result) {
               if (result.status === true) {
                   close_modal('.contact_container');
               }
           };
           processAjax(form,data,callback);
        })
});

