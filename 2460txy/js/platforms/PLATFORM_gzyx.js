var p_name = 'gzyx';

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
	this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
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
		var generate_order_id = res.d.order_id;
		var userId = res.d.userId;
		var readl_money = amount;
		// var readl_money = 1;


		this.g2b.loadScript("//js.2460.xileyougame.com/js/jquery-3.1.1.js", function() {

			var sdk_url = "http://h5api.guoziyx.com/sdk/8899sdk-1.2.0.js";

			this.g2b.loadScript(sdk_url, function() {

				var payData = {
					game_id: 670306,
					trade_id: generate_order_id,
					user_id: userId,
					role_id: orderData.openId,
					body: orderData.subject,
					total_fee: readl_money,
					notify_url: "http://h5sdk.zytxgame.com/index.php/api/notify/gzyx"
				};

				var url = "http://h5sdk.zytxgame.com/index.php/api/sign_order/gzyx/" + param.appId + "?p=" + encodeURIComponent(JSON.stringify(payData));
				this.g2b.getDataXHR(url, function(response) {
					if (response.c == 0) {
						var sign_data = response.d;
						payData.nonce_str = sign_data.nonce_str;
						payData.sign = sign_data.sign;
						$.kd_pay(payData);
					}
				});

			});
		});
		closePayWindow();


	});
};
pf.prototype.checkFocus = function(data) {


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

};
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};

pf.prototype.isDownloadable = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
