<?php
function hashUrl($url) {
	return hash('ripemd160', strval($url));
}

function objectToArray($object) {
    return (array)$object;
}

function sqlInject($value) {
	$value=str_replace('\\\\', '', $value);
	$value=str_replace("'", "\'", $value);
	$value=str_replace('"', '\"', $value);
	$value=stripslashes($value);
	return $value;
}

function dateToIso($date) {
	$datetime = new DateTime($date);
	return $datetime->format(DateTime::ATOM);
}

?>
