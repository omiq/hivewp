<?php

// Set up our query
//$query = '{"jsonrpc":"2.0","method":"condenser_api.get_discussions_by_blog","params":[{"tag":"makerhacks","limit":30}],"id":0}';

$query = 'https://omiq.ca/Hive/feed.py?blog=%hivewp%&tag=%%&filter=NULL';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $query);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

// Get the response from the API
$response = curl_exec($ch);

// Close the connection
curl_close($ch);

// Get the blog posts
$posts=json_decode($response, TRUE);
//print_r($posts);


// Iterate through the returned posts
for($x = 0; $x < count($posts); $x++) {

    // Pull out specifics
    $post=$posts[$x];
    $meta=json_decode($post['json_metadata'],TRUE);
    $image=$meta['image'];
    $tags=join(', ',$meta['tags']);

    // Don't Show Cross-Posts
    if(stristr($tags, 'cross-post')==FALSE){
        $link = "https://peakd.com/@".$post['author']."/".$post['permlink'];

        print_r($post);
    }
    
}
//
//print($content);
//*/
?>