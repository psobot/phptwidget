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
	define("USERNAME", "psobot");		//Set your username here.
	define("TWEETPREFIXLENGTH",  strlen(USERNAME) + 2);

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

	function isAtReply($tweet)	{	return strpos($tweet, "@") === 0;		}
	function isReTweet($tweet)	{	return strpos($tweet, "RT") === 0;		}
	function asString($xpath)	{	return $xpath[0];						}

	$data = simplexml_load_file("http://twitter.com/statuses/user_timeline/".USERNAME.".rss");
	if($data === false)	die("Something went wrong, Twitter's not responding... so insert a witty tweet here.");

	foreach($data->getNamespaces(true) as $prefix => $namespace)	$data->registerXPathNamespace($prefix, $namespace);

	$item = null;
	$posts = $data->xpath('/rss/channel/item');
	foreach($posts as $post){
		$title = $post->xpath('title');
		$tweet = substr($title[0], TWEETPREFIXLENGTH);
		if(!(isAtReply($tweet) || isReTweet($tweet))){
			$item = $post;
			break;
		}
	}

	$tweet = substr(asString($item->xpath('title')), TWEETPREFIXLENGTH);
	$date = Date_Difference::getStringResolved(asString($item->xpath('pubDate')));
	$loc = asString($item->xpath('twitter:place/twitter:full_name'));
	$via = asString($item->xpath('twitter:source'));
	if($loc != "")	echo "$tweet<br /><div id='twittertime'>tweeted $date from $loc</div>"; 
	else if($via != "") echo "$tweet<br /><div id='twittertime'>tweeted $date from $via</div>";
	else echo "$tweet<br /><div id='twittertime'>tweeted $date</div>"; 
?>
