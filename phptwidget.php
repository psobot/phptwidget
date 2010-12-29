<?php

	/*
	 *	Custom PHP Twitter Widget
	 *	Written by Peter Sobot (petersobot.com)
	 *	v2.0: November 24, 2010
	 *	http://github.com/psobot/phptwidget
	 *	
	 *	Licensed under the MIT license.
	 *
	 */

	ini_set("allow_url_fopen", "On");
	define("TWITTER_USERNAME", "psobot");		//Set your username here.

	class Date_Difference {
		/**
		 *	Converts a timestamp to pretty human-readable format.
		 * 
		 *	Original JavaScript Created By John Resig (jquery.com)  Copyright (c) 2008
		 *	Copyright (c) 2008 John Resig (jquery.com)
		 *	Licensed under the MIT license.
		 *	Ported to PHP >= 5.1 by Zach Leatherman (zachleat.com)
		 *
		 */
		public static function getStringResolved($date, $compareTo = NULL) { 
			if(!is_null($compareTo)) $compareTo = new DateTime($compareTo); 
			return self::getString(new DateTime($date), $compareTo); 
		} 
	
		public static function getString(DateTime $date, DateTime $compareTo = NULL) { 
			if(is_null($compareTo))	$compareTo = new DateTime('now'); 
			$diff = $compareTo->format('U') - $date->format('U'); 
			$dayDiff = floor($diff / 86400); 
	
			if(is_nan($dayDiff) || $dayDiff < 0) return ''; 
					 
			if($dayDiff == 0) { 
				if($diff < 60) return 'just now'; 
				elseif($diff < 120)	return '1 minute ago'; 
				elseif($diff < 3600) return floor($diff/60) . ' minutes ago'; 
				elseif($diff < 7200) return '1 hour ago'; 
				elseif($diff < 86400) return floor($diff/3600) . ' hours ago'; 
			} elseif($dayDiff == 1) return 'yesterday'; 
			elseif($dayDiff < 7) return $dayDiff . ' days ago'; 
			elseif($dayDiff == 7) return '1 week ago'; 
			elseif($dayDiff < (7*6)) return ceil($dayDiff/7) . ' weeks ago'; 
			elseif($dayDiff < 365) return ceil($dayDiff/(365/12)) . ' months ago'; 
			else { 
				$years = round($dayDiff/365); 
				return $years . ' year' . ($years != 1 ? 's' : '') . ' ago'; 
			} 
		} 
	}
	
	/*
	 *	input:		string of tweet
	 *	returns:	boolean (if tweet is an @reply)
	 */
	function isAtReply($tweet)	{	return strpos($tweet, "@") === 0;		}

	/*
	 *	input:		string of tweet
	 *	returns:	boolean (if tweet is a retweet)
	 */
	function isReTweet($tweet)	{	return strpos($tweet, "RT") === 0;		}

	/*
	 *	input:		Array
	 *	returns:	first element of Array
	 *	
	 */
	function firstOf($a)		{	return $a[0];							}

	/*
	 *	input: 	username to search for	(defaults to this file's TWITTER_USERNAME defined value)
	 *	output:	HTML string of latest tweet, in following format
	 *				<div id='tweet'>Tweet goes here</div>
	 *				<div id='twittertime'>tweeted x minutes ago from [physical location or web client, in that order]</div>
	 */
	function latestTweet($username = TWITTER_USERNAME){
		$data = simplexml_load_file("http://twitter.com/statuses/user_timeline/".$username.".rss");
		if($data === false)	die("Something went wrong, Twitter's not responding... so insert a witty tweet here.");
		
		foreach($data->getNamespaces(true) as $prefix => $namespace)	$data->registerXPathNamespace($prefix, $namespace);

		$prefixlength = strlen($username) + 2;
		$item = null;
		$posts = $data->xpath('/rss/channel/item');
		foreach($posts as $post){
			$title = $post->title;
			$tweet = substr($title[0], $prefixlength);
			if(!(isAtReply($tweet) || isReTweet($tweet))){	//Comment out this line to allow for @replies and retweets.
				$item = $post;
				break;
			}												//Also this line.
		}

		$tweet = substr($item->title, $prefixlength);
		//$tweet = preg_replace("/(\.)[ ]+/", "$1<br />", $tweet, 1);	//force all sentences onto newlines.

		$date = Date_Difference::getStringResolved($item->pubDate);
		$loc = @firstOf($item->xpath('twitter:place/twitter:full_name'));	//Accessing these nodes like this is messy,
		$via = @firstOf($item->xpath('twitter:source'));					//but this is the cleanest way I've found so far.
		$geoPoint = @firstOf($item->xpath('georss:point'));
		if($geoPoint) $loc = "<a href='http://maps.google.com/?q=".urlencode($geoPoint)."' target='_blank' >$loc</a>";
		$r = "<div id='tweet'>$tweet</div>";

		if($loc != "")	$r .= "<div id='twittertime'>tweeted $date from $loc</div>"; 
		else if($via != "") $r .= "<div id='twittertime'>tweeted $date from $via</div>";
		else $r .= "<div id='twittertime'>tweeted $date</div>";

		return $r;
	}

	echo latestTweet();
?>
