/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'mooUser'], factory);
    } else if (typeof exports === 'object') {
        // Node, CommonJS-like
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals (root is window)
        root.mooLike = factory();
    }
}(this, function ($, mooUser) {
    
    // app/View/Elements/likes.ctp
    var initLikeItem = function(){
        $('.likeItem').unbind('click');
        $('.likeItem').click(function(){
            var data = $(this).data();
            // bind action likeIt
            likeIt(data.type, data.id, data.status);
        });
    }
    
    // bind action like activity
    var initLikeActivity = function(){
        $('.likeActivity').unbind("click");
        $('.likeActivity').click(function(){
            var data = $(this).data();
            // bind action likeActivity
            likeActivity(data.type, data.id, data.status);
        });
    }
    
    // init like on photo
    var initLikePhoto = function(){
        $('.likePhoto').unbind('click');
        $('.likePhoto').on('click', function(){
            var data = $(this).data();
            likePhoto(data.id, data.thumbUp);
        });
    }
    
    var likeIt = function(type, item_id, thumb_up){
        $.post(mooConfig.url.base + '/likes/ajax_add/' + type + '/' + item_id + '/' + thumb_up, { noCache: 1 }, function(data){
            try
            {
                var res = $.parseJSON(data);

                $('#like_count').html( parseInt(res.like_count) );
                $('#dislike_count').html( parseInt(res.dislike_count) );
                $('#like_count2').html( parseInt(res.like_count) );
                $('#dislike_count2').html( parseInt(res.dislike_count) );

                if ( thumb_up )
                {
                    $('#like_count').parent().prev().toggleClass('active');
                    $('#dislike_count').parent().prev().removeClass('active');
                }
                else
                {
                    $('#dislike_count').parent().prev().toggleClass('active');
                    $('#like_count').parent().prev().removeClass('active');
                }
            }
            catch (err)
            {
                mooUser.validateUser();
            }
        });
    };
    
    var likePhoto = function(item_id, thumb_up){
        $.post(mooConfig.url.base + '/likes/ajax_add/Photo_Photo/' + item_id + '/' + thumb_up, { noCache: 1 }, function(data){
            try
            {
                var res = $.parseJSON(data);

                $('#photo_like_count2').html( parseInt(res.like_count) );
                $('#photo_dislike_count2').html( parseInt(res.dislike_count) );

                if ( thumb_up )
                {
                    $('#photo_like_count').toggleClass('active');
                    $('#photo_dislike_count').removeClass('active');
                }
                else
                {
                    $('#photo_dislike_count').toggleClass('active');
                    $('#photo_like_count').removeClass('active');
                }
            }
            catch (err)
            {
                mooUser.validateUser();
            }
        });
    };

    var likeActivity = function(item_type, id, thumb_up){

        var type;

        if(item_type == 'photo_comment'){
            type = 'comment';
        } else{
            type = item_type;
        }

        $.post(mooConfig.url.base + '/likes/ajax_add/' + type + '/' + id + '/' + thumb_up, { noCache: 1 }, function(data){
            try
            {
                var res = $.parseJSON(data);
                $('#' + item_type + '_like_' + id).html( parseInt(res.like_count) );
                $('#' + item_type + '_dislike_' + id).html( parseInt(res.dislike_count) );
                if(item_type == 'comment'){
                    $('#photo_comment' + '_like_' + id).html( parseInt(res.like_count) );
                    $('#photo_comment' + '_dislike_' + id).html( parseInt(res.dislike_count) );
                }

                if ( thumb_up )
                {  
                    $('#' + item_type + '_l_' + id).toggleClass('active');
                    $('#' + item_type + '_d_' + id).removeClass('active');
                    if(item_type == 'comment') {
                        $('#photo_comment' +  '_l_' + id).toggleClass('active');
                        $('#photo_comment' +  '_d_' + id).removeClass('active');
                    }
                }
                else
                { 
                    $('#' + item_type + '_d_' + id).toggleClass('active');
                    $('#' + item_type + '_l_' + id).removeClass('active');
                    if(item_type == 'comment') {
                        $('#photo_comment' + '_d_' + id).toggleClass('active');
                        $('#photo_comment' + '_l_' + id).removeClass('active');
                    }
                }
            }
            catch (err)
            {
                mooUser.validateUser();
            }
        });
    };
        
    return {
        initLikeActivity : initLikeActivity,
        likePhoto : likePhoto,
        initLikeItem : initLikeItem,
        initLikePhoto : initLikePhoto
    }
    
}));