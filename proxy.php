<?php

/*
 * Multi user Twitter Proxy 
 * Copyrght 2012. Brando Meniconi @ FuoricentroStudio (b.meniconi@fuoricentrostudio.com)
 */

// Set your return content type
header('Content-type: application/json');

$users = array('vivido','vividolab','princi_vivido','follorep','jiratrack','roomshop','cmenzani','flaviomenzani','lderiu86','rcappello','alligatore','b4cc','pciccioni' );

$tweet_array = array();
$json_requests = array();

// Build requests URL array 
foreach($users as $user) 
    $json_requests[] = 'http://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&screen_name='.$user.'&count=5';

// Send concurrent requests to twitter  
$json_responses = multiRequest($json_requests);

// Decode json requests and append to array.
foreach ($json_responses as $json_response)
    $tweet_array = array_merge($tweet_array,json_decode($json_response));


if (!empty($tweet_array )) :
        foreach ($tweet_array as &$tweet) :
 
                // check if any entites exist and if so, replace then with hyperlinked versions
                if (!empty($tweet->entities->urls) || !empty($tweet->entities->hashtags) || !empty($tweet->entities->user_mentions)) {
                        
                        $patterns = array();
                        $replacements = array();
                        
                        usort($tweet->entities->urls,'url_strlensort');
                        
                        foreach ($tweet->entities->urls as $url) {
                                $patterns[] = '/'.preg_quote($url->url,'/').'/';
                                $replacements[]  = '<a href="'.$url->url.'">'.$url->url.'</a>';                                
                        }
                        
                        usort($tweet->entities->hashtags,'hash_strlensort');
                        
                        foreach ($tweet->entities->hashtags as $hashtag) {
                                $patterns[] = '/#'.preg_quote($hashtag->text).'/';
                                $replacements[] = '<a href="http://twitter.com/#!/search/%23'.$hashtag->text.'">#'.$hashtag->text.'</a>';
                        }
                            
                       usort($tweet->entities->user_mentions,'user_strlensort');
                        
                        foreach ($tweet->entities->user_mentions as $user_mention) {
                                $patterns[] = '/@'.preg_quote($user_mention->screen_name).'/i';
                                $replacements[] = '<a href="http://twitter.com/'.$user_mention->screen_name.'">@'.$user_mention->screen_name.'</a>'; 
                        }                        
                        
                        $tweet->html_text = preg_replace($patterns,$replacements,$tweet->text);                  
                                            
                }
    endforeach;  
endif; 

function url_strlensort($a,$b){
    return strlen($b->url)-strlen($a->url);
}

function hash_strlensort($a,$b){
    return strlen($b->text)-strlen($a->text);
}
function user_strlensort($a,$b){
    return strlen($b->screen_name)-strlen($a->screen_name);
}

// Randomize tweets
shuffle($tweet_array);

//Output aggregate and json-encoded tweet array
echo json_encode($tweet_array);

function multiRequest($data, $options = array()) {

  // array of curl handles
  $curly = array();
  // data to be returned
  $result = array();

  // multi handle
  $mh = curl_multi_init();

  // loop through $data and create curl handles
  // then add them to the multi-handle
  foreach ($data as $id => $d) {

    $curly[$id] = curl_init();

    $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
    curl_setopt($curly[$id], CURLOPT_URL,            $url);
    curl_setopt($curly[$id], CURLOPT_HEADER,         0);
    curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

    // post?
    if (is_array($d)) {
      if (!empty($d['post'])) {
        curl_setopt($curly[$id], CURLOPT_POST,       1);
        curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
      }
    }

    // extra options?
    if (!empty($options)) {
      curl_setopt_array($curly[$id], $options);
    }

    curl_multi_add_handle($mh, $curly[$id]);
  }

  // execute the handles
  $running = null;
  do {
    curl_multi_exec($mh, $running);
  } while($running > 0);

  // get content and remove handles
  foreach($curly as $id => $c) {
    $result[$id] = curl_multi_getcontent($c);
    curl_multi_remove_handle($mh, $c);
  }

  // all done
  curl_multi_close($mh);

  return $result;
}


?>

