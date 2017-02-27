<?php

/*
 * This file is part of Bot
 */
namespace Bot;

use Cranberry\Core\Utils;

class Palette
{
	/**
	 * @var	array
	 */
	protected $colors=[];

	/**
	 * @var	string
	 */
	protected $name;

	/**
	 * @param	string	$name
	 */
	public function __construct( $name )
	{
		$this->name = $name;
	}

	/**
	 * @param	string	$color
	 */
	public function addColor( $group, $color )
	{
		$this->colors[$group][] = $color;
	}

	/**
	 * @param	string	$color
	 * @param	int		$offset
	 * @return	string
	 */
	public function brightness( $color, $offset )
	{
		if( substr( $color, 0, 1 ) == '#' )
		{
			$color = substr( $color, 1, 6 );
		}

		$adjusted['r'] = hexdec( substr( $color, 0, 2 ) );
		$adjusted['g'] = hexdec( substr( $color, 2, 2 ) );
		$adjusted['b'] = hexdec( substr( $color, 4, 2 ) );

		foreach( $adjusted as &$channel )
		{
			if( $channel + $offset < 0 )
			{
				$channel = 0;
			}
			elseif( $channel + $offset > 255 )
			{
				$channel = 255;
			}
			else
			{
				$channel = $channel + $offset;
			}
		}

		$adjusted['r'] = sprintf( '%02s', dechex( $adjusted['r'] ) );
		$adjusted['g'] = sprintf( '%02s', dechex( $adjusted['g'] ) );
		$adjusted['b'] = sprintf( '%02s', dechex( $adjusted['b'] ) );

		return sprintf( '#%s', implode( '', $adjusted ) );
	}

	/**
	 * @param	string	$group
	 * @return	array
	 */
	public function getColorGroup( $group )
	{
		if( !isset( $this->colors[$group] ) )
		{
			throw new \DomainException( "Unknown color group '{$group}'" );
		}

		return $this->colors[$group];
	}

	/**
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param	string	$group
	 * @return	string
	 */
	public function getRandomColor( $group )
	{
		if( !isset( $this->colors[$group] ) )
		{
			throw new \DomainException( "Unknown color group '{$group}'" );
		}

		return Utils::getRandomElement( $this->colors[$group] );
	}
}
