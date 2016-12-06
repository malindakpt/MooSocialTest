/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
_menu = {
    userSettings:{
        'chat-sounds':{
            phrase:'Chat Sounds',
            checked:true
        },
        'block-settings':{
            phrase:'Block Settings',
            checked:false
        },
        'close-all-tabs':{
            phrase:'Close All Chat Tabs',
            checked:false
        },
        'turn-off-chat':{
            phrase:'Turn Off Chat',
            checked:false
        }
    }
};
module.exports = {
    userSettings:function(){
        var menu = '';
        for (var key in _menu.userSettings) {
            
            if(_menu.userSettings[key].checked){
                menu += '<div><a id="userSettings-'+key+'" href="#" class="moochat_icon_checked moochat_menu_user_settings_poup"><span>'+_menu.userSettings[key].phrase+'</span></a></div>';
            }else{
                menu += '<div><a id="userSettings-'+key+'" href="#" class="moochat_menu_user_settings_poup"></span>'+_menu.userSettings[key].phrase+'</span></a></div>';
            }
        }
        return menu;

    }

};
