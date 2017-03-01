<?php

use Cranberry\Bot\Slack;
use Cranberry\CLI\Command;
use Cranberry\Core\HTTP;

/**
 * @name			palette
 * @description		Update palettes from remote source
 * @usage			palette
 */
$command = new Command\Command( 'palette', 'Update palettes from remote source', function()
{
	/*
	 * Plan for a problem
	 */
	$slackWebhook = $this->config->getValue( 'slack', 'webhook' );
	$slackAttachment = new Slack\Attachment( 'Update Palettes' );

	/*
	 * Fetch the palette
	 */
	$palettesFile = $this->app->dataDirectory->child( 'palettes.yml' );

	try
	{
		$palettesURL = 'https://cabrera-bots.s3.amazonaws.com/tinyheavens/palettes-private.yml';
		$request = new HTTP\Request( $palettesURL );
		$response = HTTP\HTTP::get( $request );
	}
	catch( \Exception $e )
	{
		$slackAttachment->setColor( 'warning' );
		throw new Bot\Exception( $e->getMessage(), 1, $e, $slackWebhook, $this->slackMessage, $slackAttachment );
	}

	$responseStatus = $response->getStatus();
	if( $responseStatus['code'] != 200 )
	{
		$errorMessage = "Could not fetch remote palette";
		$slackAttachment->setColor( 'warning' );
		$slackAttachment->addField( 'Response', "{$responseStatus['code']} {$responseStatus['message']}", true );

		throw new Bot\Exception( $errorMessage, 1, $slackWebhook, $this->slackMessage, $slackAttachment );
	}

	try
	{
		$palettesFile->putContents( $response->getBody() );
	}
	catch( \Exception $e )
	{
		$slackAttachment->setColor( 'danger' );
		throw new Bot\Exception( $e->getMessage(), 1, $slackWebhook, $this->slackMessage, $slackAttachment );
	}
});

return $command;
