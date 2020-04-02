var u;
var sdklogindomain;
var sdkloginmodel;
var pf = function(g2b, shareInfo, pf_params, passData) {
	this.g2b = g2b;
	this.shareInfo = shareInfo;
	this.pf_params = pf_params;

	this.passData = passData;
	p_name = passData.passId;
	this.reyunurl = "";
	this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {
	console.log(this.passData);
	var that = this;
	this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
	sdklogindomain = this.pf_params.sdklogindomain;
	sdkloginmodel = this.pf_params.sdkloginmodel;
};

pf.prototype.pay = function(amount, orderData) {
	var param = {};
	param.openId = orderData.openId;
	param.openKey = orderData.openKey;
	param.appId = this.passData.appId;
	param.money = amount;
	param.orderNo = orderData.orderNo;
	// param.orderNo = orderData.orderNo;
	param.ext = orderData.ext || "";
	param.data = orderData.actor_id; //  put actor_id into data
	param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
	param.platform = this.pf_params.passId;
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		var generate_order_id = res.d.order_id;

		var money = amount;
		// var money = 1; //  for test
		var uid = res.d.userId; // 平台用户id
		var goodsName = orderData.subject;
		var cpOrderId = generate_order_id;
		var ext = cpOrderId;

		var sign_url = 'http://h5sdk.zytxgame.com/index.php/api/sign_order/' + param.platform + '/' + param.appId + "?uid=" + uid +
			"&goodsName=" + goodsName + "&money=" + money + "&cpOrderId=" + cpOrderId + "&ext=" + ext;
		console.log(sign_url);
		this.g2b.getDataXHR(sign_url, function(response) {
			var sign = response.d.sign;
			var gameid = response.d.gameid;
			var paydata = JSON.stringify({
				'amount': money,
				'channelExt': ext,
				'game_appid': gameid,
				'props_name': goodsName,
				'trade_no': cpOrderId,
				'user_id': uid,
				'sign': sign,
			});
			console.log(paydata);
			var pay_url = "http://" + sdklogindomain + "/" + sdkloginmodel + "/Game/paysdk/?game_appid=" + gameid + "&user_id=" + uid + "&trade_no=" + cpOrderId + "&props_name=" + goodsName + "&channelExt=" + ext + "&amount=" + money + "&sign=" + sign;
			var iframe = document.createElement('iframe');
			iframe.id = "payurl_mainframe";
			iframe.name = "jsmain";
			iframe.src = pay_url;
			iframe.setAttribute("scrolling", "yes");
			iframe.setAttribute("frameborder", 0);
			iframe.style.position = "fixed";
			iframe.style.top = 0;
			iframe.style.height = "100vh";
			iframe.style.width = "100vw";
			iframe.style.maxWidth = "1000px";
			iframe.style.display = "";
			iframe.style.visibility = "inherit";
			iframe.style.zIndex = 99999;
			iframe.style.overflow = "visible";
			iframe.style.position = "absolute";
			iframe.style.backgroundRepeat = "no-repeat";
			iframe.style.backgroundSize = "cover";
			document.body.appendChild(iframe);
			window.addEventListener('message', function(e) {
				//监听运营方返回消息
				console.log(" kuooe 支付返回：" + JSON.stringify(e));
				if (e.data.event == 'pay_result') {

					if (e.data.status == 1) {
						var obj = document.getElementById("payurl_mainframe");
						document.body.removeChild(obj);
						// 支付成功
					} else if (e.data.status == -1) {
						// 放弃支付
						var obj = document.getElementById("payurl_mainframe");
						document.body.removeChild(obj);
					} else if (e.data.status == 0) {
						// 暂未支付
					}
				}
			}, false);
		});
		closePayWindow();

	});
};
pf.prototype.checkFocus = function(data) {
	this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
pf.prototype.reportData = function(data) {

	if (data.action == 'enterGame') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		// var nickName = encodeURIComponent(data.rolename);
		var nickName = data.rolename;
		var level = data.rolelevel;
		var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;


		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'create_role') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		// var nickName = encodeURIComponent(data.rolename);
		var nickName = data.rolename;
		var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/" + this.pf_params.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function(response) {});
	}
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
	console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.isDownloadable = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

// pf.prototype.showShare = function() {
// this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, false);
// }
