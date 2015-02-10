/**
 * Created by Florian on 26/01/15.
 */

// Display reference drawing content (instructions or consent form)
var display_content = function(type,lang,content,refid) {
    var html = "<div class='div_display_content' id='display_"+type+"' data-lang='"+lang
        +"' data-ref='"+refid+"' data-type='"+type+"'>"+content+"</div>";
    $('.'+type)
        .html(html)
        .fadeIn('slow');
}
// Spin animation when a page is loading
var $loading = $('#loading').hide()

$( document ).ready(function() {
    $(".mainbody")

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Header menu/Sub-menu
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Display/Hide sub-menu
        // Main menu sections
        .on('click',".menu-section",function(){
            $(".menu-section").removeClass("activepage");
            $(this).addClass("activepage");

            if ($(this).is('[data-url]')) {
                var pagetoload = $(this).attr("data-url");
                loadpageonclick(pagetoload,false);
            }
        })

        // Log out
        .on('click',"#logout",function(){
            jQuery.ajax({
                url: 'pages/logout.php',
                type: 'POST',
                async: false,
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    console.log(result);
                    window.location = "index.php";
                }
            });
        })

        // Show menu
        .on('click','.displaymenu_btn',function() {
            var status = $(this).attr('id');
            if (status == 'off') {
                $('.menu').animate({left: "+=150px"},300);
                $(this).attr('id','on');
            } else {
                $('.menu').animate({left: "-=150px"},300);
                $(this).attr('id','off');
            }
        })

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Admin information
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

		// Display change password form
		.on('click','.change_pwd', function(e) {
			$('.change_pwd_form').toggle();
		})

		// Change password on confirmation
		.on('click','.conf_changepw',function(e) {
			e.preventDefault();
			var username = $("input#ch_username").val();
			var oldpassword = $("input#ch_oldpassword").val();
			var password = $("input#ch_password").val();
            var conf_password = $("input#ch_conf_password").val();

            if (oldpassword == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#ch_oldpassword").focus();
                return false;
            }

            if (password == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#ch_password").focus();
                return false;
            }

            if (conf_password == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#ch_conf_password").focus();
                return false;
            }

            if (password !== conf_password) {
                showfeedback('<p id="warning">Passwords must match!</p>');
                $("input#ch_conf_password").focus();
                return false;
            }

            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: true,
                data: {
                    conf_changepw: true,
                    username: username,
                    oldpassword: oldpassword,
                    password: password
                    },
                	success: function(data){
	                    var result = jQuery.parseJSON(data);
	                    if (result === "changed") {
	                        $('.change_pwd_form').html('<p id="success">Your password has been modified.</p>');
	                    } else if (result === "wrong") {
	                        showfeedback('<p id="warning">Wrong password!</p>');
                            $("input#ch_oldpassword").focus();
	                    }
	                }
            });
		})

		// Modify admin information
		.on('click','.change_admininfo',function(e) {
            e.preventDefault();
			var username = $('input#ch_username').val();
			var email = $('input#ch_email').val();

            if (username == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#ch_username").focus();
                return false;
            }

            if (email == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#ch_email").focus();
                return false;
            }

            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: true,
                data: {
                    mod_admininfo: true,
                    username: username,
                    email: email
                    },
                    success: function(data){
                        var result = jQuery.parseJSON(data);
                        showfeedback(result);
                    }
            });
        })

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Experiment settings
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Select ref drawing
        .on('change','.select_ref', function(e) {
            e.preventDefault();
            var refid = $(this).val();
            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: false,
                data: {
                    select_ref: true,
                    refid: refid},
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    $('.all_ref_params').html(result);
                }
            });
        })

        // Modify parameters
        .on('click','.mod_ref_params',function(e) {
            e.preventDefault();
            var refid = $(this).attr('data-ref');
            var elo = $('input#elo_'+refid).val();
            var pair = $('input#pair_'+refid).val();
            var max_nb_users = $('input#max_nb_users_'+refid).val();
            var status = $('select#status_'+refid).val();
            var filter = $('select#filter_'+refid).val();

            if (elo == "") {
                showfeedback('<p id="warning">This field is required</p>','.feedback_params');
                $("input#elo").focus();
                return false;
            }

            if (pair == "" || pair == 0) {
                showfeedback('<p id="warning">This value must be greater than 0</p>','.feedback_params');
                $("input#pair").focus();
                return false;
            }

            if (max_nb_users == "" || max_nb_users == 0) {
                showfeedback('<p id="warning">This value must be greater than 0</p>','.feedback_params');
                $("input#max_nb_users").focus();
                return false;
            }

            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: false,
                data: {
                    mod_ref_params: true,
                    initial_score: elo,
                    refid: refid,
                    nb_pairs: pair,
                    max_nb_users: max_nb_users,
                    status: status,
                    filter: filter},
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    showfeedback(result,'.feedback_params');
                }
            });
        })

		// Add content to the database
        .on('click','.addcontent',function(e) {
            e.preventDefault();
            var lang = $('input#lang_name').val();
            var instruction = tinyMCE.get('instruction').getContent()
            var consent = tinyMCE.get('consent').getContent()
            var refid = $(this).attr('data-ref');

            if (lang == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#lang_name").focus();
                return false;
            }

            if (instruction == "") {
                showfeedback('<p id="warning">This field is required</p>');
                tinymce.execCommand('mceFocus',false,'instruction');
                return false;
            }

            if (consent == "") {
                showfeedback('<p id="warning">This field is required</p>');
                tinymce.execCommand('mceFocus',false,'consent');
                return false;
            }

            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: false,
                data: {
                    add_content: true,
                    lang: lang,
                    refid: refid,
                    instruction: instruction,
                    consent: consent},
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    $('.'+type)
                        .html(result)
                        .fadeIn();
                }
            });
        })

		// Confirm modification and update the database
        .on('click','.modcontent',function(e) {
            e.preventDefault();
            var type = $(this).attr('data-type');
            var lang = $(this).attr('data-lang');
            var content = tinyMCE.get(type).getContent();
            var refid = $(this).attr('data-ref');
            console.log(lang);

            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: false,
                data: {
                    mod_content: true,
                    type: type,
                    lang: lang,
                    refid: refid,
                    content: content},
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    display_content(type,lang,content,refid);
                }
            });
        })

		// Delete the selected content (instruction or consent form)
        .on('click','.delcontent',function(e) {
            e.preventDefault();
            var type = $(this).attr('data-type');
            var lang = $('select#select_lang').val();
            var refid = $(this).attr('data-ref');

            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: false,
                data: {
                    del_content: true,
                    type: type,
                    lang: lang,
                    refid: refid},
                success: function(data){
                    var result = jQuery.parseJSON(data);
                }
            });
        })

		// Modify content of the consent form (replace the current div by a textarea)
        .on('click','#display_consent',function(e) {
            e.preventDefault();
            var type = $(this).attr('data-type');
            var lang = $(this).attr('data-lang');
            var refid = $(this).attr('data-ref');
            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: false,
                data: {
                    cha_content: true,
                    type: type,
                    lang: lang,
                    refid: refid},
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    var txtarea = "<textarea name='"+type+"' id='"+type+"' class='tinymce'>"+result+"</textarea>";
                    $('.consent').html(txtarea+"<p style='text-align: right'><input type='submit'"+
                        " id='submit' value='Modify' class='modcontent' data-ref='"+refid+"' data-lang='"+lang+"' data-type='consent'></p>");
                    window.tinymce.dom.Event.domLoaded = true;
                    tinymcesetup();
                }
            });
        })

		// Modify content of the instructions (replace the current div by a textarea)
        .on('click','#display_instruction',function(e) {
            e.preventDefault();
            var type = $(this).attr('data-type');
            var lang = $(this).attr('data-lang');
            var refid = $(this).attr('data-ref');
            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: false,
                data: {
                    cha_content: true,
                    type: type,
                    lang: lang,
                    refid: refid},
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    var txtarea = "<textarea name='"+type+"' id='"+type+"' class='tinymce'>"+result+"</textarea>";
                    $('.instruction').html(txtarea+"<p style='text-align: right'><input type='submit' id='submit'"+
                        " value='Modify' class='modcontent' data-ref='"+refid+"' data-lang='"+lang+"' data-type='instruction'></p>");
                    window.tinymce.dom.Event.domLoaded = true;
                    tinymcesetup();
                }
            });
        })

        // Manage instructions
        .on('change','.select_lang',function(e) {
            e.preventDefault();
            var refid = $(this).attr('id');
            var option = $(this).val();
            var type = $(this).attr('data-type');
            $('.consent').hide();
            $('.instruction').hide();
            $('.lang_label').hide();

            if (option == 'add') {
                $('.lang_label')
                    .html("<label for='lang_name' class='label'>Label</label><input type='text' id='lang_name' value=''>")
                    .fadeIn();
                $('.instruction')
                    .html("<textarea name='instruction' "+
                        "class='tinymce' id='instruction'></textarea>")
                    .fadeIn();
                $('.consent')
                    .html("<textarea name='consent' class='tinymce' id='consent'></textarea>"
                        +"<p style='text-align: right'><input type='submit' id='submit' class='addcontent' data-ref='"+refid+"'></p>")
                    .fadeIn();
                window.tinymce.dom.Event.domLoaded = true;
                tinymcesetup();
            } else {
                jQuery.ajax({
                    url: '../admin/php/form.php',
                    type: 'POST',
                    async: false,
                    data: {
                        display_content: true,
                        lang: option,
                        refid: refid},
                    success: function(data){
                        var result = jQuery.parseJSON(data);
                        display_content("instruction",option,result.instruction,refid);
                        display_content("consent",option,result.consent,refid);
                    }
                });
            }
        })

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Drawing management
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Add a reference drawing
         .on('click','.newrefid',function(e) {
            e.preventDefault();
            var refid = $("input#newref").val();
            if (refid == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#newref").focus();
                return false;
            }

            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: false,
                data: {
                    check_availability: true,
                    refid: refid
                },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    if (result == true) {
                        showfeedback('<p id="warning">Sorry, this name is already taken. Please choose another one.</p>');
                        $("input#newref").focus();
                        return false;
                    } else {
                       var html = "<form method='post' action='js/mini-upload-form/upload.php' enctype='multipart/form-data' id='upload' class='upl_newref'>"+
                            "<div id='drop'>"+
                                "<a>Add files</a><input type='file' name='ref,"+refid+"' multiple/> Or drag it here"+
                            "</div>"+
                            "<ul></ul>"+
                        "</form>";
                        $('.refupload')
                            .css('display','table-cell').fadeIn('slow');
                        $('#upref').html(html);
                        return false;
                    }
                }
            });
        })

        // Delete reference drawing
        .on('click','.deleteref',function(e) {
            e.preventDefault();
            var refid = $(this).attr('data-ref');
            console.log(refid);
            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: true,
                data: {
                    deleteref: refid
                },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    console.log(result);
                    $('#'+refid).remove();
                }
            });
        })

        // Delete selected item
        .on('click','.delete_btn_item',function(e) {
            e.preventDefault();
            var itemid = $(this).attr('id');
            var drawref = $(this).attr('data-item');
            console.log(itemid);
            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: false,
                data: {
                    delete_item: itemid,
                    drawref: drawref
                },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    console.log(result);
                    $('#item_'+itemid).remove();
                }
            });
        })

        // Sort items
        .on('change','.sortitems',function(e) {
            e.preventDefault();
            var filter = $(this).val();
            var refdraw = $(this).attr('data-ref');
            jQuery.ajax({
                url: 'php/form.php',
                type: 'POST',
                async: false,
                data: {
                    sortitems: filter,
                    refdraw: refdraw
                },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    console.log(result);
                    $('#itemlist_'+refdraw).html(result);
                }
            });
            return false;
        })

        // Trigger modal dialog box for publications (show/modify/delete forms)
        .on('mouseover',"a[rel*=item_leanModal]",function(e) {
            e.preventDefault();
            $(this).leanModal({top : 50, width : 500, overlay : 0.6, closeButton: ".modal_close" });
        })

        // Show item details
        .on('click',"#modal_trigger_showitem",function(e){
            e.preventDefault();
            var refid = $(this).attr('data-ref');
            var itemid = $(this).attr('data-item');
            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: false,
                data: {
                    show_item: true,
                    refid: refid,
                    itemid: itemid},
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    console.log(result);
                    $('.item_popupContainer').show();
                    $(".item_description")
                        .show()
                        .html(result.content);
                    $(".item_delete").hide();
                    $(".header_title").text('Item description: '+result.item);
                }
            });
        })

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Export tools
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Do a full backup (database + files) if asked
        .on('click','.fullbackup',function(){
            var webproc = true;

            jQuery.ajax({
                url: '../cronjobs/full_backup.php',
                type: 'GET',
                async: false,
                data: {webproc: webproc},
                success: function(data){
                    console.log(data);

                    var json = jQuery.parseJSON(data);
                    console.log(json);
                    $('#full_backup').append('<div class="file_link" data-url="'+json+'" style="width: auto;"><a href="' + json + '">Download backup file</a></div>');
                }
            });
        })

        // Backup the database only if asked
        .on('click','.dbbackup',function(){
            jQuery.ajax({
                url: '../cronjobs/db_backup.php',
                type: 'GET',
                async: false,
                data: {webproc: true},
                success: function(data){
                    console.log(data);

                    var json = jQuery.parseJSON(data);
                    console.log(json);
                    $('#db_backup').append('<div class="file_link" data-url="'+json+'" style="width: auto;"><a href="' + json + '">Download backup file</a></div>');
                }
            });
        })

        // Export ref drawings database to xls
        .on('change','.exportdb',function(e){
            e.preventDefault();
            var refid = $(this).val();
            jQuery.ajax({
                url: 'php/form.php',
                type: 'POST',
                async: false,
                data: {
                    export: true,
                    refid: refid},
                success: function(data){
                    var json = jQuery.parseJSON(data);
                    console.log(json);
                    $('#exportdb').append('<div class="file_link" data-url="'+json+'" style="width: auto;"><a href="' + json + '">Download XLS file</a></div>');
                }
            });
        })

        // Show link to created backup
        .on('click','.file_link', function(){
            var link = $(this).attr('data-url');
            $(this)
                .html('<p id="success">Downloaded</p>')
                .fadeOut(5000);
        })

        // Add a news to the homepage
        .on('click','.post_send',function(e) {
            e.preventDefault();
            var new_post = tinyMCE.activeEditor.getContent();
            var fullname = $("input#fullname").val();
            console.log(new_post);
            if (new_post == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("textarea#post").focus();
                return false;
            }

            jQuery.ajax({
                url: 'php/form.php',
                type: 'POST',
                async: true,
                data: {
                    post_send: true,
                    fullname: fullname,
                    new_post: new_post},
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    console.log(result);

                    if (result === "posted") {
                        showfeedback("<p id='success'>Your message has been posted on the homepage!</p>");
                    }
                }
            });
            return false;
        })

    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Application settings
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

        // Configuration of the application
        .on('click','.config_form_site',function(e) {
            e.preventDefault();
            processform("config_form_site",".feedback_site");
        })

        .on('click','.config_form_exp',function(e) {
            e.preventDefault();
            processform("config_form_exp",".feedback_exp");
        })

        .on('click','.config_form_mail',function(e) {
            e.preventDefault();
            processform("config_form_mail",".feedback_mail");
        })


    /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
     Login dialog
     %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
    // Upload a file
        .on('mouseover','#fileupload').fileupload({
            dataType: 'json',
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    $('<p/>').text(file.name).appendTo(document.body);
                });
            }
        })

        // Trigger modal dialog box for log in
        .on('mouseover',"a[rel*=leanModal]",function(e) {
            e.preventDefault();
            $(this).leanModal({top : 50, overlay : 0.6, closeButton: ".modal_close" });
        })

        // Dialog log in
        .on('click',"#modal_trigger_login",function(e){
            e.preventDefault();
            $(".user_login").show();
            $(".pub_delete").hide();
            $(".header_title").text('Log in');
        });

    // Process events happening on the login/sign up modal dialog box
    $(".popupContainer")

        // Login form
        .on('click',".login",function() {
            var username = $("input#log_username").val();
            var password = $("input#log_password").val();

            if (username == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#log_username").focus();
                return false;
            }

            if (password == "") {
                showfeedback('<p id="warning">This field is required</p>');
                $("input#log_password").focus();
                return false;
            }

            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: true,
                data: {username: username,
                    password: password,
                    login: true
                },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    console.log(result);
                    if (result == 'logok') {
                        location.reload();
                    } else if (result == "wrong_username") {
                        showfeedback('<p id="warning">Wrong username</p>');
                    } else if (result == "wrong_password") {
                        showfeedback('<p id="warning">Wrong username/password</p>');
                    }
                }
            });
            return false;
        });

    $('.item_popupContainer')

        // Show publication deletion confirmation
        .on('click',".del_item",function(e){
            e.preventDefault();
            var itemid = $(this).attr('data-item');
            var drawref = $(this).attr('data-ref');
            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
                async: false,
                data: {
                    delete_item: itemid,
                    drawref: drawref
                },
                success: function(data){
                    var result = jQuery.parseJSON(data);
                    console.log(result);
                    $('#item_'+itemid).remove();
                }
            });
        });
});
