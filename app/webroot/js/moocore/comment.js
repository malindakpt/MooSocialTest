/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'mooPhrase', 'mooMention', 'mooEmoji', 'mooAttach', 'mooBehavior', 'mooLike', 'mooTooltip',
        'autogrow', 'overlay'], factory);
    } else if (typeof exports === 'object') {
        // Node, CommonJS-like
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals (root is window)
        root.mooComment = factory();
    }
}(this, function ($, mooPhrase, mooMention, mooEmoji, mooAttach, mooBehavior, mooLike, mooTooltip) {
    
    var activity_comment_edit_array = [];
    var item_comment_edit_array = [];
    
    var initOnCommentFormFlag = false;
    
    var initShowCommentBtn = function(){
        $('.showCommentBtn').unbind("onfocus");
        $('.showCommentBtn').on("focus", function(){
            var data = $(this).data();
            showCommentButton(data.id);
        });
    }
    
    var initShowCommentForm = function(){
        $('.showCommentForm').unbind("click");
        $('.showCommentForm').click(function(){
           var data = $(this).data();
           showCommentForm(data.id);
        });
    }
    
    // app/View/Elements/comments.ctp 
    var initOnCommentListing = function(){
        $("#comments li").hover(
            function () {
		$(this).contents('.cross-icon').show();
            }, 
            function () {
		$(this).contents('.cross-icon').hide();
            }
	);

        mooBehavior.initMoreResults();
       
        // init like activity
        mooLike.initLikeActivity();
        
        // bind action remove item comment
        initRemoveItemComment();
        
        // bind action edit item comment
        initEditItemComment();
    }
    
    // app/View/Elements/comment_form.ctp
    var initOnCommentForm = function(){
        // init to show coment button
        initShowCommentBtn();
        
        // bind action post comment button in detail item
        $('.shareButton').unbind("click");
        $('.shareButton').click(function(){
            var data = $(this).data();
            ajax_postComment(data.id);
        });
        
    }
    
    // app/View/Comments/ajax_share.ctp
    var initRemoveItemComment = function(){
        $('.removeItemComment').unbind("click");
        $('.removeItemComment').click(function(){
            var data = $(this).data();
            removeItemComment(data.id, data.photoComment);
        });
    }
    
    // app/View/Comments/ajax_share.ctp
    var initEditItemComment = function(){
        $('.editItemComment').unbind("click");
        $('.editItemComment').click(function(){
            var data = $(this).data();
            editItemComment(data.id, data.photoComment);
        });
    }
    
    var initRemoveActivityComment = function(){
        $('.removeActivityComment').unbind("click");
        $('.removeActivityComment').click(function(){
            var data = $(this).data();
            removeActivityComment(data.activityCommentId);
        });
    }
    
    var initEditActivityComment = function(){
        $('.editActivityComment').unbind("click");
        $('.editActivityComment').click(function(){
            var data = $(this).data();
            editActivityComment(data.activityCommentId);
        });
    }
    
    var editActivityComment = function(comment_id){
        if (activity_comment_edit_array.indexOf(comment_id) === -1)
	{
            $.post(mooConfig.url.base + '/activities/ajax_loadActivityCommentEdit/'+ comment_id, function(data){
                $('#activity_feed_comment_text_'+comment_id).hide();
                $(data).insertAfter($('#activity_feed_comment_text_'+comment_id));
                mooAttach.registerAttachCommentEdit('activity',comment_id);
                activity_comment_edit_array.push(comment_id);
                $('textarea:not(.no-grow)').autogrow();

                //user mention
                mooMention.init($(data).find('textarea').attr('id'),'edit_activity');

                //user emoji
                mooEmoji.init($(data).find('textarea').attr('id'),'edit_activity');
            });
        }
    };
    
    var removeActivityComment = function(id)
    {
        $.fn.SimpleModal({
            btn_ok: mooPhrase.__('btn_ok'),
            btn_cancel: mooPhrase.__('cancel'),
            callback: function(){
                $.post(mooConfig.url.base + '/activities/ajax_removeComment', {id: id}, function() {
                    $('#comment_'+id).fadeOut('normal', function() {
                        $('#comment_'+id).remove();
                    });
                });
            },
            title: mooPhrase.__('please_confirm'),
            contents: mooPhrase.__('please_confirm_remove_this_activity'),
            model: 'confirm', 
            hideFooter: false, 
            closeButton: false
        }).showModal();
    };
    
    // app/View/Comments/ajax_load_comment_edit.ctp
    var initOnAjaxLoadCommentEdit = function(){
        
        $('textarea:not(.no-grow)').autogrow();
        
        initCancelEditItemComment();
        
        initConfirmEditItemComment();
        
        initRemovePhotoComment();
    }
    
    // app/View/Activities/ajax_load_activity_comment_edit.ctp
    var initOnAjaxLoadActivityCommentEdit = function(){
        
        initCancelEditActivityComment();
        
        initConfirmEditActivityComment();
        
        initRemovePhotoComment();
    }
    
    var initRemovePhotoComment = function(){
        $('.removePhotoComment').unbind('click');
        $('.removePhotoComment').on('click', function(){
            var data = $(this).data();
            removePhotoComment(data.type, data.id);
        });
    }
    
    var initCancelEditActivityComment = function(){
        // init button cancel cancelEditItemComment
        $('.cancelEditActivityComment').unbind('click');
        $('.cancelEditActivityComment').click(function(){
            var data = $(this).data();
            cancelEditActivityComment(data.id);
        });
    }
    
    var initConfirmEditActivityComment = function(){
        // init button confirm confirmEditItemComment
        $('.confirmEditActivityComment').unbind('click');
        $('.confirmEditActivityComment').click(function(){
            var data = $(this).data();
            confirmEditActivityComment(data.id);
        });
    }
    
    var initCancelEditItemComment = function(){
        // init button cancel cancelEditItemComment
        $('.cancelEditItemComment').unbind('click');
        $('.cancelEditItemComment').click(function(){
            var data = $(this).data();
            cancelEditItemComment(data.id, data.photoComment);
        });
    }
    
    var initConfirmEditItemComment = function(){
        // init button confirm confirmEditItemComment
        $('.confirmEditItemComment').unbind('click');
        $('.confirmEditItemComment').click(function(){
            var data = $(this).data();
            confirmEditItemComment(data.id, data.photoComment);
        });
    }
    
    var ajax_postComment = function(id){
        
        if ($.trim($('#postComment').val()) != '' || $.trim($('#theaterPhotoComment').val()) != '' || $('#comment_image_' + id).val() != '')
        {
            if (mooConfig.comment_sort_style === '1'){
                $('.shareButton').addClass('disabled');
                $('.shareButton').append('<i class="icon-refresh icon-spin"></i>');
                var commentFormSerialize = '';
                if ($('#commentForm').length){
                    commentFormSerialize = $("#commentForm").serialize();
                }

                if ($('#theaterPhotoCommentForm').length){
                    commentFormSerialize = $("#theaterPhotoCommentForm").serialize();
                }
                $.post(mooConfig.url.base + "/comments/ajax_share", commentFormSerialize, function(data){

                    $('.shareButton').removeClass('disabled');
                    $('.shareButton i').remove();
                    $('.commentForm').css('height', '35px');

                    if ($('#postComment').length){
                        $('#postComment').val("");
                    }

                    if ($('#theaterPhotoComment').length){
                        $('#theaterPhotoComment').val("");
                    }

                    if (data != '')
                    {
                        if ($('#theaterComments').length){
                            $('#theaterComments').append(data);
                        }
                        else {
                            $('#comments').append(data);
                        }

                        $('.slide').slideDown();
                        if (!$('#theaterComments').length){                	
                                $("#comment_count").html( parseInt($("#comment_count").html()) + 1 );
                        }


                        $('#comment_preview_image_' + id).html('');
                        $('#comment_image_' + id).val('');
                        $('#comment_button_attach_'+id).show();
                        mooBehavior.registerImageComment();

                        //reset mention
                        var textArea = $("#postComment");
                        mooMention.resetMention(textArea);
                        var theaterPhotoComment = $("#theaterPhotoComment");
                        mooMention.resetMention(theaterPhotoComment);
                        mooTooltip.init();
                    }
                });
            }else{
                $('.shareButton').addClass('disabled');
                $('.shareButton').prepend('<i class="icon-refresh icon-spin"></i>');
                var commentFormSerialize = '';
                if ($('#commentForm').length){
                    commentFormSerialize = $("#commentForm").serialize();
                }

                if ($('#theaterPhotoCommentForm').length){
                    commentFormSerialize = $("#theaterPhotoCommentForm").serialize();
                }
                $.post(mooConfig.url.base + "/comments/ajax_share", commentFormSerialize, function(data){

                    $('.shareButton').removeClass('disabled');
                    $('.shareButton i').remove();
                    $('.commentForm').css('height', '35px');

                    if ($('#postComment').length){
                        $('#postComment').val("");
                    }

                    if ($('#theaterPhotoComment').length){
                        $('#theaterPhotoComment').val("");
                    }

                    if (data != '')
                    {
                        if ($('#theaterComments').length){
                            $('#theaterComments').prepend(data);
                        }
                        else {
                            $('#comments').prepend(data);
                        }

                        $('.slide').slideDown();
                        if (!$('#theaterComments').length){                	
                                $("#comment_count").html( parseInt($("#comment_count").html()) + 1 );
                        }


                        $('#comment_preview_image_' + id).html('');
                        $('#comment_image_' + id).val('');
                        $('#comment_button_attach_'+id).show();
                        mooBehavior.registerImageComment();

                        //reset mention
                        var textArea = $("#postComment");
                        mooMention.resetMention(textArea);
                        var theaterPhotoComment = $("#theaterPhotoComment");
                        mooMention.resetMention(theaterPhotoComment);
                        mooTooltip.init();
                    }
                });
            }

        }else{
            $.fn.SimpleModal({
                btn_ok : mooPhrase.__('btn_ok'),
                btn_ok : mooPhrase.__('btn_ok'),
                model: 'modal',
                title: mooPhrase.__('warning'),
                contents: mooPhrase.__('comment_empty')
            }).showModal();
        }
    };
    
    var cancelEditActivityComment =function(comment_id){
        //destroy overlay instance;
        if($("#message_activity_comment_edit_"+comment_id).siblings('.textoverlay')){
            $("#message_activity_comment_edit_"+comment_id).destroyOverlayInstance($("#message_activity_comment_edit_"+comment_id));
        }

	$('#activity_feed_comment_text_'+comment_id).show();
	$('#activity_comment_edit_'+comment_id).remove();
	
	var index = $.inArray(comment_id, activity_comment_edit_array);
	activity_comment_edit_array.splice(index, 1);
    };
    
    var confirmEditActivityComment = function(comment_id){
        if ($.trim($('#message_activity_comment_edit_'+comment_id).val()) != '' || $('#activity_comment_attach_id_'+comment_id).val() != '')
	{
            var messageVal;
            
            if($("#message_activity_comment_edit_"+comment_id+"_hidden").length != 0){
                messageVal = $("#message_activity_comment_edit_"+comment_id+"_hidden").val();
            }else{
                messageVal = $("#message_activity_comment_edit_"+comment_id).val()
            }
            
            $.post(mooConfig.url.base + '/activities/ajax_editActivityComment/'+ comment_id,{'comment_attach': $('#activity_comment_attach_id_'+comment_id).val() ,message: messageVal}, function(data){
                //destroy overlay instance;
                if($("#message_activity_comment_edit_"+comment_id).siblings('.textoverlay')){
                    $("#message_activity_comment_edit_"+comment_id).destroyOverlayInstance($("#message_activity_comment_edit_"+comment_id));
                }

                $('#activity_feed_comment_text_'+comment_id).html($(data).html());
                $('#history_activity_comment_'+comment_id).show();
                mooBehavior.registerImageComment();
                cancelEditActivityComment(comment_id);
            });
        }
    };
    
    var removeItemComment = function(id, isTheaterMode){
        
        $.fn.SimpleModal({
            btn_ok: mooPhrase.__('btn_ok'),
            btn_cancel: mooPhrase.__('btn_cancel'),
            callback: function(){
                $.post(mooConfig.url.base + '/comments/ajax_remove', {id: id}, function() {
                    $('#itemcomment_'+id).fadeOut('normal', function() {
                        $('#itemcomment_'+id).remove();
                        if(isTheaterMode != '0'){
                            $('#comment_count').html( parseInt($('#comment_count').html()) - 1 );
                        }
                        else
                        {
                            $('#photo_comment_'+id).remove();
                        }
                    });
                });
            },
            title: mooPhrase.__('please_confirm'),
            contents: mooPhrase.__('confirm_delete_comment'),
            model: 'confirm', 
            hideFooter: false, 
            closeButton: false
        }).showModal();
    };
    
    var editItemComment = function(comment_id, photoComment){
        
        if (item_comment_edit_array.indexOf(comment_id) === -1)
	{
            var isPhotoComment = 0;
            
            if(photoComment != '0'){
                isPhotoComment = 1;
            }
            
            $.post(mooConfig.url.base + '/comments/ajax_loadCommentEdit/'+ comment_id,{isPhotoComment:isPhotoComment} ,function(data){
                
                var item_feed_id = '#item_feed_comment_text_';
                if(photoComment != '0'){
                    item_feed_id = '#photo_feed_comment_text_';
                }
                
                $(item_feed_id+comment_id).hide();
                $(data).insertAfter($(item_feed_id+comment_id));
                
                mooAttach.registerAttachCommentEdit('item',comment_id);
                
                item_comment_edit_array.push(comment_id);
                $('textarea:not(.no-grow)').autogrow();
                
                //user mention
                mooMention.init($(data).find('textarea').attr('id'),'edit_activity');
                
                //user emoji
                mooEmoji.init($(data).find('textarea').attr('id'));
                
            });
        }
    };
    
    var cancelEditItemComment = function(comment_id, isPhotoComment){
        //destroy overlay instance;
        if($('#message_item_comment_edit_'+comment_id).siblings('.textoverlay')){
            $('#message_item_comment_edit_'+comment_id).destroyOverlayInstance($('#message_item_comment_edit_'+comment_id));
        }
        
        var item_feed_id = '#item_feed_comment_text_';
        
        if(isPhotoComment == 1){
            item_feed_id = '#photo_feed_comment_text_';
        }
        
        $(item_feed_id+comment_id).show();
        $('#item_comment_edit_'+comment_id).remove();

        var index = $.inArray(comment_id, item_comment_edit_array);
        item_comment_edit_array.splice(index, 1);
    };
    
    var confirmEditItemComment = function(comment_id, isPhotoComment){
        if ($.trim($('#message_item_comment_edit_'+comment_id).val()) != '' || $('#item_comment_attach_id_'+comment_id).val() != '')
	{
            var messageVal;
            
            if($("#message_item_comment_edit_"+comment_id+"_hidden").length != 0){
                messageVal = $("#message_item_comment_edit_"+comment_id+"_hidden").val();
            }else{
                messageVal = $("#message_item_comment_edit_"+comment_id).val()
            }
            
            $.post(mooConfig.url.base + '/comments/ajax_editComment/'+ comment_id,{'comment_attach': $('#item_comment_attach_id_'+comment_id).val() ,message: messageVal}, function(data){
                //destroy overlay instance;
                if($('#message_item_comment_edit_'+comment_id).siblings('.textoverlay')){
                    $('#message_item_comment_edit_'+comment_id).destroyOverlayInstance($('#message_item_comment_edit_'+comment_id));
                }

                $('#item_feed_comment_text_'+comment_id).html($(data).html());
                $('#photo_feed_comment_text_'+comment_id).html($(data).html());
                $('#history_item_comment_' + comment_id).show();
                $('#history_activity_comment_' + comment_id).show();
                mooBehavior.registerImageComment();
                cancelEditItemComment(comment_id, isPhotoComment);
            });
	}
    };
    
    var removePhotoComment = function(type,id){
        $('#'+type+'_comment_attach_id_'+id).val('');
	$('#'+type+'_comment_preview_attach_'+id).html('');
	$('#'+type+'_comment_attach_'+id).show();
    };
    
    var showCommentForm = function(activity_id)
    {
        $("#comments_"+activity_id).show();
        $("#newComment_"+activity_id).show();

        $('#commentForm_'+activity_id).focus();
        $('#commentForm_'+activity_id).focus();
    };
    
    var showCommentButton = function(activity_id)
    {
        $("#commentButton_"+activity_id).show();
        if($('#commentForm_'+activity_id).length != 0 && $('#commentForm_'+activity_id).siblings('input.messageHidden').length == 0){
            
            // init mooMention
            mooMention.init('commentForm_'+activity_id);
            
            // init mooEmoji
            mooEmoji.init('commentForm_'+activity_id);
        }
    };
    
    return {
        ajax_postComment : ajax_postComment,
        initEditActivityComment : initEditActivityComment,
        cancelEditActivityComment : cancelEditActivityComment,
        confirmEditActivityComment : confirmEditActivityComment,
        editItemComment : editItemComment,
        cancelEditItemComment : cancelEditItemComment,
        confirmEditItemComment : confirmEditItemComment,
        removePhotoComment : removePhotoComment,
        initShowCommentBtn : initShowCommentBtn,
        initShowCommentForm : initShowCommentForm,
        initOnCommentForm : initOnCommentForm,
        initOnCommentListing : initOnCommentListing,
        initOnAjaxLoadCommentEdit : initOnAjaxLoadCommentEdit,
        initRemoveItemComment : initRemoveItemComment,
        initEditItemComment : initEditItemComment,
        showCommentButton : showCommentButton,
        initRemoveActivityComment : initRemoveActivityComment,
        initOnAjaxLoadActivityCommentEdit : initOnAjaxLoadActivityCommentEdit
    }
}));