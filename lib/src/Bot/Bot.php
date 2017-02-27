<?php

/*
 * This file is part of Bot
 */
namespace Bot;

use Cranberry\Bot\History;
use Cranberry\Bot\Twitter;
use Cranberry\Core\File;
use Cranberry\Core\Utils;
use Cranberry\Pixel;
use Spyc;

class Bot
{
	/**
	 * @var	array
	 */
	protected $brushes=[];

	/**
	 * @var	Cranberry\Bot\History\History
	 */
	protected $history;

	/**
	 * @var	Cranberry\Core\File\File
	 */
	protected $imageFile;

	/**
	 * @var	array
	 */
	protected $starBrushes=[];

	/**
	 * @var	Cranberry\Core\File\Directory
	 */
	protected $tempDirectory;

	/**
	 * @param	Cranberry\Core\File\Directory	$dataDirectory
	 */
	public function __construct( File\Directory $dataDirectory )
	{
		$historyFile = $dataDirectory->child( 'history.json ');
		$this->history = new History\History( $historyFile );

		$this->tempDirectory = $dataDirectory->childDir( 'tmp' );
	}

	/**
	 * @return	Cranberry\Core\File\File
	 */
	public function generateImage()
	{
		$this->loadBrushes();

		$canvasCols = 250;
		$canvasRows = 200;

		/*
		 * Palette
		 */
		$palettes = [];

 		$palettesURL = 'http://cabrera-bots.s3.amazonaws.com/tinyheavens/palettes.yml';
 		$palettesYAML = file_get_contents( $palettesURL );
 		$palettesData = Spyc::YAMLLoadString( $palettesYAML );

 		foreach( $palettesData as $paletteData )
 		{
 			$paletteObject = new Palette( $paletteData['name'] );
 			foreach( $paletteData['colors'] as $group => $colors )
 			{
 				foreach( $colors as $color )
 				{
 					$paletteObject->addColor( $group, $color );
 				}
 			}

 			$palettes[$paletteData['name']] = $paletteObject;
 		}

		/* Try not to repeat the same palette */
		shuffle( $palettes );
		foreach( $palettes as $paletteCandidate )
		{
			if( $this->history->domainEntryExists( 'palette', $paletteCandidate->getName() ) )
			{
				continue;
			}
			else
			{
				$palette = $paletteCandidate;
				break;
			}
		}

		/* We exhausted all the palettes */
		if( !isset( $palette ) )
		{
			$this->history->resetDomain( 'palette' );
			$palette = Utils::getRandomElement( $palettes );
		}

		$this->history->addDomainEntry( 'palette', $palette->getName() );

		$canvas = new Pixel\Canvas( $canvasCols, $canvasRows, 3 );
		$canvasColor = $palette->getRandomColor( 'canvas' );
		$canvas->setBackgroundColor( $canvasColor );

		/*
		 * Texture
		 */
		$textureColorDark = Palette::brightness( $canvasColor, -2 );
		$textureColorLight = Palette::brightness( $canvasColor, 2 );

		$brush = $this->brushes['texture'];
		for( $row = 0; $row < $canvas->getRows(); $row = $row + $brush->getRows() )
		{
			for( $col = 0; $col < $canvas->getCols(); $col = $col + $brush->getCols() )
			{
				$canvas->drawWithMask( $brush, $textureColorDark, $col, $row );
				$canvas->drawWithMask( $brush, $textureColorLight, $col, $row );
			}
		}

		/*
		 * Graticule
		 */
		$latLineCount = rand( 2, 3 );
		$longLineCount = $latLineCount + rand( 0, 1 );

		try
		{
			$graticuleColor = $palette->getRandomColor( 'graticule' );
		}
		catch( \Exception $e )
		{
			$graticuleColor = Palette::brightness( $canvasColor, 10 );
		}

		for( $lat = 1; $lat <= $latLineCount; $lat++ )
		{
			$col1 = 0;
			$row1 = floor( $canvas->getRows() / $latLineCount ) * $lat + rand( -8, 8 );
			$col2 = $canvas->getCols();
			$row2 = floor( $canvas->getRows() / $latLineCount ) * $lat + rand( -8, 8 );

			$canvas->drawLine( $col1, $row1, $col2, $row2, $graticuleColor );
		}

		for( $long = 1; $long <= $longLineCount; $long++ )
		{
			$col1 = 5 + floor( $canvas->getCols() / $longLineCount ) * $long + rand( -8, 8 );
			$row1 = 0;
			$col2 = 5 + floor( $canvas->getCols() / $longLineCount ) * $long + rand( -8, 8 );
			$row2 = $canvas->getRows();

			$canvas->drawLine( $col1, $row1, $col2, $row2, $graticuleColor );
		}

		/*
		 * Stars
		 */
		$backgroundStarCount = rand( 40, 120 );

		$minCol = 0;
		$minRow = 0;

		for( $bs = 0; $bs < $backgroundStarCount; $bs++ )
		{
			$brush = rand( 0, 1 ) == 1 ? $this->starBrushes['small'] : $this->starBrushes['tiny'];

			$col = rand( $minCol, $canvas->getCols() - 5 );
			$row = rand( $minRow, $canvas->getRows() - 5 );

			$color = Palette::brightness( $palette->getRandomColor( 'starsBackground' ), -1 * rand( 0, 40 ) );

			$canvas->drawWithMask( $brush, $color, $col, $row );
		}

		$foregroundStarCount = rand( 3, 10 );

		for( $fs = 0; $fs < $foregroundStarCount; $fs++ )
		{
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['small'];
			$brushes[] = $this->starBrushes['medium'];
			$brushes[] = $this->starBrushes['medium'];
			$brushes[] = $this->starBrushes['medium'];
			$brushes[] = $this->starBrushes['large'];

			$brush = Utils::getRandomElement( $brushes );

			$col = rand( $minCol, $canvas->getCols() );
			$row = rand( $minRow, $canvas->getRows() );

			$colors = Utils::getRandomElement( $palette->getColorGroup( 'planets' ), 2 );
			$colors[] = $palette->getRandomColor( 'starsForeground' );
			$colors[] = $palette->getRandomColor( 'starsForeground' );
			$colors[] = $palette->getRandomColor( 'starsForeground' );
			$colors[] = $palette->getRandomColor( 'starsForeground' );
			$colors[] = $palette->getRandomColor( 'starsForeground' );
			$colors[] = $palette->getRandomColor( 'starsForeground' );

			$color = Utils::getRandomElement( $colors );
			$canvas->drawWithMask( $brush, $color, $col, $row );

			if( $brush->getCols() >= $this->starBrushes['medium']->getCols() )
			{
				$stencil = new Pixel\Mask( $canvasCols, $canvasRows );
				$stencil->clearMask( $brush, $col, $row );

				$canvas->applyStencil( $stencil, 0, 0 );
				$canvas->drawWithMask( $this->brushes['texture'], Palette::brightness( $color, -20 ), $col, $row );
				$canvas->drawWithMask( $this->brushes['texture'], Palette::brightness( $color, -40 ), $col, $row );
				$canvas->drawWithMask( $this->brushes['texture'], Palette::brightness( $color, -70 ), $col, $row );
				$canvas->removeStencil();
			}
		}

		/*
		 * Write to file
		 */
		$imageFile = $this->tempDirectory->child( 'heavens.png' );

		if( !$imageFile->exists() )
		{
			$imageFile->create();
		}

		$canvas->render( $imageFile );

		return $imageFile;
	}

	/**
	 * Generate and return a Tweet object
	 *
	 * @return	Cranberry\Bot\Twitter\Tweet
	 */
	public function getTweet()
	{
		$imageFile = $this->generateImage();

		$tweet = new Twitter\Tweet();
		$tweet->attachMedia( $imageFile );

		return $tweet;
	}

	/**
	 * @return	array
	 */
	public function loadBrushes()
	{
		/*
		 * Paper Texture
		 */
		$textureBrush = new Pixel\Paintbrush( 50, 35, false );
		$this->brushes['texture'] = $textureBrush;

		/*
		 * Stars
		 */
		$starBrushes = [];

		/* Tiny */
		$tinyStarBrush = new Pixel\Mask( 1, 1 );
		$this->starBrushes['tiny'] = $tinyStarBrush;

		/* Small */
		$smallStarBrush = new Pixel\Mask( 2, 2 );
		$this->starBrushes['small'] = $smallStarBrush;

		/* Medium */
		$mediumStarBrush = new Pixel\Mask( 5, 5 );
		$mediumStarBrush->clearAt( 0, 0 );
		$mediumStarBrush->clearAt( 4, 0 );
		$mediumStarBrush->clearAt( 0, 4 );
		$mediumStarBrush->clearAt( 4, 4 );
		$this->starBrushes['medium'] = $mediumStarBrush;

		/* Large */
		$largeStarBrush = new Pixel\Mask( 10, 10 );
		$largeStarBrush->clearAt( 0, 0 );
		$largeStarBrush->clearAt( 1, 0 );
		$largeStarBrush->clearAt( 2, 0 );
		$largeStarBrush->clearAt( 3, 0 );
		$largeStarBrush->clearAt( 6, 0 );
		$largeStarBrush->clearAt( 7, 0 );
		$largeStarBrush->clearAt( 8, 0 );
		$largeStarBrush->clearAt( 9, 0 );

		$largeStarBrush->clearAt( 0, 1 );
		$largeStarBrush->clearAt( 1, 1 );
		$largeStarBrush->clearAt( 8, 1 );
		$largeStarBrush->clearAt( 9, 1 );

		$largeStarBrush->clearAt( 0, 2 );
		$largeStarBrush->clearAt( 9, 2 );

		$largeStarBrush->clearAt( 0, 3 );
		$largeStarBrush->clearAt( 9, 3 );

		$largeStarBrush->clearAt( 0, 6 );
		$largeStarBrush->clearAt( 9, 6 );

		$largeStarBrush->clearAt( 0, 7 );
		$largeStarBrush->clearAt( 9, 7 );

		$largeStarBrush->clearAt( 0, 8 );
		$largeStarBrush->clearAt( 1, 8 );
		$largeStarBrush->clearAt( 8, 8 );
		$largeStarBrush->clearAt( 9, 8 );

		$largeStarBrush->clearAt( 0, 9 );
		$largeStarBrush->clearAt( 1, 9 );
		$largeStarBrush->clearAt( 2, 9 );
		$largeStarBrush->clearAt( 3, 9 );
		$largeStarBrush->clearAt( 6, 9 );
		$largeStarBrush->clearAt( 7, 9 );
		$largeStarBrush->clearAt( 8, 9 );
		$largeStarBrush->clearAt( 9, 9 );

		$this->starBrushes['large'] = $largeStarBrush;
	}

	/**
	 * Called if posting to Twitter fails
	 */
	public function tweetDidSucceed()
	{
		$this->history->write();
		$this->tempDirectory->delete();
	}

	/**
	 * Called if posting to Twitter succeeds
	 */
	public function tweetDidFail(){}
}
