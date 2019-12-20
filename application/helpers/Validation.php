<?php

namespace application\helpers;


class Validation {

	public static function checkLength($value, $maxLength, $minLength = 0) 
	{
		if (!(strlen($value) > $maxLength) && !(strlen($value) < $minLength)) {
			return true;
		} else {
			return false;
		}
	}

	public static function compare($value1, $value2, $caseSensitive = false) 
	{
		if ($caseSensitive) {
			return ($value1 == $value2 ? true : false);
		} else {
			if (strtoupper($value1) == strtoupper($value2)) {
				return true;
			} else {
				return false;
			}
		}
	}

	public static function contains($string, $find, $caseSensitive = true) 
	{
		if (strlen($find) == 0) {
			return true;
		} else {
			if ($caseSensitive) {
				return (strpos($string, $find) !== false);
			} else {
				return (strpos(strtoupper($string), strtoupper($find)) !== false);
			}
		}
	}

	public static function convertToDate($date, $timezone = TIMEZONE, $forceFixDate = true) 
	{
		if ($date instanceof DateTime) {
			return $date;
		} else {
			date_default_timezone_set($timezone);

			$timestamp = strtotime($date);

			if ($timestamp) {
				$date = DateTime::createFromFormat('U', $timestamp);
			} else {
				$date = false;
			}

			return $date;
		}
	}

	public static function getAge($dob, $timezone = TIMEZONE) 
	{
		$date     = self::convertToDate($dob, $timezone);
		$now      = new DateTime();
		$interval = $now->diff($date);
		return $interval->y;
	}

	public static function getDefaultOnEmpty($value, $default) 
	{
		if (self::hasValue($value)) {
			return $value;
		} else {
			return $default;
		}
	}

	public static function hasArrayKeys($array, $required_keys, $keys_case = false) 
	{
		$valid = true;
		if (!is_array($array)) {
			$valid = false;
		} else {
			foreach ($required_keys as $key) {
				if ($keys_case == CASE_UPPER) {
					if (!array_key_exists(strtoupper($key), $array)) {
						$valid = false;
					}
				} elseif ($keys_case == CASE_LOWER) {
					if (!array_key_exists(strtolower($key), $array)) {
						$valid = false;
					}
				} else {
					if (!array_key_exists($key, $array)) {
						$valid = false;
					}
				}
			}
		}
		return $valid;
	}

	public static function hasValue($value) 
	{
		return !(self::isEmpty($value));
	}

	public static function isOfAge($age, $legal = 18) 
	{
		return self::getAge($age) < $legal ? false : true;
	}

	public static function isAlpha($value, $allow = '') 
	{
		if (preg_match('/^[a-zA-Z' . $allow . ']+$/', $value)) {
			return true;
		} else {
			return false;
		}
	}

	public static function isAlphaNumeric($value) 
	{
		if (preg_match('/^[A-Za-z0-9 ]+$/', $value)) {
			return true;
		} else {
			return false;
		}
	}

	public static function isEmail($email) 
	{
		$pattern = '/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/';

		if (preg_match($pattern, $email)) {
			return true;
		} else {
			return false;
		}
	}

	public static function isEmpty($value) 
	{
		if (!isset($value)) {
			return true;
		} elseif (is_null($value)) {
			return true;
		} elseif (is_string($value) && strlen($value) == 0) {
			return true;
		} elseif (is_array($value) && count($value) == 0) {
			return true;
		} else {
			return false;
		}
	}

	public static function isFloat($number) 
	{
		if (is_float($number)) {
			return true;
		} else {
			$pattern = '/^[-+]?(((\\\\d+)\\\\.?(\\\\d+)?)|\\\\.\\\\d+)([eE]?[+-]?\\\\d+)?$/';
    		return (!is_bool($number) &&
				(is_float($number) || preg_match($pattern, trim($number))));
		}
	}

	public static function isInternetURL($value) 
	{
		if (preg_match('/^http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?$/i', $value)) {
			return true;
		} else {
			return false;
		}
	}

	public static function isIPAddress($value) 
	{
		$pattern = '/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.'
			. '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.'
			. '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.'
			. '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/i';
		if (preg_match($pattern, $value)) {
			return true;
		} else {
			return false;
		}
	}

	public static function isNumber($number) 
	{
		if (preg_match('/^\-?\+?[0-9e1-9]+$/', $number)) {
			return true;
		} else {
			return false;
		}
	}

	public static function isTooLong($value, $maximumLength) 
	{
		if (strlen($value) > $maximumLength) {
			return true;
		} else {
			return false;
		}
	}

	public static function isTooShort($value, $minimumLength) 
	{
		if (strlen($value) < $minimumLength) {
			return true;
		} else {
			return false;
		}
	}

	public static function isValidCreditCardNumber($cardnumber) 
	{
		$number    = preg_replace('/[^0-9]/i', '', $cardnumber);
		$length    = strlen($number);
		$revNumber = strrev($number);

		// calculate checksum.. just don't touch
		$sum       = '';
		for ($i = 0; $i < $length; $i++) {
			$sum .= $i & 1 ? $revNumber[$i] * 2 : $revNumber[$i];
		}

		return array_sum(str_split($sum)) % 10 === 0;
	}

	public static function isValidJSON($string) 
	{
		@json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	public static function sanitize($input) 
	{
		$search = array(
			'@<script[^>]*?>.*?</script>@si',   // Strip out javascript
			'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
			'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
			'@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
		);
		return preg_replace($search, '', $input);
	}

	public static function stripExcessWhitespace($string) 
	{
		return preg_replace('/  +/', ' ', $string);
	}

	public static function stripNonAlpha($string) 
	{
		return preg_replace('/[^a-z]/i', '', $string);
	}

	public static function stripNonAlphaHyphenSpaces($string) 
	{
		return preg_replace('/[^a-z\- ]/i', '', $string);
	}

	public static function stripNonAlphaNumeric($string) 
	{
		return preg_replace('/[^a-z0-9]/i', '', $string);
	}

	public static function stripNonAlphaNumericHyphenSpaces($string) 
	{
		return preg_replace('/[^a-z0-9\- ]/i', '', $string);
	}

	public static function stripNonAlphaNumericSpaces($string) 
	{
		return preg_replace('/[^a-z0-9 ]/i', '', $string);
	}

	public static function stripNonNumeric($string) 
	{
		return preg_replace('/[^0-9]/', '', $string);
	}

	public static function trim($value, $mask = ' ') 
	{
		if (is_string($value)) {
			return trim($value, $mask);
		} elseif (is_null($value)) {
			return '';
		} else {
			return $value;
		}
	}

	public static function truncate($string, $length, $dots = '') 
	{
		if (strlen($string) > $length) {
			return substr($string, 0, $length - strlen($dots)) . $dots;
		} else {
			return $string;
		}
	}

	public static function truncateDecimal($float, $precision = 0) 
	{
		$pow     = pow(10, $precision);
		$precise = (int) ($float * $pow);
		return (float) ($precise / $pow);
	}

	public static function validatePassword($password, $confirm, $email = '', $username = '', $forceUpperLower = false) 
	{

		$problem = '';

		if ($password != $confirm) {
			$problem .= 'Password and confirm password fields did not match.' . "<br>\n";
		}
		if (strlen($password) < 8) {
			$problem .= 'Password must be at least 8 characters long.' . "<br>\n";
		}
		if ($email) {
			if (strpos(strtoupper($password), strtoupper($email)) !== false
				|| strpos(strtoupper($password), strtoupper(strrev($email))) !== false) {
				$problem .= 'Password cannot contain the email address.' . "<br>\n";
			}
		}
		if ($username) {
			if (strpos(strtoupper($password), strtoupper($username)) !== false
				|| strpos(strtoupper($password), strtoupper(strrev($username))) !== false) {
				$problem .= 'Password cannot contain the username (or reversed username).' . "<br>\n";
			}
		}
		if (!preg_match('#[0-9]+#', $password)) {
			$problem .= 'Password must contain at least one number.' . "<br>\n";
		}
		if ($forceUpperLower) {
			if (!preg_match('#[a-z]+#', $password)) {
				$problem .= 'Password must contain at least one lowercase letter.' . "<br>\n";
			}
			if (!preg_match('#[A-Z]+#', $password)) {
				$problem .= 'Password must contain at least one uppercase letter.' . "<br>\n";
			}
		} else {
			if (!preg_match('#[a-zA-Z]+#', $password)) {
				$problem .= 'Password must contain at least one letter.' . "<br>\n";
			}
		}

		if (strlen($problem) == 0) {
			$problem = false;
		} else ($returnArray) {
			$problem = explode("<br>\n", trim($problem, "<br>\n"));
		}

		return $problem;
	}
}