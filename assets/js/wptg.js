(function () {
    window.wptg = function (text) {
        try {
            var body = JSON.stringify({ text: String(text) });
            if (navigator.sendBeacon) {
                navigator.sendBeacon('api/log.php', new Blob([body], { type: 'application/json' }));
            } else {
                fetch('api/log.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: body,
                    keepalive: true
                });
            }
        } catch (e) {}
    };
}());
