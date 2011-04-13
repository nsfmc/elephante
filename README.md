# Elephanté

[elephanté](http://www.flickr.com/photos/subliminal/tags/elephante/) is a lightweight tumblr cache. It's not very smart, but it handles most situations pretty well.

Instead of hitting your tumblr each time you make a read, it checks the HTTP Last-Modified header against the last blog post it saw before downloading from the api. The result is that most of the time, elephanté is reading a cached json file for your blog. It handles new posts by merging them and, for now, resolves simple consistency problems.

In the rare instances that tumblr is down, it happily serves you a locally-hosted cache of your blog (yay!)

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

that's it.

## requirements

php curl, which basically needs to be compiled in to your php (or possibly apt-gotten).

php's `json_decode` and `json_encode` which are basically php5+ (but seriously, let's not kid ourselves, this shouldn't be an issue anymore).

also, if like me, you happen to use anonymous functions in `array_map` and `array_filter` then you already know that you need at least php 5.3+ in order to use those.

## license

elephante's php is licensed under an mit license

    Copyright (C) 2011 by Marcos Ojeda, breakfastsandwich.org

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.


let me know if you find this useful, elephante@generic.cx