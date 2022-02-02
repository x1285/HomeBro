<?php

class Notification extends HasId {

	protected $text;
    protected $title;
    protected $classification;

	function __construct($text, $title, $classification, $id = null){
	    parent::__construct($id);
		$this->text = $text;
		$this->title = $title;
		$this->classification = $classification;
	}

	function getCssClass(){
		return $this->classification;
	}

	function getTitle(){
		return $this->title;
	}

	function getText(){
		return $this->text;
	}

	function printHTML(){
	    echo $this->createHTML();
    }

	function createHTML() {
        $html = "\n\t<div class=\"notification ".$this->getCssClass()."\">\n\t<div class=\"notificationtitle\">";
        $html .= $this->getTitle();
        $html .= "</div>\n\t<div class=\"notificationtext\">";
        $html .= $this->getText();
        $html .= "</div>\n</div>\n";
        return $html;
    }

    public function createJsonArray() {
        return [
            "title" => $this->getTitle(),
            "text" => $this->getText(),
            "cssClasses" => $this->getCssClass()
        ];
    }

}

?>