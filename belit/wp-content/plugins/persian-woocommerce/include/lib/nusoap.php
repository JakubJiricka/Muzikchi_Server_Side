<?php
if ( version_compare( PHP_VERSION, '5.3', '>' ) ) {
	include_once 'nusoap-php7.php';
} else {
	include_once 'nusoap-php5.3.php';
}