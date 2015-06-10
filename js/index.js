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

// Process submitted forms
var processform = function(formid,feedbackid) {
    if (typeof feedbackid == "undefined") {
        feedbackid = ".feedback";
    }
    var data = $("#" + formid).serialize();
    jQuery.ajax({
        url: 'php/form.php',
        type: 'POST',
        async: false,
        data: data,
        success: function(data){
            var result = jQuery.parseJSON(data);
            showfeedback(result,feedbackid);
        }
    });
};

// Check email validity
function checkemail(email) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(email);
}

//Show feedback
var showfeedback = function(message,selector) {
    if (typeof selector == "undefined") {
        selector = ".feedback";
    }
    $(""+selector)
        .show()
        .html(message)
        .fadeOut(5000);
};

// Set up tinyMCE (rich-text textarea)
var tinymcesetup = function() {
    tinymce.init({
        mode: "textareas",
        selector: ".tinymce",
        width: "90%",
        height: 300,
        plugins: [
            "advlist autolink lists charmap print preview hr spellchecker",
            "searchreplace wordcount visualblocks visualchars code fullscreen",
            "save contextmenu directionality template paste textcolor"
        ],
        content_css: "js/tinymce/skins/lightgray/content.min.css",
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",
        style_formats: [
            {title: 'Bold text', inline: 'b'},
            {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
            {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
            {title: 'Example 1', inline: 'span', classes: 'example1'},
            {title: 'Example 2', inline: 'span', classes: 'example2'},
            {title: 'AppTable styles'},
            {title: 'AppTable row 1', selector: 'tr', classes: 'tablerow1'}
        ]
    });

};

function getpage() {
    var params = getParams();
    var page = (params.page == undefined) ? 'home':params.page;
    jQuery.ajax({
        url: 'php/form.php',
        data: {get_app_status: true},
        type: 'POST',
        async: true,
        success: function(data) {
            var json = jQuery.parseJSON(data);
            console.log('Site status: '+json);
            console.log('page: '+page);
            if (json === 'Off') {
                $('#pagecontent')
                    .html("<div id='content'><p id='warning'>Sorry, the website is currently under maintenance.</p></div>")
                    .fadeIn(200);
            } else {
                if (page === undefined) {
                    loadpageonclick('home',false);
                } else {
                    if (page !== false && page != 'install') {
                        var urlparam = parseurl();
                        loadpageonclick(page,''+urlparam);
                    }
                }
            }
        }
    })
}

// Load page by clicking on menu sections
var loadpageonclick = function(pagetoload,param) {
    param = (param === undefined || param === "") ? false: param;
    var stateObj = { page: pagetoload };
    var url = (param === false) ? "index.php?page="+pagetoload:"index.php?page="+pagetoload+"&"+param;

    jQuery.ajax({
        url: 'pages/'+pagetoload+'.php',
        type: 'GET',
        async: true,
        data: param,
        beforeSend: function() {
            $('#pagecontent').fadeOut(200);
            $('#loading').show();
        },
        complete: function () {
            $('#loading').hide();
        },
        success: function(data){
            var json = jQuery.parseJSON(data);
            history.pushState(stateObj, pagetoload, url);

            $('#pagecontent')
                .empty()
                .html(json)
                .fadeIn(200)
                .find(".section_page").each(function() {
                    $(this).fadeIn('slow');
                });
            tinymcesetup();
        }
    });
};

// Parse URL
var parseurl = function() {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    vars = vars.slice(1,vars.length);
    vars = vars.join("&");
    return vars;
};

// Get url params ($_GET)
var getParams = function() {
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
};

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

// Close modal window
var close_modal = function(modal_id) {
    $("#lean_overlay").fadeOut(200);
    $(modal_id).css({"display":"none"});
    $('#submission_form').empty();
};

// Show the targeted modal section and hide the others
var showmodal = function(sectionid) {
    $('.modal_section').each(function() {
        var thisid = $(this).attr('id');
        if (thisid === sectionid) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
};

$( document ).ready(function() {

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Main body
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
    $('.mainbody')

        .ready(function() {
            getpage();
        })

        // Trigger modal dialog box for log in/contact
        .on('mouseover',"a[rel*=leanModal]",function(e) {
            e.preventDefault();
            $(this).leanModal({top : 50, overlay : 0.6, closeButton: ".modal_close" });
        })

        .on('click','#modal_trigger_contact',function(e) {
            e.preventDefault();
            showmodal('send_msg');
            $(".header_title").text('Send a message');
        })

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Home
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('click','.user_form',function(e) {
            e.preventDefault();
            var name = $('input#user_name').val();
            var email = $('input#user_email').val();
            var age = $('input#user_age').val();
            var gender = $('select#user_gender').val();
            var drawlvl = $('select#user_drawlvl').val();
            var artint = $('select#user_artint').val();
            var language = $('select#user_language').val();
            var refid = $(this).attr('data-ref');

            if (name == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#user_name").focus();
                return false;
            }

            if (email == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#user_email").focus();
                return false;
            }

            if (checkemail(email) == false) {
                showfeedback('<p id="warning">Invalid email</p>');
                $("input#user_email").focus();
                return false;
            }

            if (age == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#user_age").focus();
                return false;
            }

            if (gender == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("select#user_gender").focus();
                return false;
            }

            if (drawlvl == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("select#user_drawlvl").focus();
                return false;
            }

            if (artint == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("select#user_artint").focus();
                return false;
            }

            if (language == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("select#user_language").focus();
                return false;
            }

            jQuery.ajax({
                url: 'php/form.php',
                type: 'POST',
                async: false,
                data: {
                    add_user: true,
                    name: name,
                    email: email,
                    age: age,
                    gender: gender,
                    drawlvl: drawlvl,
                    artint: artint,
                    language: language,
                    refid: refid
                    },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    loadpageonclick('participation');
                }
            });
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

