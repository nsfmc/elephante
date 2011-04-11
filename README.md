# Elephanté

[elephanté](http://www.flickr.com/photos/subliminal/tags/elephante/) is a lightweight tumblr cache. It's not very smart, but it handles most situations pretty well.

Instead of hitting your tumblr each time you make a read, it checks the HTTP Last-Modified header against the last blog post it saw before downloading from the api. The result is that most of the time, elephanté is reading a cached json file for your blog. It handles new posts by merging them and, for now, resolves simple consistency problems.

It gives you a php assoc. array that behaves nearly identically to the tumblr api with the exception that it contains your whole blog's contents rather than *n posts* at a time.

## using

    $myblog = elephante('http://nsfmc.tumblr.com/', 'cache/nsfmc.json');

that's it, you can do pagination on your blog by setting a page number in the query string and setting the number of posts per page, roughly like so:

    $num_posts = 8; // the number of posts fetched at once
    
    $page = (isset($_GET["page"]) && (int)$_GET['page'] > 0) ? (int)$_GET['page']-1 : 0 ;
    $startat = $num_posts * $page;
    
    $thispage = array_slice($myblog["posts"], $startat, $startat + $num_posts);

## setup

    chmod 777 cache

it also uses php curl, which you may or may not need to install

## license

mit license

let me know if you find this useful, elephante@generic.cx