var sogou_game;
var p_name = 'threefive';
var game35;
var uid;
var token;
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
	uid = this.pf_params.uid;
	token = this.pf_params.token;
	var url = "http://www.3500.com/statics/js/lib.v1.js";
	this.g2b.getDataXHR('http://h5sdk.zytxgame.com/index.php/api/focus/' + p_name + '/' + this.passData.appId, function(response) {
		this.g2b.loadScript(url, function() {
			var data = {
				"uid": uid,
				"token": token,
			};
			game35 = new Game35(data);
			this.g2b.postMessage(g2b.MESSAGES.INIT_CALLBACK);
		})
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
	param.platform = p_name;
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		var orderid = res.d.order_id;
		// var ext = orderid;
		var realmoney = amount;
		// var realmoney = 1;
		var producta = orderData.subject;

		var sign_url = 'http://h5sdk.zytxgame.com/index.php/api/sign_order/' + p_name + '/' + param.appId + "?orderid=" + orderid + "&money=" + realmoney + "&product=" + producta;
		this.g2b.getDataXHR(sign_url, function(response) {
			var app_id = response.d.appid;
			// var sign = response.d.sign;
			var paydata = {
				"orderid": orderid,
				"money": realmoney,
				"product": producta,
				"appid": app_id,
				"sign": response.d.sign,
				"ext": orderid,
			};
			game35.pay(paydata);
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

		var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/" + p_name + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function(response) {});
	}
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
	console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);

};
pf.prototype.isDownloadable = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};


pf.prototype.showShare = function() {
	var that = this;
	// console.log(that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true));
	var info = {
		"title": "兄弟再聚，沙城争霸",
		"content": "无兄弟，不传奇，寻昔日兄弟，战热血沙城。",
	};
	game35.share(info);
	// alert(JSON.stringify(game35.share(info)));
	game35.onShareOK(function() {
		var that = this;
		that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
	});

}
