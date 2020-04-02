var ldgame;

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
	var that = this;
	this.g2b.loadScript("http://h5.mobo168.com/open/sdk/ldgame.js", function() {
		var url = "//" + location.host + "/index.php/api/init/bais/" + that.passData.appId;

		this.g2b.getDataXHR(url, function(response) {
			if (response.c == 0) {

				ldgame = new ldgame({
					"app_key": response.d.AppKey,
				});

                this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
                ldgame.setwxsharecallback(function(){
                    that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
                });

			}
		});

	});

};

pf.prototype.pay = function(amount, orderData) {
	console.log("amount " + amount);
	console.log("orderData " + orderData);
	var param = {};
	param.openId = orderData.openId;
	param.openKey = orderData.openKey;
	param.appId = this.passData.appId;
	param.money = amount;
	param.orderNo = orderData.orderNo;
	param.ext = orderData.ext || "";
	param.data = orderData.actor_id; //  put actor_id into data
	param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
	param.platform = 'qunhei';
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		// alert("res " + res.d);
		var generate_order_id = res.d.order_id;
		var open_id = res.d.userId;
		var readl_money = amount / 100;
		// var readl_money = 0.01; // for test

		var paydata = {
			"open_id": res.d.userId,
			"total_fee": readl_money,
			"game_orderno": generate_order_id,
			'subject': orderData.subject,
		};

		var p_str = this.g2b.object2search(paydata);


		var url = "//" + location.host + "/index.php/api/sign_order/bais/" + param.appId + p_str;

		this.g2b.getDataXHR(url, function(response) {
			if (response.c == 0) {
				paydata.app_key = response.d.app_key;
				paydata.notify_url = response.d.notify_url;
				paydata.timestamp = response.d.timestamp;
				paydata.nonce = response.d.nonce;
				paydata.signature = response.d.sign;

				ldgame.pay(paydata, function(code, msg) {
					console(code + ',' + msg);
				});
			}
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
		var nickName = encodeURIComponent(data.rolename);
		var level = data.rolelevel;
		var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'create_role') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		var nickName = encodeURIComponent(data.rolename);

		var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;

		var url = "//" + location.host + "/index.php/api/sign_collect/bais/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function(response) {});
	}
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
	console.log("pf showQrCode called");

};
pf.prototype.showShare = function () {
    ldgame.sendmessage( { m: that.shareInfo.title, d: {} });

}
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.isDownloadable = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
