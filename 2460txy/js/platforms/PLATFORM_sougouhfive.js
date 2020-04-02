var sogou_game;
var p_name = 'sougouhfive';
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
	g2b.postMessage(g2b.MESSAGES.INIT_CALLBACK);


	// var that = this;
	// this.g2b.loadScript("http://h5.mobo168.com/open/sdk/ldgame.js", function() {
	//     var url = "http://h5sdk.zytxgame.com/index.php/api/init/sogou/" + that.passData.appId;
	//
	//     this.g2b.getDataXHR(url, function(response) {
	//         if (response.c == 0) {
	//
	//             ldgame = new ldgame({
	//                 "app_key": response.d.AppKey,
	//             });
	//
	//             this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
	//         }
	//     });
	//
	// });

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
	param.platform = 'sougou';
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		// alert("res " + res.d);
		var generate_order_id = res.d.order_id;
		var user_id = res.d.userId;
		var readl_money = amount / 100;
		// var readl_money = 0.01;
		var subject = orderData.subject;

		var sign_url = 'http://h5sdk.zytxgame.com/index.php/api/sign_order/' + p_name + '/' + param.appId;
		this.g2b.getDataXHR(sign_url, function(response) {
			var appid = response.d.gid;
			var channel = response.d.channel;
			var url = "http://yx.sogou.com/h5game/pay/prepay?gouzaiId=" + user_id + "&thirdOrderId=" + generate_order_id + "&productName=" + subject + "&orderAmount=" + readl_money + "&gid=" + appid + "&channel=" + channel;
			console.log(url);

			if (typeof(SogouPay) != 'undefined') {
				SogouPay.activatePay(readl_money, subject, generate_order_id, generate_order_id);
			} else if (typeof(SogouGameBox) != 'undefined') {
				SogouGameBox.sogouPay(readl_money, subject, generate_order_id);
			} else {
				location.href = url;
			}

		});


		// var readl_money = 0.01; // for test
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

		var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/sougouhfive/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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
	// alert('click share button');
	this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
};
