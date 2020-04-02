var g2b = function() {
	var MESSAGES = {
		SHOWRECHARGE: "msg_recharge",
		GETORDERNO: "msg_get_order_no",
		RETURNORDERNO: "mgs_ret_order_no",
		RECHARGE_CALLBACK: "msg_recharge_cb",
		INIT: "msg_init",
		SET_SHARE: "msg_share_init",
		SHOWQRCODE: "msg_qr",
		RETURN_FOCUSSTATE: "msg_focus",
		SHARE_CALLBACK: "msg_share_cb",
		REPORTDATA: "msg_report",
		SENDTODESKTOP: "msg_send_desktop",
		SENDTODESKTOP_CALLBACK: "msg_send_desktop_cb",
		RECHARGE_PAY: "msg_pay",
		CHECKSHARE: "msg_check",
		FOCUS_GETSTATE: "msg_get_foucus",
		INIT_CALLBACK: "msg_init_cb",
		RETURNSHARE: "msg_ret_share",
		ON_LOGINERROR: "msg_error",
		FOCUS_RETURNSTATE: "msg_ret_focus",
		CHECKDOWNLOAD: "msg_check_download",
		RETURNDOWNLOAD: "msg_download"
	};
	var passType;
	var passId;
	var appid = "";
	var shareInfo;
	var rechargeItems;
	var citem;
	var gameId;
	var itemId;
	var itemName;
	var isListenerInited = false;
	var gameFrame;
	var reyunAppId = "";
	var reyunUrl = "http://www.gank-studio.com";
	var passHost;
	var ifFocused;
	var pf_obj;
	var pfsdk;
	var pf_params = {};
	var login_params = {};
	var UA = navigator.userAgent;
	var isAndroid = (UA.indexOf("Android") > -1);
	var server_id;
	var getParameter = function(key) {
		var href = location.search;
		var p = href.substr(1, href.length - 1).split("&");
		for (var i = 0; i < p.length; i++) {
			if ((p[i].split("="))[0] == key) {
				return p[i].split("=")[1]
			}
		}
	};
	var getScreenInfo = function() {
		var fHeight = window.frameHeight;
		var fWidth = (frameWidth > frameHeight ? frameHeight * 0.6 : frameWidth);
		console.log(fWidth);
		return "&frameHeight=" + fHeight + "&frameWidth=" + fWidth
	};
	var getParameters = function() {
		var href = location.search;
		var param = {};
		if (!href.length) {
			return param
		}
		var p = href.substr(1, href.length - 1).split("&");
		for (var i = 0; i < p.length; i++) {
			param[(p[i].split("="))[0]] = p[i].split("=")[1]
		}
		return param
	};
	var getPassId = function() {
		return getParameter("passIds")
	};
	var createIframe = function(src, id, tgt) {
		console.log("src " + src);
		var ifm = document.createElement("iframe");
		ifm.scrolling = "no";
		// debugtolog(document.getElementById("recharge").getAttribute("style"));
		ifm.style.width = Math.ceil(frameWidth > frameHeight ? frameHeight * 0.6 : frameWidth) + "px";
		ifm.style.height = (window.frameHeight || window.innerHeight) + "px";
		ifm.style.margin = "auto";
		ifm.style.position = "absolute";
		ifm.style.top = "0";
		ifm.style.left = "0";
		ifm.style.backgroundColor = "white";
		ifm.id = id;
		ifm.frameborder = "no";
		ifm.style.border = "none";
		ifm.border = "0px";
		ifm.style.zIndex = 99;
		(tgt || (document.body)).appendChild(ifm);
		ifm.src = src;
		return ifm
	};
	var object2search = function(param) {
		if (typeof param != "object") {
			console.error("参数不合法");
			return
		}
		var search = "?";
		var keys = Object.keys(param);
		for (var i = 0; i < keys.length; i++) {
			if (i == 0) {
				search += (keys[i] + "=" + param[keys[i]])
			} else {
				search += "&" + (keys[i] + "=" + param[keys[i]])
			}
		}
		return search
	};
	var getSearch = function() {
		return location.search
	};
	var setStorange = function(key, value) {
		localStorage.setItem(key, value)
	};
	var getStorange = function(key) {
		localStorage.getItem(key)
	};
	var gameJumpTo = function(url) {
		top.window.location.href = url
	};
	var postMessage = function(msg, d) {
		var data = {};
		data.identify = "g2460";
		data.msg = msg;
		data.data = d;
		gameFrame.contentWindow.postMessage(data, "*")
	};
	var getDataXHR = function(url, cb, param, contenttype) {
		var param = param || {};
		var type = param.type || "get";
		var data = param.data || null;
		try {
			var xhr = new XMLHttpRequest();
			xhr.open(type, url, true);
			xhr.onreadystatechange = function() {
				if (xhr.readyState == 4) {
					var responseData = JSON.parse(xhr.responseText);
					if (responseData.c < 0) {
						toastMsg(responseData.m);
						return
					}
					if (xhr.responseText == "error") {
						alert("请求" + url + "返回error");
						return
					}
					cb && cb(responseData)
				}
			};
			if (contenttype) {
				try {
					xhr.setRequestHeader("Content-Type", contenttype)
				} catch (e) {
					alert(e)
				}
			}
			xhr.send(data)
		} catch (e) {
			console.error("xhr出错", e);
			return false
		}
	};
	var toastMsg = function(msg, delay) {
		var div = document.createElement("div");
		div.id = "toast";
		div.style.width = "260px";
		div.style.height = "130px";
		div.style.margin = "auto";
		div.style.fontSize = "16px";
		div.style.position = "absolute";
		div.style.top = "0";
		div.style.bottom = "0";
		div.style.right = "0";
		div.style.left = "0";
		div.style.zIndex = "999";
		var text = document.createElement("div");
		text.innerText = msg;
		text.style.margin = "auto";
		text.style.position = "absolute";
		text.style.top = "0";
		text.style.bottom = "0";
		text.style.right = "0";
		text.style.left = "0";
		text.style.width = "250px";
		div.appendChild(text);
		div.classList.add("toast");
		var node = (document.getElementById("recharge") && document.getElementById("recharge").style.display == "block") ? document.getElementById("recharge") : document.body;
		node.appendChild(div);
		setTimeout(function() {
			node.removeChild(div)
		}, delay || 2000)
	};
	var showConfirm = function(msg, cb, cancelcb) {
		if (document.getElementById("confirm")) {
			document.body.removeChild(document.getElementById("confirm"))
		}
		var div = document.createElement("div");
		var node = (document.getElementById("recharge") && document.getElementById("recharge").style.display == "block") ? document.getElementById("recharge") : document.body;
		div.id = "confirm";
		div.style.width = "270px";
		div.style.height = "160px";
		div.style.position = "absolute";
		div.style.margin = "auto";
		div.style.top = "30%";
		div.style.left = 0;
		div.style.right = 0;
		div.style.zIndex = "999";
		div.style.fontSize = "16px";
		div.innerText = msg;
		div.classList.add("toast");
		var button = document.createElement("button");
		button.style.position = "absolute";
		button.style.left = "40px";
		button.style.top = "120px";
		button.innerText = "确定";
		button.style.height = "30px";
		button.classList.add("button");
		button.classList.add("blue");
		button.onclick = function() {
			node.removeChild(div);
			cb && cb()
		};
		var button2 = document.createElement("button");
		button2.style.top = "120px";
		button2.style.position = "absolute";
		button2.style.right = "40px";
		button2.innerText = "取消";
		button2.style.height = "30px";
		button2.classList.add("button");
		button2.classList.add("blue");
		button2.onclick = function() {
			node.removeChild(div);
			cancelcb && cancelcb()
		};
		div.appendChild(button);
		div.appendChild(button2);
		node.appendChild(div)
	};
	var showRecharge = function(items) {
		if (items == undefined) {
			return;
		}
		rechargeItems = items;
		document.getElementById("recharge").style.display = "block";
		for (var i = 0; i < items.length; i++) {
			_setItem(items[i], i)
		}
	};
	var _setItem = function(data, index) {
		var div = document.createElement("div");
		var icon = document.createElement("img");
		icon.src = data.icon;
		icon.style.margin = "auto";
		icon.style.left = "20px";
		icon.style.top = "0";
		icon.style.bottom = "0";
		icon.style.position = "absolute";
		var title = document.createElement("div");
		title.style.top = "20px";
		title.style.position = "absolute";
		title.style.margin = "auto";
		title.style.right = "0";
		title.style.left = "0";
		title.innerText = data.itemName;
		var desc = document.createElement("div");
		desc.style.bottom = "20px";
		desc.style.position = "absolute";
		desc.style.margin = "auto";
		desc.style.right = "0";
		desc.style.left = "0";
		desc.innerText = data.desc;
		var button = document.createElement("div");
		button.style.top = "0";
		button.style.bottom = "0";
		button.style.position = "absolute";
		button.style.margin = "auto";
		button.style.right = "2px";
		button.innerText = "购买";
		button.classList.add("buybtn");
		button.onclick = function() {
			// document.getElementById("loading").style.display = "block";
			itemId = data.id;
			itemName = data.itemName;
			cindex = index;
			postMessage(MESSAGES.GETORDERNO, {
				amount: data.amount,
				id: data.id
			})
		};
		div.appendChild(icon);
		div.appendChild(title);
		div.appendChild(desc);
		div.appendChild(button);
		div.classList.add("payItem");
		document.getElementById("items").appendChild(div)
	};
	var getcitem = function() {
		return rechargeItems[citem]
	};
	var initMessageListener = function() {
		console.info("g2b init listener");
		window.addEventListener("message", function(e) {
			var msg = e.data;

			if (msg.identify && msg.identify == "g2460") {
				switch (msg.msg) {
					case MESSAGES.INIT:
						init(msg.data);
						break;
					case MESSAGES.SHOWRECHARGE:
						showRecharge(msg.data);
						break;
					case MESSAGES.RETURNORDERNO:
						var orderData = msg.data.orderData;
						var amount = msg.data.amount;
						try {
							pay(amount, orderData)
						} catch (e) {
							alert(e)
						}
						break;
					case MESSAGES.SET_SHARE:
						setShareInfo(msg.data);
						pf_obj.showShare();
						break;
					case MESSAGES.SHOWQRCODE:
						pf_obj.showQrCode();
						break;
					case MESSAGES.REPORTDATA:
						pf_obj.reportData(msg.data);
						break;
					case MESSAGES.SENDTODESKTOP:
						pf_obj.sendToDesktop();
						break;
					case MESSAGES.FOCUS_GETSTATE:
						pf_obj.checkFocus(msg.data);
						break;
					case MESSAGES.RECHARGE_PAY:
						pay(msg.data.amount, msg.data.orderData);
						break;
					case MESSAGES.CHECKSHARE:
						pf_obj.isOpenShare();
						break;
					case MESSAGES.CHECKDOWNLOAD:
						pf_obj.isDownloadable();
						break;
					case MESSAGES.ON_LOGINERROR:
						pf_obj.onLoginError();
						break
				}
			} else {
				console.log("msg got " + msg);
				switch (msg) {
					case 'shareSuccess':
						postMessage(MESSAGES.SHARE_CALLBACK, true)
						break;
					case 'shareCancel':
						postMessage(MESSAGES.SHARE_CALLBACK, false)
						break;
					case 'shareok': // qunhei
						postMessage(MESSAGES.SHARE_CALLBACK, true)
						break;
					default:

				}
			}
		})
	};

	function loadScript(url, callback) {
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.onload = function() {
			callback()
		};
		script.onerror = function() {
			script.parentNode.removeChild(script);
			setTimeout(function() {
				loadScript(url, callback)
			}, 1000)
		};
		script.src = url;
		document.getElementsByTagName("head")[0].appendChild(script)
	}
	var checkLogin = function(res, cb) {
		var passIds = getParameter("passIds");
		if (passIds && passIds.split(",").length <= 1) {
			cb && cb(1);
			login(res)
		} else {
			cb && cb(2)
		}
	};
	var login = function(res, cb) {
		console.log("g2b login");
		var passId = (res && res.passId) || getPassId();
		var appId = (res && res.appId) || getParameter("appId");
		var url = "/index.php/enter/login/" + passId + "/" + appId;
		try {
			setStorange("screenInfo", getScreenInfo());
			setStorange("appId", appId);
			setStorange("passId", passId)
		} catch (e) {
			// debugtolog(e)
		}

		if (passId == "yuewan") {
			loadScript("http://sdkv2.52wan.dkmol.net/www/js/aksdk.js", function() {

				AKSDK.login(function(status, data) {
					if (status == 0) {

						res['uid'] = data.uid;
						res['userid'] = data.userid;
						res['account'] = data.account;
						res['token'] = data.token;
						url = url + object2search(res);

						getDataXHR(url, function(data) {
							if (data.c == 0) {
								document.getElementById("gameDiv").style.display = "block";
								gameFrame = createIframe(data.d.url + getScreenInfo(), "gameFrame", document.getElementById("gameDiv"));
								document.body.removeChild(document.getElementById("gbox"));
								gameFrame.onload = function() {
									console.log("gameFrame loaded");
									if (!isListenerInited) {
										initMessageListener();
										isListenerInited = true
									}
									document.getElementById("loader").style.display = "none"
								};
								cb && cb()
							}
						});
					} else {
						alert('登陆失败');
					}

				});

			});
		} else {

			pf_params = res;
			url = url + object2search(res);
			// debugtolog(url);
			getDataXHR(url, function(data) {
				if (data.c == 0) {
					document.getElementById("gameDiv").style.display = "block";
					gameFrame = createIframe(data.d.url + getScreenInfo(), "gameFrame", document.getElementById("gameDiv"));
					if (data.d.orientation == "landscape") {
						gameFrame.width = "100%"
						gameFrame.height = "100%"
						gameFrame.style.width = "";
						gameFrame.style.height = "";
						document.getElementById("gameDiv").style.position = "";
					}
					document.body.removeChild(document.getElementById("gbox"));
					gameFrame.onload = function() {
						console.log("gameFrame loaded");
						if (!isListenerInited) {
							initMessageListener();
							isListenerInited = true
						}
						document.getElementById("loader").style.display = "none"
					};
					cb && cb()
				}
			})
		}



	};

	function getCookie(name) {
		var cookies = document.cookie;
		var cookieIndex = cookies.indexOf(name);
		if (cookieIndex != -1) {
			cookieIndex += name.length + 1;
			var cookie_end = cookies.indexOf(";", cookieIndex);
			if (cookie_end == -1) {
				cookie_end = cookies.length
			}
			var value = unescape(cookies.substring(cookieIndex, cookie_end))
		}
		return value
	}
	var init = function(data) {
		console.log("g2b init");
		passType = data.platform;
		passId = data.platform;
		appid = data.appid;
		server_id = data.server_id;
		gameId = data.game_id;
		shareInfo = data.shareInfo;

		if (data.test) {
			passHost = "http://" + location.host;
		} else {
			passHost = "http://h5sdk.zytxgame.com"
		}
		if (data.test) {
			loadScript("http://" + location.host + "/js/platform.js?v=" + new Date().getTime(), function() {
				loadScript("http://" + location.host + "/js/platforms/PLATFORM_" + passType + ".js?v=" + new Date().getTime(), function() {
					pf_obj = new pf(g2b, shareInfo, pf_params, {
						appId: appid,
						passId: passId,
						gameId: gameId
					});
					console.log(pf_obj)
				})
			})
		} else {
			loadScript(passHost + "/js/platform.js?v=" + new Date().getTime(), function() {
				loadScript(passHost + "/js/platforms/PLATFORM_" + passType + ".js?v=" + new Date().getTime(), function() {
					pf_obj = new pf(g2b, shareInfo, pf_params, {
						appId: appid,
						passId: passId,
						gameId: gameId
					});
					console.log(pf_obj)
				})
			})
		}
	};

	function setBackEvent() {
		"pushState" in window.history && (window.history.pushState({
			title: document.title,
			url: location.href
		}, document.title, location.href), setTimeout(function() {
			window.addEventListener("popstate", function(a) {
				showConfirm("确认要退出游戏么？", function() {
					closePayWindow();
					window.history.go(-1)
				}, function() {
					"pushState" in window.history && (window.history.pushState({
						title: document.title,
						url: location.href
					}, document.title, location.href))
				})
			})
		}, 1000))
	}

	function setShareInfo(infos) {}

	function setSpecialShare(data) {
		if (data.channel == 2000004) {}
	}
	var createPay = function(param, cb) {
		var param = param || {};
		var url = passHost + "/index.php/api/createPay" + param.search;
		getDataXHR(url, function(res) {
			var data = res;
			cb && cb(data)
		});
	};
	var checkFocus = function(checkdata) {};
	var reportData = function(data) {};
	var onLoginError = function(loginUrl) {
		top.location.href = loginUrl;
	};
	var reyunReport = function(data) {
		var action;
		if (data.action == "login") {
			action = "/receive/login";
			getDataXHR(reyunUrl + action, function() {}, {
				type: "post",
				data: JSON.stringify({
					appid: reyunAppId,
					who: data.openId,
					deviceid: data.openId,
					serverid: data.server,
					channelid: appid,
					idfa: "",
					idfv: "",
					level: -1
				})
			}, "application/x-www-form-urlencoded;charset=UTF-8");

		}
		if (data.action == "create_role") {
			action = "/receive/register";
			getDataXHR(reyunUrl + action, function() {}, {
				type: "post",
				data: JSON.stringify({
					appid: reyunAppId,
					who: data.openId,
					deviceid: data.openId,
					serverid: data.server,
					channelid: appid,
					idfa: "",
					idfv: "",
					accounttype: "",
					gender: "",
					age: ""
				})
			}, "application/x-www-form-urlencoded;charset=UTF-8")
		}
	};
	var showQrCode = function() {};
	var isOpenShare = function() {};
	var pay = function(amount, orderData) {
		pf_obj.pay(amount, orderData);
	};
	return {
		init: init,
		checkLogin: checkLogin,
		getParameters: getParameters,
		getParameter: getParameter,
		object2search: object2search,
		getDataXHR: getDataXHR,
		loadScript: loadScript,
		getScreenInfo: getScreenInfo,
		login: login,
		toastMsg: toastMsg,
		showConfirm: showConfirm,
		showQrCode: showQrCode,
		citem: getcitem,
		createPay: createPay,
		MESSAGES: MESSAGES,
		postMessage: postMessage
	}
}();
