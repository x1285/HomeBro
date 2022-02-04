<?php

class XhrResponseBuilder {

	private $response = [];

	public function __construct() {
	}

	static public function createFor($obj = null) {
		if (is_null($obj)) {
			die("XhrResponseBuilder needs an object as parameter");
		}
		$builder = new XhrResponseBuilder();

		if (is_a($obj, "ActionButton")) {
			$printer = new ActionButtonPrinter($obj, true);
			$cssclasses = $printer->getActiveCssClasses();
			$htmlattributes = $printer->getActiveHtmlAttributes();
			$updatebtn = [
				"cssclasses" => $cssclasses, 
				"htmlattributes" => $htmlattributes
			];
			if (!is_a($obj, "ActionButtonWithSubButtons")) {
				$innerhtml = $printer->createInnerHtml();
				$updatebtn["innerhtml"] = $innerhtml;
			}
			if ($obj instanceof StateDependingActionButton) {
				$updatebtn["state"] = $obj->getCurrentState();
			}
			$todo = ["updatebtn" => $updatebtn];
			$builder->add("todo", $todo);
		} else if (is_a($obj, "Notification")) {
		    $notification = $obj->createJsonArray();
		    if (is_a($obj,"SerializableNotification")) {
                $notification["createTimestamp"] = $obj->getCreateTimestamp()->format("Y-m-d H:i:s");
                if ($obj->getFirstPrintTimestamp() === null) {
                   $obj->setFirstPrintTimestamp(time());
                }
            }
            $builder->add("notification", $notification);
        }
		return $builder;
	}

    public function addArrayValue($varname = null, $value = null) {
        if (!is_null($varname)) {
            if (!array_key_exists($varname, $this->response)) {
                $this->response[$varname] = [$value];
            } else if (is_array($this->response[$varname])) {
                $this->response[$varname][] = $value;
            }
        }
    }

	public function add($varname = null, $value = null) {
		if (!is_null($varname)) {
			$this->response[$varname] = $value;
		}
	}

	public function setResponse($array = []){
		if (is_array($array)) {
			$this->response = $array;
		}
	}

	public function getResponse() {
		return $this->response;
	}

	public function getJson() {
		return json_encode($this->response);
	}

	public function toJson() {
		return $this->getJson();
	}

}