<?php

namespace application\helpers;


use application\helpers\File;
use application\helpers\Validation;

class Email 
{

	public $to;
	public $cc;
	public $bcc;
	public $from = EMAIL['INFO'];
	public $subject = "";
	public $message = "";
	public $attachments = false;
	public $success = 0;

	private $parsed_email = "";
	private $parsed_headers = "";
	private $errors = [];
	private $debug = false;
	private $mime_boundary;

	public function setTo($to)
	{
		$this->to = $to;
		return $this;
	}

	public function setCC($cc)
	{
		$this->cc = $cc;
		return $this;
	}

	public function setBCC($bcc)
	{
		$this->bcc = $bcc;
		return $this;
	}

	public function setFrom($from)
	{
		$this->from = $from;
		return $this;
	}

	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	public function send($attachments = false)
	{
		if($attachments) $this->attachments = $attachments;

		$this->flush_error_log();
		$this->sanatize_message();
		$this->validate_message();

		if(!empty($this->errors)) return false;

		$this->compose_message();

		if($this->debug){
			$this->new_error("DEBUG: check <debug> log")
		}
		
		if (mail($this->to, $this->subject, $this->parsed_email, $this->parsed_headers)) {
		    $this->success = true;
		}

		return $this->success;
	}

	public function send_later()
	{
		// TODO: send email at later date
	}

	public function save()
	{
		// TODO: save email to DB
	}

	public function debug_mode($on = false)
	{
		if($on) $this->debug = true;
		else {
			$this->debug = false;
			$this->flush_error_log();
		}
	}

	public function error_log()
	{
		return $this->errors;
	}

	private function flush_error_log()
	{
		$this->errors = [];
	}

	private function new_error($message, $debug = false)
	{
		if($debug){
			$this->errors["debug"] = [
				"sender" => $this->to,
				"subject" => $this->subject,
				"message" => $this->parsed_email,
				"headers" => $this->parsed_headers
			];
		}else array_push($this->errors, $message);
		
	}

	private function compose_message()
	{
		$uid = md5(uniqid(time()));
		$this->mime_boundary = "==Multipart_Boundary_x{$uid}x";

		$this->parse_to();
		if($this->cc || $this->bcc) $this->parse_headers();
		if($this->attachments) $this->parse_attachment();

		if($this->attachments) $this->parse_message();
		else $this->parsed_email = $this->message;
	}

	private function parse_to()
	{
		if(is_array($this->to)) implode(',', $this->to); 
	}

	private function parse_message()
	{
		$body = "This is a multi-part message in MIME format.\r\n\r\n" .
				 "--{$mime_boundary}\r\n" .
				 "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n" .
				 "Content-Transfer-Encoding: 7bit\r\n\r\n" .
				 $this->message . "\r\n\r\n" .
				 "--{$mime_boundary}\r\n";


		$this->parsed_email = $body;
	}

	private function parse_attachment()
	{
		foreach ($this->attachments as $attachment) {
			$content = file_get_contents($attachment);
			$content = chunk_split(base64_encode($content));
			$mime = mime_content_type($attachment);
			$filename = basename($file);

			$attach = 	"--{$mime_boundary}\r\n" .
						"Content-Type: {$mime};\r\n" .
			 			" name=\"{$filename}\"\r\n" .
			 			"Content-Disposition: attachment;\r\n" .
			 			" filename=\"{$filename}\"\r\n" .
			 			"Content-Transfer-Encoding: base64\r\n\r\n" .
			 			$content . "\r\n\r\n"

			$this->parsed_email .= $attach;
		}

		$this->parsed_email .= "--{$mime_boundary}\r\n" .;
	}

	private function parse_headers()
	{
		$this->headers = "From: " . $this->from['name'] . " <" . $this->from['email'] . ">" . "\r\n" ;
		$this->headers.= "Reply-To: " . $this->from['name'] . " <" . $this->from['email'] . ">" . "\r\n" ;
		if(!isEmpty($this->cc){
			foreach ($this->cc as $cc) {
				$headers.= "Cc: " . $this->cc . " <" . $this->cc . ">" . "\r\n" ;
			}
		}
		if(!isEmpty($this->bcc){
			foreach ($this->cc as $cc) {
				$headers.= "Bcc: " . $this->bcc . " <" . $this->bcc . ">" . "\r\n" ;
			}
		}

		if($this->attachments){
			$this->headers .=  	"MIME-Version: 1.0\n" .
		 						"Content-Type: multipart/mixed;\n" .
		 						" boundary=\"{$mime_boundary}\"";
		}	
	}

	private function sanatize_message()
	{
		$this->subject = Validation::sanatize($this->subject);
		$this->message = Validation::sanatize($this->message);
		$this->message = wordwrap($this->message, 70, "\r\n"); //stupid php
	}

	private function validate_message()
	{
		//check if any of the fields are empty
		if(	Validation::isEmpty($this->to) ) 		$this->new_error("To field must not be empty");
		if(	Validation::isEmpty($this->cc) ) 		$this->new_error("Cc field must not be empty");
		if(	Validation::isEmpty($this->bcc) ) 		$this->new_error("Bcc field must not be empty");
		if(	Validation::isEmpty($this->subject) ) 	$this->new_error("Subject field must not be empty");
		if(	Validation::isEmpty($this->message) ) 	$this->new_error("Message field must not be empty");
		if( Validation::isEmpty($this->from) ){
			$this->new_error("From field should contain name and email address of sender");
		}else{
			if(	Validation::isEmpty($this->from['name']))	$this->new_error("From <name> field must not be empty");
			if(	Validation::isEmpty($this->from['email']))	$this->new_error("From <email> field must not be empty");
		}	
		
		//validate email fields
		foreach ($this->to as $address) {
			if(	!Validation::isEmail($address) ) 	$this->new_error($address . " in to field is not valid email address");
		}
		foreach ($this->cc as $address) {
			if(	!Validation::isEmail($address) ) 	$this->new_error($address . " in cc field is not valid email address");
		}
		foreach ($this->bcc as $address) {
			if(	!Validation::isEmail($address) ) 	$this->new_error($address . " in bcc field is not valid email address");
		}
		if(	!Validation::isEmail($this->from['email']) )	$this->new_error($address . " in bcc field is not valid email address");

		//validate text fields
		if(	!Validation::isAlphaNumeric($this->subject) ) 	$this->new_error("Subject field should not be empty");
		if(	!Validation::isAlphaNumeric($this->message) ) 	$this->new_error("Message field should contain only alphanumeric character and spaces");

		//check if text sent is too long
		if( Validation::isTooLong($this->from['name'], 16))	$this->new_error("Sender name is too long");
		if( Validation::isTooLong($this->subject, 16)) 		$this->new_error("Subject is too long");
		if( Validation::isTooLong($this->message, 1000)) 	$this->new_error("Message is too long");
	}


}