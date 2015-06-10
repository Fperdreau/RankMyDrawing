/*
 Copyright © 2014, Florian Perdreau
 This file is part of Journal Club Manager.

 Journal Club Manager is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Journal Club Manager is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Journal Club Manager.  If not, see <http://www.gnu.org/licenses/>.
 */

$(document).ready(function() {

    // Get file information on drop
    var getdrop = function (el, e) {
        var dt = e.dataTransfer || (e.originalEvent && e.originalEvent.dataTransfer);
        var name = el.find('.upl_input').attr('name');
        console.log(name);
        var files = e.target.files || (dt && dt.files);
        if (files) {
            var nbfiles = files.length;
            for(var i = 0; i < nbfiles; ++i){
                var data = new FormData();
                data.append(name+'[]',files[i]);
                processupl(el,data);
            }
        }
    };

    // Uploading process
    var processupl = function (el, data) {
        console.log(el);
        jQuery.ajax({
            type:'POST',
            method:'POST',
            url:'php/upload.php',
            headers:{'Cache-Control':'no-cache'},
            data:data,
            contentType:false,
            processData:false,

            success: function(response){
                result = jQuery.parseJSON(response);
                el.find('.upl_errors').hide();

                var status = result.status;
                var msg = result.msg;
                var list = el.parent('.itemList');

                if (status == true) {
                    var name = result.msg;
                    // Get item information and display item in the drawings (uploads) list
                    jQuery.ajax({
                        type:'POST',
                        url:'php/form.php',
                        data: {
                            getItems:true,
                            itemid:name,
                            refid:result.refid},
                        success: function(json) {
                            var item = jQuery.parseJSON(json);
                            list.html(item);
                        }
                    });
                } else {
                    el.find('.upl_errors').html(msg).show();
                }
            },

            error: function(response){
                el.find('.upl_errors').html(response.statusText).show();
            }

        });
    };

    var progressbar = function(el,value) {
        var size = el.width();
        var linearprogress = value;
        var text = "Progression: "+Math.round(value*100)+"%";

        el
            .show()
            .text(text)
            .css({
                background: "linear-gradient(to right, rgba(200,200,200,.7) "+linearprogress+"%, rgba(200,200,200,0) "+linearprogress+"%)"
            });
    };

    var dragcounter = 0;
    $('.mainbody')

        .on('dragenter','.upl_container', function(e) {
            e.stopPropagation();
            e.preventDefault();
            dragcounter ++;
            $(this).addClass('dragging');
        })

        .on('dragleave','.upl_container', function(e) {
            e.stopPropagation();
            e.preventDefault();
            dragcounter --;
            if (dragcounter === 0) {
                $(this).removeClass('dragging');
            }
        })

        .on('dragover','.upl_container',function(e) {
            e.stopPropagation();
            e.preventDefault();
        })

        .on('drop','.upl_container',function(e) {
            e.stopPropagation();
            e.preventDefault();
            var el = $(this);
            getdrop(el,e);
            el.removeClass('dragging');
        })

        .on('click','.upl_btn', function() {
            var id = $(this).attr('id');
            var target = $('.upl_input#'+id);
            target.click();
        })

        .on('change','.upl_input',function(e) {
            e.preventDefault();
            var fileInput = $(this)[0];
            var name = $(this).attr('name');
            var id = $(this).attr('id');
            var el = $('.upl_container#'+id);
            for(var i = 0; i < fileInput.files.length; ++i){
                var data = new FormData();
                data.append(name+'[]',fileInput.files[i]);
                processupl(el,data);
            }
        });
});
