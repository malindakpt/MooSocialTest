<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
class MooHelper extends AppHelper {

    public $helpers = array('Time', 'Text', 'Html');

    public function getItemPhoto($obj, $options_link = array(), $options_image = array(),$linkOnly=false) {
        if (empty($obj)) {
            return null;
        }
        $prefix = '';
        $thumbUrl = null;
        if (isset($options_link['prefix'])) {
            $prefix = $options_link['prefix'] . '_';
        }

        if (key($obj) == 'User') {
        	if (!$obj['User'])
        	{
        		if ($options_link['prefix'] == '50_square') {
                    $thumbUrl = FULL_BASE_URL . $this->request->webroot . strtolower(key($obj)) . '/img/noimage/Male-' . strtolower(key($obj)) . '-sm.png';
                } else {
                    $thumbUrl = FULL_BASE_URL . $this->request->webroot . strtolower(key($obj)) . '/img/noimage/Male-' . strtolower(key($obj)) . '.png';
                }
                $obj[key($obj)]['moo_title'] = addslashes(__('No login'));
                return $this->Html->image($thumbUrl, array_merge($options_image, array('alt' => h($obj[key($obj)]['moo_title']), 'title' => Configure::read('core.profile_popup')?'':h($obj[key($obj)]['moo_title']) ) ) );
        	} 	
        }

        $field = $obj[key($obj)]['moo_thumb'];
        $thumb = $obj[key($obj)][$field];
        if ($thumb) {
            $thumbUrl = FULL_BASE_URL . $this->request->webroot . 'uploads/' . strtolower(Inflector::pluralize(key($obj))) . '/' . $field . '/' . $obj[key($obj)]['id'] . '/' . $prefix . $thumb;
        } else {
            if (key($obj) == 'User') {
            	$gender = $obj[key($obj)]['gender'];
            	if (!$gender)
            		$gender = 'Unknown';
                if ($options_link['prefix'] == '50_square') {
                    $thumbUrl = FULL_BASE_URL . $this->request->webroot . strtolower(key($obj)) . '/img/noimage/' . $gender . '-' . strtolower(key($obj)) . '-sm.png';
                } else {
                    $thumbUrl = FULL_BASE_URL . $this->request->webroot . strtolower(key($obj)) . '/img/noimage/' . $gender . '-' . strtolower(key($obj)) . '.png';
                }
            } else {
                $thumbUrl = FULL_BASE_URL . $this->request->webroot . strtolower(key($obj)) . '/img/noimage/' . strtolower(key($obj)) . '.png';
            }
        }
               
        if($linkOnly) return $thumbUrl;
        if(!isset($options_link['no_tooltip'])){
            $options_link['data-item_id'] = $obj[key($obj)]['id'];
            $options_link['data-item_type'] = strtolower(key($obj));
            $show_popup = true;
            if(key($obj) == 'User'){
                $show_popup = MooCore::getInstance()->checkViewPermission($obj[key($obj)]['privacy'],$obj[key($obj)]['id']);           
            }
            if($show_popup){
                if(isset($options_link['class']))
                    $options_link['class'] = $options_link['class'] . ' moocore_tooltip_link';
                else
                    $options_link['class'] = 'moocore_tooltip_link';
            }        
        }
        return $this->Html->link($this->Html->image($thumbUrl, array_merge($options_image, array('alt' => h($obj[key($obj)]['moo_title']) , 'title' => Configure::read('core.profile_popup')?'':h($obj[key($obj)]['moo_title']) ) ) ), FULL_BASE_URL . $obj[key($obj)]['moo_href'], array_merge($options_link, array('escape' => false)));
    }

    public function getItemLink($obj, $options = array()) {
        if (empty($obj)) {
            return null;
        }
        return $this->Html->link($obj[key($obj)]['moo_title'], FULL_BASE_URL . $obj[key($obj)]['moo_href'], $options);
    }

    public function getImageUrl($obj, $options = array()) {
        $prefix = '';
        $url = null;
        if (isset($options['prefix'])) {
            $prefix = $options['prefix'] . '_';
        }

        $field = $obj[key($obj)]['moo_thumb'];
        $thumb = $obj[key($obj)][$field];
        if ($thumb) {
            if (isset($obj[key($obj)]['year_folder']) && $obj[key($obj)]['year_folder']){ // hacking for MOOSOCIAL-2771 
                $year = date('Y', strtotime($obj[key($obj)]['created']));
                $month = date('m', strtotime($obj[key($obj)]['created']));
                $day = date('d', strtotime($obj[key($obj)]['created']));
                $url = FULL_BASE_URL . $this->request->webroot . "uploads/photos/thumbnail/$year/$month/$day/" . $obj[key($obj)]['id'] . '/' . $prefix . $obj[key($obj)]['thumbnail'];
            }else{
                $url = FULL_BASE_URL . $this->request->webroot . 'uploads/' . strtolower(Inflector::pluralize(key($obj))) . '/' . $field . '/' . $obj[key($obj)]['id'] . '/' . $prefix . $thumb;
            }
            
        } else {
            if (key($obj) == 'User') {
            	$gender = $obj[key($obj)]['gender'];
            	if (!$gender)
            		$gender = 'Unknown';
                if (isset($options['prefix']) && $options['prefix'] == '50_square') {
                    $url = FULL_BASE_URL . $this->request->webroot . strtolower(key($obj)) . '/img/noimage/' . $gender . '-' . strtolower(key($obj)) . '-sm.png';
                } else {
                    $url = FULL_BASE_URL . $this->request->webroot . strtolower(key($obj)) . '/img/noimage/' . $gender . '-' . strtolower(key($obj)) . '.png';
                }
            } else {
                $url = FULL_BASE_URL . $this->request->webroot . strtolower(key($obj)) . '/img/noimage/' . strtolower(key($obj)) . '.png';
            }
        }

        return $url;
    }

    public function getImage($obj, $options = array()) {
        return $this->Html->image($this->getImageUrl($obj, $options), $options);
    }

    public function getUserPicture($pic = '', $thumb = true, $gender = 'Male') {
    	if (!$gender)
    		$gender = 'Unknown';
    		
        if ($pic)
            return $this->request->webroot . 'uploads/avatars/' . $pic;
        elseif ($thumb)
            return $this->request->webroot . 'img/' . $gender . '-no-avatar-sm.png';
        else
            return $this->request->webroot . 'img/' . $gender . '-no-avatar.png';
    }

    public function getUserImage($oUser = array(), $options = array()) {
        $prefix = '';

        if (isset($options['prefix'])) {
            $prefix = $options['prefix'] . '_';
        }

        if ($oUser['avatar']) {
            $url = FULL_BASE_URL . $this->request->webroot . 'uploads/users/avatar/' . $oUser['id'] . '/' . $prefix . $oUser['avatar'];
        } else {
            $url = FULL_BASE_URL . $this->request->webroot . 'img/' . $oUser['gender'] . '-no-avatar.png';
        }

        $classImage = isset($options['class']) ? $options['class'] : '';
        $idImage = isset($options['id']) ? $options['id'] : '';

        return $this->Html->image($url, $options);
    }

    public function getName($userdata, $bold = true, $blank = false) {
        if (!empty($userdata)) {
            $name = h($this->Text->truncate($userdata['name'], 30));

            $url = $userdata['moo_href'];
            
            $target = null;
            if ($blank){
                $target = 'target="_blank"';
            }
            
            $class = '';
            $moo_type = isset($userdata['moo_type'])?$userdata['moo_type']:'';
            if($moo_type == 'User' && !isset($userdata['no_tooltip'])){
                $show_popup = MooCore::getInstance()->checkViewPermission($userdata['privacy'],$userdata['id']);
                if($show_popup){
                    $class = 'moocore_tooltip_link';
                }
            }

            if ($bold)
                return '<a ' . $target . ' href="' . $url . '" class="' . $class . '" data-item_type="' . strtolower($moo_type) . '" data-item_id="' . $userdata['id'] . '"><b>' . $name . '</b></a>';
            else
                return '<a ' . $target . ' href="' . $url . '" class="' .  $class  . '" data-item_type="' . strtolower($moo_type) . '" data-item_id="' . $userdata['id'] . '">' . $name . '</a>';
        }
    }

    public function getNameWithoutUrl($userdata, $bold = true) {
        if (!empty($userdata)) {
            $name = h($this->Text->truncate($userdata['name'], 30));

            if ($bold)
                return '<b>' . $name . '</b>';
            else
                return $name;
        }
    }

    public function getProfileUrl($userdata) {
        if (!empty($userdata)) {
            $url = $userdata['moo_href'];
            return $url;
        }
    }

    public function getItemPicture($obj, $type, $thumb = false) {
        $prefix = '';
        if ($thumb)
            $prefix = 't_';

        if (!empty($obj['photo']))
            return $this->request->webroot . 'uploads/' . $type . '/' . $prefix . $obj['photo'];
        else
            return $this->request->webroot . 'img/' . $prefix . 'no-image-' . $type . '.jpg';
    }

    public function getAlbumCover($cover = '') {
        if ($cover && file_exists(WWW_ROOT . $cover))
            return $this->request->webroot . $cover;
        else
            return $this->request->webroot . 'img/no-image.jpg';
    }

    public function getTime($time, $format = '%B %d at %I:%M %p', $timezone = 'UTC') {
        // TODO:[pending] add option interval (+4 day) in backend
        return $this->Time->timeAgoInWords($time, array('end' => '+4 day', 'format' => $format, 'timezone' => $timezone));
    }

    public function formatText($text, $truncate = false, $parse_smilies = true, $options = array()) {
        $text = preg_replace('/(\r?\n){2,}/', "\n\n", $text);

        if (!$truncate)
            $text = nl2br(str_replace('&amp;', '&', h($text)));
        else
            $text = nl2br(str_replace('&amp;', '&', h($this->Text->truncate($text, $truncate))));


        $text = $this->Text->autoLink($text, array_merge(array('target' => '_blank', 'rel' => 'nofollow', 'escape' => false),$options));

        if ($parse_smilies)
            $text = $this->parseSmilies($text);

        return $text;
    }

    public function cleanHtml($text) {
        $text = strip_tags($text, '<span><ul><ol><li><img><a><p><br><b><i><u><strong><em><sub><sup><div><blockquote><iframe><hr><table><tr><td><h1><h2><h3><h4><h5><h6>');
        $text = $this->parseSmilies($text);

        return $text;
    }

    public function parseSmilies($text) {
        $text = str_replace(
        array(
            ']:)', ':(', ':D', '(cool)', ':O', ';(', '(:|', ':|' ,'(kiss)', ':P',
            ';)', ':$', ':^)', '|-)', '(inlove)',':)', '(yawn)','(puke)','(angry)','(wasntme)',
            '(worry)', ':x','(devil)', '(angel)', '(envy)', '(meh)','(rofl)','(happy)','(smirk)','(beer)',
            '(clap)','(sun)','(flex)','(n)','(y)','(ok)','(punch)','(*)','(car)','(poop)','(um)','(^)',
            '(d)','(f)','(mad)','(silly)','(flu)','(ex)','(pained)','(cup)','(m)','(candy)','(chicken)',
            '(cow)','(dog)','(hih)','(e)','(bike)','(time)','(u)','(slow)','(eat)','(corn)',

            ':evil:', ':sad:', ':laugh:', ':cool:', ':surprised:', ':crying:', ':sweating:', ':speechless:' ,':kiss:', ':cheeky:',
            ':wink:', ':blushing:', ':wondering:', ':sleepy:', ':inlove:',':smile:', ':yawn:',':puke:',':angry:',':wasntme:',
            ':worry:', ':love:',':devil:', ':angel:', ':envy:', ':meh:',':rofl:',':happy:',':smirk:',':beer:',
            ':clap:',':sun:',':flex:',':no:',':yes:',':ok:',':punch:',':star:',':car:',':poop:',':umbrella:',':cake:',
            ':drink:',':football:',':mad:',':silly:',':flu:',':excited:',':pained:',':cup:',':music:',':candy:',':chicken:',
            ':cow:',':dog:',':hih:',':email:',':bike:',':time:',':brokenheart:',':slow:',':eat:',':corn:'

        ), array(
            '<span title="evil" id="a48" class="iconos"></span>',
            '<span title="sad" id="a20" class="iconos"></span>',
            '<span title="Laugh" id="a1" class="iconos"></span>',
            '<span title="Cool" id="a41" class="iconos"></span>',
            '<span title="Surprised" id="a50" class="iconos"></span>',
            '<span title="Crying" id="a24" class="iconos"></span>',
            '<span title="Sweating" id="a29" class="iconos"></span>',
            '<span title="Speechless" id="a52" class="iconos"></span>',
            '<span title="Kiss" id="a8" class="iconos"></span>',
            '<span title="Cheeky" id="a39" class="iconos"></span>',
            '<span title="Wink" id="a6" class="iconos"></span>',
            '<span title="Blushing" id="a4" class="iconos"></span>',
            '<span id="a47" class="iconos"></span>',
            '<span title="Sleepy" id="a42" class="iconos"></span>',
            '<span title="in love" id="a7" class="iconos"></span>',
            '<span title="smile" id="a3" class="iconos"></span>',
            '<span title="yawn" id="a43" class="iconos"></span>',
            '<span title="puke" id="a37" class="iconos"></span>',
            '<span title="angry" id="a35" class="iconos"></span>',
            '<span title="wasntme" id="a15" class="iconos"></span>',
            '<span title="worry" id="a33" class="iconos"></span>',
            '<span id="a8" class="iconos"></span>',
            '<span id="a74" class="iconos"></span>',
            '<span title="angel" id="a72" class="iconos"></span>',
            '<span title="envy" id="a19" class="iconos"></span>',
            '<span title="meh" id="a53" class="iconos"></span>',
            '<span title="rofl" id="a23" class="iconos"></span>',
            '<span id="a18" class="iconos"></span>',
            '<span id="a57" class="iconos"></span>',
            '<span id="a77" class="iconos"></span>',
            '<span id="a76" class="iconos"></span>',
            '<span id="a2" class="iconos"></span>',
            '<span id="a5" class="iconos"></span>',
            '<span id="a9" class="iconos"></span>',
            '<span id="a10" class="iconos"></span>',
            '<span id="a11" class="iconos"></span>',
            '<span id="a12" class="iconos"></span>',
            '<span id="a13" class="iconos"></span>',
            '<span id="a14" class="iconos"></span>',
            '<span title="poop" id="a16" class="iconos"></span>',
            '<span title="umbrella" id="a17" class="iconos"></span>',
            '<span title="cake" id="a21" class="iconos"></span>',
            '<span title="drink" id="a22" class="iconos"></span>',
            '<span title="football" id="a25" class="iconos"></span>',
            '<span title="mad" id="a26" class="iconos"></span>',
            '<span title="Silly" id="a27" class="iconos"></span>',
            '<span title="Flu" id="a28" class="iconos"></span>',
            '<span title="Excited" id="a30" class="iconos"></span>',
            '<span title="pained" id="a31" class="iconos"></span>',
            '<span title="cup" id="a32" class="iconos"></span>',
            '<span title="music" id="a34" class="iconos"></span>',
            '<span title="candy" id="a36" class="iconos"></span>',
            '<span title="chicken" id="a38" class="iconos"></span>',
            '<span title="cow" id="a40" class="iconos"></span>',
            '<span title="dog" id="a44" class="iconos"></span>',
            '<span title="hand in hand" id="a45" class="iconos"></span>',
            '<span title="Mail" id="a46" class="iconos"></span>',
            '<span title="Bike" id="a49" class="iconos"></span>',
            '<span title="Time" id="a50" class="iconos"></span>',
            '<span title="Broken heart" id="a51" class="iconos"></span>',
            '<span title="slow" id="a54" class="iconos"></span>',
            '<span title="eat" id="a55" class="iconos"></span>',
            '<span title="corn" id="a56" class="iconos"></span>',

                '<span title="evil" id="a48" class="iconos"></span>',
                '<span title="sad" id="a20" class="iconos"></span>',
                '<span title="Laugh" id="a1" class="iconos"></span>',
                '<span title="Cool" id="a41" class="iconos"></span>',
                '<span title="Surprised" id="a50" class="iconos"></span>',
                '<span title="Crying" id="a24" class="iconos"></span>',
                '<span title="Sweating" id="a29" class="iconos"></span>',
                '<span title="Speechless" id="a52" class="iconos"></span>',
                '<span title="Kiss" id="a8" class="iconos"></span>',
                '<span title="Cheeky" id="a39" class="iconos"></span>',
                '<span title="Wink" id="a6" class="iconos"></span>',
                '<span title="Blushing" id="a4" class="iconos"></span>',
                '<span id="a47" class="iconos"></span>',
                '<span title="Sleepy" id="a42" class="iconos"></span>',
                '<span title="in love" id="a7" class="iconos"></span>',
                '<span title="smile" id="a3" class="iconos"></span>',
                '<span title="yawn" id="a43" class="iconos"></span>',
                '<span title="puke" id="a37" class="iconos"></span>',
                '<span title="angry" id="a35" class="iconos"></span>',
                '<span title="wasntme" id="a15" class="iconos"></span>',
                '<span title="worry" id="a33" class="iconos"></span>',
                '<span id="a8" class="iconos"></span>',
                '<span id="a74" class="iconos"></span>',
                '<span title="angel" id="a72" class="iconos"></span>',
                '<span title="envy" id="a19" class="iconos"></span>',
                '<span title="meh" id="a53" class="iconos"></span>',
                '<span title="rofl" id="a23" class="iconos"></span>',
                '<span id="a18" class="iconos"></span>',
                '<span id="a57" class="iconos"></span>',
                '<span id="a77" class="iconos"></span>',
                '<span id="a76" class="iconos"></span>',
                '<span id="a2" class="iconos"></span>',
                '<span id="a5" class="iconos"></span>',
                '<span id="a9" class="iconos"></span>',
                '<span id="a10" class="iconos"></span>',
                '<span id="a11" class="iconos"></span>',
                '<span id="a12" class="iconos"></span>',
                '<span id="a13" class="iconos"></span>',
                '<span id="a14" class="iconos"></span>',
                '<span title="poop" id="a16" class="iconos"></span>',
                '<span title="umbrella" id="a17" class="iconos"></span>',
                '<span title="cake" id="a21" class="iconos"></span>',
                '<span title="drink" id="a22" class="iconos"></span>',
                '<span title="football" id="a25" class="iconos"></span>',
                '<span title="mad" id="a26" class="iconos"></span>',
                '<span title="Silly" id="a27" class="iconos"></span>',
                '<span title="Flu" id="a28" class="iconos"></span>',
                '<span title="Excited" id="a30" class="iconos"></span>',
                '<span title="pained" id="a31" class="iconos"></span>',
                '<span title="cup" id="a32" class="iconos"></span>',
                '<span title="music" id="a34" class="iconos"></span>',
                '<span title="candy" id="a36" class="iconos"></span>',
                '<span title="chicken" id="a38" class="iconos"></span>',
                '<span title="cow" id="a40" class="iconos"></span>',
                '<span title="dog" id="a44" class="iconos"></span>',
                '<span title="hand in hand" id="a45" class="iconos"></span>',
                '<span title="Mail" id="a46" class="iconos"></span>',
                '<span title="Bike" id="a49" class="iconos"></span>',
                '<span title="Time" id="a50" class="iconos"></span>',
                '<span title="Broken heart" id="a51" class="iconos"></span>',
                '<span title="slow" id="a54" class="iconos"></span>',
                '<span title="eat" id="a55" class="iconos"></span>',
                '<span title="corn" id="a56" class="iconos"></span>',
        ), $text);
        return $text;
    }
    
    public function getTimeZoneByKey($key = 'Pacific/Kwajalein'){
        $listTimezone = $this->getTimeZones();
        return $listTimezone[$key];
    }

    public function getTimeZones() {
        $timezones = array(
            'Pacific/Kwajalein' => '(GMT-12:00) Kwajalein',
            'Pacific/Midway' => '(GMT-11:00) Midway Island',
            'Pacific/Samoa' => '(GMT-11:00) Samoa',
            'Pacific/Honolulu' => '(GMT-10:00) Hawaii',
            'America/Anchorage' => '(GMT-09:00) Alaska',
            'America/Los_Angeles' => '(GMT-08:00) Pacific Time',
            'America/Tijuana' => '(GMT-08:00) Tijuana, Baja California',
            'America/Denver' => '(GMT-07:00) Mountain Time',
            'America/Chihuahua' => '(GMT-07:00) Chihuahua',
            'America/Mazatlan' => '(GMT-07:00) Mazatlan',
            'America/Phoenix' => '(GMT-07:00) Arizona',
            'America/Regina' => '(GMT-06:00) Saskatchewan',
            'America/Tegucigalpa' => '(GMT-06:00) Central America',
            'America/Chicago' => '(GMT-06:00) Central Time',
            'America/Mexico_City' => '(GMT-06:00) Mexico City',
            'America/Monterrey' => '(GMT-06:00) Monterrey',
            'America/New_York' => '(GMT-05:00) Eastern Time',
            'America/Bogota' => '(GMT-05:00) Bogota',
            'America/Lima' => '(GMT-05:00) Lima',
            'America/Rio_Branco' => '(GMT-05:00) Rio Branco',
            'America/Indiana/Indianapolis' => '(GMT-05:00) Indiana (East)',
            'America/Caracas' => '(GMT-04:30) Caracas',
            'America/Halifax' => '(GMT-04:00) Atlantic Time',
            'America/Manaus' => '(GMT-04:00) Manaus',
            'America/Santiago' => '(GMT-04:00) Santiago',
            'America/La_Paz' => '(GMT-04:00) La Paz',
            'America/St_Johns' => '(GMT-03:30) Newfoundland',
            'America/Argentina/Buenos_Aires' => '(GMT-03:00) Georgetown',
            'America/Sao_Paulo' => '(GMT-03:00) Brasilia',
            'America/Godthab' => '(GMT-03:00) Greenland',
            'America/Montevideo' => '(GMT-03:00) Montevideo',
            'Atlantic/South_Georgia' => '(GMT-02:00) Mid-Atlantic',
            'Atlantic/Azores' => '(GMT-01:00) Azores',
            'Atlantic/Cape_Verde' => '(GMT-01:00) Cape Verde Is.',
            'Europe/Dublin' => '(GMT) Dublin',
            'Europe/Lisbon' => '(GMT) Lisbon',
            'Europe/London' => '(GMT) London',
            'Africa/Monrovia' => '(GMT) Monrovia',
            'Atlantic/Reykjavik' => '(GMT) Reykjavik',
            'Africa/Casablanca' => '(GMT) Casablanca',
            'Europe/Belgrade' => '(GMT+01:00) Belgrade',
            'Europe/Bratislava' => '(GMT+01:00) Bratislava',
            'Europe/Budapest' => '(GMT+01:00) Budapest',
            'Europe/Ljubljana' => '(GMT+01:00) Ljubljana',
            'Europe/Prague' => '(GMT+01:00) Prague',
            'Europe/Sarajevo' => '(GMT+01:00) Sarajevo',
            'Europe/Skopje' => '(GMT+01:00) Skopje',
            'Europe/Warsaw' => '(GMT+01:00) Warsaw',
            'Europe/Zagreb' => '(GMT+01:00) Zagreb',
            'Europe/Brussels' => '(GMT+01:00) Brussels',
            'Europe/Copenhagen' => '(GMT+01:00) Copenhagen',
            'Europe/Madrid' => '(GMT+01:00) Madrid',
            'Europe/Paris' => '(GMT+01:00) Paris',
            'Africa/Algiers' => '(GMT+01:00) West Central Africa',
            'Europe/Amsterdam' => '(GMT+01:00) Amsterdam',
            'Europe/Berlin' => '(GMT+01:00) Berlin',
            'Europe/Rome' => '(GMT+01:00) Rome',
            'Europe/Stockholm' => '(GMT+01:00) Stockholm',
            'Europe/Vienna' => '(GMT+01:00) Vienna',
            'Europe/Minsk' => '(GMT+02:00) Minsk',
            'Africa/Cairo' => '(GMT+02:00) Cairo',
            'Europe/Helsinki' => '(GMT+02:00) Helsinki',
            'Europe/Riga' => '(GMT+02:00) Riga',
            'Europe/Sofia' => '(GMT+02:00) Sofia',
            'Europe/Tallinn' => '(GMT+02:00) Tallinn',
            'Europe/Vilnius' => '(GMT+02:00) Vilnius',
            'Europe/Athens' => '(GMT+02:00) Athens',
            'Europe/Bucharest' => '(GMT+02:00) Bucharest',
            'Europe/Istanbul' => '(GMT+02:00) Istanbul',
            'Asia/Jerusalem' => '(GMT+02:00) Jerusalem',
            'Asia/Amman' => '(GMT+02:00) Amman',
            'Asia/Beirut' => '(GMT+02:00) Beirut',
            'Africa/Windhoek' => '(GMT+02:00) Windhoek',
            'Africa/Harare' => '(GMT+02:00) Harare',
            'Asia/Kuwait' => '(GMT+03:00) Kuwait',
            'Asia/Riyadh' => '(GMT+03:00) Riyadh',
            'Asia/Baghdad' => '(GMT+03:00) Baghdad',
            'Africa/Nairobi' => '(GMT+03:00) Nairobi',
            'Asia/Tbilisi' => '(GMT+03:00) Tbilisi',
            'Europe/Moscow' => '(GMT+03:00) Moscow',
            'Europe/Volgograd' => '(GMT+03:00) Volgograd',
            'Asia/Tehran' => '(GMT+03:30) Tehran',
            'Asia/Muscat' => '(GMT+04:00) Muscat',
            'Asia/Baku' => '(GMT+04:00) Baku',
            'Asia/Yerevan' => '(GMT+04:00) Yerevan',
            'Asia/Yekaterinburg' => '(GMT+05:00) Ekaterinburg',
            'Asia/Karachi' => '(GMT+05:00) Karachi',
            'Asia/Tashkent' => '(GMT+05:00) Tashkent',
            'Asia/Kolkata' => '(GMT+05:30) Calcutta',
            'Asia/Colombo' => '(GMT+05:30) Sri Jayawardenepura',
            'Asia/Katmandu' => '(GMT+05:45) Kathmandu',
            'Asia/Dhaka' => '(GMT+06:00) Dhaka',
            'Asia/Almaty' => '(GMT+06:00) Almaty',
            'Asia/Novosibirsk' => '(GMT+06:00) Novosibirsk',
            'Asia/Rangoon' => '(GMT+06:30) Yangon (Rangoon)',
            'Asia/Krasnoyarsk' => '(GMT+07:00) Krasnoyarsk',
            'Asia/Bangkok' => '(GMT+07:00) Bangkok',
            'Asia/Jakarta' => '(GMT+07:00) Jakarta',
            'Asia/Brunei' => '(GMT+08:00) Beijing',
            'Asia/Chongqing' => '(GMT+08:00) Chongqing',
            'Asia/Hong_Kong' => '(GMT+08:00) Hong Kong',
            'Asia/Urumqi' => '(GMT+08:00) Urumqi',
            'Asia/Irkutsk' => '(GMT+08:00) Irkutsk',
            'Asia/Ulaanbaatar' => '(GMT+08:00) Ulaan Bataar',
            'Asia/Kuala_Lumpur' => '(GMT+08:00) Kuala Lumpur',
            'Asia/Singapore' => '(GMT+08:00) Singapore',
            'Asia/Taipei' => '(GMT+08:00) Taipei',
            'Australia/Perth' => '(GMT+08:00) Perth',
            'Asia/Seoul' => '(GMT+09:00) Seoul',
            'Asia/Tokyo' => '(GMT+09:00) Tokyo',
            'Asia/Yakutsk' => '(GMT+09:00) Yakutsk',
            'Australia/Darwin' => '(GMT+09:30) Darwin',
            'Australia/Adelaide' => '(GMT+09:30) Adelaide',
            'Australia/Canberra' => '(GMT+10:00) Canberra',
            'Australia/Melbourne' => '(GMT+10:00) Melbourne',
            'Australia/Sydney' => '(GMT+10:00) Sydney',
            'Australia/Brisbane' => '(GMT+10:00) Brisbane',
            'Australia/Hobart' => '(GMT+10:00) Hobart',
            'Asia/Vladivostok' => '(GMT+10:00) Vladivostok',
            'Pacific/Guam' => '(GMT+10:00) Guam',
            'Pacific/Port_Moresby' => '(GMT+10:00) Port Moresby',
            'Asia/Magadan' => '(GMT+11:00) Magadan',
            'Pacific/Fiji' => '(GMT+12:00) Fiji',
            'Asia/Kamchatka' => '(GMT+12:00) Kamchatka',
            'Pacific/Auckland' => '(GMT+12:00) Auckland',
            'Pacific/Tongatapu' => '(GMT+13:00) Nukualofa'
        );

        return $timezones;
    }

    public function logo() {
        $event = $this->_View->getEventManager()->dispatch(new CakeEvent('mooHelper.getLogo', $this));
        if ($event->result)
        {
        	return $event->result;
        }
        $settingModel = MooCore::getInstance()->getModel('Setting');
        $logoSetting = $settingModel->find('first', array('conditions' => array('Setting.name' => 'logo')));
        
        $logo = Configure::read('core.logo');
        if (DS != '/') {
            $logo = str_replace(DS, '/', $logo);
        }

        if ($logoSetting['Setting']['value'] != $logoSetting['Setting']['value_actual']){ // logo uploaded in admincp
            return $this->request->webroot . $logo;
        }else { // default theme logo
            $currentTheme = $this->theme;
            if ($currentTheme == 'default'){
                return $this->request->webroot . 'img/logo.png';
            }else{
                if (file_exists(WWW_ROOT . 'theme' . DS . $currentTheme . DS . 'img' . DS . 'logo.png')){
                    return $this->request->webroot . "theme/$currentTheme/img/logo.png";
                }else {
                    return $this->request->webroot . 'img/logo.png';
                }
            }
        }
    }
    
    public function defaultCoverUrl(){
        $currentTheme = $this->theme;
        if ($currentTheme == 'default'){
            return $this->request->webroot . 'img/cover.jpg';
        }else{
            if (file_exists(WWW_ROOT . 'theme' . DS . "'" . $currentTheme . "'" . DS . 'img' . DS . 'cover.jpg')){
                return $this->request->webroot . "theme/$currentTheme/img/cover.jpg";
            }else {
                return $this->request->webroot . 'img/cover.jpg';
            }
        }
    }

    public function loggedIn() {
        return !empty($this->_View->viewVars['uid']);
    }

    ////////////////////////////////////////////menu////////////////////////////////////////////
    public function renderMenu($pluginName, $active_item, $type = 'admin', $menu_type = 'tab') {
        $menuHtml = '';
        switch ($type) {
            case 'admin':
                $menuHtml = $this->adminMenu($pluginName, $active_item, $menu_type);
                break;
        }
        return $menuHtml;
    }

    private function adminMenu($pluginName, $active_item, $menu_type = 'tab') {
        App::import("Model", "Plugin");
        $this->Plugin = new Plugin();

        //load plugin menu
        $pluginPath = APP . 'Plugin' . DS . $pluginName . DS . $pluginName . 'Plugin.php';
        $menus = array();
        if (file_exists($pluginPath)) {
            require_once($pluginPath);
            $classname = $pluginName . 'Plugin';
            if (class_exists($classname)) {
                $cl = new $classname();
                if (method_exists($classname, 'menu')) {
                    $menus = $cl->menu();
                }
            }
        }

        //render menu
        $menuHtml = '';
        if ($menus != null) {
            switch ($menu_type) {
                case 'tab':
                    $menuHtml .= '<ul class="nav nav-tabs list7 chart-tabs">';
                    foreach ($menus as $k => $menu) {
                        $active = '';
                        if ($active_item == $k) {
                            $active = 'class="active"';
                        }
                        $menuHtml .= '<li ' . $active . '>' . $this->Html->Link($k, $menu) . '</li>';
                    }
                    $menuHtml .= '</ul>';
                    break;
                case 'vertical':
                    $plugin = $this->Plugin->findByKey($pluginName);
                    $active = '';
                    if ($active_item != '') {
                        $active = 'class="active"';
                    }
                    $menuHtml .= '<li ' . $active . '>' . $this->Html->Link('<i class="spicon-puzzle"></i> ' . __d(Inflector::underscore($plugin['Plugin']['key']),$plugin['Plugin']['name']), reset($menus), array('escape' => false)) . '</li>';
                    break;
            }
        }
        return $menuHtml;
    }

    // check Social Integration is Enable
    public function socialIntegrationEnable($provider = 'facebook') {
        if (!Configure::read(ucfirst($provider) . 'Integration.' . $provider . '_integration')) {
            return false;
        }

        if (!Configure::read(ucfirst($provider) . 'Integration.' . $provider . '_app_id')) {
            return false;
        }

        if (!Configure::read(ucfirst($provider) . 'Integration.' . $provider . '_app_secret')) {
            return false;
        }

        if (!Configure::read(ucfirst($provider) . 'Integration.' . $provider . '_app_return_url')) {
            return false;
        }

        return true;
    }
    
    public function isRecaptchaEnabled(){
        return MooCore::getInstance()->isRecaptchaEnabled();
    }
    
    public function getRecaptchaPublickey(){
        return Configure::read('core.recaptcha_publickey');
    }

    public function isCheckAllRole($current_role = null){
        
        if (!empty($current_role)){
            $roleModel = MooCore::getInstance()->getModel('Role');
            $roles = $roleModel->find('list', array('fields' => array('Role.id')));
            
            if (json_decode($current_role) == array_values($roles)){
                return true;
            }
        }
        
        return false;
    }
    
    public function isNotificationStop($item_id, $item_type){
        $viewer_id = MooCore::getInstance()->getViewer(true);
        $notificationStopModel = MooCore::getInstance()->getModel('NotificationStop');
        return $notificationStopModel->isNotificationStop($item_id, $item_type, $viewer_id);
    }
    
    public function getGenderTxt($gender = 'Male'){
        if ($gender == 'Male'){
            echo __('Male');
        }else if ($gender == 'Female'){
            echo __('Female');
        }else if($gender == 'Unknown'){
            echo __('Unspecified');
        }
    }
    
    public function getGenderList(){
        $gender = array(
                'Male' => __('Male'), 
                'Female' => __('Female') 
                );
        
        if (Configure::read('core.enable_unspecified_gender')){
           $gender['Unknown'] = __('Unspecified');
        }

        return $gender;
    }
    
    // MOOSOCIAL-2789 hacking to support translate for : Cover Pictures, Profile Pictures, Newsfeed Photos album
    public function getAlbumTitle($album_title = null){
        if ($album_title == 'Cover Pictures'){
            echo __("Cover Pictures");
        }else if ($album_title == 'Profile Pictures'){
            echo __("Profile Pictures");
        }
        else if ($album_title == 'Newsfeed Photos'){
            echo __("Newsfeed Photos");
        }else{
            echo $album_title;
        }
    }
    
    public function getItemSitemMap($name,$limit,$offset)
    {
    	$userModel = MooCore::getInstance()->getModel("User");
    	$users = $userModel->find('all',array(
    		'limit' => $limit,
    		'offset' => $offset
    	));
    	
    	$urls = array();
    	foreach ($users as $user)
    	{
    		$urls[] = FULL_BASE_URL.$user['User']['moo_href'];
    	}
    	
    	return $urls;
    }

    public function checkProfileField($type,$field,$data)
    {
        if ($type == 'country')
        {
            if ($field['ProfileField']['required'])
            {

                if (!isset($data['country_id']) || !$data['country_id'])
                {
                    return __('Country is required');
                }
            }
        }

        return false;
    }

    public function saveProfileField($type,$field,$data,$uid)
    {
        if ($type == 'country')
        {
            $userCountryModel = MooCore::getInstance()->getModel("UserCountry");
            $userCountryModel->updateData($uid, $data);
        }
    }

    public function queryProfileField($type,&$params,$data)
    {
        if ($type == 'country')
        {
            if (isset($data['country_id']) && $data['country_id'])
            {
                $userCountryModel = MooCore::getInstance()->getModel("UserCountry");
                if (isset($data['state_id']) && $data['state_id']) {
                    $conditions =  array(
                        'UserCountry.user_id = User.id',
                        'UserCountry.country_id' => $data['country_id'],
                        'UserCountry.state_id' => $data['state_id']
                    );
                }
                else {
                    $conditions =  array(
                        'UserCountry.user_id = User.id',
                        'UserCountry.country_id' => $data['country_id'],
                    );
                }
                $tmp = array('table' => $userCountryModel->tablePrefix . 'user_countries',
                    'alias' => 'UserCountry',
                    'type' => 'INNER',
                    'conditions' => $conditions
                );
                if (!isset($params['joins'])) {
                    $params['joins'] = array(
                        $tmp
                    );
                } else {
                    $params['joins'][] = $tmp;
                }
            }
        }
    }
    
    public function convertDescriptionMeta($description, $length = 40)
    {
    	if (!$description)
    	{
    		return '';
    	}
    	
    	if (str_word_count($description) > $length)
    	{
    		return $this->subwords($description,$length);
    	}
    	
    	return $description;
    }
    
	function subwords( $str, $max = 24, $char = ' ', $end = '' ) {
	    $str = trim( $str ) ;
	    $str = $str . $char ;
	    $len = strlen( $str ) ;
	    $words = '' ;
	    $w = '' ;
	    $c = 0 ;
	    for ( $i = 0; $i < $len; $i++ )
	        if ( $str[$i] != $char )
	            $w = $w . $str[$i] ;
	        else
	            if ( ( $w != $char ) and ( $w != '' ) ) {
	                $words .= $w . $char ;
	                $c++ ;
	                if ( $c >= $max ) {
	                    break ;
	                }
	                $w = '' ;
	            }
	    if ( $i+1 >= $len ) {
	        $end = '' ;
	    }
	    return trim( $words ) . $end ;
	}
}

?>
