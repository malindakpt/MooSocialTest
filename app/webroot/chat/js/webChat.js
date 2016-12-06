(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'mooBehavior', 'mooAlert', 'mooPhrase', 'mooAjax', 'mooGlobal','bloodhound'], factory);
    } else if (typeof exports === 'object') {
        // Node, CommonJS-like
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals (root is window)
        root.mooDocument = factory();
    }
}(this, function ($,  mooBehavior, mooAlert, mooPhrase, mooAjax, mooGlobal) {
    var engine = new Bloodhound({
        initialize: false,
        local: [],
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name')
    });
    var engineBloodHoundCallback = function (users) {
        if(users.length > 0){
            $.each(users,function(index,user){
                $(".room-id-"+user.id).show();
                $("#rooms-list  > .box2").css("border","1px solid #ccc");
            });
        }
    };

    if(window.bloodhoundRawData != undefined){
        engine.clear();
        engine.add(window.bloodhoundRawData);
    }

    //  engine.search(name, engineBloodHoundCallback.bind(this), engineBloodHoundCallback.bind(this));
    var initOnMessagesPage = function(){
        $('#rooms-list > #filters >  input').keyup(function(){

            var key = $(this).val();
            if(key!=""){
                $("#rooms-list  .rooms-item").hide();
                $("#rooms-list  > .box2").css("border","none");
                engine.search(key, engineBloodHoundCallback.bind(this), engineBloodHoundCallback.bind(this));
            }else{
                $("#rooms-list  .rooms-item").show();
            }
        });
        $(".rooms-item").click(function(){
            window.location.href = $(this).data("url");
        });

    };
    var onClickedUnBlock = function(){
        $.getJSON( mooConfig.url.base+ "/chats/unblock/"+$(this).data("id"))
            .done(function( data ) {
                if(data.hasOwnProperty("users")){
                    console.log(data.users);
                    $('#blocking-userlist').html("");
                    if(data.users.length > 0){
                        for(var i=0;i<data.users.length;i++){
                            var value = data.users[i];
                            $('#blocking-userlist').append('<div class="row"> '+value.User.name+' &nbsp; <a href="#" class="chat-unblock-action" data-id="'+value.User.id+'" style=""> '+mooPhrase.__("chat_unblock")+' </a> </div>');
                        }
                        $('.chat-unblock-action').click(onClickedUnBlock);
                    }
                }
            });
    };
    var initOnBlockingPage = function(){
        $('.chat-unblock-action').show();
        $('.chat-unblock-action').click(onClickedUnBlock);
    };
    return{
        initOnMessagesPage: initOnMessagesPage,
        initOnBlockingPage:initOnBlockingPage
    }
}));