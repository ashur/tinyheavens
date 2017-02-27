<?php

use Cranberry\Bot\Slack;
use Cranberry\CLI\Command;

/**
 * @name			dev
 * @description		Dev
 * @usage			dev
 */
$command = new Command\Command( 'dev', 'Dev', function()
{
	$imageFile = $this->bot->generateImage();
	$this->bot->tweetDidSucceed();
});

return $command;
