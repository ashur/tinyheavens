<?php

use Cranberry\CLI\Command;
use Cranberry\CLI\Output;

/**
 * @name		config
 * @desc		Configure the local environment
 * @usage		config <domain>[.<key> [<value>]]
 */
$command = new Command\Command( 'config', 'Configure the local environment', function( $query=null, $value=null )
{
	$output = new Output\Output;

	if( is_null( $query ) )
	{
		$configData = $this->config->getData();

		foreach( $configData as $domain => $domainItems )
		{
			foreach( $domainItems as $key => $value )
			{
				$output->line( "{$domain}.{$key}={$value}" );
			}
		}

		return $output->flush();
	}

	/*
	 * Parse query
	 */
	$queryPieces = explode( '.', $query );
	$domain = $queryPieces[0];

	if( !isset( $queryPieces[1] ) )
	{
		$domainItems = $this->config->getDomain( $domain );
		if( is_null( $domainItems ) )
		{
			throw new Command\CommandInvokedException( "Unknown config domain '{$domain}'.", 1 );
		}

		foreach( $domainItems as $key => $value )
		{
			$output->line( "{$domain}.{$key}={$value}" );
		}

		return $output->flush();
	}

	$key = $queryPieces[1];

	/*
	 * Read values
	 */
	if( is_null( $value ) )
	{
		$domainValues = $this->config->getDomain( $domain );
		if( !isset( $domainValues[$key] ) )
		{
			// Exit silently
			exit( 1 );
		}

		$output->line( $domainValues[$key] );
		return $output->flush();
	}

	/*
	 * Write values
	 */
	$this->config->setValue( $domain, $key, $value );
	$this->config->write();
});

$command->setUsage( 'config <domain>[.<key> [<value>]]' );

return $command;
