var p_name;
var recharge;
var p_appkey;
var appid;
var responsekey;
var reportkey;
var sdkInit;
var uid;
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
	var that = this;
	this.g2b.loadScript("http://h5sdk.zytxgame.com/js/md5.min.js", function() {
		this.g2b.getDataXHR("http://h5sdk.zytxgame.com/index.php/api/focus/taren/" + that.passData.appId, function(response) {
			console.log('ajax in' + passId + '  ' + p_name);
			this.g2b.loadScript("http://h5sdk.zytxgame.com/js/jquery-3.1.1.js", function() {});
			if (response.c == 0) {
				recharge = response.d.recharge;
				appid = response.d.appid;
				reportkey = response.d.reportkey;
				responsekey = response.d.responsekey;
				uid = response.d.uid;
				console.log(recharge + '  ' + appid + ' ' + reportkey + ' ' + responsekey);
				this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
			}

		});
	});

};

pf.prototype.pay = function(amount, orderData) {
	console.log("amount " + amount);
	console.log("orderData " + orderData);
	var param = {};
	var that = this;
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
		var generate_order_id = res.d.order_id;
		var userId = res.d.userId;
		var ext = generate_order_id;
		var readl_money = amount / 100;
		// var readl_money = 1 / 100; // for test
		var url = "http://h5sdk.zytxgame.com/index.php/api/sign_order/" + passId + "/" + param.appId + "?order_id=" + generate_order_id +
			"&money=" + readl_money + "&userId=" + userId + "&appid=" + appid;

		this.g2b.getDataXHR(url, function(response) {
			if (response.c == 0) {

				var pay_url = response.d.recharge + '?appid=' + response.d.appid + '&gsid=0&uid=' + userId + '&gameorderid=' + generate_order_id +
					'&productid=' + response.d.productid + '&amount=' + readl_money + '&time=' + response.d.time +
					'&sign=' + response.d.sign + ''
				console.log(pay_url);
				location.href = pay_url;
				closePayWindow();
			}
		});
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
		var pay_t = Date.parse(new Date())
		var pay_time = pay_t / 1000;
		var report_sign = md5('64' + uid + pay_time + 'sU1do6avQr4kZACo8hWT1P7lhL0hbL7B');
		var report_url = "http://ly.zhaouc.com/h5game/roleUpdate/index?appid=" + '64' + "&uid=" + uid + "&time=" + pay_time + "&sign=" + report_sign;
		console.log(report_url);
		$.get(report_url, function(data) {
			console.log(data);
		});
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;

		var url = "/index.php/api/sign_collect/" + passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function(response) {});
	}
};
pf.prototype.showQrCode = function() {};


pf.prototype.isDownloadable = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
