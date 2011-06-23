<?php
/**
 * Wrapper class around the Twitter API for PHP
 * Based on the class originally developed by David Billingham
 * and accessible at http://twitter.slawcup.com/twitter.class.phps
 * @author David Billingham <david@slawcup.com>
 * @author Aaron Brazell <aaron@technosailor.com>
 * @author Keith Casey <caseysoftware@gmail.com>
 * @version 1.1
 * @package php-twitter
 * @subpackage classes
 */

class twitter{
	/**
	 * Authenticating Twitter user
	 * @var string
	 */
	var $username='';
	
	/**
	 * Autenticating Twitter user password
	 * @var string
	 */
	var $password='';

	/**
	 * Recommend setting a user-agent so Twitter knows how to contact you inc case of abuse. Include your email
	 * @var string
	 */
	var $user_agent='';

	/**
	 * Can be set to JSON (requires PHP 5.2 or the json pecl module) or XML - json|xml
	 * @var string
	 */
	var $type='json';

	/**
	 * It is unclear if Twitter header preferences are standardized, but I would suggest using them.
	 * More discussion at http://tinyurl.com/3xtx66
	 * @var array
	 */
	var $headers=array('Expect:', 'X-Twitter-Client: ','X-Twitter-Client-Version: ','X-Twitter-Client-URL: ');

	/**
	 * @var array
	 */
	var $responseInfo=array();
	
	/**
	 * @var boolean
	 */
	 var $suppress_response_code = false;
	 
	/**
	 * @var boolean
	 */
	 var $debug = false;
    
	function twitter()
	{
		// Nothing
	}
	
	/****** Statuses ******/

	/**
	 * Send a status update to Twitter.
	 * @param string $status total length of the status update must be 140 chars or less.
	 * @return string|boolean
	 */
	function update( $status, $replying_to = false )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
        $request = 'http://twitter.com/statuses/update.' . $this->type;
		//$status = $this->shorturl($status);
        $postargs = array( 'status' => $status );
        if( $replying_to )
            $postargs['in_reply_to_status_id'] = (int) $replying_to; 

		return $this->objectify( $this->process($request, $postargs) );
	}
	
	/**
	 * Get @ replies
	 * @param integer Optional. Paging of tweets. Number specifies which page of results
	 * @param string $since (HTTP-formatted date) Optional.  Narrows the resulting list of direct messages to just those sent after the specified date. 
     * @param integer $since_id Optional. Returns results posted that have an ID greater than $since_id
     * @return string
     **/
	function getReplies( $page = false, $since = false, $since_id = false )
	{
	    if( !in_array( $this->type, array( 'xml','json','rss','atom' ) ) )
	        return false;
	        
	    $args = array();
	    if( $page )
	        $args['page'] = (int) $page;
	    if( $since )
	        $args['since'] = (string) $since;
	    if( $since_id )
	        $args['since_id'] = (int) $since_id;
	    
	    $qs = '';
	    if( !empty( $args ) )
	        $qs = $this->_glue( $args );
	    
	    $request = 'http://twitter.com/statuses/replies.' . $this->type . $qs;    
	    return $this->objectify( $this->process( $request ) );
	}
	
	/**
	 * Destroy a tweet
	 * @param integer $id Required.
	 * @return string
	 **/
	function deleteStatus( $id )
    {
        if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
        $request = 'http://twitter.com/statuses/destroy/' . (int) $id . '.' . $this->type;
        return $this->objectify( $this->process( $request, true ) );
    }
    
	/**
	 * Send an unauthenticated request to Twitter for the public timeline. 
	 * Returns the last 20 updates by default
	 * @param boolean|integer $sinceid Returns only public statuses with an ID greater of $sinceid
	 * @return string
	 */
	function publicTimeline( $sinceid = false )
	{
	    if( !in_array( $this->type, array( 'xml','json','rss','atom' ) ) )
	        return false;
	        
        $qs='';
        if( $sinceid !== false )
            $qs = '?since_id=' . intval($sinceid);
        $request = 'http://twitter.com/statuses/public_timeline.' . $this->type . $qs;

		return $this->objectify( $this->process($request) );
	}
      
	/**
	 * Send an authenticated request to Twitter for the timeline of authenticating user. 
	 * Returns the last 20 updates by default
	 * @param boolean|integer $id Specifies the ID or screen name of the user for whom to return the friends_timeline. (set to false if you want to use authenticated user).
	 * @param boolean|integer $since Narrows the returned results to just those statuses created after the specified date.
	 * @deprecated integer $count. As of July 7 2008, Twitter has requested the limitation of the count keyword. Therefore, we deprecate
	 * @return string
	 */
	function userTimeline($id=false,$count=20,$since=false,$since_id=false,$page=false)
	{
	    if( !in_array( $this->type, array( 'xml','json','rss','atom' ) ) )
	        return false;
	    
	    $args = array();
	    if( $id )
	        $args['id'] = $id;
	    if( $count )
	        $args['count'] = (int) $count;
	    if( $since )
	        $args['since'] = (string) $since;
	    if( $since_id )
	        $args['since_id'] = (int) $since_id;
	    if( $page )
	        $args['page'] = (int) $page;
	    
	    $qs = '';
	    if( !empty( $args ) )
	        $qs = $this->_glue( $args );
			            
        if( $id === false )
            $request = 'http://twitter.com/statuses/user_timeline.' . $this->type . $qs;
        else
            $request = 'http://twitter.com/statuses/user_timeline/' . rawurlencode($id) . '.' . $this->type . $qs;
        
		return $this->objectify( $this->process($request) );
	}
    
	/**
	 * Returns a single status, specified by the id parameter below.  The status's author will be returned inline.
	 * @param integer $id The id number of the tweet to be returned.
	 * @return string
	 */
	function showStatus( $id )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
        $request = 'http://twitter.com/statuses/show/'.intval($id) . '.' . $this->type;
		return $this->objectify( $this->process($request) );
    }
    
    /**
	 * Returns the authenticating user's friends, each with current status inline.  It's also possible to request another user's friends list via the id parameter below.
	 * @param integer|string $id Optional. The user ID or name of the Twitter user to query.
	 * @param integer $page Optional. 
	 * @return string
	 */
	function friends( $id = false, $page = false )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
        $args = array();
	    if( $id )
	        $args['id'] = $page;
	    if( $page )
	        $args['page'] = (int) $page;
	    
	    $qs = '';
	    if( !empty( $args ) )
	        $qs = $this->_glue( $args );
	        
	    $request = ( $id ) ? 'http://twitter.com/statuses/friends/' . $id . '.' . $this->type . $qs : 'http://twitter.com/statuses/friends.' . $this->type . $qs;
		return $this->objectify( $this->process($request) );
	}
    
	/**
	 * Returns the authenticating user's followers, each with current status inline.
	 * @param integer $page Optional.
	 * @return string
	 */
	function followers( $page = false )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
        $request = 'http://twitter.com/statuses/followers.' . $this->type;
        if( $page )
            $request .= '?page=' . (int) $page;
        
		return $this->objectify( $this->process($request) );
	}


    /****** Favorites ******/

	/**
	 * Retrieves favorited tweets
	 * @param integer|string $id Required. The username or ID of the user to be fetched
	 * @param integer $page Optional. Tweets are returned in 20 tweet blocks. This int refers to the page/block
	 * @return string
	 */
	function getFavorites( $id, $page=false )
	{
	    if( !in_array( $this->type, array( 'xml','json','rss','atom' ) ) )
	        return false;
	        
		if( $page != false )
			$qs = '?page=' . $page;
		
		$request = 'http://twitter.com/favorites.' . $this->type . $qs; 
		return $this->objectify( $this->process($request) );
	}
	
	/**
	 * Favorites a tweet
	 * @param integer $id Required. The ID number of a tweet to be added to the authenticated user favorites
	 * @return string
	 */
	function makeFavorite( $id )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
		$request = 'http://twitter.com/favorites/create/' . $id . '.' . $this->type;
		return $this->objectify( $this->process($request) );	
	}
	
	/**
	 * Unfavorites a tweet
	 * @param integer $id Required. The ID number of a tweet to be removed to the authenticated user favorites
	 * @return string
	 */
	function removeFavorite( $id )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
		$request = 'http://twitter.com/favorites/destroy/' . $id . '.' . $this->type;
		return $this->objectify( $this->process($request) );	
	}
	
	/****** Friendships ******/
	
	/**
	 * Checks to see if a friendship already exists
	 * @param string|integer $user_a Required. The username or ID of a Twitter user
	 * @param string|integer $user_b Required. The username or ID of a Twitter user
	 * @return string
	 */
	function isFriend( $user_a, $user_b )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
		$qs = '?user_a=' . rawurlencode( $user_a ) . '&amp;' . rawurlencode( $user_b );
		$request = 'http://twitter.com/friendships/exists.' . $this->type . $qs;
		return $this->objectify( $this->process($request) );
	}
	
	/**
	 * Sends a request to follow a user specified by ID
	 * @param integer|string $id The twitter ID or screenname of the user to follow
	 * @param boolean $notifications Optional. If true, you will recieve notifications from the users updates
	 * @return string
	 */
	function followUser( $id, $notifications = false )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
		$request = 'http://twitter.com/friendships/create/' . (int) $id . '.' . $this->type;
		if( $notifications )
		    $request .= '?follow=true';
		    
		return $this->objectify( $this->process($request) );
	}
	
	/**
	 * Unfollows a user
	 * @param integer|string $id the username or ID of a person you want to unfollow
	 * @return string
	 */
	function leaveUser( $id )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
		$request = 'http://twitter.com/friendships/destroy/' . $id . '.' . $this->type;
		return $this->objectify( $this->process($request) );
	}
	
	/****** Blocks ******/
	
	/**
	 * Blocks a user
	 * @param integer|string $id the username or ID of a person you want to block
	 * @return string
	 */
	function blockUser( $id )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
		$request = 'http://twitter.com/blocks/create/' . $id . '.' . $this->type;
		return $this->objectify( $this->process($request) );
	}
	
	/**
	 * Unblocks a user
	 * @param integer|string $id the username or ID of a person you want to unblock
	 * @return string
	 */
	function unblockUser()
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
		$request = 'http://twitter.com/blocks/destroy/' . $id . '.' . $this->type;
		return $this->objectify( $this->process($request) );
	}
	
	/****** Social Graph ******/
	
	/**
	 * Returns a list of IDs of all friends for the specified user
	 * @param integer $id Required. User ID to request list of friend IDs for
	 * return string
	 */
	 
	 /* @todo same as friends()? */
	function socialGraphFollowing( $id = false)
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
	    $request = 'http://twitter.com/friends/ids';
	    if( $id )
	        $request .= '/' . (int) $id . '.' . $this->type;
	        
	    return $this->objectify( $this->process($request) );
	}

	/**
	 * Returns a list of IDs of all followers for the specified user
	 * @param integer $id Required. User ID to request list of friend IDs for
	 * return string
	 */
	 
	 /* @todo same as followers() */
	function socialGraphFollowedBy( $id = false )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
	    $request = 'http://twitter.com/followers/ids';
	    if( $id )
	        $request .= '/' . (int) $id . '.' . $this->type;
	        
	    return $this->objectify( $this->process($request) );
	}
	
	/****** Users ******/
    
	/**
	 * Returns extended information of a given user, specified by ID or screen name as per the required
	 * id parameter below.  This information includes design settings, so third party developers can theme
	 * their widgets according to a given user's preferences.	 
	 * @param integer $id Optional. The user ID.
	 * @param string $email Optional. The email address of the user being requested (can use in place of $id)
	 * @param integer $user_id Optional. The user ID (can use in place of $id)
	 * @param string $screen_name Optional. The screen name of the user being requested (can use in place of $id)
	 * @return string
	 */
	function showUser( $id, $email = false, $user_id = false, $screen_name=false )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
	    if( $user_id ) :
	        $qs = '?user_id=' . (int) $user_id;
	    elseif ( $screen_name ) :
	        $qs = '?screen_name=' . (string) $screen_name;
	    elseif ( $email ) :
	        $qs = '?email=' . (string) $email;
	    else :
	        $qs = (int) $id;
	    endif;
	    
        $request = 'http://twitter.com/users/show/' . $qs . $this->type;
        
		return $this->objectify( $this->process($request) );
	}
	
	/****** Direct Messages ******/
    
	/**
	 * Returns a list of the direct messages sent to the authenticating user.	 
	 * @param string $since (HTTP-formatted date) Optional.  Narrows the resulting list of direct messages to just those sent after the specified date. 
	 * @param int $count DEPRECATED. Remains for Backwards Compat
	 * @param integer $since_id
	 * @param integer $page
	 * @deprecated $count
	 * @return string
	 */
	function directMessages( $since = false, $count = null, $since_id = false, $page = false )
	{
	    if( !in_array( $this->type, array( 'xml','json','rss','atom' ) ) )
	        return false;
	        
        $qs='?';
        $qsparams = array();
        if( $since !== false )
            $qsparams['since'] = rawurlencode($since);
        if( $since_id )
            $qsparams['since_id'] = (int) $since_id;
        if( $page )
            $qsparams['page'] = (int) $page;
            
        $request = 'http://twitter.com/direct_messages.' . $this->type . implode( '&', $qsparams );

		return $this->objectify( $this->process($request) );
	}

	/**
	 * Returns a list of the sent direct messages from the authenticating user.	 
	 * @param string $since (HTTP-formatted date) Optional.  Narrows the resulting list of direct messages to just those sent after the specified date. 
	 * @param integer $since_id
	 * @param integer $page
	 * @deprecated $count
	 * @return string
	 */	
	function sentDirectMessage( $since = false, $since_id = false, $page = false )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
	    $qs = '?';
	    $qsparams = array();
	    if( $since !== false )
            $qsparams['since'] = rawurlencode($since);
        if( $since_id )
            $qsparams['since_id'] = (int) $since_id;
        if( $page )
            $qsparams['page'] = (int) $page;
            
        $request = 'http://twitter.com/direct_messages/sent.' . $this->type . implode( '&', $qsparams );
        return $this->objectify( $this->process($request) );
	}
    
	/**
	 * Sends a new direct message to the specified user from the authenticating user.  Requires both the user
	 * and text parameters below.	 
	 * @param string|integer Required. The ID or screen name of the recipient user.
	 * @param string $user The text of your direct message.  Be sure to URL encode as necessary, and keep it under 140 characters.  
	 * @return string
	 */
	function sendDirectMessage( $user, $text )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
        $request = 'http://twitter.com/direct_messages/new.' . $this->type;
        $postargs = 'user=' . rawurlencode($user) . '&text=' . rawurlencode($text);

		return $this->objectify( $this->process($request, $postargs) );
	}
	
	/** 
	 * Deletes a direct message
	 * @param integer $id Required
	 * @return string
	 */
	function deleteDirectMessage( $id )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
	    $request = 'http://twitter.com/direct_messages/destroy/' . (int) $id . '.' . $this->type;
	    return $this->objectify( $this->process( $request ) );
	}
	
	/****** Account ******/
	
	/**
	 * Updates delivery device
	 * @param string $device Required. Must be of type 'im', 'sms' or 'none'
	 * @return string
	 */
	function updateDevice( $device )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
		if( !in_array( $device, array('im','sms','none') ) )
			return false;
			
		$qs = '?device=' . $device;
		$request = 'http://twitter.com/account/update_delivery_device.' . $this->type . $qs;
		return $this->objectify( $this->process( $request ) );
	}
	
	/**
	 * @param binary Required. Use your script to pass a binary image (GIF, JPG, PNG <700kb) to update Twitter profile avatar
	 * @return string
	 */
	function updateAvatar( $file )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
	    // Adding @ ensures the POST will be raw multipart data encoded. This MUST be a file, not a URL. Handle it outside of the class.
	    $postdata = array( 'image' => "@$file");
	    $request = 'http://twitter.com/account/update_profile_image.' . $this->type;
	    return $this->objectify( $this->process( $request, $postdata ) );
	}
	
	/**
	 * @param binary Required. Use your script to pass a binary image (GIF, JPG, PNG <800kb) to update Twitter profile avatar. Images over 2048px wide will be scaled down
	 * @return string
	 */	
	function updateBackground( $file )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
	    // Adding @ ensures the POST will be raw multipart data encoded. This MUST be a file, not a URL. Handle it outside of the class.
	    $postdata = array( 'image' => "@$file");
	    $request = 'http://twitter.com/account/update_profile_background_image.' . $this->type;
	    return $this->objectify( $this->process( $request, $postdata ) );
	}
	
	/**
	 * @param array Requires. Pass an array of all optional members: name, email, url, location, or description. Email address must be valid if passed. Refer to Twitter RESTful API instructions on length allowed for other members
	 * @return string
	 */
	function updateProfile( $fields = array() )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
	    $postdata = array();
	    foreach( $fields as $pk => $pv ) :
	        switch( $pk ) 
	        {
	            case 'name' :
	                $postdata[$pk] = (string) substr( $pv, 0, 20 );
	                break;
	            case 'email' :
	                if( preg_match( '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $pv ) )
	                    $postdata[$pk] = (string) $pv;
	                break;
	            case 'url' :
	                $postdata[$pk] = (string) substr( $pv, 0, 100 );
	                break;
	            case 'location' :
	                $postdata[$pk] = (string) substr( $pv, 0, 30 );
	                break;
	            case 'description' :
	                $postdata[$pk] = (string) substr( $pv, 0, 160 );
	                break;
	            default :
	                break;
	        }
	    endforeach;
	    
	    $request = 'http://twitter.com/account/update_profile.' . $this->type;
	    return $this->objectify( $this->process( $request, $postdata ) );
	}
	
	/**
	 * Pass an array of values to Twitter to update Twitter profile colors
	 * @param array Required. All array members are optional. Optional color fields are: profile_background_color, profile_text_color, profile_link_color, profile_sidebar_fill_color, profile_sidebar_border_color
	 * @return string
	 */
	function updateColors( $colors = array() )
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
	    $postdata = array();
	    foreach( $colors as $ck => $cv ) :
	        if( preg_match('/^(?:(?:[a-f\d]{3}){1,2})$/i', $hex) ) :
                $postdata[$ck] = (string) $cv;
            endif;
	    endforeach;
	    
		$request = 'http://twitter.com/account/update_profile_colors.' . $this->type;
	    return $this->objectify( $this->process( $request, $postdata ) );
	}
	
	/**
	 * Rate Limit API Call. Sometimes Twitter needs to degrade. Use this non-ratelimited API call to work your logic out
	 * @return integer|boolean 
	 */
	function ratelimit()
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
		$request = 'http://twitter.com/account/rate_limit_status.' . $this->type;
		return $this->objectify( $out );
	}
	
	/**
	 * Rate Limit statuses (extended). Provides helper data like remaining-hits, hourly limit, reset time and reset time in seconds
	 * @deprecated
 	 */
	function ratelimit_status()
	{
		return $this->ratelimit();
	}
	
	/****** Tests ******/
	
	/**
	 * Detects if Twitter is up or down. Chances are, it will be down. ;-) Here's a hint - display CPM ads whenever Twitter is down
	 * @return boolean
	 */
	function twitterAvailable()
	{
	    if( !in_array( $this->type, array( 'xml','json' ) ) )
	        return false;
	        
		$request = 'http://twitter.com/help/test.' . $this->type;
		if( $this->objectify( $this->process($request) ) == 'ok' )
			return true;
		
		return false;
	}
	
	/**
	 * Any prescheduled maintenance?
	 * @deprecated
	 * @return false
	 */
	function maintenanceSchedule()
	{
		return false;
	}
	
	/****** Private and Helpers Methods ******/
	
	/**
	 * Uses the http://is.gd API to produce a shortened URL. Pluggable by extending the twitter class
	 * @param string $url The URL needing to be shortened
	 * @return string
	 */
	function shorturl( $url )
	{
		// Using is.gd because it's good
		$request = 'http://is.gd/api.php?longurl=' . $url;
		return $this->process( $request );
	}
	
	/**
	 * Internal function where all the juicy curl fun takes place
	 * this should not be called by anything external unless you are
	 * doing something else completely then knock youself out.
	 * @access private
	 * @param string $url Required. API URL to request
	 * @param string $postargs Optional. Urlencoded query string to append to the $url
	 */
	function process($url,$postargs=false)
	{
	    $url = ( $this->suppress_response_code ) ? $url . '&suppress_response_code=true' : $url;
		$ch = curl_init($url);
		if($postargs !== false)
		{
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postargs);
        }
        
		if($this->username !== false && $this->password !== false)
			curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password );
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        if( $this->debug ) :
            curl_setopt($ch, CURLOPT_HEADER, true);
        else :
            curl_setopt($ch, CURLOPT_HEADER, false);
        endif;
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

        $response = curl_exec($ch);
        
        $this->responseInfo=curl_getinfo($ch);
        curl_close($ch);
        
        
        if( $this->debug ) :
            $debug = preg_split("#\n\s*\n|\r\n\s*\r\n#m", $response);
            echo'<pre>' . $debug[0] . '</pre>'; exit;
        endif;
        
        if( intval( $this->responseInfo['http_code'] ) == 200 )
			return $response;    
        else
            return false;
	}
	
	/***DEPRECATED***/
	/**
	 * Returns a list of the users currently featured on the site with their current statuses inline.
	 * @deprecated
	 * @return string
	 */
	function featured()
	{
        return false;
	}
	
	/**
	 * Updates Geo location
	 * @deprecated
	 * @param string $location Required. 
	 * @return string
	 */
	function updateLocation( $location )
	{
		return $this->updateProfile( array( 'location' => $location ) );
	}
	
	/**
	 * Send an authenticated request to Twitter for the timeline of authenticating users friends. 
	 * Returns the last 20 updates by default
	 * @deprecated true
	 * @param boolean|integer $id Specifies the ID or screen name of the user for whom to return the friends_timeline. (set to false if you want to use authenticated user).
	 * @param boolean|integer $since Narrows the returned results to just those statuses created after the specified date.
	 * @return string
	 */
	function friendsTimeline( $id = false, $since = false, $since_id = false, $count = 20, $page = false )
	{
		return $this->userTimeline( $id, $count, $since, $since_id, $page );
	}
	

	/**
	 * Function to prepare data for return to client
	 * @access private
	 * @param string $data
	 */
	function objectify( $data )
	{
		if( $this->type ==  'json' )
			return json_decode( $data );

		else if( $this->type == 'xml' )
		{
			if( function_exists('simplexml_load_string') ) :
			    $obj = simplexml_load_string( $data );			        
			endif;
			return $obj;
		}
		else
			return false;
	}
	
	/**
	 * Function to piece together a cohesive query string
	 * @access private
	 * @param array $array
	 * @return string
	 */
	function _glue( $array )
	{
	    $query_string = '';
	    foreach( $array as $key => $val ) :
	        $query_string .= $key . '=' . rawurlencode( $val ) . '&';
	    endforeach;
	    
	    return '?' . substr( $query_string, 0, strlen( $query_string )-1 );
	}
}

/**
 * Wrapper class around the Twitter Search (formerly, and affectionately known as Summize)
 * @author Aaron Brazell <aaron@technosailor.com>
 * @author Keith Casey <caseysoftware@gmail.com>
 * @version 1.1
 * @package php-twitter
 * @subpackage classes
 */

class summize extends twitter
{
	/**
	 * Can be set to JSON (requires PHP 5.2 or the json pecl module) or Atom - json|atom
	 * @var string
	 */
	var $stype='json';	
	
	function search( $terms=false, $callback=false )
	{
		if( !$terms )
			return false;
		
		$qs = array();
		$request = 'http://search.twitter.com/search.' . $this->stype;
		
		$qs[] = 'q=' . rawurlencode( $terms );
		if( $callback && $this->stype == 'json' )
			$qs[] = 'callback=' . $callback;
			
		return $this->objectify( $this->process($request . '?' . implode('&',$qs) ) );
	}
	
	function trends( $callback = false )
	{
	    $qs = ( $callback ) ? '?callback=' . $callback : '';
	    $request = 'http://search.twitter.com/trends.' . $this->stype . $qs;
	    
	    return $this->objectify( $this->process( $request ) );
	}
}

?>