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
    date_default_timezone_set("America/Toronto");

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
	function isAtReply($tweet)	{	return !is_null($tweet->in_reply_to_user_id);	}

	/*
	 *	input: 	username to search for	(defaults to this file's TWITTER_USERNAME defined value)
	 *	output:	HTML string of latest tweet, in following format
	 *				<div id='tweet'>Tweet goes here</div>
	 *				<div id='twittertime'>tweeted x minutes ago from [physical location or web client, in that order]</div>
	 */
	function latestTweet($username = TWITTER_USERNAME){
		$posts = json_decode(file_get_contents("http://twitter.com/statuses/user_timeline/".$username.".json"));
		if($posts === false)	die("Something went wrong, Twitter's not responding... so insert a witty tweet here.");
		
		$item = null;
		foreach($posts as $post){
			if(!(isAtReply($post) || $post->retweeted)){	//Comment out this line to allow for @replies and retweets.
				$item = $post;
				break;
			}												//Also this line.
		}

		$tweet = preg_replace("%(http://[\S]+)%", "<a href=\"$1\" target=\"_blank\">$1</a>", $item->text);	//link all URLs in the tweet
		$tweet = preg_replace("%@([\S]+)%", "<a href=\"http://twitter.com/$1\" target=\"_blank\">@$1</a>", $item->text);	//link all URLs in the tweet
		$date = Date_Difference::getStringResolved($item->created_at);
		if(!is_null($item->geo)) $loc = "<a href='http://maps.google.com/?q=".urlencode($item->geo->coordinates)."' target='_blank' >".$item->place->full_name."</a>";
		else if(!is_null($item->place)) $loc = "<a href='http://twitter.com/places/".$item->place->id."' target='_blank' >".$item->place->full_name."</a>";
		$r = "<div id='tweet'>$tweet</div>";

		if($loc != "")	$r .= "<div id='twittertime'>tweeted $date from $loc</div>"; 
		else if($item->source != "") $r .= "<div id='twittertime'>tweeted $date from ".$item->source."</div>";
		else $r .= "<div id='twittertime'>tweeted $date</div>";

		return $r;
	}

	echo latestTweet();
?>
