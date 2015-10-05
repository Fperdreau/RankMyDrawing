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

/**
 * display_content
 * Display reference drawing content (instructions or consent form)
 * @param type
 * @param lang
 * @param content
 * @param refid
 */
function display_content(type,lang,content,refid) {
    var html = "<div class='div_display_content' id='display_"+type+"' data-lang='"+lang
        +"' data-ref='"+refid+"' data-type='"+type+"'>"+content+"</div>";
    $('.'+type)
        .html(html)
        .fadeIn('slow');
}

/**
 * Show form to submit a presentation
 * @param refid
 * @param itemid
 */
function showItem(refid, itemid) {
    var formel = $('#item_description');
    var data = {
        show_item: true,
        refid: refid,
        itemid: itemid
    };
    // First we remove any existing submission form
    var callback = function(result) {
        formel
            .html(result.content)
            .fadeIn(200);
    };
    processAjax(formel,data,callback,'../admin/php/form.php');
}

/**
 * Show item's settings in a modal window
 * @param refid
 */
function showItemSettings(refid) {
    var formel = $('#item_settings');
    var data = {
        show_item_settings: true,
        refid: refid
    };
    // First we remove any existing submission form
    var callback = function(result) {
        formel
            .html(result.content)
            .fadeIn(200);
        $('.popupHeader').html(result.ref+' | Settings');
    };
    processAjax(formel,data,callback,'../admin/php/form.php');
}

/**
 * logout()
 * Log out the user and trigger a modal window informing the user he/she has been logged out
 */
function logout() {
    $('.warningmsg').remove();
    jQuery.ajax({
        url: '../admin/php/form.php',
        type: 'POST',
        data: {logout: true},
        success: function() {
            $('.mainbody').append("<div class='logoutWarning'>You have been logged out!</div>");
            $('.logoutWarning').fadeIn(200);
            setTimeout(function() {
                $('.logoutWarning')
                    .fadeOut(200)
                    .empty()
                    .hide();
                location.reload();
            },3000);
        }
    });
}

/**
 * Automatically show login window on start (if user is not already logged in)
 */
function showLogin() {
    jQuery.ajax({
        url: '../admin/php/form.php',
        type: 'POST',
        data: {isLogged: true},
        success: function(data) {
            var json = jQuery.parseJSON(data);
            if (json === false) {
                $('.leanModal#user_login')
                    .leanModal({top : 50, overlay : 0.6, closeButton: ".modal_close" })
                    .click();
            }
        }
    });
}

$( document ).ready(function() {
    $('body').ready(function() {
        showLogin();
    });

    $(".mainbody")

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Header menu/Sub-menu
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Main menu sections
        .on('click',".menu-section",function(e){
            e.preventDefault();
            e.stopPropagation();

            $(".menu-section").removeClass("activepage");
            $(this).addClass("activepage");
            var sideMenu = $('.sideMenu');
            var width = sideMenu.width();
            if ($(this).is('[id]')) {
                var pagetoload = $(this).attr("id");
                var param = ($(this).is('[data-param]'))? $(this).data('param'):false;
                getPage(pagetoload,param);
                sideMenu
                    .animate({left: "-="+width+"px"},300)
                    .attr('id','off');
                $('#core').animate({left: "-="+width+"px"},300);

            }
        })

        // Show menu
        .on('click','.menu_btn',function() {
            var sideMenu = $('.sideMenu');
            var core = $('#core');
            var status = sideMenu.attr('id');
            var width = sideMenu.width();
            var height = core.outerHeight()-$('header').height();
            sideMenu.css('height',height+'px');
            if (status == 'off') {
                sideMenu
                    .animate({left: "+="+width+"px"},300)
                    .attr('id','on');
                core.animate({left: "+="+width+"px"},300);
            } else {
                sideMenu
                    .animate({left: "-="+width+"px"},300)
                    .attr('id','off');
                core.animate({left: "-="+width+"px"},300);
            }
        })

        .on('click','#core',function(e) {
            var sideMenu = $('.sideMenu');
            var status = sideMenu.attr('id');
            var width = sideMenu.width();
            if (status == 'on') {
                sideMenu
                    .animate({left: "-="+width+"px"},300)
                    .attr('id','off');
                $('#core').animate({left: "-="+width+"px"},300);
            }
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Admin information
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Display change password form
        .on('click','.change_pwd', function() {
            $('.change_pwd_form').toggle();
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Experiment settings
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        .on('click','.refdraw-settings',function(e) {
            var refid = $(this).data('ref');
            showItemSettings(refid)
        })
        // Select ref drawing
        .on('change','.select_ref', function(e) {
            e.preventDefault();
            var form = $(this).parent('section');
            var refid = $(this).val();
            var data = {select_ref: true, refid: refid};
            var callback = function(result) {
                $('.all_ref_params').html(result);
            };
            processAjax(form, data, callback,'../admin/php/form.php');
        })

        // Add content to the database
        .on('click','.addcontent',function(e) {
            e.preventDefault();
            var form = $(this).closest('#mailing_send');
            if (!checkform(form)) {return false;}
            var data = form.serializeArray();
            var consent = tinyMCE.get('consent').getContent();
            var instruction = tinyMCE.get('instruction').getContent();
            data = modArray(data,'consent',consent);
            data = modArray(data,'instruction',instruction);
            var callback = function(result) {
                $('.'+type)
                    .html(result)
                    .fadeIn();
            };
            processAjax(form,data, callback);
        })

        // Confirm modification and update the database
        .on('click','.modcontent',function(e) {
            e.preventDefault();
            var type = $(this).data('type');
            var lang = $(this).data('lang');
            var content = tinyMCE.get(type).getContent();
            var refid = $(this).data('ref');
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
            var type = $(this).data('type');
            var lang = $(this).data('lang');
            var refid = $(this).data('ref');
            jQuery.ajax({
                url: '../admin/php/form.php',
                type: 'POST',
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
                    if (result.status == true) {
                        showfeedback(result.msg);
                        $("input#newref").focus();
                        return false;
                    } else {
                        var html = result.msg;
                        $('.refupload').show();
                        $('#upref').html(html);
                        return false;
                    }
                }
            });
        })

        // Delete reference drawing
        .on('click','.refdraw-delbutton',function(e) {
            e.preventDefault();
            var refid = $(this).data('ref');
            var data = {deleteref: refid};
            var el = $('.refdraw-div#'+refid);
            var callback = function(result) {
                if (result.status == true) {
                    $('#'+refid).remove();
                }
            };
            processAjax(el,data,callback,'../admin/php/form.php');
        })

        // Delete selected item
        .on('click','.delete_btn_item',function(e) {
            e.preventDefault();
            var itemid = $(this).attr('id');
            var drawref = $(this).attr('data-item');
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
                    $('.itemList#'+refdraw).html(result);
                }
            });
        })

        // Show item details
        .on('click',".thumb",function(e){
            e.preventDefault();
            var refid = $(this).data('ref');
            var itemid = $(this).data('item');
            showItem(refid, itemid);
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Export tools
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
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
                    $('#exportdb').append('<div class="file_link" data-url="'+json+'" style="width: auto;"><a href="' + json + '">Download XLS file</a></div>');
                }
            });
        })

        // Show link to created backup
        .on('click','.file_link', function(){
            window.location.href = $(this).attr('data-url');
            $(this)
                .html('<p id="success">Downloaded</p>')
                .fadeOut(5000);
        })

        /*%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
         Login dialog
         %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
        // Login form
        .on('click',".login",function(e) {
            e.preventDefault();
            var input = $(this);
            var form = input.length > 0 ? $(input[0].form) : $();
            var callback = function(result) {
                if (result.status === true) {
                    location.reload();
                }
            };
            processForm(form,callback);
        })

        // Log out
        .on('click',"#logout",function(){
            logout();
        })

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
                    $('#item_'+itemid).remove();
                    close_modal('#item_modal');
                }
            });
        });

});
