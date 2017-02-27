<?php

use Cranberry\Bot\Slack;
use Cranberry\CLI\Command;

/**
 * @name			tweet
 * @description		Post to Twitter
 * @usage			tweet
 */
$command = new Command\Command( 'tweet', 'Post to Twitter', function()
{
	$tweet = $this->bot->getTweet();

	try
	{
		$response = $this->twitter->postTweet( $tweet );
	}
	catch( Exception $e )
	{
		/* Slack */
		$slackWebhook = $this->config->getValue( 'slack', 'webhook' );

		if( $slackWebhook != null )
		{
			$slackAttachment = new Slack\Attachment( "Post to Twitter" );
			$slackAttachment->setColor( 'danger' );
			$slackAttachment->addField( 'Status', 'Failed', true );
			$slackAttachment->addField( 'Message', $e->getMessage(), true );

			$slackMessage = new Slack\Message();
			$slackMessage->setUsername( $this->config->getValue( 'slack', 'name' ) );
			$slackMessage->setEmoji( $this->config->getValue( 'slack', 'emoji' ) );
			$slackMessage->addAttachment( $slackAttachment );

			$this->slack->postMessage( $slackWebhook, $slackMessage );
		}

		$this->bot->tweetDidFail();

		throw new Command\CommandInvokedException( "Twitter said: '" . $e->getMessage() . "'", 1 );
	}

	$this->bot->tweetDidSucceed();
});

return $command;
