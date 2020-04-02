(function() {
	var errtimes = 0;
	// console.log(jsVersion);
	function loadScript(url, callback) {
		var script = document.createElement("script");
		script.type = "text/javascript";

		script.onload = function() {
			callback && callback();
		};
		script.onerror = function() {
			script.parentNode.removeChild(script);
			if (errtimes < 2) {
				loadScript('//api.baizegame.com/js/jssdk/g2b_wxh5.js?v='+ new Date().getTime());
			} else {
				setTimeout(function() {
					loadScript(url + new Date().getTime());
				}, 1000);
			}
			errtimes++;
		};
		script.src = url // + '?v=' + gameVersion;
		document.getElementsByTagName("head")[0].appendChild(script);
	}
	loadScript('//api.baizegame.com/js/jssdk/g2b_wxh5.js?v='+ new Date().getTime());
})();
