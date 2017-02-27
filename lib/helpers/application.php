<?php

use Cranberry\Bot\Slack;
use Cranberry\Bot\Twitter;
use Cranberry\Core\Config;
use Cranberry\Core\File;

/*
 * Set directories
 */
$dataDirectory = new File\Directory( $config['data-dir'] );
$app->setDataDirectory( $dataDirectory );

if( !$dataDirectory->exists() )
{
	$dataDirectory->create();
}

/*
 * Config
 */
$configFile = $dataDirectory->child( 'config.json' );
$config = new Config( $configFile );
$app->registerCommandObject( 'config', $config );

/*
 * Twitter
 */
if( is_null( ($twitterCredentials = $config->getDomain( 'twitter' )) ) )
{
	$twitterCredentials = [];
}
$twitter = new Twitter\Twitter( $twitterCredentials );
$app->registerCommandObject( 'twitter', $twitter );

/*
 * Slack
 */
$slack = new Slack\Slack();
$app->registerCommandObject( 'slack', $slack );

/*
 * Bot
 */
$bot = new \Bot\Bot( $dataDirectory );
$app->registerCommandObject( 'bot', $bot );
