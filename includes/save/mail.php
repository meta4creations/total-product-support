<?php
	
function tops_mail_test() {
	
	$notification_emails = 'joe@metaphorcreations.com';
	$email = 'jradive@gmail.com';
	$name = 'Joe';
	$subject = sprintf(__('User review for %s', 'daveaude'), $mix_title);
	$body = "<h1>Test</h1>
	<p>
	<strong>".__('Rating', 'daveaude').":</strong> 5<br/>
	<strong>".__('Email', 'daveaude').":</strong> ".$email."<br/>
	<strong>".__('Name', 'daveaude').":</strong> ".$name."<br/>
	<strong>".__('Comment', 'daveaude').":</strong> My Comments
	</p>
	<p>
	<strong>".__('View all ratings for this mix', 'daveaude').":</strong>
	</p>
	";
	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: daveaude.com <ratings@daveaude.com>',
		'Reply-To: '.$name.' <'.$email.'>'
	);

	// Admin email
	$admin_mail_success = wp_mail($notification_emails, $subject, $body, $headers);
	if( $admin_mail_success ) {
		
	}
	
}