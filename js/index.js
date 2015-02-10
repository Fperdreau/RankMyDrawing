/**
 * Created by Florian on 04/10/14.
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
    console.log(data);
    jQuery.ajax({
        url: 'php/form.php',
        type: 'POST',
        async: false,
        data: data,
        success: function(data){
            var result = jQuery.parseJSON(data);
            console.log("returned result:"+result);
            showfeedback(result,feedbackid);
        }
    });
};

// Check email validity
function checkemail(email) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(email);
};

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
            {title: 'Table styles'},
            {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
        ]
    });

};

// Load page by clicking on menu sections
var loadpageonclick = function(pagetoload,param) {
    param = typeof param !== 'undefined' ? param : false;
    var stateObj = { page: pagetoload };

    if (param == false) {
        jQuery.ajax({
            url: 'pages/'+pagetoload+'.php',
            type: 'GET',
            async: true,
            data: param,
            success: function(data){
                var json = jQuery.parseJSON(data);
                history.pushState(stateObj, pagetoload, "index.php?page="+pagetoload);

                $('#loading').hide();
                $('#pagecontent')
                    .html('<div>'+json+'</div>')
                    .fadeIn('slow');
                tinymcesetup();

            }
        });
    } else {
        jQuery.ajax({
            url: 'pages/'+pagetoload+'.php',
            type: 'GET',
            async: false,
            data: param,
            success: function(data){
                var json = jQuery.parseJSON(data);
                history.pushState(stateObj, pagetoload, "index.php?page="+pagetoload+"&"+param);

                $('#loading').hide();
                $('#pagecontent')
                    .html('<div>'+json+'</div>')
                    .fadeIn('slow');
                tinymcesetup();

            }
        });
    }
};

// Parse URL
var parseurl = function() {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    vars = vars.slice(1,vars.length);
    vars = vars.join("&");
    console.log(vars);
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
}

var logout = function() {
    loadpageonclick('logout');
    $('.warningmsg')
        .show()
        .html("You have been logged out!");
}

$( document ).ready(function() {

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Main body
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
    $('.mainbody')

        .ready(function() {
            // Automatically parse url and load the corresponding page
            var params = getParams();
            if (params.page == undefined) {
                loadpageonclick('home',false);
            } else {
                var page = params.page;
                if (page != false && page != 'install') {
                    var urlparam = parseurl();
                    loadpageonclick(page,''+urlparam);
                }
            }
        })

        // Trigger modal dialog box for log in/contact
        .on('mouseover',"a[rel*=leanModal]",function(e) {
            e.preventDefault();
            $(this).leanModal({top : 50, overlay : 0.6, closeButton: ".modal_close" });
        })

        .on('click','#modal_trigger_contact',function(e) {
            e.preventDefault();
            $(".send_msg").show();
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
                    console.log(result);
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
                    console.log(result);
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
            console.log('hello');
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
                    console.log(result);

                    if (result === "sent") {
                        $('.send_msg').html('<p id="success">Your message has been sent!</p>');
                    } else if (result === "not_sent") {
                        showfeedback('<p id="warning">Oops, something went wrong!</p>');
                    }
                }
            });
            return false;
        })

}).on({
    ajaxStart: function() { $("#loading").show(); },
    ajaxStop: function() { $("#loading").hide(); }
});

