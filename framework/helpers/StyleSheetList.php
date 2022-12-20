<?php

    class StyleSheetList {
        public $length;
        protected $self;

        public function __construct ( ) {
            $this->self = [];
        }

        protected function __get($property) {
          $ret = false;  
          if ($property === 'length'){
              $ret = count($this->self);
            }
            return $ret;
        }

        protected function __set($property) {
            return ($property === 'length');
        }

        public function item( $index ) {
            return $this->self[$index];
        }
    } // end of class StyleSheetList

/*
    class MediaList extends StyleSheetList {

        function appendMedium ( $newMedium ) {
            array_push($this->self, $newMedium);
        }

        function deleteMedium ( $oldMedium ) {
            foreach($this->self as $item) {
                if( $item == $oldMedium ) {
                    $item = $this->self[ $this->length-1 ];
                    array_pop($this->self);
                    break;
                }
            }
        }
    }

    class DocumentStyle {
        public styleSheets;

        function __construct ( ) {
            $this->styleSheets = new StyleSheetList();
        }

        function __set($property, $val) {
            if($property == 'styleSheets')
                return true;
        }
    }

    class LinkStyle {
        public sheet;

        function __construct () {
            $this->sheet = new StyleSheet();
        }

        function __set($property, $val) {
            if($property == 'sheet')
                return true;
        }
    }

    class StyleSheet {
        public type;
        public disabled;
        public ownerNode;
        public parentStyleSheet;
        public href;
        public title;
        public media;

        function __construct( $type, $disabled, $ownerNode, $parentStyleSheet, $href, $title){
            $this->type = $type;
            $this->disabled = $disabled;
            $this->media = new MediaList();
            $this->ownerNode = $ownerNode;
            $this->parentStyleSheet = $parentStyleSheet;
            $this->href = $href;
            $this->title = $title;
        }
    }
*/