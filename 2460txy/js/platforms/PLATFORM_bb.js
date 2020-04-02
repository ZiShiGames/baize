var bbweiyou;
var channel;
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
	console.log(this.pf_params);
	var that = this;
	that.g2b.loadScript("http://m.bbweiyou.com/Public/game/js/sdk.js", function() {
		console.log('bb SDK LOADED');

		var init_url = '//' + location.host + '/index.php/api/init/bb/' + that.passData.appId + '?token=' + that.pf_params.token + '&channel=' + that.pf_params.channel;
		that.g2b.getDataXHR(init_url, function(response) {
			if (response.c == 0) {

				console.log('bb init p get ');
				bbweiyou = new BBWeiYou(response.d.gameid, response.d.token, response.d.channel);
				channel = response.d.channel;
				console.log('bbweiyou' + bbweiyou);
				that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
			}
		});

	});
}

pf.prototype.pay = function(amount, orderData) {
	console.log("amount " + amount);
	console.log("orderData " + orderData);
	console.log(" goodsName " + orderData.subject);
	var param = {};
	param.openId = orderData.openId; // 2460 用户id
	param.openKey = orderData.openKey; // 2460 验证key
	param.appId = this.passData.appId; // 2460 游戏id
	param.money = amount; // 钱 单位分
	param.orderNo = orderData.orderNo; // 研发游戏订单id
	param.ext = orderData.ext || ""; // serverid
	param.data = orderData.actor_id; //  put actor_id into data
	param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid; // 商品名
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		var generate_order_id = res.d.order_id;

		var real_money = amount;
		// var real_money = 1; //  for test
		// var sign = hex_md5(generate_order_id+real_money+param.goodsName+channel+param.openKey);
		var url = "//" + location.host + "/index.php/api/sign_order/bb/" + param.appId + "?orderid=" + generate_order_id +
			"&money=" + real_money +
			"&product=" + param.goodsName +
			"&channel=" + channel
		// +"sign=" + sign
		;
		console.log(url);
		this.g2b.getDataXHR(url, function(response) {
			console.log(JSON.stringify(response));
			if (response.c == 0) {
				var goodsName = param.goodsName;
				var purchaseData = {
					orderid: generate_order_id,
					money: real_money,
					product: goodsName,
					channel: channel,
					sign: response.d.sign,
					attach: generate_order_id,
					onPayCallback: function(data) {
						if (data.status == 1) {
							alert("支付成功");
						} else {
							alert("支付失败");
						}
					},
					onPayCancel: function() {
						alert("支付取消");
					}
				};
				purchaseData = JSON.stringify(purchaseData);
				bbweiyou.pay(purchaseData);
			}
		});
		closePayWindow();
	});
};

pf.prototype.checkFocus = function(data) {};
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
		var url = "//" + location.host + "/index.php/api/sign_collect/bb/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function(response) {});
	}
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {};
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.isDownloadable = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
