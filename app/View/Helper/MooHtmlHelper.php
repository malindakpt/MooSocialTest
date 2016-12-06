<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('HtmlHelper', 'View/Helper');
class MooHtmlHelper extends HtmlHelper{
    private $isActiveRequirejs= false;
    private $scriptBlockOptions = array();
    public function __construct(View $View, $settings = array()) {
        if ($View instanceof MooView) {
            $this->isActiveRequirejs = $View->isEnableJS("Requirejs");
        }

        parent::__construct($View,$settings);
        $this->html['viewMore']=<<<HTML
<div class="view-more">
        <a href="javascript:void(0)" class="viewMoreBtn" data-url="%s" data-div="%s">%s</a>
</div>
HTML;
        
        $this->html['viewMoreChrono']=<<<HTML
<div class="view-more-chrono">
        <a href="javascript:void(0)" class="viewMoreBtn" data-url="%s" data-div="%s">%s</a>
</div>
HTML;
        
    }
    private $html = array();
    private $isLoaded = array(
        'javascript'=>array(
            'behavior.js'=>false,
	    'rating.js' => false
        )
    );

    public function link($title, $url = null, $options = array(), $confirmMessage = false) {
		if (isset($options['no_replace_ssl']) && $options['no_replace_ssl'])
    	{
    		return parent::link($title, $url, $options, $confirmMessage);
    	}
        $ssl = Configure::read('core.ssl_mode');
        if(!empty($ssl) && env('HTTPS')){ // env('HTTPS') check enable https server
            $url = str_replace('http:', 'https:', $url);
        }
        return parent::link($title, $url, $options, $confirmMessage);
    }
    public function css($path, $options = array())
    {
        if (Configure::read('debug') == 0 && (!isset($this->_View->request->params['prefix']) || $this->_View->request->params['prefix'] != 'admin')){
            if (!isset($options['minify']) || $options['minify'])
            {
                $this->_View->Helpers->Minify->addPath($path);
                return;
            }
        }
        $ssl = Configure::read('core.ssl_mode');
        if(!empty($ssl)){
            $path = str_replace('http:', 'https:', $path);
        }
        if (!is_array($options)) {
            $rel = $options;
            $options = array();
            if ($rel) {
                $options['rel'] = $rel;
            }
            if (func_num_args() > 2) {
                $options = func_get_arg(2) + $options;
            }
            unset($rel);
        }

        return parent::css($path, $options);
    }
    public function scriptStart($options = array()) {
        $requires = array("jquery");
        $object = array("$");
        if(!empty($options['requires'])){
            $requires = $options['requires'];
            unset($options['requires']);
        }

        if(!empty($options['object'])){
            $object = $options['object'];
            unset($options['object']);
        }

        parent::scriptStart($options);
        if($this->isActiveRequirejs){
            $scriptStart = '';
            
            $scriptStart .= "require(".json_encode($requires).", function(".implode(",",$object).") {";
            
            $this->scriptBlockOptions = $options;
            
            if(isset($options['domReady']) && $options['domReady']){
                $scriptStart .= '$(document).ready(function(){';
            }
            
            echo $scriptStart;
        }

    }
    public function scriptEnd() {
        if($this->isActiveRequirejs){
            $out = '';
            
            $options = $this->scriptBlockOptions;
            if(isset($options['domReady']) && $options['domReady']){
                $out .= "});";
            }
            
            $out .= "});";
            
            echo $out;
        }
        parent::scriptEnd();
    }
    public function script($url, $options = array()) {
        if($this->isActiveRequirejs){

            if($url == 'moocore/require.js')
                return parent::script($url, $options);
        }else{
            return parent::script($url, $options);
        }

    }
    public function viewMore($moreUrl,$div='list-content',$text=null,$autoEcho=true){
        if(!$this->isLoaded['javascript']['behavior.js']){
            $this->isLoaded['javascript']['behavior.js'] = true;
        }

        if(empty($text)) $text= addslashes(__( 'View More'));
        $html = sprintf($this->html['viewMore'],$moreUrl, $div, $text);
        if($autoEcho){
            echo $html;
        }else{
            return $html;
        }
    }
    
    public function viewMoreChrono($moreUrl,$div='list-content',$text=null,$autoEcho=true){
        if(!$this->isLoaded['javascript']['behavior.js']){
            $this->isLoaded['javascript']['behavior.js'] = true;
        }

        if(empty($text)) $text= addslashes(__( 'View More'));
        $html = sprintf($this->html['viewMoreChrono'],$moreUrl, $div, $text);
        if($autoEcho){
            echo $html;
        }else{
            return $html;
        }
    }
    
    public function ajaxLink($title, $url = null, $options = array()){

    }
    public function rating($itemId,$tableItem,$plugin = null,$options = array(), $autoEcho = true){
        return false;
        $ratingSettingModel = MooCore::getInstance()->getModel('RatingSetting');
        $ratingSettings = $ratingSettingModel->find('all');
        $ratingSettings = Hash::combine($ratingSettings,'{n}.RatingSetting.name','{n}.RatingSetting.value');

        $path = WWW_ROOT . 'img' . DS . 'rating_skins' . DS;
        $skin = $ratingSettings['skin'];
        if(file_exists($path.$ratingSettings['skin']))
            $skin = 'rating_skins/'.$ratingSettings['skin'];
        $defaultOptions = array(
            'skin'=> $skin,
            'rating_system' => $ratingSettings['rating_system'],
            'step' => 1
        );

        $allowReRating = $ratingSettings['re_rating'];
        $enableRatingList = json_decode($ratingSettings['enable_rating'],true);
        $enableRatingList = array_keys(array_filter($enableRatingList));
        $rating_options = $defaultOptions;
        if(is_array($options))
            $rating_options = array_merge($defaultOptions,$options);
        $ratingModel = MooCore::getInstance()->getModel('Rating');
        $ratings = null;
        if(in_array($tableItem,$enableRatingList)){

            if(!$this->isLoaded['javascript']['rating.js']){
                $this->isLoaded['javascript']['rating.js'] = true;
                $this->_View->requireJs('moocore/rating.js','var allow_re_rating = '.$allowReRating.'; mooRating.init(allow_re_rating);');
            }

            $ratings = $ratingModel->getRatings(array('Rating.type' => $tableItem, 'Rating.type_id' => $itemId, 'Rating.plugin' => $plugin));
            $rating_options += array('ratings'=>$ratings,'type' => $tableItem, 'type_id' => $itemId, 'plugin' => $plugin);
            if($autoEcho){
                echo $this->_View->element('rating/rating',$rating_options);
            }else{
                return $this->_View->element('rating/rating',$rating_options);
            }
        }
    }
}