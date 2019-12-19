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
	public $meta = false;
	public $success = 0;

	private $errors = [];

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

	public function setFrom($from = EMAIL['INFO'])
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

	public function send($attachments = false, $meta = false)
	{
		if($attachments) $this->attachments = $attachments;
		if($meta) $this->meta = $meta;

		$this->sanatize_message();
		$this->validate_message();

		if($this->errors) return false;

		$this->compose_message($attachments, $meta);
		
		$this->success = true;
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

	public function error_log()
	{
		return $this->errors;
	}


	private function compose_message($attachments, $meta)
	{
		$file = $path.$filename;
		$content = file_get_contents( $file);
		$content = chunk_split(base64_encode($content));
		$uid = md5(uniqid(time()));
		$filename = basename($file);

		// header
		$header = "From: ".$from_name." <".$from_mail.">\r\n";
		$header .= "Reply-To: ".$replyto."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";

		// message & attachment
		$nmessage = "--".$uid."\r\n";
		$nmessage .= "Content-type:text/plain; charset=iso-8859-1\r\n";
		$nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$nmessage .= $message."\r\n\r\n";
		$nmessage .= "--".$uid."\r\n";
		$nmessage .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n";
		$nmessage .= "Content-Transfer-Encoding: base64\r\n";
		$nmessage .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
		$nmessage .= $content."\r\n\r\n";
		$nmessage .= "--".$uid."--";
	}

	private function parse_attachment()
	{
		$file = $path.$filename;
		$content = file_get_contents( $file);
		$content = chunk_split(base64_encode($content));
		$uid = md5(uniqid(time()));
		$filename = basename($file);
	}

	private function set_headers()
	{
		$header = "From: ".$from_name." <".$from_mail.">\r\n";
		$header .= "Reply-To: ".$replyto."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
	}

	private function fetch_attachments()
	{

	}

	private function determine_mime_type()
	{

	}

	private function sanatize_message()
	{

	}

	private function validate_message()
	{

	}

	$file = $path.$filename;
	$content = file_get_contents( $file);
	$content = chunk_split(base64_encode($content));
	$uid = md5(uniqid(time()));
	$filename = basename($file);

	// header
	$header = "From: ".$from_name." <".$from_mail.">\r\n";
	$header .= "Reply-To: ".$replyto."\r\n";
	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";

	// message & attachment
	$nmessage = "--".$uid."\r\n";
	$nmessage .= "Content-type:text/plain; charset=iso-8859-1\r\n";
	$nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$nmessage .= $message."\r\n\r\n";
	$nmessage .= "--".$uid."\r\n";
	$nmessage .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n";
	$nmessage .= "Content-Transfer-Encoding: base64\r\n";
	$nmessage .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
	$nmessage .= $content."\r\n\r\n";
	$nmessage .= "--".$uid."--";

	if (mail($mailto, $subject, $nmessage, $header)) {
	    return true; // Or do something here
	} else {
	  return false;
	}
}