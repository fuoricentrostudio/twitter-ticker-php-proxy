/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

(function($){
    
    $('<span> Cariamento twitter .. </span>').prependTo('#twitter-ticker');
    
    $.getJSON('proxy.php', function(data) {
      var items = [];
      
      $.each(data, function(key, tweet) {
        items.push('<li id="tweet-' + key + '"><p class="tweet-content"><a target="_blank" href="http://twitter.com/'+tweet.user.screen_name+'">@'+tweet.user.screen_name+'</a><span class="tweet-text" title="'+tweet.text+'">:&nbsp;' +  tweet.html_text + '</span><p><span class="tweet-age">'+calcTweetAge(tweet.created_at).toString()+'</span> <a class="retweet" href="http://twitter.com/home?status=' + encodeURIComponent(tweet.text) + '" target="_blank" >&nbsp;<span title="Retweet">Retweet</span></a></li>');
      });
      
      $('<div class="wrapper" />').prependTo('#twitter-ticker');
      
      $('<ul/>', {
        'class': 'tweet-list',
        html: items.join('')
      }).appendTo('#twitter-ticker .wrapper');
       
       if(items){
           $('<a class="tweet-prev" href="#"></a>').on('click',function () { tick(); return false; } ).appendTo('#twitter-ticker .wrapper');
           $('<a class="tweet-next" href="#"></a>').on('click',function () { reversetick(); return false; }).appendTo('#twitter-ticker .wrapper');
       }
       
       var twInt;
        
       function tick(){
            $('#twitter-ticker li:first').slideUp( function () { $(this).appendTo($('#twitter-ticker ul')).slideDown(); });
       }
       
       function reversetick(){
            $('#twitter-ticker li:last').hide().prependTo($('#twitter-ticker ul')).slideToggle();
       }
       twInt = setInterval(function(){ tick () }, 7000);

       $('#twitter-ticker').hover(
            function(e){ clearInterval(twInt); },
            function(e){ twInt = setInterval(function(){ tick () }, 7000); }
       );
        
    });


    function calcTweetAge(post_date_string){
       
       post_datetime = new Date( post_date_string );
       now_datetime = new Date();
       difference = now_datetime.getTime()-post_datetime.getTime();
       sec_difference = Math.ceil(difference/(1000));
       
       if(sec_difference < 60)
           return Math.ceil(sec_difference).toString()+' secondi f&agrave;';
       if(sec_difference < (60*60))
           return Math.ceil(sec_difference/60).toString()+' minuti f&agrave;';
       if(sec_difference < (60*60*24))
           return Math.ceil(sec_difference/(60*60)).toString()+' ore f&agrave;';
       if(sec_difference > (60*60*24))
           return Math.ceil(sec_difference/(60*60*24)).toString()+' giorni f&agrave;';
      
       return '';
    }
    
    function truncate (text, limit, append) {
        if (typeof text !== 'string')
            return '';
        if (typeof append == 'undefined')
            append = '...';
        var parts = text.split(' ');
        if (parts.length > limit) {
            // loop backward through the string
            for (var i = parts.length - 1; i > -1; --i) {
                // if i is over limit, drop this word from the array
                if (i+1 > limit) {
                    parts.length = i;
                }
            }
            // add the truncate append text
            parts.push(append);
        }
        // join the array back into a string
        return parts.join(' ');
    }

})(jQuery)