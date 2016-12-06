<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
class MooRequirejsHelper extends AppHelper{
    private $shim = array();
    private $paths = array();
    private $baseUrl = "js";
    private $firstRequire = array();
    private $isActive = false;
    public function __construct(View $View, $settings = array()) {
        parent::__construct($View,$settings);
        if ($View instanceof MooView) {
            $this->isActive = $this->_View->isEnableJS("Requirejs");
        }

    }
    private function pushTo($name,$values=array(),$use_array_key_exitts = true){
        if(!isset($this->{$name}))
            return false;
        if(!is_array($values))
            $values = array($values);
        foreach($values as $key=>$value){
            if($use_array_key_exitts){
                if(!array_key_exists($key,$this->{$name})){
                    $this->{$name}[$key]=$value;
                }
            }else{
                if(!in_array($value,$this->{$name}))
                    $this->{$name}[] = $value;
            }

        }
        return true;
    }
    public function addPath($paths=array()){
        return $this->pushTo('paths',$paths);
    }
    public function addShim($shims=array()){
        return $this->pushTo('shim',$shims);
    }
    public function addToFirst($js = array()){
        return $this->pushTo('firstRequire',$js,false);
    }
    public function first($autoEcho = false){
        if($autoEcho){
            echo json_encode($this->firstRequire);
        }else{
            return json_encode($this->firstRequire);
        }
    }
    public function add($paths=array(),$shims=array(),$firstRequires=array()){
        $this->addPath($paths);
        $this->addShim($shims);
        $this->registerFirstRequires($firstRequires);
    }
    public function shim($autoEcho = fasle){}
    public function paths($autoEcho = false){}
    public function config($autoEcho = false){
        $config = array(
            'baseUrl'=>$this->baseUrl,
            'shim' => $this->shim,
            'paths'=> $this->paths,
            //'enforceDefine'=>true,
        );
        // support moochat
        $config = json_encode($config);
        //$config = str_replace('"baseUrl"','config: { es6: { resolveModuleSource: function(source) { return \'es6!\'+source; } } },"baseUrl"',$config);
        // end support moochat
        if($autoEcho){
            echo str_replace('\/','/',$config);
        }else{
            return str_replace('\/','/',$config);
        }
    }
    public function assetUrlJS($path,$options = array()){
        return str_replace('.js', '', $this->assetUrl($path,$options));
    }
    public function scriptStart($autoEcho = true,$requires=array('jquery'),$object='$'){
        if(!$this->isActive) return false;
        $scriptStart = "require(".json_encode($requires).", function(".$object.") {";
        if(empty($requires)) $scriptStart="require([\"jquery\"], function($) {";

        if($autoEcho){
            echo $scriptStart;
        }else{
            return $scriptStart;
        }
    }
    public function scriptEnd($autoEcho = true){
        if(!$this->isActive) return false;
        $scriptEnd="});";
        if($autoEcho){
            echo $scriptEnd;
        }else{
            return $scriptEnd;
        }
    }
}