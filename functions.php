<?php
function debug($value,$die=false)
{
	echo '<pre>';
	if(is_array($value) || is_object($value))
			print_r($value);
	else
			echo $value;
	echo '</pre>';
	if($die==true)
			die;
}
?>
