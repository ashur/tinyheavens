<?php

/*
 * This file is part of Bot
 */
namespace Bot;

use Cranberry\Bot\Slack;
use Cranberry\CLI\Command;

class Exception extends \Exception
{
	/**
	 * @param	string							$message
	 * @param	int								$code
	 * @param	string							$slackWebhook
	 * @param	Cranberry\Bot\Slack\Message		$slackMessage
	 * @param	Cranberry\Bot\Slack\Attachment	$slackAttachment
	 */
	public function __construct( $message, $code=0, $slackWebook, Slack\Message $slackMessage, Slack\Attachment $slackAttachment )
	{
		if( $slackWebook != null )
		{
			$slack = new Slack\Slack();

			$slackAttachment->addField( 'Message', $message, true );
			$slackMessage->addAttachment( $slackAttachment );

			$slack->postMessage( $slackWebook, $slackMessage );
		}

		throw new Command\CommandInvokedException( $message, $code );
	}
}
