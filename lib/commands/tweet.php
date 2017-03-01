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
	/*
	 * Plan for a problem
	 */
	$slackWebhook = $this->config->getValue( 'slack', 'webhook' );
	$slackAttachment = new Slack\Attachment( 'Post to Twitter' );

	/*
	 * Attempt to tweet
	 */
	$tweet = $this->bot->getTweet();

	try
	{
		$response = $this->twitter->postTweet( $tweet );
	}
	catch( Exception $e )
	{
		$this->bot->tweetDidFail();

		$slackAttachment->setColor( 'danger' );
		$slackAttachment->addField( 'Code', $e->getCode(), true );

		throw new Bot\Exception( $e->getMessage(), 1, $slackWebhook, $this->slackMessage, $slackAttachment );
	}

	$this->bot->tweetDidSucceed();
});

return $command;
