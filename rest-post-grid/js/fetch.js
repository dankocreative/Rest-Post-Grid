(function(){
    function init(config){
        function fetchPosts(){
            var form = new FormData();
            form.append('action','drpg_fetch_posts');
            form.append('nonce', drpg_ajax.nonce);
            form.append('atts', JSON.stringify(config.atts));
            fetch(drpg_ajax.url, {method:'POST', credentials:'same-origin', body: form})
                .then(function(r){ return r.text(); })
                .then(function(html){
                    var el = document.getElementById(config.container);
                    if(el){ el.innerHTML = html; }
                });
        }
        setInterval(fetchPosts, config.interval * 1000);
    }
    document.addEventListener('DOMContentLoaded', function(){
        if(window.drpgQueues && Array.isArray(window.drpgQueues)){
            window.drpgQueues.forEach(init);
        }
    });
})();
