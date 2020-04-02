var pf = function(g2b, shareInfo, pf_params, passData) {
	this.g2b = g2b;
	this.shareInfo = shareInfo;
	this.pf_params = pf_params;

	this.passData = passData;
	this.reyunurl = "";
	this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {
	this.g2b.getDataXHR('http://h5sdk.zytxgame.com/index.php/api/focus/chengt/' + this.passData.appId, function(response) {
		if (response.c == 0) {
			console.log(' get jump ');
			console.log(response.d.url);
			window.location = response.d.url;
		}
		this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
	});

};

pf.prototype.pay = function(amount, orderData) {
	console.log("amount " + amount);
	console.log("orderData " + JSON.stringify(orderData));
	var param = {};
	param.openId = orderData.openId;
	param.openKey = orderData.openKey;
	param.appId = this.passData.appId;
	param.money = amount;
	param.orderNo = orderData.orderNo;
	param.ext = orderData.ext || "";
	param.data = orderData.actor_id + "__" + orderData.appUserName; //  put actor_id into data
	param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
	var search = this.g2b.object2search(param);
	this.g2b.pay({
		search: search
	}, function(res) {
		var generate_order_id = res.d.order_id;
		var appId = param.appId;
		var orderId = generate_order_id;
		var subject = orderdata.subject;
		var money = amount / 100;
		var userId = res.d.user_id;
		var buyAmount = 1;
		var url = "http://www.instaplay.games/game/pay?addId=" + appId + "&orderId=" + orderId + "&subject=" + subject +
			"&money=" + money + "&money=" + amount + "&userId=" + userId + "&buyAmount=" + buyAmount;
		window.top.location.href = url;



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
		var nickName = encodeURIComponent(data.rolename);
		var level = data.rolelevel;
		var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;


		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'create_role') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		var nickName = encodeURIComponent(data.rolename);

		var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;

		var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/chengt/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function(response) {});
	}
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
	Hwwsdk.ejectFollow();
};
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
