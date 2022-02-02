<?php

require_once("Action.class.php");

abstract class HttpAction extends Action {

	private $curl;
	private $response;

	private $withResponse = true;
	private $withFollowLocation = true;

	private $unformattedUrl = ""; /* URL ENCODED! */

	/* '%variablename' => 'value' - URL ENCODED! */
	private $variables = [];

	private $httpHeaders;

	public function __construct($id, $name, $unformattedUrl, $httpHeaders = []) {
		parent::__construct($id, $name);
		$this->setUnformattedUrl($unformattedUrl);
		$this->setHttpHeaders($httpHeaders);
	}

	/* not final: Overridden @HttpPostAction, @OpenHabRESTAction, @HttpGetStateQueryAction  */
	protected function before() {
		curl_setopt($this->getCurl(), CURLOPT_RETURNTRANSFER, $this->withResponse);
		curl_setopt($this->getCurl(), CURLOPT_FOLLOWLOCATION, $this->withFollowLocation);
		curl_setopt($this->getCurl(), CURLOPT_HTTPHEADER, $this->getHttpHeaders());

	}

	/* not final: Overridden @HttpGetStateQueryAction  */
	protected function main(){
		curl_setopt($this->curl, CURLOPT_URL, $this->getFormattedUrl());
		$this->response = curl_exec($this->curl);
		echo $this->response;
	}

	final protected function after(){
		curl_close($this->getCurl());
	}

	public function getFormattedUrl(){
		$formattedVars = array();
		foreach ($this->variables as $key => $value) {
			$formattedVars[$key] = urlencode($value);
		}
		return str_replace(array_keys($formattedVars), array_values($formattedVars), $this->unformattedUrl);
	}

	public function setVariable($key, $stringValue) { //unencoded String values!
		$this->addVariable($key, $stringValue);
	}

	public function addVariable($key, $stringValue) { //unencoded String values!
		$this->variables[$key] = $stringValue;
	}

	public function setVariables($variables) {
		$this->variables = $variables;
	}

	public function getVariables() {
		return $this->variables;
	}

	public function setHttpHeader($header) {
		$this->addHttpHeader($header);
	}

	public function addHttpHeader($header) { //unencoded String values!
		$this->httpHeaders[] = $header;
	}

	public function setHttpHeaders($httpHeaders) {
		$this->httpHeaders = $httpHeaders;
	}

	public function getHttpHeaders() {
		return $this->httpHeaders;
	}

	public function setUnformattedUrl($unformattedUrl){
		$this->unformattedUrl = $unformattedUrl;
	}

	public function getUnformattedUrl(){
		return $this->unformattedUrl;
	}

	public function getResponse() {
		return $this->response;
	}

	public function setWithResponse($withResponse = true) {
		$this->withResponse = $withResponse;
	}

	public function setWithFollowLocation($withFollowLocation = true) {
		$this->withFollowLocation = $withFollowLocation;
	}

	public function getCurl(){
		if ($this->curl === null) {
			$this->curl = curl_init();
		}
		return $this->curl;
	}

	public function setCurlOption($optionname, $optionvalue) {
		curl_setopt($this->getCurl(), $optionname, $optionvalue);
	}

}

?>